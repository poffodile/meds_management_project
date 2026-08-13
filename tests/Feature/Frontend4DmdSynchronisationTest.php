<?php

namespace Tests\Feature;

use App\Models\Frontend4TerminologyImport;
use App\Models\MedicineCatalogue;
use App\Models\MedicineGtinMapping;
use App\Services\Frontend4\DmdSynchronisationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class Frontend4DmdSynchronisationTest extends TestCase
{
    use DatabaseTransactions;

    private array $temporaryFiles = [];

    public function test_imports_official_identifiers_hierarchy_controlled_status_and_gtin(): void
    {
        $this->requireSchema();
        $source = $this->release($this->completeRelease());

        $event = app(DmdSynchronisationService::class)
            ->import($source, '2026.08.13', '2026-08-13', false, true);

        $vmp = MedicineCatalogue::where('dmd_code', '100001')->firstOrFail();
        $ampp = MedicineCatalogue::where('dmd_code', '100004')->firstOrFail();
        $this->assertSame('VMP', $vmp->dmd_concept_level);
        $this->assertTrue((bool) $vmp->is_controlled);
        $this->assertSame('2', $vmp->cd_schedule);
        $this->assertDatabaseHas('medicine_catalogue_relationships', [
            'child_medicine_id' => $ampp->id,
            'relationship_type' => 'has_amp',
        ]);
        $this->assertDatabaseHas('medicine_gtin_mappings', [
            'medicine_id' => $ampp->id,
            'gtin' => '05012345678901',
            'active' => 1,
        ]);
        $this->assertSame('applied', $event->status);
        $this->assertSame(5, (int) $event->concept_count);
    }

    public function test_dry_run_records_provenance_without_changing_the_catalogue(): void
    {
        $this->requireSchema();
        $source = $this->release($this->completeRelease('20000'));

        $event = app(DmdSynchronisationService::class)
            ->import($source, 'dry-run-fixture', '2026-08-13', true, true);

        $this->assertSame('dry_run', $event->status);
        $this->assertFalse(MedicineCatalogue::where('dmd_code', '200000')->exists());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $event->source_sha256);
    }

    public function test_official_ampp_gtin_mapping_file_supports_multiple_barcodes(): void
    {
        $this->requireSchema();
        $xml = '<DMD>'
            .'<AMPP><APPID>210000</APPID><APID>210001</APID><VPPID>210002</VPPID>'
            .'<NM>Mapped actual pack</NM><INVALID>0</INVALID></AMPP>'
            .'<AMPP><AMPPID>210000</AMPPID><GTINDATA><GTIN>5012617009784</GTIN></GTINDATA>'
            .'<GTINDATA><GTIN>05012617009784</GTIN></GTINDATA></AMPP>'
            .'</DMD>';

        app(DmdSynchronisationService::class)
            ->import($this->release($xml), 'gtin-fixture', '2026-08-13', false, true);

        $medicine = MedicineCatalogue::where('dmd_code', '210000')->firstOrFail();
        $this->assertSame(2, MedicineGtinMapping::where('medicine_id', $medicine->id)->count());
    }

    public function test_separate_official_controlled_drug_record_maps_category_to_schedule(): void
    {
        $this->requireSchema();
        $xml = '<DMD><VMP><VPID>220000</VPID><NM>Controlled fixture</NM><INVALID>0</INVALID></VMP>'
            .'<CONTROL_DRUG_INFO><VPID>220000</VPID><CATCD>10</CATCD></CONTROL_DRUG_INFO></DMD>';

        app(DmdSynchronisationService::class)
            ->import($this->release($xml), 'controlled-fixture', '2026-08-13', false, true);

        $medicine = MedicineCatalogue::where('dmd_code', '220000')->firstOrFail();
        $this->assertTrue((bool) $medicine->is_controlled);
        $this->assertSame('5', $medicine->cd_schedule);
    }

    public function test_the_same_applied_release_cannot_be_applied_twice(): void
    {
        $this->requireSchema();
        $source = $this->release($this->completeRelease('30000'));
        $service = app(DmdSynchronisationService::class);
        $service->import($source, 'duplicate-fixture', '2026-08-13', false, true);

        $this->expectException(ValidationException::class);
        $service->import($source, 'duplicate-fixture', '2026-08-13', false, true);
    }

    public function test_a_malformed_release_is_rejected_before_catalogue_mutation(): void
    {
        $this->requireSchema();
        $source = $this->release('<DMD><VTM><VTMID>400000</VTMID><NM>Broken');

        try {
            app(DmdSynchronisationService::class)
                ->import($source, 'broken-fixture', '2026-08-13', false, true);
            $this->fail('A malformed XML release was accepted.');
        } catch (ValidationException) {
            $this->assertFalse(MedicineCatalogue::where('dmd_code', '400000')->exists());
        }
    }

    public function test_explicit_replacement_stops_future_selection_but_preserves_record_identity(): void
    {
        $this->requireSchema();
        $xml = '<DMD>'
            .'<VMP><VPID>500000</VPID><NM>Previous product</NM><INVALID>0</INVALID></VMP>'
            .'<VMP><VPID>500001</VPID><VPIDPREV>500000</VPIDPREV><NM>Current product</NM><INVALID>0</INVALID></VMP>'
            .'</DMD>';

        app(DmdSynchronisationService::class)
            ->import($this->release($xml), 'replacement-fixture', '2026-08-13', false, true);

        $old = MedicineCatalogue::where('dmd_code', '500000')->firstOrFail();
        $new = MedicineCatalogue::where('dmd_code', '500001')->firstOrFail();
        $this->assertSame('discontinued', $old->dmd_status);
        $this->assertSame($new->id, (int) $old->replaced_by_id);
        $this->assertFalse(MedicineCatalogue::selectable()->whereKey($old->id)->exists());
        $this->assertDatabaseHas('medicine_catalogue_relationships', [
            'child_medicine_id' => $old->id,
            'parent_medicine_id' => $new->id,
            'relationship_type' => 'replaced_by',
        ]);
    }

    public function test_absence_from_a_later_file_never_guesses_that_a_concept_was_retired(): void
    {
        $this->requireSchema();
        $service = app(DmdSynchronisationService::class);
        $service->import($this->release(
            '<DMD><VTM><VTMID>600000</VTMID><NM>Still valid</NM><INVALID>0</INVALID></VTM></DMD>'
        ), 'first-fixture', '2026-08-06', false, true);
        $service->import($this->release(
            '<DMD><VTM><VTMID>600001</VTMID><NM>Another concept</NM><INVALID>0</INVALID></VTM></DMD>'
        ), 'second-fixture', '2026-08-13', false, true);

        $this->assertSame('current', MedicineCatalogue::where('dmd_code', '600000')->value('dmd_status'));
    }

    public function test_local_identifier_collision_rolls_back_and_is_audited_without_sensitive_details(): void
    {
        $this->requireSchema();
        MedicineCatalogue::create([
            'dmd_code' => '700000',
            'dmd_concept_level' => 'VTM',
            'name' => 'Local provisional record',
            'dmd_status' => 'current',
            'is_local' => true,
        ]);
        $source = $this->release(
            '<DMD><VTM><VTMID>700000</VTMID><NM>Official concept</NM><INVALID>0</INVALID></VTM></DMD>'
        );

        try {
            app(DmdSynchronisationService::class)
                ->import($source, 'collision-fixture', '2026-08-13', false, true);
            $this->fail('An official identifier replaced a local record.');
        } catch (ValidationException) {
            $event = Frontend4TerminologyImport::where('status', 'failed')->latest('id')->firstOrFail();
            $this->assertSame('The dm+d release failed validation.', $event->failure_message);
            $this->assertSame('Local provisional record', MedicineCatalogue::where('dmd_code', '700000')->value('name'));
        }
    }

    public function test_import_events_are_append_only(): void
    {
        $this->requireSchema();
        $event = app(DmdSynchronisationService::class)->import(
            $this->release($this->completeRelease('80000')),
            'append-only-fixture',
            '2026-08-13',
            true,
            true
        );

        $this->expectException(LogicException::class);
        $event->update(['status' => 'failed']);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    private function completeRelease(string $prefix = '10000'): string
    {
        return '<DMD>'
            .'<VTM><VTMID>'.$prefix.'0</VTMID><NM>Test substance</NM><INVALID>0</INVALID></VTM>'
            .'<VMP><VPID>'.$prefix.'1</VPID><VTMID>'.$prefix.'0</VTMID><NM>Test product</NM>'
            .'<CONTROL_DRUG_INFO><CD>2</CD></CONTROL_DRUG_INFO><INVALID>0</INVALID></VMP>'
            .'<AMP><APID>'.$prefix.'2</APID><VPID>'.$prefix.'1</VPID><NM>Test branded product</NM><INVALID>0</INVALID></AMP>'
            .'<VMPP><VPPID>'.$prefix.'3</VPPID><VPID>'.$prefix.'1</VPID><NM>Test virtual pack</NM><INVALID>0</INVALID></VMPP>'
            .'<AMPP><APPID>'.$prefix.'4</APPID><APID>'.$prefix.'2</APID><VPPID>'.$prefix.'3</VPPID>'
            .'<NM>Test actual pack</NM><GTIN>05012345678901</GTIN><INVALID>0</INVALID></AMPP>'
            .'</DMD>';
    }

    private function release(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'frontend4-dmd-');
        file_put_contents($path, $xml);
        $this->temporaryFiles[] = $path;

        return $path;
    }

    private function requireSchema(): void
    {
        foreach ([
            'medicine_catalogue',
            'frontend4_terminology_imports',
            'medicine_catalogue_relationships',
            'medicine_gtin_mappings',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped('Run the Frontend 4 dm+d synchronisation migration first.');
            }
        }
    }
}
