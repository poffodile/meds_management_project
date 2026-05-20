<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\Training;
use App\Models\StaffTraining;

class StaffTrainingTest extends TestCase
{
    protected $adminUser;
    protected $staffUser;

    protected function setUp(): void
    {
        parent::setUp();
        // Admin user (type=A) for write operations
        $this->adminUser = User::where('is_deleted', 0)->where('status', 1)->where('user_type', 'A')->first();
        // Normal staff user (type=N) for role-based access tests
        $this->staffUser = User::where('is_deleted', 0)->where('status', 1)->where('user_type', 'N')->first();
    }

    // --- Authentication tests ---

    /** @test */
    public function unauthenticated_user_cannot_access_training_list()
    {
        $response = $this->get('/staff/trainings');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_training_view()
    {
        $response = $this->get('/staff/training/view/1');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function unauthenticated_user_cannot_add_training()
    {
        $response = $this->post('/staff/training/add', [
            'name' => 'Test', 'training_provider' => 'P', 'desc' => 'D', 'training_date' => '2027-06-15'
        ]);
        $response->assertRedirect('/login');
    }

    // --- Authenticated access tests ---

    /** @test */
    public function authenticated_user_can_view_training_list()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/staff/trainings');

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.staffManagement.training_listing');
    }

    /** @test */
    public function authenticated_user_can_view_training_detail()
    {
        $homeId = explode(',', $this->adminUser->home_id)[0];
        $training = Training::where('home_id', $homeId)->where('is_deleted', 0)->first();

        if (!$training) {
            $this->markTestSkipped('No training records for this home.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/staff/training/view/' . $training->id);

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.staffManagement.training_view');
    }

    // --- Validation tests ---

    /** @test */
    public function add_training_requires_name()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->post('/staff/training/add', [
                'training_provider' => 'Provider',
                'desc' => 'Description',
                'training_date' => '2027-06-15',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function add_training_validates_date_format()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->post('/staff/training/add', [
                'name' => 'Test Training',
                'training_provider' => 'Provider',
                'desc' => 'Description',
                'training_date' => 'not-a-date',
            ]);

        $response->assertSessionHasErrors('training_date');
    }

    /** @test */
    public function edit_training_requires_all_fields()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->post('/staff/training/edit_fields', [
                'training_id' => 1,
                // Missing required fields
            ]);

        $response->assertSessionHasErrors(['name', 'training_provider', 'desc', 'training_date']);
    }

    // --- Multi-tenancy tests ---

    /** @test */
    public function cannot_view_training_from_different_home()
    {
        $homeId = explode(',', $this->adminUser->home_id)[0];

        // Find a training that belongs to a different home
        $otherTraining = Training::where('home_id', '!=', $homeId)->where('is_deleted', 0)->first();

        if (!$otherTraining) {
            $this->markTestSkipped('No training from other home available to test.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/staff/training/view/' . $otherTraining->id);

        // Should redirect away since training not found for this home
        $response->assertRedirect('/staff/trainings');
    }

    /** @test */
    public function view_fields_returns_false_for_other_homes_training()
    {
        $homeId = explode(',', $this->adminUser->home_id)[0];
        $otherTraining = Training::where('home_id', '!=', $homeId)->where('is_deleted', 0)->first();

        if (!$otherTraining) {
            $this->markTestSkipped('No training from other home available to test.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/staff/training/view_fields/' . $otherTraining->id);

        $response->assertJson(['response' => false]);
    }

    // --- Role-based access tests (#2) ---

    /** @test */
    public function non_admin_cannot_add_training()
    {
        if (!$this->staffUser) {
            $this->markTestSkipped('No staff user available to test.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->staffUser)
            ->post('/staff/training/add', [
                'name' => 'Test',
                'training_provider' => 'P',
                'desc' => 'D',
                'training_date' => '2027-06-15',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only administrators can add trainings.');
    }

    /** @test */
    public function non_admin_cannot_delete_training()
    {
        if (!$this->staffUser) {
            $this->markTestSkipped('No staff user available to test.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->staffUser)
            ->post('/staff/training/delete/1');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only administrators can delete trainings.');
    }

    /** @test */
    public function non_admin_cannot_assign_staff()
    {
        if (!$this->staffUser) {
            $this->markTestSkipped('No staff user available to test.');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->staffUser)
            ->post('/staff/training/staff/add', [
                'training_id' => 1,
                'user_ids' => [1],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only administrators can assign staff to trainings.');
    }

    // --- Delete tests ---

    /** @test */
    public function delete_requires_post_method()
    {
        // GET should return 405 Method Not Allowed
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/staff/training/delete/1');

        $response->assertStatus(405);
    }
}
