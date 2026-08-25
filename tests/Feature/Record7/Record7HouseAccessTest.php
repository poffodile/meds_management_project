<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\Service;
use Illuminate\Support\Facades\Auth;

/**
 * Section 0.4 and the switching half of 0.9 — choosing and changing house.
 */
class Record7HouseAccessTest extends Record7TestCase
{
    public function test_one_house_opens_automatically(): void
    {
        // Noah Williams holds Oakwood House only.
        $this->signIn('noah.williams');

        $this->get('/record7/houses')->assertRedirect('/record7');
        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);
    }

    public function test_several_houses_require_a_choice(): void
    {
        // Olivia Carter holds Oakwood House and Rosewood House.
        $this->signIn('olivia.carter');

        $this->get('/record7/houses')
            ->assertOk()
            ->assertSee('Oakwood House', false)
            ->assertSee('Rosewood House', false);

        // Nothing clinical is reachable until one is picked.
        $this->get('/record7')->assertRedirect('/record7/houses');
    }

    public function test_an_inactive_house_is_never_offered(): void
    {
        // Willow House is inactive in the fixture.
        $this->signIn('priya.nair');

        $content = $this->get('/record7/houses')->getContent();

        $this->assertStringNotContainsString('Willow House', $content);
        $this->assertStringContainsString('Meadow View', $content);
    }

    public function test_an_inactive_house_cannot_be_entered_by_id(): void
    {
        $this->signIn('priya.nair');

        $willow = Service::where('name', 'Willow House')->firstOrFail();

        // Refused with an inline message rather than a bare 403 page, but
        // refused all the same: the house is never opened.
        $this->post('/record7/houses', ['house_id' => $willow->id])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull(session(\App\Services\Record7\SessionManager::SERVICE));
    }

    public function test_a_house_the_account_does_not_hold_is_refused_and_audited(): void
    {
        // Noah holds Oakwood only; Rosewood must be refused.
        $this->signIn('noah.williams');

        $this->post('/record7/houses', ['house_id' => $this->house('Rosewood House')->id])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Refused means refused: Rosewood is not the open house.
        $this->assertNotSame(
            $this->house('Rosewood House')->id,
            session(\App\Services\Record7\SessionManager::SERVICE)
        );

        $event = AccessAuditEvent::where('event_type', 'house_selection')
            ->where('event_result', 'denied')->latest('id')->first();

        $this->assertNotNull($event);
        $this->assertSame('high', $event->risk_level);
    }

    public function test_choosing_a_house_is_recorded(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'house_selected')
                ->where('event_result', 'success')
                ->where('service_id', $this->house('Rosewood House')->id)
                ->exists()
        );
    }

    public function test_a_manager_can_switch_between_their_houses(): void
    {
        // Daniel Evans manages Oakwood House and Rosewood House.
        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);

        $this->post('/record7/houses', ['house_id' => $this->house('Rosewood House')->id])
            ->assertRedirect('/record7');

        $this->get('/record7')->assertOk()->assertSee('Rosewood House', false);

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'house_switched')->exists(),
            'Switching house must be recorded distinctly from opening one.'
        );
    }

    public function test_an_account_with_no_active_house_is_signed_out_safely(): void
    {
        // Ethan Cole is suspended and cannot reach this point at all, so the
        // condition is produced by suspending Olivia's only usable access.
        $this->signIn('olivia.carter');

        $this->user('olivia.carter')->serviceAccess()->update(['status' => 'suspended']);

        $this->get('/record7/houses')
            ->assertRedirect('/record7/login')
            ->assertSessionHas('error');

        $this->assertFalse(Auth::guard('record7')->check());

        $this->assertTrue(
            AccessAuditEvent::where('event_type', 'no_active_house')->exists()
        );
    }

    public function test_house_access_outside_its_date_window_is_not_offered(): void
    {
        // Grace Taylor's Rosewood placement is time limited. Move it to the
        // past and the house must disappear.
        $this->user('grace.taylor')->serviceAccess()
            ->update(['starts_at' => now()->subMonths(2), 'ends_at' => now()->subDay()]);

        $this->signIn('grace.taylor');

        $this->get('/record7/houses')->assertRedirect('/record7/login');
        $this->assertFalse(Auth::guard('record7')->check());
    }
}
