<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\Service;
use App\Models\Record7\StockBalance;
use App\Models\Record7\StockMovement;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A retrospective correction to "given" can establish that counted stock is
 * wrong without proving how much moved. Section 2.7 then requires a physical
 * count rather than inventing a debit.
 *
 * The medicine id alone is not a preparation identity. The same person can
 * legitimately have separate balances for different historical strengths,
 * forms or units. Counting whichever balance happens to be returned first must
 * therefore never make the verification requirement disappear.
 */
class Record7StockVerificationPreparationAmbiguityTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    public function test_counting_one_of_multiple_preparation_balances_does_not_clear_verification_due(): void
    {
        $balance = StockBalance::with(['client', 'medicine'])
            ->where('owner_type', 'client')
            ->whereNotNull('client_id')
            ->whereNotNull('last_movement_id')
            ->firstOrFail();

        $client = $balance->client;
        $house = Service::findOrFail($balance->service_id);
        $medicine = $balance->medicine;
        $prescription = Prescription::where('client_id', $client->id)
            ->where('medicine_id', $medicine->id)
            ->firstOrFail();
        $ledger = app(StockLedger::class);

        // Create a second, genuinely distinct historical preparation balance
        // for the same person and medicine. The ledger's own preparation key
        // keeps it separate from the current balance.
        $historicalSnapshot = [
            'medicine_id' => $medicine->id,
            'medicine_name_at_time' => $medicine->name,
            'form_at_time' => $medicine->form,
            'strength_at_time' => 'Historical-'.Str::random(12),
            'unit' => $balance->unit,
        ];

        DB::connection('record7')->transaction(function () use (
            $ledger, $client, $house, $historicalSnapshot
        ) {
            $historical = $ledger->lockBalance($client, $house, $historicalSnapshot);

            $ledger->record(
                balance: $historical,
                snapshot: $historicalSnapshot,
                action: 'opening_balance',
                quantities: ['received' => 3],
                user: $this->user('olivia.carter'),
                client: $client,
                house: $house,
            );
        });

        $original = Administration::create([
            'reference' => 'TEST-STOCK-UNKNOWN-'.Str::upper(Str::random(10)),
            'scheduled_dose_id' => null,
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
            'notes' => 'The original record said the dose was missed.',
            'action_taken' => 'Manager was told when the recording error was found.',
            'immediate_action_code' => 'manager_notified',
            'stock_no_quantity_removed' => true,
            'administered_at' => now()->subHour(),
        ]);

        $correction = Administration::create([
            'reference' => 'TEST-STOCK-UNKNOWN-FIX-'.Str::upper(Str::random(10)),
            'scheduled_dose_id' => null,
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('daniel.evans')->id,
            'outcome' => 'given',
            'reason_code' => 'manager_correction',
            'notes' => 'Approved correction established that the dose was given; quantity remains unknown.',
            'administered_at' => $original->administered_at,
            'corrects_administration_id' => $original->id,
        ]);

        $key = 'stock_verification_due:'.$correction->id;
        $registry = app(IssueRegistry::class);

        $this->assertTrue(
            $registry->conditionActive($key, $house->id),
            'Unknown historical stock consumption must require physical verification.'
        );

        // Count only the older/current balance returned first by the present
        // medicine-level lookup. A second preparation for the same medicine is
        // still unverified, so this one count cannot prove the stock position.
        $source = StockMovement::findOrFail($balance->last_movement_id);
        $snapshot = [
            'medicine_id' => $source->medicine_id,
            'medicine_name_at_time' => $source->medicine_name_at_time,
            'form_at_time' => $source->form_at_time,
            'strength_at_time' => $source->strength_at_time,
            'unit' => $source->unit,
        ];

        DB::connection('record7')->transaction(function () use (
            $ledger, $balance, $snapshot, $client, $house
        ) {
            $locked = $ledger->lockExisting($balance->fresh());

            $ledger->record(
                balance: $locked,
                snapshot: $snapshot,
                action: 'stock_check',
                quantities: ['counted' => (float) $locked->current_balance],
                user: $this->user('olivia.carter'),
                client: $client,
                house: $house,
            );
        });

        $this->assertTrue(
            $registry->conditionActive($key, $house->id),
            'A count of one candidate preparation must not clear an ambiguity involving another preparation.'
        );
    }
}
