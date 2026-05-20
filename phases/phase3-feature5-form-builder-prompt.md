# Phase 3 Feature 5 — AI Form Builder (Upload Document -> Digital Form Template + AI Form Filler)

WORKFLOW: Phase 3 Feature 5 — AI Form Builder
Run `/careos-workflow-phase3` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

**IMPORTANT:** Before starting, read `docs/logs.md` for full context on Phase 3 Features 1-4. Also read `CLAUDE.md` for project conventions, security rules, and codebase patterns.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Phase 3 Feature 5 — AI Form Builder
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — New tables, models, service, controller, routes, Blade page
[ ] BUILD — AI template generator, form renderer (12 field types), form editor, AI form filler, CRUD
[ ] TEST — Template creation, form filling, AI filler, submissions, IDOR, FULL REGRESSION
[ ] DEBUG — Real document upload + AI template generation, manual fill, AI fill with real client data
[ ] REVIEW — Adversarial curl attacks: IDOR, XSS in AI output, prompt injection, file upload attacks
[ ] AUDIT — Phase 1+2+3 grep patterns + AI output audit + form data sanitisation
[ ] PROD-READY — Upload 3 different real forms, AI fill with real client data, manual test checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Pre-Requisites (Verify Before Starting)

- [ ] **Phase 3 Features 1-4 are complete.** All shared AI infrastructure exists:
    - `app/Services/AI/OpenAIService.php` — `chat()`, `chatJson()`, `isConfigured()`
    - `app/Services/AI/PIIFilter.php` — `filter()` with `skipNames` param
    - `app/Services/AI/TokenTracker.php` — `log()`, `isCapExceeded()`, `getDailyUsage()`
    - `app/Services/AI/AIDocumentImportService.php` — `extractTextFromFile()`, `extractTextFromPdf()`, `extractTextFromDocx()`
    - `smalot/pdfparser` and `phpoffice/phpword` in composer.json
- [ ] **All 373+ tests pass:** `php -d error_reporting=0 artisan test`
- [ ] **OPENAI_API_KEY is set** in `.env`
- [ ] **Read `docs/logs.md`** for prior session context
- [ ] **Sidebar link exists:** `/roster/form-builder` already added to `roster_header.blade.php`

**Gate: All boxes checked before proceeding to SCAFFOLD.**

---

## Feature Classification

**Category: BUILD FROM SCRATCH (with AI integration)**

This is a standalone tool for digitising paper forms. It does NOT replace or connect to existing Care OS modules (incident reports, body maps, etc.). It lives at `/roster/form-builder` as its own section.

**CareRoster/FormFlow reference (UX patterns):**
- FormFlow app at `github.com/Vedang28/formflow` — Base44/React app that uploads documents, uses Gemini OCR + Claude to generate structured form JSON, renders fillable forms with 12 field types including signature canvas and editable tables. We port the concept to Laravel/jQuery.
- **Key differences from FormFlow:**
    1. FormFlow uses Base44 entities + Gemini + Claude. We use MySQL + our existing OpenAI infrastructure.
    2. FormFlow is React/shadcn. We build in jQuery/Bootstrap 3 (matching Care OS).
    3. FormFlow has no client linking. We add `client_id` on submissions for care home context.
    4. We ADD an AI Form Filler (not in FormFlow) — AI reads client data from Care OS tables and pre-fills form fields.

**Existing form builder system in Care OS:**
- `dynamic_form_builder` table (887 rows) — stores form templates with Form.io JSON in `pattern` column
- `dynamic_form` table (1198 rows) — stores filled submissions with `pattern_data` JSON, linked to `service_user_id`
- `dynamic_form_location` table (9 rows) — location tags (daily_log, incident_report, rmp, etc.)
- `DynamicFormBuilder` model at `app/DynamicFormBuilder.php`
- `DynamicForm` model at `app/DynamicForm.php`
- `FormBuilderController` at `app/Http/Controllers/backEnd/systemManage/FormBuilderController.php` — admin-side CRUD, uses `Session::get('scitsAdminSession')` for auth
- Existing routes at `/form-builder` (admin backend, NOT roster)

**CRITICAL DECISION: Reuse existing tables vs. new tables**

The existing `dynamic_form_builder` and `dynamic_form` tables use **Form.io JSON schema** (complex nested objects with `type: "textfield"`, `type: "table"`, `components` arrays, etc.). This is a completely different format from FormFlow's simpler schema (`sections[] -> fields[]` with `type: "text"`, `type: "signature"`, etc.).

**Decision: ADD COLUMNS to existing tables.** We keep the existing system working and add new capabilities:
- `dynamic_form_builder` gets: `source_filename`, `form_json` (our simpler FormFlow-style JSON), `ai_generated` flag, `status` column
- `dynamic_form` gets: no changes needed — `pattern_data` already stores JSON values, `service_user_id` already links to clients
- This way both the old admin form builder and new roster AI form builder coexist

Alternatively, if modifying existing tables is risky (887 rows of production data), create new tables:
- `form_templates` — AI-generated templates with FormFlow-style JSON
- `form_submissions` — filled instances linked to clients

**RECOMMENDED: New tables** — cleaner separation, no risk to existing 887 templates. The old system continues to work via `/admin/form-builder`. The new AI system lives at `/roster/form-builder`. They are independent.

---

## What We're Building

### Part 1: AI Form Template Creator
Upload a paper form (PDF/DOCX/DOC) → AI extracts the document structure → creates a blank, reusable digital form template. Templates are home-level (no client attached). Think of them as the care home's digital form library.

### Part 2: Form Renderer & Manual Fill
Select a template → optionally select a client → fill in the form manually → save as a submission linked to that client. All 12 field types supported: text, textarea, date, number, email, tel, select, checkbox, radio, risk rating, signature, table.

### Part 3: AI Form Filler
Select a template + a client → click "AI Fill" → AI reads the form's field labels/sections, queries relevant Care OS tables for that client's data, and pre-fills matching fields. Manager reviews, edits, and saves. The AI does **smart matching** — it figures out which tables to query based on the form's content (e.g., "Incident Report" fields → `su_incident_report`, "Medication" fields → `mar_sheets`).

### Part 4: Form Management
View all saved templates, edit templates (manual editor), delete templates, view submissions per client, edit submissions (always editable, never locked), print/download blank or filled forms.

---

## Database Schema

### New Table: `form_templates`

```sql
CREATE TABLE form_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    source_filename VARCHAR(255) NULL,           -- original uploaded filename (NULL if created manually)
    form_json LONGTEXT NOT NULL,                  -- FormFlow-style JSON schema (sections + fields)
    status VARCHAR(20) NOT NULL DEFAULT 'published',  -- draft | published | archived
    ai_generated TINYINT(1) NOT NULL DEFAULT 0,   -- 1 if created by AI, 0 if manual
    created_by INT UNSIGNED NOT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_home_id (home_id),
    INDEX idx_status (status)
);
```

### New Table: `form_submissions`

```sql
CREATE TABLE form_submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_id INT UNSIGNED NOT NULL,
    form_template_id INT UNSIGNED NOT NULL,       -- FK to form_templates.id
    client_id INT UNSIGNED NULL,                   -- FK to service_user.id (NULL if not client-specific)
    form_title VARCHAR(255) NOT NULL,              -- snapshot of template title at time of submission
    values_json LONGTEXT NOT NULL,                 -- JSON: { field_id: value } for all fields
    submitted_by INT UNSIGNED NOT NULL,            -- user who filled/submitted
    submitted_by_name VARCHAR(100) NULL,           -- display name of submitter
    ai_filled TINYINT(1) NOT NULL DEFAULT 0,       -- 1 if AI was used to pre-fill
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    INDEX idx_home_id (home_id),
    INDEX idx_template (form_template_id),
    INDEX idx_client (client_id)
);
```

### `form_json` Schema (stored in `form_templates.form_json`)

```json
{
    "formTitle": "Risk Management Plan",
    "formDescription": "Template for documenting and managing identified risks",
    "sections": [
        {
            "title": "Client Information",
            "fields": [
                {
                    "id": "client_name",
                    "label": "Name of Young Person",
                    "type": "text",
                    "required": true,
                    "hint": ""
                },
                {
                    "id": "date_created",
                    "label": "Date Created",
                    "type": "date",
                    "required": true,
                    "hint": ""
                },
                {
                    "id": "risk_level",
                    "label": "Overall Risk Level",
                    "type": "risk",
                    "required": true,
                    "options": ["Low", "Medium", "High"]
                },
                {
                    "id": "risk_details_table",
                    "label": "Risk Details",
                    "type": "table",
                    "columns": ["Risk Type", "Description", "Control Measures", "Review Date"],
                    "rows": 3
                },
                {
                    "id": "manager_signature",
                    "label": "Manager Signature",
                    "type": "signature",
                    "required": true
                }
            ]
        }
    ]
}
```

### 12 Supported Field Types

| Type | Description | Value Format | Wide? |
|------|-------------|-------------|-------|
| `text` | Single-line text input | `"string"` | No |
| `textarea` | Multi-line text | `"string"` | Yes |
| `date` | Date picker | `"YYYY-MM-DD"` | No |
| `number` | Numeric input | `"123"` | No |
| `email` | Email input | `"email@example.com"` | No |
| `tel` | Phone number | `"07700123456"` | No |
| `select` | Dropdown | `"selected_option"` | No |
| `checkbox` | Multi-select checkboxes | `["opt1", "opt2"]` | Yes |
| `radio` | Single-select radio | `"selected_option"` | Yes |
| `risk` | Color-coded risk rating (Low/Medium/High) | `"Low"` or `"Medium"` or `"High"` | Yes |
| `signature` | Canvas-based signature capture | `"data:image/png;base64,..."` | Yes |
| `table` | Editable grid with dynamic columns/rows | `{"headers":[],"rows":[[]]}` | Yes |
| `info` | Static text label (not fillable) | N/A (not counted in progress) | Yes |

---

## AI Prompt Designs

### Prompt 1: Document → Form Template (Template Creator)

**System Prompt:**
```
You are a form digitisation expert working in a UK children's residential care setting. Your task is to analyse an uploaded care document and convert it into a structured, blank digital form template.

RULES:

BLANK vs STATIC CONTENT:
1. FILLABLE fields must be BLANK — no personal data, no pre-filled values. Strip out any client-specific data (names, DOB, addresses, phone numbers, case numbers) from fillable fields. This creates a reusable template.
2. PRESERVE all non-client-specific text exactly as written: terms & conditions, legal clauses, policy statements, care guidelines, disclaimers, instructions, NB notes, procedural text, definitions, legislative references (Children Act 1989, COSHH Regulations, etc.), quality standards lists, risk matrices, and any other static content that is part of the form itself. These become type "info" fields with the full original text in the "content" property.
3. The distinction is simple: if text is ABOUT a specific client (their name, their data), strip it. If text is part of the FORM ITSELF (instructions, terms, policies, guidelines, reference tables), keep it as "info".
4. If the document is purely a policy/procedure document with NO fillable fields at all (e.g., a Missing From Care policy, staff training essay), flag it by setting formDescription to "NOTE: This document appears to be a reference/policy document, not a fillable form. It has been converted to read-only sections." and make all fields "info" type.

STRUCTURE:
5. Every section, heading, table, signature block, and note in the document MUST appear in the form.
6. Use generic formTitle and formDescription — no personal identifiers (e.g., "Risk Management Plan" not "John Smith's Risk Management Plan").
7. Group related fields into sections. Each section needs a title. If the document uses time-based grouping (e.g., "Pre admission", "Day 1", "By Day 2"), preserve those as section titles.
8. Preserve the document's logical order — sections should appear in the same sequence as the original.
9. Generate unique field IDs using snake_case labels (e.g., "client_name", "date_of_birth", "risk_level_1").

FIELD TYPES:
10. Table rows in the document become type "table" with column headers extracted from the table. Common patterns: Yes/No/NA/Comments grids, log tables (Date/Event/Action/Outcome), inspection checklists, sign-off grids.
11. Signature blocks become type "signature". Multi-party sign-off sections (Staff + Manager + Social Worker + Parent) should have SEPARATE signature fields for each party, with labels indicating the role (e.g., "Staff Signature", "Manager Signature").
12. For Yes/No or Yes/No/N/A fields, use "radio" type with options ["Yes", "No"] or ["Yes", "No", "N/A"].
13. For risk-related fields with Low/Medium/High options, use "risk" type.
14. For select/radio/checkbox fields, extract the options from the document. If the document shows tick boxes or checkboxes, use "checkbox" type.
15. Choose the most appropriate field type for each blank space. Use "textarea" for narrative/description fields that expect multiple sentences.
16. Mark fields as required if the document indicates they are mandatory (asterisk, "required", etc.). Default to false.
17. Embedded reference tables (e.g., 4x4 risk probability/consequence matrices) should be preserved as "info" type with the matrix formatted as readable text in the "content" property — do NOT make them editable tables.

REAL-WORLD DOCUMENT EXAMPLES:
- Admission Checklists: time-sectioned Yes/No grids ("Pre admission", "Day 1", "By Day 7")
- Risk Management Plans: header fields + risk matrix (static) + narrative sections + log table + multi-party sign-off
- Behaviour Management Plans: header table + narrative sections + repeating log table + action plan + sign-off
- Fire Safety Checklists: multi-section Yes/No/NA grids + static procedural instructions + multiple inspection tables
- COSHH Risk Assessments: product details + hazard/control tables with risk ratings + legislative references
- Advocacy Records: simple header + large narrative area + quality standards list (static) + sign-off
- Delegated Authority Forms: categorised consent items with delegation assignments + multi-party signatures + conditional footer text

You MUST respond with valid JSON matching the provided schema. Do not include any text outside the JSON.
```

**User Prompt:**
```
Convert the following care document into a blank digital form template.

<user_input>
--- Document: {filename} ---
{extracted_text}
--- End Document ---
</user_input>

Generate a JSON form template with sections and fields. Every field must be blank (no values). Respond with JSON only.
```

**Response JSON Schema (for `response_format`):**
```json
{
    "formTitle": "string",
    "formDescription": "string",
    "sections": [
        {
            "title": "string",
            "fields": [
                {
                    "id": "string",
                    "label": "string",
                    "type": "text|textarea|date|number|email|tel|select|checkbox|radio|risk|signature|table|info",
                    "required": "boolean",
                    "hint": "string (optional)",
                    "options": ["string array — for select/checkbox/radio/risk"],
                    "columns": ["string array — for table type"],
                    "rows": "number — for table type",
                    "content": "string — for info type (static text)"
                }
            ]
        }
    ]
}
```

### Prompt 2: AI Form Filler (Smart Matching)

**System Prompt:**
```
You are a care home data assistant. You will receive a form template (with field labels and types) and a client's care data from multiple database tables. Your task is to match the client's data to the form fields and return values for each field.

RULES:
1. Match fields by semantic meaning, not exact label match. E.g., "Name of Young Person" matches the client's name, "Date of Incident" matches incident dates.
2. Only fill fields where you have matching data. Leave unmatched fields as null.
3. For date fields, return dates in YYYY-MM-DD format.
4. For select/radio fields, match the closest option from the field's options array. If no option matches, return null.
5. For checkbox fields, return an array of matching option strings.
6. For table fields, return {"headers": [...], "rows": [[...], ...]} with data populated from matching records.
7. For risk fields, return "Low", "Medium", or "High" based on the data.
8. NEVER fill signature fields — return null for all signature types.
9. NEVER fill info fields — they are static labels.
10. If multiple records exist (e.g., multiple incidents), use the most recent one for single-value fields, or populate a table with all records.
11. Return values keyed by field ID.

You MUST respond with valid JSON. Do not include any text outside the JSON.
```

**User Prompt:**
```
Fill this form template using the client's care data.

FORM TEMPLATE:
<user_input>
{form_json with sections and fields}
</user_input>

CLIENT DATA:
<user_input>
Client Name: {name}
Date of Birth: {dob}
Gender: {gender}

--- Incident Reports ({count}) ---
{incident data}

--- Care History ({count}) ---
{care history data}

--- Medications ({count}) ---
{medication data}

--- Risk Assessments ({count}) ---
{risk data}

--- Behaviour Logs ({count}) ---
{behaviour data}

--- Body Map ({count}) ---
{body map data}

--- DoLS ({count}) ---
{dols data}

--- Care Tasks ({count}) ---
{care task data}
</user_input>

Return a JSON object where keys are field IDs and values are the matched data. Only include fields that have matching data. Respond with JSON only.
```

**Response format:**
```json
{
    "client_name": "Susanna Rose Craven",
    "date_of_birth": "2018-03-15",
    "risk_level": "High",
    "incident_date": "2025-11-15",
    "incident_description": "Verbal altercation with another resident...",
    "medication_table": {
        "headers": ["Medication", "Dosage", "Frequency", "Route"],
        "rows": [
            ["Salbutamol", "100mcg", "As required", "Inhaled"],
            ["Melatonin", "2mg", "Once daily", "Oral"]
        ]
    }
}
```

---

## Care OS Tables for AI Form Filler (Smart Matching)

When "AI Fill" is clicked, the service queries these tables for the selected client:

| Table | Query | Data Provided to AI |
|-------|-------|-------------------|
| `service_user` | `WHERE id = {client_id}` | Name, DOB, gender, phone, address, allergies, medical notes, care needs, mobility, emergency contact |
| `su_incident_report` | `WHERE service_user_id = {client_id} AND home_id = {home_id} AND is_deleted = 0 ORDER BY date DESC LIMIT 20` | Title, date, formdata (JSON) |
| `su_care_history` | `WHERE service_user_id = {client_id} AND home_id = {home_id} AND is_deleted = 0 ORDER BY date DESC LIMIT 20` | Title, date, description |
| `mar_sheets` | `WHERE client_id = {client_id} AND home_id = {home_id} AND is_deleted = 0` | medication_name, dosage, dose, route, frequency, reason_for_medication |
| `su_risk` | `WHERE service_user_id = {client_id} AND home_id = {home_id} ORDER BY created_at DESC LIMIT 20` | risk_id (join `risk` for description), status |
| `su_behavior` | `WHERE service_user_id = {client_id} AND home_id = {home_id} AND is_deleted = 0 ORDER BY created_at DESC LIMIT 20` | rate, description |
| `body_map` | `WHERE service_user_id = {client_id} AND home_id = {home_id} AND is_deleted = 0 ORDER BY injury_date DESC LIMIT 10` | injury_type, injury_description, injury_date, injury_size |
| `dols` | `WHERE client_id = {client_id} AND home_id = {home_id} ORDER BY created_at DESC LIMIT 5` | dols_status, authorisation_type, reason_for_dols, mental_capacity_assessment |
| `client_care_tasks` | `WHERE client_id = {client_id} AND home_id = {home_id} ORDER BY scheduled_date DESC LIMIT 20` | task_title, task_description, priority, frequency, status |

**PII handling:** Client data is sent to OpenAI for form filling. Apply `PIIFilter::filter()` with `skipNames: true` (names needed for matching). Log prompt hash to `ai_usage_logs`.

**Token management:** Form filling may use significant tokens (form schema + client data). Use `quality_model` (gpt-4o) for accuracy. Estimate ~2000-4000 tokens per fill request. Check daily cap before calling.

---

## API Endpoints

### Controller: `app/Http/Controllers/frontEnd/Roster/FormBuilderController.php`

**NOTE:** This is a NEW controller in the roster namespace, separate from the existing `backEnd/systemManage/FormBuilderController.php`.

| Method | Route | Purpose | Throttle |
|--------|-------|---------|----------|
| `index()` | GET `/roster/form-builder` | Main page — list templates + saved forms | — |
| `uploadAndGenerate()` | POST `/roster/form-builder/upload` | Upload document + AI generates template | throttle:10,1 |
| `storeTemplate()` | POST `/roster/form-builder/template` | Save manually created/edited template | throttle:30,1 |
| `updateTemplate()` | POST `/roster/form-builder/template/{id}` | Update existing template | throttle:30,1 |
| `deleteTemplate()` | POST `/roster/form-builder/template/{id}/delete` | Soft delete template | throttle:20,1 |
| `getTemplate()` | GET `/roster/form-builder/template/{id}` | Get template JSON for rendering | — |
| `fillForm()` | GET `/roster/form-builder/fill/{templateId}` | Open form for filling (optionally with client) | — |
| `aiFill()` | POST `/roster/form-builder/ai-fill` | AI fills form using client data | throttle:10,1 |
| `saveSubmission()` | POST `/roster/form-builder/submission` | Save filled form submission | throttle:30,1 |
| `updateSubmission()` | POST `/roster/form-builder/submission/{id}` | Update existing submission | throttle:30,1 |
| `deleteSubmission()` | POST `/roster/form-builder/submission/{id}/delete` | Soft delete submission | throttle:20,1 |
| `getSubmission()` | GET `/roster/form-builder/submission/{id}` | Get submission data for editing | — |
| `clientSubmissions()` | GET `/roster/form-builder/client/{clientId}/submissions` | List all submissions for a client | — |

### Route Definitions

```php
// In routes/web.php — Form Builder routes
Route::get('/form-builder', [RosterFormBuilderController::class, 'index']);
Route::post('/form-builder/upload', [RosterFormBuilderController::class, 'uploadAndGenerate'])->middleware('throttle:10,1');
Route::post('/form-builder/template', [RosterFormBuilderController::class, 'storeTemplate'])->middleware('throttle:30,1');
Route::post('/form-builder/template/{id}', [RosterFormBuilderController::class, 'updateTemplate'])->middleware('throttle:30,1')->where('id', '[0-9]+');
Route::post('/form-builder/template/{id}/delete', [RosterFormBuilderController::class, 'deleteTemplate'])->middleware('throttle:20,1')->where('id', '[0-9]+');
Route::get('/form-builder/template/{id}', [RosterFormBuilderController::class, 'getTemplate'])->where('id', '[0-9]+');
Route::get('/form-builder/fill/{templateId}', [RosterFormBuilderController::class, 'fillForm'])->where('templateId', '[0-9]+');
Route::post('/form-builder/ai-fill', [RosterFormBuilderController::class, 'aiFill'])->middleware('throttle:10,1');
Route::post('/form-builder/submission', [RosterFormBuilderController::class, 'saveSubmission'])->middleware('throttle:30,1');
Route::post('/form-builder/submission/{id}', [RosterFormBuilderController::class, 'updateSubmission'])->middleware('throttle:30,1')->where('id', '[0-9]+');
Route::post('/form-builder/submission/{id}/delete', [RosterFormBuilderController::class, 'deleteSubmission'])->middleware('throttle:20,1')->where('id', '[0-9]+');
Route::get('/form-builder/submission/{id}', [RosterFormBuilderController::class, 'getSubmission'])->where('id', '[0-9]+');
Route::get('/form-builder/client/{clientId}/submissions', [RosterFormBuilderController::class, 'clientSubmissions'])->where('clientId', '[0-9]+');
```

### checkUserAuth whitelist additions:
```php
array_push($allowed_path,
    'roster/form-builder',
    'roster/form-builder/upload',
    'roster/form-builder/template',
    'roster/form-builder/template/',    // for /template/{id} after digit stripping
    'roster/form-builder/fill/',        // for /fill/{id} after digit stripping
    'roster/form-builder/ai-fill',
    'roster/form-builder/submission',
    'roster/form-builder/submission/',   // for /submission/{id} after digit stripping
    'roster/form-builder/client/'       // for /client/{id}/submissions after digit stripping
);
```

---

## Service Layer

### `app/Services/AI/FormBuilderService.php`

```php
class FormBuilderService
{
    // Dependencies: OpenAIService, PIIFilter, TokenTracker, AIDocumentImportService (text extraction)

    // ── Template Creation ──

    public function generateTemplateFromDocument(string $filePath, string $filename, int $homeId, int $userId): array
    // 1. Extract text from file (reuse AIDocumentImportService::extractTextFromFile())
    // 2. PII filter the text
    // 3. Build template generation prompt
    // 4. Call OpenAIService::chatJson() with quality_model
    // 5. Validate response has sections + fields
    // 6. Save to form_templates table
    // 7. Log to ai_usage_logs
    // Returns: ['template_id' => int, 'form_json' => array]

    public function validateFormJson(array $formJson): bool
    // Validates formTitle, sections array, fields have id/label/type
    // Validates field types are in the 12 allowed types
    // Validates options exist for select/checkbox/radio/risk types
    // Validates columns exist for table type

    // ── AI Form Filler ──

    public function aiFillForm(int $templateId, int $clientId, int $homeId, int $userId): array
    // 1. Load template's form_json
    // 2. Load client data from all relevant tables (see table above)
    // 3. PII filter client data (skipNames: true)
    // 4. Build filler prompt (form schema + client data)
    // 5. Call OpenAIService::chatJson() with quality_model
    // 6. Validate response keys match field IDs
    // 7. Log to ai_usage_logs
    // Returns: ['values' => array, 'filled_count' => int, 'total_fields' => int]

    private function gatherClientData(int $clientId, int $homeId): array
    // Queries all 9 tables listed above
    // Returns structured array with client profile + all care data
    // Truncates to ~15000 chars total if data is very large

    private function buildTemplatePrompt(string $documentText, string $filename): array
    // Returns ['system' => string, 'user' => string] for template generation

    private function buildFillerPrompt(array $formJson, array $clientData): array
    // Returns ['system' => string, 'user' => string] for form filling
}
```

---

## UI Design

### Page: `/roster/form-builder` (Main Page)

The page has 3 tabs:

```
┌──────────────────────────────────────────────────────────────────────────┐
│  Form Builder                                                           │
│                                                                          │
│  [Templates]  [Saved Forms]  [Create New]                                │
│  ─────────────────────────────────────────                               │
│                                                                          │
│  ┌─ Upload & Generate ──────────────────────────────────────────────┐   │
│  │  Upload a paper form to create a digital template                 │   │
│  │  ┌─────────────────────────────────────────────────────────┐     │   │
│  │  │     Drag & drop a PDF or Word document here              │     │   │
│  │  │     or click to browse                                   │     │   │
│  │  │     PDF or Word (.docx) - Max 10MB                       │     │   │
│  │  └─────────────────────────────────────────────────────────┘     │   │
│  │  [Generate Template]                                              │   │
│  └───────────────────────────────────────────────────────────────────┘   │
│                                                                          │
│  ┌─ Your Templates ─────────────────────────────────────────────────┐   │
│  │                                                                   │   │
│  │  ┌──────────────────┐  ┌──────────────────┐  ┌────────────────┐ │   │
│  │  │ Risk Management  │  │ Incident Report  │  │ Substance Use  │ │   │
│  │  │ Plan             │  │                  │  │ Form           │ │   │
│  │  │ 4 sections       │  │ 3 sections       │  │ 5 sections     │ │   │
│  │  │ 18 fields        │  │ 12 fields        │  │ 22 fields      │ │   │
│  │  │ AI Generated     │  │ AI Generated     │  │ Manual         │ │   │
│  │  │ ──────────────── │  │ ──────────────── │  │ ────────────── │ │   │
│  │  │ [Fill] [Edit]    │  │ [Fill] [Edit]    │  │ [Fill] [Edit]  │ │   │
│  │  │ [Print] [Delete] │  │ [Print] [Delete] │  │ [Print] [Del]  │ │   │
│  │  └──────────────────┘  └──────────────────┘  └────────────────┘ │   │
│  │                                                                   │   │
│  └───────────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────────┘
```

### Tab 2: Saved Forms (Submissions)

```
┌─ Saved Forms ────────────────────────────────────────────────────────┐
│  Filter: [All Clients ▼]  [All Templates ▼]                          │
│                                                                       │
│  ┌────────────────────────────────────────────────────────────────┐  │
│  │ Risk Management Plan — Susanna Craven                          │  │
│  │ Filled by: Komal Gautam  |  10 May 2026  |  AI Filled         │  │
│  │ [View/Edit]  [Print]  [Download]  [Delete]                     │  │
│  ├────────────────────────────────────────────────────────────────┤  │
│  │ Incident Report — Jake Thompson                                │  │
│  │ Filled by: Komal Gautam  |  09 May 2026  |  Manual             │  │
│  │ [View/Edit]  [Print]  [Download]  [Delete]                     │  │
│  └────────────────────────────────────────────────────────────────┘  │
└───────────────────────────────────────────────────────────────────────┘
```

### Tab 3: Create New (Manual Form Editor)

```
┌─ Create New Template ────────────────────────────────────────────────┐
│                                                                       │
│  Form Title:  [____________________________]                          │
│  Description: [____________________________]                          │
│                                                                       │
│  ┌─ Section 1: [Client Information________] ──────────────────────┐ │
│  │                                                                 │ │
│  │  Label: [Name___________]  Type: [text ▼]  Required: [✓]      │ │
│  │  Label: [Date of Birth__]  Type: [date ▼]  Required: [✓]      │ │
│  │  Label: [Risk Level_____]  Type: [risk ▼]  Required: [ ]      │ │
│  │                                                                 │ │
│  │  [+ Add Field]                                                  │ │
│  └─────────────────────────────────────────────────────────────────┘ │
│                                                                       │
│  [+ Add Section]                                                      │
│                                                                       │
│  [Preview]  [Save Template]                                           │
└───────────────────────────────────────────────────────────────────────┘
```

### Form Fill Page (opens when clicking "Fill" on a template)

```
┌──────────────────────────────────────────────────────────────────────┐
│  Risk Management Plan                              Progress: ██░░ 40% │
│  Template for documenting and managing identified risks               │
│                                                                        │
│  Client: [Select Client ▼]     [AI Fill]                              │
│                                                                        │
│  ── Section 1: Client Information ─────────────────────────────────── │
│                                                                        │
│  Name of Young Person:        Date Created:                            │
│  [Susanna Craven_________]    [2026-05-10________]                    │
│                                                                        │
│  Overall Risk Level:                                                   │
│  [Low] [Medium] [HIGH]  ← High is selected, colored red               │
│                                                                        │
│  Risk Details:                                                         │
│  ┌──────────────┬─────────────┬──────────────────┬────────────┐      │
│  │ Risk Type    │ Description │ Control Measures │ Review Date │      │
│  ├──────────────┼─────────────┼──────────────────┼────────────┤      │
│  │ [Absconding] │ [History of]│ [1:1 supervision]│ [2026-06]  │      │
│  │ [Self-harm ] │ [Previous  ]│ [Remove sharps  ]│ [2026-06]  │      │
│  │ [__________] │ [__________]│ [______________] │ [________] │      │
│  └──────────────┴─────────────┴──────────────────┴────────────┘      │
│  [+ Add Row]                                                          │
│                                                                        │
│  ── Section 2: Signatures ─────────────────────────────────────────── │
│                                                                        │
│  Manager Signature:                                                    │
│  ┌─────────────────────────────────────────┐                          │
│  │          [signature canvas]              │                          │
│  │                                          │                          │
│  └─────────────────────────────────────────┘                          │
│  [Clear Signature]                                                     │
│                                                                        │
│  [Save Form]  [Print Blank]  [Print Filled]  [Download]               │
└──────────────────────────────────────────────────────────────────────┘
```

---

## File Structure

### Files to Create

| File | Lines (est.) | Purpose |
|------|-------------|---------|
| `app/Services/AI/FormBuilderService.php` | ~400 | AI template generation, AI form filler, client data gathering |
| `app/Http/Controllers/frontEnd/Roster/RosterFormBuilderController.php` | ~350 | 13 endpoints for templates, submissions, AI fill |
| `app/Models/FormTemplate.php` | ~40 | Eloquent model with scopes |
| `app/Models/FormSubmission.php` | ~40 | Eloquent model with scopes + client relationship |
| `resources/views/frontEnd/roster/form-builder/index.blade.php` | ~200 | Main page with 3 tabs |
| `public/js/roster/form-builder.js` | ~600 | Template management, upload, CRUD |
| `public/js/roster/form-renderer.js` | ~500 | Form renderer (12 field types), AI fill, save/print/download |
| `public/js/roster/form-editor.js` | ~400 | Manual form editor (add sections, fields, configure types) |
| `tests/Feature/FormBuilderTest.php` | ~500 | 25+ tests |

### Files to Modify

| File | Change |
|------|--------|
| `routes/web.php` | Add 13 new routes |
| `app/Http/Middleware/checkUserAuth.php` | Whitelist form-builder paths |

---

## User Flows (Complete)

### Flow 1: Upload Document → AI Template
1. Manager goes to `/roster/form-builder`
2. Drops a PDF/DOCX into the upload zone
3. Clicks "Generate Template"
4. Loading: "Extracting text..." → "Generating form structure..."
5. AI returns form JSON → saved as `form_templates` record
6. Template card appears in the grid
7. Manager can click "Fill" to start using it immediately

### Flow 2: Manual Template Creation
1. Manager clicks "Create New" tab
2. Adds form title, description
3. Adds sections, adds fields to each section
4. Configures field types, options, required flags
5. Clicks "Preview" to see the rendered form
6. Clicks "Save Template" → saved to `form_templates`

### Flow 3: Fill Form Manually
1. Manager clicks "Fill" on a template card
2. Form renderer opens with all blank fields
3. Manager selects a client from dropdown (optional)
4. Fills in fields manually
5. Progress bar updates as fields are filled
6. Clicks "Save Form" → saved to `form_submissions` with `client_id`

### Flow 4: AI Fill Form
1. Manager clicks "Fill" on a template card
2. Selects a client from dropdown (REQUIRED for AI fill)
3. Clicks "AI Fill" button
4. Loading: "Reading client data..." → "Matching fields..."
5. AI returns values → form fields populate with data
6. Manager reviews, edits any values
7. Clicks "Save Form" → saved to `form_submissions` with `ai_filled = 1`

### Flow 5: Edit Saved Submission
1. Manager goes to "Saved Forms" tab
2. Clicks "View/Edit" on a submission
3. Form renderer opens with existing values populated
4. Manager edits any fields (submissions are NEVER locked)
5. Clicks "Save" → updates existing `form_submissions` record

### Flow 6: Print / Download
1. From form renderer (blank or filled), click "Print Blank" or "Print Filled"
2. Opens print-ready HTML in new window
3. Or click "Download" for .html file download

---

## Tests (25+ tests)

### Template Tests
1. Upload PDF → template created with correct form_json
2. Upload DOCX → template created
3. Upload invalid file type → 422
4. Upload oversized file → 422
5. Upload without auth → redirect
6. Create manual template → saved correctly
7. Update template → changes persisted
8. Delete template → is_deleted = 1
9. IDOR: access template from different home → 404/403

### Form Fill Tests
10. Save submission with client_id → saved correctly
11. Save submission without client_id → saved correctly
12. Update existing submission → changes persisted
13. Delete submission → is_deleted = 1
14. Get submission → returns correct values_json
15. Client submissions → returns only that client's forms

### AI Fill Tests (mock OpenAI)
16. AI fill with valid client data → fields populated correctly
17. AI fill without selecting client → 422
18. AI fill with client having no data → empty values returned
19. Token cap exceeded → "daily limit reached"
20. AI not configured → graceful error

### Security Tests
21. IDOR: fill form for client in different home → rejected
22. Mass assignment: home_id from request body ignored
23. XSS in form field values → escaped in renderer
24. CSRF required on all POST endpoints
25. Rate limiting enforced

### Full Regression
26. All existing tests pass (373+)

---

## Security Checklist

- [ ] All POST routes have CSRF protection
- [ ] All routes have auth middleware
- [ ] Every query filters by `home_id` from authenticated user
- [ ] File upload: server-side MIME validation (PDF/DOCX only)
- [ ] File upload: max 10MB
- [ ] Files stored in `storage/app/private/form-uploads/` (not public)
- [ ] AI output escaped: `{{ }}` in Blade, `esc()` in JS
- [ ] Prompt injection defence: user text wrapped in `<user_input>` tags
- [ ] PII filter applied before sending client data to OpenAI
- [ ] Token tracking on all AI calls
- [ ] Daily token cap checked before AI calls
- [ ] No `DB::raw()` with user input
- [ ] No `{!! !!}` in Blade for user data
- [ ] Models use `$fillable` whitelist
- [ ] Route constraints: `->where('id', '[0-9]+')` on all parameterised routes
- [ ] IDOR prevention: every endpoint verifies record's home_id matches user
- [ ] Submissions always editable (no locked state)
- [ ] Signature data stored as base64 in values_json (not as separate files)
- [ ] Form JSON validated before saving (valid types, valid structure)

---

## Estimated Build Time: 8 hours

| Stage | Time |
|-------|------|
| PLAN (review + approve) | 15 min |
| SCAFFOLD (tables + models + controller + service + routes) | 30 min |
| BUILD — AI template generator | 1.5 hours |
| BUILD — Form renderer (12 field types + signature + tables) | 2 hours |
| BUILD — Form editor (manual creation) | 1 hour |
| BUILD — AI form filler | 1.5 hours |
| BUILD — CRUD + print/download | 30 min |
| TEST (25+ tests) | 1 hour |
| DEBUG + REVIEW + AUDIT | 30 min |
| PROD-READY | 30 min |

This is the largest Phase 3 feature because it has 3 major JS components (renderer, editor, builder page) and 2 AI integrations (template generation + form filling). The AI infrastructure and file upload patterns are fully reusable from Features 1-4.
