<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Models\SafeguardingReferral;
use Illuminate\Support\Facades\DB;

class SafeguardingTest extends TestCase
{
    protected $user;
    protected $crossHomeUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('user_name', 'komal')->first();
        $this->crossHomeUser = User::where('home_id', '!=', 8)
            ->whereNotNull('home_id')
            ->where('home_id', '!=', '')
            ->first();
    }

    protected function actingAsUser()
    {
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->user);
    }

    protected function actingAsCrossHomeUser()
    {
        if (!$this->crossHomeUser) {
            $this->markTestSkipped('No cross-home user available for IDOR test');
        }
        return $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
                     ->actingAs($this->crossHomeUser);
    }

    protected function getHomeId(): int
    {
        return (int) explode(',', $this->user->home_id)[0];
    }

    protected function getTestReferralId(): int
    {
        $referral = SafeguardingReferral::where('home_id', $this->getHomeId())
            ->where('is_deleted', 0)
            ->first();
        if (!$referral) {
            $this->fail('No test referral found for home ' . $this->getHomeId());
        }
        return $referral->id;
    }

    // ========================================
    // 4a. Auth tests
    // ========================================

    public function test_list_requires_authentication()
    {
        $response = $this->post('/roster/safeguarding/list');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_save_requires_authentication()
    {
        $response = $this->post('/roster/safeguarding/save');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_details_requires_authentication()
    {
        $response = $this->post('/roster/safeguarding/details', ['id' => 1]);
        $this->assertNotEquals(200, $response->status());
    }

    public function test_delete_requires_authentication()
    {
        $response = $this->post('/roster/safeguarding/delete', ['id' => 1]);
        $this->assertNotEquals(200, $response->status());
    }

    // ========================================
    // 4a. Validation tests
    // ========================================

    public function test_save_rejects_missing_required_fields()
    {
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date_of_concern', 'details_of_concern', 'safeguarding_type', 'risk_level']);
    }

    public function test_save_rejects_invalid_risk_level()
    {
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => 'Test concern',
            'safeguarding_type' => ['Physical Abuse'],
            'risk_level' => 'extreme',
            'ongoing_risk' => false,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['risk_level']);
    }

    public function test_save_rejects_invalid_safeguarding_type()
    {
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => 'Test concern',
            'safeguarding_type' => 'not_an_array',
            'risk_level' => 'high',
            'ongoing_risk' => false,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['safeguarding_type']);
    }

    // ========================================
    // 4b. Flow tests
    // ========================================

    public function test_full_lifecycle_flow()
    {
        // Create
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => 'Flow test concern — lifecycle check',
            'safeguarding_type' => ['Physical Abuse'],
            'risk_level' => 'medium',
            'ongoing_risk' => false,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $id = $response->json('data.id');
        $this->assertNotNull($id);
        $this->assertStringStartsWith('SAFE-', $response->json('data.reference_number'));

        // Appears in list
        $listResponse = $this->actingAsUser()->postJson('/roster/safeguarding/list');
        $listResponse->assertStatus(200);
        $ids = array_column($listResponse->json('data.data'), 'id');
        $this->assertContains($id, $ids);

        // Update
        $updateResponse = $this->actingAsUser()->postJson('/roster/safeguarding/update', [
            'id' => $id,
            'details_of_concern' => 'Updated concern details for flow test',
            'location_of_incident' => 'Test location',
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        // Details reflect update
        $detailResponse = $this->actingAsUser()->postJson('/roster/safeguarding/details', ['id' => $id]);
        $detailResponse->assertStatus(200);
        $this->assertEquals('Test location', $detailResponse->json('data.location_of_incident'));

        // Status change: reported → under_investigation
        $statusResponse = $this->actingAsUser()->postJson('/roster/safeguarding/status-change', [
            'id' => $id,
            'status' => 'under_investigation',
        ]);
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson(['success' => true]);

        // Delete (admin)
        $deleteResponse = $this->actingAsUser()->postJson('/roster/safeguarding/delete', ['id' => $id]);
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        // No longer in list
        $listAfter = $this->actingAsUser()->postJson('/roster/safeguarding/list');
        $idsAfter = array_column($listAfter->json('data.data'), 'id');
        $this->assertNotContains($id, $idsAfter);
    }

    // ========================================
    // 4c. IDOR tests
    // ========================================

    public function test_list_does_not_leak_cross_home_referrals()
    {
        $response = $this->actingAsCrossHomeUser()->postJson('/roster/safeguarding/list');
        $response->assertStatus(200);
        $referrals = $response->json('data.data');
        foreach ($referrals as $r) {
            $this->assertNotEquals(8, $r['home_id']);
        }
    }

    public function test_details_rejects_cross_home_referral()
    {
        $id = $this->getTestReferralId();
        $response = $this->actingAsCrossHomeUser()->postJson('/roster/safeguarding/details', ['id' => $id]);
        $response->assertStatus(404);
    }

    public function test_update_rejects_cross_home_referral()
    {
        $id = $this->getTestReferralId();
        $response = $this->actingAsCrossHomeUser()->postJson('/roster/safeguarding/update', [
            'id' => $id,
            'details_of_concern' => 'IDOR attempt',
        ]);
        $response->assertStatus(404);
    }

    public function test_delete_rejects_cross_home_referral()
    {
        $id = $this->getTestReferralId();
        // Make cross-home user admin type for this test
        if ($this->crossHomeUser) {
            $origType = $this->crossHomeUser->user_type;
            $this->crossHomeUser->user_type = 'A';
            $response = $this->actingAsCrossHomeUser()->postJson('/roster/safeguarding/delete', ['id' => $id]);
            $this->crossHomeUser->user_type = $origType;
            $response->assertStatus(404);
        }
    }

    // ========================================
    // 4d. Security payload tests
    // ========================================

    public function test_xss_payload_stored_safely()
    {
        $xss = '<script>alert("xss")</script>';
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => $xss,
            'safeguarding_type' => ['Physical Abuse'],
            'risk_level' => 'low',
            'ongoing_risk' => false,
            'location_of_incident' => $xss,
        ]);
        $response->assertStatus(200);
        $id = $response->json('data.id');

        // Verify stored as-is in DB (no mangling), returned for JS to escape
        $detail = $this->actingAsUser()->postJson('/roster/safeguarding/details', ['id' => $id]);
        $this->assertEquals($xss, $detail->json('data.details_of_concern'));
        $this->assertEquals($xss, $detail->json('data.location_of_incident'));

        // Cleanup
        $this->actingAsUser()->postJson('/roster/safeguarding/delete', ['id' => $id]);
    }

    public function test_mass_assignment_home_id_ignored()
    {
        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => 'Mass assignment test',
            'safeguarding_type' => ['Neglect'],
            'risk_level' => 'low',
            'ongoing_risk' => false,
            'home_id' => 999,
            'created_by' => 1,
            'is_deleted' => 1,
        ]);
        $response->assertStatus(200);
        $id = $response->json('data.id');

        // Verify server-side values used, not injected ones
        $record = DB::table('safeguarding_referrals')->where('id', $id)->first();
        $this->assertEquals(8, $record->home_id);
        $this->assertEquals(194, $record->created_by);
        $this->assertEquals(0, $record->is_deleted);

        // Cleanup
        DB::table('safeguarding_referrals')->where('id', $id)->update(['is_deleted' => 1]);
    }

    public function test_delete_requires_admin_role()
    {
        // Create a non-admin user mock
        $staffUser = clone $this->user;
        $staffUser->user_type = 'N';

        $id = $this->getTestReferralId();
        $response = $this->withoutMiddleware(\App\Http\Middleware\checkUserAuth::class)
            ->actingAs($staffUser)
            ->postJson('/roster/safeguarding/delete', ['id' => $id]);
        $response->assertStatus(403);
    }

    public function test_status_change_rejects_invalid_transition()
    {
        // reported can only go to under_investigation, not to closed
        $referral = SafeguardingReferral::where('home_id', $this->getHomeId())
            ->where('status', 'reported')
            ->where('is_deleted', 0)
            ->first();

        if (!$referral) {
            // Create one for this test
            $resp = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
                'date_of_concern' => '2026-04-22 10:00:00',
                'details_of_concern' => 'Status transition test',
                'safeguarding_type' => ['Neglect'],
                'risk_level' => 'low',
                'ongoing_risk' => false,
            ]);
            $referral = SafeguardingReferral::find($resp->json('data.id'));
        }

        $response = $this->actingAsUser()->postJson('/roster/safeguarding/status-change', [
            'id' => $referral->id,
            'status' => 'closed',
        ]);
        $response->assertStatus(404);
    }

    public function test_json_fields_stored_and_retrieved_correctly()
    {
        $witnesses = [
            ['name' => 'Jane Doe', 'role' => 'Nurse', 'statement' => 'Saw the incident occur'],
            ['name' => 'John Smith', 'role' => 'Carer', 'statement' => 'Heard shouting from the room'],
        ];
        $perpetrator = ['name' => 'Unknown Male', 'relationship' => 'Visitor', 'details' => 'Tall, wearing blue jacket'];

        $response = $this->actingAsUser()->postJson('/roster/safeguarding/save', [
            'date_of_concern' => '2026-04-22 10:00:00',
            'details_of_concern' => 'JSON fields test',
            'safeguarding_type' => ['Physical Abuse', 'Financial Abuse'],
            'risk_level' => 'high',
            'ongoing_risk' => true,
            'witnesses' => $witnesses,
            'alleged_perpetrator' => $perpetrator,
        ]);
        $response->assertStatus(200);
        $id = $response->json('data.id');

        $detail = $this->actingAsUser()->postJson('/roster/safeguarding/details', ['id' => $id]);
        $detail->assertStatus(200);

        $data = $detail->json('data');
        $this->assertCount(2, $data['witnesses']);
        $this->assertEquals('Jane Doe', $data['witnesses'][0]['name']);
        $this->assertEquals('Unknown Male', $data['alleged_perpetrator']['name']);
        $this->assertCount(2, $data['safeguarding_type']);
        $this->assertContains('Physical Abuse', $data['safeguarding_type']);
        $this->assertContains('Financial Abuse', $data['safeguarding_type']);

        // Cleanup
        $this->actingAsUser()->postJson('/roster/safeguarding/delete', ['id' => $id]);
    }
}
