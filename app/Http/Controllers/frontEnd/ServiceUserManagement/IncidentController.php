<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\ServiceUserIncidentReport, App\Notification,  App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation, App\HomeLabel, App\CareTeamJobTitle, App\ServiceUserCareCenter, App\ServiceUserContacts, App\SocialApp, App\ServiceUserSocialApp, App\User;
use DB, Session, Auth;
use Carbon\Carbon;

class IncidentController extends ServiceUserManagementController
{
    public function index($service_user_id = null, Request $request)
    {
        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        if ($home_id != $su_home_id) {
            die;
        }

        $today = Carbon::now()->format('Y-m-d');

        $this_location_id = DynamicFormLocation::getLocationIdByTag('incident_report');
        $incident_record  = DynamicForm::where('location_id', $this_location_id)
            ->select('dynamic_form.*', 'dynamic_form_builder.title as form_name')
            ->join('dynamic_form_builder', 'dynamic_form_builder.id', '=', 'dynamic_form.form_builder_id')
            //whereIn('form_builder_id',$form_bildr_ids)
            ->where('service_user_id', $service_user_id)
            // ->whereDate('dynamic_form.created_at', '=', $today)
            ->where('is_deleted', '0')
            ->orderBy('id', 'desc');

        /*$incident_record = ServiceUserIncidentReport::where('is_deleted','0')
                                    ->where('service_user_id', $service_user_id)
                                    ->where('home_id', $home_id)
                                    ->orderBy('id','desc');*/
        //->get();

        $pagination = '';

        if (isset($_GET['search'])) {
            if (!empty($_GET['search'])) {

                if ($_GET['searchType'] ==  1) {
                    $incident_form = $incident_record->where('title', 'like', '%' . $_GET['search'] . '%')->get();
                }

                if ($_GET['searchType'] ==  2) {
                    $search_date = date('Y-m-d', strtotime($_GET['search'])) . ' 00:00:00';
                    $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['search']))) . ' 00:00:00';
                    $incident_form = $incident_record->where('created_at', '>', $search_date)->where('created_at', '<', $search_date_next)->get();
                }

                // $incident_form = $incident_record->where('title','like','%'.$_GET['search'].'%')->get();
            }
        } else {
            $incident_form = $incident_record->paginate(50);
            if ($incident_form->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //incident_paginate
                $pagination .= $incident_form->links();
                $pagination .= '</div>';
            }
        }

        if ($request->isMethod('post') && $request->input('filter') == 1) {

            // if ($request->filled('staff_member')) {
            //     $bmp_record->where('user_id', $request->input('staff_member'));
            // }

            if ($request->filled('service_user')) {
                $incident_record->where('service_user_id', $request->input('service_user'));
            }

            if ($request->filled('category_id') && $request->input('category_id') !== 'all') {
                $incident_record->where('category_id', $request->input('category_id'));
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = Carbon::parse($request->input('start_date'))->startOfDay();
                $end   = Carbon::parse($request->input('end_date'))->endOfDay();

                $incident_record->whereBetween('dynamic_form.created_at', [$start, $end]);
            }

            if ($request->filled('keyword')) {
                $keyword = $request->input('keyword');
                $incident_record->where(function ($query) use ($keyword) {
                    $query->where('dynamic_form.title', 'like', "%{$keyword}%")
                        ->orWhere('dynamic_form.title', 'like', "%{$keyword}%");
                });
            }

            $incident_form = $incident_record->get(); // Get filtered data

        } else {

            $today = Carbon::today();
            $incident_record->whereDate('dynamic_form.created_at', $today);

            // No filters — get paginated result
            $incident_form = $incident_record->paginate();
        }

        $loop = 1;
        $colors = ['#8fd6d6', '#f57775', '#bda4ec', '#fed65a', '#81b56b'];
        shuffle($colors);
        foreach ($incident_form as $key => $value) {

            // if($value->date == '' ) {  
            //     $date = '';
            // }  else {
            //     $date = date('d-m-Y', strtotime($value->date));
            // }

            $date = !empty($value->date) ? date('d-m-Y', strtotime($value->date)) : '';
            $time = !empty($value->time) ? $value->time : '';

            $datetime = ($date || $time) ? trim($date . ' : ' . $time, ' :') : ' 00-00-0000 : 00-00';

            $color = $colors[$key % count($colors)];

            if ($loop % 2 == 0) {
                echo '<div class="col-md-6 col-sm-6 col-xs-6 cog-panel remove-incident-row rmpTimelineright">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr rightSideInput rmpTimeRit">
                                 <span class="timLineDate"> ' . $datetime . '</span>
                                 <span class="arrow"></span>
                                 <div class="rmpWithPlusInput">
                                    <input type="hidden" name="" value="' . $value->id . '" disabled="disabled" class="edit_incident_id_' . $value->id . '">
                                    <input type="text" class="form-control" style="background-color: ' . $color . ';" name="incident_title_name" disabled value="' . $value->form_name . ' : ' . $value->title . '" maxlength="255"/>
                            
                                    <span class="ritOrdring two input-group-addon cus-inpt-grp-addon clr-blue settings" style="background-color: ' . $color . ';">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id=' . $value->id . '> <span> <i class="fa fa-eye"></i> </span> View/Edit </a> </li>                                          
                                                <li> <a href="#" class="dyn_form_del_btn" id=' . $value->id . '> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>                                            
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>  ';
            } else {
                echo  '
                    <div class="col-md-6 col-sm-6 col-xs-6 cog-panel remove-incident-row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr timelineInput rmpTimeLft">
                                <span class="arrow"></span>
                                <span class="timLineDate"> ' . $datetime . '</span>
                                    <div class="rmpWithPlusInput">
                                        <input type="hidden" name="" value="' . $value->id . '" disabled="disabled" class="edit_incident_id_' . $value->id . '">
                                        <input type="text" class="form-control" style="background-color: ' . $color . ';" name="incident_title_name" disabled value="' . $value->form_name . ' - ' . $value->title . '" maxlength="255"/>
                                
                                        <span class="input-group-addon cus-inpt-grp-addon clr-blue settings" style="background-color: ' . $color . ';">
                                            <i class="fa fa-cog"></i>
                                            <div class="pop-notifbox">
                                                <ul class="pop-notification" type="none">
                                                    <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id=' . $value->id . '> <span> <i class="fa fa-eye"></i> </span> View/Edit </a> </li>                                          
                                                    <li> <a href="#" class="dyn_form_del_btn" id=' . $value->id . '> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>                                            
                                                </ul>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> ';
            }
            $loop++;
        }
        echo $pagination;
    }

    /*public function add_incident(Request $request) {
        
        $data = $request->all();

        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $home_id = Auth::user()->home_id;

            $incident                   = new ServiceUserIncidentReport;
            $incident->service_user_id  = $data['service_user_id'];
            $incident->title            = $data['incident_report_title'];
            $incident->date             = date('Y-m-d', strtotime($data['incident_report_date']));
            //$incident->details          = $data['plan_detail'];
            $incident->formdata         = $formdata;
            $incident->home_id          = $home_id;

            if($incident->save()) {

                 //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $incident->id;
                //$notification->event_type      = 'SU_HR';
                $notification->notification_event_type_id = '10';
                $notification->event_action               = 'ADD';    
                $notification->home_id                    = Auth::user()->home_id;
                $notification->user_id                    = Auth::user()->id;                  
                $notification->save();

                $result['response'] = '1';
            } else{
                $result['response'] = '0';
            }
            return $result;
        }
    }


    public function delete($su_incident_id = null) {

        $incident_record = ServiceUserIncidentReport::find($su_incident_id);

        if(!empty($incident_record)) {

            $su_home_id = ServiceUser::where('id',$incident_record->service_user_id)->value('home_id');

            if($su_home_id == Auth::user()->home_id){
        
                $res = ServiceUserIncidentReport::where('id', $su_incident_id)->update(['is_deleted' => '1']);
                echo $res;            
            }
        }
        die;
    }

    public function view_incident($su_incident_id = null) {

        $home_id  = Auth::user()->home_id;

        $incident_record = ServiceUserIncidentReport::select('su_incident_report.*')
                                    ->where('su_incident_report.id', $su_incident_id)
                                    ->where('su_incident_report.home_id', $home_id)
                                    ->where('su_incident_report.is_deleted', '0')
                                    ->first();
                                    
        if(!empty($incident_record)) {
            $formdata = $incident_record->formdata;
            $form_response = FormBuilder::showFormWithValue('incident_report', $formdata, true);
            if($form_response == true) {
                $incident_form = $form_response['pattern'];
            } else {
                $incident_form = '';
            }
            $result['response'] = true;
            $result['su_incident_id']      = $incident_record->id;
            $result['incident_title_name'] = $incident_record->title;
            $result['v_incident_r_date']   = date('d-m-Y',strtotime($incident_record->date));
            $result['incident_form']       = $incident_form;
        } else {
            $result['response'] = false;
        }
        return $result;

    }*/

    public function edit_incident(Request $request)
    {

        $data = $request->all();

        $su_incident_id = $data['su_incident_id'];
        if ($request->isMethod('post')) {
            if (isset($data['formdata'])) {
                $formdata = json_encode($data['formdata']);
            } else {
                $formdata = '';
            }

            // $home_id  = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $edit_incident = ServiceUserIncidentReport::find($su_incident_id);
            if (!empty($edit_incident)) {
                $su_home_id = ServiceUser::where('id', $edit_incident->service_user_id)->value('home_id');
                if ($home_id == $su_home_id) {
                    $edit_incident->title  = $data['v-incident-r-title'];
                    $edit_incident->date   = date('Y-m-d', strtotime($data['v-incident-r-date']));
                    $edit_incident->formdata  = $formdata;

                    if ($edit_incident->save()) {

                        //saving notification start
                        $notification                             = new Notification;
                        $notification->service_user_id            = $edit_incident->service_user_id;
                        $notification->event_id                   = $edit_incident->id;
                        //$notification->event_type      = 'SU_HR';
                        $notification->notification_event_type_id = '10';
                        $notification->event_action               = 'EDIT';
                        $notification->home_id                    = $home_id;
                        $notification->user_id                    = Auth::user()->id;
                        $notification->save();

                        $result['response'] = '1';
                    } else {
                        $result['response'] = '0';
                    }
                    return $result;
                } else {
                    echo UNAUTHORIZE_ERR;
                }
            }
        }
    }

    public function form($service_user_id = null)
    {
        $data['service_user_id'] = $service_user_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $data['labels'] = HomeLabel::getLabels($home_id);
        $data['service_users'] = ServiceUser::where('home_id', $home_id)->get()->toArray();
        $data['dynamic_forms'] = DynamicFormBuilder::getFormList();

        // update notify
        $patient = DB::table('service_user')->where('id', $service_user_id)->where('is_deleted', '0')->first();
        $data['patient'] = $patient;

        if (!empty($patient)) {
            if ($patient->home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $risks = DB::table('risk')->select('id', 'description', 'icon', 'status')
                ->where('home_id', $home_id)
                ->where('is_deleted', '0')
                ->get();
            $daily_score   = DB::table('daily_record_score')->get();
            $care_team = DB::table('su_care_team')->select('id', 'job_title_id', 'name', 'email', 'phone_no', 'image', 'address')->where('service_user_id', $service_user_id)->where('is_deleted', '0')->orderBy('id', 'desc')->get();

            $care_history = DB::table('su_care_history')->select('id', 'title', 'date', 'description')->where('service_user_id', $service_user_id)->where('is_deleted', '0')->orderBy('date', 'desc')->get();

            $file_category = DB::table('file_category')->select('id', 'name')->where('is_deleted', '0')->orderBy('name', 'asc')->get();

            //get coordnate for map
            $current_location = $patient->current_location;

            //removing new line
            $pattern = '/[^a-zA-Z0-9]/u';
            $current_location = preg_replace($pattern, ' ', (string) $current_location);
            $coordinates = ServiceUser::getLongLat($current_location);

            $latitude = (isset($coordinates['results']['0']['geometry']['location']['lat'])) ? $coordinates['results']['0']['geometry']['location']['lat'] : '';
            $longitude = (isset($coordinates['results']['0']['geometry']['location']['lng'])) ? $coordinates['results']['0']['geometry']['location']['lng'] : '';
            //get coordnate for map end

            /*$daily_records_options = DB::table('daily_record')
                                ->where('home_id',$home_id)
                                ->where('status','1')
                                ->orderBy('id','desc')
                                ->get();*/

            //living skill option
            /*$living_skill_options = DB::table('living_skill')
                                        ->where('home_id',$home_id)
                                        ->where('status','1')
                                        ->where('is_deleted','0')
                                        ->orderBy('id','desc')
                                        ->get();

            $education_record_options = DB::table('education_record')
                                        ->select('id','description')
                                        ->where('home_id', $home_id)
                                        ->where('status','1')
                                        ->where('is_deleted','0')
                                        ->orderBy('id','desc')
                                        ->get();
            //echo '<pre>'; print_r($education_record_options); die;
            $mfc_options = DB::table('mfc')
                            ->select('id','description')
                            ->where('home_id', $home_id)
                            ->where('status','1')
                            ->where('is_deleted','0')
                            ->orderBy('id','desc')
                            ->get();
            
            //service_users list for bmp-rmp
            $service_users = ServiceUser::select('id','name')
                                ->where('home_id',$home_id)
                                ->where('status','1')
                                ->where('is_deleted','0')
                                ->get()
                                ->toArray();*/

            //getting form patterns
            $form_pattern['bmp_rmp'] = '';
            $form_pattern['risk'] = '';
            $form_pattern['su_rmp'] = '';
            $form_pattern['su_bmp'] = '';
            $form_pattern['su_mfc'] = '';
            $form_pattern['incident_report'] = '';

            $service_users = ServiceUser::where('home_id', $home_id)->get()->toArray();
            $dynamic_forms = DynamicFormBuilder::getFormList();

            /*$form     =  FormBuilder::showForm('bmp_rmp');
            $response = $form['response'];
            //echo '<pre>'; print_r($service_users); die;
            if($response == true){
                $form_pattern['bmp_rmp'] = $form['pattern'];
            } else{
                $form_pattern['bmp_rmp'] = '';
            }

            $form     =  FormBuilder::showForm('change_risk');
            $response = $form['response'];
            if($response == true){
                $form_pattern['risk'] = $form['pattern']; 
            } else{
                $form_pattern['risk'] = '';
            }

            $form     =  FormBuilder::showForm('su_rmp');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_rmp'] = $form['pattern']; 
            } else{
                $form_pattern['su_rmp'] = '';
            }
            
            $form     =  FormBuilder::showForm('su_bmp');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_bmp'] = $form['pattern']; 
            } else{
                $form_pattern['su_bmp'] = '';
            }

            $form     =  FormBuilder::showForm('su_mfc');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_mfc'] = $form['pattern']; 
            } else{
                $form_pattern['su_mfc'] = '';
            }
            //echo $form_pattern['su_mfc']; die;

            $form     =  FormBuilder::showForm('incident_report');
            $response = $form['response'];
            if($response == true){
                $form_pattern['incident_report'] = $form['pattern']; 
                //echo "<pre>"; print_r($form_pattern['incident_report']); die;
            } else{
                $form_pattern['incident_report'] = '';
            }
*/
            $notifications = Notification::getSuNotification($service_user_id, '', '', 6, $home_id);

            $afc_status = ServiceUser::get_afc_status($service_user_id);

            $labels = HomeLabel::getLabels($home_id);

            //pending rmp and incident reports notifications
            /*$pending_notif = DB::table('su_risk')
                                ->select('su_risk.id','su_risk.rmp_id','su_risk.incident_report_id',
                                    'risk.description as risk_name')
                                ->where('su_risk.service_user_id',$service_user_id)
                                ->join('risk', 'su_risk.risk_id','=', 'risk.id')                                            
                                //->leftJoin('su_rmp', 'su_risk.id', '=', 'su_rmp.su_risk_id')
                                //->leftJoin('su_incident_report', 'su_risk.id', '=', 'su_incident_report.su_risk_id')
                                ->orderBy('su_risk.id','desc')
                                ->get();
            echo '<pre>'; print_r($pending_notif); die;*/
            /*$pending_notif = DB::table('su_risk')
                                ->select('su_risk.id as su_risk_id','su_rmp.id as su_rmp_id', 'su_incident_report.id as su_incident_record_id','risk.description as risk_name')
                                ->where('su_risk.service_user_id',$service_user_id)
                                ->join('risk', 'su_risk.risk_id','=', 'risk.id')                                            
                                ->leftJoin('su_rmp', 'su_risk.id', '=', 'su_rmp.su_risk_id')
                                ->leftJoin('su_incident_report', 'su_risk.id', '=', 'su_incident_report.su_risk_id')
                                ->orderBy('su_risk.id','desc')
                                ->get();*/

            //echo '<pre>'; print_r($pending_notif); die;

            //$su_log_book_category = DB::table('su_log_book_category')->get();
            $care_team_job_title = CareTeamJobTitle::where('is_deleted', '0')
                ->where('home_id', $home_id)
                ->get();
            $su_in_danger        = ServiceUserCareCenter::where('service_user_id', $service_user_id)->where('care_type', 'D')->count();
            $su_req_cb           = ServiceUserCareCenter::where('service_user_id', $service_user_id)->where('care_type', 'R')->count();
            $su_contact          = ServiceUserContacts::where('service_user_id', $service_user_id)->where('is_deleted', '0')->get();

            $social_app     = SocialApp::select('id', 'name', 'icon')->where('is_deleted', '0')->get()->toArray();
            $su_social_app  = ServiceUserSocialApp::select('id', 'social_app_id', 'value')
                ->where('su_social_app.service_user_id', $service_user_id)
                ->get()
                ->toArray();
            $social_app_val = array();
            foreach ($su_social_app as $key => $value) {
                $social_app_val[$value['social_app_id']]['id']    = $value['id'];
                $social_app_val[$value['social_app_id']]['value'] = $value['value'];
                // $social_app_val[$value['social_app_id']]['icon'] = $value['icon'];
            }
            // echo "<pre>"; print_r($social_app);
            // echo "<pre>"; print_r($su_social_app);
            // echo "<pre>"; print_r($social_app_val); 
            // die;

            //Child money
            $my_money = $this->my_money($service_user_id);
            // echo "<pre>"; print_r($my_money); die;
            $noti_data = array();
            if (Session::has('noti_data')) {
                $noti_data = Session::get('noti_data');
                Session::forget('noti_data');
            }

            $users  = User::select('id', 'name', 'email', 'image', 'phone_no')
                ->where('home_id', $home_id)
                ->where('is_deleted', '0')
                ->get()
                ->toArray();

            $data['service_user_id'] = $service_user_id;

            return view('frontEnd.serviceUserManagement.elements.incident_report', $data);
        } else {
            return view('frontEnd.error_404');
        }
    }
}
