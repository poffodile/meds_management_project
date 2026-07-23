<?php

namespace Tests\Feature;

use App\Models\ControlledDrugRegister;
use App\Models\MARAdministration;
use App\Models\MARSheet;
use App\Services\Staff\MARSheetService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The witnessed controlled-drug register: an append-only running-balance ledger.
 *
 * The register is a Misuse of Drugs Regs 2001 reg 19 duty for care homes; witnessing at
 * administration is CQC/NICE good practice (STANDARDS-REGISTER STD-07). The property that
 * matters is that the balance is a server-computed chain that cannot be fudged, and that
 * entries are appended, never edited. DatabaseTransactions (never RefreshDatabase).
 */
class ControlledDrugRegisterTest extends TestCase
{
    use DatabaseTransactions;

    private function cdSheet(): MARSheet
    {
        $s = MARSheet::forHome(101)->active()->where('is_controlled', 1)->whereNotNull('dose_quantity')->first();
        if (! $s) {
            $this->markTestSkipped('No controlled drug with a structured dose in the demo data.');
        }
        ControlledDrugRegister::forHome(101)->where('mar_sheet_id', $s->id)->delete();

        return $s;
    }

    public function test_opening_balance_is_the_recorded_stock(): void
    {
        $s = $this->cdSheet();
        $entry = ControlledDrugRegister::record($s, 'administered', 1, 427, 'Nurse Smith');

        $this->assertEqualsWithDelta((float) $s->stock_level, (float) $entry->balance_before, 0.001,
            'The first register entry did not open from the recorded stock.');
        $this->assertEqualsWithDelta((float) $s->stock_level - 1, (float) $entry->balance_after, 0.001);
    }

    public function test_the_running_balance_chains_across_movements(): void
    {
        $s = $this->cdSheet();
        $open = (float) $s->stock_level;

        ControlledDrugRegister::record($s, 'administered', 1, 427, 'W1');   // open-1
        ControlledDrugRegister::record($s, 'received', 10, 427, 'W2');      // open-1+10
        $last = ControlledDrugRegister::record($s, 'administered', 2, 427, 'W3'); // open+7

        $this->assertEqualsWithDelta($open + 7, (float) $last->balance_after, 0.001,
            'The running balance did not chain correctly across movements.');

        // Each entry's before must equal the previous entry's after — an unbroken chain.
        $rows = ControlledDrugRegister::forHome(101)->where('mar_sheet_id', $s->id)->orderBy('id')->get();
        for ($i = 1; $i < $rows->count(); $i++) {
            $this->assertEqualsWithDelta((float) $rows[$i - 1]->balance_after, (float) $rows[$i]->balance_before, 0.001,
                "The balance chain is broken between entry {$i} and the one before it.");
        }
    }

    public function test_an_adjustment_sets_the_balance_absolutely(): void
    {
        $s = $this->cdSheet();
        ControlledDrugRegister::record($s, 'administered', 5, 427, 'W1');
        $adj = ControlledDrugRegister::record($s, 'adjustment', 20, 427, 'W2');

        $this->assertEqualsWithDelta(20, (float) $adj->balance_after, 0.001,
            'A recount did not set the balance to the counted figure.');
    }

    public function test_administering_a_cd_on_the_round_writes_a_witnessed_entry(): void
    {
        $s = $this->cdSheet();
        MARAdministration::withHistory()->where('mar_sheet_id', $s->id)->delete();

        $this->actingAs(User::find(427));
        $controller = new \App\Http\Controllers\frontEnd\Medication\Medication2RoundController();
        $apply = new \ReflectionMethod($controller, 'applyRecord');
        $apply->setAccessible(true);
        $apply->invoke($controller, Request::create('/x', 'POST', [
            'mar_sheet_id' => $s->id, 'date' => now()->toDateString(),
            'time_slot' => $s->time_slots[0] ?? '08:00', 'code' => 'A',
            'dose_given' => $s->dose, 'witnessed_by' => 'Nurse Jones',
        ]), app(MARSheetService::class));

        $entry = ControlledDrugRegister::forHome(101)->where('mar_sheet_id', $s->id)->first();
        $this->assertNotNull($entry, 'Administering a CD on the round did not create a register entry.');
        $this->assertSame('administered', $entry->action_type);
        $this->assertSame('Nurse Jones', $entry->witness_name, 'The witness was not carried into the register.');
    }

    public function test_an_unwitnessed_movement_is_recorded_as_such_not_left_blank(): void
    {
        $s = $this->cdSheet();
        $entry = ControlledDrugRegister::record($s, 'administered', 1, 427, null);
        $this->assertNotEmpty($entry->witness_name, 'witness_name is a required legal field and must never be blank.');
        $this->assertSame('Not witnessed', $entry->witness_name);
    }
}
