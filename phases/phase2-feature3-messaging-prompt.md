━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 3 — Client Portal Messaging with Care Team. 
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration, model, service, controllers, Blade views, JS, routes
[ ] BUILD — Portal inbox/compose/thread + admin Client Comms Hub + dashboard stat
[ ] TEST — Permission flag, cross-client isolation, send/reply, mark-read, GDPR, IDOR
[ ] DEBUG — Login as both portal and admin, send messages both ways, check edge cases
[ ] REVIEW — Adversarial curl attacks (IDOR, XSS in message body, permission bypass, cross-client)
[ ] AUDIT — Phase 1+2 grep patterns + GDPR check + portal middleware check
[ ] PROD-READY — Two user journeys (portal + admin), manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Feature Classification

**Category: PORT** — CareRoster has two real messaging components:
1. `ClientPortalMessages.jsx` — family-facing inbox with compose, read, reply
2. `ClientCommunicationHub.jsx` — admin-side chat-style UI with client list sidebar, message thread, and reply input

We port both to Laravel Blade. CareRoster's AI categorization and "Book Appointment from chat" features are **out of scope** (4h budget). Attachments are **planned but unbuilt** in CareRoster — we skip them too.

## What Exists (infrastructure from Features 1 & 2)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal middleware (CheckPortalAccess) │ EXISTS   │ app/Http/Middleware/CheckPortalAccess.php                     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal layout                         │ EXISTS   │ frontEnd.portal.layouts.master — nav has Messages link        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal controller                     │ EXISTS   │ PortalDashboardController — Messages route → comingSoon()     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal service                        │ EXISTS   │ ClientPortalService.php — needs getMessagesData() added       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal route (/portal/messages)       │ EXISTS   │ Currently → comingSoon(). Needs to point to messages()        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ClientPortalAccess model              │ EXISTS   │ Has can_send_messages flag (boolean, default true)            │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin MessagingCenterController       │ EXISTS   │ app/Http/Controllers/frontEnd/Roster/MessagingCenterController│
│                                       │          │ — currently returns empty Blade view                          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin messaging route                 │ EXISTS   │ /roster/messaging-center → MessagingCenterController@index    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin messaging Blade                 │ EXISTS   │ messaging_center.blade.php — empty, extends master layout     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin nav links                       │ EXISTS   │ "Messaging Center" links at lines 491, 511 of roster_header   │
│                                       │          │ "Client Comms Hub" link at line 539 (points to #! dead link)  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ client_portal_messages table          │ MISSING  │ Need to create migration + model                              │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ ClientPortalMessage model             │ MISSING  │ Need to create with $fillable, scopes, relationships          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Portal messages Blade view            │ MISSING  │ Need: frontEnd.portal.messages.blade.php                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Admin comms hub Blade content         │ MISSING  │ Need: populate messaging_center.blade.php with comms hub UI   │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Test portal user                      │ EXISTS   │ portal_test / 123456 / home 8 → linked to Katie (client 27)   │
│                                       │          │ Katie has 0 messages. Need to seed test messages.             │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## CareRoster Reference (Base44)

**Two separate systems in CareRoster:**
1. **MessagingCenter** (sidebar → "Messaging Center") — staff/shift focused only (shift requests, bulk messages to staff). NO family/portal messages. We do NOT touch this.
2. **ClientCommunicationHub** (sidebar → "Client Comms Hub") — admin/staff view and reply to family messages. Chat-style UI with client list sidebar, message thread, reply input. Uses `ClientMessage` entity.

**Connection:** Family sends message via portal → stored in `ClientPortalMessage` → admin sees it in Client Comms Hub and replies from there. Both sides read/write the same table, linked by `client_id`.

**CareRoster extras we skip (out of scope):**
- AI categorization of incoming messages (LLM call on every message)
- AI suggested responses
- "Book Appointment" button from within chat
- File attachments (schema ready but no UI built in CareRoster)
- Booking form modal

**Portal side UI (from ClientPortalMessages.jsx):**
- **Inbox view:** stat cards (total, unread, sent) + message list sorted by date
- **Compose form:** To (Care Team — fixed), Subject, Category dropdown, Priority dropdown, Message body, Send button
- **Message detail view:** subject, sender name, sender type label ("Care Team" or "You"), date/time, priority badge, category badge, message body, Reply button (for staff messages)
- **Permission check:** `can_send_messages` — if false, show "Access Denied" card
- **Mark as read:** when family views a staff message, mark it as read
- **Empty state:** "No messages yet" + "Send Your First Message" button
- **Categories:** general, schedule, medication, care_plan, feedback, concern, request
- **Priorities:** low, normal, high

**Admin side UI (from ClientCommunicationHub.jsx):**
- **Three-panel layout:** client list sidebar (left) | message thread (center) | stats sidebar (right)
- **Client list:** avatar initial, client name, last message preview, unread count badge, urgent count badge. Sorted by urgent > unread > recent. Search bar to filter.
- **Message thread:** chat-bubble style. Staff messages (blue, right-aligned), family messages (white, left-aligned). Each bubble shows: category icon, sender label ("Care Team" or client name), priority badge, message body, timestamp, read status.
- **Reply input:** textarea at bottom with Send button. Enter to send, Shift+Enter for newline.
- **Stats sidebar:** priority distribution, category breakdown, quick stats (unread, pending response, today's messages).
- **No client selected state:** "Select a Client" placeholder.

## Database Design

### client_portal_messages table (new)

Based on the Phase 2 workflow schema (19 fields) adapted for Care OS:

```sql
CREATE TABLE client_portal_messages (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    home_id         INT UNSIGNED NOT NULL,
    client_id       INT UNSIGNED NOT NULL,            -- FK → service_user.id (the resident)
    sender_type     VARCHAR(20) NOT NULL,              -- 'family', 'staff'
    sender_id       INT UNSIGNED NOT NULL,             -- portal_access.id (family) or user.id (staff)
    sender_name     VARCHAR(255) NOT NULL,             -- denormalized display name
    recipient_type  VARCHAR(20) NOT NULL DEFAULT 'all_staff', -- 'family', 'all_staff'
    recipient_id    INT UNSIGNED NULL,                 -- specific portal_access.id or user.id (null for all_staff)
    subject         VARCHAR(255) NOT NULL,
    message_content TEXT NOT NULL,
    priority        VARCHAR(20) NOT NULL DEFAULT 'normal', -- low, normal, high
    category        VARCHAR(30) NOT NULL DEFAULT 'general', -- general, schedule, medication, care_plan, feedback, concern, request
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    read_at         TIMESTAMP NULL,
    read_by         VARCHAR(255) NULL,                 -- name of person who read it
    replied_to_id   BIGINT UNSIGNED NULL,              -- FK → self (threading)
    status          VARCHAR(20) NOT NULL DEFAULT 'sent', -- sent, delivered, read, archived
    is_deleted      TINYINT(1) NOT NULL DEFAULT 0,
    created_by      INT UNSIGNED NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    INDEX idx_home_client (home_id, client_id),
    INDEX idx_sender (sender_type, sender_id),
    INDEX idx_read_status (is_read, sender_type),
    INDEX idx_client_date (client_id, created_at)
);
```

**Design decisions:**
- Single table for both directions (family→staff and staff→family). Direction determined by `sender_type`.
- `home_id` for multi-tenancy (all queries filter by home_id on admin side).
- `client_id` for portal data isolation (all queries filter by client_id on portal side).
- No `deleted_at` / SoftDeletes — using project convention `is_deleted` flag.
- `replied_to_id` enables simple one-level threading (reply to a specific message).
- `sender_name` denormalized so we don't need joins for display.
- No `attachments` column — out of scope, can add later.

**NOTE:** `home_id` is INT UNSIGNED in this table (not VARCHAR like scheduled_shifts).

## What Needs Building (the plan)

**Goal:** Two-way messaging between family portal users and the care team.
- Portal users can compose messages to the care team, view their inbox, read staff replies, and reply back.
- Admin/staff can view all portal messages grouped by client in a chat-style interface, and reply to family messages.
- Permission-gated by `can_send_messages` flag.
- GDPR: staff names shown as first name only on the portal side.
- Dashboard stat card for unread messages goes live.

**Scope (Feature 3 — 4h budget):**

### Portal side:
- Messages controller method (or dedicated PortalMessagesController)
- Inbox view with stat cards, message list, compose form, message detail
- Send message (POST) — family → all_staff
- View message + mark as read
- Reply to staff message
- Permission check on `can_send_messages`

### Admin side:
- Populate the existing empty `messaging_center.blade.php` with Client Comms Hub UI
- Client list sidebar with search, unread/urgent badges
- Message thread (chat-bubble style) for selected client
- Reply input for staff → family
- Mark messages as read when staff views them
- Simple stats panel (unread, today, by priority)

### Dashboard:
- Update portal dashboard `unread_messages` stat card from hardcoded 0 to real count

### NOT in scope:
- AI categorization / suggested responses
- "Book Appointment" from chat
- File attachments
- Bulk messaging
- Push notifications
- Per-recipient read receipts
- Message search/filtering on portal side (keep it simple)

## Files to Create

1. `app/Models/ClientPortalMessage.php` — model with $fillable, scopes, relationships
2. `app/Services/Portal/PortalMessageService.php` — business logic for both sides
3. `resources/views/frontEnd/portal/messages.blade.php` — portal inbox/compose/detail view
4. `public/js/portal/messages.js` — portal-side compose toggle, AJAX send, mark-as-read
5. `public/js/roster/messaging_center.js` — admin-side client selection, thread loading, reply send

## Files to Modify

1. `app/Http/Controllers/frontEnd/Portal/PortalDashboardController.php` — add `messages()` and `sendMessage()` methods
2. `app/Http/Controllers/frontEnd/Roster/MessagingCenterController.php` — add thread loading, reply, mark-read
3. `app/Services/Portal/ClientPortalService.php` — add `getUnreadMessageCount()`
4. `routes/web.php` — update portal /messages route, add POST routes for send/reply on both sides
5. `resources/views/frontEnd/roster/messaging/messaging_center.blade.php` — populate with Client Comms Hub UI
6. `resources/views/frontEnd/portal/dashboard.blade.php` — remove "Coming soon" from messages stat card
7. `resources/views/frontEnd/roster/common/roster_header.blade.php` — wire "Client Comms Hub" link (line 539) to `/roster/messaging-center`
8. `app/Http/Middleware/checkUserAuth.php` — whitelist new POST routes

## Step-by-step Implementation

### Step 1: Create Migration & Model

Create `client_portal_messages` table via tinker `DB::statement()` (artisan migrate has known issues). Create `ClientPortalMessage` model with:

```php
protected $table = 'client_portal_messages';

protected $fillable = [
    'home_id', 'client_id', 'sender_type', 'sender_id', 'sender_name',
    'recipient_type', 'recipient_id', 'subject', 'message_content',
    'priority', 'category', 'is_read', 'read_at', 'read_by',
    'replied_to_id', 'status', 'is_deleted', 'created_by',
];

protected $casts = [
    'is_read' => 'boolean',
    'is_deleted' => 'boolean',
    'read_at' => 'datetime',
];
```

**Scopes:** `scopeForHome($homeId)`, `scopeForClient($clientId)`, `scopeActive()` (where is_deleted=0), `scopeUnread()` (where is_read=0).

**Relationships:** `client()` → ServiceUser, `repliedTo()` → self, `replies()` → self (hasMany).

### Step 2: Create PortalMessageService

`app/Services/Portal/PortalMessageService.php` — shared service for both portal and admin sides:

```php
// Portal methods
getMessagesForPortal(ClientPortalAccess $access): Collection
    → Messages where client_id = access.client_id AND home_id = access.home_id
    → GDPR: staff sender_name truncated to first name only
    → Sorted by created_at DESC

sendPortalMessage(ClientPortalAccess $access, array $data): ClientPortalMessage
    → sender_type = 'family', sender_id = access.id, sender_name = access.full_name
    → recipient_type = 'all_staff'
    → client_id from access (NOT from user input — IDOR prevention)
    → home_id from access (NOT from user input)
    → Validate: subject required max:255, message_content required max:5000, category in list, priority in list

markAsRead(int $messageId, ClientPortalAccess $access): bool
    → Only mark if message.client_id === access.client_id (IDOR check)
    → Only mark staff→family messages as read by family
    → Set is_read=1, read_at=now(), read_by=access.full_name, status='read'

getUnreadCount(ClientPortalAccess $access): int
    → Count where client_id=access.client_id, sender_type='staff', is_read=0

// Admin methods
getClientsWithMessages(int $homeId): Collection
    → Distinct clients who have messages in this home
    → Include unread count, last message preview, urgent count
    → Sorted: urgent first, then unread, then recent

getThreadForClient(int $homeId, int $clientId): Collection
    → All messages for this client in this home
    → Sorted by created_at ASC (oldest first for chat view)
    → Include sender info

sendStaffReply(int $homeId, int $staffId, string $staffName, int $clientId, array $data): ClientPortalMessage
    → sender_type = 'staff', sender_id = staffId, sender_name = staffName
    → recipient_type = 'family'
    → client_id from verified client record (check client exists in home)
    → home_id from admin session (NOT from user input)

markAsReadByStaff(int $messageId, int $homeId, string $staffName): bool
    → Only mark family→staff messages as read by staff
    → Verify message.home_id === homeId
```

### Step 3: Portal Controller Methods

In `PortalDashboardController.php`, add:

```php
public function messages(Request $request)
{
    $portalAccess = $request->attributes->get('portal_access');

    if (!$portalAccess->can_send_messages) {
        return view('frontEnd.portal.messages', [
            'portal_access' => $portalAccess,
            'access_denied' => true,
        ]);
    }

    $messageService = app(PortalMessageService::class);
    $messages = $messageService->getMessagesForPortal($portalAccess);
    $stats = [
        'total' => $messages->count(),
        'unread' => $messages->where('sender_type', 'staff')->where('is_read', false)->count(),
        'sent' => $messages->where('sender_type', 'family')->count(),
    ];

    return view('frontEnd.portal.messages', [
        'portal_access' => $portalAccess,
        'access_denied' => false,
        'messages' => $messages,
        'stats' => $stats,
    ]);
}

public function sendMessage(Request $request)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'message_content' => 'required|string|max:5000',
        'category' => 'required|in:general,schedule,medication,care_plan,feedback,concern,request',
        'priority' => 'required|in:low,normal,high',
        'replied_to_id' => 'nullable|integer',
    ]);

    $portalAccess = $request->attributes->get('portal_access');

    if (!$portalAccess->can_send_messages) {
        return response()->json(['status' => false, 'message' => 'Permission denied'], 403);
    }

    $messageService = app(PortalMessageService::class);
    $message = $messageService->sendPortalMessage($portalAccess, $request->only([
        'subject', 'message_content', 'category', 'priority', 'replied_to_id',
    ]));

    return response()->json(['status' => true, 'message' => $message]);
}

public function markMessageRead(Request $request, $id)
{
    $portalAccess = $request->attributes->get('portal_access');
    $messageService = app(PortalMessageService::class);
    $result = $messageService->markAsRead((int)$id, $portalAccess);

    return response()->json(['status' => $result]);
}
```

### Step 4: Admin Controller Methods

In `MessagingCenterController.php`, expand:

```php
public function index(Request $request)
{
    $homeId = explode(',', auth()->user()->home_id)[0];
    $messageService = app(PortalMessageService::class);
    $clientsWithMessages = $messageService->getClientsWithMessages((int)$homeId);

    return view('frontEnd.roster.messaging.messaging_center', [
        'clients_with_messages' => $clientsWithMessages,
        'home_id' => $homeId,
    ]);
}

public function getThread(Request $request)
{
    $request->validate(['client_id' => 'required|integer']);
    $homeId = explode(',', auth()->user()->home_id)[0];
    $messageService = app(PortalMessageService::class);

    // Verify client belongs to this home
    $client = \App\ServiceUser::where('id', $request->client_id)
        ->where('home_id', $homeId)
        ->first();
    if (!$client) {
        return response()->json(['status' => false, 'message' => 'Client not found'], 404);
    }

    $thread = $messageService->getThreadForClient((int)$homeId, (int)$request->client_id);

    // Mark unread family messages as read by staff
    $thread->where('sender_type', 'family')->where('is_read', false)->each(function ($msg) use ($homeId) {
        $messageService = app(PortalMessageService::class);
        $messageService->markAsReadByStaff($msg->id, (int)$homeId, auth()->user()->name);
    });

    return response()->json([
        'status' => true,
        'client' => ['id' => $client->id, 'name' => $client->name],
        'messages' => $thread,
    ]);
}

public function reply(Request $request)
{
    $request->validate([
        'client_id' => 'required|integer',
        'message_content' => 'required|string|max:5000',
        'subject' => 'nullable|string|max:255',
        'priority' => 'nullable|in:low,normal,high',
    ]);

    $homeId = explode(',', auth()->user()->home_id)[0];
    $messageService = app(PortalMessageService::class);
    $message = $messageService->sendStaffReply(
        (int)$homeId,
        auth()->user()->id,
        auth()->user()->name,
        (int)$request->client_id,
        $request->only(['message_content', 'subject', 'priority'])
    );

    return response()->json(['status' => true, 'message' => $message]);
}
```

### Step 5: Update Routes

```php
// Portal routes (inside existing portal group)
Route::get('/messages', [PortalDashboardController::class, 'messages'])->name('portal.messages');
Route::post('/messages/send', [PortalDashboardController::class, 'sendMessage'])->middleware('throttle:30,1')->name('portal.messages.send');
Route::post('/messages/read/{id}', [PortalDashboardController::class, 'markMessageRead'])->middleware('throttle:30,1')->name('portal.messages.read')->where('id', '[0-9]+');

// Admin routes (inside existing roster group)
Route::post('/messaging-center/thread', [MessagingCenterController::class, 'getThread'])->middleware('throttle:30,1');
Route::post('/messaging-center/reply', [MessagingCenterController::class, 'reply'])->middleware('throttle:30,1');
```

### Step 6: Create Portal Messages Blade View

`resources/views/frontEnd/portal/messages.blade.php`:

**Layout:**
- Extends `frontEnd.portal.layouts.master`
- Page title: "Messages"
- Subtitle: "Communicate with your care team"

**Permission denied state:**
- If `access_denied` is true, show "Access Denied" card (same pattern as schedule).

**Main view (single-page with AJAX):**
- **Stat cards row:** Total Messages (blue), Unread (green), Sent (purple)
- **"New Message" button** — toggles compose form panel
- **Compose form (hidden by default, shown via JS toggle):**
  - To: "Care Team" (disabled input)
  - Subject: text input
  - Category: dropdown (general, schedule, medication, care_plan, feedback, concern, request)
  - Priority: dropdown (low, normal, high)
  - Message: textarea (8 rows)
  - Cancel + Send buttons
  - Send via AJAX POST → on success, reload inbox
- **Message list:**
  - Each message row: avatar initial circle (blue for staff, purple for family), sender name + label ("Care Team" or "You"), subject, message preview (truncated), priority badge, category badge, date/time, "New" badge if unread staff message
  - Click row → expand to show full message content inline (accordion-style) + Reply button
  - When expanded, if sender_type=staff and is_read=false → AJAX mark-as-read
  - Unread staff messages have green-tinted background
- **Empty state:** "No messages yet" + "Send Your First Message" button

**GDPR on portal side:** Staff sender_name is already truncated to first name in the service layer.

### Step 7: Create/Populate Admin Messaging Center Blade

Populate `resources/views/frontEnd/roster/messaging/messaging_center.blade.php`:

**Layout:** Three-panel layout inside the existing roster master layout.

**Left panel — Client list sidebar:**
- Search input at top
- List of clients who have portal messages in this home
- Each row: avatar initial, client name, last message preview (truncated), unread count badge (blue), urgent count badge (red if high-priority unread)
- Click client → load their message thread via AJAX
- Selected client highlighted with blue left border
- Empty state: "No portal messages yet"

**Center panel — Message thread:**
- **No client selected:** "Select a client from the list to view messages"
- **Client selected — header:** client name, relationship label (from portal_access)
- **Chat bubbles:** staff messages right-aligned (blue), family messages left-aligned (white/grey)
  - Each bubble: sender label, priority badge, message body, timestamp, read status
  - Sorted oldest first (chat convention)
  - Auto-scroll to bottom on load
- **Reply input:** textarea + Send button at bottom
  - AJAX POST → on success, append new bubble, clear input
  - Enter to send (via JS)

**Right panel — Quick stats:**
- Unread messages count
- Today's messages count
- Priority breakdown (count per priority level)
- Total messages in home

### Step 8: Wire Admin Nav Link

In `roster_header.blade.php`, change the "Client Comms Hub" dead link (line 539) from `#!` to `{{ url('/roster/messaging-center') }}`.

### Step 9: Update Dashboard Stat Card

In `ClientPortalService::getDashboardData()`, replace hardcoded `'unread_messages' => 0` with real count from `PortalMessageService::getUnreadCount()`.

Remove the "Coming soon" text from the messages stat card in `dashboard.blade.php`.

### Step 10: Seed Test Messages

Seed 6-8 test messages for Katie (client 27) so there's data to display:

```php
$messages = [
    // Staff → family
    ['sender_type' => 'staff', 'sender_id' => 44, 'sender_name' => 'Allan Smith', 'recipient_type' => 'family', 'subject' => 'Welcome to the Portal', 'message_content' => 'Hello Jane, welcome to the Care One OS family portal. Feel free to message us any time with questions about Katie\'s care.', 'category' => 'general', 'priority' => 'normal', 'is_read' => 1, 'status' => 'read'],
    ['sender_type' => 'staff', 'sender_id' => 67, 'sender_name' => 'Robyn Piercy', 'recipient_type' => 'family', 'subject' => 'Katie\'s Schedule Update', 'message_content' => 'Hi Jane, just to let you know we\'ve updated Katie\'s schedule for next week. She\'ll have morning sessions on Monday and Wednesday.', 'category' => 'schedule', 'priority' => 'normal', 'is_read' => 0, 'status' => 'sent'],
    ['sender_type' => 'staff', 'sender_id' => 44, 'sender_name' => 'Allan Smith', 'recipient_type' => 'family', 'subject' => 'Medication Review', 'message_content' => 'Hi Jane, Katie\'s medication review is coming up on the 5th. Please let us know if you\'d like to be present.', 'category' => 'medication', 'priority' => 'high', 'is_read' => 0, 'status' => 'sent'],
    // Family → staff
    ['sender_type' => 'family', 'sender_id' => 1, 'sender_name' => 'Jane Smith', 'recipient_type' => 'all_staff', 'subject' => 'Thank you', 'message_content' => 'Thank you for the warm welcome. Katie has been settling in well and we really appreciate the care she\'s receiving.', 'category' => 'general', 'priority' => 'normal', 'is_read' => 1, 'status' => 'read'],
    ['sender_type' => 'family', 'sender_id' => 1, 'sender_name' => 'Jane Smith', 'recipient_type' => 'all_staff', 'subject' => 'Question about care plan', 'message_content' => 'Could someone update me on Katie\'s care plan review? I\'d like to understand the latest goals.', 'category' => 'care_plan', 'priority' => 'normal', 'is_read' => 0, 'status' => 'sent'],
    ['sender_type' => 'family', 'sender_id' => 1, 'sender_name' => 'Jane Smith', 'recipient_type' => 'all_staff', 'subject' => 'Visit this weekend', 'message_content' => 'Hi, I\'d like to visit Katie this Saturday afternoon around 2pm. Is that convenient?', 'category' => 'request', 'priority' => 'normal', 'is_read' => 0, 'status' => 'sent'],
];
// Each gets: home_id=8, client_id=27, is_deleted=0, created_at staggered over past 5 days
```

### Step 11: Write Tests

**Permission tests (2):**
- Portal user with `can_send_messages=true` → GET /portal/messages → 200, sees inbox
- Portal user with `can_send_messages=false` → GET /portal/messages → 200, sees "Access Denied"

**Send message tests (2):**
- Portal user sends message → POST /portal/messages/send → 200, message created in DB with correct client_id, home_id, sender_type='family'
- Portal user sends message with invalid category → 422

**Mark as read test (1):**
- Portal user marks staff message as read → POST /portal/messages/read/{id} → is_read=1 in DB

**Cross-client isolation tests (2):**
- Portal user A cannot read messages for client B → only messages for their linked client returned
- Portal user A cannot mark-as-read a message for client B → rejected

**IDOR via POST (1):**
- Portal user sends message with tampered client_id in request body → ignored, uses session client_id

**Admin thread loading (1):**
- Admin loads thread for client in their home → 200, messages returned
- Admin loads thread for client in different home → 404

**Admin reply (1):**
- Admin replies to client → message created with sender_type='staff', correct home_id

**GDPR (1):**
- Portal inbox shows staff first name only ("Allan" not "Allan Smith")

**Auth (1):**
- Unauthenticated → GET /portal/messages → 302 redirect

**Dashboard stat (1):**
- Portal dashboard shows correct unread messages count

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Cross-client IDOR     │ Portal: client_id always from session (portal_access.client_id), NEVER from  │
│                       │ request body on send. Admin: client_id validated against home before loading. │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Permission bypass     │ Controller checks can_send_messages BEFORE any query or mutation.             │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS in messages       │ {{ }} on all message content in Blade (both portal and admin views).          │
│                       │ esc() helper for AJAX-rendered content in JS.                                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ GDPR staff data       │ Staff sender_name truncated to first name in service layer for portal view.   │
│                       │ Admin sees full name (they're staff themselves).                               │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ All POST routes use CSRF token. AJAX uses $.ajaxSetup X-CSRF-TOKEN header.    │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:30,1 on send/reply, throttle:30,1 on mark-read.                     │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ subject: required|max:255. message_content: required|max:5000.                │
│                       │ category: in:list. priority: in:list. No raw SQL.                             │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ Portal: home_id from portal_access. Admin: home_id from auth user.            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ home_id, client_id, sender_type, sender_id set server-side, NOT from request. │
│                       │ Model uses $fillable whitelist.                                                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Admin impersonation   │ Admin routes require checkUserAuth middleware (existing).                      │
│                       │ Portal routes require CheckPortalAccess middleware (existing).                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Message read IDOR     │ markAsRead verifies message.client_id matches portal_access.client_id.        │
│                       │ markAsReadByStaff verifies message.home_id matches admin's home.              │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Single table, two directions.** `client_portal_messages` stores both family→staff and staff→family messages. `sender_type` distinguishes direction. Simpler than two tables, easier to render a thread.

2. **Reuse existing messaging-center page.** The admin UI goes into the already-scaffolded `messaging_center.blade.php` rather than creating a new page. The "Client Comms Hub" nav link (line 539) gets wired to this page.

3. **AJAX-heavy on admin side.** Client list loads on page render. Thread loads via AJAX when client is selected. Reply sends via AJAX and appends the bubble. This matches the chat-style UX from CareRoster.

4. **Page-reload on portal side.** Simpler approach — compose via AJAX (so the form doesn't lose state), but message list/detail via page sections. Could upgrade to full SPA-like later.

5. **GDPR: staff first name only on portal.** Service layer truncates `sender_name` to first name before returning data to portal. Admin sees full names since they're staff.

6. **No threading complexity.** `replied_to_id` exists for future use, but the current UI just shows a flat chronological list. CareRoster doesn't have deep threading either.

7. **Skip AI features.** CareRoster's AI categorization, suggested responses, and booking-from-chat are all Base44 LLM integrations. Way out of scope for a 4h feature. We keep the category field for manual selection.

## Test Verification (what user tests in browser)

After this feature is built, the user should be able to:

### Portal side (login as portal_test / 123456 / home Aries):
1. Dashboard stat card shows non-zero unread messages count (no "Coming soon" text)
2. Click "Messages" in sidebar → see inbox with stat cards (NOT "Coming Soon")
3. See seeded messages in the list: staff messages with "Care Team" label, own messages with "You" label
4. Unread staff messages have green-tinted background + "New" badge
5. Click a staff message → content expands, message auto-marked as read
6. Click "New Message" → compose form appears
7. Fill in subject, select category and priority, type message body → click Send
8. Message appears in inbox immediately
9. Staff names show first name only (GDPR) — "Allan" not "Allan Smith"

### Admin side (login as komal / 123456 / home Aries):
10. Navigate to Messaging Center (or Client Comms Hub link in sidebar)
11. See client list on the left — Katie should appear with unread badge
12. Click Katie → message thread loads in center panel showing all messages as chat bubbles
13. Staff messages (blue, right) and family messages (white, left) correctly styled
14. Type a reply in the input box → Send → new blue bubble appears
15. Stats panel on right shows correct counts

### Cross-role verification:
16. Portal user cannot access /roster/messaging-center (redirects)
17. Admin without portal access cannot see /portal/messages (redirects)
