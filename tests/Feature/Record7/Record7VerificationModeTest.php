<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\VerificationEvent;
use App\Services\Record7\AuthenticationService;
use App\Services\Record7\VerificationPolicy;
use Illuminate\Support\Facades\Auth;

/**
 * The verification step is optional, and safely so.
 *
 * THREE MODES
 *   off         the prototype default — the step is not in the journey at all,
 *               so normal UI testing runs organisation, credentials, house,
 *               Today
 *   test        the screen appears and accepts the fixed fictional code, under
 *               a plain "not real security" label
 *   production  a real provider is required; none is integrated, so it refuses
 *               everything
 *
 * The important property is the last one: 'off' must be impossible on a live
 * system, or the switch that makes testing convenient becomes the switch that
 * turns security off in production.
 */
class Record7VerificationModeTest extends Record7TestCase
{
    private function auth(): AuthenticationService
    {
        return app(AuthenticationService::class);
    }

    private function policy(): VerificationPolicy
    {
        return app(VerificationPolicy::class);
    }

    /** Sign in without the verification step, as the prototype journey does. */
    private function signInWithoutVerification(string $username): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => $username,
            'password' => self::PASSWORDS[$username],
        ]);
    }

    /* ── off: the normal test journey ────────────────────────────────────── */

    public function test_with_verification_off_the_journey_is_organisation_credentials_house(): void
    {
        config(['record7.mfa.mode' => 'off']);

        // Credentials go straight to house selection. No verification screen.
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ])->assertRedirect('/record7/houses');

        $this->assertTrue(Auth::guard('record7')->check());

        // And on to Today.
        $this->post('/record7/houses', ['house_id' => $this->house('Oakwood House')->id])
            ->assertRedirect('/record7');

        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);
    }

    public function test_with_verification_off_even_an_elevated_account_goes_straight_through(): void
    {
        config(['record7.mfa.mode' => 'off']);

        // Daniel is elevated, but the step is not in the journey at all.
        $this->assertTrue($this->policy()->hasElevatedAccess($this->user('daniel.evans')));
        $this->assertNull($this->policy()->reasonToVerify($this->user('daniel.evans'), request()));

        $this->signInWithoutVerification('daniel.evans');

        $this->assertTrue(Auth::guard('record7')->check());
    }

    public function test_skipping_the_step_is_still_recorded(): void
    {
        config(['record7.mfa.mode' => 'off']);

        $this->signInWithoutVerification('olivia.carter');

        $this->assertTrue(
            VerificationEvent::where('user_id', $this->user('olivia.carter')->id)
                ->where('reason', 'step_disabled')
                ->where('result', 'skipped')
                ->exists(),
            'A waved-through sign-in must still leave a record of why.'
        );
    }

    public function test_with_verification_off_no_code_is_offered_anywhere(): void
    {
        config(['record7.mfa.mode' => 'off']);

        $this->assertNull($this->auth()->prototypeCode());
        $this->assertFalse($this->auth()->verificationStepEnabled());
    }

    /* ── test: the screen, clearly labelled ──────────────────────────────── */

    public function test_in_test_mode_the_verification_screen_appears(): void
    {
        config(['record7.mfa.mode' => 'test']);

        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ])->assertRedirect('/record7/verify');

        // Not signed in until the code is given.
        $this->assertFalse(Auth::guard('record7')->check());

        $this->get('/record7/verify')
            ->assertOk()
            ->assertSee('&quot;prototypeCode&quot;:&quot;'.self::CODE.'&quot;', false);

        $this->post('/record7/verify', ['code' => self::CODE])
            ->assertRedirect('/record7/houses');

        $this->assertTrue(Auth::guard('record7')->check());
    }

    public function test_test_mode_is_the_only_mode_that_accepts_the_fictional_code(): void
    {
        foreach (['off', 'production'] as $mode) {
            config(['record7.mfa.mode' => $mode]);

            $this->assertNull(
                $this->auth()->prototypeCode(),
                'The fictional code must not exist in '.$mode.' mode'
            );

            $this->assertFalse(
                $this->auth()->verifyCode($this->user('olivia.carter'), self::CODE),
                'The fictional code must be refused in '.$mode.' mode'
            );
        }

        config(['record7.mfa.mode' => 'test']);

        $this->assertSame(self::CODE, $this->auth()->prototypeCode());
    }

    /* ── production: fails closed ────────────────────────────────────────── */

    public function test_production_mode_demands_verification_and_then_refuses_it(): void
    {
        config(['record7.mfa.mode' => 'production']);

        // No provider is integrated, so verification is demanded...
        $this->assertFalse($this->auth()->hasRealVerificationProvider());
        $this->assertSame(
            'no_verification_provider',
            $this->policy()->reasonToVerify($this->user('olivia.carter'), request())
        );

        // ...and then nothing satisfies it.
        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), self::CODE));
        $this->assertFalse($this->auth()->verifyCode($this->user('olivia.carter'), '000000'));
    }

    public function test_production_mode_does_not_sign_anybody_in(): void
    {
        config(['record7.mfa.mode' => 'production']);

        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ])->assertRedirect('/record7/verify');

        $this->post('/record7/verify', ['code' => self::CODE]);

        $this->assertFalse(
            Auth::guard('record7')->check(),
            'An unfinished security control must fail closed, not wave people through.'
        );
    }

    /* ── The switch cannot be abused ─────────────────────────────────────── */

    public function test_the_production_environment_forces_production_mode(): void
    {
        // This is the property that matters most: a convenience switch for
        // local testing must never be able to disable verification on a live
        // system, however the configuration is left.
        config(['record7.mfa.mode' => 'off']);
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertSame('production', $this->auth()->verificationMode());
        $this->assertTrue($this->auth()->verificationStepEnabled());
        $this->assertNull($this->auth()->prototypeCode());

        $this->assertSame(
            'no_verification_provider',
            $this->policy()->reasonToVerify($this->user('olivia.carter'), request())
        );
    }

    public function test_an_unrecognised_mode_falls_back_to_off_rather_than_guessing(): void
    {
        config(['record7.mfa.mode' => 'something-else']);

        $this->assertSame('off', $this->auth()->verificationMode());
    }
}
