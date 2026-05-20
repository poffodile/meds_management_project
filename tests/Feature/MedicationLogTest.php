<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\medicationLog;
use Illuminate\Support\Facades\DB;

class MedicationLogTest extends TestCase
{
    protected $adminUser;
    protected $homeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_type', 'A')->where('is_deleted', '0')->first();
        if ($this->adminUser) {
            $this->homeId = (int) explode(',', $this->adminUser->home_id)[0];
        }
    }

    private function getClientId(): ?int
    {
        return DB::table('su_risk')->where('home_id', $this->homeId)->value('service_user_id');
    }

    private function createTestLog($homeId = null): ?medicationLog
    {
        $clientId = $this->getClientId();
        if (!$clientId) return null;

        return medicationLog::create([
            'home_id' => $homeId ?? $this->homeId,
            'user_id' => $this->adminUser->id,
            'client_id' => $clientId,
            'medication_name' => 'Test Paracetamol',
            'dosage' => '500mg',
            'frequesncy' => 'Twice daily',
            'administrator_date' => now(),
            'status' => 1,
            'is_deleted' => 0,
        ]);
    }

    private function validPayload(): array
    {
        return [
            'medication_name' => 'Paracetamol',
            'dosage' => '500mg',
            'frequesncy' => 'Twice daily',
            'administrator_date' => '2026-04-20T10:00',
            'status' => '1',
            'witnessed_by' => 'Nurse Jane',
            'notes' => 'Administered with water',
            'side_effect' => '',
            'client_id' => $this->getClientId(),
        ];
    }

    // --- AUTH TESTS ---

    public function test_medication_log_list_requires_auth()
    {
        $response = $this->postJson('/roster/client/medication-log-list', ['client_id' => 1]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_medication_log_save_requires_auth()
    {
        $response = $this->postJson('/roster/client/medication-log-save', $this->validPayload());
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_medication_log_delete_requires_auth()
    {
        $response = $this->postJson('/roster/client/medication-log-delete', ['id' => 1]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    // --- VALIDATION TESTS ---

    public function test_save_rejects_missing_required_fields()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', []);

        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_save_rejects_invalid_status()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $payload = $this->validPayload();
        $payload['status'] = '99';

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    public function test_save_rejects_oversized_medication_name()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $payload = $this->validPayload();
        $payload['medication_name'] = str_repeat('A', 300);

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => false]);
    }

    // --- CRUD TESTS ---

    public function test_list_returns_success_with_home_id_filtering()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-list', [
                'client_id' => $this->getClientId(),
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_save_creates_medication_log()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $payload = $this->validPayload();

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $log = medicationLog::where('medication_name', 'Paracetamol')
            ->where('home_id', $this->homeId)
            ->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->homeId, $log->home_id);

        if ($log) {
            $log->update(['is_deleted' => 1]);
        }
    }

    public function test_delete_soft_deletes_record()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $log = $this->createTestLog();
        if (!$log) {
            $this->markTestSkipped('Could not create test log');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-delete', ['id' => $log->id]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $log->refresh();
        $this->assertEquals(1, $log->is_deleted);
    }

    // --- IDOR TESTS ---

    public function test_list_does_not_leak_cross_home_data()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $otherHomeLog = $this->createTestLog(999);
        if (!$otherHomeLog) {
            $this->markTestSkipped('Could not create cross-home log');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-list', [
                'client_id' => $otherHomeLog->client_id,
            ]);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($otherHomeLog->id, $ids);

        $otherHomeLog->forceDelete();
    }

    public function test_edit_rejects_cross_home_record()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $otherHomeLog = $this->createTestLog(999);
        if (!$otherHomeLog) {
            $this->markTestSkipped('Could not create cross-home log');
        }

        $payload = $this->validPayload();
        $payload['id'] = $otherHomeLog->id;

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => false]);

        $otherHomeLog->forceDelete();
    }

    public function test_delete_rejects_cross_home_record()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $otherHomeLog = $this->createTestLog(999);
        if (!$otherHomeLog) {
            $this->markTestSkipped('Could not create cross-home log');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-delete', ['id' => $otherHomeLog->id]);

        $response->assertOk();
        $response->assertJson(['success' => false]);

        $otherHomeLog->refresh();
        $this->assertEquals(0, $otherHomeLog->is_deleted);

        $otherHomeLog->forceDelete();
    }

    // --- XSS PAYLOAD TEST ---

    public function test_save_accepts_xss_payload_without_breaking()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user');
        }

        $payload = $this->validPayload();
        $payload['medication_name'] = '<script>alert("xss")</script>';
        $payload['notes'] = '<img src=x onerror=alert(1)>';

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/medication-log-save', $payload);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $log = medicationLog::where('medication_name', '<script>alert("xss")</script>')
            ->where('home_id', $this->homeId)
            ->latest('id')->first();

        $this->assertNotNull($log);
        if ($log) {
            $log->update(['is_deleted' => 1]);
        }
    }
}
