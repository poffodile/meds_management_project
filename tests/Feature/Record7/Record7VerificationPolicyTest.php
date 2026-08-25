<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\TrustedDevice;
use App\Models\Record7\VerificationEvent;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\VerificationPolicy;
use Illuminate\Support\Facades\Auth;

/**
 * Section 0.3 as a policy rather than a code on every screen.
 *
 * Two questions these answer: is the fictional code genuinely impossible in
 * production, and is verification demanded at the moments that are worth it
 * rather than constantly.
 */
class Record7VerificationPolicyTest extends Record7TestCase
{
    private function policy(): VerificationPolicy
    {
        return app(VerificationPolicy::class);
    }

    private function auth(): AuthenticationService
    {
        return app(AuthenticationService::class);
    }

    /* ── The fictional code cannot exist in production ───────────────────── */

    public function test_the_fictional_code_is_refused_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertNull($this->auth()->prototypeCode());
        $this->assertFalse($this->auth()->usingPrototypeCode());
        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), self::CODE));
    }

    public function test_the_fictional_code_needs_its_own_switch_as_well_as_a_value(): void
    {
        // The value alone is not enough. Two switches so a stray environment
        // variable cannot enable it on its own.
        config(['record7.mfa.allow_prototype_code' => false]);

        $this->assertNull($this->auth()->prototypeCode());
        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), self::CODE));
    }

    public function test_with_no_code_configured_every_code_is_refused(): void
    {
        // Fails closed. A deployment that forgets to wire real verification
        // refuses everything rather than accepting anything.
        config(['record7.mfa.prototype_code' => null]);

        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), '246810'));
        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), '000000'));
    }

    public function test_the_test_environment_label_shows_only_when_the_code_is_enabled(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ]);

        // The label itself is rendered by React, so what the server controls —
        // and what this asserts — is whether the code is handed to the page at
        // all. No code, no label.
        $this->get('/record7/verify')
            ->assertOk()
            ->assertSee('&quot;prototypeCode&quot;:&quot;'.self::CODE.'&quot;', false);

        config(['record7.mfa.allow_prototype_code' => false]);

        $this->get('/record7/verify')
            ->assertOk()
            ->assertSee('&quot;prototypeCode&quot;:null', false)
            ->assertDontSee(self::CODE, false);
    }

    /* ── When verification is demanded ───────────────────────────────────── */

    public function test_a_first_sign_in_is_verified(): void
    {
        $user = $this->user('olivia.carter');

        $this->assertSame('first_sign_in', $this->policy()->reasonToVerify($user, request()));
    }

    /**
     * Elevated access is decided from what a person can DO, never from a role
     * code. Each of these qualifies for a different reason, and none of them
     * is named anywhere in the policy.
     */
    public function test_every_elevated_account_is_verified_every_time(): void
    {
        $elevated = [
            'priya.nair' => 'Organisation Administrator',
            'daniel.evans' => 'Service Manager',
            'sarah.ahmed' => 'Medication Lead',
            'maya.thompson' => 'Quality and Compliance Reviewer',
        ];

        foreach ($elevated as $username => $what) {
            $user = $this->user($username);

            // Fully trusted device, already verified once: only elevation can
            // be the reason left.
            $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
            $this->policy()->rememberDevice($user, request());

            $this->assertTrue(
                $this->policy()->hasElevatedAccess($user),
                $what.' must count as elevated access'
            );

            $this->assertSame(
                'elevated_access',
                $this->policy()->reasonToVerify($user, request()),
                $what.' must confirm every time'
            );
        }
    }

    public function test_an_ordinary_clinical_account_is_not_treated_as_elevated(): void
    {
        // Olivia and Noah do sensitive clinical work, and it is audited, but
        // neither can change who has access — so neither is challenged on a
        // device they have already verified.
        foreach (['olivia.carter', 'noah.williams'] as $username) {
            $user = $this->user($username);

            $this->assertFalse(
                $this->policy()->hasElevatedAccess($user),
                $username.' should not be elevated merely for clinical permissions'
            );
        }
    }

    public function test_elevation_follows_the_permission_and_not_the_role(): void
    {
        // Give an ordinary support worker one elevated permission and she
        // becomes elevated, without her role changing at all.
        $olivia = $this->user('olivia.carter');
        $this->assertFalse($this->policy()->hasElevatedAccess($olivia));

        $olivia->permissionRules()->create([
            'permission_id' => \App\Models\Record7\Permission::where('code', 'view_access_audit')->value('id'),
            'service_id' => $this->house('Oakwood House')->id,
            'effect' => 'allow',
            'status' => 'active',
            'reason' => 'Test: an unusual extra permission.',
        ]);

        $this->assertTrue($this->policy()->hasElevatedAccess($olivia->fresh()));
        $this->assertContains('view_access_audit',
            $this->policy()->elevatedPermissionsHeldBy($olivia->fresh()));
    }

    public function test_an_oversight_or_manager_grant_is_elevated_by_its_nature(): void
    {
        // Daniel holds his houses as 'manager', Sarah hers as 'oversight'.
        foreach (['daniel.evans', 'sarah.ahmed'] as $username) {
            $this->assertTrue($this->policy()->hasElevatedAccess($this->user($username)));
        }
    }

    public function test_a_trusted_device_is_not_asked_again(): void
    {
        $user = $this->user('olivia.carter');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $this->policy()->rememberDevice($user, request());

        $this->assertNull($this->policy()->reasonToVerify($user, request()));
    }

    public function test_an_unseen_device_is_asked(): void
    {
        $user = $this->user('noah.williams');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        // Remembered against a different signature, so this one is unseen.
        TrustedDevice::create([
            'user_id' => $user->id,
            'device_hash' => str_repeat('a', 64),
            'status' => 'trusted',
            'trusted_at' => now(),
            'trusted_until' => now()->addDays(30),
        ]);

        $this->assertSame('new_device', $this->policy()->reasonToVerify($user, request()));
    }

    public function test_trust_in_a_device_expires(): void
    {
        $user = $this->user('noah.williams');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $device = $this->policy()->rememberDevice($user, request());
        $device->trusted_until = now()->subDay();
        $device->save();

        $this->assertSame('device_expired', $this->policy()->reasonToVerify($user, request()));
    }

    /* ── Trust duration belongs to the organisation ──────────────────────── */

    public function test_the_trust_period_falls_back_to_the_secure_default(): void
    {
        config(['record7.mfa.trust_device_days' => 30]);

        $this->assertSame(30, $this->policy()->trustDaysFor($this->user('noah.williams')));
    }

    public function test_an_organisation_can_set_its_own_trust_period(): void
    {
        $organisation = $this->organisation();
        $organisation->trusted_device_days = 7;
        $organisation->save();

        $user = $this->user('noah.williams')->fresh();

        $this->assertSame(7, $this->policy()->trustDaysFor($user));

        $device = $this->policy()->rememberDevice($user, request());

        $this->assertEqualsWithDelta(
            7,
            now()->diffInDays($device->trusted_until),
            1,
            'The device must be trusted for the organisation period, not the default.'
        );
    }

    public function test_no_organisation_can_set_a_reckless_trust_period(): void
    {
        $organisation = $this->organisation();
        $organisation->trusted_device_days = 3650;
        $organisation->save();

        $this->assertSame(
            VerificationPolicy::MAX_TRUST_DAYS,
            $this->policy()->trustDaysFor($this->user('noah.williams')->fresh())
        );
    }

    public function test_an_organisation_can_switch_device_trust_off_entirely(): void
    {
        $organisation = $this->organisation();
        $organisation->device_trust_enabled = false;
        $organisation->save();

        $user = $this->user('noah.williams')->fresh();

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $device = $this->policy()->rememberDevice($user, request());

        $this->assertNull($device->trusted_until);
        $this->assertSame('new_device', $this->policy()->reasonToVerify($user, request()));
    }

    public function test_a_zero_day_period_means_never_trust_a_device(): void
    {
        config(['record7.mfa.trust_device_days' => 0]);

        $user = $this->user('noah.williams');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $this->policy()->rememberDevice($user, request());

        $this->assertSame('new_device', $this->policy()->reasonToVerify($user, request()));
    }

    /* ── Administrators can revoke a trusted device ──────────────────────── */

    public function test_an_administrator_can_revoke_a_trusted_device(): void
    {
        $olivia = $this->user('olivia.carter');
        $priya = $this->user('priya.nair');

        $this->policy()->record($olivia, 'first_sign_in', 'passed', 'code', request());
        $device = $this->policy()->rememberDevice($olivia, request());
        $this->assertTrue($device->isUsableWithoutVerification());

        $revoked = $this->policy()->revokeDevice($device, $priya, 'Handset reported lost.');

        $this->assertSame('revoked', $revoked->status);
        $this->assertSame($priya->id, $revoked->revoked_by_user_id);
        $this->assertSame('Handset reported lost.', $revoked->revoked_reason);
        $this->assertNotNull($revoked->revoked_at);

        // And the next sign-in is challenged again.
        $this->assertSame('new_device', $this->policy()->reasonToVerify($olivia->fresh(), request()));
    }

    public function test_an_ordinary_account_cannot_revoke_a_device(): void
    {
        $olivia = $this->user('olivia.carter');
        $noah = $this->user('noah.williams');

        $device = $this->policy()->rememberDevice($olivia, request());

        $this->assertFalse($this->policy()->canManageDevices($noah));

        $this->expectException(\RuntimeException::class);
        $this->policy()->revokeDevice($device, $noah);
    }

    public function test_an_administrator_can_revoke_every_device_for_one_person(): void
    {
        $olivia = $this->user('olivia.carter');
        $priya = $this->user('priya.nair');

        $this->policy()->rememberDevice($olivia, request());
        \App\Models\Record7\TrustedDevice::create([
            'user_id' => $olivia->id,
            'device_hash' => str_repeat('b', 64),
            'status' => 'trusted',
            'trusted_at' => now(),
            'trusted_until' => now()->addDays(30),
        ]);

        $this->assertSame(2, $this->policy()->revokeAllDevices($olivia, $priya, 'Leaver.'));

        foreach ($this->policy()->devicesFor($olivia) as $device) {
            $this->assertSame('revoked', $device->status);
        }
    }

    public function test_a_shared_device_is_never_remembered(): void
    {
        $user = $this->user('olivia.carter');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $device = $this->policy()->rememberDevice($user, request(), true);

        // Recognised, but not trusted.
        $this->assertTrue($device->shared);
        $this->assertNull($device->trusted_until);
        $this->assertFalse($device->isUsableWithoutVerification());
        $this->assertSame('shared_device', $this->policy()->reasonToVerify($user, request()));
    }

    public function test_a_shared_device_is_shared_for_everyone_on_it(): void
    {
        $olivia = $this->user('olivia.carter');
        $noah = $this->user('noah.williams');

        $this->policy()->record($noah, 'first_sign_in', 'passed', 'code', request());
        // Olivia marks the trolley tablet shared.
        $this->policy()->rememberDevice($olivia, request(), true);
        // Noah then trusts the same device.
        $this->policy()->rememberDevice($noah, request(), false);

        // It is still shared, so Noah is asked anyway.
        $this->assertSame('shared_device', $this->policy()->reasonToVerify($noah, request()));
    }

    public function test_a_password_reset_forces_verification_again(): void
    {
        $user = $this->user('olivia.carter');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $this->policy()->rememberDevice($user, request());
        $this->assertNull($this->policy()->reasonToVerify($user, request()));

        $user->password_set_at = now()->addMinute();
        $user->save();

        $this->assertSame('after_password_reset', $this->policy()->reasonToVerify($user->fresh(), request()));
    }

    public function test_recent_failures_force_verification(): void
    {
        $user = $this->user('olivia.carter');

        $this->policy()->record($user, 'first_sign_in', 'passed', 'code', request());
        $this->policy()->rememberDevice($user, request());

        $user->failed_attempts = VerificationPolicy::SUSPICIOUS_FAILURES;
        $user->save();

        $this->assertSame('suspicious_activity', $this->policy()->reasonToVerify($user->fresh(), request()));
    }

    public function test_an_account_with_no_method_is_not_blocked_by_verification(): void
    {
        // Sarah Ahmed has an active method in the fixture; remove it and she
        // must still be able to work rather than be locked out of a system she
        // needs.
        $sarah = $this->user('sarah.ahmed');
        $sarah->mfaMethods()->update(['status' => 'revoked']);

        $this->assertNull($this->policy()->reasonToVerify($sarah, request()));
    }

    public function test_verification_is_not_asked_for_again_inside_a_session(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        // Every screen after sign-in, with no second code anywhere.
        $this->get('/record7')->assertOk();
        $this->get('/record7/houses')->assertOk();
        $this->post('/record7/lock');
        $this->post('/record7/unlock', ['password' => self::PASSWORDS['olivia.carter']]);
        $this->get('/record7')->assertOk();

        $this->assertTrue(Auth::guard('record7')->check());
    }

    /* ── Recovery codes ──────────────────────────────────────────────────── */

    public function test_recovery_codes_are_issued_hashed_and_work_once(): void
    {
        $user = $this->user('olivia.carter');

        $codes = $this->auth()->issueRecoveryCodes($user, 4);

        $this->assertCount(4, $codes);
        $this->assertSame(4, $this->policy()->unusedRecoveryCodeCount($user));

        // Only hashes are stored.
        foreach ($user->recoveryCodes as $stored) {
            $this->assertNotContains($stored->code_hash, $codes);
            $this->assertSame(64, strlen($stored->code_hash));
        }

        $this->assertTrue($this->auth()->consumeRecoveryCode($user, $codes[0]));
        $this->assertFalse($this->auth()->consumeRecoveryCode($user, $codes[0]));
        $this->assertSame(3, $this->policy()->unusedRecoveryCodeCount($user->fresh()));
    }

    public function test_a_recovery_code_works_at_the_verification_screen(): void
    {
        $user = $this->user('olivia.carter');
        $codes = $this->auth()->issueRecoveryCodes($user, 2);

        $this->assertTrue($this->auth()->verifyCode($user, $codes[1]));
        $this->assertFalse($this->auth()->verifyCode($user, $codes[1]));
    }

    public function test_issuing_a_new_set_replaces_the_old_one(): void
    {
        $user = $this->user('olivia.carter');

        $first = $this->auth()->issueRecoveryCodes($user, 3);
        $this->auth()->issueRecoveryCodes($user, 3);

        $this->assertFalse($this->auth()->consumeRecoveryCode($user, $first[0]));
    }

    /* ── Recording ───────────────────────────────────────────────────────── */

    public function test_both_demanded_and_skipped_verifications_are_recorded(): void
    {
        $user = $this->user('olivia.carter');

        $this->policy()->record($user, 'first_sign_in', 'required', null, request());
        $this->policy()->record($user, 'trusted_device', 'skipped', null, request());

        $results = VerificationEvent::where('user_id', $user->id)->pluck('result')->all();

        $this->assertContains('required', $results);
        $this->assertContains('skipped', $results);
    }

    public function test_the_device_signature_is_stored_only_as_a_hash(): void
    {
        $user = $this->user('olivia.carter');
        $device = $this->policy()->rememberDevice($user, request());

        $this->assertSame(64, strlen($device->device_hash));
        $this->assertStringNotContainsString('Mozilla', $device->device_hash);
        $this->assertStringNotContainsString('Symfony', $device->device_hash);
    }
}
