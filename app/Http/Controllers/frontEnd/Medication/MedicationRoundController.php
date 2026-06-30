<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicationRoundClosure;
use App\Models\MedicationStockTransaction;
use App\Models\ShiftHandover;
use App\Services\Staff\MARSheetService;
use App\ServiceUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MedicationRoundController extends Controller
{
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    /**
     * Time-of-day rounds. A medication belongs to a round when one of its
     * scheduled time slots falls within [start, end). Anything before 06:00
     * or from 18:00 onwards counts as Night.
     */
    private const ROUNDS = [
        'morning' => ['label' => 'Morning',   'start' => 6,  'end' => 12, 'icon' => 'fa-sun-o',     'window' => '06:00–12:00'],
        'lunchtime' => ['label' => 'Lunchtime', 'start' => 12, 'end' => 14, 'icon' => 'fa-coffee',    'window' => '12:00–14:00'],
        'evening' => ['label' => 'Evening',   'start' => 14, 'end' => 18, 'icon' => 'fa-cloud',     'window' => '14:00–18:00'],
        'night' => ['label' => 'Night',     'start' => 18, 'end' => 24, 'icon' => 'fa-moon-o',    'window' => '18:00–06:00'],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || ! in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    /** Resolve the carer's primary home (matches MARSheetController). */
    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    /** Which round does an "HH:MM" time fall into? */
    private function roundForTime(?string $time): string
    {
        if (! $time) {
            return 'night';
        }
        $hour = (int) substr($time, 0, 2);
        foreach (self::ROUNDS as $key => $cfg) {
            if ($key === 'night') {
                continue;
            }
            if ($hour >= $cfg['start'] && $hour < $cfg['end']) {
                return $key;
            }
        }

        return 'night';
    }

    public function index(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = $this->getHomeId();
        $date = $request->input('date', now()->toDateString());

        // Active prescriptions for this home, with the doses already recorded today.
        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($date) {
                $q->where('date', $date);
            }])
            ->orderBy('medication_name')
            ->get();

        // Resident names for the prescriptions on screen.
        $clientIds = $sheets->pluck('client_id')->unique()->values();
        $residentNames = ServiceUser::whereIn('id', $clientIds)
            ->pluck('name', 'id');

        // Build: round -> [ client_id => ['name' => , 'rows' => [ {sheet, slot, admin} ] ] ]
        $grid = [];
        foreach (array_keys(self::ROUNDS) as $roundKey) {
            $grid[$roundKey] = [];
        }

        foreach ($sheets as $sheet) {
            $slots = ! empty($sheet->time_slots) ? $sheet->time_slots : [null];
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');

            foreach ($slots as $slot) {
                $targetRounds = $slot !== null
                    ? [$this->roundForTime($slot)]
                    : array_keys(self::ROUNDS); // unscheduled / PRN meds appear in every round

                foreach ($targetRounds as $roundKey) {
                    $clientId = $sheet->client_id;
                    if (! isset($grid[$roundKey][$clientId])) {
                        $grid[$roundKey][$clientId] = [
                            'client_id' => $clientId,
                            'name' => $residentNames[$clientId] ?? ('Resident #'.$clientId),
                            'rows' => [],
                        ];
                    }
                    $grid[$roundKey][$clientId]['rows'][] = [
                        'sheet' => $sheet,
                        'slot' => $slot,
                        'admin' => $slot !== null ? $adminsBySlot->get($slot) : null,
                    ];
                }
            }
        }

        // Sort residents within each round by name, drop the keys for the view.
        foreach ($grid as $roundKey => $residents) {
            $grid[$roundKey] = collect($residents)->sortBy('name')->values()->all();
        }

        // Default the active tab to the round matching the current time.
        $currentRound = $this->roundForTime(now()->format('H:i'));

        return view('frontEnd.medication.medication_round.index', [
            'rounds' => self::ROUNDS,
            'grid' => $grid,
            'date' => $date,
            'currentRound' => $currentRound,
        ]);
    }

    /** React/Inertia version of the round grid. Same data, shaped into plain arrays. */
    public function indexReact(Request $request)
    {
        return Inertia::render('Medication/MedicationRound', $this->buildRoundProps($request));
    }

    /** Experimental copy of the round page — for trying alternative UIs without touching the main page. */
    public function indexReactLab(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab', $this->buildRoundProps($request));
    }

    /** Second experimental copy of the round page. */
    public function indexReactLab2(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab2', $this->buildRoundProps($request));
    }

    /** Experimental copy "lab 1.1" of the round page. */
    public function indexReactLab11(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab11', $this->buildRoundProps($request));
    }

    /** Experimental copy "lab 1.2" of the round page. */
    public function indexReactLab12(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab12', $this->buildRoundProps($request));
    }

    /** Experimental copy "lab 1.3" of the round page. */
    public function indexReactLab13(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab13', $this->buildRoundProps($request));
    }

    /** Experimental copy "lab 1.4" of the round page. */
    public function indexReactLab14(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab14', $this->buildRoundProps($request));
    }

    public function indexReactLab141(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab141', $this->buildRoundProps($request));
    }

    public function indexReactLab142(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab142', $this->buildRoundProps($request));
    }

    public function indexReactLab143(Request $request)
    {
        return Inertia::render('Medication/MedicationRoundLab143', $this->buildRoundProps($request));
    }

    /** Builds the shared props for the medication round React pages. */
    private function buildRoundProps(Request $request): array
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = $this->getHomeId();
        $date = $request->input('date', now()->toDateString());

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($date) {
                $q->where('date', $date)->with('administeredByUser:id,name');
            }])
            ->orderBy('medication_name')
            ->get();

        $clientIds = $sheets->pluck('client_id')->unique()->values();
        $residents = ServiceUser::whereIn('id', $clientIds)->get()->keyBy('id');

        // Per-resident regular / PRN medication counts (across all active sheets).
        $counts = [];
        foreach ($sheets as $s) {
            $cid = $s->client_id;
            if (! isset($counts[$cid])) {
                $counts[$cid] = ['regular' => 0, 'prn' => 0];
            }
            $s->as_required ? $counts[$cid]['prn']++ : $counts[$cid]['regular']++;
        }

        // Per-resident active risks (generic — surfaced with their impact level).
        $risksByClient = \DB::table('care_plan_risks')
            ->whereIn('client_id', $clientIds)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get(['client_id', 'description', 'impact'])
            ->groupBy('client_id');

        // Time context for the due / overdue / upcoming derivation (relative to "now").
        $today = now()->toDateString();
        $isToday = ($date === $today);
        $isPast = ($date < $today);
        $nowMin = (int) now()->format('H') * 60 + (int) now()->format('i');

        $grid = [];
        foreach (array_keys(self::ROUNDS) as $roundKey) {
            $grid[$roundKey] = [];
        }

        foreach ($sheets as $sheet) {
            $slots = ! empty($sheet->time_slots) ? $sheet->time_slots : [null];
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');

            // PRN ("as needed") state: how many given today, last given, next allowed.
            $prn = null;
            if ($sheet->as_required) {
                $given = $sheet->administrations->filter(fn ($a) => in_array($a->code, ['A', 'S'], true));
                $count = $given->count();
                $last = $given->sortByDesc('updated_at')->first()?->updated_at;
                $intervalH = (float) ($sheet->prn_min_interval_hours ?? 0);
                $next = ($last && $intervalH > 0) ? $last->copy()->addMinutes((int) round($intervalH * 60)) : null;
                $maxDaily = $sheet->prn_max_daily;
                $hitMax = $maxDaily !== null && $count >= (int) $maxDaily;
                $tooSoon = $isToday && $next && now()->lt($next);
                $prn = [
                    'max_daily' => $maxDaily !== null ? (int) $maxDaily : null,
                    'interval_hours' => $sheet->prn_min_interval_hours !== null ? (float) $sheet->prn_min_interval_hours : null,
                    'given_today' => $count,
                    'last_given' => $last?->format('H:i'),
                    'next_available' => $next?->format('H:i'),
                    'blocked' => $isToday && ($hitMax || $tooSoon),
                    'block_reason' => $hitMax ? 'Daily maximum reached' : ($tooSoon ? ('Not due until '.$next?->format('H:i')) : null),
                ];
            }

            foreach ($slots as $slot) {
                $targetRounds = $slot !== null ? [$this->roundForTime($slot)] : array_keys(self::ROUNDS);

                foreach ($targetRounds as $roundKey) {
                    $clientId = $sheet->client_id;
                    if (! isset($grid[$roundKey][$clientId])) {
                        $su = $residents->get($clientId);
                        $allergies = ($su && $su->allergies)
                            ? array_values(array_filter(array_map('trim', preg_split('/[,;]+/', $su->allergies))))
                            : [];
                        $riskFlags = ($risksByClient[$clientId] ?? collect())->map(function ($r) {
                            return ['label' => $r->description, 'level' => strtolower($r->impact ?: 'medium')];
                        })->values()->all();
                        $gender = ($su && $su->gender) ? (['M' => 'Male', 'F' => 'Female'][$su->gender] ?? $su->gender) : null;
                        $grid[$roundKey][$clientId] = [
                            'client_id' => $clientId,
                            'name' => $su->name ?? ('Resident #'.$clientId),
                            'photo' => ($su && $su->image) ? url('public/images/serviceUserProfileImages/'.$su->image) : null,
                            'dob' => $su->date_of_birth ?? null,
                            'gender' => $gender,
                            'weight' => ($su && $su->weight) ? $su->weight : null,
                            'weight_unit' => $su->weight_unit ?? null,
                            'room' => ($su && $su->room_number) ? $su->room_number : null,
                            'nhs' => ($su && $su->nhs_number) ? $su->nhs_number : null,
                            'mobility' => ($su && $su->suMobility) ? $su->suMobility : null,
                            'diet' => ($su && $su->diet) ? $su->diet : null,
                            'allergies' => $allergies,
                            'risk_flags' => $riskFlags,
                            'regular_count' => $counts[$clientId]['regular'] ?? 0,
                            'prn_count' => $counts[$clientId]['prn'] ?? 0,
                            'rows' => [],
                        ];
                    }
                    $admin = $slot !== null ? $adminsBySlot->get($slot) : null;
                    $grid[$roundKey][$clientId]['rows'][] = [
                        'mar_sheet_id' => $sheet->id,
                        'medication_name' => $sheet->medication_name,
                        'strength' => $sheet->dosage,
                        'dose' => $sheet->dose,
                        'route' => $sheet->route,
                        'instruction' => $sheet->prn_details ?: $sheet->reason_for_medication,
                        'slot' => $slot,
                        'stock' => $sheet->stock_level,
                        'low_stock' => ! is_null($sheet->stock_level) && ! is_null($sheet->reorder_level) && $sheet->stock_level <= $sheet->reorder_level,
                        'unit' => $sheet->unit,
                        'is_controlled' => (bool) $sheet->is_controlled,
                        'cd_schedule' => $sheet->cd_schedule,
                        'as_required' => (bool) $sheet->as_required,
                        'prn' => $prn,
                        'status' => $this->doseBucket($slot, $admin->code ?? null, $isToday, $isPast, $nowMin),
                        'code' => $admin->code ?? null,
                        'reason' => $admin?->reason,
                        'notes' => $admin?->notes,
                        'witnessed_by' => $admin?->witnessed_by,
                        'recorded_at' => $admin?->updated_at?->format('H:i'),
                        'dose_given' => $admin->dose_given ?? null,
                        'recorded_by' => ($admin && $admin->administeredByUser) ? $admin->administeredByUser->name : null,
                    ];
                }
            }
        }

        foreach ($grid as $roundKey => $residents) {
            $grid[$roundKey] = collect($residents)->sortBy('name')->values()->all();
        }

        $rounds = [];
        foreach (self::ROUNDS as $key => $cfg) {
            $rounds[] = ['key' => $key, 'label' => $cfg['label'], 'window' => $cfg['window']];
        }

        // Which rounds (for this home + date) have been ended, with who/when.
        $closures = MedicationRoundClosure::where('home_id', $homeId)->where('date', $date)
            ->with('closedByUser:id,name')->get()
            ->mapWithKeys(fn ($c) => [$c->round => [
                'by' => $c->closedByUser->name ?? null,
                'at' => $c->updated_at?->format('H:i'),
            ]])->all();

        return [
            'rounds' => $rounds,
            'grid' => $grid,
            'date' => $date,
            'currentRound' => $this->roundForTime(now()->format('H:i')),
            'closures' => $closures,
        ];
    }

    /**
     * Derive a dose's timing bucket relative to "now" (only meaningful for today):
     * completed | overdue | due_now (<=60m) | upcoming (<=120m) | later | due (PRN/unscheduled).
     */
    private function doseBucket($slot, $code, $isToday, $isPast, $nowMin)
    {
        if ($code) {
            return 'completed';
        }
        if ($slot === null || strpos((string) $slot, ':') === false) {
            return 'due'; // PRN / unscheduled
        }
        [$h, $m] = explode(':', $slot);
        $slotMin = (int) $h * 60 + (int) $m;
        if ($isPast) {
            return 'overdue';
        }
        if (! $isToday) {
            return 'later';
        }
        $diff = $slotMin - $nowMin;
        if ($diff < 0) {
            return 'overdue';
        }
        if ($diff <= 60) {
            return 'due_now';
        }
        if ($diff <= 120) {
            return 'upcoming';
        }

        return 'later';
    }

    /**
     * Record an administration via the existing MAR service, and auto-deduct stock when given.
     * Guards against double-deducting if an already-"Given" record is edited.
     */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        if (! $ok) {
            return response()->json(['ok' => false, 'message' => 'Prescription not found'], 404);
        }

        return response()->json(['ok' => true]);
    }

    /** Same record, but returns to the React/Inertia round page (keeping the date). */
    public function recordReact(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.react', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the experimental (lab) round page. */
    public function recordReactLab(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the second experimental (lab 2) round page. */
    public function recordReactLab2(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab2', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the "lab 1.1" round page. */
    public function recordReactLab11(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-1', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the "lab 1.2" round page. */
    public function recordReactLab12(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-2', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the "lab 1.3" round page. */
    public function recordReactLab13(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-3', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record + return to the "lab 1.4" round page. */
    public function recordReactLab14(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-4', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    public function recordReactLab141(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-4-1', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    public function recordReactLab142(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-4-2', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Ends (locks) a round for the home + date, recording who closed it. */
    public function endReactLab142(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('medication.medication-round.lab1-4-2', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
    }

    /** Flags a medication concern to the shift handover (appends to today's handover). */
    public function flagToHandoverLab142(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'concern' => 'required|string|max:1000',
            'client_id' => 'nullable|integer',
            'client_name' => 'nullable|string|max:255',
            'action_required' => 'nullable|boolean',
        ]);

        $homeId = $this->getHomeId();
        $date = $request->input('date');
        $user = Auth::user();

        // Append to today's handover for this home, creating a draft one if none exists.
        $handover = ShiftHandover::where('home_id', $homeId)->whereDate('handover_date', $date)->orderByDesc('id')->first();
        if (! $handover) {
            $handover = ShiftHandover::create([
                'home_id' => $homeId,
                'handover_date' => $date,
                'handover_time' => now()->format('H:i'),
                'from_carer_name' => $user->name,
                'status' => 'draft',
                'medication_concerns' => [],
                'created_by_user_id' => $user->id,
            ]);
        }

        $concerns = $handover->medication_concerns ?? [];
        $concerns[] = [
            'concern' => $request->input('concern'),
            'client_id' => $request->input('client_id'),
            'client_name' => $request->input('client_name'),
            'action_required' => $request->boolean('action_required') ? '1' : '0',
        ];
        $handover->medication_concerns = array_values($concerns);
        $handover->save();

        return redirect()->route('medication.medication-round.lab1-4-2', ['date' => $date])
            ->with('success', 'Flagged to shift handover.');
    }

    /** Re-opens (unlocks) an ended round — managers only. */
    public function reopenReactLab142(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('medication.medication-round.lab1-4-2', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }

    /**
     * Temporary absence — mark a resident away for a date range. Every scheduled
     * (non-PRN) dose in that window that hasn't already been recorded is logged as
     * Omitted (code O) with the absence reason, so it doesn't surface as "missed".
     * Locked/ended rounds are skipped.
     */
    public function temporaryAbsenceLab142(Request $request, MARSheetService $marSheetService)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'from' => 'required|date',
            'until' => 'required|date|after_or_equal:from',
            'reason' => 'required|string|max:200',
            'date' => 'nullable|date',
        ]);

        $homeId = $this->getHomeId();
        $userId = (int) Auth::id();
        $clientId = (int) $request->input('client_id');
        $from = Carbon::parse($request->input('from'))->startOfDay();
        $until = Carbon::parse($request->input('until'))->startOfDay();

        // Safety cap — never process more than 31 days in one go.
        if ($from->diffInDays($until) > 31) {
            $until = $from->copy()->addDays(31);
        }
        $reason = 'Temporary absence — '.trim((string) $request->input('reason'));

        $sheets = MARSheet::forHome($homeId)->active()
            ->where('client_id', $clientId)
            ->get();

        $count = 0;
        for ($day = $from->copy(); $day->lte($until); $day->addDay()) {
            $dateStr = $day->toDateString();
            foreach ($sheets as $sheet) {
                if ($sheet->as_required) {
                    continue; // PRN meds aren't scheduled
                }
                foreach (($sheet->time_slots ?: []) as $slot) {
                    $already = MARAdministration::where('mar_sheet_id', $sheet->id)
                        ->where('date', $dateStr)->where('time_slot', $slot)->exists();
                    if ($already) {
                        continue; // don't overwrite a real outcome
                    }
                    $round = $this->roundForTime($slot);
                    $locked = MedicationRoundClosure::where('home_id', $homeId)
                        ->where('date', $dateStr)->where('round', $round)->exists();
                    if ($locked) {
                        continue;
                    }
                    $marSheetService->administer($sheet->id, [
                        'date' => $dateStr, 'time_slot' => $slot, 'code' => 'O',
                        'dose_given' => null, 'witnessed_by' => null, 'reason' => $reason, 'notes' => null,
                    ], $homeId, $userId);
                    $count++;
                }
            }
        }

        return redirect()->route('medication.medication-round.lab1-4-2', ['date' => $request->input('date') ?? $from->toDateString()])
            ->with('success', "Temporary absence saved — {$count} dose(s) marked omitted.");
    }

    /**
     * Printable MAR chart for one resident over a date range (default: this month,
     * capped at 31 days). Returns a standalone Inertia page (no app shell) so it
     * prints cleanly. Grid = medication × time-slot rows, one column per day.
     */
    public function marReport(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $homeId = $this->getHomeId();
        $clientId = (int) $request->input('client_id');
        $from = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->endOfMonth()->toDateString()))->startOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($from->diffInDays($to) > 31) {
            $to = $from->copy()->addDays(31);
        }

        $days = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $days[] = $d->toDateString();
        }

        $client = \App\Models\ServiceUser::find($clientId);

        $sheets = MARSheet::forHome($homeId)->active()
            ->where('client_id', $clientId)
            ->orderBy('medication_name')
            ->get();

        $admins = MARAdministration::whereIn('mar_sheet_id', $sheets->pluck('id'))
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->groupBy('mar_sheet_id');

        $meds = $sheets->map(function ($s) use ($admins, $days) {
            $byKey = ($admins->get($s->id) ?? collect())->keyBy(fn ($a) => $a->date.'|'.$a->time_slot);
            $slots = $s->time_slots ?: ($s->as_required ? ['PRN'] : []);

            return [
                'medication_name' => $s->medication_name,
                'dosage' => $s->dosage,
                'is_controlled' => (bool) $s->is_controlled,
                'as_required' => (bool) $s->as_required,
                'slots' => collect($slots)->map(fn ($slot) => [
                    'slot' => $slot,
                    'cells' => collect($days)->map(fn ($day) => optional($byKey->get($day.'|'.$slot))->code)->all(),
                ])->all(),
            ];
        })->values();

        return Inertia::render('Medication/MarReport', [
            'resident' => $client ? [
                'id' => $client->id,
                'name' => $client->name,
                'dob' => optional($client->date_of_birth)->format('d M Y'),
            ] : null,
            'meds' => $meds,
            'days' => $days,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function recordReactLab143(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.lab1-4-3', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    // ---- Medication Round 4 — warm/editorial style (serif, donut, schedule timeline) ----

    /** Editorial-style medication round (serif headings, progress donut, schedule timeline). */
    public function indexMedsRound4(Request $request)
    {
        return Inertia::render('Medication/MedsRound4', $this->buildRoundProps($request));
    }

    /** Record a dose + return to the Medication Round 4 page (keeping the date). */
    public function recordMedsRound4(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.v4', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Ends (locks) a round for the home + date, recording who closed it. */
    public function endMedsRound4(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('medication.medication-round.v4', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
    }

    /** Re-opens (unlocks) an ended round — managers only. */
    public function reopenMedsRound4(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('medication.medication-round.v4', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }

    /** Record an administration + auto-deduct stock on a newly-given dose. Returns false if not found. */
    private function applyRecord(Request $request, MARSheetService $marSheetService): bool
    {
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'date' => 'required|date',
            'time_slot' => 'required|string|max:10',
            'code' => 'required|in:A,S,R,W,N,O',
            'dose_given' => 'nullable|string|max:100',
            'witnessed_by' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        // A refused / not-given / omitted dose must carry a reason (auditable record).
        if (in_array($request->input('code'), ['R', 'N', 'O'], true) && trim((string) $request->input('reason')) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Please give a reason when a dose is refused or not given.',
            ]);
        }

        $homeId = $this->getHomeId();
        $userId = (int) Auth::id();

        $sheet = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));
        if (! $sheet) {
            return false;
        }

        // A round that has been ended is locked — no further recording.
        $round = $this->roundForTime($request->input('time_slot'));
        if (MedicationRoundClosure::where('home_id', $homeId)->where('date', $request->input('date'))->where('round', $round)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'This round has been ended and is locked.',
            ]);
        }

        // Controlled drugs legally require a witness to administer ("Given").
        if ($sheet->is_controlled && $request->input('code') === 'A' && trim((string) $request->input('witnessed_by')) === '') {
            throw ValidationException::withMessages([
                'witnessed_by' => 'A witness is required to administer a controlled drug.',
            ]);
        }

        // PRN limits: enforce daily maximum + minimum interval between doses.
        if ($sheet->as_required && in_array($request->input('code'), ['A', 'S'], true)) {
            $given = MARAdministration::where('mar_sheet_id', $sheet->id)
                ->where('date', $request->input('date'))
                ->whereIn('code', ['A', 'S'])
                ->where('time_slot', '!=', $request->input('time_slot')) // exclude the dose being (re)recorded
                ->get();
            if ($sheet->prn_max_daily !== null && $given->count() >= (int) $sheet->prn_max_daily) {
                throw ValidationException::withMessages([
                    'code' => 'PRN daily maximum ('.(int) $sheet->prn_max_daily.') already reached for this medication.',
                ]);
            }
            $intervalH = (float) ($sheet->prn_min_interval_hours ?? 0);
            if ($intervalH > 0) {
                $last = $given->sortByDesc('updated_at')->first()?->updated_at;
                if ($last) {
                    $next = $last->copy()->addMinutes((int) round($intervalH * 60));
                    if (now()->lt($next)) {
                        throw ValidationException::withMessages([
                            'code' => 'Too soon — the next PRN dose is not due until '.$next->format('H:i').'.',
                        ]);
                    }
                }
            }
        }

        // Was this slot already recorded as Given? (so we don't deduct twice on edit)
        $existing = MARAdministration::where('mar_sheet_id', $request->input('mar_sheet_id'))
            ->where('date', $request->input('date'))
            ->where('time_slot', $request->input('time_slot'))
            ->first();
        $wasGiven = $existing && $existing->code === 'A';

        $admin = $marSheetService->administer(
            (int) $request->input('mar_sheet_id'),
            $request->only(['date', 'time_slot', 'code', 'dose_given', 'witnessed_by', 'reason', 'notes']),
            $homeId,
            $userId
        );

        if (! $admin) {
            return false;
        }

        // Auto-deduct stock only on a newly-given dose.
        $nowGiven = $request->input('code') === 'A';
        if ($nowGiven && ! $wasGiven) {
            if (! is_null($sheet->stock_level)) {
                $qty = (float) preg_replace('/[^0-9.]/', '', (string) ($request->input('dose_given') ?: $sheet->dose));
                if ($qty <= 0) {
                    $qty = 1;
                }
                $resident = ServiceUser::where('id', $sheet->client_id)->first();
                MedicationStockTransaction::apply($sheet, 'administered', $qty, $userId, [
                    'client_name' => $resident->name ?? null,
                    'notes' => 'Auto-deducted on administration (Medication Round)',
                ]);
            }
        }

        return true;
    }
}
