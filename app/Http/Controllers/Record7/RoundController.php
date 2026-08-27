<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Round;
use App\Models\Record7\Service;
use App\Services\Record7\RoundAuthority;
use App\Services\Record7\RoundEntry;
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
        private readonly RoundQueue $queue
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
                'window' => $this->window($round),
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
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * The scheduled window this round covers.
     *
     * Derived from the doses in it rather than from a table of shift times,
     * because the round IS its doses.
     */
    private function window(Round $round): array
    {
        $doses = \App\Models\Record7\ScheduledDose::where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->get();

        return [
            'from' => $doses->min('due_at')
                ? \Illuminate\Support\Carbon::parse($doses->min('due_at'))->format('H:i')
                : null,
            'to' => $doses->max('due_at')
                ? \Illuminate\Support\Carbon::parse($doses->max('due_at'))->format('H:i')
                : null,
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
