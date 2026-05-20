# Phase 2 Feature 7 — Workflow Automation Engine (Trigger → Action)

━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 7 — Workflow Automation Engine.
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Migration, models, service, artisan command, controller, Blade view, JS, routes
[ ] BUILD — Workflow CRUD, trigger evaluation, action execution, execution logging, management UI
[ ] TEST — CRUD validation, home isolation, trigger evaluation, action dispatch, loop guard, execution log, toggle active
[ ] DEBUG — Login as admin, create/edit/delete workflows, run evaluator manually, check execution log
[ ] REVIEW — Adversarial curl attacks (IDOR, mass assignment home_id/trigger_config, XSS in workflow name, email injection in action config)
[ ] AUDIT — Phase 1+2 grep patterns + multi-tenancy check on all queries
[ ] PROD-READY — Admin user journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Context — Read These First

Before starting, read these files for full project context:
- `docs/logs.md` — action log with teaching notes from every prior session
- `CLAUDE.md` — project conventions, security rules, tech stack, git conventions
- `phases/phase2-feature6-scheduled-reports-prompt.md` — Feature 6 prompt (pattern reference for artisan command, scheduler registration, email sending)
- `app/Services/ScheduledReportService.php` — Feature 6's service layer (pattern for artisan command + scheduler integration)
- `app/Console/Commands/DispatchScheduledReports.php` — existing artisan command (pattern for our workflow evaluator command)
- `app/Console/Kernel.php` — scheduler registration (add our command here)
- `app/Mail/ScheduledReportMail.php` — existing mailable (pattern for workflow notification email)
- `app/Services/Staff/NotificationService.php` — existing notification service (we INSERT into the `notification` table for in-app notifications)
- `app/Models/Workflow_notification.php` — existing model (references `workflow_notifications` table which does NOT exist — this is legacy/unused code, do NOT use it)

**Admin user:** `komal` / `123456` / home Aries (Admin ID 194)

## Feature Classification

**Category: BUILD FOR REAL** — CareRoster has `AutomatedWorkflows.jsx` (UI with 8 hardcoded workflow templates, localStorage-based toggle, fake stats "Executed Today: 47") and `AutomatedWorkflowEngine.jsx` (5 utility functions for care plan sync, shift generation, leave sync, geocoding, training assignment — none of which are connected to the UI templates). The `WorkflowActionEditor.jsx` is part of the form builder, not the workflow system. CareRoster's "workflow engine" is entirely client-side — no backend execution, no scheduling, no logging.

We build a **real workflow automation engine** from scratch:
- Database-backed workflow definitions with configurable triggers and actions
- Artisan command that evaluates trigger conditions on schedule and executes actions
- Execution logging with status tracking
- Admin UI to create, configure, enable/disable, and monitor workflows
- Two action types that work today: **send_notification** (in-app via existing `notification` table) and **send_email** (via Laravel Mail, reusing Feature 6's mail infrastructure)

**CareRoster reference files (UI inspiration only):**
- `src/components/workflows/AutomatedWorkflows.jsx` — 8 template cards grouped by category (scheduling, hr, training, compliance, clinical, engagement, reporting), toggle switches, stats bar (Active/Executed Today/Emails Sent/Notifications), "Configure" expand with trigger condition + recipients + email template fields
- `src/components/workflow/AutomatedWorkflowEngine.jsx` — utility functions (not related to the UI, just background sync logic)
- `src/pages/WorkflowsPage.jsx` — thin wrapper around AutomatedWorkflows component

**What we skip (out of scope — deferred to Feature 8 Pre-built Workflows):**
- Pre-seeded workflow templates (Feature 8 handles this)
- Care plan sync, shift generation, leave sync, geocoding (CareRoster's `AutomatedWorkflowEngine.jsx` functions — not applicable to Care OS's architecture)
- `create_task` and `route_form` action types (from `WorkflowActionEditor.jsx` — no task/form system in Care OS)
- Complex multi-step workflows (onboarding steps from `OnboardingWorkflow` entity)
- Custom email templates with `{{variable}}` interpolation (keep it simple — use a standard email format)

## What Exists (infrastructure we build on)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ notification table                    │ EXISTS   │ 7,480 rows. Columns: id, home_id (varchar), user_id,         │
│                                       │          │ service_user_id, event_id, notification_event_type_id,        │
│                                       │          │ event_action, message, is_sticky, status, created_at.         │
│                                       │          │ We INSERT into this table for "send_notification" actions.     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ notification_event_type table         │ EXISTS   │ 24 types (id 1-24). id=10: "Incident Report",                 │
│                                       │          │ id=24: "SOS_ALERT". We add a new type for workflow events.     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ NotificationService                   │ EXISTS   │ app/Services/Staff/NotificationService.php — list(), markRead()│
│                                       │          │ markAllRead(), unreadCount(). Uses FIND_IN_SET for home_id.    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ NotificationController                │ EXISTS   │ app/Http/Controllers/frontEnd/Roster/NotificationController.php│
│                                       │          │ Handles listing/reading notifications at /roster/notifications. │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Mail config                           │ EXISTS   │ .env: MAIL_MAILER=smtp (switch to log for dev).                │
│                                       │          │ ScheduledReportMail exists as pattern for new mailable.        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Console/Kernel.php                    │ EXISTS   │ Already has `reports:dispatch` hourly. Add our command too.     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Console/Commands/ directory           │ EXISTS   │ Has DispatchScheduledReports.php. Add our evaluator here.       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Mail/ directory                       │ EXISTS   │ Has ScheduledReportMail.php. Add WorkflowNotificationMail.      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Sidebar nav                           │ DEAD     │ No "Workflows" or "Automation" link currently in sidebar.       │
│                                       │          │ Add one in the Compliance/Reports section of roster_header.     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Workflow_notification model           │ EXISTS   │ app/Models/Workflow_notification.php — references               │
│                                       │          │ `workflow_notifications` table which DOES NOT EXIST.            │
│                                       │          │ Legacy/unused. DO NOT use or modify.                            │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ automated_workflows table             │ MISSING  │ Need migration                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ workflow_execution_logs table         │ MISSING  │ Need migration                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AutomatedWorkflow model               │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ WorkflowExecutionLog model            │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ WorkflowEngineService                 │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ WorkflowController                    │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Workflow Blade view                   │ MISSING  │ Need to create                                                │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ checkUserAuth whitelist               │ EXISTS   │ Need to add workflow endpoints.                                │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Actually Building

A **Workflow Automation** page at `/roster/workflows` where admins can create custom automation rules. Each workflow has a **trigger** (what condition to check) and an **action** (what to do when the condition is met). An **artisan command** runs every 15 minutes, evaluates all active workflow triggers, and executes actions when conditions match. Every execution is logged.

### Trigger Types (what we check)

| Trigger Type | Description | Config Fields | How It's Evaluated |
|-------------|-------------|---------------|-------------------|
| `scheduled` | Run at a specific time | `frequency` (daily/weekly/monthly), `day` (0-6 or 1-28), `time` (HH:MM) | Same as Feature 6 — check if `next_run_date <= now` |
| `condition` | Check a data condition | `entity` (incidents/training/shifts/medication/feedback), `condition` (count_exceeds/days_since/status_is), `threshold` (number), `lookback_days` (number) | Query the entity table with condition, fire if threshold met |
| `event` | Fire when a specific record state exists | `entity`, `status` (e.g., "unfilled"), `min_count` (number) | Count records matching status, fire if >= min_count |

### Action Types (what we do)

| Action Type | Description | Config Fields | How It's Executed |
|-------------|-------------|---------------|-------------------|
| `send_notification` | Create in-app notification | `message` (text), `is_sticky` (boolean) | INSERT into `notification` table for all admin users in the home |
| `send_email` | Send email to specified recipients | `recipients` (comma-separated emails), `subject` (text), `message` (text) | Send via Laravel Mail to each recipient |

### Trigger Evaluation Logic

**`scheduled` triggers** — identical to Feature 6's scheduled reports:
- Has `next_run_date` on the workflow record
- Evaluator checks `next_run_date <= now AND is_active AND !is_deleted`
- After execution, advance `next_run_date` by one period

**`condition` triggers** — query-based checks:
```
Entity: incidents
Condition: count_exceeds
Threshold: 5
Lookback: 7 days
→ SELECT COUNT(*) FROM su_incident_report WHERE home_id = ? AND created_at >= (now - 7 days)
→ If count > 5, fire the action
```

**`event` triggers** — state-based checks:
```
Entity: shifts
Status: unfilled
Min count: 3
→ SELECT COUNT(*) FROM scheduled_shifts WHERE home_id = ? AND status = 'unfilled' AND date >= CURDATE()
→ If count >= 3, fire the action
```

**Cooldown mechanism:** After a condition/event trigger fires, set `last_triggered_at` and don't re-fire for `cooldown_hours` (default 24h). This prevents the same alert spamming every 15 minutes while the condition remains true.

### UI: Workflow Management Page

```
┌─────────────────────────────────────────────────────────────┐
│  ⚡ Workflow Automation                                      │
│  Configure automated notifications and alerts               │
│                                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│  │ Active   │ │ Total    │ │ Executed │ │ Failed   │       │
│  │    5     │ │    8     │ │  Today:3 │ │  Today:0 │       │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
│                                                [+ New Workflow]
│                                                             │
│  ┌── Scheduling ─────────────────────────────────────────┐  │
│  │ ⏰ Unfilled Shift Alert                  [Edit][⏸][🗑] │  │
│  │ Trigger: event · Shifts unfilled >= 3                  │  │
│  │ Action: send_notification · "There are unfilled shifts"│  │
│  │ Last run: 27 Apr 2026 14:00 · ✓ Success               │  │
│  ├────────────────────────────────────────────────────────┤  │
│  │ 📋 Daily Shift Summary              (dimmed) [Edit][▶][🗑]│
│  │ Trigger: scheduled · Daily at 18:00                    │  │
│  │ Action: send_email · 2 recipients                      │  │
│  │ ⏸ Paused                                               │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌── Compliance ─────────────────────────────────────────┐  │
│  │ 🔔 Incident Spike Alert                  [Edit][⏸][🗑] │  │
│  │ Trigger: condition · Incidents > 5 in last 7 days      │  │
│  │ Action: send_notification · Sticky alert               │  │
│  │ Last run: Never                                        │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌── Recent Executions ──────────────────────────────────┐  │
│  │ 14:00 │ Unfilled Shift Alert │ ✓ Success │ Notified   │  │
│  │ 08:00 │ Training Expiry Warn │ ✓ Success │ Email sent │  │
│  │ 08:00 │ Daily Summary        │ ✗ Failed  │ No data    │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌─────── Create/Edit Workflow Modal ──────────────────────┐│
│  │ Workflow Name:   [Unfilled Shift Alert               ]  ││
│  │ Category:        [Scheduling ▾]                         ││
│  │                                                         ││
│  │ ── Trigger ──────────────────────────────────           ││
│  │ Trigger Type:    [Event ▾]                              ││
│  │ Entity:          [Shifts ▾]                             ││
│  │ Status:          [unfilled ▾]                           ││
│  │ Min Count:       [3]                                    ││
│  │ Cooldown (hrs):  [24]                                   ││
│  │                                                         ││
│  │ ── Action ──────────────────────────────────            ││
│  │ Action Type:     [Send Notification ▾]                  ││
│  │ Message:         [There are unfilled shifts this week ] ││
│  │ Sticky:          [✓]                                    ││
│  │                                                         ││
│  │ Active:          [✓]                                    ││
│  │                                                         ││
│  │              [Cancel]  [Save Workflow]                   ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### Backend: Artisan Command + Evaluation Engine

```
┌──────────────┐     ┌──────────────────────┐     ┌───────────────────┐
│  Laravel      │────▶│  workflows:evaluate   │────▶│  WorkflowEngine   │
│  Scheduler    │     │  (artisan command)    │     │  Service           │
│  (every 15m)  │     │                      │     │                   │
└──────────────┘     │  1. Find active flows │     │  evaluateTrigger()│
                     │  2. Check cooldowns   │     │  executeAction()  │
                     │  3. Evaluate triggers  │     │  logExecution()   │
                     │  4. Execute actions   │     │                   │
                     │  5. Log results       │     └───────────────────┘
                     │  6. Update next_run   │
                     └──────────────────────┘
```

## Database: Two Tables

### `automated_workflows` Table

```
automated_workflows
├── id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
├── home_id             INT UNSIGNED NOT NULL          -- multi-tenancy
├── workflow_name       VARCHAR(255) NOT NULL          -- display name
├── category            VARCHAR(30) NOT NULL           -- scheduling|compliance|clinical|training|hr|engagement|reporting
├── trigger_type        VARCHAR(20) NOT NULL           -- scheduled|condition|event
├── trigger_config      JSON NOT NULL                  -- type-specific config (see below)
├── action_type         VARCHAR(30) NOT NULL           -- send_notification|send_email
├── action_config       JSON NOT NULL                  -- type-specific config (see below)
├── cooldown_hours      INT UNSIGNED NOT NULL DEFAULT 24 -- min hours between trigger fires
├── is_active           TINYINT(1) NOT NULL DEFAULT 1
├── next_run_date       DATETIME NULL                  -- for scheduled triggers only
├── last_triggered_at   DATETIME NULL                  -- last time this workflow fired
├── created_by          INT UNSIGNED NOT NULL           -- FK → user.id
├── is_deleted          TINYINT(1) NOT NULL DEFAULT 0  -- soft delete (project convention)
├── created_at          TIMESTAMP NULL
├── updated_at          TIMESTAMP NULL
│
├── INDEX idx_home_id (home_id)
├── INDEX idx_active_eval (is_active, is_deleted, trigger_type)
└── INDEX idx_created_by (created_by)
```

**trigger_config JSON examples:**
```json
// scheduled trigger:
{"frequency": "daily", "day": null, "time": "18:00"}

// condition trigger:
{"entity": "incidents", "condition": "count_exceeds", "threshold": 5, "lookback_days": 7}

// event trigger:
{"entity": "shifts", "status": "unfilled", "min_count": 3}
```

**action_config JSON examples:**
```json
// send_notification:
{"message": "There are unfilled shifts this week", "is_sticky": true}

// send_email:
{"recipients": "manager@care.com,admin@care.com", "subject": "Shift Alert", "message": "There are unfilled shifts that need attention."}
```

### `workflow_execution_logs` Table

```
workflow_execution_logs
├── id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
├── workflow_id         BIGINT UNSIGNED NOT NULL       -- FK → automated_workflows.id
├── home_id             INT UNSIGNED NOT NULL          -- denormalized for fast queries
├── trigger_type        VARCHAR(20) NOT NULL           -- denormalized
├── trigger_data        JSON NULL                      -- what the trigger found (e.g., {"count": 7})
├── action_type         VARCHAR(30) NOT NULL           -- denormalized
├── action_result       VARCHAR(20) NOT NULL           -- success|failed|skipped
├── error_message       TEXT NULL                      -- if failed
├── executed_at         DATETIME NOT NULL
│
├── INDEX idx_workflow (workflow_id)
├── INDEX idx_home_date (home_id, executed_at)
└── INDEX idx_executed (executed_at)
```

## Queryable Entities (for condition/event triggers)

Each entity maps to a Care OS table. The evaluator queries these to check conditions:

| Entity | Table | Key Columns | Available Conditions | Available Statuses |
|--------|-------|-------------|---------------------|-------------------|
| `incidents` | `su_incident_report` | `home_id`, `created_at` | `count_exceeds`, `days_since` | N/A (no status column) |
| `training` | `staff_training` JOIN `training` | `training.home_id`, `staff_training.created_at`, `staff_training.status` | `count_exceeds`, `status_is` | `0` (Pending), `1` (In Progress), `2` (Completed) |
| `shifts` | `scheduled_shifts` | `home_id` (VARCHAR), `date`, `status`, `deleted_at` | `count_exceeds`, `status_is` | `unfilled`, `assigned`, `completed`, `cancelled` |
| `medication` | `mar_administrations` JOIN `mar_sheets` | `mar_sheets.home_id`, `mar_administrations.created_at`, `mar_administrations.code` | `count_exceeds`, `status_is` | `A` (Administered), `R` (Refused), `S` (Spoilt) |
| `feedback` | `client_portal_feedback` | `home_id`, `created_at`, `status` | `count_exceeds`, `status_is` | `new`, `acknowledged`, `in_progress`, `resolved`, `closed` |

**IMPORTANT data quirks (from Feature 5 logs.md):**
- `staff_training` has NO `home_id` — must JOIN `training` table and filter via `training.home_id`
- `scheduled_shifts.home_id` is VARCHAR not INT — cast: `where('home_id', (string) $homeId)`
- `scheduled_shifts` uses `deleted_at` (Laravel SoftDeletes) not `is_deleted` — filter with `whereNull('deleted_at')`
- `mar_administrations` links to `mar_sheets` via `mar_sheet_id`, and `mar_sheets` has `home_id`

## Files to Create

1. `app/Models/AutomatedWorkflow.php` — model with `$fillable`, `$casts`, scopes
2. `app/Models/WorkflowExecutionLog.php` — model with `$fillable`, `$casts`
3. `app/Services/WorkflowEngineService.php` — CRUD + trigger evaluation + action execution + logging
4. `app/Http/Controllers/frontEnd/Roster/WorkflowController.php` — new controller
5. `app/Console/Commands/EvaluateWorkflows.php` — artisan command `workflows:evaluate`
6. `app/Mail/WorkflowNotificationMail.php` — mailable for email actions
7. `resources/views/frontEnd/roster/workflow/index.blade.php` — workflow management page
8. `resources/views/emails/workflow_notification.blade.php` — email body template
9. `public/js/roster/workflows.js` — workflow management JS
10. `tests/Feature/WorkflowEngineTest.php` — 15+ tests

## Files to Modify

1. `routes/web.php` — add workflow routes (GET index, GET list, POST store, POST update, POST toggle, POST delete, GET executions)
2. `app/Http/Middleware/checkUserAuth.php` — whitelist workflow endpoints
3. `app/Console/Kernel.php` — register `workflows:evaluate` command to run every 15 minutes
4. `resources/views/frontEnd/roster/common/roster_header.blade.php` — add "Workflow Automation" sidebar link

## Step-by-step Implementation

### Step 1: Create Migration (via tinker DB::statement)

Create both tables using the schemas above. Run via `DB::statement()` in tinker (artisan migrate has known issues with older migrations in this project).

Also insert a new `notification_event_type` row for workflow notifications:
```sql
INSERT INTO notification_event_type (id, name, table_linked) VALUES (25, 'Workflow Automation', 'automated_workflows');
```

### Step 2: Create AutomatedWorkflow Model

`app/Models/AutomatedWorkflow.php`:
- `$table = 'automated_workflows'`
- `$fillable` whitelist: workflow_name, category, trigger_type, trigger_config, action_type, action_config, cooldown_hours, is_active, next_run_date, last_triggered_at, home_id, created_by
- `$casts`: trigger_config → array, action_config → array, is_active → boolean, is_deleted → boolean, next_run_date → datetime, last_triggered_at → datetime
- Scopes: `scopeForHome($homeId)`, `scopeActive()`, `scopeNotDeleted()`
- Relationship: `createdBy()` → belongsTo User, `executionLogs()` → hasMany WorkflowExecutionLog

### Step 3: Create WorkflowExecutionLog Model

`app/Models/WorkflowExecutionLog.php`:
- `$table = 'workflow_execution_logs'`
- `$fillable`: workflow_id, home_id, trigger_type, trigger_data, action_type, action_result, error_message, executed_at
- `$casts`: trigger_data → array, executed_at → datetime
- `$timestamps = false` (no created_at/updated_at — only executed_at)
- Relationship: `workflow()` → belongsTo AutomatedWorkflow

### Step 4: Create WorkflowEngineService

`app/Services/WorkflowEngineService.php`:

```php
class WorkflowEngineService
{
    // === CRUD Methods ===

    public function listForHome(int $homeId): Collection
    {
        // Return all non-deleted workflows for this home, with latest execution log
        // ORDER BY category ASC, created_at DESC
    }

    public function store(array $data, int $homeId, int $userId): AutomatedWorkflow
    {
        // Validate trigger_config and action_config structure
        // If trigger_type = scheduled, calculate next_run_date
        // If action_type = send_email, validate recipient emails
        // Create record with home_id and created_by
        // Max 20 workflows per home (prevent abuse)
    }

    public function update(int $id, array $data, int $homeId): AutomatedWorkflow
    {
        // Find workflow by ID + home_id (IDOR protection)
        // Recalculate next_run_date if trigger config changed
        // Update record
    }

    public function toggleActive(int $id, int $homeId): AutomatedWorkflow
    {
        // Find by ID + home_id, flip is_active
        // If reactivating scheduled trigger, recalculate next_run_date
    }

    public function delete(int $id, int $homeId): void
    {
        // Soft delete: set is_deleted = 1 (find by ID + home_id)
    }

    public function getExecutionLogs(int $homeId, int $limit = 20): Collection
    {
        // Return recent execution logs for this home
        // JOIN automated_workflows for workflow_name
        // ORDER BY executed_at DESC, LIMIT $limit
    }

    // === Evaluation Engine ===

    public function evaluateAllWorkflows(): array
    {
        // Find all active, non-deleted workflows
        // Group by home_id for efficient querying
        // For each workflow:
        //   1. Check cooldown (skip if last_triggered_at + cooldown_hours > now)
        //   2. For scheduled: check next_run_date <= now
        //   3. For condition/event: evaluate trigger
        //   4. If triggered: execute action, log result, update timestamps
        // Return array of results [{workflow_id, workflow_name, home_id, status, error?}]
    }

    public function evaluateTrigger(AutomatedWorkflow $workflow): array
    {
        // Returns ['triggered' => bool, 'data' => [...]]
        // Dispatch to type-specific evaluator
    }

    private function evaluateScheduledTrigger(AutomatedWorkflow $workflow): array
    {
        // Check if next_run_date <= now
        // Return ['triggered' => true/false, 'data' => ['scheduled_for' => next_run_date]]
    }

    private function evaluateConditionTrigger(AutomatedWorkflow $workflow): array
    {
        // Parse trigger_config: entity, condition, threshold, lookback_days
        // Query the entity table with home_id scope
        // Evaluate condition:
        //   count_exceeds: COUNT(*) > threshold
        //   days_since: check if any record is older than threshold days without resolution
        //   status_is: COUNT(*) WHERE status = value > 0
        // Return ['triggered' => bool, 'data' => ['count' => N, 'condition' => '...']]
    }

    private function evaluateEventTrigger(AutomatedWorkflow $workflow): array
    {
        // Parse trigger_config: entity, status, min_count
        // Count records matching entity + status + home_id (current/future records only)
        // Return ['triggered' => count >= min_count, 'data' => ['count' => N]]
    }

    // === Entity Query Methods (one per entity type) ===

    private function queryEntity(string $entity, int $homeId, array $config): int
    {
        // Switch on entity type, return count matching the condition
        // Each uses Eloquent query builder, scoped by home_id
        // Handles the data quirks (training JOIN, shifts VARCHAR home_id, etc.)
    }

    // === Action Execution ===

    public function executeAction(AutomatedWorkflow $workflow, array $triggerData): array
    {
        // Dispatch to type-specific executor
        // Returns ['status' => 'success'|'failed', 'error' => null|string]
    }

    private function executeSendNotification(AutomatedWorkflow $workflow, array $triggerData): array
    {
        // Parse action_config: message, is_sticky
        // INSERT into notification table:
        //   home_id = workflow->home_id
        //   notification_event_type_id = 25 (Workflow Automation)
        //   event_action = 'WORKFLOW'
        //   message = action_config.message
        //   is_sticky = action_config.is_sticky ? 1 : 0
        //   status = 0 (unread)
        //   created_at = now()
        // Return ['status' => 'success']
    }

    private function executeSendEmail(AutomatedWorkflow $workflow, array $triggerData): array
    {
        // Parse action_config: recipients, subject, message
        // Split recipients by comma, validate each email
        // Send WorkflowNotificationMail to each recipient
        // Return ['status' => 'success'] or ['status' => 'failed', 'error' => '...']
    }

    // === Logging ===

    private function logExecution(AutomatedWorkflow $workflow, string $triggerType, ?array $triggerData, string $actionType, string $result, ?string $error = null): void
    {
        // INSERT into workflow_execution_logs
    }

    // === Scheduling Helpers ===

    public function calculateNextRunDate(string $frequency, ?int $day, string $time): Carbon
    {
        // Same logic as ScheduledReportService::calculateNextRunDate()
        // daily: next occurrence at $time
        // weekly: next $day-of-week at $time
        // monthly: next $day-of-month at $time
    }

    public function advanceNextRunDate(AutomatedWorkflow $workflow): Carbon
    {
        // daily → +1 day, weekly → +7 days, monthly → +1 month
    }
}
```

### Step 5: Create Artisan Command

`app/Console/Commands/EvaluateWorkflows.php`:

```php
class EvaluateWorkflows extends Command
{
    protected $signature = 'workflows:evaluate';
    protected $description = 'Evaluate all active workflow triggers and execute actions';

    public function handle(WorkflowEngineService $service): int
    {
        $results = $service->evaluateAllWorkflows();
        $triggered = collect($results)->where('status', 'success')->count();
        $failed = collect($results)->where('status', 'failed')->count();
        $skipped = collect($results)->where('status', 'skipped')->count();

        $this->info("Workflows evaluated: " . count($results));
        $this->info("  Triggered: {$triggered}, Failed: {$failed}, Skipped: {$skipped}");

        foreach ($results as $r) {
            $icon = $r['status'] === 'success' ? '✓' : ($r['status'] === 'failed' ? '✗' : '○');
            $this->line("  {$icon} #{$r['workflow_id']} {$r['workflow_name']}: {$r['status']}");
        }

        return 0;
    }
}
```

### Step 6: Create WorkflowNotificationMail

`app/Mail/WorkflowNotificationMail.php`:
- Constructor receives: workflow name, subject, message body
- `build()`: from(), subject(), view('emails.workflow_notification')
- Simple, no CSV attachment (unlike Feature 6's report mail)

**Email template** (`resources/views/emails/workflow_notification.blade.php`):
- Simple HTML: workflow name as heading, message body, "Generated by Care OS Workflow Automation" footer
- No user data interpolation (prevents template injection)

### Step 7: Register in Kernel.php

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:dispatch')->hourly();
    $schedule->command('workflows:evaluate')->everyFifteenMinutes();
}
```

### Step 8: Create WorkflowController

`app/Http/Controllers/frontEnd/Roster/WorkflowController.php`:

```php
class WorkflowController extends Controller
{
    public function index()
    {
        // Return workflow/index.blade.php
        // Admin-only (roster routes are admin-only by convention)
    }

    public function list(Request $request)
    {
        // Return JSON list of workflows for admin's home
        // Include last execution data
    }

    public function store(Request $request)
    {
        // Validate: workflow_name required|max:255, category in:list,
        //   trigger_type in:scheduled,condition,event,
        //   trigger_config required (JSON string, decode + validate structure),
        //   action_type in:send_notification,send_email,
        //   action_config required (JSON string, decode + validate structure),
        //   cooldown_hours required|integer|min:1|max:168
        // Call service->store()
        // Return JSON
    }

    public function update(Request $request)
    {
        // Same validation as store + 'id' required|integer
        // Call service->update()
    }

    public function toggle(Request $request)
    {
        // Validate 'id' required|integer
        // Call service->toggleActive()
    }

    public function delete(Request $request)
    {
        // Validate 'id' required|integer
        // Call service->delete()
    }

    public function executions(Request $request)
    {
        // Return JSON list of recent execution logs for admin's home
    }
}
```

### Step 9: Add Routes

```php
// Inside roster prefix group:
Route::get('/workflows', [WorkflowController::class, 'index']);
Route::get('/workflows/list', [WorkflowController::class, 'list'])->middleware('throttle:30,1');
Route::post('/workflows/store', [WorkflowController::class, 'store'])->middleware('throttle:30,1');
Route::post('/workflows/update', [WorkflowController::class, 'update'])->middleware('throttle:30,1');
Route::post('/workflows/toggle', [WorkflowController::class, 'toggle'])->middleware('throttle:30,1');
Route::post('/workflows/delete', [WorkflowController::class, 'delete'])->middleware('throttle:20,1');
Route::get('/workflows/executions', [WorkflowController::class, 'executions'])->middleware('throttle:30,1');
```

All routes need `->where('id', '[0-9]+')` where applicable (POST routes use request body, not URL params, so route constraints only apply to GET with params).

### Step 10: Whitelist in Middleware

```php
// In checkUserAuth.php:
array_push($allowed_path,
    'roster/workflows',
    'roster/workflows/list',
    'roster/workflows/store',
    'roster/workflows/update',
    'roster/workflows/toggle',
    'roster/workflows/delete',
    'roster/workflows/executions'
);
```

### Step 11: Create Workflow Blade View

`resources/views/frontEnd/roster/workflow/index.blade.php`:

**IMPORTANT:** Read an existing roster page (e.g., `report.blade.php` or `safeguarding.blade.php`) and copy its exact layout pattern: `@extends('frontEnd.roster.common.master')`, `@section('content')`, inline CSS/JS inside the content section (admin master has NO `@yield('scripts')` or `@yield('styles')`).

Page sections:
1. **Header**: "Workflow Automation" title + subtitle
2. **Stat cards row**: Active Workflows, Total Workflows, Executed Today, Failed Today (from execution logs)
3. **Workflow list**: Cards grouped by category, each showing:
   - Workflow name, trigger type badge, action type badge
   - Trigger summary (e.g., "Shifts unfilled >= 3")
   - Action summary (e.g., "Send notification: There are unfilled shifts")
   - Last run date + status
   - Edit / Toggle / Delete buttons
   - Inactive workflows shown dimmed with "Paused" label
4. **Recent Executions panel**: Table of last 10 executions with timestamp, workflow name, result, details
5. **Create/Edit Modal** (Bootstrap 3 modal):
   - Workflow Name (text input, required)
   - Category (select: scheduling, compliance, clinical, training, hr, engagement, reporting)
   - Trigger Type (select: Scheduled, Condition, Event)
   - **Dynamic trigger config fields** (show/hide based on trigger type):
     - Scheduled: Frequency, Day, Time (same as Feature 6)
     - Condition: Entity, Condition type, Threshold, Lookback days
     - Event: Entity, Status, Min count
   - Action Type (select: Send Notification, Send Email)
   - **Dynamic action config fields** (show/hide based on action type):
     - Send Notification: Message (textarea), Sticky (checkbox)
     - Send Email: Recipients (text, comma-separated), Subject (text), Message (textarea)
   - Cooldown Hours (number input, default 24)
   - Active (checkbox, default checked)
   - Save / Cancel buttons
6. **Empty state**: "No workflows configured. Create one to automate notifications and alerts."

### Step 12: Create workflows.js

`public/js/roster/workflows.js`:

- `loadWorkflows()` — AJAX GET `/roster/workflows/list` → render grouped cards
- `loadExecutions()` — AJAX GET `/roster/workflows/executions` → render execution table
- Workflow card rendering with `esc()` for all data
- Modal open/close for create/edit
- **Dynamic form fields**: Show/hide trigger config + action config sections based on selected type
- Form submit → AJAX POST to store or update (serialize trigger_config and action_config as JSON strings)
- Toggle active → AJAX POST to toggle
- Delete → confirm → AJAX POST to delete
- Category grouping in render (group workflows by category, show category header)
- Stat card update from loaded data
- `esc()` helper function (same as reports.js — escape all dynamic content before `.html()`)

### Step 13: Wire Sidebar Link

In `roster_header.blade.php`, add "Workflow Automation" link near the Compliance Hub section:
```html
<li> <a href="{{ url('/roster/workflows') }}"><i class='bx bx-zap'></i> <span>Workflow Automation</span> </a></li>
```

Place it after "Reporting Engine" (line 535) and before "Audit Templates" (line 536).

### Step 14: Write Tests

**Test file:** `tests/Feature/WorkflowEngineTest.php`

```
1.  Workflow page loads (GET /roster/workflows → 200, contains "Workflow Automation")
2.  Workflow list returns empty array for home with no workflows
3.  Create workflow → 200, workflow appears in list
4.  Create workflow validates required fields (missing workflow_name → 422)
5.  Create workflow validates trigger_type in allowed list (invalid → 422)
6.  Create workflow validates action_type in allowed list (invalid → 422)
7.  Update workflow → 200, changes persisted
8.  Toggle workflow active → flips is_active flag
9.  Delete workflow → soft deletes (is_deleted = 1), no longer in list
10. Home isolation: admin only sees own home's workflows
11. IDOR: admin cannot update/delete workflow from another home → 404
12. Execution logs return for home only
13. Artisan command evaluates condition trigger (seed workflow + data, run command, check execution log)
14. Artisan command skips inactive workflows
15. Artisan command respects cooldown (set last_triggered_at to 1 hour ago, cooldown 24h → skip)
16. Artisan command executes send_notification action (check notification table)
17. Max workflows per home enforced (create 20, try 21st → rejected)
18. Unauthenticated → 302 redirect on all endpoints
```

## Loop Guard & Safety

To prevent infinite loops or spam:

1. **Cooldown per workflow**: Default 24 hours. After a condition/event trigger fires, it won't fire again until cooldown expires. Scheduled triggers use `next_run_date` advancement instead.

2. **Max executions per hour per home**: Cap at 50. If a home has already had 50 workflow executions in the last hour, skip remaining evaluations. Log a warning.

3. **Max 20 workflows per home**: Prevent abuse in the create endpoint.

4. **Max 5 recipients per email action**: Same as Feature 6.

5. **Error isolation**: If one workflow fails, log the error and continue evaluating the rest. Never let one bad workflow break the entire evaluation run.

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ ALL workflow queries filter by home_id from auth session.                     │
│                       │ home_id set on create from session, NEVER from request.                       │
│                       │ Artisan command evaluates each workflow with its own home_id scope.            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ IDOR                  │ Update/toggle/delete verify workflow.home_id matches admin's home_id.          │
│                       │ Return 404 if mismatch (not 403, to avoid leaking existence).                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ $fillable whitelist on model. home_id and created_by set in service layer.    │
│                       │ trigger_config and action_config validated for structure in service.            │
│                       │ Client can NOT set home_id, created_by, last_triggered_at, or next_run_date.  │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ JSON injection        │ trigger_config and action_config decoded, validated for expected keys,          │
│                       │ then re-encoded. No arbitrary JSON stored.                                     │
│                       │ Entity names validated against whitelist (incidents|training|shifts|            │
│                       │ medication|feedback). Condition names validated against whitelist.               │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Email injection       │ Recipients validated with filter_var(FILTER_VALIDATE_EMAIL).                  │
│                       │ No \r\n allowed in email addresses.                                            │
│                       │ Max 5 recipients per workflow.                                                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ SQL injection         │ Entity queries use Eloquent builder only. Entity names are whitelisted,         │
│                       │ not interpolated into SQL. No DB::raw() with user input.                       │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS                   │ {{ }} in Blade, esc() in JS for workflow names/messages in card rendering.    │
│                       │ Notification messages stored as plain text, escaped on display.                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ @csrf on modal form, $.ajaxSetup with X-CSRF-TOKEN on AJAX.                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:30,1 on create/update/list, throttle:20,1 on delete.                 │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Denial of service     │ Max 20 workflows per home. Max 50 executions/hour/home.                       │
│                       │ Cooldown prevents condition/event triggers from firing every 15 min.            │
│                       │ Error isolation — one failing workflow doesn't break others.                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ workflow_name: required|max:255. category: required|in:list.                  │
│                       │ trigger_type: required|in:scheduled,condition,event.                           │
│                       │ action_type: required|in:send_notification,send_email.                         │
│                       │ cooldown_hours: required|integer|min:1|max:168.                                │
│                       │ trigger_config/action_config: required JSON, validated per type.               │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Access control        │ All routes behind checkUserAuth. Admin-only (roster routes).                  │
│                       │ Portal users cannot access /roster/workflows.                                  │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Separate page, not a tab on an existing page.** Unlike Feature 6 (added tab to reports), workflow automation is its own domain. A dedicated `/roster/workflows` page with its own controller keeps things clean and the page won't be overloaded.

2. **Two action types only (notification + email).** CareRoster's `WorkflowActionEditor.jsx` also has `create_task` and `route_form` — but Care OS has no task system or form builder. We build what we can execute today. Feature 8 (Pre-built Workflows) can add more action types later.

3. **JSON config fields, not separate columns.** Trigger and action configs vary by type. JSON columns with validated structure are more flexible than adding 15 nullable columns. The service layer validates the JSON structure before storing.

4. **Cooldown instead of complex scheduling for condition/event triggers.** A condition like "unfilled shifts > 3" could be true for days. Without cooldown, it would fire every 15 minutes. The cooldown (default 24h) means it fires once, then waits. Admins can configure the cooldown per workflow.

5. **Every 15 minutes, not every minute.** The workflow evaluator runs `everyFifteenMinutes()`. This is frequent enough for operational alerts but doesn't hammer the database. Scheduled triggers with specific times are accurate to the 15-minute window.

6. **Denormalized execution logs.** `workflow_execution_logs` copies home_id, trigger_type, action_type from the workflow record. This means logs are self-contained — even if the workflow is deleted, the execution history survives and can be queried by home.

7. **Notification via existing table.** We INSERT into the existing `notification` table (7,480 existing rows) rather than creating a parallel notification system. This means workflow notifications show up in the existing notification bell/page that admins already use.

8. **Max 20 workflows per home.** Prevents abuse and keeps evaluation time bounded.

## Test Verification (what user tests in browser)

### Admin side (login as komal / 123456 / home Aries):
1. Navigate via sidebar "Workflow Automation" → page loads with empty state
2. Click "+ New Workflow" → modal opens with all form fields
3. Create "Unfilled Shift Alert": Event trigger (entity=shifts, status=unfilled, min_count=3), Send Notification ("There are unfilled shifts"), cooldown 24h
4. Save → modal closes, workflow card appears in "Scheduling" category section
5. Create "Daily Summary Email": Scheduled trigger (daily, 18:00), Send Email (your email, "Daily Summary", "Here is your daily shift summary")
6. Verify workflows grouped by category in the list
7. Click edit (pencil) → modal opens pre-filled with workflow data
8. Change trigger type → dynamic config fields update
9. Click pause (toggle) → workflow dims, shows "Paused"
10. Click play (toggle) → workflow reactivates
11. Click delete (trash) → confirm → workflow removed from list
12. Stat cards show correct counts (Active, Total, etc.)
13. Run `php artisan workflows:evaluate` in terminal → check execution log section on page
14. Check `storage/logs/laravel.log` for email (MAIL_MAILER=log) or notification table for in-app notification
15. Run the command again within cooldown period → verify workflow is skipped
