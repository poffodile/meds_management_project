<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\Service;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\ManagerActions;
use App\Services\Record7\ManagerBoard;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Section 1.2 — Manager Today, for one house.
 *
 * FUNCTIONALITY FIRST, ON PURPOSE.
 * The page this renders is deliberately plain: headings, lists and buttons,
 * built to be read by a test and clicked by a reviewer. It establishes no
 * visual language and shares nothing new with the design system, because the
 * real Manager Today interface is being designed separately and anything
 * decorative here would only have to be thrown away.
 *
 * What IS finished is underneath: the queries, the scoping, the permissions and
 * the actions. The data contract at the bottom of this file is what the eventual
 * design will consume.
 *
 * THE HOUSE IS THE BOUNDARY.
 * Daniel Evans manages two houses. He is only ever IN one, and every figure on
 * this page comes from that one. Switching house replaces the entire dataset
 * because the session's service id changes and every query is keyed to it —
 * there is no cross-house method on ManagerBoard to accidentally call.
 */
class ManagerController extends R7Controller
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly ManagerBoard $board,
        private readonly ManagerActions $actions
    ) {
    }

    public function index(Request $request)
    {
        $this->useR7Layout($request);
        $this->requirePermission($request, 'view_manager_dashboard');

        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        $house = Service::find($serviceId);

        /*
         * THE DATA CONTRACT
         *
         * Everything below is house-scoped and derived at request time. The
         * eventual design consumes exactly this shape:
         *
         *   house       {id, name, organisation}   which house this all is
         *   attention[] risk-ordered, one list     house, subject, issue, why,
         *                                          next, severity, at
         *               lifecycle, all separate:   acknowledged, owner,
         *                                          escalated, actionRecorded,
         *                                          closed (+ reason, evidence)
         *               and the one that decides
         *               visibility:                conditionActive — asked of
         *                                          the clinical record, never
         *                                          of the workflow
         *               plus a sentence:           status
         *   rounds[]    one per slot today         state, expected/completed/
         *                                          remaining people, late,
         *                                          openedBy, startedAt,
         *                                          interventionNeeded
         *   staff[]     medication-relevant only   role | permission |
         *                                          competency, kept separate,
         *                                          then mayAdminister + reason
         *   outcomes    {omissions, refusals,      unresolved only
         *                notTaken, incompleteRecords,
         *                prnFollowUps}
         *   review      {open[], decided[]}        the queue and its history
         *   stock[]     exceptions only            out, low, discrepancy, CD
         *   handovers[] last three                 acknowledged[] vs outstanding[]
         *   can         {} what this manager may do in THIS house
         */
        return Inertia::render('Manager', [
            'house' => [
                'id' => $house?->id,
                'name' => $house?->name,
                'organisation' => $house?->organisation_id,
            ],
            'today' => now()->format('l j F'),
            'name' => $user->displayName(),

            'attention' => $this->board->attention($serviceId),
            'rounds' => $this->board->rounds($serviceId),
            'staff' => $this->board->staffReadiness($serviceId),
            'outcomes' => $this->board->outstandingOutcomes($serviceId),
            'review' => [
                'open' => $this->board->openReviewItems($serviceId, $user),
                'decided' => $this->board->decidedReviewItems($serviceId),
            ],
            'stock' => $this->board->stockConcerns($serviceId),
            'handovers' => $this->board->handoverOversight($serviceId),

            'can' => [
                'decideCorrections' => $this->policy->allows($user, 'correction_approval', $serviceId),
                'reviewIncidents' => $this->policy->allows($user, 'incident_review', $serviceId),
                'manageStock' => $this->policy->allows($user, 'stock_management', $serviceId),
            ],

            'urls' => [
                'own' => route('record7.manager.own'),
                'acknowledge' => route('record7.manager.acknowledge'),
                'escalate' => route('record7.manager.escalate'),
                'recordAction' => route('record7.manager.action'),
                'close' => route('record7.manager.close'),
                'decide' => route('record7.manager.decide'),
                'closeRound' => route('record7.manager.round.close'),
                'houses' => route('record7.houses'),
                'today' => route('record7.today'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /* ── Actions ────────────────────────────────────────────────────────── */

    public function own(Request $request)
    {
        $data = $request->validate(['issue_key' => ['required', 'string', 'max:120']]);

        $this->actions->takeOwnership(
            $this->manager(),
            $this->house($request),
            $data['issue_key'],
            $request
        );

        return redirect()->route('record7.manager');
    }

    public function escalate(Request $request)
    {
        $data = $request->validate([
            'issue_key' => ['required', 'string', 'max:120'],
            'to_user_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->actions->escalate(
            $this->manager(),
            $this->house($request),
            $data['issue_key'],
            $data['to_user_id'] ?? null,
            $data['note'] ?? null,
            $request
        );

        return redirect()->route('record7.manager');
    }

    public function acknowledge(Request $request)
    {
        $data = $request->validate(['issue_key' => ['required', 'string', 'max:120']]);

        $this->actions->acknowledge(
            $this->manager(),
            $this->house($request),
            $data['issue_key'],
            $request
        );

        return redirect()->route('record7.manager');
    }

    /**
     * Record what was done. A note is compulsory, and this does NOT claim the
     * problem is fixed.
     */
    public function recordAction(Request $request)
    {
        $data = $request->validate([
            'issue_key' => ['required', 'string', 'max:120'],
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $this->actions->recordAction(
            $this->manager(),
            $this->house($request),
            $data['issue_key'],
            $data['note'],
            $request
        );

        return redirect()->route('record7.manager');
    }

    /**
     * Administratively close it.
     *
     * A reason is compulsory. For a safety-critical issue the service also
     * demands evidence or a linked corrective record — and even then the issue
     * stays visible while its condition is still true.
     */
    public function close(Request $request)
    {
        $data = $request->validate([
            'issue_key' => ['required', 'string', 'max:120'],
            'reason' => ['required', 'string', 'min:3', 'max:500'],
            'evidence_reference' => ['nullable', 'string', 'max:190'],
            'linked_administration_id' => ['nullable', 'integer'],
        ]);

        $this->actions->close(
            $this->manager(),
            $this->house($request),
            $data['issue_key'],
            $data['reason'],
            $data['evidence_reference'] ?? null,
            $data['linked_administration_id'] ?? null,
            $request
        );

        return redirect()->route('record7.manager');
    }

    public function decide(Request $request)
    {
        $data = $request->validate([
            'review_id' => ['required', 'integer'],
            'decision' => ['required', 'string', 'in:approved,declined'],
            'note' => ['nullable', 'string', 'max:500'],
            // Optional, and only ever used to CONFIRM what the request already
            // asked for. The service refuses a value that differs.
            'corrected_outcome' => ['nullable', 'string', 'max:40'],
        ]);

        $this->actions->decideReview(
            $this->manager(),
            $this->house($request),
            $data['review_id'],
            $data['decision'],
            $data['note'] ?? null,
            $data['corrected_outcome'] ?? null,
            $request
        );

        return redirect()->route('record7.manager');
    }

    public function closeRound(Request $request)
    {
        $data = $request->validate(['round_id' => ['required', 'integer']]);

        $this->actions->closeRound(
            $this->manager(),
            $this->house($request),
            $data['round_id'],
            $request
        );

        return redirect()->route('record7.manager');
    }

    /* ── Shared ─────────────────────────────────────────────────────────── */

    private function manager(): \App\Models\Record7\User
    {
        $user = $this->user();
        abort_unless($user !== null, 403);

        return $user;
    }

    /**
     * The house the manager is currently in — never one named in the request.
     *
     * Every action reads the house from the session rather than the payload, so
     * a posted id from another house cannot reach any of them. The services
     * then filter by it a second time.
     */
    private function house(Request $request): int
    {
        $serviceId = $this->sessions->serviceId($request);
        abort_unless($serviceId !== null, 403);

        return $serviceId;
    }
}
