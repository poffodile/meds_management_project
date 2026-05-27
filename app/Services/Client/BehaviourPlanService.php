<?php

namespace App\Services\Client;

use App\Models\ClientBehaviorSupportPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BehaviourPlanService
{
    
    public function store(array $data): ClientBehaviorSupportPlan
    {
        DB::beginTransaction();
        try{
            $behavior_plan = ClientBehaviorSupportPlan::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $behavior_plan;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Client Behavior Support Plan:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientBehaviorSupportPlan::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        $query->with(['clients']);
        return $query->orderBy('id');
    }
    public function details($id){
        return ClientBehaviorSupportPlan::with(['clients'])->find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = ClientBehaviorSupportPlan::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Behavior Support Plan:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
