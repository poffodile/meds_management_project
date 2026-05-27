<?php

namespace App\Services\Client;

use App\Models\ClientMentalCapacity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MentalCapacityService
{
    
    public function store(array $data): ClientMentalCapacity
    {
        DB::beginTransaction();
        try{
            $mentalCapacity = ClientMentalCapacity::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $mentalCapacity;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Client Mental Capacity:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientMentalCapacity::query();

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
        return ClientMentalCapacity::with(['clients'])->find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = ClientMentalCapacity::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Mental Capacity:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
