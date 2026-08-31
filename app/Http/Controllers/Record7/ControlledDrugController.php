<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\UserServiceAccess;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\ControlledDrugAdministration;
use App\Services\Record7\ControlledDrugRegister;
use App\Services\Record7\PrnAdministration;
use App\Services\Record7\RoundAuthority;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;

/**
 * Section 2.5 — controlled drugs.
 *
 * PERSON-SCOPED, LIKE SECTION 2.4 AND FOR THE SAME REASON. A controlled drug
 * may be needed at any hour, and requiring an open round would leave a worker
 * choosing between refusing somebody and opening a fake round.
 *
 * NOTHING FROM THE BROWSER IS TRUSTED. The house comes from the session, the
 * person from the house, the prescription from the person, and the witness is
 * resolved and re-authorised on the server. A number in a URL is a number
 * somebody typed.
 */
class ControlledDrugController extends R7Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly RoundAuthority $authority,
        private readonly ControlledDrugAdministration $cd,
        private readonly ControlledDrugRegister $register,
        private readonly PrnAdministration $prn,
        private readonly RoundPersonView $personView,
    ) {
    }

    /** The controlled medicines this person has, with their balances. */
    public function index(Request $request, int $client)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);
        $house = Service::find($serviceId);

        $medicines = Prescription::with('medicine')
            ->where('client_id', $person->id)
            ->whereHas('medicine', fn ($q) => $q->where('is_controlled', true))
            ->orderBy('id')
            ->get()
            ->map(fn (Prescription $p) => $this->cd->describe($p, $person, $house))
            ->values()->all();

        return Inertia::render('CdList', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $this->personView->identityFor($person),
            'safety' => $this->personView->safetyFor($person),
            'medicines' => $medicines,
            'witnessRule' => $this->register->witnessRule($house),
            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],
            'stage' => 'Section 2.5 — controlled drugs.',
            'urls' => $this->urls($person, $serviceId),
        ]);
    }

    /** The give screen: what both people must see before they sign. */
    public function give(Request $request, int $client, int $prescription)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);
        $house = Service::find($serviceId);
        $prescribed = $this->controlled($person, $prescription);

        $isPrn = $prescribed->kind === 'prn';

        return Inertia::render('CdGive', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $this->personView->identityFor($person),
            'safety' => $this->personView->safetyFor($person),
            'medicine' => $this->cd->describe($prescribed, $person, $house),
            'history' => $this->register->history(
                $person,
                $this->register->snapshot($prescribed->medicine, $prescribed->dose_unit)
            ),

            // Colleagues who could actually witness this, resolved on the
            // server. The list is a convenience; the server re-checks whoever
            // comes back regardless of what was offered.
            'witnesses' => $this->possibleWitnesses($user, $house),

            'observedReasons' => $isPrn
                ? collect(PrnAdministration::OBSERVED_REASONS)
                    ->map(fn ($word, $code) => ['code' => $code, 'word' => $word])
                    ->values()->all()
                : [],

            // Section 2.4 keeps owning as-required safety, including replay.
            'attemptToken' => $isPrn && $check['allowed']
                ? $this->prn->beginAttempt($user, $serviceId, $person, $prescribed)->token
                : null,

            'prnGuard' => $isPrn ? $this->prn->describe($prescribed, $person) : null,

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],
            'stage' => 'Section 2.5 — controlled drugs.',
            'urls' => $this->urls($person, $serviceId) + [
                'record' => route('record7.cd.record', ['client' => $person->id, 'prescription' => $prescribed->id]),
                'receipt' => route('record7.cd.receipt', ['client' => $person->id, 'prescription' => $prescribed->id]),
                'count' => route('record7.cd.count', ['client' => $person->id, 'prescription' => $prescribed->id]),
                'correct' => route('record7.cd.correct', ['client' => $person->id, 'prescription' => $prescribed->id]),
                'notGiven' => route('record7.cd.not-given', ['client' => $person->id, 'prescription' => $prescribed->id]),
            ],
        ]);
    }

    /** Give it. */
    public function record(Request $request, int $client, int $prescription)
    {
        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->controlled($person, $prescription);

        $validated = $request->validate([
            'dose_amount' => ['required', 'numeric', 'min:0.001', 'max:9999'],
            'witness_id' => ['nullable', 'integer'],
            'observed_reason' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:500'],
            'attempt_token' => ['nullable', 'string', 'max:64'],
            'scheduled_dose_id' => ['nullable', 'integer'],
        ]);

        $dose = $this->doseFor($validated['scheduled_dose_id'] ?? null, $person, $prescribed);

        try {
            $result = $this->cd->give(
                $user, Service::find($serviceId), $person, $prescribed, $dose,
                (float) $validated['dose_amount'],
                $validated['witness_id'] ?? null,
                $validated['observed_reason'] ?? null,
                $validated['notes'] ?? null,
                $request,
                $validated['attempt_token'] ?? null
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()
            ->route('record7.cd', ['client' => $person->id])
            ->with('r7.recorded', [
                'created' => $result['created'] ?? true,
                'outcome' => $result['administration']->outcomeWord(),
                'at' => $result['administration']->administered_at->format('H:i'),
                'balance' => $result['entry'] ? $this->register->tidy($result['entry']->balance_after) : null,
            ]);
    }

    /** Taken out of storage, and then not given. */
    public function notGiven(Request $request, int $client, int $prescription)
    {
        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->controlled($person, $prescription);

        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:refused,person_unavailable,not_available'],
            'reason_code' => ['required', 'string', 'max:60'],
            'quantity_removed' => ['required', 'numeric', 'min:0.001', 'max:9999'],
            'quantity_returned' => ['required', 'numeric', 'min:0', 'max:9999'],
            'quantity_wasted' => ['required', 'numeric', 'min:0', 'max:9999'],
            'witness_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
            'scheduled_dose_id' => ['nullable', 'integer'],
        ]);

        try {
            $this->cd->removedButNotGiven(
                $user, Service::find($serviceId), $person, $prescribed,
                $this->doseFor($validated['scheduled_dose_id'] ?? null, $person, $prescribed),
                $validated['outcome'], $validated['reason_code'],
                (float) $validated['quantity_removed'],
                (float) $validated['quantity_returned'],
                (float) $validated['quantity_wasted'],
                $validated['witness_id'] ?? null,
                $validated['notes'] ?? null,
                $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()->route('record7.cd', ['client' => $person->id])
            ->with('r7.recorded', ['created' => true, 'outcome' => 'Accounted for']);
    }

    /** Stock arriving — how a balance legitimately begins. */
    public function receipt(Request $request, int $client, int $prescription)
    {
        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->controlled($person, $prescription);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.001', 'max:99999'],
            'witness_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cd->receive(
                $user, Service::find($serviceId), $person, $prescribed,
                (float) $validated['quantity'], $validated['witness_id'] ?? null,
                $validated['notes'] ?? null, $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return back()->with('r7.recorded', ['created' => true, 'outcome' => 'Booked in']);
    }

    /** Count what is actually there. */
    public function count(Request $request, int $client, int $prescription)
    {
        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->controlled($person, $prescription);

        $validated = $request->validate([
            'counted' => ['required', 'numeric', 'min:0', 'max:99999'],
            'witness_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $entry = $this->cd->count(
                $user, Service::find($serviceId), $person, $prescribed,
                (float) $validated['counted'], $validated['witness_id'] ?? null,
                $validated['notes'] ?? null, $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return back()->with('r7.recorded', [
            'created' => true,
            'outcome' => $entry->is_discrepancy ? 'Counted — it does not agree' : 'Counted',
        ]);
    }

    /**
     * Put right an earlier entry, by adding one — never by editing it.
     *
     * This is also the only way a disagreement between the register and the
     * cupboard is settled: somebody accounts for it. An acknowledgement is not
     * an explanation, and the issue lifecycle already refuses one.
     */
    public function correct(Request $request, int $client, int $prescription)
    {
        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->controlled($person, $prescription);

        $validated = $request->validate([
            'corrects_register_id' => ['required', 'integer'],
            'true_balance' => ['required', 'numeric', 'min:0', 'max:99999'],
            'why' => ['required', 'string', 'min:3', 'max:500'],
            'witness_id' => ['nullable', 'integer'],
        ]);

        // Resolved through this person, never straight from the number sent.
        $original = CdRegister::where('id', $validated['corrects_register_id'])
            ->where('client_id', $person->id)
            ->where('service_id', $serviceId)
            ->first();

        abort_if($original === null, 404, 'That register entry is not theirs.');

        try {
            $this->cd->correct(
                $user, Service::find($serviceId), $person, $prescribed, $original,
                (float) $validated['true_balance'], $validated['witness_id'] ?? null,
                $validated['why'], $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return back()->with('r7.recorded', ['created' => true, 'outcome' => 'Corrected']);
    }

    /* ── Scoping ────────────────────────────────────────────────────────── */

    private function controlled(Client $person, int $prescriptionId): Prescription
    {
        $prescribed = Prescription::with('medicine')
            ->where('id', $prescriptionId)
            ->where('client_id', $person->id)
            ->first();

        abort_if($prescribed === null, 404, 'That medicine is not prescribed for them.');
        abort_if(! $prescribed->medicine?->is_controlled, 404, 'That is not a controlled drug.');

        return $prescribed;
    }

    /** A scheduled dose, if one was named, and only if it is really theirs. */
    private function doseFor(?int $doseId, Client $person, Prescription $prescription): ?ScheduledDose
    {
        if ($doseId === null) {
            return null;
        }

        return ScheduledDose::where('id', $doseId)
            ->where('client_id', $person->id)
            ->where('prescription_id', $prescription->id)
            ->first();
    }

    /** Colleagues in this house who hold the witness permission. */
    private function possibleWitnesses(User $user, ?Service $house): array
    {
        if ($house === null) {
            return [];
        }

        $policy = app(AccessPolicy::class);

        return User::whereIn('id', UserServiceAccess::where('service_id', $house->id)->pluck('user_id'))
            ->where('organisation_id', $house->organisation_id)
            ->where('id', '!=', $user->id)
            ->orderBy('full_name')
            ->get()
            ->filter(fn (User $u) => $u->accessRefusalReason() === null
                && $policy->allows($u, 'witness_medication', $house->id))
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->displayName()])
            ->values()->all();
    }

    private function urls(Client $person, ?int $serviceId = null): array
    {
        /* THE WAY BACK TO WHERE THE WORK IS.
           This screen is reached from a round but is not part of one, so
           finishing here used to leave somebody outside the round with no way
           back into it but Today and a fresh start. Their own round screen
           when a round is open and holds them; Today when it does not, which
           is the honest answer rather than a link that would be refused. */
        $round = $serviceId !== null
            ? $this->personView->openRoundHolding($serviceId, $person)
            : null;

        return [
            'cd' => route('record7.cd', ['client' => $person->id]),
            'person' => route('record7.prn', ['client' => $person->id]),
            'back' => $round
                ? route('record7.round.person', ['client' => $person->id])
                : route('record7.today'),
            'backLabel' => $round ? $person->displayName().'’s round' : 'Today',
            'today' => route('record7.today'),
            'round' => route('record7.round'),
            'houses' => route('record7.houses'),
            'lock' => route('record7.lock.now'),
            'signOut' => route('record7.signout'),
        ];
    }

    private function houseContext(Request $request): array
    {
        $session = $this->sessions->current($request);
        $user = $session?->user;

        abort_if($user === null, 403, 'Not signed in.');

        $serviceId = (int) $request->session()->get('record7.service_id');

        abort_if($serviceId <= 0, 403, 'No house is open.');

        return [$user, $serviceId, $this->authority->check($user, $serviceId)];
    }

    private function personContext(Request $request, int $clientId): array
    {
        [$user, $serviceId, $check] = $this->houseContext($request);

        $house = Service::find($serviceId);

        $person = Client::where('id', $clientId)
            ->where('service_id', $serviceId)
            ->where('organisation_id', $house?->organisation_id)
            ->first();

        abort_if($person === null, 404, 'That person is not in this house.');

        return [$user, $serviceId, $check, $person];
    }
}
