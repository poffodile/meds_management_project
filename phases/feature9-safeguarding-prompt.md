WORKFLOW: Feature 9 — Safeguarding Referrals
Run `/careos-workflow` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

## Documents to Read Before Starting

Read these files at the START of the session before writing any code:

| Document | Path | Why |
|----------|------|-----|
| **Session logs** | `docs/logs.md` | Prior context, past mistakes, teaching notes from Features 1-8 |
| **Security checklist** | `docs/security-checklist.md` | 15-item vulnerability checklist + grep patterns for AUDIT stage |
| **CareRoster Safeguarding schema** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/SafeguardingReferral.md` | Reference spec — 39 fields, status workflow, multi-agency notifications |
| **CareRoster Safeguarding data** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/SafeguardingReferral.json` | 5 sample records — includes a rich financial abuse case with full lifecycle |
| **Existing SafeguardingType model** | `app/Models/Staff/SafeguardingType.php` | 10 abuse types (Physical, Emotional, Neglect, Domestic, Self-Neglect, Sexual, Financial, Discriminatory, Modern Slavery, Organisational) |
| **Existing junction model** | `app/Models/Staff/StaffReportIncidentsSafeguarding.php` | Links incidents to safeguarding types — understand the existing relationship pattern |
| **Backend SafeguardingType controller** | `app/Http/Controllers/backEnd/homeManage/SafeguardingTypeController.php` | Admin CRUD for safeguarding types — understand existing patterns, note security issues to avoid (leaks $e->getMessage()) |
| **Incident integration** | `app/Services/Staff/StaffReportIncidentService.php` | Lines 22-24: incidents with is_safeguarding=1 sync safeguarding types — understand the link |
| **Roster header** | `resources/views/frontEnd/roster/common/roster_header.blade.php` | Sidebar navigation — NO safeguarding link exists yet, needs adding |
| **Roster index page** | `resources/views/frontEnd/roster/index.blade.php` | The ACTUAL dashboard page at `/roster` — NOT dashboard.blade.php |
| **Phase 1 plan** | `phases/phase1.md` | Feature 9 scope, key rules |
| **CLAUDE.md** | `CLAUDE.md` | Project conventions, security rules, roster page mapping |

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Feature 9 — Safeguarding Referrals
[ ] PLAN     — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration for safeguarding_referrals table + model + service + controller
[ ] BUILD    — List view, create/edit form (multi-step), detail view with timeline, sidebar link
[ ] TEST     — Unit + IDOR + security payload tests (12+)
[ ] DEBUG    — Clear laravel.log, hit all endpoints, check for errors
[ ] REVIEW   — Adversarial curl attacks (use the fixed login command from workflow)
[ ] AUDIT    — Grep patterns + regression check
[ ] PROD-READY — Curl-verified + manual checklist, user confirms "tested"
[ ] PUSH     — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## What Exists (30% done — type management + incident linking only, no referral system)

┌───────────────────────────────────────┬─────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│               Component               │ Status  │                                                 Details                                                │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `safeguarding_types`         │ EXISTS  │ 10 abuse types for home 1: Physical, Emotional/Psychological, Neglect, Domestic,                      │
│                                       │         │ Self-Neglect, Sexual, Financial, Discriminatory, Modern Slavery, Organisational.                       │
│                                       │         │ Columns: id, home_id, type, status, deleted_at, created_at, updated_at.                               │
│                                       │         │ NOTE: Only home_id=1 has types. Home 8 (Aries) has NONE — need to seed.                               │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `staff_report_incidents_     │ EXISTS  │ Junction table linking incidents to safeguarding types.                                               │
│ safeguardings`                        │         │ Columns: id, staff_report_incident_id, safeguarding_type_id.                                          │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model `SafeguardingType`              │ EXISTS  │ app/Models/Staff/SafeguardingType.php — $fillable: id, home_id, type, status. SoftDeletes.            │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model `StaffReportIncidentsSafeguarding` │ EXISTS │ app/Models/Staff/StaffReportIncidentsSafeguarding.php — junction model with incident() and           │
│                                       │         │ safeguardingType() relationships. No timestamps.                                                      │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Backend controller                    │ EXISTS  │ SafeguardingTypeController.php — admin CRUD for types (index, save, delete, status_change).           │
│                                       │         │ SECURITY ISSUES: leaks $e->getMessage() in error responses (3 places). Do NOT copy this pattern.     │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Backend view                          │ EXISTS  │ backEnd/homeManage/safeguarding_type.blade.php — admin page for managing types.                      │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Backend routes                        │ EXISTS  │ /safeguarding-type/* — admin routes for type management only.                                        │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Incident linking                      │ EXISTS  │ IncidentManagementController loads safeguarding types for the form.                                   │
│                                       │         │ StaffReportIncidentService syncs safeguarding types when is_safeguarding=1.                           │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `safeguarding_referrals`     │ MISSING │ The main case tracking table. Needs migration with ~30 columns from CareRoster spec.                 │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ SafeguardingReferral model            │ MISSING │ New model needed with $fillable, casts (JSON for witnesses/alleged_perpetrator/strategy_meeting/      │
│                                       │         │ safeguarding_plan), relationships, scopes.                                                            │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Safeguarding service                  │ MISSING │ New service for store, update, list, details, delete — all home_id scoped.                            │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Frontend controller                   │ MISSING │ New controller for roster-side safeguarding referral pages.                                           │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Frontend views                        │ MISSING │ List view, create/edit form, detail view — all need building.                                        │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Frontend routes                       │ MISSING │ No web routes for safeguarding referral pages.                                                        │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Sidebar link                          │ MISSING │ No "Safeguarding" link in roster_header.blade.php sidebar.                                           │
└───────────────────────────────────────┴─────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## What Needs Building (the plan)

**Goal:** A Safeguarding Referral system at `/roster/safeguarding` where staff can raise safeguarding concerns, track cases through the full lifecycle (reported → investigation → plan → outcome → closed), with multi-agency notification tracking, witnesses, alleged perpetrator details, and strategy meeting records. Admin/managers see all referrals for their home; staff see ones they reported.

**Scope decision:** This is Phase 1 (Patch & Polish). We are NOT building:
- AI-generated safeguarding reports — Phase 3
- Automatic CQC/police notification sending (email/SMS) — Phase 2
- Document/photo attachments — Phase 2
- Integration with external safeguarding boards — Phase 2

## CRITICAL — Lessons Learned from Features 1-8 (DO NOT REPEAT THESE MISTAKES)

### Mistake 1: Building UI on the wrong Blade file (Feature 7)
**What happened:** SOS Alerts were built on `dashboard.blade.php` but `/roster` renders `index.blade.php`. Button was invisible.
**Prevention:** BEFORE writing any UI code:
1. Identify the target URL from the sidebar link
2. Trace: route in `web.php` → controller method → `return view(...)` → actual Blade file
3. Safeguarding is a NEW page at `/roster/safeguarding` — trace the route you create to confirm the view name matches
4. After build, `curl` the URL and grep for a unique element to confirm it renders

### Mistake 2: UI entry point commented out or unreachable (Feature 4)
**What happened:** Handover link was `<!-- commented out -->` in the sidebar. Feature was invisible.
**Prevention:** After adding the sidebar link:
1. `curl` the roster page and grep for the safeguarding link
2. Verify the `href` points to `/roster/safeguarding` (not `#!`)
3. Verify it's NOT inside `<!-- -->` or `{{-- --}}` comments

### Mistake 3: Routes not whitelisted in checkUserAuth (Feature 4)
**What happened:** AJAX calls returned "unauthorize" silently because routes weren't in `$allowed_path`.
**Prevention:** Add ALL new routes to `$allowed_path` in `app/Http/Middleware/checkUserAuth.php` DURING build, not after.

### Mistake 4: Value mapping mismatch (Feature 6)
**What happened:** Form sent values 1-4 but JS mapped 0-3. Every status badge was wrong.
**Prevention:** Status values (reported, under_investigation, safeguarding_plan, closed) and risk levels (low, medium, high, critical) must match between form selects, JS badge rendering, and DB enum values exactly. Define once, use everywhere.

### Mistake 5: XSS via {!! !!} or unescaped .html() (Features 3-6)
**What happened:** Raw HTML rendering without escaping.
**Prevention:**
- {{ }} only in Blade, never {!! !!} for user data
- All JS `.html()` calls must use the `esc()` helper for user data
- Safeguarding records contain sensitive free-text (details_of_concern, outcome_details, witness statements) — ALWAYS escape

### Mistake 6: Missing home_id filtering / IDOR (Features 3-5)
**What happened:** Endpoints returned data from other homes.
**Prevention:** Every query in the safeguarding service must filter by `home_id`. The new `safeguarding_referrals` table should use integer `home_id` (not comma-separated like the notification table).

### Mistake 7: No test data (Feature 7)
**What happened:** Feature built but no data to display during testing.
**Prevention:** No safeguarding_referrals table exists yet. After migration, seed 3-5 sample referrals for home 8 (Aries) covering different statuses and risk levels. Also check safeguarding_types — only home_id=1 has types, need to seed for home 8 too.

### Mistake 8: API controller leaking exception messages (Feature 7)
**What happened:** `$e->getMessage()` returned to client in error response.
**Prevention:** All catch blocks return generic "Something went wrong" messages, never `$e->getMessage()`. NOTE: The existing SafeguardingTypeController.php has this exact bug in 3 places — do NOT copy that pattern.

### Mistake 9: Safeguarding types only exist for home_id=1 (NEW)
**What happened:** `SELECT * FROM safeguarding_types` shows all 10 types belong to home_id=1. Home 8 (Aries, our test home) has zero types.
**Prevention:** Either seed safeguarding_types for home 8, OR use a global types approach where types without a home_id (or home_id=0) are available to all homes.

## Files to Create/Modify

### New files:
1. `database/migrations/XXXX_create_safeguarding_referrals_table.php` — new table with ~30 columns
2. `app/Models/SafeguardingReferral.php` — model with $fillable, JSON casts, relationships, scopes
3. `app/Services/Staff/SafeguardingService.php` — store, update, list, details, delete (all home_id scoped)
4. `app/Http/Controllers/frontEnd/Roster/SafeguardingController.php` — index, save, update, list, details, delete, statusChange
5. `resources/views/frontEnd/roster/safeguarding.blade.php` — list view + create/edit modal + detail modal
6. `public/js/roster/safeguarding.js` — AJAX CRUD, status badges, form handling, esc() on all user data
7. `tests/Feature/SafeguardingTest.php` — 12+ tests covering auth, IDOR, validation, flow, XSS

### Modify:
8. `routes/web.php` — add safeguarding routes inside roster group with rate limiting
9. `app/Http/Middleware/checkUserAuth.php` — whitelist new safeguarding routes
10. `resources/views/frontEnd/roster/common/roster_header.blade.php` — add "Safeguarding" sidebar link

## DB Migration — `safeguarding_referrals` table

Based on CareRoster spec (39 fields), trimmed to what's practical for Phase 1:

```php
Schema::create('safeguarding_referrals', function (Blueprint $table) {
    $table->id();
    $table->unsignedInteger('home_id');
    $table->unsignedInteger('client_id')->nullable();
    $table->string('reference_number', 50)->nullable();
    
    // Concern details
    $table->unsignedInteger('reported_by');              // user_id of reporter
    $table->dateTime('date_of_concern');
    $table->string('location_of_incident', 500)->nullable();
    $table->text('details_of_concern');
    $table->text('immediate_action_taken')->nullable();
    
    // Classification
    $table->json('safeguarding_type');                   // array: ["financial_abuse", "neglect"]
    $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
    $table->enum('status', ['reported', 'under_investigation', 'safeguarding_plan', 'closed'])->default('reported');
    $table->boolean('ongoing_risk')->default(false);
    
    // People involved
    $table->json('alleged_perpetrator')->nullable();     // {name, relationship, details}
    $table->json('witnesses')->nullable();               // [{name, role, statement}]
    $table->boolean('capacity_to_make_decisions')->nullable();
    $table->text('client_wishes')->nullable();
    
    // Multi-agency notifications
    $table->boolean('police_notified')->default(false);
    $table->string('police_reference', 100)->nullable();
    $table->dateTime('police_notification_date')->nullable();
    $table->boolean('local_authority_notified')->default(false);
    $table->string('local_authority_reference', 100)->nullable();
    $table->dateTime('local_authority_notification_date')->nullable();
    $table->boolean('cqc_notified')->default(false);
    $table->dateTime('cqc_notification_date')->nullable();
    $table->boolean('family_notified')->default(false);
    $table->text('family_notification_details')->nullable();
    $table->boolean('advocate_involved')->default(false);
    $table->text('advocate_details')->nullable();
    
    // Strategy meeting
    $table->json('strategy_meeting')->nullable();        // {required, date, outcome}
    
    // Plan & outcome
    $table->json('safeguarding_plan')->nullable();       // {agreed_actions[], responsible_persons[], timescales, monitoring}
    $table->string('outcome', 50)->nullable();           // substantiated, partially_substantiated, unsubstantiated, inconclusive
    $table->text('outcome_details')->nullable();
    $table->text('lessons_learned')->nullable();
    $table->dateTime('closed_date')->nullable();
    
    // Audit
    $table->unsignedInteger('created_by');
    $table->boolean('is_deleted')->default(false);
    $table->timestamps();
    
    // Indexes
    $table->index('home_id');
    $table->index('client_id');
    $table->index('status');
    $table->index('risk_level');
    $table->index('is_deleted');
});
```

**IMPORTANT — also seed safeguarding_types for home 8:**
```sql
INSERT INTO safeguarding_types (home_id, type, status, created_at, updated_at) VALUES
(8, 'Physical Abuse', 1, NOW(), NOW()),
(8, 'Emotional/Psychological Abuse', 1, NOW(), NOW()),
(8, 'Neglect', 1, NOW(), NOW()),
(8, 'Domestic Abuse', 1, NOW(), NOW()),
(8, 'Self-Neglect', 1, NOW(), NOW()),
(8, 'Sexual Abuse', 1, NOW(), NOW()),
(8, 'Financial Abuse', 1, NOW(), NOW()),
(8, 'Discriminatory Abuse', 1, NOW(), NOW()),
(8, 'Modern Slavery', 1, NOW(), NOW()),
(8, 'Organisational Abuse', 1, NOW(), NOW());
```

## Step-by-step Implementation

### Step 0: Pre-flight checks (MANDATORY)
- Confirm `safeguarding_types` table has types for home 1 only — plan to seed for home 8
- Trace `/roster` route → confirm `index.blade.php` (NOT `dashboard.blade.php`)
- Confirm NO safeguarding link exists in `roster_header.blade.php` sidebar
- Trace where the sidebar link should go by examining existing sidebar links (SOS, Notifications, Incidents)
- Check for any existing `safeguarding_referrals` table: `SHOW TABLES LIKE '%safeguard%'`

### Step 1: Migration + Seed Data
- Create `safeguarding_referrals` table with schema above
- Seed safeguarding_types for home 8 (10 types matching home 1)
- Seed 3-5 sample referrals for home 8 covering: reported (low), under_investigation (high), safeguarding_plan (critical), closed (medium) — use realistic care home scenarios
- Run migration and verify: `SELECT COUNT(*) FROM safeguarding_referrals WHERE home_id = 8`

### Step 2: Create Model (app/Models/SafeguardingReferral.php)
- `$table = 'safeguarding_referrals'`
- `$fillable` whitelist — every column except id, created_at, updated_at
- `$casts`: safeguarding_type (array), alleged_perpetrator (array), witnesses (array), strategy_meeting (array), safeguarding_plan (array)
- Scopes: `scopeForHome($query, $homeId)`, `scopeActive($query)` (is_deleted=0)
- Relationships: `reportedByUser()`, `createdByUser()`, `client()` (if client table exists)
- Auto-generate reference_number on create: `SAFE-{YYYY}-{MM}-{sequential}`

### Step 3: Create Service (app/Services/Staff/SafeguardingService.php)
- `store(array $data, int $homeId, int $userId)` — validate, set home_id server-side, generate ref number
- `update(int $id, array $data, int $homeId)` — verify home_id match before update
- `list(int $homeId, ?string $status, ?string $riskLevel, ?string $search, int $page)` — paginated, filterable
- `details(int $id, int $homeId)` — single record with home_id verification
- `delete(int $id, int $homeId)` — soft delete (is_deleted=1), verify home_id match
- `statusChange(int $id, string $newStatus, int $homeId)` — validate status transition, verify home_id
- Every method filters by home_id (IDOR prevention) and is_deleted=0
- Log::info() on create, update, delete, status change with actor ID

### Step 4: Create Controller (app/Http/Controllers/frontEnd/Roster/SafeguardingController.php)
- `index()` — return safeguarding Blade view (GET /roster/safeguarding)
- `list(Request $request)` — validate filters, call service, return JSON (POST)
- `save(Request $request)` — full validation on all fields, call service store, return JSON (POST)
- `update(Request $request)` — validate id + fields, call service update, return JSON (POST)
- `details(Request $request)` — validate id, call service details, return JSON (POST)
- `delete(Request $request)` — validate id, call service delete, return JSON (POST)
- `statusChange(Request $request)` — validate id + status, call service, return JSON (POST)
- All methods get home_id from Auth::user() via `explode(',', $homeIds)[0]`
- Error responses must NOT leak exception messages

**Validation rules for save:**
```php
$request->validate([
    'client_id'             => 'nullable|integer',
    'date_of_concern'       => 'required|date',
    'location_of_incident'  => 'nullable|string|max:500',
    'details_of_concern'    => 'required|string|max:5000',
    'immediate_action_taken'=> 'nullable|string|max:5000',
    'safeguarding_type'     => 'required|array|min:1',
    'safeguarding_type.*'   => 'string|max:50',
    'risk_level'            => 'required|in:low,medium,high,critical',
    'ongoing_risk'          => 'required|boolean',
    'alleged_perpetrator'   => 'nullable|array',
    'witnesses'             => 'nullable|array',
    'capacity_to_make_decisions' => 'nullable|boolean',
    'client_wishes'         => 'nullable|string|max:5000',
    'police_notified'       => 'nullable|boolean',
    'police_reference'      => 'nullable|string|max:100',
    'local_authority_notified' => 'nullable|boolean',
    'local_authority_reference' => 'nullable|string|max:100',
    'cqc_notified'          => 'nullable|boolean',
    'family_notified'       => 'nullable|boolean',
    'family_notification_details' => 'nullable|string|max:2000',
    'advocate_involved'     => 'nullable|boolean',
    'advocate_details'      => 'nullable|string|max:2000',
]);
```

### Step 5: Routes + Middleware
Routes (inside roster group, with rate limiting + route constraints):
```
GET  /roster/safeguarding                → SafeguardingController@index
POST /roster/safeguarding/list           → throttle:30,1
POST /roster/safeguarding/save           → throttle:20,1
POST /roster/safeguarding/update         → throttle:20,1
POST /roster/safeguarding/details        → throttle:30,1
POST /roster/safeguarding/delete         → throttle:20,1
POST /roster/safeguarding/status-change  → throttle:20,1
```

Whitelist in checkUserAuth.php `$allowed_path`:
- `roster/safeguarding`, `roster/safeguarding/list`, `roster/safeguarding/save`, `roster/safeguarding/update`, `roster/safeguarding/details`, `roster/safeguarding/delete`, `roster/safeguarding/status-change`

### Step 6: Add sidebar link
In `roster_header.blade.php`, add a "Safeguarding" link near the existing Incident/SOS links:
- `<a href="{{ url('/roster/safeguarding') }}"><i class='bx bx-shield-alt-2'></i> Safeguarding</a>`
- Follow the same HTML structure as nearby sidebar items

### Step 7: Create Safeguarding Page (resources/views/frontEnd/roster/safeguarding.blade.php)
- Extends master layout, includes roster_header
- **CRITICAL: Verify the page renders at the correct URL** — After creating route + controller + view, immediately `curl http://127.0.0.1:8000/roster/safeguarding` and grep for a unique element
- Filter bar: status dropdown, risk level dropdown, search box
- Referral list: table/cards showing ref number, date, type badges, risk level badge (color-coded), status badge, reported by
- Risk level colours: Low=green, Medium=amber, High=orange, Critical=red
- Status colours: Reported=blue, Under Investigation=amber, Safeguarding Plan=purple, Closed=grey
- Create button → opens create modal/form
- Click row → opens detail view (modal or panel)
- Detail view shows: all concern details, people involved, agency notifications (with dates/refs), strategy meeting, safeguarding plan, outcome
- Edit button on detail view (only for non-closed cases)
- Status change buttons: "Start Investigation", "Create Plan", "Close Case" — contextual based on current status
- Delete button (soft delete, admin only)
- Empty state: "No safeguarding referrals" message

### Step 8: JavaScript (public/js/roster/safeguarding.js)
- `loadReferrals(page, filters)` — AJAX list → render table with esc() on ALL user data
- `saveReferral(formData)` — POST to save → refresh list
- `updateReferral(id, formData)` — POST to update → refresh list
- `loadDetails(id)` — POST to details → render detail view
- `deleteReferral(id)` — confirm dialog → POST to delete → refresh list
- `changeStatus(id, newStatus)` — POST to status-change → refresh list + detail
- Status/risk level mapping objects — values must match DB enums exactly
- Safeguarding type mapping: match the 10 types from safeguarding_types table
- All rendered fields use esc() helper — especially details_of_concern, witness statements, outcome_details
- All AJAX calls have error: callbacks with generic messages

### Step 9: Seed Test Data
After building, verify seed data displays correctly:
- At least 1 referral per status (reported, under_investigation, safeguarding_plan, closed)
- At least 1 with witnesses array populated
- At least 1 with alleged_perpetrator object populated
- At least 1 with multi-agency notifications (police + local authority + CQC)
- At least 1 with safeguarding_plan object populated

### Step 10: Write Tests (12+)
- Auth: list/save/update/details/delete/statusChange all reject unauthenticated
- Validation: save rejects missing required fields (date_of_concern, details_of_concern, safeguarding_type, risk_level)
- Validation: save rejects invalid risk_level and status values
- IDOR: list doesn't leak cross-home referrals
- IDOR: details rejects cross-home referral ID
- IDOR: update rejects cross-home referral ID
- IDOR: delete rejects cross-home referral ID
- Flow: save → appears in list → update → changes persist → statusChange → new status → delete → gone from list
- XSS: `<script>alert('xss')</script>` in details_of_concern — stored safely, returned escaped
- Mass assignment: home_id sent in request body is ignored, server-side home_id used
- Access control: staff can create/view, only admin can delete
- JSON fields: witnesses and alleged_perpetrator stored and retrieved as proper JSON

## Security Checklist

┌───────────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  Attack Surface   │                                              Protection                                                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Input validation  │ Full validation on save (see rules above). id: required|integer on all single-record endpoints.        │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting     │ list/details: throttle:30,1; save/update/delete/statusChange: throttle:20,1                            │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (client-side) │ esc() on ALL .html() insertions — details_of_concern, witness statements, outcome_details are          │
│                   │ the highest risk fields (long free-text from users)                                                    │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (server-side) │ {{ }} only in Blade — never {!! !!} for any user data                                                 │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ IDOR              │ Every endpoint filters by home_id. List shows only home's referrals.                                   │
│                   │ Details/update/delete verify referral's home_id matches user's home before acting.                      │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment   │ $fillable whitelist on model. home_id and created_by set server-side, never from request.              │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ CSRF              │ _token sent in all AJAX requests via $.ajaxSetup headers.                                              │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Access control    │ Any authenticated user can create and view referrals for their home.                                   │
│                   │ Only admin (user_type === 'A') can delete referrals.                                                   │
│                   │ Status changes: any authenticated user (manager workflow handled by UI, not enforced server-side).      │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Error leaking     │ All catch blocks return generic message. No $e->getMessage() to client.                                │
│                   │ NOTE: Existing SafeguardingTypeController leaks errors — do NOT copy that pattern.                     │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ JSON injection    │ witnesses, alleged_perpetrator, strategy_meeting, safeguarding_plan are JSON columns.                   │
│                   │ Validate structure server-side. Cast via Eloquent $casts, not manual json_encode/decode.                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ SQL injection     │ Eloquent only. No DB::raw() with user input. No raw WHERE clauses.                                     │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ home_id type      │ New table uses INTEGER home_id (not varchar like notification table). Simple WHERE home_id = X.        │
└───────────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Where does the safeguarding page live?** New page at `/roster/safeguarding`, linked from sidebar.

2. **Safeguarding types:** Use the existing `safeguarding_types` table for the type dropdown. Need to seed types for home 8. Store selected types as JSON array in `safeguarding_type` column (matching CareRoster pattern), NOT via the junction table (that's for incidents only).

3. **Multi-step form or single form?** Single scrollable form with sections (Concern → People → Notifications → Plan/Outcome) — simpler than a multi-step wizard for Phase 1. Outcome/plan sections only shown when status is safeguarding_plan or closed.

4. **Status workflow:** Linear progression: reported → under_investigation → safeguarding_plan → closed. No skipping steps. Contextual buttons show only the next valid status.

5. **Reference numbers:** Auto-generated as `SAFE-{YYYY}-{MM}-{seq}` on create. Not editable by users.

6. **JSON columns:** witnesses, alleged_perpetrator, strategy_meeting, safeguarding_plan stored as JSON. Eloquent $casts handles serialization. This matches CareRoster's data structure exactly.

7. **Who sees what?** All authenticated users for a home see all referrals for that home. Creating is open to all staff (anyone can raise a concern). Deleting is admin-only.

8. **Relationship to incidents:** Feature 1 (Incidents) already has `is_safeguarding` flag. For Phase 1, these remain separate systems. Phase 2 could add a `linked_incident_id` to connect them.

## Post-Build Verification Checklist (MANDATORY — from Features 4, 7, 8)
After completing the build, verify ALL of these before moving to TEST:
- [ ] `curl http://127.0.0.1:8000/roster/safeguarding` returns 200 and contains safeguarding page HTML
- [ ] `curl http://127.0.0.1:8000/roster` and grep for the sidebar safeguarding link — href must be `/roster/safeguarding`
- [ ] Sidebar link is NOT inside `<!-- -->` or `{{-- --}}` comments
- [ ] All 7 new routes are in `$allowed_path` in checkUserAuth.php
- [ ] AJAX list endpoint returns referral data for home 8
- [ ] Seed data displays correctly — at least 1 referral per status
- [ ] Safeguarding types dropdown populated for home 8
- [ ] No `{!! !!}` in the new safeguarding.blade.php
- [ ] All `.html()` calls in safeguarding.js use `esc()` for user data
- [ ] JSON fields (witnesses, alleged_perpetrator) render correctly in detail view
- [ ] Status change buttons work and show only valid next status
- [ ] Reference number auto-generated on save
