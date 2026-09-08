<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Services\Record7\ControlledDrugAdministration;
use App\Services\Record7\ControlledDrugRegister;
use Illuminate\Support\Str;

/**
 * Joined-up Section 2.5 / 2.7 safety boundary.
 *
 * A controlled administration has two permanent records: the clinical
 * administration and the controlled-drug register movement. The generic
 * correction workflow knows only ordinary stock, so it must not change one
 * side while leaving the controlled-drug register untouched.
 */
class Record7ControlledDrugCorrectionSafetyTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function cd(): ControlledDrugAdministration
    {
        return app(ControlledDrugAdministration::class);
    }

    private function registry(): ControlledDrugRegister
    {
        return app(ControlledDrugRegister::class);
    }

    private function controlledAdministration(): Administration
    {
        $house = $this->house('Oakwood House');
        $like = Prescription::with('medicine')
            ->whereHas('medicine', fn ($query) => $query->where('name', 'Morphine sulfate MR'))
            ->first();

        if ($like === null) {
            $this->markTestSkipped('No scheduled controlled prescription exists in the fixture.');
        }

        $prescription = Prescription::create([
            'reference' => 'TEST-CD-CORR-'.Str::upper(Str::random(8)),
            'client_id' => $like->client_id,
            'medicine_id' => $like->medicine_id,
            'dose' => $like->dose,
            'route' => $like->route,
            'support_type' => $like->support_type,
            'frequency_text' => $like->frequency_text,
            'kind' => $like->kind,
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
            'dose_min' => $like->dose_min,
            'dose_max' => $like->dose_max,
            'dose_unit' => $like->dose_unit,
        ])->fresh('medicine');

        $person = Client::findOrFail($prescription->client_id);
        $noah = $this->user('noah.williams');

        $this->cd()->receive(
            $noah,
            $house,
            $person,
            $prescription,
            5.0,
            null,
            null,
            request()
        );

        $result = $this->cd()->give(
            $noah,
            $house,
            $person,
            $prescription,
            null,
            1.0,
            null,
            null,
            'Correction-boundary test dose.',
            request()
        );

        return $result['administration']->fresh(['client', 'prescription.medicine']);
    }

    public function test_a_worker_cannot_raise_a_generic_correction_for_a_controlled_drug(): void
    {
        $this->signInAt('noah.williams', 'Oakwood House');
        $record = $this->controlledAdministration();

        $before = ReviewItem::where('subject_type', 'administration')
            ->where('subject_id', $record->id)
            ->count();

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'refused',
            'detail' => 'This controlled-drug administration needs a correction review.',
        ])->assertSessionHas('r7.error');

        $this->assertSame(
            $before,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $record->id)
                ->count(),
            'A generic correction request was created for a controlled drug.'
        );

        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'Refusing the request still appended a controlled-drug correction.'
        );
    }

    public function test_a_legacy_controlled_drug_correction_rolls_back_without_moving_the_register(): void
    {
        $this->signInAt('noah.williams', 'Oakwood House');
        $record = $this->controlledAdministration();
        $service = $this->house('Oakwood House');
        $entry = CdRegister::findOrFail($record->cd_register_id);
        $snapshot = $this->registry()->snapshot($record->prescription->medicine, $entry->unit);
        $balanceBefore = $this->registry()->balanceFor($record->client, $snapshot)->current_balance;
        $registerCountBefore = CdRegister::where('prescription_id', $record->prescription_id)->count();

        $item = ReviewItem::create([
            'reference' => 'TEST-CD-REVIEW-'.Str::upper(Str::random(8)),
            'organisation_id' => $record->client->organisation_id,
            'service_id' => $service->id,
            'kind' => 'correction_request',
            'title' => 'Legacy controlled-drug correction request',
            'detail' => 'Created to prove generic approval cannot split the MAR from the CD register.',
            'subject_type' => 'administration',
            'subject_id' => $record->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => 'refused',
            'raised_by_user_id' => $this->user('noah.williams')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $administrationsBefore = Administration::count();

        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->withoutExceptionHandling();

        try {
            $this->post('/record7/manager/decide', [
                'review_id' => $item->id,
                'decision' => 'approved',
                'corrected_outcome' => 'refused',
                'note' => 'Attempting to approve a legacy generic CD correction.',
            ]);

            $this->fail('A generic controlled-drug correction approval was not refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('controlled-drug correction pathway', $exception->getMessage());
        }

        $this->assertSame($administrationsBefore, Administration::count());
        $this->assertSame('open', $item->fresh()->status, 'The failed approval was not rolled back.');
        $this->assertSame(
            $registerCountBefore,
            CdRegister::where('prescription_id', $record->prescription_id)->count(),
            'The failed correction wrote a controlled-drug register entry.'
        );
        $this->assertSame(
            $balanceBefore,
            $this->registry()->balanceFor($record->client, $snapshot)->fresh()->current_balance,
            'The failed correction changed the controlled-drug balance.'
        );
        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'A controlled-drug correction was appended despite the safety boundary.'
        );
    }
}
