<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Service;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockMovement;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\ManagerActions;
use App\Services\Record7\ManagerBoard;
use App\Services\Record7\StockLedger;
use App\Services\Record7\StockRefusal;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Section 2.7 — corrections, and the stock they do and do not move.
 *
 * THE ATTRIBUTABLE QUANTITY, NOT "THE ORIGINAL DEBIT".
 * An administration movement debits given + wasted. Correcting the clinical
 * outcome does not un-waste anything — the wasted portion was destroyed as a
 * separate physical act that no MAR correction has touched. Restoring it
 * because an outcome changed would put a destroyed tablet back in a cupboard,
 * so only `quantity_given` comes back. Every row of the matrix below turns on
 * that distinction.
 *
 * AND HISTORY NEVER ACQUIRES TODAY'S PRESCRIPTION.
 * Where a correction establishes that a dose WAS given, the amount comes from
 * the approved evidence or not at all. A prescription can change between the
 * event and the correction, and reading today's figure into last month's dose
 * is precisely the rewrite this section exists to prevent.
 */
class Record7StockCorrectionTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function ledger(): StockLedger
    {
        return app(StockLedger::class);
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    private function balanceFor(string $person, string $medicine): StockBalance
    {
        return StockBalance::whereHas('client', fn ($q) => $q->where('preferred_name', $person))
            ->whereHas('medicine', fn ($q) => $q->where('name', $medicine))
            ->firstOrFail();
    }

    /**
     * An administration that moved stock, written the way the product writes
     * one: movement first, then the clinical record that names it.
     *
     * @return array{administration:Administration, movement:StockMovement}
     */
    private function administrationWithMovement(
        StockBalance $balance, float $given, float $returned = 0, float $wasted = 0
    ): array {
        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)
            ->firstOrFail();

        return DB::connection('record7')->transaction(function () use (
            $balance, $given, $returned, $wasted, $prescription
        ) {
            $locked = $this->ledger()->lockExisting($balance);

            $movement = $this->ledger()->record(
                balance: $locked,
                snapshot: $this->ledger()->snapshot($balance->medicine, $balance->unit),
                action: 'administration',
                quantities: [
                    'removed' => $given + $returned + $wasted,
                    'given' => $given,
                    'returned' => $returned,
                    'wasted' => $wasted,
                ],
                user: $this->user('olivia.carter'),
                client: $balance->client,
                house: Service::findOrFail($balance->service_id),
                prescription: $prescription,
            );

            $administration = Administration::create([
                'reference' => 'TEST-'.strtoupper(Str::random(10)),
                'scheduled_dose_id' => null,
                'prescription_id' => $prescription->id,
                'client_id' => $balance->client_id,
                'service_id' => $balance->service_id,
                'recorded_by_user_id' => $this->user('olivia.carter')->id,
                'outcome' => 'given',
                'dose_amount' => $given,
                'dose_unit' => $balance->unit,
                'administered_at' => now()->subHour(),
                'stock_movement_id' => $movement->id,
            ]);

            return ['administration' => $administration, 'movement' => $movement];
        });
    }

    /** Raise a correction request and have the manager approve it. */
    private function approveCorrection(
        Administration $original, string $outcome, ?float $amount = null, ?string $unit = null
    ): ReviewItem {
        $item = ReviewItem::create([
            'reference' => 'TEST-COR-'.strtoupper(Str::random(8)),
            'organisation_id' => $original->client->organisation_id,
            'service_id' => $original->service_id,
            'kind' => 'correction_request',
            'title' => 'Correct an administration',
            'subject_type' => 'administration',
            'subject_id' => $original->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => $outcome,
            'requested_dose_amount' => $amount,
            'requested_dose_unit' => $unit,
            'raised_by_user_id' => $this->user('olivia.carter')->id,
            'raised_at' => now(),
            'severity' => 'medium',
            'status' => 'open',
        ]);

        app(ManagerActions::class)->decideReview(
            $this->user('daniel.evans'),
            $original->service_id,
            $item->id,
            'approved',
            'Established with the worker.',
            $outcome,
            request()
        );

        return $item->fresh();
    }

    private function correctionFor(StockMovement $original): ?StockMovement
    {
        return StockMovement::where('corrects_movement_id', $original->id)->first();
    }

    /* ── Rows 1 to 4: given -> a non-consuming outcome ──────────────────── */

    #[\PHPUnit\Framework\Attributes\DataProvider('nonConsumingOutcomes')]
    public function test_a_dose_corrected_to_a_non_consuming_outcome_gives_the_quantity_back(string $outcome): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, 1);

        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);

        $this->approveCorrection($administration, $outcome);

        $correction = $this->correctionFor($movement);

        $this->assertNotNull($correction, 'The stock consequence travels with the clinical one.');
        $this->assertSame('correction', $correction->action);
        $this->assertSame('1.000', (string) $correction->quantity_delta);
        $this->assertSame($before, (float) $balance->fresh()->current_balance);

        // The original stands, exactly as it was.
        $this->assertSame('1.000', (string) $movement->fresh()->quantity_given);
        $this->assertSame('given', $administration->fresh()->outcome);
    }

    public static function nonConsumingOutcomes(): array
    {
        return [
            'refused' => ['refused'],
            'missed' => ['missed'],
            'not available' => ['not_available'],
            'withheld' => ['withheld'],
        ];
    }

    /* ── Rows 9 and 10: returns and waste ───────────────────────────────── */

    public function test_a_returned_quantity_contributes_nothing_to_the_compensation(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        // Two came out, one went in, one went back. Only one left the balance.
        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, given: 1, returned: 1);

        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);

        $this->approveCorrection($administration, 'refused');

        $this->assertSame('1.000', (string) $this->correctionFor($movement)->quantity_delta);
        $this->assertSame($before, (float) $balance->fresh()->current_balance);
    }

    public function test_a_wasted_quantity_is_never_restored_by_a_clinical_correction(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        // Two came out, one went in, one went in the bin.
        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, given: 1, wasted: 1);

        $this->assertSame($before - 2.0, (float) $balance->fresh()->current_balance);

        $this->approveCorrection($administration, 'refused');

        // +1, NOT +2. The wasted tablet was destroyed, and no correction to a
        // MAR outcome can un-destroy it.
        $this->assertSame('1.000', (string) $this->correctionFor($movement)->quantity_delta);
        $this->assertSame(
            $before - 1.0,
            (float) $balance->fresh()->current_balance,
            'The waste stands until it is separately corrected with its own evidence.'
        );
    }

    public function test_a_mixed_episode_compensates_only_what_was_given(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        // Three out: one given, one back, one destroyed.
        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, given: 1, returned: 1, wasted: 1);

        $this->assertSame($before - 2.0, (float) $balance->fresh()->current_balance);

        $this->approveCorrection($administration, 'refused');

        $this->assertSame('1.000', (string) $this->correctionFor($movement)->quantity_delta);
        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);
    }

    /* ── Rows 5 and 6: given -> given at a different amount ─────────────── */

    public function test_correcting_to_a_smaller_amount_returns_only_the_difference(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, 2);

        $this->approveCorrection($administration, 'given', 1, $balance->unit);

        $this->assertSame('1.000', (string) $this->correctionFor($movement)->quantity_delta);
        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);

        $correction = Administration::where('corrects_administration_id', $administration->id)
            ->firstOrFail();

        $this->assertSame('1.000', (string) $correction->dose_amount);
        $this->assertSame($balance->unit, $correction->dose_unit);
    }

    public function test_correcting_to_a_larger_amount_debits_the_difference(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;

        ['administration' => $administration, 'movement' => $movement]
            = $this->administrationWithMovement($balance, 1);

        $this->approveCorrection($administration, 'given', 3, $balance->unit);

        $this->assertSame('-2.000', (string) $this->correctionFor($movement)->quantity_delta);
        $this->assertSame($before - 3.0, (float) $balance->fresh()->current_balance);
    }

    public function test_a_correction_in_another_unit_fails_closed(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        ['administration' => $administration] = $this->administrationWithMovement($balance, 1);

        // NO CONVERSION, EVER. Guessing what somebody meant is how a millilitre
        // becomes a milligram.
        $this->expectException(RuntimeException::class);

        $this->approveCorrection($administration, 'given', 1, 'ml');
    }

    public function test_correcting_a_given_dose_to_given_needs_an_actual_amount(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        ['administration' => $administration] = $this->administrationWithMovement($balance, 1);

        $this->expectException(RuntimeException::class);

        $this->approveCorrection($administration, 'given');
    }

    /* ── Row 7: non-consuming -> given, amount known ────────────────────── */

    public function test_a_correction_that_establishes_a_dose_writes_an_administration_movement(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;
        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        $missed = Administration::create([
            'reference' => 'TEST-MISSED-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Signed on the wrong line.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(2),

            // The preparation is tracked and quantified, so the declaration is
            // compulsory — the trigger refuses the row without it. Nothing came
            // out of the cupboard for a dose that was missed.
            'stock_no_quantity_removed' => true,
        ]);

        $this->approveCorrection($missed, 'given', 1, $balance->unit);

        $correction = Administration::where('corrects_administration_id', $missed->id)->firstOrFail();

        $this->assertNotNull($correction->stock_movement_id);

        $movement = StockMovement::findOrFail($correction->stock_movement_id);

        // NOT a `correction`: there is no earlier movement to point at, and
        // neither the constraint nor the vocabulary is bent to pretend there is.
        $this->assertSame('administration', $movement->action);
        $this->assertSame('1.000', (string) $movement->quantity_given);
        $this->assertNotNull($movement->review_item_id, 'It consumes the approval.');
        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);

        // And the chain that explains why the debit exists.
        $this->assertSame($missed->id, $correction->corrects_administration_id);
    }

    /* ── Row 8: non-consuming -> given, amount unknown ──────────────────── */

    public function test_an_unknown_historical_amount_invents_no_debit_and_raises_a_verification(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $before = (float) $balance->current_balance;
        $movements = StockMovement::count();

        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        $missed = Administration::create([
            'reference' => 'TEST-UNKNOWN-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Nobody can say how much was taken.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(2),

            // The preparation is tracked and quantified, so the declaration is
            // compulsory — the trigger refuses the row without it. Nothing came
            // out of the cupboard for a dose that was missed.
            'stock_no_quantity_removed' => true,
        ]);

        // Approved with no amount: the clinical correction stands, and nothing
        // is invented from the prescription.
        $this->approveCorrection($missed, 'given');

        $correction = Administration::where('corrects_administration_id', $missed->id)->firstOrFail();

        $this->assertNull($correction->stock_movement_id);
        $this->assertSame($movements, StockMovement::count(), 'No debit was invented.');
        $this->assertSame($before, (float) $balance->fresh()->current_balance);

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'stock_verification_due:'.$correction->id, $balance->service_id
            ),
            'Somebody has to go and count.'
        );

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_verification_required',
        ], 'record7');
    }

    public function test_only_a_qualifying_count_clears_a_verification_requirement(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        $missed = Administration::create([
            'reference' => 'TEST-QUAL-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Nobody can say how much.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(2),

            // The preparation is tracked and quantified, so the declaration is
            // compulsory — the trigger refuses the row without it. Nothing came
            // out of the cupboard for a dose that was missed.
            'stock_no_quantity_removed' => true,
        ]);

        $this->approveCorrection($missed, 'given');
        $correction = Administration::where('corrects_administration_id', $missed->id)->firstOrFail();
        $key = 'stock_verification_due:'.$correction->id;
        $registry = app(IssueRegistry::class);

        $this->assertTrue($registry->conditionActive($key, $balance->service_id));

        // A count of a DIFFERENT person's balance proves nothing about this one.
        $other = $this->balanceFor('Joyce', 'Macrogol');
        $this->countIt($other);
        $this->assertTrue($registry->conditionActive($key, $balance->service_id));

        // And the manager's own workflow proves nothing either.
        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Looked into it.',
            'evidence_reference' => 'INCIDENT-2026-0999',
        ]);
        $this->assertTrue($registry->conditionActive($key, $balance->service_id));

        // Somebody counting THIS balance, afterwards, is what answers it.
        $this->countIt($balance->fresh());

        $this->assertFalse($registry->conditionActive($key, $balance->service_id));
    }


    /**
     * A count taken BEFORE the correction proves nothing about a position the
     * correction has since changed. Added because a mutation that dropped the
     * time filter altogether was killed by nothing: the suite proved the scope
     * (person, preparation, house) and never the ordering.
     */
    public function test_a_count_taken_before_the_correction_does_not_clear_it(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        // Counted first, while everything still looked right.
        $this->countIt($balance);

        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(30));

        $missed = Administration::create([
            'reference' => 'TEST-EARLY-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Nobody can say how much.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(2),
            'stock_no_quantity_removed' => true,
        ]);

        $this->approveCorrection($missed, 'given');
        $correction = Administration::where('corrects_administration_id', $missed->id)->firstOrFail();

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'stock_verification_due:'.$correction->id, $balance->service_id
            ),
            'An older count says nothing about a position that has since changed.'
        );
    }

    /**
     * The unit check on the OTHER correction path — where a debit is being
     * established and there is no earlier movement to compare against, so the
     * balance head's own unit is the authority.
     */
    public function test_establishing_a_debit_in_another_unit_fails_closed(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        $missed = Administration::create([
            'reference' => 'TEST-UNIT-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Wrong unit on the request.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(2),
            'stock_no_quantity_removed' => true,
        ]);

        $this->expectException(StockRefusal::class);

        $this->approveCorrection($missed, 'given', 5, 'ml');
    }

    /**
     * Approving a stock reconciliation writes NO movement, checked from the
     * ledger rather than from the balance. Added because the auto-execute
     * mutation was killed by a single test.
     */
    public function test_approving_a_stock_reconciliation_writes_no_movement(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();
        $movements = StockMovement::count();

        $item = ReviewItem::create([
            'reference' => 'TEST-SC-'.strtoupper(Str::random(8)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $entry->service_id,
            'kind' => 'correction_request',
            'title' => 'Reconcile',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        app(ManagerActions::class)->decideReview(
            $this->user('daniel.evans'), $entry->service_id, $item->id,
            'approved', 'Agreed.', null, request()
        );

        $this->assertSame($movements, StockMovement::count());
        $this->assertNull(StockMovement::where('review_item_id', $item->id)->first());
    }


    /**
     * A unit that only LOOKS the same is still a different unit.
     *
     * Added as a second guard on the same rule: "tablets" is not "tablet", and
     * a comparison that quietly tolerated it would be the first step towards
     * converting between things nobody asked it to convert.
     */
    public function test_a_plausible_looking_unit_is_still_a_mismatch(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $this->assertSame('tablet', $balance->unit);

        ['administration' => $administration] = $this->administrationWithMovement($balance, 1);

        $this->expectException(RuntimeException::class);

        $this->approveCorrection($administration, 'given', 1, 'tablets');
    }

    /**
     * A count of the right balance, taken before the correction, and then a
     * later one. The requirement survives the first and ends on the second.
     *
     * Added as a second guard because dropping the ordering filter altogether
     * was otherwise caught by a single test.
     */
    public function test_the_requirement_ends_on_the_later_count_and_not_the_earlier_one(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $early = $this->countIt($balance);

        \Illuminate\Support\Carbon::setTestNow(now()->addHour());

        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)->firstOrFail();

        $missed = Administration::create([
            'reference' => 'TEST-ORDER-'.strtoupper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'staff_error',
            'notes' => 'Nobody can say how much.',
            'action_taken' => 'Told the manager.',
            'immediate_action_code' => 'no_escalation_required_under_policy',
            'administered_at' => now()->subHours(3),
            'stock_no_quantity_removed' => true,
        ]);

        $this->approveCorrection($missed, 'given');
        $correction = Administration::where('corrects_administration_id', $missed->id)->firstOrFail();
        $key = 'stock_verification_due:'.$correction->id;
        $registry = app(IssueRegistry::class);

        $this->assertTrue(
            $registry->conditionActive($key, $balance->service_id),
            'The earlier count describes a position that has since changed.'
        );

        \Illuminate\Support\Carbon::setTestNow(now()->addHour());
        $late = $this->countIt($balance->fresh());

        $this->assertTrue($late->occurred_at->greaterThan($early->occurred_at));
        $this->assertFalse($registry->conditionActive($key, $balance->service_id));
    }


    /* ── The actions a manager is offered ───────────────────────────────── */

    /**
     * WHAT A PERSON IS OFFERED MUST MATCH WHAT THEY MAY DO.
     *
     * The queue used to hard-code Decline for every request and show it to
     * everyone, so Sarah — who has no `correction_approval` — was offered a
     * button that would have met a 403. Offering somebody an action they
     * cannot perform is its own defect, whatever the server does next.
     */
    private function reviewRow(string $username, string $house, int $reviewId): ?array
    {
        $this->signInAt($username, $house);

        $items = app(ManagerBoard::class)->openReviewItems(
            $this->house($house)->id, $this->user($username)
        );

        return collect($items)->firstWhere('id', $reviewId);
    }

    private function stockRequest(): \App\Models\Record7\ReviewItem
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        return \App\Models\Record7\ReviewItem::create([
            'reference' => 'TEST-ACT-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $entry->service_id,
            'kind' => 'correction_request',
            'title' => 'Reconcile the senna count',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);
    }

    public function test_a_stock_reconciliation_offers_only_the_actions_that_mean_something(): void
    {
        $item = $this->stockRequest();

        $row = $this->reviewRow('daniel.evans', 'Rosewood House', $item->id);

        $this->assertNotNull($row);

        $labels = array_column($row['actions'], 'label');

        // Never "approve as missed": this request asks for a quantity.
        $this->assertContains('Approve this adjustment', $labels);
        $this->assertNotContains('Approve as missed', $labels);

        $approve = collect($row['actions'])->firstWhere('key', 'approve');

        $this->assertNull(
            $approve['correctedOutcome'],
            'A stock reconciliation carries no outcome to correct.'
        );
    }

    public function test_somebody_who_cannot_decide_is_offered_nothing(): void
    {
        $item = $this->stockRequest();

        // Sarah raises reconciliations and carries them out. She does not
        // approve them, so the queue must offer her neither button — including
        // Decline, which the screen used to show unconditionally.
        $row = $this->reviewRow('sarah.ahmed', 'Rosewood House', $item->id);

        $this->assertNotNull($row);
        $this->assertSame([], $row['actions']);
    }

    public function test_an_administration_correction_keeps_its_existing_actions(): void
    {
        $item = \App\Models\Record7\ReviewItem::where('kind', 'correction_request')
            ->where('subject_type', 'administration')
            ->where('status', 'open')
            ->first();

        if (! $item) {
            $this->markTestSkipped('No open administration correction in the fixture.');
        }

        $row = $this->reviewRow('daniel.evans', $item->service_id === $this->house('Oakwood House')->id
            ? 'Oakwood House' : 'Rosewood House', $item->id);

        $this->assertNotNull($row);

        $approve = collect($row['actions'])->firstWhere('key', 'approve');

        $this->assertNotNull($approve);
        $this->assertSame($item->requested_outcome, $approve['correctedOutcome'],
            'The outcome stays the requester\'s, sent back so a substitution can be refused.');
        $this->assertContains('decline', array_column($row['actions'], 'key'));
    }

    public function test_a_round_reopen_request_is_gated_on_its_own_permission(): void
    {
        // Built rather than borrowed: the fixture's rounds live in the other
        // house, and skipping would leave this rule unproved.
        $house = $this->rosewood();

        $roundId = DB::connection('record7')->table('record7_rounds')->insertGetId([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => 'Actions-'.uniqid(),
            'started_by_user_id' => $this->user('olivia.carter')->id,
            'started_at' => now()->subHours(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $round = \App\Models\Record7\Round::findOrFail($roundId);

        $item = \App\Models\Record7\ReviewItem::create([
            'reference' => 'TEST-REOPEN-'.strtoupper(\Illuminate\Support\Str::random(8)),
            'organisation_id' => $this->rosewood()->organisation_id,
            'service_id' => $this->rosewood()->id,
            'kind' => 'round_reopen_request',
            'title' => 'Reopen the morning round',
            'subject_type' => 'round',
            'subject_id' => $round->id,
            'raised_by_user_id' => $this->user('olivia.carter')->id,
            'raised_at' => now(),
            'severity' => 'medium',
            'status' => 'open',
        ]);

        // Daniel holds reopen_medication_round through the Service Manager role.
        $daniel = $this->reviewRow('daniel.evans', 'Rosewood House', $item->id);
        $this->assertNotNull($daniel);
        $this->assertSame(['approve', 'decline'], array_column($daniel['actions'], 'key'));
        $this->assertSame('Approve', collect($daniel['actions'])->firstWhere('key', 'approve')['label']);

        // Olivia does not.
        $olivia = $this->reviewRow('olivia.carter', 'Rosewood House', $item->id);

        if ($olivia !== null) {
            $this->assertSame([], $olivia['actions']);
        }
    }

    public function test_a_decided_request_offers_nothing_further(): void
    {
        $item = $this->stockRequest();
        $item->forceFill([
            'status' => 'declined',
            'decided_by_user_id' => $this->user('daniel.evans')->id,
            'decided_at' => now(),
        ])->save();

        $row = $this->reviewRow('daniel.evans', 'Rosewood House', $item->id);

        // A decided item leaves the open queue entirely; if it is still listed
        // anywhere, it carries no actions.
        if ($row !== null) {
            $this->assertSame([], $row['actions']);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * AND THE SCREEN IS A COURTESY, NOT THE CONTROL.
     *
     * A forged post naming an action the person was never offered is refused
     * by the server on its own account.
     */
    public function test_a_forged_decision_is_still_refused_by_the_server(): void
    {
        $item = $this->stockRequest();

        // Sarah was offered nothing. She posts anyway.
        $this->signInAt('sarah.ahmed', 'Rosewood House');

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'note' => 'Forged.',
        ])->assertStatus(403);

        $this->assertSame('open', $item->fresh()->status);

        // And declining is refused on the same authority.
        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'declined',
            'note' => 'Forged.',
        ])->assertStatus(403);

        $this->assertSame('open', $item->fresh()->status);
    }

    private function countIt(StockBalance $balance): StockMovement
    {
        return DB::connection('record7')->transaction(function () use ($balance) {
            $locked = $this->ledger()->lockExisting($balance);

            return $this->ledger()->record(
                balance: $locked,
                snapshot: $this->ledger()->snapshot($balance->medicine, $balance->unit),
                action: 'stock_check',
                quantities: ['counted' => (float) $locked->current_balance],
                user: $this->user('sarah.ahmed'),
                client: $balance->client,
                house: Service::findOrFail($balance->service_id),
            );
        });
    }

    /* ── The approval, and what consuming it means ──────────────────────── */

    public function test_approving_a_stock_reconciliation_does_not_carry_it_out(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();
        $before = (string) $senna->current_balance;

        $item = ReviewItem::create([
            'reference' => 'TEST-SC-'.strtoupper(Str::random(8)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $entry->service_id,
            'kind' => 'correction_request',
            'title' => 'Reconcile',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        app(ManagerActions::class)->decideReview(
            $this->user('daniel.evans'), $entry->service_id, $item->id,
            'approved', 'Agreed.', null, request()
        );

        $this->assertSame('approved', $item->fresh()->status);
        $this->assertSame(
            $before,
            (string) $senna->fresh()->current_balance,
            'Approval authorises. It does not move a balance.'
        );
        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'stock_discrepancy:'.$entry->id, $entry->service_id
            )
        );

        // And nothing pretended to be a corrective administration either.
        $this->assertNull(Administration::where('corrects_administration_id', $entry->id)->first());
    }

    public function test_one_approval_buys_exactly_one_correction(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        $item = $this->approvedReconciliation($entry, -2);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -2, $item->id)
        );

        $this->expectException(QueryException::class);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -2, $item->id)
        );
    }

    public function test_a_correction_must_apply_exactly_the_approved_figure(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        $item = $this->approvedReconciliation($entry, -2);

        $this->expectException(QueryException::class);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -9, $item->id)
        );
    }

    public function test_an_unapproved_request_cannot_be_carried_out(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        $item = ReviewItem::create([
            'reference' => 'TEST-SC-'.strtoupper(Str::random(8)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $entry->service_id,
            'kind' => 'correction_request',
            'title' => 'Reconcile',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $this->expectException(QueryException::class);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -2, $item->id)
        );
    }

    public function test_a_reconciliation_must_name_an_unresolved_discrepancy(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        // An ordinary opening movement, which is not a disagreement at all.
        $notADiscrepancy = StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->where('is_discrepancy', false)
            ->firstOrFail();

        $item = $this->approvedReconciliation($notADiscrepancy, -1);

        $this->expectException(QueryException::class);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $notADiscrepancy, -1, $item->id)
        );
    }


    /**
     * A second attempt at the same approved reconciliation writes no second
     * audit event.
     *
     * The two-connection probe proves one of them blocks and the other is
     * refused. This proves what the TRAIL says afterwards: one correction, one
     * `stock_corrected` event. A refusal that still audited a success would
     * make the record claim something happened twice.
     */
    public function test_a_second_attempt_at_one_approval_leaves_one_audit_event(): void
    {
        $this->signInAt('sarah.ahmed', 'Rosewood House');

        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();
        $item = $this->approvedReconciliation($entry, -2);

        $before = DB::connection('record7')->table('record7_access_audit_events')
            ->where('event_type', 'stock_corrected')->count();

        $url = '/record7/stock/movement/'.$entry->id.'/correct';

        $this->post($url)->assertRedirect();

        // The same submission again, exactly as a double click would send it.
        try {
            $this->post($url);
        } catch (\Throwable) {
            // Refused, which is the point. What matters is the trail below.
        }

        $after = DB::connection('record7')->table('record7_access_audit_events')
            ->where('event_type', 'stock_corrected')->count();

        $this->assertSame($before + 1, $after, 'The trail claims two corrections.');

        $this->assertSame(
            1,
            StockMovement::where('corrects_movement_id', $entry->id)->count(),
            'Two movements were written for one approval.'
        );

        $this->assertSame(
            1,
            StockMovement::where('review_item_id', $item->id)->count()
        );
    }

    private function approvedReconciliation(StockMovement $target, float $delta): ReviewItem
    {
        return ReviewItem::create([
            'reference' => 'TEST-SC-'.strtoupper(Str::random(8)),
            'organisation_id' => $target->organisation_id,
            'service_id' => $target->service_id,
            'kind' => 'correction_request',
            'title' => 'Reconcile',
            'subject_type' => 'stock_movement',
            'subject_id' => $target->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => $delta,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'approved',
            'decided_by_user_id' => $this->user('daniel.evans')->id,
            'decided_at' => now(),
        ]);
    }

    /* ── The two request shapes stay apart ──────────────────────────────── */

    public function test_a_review_item_cannot_be_both_shapes_at_once(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_review_items')->insert([
            'reference' => 'TEST-BOTH-'.strtoupper(Str::random(8)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $entry->service_id,
            'kind' => 'correction_request',
            'title' => 'Both at once',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'requested_outcome' => 'refused',
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_a_stock_delta_request_must_name_a_movement(): void
    {
        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_review_items')->insert([
            'reference' => 'TEST-SHAPE-'.strtoupper(Str::random(8)),
            'organisation_id' => $this->house('Rosewood House')->organisation_id,
            'service_id' => $this->house('Rosewood House')->id,
            'kind' => 'correction_request',
            'title' => 'Wrong subject',
            'subject_type' => 'administration',
            'subject_id' => 1,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ── The correction chain itself ────────────────────────────────────── */

    public function test_a_correction_cannot_name_a_movement_on_another_balance(): void
    {
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');
        $joyce = $this->balanceFor('Joyce', 'Macrogol');

        $target = StockMovement::where('owner_ref', $joyce->owner_ref)
            ->where('preparation_key', $joyce->preparation_key)
            ->firstOrFail();

        $item = $this->approvedReconciliation($target, -1);

        // Pointed at Joyce's movement while describing Dennis's preparation.
        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-CROSS-1',
            'organisation_id' => $dennis->organisation_id,
            'service_id' => $dennis->service_id,
            'owner_type' => 'client',
            'client_id' => $dennis->client_id,
            'medicine_id' => $dennis->medicine_id,
            'medicine_name_at_time' => 'Colecalciferol',
            'form_at_time' => 'tablet',
            'strength_at_time' => '800unit',
            'unit' => 'tablet',
            'action' => 'correction',
            'quantity_delta' => -1,
            'balance_before' => (float) $dennis->current_balance,
            'balance_after' => (float) $dennis->current_balance - 1,
            'corrects_movement_id' => $target->id,
            'review_item_id' => $item->id,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => $dennis->last_sequence_no + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_one_discrepancy_takes_one_correction(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->firstOrFail();

        $first = $this->approvedReconciliation($entry, -2);
        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -2, $first->id)
        );

        $second = $this->approvedReconciliation($entry, -1);

        $this->expectException(QueryException::class);

        DB::connection('record7')->transaction(
            fn () => $this->ledger()->compensate($this->user('sarah.ahmed'), $entry, -1, $second->id)
        );
    }
}
