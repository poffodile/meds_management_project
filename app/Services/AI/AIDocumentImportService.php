<?php

namespace App\Services\AI;

use App\Models\AIDocumentImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;

class AIDocumentImportService
{
    private OpenAIService $openAI;
    private PIIFilter $piiFilter;
    private TokenTracker $tokenTracker;

    public function __construct(OpenAIService $openAI, PIIFilter $piiFilter, TokenTracker $tokenTracker)
    {
        $this->openAI = $openAI;
        $this->piiFilter = $piiFilter;
        $this->tokenTracker = $tokenTracker;
    }

    public function extractTextFromFile(string $filePath): string
    {
        $fullPath = storage_path('app/private/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new RuntimeException('File not found.');
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            $text = $this->extractTextFromPdf($fullPath);
        } elseif (in_array($ext, ['docx', 'doc'])) {
            $text = $this->extractTextFromDocx($fullPath);
        } else {
            throw new RuntimeException('Unsupported file type.');
        }

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        if (strlen($text) < 50) {
            throw new RuntimeException('Could not extract enough readable text from this document. The file may be a scanned image or contain mostly images.');
        }

        return $text;
    }

    public function extractTextFromPdf(string $fullPath): string
    {
        $fileSize = filesize($fullPath);
        if ($fileSize > 20 * 1024 * 1024) {
            throw new RuntimeException('PDF file is too large to process safely.');
        }

        $rawBytes = file_get_contents($fullPath, false, null, 0, 50000);
        if (preg_match('/\/JS\s|\/JavaScript\s|\/Launch\s|\/SubmitForm\s|\/OpenAction\s/i', $rawBytes)) {
            throw new RuntimeException('This PDF contains embedded scripts or actions and cannot be processed for security reasons.');
        }

        $parser = new PdfParser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        if (strlen($text) > 500000) {
            $text = substr($text, 0, 500000);
        }

        return $text;
    }

    private function extractTextFromDocx(string $fullPath): string
    {
        $fileSize = filesize($fullPath);
        if ($fileSize > 20 * 1024 * 1024) {
            throw new RuntimeException('Document file is too large to process safely.');
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($fullPath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->extractElementText($element) . "\n";
                if (strlen($text) > 500000) {
                    break 2;
                }
            }
        }

        return $text;
    }

    private function extractElementText($element): string
    {
        if (method_exists($element, 'getText')) {
            return $element->getText();
        }

        if (method_exists($element, 'getElements')) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $parts[] = $this->extractElementText($child);
            }
            return implode(' ', array_filter($parts));
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
            $rows = [];
            foreach ($element->getRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cellText = [];
                    foreach ($cell->getElements() as $cellElement) {
                        $cellText[] = $this->extractElementText($cellElement);
                    }
                    $cells[] = implode(' ', array_filter($cellText));
                }
                $rows[] = implode(' | ', $cells);
            }
            return implode("\n", $rows);
        }

        return '';
    }

    public function extractDataWithAI(int $importId, int $homeId, int $userId): array
    {
        $import = AIDocumentImport::where('id', $importId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$import) {
            throw new RuntimeException('Import record not found.');
        }

        if ($import->import_status !== 'uploaded') {
            throw new RuntimeException('This document has already been processed.');
        }

        if (!$this->openAI->isConfigured()) {
            throw new RuntimeException('AI is not configured. Please contact your administrator.');
        }

        if ($this->tokenTracker->isCapExceeded($homeId)) {
            throw new RuntimeException('Daily AI usage limit reached. Please try again tomorrow.');
        }

        $import->update(['import_status' => 'extracting']);

        try {
            $text = $this->extractTextFromFile($import->stored_path);

            $client = DB::table('service_user')
                ->where('id', $import->client_id)
                ->where('home_id', $homeId)
                ->first();

            $clientName = $client ? $client->name : 'Unknown Client';

            $filteredText = $this->piiFilter->filter($text, $homeId, true);

            if (strlen($filteredText) > 15000) {
                $filteredText = substr($filteredText, 0, 15000) . "\n\n[Document truncated — remaining text omitted]";
            }

            $prompts = $this->buildExtractionPrompt($filteredText, $clientName);

            $result = $this->openAI->chatJson(
                [
                    ['role' => 'system', 'content' => $prompts['system_prompt']],
                    ['role' => 'user', 'content' => $prompts['user_prompt']],
                ],
                config('ai.quality_model', 'gpt-4o')
            );

            $extracted = json_decode($result['content'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('AI returned invalid response format.');
            }

            $validCategories = ['care_history', 'medications', 'risk_assessments', 'client_profile', 'body_map', 'dols', 'document_summary'];
            $cleaned = [];
            foreach ($validCategories as $cat) {
                if (isset($extracted[$cat])) {
                    $cleaned[$cat] = $extracted[$cat];
                }
            }

            $import->update([
                'import_status' => 'extracted',
                'extracted_data' => $cleaned,
                'extracted_text_length' => strlen($text),
                'ai_model' => $result['model'],
                'tokens_input' => $result['tokens_input'],
                'tokens_output' => $result['tokens_output'],
                'generation_time_ms' => $result['latency_ms'],
            ]);

            $this->tokenTracker->log(
                $homeId, $userId, 'document_import', $result['model'],
                $result['tokens_input'], $result['tokens_output'], 'success',
                null, null, $result['latency_ms']
            );

            return [
                'status' => true,
                'extracted_data' => $cleaned,
                'tokens_used' => $result['tokens_input'] + $result['tokens_output'],
            ];

        } catch (RuntimeException $e) {
            $import->update([
                'import_status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000),
            ]);

            $this->tokenTracker->log(
                $homeId, $userId, 'document_import', config('ai.quality_model', 'gpt-4o'),
                0, 0, 'error', null, $e->getMessage()
            );

            throw $e;
        }
    }

    public function importToDatabase(int $importId, array $selectedCategories, int $homeId, int $userId): array
    {
        $import = AIDocumentImport::where('id', $importId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$import) {
            throw new RuntimeException('Import record not found.');
        }

        if ($import->import_status !== 'extracted') {
            throw new RuntimeException('Document must be extracted before importing.');
        }

        $data = $import->extracted_data;
        if (!$data) {
            throw new RuntimeException('No extracted data available.');
        }

        $validCategories = ['care_history', 'medications', 'risk_assessments', 'client_profile', 'body_map', 'dols'];
        $selectedCategories = array_intersect($selectedCategories, $validCategories);

        if (empty($selectedCategories)) {
            return ['status' => true, 'summary' => [], 'message' => 'No categories selected.'];
        }

        $import->update(['import_status' => 'importing']);

        $summary = [];
        $clientId = $import->client_id;

        try {
            foreach ($selectedCategories as $category) {
                if (!isset($data[$category]) || empty($data[$category])) {
                    $summary[$category] = 0;
                    continue;
                }

                switch ($category) {
                    case 'care_history':
                        $summary[$category] = $this->importCareHistory($clientId, $homeId, $data[$category]);
                        break;
                    case 'medications':
                        $summary[$category] = $this->importMedications($clientId, $homeId, $userId, $data[$category]);
                        break;
                    case 'risk_assessments':
                        $summary[$category] = $this->importRiskAssessments($clientId, $homeId, $data[$category]);
                        break;
                    case 'client_profile':
                        $summary[$category] = $this->updateClientProfile($clientId, $homeId, $data[$category]);
                        break;
                    case 'body_map':
                        $summary[$category] = $this->importBodyMap($clientId, $homeId, $userId, $data[$category]);
                        break;
                    case 'dols':
                        $summary[$category] = $this->importDols($clientId, $homeId, $userId, $data[$category]);
                        break;
                }
            }

            $this->storeFileRecord($import, $homeId);

            $import->update([
                'import_status' => 'completed',
                'imported_categories' => $selectedCategories,
                'import_summary' => $summary,
            ]);

            return ['status' => true, 'summary' => $summary];

        } catch (\Exception $e) {
            Log::error('Document import failed', [
                'import_id' => $importId,
                'error' => $e->getMessage(),
            ]);

            $import->update([
                'import_status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 1000),
            ]);

            throw new RuntimeException('Import failed: ' . $e->getMessage());
        }
    }

    private function importCareHistory(int $clientId, int $homeId, array $entries): int
    {
        $count = 0;
        $now = Carbon::now();

        foreach ($entries as $entry) {
            if (empty($entry['title']) || empty($entry['description'])) {
                continue;
            }

            DB::table('su_care_history')->insert([
                'home_id' => $homeId,
                'service_user_id' => $clientId,
                'title' => substr($entry['title'], 0, 255),
                'date' => $this->parseDate($entry['date'] ?? null) ?? $now->toDateString(),
                'description' => substr($entry['description'], 0, 5000),
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function importMedications(int $clientId, int $homeId, int $userId, array $meds): int
    {
        $count = 0;
        $now = Carbon::now();

        foreach ($meds as $med) {
            if (empty($med['medication_name'])) {
                continue;
            }

            $timeSlots = null;
            if (!empty($med['time_slots']) && is_array($med['time_slots'])) {
                $timeSlots = json_encode($med['time_slots']);
            }

            DB::table('mar_sheets')->insert([
                'home_id' => $homeId,
                'client_id' => $clientId,
                'medication_name' => substr($med['medication_name'], 0, 255),
                'dosage' => substr($med['dosage'] ?? '', 0, 100) ?: null,
                'dose' => substr($med['dose'] ?? '', 0, 100) ?: null,
                'route' => substr($med['route'] ?? '', 0, 100) ?: null,
                'frequency' => substr($med['frequency'] ?? '', 0, 255) ?: null,
                'time_slots' => $timeSlots,
                'as_required' => 0,
                'reason_for_medication' => !empty($med['reason_for_medication']) ? substr($med['reason_for_medication'], 0, 2000) : null,
                'allergies_warnings' => !empty($med['allergies_warnings']) ? substr($med['allergies_warnings'], 0, 2000) : null,
                'start_date' => $this->parseDate($med['start_date'] ?? null),
                'mar_status' => 'active',
                'discontinued' => 0,
                'created_by' => $userId,
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function importRiskAssessments(int $clientId, int $homeId, array $risks): int
    {
        $count = 0;
        $now = Carbon::now();

        $existingRisks = DB::table('risk')
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->pluck('id', 'description')
            ->toArray();

        foreach ($risks as $risk) {
            if (empty($risk['risk_type'])) {
                continue;
            }

            $riskId = $this->matchRiskType($risk['risk_type'], $existingRisks);

            if (!$riskId) {
                $riskId = DB::table('risk')->insertGetId([
                    'home_id' => $homeId,
                    'description' => substr($risk['risk_type'], 0, 255),
                    'icon' => 'fa-exclamation-triangle',
                    'status' => 1,
                    'is_deleted' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $statusMap = ['low' => 1, 'medium' => 2, 'high' => 3];
            $status = $statusMap[strtolower($risk['risk_level'] ?? 'medium')] ?? 2;

            DB::table('su_risk')->insert([
                'home_id' => (string) $homeId,
                'service_user_id' => $clientId,
                'risk_id' => $riskId,
                'status' => $status,
                'read_notify' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function updateClientProfile(int $clientId, int $homeId, $profile): int
    {
        if (!is_array($profile) || empty($profile)) {
            return 0;
        }

        $allowedFields = ['allergies', 'medical_notes', 'care_needs', 'mental_health_issues', 'drug_n_alcohol_issues', 'suMobility'];

        $updates = [];
        foreach ($allowedFields as $field) {
            if (isset($profile[$field]) && is_string($profile[$field]) && trim($profile[$field]) !== '') {
                $updates[$field] = substr(trim($profile[$field]), 0, 5000);
            }
        }

        if (empty($updates)) {
            return 0;
        }

        $updates['updated_at'] = Carbon::now();

        DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->update($updates);

        return count($updates) - 1;
    }

    private function importBodyMap(int $clientId, int $homeId, int $userId, array $entries): int
    {
        $count = 0;
        $now = Carbon::now();

        foreach ($entries as $entry) {
            if (empty($entry['injury_type']) && empty($entry['injury_description'])) {
                continue;
            }

            DB::table('body_map')->insert([
                'home_id' => $homeId,
                'service_user_id' => $clientId,
                'staff_id' => $userId,
                'su_risk_id' => 0,
                'sel_body_map_id' => $entry['body_part'] ?? 'general',
                'injury_type' => substr($entry['injury_type'] ?? '', 0, 50) ?: null,
                'injury_description' => !empty($entry['injury_description']) ? substr($entry['injury_description'], 0, 2000) : null,
                'injury_date' => $this->parseDate($entry['injury_date'] ?? null) ?? $now->toDateString(),
                'injury_size' => substr($entry['injury_size'] ?? '', 0, 100) ?: null,
                'injury_colour' => substr($entry['injury_colour'] ?? '', 0, 50) ?: null,
                'is_deleted' => '0',
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function importDols(int $clientId, int $homeId, int $userId, array $entries): int
    {
        $count = 0;
        $now = Carbon::now();

        foreach ($entries as $entry) {
            if (empty($entry['dols_status'])) {
                continue;
            }

            DB::table('dols')->insert([
                'home_id' => $homeId,
                'user_id' => $userId,
                'client_id' => $clientId,
                'dols_status' => substr($entry['dols_status'], 0, 255),
                'authorisation_type' => !empty($entry['authorisation_type']) ? substr($entry['authorisation_type'], 0, 255) : null,
                'reason_for_dols' => !empty($entry['reason_for_dols']) ? substr($entry['reason_for_dols'], 0, 255) : null,
                'mental_capacity_assessment' => !empty($entry['mental_capacity_assessment']) ? 1 : 0,
                'imca_appointed' => 0,
                'appeal_rights' => 0,
                'care_plan_updated' => 0,
                'family_notified' => 0,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $count++;
        }

        return $count;
    }

    private function storeFileRecord(AIDocumentImport $import, int $homeId): void
    {
        $medicalCategoryId = DB::table('file_category')
            ->where('name', 'medical')
            ->where('is_deleted', 0)
            ->value('id');

        if (!$medicalCategoryId) {
            $medicalCategoryId = DB::table('file_category')
                ->where('is_deleted', 0)
                ->value('id') ?? 1;
        }

        DB::table('su_file_manager')->insert([
            'home_id' => $homeId,
            'service_user_id' => $import->client_id,
            'category_id' => $medicalCategoryId,
            'file' => $import->stored_path,
            'is_deleted' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    private function buildExtractionPrompt(string $pdfText, string $clientName): array
    {
        $systemPrompt = <<<'PROMPT'
You are a healthcare document analysis expert working in a UK care home setting.
Your task is to extract structured data from uploaded care documents (assessments, discharge letters, GP summaries, care plans).

RULES:
1. Only extract information that is EXPLICITLY stated in the document. Never infer or generate data that isn't there.
2. If a category has no relevant data in the document, return an empty array or null for that category.
3. Dates should be in YYYY-MM-DD format. If only a month/year is given, use the 1st of the month.
4. For medications, extract ALL fields if available. Use null for fields not mentioned in the document.
5. For risk assessments, map risk types to standard categories: Falls, Choking, Pressure Sores, Self-Harm, Aggression, Absconding, Fire, Moving and Handling, Nutrition, Medication, Safeguarding.
6. For client profile fields, only include fields that contain NEW or UPDATED information from the document.
7. The document_summary should be 1-2 sentences describing what the document is and what data it contains.

You MUST respond with valid JSON matching the provided schema. Do not include any text outside the JSON.
PROMPT;

        $userPrompt = <<<PROMPT
Analyse the following care document for client: <client_name>{$clientName}</client_name>

Document text:
---
{$pdfText}
---

Extract all relevant structured data into the following JSON format:
{
    "care_history": [{"title": "string", "date": "YYYY-MM-DD", "description": "string"}],
    "medications": [{"medication_name": "string", "dosage": "string", "dose": "string", "route": "string", "frequency": "string", "time_slots": ["HH:MM"], "reason_for_medication": "string", "allergies_warnings": "string", "start_date": "YYYY-MM-DD"}],
    "risk_assessments": [{"risk_type": "string", "risk_level": "low|medium|high", "description": "string", "control_measures": "string"}],
    "client_profile": {"allergies": "string", "medical_notes": "string", "care_needs": "string", "mental_health_issues": "string", "drug_n_alcohol_issues": "string", "suMobility": "string"},
    "body_map": [{"injury_type": "string", "injury_description": "string", "body_part": "string", "injury_date": "YYYY-MM-DD", "injury_size": "string", "injury_colour": "string"}],
    "dols": [{"dols_status": "string", "authorisation_type": "string", "reason_for_dols": "string", "mental_capacity_assessment": "string"}],
    "document_summary": "string"
}

Return empty arrays [] for categories with no data found. Respond with JSON only.
PROMPT;

        return [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
        ];
    }

    private function matchRiskType(string $riskType, array $existingRisks): ?int
    {
        $riskTypeLower = strtolower(trim($riskType));

        foreach ($existingRisks as $description => $id) {
            if (strtolower(trim($description)) === $riskTypeLower) {
                return $id;
            }
        }

        foreach ($existingRisks as $description => $id) {
            if (str_contains(strtolower($description), $riskTypeLower) ||
                str_contains($riskTypeLower, strtolower($description))) {
                return $id;
            }
        }

        return null;
    }

    private function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        try {
            $date = Carbon::parse($dateStr);
            if ($date->year < 1900 || $date->year > 2100) {
                return null;
            }
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
