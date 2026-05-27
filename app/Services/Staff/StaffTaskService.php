<?php

namespace App\Services\Staff;

use App\DynamicFormBuilder;
use App\Models\Staff\StaffTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffTaskService
{

    public function store(array $data): StaffTask
    {
        DB::beginTransaction();
        try {
            $form_template = $data['form_template_id'];
            if (isset($form_template)) {
                $data['form_template'] = DynamicFormBuilder::where('id', $form_template)->first()->pattern;
            }
            $data['due_date'] = Carbon::parse($data['due_date'])->format('Y-m-d');
            $data['scheduled_date'] = Carbon::parse($data['scheduled_date'])->format('Y-m-d');
            $data['scheduled_time'] = Carbon::parse($data['scheduled_time'])->format('H:i:s');
            $staffTask = StaffTask::updateOrCreate(['id' => $data['id'] ?? null], $data);
            DB::commit();
            return $staffTask;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Staff Task:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }


    public function list(array $filters = [])
    {
        $query = StaffTask::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }

        if (!empty($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['assigns', 'staffMembers', 'stafftasktype'])->where('user_id', $filters['user_id'])->latest('due_date')->paginate(10);
    }
    public function apiList(array $filters = [])
    {
        $baseQuery = StaffTask::query()
            ->where('user_id', $filters['user_id'])
            ->when($filters['home_id'] ?? null, fn($q, $v) => $q->where('home_id', $v))
            ->when($filters['due_date'] ?? null, fn($q, $v) => $q->whereDate('due_date', $v))
            ->with(['assigns', 'staffMembers'])
            ->latest('due_date');

        $pending = (clone $baseQuery)->where('status', 0)->paginate(10, ['*'], 'pending_page');
        $completed = (clone $baseQuery)->where('status', 1)->paginate(10, ['*'], 'completed_page');
        $all = (clone $baseQuery)->paginate(10, ['*'], 'all_page');

        $allArr = array();
        $pendingArr = array();
        $completedArr = array();
        $priority_array = [
            '1' => 'Low',
            '2' => 'Medium',
            '3' => 'High',
            '4' => 'Urgent',
        ];
        $status_array = [
            '0' => 'Pending',
            '1' => 'Completed',
            '2' => 'In-Progress',
            '3' => 'Resolved',
            '4' => 'Closed',
        ];
        foreach ($all as $val) {
            $allArr[] = [
                'id' => $val->id,
                'user_id' => $val->user_id,
                'task_type_id' => $val->task_type_id,
                'task_type_name' => "Assessment",
                'title' => $val->title,
                'assign_to' => $val->assign_to,
                'assign_to_user' => $val->assigns->name ?? "",
                'staff_member' => $val->staff_member,
                'staff_member_name' => $val->staffMembers->name ?? "",
                'form_template_id' => $val->form_template_id,
                'due_date' => date("d M Y", strtotime($val->due_date)),
                'scheduled_date' => $val->scheduled_date,
                'scheduled_time' => $val->scheduled_time,
                'priority' => $priority_array[$val->priority] ?? '',
                'description' => $val->description,
                'complete_notes' => $val->complete_notes ?? "",
                'status' => $status_array[$val->status] ?? '',
            ];
        }
        foreach ($completed as $val) {
            $completedArr[] = [
                'id' => $val->id,
                'user_id' => $val->user_id,
                'task_type_id' => $val->task_type_id,
                'task_type_name' => "Assessment",
                'title' => $val->title,
                'assign_to' => $val->assign_to,
                'assign_to_user' => $val->assigns->name ?? "",
                'staff_member' => $val->staff_member,
                'staff_member_name' => $val->staffMembers->name ?? "",
                'form_template_id' => $val->form_template_id,
                'due_date' => date("d M Y", strtotime($val->due_date)),
                'scheduled_date' => $val->scheduled_date,
                'scheduled_time' => $val->scheduled_time,
                'priority' => $priority_array[$val->priority] ?? '',
                'description' => $val->description,
                'complete_notes' => $val->complete_notes ?? "",
                'status' => $status_array[$val->status] ?? '',
            ];
        }
        foreach ($pending as $val) {
            $pendingArr[] = [
                'id' => $val->id,
                'user_id' => $val->user_id,
                'task_type_id' => $val->task_type_id,
                'task_type_name' => "Assessment",
                'title' => $val->title,
                'assign_to' => $val->assign_to,
                'assign_to_user' => $val->assigns->name ?? "",
                'staff_member' => $val->staff_member,
                'staff_member_name' => $val->staffMembers->name ?? "",
                'form_template_id' => $val->form_template_id,
                'due_date' => date("d M Y", strtotime($val->due_date)),
                'scheduled_date' => $val->scheduled_date,
                'scheduled_time' => $val->scheduled_time,
                'priority' => $priority_array[$val->priority] ?? '',
                'description' => $val->description,
                'complete_notes' => $val->complete_notes ?? "",
                'status' => $status_array[$val->status] ?? '',
            ];
        }
        $pagination = [
            'all_data' => [
                'total' => $all->total(),
                'per_page' => $all->perPage(),
                'current_page' => $all->currentPage(),
                'total_pages' => $all->lastPage(),
            ],
            'pending' => [
                'total' => $pending->total(),
                'per_page' => $pending->perPage(),
                'current_page' => $pending->currentPage(),
                'total_pages' => $pending->lastPage(),
            ],
            'completed' => [
                'total' => $completed->total(),
                'per_page' => $completed->perPage(),
                'current_page' => $completed->currentPage(),
                'total_pages' => $completed->lastPage(),
            ],
        ];
        return [
            'all' => $allArr,
            'completed' =>  $completedArr,
            'pending' => $pendingArr,
            'pagination' => $pagination
        ];
        // return [
        //     'completed' => (clone $baseQuery)->where('status', 1)->paginate(10, ['*'], 'completed_page'),
        //     'all' => (clone $baseQuery)->paginate(10, ['*'], 'all_page'),
        // ];
    }

    public function details($id)
    {
        return StaffTask::with([
            'assigns',
            'staffMembers'
        ])
            ->find($id);
    }
    public function add_comment($req)
    {
        try {
            DB::beginTransaction();
            $task_id = $req['id'];
            $comments = $req['comment'];
            $task_record = StaffTask::where('id', $task_id)
                ->first();

            if (!isset($task_record)) {
                return [
                    'status' => false,
                    'message' => "Data Not Found !!"
                ];
            }
            $task_record->comments = $comments;
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
    public function status_change($req)
    {
        try {
            DB::beginTransaction();
            $task_id = $req['id'];
            $status = $req['status'];
            $task_record = StaffTask::where('id', $task_id)
                ->first();

            if (!isset($task_record)) {
                return [
                    'status' => false,
                    'message' => "Data Not Found !!"
                ];
            }
            $task_record->status = $status;
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

    public function staffTaskFormSave($req)
    {
        // return $req;
        $singleData = StaffTask::find($req['staff_task_id']);
        $singleData->is_form_filled = 1;
        $singleData->form_template = json_encode($req['data']);
        $singleData->save();
        return $singleData;
    }
    public function staffTaskFormFetch($request)
    {
        $singleData = StaffTask::find($request['staff_task_id']);
        $formTemplate = DynamicFormBuilder::where('id', $singleData->form_template_id)->first();
        return ['pattern_value' => $singleData->form_template, 'pattern' => $formTemplate->pattern];
    }
}
