<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Services\Record7\ControlledDrugAdministration;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Joined-up regression for Sections 2.5 and 2.6.
 *
 * The controlled-drug workspace is person-scoped so PRN medicines can be used
 * outside a round. A scheduled controlled medicine is different: its clinical
 * record must still answer the exact planned dose in the current round, even if
 * the browser handoff did not carry a scheduled_dose_id.
 */
class Record7ScheduledControlledDrugRoundBoundaryTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function house(): Service
    {
        return $this->house('Oakwood House');
    }

    private function source(): Prescription
    {
        return Prescription::with('medicine')
            ->where('kind', 'scheduled')
            ->whereHas('client', fn ($q) => $q->where('service_id', $this->house()->id))
            ->whereHas('medicine', fn ($q) => $q->where('is_controlled', true))
            ->firstOrFail();
    }

    private function copyPrescription(): Prescription
    {
        $source = $this->source();

        return Prescription::create([
            'reference' => 'TEST-CD-ROUND-'.Str::random(12),
            'client_id' => $source->client_id,
            'medicine_id' => $source->medicine_id,
            'dose' => $source->dose,
            'route' => $source->route,
            'support_type' => $source->support_type,
            'frequency_text' => $source->frequency_text,
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
            'dose_min' => $source->dose_min,
            'dose_max' => $source->dose_max,
            'dose_unit' => $source->dose_unit,
        ])->fresh('medicine');
    }

    private function roundFor(Prescription $prescription): Round
    {
        $house = $this->house();

        return Round::create([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => 'CdAnchor-'.Str::random(12),
            'started_by_user_id' => $this->user('noah.williams')->id,
            // Make this the current open round rather than an older fixture row.
            'started_at' => now(),
        ]);
    }

    private function dose(Round $round, Prescription $prescription, int $minute = 0): ScheduledDose
    {
        return ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $round->service_id,
            'due_at' => now()->setTime(9, $minute),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);
    }

    private function witnessId(Service $house): ?int
    {
        return $house->controlledDrugWitnessRequired()
            ? $this->user('olivia.carter')->id
            : null;
    }

    private function seedStock(Prescription $prescription, Client $person): void
    {
        $house = $this->house();

        app(ControlledDrugAdministration::class)->receive(
            $this->user('noah.williams'),
            $house,
            $person,
            $prescription,
            10,
            $this->witnessId($house),
            'Test opening stock.',
            request()
        );
    }

    public function test_a_scheduled_cd_without_a_posted_dose_id_is_anchored_to_the_open_round_dose(): void
    {
        $prescription = $this->copyPrescription();
        $person = Client::findOrFail($prescription->client_id);
        $round = $this->roundFor($prescription);
        $dose = $this->dose($round, $prescription);
        $this->seedStock($prescription, $person);

        $result = app(ControlledDrugAdministration::class)->give(
            $this->user('noah.williams'),
            $this->house(),
            $person,
            $prescription,
            null,
            (float) ($prescription->dose_min ?? 1),
            $this->witnessId($this->house()),
            null,
            'Scheduled controlled medicine.',
            request()
        );

        $this->assertSame($dose->id, $result['administration']->scheduled_dose_id);
        $this->assertTrue($dose->fresh()->isRecorded());
        $this->assertSame(1, Administration::where('scheduled_dose_id', $dose->id)->count());
    }

    public function test_a_scheduled_cd_is_not_guessed_when_two_round_doses_match(): void
    {
        $prescription = $this->copyPrescription();
        $person = Client::findOrFail($prescription->client_id);
        $round = $this->roundFor($prescription);
        $this->dose($round, $prescription, 0);
        $this->dose($round, $prescription, 1);
        $this->seedStock($prescription, $person);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot identify one scheduled dose');

        app(ControlledDrugAdministration::class)->give(
            $this->user('noah.williams'),
            $this->house(),
            $person,
            $prescription,
            null,
            (float) ($prescription->dose_min ?? 1),
            $this->witnessId($this->house()),
            null,
            'Ambiguous scheduled controlled medicine.',
            request()
        );
    }
}
