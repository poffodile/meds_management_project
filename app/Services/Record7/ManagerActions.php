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

        if ($linkedAdministrationId !== null) {
            Administration::where('service_id', $serviceId)->findOrFail($linkedAdministrationId);
        }

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
            'condition_active_at_closure' => $this->registry->conditionActive($issueKey, $serviceId),
        ]);

        return $state;
    }

    /* ── The review queue ───────────────────────────────────────────────── */

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
            'round_reopen_request' => 'reopen_medication_round',
            default => 'view_manager_dashboard',
        });

        if (! $item->isOpen()) {
            throw new RuntimeException('That has already been decided.');
        }

        DB::connection('record7')->transaction(function () use (
            $manager, $serviceId, $item, $decision, $note, $correctedOutcome
        ) {
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

    private function carryOut(
        User $manager,
        int $serviceId,
        ReviewItem $item,
        ?string $correctedOutcome,
        ?string $note
    ): void {
        if ($item->kind === 'correction_request') {
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

            app(RoundLifecycle::class)->reopen(
                $manager,
                $round,
                $item,
                $note ?: 'Reopened on approval of '.$item->reference.'.',
                request()
            );
        }
    }

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

        $outcomes = [
            'given',
            'self_administered',
            'refused',
            'withheld',
            'not_available',
            'missed',
            'person_unavailable',
        ];

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

        $original = Administration::where('service_id', $serviceId)->findOrFail($item->subject_id);

        if ((int) $original->client->organisation_id !== (int) $item->organisation_id) {
            throw new RuntimeException('That record belongs to another organisation.');
        }

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
            'notes' => trim(sprintf(
                'Correction %s requested by %s, approved by %s. %s',
                $item->reference,
                $item->raisedBy?->displayName() ?? 'a colleague',
                $manager->displayName(),
                (string) $note
            )),
            'administered_at' => $original->administered_at,
            'corrects_administration_id' => $original->id,
            'stock_movement_id' => $stock['establishes']?->id,
            'dose_amount' => $stock['dose_amount'],
            'dose_unit' => $stock['dose_unit'],
        ]);

        if ($stock['verification_due']) {
            $this->stock->auditVerificationDue($correction, $manager, request());
        }
    }

    /**
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

        if ($originalMovement === null && ! $consuming) {
            return $none;
        }

        if ($originalMovement === null) {
            $medicineId = $original->prescription?->medicine_id;

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

        $this->stock->compensate($manager, $originalMovement, $attributable, $item->id);

        return $none;
    }

    /* ── Rounds ─────────────────────────────────────────────────────────── */

    public function closeRound(User $manager, int $serviceId, int $roundId, Request $request): Round
    {
        $this->require($manager, $serviceId, 'view_manager_dashboard');

        $round = Round::where('service_id', $serviceId)->findOrFail($roundId);

        app(RoundLifecycle::class)->close($manager, $round, $request);

        return $round->fresh();
    }

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

        $round = Round::where('service_id', $serviceId)->findOrFail($roundId);

        if (! $round->isClosed()) {
            throw new RuntimeException('That round is not closed.');
        }

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

    private function require(User $manager, int $serviceId, string $permission): void
    {
        $decision = $this->policy->decide($manager, $permission, $serviceId);

        abort_if($decision->denied(), 403, $decision->message ?? 'You do not have permission to do that.');
    }

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
