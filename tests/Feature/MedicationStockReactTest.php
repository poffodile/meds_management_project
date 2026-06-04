<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tests for the React/Inertia Medication Stock pilot page.
 * Follows the project convention: run against the real DB, use an existing user.
 */
class MedicationStockReactTest extends TestCase
{
    protected $managerUser;
    protected $homeId;

    protected function setUp(): void
    {
        parent::setUp();

        // A manager-level user (M/CM/A/O) with a home — same split the app uses.
        $this->managerUser = User::whereIn('user_type', ['M', 'CM', 'A', 'O'])
            ->whereNotNull('home_id')
            ->where('home_id', '!=', '')
            ->where('is_deleted', '0')
            ->first();

        if ($this->managerUser) {
            $this->homeId = (int) explode(',', $this->managerUser->home_id)[0];
        }
    }

    /** Guests must be redirected to login. */
    public function test_stock_react_requires_auth()
    {
        $this->get('/medication/stock-react')->assertRedirect();
    }

    /** A manager gets the Inertia page with the expected data props. */
    public function test_manager_sees_stock_react_inertia_page()
    {
        $this->assertNotNull($this->managerUser, 'No manager user with a home found in DB');

        // withoutMiddleware: this app uses custom session auth middleware that actingAs
        // alone doesn't satisfy (same convention as the other Feature tests).
        $response = $this->actingAs($this->managerUser)->withoutMiddleware()->get('/medication/stock-react');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Medication/Stock')
            ->has('meds')
            ->has('transactions')
            ->has('stats')
        );
    }

    /** The stock-adjust endpoint must be protected too. */
    public function test_stock_react_adjust_requires_auth()
    {
        $this->post('/medication/stock-react/adjust', [])->assertRedirect();
    }
}
