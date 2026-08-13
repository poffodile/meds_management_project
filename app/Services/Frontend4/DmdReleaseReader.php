<?php

namespace App\Services\Frontend4;

use Generator;
use Illuminate\Validation\ValidationException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleXMLElement;
use XMLReader;

class DmdReleaseReader
{
    private const CONCEPTS = [
        'VTM' => 'VTMID',
        'VMP' => 'VPID',
        'AMP' => 'APID',
        'VMPP' => 'VPPID',
        'AMPP' => 'APPID',
    ];

    public function scan(string $source): array
    {
        $this->ensureExtensions();
        $files = $this->files($source);
        $hash = hash_init('sha256');
        $counts = [
            'concepts' => 0, 'relationships' => 0, 'gtins' => 0,
            'replacements' => 0, 'classifications' => 0,
        ];
        foreach ($files as $file) {
            hash_update($hash, basename($file)."\0".hash_file('sha256', $file)."\0");
        }
        foreach ($this->recordsFromFiles($files) as $record) {
            $counts[$record['type'].'s']++;
        }

        return [
            'source_name' => count($files) === 1 ? basename($files[0]) : basename(realpath($source)),
            'source_sha256' => hash_final($hash),
            'file_count' => count($files),
            'counts' => $counts,
        ];
    }

    public function records(string $source): Generator
    {
        $this->ensureExtensions();
        yield from $this->recordsFromFiles($this->files($source));
    }

    private function recordsFromFiles(array $files): Generator
    {
        foreach ($files as $file) {
            yield from $this->readFile($file);
        }
    }

    private function readFile(string $file): Generator
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $reader = new XMLReader;
        if (! $reader->open($file, null, LIBXML_NONET | LIBXML_COMPACT)) {
            libxml_use_internal_errors($previous);
            $this->invalid('The dm+d XML file could not be opened.');
        }
        $reader->setParserProperty(XMLReader::SUBST_ENTITIES, false);

        try {
            while ($reader->read()) {
                if ($reader->nodeType === XMLReader::DOC_TYPE) {
                    $this->invalid('DTD declarations are not accepted in dm+d release files.');
                }
                if ($reader->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }
                $level = strtoupper($reader->localName);
                if ($level === 'CONTROL_DRUG_INFO') {
                    $node = simplexml_load_string(
                        $reader->readOuterXml(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT
                    );
                    if (! $node) {
                        $this->invalid('A controlled-drug classification record is malformed.');
                    }
                    $code = $this->code($this->value($node, 'VPID'), 'VPID');
                    $category = trim((string) ($this->value($node, 'CATCD') ?? $this->value($node, 'CD')));
                    yield [
                        'type' => 'classification',
                        'code' => $code,
                        'cd_schedule' => $this->scheduleForCategory($category),
                    ];
                    continue;
                }
                if (in_array($level, ['AMPPGTIN', 'AMPP_GTIN'], true)) {
                    $node = simplexml_load_string(
                        $reader->readOuterXml(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT
                    );
                    $code = $this->code($this->value($node, 'APPID'), 'APPID');
                    $gtin = trim((string) ($this->value($node, 'GTIN') ?? $this->value($node, 'GTINCD')));
                    if (! preg_match('/^[0-9]{8,14}$/', $gtin)) {
                        $this->invalid('An AMPP GTIN is not an 8 to 14 digit identifier.');
                    }
                    yield ['type' => 'gtin', 'code' => $code, 'gtin' => $gtin];
                    continue;
                }
                if (! isset(self::CONCEPTS[$level])) {
                    continue;
                }
                $node = simplexml_load_string(
                    $reader->readOuterXml(),
                    SimpleXMLElement::class,
                    LIBXML_NONET | LIBXML_COMPACT
                );
                if (! $node) {
                    $this->invalid('A dm+d XML record is malformed.');
                }
                if ($level === 'AMPP' && $this->value($node, 'APPID') === null
                    && $this->value($node, 'AMPPID') !== null) {
                    $code = $this->code($this->value($node, 'AMPPID'), 'AMPPID');
                    $gtins = $node->xpath('.//*[local-name()="GTIN"]');
                    if (! $gtins) {
                        $this->invalid('An AMPP/GTIN mapping record contains no GTIN.');
                    }
                    $seen = [];
                    foreach ($gtins as $gtinNode) {
                        $gtin = trim((string) $gtinNode);
                        if (! preg_match('/^[0-9]{8,14}$/', $gtin)) {
                            $this->invalid('An AMPP GTIN is not an 8 to 14 digit identifier.');
                        }
                        if (! isset($seen[$gtin])) {
                            yield ['type' => 'gtin', 'code' => $code, 'gtin' => $gtin];
                            $seen[$gtin] = true;
                        }
                    }
                    continue;
                }
                $code = $this->code($this->value($node, self::CONCEPTS[$level]), self::CONCEPTS[$level]);
                $name = trim((string) $this->value($node, 'NM'));
                $standaloneGtin = trim((string) ($this->value($node, 'GTIN') ?? $this->value($node, 'GTINCD')));
                if ($level === 'AMPP' && $name === '' && $standaloneGtin !== '') {
                    if (! preg_match('/^[0-9]{8,14}$/', $standaloneGtin)) {
                        $this->invalid('An AMPP GTIN is not an 8 to 14 digit identifier.');
                    }
                    yield ['type' => 'gtin', 'code' => $code, 'gtin' => $standaloneGtin];
                    continue;
                }
                if ($name === '' || mb_strlen($name) > 255) {
                    $this->invalid('A dm+d concept has a missing or overlong name.');
                }
                $invalid = trim((string) $this->value($node, 'INVALID'));
                $cd = $this->controlSchedule($node);
                $cdNodes = $node->xpath(
                    './/*[local-name()="CONTROL_DRUG_INFO"]/*[local-name()="CD"]'
                );
                $hasCdClassification = (bool) $cdNodes || $this->value($node, 'SCHED') !== null;

                yield [
                    'type' => 'concept',
                    'code' => $code,
                    'level' => $level,
                    'name' => $name,
                    'status' => ($invalid !== '' && $invalid !== '0') ? 'invalid' : 'current',
                    'cd_schedule' => $cd,
                    'has_cd_classification' => $hasCdClassification,
                ];

                foreach ($this->relationships($node, $level, $code) as $relationship) {
                    yield $relationship;
                }
                if ($level === 'AMPP') {
                    $gtin = $standaloneGtin;
                    if ($gtin !== '') {
                        if (! preg_match('/^[0-9]{8,14}$/', $gtin)) {
                            $this->invalid('An AMPP GTIN is not an 8 to 14 digit identifier.');
                        }
                        yield ['type' => 'gtin', 'code' => $code, 'gtin' => $gtin];
                    }
                }
                $previousCode = $this->previousCode($node, $level);
                if ($previousCode !== null && $previousCode !== $code) {
                    yield ['type' => 'replacement', 'old_code' => $previousCode, 'new_code' => $code];
                }
            }
            if (libxml_get_errors() !== []) {
                $this->invalid('The dm+d XML release is not well formed.');
            }
        } finally {
            $reader->close();
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function relationships(SimpleXMLElement $node, string $level, string $code): array
    {
        $parents = match ($level) {
            'VMP' => ['VTMID' => 'has_vtm'],
            'AMP' => ['VPID' => 'has_vmp'],
            'VMPP' => ['VPID' => 'has_vmp'],
            'AMPP' => ['APID' => 'has_amp', 'VPPID' => 'has_vmpp'],
            default => [],
        };
        $records = [];
        foreach ($parents as $tag => $type) {
            $parent = trim((string) $this->value($node, $tag));
            if ($parent !== '') {
                $records[] = [
                    'type' => 'relationship',
                    'child_code' => $code,
                    'parent_code' => $this->code($parent, $tag),
                    'relationship_type' => $type,
                ];
            }
        }

        return $records;
    }

    private function previousCode(SimpleXMLElement $node, string $level): ?string
    {
        $idTag = self::CONCEPTS[$level];
        foreach ([$idTag.'PREV', 'PREV'.$idTag, 'PREVIOUS'.$idTag] as $tag) {
            $value = trim((string) $this->value($node, $tag));
            if ($value !== '') {
                return $this->code($value, $tag);
            }
        }

        return null;
    }

    private function value(SimpleXMLElement $node, string $name): ?string
    {
        $values = $node->xpath('.//*[local-name()="'.$name.'"]');
        return $values && isset($values[0]) ? (string) $values[0] : null;
    }

    private function code(?string $value, string $field): string
    {
        $value = trim((string) $value);
        if (! preg_match('/^[0-9]{6,18}$/', $value)) {
            $this->invalid('A dm+d '.$field.' value is not a valid numeric concept identifier.');
        }

        return $value;
    }

    private function controlSchedule(SimpleXMLElement $node): ?string
    {
        $values = $node->xpath(
            './/*[local-name()="CONTROL_DRUG_INFO"]/*[local-name()="CD"]'
        );
        $value = $values && isset($values[0]) ? trim((string) $values[0]) : '';
        if ($value === '') {
            $value = trim((string) $this->value($node, 'SCHED'));
        }

        return $this->scheduleForCategory($value);
    }

    private function scheduleForCategory(string $value): ?string
    {
        return match (trim($value)) {
            '1' => '1',
            '2', '3' => '2',
            '4', '5', '6', '7' => '3',
            '8', '9' => '4',
            '10' => '5',
            default => null,
        };
    }

    private function files(string $source): array
    {
        $root = realpath($source);
        if ($root === false || (! is_file($root) && ! is_dir($root))) {
            $this->invalid('Provide an existing extracted dm+d XML file or directory.');
        }
        if (is_link($source)) {
            $this->invalid('Symbolic links are not accepted as dm+d release sources.');
        }
        $files = [];
        if (is_file($root)) {
            $files[] = $root;
        } else {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $entry) {
                if ($entry->isLink()) {
                    $this->invalid('Symbolic links are not accepted inside a dm+d release.');
                }
                if ($entry->isFile() && strtolower($entry->getExtension()) === 'xml') {
                    $files[] = $entry->getRealPath();
                }
                if (count($files) > 200) {
                    $this->invalid('The dm+d release contains too many XML files.');
                }
            }
        }
        sort($files, SORT_STRING);
        if ($files === []) {
            $this->invalid('No XML files were found in the extracted dm+d release.');
        }
        foreach ($files as $file) {
            if (filesize($file) > 2_000_000_000) {
                $this->invalid('A dm+d XML file exceeds the two gigabyte safety limit.');
            }
        }

        return $files;
    }

    private function ensureExtensions(): void
    {
        if (! class_exists(XMLReader::class) || ! function_exists('simplexml_load_string')) {
            $this->invalid('PHP XMLReader and SimpleXML support are required for dm+d imports.');
        }
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['release' => $message]);
    }
}
