<?php

namespace App\Services\AI;

use App\Models\AIUsageLog;
use Carbon\Carbon;

class TokenTracker
{
    public function log(int $homeId, int $userId, string $feature, string $model,
                        int $tokensInput, int $tokensOutput, string $status,
                        ?string $promptHash = null, ?string $error = null, ?int $latencyMs = null): void
    {
        $log = new AIUsageLog();
        $log->home_id = $homeId;
        $log->user_id = $userId;
        $log->feature = $feature;
        $log->model_used = $model;
        $log->tokens_input = $tokensInput;
        $log->tokens_output = $tokensOutput;
        $log->tokens_total = $tokensInput + $tokensOutput;
        $log->prompt_hash = $promptHash;
        $log->response_status = $status;
        $log->error_message = $error;
        $log->latency_ms = $latencyMs;
        $log->created_at = Carbon::now();
        $log->save();
    }

    public function isCapExceeded(int $homeId): bool
    {
        return $this->getDailyUsage($homeId) >= $this->getDailyCap($homeId);
    }

    public function getDailyUsage(int $homeId): int
    {
        return (int) AIUsageLog::where('home_id', $homeId)
            ->where('created_at', '>=', Carbon::today())
            ->where('response_status', 'success')
            ->sum('tokens_total');
    }

    public function getRemainingTokens(int $homeId): int
    {
        $remaining = $this->getDailyCap($homeId) - $this->getDailyUsage($homeId);
        return max(0, $remaining);
    }

    public function getDailyCap(int $homeId): int
    {
        return (int) config('ai.daily_token_cap', 100000);
    }
}
