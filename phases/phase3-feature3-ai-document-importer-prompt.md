# Phase 3 Feature 3 — AI Document Importer (PDF → Client Data)

WORKFLOW: Phase 3 Feature 3 — AI Document Importer
Run `/careos-workflow-phase3` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

**IMPORTANT:** Before starting, read `docs/logs.md` for full context on Phase 3 Features 1-2. Also read `CLAUDE.md` for project conventions, security rules, and codebase patterns.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Phase 3 Feature 3 — AI Document Importer (PDF → Client Data)
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Create 1 new table (ai_document_imports), new service class, controller, routes
[ ] BUILD — PDF upload, text extraction, AI data extraction, review/confirm UI, import to target tables
[ ] TEST — File upload security tests, AI mock tests, PII filter tests, IDOR, malicious file tests, FULL REGRESSION
[ ] DEBUG — Real PDF upload + AI extraction test, check ai_usage_logs for feature='document_import', verify imported data in target tables
[ ] REVIEW — Adversarial curl attacks: malicious file upload, prompt injection via PDF content, IDOR across homes, XSS in extracted data
[ ] AUDIT — Phase 1+2+3F1-F2 grep patterns + file upload security audit + AI output rendering audit
[ ] PROD-READY — AI quality check (import 3 different PDF types), manual test checklist, user confirms "tested"
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Pre-Requisites (Verify Before Starting)

- [ ] **Phase 3 Features 1-2 are complete:** All shared AI infrastructure exists and tests pass:
    - `config/ai.php` — AI configuration (default_model=gpt-4o-mini, quality_model=gpt-4o)
    - `app/Services/AI/OpenAIService.php` — `chat()`, `chatJson()`, `isConfigured()`
    - `app/Services/AI/PIIFilter.php` — `filter()` with `skipNames` param, `filterClientData()`
    - `app/Services/AI/TokenTracker.php` — `log()`, `isCapExceeded()`, `getDailyUsage()`
    - `app/Services/AI/PromptBuilder.php` — `buildClientContext()`, `buildCarePlanGenerationPrompt()`, `collectAssessmentData()`
    - `app/Services/AI/AICarePlanService.php` — Feature 2's care plan generation service
    - DB tables: `ai_chat_sessions`, `ai_chat_messages`, `ai_usage_logs`, `ai_care_plans`
- [ ] **All tests pass:** `php -d error_reporting=0 artisan test` — no regressions
- [ ] **OPENAI_API_KEY is set** in `.env`
- [ ] **Read `docs/logs.md`** for prior session context (Logs 21-25 cover Features 1-2)

**Gate: All boxes checked before proceeding to SCAFFOLD.**

---

## Feature Classification

**Category: BUILD FROM SCRATCH** — The current client details page (`/roster/client-details/{id}`) has a Documents tab (tab button at line 286 of `client_details.blade.php`) with hardcoded placeholder UI showing two static "AI Care Plan" document cards and a non-functional upload form. There's also an "Import Documents" button in the page header (line 53) that does nothing. None of this is wired to any backend. We are building AI-powered PDF document import that extracts structured data and populates existing database tables.

**CareRoster reference (UX patterns only):**

- `AIDocumentImporter.jsx` (~500 lines) — Upload → select import types → AI extraction via `InvokeLLM` with `file_urls` → review extracted data in expandable cards → confirm import to individual entity tables. We adopt the same upload → extract → review → import pattern but build it as a Laravel service with server-side PDF text extraction + OpenAI JSON output.
- `AINewClientImporter.jsx` — Multi-file variant that also creates a new client record. Out of scope for this feature (we import to existing clients only).

**Key difference from CareRoster:** CareRoster sends the PDF file URL directly to Base44's LLM (which handles file reading). We can't do that with OpenAI's chat API — it accepts text, not files. So we must:
1. Extract text from the PDF server-side (using `smalot/pdfparser` or similar PHP library)
2. Send the extracted text to OpenAI for structured data extraction

## What Exists

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AI infrastructure (OpenAIService,     │ EXISTS   │ Built in Feature 1-2. Reuse OpenAIService::chatJson() for   │
│ PIIFilter, TokenTracker, PromptBuilder│          │ structured extraction output. Use quality_model (gpt-4o).   │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ai_usage_logs table                   │ EXISTS   │ Feature 1 created this. Feature 3 logs with feature=        │
│                                       │          │ 'document_import'. TokenTracker already supports this.      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Documents tab in client details       │ EXISTS   │ Tab button at line 286 of client_details.blade.php.         │
│                                       │          │ Content is HARDCODED placeholder (lines 3490-3686). Shows   │
│                                       │          │ static upload form and two fake document cards.              │
│                                       │          │ Replace with dynamic AI-powered import flow.                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ "Import Documents" button in header   │ EXISTS   │ Line 53 of client_details.blade.php. Currently does nothing.│
│                                       │          │ Wire this to open the AI Document Import modal.             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ su_file_manager table                 │ EXISTS   │ 289 rows (8 in home 8). Stores uploaded files per client.   │
│                                       │          │ Cols: home_id, service_user_id, category_id, file.          │
│                                       │          │ Has is_deleted flag. Model: app/FileManager.php             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ file_category table                   │ EXISTS   │ 10 rows. Categories: medical, educational, academic,        │
│                                       │          │ Reports, Money Request, etc. Cols: id, name, is_deleted.    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ su_care_history_file table            │ EXISTS   │ 19 rows. File attachments for care history records.         │
│                                       │          │ Cols: su_care_history_id, file. No home_id column.          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ service_user table                    │ EXISTS   │ 17 clients in home 8. Target for updating client profile    │
│                                       │          │ fields: allergies, medical_notes, care_needs, etc.          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ su_care_history table                 │ EXISTS   │ 4 rows in home 8. Target for imported care history entries.  │
│                                       │          │ Cols: home_id, service_user_id, title, date, description.   │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ mar_sheets table                      │ EXISTS   │ 11 active in home 8. Target for imported medications.       │
│                                       │          │ Cols: medication_name, dosage, dose, route, frequency, etc. │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ su_risk + risk tables                 │ EXISTS   │ 57 risk assessments in home 8. Target for imported risks.   │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ barryvdh/laravel-dompdf               │ EXISTS   │ Already in composer.json. This is for PDF generation, NOT   │
│                                       │          │ parsing. We need a SEPARATE library for PDF text extraction. │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ai_document_imports table             │ MISSING  │ Need audit trail table for document imports.                 │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AIDocumentImportService               │ MISSING  │ Need service class for extraction + import logic.           │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AIDocumentImportController + routes   │ MISSING  │ Need controller for upload, extract, confirm import.        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ PDF text extraction library           │ MISSING  │ Need smalot/pdfparser or similar. See dependency section.    │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Building

An **AI Document Importer** — accessible from the client details page via the "Import Documents" header button and the Documents tab. When staff upload a PDF (care plan, discharge summary, GP letter, assessment report), the system:

1. **Uploads** the PDF securely (validated MIME type, max 10MB, stored in `storage/app/private/imports/`)
2. **Extracts** text from the PDF server-side using `smalot/pdfparser`
3. **Sends** the extracted text to GPT-4o with a structured prompt requesting JSON output matching import categories
4. **Displays** the extracted data in a review screen with expandable sections per category
5. **Staff review** and select which categories to import (checkbox per category)
6. **Imports** confirmed data into the appropriate database tables
7. **Logs** the import in `ai_document_imports` table (audit trail) and `ai_usage_logs` (token tracking)

### Supported Import Types (from CareRoster reference)

| Import Type | Target Table(s) | What Gets Extracted |
|---|---|---|
| Care History | `su_care_history` | Title, date, description entries |
| Medications | `mar_sheets` | Drug name, dosage, route, frequency, time slots, reason, allergies/warnings |
| Risk Assessments | `su_risk` | Risk type, status, linked risk_id from `risk` table |
| Client Profile | `service_user` | Allergies, medical_notes, care_needs, mental_health_issues, drug_n_alcohol_issues, mobility |
| Body Map | `body_map` | Injury type, description, location, date, size, colour |
| DoLS | `dols` | DoLS status, authorisation type, reason, mental capacity assessment |

**NOT in scope (tables don't exist yet in Care OS):**
- Behaviour Support Plan (su_behavior only has rate/description, not structured plans)
- Mental Capacity Assessment (no dedicated table — only a field in dols)
- PEEP (no table exists)

### User Flow

```
Client Details page → Click "Import Documents" (header) OR Documents tab → "Upload Document"
    ↓
Upload modal: Select PDF file (drag & drop or file picker)
    ↓
Loading: "Extracting text from document..." (1-2 seconds, local)
    ↓
AI Processing: "Analysing document with AI..." (10-20 seconds, GPT-4o)
    ↓
Review screen: Extracted data shown in expandable category cards
    ↓
Staff toggle checkboxes per category (pre-selected based on what AI found)
    ↓
Click "Confirm Import" → data saved to target tables
    ↓
Success summary: "Imported: 3 medications, 2 risk assessments, 1 care history entry"
    ↓
Document stored in su_file_manager + import logged in ai_document_imports
```

### UI Design: Import Modal

```
┌─────────────────────────────────────────────────────────────────────┐
│  AI Document Import                                          [X]   │
│                                                                     │
│  ┌─ Step 1: Upload ─────────────────────────────────────────────┐  │
│  │                                                               │  │
│  │   ┌─────────────────────────────────────────────────────┐    │  │
│  │   │          📄 Drag & drop PDF here                     │    │  │
│  │   │          or click to browse                          │    │  │
│  │   │          PDF only • Max 10MB                         │    │  │
│  │   └─────────────────────────────────────────────────────┘    │  │
│  │                                                               │  │
│  │   [Upload & Analyse]                                          │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌─ Step 2: Review Extracted Data ──────────────────────────────┐  │
│  │                                                               │  │
│  │  [✓] Care History (2 entries found)              [▼ Expand]  │  │
│  │      ● "Sefton House — 2010-01-04"                           │  │
│  │      ● "Omega Care Group — 2012-05-16"                       │  │
│  │                                                               │  │
│  │  [✓] Medications (5 found)                       [▼ Expand]  │  │
│  │      ● Paracetamol 500mg — twice daily — oral                │  │
│  │      ● Omeprazole 20mg — once daily — oral                   │  │
│  │      ● ...                                                    │  │
│  │                                                               │  │
│  │  [✓] Risk Assessments (3 found)                  [▼ Expand]  │  │
│  │      ● Falls Risk — High                                     │  │
│  │      ● Choking Risk — Medium                                 │  │
│  │      ● ...                                                    │  │
│  │                                                               │  │
│  │  [ ] Client Profile Updates (4 fields)           [▼ Expand]  │  │
│  │      ● Allergies: "Penicillin, Latex"                        │  │
│  │      ● Medical Notes: "Type 2 Diabetes, COPD"               │  │
│  │      ● ...                                                    │  │
│  │                                                               │  │
│  │  ⚠ No Body Map or DoLS data found in this document           │  │
│  │                                                               │  │
│  │  [Confirm Import (3 categories)]     [Cancel]                │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌─ Step 3: Import Summary ─────────────────────────────────────┐  │
│  │  ✓ Imported 2 care history entries                            │  │
│  │  ✓ Imported 5 medications to MAR sheets                       │  │
│  │  ✓ Imported 3 risk assessments                                │  │
│  │  ✗ Client profile — skipped (unchecked)                       │  │
│  │                                                               │  │
│  │  Document saved to client files.                              │  │
│  │  [Close]                                                      │  │
│  └───────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────┘
```

### UI Design: Documents Tab (Dynamic List)

Replace the static placeholder content in `clientDocumentsTab` (lines 3490-3686) with a dynamic AJAX-loaded list:

```
┌─────────────────────────────────────────────────────────────────────┐
│  Document Management                   [Import Documents] [Upload] │
│  Store and manage client-related documents                          │
│                                                                      │
│  ┌─ Filters ────────────────────────────────────────────────────┐  │
│  │ [All] [Care Plan] [Risk Assessment] [Medical] [Other]        │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌─ GP Discharge Letter.pdf ────────────────────────────────────┐  │
│  │ 📄 Medical • Uploaded: 8 May 2026 • By: Komal Gautam        │  │
│  │ Size: 245 KB                                                  │  │
│  │ Tags: ai-imported, medications, risk-assessment               │  │
│  │ AI imported: 5 medications, 3 risk assessments                │  │
│  │ [Download] [Delete]                                           │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌─ Initial Assessment.pdf ─────────────────────────────────────┐  │
│  │ 📄 Care Plan • Uploaded: 5 May 2026 • By: Komal Gautam      │  │
│  │ Size: 1.2 MB                                                  │  │
│  │ [Download] [Delete]                                           │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  No more documents.                                                  │
└──────────────────────────────────────────────────────────────────────┘
```

## Database: One New Table

### `ai_document_imports` Table

```sql
CREATE TABLE ai_document_imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,                 -- service_user.id
    uploaded_by INT UNSIGNED NOT NULL,               -- user.id
    original_filename VARCHAR(255) NOT NULL,
    stored_path VARCHAR(500) NOT NULL,               -- path in storage/app/private/imports/
    file_size INT UNSIGNED NOT NULL,                 -- bytes
    file_mime VARCHAR(100) NOT NULL,                 -- validated MIME type
    extracted_text_length INT UNSIGNED NULL,          -- chars of text extracted from PDF
    import_status VARCHAR(20) NOT NULL DEFAULT 'uploaded', -- uploaded, extracting, extracted, importing, completed, failed
    extracted_data JSON NULL,                          -- the AI-extracted structured data (before user confirmation)
    imported_categories JSON NULL,                     -- which categories user confirmed (e.g. ["medications", "risk_assessments"])
    import_summary JSON NULL,                          -- result counts (e.g. {"medications": 5, "risk_assessments": 3})
    ai_model VARCHAR(50) NULL,                        -- e.g. 'gpt-4o'
    tokens_input INT UNSIGNED NOT NULL DEFAULT 0,
    tokens_output INT UNSIGNED NOT NULL DEFAULT 0,
    generation_time_ms INT UNSIGNED NULL,
    error_message TEXT NULL,                           -- if import_status = 'failed'
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_adi_home_client (home_id, client_id),
    INDEX idx_adi_status (import_status),
    INDEX idx_adi_uploaded_by (uploaded_by)
);
```

### `extracted_data` JSON Structure (what AI returns)

```json
{
    "care_history": [
        {
            "title": "Previous placement at Sefton House",
            "date": "2010-01-04",
            "description": "Client was placed at Sefton House residential care from Jan 2010. Transferred due to changing care needs."
        }
    ],
    "medications": [
        {
            "medication_name": "Paracetamol",
            "dosage": "500mg",
            "dose": "2 tablets",
            "route": "Oral",
            "frequency": "Twice daily",
            "time_slots": ["08:00", "20:00"],
            "reason_for_medication": "Pain management",
            "allergies_warnings": "None known",
            "start_date": "2025-01-15"
        }
    ],
    "risk_assessments": [
        {
            "risk_type": "Falls",
            "risk_level": "high",
            "description": "History of falls, reduced mobility. Requires grab rails and walking aid.",
            "control_measures": "Grab rails fitted, non-slip mats, assisted walks twice daily"
        }
    ],
    "client_profile": {
        "allergies": "Penicillin, Latex",
        "medical_notes": "Type 2 Diabetes (controlled), COPD, Hypertension",
        "care_needs": "Requires assistance with personal care, medication management, and mobility",
        "mental_health_issues": "Mild anxiety, managed with CBT techniques",
        "drug_n_alcohol_issues": "None",
        "suMobility": "Uses walking frame, can weight-bear with assistance"
    },
    "body_map": [
        {
            "injury_type": "Bruise",
            "injury_description": "Small bruise on left forearm, likely from minor bump",
            "body_part": "Left forearm",
            "injury_date": "2026-04-20",
            "injury_size": "2cm x 1cm",
            "injury_colour": "Purple/yellow (healing)"
        }
    ],
    "dols": [
        {
            "dols_status": "Authorised",
            "authorisation_type": "Standard",
            "reason_for_dols": "Client lacks capacity to consent to care placement",
            "mental_capacity_assessment": "Assessed as lacking capacity for residence decisions"
        }
    ],
    "document_summary": "GP discharge letter for [client]. Contains medication list (5 drugs), risk assessment notes, and updated allergy information. Document dated 2026-04-15."
}
```

## Dependency: PDF Text Extraction

**Problem:** OpenAI's chat completions API accepts text, not PDF files. We need to extract text from uploaded PDFs before sending to the AI.

**Option A: `smalot/pdfparser` (RECOMMENDED)**
```bash
composer require smalot/pdfparser
```
- Pure PHP, no system dependencies
- Handles most PDF formats including scanned text (if the PDF has a text layer)
- Does NOT do OCR on image-only PDFs (limitation)
- Lightweight, well-maintained

**Option B: `spatie/pdf-to-text`**
- Requires `pdftotext` binary installed on the system (poppler-utils)
- Better text extraction quality
- Harder to deploy (system dependency)

**Decision: Use `smalot/pdfparser`.** If it fails on PHP 8.5 (like `openai-php/client` did in Feature 1), fall back to a simple `exec('pdftotext')` call — but try smalot first.

**For image-only PDFs (scanned documents with no text layer):** Return a clear error message: "This PDF appears to be a scanned image without readable text. Please use a PDF with selectable text, or re-scan with OCR enabled." We do NOT attempt OCR — it requires heavy dependencies (Tesseract) and is out of scope.

## Shared AI Infrastructure (Reuse from Features 1-2)

### 1. `OpenAIService::chatJson()` — Structured Output

Same as Feature 2. Use `chatJson()` with `config('ai.quality_model')` (gpt-4o) for document extraction. PDF text can be long — increase `max_tokens` to 4000 for extraction responses.

### 2. `PIIFilter::filter()` — Filter PDF Text Before Sending

The extracted PDF text will contain PII (names, DOB, addresses, NHS numbers). Apply `PIIFilter::filter()` to the extracted text before sending to OpenAI. Use `skipNames: true` since the AI needs names to associate data correctly.

### 3. `TokenTracker::log()` with `feature = 'document_import'`

PDF text extraction can produce large prompts (5000-10000 tokens input). Log every extraction call. The daily cap (100K tokens/home) is shared across all AI features.

### 4. `TokenTracker::isCapExceeded()` — Check Before Extraction

Same cap check as copilot/care plan. Reject extraction if daily cap exceeded.

## API Endpoints

### Controller: `app/Http/Controllers/frontEnd/Roster/AIDocumentImportController.php`

| Method             | Route                                              | Purpose                                        | Throttle      |
| ------------------ | -------------------------------------------------- | ---------------------------------------------- | ------------- |
| `upload()`         | POST `/roster/ai-document-import/upload`           | Upload PDF and extract text                    | throttle:10,1 |
| `extract()`        | POST `/roster/ai-document-import/extract`          | Send extracted text to AI for structured data  | throttle:10,1 |
| `confirmImport()`  | POST `/roster/ai-document-import/confirm`          | Import confirmed categories to target tables   | throttle:10,1 |
| `list()`           | GET  `/roster/ai-document-import/list?client_id=X` | List all document imports for a client         | throttle:30,1 |
| `documents()`      | GET  `/roster/ai-document-import/documents?client_id=X` | List all documents (from su_file_manager) for a client | throttle:30,1 |
| `delete()`         | POST `/roster/ai-document-import/delete`           | Soft-delete an import record                   | throttle:20,1 |
| `download()`       | GET  `/roster/ai-document-import/download/{id}`    | Download the original uploaded file            | throttle:30,1 |

### `upload()` endpoint — Step 1:

```
User clicks "Import Documents" → selects PDF file
    ↓
1.  Validate file: required, mimes:pdf, max:10240 (10MB)
2.  Validate MIME type server-side (check file header bytes, don't trust extension)
3.  Validate client_id belongs to user's home (IDOR check)
4.  Store file in storage/app/private/imports/{home_id}/{timestamp}_{original_name}
5.  Extract text using smalot/pdfparser
6.  If no text extracted (image-only PDF) → return error
7.  If text < 50 chars → return error "Document contains too little text"
8.  Create ai_document_imports record with status='uploaded', store extracted_text_length
9.  Return import_id + text preview (first 500 chars) + text_length
```

### `extract()` endpoint — Step 2:

```
Frontend sends import_id to extract
    ↓
1.  Validate import_id exists, belongs to user's home, status is 'uploaded'
2.  Check AI is configured (OpenAIService::isConfigured())
3.  Check daily token cap (TokenTracker::isCapExceeded)
4.  Retrieve stored extracted text (re-extract from file, don't store raw text in DB)
5.  PII-filter the text (PIIFilter::filter() with skipNames: true)
6.  Truncate to max 15000 chars if needed (prevent token explosion)
7.  Build extraction prompt with JSON schema
8.  Call OpenAI chatJson() with quality_model (gpt-4o), max_tokens: 4000
9.  Parse and validate JSON response structure
10. Update ai_document_imports: status='extracted', extracted_data=JSON, tokens, ai_model
11. Log usage to ai_usage_logs (feature='document_import')
12. Return extracted_data JSON for review UI
```

### `confirmImport()` endpoint — Step 3:

```
Staff reviews extracted data → selects categories → clicks "Confirm Import"
    ↓
1.  Validate import_id, belongs to user's home, status is 'extracted'
2.  Validate selected_categories is array of valid category names
3.  For each selected category, import data to target tables:

    care_history → INSERT into su_care_history (home_id, service_user_id, title, date, description)
    medications → INSERT into mar_sheets (home_id, service_user_id, medication_name, dosage, dose, route, frequency, time_slots, reason_for_medication, allergies_warnings, start_date, status='active')
    risk_assessments → Match risk_type to risk.description, INSERT into su_risk (home_id, service_user_id, risk_id, status)
    client_profile → UPDATE service_user SET allergies, medical_notes, care_needs, etc. WHERE id AND home_id
    body_map → INSERT into body_map (home_id, service_user_id, injury_type, injury_description, injury_date, injury_size, injury_colour, body_part)
    dols → INSERT into dols (home_id, service_user_id, dols_status, authorisation_type, reason_for_dols, mental_capacity_assessment)

4.  Also store original file in su_file_manager (home_id, service_user_id, category_id, file)
5.  Update ai_document_imports: status='completed', imported_categories, import_summary
6.  Return summary JSON with counts per category
```

### `download()` endpoint — File download:

```
1.  Validate import belongs to user's home
2.  Return file from storage/app/private/imports/ as download response
3.  Set Content-Disposition: attachment to force download (never inline display)
```

## AI Extraction Prompt Design

### System Prompt

```
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
```

### User Prompt Template

```
Analyse the following care document for client: {client_name}

Document text:
---
{extracted_pdf_text}
---

Extract all relevant structured data into the following categories:
- care_history: Previous placements, care history entries
- medications: All medication details (drug name, dosage, route, frequency, times, reason, warnings)
- risk_assessments: Identified risks with levels and control measures
- client_profile: Personal details updates (allergies, medical notes, care needs, mental health, mobility)
- body_map: Any recorded injuries or skin conditions
- dols: Deprivation of Liberty Safeguards information
- document_summary: Brief description of the document

Respond with JSON only.
```

## Service Layer

### `app/Services/AI/AIDocumentImportService.php`

```php
class AIDocumentImportService
{
    // Dependencies: OpenAIService, PIIFilter, TokenTracker

    public function extractTextFromPdf(string $filePath): string
    // Uses smalot/pdfparser to extract text from stored PDF
    // Throws RuntimeException if no text extractable
    // Returns cleaned text (strip excessive whitespace/newlines)

    public function extractDataWithAI(int $importId, int $homeId): array
    // Orchestrates: load text → PII filter → build prompt → call AI → validate → store
    // Returns the extracted_data JSON

    public function importToDatabase(int $importId, array $selectedCategories, int $homeId, int $userId): array
    // For each category, calls the appropriate private import method
    // Returns summary: ['care_history' => 2, 'medications' => 5, ...]

    private function importCareHistory(int $clientId, int $homeId, array $entries): int
    private function importMedications(int $clientId, int $homeId, array $meds): int
    private function importRiskAssessments(int $clientId, int $homeId, array $risks): int
    private function updateClientProfile(int $clientId, int $homeId, array $profile): int
    private function importBodyMap(int $clientId, int $homeId, array $entries): int
    private function importDols(int $clientId, int $homeId, array $entries): int

    private function buildExtractionPrompt(string $pdfText, string $clientName): array
    // Returns ['system_prompt' => string, 'user_prompt' => string]
```

## File Upload Security (CRITICAL)

All rules from `careos-workflow-phase3.md` apply, plus:

1. **MIME validation is server-side, not client-side.** Check the file's magic bytes, not just the extension. A `.php` file renamed to `.pdf` must be rejected.
   ```php
   $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);
   // ALSO check MIME manually:
   $mime = $file->getMimeType(); // uses fileinfo extension
   if ($mime !== 'application/pdf') abort(422, 'Invalid file type');
   ```

2. **Storage location is PRIVATE.** `storage/app/private/imports/` — NOT `public/`. Files are served through the `download()` endpoint which checks auth + home_id, never via direct URL.

3. **Filename sanitization.** Never use the original filename for storage. Use: `{home_id}/{timestamp}_{hash}.pdf` where hash is `substr(md5(original_name . time()), 0, 8)`.

4. **No PHP execution.** Uploaded files must never be stored anywhere that Apache/Nginx might execute them. `storage/app/private/` is safe — it's not in the webroot.

5. **File size limit enforced at both Laravel validation AND php.ini level.** `upload_max_filesize` and `post_max_size` in php.ini must allow 10MB.

6. **Delete after failed processing.** If PDF text extraction or AI processing fails permanently, don't leave orphan files. Mark the import as 'failed' but keep the file for debugging (admin can clean up later).

## Routes Configuration

```php
// In routes/web.php — inside the existing roster group
Route::post('/roster/ai-document-import/upload', [AIDocumentImportController::class, 'upload'])
    ->middleware('throttle:10,1')->where('id', '[0-9]+');
Route::post('/roster/ai-document-import/extract', [AIDocumentImportController::class, 'extract'])
    ->middleware('throttle:10,1');
Route::post('/roster/ai-document-import/confirm', [AIDocumentImportController::class, 'confirmImport'])
    ->middleware('throttle:10,1');
Route::get('/roster/ai-document-import/list', [AIDocumentImportController::class, 'list'])
    ->middleware('throttle:30,1');
Route::get('/roster/ai-document-import/documents', [AIDocumentImportController::class, 'documents'])
    ->middleware('throttle:30,1');
Route::post('/roster/ai-document-import/delete', [AIDocumentImportController::class, 'delete'])
    ->middleware('throttle:20,1');
Route::get('/roster/ai-document-import/download/{id}', [AIDocumentImportController::class, 'download'])
    ->middleware('throttle:30,1')->where('id', '[0-9]+');
```

**checkUserAuth whitelist additions:**
```php
'roster/ai-document-import/upload' => 'roster/ai-document-import/upload',
'roster/ai-document-import/upload/' => 'roster/ai-document-import/upload/',
'roster/ai-document-import/extract' => 'roster/ai-document-import/extract',
'roster/ai-document-import/extract/' => 'roster/ai-document-import/extract/',
'roster/ai-document-import/confirm' => 'roster/ai-document-import/confirm',
'roster/ai-document-import/confirm/' => 'roster/ai-document-import/confirm/',
'roster/ai-document-import/list' => 'roster/ai-document-import/list',
'roster/ai-document-import/list/' => 'roster/ai-document-import/list/',
'roster/ai-document-import/documents' => 'roster/ai-document-import/documents',
'roster/ai-document-import/documents/' => 'roster/ai-document-import/documents/',
'roster/ai-document-import/delete' => 'roster/ai-document-import/delete',
'roster/ai-document-import/delete/' => 'roster/ai-document-import/delete/',
'roster/ai-document-import/download/' => 'roster/ai-document-import/download/',
```

## JavaScript: `public/js/roster/ai-document-import.js`

### Key Functions

```javascript
// CSRF setup (same pattern as other AI features)
var csrfToken = $('meta[name="csrf-token"]').attr('content');
$.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} });

// XSS helper (same as copilot/care plan)
function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function openImportModal(clientId)
// Opens #aiDocumentImportModal, resets to Step 1

function handleFileSelect(event)
// Validates file type (PDF only) and size (max 10MB) client-side
// Shows file name and size in UI

function uploadAndExtractText()
// POST /roster/ai-document-import/upload with FormData
// Shows loading state: "Extracting text from document..."
// On success, stores import_id, shows text preview, enables "Analyse with AI"

function analyseWithAI(importId)
// POST /roster/ai-document-import/extract
// Shows loading state: "Analysing document with AI..." with progress bar
// On success, renders extracted data in review cards (Step 2)
// On error, shows specific message

function renderExtractedData(data)
// Builds expandable card HTML for each category found
// Pre-checks categories that have data, unchecks empty ones
// Uses esc() for ALL data values

function toggleCategory(category)
// Checkbox toggle for import categories

function confirmImport(importId)
// Collects checked categories
// POST /roster/ai-document-import/confirm
// Shows loading: "Importing data..."
// On success, renders import summary (Step 3)
// Reloads document list in Documents tab

function loadDocumentList(clientId)
// GET /roster/ai-document-import/documents?client_id=X
// Renders document cards in Documents tab
// Called on tab activation and after import

function deleteDocument(importId)
// confirm() prompt → POST /roster/ai-document-import/delete
// Reloads document list
```

## Model

### `app/Models/AIDocumentImport.php`

```php
class AIDocumentImport extends Model
{
    protected $table = 'ai_document_imports';

    protected $fillable = [
        'home_id',
        'client_id',
        'uploaded_by',
        'original_filename',
        'stored_path',
        'file_size',
        'file_mime',
        'extracted_text_length',
        'import_status',
        'extracted_data',
        'imported_categories',
        'import_summary',
        'ai_model',
        'tokens_input',
        'tokens_output',
        'generation_time_ms',
        'error_message',
        'is_deleted',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'imported_categories' => 'array',
        'import_summary' => 'array',
    ];
}
```

## Tests (25+ tests)

### File Upload Security Tests
1. Upload valid PDF → 200, file stored, text extracted
2. Upload .php file renamed to .pdf → 422 rejected (MIME check)
3. Upload .exe file → 422 rejected
4. Upload PDF > 10MB → 422 rejected
5. Upload non-file (empty request) → 422 validation error
6. Upload to client in different home → 403 IDOR check

### AI Extraction Tests (mock OpenAI)
7. Valid PDF text → AI returns structured JSON → stored correctly
8. AI returns empty response → graceful error
9. AI returns invalid JSON → error logged, user sees fallback
10. AI returns JSON missing required fields → partial data handled
11. AI timeout → graceful error message
12. API rate limit (429) → "try again later"
13. Token cap exceeded → "daily limit reached"

### Import Tests
14. Confirm import with care_history → rows inserted in su_care_history with correct home_id
15. Confirm import with medications → rows inserted in mar_sheets with correct home_id
16. Confirm import with risk_assessments → rows inserted in su_risk with correct home_id
17. Confirm import with client_profile → service_user updated (only specified fields)
18. Confirm import with empty categories → nothing imported, no error
19. Confirm import for wrong home → 403 IDOR check
20. Import same document twice → creates new records (idempotency is user's responsibility)

### Document List Tests
21. List documents for client → returns correct documents filtered by home_id
22. List documents for client in different home → empty (IDOR)
23. Download document → file served with correct headers
24. Download document from different home → 403

### PII Tests
25. PDF text with NHS numbers → filtered before AI call
26. PDF text with addresses/postcodes → filtered before AI call

### Auth Tests
27. All endpoints without auth → redirect to login
28. All POST endpoints without CSRF → 419

### Full Regression
29. Run all existing tests → 0 failures

## Blade Changes

### client_details.blade.php

1. **Wire "Import Documents" header button (line 53):**
   Change from non-functional to modal trigger:
   ```html
   <button class="btn borderBtn" onclick="openImportModal({{ $service_user_id }})">
       <i class='bx bx-arrow-in-up-square-half'></i> Import Documents
   </button>
   ```

2. **Delete static Documents tab content (lines 3518-3686):**
   Replace the hardcoded upload form and two static document cards with a dynamic container:
   ```html
   <div id="documentListContainer">
       <div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Loading documents...</div>
   </div>
   ```

3. **Add import modal at BOTTOM of page (OUTSIDE all tab divs — Bug 2 prevention):**
   `#aiDocumentImportModal` — the 3-step import flow modal

4. **Include JS file:**
   ```html
   <script src="{{ url('js/roster/ai-document-import.js') }}"></script>
   ```
   Inside `@section('content')`, not `@section('scripts')` (Bug prevention — admin layout has no @yield('scripts')).

### Div Balance Check (mandatory post-build):
After deleting static content and adding new HTML, run:
```bash
grep -c '<div' client_details.blade.php && grep -c '</div>' client_details.blade.php
```
Difference must be <= 1.

## Security Checklist (Feature-Specific)

- [ ] PDF MIME validated server-side (magic bytes, not just extension)
- [ ] File stored in `storage/app/private/` (not public webroot)
- [ ] Filename sanitized (no user-controlled path components)
- [ ] File size enforced at validation layer (max 10MB)
- [ ] Download endpoint checks auth + home_id before serving file
- [ ] Content-Disposition: attachment on downloads (no inline rendering)
- [ ] PII filtered from PDF text before sending to OpenAI
- [ ] AI output escaped with `{{ }}` in Blade and `esc()` in JS
- [ ] All import INSERTs include home_id from authenticated user (not from request)
- [ ] IDOR check on every endpoint (import record's home_id matches user's home)
- [ ] Token tracking on AI calls (feature='document_import')
- [ ] Token cap check before AI extraction
- [ ] CSRF on all POST endpoints
- [ ] Rate limiting on all endpoints
- [ ] No `DB::raw()` with any data derived from PDF content
- [ ] No `{!! !!}` for any AI-extracted or PDF-derived content

## Estimated Build Time: 7 hours

| Stage | Time |
|---|---|
| PLAN (review + approve) | 15 min |
| SCAFFOLD (table + model + controller + routes + composer) | 30 min |
| BUILD (service + upload + extraction + import + UI) | 3.5 hours |
| TEST (25+ tests) | 1.5 hours |
| DEBUG + REVIEW + AUDIT | 45 min |
| PROD-READY (3 real PDF imports + manual checklist) | 30 min |
