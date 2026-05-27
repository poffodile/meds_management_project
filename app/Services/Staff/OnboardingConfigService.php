<?php

namespace App\Services\Staff;

use App\Models\CompanyDepartment;
use App\Models\OnboardingConfig;
use App\Models\OnboardingConfigStage;
use App\User;
use App\ServiceUser;
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
            $req['type'] == 'staff' ? $data->access_level = $req['access_level'] ?? "" : "";
            $data->title = $title;
            $data->status = 0;
            $data->auto_activate = 1;
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
            $data->dynamic_form_id = $req['dynamic_form_id'];
            $data->selected_user_ids = $req['service_user_ids'] ?? null;
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
        $id = $filters['id'] ?? null;
        $home_id = $filters['home_id'] ?? null;
        $user_id = $filters['user_id'] ?? null;
        $type = $filters['type'] ?? null;
        $care_type = $filters['care_type'] ?? null;
        $access_level = $filters['access_level'] ?? null;
        $subQuery = OnboardingConfig::query();
        if (!empty($id)) {
            $subQuery->where('id', $id);
        }
        if (!empty($home_id)) {
            $subQuery->where('home_id', $home_id);
        }
        if (!empty($user_id)) {
            $subQuery->where('user_id', $user_id);
        }
        if (!empty($type)) {
            $subQuery->where('type', $type);
        }
        if (!empty($care_type) && $care_type != 'all') {
            $subQuery->where('care_type', $care_type);
        }
        if (!empty($type) && $type == 'staff' && !empty($access_level)) {
            $subQuery->where('access_level', $access_level);
        }
        return  $subQuery;
    }
    public function loadWorkflowStages($filters)
    {
        $workflow_id = $filters['workflow_id'] ?? null;
        $selected_user_ids = $filters['selected_user_ids'] ?? null;
        $selected_user_ids;
        $subQuery = OnboardingConfigStage::query();

        if (!empty($workflow_id)) {
            $subQuery->where('onboarding_config_id', $workflow_id);
        }
        if (!empty($selected_user_ids)) {
            $subQuery->where(function ($q1) use ($selected_user_ids) {
                $q1->whereNull('selected_user_ids')
                    ->orWhereJsonLength('selected_user_ids', 0)
                    ->orWhereJsonContains('selected_user_ids', $selected_user_ids);
            });
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
            // return $req;
            DB::beginTransaction();
            $id = $req['id'];
            $home_id = $req['home_id'];
            $type = $req['type'];
            if ($type == 'workflow') {
                $category = $req['category'];
                $data = OnboardingConfig::find($id);
                OnboardingConfig::where('home_id', $home_id)->where('type', $category)
                    ->where('care_type', $data->care_type)->where('access_level', $data->access_level)->update(['status' => 0]);

                if (!$data) {
                    return false;
                }

                // Delete stages (safe way)
                // if ($data->getstages()->exists()) {
                //     foreach ($data->getstages as $stage) {
                //         $stage->status = $stage->status == 0 ? 1 : 0;
                //         $stage->save();
                //     }
                // }

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
    public function activatestatus($req)
    {
        try {
            // return $req;
            DB::beginTransaction();
            $id = $req['workflow_id'];
            OnboardingConfig::where('id', $id)->update([
                'auto_activate' => DB::raw('IF(auto_activate = 1, 0, 1)')
            ]);
            DB::commit();
            return true;
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

    function loadActiveWorkFlow($req) {}

    public function getUserFormPercentage($homeId, $userId = null){
        $users = ServiceUser::where('home_id', $homeId)
            ->when($userId, fn($q) => $q->where('id', $userId))
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get();

        $result = [];

        foreach ($users as $user) {

            $workflow = $this->loadWorkFlow([
                'type' => 'client',
                'care_type' => $user->department,
                'home_id'=>$user->home_id
            ])
            ->where('status', 1)
            ->withCount([

                'getstages as total' => function ($q) use ($user) {

                    $q->where('status', 1)
                        ->where('required_stage', 1)
                        ->where(function ($q1) use ($user) {

                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $user->id);
                        });
                },

                'getstages as completed' => function ($q) use ($user) {

                    $q->where('status', 1)
                        ->where('required_stage', 1)
                        ->where(function ($q1) use ($user) {

                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $user->id);
                        })
                        ->whereHas('onboardingforms', function ($q2) use ($user) {

                            $q2->where('user_id', $user->id);
                        });
                }

            ])
            ->first();

            $total = $workflow->total ?? 0;
            $completed = $workflow->completed ?? 0;

            $result[$user->id] = [
                'percentage' => $total > 0
                    ? round(($completed / $total) * 100, 0)
                    : 0,

                'total' => $total,
                'completed' => $completed,
            ];
        }

        return $result;
    }
}
