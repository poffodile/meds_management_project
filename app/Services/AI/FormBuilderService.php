<?php

namespace App\Services\AI;

use App\Models\FormTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FormBuilderService
{
    private OpenAIService $openAI;
    private PIIFilter $piiFilter;
    private TokenTracker $tokenTracker;
    private AIDocumentImportService $docService;

    public function __construct(
        OpenAIService $openAI,
        PIIFilter $piiFilter,
        TokenTracker $tokenTracker,
        AIDocumentImportService $docService
    ) {
        $this->openAI = $openAI;
        $this->piiFilter = $piiFilter;
        $this->tokenTracker = $tokenTracker;
        $this->docService = $docService;
    }

    private const ALLOWED_TYPES = ['text', 'textarea', 'date', 'number', 'email', 'tel', 'select', 'checkbox', 'radio', 'risk', 'signature', 'table', 'info'];

    public function generateTemplateFromDocument(string $filePath, string $filename, int $homeId, int $userId): array
    {
        if (!$this->openAI->isConfigured()) {
            throw new RuntimeException('AI is not configured. Please check OPENAI_API_KEY in .env.');
        }

        if ($this->tokenTracker->isCapExceeded($homeId)) {
            throw new RuntimeException('Daily AI token limit reached. Please try again tomorrow.');
        }

        $text = $this->docService->extractTextFromFile($filePath);

        if (strlen($text) > 200000) {
            throw new RuntimeException('Document is too large to process. Please upload a shorter document (under 50 pages).');
        }

        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text);

        $filteredText = $this->piiFilter->filter($text, $homeId);

        $safeFilename = preg_replace('/[^a-zA-Z0-9._\- ]/', '', pathinfo($filename, PATHINFO_FILENAME));
        $safeFilename = substr($safeFilename, 0, 100) ?: 'document';

        $prompts = $this->buildTemplatePrompt($filteredText, $safeFilename);

        $textLen = strlen($filteredText);
        $maxTokens = $textLen > 50000 ? 16384 : ($textLen > 20000 ? 12000 : 8000);

        $formJson = $this->callAIForTemplate($prompts, $maxTokens);

        if (!$formJson) {
            $formJson = $this->callAIForTemplate($prompts, 16384);
        }

        if (!$formJson) {
            Log::warning('Form Builder: AI returned invalid JSON after retry', ['filename' => $filename]);
            $this->tokenTracker->log($homeId, $userId, 'form_template', 'gpt-4o',
                0, 0, 'error', md5($filteredText), 'AI returned invalid JSON after retry', 0);
            throw new RuntimeException('AI could not generate a valid form template from this document. Please try a different file.');
        }

        $formJson = $this->normalizeFieldTypes($formJson);

        $validationError = $this->getValidationError($formJson);
        if ($validationError) {
            Log::warning('Form Builder: AI JSON failed validation', ['error' => $validationError]);
            $this->tokenTracker->log($homeId, $userId, 'form_template', 'gpt-4o',
                0, 0, 'error', md5($filteredText), 'Validation: ' . $validationError, 0);
            throw new RuntimeException('AI could not generate a valid form template from this document. Please try a different file.');
        }

        $formJson = $this->sanitizeFormJson($formJson);

        $this->tokenTracker->log($homeId, $userId, 'form_template', 'gpt-4o',
            0, 0, 'success', md5($filteredText), null, 0);

        $template = FormTemplate::create([
            'home_id' => $homeId,
            'title' => substr(strip_tags($formJson['formTitle'] ?? 'Untitled Form'), 0, 255),
            'description' => isset($formJson['formDescription']) ? substr(strip_tags($formJson['formDescription']), 0, 1000) : null,
            'source_filename' => substr($filename, 0, 255),
            'form_json' => json_encode($formJson),
            'status' => 'published',
            'ai_generated' => 1,
            'created_by' => $userId,
        ]);

        return [
            'template_id' => $template->id,
            'form_json' => $formJson,
        ];
    }

    public function validateFormJson(array $formJson): bool
    {
        if (empty($formJson['formTitle']) || !is_string($formJson['formTitle'])) {
            return false;
        }

        if (!isset($formJson['sections']) || !is_array($formJson['sections']) || count($formJson['sections']) === 0) {
            return false;
        }

        foreach ($formJson['sections'] as $section) {
            if (empty($section['title']) || !is_string($section['title'])) {
                return false;
            }
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                return false;
            }
            foreach ($section['fields'] as $field) {
                if (empty($field['id']) || empty($field['label']) || empty($field['type'])) {
                    return false;
                }
                if (!in_array($field['type'], self::ALLOWED_TYPES)) {
                    return false;
                }
                if (in_array($field['type'], ['select', 'checkbox', 'radio', 'risk'])) {
                    if (empty($field['options']) || !is_array($field['options'])) {
                        return false;
                    }
                }
                if ($field['type'] === 'table') {
                    if (empty($field['columns']) || !is_array($field['columns'])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function sanitizeFormJson(array $formJson): array
    {
        $formJson['formTitle'] = strip_tags($formJson['formTitle'] ?? '');
        $formJson['formDescription'] = strip_tags($formJson['formDescription'] ?? '');

        if (!empty($formJson['sections'])) {
            foreach ($formJson['sections'] as &$section) {
                $section['title'] = strip_tags($section['title'] ?? '');
                if (!empty($section['fields'])) {
                    foreach ($section['fields'] as &$field) {
                        $field['label'] = strip_tags($field['label'] ?? '');
                        if (isset($field['hint'])) {
                            $field['hint'] = strip_tags($field['hint']);
                        }
                        if (isset($field['content'])) {
                            $field['content'] = strip_tags($field['content']);
                        }
                        if (!empty($field['options'])) {
                            $field['options'] = array_map('strip_tags', $field['options']);
                        }
                        if (!empty($field['columns'])) {
                            $field['columns'] = array_map('strip_tags', $field['columns']);
                        }
                    }
                    unset($field);
                }
            }
            unset($section);
        }

        return $formJson;
    }

    private function callAIForTemplate(array $prompts, int $maxTokens): ?array
    {
        try {
            $result = $this->openAI->chatJson([
                ['role' => 'system', 'content' => $prompts['system']],
                ['role' => 'user', 'content' => $prompts['user']],
            ], config('ai.quality_model', 'gpt-4o'), ['max_tokens' => $maxTokens, 'temperature' => 0.3]);
        } catch (\Exception $e) {
            Log::warning('Form Builder: AI call failed', ['error' => $e->getMessage()]);
            return null;
        }

        $formJson = json_decode($result['content'], true);
        if (!$formJson) {
            $cleaned = preg_replace('/^[^{]*/', '', $result['content']);
            $cleaned = preg_replace('/[^}]*$/', '', $cleaned);
            $formJson = json_decode($cleaned, true);
        }
        if (!$formJson) {
            Log::warning('Form Builder: AI returned invalid JSON', ['content_preview' => substr($result['content'], 0, 200)]);
        }

        return $formJson ?: null;
    }

    private function normalizeFieldTypes(array $formJson): array
    {
        $typeMap = [
            'datetime' => 'date',
            'time' => 'text',
            'phone' => 'tel',
            'url' => 'text',
            'file' => 'text',
            'image' => 'text',
            'password' => 'text',
            'hidden' => 'text',
            'color' => 'text',
            'range' => 'number',
            'multiselect' => 'checkbox',
            'dropdown' => 'select',
        ];

        if (!empty($formJson['sections'])) {
            foreach ($formJson['sections'] as &$section) {
                if (!empty($section['fields'])) {
                    foreach ($section['fields'] as &$field) {
                        if (isset($field['type'])) {
                            // Normalize known synonyms
                            if (isset($typeMap[$field['type']])) {
                                $field['type'] = $typeMap[$field['type']];
                            }

                            // Ensure select/checkbox/radio/risk have options
                            if (in_array($field['type'], ['select', 'checkbox', 'radio', 'risk'])) {
                                if (empty($field['options']) || !is_array($field['options'])) {
                                    if ($field['type'] === 'risk') {
                                        $field['options'] = ['Low', 'Medium', 'High'];
                                    } else {
                                        // Fallback to text if AI missed options
                                        $field['type'] = 'text';
                                    }
                                }
                            }

                            // Ensure table has columns
                            if ($field['type'] === 'table') {
                                if (empty($field['columns']) || !is_array($field['columns'])) {
                                    $field['columns'] = ['Column 1', 'Column 2', 'Column 3'];
                                }
                                if (empty($field['rows'])) {
                                    $field['rows'] = 3;
                                }
                            }
                        }
                    }
                    unset($field);
                }
            }
            unset($section);
        }

        return $formJson;
    }

    private function getValidationError(array $formJson): ?string
    {
        if (empty($formJson['formTitle']) || !is_string($formJson['formTitle'])) {
            return 'Missing or invalid formTitle';
        }
        if (!isset($formJson['sections']) || !is_array($formJson['sections']) || count($formJson['sections']) === 0) {
            return 'Missing or empty sections array';
        }
        foreach ($formJson['sections'] as $i => $section) {
            if (empty($section['title'])) {
                return "Section $i missing title";
            }
            if (!isset($section['fields']) || !is_array($section['fields'])) {
                return "Section '{$section['title']}' missing fields array";
            }
            foreach ($section['fields'] as $j => $field) {
                if (empty($field['id']) || empty($field['label']) || empty($field['type'])) {
                    return "Section '{$section['title']}' field $j missing id/label/type";
                }
                if (!in_array($field['type'], self::ALLOWED_TYPES)) {
                    return "Field '{$field['label']}' has invalid type '{$field['type']}'";
                }
                if (in_array($field['type'], ['select', 'checkbox', 'radio', 'risk'])) {
                    if (empty($field['options']) || !is_array($field['options'])) {
                        return "Field '{$field['label']}' (type {$field['type']}) missing options";
                    }
                }
                if ($field['type'] === 'table' && (empty($field['columns']) || !is_array($field['columns']))) {
                    return "Table field '{$field['label']}' missing columns";
                }
            }
        }
        return null;
    }

    public function aiFillForm(int $templateId, int $clientId, int $homeId, int $userId): array
    {
        if (!$this->openAI->isConfigured()) {
            throw new RuntimeException('AI is not configured. Please check OPENAI_API_KEY in .env.');
        }

        if ($this->tokenTracker->isCapExceeded($homeId)) {
            throw new RuntimeException('Daily AI token limit reached. Please try again tomorrow.');
        }

        $template = FormTemplate::where('id', $templateId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$template) {
            throw new RuntimeException('Template not found.');
        }

        $formJson = json_decode($template->form_json, true);
        if (!$formJson) {
            throw new RuntimeException('Invalid template data.');
        }

        $clientData = $this->gatherClientData($clientId, $homeId);
        if (empty($clientData['client'])) {
            throw new RuntimeException('Client not found.');
        }

        $prompts = $this->buildFillerPrompt($formJson, $clientData);

        $result = $this->openAI->chatJson([
            ['role' => 'system', 'content' => $prompts['system']],
            ['role' => 'user', 'content' => $prompts['user']],
        ], config('ai.quality_model', 'gpt-4o'), ['max_tokens' => 3000, 'temperature' => 0.2]);

        $values = json_decode($result['content'], true);
        if (!is_array($values)) {
            $this->tokenTracker->log($homeId, $userId, 'form_fill', $result['model'],
                $result['tokens_input'], $result['tokens_output'], 'error',
                md5(json_encode($formJson)), 'Invalid JSON from AI filler', $result['latency_ms']);
            throw new RuntimeException('AI could not fill the form. Please try again.');
        }

        $this->tokenTracker->log($homeId, $userId, 'form_fill', $result['model'],
            $result['tokens_input'], $result['tokens_output'], 'success',
            md5(json_encode($formJson)), null, $result['latency_ms']);

        $totalFields = 0;
        $filledCount = 0;
        foreach ($formJson['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                if (in_array($field['type'], ['info', 'signature'])) {
                    continue;
                }
                $totalFields++;
                if (isset($values[$field['id']]) && $values[$field['id']] !== null && $values[$field['id']] !== '') {
                    $filledCount++;
                }
            }
        }

        return [
            'values' => $values,
            'filled_count' => $filledCount,
            'total_fields' => $totalFields,
        ];
    }

    private function gatherClientData(int $clientId, int $homeId): array
    {
        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$client) {
            return ['client' => null];
        }

        $data = [
            'client' => [
                'name' => $client->name ?? '',
                'date_of_birth' => $client->date_of_birth ?? '',
                'gender' => $client->gender ?? '',
                'phone_no' => $client->phone_no ?? '',
                'address' => trim(($client->street ?? '') . ' ' . ($client->city ?? '') . ' ' . ($client->postcode ?? '')),
                'allergies' => $client->allergies ?? '',
                'medical_notes' => $client->medical_notes ?? '',
                'care_needs' => $client->care_needs ?? '',
                'mobility' => $client->suMobility ?? '',
                'emergency_contact' => ($client->em_name ?? '') . ' ' . ($client->em_phone ?? ''),
            ],
        ];

        $data['incidents'] = Schema::hasTable('su_incident_report')
            ? DB::table('su_incident_report')
                ->where('service_user_id', $clientId)
                ->where('home_id', $homeId)
                ->where('is_deleted', 0)
                ->orderBy('date', 'desc')
                ->limit(20)
                ->get(['title', 'date', 'formdata'])
                ->map(function ($row) {
                    $fd = json_decode($row->formdata ?? '{}', true);
                    return [
                        'title' => $row->title ?? '',
                        'date' => $row->date ?? '',
                        'description' => $fd['description'] ?? ($fd['what_happened'] ?? ''),
                        'severity' => $fd['severity'] ?? '',
                        'action_taken' => $fd['action_taken'] ?? '',
                    ];
                })->toArray()
            : [];

        $data['care_history'] = Schema::hasTable('su_care_history')
            ? DB::table('su_care_history')
                ->where('service_user_id', $clientId)
                ->where('home_id', $homeId)
                ->where('is_deleted', 0)
                ->orderBy('date', 'desc')
                ->limit(20)
                ->get(['title', 'date', 'description'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        $data['medications'] = Schema::hasTable('medication_logs')
            ? DB::table('medication_logs')
                ->where('client_id', $clientId)
                ->where('home_id', $homeId)
                ->whereNull('deleted_at')
                ->get(['medication_name', 'dosage', 'frequesncy as frequency', 'notes as reason_for_medication'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        $data['risks'] = (Schema::hasTable('su_risk') && Schema::hasTable('risk'))
            ? DB::table('su_risk')
                ->where('service_user_id', $clientId)
                ->where('home_id', $homeId)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(['risk_id', 'status', 'created_at'])
                ->map(function ($row) {
                    $risk = DB::table('risk')->where('id', $row->risk_id)->first();
                    return [
                        'risk' => $risk->description ?? ('Risk #' . $row->risk_id),
                        'status' => $row->status ?? '',
                        'date' => $row->created_at ?? '',
                    ];
                })->toArray()
            : [];

        $data['behaviour'] = Schema::hasTable('su_behavior')
            ? DB::table('su_behavior')
                ->where('service_user_id', $clientId)
                ->where('home_id', $homeId)
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(['rate', 'description'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        $data['body_maps'] = Schema::hasTable('body_map')
            ? DB::table('body_map')
                ->where('service_user_id', $clientId)
                ->where('home_id', $homeId)
                ->where('is_deleted', 0)
                ->orderBy('injury_date', 'desc')
                ->limit(10)
                ->get(['injury_type', 'injury_description', 'injury_date', 'injury_size'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        $data['dols'] = Schema::hasTable('dols')
            ? DB::table('dols')
                ->where('client_id', $clientId)
                ->where('home_id', $homeId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['dols_status', 'authorisation_type', 'reason_for_dols', 'mental_capacity_assessment'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        $data['care_tasks'] = Schema::hasTable('client_care_tasks')
            ? DB::table('client_care_tasks')
                ->where('client_id', $clientId)
                ->where('home_id', $homeId)
                ->orderBy('scheduled_date', 'desc')
                ->limit(20)
                ->get(['task_title', 'task_description', 'priority', 'frequency', 'status'])
                ->map(fn($row) => (array)$row)->toArray()
            : [];

        return $data;
    }

    private function buildTemplatePrompt(string $documentText, string $filename): array
    {
        $system = <<<'PROMPT'
You are a form digitisation expert for UK children's residential care. You convert uploaded care documents into structured, blank digital form templates.

CRITICAL: Most uploaded documents are FILLED-IN forms (assessments, reviews, plans with real client data). REVERSE-ENGINEER the blank template. Every data entry point becomes a blank fillable field. Every piece of client data proves a field exists — extract the field, discard the data.

RULES:
1. EVERY FIELD MATTERS — do NOT skip or merge fields. Go section by section through the ENTIRE document.
2. BLANK TEMPLATE — all fillable fields must be empty. No client names, dates, case numbers, addresses, phone numbers, or NHS numbers.
3. HINTS — every field MUST have a "hint" property with a short description of what to enter (e.g., "Enter the child's full name", "Select the date of the assessment").
4. REQUIRED — mark fields as required (true) if they appear critical to the form (names, dates, case numbers, signatures). Default false.
5. FIELD TYPES: text (short answer), textarea (paragraph), date, radio (Yes/No with options), checkbox (multi-select with options), select (dropdown with options), risk (Low/Medium/High with options), signature, table (with columns array and rows count), info (static text with content).
6. TABLES — when the document has a data table, create a "table" field with column headers in the "columns" array and "rows" set to 3-5. NEVER flatten tables into text.
7. REPEATED SECTIONS — if the same section repeats for multiple children/people, include it ONCE with a "text" field for the person's name at the top.
8. NEVER classify assessment forms, review forms, or care plans as "reference/policy documents". They are ALWAYS fillable.
9. COMPLETENESS — before outputting, verify every section heading from the original document has a matching section in your output.

EXAMPLE — a "Child Health Referral Form" should produce JSON like this (abbreviated):
{"formTitle":"Child Health Referral Form","formDescription":"Referral of children for health services.","sections":[{"title":"Child Information","fields":[{"id":"full_name","label":"Full Name","type":"text","required":true,"hint":"Enter the child's full name"},{"id":"date_of_birth","label":"Date of Birth","type":"date","required":true,"hint":"Select the child's date of birth"},{"id":"case_number","label":"Case Number","type":"text","required":true,"hint":"Enter the unique case number"},{"id":"gender","label":"Gender","type":"checkbox","required":true,"hint":"Select the child's gender","options":["Female","Male","Other"]},{"id":"referral_start_date","label":"Referral Start Date","type":"date","required":true,"hint":"Select the date the referral starts"}]},{"title":"Contact Information","fields":[{"id":"council_contact","label":"Council Contact","type":"text","hint":"Contact information for the council"},{"id":"telephone","label":"Telephone Number","type":"tel","hint":"Enter the contact telephone number"},{"id":"fax","label":"Fax Number","type":"tel","hint":"Enter the contact fax number"}]},{"title":"Referral Details","fields":[{"id":"reason_for_referral","label":"Reason for Referral","type":"textarea","required":true,"hint":"Provide details on the reason for referral"},{"id":"risk_assessment","label":"Risk Assessment","type":"risk","required":true,"hint":"Assess the risk level","options":["Low","Medium","High"]},{"id":"additional_notes","label":"Additional Notes","type":"textarea","hint":"Any other relevant information"},{"id":"services_needed","label":"Services Needed","type":"checkbox","hint":"Select all services required","options":["Physiotherapy","Psychology","Occupational Therapy","Speech Therapy"]},{"id":"date_referral_received","label":"Date Referral Received","type":"date","hint":"The date when the referral was received"}]}]}

Notice: every field has id, label, type, hint. Tables use "columns" and "rows". No client data anywhere.

OUTPUT: Valid JSON only. formTitle (string), formDescription (string), sections (array of {title, fields}). Each field: id (snake_case, unique), label, type. Always include: hint. Optional: required, options, columns, rows, content.
PROMPT;

        $user = "Convert the following care document into a blank digital form template. This document may contain filled-in client data — extract the STRUCTURE (field labels, sections, tables) but leave all fields BLANK.\n\n<user_input>\n--- Document: {$filename} ---\n{$documentText}\n--- End Document ---\n</user_input>\n\nGenerate a complete JSON form template covering EVERY section and field in the document. Every field needs a hint. Respond with JSON only.";

        return ['system' => $system, 'user' => $user];
    }

    private function buildFillerPrompt(array $formJson, array $clientData): array
    {
        $system = <<<'PROMPT'
You are a care home data assistant. You will receive a form template (with field labels and types) and a client's care data from multiple database tables. Your task is to match the client's data to the form fields and return values for each field.

RULES:
1. Match fields by semantic meaning, not exact label match. E.g., "Name of Young Person" matches the client's name.
2. Only fill fields where you have matching data. Leave unmatched fields as null.
3. For date fields, return dates in YYYY-MM-DD format.
4. For select/radio fields, match the closest option from the field's options array. If no option matches, return null.
5. For checkbox fields, return an array of matching option strings.
6. For table fields, return {"headers": [...], "rows": [[...], ...]} with data populated from matching records.
7. For risk fields, return "Low", "Medium", or "High" based on the data.
8. NEVER fill signature fields — return null for all signature types.
9. NEVER fill info fields — they are static labels.
10. If multiple records exist, use the most recent one for single-value fields, or populate a table with all records.
11. Return values keyed by field ID.

Respond with valid JSON only. Keys are field IDs, values are the matched data.
PROMPT;

        $clientText = $this->formatClientDataForPrompt($clientData);

        $user = "Fill this form template using the client's care data.\n\nFORM TEMPLATE:\n<user_input>\n" . json_encode($formJson, JSON_PRETTY_PRINT) . "\n</user_input>\n\nCLIENT DATA:\n<user_input>\n{$clientText}\n</user_input>\n\nReturn a JSON object where keys are field IDs and values are the matched data. Only include fields that have matching data. Respond with JSON only.";

        return ['system' => $system, 'user' => $user];
    }

    private function formatClientDataForPrompt(array $data): string
    {
        $parts = [];

        if (!empty($data['client'])) {
            $c = $data['client'];
            $parts[] = "Client Name: {$c['name']}";
            $parts[] = "Date of Birth: {$c['date_of_birth']}";
            $parts[] = "Gender: {$c['gender']}";
            if (!empty($c['phone_no'])) $parts[] = "Phone: {$c['phone_no']}";
            if (!empty($c['address'])) $parts[] = "Address: {$c['address']}";
            if (!empty($c['allergies'])) $parts[] = "Allergies: {$c['allergies']}";
            if (!empty($c['medical_notes'])) $parts[] = "Medical Notes: {$c['medical_notes']}";
            if (!empty($c['care_needs'])) $parts[] = "Care Needs: {$c['care_needs']}";
            if (!empty($c['mobility'])) $parts[] = "Mobility: {$c['mobility']}";
            if (!empty($c['emergency_contact'])) $parts[] = "Emergency Contact: {$c['emergency_contact']}";
        }

        if (!empty($data['incidents'])) {
            $parts[] = "\n--- Incident Reports (" . count($data['incidents']) . ") ---";
            foreach ($data['incidents'] as $inc) {
                $parts[] = "- [{$inc['date']}] {$inc['title']}: {$inc['description']}";
                if (!empty($inc['severity'])) $parts[] = "  Severity: {$inc['severity']}";
                if (!empty($inc['action_taken'])) $parts[] = "  Action: {$inc['action_taken']}";
            }
        }

        if (!empty($data['care_history'])) {
            $parts[] = "\n--- Care History (" . count($data['care_history']) . ") ---";
            foreach ($data['care_history'] as $ch) {
                $parts[] = "- [" . ($ch['date'] ?? '') . "] " . ($ch['title'] ?? '') . ": " . ($ch['description'] ?? '');
            }
        }

        if (!empty($data['medications'])) {
            $parts[] = "\n--- Medications (" . count($data['medications']) . ") ---";
            foreach ($data['medications'] as $med) {
                $parts[] = "- " . ($med['medication_name'] ?? '') . ": " . ($med['dosage'] ?? '') . " " . ($med['dose'] ?? '') . ", " . ($med['route'] ?? '') . ", " . ($med['frequency'] ?? '');
                if (!empty($med['reason_for_medication'])) $parts[] = "  Reason: " . $med['reason_for_medication'];
            }
        }

        if (!empty($data['risks'])) {
            $parts[] = "\n--- Risk Assessments (" . count($data['risks']) . ") ---";
            foreach ($data['risks'] as $r) {
                $parts[] = "- {$r['risk']}: Status {$r['status']} ({$r['date']})";
            }
        }

        if (!empty($data['behaviour'])) {
            $parts[] = "\n--- Behaviour Logs (" . count($data['behaviour']) . ") ---";
            foreach ($data['behaviour'] as $b) {
                $parts[] = "- Rating " . ($b['rate'] ?? '') . ": " . ($b['description'] ?? '');
            }
        }

        if (!empty($data['body_maps'])) {
            $parts[] = "\n--- Body Maps (" . count($data['body_maps']) . ") ---";
            foreach ($data['body_maps'] as $bm) {
                $parts[] = "- [" . ($bm['injury_date'] ?? '') . "] " . ($bm['injury_type'] ?? '') . ": " . ($bm['injury_description'] ?? '') . " (Size: " . ($bm['injury_size'] ?? '') . ")";
            }
        }

        if (!empty($data['dols'])) {
            $parts[] = "\n--- DoLS (" . count($data['dols']) . ") ---";
            foreach ($data['dols'] as $d) {
                $parts[] = "- Status: " . ($d['dols_status'] ?? '') . ", Type: " . ($d['authorisation_type'] ?? '');
                if (!empty($d['reason_for_dols'])) $parts[] = "  Reason: " . $d['reason_for_dols'];
            }
        }

        if (!empty($data['care_tasks'])) {
            $parts[] = "\n--- Care Tasks (" . count($data['care_tasks']) . ") ---";
            foreach ($data['care_tasks'] as $ct) {
                $parts[] = "- " . ($ct['task_title'] ?? '') . ": " . ($ct['task_description'] ?? '') . " (Priority: " . ($ct['priority'] ?? '') . ", Status: " . ($ct['status'] ?? '') . ")";
            }
        }

        $text = implode("\n", $parts);

        if (strlen($text) > 15000) {
            $text = substr($text, 0, 15000) . "\n\n[Data truncated for length]";
        }

        return $text;
    }
}
