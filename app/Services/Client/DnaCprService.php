<?php

namespace App\Services\Client;

use App\Models\DoNotAttemptCpr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DnaCprService
{
    
    public function store(array $data): DoNotAttemptCpr
    {
        DB::beginTransaction();
        try{
            $dncpr = DoNotAttemptCpr::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $dncpr;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Do Not Attempt CPR:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = DoNotAttemptCpr::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        $dncpr = $query
            ->orderBy('id')
            ->paginate(10);
        return $dncpr;
    }
    public function details($id){
        return DoNotAttemptCpr::find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = DoNotAttemptCpr::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Do Not Attempt CPR:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
