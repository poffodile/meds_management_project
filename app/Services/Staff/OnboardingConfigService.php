<?php

namespace App\Services\Staff;

use App\Models\CompanyDepartment;
use App\Models\OnboardingConfig;
use App\Models\OnboardingConfigStage;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OnboardingConfigService
{

    public function createWorkflow($req)
    {
        try {
            DB::beginTransaction();
 $title = "All Settings " . ucfirst($req['type']) . " Onboarding";
            if (isset($req['care_type']) && $req['care_type'] != 'all') {
                $careD = CompanyDepartment::find($req['care_type']);
                $title = ucfirst($careD->name) . " Onboarding";
            }
            $data =  new OnboardingConfig;
            $data->type = $req['type'];
            $data->home_id = $req['home_id'];
            $data->user_id = $req['user_id'];
            $data->care_type = $req['care_type'] ?? "";
            $data->title = $title;
            $data->save();

            DB::commit();
            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function deleteWorkflow($req)
    {
        try {
            DB::beginTransaction();

            $data = OnboardingConfig::find($req['workflow_id']);

            if (!$data) {
                return false;
            }

            // Delete stages (safe way)
            if ($data->getstages()->exists()) {
                foreach ($data->getstages as $stage) {
                    $stage->delete();
                }
            }

            // Delete workflow
            $data->delete();

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function deleteWorkflowStage($req)
    {
        try {
            DB::beginTransaction();
            $workflowId = $req['workflow_id'];
            $data = OnboardingConfigStage::find($req['id']);

            if (!$data) {
                return false;
            }

            // Delete workflow
            $data->delete();

            $stages = OnboardingConfigStage::where('onboarding_config_id', $workflowId)
                ->orderBy('order_no', 'asc')
                ->get();

            $order = 1;
            foreach ($stages as $stage) {
                OnboardingConfigStage::where('id', $stage->id)
                    ->update(['order_no' => $order++]);
            }
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function createStages($req)
    {
        try {
            DB::beginTransaction();
            $lastOrder = OnboardingConfigStage::where('onboarding_config_id', $req['workflow_id'])
                ->max('order_no');
            $data = $req['stage_id'] ? OnboardingConfigStage::find($req['stage_id']) : new OnboardingConfigStage;
            $data->onboarding_config_id = $req['workflow_id'];
            $data->entity_type_id = $req['entity_type_id'];


            !isset($req['stage_id']) ? $data->order_no = $lastOrder ? $lastOrder + 1 : 1 : "";
            $data->required_stage = $req['required_stage'] ?? 0;
            $data->auto_create_task = $req['auto_create_task'] ?? 0;
            $data->stage_name = $req['stage_name'];
            $data->status_name = $req['status_name'];
            $data->description = $req['description'];
            $data->save();

            DB::commit();
            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function loadWorkFlow($filters)
    {
        $home_id = $filters['home_id'] ?? null;
        $user_id = $filters['user_id'] ?? null;
        $type = $filters['type'] ?? null;
        $care_type = $filters['care_type'] ?? null;
        $subQuery = OnboardingConfig::query();
        if (!empty($home_id)) {
            $subQuery->where('home_id', $home_id);
        }
        if (!empty($user_id)) {
            $subQuery->where('user_id', $user_id);
        }
        if (!empty($type)) {
            $subQuery->where('type', $type);
        }
        if (!empty($care_type)) {
            $subQuery->where('care_type', $care_type);
        }
        return  $subQuery;
    }
    public function loadWorkflowStages($filters)
    {
        $workflow_id = $filters['workflow_id'] ?? null;
        $subQuery = OnboardingConfigStage::query();

        if (!empty($workflow_id)) {
            $subQuery->where('onboarding_config_id', $workflow_id);
        }
        $subQuery->with([
            'entitydata:id,type',
            'workflow:id,type,care_type,title',
        ]);
        return  $subQuery;
    }
    public function status_change($req)
    {
        try {
            DB::beginTransaction();
            $id = $req['id'];
            $type = $req['type'];
            if ($type == 'workflow') {
                $data = OnboardingConfig::find($id);

                if (!$data) {
                    return false;
                }

                // Delete stages (safe way)
                if ($data->getstages()->exists()) {
                    foreach ($data->getstages as $stage) {
                        $stage->status = $stage->status == 0 ? 1 : 0;
                        $stage->save();
                    }
                }

                // Delete workflow
                $data->status = $data->status == 0 ? 1 : 0;
                $data->save();
            } else {
                $data = OnboardingConfigStage::find($id);

                if (!$data) {
                    return false;
                }

                // Delete workflow
                $data->status = $data->status == 0 ? 1 : 0;
                $data->save();
            }
            DB::commit();
            return $data;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function orderingStages($req)
    {
        try {
            DB::beginTransaction();
            $stageId = $req['id'];
            $order = $req['order'];
            $data = OnboardingConfigStage::find($stageId);

            if (!$data) {
                return false;
            }
            $workflowId = $data->onboarding_config_id;
            $oldOrderNo = $data->order_no;
            if ($order == 'asc') {
                $newOrderNo = $oldOrderNo - 1;
            } else {
                $newOrderNo = $oldOrderNo + 1;
            }
            $stages = OnboardingConfigStage::where('onboarding_config_id', $workflowId)
                ->where('order_no', $newOrderNo)
                ->first();
            $stages->order_no = $oldOrderNo;
            $stages->save();
            $data->order_no = $newOrderNo;
            $data->save();
            DB::commit();
            // return [
            //     "workflowId: $workflowId, oldOrderNo: $oldOrderNo , newOrderNo : $newOrderNo",
            //     $req,
            //     $stages
            // ];
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
