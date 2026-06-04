<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tests for the React/Inertia Controlled Drugs Register page.
 * Same convention as the other Feature tests (real DB, existing user).
 */
class ControlledDrugsReactTest extends TestCase
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

    public function test_controlled_drugs_react_requires_auth()
    {
        $this->get('/medication/controlled-drugs-react')->assertRedirect();
    }

    public function test_manager_sees_controlled_drugs_inertia_page()
    {
        $this->assertNotNull($this->managerUser, 'No manager user with a home found in DB');

        $response = $this->actingAs($this->managerUser)->withoutMiddleware()
            ->get('/medication/controlled-drugs-react');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Medication/ControlledDrugs')
            ->has('entries')
            ->has('residents')
            ->has('medsByClient')
            ->has('lastBalances')
        );
    }

    /** The add-entry endpoint must be protected too. */
    public function test_controlled_drugs_react_store_requires_auth()
    {
        $this->post('/medication/controlled-drugs-react', [])->assertRedirect();
    }
}
