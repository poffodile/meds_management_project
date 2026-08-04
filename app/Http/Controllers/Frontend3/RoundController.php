<?php

namespace App\Http\Controllers\Frontend3;

use App\Http\Controllers\Frontend3\Concerns\LabelsMedicines;
use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use App\Models\MedicationRoundClosure;
use App\Services\Staff\MARSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * frontend3 — Medication round (spec §6).
 *
 * "Make the safe path the shortest path while documenting every deviation."
 *
 * WHAT THIS CONTROLLER DOES NOT DO
 * It does not implement recording. Every dose goes through applyRecord() on the
 * shared BuildsMedicationRound trait — the same code path the existing round
 * pages use. That path already carries, and must keep carrying:
 *
 *   · a row-level lock on the prescription, so two carers tapping "Given" at
 *     the same moment cannot both pass the PRN maximum check
 *   · the PRN daily maximum and minimum-interval checks
 *   · the duplicate-submission window that absorbs a double tap
 *   · the mandatory reason on refused / withheld / not-available / other
 *   · the controlled-drug quantity requirement
 *   · round-closure locking
 *   · automatic stock deduction
 *
 * Re-implementing any of that here to make a nicer-looking screen would be the
 * single most dangerous thing anyone could do to this codebase. frontend3
 * isolates the FRONT END — layout, theme, CSS. It shares clinical logic on
 * purpose. See docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md.
 */
class RoundController extends F3Controller
{
    use BuildsMedicationRound;
    use LabelsMedicines;

    /** Same access rule as the existing medication pages. */
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    /** Re-opening a locked round is a manager decision, not a carer one. */
    private const MANAGER_USER_TYPES = ['M', 'CM', 'A', 'O'];

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
        $this->useF3Layout();

        $props = $this->buildRoundProps($request);

        // Give every row the same medicine label Today uses. Nothing else about
        // the payload is changed — the round page reads the trait's shape.
        foreach ($props['grid'] as $roundKey => $residents) {
            foreach ($residents as $ri => $resident) {
                foreach ($resident['rows'] as $rowi => $row) {
                    $props['grid'][$roundKey][$ri]['rows'][$rowi]['label'] = $this->medLabel($row);
                }
            }
        }

        $props['canReopen'] = in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true);
        $props['currentUser'] = ['id' => (int) Auth::id(), 'name' => Auth::user()->name];

        return Inertia::render('Round', $props);
    }

    /**
     * End (lock) a round.
     *
     * Deliberately does NOT require everything to be recorded first. A round can
     * legitimately end with doses unrecorded — a person went out, a medicine was
     * unavailable — and forcing a false "given" to close the round would be far
     * worse than an honest gap. The page states what is being left, and the
     * record shows who ended it and when.
     */
    public function end(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        MedicationRoundClosure::updateOrCreate(
            [
                'home_id' => $this->getHomeId(),
                'date' => $request->input('date'),
                'round' => $request->input('round'),
            ],
            ['closed_by' => (int) Auth::id()],
        );

        return redirect()
            ->route('frontend3.round', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
    }

    /** Re-open a locked round. Managers only — the same rule the existing pages apply. */
    public function reopen(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        if (! in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true)) {
            abort(403, 'Only a manager can re-open a round.');
        }

        MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()
            ->route('frontend3.round', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }

    /**
     * Record one dose.
     *
     * applyRecord() throws a ValidationException on anything it refuses, which
     * Inertia surfaces as field errors on the page. A redirect here therefore
     * means the dose really was written.
     */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        $this->applyRecord($request, $marSheetService);

        return redirect()
            ->route('frontend3.round', ['date' => $request->input('date')])
            ->with('success', 'Recorded.');
    }
}
