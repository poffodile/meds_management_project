# Phase 2 — Complete UI Walkthrough

> **Purpose:** This document describes every screen, button, panel, modal, and interaction built in Phase 2. Use it to demo the system, onboard new developers, or verify nothing is missing.
>
> **Login credentials:**
> - **Admin:** `komal` / `123456` / home Aries → `http://127.0.0.1:8000`
> - **Portal (family):** Any user whose email has a `client_portal_accesses` record → auto-redirects to `/portal`

---

## Table of Contents

1. [Feature 1 — Client Portal Login & Dashboard](#feature-1--client-portal-login--dashboard)
2. [Feature 2 — Client Portal Schedule View](#feature-2--client-portal-schedule-view)
3. [Feature 3 — Client Portal Messaging](#feature-3--client-portal-messaging)
4. [Feature 4 — Client Portal Feedback & Satisfaction](#feature-4--client-portal-feedback--satisfaction)
5. [Feature 5 — Custom Report Builder](#feature-5--custom-report-builder)
6. [Feature 6 — Scheduled Reports](#feature-6--scheduled-reports)
7. [Feature 7 — Workflow Automation Engine](#feature-7--workflow-automation-engine)
8. [Feature 8 — Pre-built Workflow Templates](#feature-8--pre-built-workflow-templates)
9. [Admin-Side Supporting Pages](#admin-side-supporting-pages)
10. [Navigation Map](#navigation-map)

---

## Feature 1 — Client Portal Login & Dashboard

**URL:** `/portal`
**Controller:** `PortalDashboardController@index`
**View:** `resources/views/frontEnd/portal/dashboard.blade.php`
**Middleware:** `portal.access` (checks `client_portal_accesses` table for active record matching logged-in user's email)

### How a family member gets portal access

1. Admin logs in → navigates to `/roster/client-details/{id}` (any resident's detail page)
2. Clicks the **"Portal Access"** tab (last tab in the client details tab bar)
3. Clicks **"Grant Access"** → an inline form expands with fields:
   - Full Name (text, required)
   - Email (text, required — must match an existing user account in the system)
   - Relationship (dropdown: parent, child, spouse, sibling, guardian, advocate, social worker, self, other)
   - Access Level (dropdown: View & Message, View Only, Full Access)
   - Phone (optional)
   - Notes (optional)
   - Checkboxes: Primary Contact, Can View Schedule, Can View Care Notes, Can Send Messages, Can Request Bookings
4. Admin clicks **Save** → a `client_portal_accesses` record is created linking that email to the resident
5. A table below shows all granted access records with columns: Name | Email | Relationship | Access Level | Status | Last Login | Actions (edit / revoke / delete)

### Portal login flow

1. Family member goes to `/login` and logs in with their regular credentials (same `user` table)
2. The `portal.access` middleware detects they have an active `client_portal_accesses` record
3. They are redirected to `/portal` — the portal dashboard — NOT the admin roster

### Dashboard screen

```
┌─────────────────────────────────────────────────────────────────────┐
│  Welcome, [Full Name]                                               │
│  [Relationship] of [Resident Name]                                  │
│  (gradient blue welcome banner)                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────┐  Resident card                                    │
│  │  Avatar      │  [Resident Full Name]                             │
│  │  circle      │  Date of Birth: [DD/MM/YYYY]                     │
│  └──────────────┘                                                   │
│                                                                     │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌────────────┐│
│  │  Upcoming   │  │  Unread     │  │  Pending    │  │ Notifica-  ││
│  │  Schedule   │  │  Messages   │  │  Requests   │  │ tions      ││
│  │    [N]      │  │    [N]      │  │ Coming soon │  │ Coming soon││
│  └─────────────┘  └─────────────┘  └─────────────┘  └────────────┘│
│                                                                     │
│  Quick Actions:                                                     │
│  [View Schedule]  [Send Message]  [Submit Feedback]                 │
│   (blue btn)       (green btn)     (orange btn)                     │
└─────────────────────────────────────────────────────────────────────┘
```

**Stat cards:** Upcoming Schedule and Unread Messages show live counts from the database. Pending Requests and Notifications show "Coming soon" (not yet built).

**Quick action buttons** link to:
- View Schedule → `/portal/schedule`
- Send Message → `/portal/messages`
- Submit Feedback → `/portal/feedback`

**GDPR note:** The portal ONLY shows data for the linked resident. No other residents are visible. Staff personal details (phone, email, address) are never exposed — only first names appear in messages and schedule.

---

## Feature 2 — Client Portal Schedule View

**URL:** `/portal/schedule`
**Controller:** `PortalDashboardController@schedule`
**View:** `resources/views/frontEnd/portal/schedule.blade.php`

### Screen layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  My Schedule                                                        │
│                                                                     │
│  [< Prev]   Week of 28 Apr – 4 May 2026   [Next >]   [Today]      │
│                                                                     │
│  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐  ┌─────┐  │
│  │ Mon │  │ Tue │  │ Wed │  │ Thu │  │ Fri │  │ Sat │  │ Sun │  │
│  │ 28  │  │ 29  │  │ 30  │  │  1  │  │  2  │  │  3  │  │  4  │  │
│  │(2)  │  │(1)  │  │(0)  │  │(3)  │  │(1)  │  │(0)  │  │(0)  │  │
│  │     │  │     │  │     │  │     │  │     │  │     │  │     │  │
│  │┌───┐│  │┌───┐│  │     │  │┌───┐│  │┌───┐│  │     │  │     │  │
│  ││7-3││  ││7-3││  │     │  ││7-3││  ││3-11│  │     │  │     │  │
│  ││AM ││  ││AM ││  │     │  ││AM ││  ││PM ││  │     │  │     │  │
│  ││John│  ││Jane│  │     │  ││John│  ││Amy ││  │     │  │     │  │
│  │└───┘│  │└───┘│  │     │  │└───┘│  │└───┘│  │     │  │     │  │
│  │┌───┐│  │     │  │     │  │┌───┐│  │     │  │     │  │     │  │
│  ││3-11│  │     │  │     │  ││3-11│  │     │  │     │  │     │  │
│  ││PM ││  │     │  │     │  ││PM ││  │     │  │     │  │     │  │
│  ││Sue ││  │     │  │     │  ││Sue ││  │     │  │     │  │     │  │
│  │└───┘│  │     │  │     │  │└───┘│  │     │  │     │  │     │  │
│  └─────┘  └─────┘  └─────┘  └─────┘  └─────┘  └─────┘  └─────┘  │
│                                                                     │
│  ┌── This Week's Shifts (list view) ────────────────────────────┐  │
│  │ Mon 28 Apr  07:00–15:00  Morning  John    ● Completed        │  │
│  │ Mon 28 Apr  15:00–23:00  Afternoon Sue    ● In Progress      │  │
│  │ Tue 29 Apr  07:00–15:00  Morning  Jane    ● Scheduled        │  │
│  │ ...                                                           │  │
│  └───────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### Interactions

- **Week navigation:** Left/right arrows move one week at a time. "Today" button jumps back to current week.
- **Shift cards** are colour-coded by shift type: Morning (blue), Afternoon (orange), Evening (purple), Night (dark).
- **Status badges:** Completed (green), In Progress (blue), Scheduled (grey), Unfilled (red).
- **Staff names:** Only first name shown (GDPR — no surname, phone, or email).
- **Access check:** If the family member's `can_view_schedule` permission is false, they see an "Access Denied" message with a "Back to Dashboard" button instead of the calendar.

---

## Feature 3 — Client Portal Messaging

**URL:** `/portal/messages`
**Controller:** `PortalDashboardController@messages` (GET), `sendMessage` (POST), `markMessageRead` (POST)
**View:** `resources/views/frontEnd/portal/messages.blade.php`

### Screen layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  Messages — Communicate with your care team                         │
│                                                                     │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐                   │
│  │  Total     │  │  Unread    │  │  Sent      │                   │
│  │  Messages  │  │  Messages  │  │  Messages  │                   │
│  │    [N]     │  │    [N]     │  │    [N]     │                   │
│  └────────────┘  └────────────┘  └────────────┘                   │
│                                                                     │
│  [+ New Message]                                                    │
│                                                                     │
│  ┌── Compose Panel (hidden until New Message clicked) ──────────┐  │
│  │  Priority: [Normal ▼]    Category: [General ▼]               │  │
│  │  Subject:  [________________________]                         │  │
│  │  Message:  [________________________]                         │  │
│  │            [________________________]                         │  │
│  │  [Send Message]  [Cancel]                                     │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌── Message List ──────────────────────────────────────────────┐  │
│  │  ┌ [Staff avatar] Jane — Re: Medication schedule  [2h ago]  ┐│  │
│  │  │ Priority: normal  Category: medication  [● Unread]        ││  │
│  │  │ (click to expand → shows full message + Reply button)     ││  │
│  │  └───────────────────────────────────────────────────────────┘│  │
│  │  ┌ [Family avatar] You — Question about visits    [1d ago]  ┐│  │
│  │  │ Priority: normal  Category: general   [● Read]            ││  │
│  │  └───────────────────────────────────────────────────────────┘│  │
│  └───────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### Interactions

- **New Message button** toggles the compose panel open/closed
- **Compose form fields:** Priority (low/normal/high), Category (general/schedule/medication/care_plan/feedback/concern/request), Subject, Message body
- **Send Message** → POST to `/portal/messages/send` → message appears in list, compose panel closes
- **Message list:** Each message is a clickable row. Clicking expands to show full message content and a **Reply** button (for staff-sent messages)
- **Unread indicator:** Staff messages show blue "unread" dot until clicked. Clicking triggers POST to `/portal/messages/read/{id}`
- **Avatar colours:** Staff messages have a blue avatar, family messages have a green avatar
- **Access check:** `can_send_messages` permission checked — if false, compose panel is hidden

### Admin side — Messaging Center

**URL:** `/roster/messaging-center`
**Sidebar:** General → Messaging Center (also appears as "Client Comms Hub" further down)

The admin sees a **3-panel layout:**
1. **Left panel:** List of all conversations grouped by client, showing latest message preview and unread count
2. **Center panel:** Selected conversation thread showing all messages chronologically
3. **Right panel:** Reply composer with message field

Admins can view all messages for all clients in their home. They reply as `sender_type: staff`.

---

## Feature 4 — Client Portal Feedback & Satisfaction

**URL:** `/portal/feedback`
**Controller:** `PortalDashboardController@feedback` (GET), `submitFeedback` (POST)
**View:** `resources/views/frontEnd/portal/feedback.blade.php`

### Screen layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  Feedback — Share your experience with our care services            │
│                                                                     │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐                   │
│  │  Total     │  │  With      │  │  Pending   │                   │
│  │  Submitted │  │  Responses │  │  Review    │                   │
│  │    [N]     │  │    [N]     │  │    [N]     │                   │
│  └────────────┘  └────────────┘  └────────────┘                   │
│                                                                     │
│  [Submit Feedback]  [My Feedback History]   ← toggle buttons        │
│                                                                     │
│  ┌── Feedback Form (default view) ──────────────────────────────┐  │
│  │  Relationship:    [Parent ▼]                                  │  │
│  │  Feedback Type:   [Compliment ▼]  (general/compliment/        │  │
│  │                    complaint/suggestion/concern)               │  │
│  │  Category:        [Care Quality ▼]  (staff_performance/       │  │
│  │                    care_quality/communication/punctuality/     │  │
│  │                    professionalism/facilities/safety/other)    │  │
│  │  Rating:          ★ ★ ★ ★ ☆  (1-5 clickable stars)          │  │
│  │  Subject:         [________________________]                  │  │
│  │  Comments:        [________________________]                  │  │
│  │                   [________________________]                  │  │
│  │  Contact email:   [____________] (optional)                   │  │
│  │  Contact phone:   [____________] (optional)                   │  │
│  │  [ ] Anonymous    [ ] Request callback                        │  │
│  │                                                               │  │
│  │  [Submit Feedback]  [Cancel]                                  │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  After submit → Success card with green checkmark:                  │
│  "Thank you for your feedback! Your feedback has been submitted     │
│  and our team will review it shortly."                              │
│  [Submit Another]  [View My Feedback]                               │
│                                                                     │
│  ┌── My Feedback History (toggle view) ─────────────────────────┐  │
│  │  ┌ Compliment — "Excellent care"  ★★★★★  [Resolved]  [2d] ┐ │  │
│  │  │ "The morning staff are wonderful..."                     │ │  │
│  │  │ ┌ Staff Response (green box):                            │ │  │
│  │  │ │ "Thank you for your kind words!" — Jane, 1d ago       │ │  │
│  │  │ └───────────────────────────────────────────────────────┘│ │  │
│  │  └──────────────────────────────────────────────────────────┘ │  │
│  │  ┌ Concern — "Scheduling issue"  ★★★☆☆  [In Progress] [5d]┐ │  │
│  │  │ "Sometimes the evening shift arrives late..."            │ │  │
│  │  └──────────────────────────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────┘
```

### Interactions

- **Toggle buttons:** "Submit Feedback" shows the form, "My Feedback History" shows past submissions
- **Star rating:** 5 clickable stars with visual highlight on hover/select
- **Type badges:** Colour-coded — compliment (green), complaint (red), suggestion (blue), concern (orange), general (grey)
- **Status badges:** new (blue), acknowledged (yellow), in_progress (orange), resolved (green), closed (grey)
- **Anonymous checkbox:** If checked, submitted_by is stored as "Anonymous"
- **Staff responses** appear in a green response box below the feedback card in the history view
- **Empty state:** "Share your first feedback" button if no history

### Admin side — Feedback Hub

**URL:** `/roster/feedback-hub`
**Sidebar:** Domiciliary Care → Client Feedback, and also General → (linked)

The admin sees all feedback from all portal users in their home:
- Filter by status/type
- **Acknowledge** button → sets `acknowledged_by_staff_id` and `acknowledged_date`
- **Respond** button → opens text area to write a response (visible to the family member in their portal)
- **Close** button → marks feedback as closed
- Status badge progression: new → acknowledged → in_progress → resolved → closed

---

## Feature 5 — Custom Report Builder

**URL:** `/roster/reports`
**Controller:** `ReportController@index` (page), `generate` (AJAX GET), `scheduleList/Store/Update/Toggle/Delete` (AJAX)
**View:** `resources/views/frontEnd/roster/report/report.blade.php`
**Sidebar:** General → Reporting Engine

### Screen layout — Generate Report tab

```
┌─────────────────────────────────────────────────────────────────────┐
│  Reports                                                            │
│  Generate and schedule reports from your care home data             │
│                                                                     │
│  [Generate Report]  [Scheduled Reports]   ← tab bar                │
│                                                                     │
│  Select a report type:                                              │
│                                                                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────┐│
│  │ Incident │  │ Training │  │   MAR    │  │  Shift   │  │Client││
│  │ Summary  │  │Compliance│  │Compliance│  │ Coverage │  │Feedbk││
│  │  (red)   │  │ (purple) │  │  (pink)  │  │  (blue)  │  │(green)│
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  └──────┘│
│                                                                     │
│  ┌── Filters (appears after selecting report type) ─────────────┐  │
│  │  Date From: [________]   Date To: [________]                  │  │
│  │                                                               │  │
│  │  Type-specific filter (varies):                               │  │
│  │  - Incidents: (no extra filter)                               │  │
│  │  - Training: Status [All/Completed/Pending/Expired ▼]         │  │
│  │  - MAR: Code [All/A (Administered)/R (Refused)/O (Omitted) ▼]│  │
│  │  - Shifts: Type [All/Morning/Afternoon/Night ▼]               │  │
│  │            Status [All/Completed/Unfilled/In Progress ▼]      │  │
│  │  - Feedback: Type [All/Compliment/Complaint/... ▼]            │  │
│  │              Status [All/New/Acknowledged/Resolved/... ▼]     │  │
│  │                                                               │  │
│  │  [Generate]   [Export CSV]  (CSV appears after generation)    │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌── Summary Stats (after generation) ──────────────────────────┐  │
│  │  Total Records: 42   |   Date Range: 01 Apr – 28 Apr 2026   │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌── Report Table ──────────────────────────────────────────────┐  │
│  │  Column headers are sortable (click to sort asc/desc)        │  │
│  │                                                               │  │
│  │  For Incidents:                                               │  │
│  │  Date | Client | Type | Severity | Status | Reported By      │  │
│  │                                                               │  │
│  │  For Training:                                                │  │
│  │  Staff | Training | Status | Date | Score                    │  │
│  │                                                               │  │
│  │  For MAR:                                                     │  │
│  │  Date | Client | Medication | Code | Given By | Notes        │  │
│  │                                                               │  │
│  │  For Shifts:                                                  │  │
│  │  Date | Start | End | Type | Status | Staff | Client         │  │
│  │                                                               │  │
│  │  For Feedback:                                                │  │
│  │  Date | Submitted By | Type | Category | Rating | Status     │  │
│  │                                                               │  │
│  │  (max 500 rows shown; truncation notice if exceeded)         │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  Empty state: "Select a report type above to get started"          │
└─────────────────────────────────────────────────────────────────────┘
```

### Interactions

- **Report type cards** are clickable — clicking one highlights it (blue border) and reveals the filter section
- **Generate button** → AJAX GET to `/roster/reports/generate?type=...&date_from=...&date_to=...&filters...` → populates the table
- **Export CSV** → triggers browser download of the currently displayed data
- **Sort columns** → clicking a column header sorts the table client-side (ascending, then descending on second click)
- **Loading spinner** → overlay appears while report generates

---

## Feature 6 — Scheduled Reports

**URL:** `/roster/reports` (second tab: "Scheduled Reports")
**Controller:** `ReportController@scheduleList/Store/Update/Toggle/Delete`
**View:** Same Blade file as Feature 5 — second tab panel

### Screen layout — Scheduled Reports tab

```
┌─────────────────────────────────────────────────────────────────────┐
│  [Generate Report]  [Scheduled Reports]   ← second tab active      │
│                                                                     │
│  ┌────────────┐  ┌────────────┐                                    │
│  │  Active    │  │  Sent This │                                    │
│  │  Schedules │  │  Month     │                                    │
│  │    [N]     │  │    [N]     │                                    │
│  └────────────┘  └────────────┘                                    │
│                                                                     │
│  [+ New Schedule]                                                   │
│                                                                     │
│  ┌── Schedule Cards ────────────────────────────────────────────┐  │
│  │  ┌ Weekly Incident Report                                   ┐│  │
│  │  │ Type: Incident Summary   Freq: Weekly (Mon)              ││  │
│  │  │ Time: 09:00   Recipients: admin@care.com, mgr@care.com  ││  │
│  │  │ Format: CSV   Next run: Mon 5 May 09:00                 ││  │
│  │  │ Last run: Mon 28 Apr 09:00 [● Success]                  ││  │
│  │  │ [● Active]                                               ││  │
│  │  │ Actions: [Edit] [Toggle] [Delete]                        ││  │
│  │  └──────────────────────────────────────────────────────────┘│  │
│  │                                                               │  │
│  │  ┌ Daily MAR Summary                                        ┐│  │
│  │  │ Type: MAR Compliance   Freq: Daily                       ││  │
│  │  │ Time: 18:00   Recipients: nurse@care.com                 ││  │
│  │  │ Format: Email Summary   Next run: Today 18:00            ││  │
│  │  │ Last run: Yesterday 18:00 [● Success]                    ││  │
│  │  │ [● Active]                                               ││  │
│  │  │ Actions: [Edit] [Toggle] [Delete]                        ││  │
│  │  └──────────────────────────────────────────────────────────┘│  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  Empty state: "No scheduled reports. Create one to automate         │
│  report delivery."                                                  │
└─────────────────────────────────────────────────────────────────────┘
```

### Schedule Create/Edit Modal

```
┌── Schedule Report ──────────────────────────────────────────────────┐
│                                                                     │
│  Report Name:    [Weekly Incident Report___________]                │
│  Report Type:    [Incident Summary ▼]                               │
│  Frequency:      [Weekly ▼]                                         │
│  Day of Week:    [Monday ▼]        (hidden for daily)               │
│  Day of Month:   [1-28 ▼]          (only for monthly)              │
│  Time:           [09:00]                                            │
│  Recipients:     [admin@care.com, mgr@care.com____]  (max 5)       │
│  Output Format:  [CSV ▼]           (CSV or Email Summary)          │
│  [✓] Active                                                        │
│  Notes:          [________________________]                         │
│                                                                     │
│  Next run: Mon 5 May 2026 at 09:00                                 │
│                                                                     │
│  [Cancel]  [Save Schedule]                                          │
└─────────────────────────────────────────────────────────────────────┘
```

### Interactions

- **New Schedule button** → opens the create modal
- **Edit** → opens the same modal pre-populated with existing values
- **Toggle** → enables/disables the schedule (toggles `is_active`, updates the badge)
- **Delete** → confirmation prompt → soft-deletes the schedule
- **Frequency options:** daily, weekly, fortnightly, monthly
- **Day of Week** dropdown appears only for weekly/fortnightly. **Day of Month** appears only for monthly.
- **Next run preview** updates live as you change frequency/day/time
- **Backend:** `php artisan reports:dispatch` runs via Laravel Scheduler, checks `next_run_date`, sends email, advances to next run date

---

## Feature 7 — Workflow Automation Engine

**URL:** `/roster/workflows`
**Controller:** `WorkflowController@index` (page), `list/store/update/toggle/delete/executions` (AJAX)
**View:** `resources/views/frontEnd/roster/workflow/index.blade.php`
**Sidebar:** General → Workflow Automation

### Screen layout

```
┌─────────────────────────────────────────────────────────────────────┐
│  Workflow Automation                                                 │
│  Configure automated notifications and alerts                       │
│                                                                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐          │
│  │  Active  │  │  Total   │  │ Executed │  │  Failed  │          │
│  │    [N]   │  │    [N]   │  │  Today   │  │  Today   │          │
│  │  (green) │  │          │  │    [N]   │  │    [N]   │          │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘          │
│                                                                     │
│  [+ New Workflow]                                                   │
│                                                                     │
│  ┌── Template Gallery (Feature 8 — see below) ──────────────────┐  │
│  │  ... (collapsible section)                                    │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌── COMPLIANCE ─────────────────────────────────────────────────  │
│  │                                                                  │
│  │  ┌ Incident → Notify Manager                                ┐  │
│  │  │ [event] [notification] — incidents with status "new" >= 1│  │
│  │  │ Notify: "New incident reported — please review..."       │  │
│  │  │ Last run: 28 Apr 09:15 [● Success]                      │  │
│  │  │                              [Edit] [Pause] [Delete]     │  │
│  │  └──────────────────────────────────────────────────────────┘  │
│  │                                                                  │
│  ┌── SCHEDULING ─────────────────────────────────────────────────  │
│  │                                                                  │
│  │  ┌ Unfilled Shift Alert  [● Paused]                         ┐  │
│  │  │ [event] [notification] — shifts with status "unfilled">=3│  │
│  │  │ Notify: "There are unfilled shifts..."                   │  │
│  │  │ Last run: Never                                          │  │
│  │  │                           [Edit] [Activate] [Delete]     │  │
│  │  └──────────────────────────────────────────────────────────┘  │
│  │                                                                  │
│  ... (more categories: clinical, training, hr, engagement, etc.)   │
│                                                                     │
│  ┌── Recent Executions ─────────────────────────────────────────┐  │
│  │  Time        │ Workflow              │ Trigger │ Action      │  │
│  │              │                       │         │ Result      │  │
│  │──────────────┼───────────────────────┼─────────┼─────────────│  │
│  │  28 Apr 9:15 │ Incident→Notify Mgr  │ event   │ notification│  │
│  │              │                       │         │ [● Success] │  │
│  │  28 Apr 9:15 │ Incident Spike Alert  │ cond.   │ email       │  │
│  │              │                       │         │ [● Failed]  │  │
│  │              │                       │         │ No valid    │  │
│  │              │                       │         │ recipients  │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  Empty state: "No workflows configured. Create one to automate      │
│  notifications and alerts."                                         │
└─────────────────────────────────────────────────────────────────────┘
```

### Workflow cards show

- **Name** + optional "Paused" badge if inactive
- **Trigger badge:** scheduled (blue), condition (orange), event (purple)
- **Action badge:** notification (green), email (pink)
- **Trigger summary:** e.g., "Daily at 18:00", "incidents count exceeds 5 in last 7 days", "shifts with status 'unfilled' >= 3"
- **Action summary:** e.g., "Notify: 'New incident reported...'" or "Email 2 recipient(s): Weekly Shift Report"
- **Last run:** timestamp + success/failed badge, or "Never"
- **Action buttons:** Edit (pencil icon), Toggle (play/pause icon), Delete (trash icon, red on hover)

### Create/Edit Modal

```
┌── New Workflow ──────────────────────────────────────────────────────┐
│                                                                      │
│  Workflow Name:  [_________________________________]                 │
│  Category:       [Scheduling ▼]                                      │
│                                                                      │
│  ┌── Trigger ────────────────────────────────────────────────────┐  │
│  │  Trigger Type: [Scheduled ▼]                                  │  │
│  │                                                               │  │
│  │  (Scheduled fields — shown when "Scheduled" selected):        │  │
│  │  Frequency: [Daily ▼]                                         │  │
│  │  Day:       [___] (0=Sun weekly, 1-28 monthly, blank daily)  │  │
│  │  Time:      [08:00]                                           │  │
│  │                                                               │  │
│  │  (Condition fields — shown when "Condition" selected):        │  │
│  │  Entity:     [Incidents ▼]                                    │  │
│  │  Condition:  [Count Exceeds ▼]  (count_exceeds/days_since/    │  │
│  │              status_is)                                        │  │
│  │  Threshold:  [5]                                              │  │
│  │  Lookback:   [7] days                                         │  │
│  │                                                               │  │
│  │  (Event fields — shown when "Event" selected):                │  │
│  │  Entity:     [Shifts ▼]                                       │  │
│  │  Status:     [unfilled____]                                   │  │
│  │  Min Count:  [1]                                              │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  ┌── Action ─────────────────────────────────────────────────────┐  │
│  │  Action Type: [Send Notification ▼]                           │  │
│  │                                                               │  │
│  │  (Notification fields):                                       │  │
│  │  Message:    [________________________]                       │  │
│  │  [✓] Sticky notification (stays until dismissed)              │  │
│  │                                                               │  │
│  │  (Email fields):                                              │  │
│  │  Recipients: [admin@care.com, mgr@care.com] (max 5)          │  │
│  │  Subject:    [_________________________________]              │  │
│  │  Message:    [________________________]                       │  │
│  └───────────────────────────────────────────────────────────────┘  │
│                                                                      │
│  Cooldown Hours: [24]  (min hours between trigger fires)            │
│  [✓] Active                                                         │
│                                                                      │
│  [Cancel]  [Save Workflow]                                           │
└──────────────────────────────────────────────────────────────────────┘
```

### How the engine runs

- `php artisan workflows:evaluate` runs every 15 minutes via Laravel Scheduler (registered in `app/Console/Kernel.php`)
- Scans all active, non-deleted workflows across all homes
- For each: checks cooldown, evaluates trigger, executes action if triggered, logs result to `workflow_execution_logs`
- Safety limits: max 20 workflows per home, max 50 executions per hour per home

---

## Feature 8 — Pre-built Workflow Templates

**URL:** `/roster/workflows` (template gallery section within the same page)
**Controller:** `WorkflowController@templates` (GET), `installTemplate` (POST)
**Service:** `WorkflowTemplateRegistry` (static PHP class with 8 template definitions)

### Template Gallery (collapsible section above the workflow list)

```
┌── Template Gallery ──────────────────────── [▲ Hide] ───────────────┐
│  Browse pre-built workflows — click Install to add                  │
│                                                                     │
│  ── COMPLIANCE ──────────────────────────────────────────────────── │
│  ┌ ⚠ Incident → Notify Manager                    [Installed ✓] ┐ │
│  │   Automatically notify managers when a new incident is         │ │
│  │   reported. Ensures timely review and response.                │ │
│  │   [event] [notification]                                       │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌ 📈 Incident Spike Alert                           [Install]   ┐ │
│  │   Email alert when incidents exceed a threshold. Flags         │ │
│  │   potential systemic issues.                                   │ │
│  │   [condition] [email]                                          │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ── SCHEDULING ──────────────────────────────────────────────────── │
│  ┌ 📅 Unfilled Shift Alert                           [Install]   ┐ │
│  │   Alert managers when shifts remain unfilled. Helps ensure     │ │
│  │   adequate staffing coverage.                                  │ │
│  │   [event] [notification]                                       │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌ 📊 Weekly Shift Report                            [Install]   ┐ │
│  │   Send a weekly shift coverage report every Monday morning.    │ │
│  │   [scheduled] [email]                                          │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ── CLINICAL ────────────────────────────────────────────────────── │
│  ┌ 💊 Missed Medication Alert                        [Install]   ┐ │
│  │   Immediate alert when medication is refused or missed.        │ │
│  │   Critical for clinical safety.                                │ │
│  │   [event] [notification]                                       │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ── TRAINING ────────────────────────────────────────────────────── │
│  ┌ 🎓 Training Expiry Warning                        [Install]   ┐ │
│  │   Alert when staff have pending training records that need     │ │
│  │   completion or renewal.                                       │ │
│  │   [condition] [notification]                                   │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ── ENGAGEMENT ──────────────────────────────────────────────────── │
│  ┌ 💬 New Feedback Alert                             [Install]   ┐ │
│  │   Notify staff when new client or family feedback is received. │ │
│  │   [event] [notification]                                       │ │
│  └────────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ── REPORTING ───────────────────────────────────────────────────── │
│  ┌ ✉ Daily Summary Email                             [Install]   ┐ │
│  │   Send a daily operations summary email to managers at 6pm.   │ │
│  │   [scheduled] [email]                                          │ │
│  └────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

### The 8 templates

| # | Name | Category | Trigger | Action | Installs Active? |
|---|------|----------|---------|--------|-----------------|
| 1 | Incident → Notify Manager | Compliance | Event: new incidents >= 1 | Notification (sticky) | Yes |
| 2 | Unfilled Shift Alert | Scheduling | Event: unfilled shifts >= 3 | Notification | Yes |
| 3 | Training Expiry Warning | Training | Condition: pending training > 5 in 30d | Notification | Yes |
| 4 | Missed Medication Alert | Clinical | Event: refused medication >= 1 | Notification (sticky) | Yes |
| 5 | Incident Spike Alert | Compliance | Condition: incidents > 5 in 7d | Email (no recipients) | No* |
| 6 | New Feedback Alert | Engagement | Event: new feedback >= 1 | Notification | No |
| 7 | Daily Summary Email | Reporting | Scheduled: daily at 18:00 | Email (no recipients) | No* |
| 8 | Weekly Shift Report | Scheduling | Scheduled: weekly Mon 08:00 | Email (no recipients) | No* |

*Templates 5, 7, 8 are email actions with empty recipients — they install as **inactive** regardless of their `default_active` flag. Admin must edit to add recipients before activating.

### Interactions

- **Install button** → POST to `/roster/workflows/install-template` → button changes to "Installed ✓" (green, non-clickable), workflow appears in the main list below
- **Email templates:** After install, alert says "Workflow installed! Please edit it to add email recipients before it can send."
- **Duplicate prevention:** Clicking Install on an already-installed template shows error "This template is already installed"
- **Gallery toggle:** Clicking the header bar collapses/expands the gallery. State saved in localStorage — persists across page reloads.
- **After install:** The template becomes a regular workflow — fully editable, togglable, deletable. Deleting an installed template makes the gallery show "Install" again on next load.

---

## Admin-Side Supporting Pages

### Portal Access Management (admin)
**URL:** `/roster/client-details/{id}` → "Portal Access" tab
**Controller:** `PortalAccessController@list/save/revoke/delete`

Not a standalone page — it's a tab inside the client details page. Admin manages which family members can access the portal for each resident. See Feature 1 section above for full details.

### Messaging Center (admin)
**URL:** `/roster/messaging-center`
**Controller:** `MessagingCenterController@index/getThread/reply`
**Sidebar:** General → Messaging Center, and General → Client Comms Hub (both link to same page)

3-panel admin inbox for managing all portal messages. See Feature 3 section above for details.

### Feedback Hub (admin)
**URL:** `/roster/feedback-hub`
**Controller:** `FeedbackHubController@index/list/acknowledge/respond/close`
**Sidebar:** Domiciliary Care → Client Feedback

Admin dashboard for triaging and responding to all client feedback. See Feature 4 section above for details.

---

## Navigation Map

### Portal user (family member)

After login, portal users see a simplified navigation bar (NOT the admin sidebar):

```
Portal Nav:  [Home]  [Schedule]  [Messages]  [Feedback]  [Logout]
                |         |           |           |
                v         v           v           v
           /portal  /portal/   /portal/    /portal/
                    schedule   messages    feedback
```

Portal users **cannot** access any `/roster/*` routes — the `portal.access` middleware blocks them.

### Admin user

Phase 2 features are accessible from the admin sidebar:

```
Sidebar Section         Link                    URL
─────────────────────── ─────────────────────── ──────────────────────
Domiciliary Care        Reports                 /roster/reports
                        Client Feedback         /roster/feedback-hub

General                 Messaging Center        /roster/messaging-center
                        Reporting Engine        /roster/reports
                        Workflow Automation      /roster/workflows
                        Client Comms Hub        /roster/messaging-center

Client Details page     Portal Access tab       /roster/client-details/{id}
```

Note: "Reports" under Domiciliary Care and "Reporting Engine" under General both link to `/roster/reports`. "Messaging Center" and "Client Comms Hub" both link to `/roster/messaging-center`.

---

## Quick Reference — All Phase 2 URLs

| URL | Method | Auth | Feature | Purpose |
|-----|--------|------|---------|---------|
| `/portal` | GET | Portal | F1 | Family dashboard |
| `/portal/schedule` | GET | Portal | F2 | Weekly schedule grid |
| `/portal/messages` | GET | Portal | F3 | Message inbox |
| `/portal/messages/send` | POST | Portal | F3 | Send message |
| `/portal/messages/read/{id}` | POST | Portal | F3 | Mark message read |
| `/portal/feedback` | GET | Portal | F4 | Feedback form + history |
| `/portal/feedback/submit` | POST | Portal | F4 | Submit feedback |
| `/portal/logout` | POST | Portal | F1 | Logout |
| `/roster/client/portal-access-list` | POST | Admin | F1 | List portal access records |
| `/roster/client/portal-access-save` | POST | Admin | F1 | Grant/update portal access |
| `/roster/client/portal-access-revoke` | POST | Admin | F1 | Revoke portal access |
| `/roster/client/portal-access-delete` | POST | Admin | F1 | Delete portal access |
| `/roster/messaging-center` | GET | Admin | F3 | Admin messaging inbox |
| `/roster/messaging-center/thread` | POST | Admin | F3 | Load conversation thread |
| `/roster/messaging-center/reply` | POST | Admin | F3 | Reply to message |
| `/roster/feedback-hub` | GET | Admin | F4 | Admin feedback dashboard |
| `/roster/feedback-hub/list` | GET | Admin | F4 | List all feedback |
| `/roster/feedback-hub/acknowledge` | POST | Admin | F4 | Acknowledge feedback |
| `/roster/feedback-hub/respond` | POST | Admin | F4 | Respond to feedback |
| `/roster/feedback-hub/close` | POST | Admin | F4 | Close feedback |
| `/roster/reports` | GET | Admin | F5 | Report builder page |
| `/roster/reports/generate` | GET | Admin | F5 | Generate report data |
| `/roster/reports/schedules` | GET | Admin | F6 | List scheduled reports |
| `/roster/reports/schedule/store` | POST | Admin | F6 | Create schedule |
| `/roster/reports/schedule/update` | POST | Admin | F6 | Update schedule |
| `/roster/reports/schedule/toggle` | POST | Admin | F6 | Toggle schedule active |
| `/roster/reports/schedule/delete` | POST | Admin | F6 | Delete schedule |
| `/roster/workflows` | GET | Admin | F7 | Workflow page |
| `/roster/workflows/list` | GET | Admin | F7 | List workflows + stats |
| `/roster/workflows/store` | POST | Admin | F7 | Create workflow |
| `/roster/workflows/update` | POST | Admin | F7 | Update workflow |
| `/roster/workflows/toggle` | POST | Admin | F7 | Toggle workflow active |
| `/roster/workflows/delete` | POST | Admin | F7 | Delete workflow |
| `/roster/workflows/executions` | GET | Admin | F7 | Execution log |
| `/roster/workflows/templates` | GET | Admin | F8 | Template gallery data |
| `/roster/workflows/install-template` | POST | Admin | F8 | Install template |
