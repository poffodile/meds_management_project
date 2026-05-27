<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\Staff\ClientCareTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class ClientCareTaskApi extends Controller
{
    protected $clientCareTaskService;

    public function __construct(ClientCareTaskService $clientCareTaskService)
    {
        $this->clientCareTaskService = $clientCareTaskService;
    }
    public function index(Request $req)
    {
          $validator = Validator::make($req->all(), [
            'child_id'  => 'required',
            'user_id'  => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $requestData['carer_id'] = $req->user_id;
        $requestData['client_id'] = $req->child_id;
        $requestData['isGrouped'] = 1;
        $clientCareTask = $this->clientCareTaskService->list($requestData);


       
        $pending = (clone $clientCareTask)->where('status', 0)->latest()->paginate(10, ['*'], 'pending_page');
        $completed = (clone $clientCareTask)->where('status', 1)->latest('updated_at')->paginate(10, ['*'], 'completed_page');
        $all = (clone $clientCareTask)->latest()->paginate(10, ['*'], 'all_page');


        $pendingArr = [];
        $completedArr = [];
        $allArr = [];
        foreach ($pending as $item) {
            $pendingArr[] = [
                'id' => $item->id,
                'home_id' => $item->home_id,
                'user_id' => $item->user_id,
                'task_title' => $item->task_title??"",
                'task_category' => $item->clientTaskCategorys->title??"",
                'task_type' => $item->clientTaskType->title ??"",
                'priority' => $item->priority,
                'frequency' => $item->frequency,
                'duration' => "{$item->duration} minutes",
                'date' => date("d M Y", strtotime($item->scheduled_date)),
                'status' => "Pending",
                'assign_to' => $item->carer->name ?? "",
            ];
        }
        foreach ($completed as $item) {
            $completedArr[] = [
                'id' => $item->id,
                'home_id' => $item->home_id,
                'user_id' => $item->user_id,
                'task_title' => $item->task_title,
                'task_category' => $item->clientTaskCategorys->title,
                'task_type' => $item->clientTaskType->title,
                'priority' => $item->priority,
                'frequency' => $item->frequency,
                'duration' => "{$item->duration} minutes",
                'date' => date("d M Y", strtotime($item->scheduled_date)),
                'status' => "Completed",
                'assign_to' => $item->carer->name ?? "",
            ];
        }
        foreach ($all as $item) {
            $allArr[] = [
                'id' => $item->id,
                'home_id' => $item->home_id,
                'user_id' => $item->user_id,
                'task_title' => $item->task_title,
                'task_category' => $item->clientTaskCategorys->title,
                'task_type' => $item->clientTaskType->title,
                'priority' => $item->priority,
                'frequency' => $item->frequency,
                'duration' => "{$item->duration} minutes",
                'date' => date("d M Y", strtotime($item->scheduled_date)),
                'status' => $item->status == 1 ? "Completed" : "Pending",
                'assign_to' => $item->carer->name ?? "",
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
        // echo "<pre>";print_r($clientCareTask);die;
        return response()->json([
            'success' => true,
            'message' => 'Client Care Task List',
            'pending_data' => $pendingArr,
            'completed_data' => $completedArr,
            'all_data' => $allArr,
            'pagination' => $pagination
        ]);
    }
     public function details(Request $req)
    {
        $validator = Validator::make($req->all(), ['id' => 'required|exists:client_care_tasks,id']);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        $val = $this->clientCareTaskService->details($req->id);
        $location_array = [
            '1' => 'Home',
            '2' => 'Community',
            '3' => 'Facility',
        ];
        $status_array = [
            '0' => 'Pending',
            '1' => 'Completed',
            '2' => 'In-Progress',
            '3' => 'Resolved',
            '4' => 'Closed',
        ];
        $allArr = [
            'id' => $val->id,
            'user_id' => $val->user_id,
            'task_type_id' => $val->task_type_id,
            'client_id' => $val->client_id,
            'task_category_id' => $val->task_category_id,
            'carer_id' => $val->carer_id,
            'task_type_name' => $val->clientTaskType->title ?? "",
            'task_category_name' => $val->clientTaskCategorys->title ?? "",
            'title' => $val->task_title,
            'client_name' => $val->clients->name ?? "",
            'carer_name' => $val->carers->name ?? "",
            'location' => $location_array[$val->location],
            'scheduled_date' => date("d M Y", strtotime($val->scheduled_date)),
            'scheduled_time' => date("H:i", strtotime($val->scheduled_time)),
            'priority' => $val->priority ?? '',
             'duration' => "{$val->duration} minutes",
            'visit_title' => $val->visit_id == 1 ? "Personal Care" : "Spot Check",
            'status' => $status_array[$val->status] ?? '',
            'statusCode' => $val->status,
            'safeguarding' => $val->safeguarding,
            'two_person' => $val->two_person,
            'ppe_required' => $val->ppe_required,
            'frequency' => $val->frequency,
            'task_tag' => $val->task_tag ?? "",
            'comments' => $val->comment ?? '',
            'risk_notes' => $val->risk_notes ?? '',
            'task_description' => $val->task_description ?? '',
        ];
        return response()->json([
            'status'  => true,
            'message' => 'Task Details',
            'data' => $allArr,
        ]);
    }


    public function add_comment(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->all(),
                [
                    'id' => 'required',
                    'comment' => 'required'
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $this->clientCareTaskService->add_comment($req->all());
            if ($data['status']) {
                return response()->json([
                    'status'  => true,
                    'message' => $data['message']
                ]);
            }
            return response()->json([
                'status'  => false,
                'message' => $data['message']
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function status_change(Request $req)
    {
        try {
            $validator = Validator::make(
                $req->all(),
                [
                    'id' => 'required',
                    'status' => 'required'
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
              Log::error('Error delete Client Care Task:', [
                 'error' => '',
                 'data'  => $req->all()
           ]);
            $data = $this->clientCareTaskService->status_change($req->all());
            if ($data['status']) {
                return response()->json([
                    'status'  => true,
                    'message' => $data['message']
                ]);
            }
            return response()->json([
                'status'  => false,
                'message' => $data['message']
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
