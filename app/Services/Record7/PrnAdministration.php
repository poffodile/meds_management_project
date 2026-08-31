<?php

namespace App\Services\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnAttempt;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Section 2.4 — a medicine given because somebody needed it, not because the
 * clock said so.
 *
 * WHY THIS IS NOT THE ROUND.
 * A person in pain at three in the morning is not a round, and requiring one
 * would mean either refusing them or opening a fake round to get past the
 * software. So PRN is reached through the person, at any hour, and carries its
 * own authority check rather than borrowing the round's.
 *
 * NO SCHEDULED DOSE, EVER.
 * A PRN answers a need, not a plan. Giving it a scheduled dose to reuse the
 * Section 2.2 code would invent an obligation nobody had, make it show up as
 * outstanding work, and let the one-answer-per-dose constraint refuse a second
 * dose the prescription expressly permits.
 *
 * WHAT ACTUALLY GUARDS A DOSE
 *   the medicine is not controlled            controlled PRN is Section 2.5
 *   the prescription is active and is PRN
 *   the person is here
 *   enough time since the last one             from the prescription, if stated
 *   within the count limit                     from the prescription, if stated
 *   within the amount limit                    from the prescription, if stated
 *   the dose is inside the permitted range     from the prescription, if stated
 *
 * EVERY ONE OF THOSE IS "IF STATED".
 * Where a prescription is silent, nothing is enforced and nothing is invented.
 * A limit nobody wrote down is a limit nobody agreed, and a made-up maximum
 * that blocks a needed dose is as dangerous as a missing one that permits too
 * many.
 *
 * ONLY A DOSE THAT WAS ACTUALLY GIVEN COUNTS.
 * A refusal, an absence, a medicine that was not there — all are real records
 * and none of them is a dose. Somebody who declined at two and asks at three
 * has had nothing, and must not be locked out by their own refusal.
 */
class PrnAdministration
{
    /** What staff observed, at the moment they gave it. */
    public const OBSERVED_REASONS = [
        'reported_pain' => 'They said they were in pain',
        'observed_distress' => 'They seemed distressed',
        'reported_breathless' => 'They said they were breathless',
        'observed_breathless' => 'They seemed breathless',
        'reported_nausea' => 'They said they felt sick',
        'requested_by_person' => 'They asked for it',
        'other_recorded_below' => 'Something else — written below',
    ];

    /** What was done about a response that worried somebody. */
    public const CONCERN_ACTIONS = [
        'manager_notified' => 'Told the manager',
        'medication_lead_notified' => 'Told the medication lead',
        'prescriber_contacted' => 'Spoke to the prescriber or GP',
        'pharmacist_contacted' => 'Spoke to the pharmacist',
        'nhs_111_contacted' => 'Called NHS 111',
        'emergency_services_contacted' => 'Called the emergency services',
        'monitoring_only' => 'Watching them, nothing else needed yet',
    ];

    /** Outcomes that mean the medicine actually went in. */
    private const CONSUMES_ALLOWANCE = ['given', 'self_administered'];

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly StockLedger $stock,
    ) {
    }

    /**
     * The PRN medicines this person has, each with what is and is not allowed
     * right now.
     */
    public function forPerson(Client $client, ?Carbon $now = null): array
    {
        $now ??= now();

        return Prescription::with('medicine')
            ->where('client_id', $client->id)
            ->where('kind', 'prn')
            ->orderBy('id')
            ->get()
            ->map(fn ($prescription) => $this->describe($prescription, $client, $now))
            ->values()->all();
    }

    /** One PRN medicine, resolved through the person who is prescribed it. */
    public function resolve(Client $client, int $prescriptionId): ?Prescription
    {
        return Prescription::with('medicine')
            ->where('id', $prescriptionId)
            ->where('client_id', $client->id)
            ->where('kind', 'prn')
            ->first();
    }

    /**
     * Everything a worker needs to decide, and everything the server will check
     * again when they press the button.
     */
    public function describe(Prescription $prescription, Client $client, ?Carbon $now = null): array
    {
        $now ??= now();

        $medicine = $prescription->medicine;
        $last = $this->lastGiven($prescription);
        $nextAllowed = $this->nextAllowedAt($prescription, $last);
        $window = $this->windowUsage($prescription, $now);
        $eligibility = $this->eligibility($prescription, $client, $now);

        return [
            'prescriptionId' => $prescription->id,

            'name' => $medicine?->name,
            'strength' => $medicine?->strength,
            'form' => $medicine?->form,
            'controlled' => (bool) $medicine?->is_controlled,

            'directions' => $prescription->dose,
            'route' => $prescription->route,
            'indication' => $prescription->prn_indication,
            'instructions' => $prescription->instructions,

            'support' => $prescription->support_type,
            'supportWord' => RoundPersonView::SUPPORT_WORDS[$prescription->support_type]
                ?? $prescription->support_type,
            'selfManaged' => (bool) $prescription->isFullySelfManaged(),

            // The permitted dose, structurally. Null where the prescription
            // does not say, and the screen says so rather than guessing.
            'doseMin' => $prescription->dose_min !== null ? (float) $prescription->dose_min : null,
            'doseMax' => $prescription->dose_max !== null ? (float) $prescription->dose_max : null,
            'doseUnit' => $prescription->dose_unit,
            'doseUnitWord' => $this->unitWord(
                $prescription->dose_unit,
                (float) ($prescription->dose_max ?? $prescription->dose_min ?? 1)
            ),
            'limitUnitWord' => $this->unitWord(
                $prescription->dose_unit,
                (float) ($prescription->prn_max_total_amount ?? 2)
            ),
            'doseIsRange' => $prescription->dose_min !== null
                && $prescription->dose_max !== null
                && (float) $prescription->dose_min !== (float) $prescription->dose_max,

            'lastGivenAt' => $last?->administered_at->format('H:i'),
            'lastGivenOn' => $last?->administered_at->format('j F'),
            'lastGivenBy' => $last?->recordedBy?->displayName(),
            'lastDoseAmount' => $last?->dose_amount !== null ? (float) $last->dose_amount : null,
            'lastDoseUnit' => $last?->dose_unit,
            'lastDoseUnitWord' => $last && $last->dose_amount !== null
                ? $this->unitWord($last->dose_unit, (float) $last->dose_amount)
                : null,

            'minGapMinutes' => $prescription->prn_min_gap_minutes,
            'nextAllowedAt' => $nextAllowed?->format('H:i'),
            'tooSoon' => $nextAllowed !== null && $now->lessThan($nextAllowed),

            // The window, said in full so a worker can see the arithmetic
            // rather than being told "no".
            'limitPeriod' => $prescription->prn_limit_period,
            'limitPeriodWord' => $this->periodWord($prescription->prn_limit_period),
            'maxAdministrations' => $prescription->prn_max_administrations,
            'givenInWindow' => $window['count'],
            'maxTotalAmount' => $prescription->prn_max_total_amount !== null
                ? (float) $prescription->prn_max_total_amount
                : null,
            'amountInWindow' => $window['amount'],

            'reviewAfterMinutes' => $prescription->prn_review_after_minutes,

            'canGive' => $eligibility['allowed'],
            'blockedCode' => $eligibility['code'],
            'blockedReason' => $eligibility['reason'],
            'nextSection' => $eligibility['nextSection'],
        ];
    }

    /**
     * May this be given right now?
     *
     * The order is deliberate: the boundaries that belong to another section
     * come first, so a controlled drug is never told it is "too soon" when the
     * real answer is that Record7 cannot witness it yet.
     *
     * @return array{allowed:bool, code:?string, reason:?string, nextSection:?string}
     */
    /**
     * @param bool $controlledPathway Section 2.5 is handling the register, the
     *   witness and the balance itself. It skips ONLY the stop below, and every
     *   later guard still runs in order — which is the whole reason this is a
     *   flag rather than the caller tolerating a returned code, because that
     *   would have skipped prescription status, support type, availability,
     *   interval and maximum along with it.
     */
    public function eligibility(
        Prescription $prescription,
        Client $client,
        ?Carbon $now = null,
        bool $controlledPathway = false
    ): array {
        $now ??= now();
        $medicine = $prescription->medicine;

        if ($prescription->kind !== 'prn') {
            return $this->no('not_prn', 'This is a scheduled medicine, not an as-required one.');
        }

        // Section 2.5 owns the register, the witness and the balance. Nothing
        // here may write a controlled administration without them.
        if ($medicine?->is_controlled && ! $controlledPathway) {
            return $this->no(
                'witness_required',
                'This is a controlled drug. It needs a witness and a register entry, which '
                .'Record7 cannot do yet.',
                '2.5'
            );
        }

        if ($prescription->status !== 'active') {
            return $this->no(
                'prescription_'.$prescription->status,
                'This prescription is '.$prescription->status.'. Do not give it without asking.'
            );
        }

        if ($prescription->isFullySelfManaged()) {
            return $this->no(
                'self_managed',
                'They manage this themselves. There is no staff record to make for each dose.'
            );
        }

        if ($prescription->support_type === 'prompted') {
            return $this->no(
                'support_type_prompted',
                'They take this themselves after a reminder. Recording it as given by staff '
                .'would say somebody handed it to them.'
            );
        }

        if (! $client->isAvailable()) {
            return $this->no(
                'person_away',
                $client->statusWord().'. They are not here to be given anything.'
            );
        }

        // Enough time since the last one they actually had.
        $nextAllowed = $this->nextAllowedAt($prescription, $this->lastGiven($prescription));

        if ($nextAllowed !== null && $now->lessThan($nextAllowed)) {
            return $this->no(
                'too_soon',
                'The last dose was less than '.$prescription->prn_min_gap_minutes
                .' minutes ago. The next one is not due until '.$nextAllowed->format('H:i').'.'
            );
        }

        $window = $this->windowUsage($prescription, $now);

        // A COUNT limit, only if the prescription states one.
        if ($prescription->prn_max_administrations !== null
            && $window['count'] >= (int) $prescription->prn_max_administrations) {
            return $this->no(
                'max_administrations_reached',
                'They have already had '.$window['count'].' of a maximum '
                .$prescription->prn_max_administrations.' doses '
                .$this->periodWord($prescription->prn_limit_period).'.'
            );
        }

        return ['allowed' => true, 'code' => null, 'reason' => null, 'nextSection' => null];
    }

    /**
     * Would THIS dose take them over an amount limit?
     *
     * Separate from eligibility() because it cannot be answered until somebody
     * says how much they are about to give. A count limit is about how often; an
     * amount limit is about how much, and a prescription may carry either.
     *
     * @return array{allowed:bool, code:?string, reason:?string}
     */
    public function checkDose(Prescription $prescription, float $amount, ?Carbon $now = null): array
    {
        $now ??= now();

        if ($amount <= 0) {
            return $this->no('dose_not_positive', 'Say how much you actually gave.');
        }

        // Inside the permitted single dose, where one is stated.
        $min = $prescription->dose_min !== null ? (float) $prescription->dose_min : null;
        $max = $prescription->dose_max !== null ? (float) $prescription->dose_max : null;
        $unit = $prescription->dose_unit ? ' '.$prescription->dose_unit : '';

        if ($min !== null && $amount < $min) {
            return $this->no(
                'below_permitted_dose',
                'The prescription says at least '.$this->number($min).$unit.' for one dose.'
            );
        }

        if ($max !== null && $amount > $max) {
            return $this->no(
                'above_permitted_dose',
                'The prescription says at most '.$this->number($max).$unit.' for one dose.'
            );
        }

        // And inside the amount allowed across the window, where one is stated.
        if ($prescription->prn_max_total_amount !== null) {
            $already = $this->windowUsage($prescription, $now)['amount'];
            $limit = (float) $prescription->prn_max_total_amount;

            if ($already + $amount > $limit) {
                return $this->no(
                    'max_total_amount_reached',
                    'That would take them to '.$this->number($already + $amount).$unit
                    .' out of a maximum '.$this->number($limit).$unit.' '
                    .$this->periodWord($prescription->prn_limit_period)
                    .' — they have already had '.$this->number($already).$unit.'.'
                );
            }
        }

        return ['allowed' => true, 'code' => null, 'reason' => null];
    }

    /**
     * Mint an attempt for the give screen.
     *
     * The identity of an attempt is issued here, by the server, and never
     * accepted from a browser as a new value. A worker may only hand back one
     * they were given, for the person and medicine it was given for.
     */
    public function beginAttempt(
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription
    ): PrnAttempt {
        return PrnAttempt::create([
            'token' => (string) Str::uuid().'-'.Str::random(24),
            'organisation_id' => $client->organisation_id ?? $user->organisation_id,
            'service_id' => $serviceId,
            'client_id' => $client->id,
            'prescription_id' => $prescription->id,
            'issued_to_user_id' => $user->id,
            'issued_at' => now(),
        ]);
    }

    /**
     * Record that it was given, and set the ask-back.
     *
     * WHY THIS IS ONE TRANSACTION AROUND A LOCK.
     * Every limit used to be checked and then written, with nothing holding the
     * two together. Two workers pressing at the same moment both read the same
     * usage window, both concluded the interval had passed, and both wrote —
     * two doses out of an allowance for one. A check has to happen where the
     * write happens.
     *
     * So the prescription row is locked first. It is the authoritative row for
     * this person and this medicine — a PRN prescription belongs to exactly one
     * client — which makes it the right thing to serialise on without inventing
     * a table to hold a lock. Everything after the lock sees committed state,
     * so the second worker reads the first worker's dose rather than the
     * history that existed before it.
     *
     * REPLAY IS NOT A SECOND DOSE.
     * The attempt is spent inside the same lock. A double-click, a retry or a
     * resubmitted form arrives carrying a token that is already spent, and gets
     * back the administration it already created — not an error, because
     * nothing went wrong, and not a second record, because nothing happened
     * twice.
     *
     * @return array{administration:Administration, followUp:?PrnFollowUp, created:bool}
     */
    public function record(
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription,
        float $amount,
        string $observedReason,
        ?string $notes,
        Request $request,
        ?string $attemptToken = null
    ): array {
        if (! array_key_exists($observedReason, self::OBSERVED_REASONS)) {
            throw new RuntimeException('Say what you saw or what they told you.');
        }

        if ($attemptToken === null || trim($attemptToken) === '') {
            throw new RuntimeException(
                'Start again from the medicine so this can be recorded safely.'
            );
        }

        return DB::connection('record7')->transaction(function () use (
            $user, $serviceId, $client, $prescription, $amount,
            $observedReason, $notes, $request, $attemptToken
        ) {
            // THE LOCK. Held until this transaction commits, so no other
            // request can evaluate an interval or a daily maximum against
            // history that is about to change.
            $locked = Prescription::where('id', $prescription->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null) {
                throw new RuntimeException('That prescription is no longer available.');
            }

            $attempt = $this->claimAttempt($attemptToken, $user, $serviceId, $client, $locked);

            // Already recorded. Hand back what it became.
            if ($attempt->isSpent()) {
                $existing = $attempt->administration()->first();

                if ($existing !== null) {
                    return [
                        'administration' => $existing,
                        'followUp' => PrnFollowUp::where('administration_id', $existing->id)->first(),
                        'created' => false,
                    ];
                }
            }

            return $this->write(
                $user, $serviceId, $client, $locked, $amount,
                $observedReason, $notes, $request, $attempt
            );
        });
    }

    /**
     * Section 2.5 reuses the attempt machinery rather than working around it.
     *
     * A controlled as-required medicine is still an as-required medicine: the
     * same double-click, the same retry, the same need to tell a replay from a
     * second dose. This claims the attempt exactly as the ordinary path does,
     * inside whatever transaction and lock the caller is already holding.
     *
     * @return array{attempt:PrnAttempt, already:?Administration}
     */
    public function claimForControlled(
        ?string $token,
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription
    ): array {
        if ($token === null || trim($token) === '') {
            throw new RuntimeException(
                'Start again from the medicine so this can be recorded safely.'
            );
        }

        $attempt = $this->claimAttempt($token, $user, $serviceId, $client, $prescription);

        return [
            'attempt' => $attempt,
            'already' => $attempt->isSpent() ? $attempt->administration()->first() : null,
        ];
    }

    /**
     * Every Section 2.4 guard, run for a controlled medicine.
     *
     * The ONLY thing 2.5 is allowed to skip is 2.4's own refusal that a
     * controlled drug cannot be given yet — which 2.5 exists to replace. Every
     * other guard still bites, and each has a test proving it still refuses a
     * controlled as-required dose independently.
     */
    public function assertGivable(
        Prescription $prescription,
        Client $client,
        float $amount,
        ?string $observedReason,
        bool $controlled = false
    ): void {
        if (! array_key_exists((string) $observedReason, self::OBSERVED_REASONS)) {
            throw new RuntimeException('Say what you saw or what they told you.');
        }

        $eligibility = $this->eligibility($prescription, $client, null, $controlled);

        if (! $eligibility['allowed']) {
            throw new RuntimeException($eligibility['reason']);
        }

        $doseCheck = $this->checkDose($prescription, $amount);

        if (! $doseCheck['allowed']) {
            throw new RuntimeException($doseCheck['reason']);
        }
    }

    /** Spend an attempt against the administration it produced. */
    public function spend(PrnAttempt $attempt, Administration $administration): void
    {
        $attempt->forceFill([
            'consumed_at' => now(),
            'administration_id' => $administration->id,
        ])->save();
    }

    /**
     * Find the attempt this request is spending, and prove it is theirs.
     *
     * A token is not a password but it is a claim, so every part of the claim
     * is checked rather than trusted: that it exists, that it was issued to
     * this worker, and that it was issued for this person, this medicine and
     * this house. An unknown token is refused outright rather than quietly
     * treated as a fresh attempt.
     */
    private function claimAttempt(
        string $token,
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription
    ): PrnAttempt {
        $attempt = PrnAttempt::where('token', $token)->lockForUpdate()->first();

        if ($attempt === null) {
            throw new RuntimeException(
                'Start again from the medicine so this can be recorded safely.'
            );
        }

        $belongs = $attempt->issued_to_user_id === $user->id
            && $attempt->service_id === $serviceId
            && $attempt->client_id === $client->id
            && $attempt->prescription_id === $prescription->id;

        if (! $belongs) {
            throw new RuntimeException(
                'That record does not belong to this person and medicine.'
            );
        }

        return $attempt;
    }

    /**
     * Move the stock an as-required dose actually consumed.
     *
     * Returns null where there is nothing to move: a controlled medicine
     * (Section 2.5's, entirely), one nobody is counting for this person, one
     * with no unit recorded, or one the person holds and manages themselves.
     *
     * Must be called inside the caller's transaction. The balance lock is taken
     * AFTER the prescription lock and never before it, so the two locks are
     * always acquired in the same order and a cycle cannot form.
     */
    private function debitStock(
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription,
        float $amount,
        Request $request
    ): ?\App\Models\Record7\StockMovement {
        $medicine = $prescription->medicine;

        if (! $medicine
            || $medicine->is_controlled
            || $prescription->dose_unit === null
            || trim((string) $prescription->dose_unit) === ''
            || ! $this->stock->consumesAccountedStock($prescription)
            || $amount <= 0) {
            return null;
        }

        $snapshot = $this->stock->snapshot($medicine, $prescription->dose_unit);
        $balance = $this->stock->balanceFor($client, $snapshot);

        // Untracked: nobody is counting this medicine for this person, so a
        // dose changes nothing and claims nothing.
        if (! $balance) {
            return null;
        }

        $locked = $this->stock->lockExisting($balance);
        $cover = $this->stock->canCover($locked, 'administration', $amount);
        $shortfall = null;

        if (! $cover['sufficient']) {
            $shortfall = $this->stock->verifyShortfall($user, $request->only([
                'shortfall_basis', 'shortfall_statement', 'shortfall_observed_quantity',
            ]));
        }

        return $this->stock->record(
            balance: $locked,
            snapshot: $snapshot,
            action: 'administration',
            quantities: ['removed' => $amount, 'given' => $amount],
            user: $user,
            client: $client,
            house: Service::findOrFail($serviceId),
            prescription: $prescription,
            shortfall: $shortfall,
        );
    }

    /**
     * The write itself, inside the lock and the transaction.
     *
     * @return array{administration:Administration, followUp:?PrnFollowUp, created:bool}
     */
    private function write(
        User $user,
        int $serviceId,
        Client $client,
        Prescription $prescription,
        float $amount,
        string $observedReason,
        ?string $notes,
        Request $request,
        PrnAttempt $attempt
    ): array {
        // RE-EVALUATED HERE, under the lock. The screen having offered the
        // button is not evidence, and neither is a check made before waiting
        // for the lock.
        $eligibility = $this->eligibility($prescription, $client);

        if (! $eligibility['allowed']) {
            throw new RuntimeException($eligibility['reason']);
        }

        $doseCheck = $this->checkDose($prescription, $amount);

        if (! $doseCheck['allowed']) {
            throw new RuntimeException($doseCheck['reason']);
        }

        // Self-administered WITH monitoring is still their dose, not staff's.
        // Recording it as "given" would say a worker handed it over.
        $outcome = $prescription->support_type === 'self_administered'
            ? 'self_administered'
            : 'given';

        // SECTION 2.7. What was actually taken is what leaves the cupboard —
        // never the prescribed range, and never anything read out of the dose
        // text. This runs inside the transaction and the prescription lock the
        // caller is already holding; the balance lock is taken after the
        // prescription one, always in that order, so no cycle can form.
        $movement = $this->debitStock($user, $serviceId, $client, $prescription, $amount, $request);

        $administration = Administration::create([
            'reference' => 'R7P-'.strtoupper(Str::random(12)),

            // NO SCHEDULED DOSE. A PRN answers a need, not a plan — and a null
            // here is also what keeps the one-answer-per-dose constraint out of
            // the way of a second dose the prescription permits.
            'scheduled_dose_id' => null,

            'stock_movement_id' => $movement?->id,

            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $serviceId,
            'recorded_by_user_id' => $user->id,
            'outcome' => $outcome,

            // What they observed, structurally. The prescription says what the
            // medicine is FOR; this says why it was needed this time.
            'reason_code' => $observedReason,
            'notes' => $this->cleanNotes($notes),

            // Snapshotted, so this stays readable if the prescription changes.
            'dose_amount' => $amount,
            'dose_unit' => $prescription->dose_unit,

            'administered_at' => now(),
        ]);

        // The ask-back, timed from the prescription rather than a guess. Where
        // no interval is stated none is invented — the gap is surfaced instead.
        $followUp = null;

        // SPEND IT, inside the same lock that authorised it. The unique index
        // on administration_id is the last line: even if everything above were
        // bypassed, one attempt can never point at two records.
        $attempt->forceFill([
            'consumed_at' => now(),
            'administration_id' => $administration->id,
        ])->save();

        if ($prescription->prn_review_after_minutes !== null) {
            $followUp = PrnFollowUp::create([
                'administration_id' => $administration->id,
                'client_id' => $client->id,
                'service_id' => $serviceId,
                'due_at' => $administration->administered_at
                    ->copy()->addMinutes((int) $prescription->prn_review_after_minutes),
                'outcome' => 'pending',
            ]);
        }

        $this->audit->record(
            eventType: 'prn_medication_administered',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: null,
            riskLevel: 'medium',
            metadata: [
                'prescription_id' => $prescription->id,
                'medicine_id' => $prescription->medicine_id,
                'client_id' => $client->id,
                'administration_id' => $administration->id,
                'outcome' => $outcome,
                'observed_reason' => $observedReason,
                'dose_amount' => (float) $amount,
                'dose_unit' => $prescription->dose_unit,
                'administered_at' => $administration->administered_at->toIso8601String(),
                'follow_up_id' => $followUp?->id,
                'follow_up_due_at' => $followUp?->due_at->toIso8601String(),
            ],
            request: $request
        );

        return [
            'administration' => $administration,
            'followUp' => $followUp,
            'created' => true,
        ];
    }

    /**
     * Answer the ask-back: did it work, and did anything worry you?
     *
     * TWO SEPARATE QUESTIONS, ON PURPOSE.
     * "It did not work" and "something about them concerned me" are different
     * observations. A medicine can work perfectly and still produce something
     * worth reporting, and a medicine can do nothing at all without any
     * reaction. Folding one into the other loses whichever mattered.
     *
     * The follow-up row is completed rather than replaced, and the
     * administration it belongs to is never touched.
     */
    public function recordReview(
        User $user,
        int $serviceId,
        PrnFollowUp $followUp,
        string $outcome,
        ?string $notes,
        bool $concerning,
        ?string $concernObserved,
        ?string $concernAction,
        Request $request
    ): PrnFollowUp {
        if (! in_array($outcome, ['effective', 'partly_effective', 'not_effective'], true)) {
            throw new RuntimeException('Say whether it worked.');
        }

        if ((int) $followUp->service_id !== $serviceId) {
            throw new RuntimeException('That follow-up belongs to another house.');
        }

        if (! $followUp->isOutstanding()) {
            throw new RuntimeException('That has already been answered.');
        }

        // A concern has to say what was seen and what was done about it.
        // "Something worried me" with nothing after it is not a record anybody
        // can act on tomorrow.
        if ($concerning) {
            $observed = trim((string) $concernObserved);

            if ($observed === '') {
                throw new RuntimeException('Write down what you actually saw.');
            }

            if (! array_key_exists((string) $concernAction, self::CONCERN_ACTIONS)) {
                throw new RuntimeException('Say what you did about it.');
            }
        }

        $followUp->forceFill([
            'outcome' => $outcome,
            'notes' => $this->cleanNotes($notes),
            'concerning_response' => $concerning,
            'concern_observed' => $concerning ? $this->cleanNotes($concernObserved) : null,
            'concern_action_code' => $concerning ? $concernAction : null,
            'completed_at' => now(),
            'completed_by_user_id' => $user->id,
        ])->save();

        $this->audit->record(
            eventType: $concerning ? 'prn_concerning_response_recorded' : 'prn_effectiveness_recorded',
            result: AuditRecorder::SUCCESS,
            user: $user,
            serviceId: $serviceId,
            reason: null,
            riskLevel: $concerning ? 'high' : 'low',
            metadata: [
                'follow_up_id' => $followUp->id,
                'administration_id' => $followUp->administration_id,
                'client_id' => $followUp->client_id,
                'outcome' => $outcome,
                'concerning_response' => $concerning,
                'concern_action_code' => $concerning ? $concernAction : null,
                'completed_at' => $followUp->completed_at->toIso8601String(),
            ],
            request: $request
        );

        return $followUp;
    }

    /**
     * The last dose they ACTUALLY had.
     *
     * Not the last row — the last dose. A refusal is a real record and not a
     * dose, and somebody who said no at two and asks at three has had nothing.
     */
    public function lastGiven(Prescription $prescription): ?Administration
    {
        return Administration::with('recordedBy')
            ->where('prescription_id', $prescription->id)
            ->whereIn('outcome', self::CONSUMES_ALLOWANCE)
            ->orderByDesc('administered_at')
            ->orderByDesc('id')
            ->first();
    }

    /** When the next one becomes due, if the prescription states a gap. */
    public function nextAllowedAt(Prescription $prescription, ?Administration $last): ?Carbon
    {
        if ($last === null || ! $prescription->prn_min_gap_minutes) {
            return null;
        }

        return $last->administered_at->copy()
            ->addMinutes((int) $prescription->prn_min_gap_minutes);
    }

    /**
     * How much has been had inside the limit window, and how many times.
     *
     * Rolling by default: the window ends now and starts twenty-four hours ago,
     * so four doses before midnight and four after are eight doses in the same
     * window rather than two tidy allowances.
     *
     * @return array{count:int, amount:float, from:?Carbon}
     */
    public function windowUsage(Prescription $prescription, ?Carbon $now = null): array
    {
        $now ??= now();
        $from = $this->windowStart($prescription, $now);

        if ($from === null) {
            return ['count' => 0, 'amount' => 0.0, 'from' => null];
        }

        $doses = Administration::where('prescription_id', $prescription->id)
            ->whereIn('outcome', self::CONSUMES_ALLOWANCE)
            ->where('administered_at', '>=', $from)
            ->where('administered_at', '<=', $now)
            ->get();

        return [
            'count' => $doses->count(),
            'amount' => (float) $doses->sum(fn ($d) => (float) ($d->dose_amount ?? 0)),
            'from' => $from,
        ];
    }

    private function windowStart(Prescription $prescription, Carbon $now): ?Carbon
    {
        return match ($prescription->prn_limit_period) {
            'rolling_24h' => $now->copy()->subDay(),
            'calendar_day' => $now->copy()->startOfDay(),
            default => null,
        };
    }

    private function periodWord(?string $period): string
    {
        return match ($period) {
            'rolling_24h' => 'in the last 24 hours',
            'calendar_day' => 'today',
            default => 'in the limit period',
        };
    }

    /**
     * "2 puffs", not "2 puff".
     *
     * Small, and it is the difference between a screen that reads like a person
     * wrote it and one that reads like a database printed it. Decided here so
     * every surface agrees, and left alone for units that do not take an s.
     */
    public function unitWord(?string $unit, float $amount): string
    {
        if ($unit === null || $unit === '') {
            return '';
        }

        // Units that are already plural, or are measures rather than countable
        // things, stay exactly as written.
        $invariant = ['ml', 'mg', 'mcg', 'g', 'unit(s)', 'drops', 'puffs', 'tablets'];

        if (abs($amount - 1.0) < 0.0001 || in_array(strtolower($unit), $invariant, true)) {
            return $unit;
        }

        return $unit.'s';
    }

    /** 2.0 reads as "2"; 2.5 stays 2.5. */
    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    private function cleanNotes(?string $notes): ?string
    {
        $notes = trim((string) $notes);

        return $notes === '' ? null : Str::limit($notes, 495, '');
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
