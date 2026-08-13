<?php

namespace App\Console\Commands;

use App\Services\Frontend4\DmdSynchronisationService;
use Illuminate\Console\Command;

class ImportFrontend4DmdRelease extends Command
{
    protected $signature = 'frontend4:dmd-import
        {path : Extracted NHS dm+d XML file or directory}
        {--version= : Official release version}
        {--release-date= : Official release date (YYYY-MM-DD)}
        {--dry-run : Validate, checksum and count without changing the catalogue}
        {--allow-small : Permit a small controlled test fixture}';

    protected $description = 'Validate and synchronise an extracted NHS dm+d release';

    public function handle(DmdSynchronisationService $service): int
    {
        $this->warn('Use only an approved, extracted NHS dm+d release. This command does not download TRUD data.');

        $event = $service->import(
            (string) $this->argument('path'),
            (string) $this->option('version'),
            (string) $this->option('release-date'),
            (bool) $this->option('dry-run'),
            (bool) $this->option('allow-small')
        );

        $this->info(sprintf(
            'dm+d %s: %d concepts, %d relationships, %d GTIN mappings, checksum %s',
            str_replace('_', ' ', $event->status),
            $event->concept_count,
            $event->relationship_count,
            $event->gtin_count,
            $event->source_sha256
        ));

        return self::SUCCESS;
    }
}
