<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\ManagerBoard;
use App\Services\Record7\ShiftBoard;
use Illuminate\Support\Str;

/**
 * Joined-up read-side regression for Sections 2.3 and 2.7.
 *
 * Append-only history means the newest database row is not automatically the
 * current clinical answer. Readers have to distinguish a new clinical event
 * (for example a re-offer) from a correction to an older event.
 */
class Record7EffectiveAdministrationReadTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function ordinaryScheduledPrescription(): Prescription
    {
        return Prescription::with('medicine')
            ->where('kind', 'scheduled')
            ->whereHas('client', fn ($query) => $query->where('service_id', $this->oakwood()->id))
            ->whereHas('medicine', fn ($query) => $query->where('is_controlled', false))
            ->firstOrFail();
    }

    private function dose(Prescription $prescription, string $slot): ScheduledDose
    {
        return ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $this->oakwood()->id,
            'due_at' => now()->subMinutes(30),
            'slot' => $slot,
            'grace_minutes' => 60,
        ]);
    }

    private function administration(
        ScheduledDose $dose,
        string $outcome,
        ?int $corrects = null,
        ?int $reofferOf = null
    ): Administration {
        return Administration::create([
            'reference' => 'TEST-EFFECTIVE-'.strtoupper(Str::random(12)),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => $outcome,
            'reason_code' => $outcome === 'refused' ? 'client_declined' : null,
            'administered_at' => now()->subMinutes(20),
            'corrects_administration_id' => $corrects,
            'reoffer_of_administration_id' => $reofferOf,
        ]);
    }

    public function test_today_counts_the_effective_corrected_outcome_without_counting_a_correction_as_an_extra_event(): void
    {
        $prescription = $this->ordinaryScheduledPrescription();
        $dose = $this->dose($prescription, 'EffectiveRead-'.Str::random(10));
        $refusal = $this->administration($dose, 'refused');

        $board = app(ShiftBoard::class);
        $beforeOverview = $board->overview($dose->service_id);
        $beforeRecent = $board->recentlyCompleted($dose->service_id);

        $this->administration($dose, 'given', $refusal->id);

        $afterOverview = $board->overview($dose->service_id);
        $afterRecent = $board->recentlyCompleted($dose->service_id);

        $this->assertSame(
            $beforeOverview['notTaken'] - 1,
            $afterOverview['notTaken'],
            'A refusal corrected to given must no longer count as not taken.'
        );

        $this->assertSame(
            $beforeRecent['count'],
            $afterRecent['count'],
            'Appending a correction must not look like another medication event.'
        );

        $entry = collect($afterRecent['entries'])->firstWhere('id', $refusal->id);
        $this->assertNotNull($entry);
        $this->assertSame('given', $entry['outcome']);
        $this->assertTrue($entry['corrected']);
    }

    public function test_a_later_correction_to_an_older_refusal_does_not_jump_ahead_of_a_newer_reoffer(): void
    {
        $prescription = $this->ordinaryScheduledPrescription();
        $dose = $this->dose($prescription, 'EffectiveChain-'.Str::random(10));

        $firstRefusal = $this->administration($dose, 'refused');
        $acceptedReoffer = $this->administration($dose, 'given', null, $firstRefusal->id);

        // Written later, but it corrects an older clinical event. It must not
        // become the effective state of the whole dose merely because its id is
        // now the highest row id for this scheduled dose.
        $this->administration($dose, 'missed', $firstRefusal->id);

        $answer = $dose->fresh()->effectiveAdministration();

        $this->assertNotNull($answer);
        $this->assertSame($acceptedReoffer->id, $answer->id);
        $this->assertSame('given', $answer->outcome);
    }

    public function test_a_refusal_corrected_to_given_is_not_still_offered_for_reoffer(): void
    {
        $prescription = $this->ordinaryScheduledPrescription();
        $dose = $this->dose($prescription, 'CorrectedRefusal-'.Str::random(10));
        $refusal = $this->administration($dose, 'refused');

        $this->assertSame(
            $refusal->id,
            app(AdministrationRecorder::class)->openRefusalFor($dose)?->id,
            'The uncorrected refusal is the live re-offer target.'
        );

        $this->administration($dose, 'given', $refusal->id);

        $this->assertNull(
            app(AdministrationRecorder::class)->openRefusalFor($dose->fresh()),
            'A refusal corrected away remains in history but must no longer be actionable as a re-offer.'
        );
    }

    public function test_manager_not_taken_list_uses_effective_outcome_and_does_not_count_a_correction_twice(): void
    {
        $prescription = $this->ordinaryScheduledPrescription();
        $dose = $this->dose($prescription, 'ManagerEffective-'.Str::random(10));
        $notAvailable = $this->administration($dose, 'not_available');

        $before = collect(app(ManagerBoard::class)->outstandingOutcomes($dose->service_id)['notTaken']);
        $this->assertTrue($before->contains(fn ($row) => $row['id'] === $notAvailable->id));

        $this->administration($dose, 'given', $notAvailable->id);

        $after = collect(app(ManagerBoard::class)->outstandingOutcomes($dose->service_id)['notTaken']);

        $this->assertFalse(
            $after->contains(fn ($row) => $row['id'] === $notAvailable->id),
            'A medicine-unavailable event corrected to given must leave the manager not-taken list.'
        );

        $this->assertFalse(
            $after->contains(fn ($row) => $row['id'] !== $notAvailable->id
                && ($row['client'] ?? null) === $notAvailable->client->displayName()
                && ($row['at'] ?? null) === $notAvailable->administered_at->format('H:i')),
            'The correction row must not appear as a second not-taken medication event.'
        );
    }
}
