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
}
