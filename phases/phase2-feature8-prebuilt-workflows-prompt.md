# Phase 2 Feature 8 — Pre-built Workflow Templates (Incident → Notify Manager)

━━━━━━━━━━━━━━━━━━━━━━
Run `/careos-workflow-phase2` and follow all 9 stages.
WORKFLOW: Phase 2 Feature 8 — Pre-built Workflow Templates.
[ ] PLAN — Pre-built below, present to user for approval
[ ] SCAFFOLD — Seeder, service method, controller endpoint, Blade template gallery, JS
[ ] BUILD — Template gallery UI, one-click install, template definitions, seeder command
[ ] TEST — Template install, duplicate prevention, installed template editing, home isolation, seeder idempotency
[ ] DEBUG — Login as admin, browse gallery, install templates, verify they appear in workflow list, run evaluator
[ ] REVIEW — Adversarial curl attacks (IDOR, installing templates across homes, mass assignment, template_id injection)
[ ] AUDIT — Phase 1+2 grep patterns + multi-tenancy check on all queries
[ ] PROD-READY — Admin user journey, manual checklist
[ ] PUSH — Commit and push
━━━━━━━━━━━━━━━━━━━━━━

## Context — Read These First

Before starting, read these files for full project context:
- `docs/logs.md` — action log with teaching notes from every prior session
- `CLAUDE.md` — project conventions, security rules, tech stack, git conventions
- `phases/phase2-feature7-workflow-engine-prompt.md` — Feature 7 prompt (the engine we extend)
- `app/Services/WorkflowEngineService.php` — existing workflow CRUD + evaluation engine (~700 lines)
- `app/Http/Controllers/frontEnd/Roster/WorkflowController.php` — existing 7 endpoints
- `app/Models/AutomatedWorkflow.php` — model with $fillable, $casts, scopes
- `resources/views/frontEnd/roster/workflow/index.blade.php` — existing workflow page (add template gallery here)
- `public/js/roster/workflows.js` — existing workflow JS (add template gallery functions here)

**Admin user:** `komal` / `123456` / home Aries (Admin ID 194)

## Feature Classification

**Category: BUILD FOR REAL** — CareRoster's `AutomatedWorkflows.jsx` has 8 hardcoded `WORKFLOW_TEMPLATES` (shift_reminder_24h, unfilled_shift_alert, leave_approval_reminder, training_expiry_warning, incident_follow_up, medication_missed_alert, client_birthday_reminder, daily_summary_email). These are localStorage-toggled cards with no backend — toggling ON does nothing, "Test" shows a fake success toast after 2 seconds. No template is ever written to the database.

We build **real pre-built workflow templates** that:
- Present a template gallery on the workflow page where admins can browse and one-click install
- Each template creates a real `automated_workflows` DB record with sensible defaults
- Installed templates are fully editable (they become regular workflows)
- Templates are NOT hardcoded in the frontend — they're defined server-side and served via API
- An artisan seeder can pre-populate templates for new homes

**CareRoster reference files (UI inspiration only):**
- `src/components/workflows/AutomatedWorkflows.jsx` — 8 template objects with id, name, description, trigger type, action type, category, icon, default_enabled flag. Grouped by category with toggle switches and expandable "Configure" sections.

## What Exists (infrastructure from Feature 7)

┌───────────────────────────────────────┬──────────┬──────────────────────────────────────────────────────────────┐
│ Component                             │ Status   │ Notes                                                        │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ automated_workflows table             │ EXISTS   │ Full schema: id, home_id, workflow_name, category,            │
│                                       │          │ trigger_type, trigger_config (JSON), action_type,             │
│                                       │          │ action_config (JSON), cooldown_hours, is_active,              │
│                                       │          │ next_run_date, last_triggered_at, created_by, is_deleted,     │
│                                       │          │ created_at, updated_at.                                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ workflow_execution_logs table         │ EXISTS   │ Logs every trigger evaluation + action result.                 │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ AutomatedWorkflow model               │ EXISTS   │ $fillable whitelist, JSON casts, scopes (forHome, active,     │
│                                       │          │ notDeleted). Relationship: createdBy(), executionLogs().       │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ WorkflowEngineService                 │ EXISTS   │ CRUD (store, update, toggle, delete, listForHome, getStats),   │
│                                       │          │ evaluation engine (evaluateAllWorkflows, evaluateTrigger),     │
│                                       │          │ 5 entity query methods (incidents, training, shifts,           │
│                                       │          │ medication, feedback), 2 action executors (notification,       │
│                                       │          │ email), validation, scheduling. ~700 lines.                   │
│                                       │          │ MAX_WORKFLOWS_PER_HOME = 20.                                  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ WorkflowController                    │ EXISTS   │ 7 endpoints: index, list, store, update, toggle, delete,       │
│                                       │          │ executions. All scoped by homeId() from auth session.          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Workflow Blade view                   │ EXISTS   │ Stats bar, "+ New Workflow" button, grouped card list,          │
│                                       │          │ execution log table, create/edit modal with dynamic fields.    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ workflows.js                          │ EXISTS   │ CRUD, card rendering, modal, trigger/action field switching,   │
│                                       │          │ execution log, esc() helper. ~390 lines.                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Routes                                │ EXISTS   │ 7 routes under /roster/workflows/ with throttle middleware.    │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ checkUserAuth whitelist               │ EXISTS   │ All 7 workflow endpoints whitelisted.                          │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Console/Kernel.php                    │ EXISTS   │ workflows:evaluate runs every 15 minutes.                      │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ notification table                    │ EXISTS   │ event_type_id 25 = "Workflow Automation" (inserted in F7).     │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Template definitions                  │ MISSING  │ Need to define server-side template registry.                  │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Template gallery UI                   │ MISSING  │ Need to add to existing workflow page.                         │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Template install endpoint             │ MISSING  │ Need controller + route for one-click install.                 │
├───────────────────────────────────────┼──────────┼──────────────────────────────────────────────────────────────┤
│ Seeder command                        │ MISSING  │ Need artisan command for bulk pre-population.                  │
└───────────────────────────────────────┴──────────┴──────────────────────────────────────────────────────────────┘

## What We're Actually Building

A **template gallery** section on the existing `/roster/workflows` page. Admins browse pre-built workflow templates grouped by category, see a description of what each does, and click "Install" to create a real workflow from the template with sensible defaults. Installed templates become regular workflows — fully editable, togglable, deletable.

### The 8 Pre-built Templates

These match CareRoster's 8 templates but with **real, working** trigger and action configs:

| # | Template ID | Name | Category | Trigger | Action | Default Active |
|---|------------|------|----------|---------|--------|---------------|
| 1 | `incident_notify_manager` | Incident → Notify Manager | compliance | **event**: incidents created today >= 1 | **send_notification**: "New incident reported — please review and take action" (sticky) | Yes |
| 2 | `unfilled_shift_alert` | Unfilled Shift Alert | scheduling | **event**: shifts with status "unfilled" >= 3 | **send_notification**: "There are unfilled shifts that need attention" | Yes |
| 3 | `training_expiry_warning` | Training Expiry Warning | training | **condition**: training status_is "0" (pending) count > 5, lookback 30 days | **send_notification**: "Staff have pending training records that need attention" | Yes |
| 4 | `medication_missed_alert` | Missed Medication Alert | clinical | **event**: medication with code "R" (refused) >= 1 today | **send_notification**: "Medication has been refused or missed — please review" (sticky) | Yes |
| 5 | `incident_spike_alert` | Incident Spike Alert | compliance | **condition**: incidents count_exceeds 5 in last 7 days | **send_email**: subject "Incident Spike Alert", message "There have been more than 5 incidents in the past 7 days. Please review." | Yes |
| 6 | `feedback_new_alert` | New Feedback Alert | engagement | **event**: feedback with status "new" >= 1 | **send_notification**: "New client/family feedback received — please review and respond" | No |
| 7 | `daily_summary_email` | Daily Summary Email | reporting | **scheduled**: daily at 18:00 | **send_email**: subject "Daily Operations Summary", message "Here is your daily operations summary for today." | No |
| 8 | `weekly_shift_report` | Weekly Shift Report | scheduling | **scheduled**: weekly on Monday (day=1) at 08:00 | **send_email**: subject "Weekly Shift Report", message "Here is your weekly shift report." | No |

**Template definition structure:**
```php
[
    'template_id' => 'incident_notify_manager',
    'workflow_name' => 'Incident → Notify Manager',
    'description' => 'Automatically notify managers whenever a new incident is reported. Ensures timely review and response.',
    'category' => 'compliance',
    'trigger_type' => 'event',
    'trigger_config' => ['entity' => 'incidents', 'status' => 'new', 'min_count' => 1],
    'action_type' => 'send_notification',
    'action_config' => ['message' => 'New incident reported — please review and take action', 'is_sticky' => true],
    'cooldown_hours' => 24,
    'default_active' => true,
    'icon' => 'bx-error-circle',
]
```

### How Installation Works

1. Admin clicks "Install" on a template card
2. Frontend POSTs to `/roster/workflows/install-template` with `{ template_id: "incident_notify_manager" }`
3. Controller calls `WorkflowEngineService::installTemplate($templateId, $homeId, $userId)`
4. Service:
   - Looks up template_id in the template registry (hardcoded PHP array — NOT from DB)
   - Checks if this home already has a workflow with `template_id` in a metadata field (prevents duplicate installs)
   - Creates a new `automated_workflows` record with the template's defaults
   - For email templates, the recipients field is left empty — admin must configure before it can send
   - Sets `is_active` based on template's `default_active` AND whether the action is ready (email with no recipients → inactive)
   - Returns the created workflow
5. Admin sees the template card switch to "Installed ✓" and the workflow appears in the main list
6. Admin can click Edit on the installed workflow to customise trigger thresholds, action messages, recipients, etc.

### Schema Change: Add `template_id` Column

Add a nullable `template_id` column to `automated_workflows` to track which workflows were installed from templates:

```sql
ALTER TABLE automated_workflows ADD COLUMN template_id VARCHAR(50) NULL AFTER workflow_name;
CREATE INDEX idx_template ON automated_workflows (template_id);
```

This column:
- Is NULL for manually created workflows
- Contains the template's string ID (e.g., `incident_notify_manager`) for template-installed workflows
- Used to check "is this template already installed for this home?" (prevent duplicates)
- Is NOT in `$fillable` — set only by the service layer, never from user input

### UI: Template Gallery

Add a collapsible "Template Gallery" section **above** the existing workflow list, between the toolbar and the `#wf-list` div:

```
┌─────────────────────────────────────────────────────────────┐
│  ⚡ Workflow Automation                                      │
│  Configure automated notifications and alerts               │
│                                                             │
│  [Stats bar — unchanged]                                    │
│                                                             │
│  [+ New Workflow]                                           │
│                                                             │
│  ┌── 📋 Template Gallery ──────────── [Hide Gallery ▲] ──┐  │
│  │ Browse pre-built workflows — click Install to add     │  │
│  │                                                       │  │
│  │ ┌── Compliance ──────────────────────────────────┐    │  │
│  │ │ ⚠ Incident → Notify Manager        [Installed ✓]│   │  │
│  │ │ Notify managers when a new incident is reported │   │  │
│  │ │                                                 │   │  │
│  │ │ 🔔 Incident Spike Alert              [Install]  │   │  │
│  │ │ Alert when incidents exceed 5 in 7 days         │   │  │
│  │ └─────────────────────────────────────────────────┘   │  │
│  │                                                       │  │
│  │ ┌── Scheduling ──────────────────────────────────┐    │  │
│  │ │ 📅 Unfilled Shift Alert              [Install]  │   │  │
│  │ │ Alert when 3+ shifts are unfilled               │   │  │
│  │ │                                                 │   │  │
│  │ │ 📊 Weekly Shift Report               [Install]  │   │  │
│  │ │ Weekly Monday report of shift coverage           │   │  │
│  │ └─────────────────────────────────────────────────┘   │  │
│  │                                                       │  │
│  │ ┌── Clinical ────────────────────────────────────┐    │  │
│  │ │ 💊 Missed Medication Alert           [Install]  │   │  │
│  │ │ Alert when medication is refused or missed      │   │  │
│  │ └─────────────────────────────────────────────────┘   │  │
│  │                                                       │  │
│  │ [... more categories ...]                             │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌── Your Workflows ─────────────────────────────────────┐  │
│  │ [existing grouped workflow cards — unchanged]          │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                             │
│  [Recent Executions — unchanged]                            │
└─────────────────────────────────────────────────────────────┘
```

**Template card design:**
- Icon (Boxicons class from template definition) + workflow name + install state badge
- One-line description below the name
- Trigger type badge + action type badge (same styling as existing workflow cards)
- "Install" button (blue) or "Installed ✓" badge (green, non-clickable) based on whether the home already has this template
- Cards grouped by category with category headers (same style as existing workflow list)
- Gallery is collapsible — default expanded on first visit, remembers state in localStorage

**After installation:**
- Template card updates to "Installed ✓" without page reload
- The installed workflow appears in the main workflow list below
- For email action templates: a toast/alert tells the admin "Workflow installed — configure email recipients to activate"

## Files to Create

1. `app/Services/WorkflowTemplateRegistry.php` — static class with template definitions and lookup methods

## Files to Modify

1. `app/Services/WorkflowEngineService.php` — add `installTemplate()`, `getTemplates()`, `getInstalledTemplateIds()` methods
2. `app/Http/Controllers/frontEnd/Roster/WorkflowController.php` — add `templates()` and `installTemplate()` endpoints
3. `app/Models/AutomatedWorkflow.php` — add `template_id` to `$casts` (not $fillable — set by service only)
4. `routes/web.php` — add 2 routes: GET templates, POST install-template
5. `app/Http/Middleware/checkUserAuth.php` — whitelist 2 new endpoints
6. `resources/views/frontEnd/roster/workflow/index.blade.php` — add template gallery HTML section
7. `public/js/roster/workflows.js` — add template gallery rendering, install function, gallery toggle
8. `tests/Feature/WorkflowEngineTest.php` — add template-specific tests (or create separate test file)

## Step-by-step Implementation

### Step 1: Add `template_id` Column

Run via `DB::statement()` in tinker:
```sql
ALTER TABLE automated_workflows ADD COLUMN template_id VARCHAR(50) NULL AFTER workflow_name;
CREATE INDEX idx_template ON automated_workflows (template_id);
```

### Step 2: Create WorkflowTemplateRegistry

`app/Services/WorkflowTemplateRegistry.php`:

```php
class WorkflowTemplateRegistry
{
    private const TEMPLATES = [
        [
            'template_id' => 'incident_notify_manager',
            'workflow_name' => 'Incident → Notify Manager',
            'description' => 'Automatically notify managers whenever a new incident is reported. Ensures timely review and response.',
            'category' => 'compliance',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'incidents', 'status' => 'new', 'min_count' => 1],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'New incident reported — please review and take action', 'is_sticky' => true],
            'cooldown_hours' => 24,
            'default_active' => true,
            'icon' => 'bx-error-circle',
        ],
        [
            'template_id' => 'unfilled_shift_alert',
            'workflow_name' => 'Unfilled Shift Alert',
            'description' => 'Alert managers when shifts remain unfilled. Helps ensure adequate staffing coverage.',
            'category' => 'scheduling',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'shifts', 'status' => 'unfilled', 'min_count' => 3],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'There are unfilled shifts that need attention', 'is_sticky' => false],
            'cooldown_hours' => 24,
            'default_active' => true,
            'icon' => 'bx-calendar-exclamation',
        ],
        [
            'template_id' => 'training_expiry_warning',
            'workflow_name' => 'Training Expiry Warning',
            'description' => 'Alert when staff have pending training records that need completion or renewal.',
            'category' => 'training',
            'trigger_type' => 'condition',
            'trigger_config' => ['entity' => 'training', 'condition' => 'status_is', 'threshold' => 5, 'lookback_days' => 30, 'status' => '0'],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'Staff have pending training records that need attention', 'is_sticky' => false],
            'cooldown_hours' => 48,
            'default_active' => true,
            'icon' => 'bx-certification',
        ],
        [
            'template_id' => 'medication_missed_alert',
            'workflow_name' => 'Missed Medication Alert',
            'description' => 'Immediate alert when medication is refused or missed. Critical for clinical safety.',
            'category' => 'clinical',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'medication', 'status' => 'R', 'min_count' => 1],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'Medication has been refused or missed — please review', 'is_sticky' => true],
            'cooldown_hours' => 12,
            'default_active' => true,
            'icon' => 'bx-capsule',
        ],
        [
            'template_id' => 'incident_spike_alert',
            'workflow_name' => 'Incident Spike Alert',
            'description' => 'Email alert when incidents exceed a threshold within a time period. Flags potential systemic issues.',
            'category' => 'compliance',
            'trigger_type' => 'condition',
            'trigger_config' => ['entity' => 'incidents', 'condition' => 'count_exceeds', 'threshold' => 5, 'lookback_days' => 7],
            'action_type' => 'send_email',
            'action_config' => ['recipients' => '', 'subject' => 'Incident Spike Alert', 'message' => 'There have been more than 5 incidents in the past 7 days. Please review the incident log and investigate any patterns.'],
            'cooldown_hours' => 48,
            'default_active' => true,
            'icon' => 'bx-line-chart-down',
        ],
        [
            'template_id' => 'feedback_new_alert',
            'workflow_name' => 'New Feedback Alert',
            'description' => 'Notify staff when new client or family feedback is received. Supports timely responses.',
            'category' => 'engagement',
            'trigger_type' => 'event',
            'trigger_config' => ['entity' => 'feedback', 'status' => 'new', 'min_count' => 1],
            'action_type' => 'send_notification',
            'action_config' => ['message' => 'New client/family feedback received — please review and respond', 'is_sticky' => false],
            'cooldown_hours' => 24,
            'default_active' => false,
            'icon' => 'bx-message-dots',
        ],
        [
            'template_id' => 'daily_summary_email',
            'workflow_name' => 'Daily Summary Email',
            'description' => 'Send a daily operations summary email to managers at 6pm. Keeps leadership informed.',
            'category' => 'reporting',
            'trigger_type' => 'scheduled',
            'trigger_config' => ['frequency' => 'daily', 'day' => null, 'time' => '18:00'],
            'action_type' => 'send_email',
            'action_config' => ['recipients' => '', 'subject' => 'Daily Operations Summary', 'message' => 'Here is your daily operations summary. Please review any outstanding items requiring attention.'],
            'cooldown_hours' => 24,
            'default_active' => false,
            'icon' => 'bx-envelope',
        ],
        [
            'template_id' => 'weekly_shift_report',
            'workflow_name' => 'Weekly Shift Report',
            'description' => 'Send a weekly shift coverage report every Monday morning. Helps with scheduling planning.',
            'category' => 'scheduling',
            'trigger_type' => 'scheduled',
            'trigger_config' => ['frequency' => 'weekly', 'day' => 1, 'time' => '08:00'],
            'action_type' => 'send_email',
            'action_config' => ['recipients' => '', 'subject' => 'Weekly Shift Report', 'message' => 'Here is your weekly shift coverage report. Please review staffing levels for the coming week.'],
            'cooldown_hours' => 24,
            'default_active' => false,
            'icon' => 'bx-bar-chart-alt-2',
        ],
    ];

    public static function all(): array
    {
        return self::TEMPLATES;
    }

    public static function find(string $templateId): ?array
    {
        foreach (self::TEMPLATES as $template) {
            if ($template['template_id'] === $templateId) {
                return $template;
            }
        }
        return null;
    }

    public static function validIds(): array
    {
        return array_column(self::TEMPLATES, 'template_id');
    }
}
```

### Step 3: Add Methods to WorkflowEngineService

Add to `app/Services/WorkflowEngineService.php`:

```php
use App\Services\WorkflowTemplateRegistry;

public function getTemplates(int $homeId): array
{
    $templates = WorkflowTemplateRegistry::all();
    $installedIds = $this->getInstalledTemplateIds($homeId);

    foreach ($templates as &$template) {
        $template['installed'] = in_array($template['template_id'], $installedIds);
    }

    return $templates;
}

public function getInstalledTemplateIds(int $homeId): array
{
    return AutomatedWorkflow::forHome($homeId)
        ->notDeleted()
        ->whereNotNull('template_id')
        ->pluck('template_id')
        ->toArray();
}

public function installTemplate(string $templateId, int $homeId, int $userId): AutomatedWorkflow
{
    $template = WorkflowTemplateRegistry::find($templateId);
    if (!$template) {
        throw new \RuntimeException('Unknown template: ' . $templateId);
    }

    // Check if already installed
    $existing = AutomatedWorkflow::forHome($homeId)
        ->notDeleted()
        ->where('template_id', $templateId)
        ->exists();

    if ($existing) {
        throw new \RuntimeException('This template is already installed.');
    }

    // Check max workflows
    $count = AutomatedWorkflow::forHome($homeId)->notDeleted()->count();
    if ($count >= self::MAX_WORKFLOWS_PER_HOME) {
        throw new \RuntimeException('Maximum of ' . self::MAX_WORKFLOWS_PER_HOME . ' workflows per home.');
    }

    // Determine if the workflow should be active
    // Email templates with empty recipients start inactive regardless of default_active
    $isActive = $template['default_active'];
    if ($template['action_type'] === 'send_email' && empty($template['action_config']['recipients'])) {
        $isActive = false;
    }

    $workflow = new AutomatedWorkflow();
    $workflow->workflow_name = $template['workflow_name'];
    $workflow->template_id = $template['template_id'];
    $workflow->category = $template['category'];
    $workflow->trigger_type = $template['trigger_type'];
    $workflow->trigger_config = $template['trigger_config'];
    $workflow->action_type = $template['action_type'];
    $workflow->action_config = $template['action_config'];
    $workflow->cooldown_hours = $template['cooldown_hours'];
    $workflow->is_active = $isActive;
    $workflow->home_id = $homeId;
    $workflow->created_by = $userId;

    if ($template['trigger_type'] === 'scheduled') {
        $config = $template['trigger_config'];
        $workflow->next_run_date = $this->calculateNextRunDate(
            $config['frequency'],
            isset($config['day']) ? (int) $config['day'] : null,
            $config['time']
        );
    }

    $workflow->save();

    Log::info("Workflow template installed: '{$template['template_id']}' as #{$workflow->id} for home {$homeId}");

    return $workflow;
}
```

### Step 4: Add Controller Endpoints

Add to `app/Http/Controllers/frontEnd/Roster/WorkflowController.php`:

```php
public function templates(WorkflowEngineService $service)
{
    $templates = $service->getTemplates($this->homeId());
    return response()->json(['status' => true, 'templates' => $templates]);
}

public function installTemplate(Request $request, WorkflowEngineService $service)
{
    $request->validate([
        'template_id' => 'required|string|max:50',
    ]);

    try {
        $workflow = $service->installTemplate($request->template_id, $this->homeId(), Auth::user()->id);

        $needsConfig = $workflow->action_type === 'send_email' && empty($workflow->action_config['recipients']);

        return response()->json([
            'status' => true,
            'workflow' => $workflow,
            'needs_config' => $needsConfig,
        ]);
    } catch (\RuntimeException $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
    }
}
```

### Step 5: Add Routes

```php
// Inside roster prefix group, after existing workflow routes:
Route::get('/workflows/templates', [WorkflowController::class, 'templates'])->middleware('throttle:30,1');
Route::post('/workflows/install-template', [WorkflowController::class, 'installTemplate'])->middleware('throttle:30,1');
```

### Step 6: Whitelist in Middleware

```php
// In checkUserAuth.php, add to the workflow array_push:
'roster/workflows/templates',
'roster/workflows/install-template'
```

### Step 7: Update AutomatedWorkflow Model

The `template_id` column should NOT be in `$fillable` (it's set directly in the service, never from user input). No model change needed if using direct property assignment. But do verify that mass assignment via `fill()` on update/store doesn't overwrite template_id — it won't, since it's not in $fillable.

### Step 8: Add Template Gallery to Blade View

In `resources/views/frontEnd/roster/workflow/index.blade.php`, add between the toolbar div and the `#wf-list` div:

```html
<!-- Template Gallery -->
<div id="template-gallery-section">
    <div class="tpl-header" onclick="toggleGallery()">
        <span class="tpl-header-title">📋 Template Gallery</span>
        <span class="tpl-header-sub">Browse pre-built workflows — click Install to add</span>
        <span class="tpl-toggle" id="tpl-toggle-btn">▲ Hide</span>
    </div>
    <div id="tpl-gallery" class="tpl-gallery">
        <div class="wf-loading">Loading templates...</div>
    </div>
</div>
```

Add CSS for the template gallery section:

```css
/* Template Gallery */
.tpl-header {
    display: flex; align-items: center; gap: 15px; cursor: pointer;
    padding: 14px 20px; background: #f0f7ff; border-radius: 10px 10px 0 0;
    border: 1px solid #d0e3f7; margin-bottom: 0;
}
.tpl-header-title { font-size: 16px; font-weight: 600; color: #2c3e50; }
.tpl-header-sub { font-size: 13px; color: #777; flex: 1; }
.tpl-toggle { font-size: 12px; color: #3498db; font-weight: 600; }
.tpl-gallery {
    background: #f8fbff; border: 1px solid #d0e3f7; border-top: none;
    border-radius: 0 0 10px 10px; padding: 20px; margin-bottom: 25px;
}
.tpl-gallery.collapsed { display: none; }

.tpl-cat-header {
    font-size: 13px; font-weight: 700; color: #777; text-transform: uppercase;
    letter-spacing: 0.5px; margin: 15px 0 8px; padding-bottom: 4px;
    border-bottom: 1px solid #e0e8f0;
}
.tpl-cat-header:first-child { margin-top: 0; }

.tpl-card {
    display: flex; align-items: center; gap: 14px;
    background: #fff; border-radius: 8px; padding: 14px 18px; margin-bottom: 8px;
    border: 1px solid #e9ecef;
}
.tpl-card-icon {
    width: 38px; height: 38px; border-radius: 8px; background: #eef2ff;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: #3498db; flex-shrink: 0;
}
.tpl-card-body { flex: 1; }
.tpl-card-name { font-size: 14px; font-weight: 600; color: #2c3e50; margin-bottom: 2px; }
.tpl-card-desc { font-size: 12px; color: #888; margin-bottom: 4px; }
.tpl-card-badges { display: flex; gap: 4px; }

.btn-install {
    background: #3498db; color: #fff; border: none; border-radius: 6px;
    padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer;
    white-space: nowrap;
}
.btn-install:hover { background: #2980b9; }
.btn-installed {
    background: #27ae60; color: #fff; border: none; border-radius: 6px;
    padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: default;
    white-space: nowrap;
}
```

### Step 9: Add Template Gallery JS

Add to `public/js/roster/workflows.js`:

```javascript
// Add to $(document).ready:
loadTemplates();
initGalleryToggle();

function loadTemplates() {
    $.get(baseUrl + '/roster/workflows/templates', function (res) {
        if (!res.status) return;
        renderTemplates(res.templates || []);
    });
}

function renderTemplates(templates) {
    if (!templates.length) {
        $('#tpl-gallery').html('<div class="wf-empty" style="padding:20px;">No templates available.</div>');
        return;
    }

    var grouped = {};
    var catOrder = ['compliance', 'scheduling', 'clinical', 'training', 'hr', 'engagement', 'reporting'];
    for (var i = 0; i < templates.length; i++) {
        var t = templates[i];
        var cat = t.category || 'other';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(t);
    }

    var html = '';
    for (var ci = 0; ci < catOrder.length; ci++) {
        var cat = catOrder[ci];
        if (!grouped[cat]) continue;
        html += '<div class="tpl-cat-header">' + esc(cat) + '</div>';
        for (var j = 0; j < grouped[cat].length; j++) {
            html += renderTemplateCard(grouped[cat][j]);
        }
    }

    $('#tpl-gallery').html(html);
}

function renderTemplateCard(t) {
    var triggerBadge = '<span class="wf-badge wf-badge-' + esc(t.trigger_type) + '">' + esc(t.trigger_type) + '</span>';
    var actionBadge = t.action_type === 'send_notification'
        ? '<span class="wf-badge wf-badge-notification">notification</span>'
        : '<span class="wf-badge wf-badge-email">email</span>';

    var installBtn = t.installed
        ? '<span class="btn-installed">Installed ✓</span>'
        : '<button class="btn-install" onclick="installTemplate(\'' + esc(t.template_id) + '\')">Install</button>';

    return '<div class="tpl-card" id="tpl-' + esc(t.template_id) + '">' +
        '<div class="tpl-card-icon"><i class="bx ' + esc(t.icon || 'bx-zap') + '"></i></div>' +
        '<div class="tpl-card-body">' +
            '<div class="tpl-card-name">' + esc(t.workflow_name) + '</div>' +
            '<div class="tpl-card-desc">' + esc(t.description) + '</div>' +
            '<div class="tpl-card-badges">' + triggerBadge + ' ' + actionBadge + '</div>' +
        '</div>' +
        installBtn +
    '</div>';
}

function installTemplate(templateId) {
    $.post(baseUrl + '/roster/workflows/install-template', { template_id: templateId }, function (res) {
        if (res.status) {
            // Update the install button to "Installed ✓"
            var card = $('#tpl-' + templateId);
            card.find('.btn-install').replaceWith('<span class="btn-installed">Installed ✓</span>');

            // Reload the workflow list to show the new workflow
            loadWorkflows();

            if (res.needs_config) {
                alert('Workflow installed! Please edit it to add email recipients before it can send.');
            }
        } else {
            alert(res.message || 'Failed to install template.');
        }
    }).fail(function (xhr) {
        var msg = 'Failed to install template.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        alert(msg);
    });
}

function toggleGallery() {
    var gallery = $('#tpl-gallery');
    var btn = $('#tpl-toggle-btn');
    if (gallery.hasClass('collapsed')) {
        gallery.removeClass('collapsed');
        btn.text('▲ Hide');
        localStorage.setItem('wf_gallery_collapsed', '0');
    } else {
        gallery.addClass('collapsed');
        btn.text('▼ Show');
        localStorage.setItem('wf_gallery_collapsed', '1');
    }
}

function initGalleryToggle() {
    if (localStorage.getItem('wf_gallery_collapsed') === '1') {
        $('#tpl-gallery').addClass('collapsed');
        $('#tpl-toggle-btn').text('▼ Show');
    }
}
```

### Step 10: Create Seeder Command (optional but useful)

`app/Console/Commands/SeedWorkflowTemplates.php`:

```php
class SeedWorkflowTemplates extends Command
{
    protected $signature = 'workflows:seed-templates {home_id} {user_id}';
    protected $description = 'Install all default-active workflow templates for a home';

    public function handle(WorkflowEngineService $service): int
    {
        $homeId = (int) $this->argument('home_id');
        $userId = (int) $this->argument('user_id');

        $templates = WorkflowTemplateRegistry::all();
        $installed = 0;
        $skipped = 0;

        foreach ($templates as $template) {
            if (!$template['default_active']) {
                $this->line("  ○ {$template['template_id']}: skipped — not default active");
                $skipped++;
                continue;
            }

            try {
                $service->installTemplate($template['template_id'], $homeId, $userId);
                $this->info("  ✓ {$template['template_id']}: installed");
                $installed++;
            } catch (\RuntimeException $e) {
                $this->warn("  ○ {$template['template_id']}: skipped — {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->info("Done. Installed: {$installed}, Skipped: {$skipped}");
        return 0;
    }
}
```

### Step 11: Write Tests

**Test file:** `tests/Feature/WorkflowTemplateTest.php` (separate file to keep it clean)

```
1.  Templates endpoint returns all 8 templates with installed status
2.  Templates endpoint marks installed templates correctly
3.  Install template creates workflow with correct defaults
4.  Install template sets template_id on the workflow record
5.  Install template prevents duplicate installation (same template_id + home_id)
6.  Install template respects max workflows per home limit
7.  Install template sets email workflows inactive when recipients empty
8.  Install template calculates next_run_date for scheduled templates
9.  Install template rejects invalid template_id
10. Installed template workflow is fully editable (update endpoint works)
11. Installed template workflow is deletable (delete endpoint works)
12. Home isolation: templates installed status is per-home
13. IDOR: cannot install template claiming a different home (home_id comes from session)
14. Seeder command installs default-active templates
15. Seeder command is idempotent (running twice doesn't create duplicates)
16. Unauthenticated → 302 redirect on template endpoints
```

## Security Checklist

┌───────────────────────┬──────────────────────────────────────────────────────────────────────────────┐
│ Attack Surface        │ Protection                                                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Template injection    │ Template IDs are validated against a hardcoded whitelist                       │
│                       │ (WorkflowTemplateRegistry::validIds()). No arbitrary template_id               │
│                       │ values are accepted. Template definitions are server-side PHP                  │
│                       │ constants, not user-modifiable.                                               │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Multi-tenancy         │ install-template uses homeId() from auth session, not request.                │
│                       │ Templates installed status is per-home (getInstalledTemplateIds).              │
│                       │ Same IDOR protection as Feature 7 on all CRUD operations.                     │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Mass assignment       │ template_id is NOT in $fillable — set directly on model in service.           │
│                       │ Client cannot set template_id, home_id, or created_by via request.            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Duplicate abuse       │ Duplicate install check prevents creating multiple workflows                  │
│                       │ from the same template per home.                                              │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ XSS                   │ Template names/descriptions rendered via esc() in JS.                         │
│                       │ Template data is server-defined, not user input, but still escaped.            │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ CSRF                  │ POST install-template uses existing $.ajaxSetup X-CSRF-TOKEN.                │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Rate limiting         │ throttle:30,1 on both new endpoints.                                         │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Input validation      │ template_id: required|string|max:50. Validated against registry whitelist.     │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ DoS prevention        │ Max 20 workflows per home (existing limit). Max 8 templates.                  │
│                       │ Duplicate check prevents repeated installs.                                   │
├───────────────────────┼──────────────────────────────────────────────────────────────────────────────┤
│ Access control        │ All routes behind checkUserAuth. Admin-only (roster routes).                  │
└───────────────────────┴──────────────────────────────────────────────────────────────────────────────┘

## Key Design Decisions

1. **Templates as a PHP constant, not a DB table.** Templates are defined once by developers, not created by users. A PHP array is simpler, has no migration, and can't be tampered with. If we later want admin-defined templates, we'd add a `workflow_templates` table — but that's out of scope.

2. **`template_id` column on `automated_workflows`, not a separate junction table.** A simple nullable string column is enough to track "was this installed from a template?" and prevent duplicate installs. No need for a many-to-many relationship.

3. **Email templates install as inactive.** Templates with `send_email` action and empty recipients are forced inactive on install. This prevents a template from immediately trying to send email to nobody. The admin must edit the workflow, add recipients, then activate it.

4. **Installed templates are regular workflows.** Once installed, a template becomes a normal workflow record. The admin can edit every field — name, trigger, action, cooldown. The `template_id` column is only used for duplicate-install prevention, not to lock the workflow to the template's original config.

5. **Gallery is collapsible with localStorage memory.** After initial exploration, admins will mostly work with their installed workflows. The gallery collapses to get out of the way, and remembers the collapsed state across page loads.

6. **Seeder command for new home onboarding.** `php artisan workflows:seed-templates {home_id} {user_id}` installs all default-active templates for a home. Useful when setting up a new care home. Idempotent — running twice won't create duplicates.

7. **Separate registry class.** `WorkflowTemplateRegistry` is a clean, single-responsibility class. It could be expanded later with template versioning, feature-gating, etc. Keeps the service layer focused on CRUD + evaluation.

## Test Verification (what user tests in browser)

### Admin side (login as komal / 123456 / home Aries):

1. Navigate to `/roster/workflows` → template gallery appears above the workflow list
2. Gallery shows 8 templates grouped by category (Compliance, Scheduling, Clinical, Training, Engagement, Reporting)
3. Each template card shows icon, name, description, trigger/action badges, and "Install" button
4. Click "Install" on "Incident → Notify Manager" → button changes to "Installed ✓"
5. The workflow appears in the main list below under "Compliance" category
6. Click "Install" on "Incident → Notify Manager" again → error "This template is already installed"
7. Click "Install" on "Daily Summary Email" → installs as **inactive** (because no email recipients)
8. Toast/alert says "configure email recipients to activate"
9. Edit the Daily Summary Email workflow → add recipients → save → toggle active
10. Click gallery header → gallery collapses. Refresh page → gallery stays collapsed.
11. Click gallery header again → gallery expands
12. "Unfilled Shift Alert" template → Install → verify trigger config is correct (shifts, unfilled, >=3)
13. Edit the installed "Unfilled Shift Alert" → change min_count to 1 → save → run `php artisan workflows:evaluate` → verify it triggers (there are unfilled shifts in the system)
14. Delete an installed template workflow → the template card in gallery should go back to "Install" button (on next page load / gallery reload)
