<?php

namespace App\Services\AI;

use App\Models\AICarePlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AICarePlanService
{
    private OpenAIService $openAI;
    private PIIFilter $piiFilter;
    private TokenTracker $tokenTracker;
    private PromptBuilder $promptBuilder;

    public function __construct(
        OpenAIService $openAI,
        PIIFilter $piiFilter,
        TokenTracker $tokenTracker,
        PromptBuilder $promptBuilder
    ) {
        $this->openAI = $openAI;
        $this->piiFilter = $piiFilter;
        $this->tokenTracker = $tokenTracker;
        $this->promptBuilder = $promptBuilder;
    }

    public function generate(int $clientId, int $homeId, int $userId, string $assessmentType, string $careSetting): array
    {
        if (!$this->openAI->isConfigured()) {
            return ['status' => false, 'error' => 'AI is not configured. Please contact your administrator.'];
        }

        if ($this->tokenTracker->isCapExceeded($homeId)) {
            return ['status' => false, 'error' => 'Daily AI usage limit reached. Resets at midnight.'];
        }

        $prompts = $this->promptBuilder->buildCarePlanGenerationPrompt(
            $clientId,
            $homeId,
            $assessmentType,
            $careSetting
        );

        if (empty($prompts['assessment_data'])) {
            return ['status' => false, 'error' => 'No assessment data found for this client.'];
        }

        $userPrompt = $this->piiFilter->filter($prompts['user_prompt'], $homeId, skipNames: true);

        $messages = [
            ['role' => 'system', 'content' => $prompts['system_prompt']],
            ['role' => 'user', 'content' => '<assessment_data>' . $userPrompt . '</assessment_data>'],
        ];

        $promptHash = hash('sha256', json_encode($messages));

        try {
            $startTime = microtime(true);

            $result = $this->openAI->chatJson($messages, config('ai.quality_model', 'gpt-4o'));

            $generationTimeMs = (int) ((microtime(true) - $startTime) * 1000);

            $planData = json_decode($result['content'], true);

            if (!$this->validatePlanStructure($planData)) {
                $this->tokenTracker->log(
                    $homeId,
                    $userId,
                    'care_plan',
                    $result['model'],
                    $result['tokens_input'],
                    $result['tokens_output'],
                    'error',
                    $promptHash,
                    'Invalid JSON structure from AI'
                );
                return ['status' => false, 'error' => 'AI returned an invalid care plan structure. Please try again.'];
            }

            $this->tokenTracker->log(
                $homeId,
                $userId,
                'care_plan',
                $result['model'],
                $result['tokens_input'],
                $result['tokens_output'],
                'success',
                $promptHash,
                null,
                $result['latency_ms'] ?? $generationTimeMs
            );

            return [
                'status' => true,
                'plan_data' => $planData,
                'tokens_used' => $result['tokens_input'] + $result['tokens_output'],
                'model' => $result['model'],
                'tokens_input' => $result['tokens_input'],
                'tokens_output' => $result['tokens_output'],
                'generation_time_ms' => $generationTimeMs,
                'assessment_snapshot' => $prompts['assessment_data'],
            ];
        } catch (RuntimeException $e) {
            $this->tokenTracker->log(
                $homeId,
                $userId,
                'care_plan',
                config('ai.quality_model', 'gpt-4o'),
                0,
                0,
                'error',
                $promptHash,
                $e->getMessage()
            );

            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    public function save(
        int $clientId,
        int $homeId,
        int $userId,
        array $planData,
        string $assessmentType,
        string $careSetting,
        string $status,
        ?array $assessmentSnapshot,
        string $model,
        int $tokensInput,
        int $tokensOutput,
        ?int $generationTimeMs = null
    ): AICarePlan {
        $reviewMonths = 3;
        if (isset($planData['review_schedule']['review_frequency'])) {
            $freq = $planData['review_schedule']['review_frequency'];
            if ($freq === '6_months') {
                $reviewMonths = 6;
            } elseif ($freq === '1_month') {
                $reviewMonths = 1;
            }
        }

        $plan = new AICarePlan();
        $plan->home_id = $homeId;
        $plan->client_id = $clientId;
        $plan->created_by = $userId;
        $plan->plan_status = $status;
        $plan->assessment_type = $assessmentType;
        $plan->care_setting = $careSetting;
        $plan->plan_data = $planData;
        $plan->assessment_snapshot = $assessmentSnapshot;
        $plan->ai_model = $model;
        $plan->tokens_input = $tokensInput;
        $plan->tokens_output = $tokensOutput;
        $plan->generation_time_ms = $generationTimeMs;
        $plan->review_date = Carbon::now()->addMonths($reviewMonths)->toDateString();
        $plan->is_deleted = 0;
        $plan->created_at = Carbon::now();
        $plan->updated_at = Carbon::now();

        if ($status === 'active') {
            $plan->approved_at = Carbon::now();
            $plan->approved_by = $userId;
            $this->supersedePreviousActive($clientId, $homeId);
        }

        $plan->save();

        return $plan;
    }

    public function listPlans(int $clientId, int $homeId): Collection
    {
        return AICarePlan::forHome($homeId)
            ->forClient($clientId)
            ->notDeleted()
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPlan(int $planId, int $homeId): ?AICarePlan
    {
        return AICarePlan::forHome($homeId)
            ->notDeleted()
            ->find($planId);
    }

    public function updatePlan(int $planId, int $homeId, array $planData): bool
    {
        $plan = $this->getPlan($planId, $homeId);
        if (!$plan) {
            return false;
        }

        $plan->plan_data = $planData;
        $plan->updated_at = Carbon::now();
        $plan->save();

        return true;
    }

    public function deletePlan(int $planId, int $homeId): void
    {
        $plan = AICarePlan::forHome($homeId)->notDeleted()->find($planId);
        if ($plan) {
            $plan->is_deleted = 1;
            $plan->save();
        }
    }

    public function activatePlan(int $planId, int $homeId, int $userId): bool
    {
        $plan = $this->getPlan($planId, $homeId);
        if (!$plan) {
            return false;
        }

        $this->supersedePreviousActive($plan->client_id, $homeId);

        $plan->plan_status = 'active';
        $plan->approved_at = Carbon::now();
        $plan->approved_by = $userId;
        $plan->updated_at = Carbon::now();
        $plan->save();

        return true;
    }

    private function supersedePreviousActive(int $clientId, int $homeId): void
    {
        AICarePlan::forHome($homeId)
            ->forClient($clientId)
            ->active()
            ->notDeleted()
            ->update(['plan_status' => 'superseded', 'updated_at' => Carbon::now()]);
    }

    private function validatePlanStructure(?array $data): bool
    {
        if (!$data) {
            return false;
        }

        $required = ['summary', 'objectives', 'care_tasks', 'risk_factors'];
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        if (!is_array($data['objectives']) || !is_array($data['care_tasks']) || !is_array($data['risk_factors'])) {
            return false;
        }

        return true;
    }
}
