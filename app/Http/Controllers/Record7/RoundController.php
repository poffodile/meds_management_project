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

            'authority' => [
                'allowed' => $check['allowed'],
                'blocked' => $check['blocked'] ?? false,
                'reason' => $check['reason'] ?? null,
            ],

            'stage' => 'Section 2.2 — a scheduled medicine can be recorded as given.',

            'urls' => [
                'round' => route('record7.round'),
                'person' => route('record7.round.person', ['client' => '__ID__']),
                // Placeholders the page fills from the medicines it was given,
                // never from a number the browser invented.
                'confirm' => route('record7.round.confirm', [
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
