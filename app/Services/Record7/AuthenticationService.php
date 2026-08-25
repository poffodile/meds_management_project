<?php

namespace App\Services\Record7;

use App\Models\Record7\AccountInvitation;
use App\Models\Record7\MfaMethod;
use App\Models\Record7\Organisation;
use App\Models\Record7\PasswordReset;
use App\Models\Record7\RecoveryCode;
use App\Models\Record7\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Record7 credentials: passwords, lockout, verification codes, invitations
 * and password resets.
 *
 * Covers Sections 0.2, 0.3, 0.6, 0.7 and the lockout half of 0.8.
 *
 * ONE FAILURE MESSAGE
 * Every credential failure — unknown username, wrong password, suspended
 * account, expired access — returns the same sentence. Distinguishing them
 * would let an anonymous visitor enumerate who works for an organisation.
 * The audit trail records the real reason; the screen never does.
 */
class AuthenticationService
{
    /** Wrong attempts before the account locks. */
    public const MAX_ATTEMPTS = 5;

    /** How long a lock lasts. */
    public const LOCK_MINUTES = 15;

    /** How long an invitation or reset link stays usable. */
    public const INVITATION_HOURS = 72;

    public const RESET_MINUTES = 30;

    public function failureMessage(): string
    {
        return 'We could not sign you in with those details.';
    }

    /* ── Passwords ───────────────────────────────────────────────────────── */

    public function findUser(Organisation $organisation, string $username): ?User
    {
        return User::where('organisation_id', $organisation->id)
            ->whereRaw('LOWER(username) = ?', [mb_strtolower(trim($username))])
            ->first();
    }

    public function passwordMatches(User $user, string $plainPassword): bool
    {
        $hash = (string) $user->password_hash;

        return $hash !== '' && Hash::check($plainPassword, $hash);
    }

    public function registerFailure(User $user): void
    {
        $user->failed_attempts = (int) $user->failed_attempts + 1;

        if ($user->failed_attempts >= self::MAX_ATTEMPTS) {
            $user->locked_until = now()->addMinutes(self::LOCK_MINUTES);
            $user->account_status = 'security_locked';
        }

        $user->save();
    }

    public function registerSuccess(User $user): void
    {
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->last_signed_in_at = now();
        $user->save();
    }

    /**
     * Clear a lock whose time has run out.
     *
     * A lock is a delay, not a permanent state. Without this the account would
     * stay security_locked for ever and need an administrator.
     */
    public function releaseExpiredLock(User $user): void
    {
        if ($user->account_status === 'security_locked'
            && $user->locked_until !== null
            && now()->greaterThanOrEqualTo($user->locked_until)) {
            $user->account_status = 'active';
            $user->locked_until = null;
            $user->failed_attempts = 0;
            $user->save();
        }
    }

    /* ── Security verification, Section 0.3 ──────────────────────────────── */

    /**
     * The fixed verification code this environment will accept.
     *
     * LOCAL DEVELOPMENT ONLY, AND IMPOSSIBLE IN PRODUCTION.
     *
     * The supplied Section 0 package fixes a code so the journey can be walked
     * without a real second factor. Three independent conditions must all hold
     * before it is honoured, so no single mistake can carry it into a live
     * deployment:
     *
     *   1. The environment must not be production.
     *   2. config('record7.mfa.allow_prototype_code') must be explicitly true.
     *   3. RECORD7_PROTOTYPE_MFA_CODE must be set to a value.
     *
     * When any of those fails this returns null, and verifyCode() then refuses
     * EVERY code rather than falling back to something permissive. A deployment
     * that forgets to configure real verification fails closed.
     */
    /**
     * Which verification mode this environment is running in.
     *
     * A live system is always 'production', whatever configuration says. The
     * mode is a development convenience and must never be able to switch
     * verification off on a real deployment.
     */
    public function verificationMode(): string
    {
        if (app()->environment('production')) {
            return 'production';
        }

        $mode = (string) config('record7.mfa.mode', 'off');

        return in_array($mode, ['off', 'test', 'production'], true) ? $mode : 'off';
    }

    /** Is the verification step part of the journey at all? */
    public function verificationStepEnabled(): bool
    {
        return $this->verificationMode() !== 'off';
    }

    /**
     * Is there any real way to verify?
     *
     * No, and there will not be until an authenticator, a passkey or a
     * delivery channel is integrated. In 'production' mode that means
     * verification is demanded and then refuses everything, so nobody signs
     * in. That is deliberate: an unfinished security control must fail closed
     * rather than wave people through.
     */
    public function hasRealVerificationProvider(): bool
    {
        return false;
    }

    public function prototypeCode(): ?string
    {
        if (app()->environment('production')) {
            return null;
        }

        if ($this->verificationMode() !== 'test') {
            return null;
        }

        if (! config('record7.mfa.allow_prototype_code')) {
            return null;
        }

        $code = config('record7.mfa.prototype_code');

        return is_string($code) && $code !== '' ? $code : null;
    }

    /** Is this environment running with the fictional code enabled? */
    public function usingPrototypeCode(): bool
    {
        return $this->prototypeCode() !== null;
    }

    public function requiresVerification(User $user): bool
    {
        return $user->mfaMethods()->where('status', 'active')->exists();
    }

    public function primaryMethod(User $user): ?MfaMethod
    {
        return $user->mfaMethods()->where('status', 'active')
            ->orderByDesc('is_primary')->orderBy('id')->first();
    }

    /**
     * Check a submitted code.
     *
     * Two ways in: the environment's fixed development code, or one of the
     * person's own recovery codes. A real deployment configures neither, and
     * this returns false for everything until a genuine method is wired in —
     * which is the correct behaviour for a system that is not finished.
     */
    public function verifyCode(User $user, string $code): bool
    {
        $submitted = trim($code);
        $expected = $this->prototypeCode();

        if ($expected !== null && hash_equals($expected, $submitted)) {
            $this->stampMethod($user);

            return true;
        }

        return $this->consumeRecoveryCode($user, $submitted);
    }

    private function stampMethod(User $user): void
    {
        $method = $this->primaryMethod($user);

        if ($method) {
            $method->last_verified_at = now();
            $method->failed_attempts = 0;
            $method->save();
        }
    }

    /* ── Recovery codes ──────────────────────────────────────────────────── */

    /**
     * Issue a fresh set, replacing any that are left.
     *
     * Returned in plain text exactly once, here. Only hashes are stored, so a
     * lost set cannot be recovered — it can only be replaced.
     */
    public function issueRecoveryCodes(User $user, int $count = 10): array
    {
        $user->recoveryCodes()->whereNull('used_at')->delete();

        $codes = [];

        for ($index = 0; $index < $count; $index++) {
            // Grouped for reading aloud over the phone, which is how these
            // actually get used when somebody is locked out mid-shift.
            $plain = strtolower(Str::random(4).'-'.Str::random(4).'-'.Str::random(4));
            $codes[] = $plain;

            RecoveryCode::create([
                'user_id' => $user->id,
                'code_hash' => hash('sha256', $plain),
                'issued_at' => now(),
            ]);
        }

        return $codes;
    }

    /** Spend one recovery code. Each works once and once only. */
    public function consumeRecoveryCode(User $user, string $plain): bool
    {
        $code = $user->recoveryCodes()
            ->whereNull('used_at')
            ->where('code_hash', hash('sha256', strtolower(trim($plain))))
            ->first();

        if (! $code) {
            return false;
        }

        $code->used_at = now();
        $code->save();

        $this->stampMethod($user);

        return true;
    }

    /* ── First-time activation, Section 0.6 ──────────────────────────────── */

    /** Issue an invitation and return the plain token, which is never stored. */
    public function issueInvitation(User $user): string
    {
        $user->invitations()->where('status', 'pending')->update(['status' => 'cancelled']);

        $plain = Str::random(48);

        AccountInvitation::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'status' => 'pending',
            'sent_at' => now(),
            'expires_at' => now()->addHours(self::INVITATION_HOURS),
        ]);

        return $plain;
    }

    public function findInvitation(string $plainToken): ?AccountInvitation
    {
        $invitation = AccountInvitation::where('token_hash', hash('sha256', trim($plainToken)))
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        return $invitation && $invitation->isRedeemable() ? $invitation : null;
    }

    /** Set the first password and open the account. */
    public function activate(AccountInvitation $invitation, string $password): User
    {
        $user = $invitation->user;

        $user->password_hash = Hash::make($password);
        $user->password_set_at = now();
        $user->account_status = 'active';
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->save();

        $invitation->status = 'used';
        $invitation->used_at = now();
        $invitation->save();

        return $user;
    }

    /* ── Password recovery, Section 0.7 ──────────────────────────────────── */

    public function issueReset(User $user): string
    {
        $user->passwordResets()->where('status', 'pending')->update(['status' => 'cancelled']);

        $plain = Str::random(48);

        PasswordReset::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'status' => 'pending',
            'requested_at' => now(),
            'expires_at' => now()->addMinutes(self::RESET_MINUTES),
        ]);

        return $plain;
    }

    public function findReset(string $plainToken): ?PasswordReset
    {
        $reset = PasswordReset::where('token_hash', hash('sha256', trim($plainToken)))
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        return $reset && $reset->isRedeemable() ? $reset : null;
    }

    /**
     * Complete a reset.
     *
     * Completing a reset also clears a security lock: someone who can prove
     * control of their recovery route has answered the question the lock asked.
     */
    public function completeReset(PasswordReset $reset, string $password): User
    {
        $user = $reset->user;

        $user->password_hash = Hash::make($password);
        $user->password_set_at = now();
        $user->failed_attempts = 0;
        $user->locked_until = null;

        if ($user->account_status === 'security_locked') {
            $user->account_status = 'active';
        }

        $user->save();

        $reset->status = 'used';
        $reset->completed_at = now();
        $reset->save();

        return $user;
    }
}
