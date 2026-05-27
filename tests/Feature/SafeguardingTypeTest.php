<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Staff\SafeguardingType;
use Illuminate\Support\Facades\Session;

class SafeguardingTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock admin session
        $adminSession = new \stdClass();
        $adminSession->home_id = 1;
        $adminSession->access_type = 'A';
        Session::put('scitsAdminSession', $adminSession);
    }

    public function test_admin_can_view_safeguarding_types()
    {
        $response = $this->get('/admin/safeguarding-type');
        $response->assertStatus(200);
        $response->assertViewHas('incidentType');
    }

    public function test_admin_can_add_and_edit_safeguarding_type()
    {
        // Add
        $response = $this->postJson('/admin/safeguarding-type/add', [
            'type' => 'Test Abuse Type',
            'status' => 1
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $type = SafeguardingType::where('type', 'Test Abuse Type')->first();
        $this->assertNotNull($type);
        $this->assertEquals(1, $type->status);

        // Edit
        $response = $this->postJson('/admin/safeguarding-type/edit', [
            'id' => $type->id,
            'type' => 'Updated Test Abuse Type',
            'status' => 0
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $updatedType = SafeguardingType::find($type->id);
        $this->assertEquals('Updated Test Abuse Type', $updatedType->type);
        $this->assertEquals(0, $updatedType->status);
        
        // Cleanup
        $updatedType->forceDelete();
    }

    public function test_admin_can_change_status_of_safeguarding_type()
    {
        $type = SafeguardingType::create([
            'home_id' => 1,
            'type' => 'Status Test Type',
            'status' => 1
        ]);

        $response = $this->postJson('/admin/safeguarding-type/status-change', [
            'id' => $type->id,
            'status' => 0
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $updatedType = SafeguardingType::find($type->id);
        $this->assertEquals(0, $updatedType->status);

        // Cleanup
        $updatedType->forceDelete();
    }

    public function test_admin_can_delete_safeguarding_type()
    {
        $type = SafeguardingType::create([
            'home_id' => 1,
            'type' => 'Delete Test Type',
            'status' => 1
        ]);

        $response = $this->get('/admin/safeguarding-type/delete/' . $type->id);
        $response->assertStatus(302); // Redirects back

        $deletedType = SafeguardingType::withTrashed()->find($type->id);
        $this->assertNotNull($deletedType->deleted_at);

        // Cleanup
        $deletedType->forceDelete();
    }
}
