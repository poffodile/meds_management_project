<?php

namespace App\Console\Commands;

use App\Services\WorkflowEngineService;
use Illuminate\Console\Command;

class EvaluateWorkflows extends Command
{
    protected $signature = 'workflows:evaluate';
    protected $description = 'Evaluate all active workflow triggers and execute actions';

    public function handle(WorkflowEngineService $service): int
    {
        $this->info('Evaluating workflow triggers...');

        $results = $service->evaluateAllWorkflows();

        if (empty($results)) {
            $this->info('No active workflows to evaluate.');
            return 0;
        }

        $triggered = collect($results)->where('status', 'success')->count();
        $failed = collect($results)->where('status', 'failed')->count();
        $skipped = collect($results)->where('status', 'skipped')->count();

        $this->info("Workflows evaluated: " . count($results));
        $this->info("  Triggered: {$triggered}, Failed: {$failed}, Skipped: {$skipped}");

        foreach ($results as $r) {
            $icon = $r['status'] === 'success' ? '✓' : ($r['status'] === 'failed' ? '✗' : '○');
            $detail = $r['error'] ?? $r['reason'] ?? '';
            $line = "  {$icon} #{$r['workflow_id']} {$r['workflow_name']}: {$r['status']}";
            if ($detail) {
                $line .= " — {$detail}";
            }
            $this->line($line);
        }

        return 0;
    }
}
