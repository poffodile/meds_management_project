<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
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
    use BuildsMedicationRound;
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || ! in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
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

    /**
     * Record an administration via the existing MAR service, and auto-deduct stock when given.
     * Guards against double-deducting if an already-"Given" record is edited.
     */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        // applyRecord now throws on a missing sheet (audit CR-07) rather than returning
        // false. This JSON endpoint preserves its old 404 contract by catching it; the
        // other validation failures (PRN, CD witness, reason, round lock) surface as the
        // usual 422 that Laravel produces from a ValidationException.
        try {
            $this->applyRecord($request, $marSheetService);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (array_key_exists('mar_sheet_id', $e->errors())) {
                return response()->json(['ok' => false, 'message' => 'Prescription not found'], 404);
            }
            throw $e;
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

        // Scope the resident lookup to this home. An unscoped find() leaks another
        // tenant's name/DOB even though the medication grid below stays correctly empty.
        $client = \App\Models\ServiceUser::where('id', $clientId)
            ->where('home_id', $homeId)
            ->first();
        abort_if(! $client, 404);

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

    // ---- Medication Round 4.2 — UI-fix duplicate of Round 4 (same data + behaviour) ----

    /** UI-fix duplicate of the editorial round page. Same shared props as Round 4. */
    public function indexMedsRound42(Request $request)
    {
        return Inertia::render('Medication/MedsRound42', $this->buildRoundProps($request));
    }

    /** Record a dose + return to the Medication Round 4.2 page (keeping the date). */
    public function recordMedsRound42(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.v42', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Ends (locks) a round for the home + date, recording who closed it. */
    public function endMedsRound42(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('medication.medication-round.v42', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
    }

    /** Re-opens (unlocks) an ended round — managers only. */
    public function reopenMedsRound42(Request $request)
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

        return redirect()->route('medication.medication-round.v42', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }

    // ---- Frontend2 — CLINIK-style "today's round" (renders in the frontend2 shell) ----

    /** Today's round in the frontend2 shell. Same shared props/logic as the other round pages. */
    public function indexFrontend2(Request $request)
    {
        return Inertia::render('Frontend2/MedicationRound', $this->buildRoundProps($request));
    }

    /** Record a dose + return to the frontend2 round page (keeping the date). */
    public function recordFrontend2(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('frontend2.medication-round', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Ends (locks) a round for the home + date. */
    public function endFrontend2(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('frontend2.medication-round', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
    }

    /** Re-opens an ended round — managers only. */
    public function reopenFrontend2(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('frontend2.medication-round', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }

    // ---------------------------------------------------------------
    // Frontend2 "V2" — an experimental redesign of the round page
    // (teal theme, expanded med panel, full signature modal). Shares
    // the same data + record logic; only the route/page name differ.
    // ---------------------------------------------------------------

    public function indexFrontend2V2(Request $request)
    {
        return Inertia::render('Frontend2/MedicationRoundV2', $this->buildRoundProps($request));
    }

    /** Master–detail split variant (test): full V1 feature set, split transition. */
    public function indexFrontend2Split(Request $request)
    {
        return Inertia::render('Frontend2/MedicationRoundSplit', $this->buildRoundProps($request));
    }

    public function recordFrontend2Split(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        return redirect()->route('frontend2.medication-round-split', ['date' => $request->input('date')])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Split variant "B" (test): same feature set + data, redesigned right rail. */
    public function indexFrontend2SplitB(Request $request)
    {
        return Inertia::render('Frontend2/MedicationRoundSplitB', $this->buildRoundProps($request));
    }

    public function recordFrontend2SplitB(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        return redirect()->route('frontend2.medication-round-split-b', ['date' => $request->input('date')])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    public function endFrontend2SplitB(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('frontend2.medication-round-split-b', ['date' => $request->input('date')])->with('success', 'Round ended.');
    }

    public function reopenFrontend2SplitB(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('frontend2.medication-round-split-b', ['date' => $request->input('date')])->with('success', 'Round re-opened.');
    }

    /** Split variant "C" (test): same feature set + data, mockup-sized right rail (profile-shaped quick actions). */
    public function indexFrontend2SplitC(Request $request)
    {
        return Inertia::render('Frontend2/MedicationRoundSplitC', $this->buildRoundProps($request));
    }

    public function recordFrontend2SplitC(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        return redirect()->route('frontend2.medication-round-split-c', ['date' => $request->input('date')])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    public function endFrontend2SplitC(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('frontend2.medication-round-split-c', ['date' => $request->input('date')])->with('success', 'Round ended.');
    }

    public function reopenFrontend2SplitC(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('frontend2.medication-round-split-c', ['date' => $request->input('date')])->with('success', 'Round re-opened.');
    }

    public function endFrontend2Split(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('frontend2.medication-round-split', ['date' => $request->input('date')])->with('success', 'Round ended.');
    }

    public function reopenFrontend2Split(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('frontend2.medication-round-split', ['date' => $request->input('date')])->with('success', 'Round re-opened.');
    }

    /** Per-resident "give meds" page (V2 test): one client's info + their meds by round. */
    public function residentRoundV2(Request $request, $client)
    {
        $props = $this->buildRoundProps($request);
        $round = $request->input('round', $props['currentRound']);

        return Inertia::render('Frontend2/MedicationRoundResident', array_merge($props, [
            'clientId' => (int) $client,
            'round' => collect($props['rounds'])->firstWhere('key', $round) ?? ($props['rounds'][0] ?? null),
        ]));
    }

    public function recordFrontend2V2(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        // Allow returning to the per-resident page it was recorded from (internal paths only).
        $to = $request->input('redirect_to');
        $back = ($to && str_starts_with($to, '/frontend2'))
            ? redirect($to)
            : redirect()->route('frontend2.medication-round-v2', ['date' => $request->input('date')]);

        return $back->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    public function endFrontend2V2(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        $to = $request->input('redirect_to');
        $back = ($to && str_starts_with($to, '/frontend2')) ? redirect($to) : redirect()->route('frontend2.medication-round-v2', ['date' => $request->input('date')]);

        return $back->with('success', 'Round ended.');
    }

    public function reopenFrontend2V2(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        $to = $request->input('redirect_to');
        $back = ($to && str_starts_with($to, '/frontend2')) ? redirect($to) : redirect()->route('frontend2.medication-round-v2', ['date' => $request->input('date')]);

        return $back->with('success', 'Round re-opened.');
    }

}
