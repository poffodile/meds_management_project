<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\IssueState;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\WelfareCheck;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Section 2.2 — recording that a planned medicine was actually given.
 *
 * ONE OUTCOME, AND ONLY THE SUCCESSFUL ONE.
 * Refusal, omission, unavailability and re-offer are Section 2.3. Nothing here
 * can express "not given", and a screen offering a half-built version of that
 * would collect worse records than no record at all.
 *
 * THE PLANNED DOSE IS THE ANCHOR, NOT A DETAIL.
 * An administration is always ABOUT a specific planned obligation. It never
 * floats free of one, the planned row is never edited or removed to mark it
 * done, and the scheduled time is never overwritten with the time somebody
 * happened to press the button. Those are two different facts, and a medicines
 * record that conflates them cannot answer "was it late?" a month later.
 *
 * THE DATABASE SETTLES DUPLICATES, NOT THIS CLASS.
 * Two phones, a double tap, a retry after a timeout, two tabs — all of them put
 * two requests in flight, and every "has this been recorded already?" check has
 * a gap between the question and the answer. So the insert is simply attempted,
 * and a unique-constraint violation is read as "somebody else got there first"
 * and answered with THEIR record. Administrations cannot be deleted, so a
 * duplicate is not something anybody can tidy up afterwards.
 *
 * WHAT IT REFUSES, AND WHY EACH REFUSAL EXISTS
 *   already recorded    a permanent record already answers this obligation
 *   not in this round   the id came from a browser and proves nothing
 *   person away         nobody hands a tablet to somebody in hospital
 *   controlled drug     needs a witness, and that is Section 2.5
 *   as-required         PRN has its own reasoning, and that is Section 2.4
 *   prompted            staff did not administer it, so "given" would be false
 *   self-administered   the person is authorised to take it themselves
 *   prescription state  suspended or stopped is not something to hand over
 */
class AdministrationRecorder
{
    /**
     * Arrangements this section can honestly record as "given".
     *
     * STAFF ADMINISTERED is the ordinary case: the worker hands it over.
     *
     * ASSISTED is included because the worker is physically part of the
     * administration — steadying a hand, holding the cup — which is what
     * "given" means and what a paper MAR chart has always been signed for.
     *
     * PROMPTED is NOT included. There the worker reminds and watches; the
     * person takes it themselves. Recording "given by Noah" would put a false
     * statement about who administered a medicine into a record that can never
     * be deleted. It needs an outcome this enum cannot currently express, so it
     * waits rather than being approximated.
     */
    public const CAN_BE_GIVEN = ['staff_administered', 'assisted'];

    /** What the worker is actually confirming, in their own words. */
    public const CONFIRMATION = [
        'staff_administered' => 'You are recording that you gave this medicine.',
        'assisted' => 'You are recording that you helped them to take this medicine.',
    ];

    public const REFUSAL_REASONS = [
        'client_declined',
        'disliked_form_or_taste',
        'felt_unwell',
        'no_reason_given',
    ];

    public const PERSON_UNAVAILABLE_REASONS = [
        'in_hospital',
        'away_on_leave',
        'at_appointment',
        'not_found_in_service',
    ];

    public const MEDICINE_UNAVAILABLE_REASONS = [
        'stock_unavailable',
        'awaiting_delivery',
        'damaged_or_expired',
        'wrong_item_supplied',
    ];

    public const MISSED_REASONS = [
        'round_not_completed',
        'overlooked',
        'staffing_shortfall',
        'discovered_later',
    ];

    /**
     * The words staff read, for every stored code.
     *
     * A code is what the record keeps; it is not what a worker should be asked
     * to choose from at half past seven in a corridor. "no_reason_given" on a
     * screen is a database column leaking into somebody's working day.
     */
    public const REASON_WORDS = [
        'client_declined' => 'They said no',
        'disliked_form_or_taste' => 'They did not like the taste or the form',
        'felt_unwell' => 'They felt unwell',
        'no_reason_given' => 'They gave no reason',

        'in_hospital' => 'In hospital',
        'away_on_leave' => 'Away on leave',
        'at_appointment' => 'Out at an appointment',
        'not_found_in_service' => 'Could not be found here',

        'stock_unavailable' => 'None in stock',
        'awaiting_delivery' => 'Waiting on a delivery',
        'damaged_or_expired' => 'Damaged or out of date',
        'wrong_item_supplied' => 'The wrong item was supplied',

        'round_not_completed' => 'The round was not finished',
        'overlooked' => 'It was overlooked',
        'staffing_shortfall' => 'There were not enough staff',
        'discovered_later' => 'Found afterwards, during a check',
    ];

    /** What was actually done about a missed dose, in the same plain words. */
    public const MISSED_ACTION_WORDS = [
        'manager_notified' => 'Told the manager',
        'medication_lead_notified' => 'Told the medication lead',
        'pharmacist_contacted' => 'Spoke to the pharmacist',
        'prescriber_contacted' => 'Spoke to the prescriber or GP',
        'nhs_111_contacted' => 'Called NHS 111',
        'emergency_services_contacted' => 'Called the emergency services',
        'no_escalation_required_under_policy' => 'No escalation needed under our policy',
    ];

    /**
     * How a second offer can end.
     *
     * They took it, or they said no again. A re-offer is an OFFER — the other
     * outcomes describe what happened to the original obligation rather than
     * how an offer went, and filing them here would put the wrong shape of
     * fact on the chain.
     */
    public const REOFFER_OUTCOMES = ['given', 'self_administered', 'refused'];

    public const MISSED_ACTIONS = [
        'manager_notified',
        'medication_lead_notified',
        'pharmacist_contacted',
        'prescriber_contacted',
        'nhs_111_contacted',
        'emergency_services_contacted',
        'no_escalation_required_under_policy',
    ];

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly StockLedger $stock,
    ) {
    }

    /* ── Section 2.7: what this dose does to the cupboard ─────────────────── */

    /**
     * What Record7 knows about the stock behind one scheduled dose.
     *
     * Three honest states, and the screens name all three rather than letting
     * silence look like success:
     *
     *   untracked      nobody has counted this medicine for this person, so a
     *                  dose changes nothing and says nothing;
     *   unquantified   it is counted, but the prescription carries no fixed
     *                  structured dose, so a debit would be a guess;
     *   tracked        a quantity is known and the balance moves.
     *
     * The quantity comes from the structured columns or not at all.
     * `record7_prescriptions.dose` is display text — legacy audit CR-02 is what
     * happens when arithmetic reads it.
     *
     * @return array{state:string, balance:?\App\Models\Record7\StockBalance, snapshot:?array,
     *               quantity:?float, sufficient:bool, shortfall:float, unit:?string}
     */
    public function stockPosition(Client $client, ScheduledDose $dose, ?float $recorded = null): array
    {
        $none = [
            'state' => 'untracked', 'balance' => null, 'snapshot' => null,
            'quantity' => null, 'sufficient' => true, 'shortfall' => 0.0, 'unit' => null,
        ];

        $prescription = $dose->prescription;
        $medicine = $prescription?->medicine;

        // Controlled medicines are Section 2.5's, entirely.
        if (! $prescription || ! $medicine || $medicine->is_controlled) {
            return $none;
        }

        // A medicine the person holds and manages themselves is not in stock
        // Record7 accounts for, so no dose of it moves a balance.
        if (! $this->stock->consumesAccountedStock($prescription)) {
            // Union keeps the LEFT side's keys, so the override goes first.
            return ['state' => 'self_managed'] + $none;
        }

        if ($prescription->dose_unit === null || trim((string) $prescription->dose_unit) === '') {
            return $none;
        }

        $snapshot = $this->stock->snapshot($medicine, $prescription->dose_unit);
        $balance = $this->stock->balanceFor($client, $snapshot);

        if (! $balance) {
            return $none;
        }

        $quantity = $this->stock->doseQuantity($prescription, $recorded);

        if ($quantity === null) {
            return [
                'state' => 'unquantified', 'balance' => $balance, 'snapshot' => $snapshot,
                'quantity' => null, 'sufficient' => true, 'shortfall' => 0.0,
                'unit' => $snapshot['unit'],
            ];
        }

        $cover = $this->stock->canCover($balance, 'administration', $quantity);

        return [
            'state' => 'tracked',
            'balance' => $balance,
            'snapshot' => $snapshot,
            'quantity' => $quantity,
            'sufficient' => $cover['sufficient'],
            'shortfall' => $cover['shortfall'],
            'unit' => $snapshot['unit'],
        ];
    }

    /**
     * Move the stock a recorded dose actually consumed.
     *
     * Called inside the transaction that will write the administration, so the
     * two stand or fall together. The balance is locked before any arithmetic
     * and the head moves last, inside the same lock.
     *
     * Returns null where there is nothing to move — an untracked medicine, an
     * unquantified prescription, or a controlled one.
     */
    private function debitForDose(
        User $user, Round $round, Client $client, ScheduledDose $dose, array $shortfallInput
    ): ?\App\Models\Record7\StockMovement {
        $position = $this->stockPosition($client, $dose);

        if ($position['state'] !== 'tracked') {
            return null;
        }

        $house = Service::findOrFail($round->service_id);
        $balance = $this->stock->lockExisting($position['balance']);
        $quantity = (float) $position['quantity'];

        // RE-ASKED UNDER THE LOCK. What was true a moment ago is not evidence.
        $cover = $this->stock->canCover($balance, 'administration', $quantity);
        $shortfall = null;

        if (! $cover['sufficient']) {
            // The record shows less than this dose needs. The dose is not
            // refused — somebody checked and it was there — but Record7 will
            // not claim a balance it cannot support, so the verification is
            // required and the resulting position is kept, negative and all.
            $shortfall = $this->stock->verifyShortfall($user, $shortfallInput);
        }

        return $this->stock->record(
            balance: $balance,
            snapshot: $position['snapshot'],
            action: 'administration',
            quantities: ['removed' => $quantity, 'given' => $quantity],
            user: $user,
            client: $client,
            house: $house,
            prescription: $dose->prescription,
            shortfall: $shortfall,
        );
    }

    /**
     * Find the dose, or refuse to admit it exists.
     *
     * Every filter is applied in the query rather than checked afterwards, so a
     * dose belonging to another organisation, another house, another person,
     * another date or another slot is not found rather than merely rejected —
     * and the reply cannot be used to discover that it exists at all.
     */
    public function resolve(Round $round, Client $client, int $doseId): ?ScheduledDose
    {
        return ScheduledDose::with(['prescription.medicine', 'administration'])
            ->where('id', $doseId)
            ->where('client_id', $client->id)
            ->where('service_id', $round->service_id)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->where('slot', $round->slot)
            ->whereHas('client', fn ($q) => $q->where('organisation_id', $round->organisation_id))
            ->first();
    }

    /**
     * May this dose be recorded as given, and if not, what is the worker told?
     *
     * Order matters. "Already recorded" comes first because it is the answer to
     * a retry rather than a refusal, and a worker who has just pressed the
     * button twice should be told what happened, not lectured about controlled
     * drugs.
     *
     * @return array{allowed:bool, code:?string, reason:?string, nextSection:?string}
     */
    public function eligibility(ScheduledDose $dose, Client $client, bool $asReoffer = false): array
    {
        $prescription = $dose->prescription;
        $medicine = $prescription?->medicine;

        // A re-offer exists BECAUSE an answer already exists. Every other check
        // below still applies to it — support type, controlled drugs, PRN, the
        // person being here — so a second offer can never sidestep a safeguard
        // the first one was held to.
        if (! $asReoffer && $dose->administration !== null) {
            return $this->no(
                'already_recorded',
                'This dose already has a recorded outcome. It cannot be recorded twice.'
            );
        }

        if (! $prescription) {
            return $this->no('no_prescription', 'This dose has no prescription attached to it.');
        }

        if ($prescription->status !== 'active') {
            return $this->no(
                'prescription_'.$prescription->status,
                'This prescription is '.$prescription->status.'. Do not give it without asking.'
            );
        }

        // PRN reasoning is a different question entirely — was it needed, has
        // enough time passed, did it work afterwards. Section 2.4.
        if ($prescription->kind === 'prn') {
            return $this->no(
                'as_required',
                'This is an as-required medicine. Record it on the as-required screen, '
                .'where the reason for giving it and whether it worked are recorded too.',
                '2.4'
            );
        }

        if ($prescription->support_type === 'self_administered'
            && $prescription->self_administration_monitoring === 'none') {
            return $this->no(
                'self_managed',
                'This medicine is fully self-managed. It does not need individual staff dose recording.',
                null
            );
        }

        // A controlled drug needs a second person to witness it. The general
        // "given" button existing is not a reason to skip that.
        if ($medicine?->is_controlled) {
            return $this->no(
                'witness_required',
                'This is a controlled drug. It needs a witness and a register entry, so it '
                .'is recorded on the controlled drug screen rather than here.',
                '2.5'
            );
        }

        if (! in_array($prescription->support_type, self::CAN_BE_GIVEN, true)) {
            return $this->no(
                'support_type_'.$prescription->support_type,
                $prescription->support_type === 'self_administered'
                    ? 'They are authorised to take this themselves. Recording it as given by '
                        .'staff would say somebody handed it to them.'
                    : 'They take this themselves after a reminder. Recording it as given by '
                        .'staff would say somebody handed it to them.',
                '2.3'
            );
        }

        // Callum is in hospital. His dose stays planned and still needs an
        // answer — but the answer is not "given".
        if (! $client->isAvailable()) {
            return $this->no(
                'person_away',
                $client->statusWord().'. This cannot be recorded as given while they are away; '
                .'it needs an outcome saying why it was not given.',
                '2.3'
            );
        }

        return ['allowed' => true, 'code' => null, 'reason' => null, 'nextSection' => null];
    }

    /**
     * Write it, or hand back the record that already exists.
     *
     * @return array{administration:Administration, created:bool}
     */
    public function recordGiven(
        User $user,
        Round $round,
        Client $client,
        ScheduledDose $dose,
        ?string $notes,
        Request $request,
        ?Administration $reofferOf = null
    ): array {
        // SECTION 2.7. The stock movement and the clinical record describe one
        // physical event and are written together. The movement goes first
        // because the administration carries the reference: the other direction
        // would mean writing the ledger row with a null link and UPDATING it,
        // which is exactly what an append-only ledger must never allow.
        //
        // If the administration insert fails, the movement rolls back with it.
        // There is no state in which a debit exists for a dose nobody recorded.
        $shortfallInput = $request->only([
            'shortfall_basis', 'shortfall_statement', 'shortfall_observed_quantity',
        ]);

        try {
            $administration = $this->stock->guarded(
                fn () => DB::connection('record7')->transaction(function () use (
                    $user, $round, $client, $dose, $notes, $reofferOf, $shortfallInput
                ) {
                    $movement = $this->debitForDose($user, $round, $client, $dose, $shortfallInput);

                    return Administration::create([
                        'reference' => 'R7A-'.strtoupper(Str::random(12)),
                        'scheduled_dose_id' => $dose->id,
                        'prescription_id' => $dose->prescription_id,
                        'client_id' => $client->id,
                        'service_id' => $round->service_id,

                        // THE AUTHENTICATED USER. Never an id from the request — a
                        // worker must not be able to sign a medicine in somebody
                        // else's name, and joining a colleague's round must not make
                        // your actions look like theirs.
                        'recorded_by_user_id' => $user->id,

                        'outcome' => 'given',

                        // No reason code. A medicine given as prescribed does not need
                        // one, and forcing a choice would fill the record with filler.
                        'reason_code' => null,
                        'notes' => $this->cleanNotes($notes),

                        // The server's clock. A browser can say anything, and the time
                        // a medicine was given is a clinical fact. The dose's own
                        // due_at is left exactly as it was.
                        'administered_at' => now(),

                        // Set only when this is a second offer of the same dose. The
                        // refusal it follows is never touched — it stays exactly as it
                        // was recorded, and the two rows together tell the real story.
                        'reoffer_of_administration_id' => $reofferOf?->id,

                        'stock_movement_id' => $movement?->id,
                    ]);
                }),
                $user,
                $round->service_id,
                ['client_id' => $client->id, 'scheduled_dose_id' => $dose->id, 'outcome' => 'given'],
                $request
            );
        } catch (UniqueConstraintViolationException $clash) {
            // Somebody — or another request from this same worker — got there
            // first. The obligation is answered, so this is a safe outcome
            // rather than an error. Hand back THEIR record.
            $existing = Administration::where('scheduled_dose_id', $dose->id)
                ->whereNull('corrects_administration_id')
                ->where(fn ($q) => $reofferOf
                    ? $q->where('reoffer_of_administration_id', $reofferOf->id)
                    : $q->whereNull('reoffer_of_administration_id'))
                ->first();

            if (! $existing) {
                throw $clash;
            }

            $this->audit->record(
                eventType: 'medication_administration_duplicate_blocked',
                result: AuditRecorder::WARNING,
                user: $user,
                serviceId: $round->service_id,
                reason: 'A second administration was attempted for a dose already recorded.',
                riskLevel: 'medium',
                metadata: $this->trail($round, $client, $dose, $existing),
                request: $request
            );

            return ['administration' => $existing, 'created' => false];
        }

        $this->audit->record(
            eventType: 'medication_administered',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $round->service_id,
            reason: null,
            riskLevel: 'medium',
            metadata: $this->trail($round, $client, $dose, $administration),
            request: $request
        );

        return ['administration' => $administration, 'created' => true];
    }

    /**
     * Record an explicit Section 2.3 outcome for a planned dose.
     *
     * This shares the same resolved round/client/dose context as Section 2.2.
     * The method still validates every clinical boundary again because the
     * posted outcome is only an assertion from a browser, not authority.
     *
     * @return array{administration:Administration, created:bool}
     */
    public function recordNonAdministration(
        User $user,
        Round $round,
        Client $client,
        ScheduledDose $dose,
        string $outcome,
        array $input,
        Request $request
    ): array {
        $prescription = $dose->prescription;
        $medicine = $prescription?->medicine;

        if (! in_array($outcome, ['refused', 'person_unavailable', 'not_available', 'missed'], true)) {
            throw new RuntimeException('That outcome is not available in Section 2.3.');
        }

        if (! $prescription) {
            throw new RuntimeException('This dose has no prescription attached to it.');
        }

        if ($prescription->kind === 'prn') {
            throw new RuntimeException('As-required medicines are Section 2.4.');
        }

        if ($outcome === 'withheld') {
            throw new RuntimeException('Withheld recording is not part of Section 2.3.');
        }

        if ($medicine?->is_controlled && ! (bool) ($input['controlled_drug_no_quantity_removed'] ?? false)) {
            throw new RuntimeException(
                'For a controlled drug, confirm no quantity was removed from secure storage. '
                .'If any quantity was removed, use the controlled-drug pathway.'
            );
        }

        $reason = $this->requireReason($outcome, $input['reason_code'] ?? null);
        $notes = $this->cleanMeaningful($input['notes'] ?? null, $outcome === 'missed');
        $actionTaken = $this->cleanMeaningful($input['action_taken'] ?? null, $outcome === 'missed');
        $immediateAction = $input['immediate_action_code'] ?? null;

        if ($outcome === 'missed' && ! in_array($immediateAction, self::MISSED_ACTIONS, true)) {
            throw new RuntimeException('Choose the immediate action taken for the missed dose.');
        }

        $target = $this->reofferTarget($round, $client, $dose, $outcome, $input['reoffer_of_administration_id'] ?? null);

        /* ── Section 2.7: did any of it physically leave the cupboard? ──── */

        // Only a preparation that is being counted, and whose dose is known,
        // has anything to declare. Anywhere else the honest answer is silence,
        // and demanding one would be demanding a guess.
        $position = $this->stockPosition($client, $dose);
        $declaration = $this->stockDeclaration($position, $input);

        try {
            $administration = $this->stock->guarded(
                fn () => DB::connection('record7')->transaction(function () use (
                    $user, $round, $client, $dose, $outcome, $reason, $notes, $actionTaken,
                    $immediateAction, $medicine, $target, $position, $declaration
                ) {
                    $movement = $declaration['removed']
                        ? $this->accountForRemoval($user, $round, $client, $dose, $position, $declaration)
                        : null;

                    return Administration::create([
                        'reference' => 'R7N-'.strtoupper(Str::random(12)),
                        'scheduled_dose_id' => $dose->id,
                        'prescription_id' => $dose->prescription_id,
                        'client_id' => $client->id,
                        'service_id' => $round->service_id,
                        'recorded_by_user_id' => $user->id,
                        'outcome' => $outcome,
                        'reason_code' => $reason,
                        'notes' => $notes,
                        'action_taken' => $actionTaken,
                        'immediate_action_code' => $outcome === 'missed' ? $immediateAction : null,
                        'controlled_drug_no_quantity_removed' => $medicine?->is_controlled ? true : null,
                        'administered_at' => now(),
                        'reoffer_of_administration_id' => $target?->id,
                        'stock_no_quantity_removed' => $declaration['declared'],
                        'stock_movement_id' => $movement?->id,
                    ]);
                }),
                $user,
                $round->service_id,
                ['client_id' => $client->id, 'scheduled_dose_id' => $dose->id, 'outcome' => $outcome],
                $request
            );
        } catch (UniqueConstraintViolationException $clash) {
            $existing = Administration::where('scheduled_dose_id', $dose->id)
                ->whereNull('corrects_administration_id')
                ->where('reoffer_of_administration_id', $target?->id)
                ->first();

            if (! $existing) {
                throw $clash;
            }

            return ['administration' => $existing, 'created' => false];
        }

        if ($outcome === 'person_unavailable' && $reason === 'not_found_in_service') {
            $this->createWelfareAttention($round, $administration);
        }

        $this->audit->record(
            eventType: 'medication_non_administration_recorded',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $round->service_id,
            reason: null,
            riskLevel: $outcome === 'missed' ? 'high' : 'medium',
            metadata: $this->trail($round, $client, $dose, $administration) + [
                'reason_code' => $reason,
                'reoffer_of_administration_id' => $target?->id,
            ],
            request: $request
        );

        return ['administration' => $administration, 'created' => true];
    }

    /**
     * What the recorder said about the cupboard, checked before anything moves.
     *
     * PHYSICAL MOVEMENT AND MAR OUTCOME ARE SEPARATE FACTS. A refusal where the
     * tablet never left the pot moves nothing; the same refusal where it came
     * out and went in the bin moves something. Only the person who was there
     * knows which, so they are asked — and the answer is frozen on insert like
     * every other clinical fact, in the model and in the trigger.
     *
     * @return array{declared:?bool, removed:bool, returned:float, wasted:float}
     */
    private function stockDeclaration(array $position, array $input): array
    {
        if ($position['state'] !== 'tracked') {
            // Nothing is being counted, or no quantity is knowable. There is
            // no question to answer, so none is recorded.
            return ['declared' => null, 'removed' => false, 'returned' => 0.0, 'wasted' => 0.0];
        }

        $declared = $input['stock_no_quantity_removed'] ?? null;

        if ($declared === null || $declared === '') {
            throw new RuntimeException(
                'Say whether any of this medicine was taken out of stock.'
            );
        }

        $noneRemoved = filter_var($declared, FILTER_VALIDATE_BOOLEAN);

        if ($noneRemoved) {
            return ['declared' => true, 'removed' => false, 'returned' => 0.0, 'wasted' => 0.0];
        }

        $returned = (float) ($input['stock_quantity_returned'] ?? 0);
        $wasted = (float) ($input['stock_quantity_wasted'] ?? 0);

        if ($returned < 0 || $wasted < 0) {
            throw new RuntimeException('A quantity cannot be less than nothing.');
        }

        if ($returned + $wasted <= 0) {
            throw new RuntimeException(
                'Say how much went back into stock and how much was disposed of.'
            );
        }

        return [
            'declared' => false,
            'removed' => true,
            'returned' => $returned,
            'wasted' => $wasted,
        ];
    }

    /**
     * One movement for one physical episode: what came out, and where it went.
     *
     * Deliberately not a removal followed by a separate return. Two movements
     * would let a crash between them leave a removal with nothing accounting
     * for it, which is the state a ledger exists to make impossible.
     */
    private function accountForRemoval(
        User $user, Round $round, Client $client, ScheduledDose $dose,
        array $position, array $declaration
    ): \App\Models\Record7\StockMovement {
        $house = Service::findOrFail($round->service_id);
        $balance = $this->stock->lockExisting($position['balance']);

        $removed = $declaration['returned'] + $declaration['wasted'];

        // Only what was destroyed leaves the balance. What went back was never
        // really gone, and recording it as a debit and a credit would say twice
        // that something happened once.
        $cover = $this->stock->canCover($balance, 'non_administration', $declaration['wasted']);

        if (! $cover['sufficient']) {
            $this->stock->refuse(
                'insufficient_for_waste',
                'The record shows less of this medicine than that. Count what is physically '
                .'there and record it before disposing of any.'
            );
        }

        return $this->stock->record(
            balance: $balance,
            snapshot: $position['snapshot'],
            action: 'non_administration',
            quantities: [
                'removed' => $removed,
                'given' => 0,
                'returned' => $declaration['returned'],
                'wasted' => $declaration['wasted'],
            ],
            user: $user,
            client: $client,
            house: $house,
            prescription: $dose->prescription,
        );
    }

    /**
     * The structured trail, and nothing beyond it.
     *
     * Identifiers, both times and the outcome — enough to reconstruct exactly
     * what was recorded and by whom. Deliberately no clinical free text: the
     * notes belong on the clinical record, and copying them into the access
     * audit would spread the same sensitive sentence across two tables with two
     * different retention rules.
     */
    private function trail(Round $round, Client $client, ScheduledDose $dose, Administration $administration): array
    {
        return [
            'round_id' => $round->id,
            'slot' => $round->slot,
            'client_id' => $client->id,
            'prescription_id' => $dose->prescription_id,
            'medicine_id' => $dose->prescription?->medicine_id,
            'scheduled_dose_id' => $dose->id,
            'support_type' => $dose->prescription?->support_type,

            // BOTH times. Either one alone makes lateness unanswerable later.
            'due_at' => $dose->due_at->toIso8601String(),
            'administered_at' => $administration->administered_at->toIso8601String(),
            'minutes_late' => $dose->minutesLate($administration->administered_at),

            'outcome' => $administration->outcome,
            'administration_id' => $administration->id,
            'administration_reference' => $administration->reference,
        ];
    }

    /** Empty is null, not an empty string pretending to be a note. */
    private function cleanNotes(?string $notes): ?string
    {
        $notes = trim((string) $notes);

        return $notes === '' ? null : Str::limit($notes, 495, '');
    }

    private function requireReason(string $outcome, ?string $reason): string
    {
        $allowed = match ($outcome) {
            'refused' => self::REFUSAL_REASONS,
            'person_unavailable' => self::PERSON_UNAVAILABLE_REASONS,
            'not_available' => self::MEDICINE_UNAVAILABLE_REASONS,
            'missed' => self::MISSED_REASONS,
            default => [],
        };

        if (! in_array($reason, $allowed, true)) {
            throw new RuntimeException('Choose the structured reason for this outcome.');
        }

        return $reason;
    }

    private function cleanMeaningful(?string $value, bool $required): ?string
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $value));

        if ($clean === '') {
            if ($required) {
                throw new RuntimeException('Write a meaningful explanation.');
            }

            return null;
        }

        $normalised = strtolower(str_replace(['.', '/', '\\', '_'], '', $clean));

        if (in_array($normalised, ['na', 'none', 'nil', 'no', 'notapplicable', '-'], true)
            || preg_match('/^-+$/', $clean)) {
            throw new RuntimeException('Write a meaningful explanation, not filler.');
        }

        return Str::limit($clean, 495, '');
    }

    private function reofferTarget(
        Round $round,
        Client $client,
        ScheduledDose $dose,
        string $outcome,
        mixed $targetId
    ): ?Administration {
        if ($targetId === null || $targetId === '') {
            return null;
        }

        // A second offer can end two ways: they take it, or they say no again.
        // Both are answers to the SAME planned dose and both belong on the
        // chain. Anything else — the medicine running out, the person leaving,
        // the round being missed — is a fact about the original obligation, not
        // an answer to an offer, so it cannot be filed as one.
        if (! in_array($outcome, self::REOFFER_OUTCOMES, true)) {
            throw new RuntimeException(
                'A re-offer records whether they took it or refused it again. Nothing else.'
            );
        }

        // Every filter is in the query, so a refusal belonging to another
        // person, house, prescription or dose is not found rather than merely
        // rejected. The database trigger asserts the same thing again for
        // anything that bypasses this class entirely.
        $target = Administration::where('service_id', $round->service_id)
            ->where('client_id', $client->id)
            ->where('scheduled_dose_id', $dose->id)
            ->where('prescription_id', $dose->prescription_id)
            ->where('outcome', 'refused')
            ->find((int) $targetId);

        if (! $target) {
            throw new RuntimeException('A re-offer must refer to the original refusal for this same dose.');
        }

        return $target;
    }

    /**
     * Record that somebody went and looked.
     *
     * The ONLY thing that answers a "could not be found" concern. Everything
     * about it is resolved from the authenticated session and the concern
     * itself — the actor, the house, the organisation, the person — so a
     * posted id cannot attach evidence to somebody else's concern, and the
     * database asserts the same thing again underneath.
     *
     * Deliberately NOT a safeguarding classification. Recording that you found
     * somebody says exactly that and nothing more; whether it becomes a
     * safeguarding matter is a judgement for a manager and the provider's own
     * policy, not something a medicines round decides on their behalf.
     *
     * @return array{check:WelfareCheck, created:bool}
     */
    public function recordWelfareCheck(
        User $user,
        int $serviceId,
        Administration $report,
        string $resolutionType,
        ?string $note,
        Request $request
    ): array {
        if (! array_key_exists($resolutionType, WelfareCheck::RESOLUTION_WORDS)) {
            throw new RuntimeException('Say what you actually found.');
        }

        if ($report->outcome !== 'person_unavailable'
            || $report->reason_code !== 'not_found_in_service'
            || (int) $report->service_id !== $serviceId) {
            throw new RuntimeException('That is not a could-not-be-found record for this house.');
        }

        $service = Service::findOrFail($serviceId);

        try {
            $check = WelfareCheck::create([
                'reference' => 'R7W-'.strtoupper(Str::random(12)),
                'organisation_id' => $service->organisation_id,
                'service_id' => $serviceId,
                'client_id' => $report->client_id,
                'administration_id' => $report->id,
                'resolution_type' => $resolutionType,
                'note' => $this->cleanNotes($note),
                'recorded_by_user_id' => $user->id,
                'occurred_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException $clash) {
            // Somebody else already answered it. That is a safe outcome, not an
            // error — hand back what they recorded.
            $existing = WelfareCheck::where('administration_id', $report->id)->first();

            if (! $existing) {
                throw $clash;
            }

            return ['check' => $existing, 'created' => false];
        }

        $this->audit->record(
            eventType: 'welfare_check_recorded',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: null,
            riskLevel: 'medium',
            metadata: [
                'welfare_check_id' => $check->id,
                'administration_id' => $report->id,
                'client_id' => $report->client_id,
                'resolution_type' => $resolutionType,
                'occurred_at' => $check->occurred_at->toIso8601String(),
            ],
            request: $request
        );

        return ['check' => $check, 'created' => true];
    }

    /**
     * The unanswered "could not be found" concern for this person, if there is
     * one — so a screen can offer to answer it and nothing else can.
     */
    public function openWelfareConcernFor(int $serviceId, int $clientId): ?Administration
    {
        return Administration::where('service_id', $serviceId)
            ->where('client_id', $clientId)
            ->where('outcome', 'person_unavailable')
            ->where('reason_code', 'not_found_in_service')
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('record7_welfare_checks')
                ->whereColumn('record7_welfare_checks.administration_id', 'record7_administrations.id'))
            ->orderByDesc('id')
            ->first();
    }

    /**
     * The refusal on this dose that is still waiting for a second offer, if
     * there is one.
     *
     * A refusal that has already been offered again is closed to further
     * attempts — the next attempt chains from THAT answer instead, so two
     * workers cannot both re-offer the same refusal and produce two competing
     * second attempts.
     */
    public function openRefusalFor(ScheduledDose $dose): ?Administration
    {
        $refusals = Administration::where('scheduled_dose_id', $dose->id)
            ->where('outcome', 'refused')
            ->orderBy('id')
            ->get();

        return $refusals->first(fn ($refusal) => ! Administration::where(
            'reoffer_of_administration_id', $refusal->id
        )->exists());
    }

    private function createWelfareAttention(Round $round, Administration $administration): void
    {
        $service = Service::findOrFail($round->service_id);

        IssueState::firstOrCreate([
            'organisation_id' => $service->organisation_id,
            'service_id' => $round->service_id,
            'issue_type' => 'welfare_check',
            'source_id' => $administration->id,
        ], [
            'issue_key' => 'welfare_check:'.$administration->id,
            'acknowledged_at' => null,
        ]);
    }

    private function no(string $code, string $reason, ?string $nextSection = null): array
    {
        return [
            'allowed' => false,
            'code' => $code,
            'reason' => $reason,
            'nextSection' => $nextSection,
        ];
    }
}
