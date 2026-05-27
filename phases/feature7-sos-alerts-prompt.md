# Feature 7 Prompt — SOS Alerts

Copy-paste this for the next session:

---

WORKFLOW: Feature 7 — SOS Alerts
Run `/careos-workflow` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Feature 7 — SOS Alerts
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Skip — model, API controller, API route, DB table all exist
[ ] BUILD — Security hardening + missing functionality (web UI, sticky notification, acknowledge/resolve)
[ ] TEST — Unit + IDOR + security payload tests
[ ] DEBUG — Clear laravel.log, hit all endpoints, check for errors
[ ] REVIEW — Adversarial curl attacks (use the fixed login command from workflow)
[ ] AUDIT — Grep patterns + regression check
[ ] PROD-READY — Curl-verified + manual checklist, user confirms "tested"
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## What Exists (30% done — API only, no web UI)

┌───────────────────────────────────────┬─────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Component │ Status │ Issues │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ DB table sos_alerts │ EXISTS │ Only 3 columns: staff_id, location, deleted_at. Missing: home_id, message, status, │
│ │ │ acknowledged_by, acknowledged_at, resolved_by, resolved_at, latitude, longitude. │
│ │ │ Uses deleted_at (SoftDeletes) instead of is_deleted. Zero records in table. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Model app/Models/staffManagement/ │ EXISTS │ Completely bare — no $fillable, no relationships, no casts, no scopes. │
│ sosAlert.php │ │ Class name breaks PSR-4 (lowercase). │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ API Controller │ EXISTS │ StaffManagementController@sos_alert — creates alert + notifies managers (event_type 24). │
│ Api/Staff/StaffManagementController │ │ Issues: uses whereRaw('FIND_IN_SET(?, home_id)') — SQL injection risk if home_id is tainted. │
│ │ │ No home_id stored on sos_alerts record. No web controller at all. │
│ │ │ Hardcoded user_type='N' check — only staff can trigger, not admins. │
│ │ │ Error response leaks exception message. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ API Route api.php │ EXISTS │ POST /api/staff/sos_alert — no rate limiting, no web equivalent. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Web Routes │ MISSING │ No web routes for SOS alerts (list, trigger, acknowledge, resolve). │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Web Controller │ MISSING │ No roster controller methods for SOS. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Service layer │ MISSING │ No SOS service — logic is all in API controller. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Views / Blade │ MISSING │ No SOS UI anywhere. No trigger button, no alert list, no detail view. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ JavaScript │ MISSING │ No SOS JS code anywhere in public/js/. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Sticky Notification (event type 24) │ MISSING │ sticky_notification.blade.php handles types 4,11,14,15,16,17,18,21,22,23 but NOT 24. │
│ │ │ SOS alerts will be created but never displayed to managers. CRITICAL gap. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Middleware whitelist │ N/A │ API route doesn't go through checkUserAuth. New web routes will need whitelisting. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Test data │ MISSING │ Zero records in sos_alerts table. Zero type-24 notifications. │
├───────────────────────────────────────┼─────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Existing client alerts system │ EXISTS │ Client details page already has an Alerts tab with alert types (Fall Risk, etc.), │
│ │ │ create/acknowledge/resolve/archive flow. SOS should NOT duplicate this — it's a │
│ │ │ separate staff emergency system, not a client alert. │
└───────────────────────────────────────┴─────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## What Needs Building (the plan)

**Goal:** Staff can trigger an SOS alert from the roster dashboard. Managers see it as a sticky notification (Gritter popup) and can acknowledge/resolve it. An SOS Alert History section on the dashboard shows past alerts. All with proper security.

**Scope decision:** This is Phase 1 (Patch & Polish, 2h budget). We are NOT building:

- Mobile app SOS trigger (that's the API endpoint, already exists)
- GPS/latitude/longitude tracking (no geolocation API in web)
- Real-time WebSocket push (sticky notifications load on page refresh)
- Complex status workflow (keep it simple: Active → Acknowledged → Resolved)

**Files to modify:**

1. `app/Models/staffManagement/sosAlert.php` — add $fillable, relationships, casts, scopes
2. `app/Services/Staff/SosAlertService.php` — NEW: create, list, acknowledge, resolve (extract from API controller)
3. `app/Http/Controllers/frontEnd/Roster/SosAlertController.php` — NEW: web controller for trigger, list, acknowledge, resolve
4. `routes/web.php` — add SOS routes with rate limiting inside roster group
5. `app/Http/Middleware/checkUserAuth.php` — whitelist new SOS routes
6. `resources/views/frontEnd/roster/dashboard.blade.php` — add SOS trigger button (prominent, red) + SOS alert history section
7. `resources/views/frontEnd/common/sticky_notification.blade.php` — add event_type_id 24 case for SOS_ALERT display
8. `public/js/roster/sos_alerts.js` — NEW: trigger confirm, list AJAX, acknowledge/resolve handlers

**DB migration needed:** Add columns to sos_alerts table:

- `home_id` INT (CRITICAL — multi-tenancy)
- `message` TEXT nullable (optional description of emergency)
- `status` TINYINT default 1 (1=Active, 2=Acknowledged, 3=Resolved)
- `acknowledged_by` INT nullable (user ID of acknowledging manager)
- `acknowledged_at` DATETIME nullable
- `resolved_by` INT nullable
- `resolved_at` DATETIME nullable
- `is_deleted` TINYINT default 0 (replace deleted_at SoftDeletes pattern)

**NOTE:** Run `DESCRIBE sos_alerts` to verify actual columns before writing migration. If `artisan migrate` fails due to older broken migrations, use `DB::statement('ALTER TABLE ...')` via tinker as fallback.

## Step-by-step implementation

### Step 1: Migration — add missing columns

- Add home_id, message, status, acknowledged_by, acknowledged_at, resolved_by, resolved_at, is_deleted
- If artisan migrate fails, use raw SQL via tinker

### Step 2: Fix Model

- Add $fillable (all columns except id, timestamps)
- Add casts (status→integer, acknowledged_at→datetime, resolved_at→datetime)
- Add scopes: active(), forHome()
- Add relationships: staff() → belongsTo User, acknowledgedBy() → belongsTo User, resolvedBy() → belongsTo User
- Remove SoftDeletes trait if present, use is_deleted flag

### Step 3: Create Service (app/Services/Staff/SosAlertService.php)

- `trigger(int $staffId, int $homeId, ?string $message)` — create alert + create sticky notifications for all managers of that home
- `list(int $homeId)` — list alerts for home, latest first, with staff relationship
- `acknowledge(int $id, int $homeId, int $userId)` — set status=2, acknowledged_by, acknowledged_at
- `resolve(int $id, int $homeId, int $userId)` — set status=3, resolved_by, resolved_at
- Every method filters by home_id (IDOR prevention)

### Step 4: Create Web Controller (app/Http/Controllers/frontEnd/Roster/SosAlertController.php)

- `trigger(Request $request)` — validate message (nullable|string|max:2000), call service, return JSON
- `list(Request $request)` — return paginated alerts for user's home
- `acknowledge(Request $request)` — validate id (required|integer|exists), call service, return JSON
- `resolve(Request $request)` — validate id + notes (nullable|string|max:2000), call service, return JSON
- All methods get home_id from Auth::user() via explode pattern
- Error responses must NOT leak exception messages

### Step 5: Routes + Middleware

Routes (inside roster group, with rate limiting):

- POST `/roster/sos-alert/trigger` → throttle:5,1 (SENSITIVE — low limit)
- POST `/roster/sos-alert/list` → throttle:30,1
- POST `/roster/sos-alert/acknowledge` → throttle:20,1
- POST `/roster/sos-alert/resolve` → throttle:20,1

Whitelist in checkUserAuth.php $allowed_path:

- `roster/sos-alert/trigger`, `roster/sos-alert/list`, `roster/sos-alert/acknowledge`, `roster/sos-alert/resolve`

### Step 6: Dashboard UI

Add to `resources/views/frontEnd/roster/dashboard.blade.php`:

- **SOS Trigger Button** — prominent red button in header area: "🚨 SOS Alert" with confirm() dialog
- **SOS Alert History** — section showing recent alerts as cards:
    - Active = red card with pulse/flashing CSS
    - Acknowledged = amber card
    - Resolved = green card
    - Each card shows: staff name, time, message, status badge, acknowledge/resolve buttons (for managers only)

### Step 7: Fix Sticky Notification

Add event_type_id 24 case to `sticky_notification.blade.php`:

```php
} else if($event_type_id == '24') { // SOS Alert
    $staff_name = App\User::where('id', $event_id)->value('name'); // event_id stores sos_alert id, need to join
    $title = "🚨 SOS Alert";
    $message = " has triggered an SOS alert" . $created_at . ".";
    $type = 'SOS_ALERT';
}
```

NOTE: The current code uses `$su_name` (service user name) for the notification text. SOS alerts are staff-triggered, not service-user-related. The `service_user_id` field in the notification will be null. Handle this: use staff name from the sos_alerts record instead.

### Step 8: JavaScript (public/js/roster/sos_alerts.js)

- `triggerSosAlert()` — confirm dialog → POST to trigger endpoint → show success/reload
- `loadSosAlerts()` — AJAX list → render cards with esc() on all user data
- `acknowledgeSosAlert(id)` — POST to acknowledge → reload list
- `resolveSosAlert(id)` — prompt for notes → POST to resolve → reload list
- All rendered fields use esc() helper
- All AJAX calls have error: callbacks

### Step 9: Insert test data for Aries (home_id 8)

### Step 10: Write tests

- Auth: trigger/list/acknowledge/resolve all reject unauthenticated
- Validation: trigger rejects oversized message, acknowledge/resolve reject non-integer id
- IDOR: list doesn't leak cross-home alerts, acknowledge/resolve reject cross-home records
- Flow: trigger creates alert with status=1 → acknowledge sets status=2 → resolve sets status=3
- XSS: `<script>` in message field saved raw, not executed
- Rate limit: trigger has throttle:5,1

## Security Checklist

┌───────────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface │ Protection │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Input validation │ message: nullable|string|max:2000; id: required|integer|exists:sos_alerts,id │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting │ trigger: throttle:5,1 (SENSITIVE), list: throttle:30,1, ack/resolve: throttle:20,1 │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ XSS (client-side) │ esc() on message, staff name, location in all .html() insertions │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ IDOR │ Every endpoint verifies home_id matches auth user. List filters by home_id. │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment │ $fillable whitelist, no $guarded = []. home_id set server-side, never from request. │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ CSRF │ \_token sent in all AJAX requests │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Access control │ Any authenticated user can trigger SOS. Only managers/admins can acknowledge/resolve. │
│ │ NOTE: Decide if acknowledge/resolve needs user_type check or if any staff in same home can do it. │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Error leaking │ API controller currently returns $e->getMessage() — fix to generic "Something went wrong" │
├───────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ SQL injection │ API controller uses whereRaw('FIND_IN_SET(?, home_id)') — this IS parameterized (? binding), │
│ │ so it's safe. But verify in new service code: use Eloquent only, no raw queries. │
└───────────────────┴────────────────────────────────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Where does the SOS button live?** Roster dashboard header — visible on every dashboard load. NOT on client details (SOS is staff emergency, not client-specific).

2. **Who can trigger?** Any authenticated user (staff, manager, admin). The API restricts to user_type='N' but that seems wrong — anyone might need help.

3. **Who can acknowledge/resolve?** Managers (user_type='M') and admins (user_type='A'). Staff who triggered can't self-acknowledge.

4. **Notification delivery:** Via existing Gritter sticky notifications (loads on page refresh). No real-time push — that's Phase 2.

5. **SOS Alert History:** On the dashboard, below the trigger button. Shows last 10 alerts. Managers see acknowledge/resolve buttons. Staff see read-only cards.

## Value Mapping Check (from Feature 6 learning)

- Status values: 1=Active, 2=Acknowledged, 3=Resolved
- Form/DB/JS must ALL use 1, 2, 3 — no 0-indexed mismatch
- Status badges: 1=red "Active", 2=amber "Acknowledged", 3=green "Resolved"

## DB Column Verification (from Feature 6 learning)

Current sos_alerts columns: id, staff_id, location, deleted_at, created_at, updated_at

- Uses deleted_at (SoftDeletes) — need to add is_deleted and stop using SoftDeletes
- Missing: home_id, message, status, acknowledged_by, acknowledged_at, resolved_by, resolved_at
- If artisan migrate fails, use: `DB::statement('ALTER TABLE sos_alerts ADD COLUMN ...')` via tinker
