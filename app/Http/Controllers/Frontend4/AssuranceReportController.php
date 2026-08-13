<?php

namespace App\Http\Controllers\Frontend4;

use App\Home;
use App\Models\Frontend4AssuranceReview;
use App\Models\Frontend4ReportExportEvent;
use App\Models\Frontend4User;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\AssuranceReportingService;
use App\Services\Frontend4\Permissions;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AssuranceReportController extends F4Controller
{
    public function assurance(Request $request, AccessContext $context, AssuranceReportingService $reporting)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::VIEW_REPORTS);
        [$start, $end] = $this->period($request);
        $user = $this->user();
        $metrics = $reporting->metrics($user, $context, $start, $end);

        $history = Frontend4AssuranceReview::forContext($context->organisationId(), $context->serviceId())
            ->when($context->locationId() !== null, fn ($query) => $query->where('location_id', $context->locationId()))
            ->orderByDesc('reviewed_at')->limit(20)->get()->map(fn ($review) => [
                'id' => $review->id,
                'periodStart' => $review->period_start->format('Y-m-d'),
                'periodEnd' => $review->period_end->format('Y-m-d'),
                'reviewNote' => $review->review_note,
                'actionSummary' => $review->action_summary,
                'reviewedByUserId' => $review->reviewed_by_user_id,
                'reviewedAt' => $review->reviewed_at->format('d M Y H:i'),
                'snapshot' => $this->snapshot($review),
            ])->values();

        return Inertia::render('Assurance', $this->roleProps() + [
            'place' => $this->place($context), 'user' => $user->name,
            'metrics' => $metrics, 'history' => $history,
            'filters' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
        ]);
    }

    public function reports(Request $request, AccessContext $context, AssuranceReportingService $reporting)
    {
        $this->useF4Layout();
        $this->requirePermission(Permissions::VIEW_REPORTS);
        [$start, $end] = $this->period($request);
        $user = $this->user();

        $exports = Frontend4ReportExportEvent::forContext($context->organisationId(), $context->serviceId())
            ->when($context->locationId() !== null, fn ($query) => $query->where('location_id', $context->locationId()))
            ->orderByDesc('generated_at')->limit(20)->get()->map(fn ($event) => [
                'id' => $event->id, 'reportType' => $event->report_type,
                'periodStart' => $event->period_start->format('Y-m-d'), 'periodEnd' => $event->period_end->format('Y-m-d'),
                'identifiable' => $event->identifiable, 'recordCount' => $event->record_count,
                'reason' => $event->reason, 'requestedByUserId' => $event->requested_by_user_id,
                'generatedAt' => $event->generated_at->format('d M Y H:i'),
            ])->values();

        return Inertia::render('Reports', $this->roleProps() + [
            'place' => $this->place($context), 'user' => $user->name,
            'metrics' => $reporting->metrics($user, $context, $start, $end),
            'exports' => $exports,
            'reportTypes' => AssuranceReportingService::REPORT_TYPES,
            'filters' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
        ]);
    }

    public function review(Request $request, AccessContext $context, AssuranceReportingService $reporting)
    {
        $this->requirePermission(Permissions::COMPLETE_ASSURANCE_REVIEW);
        $data = $request->validate([
            'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'review_note' => ['required', 'string', 'min:10', 'max:8000'],
            'action_summary' => ['nullable', 'string', 'max:8000'],
        ]);
        [$start, $end] = $this->checkedPeriod($data['period_start'], $data['period_end']);
        $user = $this->user();
        $metrics = $reporting->metrics($user, $context, $start, $end);
        if (! $metrics['allAvailable']) {
            throw ValidationException::withMessages(['review_note' => 'A review cannot be signed while a source dataset is unavailable.']);
        }
        $values = $metrics['values'];

        Frontend4AssuranceReview::create([
            'organisation_id' => $context->organisationId(), 'service_id' => $context->serviceId(),
            'location_id' => $context->locationId(), 'period_start' => $start, 'period_end' => $end,
            'administration_records' => $values['administrationRecords'], 'given_records' => $values['givenRecords'],
            'not_given_records' => $values['notGivenRecords'], 'late_records' => $values['lateRecords'],
            'prn_records' => $values['prnRecords'], 'low_stock_count' => $values['lowStockCount'],
            'cd_discrepancy_count' => $values['cdDiscrepancyCount'], 'open_task_count' => $values['openTaskCount'],
            'overdue_task_count' => $values['overdueTaskCount'], 'open_incident_count' => $values['openIncidentCount'],
            'high_risk_incident_count' => $values['highRiskIncidentCount'],
            'prescription_event_count' => $values['prescriptionEventCount'],
            'review_note' => $data['review_note'], 'action_summary' => $data['action_summary'] ?? null,
            'reviewed_by_user_id' => $user->id, 'reviewed_at' => now(),
        ]);

        return redirect()->route('frontend4.assurance', ['start' => $start->toDateString(), 'end' => $end->toDateString()])
            ->with('success', 'Assurance review signed. The snapshot is append-only.');
    }

    public function export(Request $request, AccessContext $context, AssuranceReportingService $reporting): Response
    {
        $this->requirePermission(Permissions::EXPORT_REPORT);
        $data = $request->validate([
            'report_type' => ['required', Rule::in(AssuranceReportingService::REPORT_TYPES)],
            'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'identifiable' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'authorised' => ['accepted'],
        ]);
        [$start, $end] = $this->checkedPeriod($data['period_start'], $data['period_end']);
        $user = $this->user();
        $rows = $reporting->exportRows($data['report_type'], (bool) $data['identifiable'], $user, $context, $start, $end);

        Frontend4ReportExportEvent::create([
            'organisation_id' => $context->organisationId(), 'service_id' => $context->serviceId(),
            'location_id' => $context->locationId(), 'requested_by_user_id' => $user->id,
            'report_type' => $data['report_type'], 'period_start' => $start, 'period_end' => $end,
            'identifiable' => (bool) $data['identifiable'], 'format' => 'csv',
            'record_count' => count($rows), 'reason' => $data['reason'], 'generated_at' => now(),
        ]);

        $filename = 'care-one-'.$data['report_type'].'-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';
        $csv = "\xEF\xBB\xBF";
        if ($rows !== []) {
            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                abort(500, 'The report could not be generated.');
            }
            fputcsv($stream, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($stream, array_values($row));
            }
            rewind($stream);
            $csv .= stream_get_contents($stream);
            fclose($stream);
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function period(Request $request): array
    {
        return $this->checkedPeriod(
            (string) $request->query('start', now()->subDays(29)->toDateString()),
            (string) $request->query('end', now()->toDateString())
        );
    }

    private function checkedPeriod(string $start, string $end): array
    {
        try {
            $from = CarbonImmutable::parse($start)->startOfDay();
            $to = CarbonImmutable::parse($end)->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages(['period_start' => 'Enter a valid reporting period.']);
        }
        if ($to->lt($from)) throw ValidationException::withMessages(['period_end' => 'The end date must be on or after the start date.']);
        if ($from->diffInDays($to) > 365) throw ValidationException::withMessages(['period_end' => 'Reporting periods are limited to 366 days.']);
        return [$from, $to];
    }

    private function user(): Frontend4User
    {
        $user = Auth::guard('frontend4')->user();
        abort_unless($user instanceof Frontend4User, 403);
        return $user;
    }

    private function place(AccessContext $context): string
    {
        return Home::whereKey($context->serviceId())->value('title') ?: 'Current service';
    }

    private function snapshot(Frontend4AssuranceReview $review): array
    {
        return [
            'administrationRecords' => $review->administration_records, 'givenRecords' => $review->given_records,
            'notGivenRecords' => $review->not_given_records, 'lateRecords' => $review->late_records,
            'prnRecords' => $review->prn_records, 'lowStockCount' => $review->low_stock_count,
            'cdDiscrepancyCount' => $review->cd_discrepancy_count, 'openTaskCount' => $review->open_task_count,
            'overdueTaskCount' => $review->overdue_task_count, 'openIncidentCount' => $review->open_incident_count,
            'highRiskIncidentCount' => $review->high_risk_incident_count,
            'prescriptionEventCount' => $review->prescription_event_count,
        ];
    }
}
