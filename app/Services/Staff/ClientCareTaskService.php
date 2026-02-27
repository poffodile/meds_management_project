<?php

namespace App\Services\Staff;

use App\Models\ClientCareTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientCareTaskService
{
    
    public function store(array $data): ClientCareTask
    {
        DB::beginTransaction();
        try{
            $data['scheduled_date'] = Carbon::parse($data['scheduled_date'])->format('Y-m-d');
            $clientCareTask = ClientCareTask::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $clientCareTask;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client Care Task:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientCareTask::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        $tasks = $query
            ->where('user_id', $filters['user_id'])
            // ->where('client_id', $filters['client_id'])
            ->with('clientTaskCategorys:id,title')
            ->orderBy('task_category_id')
            ->orderBy('id')
            ->paginate(10);
        $groupedTasks = $tasks->getCollection()->groupBy('task_category_id');
        $tasks->setCollection($groupedTasks);
        return $tasks;
    }
    public function details($id){
        return ClientCareTask::find($id);
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = ClientCareTask::find($id);
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
