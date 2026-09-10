<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\RoundLifecycleEvent;
use App\Models\Record7\ScheduledDose;
use App\Services\Record7\RoundLifecycle;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Joined-up regression for Sections 2.6 and 2.7.
 *
 * Closing a round and writing a scheduled clinical answer share the round row
 * as their serialization point. Retrospective corrections are deliberately
 * different: they may be appended after close, because they describe what we
 * now know about an event that was already present when the manager signed.
 */
class Record7RoundCorrectionBoundaryTest extends Record7TestCase
{
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function lifecycle(): RoundLifecycle
    {
        return app(RoundLifecycle::class);
    }

    private function manager()
    {
        return $this->user('daniel.evans');
    }

    private function worker()
    {
        return $this->user('noah.williams');
    }

    private function scheduledPrescription(): Prescription
    {
        $prescription = Prescription::with('medicine')
            ->whereHas('client', fn ($q) => $q->where('service_id', $this->house('Oakwood House')->id))
            ->where('kind', 'scheduled')
            ->whereHas('medicine', fn ($q) => $q->where('is_controlled', false))
            ->firstOrFail();

        return $prescription;
    }

    private function freshRoundAndDose(): array
    {
        $house = $this->house('Oakwood House');
        $prescription = $this->scheduledPrescription();
        $slot = 'Boundary-'.Str::random(12);

        $round = Round::create([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => $slot,
            'started_by_user_id' => $this->worker()->id,
            'started_at' => now()->subHour(),
        ]);

        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $house->id,
            'due_at' => now()->setTime(9, 0),
            'slot' => $slot,
            'grace_minutes' => 60,
        ]);

        return [$round, $dose, $prescription];
    }

    private function appendAnswer(ScheduledDose $dose, Prescription $prescription, string $outcome = 'given'): Administration
    {
        return Administration::create([
            'reference' => 'TEST-2-6-2-7-'.Str::random(12),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'recorded_by_user_id' => $this->worker()->id,
            'outcome' => $outcome,
            'reason_code' => in_array($outcome, ['given', 'self_administered'], true)
                ? null : 'client_declined',
            'administered_at' => now(),
        ]);
    }

    public function test_a_closed_round_refuses_a_new_scheduled_clinical_record(): void
    {
        [$round, $dose, $prescription] = $this->freshRoundAndDose();

        $this->lifecycle()->close($this->manager(), $round, request());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reopen it before recording another scheduled outcome');

        $this->appendAnswer($dose, $prescription);
    }

    public function test_an_approved_reopen_makes_the_scheduled_round_writable_again(): void
    {
        [$round, $dose, $prescription] = $this->freshRoundAndDose();

        $this->lifecycle()->close($this->manager(), $round, request());

        $approval = ReviewItem::create([
            'reference' => 'TEST-2-6-RO-'.Str::random(12),
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'kind' => 'round_reopen_request',
            'title' => 'Reopen for a missing scheduled record',
            'detail' => 'The clinical outcome needs to be recorded after the round was signed off.',
            'subject_type' => 'round',
            'subject_id' => $round->id,
            'raised_by_user_id' => $this->worker()->id,
            'raised_at' => now(),
            'severity' => 'medium',
            'status' => 'approved',
            'decided_by_user_id' => $this->manager()->id,
            'decided_at' => now(),
        ]);

        $this->lifecycle()->reopen(
            $this->manager(),
            $round->fresh(),
            $approval,
            'A scheduled outcome was missing when the round was closed.',
            request()
        );

        $answer = $this->appendAnswer($dose, $prescription);

        $this->assertSame($dose->id, $answer->scheduled_dose_id);
        $this->assertFalse($round->fresh()->isClosed());
        $this->assertSame(
            ['closed', 'reopened'],
            RoundLifecycleEvent::where('round_id', $round->id)
                ->orderBy('sequence_no')->pluck('event')->all()
        );
    }

    public function test_a_retrospective_correction_after_close_does_not_rewrite_round_history(): void
    {
        [$round, $dose, $prescription] = $this->freshRoundAndDose();
        $original = $this->appendAnswer($dose, $prescription, 'refused');

        $closed = $this->lifecycle()->close($this->manager(), $round, request());
        $snapshot = [
            'planned' => $closed->planned_doses,
            'accounted' => $closed->accounted_doses,
            'unrecorded' => $closed->unrecorded_doses,
            'unresolved' => $closed->unresolved_categories,
            'occurred_at' => $closed->occurred_at?->toISOString(),
        ];

        $correction = Administration::create([
            'reference' => 'TEST-COR-'.Str::random(12),
            'scheduled_dose_id' => $original->scheduled_dose_id,
            'prescription_id' => $original->prescription_id,
            'client_id' => $original->client_id,
            'service_id' => $original->service_id,
            'recorded_by_user_id' => $this->manager()->id,
            'outcome' => 'missed',
            'reason_code' => 'manager_correction',
            'notes' => 'Retrospective correction after round close.',
            'administered_at' => $original->administered_at,
            'corrects_administration_id' => $original->id,
        ]);

        $after = $closed->fresh();

        $this->assertSame($original->id, $correction->corrects_administration_id);
        $this->assertTrue($round->fresh()->isClosed(), 'A correction is not a reopen.');
        $this->assertSame(1, RoundLifecycleEvent::where('round_id', $round->id)->count());
        $this->assertSame($snapshot['planned'], $after->planned_doses);
        $this->assertSame($snapshot['accounted'], $after->accounted_doses);
        $this->assertSame($snapshot['unrecorded'], $after->unrecorded_doses);
        $this->assertSame($snapshot['unresolved'], $after->unresolved_categories);
        $this->assertSame($snapshot['occurred_at'], $after->occurred_at?->toISOString());
    }
}
