<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tests for the React/Inertia Shift Handover page.
 */
class ShiftHandoverReactTest extends TestCase
{
    protected $managerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->managerUser = User::whereIn('user_type', ['M', 'CM', 'A', 'O'])
            ->whereNotNull('home_id')
            ->where('home_id', '!=', '')
            ->where('is_deleted', '0')
            ->first();
    }

    public function test_shift_handover_react_requires_auth()
    {
        $this->get('/medication/shift-handover-react')->assertRedirect();
    }

    public function test_manager_sees_shift_handover_inertia_page()
    {
        $this->assertNotNull($this->managerUser, 'No manager user with a home found in DB');

        $response = $this->actingAs($this->managerUser)->withoutMiddleware()
            ->get('/medication/shift-handover-react');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Medication/ShiftHandover')
            ->has('handovers')
            ->has('serviceUsers')
            ->has('selectedDate')
        );
    }

    public function test_shift_handover_react_acknowledge_requires_auth()
    {
        $this->post('/medication/shift-handover-react/1/acknowledge')->assertRedirect();
    }

    public function test_shift_handover_react_store_requires_auth()
    {
        $this->post('/medication/shift-handover-react', [])->assertRedirect();
    }
}
