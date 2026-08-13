<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4PrescriptionEvent;
use App\Models\Frontend4User;
use App\Models\MARSheet;
use App\Models\MedicineCatalogue;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\PrescriptionRecordService;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;
use Mockery;
use Tests\TestCase;

class Frontend4PrescriptionLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_creates_a_catalogue_backed_prescription_and_derives_liquid_quantity(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine(['strength_amount' => 250, 'strength_volume' => 5, 'countable_unit' => 'ml']);
        $this->bindRole(RoleResolver::MANAGER);

        $response = $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/clients/'.$client->id.'/medications', $this->payload($medicine, [
                'dose_amount' => 500, 'dose_unit' => 'mg',
            ]));

        $sheet = MARSheet::where('client_id', $client->id)->where('medicine_id', $medicine->id)->firstOrFail();
        $response->assertRedirect('/frontend4/clients/'.$client->id);
        $this->assertSame($medicine->name, $sheet->medication_name);
        $this->assertSame(10.0, (float) $sheet->dose_quantity);
        $this->assertSame(1, (int) $sheet->prescription_version);
        $this->assertDatabaseHas('frontend4_prescription_events', [
            'mar_sheet_id' => $sheet->id, 'event_type' => 'created', 'medicine_id' => $medicine->id,
        ]);
    }

    public function test_controlled_drug_classification_comes_only_from_the_catalogue(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine(['is_controlled' => 1, 'cd_schedule' => '2']);
        $this->bindRole(RoleResolver::MANAGER);
        $payload = $this->payload($medicine) + ['is_controlled' => false, 'cd_schedule' => null];

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/clients/'.$client->id.'/medications', $payload)->assertRedirect();

        $sheet = MARSheet::where('client_id', $client->id)->where('medicine_id', $medicine->id)->firstOrFail();
        $this->assertTrue((bool) $sheet->is_controlled);
        $this->assertSame('2', $sheet->cd_schedule);
    }

    public function test_duplicate_active_catalogue_prescription_is_rejected(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine();
        $this->bindRole(RoleResolver::MANAGER);
        $session = $this->sessionFor($user, $service);

        $this->actingAs($user, 'frontend4')->withSession($session)
            ->post('/frontend4/clients/'.$client->id.'/medications', $this->payload($medicine))->assertRedirect();
        $this->actingAs($user, 'frontend4')->withSession($session)
            ->post('/frontend4/clients/'.$client->id.'/medications', $this->payload($medicine))
            ->assertSessionHasErrors('medicine_id');

        $this->assertSame(1, MARSheet::where('client_id', $client->id)->where('medicine_id', $medicine->id)->count());
    }

    public function test_amendment_keeps_the_mar_identity_and_appends_before_after_history(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine(['strength_amount' => 250, 'strength_volume' => 5, 'countable_unit' => 'ml']);
        $sheet = app(PrescriptionRecordService::class)->create(
            $client, $medicine, $this->servicePayload(['dose_amount' => 500]),
            $user, (int) $service->admin_id, (int) $service->id
        );
        $this->bindRole(RoleResolver::MANAGER);

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->put('/frontend4/clients/'.$client->id.'/medications/'.$sheet->id, $this->payload($medicine, [
                'dose_amount' => 250, 'amendment_reason' => 'Prescriber issued a replacement direction',
            ]))->assertRedirect('/frontend4/clients/'.$client->id);

        $updated = MARSheet::findOrFail($sheet->id);
        $this->assertSame(2, (int) $updated->prescription_version);
        $this->assertSame(5.0, (float) $updated->dose_quantity);
        $event = Frontend4PrescriptionEvent::where('mar_sheet_id', $sheet->id)->where('event_type', 'amended')->firstOrFail();
        $this->assertSame(500.0, (float) $event->changes['before']['dose_amount']);
        $this->assertSame(250.0, (float) $event->changes['after']['dose_amount']);
    }

    public function test_pause_resume_and_stop_follow_a_terminal_state_machine(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine();
        $records = app(PrescriptionRecordService::class);
        $sheet = $records->create(
            $client, $medicine, $this->servicePayload(), $user, (int) $service->admin_id, (int) $service->id
        );

        $records->changeStatus($sheet, 'pause', 'Temporary clinical hold', $user, (int) $service->admin_id);
        $this->assertSame('paused', $sheet->fresh()->mar_status);
        $records->changeStatus($sheet, 'resume', 'Prescriber confirmed treatment continues', $user, (int) $service->admin_id);
        $this->assertSame('active', $sheet->fresh()->mar_status);
        $records->changeStatus($sheet, 'stop', 'Prescriber discontinued treatment', $user, (int) $service->admin_id);
        $this->assertSame('discontinued', $sheet->fresh()->mar_status);

        $this->expectException(ValidationException::class);
        $records->changeStatus($sheet, 'resume', 'Unsafe attempted restart', $user, (int) $service->admin_id);
    }

    public function test_prescription_events_are_append_only(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine();
        $sheet = app(PrescriptionRecordService::class)->create(
            $client, $medicine, $this->servicePayload(), $user, (int) $service->admin_id, (int) $service->id
        );
        $event = Frontend4PrescriptionEvent::where('mar_sheet_id', $sheet->id)->firstOrFail();

        $this->expectException(LogicException::class);
        $event->update(['reason' => 'rewritten']);
    }

    public function test_future_and_ended_prescriptions_are_excluded_by_effective_date_scope(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine();
        $sheet = app(PrescriptionRecordService::class)->create(
            $client, $medicine, $this->servicePayload([
                'start_date' => now()->addDays(2)->toDateString(),
                'review_due_date' => now()->addMonth()->toDateString(),
            ]), $user, (int) $service->admin_id, (int) $service->id
        );

        $this->assertFalse(MARSheet::effectiveOn(now()->toDateString())->whereKey($sheet->id)->exists());
        $this->assertTrue(MARSheet::effectiveOn(now()->addDays(2)->toDateString())->whereKey($sheet->id)->exists());
    }

    public function test_new_prescription_is_blocked_for_an_inactive_client(): void
    {
        [$user, $service, $client] = $this->fixture();
        $medicine = $this->medicine();
        $client->lifecycle_status = 'inactive';
        $client->status = 0;
        $client->save();
        $this->bindRole(RoleResolver::MANAGER);

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/clients/'.$client->id.'/medications', $this->payload($medicine))
            ->assertSessionHasErrors('client');
        $this->assertFalse(MARSheet::where('client_id', $client->id)->where('medicine_id', $medicine->id)->exists());
    }

    private function fixture(): array
    {
        $this->requireSchema();
        foreach (Frontend4User::where('status', 1)->where('is_deleted', 0)->get() as $user) {
            $legacyIds = collect(explode(',', (string) $user->real_home_id))
                ->map(fn ($id) => (int) trim($id))->filter();
            foreach (Home::whereIn('id', $legacyIds)->where('is_deleted', 0)->get()->groupBy('admin_id') as $services) {
                $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $services->first()->admin_id);
                foreach ($services->whereIn('id', $allowed) as $service) {
                    session($this->sessionFor($user, $service));
                    $client = app(AccessContext::class)->scopeClients(ServiceUser::query(), $user)
                        ->where('is_deleted', 0)
                        ->where(function ($active) {
                            $active->where('lifecycle_status', 'active')
                                ->orWhere(function ($legacy) {
                                    $legacy->whereNull('lifecycle_status')->where('status', 1);
                                });
                        })->first();
                    if ($client) {
                        return [$user, $service, $client];
                    }
                }
            }
        }
        $this->markTestSkipped('The fixture database has no suitable Frontend 4 user, service and client.');
    }

    private function medicine(array $overrides = []): MedicineCatalogue
    {
        return MedicineCatalogue::create(array_merge([
            'dmd_code' => (string) random_int(100000000000000000, 999999999999999999),
            'dmd_concept_level' => 'VMP',
            'name' => 'R6 verification medicine '.bin2hex(random_bytes(4)),
            'form' => 'Oral suspension',
            'default_route' => 'Oral',
            'countable_unit' => 'ml',
            'strength_amount' => 250,
            'strength_unit' => 'mg',
            'strength_volume' => 5,
            'strength_volume_unit' => 'ml',
            'is_controlled' => 0,
            'cd_schedule' => null,
            'dmd_status' => 'current',
            'is_local' => 0,
        ], $overrides));
    }

    private function payload(MedicineCatalogue $medicine, array $overrides = []): array
    {
        return array_merge($this->servicePayload(), ['medicine_id' => $medicine->id], $overrides);
    }

    private function servicePayload(array $overrides = []): array
    {
        return array_merge([
            'medication_name_as_written' => 'Verified label text',
            'dose_amount' => 500,
            'dose_unit' => 'mg',
            'route' => 'Oral',
            'frequency' => 'Twice daily',
            'time_slots' => ['08:00', '20:00'],
            'as_required' => false,
            'prn_details' => null,
            'prn_max_daily' => null,
            'prn_min_interval_hours' => null,
            'reason_for_medication' => 'Test indication',
            'administration_instructions' => 'Follow verified label',
            'prescriber' => 'Test prescriber',
            'pharmacy' => 'Test pharmacy',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'review_due_date' => now()->addMonth()->toDateString(),
            'prescription_source' => 'paper_prescription',
        ], $overrides);
    }

    private function sessionFor(Frontend4User $user, Home $service): array
    {
        $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id);
        return [
            'frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $service->id,
            'frontend4.allowed_service_ids' => $allowed,
            'frontend4.active_location_id' => null,
            'frontend4.active_home_id' => (int) $service->id,
            'frontend4.allowed_home_ids' => $allowed,
            'frontend4.last_activity' => time(),
        ];
    }

    private function bindRole(string $role): void
    {
        $resolver = Mockery::mock(RoleResolver::class);
        $resolver->shouldReceive('resolve')->andReturn($role);
        $resolver->shouldReceive('label')->andReturn(RoleResolver::LABELS[$role]);
        $this->app->instance(RoleResolver::class, $resolver);
    }

    private function requireSchema(): void
    {
        foreach (['medicine_catalogue', 'frontend4_prescription_events'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped('Run the Frontend 4 medicine catalogue migration first.');
            }
        }
    }
}
