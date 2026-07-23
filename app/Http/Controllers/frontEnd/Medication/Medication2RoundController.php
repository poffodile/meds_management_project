<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use App\Services\Staff\MARSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * The Medication 2 → Round page (the focused one-resident flow).
 *
 * COMPOSES the shared round logic via the BuildsMedicationRound trait rather than
 * extending MedicationRoundController — the deliberate choice made 2026-07-16. The
 * trait carries buildRoundProps()/applyRecord() and their safety rules (PRN limits,
 * CD witness by care setting, append-only records, structured stock deduction), so
 * this page gets all of them without inheriting the 18 legacy round variants' surface
 * or risking an edit here reshaping those pages.
 *
 * The page itself is a new React component; the DATA is identical to every other
 * round page, which is the point — one write path, one set of rules.
 */
class Medication2RoundController extends Controller
{
    use BuildsMedicationRound;

    /** Clinical roles only (mirrors MedicationRoundController). */
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

    /** Render the round (same props contract as every other round page). */
    public function index(Request $request)
    {
        return Inertia::render('Frontend2/Medication2/Round', $this->buildRoundProps($request));
    }

    /** Record one dose, then return to the round keeping the date. */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        // applyRecord throws on any failure (audit CR-07) — a missing sheet, a PRN block,
        // a missing witness, an unreasoned refusal. Those surface as a 302-with-errors that
        // the page renders; there is no silent-success path here.
        $this->applyRecord($request, $marSheetService);

        return redirect()
            ->route('frontend2.medication2.round', ['date' => $request->input('date')])
            ->with('success', 'Dose recorded.');
    }

    /**
     * End (lock) a round. The overview confirms outstanding doses before calling this
     * (the carer chose to proceed), so this records WHO ended it and, if there were
     * unrecorded doses, HOW MANY — an auditable fact, not a silent lock (closes the
     * spirit of HAZ-10: an unguarded end with no record of what was left).
     */
    public function endRound(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
            'outstanding' => 'nullable|integer|min:0',
        ]);

        \App\Models\MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $request->input('round')],
            ['closed_by' => (int) Auth::id()]
        );

        $outstanding = (int) $request->input('outstanding', 0);
        $msg = $outstanding > 0
            ? "Round ended with {$outstanding} dose(s) still outstanding."
            : 'Round ended.';

        return redirect()->route('frontend2.medication2.round', ['date' => $request->input('date')])
            ->with('success', $msg);
    }

    /** Re-open a locked round. Manager-only (mirrors the original round). */
    public function reopenRound(Request $request)
    {
        $request->validate(['date' => 'required|date', 'round' => 'required|string|max:20']);

        if (! in_array(Auth::user()->user_type, ['M', 'CM', 'A', 'O'], true)) {
            abort(403, 'Only managers can re-open a round.');
        }

        \App\Models\MedicationRoundClosure::where('home_id', $this->getHomeId())
            ->where('date', $request->input('date'))
            ->where('round', $request->input('round'))
            ->delete();

        return redirect()->route('frontend2.medication2.round', ['date' => $request->input('date')])
            ->with('success', 'Round re-opened.');
    }
}
