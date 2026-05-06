<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth, DB, Session;
use Illuminate\Support\Facades\Validator;
use App\Services\Staff\StaffReportIncidentService;
use App\ServiceUser;
use App\Models\IncidentType;
use App\Models\Staff\SafeguardingType;

class IncidentManagementController extends Controller
{
    protected $incidentService;

    public function __construct(StaffReportIncidentService $incidentService)
    {
        $this->incidentService = $incidentService;
    }
    public function index(Request $request)
    {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $data['client'] = ServiceUser::select('id', 'home_id', 'earning_scheme_label_id', 'name', 'user_name', 'phone_no', 'date_of_birth', 'child_type', 'room_type', 'current_location', 'street', 'care_needs', 'status', 'is_deleted')
            ->where(['home_id' => $home_id, 'is_deleted' => 0, 'status' => 1])->get();
        $data['incident_type'] = IncidentType::where('status', 1)->get();
        $data['safeguard_type'] = SafeguardingType::where('status', 1)->get();
        // echo "<pre>";print_r($data['incidents']);die;
        return view('frontEnd.roster.incident_management.incident', $data);
    }
    public function ai_prevention()
    {
        return view('frontEnd.roster.incident_management.ai_prevention');
    }
    public function incident_report_details($id)
    {
        $report_details = $this->incidentService->report_details($id);
        $severityhtml = '';
        if ($report_details->severity_id == 1) {
            $severityhtml = '<span class="careBadg">Low</span>';
        } else if ($report_details->severity_id == 2) {
            $severityhtml = '<span class="careBadg yellowBadges">Medium</span>';
        } else if ($report_details->severity_id == 3) {
            $severityhtml = '<span class="careBadg highBadges">High</span>';
        } else if ($report_details->severity_id == 4) {
            $severityhtml = '<span class="careBadg redbadges">Critical</span>';
        }
        $statushtml = '';
        if ($report_details->status == 1) {
            $statushtml = '<span class="careBadg muteBadges">Reported</span>';
        } else if ($report_details->status == 2) {
            $statushtml = '<span class="careBadg muteBadges">Under Investigation</span>';
        } else if ($report_details->status == 3) {
            $statushtml = '<span class="careBadg muteBadges">Resoled</span>';
        } else if ($report_details->status == 4) {
            $statushtml = '<span class="careBadg muteBadges">Closed</span>';
        }
        $data['severity'] = $severityhtml;
        $data['statushtml'] = $statushtml;
        $data['report_details'] = $report_details;

        // echo "<pre>";print_r($data);die;
        return view('frontEnd.roster.incident_management.incident_report_details', $data);
    }
    public function incident_report_save(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'incident_type_id'  => 'required|integer',
            'severity_id'       => 'required|integer',
            'client_id'         => 'required|integer',
            'date_time'         => 'required|date',
            'location'          => 'required|string',
            'what_happened'     => 'required|string',
            'immediate_action'  => 'required|string',
        ]);

        if ($validator->fails()) {
            Session::flash('error', $validator->errors()->first());
            return redirect()->back();
        }
        try {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $incident = $this->incidentService->store($requestData);
            Session::flash('success', 'Incident Report saved successfully');
            return redirect()->back();
        } catch (Exception $e) {
            Session::flash('error', $e->getMessage());
            return redirect()->back();
        }
    }
    public function incidentReportLoadData(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $requestData = $request->all();
        $requestData['home_id'] = $home_id;
        $incidents = $this->incidentService->list($requestData);
        return response()->json([
            'success' => true,
            'message' => 'Incident Report List',
            'data' => $incidents->items(),
            'pagination' => [
                'next_page_url' => $incidents->nextPageUrl(),
                'prev_page_url' => $incidents->previousPageUrl(),
            ]
        ]);
    }
}
