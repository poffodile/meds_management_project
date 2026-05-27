# Phase 2, Feature 1 Prompt — Client Portal: Family-Facing Login & Dashboard

Copy-paste this for the next session:

---

WORKFLOW: Phase 2 Feature 1 — Client Portal Login & Dashboard
Run `/careos-workflow-phase2` and follow all 9 stages. Here is the pre-built PLAN to feed into Stage 1.

━━━━━━━━━━━━━━━━━━━━━━
WORKFLOW: Phase 2 Feature 1 — Client Portal Login & Dashboard
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Create migration, model, middleware, portal layout, controller, service, routes
[ ] BUILD — Portal auth flow, dashboard UI, admin portal user management
[ ] TEST — Multi-role + cross-client isolation + security payload tests
[ ] DEBUG — Multi-session test (admin + portal), check laravel.log
[ ] REVIEW — Adversarial curl attacks as BOTH admin and portal user
[ ] AUDIT — Phase 1 grep patterns + GDPR check + portal middleware check
[ ] PROD-READY — Portal user journey + admin portal management journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Feature Classification

**Category: PORT** — CareRoster has a real backend for this (ClientPortalAccess entity reads/writes real data, email-matching auth). We port the schema and rebuild the UI in Laravel Blade.

## What Exists (0% — nothing in Care OS)

┌───────────────────────────────────────┬─────────┬─────────────────────────────────────────────────────────────┐
│ Component                             │ Status  │ Notes                                                       │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ DB table client_portal_accesses       │ MISSING │ Table does not exist. Need to create from Base44 schema.    │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Model ClientPortalAccess              │ MISSING │ No model file anywhere.                                     │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal middleware                     │ MISSING │ No CheckPortalAccess middleware. No portal auth flow.        │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal layout                         │ MISSING │ Only layouts: frontEnd.layouts.master (staff/admin),         │
│                                       │         │ frontEnd.layouts.login, backEnd.layouts.master.              │
│                                       │         │ No portal-specific layout.                                   │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal controller                     │ MISSING │ No portal controllers in app/Http/Controllers.               │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal service                        │ MISSING │ No portal service in app/Services.                           │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal routes                         │ MISSING │ No /portal routes in web.php.                                │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal views                          │ MISSING │ No portal blade views anywhere.                              │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Portal JS                             │ MISSING │ No portal JavaScript files.                                  │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Login redirect logic                  │ EXISTS  │ UserController@login redirects ALL users to /roster after    │
│                                       │         │ login. Needs modification to redirect portal users to        │
│                                       │         │ /portal instead.                                             │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ User table                            │ EXISTS  │ 366 users, 105 in home 8. User types: N=staff(254),         │
│                                       │         │ A=admin(67), M=manager(34), CM=care_manager(11).             │
│                                       │         │ Has: email, user_name, password, home_id, user_type.         │
│                                       │         │ Portal users will be regular user records with a             │
│                                       │         │ ClientPortalAccess record linking them to a client.          │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Service user table                    │ EXISTS  │ 17 clients in home 8. Sample: id=27 name=Katie.             │
│                                       │         │ Portal users are linked to service_user records.              │
├───────────────────────────────────────┼─────────┼─────────────────────────────────────────────────────────────┤
│ Existing layout                       │ EXISTS  │ frontEnd.layouts.master — sidebar, header, footer.           │
│                                       │         │ Roster pages extend this. Portal needs its own simpler       │
│                                       │         │ layout (no admin sidebar, just portal nav).                   │
└───────────────────────────────────────┴─────────┴─────────────────────────────────────────────────────────────┘

## CareRoster Reference (Base44)

CareRoster's portal dashboard (`ClientPortal.jsx`) has:
- Welcome message with client name
- Dashboard stat cards: upcoming schedule count, unread messages, pending requests, notifications
- Quick action buttons: Messages, Schedule, Bookings
- Preview widgets: upcoming schedule items, recent messages
- Handles 4 client types: residential, domiciliary, supported_living, day_centre

**Auth model:** Same user table, email-matching to ClientPortalAccess record. If user's email matches an active ClientPortalAccess → portal user. If not → staff/admin flow.

**CareRoster source:** `/Users/vedangvaidya/Desktop/Omega Life/CareRoster/src/pages/ClientPortal.jsx`

## Base44 Entity Schema → Laravel Migration

```
client_portal_accesses table:
- id              INT AUTO_INCREMENT PRIMARY KEY
- home_id         INT NOT NULL                    -- multi-tenancy (Care OS convention, not in Base44)
- client_id       INT NOT NULL                    -- FK → service_user.id
- client_type     VARCHAR(50) DEFAULT 'residential'  -- residential/domiciliary/supported_living/day_centre
- user_email      VARCHAR(255) NOT NULL           -- matched against user.email on login
- full_name       VARCHAR(255) NOT NULL           -- display name of portal user (e.g., "Jane Smith")
- relationship    VARCHAR(50) NOT NULL            -- self/parent/child/spouse/sibling/guardian/advocate/social_worker/other
- access_level    VARCHAR(50) DEFAULT 'view_and_message' -- view_only/view_and_message/full_access
- can_view_schedule    TINYINT(1) DEFAULT 1
- can_view_care_notes  TINYINT(1) DEFAULT 1
- can_send_messages    TINYINT(1) DEFAULT 1
- can_request_bookings TINYINT(1) DEFAULT 0
- phone           VARCHAR(50) NULL
- is_primary_contact   TINYINT(1) DEFAULT 0
- is_active       TINYINT(1) DEFAULT 1
- activation_date DATE NULL
- last_login      DATETIME NULL
- notes           TEXT NULL
- is_deleted      TINYINT(1) DEFAULT 0           -- Care OS convention
- created_by      INT NOT NULL                    -- FK → user.id (admin who created this)
- created_at      TIMESTAMP NULL
- updated_at      TIMESTAMP NULL

INDEXES:
- INDEX idx_cpa_home_id (home_id)
- INDEX idx_cpa_client_id (client_id)
- INDEX idx_cpa_user_email (user_email)
- INDEX idx_cpa_active (is_active, is_deleted)
```

**NOTE:** Run `DESCRIBE service_user` to verify the actual column names (id, name, home_id, etc.) before writing relationships. If `artisan migrate` fails due to older broken migrations, use `DB::statement('ALTER TABLE ...')` via tinker as fallback.

## Portal Auth Flow (CRITICAL — this is the foundation)

```
User visits /login
    ↓
Enters credentials (username, password, home)
    ↓
UserController@login authenticates via Auth::attempt()
    ↓
Check: Does this user have an active ClientPortalAccess record?
    → YES → redirect to /portal (portal dashboard)
    → NO  → redirect to /roster (normal staff/admin dashboard)
```

**Implementation approach:**
1. After successful login in `UserController@login`, check `ClientPortalAccess::where('user_email', Auth::user()->email)->where('is_active', 1)->where('is_deleted', 0)->first()`
2. If found → `Session::put('portal_access_id', $portalAccess->id)` + `Session::put('portal_client_id', $portalAccess->client_id)` → redirect to `/portal`
3. If not found → existing redirect to `/roster`
4. Create `CheckPortalAccess` middleware that:
   - Reads `session('portal_access_id')`
   - If null → redirect to `/roster` (not a portal user)
   - If set → verify the record still exists and is_active → continue
   - Store the `ClientPortalAccess` instance on the request for controllers to use

## What Needs Building (the plan)

**Goal:** Family members can log in with their credentials and see a portal dashboard showing their linked resident's name, quick stats (placeholder counts for now — real data comes in Features 2-4), and navigation to Schedule, Messages, and Feedback pages. Admins can manage portal user access from the admin side.

**Scope (Feature 1 only — 8h budget):**
- Portal login redirect flow
- Portal Blade layout (separate from admin)
- Portal dashboard page (welcome, stat cards, quick actions)
- Admin portal user management UI (list, create, revoke access)
- Test portal user account seeded for Aries
- CheckPortalAccess middleware
- Portal users CANNOT access /roster/* routes
- Staff/admin users WITHOUT portal access CANNOT access /portal/* routes

**NOT in scope (later features):**
- Schedule view (Feature 2)
- Messaging (Feature 3)
- Feedback forms (Feature 4)
- Real stat counts on dashboard (Features 2-4 will populate these)

**Files to create:**

1. `database/migrations/2026_04_25_200000_create_client_portal_accesses_table.php` — migration
2. `app/Models/ClientPortalAccess.php` — model with $fillable, relationships, scopes
3. `app/Services/Portal/ClientPortalService.php` — portal business logic (dashboard stats, manage access)
4. `app/Http/Controllers/frontEnd/Portal/PortalDashboardController.php` — portal dashboard
5. `app/Http/Controllers/frontEnd/Roster/PortalAccessController.php` — admin portal user management
6. `app/Http/Middleware/CheckPortalAccess.php` — portal auth middleware
7. `resources/views/frontEnd/portal/layouts/master.blade.php` — portal layout (simpler than admin)
8. `resources/views/frontEnd/portal/dashboard.blade.php` — portal dashboard view
9. `resources/views/frontEnd/roster/portal_access.blade.php` — admin portal user management (or section in existing page)
10. `public/js/portal/dashboard.js` — portal dashboard JS
11. `public/js/roster/portal_access.js` — admin portal management JS

**Files to modify:**

1. `app/Http/Controllers/frontEnd/UserController.php` — add portal redirect after login (~line 98)
2. `routes/web.php` — add portal route group + admin portal management routes
3. `app/Http/Middleware/checkUserAuth.php` — whitelist new routes
4. `app/Http/Kernel.php` — register CheckPortalAccess middleware alias

## Step-by-step Implementation

### Step 1: Migration — create client_portal_accesses table

- Create migration with all columns from the schema above
- Apply via tinker DB::statement if artisan migrate fails
- Add indexes on home_id, client_id, user_email, (is_active, is_deleted)

### Step 2: Create Model (app/Models/ClientPortalAccess.php)

- $fillable: all columns except id, timestamps
- $casts: is_active→boolean, is_primary_contact→boolean, can_view_schedule→boolean, can_view_care_notes→boolean, can_send_messages→boolean, can_request_bookings→boolean, activation_date→date, last_login→datetime
- Scopes: active() → where is_active=1, is_deleted=0; forHome($homeId); forClient($clientId); forEmail($email)
- Relationships: client() → belongsTo ServiceUser; createdBy() → belongsTo User
- Do NOT use SoftDeletes — use is_deleted flag (project convention)

### Step 3: Create CheckPortalAccess Middleware

- Check session('portal_access_id') exists
- Verify the ClientPortalAccess record still exists, is_active=1, is_deleted=0
- If invalid → clear portal session vars → redirect to /roster
- If valid → store ClientPortalAccess instance on request → continue
- Register in Kernel.php as 'portal.access'

### Step 4: Create Portal Layout (resources/views/frontEnd/portal/layouts/master.blade.php)

- Standalone HTML (similar structure to frontEnd.layouts.master but simplified)
- Include Bootstrap 3, Font Awesome 4.7, jQuery (same CDN/local assets as admin)
- Simple top navbar with: Care OS logo, "Hi, [full_name]" greeting, Logout button
- Left sidebar or top nav with just 4 links: Dashboard, Schedule, Messages, Feedback
- Schedule/Messages/Feedback links can point to /portal/schedule etc. (placeholder pages for now — show "Coming soon")
- @yield('content') for page content
- Include CSRF meta tag
- Include portal-specific CSS (cleaner, family-friendly design)
- NO admin sidebar, NO staff navigation, NO home switcher

### Step 5: Create Portal Dashboard Controller + View

**Controller** (`app/Http/Controllers/frontEnd/Portal/PortalDashboardController.php`):
- `index()` — get ClientPortalAccess from request (set by middleware), get linked client from service_user, get placeholder stats, return dashboard view
- Get client details: name, date_of_birth, room_number (if exists), photo (if exists)
- Placeholder stats: upcoming_shifts=0, unread_messages=0, pending_requests=0 (Features 2-4 will populate)

**View** (`resources/views/frontEnd/portal/dashboard.blade.php`):
- Welcome banner: "Welcome, [full_name]" with relationship label ("Mother of Katie")
- Resident info card: name, photo (or placeholder avatar), DOB, room
- Stat cards row (4 cards): Upcoming Schedule, Unread Messages, Pending Requests, Notifications — all showing 0 for now with "Coming soon" subtitle
- Quick Actions row: buttons for "View Schedule", "Send Message", "Submit Feedback" — link to /portal/schedule, /portal/messages, /portal/feedback (placeholder pages)
- All user data rendered with {{ }} (Blade escaping)

### Step 6: Create Portal Service (app/Services/Portal/ClientPortalService.php)

- `getDashboardData(ClientPortalAccess $access)` — returns client info + placeholder stats
- `listPortalUsers(int $homeId)` — returns all portal access records for a home (admin use)
- `createPortalAccess(array $data, int $homeId, int $createdBy)` — creates new portal access record
- `revokePortalAccess(int $id, int $homeId)` — sets is_active=0 (soft revoke)
- `deletePortalAccess(int $id, int $homeId)` — sets is_deleted=1
- Every method filters by home_id (multi-tenancy)

### Step 7: Modify Login Redirect (UserController.php)

After successful Auth::attempt() and session setup (around line 98 in UserController@login):

```php
// Check if user is a portal user
$portalAccess = \App\Models\ClientPortalAccess::where('user_email', Auth::user()->email)
    ->where('is_active', 1)
    ->where('is_deleted', 0)
    ->first();

if ($portalAccess) {
    Session::put('portal_access_id', $portalAccess->id);
    Session::put('portal_client_id', $portalAccess->client_id);
    $portalAccess->update(['last_login' => now()]);
    return redirect('/portal')->with('success', 'Welcome ' . $portalAccess->full_name);
}

// Existing redirect for staff/admin
return redirect('/roster')->with('success', 'Welcome back ' . Auth::user()->user_name);
```

**CRITICAL:** The login flow has multiple paths (duplicate login detection, multi-home users, etc.). Read the FULL UserController@login method before modifying. Place the portal check at EVERY successful login redirect point, not just one.

### Step 8: Add Routes

```php
// Portal routes (for family/portal users)
Route::prefix('portal')->middleware(['auth', 'portal.access'])->group(function () {
    Route::get('/', [PortalDashboardController::class, 'index'])->name('portal.dashboard');
    Route::get('/schedule', [PortalDashboardController::class, 'comingSoon'])->name('portal.schedule');
    Route::get('/messages', [PortalDashboardController::class, 'comingSoon'])->name('portal.messages');
    Route::get('/feedback', [PortalDashboardController::class, 'comingSoon'])->name('portal.feedback');
    Route::post('/logout', [PortalDashboardController::class, 'logout'])->name('portal.logout');
});

// Admin portal user management (inside existing roster group)
Route::post('/client/portal-access-list', [PortalAccessController::class, 'list'])->middleware('throttle:30,1');
Route::post('/client/portal-access-save', [PortalAccessController::class, 'save'])->middleware('throttle:20,1');
Route::post('/client/portal-access-revoke', [PortalAccessController::class, 'revoke'])->middleware('throttle:20,1');
Route::post('/client/portal-access-delete', [PortalAccessController::class, 'delete'])->middleware('throttle:20,1');
```

### Step 9: Admin Portal User Management

**Controller** (`app/Http/Controllers/frontEnd/Roster/PortalAccessController.php`):
- `list(Request $request)` — list portal users for home (or for specific client_id)
- `save(Request $request)` — create new portal access with validation:
  - client_id: required|integer|exists:service_user,id
  - user_email: required|email|max:255
  - full_name: required|string|max:255
  - relationship: required|in:self,parent,child,spouse,sibling,guardian,advocate,social_worker,other
  - access_level: nullable|in:view_only,view_and_message,full_access
  - Verify client_id belongs to user's home_id (IDOR prevention)
  - Verify user_email exists in user table (they must have an account first)
- `revoke(Request $request)` — set is_active=0
- `delete(Request $request)` — set is_deleted=1 (admin only)

**UI location:** Add a "Portal Access" tab or section in `client_details.blade.php` for each client, OR create a dedicated page at `/roster/portal-access`. Decide during build — check where it fits best with existing UI.

### Step 10: Seed Test Data

Create a test portal user for Aries (home_id 8):
1. Create a user record (or find an existing one) with email matching a test portal user
2. Create a ClientPortalAccess record linking that user's email to client 27 (Katie)
3. Set relationship='parent', access_level='full_access', all permissions=true
4. Test credentials should be documented in the plan (e.g., username: portal_test, password: 123456, home: 8)

### Step 11: Whitelist Routes in checkUserAuth.php

Add to $allowed_path array:
- `portal` (covers /portal and /portal/*)
- `portal/schedule`
- `portal/messages`
- `portal/feedback`
- `portal/logout`
- `roster/client/portal-access-list`
- `roster/client/portal-access-save`
- `roster/client/portal-access-revoke`
- `roster/client/portal-access-delete`

**NOTE:** Check how checkUserAuth handles the portal prefix — it strips digits with preg_replace, so /portal should be straightforward. But verify.

### Step 12: Write Tests

**Auth tests (4a):**
- Portal dashboard rejects unauthenticated → 302
- Portal dashboard rejects user without ClientPortalAccess → redirect to /roster
- Portal dashboard allows user with active ClientPortalAccess → 200
- Admin portal-access-list rejects unauthenticated → 302

**Multi-role tests (4b):**
- Admin (komal) can access /roster/* but NOT /portal (no ClientPortalAccess) → redirect
- Portal user can access /portal but NOT /roster/* → 403 or redirect
- Portal user cannot access admin portal management endpoints → 403

**Cross-client isolation tests (4c):**
- Create portal user A linked to client X
- Create portal user B linked to client Y
- Portal user A's dashboard shows client X's name, not client Y's
- Admin portal-access-list filters by home_id (cross-home IDOR)

**Validation tests:**
- portal-access-save rejects missing client_id → 422
- portal-access-save rejects invalid relationship → 422
- portal-access-save rejects client_id from different home → 404

**Security payload tests (4g):**
- XSS in full_name: `<script>alert(1)</script>` → stored raw, rendered escaped
- Mass assignment: home_id=999 in save → home_id stays correct
- CSRF: POST without token → 419

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Portal ↔ admin        │ CheckPortalAccess middleware on /portal/* routes. Portal users redirected    │
│ boundary              │ away from /roster/*. Staff redirected away from /portal/*.                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Cross-client IDOR     │ Portal queries scope by client_id from session, never from request.          │
│                       │ Admin endpoints verify client's home_id matches auth user's home_id.         │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ GDPR                  │ Portal dashboard shows client name/DOB/room only. No staff personal          │
│                       │ details (phone, email, address). Only staff first names in messages.          │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ All POST endpoints use $request->validate() with types, max lengths, enums. │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ Admin management: throttle:20,1 on write, throttle:30,1 on read.            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS (server)          │ {{ }} on all user data in Blade. Never {!! !!}.                              │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS (client)          │ esc() helper on all API data before .html() in JS.                           │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ @csrf on forms, $.ajaxSetup with X-CSRF-TOKEN on AJAX.                       │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ $fillable whitelist. home_id and created_by set server-side only.             │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Session security      │ Portal session vars (portal_access_id, portal_client_id) set on login,       │
│                       │ verified by middleware on every request. Cleared on logout.                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Access control        │ portal-access-delete requires user_type='A' (admin only).                    │
│                       │ portal-access-save/revoke allowed for admins and managers (A/M).              │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions (for PLAN approval)

1. **Same login page, different redirect.** Portal users use the existing /login page with the same username/password/home fields. After successful auth, the system checks for a ClientPortalAccess record and redirects accordingly. No separate portal login page needed.

2. **Portal layout is separate from admin layout.** Portal users get `frontEnd.portal.layouts.master` — a clean, simple layout with no admin sidebar. This prevents accidental exposure of admin navigation to family members.

3. **Portal user management lives on the admin side.** Admins manage portal access from the roster/client details area. Family members cannot self-register — an admin must create the ClientPortalAccess record.

4. **Session-based portal detection.** The portal_access_id is stored in session at login time and checked by middleware on every portal request. This avoids querying ClientPortalAccess on every request while still validating access.

5. **Placeholder stats on dashboard.** The dashboard shows stat cards with 0 counts and "Coming soon" labels. Features 2-4 will populate real data (schedule count, message count, feedback count). This lets us ship a working portal shell without waiting for all features.

6. **Portal users need a real user account first.** Before an admin can grant portal access, the family member must have a user record in the `user` table. The admin creates the user account (if needed) and then creates the ClientPortalAccess record linking their email to a specific client.

## Test Verification (what user tests in browser)

After this feature is built, the user should be able to:

1. Login as the test portal user → see the portal dashboard (NOT the admin roster)
2. See the linked resident's name on the dashboard
3. See stat cards (all showing 0 / "Coming soon")
4. See navigation links (Dashboard, Schedule, Messages, Feedback)
5. Click Schedule/Messages/Feedback → see "Coming soon" placeholder page
6. Click Logout → return to login page
7. Login as komal (admin) → see normal /roster dashboard (NOT the portal)
8. Navigate to portal user management → see the test portal user listed
9. Create a new portal access record for another client
10. Revoke a portal access → that family member should get redirected to /roster on next login
