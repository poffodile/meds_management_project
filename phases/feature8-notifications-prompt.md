WORKFLOW: Feature 8 — Notification Centre
Run `/careos-workflow` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

## Documents to Read Before Starting

Read these files at the START of the session before writing any code:

| Document | Path | Why |
|----------|------|-----|
| **Session logs** | `docs/logs.md` | Prior context, past mistakes, teaching notes from Features 1-7 |
| **Security checklist** | `docs/security-checklist.md` | 15-item vulnerability checklist + grep patterns for AUDIT stage |
| **CareRoster Notification schema** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/Notification.md` | Reference spec — field names, types, priority levels, entity linking |
| **CareRoster Notification data** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/Notification.json` | 69 sample records — understand notification shapes and content |
| **CareRoster DomCare Notification** | `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/export/DomCareNotification.md` | Domiciliary care variant — may have different notification types |
| **Existing Notification model** | `app/Notification.php` | 1,294 lines — understand getSuNotification(), getStickyNotifications(), how events are created |
| **Sticky notification view** | `resources/views/frontEnd/common/sticky_notification.blade.php` | How Gritter popups work, which event types are handled (4,5,11,14-18,21,24) |
| **Notification bar partial** | `resources/views/frontEnd/common/notification_bar.blade.php` | Existing notification sidebar — uses {!! !!} (XSS risk to avoid in new code) |
| **Roster header** | `resources/views/frontEnd/roster/common/roster_header.blade.php` | Line 525: bell icon → needs wiring. Also check sidebar structure for placement |
| **Roster index page** | `resources/views/frontEnd/roster/index.blade.php` | The ACTUAL dashboard page at `/roster` — NOT dashboard.blade.php |
| **Phase 1 plan** | `phases/phase1.md` | Feature list, scope constraints, key rules |
| **CLAUDE.md** | `CLAUDE.md` | Project conventions, security rules, roster page mapping |

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Feature 8 — Notification Centre
[ ] PLAN     — Pre-built below, present to user for approval
[ ] SCAFFOLD — Skip — notification table, model, event types all exist
[ ] BUILD    — Notification centre page, bell icon with count, mark-read, filtering
[ ] TEST     — Unit + IDOR + security payload tests
[ ] DEBUG    — Clear laravel.log, hit all endpoints, check for errors
[ ] REVIEW   — Adversarial curl attacks (use the fixed login command from workflow)
[ ] AUDIT    — Grep patterns + regression check
[ ] PROD-READY — Curl-verified + manual checklist, user confirms "tested"
[ ] PUSH     — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## What Exists (60% done — backend + sticky alerts, no notification centre)

┌───────────────────────────────────────┬─────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│               Component               │ Status  │                                                 Details                                                │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `notification`               │ EXISTS  │ 14 columns: id, home_id, user_id, service_user_id, event_id,                                          │
│                                       │         │ notification_event_type_id, event_action, message, is_sticky,                                          │
│                                       │         │ sticky_individual_ack, sticky_master_ack, sticky_master_ack_timestamp,                                 │
│                                       │         │ status, created_at, updated_at.                                                                        │
│                                       │         │ Missing: is_read, read_at columns for per-user read tracking.                                          │
│                                       │         │ 881 records for home 8. Data exists from multiple event types.                                         │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table `notification_event_type`    │ EXISTS  │ 24 event types (id 1-24): Health Record, Daily Record, Placement Plan,                                │
│                                       │         │ Incident Report, Risk, In Danger, Callback, Assistance, Location,                                      │
│                                       │         │ Money Request, Mood, Behavior, Log Book, SOS_ALERT, etc.                                               │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model `app/Notification.php`          │ EXISTS  │ 1,294 lines. Static methods: getSuNotification(), getStickyNotifications().                            │
│                                       │         │ Returns raw HTML from getSuNotification() — renders notifications server-side.                          │
│                                       │         │ No $fillable defined. Uses `protected $table = 'notification'`.                                        │
│                                       │         │ No is_deleted column — uses status field (0=unread, 1=read maybe?).                                    │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Sticky notification system            │ EXISTS  │ `sticky_notification.blade.php` — Gritter popup alerts included via footer.blade.php.                  │
│                                       │         │ Handles event types 4,5,11,14,15,16,17,18,21,24.                                                      │
│                                       │         │ Individual ack (per user) and master ack (removes for all) supported.                                  │
│                                       │         │ Included on ALL pages via master.blade.php → footer.blade.php.                                        │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Notification bar partial              │ EXISTS  │ `notification_bar.blade.php` — sidebar panel on old dashboard (dashboard.blade.php).                   │
│                                       │         │ Calls getSuNotification() which returns raw HTML ({!! !!}).                                            │
│                                       │         │ NOT included on the actual roster index page (/roster → index.blade.php).                              │
│                                       │         │ XSS risk: uses {!! $notifications !!} — raw HTML output.                                              │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Controllers                           │ EXISTS  │ StickyNotificationController — ack_master(), ack_individual().                                        │
│                                       │         │ API StickyNotificationController — notifications/list, notifications/count.                            │
│                                       │         │ No web controller for a notification centre page.                                                      │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Routes                                │ EXISTS  │ Web: /notification/ack/master/{id}, /notification/ack/indiv/{id}, /notif/response.                     │
│                                       │         │ API: /notifications/list, /notifications/count.                                                        │
│                                       │         │ No web route for a notification centre page or mark-read endpoint.                                     │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Sidebar bell icon                     │ EXISTS  │ roster_header.blade.php line 525: `<a href="#!"><i class='bx bx-bell'></i> Notifications</a>`          │
│                                       │         │ Points to `#!` — NOT wired to any page. Needs to link to /roster/notifications.                       │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Notification centre page              │ MISSING │ No dedicated page to view all notifications, filter, mark read.                                       │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Read/unread tracking                  │ MISSING │ No is_read or read_at column. status field exists (default 0) — may be unused.                        │
│                                       │         │ Need migration to add is_read + read_at, OR repurpose status column.                                  │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Unread count badge                    │ MISSING │ Bell icon has no count. Need AJAX endpoint to get unread count + render badge.                        │
└───────────────────────────────────────┴─────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## What Needs Building (the plan)

**Goal:** A Notification Centre page at `/roster/notifications` where users can see all their notifications, filter by type/date, mark individual or all as read, with an unread count badge on the sidebar bell icon. All with proper security.

**Scope decision:** This is Phase 1 (Patch & Polish). We are NOT building:
- Notification preferences (user chooses which types to receive) — Phase 2
- Real-time push notifications (WebSocket) — Phase 2
- Email/SMS notification delivery — Phase 2
- New notification creation logic (events already create notifications via existing code)

## CRITICAL — Lessons Learned from Features 1-7 (DO NOT REPEAT THESE MISTAKES)

### Mistake 1: Building UI on the wrong Blade file (Feature 7)
**What happened:** SOS Alerts were built on `dashboard.blade.php` but `/roster` renders `index.blade.php`. Button was invisible.
**Prevention:** BEFORE writing any UI code:
1. Identify the target URL from the sidebar link
2. Trace: route in `web.php` → controller method → `return view(...)` → actual Blade file
3. The notification centre is a NEW page at `/roster/notifications` — trace the route you create to confirm the view name matches
4. After build, `curl` the URL and grep for a unique element to confirm it renders

### Mistake 2: UI entry point commented out or unreachable (Feature 4)
**What happened:** Handover link was `<!-- commented out -->` in the sidebar. Feature was invisible.
**Prevention:** After wiring the sidebar bell icon:
1. `curl` the roster page and grep for the bell link
2. Verify the `href` points to `/roster/notifications` (not `#!`)
3. Verify it's NOT inside `<!-- -->` or `{{-- --}}` comments

### Mistake 3: Routes not whitelisted in checkUserAuth (Feature 4)
**What happened:** AJAX calls returned "unauthorize" silently because routes weren't in `$allowed_path`.
**Prevention:** Add ALL new routes to `$allowed_path` in `app/Http/Middleware/checkUserAuth.php` DURING build, not after.

### Mistake 4: Value mapping mismatch (Feature 6)
**What happened:** Form sent values 1-4 but JS mapped 0-3. Every status badge was wrong.
**Prevention:** The notification event types use IDs 1-24. Any type-to-label mapping in JS must use the SAME IDs as the DB. Define a mapping object and verify it matches `notification_event_type` table.

### Mistake 5: XSS via {!! !!} or unescaped .html() (Features 3-6)
**What happened:** Raw HTML rendering without escaping.
**Prevention:**
- The existing `notification_bar.blade.php` uses `{!! $notifications !!}` — this is an XSS risk. The NEW notification centre must use `{{ }}` only.
- All JS `.html()` calls must use the `esc()` helper for user data.
- The `message` field in notifications contains user-generated text — ALWAYS escape it.

### Mistake 6: Missing home_id filtering / IDOR (Features 3-5)
**What happened:** Endpoints returned data from other homes.
**Prevention:** Every query in the notification service must filter by `home_id`. The notification table's `home_id` is varchar (comma-separated like "8,18,1") — use `FIND_IN_SET` or match appropriately.

### Mistake 7: No test data (Feature 7)
**What happened:** Feature built but no data to display during testing.
**Prevention:** Home 8 already has 881 notifications across multiple event types. Verify with `SELECT COUNT(*) FROM notification WHERE home_id LIKE '%8%'` before building.

### Mistake 8: API controller leaking exception messages (Feature 7)
**What happened:** `$e->getMessage()` returned to client in error response.
**Prevention:** All catch blocks return generic "Something went wrong" messages, never `$e->getMessage()`.

## Files to modify

1. `app/Services/Staff/NotificationService.php` — NEW: list, markRead, markAllRead, unreadCount
2. `app/Http/Controllers/frontEnd/Roster/NotificationController.php` — NEW: index (page), list (AJAX), markRead, markAllRead, unreadCount
3. `routes/web.php` — add notification routes inside roster group with rate limiting
4. `app/Http/Middleware/checkUserAuth.php` — whitelist new notification routes
5. `resources/views/frontEnd/roster/notifications.blade.php` — NEW: notification centre page
6. `resources/views/frontEnd/roster/common/roster_header.blade.php` — wire bell icon href + add unread count badge
7. `public/js/roster/notifications.js` — NEW: AJAX list/filter/markRead + bell badge update
8. `database/migrations/XXXX_add_is_read_to_notification.php` — NEW: add is_read, read_at columns

**DB migration needed:** Add columns to `notification` table:
- `is_read` TINYINT default 0
- `read_at` DATETIME nullable
- Index on `is_read` for count queries
- Check if existing `status` column is already used for read/unread — if so, repurpose it instead of adding new columns. Run `SELECT DISTINCT status FROM notification LIMIT 10` to check.

**IMPORTANT — Verify `status` column first:** Before creating a migration, check:
```sql
SELECT DISTINCT status, COUNT(*) FROM notification GROUP BY status;
```
If status is always 0, we can repurpose it as is_read. If it has mixed values, add new columns.

## Step-by-step implementation

### Step 0: Pre-flight checks (MANDATORY — from Feature 7 learning)
- Run `SELECT DISTINCT status, COUNT(*) FROM notification GROUP BY status;` to understand status column usage
- Trace the sidebar "Notifications" link to confirm it currently goes to `#!`
- Trace `/roster` route → controller → view to confirm it's `index.blade.php` (NOT `dashboard.blade.php`)
- Verify notification data exists: `SELECT COUNT(*) FROM notification WHERE home_id LIKE '%8%'`

### Step 1: Migration — add is_read tracking (if needed)
- If `status` is always 0, repurpose it as is_read (no migration needed, just use it)
- If `status` has mixed values, add `is_read` TINYINT default 0 and `read_at` DATETIME nullable
- Add index on the read-tracking column

### Step 2: Create Service (app/Services/Staff/NotificationService.php)
- `list(int $homeId, ?int $userId, ?int $typeId, ?string $startDate, ?string $endDate, int $page = 1)` — paginated list with eager loading of event type
- `markRead(int $id, int $homeId, int $userId)` — mark single notification as read, verify home_id match
- `markAllRead(int $homeId, int $userId)` — mark all unread notifications as read for this user in this home
- `unreadCount(int $homeId, int $userId)` — count unread notifications for bell badge
- Every method filters by home_id (IDOR prevention)
- `user_id` filtering: notifications are targeted to specific users via `user_id` column — filter by Auth user's ID

### Step 3: Create Web Controller (app/Http/Controllers/frontEnd/Roster/NotificationController.php)
- `index()` — return the notification centre Blade view (GET /roster/notifications)
- `list(Request $request)` — validate filters, call service, return JSON (POST)
- `markRead(Request $request)` — validate id (required|integer), call service, return JSON (POST)
- `markAllRead(Request $request)` — call service, return JSON (POST)
- `unreadCount()` — call service, return JSON count (POST)
- All methods get home_id from Auth::user() via explode pattern
- Error responses must NOT leak exception messages

### Step 4: Routes + Middleware
Routes (inside roster group, with rate limiting):
- GET `/roster/notifications` → no throttle (page load)
- POST `/roster/notifications/list` → throttle:30,1
- POST `/roster/notifications/mark-read` → throttle:30,1
- POST `/roster/notifications/mark-all-read` → throttle:20,1
- POST `/roster/notifications/unread-count` → throttle:30,1

Whitelist in checkUserAuth.php `$allowed_path`:
- `roster/notifications`, `roster/notifications/list`, `roster/notifications/mark-read`, `roster/notifications/mark-all-read`, `roster/notifications/unread-count`

### Step 5: Wire sidebar bell icon
In `roster_header.blade.php` line 525:
- Change `href="#!"` to `href="{{ url('/roster/notifications') }}"`
- Add unread count badge: `<span id="notification-badge" class="badge" style="background:#d9534f;color:#fff;position:absolute;top:-5px;right:-5px;font-size:10px;"></span>`
- Add AJAX call to load unread count on every page load (this partial is included on ALL roster pages)

### Step 6: Create Notification Centre page (resources/views/frontEnd/roster/notifications.blade.php)
- Extends master layout, includes roster_header
- **CRITICAL: Verify the page renders at the correct URL** — After creating the route, controller, and view, immediately `curl http://127.0.0.1:8000/roster/notifications` and grep for a unique element
- Filter bar: dropdown for event type (load from notification_event_type table), date range picker
- "Mark All as Read" button
- Notification list: cards showing icon, type label, message, time, read/unread indicator
- Each card has a "Mark as Read" button (or click to mark read)
- Pagination (load more on scroll or page buttons)
- Empty state: "No notifications" message when list is empty

### Step 7: JavaScript (public/js/roster/notifications.js)
- `loadNotifications(page, filters)` — AJAX list → render cards with esc() on all user data
- `markAsRead(id)` — POST to mark-read → update card UI → update badge count
- `markAllAsRead()` — POST to mark-all-read → update all cards → clear badge
- `updateBadgeCount()` — AJAX unread count → update bell badge number
- Event type mapping: object mapping event_type_id → {label, icon, color} — IDs must match DB (1-24)
- All rendered fields use esc() helper
- All AJAX calls have error: callbacks with specific messages

### Step 8: Bell badge on all roster pages
- Add a small inline `<script>` in `roster_header.blade.php` that calls the unread-count endpoint on page load
- Updates the `#notification-badge` element with the count (or hides if 0)
- This runs on EVERY roster page, giving a live unread count

### Step 9: Write tests (12+)
- Auth: list/markRead/markAllRead/unreadCount all reject unauthenticated
- Validation: markRead rejects non-integer id
- IDOR: list doesn't leak cross-home notifications, markRead rejects cross-home record
- Flow: create notification → appears in list → markRead → unreadCount decreases → no longer shows as unread
- XSS: `<script>` in notification message — returned escaped, not raw
- Access control: user can only see notifications targeted to them (user_id match)

## Security Checklist

┌───────────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  Attack Surface   │                                              Protection                                                │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Input validation  │ id: required|integer; type_id: nullable|integer; dates: nullable|date                                  │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting     │ list/markRead/unreadCount: throttle:30,1; markAllRead: throttle:20,1                                   │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (client-side) │ esc() on message, event type name in all .html() insertions                                           │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (server-side) │ {{ }} only in Blade — existing notification_bar uses {!! !!}, new page must NOT                       │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ IDOR              │ Every endpoint filters by home_id AND user_id. List shows only user's notifications.                   │
│                   │ markRead verifies notification belongs to user's home before updating.                                 │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment   │ $fillable whitelist if model is updated. is_read set server-side, never from request.                  │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ CSRF              │ _token sent in all AJAX requests. Bell badge count is POST (not GET) to enforce CSRF.                  │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Access control    │ Any authenticated user can view their own notifications. No admin-only actions needed.                 │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Error leaking     │ All catch blocks return generic message. No $e->getMessage() to client.                               │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ home_id type      │ notification.home_id is VARCHAR (comma-separated IDs like "8,18,1"). Use FIND_IN_SET                  │
│                   │ or exact match — do NOT cast to int or use simple WHERE home_id = X.                                   │
└───────────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Where does the notification centre live?** New page at `/roster/notifications`, linked from sidebar bell icon.

2. **Read/unread tracking:** Either repurpose existing `status` column or add `is_read`/`read_at` — decide after checking status column usage in Step 0.

3. **Who sees what?** Users see notifications where `user_id` matches their ID, filtered by `home_id`. This is different from the existing notification_bar which shows ALL notifications for a home.

4. **Bell badge scope:** Shows on ALL roster pages (roster_header.blade.php is shared). Count updates on page load only (not real-time).

5. **Event type labels:** Map the 24 DB event types to user-friendly labels and icons in JS. IDs must match DB exactly (1-24).

6. **Pagination:** Server-side pagination (10-20 per page) to handle homes with hundreds of notifications.

7. **Relationship to sticky notifications:** Sticky notifications (Gritter popups) continue working as-is. The notification centre is a separate, persistent view of ALL notifications, not just sticky ones.

## DB Column Verification (from Feature 6 learning)
- notification.home_id is VARCHAR — NOT integer. Uses comma-separated home IDs. Use FIND_IN_SET for queries.
- notification.status — check actual usage before deciding read tracking approach.
- notification.user_id — this is the TARGET user (who should see the notification), not the creator.
- notification.service_user_id — the client/service user the notification is about (can be null for staff alerts like SOS).

## Post-Build Verification Checklist (MANDATORY — from Features 4 & 7)
After completing the build, verify ALL of these before moving to TEST:
- [ ] `curl http://127.0.0.1:8000/roster/notifications` returns 200 and contains the notification centre HTML
- [ ] `curl http://127.0.0.1:8000/roster` and grep for the bell icon link — href must be `/roster/notifications`, NOT `#!`
- [ ] Bell icon link is NOT inside `<!-- -->` or `{{-- --}}` comments
- [ ] All 5 new routes are in `$allowed_path` in checkUserAuth.php
- [ ] AJAX list endpoint returns notification data for home 8
- [ ] No `{!! !!}` in the new notifications.blade.php
- [ ] All `.html()` calls in notifications.js use `esc()` for user data
