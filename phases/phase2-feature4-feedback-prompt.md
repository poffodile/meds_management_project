━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 4 — Client Portal Feedback & Satisfaction Forms.
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration, model, service, controllers, Blade views, JS, routes
[ ] BUILD — Portal feedback form + submission + history + admin Feedback Hub + dashboard stat
[ ] TEST — Permission flag, cross-client isolation, validation, IDOR, GDPR, star rating, anonymous
[ ] DEBUG — Login as both portal and admin, submit feedback, respond, check edge cases
[ ] REVIEW — Adversarial curl attacks (IDOR, XSS in feedback body, permission bypass, cross-client, rating tampering)
[ ] AUDIT — Phase 1+2 grep patterns + GDPR check + portal middleware check
[ ] PROD-READY — Two user journeys (portal + admin), manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Context — Read These First

Before starting, read these files for full project context:
- `docs/logs.md` — action log with teaching notes from every prior session
- `phases/phase2.md` — Phase 2 roadmap and feature list
- `CLAUDE.md` — project conventions, security rules, tech stack, git conventions
- `phases/phase2-feature3-messaging-prompt.md` — Feature 3 prompt (pattern reference for controller/service/route structure)

**Test portal user:** `portal_test` / `123456` / home Aries → linked to Katie (client 27, home 8)
**Admin user:** `komal` / `123456` / home Aries (Admin ID 194)

## Feature Classification

**Category: PORT** — CareRoster has two feedback components:
1. `ClientFeedbackForm.jsx` (`src/components/feedback/ClientFeedbackForm.jsx`) — family-facing form with type, category, rating, subject, comments, anonymous option, callback request
2. `ClientFeedback.jsx` (`src/pages/ClientFeedback.jsx`) — admin-side feedback hub with stat cards, status tabs (new/acknowledged/resolved/closed), type/rating filters, acknowledge/respond actions, CSV export

We port both to Laravel Blade. CareRoster's notification creation on submit is **out of scope** (no notification system yet). CSV export is **nice-to-have, not required** — build it only if time permits.

## What Exists (infrastructure from Features 1–3)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal middleware (CheckPortalAccess) │ EXISTS   │ app/Http/Middleware/CheckPortalAccess.php                     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal layout                         │ EXISTS   │ frontEnd.portal.layouts.master — nav has Feedback link        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal controller                     │ EXISTS   │ PortalDashboardController — Feedback route → comingSoon()     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal service                        │ EXISTS   │ ClientPortalService.php — needs getFeedbackData() added       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal route (/portal/feedback)       │ EXISTS   │ Currently → comingSoon(). Needs to point to feedback()        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ClientPortalAccess model              │ EXISTS   │ Has NO can_submit_feedback flag — we need to add one via      │
│                                       │          │ migration, OR default all portal users to allowed (simpler).  │
│                                       │          │ Decision: use can_send_messages flag as a proxy (feedback is  │
│                                       │          │ a form of communication). See design decisions below.         │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Dashboard "Submit Feedback" button    │ EXISTS   │ dashboard.blade.php line 170 — links to /portal/feedback      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin nav "Client Feedback" link      │ EXISTS   │ roster_header.blade.php line 482 — points to dead `#` link    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ client_portal_feedback table          │ MISSING  │ Need to create migration + model                              │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ClientPortalFeedback model            │ MISSING  │ Need to create with $fillable, scopes, relationships          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal feedback Blade view            │ MISSING  │ Need: frontEnd.portal.feedback.blade.php                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin feedback hub Blade view         │ MISSING  │ Need: frontEnd.roster.feedback.feedback_hub.blade.php         │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin FeedbackHubController           │ MISSING  │ Need to create for admin-side feedback management             │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Test portal user                      │ EXISTS   │ portal_test / 123456 / home 8 → linked to Katie (client 27)   │
│                                       │          │ Katie has 0 feedback. Need to seed test feedback.             │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## CareRoster Reference (Base44)

**Two components in CareRoster:**

### 1. ClientFeedbackForm.jsx (family-facing form)
- Fields: your name (pre-filled), relationship dropdown (self/family/guardian/representative/other), feedback type (compliment/complaint/suggestion/concern/general), category (staff_performance/care_quality/communication/punctuality/professionalism/facilities/safety/other), star rating (1-5 clickable stars), subject, comments (textarea), contact email, contact phone, wants callback checkbox, anonymous checkbox
- On submit: creates feedback record with status "new", auto-sets priority (complaint → high, others → medium)
- Success state: "Thank You!" card with green checkmark

### 2. ClientFeedback.jsx (admin hub)
- **Stat cards:** Total, New (orange), Compliments (green), Avg Rating (yellow stars)
- **Status tab filters:** All | New | Acknowledged | Resolved
- **Dropdown filters:** feedback type, rating
- **Feedback list:** cards with coloured left border (complaint=red, compliment=green, suggestion=blue), subject, type badge, status badge, category badge, submitter name ("Anonymous" if anonymous), client name, date, star rating display, comments section, callback request alert (blue box), response section (green box), action buttons
- **Actions per status:**
  - New → Acknowledge button, Respond button
  - Acknowledged → Respond button
  - Resolved → Close button
- **Respond modal:** shows original feedback + textarea for response → sets status to "resolved", records response_date and assigned_to_staff_id
- **Export CSV** button (nice-to-have)

**CareRoster extras we skip (out of scope):**
- Notification creation on feedback submit (no notification system yet)
- "Email Client" button (opens mailto: — not useful in a care home context)
- AI categorization or sentiment analysis

## Database Design

### client_portal_feedback table (new)

```sql
CREATE TABLE client_portal_feedback (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_id             INT UNSIGNED NOT NULL,
    client_id           INT UNSIGNED NOT NULL,            -- FK → service_user.id (the resident)
    submitted_by        VARCHAR(255) NOT NULL,             -- name of submitter
    submitted_by_id     INT UNSIGNED NOT NULL,             -- FK → client_portal_accesses.id
    relationship        VARCHAR(50) NOT NULL DEFAULT 'family', -- self, family, guardian, representative, other
    feedback_type       VARCHAR(30) NOT NULL DEFAULT 'general', -- compliment, complaint, suggestion, concern, general
    category            VARCHAR(30) NOT NULL DEFAULT 'care_quality', -- staff_performance, care_quality, communication, punctuality, professionalism, facilities, safety, other
    rating              TINYINT UNSIGNED NOT NULL DEFAULT 5, -- 1-5 star rating
    subject             VARCHAR(255) NOT NULL,
    comments            TEXT NOT NULL,
    priority            VARCHAR(20) NOT NULL DEFAULT 'medium', -- low, medium, high
    status              VARCHAR(20) NOT NULL DEFAULT 'new', -- new, acknowledged, in_progress, resolved, closed
    is_anonymous        TINYINT(1) NOT NULL DEFAULT 0,
    wants_callback      TINYINT(1) NOT NULL DEFAULT 0,
    contact_email       VARCHAR(255) NULL,
    contact_phone       VARCHAR(50) NULL,
    response            TEXT NULL,                         -- admin's response text
    response_date       TIMESTAMP NULL,
    responded_by        INT UNSIGNED NULL,                 -- FK → user.id (staff who responded)
    responded_by_name   VARCHAR(255) NULL,                 -- denormalized staff name
    acknowledged_by     INT UNSIGNED NULL,                 -- FK → user.id
    acknowledged_date   TIMESTAMP NULL,
    is_deleted          TINYINT(1) NOT NULL DEFAULT 0,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,
    INDEX idx_home_client (home_id, client_id),
    INDEX idx_status (status),
    INDEX idx_type (feedback_type),
    INDEX idx_home_status (home_id, status),
    INDEX idx_created (created_at)
);
```

**Design decisions:**
- `home_id` for multi-tenancy (all admin queries filter by home_id).
- `client_id` for portal data isolation (portal queries filter by client_id).
- `submitted_by_id` links to portal_access record (so we know who submitted it).
- `is_anonymous` flag — when true, admin sees "Anonymous" instead of the submitter's name. The record still stores `submitted_by` and `submitted_by_id` for audit purposes, but the admin UI hides them.
- `priority` auto-set on creation: complaint → high, concern → medium, others → medium.
- No `deleted_at` / SoftDeletes — using project convention `is_deleted` flag.
- `responded_by_name` denormalized so we don't need joins for display.

**NOTE:** `home_id` is INT UNSIGNED in this table (not VARCHAR like scheduled_shifts).

## What Needs Building (the plan)

**Goal:** Family portal users can submit feedback/satisfaction forms about the care their loved one receives. Admins can view, acknowledge, respond to, and manage all feedback in a dedicated hub. Feedback includes star ratings, typed categories, optional anonymous submission, and callback requests.

**Scope (Feature 4 — 4h budget):**

### Portal side:
- Feedback page with two views: submit form + submission history
- Submit feedback form: type, category, star rating, subject, comments, anonymous, callback, contact info
- View past feedback submissions (own submissions only) with status indicators
- Permission check via `can_send_messages` flag (feedback is a form of communication)

### Admin side:
- Client Feedback Hub page — new controller + Blade view
- Stat cards: total, new/pending, compliments, average rating
- Status tab filters (All/New/Acknowledged/Resolved/Closed)
- Feedback list with coloured cards, type/status badges, star ratings
- Acknowledge action (one-click status update)
- Respond action (modal with textarea → sets status to resolved)
- Close action for resolved feedback

### Dashboard:
- Portal dashboard "Submit Feedback" button already exists and links to /portal/feedback — just needs the page to not be "Coming Soon"

### NOT in scope:
- Notification system on feedback submit
- CSV export (nice-to-have, only if time permits)
- "Email Client" button
- AI sentiment analysis
- File attachments on feedback
- Push notifications to admin

## Files to Create

1. `app/Models/ClientPortalFeedback.php` — model with $fillable, scopes, relationships
2. `app/Services/Portal/PortalFeedbackService.php` — business logic for both portal and admin sides
3. `app/Http/Controllers/frontEnd/Roster/FeedbackHubController.php` — admin-side feedback management
4. `resources/views/frontEnd/portal/feedback.blade.php` — portal feedback form + history view
5. `resources/views/frontEnd/roster/feedback/feedback_hub.blade.php` — admin feedback hub
6. `public/js/portal/feedback.js` — portal-side star rating widget, AJAX form submit, toggle views
7. `public/js/roster/feedback_hub.js` — admin-side tab filtering, acknowledge/respond actions, AJAX

## Files to Modify

1. `app/Http/Controllers/frontEnd/Portal/PortalDashboardController.php` — add `feedback()` and `submitFeedback()` methods
2. `routes/web.php` — update portal /feedback route, add POST routes for submit, add admin feedback routes
3. `resources/views/frontEnd/roster/common/roster_header.blade.php` — wire "Client Feedback" link (line 482) from `#` to `{{ url('/roster/feedback-hub') }}`
4. `app/Http/Middleware/checkUserAuth.php` — whitelist new admin routes (/roster/feedback-hub, POST endpoints)

## Step-by-step Implementation

### Step 1: Create Migration & Model

Create `client_portal_feedback` table via tinker `DB::statement()` (artisan migrate has known issues). Create `ClientPortalFeedback` model with:

```php
protected $table = 'client_portal_feedback';

protected $fillable = [
    'home_id', 'client_id', 'submitted_by', 'submitted_by_id', 'relationship',
    'feedback_type', 'category', 'rating', 'subject', 'comments',
    'priority', 'status', 'is_anonymous', 'wants_callback',
    'contact_email', 'contact_phone',
    'response', 'response_date', 'responded_by', 'responded_by_name',
    'acknowledged_by', 'acknowledged_date',
    'is_deleted',
];

protected $casts = [
    'rating' => 'integer',
    'is_anonymous' => 'boolean',
    'wants_callback' => 'boolean',
    'is_deleted' => 'boolean',
    'response_date' => 'datetime',
    'acknowledged_date' => 'datetime',
];
```

**Scopes:** `scopeForHome($homeId)`, `scopeForClient($clientId)`, `scopeActive()` (where is_deleted=0), `scopeByStatus($status)`.

**Relationships:** `client()` → ServiceUser, `portalAccess()` → ClientPortalAccess.

### Step 2: Create PortalFeedbackService

`app/Services/Portal/PortalFeedbackService.php` — shared service for both portal and admin sides:

```php
// Portal methods
getFeedbackForPortal(ClientPortalAccess $access): Collection
    → Feedback where client_id = access.client_id AND home_id = access.home_id
    → Only own submissions (submitted_by_id = access.id)
    → Sorted by created_at DESC

submitFeedback(ClientPortalAccess $access, array $data): ClientPortalFeedback
    → home_id from access (NOT from user input — IDOR prevention)
    → client_id from access (NOT from user input)
    → submitted_by_id = access.id
    → submitted_by = access.full_name
    → Auto-set priority: complaint → 'high', concern → 'medium', others → 'medium'
    → Status = 'new'
    → Validate: subject required max:255, comments required max:5000, feedback_type in list,
      category in list, rating integer 1-5, relationship in list, is_anonymous boolean,
      wants_callback boolean, contact_email nullable|email|max:255, contact_phone nullable|max:50

getFeedbackStats(ClientPortalAccess $access): array
    → Count of own submissions, count with responses

// Admin methods
getAllFeedbackForHome(int $homeId, ?string $status = null, ?string $type = null): Collection
    → All feedback in this home, filtered by status/type
    → If is_anonymous, replace submitted_by with "Anonymous" in the returned data
    → Include client name via relationship
    → Sorted by created_at DESC (newest first)

getAdminStats(int $homeId): array
    → total, new, compliments, complaints, average rating

acknowledgeFeedback(int $feedbackId, int $homeId, int $staffId): bool
    → Verify feedback.home_id === homeId
    → Set status='acknowledged', acknowledged_by=staffId, acknowledged_date=now()

respondToFeedback(int $feedbackId, int $homeId, int $staffId, string $staffName, string $response): bool
    → Verify feedback.home_id === homeId
    → Set status='resolved', response=text, response_date=now(), responded_by=staffId, responded_by_name=staffName

closeFeedback(int $feedbackId, int $homeId): bool
    → Verify feedback.home_id === homeId, status='resolved'
    → Set status='closed'
```

### Step 3: Portal Controller Methods

In `PortalDashboardController.php`, add:

```php
public function feedback(Request $request)
{
    $portalAccess = $request->attributes->get('portal_access');

    if (!$portalAccess->can_send_messages) {
        return view('frontEnd.portal.feedback', [
            'portal_access' => $portalAccess,
            'access_denied' => true,
        ]);
    }

    $feedbackService = app(PortalFeedbackService::class);
    $feedbackList = $feedbackService->getFeedbackForPortal($portalAccess);
    $stats = $feedbackService->getFeedbackStats($portalAccess);

    return view('frontEnd.portal.feedback', [
        'portal_access' => $portalAccess,
        'access_denied' => false,
        'feedback_list' => $feedbackList,
        'stats' => $stats,
    ]);
}

public function submitFeedback(Request $request)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'comments' => 'required|string|max:5000',
        'feedback_type' => 'required|in:compliment,complaint,suggestion,concern,general',
        'category' => 'required|in:staff_performance,care_quality,communication,punctuality,professionalism,facilities,safety,other',
        'rating' => 'required|integer|min:1|max:5',
        'relationship' => 'required|in:self,family,guardian,representative,other',
        'is_anonymous' => 'nullable|boolean',
        'wants_callback' => 'nullable|boolean',
        'contact_email' => 'nullable|email|max:255',
        'contact_phone' => 'nullable|string|max:50',
    ]);

    $portalAccess = $request->attributes->get('portal_access');

    if (!$portalAccess->can_send_messages) {
        return response()->json(['status' => false, 'message' => 'Permission denied'], 403);
    }

    $feedbackService = app(PortalFeedbackService::class);
    $feedback = $feedbackService->submitFeedback($portalAccess, $request->only([
        'subject', 'comments', 'feedback_type', 'category', 'rating',
        'relationship', 'is_anonymous', 'wants_callback', 'contact_email', 'contact_phone',
    ]));

    return response()->json(['status' => true, 'feedback' => $feedback]);
}
```

### Step 4: Admin Controller — FeedbackHubController

Create `app/Http/Controllers/frontEnd/Roster/FeedbackHubController.php`:

```php
public function index(Request $request)
{
    $homeId = explode(',', auth()->user()->home_id)[0];
    $feedbackService = app(PortalFeedbackService::class);
    $stats = $feedbackService->getAdminStats((int)$homeId);

    return view('frontEnd.roster.feedback.feedback_hub', [
        'stats' => $stats,
        'home_id' => $homeId,
    ]);
}

public function list(Request $request)
{
    $request->validate([
        'status' => 'nullable|in:new,acknowledged,in_progress,resolved,closed',
        'type' => 'nullable|in:compliment,complaint,suggestion,concern,general',
    ]);
    $homeId = explode(',', auth()->user()->home_id)[0];
    $feedbackService = app(PortalFeedbackService::class);
    $feedback = $feedbackService->getAllFeedbackForHome(
        (int)$homeId, $request->status, $request->type
    );
    return response()->json(['status' => true, 'feedback' => $feedback]);
}

public function acknowledge(Request $request)
{
    $request->validate(['feedback_id' => 'required|integer']);
    $homeId = explode(',', auth()->user()->home_id)[0];
    $feedbackService = app(PortalFeedbackService::class);
    $result = $feedbackService->acknowledgeFeedback(
        (int)$request->feedback_id, (int)$homeId, auth()->user()->id
    );
    return response()->json(['status' => $result]);
}

public function respond(Request $request)
{
    $request->validate([
        'feedback_id' => 'required|integer',
        'response' => 'required|string|max:5000',
    ]);
    $homeId = explode(',', auth()->user()->home_id)[0];
    $feedbackService = app(PortalFeedbackService::class);
    $result = $feedbackService->respondToFeedback(
        (int)$request->feedback_id,
        (int)$homeId,
        auth()->user()->id,
        auth()->user()->name,
        $request->response
    );
    return response()->json(['status' => $result]);
}

public function close(Request $request)
{
    $request->validate(['feedback_id' => 'required|integer']);
    $homeId = explode(',', auth()->user()->home_id)[0];
    $feedbackService = app(PortalFeedbackService::class);
    $result = $feedbackService->closeFeedback(
        (int)$request->feedback_id, (int)$homeId
    );
    return response()->json(['status' => $result]);
}
```

### Step 5: Update Routes

```php
// Portal routes (inside existing portal group)
Route::get('/feedback', [PortalDashboardController::class, 'feedback'])->name('portal.feedback');
Route::post('/feedback/submit', [PortalDashboardController::class, 'submitFeedback'])->middleware('throttle:20,1')->name('portal.feedback.submit');

// Admin routes (inside existing roster group)
Route::get('/feedback-hub', [FeedbackHubController::class, 'index'])->name('roster.feedback-hub');
Route::get('/feedback-hub/list', [FeedbackHubController::class, 'list'])->middleware('throttle:30,1');
Route::post('/feedback-hub/acknowledge', [FeedbackHubController::class, 'acknowledge'])->middleware('throttle:30,1');
Route::post('/feedback-hub/respond', [FeedbackHubController::class, 'respond'])->middleware('throttle:30,1');
Route::post('/feedback-hub/close', [FeedbackHubController::class, 'close'])->middleware('throttle:20,1');
```

### Step 6: Create Portal Feedback Blade View

`resources/views/frontEnd/portal/feedback.blade.php`:

**Layout:**
- Extends `frontEnd.portal.layouts.master`
- Page title: "Feedback"
- Subtitle: "Share your experience with our care services"

**Permission denied state:**
- If `access_denied` is true, show "Access Denied" card (same pattern as schedule/messages).

**Main view (two sections, toggle via buttons):**

**Section A — Submit Feedback Form:**
- "We Value Your Feedback" header with icon
- Two-column row: Relationship dropdown (pre-selected to portal_access.relationship), Feedback Type dropdown (compliment/complaint/suggestion/concern/general)
- Two-column row: Category dropdown, (empty or spacer)
- Star rating widget: 5 clickable stars with visual fill, "X/5" label
- Subject: text input
- Comments: textarea (6 rows)
- Contact section (collapsible or always visible):
  - Contact email input (pre-filled from portal_access.user_email)
  - Contact phone input
  - "I would like someone to contact me" checkbox
  - "Submit anonymously" checkbox
- Cancel + Submit buttons
- Submit via AJAX POST → on success, show "Thank You!" success card with green checkmark, then auto-switch to history view after 3 seconds

**Section B — My Feedback History:**
- Stat summary: total submitted, with responses, pending
- List of own past submissions sorted newest first
- Each card: subject, type badge (colour-coded: compliment=green, complaint=red, suggestion=blue, concern=orange, general=grey), status badge, star rating display, date, comments preview (truncated)
- If status is "resolved" or "closed", show the response in a green box below
- Empty state: "No feedback submitted yet" + "Share Your First Feedback" button

**Star rating widget CSS:**
- Stars are clickable FA icons (fa-star / fa-star-o)
- Filled stars: gold (#f5a623)
- Hover: highlight stars up to hovered position
- jQuery click handler sets hidden input value

### Step 7: Create Admin Feedback Hub Blade View

`resources/views/frontEnd/roster/feedback/feedback_hub.blade.php`:

**Layout:** Extends roster master layout. Single-page AJAX-powered hub.

**Stat cards row (4 cards):**
- Total Feedback (blue icon) — count
- New / Pending (orange icon) — count of status='new'
- Compliments (green icon) — count of feedback_type='compliment'
- Average Rating (yellow stars) — average of all ratings, displayed as "4.2/5" with star icons

**Filter bar:**
- Status tab buttons: All | New | Acknowledged | Resolved | Closed
- Type dropdown: All Types | Compliments | Complaints | Suggestions | Concerns | General
- Active tab highlighted

**Feedback list (loaded via AJAX, re-fetched on filter change):**
- Each feedback as a card with coloured left border:
  - complaint → red border
  - compliment → green border
  - suggestion → blue border
  - concern → orange border
  - general → grey border
- Card content:
  - Row 1: subject (bold), type badge, status badge, category badge
  - Row 2: "From: [name or Anonymous]" • "Client: [client name]" • date/time
  - Row 3: star rating (filled/empty stars)
  - Row 4: comments (full text in a grey box)
  - Row 5 (if wants_callback): blue info box "Contact requested: email / phone"
  - Row 6 (if response exists): green box with response, response date, responder name
  - Row 7: action buttons based on status:
    - New → [Acknowledge] [Respond]
    - Acknowledged → [Respond]
    - Resolved → [Close]
    - Closed → (no actions)

**Respond modal:**
- Shows original feedback subject + comments
- Textarea for response (required, max 5000)
- Cancel + Send Response buttons
- AJAX POST → on success, close modal, refresh list

**Acknowledge action:** One-click AJAX POST → refresh card in place.

**Close action:** One-click AJAX POST → refresh card in place.

### Step 8: Wire Admin Nav Link

In `roster_header.blade.php`, change the "Client Feedback" dead link (line 482) from `#` to `{{ url('/roster/feedback-hub') }}`.

### Step 9: Seed Test Feedback

Seed 6-8 test feedback entries for Katie (client 27, home 8) so there's data to display on both portal and admin sides:

```php
$feedback = [
    // Compliment — resolved
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'compliment', 'category' => 'care_quality', 'rating' => 5,
        'subject' => 'Wonderful care for Katie', 'comments' => 'The team has been absolutely wonderful with Katie. She always seems happy and well-cared for when we visit. Special thanks to the morning team.',
        'priority' => 'medium', 'status' => 'resolved', 'is_anonymous' => 0, 'wants_callback' => 0,
        'response' => 'Thank you so much for your kind words, Jane. We will pass your compliments on to the morning team. Katie is a pleasure to care for.',
        'response_date' => now()->subDays(3), 'responded_by' => 194, 'responded_by_name' => 'Komal Gautam',
    ],
    // Complaint — new (urgent)
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'complaint', 'category' => 'communication', 'rating' => 2,
        'subject' => 'Not informed about schedule change', 'comments' => 'Katie\'s Wednesday afternoon session was cancelled last week and nobody told us until we arrived. This has happened twice now. We need better communication about schedule changes.',
        'priority' => 'high', 'status' => 'new', 'is_anonymous' => 0, 'wants_callback' => 1,
        'contact_email' => 'jane.smith@example.com', 'contact_phone' => '07700 900123',
    ],
    // Suggestion — acknowledged
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'suggestion', 'category' => 'facilities', 'rating' => 4,
        'subject' => 'Garden area improvement', 'comments' => 'The garden area is lovely but could benefit from some more seating and shade. Katie enjoys being outside and it would be nice to have more comfortable spots.',
        'priority' => 'medium', 'status' => 'acknowledged', 'is_anonymous' => 0, 'wants_callback' => 0,
        'acknowledged_by' => 194, 'acknowledged_date' => now()->subDays(1),
    ],
    // Concern — new
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'concern', 'category' => 'safety', 'rating' => 3,
        'subject' => 'Wet floor in corridor', 'comments' => 'Noticed the corridor floor was wet during my visit on Tuesday with no warning sign. This could be a slip hazard for residents.',
        'priority' => 'medium', 'status' => 'new', 'is_anonymous' => 0, 'wants_callback' => 0,
    ],
    // Anonymous general — new
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'general', 'category' => 'staff_performance', 'rating' => 4,
        'subject' => 'Staff are friendly', 'comments' => 'Overall the staff are very friendly and approachable. Always happy to answer questions about Katie\'s day.',
        'priority' => 'medium', 'status' => 'new', 'is_anonymous' => 1, 'wants_callback' => 0,
    ],
    // Compliment — closed
    [
        'submitted_by' => 'Jane Smith', 'submitted_by_id' => 1, 'relationship' => 'family',
        'feedback_type' => 'compliment', 'category' => 'professionalism', 'rating' => 5,
        'subject' => 'Excellent medication management', 'comments' => 'Very impressed with how professionally the medication administration is handled. Always on time and thoroughly documented.',
        'priority' => 'medium', 'status' => 'closed', 'is_anonymous' => 0, 'wants_callback' => 0,
        'response' => 'Thank you Jane. Our medication team takes great pride in their work. We\'ll share your feedback with them.',
        'response_date' => now()->subDays(7), 'responded_by' => 194, 'responded_by_name' => 'Komal Gautam',
    ],
];
// Each gets: home_id=8, client_id=27, is_deleted=0, created_at staggered over past 10 days
```

### Step 10: Write Tests

**Permission tests (2):**
- Portal user with `can_send_messages=true` → GET /portal/feedback → 200, sees feedback form
- Portal user with `can_send_messages=false` → GET /portal/feedback → 200, sees "Access Denied"

**Submit feedback tests (3):**
- Portal user submits feedback → POST /portal/feedback/submit → 200, record created in DB with correct client_id, home_id, submitted_by_id, status='new'
- Portal user submits complaint → priority auto-set to 'high'
- Portal user submits with invalid feedback_type → 422

**IDOR tests (2):**
- Portal user submits feedback with tampered client_id in request body → ignored, uses session client_id
- Portal user submits feedback with tampered home_id → ignored, uses session home_id

**Cross-client isolation (1):**
- Portal user can only see own feedback submissions, not feedback from other portal users for same client

**Anonymous feedback (1):**
- Admin views anonymous feedback → submitted_by shows "Anonymous", not the real name

**Admin acknowledge (1):**
- Admin acknowledges feedback → status changes to 'acknowledged', acknowledged_by and acknowledged_date set

**Admin respond (1):**
- Admin responds to feedback → status changes to 'resolved', response text saved, responded_by set

**Admin home isolation (1):**
- Admin cannot acknowledge/respond to feedback from a different home → rejected

**Star rating validation (1):**
- Submit with rating=0 or rating=6 → 422

**Auth (1):**
- Unauthenticated → GET /portal/feedback → 302 redirect

**GDPR (1):**
- Anonymous feedback in admin hub shows "Anonymous" not submitter name; non-anonymous shows full name

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Cross-client IDOR     │ Portal: client_id AND home_id always from session (portal_access), NEVER     │
│                       │ from request body on submit. submitted_by_id from portal_access.id.           │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Permission bypass     │ Controller checks can_send_messages BEFORE any query or mutation.             │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS in feedback       │ {{ }} on all feedback content in Blade (both portal and admin views).         │
│                       │ esc() helper for AJAX-rendered content in JS.                                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Anonymous privacy     │ is_anonymous checked in service layer — admin UI shows "Anonymous" not real   │
│                       │ name. Real identity stored in DB for audit but never exposed in admin UI.     │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ All POST routes use CSRF token. AJAX uses $.ajaxSetup X-CSRF-TOKEN header.    │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:20,1 on feedback submit and close. throttle:30,1 on                  │
│                       │ acknowledge/respond/list.                                                      │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ subject: required|max:255. comments: required|max:5000. rating: integer|1-5.  │
│                       │ All enum fields: in:list. contact_email: nullable|email|max:255.              │
│                       │ contact_phone: nullable|max:50. No raw SQL.                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ Portal: home_id from portal_access. Admin: home_id from auth user.            │
│                       │ All admin actions verify feedback.home_id matches admin's home.                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ home_id, client_id, submitted_by_id, priority, status set server-side, NOT    │
│                       │ from request. Model uses $fillable whitelist.                                  │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rating tampering      │ Server validates rating is integer between 1-5. Frontend star widget is UI    │
│                       │ only — server is authoritative.                                                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Admin impersonation   │ Admin routes require checkUserAuth middleware (existing).                      │
│                       │ Portal routes require CheckPortalAccess middleware (existing).                 │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Reuse `can_send_messages` for permission gating.** The ClientPortalAccess model has no `can_submit_feedback` flag. Rather than adding a new migration column, we treat feedback as a form of communication and gate it on `can_send_messages`. If the client wants a separate flag later, it's a 5-minute migration.

2. **Separate service file, not shared with messages.** `PortalFeedbackService.php` is its own file, not bolted onto `PortalMessageService`. Feedback has different data shapes, workflows (acknowledge → respond → close), and query patterns.

3. **New admin controller, not reusing MessagingCenterController.** Feedback Hub is a distinct admin feature with its own route, views, and actions. Cleaner to have `FeedbackHubController` than to overload the messaging controller.

4. **Anonymous feedback: stored but hidden.** The `is_anonymous` flag controls what admins see in the UI. The DB always stores the real submitter for audit/compliance purposes. The service layer replaces the name with "Anonymous" before returning data to the admin view.

5. **Auto-priority on creation.** Complaints auto-set to 'high' priority, everything else to 'medium'. This matches CareRoster's logic and ensures complaints get attention. Admins cannot change priority (keep it simple — out of scope).

6. **Portal users see their own history.** Unlike messages (which show both sides of a conversation), feedback history only shows the portal user's own submissions + any responses. They can track whether their feedback was acknowledged/resolved.

7. **Star rating as clickable FA icons.** Using Font Awesome `fa-star` / `fa-star-o` with jQuery click handlers. This avoids importing any new dependencies and matches the project's existing jQuery + FA stack.

8. **No CSV export in initial build.** CareRoster has this but it's a nice-to-have. Build the core feedback loop first; export can be added later if needed.

## Test Verification (what user tests in browser)

### Portal side (login as portal_test / 123456 / home Aries):
1. Dashboard "Submit Feedback" button links to /portal/feedback (NOT "Coming Soon")
2. Click "Feedback" in sidebar → see feedback page with form and history
3. Star rating widget works: click star 3 → stars 1-3 fill gold, stars 4-5 empty
4. Fill in form: select "Complaint", category "Communication", 2 stars, subject, comments
5. Check "I would like someone to contact me" → email/phone fields become relevant
6. Check "Submit anonymously" checkbox
7. Click Submit → "Thank You!" success message appears
8. Switch to history view → see the just-submitted feedback with "New" status badge
9. Older seeded feedback visible with status badges (Resolved shows response in green box)
10. Star ratings display correctly on history cards

### Admin side (login as komal / 123456 / home Aries):
11. Navigate to "Client Feedback" in sidebar → Feedback Hub loads
12. Stat cards show correct counts (total, new, compliments, avg rating)
13. Feedback list shows all seeded entries with correct coloured borders
14. Anonymous feedback shows "Anonymous" not "Jane Smith"
15. Click status tabs (New/Acknowledged/Resolved) → list filters correctly
16. Click "Acknowledge" on a new feedback → status changes to "Acknowledged"
17. Click "Respond" on a feedback → modal opens with original text
18. Type response → Send → feedback status changes to "Resolved", response appears
19. Click "Close" on resolved feedback → status changes to "Closed"
20. Complaint feedback has red border and "high" priority indicator

### Cross-role verification:
21. Portal user cannot access /roster/feedback-hub (redirects)
22. Admin without portal access cannot see /portal/feedback (redirects)
