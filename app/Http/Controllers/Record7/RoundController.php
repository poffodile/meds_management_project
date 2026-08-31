<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\Round;
use App\Models\Record7\Service;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\RoundAuthority;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\RoundQueue;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Section 2.0 — the round workspace, and safe entry to it.
 *
 * NOTHING IS ADMINISTERED HERE. There is no control on this screen that records
 * an outcome, and no method on this controller that writes to
 * record7_administrations. Giving a medicine begins at 2.2. This section exists
 * to get the right person into the right round with their authority checked,
 * and to show them who is waiting.
 *
 * EVERY REQUEST RE-CHECKS AUTHORITY.
 * Not once at login — on every entry and on every load of the workspace. A
 * competency can expire, access can be suspended and a round can be closed in
 * the middle of a shift, and a product that checked at sign-in would carry on
 * as though none of that had happened.
 *
 * NOTHING FROM THE BROWSER IS TRUSTED.
 * The house comes from the session. The round is resolved through that house.
 * A round id, house id or person id in a request is never used to find anything
 * without being filtered by the authenticated organisation and selected house
 * first.
 */
class RoundController extends R7Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly RoundAuthority $authority,
        private readonly RoundEntry $entry,
        private readonly RoundQueue $queue,
        private readonly RoundPersonView $personView,
        private readonly AdministrationRecorder $recorder
    ) {
    }

    /**
     * Start the round, join the one already open, or resume your own.
     *
     * Which of the three it is depends on the state of the world, not on which
     * button was pressed — a person who taps Start when a colleague has already
     * opened the round joins it, and a person returning to their own round
     * resumes it.
     */
    public function enter(Request $request)
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $check = $this->authority->check($user, $serviceId);

        if (! $check['allowed']) {
            $this->authority->refuse($user, $serviceId, $check, $request);
            abort(403, $check['reason']);
        }

        // The slot comes from the record, never from the request. A posted slot
        // could name a round this house is not currently working on.
        $slot = $this->entry->currentSlot($serviceId);

        if ($slot === null) {
            return redirect()->route('record7.today');
        }

        $this->noteAnyRoundsLeftBehind($user, $serviceId, $request);

        $result = $this->entry->enter($user, $serviceId, $slot, $request);

        return redirect()->route('record7.round');
    }

    /**
     * The round workspace.
     *
     * Resolves the round from the session's house. There is no round id in the
     * URL by design: a round is not something you can navigate to by guessing a
     * number, it is whatever is open in the house you are standing in.
     */
    public function show(Request $request)
    {
        $this->useR7Layout($request);

        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $round = $this->entry->openRoundFor($serviceId);

        if (! $round) {
            return redirect()->route('record7.today');
        }

        // Authority is re-checked HERE, on every load, not inherited from
        // whatever was true when the round was opened.
        $check = $this->authority->checkContinuing($user, $serviceId, $round, $request);

        $house = Service::find($serviceId);

        return Inertia::render('Round', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'round' => [
                'id' => $round->id,
                'slot' => $round->slot,
                'date' => $round->round_date->format('l j F'),
                'status' => $round->status(),
                'openedBy' => $round->openedBy?->displayName(),
                'startedAt' => $round->started_at->format('H:i'),
                'closedAt' => $round->closed_at?->format('H:i'),
                // The scheduled window this round covers, from the doses.
                'window' => $this->queue->window($round),
            ],
            'participants' => $this->entry->participants($round),
            'progress' => $this->queue->progress($round),
            // Blocked people still see who is waiting — knowing the round is
            // half done matters even when you may not touch it — but there is
            // nothing here to press.
            'queue' => $this->queue->forRound($round),

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
                'competencyExpires' => $this->authority->competencyExpiry($user, $serviceId),
            ],

            // Stated plainly so nobody mistakes this screen for a working one.
            'stage' => 'Section 2.0 — safe entry only. Recording begins at Section 2.2.',

            'urls' => [
                // The id is a placeholder the page fills in from the queue it
                // was given — never a number the browser invented.
                'person' => route('record7.round.person', ['client' => '__ID__']),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * Section 2.1 — one person, checked before anything is given.
     *
     * The client id in the URL is a number somebody typed. It is resolved
     * through the organisation, the house AND the round before any record is
     * loaded, so a person from another company, another house or another round
     * is not found rather than merely not shown.
     *
     * Authority is re-checked here too. Having entered the round earlier is not
     * a reason to be allowed in now — a competency can expire between the queue
     * and the person.
     */
    public function person(Request $request, int $client)
    {
        $this->useR7Layout($request);

        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $round = $this->entry->openRoundFor($serviceId);

        if (! $round) {
            return redirect()->route('record7.today');
        }

        $check = $this->authority->checkContinuing($user, $serviceId, $round, $request);

        $person = $this->personView->resolve($round, $client);

        // Not "no permission" and not a redirect to somebody else. A person who
        // is not in this round does not exist as far as this screen is
        // concerned, and silently falling back to another person would be the
        // most dangerous possible response.
        abort_if($person === null, 404, 'That person is not in this round.');

        $view = $this->personView->forPerson($round, $person);
        $queue = $this->queue->forRound($round);
        $house = Service::find($serviceId);

        return Inertia::render('RoundPerson', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'round' => [
                'id' => $round->id,
                'slot' => $round->slot,
                'date' => $round->round_date->format('l j F'),
                'status' => $round->status(),
                'window' => $this->queue->window($round),
            ],
            'progress' => $this->queue->progress($round),

            'person' => $view['person'],
            'safety' => $view['safety'],
            'medicines' => $view['medicines'],
            'neighbours' => $this->personView->neighbours($queue, $person->id),

            // An unanswered "could not be found" concern for this person. Its
            // presence is what puts the action on the screen; nothing else can.
            'welfareConcern' => $this->recorder->openWelfareConcernFor($serviceId, $person->id)
                ? ['url' => route('record7.round.welfare', ['client' => $person->id])]
                : null,

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Sections 2.2 and 2.3 — a scheduled medicine can be recorded as '
                .'given, or answered with why it was not.',

            'urls' => [
                'round' => route('record7.round'),
                'person' => route('record7.round.person', ['client' => '__ID__']),
                // Placeholders the page fills from the medicines it was given,
                // never from a number the browser invented.
                'confirm' => route('record7.round.confirm', [
                    'client' => '__ID__', 'dose' => '__DOSE__',
                ]),
                'outcome' => route('record7.round.outcome', [
                    'client' => '__ID__', 'dose' => '__DOSE__',
                ]),
                'reoffer' => route('record7.round.reoffer', [
                    'client' => '__ID__', 'dose' => '__DOSE__',
                ]),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * Section 2.2 — the last screen before a medicine is recorded as given.
     *
     * A SEPARATE SCREEN, ON PURPOSE.
     * Opening a medicine and recording it must not be the same gesture. A tap
     * that both reveals and commits is how a thumb resting on a phone during a
     * scroll signs for a dose nobody gave. So the person view links here, and
     * only here is there a control that writes anything.
     *
     * IT SHOWS EVERYTHING AGAIN.
     * Person, allergies, medicine, strength, form, dose, route, due time,
     * instructions and the support arrangement — all of it, on the screen where
     * the decision is made. "Are you sure?" over a hidden context is not a
     * check, it is a habit.
     */
    public function confirm(Request $request, int $client, int $dose)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        return Inertia::render('RoundConfirm', $this->confirmProps(
            $serviceId, $round, $person, $scheduled, $check
        ));
    }

    /**
     * Record that this planned dose was given.
     *
     * NOTHING FROM THE BROWSER IS TRUSTED, INCLUDING WHO IS DOING IT.
     * The house comes from the session, the round from the house, the person
     * from the round, the dose from the person — and the worker from the
     * authenticated user. A posted staff id is not read at all, so a medicine
     * cannot be signed in somebody else's name.
     *
     * AUTHORITY IS ASKED AGAIN HERE, NOT INHERITED FROM THE LAST SCREEN.
     * A competency can expire between reading the label and pressing the
     * button. When that happens the planned dose survives untouched, the audit
     * survives, and the worker is told that somebody else has to continue.
     */
    public function record(Request $request, int $client, int $dose)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],

            // Section 2.7. Present only where the record could not cover the
            // dose. Validated for shape here; whether it is REQUIRED is
            // decided under the balance lock, because the answer can change
            // between opening the screen and pressing the button.
            'shortfall_basis' => ['nullable', 'string', 'max:60'],
            'shortfall_statement' => ['nullable', 'string', 'max:190'],
            'shortfall_observed_quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        $eligibility = $this->recorder->eligibility($scheduled, $person);

        // An already-recorded dose is a retry, not an error. It falls through
        // to the recorder, which answers with the record that already exists.
        if (! $eligibility['allowed'] && $eligibility['code'] !== 'already_recorded') {
            return back()->with('r7.error', $eligibility['reason']);
        }

        $result = $this->recorder->recordGiven(
            $user, $round, $person, $scheduled, $validated['notes'] ?? null, $request
        );

        return redirect()
            ->route('record7.round.person', ['client' => $person->id])
            ->with('r7.recorded', [
                'doseId' => $scheduled->id,
                'created' => $result['created'],
                // The SAME word the medicine itself will show a line below.
                // "Recorded: Given" over an item reading "Given with help" is
                // one fact told two ways in the space of one screen.
                'outcome' => $this->personView->recordedWord(
                    $result['administration'],
                    $scheduled->prescription?->support_type ?? 'staff_administered'
                ),
                'at' => $result['administration']->administered_at->format('H:i'),
                'by' => $result['administration']->recordedBy?->displayName(),
            ]);
    }

    /**
     * Section 2.3 — recording that a planned medicine was NOT given.
     *
     * A SEPARATE SCREEN FROM "GIVEN", AND FROM THE ROUND.
     * Four different things can have happened, and they are not interchangeable:
     * the person said no; the person was not there; the medicine was not there;
     * the round never reached them. Collapsing those into one "not given"
     * button is how a medicines record stops being able to answer the question
     * an inspection actually asks.
     *
     * NOTHING IS PRESELECTED. Callum's client status says he is in hospital,
     * and the screen still will not choose "person unavailable" for the worker.
     * A status is a fact about where somebody is; an outcome is a statement
     * about what a worker did, and only a person can make that statement.
     */
    public function outcome(Request $request, int $client, int $dose)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        return Inertia::render('RoundOutcome', $this->outcomeProps(
            $serviceId, $round, $person, $scheduled, $check
        ));
    }

    /**
     * Section 2.3 — recording that somebody was found.
     *
     * The ONLY thing that answers a "could not be found" concern. Not
     * acknowledging it, not owning it, not writing a note, not closing a
     * review item, and not recording an unrelated medicine for them later —
     * none of those establish where a person is.
     *
     * It does not classify anything as safeguarding. Saying you found somebody
     * says exactly that; whether it becomes a safeguarding matter is a
     * judgement for a manager and the provider's policy.
     */
    public function welfare(Request $request, int $client)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        $concern = $this->recorder->openWelfareConcernFor($serviceId, $person->id);

        abort_if($concern === null, 404, 'There is no open welfare concern for this person.');

        $view = $this->personView->forPerson($round, $person);
        $house = Service::find($serviceId);

        return Inertia::render('RoundWelfare', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'person' => $view['person'],
            'safety' => $view['safety'],

            'concern' => [
                'id' => $concern->id,
                'at' => $concern->administered_at->format('H:i'),
                'by' => $concern->recordedBy?->displayName(),
                'note' => $concern->notes,
            ],

            'resolutions' => collect(\App\Models\Record7\WelfareCheck::RESOLUTION_WORDS)
                ->map(fn ($word, $code) => ['code' => $code, 'word' => $word])
                ->values()->all(),

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.3 — recording that somebody was found.',

            'urls' => [
                'record' => route('record7.round.welfare.record', ['client' => $person->id]),
                'person' => route('record7.round.person', ['client' => $person->id]),
                'round' => route('record7.round'),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    public function recordWelfare(Request $request, int $client)
    {
        $validated = $request->validate([
            'resolution_type' => ['required', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $concern = $this->recorder->openWelfareConcernFor($serviceId, $person->id);

        abort_if($concern === null, 404, 'There is no open welfare concern for this person.');

        try {
            $this->recorder->recordWelfareCheck(
                $user, $serviceId, $concern,
                $validated['resolution_type'], $validated['note'] ?? null, $request
            );
        } catch (\RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()
            ->route('record7.round.person', ['client' => $person->id])
            ->with('r7.recorded', [
                'doseId' => null,
                'created' => true,
                'outcome' => 'Welfare check recorded',
                'at' => now()->format('H:i'),
                'by' => $user->displayName(),
            ]);
    }

    /**
     * Section 2.3 — offering a refused dose again.
     *
     * THIS IS NOT A CORRECTION AND NOT A NEW DOSE.
     * It is a second attempt at the SAME planned obligation. She said no at
     * eight; it is now nine and somebody is asking again. Both answers are
     * true, both stay on the record, and the screen says so before anything is
     * recorded — a second attempt that hid the first would read as though the
     * refusal never happened.
     *
     * Every safeguard the first attempt was held to still applies: support
     * type, controlled drugs, as-required medicines, competency, permission,
     * the house and the organisation. A re-offer is a way back to the same
     * dose, not a way around anything.
     */
    public function reoffer(Request $request, int $client, int $dose)
    {
        $this->useR7Layout($request);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        $refusal = $this->recorder->openRefusalFor($scheduled);

        // Not "no permission" — there is genuinely nothing here to offer again.
        abort_if($refusal === null, 404, 'There is no refusal on this dose to offer again.');

        $props = $this->outcomeProps($serviceId, $round, $person, $scheduled, $check);

        // A second offer ends one of two ways. The other outcomes describe what
        // happened to the original obligation rather than how an offer went.
        $props['outcomes'] = [
            [
                'code' => 'given',
                'word' => 'They took it this time',
                'meaning' => 'You offered it again and they accepted it.',
                'tone' => 'success',
                'reasons' => [],
            ],
            [
                'code' => 'refused',
                'word' => 'They refused it again',
                'meaning' => 'You offered it again and they still said no.',
                'tone' => 'warning',
                'reasons' => AdministrationRecorder::REFUSAL_REASONS,
            ],
        ];

        $props['reoffer'] = [
            'of' => $refusal->id,
            'word' => $refusal->outcomeWord(),
            'at' => $refusal->administered_at->format('H:i'),
            'by' => $refusal->recordedBy?->displayName(),
            'reason' => $refusal->reason_code
                ? (AdministrationRecorder::REASON_WORDS[$refusal->reason_code] ?? $refusal->reason_code)
                : null,
            'notes' => $refusal->notes,

            // HOW LATE IT IS NOW, not how late the refusal was.
            // A dose stops being "late" the moment it is answered — right for
            // the chase lists, wrong here: somebody offering a medicine again
            // twelve hours after it was due needs to know that before they
            // decide, and the due time alone makes them do the arithmetic.
            'stillLate' => $scheduled->due_at->lessThan(now())
                ? $scheduled->due_at->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2])
                : null,
        ];

        $props['stage'] = 'Section 2.3 — offering the same planned dose again.';
        $props['urls']['record'] = route('record7.round.reoffer.record', [
            'client' => $person->id, 'dose' => $scheduled->id,
        ]);

        return Inertia::render('RoundOutcome', $props);
    }

    /**
     * Record how the second offer went.
     *
     * The refusal being answered is resolved on the server from the dose, never
     * taken from the request — so a posted id cannot attach this attempt to
     * somebody else's refusal.
     */
    public function recordReoffer(Request $request, int $client, int $dose)
    {
        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:given,refused'],
            'reason_code' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        $refusal = $this->recorder->openRefusalFor($scheduled);

        abort_if($refusal === null, 404, 'There is no refusal on this dose to offer again.');

        // Every safeguard, asked again as a re-offer.
        $eligibility = $this->recorder->eligibility($scheduled, $person, asReoffer: true);

        if (! $eligibility['allowed']) {
            return back()->with('r7.error', $eligibility['reason']);
        }

        try {
            $result = $validated['outcome'] === 'given'
                ? $this->recorder->recordGiven(
                    $user, $round, $person, $scheduled, $validated['notes'] ?? null, $request, $refusal
                )
                : $this->recorder->recordNonAdministration(
                    $user, $round, $person, $scheduled, 'refused',
                    $validated + ['reoffer_of_administration_id' => $refusal->id],
                    $request
                );
        } catch (\RuntimeException $refused) {
            return back()->with('r7.error', $refused->getMessage());
        }

        return redirect()
            ->route('record7.round.person', ['client' => $person->id])
            ->with('r7.recorded', [
                'doseId' => $scheduled->id,
                'created' => $result['created'],
                'outcome' => $result['administration']->outcomeWord(),
                'at' => $result['administration']->administered_at->format('H:i'),
                'by' => $result['administration']->recordedBy?->displayName(),
            ]);
    }

    /**
     * Why this medicine cannot be answered on the ordinary Section 2.3 screen.
     *
     * A screen must never offer an action the server would refuse. An
     * as-required medicine, a fully self-managed one and a suspended
     * prescription all belong somewhere else — and a worker who fills in a
     * reason and a note before being told that has been made to waste the one
     * thing a medicines round has least of.
     *
     * Note what is NOT here: a controlled drug, an absent person and a
     * self-administered medicine are all answerable. Those are precisely the
     * cases this section exists for.
     */
    private function outcomeBlockedReason($scheduled): ?string
    {
        $prescription = $scheduled->prescription;

        if (! $prescription) {
            return 'This dose has no prescription attached to it.';
        }

        if ($prescription->kind === 'prn') {
            return 'This is an as-required medicine. Recording it needs the as-required '
                .'workflow, which is not built yet.';
        }

        if ($prescription->isFullySelfManaged()) {
            return 'This medicine is fully self-managed. It does not need an individual '
                .'staff record, so there is nothing to answer here.';
        }

        if ($prescription->status !== 'active') {
            return 'This prescription is '.$prescription->status.'. Ask before recording '
                .'anything against it.';
        }

        return null;
    }

    /**
     * Write the non-administration outcome.
     *
     * Authority is re-checked here, not inherited from the screen: a competency
     * can expire between choosing a reason and pressing the button. The dose,
     * the person, the house and the organisation are all resolved from the
     * session rather than from the numbers in the URL.
     */
    public function recordOutcome(Request $request, int $client, int $dose)
    {
        $validated = $request->validate([
            'outcome' => ['required', 'string', 'in:refused,person_unavailable,not_available,missed'],
            'reason_code' => ['required', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:500'],
            'action_taken' => ['nullable', 'string', 'max:500'],
            'immediate_action_code' => ['nullable', 'string', 'max:80'],
            'controlled_drug_no_quantity_removed' => ['nullable', 'boolean'],
        ]);

        [$user, $serviceId, $round, $check, $person] = $this->roundContext($request, $client);

        if (! $check['allowed']) {
            return back()->with('r7.error', $check['reason']);
        }

        $scheduled = $this->recorder->resolve($round, $person, $dose);

        abort_if($scheduled === null, 404, 'That medicine is not in this round.');

        try {
            $result = $this->recorder->recordNonAdministration(
                $user, $round, $person, $scheduled, $validated['outcome'], $validated, $request
            );
        } catch (\RuntimeException $refusal) {
            // Said in the page, not thrown at the worker as a server error.
            return back()->with('r7.error', $refusal->getMessage());
        }

        return redirect()
            ->route('record7.round.person', ['client' => $person->id])
            ->with('r7.recorded', [
                'doseId' => $scheduled->id,
                'created' => $result['created'],
                'outcome' => $result['administration']->outcomeWord(),
                'at' => $result['administration']->administered_at->format('H:i'),
                'by' => $result['administration']->recordedBy?->displayName(),
            ]);
    }

    /** Everything the outcome screen needs, in the order it is read. */
    private function outcomeProps(
        int $serviceId, Round $round, Client $person, $scheduled, array $check
    ): array {
        $view = $this->personView->forPerson($round, $person);
        $medicine = collect($view['medicines'])->firstWhere('doseId', $scheduled->id);
        $house = Service::find($serviceId);

        return [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'round' => [
                'slot' => $round->slot,
                'date' => $round->round_date->format('l j F'),
            ],

            'person' => $view['person'],
            'safety' => $view['safety'],
            'medicine' => $medicine,

            // Said before anything is filled in, not after it is submitted.
            'blockedReason' => $this->outcomeBlockedReason($scheduled),

            // The four things that can have happened, each with the reasons
            // that belong to it. Sent from the server so the screen can never
            // offer a combination the recorder would refuse.
            'outcomes' => [
                [
                    'code' => 'refused',
                    'word' => 'They refused it',
                    'meaning' => 'You offered it properly and they said no.',
                    'tone' => 'warning',
                    'reasons' => AdministrationRecorder::REFUSAL_REASONS,
                ],
                [
                    'code' => 'person_unavailable',
                    'word' => 'They were not here',
                    'meaning' => 'You could not offer it because they were not available to this service.',
                    'tone' => 'info',
                    'reasons' => AdministrationRecorder::PERSON_UNAVAILABLE_REASONS,
                ],
                [
                    'code' => 'not_available',
                    'word' => 'The medicine was not available',
                    'meaning' => 'The medicine itself could not be given. Nothing about the person.',
                    'tone' => 'error',
                    'reasons' => AdministrationRecorder::MEDICINE_UNAVAILABLE_REASONS,
                ],
                [
                    'code' => 'missed',
                    'word' => 'It was missed',
                    'meaning' => 'Nobody gave it and nobody recorded why at the time. '
                        .'This is a medication error and needs more from you.',
                    'tone' => 'error',
                    'reasons' => AdministrationRecorder::MISSED_REASONS,
                ],
            ],

            'reasonWords' => AdministrationRecorder::REASON_WORDS,
            'missedActions' => AdministrationRecorder::MISSED_ACTIONS,
            'missedActionWords' => AdministrationRecorder::MISSED_ACTION_WORDS,

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.3 — recording why a medicine was not given.',

            'urls' => [
                'record' => route('record7.round.outcome.record', [
                    'client' => $person->id, 'dose' => $scheduled->id,
                ]),
                'person' => route('record7.round.person', ['client' => $person->id]),
                'round' => route('record7.round'),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ];
    }

    /**
     * House, round, authority and person — resolved the same way for every
     * Section 2.2 request, so no route can quietly skip one of them.
     */
    private function roundContext(Request $request, int $clientId): array
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $round = $this->entry->openRoundFor($serviceId);

        abort_if($round === null, 409, 'There is no open round in this house.');

        $check = $this->authority->checkContinuing($user, $serviceId, $round, $request);

        $person = $this->personView->resolve($round, $clientId);

        abort_if($person === null, 404, 'That person is not in this round.');

        return [$user, $serviceId, $round, $check, $person];
    }

    /** Everything the confirmation screen needs, in the order it is read. */
    /**
     * What Record7 can say about the stock behind this dose.
     *
     * Three honest states and one warning, all of them said in words rather
     * than left to a blank space. `sufficient` false is not a refusal: it is
     * the moment the screen asks somebody to go and look.
     *
     * @return array{state:string, sufficient:bool, balance:?string, needed:?string,
     *               shortfall:?string, unit:?string}
     */
    private function stockNotice(Client $person, $scheduled): array
    {
        $position = app(AdministrationRecorder::class)->stockPosition($person, $scheduled);
        $ledger = app(\App\Services\Record7\StockLedger::class);

        return [
            'state' => $position['state'],
            'sufficient' => $position['sufficient'],
            'balance' => $position['balance']
                ? $ledger->tidy($position['balance']->current_balance) : null,
            'needed' => $position['quantity'] !== null
                ? $ledger->tidy($position['quantity']) : null,
            'shortfall' => $position['shortfall'] > 0
                ? $ledger->tidy($position['shortfall']) : null,
            'unit' => $position['unit'],
        ];
    }

    private function confirmProps(
        int $serviceId, Round $round, Client $person, $scheduled, array $check
    ): array {
        $view = $this->personView->forPerson($round, $person);
        $medicine = collect($view['medicines'])->firstWhere('doseId', $scheduled->id);
        $house = Service::find($serviceId);
        $support = $scheduled->prescription?->support_type;

        return [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'round' => [
                'slot' => $round->slot,
                'date' => $round->round_date->format('l j F'),
                'window' => $this->queue->window($round),
            ],

            'person' => $view['person'],
            'safety' => $view['safety'],
            'medicine' => $medicine,

            // Said in the first person, so the worker reads a sentence about
            // what THEY are about to record rather than a category name.
            'confirmation' => AdministrationRecorder::CONFIRMATION[$support] ?? null,

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            /* SECTION 2.7. WHAT THE CUPBOARD SAYS, BEFORE THE BUTTON.
             *
             * A worker about to give a dose needs to know the record cannot
             * cover it BEFORE they press Given, not afterwards. Where it
             * cannot, the screen says so and asks what they physically
             * checked — and the dose is still recordable, because refusing to
             * record a medicine somebody actually gave would make the record
             * less truthful than the room. */
            'stock' => $this->stockNotice($person, $scheduled),

            'shortfallBases' => \App\Models\Record7\StockMovement::SHORTFALL_BASES,

            'stage' => 'Section 2.2 — recording that a medicine was given. '
                .'Refusals and omissions begin at Section 2.3.',

            'urls' => [
                'record' => route('record7.round.record', [
                    'client' => $person->id, 'dose' => $scheduled->id,
                ]),
                'person' => route('record7.round.person', ['client' => $person->id]),
                'round' => route('record7.round'),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ];
    }

    /**
     * Notice, and record, a round left open in another house.
     *
     * Not an error — people move between houses legitimately. It is audited
     * because a round abandoned in one house while its worker is in another is
     * something a manager may need to know, and because nothing about that
     * round should follow them across.
     */
    private function noteAnyRoundsLeftBehind($user, int $serviceId, Request $request): void
    {
        foreach ($this->entry->openRoundsInOtherHouses($user, $serviceId) as $elsewhere) {
            $this->authority->noteHouseChange($user, $elsewhere, $serviceId, $request);
        }
    }
}
