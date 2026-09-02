<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\IssueState;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockMovement;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The things a manager can actually DO from Manager Today.
 *
 * WHAT A MANAGER CANNOT DO, AND THIS CLASS IS WHERE THAT IS ENFORCED
 * They cannot rewrite or delete a clinical record. Not by correcting it, not by
 * approving a correction, not by any path that exists here. Approving a
 * correction writes a NEW administration that points back at the original with
 * corrects_administration_id; the original keeps saying exactly what it said,
 * because what somebody recorded at the time is a fact about that moment and no
 * later authority changes it. The database refuses the alternative and so does
 * the Administration model — this class simply never asks.
 *
 * EVERY ACTION IS SCOPED AND AUDITED
 * Each one takes the house the manager is currently in and refuses anything
 * belonging to another, whatever id is posted — the issue key is resolved
 * against the house through IssueRegistry before a single row is written, so a
 * crafted id finds nothing rather than attaching state to a stranger's record.
 * Each one writes to Section 0's append-only trail with a reason, an actor and
 * a timestamp. A manager decision that leaves no trace is not a decision
 * anybody can be held to.
 *
 * AND CLOSING SOMETHING IS NOT FIXING IT
 * None of these actions can remove a live clinical condition from a manager's
 * screen. Acknowledging, owning, escalating, recording an action and closing
 * are five separate things that all describe the RESPONSE; whether the dose is
 * still unrecorded or the balance still does not match is asked of the clinical
 * record every time and cannot be overridden from here.
 */
class ManagerActions
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly AuditRecorder $audit,
        private readonly IssueRegistry $registry,
        private readonly StockLedger $stock
    ) {
    }

    /* ── Issues: five ways of responding, none of which is "fixed" ─────── */

    /**
     * Somebody has seen it.
     *
     * The weakest state and worth having on its own: an issue nobody has even
     * looked at is different from one somebody looked at and left, and a
     * manager arriving on shift needs to tell those apart.
     */
    public function acknowledge(User $manager, int $serviceId, string $issueKey, Request $request): IssueState
    {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        $state = $this->stateFor($manager, $serviceId, $issueKey);
        $state->acknowledged_at ??= now();
        $state->acknowledged_by_user_id ??= $manager->id;
        $state->save();

        $this->record($manager, $serviceId, 'issue_acknowledged', $issueKey, $request);

        return $state;
    }

    /**
     * Take ownership of a derived issue.
     *
     * Writes to record7_issue_states and nowhere else. Owning a late dose must
     * never put a manager's name on the dose.
     */
    public function takeOwnership(User $manager, int $serviceId, string $issueKey, Request $request): IssueState
    {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        $state = $this->stateFor($manager, $serviceId, $issueKey);
        $state->owner_user_id = $manager->id;
        $state->assigned_at = now();
        $state->acknowledged_at ??= now();
        $state->acknowledged_by_user_id ??= $manager->id;
        $state->save();

        $this->record($manager, $serviceId, 'issue_owned', $issueKey, $request);

        return $state;
    }

    public function escalate(
        User $manager,
        int $serviceId,
        string $issueKey,
        ?int $toUserId,
        ?string $note,
        Request $request
    ): IssueState {
        $this->require($manager, $serviceId, 'incident_review');

        // Escalating TO somebody means somebody who is actually here.
        if ($toUserId !== null && ! $this->policy->usableAccess(User::findOrFail($toUserId), $serviceId)) {
            throw new RuntimeException('That person does not have access to this house.');
        }

        $state = $this->stateFor($manager, $serviceId, $issueKey);
        $state->escalated_at = now();
        $state->escalated_to_user_id = $toUserId;
        $state->note = $note ?: $state->note;
        $state->save();

        $this->record($manager, $serviceId, 'issue_escalated', $issueKey, $request, [
            'escalated_to' => $toUserId,
        ]);

        return $state;
    }

    /**
     * Record that something was done, and what.
     *
     * A note is compulsory. "Actioned" with no words is the same as nothing at
     * all to the person reading it next week, and this is the field that gets
     * quoted back at an inspection.
     *
     * It does NOT claim the problem is fixed, and the issue stays on the list
     * until the clinical record says otherwise.
     */
    public function recordAction(
        User $manager,
        int $serviceId,
        string $issueKey,
        string $note,
        Request $request
    ): IssueState {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        if (trim($note) === '') {
            throw new RuntimeException('Say what was actually done.');
        }

        $state = $this->stateFor($manager, $serviceId, $issueKey);
        $state->action_recorded_at = now();
        $state->action_recorded_by_user_id = $manager->id;
        $state->action_note = $note;
        $state->acknowledged_at ??= now();
        $state->acknowledged_by_user_id ??= $manager->id;
        $state->save();

        $this->record($manager, $serviceId, 'issue_action_recorded', $issueKey, $request, [
            'note' => $note,
        ]);

        return $state;
    }

    /**
     * Administratively close it.
     *
     * THIS IS THE ONE THAT USED TO BE DANGEROUS. It used to hide the issue.
     * It no longer can: closing records a reason, an actor and a timestamp, and
     * the issue stays visible — reading "Action recorded — underlying issue
     * remains unresolved" — for as long as the dose is still unrecorded, the
     * balance still short or the competency still expired.
     *
     * For a safety-critical issue closing also requires evidence: either a
     * reference somebody can follow, or a link to the corrective clinical
     * record that fixed it. A manager may still close something that is not
     * fixed, because sometimes that is the honest state of the world — but they
     * cannot do it silently, anonymously, or invisibly.
     */
    public function close(
        User $manager,
        int $serviceId,
        string $issueKey,
        string $reason,
        ?string $evidenceReference,
        ?int $linkedAdministrationId,
        Request $request
    ): IssueState {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        if (trim($reason) === '') {
            throw new RuntimeException('Closing something needs a reason.');
        }

        $parsed = $this->registry->assertBelongsToHouse($issueKey, $serviceId);

        // A linked corrective record has to be one from THIS house.
        if ($linkedAdministrationId !== null) {
            Administration::where('service_id', $serviceId)->findOrFail($linkedAdministrationId);
        }

        // Asked of the registry, which loads the record rather than guessing
        // from the key. "stock_event" tells you nothing about whether it is a
        // controlled-drug discrepancy or a late delivery.
        if ($this->registry->requiresEvidence($issueKey, $serviceId)
            && blank($evidenceReference)
            && $linkedAdministrationId === null) {
            throw new RuntimeException(
                'This is a safety-critical issue. Closing it needs either a reference to the '
                .'evidence or a link to the corrective record.'
            );
        }

        $state = $this->stateFor($manager, $serviceId, $issueKey);
        $state->closed_at = now();
        $state->closed_by_user_id = $manager->id;
        $state->closure_reason = $reason;
        $state->evidence_reference = $evidenceReference;
        $state->linked_administration_id = $linkedAdministrationId;
        $state->acknowledged_at ??= now();
        $state->acknowledged_by_user_id ??= $manager->id;
        $state->save();

        /* SECTION 2.7. THE ONE PLACE THIS STILL HAPPENS, AND WHY.
         *
         * This used to close ANY stock event by writing resolved_at, which made
         * a quantity discrepancy stop existing because a manager typed a
         * sentence. Fixture row 90 is what that looked like: a Senna count
         * short by two, closed with "Found recorded on the wrong chart. Balance
         * corrected at the next count." No balance was corrected and no
         * corrective record exists. Two tablets are unaccounted for and the
         * system said nothing.
         *
         * Quantity discrepancies are now derived from the ledger and end only
         * when a correction names them. `delivery_overdue` is different in kind:
         * it asserts no quantity at all. The condition it describes is "the
         * pharmacy has not delivered" and the fact that ends it is "it arrived",
         * so here the workflow act and the condition genuinely do coincide.
         * Nothing about closing it can make a missing quantity cease to exist,
         * because it never claimed one.
         */
        if ($parsed['type'] === 'stock_event') {
            $event = StockEvent::where('service_id', $serviceId)->find($parsed['sourceId']);

            if ($event && $event->kind === 'delivery_overdue') {
                $event->forceFill([
                    'resolved_at' => now(),
                    'resolved_by_user_id' => $manager->id,
                    'resolution_note' => $reason,
                ])->save();
            }
        }

        $this->record($manager, $serviceId, 'issue_closed', $issueKey, $request, [
            'reason' => $reason,
            'evidence_reference' => $evidenceReference,
            'linked_administration_id' => $linkedAdministrationId,
            // Recorded at the moment of closing, so the trail shows whether the
            // manager closed something that was actually still happening.
            'condition_active_at_closure' => $this->registry->conditionActive($issueKey, $serviceId),
        ]);

        return $state;
    }

    /* ── The review queue ───────────────────────────────────────────────── */

    /**
     * Approve or decline something waiting on a manager.
     *
     * Approving a correction request is the one place in Record7 where a
     * manager changes what the medicines record SAYS — and even here it does
     * not change what it said. A second administration is written, pointing at
     * the first, recorded against the manager who authorised it. Both rows
     * survive, in order, for ever.
     */
    public function decideReview(
        User $manager,
        int $serviceId,
        int $reviewId,
        string $decision,
        ?string $note,
        ?string $correctedOutcome,
        Request $request
    ): ReviewItem {
        if (! in_array($decision, ['approved', 'declined'], true)) {
            throw new RuntimeException('A review is either approved or declined.');
        }

        $item = ReviewItem::where('service_id', $serviceId)->findOrFail($reviewId);

        $this->require($manager, $serviceId, match ($item->kind) {
            'correction_request' => 'correction_approval',
            'incident', 'handover_escalation' => 'incident_review',

            // Section 2.6. Reopening makes a signed-off period writable again,
            // which is not the same act as looking at a dashboard — and
            // view_manager_dashboard, which this used to fall through to, is
            // held by anybody who can see the manager screen at all.
            'round_reopen_request' => 'reopen_medication_round',
            default => 'view_manager_dashboard',
        });

        if (! $item->isOpen()) {
            throw new RuntimeException('That has already been decided.');
        }

        // ONE TRANSACTION. The decision and what it causes stand or fall
        // together: if carrying it out fails, the item must not be left
        // claiming it was approved.
        DB::connection('record7')->transaction(function () use (
            $manager, $serviceId, $item, $decision, $note, $correctedOutcome
        ) {
        // THE DECISION IS RECORDED FIRST, then carried out.
        //
        // It used to be the other way round, which broke the moment a
        // consequence needed to see the decision: Section 2.6 reopens a round
        // only against an APPROVED request, and checking that while the row
        // still said "open" refused every legitimate reopen. Deciding, then
        // acting on the decision, is also the more honest order — the approval
        // is what authorises the act, so it exists before the act does.
        $item->status = $decision;
        $item->decided_by_user_id = $manager->id;
        $item->decided_at = now();
        $item->decision_note = $note;
        $item->save();

        if ($decision === 'approved') {
            $this->carryOut($manager, $serviceId, $item->fresh(), $correctedOutcome, $note);
        }
        });

        $this->record($manager, $serviceId, 'review_'.$decision, $item->reference, $request, [
            'review_item_id' => $item->id,
            'kind' => $item->kind,
        ]);

        return $item;
    }

    /** What approving actually does, which depends on what was asked. */
    private function carryOut(
        User $manager,
        int $serviceId,
        ReviewItem $item,
        ?string $correctedOutcome,
        ?string $note
    ): void {
        if ($item->kind === 'correction_request') {
            // SECTION 2.7. APPROVAL IS NOT EXECUTION, for a stock correction.
            //
            // An administration correction is carried out here because the
            // manager approving it holds the only authority it needs. A stock
            // reconciliation does not: carrying it out requires the
            // `reconciliation` permission, the balance lock, request-time
            // authority and the exact approved delta, and the approver holds
            // none of those and takes neither lock. So this approves and stops,
            // and somebody with `reconciliation` executes it from the stock
            // screen against the approval this just granted.
            if ($item->subject_type === 'stock_movement') {
                return;
            }

            $this->correct($manager, $serviceId, $item, $correctedOutcome, $note);

            return;
        }

        if ($item->kind === 'round_reopen_request' && $item->subject_id) {
            $round = Round::where('service_id', $serviceId)->find($item->subject_id);

            if ($round === null) {
                return;
            }

            // APPROVING AUTHORISES THE TRANSITION; IT IS NOT THE TRANSITION.
            // The old code here set closed_at = null, which destroyed the very
            // closure it was undoing and kept only the most recent reopen. The
            // lifecycle service appends instead, so every cycle survives.
            app(RoundLifecycle::class)->reopen(
                $manager,
                $round,
                $item,
                $note ?: 'Reopened on approval of '.$item->reference.'.',
                request()
            );
        }
    }

    /**
     * Write the correction. Never touch the original.
     *
     * administered_at is copied from the original on purpose: the correction
     * changes what we now believe happened, not when it happened. Changing the
     * time as well would quietly rewrite the timeline.
     */
    private function correct(
        User $manager,
        int $serviceId,
        ReviewItem $item,
        ?string $correctedOutcome,
        ?string $note
    ): void {
        if ($item->subject_type !== 'administration' || ! $item->subject_id) {
            throw new RuntimeException('That correction request does not name a record to correct.');
        }

        $outcomes = ['given', 'self_administered', 'refused', 'withheld', 'not_available', 'missed'];

        // THE MANAGER APPROVES A REQUEST — THEY DO NOT WRITE ONE.
        // The person who was there says what they believe happened; the manager
        // says yes or no to that. Letting the manager type any outcome at the
        // moment of approving would be a new clinical judgement wearing
        // somebody else's request as a disguise.
        $requested = $item->requested_outcome;

        if (! in_array($requested, $outcomes, true)) {
            throw new RuntimeException(
                'That correction request does not say what the record should say instead, '
                .'so there is nothing to approve.'
            );
        }

        if ($correctedOutcome !== null && $correctedOutcome !== $requested) {
            throw new RuntimeException(
                'A manager can approve or decline what was requested, not substitute a '
                .'different outcome. Decline it and ask for a new request.'
            );
        }

        $correctedOutcome = $requested;

        // Scoped twice: the review item was already loaded for this house, and
        // the administration it names has to be in this house too.
        $original = Administration::where('service_id', $serviceId)->findOrFail($item->subject_id);

        if ((int) $original->client->organisation_id !== (int) $item->organisation_id) {
            throw new RuntimeException('That record belongs to another organisation.');
        }

        // SECTION 2.7. The stock consequence travels with the clinical one, in
        // this transaction, or neither happens. Worked out before the record is
        // written so a refusal cannot leave a corrected outcome standing with
        // no matching movement.
        $stock = $this->stockConsequence($manager, $item, $original, $correctedOutcome);

        $correction = Administration::create([
            'reference' => 'COR-'.Str::upper(Str::random(10)),
            'scheduled_dose_id' => $original->scheduled_dose_id,
            'prescription_id' => $original->prescription_id,
            'client_id' => $original->client_id,
            'service_id' => $original->service_id,
            'recorded_by_user_id' => $manager->id,
            'outcome' => $correctedOutcome,
            'reason_code' => 'manager_correction',
            // Who asked and who approved, both on the record itself, so the
            // correction is readable without joining back to the queue.
            'notes' => trim(sprintf(
                'Correction %s requested by %s, approved by %s. %s',
                $item->reference,
                $item->raisedBy?->displayName() ?? 'a colleague',
                $manager->displayName(),
                (string) $note
            )),
            'administered_at' => $original->administered_at,
            'corrects_administration_id' => $original->id,

            // Only where the correction ESTABLISHED a debit that never existed.
            // A compensating correction points at the movement it corrects
            // instead, and is not carried on the administration.
            'stock_movement_id' => $stock['establishes']?->id,
            'dose_amount' => $stock['dose_amount'],
            'dose_unit' => $stock['dose_unit'],
        ]);

        if ($stock['verification_due']) {
            // The clinical correction stands and no debit is invented. What is
            // now known is that the balance is wrong by an amount nobody can
            // state, and the only thing that answers that is somebody counting.
            $this->stock->auditVerificationDue($correction, $manager, request());
        }
    }

    /**
     * What a corrected outcome does to the cupboard.
     *
     * THE ATTRIBUTABLE QUANTITY, NOT "THE ORIGINAL DEBIT". An administration
     * movement debits `given + wasted`, and correcting the outcome does not
     * un-waste anything: the wasted portion was destroyed as a separate
     * physical act that no clinical correction has touched. So only
     * `quantity_given` comes back, and any return or waste on the original
     * episode stands until separately corrected with its own evidence.
     *
     * @return array{establishes:?\App\Models\Record7\StockMovement,
     *               verification_due:bool, dose_amount:?float, dose_unit:?string}
     */
    private function stockConsequence(
        User $manager, ReviewItem $item, Administration $original, string $correctedOutcome
    ): array {
        $none = [
            'establishes' => null, 'verification_due' => false,
            'dose_amount' => null, 'dose_unit' => null,
        ];

        $consuming = in_array($correctedOutcome, ['given', 'self_administered'], true);
        $originalMovement = $original->stock_movement_id
            ? StockMovement::find($original->stock_movement_id)
            : null;

        // Nothing moved and nothing is being claimed to have moved.
        if ($originalMovement === null && ! $consuming) {
            return $none;
        }

        // A debit that never existed is being established. The historical
        // amount must be stated in the approved evidence — reading today's
        // prescription would give last month's dose this month's figure.
        if ($originalMovement === null) {
            $medicineId = $original->prescription?->medicine_id;

            // Nothing is being counted for this person and this medicine, so
            // there is no balance to move and nothing to go and verify.
            if ($medicineId === null
                || $this->stock->trackedFor($original->client_id, $medicineId) === null) {
                return $none;
            }

            if ($item->requested_dose_amount === null || $item->requested_dose_unit === null) {
                return ['establishes' => null, 'verification_due' => true,
                    'dose_amount' => null, 'dose_unit' => null];
            }

            $movement = $this->stock->establishDebit(
                $manager, $original, (float) $item->requested_dose_amount,
                (string) $item->requested_dose_unit, $item->id
            );

            return [
                'establishes' => $movement,
                'verification_due' => $movement === null,
                'dose_amount' => $movement ? (float) $item->requested_dose_amount : null,
                'dose_unit' => $movement ? (string) $item->requested_dose_unit : null,
            ];
        }

        $attributable = (float) $originalMovement->quantity_given;

        // given -> given, at a different actual amount. Only the difference
        // moves; the unit must match exactly and is never converted.
        if ($consuming) {
            if ($item->requested_dose_amount === null || $item->requested_dose_unit === null) {
                throw new RuntimeException(
                    'A correction to a dose that was given has to say how much was actually given.'
                );
            }

            if ((string) $item->requested_dose_unit !== (string) $originalMovement->unit) {
                throw new RuntimeException(
                    'That correction is in a different unit from the movement it corrects. '
                    .'Record7 does not convert between units.'
                );
            }

            $delta = $attributable - (float) $item->requested_dose_amount;

            $this->stock->compensate($manager, $originalMovement, $delta, $item->id);

            return [
                'establishes' => null, 'verification_due' => false,
                'dose_amount' => (float) $item->requested_dose_amount,
                'dose_unit' => (string) $item->requested_dose_unit,
            ];
        }

        // given -> a non-consuming outcome. The dose comes back; the waste does not.
        $this->stock->compensate($manager, $originalMovement, $attributable, $item->id);

        return $none;
    }

    /* ── Rounds ─────────────────────────────────────────────────────────── */

    /**
     * Sign a round off.
     *
     * Different from finishing it: completed_at is the last dose recorded,
     * closed_at is a manager saying the round is accounted for. A round with
     * unexplained gaps can still be closed — but the gaps stay on the
     * attention list, because closing the round does not close them.
     */
    public function closeRound(User $manager, int $serviceId, int $roundId, Request $request): Round
    {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        $round = Round::where('service_id', $serviceId)->findOrFail($roundId);

        // Section 2.6 owns the transition. Writing closed_at here as well would
        // give a round two ways to become closed, and only one of them would
        // leave history behind.
        app(RoundLifecycle::class)->close($manager, $round, $request);

        return $round->fresh();
    }

    /**
     * Ask for a closed round to be opened again. Raises a request; nothing else.
     *
     * SEPARATION OF DUTIES IS THE POINT. Seeing a closed round and asking about
     * it is `view_manager_dashboard`; opening it is `reopen_medication_round`,
     * checked at the moment of the decision by decideReview() and again inside
     * RoundLifecycle::reopen(). This method deliberately holds neither — it
     * cannot reopen a round even if its caller could.
     */
    public function requestRoundReopen(
        User $manager,
        int $serviceId,
        int $roundId,
        string $reason,
        Request $request
    ): ReviewItem {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        if (trim($reason) === '') {
            throw new RuntimeException('Say why this round should be opened again.');
        }

        // Scoped to the house from the session. An id naming another house's
        // round is a 404, not a request against it.
        $round = Round::where('service_id', $serviceId)->findOrFail($roundId);

        if (! $round->isClosed()) {
            throw new RuntimeException('That round is not closed.');
        }

        // One open request per round. Two people noticing the same gap should
        // not produce two decisions, and the second approval would meet "that
        // round is not closed" after the first had already reopened it.
        $existing = ReviewItem::where('service_id', $serviceId)
            ->where('kind', 'round_reopen_request')
            ->where('subject_type', 'round')
            ->where('subject_id', $round->id)
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            throw new RuntimeException('Somebody has already asked for that round to be opened again.');
        }

        $item = ReviewItem::create([
            'reference' => 'R7RR-'.Str::upper(Str::random(10)),
            'organisation_id' => $round->organisation_id,
            'service_id' => $serviceId,
            'kind' => 'round_reopen_request',
            'title' => 'Reopen the '.strtolower($round->slot).' round of '
                .$round->round_date->format('j F'),
            'detail' => $reason,
            'subject_type' => 'round',
            'subject_id' => $round->id,
            'raised_by_user_id' => $manager->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $this->audit->record(
            eventType: 'round_reopen_requested',
            result: AuditRecorder::SUCCESS,
            user: $manager,
            serviceId: $serviceId,
            reason: $reason,
            riskLevel: 'medium',
            metadata: ['round_id' => $round->id, 'review_item_id' => $item->id],
            request: $request
        );

        return $item;
    }

    /* ── Shared ─────────────────────────────────────────────────────────── */

    /**
     * Refuse anything this person may not do in THIS house.
     *
     * Asked of AccessPolicy rather than reimplemented, so a manager action can
     * never be permitted by a rule the rest of the product would refuse.
     */
    private function require(User $manager, int $serviceId, string $permission): void
    {
        $decision = $this->policy->decide($manager, $permission, $serviceId);

        abort_if($decision->denied(), 403, $decision->message ?? 'You do not have permission to do that.');
    }

    /**
     * Find or start the state row — after proving the issue is this house's.
     *
     * The key is never trusted on its own. IssueRegistry loads the record it
     * names, filtered by service id, and refuses anything that belongs
     * elsewhere. Only then is identity written, and it is written as explicit
     * organisation, house, type and source columns rather than a text key.
     */
    private function stateFor(User $manager, int $serviceId, string $issueKey): IssueState
    {
        $parsed = $this->registry->assertBelongsToHouse($issueKey, $serviceId);
        $service = Service::findOrFail($serviceId);

        return IssueState::firstOrNew([
            'organisation_id' => $service->organisation_id,
            'service_id' => $serviceId,
            'issue_type' => $parsed['type'],
            'source_id' => $parsed['sourceId'],
        ])->fill([
            'organisation_id' => $service->organisation_id,
            'issue_key' => $issueKey,
            'issue_type' => $parsed['type'],
            'source_id' => $parsed['sourceId'],
        ]);
    }

    private function record(
        User $manager,
        int $serviceId,
        string $event,
        string $subject,
        Request $request,
        array $metadata = []
    ): void {
        $this->audit->record(
            eventType: $event,
            result: AuditRecorder::SUCCESS,
            user: $manager,
            serviceId: $serviceId,
            reason: $subject,
            riskLevel: str_contains($event, 'review_') ? 'medium' : 'low',
            metadata: $metadata,
            request: $request
        );
    }
}
