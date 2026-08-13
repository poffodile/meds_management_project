<?php

namespace App\Services\Frontend4;

use App\Models\Frontend4TerminologyImport;
use App\Models\MedicineCatalogue;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DmdSynchronisationService
{
    private const BATCH_SIZE = 500;

    public function __construct(private DmdReleaseReader $reader)
    {
    }

    public function import(
        string $source,
        string $version,
        string $releaseDate,
        bool $dryRun = false,
        bool $allowSmallRelease = false,
        ?int $requestedBy = null
    ): Frontend4TerminologyImport {
        $version = trim($version);
        if ($version === '' || mb_strlen($version) > 100) {
            $this->invalid('Provide the official dm+d release version.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $releaseDate);
        if (! $date || $date->format('Y-m-d') !== $releaseDate) {
            $this->invalid('The release date must be a real date in YYYY-MM-DD format.');
        }

        $scan = $this->reader->scan($source);
        if (! $allowSmallRelease && $scan['counts']['concepts'] < 10000) {
            $this->invalid('The release is unexpectedly small; use --allow-small only for controlled test fixtures.');
        }
        if (Frontend4TerminologyImport::where('provider', 'nhs_dmd')
            ->where('source_sha256', $scan['source_sha256'])->where('status', 'applied')->exists()) {
            $this->invalid('This exact dm+d release has already been applied.');
        }
        if ($dryRun) {
            return $this->event($scan, $version, $releaseDate, 'dry_run', $requestedBy);
        }

        try {
            return DB::transaction(function () use ($source, $version, $releaseDate, $scan, $requestedBy) {
                $counts = [
                    'created' => 0, 'updated' => 0, 'relationships' => 0,
                    'gtins' => 0, 'replacements' => 0, 'classifications' => 0,
                ];
                $concepts = [];
                foreach ($this->reader->records($source) as $record) {
                    if ($record['type'] !== 'concept') {
                        continue;
                    }
                    $concepts[] = $record;
                    if (count($concepts) >= self::BATCH_SIZE) {
                        $this->writeConcepts($concepts, $version, $counts);
                        $concepts = [];
                    }
                }
                $this->writeConcepts($concepts, $version, $counts);

                $relations = $gtins = $replacements = $classifications = [];
                foreach ($this->reader->records($source) as $record) {
                    if ($record['type'] === 'relationship') {
                        $relations[] = $record;
                        if (count($relations) >= self::BATCH_SIZE) {
                            $counts['relationships'] += $this->writeRelationships($relations, $version);
                            $relations = [];
                        }
                    } elseif ($record['type'] === 'gtin') {
                        $gtins[] = $record;
                        if (count($gtins) >= self::BATCH_SIZE) {
                            $counts['gtins'] += $this->writeGtins($gtins, $version);
                            $gtins = [];
                        }
                    } elseif ($record['type'] === 'replacement') {
                        $replacements[] = $record;
                        if (count($replacements) >= self::BATCH_SIZE) {
                            $counts['replacements'] += $this->writeReplacements($replacements, $version);
                            $replacements = [];
                        }
                    } elseif ($record['type'] === 'classification') {
                        $classifications[] = $record;
                        if (count($classifications) >= self::BATCH_SIZE) {
                            $counts['classifications'] += $this->writeClassifications($classifications, $version);
                            $classifications = [];
                        }
                    }
                }
                $counts['relationships'] += $this->writeRelationships($relations, $version);
                $counts['gtins'] += $this->writeGtins($gtins, $version);
                $counts['replacements'] += $this->writeReplacements($replacements, $version);
                $counts['classifications'] = ($counts['classifications'] ?? 0)
                    + $this->writeClassifications($classifications, $version);

                return $this->event($scan, $version, $releaseDate, 'applied', $requestedBy, $counts);
            }, 3);
        } catch (Throwable $exception) {
            $message = $exception instanceof ValidationException
                ? 'The dm+d release failed validation.'
                : 'The dm+d release could not be applied.';
            $this->event($scan, $version, $releaseDate, 'failed', $requestedBy, [], $message);
            throw $exception;
        }
    }

    private function writeConcepts(array $records, string $version, array &$counts): void
    {
        if ($records === []) {
            return;
        }
        $codes = array_values(array_unique(array_column($records, 'code')));
        $existing = MedicineCatalogue::whereIn('dmd_code', $codes)->get()->keyBy('dmd_code');
        foreach ($existing as $medicine) {
            if ((bool) $medicine->is_local) {
                $this->invalid('A local catalogue record conflicts with an official dm+d concept identifier.');
            }
        }
        $now = now();
        $rows = [];
        foreach ($records as $record) {
            $current = $existing->get($record['code']);
            $controlled = $record['has_cd_classification']
                ? $record['cd_schedule'] !== null
                : (bool) ($current?->is_controlled ?? false);
            $schedule = $record['has_cd_classification']
                ? $record['cd_schedule']
                : $current?->cd_schedule;
            $rows[] = [
                'dmd_code' => $record['code'],
                'dmd_concept_level' => $record['level'],
                'name' => $record['name'],
                'is_controlled' => $controlled,
                'cd_schedule' => $schedule,
                'dmd_status' => $record['status'],
                'is_local' => false,
                'source_version' => $version,
                'source_updated_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            isset($existing[$record['code']]) ? $counts['updated']++ : $counts['created']++;
        }
        DB::table('medicine_catalogue')->upsert($rows, ['dmd_code'], [
            'dmd_concept_level', 'name', 'is_controlled', 'cd_schedule', 'dmd_status',
            'is_local', 'source_version', 'source_updated_at', 'updated_at',
        ]);
    }

    private function writeRelationships(array $records, string $version): int
    {
        if ($records === []) {
            return 0;
        }
        $codes = [];
        foreach ($records as $record) {
            $codes[] = $record['child_code'];
            $codes[] = $record['parent_code'];
        }
        $ids = MedicineCatalogue::whereIn('dmd_code', array_unique($codes))->pluck('id', 'dmd_code');
        $rows = [];
        foreach ($records as $record) {
            if (! isset($ids[$record['child_code']], $ids[$record['parent_code']])) {
                continue;
            }
            $rows[] = [
                'child_medicine_id' => $ids[$record['child_code']],
                'parent_medicine_id' => $ids[$record['parent_code']],
                'relationship_type' => $record['relationship_type'],
                'source_version' => $version,
                'source_updated_at' => now(),
            ];
        }
        if ($rows !== []) {
            DB::table('medicine_catalogue_relationships')->upsert(
                $rows,
                ['child_medicine_id', 'parent_medicine_id', 'relationship_type'],
                ['source_version', 'source_updated_at']
            );
        }

        return count($rows);
    }

    private function writeGtins(array $records, string $version): int
    {
        if ($records === []) {
            return 0;
        }
        $ids = MedicineCatalogue::whereIn('dmd_code', array_unique(array_column($records, 'code')))
            ->pluck('id', 'dmd_code');
        $rows = [];
        foreach ($records as $record) {
            if (! isset($ids[$record['code']])) {
                continue;
            }
            $rows[] = [
                'gtin' => $record['gtin'],
                'medicine_id' => $ids[$record['code']],
                'ampp_code' => $record['code'],
                'active' => true,
                'source_version' => $version,
                'source_updated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($rows !== []) {
            DB::table('medicine_gtin_mappings')->upsert(
                $rows, ['gtin', 'ampp_code'],
                ['medicine_id', 'active', 'source_version', 'source_updated_at', 'updated_at']
            );
        }

        return count($rows);
    }

    private function writeReplacements(array $records, string $version): int
    {
        if ($records === []) {
            return 0;
        }
        $codes = array_unique(array_merge(array_column($records, 'old_code'), array_column($records, 'new_code')));
        $ids = MedicineCatalogue::whereIn('dmd_code', $codes)->pluck('id', 'dmd_code');
        $rows = [];
        foreach ($records as $record) {
            if (! isset($ids[$record['old_code']], $ids[$record['new_code']])) {
                continue;
            }
            MedicineCatalogue::whereKey($ids[$record['old_code']])->update([
                'dmd_status' => 'discontinued',
                'replaced_by_id' => $ids[$record['new_code']],
                'source_version' => $version,
                'source_updated_at' => now(),
            ]);
            $rows[] = [
                'child_medicine_id' => $ids[$record['old_code']],
                'parent_medicine_id' => $ids[$record['new_code']],
                'relationship_type' => 'replaced_by',
                'source_version' => $version,
                'source_updated_at' => now(),
            ];
        }
        if ($rows !== []) {
            DB::table('medicine_catalogue_relationships')->upsert(
                $rows,
                ['child_medicine_id', 'parent_medicine_id', 'relationship_type'],
                ['source_version', 'source_updated_at']
            );
        }

        return count($rows);
    }

    private function writeClassifications(array $records, string $version): int
    {
        if ($records === []) {
            return 0;
        }
        $updated = 0;
        foreach ($records as $record) {
            $updated += MedicineCatalogue::where('dmd_code', $record['code'])->update([
                'is_controlled' => $record['cd_schedule'] !== null,
                'cd_schedule' => $record['cd_schedule'],
                'source_version' => $version,
                'source_updated_at' => now(),
            ]);
        }

        return $updated;
    }

    private function event(
        array $scan,
        string $version,
        string $releaseDate,
        string $status,
        ?int $requestedBy,
        array $appliedCounts = [],
        ?string $failure = null
    ): Frontend4TerminologyImport {
        $counts = $scan['counts'];

        return Frontend4TerminologyImport::create([
            'provider' => 'nhs_dmd',
            'source_version' => $version,
            'release_date' => $releaseDate,
            'source_name' => $scan['source_name'],
            'source_sha256' => $scan['source_sha256'],
            'status' => $status,
            'file_count' => $scan['file_count'],
            'concept_count' => $counts['concepts'],
            'relationship_count' => $counts['relationships'],
            'gtin_count' => $counts['gtins'],
            'replacement_count' => $counts['replacements'],
            'summary' => $appliedCounts,
            'failure_message' => $failure,
            'requested_by_user_id' => $requestedBy,
            'created_at' => now(),
        ]);
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['release' => $message]);
    }
}
