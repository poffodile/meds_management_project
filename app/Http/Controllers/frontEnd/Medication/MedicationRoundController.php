<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\MARAdministration;
use App\Models\MedicationStockTransaction;
use App\Services\Staff\MARSheetService;
use App\ServiceUser;
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
        'morning'   => ['label' => 'Morning',   'start' => 6,  'end' => 12, 'icon' => 'fa-sun-o',     'window' => '06:00–12:00'],
        'lunchtime' => ['label' => 'Lunchtime', 'start' => 12, 'end' => 14, 'icon' => 'fa-coffee',    'window' => '12:00–14:00'],
        'evening'   => ['label' => 'Evening',   'start' => 14, 'end' => 18, 'icon' => 'fa-cloud',     'window' => '14:00–18:00'],
        'night'     => ['label' => 'Night',     'start' => 18, 'end' => 24, 'icon' => 'fa-moon-o',    'window' => '18:00–06:00'],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
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
        if (!$time) {
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
        $date   = $request->input('date', now()->toDateString());

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
            $slots = !empty($sheet->time_slots) ? $sheet->time_slots : [null];
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');

            foreach ($slots as $slot) {
                $targetRounds = $slot !== null
                    ? [$this->roundForTime($slot)]
                    : array_keys(self::ROUNDS); // unscheduled / PRN meds appear in every round

                foreach ($targetRounds as $roundKey) {
                    $clientId = $sheet->client_id;
                    if (!isset($grid[$roundKey][$clientId])) {
                        $grid[$roundKey][$clientId] = [
                            'client_id' => $clientId,
                            'name'      => $residentNames[$clientId] ?? ('Resident #' . $clientId),
                            'rows'      => [],
                        ];
                    }
                    $grid[$roundKey][$clientId]['rows'][] = [
                        'sheet' => $sheet,
                        'slot'  => $slot,
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
            'rounds'       => self::ROUNDS,
            'grid'         => $grid,
            'date'         => $date,
            'currentRound' => $currentRound,
        ]);
    }

    /** React/Inertia version of the round grid. Same data, shaped into plain arrays. */
    public function indexReact(Request $request)
    {
        $request->validate(['date' => 'nullable|date']);

        $homeId = $this->getHomeId();
        $date   = $request->input('date', now()->toDateString());

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => function ($q) use ($date) {
                $q->where('date', $date)->with('administeredByUser:id,name');
            }])
            ->orderBy('medication_name')
            ->get();

        $clientIds = $sheets->pluck('client_id')->unique()->values();
        $residentNames = ServiceUser::whereIn('id', $clientIds)->pluck('name', 'id');

        $grid = [];
        foreach (array_keys(self::ROUNDS) as $roundKey) {
            $grid[$roundKey] = [];
        }

        foreach ($sheets as $sheet) {
            $slots = !empty($sheet->time_slots) ? $sheet->time_slots : [null];
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');

            foreach ($slots as $slot) {
                $targetRounds = $slot !== null ? [$this->roundForTime($slot)] : array_keys(self::ROUNDS);

                foreach ($targetRounds as $roundKey) {
                    $clientId = $sheet->client_id;
                    if (!isset($grid[$roundKey][$clientId])) {
                        $grid[$roundKey][$clientId] = [
                            'client_id' => $clientId,
                            'name'      => $residentNames[$clientId] ?? ('Resident #' . $clientId),
                            'rows'      => [],
                        ];
                    }
                    $admin = $slot !== null ? $adminsBySlot->get($slot) : null;
                    $grid[$roundKey][$clientId]['rows'][] = [
                        'mar_sheet_id'    => $sheet->id,
                        'medication_name' => $sheet->medication_name,
                        'dose'            => $sheet->dose,
                        'slot'            => $slot,
                        'code'            => $admin->code ?? null,
                        'dose_given'      => $admin->dose_given ?? null,
                        'recorded_by'     => ($admin && $admin->administeredByUser) ? $admin->administeredByUser->name : null,
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

        return Inertia::render('Medication/MedicationRound', [
            'rounds'       => $rounds,
            'grid'         => $grid,
            'date'         => $date,
            'currentRound' => $this->roundForTime(now()->format('H:i')),
        ]);
    }

    /**
     * Record an administration via the existing MAR service, and auto-deduct stock when given.
     * Guards against double-deducting if an already-"Given" record is edited.
     */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        $ok = $this->applyRecord($request, $marSheetService);

        if (!$ok) {
            return response()->json(['ok' => false, 'message' => 'Prescription not found'], 404);
        }

        return response()->json(['ok' => true]);
    }

    /** Same record, but returns to the React/Inertia round page (keeping the date). */
    public function recordReact(Request $request, MARSheetService $marSheetService)
    {
        $ok   = $this->applyRecord($request, $marSheetService);
        $date = $request->input('date');

        return redirect()->route('medication.medication-round.react', ['date' => $date])
            ->with($ok ? 'success' : 'error', $ok ? 'Dose recorded.' : 'Prescription not found.');
    }

    /** Record an administration + auto-deduct stock on a newly-given dose. Returns false if not found. */
    private function applyRecord(Request $request, MARSheetService $marSheetService): bool
    {
        $request->validate([
            'mar_sheet_id' => 'required|integer',
            'date'         => 'required|date',
            'time_slot'    => 'required|string|max:10',
            'code'         => 'required|in:A,S,R,W,N,O',
            'dose_given'   => 'nullable|string|max:100',
            'witnessed_by' => 'nullable|string|max:255',
            'notes'        => 'nullable|string|max:2000',
        ]);

        $homeId = $this->getHomeId();
        $userId = (int) Auth::id();

        // Was this slot already recorded as Given? (so we don't deduct twice on edit)
        $existing = MARAdministration::where('mar_sheet_id', $request->input('mar_sheet_id'))
            ->where('date', $request->input('date'))
            ->where('time_slot', $request->input('time_slot'))
            ->first();
        $wasGiven = $existing && $existing->code === 'A';

        $admin = $marSheetService->administer(
            (int) $request->input('mar_sheet_id'),
            $request->only(['date', 'time_slot', 'code', 'dose_given', 'witnessed_by', 'notes']),
            $homeId,
            $userId
        );

        if (!$admin) {
            return false;
        }

        // Auto-deduct stock only on a newly-given dose.
        $nowGiven = $request->input('code') === 'A';
        if ($nowGiven && !$wasGiven) {
            $sheet = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));
            if ($sheet && !is_null($sheet->stock_level)) {
                $qty = (float) preg_replace('/[^0-9.]/', '', (string) ($request->input('dose_given') ?: $sheet->dose));
                if ($qty <= 0) {
                    $qty = 1;
                }
                $resident = ServiceUser::where('id', $sheet->client_id)->first();
                MedicationStockTransaction::apply($sheet, 'administered', $qty, $userId, [
                    'client_name' => $resident->name ?? null,
                    'notes'       => 'Auto-deducted on administration (Medication Round)',
                ]);
            }
        }

        return true;
    }
}
