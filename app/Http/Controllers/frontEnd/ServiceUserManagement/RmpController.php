<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\FormBuilder, App\ServiceUserRmp, App\Notification, App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation, App\HomeLabel, App\CareTeamJobTitle, App\ServiceUserCareCenter, App\ServiceUserContacts, App\SocialApp, App\ServiceUserSocialApp, App\User;
use DB, Auth;
use Illuminate\Support\Facades\Session;


class RmpController extends ServiceUserManagementController
{

    public function index($service_user_id = null)
    {

        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        if ($home_id != $su_home_id) {
            die;
        }

        // $home_id   = Auth::user()->home_id;

        //in search case editing start for plan,details and review
        if (isset($_POST)) {
            $data = $_POST;
        }

        if (isset($data['su_rmp_id'])) {

            $su_rmp_ids = $data['su_rmp_id'];
            // dd($su_rmp_ids);

            if (!empty($su_rmp_ids)) {
                foreach ($su_rmp_ids as $key => $record_id) {
                    // $record = ServiceUserRmp::find($record_id);
                    $record = DynamicForm::find($record_id);
                    $su_home_id = ServiceUser::where('id', $record->service_user_id)->value('home_id');
                    if ($home_id == $su_home_id) {
                        $record->details = $data['edit_rmp_details'][$key];
                        // $record->plan    = $data['edit_rmp_plan'][$key];
                        // $record->review  = $data['edit_rmp_review'][$key];
                        $record->save();
                    }
                }
            }
        }
        //in search case editing end
        // $rmp_title = ServiceUserRmp::where('is_deleted','0')
        //                             ->where('service_user_id', $service_user_id)
        //                             ->where('home_id', $home_id)
        //                             ->orderBy('id','desc')
        //                             ->get();

        $this_location_id = DynamicFormLocation::getLocationIdByTag('rmp');
        $rmp_title        = DynamicForm::where('location_id', $this_location_id)
            //whereIn('form_builder_id',$form_bildr_ids)
            ->where('service_user_id', $service_user_id)
            ->where('is_deleted', '0')
            ->orderBy('id', 'desc');

        // dd($rmp_title);

        $pagination = '';

        if (isset($_GET['search'])) {
            if (!empty($_GET['search'])) {
                // $rmp_form_title = $rmp_title->where('title', 'like', '%'.$_GET['search'].'%')->get();
                if ($_GET['searchType'] ==  1) {
                    $rmp_form_title = $rmp_title->where('title', 'like', '%' . $_GET['search'] . '%')->get();
                }
                if ($_GET['searchType'] ==  2) {
                    $search_date = date('Y-m-d', strtotime($_GET['search'])) . ' 00:00:00';
                    $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['search']))) . ' 00:00:00';
                    $rmp_form_title = $rmp_title->where('created_at', '>', $search_date)->where('created_at', '<', $search_date_next)->get();
                }
                $tick_btn_class = "search-rmp-btn search-bmp-rmp-btn";
            }
        } else {
            // $rmp_form_title = $rmp_title->get();
            $rmp_form_title = $rmp_title->paginate(50);
            if ($rmp_form_title->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //rmp_paginate
                $pagination .= $rmp_form_title->links();
                $pagination .= '</div>';
            }
            $tick_btn_class = "sbt-edit-rmp-record submit-edit-logged-record";
        }
        $loop = 1;

        foreach ($rmp_form_title as $key => $value) {

            $form_title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');

            $details_check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';
            //$plan_check    = (!empty($value->plan)) ? '<i class="fa fa-check"></i>' : '';
            //$review_check  = (!empty($value->review)) ? '<i class ="fa fa-check"></i>' : '';

            // if($value->date == '' ) {  
            //     $date = '';
            // }  else {
            //     $date = date('d-m-Y', strtotime($value->date));
            // }

            if ($value->created_at == '') {
                $date = '';
            } else {
                $date = \Carbon\Carbon::parse($value->created_at)->format('d-m-Y');
            }

            if ((!empty($date)) || (!empty($value->time))) {
                $start_brct = '(';
                $end_brct = ')';
            } else {
                $start_brct = '';
                $end_brct = '';
            }

            if (!empty($value->time)) {
                $time = $value->time;
            } else {
                $time = '00:00';
            }

            if ($loop % 2 == 0) {
                echo   '<div class="col-md-6 col-sm-6 col-xs-6 cog-panel delete-row rows rmpTimelineright">
                        <div class="form-group p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr rightSideInput rmpTimeRit">
                                <span class="timLineDate">29-07-2025 - 2</span>
                                <div>
                                <input type="hidden" name="su_rmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_rmp_id_' . $value->id . '">
                                <input type="text" class="form-control" name="rmp_title_name" disabled value="' . $form_title . ' - ' . $value->title . ' ' . $start_brct . $date . ' : ' .
                    $time . ' ' . $end_brct . '" maxlength="255"/>
                                <div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>   
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '" > <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                                <li> <a href="#" class="edit_rmp_details" su_rmp_id="' . $value->id . '"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                                <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                                </div>

                            </div>
                        </div>

                        <!-- Details textarea -->
                        <div class="col-xs-12 input-plusbox form-group p-0 detail">
                            <label class="col-sm-1 col-xs-12 color-themecolor r-p-0"> Details: </label>
                            <div class="col-sm-11 r-p-0">
                                <div class="input-group">
                                    <textarea class="form-control tick_text edit_rcrd txtarea edit_rmp_details_' . $value->id . '" name="edit_rmp_details[]" disabled rows="5" value="" maxlength="1000">' . $value->details . '</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div ' . $tick_btn_class . '">' . $details_check . '</div>
                                    </div>
                                   <!--  <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show ' . $tick_btn_class . '">' . $details_check . '
                                    </span> -->
                                </div>
                            </div>
                        </div>
                    </div>  ';
            } else {
                echo   '<div class="col-md-6 col-sm-6 col-xs-6 cog-panel delete-row rows rmpTimelineright">
                        <div class="form-group p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr rightSideInput rmpTimeLft">
                                <span class="timLineDate">29-07-2025 - 2</span>
                                <div>
                                <input type="hidden" name="su_rmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_rmp_id_' . $value->id . '">
                                <input type="text" class="form-control" name="rmp_title_name" disabled value="' . $form_title . ' - ' . $value->title . ' ' . $start_brct . $date . ' : ' .
                    $time . ' ' . $end_brct . '" maxlength="255"/>
                                <div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>   
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '" > <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                                <li> <a href="#" class="edit_rmp_details" su_rmp_id="' . $value->id . '"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                                <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                                </div>

                            </div>
                        </div>

                        <!-- Details textarea -->
                        <div class="col-xs-12 input-plusbox form-group p-0 detail">
                            <label class="col-sm-1 col-xs-12 color-themecolor r-p-0"> Details: </label>
                            <div class="col-sm-11 r-p-0">
                                <div class="input-group">
                                    <textarea class="form-control tick_text edit_rcrd txtarea edit_rmp_details_' . $value->id . '" name="edit_rmp_details[]" disabled rows="5" value="" maxlength="1000">' . $value->details . '</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div ' . $tick_btn_class . '">' . $details_check . '</div>
                                    </div>
                                   <!--  <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show ' . $tick_btn_class . '">' . $details_check . '
                                    </span> -->
                                </div>
                            </div>
                        </div>
                    </div>  ';
            }
            $loop++;
        }
        echo $pagination;
    }

    /*public function add_rmp(Request $request) {
        
        $data = $request->all();

        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $home_id = Auth::user()->home_id;

            $rmp                   = new ServiceUserRmp;
            $rmp->service_user_id  = $data['service_user_id'];
            $rmp->title            = $data['rmp_title_name'];
            $rmp->sent_to          = $data['sent_to'];
            //$rmp->details          = $data['plan_detail'];
            $rmp->formdata         = $formdata;
            $rmp->home_id          = $home_id;

            if($rmp->save()) {

                   //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $rmp->id;
                //$notification->event_type      = 'SU_HR';
                $notification->notification_event_type_id = '9';
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
    }*/

    public function edit(Request $request)
    {

        $data = $request->all();
        if (isset($data['su_rmp_id'])) {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $su_rmp_ids = $data['su_rmp_id'];
            if (!empty($su_rmp_ids)) {
                foreach ($su_rmp_ids as $key => $record_id) {
                    //$record = ServiceUserRmp::find($record_id);
                    $record = DynamicForm::find($record_id);
                    $su_home_id = ServiceUser::where('id', $record->service_user_id)->value('home_id');
                    if ($home_id == $su_home_id) {
                        $record->details = $data['edit_rmp_details'][$key];
                        // $record->plan    = $data['edit_rmp_plan'][$key];
                        // $record->review  = $data['edit_rmp_review'][$key];
                        //$record->save();
                        if ($record->save()) {

                            $notification                             = new Notification;
                            $notification->service_user_id            = $record->service_user_id;
                            $notification->event_id                   = $record->id;
                            $notification->notification_event_type_id = '9';
                            $notification->event_action               = 'EDIT';
                            $notification->home_id                    = $home_id;
                            $notification->user_id                    = Auth::user()->id;
                            $notification->save();
                        }
                    }
                }
            }
        }
        $service_user_id = $record->service_user_id;

        $res = $this->index($service_user_id);
        echo $res;
    }


    /*public function view_rmp($su_rmp_id = null) {

        $home_id  = Auth::user()->home_id;

        $rmp_record = DB::table('su_rmp as surmp')
                        ->select('surmp.id as su_rmp_id','surmp.service_user_id as service_user_id','surmp.title','surmp.formdata', 'surmp.sent_to')
                        ->where('surmp.id', $su_rmp_id)
                        ->where('surmp.home_id', $home_id)
                        ->where('surmp.is_deleted', '0')
                        ->first();

        if(!empty($rmp_record)) {
            $formdata = $rmp_record->formdata;
            $form_response = FormBuilder::showFormWithValue('su_rmp', $formdata,true);

            if($form_response == true) {
                $rmp_form = $form_response['pattern'];
            } else {
                $rmp_form = '';
            }
            $result['response']        = true;
            $result['su_rmp_id']       = $rmp_record->su_rmp_id;
            $result['rmp_title_name']  = $rmp_record->title;
            $result['rmp_sent_to']     = $rmp_record->sent_to;
            $result['rmp_form']        = $rmp_form;
        } else {
            $result['response'] = false;
        }
        return $result;

    }


    public function delete($su_rmp_id = null) {

        $rmp_record = ServiceUserRmp::find($su_rmp_id);

        if(!empty($rmp_record)) {

            $su_home_id = ServiceUser::where('id',$rmp_record->service_user_id)->value('home_id');

            if($su_home_id == Auth::user()->home_id){
        
                $res = ServiceUserRmp::where('id', $su_rmp_id)->update(['is_deleted' => '1']);
                echo $res;            
            }
        }
        die;
    }

    public function edit_rmp(Request $request) {
        
        $data = $request->all();

        $su_rmp_id = $data['su_rmp_id'];

        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            
            $home_id = Auth::user()->home_id;
            $edit_rmp = ServiceUserRmp::find($su_rmp_id);
            if(!empty($edit_rmp)) {

                $su_home_id = ServiceUser::where('id',$edit_rmp->service_user_id)->value('home_id');
                if($su_home_id ==  $home_id) {
                    $edit_rmp->title            = $data['edit_rmp_title'];
                    $edit_rmp->sent_to          = $data['edit_sent_to'];
                    $edit_rmp->formdata         = $formdata;

                    if($edit_rmp->save()) {
                        $result['response'] = '1';
                        //$result['rmp_list'] = $this->index($edit_rmp->service_user_id);
                        
                    } else{
                        $result['response'] = '0';
                    }
                    return $result;
                } else {
                    echo UNAUTHORIZE_ERR;
                }
            }
        }
    }*/

    /* only color change and only update
    public function change_risk_status(Request $request){

        $data = $request->all();    
        if($request->isMethod('post')) {
            $risk = ServiceUserRisk::where('service_user_id',$data['service_user_id'])
                        ->where('risk_id',$data['risk_id'])
                        ->first();

            if(empty($risk)) {
                $risk = new ServiceUserRisk;
                $risk->service_user_id = $data['service_user_id'];
                $risk->risk_id = $data['risk_id'];
            } 

            $risk->status = $data['status'];
            if($data['status'] == 0){
                ServiceUserRisk::where('service_user_id',$data['service_user_id'])
                        ->where('risk_id',$data['risk_id'])
                        ->delete();
                echo '1';                
            } else{

                if($risk->save()) {
                    echo '1';
                } else{
                    echo '0';
                } 
            }
            die;
        }
    }	*/

    public function form($service_user_id = null)
    {

        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $data['labels'] = HomeLabel::getLabels($home_id);
        $data['service_users'] = ServiceUser::where('home_id', $home_id)->get()->toArray();
        $data['dynamic_forms'] = DynamicFormBuilder::getFormList();
        $data['service_user_id'] = $service_user_id;

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

            return view('frontEnd.serviceUserManagement.elements.rmp', $data);
            // return view('frontEnd.serviceUserManagement.elements.bmp', $data);
        } else {
            return view('frontEnd.error_404');
        }
    }
}
