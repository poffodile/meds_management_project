<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\ManagerBoard;
use Illuminate\Support\Str;

/**
 * Manager Today and IssueRegistry must tell the same refusal story.
 *
 * An unrelated later dose of the same prescription is a different clinical
 * obligation and cannot resolve an earlier refusal. Only an accepted re-offer
 * linked to that refusal and scheduled dose can do that.
 */
class Record7ManagerRefusalConsistencyTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'ROSE-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1.2 fixtures first.');
        }
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    private function prescription(): Prescription
    {
        return Prescription::with('medicine')
            ->where('kind', 'scheduled')
            ->whereHas('client', fn ($query) => $query->where('service_id', $this->rosewood()->id))
            ->whereHas('medicine', fn ($query) => $query->where('is_controlled', false))
            ->firstOrFail();
    }

    private function boardKeys(int $serviceId): array
    {
        return array_column(app(ManagerBoard::class)->attention($serviceId), 'key');
    }

    public function test_manager_today_uses_the_same_same_dose_reoffer_rule_as_issue_registry(): void
    {
        $house = $this->rosewood();
        $prescription = $this->prescription();
        $client = Client::findOrFail($prescription->client_id);
        $slot = 'RefusalConsistency-'.Str::random(10);

        $round = Round::create([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => $slot,
            'started_by_user_id' => $this->user('olivia.carter')->id,
            'started_at' => now()->subMinutes(30),
        ]);

        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'due_at' => now()->subMinutes(25),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);

        $refusal = Administration::create([
            'reference' => 'TEST-REFUSAL-'.Str::random(12),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'refused',
            'reason_code' => 'person_declined',
            'administered_at' => now()->subMinutes(20),
        ]);

        $key = 'refusal:'.$refusal->id;
        $registry = app(IssueRegistry::class);

        $this->assertTrue($registry->conditionActive($key, $house->id));
        $this->assertContains($key, $this->boardKeys($house->id));

        // A different administration of the same prescription is not a re-offer
        // of this dose. It must not make Manager Today disagree with the shared
        // clinical condition registry.
        Administration::create([
            'reference' => 'TEST-UNRELATED-GIVEN-'.Str::random(12),
            'scheduled_dose_id' => null,
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'given',
            'administered_at' => now()->subMinutes(10),
        ]);

        $this->assertTrue(
            $registry->conditionActive($key, $house->id),
            'An unrelated later given must not resolve the earlier refusal.'
        );
        $this->assertContains(
            $key,
            $this->boardKeys($house->id),
            'Manager Today must not hide a refusal that IssueRegistry still considers open.'
        );

        // This is the resolving fact: an accepted re-offer explicitly linked to
        // the same refused scheduled dose.
        Administration::create([
            'reference' => 'TEST-ACCEPTED-REOFFER-'.Str::random(12),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now()->subMinutes(5),
        ]);

        $this->assertFalse($registry->conditionActive($key, $house->id));
        $this->assertNotContains($key, $this->boardKeys($house->id));
    }
}
