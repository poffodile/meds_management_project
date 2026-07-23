<?php

namespace Tests\Feature;

use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Models\MedicationRoundClosure;
use App\Services\Staff\MARSheetService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * The safety rules of the medication write path, as executable tests.
 *
 * ================== WHY THIS FILE EXISTS ==================
 *
 * On 2026-07-16 three separate "verified" claims about this write path turned out to
 * be false, all for the same reason: they were checked against a database that had
 * already been hand-shaped into the state the checker wanted, instead of against what
 * the product actually produces.
 *
 *   1. The original PRN demo fixtures were INSERTed directly with realistic clock
 *      times, so the daily-max block appeared to work. A real carer submits every PRN
 *      dose against one fixed nominal slot, where it did not work at all.
 *   2. A double-deduct guard keyed on time_slot made PRN doses 2..n look like edits of
 *      dose 1 and skip stock deduction. Found only because one test happened to print
 *      stock after every dose rather than merely asserting the block fired.
 *   3. The PRN interval block "passed" only because a migration had backfilled
 *      administered_at AFTER the seeder ran. On a clean seed the column was NULL and
 *      the block silently did nothing.
 *
 * So the rules here are:
 *
 *   - Build the fixture THE WAY THE PRODUCT DOES. If a prescription can only reach a
 *     safe state via a direct DB write that no user can perform, the safety rule does
 *     not exist. See test_prn_limits_are_reachable_through_the_product().
 *   - Assert the SIDE EFFECTS, not just the happy path. Every dose test checks the row
 *     count AND the stock movement, because that is where the hidden bug was.
 *   - Prove a guard by BREAKING it, not by watching it not complain.
 *
 * ================== SAFETY ==================
 *
 * DatabaseTransactions (NOT RefreshDatabase). This project has no .env.testing and
 * phpunit.xml does not override DB_DATABASE, so tests run against the REAL `laravel`
 * database. RefreshDatabase here would drop it — and because the schema came from a
 * dump with no create-migrations for `home`/`service_user`, it would not come back.
 * Three existing tests in this suite do use RefreshDatabase; running the full suite is
 * currently destructive. Do not follow their lead.
 */
class MedicationRoundSafetyTest extends TestCase
{
    use DatabaseTransactions;

    private User $neptuneStaff;

    private MARSheetService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->neptuneStaff = User::find(427);
        $this->service = app(MARSheetService::class);

        if (! $this->neptuneStaff) {
            $this->markTestSkipped('Demo data absent — run NeptuneHouseDemoSeeder.');
        }
    }

    /** Record a dose exactly the way the round page does. */
    private function record(array $args, ?int $userId = null): string
    {
        $this->actingAs(User::find($userId ?? 427));

        $controller = new \App\Http\Controllers\frontEnd\Medication\MedicationRoundController();
        $method = new \ReflectionMethod($controller, 'applyRecord');
        $method->setAccessible(true);

        try {
            $method->invoke($controller, Request::create('/x', 'POST', $args), $this->service);

            return 'accepted';
        } catch (ValidationException $e) {
            return 'blocked';
        }
    }

    private function prnSheet(int $clientId): ?MARSheet
    {
        return MARSheet::forHome(101)->active()->where('client_id', $clientId)->where('as_required', 1)->first();
    }

    /** Insert a past "given" administration directly (home_id is not fillable on the model). */
    private function seedAdministration(int $sheetId, string $slot, \Illuminate\Support\Carbon $at): void
    {
        \Illuminate\Support\Facades\DB::table('mar_administrations')->insert([
            'mar_sheet_id' => $sheetId, 'home_id' => 101, 'date' => now()->toDateString(),
            'time_slot' => $slot, 'administered_at' => $at, 'given' => 1, 'code' => 'A',
            'administered_by' => 427, 'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    // ---------------------------------------------------------------- PRN

    /**
     * THE TEST THAT MATTERS MOST.
     *
     * Every other PRN test below passes only because the seeder writes prn_max_daily
     * and prn_min_interval_hours with a direct DB write. This test builds the sheet the
     * way the product builds one, and asserts the limits survive.
     *
     * It currently FAILS (audit D1): those two columns are absent from MARSheet::$fillable
     * and from every validation/only() list, so no prescription a real user creates can
     * ever carry a PRN limit. The enforcement below is unreachable in production.
     *
     * Do not delete this test to make the suite green. Fix $fillable.
     */
    public function test_prn_limits_are_reachable_through_the_product(): void
    {
        $sheet = $this->service->store([
            'client_id' => 243,
            'medication_name' => 'Test PRN medicine',
            'dose' => '1 tablet',
            'dose_quantity' => 1,
            'unit' => 'tablet',
            'form' => 'Tablet',
            'route' => 'Oral',
            'time_slots' => ['12:00'],
            'as_required' => true,
            'prn_max_daily' => 4,
            'prn_min_interval_hours' => 4.0,
            'stock_level' => 20,
            'mar_status' => 'active',
        ], 101, 427);

        $fresh = MARSheet::find($sheet->id);

        $this->assertSame(4, (int) $fresh->prn_max_daily,
            'prn_max_daily did not persist. A PRN prescription created through the product '
            .'carries no daily maximum, so the limit can never be enforced for a real resident.');
        $this->assertEqualsWithDelta(4.0, (float) $fresh->prn_min_interval_hours, 0.001,
            'prn_min_interval_hours did not persist — the minimum-interval block is unreachable.');
    }

    /**
     * With NO limits set — which is every real prescription today — a double-tap must
     * still not record two doses. Nothing else is protecting this path.
     */
    public function test_double_tap_on_a_prn_without_limits_does_not_double_record(): void
    {
        $sheet = $this->prnSheet(243);
        $this->assertNotNull($sheet);

        // Put it in the state a product-created prescription is actually in.
        MARSheet::where('id', $sheet->id)->update([
            'prn_max_daily' => null, 'prn_min_interval_hours' => null, 'stock_level' => 12,
        ]);
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        $slot = $sheet->time_slots[0] ?? '12:00';
        $args = ['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose];

        $this->record($args);
        $this->record($args);   // the same real dose event, submitted twice

        $rows = MARAdministration::where('mar_sheet_id', $sheet->id)->whereDate('date', now())->count();
        $stock = (float) MARSheet::find($sheet->id)->stock_level;

        $this->assertSame(1, $rows, 'One dose event produced more than one administration row.');
        $this->assertEqualsWithDelta(10.0, $stock, 0.001, 'Stock was deducted twice for a single dose event.');
    }

    /**
     * These two tests PROVISION THEIR OWN same-day state rather than trusting the
     * seeder's date-relative fixtures. The seeder stamps "today" at seed time; run the
     * suite a day later and those rows are yesterday's, so the block silently stops
     * firing and the test passes for the wrong reason (accepted, not blocked) or fails
     * with no code change. A test that depends on when the seeder last ran is exactly
     * the rotting-fixture trap this whole effort exists to kill.
     */
    public function test_prn_daily_maximum_blocks_the_next_dose(): void
    {
        $sheet = $this->prnSheet(233);
        $this->assertNotNull($sheet, 'PRN fixture missing.');
        $slot = $sheet->time_slots[0] ?? '12:00';

        MARSheet::where('id', $sheet->id)->update(['prn_max_daily' => 4, 'prn_min_interval_hours' => 4.0]);
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        for ($i = 0; $i < 4; $i++) {
            $this->seedAdministration($sheet->id, $slot, now()->subHours(($i + 1) * 5));
        }

        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose,
        ]), 'A fifth dose was allowed past a daily maximum of four.');
    }

    public function test_prn_minimum_interval_blocks_the_next_dose(): void
    {
        $sheet = $this->prnSheet(235);
        $this->assertNotNull($sheet, 'PRN fixture missing.');
        $slot = $sheet->time_slots[0] ?? '12:00';

        MARSheet::where('id', $sheet->id)->update(['prn_max_daily' => 4, 'prn_min_interval_hours' => 4.0]);
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        // One dose an hour ago — inside a four-hour interval.
        $this->seedAdministration($sheet->id, $slot, now()->subHour());

        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose,
        ]), 'A dose was allowed inside the minimum interval.');
    }

    /**
     * The interval must run from when the dose was GIVEN, not from the last edit.
     * Editing a note used to push the clock forward and lock a resident out.
     */
    public function test_amending_a_note_does_not_move_the_administration_time(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        $slot = $sheet->time_slots[0] ?? '08:00';
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();

        $this->record(['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose]);
        $before = MARAdministration::where('mar_sheet_id', $sheet->id)->firstOrFail()->administered_at;

        $this->service->administer($sheet->id, [
            'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'A', 'notes' => 'edited later',
        ], 101, 427);

        $after = MARAdministration::where('mar_sheet_id', $sheet->id)->firstOrFail()->administered_at;
        $this->assertEquals($before, $after, 'Amending a record moved administered_at — the PRN clock would reset.');
    }

    // ---------------------------------------------------------------- Stock

    public function test_free_text_can_never_drive_the_stock_deduction(): void
    {
        $sheet = MARSheet::forHome(101)->active()->whereNotNull('dose_quantity')
            ->where('as_required', 0)->whereNotNull('stock_level')->firstOrFail();
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();

        $before = (float) $sheet->stock_level;
        $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $sheet->time_slots[0] ?? '08:00', 'code' => 'A',
            'dose_given' => '99999999',   // adversarial: what the old regex would have eaten
        ]);

        $moved = $before - (float) MARSheet::find($sheet->id)->stock_level;
        $this->assertEqualsWithDelta((float) $sheet->dose_quantity, $moved, 0.001,
            'The typed dose text influenced the stock deduction.');
    }

    public function test_a_fractional_dose_deducts_exactly(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('dose_quantity', 7.5)->first();
        if (! $sheet) {
            $this->markTestSkipped('No fractional-dose fixture present.');
        }
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        MARSheet::where('id', $sheet->id)->update(['stock_level' => 60]);

        $this->record(['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $sheet->time_slots[0] ?? '12:00', 'code' => 'A', 'dose_given' => $sheet->dose]);

        $this->assertEqualsWithDelta(52.5, (float) MARSheet::find($sheet->id)->stock_level, 0.001,
            '7.5 ml did not deduct exactly — fractional stock is being rounded.');
    }

    // ---------------------------------------------------------------- Codes

    /** Asleep means the medicine did not go in. It must cost nothing and block nothing. */
    public function test_asleep_is_not_given_and_consumes_no_prn_allowance(): void
    {
        $sheet = $this->prnSheet(243);
        $this->assertNotNull($sheet);
        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        MARSheet::where('id', $sheet->id)->update(['prn_max_daily' => 4, 'prn_min_interval_hours' => 4.0, 'stock_level' => 12]);
        $slot = $sheet->time_slots[0] ?? '12:00';

        for ($i = 0; $i < 4; $i++) {
            $this->record(['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'S']);
        }

        $this->assertEqualsWithDelta(12.0, (float) MARSheet::find($sheet->id)->stock_level, 0.001,
            'Recording "asleep" moved stock — nothing was administered.');
        $this->assertSame(0, MARAdministration::where('mar_sheet_id', $sheet->id)->where('given', 1)->count(),
            '"Asleep" was recorded as given.');
        $this->assertSame('accepted', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot,
            'code' => 'A', 'dose_given' => $sheet->dose,
        ]), 'A resident recorded asleep four times was then refused their medicine — they had received none of it.');
    }

    public function test_a_refusal_requires_a_reason(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $sheet->time_slots[0] ?? '08:00', 'code' => 'R', 'reason' => '',
        ]));
    }

    /** Withholding a prescribed dose is a clinical decision; the record must say why (REQ-MED-06). */
    public function test_a_withheld_dose_requires_a_reason(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        $slot = $sheet->time_slots[0] ?? '08:00';

        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $slot, 'code' => 'W', 'reason' => '',
        ]), 'A dose was withheld with no reason.');

        MARAdministration::where('mar_sheet_id', $sheet->id)->delete();
        $this->assertSame('accepted', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => $slot, 'code' => 'W', 'reason' => 'Withheld — clinical advice',
        ]), 'A withheld dose with a reason should record.');
    }

    // ---------------------------------------------------------------- Controlled drugs

    public function test_controlled_drug_needs_a_witness_in_a_childrens_home(): void
    {
        $cd = MARSheet::forHome(101)->active()->where('is_controlled', 1)->firstOrFail();
        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $cd->id, 'date' => now()->toDateString(),
            'time_slot' => $cd->time_slots[0] ?? '08:00', 'code' => 'A', 'witnessed_by' => '',
        ]));
    }

    public function test_controlled_drug_witness_is_optional_in_supported_living(): void
    {
        $cd = MARSheet::forHome(8)->active()->where('is_controlled', 1)->firstOrFail();
        $this->assertSame('accepted', $this->record([
            'mar_sheet_id' => $cd->id, 'date' => now()->toDateString(),
            'time_slot' => $cd->time_slots[0] ?? '08:00', 'code' => 'A', 'witnessed_by' => '',
            'dose_given' => $cd->dose,
        ], 219));
    }

    /** A home with no care_setting must fail SAFE — witness required, not skipped. */
    public function test_unconfigured_home_still_requires_a_witness(): void
    {
        $controller = new \App\Http\Controllers\frontEnd\Medication\MedicationRoundController();
        $method = new \ReflectionMethod($controller, 'cdWitnessRequired');
        $method->setAccessible(true);

        \App\Home::where('id', 101)->update(['care_setting' => null]);
        $this->assertTrue($method->invoke($controller, 101),
            'A home with no care_setting skipped the controlled-drug witness. NULL must fail safe.');
        $this->assertTrue($method->invoke($controller, 999999),
            'A nonexistent home skipped the controlled-drug witness.');
    }

    // ---------------------------------------------------------------- Silent failure

    /**
     * A write against a prescription that isn't found must FAIL, not quietly succeed.
     * applyRecord used to return false here, which every wrapper turned into a 302 the
     * frontend read as success — the modal cleared as though the dose was saved (CR-07).
     */
    public function test_a_missing_prescription_does_not_report_success(): void
    {
        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => 999999999, 'date' => now()->toDateString(),
            'time_slot' => '08:00', 'code' => 'A', 'dose_given' => '1 tablet',
        ]), 'Recording against a non-existent prescription did not raise an error.');
    }

    // ---------------------------------------------------------------- Append-only records

    /**
     * Correcting a dose must PRESERVE the original, not overwrite it (CQC Reg 17 /
     * Children's Homes Regs reg 23(2)(c)). "Given" changed to "Refused" must leave the
     * original "Given" entry, its author and its time, retrievable for audit.
     */
    public function test_amending_a_dose_preserves_the_original_as_a_superseded_version(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        $slot = $sheet->time_slots[0] ?? '08:00';
        MARAdministration::withHistory()->where('mar_sheet_id', $sheet->id)->delete();

        // Record Given, then correct it to Refused.
        $this->record(['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose]);
        $this->record(['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'R', 'reason' => 'Resident refused']);

        // The default (scoped) view shows exactly one CURRENT record, and it is the correction.
        $current = MARAdministration::where('mar_sheet_id', $sheet->id)->get();
        $this->assertCount(1, $current, 'More than one current version exists for one slot.');
        $this->assertSame('R', $current->first()->code);

        // The full history retains the original, marked superseded and linked from the correction.
        $all = MARAdministration::withHistory()->where('mar_sheet_id', $sheet->id)->get();
        $this->assertCount(2, $all, 'The original was overwritten instead of superseded.');
        $original = $all->firstWhere('code', 'A');
        $this->assertNotNull($original, 'The original "Given" entry was destroyed.');
        $this->assertFalse((bool) $original->is_current, 'The original was not marked superseded.');
        $this->assertNotNull($original->superseded_at);
        $this->assertSame($original->id, (int) $current->first()->supersedes_id,
            'The correction does not point back to the record it replaced.');
    }

    /** An identical re-submit (double-tap) must NOT create a superseding version. */
    public function test_an_unchanged_resubmit_does_not_spam_the_history(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        $slot = $sheet->time_slots[0] ?? '08:00';
        MARAdministration::withHistory()->where('mar_sheet_id', $sheet->id)->delete();

        $args = ['mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(), 'time_slot' => $slot, 'code' => 'A', 'dose_given' => $sheet->dose];
        $this->record($args);
        $this->record($args);
        $this->record($args);

        $this->assertCount(1, MARAdministration::withHistory()->where('mar_sheet_id', $sheet->id)->get(),
            'Identical re-submissions created superseded versions.');
    }

    // ---------------------------------------------------------------- Round lock

    public function test_an_ended_round_cannot_be_recorded_into(): void
    {
        $sheet = MARSheet::forHome(101)->active()->where('as_required', 0)->firstOrFail();
        MedicationRoundClosure::updateOrCreate(
            ['home_id' => 101, 'date' => now()->toDateString(), 'round' => 'morning'],
            ['closed_by' => 427]
        );

        $this->assertSame('blocked', $this->record([
            'mar_sheet_id' => $sheet->id, 'date' => now()->toDateString(),
            'time_slot' => '08:00', 'code' => 'A', 'dose_given' => $sheet->dose,
        ]));
    }

    // ---------------------------------------------------------------- The fixtures themselves

    /**
     * The demo fixtures must actually demonstrate what they claim.
     *
     * For a whole day they did not: administered_at was never written by the seeder, so
     * the interval block quietly did nothing while every report said it worked.
     */
    public function test_seeded_fixtures_carry_the_data_their_own_safety_rules_read(): void
    {
        $nullAt = MARAdministration::whereHas('marSheet', fn ($q) => $q->whereIn('home_id', [101, 8]))
            ->whereNull('administered_at')->count();
        $this->assertSame(0, $nullAt,
            'Seeded administrations have no administered_at — the PRN interval block reads that column '
            .'and would silently do nothing.');

        $nullQty = MARSheet::whereIn('home_id', [101, 8])->where('is_deleted', 0)
            ->whereNull('dose_quantity')->count();
        $this->assertSame(0, $nullQty,
            'Seeded prescriptions have no dose_quantity — their stock would never decrement.');
    }
}
