<?php

namespace App\Services\Record7;

use App\Models\Record7\CdRegister;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\RoundLifecycleEvent;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Section 2.6 — closing a round, and opening it again.
 *
 * TWO IDEAS THAT MUST NOT BE CONFUSED.
 *
 *   ACCOUNTABILITY  every planned dose has an answer
 *   RESOLUTION      the clinical consequences of those answers are dealt with
 *
 * A refusal is a complete answer to "what happened to this dose". It is not an
 * answer to "is this person all right and does somebody need to go back". So a
 * refusal makes its dose accounted for while the refusal itself stays wide open,
 * and closing a round settles the first without touching the second.
 *
 * CLOSING RESOLVES NOTHING. It writes no issue state, clears no condition and
 * touches no clinical record. What it does is record what was outstanding at
 * the moment somebody signed, so the sign-off can be read afterwards for what
 * it actually was.
 *
 * REOPENING ERASES NOTHING. The old behaviour set `closed_at = null`, which
 * destroyed the closure it was undoing. Every transition is now appended.
 */
class RoundLifecycle
{
    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly ManagerBoard $board,
        private readonly AuditRecorder $audit,
    ) {
    }

    /* ── Accountability ─────────────────────────────────────────────────── */

    /**
     * The planned doses this round is answerable for.
     *
     * FULLY SELF-MANAGED MEDICINES ARE EXCLUDED. Where the agreed arrangement is
     * that the person handles it themselves, there is no staff record to make,
     * so counting it would leave every round permanently incomplete and teach
     * people that "3 remaining" means nothing. RoundQueue already excludes them
     * and this uses the same rule so the screen and the count cannot disagree.
     *
     * PRN NEVER APPEARS. An as-required medicine answers a need, not a plan, so
     * it is neither owed by the round nor able to complete it.
     */
    public function plannedDoses(Round $round)
    {
        return ScheduledDose::with(['prescription', 'administration'])
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date)
            ->where('slot', $round->slot)
            ->get()
            ->reject(fn (ScheduledDose $dose) => (bool) $dose->prescription?->isFullySelfManaged());
    }

    /**
     * How the round stands: planned, accounted for, still unrecorded.
     *
     * ACCOUNTED FOR MEANS AN ANSWER EXISTS, whatever the answer was. Given,
     * refused, withheld, missed, not available, person unavailable — each is a
     * real record of what happened, and the dose is no longer waiting.
     *
     * @return array{planned:int, accounted:int, unrecorded:int}
     */
    public function accountability(Round $round): array
    {
        $planned = $this->plannedDoses($round);

        // Existence, not outcome. A correction or a re-offer adds rows to the
        // same dose, so counting rows would double-count; this asks whether the
        // dose has any answer at all, which is what accountability means.
        $accounted = $planned->filter(fn (ScheduledDose $dose) => $dose->administration !== null);

        return [
            'planned' => $planned->count(),
            'accounted' => $accounted->count(),
            'unrecorded' => $planned->count() - $accounted->count(),
        ];
    }

    public function isComplete(Round $round): bool
    {
        return $this->accountability($round)['unrecorded'] === 0;
    }

    /* ── What is still unresolved, by category ──────────────────────────── */

    /**
     * The category names of everything still open in this house.
     *
     * NAMES ONLY, and evidence rather than authority.
     *
     * This is a close-time snapshot of what was visible for the HOUSE, not a
     * claim that every category belongs to this round or this slot. It is
     * service-wide on purpose: it records what the manager was actually looking
     * at, and their screen shows the house.
     *
     * Nothing resolves a condition by dropping out of this list, and nothing
     * reads this list to decide whether a condition is still open. The refusal,
     * the welfare check, the register and the stock event each remain
     * authoritative for their own lifecycle. This answers only "what did this
     * person see when they signed".
     *
     * @return list<string>
     */
    public function unresolvedCategories(Round $round): array
    {
        // Taken from the manager board rather than assembled again here. It
        // already knows every live concern in a house, and a second list built
        // from the same records would drift from the one a manager is actually
        // looking at.
        $categories = collect($this->board->attention($round->service_id))
            ->pluck('kind')
            ->filter()
            ->all();

        if ($this->hasControlledDrugDiscrepancy($round->service_id)) {
            $categories[] = 'controlled_drug_register_discrepancy';
        }

        sort($categories);

        return array_values(array_unique($categories));
    }

    /**
     * A Section 2.5 register disagreement nobody has corrected.
     *
     * Derived from the immutable register exactly as 2.5 derives it. There is no
     * status to tick and no review item that decides it: an entry marked as a
     * disagreement with no correction naming it IS the condition, and only a
     * correction ends it.
     */
    public function hasControlledDrugDiscrepancy(int $serviceId): bool
    {
        return CdRegister::where('service_id', $serviceId)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('record7_cd_register as fix')
                    ->whereColumn('fix.corrects_register_id', 'record7_cd_register.id');
            })
            ->exists();
    }

    /* ── Closing ────────────────────────────────────────────────────────── */

    /**
     * Sign a round off.
     *
     * A round with unrecorded doses may still be closed. A manager has to be
     * able to sign off a shift, and refusing would produce worse workarounds
     * than an honest count — so the gap is recorded on the event instead, and
     * everything still open stays open.
     */
    public function close(User $manager, Round $round, Request $request): RoundLifecycleEvent
    {
        $this->require($manager, $round->service_id, 'view_manager_dashboard');

        return DB::connection('record7')->transaction(function () use ($manager, $round, $request) {
            $locked = $this->lock($round);

            if ($locked->isClosed()) {
                throw new RuntimeException('That round is already closed.');
            }

            $counts = $this->accountability($locked);
            $categories = $this->unresolvedCategories($locked);

            $event = $this->append($locked, 'closed', $manager, [
                'planned_doses' => $counts['planned'],
                'accounted_doses' => $counts['accounted'],
                'unrecorded_doses' => $counts['unrecorded'],
                'unresolved_categories' => $categories,
            ]);

            // The projection, written last and never read for state.
            $locked->forceFill([
                'closed_at' => $event->occurred_at,
                'closed_by_user_id' => $manager->id,
                'last_lifecycle_event_id' => $event->id,
            ])->save();

            $this->audit->record(
                eventType: 'round_closed',
                result: AuditRecorder::SUCCESS,
                user: $manager,
                serviceId: $round->service_id,
                reason: null,
                riskLevel: $counts['unrecorded'] > 0 ? 'medium' : 'low',
                metadata: [
                    'round_id' => $round->id,
                    'lifecycle_event_id' => $event->id,
                ] + $counts + ['unresolved_categories' => $categories],
                request: $request
            );

            return $event;
        });
    }

    /* ── Reopening ──────────────────────────────────────────────────────── */

    /**
     * Make a closed round writable again.
     *
     * The approved request AUTHORISES this; it is not itself the transition.
     * That distinction matters because a review item is mutable by design — its
     * status, decision note and decided-at all move — and a mutable row must
     * never be what says whether a clinical period is open.
     *
     * One approval, one reopen: the unique key on `review_item_id` refuses a
     * second, so a replayed approval loses at the database rather than
     * producing a transition nobody asked for.
     */
    public function reopen(
        User $manager,
        Round $round,
        ReviewItem $request_item,
        string $reason,
        Request $request
    ): RoundLifecycleEvent {
        // Checked here AND again inside the lock, so a permission removed
        // between the two takes effect.
        $this->require($manager, $round->service_id, 'reopen_medication_round');

        if (trim($reason) === '') {
            throw new RuntimeException('Say why this round is being reopened.');
        }

        return DB::connection('record7')->transaction(function () use (
            $manager, $round, $request_item, $reason, $request
        ) {
            $locked = $this->lock($round);

            $approval = ReviewItem::where('id', $request_item->id)->lockForUpdate()->first();

            $this->require($manager, $locked->service_id, 'reopen_medication_round');

            if ($approval === null
                || $approval->kind !== 'round_reopen_request'
                || $approval->status !== 'approved'
                || $approval->subject_type !== 'round'
                || (int) $approval->subject_id !== (int) $locked->id
                || (int) $approval->service_id !== (int) $locked->service_id) {
                throw new RuntimeException(
                    'That approval does not authorise reopening this round.'
                );
            }

            if (! $locked->isClosed()) {
                throw new RuntimeException('That round is not closed.');
            }

            $event = $this->append($locked, 'reopened', $manager, [
                'review_item_id' => $approval->id,
                'reason' => $reason,
            ]);

            // closed_at is deliberately NOT cleared. It now means "the most
            // recent closure", and erasing it is the behaviour this replaced.
            $locked->forceFill([
                'reopened_at' => $event->occurred_at,
                'reopened_by_user_id' => $manager->id,
                'last_lifecycle_event_id' => $event->id,
            ])->save();

            $this->audit->record(
                eventType: 'round_reopened',
                result: AuditRecorder::SUCCESS,
                user: $manager,
                serviceId: $round->service_id,
                reason: $reason,
                riskLevel: 'high',
                metadata: [
                    'round_id' => $round->id,
                    'lifecycle_event_id' => $event->id,
                    'review_item_id' => $approval->id,
                ],
                request: $request
            );

            return $event;
        });
    }

    /* ── History, for a screen ──────────────────────────────────────────── */

    public function history(Round $round): array
    {
        return $round->lifecycleEvents()->with('actor')->get()
            ->map(fn (RoundLifecycleEvent $e) => [
                'id' => $e->id,
                'event' => $e->event,
                'word' => $e->word(),
                'at' => $e->occurred_at?->format('H:i \o\n j F'),
                'by' => $e->actor_name_at_time ?? $e->actor?->displayName(),
                'reason' => $e->reason,
                'planned' => $e->planned_doses,
                'accounted' => $e->accounted_doses,
                'unrecorded' => $e->unrecorded_doses,
                'unresolved' => $e->unresolved_categories ?? [],
                'imported' => (bool) $e->imported,
                'importNote' => $e->import_note,
            ])->values()->all();
    }

    /* ── Shared ─────────────────────────────────────────────────────────── */

    /**
     * The round row is the lock for every transition.
     *
     * Two managers closing at once, a double-submitted close, and two attempts
     * to spend one approval all queue here, so each of them reads settled state
     * rather than the state that was true when the button was pressed.
     */
    private function lock(Round $round): Round
    {
        return Round::where('id', $round->id)->lockForUpdate()->firstOrFail();
    }

    /** Append one transition. Never update; never delete. */
    private function append(Round $round, string $event, User $actor, array $extra): RoundLifecycleEvent
    {
        $next = ((int) $round->lifecycleEvents()->reorder()->max('sequence_no')) + 1;

        // Taken from the house, not from the round. `record7_rounds`
        // .organisation_id is nullable and older rows do not all carry it, and
        // the ownership on a permanent record has to be right rather than
        // however the round row happened to be written.
        $organisationId = $round->organisation_id
            ?? \App\Models\Record7\Service::find($round->service_id)?->organisation_id;

        if ($organisationId === null) {
            throw new RuntimeException('That round is not attached to an organisation.');
        }

        return RoundLifecycleEvent::create([
            'reference' => 'R7RL-'.strtoupper(Str::random(12)),
            'organisation_id' => $organisationId,
            'service_id' => $round->service_id,
            'round_id' => $round->id,
            'event' => $event,
            'sequence_no' => $next,
            'occurred_at' => now(),
            'actor_user_id' => $actor->id,
            'actor_name_at_time' => $actor->full_name,
            'actor_role_at_time' => $actor->primaryRole()?->name,
            'imported' => false,
        ] + $extra);
    }

    private function require(User $manager, int $serviceId, string $permission): void
    {
        $decision = $this->policy->decide($manager, $permission, $serviceId);

        abort_if($decision->denied(), 403, $decision->message ?? 'You do not have permission to do that.');
    }
}
