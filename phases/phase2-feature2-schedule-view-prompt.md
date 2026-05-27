# Phase 2, Feature 2 Prompt — Client Portal: Schedule View for Family

Copy-paste this for the next session:

---

WORKFLOW: Phase 2 Feature 2 — Client Portal Schedule View
Run `/careos-workflow-phase2` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Phase 2 Feature 2 — Client Portal Schedule View
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Controller method, service method, Blade view, JS, routes
[ ] BUILD — Weekly calendar grid, list view, permission checks, GDPR scoping
[ ] TEST — Permission flag, cross-client isolation, date navigation, GDPR staff name masking
[ ] DEBUG — Login as portal user, navigate schedule, check edge cases
[ ] REVIEW — Adversarial curl attacks (IDOR, permission bypass, date injection)
[ ] AUDIT — Phase 1+2 grep patterns + GDPR check + portal middleware check
[ ] PROD-READY — Portal schedule journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Feature Classification

**Category: PORT** — CareRoster has a real schedule view (`ClientPortalSchedule.jsx`) that queries `scheduled_shifts` / `DayCentreSession` / `SupportedLivingShift` by `client_id`. We port the UI to Laravel Blade, querying the existing `scheduled_shifts` table.

## What Exists (75% infrastructure from Feature 1)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal middleware (CheckPortalAccess) │ EXISTS   │ app/Http/Middleware/CheckPortalAccess.php — checks session   │
│                                       │          │ portal_access_id, loads ClientPortalAccess onto request      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal layout                         │ EXISTS   │ frontEnd.portal.layouts.master — nav has Dashboard,          │
│                                       │          │ Schedule, Messages, Feedback links                           │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal controller                     │ EXISTS   │ PortalDashboardController — has index(), comingSoon(),       │
│                                       │          │ logout(). Schedule route currently → comingSoon()            │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal service                        │ EXISTS   │ app/Services/Portal/ClientPortalService.php — has            │
│                                       │          │ getDashboardData(). Needs getScheduleData() added.          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal route (/portal/schedule)       │ EXISTS   │ Currently: Route::get('/schedule', ...comingSoon)            │
│                                       │          │ Needs: point to new schedule() method instead                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ClientPortalAccess model              │ EXISTS   │ Has can_view_schedule flag (boolean, default true)           │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ scheduled_shifts table                │ EXISTS   │ 15 rows for home 8 (all for client 180/Alex Sheffield).     │
│                                       │          │ Columns: service_user_id, home_id, staff_id, start_date,    │
│                                       │          │ start_time, end_time, status, shift_type, tasks, notes.     │
│                                       │          │ Uses SoftDeletes (deleted_at column).                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ScheduledShift model                  │ EXISTS   │ app/Models/ScheduledShift.php — has client(), staff(),      │
│                                       │          │ recurrence(), assessments(), documents() relationships.     │
│                                       │          │ Uses SoftDeletes. Has scopeTodayShifts, scopeUnfilledShifts │
│                                       │          │ WARNING: uses $guarded = [] — violates security rules.      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Test portal user                      │ EXISTS   │ portal_test / 123456 / home 8 → linked to Katie (client 27) │
│                                       │          │ BUT Katie has 0 scheduled shifts. Need to seed test data.   │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Schedule view (Blade)                 │ MISSING  │ Need: frontEnd.portal.schedule.blade.php                     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Schedule JS                           │ MISSING  │ Need: public/js/portal/schedule.js                           │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## CareRoster Reference (Base44)

CareRoster's `ClientPortalSchedule.jsx` (`/Users/vedangvaidya/Desktop/Omega Life/CareRoster/src/pages/ClientPortalSchedule.jsx`) has:

**Two views:**
1. **Weekly calendar grid** — 7-column grid (Mon–Sun), each column shows date + day name + item count. Below the header, each day cell lists shift cards with time, activity name, and location. Today is highlighted in blue. Nav arrows to go prev/next week + "Today" button to jump back.
2. **List view** — Below the grid, a "This Week's Schedule" card shows the same shifts as a vertical list with icons, date, time range, location. "Today" badge on current-day items. Empty state: calendar icon + "No scheduled items".

**Data source (CareRoster):** Queries `DayCentreSession` or `SupportedLivingShift` by `client_id` from `portalAccess`. Filters by `client_type` (day_centre vs supported_living).

**For Care OS:** We use the `scheduled_shifts` table instead. All clients are `residential` type (home 8 is Aries, a residential care home). Filter by `service_user_id = portal_access.client_id` and `home_id`.

**Permission check:** If `can_view_schedule` is false on the ClientPortalAccess record, show an "Access Denied" card instead of the schedule.

**GDPR rule:** Show staff **first name only** (e.g., "Allan" not "Allan Smith"). Never expose staff email, phone, or full surname to family members.

## Database Context

**scheduled_shifts table structure:**
```
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
home_id         VARCHAR(255) NOT NULL          -- care home ID (string, not int!)
care_type_id    VARCHAR(255) NOT NULL          -- always "1" for home 8
assignment      VARCHAR(255) NOT NULL          -- always "Client" for home 8
service_user_id BIGINT UNSIGNED NULL           -- FK → service_user.id (the resident)
property_id     BIGINT UNSIGNED NULL
home_area_id    INT NULL
staff_id        BIGINT UNSIGNED NULL           -- FK → user.id (the carer)
location_name   VARCHAR(255) NULL
location_address VARCHAR(255) NULL
start_date      DATE NULL
start_time      TIME NULL
end_time        TIME NULL
status          ENUM('unfilled','assigned','in_progress','completed','cancelled','no_show')
shift_type      VARCHAR(255) NULL              -- e.g., "morning", "afternoon"
tasks           TEXT NULL                       -- task description
notes           TEXT NULL
is_recurring    TINYINT(1) DEFAULT 0
acknowledge     TINYINT(1) DEFAULT 0
created_at      TIMESTAMP NULL
updated_at      TIMESTAMP NULL
deleted_at      TIMESTAMP NULL                 -- SoftDeletes
```

**NOTE:** `home_id` is VARCHAR in this table (not INT). When querying, use string comparison: `->where('home_id', (string)$homeId)`.

**Sample data (home 8, client 180):**
```
id=28  date=2026-03-02  09:00-17:00  assigned   morning    staff=Allan Smith
id=29  date=2026-03-02  09:00-17:00  assigned   morning    staff=Komal Gautam
id=37  date=2026-03-06  09:00-17:00  assigned   morning    staff=Komal Gautam
id=40  date=2026-03-07  09:00-17:00  assigned   afternoon  staff=Allan Smith
id=41  date=2026-03-07  09:00-17:00  unfilled   morning    staff=NULL
id=49  date=2026-03-11  09:00-17:00  completed  morning    staff=Allan Smith
```

**Test data gap:** Katie (client 27, the test portal user's linked client) has **0 scheduled shifts**. The build step MUST seed 8-10 test shifts for Katie spanning the current and next week so there's something to display in the browser.

## What Needs Building (the plan)

**Goal:** Family members logged into the portal can view a weekly schedule of their linked resident's care shifts. The view shows a 7-day calendar grid with shift cards, plus a list view below. They can navigate between weeks with prev/next arrows and jump to "Today". Permission-gated by `can_view_schedule` flag. Staff names shown as first name only (GDPR).

**Scope (Feature 2 only — 4h budget):**
- Schedule controller method + service method
- Weekly calendar grid view (Blade + jQuery)
- List view below the calendar
- Week navigation (prev/next/today) via AJAX or page reload (either is fine)
- Permission check on `can_view_schedule`
- GDPR: staff first name only
- Seed test shifts for Katie (client 27)
- Update dashboard stat card to show real upcoming schedule count

**NOT in scope (later features):**
- Booking requests from schedule (Feature 3 or separate)
- Messaging from schedule page
- Shift details modal (keep it simple — inline cards only)
- Any write operations (portal users are read-only on schedule)

## Files to Create

1. `resources/views/frontEnd/portal/schedule.blade.php` — weekly calendar grid + list view
2. `public/js/portal/schedule.js` — week navigation, AJAX loading

## Files to Modify

1. `app/Http/Controllers/frontEnd/Portal/PortalDashboardController.php` — add `schedule()` method (or create dedicated `PortalScheduleController`)
2. `app/Services/Portal/ClientPortalService.php` — add `getScheduleData()` and `getUpcomingScheduleCount()`
3. `routes/web.php` — update `/portal/schedule` route from `comingSoon` to new method
4. `app/Http/Middleware/checkUserAuth.php` — verify `/portal/schedule` is already whitelisted (it should be from Feature 1 since it was a comingSoon page)

## Step-by-step Implementation

### Step 1: Seed Test Data for Katie (client 27)

Before building, Katie needs scheduled shifts to display. Seed via tinker or a seeder:

```php
// Seed 10 shifts for Katie (client 27, home 8) across current and next week
$dates = [
    // This week
    ['start_date' => '2026-04-27', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 44],
    ['start_date' => '2026-04-27', 'start_time' => '14:00:00', 'end_time' => '18:00:00', 'shift_type' => 'afternoon', 'status' => 'assigned', 'staff_id' => 219],
    ['start_date' => '2026-04-28', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 44],
    ['start_date' => '2026-04-29', 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'shift_type' => 'morning', 'status' => 'unfilled', 'staff_id' => null],
    ['start_date' => '2026-04-30', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 334],
    // Next week
    ['start_date' => '2026-05-01', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 44],
    ['start_date' => '2026-05-02', 'start_time' => '10:00:00', 'end_time' => '16:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 68],
    ['start_date' => '2026-05-04', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'shift_type' => 'morning', 'status' => 'assigned', 'staff_id' => 219],
    ['start_date' => '2026-05-05', 'start_time' => '08:00:00', 'end_time' => '12:00:00', 'shift_type' => 'morning', 'status' => 'completed', 'staff_id' => 44],
    ['start_date' => '2026-05-06', 'start_time' => '09:00:00', 'end_time' => '15:00:00', 'shift_type' => 'afternoon', 'status' => 'assigned', 'staff_id' => 334],
];

foreach ($dates as $d) {
    DB::table('scheduled_shifts')->insert(array_merge($d, [
        'home_id' => '8',
        'service_user_id' => 27,
        'care_type_id' => '1',
        'assignment' => 'Client',
        'tasks' => 'Personal care and support',
        'notes' => 'Seeded for portal schedule testing',
        'created_at' => now(),
        'updated_at' => now(),
    ]));
}
```

**Staff IDs for home 8:** 44 (Allan Smith), 219 (Komal Gautam), 334, 68 — verify these exist before seeding.

### Step 2: Add Service Method — getScheduleData()

In `app/Services/Portal/ClientPortalService.php`, add:

```php
use App\Models\ScheduledShift;
use Carbon\Carbon;

public function getScheduleData(ClientPortalAccess $access, ?string $weekStart = null): array
{
    $start = $weekStart
        ? Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY)
        : Carbon::now()->startOfWeek(Carbon::MONDAY);
    $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

    $shifts = ScheduledShift::where('home_id', (string) $access->home_id)
        ->where('service_user_id', $access->client_id)
        ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
        ->whereNotIn('status', ['cancelled'])
        ->with('staff:id,name')
        ->orderBy('start_date')
        ->orderBy('start_time')
        ->get();

    // GDPR: mask staff names to first name only
    $shifts->each(function ($shift) {
        if ($shift->staff) {
            $shift->staff->name = explode(' ', $shift->staff->name)[0];
        }
    });

    return [
        'shifts' => $shifts,
        'week_start' => $start,
        'week_end' => $end,
        'week_days' => collect(range(0, 6))->map(fn($i) => $start->copy()->addDays($i)),
    ];
}

public function getUpcomingScheduleCount(ClientPortalAccess $access): int
{
    return ScheduledShift::where('home_id', (string) $access->home_id)
        ->where('service_user_id', $access->client_id)
        ->where('start_date', '>=', Carbon::today()->toDateString())
        ->whereNotIn('status', ['cancelled', 'completed'])
        ->count();
}
```

**CRITICAL:** `home_id` is VARCHAR in the scheduled_shifts table, so cast to string: `(string) $access->home_id`.

### Step 3: Add Controller Method — schedule()

In `PortalDashboardController.php`, add:

```php
public function schedule(Request $request)
{
    $portalAccess = $request->attributes->get('portal_access');

    // Permission check
    if (!$portalAccess->can_view_schedule) {
        return view('frontEnd.portal.schedule', [
            'portal_access' => $portalAccess,
            'access_denied' => true,
        ]);
    }

    $weekStart = $request->query('week');
    $data = $this->portalService->getScheduleData($portalAccess, $weekStart);

    return view('frontEnd.portal.schedule', array_merge($data, [
        'portal_access' => $portalAccess,
        'access_denied' => false,
    ]));
}
```

**Week navigation:** Use query parameter `?week=2026-04-28` to navigate between weeks. The service method parses it and snaps to Monday. Default = current week.

### Step 4: Update Route

In `routes/web.php`, change:
```php
// FROM:
Route::get('/schedule', [PortalDashboardController::class, 'comingSoon'])->name('portal.schedule');

// TO:
Route::get('/schedule', [PortalDashboardController::class, 'schedule'])->name('portal.schedule');
```

### Step 5: Create Schedule Blade View

`resources/views/frontEnd/portal/schedule.blade.php`:

**Layout:**
- Extends `frontEnd.portal.layouts.master`
- Page title: "My Schedule"
- Subtitle: "View your upcoming sessions and appointments"

**Permission denied state:**
- If `access_denied` is true, show a red alert card: "Access Denied — You do not have permission to view the schedule. Please contact the care team." + Back to Dashboard button.

**Weekly calendar grid (matches CareRoster design):**
- Header row: prev arrow | "Apr 28 – May 4, 2026" | next arrow | "Today" button
- 7-column grid (Mon–Sun), each column header shows: day abbreviation (MON, TUE...), date number, item count for that day
- Today's column highlighted with a different background colour
- Below headers: each column cell lists shift cards for that day
- Each shift card shows: time range (09:00 – 13:00), shift type badge (Morning/Afternoon), staff first name (or "Unfilled" in orange if no staff assigned), status badge if completed

**List view (below grid):**
- Card titled "This Week's Schedule"
- Each shift as a horizontal row: icon | shift type + date | time range | staff name
- Today's shifts get a "Today" badge
- Empty state: calendar icon + "No scheduled items this week"

**Shift card colour coding:**
- Assigned: blue left border
- Completed: green left border
- Unfilled: orange left border + "Unfilled" text instead of staff name

**Status badges:**
- assigned → blue "Assigned"
- completed → green "Completed"
- in_progress → yellow "In Progress"
- unfilled → orange "Unfilled"

### Step 6: Create Schedule JS

`public/js/portal/schedule.js`:
- Week navigation: clicking prev/next arrows changes `?week=` parameter and reloads the page (simple GET — no need for AJAX on a 4h feature)
- "Today" button: navigates to current week (removes `?week=` parameter)
- Alternatively, could use AJAX to load week data without page reload — builder's choice based on time

### Step 7: Update Dashboard Stat Card

In `ClientPortalService::getDashboardData()`, replace the hardcoded `'upcoming_schedule' => 0` with:
```php
'upcoming_schedule' => $access->can_view_schedule
    ? $this->getUpcomingScheduleCount($access)
    : 0,
```

This makes the dashboard stat card show the real count of upcoming shifts.

### Step 8: Write Tests

**Permission tests (3):**
- Portal user with `can_view_schedule=true` → GET /portal/schedule → 200 + sees schedule
- Portal user with `can_view_schedule=false` → GET /portal/schedule → 200 + sees "Access Denied" message
- Non-portal user (no session) → GET /portal/schedule → 302 redirect to /roster

**Cross-client isolation tests (2):**
- Portal user A linked to client X → GET /portal/schedule → only sees shifts for client X
- Portal user A cannot see shifts for client Y (different service_user_id)

**Week navigation tests (2):**
- GET /portal/schedule → shows current week's shifts
- GET /portal/schedule?week=2026-05-04 → shows shifts for that week (May 4–10)

**GDPR test (1):**
- Staff name in response body is first name only ("Allan"), not full name ("Allan Smith")

**IDOR test (1):**
- Manipulating query params cannot reveal shifts from other clients or homes

**Dashboard stat test (1):**
- Portal dashboard shows correct upcoming schedule count (non-zero)

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Cross-client IDOR     │ All queries filter by client_id from session (portal_access.client_id),      │
│                       │ NEVER from request parameters. Family cannot URL-hack to see other clients.  │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Permission bypass     │ Controller checks can_view_schedule BEFORE querying. If false → denied view. │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ GDPR staff data       │ Staff names truncated to first name only in service layer. No staff email,   │
│                       │ phone, or surname exposed. Shift notes/tasks visible (care context).          │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS (server)          │ {{ }} on all user data in Blade. Never {!! !!}.                              │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS (client)          │ esc() helper on any data injected via JS.                                    │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Query param injection │ `week` parameter parsed by Carbon::parse() — invalid values fall back to     │
│                       │ current week. No raw SQL from user input.                                     │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ All queries filter by home_id from portal_access record (session-derived).   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Read-only             │ Schedule view is GET only. No POST/PUT/DELETE endpoints. Portal users cannot  │
│                       │ modify shifts.                                                                │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Reuse portal infrastructure.** No new middleware, layout, or auth flow needed — Feature 1 built all of that. We just add a controller method, service method, and Blade view.

2. **Query `scheduled_shifts` table directly.** Care OS only has `residential` clients in home 8, so we don't need CareRoster's `day_centre` vs `supported_living` branching. One table, one query.

3. **Week navigation via query parameter.** `?week=2026-04-28` reloads the page with that week's data. Simple, works without JS complexity, matches the portal's clean design ethos. Could upgrade to AJAX later if needed.

4. **Staff first name only (GDPR).** The service layer strips surnames before passing data to the view. This is a hard rule from CareRoster's data scoping docs — family members should not see staff personal information.

5. **Seed test data.** Katie (client 27) has 0 shifts. We seed 10 test shifts spanning current + next week so the builder can verify the UI in the browser.

6. **Dashboard stat card goes live.** The hardcoded `upcoming_schedule: 0` on the portal dashboard gets replaced with a real count query. Small win that makes the dashboard feel connected.

## Test Verification (what user tests in browser)

After this feature is built, the user should be able to:

1. Login as `portal_test` / `123456` / home Aries → portal dashboard
2. Dashboard stat card shows non-zero upcoming schedule count
3. Click "Schedule" in nav → see weekly calendar grid (NOT "Coming Soon")
4. See Katie's shifts displayed as cards in the correct day columns
5. Today's column highlighted in a different colour
6. Shift cards show: time range, shift type, staff first name (not full name)
7. Unfilled shifts show "Unfilled" in orange instead of a staff name
8. Click prev/next arrows → grid updates to show different week's shifts
9. Click "Today" → returns to current week
10. Navigate to a week with no shifts → see "No scheduled items" empty state
11. Below the grid: list view shows same shifts with more detail
12. Login as komal (admin) → /roster works normally, /portal/schedule redirects away
