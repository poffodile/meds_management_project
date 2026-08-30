<?php

namespace App\Services\Record7;

use App\Models\Record7\CdBalance;
use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * The controlled drug register — what physically happened to the stock.
 *
 * TWO RECORDS, TWO QUESTIONS. The administration says what happened clinically
 * to a person and a dose. This says what happened physically to a quantity of
 * a controlled medicine. A refusal where nothing left the cupboard is a
 * complete clinical record with no movement at all, and inventing a
 * zero-quantity entry for it would fill the ledger with non-events.
 *
 * NOTHING HERE DECIDES WHETHER A DOSE SHOULD BE GIVEN. That is a clinical
 * judgement made by a person under their service's procedure. This decides only
 * whether Record7 can PROVE a movement — enough verified stock, and the witness
 * conditions satisfied — and it fails closed when it cannot. Refusing to record
 * is not the same as saying the medicine must not be given, and the screens say
 * so.
 */
class ControlledDrugRegister
{
    /** A movement that reduces what is in the cupboard needs stock to reduce. */
    private const CONSUMES_STOCK = ['administration', 'non_administration', 'waste'];

    public function __construct(private readonly AuditRecorder $audit)
    {
    }

    /**
     * What the rule demands for this house, right now.
     *
     * @return array{required:bool, why:string}
     */
    public function witnessRule(Service $house): array
    {
        return [
            'required' => $house->controlledDrugWitnessRequired(),
            'why' => $house->witnessRuleExplained(),
        ];
    }

    /**
     * The preparation this medicine currently represents.
     *
     * Snapshotted at the moment of the movement, because form and strength are
     * ordinary editable columns. A register keyed on the medicine row alone
     * would be quietly falsified the first time somebody corrected a strength:
     * every historical balance would start meaning something else without a
     * single entry changing.
     *
     * @return array<string, mixed>
     */
    public function snapshot(Medicine $medicine, ?string $unit): array
    {
        $unit = $unit !== null && trim($unit) !== '' ? trim($unit) : null;

        if ($unit === null) {
            throw new RuntimeException(
                'This medicine has no unit recorded, so a quantity cannot be counted. '
                .'Ask for the prescription to be completed before giving it.'
            );
        }

        return [
            'medicine_id' => $medicine->id,
            'medicine_name_at_time' => $medicine->name,
            'form_at_time' => $medicine->form,
            'strength_at_time' => $medicine->strength,
            'unit' => $unit,
            'cd_schedule_at_time' => $medicine->cd_schedule,
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
            $snapshot['cd_schedule_at_time'] ?? '',
        ]));
    }

    /** What the ledger says is there, without taking a lock. For display only. */
    public function balanceFor(Client $client, array $snapshot): ?CdBalance
    {
        return CdBalance::where('client_id', $client->id)
            ->where('preparation_key', $this->preparationKey($snapshot))
            ->first();
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
    public function lockBalance(Client $client, Service $house, array $snapshot): CdBalance
    {
        $key = $this->preparationKey($snapshot);

        DB::connection('record7')->table('record7_cd_balances')->insertOrIgnore([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'client_id' => $client->id,
            'medicine_id' => $snapshot['medicine_id'],
            'preparation_key' => $key,
            'unit' => $snapshot['unit'],
            'current_balance' => 0,
            'last_sequence_no' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return CdBalance::where('client_id', $client->id)
            ->where('preparation_key', $key)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Can this movement be proved right now?
     *
     * @return array{allowed:bool, code:?string, reason:?string}
     */
    public function canMove(CdBalance $balance, string $action, float $leaving): array
    {
        if (! in_array($action, self::CONSUMES_STOCK, true)) {
            return ['allowed' => true, 'code' => null, 'reason' => null];
        }

        if ((float) $balance->current_balance <= 0) {
            return [
                'allowed' => false,
                'code' => 'no_balance',
                'reason' => 'Record7 has no counted stock for this medicine, so it cannot '
                    .'account for a dose. Record what is physically there first.',
            ];
        }

        if ($leaving > (float) $balance->current_balance) {
            return [
                'allowed' => false,
                'code' => 'insufficient_balance',
                'reason' => 'The register shows '.$this->tidy($balance->current_balance).' '
                    .$balance->unit.', which is less than this would take out. '
                    .'Count what is there and record it before going further.',
            ];
        }

        return ['allowed' => true, 'code' => null, 'reason' => null];
    }

    /**
     * Is there an unresolved disagreement about this stock?
     *
     * Where there is, Record7 cannot prove a balance, so it will not record a
     * movement against one. It is not refusing the medicine — it is refusing to
     * claim it knows something it does not.
     */
    public function openDiscrepancy(Client $client, array $snapshot): ?CdRegister
    {
        return CdRegister::where('client_id', $client->id)
            ->where('is_discrepancy', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('record7_cd_register as fix')
                    ->whereColumn('fix.corrects_register_id', 'record7_cd_register.id');
            })
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Write one movement. Must be called inside the transaction that took the
     * lock, so the balance it computes from cannot move underneath it.
     */
    public function record(
        CdBalance $balance,
        array $snapshot,
        string $action,
        array $quantities,
        User $user,
        ?User $witness,
        bool $witnessRequired,
        ?string $unwitnessedBasis,
        Client $client,
        Service $house,
        ?Prescription $prescription = null,
        ?string $notes = null,
        ?CdRegister $corrects = null,
        ?Carbon $at = null
    ): CdRegister {
        $before = (float) $balance->current_balance;

        $after = match ($action) {
            'receipt' => $before + (float) ($quantities['received'] ?? 0),
            'administration' => $before - ((float) ($quantities['given'] ?? 0) + (float) ($quantities['wasted'] ?? 0)),
            'non_administration' => $before - (float) ($quantities['wasted'] ?? 0),
            'return_to_storage' => $before + (float) ($quantities['returned'] ?? 0),
            'waste' => $before - (float) ($quantities['wasted'] ?? 0),
            'stock_check' => (float) ($quantities['counted'] ?? 0),
            'correction' => $before + (float) ($quantities['delta'] ?? 0),
            default => throw new RuntimeException('Unknown controlled drug movement.'),
        };

        // A count that disagrees with the ledger is the whole reason a register
        // exists. Both figures are kept; the verified one becomes the balance.
        $isDiscrepancy = $action === 'stock_check'
            && abs((float) ($quantities['counted'] ?? 0) - $before) > 0.0001;

        $entry = CdRegister::create([
            'reference' => 'R7CD-'.strtoupper(Str::random(12)),
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'client_id' => $client->id,
            'prescription_id' => $prescription?->id,

            ...$snapshot,

            'action' => $action,
            'quantity_received' => $quantities['received'] ?? null,
            'quantity_removed' => $quantities['removed'] ?? null,
            'quantity_given' => $quantities['given'] ?? null,
            'quantity_returned' => $quantities['returned'] ?? null,
            'quantity_wasted' => $quantities['wasted'] ?? null,

            // Kept side by side. The expected figure is evidence of the
            // divergence and is never overwritten by the count that found it.
            'expected_quantity' => $action === 'stock_check' ? $before : null,
            'counted_quantity' => $quantities['counted'] ?? null,

            'balance_before' => $action === 'receipt' && $balance->last_sequence_no === 0 ? null : $before,
            'balance_after' => $after,
            'is_discrepancy' => $isDiscrepancy,

            'recorded_by_user_id' => $user->id,
            'witnessed_by_user_id' => $witness?->id,
            'witness_was_required' => $witnessRequired,
            'unwitnessed_basis' => $witness === null ? $unwitnessedBasis : null,
            // Evidence, never identity. The FK above is who the witness IS;
            // these keep the record readable if they later leave, exactly as
            // the audit trail already does with staff_name_at_time.
            'witness_name_at_time' => $witness?->full_name,
            'witness_role_at_time' => $witness?->primaryRole()?->name,

            'occurred_at' => $at ?? now(),
            'corrects_register_id' => $corrects?->id,
            'notes' => $notes,
            'sequence_no' => $balance->last_sequence_no + 1,
        ]);

        // The head moves last, inside the same lock, so it can never describe a
        // movement that was not written.
        $balance->forceFill([
            'current_balance' => $after,
            'last_sequence_no' => $entry->sequence_no,
            'last_register_id' => $entry->id,
        ])->save();

        return $entry;
    }

    /** History for one person and preparation, newest first. */
    public function history(Client $client, array $snapshot, int $limit = 20): array
    {
        return CdRegister::with(['recordedBy', 'witnessedBy'])
            ->where('client_id', $client->id)
            ->where('preparation_key', $this->preparationKey($snapshot))
            ->orderByDesc('sequence_no')
            ->limit($limit)
            ->get()
            ->map(fn (CdRegister $e) => [
                'id' => $e->id,
                'action' => $e->action,
                'word' => $e->actionWord(),
                'given' => $e->quantity_given !== null ? $this->tidy($e->quantity_given) : null,
                'removed' => $e->quantity_removed !== null ? $this->tidy($e->quantity_removed) : null,
                'returned' => $e->quantity_returned !== null ? $this->tidy($e->quantity_returned) : null,
                'wasted' => $e->quantity_wasted !== null ? $this->tidy($e->quantity_wasted) : null,
                'counted' => $e->counted_quantity !== null ? $this->tidy($e->counted_quantity) : null,
                'expected' => $e->expected_quantity !== null ? $this->tidy($e->expected_quantity) : null,
                'balance' => $this->tidy($e->balance_after),
                'unit' => $e->unit,
                'balanceUnit' => $this->unitWord($e->unit, (float) $e->balance_after),
                'givenUnit' => $this->unitWord($e->unit, (float) $e->quantity_given),
                'discrepancy' => (bool) $e->is_discrepancy,
                'by' => $e->recordedBy?->displayName(),
                'witness' => $e->witnessedBy?->displayName(),
                'unwitnessed' => $e->unwitnessed_basis
                    ? (CdRegister::UNWITNESSED_REASONS[$e->unwitnessed_basis] ?? $e->unwitnessed_basis)
                    : null,
                // The o and the n in "on" are both date format characters, so
                // they are escaped. Unescaped, "on" renders as an ISO year and
                // a month number.
                'at' => $e->occurred_at?->format('H:i \o\n j F'),
                'notes' => $e->notes,
            ])->all();
    }

    /**
     * "1 tablet", "14 tablets", "5ml".
     *
     * Reuses Section 2.4's rule rather than writing a second one, so the two
     * screens cannot start disagreeing about how to say the same quantity.
     */
    public function unitWord(?string $unit, float $amount): string
    {
        return app(PrnAdministration::class)->unitWord($unit, $amount);
    }

    /** 2.000 reads as 2, 0.500 as 0.5. Nobody says "two point zero zero zero". */
    public function tidy(float|string|null $value): string
    {
        if ($value === null) {
            return '0';
        }

        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.') ?: '0';
    }

    public function auditBlocked(
        string $code,
        string $reason,
        User $user,
        int $serviceId,
        array $trail,
        $request
    ): void {
        $this->audit->record(
            eventType: 'controlled_drug_movement_blocked',
            result: AuditRecorder::WARNING,
            user: $user,
            serviceId: $serviceId,
            reason: $reason,
            riskLevel: 'high',
            metadata: ['code' => $code] + $trail,
            request: $request
        );
    }
}
