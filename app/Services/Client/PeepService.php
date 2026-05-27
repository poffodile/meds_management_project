<?php

namespace App\Services\Client;

use App\Models\ClientPeep;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeepService
{
    
    public function store(array $data): ClientPeep
    {
        DB::beginTransaction();
        try{
            $peep = ClientPeep::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $peep;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Client Peep:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientPeep::query();

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
        return ClientPeep::with(['clients'])->find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = ClientPeep::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Peep:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
