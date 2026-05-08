<?php

namespace App\Http\Controllers\frontend\roster\Client;

use App\AccessLevel;
use App\DynamicFormBuilder;
use App\Home;
use App\Http\Controllers\Controller;
use App\Models\AlertType;
use App\Models\ClientTaskCategory;
use App\Models\ClientTaskType;
use App\Models\EducationType;
use App\Models\SuEducationProfile, App\Models\SuEducationStaffAssignment, App\Models\SuEducationTask, App\Models\SuEducationAttendance, App\Models\SuEducationNote, App\Models\SuEducationResource;
use App\Models\suUserCourse;
use App\Services\Client\ClientAlertService;
use App\Services\Staff\ClientCareTaskService;
use App\Services\Staff\ClientManagementService;
use App\Services\Staff\StaffService;
use App\ServiceUser;
use Auth, DB, Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Models\ClientCarePlan;

class ClientController extends Controller
{
    protected $clientService;
    protected StaffService $staffService;
    protected $clientCareTaskService;
    protected $clientAlertService;

    public function __construct(ClientManagementService $clientService, StaffService $staffService, ClientCareTaskService $clientCareTaskService, ClientAlertService $clientAlertService)
    {
        $this->clientService = $clientService;
        $this->staffService = $staffService;
        $this->clientCareTaskService = $clientCareTaskService;
        $this->clientAlertService = $clientAlertService;
    }
    public function index()
    {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $query = ServiceUser::with(['courses', 'carers.carers_data:id,name'])->select('id', 'home_id', 'earning_scheme_label_id', 'name', 'user_name', 'phone_no', 'date_of_birth', 'child_type', 'room_type', 'current_location', 'street', 'care_needs', 'suFundingType', 'status', 'is_deleted')
            ->where(['home_id' => $home_id, 'is_deleted' => 0]);
        $data['child'] = $query->get();
        $data['active_child_count'] = (clone $query)->where('status', 1)->get();
        $data['inactive_child_count'] = (clone $query)->where('status', 0)->get();
        // echo "<pre>";print_r($data['child']);die;
        return view('frontEnd.roster.client.client', $data);
    }

    // public function client_details($client_id)
    // {
    //     $home_ids = Auth::user()->home_id;
    //     $ex_home_ids = explode(',', $home_ids);
    //     $home_id = $ex_home_ids[0];

    //     $clientData = $this->child_courses($client_id);
    //     $responseData = $clientData->getData(true);
    //     $data['clientDetails'] = $responseData['data'];
    //     if ($data['clientDetails']['status'] == 1) {
    //         $status = 'Active';
    //     } else if ($data['clientDetails']['status'] == 0) {
    //         $status = 'Inactive';
    //     } else {
    //         $status = 'Archived';
    //     }
    //     $data['status'] = $status;
    //     $requestData['user_id'] = Auth::user()->id;
    //     $data['client_id'] = $client_id;
    //     $data['alert_type'] = AlertType::where('status', 1)->get();
    //     $data['task_category'] = clientTaskCategory::where('status', 1)->get();
    //     $data['education_type'] = EducationType::where('status', 1)->get();
    //     $data['dynamic_form_builder'] = DynamicFormBuilder::getFormList

    //     // Education Data
    //     $data['education_profile'] = SuEducationProfile::where('service_user_id', $client_id)->where('status', 1)->first();
    //     $data['assigned_staff'] = SuEducationStaffAssignment::with('staff')->where('service_user_id', $client_id)->where('status', 1)->get();
    //     $data['users'] = DB::table('user')->where('home_id', $home_id)->where('is_deleted', 0)->get()->toArray();
    //     $data['service_user_id'] = $client_id;

    //     $data['education_tasks'] = SuEducationTask::with('staff')->where('service_user_id', $client_id)->orderBy('due_date', 'asc')->get();
    //     $data['education_attendance'] = SuEducationAttendance::with('staff')->where('service_user_id', $client_id)->orderBy('date', 'desc')->get();
    //     $data['education_notes'] = SuEducationNote::with('staff')->where('service_user_id', $client_id)->orderBy('created_at', 'desc')->get();
    //     $data['education_resources'] = SuEducationResource::where('service_user_id', $client_id)->orderBy('created_at', 'desc')->get();


    //     return view('frontEnd.roster.client.client_details', $data);
    // }

    public function client_details($client_id)
    {
        $clientData = $this->child_courses($client_id);
        $responseData = $clientData->getData(true);
        $data['clientDetails'] = $responseData['data'];
        if ($data['clientDetails']['status'] == 1) {
            $status = 'Active';
        } else if ($data['clientDetails']['status'] == 0) {
            $status = 'Inactive';
        } else {
            $status = 'Archived';
        }
        $data['status'] = $status;
        $requestData['user_id'] = Auth::user()->id;
        $data['client_id'] = $client_id;
        $data['alert_type'] = AlertType::where('status', 1)->get();
        $data['task_category'] = clientTaskCategory::where('status', 1)->get();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $data['home_details'] = Home::find($home_id);
        $data['access_level'] = AccessLevel::select('id', 'name')->where('home_id', $home_id)->where('is_deleted', 0)->get();

        // Education Data
        $data['education_profile'] = SuEducationProfile::where('service_user_id', $client_id)->where('status', 1)->first();
        $data['assigned_staff'] = SuEducationStaffAssignment::with('staff')->where('service_user_id', $client_id)->where('status', 1)->get();
        $data['users'] = DB::table('user')->where('home_id', $home_id)->where('is_deleted', 0)->get()->toArray();
        $data['service_user_id'] = $client_id;

        $data['education_tasks'] = SuEducationTask::with('staff')->where('service_user_id', $client_id)->orderBy('due_date', 'asc')->get();
        $data['education_attendance'] = SuEducationAttendance::with('staff')->where('service_user_id', $client_id)->orderBy('date', 'desc')->get();
        $data['education_notes'] = SuEducationNote::with('staff')->where('service_user_id', $client_id)->orderBy('created_at', 'desc')->get();
        $data['education_resources'] = SuEducationResource::where('service_user_id', $client_id)->orderBy('created_at', 'desc')->get();

        return view('frontEnd.roster.client.client_details', $data);
    }


    public function child_courses($childId)
    {
        // $all_courses = suUserCourse::where('su_user_id',$childId)->get();
        $all_courses = ServiceUser::with(['courses', 'carers'])->where('id', $childId)->first();
        return response()->json(['success' => true, 'data' => $all_courses]);
    }
    public function client_delete(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:service_user,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        $user = ServiceUser::find($request->id);
        $user->is_deleted = 1;
        $user->save();
        Session::flash('success', 'Client deleted successfully done.');
        return [
            'success' => true,
            'message' => 'Client deleted successfully done.'
        ];
    }
    public function client_search(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $query = ServiceUser::select('id', 'home_id', 'earning_scheme_label_id', 'name', 'user_name', 'phone_no', 'date_of_birth', 'child_type', 'room_type', 'current_location', 'street', 'care_needs', 'suFundingType', 'status', 'is_deleted')
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%')
                    ->orWhere('user_name', 'like', '%' . $request->user_name . '%');
            })
            ->where(['home_id' => $home_id, 'is_deleted' => 0]);
        $query_AllSql =  (clone $query)->get();
        $query_ActiveSql =  (clone $query)->where('status', 1)->get();
        $query_InactiveSql = (clone $query)->where('status', 0)->get();

        $allHtml_data = $this->html_data_prepaire($query_AllSql);
        $activeHtml_data = $this->html_data_prepaire($query_ActiveSql);
        $InactiveHtml_data = $this->html_data_prepaire($query_InactiveSql);
        return response()->json(['success' => true, 'allHtml_data' => $allHtml_data, 'activeHtml_data' => $activeHtml_data, 'InactiveHtml_data' => $InactiveHtml_data, 'query_AllSqlData' => $query_AllSql, 'query_ActiveSqlData' => $query_ActiveSql, 'query_InactiveSql' => $query_InactiveSql]);
    }
    public function html_data_prepaire($query)
    {
        $html_data = '';
        foreach ($query as $childVal) {
            $html_data .= '
            <div class="col-md-4">                                 
                <div class="profile-card">
                    <div class="card-header">
                        <div class="user">
                            <div class="avatar">' . strtoupper(substr($childVal->name, 0, 1)) . '</div>
                            <div class="info">
                                <div class="name"><a href="' . url("roster/client-details/" . $childVal->id) . '"> ' . $childVal->name . '</a></div>
                                <div class="role">' . $childVal->suFundingType . '</div>
                            </div>
                        </div>';
            if ($childVal->status == 1) {
                $html_data .= '<span class="status greenShowbtn">Active</span>';
            } else {
                $html_data .= '<span class="status radShowbtn">Inactive</span>';
            }
            $html_data .= '</div>
                    <div class="details">
                        <div class="item">
                            <i class="fa-solid fa-phone"></i> <span>' . $childVal->phone_no . '</span>
                        </div>
                        
                        <div class="item">
                            <i class="fa-solid fa-location-dot"></i> <span>' . $childVal->street . '</span>
                        </div>
                    </div>
                    <div class="section care-needs">
                        <div class="label">
                            <i class="fa-regular fa-heart"></i>
                            Care Needs:
                        </div>

                        <div class="sectionCarer">

                            <div class="tags">';
            $moreNeeds = 0;
            if (!empty($childVal->care_needs)) {
                $ex = explode(',', $childVal->care_needs);
                $moreNeeds = count($ex) - 5;
                for ($i = 0; $i < 5; $i++) {
                    if (!empty($ex[$i])) {
                        $html_data .= '<span>' . $ex[$i] . '</span>';
                    }
                }
            }
            if ($moreNeeds > 0) {
                $html_data .= '<button class="care-more">+' . $moreNeeds . ' more</button>';
            }
            $html_data .= '</div>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button class="view" type="button" onclick="redirectLocation(' . $childVal->id . ')">
                            <i class="fa-regular fa-eye"></i>
                            View Details
                        </button>
                        <button class="edit" type="button" data-toggle="modal" data-target="#addServiceUserModal" data-child_id="' . $childVal->id . '">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        <button class="delete client_delete" type="button" data-child_id="' . $childVal->id . '">
                            <i class="fa-regular fa-trash-can"></i>
                        </button>
                    </div>
                </div>                                
            </div>';
        }
        return $html_data;
    }
    public function care_task_add(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $task_id = $request->task_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $data['task_type'] = ClientTaskType::where('status', 1)->get();
        $data['task_category'] = ClientTaskCategory::where('status', 1)->get();
        $data['child'] = ServiceUser::select('id', 'home_id', 'earning_scheme_label_id', 'name', 'user_name', 'phone_no', 'date_of_birth', 'child_type', 'room_type', 'current_location', 'street', 'care_needs', 'suFundingType', 'status', 'is_deleted')
            ->where(['home_id' => $home_id, 'is_deleted' => 0, 'status' => 1])->get();
        $data['carer'] = $this->staffService->activeStaff($home_id)->get();
        $data['care_plans'] = ClientCarePlan::where(['home_id' => $home_id, 'status' => 1])->get();

        $clientCareTask = '';
        if ($task_id) {
            $clientCareTask = $this->clientCareTaskService->details($task_id);
        }
        $data['clientCareTask'] = $clientCareTask;
        $data['client_id'] = $request->client_id;
        // echo "<pre>";print_r($data['clientCareTask']);die;
        return view('frontEnd.roster.client.care_task_form', $data);
    }
    public function get_carer_shifts(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $requestData = $request->all();
        $requestData['home_id'] = $home_id;
        $scheduled_shifts = $this->clientCareTaskService->shiftCheck($requestData);
        return response()->json(['success' => true, 'message' => 'Schedule Shift', 'data' => $scheduled_shifts]);
    }
    public function care_task_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        if (!empty($request->id)) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:client_care_tasks,id',
                'task_title' => 'required',
                'task_category_id' => 'required',
                'priority' => 'required',
                'client_id' => 'required',
                'care_plan_id' => 'required',
                'frequency' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'task_title' => 'required',
                'task_category_id' => 'required',
                'priority' => 'required',
                'client_id' => 'required',
                'care_plan_id' => 'required',
                'frequency' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $requestData['safeguarding'] = $request->safeguarding ?? 0;
            $requestData['two_person'] = $request->two_person ?? 0;
            $requestData['ppe_required'] = $request->ppe_required ?? 0;
            $requestData['shift_id'] = $request->shift_id ?? null;
            // echo "<pre>";print_r($requestData);die;
            $clientCareTask = $this->clientCareTaskService->store($requestData);
            Session::flash('success', 'Client Care Task save successfully done.');
            return response()->json(['success' => true, 'message' => "Client Care Task saved successfully", 'data' => $clientCareTask]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function care_task_list(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $requestData = $request->all();
        // $requestData['user_id'] = Auth::user()->id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $requestData['home_id'] = $home_id;
        $clientCareTask = $this->clientCareTaskService->list($requestData);
        // echo "<pre>";print_r($clientCareTask);die;
        return response()->json([
            'success' => true,
            'message' => 'Client Care Task List',
            'data' => $clientCareTask->items(),
            'total' => $clientCareTask->total(),
            'pagination' => [
                'next_page_url' => $clientCareTask->nextPageUrl(),
                'prev_page_url' => $clientCareTask->previousPageUrl(),
            ]
        ]);
    }
    public function care_task_delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_care_tasks,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $clientCareTask = $this->clientCareTaskService->delete($request->id);
            Session::flash('success', 'Client Care Task deleted successfully done.');
            return response()->json(['success' => true, 'message' => "Client Care Task deleted successfully", 'data' => $clientCareTask]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function medication_log_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        if (!empty($request->id)) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:medication_logs,id',
                'medication_name' => 'required',
                'dosage' => 'required',
                'administrator_date' => 'required',
                'status' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'medication_name' => 'required',
                'dosage' => 'required',
                'administrator_date' => 'required',
                'status' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            // echo "<pre>";print_r($requestData);die;
            $clientMediLog = $this->clientService->store($requestData);
            return response()->json(['success' => true, 'message' => "Medication Log saved successfully", 'data' => $clientMediLog]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function medication_log_list(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $requestData = $request->all();
        $requestData['user_id'] = Auth::user()->id;
        $medicationLogs = $this->clientService->list($requestData);
        return response()->json([
            'success' => true,
            'message' => 'Medication Log List',
            'data' => $medicationLogs->items(),
            'total' => $medicationLogs->total(),
            'pagination' => [
                'next_page_url' => $medicationLogs->nextPageUrl(),
                'prev_page_url' => $medicationLogs->previousPageUrl(),
            ]
        ]);
    }
    public function client_alert_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        if (!empty($request->id)) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:client_alerts,id',
                'alert_type_id' => 'required',
                'severity' => 'required',
                'alert_title' => 'required',
                'description' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'alert_type_id' => 'required',
                'severity' => 'required',
                'alert_title' => 'required',
                'description' => 'required',
            ]);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            // 
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            if ($request->has('expiry_date') && !empty($data['expiry_date'])) {
                $requestData['expiry_date'] = Carbon::parse($request->expiry_date)->format('Y-m-d');
            }
            // echo "<pre>";print_r($requestData);die;
            $clientAlert = $this->clientAlertService->store($requestData);
            return response()->json(['success' => true, 'message' => "Client Alert saved successfully", 'data' => $clientAlert]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function client_alert_type(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $requestData = $request->all();
        $requestData['user_id'] = Auth::user()->id;
        $alerts = $this->clientAlertService->list($requestData);
        return response()->json([
            'success' => true,
            'message' => 'Alerts List',
            'data' => $alerts->items(),
            'total' => $alerts->total(),
            'pagination' => [
                'next_page_url' => $alerts->nextPageUrl(),
                'prev_page_url' => $alerts->previousPageUrl(),
            ]
        ]);
    }
    public function alert_increase_acknowledge(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_alerts,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $alert_increase_acknowledge = $this->clientAlertService->alert_increase_acknowledge($request->id);
            return response()->json(['success' => true, 'message' => "Acknowledge Increased", 'data' => $alert_increase_acknowledge]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function client_alert_resolve(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_alerts,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $resolve = $this->clientAlertService->client_alert_resolve($request->id);
            return response()->json(['success' => true, 'message' => "Alert Resolved", 'data' => $resolve]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function client_alert_archived(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:client_alerts,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try {
            $archived = $this->clientAlertService->client_alert_archived($request->id);
            return response()->json(['success' => true, 'message' => "Alert archived successfully", 'data' => $archived]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function alert_increase_all_acknowledge(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        try {
            $alert_increase_all_acknowledge = $this->clientAlertService->alert_increase_all_acknowledge();
            return response()->json(['success' => true, 'message' => "All Acknowledge Increased", 'data' => $alert_increase_all_acknowledge]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong", 'data' => $e->getMessage()]);
        }
    }
    public function carer_list(Request $request)
    {
        $home_id = explode(',', Auth::user()->home_id)[0];
        $carer = $this->staffService->activeStaff($home_id)->get();
        return response()->json(['status' => true, 'message' => 'Carer List', 'data' => $carer]);
    }
    public function client_active_status(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|exists:service_user,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        $data = ServiceUser::find($req->id);
        $data->status = $req->status;
        $data->save();
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
