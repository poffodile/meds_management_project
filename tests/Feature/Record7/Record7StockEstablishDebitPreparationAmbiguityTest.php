<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Service;
use App\Models\Record7\StockBalance;
use App\Services\Record7\StockLedger;
use App\Services\Record7\StockRefusal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A historical debit must name one provable preparation.
 *
 * The same person and medicine can have distinct balances for different
 * historical strengths/forms/units. Medicine identity alone must never choose
 * whichever balance happens to be returned first.
 */
class Record7StockEstablishDebitPreparationAmbiguityTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    public function test_historical_debit_fails_closed_when_more_than_one_preparation_balance_matches(): void
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

        $otherPreparation = [
            'medicine_id' => $medicine->id,
            'medicine_name_at_time' => $medicine->name,
            'form_at_time' => $medicine->form,
            'strength_at_time' => 'Historical-'.Str::random(12),
            'unit' => $balance->unit,
        ];

        DB::connection('record7')->transaction(function () use (
            $ledger, $client, $house, $otherPreparation
        ) {
            $other = $ledger->lockBalance($client, $house, $otherPreparation);
            $ledger->record(
                balance: $other,
                snapshot: $otherPreparation,
                action: 'opening_balance',
                quantities: ['received' => 3],
                user: $this->user('olivia.carter'),
                client: $client,
                house: $house,
            );
        });

        $original = Administration::create([
            'reference' => 'TEST-HISTORICAL-MISSED-'.Str::upper(Str::random(8)),
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
            'notes' => 'The original record said the dose was missed.',
            'action_taken' => 'Manager was told.',
            'immediate_action_code' => 'manager_notified',
            'stock_no_quantity_removed' => true,
            'administered_at' => now()->subHour(),
        ]);

        $approval = ReviewItem::create([
            'reference' => 'TEST-HISTORICAL-DEBIT-'.Str::upper(Str::random(8)),
            'organisation_id' => $client->organisation_id,
            'service_id' => $house->id,
            'kind' => 'correction_request',
            'title' => 'Historical administration correction',
            'detail' => 'Approved evidence says the dose was given, but preparation identity is ambiguous.',
            'subject_type' => 'administration',
            'subject_id' => $original->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => 'given',
            'requested_dose_amount' => 1,
            'requested_dose_unit' => $balance->unit,
            'raised_by_user_id' => $this->user('olivia.carter')->id,
            'raised_at' => now()->subMinute(),
            'severity' => 'high',
            'status' => 'approved',
            'decided_by_user_id' => $this->user('daniel.evans')->id,
            'decided_at' => now(),
            'decision_note' => 'Approved for regression coverage.',
        ]);

        $this->expectException(StockRefusal::class);
        $this->expectExceptionMessage('more than one preparation');

        DB::connection('record7')->transaction(fn () => $ledger->establishDebit(
            $this->user('daniel.evans'),
            $original,
            1,
            $balance->unit,
            $approval->id
        ));
    }
}
