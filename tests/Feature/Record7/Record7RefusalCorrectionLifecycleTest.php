<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Services\Record7\IssueRegistry;
use Illuminate\Support\Str;

/**
 * Joined-up Section 2.3 / 2.7 refusal lifecycle.
 *
 * A re-offer and a correction are both append-only. The refusal condition must
 * therefore use the effective corrected outcome of the linked re-offer rather
 * than treating the first value ever written on that row as permanent truth.
 */
class Record7RefusalCorrectionLifecycleTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function registry(): IssueRegistry
    {
        return app(IssueRegistry::class);
    }

    private function ordinaryPrescription(): Prescription
    {
        $service = $this->house('Oakwood House');

        $prescription = Prescription::with(['medicine', 'client'])
            ->where('kind', '!=', 'prn')
            ->whereHas('medicine', fn ($query) => $query->where('is_controlled', false))
            ->whereHas('client', fn ($query) => $query->where('service_id', $service->id))
            ->first();

        if ($prescription === null) {
            $this->markTestSkipped('No ordinary scheduled prescription exists in the fixture.');
        }

        return $prescription;
    }

    /** @return array{Administration, ScheduledDose} */
    private function refusal(): array
    {
        $prescription = $this->ordinaryPrescription();
        $service = $this->house('Oakwood House');

        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'due_at' => now()->subHour(),
            'slot' => 'Morning',
            'grace_minutes' => 30,
        ]);

        $refusal = Administration::create([
            'reference' => 'TEST-REF-CORR-'.Str::upper(Str::random(8)),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'administered_at' => now()->subHour(),
        ]);

        return [$refusal, $dose];
    }

    private function reoffer(Administration $refusal, ScheduledDose $dose, string $outcome): Administration
    {
        return Administration::create([
            'reference' => 'TEST-REOFFER-CORR-'.Str::upper(Str::random(8)),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $refusal->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => $outcome,
            'reason_code' => $outcome === 'refused' ? 'client_declined' : null,
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now()->subMinutes(40),
        ]);
    }

    private function correctReoffer(Administration $reoffer, string $outcome): Administration
    {
        return Administration::create([
            'reference' => 'TEST-REOFFER-FIX-'.Str::upper(Str::random(8)),
            'scheduled_dose_id' => $reoffer->scheduled_dose_id,
            'prescription_id' => $reoffer->prescription_id,
            'client_id' => $reoffer->client_id,
            'service_id' => $reoffer->service_id,
            'recorded_by_user_id' => $this->user('daniel.evans')->id,
            'outcome' => $outcome,
            'reason_code' => 'manager_correction',
            'administered_at' => $reoffer->administered_at,
            'corrects_administration_id' => $reoffer->id,
        ]);
    }

    public function test_an_accepted_reoffer_corrected_to_not_taken_reopens_the_refusal_condition(): void
    {
        [$refusal, $dose] = $this->refusal();
        $reoffer = $this->reoffer($refusal, $dose, 'given');

        $this->assertFalse(
            $this->registry()->conditionActive('refusal:'.$refusal->id, $refusal->service_id),
            'An accepted re-offer should resolve the refusal before it is corrected.'
        );

        $this->correctReoffer($reoffer, 'refused');

        $this->assertTrue(
            $this->registry()->conditionActive('refusal:'.$refusal->id, $refusal->service_id),
            'The original given row survived and incorrectly kept the refusal resolved.'
        );
    }

    public function test_a_refused_reoffer_corrected_to_taken_resolves_the_refusal_condition(): void
    {
        [$refusal, $dose] = $this->refusal();
        $reoffer = $this->reoffer($refusal, $dose, 'refused');

        $this->assertTrue(
            $this->registry()->conditionActive('refusal:'.$refusal->id, $refusal->service_id),
            'A second refusal should keep the refusal condition open.'
        );

        $this->correctReoffer($reoffer, 'given');

        $this->assertFalse(
            $this->registry()->conditionActive('refusal:'.$refusal->id, $refusal->service_id),
            'The corrected accepted re-offer did not resolve the refusal condition.'
        );
    }
}
