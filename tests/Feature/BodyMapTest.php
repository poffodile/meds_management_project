<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\BodyMap;
use Illuminate\Support\Facades\DB;

class BodyMapTest extends TestCase
{
    protected $adminUser;
    protected $staffUser;
    protected $homeId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser = User::where('user_type', 'A')->where('is_deleted', '0')->first();
        $this->staffUser = User::where('user_type', 'N')->where('is_deleted', '0')->first();
        if ($this->adminUser) {
            $this->homeId = (int) explode(',', $this->adminUser->home_id)[0];
        }
    }

    private function getValidRiskId(): ?int
    {
        return DB::table('su_risk')->where('home_id', $this->homeId)->value('id');
    }

    /**
     * Create a temporary injury record for testing cross-home access.
     * Uses a fake home_id that doesn't match the admin user's home.
     */
    private function createCrossHomeInjury(): ?BodyMap
    {
        $riskId = $this->getValidRiskId();
        if (!$riskId) return null;

        $serviceUserId = DB::table('su_risk')->where('id', $riskId)->value('service_user_id');

        // Find a home_id that differs from the admin user's home
        $otherHomeId = DB::table('home')->where('id', '!=', $this->homeId)->value('id');
        if (!$otherHomeId) return null;

        return BodyMap::create([
            'home_id'         => $otherHomeId,
            'service_user_id' => $serviceUserId,
            'staff_id'        => $this->adminUser->id,
            'su_risk_id'      => $riskId,
            'sel_body_map_id' => 'frt_idor_test_' . time(),
            'injury_type'     => 'bruise',
            'injury_description' => 'IDOR test injury',
            'is_deleted'      => '0',
            'created_by'      => $this->adminUser->id,
        ]);
    }

    // --- Authentication Tests ---

    public function test_body_map_index_requires_auth()
    {
        $response = $this->get('/service/body-map/1');
        $response->assertRedirect();
    }

    public function test_add_injury_requires_auth()
    {
        $response = $this->post('/service/body-map/injury/add', []);
        $response->assertRedirect();
    }

    public function test_remove_injury_requires_auth()
    {
        $response = $this->post('/service/body-map/injury/remove', []);
        $response->assertRedirect();
    }

    // --- Validation Tests ---

    public function test_add_injury_validates_required_fields()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['service_user_id', 'su_risk_id', 'sel_body_map_id']);
    }

    public function test_add_injury_validates_injury_type_enum()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $riskId = $this->getValidRiskId();
        if (!$riskId) { $this->markTestSkipped('No risk found.'); }

        $serviceUserId = DB::table('su_risk')->where('id', $riskId)->value('service_user_id');

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', [
                'service_user_id' => $serviceUserId,
                'su_risk_id'      => $riskId,
                'sel_body_map_id' => 'frt_99',
                'injury_type'     => 'invalid_type',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('injury_type');
    }

    public function test_remove_injury_validates_injury_id()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/remove', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('injury_id');
    }

    public function test_add_injury_rejects_description_over_max_length()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $riskId = $this->getValidRiskId();
        if (!$riskId) { $this->markTestSkipped('No risk found.'); }

        $serviceUserId = DB::table('su_risk')->where('id', $riskId)->value('service_user_id');

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', [
                'service_user_id'    => $serviceUserId,
                'su_risk_id'         => $riskId,
                'sel_body_map_id'    => 'frt_maxlen',
                'injury_description' => str_repeat('x', 1001),
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('injury_description');
    }

    // --- Multi-tenancy Tests ---

    public function test_index_rejects_risk_from_wrong_home()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/service/body-map/999999');
        $response->assertRedirect();
    }

    public function test_add_injury_rejects_nonexistent_risk()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', [
                'service_user_id' => 1,
                'su_risk_id'      => 999999,
                'sel_body_map_id' => 'frt_1',
            ]);
        // 422 because su_risk_id doesn't exist in table (exists: validation)
        $response->assertStatus(422);
    }

    // --- IDOR Tests (Cross-Home Access) ---

    public function test_get_injury_rejects_cross_home_access()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $crossHomeInjury = $this->createCrossHomeInjury();
        if (!$crossHomeInjury) { $this->markTestSkipped('Could not create cross-home injury.'); }

        try {
            $response = $this->withoutMiddleware()
                ->actingAs($this->adminUser)
                ->getJson('/service/body-map/injury/' . $crossHomeInjury->id);

            // Should return 404 because injury belongs to a different home
            $response->assertStatus(404);
        } finally {
            DB::table('body_map')->where('id', $crossHomeInjury->id)->delete();
        }
    }

    public function test_remove_injury_rejects_cross_home_access()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $crossHomeInjury = $this->createCrossHomeInjury();
        if (!$crossHomeInjury) { $this->markTestSkipped('Could not create cross-home injury.'); }

        try {
            $response = $this->withoutMiddleware()
                ->actingAs($this->adminUser)
                ->postJson('/service/body-map/injury/remove', [
                    'injury_id' => $crossHomeInjury->id,
                ]);

            // Should return 404 because injury belongs to a different home
            $response->assertStatus(404);

            // Verify the injury was NOT deleted
            $this->assertDatabaseHas('body_map', [
                'id'         => $crossHomeInjury->id,
                'is_deleted' => '0',
            ]);
        } finally {
            DB::table('body_map')->where('id', $crossHomeInjury->id)->delete();
        }
    }

    public function test_update_injury_rejects_cross_home_access()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $crossHomeInjury = $this->createCrossHomeInjury();
        if (!$crossHomeInjury) { $this->markTestSkipped('Could not create cross-home injury.'); }

        try {
            $response = $this->withoutMiddleware()
                ->actingAs($this->adminUser)
                ->postJson('/service/body-map/injury/update', [
                    'id'                 => $crossHomeInjury->id,
                    'injury_description' => 'Hacked description',
                ]);

            // Should return 404 because injury belongs to a different home
            $response->assertStatus(404);

            // Verify the description was NOT changed
            $this->assertDatabaseHas('body_map', [
                'id'                 => $crossHomeInjury->id,
                'injury_description' => 'IDOR test injury',
            ]);
        } finally {
            DB::table('body_map')->where('id', $crossHomeInjury->id)->delete();
        }
    }

    // --- Role-based Access Tests ---

    public function test_non_admin_cannot_remove_injury()
    {
        if (!$this->staffUser) { $this->markTestSkipped('No staff user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->staffUser)
            ->postJson('/service/body-map/injury/remove', [
                'injury_id' => 1,
            ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_remove_nonexistent_returns_404()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/remove', [
                'injury_id' => 999999,
            ]);
        $response->assertStatus(404);
    }

    // --- Happy Path Tests ---

    public function test_add_injury_creates_record()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $riskId = $this->getValidRiskId();
        if (!$riskId) { $this->markTestSkipped('No risk found.'); }

        $serviceUserId = DB::table('su_risk')->where('id', $riskId)->value('service_user_id');
        $uniqueBodyPart = 'frt_test_' . time();

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', [
                'service_user_id' => $serviceUserId,
                'su_risk_id'      => $riskId,
                'sel_body_map_id' => $uniqueBodyPart,
                'injury_type'     => 'bruise',
                'injury_description' => 'Test bruise on arm',
                'injury_date'     => '2026-04-11',
                'injury_size'     => '2cm x 3cm',
                'injury_colour'   => 'Purple',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('body_map', [
            'sel_body_map_id' => $uniqueBodyPart,
            'home_id'         => $this->homeId,
            'injury_type'     => 'bruise',
            'created_by'      => $this->adminUser->id,
            'is_deleted'      => '0',
        ]);

        // Clean up
        DB::table('body_map')->where('sel_body_map_id', $uniqueBodyPart)->delete();
    }

    public function test_get_injury_detail()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $injury = BodyMap::forHome($this->homeId)->active()->first();
        if (!$injury) { $this->markTestSkipped('No injury found.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->getJson('/service/body-map/injury/' . $injury->id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_get_history()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $injury = BodyMap::forHome($this->homeId)->first();
        if (!$injury) { $this->markTestSkipped('No injury found.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->getJson('/service/body-map/history/' . $injury->service_user_id);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    // --- XSS Tests ---

    public function test_add_injury_stores_xss_payload_safely()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $riskId = $this->getValidRiskId();
        if (!$riskId) { $this->markTestSkipped('No risk found.'); }

        $serviceUserId = DB::table('su_risk')->where('id', $riskId)->value('service_user_id');
        $uniqueBodyPart = 'frt_xss_' . time();
        $xssPayload = '<script>alert("xss")</script>';

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->postJson('/service/body-map/injury/add', [
                'service_user_id'    => $serviceUserId,
                'su_risk_id'         => $riskId,
                'sel_body_map_id'    => $uniqueBodyPart,
                'injury_type'        => 'other',
                'injury_description' => $xssPayload,
            ]);

        $response->assertStatus(200);

        // Verify the raw HTML is stored (Blade {{ }} will escape on render)
        $this->assertDatabaseHas('body_map', [
            'sel_body_map_id'    => $uniqueBodyPart,
            'injury_description' => $xssPayload,
        ]);

        // Clean up
        DB::table('body_map')->where('sel_body_map_id', $uniqueBodyPart)->delete();
    }

    // --- Route Method Tests ---

    public function test_add_injury_rejects_get()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/service/body-map/injury/add');
        $response->assertStatus(405);
    }

    public function test_remove_injury_rejects_get()
    {
        if (!$this->adminUser) { $this->markTestSkipped('No admin user.'); }

        $response = $this->withoutMiddleware()
            ->actingAs($this->adminUser)
            ->get('/service/body-map/injury/remove');
        $response->assertStatus(405);
    }
}
