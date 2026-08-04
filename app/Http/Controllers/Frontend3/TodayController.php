<?php

namespace App\Http\Controllers\Frontend3;

use App\Http\Controllers\Frontend3\Concerns\LabelsMedicines;
use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * frontend3 — Today.
 *
 * The frontline dashboard from spec §5. Answers three questions and nothing
 * else: what is due, what is at risk, what do I do next.
 *
 * WHY IT REUSES THE EXISTING TRAIT
 * The dose derivation (overdue / due now / upcoming / completed) comes from
 * BuildsMedicationRound — the same code the existing round pages use. That is
 * deliberate. A second implementation of "is this dose overdue" would drift
 * from the first, and the two screens would disagree about a safety-critical
 * fact. frontend3 is isolated in its FRONT END (own layout, own theme, own
 * scoped CSS). It shares the backend on purpose.
 *
 * This controller is read-only. It records nothing.
 */
class TodayController extends F3Controller
{
    use BuildsMedicationRound;
    use LabelsMedicines;

    /** Same access rule as the existing medication pages. */
    private const ALLOWED_USER_TYPES = ['N', 'M', 'A', 'CM', 'O'];

    /**
     * Outcome codes that need a human response.
     *
     * 'S' (asleep) is deliberately absent. The server does not even require a
     * reason for it, because the code already states the reason — a resident
     * was asleep and was not woken. It is a recorded outcome, not an open
     * question. Listing it here made the attention list longer without making
     * anyone safer. (A *pattern* of asleep doses is a real concern, but that is
     * a trend for the manager view, not a to-do for this shift.)
     */
    private const ATTENTION_CODES = [
        'R' => 'Refused',
        'W' => 'Withheld',
        'N' => 'Not available',
        'O' => 'Other outcome',
    ];

    /**
     * How late a dose must be before it counts as needing attention.
     *
     * A dose is not a problem the moment its window closes — the round is in
     * progress and someone is working through it. Flagging every dose the
     * instant it slips produces a list that is mostly just "the round is
     * running", which trains people to ignore the list entirely.
     */
    private const OVERDUE_GRACE_MINUTES = 60;

    /** Never show more than this. Beyond it, the count is the message. */
    private const ATTENTION_CAP = 8;

    /**
     * When this many medicines are overdue in the SAME round, that is one
     * problem — the round was not recorded — not N separate problems.
     *
     * Listing them individually buries the things that need a different
     * response (a refusal, a supply failure) under a wall of identical rows.
     */
    private const OVERDUE_CLUSTER = 3;

    public function __construct()
    {
        // Registering middleware in the constructor is fine — it is not Inertia
        // state, and it runs as part of the pipeline it is registered into.
        $this->middleware(function ($request, $next) {
            if (! Auth::check() || ! in_array(Auth::user()->user_type, self::ALLOWED_USER_TYPES, true)) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->useF3Layout();

        $props = $this->buildRoundProps($request);
        $grid = $props['grid'] ?? [];
        $rounds = $props['rounds'] ?? [];
        $currentKey = $props['currentRound'] ?? 'morning';

        return Inertia::render('Today', [
            'home' => $props['home'] ?: 'Your home',
            'date' => $props['date'],
            'now' => now()->format('H:i'),
            'greeting' => $this->greeting(),
            'firstName' => explode(' ', trim((string) (Auth::user()->name ?? '')))[0] ?: null,
            // `rounds` is a LIST of ['key','label','window'] — not keyed by round.
            // Indexing it by the round key silently yields null, which is why the
            // round window never appeared on this page until 2026-08-04.
            'round' => $this->roundMeta($rounds, $currentKey),
            'summary' => $this->summary($grid, $currentKey),
            'dueNow' => $this->dueNow($grid[$currentKey] ?? []),
            'attention' => $this->attention($grid, $props['date'], $rounds),
            'attentionCap' => self::ATTENTION_CAP,
            'upcoming' => $this->upcoming($grid[$currentKey] ?? []),
            'roundUrl' => route('frontend3.round'),
        ]);
    }

    /** Find one round's metadata in the trait's list-shaped `rounds` payload. */
    private function roundMeta(array $rounds, string $key): array
    {
        foreach ($rounds as $r) {
            if (($r['key'] ?? null) === $key) {
                return ['key' => $key, 'label' => $r['label'], 'window' => $r['window'] ?? null];
            }
        }

        return ['key' => $key, 'label' => ucfirst($key), 'window' => null];
    }

    private function greeting(): string
    {
        $h = (int) now()->format('H');

        return $h < 12 ? 'Good morning' : ($h < 18 ? 'Good afternoon' : 'Good evening');
    }

    /**
     * Today's totals across every round.
     *
     * Deduplicated by prescription + slot, because PRN and unscheduled
     * medicines are deliberately repeated into every round by the trait —
     * counting the raw rows would inflate every number on the page.
     */
    private function summary(array $grid, string $currentKey): array
    {
        $seen = [];
        $done = $outstanding = 0;

        foreach ($grid as $residents) {
            foreach ($residents as $r) {
                foreach ($r['rows'] as $row) {
                    $key = $row['mar_sheet_id'].'|'.($row['slot'] ?? 'prn');
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    // PRN has no scheduled time, so it is never "outstanding".
                    if (($row['status'] ?? null) === 'completed') {
                        $done++;
                    } elseif (! ($row['as_required'] ?? false)) {
                        $outstanding++;
                    }
                }
            }
        }

        $current = $grid[$currentKey] ?? [];
        $due = $overdue = 0;
        $people = 0;
        foreach ($current as $r) {
            $personHasWork = false;
            foreach ($r['rows'] as $row) {
                // A when-required medicine is AVAILABLE, not outstanding. Nobody
                // is behind because a PRN dose has not been given. The round page
                // uses the same rule, so the two screens cannot disagree about
                // how much work is left.
                if ($row['as_required'] ?? false) {
                    continue;
                }
                $s = $row['status'] ?? null;
                if ($s === 'overdue') {
                    $overdue++;
                    $personHasWork = true;
                } elseif ($s === 'due_now') {
                    $due++;
                    $personHasWork = true;
                }
            }
            if ($personHasWork) {
                $people++;
            }
        }

        $total = $done + $outstanding;

        return [
            'due' => $due,
            'overdue' => $overdue,
            'people' => $people,
            'completedToday' => $done,
            'scheduledToday' => $total,
            'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 100,
        ];
    }

    /**
     * People with something outstanding in the current round, most urgent first.
     * Completed people are kept but sorted to the bottom — seeing the round
     * finish is part of knowing where you are.
     */
    private function dueNow(array $residents): array
    {
        $out = [];

        foreach ($residents as $r) {
            $overdue = $due = $later = $done = 0;
            $earliest = null;

            foreach ($r['rows'] as $row) {
                // Same rule as summary() and the round page: when-required
                // medicines are available, not outstanding.
                if ($row['as_required'] ?? false) {
                    continue;
                }
                switch ($row['status'] ?? null) {
                    case 'overdue':   $overdue++; break;
                    case 'due_now':   $due++; break;
                    case 'completed': $done++; break;
                    default:          $later++;
                }
                if (! empty($row['slot']) && ($row['status'] ?? null) !== 'completed') {
                    if ($earliest === null || $row['slot'] < $earliest) {
                        $earliest = $row['slot'];
                    }
                }
            }

            $outstanding = $overdue + $due;
            if ($outstanding === 0 && $done === 0 && $later === 0) {
                continue; // nothing scheduled for this person in this round
            }

            $out[] = [
                'client_id' => $r['client_id'],
                'name' => $r['name'],
                'photo' => $r['photo'] ?? null,
                'room' => $r['room'] ?? null,
                'allergies' => array_values(array_filter((array) ($r['allergies'] ?? []))),
                'risks' => array_values(array_filter((array) ($r['risk_flags'] ?? []))),
                'overdue' => $overdue,
                'due' => $due,
                'later' => $later,
                'done' => $done,
                'outstanding' => $outstanding,
                'nextSlot' => $earliest,
                'state' => $overdue > 0 ? 'overdue' : ($outstanding > 0 ? 'due' : 'done'),
            ];
        }

        usort($out, function ($a, $b) {
            $rank = ['overdue' => 0, 'due' => 1, 'done' => 2];

            return [$rank[$a['state']], $a['nextSlot'] ?? '99:99', $a['name']]
                <=> [$rank[$b['state']], $b['nextSlot'] ?? '99:99', $b['name']];
        });

        return $out;
    }

    /**
     * Things a person must respond to.
     *
     * The rule: **one entry per problem, not one per dose.**
     *
     * The first version of this listed every affected dose separately and
     * flagged anything a minute past its window. On real data that produced 19
     * entries — mostly the same medicine repeated once per time slot, plus
     * doses that were merely mid-round. A list of 19 is a list nobody reads,
     * and an attention list that cries wolf is worse than no list at all.
     *
     * So now:
     *   · grouped by person + medicine + kind, with a dose count
     *   · a medicine low on stock is ONE supply problem, not one per slot
     *   · a dose must be {@see OVERDUE_GRACE_MINUTES} late before it counts
     *   · 'asleep' is an outcome, not an open question (see ATTENTION_CODES)
     *   · capped, with the overflow stated rather than hidden
     */
    private function attention(array $grid, string $date, array $rounds): array
    {
        $groups = [];
        $overdue = [];
        $seenDose = [];

        $roundLabels = [];
        foreach ($rounds as $r) {
            $roundLabels[$r['key']] = $r['label'];
        }

        $isToday = $date === now()->toDateString();
        $nowMin = ((int) now()->format('H') * 60) + (int) now()->format('i');

        $add = function (string $key, array $item, ?string $at) use (&$groups) {
            if (! isset($groups[$key])) {
                $groups[$key] = $item + ['count' => 0, 'at' => $at];
            }
            $groups[$key]['count']++;
            // Keep the earliest time — that is when the problem started.
            if ($at !== null && ($groups[$key]['at'] === null || $at < $groups[$key]['at'])) {
                $groups[$key]['at'] = $at;
            }
        };

        foreach ($grid as $residents) {
            foreach ($residents as $r) {
                foreach ($r['rows'] as $row) {
                    // The trait repeats unscheduled medicines into every round,
                    // so the same dose can arrive several times.
                    $doseKey = $row['mar_sheet_id'].'|'.($row['slot'] ?? 'prn');
                    if (isset($seenDose[$doseKey])) {
                        continue;
                    }
                    $seenDose[$doseKey] = true;

                    $code = $row['code'] ?? null;
                    $med = $this->medLabel($row);
                    $person = $r['name'];
                    $base = $row['mar_sheet_id'].'|'.$r['client_id'];

                    // 1. Supply. Belongs to the MEDICINE, so it is counted once
                    //    however many times a day that medicine is given.
                    if (! empty($row['low_stock'])) {
                        $stock = $row['stock'];
                        $out = ($stock !== null && $stock <= 0);
                        $add($base.'|supply', [
                            'kind' => 'supply',
                            'tone' => $out ? 'risk' : 'caution',
                            'label' => $out ? 'Out of stock' : 'Low stock',
                            'name' => $person,
                            'detail' => $med,
                            'note' => $stock !== null
                                ? trim($stock.' '.($row['unit'] ?: '')).' left'
                                : null,
                        ], null);
                        // A supply problem does not stop the dose also being
                        // refused or overdue — fall through.
                    }

                    // 2. Recorded as not administered. Needs a follow-up decision.
                    if ($code && isset(self::ATTENTION_CODES[$code])) {
                        $add($base.'|'.$code, [
                            'kind' => 'outcome',
                            'tone' => $code === 'N' ? 'risk' : 'caution',
                            'label' => self::ATTENTION_CODES[$code],
                            'name' => $person,
                            'detail' => $med,
                            'note' => $row['reason'] ?? null,
                        ], $row['slot'] ?? null);

                        continue;
                    }

                    // 3. Overdue — but only once it is meaningfully late.
                    if (($row['status'] ?? null) === 'overdue' && ! $code) {
                        $slot = $row['slot'] ?? null;
                        $lateBy = null;

                        if ($isToday && $slot && str_contains($slot, ':')) {
                            [$h, $m] = explode(':', $slot);
                            $lateBy = $nowMin - (((int) $h * 60) + (int) $m);
                            if ($lateBy < self::OVERDUE_GRACE_MINUTES) {
                                continue; // still mid-round, not a problem yet
                            }
                        }

                        // Held per round, so a whole unrecorded round can be
                        // reported as the single problem it actually is.
                        $rk = $this->roundForTime($slot);
                        $overdue[$rk]['label'] ??= $roundLabels[$rk] ?? ucfirst($rk);
                        $overdue[$rk]['at'] = min($overdue[$rk]['at'] ?? '99:99', $slot ?? '99:99');
                        $overdue[$rk]['lateBy'] = max($overdue[$rk]['lateBy'] ?? 0, $lateBy ?? 0);
                        $overdue[$rk]['people'][$r['client_id']] = true;
                        $overdue[$rk]['items'][$base] ??= [
                            'kind' => 'overdue',
                            'tone' => 'risk',
                            'label' => 'Overdue',
                            'name' => $person,
                            'detail' => $med,
                            'note' => $lateBy !== null ? $this->humanLate($lateBy) : 'not recorded',
                            'count' => 0,
                            'at' => $slot,
                        ];
                        $overdue[$rk]['items'][$base]['count']++;
                    }
                }
            }
        }

        $items = array_values($groups);

        // A whole round left unrecorded is ONE problem, reported as one line.
        // Below the cluster threshold, the individual medicines are the problem.
        foreach ($overdue as $round) {
            if (count($round['items']) >= self::OVERDUE_CLUSTER) {
                $doses = array_sum(array_column($round['items'], 'count'));
                $people = count($round['people']);
                $items[] = [
                    'kind' => 'overdue',
                    'tone' => 'risk',
                    'label' => $round['label'].' round not recorded',
                    'name' => "{$doses} ".($doses === 1 ? 'dose' : 'doses')
                        ." across {$people} ".($people === 1 ? 'person' : 'people'),
                    'detail' => count($round['items']).' medicines with no outcome recorded',
                    'note' => $this->humanLate((int) $round['lateBy']),
                    'count' => $doses,
                    'at' => $round['at'] === '99:99' ? null : $round['at'],
                ];

                continue;
            }

            foreach ($round['items'] as $item) {
                $items[] = $item;
            }
        }

        /*
         * Ranking: supply and recorded-outcome problems come BEFORE overdue.
         *
         * They need a distinct decision — chase a pharmacy, respond to a
         * refusal. Overdue needs one thing: go and do the round. If overdue
         * sorts first it floods the list and hides everything that is genuinely
         * different, which is exactly how an attention list stops being read.
         */
        $rank = ['supply' => 0, 'outcome' => 1, 'overdue' => 2];
        usort($items, fn ($a, $b) => [
            $rank[$a['kind']], $a['tone'] === 'risk' ? 0 : 1, -$a['count'], $a['at'] ?? '99:99',
        ] <=> [
            $rank[$b['kind']], $b['tone'] === 'risk' ? 0 : 1, -$b['count'], $b['at'] ?? '99:99',
        ]);

        return $items;
    }

    /** "1h 20m late" — a duration a person can act on, not a raw number. */
    private function humanLate(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.'m late';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $m > 0 ? "{$h}h {$m}m late" : "{$h}h late";
    }

    /** Later in this round, grouped by time. A preview, not a work list. */
    private function upcoming(array $residents): array
    {
        $bySlot = [];

        foreach ($residents as $r) {
            foreach ($r['rows'] as $row) {
                if (! in_array($row['status'] ?? null, ['upcoming', 'later'], true)) {
                    continue;
                }
                $slot = $row['slot'] ?? null;
                if (! $slot) {
                    continue;
                }
                $bySlot[$slot] ??= ['slot' => $slot, 'doses' => 0, 'people' => []];
                $bySlot[$slot]['doses']++;
                $bySlot[$slot]['people'][$r['client_id']] = true;
            }
        }

        ksort($bySlot);

        return array_values(array_map(fn ($s) => [
            'slot' => $s['slot'],
            'doses' => $s['doses'],
            'people' => count($s['people']),
        ], $bySlot));
    }
}
