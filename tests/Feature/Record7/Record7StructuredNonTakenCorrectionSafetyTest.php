<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ReviewItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Safety boundary for generic corrections that would CREATE a Section 2.3
 * non-taken outcome.
 *
 * Refused, medicine unavailable and missed each require structured clinical
 * evidence that the current generic correction request does not capture. Until
 * that contract exists, free text cannot stand in for those permanent fields.
 */
class Record7StructuredNonTakenCorrectionSafetyTest extends Record7TestCase
{
    private const BLOCKED = ['refused', 'not_available', 'missed'];

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
            ->whereHas('prescription', fn ($query) => $query->where('kind', 'scheduled'))
            ->whereHas('prescription.medicine', fn ($query) => $query->where('is_controlled', false))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('record7_administrations as fix')
                ->whereColumn('fix.corrects_administration_id', 'record7_administrations.id'))
            ->first();

        if ($record === null) {
            $this->markTestSkipped('No ordinary correctable scheduled administration exists in the fixture.');
        }

        return $record;
    }

    public function test_worker_cannot_request_structured_non_taken_outcomes_through_generic_correction(): void
    {
        $record = $this->correctableAdministration();
        $this->signInAt('olivia.carter', 'Rosewood House');

        foreach (self::BLOCKED as $outcome) {
            $before = ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $record->id)
                ->count();

            $this->post('/record7/administration/'.$record->id.'/correction', [
                'requested_outcome' => $outcome,
                'detail' => 'This request deliberately exercises the structured non-taken safety boundary.',
            ])->assertSessionHasErrors('requested_outcome');

            $this->assertSame(
                $before,
                ReviewItem::where('subject_type', 'administration')
                    ->where('subject_id', $record->id)
                    ->count(),
                'A blocked '.$outcome.' correction request entered the review queue.'
            );
        }

        $this->assertNull(Administration::where('corrects_administration_id', $record->id)->first());
    }

    public function test_legacy_structured_non_taken_requests_cannot_append_partial_corrections(): void
    {
        $service = $this->house('Rosewood House');
        $this->signInAt('daniel.evans', 'Rosewood House');
        $this->withoutExceptionHandling();

        foreach (self::BLOCKED as $outcome) {
            $record = $this->correctableAdministration();

            $item = ReviewItem::create([
                'reference' => 'TEST-STRUCTURED-'.Str::upper(Str::random(10)),
                'organisation_id' => $record->client->organisation_id,
                'service_id' => $service->id,
                'kind' => 'correction_request',
                'title' => 'Legacy structured non-taken correction request',
                'detail' => 'Legacy/open request created before the structured non-taken correction boundary.',
                'subject_type' => 'administration',
                'subject_id' => $record->id,
                'correction_shape' => 'administration_outcome',
                'requested_outcome' => $outcome,
                'raised_by_user_id' => $this->user('olivia.carter')->id,
                'raised_at' => now(),
                'severity' => 'high',
                'status' => 'open',
            ]);

            $before = Administration::count();

            try {
                $this->post('/record7/manager/decide', [
                    'review_id' => $item->id,
                    'decision' => 'approved',
                    'corrected_outcome' => $outcome,
                    'note' => 'Attempting a legacy generic '.$outcome.' correction.',
                ]);

                $this->fail('The '.$outcome.' correction approval was not refused.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('structured Section 2.3 correction pathway', $exception->getMessage());
            }

            $this->assertSame($before, Administration::count());
            $this->assertSame('open', $item->fresh()->status, 'The failed approval was not rolled back.');
            $this->assertNull(
                Administration::where('corrects_administration_id', $record->id)->first(),
                'A blocked '.$outcome.' correction was appended.'
            );
        }
    }
}
