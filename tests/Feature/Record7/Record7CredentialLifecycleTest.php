<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\AccountInvitation;
use App\Models\Record7\PasswordReset;
use App\Models\Record7\User;
use App\Services\Record7\AuthenticationService;
use Illuminate\Support\Facades\Auth;

/**
 * Sections 0.6, 0.7 and 0.8 — activation, recovery and restricted states.
 */
class Record7CredentialLifecycleTest extends Record7TestCase
{
    private function auth(): AuthenticationService
    {
        return app(AuthenticationService::class);
    }

    /* ── 0.6 First-time activation ───────────────────────────────────────── */

    public function test_an_invited_account_cannot_sign_in_before_activation(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        $this->from('/record7/login')->post('/record7/login/credentials', [
            'username' => 'adam.fletcher',
            'password' => 'anything-at-all',
        ])->assertSessionHas('error', 'We could not sign you in with those details.');

        $this->assertFalse(Auth::guard('record7')->check());
    }

    public function test_a_valid_activation_link_opens_the_activation_screen(): void
    {
        $this->get('/record7/activate/record7-local-activation-token')
            ->assertOk()
            ->assertSee('Auth\/Activate', false)
            ->assertSee('&quot;valid&quot;:true', false);
    }

    public function test_an_unknown_activation_link_is_refused_without_a_form(): void
    {
        $this->get('/record7/activate/not-a-real-token')
            ->assertOk()
            ->assertSee('&quot;valid&quot;:false', false);
    }

    public function test_activation_sets_the_first_password_and_opens_the_account(): void
    {
        $this->post('/record7/activate/record7-local-activation-token', [
            'password' => 'FirstPassword2026',
            'password_confirmation' => 'FirstPassword2026',
        ])->assertRedirect('/record7/login');

        $user = User::where('username', 'adam.fletcher')->firstOrFail();

        $this->assertSame('active', $user->account_status);
        $this->assertNotNull($user->password_hash);

        // And the account now works, end to end.
        $this->signIn('adam.fletcher', 'FirstPassword2026');
        $this->assertTrue(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'activation')
                ->where('event_result', 'success')->exists()
        );
    }

    public function test_an_activation_link_can_only_be_used_once(): void
    {
        $this->post('/record7/activate/record7-local-activation-token', [
            'password' => 'FirstPassword2026',
            'password_confirmation' => 'FirstPassword2026',
        ]);

        $this->post('/record7/activate/record7-local-activation-token', [
            'password' => 'SecondPassword2026',
            'password_confirmation' => 'SecondPassword2026',
        ])->assertSessionHas('error');

        $this->assertSame('used', AccountInvitation::latest('id')->first()->status);
    }

    public function test_an_expired_invitation_is_refused(): void
    {
        AccountInvitation::query()->update(['expires_at' => now()->subHour()]);

        $this->get('/record7/activate/record7-local-activation-token')
            ->assertSee('&quot;valid&quot;:false', false);
    }

    public function test_a_weak_first_password_is_refused(): void
    {
        $this->post('/record7/activate/record7-local-activation-token', [
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertSame('invited', User::where('username', 'adam.fletcher')->value('account_status'));
    }

    /* ── 0.7 Password recovery ───────────────────────────────────────────── */

    public function test_a_reset_request_gives_the_same_answer_for_a_real_and_an_unknown_account(): void
    {
        $expected = 'If those details match an account, a reset link has been sent to its work email address.';

        $this->post('/record7/forgot-password', [
            'organisation' => self::ORGANISATION, 'username' => 'olivia.carter',
        ])->assertSessionHas('status', $expected);

        $this->post('/record7/forgot-password', [
            'organisation' => self::ORGANISATION, 'username' => 'nobody.here',
        ])->assertSessionHas('status', $expected);
    }

    public function test_a_reset_link_changes_the_password_and_the_old_one_stops_working(): void
    {
        $olivia = $this->user('olivia.carter');
        $token = $this->auth()->issueReset($olivia);

        $this->post('/record7/reset-password/'.$token, [
            'password' => 'BrandNewPassword2026',
            'password_confirmation' => 'BrandNewPassword2026',
        ])->assertRedirect('/record7/login');

        $this->signIn('olivia.carter', 'BrandNewPassword2026');
        $this->assertTrue(Auth::guard('record7')->check());

        $this->post('/record7/sign-out');

        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ]);
        $this->assertFalse(Auth::guard('record7')->check(), 'The old password must stop working.');
    }

    public function test_a_reset_link_can_only_be_used_once(): void
    {
        $token = $this->auth()->issueReset($this->user('olivia.carter'));

        $this->post('/record7/reset-password/'.$token, [
            'password' => 'BrandNewPassword2026', 'password_confirmation' => 'BrandNewPassword2026',
        ]);

        $this->post('/record7/reset-password/'.$token, [
            'password' => 'AnotherPassword2026', 'password_confirmation' => 'AnotherPassword2026',
        ])->assertSessionHas('error');

        $this->assertSame('used', PasswordReset::latest('id')->first()->status);
    }

    public function test_an_expired_reset_link_is_refused(): void
    {
        $token = $this->auth()->issueReset($this->user('olivia.carter'));
        PasswordReset::query()->update(['expires_at' => now()->subMinute()]);

        $this->get('/record7/reset-password/'.$token)->assertSee('&quot;valid&quot;:false', false);
    }

    public function test_completing_a_reset_clears_a_security_lock(): void
    {
        $user = $this->user('olivia.carter');
        $user->account_status = 'security_locked';
        $user->locked_until = now()->addHour();
        $user->failed_attempts = 5;
        $user->save();

        $token = $this->auth()->issueReset($user);

        $this->post('/record7/reset-password/'.$token, [
            'password' => 'BrandNewPassword2026', 'password_confirmation' => 'BrandNewPassword2026',
        ]);

        $user->refresh();

        $this->assertSame('active', $user->account_status);
        $this->assertNull($user->locked_until);
        $this->assertSame(0, $user->failed_attempts);
    }

    public function test_a_suspended_account_is_never_sent_a_reset_link(): void
    {
        $this->post('/record7/forgot-password', [
            'organisation' => self::ORGANISATION, 'username' => 'ethan.cole',
        ]);

        $this->assertSame(
            0,
            PasswordReset::where('user_id', $this->user('ethan.cole')->id)->count()
        );
    }

    /* ── 0.8 Restricted states ───────────────────────────────────────────── */

    public function test_every_restricted_state_is_refused_before_any_house_is_revealed(): void
    {
        $states = ['suspended', 'inactive', 'access_expired', 'security_locked', 'invited'];

        foreach ($states as $state) {
            $user = $this->user('olivia.carter');
            $user->account_status = $state;
            $user->locked_until = $state === 'security_locked' ? now()->addHour() : null;
            $user->save();

            $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
            $response = $this->post('/record7/login/credentials', [
                'username' => 'olivia.carter',
                'password' => self::PASSWORDS['olivia.carter'],
            ]);

            $this->assertFalse(Auth::guard('record7')->check(), $state.' must not sign in');
            $this->assertStringNotContainsString('Oakwood', $response->getContent(), $state.' must reveal no house');
        }
    }

    public function test_a_lock_is_released_once_its_time_has_passed(): void
    {
        $user = $this->user('olivia.carter');
        $user->account_status = 'security_locked';
        $user->locked_until = now()->subMinute();
        $user->failed_attempts = 5;
        $user->save();

        $this->signIn('olivia.carter');

        $this->assertTrue(Auth::guard('record7')->check(), 'An expired lock must not need an administrator.');
    }

    public function test_access_withdrawn_mid_session_stops_the_next_request(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->get('/record7')->assertOk();

        // A manager suspends the account while the person is working.
        $user = $this->user('olivia.carter');
        $user->account_status = 'suspended';
        $user->save();

        $this->get('/record7')->assertRedirect('/record7/login');
        $this->assertFalse(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'session_revoked')->exists()
        );
    }
}
