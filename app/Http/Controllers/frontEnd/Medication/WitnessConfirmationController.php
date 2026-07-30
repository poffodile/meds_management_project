<?php

namespace App\Http\Controllers\frontEnd\Medication;

use App\Http\Controllers\Controller;
use App\Models\CdWitnessConfirmation;
use App\Models\ControlledDrugRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Controlled-drug witness co-signatures (issue #14 / owner decision A2).
 *
 * Where a carer names a colleague as the witness of a CD administration/movement, the
 * colleague is asked to CONFIRM the signature on their own account. This screen is that
 * colleague's list of signatures awaiting them, and (stage 4) the confirm / manager-override
 * actions. The register itself is untouched — a confirmation lives in its own table.
 */
class WitnessConfirmationController extends Controller
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

    /** "Signatures awaiting you" — the confirmations pending THIS user's sign-off. */
    public function index(Request $request)
    {
        $userId = (int) Auth::id();

        $pending = CdWitnessConfirmation::with(['registerEntry', 'recordedBy:id,name'])
            ->pendingForUser($userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (CdWitnessConfirmation $c) => $this->present($c));

        return Inertia::render('Frontend2/Medication2/WitnessConfirmations', [
            'pending'   => $pending->values(),
            'isManager' => in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true),
        ]);
    }

    /**
     * The named witness confirms their own signature (issue #14 / A2). Only the person the
     * signature is FOR may confirm it here — a manager confirming on someone's behalf is an
     * override (below), recorded as such. Idempotent: re-confirming an already-resolved one
     * is a no-op success.
     */
    public function confirm(Request $request, int $id)
    {
        $userId = (int) Auth::id();

        $c = CdWitnessConfirmation::find($id);
        if (! $c) {
            return back()->with('error', 'That signature request could not be found.');
        }
        if ($c->witness_user_id !== $userId) {
            // Not your signature to confirm. A manager uses override, not this.
            abort(403, 'Only the named witness can confirm this signature.');
        }
        if (! $c->isPending()) {
            return back()->with('success', 'That signature is already resolved.');
        }

        $c->update([
            'status'               => CdWitnessConfirmation::STATUS_CONFIRMED,
            'confirmed_by_user_id' => $userId,
            'confirmed_at'         => now(),
        ]);

        return redirect()->route('frontend2.medication2.witness-confirmations')
            ->with('success', 'Signature confirmed.');
    }

    /**
     * A manager overrides — confirms a signature on the witness's behalf, with a reason. The
     * record shows it was MANAGER-OVERRIDDEN, not witness-confirmed. Manager-only.
     */
    public function override(Request $request, int $id)
    {
        if (! in_array(Auth::user()->user_type, self::MANAGER_USER_TYPES, true)) {
            abort(403, 'Only a manager can override a witness confirmation.');
        }

        $request->validate([
            'override_reason' => 'required|string|max:255',
        ]);

        $c = CdWitnessConfirmation::find($id);
        if (! $c) {
            return back()->with('error', 'That signature request could not be found.');
        }
        if (! $c->isPending()) {
            return back()->with('success', 'That signature is already resolved.');
        }

        $c->update([
            'status'               => CdWitnessConfirmation::STATUS_OVERRIDDEN,
            'confirmed_by_user_id' => (int) Auth::id(),
            'confirmed_at'         => now(),
            'override_reason'      => $request->input('override_reason'),
        ]);

        return redirect()->route('frontend2.medication2.witness-confirmations')
            ->with('success', 'Signature marked as manager-overridden.');
    }

    /** Shape one confirmation + its register movement for display. */
    private function present(CdWitnessConfirmation $c): array
    {
        $e = $c->registerEntry;

        return [
            'id'              => $c->id,
            'medication_name' => $e->medication_name ?? '—',
            'client_name'     => $e->client_name ?? null,
            'action_type'     => $e->action_type ?? null,
            'dose_quantity'   => $e->dose_quantity ?? null,
            'unit'            => $e->unit ?? null,
            'balance_after'   => $e->balance_after ?? null,
            'entry_date'      => ($e && $e->entry_date) ? \Carbon\Carbon::parse($e->entry_date)->format('d M Y') : null,
            'entry_time'      => $e->entry_time ?? null,
            'recorded_by'     => $c->recordedBy->name ?? null,
            'witness_name'    => $c->witness_name,
        ];
    }
}
