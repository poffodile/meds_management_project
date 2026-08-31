<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\CdBalance;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Service;
use App\Models\Record7\StockAttempt;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockMovement;
use App\Models\Record7\User;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use App\Services\Record7\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use RuntimeException;

/**
 * Section 2.7 — what is actually in the cupboard, and what it does not agree with.
 *
 * NOTHING FROM THE BROWSER IS TRUSTED. The house comes from the session, the
 * balance from the house, the movement from the balance. A number in a URL is a
 * number somebody typed, and every lookup is scoped before the id is used, so a
 * crafted id finds nothing rather than being rejected — which also means the
 * reply cannot be used to discover that it exists.
 *
 * THREE DIFFERENT AUTHORITIES, DELIBERATELY NOT ONE.
 *   stock_management  custody: opening a balance, booking a delivery in,
 *                     counting, disposing, putting back, setting a reorder level
 *   reconciliation    declaring what is true when the ledger and the cupboard
 *                     disagree — and nothing else
 *   correction_approval  approving that declaration, on the manager screen
 *
 * A stock manager cannot erase a discrepancy. That is the point of the split.
 */
class StockController extends R7Controller
{
    public function __construct(
        private readonly SessionManager $sessions,
        private readonly AccessPolicy $policy,
        private readonly StockLedger $ledger,
        private readonly AuditRecorder $audit,
    ) {
    }

    /* ── Reading ─────────────────────────────────────────────────────────── */

    /** Everything this house counts, exceptions first. */
    public function index(Request $request)
    {
        $this->useR7Layout($request);
        $this->requirePermission($request, 'view_dashboard');

        [$user, $serviceId] = $this->context($request);
        $house = Service::find($serviceId);

        $balances = StockBalance::with(['client', 'medicine', 'threshold'])
            ->where('service_id', $serviceId)
            ->get()
            ->map(fn (StockBalance $b) => $this->describe($b))
            ->sortBy(fn ($row) => [
                $row['discrepancies'] > 0 ? 0 : 1,
                $row['out'] ? 0 : ($row['low'] ? 1 : 2),
                $row['person'],
                $row['medicine'],
            ])
            ->values()->all();

        return Inertia::render('Stock', [
            'house' => ['id' => $house?->id, 'name' => $house?->name],
            'balances' => $balances,

            // READ ONLY, AND FROM SECTION 2.5. Shown so a manager can see the
            // whole cupboard in one place; there is no write path to it here
            // and the screen says which register owns it.
            'controlled' => $this->controlledView($serviceId),

            'unquantified' => $this->unquantified($serviceId),

            'can' => [
                'manageStock' => $this->policy->allows($user, 'stock_management', $serviceId),
                'reconcile' => $this->policy->allows($user, 'reconciliation', $serviceId),
            ],
            'stage' => 'Section 2.7 — stock, reconciliation and corrections.',
            'urls' => $this->urls(),
        ]);
    }

    /** One balance, its history and its unresolved disagreements. */
    public function show(Request $request, int $balance)
    {
        $this->useR7Layout($request);
        $this->requirePermission($request, 'view_dashboard');

        [$user, $serviceId] = $this->context($request);
        $row = $this->balance($serviceId, $balance);

        $unresolved = $this->ledger->unresolvedDiscrepancies($row);

        return Inertia::render('StockBalance', [
            'house' => ['id' => $serviceId, 'name' => Service::find($serviceId)?->name],
            'balance' => $this->describe($row),
            'history' => $this->ledger->history($row),
            'unresolved' => $unresolved->map(fn (StockMovement $m) => [
                'id' => $m->id,
                'cause' => $m->discrepancyCause(),
                'expected' => $m->expected_quantity !== null ? $this->ledger->tidy($m->expected_quantity) : null,
                'counted' => $m->counted_quantity !== null ? $this->ledger->tidy($m->counted_quantity) : null,
                'difference' => $m->difference(),
                'balanceAfter' => $this->ledger->tidy($m->balance_after),
                'at' => $m->occurred_at?->format('H:i \o\n j F'),
                'by' => $m->recordedBy?->displayName(),
                'approved' => $this->approvalFor($m, $serviceId)?->id,
                'requestedDelta' => $this->approvalFor($m, $serviceId)?->requested_quantity_delta,
                'pending' => $this->pendingRequestFor($m, $serviceId) !== null,
            ])->values()->all(),

            // Server-issued, and the only way a receipt or a count can be
            // recorded. A browser may hand one back; it may never mint one.
            'tokens' => $this->policy->allows($user, 'stock_management', $serviceId)
                ? [
                    'receipt' => $this->mintToken($user, $row, 'receipt'),
                    'count' => $this->mintToken($user, $row, 'stock_check'),
                    'opening' => $row->last_sequence_no === 0
                        ? $this->mintToken($user, $row, 'opening_balance')
                        : null,
                ]
                : [],

            'can' => [
                'manageStock' => $this->policy->allows($user, 'stock_management', $serviceId),
                'reconcile' => $this->policy->allows($user, 'reconciliation', $serviceId),
            ],
            'stage' => 'Section 2.7 — one balance.',
            'urls' => $this->urls() + [
                'stock' => route('record7.stock'),
                'opening' => route('record7.stock.opening', $row->id),
                'receipt' => route('record7.stock.receipt', $row->id),
                'count' => route('record7.stock.count', $row->id),
                'threshold' => route('record7.stock.threshold', $row->id),
            ],
        ]);
    }

    /* ── Custody ─────────────────────────────────────────────────────────── */

    /** The first count in a house: what is there, before anything moves. */
    public function open(Request $request, int $balance)
    {
        return $this->custody($request, $balance, 'opening_balance', function ($row, $user, $house, $input) {
            return $this->ledger->record(
                balance: $row,
                snapshot: $this->snapshotOf($row),
                action: 'opening_balance',
                quantities: ['received' => $this->quantity($input, 'quantity')],
                user: $user,
                client: $row->client,
                house: $house,
                notes: $input['note'] ?? null,
            );
        }, 'stock_opening_balance_recorded');
    }

    /** A delivery, booked in. */
    public function receipt(Request $request, int $balance)
    {
        return $this->custody($request, $balance, 'receipt', function ($row, $user, $house, $input) {
            return $this->ledger->record(
                balance: $row,
                snapshot: $this->snapshotOf($row),
                action: 'receipt',
                quantities: ['received' => $this->quantity($input, 'quantity')],
                user: $user,
                client: $row->client,
                house: $house,
                notes: $input['note'] ?? null,
            );
        }, 'stock_received');
    }

    /**
     * A physical count.
     *
     * IT OBSERVES; IT DOES NOT CORRECT. The balance does not move, whatever the
     * count says. Where the two disagree both figures are kept side by side for
     * ever and a discrepancy opens — and only an approved correction can settle
     * it. That is what stops a count being used to make an awkward figure go
     * away, and it is what gives the reconciliation workflow its meaning.
     */
    public function count(Request $request, int $balance)
    {
        return $this->custody($request, $balance, 'stock_check', function ($row, $user, $house, $input) {
            return $this->ledger->record(
                balance: $row,
                snapshot: $this->snapshotOf($row),
                action: 'stock_check',
                quantities: ['counted' => $this->quantity($input, 'counted', allowZero: true)],
                user: $user,
                client: $row->client,
                house: $house,
                notes: $input['note'] ?? null,
            );
        }, 'stock_counted');
    }

    /** What "low" means here — or that nobody has said. */
    public function threshold(Request $request, int $balance)
    {
        [$user, $serviceId] = $this->context($request);
        $this->must($user, $serviceId, 'stock_management');

        $row = $this->balance($serviceId, $balance);
        $value = $request->input('low_threshold');

        $this->ledger->setThreshold(
            $row,
            ($value === null || $value === '') ? null : (float) $value,
            $user,
            $request->input('note'),
            $request
        );

        return back();
    }

    /* ── Reconciliation ──────────────────────────────────────────────────── */

    /**
     * Ask for a discrepancy to be settled, naming the figure.
     *
     * Reuses the Section 1.2 correction queue rather than building a second
     * approval path. What the manager approves is a NUMBER, not the idea of a
     * correction, and the movement that carries it out has to match it exactly.
     */
    public function requestCorrection(Request $request, int $movement)
    {
        [$user, $serviceId] = $this->context($request);

        // Either authority may raise it. Only `reconciliation` may carry it out.
        if (! $this->policy->allows($user, 'stock_management', $serviceId)
            && ! $this->policy->allows($user, 'reconciliation', $serviceId)) {
            abort(403, 'You do not have permission to do that.');
        }

        $target = StockMovement::where('service_id', $serviceId)->findOrFail($movement);

        if (! $this->ledger->discrepancyOpen($target->id, $serviceId)) {
            throw new RuntimeException('That disagreement has already been settled.');
        }

        if ($this->pendingRequestFor($target, $serviceId)) {
            throw new RuntimeException('Somebody has already asked for that to be settled.');
        }

        $delta = $request->input('quantity_delta');

        if ($delta === null || $delta === '' || ! is_numeric($delta)) {
            throw new RuntimeException('Say what the balance should be adjusted by.');
        }

        if (abs((float) $delta) < 0.0005) {
            throw new RuntimeException('A correction of nothing is not a correction.');
        }

        $item = ReviewItem::create([
            'reference' => 'R7SC-'.strtoupper(Str::random(10)),
            'organisation_id' => $target->organisation_id,
            'service_id' => $serviceId,
            'kind' => 'correction_request',
            'title' => 'Stock reconciliation: '.$target->medicine_name_at_time,
            'detail' => trim((string) $request->input('detail')),
            'subject_type' => 'stock_movement',
            'subject_id' => $target->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => (float) $delta,
            'raised_by_user_id' => $user->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $this->audit->record(
            eventType: 'stock_correction_requested',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: 'Reconciliation asked for on movement '.$target->reference,
            riskLevel: 'medium',
            metadata: ['movement_id' => $target->id, 'review_item_id' => $item->id, 'delta' => (float) $delta],
            request: $request
        );

        return back();
    }

    /**
     * Carry out an approved reconciliation.
     *
     * APPROVAL IS NOT EXECUTION. The manager who approved it holds
     * `correction_approval`; carrying it out needs `reconciliation`, the
     * balance lock, request-time authority and the exact approved figure. All
     * four are checked here, and the database checks them again.
     */
    public function correct(Request $request, int $movement)
    {
        [$user, $serviceId] = $this->context($request);
        $this->must($user, $serviceId, 'reconciliation');

        $target = StockMovement::where('service_id', $serviceId)->findOrFail($movement);
        $house = Service::findOrFail($serviceId);

        $trail = ['movement_id' => $target->id, 'service_id' => $serviceId];

        $this->ledger->guarded(function () use ($user, $serviceId, $target, $request) {
            return DB::connection('record7')->transaction(function () use ($user, $serviceId, $target, $request) {
                // RE-ASKED UNDER THE LOCK, in the order that makes the answer
                // worth having: the approval first, then the balance.
                $item = ReviewItem::where('service_id', $serviceId)
                    ->where('subject_type', 'stock_movement')
                    ->where('subject_id', $target->id)
                    ->where('correction_shape', 'stock_delta')
                    ->where('status', 'approved')
                    ->lockForUpdate()
                    ->first();

                if ($item === null) {
                    $this->ledger->refuse(
                        'not_approved',
                        'A manager has not approved a figure for that yet.'
                    );
                }

                if (! $this->ledger->discrepancyOpen($target->id, $serviceId)) {
                    $this->ledger->refuse(
                        'already_settled',
                        'That disagreement has already been settled.'
                    );
                }

                $movement = $this->ledger->compensate(
                    $user, $target, (float) $item->requested_quantity_delta, $item->id
                );

                $this->audit->record(
                    eventType: 'stock_corrected',
                    result: AuditRecorder::SUCCESS,
                    user: $user,
                    serviceId: $serviceId,
                    reason: 'Reconciliation of '.$target->reference,
                    riskLevel: 'medium',
                    metadata: [
                        'movement_id' => $target->id,
                        'correction_id' => $movement?->id,
                        'review_item_id' => $item->id,
                        'delta' => (float) $item->requested_quantity_delta,
                    ],
                    request: $request
                );

                return $movement;
            });
        }, $user, $serviceId, $trail, $request);

        return back();
    }

    /* ── Shared ──────────────────────────────────────────────────────────── */

    /** The frame's own links. Named routes, never strings built here. */
    private function urls(): array
    {
        return [
            'houses' => route('record7.houses'),
            'today' => route('record7.today'),
            'manager' => route('record7.manager'),
            'stock' => route('record7.stock'),
            'lock' => route('record7.lock.now'),
            'signOut' => route('record7.signout'),
        ];
    }


    /** @return array{0:User, 1:int} */
    private function context(Request $request): array
    {
        $user = $this->user();
        $serviceId = $this->sessions->serviceId($request);

        abort_unless($user !== null && $serviceId !== null, 403);

        return [$user, (int) $serviceId];
    }

    /** Request-time authority, asked of AccessPolicy and nothing else. */
    private function must(User $user, int $serviceId, string $permission): void
    {
        $decision = $this->policy->decide($user, $permission, $serviceId);

        abort_if($decision->denied(), 403, $decision->message ?? 'You do not have permission to do that.');
    }

    /** Scoped to the house before the id is used. */
    private function balance(int $serviceId, int $id): StockBalance
    {
        return StockBalance::with(['client', 'medicine', 'threshold'])
            ->where('service_id', $serviceId)
            ->findOrFail($id);
    }

    /** One custody act: authorise, spend the token, write, audit. */
    private function custody(
        Request $request, int $balance, string $action, callable $write, string $event
    ) {
        [$user, $serviceId] = $this->context($request);
        $this->must($user, $serviceId, 'stock_management');

        $row = $this->balance($serviceId, $balance);
        $house = Service::findOrFail($serviceId);
        $input = $request->all();
        $token = (string) $request->input('attempt_token');

        $trail = ['balance_id' => $row->id, 'action' => $action];

        $movement = $this->ledger->guarded(
            fn () => DB::connection('record7')->transaction(
                function () use ($row, $user, $house, $input, $write, $action, $token, $serviceId) {
                    $attempt = $this->claim($token, $user, $serviceId, $row, $action);

                    // Already recorded. Hand back what it became rather than
                    // doing it twice — a second receipt doubles a delivery.
                    if ($attempt->isSpent()) {
                        return $attempt->movement()->first();
                    }

                    $locked = $this->ledger->lockExisting($row);
                    $movement = $write($locked, $user, $house, $input);

                    $attempt->forceFill([
                        'consumed_at' => now(),
                        'stock_movement_id' => $movement->id,
                    ])->save();

                    return $movement;
                }
            ),
            $user, $serviceId, $trail, $request
        );

        $this->audit->record(
            eventType: $event,
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: null,
            riskLevel: 'low',
            metadata: $trail + ['movement_id' => $movement?->id],
            request: $request
        );

        if ($movement?->is_discrepancy) {
            $this->audit->record(
                eventType: 'stock_discrepancy_found',
                result: AuditRecorder::WARNING,
                user: $user,
                serviceId: $serviceId,
                reason: 'A count did not match the record.',
                riskLevel: 'high',
                metadata: $trail + [
                    'movement_id' => $movement->id,
                    'expected' => (float) $movement->expected_quantity,
                    'counted' => (float) $movement->counted_quantity,
                ],
                request: $request
            );
        }

        return back();
    }

    /**
     * Claim the attempt, or refuse.
     *
     * Scoped to the balance, the action and the person it was minted for, so a
     * token cannot be pointed at another balance or spent by a colleague.
     */
    private function claim(string $token, User $user, int $serviceId, StockBalance $row, string $action): StockAttempt
    {
        if (trim($token) === '') {
            $this->ledger->refuse(
                'no_attempt',
                'Start again from the medicine so this can be recorded safely.'
            );
        }

        $attempt = StockAttempt::where('token', $token)->lockForUpdate()->first();

        if ($attempt === null
            || $attempt->service_id !== $serviceId
            || $attempt->stock_balance_id !== $row->id
            || $attempt->action !== $action
            || $attempt->issued_to_user_id !== $user->id) {
            $this->ledger->refuse(
                'wrong_attempt',
                'Start again from the medicine so this can be recorded safely.'
            );
        }

        return $attempt;
    }

    private function mintToken(User $user, StockBalance $row, string $action): string
    {
        $token = (string) Str::uuid();

        StockAttempt::create([
            'token' => $token,
            'organisation_id' => $row->organisation_id,
            'service_id' => $row->service_id,
            'stock_balance_id' => $row->id,
            'action' => $action,
            'issued_to_user_id' => $user->id,
            'issued_at' => now(),
        ]);

        return $token;
    }

    private function quantity(array $input, string $key, bool $allowZero = false): float
    {
        $value = $input[$key] ?? null;

        if ($value === null || $value === '' || ! is_numeric($value)) {
            $this->ledger->refuse('no_quantity', 'Say how much, as a number.');
        }

        $value = (float) $value;

        if ($value < 0 || (! $allowZero && $value <= 0)) {
            $this->ledger->refuse('bad_quantity', 'That is not a quantity this can record.');
        }

        return $value;
    }

    /** The preparation this balance describes, as the ledger recorded it. */
    private function snapshotOf(StockBalance $row): array
    {
        $medicine = $row->medicine;

        return [
            'medicine_id' => $medicine->id,
            'medicine_name_at_time' => $medicine->name,
            'form_at_time' => $medicine->form,
            'strength_at_time' => $medicine->strength,
            'unit' => $row->unit,
        ];
    }

    /** One balance, said in words a person can act on. */
    private function describe(StockBalance $row): array
    {
        $unresolved = $this->ledger->unresolvedDiscrepancies($row);
        $threshold = $row->threshold;

        return [
            'id' => $row->id,
            'person' => $row->client?->preferred_name,
            'clientId' => $row->client_id,
            'medicine' => $row->medicine?->name,
            'form' => $row->medicine?->form,
            'strength' => $row->medicine?->strength,
            'unit' => $row->unit,
            'balance' => $this->ledger->tidy($row->current_balance),
            'balanceWord' => $this->ledger->unitWord($row->unit, (float) $row->current_balance),
            'out' => $row->isOut(),
            'low' => $row->isLow(),
            'negative' => (float) $row->current_balance < 0,

            // NULL IS NOT HEALTHY, and the screen must not let it look that way.
            'hasThreshold' => $threshold !== null,
            'threshold' => $threshold ? $this->ledger->tidy($threshold->low_threshold) : null,
            'thresholdNote' => $threshold === null
                ? 'No reorder level recorded'
                // Section 2.4's rule, reused: "6 sachets", never "6 sachet".
                : 'Low below '.$this->ledger->tidy($threshold->low_threshold).' '
                    .$this->ledger->unitWord($row->unit, (float) $threshold->low_threshold),

            'discrepancies' => $unresolved->count(),
            'lastCounted' => $row->last_counted_at?->format('j F'),
            'controlled' => false,
        ];
    }

    /**
     * Controlled balances, read from Section 2.5 and clearly labelled.
     *
     * There is no write path from this screen and there will not be one. The
     * register is their sole authority; this shows the figure so a manager does
     * not have to hold two screens open, and says where it comes from.
     */
    private function controlledView(int $serviceId): array
    {
        return CdBalance::with(['client', 'medicine'])
            ->where('service_id', $serviceId)
            ->get()
            ->map(fn (CdBalance $b) => [
                'person' => $b->client?->preferred_name,
                'medicine' => $b->medicine?->name,
                'balance' => $this->ledger->tidy($b->current_balance),
                'unit' => $this->ledger->unitWord($b->unit, (float) $b->current_balance),
                'source' => 'Controlled drug register (Section 2.5)',
                'writable' => false,
            ])->values()->all();
    }

    /**
     * Counted, but no dose quantity is recorded.
     *
     * Named on the screen rather than left silent, because a medicine whose
     * doses quietly never move the balance looks exactly like one that is
     * behaving — until somebody relies on the figure.
     */
    private function unquantified(int $serviceId): array
    {
        $balances = StockBalance::with('medicine')->where('service_id', $serviceId)->get();
        $rows = [];

        foreach ($balances as $balance) {
            $prescriptions = Prescription::with('medicine')
                ->where('client_id', $balance->client_id)
                ->where('medicine_id', $balance->medicine_id)
                ->where('status', 'active')
                ->get();

            foreach ($prescriptions as $prescription) {
                if ($this->ledger->doseQuantity($prescription) !== null) {
                    continue;
                }

                $rows[] = [
                    'balanceId' => $balance->id,
                    'person' => Client::find($balance->client_id)?->preferred_name,
                    'medicine' => $balance->medicine?->name,
                    'dose' => $prescription->dose,
                    'why' => 'Counted, but no dose quantity is recorded, so doses do not move '
                        .'the balance.',
                ];
            }
        }

        return $rows;
    }

    private function approvalFor(StockMovement $movement, int $serviceId): ?ReviewItem
    {
        return ReviewItem::where('service_id', $serviceId)
            ->where('subject_type', 'stock_movement')
            ->where('subject_id', $movement->id)
            ->where('correction_shape', 'stock_delta')
            ->where('status', 'approved')
            ->first();
    }

    private function pendingRequestFor(StockMovement $movement, int $serviceId): ?ReviewItem
    {
        return ReviewItem::where('service_id', $serviceId)
            ->where('subject_type', 'stock_movement')
            ->where('subject_id', $movement->id)
            ->where('status', 'open')
            ->first();
    }
}
