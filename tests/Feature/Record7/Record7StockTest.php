<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockMovement;
use App\Models\Record7\StockThreshold;
use App\Models\Record7\User;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\ManagerBoard;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundQueue;
use App\Services\Record7\StockLedger;
use App\Services\Record7\StockRefusal;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Section 2.7 — the ordinary stock ledger.
 *
 * WHAT THESE TESTS ARE DEFENDING, in one sentence each:
 *
 *   a balance is derived from an append-only ledger and cannot be typed over;
 *   a count observes and never moves the running figure;
 *   every disagreement keeps its own identity and its own requirement;
 *   a dose that was physically given is always recordable, even when the
 *     ledger cannot cover it — and the resulting shortfall is preserved;
 *   a controlled medicine never appears here at all;
 *   nothing is ever inferred: not a quantity, not a unit, not a reorder level.
 */
class Record7StockTest extends Record7TestCase
{
    /** These describe the medication day, so they run at a fixed hour in it. */
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    private function ledger(): StockLedger
    {
        return app(StockLedger::class);
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    /** One person's balance for one medicine, from the fixture. */
    private function balanceFor(string $person, string $medicine): StockBalance
    {
        return StockBalance::whereHas('client', fn ($q) => $q->where('preferred_name', $person))
            ->whereHas('medicine', fn ($q) => $q->where('name', $medicine))
            ->firstOrFail();
    }

    private function snapshotOf(StockBalance $balance): array
    {
        return $this->ledger()->snapshot($balance->medicine, $balance->unit);
    }

    /** Write one movement the way the application does: locked, in a transaction. */
    private function move(StockBalance $balance, string $action, array $quantities, array $extra = []): StockMovement
    {
        return DB::connection('record7')->transaction(function () use ($balance, $action, $quantities, $extra) {
            $locked = $this->ledger()->lockExisting($balance);

            return $this->ledger()->record(
                balance: $locked,
                snapshot: $this->snapshotOf($balance),
                action: $action,
                quantities: $quantities,
                user: $extra['user'] ?? $this->user('sarah.ahmed'),
                client: $balance->client,
                house: Service::findOrFail($balance->service_id),
                shortfall: $extra['shortfall'] ?? null,
            );
        });
    }

    /** A dose in this round, for a tracked balance, with nothing recorded yet. */
    private function freshDoseFor($round, StockBalance $balance): ScheduledDose
    {
        $prescription = Prescription::where('client_id', $balance->client_id)
            ->where('medicine_id', $balance->medicine_id)
            ->where('kind', 'scheduled')
            ->firstOrFail();

        return ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $balance->client_id,
            'service_id' => $balance->service_id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('07:15'),
            'slot' => $round->slot,
            'grace_minutes' => 120,
        ])->fresh(['prescription.medicine', 'administration']);
    }

    private function enterRound(string $username = 'noah.williams', string $house = 'Oakwood House')
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
        $this->post('/record7/round/start');

        return app(RoundEntry::class)->openRoundFor($this->house($house)->id);
    }

    /** The dose in this round for one named person and medicine. */
    private function doseFor($round, string $person, string $medicine): ?ScheduledDose
    {
        $client = Client::where('service_id', $round->service_id)
            ->where('preferred_name', $person)->first();

        if (! $client) {
            return null;
        }

        return ScheduledDose::with(['prescription.medicine', 'administration'])
            ->where('client_id', $client->id)
            ->where('slot', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->get()
            ->first(fn ($d) => $d->prescription?->medicine?->name === $medicine);
    }

    /* ── 1. The fixture itself ──────────────────────────────────────────── */

    public function test_the_fixture_opens_balances_through_the_ledger(): void
    {
        $this->assertGreaterThanOrEqual(5, StockBalance::count());

        foreach (StockBalance::all() as $balance) {
            $this->assertGreaterThan(
                0,
                StockMovement::where('service_id', $balance->service_id)
                    ->where('owner_ref', $balance->owner_ref)
                    ->where('preparation_key', $balance->preparation_key)
                    ->count(),
                'A balance with no movements behind it is a figure nobody can account for.'
            );
        }
    }

    public function test_no_fixture_movement_claims_to_be_imported(): void
    {
        // Section 2.7 imports nothing. The columns exist only for the eventual
        // production migration, which is the only writer that may set them.
        $this->assertSame(0, StockMovement::where('imported', true)->count());
    }

    public function test_two_people_prescribed_one_medicine_hold_separate_balances(): void
    {
        // The exhibit for the whole ownership decision. The retired table held
        // ONE macrogol figure for a house where two people are prescribed it,
        // so it could not say whose had run out.
        $margaret = $this->balanceFor('Margaret', 'Macrogol');
        $joyce = $this->balanceFor('Joyce', 'Macrogol');

        $this->assertNotSame($margaret->id, $joyce->id);
        $this->assertSame($margaret->preparation_key, $joyce->preparation_key, 'Same preparation.');
        $this->assertNotEquals($margaret->owner_ref, $joyce->owner_ref, 'Different people.');
        $this->assertSame('0.000', (string) $margaret->current_balance);
        $this->assertSame('5.000', (string) $joyce->current_balance);
    }

    /* ── 2. Arithmetic, and the head that follows it ────────────────────── */

    public function test_a_receipt_adds_to_the_balance(): void
    {
        $balance = $this->balanceFor('Joyce', 'Macrogol');

        $movement = $this->move($balance, 'receipt', ['received' => 7]);

        $this->assertSame('5.000', (string) $movement->balance_before);
        $this->assertSame('12.000', (string) $movement->balance_after);
        $this->assertSame('12.000', (string) $balance->fresh()->current_balance);
    }

    public function test_waste_and_return_move_the_balance_in_opposite_directions(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->move($balance, 'waste', ['wasted' => 4]);
        $this->assertSame('16.000', (string) $balance->fresh()->current_balance);

        $this->move($balance->fresh(), 'return_to_stock', ['returned' => 1]);
        $this->assertSame('17.000', (string) $balance->fresh()->current_balance);
    }

    public function test_every_balance_rebuilds_from_its_own_ledger(): void
    {
        // THE HEAD IS DERIVED, and this is what proves it rather than asserting
        // it in a comment. Walked for every balance in the fixture, not one.
        foreach (StockBalance::all() as $balance) {
            $rebuilt = $this->ledger()->rebuild($balance);

            $this->assertSame(
                round((float) $balance->current_balance, 3),
                $rebuilt['balance'],
                'The stored balance disagrees with its own ledger.'
            );
            $this->assertSame((int) $balance->last_sequence_no, $rebuilt['sequence']);
        }
    }

    public function test_the_rebuild_still_agrees_after_a_run_of_movements(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->move($balance, 'receipt', ['received' => 10]);
        $this->move($balance->fresh(), 'waste', ['wasted' => 3]);
        $this->move($balance->fresh(), 'return_to_stock', ['returned' => 2]);
        $this->move($balance->fresh(), 'stock_check', ['counted' => 29]);

        $fresh = $balance->fresh();
        $rebuilt = $this->ledger()->rebuild($fresh);

        $this->assertSame(round((float) $fresh->current_balance, 3), $rebuilt['balance']);
    }

    /* ── 3. A count observes; it does not correct ───────────────────────── */

    public function test_a_count_that_agrees_moves_nothing_and_raises_nothing(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $movement = $this->move($balance, 'stock_check', ['counted' => 20]);

        $this->assertSame('20.000', (string) $movement->expected_quantity);
        $this->assertSame('20.000', (string) $movement->counted_quantity);
        $this->assertSame('20.000', (string) $movement->balance_after);
        $this->assertFalse((bool) $movement->is_discrepancy);
        $this->assertSame('20.000', (string) $balance->fresh()->current_balance);
    }

    public function test_a_count_that_disagrees_keeps_both_figures_and_still_moves_nothing(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $movement = $this->move($balance, 'stock_check', ['counted' => 17]);

        $this->assertSame('20.000', (string) $movement->expected_quantity, 'What the ledger said.');
        $this->assertSame('17.000', (string) $movement->counted_quantity, 'What somebody counted.');
        $this->assertTrue((bool) $movement->is_discrepancy);

        // THE POINT OF THE WHOLE SECTION. A count cannot be used to make an
        // awkward figure go away; only an approved correction moves the ledger.
        $this->assertSame(
            '20.000',
            (string) $balance->fresh()->current_balance,
            'A count observes. It does not correct.'
        );

        $this->assertSame(-3.0, $movement->difference(), 'Signed, and never floored.');
    }

    public function test_a_count_records_when_it_was_counted(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $this->assertNull($balance->last_counted_at);

        $this->move($balance, 'stock_check', ['counted' => 20]);

        $this->assertNotNull($balance->fresh()->last_counted_at);
    }

    /* ── 4. Discrepancies keep their own identity ───────────────────────── */

    public function test_two_short_counts_are_two_separate_unresolved_disagreements(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');

        $open = $this->ledger()->unresolvedDiscrepancies($senna);

        $this->assertCount(2, $open, 'The fixture counts short twice, and both stand.');
        $this->assertSame(['28.000', '27.000'], $open->pluck('counted_quantity')
            ->map(fn ($v) => (string) $v)->all());

        foreach ($open as $entry) {
            $this->assertTrue(
                app(IssueRegistry::class)->conditionActive(
                    'stock_discrepancy:'.$entry->id, $senna->service_id
                )
            );
        }
    }

    public function test_correcting_the_earlier_disagreement_never_hides_the_later_one(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $open = $this->ledger()->unresolvedDiscrepancies($senna);
        [$first, $second] = [$open[0], $open[1]];

        $this->correctThrough($senna, $first, -2);

        $registry = app(IssueRegistry::class);

        $this->assertFalse(
            $registry->conditionActive('stock_discrepancy:'.$first->id, $senna->service_id),
            'The one that was settled is settled.'
        );
        $this->assertTrue(
            $registry->conditionActive('stock_discrepancy:'.$second->id, $senna->service_id),
            'And the one that was not is still there.'
        );

        $this->assertCount(1, $this->ledger()->unresolvedDiscrepancies($senna->fresh()));
    }

    /** Raise, approve and carry out a reconciliation, as the screens do. */
    private function correctThrough(StockBalance $balance, StockMovement $target, float $delta): void
    {
        $item = \App\Models\Record7\ReviewItem::create([
            'reference' => 'R7SC-'.strtoupper(\Illuminate\Support\Str::random(10)),
            'organisation_id' => $target->organisation_id,
            'service_id' => $target->service_id,
            'kind' => 'correction_request',
            'title' => 'Test reconciliation',
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

        DB::connection('record7')->transaction(function () use ($target, $delta, $item) {
            $this->ledger()->compensate($this->user('sarah.ahmed'), $target, $delta, $item->id);
        });
    }

    public function test_only_a_correction_settles_a_disagreement(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->first();
        $key = 'stock_discrepancy:'.$entry->id;
        $registry = app(IssueRegistry::class);

        // Every workflow act somebody might reach for, one after another.
        $this->signInAt('daniel.evans', 'Rosewood House');
        $this->post('/record7/manager/acknowledge', ['issue_key' => $key]);
        $this->post('/record7/manager/own', ['issue_key' => $key]);
        $this->post('/record7/manager/escalate', ['issue_key' => $key, 'note' => 'Told the lead.']);
        $this->post('/record7/manager/action', ['issue_key' => $key, 'note' => 'Recounted twice.']);
        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Dealt with it.',
            'evidence_reference' => 'INCIDENT-2026-0777',
        ]);

        $this->assertTrue(
            $registry->conditionActive($key, $senna->service_id),
            'Acknowledging, owning, escalating, recording an action and closing '
            .'together do not put two tablets back.'
        );

        $this->correctThrough($senna, $entry, -2);

        $this->assertFalse($registry->conditionActive($key, $senna->service_id));
    }

    public function test_a_correction_moves_the_balance_to_what_was_counted(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->first();

        $this->assertSame('30.000', (string) $senna->current_balance);

        $this->correctThrough($senna, $entry, -2);

        $this->assertSame('28.000', (string) $senna->fresh()->current_balance);
    }

    /* ── 5. Shortfall: the record is never suppressed ───────────────────── */

    public function test_a_dose_the_ledger_cannot_cover_is_refused_without_verification(): void
    {
        $balance = $this->balanceFor('Margaret', 'Macrogol');
        $this->assertSame('0.000', (string) $balance->current_balance);

        $this->expectException(StockRefusal::class);

        $this->move($balance, 'administration', ['removed' => 1, 'given' => 1]);
    }

    public function test_a_dose_that_was_actually_given_is_recorded_and_the_shortfall_preserved(): void
    {
        $balance = $this->balanceFor('Margaret', 'Macrogol');

        $movement = $this->move($balance, 'administration', ['removed' => 1, 'given' => 1], [
            'shortfall' => $this->ledger()->verifyShortfall($this->user('sarah.ahmed'), [
                'shortfall_basis' => 'unrecorded_stock_present',
                'shortfall_statement' => 'A box arrived this morning that has not been booked in.',
                'shortfall_observed_quantity' => 6,
            ]),
        ]);

        // NEVER FLOORED. The legacy defect wrote 3 minus 5 as a balance of
        // zero, producing a ledger whose own three numbers contradict it.
        $this->assertSame('-1.000', (string) $movement->balance_after);
        $this->assertTrue((bool) $movement->is_discrepancy);
        $this->assertSame('-1.000', (string) $balance->fresh()->current_balance);

        $this->assertSame($this->user('sarah.ahmed')->id, $movement->shortfall_verified_by_user_id);
        $this->assertNotNull($movement->shortfall_verified_at);
        $this->assertSame('unrecorded_stock_present', $movement->shortfall_basis);
        $this->assertNotEmpty($movement->shortfall_statement);
        $this->assertSame('6.000', (string) $movement->shortfall_observed_quantity);
    }

    public function test_the_observed_quantity_is_not_a_count(): void
    {
        $balance = $this->balanceFor('Margaret', 'Macrogol');

        $movement = $this->move($balance, 'administration', ['removed' => 1, 'given' => 1], [
            'shortfall' => $this->ledger()->verifyShortfall($this->user('sarah.ahmed'), [
                'shortfall_basis' => 'physically_counted_sufficient',
                'shortfall_statement' => 'Counted what is here.',
                'shortfall_observed_quantity' => 6,
            ]),
        ]);

        // What a worker saw at the point of care is useful to whoever
        // reconciles this. It is NOT a verified count, and treating it as one
        // would let the cupboard be re-declared by somebody mid-round.
        $this->assertNull($movement->counted_quantity);
        $this->assertNull($movement->expected_quantity);
        $this->assertSame('administration', $movement->action);
        $this->assertNull($balance->fresh()->last_counted_at);
    }

    public function test_a_shortfall_needs_a_basis_and_a_statement(): void
    {
        $balance = $this->balanceFor('Margaret', 'Macrogol');

        foreach ([
            ['shortfall_basis' => null, 'shortfall_statement' => 'Checked.'],
            ['shortfall_basis' => 'invented_reason', 'shortfall_statement' => 'Checked.'],
            ['shortfall_basis' => 'physically_counted_sufficient', 'shortfall_statement' => '  '],
        ] as $attempt) {
            try {
                $this->ledger()->verifyShortfall($this->user('sarah.ahmed'), $attempt);
                $this->fail('Accepted: '.json_encode($attempt));
            } catch (StockRefusal $refused) {
                $this->assertNotEmpty($refused->refusalCode);
            }
        }
    }

    /* ── 6. Controlled medicines are not this ledger's ──────────────────── */

    public function test_a_controlled_medicine_cannot_have_an_ordinary_movement(): void
    {
        $oxycodone = Medicine::where('name', 'Oxycodone')->firstOrFail();
        $bridget = Client::where('preferred_name', 'Bridget')->firstOrFail();

        // Straight past every service, through raw SQL.
        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-CD-1',
            'organisation_id' => $this->rosewood()->organisation_id,
            'service_id' => $this->rosewood()->id,
            'owner_type' => 'client',
            'client_id' => $bridget->id,
            'medicine_id' => $oxycodone->id,
            'medicine_name_at_time' => 'Oxycodone',
            'form_at_time' => 'capsule',
            'strength_at_time' => '5mg',
            'unit' => 'capsule',
            'action' => 'opening_balance',
            'quantity_received' => 10,
            'balance_after' => 10,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_the_service_refuses_a_controlled_snapshot_before_anything_is_written(): void
    {
        $oxycodone = Medicine::where('name', 'Oxycodone')->firstOrFail();

        $this->expectException(StockRefusal::class);

        $this->ledger()->snapshot($oxycodone, 'capsule');
    }

    public function test_no_controlled_medicine_has_an_ordinary_balance_in_the_fixture(): void
    {
        $controlled = StockBalance::whereHas('medicine', fn ($q) => $q->where('is_controlled', true))->count();

        $this->assertSame(0, $controlled, 'Section 2.5 is the only authority for those.');
    }

    /* ── 7. Ownership: client only in this version ──────────────────────── */

    public function test_service_owned_stock_fails_closed(): void
    {
        // The column exists so a later section needs no data migration. Until
        // one deliberately enables it, a write for it is refused.
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-SVC-1',
            'organisation_id' => $balance->organisation_id,
            'service_id' => $balance->service_id,
            'owner_type' => 'service',
            'client_id' => null,
            'medicine_id' => $balance->medicine_id,
            'medicine_name_at_time' => 'Colecalciferol',
            'form_at_time' => 'tablet',
            'strength_at_time' => '800unit',
            'unit' => 'tablet',
            'action' => 'opening_balance',
            'quantity_received' => 5,
            'balance_after' => 5,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ── 8. Append-only, at both levels ─────────────────────────────────── */

    public function test_a_movement_cannot_be_rewritten_or_deleted(): void
    {
        $movement = StockMovement::orderByDesc('id')->firstOrFail();

        foreach ([
            'UPDATE record7_stock_movements SET notes = "tidied" WHERE id = ?',
            'UPDATE record7_stock_movements SET balance_after = 99 WHERE id = ?',
            'UPDATE record7_stock_movements SET is_discrepancy = 0 WHERE id = ?',
            'DELETE FROM record7_stock_movements WHERE id = ?',
        ] as $sql) {
            try {
                DB::connection('record7')->statement($sql, [$movement->id]);
                $this->fail('The database allowed: '.$sql);
            } catch (QueryException $refused) {
                $this->assertNotEmpty($refused->getMessage());
            }
        }
    }

    public function test_the_model_refuses_the_same_two_things(): void
    {
        $movement = StockMovement::orderByDesc('id')->firstOrFail();

        try {
            $movement->update(['notes' => 'tidied']);
            $this->fail('The model allowed a rewrite.');
        } catch (RuntimeException $refused) {
            $this->assertStringContainsStringIgnoringCase('permanent', $refused->getMessage());
        }

        try {
            $movement->delete();
            $this->fail('The model allowed a delete.');
        } catch (RuntimeException $refused) {
            $this->assertStringContainsStringIgnoringCase('cannot be deleted', $refused->getMessage());
        }
    }

    public function test_a_balance_cannot_be_moved_to_a_figure_the_ledger_never_reached(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        foreach ([
            'UPDATE record7_stock_balances SET current_balance = 500 WHERE id = ?',
            'UPDATE record7_stock_balances SET current_balance = 500, last_sequence_no = 9 WHERE id = ?',
            'UPDATE record7_stock_balances SET client_id = NULL, owner_type = "service" WHERE id = ?',
        ] as $sql) {
            try {
                DB::connection('record7')->statement($sql, [$balance->id]);
                $this->fail('The database allowed: '.$sql);
            } catch (QueryException $refused) {
                $this->assertNotEmpty($refused->getMessage());
            }
        }

        $this->assertSame('20.000', (string) $balance->fresh()->current_balance);
    }

    public function test_a_browser_supplied_balance_is_rejected_not_ignored(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-ARITH-1',
            'organisation_id' => $balance->organisation_id,
            'service_id' => $balance->service_id,
            'owner_type' => 'client',
            'client_id' => $balance->client_id,
            'medicine_id' => $balance->medicine_id,
            'medicine_name_at_time' => 'Colecalciferol',
            'form_at_time' => 'tablet',
            'strength_at_time' => '800unit',
            'unit' => 'tablet',
            'action' => 'receipt',
            'quantity_received' => 5,
            'balance_before' => 20,
            'balance_after' => 999,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_two_movements_cannot_claim_one_position_in_the_chain(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->expectException(QueryException::class);

        // Sequence 1 is the opening balance. A second row at the same position
        // is what two workers reading the same tail would both try to write.
        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-SEQ-1',
            'organisation_id' => $balance->organisation_id,
            'service_id' => $balance->service_id,
            'owner_type' => 'client',
            'client_id' => $balance->client_id,
            'medicine_id' => $balance->medicine_id,
            'medicine_name_at_time' => 'Colecalciferol',
            'form_at_time' => 'tablet',
            'strength_at_time' => '800unit',
            'unit' => 'tablet',
            'action' => 'receipt',
            'quantity_received' => 5,
            'balance_before' => 20,
            'balance_after' => 25,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ── 9. Thresholds ──────────────────────────────────────────────────── */

    public function test_low_stock_fires_only_where_a_reorder_level_is_recorded(): void
    {
        $joyce = $this->balanceFor('Joyce', 'Macrogol');
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->assertTrue($joyce->hasThreshold());
        $this->assertTrue($joyce->isLow(), 'Five against a level of six.');

        $this->assertFalse($dennis->hasThreshold());
        $this->assertFalse($dennis->isLow());
    }

    public function test_no_reorder_level_means_unavailable_and_never_healthy(): void
    {
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');

        // Drive it to one tablet. With no rule recorded, nothing can call that
        // low — and the screen has to say so in words rather than show a blank.
        $this->move($dennis, 'waste', ['wasted' => 19]);

        $fresh = $dennis->fresh();

        $this->assertSame('1.000', (string) $fresh->current_balance);
        $this->assertFalse($fresh->isLow(), 'Nothing invents a level.');
        $this->assertFalse($fresh->hasThreshold());
    }

    public function test_stock_out_needs_no_configuration_at_all(): void
    {
        $margaret = $this->balanceFor('Margaret', 'Macrogol');

        $this->assertFalse($margaret->hasThreshold());
        $this->assertTrue($margaret->isOut());
        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'stock_out:'.$margaret->id, $margaret->service_id
            )
        );
    }

    public function test_setting_a_reorder_level_is_recorded_and_audited(): void
    {
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->ledger()->setThreshold($dennis, 8, $this->user('sarah.ahmed'), 'A week of doses.');

        $threshold = StockThreshold::where('stock_balance_id', $dennis->id)->firstOrFail();
        $this->assertSame('8.000', (string) $threshold->low_threshold);
        $this->assertSame($this->user('sarah.ahmed')->id, $threshold->set_by_user_id);

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_threshold_set',
        ], 'record7');

        $this->ledger()->setThreshold($dennis, null, $this->user('sarah.ahmed'), null);

        $this->assertNull(StockThreshold::where('stock_balance_id', $dennis->id)->first());
        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_threshold_cleared',
        ], 'record7');
    }

    /* ── 10. Quantity comes from structure or not at all ────────────────── */

    public function test_a_quantity_is_never_read_out_of_the_dose_text(): void
    {
        $client = Client::where('preferred_name', 'Harold')->firstOrFail();

        $prescription = Prescription::create([
            'reference' => 'TEST-FREETEXT-1',
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('name', 'Senna')->firstOrFail()->id,
            'dose' => '10 ml (500mg)',
            'route' => 'Oral',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        // The legacy defect took '10 ml (500mg)' to mean 10500 and floored a
        // real 56-unit balance to zero on one tap. Nothing here reads it.
        $this->assertNull($this->ledger()->doseQuantity($prescription));
    }

    public function test_a_range_is_not_an_answer(): void
    {
        $client = Client::where('preferred_name', 'Harold')->firstOrFail();

        $prescription = Prescription::create([
            'reference' => 'TEST-RANGE-1',
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('name', 'Senna')->firstOrFail()->id,
            'dose' => 'One or two tablets',
            'dose_min' => 1,
            'dose_max' => 2,
            'dose_unit' => 'tablet',
            'route' => 'Oral',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        $this->assertNull(
            $this->ledger()->doseQuantity($prescription),
            'A range does not say what was taken; only the person who gave it can.'
        );

        $this->assertSame(
            2.0,
            $this->ledger()->doseQuantity($prescription, 2.0),
            'What was actually recorded as given does.'
        );
    }

    public function test_a_tracked_medicine_with_no_structured_dose_moves_nothing(): void
    {
        $insulin = $this->balanceFor('Sylvia', 'Insulin glargine');
        $before = (string) $insulin->current_balance;

        $prescription = Prescription::whereHas(
            'medicine', fn ($q) => $q->where('name', 'Insulin glargine')
        )->where('client_id', $insulin->client_id)->firstOrFail();

        $this->assertNull($this->ledger()->doseQuantity($prescription));
        $this->assertSame($before, (string) $insulin->fresh()->current_balance);
    }

    /* ── 11. Self-managed medicines are not accounted stock ─────────────── */

    public function test_a_self_managed_medicine_never_moves_a_balance(): void
    {
        $client = Client::where('preferred_name', 'Harold')->firstOrFail();

        $selfManaged = Prescription::create([
            'reference' => 'TEST-SELF-1',
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('name', 'Senna')->firstOrFail()->id,
            'dose' => 'Two tablets',
            'dose_min' => 2, 'dose_max' => 2, 'dose_unit' => 'tablet',
            'route' => 'Oral',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'support_type' => 'self_administered',
            'self_administration_monitoring' => 'none',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        $this->assertFalse($this->ledger()->consumesAccountedStock($selfManaged));

        $monitored = $selfManaged->replicate();
        $monitored->reference = 'TEST-SELF-2';
        $monitored->self_administration_monitoring = 'check_and_record';
        $monitored->save();

        $this->assertTrue(
            $this->ledger()->consumesAccountedStock($monitored),
            'Staff handed it over and wrote that down, so it left accountable stock.'
        );
    }

    /* ── 12. The clinical path ──────────────────────────────────────────── */

    public function test_giving_a_tracked_dose_debits_the_balance_atomically(): void
    {
        $round = $this->enterRound('noah.williams', 'Oakwood House');
        $balance = $this->balanceFor('Joyce', 'Macrogol');

        // BUILT, NOT BORROWED. A fixture dose that already has an outcome
        // refuses for the wrong reason, and the test then passes without
        // exercising the rule it names.
        $dose = $this->freshDoseFor($round, $balance);
        $before = (float) $balance->current_balance;

        $this->post('/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/given')
            ->assertRedirect();

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertNotNull($administration->stock_movement_id, 'The dose carries its movement.');
        $this->assertSame($before - 1.0, (float) $balance->fresh()->current_balance);

        $movement = StockMovement::findOrFail($administration->stock_movement_id);
        $this->assertSame('administration', $movement->action);
        $this->assertSame('1.000', (string) $movement->quantity_given);
        $this->assertSame('1.000', (string) $movement->quantity_removed);
    }

    public function test_an_administration_of_an_untracked_medicine_moves_no_stock(): void
    {
        // REPLACES part of the Section 2.2 guarantee, narrowed rather than
        // dropped. Nothing is being counted for this medicine, so a dose
        // changes nothing and claims nothing.
        $round = $this->enterRound('noah.williams', 'Oakwood House');
        $dose = $this->doseFor($round, 'Aisha', 'Ferrous fumarate')
            ?? $this->doseFor($round, 'Terry', 'Lansoprazole');

        if (! $dose || $dose->administration) {
            $this->markTestSkipped('No untracked dose is open in this round.');
        }

        $movements = StockMovement::count();

        $this->post('/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/given');

        $this->assertSame($movements, StockMovement::count());
        $this->assertNull(
            Administration::where('scheduled_dose_id', $dose->id)->first()?->stock_movement_id
        );
    }

    /* ── 13. Permissions ────────────────────────────────────────────────── */

    public function test_the_fixture_can_exercise_both_stock_workflows_without_manual_grants(): void
    {
        $policy = app(\App\Services\Record7\AccessPolicy::class);
        $rosewood = $this->rosewood()->id;

        $sarah = $this->user('sarah.ahmed');
        $daniel = $this->user('daniel.evans');
        $olivia = $this->user('olivia.carter');

        $this->assertTrue($policy->allows($sarah, 'stock_management', $rosewood));
        $this->assertTrue($policy->allows($sarah, 'reconciliation', $rosewood));

        $this->assertTrue($policy->allows($daniel, 'stock_management', $rosewood));
        $this->assertTrue($policy->allows($daniel, 'correction_approval', $rosewood));

        // THE POINT OF THE SPLIT. A stock manager may count, and may not
        // declare what the true balance is.
        $this->assertFalse(
            $policy->allows($daniel, 'reconciliation', $rosewood),
            'A stock manager cannot erase a discrepancy.'
        );

        $this->assertFalse($policy->allows($olivia, 'stock_management', $rosewood));
        $this->assertFalse($policy->allows($olivia, 'reconciliation', $rosewood));
    }

    public function test_a_worker_without_stock_rights_cannot_record_a_count(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');
        $balance = $this->balanceFor('Sylvia', 'Senna');

        $this->post('/record7/stock/'.$balance->id.'/count', ['counted' => 1])
            ->assertStatus(403);
    }

    public function test_a_stock_manager_cannot_carry_out_a_reconciliation(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $entry = $this->ledger()->unresolvedDiscrepancies($senna)->first();

        $this->post('/record7/stock/movement/'.$entry->id.'/correct')->assertStatus(403);
    }

    /* ── 14. Tenant isolation ───────────────────────────────────────────── */

    public function test_a_balance_from_another_house_is_not_found(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');
        $rosewoodBalance = $this->balanceFor('Sylvia', 'Senna');

        $this->get('/record7/stock/'.$rosewoodBalance->id)->assertStatus(404);
        $this->post('/record7/stock/'.$rosewoodBalance->id.'/count', ['counted' => 1])
            ->assertStatus(404);
    }

    public function test_the_stock_screen_shows_only_this_house(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');

        $body = $this->get('/record7/stock')->assertOk()->getContent();

        $this->assertStringContainsString('Margaret', $body);
        $this->assertStringNotContainsString('Sylvia', $body, 'Another house leaked in.');
    }

    /* ── 15. The retired tables ─────────────────────────────────────────── */

    public function test_the_old_stock_level_table_is_read_only(): void
    {
        $row = DB::connection('record7')->table('record7_stock_levels')->first();

        if (! $row) {
            $this->markTestSkipped('No legacy stock levels remain to test against.');
        }

        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_levels')
            ->where('id', $row->id)->update(['quantity' => 500]);
    }

    public function test_an_old_discrepancy_cannot_be_resolved_any_more(): void
    {
        $row = DB::connection('record7')->table('record7_stock_events')
            ->where('kind', 'discrepancy')->first();

        if (! $row) {
            $this->markTestSkipped('No legacy discrepancy remains to test against.');
        }

        $this->expectException(QueryException::class);

        DB::connection('record7')->table('record7_stock_events')
            ->where('id', $row->id)->update(['resolved_at' => now()]);
    }

    public function test_a_delivery_that_has_not_arrived_may_still_be_closed(): void
    {
        // NOT AN EXCEPTION TO THE RULE. A delivery asserts no quantity, so
        // "it arrived" genuinely is the fact that ends it, and nothing about
        // closing it can make a missing quantity cease to exist.
        // Scoped to a LIVE house. Fifteen of the eighteen legacy rows belong
        // to services an old reseed deleted, and touching one fails its own
        // foreign key — which is the debris Section 2.7 deliberately leaves
        // where it is rather than promoting into the ledger.
        $event = \App\Models\Record7\StockEvent::where('kind', 'delivery_overdue')
            ->whereNull('resolved_at')
            ->whereIn('service_id', [$this->oakwood()->id, $this->rosewood()->id])
            ->first();

        if (! $event) {
            $this->markTestSkipped('No overdue delivery in the fixture.');
        }

        $registry = app(IssueRegistry::class);
        $key = 'stock_event:'.$event->id;

        $this->assertTrue($registry->conditionActive($key, $event->service_id));

        $event->forceFill(['resolved_at' => now()])->save();

        $this->assertFalse($registry->conditionActive($key, $event->service_id));
    }

    /* ── 16. The manager board ──────────────────────────────────────────── */

    public function test_the_board_shows_one_card_per_balance_and_lists_every_entry(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $concerns = app(ManagerBoard::class)->stockConcerns($senna->service_id);

        $card = collect($concerns)->firstWhere('kind', 'stock_discrepancy');

        $this->assertNotNull($card);
        $this->assertSame(2, $card['unresolvedCount']);
        $this->assertCount(2, $card['entries'], 'Aggregated for the eye, never for the ledger.');

        $open = $this->ledger()->unresolvedDiscrepancies($senna);

        $this->assertSame(
            'stock_discrepancy:'.$open->first()->id,
            $card['key'],
            'The card is actionable through the oldest unresolved entry.'
        );

        foreach ($card['entries'] as $entry) {
            $this->assertArrayHasKey('key', $entry);
            $this->assertArrayHasKey('movementId', $entry);
        }
    }

    public function test_the_card_survives_settling_one_of_its_entries(): void
    {
        $senna = $this->balanceFor('Sylvia', 'Senna');
        $first = $this->ledger()->unresolvedDiscrepancies($senna)->first();

        $this->correctThrough($senna, $first, -2);

        $card = collect(app(ManagerBoard::class)->stockConcerns($senna->service_id))
            ->firstWhere('kind', 'stock_discrepancy');

        $this->assertNotNull($card, 'One settled entry does not settle the balance.');
        $this->assertSame(1, $card['unresolvedCount']);
    }

    public function test_ordinary_and_controlled_cards_differ_in_more_than_colour(): void
    {
        $ordinary = ManagerBoard::ORDINARY_DISCREPANCY_CONSEQUENCE;
        $controlled = ManagerBoard::CONTROLLED_DISCREPANCY_CONSEQUENCE;

        // Asserted verbatim, so a later redesign cannot quietly soften either.
        $this->assertSame(
            'Medicine may still be administered if physical availability is verified. '
            .'Reconciliation required.',
            $ordinary
        );
        $this->assertSame('Further CD movement is blocked until reconciled.', $controlled);
        $this->assertNotSame($ordinary, $controlled);

        $card = collect(app(ManagerBoard::class)->stockConcerns($this->rosewood()->id))
            ->firstWhere('kind', 'stock_discrepancy');

        $this->assertSame($ordinary, $card['next'], 'The consequence is on the card itself.');
        $this->assertFalse($card['controlled']);
    }

    public function test_the_board_no_longer_derives_a_controlled_discrepancy_from_stock_events(): void
    {
        foreach (app(ManagerBoard::class)->stockConcerns($this->rosewood()->id) as $concern) {
            if ($concern['kind'] === 'controlled_drug_discrepancy') {
                $this->assertTrue(
                    $concern['controlled'],
                    'A controlled discrepancy can only come from the register now.'
                );
            }
        }

        // And no stock event of a controlled medicine is offered at all.
        $keys = array_column(app(ManagerBoard::class)->stockConcerns($this->rosewood()->id), 'kind');
        $this->assertNotContains('stock_discrepancy_controlled', $keys);
    }

    /* ── 17. Audit ──────────────────────────────────────────────────────── */

    public function test_a_count_and_its_disagreement_are_both_audited(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $page = $this->get('/record7/stock/'.$balance->id)->assertOk();
        $token = $page->viewData('page')['props']['tokens']['count'];

        $this->post('/record7/stock/'.$balance->id.'/count', [
            'counted' => 15,
            'attempt_token' => $token,
        ])->assertRedirect();

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_counted',
        ], 'record7');

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_discrepancy_found',
        ], 'record7');
    }

    public function test_a_blocked_stock_act_is_audited_after_the_transaction_unwinds(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        // No token: refused inside a transaction that then rolls back. The
        // record of the refusal must survive the unwind.
        try {
            $this->post('/record7/stock/'.$balance->id.'/receipt', ['quantity' => 5]);
        } catch (\Throwable) {
            // The refusal is the point; how the framework renders it is not.
        }

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'stock_movement_blocked',
        ], 'record7');
    }

    /* ── 18. Replay ─────────────────────────────────────────────────────── */

    public function test_a_receipt_cannot_be_recorded_twice_with_one_token(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $page = $this->get('/record7/stock/'.$balance->id)->assertOk();
        $token = $page->viewData('page')['props']['tokens']['receipt'];

        $this->post('/record7/stock/'.$balance->id.'/receipt', [
            'quantity' => 10, 'attempt_token' => $token,
        ])->assertRedirect();

        $after = (string) $balance->fresh()->current_balance;
        $this->assertSame('30.000', $after);

        // The same submission again — a double click, or a retry.
        $this->post('/record7/stock/'.$balance->id.'/receipt', [
            'quantity' => 10, 'attempt_token' => $token,
        ]);

        $this->assertSame(
            '30.000',
            (string) $balance->fresh()->current_balance,
            'A double submit doubles a delivery unless something stops it.'
        );
    }

    public function test_a_token_cannot_be_spent_on_another_balance(): void
    {
        $this->signInAt('sarah.ahmed', 'Oakwood House');
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');
        $joyce = $this->balanceFor('Joyce', 'Macrogol');

        $token = $this->get('/record7/stock/'.$dennis->id)
            ->viewData('page')['props']['tokens']['receipt'];

        try {
            $this->post('/record7/stock/'.$joyce->id.'/receipt', [
                'quantity' => 5, 'attempt_token' => $token,
            ]);
        } catch (\Throwable) {
            // Refused, which is the assertion below.
        }

        $this->assertSame('5.000', (string) $joyce->fresh()->current_balance);
    }

    /* ── 19. Atomicity ──────────────────────────────────────────────────── */

    public function test_a_failed_administration_leaves_no_debit_behind(): void
    {
        $balance = $this->balanceFor('Joyce', 'Macrogol');
        $before = (string) $balance->current_balance;
        $movements = StockMovement::count();

        // A movement is written, then the administration insert is made to
        // fail. Both must disappear together.
        try {
            DB::connection('record7')->transaction(function () use ($balance) {
                $locked = $this->ledger()->lockExisting($balance);

                $this->ledger()->record(
                    balance: $locked,
                    snapshot: $this->snapshotOf($balance),
                    action: 'administration',
                    quantities: ['removed' => 1, 'given' => 1],
                    user: $this->user('sarah.ahmed'),
                    client: $balance->client,
                    house: Service::findOrFail($balance->service_id),
                );

                throw new RuntimeException('The clinical record failed to write.');
            });
        } catch (RuntimeException) {
            // Expected.
        }

        $this->assertSame($movements, StockMovement::count(), 'The debit rolled back with it.');
        $this->assertSame($before, (string) $balance->fresh()->current_balance);
    }


    /**
     * The refusal a worker actually meets, in words.
     *
     * The CHECK constraint catches this too, and must — but a person at a
     * trolley should never be shown a database error. Added because a mutation
     * removing the service-level guard was killed by only one test.
     */
    public function test_the_shortfall_refusal_says_what_to_do(): void
    {
        $balance = $this->balanceFor('Margaret', 'Macrogol');

        try {
            $this->move($balance, 'administration', ['removed' => 1, 'given' => 1]);
            $this->fail('A dose was recorded against a balance that could not cover it.');
        } catch (StockRefusal $refused) {
            $this->assertSame('shortfall_unverified', $refused->refusalCode);
            $this->assertStringContainsStringIgnoringCase('check what is physically there', $refused->getMessage());
        }

        $this->assertSame('0.000', (string) $balance->fresh()->current_balance);
    }

    /**
     * The two cards differ in the ACTION they offer, not only in their wording.
     * Added because the wording assertion alone left the distinction resting on
     * one string.
     */
    public function test_an_ordinary_discrepancy_card_offers_a_different_action_from_a_controlled_one(): void
    {
        $ordinary = collect(app(ManagerBoard::class)->stockConcerns($this->rosewood()->id))
            ->firstWhere('kind', 'stock_discrepancy');

        $this->assertNotNull($ordinary);
        $this->assertSame('stock_discrepancy', $ordinary['kind']);
        $this->assertFalse($ordinary['controlled']);
        $this->assertStringContainsString('may still be administered', $ordinary['next']);
        $this->assertStringNotContainsString('blocked', $ordinary['next']);

        // And the two constants are genuinely different sentences, so a card
        // cannot be mistaken for the other kind with the colour turned off.
        $this->assertStringContainsString(
            'blocked', ManagerBoard::CONTROLLED_DISCREPANCY_CONSEQUENCE
        );
        $this->assertStringNotContainsString(
            'blocked', ManagerBoard::ORDINARY_DISCREPANCY_CONSEQUENCE
        );
    }


    public function test_the_model_freezes_what_a_dose_did_to_the_cupboard(): void
    {
        $administration = Administration::whereNotNull('stock_movement_id')->first();

        if (! $administration) {
            $balance = $this->balanceFor('Dennis', 'Colecalciferol');
            $movement = $this->move($balance, 'receipt', ['received' => 1]);

            $administration = Administration::create([
                'reference' => 'TEST-FROZEN-1',
                'prescription_id' => Prescription::where('client_id', $balance->client_id)
                    ->where('medicine_id', $balance->medicine_id)->firstOrFail()->id,
                'client_id' => $balance->client_id,
                'service_id' => $balance->service_id,
                'recorded_by_user_id' => $this->user('olivia.carter')->id,
                'outcome' => 'given',
                'administered_at' => now(),
            ]);
        }

        foreach (['stock_movement_id' => null, 'stock_no_quantity_removed' => true] as $field => $value) {
            try {
                $administration->update([$field => $value]);
                $this->fail("The model allowed {$field} to be changed.");
            } catch (RuntimeException $refused) {
                $this->assertStringContainsStringIgnoringCase('permanent record', $refused->getMessage());
            }
        }
    }


    /**
     * A second worker computing from a balance that has already moved.
     *
     * The first test proves the index refuses a hand-written duplicate. This
     * proves it refuses the way it would actually happen: two requests both
     * holding a balance object read before either wrote.
     */
    public function test_a_stale_balance_cannot_write_over_a_position_already_taken(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $stale = $balance->replicate();
        $stale->id = $balance->id;
        $stale->exists = true;

        $this->move($balance, 'receipt', ['received' => 1]);

        $this->expectException(QueryException::class);

        // The stale copy still believes it is at the old position.
        DB::connection('record7')->transaction(function () use ($stale) {
            $this->ledger()->record(
                balance: $stale,
                snapshot: $this->snapshotOf($stale),
                action: 'receipt',
                quantities: ['received' => 1],
                user: $this->user('sarah.ahmed'),
                client: $stale->client,
                house: Service::findOrFail($stale->service_id),
            );
        });
    }

    /**
     * The route itself names the permission, not just the controller.
     *
     * Both have to say `reconciliation`; a mutation that widened either alone
     * would otherwise be caught by nothing but the other.
     */
    public function test_the_reconciliation_route_is_gated_on_reconciliation(): void
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()
            ->getByName('record7.stock.correct');

        $this->assertNotNull($route);

        $middleware = implode(' ', $route->gatherMiddleware());

        $this->assertStringContainsString('reconciliation', $middleware);
        $this->assertStringNotContainsString('stock_management', $middleware);
    }

    /**
     * A duplicate dose leaves no orphan debit.
     *
     * The other atomicity test forces the clinical write to fail. This one uses
     * the way it actually happens: the one-answer-per-dose index refuses the
     * second administration, and the movement written alongside it must go too.
     */
    public function test_a_duplicate_dose_leaves_no_orphan_movement(): void
    {
        $round = $this->enterRound('noah.williams', 'Oakwood House');
        $balance = $this->balanceFor('Joyce', 'Macrogol');
        $dose = $this->freshDoseFor($round, $balance);

        $url = '/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/given';

        $this->post($url)->assertRedirect();

        $after = StockMovement::count();
        $balanceAfter = (string) $balance->fresh()->current_balance;

        // The same submission again.
        $this->post($url);

        $this->assertSame($after, StockMovement::count(), 'A second dose wrote a second debit.');
        $this->assertSame($balanceAfter, (string) $balance->fresh()->current_balance);
    }

    /**
     * The balance head has its own insert guard, and it is not the movement
     * trigger. A controlled medicine, another house's person, and a head that
     * opens with a figure already in it are each refused here.
     */
    public function test_a_balance_head_cannot_be_opened_wrongly(): void
    {
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');
        $oxycodone = Medicine::where('name', 'Oxycodone')->firstOrFail();
        $rosewoodClient = Client::where('preferred_name', 'Sylvia')->firstOrFail();

        $row = fn (array $override) => array_merge([
            'organisation_id' => $dennis->organisation_id,
            'service_id' => $dennis->service_id,
            'owner_type' => 'client',
            'client_id' => $dennis->client_id,
            'medicine_id' => $dennis->medicine_id,
            'preparation_key' => hash('sha256', 'test-'.uniqid()),
            'unit' => 'tablet',
            'current_balance' => 0,
            'last_sequence_no' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $override);

        foreach ([
            'a controlled medicine' => ['medicine_id' => $oxycodone->id],
            'service ownership' => ['owner_type' => 'service', 'client_id' => null],
        ] as $what => $override) {
            try {
                DB::connection('record7')->table('record7_stock_balances')->insert($row($override));
                $this->fail('The database opened a balance for '.$what);
            } catch (QueryException $refused) {
                $this->assertNotEmpty($refused->getMessage());
            }
        }
    }


    /**
     * And the head cannot be opened outside its own house, or already holding
     * stock. Split from the test above so the guard does not rest on one
     * method: a balance that arrives with a figure in it has bypassed the
     * ledger by definition.
     */
    public function test_a_balance_head_cannot_be_opened_out_of_scope_or_pre_filled(): void
    {
        $dennis = $this->balanceFor('Dennis', 'Colecalciferol');
        $rosewoodClient = Client::where('preferred_name', 'Sylvia')->firstOrFail();

        $row = fn (array $override) => array_merge([
            'organisation_id' => $dennis->organisation_id,
            'service_id' => $dennis->service_id,
            'owner_type' => 'client',
            'client_id' => $dennis->client_id,
            'medicine_id' => $dennis->medicine_id,
            'preparation_key' => hash('sha256', 'scope-'.uniqid()),
            'unit' => 'tablet',
            'current_balance' => 0,
            'last_sequence_no' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $override);

        foreach ([
            'somebody from another house' => ['client_id' => $rosewoodClient->id],
            'a head that opens with stock already in it' => ['current_balance' => 40],
            'a head that opens mid-chain' => ['last_sequence_no' => 6],
        ] as $what => $override) {
            try {
                DB::connection('record7')->table('record7_stock_balances')->insert($row($override));
                $this->fail('The database opened a balance for '.$what);
            } catch (QueryException $refused) {
                $this->assertNotEmpty($refused->getMessage());
            }
        }
    }


    /**
     * THE SEQUENCE IS ALLOCATED UNDER THE LOCK, and the index is what makes
     * that mean something.
     *
     * The behavioural half: after a run of movements the chain is 1..n with no
     * gap and no repeat, and the head names the last of them. If the unique key
     * were gone, the stale-balance test would write a second row at a position
     * already taken and this walk would find two movements claiming one number.
     */
    public function test_the_chain_has_exactly_one_movement_at_every_position(): void
    {
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');

        $this->move($balance, 'receipt', ['received' => 3]);
        $this->move($balance->fresh(), 'waste', ['wasted' => 1]);

        // A worker whose balance was read before any of that, writing now —
        // the shape a second request actually takes. It is expected to be
        // refused; what this test is about is the state it leaves behind
        // either way, so the refusal is swallowed and the chain is walked.
        $stale = $balance->replicate();
        $stale->id = $balance->id;
        $stale->exists = true;

        try {
            DB::connection('record7')->transaction(function () use ($stale) {
                $this->ledger()->record(
                    balance: $stale,
                    snapshot: $this->snapshotOf($stale),
                    action: 'receipt',
                    quantities: ['received' => 5],
                    user: $this->user('sarah.ahmed'),
                    client: $stale->client,
                    house: Service::findOrFail($stale->service_id),
                );
            });
        } catch (QueryException) {
            // Refused, which is the intended outcome.
        }

        $fresh = $balance->fresh();

        $sequences = StockMovement::where('service_id', $fresh->service_id)
            ->where('owner_ref', $fresh->owner_ref)
            ->where('preparation_key', $fresh->preparation_key)
            ->orderBy('sequence_no')
            ->pluck('sequence_no')
            ->all();

        $this->assertSame(
            range(1, count($sequences)),
            array_map('intval', $sequences),
            'The chain has a gap or a repeat.'
        );

        $this->assertSame(
            count($sequences),
            count(array_unique($sequences)),
            'Two movements claim one position in the chain.'
        );

        $this->assertSame((int) $fresh->last_sequence_no, (int) end($sequences));
    }

    /**
     * And the adversarial half: raw SQL, straight past the service and its
     * lock, still cannot put two movements at one position.
     *
     * Deliberately separate from the test above. One proves the application
     * allocates correctly; this proves the database would refuse even if it
     * did not.
     */
    public function test_raw_sql_cannot_duplicate_a_position_even_with_the_lock_bypassed(): void
    {
        $balance = $this->balanceFor('Joyce', 'Macrogol');

        $existing = StockMovement::where('service_id', $balance->service_id)
            ->where('owner_ref', $balance->owner_ref)
            ->where('preparation_key', $balance->preparation_key)
            ->orderBy('sequence_no')
            ->firstOrFail();

        $this->expectException(QueryException::class);

        // Every identifying column copied from a movement that already exists,
        // so the ONLY thing that can refuse this is the composite unique key.
        DB::connection('record7')->table('record7_stock_movements')->insert([
            'reference' => 'TEST-RAWSEQ-'.uniqid(),
            'organisation_id' => $existing->organisation_id,
            'service_id' => $existing->service_id,
            'owner_type' => 'client',
            'client_id' => $existing->client_id,
            'medicine_id' => $existing->medicine_id,
            'medicine_name_at_time' => $existing->medicine_name_at_time,
            'form_at_time' => $existing->form_at_time,
            'strength_at_time' => $existing->strength_at_time,
            'unit' => $existing->unit,
            'action' => 'receipt',
            'quantity_received' => 1,
            'balance_before' => 0,
            'balance_after' => 1,
            'recorded_by_user_id' => $this->user('sarah.ahmed')->id,
            'occurred_at' => now(),
            'sequence_no' => $existing->sequence_no,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /* ── 20. Locking, structurally ──────────────────────────────────────── */

    public function test_the_balance_is_locked_before_any_arithmetic(): void
    {
        // STRUCTURAL EVIDENCE ONLY. A single-threaded suite cannot watch a lock
        // hold anybody up; this asserts that FOR UPDATE is issued before the
        // figure is read. Genuine serialisation is proved by the two-connection
        // probe in the scratchpad, exactly as Sections 2.5 and 2.6 do it.
        $balance = $this->balanceFor('Dennis', 'Colecalciferol');
        $statements = [];

        DB::connection('record7')->listen(function ($query) use (&$statements) {
            $statements[] = $query->sql;
        });

        DB::connection('record7')->transaction(function () use ($balance) {
            $this->ledger()->lockExisting($balance);
        });

        $locking = collect($statements)->filter(
            fn ($sql) => str_contains($sql, 'record7_stock_balances') && str_contains($sql, 'for update')
        );

        $this->assertNotEmpty($locking, 'The head is read without a lock.');
    }

    public function test_opening_a_balance_inserts_before_it_locks(): void
    {
        // Taking FOR UPDATE on a row that does not exist sets a gap lock under
        // REPEATABLE READ, and two workers opening the same first movement
        // would deadlock on the gap rather than queue.
        $statements = [];

        DB::connection('record7')->listen(function ($query) use (&$statements) {
            $statements[] = strtolower($query->sql);
        });

        $client = Client::where('preferred_name', 'Terry')->firstOrFail();
        $medicine = Medicine::where('name', 'Lansoprazole')->firstOrFail();

        DB::connection('record7')->transaction(function () use ($client, $medicine) {
            $this->ledger()->lockBalance(
                $client,
                Service::findOrFail($client->service_id),
                $this->ledger()->snapshot($medicine, 'capsule')
            );
        });

        $insertAt = collect($statements)->search(fn ($s) => str_contains($s, 'insert ignore'));
        $lockAt = collect($statements)->search(fn ($s) => str_contains($s, 'for update'));

        $this->assertNotFalse($insertAt, 'No insertOrIgnore before the lock.');
        $this->assertNotFalse($lockAt);
        $this->assertLessThan($lockAt, $insertAt, 'The lock must follow the insert, not precede it.');
    }
}
