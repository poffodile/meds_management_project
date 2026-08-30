<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\Service;
use App\Services\Record7\PrnAdministration;
use App\Services\Record7\RoundAuthority;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;

/**
 * Section 2.4 — as-required medicines, reached through the person.
 *
 * DELIBERATELY NOT BEHIND A ROUND.
 * Somebody in pain at three in the morning is not a medication round. Requiring
 * one would leave a worker with two bad choices: refuse a medicine somebody
 * needs, or open a fake round to get past the software. Neither belongs in a
 * medicines record, so this route asks the person, not the timetable.
 *
 * NOT BEHIND A ROUND IS NOT THE SAME AS UNGUARDED.
 * Every check the round performs still runs here — the account, the
 * organisation, the house, the permission, the competency — asked on every
 * request through the same RoundAuthority the round uses, so the two can never
 * diverge. The only thing dropped is the requirement for an open round, which
 * is a scheduling fact and not a safety one.
 *
 * NOTHING FROM THE BROWSER IS TRUSTED.
 * The house comes from the session, the person from the house, the prescription
 * from the person. A client id or prescription id in a URL is a number somebody
 * typed and is never used to load anything until it has been resolved through
 * the authenticated organisation and selected house.
 */
class PrnController extends R7Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly RoundAuthority $authority,
        private readonly PrnAdministration $prn,
        private readonly RoundPersonView $personView
    ) {
    }

    /** Every as-required medicine this person has, and what is allowed now. */
    public function index(Request $request, int $client)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        $house = Service::find($serviceId);

        return Inertia::render('PrnList', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $this->personView->identityFor($person),
            'safety' => $this->personView->safetyFor($person),
            'medicines' => $this->prn->forPerson($person),

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.4 — as-required medicines.',

            'urls' => [
                'give' => route('record7.prn.give', [
                    'client' => $person->id, 'prescription' => '__PRESCRIPTION__',
                ]),
                'today' => route('record7.today'),
                'round' => route('record7.round'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * The last look before an as-required medicine is given.
     *
     * Shows the whole arithmetic rather than a verdict: when the last dose was,
     * when the next is due, how many they have had in the window and how much.
     * A worker told only "no" cannot tell a safety limit from a bug.
     */
    public function give(Request $request, int $client, int $prescription)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        $prescribed = $this->prn->resolve($person, $prescription);

        abort_if($prescribed === null, 404, 'That as-required medicine is not prescribed for them.');

        $house = Service::find($serviceId);

        return Inertia::render('PrnGive', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $this->personView->identityFor($person),
            'safety' => $this->personView->safetyFor($person),
            'medicine' => $this->prn->describe($prescribed, $person),

            'observedReasons' => collect(PrnAdministration::OBSERVED_REASONS)
                ->map(fn ($word, $code) => ['code' => $code, 'word' => $word])
                ->values()->all(),

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.4 — giving an as-required medicine.',

            'urls' => [
                'record' => route('record7.prn.record', [
                    'client' => $person->id, 'prescription' => $prescribed->id,
                ]),
                'list' => route('record7.prn', ['client' => $person->id]),
                'today' => route('record7.today'),
                'round' => route('record7.round'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * Record it.
     *
     * Every limit is re-checked here. The screen having offered the button is
     * not evidence that it is still allowed — another worker may have given a
     * dose in the meantime, and the interval clock has moved on regardless.
     */
    public function record(Request $request, int $client, int $prescription)
    {
        $validated = $request->validate([
            'dose_amount' => ['required', 'numeric', 'min:0.001', 'max:9999'],
            'observed_reason' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        [$user, $serviceId, $check, $person] = $this->personContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $prescribed = $this->prn->resolve($person, $prescription);

        abort_if($prescribed === null, 404, 'That as-required medicine is not prescribed for them.');

        try {
            $result = $this->prn->record(
                $user, $serviceId, $person, $prescribed,
                (float) $validated['dose_amount'],
                $validated['observed_reason'],
                $validated['notes'] ?? null,
                $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()
            ->route('record7.prn', ['client' => $person->id])
            ->with('r7.recorded', [
                'created' => true,
                'outcome' => $result['administration']->outcomeWord(),
                'at' => $result['administration']->administered_at->format('H:i'),
                'by' => $user->displayName(),
                'reviewAt' => $result['followUp']?->due_at->format('H:i'),
            ]);
    }

    /** Did it work — and did anything worry you? */
    public function review(Request $request, int $followUp)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $check] = $this->houseContext($request);

        $outstanding = PrnFollowUp::with(['administration.prescription.medicine', 'client'])
            ->where('service_id', $serviceId)
            ->find($followUp);

        abort_if($outstanding === null, 404, 'That follow-up is not in this house.');

        $person = $outstanding->client;
        $administration = $outstanding->administration;
        $house = Service::find($serviceId);

        return Inertia::render('PrnReview', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $this->personView->identityFor($person),
            'safety' => $this->personView->safetyFor($person),

            'given' => [
                'medicine' => $administration?->prescription?->medicine?->name,
                'strength' => $administration?->prescription?->medicine?->strength,
                'amount' => $administration?->dose_amount !== null
                    ? (float) $administration->dose_amount : null,
                'unit' => $administration?->dose_unit,
                'unitWord' => $administration && $administration->dose_amount !== null
                    ? $this->prn->unitWord(
                        $administration->dose_unit, (float) $administration->dose_amount
                    )
                    : null,
                'at' => $administration?->administered_at->format('H:i'),
                'by' => $administration?->recordedBy?->displayName(),
                'indication' => $administration?->prescription?->prn_indication,
                'observed' => PrnAdministration::OBSERVED_REASONS[$administration?->reason_code] ?? null,
                'notes' => $administration?->notes,
            ],

            'followUp' => [
                'id' => $outstanding->id,
                'dueAt' => $outstanding->due_at->format('H:i'),
                'answered' => ! $outstanding->isOutstanding(),
            ],

            'concernActions' => collect(PrnAdministration::CONCERN_ACTIONS)
                ->map(fn ($word, $code) => ['code' => $code, 'word' => $word])
                ->values()->all(),

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.4 — did the as-required medicine work?',

            'urls' => [
                'record' => route('record7.prn.review.record', ['followUp' => $outstanding->id]),
                'person' => route('record7.prn', ['client' => $person->id]),
                'today' => route('record7.today'),
                'round' => route('record7.round'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    public function recordReview(Request $request, int $followUp)
    {
        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:effective,partly_effective,not_effective'],
            'notes' => ['nullable', 'string', 'max:500'],
            'concerning_response' => ['nullable', 'boolean'],
            'concern_observed' => ['nullable', 'string', 'max:500'],
            'concern_action_code' => ['nullable', 'string', 'max:60'],
        ]);

        [$user, $serviceId, $check] = $this->houseContext($request);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $outstanding = PrnFollowUp::where('service_id', $serviceId)->find($followUp);

        abort_if($outstanding === null, 404, 'That follow-up is not in this house.');

        try {
            $this->prn->recordReview(
                $user, $serviceId, $outstanding,
                $validated['outcome'],
                $validated['notes'] ?? null,
                (bool) ($validated['concerning_response'] ?? false),
                $validated['concern_observed'] ?? null,
                $validated['concern_action_code'] ?? null,
                $request
            );
        } catch (RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()
            ->route('record7.today')
            ->with('r7.recorded', [
                'created' => true,
                'outcome' => 'Follow-up recorded',
                'at' => now()->format('H:i'),
                'by' => $user->displayName(),
            ]);
    }

    /**
     * The house and the authority to work in it — WITHOUT requiring a round.
     *
     * RoundAuthority::check() is called with no round, so it asks the same six
     * questions the round asks (account, organisation, house access, the access
     * window, permission, competency) and simply omits the seventh, which is
     * about a round that does not need to exist for a PRN.
     */
    private function houseContext(Request $request): array
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $check = $this->authority->check($user, $serviceId);

        return [$user, $serviceId, $check];
    }

    /** The above, plus a person who genuinely lives in that house. */
    private function personContext(Request $request, int $clientId): array
    {
        [$user, $serviceId, $check] = $this->houseContext($request);

        $house = Service::find($serviceId);

        // Three filters before the record is fetched: the house owns them, the
        // organisation owns the house, and the id came from a browser.
        $person = Client::where('id', $clientId)
            ->where('service_id', $serviceId)
            ->where('organisation_id', $house?->organisation_id)
            ->first();

        abort_if($person === null, 404, 'That person is not in this house.');

        return [$user, $serviceId, $check, $person];
    }
}
