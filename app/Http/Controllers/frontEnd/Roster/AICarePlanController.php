<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Services\AI\AICarePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AICarePlanController extends Controller
{
    private AICarePlanService $carePlanService;

    public function __construct(AICarePlanService $carePlanService)
    {
        $this->carePlanService = $carePlanService;
    }

    private function homeId(): int
    {
        return (int) explode(',', Auth::user()->home_id)[0];
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
            'assessment_type' => 'required|in:initial,review,reassessment',
            'care_setting' => 'required|in:residential,nursing,domiciliary',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        $result = $this->carePlanService->generate(
            $clientId,
            $homeId,
            Auth::id(),
            $request->input('assessment_type'),
            $request->input('care_setting')
        );

        return response()->json($result);
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
            'plan_data' => 'required|array',
            'assessment_type' => 'required|in:initial,review,reassessment',
            'care_setting' => 'required|in:residential,nursing,domiciliary',
            'status' => 'required|in:draft,active',
            'model' => 'required|string|max:50',
            'tokens_input' => 'required|integer|min:0',
            'tokens_output' => 'required|integer|min:0',
            'generation_time_ms' => 'nullable|integer|min:0',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        $plan = $this->carePlanService->save(
            $clientId,
            $homeId,
            Auth::id(),
            $request->input('plan_data'),
            $request->input('assessment_type'),
            $request->input('care_setting'),
            $request->input('status'),
            $request->input('assessment_snapshot'),
            $request->input('model'),
            (int) $request->input('tokens_input'),
            (int) $request->input('tokens_output'),
            $request->input('generation_time_ms') ? (int) $request->input('generation_time_ms') : null
        );

        return response()->json([
            'status' => true,
            'plan_id' => $plan->id,
            'message' => 'Care plan saved successfully.',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $clientId = (int) $request->input('client_id');

        $client = DB::table('service_user')
            ->where('id', $clientId)
            ->where('home_id', $homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$client) {
            return response()->json(['status' => false, 'error' => 'Client not found.'], 404);
        }

        $plans = $this->carePlanService->listPlans($clientId, $homeId);

        return response()->json([
            'status' => true,
            'plans' => $plans->map(function ($plan) {
                $planData = $plan->plan_data;
                return [
                    'id' => $plan->id,
                    'plan_status' => $plan->plan_status,
                    'assessment_type' => $plan->assessment_type,
                    'care_setting' => $plan->care_setting,
                    'objectives_count' => isset($planData['objectives']) ? count($planData['objectives']) : 0,
                    'tasks_count' => isset($planData['care_tasks']) ? count($planData['care_tasks']) : 0,
                    'risks_count' => isset($planData['risk_factors']) ? count($planData['risk_factors']) : 0,
                    'medications_count' => isset($planData['medication_summary']['total_medications']) ? $planData['medication_summary']['total_medications'] : 0,
                    'review_date' => $plan->review_date ? $plan->review_date->format('M j, Y') : null,
                    'created_at' => $plan->created_at ? $plan->created_at->format('M j, Y') : null,
                    'created_by_name' => optional($plan->createdBy)->name ?? 'Unknown',
                    'tokens_used' => $plan->tokens_input + $plan->tokens_output,
                ];
            }),
        ]);
    }

    public function view(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $plan = $this->carePlanService->getPlan((int) $request->input('plan_id'), $homeId);

        if (!$plan) {
            return response()->json(['status' => false, 'error' => 'Care plan not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'plan' => [
                'id' => $plan->id,
                'client_id' => $plan->client_id,
                'plan_status' => $plan->plan_status,
                'assessment_type' => $plan->assessment_type,
                'care_setting' => $plan->care_setting,
                'plan_data' => $plan->plan_data,
                'ai_model' => $plan->ai_model,
                'tokens_input' => $plan->tokens_input,
                'tokens_output' => $plan->tokens_output,
                'generation_time_ms' => $plan->generation_time_ms,
                'review_date' => $plan->review_date ? $plan->review_date->format('Y-m-d') : null,
                'approved_at' => $plan->approved_at ? $plan->approved_at->format('M j, Y H:i') : null,
                'created_at' => $plan->created_at ? $plan->created_at->format('M j, Y H:i') : null,
                'created_by_name' => optional($plan->createdBy)->name ?? 'Unknown',
                'approved_by_name' => optional($plan->approvedBy)->name ?? null,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'plan_data' => 'required|array',
        ]);

        $homeId = $this->homeId();
        $updated = $this->carePlanService->updatePlan(
            (int) $request->input('plan_id'),
            $homeId,
            $request->input('plan_data')
        );

        if (!$updated) {
            return response()->json(['status' => false, 'error' => 'Care plan not found.'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Care plan updated.']);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $this->carePlanService->deletePlan((int) $request->input('plan_id'), $homeId);

        return response()->json(['status' => true, 'message' => 'Care plan deleted.']);
    }

    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer',
        ]);

        $homeId = $this->homeId();
        $activated = $this->carePlanService->activatePlan(
            (int) $request->input('plan_id'),
            $homeId,
            Auth::id()
        );

        if (!$activated) {
            return response()->json(['status' => false, 'error' => 'Care plan not found.'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Care plan activated.']);
    }
}
