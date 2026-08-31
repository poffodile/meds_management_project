<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Handover;
use App\Models\Record7\HandoverRead;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\User;
use Illuminate\Support\Carbon;

/**
 * Everything one shift, in one house, needs to know right now.
 *
 * WHY THIS IS A SERVICE AND NOT A CONTROLLER METHOD
 * The order things appear in matters clinically, and the rules that decide that
 * order — what counts as late, what counts as time-critical, what still needs
 * an answer — are the substance of the screen. They are testable here and they
 * would not be inside a controller action.
 *
 * WHAT IT WILL NOT DO
 * Every query is bound to one service id, which the caller has already proved
 * the person may enter. There is no cross-house query in this class and no
 * organisation-level aggregate: a support worker's dashboard is the house they
 * are standing in, and nothing else.
 *
 * AND IT NAMES NO MEDICINES.
 * Today answers one question — what do I need to do right now — and a drug
 * name does not help answer it. Which person, how urgent, and what to do next
 * does. Names, strengths, routes, doses and instructions belong on the round
 * screen, where somebody is actually holding the box; putting them here made
 * the dashboard a worse version of a screen that does not exist yet.
 *
 * Nothing was deleted from the database to achieve this. The detail is still
 * recorded, still queried, still authorised — it is simply not this screen's
 * job to show it.
 */
class ShiftBoard
{
    /**
     * Whether a clinical condition is still live is ONE question with ONE
     * answer, and IssueRegistry owns it. Asking rather than repeating the
     * query is what stops this board and the manager's board drifting apart —
     * which is exactly what happened before Section 2.3.
     */
    public function __construct(private readonly IssueRegistry $issues)
    {
    }

    /** Beyond this a "recently completed" entry is not recent, it is history. */
    private const RECENT_HOURS = 12;

    /** How much of a handover fits in a corridor. */
    private const HANDOVER_NOTES = 3;

    /* ── Shift handover ─────────────────────────────────────────────────── */

    /**
     * The most recent handover covering this house — at most three things.
     *
     * Urgent first, then important, then routine. Three because a briefing
     * somebody actually reads standing in a corridor is three lines long; the
     * fourteen-line version gets scrolled past, including the two that
     * mattered. The rest stay one tap away rather than being lost.
     */
    public function handover(int $serviceId, ?User $user = null): ?array
    {
        $handover = Handover::with(['notes.client', 'writtenBy'])
            ->where('service_id', $serviceId)
            ->orderByDesc('covers_to')
            ->first();

        if (! $handover) {
            return null;
        }

        $rank = ['urgent' => 0, 'important' => 1, 'routine' => 2];

        $ordered = $handover->notes->sortBy(fn ($note) => $rank[$note->priority] ?? 3)->values();

        $notes = $ordered->take(self::HANDOVER_NOTES)
            ->map(fn ($note) => [
                'id' => $note->id,
                'priority' => $note->priority,
                'note' => $note->note,
                'client' => $note->client?->displayName(),
            ])->all();

        $read = $user
            ? HandoverRead::where('handover_id', $handover->id)->where('user_id', $user->id)->first()
            : null;

        return [
            'id' => $handover->id,
            'shift' => $handover->shift,
            'writtenBy' => $handover->writtenBy?->displayName(),
            'endedAt' => $this->relative($handover->covers_to),
            'summary' => $handover->summary,
            'notes' => $notes,
            'moreCount' => max(0, $ordered->count() - self::HANDOVER_NOTES),
            'urgentCount' => $ordered->where('priority', 'urgent')->count(),
            'readAt' => $read?->read_at->format('H:i'),
        ];
    }

    /* ── Needs attention ────────────────────────────────────────────────── */

    /**
     * The things that are wrong right now, worst first.
     *
     * One list, not four counters. A support worker does not want to know
     * there are two late doses and one outstanding follow-up; they want to
     * know what to do first, and that is a judgement across all of them.
     *
     * Four things per row and no more: WHO, WHAT IS WRONG in plain words, HOW
     * URGENT, and WHAT TO DO NEXT. No medicine names — knowing it is
     * co-careldopa does not change the next action, and it pushed the action
     * itself off the bottom of a phone.
     */
    public function needsAttention(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();
        $items = [];

        foreach ($this->lateDoses($serviceId, $now) as $dose) {
            $prescription = $dose->prescription;
            $critical = $prescription->is_time_critical;

            $away = ! $dose->client->isAvailable();

            $items[] = [
                'kind' => $away ? 'away' : ($critical ? 'late_time_critical' : 'late'),
                'severity' => $away ? 2 : ($critical ? 0 : 1),
                'minutes' => $dose->minutesLate($now),
                'client' => $dose->client->displayName(),
                'room' => $dose->client->room_name,
                'dueAt' => $dose->due_at->format('H:i'),
                // Not "unexplained" when the explanation is that they are on a
                // ward. The dose still needs an outcome; it does not need
                // somebody sent to their room to look for them.
                'problem' => $away
                    ? 'A planned medicine is unrecorded while '.$dose->client->displayName()
                        .' is '.strtolower($dose->client->statusWord())
                    : ($critical ? 'A time-critical medicine is late' : 'A medicine is late'),
                'next' => $away
                    ? 'Record why it was not given'
                    : ($critical
                        ? 'Go to '.$dose->client->displayName().' before anyone else'
                        : 'Give it in this round'),
            ];
        }

        foreach ($this->outstandingFollowUps($serviceId, $now) as $followUp) {
            $administration = $followUp->administration;

            $items[] = [
                'kind' => 'follow_up',
                'severity' => 2,
                'minutes' => (int) max(0, $followUp->due_at->diffInMinutes($now, false)),
                'client' => $followUp->client->displayName(),
                'room' => $followUp->client->room_name,
                'dueAt' => $followUp->due_at->format('H:i'),
                'problem' => 'Nobody has recorded whether an as-required medicine worked',
                'next' => 'Ask '.$followUp->client->displayName().' and record the answer',

                // THE ROW SAYS WHAT TO DO; THIS IS WHAT LETS SOMEBODY DO IT.
                // The follow-up screen was built in Section 2.4 and has been
                // reachable only by typing its address. The id travels; Today
                // turns it into a link, because building routes is the
                // controller's job and not this board's.
                'followUpId' => $followUp->id,
            ];
        }

        foreach ($this->recentRefusalsAndGaps($serviceId, $now) as $administration) {
            $gap = $administration->outcome === 'not_available';

            $items[] = [
                'kind' => $administration->outcome,
                'severity' => $gap ? 2 : 3,
                'minutes' => (int) max(0, $administration->administered_at->diffInMinutes($now, false)),
                'client' => $administration->client->displayName(),
                'room' => $administration->client->room_name,
                'dueAt' => $administration->administered_at->format('H:i'),
                'problem' => $gap
                    ? 'A medicine is out of stock'
                    : 'A medicine was refused and has not been offered again',
                'next' => $gap
                    ? 'Chase the pharmacy and tell the manager'
                    : 'Offer it again to '.$administration->client->displayName(),
            ];
        }

        foreach ($this->recentChanges($serviceId) as $change) {
            $items[] = [
                'kind' => 'changed',
                'severity' => 2,
                'minutes' => 0,
                'client' => $change['client'],
                'room' => $change['room'],
                'dueAt' => null,
                'problem' => 'A medicine was changed on '.$change['changedOn'],
                'next' => 'Read the change before giving anything to '.$change['client'],
            ];
        }

        // Worst first, and within that the one that has been waiting longest.
        usort($items, fn ($a, $b) => [$a['severity'], -$a['minutes']] <=> [$b['severity'], -$b['minutes']]);

        return $items;
    }

    /* ── Shift overview ─────────────────────────────────────────────────── */

    /**
     * Where the day has got to. Three plain numbers, not a chart.
     *
     * The closest this screen comes to a statistic, and it stays because "ten
     * still to do" is genuinely what you want to know when you walk in. It is
     * not a manager's number — it is today, this house, and it changes as you
     * work.
     */
    public function overview(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        $doses = ScheduledDose::with('administration')
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->get();

        $recorded = $doses->filter(fn ($dose) => $dose->administration !== null);

        return [
            'total' => $doses->count(),
            'recorded' => $recorded->count(),
            'remaining' => $doses->count() - $recorded->count(),
            'notTaken' => $recorded->filter(
                fn ($dose) => in_array($dose->administration->outcome, Administration::NOT_TAKEN, true)
            )->count(),
            'peopleAway' => Client::where('service_id', $serviceId)
                ->where('status', '!=', 'active')->count(),
        ];
    }

    /* ── The round ──────────────────────────────────────────────────────── */

    /**
     * The next or current round, and whether it has been started.
     *
     * "Next" is the earliest slot today that still has an unrecorded dose. That
     * is more honest than reading the clock: if the morning was never finished
     * it is still the morning round, whatever time it is now.
     */
    public function round(int $serviceId, ?Carbon $now = null): ?array
    {
        $now ??= now();

        $doses = ScheduledDose::with(['administration', 'prescription'])
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->orderBy('due_at')
            ->get();

        $outstanding = $doses->filter(fn ($dose) => $dose->administration === null);

        if ($outstanding->isEmpty()) {
            return [
                'slot' => null,
                'state' => 'done',
                'total' => $doses->count(),
                'recorded' => $doses->count(),
            ];
        }

        $slot = $outstanding->first()->slot;
        $inSlot = $doses->where('slot', $slot);
        $recordedInSlot = $inSlot->filter(fn ($dose) => $dose->administration !== null);

        $round = Round::where('service_id', $serviceId)
            ->whereDate('round_date', $now->toDateString())
            ->where('slot', $slot)
            ->first();

        $dueAt = $inSlot->min('due_at');

        return [
            'slot' => $slot,
            'state' => $round && $round->isInProgress() ? 'in_progress' : 'not_started',
            'startedAt' => $round?->started_at?->format('H:i'),
            'dueAt' => Carbon::parse($dueAt)->format('H:i'),
            'startsIn' => $this->relative(Carbon::parse($dueAt)),
            'total' => $inSlot->count(),
            'recorded' => $recordedInSlot->count(),
            'remaining' => $inSlot->count() - $recordedInSlot->count(),
            'timeCritical' => $inSlot->filter(
                fn ($dose) => $dose->administration === null && $dose->prescription->is_time_critical
            )->count(),
        ];
    }

    /* ── People due ─────────────────────────────────────────────────────── */

    /**
     * Who needs something NOW, grouped by person rather than by dose.
     *
     * By person because that is how the work is actually done: you walk to
     * Terence's flat once and deal with everything he is due, rather than
     * walking the building once per tablet.
     *
     * "Now" means late, or in the round that is currently open. A tablet due at
     * half past nine tonight is not something anybody is due at ten in the
     * morning, and putting it here turns a list of six urgent things into a
     * list of thirty, most of which can wait. Everything further out is counted
     * by laterToday() instead of given a card.
     */
    public function peopleDue(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        $doses = $this->outstandingToday($serviceId, $now)
            ->filter(fn ($dose) => $dose->isLate($now) || $dose->slot === $this->currentSlot($serviceId, $now));

        $people = $doses->groupBy('client_id')->map(function ($forClient) use ($now) {
            $client = $forClient->first()->client;
            $next = $forClient->sortBy('due_at')->first();

            return [
                'clientId' => $client->id,
                'name' => $client->displayName(),
                'fullName' => $client->full_name,
                'room' => $client->room_name,
                // Somebody can be due a dose and not be in the building. Saying
                // "1 medicine due" without saying "in hospital" would send a
                // support worker to an empty flat.
                'available' => $client->isAvailable(),
                'whereabouts' => $client->isAvailable() ? null : $client->statusWord(),
                'nextDueAt' => $next->due_at->format('H:i'),
                'slot' => $next->slot,
                'isLate' => $next->isLate($now),
                'minutesLate' => $next->minutesLate($now),
                'timeCritical' => $forClient->contains(fn ($d) => $d->prescription->is_time_critical),
                // The substance and how bad it is. Not the reaction — that is
                // detail for the moment of giving, and this is the moment of
                // deciding who to walk to.
                'criticalAllergies' => $client->allergies
                    ->filter(fn ($allergy) => $allergy->isCritical())
                    ->map(fn ($allergy) => [
                        'substance' => $allergy->substance,
                        'severity' => $allergy->severityWord(),
                    ])->values()->all(),
                // A COUNT, NOT A LIST. How many things this person is waiting
                // for is what decides whether you can fit them in before the
                // handover meeting. Which medicines they are is the round
                // screen's business, and naming them here turned a list of
                // five people into three screenfuls.
                'medicineCount' => $forClient->count(),
                'changed' => $forClient->contains(fn ($d) => $d->prescription->changedRecently()),
            ];
        })->values()->all();

        // Late first, then time-critical, then simply by when it is due.
        usort($people, fn ($a, $b) => [! $a['isLate'], ! $a['timeCritical'], $a['nextDueAt']]
            <=> [! $b['isLate'], ! $b['timeCritical'], $b['nextDueAt']]);

        return $people;
    }

    /**
     * The rest of the day, in one line per person.
     *
     * Not cards. This exists so somebody can see the shape of the shift ahead
     * without it competing with what is due now.
     */
    public function laterToday(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();
        $slot = $this->currentSlot($serviceId, $now);

        $doses = $this->outstandingToday($serviceId, $now)
            ->filter(fn ($dose) => ! $dose->isLate($now) && $dose->slot !== $slot);

        return $doses->groupBy('slot')->map(fn ($inSlot, $slotName) => [
            'slot' => $slotName,
            'at' => $inSlot->min('due_at')
                ? Carbon::parse($inSlot->min('due_at'))->format('H:i')
                : null,
            'doses' => $inSlot->count(),
            'people' => $inSlot->pluck('client_id')->unique()->count(),
        ])->values()->all();
    }

    /* ── My tasks and PRN follow-ups ────────────────────────────────────── */

    /**
     * What this person specifically still has to close off.
     *
     * "Mine" is deliberately narrow: an outstanding follow-up on a PRN this
     * person gave is theirs. Everything else in the house is the shift's, and
     * lives in Needs attention. Blurring the two produces a task list nobody
     * feels responsible for.
     */
    public function myTasks(User $user, int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        $followUps = PrnFollowUp::with(['administration.prescription.medicine', 'client'])
            ->where('service_id', $serviceId)
            ->where('outcome', 'pending')
            ->orderBy('due_at')
            ->get();

        return $followUps->map(function ($followUp) use ($now, $user) {
            $administration = $followUp->administration;

            return [
                'id' => $followUp->id,
                'client' => $followUp->client->displayName(),
                'room' => $followUp->client->room_name,
                // Why it was given, not what was given. "For pain" is what
                // makes "did it work?" a question somebody can answer; the
                // drug name is not, and it is the round screen's to show.
                'indication' => $administration->prescription->prn_indication,
                'givenAt' => $administration->administered_at->format('H:i'),
                'givenBy' => $administration->recordedBy?->displayName(),
                'mine' => $administration->recorded_by_user_id === $user->id,
                'overdue' => $followUp->due_at->lessThan($now),
                'waitingFor' => $this->relative($followUp->due_at),
            ];
        })->values()->all();
    }

    /* ── Recently completed ─────────────────────────────────────────────── */

    /**
     * What has already been done, so nobody does it twice.
     *
     * The commonest double-dose near-miss on a shift change is somebody
     * repeating a round the previous person had already finished but not yet
     * mentioned. This is the answer to "has Margaret had her morning ones?"
     */
    public function recentlyCompleted(int $serviceId, ?Carbon $now = null): array
    {
        $now ??= now();

        $entries = Administration::with(['client', 'recordedBy'])
            ->where('service_id', $serviceId)
            ->where('administered_at', '>=', $now->copy()->subHours(self::RECENT_HOURS))
            ->orderByDesc('administered_at')
            ->limit(20)
            ->get()
            ->map(fn ($administration) => [
                'id' => $administration->id,
                'client' => $administration->client->displayName(),
                'outcome' => $administration->outcome,
                'outcomeWord' => $administration->outcomeWord(),
                'taken' => $administration->wasTaken(),
                'at' => $administration->administered_at->format('H:i'),
                'by' => $administration->recordedBy?->displayName(),
            ])->all();

        return [
            // The band is collapsed, so the summary is what most people ever
            // read: enough to answer "has the morning been done?" without
            // opening anything.
            'count' => count($entries),
            'notTaken' => count(array_filter($entries, fn ($e) => ! $e['taken'])),
            'entries' => $entries,
        ];
    }

    /* ── Medication changes ─────────────────────────────────────────────── */

    /**
     * Prescriptions changed in the last week, for this house only.
     *
     * A change nobody was told about is the classic cause of a wrong dose after
     * a few days off, so it belongs on this screen — but as an ACTION, not as a
     * list of medicines. It is folded into Needs attention, which is where
     * things that need doing live, and it says which person and what to do
     * rather than which drug and what strength.
     */
    public function recentChanges(int $serviceId): array
    {
        return Prescription::with('client')
            ->whereIn('client_id', Client::where('service_id', $serviceId)->select('id'))
            ->whereNotNull('changed_at')
            ->where('changed_at', '>=', now()->subDays(7))
            ->orderByDesc('changed_at')
            ->get()
            ->map(fn ($prescription) => [
                'client' => $prescription->client->displayName(),
                'room' => $prescription->client->room_name,
                'changedOn' => $prescription->changed_at->format('l j F'),
            ])->all();
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    /** Today's doses with nothing recorded against them yet. */
    private function outstandingToday(int $serviceId, Carbon $now)
    {
        return ScheduledDose::with(['administration', 'prescription.medicine', 'client.allergies'])
            ->where('service_id', $serviceId)
            ->whereBetween('due_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
            ->orderBy('due_at')
            ->get()
            ->filter(fn ($dose) => $dose->administration === null);
    }

    /**
     * The slot the house is actually working on.
     *
     * The earliest slot today that still has something unrecorded — which is
     * more honest than reading the clock. If the morning was never finished it
     * is still the morning round, whatever time it is now.
     */
    private function currentSlot(int $serviceId, Carbon $now): ?string
    {
        return $this->outstandingToday($serviceId, $now)->first()?->slot;
    }

    /** Doses past their grace period with nothing recorded against them. */
    private function lateDoses(int $serviceId, Carbon $now)
    {
        return ScheduledDose::with(['prescription.medicine', 'client', 'administration'])
            ->where('service_id', $serviceId)
            ->where('due_at', '>=', $now->copy()->startOfDay())
            ->where('due_at', '<=', $now)
            ->get()
            ->filter(fn ($dose) => $dose->isLate($now));
    }

    private function outstandingFollowUps(int $serviceId, Carbon $now)
    {
        return PrnFollowUp::with(['administration.prescription.medicine', 'administration.recordedBy', 'client'])
            ->where('service_id', $serviceId)
            ->where('outcome', 'pending')
            ->where('due_at', '<=', $now)
            ->get();
    }

    /**
     * Refusals and stock gaps from this shift that nothing has happened about.
     *
     * "Nothing has happened about" means no later administration for the same
     * prescription. A refusal that was re-offered and accepted an hour later is
     * closed, and putting it on this list would train people to ignore it.
     */
    private function recentRefusalsAndGaps(int $serviceId, Carbon $now)
    {
        $flagged = Administration::with(['client', 'prescription.medicine'])
            ->where('service_id', $serviceId)
            ->whereIn('outcome', ['refused', 'not_available'])
            ->where('administered_at', '>=', $now->copy()->subHours(self::RECENT_HOURS))
            ->orderByDesc('administered_at')
            ->get();

        // SECTION 2.3. This used to close a refusal as soon as ANY later dose
        // of the same prescription was given — so tonight's tablet answered for
        // this morning's refusal, and a person nobody went back to quietly
        // dropped off the list.
        //
        // A refusal is now answered only by an accepted re-offer of the SAME
        // planned dose, linked to the refusal itself. That is the same rule
        // IssueRegistry applies; asking it here rather than repeating the query
        // is what stops the two drifting apart again.
        return $flagged->filter(
            fn ($administration) => $this->issues->conditionActive(
                ($administration->outcome === 'refused' ? 'refusal:' : 'incomplete_record:')
                    .$administration->id,
                $serviceId
            )
        );
    }

    /**
     * "20 minutes ago", "in 2 hours".
     *
     * Written out rather than a timestamp because the question a support worker
     * is actually asking is how long, not when.
     */
    private function relative(Carbon $moment): string
    {
        $minutes = (int) round($moment->diffInSeconds(now(), false) / 60);
        $past = $minutes >= 0;
        $size = abs($minutes);

        if ($size < 1) {
            return 'just now';
        }

        $phrase = $size < 60
            ? $size.' '.($size === 1 ? 'minute' : 'minutes')
            : $this->hours($size);

        return $past ? $phrase.' ago' : 'in '.$phrase;
    }

    private function hours(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        $phrase = $hours.' '.($hours === 1 ? 'hour' : 'hours');

        return $rest ? $phrase.' '.$rest.' min' : $phrase;
    }
}
