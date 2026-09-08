<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use Illuminate\Support\Str;

/**
 * Joined-up Section 2.4 / 2.7 safety boundary.
 *
 * A generic administration correction can replace an outcome, but a PRN dose
 * also carries the actual amount taken, spends interval/count/amount allowance,
 * may move stock, and may create an effectiveness follow-up. Until those
 * consequences travel together through a PRN-specific correction contract,
 * the generic correction workflow must refuse PRN records entirely.
 */
class Record7PrnCorrectionSafetyTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function prnAdministration(): Administration
    {
        $service = $this->house('Oakwood House');

        $prescription = Prescription::with(['medicine', 'client'])
            ->where('kind', 'prn')
            ->whereHas('client', fn ($query) => $query->where('service_id', $service->id))
            ->first();

        if ($prescription === null) {
            $this->markTestSkipped('No PRN prescription exists in the Oakwood fixture.');
        }

        return Administration::create([
            'reference' => 'TEST-PRN-CORR-'.Str::upper(Str::random(8)),
            'scheduled_dose_id' => null,
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'reason_code' => 'requested_by_person',
            'dose_amount' => (float) ($prescription->dose_min ?? $prescription->dose_max ?? 1),
            'dose_unit' => $prescription->dose_unit,
            'administered_at' => now()->subHour(),
        ]);
    }

    public function test_a_worker_cannot_raise_a_generic_correction_for_a_prn_record(): void
    {
        $record = $this->prnAdministration();

        $this->signInAt('noah.williams', 'Oakwood House');

        $before = ReviewItem::where('subject_type', 'administration')
            ->where('subject_id', $record->id)
            ->count();

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'refused',
            'detail' => 'This PRN administration was recorded in error and needs review.',
        ])->assertSessionHas('r7.error');

        $this->assertSame(
            $before,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $record->id)
                ->count(),
            'A generic correction request was created for a PRN administration.'
        );

        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'Refusing the request still appended a PRN correction.'
        );
    }

    public function test_a_legacy_prn_correction_request_rolls_back_on_manager_approval(): void
    {
        $record = $this->prnAdministration();
        $service = $this->house('Oakwood House');

        $item = ReviewItem::create([
            'reference' => 'TEST-PRN-REVIEW-'.Str::upper(Str::random(8)),
            'organisation_id' => $record->client->organisation_id,
            'service_id' => $service->id,
            'kind' => 'correction_request',
            'title' => 'Legacy PRN correction request',
            'detail' => 'Created to prove an older generic PRN request cannot be approved.',
            'subject_type' => 'administration',
            'subject_id' => $record->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => 'refused',
            'raised_by_user_id' => $this->user('noah.williams')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $before = Administration::count();

        $this->signInAt('daniel.evans', 'Oakwood House');
        $this->withoutExceptionHandling();

        try {
            $this->post('/record7/manager/decide', [
                'review_id' => $item->id,
                'decision' => 'approved',
                'corrected_outcome' => 'refused',
                'note' => 'Attempting to approve a legacy generic PRN correction.',
            ]);

            $this->fail('A generic PRN correction approval was not refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('PRN-specific correction pathway', $exception->getMessage());
        }

        $this->assertSame(
            $before,
            Administration::count(),
            'The failed PRN approval still appended a clinical correction.'
        );
        $this->assertSame('open', $item->fresh()->status, 'The failed approval was not rolled back.');
        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'A PRN correction was appended despite the model safety boundary.'
        );
    }
}
