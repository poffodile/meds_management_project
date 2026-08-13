<?php

namespace App\Services\Frontend4;

use App\Models\ControlledDrugRegister;
use App\Models\Frontend4ClinicalEvent;
use App\Models\Frontend4FollowUpTask;
use App\Models\Frontend4Handover;
use App\Models\Frontend4HandoverAcknowledgement;
use App\Models\Frontend4MedicationIncident;
use App\Models\Frontend4PrescriptionEvent;
use App\Models\Frontend4User;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HandoverIncidentService
{
    public function createDraft(
        int $organisationId,
        int $serviceId,
        ?int $locationId,
        Carbon $start,
        Carbon $end,
        Frontend4User $actor,
        array $allowedClientIds
    ): Frontend4Handover {
        if ($end->lessThanOrEqualTo($start) || $start->diffInHours($end) > 24) {
            throw ValidationException::withMessages(['shift_end' => 'The handover window must be after its start and no longer than 24 hours.']);
        }

        return DB::transaction(function () use ($organisationId, $serviceId, $locationId, $start, $end, $actor, $allowedClientIds) {
            $existing = Frontend4Handover::forContext($organisationId, $serviceId)
                ->where('status', 'draft')->where('created_by_user_id', $actor->id)
                ->where('shift_start', $start)->where('shift_end', $end)->lockForUpdate()->first();
            if ($existing) {
                return $existing->load('items');
            }

            $handover = Frontend4Handover::create([
                'organisation_id' => $organisationId, 'service_id' => $serviceId,
                'location_id' => $locationId, 'shift_start' => $start, 'shift_end' => $end,
                'status' => 'draft', 'created_by_user_id' => $actor->id,
            ]);

            foreach ($this->sourceItems($serviceId, $start, $end, $allowedClientIds) as $item) {
                $handoverItem = $handover->items()->create($item);
                if ($handoverItem->category === 'controlled_drug') {
                    $incident = Frontend4MedicationIncident::firstOrCreate([
                        'service_id' => $serviceId, 'source_type' => $handoverItem->source_type,
                        'source_id' => $handoverItem->source_id,
                    ], [
                        'organisation_id' => $organisationId, 'location_id' => $locationId,
                        'handover_item_id' => $handoverItem->id, 'client_id' => $handoverItem->client_id,
                        'mar_sheet_id' => $handoverItem->mar_sheet_id, 'category' => 'controlled_drug',
                        'severity' => 'high', 'description' => $handoverItem->summary.' — '.$handoverItem->detail,
                        'immediate_action' => 'Automatically escalated for manager investigation; complete an immediate witnessed physical check.',
                        'status' => 'reported', 'reported_by_user_id' => $actor->id, 'reported_at' => now(),
                    ]);
                    if ($incident->wasRecentlyCreated) {
                        $this->event($organisationId, $serviceId, 'incident', $incident->id, 'reported', $actor, [
                            'automatic' => true, 'source_type' => $handoverItem->source_type, 'source_id' => $handoverItem->source_id,
                        ]);
                    }
                }
            }
            $this->event($organisationId, $serviceId, 'handover', $handover->id, 'draft_created', $actor, [
                'shift_start' => $start->toIso8601String(), 'shift_end' => $end->toIso8601String(),
                'source_item_count' => $handover->items()->count(),
            ]);

            return $handover->load('items');
        });
    }

    public function updateDraft(Frontend4Handover $handover, ?string $notes, Frontend4User $actor): Frontend4Handover
    {
        if ($handover->status !== 'draft') {
            throw ValidationException::withMessages(['handover' => 'A submitted handover cannot be edited. Add a follow-up task or incident instead.']);
        }
        $handover->general_notes = $notes;
        $handover->save();
        $this->eventFor($handover, 'draft_notes_updated', $actor);
        return $handover;
    }

    public function submit(Frontend4Handover $handover, Frontend4User $actor): Frontend4Handover
    {
        return DB::transaction(function () use ($handover, $actor) {
            $row = Frontend4Handover::whereKey($handover->id)->lockForUpdate()->firstOrFail();
            if ($row->status !== 'draft') {
                throw ValidationException::withMessages(['handover' => 'Only a draft can be submitted.']);
            }
            if (! $row->items()->exists() && trim((string) $row->general_notes) === '') {
                throw ValidationException::withMessages(['handover' => 'There is nothing to hand over. Add a note or use a window containing source records.']);
            }
            $row->status = 'submitted';
            $row->submitted_by_user_id = $actor->id;
            $row->submitted_at = now();
            $row->save();
            $this->eventFor($row, 'submitted', $actor, ['item_count' => $row->items()->count()]);
            return $row;
        });
    }

    public function acknowledge(Frontend4Handover $handover, Frontend4User $actor): Frontend4HandoverAcknowledgement
    {
        return DB::transaction(function () use ($handover, $actor) {
            $row = Frontend4Handover::whereKey($handover->id)->lockForUpdate()->firstOrFail();
            if (! in_array($row->status, ['submitted', 'acknowledged'], true)) {
                throw ValidationException::withMessages(['handover' => 'Only a submitted handover can be acknowledged.']);
            }
            $ack = Frontend4HandoverAcknowledgement::firstOrCreate(
                ['handover_id' => $row->id, 'user_id' => $actor->id],
                ['acknowledged_at' => now()]
            );
            if ($row->status === 'submitted') {
                $row->status = 'acknowledged';
                $row->save();
            }
            if ($ack->wasRecentlyCreated) {
                $this->eventFor($row, 'acknowledged', $actor);
            }
            return $ack;
        });
    }

    public function createTask(array $data, Frontend4User $actor, int $organisationId, int $serviceId, ?int $locationId): Frontend4FollowUpTask
    {
        $task = Frontend4FollowUpTask::create($data + [
            'organisation_id' => $organisationId, 'service_id' => $serviceId,
            'location_id' => $locationId, 'status' => 'open', 'created_by_user_id' => $actor->id,
        ]);
        $this->event($organisationId, $serviceId, 'task', $task->id, 'created', $actor, [
            'owner_user_id' => $task->owner_user_id, 'due_at' => $task->due_at?->toIso8601String(),
            'escalate_at' => $task->escalate_at?->toIso8601String(),
        ]);
        return $task;
    }

    public function completeTask(Frontend4FollowUpTask $task, string $note, Frontend4User $actor): Frontend4FollowUpTask
    {
        return DB::transaction(function () use ($task, $note, $actor) {
            $row = Frontend4FollowUpTask::whereKey($task->id)->lockForUpdate()->firstOrFail();
            if ($row->status !== 'open') {
                throw ValidationException::withMessages(['task' => 'Only an open task can be completed.']);
            }
            $row->status = 'completed';
            $row->completion_note = $note;
            $row->completed_by_user_id = $actor->id;
            $row->completed_at = now();
            $row->save();
            $this->event($row->organisation_id, $row->service_id, 'task', $row->id, 'completed', $actor, ['note' => $note]);
            return $row;
        });
    }

    public function reportIncident(array $data, Frontend4User $actor, int $organisationId, int $serviceId, ?int $locationId): Frontend4MedicationIncident
    {
        $incident = Frontend4MedicationIncident::create($data + [
            'organisation_id' => $organisationId, 'service_id' => $serviceId,
            'location_id' => $locationId, 'status' => 'reported',
            'reported_by_user_id' => $actor->id, 'reported_at' => now(),
        ]);
        $this->event($organisationId, $serviceId, 'incident', $incident->id, 'reported', $actor, [
            'category' => $incident->category, 'severity' => $incident->severity,
            'source_type' => $incident->source_type, 'source_id' => $incident->source_id,
        ]);
        return $incident;
    }

    public function investigate(Frontend4MedicationIncident $incident, Frontend4User $actor): Frontend4MedicationIncident
    {
        if ($incident->status !== 'reported') {
            throw ValidationException::withMessages(['incident' => 'Only a reported incident can enter investigation.']);
        }
        $incident->status = 'investigating';
        $incident->investigator_user_id = $actor->id;
        $incident->save();
        $this->eventForIncident($incident, 'investigation_started', $actor);
        return $incident;
    }

    public function closeIncident(Frontend4MedicationIncident $incident, string $outcome, string $learning, Frontend4User $actor): Frontend4MedicationIncident
    {
        if (! in_array($incident->status, ['reported', 'investigating'], true)) {
            throw ValidationException::withMessages(['incident' => 'This incident is already closed.']);
        }
        $incident->status = 'closed';
        $incident->outcome = $outcome;
        $incident->learning = $learning;
        $incident->closed_by_user_id = $actor->id;
        $incident->closed_at = now();
        $incident->save();
        $this->eventForIncident($incident, 'closed', $actor, ['outcome' => $outcome, 'learning' => $learning]);
        return $incident;
    }

    private function sourceItems(int $serviceId, Carbon $start, Carbon $end, array $allowedClientIds): array
    {
        $items = [];
        $administrations = MARAdministration::forHome($serviceId)
            ->with('marSheet')->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereHas('marSheet', fn ($q) => $q->whereIn('client_id', $allowedClientIds))->get();

        foreach ($administrations as $record) {
            $sheet = $record->marSheet;
            $occurred = $record->administered_at ?: Carbon::parse($record->date->format('Y-m-d').' '.($record->time_slot ?: '00:00'));
            if ($occurred->lt($start) || $occurred->gt($end)) continue;
            if (in_array($record->code, ['R', 'W', 'N', 'O'], true) || $record->is_late) {
                $label = ['R' => 'Refused', 'W' => 'Withheld', 'N' => 'Not available', 'O' => 'Other outcome'][$record->code] ?? 'Given late';
                $items[] = $this->item('dose_exception', 'mar_administration', $record->id, $sheet, $occurred,
                    $record->code === 'N' ? 'urgent' : 'high', $label.': '.$sheet->medication_name,
                    $record->reason ?: $record->notes, true);
            }
            if ($sheet->as_required && $record->given) {
                $items[] = $this->item('prn_review', 'mar_administration', $record->id, $sheet, $occurred,
                    'normal', 'Review PRN effect: '.$sheet->medication_name,
                    trim(($record->dose_given ?: '').($record->reason ? ' · '.$record->reason : '')), true);
            }
        }

        foreach (ControlledDrugRegister::forHome($serviceId)->where('is_discrepancy', 1)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])->get() as $entry) {
            if (! in_array((int) $entry->client_id, $allowedClientIds, true)) continue;
            $at = Carbon::parse($entry->entry_date->format('Y-m-d').' '.$entry->entry_time);
            $items[] = [
                'category' => 'controlled_drug', 'source_type' => 'controlled_drug_register', 'source_id' => $entry->id,
                'source_key' => 'controlled_drug_register:'.$entry->id, 'client_id' => $entry->client_id,
                'mar_sheet_id' => $entry->mar_sheet_id, 'occurred_at' => $at, 'priority' => 'urgent',
                'summary' => 'Controlled-drug discrepancy: '.$entry->medication_name,
                'detail' => 'Recorded balance '.$entry->balance_after.' '.$entry->unit, 'requires_action' => true,
            ];
        }

        foreach (MARSheet::forHome($serviceId)->active()->currentlyActive()->whereIn('client_id', $allowedClientIds)
            ->whereNotNull('reorder_level')->whereColumn('stock_level', '<=', 'reorder_level')->get() as $sheet) {
            $items[] = $this->item('stock', 'mar_sheet', $sheet->id, $sheet, $end, $sheet->stock_level <= 0 ? 'urgent' : 'high',
                ($sheet->stock_level <= 0 ? 'Out of stock: ' : 'Low stock: ').$sheet->medication_name,
                $sheet->stock_level.' '.($sheet->unit ?: 'units').' remaining', true);
        }

        foreach (Frontend4PrescriptionEvent::where('service_id', $serviceId)->whereIn('client_id', $allowedClientIds)
            ->whereBetween('created_at', [$start, $end])->get() as $event) {
            $items[] = [
                'category' => 'prescription', 'source_type' => 'frontend4_prescription_event', 'source_id' => $event->id,
                'source_key' => 'frontend4_prescription_event:'.$event->id, 'client_id' => $event->client_id,
                'mar_sheet_id' => $event->mar_sheet_id, 'occurred_at' => $event->created_at, 'priority' => 'normal',
                'summary' => 'Prescription '.$event->event_type, 'detail' => $event->reason, 'requires_action' => false,
            ];
        }

        usort($items, fn ($a, $b) => strcmp((string) $a['occurred_at'], (string) $b['occurred_at']));
        return $items;
    }

    private function item(string $category, string $sourceType, int $sourceId, MARSheet $sheet, Carbon $at, string $priority, string $summary, ?string $detail, bool $action): array
    {
        return ['category' => $category, 'source_type' => $sourceType, 'source_id' => $sourceId,
            'source_key' => $sourceType.':'.$sourceId.':'.$category, 'client_id' => $sheet->client_id,
            'mar_sheet_id' => $sheet->id, 'occurred_at' => $at, 'priority' => $priority,
            'summary' => $summary, 'detail' => $detail, 'requires_action' => $action];
    }

    private function eventFor(Frontend4Handover $handover, string $type, Frontend4User $actor, array $metadata = []): void
    { $this->event($handover->organisation_id, $handover->service_id, 'handover', $handover->id, $type, $actor, $metadata); }
    private function eventForIncident(Frontend4MedicationIncident $incident, string $type, Frontend4User $actor, array $metadata = []): void
    { $this->event($incident->organisation_id, $incident->service_id, 'incident', $incident->id, $type, $actor, $metadata); }
    private function event(int $organisationId, int $serviceId, string $subjectType, int $subjectId, string $type, Frontend4User $actor, array $metadata = []): void
    {
        Frontend4ClinicalEvent::create(['organisation_id' => $organisationId, 'service_id' => $serviceId,
            'subject_type' => $subjectType, 'subject_id' => $subjectId, 'event_type' => $type,
            'actor_user_id' => $actor->id, 'metadata' => $metadata ?: null, 'occurred_at' => now()]);
    }
}
