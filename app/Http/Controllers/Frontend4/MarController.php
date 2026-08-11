<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicationStockTransaction;
use App\Services\Frontend4\Outcomes;
use App\Services\Frontend4\Permissions;
use App\Services\Medication\DoseOutcome;
use App\Services\Staff\MARSheetService;
use App\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * frontend4 — MAR sheet (Page 4), Slice A: the grid.
 *
 * The official medication administration record for one client: medicines down,
 * days across, a coded box per scheduled dose. Reached FROM the client profile
 * (owner decision 2026-08-05), not the sidebar — route
 * /frontend4/clients/:id/mar. It reuses the same `mar_administrations` the round
 * writes; this is a presentation of that record, not a second one.
 *
 * Read-only. Slices B–D (entry detail, corrections, export) come next.
 */
class MarController extends F4Controller
{
    use ResolvesCurrentHome;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::guard('frontend4')->check()) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    public function index(Request $request, int $client)
    {
        $this->useF4Layout();
        $this->requirePermission(\App\Services\Frontend4\Permissions::VIEW_MAR);
        $request->validate(['week_start' => 'nullable|date']);

        $homeId = $this->currentHomeId();
        $su = ServiceUser::where('home_id', $homeId)->where('is_deleted', 0)->where('id', $client)->first();
        if (! $su) {
            abort(404);
        }

        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->input('week_start'))->startOfWeek()
            : Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();
        $today = Carbon::now()->toDateString();

        $days = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $days[] = [
                'date' => $d->toDateString(),
                'dow' => $d->format('D'),
                'day' => $d->format('j'),
                'today' => $d->toDateString() === $today,
            ];
        }

        $sheets = MARSheet::forHome($homeId)->active()
            ->where('client_id', $su->id)
            ->orderBy('medication_name')
            ->get();

        $sheetIds = $sheets->pluck('id')->all();

        // All records (current AND superseded) so a cell can show its full detail
        // and its correction chain. Joined to `user` for staff and witness names.
        $rows = collect();
        if ($sheetIds) {
            $rows = DB::table('mar_administrations as a')
                ->leftJoin('user as u', 'u.id', '=', 'a.administered_by')
                ->leftJoin('user as w', 'w.id', '=', 'a.witnessed_by')
                ->whereIn('a.mar_sheet_id', $sheetIds)
                ->whereBetween('a.date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->orderBy('a.created_at')
                ->get([
                    'a.id', 'a.mar_sheet_id', 'a.time_slot', 'a.date', 'a.code', 'a.is_late',
                    'a.is_current', 'a.administered_at', 'a.dose_given', 'a.reason', 'a.notes',
                    'a.amendment_reason', 'a.created_at', 'u.name as staff', 'w.name as witness',
                ]);
        }

        $keyOf = fn ($r) => $r->mar_sheet_id.'|'.$r->time_slot.'|'.(string) $r->date;
        $currentByKey = $rows->where('is_current', 1)->keyBy($keyOf);
        $allByKey = $rows->groupBy($keyOf);

        $outcomes = app(Outcomes::class);

        // One record → a history line for the correction chain.
        $historyItem = fn ($r) => [
            'code' => $r->code,
            'label' => $outcomes->label($r->code),
            'status' => $outcomes->status($r->code),
            'when' => $this->fmtDateTime($r->created_at),
            'staff' => $r->staff,
            'amendmentReason' => $r->amendment_reason,
            'isCurrent' => (bool) $r->is_current,
        ];

        $meds = [];
        $scheduled = $given = $notGiven = $late = $outstanding = $prnGiven = 0;

        foreach ($sheets as $s) {
            $isCurrent = in_array(strtolower((string) $s->mar_status), ['active', 'paused'], true);
            $hasAdmin = $rows->contains(fn ($r) => (int) $r->mar_sheet_id === (int) $s->id);

            // A discontinued medicine with nothing recorded this week is not shown.
            if (! $isCurrent && ! $hasAdmin) {
                continue;
            }

            $slots = $s->as_required ? [] : (is_array($s->time_slots) ? array_values($s->time_slots) : []);
            $grid = [];

            foreach ($slots as $slot) {
                $grid[$slot] = [];
                foreach ($days as $day) {
                    $isPast = $day['date'] <= $today;
                    if ($isPast) {
                        $scheduled++;
                    }

                    $key = $s->id.'|'.$slot.'|'.$day['date'];
                    $cur = $currentByKey->get($key);

                    if ($cur) {
                        $chain = ($allByKey->get($key) ?? collect())->sortBy('created_at');
                        $history = $chain->count() > 1 ? $chain->map($historyItem)->values()->all() : [];

                        $grid[$slot][$day['date']] = [
                            'code' => $cur->code,
                            'label' => $outcomes->label($cur->code),
                            'status' => $outcomes->status($cur->code),
                            'late' => (bool) $cur->is_late,
                            'detail' => [
                                'scheduled' => $slot,
                                'actual' => $this->fmtTime($cur->administered_at),
                                'staff' => $cur->staff,
                                'witness' => $cur->witness,
                                'dose' => $cur->dose_given,
                                'reason' => $cur->reason,
                                'notes' => $cur->notes,
                                'late' => (bool) $cur->is_late,
                                'history' => $history,
                            ],
                        ];

                        $outcomes->isGiven($cur->code) ? $given++ : $notGiven++;
                        if ($cur->is_late) {
                            $late++;
                        }
                    } else {
                        $grid[$slot][$day['date']] = null;
                        if ($isPast) {
                            $outstanding++;
                        }
                    }
                }
            }

            // PRN: given-count per day, plus that day's doses for the detail panel.
            $prnByDay = [];
            if ($s->as_required) {
                foreach ($days as $day) {
                    $dayDoses = $rows->where('is_current', 1)
                        ->filter(fn ($r) => (int) $r->mar_sheet_id === (int) $s->id && (string) $r->date === $day['date'])
                        ->sortBy('created_at');
                    if ($dayDoses->isEmpty()) {
                        continue;
                    }
                    $n = $dayDoses->filter(fn ($r) => $outcomes->isGiven($r->code))->count();
                    $prnGiven += $n;
                    $prnByDay[$day['date']] = [
                        'count' => $n,
                        'doses' => $dayDoses->map(fn ($r) => [
                            'time' => $this->fmtTime($r->administered_at) ?: $r->time_slot,
                            'code' => $r->code,
                            'label' => $outcomes->label($r->code),
                            'status' => $outcomes->status($r->code),
                            'staff' => $r->staff,
                            'reason' => $r->reason,
                        ])->values()->all(),
                    ];
                }
            }

            $meds[] = [
                'id' => $s->id,
                'name' => $s->medication_name,
                'strength' => $s->dosage ?: null,
                'form' => $s->form ?: null,
                'dose' => $s->dose ?: null,
                'route' => $s->route ?: null,
                'asRequired' => (bool) $s->as_required,
                'isControlled' => (bool) $s->is_controlled,
                'slots' => $slots,
                'grid' => $grid,
                'prnByDay' => $prnByDay,
                'status' => $isCurrent ? (strtolower((string) $s->mar_status) === 'paused' ? 'paused' : 'active') : 'stopped',
            ];
        }

        // The legend — only the codes frontend4 can record, each with its meaning.
        $legend = [];
        foreach (['A', 'R', 'S', 'AW', 'N', 'W', 'OP', 'VO', 'NR', 'O'] as $code) {
            $legend[] = ['code' => $code, 'label' => $outcomes->label($code), 'status' => $outcomes->status($code)];
        }

        // The outcome choices for a correction, with which ones need a reason.
        $outcomeChoices = array_map(fn ($c) => [
            'code' => $c,
            'label' => $outcomes->label($c),
            'status' => $outcomes->status($c),
            'needsReason' => $outcomes->needsReason($c),
        ], $outcomes->codes());

        return Inertia::render('MarSheet', $this->roleProps() + [
            'terms' => ['person' => 'client', 'people' => 'clients', 'place' => 'home'],
            'place' => DB::table('home')->where('id', $homeId)->value('title') ?: 'Your home',
            'user' => Auth::user()->name ?? null,
            'client' => [
                'id' => $su->id,
                'name' => trim((string) $su->name) ?: ('Client #'.$su->id),
                'photo' => $su->image ? url('public/images/serviceUserProfileImages/'.$su->image) : null,
                'age' => $this->ageFromDob($su->date_of_birth),
                'location' => $su->room_number ?: null,
                'nhs' => $su->nhs_number ?: null,
                'allergies' => $su->allergies
                    ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
                    : [],
            ],
            'days' => $days,
            'weekStart' => $weekStart->toDateString(),
            'weekLabel' => $weekStart->format('j M').' – '.$weekEnd->format('j M Y'),
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'isThisWeek' => $weekStart->isSameWeek(Carbon::now()),
            'meds' => $meds,
            'summary' => [
                'scheduled' => $scheduled,
                'given' => $given,
                'notGiven' => $notGiven,
                'late' => $late,
                'outstanding' => max(0, $outstanding),
                'prn' => $prnGiven,
            ],
            'legend' => $legend,
            'outcomes' => $outcomeChoices,
        ]);
    }

    /**
     * Correct an administration on the MAR — shift lead and above.
     *
     * Uses the shared MARSheetService::administer(), which writes an APPEND-ONLY
     * amendment: a new row that supersedes the original, keeping the original and
     * its author forever (compliance CQC Reg 17). The correction is server-refused
     * without the `correct_record` permission and without an amendment reason.
     *
     * TWO DELIBERATE LIMITS (Slice C):
     *  - Controlled drugs are BLOCKED here — a CD correction has register/witness
     *    consequences and belongs to the controlled-drug workflow, not this path.
     *  - Stock is NOT reconciled by a correction (administer() never touches
     *    stock). A correction that changes whether the dose went in can therefore
     *    leave a stock discrepancy for the stock/discrepancy workflow to catch.
     *    Recorded as issue I19; do not mistake it for solved.
     */
    public function correct(Request $request, MARSheetService $marSheetService, int $client, int $sheet)
    {
        $this->requirePermission(Permissions::CORRECT_RECORD);

        $outcomes = app(Outcomes::class);

        $data = $request->validate([
            'date' => 'required|date',
            'time_slot' => 'required|string|max:16',
            'code' => 'required|string|in:'.implode(',', $outcomes->codes()),
            'reason' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'amendment_reason' => 'required|string|min:3|max:1000',
        ], [
            'amendment_reason.required' => 'Say why this record is being corrected — it is kept with the change.',
            'amendment_reason.min' => 'Give a little more detail so the correction is clear later.',
        ]);

        if ($outcomes->needsReason($data['code']) && trim((string) ($data['reason'] ?? '')) === '') {
            throw ValidationException::withMessages(['reason' => 'This outcome needs a reason.']);
        }

        $homeId = $this->currentHomeId();
        $userId = (int) Auth::id();

        // The whole correction runs in ONE transaction with the prescription row
        // LOCKED (I19/500 fix). The lock serialises rapid corrections — the shared
        // administer() takes no lock of its own, so two in quick succession could
        // otherwise collide (the transient 500 seen once). It also holds the stock
        // reconciliation and the amendment together: both land, or neither does.
        DB::transaction(function () use ($marSheetService, $data, $homeId, $userId, $client, $sheet, $outcomes) {
            $sheetRow = MARSheet::forHome($homeId)->active()
                ->where('id', $sheet)->where('client_id', $client)
                ->lockForUpdate()
                ->first();
            if (! $sheetRow) {
                abort(404);
            }

            if ($sheetRow->is_controlled) {
                throw ValidationException::withMessages([
                    'code' => 'Controlled-drug entries are corrected through the controlled-drug register, not here.',
                ]);
            }

            // Was the dose recorded as given before this correction?
            $existing = MARAdministration::where('mar_sheet_id', $sheetRow->id)
                ->where('date', $data['date'])
                ->where('time_slot', $data['time_slot'])
                ->where('is_current', 1)
                ->first();
            $oldGiven = $existing ? DoseOutcome::isGiven($existing->code) : false;
            $newGiven = DoseOutcome::isGiven($data['code']);

            // The append-only amendment (original preserved). Does not touch stock.
            $marSheetService->administer($sheetRow->id, [
                'date' => $data['date'],
                'time_slot' => $data['time_slot'],
                'code' => $data['code'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'amendment_reason' => $data['amendment_reason'],
            ], $homeId, $userId);

            // Reconcile stock when the correction changes whether the dose went in
            // (I19). Only for a tracked stock figure with a structured dose quantity —
            // otherwise there is nothing safe to move. Written through the same
            // audited ledger the round uses.
            $qty = ($sheetRow->dose_quantity !== null && (float) $sheetRow->dose_quantity > 0)
                ? (float) $sheetRow->dose_quantity : null;

            if ($qty !== null && ! is_null($sheetRow->stock_level) && $oldGiven !== $newGiven) {
                $clientName = ServiceUser::where('id', $client)->value('name');

                if ($newGiven) {
                    // Not-given → given: the dose was taken. Deduct it.
                    MedicationStockTransaction::apply($sheetRow, 'administered', $qty, $userId, [
                        'client_name' => $clientName,
                        'reason' => 'MAR correction — dose now recorded as given; stock reconciled.',
                        'notes' => 'Reconciled after a MAR correction.',
                    ]);
                } else {
                    // Given → not-given: the dose was not taken. Return the quantity by
                    // correcting the balance up to before + qty (an absolute recount).
                    MedicationStockTransaction::apply($sheetRow, 'correction', (float) $sheetRow->stock_level + $qty, $userId, [
                        'client_name' => $clientName,
                        'reason' => 'MAR correction — dose no longer recorded as given; quantity returned to stock.',
                        'notes' => 'Reconciled after a MAR correction.',
                    ]);
                }
            }
        });

        return redirect()
            ->route('frontend4.clients.mar', ['client' => $client, 'week_start' => $request->input('week_start')])
            ->with('success', 'Record corrected.');
    }

    private function ageFromDob($dob): ?int
    {
        if (! $dob || $dob === '0000-00-00') {
            return null;
        }
        try {
            $age = Carbon::parse($dob)->age;
        } catch (\Throwable $e) {
            return null;
        }

        return ($age >= 0 && $age < 130) ? $age : null;
    }

    private function fmtTime($d): ?string
    {
        if (! $d || str_starts_with((string) $d, '0000-00-00')) {
            return null;
        }
        try {
            return Carbon::parse($d)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fmtDateTime($d): ?string
    {
        if (! $d || str_starts_with((string) $d, '0000-00-00')) {
            return null;
        }
        try {
            return Carbon::parse($d)->format('j M Y, H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
