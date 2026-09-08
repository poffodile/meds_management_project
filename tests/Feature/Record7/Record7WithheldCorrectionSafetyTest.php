<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ReviewItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Safety boundary for withheld corrections.
 *
 * Record7 does not yet hold the structured instruction/authority/evidence that
 * would justify recording a medicine as withheld. Until that model exists, a
 * correction must not become a back door for asserting `withheld`.
 */
class Record7WithheldCorrectionSafetyTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'ROSE-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1.2 fixture first.');
        }
    }

    private function correctableAdministration(): Administration
    {
        $record = Administration::where('service_id', $this->house('Rosewood House')->id)
            ->whereNull('corrects_administration_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('record7_administrations as fix')
                ->whereColumn('fix.corrects_administration_id', 'record7_administrations.id'))
            ->first();

        if ($record === null) {
            $this->markTestSkipped('No correctable administration exists in the fixture.');
        }

        return $record;
    }

    public function test_a_worker_cannot_request_withheld_as_a_correction_target(): void
    {
        $record = $this->correctableAdministration();
        $before = ReviewItem::where('subject_type', 'administration')
            ->where('subject_id', $record->id)
            ->count();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'withheld',
            'detail' => 'The medicine should have been recorded as withheld.',
        ])->assertSessionHasErrors('requested_outcome');

        $this->assertSame(
            $before,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $record->id)
                ->count(),
            'A withheld correction request entered the review queue.'
        );

        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'Refusing the request still wrote a clinical correction.'
        );
    }

    public function test_a_manager_cannot_approve_a_withheld_correction_request(): void
    {
        $record = $this->correctableAdministration();
        $service = $this->house('Rosewood House');

        $item = ReviewItem::create([
            'reference' => 'TEST-WITHHELD-'.Str::upper(Str::random(8)),
            'organisation_id' => $record->client->organisation_id,
            'service_id' => $service->id,
            'kind' => 'correction_request',
            'title' => 'Legacy withheld correction request',
            'detail' => 'Legacy/open request created before the withheld safety boundary.',
            'subject_type' => 'administration',
            'subject_id' => $record->id,
            'correction_shape' => 'administration_outcome',
            'requested_outcome' => 'withheld',
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
                'corrected_outcome' => 'withheld',
                'note' => 'Attempting to approve a legacy withheld correction.',
            ]);

            $this->fail('Withheld correction approval was not refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('nothing to approve', $exception->getMessage());
        }

        $this->assertSame(
            $before,
            Administration::count(),
            'Refusing a withheld correction still appended a clinical record.'
        );
        $this->assertSame('open', $item->fresh()->status, 'The failed approval was not rolled back.');
        $this->assertNull(
            Administration::where('corrects_administration_id', $record->id)->first(),
            'A withheld correction was appended despite the safety boundary.'
        );
    }
}
