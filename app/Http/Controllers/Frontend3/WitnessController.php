<?php

namespace App\Http\Controllers\Frontend3;

use App\Models\CdWitnessConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * frontend3 — controlled-drug witness co-signatures.
 *
 * When a carer records a controlled drug and names a colleague as the witness,
 * the round already opens a PENDING co-signature (applyRecord does this, via
 * CdWitnessConfirmation::open). This is where that colleague signs it off on
 * their own account.
 *
 * Two rules the spec is emphatic about, both enforced on the server:
 *   · Only the person a signature is FOR may confirm it. A manager acting on
 *     someone's behalf is an OVERRIDE, recorded as such with a reason — it is
 *     never silently recorded as a witness confirmation.
 *   · Self-witnessing is impossible. The witness list at administration already
 *     excludes the current user, so a confirmation can never be pending for the
 *     person who recorded the dose.
 *
 * The register itself is untouched: a confirmation lives in its own table and
 * never rewrites the append-only ledger.
 */
class WitnessController extends F3Controller
{
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];
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

    /** "Signatures awaiting you" — the co-signatures pending THIS user. */
    public function index(Request $request)
    {
        $this->useF3Layout();

        $userId = (int) Auth::id();

        $pending = CdWitnessConfirmation::with(['registerEntry', 'recordedBy:id,name'])
            ->pendingForUser($userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (CdWitnessConfirmation $c) => $this->present($c))
            ->values();

        return Inertia::render('Witness', [
            'pending' => $pending,
            'isManager' => in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true),
        ]);
    }

    /**
     * The named witness confirms their own signature.
     *
     * Idempotent: re-confirming an already-resolved signature is a no-op success,
     * so a double tap cannot produce a second record or an error.
     */
    public function confirm(Request $request, int $id)
    {
        $userId = (int) Auth::id();

        $c = CdWitnessConfirmation::find($id);
        if (! $c) {
            return back()->with('error', 'That signature request could not be found.');
        }

        if ((int) $c->witness_user_id !== $userId) {
            // Not yours to confirm. A manager uses override, which is recorded differently.
            abort(403, 'Only the named witness can confirm this signature.');
        }

        if (! $c->isPending()) {
            return back()->with('success', 'That signature is already resolved.');
        }

        $c->update([
            'status' => CdWitnessConfirmation::STATUS_CONFIRMED,
            'confirmed_by_user_id' => $userId,
            'confirmed_at' => now(),
        ]);

        return redirect()->route('frontend3.witness')->with('success', 'Signature confirmed.');
    }

    /**
     * A manager confirms on the witness's behalf, with a reason.
     *
     * The record shows MANAGER-OVERRIDDEN, not witness-confirmed. That distinction
     * is the whole point — an override must never be able to masquerade as a
     * genuine second signature.
     */
    public function override(Request $request, int $id)
    {
        if (! in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true)) {
            abort(403, 'Only a manager can override a witness confirmation.');
        }

        $request->validate(['override_reason' => 'required|string|max:255']);

        $c = CdWitnessConfirmation::find($id);
        if (! $c) {
            return back()->with('error', 'That signature request could not be found.');
        }

        if (! $c->isPending()) {
            return back()->with('success', 'That signature is already resolved.');
        }

        $c->update([
            'status' => CdWitnessConfirmation::STATUS_OVERRIDDEN,
            'confirmed_by_user_id' => (int) Auth::id(),
            'confirmed_at' => now(),
            'override_reason' => $request->input('override_reason'),
        ]);

        return redirect()->route('frontend3.witness')->with('success', 'Signature marked as manager-overridden.');
    }

    /** Shape one confirmation and its register movement for display. */
    private function present(CdWitnessConfirmation $c): array
    {
        $e = $c->registerEntry;

        return [
            'id' => $c->id,
            'medication_name' => $e->medication_name ?? '—',
            'client_name' => $e->client_name ?? null,
            'action_type' => $e->action_type ?? null,
            'dose_quantity' => $e->dose_quantity ?? null,
            'unit' => $e->unit ?? null,
            'balance_after' => $e->balance_after ?? null,
            'entry_date' => ($e && $e->entry_date)
                ? \Carbon\Carbon::parse($e->entry_date)->format('j M Y')
                : null,
            'entry_time' => $e->entry_time ?? null,
            'recorded_by' => $c->recordedBy->name ?? null,
            'witness_name' => $c->witness_name,
        ];
    }
}
