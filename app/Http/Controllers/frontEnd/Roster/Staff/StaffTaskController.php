<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\DynamicForm;
use App\DynamicFormBuilder;
use App\Http\Controllers\Controller;
use App\Models\Staff\StaffTask;
use App\Models\Staff\StaffTaskType;
use App\Services\Staff\StaffTaskService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StaffTaskController extends Controller
{
    protected $stafftask;
    public function __Construct(StaffTaskService $stafftask)
    {
        $this->stafftask = $stafftask;
    }
    public function index(Request $req)
    {
        $home_ids = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $userList = User::select('id', 'name', 'home_id')->where('home_id', $home_id)->latest()->get();
        $data['userList'] = $userList;
        $data['taskTypeList'] = StaffTaskType::where('home_id', $home_id)->orderBy('type')->get();
        $data['priority_array'] = [
            ['id' => '1', 'value' => 'Low'],
            ['id' => '2', 'value' => 'Medium'],
            ['id' => '3', 'value' => 'High'],
            ['id' => '4', 'value' => 'Urgent'],
        ];
        $data['formTemplate'] = DynamicFormBuilder::select('id', 'title')->where('home_id', $home_id)->orderBy('title')->get();
        return view('frontEnd.roster.staff.staff_task', $data);
    }
    public function staffTaskDetail($id = null)
    {
        if (!$id) {
            return response(view('frontEnd.error_404'), 404);
        }
        $singleData = StaffTask::find($id);
        if (!$singleData) {
            return response(view('frontEnd.error_404'), 404);
        }
        $data['singleData'] = $singleData;
        $data['formTemplate'] = DynamicFormBuilder::where('id', $singleData->form_template_id)->first();
        return view('frontEnd/roster/staff/staffTaskDetail', $data);
    }

    public function staffTaskSave(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id' => 'nullable',
                'task_type_id' => 'required',
                'title' => 'required',
                'assign_to' => 'required',
                'staff_member' => 'required',
                'form_template_id' => 'nullable',
                'due_date' => 'required|date',
                'priority' => 'required',
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
                'description' => 'nullable'
            ], [
                'task_type_id.required' => 'Please select a task type.',
                'title.required' => 'Enter title',
                'assign_to.required' => 'Please select a assignee',
                'staff_member.date' => 'Please select staff member',
                'due_date.required' => 'Please choose due date',
                'scheduled_date.required' => 'Please choose scheduled date',
                'scheduled_time.required' => 'Please choose scheduled time',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->toArray(),
                ], 422);
            }
            // return $req;
            $home_ids = Auth::user()->home_id;
            $user_id = Auth::user()->id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $reqData = $req->all();
            $reqData['home_id'] = $home_id;
            $reqData['user_id'] = $user_id;
            // return $reqData;
            $data = $this->stafftask->store($reqData);
            return response()->json([
                'status'  => true,
                'message' => 'Task added successfully !!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function staffTaskFormSave(Request $req)
    {
        // return $req;
        $data = $this->stafftask->staffTaskFormSave($req);
        if ($data) {
            return response()->json(['status' => true, 'message' => 'Form Saved Successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Form not Saved']);
    }


    public function fetch_list(Request $req)
    {
        $home_ids = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $reqData = $req->all();
        // $reqData['home_id'] = $home_id;
        $reqData['user_id'] = $user_id;
        $record = $this->stafftask->list($reqData);
        $priority_array = [
            '1' => ['Low', 'muteBadges'],
            '2' => ['Medium', 'buleBadges'],
            '3' => ['High', 'redbadges'],
            '4' => ['Urgent', 'redDarkBadges'],
        ];
        $record->getCollection()->transform(function ($q) use ($priority_array) {

            $statusText = "<span class='careBadg greenbadges'>Pending</span>";
            $priorityText = "<span class='careBadg {$priority_array[$q->priority][1]}'>{$priority_array[$q->priority][0]}</span>";

            // if ($q->date) {
            //     $dueDate = Carbon::parse($q->date)->addDays($q->frequency);
            //     $today = Carbon::today();
            //     $diff = $today->diffInDays($dueDate, false); // negative = overdue

            //     if ($diff < 0) {

            //         $statusText = "<span class='careBadg redbadges'>Overdue</span>";
            //     } elseif ($diff <= 7) {
            //         $statusText = "<span class='careBadg yellowbadges'>Due Soon</span>";
            //     } else {
            //         $statusText = "<span class='careBadg greenbadges'>On Track</span>";
            //     }
            // }

            $taskTypeName = "<span class='careBadg greenbadges'>" . $q->stafftasktype->type . "</span>";
            return [
                'id' => $q->id,
                'assign_to_name' => ucfirst($q->assigns->name) ?: '',
                'staffmembers_name' => ucfirst($q->staffMembers->name) ?: '',
                'title' => $q->title,
                // 'priority' => $priority_array[$q->priority] ?? 'N/A',
                'due_date' => $q->due_date ? Carbon::parse($q->due_date)->format('d M, Y') : 'N/A',
                'scheduled_date' => $q->scheduled_date ? $q->scheduled_date : 'N/A',
                'scheduled_time' => $q->scheduled_time ? $q->scheduled_time : 'N/A',
                'status' => $statusText,
                // 'next_due' => $q->date ? Carbon::parse($q->date)->addDays($q->frequency)->format('d M, Y') : 'N/A',
                'priorityText' => $priorityText,
                'taskTypeName' => $taskTypeName,
                'form_template_id' => $q->form_template_id,
                // 'note' => $q->note ?? "Supervisor Note",
                // 'comment' => $q->comment ?? "Supervisor Comments",
            ];
        });
        // if (!$record) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Staff task record not found.'
        //     ], 404);
        // }
        $allRecords = StaffTask::where('user_id', $user_id)->get();
        $counts = [
            'total' => $allRecords->count(),
            'pending' => 0,
            'progress' => 0,
            'completed' => 0,
            'overdue' => 0
        ];
        $overdueNames = [];
        foreach ($allRecords as $q) {
            $status = $q->status;



            if ($status == 0) {
                $counts['pending']++;
            } elseif ($status == 2) {
                $counts['progress']++;
            } elseif ($status == 1) {
                $counts['completed']++;
            }
        }
        return response()->json([
            'success'  => true,
            'message' => 'list',
            'data' => $record->items(),
            'pagination' => [
                'total' => $record->total(),
                'per_page' => $record->perPage(),
                'current_page' => $record->currentPage(),
                'total_pages' => $record->lastPage(),
            ],
            'counts' => $counts
        ]);
    }
    public function staffTaskFormFetch(Request $request)
    {
        return $this->stafftask->staffTaskFormFetch($request);
    }

    public function webview_form($staff_task_id)
    {
        $singleData = StaffTask::find($staff_task_id);
        if (!$singleData) {
            return response(view('frontEnd.error_404'), 404);
        }
        $data['singleData'] = $singleData;
        $data['formTemplate'] = DynamicFormBuilder::where('id', $singleData->form_template_id)->first();
        return view('frontEnd.roster.staff.staffTaskFormwebview', $data);
    }
    public function patterndataformio(Request $request)
    {
        $patterndata = $request->patterndata;
        $home_id = $request->home_id;
        $result = DynamicFormBuilder::where("id", $patterndata)->value("pattern");
        return $result;
    }
}
