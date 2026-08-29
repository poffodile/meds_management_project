<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockLevel;
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
        'staff_readiness',
        'review',
        'handover_unread',
        'welfare_check',
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
            'stock_out', 'stock_low' => StockLevel::where('service_id', $serviceId)
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

            // The stock event has not been resolved.
            'stock_event' => StockEvent::where('service_id', $serviceId)
                ->where('id', $id)->whereNull('resolved_at')->exists(),

            'stock_out' => (bool) StockLevel::where('service_id', $serviceId)->find($id)?->isOut(),
            'stock_low' => (bool) StockLevel::where('service_id', $serviceId)->find($id)?->isLow(),

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

            default => true,
        };
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
