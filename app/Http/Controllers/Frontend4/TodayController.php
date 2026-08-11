<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * frontend4 — Today.
 *
 * The frontline dashboard. It answers three questions and nothing else:
 * what is due, what is at risk, what do I do next.
 *
 * WHY IT REUSES THE EXISTING TRAIT
 * The dose derivation — is this dose overdue, due now, upcoming or complete —
 * comes from BuildsMedicationRound, the same code every other round screen in
 * this application uses. That is deliberate. A second implementation of "is
 * this dose overdue" would drift from the first, and two screens would then
 * disagree about a safety-critical fact.
 *
 * frontend4 is isolated in its FRONT END: its own layout, its own stylesheet,
 * its own bundle. It shares the database and the backend on purpose. See
 * docs/care-one-os/FRONTEND4/FRONTEND4-PLAN.md.
 *
 * KNOWN DUPLICATION, DELIBERATE FOR NOW
 * The *shaping* below (what counts as needing attention, how doses are grouped)
 * is written again here rather than shared with frontend3's Today, because
 * sharing it would mean editing a working front end to suit a new one. If both
 * front ends survive, this belongs in one service — noted in FRONTEND4.md.
 * The derivation, which is the part that would be dangerous to duplicate, is
 * NOT duplicated.
 *
 * This controller is read-only. It records nothing.
 */
class TodayController extends F4Controller
{
    use BuildsMedicationRound;

    /**
     * Outcomes that still need a human response.
     *
     * 'S' (asleep) is deliberately absent. The code already states the reason —
     * someone was asleep and was not woken. That is a recorded outcome, not an
     * open question. A *pattern* of asleep doses is a real concern, but it is a
     * trend for a manager, not a to-do for this shift.
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
     * instant it slips produces a list that mostly says "the round is running",
     * which teaches people to ignore the list.
     */
    private const OVERDUE_GRACE_MINUTES = 60;

    /** Beyond this many entries, the count is the message. */
    private const ATTENTION_CAP = 8;

    public function __construct()
    {
        // Middleware in the constructor is fine — it is not Inertia state, and
        // it runs as part of the pipeline it is registered into. The root view
        // is the thing that must not be set here; see F4Controller.
        $this->middleware(function ($request, $next) {
            if (! Auth::guard('frontend4')->check()) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->useF4Layout();

        // The role check, not a user-type check.
        //
        // This previously admitted N, M, A, CM and O — every user type there
        // is — which meant anyone who could log in could reach medication
        // management. It now refuses anyone whose access level maps to no
        // medication access, including finance accounts whose account type
        // happens to say admin. See App\Services\Frontend4\RoleResolver.
        $this->requirePermission(\App\Services\Frontend4\Permissions::VIEW_TODAY);

        $props = $this->buildRoundProps($request);
        $grid = $props['grid'] ?? [];
        $rounds = $props['rounds'] ?? [];
        $currentKey = $props['currentRound'] ?? 'morning';
        $current = $grid[$currentKey] ?? [];

        $summary = $this->summary($grid, $currentKey);

        return Inertia::render('Today', $this->roleProps() + [
            /*
             * Terminology, not labels.
             *
             * Frontend4 serves a care home, a children's home, supported living,
             * a domiciliary service or an individual, so no screen may hard-code
             * "resident". These are the neutral defaults; an organisation's own
             * wording replaces them once service-mode configuration exists.
             * Until then the page reads correctly everywhere rather than
             * correctly in one setting and oddly in the rest.
             */
            'terms' => [
                'person' => 'person',
                'people' => 'people',
                'place' => 'home',
                'team' => 'team',
            ],

            'place' => $props['home'] ?: 'Your home',
            'date' => $props['date'],
            'now' => now()->format('H:i'),
            'greeting' => $this->greeting(),
            'firstName' => explode(' ', trim((string) (Auth::user()->name ?? '')))[0] ?: null,
            'user' => Auth::user()->name ?? null,

            'round' => $this->roundMeta($rounds, $currentKey),
            'summary' => $summary,
            'dueNow' => $this->dueNow($current),
            'attention' => $this->attention($grid, $props['date']),
            'attentionCap' => self::ATTENTION_CAP,
            'upcoming' => $this->upcoming($current),
        ]);
    }

    /**
     * Find one round's metadata.
     *
     * `rounds` is a LIST of ['key','label','window'], not a map keyed by round.
     * Indexing it by the key yields null silently — the mistake that left the
     * round window blank on frontend3's Today for a while.
     */
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
     * A medicine label that does not repeat itself.
     *
     * Many prescriptions already carry the strength inside the name
     * ("Risperidone 500microgram tablets"), so appending the strength column
     * produces "Risperidone 500microgram tablets 500mcg".
     */
    private function medLabel(array $row): string
    {
        $name = trim((string) ($row['medication_name'] ?? 'Medicine'));
        $strength = trim((string) ($row['strength'] ?? ''));

        if ($strength === '') {
            return $name;
        }

        $norm = fn ($s) => preg_replace('/[^a-z0-9]/', '', strtolower($s));

        if (str_contains($norm($name), $norm($strength))) {
            return $name;
        }

        // Same number, different unit word — "500microgram" vs "500mcg".
        if (preg_match('/^(\d+(?:\.\d+)?)/', $strength, $m)) {
            $pattern = '/(?:^|[^0-9.])'.preg_quote($m[1], '/').'\s*(?:mcg|microgram|micrograms|mg|milligram|milligrams|g|ml|unit|units)/i';
            if (preg_match($pattern, $name)) {
                return $name;
            }
        }

        return $name.' '.$strength;
    }

    /**
     * Today's totals.
     *
     * Deduplicated by prescription + slot. When-required and unscheduled
     * medicines are deliberately repeated into every round by the trait, so
     * counting raw rows inflates every number on the page.
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

                    if (($row['status'] ?? null) === 'completed') {
                        $done++;
                    } elseif (! ($row['as_required'] ?? false)) {
                        // A when-required medicine is AVAILABLE, not outstanding.
                        // Nobody is behind because a PRN dose was not given.
                        $outstanding++;
                    }
                }
            }
        }

        $due = $overdue = $people = 0;
        foreach ($grid[$currentKey] ?? [] as $r) {
            $hasWork = false;
            foreach ($r['rows'] as $row) {
                if ($row['as_required'] ?? false) {
                    continue;
                }
                if (($row['status'] ?? null) === 'overdue') {
                    $overdue++;
                    $hasWork = true;
                } elseif (($row['status'] ?? null) === 'due_now') {
                    $due++;
                    $hasWork = true;
                }
            }
            if ($hasWork) {
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
     *
     * Finished people are kept and sorted to the bottom — seeing the round
     * finish is part of knowing where you are.
     */
    private function dueNow(array $residents): array
    {
        $out = [];

        foreach ($residents as $r) {
            $overdue = $due = $later = $done = 0;
            $earliest = null;

            foreach ($r['rows'] as $row) {
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

            if ($overdue + $due + $later + $done === 0) {
                continue; // nothing scheduled for this person in this round
            }

            $out[] = [
                'client_id' => $r['client_id'],
                'name' => $r['name'],
                'photo' => $r['photo'] ?? null,
                /*
                 * `meta` is assembled here, not in the component, and it is
                 * whatever this service actually uses. A room number appears
                 * only when there is one — assuming every person has a room is
                 * exactly the care-home default frontend4 must not bake in.
                 */
                'meta' => $r['room'] ? 'Room '.$r['room'] : null,
                'allergies' => array_values(array_filter((array) ($r['allergies'] ?? []))),
                'risks' => array_values(array_map(
                    fn ($f) => is_array($f) ? ($f['label'] ?? null) : $f,
                    (array) ($r['risk_flags'] ?? [])
                )),
                'overdue' => $overdue,
                'due' => $due,
                'later' => $later,
                'done' => $done,
                'outstanding' => $overdue + $due,
                'nextSlot' => $earliest,
                'state' => $overdue > 0 ? 'overdue' : (($overdue + $due) > 0 ? 'due' : 'done'),
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
     * THE RULE: one entry per problem, not one per dose.
     *
     * Listing every affected dose separately produces a list of twenty that is
     * mostly the same medicine repeated once per time slot. A list of twenty is
     * a list nobody reads, and an attention list that cries wolf is worse than
     * no list at all. So:
     *   · grouped by person + medicine + kind, with a dose count
     *   · a medicine low on stock is ONE supply problem, not one per slot
     *   · a dose must be meaningfully late before it counts
     *   · supply and outcome problems rank above overdue, because they need a
     *     different decision — chase a pharmacy, respond to a refusal. Overdue
     *     needs one thing: go and do the round. If overdue sorted first it
     *     would flood the list and bury everything genuinely different.
     */
    private function attention(array $grid, string $date): array
    {
        $groups = [];
        $seenDose = [];

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
                    // so the same dose arrives several times.
                    $doseKey = $row['mar_sheet_id'].'|'.($row['slot'] ?? 'prn');
                    if (isset($seenDose[$doseKey])) {
                        continue;
                    }
                    $seenDose[$doseKey] = true;

                    $code = $row['code'] ?? null;
                    $med = $this->medLabel($row);
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
                            'name' => $r['name'],
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
                            'name' => $r['name'],
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

                        $add($base.'|overdue', [
                            'kind' => 'overdue',
                            'tone' => 'risk',
                            'label' => 'Overdue',
                            'name' => $r['name'],
                            'detail' => $med,
                            'note' => $lateBy !== null ? $this->humanLate($lateBy) : 'not recorded',
                        ], $slot);
                    }
                }
            }
        }

        $items = array_values($groups);

        $rank = ['supply' => 0, 'outcome' => 1, 'overdue' => 2];
        usort($items, fn ($a, $b) => [
            $rank[$a['kind']], $a['tone'] === 'risk' ? 0 : 1, -$a['count'], $a['at'] ?? '99:99',
        ] <=> [
            $rank[$b['kind']], $b['tone'] === 'risk' ? 0 : 1, -$b['count'], $b['at'] ?? '99:99',
        ]);

        return $items;
    }

    /** "1h 20m late" — a duration someone can act on, not a raw number. */
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
