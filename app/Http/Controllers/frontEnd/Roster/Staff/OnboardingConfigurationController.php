<?php

namespace App\Http\Controllers\frontend\Roster\Staff;

use App\Http\Controllers\Controller;
use App\Models\CompanyDepartment;
use App\Models\EntityType;
use App\Models\OnboardingConfigStage;
use App\Services\Staff\OnboardingConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboardingConfigurationController extends Controller
{
    protected OnboardingConfigService $onboardingConfigService;

    public function __construct(OnboardingConfigService $onboardingConfigService)
    {
        $this->onboardingConfigService = $onboardingConfigService;
    }
    public function index()
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $entityType = EntityType::where('home_id', $homeId)->where('status', 1)->orderBy('type')->get();
        $company_departments = CompanyDepartment::getActiveCompanyDepartment();
        $data['entityType'] = $entityType;
        $data['company_departments'] = $company_departments;

        return view('frontEnd/roster/staff/onboarding_config/index', $data);
    }
    public function create_workflow(Request $req)
    {

        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $homeId  = $homeIds[0] ?? null;
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = auth()->id();
            // return $reqData;
            $data = $this->onboardingConfigService->createWorkflow($reqData);
            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data not saved'], 422);
            }
            return response()->json(['status' => true, 'message' => 'Workflow created successfully']);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function create_stages(Request $req)
    {

        try {
            // return $req;
            $messages = [
                'workflow_id.required' => 'Please select a workflow.',
                'stage_id.required' => 'Stage ID is required.',
                'stage_name.required' => 'Stage name is required.',
                'description.required' => 'Description cannot be empty.',
                'entity_type_id.required' => 'Please select an entity type.',

                'status_name.nullable' => 'Status name is optional.',
                'required_stage.nullable' => 'Required stage field is optional.',
                'auto_create_task.nullable' => 'Auto create task field is optional.',
            ];
            $validator = Validator::make(
                $req->all(),
                [
                    'workflow_id' => 'required',
                    'stage_id' => 'nullable',
                    'stage_name' => 'required',
                    'description' => 'required',
                    'entity_type_id' => 'required',
                    'status_name' => 'nullable',
                    'required_stage' => 'nullable',
                    'auto_create_task' => 'nullable',
                ],
                $messages
            );
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'status' => false,
                    'message' => $errors->first(),                 // first error message
                    'key' => array_key_first($errors->toArray()),
                ], 422);
            }
            $homeIds = explode(',', auth()->user()->home_id);
            $homeId  = $homeIds[0] ?? null;
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = auth()->id();
            // return $reqData;


            $data = $this->onboardingConfigService->createStages($reqData);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not saved'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => $req->stage_id ? 'Stage updated successfully' : 'Stage created successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function load_workflow(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $reqData = $req->all();
        $reqData['home_id'] = $homeId;
        $data = $this->onboardingConfigService->loadWorkFlow($reqData)
            ->with(['departments:id,name'])
            ->withCount('getstages')->latest()->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
    public function load_stages(Request $req)
    {
        $reqData = $req->all();
        $data = $this->onboardingConfigService->loadWorkflowStages($reqData)
            ->orderBy('order_no', 'ASC')->get();
        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function status_workflow(Request $req)
    {
        try {
            $reqData = $req->all();
            $data = $this->onboardingConfigService->status_change($reqData);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Status not changed'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Status Changed Successfully',
                'data' => $data->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function delete_workflow(Request $req)
    {
        try {
            $reqData = $req->all();
            $data = $this->onboardingConfigService->deleteWorkflow($reqData);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Workflow not deleted'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Workflow deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function delete_stages(Request $req)
    {
        try {
            $reqData = $req->all();
            $data = $this->onboardingConfigService->deleteWorkflowStage($reqData);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Workflow stage not deleted'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Workflow stage deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function ordering_stages(Request $req)
    {
        try {
            $reqData = $req->all();
            $data = $this->onboardingConfigService->orderingStages($reqData);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Workflow not updated'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Workflow updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function details_stages(Request $req)
    {
        try {
            $data = OnboardingConfigStage::find($req->id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
