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
            'handover_unread' => \App\Models\Record7\Handover::where('service_id', $serviceId)
                ->find($parsed['sourceId']),
            'welfare_check' => Administration::where('service_id', $serviceId)
                ->where('outcome', 'person_unavailable')
                ->where('reason_code', 'not_found_in_service')
                ->find($parsed['sourceId']),
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
            'omitted_dose', 'time_critical_omission' => (bool) ScheduledDose::with('administration')
                ->where('service_id', $serviceId)
                ->find($id)?->isLate($now),
            'refusal' => $this->refusalStillOpen($serviceId, $id),
            'prn_follow_up' => PrnFollowUp::where('service_id', $serviceId)
                ->where('id', $id)->where('outcome', 'pending')->exists(),
            'incomplete_record' => $this->recordStillIncomplete($serviceId, $id),
            'stock_event' => StockEvent::where('service_id', $serviceId)
                ->where('id', $id)->where('kind', 'delivery_overdue')
                ->whereNull('resolved_at')->exists(),
            'stock_out' => (bool) StockBalance::with('threshold')
                ->where('service_id', $serviceId)->find($id)?->isOut(),
            'stock_low' => (bool) StockBalance::with('threshold')
                ->where('service_id', $serviceId)->find($id)?->isLow(),
            'stock_discrepancy' => $this->stockDiscrepancyOpen($serviceId, $id),
            'stock_verification_due' => $this->stockVerificationDue($serviceId, $id),
            'controlled_drug_discrepancy' => $this->controlledDiscrepancyOpen($serviceId, $id),
            'staff_readiness' => $this->staffStillBlocked($serviceId, $id),
            'review' => ReviewItem::where('service_id', $serviceId)
                ->where('id', $id)->where('status', 'open')->exists(),
            'handover_unread' => true,
            'welfare_check' => $this->personStillUnaccountedFor($serviceId, $id),
            'prn_concerning_response' => PrnFollowUp::where('service_id', $serviceId)
                ->where('id', $id)
                ->where('concerning_response', true)
                ->exists(),
            default => true,
        };
    }

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

        $balance = StockBalance::where('client_id', $correction->client_id)
            ->where('medicine_id', $medicineId)
            ->first();

        if ($balance === null) {
            return false;
        }

        $counted = StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->where('action', 'stock_check')
            ->where('occurred_at', '>=', $correction->created_at)
            ->exists();

        return ! $counted;
    }

    public function requiresEvidence(string $issueKey, int $serviceId): bool
    {
        $parsed = $this->parse($issueKey);

        if ($parsed['type'] === 'stock_event') {
            $event = StockEvent::with('medicine')
                ->where('service_id', $serviceId)
                ->find($parsed['sourceId']);

            return $event?->kind === 'discrepancy' || (bool) $event?->medicine?->is_controlled;
        }

        return in_array($parsed['type'], [
            'time_critical_omission',
            'stock_out',
            'stock_discrepancy',
            'stock_verification_due',
            'controlled_drug_discrepancy',
            'welfare_check',
            'prn_concerning_response',
        ], true);
    }

    private function personStillUnaccountedFor(int $serviceId, ?int $id): bool
    {
        $report = Administration::where('service_id', $serviceId)
            ->where('outcome', 'person_unavailable')
            ->where('reason_code', 'not_found_in_service')
            ->find($id);

        if (! $report) {
            return false;
        }

        $evidence = WelfareCheck::where('administration_id', $report->id)
            ->where('client_id', $report->client_id)
            ->where('service_id', $report->service_id)
            ->exists();

        if ($evidence) {
            return false;
        }

        $client = Client::find($report->client_id);

        return ! ($client && $client->status !== 'active');
    }

    private function refusalStillOpen(int $serviceId, ?int $id): bool
    {
        $refusal = Administration::where('service_id', $serviceId)->find($id);

        if (! $refusal || $refusal->outcome !== 'refused') {
            return false;
        }

        $reoffers = Administration::where('scheduled_dose_id', $refusal->scheduled_dose_id)
            ->where('service_id', $serviceId)
            ->where('client_id', $refusal->client_id)
            ->where('prescription_id', $refusal->prescription_id)
            ->where('reoffer_of_administration_id', $refusal->id)
            ->get();

        $accepted = $reoffers->contains(function (Administration $reoffer) use ($serviceId) {
            // Corrections are append-only, so the original re-offer row remains.
            // The refusal lifecycle must use what that re-offer now effectively
            // says, not merely the first value that was written on it.
            $correction = Administration::where('service_id', $serviceId)
                ->where('corrects_administration_id', $reoffer->id)
                ->first();

            return ($correction ?? $reoffer)->wasTaken();
        });

        return ! $accepted;
    }

    private function recordStillIncomplete(int $serviceId, ?int $id): bool
    {
        $administration = Administration::where('service_id', $serviceId)->find($id);

        if (! $administration || $administration->wasTaken()) {
            return false;
        }

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
