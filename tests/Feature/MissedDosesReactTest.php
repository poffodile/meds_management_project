<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tests for the React/Inertia Missed Doses review page.
 */
class MissedDosesReactTest extends TestCase
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

    public function test_missed_doses_react_requires_auth()
    {
        $this->get('/medication/missed-doses-react')->assertRedirect();
    }

    public function test_manager_sees_missed_doses_inertia_page()
    {
        $this->assertNotNull($this->managerUser, 'No manager user with a home found in DB');

        $response = $this->actingAs($this->managerUser)->withoutMiddleware()
            ->get('/medication/missed-doses-react');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Medication/MissedDoses')
            ->has('items')
            ->has('stats')
            ->has('date')
            ->has('statusFilter')
        );
    }

    public function test_missed_doses_react_resolve_requires_auth()
    {
        $this->post('/medication/missed-doses-react/resolve', [])->assertRedirect();
    }
}
