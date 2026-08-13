<?php

namespace Tests\Feature;

use App\Home;
use App\Models\ControlledDrugRegister;
use App\Models\Frontend4ClinicalEvent;
use App\Models\Frontend4FollowUpTask;
use App\Models\Frontend4Handover;
use App\Models\Frontend4MedicationIncident;
use App\Models\Frontend4User;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicineCatalogue;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\HandoverIncidentService;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\PrescriptionRecordService;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;
use Mockery;
use Tests\TestCase;

class Frontend4HandoverIncidentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_automatic_draft_links_real_dose_exception_without_copying_or_changing_it(): void
    {
        [$user, $service, $client] = $this->fixture();
        $sheet = $this->prescription($user, $service, $client);
        $administration = new MARAdministration();
        $administration->home_id = $service->id; $administration->mar_sheet_id = $sheet->id;
        $administration->date = now()->toDateString(); $administration->time_slot = now()->format('H:i');
        $administration->administered_at = now(); $administration->given = 0; $administration->code = 'R';
        $administration->reason = 'Person declined after support was offered'; $administration->is_current = 1;
        $administration->save();

        $handover = app(HandoverIncidentService::class)->createDraft((int) $service->admin_id, (int) $service->id, null,
            now()->subHour(), now()->addMinute(), $user, [$client->id]);

        $item = $handover->items()->where('source_type', 'mar_administration')->where('source_id', $administration->id)->firstOrFail();
        $this->assertSame('dose_exception', $item->category);
        $this->assertSame('R', $administration->fresh()->code);
        $this->assertSame('Person declined after support was offered', $administration->fresh()->reason);
    }

    public function test_submitted_handover_cannot_be_rewritten(): void
    {
        [$user, $service] = $this->fixture();
        $records = app(HandoverIncidentService::class);
        $handover = $this->handover($user, $service, 'Important verified shift note');
        $records->submit($handover, $user);
        $this->expectException(ValidationException::class);
        $records->updateDraft($handover->fresh(), 'Rewritten after submission', $user);
    }

    public function test_acknowledgement_confirms_receipt_but_does_not_complete_follow_up_work(): void
    {
        [$user, $service, $client] = $this->fixture();
        $records = app(HandoverIncidentService::class);
        $handover = $this->handover($user, $service, 'Medication review required');
        $records->submit($handover, $user);
        $records->acknowledge($handover->fresh(), $user);
        $task = $records->createTask($this->taskData($user, $client), $user, (int) $service->admin_id, (int) $service->id, null);
        $this->assertSame('acknowledged', $handover->fresh()->status);
        $this->assertSame('open', $task->fresh()->status);
    }

    public function test_follow_up_task_requires_owner_deadline_and_escalation_and_records_completion(): void
    {
        [$user, $service, $client] = $this->fixture();
        $records = app(HandoverIncidentService::class);
        $task = $records->createTask($this->taskData($user, $client), $user, (int) $service->admin_id, (int) $service->id, null);
        $this->assertSame($user->id, (int) $task->owner_user_id);
        $this->assertTrue($task->escalate_at->greaterThanOrEqualTo($task->due_at));
        $records->completeTask($task, 'GP advice received and prescription checked', $user);
        $this->assertSame('completed', $task->fresh()->status);
        $this->assertDatabaseHas('frontend4_clinical_events', ['subject_type' => 'task', 'subject_id' => $task->id, 'event_type' => 'completed']);
    }

    public function test_medication_incident_report_preserves_source_and_opens_an_investigation_record(): void
    {
        [$user, $service, $client] = $this->fixture();
        $incident = app(HandoverIncidentService::class)->reportIncident([
            'client_id' => $client->id, 'category' => 'omission', 'severity' => 'high',
            'description' => 'A scheduled medicine was not available during the round.',
            'immediate_action' => 'Shift lead informed and pharmacy contacted.',
            'source_type' => 'mar_administration', 'source_id' => 7123,
        ], $user, (int) $service->admin_id, (int) $service->id, null);
        $this->assertSame('reported', $incident->status);
        $this->assertSame('mar_administration', $incident->source_type);
        $this->assertDatabaseHas('frontend4_clinical_events', ['subject_type' => 'incident', 'subject_id' => $incident->id, 'event_type' => 'reported']);
    }

    public function test_controlled_drug_discrepancy_in_a_draft_automatically_opens_one_incident(): void
    {
        [$user, $service, $client] = $this->fixture();
        $sheet = $this->prescription($user, $service, $client);
        $entry = ControlledDrugRegister::create([
            'home_id' => $service->id, 'client_id' => $client->id, 'client_name' => $client->name,
            'mar_sheet_id' => $sheet->id, 'medication_name' => $sheet->medication_name,
            'cd_schedule' => '2', 'action_type' => 'adjustment', 'entry_date' => now()->toDateString(),
            'entry_time' => now()->format('H:i:s'), 'dose_quantity' => null, 'unit' => 'tablet',
            'balance_before' => 10, 'balance_after' => 9, 'is_discrepancy' => 1,
            'witness_name' => 'R7 test witness', 'notes' => 'Physical count did not reconcile',
            'created_by_user_id' => $user->id,
        ]);
        $records = app(HandoverIncidentService::class);
        $records->createDraft((int) $service->admin_id, (int) $service->id, null,
            now()->subHour(), now()->addMinute(), $user, [$client->id]);
        $this->assertDatabaseHas('frontend4_medication_incidents', [
            'service_id' => $service->id, 'source_type' => 'controlled_drug_register',
            'source_id' => $entry->id, 'category' => 'controlled_drug', 'status' => 'reported',
        ]);
        $this->assertSame(1, Frontend4MedicationIncident::where('service_id', $service->id)
            ->where('source_type', 'controlled_drug_register')->where('source_id', $entry->id)->count());
    }

    public function test_incident_closure_requires_outcome_and_learning_and_is_terminal(): void
    {
        [$user, $service, $client] = $this->fixture();
        $records = app(HandoverIncidentService::class);
        $incident = $records->reportIncident([
            'client_id' => $client->id, 'category' => 'stock', 'severity' => 'moderate',
            'description' => 'Recorded stock did not match the physical quantity checked.',
            'immediate_action' => 'Medicine isolated and a witnessed recount completed.',
        ], $user, (int) $service->admin_id, (int) $service->id, null);
        $records->investigate($incident, $user);
        $records->closeIncident($incident, 'Ledger corrected through the approved correction route.', 'Add a second-person count at receipt.', $user);
        $this->assertSame('closed', $incident->fresh()->status);
        $this->expectException(ValidationException::class);
        $records->closeIncident($incident->fresh(), 'Second closure attempt', 'No rewrite permitted', $user);
    }

    public function test_clinical_lifecycle_events_are_append_only(): void
    {
        [$user, $service] = $this->fixture();
        $handover = $this->handover($user, $service, 'Append-only event verification');
        $event = Frontend4ClinicalEvent::where('subject_type', 'handover')->where('subject_id', $handover->id)->firstOrFail();
        $this->expectException(LogicException::class);
        $event->update(['event_type' => 'rewritten']);
    }

    public function test_cross_service_handover_and_incident_urls_are_not_found(): void
    {
        [$user, $service] = $this->fixture();
        $other = Frontend4Handover::create(['organisation_id' => $service->admin_id,
            'service_id' => ((int) $service->id) + 1000000, 'shift_start' => now()->subHours(12),
            'shift_end' => now(), 'status' => 'submitted', 'general_notes' => 'Out-of-scope verification record',
            'created_by_user_id' => $user->id, 'submitted_by_user_id' => $user->id, 'submitted_at' => now()]);
        $this->bindRole(RoleResolver::CARER);
        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/handover/'.$other->id.'/acknowledge')->assertNotFound();
    }

    private function handover(Frontend4User $user, Home $service, string $notes): Frontend4Handover
    {
        $handover = Frontend4Handover::create(['organisation_id' => $service->admin_id, 'service_id' => $service->id,
            'shift_start' => now()->subHours(12), 'shift_end' => now(), 'status' => 'draft',
            'general_notes' => $notes, 'created_by_user_id' => $user->id]);
        Frontend4ClinicalEvent::create(['organisation_id' => $service->admin_id, 'service_id' => $service->id,
            'subject_type' => 'handover', 'subject_id' => $handover->id, 'event_type' => 'draft_created',
            'actor_user_id' => $user->id, 'occurred_at' => now()]);
        return $handover;
    }

    private function taskData(Frontend4User $user, ServiceUser $client): array
    { return ['client_id' => $client->id, 'task_type' => 'professional_advice', 'title' => 'Contact GP for advice',
        'instructions' => 'Record the advice received.', 'owner_user_id' => $user->id, 'priority' => 'high',
        'due_at' => now()->addHour(), 'escalate_at' => now()->addHours(2)]; }

    private function prescription(Frontend4User $user, Home $service, ServiceUser $client): MARSheet
    {
        $medicine = MedicineCatalogue::create(['dmd_code' => (string) random_int(100000000000000000, 999999999999999999),
            'dmd_concept_level' => 'VMP', 'name' => 'R7 verification medicine '.bin2hex(random_bytes(4)),
            'form' => 'Tablet', 'default_route' => 'Oral', 'countable_unit' => 'tablet', 'is_controlled' => 0,
            'dmd_status' => 'current', 'is_local' => 0]);
        return app(PrescriptionRecordService::class)->create($client, $medicine, [
            'medication_name_as_written' => 'Verified R7 label', 'dose_amount' => 1, 'dose_unit' => 'tablet',
            'route' => 'Oral', 'frequency' => 'Once daily', 'time_slots' => ['08:00'], 'as_required' => false,
            'prn_details' => null, 'prn_max_daily' => null, 'prn_min_interval_hours' => null,
            'reason_for_medication' => 'Test', 'administration_instructions' => null, 'prescriber' => 'Test prescriber',
            'pharmacy' => null, 'start_date' => now()->toDateString(), 'end_date' => null,
            'review_due_date' => now()->addMonth()->toDateString(), 'prescription_source' => 'paper_prescription',
        ], $user, (int) $service->admin_id, (int) $service->id);
    }

    private function fixture(): array
    {
        $this->requireSchema();
        foreach (Frontend4User::where('status', 1)->where('is_deleted', 0)->get() as $user) {
            foreach (Home::where('is_deleted', 0)->get() as $service) {
                if (! in_array((int) $service->id, app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id), true)) continue;
                session($this->sessionFor($user, $service));
                $client = app(AccessContext::class)->scopeClients(ServiceUser::query(), $user)->where('is_deleted', 0)
                    ->where(function ($q) { $q->where('lifecycle_status', 'active')->orWhere(fn ($legacy) => $legacy->whereNull('lifecycle_status')->where('status', 1)); })->first();
                if ($client) return [$user, $service, $client];
            }
        }
        $this->markTestSkipped('The fixture database has no suitable Frontend 4 user, service and client.');
    }

    private function sessionFor(Frontend4User $user, Home $service): array
    { $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id); return [
        'frontend4.organisation_id' => (int) $service->admin_id, 'frontend4.active_service_id' => (int) $service->id,
        'frontend4.allowed_service_ids' => $allowed, 'frontend4.active_location_id' => null,
        'frontend4.active_home_id' => (int) $service->id, 'frontend4.allowed_home_ids' => $allowed,
        'frontend4.last_activity' => time()]; }

    private function bindRole(string $role): void
    { $resolver = Mockery::mock(RoleResolver::class); $resolver->shouldReceive('resolve')->andReturn($role);
      $resolver->shouldReceive('label')->andReturn(RoleResolver::LABELS[$role]); $this->app->instance(RoleResolver::class, $resolver); }

    private function requireSchema(): void
    { foreach (['frontend4_handovers', 'frontend4_handover_items', 'frontend4_handover_acknowledgements', 'frontend4_follow_up_tasks', 'frontend4_medication_incidents', 'frontend4_clinical_events', 'medicine_catalogue'] as $table)
        if (! Schema::hasTable($table)) $this->markTestSkipped('Run the Frontend 4 R6 and R7 migrations first.'); }
}
