<?php

namespace App\Services\Record7;

use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use Illuminate\Support\Carbon;

/**
 * Who is in this round, in the order they should be seen.
 *
 * SECTION 2.0 SHOWS WHO AND WHEN, NEVER WHAT.
 * No medicine names, no doses, no instructions and no controls. This screen
 * answers "who do I go to next"; what to give them is 2.1, and recording it is
 * 2.2. Putting medicine detail here would mean building the same thing twice
 * and having two places to get it wrong.
 *
 * THE ORDER IS DERIVED, NEVER WRITTEN DOWN.
 * Five bands, in this order, and every one of them is computed from the dose
 * record and the prescription:
 *
 *   1. late AND time-sensitive     a Parkinson's dose an hour overdue
 *   2. late                        anything else past its grace period
 *   3. due now, time-sensitive     the insulin that must not drift
 *   4. due now                     the rest of this round
 *   5. later in this round         everything still ahead of its time
 *   6. away from the house         still owed an outcome, but not in their flat
 *
 * Within a band, earliest due first. No name appears anywhere in the sort.
 *
 * NOBODY DROPS OUT OF THE QUEUE FOR BEING ABSENT.
 * A person in hospital, away for the day, or managing their own medicines still
 * has a planned dose belonging to this house, this date and this round. That
 * obligation does not evaporate because they are not in their flat — somebody
 * still has to record what happened to it and why.
 *
 * So they stay in the queue, flagged with where they actually are and how they
 * are supported, and NOTHING is marked administered, completed or omitted on
 * their behalf. Recording the real outcome is Section 2.3's job. Filtering them
 * out would be worse than either: the dose would silently cease to exist and
 * nobody would ever be asked about it.
 */
class RoundQueue
{
    /** How a person is supported, in the words staff use. */
    private const SUPPORT_WORDS = [
        'staff_administered' => 'Staff administered',
        'assisted' => 'Assisted',
        'prompted' => 'Prompted',
        'self_administered' => 'Self-administered',
    ];

    /**
     * The people in one round of one house on one day.
     *
     * Everything is filtered by the round's own service and date, so a dose
     * belonging to another house, another day or another slot cannot appear
     * however it is asked for.
     */
    public function forRound(Round $round, ?Carbon $now = null): array
    {
        $now ??= now();

        $doses = ScheduledDose::with([
            'administration',
            'prescription',
            'client.allergies',
        ])
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->get()
            // A client whose record has moved house since the dose was planned
            // is not this round's, whatever the dose says.
            ->filter(fn ($dose) => $dose->client
                && (int) $dose->client->service_id === (int) $round->service_id);

        $people = $doses->groupBy('client_id')->map(function ($forClient) use ($now) {
            $client = $forClient->first()->client;

            // A FULLY SELF-MANAGED MEDICINE IS NOT OUTSTANDING WORK.
            // Where the agreed arrangement is that the person handles it
            // entirely themselves, there is no staff record to make — so
            // counting it as unanswered would leave every round permanently
            // incomplete and teach people that "3 remaining" means nothing.
            //
            // It stays visible on the person's own page as context; it simply
            // stops being something the round is waiting for.
            $outstanding = $forClient->filter(
                fn ($dose) => $dose->administration === null
                    && ! (bool) $dose->prescription?->isFullySelfManaged()
            );
            $late = $outstanding->filter(fn ($dose) => $dose->isLate($now));
            $timeCritical = $outstanding->filter(fn ($dose) => $dose->prescription->is_time_critical);

            $dueAt = Carbon::parse($forClient->min('due_at'));
            $isLate = $late->isNotEmpty();
            $isTimeCritical = $timeCritical->isNotEmpty();
            $dueNow = $dueAt->lessThanOrEqualTo($now);

            return [
                'clientId' => $client->id,
                // Identity: what staff call them, and their full name, because
                // two people in one house can share a first name.
                'name' => $client->displayName(),
                'fullName' => $client->full_name,
                'room' => $client->room_name,

                'dueAt' => $dueAt->format('H:i'),
                // A COUNT, not a list. The medicines themselves are 2.1.
                'itemCount' => $forClient->count(),
                'outstandingCount' => $outstanding->count(),

                'timeSensitive' => $isTimeCritical,
                'late' => $isLate,
                'minutesLate' => $isLate ? $late->max(fn ($dose) => $dose->minutesLate($now)) : 0,

                // Progress, derived from what has been recorded.
                'progress' => match (true) {
                    $outstanding->isEmpty() => 'recorded',
                    $outstanding->count() < $forClient->count() => 'part_recorded',
                    default => 'not_started',
                },

                // Presence of a warning, not the warning itself.
                'hasSafetyWarning' => $client->allergies->contains(fn ($a) => $a->isCritical()),

                // Where they actually are. Not a reason to remove them from the
                // round — a reason the round needs to know about them.
                'clientStatus' => $client->status,
                'clientStatusWord' => $client->statusWord(),
                'available' => $client->isAvailable(),
                // The planned obligation is still outstanding and still needs an
                // explicit outcome. Section 2.3 will collect it.
                'needsOutcome' => ! $client->isAvailable() && $outstanding->isNotEmpty(),

                // How they are supported, which decides who does what.
                'support' => $this->supportFor($forClient),

                // Sorting only. Never rendered.
                //
                // Somebody who is not in the building sorts below everybody who
                // is. Their dose still has to be answered for, but nobody is
                // walking to a room they are not in, and putting them among the
                // people who are waiting would send staff to an empty flat.
                '_band' => ! $client->isAvailable() ? 6 : match (true) {
                    $isLate && $isTimeCritical => 1,
                    $isLate => 2,
                    $dueNow && $isTimeCritical => 3,
                    $dueNow => 4,
                    default => 5,
                },
                '_due' => $dueAt->timestamp,
            ];
        })->values()->all();

        usort($people, fn ($a, $b) => [$a['_band'], $a['_due']] <=> [$b['_band'], $b['_due']]);

        return array_map(function ($person) {
            unset($person['_band'], $person['_due']);

            return $person;
        }, $people);
    }

    /**
     * One word for how this person is supported in this round.
     *
     * Support type sits on the prescription because it varies by medicine —
     * Aisha manages her own inhaler and is given her epilepsy tablets. Where a
     * person's medicines in one round disagree, the honest answer is "mixed"
     * rather than picking one and being wrong about the other.
     */
    private function supportFor($doses): array
    {
        $types = $doses->map(fn ($dose) => $dose->prescription->support_type ?? 'staff_administered')
            ->unique()
            ->values();

        if ($types->count() === 1) {
            return [
                'type' => $types->first(),
                'word' => self::SUPPORT_WORDS[$types->first()] ?? $types->first(),
                'mixed' => false,
            ];
        }

        return [
            'type' => 'mixed',
            'word' => 'Mixed — see each medicine',
            'mixed' => true,
        ];
    }

    /**
     * The scheduled window this round actually covers.
     *
     * Read from the doses rather than assumed. A round whose doses all fall at
     * one minute is a single time and is said once — "08:00 to 08:00" is not a
     * window, it is the same number twice. A round genuinely spread across a
     * period reports its real first and last.
     *
     * Both cases come from COUNT(DISTINCT time); neither is special-cased for
     * the shape of today's fixture.
     */
    public function window(Round $round): array
    {
        $times = ScheduledDose::where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->pluck('due_at')
            ->map(fn ($due) => Carbon::parse($due)->format('H:i'))
            ->unique()
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return ['from' => null, 'to' => null, 'single' => false, 'label' => null];
        }

        $single = $times->count() === 1;

        return [
            'from' => $times->first(),
            'to' => $times->last(),
            'single' => $single,
            'label' => $single ? $times->first() : $times->first().' to '.$times->last(),
        ];
    }

    /** Round-level figures, counted from the same records. */
    public function progress(Round $round, ?Carbon $now = null): array
    {
        $people = $this->forRound($round, $now);

        return [
            'people' => count($people),
            'recorded' => count(array_filter($people, fn ($p) => $p['progress'] === 'recorded')),
            'remaining' => count(array_filter($people, fn ($p) => $p['progress'] !== 'recorded')),
            'late' => count(array_filter($people, fn ($p) => $p['late'])),
            'timeSensitive' => count(array_filter($people, fn ($p) => $p['timeSensitive'])),
            // Counted separately so "3 remaining" is not read as three people
            // standing in their doorways.
            'awayNeedingOutcome' => count(array_filter($people, fn ($p) => $p['needsOutcome'])),
        ];
    }
}
