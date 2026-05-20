# Phase 3 Feature 4 — AI New Client Importer (Documents → New Client)

WORKFLOW: Phase 3 Feature 4 — AI New Client Importer
Run `/careos-workflow-phase3` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

**IMPORTANT:** Before starting, read `docs/logs.md` for full context on Phase 3 Features 1-3. Also read `CLAUDE.md` for project conventions, security rules, and codebase patterns.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Phase 3 Feature 4 — AI New Client Importer (Documents → New Client)
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — New service class, controller, routes (reuses existing ai_document_imports table)
[ ] BUILD — Multi-file upload, AI extraction with client profile fields, review UI, create client + import care records
[ ] TEST — Upload validation, AI mock tests, client creation tests, IDOR, FULL REGRESSION
[ ] DEBUG — Real document upload + AI extraction, verify new client created in service_user, verify care records linked
[ ] REVIEW — Adversarial curl attacks: IDOR, XSS in AI output, duplicate client creation, malicious files
[ ] AUDIT — Phase 1+2+3 grep patterns + client creation audit + mass assignment audit
[ ] PROD-READY — Import 3 different document sets to create new clients, manual test checklist, user confirms "tested"
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Pre-Requisites (Verify Before Starting)

- [ ] **Phase 3 Features 1-3 are complete:** All shared AI infrastructure exists and tests pass:
    - `config/ai.php` — AI configuration (default_model=gpt-4o-mini, quality_model=gpt-4o)
    - `app/Services/AI/OpenAIService.php` — `chat()`, `chatJson()`, `isConfigured()`
    - `app/Services/AI/PIIFilter.php` — `filter()` with `skipNames` param, `filterClientData()`
    - `app/Services/AI/TokenTracker.php` — `log()`, `isCapExceeded()`, `getDailyUsage()`
    - `app/Services/AI/PromptBuilder.php` — `buildClientContext()`, etc.
    - `app/Services/AI/AIDocumentImportService.php` — Feature 3's document import service (reuse `extractTextFromFile()`, `extractTextFromPdf()`, `extractTextFromDocx()`)
    - `smalot/pdfparser` and `phpoffice/phpword` in composer.json
    - DB tables: `ai_chat_sessions`, `ai_chat_messages`, `ai_usage_logs`, `ai_care_plans`, `ai_document_imports`
- [ ] **All 353+ tests pass:** `php -d error_reporting=0 artisan test` — no regressions
- [ ] **OPENAI_API_KEY is set** in `.env`
- [ ] **Read `docs/logs.md`** for prior session context

**Gate: All boxes checked before proceeding to SCAFFOLD.**

---

## Feature Classification

**Category: BUILD FROM SCRATCH** — The current clients list page (`/roster/client`) has an "Add Client" button (line 17 of `client.blade.php`) that opens a manual form modal. There is no automated way to create a client from uploaded documents. We are building an AI-powered flow that extracts client details and care records from uploaded documents, then creates a new `service_user` record with all associated care data.

**CareRoster reference (UX patterns only):**

- `AINewClientImporter.jsx` (~890 lines) — Multi-file upload → AI extracts client profile + care records → review extracted data in expandable cards → confirm creates new client + care plan + medications + risk assessments + behaviour plan + mental capacity + PEEP + auto-generated care tasks. We adopt the same upload → extract → review → create pattern but build it as a Laravel service.
- **Key differences from CareRoster:**
    1. CareRoster sends file URLs to Base44's LLM which handles file reading. We extract text server-side first (reuse Feature 3's `extractTextFromFile()`)
    2. CareRoster creates entities like CarePlan, BehaviorChart, PEEP, MentalCapacityAssessment — some of these tables don't exist in Care OS. We map to existing tables.
    3. CareRoster supports multi-file upload and combines data. We support the same.

**Key difference from Feature 3 (AI Document Importer):**
- Feature 3 imports documents into an **existing** client's records
- Feature 4 **creates a brand new client** from documents — the AI extracts the client's name, DOB, address, emergency contacts, etc. to build the `service_user` record

## What Exists

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AI infrastructure (OpenAIService,     │ EXISTS   │ Built in Features 1-3. Reuse OpenAIService::chatJson() for  │
│ PIIFilter, TokenTracker)              │          │ extraction. Use quality_model (gpt-4o).                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AIDocumentImportService               │ EXISTS   │ Feature 3 built extractTextFromFile(), extractTextFromPdf(), │
│                                       │          │ extractTextFromDocx(). Reuse these methods. The new service  │
│                                       │          │ will call them or inherit/compose.                           │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ai_document_imports table             │ EXISTS   │ Reuse for audit trail. Add client_id=NULL support for        │
│                                       │          │ imports that create a new client (client_id set after        │
│                                       │          │ creation). OR use a separate tracking approach.              │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ smalot/pdfparser + phpoffice/phpword  │ EXISTS   │ Already installed in composer.json from Feature 3.           │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Clients list page                     │ EXISTS   │ Route: GET /roster/client                                    │
│                                       │          │ Controller: ClientController::index()                        │
│                                       │          │ View: frontEnd.roster.client.client                          │
│                                       │          │ Has "Add Client" button at line 17, opens manual form modal  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ service_user table                    │ EXISTS   │ 17 clients in home 8. Required NOT NULL fields (no default): │
│                                       │          │ home_id, name, user_name, department, section,               │
│                                       │          │ short_description, height_unit, weight_unit, hair_and_eyes,  │
│                                       │          │ markings, image, password, personal_info,                    │
│                                       │          │ education_history, bereavement_issues,                       │
│                                       │          │ drug_n_alcohol_issues, mental_health_issues,                 │
│                                       │          │ current_location, previous_location, created_at, updated_at  │
│                                       │          │ Optional fields: date_of_birth, gender, phone_no, email,     │
│                                       │          │ allergies, medical_notes, care_needs, suMobility,            │
│                                       │          │ suFundingType, em_name, em_phone, relationship,              │
│                                       │          │ street, city, postcode, child_type, local_authority          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Target tables for care records        │ EXISTS   │ su_care_history, mar_sheets, su_risk + risk, body_map, dols, │
│                                       │          │ service_user (profile fields). Same as Feature 3 targets.    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AINewClientImportController           │ MISSING  │ Need new controller for new-client import flow.              │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AINewClientImportService              │ MISSING  │ Need service class for client creation + care record import. │
│                                       │          │ Can compose/reuse AIDocumentImportService methods.           │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Building

An **AI New Client Importer** — accessible from the clients list page (`/roster/client`) via a new "Import Client" button next to the existing "Add Client" button. When staff upload one or more documents (PDF/DOCX) about a new client, the system:

1. **Uploads** multiple files securely (validated MIME types, max 10MB each, stored in `storage/app/private/imports/`)
2. **Extracts** text from all files server-side (reuse Feature 3's extractors)
3. **Combines** text from all documents and sends to GPT-4o with a structured prompt requesting:
   - **Client profile** (name, DOB, gender, phone, address, emergency contact, care needs, medical notes, mobility, funding type)
   - **Care records** (care history, medications, risk assessments, client profile details, body map, DoLS)
4. **Displays** extracted data in a review screen — client profile card at top, then expandable care record sections
5. **Staff review** the extracted data and select which care categories to import
6. **On confirm**: creates a new `service_user` record, then imports selected care records into the target tables linked to the new client
7. **Redirects** to the new client's details page after creation
8. **Logs** the import in `ai_document_imports` table and `ai_usage_logs`

### What AI Extracts (Client Profile)

From CareRoster's `AINewClientImporter.jsx`, the AI extracts these client fields:

| AI Field | Maps to `service_user` Column | Notes |
|---|---|---|
| `full_name` | `name` | REQUIRED — abort if not found |
| `date_of_birth` | `date_of_birth` | Format: YYYY-MM-DD |
| `gender` | `gender` | Map to enum: 'M' or 'F' |
| `phone` | `phone_no` | |
| `address.street` | `street` | |
| `address.city` | `city` | |
| `address.postcode` | `postcode` | |
| `emergency_contact.name` | `em_name` | |
| `emergency_contact.phone` | `em_phone` | |
| `emergency_contact.relationship` | `relationship` | |
| `care_needs` | `care_needs` | Array → joined text |
| `medical_notes` | `medical_notes` | |
| `mobility` | `suMobility` | |
| `funding_type` | `suFundingType` | |
| `allergies` | `allergies` | |
| `mental_health_issues` | `mental_health_issues` | |
| `drug_n_alcohol_issues` | `drug_n_alcohol_issues` | |
| `personal_info` | `personal_info` | Background/history |
| `local_authority` | `local_authority` | |
| `child_type` | `child_type` | residential/accommodation/leavers |

### Required Fields for `service_user` (must fill even if AI can't extract)

These NOT NULL fields need defaults when AI doesn't find them:

| Column | Default when not extracted |
|---|---|
| `user_name` | Generate from name: lowercase, no spaces + random 4 digits |
| `department` | `0` |
| `section` | `''` (empty string) |
| `short_description` | `'Imported via AI Document Import'` |
| `height_unit` | `'cm'` |
| `weight_unit` | `'kg'` |
| `hair_and_eyes` | `''` |
| `markings` | `''` |
| `image` | `''` |
| `password` | `bcrypt('changeme123')` — force password change on first login |
| `personal_info` | `''` or extracted |
| `education_history` | `''` or extracted |
| `bereavement_issues` | `''` |
| `current_location` | `''` |
| `previous_location` | `''` |

### Supported Care Record Categories (same as Feature 3)

| Import Type | Target Table(s) | What Gets Extracted |
|---|---|---|
| Care History | `su_care_history` | Title, date, description entries |
| Medications | `mar_sheets` | Drug name, dosage, route, frequency, reason |
| Risk Assessments | `su_risk` | Risk type, level, description, control measures |
| Body Map | `body_map` | Injury type, description, location, date |
| DoLS | `dols` | Status, authorisation type, reason, capacity |

**Note:** `client_profile` is NOT a separate import category here — it's always extracted and used to create the `service_user` record itself.

### User Flow

```
Clients list page (/roster/client) → Click "Import Client" button
    ↓
Import modal opens: Upload one or more PDF/DOCX files (drag & drop or file picker)
    ↓
Loading: "Uploading files..." → "Extracting text..." → "Analysing with AI..."
    ↓
Review screen:
  ┌─ Client Profile ──────────────────────────────────────┐
  │ Name: Susanna Rose Craven                              │
  │ DOB: 2018-03-15  Gender: Female                        │
  │ Address: 12 Oak Street, Liverpool, L15 4AB             │
  │ Emergency: Jane Craven (Mother) — 07700 123456         │
  │ Mobility: Requires assistance                          │
  │ Care Needs: Personal care support, medication mgmt...  │
  └────────────────────────────────────────────────────────┘
  [✓] Care History (4 entries)                [▼ Expand]
  [✓] Medications (2 found)                   [▼ Expand]
  [✓] Risk Assessments (3 found)              [▼ Expand]
  [ ] Body Map (0 found)
  [ ] DoLS (0 found)
    ↓
Click "Create Client with Selected Records"
    ↓
Creates service_user → imports care records → redirects to new client page
    ↓
Success: "Susanna Rose Craven created with 4 care history, 2 medications, 3 risk assessments"
```

### UI Design: Import Modal

```
┌─────────────────────────────────────────────────────────────────────┐
│  Import New Client from Documents                            [X]   │
│  Upload client documentation to automatically create a new client  │
│                                                                     │
│  ┌─ Step 1: Upload Documents ───────────────────────────────────┐  │
│  │                                                               │  │
│  │   ┌─────────────────────────────────────────────────────┐    │  │
│  │   │          📄 Drag & drop files here                    │    │  │
│  │   │          or click to browse                          │    │  │
│  │   │          PDF or Word (.docx) • Max 10MB each         │    │  │
│  │   │          Multiple files allowed                      │    │  │
│  │   └─────────────────────────────────────────────────────┘    │  │
│  │                                                               │  │
│  │   ┌─ Uploaded Files ──────────────────────────────────┐      │  │
│  │   │ ✓ CLA_Review_Oct2025.pdf (245 KB)           [X]   │      │  │
│  │   │ ✓ Placement_Info.pdf (1.2 MB)               [X]   │      │  │
│  │   │ ✓ SC_BSP_Jan2026.docx (89 KB)              [X]   │      │  │
│  │   └───────────────────────────────────────────────────┘      │  │
│  │                                                               │  │
│  │   What care records should we create?                         │  │
│  │   [✓ Care History] [✓ Medications] [✓ Risk Assessments]      │  │
│  │   [  Body Map] [  DoLS]                                       │  │
│  │                                                               │  │
│  │   [Extract Client & Care Data]                                │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌─ Step 2: Review Extracted Data ──────────────────────────────┐  │
│  │                                                               │  │
│  │  ┌─ Client Profile ──────────────────────────────────────┐   │  │
│  │  │ Name: Susanna Rose Craven                              │   │  │
│  │  │ DOB: 15 Mar 2018  Gender: Female                       │   │  │
│  │  │ Local Authority: Sefton Council                        │   │  │
│  │  │ Care Needs: Personal care, medication management       │   │  │
│  │  │ Medical Notes: Asthma, mild learning difficulties      │   │  │
│  │  └────────────────────────────────────────────────────────┘   │  │
│  │                                                               │  │
│  │  [✓] Care History (4 entries)                  [▼ Expand]    │  │
│  │  [✓] Medications (2 found)                     [▼ Expand]    │  │
│  │  [✓] Risk Assessments (3 found)                [▼ Expand]    │  │
│  │  ⚠ No Body Map or DoLS data found                            │  │
│  │                                                               │  │
│  │  [Create "Susanna Rose Craven" with 3 categories]  [Cancel]  │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌─ Step 3: Success ────────────────────────────────────────────┐  │
│  │  ✓ Client "Susanna Rose Craven" created                       │  │
│  │  ✓ Imported 4 care history entries                            │  │
│  │  ✓ Imported 2 medications to MAR sheets                       │  │
│  │  ✓ Imported 3 risk assessments                                │  │
│  │                                                               │  │
│  │  [View Client Profile]                                        │  │
│  └───────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────┘
```

## Database Changes

### `ai_document_imports` table — Minor modification

The existing `ai_document_imports` table has `client_id INT UNSIGNED NOT NULL`. For new-client imports, we don't have a client_id until after creation. Options:

**Option A (RECOMMENDED): Make client_id nullable**
```sql
ALTER TABLE ai_document_imports MODIFY client_id INT UNSIGNED NULL;
```
Set `client_id` to NULL during upload, then update it to the new client's ID after creation.

**Option B: Use a sentinel value like 0**
Less clean, but avoids schema change.

**Decision: Option A** — nullable client_id. Add `import_type` column to distinguish:
```sql
ALTER TABLE ai_document_imports ADD COLUMN import_type VARCHAR(20) NOT NULL DEFAULT 'existing_client' AFTER client_id;
-- Values: 'existing_client' (Feature 3) or 'new_client' (Feature 4)
```

### `extracted_data` JSON Structure (what AI returns for new client)

```json
{
    "client": {
        "full_name": "Susanna Rose Craven",
        "date_of_birth": "2018-03-15",
        "gender": "Female",
        "phone": "",
        "address": {
            "street": "12 Oak Street",
            "city": "Liverpool",
            "postcode": "L15 4AB"
        },
        "emergency_contact": {
            "name": "Jane Craven",
            "phone": "07700 123456",
            "relationship": "Mother"
        },
        "care_needs": ["Personal care support", "Medication management", "Educational support"],
        "medical_notes": "Asthma (uses inhaler PRN), mild learning difficulties",
        "allergies": "None known",
        "mobility": "Independent with supervision",
        "funding_type": "Local authority",
        "local_authority": "Sefton Council",
        "mental_health_issues": "Anxiety — managed with routine and reassurance",
        "drug_n_alcohol_issues": "None",
        "personal_info": "Susanna is a child in care, placed under Section 31 interim care order",
        "child_type": "residential"
    },
    "care_history": [
        {
            "title": "Placement at previous care home",
            "date": "2024-06-01",
            "description": "Placed at Neptune House. Transferred due to staffing concerns."
        }
    ],
    "medications": [
        {
            "medication_name": "Salbutamol Inhaler",
            "dosage": "100mcg",
            "dose": "2 puffs",
            "route": "Inhaled",
            "frequency": "As required",
            "reason_for_medication": "Asthma — use when wheezing or before exercise"
        }
    ],
    "risk_assessments": [
        {
            "risk_type": "Absconding",
            "risk_level": "high",
            "description": "History of absconding from previous placement. Ran away 3 times in 6 months.",
            "control_measures": "1:1 supervision outdoors, secure doors policy, GPS watch"
        }
    ],
    "body_map": [],
    "dols": [],
    "document_summary": "Combined data from 3 documents: CLA Review Minutes, Placement Information Record, and Behaviour Support Plan for Susanna Rose Craven."
}
```

## AI Extraction Prompt Design

### System Prompt

```
You are a healthcare documentation expert working in a UK care home setting.
Your task is to analyse uploaded care documents and extract ALL client information to create a new client record plus their care documentation. If multiple documents are provided, combine information from all documents into a single comprehensive profile.

RULES:
1. FIRST extract the CLIENT DETAILS — full name is REQUIRED. If you cannot find a client name, set full_name to null.
2. Only extract information that is EXPLICITLY stated in the documents. Never infer or generate data.
3. If a category has no relevant data, return an empty array for that category.
4. Dates should be in YYYY-MM-DD format. If only month/year is given, use the 1st of the month.
5. For medications, extract ALL fields if available. Use null for fields not mentioned.
6. For risk assessments, map risk types to standard categories: Falls, Choking, Pressure Sores, Self-Harm, Aggression, Absconding, Fire, Moving and Handling, Nutrition, Medication, Safeguarding.
7. If information conflicts between documents, use the most recent or most complete version.
8. The document_summary should be 1-2 sentences describing what documents were analysed and what data was found.
9. For gender, return "Male" or "Female" only.
10. For child_type, return one of: "residential", "accommodation", or "leavers".

You MUST respond with valid JSON matching the provided schema. Do not include any text outside the JSON.
```

### User Prompt Template

```
Analyse the following care document(s) and extract ALL client information to create a new client record.

{For each file:}
--- Document {N}: {filename} ---
{extracted_text}
--- End Document {N} ---

Extract:
1. Client profile (full_name, date_of_birth, gender, phone, address, emergency_contact, care_needs, medical_notes, allergies, mobility, funding_type, local_authority, mental_health_issues, drug_n_alcohol_issues, personal_info, child_type)
2. Care history entries
3. Medications
4. Risk assessments
5. Body map entries
6. DoLS information
7. Document summary

Respond with JSON only.
```

## API Endpoints

### Controller: `app/Http/Controllers/frontEnd/Roster/AINewClientImportController.php`

| Method             | Route                                                | Purpose                                        | Throttle      |
| ------------------ | ---------------------------------------------------- | ---------------------------------------------- | ------------- |
| `upload()`         | POST `/roster/ai-new-client-import/upload`           | Upload files and extract text from all          | throttle:10,1 |
| `extract()`        | POST `/roster/ai-new-client-import/extract`          | Send combined text to AI for structured data    | throttle:10,1 |
| `confirm()`        | POST `/roster/ai-new-client-import/confirm`          | Create new client + import care records         | throttle:10,1 |

### `upload()` endpoint — Step 1:

```
User clicks "Import Client" → selects one or more files
    ↓
1.  Validate files: required, mimes:pdf,docx,doc, max:10240 each
2.  Validate MIME type server-side for each file
3.  Store files in storage/app/private/imports/{home_id}/
4.  Extract text from each file (reuse AIDocumentImportService::extractTextFromFile())
5.  Combine extracted text from all files
6.  Create ai_document_imports record: import_type='new_client', client_id=NULL, status='uploaded'
7.  Store all file paths in a JSON array in stored_path (or create multiple import records)
8.  Return import_id + combined text length + per-file text lengths + text preview
```

### `extract()` endpoint — Step 2:

```
Frontend sends import_id
    ↓
1.  Validate import_id exists, belongs to user's home, status is 'uploaded', import_type='new_client'
2.  Check AI configured + token cap
3.  Re-extract text from all stored files
4.  PII-filter combined text (PIIFilter::filter() with skipNames: true)
5.  Truncate to max 20000 chars if needed (multi-doc may be larger)
6.  Build extraction prompt with client profile + care records schema
7.  Call OpenAI chatJson() with quality_model (gpt-4o), max_tokens: 4000
8.  Validate response: client.full_name MUST exist
9.  Update ai_document_imports: status='extracted', extracted_data=JSON
10. Log usage to ai_usage_logs (feature='new_client_import')
11. Return extracted_data for review UI
```

### `confirm()` endpoint — Step 3:

```
Staff reviews → clicks "Create Client"
    ↓
1.  Validate import_id, belongs to user's home, status is 'extracted'
2.  Validate selected_categories array
3.  Extract client profile from extracted_data
4.  Validate full_name exists (abort if missing)
5.  CREATE service_user record:
    - home_id from authenticated user
    - name from extracted full_name
    - user_name generated (lowercase name + 4 random digits)
    - All optional fields from AI extraction
    - All required NOT NULL fields get safe defaults
    - status = 1 (active)
    - is_deleted = 0
6.  Update ai_document_imports: client_id = new service_user.id
7.  For each selected category, import care records (reuse Feature 3's import methods):
    - care_history → INSERT into su_care_history with new client_id
    - medications → INSERT into mar_sheets with new client_id
    - risk_assessments → INSERT into su_risk with new client_id
    - body_map → INSERT into body_map with new client_id
    - dols → INSERT into dols with new client_id
8.  Update ai_document_imports: status='completed', imported_categories, import_summary
9.  Return new client_id + summary + redirect URL
```

## Service Layer

### `app/Services/AI/AINewClientImportService.php`

```php
class AINewClientImportService
{
    // Dependencies: OpenAIService, PIIFilter, TokenTracker, AIDocumentImportService (for text extraction + care record import methods)

    public function extractFromMultipleFiles(array $filePaths): string
    // Extracts text from all files, concatenates with document separators
    // Returns combined text

    public function extractDataWithAI(int $importId, int $homeId, int $userId): array
    // Orchestrates: load text → PII filter → build prompt → call AI → validate client.full_name → store
    // Returns the extracted_data JSON

    public function createClientAndImport(int $importId, array $selectedCategories, int $homeId, int $userId): array
    // 1. Creates service_user record from extracted client profile
    // 2. Calls AIDocumentImportService import methods for each category
    // 3. Returns ['client_id' => X, 'client_name' => '...', 'summary' => [...]]

    private function createServiceUser(array $clientData, int $homeId): int
    // Builds service_user record with extracted + default values
    // Returns new service_user.id

    private function buildExtractionPrompt(string $combinedText, array $filenames): array
    // Returns ['system_prompt' => string, 'user_prompt' => string]
    // Includes client profile schema + care records schema
```

## Routes Configuration

```php
// In routes/web.php — inside the existing roster group, after ai-document-import routes
Route::post('/ai-new-client-import/upload', [AINewClientImportController::class, 'upload'])
    ->middleware('throttle:10,1');
Route::post('/ai-new-client-import/extract', [AINewClientImportController::class, 'extract'])
    ->middleware('throttle:10,1');
Route::post('/ai-new-client-import/confirm', [AINewClientImportController::class, 'confirm'])
    ->middleware('throttle:10,1');
```

**checkUserAuth whitelist additions:**
```php
// AI New Client Importer
array_push($allowed_path,
    'roster/ai-new-client-import/upload',
    'roster/ai-new-client-import/extract',
    'roster/ai-new-client-import/confirm'
);
```

## Blade Changes

### `client.blade.php` (clients list page)

1. **Add "Import Client" button next to "Add Client" (line 17):**
   ```html
   <button class="btn" onclick="childCourseData()" data-toggle="modal" data-target="#addServiceUserModal">
       <i class="fa fa-plus"></i> Add Client
   </button>
   <button class="btn borderBtn" onclick="openNewClientImportModal()">
       <i class="fa fa-file-text"></i> Import Client
   </button>
   ```

2. **Add import modal at BOTTOM of page (OUTSIDE all tab/content divs):**
   `#aiNewClientImportModal` — the 3-step import flow

3. **Include JS file:**
   ```html
   <script>var newClientImportBaseUrl = '{{ url("/roster") }}';</script>
   <script src="{{ url('js/roster/ai-new-client-import.js') }}"></script>
   ```

## JavaScript: `public/js/roster/ai-new-client-import.js`

### Key Functions

```javascript
// CSRF setup + XSS esc() helper (same pattern as other AI features)

var uploadedFiles = [];  // Track uploaded file data
var currentImportId = null;

function openNewClientImportModal()
// Opens modal, resets to Step 1, clears uploaded files

function handleFileDrop(event) / handleFileSelect(event)
// Multi-file support: validates each file (PDF/DOCX, max 10MB)
// Shows file list with remove buttons
// Drag & drop zone support

function removeFile(index)
// Remove a file from the upload list before submitting

function uploadFiles()
// POST all files as FormData to /ai-new-client-import/upload
// Shows progress: "Uploading X files..."
// On success, stores import_id, chains to extractData()

function extractData(importId)
// POST to /ai-new-client-import/extract
// Shows: "Analysing documents with AI..."
// On success, renders review screen (Step 2)

function renderReviewScreen(data)
// Renders client profile card (always shown, not toggleable)
// Renders care record categories with checkboxes + expandable cards
// Uses esc() for ALL values
// Shows "Create [Client Name] with X categories" button

function toggleCategory(category) / toggleCategoryExpand(category)
// Checkbox and expand/collapse for care record sections

function confirmCreate(importId)
// Collects checked categories
// POST to /ai-new-client-import/confirm
// Shows: "Creating client..."
// On success, shows Step 3 with "View Client Profile" button
// "View Client Profile" redirects to /roster/client-details/{new_id}

function renderCategoryItems(category, items)
// Same rendering pattern as Feature 3's ai-document-import.js
```

## Tests (20+ tests)

### Upload Tests
1. Upload single PDF → 200, file stored, text extracted
2. Upload multiple files (PDF + DOCX) → 200, all stored
3. Upload non-PDF/DOCX → 422 rejected
4. Upload oversized file → 422 rejected
5. Upload without auth → 302 redirect

### AI Extraction Tests (mock OpenAI)
6. Valid document text → AI returns client profile + care records → stored correctly
7. AI returns no client name (full_name null) → error "Could not find client name"
8. AI returns empty care records → success with empty categories
9. Token cap exceeded → "daily limit reached"
10. AI not configured → graceful error

### Client Creation Tests
11. Confirm with valid data → new service_user created with correct home_id
12. Confirm creates all required NOT NULL fields with defaults
13. Confirm with care_history → su_care_history rows linked to new client
14. Confirm with medications → mar_sheets rows linked to new client
15. Confirm with risk_assessments → su_risk rows linked to new client
16. Confirm with no categories → client created, no care records
17. IDOR: confirm import from different home → 422

### Security Tests
18. Mass assignment: home_id from request body ignored (uses auth user's home)
19. Generated user_name is unique
20. XSS in client name doesn't execute (escaped in UI)

### Full Regression
21. Run all existing tests → 0 failures (353+ tests still pass)

## Security Checklist (Feature-Specific)

- [ ] All Feature 3 file upload security rules apply (MIME validation, private storage, filename sanitization)
- [ ] New client's home_id comes from authenticated user, NEVER from request
- [ ] user_name auto-generated (not user-controlled) to prevent injection
- [ ] password is bcrypt-hashed, never stored in plain text
- [ ] client_id on ai_document_imports set server-side after creation, not from request
- [ ] PII filtered from document text before sending to OpenAI
- [ ] AI output escaped with esc() in JS and {{ }} in Blade
- [ ] All import INSERTs include home_id from authenticated user
- [ ] IDOR check on every endpoint
- [ ] Token tracking with feature='new_client_import'
- [ ] CSRF on all POST endpoints
- [ ] Rate limiting on all endpoints
- [ ] No duplicate client check by name+DOB (warn user if similar client exists)

## Estimated Build Time: 5 hours

| Stage | Time |
|---|---|
| PLAN (review + approve) | 15 min |
| SCAFFOLD (controller + service + routes + DB alter) | 20 min |
| BUILD (multi-file upload + AI extraction + client creation + care import + UI) | 2.5 hours |
| TEST (20+ tests) | 1 hour |
| DEBUG + REVIEW + AUDIT | 30 min |
| PROD-READY (import 3 document sets → create 3 clients) | 25 min |

Shorter than Feature 3 because we reuse most of the infrastructure:
- File upload/extraction from AIDocumentImportService
- Care record import methods from AIDocumentImportService
- Same modal UI pattern
- Same AI prompt structure (extended with client profile)
