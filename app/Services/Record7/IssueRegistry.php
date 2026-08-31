<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockMovement;
use App\Models\Record7\User;
use App\Models\Record7\UserServiceAccess;
use App\Models\Record7\WelfareCheck;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * What an issue key MEANS, and whether it belongs to this house.
 *
 * WHY THIS EXISTS
 * An issue was identified by free text — "omitted_dose:412" — and nothing tied
 * 412 to the house the state row was being stored under. A manager in one house
 * could post a key naming a record in another and have state written against
 * it. The key alone was never proof of anything.
 *
 * Everything now goes through here. A key is parsed into a type and a source
 * id, the source is LOADED, and it is refused unless it belongs to the house
 * the session is in. Two organisations can both have a dose 412 and they never
 * meet, because the lookup is scoped before the id is ever trusted.
 *
 * AND IT ANSWERS THE ONLY QUESTION THAT MATTERS FOR SAFETY
 * conditionActive() asks the clinical record whether the actual problem still
 * exists — not whether anybody has ticked anything. That answer is what decides
 * whether an issue stays on a manager's screen, and it can never be overridden
 * by workflow state.
 */
class IssueRegistry
{
    /** Types this system knows how to verify and re-check. */
    public const TYPES = [
        'omitted_dose',
        'time_critical_omission',
        'refusal',
        'prn_follow_up',
        'incomplete_record',
        'stock_event',
        'stock_out',
        'stock_low',
        'stock_discrepancy',
        'stock_verification_due',
        'controlled_drug_discrepancy',
        'staff_readiness',
        'review',
        'handover_unread',
        'welfare_check',
        'prn_concerning_response',
    ];

    /** Split a key without trusting either half yet. */
    public function parse(string $issueKey): array
    {
        [$type, $source] = array_pad(explode(':', $issueKey, 2), 2, null);

        if (! in_array($type, self::TYPES, true)) {
            throw new RuntimeException('That is not an issue this system recognises.');
        }

        return [
            'type' => $type,
            'sourceId' => is_numeric($source) ? (int) $source : null,
            'sourceKey' => $source,
        ];
    }

    /**
     * Refuse anything that does not belong to this house.
     *
     * Every lookup is filtered by service_id BEFORE the id is used, so a
     * crafted id from another house or another organisation finds nothing and
     * is rejected rather than silently attaching state to a stranger's record.
     */
    public function assertBelongsToHouse(string $issueKey, int $serviceId): array
    {
        $parsed = $this->parse($issueKey);

        $found = match ($parsed['type']) {
            'omitted_dose', 'time_critical_omission' => ScheduledDose::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'refusal', 'incomplete_record' => Administration::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'prn_follow_up' => PrnFollowUp::where('service_id', $serviceId)->find($parsed['sourceId']),
            'stock_event' => StockEvent::where('service_id', $serviceId)->find($parsed['sourceId']),
            'stock_out', 'stock_low' => StockBalance::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'stock_discrepancy' => StockMovement::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'stock_verification_due' => Administration::where('service_id', $serviceId)
                ->whereNotNull('corrects_administration_id')
                ->find($parsed['sourceId']),
            'controlled_drug_discrepancy' => \App\Models\Record7\CdRegister::where('service_id', $serviceId)
                ->where('is_discrepancy', true)
                ->find($parsed['sourceId']),
            'review' => ReviewItem::where('service_id', $serviceId)->find($parsed['sourceId']),
            'staff_readiness' => UserServiceAccess::where('service_id', $serviceId)
                ->where('user_id', $parsed['sourceId'])->first(),
            // A handover key names a handover in this house.
            'handover_unread' => \App\Models\Record7\Handover::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'welfare_check' => Administration::where('service_id', $serviceId)
                ->where('outcome', 'person_unavailable')
                ->where('reason_code', 'not_found_in_service')
                ->find($parsed['sourceId']),

            // A response somebody was worried about, on a PRN in this house.
            'prn_concerning_response' => PrnFollowUp::where('service_id', $serviceId)
                ->where('concerning_response', true)
                ->find($parsed['sourceId']),
            default => null,
        };

        abort_if(
            $found === null,
            404,
            'That issue does not belong to the house you are working in.'
        );

        return $parsed;
    }

    /**
     * Is the actual problem still there?
     *
     * Asked of the clinical, stock and access records — never of a workflow
     * flag. This is the single check that decides whether a manager keeps
     * seeing something, and no button anywhere can change its answer.
     */
    public function conditionActive(string $issueKey, int $serviceId, ?Carbon $now = null): bool
    {
        $now ??= now();
        $parsed = $this->parse($issueKey);
        $id = $parsed['sourceId'];

        return match ($parsed['type']) {
            // Still nothing recorded against the dose.
            'omitted_dose', 'time_critical_omission' => (bool) ScheduledDose::with('administration')
                ->where('service_id', $serviceId)
                ->find($id)?->isLate($now),

            // No later "given" for the same prescription.
            'refusal' => $this->refusalStillOpen($serviceId, $id),

            // Still waiting for an answer.
            'prn_follow_up' => PrnFollowUp::where('service_id', $serviceId)
                ->where('id', $id)->where('outcome', 'pending')->exists(),

            // A not-taken outcome with nothing said about why.
            'incomplete_record' => $this->recordStillIncomplete($serviceId, $id),

            // SECTION 2.7. `stock_event` now covers ONE kind: a delivery that
            // has not arrived. It asserts no quantity, so "somebody said it
            // arrived" genuinely is the fact that ends it. Counts and quantity
            // discrepancies moved to the ledger, where a note cannot end them.
            'stock_event' => StockEvent::where('service_id', $serviceId)
                ->where('id', $id)->where('kind', 'delivery_overdue')
                ->whereNull('resolved_at')->exists(),

            // Derived from the ledger balance, which is itself derived from the
            // ledger. Nothing here can be set by a person.
            'stock_out' => (bool) StockBalance::with('threshold')
                ->where('service_id', $serviceId)->find($id)?->isOut(),

            // Unavailable rather than false where no reorder level is recorded.
            // `isLow()` returns false in that case and the board does not offer
            // the issue at all, so a blank never renders as healthy.
            'stock_low' => (bool) StockBalance::with('threshold')
                ->where('service_id', $serviceId)->find($id)?->isLow(),

            // ONE ENTRY, ONE ANSWER. This movement proved an inconsistency and
            // stays live until a correction names THIS movement. Correcting an
            // earlier discrepancy on the same balance does not touch it.
            'stock_discrepancy' => $this->stockDiscrepancyOpen($serviceId, $id),

            // A correction established that a dose was given without saying how
            // much. Only somebody counting answers it — see the method.
            'stock_verification_due' => $this->stockVerificationDue($serviceId, $id),

            // SECTION 2.5 REMAINS THE SOLE AUTHORITY. Derived exactly as it
            // derives it: a register entry marked as a disagreement, with no
            // correction naming it, IS the condition. Ordinary reconciliation
            // cannot reach it and nothing here can close it.
            'controlled_drug_discrepancy' => $this->controlledDiscrepancyOpen($serviceId, $id),

            // The person still cannot administer here.
            'staff_readiness' => $this->staffStillBlocked($serviceId, $id),

            // The review item is still waiting for a decision.
            'review' => ReviewItem::where('service_id', $serviceId)
                ->where('id', $id)->where('status', 'open')->exists(),

            // Somebody on shift still has not confirmed it.
            'handover_unread' => true,
            // NOT "the report exists" — that is true forever, because an
            // administration is permanent. A welfare item nobody can ever
            // resolve sits on a manager's board for good and teaches everybody
            // to scroll past it, which is the opposite of what it is for.
            //
            // The real condition is that nobody has accounted for the person
            // since. It clears on a FACT: somebody records anything else for
            // them, or their status becomes a known whereabouts. Not on a tick.
            'welfare_check' => $this->personStillUnaccountedFor($serviceId, $id),

            // Live while the concern stands on the record. Recorded by a person
            // and, like every other condition here, not something a manager can
            // clear by ticking — it clears when the clinical record says so.
            'prn_concerning_response' => PrnFollowUp::where('service_id', $serviceId)
                ->where('id', $id)
                ->where('concerning_response', true)
                ->exists(),

            default => true,
        };
    }

    /**
     * Is THIS disagreement still unanswered?
     *
     * Each movement that proved an inconsistency carries its own identity and
     * its own requirement. A later shortfall is not swallowed because an earlier
     * count is still open, and correcting the earlier one can never hide the
     * later one — they are separate rows. Only a correction naming THIS
     * movement ends it, and the unique index on `corrects_movement_id` means
     * that correction can only ever exist once.
     */
    private function stockDiscrepancyOpen(int $serviceId, ?int $id): bool
    {
        if ($id === null) {
            return false;
        }

        return StockMovement::where('service_id', $serviceId)
            ->where('id', $id)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('record7_stock_movements as fix')
                    ->whereColumn('fix.corrects_movement_id', 'record7_stock_movements.id');
            })
            ->exists();
    }

    /** A Section 2.5 register disagreement nobody has corrected. */
    private function controlledDiscrepancyOpen(int $serviceId, ?int $id): bool
    {
        if ($id === null) {
            return false;
        }

        return \App\Models\Record7\CdRegister::where('service_id', $serviceId)
            ->where('id', $id)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('record7_cd_register as fix')
                    ->whereColumn('fix.corrects_register_id', 'record7_cd_register.id');
            })
            ->exists();
    }

    /**
     * Does somebody still have to go and count?
     *
     * Raised where a correction established that a dose WAS given but nobody
     * could say how much, so no debit was invented. The balance is now known to
     * be wrong by an unknown amount, and the only thing that answers that is a
     * physical count.
     *
     * IT CLEARS ON A FACT, and a narrow one. The count must be later than the
     * correction, and belong to the same organisation, service, person and
     * preparation. An older count proves nothing about a position that has
     * since changed; somebody else's count, or a count of another preparation,
     * proves nothing about this one. A note, a review decision and an
     * IssueState closure each prove nothing at all.
     */
    private function stockVerificationDue(int $serviceId, ?int $id): bool
    {
        $correction = Administration::with('prescription')
            ->where('service_id', $serviceId)
            ->whereNotNull('corrects_administration_id')
            ->whereIn('outcome', ['given', 'self_administered'])
            ->whereNull('stock_movement_id')
            ->find($id);

        if ($correction === null) {
            return false;
        }

        $medicineId = $correction->prescription?->medicine_id;

        if ($medicineId === null) {
            return false;
        }

        // Nothing is being counted for this person and this medicine, so there
        // is no balance to be wrong and nothing to verify.
        $balance = StockBalance::where('client_id', $correction->client_id)
            ->where('medicine_id', $medicineId)
            ->first();

        if ($balance === null) {
            return false;
        }

        // FROM THE MOMENT OF THE CORRECTION ONWARDS, not strictly after it.
        //
        // A correction that establishes an unknown quantity writes no movement,
        // so the balance does not change at that instant — which means a count
        // taken in the same second establishes the position just as well as one
        // taken a minute later. Requiring strictly later would also make this
        // unclearable whenever the two share a timestamp, which is not a rare
        // edge: it is what happens under a frozen clock, and it is what happens
        // when somebody corrects and counts in one go.
        $counted = StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->where('action', 'stock_check')
            ->where('occurred_at', '>=', $correction->created_at)
            ->exists();

        return ! $counted;
    }

    /**
     * Does closing this need evidence?
     *
     * Type alone was not enough. A stock event's key is "stock_event:12"
     * whatever it is about, so asking the type produced "stock_event" — which
     * was not on the safety-critical list, and a controlled-drug balance
     * discrepancy could therefore be closed with no evidence at all. The event
     * itself has to be loaded and asked what it is.
     */
    public function requiresEvidence(string $issueKey, int $serviceId): bool
    {
        $parsed = $this->parse($issueKey);

        if ($parsed['type'] === 'stock_event') {
            $event = StockEvent::with('medicine')
                ->where('service_id', $serviceId)
                ->find($parsed['sourceId']);

            // A discrepancy always. A controlled drug always. A late delivery
            // is a nuisance rather than an investigation, so it does not.
            return $event !== null
                && ($event->kind === 'discrepancy' || (bool) $event->medicine?->is_controlled);
        }

        return \App\Models\Record7\IssueState::needsEvidence($parsed['type']);
    }

    /**
     * Has anybody accounted for this person since they could not be found?
     *
     * Two things count, and both are facts rather than workflow:
     *
     *   somebody recorded ANYTHING for them afterwards — you cannot record a
     *   medicine for somebody you have not found; or
     *
     *   their status now says where they are: on leave, in hospital, moved out.
     *
     * Until one of those is true the person is still missing as far as this
     * service knows, and no amount of acknowledging changes that.
     */
    private function personStillUnaccountedFor(int $serviceId, ?int $id): bool
    {
        $report = Administration::where('service_id', $serviceId)
            ->where('outcome', 'person_unavailable')
            ->where('reason_code', 'not_found_in_service')
            ->find($id);

        if (! $report) {
            return false;
        }

        // 1. SOMEBODY WENT AND LOOKED, AND SAID WHAT THEY FOUND.
        //    A structured record naming this concern, this person, this house
        //    and this organisation. Not an acknowledgement, not a note, not a
        //    closed review item, and not an unrelated medicine recorded later
        //    — none of those establish where anybody is.
        $evidence = WelfareCheck::where('administration_id', $report->id)
            ->where('client_id', $report->client_id)
            ->where('service_id', $report->service_id)
            ->exists();

        if ($evidence) {
            return false;
        }

        // 2. OR THEIR WHEREABOUTS ARE NOW ON THE RECORD.
        //    "active" means we believe they are here, which is the very thing
        //    in doubt. Any other status names where they actually are.
        $client = Client::find($report->client_id);

        return ! ($client && $client->status !== 'active');
    }

    private function refusalStillOpen(int $serviceId, ?int $id): bool
    {
        $refusal = Administration::where('service_id', $serviceId)->find($id);

        if (! $refusal || $refusal->outcome !== 'refused') {
            return false;
        }

        return ! Administration::where('scheduled_dose_id', $refusal->scheduled_dose_id)
            ->where('service_id', $serviceId)
            ->where('client_id', $refusal->client_id)
            ->where('prescription_id', $refusal->prescription_id)
            ->where('reoffer_of_administration_id', $refusal->id)
            ->whereIn('outcome', ['given', 'self_administered'])
            ->exists();

        // NOTE the absence of a timestamp comparison. The chain link already
        // establishes the order — a re-offer can only name a refusal that
        // exists, so it is later by construction. Comparing administered_at as
        // well made the rule depend on two writes landing in different seconds,
        // which is not something a clinical rule should turn on.
    }

    /**
     * A dose that was not taken, with no reason and no note.
     *
     * "Withheld" with nothing said about why is a gap in the record that only a
     * manager can get closed, and it is the sort of thing an inspector finds
     * long after everybody has forgotten.
     */
    private function recordStillIncomplete(int $serviceId, ?int $id): bool
    {
        $administration = Administration::where('service_id', $serviceId)->find($id);

        if (! $administration || $administration->wasTaken()) {
            return false;
        }

        // A later correction explaining it closes the gap.
        if (Administration::where('corrects_administration_id', $administration->id)->exists()) {
            return false;
        }

        return blank($administration->reason_code) && blank($administration->notes);
    }

    private function staffStillBlocked(int $serviceId, ?int $userId): bool
    {
        $user = User::find($userId);

        if (! $user) {
            return false;
        }

        return ! app(AccessPolicy::class)->allows($user, 'administer_medication', $serviceId);
    }
}
