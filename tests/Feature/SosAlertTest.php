<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\staffManagement\sosAlert;

class SosAlertTest extends TestCase
{
    private function loginAsAdmin()
    {
        $admin = User::where('user_type', 'A')->where('is_deleted', 0)->first();
        $this->actingAs($admin);
        return $admin;
    }

    private function loginAsStaff()
    {
        $staff = User::where('user_type', 'N')->where('is_deleted', 0)->first();
        if (!$staff) {
            $this->markTestSkipped('No staff user available');
        }
        $this->actingAs($staff);
        return $staff;
    }

    private function getAdminHomeId($admin)
    {
        return (int) explode(',', $admin->home_id)[0];
    }

    // === 4a: Happy Path ===

    public function test_trigger_creates_alert()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/trigger', [
            'message' => 'Test emergency',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sos_alerts', [
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'message' => 'Test emergency',
            'status' => 1,
        ]);
    }

    public function test_list_returns_alerts()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/list');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['data']);
    }

    public function test_acknowledge_updates_status()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        $alert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'location' => 'Test',
            'status' => 1,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/acknowledge', [
            'id' => $alert->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $alert->refresh();
        $this->assertEquals(2, $alert->status);
        $this->assertEquals($admin->id, $alert->acknowledged_by);
    }

    public function test_resolve_updates_status()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        $alert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'location' => 'Test',
            'status' => 2,
            'acknowledged_by' => $admin->id,
            'acknowledged_at' => now(),
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/resolve', [
            'id' => $alert->id,
            'notes' => 'All clear',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $alert->refresh();
        $this->assertEquals(3, $alert->status);
        $this->assertEquals($admin->id, $alert->resolved_by);
    }

    // === 4b: Multi-Step Flow ===

    public function test_full_flow_trigger_acknowledge_resolve()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        // Trigger
        $response = $this->withoutMiddleware()->post('/roster/sos-alert/trigger', [
            'message' => 'Flow test emergency',
        ]);
        $response->assertJson(['success' => true]);
        $alertId = $response->json('data.id');

        // Verify in list
        $listResp = $this->withoutMiddleware()->post('/roster/sos-alert/list');
        $found = collect($listResp->json('data'))->firstWhere('id', $alertId);
        $this->assertNotNull($found);
        $this->assertEquals(1, $found['status']);

        // Acknowledge
        $ackResp = $this->withoutMiddleware()->post('/roster/sos-alert/acknowledge', ['id' => $alertId]);
        $ackResp->assertJson(['success' => true]);
        $this->assertEquals(2, sosAlert::find($alertId)->status);

        // Resolve
        $resResp = $this->withoutMiddleware()->post('/roster/sos-alert/resolve', ['id' => $alertId, 'notes' => 'Done']);
        $resResp->assertJson(['success' => true]);
        $this->assertEquals(3, sosAlert::find($alertId)->status);
    }

    // === 4c: IDOR Tests ===

    public function test_list_does_not_leak_cross_home_alerts()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        $crossHomeAlert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => 99999,
            'location' => 'Cross home',
            'status' => 1,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/list');
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($crossHomeAlert->id, $ids);

        $crossHomeAlert->delete();
    }

    public function test_acknowledge_rejects_cross_home_alert()
    {
        $admin = $this->loginAsAdmin();

        $crossHomeAlert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => 99999,
            'location' => 'Cross home',
            'status' => 1,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/acknowledge', [
            'id' => $crossHomeAlert->id,
        ]);

        $response->assertStatus(404);
        $crossHomeAlert->refresh();
        $this->assertEquals(1, $crossHomeAlert->status);

        $crossHomeAlert->delete();
    }

    public function test_resolve_rejects_cross_home_alert()
    {
        $admin = $this->loginAsAdmin();

        $crossHomeAlert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => 99999,
            'location' => 'Cross home',
            'status' => 1,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/resolve', [
            'id' => $crossHomeAlert->id,
        ]);

        $response->assertStatus(404);
        $crossHomeAlert->refresh();
        $this->assertEquals(1, $crossHomeAlert->status);

        $crossHomeAlert->delete();
    }

    // === 4c: Access Control ===

    public function test_staff_cannot_acknowledge()
    {
        $staff = $this->loginAsStaff();
        $homeId = (int) explode(',', $staff->home_id)[0];

        $alert = sosAlert::create([
            'staff_id' => $staff->id,
            'home_id' => $homeId,
            'location' => 'Test',
            'status' => 1,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/acknowledge', [
            'id' => $alert->id,
        ]);

        $response->assertStatus(403);
        $alert->delete();
    }

    public function test_staff_cannot_resolve()
    {
        $staff = $this->loginAsStaff();
        $homeId = (int) explode(',', $staff->home_id)[0];

        $alert = sosAlert::create([
            'staff_id' => $staff->id,
            'home_id' => $homeId,
            'location' => 'Test',
            'status' => 2,
        ]);

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/resolve', [
            'id' => $alert->id,
        ]);

        $response->assertStatus(403);
        $alert->delete();
    }

    // === 4d: Security Payload Tests ===

    public function test_xss_in_message_stored_safely()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->withoutMiddleware()->post('/roster/sos-alert/trigger', [
            'message' => '<script>alert("xss")</script>',
        ]);

        $response->assertJson(['success' => true]);
        $alertId = $response->json('data.id');

        $alert = sosAlert::find($alertId);
        $this->assertEquals('<script>alert("xss")</script>', $alert->message);
    }

    public function test_trigger_rejects_oversized_message()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->withoutMiddleware()->postJson('/roster/sos-alert/trigger', [
            'message' => str_repeat('A', 2001),
        ]);

        $response->assertStatus(422);
    }

    public function test_acknowledge_rejects_non_integer_id()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->withoutMiddleware()->postJson('/roster/sos-alert/acknowledge', [
            'id' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    public function test_resolve_rejects_non_integer_id()
    {
        $admin = $this->loginAsAdmin();

        $response = $this->withoutMiddleware()->postJson('/roster/sos-alert/resolve', [
            'id' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    public function test_trigger_with_middleware()
    {
        $admin = $this->loginAsAdmin();
        $homeId = $this->getAdminHomeId($admin);

        $this->withSession([]);
        $token = csrf_token();

        $admin->session_token = $token;
        $admin->save();

        $response = $this->post('/roster/sos-alert/trigger', [
            '_token' => $token,
            'message' => 'Middleware test emergency',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_manager_bypass_middleware()
    {
        $manager = User::where('user_type', 'M')->where('is_deleted', 0)->first();
        if (!$manager) {
            $this->markTestSkipped('No manager user available');
        }

        $this->actingAs($manager);

        $this->withSession([]);
        $token = csrf_token();

        $manager->session_token = $token;
        $manager->save();

        $response = $this->post('/roster/sos-alert/trigger', [
            '_token' => $token,
            'message' => 'Manager bypass test',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // === 5: API Endpoints Tests ===

    public function test_api_trigger_creates_alert()
    {
        $admin = User::where('user_type', 'A')->where('is_deleted', 0)->first();
        $homeId = $this->getAdminHomeId($admin);

        $response = $this->postJson('/api/staff/sos-alert/trigger', [
            'staff_id' => $admin->id,
            'message' => 'API test emergency',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('sos_alerts', [
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'message' => 'API test emergency',
        ]);
    }

    public function test_api_list_returns_alerts()
    {
        $admin = User::where('user_type', 'A')->where('is_deleted', 0)->first();
        $homeId = $this->getAdminHomeId($admin);

        // Ensure at least one alert exists
        $alert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'location' => 'API Test Location',
            'message' => 'API Temp Alert',
            'status' => 1,
        ]);

        $response = $this->postJson('/api/staff/sos-alert/list', [
            'home_id' => $homeId,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'staff_id', 'home_id', 'message', 'status']
            ]
        ]);

        $data = $response->json('data');
        $found = false;
        foreach ($data as $item) {
            if ($item['id'] == $alert->id) {
                $this->assertEquals('Active', $item['status']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        $alert->delete();
    }

    public function test_api_acknowledge_and_resolve()
    {
        $admin = User::where('user_type', 'A')->where('is_deleted', 0)->first();
        $homeId = $this->getAdminHomeId($admin);

        $alert = sosAlert::create([
            'staff_id' => $admin->id,
            'home_id' => $homeId,
            'location' => 'Test API Location',
            'status' => 1,
        ]);

        // Acknowledge
        $response = $this->postJson('/api/staff/sos-alert/acknowledge', [
            'id' => $alert->id,
            'staff_id' => $admin->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Alert acknowledged.']);
        $this->assertEquals(2, sosAlert::find($alert->id)->status);

        // Resolve
        $response = $this->postJson('/api/staff/sos-alert/resolve', [
            'id' => $alert->id,
            'staff_id' => $admin->id,
            'notes' => 'API resolve notes',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Alert resolved.']);
        $this->assertEquals(3, sosAlert::find($alert->id)->status);

        $alert->delete();
    }
}
