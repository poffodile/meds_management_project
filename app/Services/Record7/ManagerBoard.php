<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Handover;
use App\Models\Record7\HandoverRead;
use App\Models\Record7\IssueState;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockLevel;
use App\Models\Record7\User;
use App\Models\Record7\UserServiceAccess;
use Illuminate\Support\Carbon;

/**
 * Everything a manager has to act on, in ONE house.
 *
 * THE HOUSE IS THE BOUNDARY, AND IT IS NOT NEGOTIABLE.
 * Daniel Evans manages two houses and can switch between them, but he is only
 * ever IN one. Every method here takes a service id and filters by it; there is
 * no method that spans houses and no aggregate across an organisation. Two
 * houses' medicines, staff and incidents never appear in the same list, because
 * a manager acting on the wrong house's information is exactly the failure this
 * boundary exists to prevent. The caller has already proved he may enter the
 * house he asked for.
 *
 * ISSUES ARE DERIVED, NOT STORED.
 * A late dose, an unread handover, an expired competency — all of these are
 * computed from the clinical and access records every time they are asked for.
 * Nothing here writes a copy of a problem into a "manager problems" table,
 * because a copy goes stale and then two screens disagree about whether
 * something is still true.
 *
 * What IS stored is what people have done about a problem — acknowledged,
 * owned, escalated, action recorded, closed — and that lives on
 * record7_issue_states.
 *
 * WORKFLOW STATE CANNOT HIDE A LIVE CONDITION, AND THAT IS THE POINT.
 * An earlier version filtered anything marked resolved out of this list, which
 * meant a manager could clear a time-critical omission off their own screen
 * while the dose was still unrecorded. Nothing is filtered by workflow state
 * now. An issue is present for exactly as long as IssueRegistry says the
 * clinical, stock or access condition behind it is still true; if somebody has
 * closed it in the meantime it stays, and says so.
 */
class ManagerBoard
{
    /** Recent enough that it is this shift's problem rather than history. */
    private const RECENT_HOURS = 24;

    /** A competency expiring inside this is a rota problem now, not later. */
    private const EXPIRY_WARNING_DAYS = 30;

    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly IssueRegistry $registry
    ) {
    }

    /* ── 1. Management attention ────────────────────────────────────────── */

    /**
     * One risk-ordered list of everything unresolved that needs a manager.
     *
     * Deliberately one list. A manager arriving mid-shift does not want six
     * counters; they want to know what to do first, and that is a judgement
     * across staffing, medicines, stock and paperwork at once — no one of those
     * categories is reliably more urgent than another.
     *
     * Every item says WHY a manager specifically is needed, because most of
     * what goes wrong in a house is a support worker's to fix and putting those
     * here would bury the handful that are not.
     */
    public function attention(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();
        $house = Service::find($serviceId);
        $items = [];

        foreach ($this->omittedDoses($serviceId, $now) as $dose) {
            $critical = $dose->prescription->is_time_critical;

            $items[] = $this->item($serviceId, [
                'key' => 'omitted_dose:'.$dose->id,
                'kind' => $critical ? 'time_critical_omission' : 'omission',
                'severity' => $critical ? 'high' : 'medium',
                'rank' => $critical ? 0 : 2,
                'subject' => $dose->client->displayName(),
                'subjectKind' => 'client',
                'issue' => $critical
                    ? 'A time-critical medicine has no record and no explanation'
                    : 'A dose has no record and no explanation',
                'why' => $critical
                    ? 'An unexplained time-critical omission is reportable and needs establishing today'
                    : 'An unexplained gap in the record cannot be closed by the person who left it',
                'next' => 'Establish what happened and record the outcome or an incident',
                'at' => $dose->due_at,
                'minutes' => $dose->minutesLate($now),
            ]);
        }

        foreach ($this->unresolvedRefusals($serviceId, $now) as $administration) {
            $items[] = $this->item($serviceId, [
                'key' => 'refusal:'.$administration->id,
                'kind' => 'refusal',
                'severity' => 'medium',
                'rank' => 3,
                'subject' => $administration->client->displayName(),
                'subjectKind' => 'client',
                'issue' => 'A refused dose has not been offered again',
                'why' => 'Repeated refusal is a capacity and care-plan question, not a round problem',
                'next' => 'Check it has been re-offered and review the care plan if it is a pattern',
                'at' => $administration->administered_at,
                'minutes' => (int) max(0, $administration->administered_at->diffInMinutes($now, false)),
            ]);
        }

        foreach ($this->outstandingFollowUps($serviceId, $now) as $followUp) {
            $items[] = $this->item($serviceId, [
                'key' => 'prn_follow_up:'.$followUp->id,
                'kind' => 'prn_follow_up',
                'severity' => 'medium',
                'rank' => 4,
                'subject' => $followUp->client->displayName(),
                'subjectKind' => 'client',
                'issue' => 'Nobody has recorded whether an as-required medicine worked',
                'why' => 'An unanswered effectiveness check is an audit finding and can hide uncontrolled pain',
                'next' => 'Have the answer recorded before the shift ends',
                'at' => $followUp->due_at,
                'minutes' => (int) max(0, $followUp->due_at->diffInMinutes($now, false)),
            ]);
        }

        foreach ($this->incompleteRecords($serviceId, $now) as $administration) {
            $items[] = $this->item($serviceId, [
                'key' => 'incomplete_record:'.$administration->id,
                'kind' => 'incomplete_record',
                'severity' => 'medium',
                'rank' => 2,
                'subject' => $administration->client->displayName(),
                'subjectKind' => 'client',
                'issue' => 'A dose was not taken and no reason was recorded',
                'why' => 'A gap with no explanation cannot be closed by the person who left it, '
                    .'and it is what an inspection finds first',
                'next' => 'Establish the reason and have a correction recorded against it',
                'at' => $administration->administered_at,
                'minutes' => (int) max(0, $administration->administered_at->diffInMinutes($now, false)),
            ]);
        }

        foreach ($this->stockConcerns($serviceId) as $concern) {
            $items[] = $this->item($serviceId, [
                'key' => $concern['key'],
                'kind' => $concern['kind'],
                'severity' => $concern['severity'],
                'rank' => $concern['kind'] === 'controlled_drug_discrepancy' ? 1 : 3,
                'subject' => $concern['medicine'],
                'subjectKind' => 'medicine',
                'issue' => $concern['issue'],
                'why' => $concern['why'],
                'next' => $concern['next'],
                'at' => $concern['at'],
                'minutes' => $concern['at']
                    ? (int) max(0, Carbon::parse($concern['at'])->diffInMinutes($now, false))
                    : 0,
            ]);
        }

        foreach ($this->staffReadiness($serviceId) as $member) {
            if ($member['mayAdminister'] || ! $member['blocking']) {
                continue;
            }

            $items[] = $this->item($serviceId, [
                'key' => 'staff_readiness:'.$member['userId'],
                'kind' => 'staff_readiness',
                'severity' => 'medium',
                'rank' => 2,
                'subject' => $member['name'],
                'subjectKind' => 'staff',
                'issue' => $member['reason'],
                'why' => 'Only a manager can arrange reassessment or change the rota',
                'next' => 'Cover the rota and book the reassessment',
                'at' => null,
                'minutes' => 0,
            ]);
        }

        foreach ($this->openReviewItems($serviceId) as $review) {
            $items[] = $this->item($serviceId, [
                'key' => 'review:'.$review['id'],
                'kind' => 'review',
                'severity' => $review['severity'],
                'rank' => $review['severity'] === 'high' ? 1 : 3,
                'subject' => $review['kindWord'],
                'subjectKind' => 'review',
                'issue' => $review['title'],
                'why' => 'It is waiting on a management decision and nothing moves until it is made',
                'next' => 'Approve or decline it',
                'at' => $review['raisedAt'],
                'minutes' => $review['waitingMinutes'],
            ]);
        }

        foreach ($this->handoverOversight($serviceId) as $handover) {
            if (! $handover['urgentCount'] || ! $handover['outstanding']) {
                continue;
            }

            $items[] = $this->item($serviceId, [
                'key' => 'handover_unread:'.$handover['id'],
                'kind' => 'handover_unread',
                'severity' => 'medium',
                'rank' => 2,
                'subject' => $handover['shift'],
                'subjectKind' => 'handover',
                'issue' => count($handover['outstanding']).' staff have not confirmed an urgent handover',
                'why' => 'A handover nobody confirms reading has not been handed over',
                'next' => 'Confirm the urgent notes have reached everybody on shift',
                'at' => null,
                'minutes' => 0,
            ]);
        }

        /*
         * NOTHING IS FILTERED OUT BY WORKFLOW STATE.
         *
         * There used to be a line here removing anything a manager had marked
         * resolved. It meant pressing a button cleared a live problem off the
         * screen, which is the exact failure mode this whole section exists to
         * prevent. An issue is on this list because its condition is still
         * true; a closed one is still on it, wearing the words that say so.
         *
         * A closed issue sorts below an open one of the same rank, because
         * somebody is already on it — but it never disappears.
         */
        usort($items, fn ($a, $b) => [$a['rank'], $a['closed'], -$a['minutes']]
            <=> [$b['rank'], $b['closed'], -$b['minutes']]);

        return array_map(function ($item) use ($house) {
            $item['house'] = $house?->name;

            return $item;
        }, $items);
    }

    /**
     * Attach whatever anybody has already done about this issue.
     *
     * The issue is derived; the ownership, escalation and resolution are read
     * from record7_issue_states. Keeping them apart is what lets a manager take
     * ownership of a late dose without writing anything to the dose.
     */
    private function item(int $serviceId, array $item): array
    {
        $state = $this->issueStates($serviceId)->get($item['key']);

        // Formatted BEFORE the merge. PHP's + keeps the left-hand value for a
        // key that already exists, so putting this in the defaults below meant
        // the raw timestamp survived and the screen printed an ISO string.
        $item['at'] = ! empty($item['at']) ? Carbon::parse($item['at'])->format('H:i') : null;

        // Asked of the clinical record, never of the state row. This is the one
        // fact on the item that no manager action can change.
        $conditionActive = $this->registry->conditionActive($item['key'], $serviceId);

        return $item + [
            // The six concepts, reported separately so nobody has to guess
            // which of them "resolved" was supposed to mean.
            'acknowledged' => (bool) $state?->isAcknowledged(),
            'owner' => $state?->owner?->displayName(),
            'ownerId' => $state?->owner_user_id,
            'escalated' => (bool) $state?->isEscalated(),
            'escalatedAt' => $state?->escalated_at?->format('H:i'),
            'actionRecorded' => (bool) $state?->hasActionRecorded(),
            'actionNote' => $state?->action_note,
            'closed' => (bool) $state?->isClosed(),
            'closedBy' => $state?->closedBy?->displayName(),
            'closedAt' => $state?->closed_at?->format('j M H:i'),
            'closureReason' => $state?->closure_reason,
            'evidenceReference' => $state?->evidence_reference,
            'conditionActive' => $conditionActive,
            'needsEvidenceToClose' => $this->registry->requiresEvidence($item['key'], $serviceId),
            // One sentence a manager can act on, including the uncomfortable
            // one where the paperwork and the reality disagree.
            'status' => $state
                ? $state->statusWording($conditionActive)
                : ($conditionActive ? 'Open' : 'Resolved'),
            'note' => $state?->note,
        ];
    }

    private function typeOf(string $issueKey): string
    {
        return explode(':', $issueKey, 2)[0];
    }

    private ?\Illuminate\Support\Collection $states = null;

    private function issueStates(int $serviceId): \Illuminate\Support\Collection
    {
        return $this->states ??= IssueState::with('owner')
            ->where('service_id', $serviceId)
            ->get()
            ->keyBy('issue_key');
    }

    /* ── 2. Round oversight ─────────────────────────────────────────────── */

    /**
     * Every round planned for today in this house, and how it is going.
     *
     * Figures are counted from the plan and the outcomes every time. There is
     * no stored "completed" counter to drift out of step with what actually
     * happened — a number a manager acts on has to be the truth at the moment
     * they look at it.
     *
     * This is oversight, not the round itself: who, how many, how late. No
     * prescription instructions, no dose detail. That is Section 2.
     */
    public function rounds(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        $doses = ScheduledDose::with(['administration', 'prescription', 'client'])
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->orderBy('due_at')
            ->get();

        $rounds = Round::where('service_id', $serviceId)
            ->whereDate('round_date', $now->toDateString())
            ->get()
            ->keyBy('slot');

        return $doses->groupBy('slot')->map(function ($inSlot, $slot) use ($rounds, $now) {
            $round = $rounds->get($slot);

            $recorded = $inSlot->filter(fn ($dose) => $dose->administration !== null);
            $people = $inSlot->pluck('client_id')->unique();
            $donePeople = $recorded->pluck('client_id')->unique();

            $late = $inSlot->filter(fn ($dose) => $dose->isLate($now));
            $criticalLate = $late->filter(fn ($dose) => $dose->prescription->is_time_critical);

            $state = match (true) {
                $round?->closed_at !== null => 'closed',
                $recorded->count() === $inSlot->count() => 'completed',
                $round !== null => 'in_progress',
                default => 'not_started',
            };

            return [
                'slot' => $slot,
                'dueAt' => Carbon::parse($inSlot->min('due_at'))->format('H:i'),
                'state' => $state,
                'expectedPeople' => $people->count(),
                // People, not doses: a manager thinks in flats visited.
                'completedPeople' => $people->filter(
                    fn ($id) => ! $inSlot->where('client_id', $id)
                        ->contains(fn ($dose) => $dose->administration === null)
                )->count(),
                'remainingPeople' => $people->count() - $donePeople->intersect($people)->count(),
                'expectedDoses' => $inSlot->count(),
                'recordedDoses' => $recorded->count(),
                'lateCount' => $late->count(),
                'timeCriticalLate' => $criticalLate->count(),
                'openedBy' => $round?->started_by_user_id
                    ? User::find($round->started_by_user_id)?->displayName()
                    : null,
                'startedAt' => $round?->started_at?->format('H:i'),
                'closedAt' => $round?->closed_at?->format('H:i'),
                'roundId' => $round?->id,
                // The only judgement this method makes, and it is the one a
                // manager actually wants: do I need to do something.
                'interventionNeeded' => $criticalLate->count() > 0
                    || ($late->count() > 0 && $state !== 'closed'),
            ];
        })->values()->all();
    }

    /* ── 3. Staff readiness ─────────────────────────────────────────────── */

    /**
     * Who can give medicines in this house right now, and who cannot, and why.
     *
     * ROLE, PERMISSION AND COMPETENCY ARE REPORTED SEPARATELY, always. A job
     * title never authorises administration: Ruth Coleman is a Support Worker
     * with the permission and an expired competency, and the honest answer is
     * that she may not administer while remaining exactly as employed as she
     * was yesterday. Collapsing the three into one status would make that
     * impossible to say.
     *
     * The decision itself is not recomputed here — it is asked of AccessPolicy,
     * the same object that refuses the request server-side, so this screen can
     * never disagree with what actually happens.
     */
    public function staffReadiness(int $serviceId): array
    {
        $gate = CompetencyType::where('gates_permission', 'administer_medication')->first();

        $access = UserServiceAccess::with('user')
            ->where('service_id', $serviceId)
            ->get()
            ->filter(fn ($row) => $row->user !== null);

        $relevant = $access->filter(function ($row) use ($serviceId, $gate) {
            $user = $row->user;

            // Somebody the question is meaningful for: they can administer,
            // or somebody has explicitly said they may or may not, or they
            // hold the competency that gates it.
            if (in_array('administer_medication', $this->policy->grantedPermissions($user, $serviceId), true)) {
                return true;
            }

            if ($user->permissionRules()->whereHas('permission',
                fn ($q) => $q->where('code', 'administer_medication'))->exists()) {
                return true;
            }

            return $gate && $user->competencies()->where('competency_type_id', $gate->id)->exists();
        });

        return $relevant->map(function ($row) use ($serviceId, $gate) {
            $user = $row->user;
            $decision = $this->policy->decide($user, 'administer_medication', $serviceId);

            $competency = $gate
                ? $user->competencies()
                    ->where('competency_type_id', $gate->id)
                    ->where(fn ($q) => $q->whereNull('service_id')->orWhere('service_id', $serviceId))
                    ->first()
                : null;

            $expiry = $competency?->review_due_at;

            return [
                'userId' => $user->id,
                'name' => $user->displayName(),
                'fullName' => $user->full_name,
                // Employment. Never a stand-in for what they may do.
                'role' => $user->primaryRole()?->name,
                'employmentType' => $user->employment_type,
                'accountStatus' => $user->account_status,
                // Access to this house, and what kind.
                'accessType' => $row->access_type,
                'accessStatus' => $row->status,
                // Permission, separately — and deliberately WITHOUT the
                // competency gate. grantedPermissions() folds competency in,
                // which is right for deciding but wrong for reporting: it would
                // show Ruth as having no permission when what she has lost is
                // her competency. Two different problems with two different
                // fixes, and a manager needs to see which one this is.
                'hasPermission' => $this->permissionGranted($user, $serviceId),
                // Competency, separately again.
                'competencyStatus' => $competency?->status ?? 'not_assessed',
                'competencyExpires' => $expiry?->format('j M Y'),
                'competencyExpiringSoon' => $expiry !== null
                    && $competency?->status === 'current'
                    && $expiry->isBefore(now()->addDays(self::EXPIRY_WARNING_DAYS)),
                'restriction' => $row->status !== 'active' ? 'Access to this house is '.$row->status : null,
                // And only then, the decision.
                'mayAdminister' => $decision->allowed,
                'reason' => $decision->allowed ? null : $decision->message,
                // Whether this is a rota problem the manager must solve, as
                // opposed to somebody who was never meant to administer.
                'blocking' => ! $decision->allowed && $this->wasExpectedToAdminister($user, $serviceId),
            ];
        })->sortBy([
            fn ($a, $b) => $a['mayAdminister'] <=> $b['mayAdminister'],
            fn ($a, $b) => strcmp($a['name'], $b['name']),
        ])->values()->all();
    }

    /**
     * Does the permission layer alone allow this, ignoring competency?
     *
     * Deny beats allow beats the role matrix, which is the same order
     * AccessPolicy uses — this asks only the first three layers so that the
     * fourth can be reported as its own separate fact.
     */
    private function permissionGranted(User $user, int $serviceId): bool
    {
        $rule = $user->permissionRules()
            ->whereHas('permission', fn ($q) => $q->where('code', 'administer_medication'))
            ->where(fn ($q) => $q->whereNull('service_id')->orWhere('service_id', $serviceId))
            ->get()
            ->filter(fn ($rule) => $rule->isInForce());

        if ($rule->contains(fn ($r) => $r->effect === 'deny')) {
            return false;
        }

        if ($rule->contains(fn ($r) => $r->effect === 'allow')) {
            return true;
        }

        return (bool) $user->primaryRole()?->permissions()
            ->where('code', 'administer_medication')->exists();
    }

    /**
     * Somebody the rota was relying on.
     *
     * A reviewer who cannot administer is not a staffing problem — that is the
     * design. Somebody who holds the permission but is blocked by a lapsed
     * competency is, and it is the difference between an alert worth raising
     * and noise that trains managers to ignore alerts.
     */
    private function wasExpectedToAdminister(User $user, int $serviceId): bool
    {
        return $user->permissionRules()
            ->where('effect', 'allow')
            ->where(fn ($q) => $q->whereNull('service_id')->orWhere('service_id', $serviceId))
            ->whereHas('permission', fn ($q) => $q->where('code', 'administer_medication'))
            ->exists();
    }

    /* ── 4. Outstanding outcomes and follow-ups ─────────────────────────── */

    /**
     * Everything clinical that is still open in this house.
     *
     * Resolved things are absent by construction rather than by a flag: a
     * refusal that was re-offered and accepted is not in this list because a
     * later "given" exists for the same prescription, and a follow-up that was
     * answered is not pending. Nothing has to be tidied up afterwards, so
     * nothing can be forgotten and left showing as a problem.
     */
    public function outstandingOutcomes(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        return [
            'omissions' => $this->omittedDoses($serviceId, $now)->map(fn ($dose) => [
                'id' => $dose->id,
                'client' => $dose->client->displayName(),
                'slot' => $dose->slot,
                'dueAt' => $dose->due_at->format('H:i'),
                'minutesLate' => $dose->minutesLate($now),
                'timeCritical' => (bool) $dose->prescription->is_time_critical,
                'issueKey' => 'omitted_dose:'.$dose->id,
            ])->values()->all(),

            'refusals' => $this->unresolvedRefusals($serviceId, $now)->map(fn ($a) => [
                'id' => $a->id,
                'client' => $a->client->displayName(),
                'at' => $a->administered_at->format('H:i'),
                'note' => $a->notes,
                'issueKey' => 'refusal:'.$a->id,
            ])->values()->all(),

            'notTaken' => Administration::with('client')
                ->where('service_id', $serviceId)
                ->whereIn('outcome', ['withheld', 'not_available', 'missed'])
                ->where('administered_at', '>=', $now->copy()->subHours(self::RECENT_HOURS))
                ->get()
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'client' => $a->client->displayName(),
                    'outcome' => $a->outcomeWord(),
                    'at' => $a->administered_at->format('H:i'),
                    'note' => $a->notes,
                ])->values()->all(),

            'incompleteRecords' => $this->incompleteRecords($serviceId, $now)->map(fn ($a) => [
                'id' => $a->id,
                'client' => $a->client->displayName(),
                'outcome' => $a->outcomeWord(),
                'at' => $a->administered_at->format('H:i'),
                'issueKey' => 'incomplete_record:'.$a->id,
            ])->values()->all(),

            'prnFollowUps' => $this->outstandingFollowUps($serviceId, $now)->map(fn ($f) => [
                'id' => $f->id,
                'client' => $f->client->displayName(),
                'dueAt' => $f->due_at->format('H:i'),
                'overdue' => $f->due_at->lessThan($now),
                'givenAt' => $f->administration->administered_at->format('H:i'),
                'givenBy' => $f->administration->recordedBy?->displayName(),
                'issueKey' => 'prn_follow_up:'.$f->id,
            ])->values()->all(),
        ];
    }

    /* ── 5. The review queue ────────────────────────────────────────────── */

    public function openReviewItems(int $serviceId): array
    {
        $rank = ['high' => 0, 'medium' => 1, 'low' => 2];

        return ReviewItem::with(['raisedBy'])
            ->where('service_id', $serviceId)
            ->where('status', 'open')
            ->get()
            ->sortBy(fn ($item) => [$rank[$item->severity] ?? 3, -$item->raised_at->timestamp])
            ->map(fn ($item) => [
                'id' => $item->id,
                'reference' => $item->reference,
                'kind' => $item->kind,
                'kindWord' => $item->kindWord(),
                'title' => $item->title,
                'detail' => $item->detail,
                'severity' => $item->severity,
                'raisedBy' => $item->raisedBy?->displayName(),
                'raisedAt' => $item->raised_at,
                'waitingMinutes' => (int) max(0, $item->raised_at->diffInMinutes(now(), false)),
                'subjectType' => $item->subject_type,
                'subjectId' => $item->subject_id,
            ])->values()->all();
    }

    /** Decided items, for the audit view rather than the active queue. */
    public function decidedReviewItems(int $serviceId): array
    {
        return ReviewItem::with(['raisedBy', 'decidedBy'])
            ->where('service_id', $serviceId)
            ->whereIn('status', ['approved', 'declined'])
            ->orderByDesc('decided_at')
            ->limit(25)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'reference' => $item->reference,
                'kindWord' => $item->kindWord(),
                'title' => $item->title,
                'status' => $item->status,
                'decidedBy' => $item->decidedBy?->displayName(),
                'decidedAt' => $item->decided_at?->format('j M Y H:i'),
                'decisionNote' => $item->decision_note,
            ])->values()->all();
    }

    /* ── 6. Stock and controlled drugs ──────────────────────────────────── */

    /**
     * Only the exceptions a manager has to act on today.
     *
     * Not an inventory. A stock page will show what is in the cupboard; this
     * shows the four things that stop a round or start an investigation, and
     * anything already resolved is absent.
     */
    public function stockConcerns(int $serviceId): array
    {
        $concerns = [];

        foreach (StockEvent::with('medicine')
            ->where('service_id', $serviceId)
            ->whereNull('resolved_at')
            ->orderByDesc('occurred_at')
            ->get() as $event) {
            $controlled = (bool) $event->medicine?->is_controlled;

            $concerns[] = [
                'key' => 'stock_event:'.$event->id,
                'kind' => $event->kind === 'discrepancy'
                    ? ($controlled ? 'controlled_drug_discrepancy' : 'stock_discrepancy')
                    : 'delivery_overdue',
                'severity' => $controlled ? 'high' : 'medium',
                'medicine' => $event->medicine?->name,
                'controlled' => $controlled,
                'difference' => $event->difference(),
                'issue' => match (true) {
                    $event->kind === 'delivery_overdue' => 'An ordered medicine has not been delivered',
                    $controlled => 'A controlled-drug balance does not match the record',
                    default => 'A stock count does not match the record',
                },
                'why' => $controlled
                    ? 'A controlled-drug discrepancy is reportable and must be investigated by a manager'
                    : 'Only a manager can authorise a correction or chase a supplier',
                'next' => $controlled
                    ? 'Recount with a witness and report if it does not reconcile'
                    : 'Chase the supplier and record the outcome',
                'note' => $event->note,
                'at' => $event->occurred_at,
            ];
        }

        foreach (StockLevel::with('medicine')->where('service_id', $serviceId)->get() as $level) {
            if (! $level->isOut() && ! $level->isLow()) {
                continue;
            }

            $out = $level->isOut();

            $concerns[] = [
                'key' => ($out ? 'stock_out:' : 'stock_low:').$level->id,
                'kind' => $out ? 'stock_out' : 'stock_low',
                'severity' => $out ? 'high' : 'medium',
                'medicine' => $level->medicine?->name,
                'controlled' => (bool) $level->medicine?->is_controlled,
                'difference' => null,
                'issue' => $out
                    ? 'None of this medicine is in the house'
                    : 'Stock is below the level this house keeps',
                'why' => $out
                    ? 'A dose cannot be given and somebody has to obtain a supply today'
                    : 'Ordering and emergency supply are a management decision',
                'next' => $out ? 'Arrange an emergency supply and tell the prescriber' : 'Order more',
                'note' => null,
                'at' => $level->last_counted_at,
            ];
        }

        return $concerns;
    }

    /* ── 7. Handover oversight ──────────────────────────────────────────── */

    /**
     * Who has confirmed reading what, in this house only.
     *
     * "Relevant staff" is everybody with active access to the house who could
     * be working from this handover. A reviewer or an oversight account is not
     * expected to confirm a shift handover and is not counted as outstanding —
     * counting them would make the number permanently wrong and therefore
     * permanently ignored.
     */
    public function handoverOversight(int $serviceId): array
    {
        $expected = UserServiceAccess::with('user')
            ->where('service_id', $serviceId)
            ->where('status', 'active')
            ->whereIn('access_type', ['standard', 'manager', 'temporary'])
            ->get()
            ->filter(fn ($row) => $row->user && $row->user->account_status === 'active')
            ->pluck('user')
            ->keyBy('id');

        return Handover::with(['notes', 'writtenBy'])
            ->where('service_id', $serviceId)
            ->orderByDesc('covers_to')
            ->limit(3)
            ->get()
            ->map(function ($handover) use ($expected) {
                $read = HandoverRead::where('handover_id', $handover->id)->get()->keyBy('user_id');

                $acknowledged = $expected->filter(fn ($user) => $read->has($user->id));
                $outstanding = $expected->reject(fn ($user) => $read->has($user->id));

                return [
                    'id' => $handover->id,
                    'shift' => $handover->shift,
                    'writtenBy' => $handover->writtenBy?->displayName(),
                    'coversTo' => $handover->covers_to->format('j M H:i'),
                    'urgentCount' => $handover->notes->where('priority', 'urgent')->count(),
                    'acknowledged' => $acknowledged->map(fn ($user) => [
                        'name' => $user->displayName(),
                        'at' => $read->get($user->id)?->read_at->format('H:i'),
                    ])->values()->all(),
                    'outstanding' => $outstanding->map(fn ($user) => [
                        'name' => $user->displayName(),
                        'role' => $user->primaryRole()?->name,
                    ])->values()->all(),
                    'escalated' => ReviewItem::where('service_id', $handover->service_id)
                        ->where('kind', 'handover_escalation')
                        ->where('status', 'open')
                        ->exists(),
                ];
            })->values()->all();
    }

    /* ── Shared derivations ─────────────────────────────────────────────── */

    /** Past their grace period, still nothing recorded against them. */
    private function omittedDoses(int $serviceId, Carbon $now)
    {
        return ScheduledDose::with(['prescription', 'client', 'administration'])
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->subHours(self::RECENT_HOURS), $now])
            ->get()
            ->filter(fn ($dose) => $dose->isLate($now));
    }

    /** Refused, with no later "given" for the same prescription. */
    private function unresolvedRefusals(int $serviceId, Carbon $now)
    {
        return Administration::with(['client'])
            ->where('service_id', $serviceId)
            ->where('outcome', 'refused')
            ->where('administered_at', '>=', $now->copy()->subHours(self::RECENT_HOURS))
            ->get()
            ->filter(fn ($administration) => ! Administration::where('prescription_id', $administration->prescription_id)
                ->where('administered_at', '>', $administration->administered_at)
                ->whereIn('outcome', ['given', 'self_administered'])
                ->exists());
    }

    /**
     * Doses that were not taken, with nothing said about why.
     *
     * "Withheld" and a blank reason is a gap in the medicines record. The
     * person who left it usually cannot close it — they have gone home, or they
     * genuinely do not remember — which is what makes it a manager's.
     */
    private function incompleteRecords(int $serviceId, Carbon $now)
    {
        return Administration::with('client')
            ->where('service_id', $serviceId)
            ->whereIn('outcome', ['withheld', 'not_available', 'missed'])
            ->where('administered_at', '>=', $now->copy()->subHours(self::RECENT_HOURS))
            ->get()
            ->filter(fn ($administration) => blank($administration->reason_code)
                && blank($administration->notes)
                && ! Administration::where('corrects_administration_id', $administration->id)->exists());
    }

    private function outstandingFollowUps(int $serviceId, Carbon $now)
    {
        return PrnFollowUp::with(['administration.recordedBy', 'client'])
            ->where('service_id', $serviceId)
            ->where('outcome', 'pending')
            ->orderBy('due_at')
            ->get();
    }

    /** How many people live in this house, for the round figures. */
    public function clientCount(int $serviceId): int
    {
        return Client::where('service_id', $serviceId)->where('status', 'active')->count();
    }
}
