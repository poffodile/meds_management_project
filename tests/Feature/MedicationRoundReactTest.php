<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tests for the React/Inertia Medication Round page.
 */
class MedicationRoundReactTest extends TestCase
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

    public function test_medication_round_react_requires_auth()
    {
        $this->get('/medication/medication-round-react')->assertRedirect();
    }

    public function test_manager_sees_medication_round_inertia_page()
    {
        $this->assertNotNull($this->managerUser, 'No manager user with a home found in DB');

        $response = $this->actingAs($this->managerUser)->withoutMiddleware()
            ->get('/medication/medication-round-react');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Medication/MedicationRound')
            ->has('rounds')
            ->has('grid')
            ->has('date')
            ->has('currentRound')
        );
    }

    public function test_medication_round_react_record_requires_auth()
    {
        $this->post('/medication/medication-round-react/record', [])->assertRedirect();
    }
}
