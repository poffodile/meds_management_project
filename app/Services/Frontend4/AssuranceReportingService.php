<?php

namespace App\Services\Frontend4;

use App\Models\ControlledDrugRegister;
use App\Models\Frontend4FollowUpTask;
use App\Models\Frontend4MedicationIncident;
use App\Models\Frontend4PrescriptionEvent;
use App\Models\Frontend4User;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\ServiceUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AssuranceReportingService
{
    public const REPORT_TYPES = [
        'overview', 'administrations', 'exceptions', 'incidents',
        'tasks', 'prescriptions', 'controlled_drugs', 'stock',
    ];

    public function metrics(
        Frontend4User $user,
        AccessContext $context,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $serviceId = $context->serviceId();
        $clientIds = $this->clientIds($user, $context);
        $availability = [
            'administrations' => Schema::hasTable('mar_administrations') && Schema::hasTable('mar_sheets'),
            'stock' => Schema::hasTable('mar_sheets'),
            'controlledDrugs' => Schema::hasTable('controlled_drug_register'),
            'tasks' => Schema::hasTable('frontend4_follow_up_tasks'),
            'incidents' => Schema::hasTable('frontend4_medication_incidents'),
            'prescriptions' => Schema::hasTable('frontend4_prescription_events'),
        ];

        $administrations = $availability['administrations']
            ? $this->administrations($serviceId, $clientIds, $start, $end)
            : null;
        $tasks = $availability['tasks']
            ? $this->contextRows(Frontend4FollowUpTask::forContext($context->organisationId(), $serviceId), $context, $clientIds)
            : null;
        $incidents = $availability['incidents']
            ? $this->contextRows(Frontend4MedicationIncident::forContext($context->organisationId(), $serviceId), $context, $clientIds)
            : null;

        $values = [
            'administrationRecords' => $administrations?->count(),
            'givenRecords' => $administrations ? (clone $administrations)->where('given', 1)->count() : null,
            'notGivenRecords' => $administrations ? (clone $administrations)->whereIn('code', ['R', 'W', 'N', 'O'])->count() : null,
            'lateRecords' => $administrations ? (clone $administrations)->where('is_late', 1)->count() : null,
            'prnRecords' => $administrations ? (clone $administrations)->where('given', 1)
                ->whereHas('marSheet', fn ($query) => $query->where('as_required', 1))->count() : null,
            'lowStockCount' => $availability['stock'] ? MARSheet::forHome($serviceId)->active()->currentlyActive()
                ->whereIn('client_id', $clientIds)->whereNotNull('reorder_level')
                ->whereColumn('stock_level', '<=', 'reorder_level')->count() : null,
            'cdDiscrepancyCount' => $availability['controlledDrugs'] ? ControlledDrugRegister::forHome($serviceId)
                ->whereIn('client_id', $clientIds)->where('is_discrepancy', 1)
                ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])->count() : null,
            'openTaskCount' => $tasks ? (clone $tasks)->where('status', 'open')->count() : null,
            'overdueTaskCount' => $tasks ? (clone $tasks)->where('status', 'open')->where('due_at', '<', now())->count() : null,
            'openIncidentCount' => $incidents ? (clone $incidents)->where('status', '!=', 'closed')->count() : null,
            'highRiskIncidentCount' => $incidents ? (clone $incidents)->where('status', '!=', 'closed')
                ->whereIn('severity', ['high', 'critical'])->count() : null,
            'prescriptionEventCount' => $availability['prescriptions'] ? Frontend4PrescriptionEvent::where('service_id', $serviceId)
                ->whereIn('client_id', $clientIds)->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])->count() : null,
        ];

        return [
            'periodStart' => $start->toDateString(),
            'periodEnd' => $end->toDateString(),
            'values' => $values,
            'availability' => $availability,
            'allAvailable' => ! in_array(false, $availability, true),
        ];
    }

    public function exportRows(
        string $type,
        bool $identifiable,
        Frontend4User $user,
        AccessContext $context,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        if (! in_array($type, self::REPORT_TYPES, true)) {
            throw ValidationException::withMessages(['report_type' => 'Choose an available report.']);
        }

        $metrics = $this->metrics($user, $context, $start, $end);
        if (! $identifiable || $type === 'overview') {
            return $this->summaryRows($type, $metrics['values']);
        }

        $serviceId = $context->serviceId();
        $clientIds = $this->clientIds($user, $context);
        $names = ServiceUser::whereIn('id', $clientIds)->pluck('name', 'id');

        $rows = match ($type) {
            'administrations', 'exceptions' => $this->administrationRows($type, $serviceId, $clientIds, $start, $end, $names),
            'incidents' => $this->incidentRows($context, $clientIds, $names, $start, $end),
            'tasks' => $this->taskRows($context, $clientIds, $names, $start, $end),
            'prescriptions' => $this->prescriptionRows($serviceId, $clientIds, $start, $end, $names),
            'controlled_drugs' => $this->controlledDrugRows($serviceId, $clientIds, $start, $end, $names),
            'stock' => $this->stockRows($serviceId, $clientIds, $names),
            default => [],
        };

        if (count($rows) > 10000) {
            throw ValidationException::withMessages(['period_end' => 'This export contains more than 10,000 rows. Choose a shorter period.']);
        }

        return $rows;
    }

    private function clientIds(Frontend4User $user, AccessContext $context): array
    {
        return $context->scopeClients(ServiceUser::query(), $user)
            ->where('is_deleted', 0)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function administrations(int $serviceId, array $clientIds, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return MARAdministration::forHome($serviceId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereHas('marSheet', fn ($query) => $query->whereIn('client_id', $clientIds));
    }

    private function contextRows(Builder $query, AccessContext $context, array $clientIds): Builder
    {
        if ($context->locationId() === null) {
            return $query;
        }

        return $query->where(function ($scope) use ($context, $clientIds) {
            $scope->whereIn('client_id', $clientIds)
                ->orWhere(function ($serviceWide) use ($context) {
                    $serviceWide->whereNull('client_id')->where('location_id', $context->locationId());
                });
        });
    }

    private function summaryRows(string $type, array $values): array
    {
        $keys = match ($type) {
            'administrations' => ['administrationRecords', 'givenRecords', 'lateRecords'],
            'exceptions' => ['notGivenRecords', 'lateRecords', 'prnRecords'],
            'incidents' => ['openIncidentCount', 'highRiskIncidentCount'],
            'tasks' => ['openTaskCount', 'overdueTaskCount'],
            'prescriptions' => ['prescriptionEventCount'],
            'controlled_drugs' => ['cdDiscrepancyCount'],
            'stock' => ['lowStockCount'],
            default => array_keys($values),
        };

        return collect($keys)->map(fn ($key) => [
            'metric' => trim(preg_replace('/(?<!^)[A-Z]/', ' $0', $key)),
            'value' => $values[$key] === null ? 'Data unavailable' : $values[$key],
        ])->all();
    }

    private function administrationRows(string $type, int $serviceId, array $clientIds, CarbonImmutable $start, CarbonImmutable $end, $names): array
    {
        $query = $this->administrations($serviceId, $clientIds, $start, $end)->with('marSheet');
        if ($type === 'exceptions') {
            $query->where(fn ($exception) => $exception->whereIn('code', ['R', 'W', 'N', 'O'])->orWhere('is_late', 1));
        }
        return $query->orderBy('date')->orderBy('time_slot')->limit(10001)->get()->map(fn ($row) => [
            'record_id' => $row->id,
            'date' => $row->date?->format('Y-m-d'),
            'time_slot' => $row->time_slot,
            'client_id' => $row->marSheet?->client_id,
            'client_name' => $names[$row->marSheet?->client_id] ?? 'Unknown client',
            'medicine' => $row->marSheet?->medication_name,
            'outcome_code' => $row->code,
            'given' => $row->given ? 'Yes' : 'No',
            'late' => $row->is_late ? 'Yes' : 'No',
            'reason' => $row->reason,
            'administered_at' => $row->administered_at?->toIso8601String(),
            'recorded_by_user_id' => $row->administered_by,
        ])->all();
    }

    private function incidentRows(AccessContext $context, array $clientIds, $names, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return $this->contextRows(Frontend4MedicationIncident::forContext($context->organisationId(), $context->serviceId()), $context, $clientIds)
            ->whereBetween('reported_at', [$start->startOfDay(), $end->endOfDay()])
            ->orderByDesc('reported_at')->limit(10001)->get()->map(fn ($row) => [
                'incident_id' => $row->id, 'client_id' => $row->client_id,
                'client_name' => $names[$row->client_id] ?? ($row->client_id ? 'Unknown client' : 'Service-wide'),
                'category' => $row->category, 'severity' => $row->severity, 'status' => $row->status,
                'reported_at' => $row->reported_at?->toIso8601String(), 'reported_by_user_id' => $row->reported_by_user_id,
                'description' => $row->description, 'immediate_action' => $row->immediate_action,
                'outcome' => $row->outcome, 'learning' => $row->learning,
            ])->all();
    }

    private function taskRows(AccessContext $context, array $clientIds, $names, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return $this->contextRows(Frontend4FollowUpTask::forContext($context->organisationId(), $context->serviceId()), $context, $clientIds)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->orderBy('due_at')->limit(10001)->get()->map(fn ($row) => [
                'task_id' => $row->id, 'client_id' => $row->client_id,
                'client_name' => $names[$row->client_id] ?? ($row->client_id ? 'Unknown client' : 'Service-wide'),
                'task_type' => $row->task_type, 'title' => $row->title, 'priority' => $row->priority,
                'status' => $row->status, 'owner_user_id' => $row->owner_user_id,
                'due_at' => $row->due_at?->toIso8601String(), 'escalate_at' => $row->escalate_at?->toIso8601String(),
                'completed_at' => $row->completed_at?->toIso8601String(), 'completion_note' => $row->completion_note,
            ])->all();
    }

    private function prescriptionRows(int $serviceId, array $clientIds, CarbonImmutable $start, CarbonImmutable $end, $names): array
    {
        return Frontend4PrescriptionEvent::where('service_id', $serviceId)->whereIn('client_id', $clientIds)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])->orderBy('created_at')->limit(10001)->get()->map(fn ($row) => [
                'event_id' => $row->id, 'client_id' => $row->client_id, 'client_name' => $names[$row->client_id] ?? 'Unknown client',
                'mar_sheet_id' => $row->mar_sheet_id, 'event_type' => $row->event_type, 'reason' => $row->reason,
                'actor_user_id' => $row->actor_user_id, 'effective_at' => $row->effective_at?->toIso8601String(),
                'recorded_at' => $row->created_at?->toIso8601String(),
            ])->all();
    }

    private function controlledDrugRows(int $serviceId, array $clientIds, CarbonImmutable $start, CarbonImmutable $end, $names): array
    {
        return ControlledDrugRegister::forHome($serviceId)->whereIn('client_id', $clientIds)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])->orderBy('entry_date')->orderBy('entry_time')
            ->limit(10001)->get()->map(fn ($row) => [
                'entry_id' => $row->id, 'date' => $row->entry_date?->format('Y-m-d'), 'time' => $row->entry_time,
                'client_id' => $row->client_id, 'client_name' => $names[$row->client_id] ?? 'Unknown client',
                'medicine' => $row->medication_name, 'action' => $row->action_type, 'quantity' => $row->dose_quantity,
                'unit' => $row->unit, 'balance_before' => $row->balance_before, 'balance_after' => $row->balance_after,
                'discrepancy' => $row->is_discrepancy ? 'Yes' : 'No', 'witness' => $row->witness_name,
                'recorded_by_user_id' => $row->created_by_user_id,
            ])->all();
    }

    private function stockRows(int $serviceId, array $clientIds, $names): array
    {
        return MARSheet::forHome($serviceId)->active()->currentlyActive()->whereIn('client_id', $clientIds)
            ->orderBy('client_id')->orderBy('medication_name')->limit(10001)->get()->map(fn ($row) => [
                'mar_sheet_id' => $row->id, 'client_id' => $row->client_id,
                'client_name' => $names[$row->client_id] ?? 'Unknown client', 'medicine' => $row->medication_name,
                'stock_level' => $row->stock_level, 'reorder_level' => $row->reorder_level, 'unit' => $row->unit,
                'low_stock' => $row->reorder_level !== null && $row->stock_level <= $row->reorder_level ? 'Yes' : 'No',
                'expiry_date' => $row->expiry_date?->format('Y-m-d'),
            ])->all();
    }
}
