<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ReviewItem;
use Illuminate\Support\Facades\DB;

/**
 * Regression for the Section 2.3 person_unavailable outcome travelling through
 * the later Section 2.7 correction workflow.
 *
 * The database and Administration model already know this outcome. The defect
 * was that the worker request and manager approval allowlists stopped at
 * `missed`, so a truthful correction could not make it through the workflow.
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

    public function test_person_unavailable_can_be_requested_and_approved_as_a_correction(): void
    {
        $original = $this->correctableNonConsumingAdministration();
        $originalAttributes = $original->getAttributes();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->post('/record7/administration/'.$original->id.'/correction', [
            'requested_outcome' => 'person_unavailable',
            'detail' => 'The person was in hospital at the scheduled time and was not at the service.',
        ])->assertRedirect();

        $item = ReviewItem::where('service_id', $this->house('Rosewood House')->id)
            ->where('kind', 'correction_request')
            ->where('subject_type', 'administration')
            ->where('subject_id', $original->id)
            ->where('status', 'open')
            ->firstOrFail();

        $this->assertSame('person_unavailable', $item->requested_outcome);
        $this->assertSame(
            $originalAttributes,
            $original->fresh()->getAttributes(),
            'Requesting the correction rewrote the original clinical record.'
        );

        $this->signInAt('daniel.evans', 'Rosewood House');

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'corrected_outcome' => 'person_unavailable',
            'note' => 'Confirmed against the hospital attendance information.',
        ])->assertRedirect('/record7/manager');

        $correction = Administration::where('service_id', $original->service_id)
            ->where('corrects_administration_id', $original->id)
            ->firstOrFail();

        $this->assertSame('person_unavailable', $correction->outcome);
        $this->assertSame($original->scheduled_dose_id, $correction->scheduled_dose_id);
        $this->assertSame($original->prescription_id, $correction->prescription_id);
        $this->assertSame($original->client_id, $correction->client_id);
        $this->assertNull($correction->stock_movement_id);

        $this->assertSame(
            $originalAttributes,
            $original->fresh()->getAttributes(),
            'Approving the correction rewrote the original clinical record.'
        );

        $this->assertSame('approved', $item->fresh()->status);
    }
}
