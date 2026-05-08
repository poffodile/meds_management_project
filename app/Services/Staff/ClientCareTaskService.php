<?php

namespace App\Services\Staff;

use App\Models\ClientCareTask;
use App\Models\ScheduledShift;
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
        $isGrouped = $filters['isGrouped'] ?? 0;
        // echo "<pre>";print_r($filters);die;
        $query = ClientCareTask::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['carer_id'])) {
            $query->where('carer_id', $filters['carer_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        $query
            // ->where('client_id', $filters['client_id'])
            ->with([
                'clientTaskCategorys:id,title',
                'clientTaskType:id,title',
                'carer:id,name'
            ]);
        if ($isGrouped == 0) {
            $tasks = $query->orderBy('task_category_id')
                ->orderBy('id', 'DESC')->paginate(10);
            $groupedTasks = $tasks->getCollection()->groupBy('task_category_id');
            $tasks->setCollection($groupedTasks);
        } else {
            $tasks = $query->latest();
        }
        return $tasks;
    }
    public function details($id){
        return ClientCareTask::find($id);
    }
    public function status_change($req)
    {
        try {
            DB::beginTransaction();
            $task_id = $req['id'];
            $status = $req['status'];
            $task_record = ClientCareTask::where('id', $task_id)
                ->first();

            if (!isset($task_record)) {
                return [
                    'status' => false,
                    'message' => "Data Not Found !!"
                ];
            }
            $task_record->status = $task_record->status == 1 ? 1 : $status;
            $task_record->save();
            DB::commit();
            return [
                'status' => true,
                'message' => 'Status changed successfully !!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function add_comment($req)
    {
        try {
            DB::beginTransaction();
            $task_id = $req['id'];
            $comments = $req['comment'];
            $task_record = ClientCareTask::where('id', $task_id)
                ->first();

            if (!isset($task_record)) {
                return [
                    'status' => false,
                    'message' => "Data Not Found !!"
                ];
            }
            $task_record->comment = $comments;
            $task_record->save();
            DB::commit();
            return [
                'status' => true,
                'message' => 'Comment add successfully !!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
            Log::error('Error delete Client Care Task:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }
    public function shiftCheck($data){
        $query = ScheduledShift::query();

        if (!empty($data['home_id'])) {
            $query->where('home_id', $data['home_id']);
        }

        if (!empty($data['carer_id'])) {
            $query->where('staff_id', $data['carer_id']);
        }
        $query->whereDate('start_date', '>=', now());
        $query->whereNotExists(function ($q) {
            $q->select(\DB::raw(1))
            ->from('client_care_tasks')
            ->whereColumn('client_care_tasks.shift_id', 'scheduled_shifts.id')
            ->whereNull('client_care_tasks.deleted_at');
        });

        return $query->orderBy('start_date', 'asc')->get();
    }
}
