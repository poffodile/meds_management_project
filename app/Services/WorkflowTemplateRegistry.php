<?php

namespace App\Services;

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
