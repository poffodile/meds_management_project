<?php

namespace App\Services;

use App\Models\AutomatedWorkflow;
use App\Models\WorkflowExecutionLog;
use App\Mail\WorkflowNotificationMail;
use App\Services\WorkflowTemplateRegistry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WorkflowEngineService
{
    private const MAX_WORKFLOWS_PER_HOME = 20;
    private const MAX_EXECUTIONS_PER_HOUR = 50;
    private const MAX_EMAIL_RECIPIENTS = 5;

    private const VALID_ENTITIES = ['incidents', 'training', 'shifts', 'medication', 'feedback'];
    private const VALID_CONDITIONS = ['count_exceeds', 'days_since', 'status_is'];
    private const VALID_CATEGORIES = ['scheduling', 'compliance', 'clinical', 'training', 'hr', 'engagement', 'reporting'];

    // ==================== CRUD ====================

    public function listForHome(int $homeId): Collection
    {
        $workflows = AutomatedWorkflow::forHome($homeId)
            ->notDeleted()
            ->orderBy('category', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $workflowIds = $workflows->pluck('id')->toArray();

        $lastLogs = [];
        if (!empty($workflowIds)) {
            $logs = WorkflowExecutionLog::whereIn('workflow_id', $workflowIds)
                ->orderBy('executed_at', 'desc')
                ->get()
                ->groupBy('workflow_id');

            foreach ($logs as $wfId => $wfLogs) {
                $lastLogs[$wfId] = $wfLogs->first();
            }
        }

        foreach ($workflows as $wf) {
            $wf->last_execution = $lastLogs[$wf->id] ?? null;
        }

        return $workflows;
    }

    public function store(array $data, int $homeId, int $userId): AutomatedWorkflow
    {
        $count = AutomatedWorkflow::forHome($homeId)->notDeleted()->count();
        if ($count >= self::MAX_WORKFLOWS_PER_HOME) {
            throw new \RuntimeException('Maximum of ' . self::MAX_WORKFLOWS_PER_HOME . ' workflows per home.');
        }

        $this->validateTriggerConfig($data['trigger_type'], $data['trigger_config']);
        $this->validateActionConfig($data['action_type'], $data['action_config']);

        $workflow = new AutomatedWorkflow();
        $workflow->fill($data);
        $workflow->home_id = $homeId;
        $workflow->created_by = $userId;

        if ($data['trigger_type'] === 'scheduled') {
            $config = $data['trigger_config'];
            $workflow->next_run_date = $this->calculateNextRunDate(
                $config['frequency'],
                isset($config['day']) ? (int) $config['day'] : null,
                $config['time']
            );
        }

        $workflow->save();

        Log::info("Workflow created: #{$workflow->id} '{$workflow->workflow_name}' for home {$homeId}");

        return $workflow;
    }

    public function update(int $id, array $data, int $homeId): AutomatedWorkflow
    {
        $workflow = AutomatedWorkflow::forHome($homeId)->notDeleted()->where('id', $id)->firstOrFail();

        $this->validateTriggerConfig($data['trigger_type'], $data['trigger_config']);
        $this->validateActionConfig($data['action_type'], $data['action_config']);

        $workflow->fill($data);

        if ($data['trigger_type'] === 'scheduled') {
            $config = $data['trigger_config'];
            $workflow->next_run_date = $this->calculateNextRunDate(
                $config['frequency'],
                isset($config['day']) ? (int) $config['day'] : null,
                $config['time']
            );
        } else {
            $workflow->next_run_date = null;
        }

        $workflow->save();

        Log::info("Workflow updated: #{$workflow->id} '{$workflow->workflow_name}'");

        return $workflow;
    }

    public function toggleActive(int $id, int $homeId): AutomatedWorkflow
    {
        $workflow = AutomatedWorkflow::forHome($homeId)->notDeleted()->where('id', $id)->firstOrFail();
        $workflow->is_active = !$workflow->is_active;

        if ($workflow->is_active && $workflow->trigger_type === 'scheduled') {
            $config = $workflow->trigger_config;
            $workflow->next_run_date = $this->calculateNextRunDate(
                $config['frequency'],
                isset($config['day']) ? (int) $config['day'] : null,
                $config['time']
            );
        }

        $workflow->save();

        Log::info("Workflow toggled: #{$workflow->id} is_active=" . ($workflow->is_active ? '1' : '0'));

        return $workflow;
    }

    public function delete(int $id, int $homeId): void
    {
        $workflow = AutomatedWorkflow::forHome($homeId)->notDeleted()->where('id', $id)->firstOrFail();
        $workflow->is_deleted = 1;
        $workflow->save();

        Log::info("Workflow soft-deleted: #{$workflow->id} '{$workflow->workflow_name}'");
    }

    public function getExecutionLogs(int $homeId, int $limit = 20): Collection
    {
        return WorkflowExecutionLog::where('workflow_execution_logs.home_id', $homeId)
            ->leftJoin('automated_workflows', 'workflow_execution_logs.workflow_id', '=', 'automated_workflows.id')
            ->select(
                'workflow_execution_logs.*',
                'automated_workflows.workflow_name'
            )
            ->orderBy('workflow_execution_logs.executed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getStats(int $homeId): array
    {
        $total = AutomatedWorkflow::forHome($homeId)->notDeleted()->count();
        $active = AutomatedWorkflow::forHome($homeId)->notDeleted()->active()->count();

        $todayStart = Carbon::today()->toDateTimeString();
        $executedToday = WorkflowExecutionLog::where('home_id', $homeId)
            ->where('executed_at', '>=', $todayStart)
            ->where('action_result', 'success')
            ->count();
        $failedToday = WorkflowExecutionLog::where('home_id', $homeId)
            ->where('executed_at', '>=', $todayStart)
            ->where('action_result', 'failed')
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'executed_today' => $executedToday,
            'failed_today' => $failedToday,
        ];
    }

    // ==================== TEMPLATES ====================

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
            throw new \RuntimeException('Unknown template.');
        }

        $existing = AutomatedWorkflow::forHome($homeId)
            ->notDeleted()
            ->where('template_id', $templateId)
            ->exists();

        if ($existing) {
            throw new \RuntimeException('This template is already installed.');
        }

        $count = AutomatedWorkflow::forHome($homeId)->notDeleted()->count();
        if ($count >= self::MAX_WORKFLOWS_PER_HOME) {
            throw new \RuntimeException('Maximum of ' . self::MAX_WORKFLOWS_PER_HOME . ' workflows per home.');
        }

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

    // ==================== MANUAL RUN ====================

    public function runSingleForHome(int $id, int $homeId): array
    {
        $workflow = AutomatedWorkflow::forHome($homeId)->notDeleted()->where('id', $id)->firstOrFail();

        $triggerResult = $this->evaluateTrigger($workflow);
        $actionResult = $this->executeAction($workflow, $triggerResult['data'] ?? []);

        $this->logExecution(
            $workflow,
            'manual',
            array_merge($triggerResult['data'] ?? [], ['trigger_matched' => $triggerResult['triggered']]),
            $workflow->action_type,
            $actionResult['status'],
            $actionResult['error'] ?? null
        );

        $workflow->last_triggered_at = Carbon::now();
        $workflow->save();

        return [
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->workflow_name,
            'status' => $actionResult['status'],
            'trigger_matched' => $triggerResult['triggered'],
            'error' => $actionResult['error'] ?? null,
        ];
    }

    public function evaluateAllForHome(int $homeId): array
    {
        $workflows = AutomatedWorkflow::forHome($homeId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get();

        $results = [];
        foreach ($workflows as $workflow) {
            try {
                $result = $this->evaluateSingleWorkflow($workflow);
                $results[] = $result;
            } catch (\Throwable $e) {
                Log::error("Workflow evaluation failed: #{$workflow->id} — {$e->getMessage()}");
                $this->logExecution($workflow, $workflow->trigger_type, null, $workflow->action_type, 'failed', $e->getMessage());
                $results[] = [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->workflow_name,
                    'home_id' => $homeId,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    // ==================== EVALUATION ENGINE ====================

    public function evaluateAllWorkflows(): array
    {
        $workflows = AutomatedWorkflow::where('is_active', 1)
            ->where('is_deleted', 0)
            ->get();

        $results = [];
        $homeExecutionCounts = [];

        foreach ($workflows as $workflow) {
            $hid = $workflow->home_id;

            if (!isset($homeExecutionCounts[$hid])) {
                $homeExecutionCounts[$hid] = WorkflowExecutionLog::where('home_id', $hid)
                    ->where('executed_at', '>=', Carbon::now()->subHour())
                    ->count();
            }

            if ($homeExecutionCounts[$hid] >= self::MAX_EXECUTIONS_PER_HOUR) {
                $results[] = [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->workflow_name,
                    'home_id' => $hid,
                    'status' => 'skipped',
                    'reason' => 'max executions per hour reached',
                ];
                continue;
            }

            try {
                $result = $this->evaluateSingleWorkflow($workflow);
                $results[] = $result;

                if ($result['status'] === 'success') {
                    $homeExecutionCounts[$hid]++;
                }
            } catch (\Throwable $e) {
                Log::error("Workflow evaluation failed: #{$workflow->id} — {$e->getMessage()}");

                $this->logExecution(
                    $workflow,
                    $workflow->trigger_type,
                    null,
                    $workflow->action_type,
                    'failed',
                    $e->getMessage()
                );

                $results[] = [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->workflow_name,
                    'home_id' => $hid,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function evaluateSingleWorkflow(AutomatedWorkflow $workflow): array
    {
        if ($workflow->trigger_type !== 'scheduled' && $workflow->last_triggered_at) {
            $cooldownEnd = $workflow->last_triggered_at->copy()->addHours($workflow->cooldown_hours);
            if (Carbon::now()->lt($cooldownEnd)) {
                return [
                    'workflow_id' => $workflow->id,
                    'workflow_name' => $workflow->workflow_name,
                    'home_id' => $workflow->home_id,
                    'status' => 'skipped',
                    'reason' => 'cooldown active',
                ];
            }
        }

        $triggerResult = $this->evaluateTrigger($workflow);

        if (!$triggerResult['triggered']) {
            return [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->workflow_name,
                'home_id' => $workflow->home_id,
                'status' => 'skipped',
                'reason' => 'trigger not met',
            ];
        }

        $actionResult = $this->executeAction($workflow, $triggerResult['data']);

        $this->logExecution(
            $workflow,
            $workflow->trigger_type,
            $triggerResult['data'],
            $workflow->action_type,
            $actionResult['status'],
            $actionResult['error'] ?? null
        );

        $workflow->last_triggered_at = Carbon::now();

        if ($workflow->trigger_type === 'scheduled') {
            $workflow->next_run_date = $this->advanceNextRunDate($workflow);
        }

        $workflow->save();

        return [
            'workflow_id' => $workflow->id,
            'workflow_name' => $workflow->workflow_name,
            'home_id' => $workflow->home_id,
            'status' => $actionResult['status'],
            'error' => $actionResult['error'] ?? null,
        ];
    }

    public function evaluateTrigger(AutomatedWorkflow $workflow): array
    {
        return match ($workflow->trigger_type) {
            'scheduled' => $this->evaluateScheduledTrigger($workflow),
            'condition' => $this->evaluateConditionTrigger($workflow),
            'event' => $this->evaluateEventTrigger($workflow),
            default => ['triggered' => false, 'data' => []],
        };
    }

    private function evaluateScheduledTrigger(AutomatedWorkflow $workflow): array
    {
        $triggered = $workflow->next_run_date && $workflow->next_run_date->lte(Carbon::now());

        return [
            'triggered' => $triggered,
            'data' => ['scheduled_for' => $workflow->next_run_date?->toDateTimeString()],
        ];
    }

    private function evaluateConditionTrigger(AutomatedWorkflow $workflow): array
    {
        $config = $workflow->trigger_config;
        $entity = $config['entity'] ?? '';
        $condition = $config['condition'] ?? '';
        $threshold = (int) ($config['threshold'] ?? 0);
        $lookbackDays = (int) ($config['lookback_days'] ?? 7);

        $homeId = $workflow->home_id;
        $count = $this->queryEntityCount($entity, $homeId, $condition, $lookbackDays, $config);

        $triggered = false;
        if ($condition === 'count_exceeds') {
            $triggered = $count > $threshold;
        } elseif ($condition === 'days_since') {
            $triggered = $count > $threshold;
        } elseif ($condition === 'status_is') {
            $triggered = $count > 0;
        }

        return [
            'triggered' => $triggered,
            'data' => ['count' => $count, 'condition' => $condition, 'threshold' => $threshold],
        ];
    }

    private function evaluateEventTrigger(AutomatedWorkflow $workflow): array
    {
        $config = $workflow->trigger_config;
        $entity = $config['entity'] ?? '';
        $status = $config['status'] ?? '';
        $minCount = (int) ($config['min_count'] ?? 1);

        $homeId = $workflow->home_id;
        $count = $this->queryEntityByStatus($entity, $homeId, $status);

        return [
            'triggered' => $count >= $minCount,
            'data' => ['count' => $count, 'status' => $status, 'min_count' => $minCount],
        ];
    }

    // ==================== ENTITY QUERIES ====================

    private function queryEntityCount(string $entity, int $homeId, string $condition, int $lookbackDays, array $config): int
    {
        $since = Carbon::now()->subDays($lookbackDays);

        return match ($entity) {
            'incidents' => $this->queryIncidents($homeId, $condition, $since, $config),
            'training' => $this->queryTraining($homeId, $condition, $since, $config),
            'shifts' => $this->queryShifts($homeId, $condition, $since, $config),
            'medication' => $this->queryMedication($homeId, $condition, $since, $config),
            'feedback' => $this->queryFeedback($homeId, $condition, $since, $config),
            default => 0,
        };
    }

    private function queryEntityByStatus(string $entity, int $homeId, string $status): int
    {
        return match ($entity) {
            'incidents' => DB::table('su_incident_report')
                ->where('home_id', $homeId)
                ->where('created_at', '>=', Carbon::today())
                ->count(),
            'training' => DB::table('staff_training')
                ->join('training', 'staff_training.training_id', '=', 'training.id')
                ->where('training.home_id', $homeId)
                ->where('staff_training.status', $status)
                ->count(),
            'shifts' => DB::table('scheduled_shifts')
                ->where('home_id', (string) $homeId)
                ->whereNull('deleted_at')
                ->where('status', $status)
                ->where('start_date', '>=', Carbon::today()->toDateString())
                ->count(),

            'medication' => DB::table('mar_administrations')
                ->join('mar_sheets', 'mar_administrations.mar_sheet_id', '=', 'mar_sheets.id')
                ->where('mar_sheets.home_id', $homeId)
                ->where('mar_administrations.code', $status)
                ->where('mar_administrations.created_at', '>=', Carbon::today())
                ->count(),
            'feedback' => DB::table('client_portal_feedback')
                ->where('home_id', $homeId)
                ->where('status', $status)
                ->count(),
            default => 0,
        };
    }

    private function queryIncidents(int $homeId, string $condition, Carbon $since, array $config): int
    {
        $query = DB::table('su_incident_report')->where('home_id', $homeId);

        if ($condition === 'count_exceeds') {
            return $query->where('created_at', '>=', $since)->count();
        }

        if ($condition === 'days_since') {
            $oldest = $query->orderBy('created_at', 'desc')->value('created_at');
            if (!$oldest) return 999;
            return (int) Carbon::parse($oldest)->diffInDays(Carbon::now());
        }

        return 0;
    }

    private function queryTraining(int $homeId, string $condition, Carbon $since, array $config): int
    {
        $query = DB::table('staff_training')
            ->join('training', 'staff_training.training_id', '=', 'training.id')
            ->where('training.home_id', $homeId);

        if ($condition === 'count_exceeds') {
            return $query->where('staff_training.created_at', '>=', $since)->count();
        }

        if ($condition === 'status_is') {
            $status = $config['status'] ?? '0';
            return $query->where('staff_training.status', $status)->count();
        }

        return 0;
    }

    private function queryShifts(int $homeId, string $condition, Carbon $since, array $config): int
    {
        $query = DB::table('scheduled_shifts')
            ->where('home_id', (string) $homeId)
            ->whereNull('deleted_at');

        if ($condition === 'count_exceeds') {
            return $query->where('start_date', '>=', $since->toDateString())->count();
        }

        if ($condition === 'status_is') {
            $status = $config['status'] ?? 'unfilled';
            return $query->where('status', $status)
                ->where('start_date', '>=', Carbon::today()->toDateString())
                ->count();
        }

        return 0;
    }

    private function queryMedication(int $homeId, string $condition, Carbon $since, array $config): int
    {
        $query = DB::table('mar_administrations')
            ->join('mar_sheets', 'mar_administrations.mar_sheet_id', '=', 'mar_sheets.id')
            ->where('mar_sheets.home_id', $homeId);

        if ($condition === 'count_exceeds') {
            return $query->where('mar_administrations.created_at', '>=', $since)->count();
        }

        if ($condition === 'status_is') {
            $code = $config['status'] ?? 'R';
            return $query->where('mar_administrations.code', $code)->count();
        }

        return 0;
    }

    private function queryFeedback(int $homeId, string $condition, Carbon $since, array $config): int
    {
        $query = DB::table('client_portal_feedback')->where('home_id', $homeId);

        if ($condition === 'count_exceeds') {
            return $query->where('created_at', '>=', $since)->count();
        }

        if ($condition === 'status_is') {
            $status = $config['status'] ?? 'new';
            return $query->where('status', $status)->count();
        }

        return 0;
    }

    // ==================== ACTION EXECUTION ====================

    public function executeAction(AutomatedWorkflow $workflow, array $triggerData): array
    {
        return match ($workflow->action_type) {
            'send_notification' => $this->executeSendNotification($workflow, $triggerData),
            'send_email' => $this->executeSendEmail($workflow, $triggerData),
            default => ['status' => 'failed', 'error' => 'Unknown action type: ' . $workflow->action_type],
        };
    }

    private function executeSendNotification(AutomatedWorkflow $workflow, array $triggerData): array
    {
        $config = $workflow->action_config;
        $message = $config['message'] ?? 'Workflow triggered';
        $isSticky = !empty($config['is_sticky']) ? 1 : 0;

        DB::table('notification')->insert([
            'home_id' => (string) $workflow->home_id,
            'service_user_id' => 0,
            'event_id' => $workflow->id,
            'notification_event_type_id' => 25,
            'event_action' => 'WORKFLOW',
            'message' => $message,
            'is_sticky' => $isSticky,
            'sticky_master_ack' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => 'success'];
    }

    private function executeSendEmail(AutomatedWorkflow $workflow, array $triggerData): array
    {
        $config = $workflow->action_config;
        $recipientsStr = $config['recipients'] ?? '';
        $subject = $config['subject'] ?? 'Care OS Workflow Alert';
        $message = $config['message'] ?? 'A workflow has been triggered.';

        $recipients = $this->parseRecipients($recipientsStr);

        if (empty($recipients)) {
            return ['status' => 'failed', 'error' => 'No valid recipients'];
        }

        $mail = new WorkflowNotificationMail($workflow->workflow_name, $subject, $message);

        Mail::to($recipients[0])
            ->cc(array_slice($recipients, 1))
            ->send($mail);

        return ['status' => 'success'];
    }

    // ==================== LOGGING ====================

    private function logExecution(AutomatedWorkflow $workflow, string $triggerType, ?array $triggerData, string $actionType, string $result, ?string $error = null): void
    {
        WorkflowExecutionLog::create([
            'workflow_id' => $workflow->id,
            'home_id' => $workflow->home_id,
            'trigger_type' => $triggerType,
            'trigger_data' => $triggerData,
            'action_type' => $actionType,
            'action_result' => $result,
            'error_message' => $error,
            'executed_at' => Carbon::now(),
        ]);
    }

    // ==================== SCHEDULING HELPERS ====================

    public function calculateNextRunDate(string $frequency, ?int $day, string $time): Carbon
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 8);
        $minute = (int) ($parts[1] ?? 0);
        $now = Carbon::now();

        switch ($frequency) {
            case 'daily':
                $next = $now->copy()->setTime($hour, $minute, 0);
                if ($next->lte($now)) {
                    $next->addDay();
                }
                return $next;

            case 'weekly':
                $dayOfWeek = $day ?? 1;
                $next = $now->copy()->next($dayOfWeek)->setTime($hour, $minute, 0);
                if ($now->dayOfWeek === $dayOfWeek) {
                    $today = $now->copy()->setTime($hour, $minute, 0);
                    if ($today->gt($now)) {
                        $next = $today;
                    }
                }
                return $next;

            case 'monthly':
                $dayOfMonth = $day ?? 1;
                if ($dayOfMonth > 28) $dayOfMonth = 28;
                $next = $now->copy()->setDay($dayOfMonth)->setTime($hour, $minute, 0);
                if ($next->lte($now)) {
                    $next->addMonth();
                    $next->setDay(min($dayOfMonth, $next->daysInMonth));
                }
                return $next;

            default:
                return $now->copy()->addDay()->setTime($hour, $minute, 0);
        }
    }

    public function advanceNextRunDate(AutomatedWorkflow $workflow): Carbon
    {
        $base = $workflow->next_run_date ?? Carbon::now();
        $config = $workflow->trigger_config;
        $frequency = $config['frequency'] ?? 'daily';

        return match ($frequency) {
            'daily' => $base->copy()->addDay(),
            'weekly' => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonth(),
            default => $base->copy()->addDay(),
        };
    }

    // ==================== VALIDATION ====================

    private function validateTriggerConfig(string $type, array $config): void
    {
        switch ($type) {
            case 'scheduled':
                if (empty($config['frequency']) || !in_array($config['frequency'], ['daily', 'weekly', 'monthly'])) {
                    throw new \RuntimeException('Invalid schedule frequency.');
                }
                if (empty($config['time']) || !preg_match('/^\d{2}:\d{2}$/', $config['time'])) {
                    throw new \RuntimeException('Invalid schedule time format. Use HH:MM.');
                }
                break;

            case 'condition':
                if (empty($config['entity']) || !in_array($config['entity'], self::VALID_ENTITIES)) {
                    throw new \RuntimeException('Invalid entity.');
                }
                if (empty($config['condition']) || !in_array($config['condition'], self::VALID_CONDITIONS)) {
                    throw new \RuntimeException('Invalid condition.');
                }
                if (!isset($config['threshold']) || (int) $config['threshold'] < 0) {
                    throw new \RuntimeException('Invalid threshold.');
                }
                if (!isset($config['lookback_days']) || (int) $config['lookback_days'] < 1 || (int) $config['lookback_days'] > 365) {
                    throw new \RuntimeException('Lookback days must be between 1 and 365.');
                }
                break;

            case 'event':
                if (empty($config['entity']) || !in_array($config['entity'], self::VALID_ENTITIES)) {
                    throw new \RuntimeException('Invalid entity.');
                }
                if (!isset($config['status']) || $config['status'] === '') {
                    throw new \RuntimeException('Status is required for event triggers.');
                }
                if (!isset($config['min_count']) || (int) $config['min_count'] < 1) {
                    throw new \RuntimeException('Min count must be at least 1.');
                }
                break;

            default:
                throw new \RuntimeException('Invalid trigger type.');
        }
    }

    private function validateActionConfig(string $type, array $config): void
    {
        switch ($type) {
            case 'send_notification':
                if (empty($config['message']) || strlen($config['message']) > 1000) {
                    throw new \RuntimeException('Notification message is required (max 1000 chars).');
                }
                break;

            case 'send_email':
                if (empty($config['recipients'])) {
                    throw new \RuntimeException('Email recipients are required.');
                }
                $recipients = $this->parseRecipients($config['recipients']);
                if (empty($recipients)) {
                    throw new \RuntimeException('No valid email recipients found.');
                }
                if (count($recipients) > self::MAX_EMAIL_RECIPIENTS) {
                    throw new \RuntimeException('Maximum of ' . self::MAX_EMAIL_RECIPIENTS . ' email recipients.');
                }
                if (empty($config['subject']) || strlen($config['subject']) > 255) {
                    throw new \RuntimeException('Email subject is required (max 255 chars).');
                }
                if (empty($config['message']) || strlen($config['message']) > 2000) {
                    throw new \RuntimeException('Email message is required (max 2000 chars).');
                }
                break;

            default:
                throw new \RuntimeException('Invalid action type.');
        }
    }

    private function parseRecipients(string $recipientsStr): array
    {
        $raw = array_map('trim', explode(',', $recipientsStr));
        $valid = [];

        foreach ($raw as $email) {
            if ($email === '') continue;
            if (str_contains($email, "\r") || str_contains($email, "\n")) continue;
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            $valid[] = $email;
        }

        return $valid;
    }
}
