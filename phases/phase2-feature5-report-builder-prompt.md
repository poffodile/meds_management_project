# Phase 2 Feature 5 — Custom Report Builder UI

━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 5 — Custom Report Builder UI.
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Controller, service, Blade views, JS, routes
[ ] BUILD — Report type cards, date filters, generate + results table, CSV export
[ ] TEST — Home isolation, validation, CSV output, empty state, pagination, XSS
[ ] DEBUG — Login as admin, generate each report type, test filters, export CSV
[ ] REVIEW — Adversarial curl attacks (cross-home data, param injection, XSS in export)
[ ] AUDIT — Phase 1+2 grep patterns + multi-tenancy check on all queries
[ ] PROD-READY — Admin user journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Context — Read These First

Before starting, read these files for full project context:
- `docs/logs.md` — action log with teaching notes from every prior session
- `CLAUDE.md` — project conventions, security rules, tech stack, git conventions
- `phases/phase2-feature4-feedback-prompt.md` — Feature 4 prompt (pattern reference for controller/service/route structure)

**Admin user:** `komal` / `123456` / home Aries (Admin ID 194)

## Feature Classification

**Category: PORT** — CareRoster has three report-related pages:
1. `Reports.jsx` — Business analytics dashboard with overview, staff performance, financial, operational tabs, charts, CSV export
2. `ReportingEngine.jsx` — Generate/schedule/history tabs with 9 report type cards (client progress, staff performance, compliance, payroll, incident trends, training compliance, audit summary, occupancy, medication compliance)
3. `CustomReportsPage.jsx` + `CustomReportBuilder.jsx` — Pick entity (shifts/carers/clients/incidents/training/medications), select fields, add filters, run → results table, save/load report configs

We port a **practical subset** to Laravel Blade. CareRoster's reporting is aspirational (many report types reference entities that don't exist yet). We build reports against **data that actually exists in Care OS**.

**What we skip entirely (out of scope):**
- Scheduled reports (no cron/queue infrastructure)
- Report history / saved generated reports (no `generated_reports` table)
- Email reports to recipients
- Charts/visualizations (Recharts is React-only; no charting library in Care OS)
- Custom report builder with entity selection (too complex for initial build, most entities don't exist)
- AI-generated reports
- Report types where underlying data doesn't exist (payroll, compliance tasks, quality audits, client progress records, timesheets, occupancy)

## What Exists (infrastructure)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ReportController                      │ EXISTS   │ app/Http/Controllers/frontEnd/Roster/ReportController.php     │
│                                       │          │ Only has index() → empty report.blade.php                     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Report route (/roster/reports)        │ EXISTS   │ routes/web.php line 172 — points to ReportController@index    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Report Blade view                     │ EXISTS   │ report.blade.php — completely empty (just extends master)     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Sidebar nav link "Reports"            │ EXISTS   │ roster_header.blade.php line 479 — already points to          │
│                                       │          │ /roster/reports ✓                                             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Sidebar "Reporting Engine" link       │ EXISTS   │ roster_header.blade.php line 535 — dead `#!` link             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ checkUserAuth whitelist               │ EXISTS   │ /roster/reports is already allowed via access_rights check     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Actual data tables with real data:    │          │                                                              │
│   su_incident_report                  │ EXISTS   │ Incident reports with types, severity, dates                  │
│   staff_training / training           │ EXISTS   │ Training records with completion status                       │
│   scheduled_shifts                    │ EXISTS   │ Shift records with dates, staff, clients                      │
│   mar_sheets / mar_administrations    │ EXISTS   │ Medication records with admin status                          │
│   handover_log_book                   │ EXISTS   │ Handover notes with dates                                     │
│   dols                                │ EXISTS   │ DoLS records with status, dates                               │
│   body_map                            │ EXISTS   │ Body map entries                                              │
│   safeguarding_referrals              │ EXISTS   │ Safeguarding referrals with status                            │
│   client_portal_feedback              │ EXISTS   │ Client feedback with ratings, status                          │
│   client_portal_messages              │ EXISTS   │ Portal messages                                               │
│   staff_leaves / staff_sick_leave     │ EXISTS   │ Staff leave records                                           │
│   roster_daily_logs                   │ EXISTS   │ Daily care logs                                                │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Actually Building

A **report hub** where admins pick a report type, set a date range, and get a results table with CSV export. No charts, no scheduling, no custom builder — just clean, useful data summaries from real tables.

**5 report types (all backed by existing data):**

### 1. Incident Summary Report
- **Source:** `su_incident_report` table
- **Summary stats:** total, by type, by severity, open vs resolved
- **Table columns:** date, client, incident type, severity, status, reported by, description (truncated)
- **Filters:** date range, severity dropdown, status dropdown

### 2. Staff Training Report
- **Source:** `staff_training` / `training` tables
- **Summary stats:** total assignments, completed, expired/overdue, compliance %
- **Table columns:** staff name, course/module, status, completion date, expiry date
- **Filters:** date range, status dropdown (completed/pending/expired)

### 3. Medication (MAR) Compliance Report
- **Source:** `mar_sheets` + `mar_administrations` tables
- **Summary stats:** total administrations, given, refused, missed, compliance %
- **Table columns:** client, medication, scheduled time, status, administered by, date
- **Filters:** date range, status dropdown (given/refused/missed/not_given)

### 4. Shift Coverage Report
- **Source:** `scheduled_shifts` table
- **Summary stats:** total shifts, filled, unfilled, fill rate %
- **Table columns:** date, client, shift type, start/end time, assigned staff (or "Unfilled"), status
- **Filters:** date range, shift type dropdown, filled/unfilled toggle

### 5. Client Feedback Summary
- **Source:** `client_portal_feedback` table
- **Summary stats:** total, by type (compliment/complaint/suggestion/concern/general), avg rating, new vs resolved
- **Table columns:** date, client, type, category, rating (stars), status, subject
- **Filters:** date range, type dropdown, status dropdown

**All report types share:**
- Date range picker (from/to date inputs)
- Generate button → AJAX fetch → render results table
- CSV export button (client-side JS, builds CSV from displayed data)
- "X records found" count badge
- Results table with sortable columns (click header to sort)
- Empty state when no data matches filters
- Multi-tenancy: ALL queries filter by admin's home_id

## Page Layout

**Single page at `/roster/reports`** (replace the empty existing view):

```
┌─────────────────────────────────────────────────────────────┐
│  📊 Reports                                                 │
│  Generate reports from your care home data                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Incidents │  │ Training │  │   MAR    │  │  Shifts  │   │
│  │  Summary  │  │ Compliance│  │Compliance│  │ Coverage │   │
│  │    🔴     │  │    🎓    │  │    💊    │  │    📅    │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
│                               ┌──────────┐                  │
│                               │ Client   │                  │
│                               │ Feedback │                  │
│                               │    💬    │                  │
│                               └──────────┘                  │
│                                                             │
│  [Selected: Incident Summary Report]                        │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ From: [____] To: [____] Severity: [All ▾] [Generate]   ││
│  │                                            [Export CSV] ││
│  ├─────────────────────────────────────────────────────────┤│
│  │ 23 records found                                        ││
│  │ Summary: 23 total | 5 High | 12 Medium | 6 Low         ││
│  ├─────────────────────────────────────────────────────────┤│
│  │ Date    │ Client │ Type      │ Severity │ Status │ ...  ││
│  │ 26 Apr  │ Katie  │ Fall      │ High     │ Open   │ ...  ││
│  │ 25 Apr  │ John   │ Medication│ Medium   │ Closed │ ...  ││
│  │ ...     │ ...    │ ...       │ ...      │ ...    │ ...  ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

## Files to Create

1. `app/Services/ReportService.php` — business logic for all 5 report types, each returning summary stats + data array
2. `public/js/roster/reports.js` — report type selection, AJAX generate, CSV export, column sorting
3. `tests/Feature/ReportBuilderTest.php` — 12 tests

## Files to Modify

1. `app/Http/Controllers/frontEnd/Roster/ReportController.php` — add `generate()` method for AJAX report data
2. `resources/views/frontEnd/roster/report/report.blade.php` — replace empty view with full report hub UI
3. `routes/web.php` — add GET `/reports/generate` route
4. `app/Http/Middleware/checkUserAuth.php` — whitelist `/roster/reports/generate`

## Step-by-step Implementation

### Step 1: Create ReportService

`app/Services/ReportService.php` — one method per report type, all filter by home_id:

```php
class ReportService
{
    public function generateIncidentReport(int $homeId, ?string $dateFrom, ?string $dateTo, ?string $severity, ?string $status): array
    {
        // Query su_incident_report WHERE home_id = $homeId
        // Apply date range to incident_date
        // Apply severity/status filters
        // Return ['summary' => [...], 'data' => [...], 'columns' => [...]]
        // Summary: total, by_severity (high/medium/low), by_status (open/resolved/closed)
        // Need to join service_user for client name
    }

    public function generateTrainingReport(int $homeId, ?string $dateFrom, ?string $dateTo, ?string $status): array
    {
        // Query staff_training JOIN training (for course name) JOIN user (for staff name)
        // Filter by home_id, date range, status
        // Summary: total, completed, pending, expired, compliance_rate
    }

    public function generateMARReport(int $homeId, ?string $dateFrom, ?string $dateTo, ?string $adminStatus): array
    {
        // Query mar_administrations JOIN mar_sheets (for medication name) JOIN service_user (for client)
        // Filter by home_id, date range, admin status
        // Summary: total, given, refused, missed, compliance_rate
    }

    public function generateShiftReport(int $homeId, ?string $dateFrom, ?string $dateTo, ?string $shiftType, ?string $fillStatus): array
    {
        // Query scheduled_shifts JOIN service_user (client name) LEFT JOIN user (staff name)
        // Filter by home_id (VARCHAR in this table!), date range, shift type, filled/unfilled
        // Summary: total, filled, unfilled, fill_rate
    }

    public function generateFeedbackReport(int $homeId, ?string $dateFrom, ?string $dateTo, ?string $type, ?string $status): array
    {
        // Query client_portal_feedback JOIN service_user (client name)
        // Filter by home_id, date range, type, status
        // Summary: total, by_type, avg_rating, new, resolved
        // Respect is_anonymous: show "Anonymous" not real name
    }
}
```

**Important data quirks to handle:**
- `scheduled_shifts.home_id` is VARCHAR, not INT. Cast with `(string)` when querying.
- `su_incident_report` — check column names via `DESCRIBE` before querying (the table may use different column names than expected).
- `staff_training` — check actual schema, may join `training` table for course name.
- `mar_administrations` — check actual schema for status values and column names.
- For ALL queries: use Eloquent or query builder with bindings. NEVER use `DB::raw()` with user input.

### Step 2: Update ReportController

```php
public function index()
{
    $homeId = explode(',', auth()->user()->home_id)[0];
    return view('frontEnd.roster.report.report', ['home_id' => $homeId]);
}

public function generate(Request $request)
{
    $request->validate([
        'report_type' => 'required|in:incidents,training,mar,shifts,feedback',
        'date_from' => 'nullable|date',
        'date_to' => 'nullable|date',
        'severity' => 'nullable|string|max:30',
        'status' => 'nullable|string|max:30',
        'shift_type' => 'nullable|string|max:30',
        'fill_status' => 'nullable|in:filled,unfilled',
        'type' => 'nullable|string|max:30',
        'admin_status' => 'nullable|string|max:30',
    ]);

    $homeId = explode(',', auth()->user()->home_id)[0];
    $service = app(ReportService::class);

    $result = match ($request->report_type) {
        'incidents' => $service->generateIncidentReport(
            (int) $homeId, $request->date_from, $request->date_to,
            $request->severity, $request->status
        ),
        'training' => $service->generateTrainingReport(
            (int) $homeId, $request->date_from, $request->date_to,
            $request->status
        ),
        'mar' => $service->generateMARReport(
            (int) $homeId, $request->date_from, $request->date_to,
            $request->admin_status
        ),
        'shifts' => $service->generateShiftReport(
            (int) $homeId, $request->date_from, $request->date_to,
            $request->shift_type, $request->fill_status
        ),
        'feedback' => $service->generateFeedbackReport(
            (int) $homeId, $request->date_from, $request->date_to,
            $request->type, $request->status
        ),
    };

    return response()->json(['status' => true, 'report' => $result]);
}
```

### Step 3: Update Routes

```php
// Inside roster prefix group:
Route::get('/reports/generate', [ReportController::class, 'generate'])->middleware('throttle:30,1');
```

**Note:** Use GET not POST — report generation is a read-only operation, and GET allows bookmarkable URLs with query params.

### Step 4: Whitelist in Middleware

```php
// In checkUserAuth.php allowed_path:
array_push($allowed_path,
    'roster/reports/generate'
);
```

### Step 5: Build Report Blade View

`resources/views/frontEnd/roster/report/report.blade.php`:

**Replaces the existing empty view.** Uses `@extends('frontEnd.layouts.master')`, includes `roster_header`, wraps in `<main class="page-content">`.

**Layout:**
- Title: "Reports" with subtitle
- 5 report type cards in a row (clickable, highlighted when active)
- Filter bar (date range + report-specific dropdowns, shown when a type is selected)
- Generate button + Export CSV button
- Summary stats bar (shown after generate)
- Results table (shown after generate)
- Empty state when no results

**Report type cards:**
- Incident Summary — red icon, `fa-exclamation-triangle`
- Training Compliance — indigo icon, `fa-graduation-cap`
- MAR Compliance — pink icon, `fa-medkit`
- Shift Coverage — blue icon, `fa-calendar`
- Client Feedback — green icon, `fa-comments`

**Filter bar (changes by report type):**
- All types: From date, To date (default: last 30 days)
- Incidents: + Severity dropdown (All/High/Medium/Low) + Status dropdown (All/Open/Resolved/Closed)
- Training: + Status dropdown (All/Completed/Pending/Expired)
- MAR: + Admin Status dropdown (All/Given/Refused/Missed/Not Given)
- Shifts: + Shift Type dropdown (All/Morning/Afternoon/Night/Waking Night) + Fill Status (All/Filled/Unfilled)
- Feedback: + Type dropdown (All/Compliment/Complaint/Suggestion/Concern/General) + Status dropdown (All/New/Acknowledged/Resolved/Closed)

**Results table:**
- Rendered via JS from AJAX response
- All user data escaped with `esc()` helper
- Column headers clickable for sorting (client-side sort)
- Max 100 rows shown; if more, show "Showing 100 of X records. Export CSV to see all."

**CSV export:**
- Client-side JS builds CSV string from the full data array (not just visible 100)
- Uses `Blob` + `URL.createObjectURL` + click-to-download pattern
- Filename: `{report_type}_report_{date}.csv`
- Columns match the displayed table columns

### Step 6: Build Reports JS

`public/js/roster/reports.js`:

```javascript
// Key features:
// - Report type card selection → show/hide relevant filters
// - Generate button → AJAX GET /roster/reports/generate?report_type=X&date_from=Y&...
// - Render summary stats bar from response.report.summary
// - Render results table from response.report.data using response.report.columns
// - Column header click → sort data client-side
// - Export CSV button → build CSV from full data, trigger download
// - esc() helper for all user data in .html() calls
// - $.ajaxSetup with X-CSRF-TOKEN header
```

### Step 7: Write Tests

**Test file:** `tests/Feature/ReportBuilderTest.php`

```
1.  Report page loads for admin → 200, sees "Reports"
2.  Report page loads with report type cards visible
3.  Generate incident report → 200, response has summary + data arrays
4.  Generate training report → 200, response has summary + data arrays
5.  Generate MAR report → 200, response has summary + data arrays
6.  Generate shift report → 200, response has summary + data arrays
7.  Generate feedback report → 200, response has summary + data arrays
8.  Invalid report_type → 422
9.  Home isolation: admin only sees data from own home
10. Date range filter works: no data outside range
11. Unauthenticated → 302 redirect
12. Portal user cannot access reports page
```

## Data Discovery Step (CRITICAL)

**Before writing the service, you MUST run `DESCRIBE` on each source table to learn the actual column names.** Don't assume column names from CareRoster — the Care OS tables may differ.

```php
// Run these in tinker:
DB::select('DESCRIBE su_incident_report');
DB::select('DESCRIBE staff_training');
DB::select('DESCRIBE training');
DB::select('DESCRIBE mar_administrations');
DB::select('DESCRIBE mar_sheets');
DB::select('DESCRIBE scheduled_shifts');
DB::select('DESCRIBE client_portal_feedback');
DB::select('DESCRIBE service_user');  // for client name joins

// Also check what data exists:
DB::table('su_incident_report')->where('home_id', 8)->count();
DB::table('staff_training')->limit(5)->get();
DB::table('mar_administrations')->limit(5)->get();
DB::table('scheduled_shifts')->where('home_id', '8')->limit(5)->get();
DB::table('client_portal_feedback')->where('home_id', 8)->count();
```

**If a table has zero rows for home 8, you still build the report type — it just shows "No data found" empty state. Don't skip report types based on empty data.**

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ ALL report queries MUST filter by home_id from auth user session.             │
│                       │ home_id NEVER from request params.                                            │
│                       │ scheduled_shifts uses VARCHAR home_id — cast to string for comparison.         │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ SQL injection         │ Eloquent query builder with parameter binding only.                           │
│                       │ NEVER use DB::raw() with user input.                                          │
│                       │ Filter values (severity, status, type) validated as in:list or max:30.        │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS                   │ Blade: {{ }} only, no {!! !!}.                                                │
│                       │ JS: esc() helper for all data before .html().                                 │
│                       │ CSV export: no HTML in CSV output.                                            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ GET for read-only generate (no mutation). AJAX uses X-CSRF-TOKEN header.      │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:30,1 on generate endpoint.                                           │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Access control        │ checkUserAuth middleware on all routes. Report page admin-only.                │
│                       │ Portal users cannot access /roster/reports.                                    │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Data exposure         │ Anonymous feedback: respect is_anonymous flag in feedback report.              │
│                       │ Staff names: full names OK for admin reports (internal use).                   │
│                       │ No PII in CSV export filenames.                                               │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ report_type: required|in:list. date_from/date_to: nullable|date.              │
│                       │ All dropdown filters: nullable|string|max:30.                                  │
│                       │ fill_status: nullable|in:filled,unfilled.                                      │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Reuse existing ReportController and route.** `/roster/reports` already exists with an empty view. We fill it in rather than creating a new controller. Add just one new route: `/reports/generate` for AJAX data.

2. **GET for generate, not POST.** Report generation is read-only. GET allows the browser to cache responses and makes URLs bookmarkable. AJAX still sends X-CSRF-TOKEN header.

3. **No new database tables.** Reports query existing tables only. No `generated_reports` or `saved_reports` tables — keep it simple.

4. **5 report types only, all backed by real data.** CareRoster has 9+ report types, but most reference entities that don't exist in Care OS. We build reports for tables that actually have data.

5. **Client-side CSV export.** The AJAX response returns all matching rows (capped at 500 to prevent memory issues). JS builds the CSV client-side. No server-side file generation needed.

6. **Client-side column sorting.** Click a column header → sort the data array in JS → re-render table. No server round-trip for sorting.

7. **No charts.** CareRoster uses Recharts (React library). Care OS has no charting library. Summary stat cards provide the at-a-glance view instead.

8. **Server caps results at 500 rows.** The service methods return max 500 rows per query. If more exist, the summary stats reflect the full count but the data array is truncated. The "Export CSV" includes all 500 rows but notes if truncated.

## Test Verification (what user tests in browser)

### Admin side (login as komal / 123456 / home Aries):
1. Navigate to "Reports" in sidebar → Report hub page loads with 5 type cards
2. Click "Incident Summary" → filter bar shows date range + severity + status dropdowns
3. Click "Generate" with default date range → summary stats + data table appear
4. Click column header → data sorts by that column
5. Click "Export CSV" → CSV file downloads with matching data
6. Switch to "Training Compliance" → filter bar changes, generate shows training data
7. Switch to "MAR Compliance" → filter bar changes, generate shows medication data
8. Switch to "Shift Coverage" → filter bar changes, generate shows shift data
9. Switch to "Client Feedback" → anonymous feedback shows "Anonymous" in submitted_by column
10. Set date range to far future → "No data found" empty state
11. All report data is from home Aries only (home_id = 8)
