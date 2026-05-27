# Phase 2 Feature 6 — Scheduled Reports (Daily/Weekly/Monthly Email)

━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 6 — Scheduled Reports.
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration, model, service, artisan command, controller, Blade view, JS, routes
[ ] BUILD — Schedule CRUD, artisan command to dispatch reports, email with CSV, management UI
[ ] TEST — CRUD validation, home isolation, next_run_date calculation, command dispatch, email, toggle active
[ ] DEBUG — Login as admin, create/edit/delete schedules, run command manually, check email log
[ ] REVIEW — Adversarial curl attacks (IDOR, mass assignment home_id/next_run_date, email injection, XSS in report name)
[ ] AUDIT — Phase 1+2 grep patterns + multi-tenancy check on all queries
[ ] PROD-READY — Admin user journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Context — Read These First

Before starting, read these files for full project context:
- `docs/logs.md` — action log with teaching notes from every prior session
- `CLAUDE.md` — project conventions, security rules, tech stack, git conventions
- `phases/phase2-feature4-feedback-prompt.md` — Feature 4 prompt (pattern reference for controller/service/route structure)
- `app/Services/ReportService.php` — Feature 5's report generator (5 methods we reuse for scheduled execution)
- `app/Http/Controllers/frontEnd/Roster/ReportController.php` — existing report controller (add schedule methods here)
- `resources/views/frontEnd/roster/report/report.blade.php` — existing report hub view (add Scheduled tab here)

**Admin user:** `komal` / `123456` / home Aries (Admin ID 194)

## Feature Classification

**Category: BUILD FOR REAL** — CareRoster has a fully functional `ScheduledReportDialog.jsx` and scheduled reports tab in `ReportingEngine.jsx` with real CRUD against Base44 backend. However, CareRoster **never actually executes** the schedules — there's no cron job, no email sending, no report generation on schedule. The UI creates/edits/deletes schedule records, but nothing runs them.

We port the **schedule management UI** from CareRoster and build the **actual execution backend** from scratch:
- Artisan command that runs on schedule, finds due reports, generates them via `ReportService`, emails results as CSV
- Laravel scheduler registration in `Kernel.php`
- Execution logging to track what ran and when

**CareRoster reference files:**
- `src/components/reports/ScheduledReportDialog.jsx` — create/edit form with: report name, report type, frequency, day, time, recipients, output format, active toggle, notes, next_run_date preview
- `src/pages/ReportingEngine.jsx` — tabs (Generate | Scheduled | History), scheduled tab shows card list with name, type badge, frequency badge, recipient count, next run date, edit/toggle/delete actions

**What we skip (out of scope):**
- Report History tab (no `generated_reports` table — we log executions but don't store full report output)
- PDF output format (no PDF library in Care OS — CSV only)
- "Include Charts" option (no charting library)
- "Parameters" config (date_range_type, include_summary, include_details) — we always generate for the last period matching the frequency
- CareRoster report types that don't exist in Care OS (client_progress, staff_performance, compliance, payroll_summary, audit_summary) — we use the 5 types from Feature 5

## What Exists (infrastructure from Feature 5)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ReportService                         │ EXISTS   │ app/Services/ReportService.php — 5 methods:                  │
│                                       │          │   generateIncidentReport(homeId, dateFrom, dateTo)           │
│                                       │          │   generateTrainingReport(homeId, dateFrom, dateTo, status)   │
│                                       │          │   generateMARReport(homeId, dateFrom, dateTo, code)          │
│                                       │          │   generateShiftReport(homeId, dateFrom, dateTo, shiftType,   │
│                                       │          │     status)                                                  │
│                                       │          │   generateFeedbackReport(homeId, dateFrom, dateTo,           │
│                                       │          │     feedbackType, status)                                    │
│                                       │          │ Each returns: ['summary' => [...], 'columns' => [...],      │
│                                       │          │   'data' => [...]]                                           │
│                                       │          │ All filter by home_id. All cap at 500 rows.                  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ReportController                      │ EXISTS   │ app/Http/Controllers/frontEnd/Roster/ReportController.php     │
│                                       │          │ index() → report.blade.php, generate() → JSON via AJAX       │
│                                       │          │ Add schedule CRUD methods here.                               │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Report Blade view                     │ EXISTS   │ report.blade.php — 262 lines, has report type cards + filter  │
│                                       │          │ bar + generate + CSV export. Add "Scheduled Reports" tab.     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ reports.js                            │ EXISTS   │ public/js/roster/reports.js — card select, AJAX generate,     │
│                                       │          │ sort, CSV export. Add schedule management JS.                 │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Report route (/roster/reports)        │ EXISTS   │ GET /roster/reports → index                                   │
│                                       │          │ GET /roster/reports/generate → generate (throttle:30,1)       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Sidebar nav "Reporting Engine" link   │ EXISTS   │ roster_header.blade.php line 535 — dead `#!` link             │
│                                       │          │ Wire this to /roster/reports (same page, scheduled tab)       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Mail config                           │ EXISTS   │ .env: MAIL_MAILER=smtp, MAIL_HOST=smtp.gmail.com,             │
│                                       │          │ MAIL_PORT=465, MAIL_ENCRYPTION=ssl                           │
│                                       │          │ MAIL_FROM=mobappssolutions131@gmail.com                       │
│                                       │          │ NOTE: For dev/testing, switch to MAIL_MAILER=log to avoid     │
│                                       │          │ sending real emails. Verify in storage/logs/laravel.log.      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Queue config                          │ EXISTS   │ QUEUE_CONNECTION=sync — jobs run inline (no queue worker       │
│                                       │          │ needed). Fine for scheduled reports since the artisan command  │
│                                       │          │ runs via cron, not during web requests.                       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Scheduler (Kernel.php)                │ EXISTS   │ app/Console/Kernel.php — empty schedule() method.              │
│                                       │          │ Register our command here.                                     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Jobs directory                        │ MISSING  │ No app/Jobs/ — not needed (sync queue, command does the work)  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Mail directory                        │ MISSING  │ No app/Mail/ — create ScheduledReportMail mailable             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Commands directory                    │ MISSING  │ No app/Console/Commands/ — create directory + command          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ scheduled_reports table               │ MISSING  │ Need migration                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ScheduledReport model                 │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ checkUserAuth whitelist               │ EXISTS   │ /roster/reports and /roster/reports/generate already added.    │
│                                       │          │ Need to add schedule CRUD endpoints.                          │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Actually Building

A **"Scheduled Reports" tab** on the existing report hub page where admins can create, edit, toggle, and delete report schedules. An **artisan command** runs hourly (or can be triggered manually), finds schedules that are due, generates the report via `ReportService`, and emails the results as a CSV attachment.

### UI: Two-Tab Layout on Existing Reports Page

The existing report hub (cards + generate) becomes **Tab 1: "Generate Report"**. We add **Tab 2: "Scheduled Reports"** that shows:

```
┌─────────────────────────────────────────────────────────────┐
│  📊 Reports                                                 │
│  Generate and schedule reports from your care home data     │
│                                                             │
│  [Generate Report]  [Scheduled Reports]     ← tab bar       │
│  ─────────────────────────────────────────────               │
│                                                             │
│  ┌──────────────────────────────────────┐  [+ New Schedule] │
│  │ 📋 Active: 3  │ 📧 This Month: 12   │                   │
│  └──────────────────────────────────────┘                   │
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Weekly Training Report                    [Edit][⏸][🗑] ││
│  │ 📊 Training Compliance · ⏰ Weekly · 👤 2 recipients    ││
│  │ Next run: Mon, 28 Apr 2026 at 08:00                     ││
│  ├─────────────────────────────────────────────────────────┤│
│  │ Monthly MAR Summary                       [Edit][⏸][🗑] ││
│  │ 📊 MAR Compliance · ⏰ Monthly · 👤 1 recipient        ││
│  │ Next run: 1 May 2026 at 09:00                           ││
│  ├─────────────────────────────────────────────────────────┤│
│  │ Daily Shift Overview              (dimmed) [Edit][▶][🗑] ││
│  │ 📊 Shift Coverage · ⏰ Daily · 👤 3 recipients          ││
│  │ ⏸ Paused                                                ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────── Create/Edit Schedule Modal ──────────────────────┐│
│  │ Report Name:     [Weekly Training Report              ] ││
│  │ Report Type:     [Training Compliance ▾]                ││
│  │ Frequency:       [Weekly ▾]                             ││
│  │ Day of Week:     [Monday ▾]        Time: [08:00]       ││
│  │ Recipients:      [manager@care.com, admin@care.com    ] ││
│  │ Output Format:   [CSV Attachment ▾]                     ││
│  │ Active:          [✓]                                    ││
│  │ Notes:           [________________________________]      ││
│  │                                                         ││
│  │ 📅 Next run: Monday, 28 April 2026 at 08:00            ││
│  │                                                         ││
│  │              [Cancel]  [Save Schedule]                  ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### Backend: Artisan Command + Email

```
┌──────────────┐     ┌─────────────────────┐     ┌───────────────────┐
│  Laravel      │────▶│  reports:dispatch    │────▶│  ReportService    │
│  Scheduler    │     │  (artisan command)   │     │  (generate data)  │
│  (hourly)     │     │                     │     │                   │
└──────────────┘     │  1. Find due reports │     └────────┬──────────┘
                     │  2. Generate each    │              │
                     │  3. Build CSV        │              ▼
                     │  4. Send email       │     ┌───────────────────┐
                     │  5. Update next_run  │     │  ScheduledReport  │
                     │  6. Log execution    │     │  Mail + CSV       │
                     └─────────────────────┘     └───────────────────┘
```

## Database: `scheduled_reports` Table

Based on CareRoster's `ScheduledReport` entity, adapted for Care OS:

```
scheduled_reports
├── id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
├── home_id             INT UNSIGNED NOT NULL          -- multi-tenancy
├── report_name         VARCHAR(255) NOT NULL          -- display name
├── report_type         VARCHAR(30) NOT NULL           -- incidents|training|mar|shifts|feedback
├── schedule_frequency  VARCHAR(20) NOT NULL           -- daily|weekly|fortnightly|monthly
├── schedule_day        TINYINT UNSIGNED NULL          -- 0-6 (day of week) or 1-28 (day of month)
├── schedule_time       VARCHAR(5) NOT NULL DEFAULT '08:00' -- HH:MM
├── recipients          JSON NOT NULL                  -- ["email1@test.com","email2@test.com"]
├── output_format       VARCHAR(20) NOT NULL DEFAULT 'csv' -- csv|email_summary
├── is_active           TINYINT(1) NOT NULL DEFAULT 1
├── notes               TEXT NULL
├── next_run_date       DATETIME NULL                  -- calculated, used by dispatcher
├── last_run_date       DATETIME NULL                  -- set after successful run
├── last_run_status     VARCHAR(20) NULL               -- success|failed
├── created_by          INT UNSIGNED NOT NULL           -- FK → user.id
├── is_deleted          TINYINT(1) NOT NULL DEFAULT 0  -- soft delete (project convention)
├── created_at          TIMESTAMP NULL
├── updated_at          TIMESTAMP NULL
│
├── INDEX idx_home_id (home_id)
├── INDEX idx_next_run (next_run_date, is_active, is_deleted)
└── INDEX idx_created_by (created_by)
```

**Simplified vs CareRoster:**
- Dropped `quarterly` and `annually` frequencies (overkill for a care home)
- Dropped `include_charts` (no charting library)
- Dropped `parameters` JSON (always generate for the last period)
- Dropped `pdf` and `excel` output formats (no PDF/Excel library — CSV and email_summary only)
- Report types limited to the 5 that exist in Feature 5 (not CareRoster's 8+ types)

## Files to Create

1. `app/Models/ScheduledReport.php` — model with `$fillable`, `$casts`, scopes
2. `app/Services/ScheduledReportService.php` — CRUD + dispatch logic
3. `app/Console/Commands/DispatchScheduledReports.php` — artisan command `reports:dispatch`
4. `app/Mail/ScheduledReportMail.php` — mailable with CSV attachment
5. `resources/views/emails/scheduled_report.blade.php` — simple email body template
6. `tests/Feature/ScheduledReportTest.php` — 15 tests

## Files to Modify

1. `app/Http/Controllers/frontEnd/Roster/ReportController.php` — add schedule CRUD methods: `scheduleList()`, `scheduleStore()`, `scheduleUpdate()`, `scheduleToggle()`, `scheduleDelete()`
2. `resources/views/frontEnd/roster/report/report.blade.php` — add tab bar (Generate | Scheduled) + scheduled reports list + create/edit modal
3. `public/js/roster/reports.js` — add tab switching, schedule CRUD AJAX, modal open/close, next_run_date preview calculation
4. `routes/web.php` — add 5 schedule routes (GET list, POST store, POST update, POST toggle, POST delete)
5. `app/Http/Middleware/checkUserAuth.php` — whitelist new schedule endpoints
6. `app/Console/Kernel.php` — register `reports:dispatch` command to run hourly
7. `resources/views/frontEnd/roster/common/roster_header.blade.php` — wire "Reporting Engine" link to `/roster/reports`

## Step-by-step Implementation

### Step 1: Create Migration (via tinker DB::statement)

Create the `scheduled_reports` table using the schema above. Run via `DB::statement()` in tinker (artisan migrate has known issues with older migrations in this project).

### Step 2: Create ScheduledReport Model

`app/Models/ScheduledReport.php`:
- `$table = 'scheduled_reports'`
- `$fillable` whitelist: report_name, report_type, schedule_frequency, schedule_day, schedule_time, recipients, output_format, is_active, notes, next_run_date, last_run_date, last_run_status, home_id, created_by
- `$casts`: recipients → array, is_active → boolean, is_deleted → boolean, next_run_date → datetime, last_run_date → datetime
- Scopes: `scopeForHome($homeId)`, `scopeActive()`, `scopeDue()` (where next_run_date <= now AND is_active = 1 AND is_deleted = 0)
- Relationship: `createdBy()` → belongsTo User

### Step 3: Create ScheduledReportService

`app/Services/ScheduledReportService.php`:

```php
class ScheduledReportService
{
    public function listForHome(int $homeId): Collection
    {
        // Return all non-deleted schedules for this home, ordered by created_at desc
    }

    public function store(array $data, int $homeId, int $userId): ScheduledReport
    {
        // Validate recipients are valid emails
        // Calculate next_run_date from frequency/day/time
        // Create record with home_id and created_by
    }

    public function update(int $id, array $data, int $homeId): ScheduledReport
    {
        // Find schedule by ID + home_id (IDOR protection)
        // Recalculate next_run_date if frequency/day/time changed
        // Update record
    }

    public function toggleActive(int $id, int $homeId): ScheduledReport
    {
        // Find by ID + home_id, flip is_active
        // If reactivating, recalculate next_run_date
    }

    public function delete(int $id, int $homeId): void
    {
        // Soft delete: set is_deleted = 1 (find by ID + home_id)
    }

    public function calculateNextRunDate(string $frequency, ?int $day, string $time): Carbon
    {
        // Same logic as CareRoster's calculateNextRunDate:
        // daily: tomorrow at $time (or today if not yet past)
        // weekly: next $day-of-week at $time
        // fortnightly: next $day-of-week + 2 weeks at $time
        // monthly: next $day-of-month at $time
        // If calculated time is in the past, advance by one period
    }

    public function advanceNextRunDate(ScheduledReport $schedule): Carbon
    {
        // After execution, calculate the NEXT occurrence
        // daily → +1 day, weekly → +1 week, fortnightly → +2 weeks, monthly → +1 month
    }

    public function dispatchDueReports(): array
    {
        // Find all schedules where next_run_date <= now AND is_active AND !is_deleted
        // For each:
        //   1. Calculate date range based on frequency (last day/week/fortnight/month)
        //   2. Call ReportService->generate*Report(homeId, dateFrom, dateTo)
        //   3. Build CSV from result data
        //   4. Send email with CSV attachment to all recipients
        //   5. Update last_run_date, last_run_status, advance next_run_date
        //   6. Log the execution
        // Return array of results [{schedule_id, status, error?}]
    }

    public function buildCSV(array $columns, array $data): string
    {
        // Build CSV string from report columns + data arrays
        // Same format as the client-side CSV export in reports.js
    }
}
```

**Date range logic by frequency:**
- `daily` → yesterday 00:00 to yesterday 23:59
- `weekly` → last 7 days
- `fortnightly` → last 14 days
- `monthly` → 1st to last day of previous month

### Step 4: Create Artisan Command

`app/Console/Commands/DispatchScheduledReports.php`:

```php
class DispatchScheduledReports extends Command
{
    protected $signature = 'reports:dispatch';
    protected $description = 'Dispatch all scheduled reports that are due';

    public function handle(ScheduledReportService $service): int
    {
        $results = $service->dispatchDueReports();
        $this->info("Dispatched " . count($results) . " reports");
        foreach ($results as $r) {
            $this->line("  Schedule #{$r['schedule_id']}: {$r['status']}");
        }
        return 0;
    }
}
```

### Step 5: Create ScheduledReportMail

`app/Mail/ScheduledReportMail.php`:

```php
class ScheduledReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reportName;
    public array $summary;
    public string $csvContent;
    public string $csvFilename;

    // Constructor receives: report name, summary stats, CSV content, filename
    // build(): from(), subject(), view('emails.scheduled_report'), attachData(CSV)
}
```

**Email template** (`resources/views/emails/scheduled_report.blade.php`):
- Simple HTML email: "Your scheduled report [name] has been generated"
- Summary stats inline (total records, key metrics)
- "The full report is attached as a CSV file"
- Generated by Care OS footer

### Step 6: Register in Kernel.php

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:dispatch')->hourly();
}
```

### Step 7: Update ReportController

Add these methods to the existing controller:

```php
public function scheduleList(Request $request)
{
    // Return JSON list of schedules for admin's home
}

public function scheduleStore(Request $request)
{
    // Validate: report_name required|max:255, report_type in:list,
    //   schedule_frequency in:list, schedule_day nullable|integer|min:0|max:28,
    //   schedule_time required|date_format:H:i, recipients required|string|max:1000,
    //   output_format in:csv,email_summary, notes nullable|max:1000
    // Parse recipients string → array of emails, validate each
    // Call service->store()
    // Return JSON
}

public function scheduleUpdate(Request $request)
{
    // Same validation as store
    // Validate 'id' required|integer
    // Call service->update()
}

public function scheduleToggle(Request $request)
{
    // Validate 'id' required|integer
    // Call service->toggleActive()
}

public function scheduleDelete(Request $request)
{
    // Validate 'id' required|integer
    // Call service->delete()
}
```

### Step 8: Add Routes

```php
// Inside roster prefix group, after existing report routes:
Route::get('/reports/schedules', [ReportController::class, 'scheduleList'])->middleware('throttle:30,1');
Route::post('/reports/schedule/store', [ReportController::class, 'scheduleStore'])->middleware('throttle:30,1');
Route::post('/reports/schedule/update', [ReportController::class, 'scheduleUpdate'])->middleware('throttle:30,1');
Route::post('/reports/schedule/toggle', [ReportController::class, 'scheduleToggle'])->middleware('throttle:30,1');
Route::post('/reports/schedule/delete', [ReportController::class, 'scheduleDelete'])->middleware('throttle:20,1');
```

### Step 9: Whitelist in Middleware

```php
// In checkUserAuth.php — update existing report builder block:
array_push($allowed_path,
    'roster/reports',
    'roster/reports/generate',
    'roster/reports/schedules',
    'roster/reports/schedule/store',
    'roster/reports/schedule/update',
    'roster/reports/schedule/toggle',
    'roster/reports/schedule/delete'
);
```

### Step 10: Update Report Blade View

Add to the existing `report.blade.php`:
- **Tab bar** at the top: "Generate Report" (default active) | "Scheduled Reports"
- Tab 1 content = existing report cards + filter bar + results (wrap in a div)
- Tab 2 content = scheduled reports list:
  - Stat badges: "Active: X" + "Sent This Month: X" (from last_run_date count)
  - "+ New Schedule" button → opens modal
  - Schedule cards: report name, type badge, frequency badge, recipient count, next run date, edit/toggle/delete buttons
  - Inactive schedules shown dimmed with "Paused" label
  - Empty state: "No scheduled reports. Create one to automate report generation."
- **Create/Edit Modal** (Bootstrap 3 modal since that's the project's CSS framework):
  - Report Name (text input, required)
  - Report Type (select: Incident Summary, Training Compliance, MAR Compliance, Shift Coverage, Client Feedback)
  - Frequency (select: Daily, Weekly, Fortnightly, Monthly)
  - Day of Week (select: Mon-Sun, shown only for weekly/fortnightly)
  - Day of Month (select: 1-28, shown only for monthly)
  - Time (time input, default 08:00)
  - Recipients (text input, comma-separated emails)
  - Output Format (select: CSV Attachment, Email Summary)
  - Active (checkbox, default checked)
  - Notes (textarea, optional)
  - Next run preview (calculated and displayed, like CareRoster)
  - Save / Cancel buttons

### Step 11: Update reports.js

Add to the existing `reports.js`:
- Tab switching (Generate / Scheduled)
- `loadSchedules()` — AJAX GET `/roster/reports/schedules` → render list
- Schedule card rendering with esc() for all data
- Modal open/close for create/edit
- Form submit → AJAX POST to store or update
- Toggle active → AJAX POST to toggle
- Delete → confirm → AJAX POST to delete
- Next run date preview calculation (client-side, matching CareRoster's logic):
  - daily → tomorrow at selected time
  - weekly → next selected day-of-week at selected time
  - fortnightly → next selected day + 2 weeks
  - monthly → next selected day-of-month
- Recipient email validation (basic regex check before submit)

### Step 12: Wire Sidebar Link

In `roster_header.blade.php`, change the "Reporting Engine" link from `#!` to `{{ url('/roster/reports') }}`:
```html
<li> <a href="{{ url('/roster/reports') }}"><i class='bx bx-file-detail'></i> <span>Reporting Engine</span> </a></li>
```

### Step 13: Write Tests

**Test file:** `tests/Feature/ScheduledReportTest.php`

```
1.  Report page loads with tab bar (Generate + Scheduled tabs visible)
2.  Schedule list returns empty array for home with no schedules
3.  Create schedule → 200, schedule appears in list
4.  Create schedule validates required fields (missing report_name → 422)
5.  Create schedule validates report_type in allowed list (invalid → 422)
6.  Create schedule validates recipients format (no empty → 422)
7.  Update schedule → 200, changes persisted
8.  Toggle schedule active → flips is_active flag
9.  Delete schedule → soft deletes (is_deleted = 1), no longer in list
10. Home isolation: admin only sees own home's schedules
11. IDOR: admin cannot update/delete schedule from another home
12. Artisan command dispatches due reports (seed schedule with past next_run_date)
13. Artisan command skips inactive schedules
14. Artisan command updates next_run_date after execution
15. Unauthenticated → 302 redirect on all endpoints
```

## Date Range Calculation (for report execution)

When the artisan command runs a scheduled report, it needs a date range. The range depends on the frequency:

| Frequency    | date_from                          | date_to                            |
|-------------|------------------------------------|------------------------------------|
| daily       | Yesterday                          | Yesterday                          |
| weekly      | 7 days ago                         | Yesterday                          |
| fortnightly | 14 days ago                        | Yesterday                          |
| monthly     | 1st of previous month              | Last day of previous month          |

This means a "Weekly Training Report" scheduled for Monday at 08:00 will generate a report covering the last 7 days when it runs.

## Next Run Date Calculation

**On create/update:**
```
Calculate the next occurrence of {frequency} on {day} at {time} from NOW.
If the calculated datetime is in the past (or now), advance by one period.

Examples:
- Weekly, Monday, 08:00 (today is Sunday) → tomorrow Monday 08:00
- Weekly, Monday, 08:00 (today is Monday 10:00) → next Monday 08:00
- Daily, 09:00 (now is 07:00) → today 09:00
- Daily, 09:00 (now is 10:00) → tomorrow 09:00
- Monthly, day 15, 08:00 (today is 10th) → 15th this month 08:00
- Monthly, day 15, 08:00 (today is 20th) → 15th next month 08:00
```

**After execution (advance):**
```
- daily → next_run_date + 1 day
- weekly → next_run_date + 7 days
- fortnightly → next_run_date + 14 days
- monthly → next_run_date + 1 month (Carbon::addMonth())
```

## Email Handling

**Dev/test environment:**
- Change `.env` `MAIL_MAILER=log` during development to avoid sending real emails
- Verify emails appear in `storage/logs/laravel.log`
- The artisan command should work regardless of mail driver

**Email content for `email_summary` format:**
- Subject: "[Care OS] {report_name} — {date}"
- Body: summary stats in a simple HTML table, no CSV attachment
- e.g., "Training Compliance: 4 total, 3 completed, 75% compliance rate"

**Email content for `csv` format:**
- Subject: "[Care OS] {report_name} — {date}"
- Body: brief summary + "Full report attached as CSV"
- Attachment: `{report_type}_report_{date}.csv`

**Recipient validation:**
- Split by comma, trim whitespace, filter empty
- Validate each email with PHP's `filter_var(FILTER_VALIDATE_EMAIL)`
- Reject if zero valid recipients

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ ALL schedule queries filter by home_id from auth session.                     │
│                       │ home_id set on create from session, NEVER from request.                       │
│                       │ Artisan command iterates ALL homes — each report scoped by its own home_id.   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ IDOR                  │ Update/toggle/delete verify schedule.home_id matches admin's home_id.          │
│                       │ Return 404 if mismatch (not 403, to avoid leaking existence).                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ $fillable whitelist on model. home_id and created_by set in service layer.    │
│                       │ next_run_date, last_run_date, last_run_status NOT settable from request.      │
│                       │ Client can NOT set home_id, created_by, or run dates via POST.                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Email injection       │ Recipients validated with filter_var(FILTER_VALIDATE_EMAIL).                  │
│                       │ No \r\n allowed in email addresses (header injection prevention).              │
│                       │ Reject payloads like "test@evil.com\r\nBCC:attacker@evil.com".                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ SQL injection         │ Eloquent ORM only. No DB::raw() with user input.                              │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS                   │ {{ }} in Blade, esc() in JS for schedule names/notes in card rendering.       │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ @csrf on modal form, $.ajaxSetup with X-CSRF-TOKEN on AJAX.                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:30,1 on create/update/list, throttle:20,1 on delete.                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ report_name: required|max:255. report_type: required|in:list.                 │
│                       │ schedule_frequency: required|in:daily,weekly,fortnightly,monthly.              │
│                       │ schedule_day: nullable|integer|min:0|max:28.                                   │
│                       │ schedule_time: required|date_format:H:i.                                       │
│                       │ recipients: required|string|max:1000 (then split+validate emails).            │
│                       │ output_format: required|in:csv,email_summary.                                  │
│                       │ notes: nullable|max:1000.                                                      │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Access control        │ All routes behind checkUserAuth. Admin-only (roster routes).                  │
│                       │ Portal users cannot access /roster/reports.                                    │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Add tab to existing report page, not a new page.** The report hub already exists at `/roster/reports`. Adding a "Scheduled" tab keeps everything in one place and avoids a separate controller/route/nav entry. The "Reporting Engine" sidebar link also points here.

2. **Reuse Feature 5's `ReportService`.** The artisan command calls the same 5 `generate*Report()` methods that the AJAX generate endpoint uses. No duplicated query logic.

3. **CSV only, no PDF.** Care OS has no PDF generation library. CSV is sufficient — it opens in Excel, which is what care home managers use. The `email_summary` format sends key stats inline in the email body (no attachment).

4. **Artisan command, not queue job.** With `QUEUE_CONNECTION=sync`, a queued job would run inline during the web request that creates it — pointless. The artisan command runs via Laravel scheduler (`$schedule->command()->hourly()`) which is the standard approach. In production, a cron entry runs `php artisan schedule:run` every minute.

5. **Simplified frequencies.** CareRoster offers quarterly and annually — these are overkill for a care home. Daily, weekly, fortnightly, and monthly cover all practical use cases.

6. **No report history table.** CareRoster has a "Report History" tab but we skip it. Execution is logged via `last_run_date` and `last_run_status` on the schedule record itself, plus `Log::info()` entries. A full history table adds complexity with little value.

7. **Max 10 schedules per home.** Prevent abuse — validate in service layer before create.

8. **Max 5 recipients per schedule.** Prevent email spam — validate after splitting the comma-separated list.

## Test Verification (what user tests in browser)

### Admin side (login as komal / 123456 / home Aries):
1. Navigate to "Reports" → page loads with two tabs: "Generate Report" and "Scheduled Reports"
2. Click "Scheduled Reports" tab → empty state "No scheduled reports"
3. Click "+ New Schedule" → modal opens with form fields
4. Fill in: "Weekly Training Report", Training Compliance, Weekly, Monday, 08:00, your email, CSV
5. Click "Save Schedule" → modal closes, schedule card appears in list
6. Verify "Next run" shows next Monday at 08:00
7. Click edit (pencil icon) → modal opens pre-filled with schedule data
8. Change frequency to "Daily" → save → next run updates
9. Click pause (toggle) → schedule dims, shows "Paused"
10. Click play (toggle) → schedule reactivates, next run recalculated
11. Click delete (trash) → confirm → schedule removed from list
12. Tab back to "Generate Report" → existing report builder still works as before
13. Run `php artisan reports:dispatch` in terminal → check `storage/logs/laravel.log` for email (MAIL_MAILER=log)
