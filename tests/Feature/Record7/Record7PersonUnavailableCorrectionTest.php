<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\ScheduledDose;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\IssueRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Regression for the Section 2.3 person_unavailable correction boundary.
 *
 * A generic correction may correct an erroneous person-unavailable record AWAY
 * to a taken outcome, but it may not CREATE person_unavailable because the
 * generic request does not capture the structured whereabouts reason required
 * by Section 2.3. Legacy/open requests are blocked again at the append boundary.
 */
class Record7PersonUnavailableCorrectionTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'ROSE-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1.2 fixture first.');
        }
    }

    private function correctableNonConsumingAdministration(): Administration
    {
        $record = Administration::where('service_id', $this->house('Rosewood House')->id)
            ->whereIn('outcome', ['refused', 'not_available', 'missed'])
            ->whereNull('stock_movement_id')
            ->whereNull('corrects_administration_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('record7_administrations as fix')
                ->whereColumn('fix.corrects_administration_id', 'record7_administrations.id'))
            ->first();

        if ($record === null) {
            $this->markTestSkipped('No suitable non-consuming administration exists in the fixture.');
        }

        return $record;
    }

    public function test_person_unavailable_cannot_be_requested_as_a_generic_correction_target(): void
    {
        $original = $this->correctableNonConsumingAdministration();
        $before = ReviewItem::where('subject_type', 'administration')
            ->where('subject_id', $original->id)
            ->count();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->post('/record7/administration/'.$original->id.'/correction', [
            'requested_outcome' => 'person_unavailable',
            'detail' => 'The person was in hospital at the scheduled time and was not at the service.',
        ])->assertSessionHasErrors('requested_outcome');

        $this->assertSame(
            $before,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $original->id)
                ->count(),
            'A person-unavailable generic correction request entered the queue.'
        );

        $this->assertNull(
            Administration::where('corrects_administration_id', $original->id)->first(),
            'Refusing the request still wrote a clinical correction.'
        );
    }

    public function test_legacy_person_unavailable_request_cannot_be_approved_without_structured_reason(): void
    {
        $original = $this->correctableNonConsumingAdministration();
        $service = $this->house('Rosewood House');

        $item = ReviewItem::create([
            'reference' => 'TEST-PERSON-UNAVAILABLE-'.Str::upper(Str::random(8)),
            'organisation_id' => $original->client->organisation_id,
            'service_id' => $service->id,
            'kind' => 'correction_request',
            'title' => 'Legacy person-unavailable correction request',
            'detail' => 'Legacy/open request created before the structured non-taken correction boundary.',
            'subject_type' => 'administration',
            'subject_id' => $original->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => 'person_unavailable',
            'raised_by_user_id' => $this->user('olivia.carter')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'open',
        ]);

        $before = Administration::count();

        $this->signInAt('daniel.evans', 'Rosewood House');
        $this->withoutExceptionHandling();

        try {
            $this->post('/record7/manager/decide', [
                'review_id' => $item->id,
                'decision' => 'approved',
                'corrected_outcome' => 'person_unavailable',
                'note' => 'Attempting to approve a legacy person-unavailable correction.',
            ]);

            $this->fail('Person-unavailable correction approval was not refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('structured Section 2.3 correction pathway', $exception->getMessage());
        }

        $this->assertSame($before, Administration::count());
        $this->assertSame('open', $item->fresh()->status, 'The failed approval was not rolled back.');
        $this->assertNull(
            Administration::where('corrects_administration_id', $original->id)->first()
        );
    }

    public function test_correcting_a_not_found_report_away_closes_the_welfare_lifecycle_everywhere(): void
    {
        $service = $this->house('Rosewood House');
        $prescription = Prescription::with(['client', 'medicine'])
            ->where('kind', 'scheduled')
            ->whereHas('client', fn ($query) => $query->where('service_id', $service->id))
            ->whereHas('medicine', fn ($query) => $query->where('is_controlled', false))
            ->firstOrFail();

        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'due_at' => now()->subMinutes(17),
            'slot' => 'WelfareCorrection-'.Str::random(8),
            'grace_minutes' => 60,
        ]);

        $report = Administration::create([
            'reference' => 'TEST-WELFARE-'.Str::upper(Str::random(10)),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
            'notes' => 'Could not find the person in the service at the scheduled time.',
            'administered_at' => now()->subMinutes(12),
        ]);

        $registry = app(IssueRegistry::class);
        $recorder = app(AdministrationRecorder::class);
        $key = 'welfare_check:'.$report->id;

        $this->assertTrue($registry->conditionActive($key, $service->id));
        $this->assertSame($report->id, $recorder->openWelfareConcernFor($service->id, $report->client_id)?->id);

        Administration::create([
            'reference' => 'TEST-WELFARE-FIX-'.Str::upper(Str::random(10)),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $service->id,
            'recorded_by_user_id' => $this->user('daniel.evans')->id,
            'outcome' => 'given',
            'reason_code' => 'manager_correction',
            'notes' => 'Approved correction: the person was present and the medicine was given.',
            'administered_at' => now()->subMinutes(5),
            'corrects_administration_id' => $report->id,
        ]);

        $this->assertFalse(
            $registry->conditionActive($key, $service->id),
            'A corrected-away not-found report must not remain a live welfare condition.'
        );
        $this->assertNull(
            $recorder->openWelfareConcernFor($service->id, $report->client_id),
            'The round screen must not continue offering a welfare action for a corrected-away report.'
        );
    }
}
