<?php

namespace App\Http\Controllers\Frontend4;

use App\Http\Controllers\frontEnd\Medication\Concerns\BuildsMedicationRound;
use App\Services\Frontend4\Outcomes;
use App\Services\Staff\MARSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * frontend4 — the medication round.
 *
 * The main screen. A carer opens it at the start of a round: who is due now,
 * who is overdue, who is done. They choose a person, see that person's
 * medicines, and record what happened to each one.
 *
 * SCOPE — M2 ONLY
 * The queue, the person's medicines, and recording an outcome. Deliberately NOT
 * here yet, on the owner's instruction and the specification's: when-required
 * (PRN) medicines as their own section, controlled-drug witnessing, stock
 * deduction shown in the interface, and end-of-round sign-off. Those arrive one
 * at a time in M3 so the page stays possible to judge.
 *
 * Note that the SERVER still enforces the rules for those things. A controlled
 * drug recorded without a witness is refused by applyRecord() — this page shows
 * that refusal in the carer's own words rather than pretending it cannot happen.
 *
 * The dose derivation and the recording both come from BuildsMedicationRound —
 * the same code every other round screen in this application uses. A second
 * implementation of "is this dose overdue" would drift, and two screens
 * disagreeing about a safety-critical fact is exactly the failure to avoid.
 */
class RoundController extends F4Controller
{
    use BuildsMedicationRound;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! Auth::guard('frontend4')->check()) {
                abort(403, 'You do not have access to medication management.');
            }

            return $next($request);
        });
    }

    /** The round: a queue of people, and one person's medicines beside it. */
    public function index(Request $request)
    {
        $this->useF4Layout();
        $this->requireMedicationAccess();

        $props = $this->buildRoundProps($request);
        $rounds = $props['rounds'] ?? [];
        $grid = $props['grid'] ?? [];

        // The round being worked on. A carer can look at another round — the
        // evening dose someone is preparing for — so this is a request value,
        // defaulting to the one we are actually in.
        $roundKey = (string) $request->input('round', $props['currentRound'] ?? 'morning');
        if (! array_key_exists($roundKey, $grid)) {
            $roundKey = $props['currentRound'] ?? 'morning';
        }

        $residents = $grid[$roundKey] ?? [];
        $queue = $this->queue($residents);

        // Which person is open. Defaults to the first person who still has work,
        // so a carer starting a round lands on someone rather than an empty
        // panel and one more tap.
        $selectedId = $request->input('client');
        $selectedId = $selectedId !== null ? (int) $selectedId : ($queue[0]['client_id'] ?? null);

        $outcomes = app(Outcomes::class);

        return Inertia::render('Round', $this->roleProps() + [
            'terms' => ['person' => 'client', 'people' => 'clients', 'place' => 'home'],

            'place' => $props['home'] ?: 'Your home',
            'date' => $props['date'],
            'now' => now()->format('H:i'),
            'user' => Auth::user()->name ?? null,

            'rounds' => $rounds,
            'round' => $this->roundMeta($rounds, $roundKey),
            'closure' => $props['closures'][$roundKey] ?? null,

            'queue' => $queue,
            'selectedClientId' => $selectedId,
            'selected' => $this->person($residents, $selectedId),
            'progress' => $this->progress($residents),

            // The outcome vocabulary travels with the page so no screen can
            // invent its own wording, and so a stored code is never shown bare.
            'outcomes' => $outcomes->forClient(),

            'recordUrl' => route('frontend4.round.record'),
        ]);
    }

    /**
     * Record one dose.
     *
     * Everything that makes this safe — the mandatory reason, the row lock
     * against a double tap, the PRN maximum and interval, the controlled-drug
     * witness, the stock movement — happens inside applyRecord(). This method
     * deliberately adds none of its own, because a second set of rules beside
     * the shared ones is how two screens start disagreeing.
     *
     * A refusal throws a ValidationException, which Inertia returns as an error
     * on the field. The page shows the server's own message.
     */
    public function record(Request $request, MARSheetService $marSheetService)
    {
        $this->requirePermission(\App\Services\Frontend4\Permissions::RECORD_ADMINISTRATION);

        $this->applyRecord($request, $marSheetService);

        return redirect()->route('frontend4.round', [
            'date' => $request->input('date'),
            'round' => $request->input('round'),
            'client' => $request->input('client'),
        ])->with('success', 'Recorded.');
    }

    /** One round's metadata. `rounds` is a LIST, not a map keyed by round. */
    private function roundMeta(array $rounds, string $key): array
    {
        foreach ($rounds as $r) {
            if (($r['key'] ?? null) === $key) {
                return ['key' => $key, 'label' => $r['label'], 'window' => $r['window'] ?? null];
            }
        }

        return ['key' => $key, 'label' => ucfirst($key), 'window' => null];
    }

    /**
     * The people queue, most urgent first.
     *
     * Grouped by state rather than sorted alphabetically, because a round is
     * worked in order of what is due — the specification is explicit that the
     * list groups by time window, not by alphabet alone. Finished people stay
     * on the list, at the bottom: watching the round empty out is how a carer
     * knows where they are.
     */
    private function queue(array $residents): array
    {
        $out = [];

        foreach ($residents as $r) {
            $overdue = $due = $later = $done = 0;
            $earliest = null;
            $needsAttention = false;

            foreach ($r['rows'] as $row) {
                // When-required medicines are AVAILABLE, not outstanding —
                // nobody is behind because a PRN dose was not given. They get
                // their own section in M3.
                if ($row['as_required'] ?? false) {
                    continue;
                }

                switch ($row['status'] ?? null) {
                    case 'overdue':   $overdue++; break;
                    case 'due_now':   $due++; break;
                    case 'completed': $done++; break;
                    default:          $later++;
                }

                // A recorded non-administration, or a supply problem, is
                // something a person has to respond to — not just a dose left.
                if (in_array($row['code'] ?? null, ['R', 'W', 'N', 'O', 'OP', 'VO'], true)) {
                    $needsAttention = true;
                }
                if (! empty($row['low_stock'])) {
                    $needsAttention = true;
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

            $outstanding = $overdue + $due;

            $out[] = [
                'client_id' => $r['client_id'],
                'name' => $r['name'],
                'photo' => $r['photo'] ?? null,
                // Only present when the service actually records one. Assuming
                // everyone has a room is the care-home default frontend4 must
                // not bake in — in domiciliary care there is no room.
                'meta' => $r['room'] ? 'Room '.$r['room'] : null,
                'allergies' => array_values(array_filter((array) ($r['allergies'] ?? []))),
                'overdue' => $overdue,
                'due' => $due,
                'later' => $later,
                'done' => $done,
                'outstanding' => $outstanding,
                'total' => $overdue + $due + $later + $done,
                'nextSlot' => $earliest,
                'needsAttention' => $needsAttention,
                'state' => $overdue > 0 ? 'overdue'
                    : ($outstanding > 0 ? 'due'
                    : ($later > 0 ? 'upcoming' : 'given')),
            ];
        }

        usort($out, function ($a, $b) {
            $rank = ['overdue' => 0, 'due' => 1, 'upcoming' => 2, 'given' => 3];

            return [$rank[$a['state']], $a['nextSlot'] ?? '99:99', $a['name']]
                <=> [$rank[$b['state']], $b['nextSlot'] ?? '99:99', $b['name']];
        });

        return $out;
    }

    /**
     * One person, with their medicines for this round.
     *
     * Everything the specification asks to be visible while a dose is being
     * given: who they are, what they are allergic to, and for each medicine the
     * dose, route, time, instructions, why it is prescribed and what is left in
     * stock.
     */
    private function person(array $residents, ?int $clientId): ?array
    {
        if ($clientId === null) {
            return null;
        }

        foreach ($residents as $r) {
            if ((int) $r['client_id'] !== $clientId) {
                continue;
            }

            $outcomes = app(Outcomes::class);
            $medicines = [];
            $prnMedicines = [];

            foreach ($r['rows'] as $row) {
                $payload = [
                    'mar_sheet_id' => $row['mar_sheet_id'],
                    'name' => $row['medication_name'],
                    'strength' => $row['strength'],
                    'form' => $row['form'] ?? null,
                    'dose' => $row['dose'],
                    'route' => $row['route'],
                    'slot' => $row['slot'],
                    'status' => $row['status'],
                    // Kept apart on purpose: `instruction` is how to give it,
                    // `indication` is why it is prescribed. Merging them let an
                    // indication render as a directive, which is a real hazard.
                    'instruction' => $row['instruction'],
                    'indication' => $row['indication'],
                    'stock' => $row['stock'],
                    'unit' => $row['unit'],
                    'lowStock' => (bool) ($row['low_stock'] ?? false),
                    'isControlled' => (bool) ($row['is_controlled'] ?? false),
                    // What was recorded, if anything — as a full label, never a
                    // bare letter.
                    'code' => $row['code'],
                    'outcome' => $outcomes->label($row['code']),
                    'outcomeStatus' => $row['code'] ? $outcomes->status($row['code']) : null,
                    'reason' => $row['reason'],
                    'notes' => $row['notes'],
                    'recordedAt' => $row['recorded_at'],
                    'recordedBy' => $row['recorded_by'],
                ];

                if ($row['as_required'] ?? false) {
                    $payload['prn'] = $row['prn'] ?? null;
                    $prnMedicines[] = $payload;
                    continue;
                }

                $medicines[] = $payload;
            }

            usort($medicines, fn ($a, $b) => [$a['slot'] ?? '99:99', $a['name']] <=> [$b['slot'] ?? '99:99', $b['name']]);
            usort($prnMedicines, fn ($a, $b) => [$a['name']] <=> [$b['name']]);

            return [
                'client_id' => $r['client_id'],
                'name' => $r['name'],
                'photo' => $r['photo'] ?? null,
                'dob' => $r['dob'] ?? null,
                'meta' => $r['room'] ? 'Room '.$r['room'] : null,
                'allergies' => array_values(array_filter((array) ($r['allergies'] ?? []))),
                'risks' => array_values(array_filter(array_map(
                    fn ($f) => is_array($f) ? ($f['label'] ?? null) : $f,
                    (array) ($r['risk_flags'] ?? [])
                ))),
                'medicines' => $medicines,
                'prnMedicines' => $prnMedicines,
            ];
        }

        return null;
    }

    /** How far through the round: medicines and people, both stated. */
    private function progress(array $residents): array
    {
        $doses = $recorded = 0;
        $people = $peopleDone = 0;

        foreach ($residents as $r) {
            $has = false;
            $allDone = true;

            foreach ($r['rows'] as $row) {
                if ($row['as_required'] ?? false) {
                    continue;
                }
                $has = true;
                $doses++;
                if (($row['status'] ?? null) === 'completed') {
                    $recorded++;
                } else {
                    $allDone = false;
                }
            }

            if ($has) {
                $people++;
                if ($allDone) {
                    $peopleDone++;
                }
            }
        }

        return [
            'doses' => $doses,
            'recorded' => $recorded,
            'outstanding' => $doses - $recorded,
            'people' => $people,
            'peopleDone' => $peopleDone,
            'percent' => $doses > 0 ? (int) round(($recorded / $doses) * 100) : 100,
        ];
    }
}
