<?php

namespace App\Services\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\TrustedDevice;
use App\Models\Record7\User;
use App\Models\Record7\VerificationEvent;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * When Record7 asks for a second factor, and when it does not.
 *
 * ─────────────────────────────────────────────────────────────────────────
 * NOT PRODUCTION READY. The DECISIONS below are real and enforced. The
 * VERIFICATION ITSELF is still a prototype: there is no authenticator app,
 * no passkey, no email and no SMS delivery integrated yet. Today a code is
 * checked against a fixed development value or a recovery code, and in
 * production both of those are unavailable, so verification refuses
 * everything. Wiring a real method is an outstanding production integration.
 * See docs/care-one-os/RECORD7/MFA-OUTSTANDING.md.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * THE PROBLEM WITH ASKING EVERY TIME
 * A code demanded on every screen, or repeatedly through one shift, stops
 * being a security control and becomes a reflex. People stop reading it, and
 * on a shared trolley tablet it ends up written on a sticky note taped to the
 * back. Asking less, at the moments that matter, is the stronger position.
 *
 * SO IT IS ASKED FOR WHEN IT IS WORTH SOMETHING
 *   first_sign_in        the account has never been verified on anything
 *   new_device           this device has not been seen before
 *   shared_device        the device is shared, so it is never remembered
 *   device_expired       trust in this device has run out
 *   after_password_reset the password changed, so prove it is still you
 *   suspicious_activity  recent failures, or a lock, on this account
 *   elevated_access      the account can reach beyond its own clinical work
 *
 * AND NOT ASKED FOR
 *   on any screen after sign-in;
 *   again during a session;
 *   again on a device already trusted, until that trust expires.
 */
class VerificationPolicy
{
    /**
     * Permissions that make an account worth challenging every time.
     *
     * NOT simply "sensitive": in the supplied permission set twelve of the
     * thirteen permissions are flagged sensitive, so treating that flag as the
     * test would challenge everybody and mean nothing.
     *
     * These are the permissions where an impostor does damage that outlives the
     * session — changing who has access, rewriting a record, or reading across
     * the organisation. Administering a medicine is sensitive and is audited,
     * but it does not let someone quietly grant themselves more tomorrow.
     *
     * Configurable, because a different organisation may draw the line
     * elsewhere.
     */
    public function elevatedPermissions(): array
    {
        return (array) config('record7.mfa.elevated_permissions', [
            'manage_organisation',
            'manage_staff',
            'view_access_audit',
            'correction_approval',
            'cross_service_access',
        ]);
    }

    /**
     * Access types that are elevated by their nature.
     *
     * A manager or an oversight grant reaches beyond the holder's own work, so
     * it is challenged whatever permissions happen to be attached.
     */
    public function elevatedAccessTypes(): array
    {
        return (array) config('record7.mfa.elevated_access_types', ['manager', 'oversight']);
    }

    /** Failures in the recent past that make an account look interesting. */
    public const SUSPICIOUS_FAILURES = 3;

    /** The longest any organisation may trust a device for. */
    public const MAX_TRUST_DAYS = 90;

    public function __construct(
        private readonly AuthenticationService $auth,
        private readonly AccessPolicy $access
    ) {
    }

    /**
     * Should this sign-in be verified, and why.
     *
     * Returns null when it should not. The reason is recorded either way, so a
     * manager can see both what was asked for and what was waved through.
     */
    public function reasonToVerify(User $user, Request $request, bool $deviceIsShared = false): ?string
    {
        $mode = $this->auth->verificationMode();

        // PROTOTYPE DEFAULT: the step is not part of the journey at all, so
        // normal UI testing runs organisation, credentials, house, Today.
        if ($mode === 'off') {
            return null;
        }

        // PRODUCTION: no real provider is integrated, so verification is
        // demanded and then refuses everything. Fails closed on purpose.
        if ($mode === 'production' && ! $this->auth->hasRealVerificationProvider()) {
            return 'no_verification_provider';
        }

        // Nothing to verify with. An account with no method configured cannot
        // be asked for one, and blocking it would lock people out of a system
        // they need — so it is allowed through and recorded as skipped.
        if (! $this->hasAnyMethod($user)) {
            return null;
        }

        if ($this->hasElevatedAccess($user)) {
            return 'elevated_access';
        }

        if ($deviceIsShared || $this->deviceIsKnownShared($request)) {
            return 'shared_device';
        }

        if ($this->neverVerified($user)) {
            return 'first_sign_in';
        }

        if ($this->passwordChangedSinceLastVerification($user)) {
            return 'after_password_reset';
        }

        if ($this->looksSuspicious($user)) {
            return 'suspicious_activity';
        }

        // An organisation can switch device trust off entirely.
        if (! $this->deviceTrustEnabled($user)) {
            return 'new_device';
        }

        $device = $this->device($user, $request);

        if (! $device) {
            return 'new_device';
        }

        if (! $device->isUsableWithoutVerification()) {
            return $device->hasExpired() ? 'device_expired' : 'new_device';
        }

        return null;
    }

    /** Words for the screen, explaining why the code is being asked for. */
    public function explain(string $reason): string
    {
        return match ($reason) {
            'first_sign_in' => 'This is the first time you have signed in, so we need to confirm it is you.',
            'new_device' => 'We have not seen this device before, so we need to confirm it is you.',
            'shared_device' => 'This is a shared device, so everyone confirms their identity each time.',
            'device_expired' => 'It has been a while since you confirmed your identity on this device.',
            'after_password_reset' => 'Your password changed recently, so we need to confirm it is you.',
            'suspicious_activity' => 'There have been failed sign-in attempts on this account recently.',
            'elevated_access' => 'Your access reaches beyond your own work, so it is confirmed every time.',
            'no_verification_provider' => 'Security verification is not available on this system yet. '
                .'Please contact your organisation administrator.',
            default => 'We need to confirm it is you.',
        };
    }

    /* ── Who counts as elevated ──────────────────────────────────────────── */

    /**
     * Does this account reach beyond its own clinical work?
     *
     * Decided from what the person can actually DO and how they hold their
     * access — not from a role code. A Service Manager, a Medication Lead, an
     * Organisation Administrator and a Quality and Compliance Reviewer all
     * qualify through their permissions or their access type, without any of
     * them being named here. A support worker with an unusual extra permission
     * qualifies too, which naming roles would have missed.
     */
    public function hasElevatedAccess(User $user): bool
    {
        foreach ($user->serviceAccess()->get() as $grant) {
            if ($grant->isUsable() && in_array($grant->access_type, $this->elevatedAccessTypes(), true)) {
                return true;
            }
        }

        $elevated = $this->elevatedPermissions();

        // Checked per house, because a permission can be granted in one and
        // not another — and holding it anywhere is enough to be challenged.
        $houses = $this->access->availableServices($user);
        $scopes = $houses === [] ? [null] : array_map(fn ($house) => $house->id, $houses);

        foreach ($scopes as $serviceId) {
            foreach ($elevated as $permission) {
                if ($this->access->allows($user, $permission, $serviceId)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** The elevated permissions this account actually holds, for an audit view. */
    public function elevatedPermissionsHeldBy(User $user): array
    {
        $held = [];
        $houses = $this->access->availableServices($user);
        $scopes = $houses === [] ? [null] : array_map(fn ($house) => $house->id, $houses);

        foreach ($this->elevatedPermissions() as $permission) {
            foreach ($scopes as $serviceId) {
                if ($this->access->allows($user, $permission, $serviceId)) {
                    $held[] = $permission;
                    break;
                }
            }
        }

        return $held;
    }

    /* ── How long a device stays trusted ─────────────────────────────────── */

    /**
     * The trust period for this person's organisation.
     *
     * The organisation's own setting, falling back to the configured default,
     * and clamped so nobody can set it to something reckless. A value of zero
     * means devices are never trusted.
     */
    public function trustDaysFor(User $user): int
    {
        $organisation = $user->relationLoaded('organisation')
            ? $user->organisation
            : Organisation::find($user->organisation_id);

        $days = $organisation?->trusted_device_days
            ?? (int) config('record7.mfa.trust_device_days', 30);

        return max(0, min((int) $days, self::MAX_TRUST_DAYS));
    }

    public function deviceTrustEnabled(User $user): bool
    {
        $organisation = $user->relationLoaded('organisation')
            ? $user->organisation
            : Organisation::find($user->organisation_id);

        if ($organisation && $organisation->device_trust_enabled === false) {
            return false;
        }

        return $this->trustDaysFor($user) > 0;
    }

    /* ── Devices ─────────────────────────────────────────────────────────── */

    /**
     * A stable, non-identifying signature for this device.
     *
     * The raw user agent is never stored — it is a fingerprinting surface and
     * adds nothing. A hash is enough to answer "the same device again?".
     */
    public function deviceHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->userAgent() ?? 'unknown',
            $request->ip() ?? 'unknown',
        ]));
    }

    public function device(User $user, Request $request): ?TrustedDevice
    {
        return TrustedDevice::where('user_id', $user->id)
            ->where('device_hash', $this->deviceHash($request))
            ->first();
    }

    private function deviceIsKnownShared(Request $request): bool
    {
        // Shared is a property of the DEVICE, not of one person's row: if
        // anybody has marked this device shared, it is shared for everybody.
        return TrustedDevice::where('device_hash', $this->deviceHash($request))
            ->where('shared', true)
            ->exists();
    }

    /**
     * Remember this device, unless it is shared or the organisation says not to.
     *
     * A shared device is recorded so it can be recognised, but never trusted —
     * so the next person to pick the tablet up is asked for their own code
     * rather than inheriting the last person's.
     */
    public function rememberDevice(User $user, Request $request, bool $shared = false, ?string $label = null): TrustedDevice
    {
        $device = TrustedDevice::firstOrNew([
            'user_id' => $user->id,
            'device_hash' => $this->deviceHash($request),
        ]);

        $device->label = $label ?? $device->label;
        $device->shared = $shared || $device->shared;

        $trustable = ! $device->shared && $this->deviceTrustEnabled($user);

        $device->status = $trustable ? 'trusted' : 'revoked';
        $device->trusted_at = now();
        $device->trusted_until = $trustable ? now()->addDays($this->trustDaysFor($user)) : null;
        $device->last_seen_at = now();
        $device->save();

        return $device;
    }

    /**
     * Withdraw trust in one device.
     *
     * Only somebody who can manage staff or the organisation may do this, and
     * only within their own organisation. The revocation names who did it and
     * why, because a device taken away from somebody has to be answerable.
     */
    public function revokeDevice(TrustedDevice $device, User $actor, ?string $reason = null): TrustedDevice
    {
        if (! $this->canManageDevices($actor)) {
            throw new RuntimeException('You do not have permission to revoke a trusted device.');
        }

        $owner = $device->user;

        if (! $owner || (int) $owner->organisation_id !== (int) $actor->organisation_id) {
            throw new RuntimeException('That device belongs to another organisation.');
        }

        $device->status = 'revoked';
        $device->trusted_until = null;
        $device->revoked_by_user_id = $actor->id;
        $device->revoked_at = now();
        $device->revoked_reason = $reason;
        $device->save();

        return $device;
    }

    /** Withdraw trust in every device for one person. */
    public function revokeAllDevices(User $user, User $actor, ?string $reason = null): int
    {
        $count = 0;

        foreach (TrustedDevice::where('user_id', $user->id)->where('status', 'trusted')->get() as $device) {
            $this->revokeDevice($device, $actor, $reason);
            $count++;
        }

        return $count;
    }

    /** Devices an administrator can see and act on, for one person. */
    public function devicesFor(User $user): array
    {
        return TrustedDevice::where('user_id', $user->id)
            ->orderByDesc('last_seen_at')
            ->get()
            ->all();
    }

    public function canManageDevices(User $actor): bool
    {
        foreach (['manage_staff', 'manage_organisation'] as $permission) {
            foreach ($this->access->availableServices($actor) as $house) {
                if ($this->access->allows($actor, $permission, $house->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Everything a person is signed in on, so they can end it themselves. */
    public function forgetDevices(User $user): void
    {
        TrustedDevice::where('user_id', $user->id)->update(['status' => 'revoked', 'trusted_until' => null]);
    }

    /* ── Recording ───────────────────────────────────────────────────────── */

    public function record(User $user, string $reason, string $result, ?string $method, Request $request): void
    {
        VerificationEvent::create([
            'user_id' => $user->id,
            'reason' => $reason,
            'method' => $method,
            'result' => $result,
            'device_hash' => $this->deviceHash($request),
            'occurred_at' => now(),
        ]);
    }

    /* ── The individual questions ────────────────────────────────────────── */

    private function hasAnyMethod(User $user): bool
    {
        return $user->mfaMethods()->where('status', 'active')->exists();
    }

    private function neverVerified(User $user): bool
    {
        return ! VerificationEvent::where('user_id', $user->id)->where('result', 'passed')->exists();
    }

    private function passwordChangedSinceLastVerification(User $user): bool
    {
        if (! $user->password_set_at) {
            return false;
        }

        $lastPassed = VerificationEvent::where('user_id', $user->id)
            ->where('result', 'passed')
            ->max('occurred_at');

        return $lastPassed === null || $user->password_set_at->greaterThan($lastPassed);
    }

    private function looksSuspicious(User $user): bool
    {
        if ($user->account_status === 'security_locked' || $user->locked_until !== null) {
            return true;
        }

        return (int) $user->failed_attempts >= self::SUSPICIOUS_FAILURES;
    }

    /* ── Recovery codes ──────────────────────────────────────────────────── */

    public function unusedRecoveryCodeCount(User $user): int
    {
        return $user->recoveryCodes()->whereNull('used_at')->count();
    }
}
