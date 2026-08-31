<?php

namespace App\Services\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\Service;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockMovement;
use App\Models\Record7\StockThreshold;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The ordinary stock ledger — what physically happened to the medicine.
 *
 * TWO RECORDS, TWO QUESTIONS. The administration says what happened clinically
 * to a person and a dose. This says what happened physically to a quantity. A
 * refusal where nothing left the cupboard is a complete clinical record with no
 * movement at all, and inventing a zero-quantity entry for it would fill the
 * ledger with non-events.
 *
 * NOTHING HERE DECIDES WHETHER A DOSE SHOULD BE GIVEN. That is a clinical
 * judgement made by a person. This decides only what Record7 can PROVE about a
 * quantity — and where it cannot prove one, it records the truth and says so
 * rather than inventing a figure or refusing the clinical record.
 *
 * THREE THINGS THIS WILL NOT DO.
 *
 *   It will not parse a dose. `record7_prescriptions.dose` is display text.
 *   Legacy audit CR-02 is what happens when something does: regex-stripping
 *   '10 ml (500mg)' produced 10500 and floored a real 56-unit balance to zero
 *   on one tap.
 *
 *   It will not convert units. An exact match or a refusal, never a guess at
 *   what somebody meant.
 *
 *   It will not touch a controlled medicine. Section 2.5's register is their
 *   sole authority, and the insert trigger refuses one before any other check.
 */
class StockLedger
{
    /** Movements that reduce what is in the cupboard. */
    private const CONSUMES_STOCK = ['administration', 'non_administration', 'waste'];

    public function __construct(private readonly AuditRecorder $audit)
    {
    }

    /* ── Refusals ────────────────────────────────────────────────────────── */

    /** A refusal, carrying the code that caused it. */
    public function refuse(string $code, string $reason): never
    {
        throw new StockRefusal($reason, $code);
    }

    /** Run a stock act, and make sure a refusal is still audited. */
    public function guarded(
        callable $work, User $user, int $serviceId, array $trail, ?Request $request = null
    ): mixed {
        try {
            return $work();
        } catch (StockRefusal $refused) {
            // OUTSIDE the transaction, which has already rolled back.
            $this->auditBlocked(
                $refused->refusalCode, $refused->getMessage(), $user, $serviceId, $trail, $request
            );

            throw $refused;
        }
    }

    public function auditBlocked(
        string $code, string $reason, User $user, int $serviceId, array $trail, $request = null
    ): void {
        $this->audit->record(
            eventType: 'stock_movement_blocked',
            result: AuditRecorder::WARNING,
            user: $user,
            serviceId: $serviceId,
            reason: $reason,
            riskLevel: 'medium',
            metadata: ['code' => $code] + $trail,
            request: $request
        );
    }

    /* ── Preparation identity ────────────────────────────────────────────── */

    /**
     * The preparation this medicine currently represents.
     *
     * Snapshotted at the moment of the movement, because form and strength are
     * ordinary editable columns. A ledger keyed on the medicine row alone would
     * be quietly falsified the first time somebody corrected a strength: every
     * historical balance would start meaning something else without a single
     * entry changing.
     *
     * @return array<string, mixed>
     */
    public function snapshot(Medicine $medicine, ?string $unit): array
    {
        $unit = $unit !== null && trim($unit) !== '' ? trim($unit) : null;

        if ($unit === null) {
            $this->refuse(
                'no_unit',
                'This medicine has no unit recorded, so a quantity cannot be counted. '
                .'Ask for the prescription to be completed before counting it.'
            );
        }

        if ($medicine->is_controlled) {
            $this->refuse(
                'controlled_medicine',
                'This is a controlled medicine, so it is accounted for in the controlled '
                .'drug register rather than here.'
            );
        }

        return [
            'medicine_id' => $medicine->id,
            'medicine_name_at_time' => $medicine->name,
            'form_at_time' => $medicine->form,
            'strength_at_time' => $medicine->strength,
            'unit' => $unit,
        ];
    }

    /** The same key MySQL generates, so PHP and the database agree. */
    public function preparationKey(array $snapshot): string
    {
        return hash('sha256', implode('|', [
            $snapshot['medicine_id'],
            $snapshot['form_at_time'] ?? '',
            $snapshot['strength_at_time'] ?? '',
            $snapshot['unit'],
        ]));
    }

    /* ── Quantities ──────────────────────────────────────────────────────── */

    /**
     * How much of this prescription does one dose consume?
     *
     * Returns null when the answer is not known with certainty, and the caller
     * must then move no stock rather than guess.
     *
     * Two sources, in order: what was actually recorded as given, then a fixed
     * prescribed dose. A range is not an answer — "one or two tablets" does not
     * say what was taken, and only the person who gave it can.
     */
    public function doseQuantity(Prescription $prescription, ?float $recorded = null): ?float
    {
        if ($recorded !== null && $recorded > 0) {
            return $recorded;
        }

        if ($prescription->dose_unit === null
            || $prescription->dose_min === null
            || $prescription->dose_max === null) {
            return null;
        }

        if ((float) $prescription->dose_min !== (float) $prescription->dose_max) {
            return null;
        }

        $amount = (float) $prescription->dose_min;

        return $amount > 0 ? $amount : null;
    }

    /**
     * Did this dose come out of stock Record7 accounts for?
     *
     * Where staff hand a medicine over — administered, assisted or prompted —
     * it left the cupboard and the ledger should say so.
     *
     * Where the person holds their own supply and manages it themselves, it did
     * not. Staff watching somebody take their own tablet and writing it down is
     * a clinical record, not a stock movement, and inventing a debit from an
     * absent staff record would be inventing consumption from a supply Record7
     * does not hold. Only monitored self-administration, where staff hand the
     * medicine over and record that they did, moves accounted stock.
     */
    public function consumesAccountedStock(Prescription $prescription): bool
    {
        if ($prescription->support_type !== 'self_administered') {
            return true;
        }

        return $prescription->self_administration_monitoring === 'check_and_record';
    }

    /* ── The balance ─────────────────────────────────────────────────────── */

    /** What the ledger says is there, without taking a lock. For display only. */
    public function balanceFor(Client $client, array $snapshot): ?StockBalance
    {
        return StockBalance::with('threshold')
            ->where('client_id', $client->id)
            ->where('preparation_key', $this->preparationKey($snapshot))
            ->first();
    }

    /** Is this preparation being counted for this person at all? */
    public function isTracked(Client $client, array $snapshot): bool
    {
        return $this->balanceFor($client, $snapshot) !== null;
    }

    /**
     * Take the lock, and hand back the balance row to decide against.
     *
     * INSERT FIRST, THEN LOCK, deliberately. Taking `FOR UPDATE` on a row that
     * does not exist sets a gap lock under REPEATABLE READ, and two workers
     * opening the same person's first ever movement would deadlock on the gap
     * rather than queue. `insertOrIgnore` resolves that race on the unique key
     * instead: one insert wins, the other is a no-op, and both then lock the
     * same real row.
     *
     * Must be called inside a transaction. The lock is held until it commits.
     */
    public function lockBalance(Client $client, Service $house, array $snapshot): StockBalance
    {
        $key = $this->preparationKey($snapshot);

        DB::connection('record7')->table('record7_stock_balances')->insertOrIgnore([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'owner_type' => 'client',
            'client_id' => $client->id,
            'medicine_id' => $snapshot['medicine_id'],
            'preparation_key' => $key,
            'unit' => $snapshot['unit'],
            'current_balance' => 0,
            'last_sequence_no' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return StockBalance::where('client_id', $client->id)
            ->where('preparation_key', $key)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /** The same, but only where the balance already exists. */
    public function lockExisting(StockBalance $balance): StockBalance
    {
        return StockBalance::where('id', $balance->id)->lockForUpdate()->firstOrFail();
    }

    /* ── Discrepancies ───────────────────────────────────────────────────── */

    /**
     * Every unresolved disagreement on this balance, oldest first.
     *
     * EACH ENTRY KEEPS ITS OWN IDENTITY. A later shortfall is not swallowed
     * because an earlier count is still open, and correcting the earlier one
     * can never hide the later one — they are separate rows with separate
     * requirements. Aggregation happens on the manager board and nowhere else.
     *
     * @return \Illuminate\Support\Collection<int, StockMovement>
     */
    public function unresolvedDiscrepancies(StockBalance $balance)
    {
        return StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('record7_stock_movements as fix')
                    ->whereColumn('fix.corrects_movement_id', 'record7_stock_movements.id');
            })
            ->orderBy('sequence_no')
            ->get();
    }

    /** Is this one particular disagreement still unanswered? */
    public function discrepancyOpen(int $movementId, int $serviceId): bool
    {
        return StockMovement::where('id', $movementId)
            ->where('service_id', $serviceId)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('record7_stock_movements as fix')
                    ->whereColumn('fix.corrects_movement_id', 'record7_stock_movements.id');
            })
            ->exists();
    }

    /* ── Writing ─────────────────────────────────────────────────────────── */

    /**
     * Write one movement. Must be called inside the transaction that took the
     * lock, so the balance it computes from cannot move underneath it.
     *
     * The head moves last, inside the same lock, so it can never describe a
     * movement that was not written.
     */
    public function record(
        StockBalance $balance,
        array $snapshot,
        string $action,
        array $quantities,
        User $user,
        Client $client,
        Service $house,
        ?Prescription $prescription = null,
        ?string $notes = null,
        ?StockMovement $corrects = null,
        ?int $reviewItemId = null,
        ?array $shortfall = null,
        ?Carbon $at = null
    ): StockMovement {
        $before = (float) $balance->current_balance;
        $opening = $action === 'opening_balance';

        $after = match ($action) {
            'opening_balance' => (float) ($quantities['received'] ?? 0),
            'receipt' => $before + (float) ($quantities['received'] ?? 0),
            'administration' => $before - ((float) ($quantities['given'] ?? 0) + (float) ($quantities['wasted'] ?? 0)),
            'non_administration' => $before - (float) ($quantities['wasted'] ?? 0),
            'return_to_stock' => $before + (float) ($quantities['returned'] ?? 0),
            'waste' => $before - (float) ($quantities['wasted'] ?? 0),

            // A COUNT OBSERVES; IT DOES NOT CORRECT. The balance does not move.
            // Only the later approved correction applies the reconciled delta,
            // which is what gives that workflow its meaning — and what stops a
            // count being used to make an inconvenient figure go away.
            'stock_check' => $before,

            'correction' => $before + (float) ($quantities['delta'] ?? 0),
            default => $this->refuse('unknown_action', 'Unknown stock movement.'),
        };

        $counted = $quantities['counted'] ?? null;

        // A count that disagrees is the whole reason a ledger exists. Both
        // figures are kept; neither overwrites the other.
        $isDiscrepancy = $action === 'stock_check'
            && $counted !== null
            && abs((float) $counted - $before) > 0.0005;

        // And so is a dose the balance could not cover. Every negative position
        // is a disagreement, whatever verb produced it.
        if ($after < 0) {
            $isDiscrepancy = true;
        }

        // THE SERVICE REFUSES FIRST, so a person gets a sentence they can act
        // on instead of a database error. The CHECK constraint says the same
        // thing and stays as the backstop for anything that bypasses this —
        // but a worker at a trolley should never be the one who meets it.
        //
        // Scoped the way the constraint is: to a contemporaneous administration.
        // A retrospective movement written under an approved correction has no
        // point of care and no cupboard to check.
        if ($action === 'administration'
            && $after < 0
            && $shortfall === null
            && $reviewItemId === null) {
            $this->refuse(
                'shortfall_unverified',
                'The record shows less of this medicine than this dose needs. Check what is '
                .'physically there and say what you found before recording it.'
            );
        }

        $movement = StockMovement::create([
            'reference' => 'R7SM-'.strtoupper(Str::random(12)),
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'owner_type' => 'client',
            'client_id' => $client->id,
            'prescription_id' => $prescription?->id,

            ...$snapshot,

            'action' => $action,
            'quantity_received' => $quantities['received'] ?? null,
            'quantity_removed' => $quantities['removed'] ?? null,
            'quantity_given' => $quantities['given'] ?? null,
            'quantity_returned' => $quantities['returned'] ?? null,
            'quantity_wasted' => $quantities['wasted'] ?? null,
            'quantity_delta' => $action === 'correction' ? ($quantities['delta'] ?? null) : null,

            // Kept side by side. The expected figure is the evidence of the
            // divergence and is never overwritten by the count that found it.
            'expected_quantity' => $action === 'stock_check' ? $before : null,
            'counted_quantity' => $action === 'stock_check' ? $counted : null,

            'balance_before' => $opening ? null : $before,
            'balance_after' => $after,
            'is_discrepancy' => $isDiscrepancy,

            'shortfall_verified_by_user_id' => $shortfall['user_id'] ?? null,
            'shortfall_verified_at' => $shortfall === null ? null : now(),
            'shortfall_basis' => $shortfall['basis'] ?? null,
            'shortfall_statement' => $shortfall['statement'] ?? null,
            'shortfall_observed_quantity' => $shortfall['observed'] ?? null,

            'recorded_by_user_id' => $user->id,
            'occurred_at' => $at ?? now(),
            'corrects_movement_id' => $corrects?->id,
            'review_item_id' => $reviewItemId,
            'notes' => $notes,
            'sequence_no' => $balance->last_sequence_no + 1,
        ]);

        $head = [
            'current_balance' => $after,
            'last_sequence_no' => $movement->sequence_no,
            'last_movement_id' => $movement->id,
        ];

        if ($action === 'stock_check') {
            $head['last_counted_at'] = $movement->occurred_at;
        }

        $balance->forceFill($head)->save();

        return $movement;
    }

    /* ── Can this movement be proved right now? ──────────────────────────── */

    /**
     * Whether the balance can carry what is about to leave it.
     *
     * This is NOT a decision about whether the medicine should be given. Where
     * the ledger cannot cover a dose, the answer is that structured physical
     * verification is needed first — not that the person goes without.
     *
     * @return array{sufficient:bool, shortfall:float}
     */
    public function canCover(StockBalance $balance, string $action, float $leaving): array
    {
        if (! in_array($action, self::CONSUMES_STOCK, true) || $leaving <= 0) {
            return ['sufficient' => true, 'shortfall' => 0.0];
        }

        $available = (float) $balance->current_balance;

        return [
            'sufficient' => $available >= $leaving,
            'shortfall' => max(0.0, $leaving - $available),
        ];
    }

    /**
     * Check the physical verification a shortfall needs, and shape it.
     *
     * Four pieces of evidence, not a checkbox: who (the authenticated actor,
     * never an id from the request), when (the server clock), what basis, and
     * what they actually say they checked. The optional observed quantity is
     * what they saw for whoever reconciles this — deliberately NOT a count.
     *
     * @return array<string, mixed>
     */
    public function verifyShortfall(User $user, array $input): array
    {
        $basis = $input['shortfall_basis'] ?? null;
        $statement = trim((string) ($input['shortfall_statement'] ?? ''));

        if (! array_key_exists((string) $basis, StockMovement::SHORTFALL_BASES)) {
            $this->refuse(
                'shortfall_unverified',
                'The record shows less of this medicine than this dose needs. Say what you '
                .'checked before recording it.'
            );
        }

        if ($statement === '') {
            $this->refuse(
                'shortfall_unstated',
                'Say in your own words what you checked, so whoever reconciles this knows '
                .'what you found.'
            );
        }

        $observed = $input['shortfall_observed_quantity'] ?? null;

        if ($observed !== null && $observed !== '' && (float) $observed < 0) {
            $this->refuse('shortfall_observed_negative', 'A quantity cannot be less than nothing.');
        }

        return [
            // THE AUTHENTICATED USER, never an id from the request.
            'user_id' => $user->id,
            'basis' => $basis,
            'statement' => Str::limit($statement, 185, '…'),
            'observed' => ($observed === null || $observed === '') ? null : (float) $observed,
        ];
    }

    /* ── Corrections ─────────────────────────────────────────────────────── */

    /**
     * Is this person's medicine being counted at all?
     *
     * Asked by medicine rather than by preparation, because the question is
     * "does a balance exist for this" and not "which one". Where nothing is
     * counted there is nothing to correct and nothing to verify.
     */
    public function trackedFor(int $clientId, int $medicineId): ?StockBalance
    {
        return StockBalance::where('client_id', $clientId)
            ->where('medicine_id', $medicineId)
            ->first();
    }

    /**
     * Give back the quantity a corrected administration says never left.
     *
     * THE ATTRIBUTABLE QUANTITY ONLY. An administration debits given + wasted,
     * and correcting the clinical outcome does not un-waste anything — the
     * wasted portion was destroyed as a separate physical act. The caller works
     * out what is attributable; this applies it.
     *
     * A zero delta writes nothing. A movement that says a balance did not move
     * is a non-event, and the ledger is not the place for those.
     */
    public function compensate(
        User $manager, StockMovement $original, float $delta, int $reviewItemId
    ): ?StockMovement {
        if (abs($delta) < 0.0005) {
            return null;
        }

        $balance = $this->lockFor($original);

        if ($balance === null) {
            $this->refuse(
                'no_balance_to_correct',
                'There is no stock balance for that medicine any more, so the correction '
                .'cannot be applied to one.'
            );
        }

        return $this->record(
            balance: $balance,
            snapshot: $this->snapshotOf($original),
            action: 'correction',
            quantities: ['delta' => $delta],
            user: $manager,
            client: $original->client,
            house: Service::findOrFail($original->service_id),
            prescription: $original->prescription,
            notes: 'Correction of '.$original->reference.'.',
            corrects: $original,
            reviewItemId: $reviewItemId,
        );
    }

    /**
     * Establish a debit for a dose that was given but never recorded as such.
     *
     * THE AMOUNT COMES FROM THE APPROVED EVIDENCE, NEVER THE PRESCRIPTION. A
     * prescription can change between the event and the correction, and reading
     * today's figure into last month's dose would give historical MAR data
     * newer prescribing details — which is exactly the rewrite this section
     * exists to prevent.
     *
     * The verb is `administration`, not `correction`: there is no earlier
     * movement to point at, and neither the correction constraint nor the
     * vocabulary is bent to pretend otherwise. What explains it is the
     * corrective administration's own `corrects_administration_id` chain.
     */
    public function establishDebit(
        User $manager,
        \App\Models\Record7\Administration $original,
        float $amount,
        string $unit,
        int $reviewItemId
    ): ?StockMovement {
        $prescription = $original->prescription;
        $medicine = $prescription?->medicine;

        if (! $prescription || ! $medicine || $medicine->is_controlled) {
            return null;
        }

        $balance = $this->trackedFor($original->client_id, $medicine->id);

        if ($balance === null) {
            return null;
        }

        // EXACT MATCH OR NOTHING. Record7 does not convert between units, and
        // guessing what somebody meant is how a millilitre becomes a milligram.
        if (trim($unit) !== (string) $balance->unit) {
            $this->refuse(
                'unit_mismatch',
                'That correction is in '.trim($unit).' and this balance is counted in '
                .$balance->unit.'. Record7 does not convert between units, so the request '
                .'has to be raised again in the unit the balance uses.'
            );
        }

        if ($amount <= 0) {
            $this->refuse('nothing_to_debit', 'A dose that was given was more than nothing.');
        }

        $snapshot = $this->snapshot($medicine, $balance->unit);
        $locked = $this->lockExisting($balance);

        return $this->record(
            balance: $locked,
            snapshot: $snapshot,
            action: 'administration',
            quantities: ['removed' => $amount, 'given' => $amount],
            user: $manager,
            client: $original->client,
            house: Service::findOrFail($original->service_id),
            prescription: $prescription,
            notes: 'Established by correction of '.$original->reference.'.',
            reviewItemId: $reviewItemId,
        );
    }

    /**
     * Somebody has to go and count, and nothing else will answer it.
     *
     * The condition itself is DERIVED — see IssueRegistry — so there is no row
     * to write and nothing anybody can tick. This records that the requirement
     * arose, because a manager reading the audit later needs to see the moment
     * the balance became known to be wrong.
     */
    public function auditVerificationDue(
        \App\Models\Record7\Administration $correction, User $manager, ?Request $request = null
    ): void {
        $this->audit->record(
            eventType: 'stock_verification_required',
            result: AuditRecorder::WARNING,
            user: $manager,
            serviceId: $correction->service_id,
            reason: 'A correction established that a dose was given, but not how much. '
                .'The balance cannot be adjusted until somebody counts.',
            riskLevel: 'medium',
            metadata: [
                'administration_id' => $correction->id,
                'client_id' => $correction->client_id,
                'prescription_id' => $correction->prescription_id,
            ],
            request: $request
        );
    }

    /** The balance a past movement belongs to, locked. */
    private function lockFor(StockMovement $movement): ?StockBalance
    {
        $balance = StockBalance::where('service_id', $movement->service_id)
            ->where('owner_ref', $movement->owner_ref)
            ->where('preparation_key', $movement->preparation_key)
            ->first();

        return $balance ? $this->lockExisting($balance) : null;
    }

    /** The preparation a past movement recorded, exactly as it recorded it. */
    private function snapshotOf(StockMovement $movement): array
    {
        return [
            'medicine_id' => $movement->medicine_id,
            'medicine_name_at_time' => $movement->medicine_name_at_time,
            'form_at_time' => $movement->form_at_time,
            'strength_at_time' => $movement->strength_at_time,
            'unit' => $movement->unit,
        ];
    }

    /* ── Thresholds ──────────────────────────────────────────────────────── */

    /**
     * Record what "low" means for this balance, or remove the rule.
     *
     * Nothing is ever inferred. Where no rule exists, `stock_low` is
     * unavailable rather than false, and the screens say so rather than showing
     * a reassuring blank.
     */
    public function setThreshold(
        StockBalance $balance, ?float $threshold, User $user, ?string $note, ?Request $request = null
    ): ?StockThreshold {
        if ($threshold === null) {
            StockThreshold::where('stock_balance_id', $balance->id)->delete();

            $this->audit->record(
                eventType: 'stock_threshold_cleared',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $balance->service_id,
                reason: 'Reorder level removed',
                metadata: ['balance_id' => $balance->id],
                request: $request
            );

            return null;
        }

        if ($threshold < 0) {
            $this->refuse('threshold_negative', 'A reorder level cannot be less than nothing.');
        }

        $row = StockThreshold::updateOrCreate(
            ['stock_balance_id' => $balance->id],
            [
                'low_threshold' => $threshold,
                'set_by_user_id' => $user->id,
                'set_at' => now(),
                'note' => $note,
            ]
        );

        $this->audit->record(
            eventType: 'stock_threshold_set',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $balance->service_id,
            reason: 'Reorder level set to '.$this->tidy($threshold),
            metadata: ['balance_id' => $balance->id, 'low_threshold' => $threshold],
            request: $request
        );

        return $row;
    }

    /* ── Reading ─────────────────────────────────────────────────────────── */

    /** History for one balance, newest first. */
    public function history(StockBalance $balance, int $limit = 30): array
    {
        return StockMovement::with(['recordedBy', 'shortfallVerifiedBy'])
            ->where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->orderByDesc('sequence_no')
            ->limit($limit)
            ->get()
            ->map(fn (StockMovement $m) => [
                'id' => $m->id,
                'action' => $m->action,
                'word' => $m->actionWord(),
                'received' => $m->quantity_received !== null ? $this->tidy($m->quantity_received) : null,
                'given' => $m->quantity_given !== null ? $this->tidy($m->quantity_given) : null,
                'removed' => $m->quantity_removed !== null ? $this->tidy($m->quantity_removed) : null,
                'returned' => $m->quantity_returned !== null ? $this->tidy($m->quantity_returned) : null,
                'wasted' => $m->quantity_wasted !== null ? $this->tidy($m->quantity_wasted) : null,
                'delta' => $m->quantity_delta !== null ? $this->tidy($m->quantity_delta) : null,
                'expected' => $m->expected_quantity !== null ? $this->tidy($m->expected_quantity) : null,
                'counted' => $m->counted_quantity !== null ? $this->tidy($m->counted_quantity) : null,
                'balance' => $this->tidy($m->balance_after),
                'unit' => $m->unit,
                'balanceUnit' => $this->unitWord($m->unit, (float) $m->balance_after),
                'discrepancy' => (bool) $m->is_discrepancy,
                'cause' => $m->discrepancyCause(),
                'shortfallBasis' => $m->shortfall_basis
                    ? (StockMovement::SHORTFALL_BASES[$m->shortfall_basis] ?? $m->shortfall_basis)
                    : null,
                'shortfallStatement' => $m->shortfall_statement,
                'shortfallObserved' => $m->shortfall_observed_quantity !== null
                    ? $this->tidy($m->shortfall_observed_quantity) : null,
                'by' => $m->recordedBy?->displayName(),
                // The o and the n in "on" are both date format characters, so
                // they are escaped. Unescaped, "on" renders as an ISO year and
                // a month number.
                'at' => $m->occurred_at?->format('H:i \o\n j F'),
                'notes' => $m->notes,
                'imported' => (bool) $m->imported,
            ])->all();
    }

    /**
     * Rebuild a balance from its ledger, without touching anything.
     *
     * The head is derived, and this is what proves it. A test walks every
     * balance in the fixture through this and asserts the stored figure agrees.
     *
     * @return array{balance:float, sequence:int, counted_at:?string}
     */
    public function rebuild(StockBalance $balance): array
    {
        $movements = StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->orderBy('sequence_no')
            ->get();

        $running = 0.0;
        $sequence = 0;
        $countedAt = null;

        foreach ($movements as $m) {
            $running = match ($m->action) {
                'opening_balance' => (float) $m->quantity_received,
                'receipt' => $running + (float) $m->quantity_received,
                'administration' => $running - ((float) $m->quantity_given + (float) $m->quantity_wasted),
                'non_administration' => $running - (float) $m->quantity_wasted,
                'return_to_stock' => $running + (float) $m->quantity_returned,
                'waste' => $running - (float) $m->quantity_wasted,
                'stock_check' => $running,
                'correction' => $running + (float) $m->quantity_delta,
            };

            $sequence = (int) $m->sequence_no;

            if ($m->action === 'stock_check') {
                $countedAt = $m->occurred_at?->toDateTimeString();
            }
        }

        return ['balance' => round($running, 3), 'sequence' => $sequence, 'counted_at' => $countedAt];
    }

    /* ── Words ───────────────────────────────────────────────────────────── */

    /**
     * "1 tablet", "14 tablets", "5ml".
     *
     * Reuses Section 2.4's rule rather than writing a second one, so the
     * screens cannot start disagreeing about how to say the same quantity.
     */
    public function unitWord(?string $unit, float $amount): string
    {
        return app(PrnAdministration::class)->unitWord($unit, $amount);
    }

    /** 2.000 reads as 2; 2.500 reads as 2.5. */
    public function tidy($amount): string
    {
        return rtrim(rtrim(number_format((float) $amount, 3, '.', ''), '0'), '.') ?: '0';
    }
}
