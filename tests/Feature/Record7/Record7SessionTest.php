<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\LoginSession;
use App\Services\Record7\SessionManager;
use Illuminate\Support\Facades\Auth;

/**
 * Section 0.9 — lock screen and sign out.
 *
 * The switching half of 0.9 lives in Record7HouseAccessTest.
 */
class Record7SessionTest extends Record7TestCase
{
    public function test_locking_keeps_the_session_and_the_house(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $this->post('/record7/lock')->assertRedirect('/record7/locked');

        // Still signed in, still scoped to the house.
        $this->assertTrue(Auth::guard('record7')->check());
        $this->assertSame($this->house('Oakwood House')->id, session(SessionManager::SERVICE));

        $session = LoginSession::latest('id')->first();
        $this->assertSame('locked', $session->status);
        $this->assertNotNull($session->locked_at);
    }

    public function test_a_locked_screen_blocks_every_protected_page(): void
    {
        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->post('/record7/lock');

        $this->get('/record7')->assertRedirect('/record7/locked');
        $this->get('/record7/access-audit')->assertRedirect('/record7/locked');
    }

    public function test_the_lock_screen_names_the_house_but_reveals_nothing_else(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/lock');

        $content = $this->get('/record7/locked')->assertOk()->getContent();

        $this->assertStringContainsString('Oakwood House', $content);
        // No other house, and no permission read-out, while locked.
        $this->assertStringNotContainsString('Rosewood House', $content);
        $this->assertStringNotContainsString('administer_medication', $content);
    }

    public function test_the_correct_password_unlocks_without_asking_for_verification_again(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/lock');

        $this->post('/record7/unlock', ['password' => self::PASSWORDS['olivia.carter']])
            ->assertRedirect('/record7');

        $this->get('/record7')->assertOk();
        $this->assertSame('active', LoginSession::latest('id')->first()->status);

        $this->assertTrue(AccessAuditEvent::where('event_type', 'session_unlocked')->exists());
    }

    public function test_a_wrong_password_at_the_lock_screen_is_refused_and_audited(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/lock');

        $this->post('/record7/unlock', ['password' => 'not-the-password'])
            ->assertSessionHas('error');

        $this->get('/record7')->assertRedirect('/record7/locked');

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'unlock')
                ->where('event_result', 'failure')->exists()
        );
    }

    public function test_repeated_wrong_passwords_at_the_lock_screen_end_the_session(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/lock');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/record7/unlock', ['password' => 'wrong-'.$attempt]);
        }

        $this->assertFalse(Auth::guard('record7')->check());
    }

    public function test_the_screen_locks_itself_after_a_few_idle_minutes(): void
    {
        $this->signInAt('noah.williams', 'Oakwood House');
        $this->get('/record7')->assertOk();

        // A tablet left on a trolley.
        session([SessionManager::LAST_ACTIVITY => time() - (SessionManager::IDLE_LOCK_MINUTES * 60 + 30)]);

        $this->get('/record7')->assertRedirect('/record7/locked');

        // Locked, not signed out: the identity survives.
        $this->assertTrue(Auth::guard('record7')->check());
    }

    public function test_a_long_idle_period_ends_the_session_entirely(): void
    {
        $this->signInAt('noah.williams', 'Oakwood House');

        session([SessionManager::LAST_ACTIVITY => time() - (SessionManager::IDLE_END_MINUTES * 60 + 60)]);

        $this->get('/record7')->assertRedirect('/record7/login');
        $this->assertFalse(Auth::guard('record7')->check());
        $this->assertSame('expired', LoginSession::latest('id')->first()->status);
    }

    public function test_signing_out_ends_the_session_and_clears_the_house(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');

        $this->post('/record7/sign-out')->assertRedirect('/record7/login');

        $this->assertFalse(Auth::guard('record7')->check());
        $this->assertNull(session(SessionManager::SERVICE));
        $this->assertSame('signed_out', LoginSession::latest('id')->first()->status);

        $this->assertTrue(AccessAuditEvent::where('event_type', 'sign_out')->exists());
    }

    public function test_signing_out_from_the_lock_screen_works(): void
    {
        $this->signInAt('olivia.carter', 'Oakwood House');
        $this->post('/record7/lock');

        $this->post('/record7/sign-out')->assertRedirect('/record7/login');
        $this->assertFalse(Auth::guard('record7')->check());
    }
}
