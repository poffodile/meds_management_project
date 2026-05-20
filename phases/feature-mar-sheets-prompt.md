WORKFLOW: MAR Sheets — Prescription Management & Administration Grid
Run `/careos-workflow` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

## Documents to Read Before Starting

Read these files at the START of the session before writing any code:

| Document | Path | Why |
|----------|------|-----|
| **Session logs** | `docs/logs.md` | Prior context, past mistakes, teaching notes from Features 1-9 |
| **Security checklist** | `docs/security-checklist.md` | 15-item vulnerability checklist + grep patterns for AUDIT stage |
| **CareRoster MAR schema** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/MARSheet.md` | Reference spec — 32 fields, time_slots, administration_records |
| **CareRoster MAR data** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/MARSheet.json` | 39 sample records — includes rich records with administration_records, time_slots, stock_level |
| **Existing medicationLog model** | `app/Models/medicationLog.php` | Existing simple log model — $fillable, scopes, `frequesncy` typo |
| **Existing medication methods** | `app/Http/Controllers/frontEnd/Roster/Client/ClientController.php` (lines 332-400) | medication_log_save, medication_log_list, medication_log_delete — understand existing patterns |
| **Client details view** | `resources/views/frontEnd/roster/client/client_details.blade.php` (lines 3370-3500) | MAR Sheets tab with "Coming in Phase 2" placeholder + hardcoded detail view |
| **Client details JS** | `public/js/roster/client/client_details.js` | Existing medication JS functions (lines 1-212) + MAR toggle (line 8582) |
| **Roster header** | `resources/views/frontEnd/roster/common/roster_header.blade.php` | Sidebar navigation — no MAR link needed (feature lives in client details) |
| **Phase 1 plan** | `phases/phase1.md` | Feature scope, key rules |
| **CLAUDE.md** | `CLAUDE.md` | Project conventions, security rules |

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: MAR Sheets — Prescription Management & Administration Grid
[ ] PLAN     — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration for mar_sheets + mar_administrations tables, models, service, controller
[ ] BUILD    — Replace placeholder, prescription CRUD, administration grid, detail view
[ ] TEST     — Unit + IDOR + security payload tests (14+)
[ ] DEBUG    — Clear laravel.log, hit all endpoints, check for errors
[ ] REVIEW   — Adversarial curl attacks (use the fixed login command from workflow)
[ ] AUDIT    — Grep patterns + regression check
[ ] PROD-READY — Curl-verified + manual checklist, user confirms "tested"
[ ] PUSH     — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## What Exists (25% done — basic medication logging only, no prescriptions or MAR grid)

┌───────────────────────────────────────┬─────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│               Component               │ Status  │                                                 Details                                                │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `medication_logs`            │ EXISTS  │ Basic log table: id, home_id, user_id, client_id, medication_name, dosage, frequesncy (TYPO),         │
│                                       │         │ administrator_date, witnessed_by, notes, side_effect, status (1-4), is_deleted, timestamps.            │
│                                       │         │ Has data for home 8: Paracetamol, Omeprazole, Ibuprofen, etc.                                         │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model `medicationLog`                 │ EXISTS  │ app/Models/medicationLog.php — $fillable, forHome() + active() scopes, user() relationship.           │
│                                       │         │ Note: `frequesncy` typo matches DB column — do NOT fix without migration.                              │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Controller methods                    │ EXISTS  │ ClientController.php — medication_log_save (line 332), medication_log_list (370), delete (386).        │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Routes                                │ EXISTS  │ POST /roster/client/medication-log-save, medication-log-list, medication-log-delete.                   │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Medication Logs tab UI                │ EXISTS  │ client_details.blade.php lines 3393-3480. Working CRUD form + card list. Keep as-is.                   │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MAR Sheets tab UI                     │ PLACEHOLDER │ Lines 3380-3391: "MAR Sheets — Coming in Phase 2" with calendar icon. REPLACE with real UI.       │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MAR detail section                    │ HARDCODED │ Lines 3486-3570+: `medicationSectionSecond` div with hardcoded Norethisterone data.                  │
│                                       │         │ Has List/Table toggle. `.marSheetDetails` click handler toggles to this view (JS line 8582).           │
│                                       │         │ REPLACE with dynamic detail view.                                                                      │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Tests                                 │ EXISTS  │ tests/Feature/MedicationLogTest.php — 12 tests for the existing medication logs.                       │
│                                       │         │ Do NOT modify these. Write separate MARSheetTest.php for new functionality.                            │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `mar_sheets`                 │ MISSING │ Prescription records table. Needs migration.                                                          │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `mar_administrations`        │ MISSING │ Individual dose tracking table. Needs migration.                                                      │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MARSheet model                        │ MISSING │ New model for prescriptions.                                                                          │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MARAdministration model               │ MISSING │ New model for dose records.                                                                           │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MARSheet service                      │ MISSING │ Business logic for prescription CRUD + administration.                                                │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MARSheet controller                   │ MISSING │ API endpoints for MAR CRUD.                                                                           │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ MAR JS file                           │ MISSING │ Separate JS for MAR sheet functionality.                                                              │
└───────────────────────────────────────┴─────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## What Needs Building (the plan)

**Goal:** Replace the "Coming in Phase 2" placeholder with a working MAR Sheet system. Staff can manage prescriptions (add/edit/discontinue medications), record daily administration at each time slot, and view a MAR grid showing medication compliance over time. All within the existing client details page — no new sidebar link needed.

**Architecture:**
- **`mar_sheets` table** — Prescription records (what the client takes, dosage, frequency, time slots)
- **`mar_administrations` table** — Individual dose records (when each dose was given/missed/refused)
- **UI lives on client_details.blade.php** — replaces the placeholder tab, reuses the detail section

**Relationship to existing medication_logs:**
- `medication_logs` = simple ad-hoc logging (existing Feature 6, stays untouched)
- `mar_sheets` + `mar_administrations` = structured prescription management + dose tracking (new)
- They are independent systems. Do NOT modify medication_logs or its endpoints.

**Scope — what we ARE building:**
- Prescription CRUD (add, edit, discontinue medications for a client)
- Daily administration recording (mark each time slot as given/refused/missed/etc.)
- MAR grid view (medications × time slots for a selected date)
- PRN (as-required) medication support
- Prescription detail view with administration history
- Stock level tracking (simple count, no inventory management)

**Scope — what we are NOT building (deferred):**
- Admin dashboard across all clients — defer
- PDF export / print MAR chart — defer
- Medication alerts / reminders — defer
- Pharmacy integration — defer
- Barcode scanning — defer

## CRITICAL — Lessons Learned from Features 1-9 (DO NOT REPEAT THESE MISTAKES)

### Mistake 1: Building UI on the wrong Blade file (Feature 7)
**What happened:** SOS Alerts were built on `dashboard.blade.php` but `/roster` renders `index.blade.php`. Button was invisible.
**Prevention:** This feature goes on `client_details.blade.php` (confirmed — it's the existing MAR placeholder location). But VERIFY by tracing: `/roster/client-details/{id}` route → controller → `return view(...)`.

### Mistake 2: User table has `name` column, NOT `first_name`/`last_name` (Feature 9)
**What happened:** Eager loading selected `first_name,last_name` but user table only has `name`.
**Prevention:** When eager loading user relationships, always use `->with(['user:id,name'])`.

### Mistake 3: Routes not whitelisted in checkUserAuth (Feature 4)
**What happened:** AJAX calls returned "unauthorize" silently because routes weren't in `$allowed_path`.
**Prevention:** Add ALL new MAR routes to `$allowed_path` in `checkUserAuth.php` DURING build, not after.

### Mistake 4: Value mapping mismatch (Feature 6)
**What happened:** Form sent values 1-4 but JS mapped 0-3. Every status badge was wrong.
**Prevention:** Administration codes (A, S, R, W, N, O) must match exactly between form options, JS display mapping, and DB stored values. Define once, use everywhere.

### Mistake 5: Column name typo in existing table (Feature 6)
**What happened:** `medication_logs` has `frequesncy` (typo) — code must match the actual DB column, not the "correct" spelling.
**Prevention:** For the NEW `mar_sheets` table, use correct spelling (`frequency`). Do NOT try to fix `medication_logs.frequesncy` — that's a separate migration.

### Mistake 6: artisan migrate fails (Features 6, 9)
**What happened:** Old broken migration `2025_11_20_111238` causes duplicate column error.
**Prevention:** Use direct SQL via `php artisan tinker` → `DB::statement('CREATE TABLE ...')` as fallback if `artisan migrate` fails.

### Mistake 7: Double-escaping in detail views (Feature 9)
**What happened:** `esc()` called inside strings passed to `detailRow()` which also calls `esc()`.
**Prevention:** Escape at ONE level only. If a render helper escapes, don't pre-escape the input.

### Mistake 8: XSS via unescaped .html() (Features 3-6)
**What happened:** Raw HTML rendering without escaping.
**Prevention:** All JS `.html()` calls must use the `esc()` helper for user data. The MAR grid renders lots of user data (medication names, notes, staff names) — every single one must be escaped.

### Mistake 9: API controller leaking exception messages (Feature 7)
**What happened:** `$e->getMessage()` returned to client in error response.
**Prevention:** All catch blocks return generic "Something went wrong" messages, never `$e->getMessage()`.

## Files to Create/Modify

### New files:
1. `database/migrations/2026_04_23_100000_create_mar_sheets_tables.php` — two tables + seed data
2. `app/Models/MARSheet.php` — prescription model with $fillable, JSON casts, relationships, scopes
3. `app/Models/MARAdministration.php` — dose record model
4. `app/Services/Staff/MARSheetService.php` — prescription CRUD + administration recording
5. `app/Http/Controllers/frontEnd/Roster/Client/MARSheetController.php` — 7 endpoints with validation
6. `public/js/roster/client/mar_sheets.js` — AJAX CRUD, MAR grid rendering, administration recording
7. `tests/Feature/MARSheetTest.php` — 14+ tests

### Modify:
8. `routes/web.php` — 7 MAR sheet routes with rate limiting
9. `app/Http/Middleware/checkUserAuth.php` — whitelist 7 new routes
10. `resources/views/frontEnd/roster/client/client_details.blade.php` — replace placeholder + detail section

### Do NOT modify:
- `app/Models/medicationLog.php` — existing model, leave as-is
- `app/Http/Controllers/frontEnd/Roster/Client/ClientController.php` — existing medication methods, leave as-is
- `tests/Feature/MedicationLogTest.php` — existing tests, leave as-is
- `public/js/roster/client/client_details.js` — existing medication JS, leave as-is (the marSheetDetails toggle at line 8582 can stay or be updated)

## DB Migration — Two Tables

### Table 1: `mar_sheets` (Prescriptions)

Based on CareRoster spec (32 fields), mapped to relational schema:

```php
Schema::create('mar_sheets', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('home_id');
    $table->unsignedInteger('client_id');

    // Medication details
    $table->string('medication_name', 255);
    $table->string('dosage', 100)->nullable();         // e.g., "500mg"
    $table->string('dose', 100)->nullable();            // e.g., "2 tablets"
    $table->string('route', 100)->nullable();           // e.g., "Oral", "Topical", "Inhaled"
    $table->string('frequency', 255)->nullable();       // e.g., "Twice daily", "Once weekly"
    $table->json('time_slots')->nullable();             // ["08:00", "14:00", "22:00"]
    $table->boolean('as_required')->default(false);     // PRN medication
    $table->text('prn_details')->nullable();            // When/why to give PRN medication
    $table->text('reason_for_medication')->nullable();  // Why prescribed

    // Prescriber & pharmacy
    $table->string('prescribed_by', 255)->nullable();   // Doctor who prescribed
    $table->string('prescriber', 255)->nullable();      // GP/specialist name
    $table->string('pharmacy', 255)->nullable();        // Dispensing pharmacy

    // Dates
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();

    // Stock
    $table->unsignedInteger('stock_level')->nullable();
    $table->unsignedInteger('reorder_level')->nullable();
    $table->text('storage_requirements')->nullable();   // e.g., "Room temperature, away from light"

    // Warnings
    $table->text('allergies_warnings')->nullable();

    // Status
    $table->string('mar_status', 20)->default('active'); // active, discontinued
    $table->boolean('discontinued')->default(false);
    $table->date('discontinued_date')->nullable();
    $table->text('discontinued_reason')->nullable();
    $table->date('last_audited')->nullable();

    // Audit
    $table->unsignedInteger('created_by');
    $table->boolean('is_deleted')->default(false);
    $table->timestamps();

    // Indexes
    $table->index('home_id');
    $table->index('client_id');
    $table->index('mar_status');
    $table->index('is_deleted');
});
```

### Table 2: `mar_administrations` (Dose Records)

```php
Schema::create('mar_administrations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('mar_sheet_id');
    $table->unsignedInteger('home_id');

    // Administration details
    $table->date('date');                               // Date of administration
    $table->string('time_slot', 10);                    // "08:00", "14:00", etc.
    $table->boolean('given')->default(false);            // Was the dose given?
    $table->string('dose_given', 100)->nullable();      // Actual dose given (may differ from prescribed)
    $table->unsignedInteger('administered_by');          // user_id of staff
    $table->string('witnessed_by', 255)->nullable();    // Witness name or user_id
    $table->string('code', 5);                          // A=Administered, S=Self, R=Refused, W=Withheld, N=Not Available, O=Other
    $table->text('notes')->nullable();

    // Audit
    $table->timestamps();

    // Indexes
    $table->index('mar_sheet_id');
    $table->index('home_id');
    $table->index('date');
    $table->index('administered_by');
    $table->index(['mar_sheet_id', 'date', 'time_slot']); // Composite for grid lookups
});
```

### Seed Data for Home 8

Seed 4-5 prescriptions for client_id 27 (Aries test client):

```
1. Metformin 500mg — Oral, Twice daily, time_slots: ["08:00", "18:00"], prescribed_by: "Dr. Helen Roberts", pharmacy: "Boots Pharmacy", active, stock_level: 56, reason: "Type 2 Diabetes management", allergies_warnings: "Penicillin allergy"
2. Paracetamol 1g — Oral, Four times daily PRN, time_slots: ["08:00", "12:00", "16:00", "22:00"], as_required: true, prn_details: "For pain relief. Max 4g in 24 hours.", active, stock_level: 30
3. Amlodipine 5mg — Oral, Once daily, time_slots: ["08:00"], prescribed_by: "Dr. Amanda Foster", active, stock_level: 28, reason: "Hypertension"
4. Gabapentin 300mg — Oral, Three times daily, time_slots: ["08:00", "14:00", "22:00"], prescribed_by: "Dr. James Park, Pain Specialist", active, stock_level: 84, reason: "Chronic neuropathic pain"
5. Folic Acid 5mg — Oral, Once daily, time_slots: ["08:00"], discontinued: true, discontinued_date: "2026-03-01", discontinued_reason: "Patient refused — later informed not needed", mar_status: "discontinued"
```

Also seed 6-8 administration records for today and yesterday to populate the grid:
```
- Metformin: yesterday 08:00 given (code A), yesterday 18:00 given (code A)
- Metformin: today 08:00 given (code A), today 18:00 not yet recorded
- Amlodipine: yesterday 08:00 given (code A), today 08:00 given (code S — self-administered)
- Gabapentin: yesterday 08:00 given, yesterday 14:00 refused (code R, notes: "Patient feeling nauseous")
- Paracetamol: yesterday 08:00 given (PRN, notes: "Complained of headache")
```

## Step-by-step Implementation

### Step 0: Pre-flight checks (MANDATORY)
- Confirm `SHOW TABLES LIKE '%mar%'` returns zero — no existing MAR tables
- Confirm `medication_logs` table structure matches known columns (especially the `frequesncy` typo)
- Trace `/roster/client-details/27` route → controller → view to confirm `client_details.blade.php` is correct
- Locate the "Coming in Phase 2" placeholder (line ~3387) and the hardcoded detail section (line ~3486)
- Check `checkUserAuth.php` for existing medication routes to understand the pattern
- Confirm client_id 27 exists for home 8: `SELECT id, first_name FROM clients WHERE id = 27`
  - NOTE: Check if clients table uses `first_name`/`last_name` or `name` — don't assume!

### Step 1: Migration + Seed Data
- Create both tables using schema above
- Seed 5 prescriptions + 8 administration records for home 8, client 27
- Run migration (use `DB::statement()` via tinker as fallback if `artisan migrate` fails)
- Verify: `SELECT COUNT(*) FROM mar_sheets WHERE home_id = 8` → 5
- Verify: `SELECT COUNT(*) FROM mar_administrations WHERE home_id = 8` → 8

### Step 2: Create Models

**app/Models/MARSheet.php:**
- `$table = 'mar_sheets'`
- `$fillable` whitelist — every prescription field (NOT home_id, created_by, is_deleted)
- `$casts`: time_slots (array), as_required (boolean), discontinued (boolean), start_date (date), end_date (date), discontinued_date (date), last_audited (date), stock_level (integer), reorder_level (integer)
- Scopes: `scopeForHome($query, $homeId)`, `scopeActive($query)` (is_deleted=0), `scopeCurrentlyActive($query)` (mar_status='active')
- Relationships: `administrations()` → hasMany MARAdministration, `createdByUser()` → belongsTo User
- Helper: `getTimeSlotsAttribute()` should return decoded JSON or empty array

**app/Models/MARAdministration.php:**
- `$table = 'mar_administrations'`
- `$fillable`: mar_sheet_id, date, time_slot, given, dose_given, administered_by, witnessed_by, code, notes
- `$casts`: date (date), given (boolean)
- Scopes: `scopeForHome($query, $homeId)`, `scopeForDate($query, $date)`
- Relationships: `marSheet()` → belongsTo MARSheet, `administeredByUser()` → belongsTo User

### Step 3: Create Service (app/Services/Staff/MARSheetService.php)

Methods:
- `store(array $data, int $homeId, int $userId)` — create prescription, set home_id/created_by server-side
- `update(int $id, array $data, int $homeId)` — update prescription, verify home_id
- `list(int $clientId, int $homeId, ?string $status)` — list prescriptions for a client (active/discontinued/all), eager load recent administrations
- `details(int $id, int $homeId)` — single prescription with all administrations
- `delete(int $id, int $homeId)` — soft delete (is_deleted=1), admin only
- `discontinue(int $id, array $data, int $homeId)` — set discontinued=true, mar_status='discontinued', discontinued_date, discontinued_reason
- `administer(int $marSheetId, array $data, int $homeId, int $userId)` — record a dose: validate mar_sheet belongs to home, create administration record
- `getAdministrationsForDate(int $clientId, int $homeId, string $date)` — get all administrations for a client on a specific date (for the MAR grid)

Every method:
- Filters by home_id (IDOR prevention)
- Log::info() on mutations with actor ID
- Generic error messages only

### Step 4: Create Controller (app/Http/Controllers/frontEnd/Roster/Client/MARSheetController.php)

Endpoints:
- `list(Request $request)` — POST, validates client_id + optional status filter
- `save(Request $request)` — POST, full validation on all prescription fields
- `update(Request $request)` — POST, validates id + prescription fields (nullable for partial update)
- `details(Request $request)` — POST, validates id
- `delete(Request $request)` — POST, validates id, admin-only check
- `discontinue(Request $request)` — POST, validates id + discontinued_reason
- `administer(Request $request)` — POST, validates mar_sheet_id + date + time_slot + code + dose_given
- `administrationGrid(Request $request)` — POST, validates client_id + date, returns all prescriptions with their administrations for that date

All methods get home_id from `(int) explode(',', Auth::user()->home_id)[0]`.

**Validation rules for save:**
```php
$request->validate([
    'client_id'              => 'required|integer',
    'medication_name'        => 'required|string|max:255',
    'dosage'                 => 'nullable|string|max:100',
    'dose'                   => 'nullable|string|max:100',
    'route'                  => 'nullable|string|max:100',
    'frequency'              => 'nullable|string|max:255',
    'time_slots'             => 'nullable|array',
    'time_slots.*'           => 'string|max:10',
    'as_required'            => 'nullable|boolean',
    'prn_details'            => 'nullable|string|max:2000',
    'reason_for_medication'  => 'nullable|string|max:2000',
    'prescribed_by'          => 'nullable|string|max:255',
    'prescriber'             => 'nullable|string|max:255',
    'pharmacy'               => 'nullable|string|max:255',
    'start_date'             => 'nullable|date',
    'end_date'               => 'nullable|date|after_or_equal:start_date',
    'stock_level'            => 'nullable|integer|min:0',
    'reorder_level'          => 'nullable|integer|min:0',
    'storage_requirements'   => 'nullable|string|max:1000',
    'allergies_warnings'     => 'nullable|string|max:1000',
]);
```

**Validation rules for administer:**
```php
$request->validate([
    'mar_sheet_id'  => 'required|integer',
    'date'          => 'required|date',
    'time_slot'     => 'required|string|max:10',
    'code'          => 'required|in:A,S,R,W,N,O',
    'given'         => 'required|boolean',
    'dose_given'    => 'nullable|string|max:100',
    'witnessed_by'  => 'nullable|string|max:255',
    'notes'         => 'nullable|string|max:2000',
]);
```

### Step 5: Routes + Middleware

Routes (inside roster client group, with rate limiting):
```
POST /roster/client/mar-sheet-list           → MARSheetController@list           → throttle:30,1
POST /roster/client/mar-sheet-save           → MARSheetController@save           → throttle:20,1
POST /roster/client/mar-sheet-update         → MARSheetController@update         → throttle:20,1
POST /roster/client/mar-sheet-details        → MARSheetController@details        → throttle:30,1
POST /roster/client/mar-sheet-delete         → MARSheetController@delete         → throttle:20,1
POST /roster/client/mar-sheet-discontinue    → MARSheetController@discontinue    → throttle:20,1
POST /roster/client/mar-administer           → MARSheetController@administer     → throttle:30,1
POST /roster/client/mar-administration-grid  → MARSheetController@administrationGrid → throttle:30,1
```

Whitelist ALL 8 routes in `checkUserAuth.php` `$allowed_path`.

### Step 6: Replace UI in client_details.blade.php

**CRITICAL:** Do NOT add a sidebar link. The MAR sheet is accessed from the client details page → Medication tab → MAR Sheets sub-tab. The entry point already exists.

**Replace the "Coming in Phase 2" placeholder** (lines ~3385-3389) with:

**MAR Sheets Panel (replaces placeholder):**
1. **Header bar:** "MAR Sheets" title + "Add Prescription" button
2. **Filter:** Status toggle (Active / Discontinued / All)
3. **Prescription list** (`#mar-sheet-list`): Cards showing:
   - Medication name + dosage badge
   - Route + frequency
   - Time slots as small badges (e.g., `08:00` `14:00` `22:00`)
   - PRN flag if as_required
   - Status badge: Active (green) / Discontinued (grey)
   - Stock level indicator (if tracked): green if > reorder_level, amber if ≤, red if 0
   - "View MAR" button → opens detail/grid view
   - "Edit" button → opens edit form
   - "Discontinue" button (for active prescriptions)
   - "Delete" button (admin only)
4. **Pagination** (`#mar-sheet-pagination`)

**Replace the hardcoded detail section** (lines ~3486-3570+) with:

**MAR Detail View (`medicationSectionSecond`):**
1. **Back button** — returns to prescription list
2. **Prescription header**: medication name, dosage, route, frequency, prescriber, pharmacy, status
3. **Date picker**: defaults to today, allows browsing previous days
4. **MAR Administration Grid**:
   - If medication has defined `time_slots`: show one column per time slot
   - Each cell shows administration status for that time slot on the selected date:
     - Empty (grey) = not yet administered
     - Green checkmark (✓) = Given (code A or S)
     - Red X = Refused (code R)
     - Amber ⊘ = Withheld (code W)
     - Grey dash = Not Available (code N)
   - Click empty cell → opens "Administer" modal
   - Click filled cell → shows administration details (who, when, notes)
   - PRN medications: show a single "Record PRN" button instead of time slot grid
5. **Administration History**: scrollable list of recent administrations for this prescription
6. **Prescription Details Panel**: collapsible section with full prescription info (prescriber, pharmacy, start_date, stock, allergies, storage)

**Add Prescription Modal / Form:**
- Inline form (matching existing medication log form style) OR modal
- Fields: medication_name*, dosage, dose, route (dropdown: Oral/Topical/Inhaled/Injection/Sublingual/Rectal/Other), frequency, time_slots (dynamic add/remove), as_required checkbox + prn_details, prescribed_by, prescriber, pharmacy, start_date, end_date, stock_level, reorder_level, storage_requirements, allergies_warnings, reason_for_medication
- Time slots: "Add Time Slot" button → adds an `<input type="time">` field. Can add multiple. Can remove.

**Administer Modal:**
- Small modal that opens when clicking a grid cell
- Shows: medication name, dosage, time slot, date
- Fields: code* (dropdown: A=Administered, S=Self-administered, R=Refused, W=Withheld, N=Not Available, O=Other), dose_given (pre-filled with prescription dosage), witnessed_by, notes
- "given" boolean auto-set based on code (A, S → true; R, W, N, O → false)

### Step 7: JavaScript (public/js/roster/client/mar_sheets.js)

Include this file in client_details.blade.php:
```html
<script src="{{ url('public/js/roster/client/mar_sheets.js') }}"></script>
```

**Functions:**
- `esc(str)` — XSS escape helper (same as other features)
- `loadPrescriptions(clientId, status)` — AJAX POST to /mar-sheet-list → render prescription cards
- `renderPrescriptionList(prescriptions)` — builds card HTML with esc() on ALL user data
- `savePrescription(formData)` — POST to /mar-sheet-save → refresh list
- `updatePrescription(id, formData)` — POST to /mar-sheet-update → refresh
- `loadDetails(id)` — POST to /mar-sheet-details → show detail view
- `deletePrescription(id)` — confirm → POST to /mar-sheet-delete → refresh
- `discontinuePrescription(id)` — prompt for reason → POST to /mar-sheet-discontinue → refresh
- `loadAdministrationGrid(clientId, date)` — POST to /mar-administration-grid → render grid
- `renderGrid(prescriptions, administrations, date)` — build the time-slot grid
- `administerDose(marSheetId, date, timeSlot)` — opens administer modal
- `saveAdministration(data)` — POST to /mar-administer → refresh grid cell

**Code mapping (MUST match DB values exactly):**
```javascript
var codeMappings = {
    'A': { label: 'Administered', color: '#27ae60', icon: '✓', given: true },
    'S': { label: 'Self-administered', color: '#2ecc71', icon: '✓', given: true },
    'R': { label: 'Refused', color: '#e74c3c', icon: '✗', given: false },
    'W': { label: 'Withheld', color: '#f39c12', icon: '⊘', given: false },
    'N': { label: 'Not Available', color: '#95a5a6', icon: '—', given: false },
    'O': { label: 'Other', color: '#7f8c8d', icon: '?', given: false }
};
```

**AJAX setup:**
- Get CSRF token from `$('meta[name="csrf-token"]').attr('content')` or `$('input[name="_token"]').first().val()`
- `$.ajaxSetup({ headers: {'X-CSRF-TOKEN': csrfToken} })`
- All AJAX calls have `error:` callbacks with user-visible messages
- `baseUrl` from meta tag or `window.location.origin`

### Step 8: Seed Data Verification
After building, verify:
- [ ] 5 prescriptions appear in MAR Sheet list for client 27
- [ ] 1 is discontinued (Folic Acid) with grey badge
- [ ] 4 are active with green badges
- [ ] Time slot badges render correctly (e.g., Metformin shows "08:00" "18:00")
- [ ] MAR grid shows administration data for yesterday/today
- [ ] PRN medication (Paracetamol) shows "Record PRN" button, not time slot grid
- [ ] Stock levels show correct colors

### Step 9: Write Tests (14+)

**Auth tests (3):**
- list, save, administer all reject unauthenticated

**Validation tests (3):**
- save rejects missing medication_name (422)
- administer rejects invalid code (422, not in A,S,R,W,N,O)
- save rejects negative stock_level (422)

**Flow test (1):**
- Create prescription → appears in list → update dosage → details reflect change → administer a dose → grid shows it → discontinue → status changes → delete → gone from list

**IDOR tests (4):**
- list doesn't leak cross-home prescriptions
- details rejects cross-home prescription ID (404)
- update rejects cross-home prescription ID (404)
- administer rejects cross-home prescription (can't record dose for another home's prescription)

**Security tests (3):**
- XSS: `<script>alert(1)</script>` in medication_name — stored raw, returned for JS to escape
- Mass assignment: home_id=999, created_by=1 in save request — ignored, server values used
- Admin-only delete: non-admin user → 403

**Functional tests (1+):**
- Duplicate administration: recording same mar_sheet_id + date + time_slot twice should update (not create duplicate)

## Security Checklist

┌───────────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  Attack Surface   │                                              Protection                                                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Input validation  │ Full validation on save (see rules above). id/mar_sheet_id: required|integer on all endpoints.         │
│                   │ code: required|in:A,S,R,W,N,O. stock_level: nullable|integer|min:0.                                    │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting     │ list/details/grid: throttle:30,1; save/update/delete/discontinue/administer: throttle:20,1             │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (client-side) │ esc() on ALL .html() insertions — medication_name, notes, reason_for_medication, allergies_warnings    │
│                   │ are highest risk (free-text). Grid cells with staff names also need escaping.                           │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (server-side) │ {{ }} only in Blade — never {!! !!} for any user data                                                 │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ IDOR              │ Every endpoint filters by home_id. Prescriptions + administrations both verify home_id.                │
│                   │ Administer must verify the mar_sheet's home_id matches the user's home.                                │
│                   │ List must verify client belongs to the user's home (or filter prescriptions by home_id).                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment   │ $fillable whitelist on both models. home_id and created_by set server-side, never from request.        │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ CSRF              │ _token sent in all AJAX requests via $.ajaxSetup headers.                                              │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Access control    │ Any authenticated user can create prescriptions and record administrations for their home.              │
│                   │ Only admin (user_type === 'A') can delete prescriptions.                                               │
│                   │ Any user can discontinue (it's a clinical action, not destructive).                                     │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Error leaking     │ All catch blocks return generic message. No $e->getMessage() to client.                                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ SQL injection     │ Eloquent only. No DB::raw() with user input. time_slot is a string field, validate format.             │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ home_id type      │ Both new tables use INTEGER home_id. Simple WHERE home_id = X.                                         │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Duplicate doses   │ Composite check on (mar_sheet_id, date, time_slot) before inserting — updateOrCreate to prevent         │
│                   │ double-recording at the same time slot.                                                                 │
└───────────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Two tables, not one.** Prescriptions (`mar_sheets`) and dose records (`mar_administrations`) are separate tables. CareRoster stores `administration_records` as JSON within each MAR sheet — we use a proper relational table for queryability, audit trails, and race condition prevention.

2. **Independent of medication_logs.** The existing `medication_logs` is a simple ad-hoc log (Feature 6). MAR sheets are structured prescription management. They serve different purposes and coexist. No foreign keys between them.

3. **No sidebar link.** Feature lives within client details → Medication tab → MAR Sheets sub-tab. The tab button already exists (line 3376). The entry point is already wired.

4. **Administration codes follow NHS MAR conventions:** A (Administered), S (Self-administered), R (Refused), W (Withheld), N (Not Available), O (Other). The `given` boolean is derived from the code (A/S = true, everything else = false).

5. **PRN medications** have `as_required=true` and display a "Record PRN" button instead of a fixed time-slot grid, since PRN doses are given on demand.

6. **Duplicate prevention** via `updateOrCreate` on (mar_sheet_id, date, time_slot) — if a dose was already recorded for that slot, it gets updated rather than duplicated.

7. **Stock tracking is simple.** Just a `stock_level` integer that staff manually update. No automatic decrement on administration (too error-prone for Phase 1). Reorder alerts are visual only (amber/red badge when stock ≤ reorder_level).

8. **The `marSheetDetails` click handler** (JS line 8582) already toggles between `medicationSectionFirst` and `medicationSectionSecond`. We reuse this toggle mechanism — prescription list in first section, detail/grid in second section.

## Post-Build Verification Checklist (MANDATORY)

After completing the build, verify ALL of these before moving to TEST:
- [ ] `curl http://127.0.0.1:8000/roster/client-details/27` returns 200 and contains "Add Prescription" button
- [ ] "Coming in Phase 2" text is GONE from the response
- [ ] All 8 new routes are in `$allowed_path` in checkUserAuth.php
- [ ] AJAX list endpoint returns prescriptions for client 27, home 8
- [ ] No `{!! !!}` in modified sections of client_details.blade.php
- [ ] All `.html()` calls in mar_sheets.js use `esc()` for user data
- [ ] Save prescription works — new record appears in list
- [ ] Administer dose works — grid cell updates
- [ ] Discontinue works — prescription status changes to grey
- [ ] Delete works for admin user
- [ ] MAR grid renders time slot columns correctly
- [ ] PRN medication shows "Record PRN" button, not time slot grid
- [ ] Existing Medication Logs tab still works (no regressions)
- [ ] Stock level badges render with correct colors
- [ ] Date picker on grid view allows browsing previous days
