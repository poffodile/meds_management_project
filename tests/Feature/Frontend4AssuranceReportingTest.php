<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4AssuranceReview;
use App\Models\Frontend4MedicationIncident;
use App\Models\Frontend4ReportExportEvent;
use App\Models\Frontend4User;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicineCatalogue;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\AssuranceReportingService;
use App\Services\Frontend4\PrescriptionRecordService;
use App\Services\Frontend4\RoleResolver;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Mockery;
use Tests\TestCase;

class Frontend4AssuranceReportingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assurance_uses_real_scoped_records_without_inventing_a_score(): void
    {
        [$user, $service, $client] = $this->fixture();
        $reporting = app(AssuranceReportingService::class);
        $period = [CarbonImmutable::today(), CarbonImmutable::today()];
        $before = $reporting->metrics($user, app(AccessContext::class), ...$period);
        $sheet = $this->prescription($user, $service, $client);
        $this->administration($user, $service, $sheet, 'R', false);
        $after = $reporting->metrics($user, app(AccessContext::class), ...$period);

        $this->assertSame($before['values']['administrationRecords'] + 1, $after['values']['administrationRecords']);
        $this->assertSame($before['values']['notGivenRecords'] + 1, $after['values']['notGivenRecords']);
        $this->assertArrayNotHasKey('score', $after);
        $this->assertArrayHasKey('availability', $after);
    }

    public function test_manager_signs_an_append_only_evidence_snapshot(): void
    {
        [$user, $service] = $this->fixture();
        $this->bindRole(RoleResolver::MANAGER);
        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))->post('/frontend4/assurance/reviews', [
            'period_start' => now()->subDays(6)->toDateString(), 'period_end' => now()->toDateString(),
            'review_note' => 'I reviewed each exception and the open action list.',
            'action_summary' => 'The shift lead will close the overdue action tomorrow.',
        ])->assertRedirect();

        $review = Frontend4AssuranceReview::where('service_id', $service->id)->latest('id')->firstOrFail();
        $this->assertSame($user->id, (int) $review->reviewed_by_user_id);
        $this->assertNotNull($review->reviewed_at);
        $this->expectException(LogicException::class);
        $review->update(['review_note' => 'Rewritten']);
    }

    public function test_carer_cannot_open_reports_by_direct_url(): void
    {
        [$user, $service] = $this->fixture();
        $this->bindRole(RoleResolver::CARER);
        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->get('/frontend4/reports')->assertForbidden();
    }

    public function test_administrator_can_read_reports_but_cannot_sign_a_clinical_assurance_review(): void
    {
        [$user, $service] = $this->fixture();
        $this->bindRole(RoleResolver::ADMIN);
        $session = $this->sessionFor($user, $service);
        $this->actingAs($user, 'frontend4')->withSession($session)->get('/frontend4/reports')->assertOk();
        $this->actingAs($user, 'frontend4')->withSession($session)->post('/frontend4/assurance/reviews', [
            'period_start' => now()->subDay()->toDateString(), 'period_end' => now()->toDateString(),
            'review_note' => 'An administrator must not sign clinical assurance.',
        ])->assertForbidden();
    }

    public function test_summary_export_omits_client_identity_and_records_the_access_event(): void
    {
        [$user, $service, $client] = $this->fixture();
        $this->bindRole(RoleResolver::MANAGER);
        $response = $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/reports/export', [
                'report_type' => 'overview', 'period_start' => now()->subDays(6)->toDateString(),
                'period_end' => now()->toDateString(), 'identifiable' => 0,
                'reason' => 'Weekly medication governance meeting.', 'authorised' => 1,
            ])->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString('metric,value', str_replace("\r", '', $content));
        $this->assertStringNotContainsString($client->name, $content);
        $this->assertDatabaseHas('frontend4_report_export_events', [
            'service_id' => $service->id, 'requested_by_user_id' => $user->id,
            'report_type' => 'overview', 'identifiable' => 0,
        ]);
    }

    public function test_identifiable_export_contains_source_identity_and_is_audited(): void
    {
        [$user, $service, $client] = $this->fixture();
        $sheet = $this->prescription($user, $service, $client);
        $this->administration($user, $service, $sheet, 'A', true);
        $this->bindRole(RoleResolver::MANAGER);
        $response = $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/reports/export', [
                'report_type' => 'administrations', 'period_start' => now()->toDateString(),
                'period_end' => now()->toDateString(), 'identifiable' => 1,
                'reason' => 'Authorised investigation of an administration record.', 'authorised' => 1,
            ])->assertOk();

        $this->assertStringContainsString($client->name, $response->streamedContent());
        $event = Frontend4ReportExportEvent::where('service_id', $service->id)->latest('id')->firstOrFail();
        $this->assertTrue($event->identifiable);
        $this->expectException(LogicException::class);
        $event->delete();
    }

    public function test_export_requires_a_reason_and_authorisation_confirmation(): void
    {
        [$user, $service] = $this->fixture();
        $this->bindRole(RoleResolver::MANAGER);
        $before = Frontend4ReportExportEvent::where('service_id', $service->id)->count();
        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/reports/export', [
                'report_type' => 'overview', 'period_start' => now()->subDay()->toDateString(),
                'period_end' => now()->toDateString(), 'identifiable' => 0, 'reason' => 'short',
            ])->assertSessionHasErrors(['reason', 'authorised']);
        $this->assertSame($before, Frontend4ReportExportEvent::where('service_id', $service->id)->count());
    }

    public function test_reporting_period_is_bounded_and_cannot_silently_truncate_history(): void
    {
        [$user, $service] = $this->fixture();
        $this->bindRole(RoleResolver::MANAGER);
        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->get('/frontend4/reports?start='.now()->subDays(400)->toDateString().'&end='.now()->toDateString())
            ->assertSessionHasErrors('period_end');
    }

    public function test_report_rows_cannot_cross_the_active_service_boundary(): void
    {
        [$user, $service, $client] = $this->fixture();
        $other = Frontend4MedicationIncident::create([
            'organisation_id' => $service->admin_id, 'service_id' => ((int) $service->id) + 1000000,
            'location_id' => null, 'client_id' => $client->id, 'category' => 'other', 'severity' => 'low',
            'description' => 'Out-of-scope incident marker '.bin2hex(random_bytes(6)),
            'immediate_action' => 'No action in the active service.', 'status' => 'reported',
            'reported_by_user_id' => $user->id, 'reported_at' => now(),
        ]);
        $rows = app(AssuranceReportingService::class)->exportRows('incidents', true, $user,
            app(AccessContext::class), CarbonImmutable::today(), CarbonImmutable::today());
        $this->assertStringNotContainsString($other->description, json_encode($rows));
    }

    private function administration(Frontend4User $user, Home $service, MARSheet $sheet, string $code, bool $given): MARAdministration
    {
        $row = new MARAdministration();
        $row->home_id = $service->id; $row->mar_sheet_id = $sheet->id;
        $row->date = now()->toDateString(); $row->time_slot = now()->format('H:i');
        $row->administered_at = now(); $row->given = $given; $row->code = $code;
        $row->reason = $given ? null : 'Person declined after support was offered';
        $row->administered_by = $user->id; $row->is_current = 1; $row->save();
        return $row;
    }

    private function prescription(Frontend4User $user, Home $service, ServiceUser $client): MARSheet
    {
        $medicine = MedicineCatalogue::create([
            'dmd_code' => (string) random_int(100000000000000000, 999999999999999999),
            'dmd_concept_level' => 'VMP', 'name' => 'R8 verification medicine '.bin2hex(random_bytes(4)),
            'form' => 'Tablet', 'default_route' => 'Oral', 'countable_unit' => 'tablet',
            'is_controlled' => 0, 'dmd_status' => 'current', 'is_local' => 0,
        ]);
        return app(PrescriptionRecordService::class)->create($client, $medicine, [
            'medication_name_as_written' => 'Verified R8 label', 'dose_amount' => 1, 'dose_unit' => 'tablet',
            'route' => 'Oral', 'frequency' => 'Once daily', 'time_slots' => ['08:00'], 'as_required' => false,
            'prn_details' => null, 'prn_max_daily' => null, 'prn_min_interval_hours' => null,
            'reason_for_medication' => 'Verification', 'administration_instructions' => null,
            'prescriber' => 'Test prescriber', 'pharmacy' => null, 'start_date' => now()->toDateString(),
            'end_date' => null, 'review_due_date' => now()->addMonth()->toDateString(),
            'prescription_source' => 'paper_prescription',
        ], $user, (int) $service->admin_id, (int) $service->id);
    }

    private function fixture(): array
    {
        $this->requireSchema();
        foreach (Frontend4User::where('status', 1)->where('is_deleted', 0)->get() as $user) {
            foreach (Home::where('is_deleted', 0)->get() as $service) {
                if (! in_array((int) $service->id, app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id), true)) continue;
                session($this->sessionFor($user, $service));
                $client = app(AccessContext::class)->scopeClients(ServiceUser::query(), $user)->where('is_deleted', 0)->first();
                if ($client) return [$user, $service, $client];
            }
        }
        $this->markTestSkipped('The fixture database has no suitable Frontend 4 user, service and client.');
    }

    private function sessionFor(Frontend4User $user, Home $service): array
    {
        $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id);
        return ['frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $service->id, 'frontend4.allowed_service_ids' => $allowed,
            'frontend4.active_location_id' => null, 'frontend4.active_home_id' => (int) $service->id,
            'frontend4.allowed_home_ids' => $allowed, 'frontend4.last_activity' => time()];
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
        foreach (['frontend4_assurance_reviews', 'frontend4_report_export_events', 'frontend4_follow_up_tasks',
            'frontend4_medication_incidents', 'frontend4_prescription_events', 'medicine_catalogue'] as $table) {
            if (! Schema::hasTable($table)) $this->markTestSkipped('Run the Frontend 4 R6-R8 migrations first.');
        }
    }
}
