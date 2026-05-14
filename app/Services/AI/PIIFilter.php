<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;

class PIIFilter
{
    private array $nameCache = [];

    /**
     * @param bool $skipNames When true, only filter structured PII (email, phone, NHS, DOB, postcode)
     *                        but keep known names intact. Used when names are already in the system prompt.
     */
    public function filter(string $text, int $homeId, bool $skipNames = false): string
    {
        $mode = $this->getMode();

        if ($mode === 'consent') {
            return $text;
        }

        if ($mode === 'redact') {
            return $this->redact($text, $homeId);
        }

        return $this->anonymise($text, $homeId, $skipNames);
    }

    public function getMode(): string
    {
        return config('ai.pii_mode', 'anonymise');
    }

    public function filterClientData(array $data): array
    {
        $mode = $this->getMode();
        if ($mode === 'consent') {
            return $data;
        }

        $sensitiveKeys = ['name', 'email', 'phone_no', 'mobile', 'date_of_birth', 'postcode', 'street', 'city', 'em_name', 'em_phone', 'nhs_number'];
        $filtered = $data;

        foreach ($sensitiveKeys as $key) {
            if (isset($filtered[$key])) {
                $filtered[$key] = $mode === 'redact' ? '[REDACTED]' : '[' . strtoupper(str_replace('_', ' ', $key)) . ']';
            }
        }

        return $filtered;
    }

    private function anonymise(string $text, int $homeId, bool $skipNames = false): string
    {
        $text = preg_replace('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', '[EMAIL]', $text);
        $text = preg_replace('/\b(?:0\d{2,4}[\s\-]?\d{3,4}[\s\-]?\d{3,4}|\+44[\s\-]?\d{4}[\s\-]?\d{6})\b/', '[PHONE]', $text);
        $text = preg_replace('/\b\d{3}\s?\d{3}\s?\d{4}\b/', '[NHS_NUMBER]', $text);

        $text = preg_replace('/\b\d{2}[\/-]\d{2}[\/-]\d{4}\b/', '[DATE]', $text);
        $text = preg_replace('/\b\d{4}[\/-]\d{2}[\/-]\d{2}\b/', '[DATE]', $text);
        $months = 'Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|January|February|March|April|May|June|July|August|September|October|November|December';
        $text = preg_replace('/\b\d{1,2}[\-\/\s](?:' . $months . ')[\-\/\s]\d{4}\b/i', '[DATE]', $text);
        $text = preg_replace('/\b(?:' . $months . ')[\-\/\s]\d{1,2},?\s*\d{4}\b/i', '[DATE]', $text);

        $text = preg_replace('/\b[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}\b/i', '[POSTCODE]', $text);

        $text = preg_replace('/\b(?:Ref|Reference|Case\s*(?:No|Number|Ref)?)[\s:]*#?\s*\d{4,}\b/i', '[CASE_REF]', $text);

        if ($skipNames) {
            return $text;
        }

        $names = $this->getNames($homeId);

        $clientLabel = 'A';
        foreach ($names['clients'] as $name) {
            if (empty($name)) {
                continue;
            }
            $parts = preg_split('/\s+/', trim($name));
            foreach ($parts as $part) {
                if (strlen($part) >= 2) {
                    $text = str_ireplace($part, '[Client ' . $clientLabel . ']', $text);
                }
            }
            $clientLabel++;
        }

        $staffNum = 1;
        foreach ($names['staff'] as $name) {
            if (empty($name)) {
                continue;
            }
            $parts = preg_split('/\s+/', trim($name));
            foreach ($parts as $part) {
                if (strlen($part) >= 2) {
                    $text = str_ireplace($part, '[Staff ' . $staffNum . ']', $text);
                }
            }
            $staffNum++;
        }

        $text = $this->anonymiseProperNouns($text);

        return $text;
    }

    private function anonymiseProperNouns(string $text): string
    {
        $titlePattern = '/\b(?:Mr|Mrs|Ms|Miss|Dr|Prof|Cllr|Rev|Sgt|Insp|PC|DC|DS|DI)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/';
        $text = preg_replace($titlePattern, '[PERSON]', $text);

        $fullNamePattern = '/\b([A-Z][a-z]{1,20})\s+([A-Z][a-z]{1,20})\s+([A-Z][a-z]{1,20})\b/';
        $text = preg_replace_callback($fullNamePattern, function ($match) {
            $skipWords = ['The', 'This', 'That', 'These', 'Those', 'They', 'Their', 'There', 'When', 'Where',
                'What', 'Which', 'While', 'With', 'From', 'Into', 'About', 'After', 'Before', 'During',
                'Under', 'Over', 'Between', 'Through', 'Against', 'Without', 'Within', 'Along',
                'Child', 'Family', 'Assessment', 'Protection', 'Planning', 'Meeting', 'Section',
                'School', 'Health', 'Social', 'Care', 'Service', 'Primary', 'Community', 'General',
                'Risk', 'Date', 'Type', 'Role', 'Agency', 'Professional', 'Information', 'Contact',
                'Not', 'Yes', 'All', 'Has', 'Had', 'Was', 'Were', 'Are', 'Been', 'Being', 'Have',
                'May', 'Can', 'Could', 'Would', 'Should', 'Will', 'Shall',
                'Low', 'Medium', 'High', 'None', 'Some', 'Any', 'Each', 'Every', 'Other'];
            if (in_array($match[1], $skipWords) || in_array($match[2], $skipWords) || in_array($match[3], $skipWords)) {
                return $match[0];
            }
            return '[PERSON]';
        }, $text);

        return $text;
    }

    private function redact(string $text, int $homeId): string
    {
        $text = $this->anonymise($text, $homeId);
        $text = str_replace(
            ['[Client ', '[Staff '],
            ['[REDACTED', '[REDACTED'],
            $text
        );
        return $text;
    }

    private function getNames(int $homeId): array
    {
        if (isset($this->nameCache[$homeId])) {
            return $this->nameCache[$homeId];
        }

        $clients = DB::table('service_user')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->pluck('name')
            ->toArray();

        $staff = DB::table('user')
            ->whereRaw("FIND_IN_SET(?, home_id)", [$homeId])
            ->where('is_deleted', 0)
            ->pluck('name')
            ->toArray();

        $this->nameCache[$homeId] = [
            'clients' => $clients,
            'staff' => $staff,
        ];

        return $this->nameCache[$homeId];
    }
}
