<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Home;
use App\AccessLevel;
use App\Models\CompanyDepartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CarerCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $home;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a home
        $this->home = new Home();
        $this->home->title = 'Test Home';
        $this->home->admin_id = 1;
        $this->home->save();

        // Create a department
        $dept = new CompanyDepartment();
        $dept->name = 'Care';
        // status and home_id might not be fillable, set explicitly
        $dept->status = 1;
        $dept->save();

        // Create access levels
        $al = new AccessLevel();
        $al->name = 'Staff';
        $al->home_id = $this->home->id;
        $al->access_rights = '1,2,3';
        $al->save();

        // Create an admin user to act as
        $this->adminUser = new User();
        $this->adminUser->name = 'Admin User';
        $this->adminUser->user_name = 'admin';
        $this->adminUser->email = 'admin@example.com';
        $this->adminUser->password = bcrypt('password');
        $this->adminUser->home_id = $this->home->id;
        $this->adminUser->user_type = 'A';
        $this->adminUser->is_deleted = 0;
        $this->adminUser->status = 1;
        $this->adminUser->save();

        // Mock the session
        Session::put('scitsAdminSession', $this->adminUser);
    }

    /** @test */
    public function can_create_carer()
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/add-staff-user', [
                'staff_name' => 'John Doe',
                'staff_user_name' => 'johndoe',
                'staff_phone_no' => '1234567890',
                'staff_email' => 'john@example.com',
                'job_title' => 'Carer',
                'department' => 1,
                'status' => 1,
                'hourly_rate' => 15.50,
                'employment_type' => 'full_time',
                'holiday_entitlement' => 20,
                'date_of_joining' => '2023-01-01',
                'date_of_leaving' => '',
                'dbs_certificate_number' => 'DBS123',
                'dbs_expiry_date' => '2025-01-01',
                'qualifications' => [],
                'emergency_contact' => []
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('user', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_deleted' => 0
        ]);
    }

    /** @test */
    public function can_read_carer_list()
    {
        $carer = new User();
        $carer->name = 'Jane Doe';
        $carer->user_name = 'janedoe';
        $carer->email = 'jane@example.com';
        $carer->home_id = $this->home->id;
        $carer->status = 1;
        $carer->is_deleted = 0;
        $carer->save();

        $response = $this->actingAs($this->adminUser)
            ->post('/carer/getStaffByStatus', [
                'type' => 'allCarerActibity',
                'search' => '',
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Jane Doe']);
    }

    /** @test */
    public function can_update_carer()
    {
        $carer = new User();
        $carer->name = 'Old Name';
        $carer->email = 'old@example.com';
        $carer->home_id = $this->home->id;
        $carer->is_deleted = 0;
        $carer->status = 1;
        $carer->save();

        $response = $this->actingAs($this->adminUser)
            ->put("/carer-update/{$carer->id}", [
                'staff_name' => 'New Name',
                'staff_email' => 'new@example.com',
                'staff_phone_no' => '0987654321',
                'status' => 1,
                'qualifications' => [] 
            ]);

        $response->assertStatus(302);
        
        // Refresh the model from database
        $carer->refresh();
        $this->assertEquals('New Name', $carer->name);
        $this->assertEquals('new@example.com', $carer->email);
    }

    /** @test */
    public function can_delete_carer()
    {
        $carer = new User();
        $carer->name = 'To Be Deleted';
        $carer->email = 'delete@example.com';
        $carer->home_id = $this->home->id;
        $carer->is_deleted = 0;
        $carer->status = 1;
        $carer->save();

        $response = $this->actingAs($this->adminUser)
            ->post('/carer/delete', [
                'carer_id' => $carer->id,
                '_token' => csrf_token()
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user', [
            'id' => $carer->id,
            'is_deleted' => 1
        ]);
    }
}
