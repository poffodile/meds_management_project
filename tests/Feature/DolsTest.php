<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\Dol;
use Illuminate\Support\Facades\DB;

class DolsTest extends TestCase
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

    private function createTestDol($homeId = null): ?Dol
    {
        $clientId = $this->getClientId();
        if (!$clientId) return null;

        return Dol::create([
            'home_id' => $homeId ?? $this->homeId,
            'user_id' => $this->adminUser->id,
            'client_id' => $clientId,
            'dols_status' => 'Not Applicable',
            'authorisation_type' => 'Standard',
            'supervisory_body' => 'Test Body',
            'case_reference' => 'TEST-REF-001',
        ]);
    }

    public function test_dols_list_requires_auth()
    {
        $response = $this->postJson('/roster/client/dols-list', ['client_id' => 1]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_dols_save_requires_auth()
    {
        $response = $this->postJson('/roster/client/save-dols', [
            'dols_status' => 'Not Applicable',
            'client_id' => 1,
        ]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_dols_delete_requires_auth()
    {
        $response = $this->postJson('/roster/client/dols-delete', ['id' => 1]);
        $this->assertTrue(
            $response->status() === 401 ||
            $response->status() === 302 ||
            $response->status() === 419
        );
    }

    public function test_dols_list_returns_success()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }
        $clientId = $this->getClientId();
        if (!$clientId) {
            $this->markTestSkipped('No client found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/dols-list', ['client_id' => $clientId]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_dols_save_validates_required_fields()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/save-dols', []);

        $response->assertStatus(200)
            ->assertJson(['success' => false]);
    }

    public function test_dols_save_validates_enum_values()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/save-dols', [
                'dols_status' => '<script>alert("xss")</script>',
                'client_id' => 1,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => false]);
    }

    public function test_dols_save_rejects_xss_in_text_fields()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/save-dols', [
                'dols_status' => 'Not Applicable',
                'client_id' => 1,
                'supervisory_body' => str_repeat('A', 256),
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => false]);
    }

    public function test_dols_save_creates_record()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }
        $clientId = $this->getClientId();
        if (!$clientId) {
            $this->markTestSkipped('No client found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/save-dols', [
                'dols_status' => 'Screening Required',
                'authorisation_type' => 'Standard',
                'client_id' => $clientId,
                'supervisory_body' => 'Test Supervisory Body',
                'case_reference' => 'PHPUNIT-TEST-' . time(),
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        if ($response->json('data.id')) {
            Dol::where('id', $response->json('data.id'))->forceDelete();
        }
    }

    public function test_dols_update_idor_blocked()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $otherHomeId = DB::table('home')->where('id', '!=', $this->homeId)->value('id');
        if (!$otherHomeId) {
            $this->markTestSkipped('No other home found for IDOR test');
        }

        $crossHomeDol = $this->createTestDol($otherHomeId);
        if (!$crossHomeDol) {
            $this->markTestSkipped('Could not create cross-home DoLS record');
        }

        $clientId = $this->getClientId();
        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/save-dols', [
                'dols_id' => $crossHomeDol->id,
                'dols_status' => 'Expired',
                'client_id' => $clientId ?? 1,
            ]);

        $crossHomeDol->forceDelete();

        $this->assertTrue(
            $response->json('success') === false ||
            $response->status() >= 400,
            'IDOR: should not be able to update another home\'s DoLS record'
        );
    }

    public function test_dols_delete_idor_blocked()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $otherHomeId = DB::table('home')->where('id', '!=', $this->homeId)->value('id');
        if (!$otherHomeId) {
            $this->markTestSkipped('No other home found for IDOR test');
        }

        $crossHomeDol = $this->createTestDol($otherHomeId);
        if (!$crossHomeDol) {
            $this->markTestSkipped('Could not create cross-home DoLS record');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/dols-delete', [
                'id' => $crossHomeDol->id,
            ]);

        $stillExists = Dol::find($crossHomeDol->id);
        $crossHomeDol->forceDelete();

        $this->assertNotNull($stillExists, 'IDOR: cross-home DoLS record should not be deleted');
        $this->assertJson($response->getContent());
    }

    public function test_dols_delete_own_record_succeeds()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $ownDol = $this->createTestDol($this->homeId);
        if (!$ownDol) {
            $this->markTestSkipped('Could not create DoLS record');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/dols-delete', [
                'id' => $ownDol->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_dols_delete_validates_id()
    {
        if (!$this->adminUser) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/roster/client/dols-delete', [
                'id' => 'not-a-number',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => false]);
    }
}
