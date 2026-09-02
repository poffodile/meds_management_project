<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Handover;
use App\Models\Record7\HandoverRead;
use App\Models\Record7\Service;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use App\Services\Record7\ShiftBoard;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Section 1.1 — the support worker's Today screen for one house.
 *
 * WHAT THIS SCREEN IS FOR
 * Somebody has just walked in, or come back from a break, and needs to know
 * what to do next. Not how the house has been performing: what to do next.
 * Everything on it is either something to act on or something they would
 * otherwise have to ask a colleague.
 *
 * ORDER IS THE DESIGN
 * Handover, then what is wrong, then where the day is, then who is waiting,
 * then what is still on this person specifically, then what is already done.
 * That is the order the questions actually arrive in on a shift, and on a
 * phone it is the order they are scrolled through.
 *
 * WHAT IT DELIBERATELY DOES NOT SHOW
 * No manager statistics, no organisation-wide anything, no charts. Every query
 * is bound to the one house the session is in, which the person has already
 * proved they may enter.
 *
 * The numbering, so nothing drifts again: 1.1 is this screen, 1.2 is Manager
 * Today, and Section 2 is the Medication Round. Neither of those is built.
 */
class TodayController extends R7Controller
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly ShiftBoard $board,
        private readonly AuditRecorder $audit
    ) {
    }

    public function index(Request $request)
    {
        $this->useR7Layout($request);
        $this->requirePermission($request, 'view_dashboard');

        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $granted = $this->policy->grantedPermissions($user, $serviceId);

        // Whether the round button does anything is an authorisation question,
        // answered here rather than in the page. A person who may not record
        // still sees the round — knowing what is outstanding is part of being
        // useful on a shift — but is told plainly that they cannot record it.
        $mayRecord = $this->policy->decide($user, 'administer_medication', $serviceId);

        return Inertia::render('Today', [
            'name' => $user->displayName(),
            'house' => Service::find($serviceId)?->name,
            'greeting' => $this->greeting(),
            'today' => now()->format('l j F'),

            'handover' => $this->board->handover($serviceId, $user),
            'attention' => $this->board->needsAttention($serviceId),
            'overview' => $this->board->overview($serviceId),
            'round' => $this->board->round($serviceId),
            'peopleDue' => $this->board->peopleDue($serviceId),
            // Just the next one. The whole day's schedule is not what
            // "what comes next" means, and it is not Today's job.
            'nextRound' => $this->board->laterToday($serviceId)[0] ?? null,
            'tasks' => $this->board->myTasks($user, $serviceId),
            'completed' => $this->board->recentlyCompleted($serviceId),

            'can' => [
                'record' => $mayRecord->allowed,
                'recordRefusal' => $mayRecord->allowed ? null : $mayRecord->message,
                'viewPeople' => in_array('view_people', $granted, true),

                /* STOCK IS OFFERED TO THE PEOPLE WHOSE JOB IT IS.
                   Reading the stock page is allowed to anybody who may see the
                   dashboard, and that has not changed — this decides whether
                   the way in is PUT IN FRONT of somebody, which is a different
                   question from whether they may look. A support worker with no
                   stock duty gets a menu about their own work; the two stock
                   authorities get the door. Every write on that page is gated
                   again by its own route middleware and again in the
                   controller, so a visible page is never a granted action. */
                'viewStock' => in_array('stock_management', $granted, true)
                    || in_array('reconciliation', $granted, true),

                /* THE TWO SENIOR DESTINATIONS.
                   Manager Today and the access audit were both built, both
                   tested, and referenced by nothing: the only mention of
                   /record7/manager in the whole front end was the manager
                   page's own nav item marking itself as current. A service
                   manager holding every senior permission could not navigate
                   to the screen that is their job.

                   Offered on the permission the destination itself enforces,
                   so the menu and the door agree. Being shown a way in is not
                   authority to do anything once inside: every action on both
                   screens is re-checked server-side against this house. */
                'viewManager' => in_array('view_manager_dashboard', $granted, true),
                'viewAudit' => in_array('view_access_audit', $granted, true),
            ],

            'urls' => [
                'startRound' => route('record7.round.start'),
                'readHandover' => route('record7.handover.read'),

                /* WHERE TODAY CAN SEND SOMEBODY.
                   Today names work in three places — the round, an as-required
                   follow-up, the stock a house holds — and until now none of
                   them was a link. A board that says what is wrong and offers
                   no way to it teaches people to work around the screen. */
                'today' => route('record7.today'),
                'round' => route('record7.round'),
                'stock' => route('record7.stock'),
                'manager' => route('record7.manager'),
                'audit' => route('record7.audit'),
                'person' => route('record7.round.person', ['client' => '__ID__']),
                'prnFollowUp' => route('record7.prn.review', ['followUp' => '__ID__']),

                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /**
     * "I have read the handover."
     *
     * A handover nobody confirms reading is a handover that was not handed
     * over. This is what turns notes left on a screen into an actual transfer
     * of responsibility, so it is recorded per person and audited.
     *
     * It needs no permission beyond being in the house: reading what the last
     * shift left is not a privileged act, and gating it would mean somebody
     * who may not administer also may not confirm they have been told.
     */
    public function readHandover(Request $request)
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $data = $request->validate(['handover_id' => ['required', 'integer']]);

        // Scoped to this house, so a handover id from elsewhere cannot be
        // acknowledged by somebody who was never given it.
        $handover = Handover::where('id', $data['handover_id'])
            ->where('service_id', $serviceId)
            ->first();

        abort_unless($handover !== null, 404);

        $read = HandoverRead::firstOrCreate(
            ['handover_id' => $handover->id, 'user_id' => $user->id],
            ['read_at' => now()]
        );

        if ($read->wasRecentlyCreated) {
            $this->audit->record(
                eventType: 'handover_read',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $serviceId,
                reason: $handover->shift.' handover',
                metadata: ['handover_id' => $handover->id],
                request: $request
            );
        }

        return redirect()->route('record7.today');
    }

    /** "Good morning" is not decoration — it tells you the shift you are on. */
    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }
}
