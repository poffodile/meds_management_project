<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\MARSheet;
use App\Models\MARAdministration;
use App\User;
use Illuminate\Support\Facades\DB;

class MARSheetTest extends TestCase
{
    private $user;
    private $otherUser;
    private $csrfToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('id', 194)->first();
        $this->otherUser = User::where('home_id', 'NOT LIKE', '%8%')
            ->where('is_deleted', 0)
            ->first();
    }

    private function actingAsUser($user = null)
    {
        $u = $user ?? $this->user;
        return $this->withoutMiddleware()
            ->actingAs($u);
    }

    private function createTestSheet($overrides = [])
    {
        $sheet = new MARSheet();
        $sheet->fill(array_merge([
            'client_id' => 27,
            'medication_name' => 'Test Medication ' . uniqid(),
            'dosage' => '100mg',
            'route' => 'Oral',
            'frequency' => 'Once daily',
            'time_slots' => ['08:00'],
            'mar_status' => 'active',
        ], $overrides));
        $sheet->home_id = 8;
        $sheet->created_by = 194;
        $sheet->save();
        return $sheet;
    }

    private function createOtherHomeSheet()
    {
        $sheet = new MARSheet();
        $sheet->fill([
            'client_id' => 999,
            'medication_name' => 'Other Home Med',
            'dosage' => '50mg',
            'mar_status' => 'active',
        ]);
        $sheet->home_id = 999;
        $sheet->created_by = 1;
        $sheet->save();
        return $sheet;
    }

    // ==================== AUTH TESTS (3) ====================

    public function test_list_rejects_unauthenticated()
    {
        $response = $this->post('/roster/client/mar-sheet-list', [
            'client_id' => 27,
        ]);
        $response->assertStatus(302);
    }

    public function test_save_rejects_unauthenticated()
    {
        $response = $this->post('/roster/client/mar-sheet-save', [
            'client_id' => 27,
            'medication_name' => 'Test',
        ]);
        $response->assertStatus(302);
    }

    public function test_administer_rejects_unauthenticated()
    {
        $response = $this->post('/roster/client/mar-administer', [
            'mar_sheet_id' => 1,
            'date' => now()->toDateString(),
            'time_slot' => '08:00',
            'code' => 'A',
        ]);
        $response->assertStatus(302);
    }

    // ==================== VALIDATION TESTS (3) ====================

    public function test_save_rejects_missing_medication_name()
    {
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-save', [
                'client_id' => 27,
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('medication_name');
    }

    public function test_administer_rejects_invalid_code()
    {
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-administer', [
                'mar_sheet_id' => 1,
                'date' => now()->toDateString(),
                'time_slot' => '08:00',
                'code' => 'X',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('code');
    }

    public function test_save_rejects_negative_stock_level()
    {
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-save', [
                'client_id' => 27,
                'medication_name' => 'Test',
                'stock_level' => -5,
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('stock_level');
    }

    // ==================== FLOW TEST (1) ====================

    public function test_full_prescription_lifecycle()
    {
        // Create
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-save', [
                'client_id' => 27,
                'medication_name' => 'Lifecycle Test Med',
                'dosage' => '250mg',
                'route' => 'Oral',
                'frequency' => 'Twice daily',
                'time_slots' => ['08:00', '18:00'],
            ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $id = $response->json('data.id');
        $this->assertNotNull($id);

        // Appears in list
        $listResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-list', [
                'client_id' => 27,
                'status' => 'all',
            ]);
        $listResponse->assertStatus(200);
        $names = array_column($listResponse->json('data'), 'medication_name');
        $this->assertContains('Lifecycle Test Med', $names);

        // Update dosage
        $updateResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-update', [
                'id' => $id,
                'dosage' => '500mg',
            ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        // Details reflect change
        $detailsResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-details', [
                'id' => $id,
            ]);
        $detailsResponse->assertStatus(200);
        $this->assertEquals('500mg', $detailsResponse->json('data.dosage'));

        // Administer a dose
        $adminResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-administer', [
                'mar_sheet_id' => $id,
                'date' => now()->toDateString(),
                'time_slot' => '08:00',
                'code' => 'A',
                'dose_given' => '500mg',
            ]);
        $adminResponse->assertStatus(200);
        $adminResponse->assertJson(['success' => true]);

        // Grid shows it
        $gridResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-administration-grid', [
                'client_id' => 27,
                'date' => now()->toDateString(),
            ]);
        $gridResponse->assertStatus(200);
        $found = false;
        foreach ($gridResponse->json('data') as $sheet) {
            if ($sheet['id'] == $id && count($sheet['administrations']) > 0) {
                $found = true;
            }
        }
        $this->assertTrue($found, 'Administration should appear in grid');

        // Discontinue
        $discResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-discontinue', [
                'id' => $id,
                'discontinued_reason' => 'Test discontinue',
            ]);
        $discResponse->assertStatus(200);
        $this->assertEquals('discontinued', $discResponse->json('data.mar_status'));

        // Delete
        $deleteResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-delete', [
                'id' => $id,
            ]);
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        // Gone from list
        $listResponse2 = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-list', [
                'client_id' => 27,
                'status' => 'all',
            ]);
        $ids = array_column($listResponse2->json('data'), 'id');
        $this->assertNotContains($id, $ids);
    }

    // ==================== IDOR TESTS (4) ====================

    public function test_list_does_not_leak_cross_home_prescriptions()
    {
        $otherSheet = $this->createOtherHomeSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-list', [
                'client_id' => 999,
                'status' => 'all',
            ]);
        $response->assertStatus(200);
        $ids = array_column($response->json('data'), 'id');
        $this->assertNotContains($otherSheet->id, $ids);

        $otherSheet->delete();
    }

    public function test_details_rejects_cross_home_prescription()
    {
        $otherSheet = $this->createOtherHomeSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-details', [
                'id' => $otherSheet->id,
            ]);
        $response->assertStatus(404);

        $otherSheet->delete();
    }

    public function test_update_rejects_cross_home_prescription()
    {
        $otherSheet = $this->createOtherHomeSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-update', [
                'id' => $otherSheet->id,
                'medication_name' => 'Hacked Name',
            ]);
        $response->assertStatus(404);

        $this->assertNotEquals('Hacked Name', MARSheet::find($otherSheet->id)->medication_name);
        $otherSheet->delete();
    }

    public function test_administer_rejects_cross_home_prescription()
    {
        $otherSheet = $this->createOtherHomeSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-administer', [
                'mar_sheet_id' => $otherSheet->id,
                'date' => now()->toDateString(),
                'time_slot' => '08:00',
                'code' => 'A',
            ]);
        $response->assertStatus(404);

        $otherSheet->delete();
    }

    // ==================== SECURITY TESTS (3) ====================

    public function test_xss_in_medication_name_stored_raw()
    {
        $xss = '<script>alert(1)</script>';
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-save', [
                'client_id' => 27,
                'medication_name' => $xss,
            ]);
        $response->assertStatus(200);
        $id = $response->json('data.id');

        $detailsResponse = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-details', ['id' => $id]);
        $this->assertEquals($xss, $detailsResponse->json('data.medication_name'));

        MARSheet::where('id', $id)->forceDelete();
    }

    public function test_mass_assignment_home_id_and_created_by_ignored()
    {
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-sheet-save', [
                'client_id' => 27,
                'medication_name' => 'Mass Assignment Test',
                'home_id' => 999,
                'created_by' => 1,
                'is_deleted' => 1,
            ]);
        $response->assertStatus(200);
        $id = $response->json('data.id');

        $sheet = MARSheet::find($id);
        $this->assertEquals(8, $sheet->home_id);
        $this->assertEquals(194, $sheet->created_by);
        $this->assertEquals(0, $sheet->is_deleted);

        $sheet->forceDelete();
    }

    public function test_admin_only_delete()
    {
        $sheet = $this->createTestSheet();

        if ($this->otherUser && $this->otherUser->user_type !== 'A') {
            $nonAdmin = $this->otherUser;
        } else {
            $nonAdmin = User::where('user_type', '!=', 'A')
                ->where('is_deleted', 0)
                ->first();
        }

        if (!$nonAdmin) {
            $this->markTestSkipped('No non-admin user available for test');
        }

        $response = $this->actingAsUser($nonAdmin)
            ->postJson('/roster/client/mar-sheet-delete', [
                'id' => $sheet->id,
            ]);
        $response->assertStatus(403);

        $this->assertEquals(0, MARSheet::find($sheet->id)->is_deleted);
        $sheet->forceDelete();
    }

    // ==================== FUNCTIONAL TEST (1) ====================

    public function test_duplicate_administration_updates_instead_of_creating()
    {
        $sheet = $this->createTestSheet();
        $date = now()->toDateString();

        // First administration
        $this->actingAsUser()
            ->postJson('/roster/client/mar-administer', [
                'mar_sheet_id' => $sheet->id,
                'date' => $date,
                'time_slot' => '08:00',
                'code' => 'A',
                'dose_given' => '100mg',
            ]);

        // Second administration at same slot — should update
        $this->actingAsUser()
            ->postJson('/roster/client/mar-administer', [
                'mar_sheet_id' => $sheet->id,
                'date' => $date,
                'time_slot' => '08:00',
                'code' => 'R',
                'notes' => 'Patient changed mind',
            ]);

        $count = MARAdministration::where('mar_sheet_id', $sheet->id)
            ->where('date', $date)
            ->where('time_slot', '08:00')
            ->count();
        $this->assertEquals(1, $count, 'Should have exactly 1 record, not a duplicate');

        $admin = MARAdministration::where('mar_sheet_id', $sheet->id)
            ->where('date', $date)
            ->where('time_slot', '08:00')
            ->first();
        $this->assertEquals('R', $admin->code, 'Should be updated to Refused');

        MARAdministration::where('mar_sheet_id', $sheet->id)->forceDelete();
        $sheet->forceDelete();
    }

    // ==================== MONTHLY GRID TESTS (5) ====================

    public function test_monthly_grid_returns_prescriptions_for_month()
    {
        $sheet = $this->createTestSheet();

        $admin = new MARAdministration();
        $admin->fill([
            'mar_sheet_id' => $sheet->id,
            'date' => now()->toDateString(),
            'time_slot' => '08:00',
            'given' => true,
            'code' => 'A',
            'administered_by' => 194,
        ]);
        $admin->home_id = 8;
        $admin->save();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-monthly-grid', [
                'client_id' => 27,
                'year' => now()->year,
                'month' => now()->month,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['data' => ['sheets', 'year', 'month', 'days_in_month']]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data['sheets']));
        $this->assertEquals(now()->year, $data['year']);
        $this->assertEquals(now()->month, $data['month']);

        MARAdministration::where('id', $admin->id)->forceDelete();
        $sheet->forceDelete();
    }

    public function test_monthly_grid_rejects_invalid_month()
    {
        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-monthly-grid', [
                'client_id' => 27,
                'year' => 2026,
                'month' => 13,
            ]);
        $response->assertStatus(422);
    }

    public function test_monthly_grid_rejects_unauthenticated()
    {
        $response = $this->post('/roster/client/mar-monthly-grid', [
            'client_id' => 27,
            'year' => 2026,
            'month' => 4,
        ]);
        $response->assertStatus(302);
    }

    public function test_stock_update_saves_quantities()
    {
        $sheet = $this->createTestSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-stock-update', [
                'id' => $sheet->id,
                'quantity_received' => 60,
                'quantity_carried_forward' => 5,
                'quantity_returned' => 2,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $updated = MARSheet::find($sheet->id);
        $this->assertEquals(60, $updated->quantity_received);
        $this->assertEquals(5, $updated->quantity_carried_forward);
        $this->assertEquals(2, $updated->quantity_returned);

        $sheet->forceDelete();
    }

    public function test_stock_update_rejects_cross_home()
    {
        $otherSheet = $this->createOtherHomeSheet();

        $response = $this->actingAsUser()
            ->postJson('/roster/client/mar-stock-update', [
                'id' => $otherSheet->id,
                'quantity_received' => 100,
            ]);

        $response->assertStatus(404);

        $otherSheet->forceDelete();
    }
}
