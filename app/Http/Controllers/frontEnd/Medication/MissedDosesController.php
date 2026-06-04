<?php

namespace App\Http\Controllers\frontEnd\Medication;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\MARSheet;
use App\Models\MedicationDoseReview;
use App\ServiceUser;
use Inertia\Inertia;

class MissedDosesController extends Controller
{
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    // MAR codes that count as "not given as planned".
    private const NOT_GIVEN_CODES = ['R', 'O', 'W', 'N'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }
            return $next($request);
        });
    }

    private function getHomeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function index(Request $request)
    {
        $request->validate([
            'date'   => 'nullable|date',
            'status' => 'nullable|in:outstanding,resolved,all',
        ]);

        $homeId       = $this->getHomeId();
        $date         = $request->input('date', now()->toDateString());
        $statusFilter = $request->input('status', 'outstanding');

        $carbon  = \Carbon\Carbon::parse($date);
        $today   = \Carbon\Carbon::today();
        $isToday = $carbon->isSameDay($today);
        $isPast  = $carbon->lt($today);
        $nowTime = now()->format('H:i');

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => fn($q) => $q->where('date', $date)->with('administeredByUser:id,name')])
            ->orderBy('medication_name')
            ->get();

        $residentNames = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())->pluck('name', 'id');

        // Existing reviews for this date, keyed by sheet|slot.
        $reviews = MedicationDoseReview::forHome($homeId)
            ->whereDate('review_date', $date)
            ->with('reviewedByUser:id,name')
            ->get()
            ->keyBy(fn($r) => $r->mar_sheet_id . '|' . $r->time_slot);

        // Build every dose issue for the day (before applying the status filter).
        $all = [];
        foreach ($sheets as $sheet) {
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');
            foreach (($sheet->time_slots ?: []) as $slot) {
                $admin = $adminsBySlot->get($slot);

                if ($admin) {
                    if (!in_array($admin->code, self::NOT_GIVEN_CODES, true)) {
                        continue; // given / sleeping — fine
                    }
                    $kind = 'not_given';
                    $code = $admin->code;
                } else {
                    // Not recorded: only a problem once the slot time has passed.
                    $passed = $isPast || ($isToday && $slot < $nowTime);
                    if (!$passed) {
                        continue;
                    }
                    $kind = 'missed';
                    $code = null;
                }

                $all[] = [
                    'sheet'         => $sheet,
                    'slot'          => $slot,
                    'kind'          => $kind,
                    'code'          => $code,
                    'admin'         => $admin,
                    'review'        => $reviews->get($sheet->id . '|' . $slot),
                    'resident_name' => $residentNames[$sheet->client_id] ?? ('#' . $sheet->client_id),
                ];
            }
        }

        $all = collect($all);
        $stats = [
            'missed'      => $all->where('kind', 'missed')->count(),
            'not_given'   => $all->where('kind', 'not_given')->count(),
            'resolved'    => $all->filter(fn($i) => $i['review'])->count(),
            'outstanding' => $all->filter(fn($i) => !$i['review'])->count(),
        ];

        $items = $all->filter(function ($i) use ($statusFilter) {
            if ($statusFilter === 'outstanding') return !$i['review'];
            if ($statusFilter === 'resolved')    return (bool) $i['review'];
            return true;
        })->values()->all();

        return view('frontEnd.medication.missed_doses.index', [
            'items'        => $items,
            'stats'        => $stats,
            'date'         => $date,
            'prevDate'     => $carbon->copy()->subDay()->toDateString(),
            'nextDate'     => $carbon->copy()->addDay()->toDateString(),
            'todayDate'    => $today->toDateString(),
            'statusFilter' => $statusFilter,
        ]);
    }

    /** React/Inertia version of the missed-doses review. */
    public function indexReact(Request $request)
    {
        $request->validate([
            'date'   => 'nullable|date',
            'status' => 'nullable|in:outstanding,resolved,all',
        ]);

        $homeId       = $this->getHomeId();
        $date         = $request->input('date', now()->toDateString());
        $statusFilter = $request->input('status', 'outstanding');

        $carbon  = \Carbon\Carbon::parse($date);
        $today   = \Carbon\Carbon::today();
        $isToday = $carbon->isSameDay($today);
        $isPast  = $carbon->lt($today);
        $nowTime = now()->format('H:i');

        $sheets = MARSheet::forHome($homeId)
            ->active()
            ->currentlyActive()
            ->with(['administrations' => fn($q) => $q->where('date', $date)->with('administeredByUser:id,name')])
            ->orderBy('medication_name')
            ->get();

        $residentNames = ServiceUser::whereIn('id', $sheets->pluck('client_id')->unique())->pluck('name', 'id');

        $reviews = MedicationDoseReview::forHome($homeId)
            ->whereDate('review_date', $date)
            ->with('reviewedByUser:id,name')
            ->get()
            ->keyBy(fn($r) => $r->mar_sheet_id . '|' . $r->time_slot);

        $all = [];
        foreach ($sheets as $sheet) {
            $adminsBySlot = $sheet->administrations->keyBy('time_slot');
            foreach (($sheet->time_slots ?: []) as $slot) {
                $admin = $adminsBySlot->get($slot);

                if ($admin) {
                    if (!in_array($admin->code, self::NOT_GIVEN_CODES, true)) {
                        continue;
                    }
                    $kind = 'not_given';
                    $code = $admin->code;
                } else {
                    $passed = $isPast || ($isToday && $slot < $nowTime);
                    if (!$passed) {
                        continue;
                    }
                    $kind = 'missed';
                    $code = null;
                }

                $review = $reviews->get($sheet->id . '|' . $slot);

                $all[] = [
                    'id'              => $sheet->id . '|' . $slot,
                    'mar_sheet_id'    => $sheet->id,
                    'medication_name' => $sheet->medication_name,
                    'resident_name'   => $residentNames[$sheet->client_id] ?? ('#' . $sheet->client_id),
                    'slot'            => $slot,
                    'kind'            => $kind,
                    'code'            => $code,
                    'resolved'        => (bool) $review,
                    'clinical_action' => $review->clinical_action ?? null,
                    'reviewed_by'     => ($review && $review->reviewedByUser) ? $review->reviewedByUser->name : null,
                ];
            }
        }

        $all = collect($all);
        $stats = [
            'missed'      => $all->where('kind', 'missed')->count(),
            'not_given'   => $all->where('kind', 'not_given')->count(),
            'resolved'    => $all->where('resolved', true)->count(),
            'outstanding' => $all->where('resolved', false)->count(),
        ];

        $items = $all->filter(function ($i) use ($statusFilter) {
            if ($statusFilter === 'outstanding') return !$i['resolved'];
            if ($statusFilter === 'resolved')    return $i['resolved'];
            return true;
        })->values();

        return Inertia::render('Medication/MissedDoses', [
            'items'        => $items,
            'stats'        => $stats,
            'date'         => $date,
            'prevDate'     => $carbon->copy()->subDay()->toDateString(),
            'nextDate'     => $carbon->copy()->addDay()->toDateString(),
            'todayDate'    => $today->toDateString(),
            'statusFilter' => $statusFilter,
        ]);
    }

    public function resolve(Request $request)
    {
        $error = $this->runResolve($request);

        return redirect()->route('medication.missed-doses.index', ['date' => $request->input('review_date')])
            ->with($error ? 'error' : 'success', $error ?? 'Dose reviewed and resolved.');
    }

    /** Same resolve, but returns to the React/Inertia page (keeping the date). */
    public function resolveReact(Request $request)
    {
        $error = $this->runResolve($request);

        return redirect()->route('medication.missed-doses.react', ['date' => $request->input('review_date')])
            ->with($error ? 'error' : 'success', $error ?? 'Dose reviewed and resolved.');
    }

    /** Validate + record a dose review. Returns an error message, or null on success. */
    private function runResolve(Request $request): ?string
    {
        $request->validate([
            'mar_sheet_id'    => 'required|integer',
            'review_date'     => 'required|date',
            'time_slot'       => 'required|string|max:10',
            'dose_kind'       => 'required|in:missed,not_given',
            'code'            => 'nullable|string|max:5',
            'clinical_action' => 'required|string|max:100',
            'notes'           => 'nullable|string',
        ]);

        $homeId = $this->getHomeId();
        $sheet  = MARSheet::forHome($homeId)->active()->find($request->input('mar_sheet_id'));

        if (!$sheet) {
            return 'Medication not found.';
        }

        $resident = ServiceUser::where('id', $sheet->client_id)->first();

        MedicationDoseReview::updateOrCreate(
            [
                'mar_sheet_id' => $sheet->id,
                'review_date'  => $request->input('review_date'),
                'time_slot'    => $request->input('time_slot'),
            ],
            [
                'home_id'             => $homeId,
                'client_id'           => $sheet->client_id,
                'client_name'         => $resident->name ?? null,
                'medication_name'     => $sheet->medication_name,
                'dose_kind'           => $request->input('dose_kind'),
                'code'                => $request->input('code'),
                'clinical_action'     => $request->input('clinical_action'),
                'notes'               => $request->input('notes'),
                'status'              => 'resolved',
                'reviewed_by_user_id' => Auth::id(),
            ]
        );

        return null;
    }
}
