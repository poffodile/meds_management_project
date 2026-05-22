<?php

namespace App\Services\Client;

use App\Models\ClientAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientAlertService
{
    
    public function store(array $data): ClientAlert
    {
        DB::beginTransaction();
        try{
            $clientAlert = ClientAlert::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $clientAlert;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Alert Type:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientAlert::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if(!empty($filters['severity']) && $filters['severity'] != 1){
            $query->where('severity', $filters['severity']);
        }
        if(!empty($filters['status']) && $filters['status'] != 0){
            $query->where('status', $filters['status']);
        }
        if(!empty($filters['type']) && $filters['type'] != 0){
            $query->where('alert_type_id', $filters['type']);
        }
        // if($filters['sort_by'] != 1){
            
        // }

       return $query->with('alert_types:id,title')->orderBy('id','DESC');
    }
    public function details($id){
        return ClientAlert::find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = ClientAlert::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Care Task:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
    public function alert_increase_acknowledge($id){
        DB::beginTransaction();
        try{
            $clientAlert = ClientAlert::find($id);
            $count = $clientAlert->staff_acknowledgment_count;
            if($count){
                $clientAlert->staff_acknowledgment_count = $count+1;
            }else{
                $clientAlert->staff_acknowledgment_count = 1;
            }
            $clientAlert->save();
            DB::commit();
            return $clientAlert;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error increase in acknowledge :', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }
    public function client_alert_resolve($id){
       DB::beginTransaction();
        try{
            $clientAlert = ClientAlert::find($id);
            $clientAlert->resolve_date = date('Y-m-d H:i');
            $clientAlert->status =2;
            $clientAlert->save();
            DB::commit();
            return $clientAlert;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error alert resolved :', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        } 
    }
    public function client_alert_archived($id){
        DB::beginTransaction();
        try{
            $clientAlert = ClientAlert::find($id);
            $clientAlert->status = 3;
            $clientAlert->save();
            DB::commit();
            return $clientAlert;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error alert archived :', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        } 
    }
    public function alert_increase_all_acknowledge(){
         DB::beginTransaction();

        try {

            $data = ClientAlert::where('status', 1)
                ->where('requires_staff_acknowledgment', 1)
                ->increment('staff_acknowledgment_count');

            DB::commit();

            return $data;

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error increase in all acknowledge :', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
