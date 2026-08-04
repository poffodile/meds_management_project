<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * MAR chart — the Medication Administration Record (issue: standalone meds product, owner
 * 2026-07-30). The canonical, read-only view of what was recorded: per resident, each
 * medicine and the outcome of every scheduled dose across a week. Recording happens on the
 * Medication Round; this is the record you'd read, audit or print.
 *
 * Deliberately setting-agnostic — a single individual managing their own meds is a
 * first-class user, so the page works with one resident just as well as a home full.
 */
class MarChartController extends Controller
{
    use \App\Http\Controllers\frontEnd\Concerns\ResolvesCurrentHome;

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

    private function getHomeId(): int
    {
        return $this->currentHomeId();
    }

    public function index(Request $request)
    {
        $request->validate([
            'client_id'  => 'nullable|integer',
            'week_start' => 'nullable|date',
        ]);

        $homeId = $this->getHomeId();

        // Residents in view (home-scoped) for the picker.
        $residents = ServiceUser::where('home_id', $homeId)->where('status', 1)
            ->orderBy('name')->get(['id', 'name', 'date_of_birth', 'room_number', 'nhs_number']);

        if ($residents->isEmpty()) {
            return Inertia::render('Frontend2/Medication2/MarChart', [
                'residents' => [], 'resident' => null, 'meds' => [], 'days' => [],
                'weekStart' => null, 'prevWeek' => null, 'nextWeek' => null, 'isThisWeek' => true,
            ]);
        }

        // Selected resident — the requested one (validated to this home) or the first.
        $clientId = (int) $request->input('client_id');
        $resident = $residents->firstWhere('id', $clientId) ?? $residents->first();

        // Week window (Mon–Sun), defaulting to the week containing today.
        $weekStart = $request->filled('week_start')
            ? \Carbon\Carbon::parse($request->input('week_start'))->startOfWeek()
            : \Carbon\Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $days = [];
        for ($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay()) {
            $days[] = [
                'date'  => $d->toDateString(),
                'dow'   => $d->format('D'),
                'day'   => $d->format('j'),
                'today' => $d->isToday(),
            ];
        }

        // The resident's active medicines.
        $sheets = MARSheet::forHome($homeId)->active()->currentlyActive()
            ->where('client_id', $resident->id)
            ->orderBy('medication_name')
            ->get();

        // Every administration for those medicines across the week, keyed for O(1) cell lookup.
        $adminsByKey = MARAdministration::whereIn('mar_sheet_id', $sheets->pluck('id'))
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn ($a) => $a->mar_sheet_id.'|'.$a->time_slot.'|'.($a->date instanceof \Carbon\Carbon ? $a->date->toDateString() : $a->date));

        $meds = $sheets->map(function ($s) use ($days, $adminsByKey) {
            // Scheduled slots come from the stored time_slots; PRN has none. `time_slots` may
            // be an array (JSON cast) or a comma string depending on the row — handle both.
            $rawSlots = $s->as_required ? [] : $s->time_slots;
            if (is_array($rawSlots)) {
                $slots = array_values(array_filter(array_map(fn ($x) => trim((string) $x), $rawSlots)));
            } else {
                $slots = array_values(array_filter(array_map('trim', explode(',', (string) $rawSlots))));
            }

            // grid[slot][date] = the recorded outcome for that box (null = not yet recorded).
            $grid = [];
            foreach ($slots as $slot) {
                foreach ($days as $day) {
                    $a = $adminsByKey->get($s->id.'|'.$slot.'|'.$day['date']);
                    $grid[$slot][$day['date']] = $a ? [
                        'code'    => $a->code,
                        'given'   => (bool) $a->given,
                        'is_late' => (bool) ($a->is_late ?? false),
                    ] : null;
                }
            }

            // For PRN, a per-day count of doses actually given (there is no fixed slot).
            $prnByDay = [];
            if ($s->as_required) {
                foreach ($days as $day) {
                    $prnByDay[$day['date']] = $adminsByKey
                        ->filter(fn ($a, $k) => str_starts_with($k, $s->id.'|') && str_ends_with($k, '|'.$day['date']) && $a->code === 'A')
                        ->count();
                }
            }

            return [
                'mar_sheet_id'    => $s->id,
                'medication_name' => $s->medication_name,
                'dose'            => $s->dose,
                'route'           => $s->route,
                'is_controlled'   => (bool) $s->is_controlled,
                'as_required'     => (bool) $s->as_required,
                'slots'           => $slots,
                'grid'            => $grid,
                'prn_by_day'      => $prnByDay,
            ];
        })->values();

        return Inertia::render('Frontend2/Medication2/MarChart', [
            'residents'  => $residents->map(fn ($r) => ['value' => (string) $r->id, 'label' => $r->name])->all(),
            'resident'   => [
                'id'   => $resident->id,
                'name' => $resident->name,
                'dob'  => $resident->date_of_birth,
                'room' => $resident->room_number,
                'nhs'  => $resident->nhs_number,
            ],
            'meds'       => $meds,
            'days'       => $days,
            'weekStart'  => $weekStart->toDateString(),
            'weekLabel'  => $weekStart->format('j M').' – '.$weekEnd->format('j M Y'),
            'prevWeek'   => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek'   => $weekStart->copy()->addWeek()->toDateString(),
            'isThisWeek' => $weekStart->isSameWeek(\Carbon\Carbon::now()),
        ]);
    }
}
