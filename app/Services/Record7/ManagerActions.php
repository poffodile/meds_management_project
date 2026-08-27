<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\IssueState;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\User;
use Illuminate\Http\Request;
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
        private readonly IssueRegistry $registry
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

        // Closing a stock event is the one case where the workflow act and the
        // condition genuinely coincide: the event IS the record of the problem.
        if ($parsed['type'] === 'stock_event') {
            StockEvent::where('service_id', $serviceId)
                ->find($parsed['sourceId'])
                ?->forceFill([
                    'resolved_at' => now(),
                    'resolved_by_user_id' => $manager->id,
                    'resolution_note' => $reason,
                ])->save();
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
            default => 'view_manager_dashboard',
        });

        if (! $item->isOpen()) {
            throw new RuntimeException('That has already been decided.');
        }

        if ($decision === 'approved') {
            $this->carryOut($manager, $serviceId, $item, $correctedOutcome, $note);
        }

        $item->status = $decision;
        $item->decided_by_user_id = $manager->id;
        $item->decided_at = now();
        $item->decision_note = $note;
        $item->save();

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
            $this->correct($manager, $serviceId, $item, $correctedOutcome, $note);

            return;
        }

        if ($item->kind === 'round_reopen_request' && $item->subject_id) {
            $round = Round::where('service_id', $serviceId)->find($item->subject_id);

            $round?->forceFill([
                'closed_at' => null,
                'reopened_at' => now(),
                'reopened_by_user_id' => $manager->id,
            ])->save();
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

        Administration::create([
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
        ]);
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

        $round->forceFill([
            'closed_at' => now(),
            'closed_by_user_id' => $manager->id,
        ])->save();

        $this->record($manager, $serviceId, 'round_closed', $round->slot.' round', $request, [
            'round_id' => $round->id,
        ]);

        return $round;
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
