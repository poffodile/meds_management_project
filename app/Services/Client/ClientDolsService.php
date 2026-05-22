<?php

namespace App\Services\Client;

use App\Models\Dol;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientDolsService
{
    
    public function store(array $data): Dol
    {
        DB::beginTransaction();
        try{
            $clientDols = Dol::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $clientDols;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Client Dols:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = Dol::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        $dols = $query
            ->orderBy('id')
            ->paginate(10);
        return $dols;
    }
    public function details($id){
        return Dol::find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = Dol::find($id);
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
}
