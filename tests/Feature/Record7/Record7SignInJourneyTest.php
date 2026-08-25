<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Sections 0.1, 0.2 and 0.3 — organisation, credentials, verification.
 */
class Record7SignInJourneyTest extends Record7TestCase
{
    /* ── 0.1 Organisation ────────────────────────────────────────────────── */

    public function test_the_journey_starts_at_the_organisation_step(): void
    {
        $response = $this->get('/record7/login')
            ->assertOk()
            ->assertSee('Auth\/SignIn', false)
            ->assertSee('&quot;step&quot;:&quot;organisation&quot;', false);

        $this->assertServedRecord7Bundle($response);
    }

    public function test_the_organisation_name_is_matched_ignoring_capitalisation(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => 'omega care group'])
            ->assertRedirect('/record7/login');

        $this->get('/record7/login')->assertSee('&quot;step&quot;:&quot;credentials&quot;', false);
    }

    public function test_the_organisation_name_is_matched_ignoring_repeated_spaces(): void
    {
        // The spec requires runs of whitespace to collapse, not merely trim.
        $this->post('/record7/login/organisation', ['organisation' => '  Omega   Care    Group  '])
            ->assertRedirect('/record7/login');

        $this->get('/record7/login')
            ->assertSee('&quot;step&quot;:&quot;credentials&quot;', false)
            ->assertSee('Omega Care Group', false);
    }

    public function test_an_unrecognised_organisation_reveals_nothing(): void
    {
        $response = $this->from('/record7/login')
            ->post('/record7/login/organisation', ['organisation' => 'Some Other Care Group'])
            ->assertRedirect('/record7/login');

        $response->assertSessionHas('error', 'We could not sign you in with those details.');

        // Still on step one: no credential fields are offered.
        $this->get('/record7/login')->assertSee('&quot;step&quot;:&quot;organisation&quot;', false);
    }

    public function test_the_sign_in_screen_never_lists_organisations(): void
    {
        $content = $this->get('/record7/login')->getContent();

        // Meadow View and Willow House exist in the fixture. Neither, nor any
        // other organisation or house name, may appear before authentication.
        $this->assertStringNotContainsString('Meadow View', $content);
        $this->assertStringNotContainsString('Willow House', $content);
        $this->assertStringNotContainsString('Oakwood', $content);
    }

    public function test_an_unrecognised_organisation_is_audited_without_storing_what_was_typed(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => 'Trying Every Name Ltd']);

        $event = AccessAuditEvent::where('event_type', 'organisation_not_recognised')->latest('id')->first();

        $this->assertNotNull($event);
        $this->assertSame('failure', $event->event_result);
        $this->assertStringNotContainsString('Trying Every Name', json_encode($event->metadata));
    }

    /* ── 0.2 Credentials ─────────────────────────────────────────────────── */

    public function test_credentials_cannot_be_submitted_before_an_organisation_is_chosen(): void
    {
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ])->assertRedirect('/record7/login');

        $this->assertFalse(Auth::guard('record7')->check());
    }

    public function test_a_wrong_password_is_refused_and_audited(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        $this->from('/record7/login')->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => 'not-the-password',
        ])->assertSessionHas('error', 'We could not sign you in with those details.');

        $this->assertFalse(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'sign_in')
                ->where('event_result', 'failure')
                ->where('user_id', $this->user('olivia.carter')->id)
                ->exists()
        );
    }

    public function test_an_unknown_username_gives_the_same_answer_as_a_wrong_password(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        $this->from('/record7/login')->post('/record7/login/credentials', [
            'username' => 'nobody.here',
            'password' => 'anything-at-all',
        ])->assertSessionHas('error', 'We could not sign you in with those details.');
    }

    public function test_repeated_wrong_passwords_lock_the_account(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/record7/login/credentials', [
                'username' => 'olivia.carter',
                'password' => 'wrong-'.$attempt,
            ]);
        }

        $user = $this->user('olivia.carter')->refresh();

        $this->assertSame('security_locked', $user->account_status);
        $this->assertTrue($user->isLockedOut());
    }

    /* ── 0.3 Security verification ───────────────────────────────────────── */

    public function test_a_correct_password_alone_does_not_sign_anyone_in(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ])->assertRedirect('/record7/verify');

        // Partial authentication only.
        $this->assertFalse(Auth::guard('record7')->check());

        // And nothing protected is reachable from it.
        $this->get('/record7')->assertRedirect('/record7/login');
        $this->get('/record7/houses')->assertRedirect('/record7/login');
    }

    public function test_the_verification_screen_cannot_be_reached_without_a_password(): void
    {
        $this->get('/record7/verify')->assertRedirect('/record7/login');
    }

    public function test_a_wrong_verification_code_is_refused_and_audited(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ]);

        $this->post('/record7/verify', ['code' => '000000'])
            ->assertSessionHas('error', 'That code was not correct.');

        $this->assertFalse(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'verification')
                ->where('event_result', 'failure')->exists()
        );
    }

    public function test_the_correct_code_completes_the_sign_in(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ]);

        $this->post('/record7/verify', ['code' => self::CODE])
            ->assertRedirect('/record7/houses');

        $this->assertTrue(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'sign_in')
                ->where('event_result', 'success')
                ->where('user_id', $this->user('olivia.carter')->id)
                ->exists()
        );
    }

    public function test_the_verification_step_expires(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => 'olivia.carter',
            'password' => self::PASSWORDS['olivia.carter'],
        ]);

        // Push the pending window past its limit.
        session(['record7.pending_at' => time() - 400]);

        $this->post('/record7/verify', ['code' => self::CODE])
            ->assertRedirect('/record7/login');

        $this->assertFalse(Auth::guard('record7')->check());
    }
}
