<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\UserServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Giving a controlled drug — the clinical record and the movement, together.
 *
 * ONE TRANSACTION, ONE LOCK, IN THIS ORDER.
 *   1. lock the balance row for this person and preparation
 *   2. decide, against state that can no longer move
 *   3. write the movement
 *   4. write the administration, carrying the movement's id
 *
 * The link points administration -> register, not the other way round. If the
 * register pointed at the administration it would have to be written first with
 * a null link and updated afterwards, and updating an append-only ledger is
 * exactly what must never happen. Neither row can exist without the other:
 * a database trigger refuses a controlled administration with no movement, and
 * a unique key refuses two administrations claiming one movement.
 *
 * WHAT THIS REFUSES, AND WHAT THAT MEANS.
 * Record7 fails closed when it cannot prove a movement — no counted stock, not
 * enough of it, an unresolved disagreement about it, or the witness conditions
 * unmet. That is not Record7 deciding the medicine must not be given. It is
 * Record7 declining to claim it accounted for something it could not account
 * for, and the screens say exactly that.
 */
class ControlledDrugAdministration
{
    /**
     * A refusal, carrying the code that caused it.
     *
     * WHY THIS EXISTS. The audit of a blocked movement used to be written
     * inside the transaction that was about to be rolled back, so the record of
     * the refusal vanished along with the refusal. A blocked attempt on a
     * controlled drug is exactly the thing somebody asks about afterwards, so
     * the code travels out on the exception and is audited once the transaction
     * has unwound.
     */
    private function refuse(string $code, string $reason): never
    {
        throw new ControlledDrugRefusal($reason, $code);
    }

    /** Run a movement, and make sure a refusal is still audited. */
    private function guarded(
        callable $work, User $user, int $serviceId, array $trail, Request $request
    ): mixed {
        try {
            return $work();
        } catch (ControlledDrugRefusal $refused) {
            // OUTSIDE the transaction, which has already rolled back.
            $this->register->auditBlocked(
                $refused->refusalCode, $refused->getMessage(), $user, $serviceId, $trail, $request
            );

            throw $refused;
        }
    }

    public function __construct(
        private readonly ControlledDrugRegister $register,
        private readonly PrnAdministration $prn,
        private readonly AuditRecorder $audit,
    ) {
    }

    /**
     * Is this witness a real, authorised, different person?
     *
     * Every part of the claim is checked rather than trusted, because a witness
     * is the one control here that exists purely to be somebody else.
     *
     * @return array{ok:bool, code:?string, reason:?string, witness:?User}
     */
    public function checkWitness(?int $witnessId, User $user, Service $house, bool $required): array
    {
        if (! $required) {
            return ['ok' => true, 'code' => null, 'reason' => null, 'witness' => null];
        }

        if ($witnessId === null) {
            return [
                'ok' => false, 'code' => 'witness_missing', 'witness' => null,
                'reason' => 'A second person has to witness this. Ask a colleague who is '
                    .'signed in and authorised to witness medication.',
            ];
        }

        if ($witnessId === $user->id) {
            return [
                'ok' => false, 'code' => 'witness_is_you', 'witness' => null,
                'reason' => 'You cannot witness your own administration. A witness is a '
                    .'second pair of eyes, which means somebody else.',
            ];
        }

        $witness = User::where('id', $witnessId)
            ->where('organisation_id', $house->organisation_id)
            ->first();

        // The same refusal reasons the sign-in screen uses, so a suspended or
        // expired colleague cannot witness anything either.
        if ($witness === null || $witness->accessRefusalReason() !== null) {
            return [
                'ok' => false, 'code' => 'witness_unknown', 'witness' => null,
                'reason' => 'That witness is not an active account in this organisation.',
            ];
        }

        $hasHouse = UserServiceAccess::where('user_id', $witness->id)
            ->where('service_id', $house->id)->exists();

        if (! $hasHouse) {
            return [
                'ok' => false, 'code' => 'witness_wrong_house', 'witness' => null,
                'reason' => 'That witness does not work in this house.',
            ];
        }

        if (! app(AccessPolicy::class)->allows($witness, 'witness_medication', $house->id)) {
            return [
                'ok' => false, 'code' => 'witness_not_authorised', 'witness' => null,
                'reason' => 'That colleague is not authorised to witness medication.',
            ];
        }

        return ['ok' => true, 'code' => null, 'reason' => null, 'witness' => $witness];
    }

    /**
     * Everything a worker needs on screen before two people sign.
     *
     * A witness who cannot see the balance cannot witness the balance, so the
     * current figure and the figure this will leave behind are both here.
     */
    public function describe(Prescription $prescription, Client $client, Service $house): array
    {
        $medicine = $prescription->medicine;
        $snapshot = $this->register->snapshot($medicine, $prescription->dose_unit);
        $balance = $this->register->balanceFor($client, $snapshot);
        $rule = $this->register->witnessRule($house);
        $discrepancy = $this->register->openDiscrepancy($client, $snapshot);

        return [
            'prescriptionId' => $prescription->id,
            'name' => $medicine->name,
            'form' => $medicine->form,
            'strength' => $medicine->strength,
            'schedule' => $medicine->cd_schedule,
            'scheduleUnknown' => $medicine->cd_schedule === null,
            'dose' => $prescription->dose,
            'route' => $prescription->route,
            'unit' => $prescription->dose_unit,
            'unitWord' => $this->register->unitWord(
                $prescription->dose_unit, (float) ($balance?->current_balance ?? 0)
            ),
            'doseUnitWord' => $this->register->unitWord(
                $prescription->dose_unit, (float) ($prescription->dose_max ?? 1)
            ),
            'doseMin' => $prescription->dose_min !== null ? (float) $prescription->dose_min : null,
            'doseMax' => $prescription->dose_max !== null ? (float) $prescription->dose_max : null,
            'indication' => $prescription->prn_indication,
            'kind' => $prescription->kind,

            'balance' => $balance ? $this->register->tidy($balance->current_balance) : null,
            'balanceKnown' => $balance !== null && $balance->last_sequence_no > 0,

            'witnessRequired' => $rule['required'],
            'witnessWhy' => $rule['why'],

            'discrepancy' => $discrepancy === null ? null : [
                'counted' => $this->register->tidy($discrepancy->counted_quantity),
                'expected' => $this->register->tidy($discrepancy->expected_quantity),
                'unit' => $discrepancy->unit,
                // "on" escaped: both letters are date format characters.
                'at' => $discrepancy->occurred_at?->format('H:i \o\n j F'),
            ],
        ];
    }

    /**
     * Record an opening receipt — how a balance legitimately starts.
     *
     * Without one there is no counted stock, and Record7 will not pretend to
     * account for a dose out of a cupboard it has never counted.
     */
    public function receive(
        User $user, Service $house, Client $client, Prescription $prescription,
        float $quantity, ?int $witnessId, ?string $notes, Request $request
    ): CdRegister {
        if ($quantity <= 0) {
            throw new RuntimeException('Say how much came in.');
        }

        $outerTrail = [
            'client_id' => $client->id, 'prescription_id' => $prescription->id,
            'action' => 'receipt', 'quantity' => $quantity,
        ];

        return $this->guarded(fn () => DB::connection('record7')->transaction(function () use (
            $user, $house, $client, $prescription, $quantity, $witnessId, $notes, $request
        ) {
            $snapshot = $this->register->snapshot($prescription->medicine, $prescription->dose_unit);
            $rule = $this->register->witnessRule($house);

            $witnessCheck = $this->checkWitness($witnessId, $user, $house, $rule['required']);

            if (! $witnessCheck['ok']) {
                $this->refuse($witnessCheck['code'], $witnessCheck['reason']);
            }

            $balance = $this->register->lockBalance($client, $house, $snapshot);

            $entry = $this->register->record(
                balance: $balance,
                snapshot: $snapshot,
                action: 'receipt',
                quantities: ['received' => $quantity],
                user: $user,
                witness: $witnessCheck['witness'],
                witnessRequired: $rule['required'],
                unwitnessedBasis: $rule['required'] ? null : 'setting_does_not_require',
                client: $client,
                house: $house,
                prescription: $prescription,
                notes: $notes,
            );

            $this->audit->record(
                eventType: 'controlled_drug_received',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $house->id,
                reason: null,
                riskLevel: 'medium',
                metadata: [
                    'register_id' => $entry->id, 'client_id' => $client->id,
                    'quantity' => $quantity, 'balance_after' => (float) $entry->balance_after,
                    'witness_id' => $witnessCheck['witness']?->id,
                ],
                request: $request
            );

            return $entry;
        }), $user, $house->id, $outerTrail, $request);
    }

    /**
     * Give a controlled drug — scheduled or as required.
     *
     * @return array{administration:Administration, entry:CdRegister, followUp:?PrnFollowUp}
     */
    public function give(
        User $user,
        Service $house,
        Client $client,
        Prescription $prescription,
        ?ScheduledDose $dose,
        float $amount,
        ?int $witnessId,
        ?string $observedReason,
        ?string $notes,
        Request $request,
        ?string $prnAttemptToken = null
    ): array {
        $outerTrail = [
            'client_id' => $client->id,
            'prescription_id' => $prescription->id,
            'scheduled_dose_id' => $dose?->id,
            'amount' => $amount,
        ];

        return $this->guarded(fn () => DB::connection('record7')->transaction(function () use (
            $user, $house, $client, $prescription, $dose, $amount,
            $witnessId, $observedReason, $notes, $request, $prnAttemptToken
        ) {
            $snapshot = $this->register->snapshot($prescription->medicine, $prescription->dose_unit);
            $rule = $this->register->witnessRule($house);
            $trail = [
                'client_id' => $client->id,
                'prescription_id' => $prescription->id,
                'scheduled_dose_id' => $dose?->id,
                'amount' => $amount,
            ];

            // 1. THE LOCK. Everything after this reads settled state.
            $balance = $this->register->lockBalance($client, $house, $snapshot);

            // 2. As-required guards, re-run inside the lock. Section 2.4 owns
            //    these and 2.5 does not get to skip any of them.
            $attempt = null;

            if ($prescription->kind === 'prn') {
                try {
                    $attempt = $this->prn->claimForControlled(
                        $prnAttemptToken, $user, $house->id, $client, $prescription
                    );
                } catch (RuntimeException $attemptRefused) {
                    $this->refuse('prn_attempt_refused', $attemptRefused->getMessage());
                }

                if ($attempt['already'] !== null) {
                    return [
                        'administration' => $attempt['already'],
                        'entry' => CdRegister::find($attempt['already']->cd_register_id),
                        'followUp' => PrnFollowUp::where('administration_id', $attempt['already']->id)->first(),
                        'created' => false,
                    ];
                }

                // The Section 2.4 guards throw their own plain refusals. For a
                // controlled drug a blocked attempt is exactly what somebody
                // asks about afterwards, so they are re-thrown as a controlled
                // refusal and audited like any other.
                try {
                    $this->prn->assertGivable($prescription, $client, $amount, $observedReason, controlled: true);
                } catch (ControlledDrugRefusal $alreadyOurs) {
                    throw $alreadyOurs;
                } catch (RuntimeException $prnGuard) {
                    $this->refuse('prn_guard_refused', $prnGuard->getMessage());
                }
            }

            // 3. Witness.
            $witnessCheck = $this->checkWitness($witnessId, $user, $house, $rule['required']);

            if (! $witnessCheck['ok']) {
                $this->refuse($witnessCheck['code'], $witnessCheck['reason']);
            }

            // 4. FAIL CLOSED. An unresolved disagreement means the balance is
            //    not verified, so nothing can be proved against it.
            $open = $this->register->openDiscrepancy($client, $snapshot);

            if ($open !== null) {
                $reason = 'The count for this medicine does not agree with the register and '
                    .'has not been resolved, so Record7 cannot account for a dose. This does '
                    .'not decide whether the medicine should be given — ask your manager.';

                $this->refuse('open_discrepancy', $reason);
            }

            $can = $this->register->canMove($balance, 'administration', $amount);

            if (! $can['allowed']) {
                $this->refuse($can['code'], $can['reason']);
            }

            // 5. The movement, then the clinical record that carries it.
            $entry = $this->register->record(
                balance: $balance,
                snapshot: $snapshot,
                action: 'administration',
                quantities: ['removed' => $amount, 'given' => $amount, 'returned' => 0, 'wasted' => 0],
                user: $user,
                witness: $witnessCheck['witness'],
                witnessRequired: $rule['required'],
                unwitnessedBasis: $rule['required'] ? null : 'setting_does_not_require',
                client: $client,
                house: $house,
                prescription: $prescription,
                notes: $notes,
            );

            $outcome = $prescription->support_type === 'self_administered'
                ? 'self_administered'
                : 'given';

            $administration = Administration::create([
                'reference' => 'R7C-'.strtoupper(Str::random(12)),
                'scheduled_dose_id' => $dose?->id,
                'prescription_id' => $prescription->id,
                'client_id' => $client->id,
                'service_id' => $house->id,
                'recorded_by_user_id' => $user->id,
                'witnessed_by_user_id' => $witnessCheck['witness']?->id,
                'outcome' => $outcome,
                'reason_code' => $prescription->kind === 'prn' ? $observedReason : null,
                'notes' => $notes,
                'dose_amount' => $amount,
                'dose_unit' => $prescription->dose_unit,
                'administered_at' => now(),
                'cd_register_id' => $entry->id,
            ]);

            $followUp = null;

            if ($prescription->kind === 'prn' && $prescription->prn_review_after_minutes !== null) {
                $followUp = PrnFollowUp::create([
                    'administration_id' => $administration->id,
                    'client_id' => $client->id,
                    'service_id' => $house->id,
                    'due_at' => $administration->administered_at
                        ->copy()->addMinutes((int) $prescription->prn_review_after_minutes),
                    'outcome' => 'pending',
                ]);
            }

            if ($attempt !== null) {
                $this->prn->spend($attempt['attempt'], $administration);
            }

            $this->audit->record(
                eventType: 'controlled_medication_administered',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $house->id,
                reason: null,
                riskLevel: 'high',
                metadata: $trail + [
                    'administration_id' => $administration->id,
                    'register_id' => $entry->id,
                    'witness_id' => $witnessCheck['witness']?->id,
                    'witness_was_required' => $rule['required'],
                    'balance_after' => (float) $entry->balance_after,
                ],
                request: $request
            );

            return [
                'administration' => $administration,
                'entry' => $entry,
                'followUp' => $followUp,
                'created' => true,
            ];
        }), $user, $house->id, $outerTrail, $request);
    }

    /**
     * Removed from storage, and then not given.
     *
     * The clinical outcome and the physical movement are recorded separately
     * and truthfully: a refusal is a refusal, and the stock that came out has
     * to be accounted for as returned intact, destroyed, or some of each.
     * Nothing is guessed — where the outcome is unknown, this refuses rather
     * than inventing a return.
     */
    public function removedButNotGiven(
        User $user,
        Service $house,
        Client $client,
        Prescription $prescription,
        ?ScheduledDose $dose,
        string $outcome,
        string $reasonCode,
        float $removed,
        float $returned,
        float $wasted,
        ?int $witnessId,
        ?string $notes,
        Request $request
    ): array {
        if ($removed <= 0) {
            throw new RuntimeException(
                'If nothing was taken out of storage, record this the ordinary way.'
            );
        }

        if (abs($removed - ($returned + $wasted)) > 0.0001) {
            throw new RuntimeException(
                'Account for all of it: what went back and what was disposed of must '
                .'add up to what came out.'
            );
        }

        $outerTrail = [
            'client_id' => $client->id, 'prescription_id' => $prescription->id,
            'removed' => $removed, 'returned' => $returned, 'wasted' => $wasted,
        ];

        return $this->guarded(fn () => DB::connection('record7')->transaction(function () use (
            $user, $house, $client, $prescription, $dose, $outcome, $reasonCode,
            $removed, $returned, $wasted, $witnessId, $notes, $request
        ) {
            $snapshot = $this->register->snapshot($prescription->medicine, $prescription->dose_unit);
            $rule = $this->register->witnessRule($house);
            $trail = [
                'client_id' => $client->id, 'prescription_id' => $prescription->id,
                'removed' => $removed, 'returned' => $returned, 'wasted' => $wasted,
            ];

            $balance = $this->register->lockBalance($client, $house, $snapshot);

            $witnessCheck = $this->checkWitness($witnessId, $user, $house, $rule['required']);

            if (! $witnessCheck['ok']) {
                $this->refuse($witnessCheck['code'], $witnessCheck['reason']);
            }

            // FAIL CLOSED, exactly as a give does. An episode that takes stock
            // out of a cupboard whose contents Record7 cannot vouch for is not
            // something it can account for, whether or not a dose was given.
            //
            // Counting, correcting and booking in stay open, because those are
            // how somebody investigates and resolves it. Blocking those would
            // trap the disagreement with no way out.
            $open = $this->register->openDiscrepancy($client, $snapshot);

            if ($open !== null) {
                $this->refuse(
                    'open_discrepancy',
                    'The count for this medicine does not agree with the register and has '
                    .'not been resolved, so Record7 cannot account for stock leaving storage. '
                    .'This does not decide whether the medicine should be given — ask your manager.'
                );
            }

            // Only what is destroyed leaves the balance; what goes back never
            // left it. The register still records all three figures.
            $can = $this->register->canMove($balance, 'non_administration', $wasted);

            if (! $can['allowed']) {
                $this->refuse($can['code'], $can['reason']);
            }

            $entry = $this->register->record(
                balance: $balance,
                snapshot: $snapshot,
                action: 'non_administration',
                quantities: [
                    'removed' => $removed, 'given' => 0,
                    'returned' => $returned, 'wasted' => $wasted,
                ],
                user: $user,
                witness: $witnessCheck['witness'],
                witnessRequired: $rule['required'],
                unwitnessedBasis: $rule['required'] ? null : 'setting_does_not_require',
                client: $client,
                house: $house,
                prescription: $prescription,
                notes: $notes,
            );

            $administration = Administration::create([
                'reference' => 'R7C-'.strtoupper(Str::random(12)),
                'scheduled_dose_id' => $dose?->id,
                'prescription_id' => $prescription->id,
                'client_id' => $client->id,
                'service_id' => $house->id,
                'recorded_by_user_id' => $user->id,
                'witnessed_by_user_id' => $witnessCheck['witness']?->id,
                'outcome' => $outcome,
                'reason_code' => $reasonCode,
                'notes' => $notes,
                'administered_at' => now(),

                // Something WAS removed, so the declaration is false and the
                // trigger requires the movement this row carries.
                'controlled_drug_no_quantity_removed' => false,
                'cd_register_id' => $entry->id,
            ]);

            $this->audit->record(
                eventType: 'controlled_medication_not_administered',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $house->id,
                reason: null,
                riskLevel: 'high',
                metadata: $trail + [
                    'administration_id' => $administration->id,
                    'register_id' => $entry->id,
                    'outcome' => $outcome,
                    'witness_id' => $witnessCheck['witness']?->id,
                ],
                request: $request
            );

            return ['administration' => $administration, 'entry' => $entry];
        }), $user, $house->id, $outerTrail, $request);
    }

    /**
     * Correct an earlier entry, without touching it.
     *
     * WHY A SECOND ENTRY AND NOT AN EDIT.
     * The original figure is part of what happened. Somebody wrote it, somebody
     * else may have acted on it, and a register that quietly replaces it is no
     * longer a record of anything. So a correction is a new movement that names
     * the entry it corrects, states the true figure, and moves the balance by
     * the difference. Both stay, and the error stays visible.
     *
     * A correction is the only thing that resolves a discrepancy, because a
     * disagreement between the ledger and the cupboard is settled by somebody
     * accounting for it, never by an acknowledgement.
     */
    public function correct(
        User $user, Service $house, Client $client, Prescription $prescription,
        CdRegister $original, float $trueBalance, ?int $witnessId, string $why, Request $request
    ): CdRegister {
        if (trim($why) === '') {
            throw new RuntimeException('Say what was wrong and how you know.');
        }

        $outerTrail = [
            'client_id' => $client->id, 'prescription_id' => $prescription->id,
            'corrects' => $original->id, 'true_balance' => $trueBalance,
        ];

        return $this->guarded(fn () => DB::connection('record7')->transaction(function () use (
            $user, $house, $client, $prescription, $original, $trueBalance, $witnessId, $why
        ) {
            $snapshot = $this->register->snapshot($prescription->medicine, $prescription->dose_unit);
            $rule = $this->register->witnessRule($house);

            $witnessCheck = $this->checkWitness($witnessId, $user, $house, $rule['required']);

            if (! $witnessCheck['ok']) {
                $this->refuse($witnessCheck['code'], $witnessCheck['reason']);
            }

            $balance = $this->register->lockBalance($client, $house, $snapshot);

            if ((int) $original->client_id !== (int) $client->id) {
                $this->refuse('correction_wrong_person', 'That entry is for somebody else.');
            }

            $entry = $this->register->record(
                balance: $balance,
                snapshot: $snapshot,
                action: 'correction',
                quantities: ['delta' => $trueBalance - (float) $balance->current_balance],
                user: $user,
                witness: $witnessCheck['witness'],
                witnessRequired: $rule['required'],
                unwitnessedBasis: $rule['required'] ? null : 'setting_does_not_require',
                client: $client,
                house: $house,
                prescription: $prescription,
                notes: $why,
                corrects: $original,
            );

            $this->audit->record(
                eventType: 'controlled_drug_corrected',
                result: AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $house->id,
                reason: $why,
                riskLevel: 'high',
                metadata: [
                    'register_id' => $entry->id, 'corrects_register_id' => $original->id,
                    'client_id' => $client->id, 'balance_after' => (float) $entry->balance_after,
                    'witness_id' => $witnessCheck['witness']?->id,
                ],
                request: request()
            );

            return $entry;
        }), $user, $house->id, $outerTrail, $request);
    }

    /**
     * Count what is physically there.
     *
     * The verified figure becomes the balance. The expected figure is kept
     * beside it as evidence of the divergence, never overwritten by it, and a
     * disagreement is flagged rather than tidied away.
     */
    public function count(
        User $user, Service $house, Client $client, Prescription $prescription,
        float $counted, ?int $witnessId, ?string $notes, Request $request
    ): CdRegister {
        if ($counted < 0) {
            throw new RuntimeException('A count cannot be less than nothing.');
        }

        $outerTrail = [
            'client_id' => $client->id, 'prescription_id' => $prescription->id,
            'action' => 'stock_check', 'counted' => $counted,
        ];

        return $this->guarded(fn () => DB::connection('record7')->transaction(function () use (
            $user, $house, $client, $prescription, $counted, $witnessId, $notes, $request
        ) {
            $snapshot = $this->register->snapshot($prescription->medicine, $prescription->dose_unit);
            $rule = $this->register->witnessRule($house);

            $witnessCheck = $this->checkWitness($witnessId, $user, $house, $rule['required']);

            if (! $witnessCheck['ok']) {
                throw new RuntimeException($witnessCheck['reason']);
            }

            $balance = $this->register->lockBalance($client, $house, $snapshot);

            $entry = $this->register->record(
                balance: $balance,
                snapshot: $snapshot,
                action: 'stock_check',
                quantities: ['counted' => $counted],
                user: $user,
                witness: $witnessCheck['witness'],
                witnessRequired: $rule['required'],
                unwitnessedBasis: $rule['required'] ? null : 'setting_does_not_require',
                client: $client,
                house: $house,
                prescription: $prescription,
                notes: $notes,
            );

            $this->audit->record(
                eventType: $entry->is_discrepancy
                    ? 'controlled_drug_discrepancy_found'
                    : 'controlled_drug_counted',
                result: $entry->is_discrepancy ? AuditRecorder::WARNING : AuditRecorder::SUCCESS,
                user: $user,
                serviceId: $house->id,
                reason: $entry->is_discrepancy
                    ? 'A controlled drug count does not agree with the register.'
                    : null,
                riskLevel: $entry->is_discrepancy ? 'high' : 'low',
                metadata: [
                    'register_id' => $entry->id, 'client_id' => $client->id,
                    'expected' => (float) $entry->expected_quantity,
                    'counted' => (float) $entry->counted_quantity,
                ],
                request: $request
            );

            return $entry;
        }), $user, $house->id, $outerTrail, $request);
    }
}
