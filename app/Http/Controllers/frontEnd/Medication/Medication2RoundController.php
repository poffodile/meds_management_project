<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use App\Services\Staff\MARSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
     * End (lock) a round — ONLY once every scheduled dose has an outcome (owner decision
     * B3, 2026-07-28; pending CSO). Nothing is auto-filled: the carer must record each dose
     * (given, or a reason such as refused / not available) first. The outstanding count is
     * recomputed here from the same data the page shows — the client's number is never
     * trusted — and the end is refused if anything is still unrecorded. Records WHO ended it.
     */
    public function endRound(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'round' => 'required|string|max:20',
        ]);

        $round = $request->input('round');

        // Recompute outstanding scheduled (non-PRN) doses with no recorded outcome.
        $props = $this->buildRoundProps($request);
        $outstanding = 0;
        foreach (($props['grid'][$round] ?? []) as $resident) {
            foreach (($resident['rows'] ?? []) as $row) {
                if (empty($row['as_required']) && empty($row['code'])) {
                    $outstanding++;
                }
            }
        }

        if ($outstanding > 0) {
            throw ValidationException::withMessages([
                'round' => "This round still has {$outstanding} dose".($outstanding === 1 ? '' : 's')
                    .' to record. Record each one — given, or a reason — before ending the round.',
            ]);
        }

        \App\Models\MedicationRoundClosure::updateOrCreate(
            ['home_id' => $this->getHomeId(), 'date' => $request->input('date'), 'round' => $round],
            ['closed_by' => (int) Auth::id()]
        );

        return redirect()->route('frontend2.medication2.round', ['date' => $request->input('date')])
            ->with('success', 'Round ended.');
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
