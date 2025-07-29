<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\FormBuilder, App\ServiceUserBmp, App\Notification, App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation, App\HomeLabel, App\CareTeamJobTitle, App\ServiceUserCareCenter, App\ServiceUserContacts, App\SocialApp, App\ServiceUserSocialApp, App\ServiceUserMoney, App\ServiceUserMoneyRequest, App\User;
use DB, Auth;
use Illuminate\Support\Facades\Session;

class BmpController extends ServiceUserManagementController
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

        // $home_id = Auth::user()->home_id;
        //in search case editing start for plan,details and review
        if (isset($_POST)) {
            $data = $_POST;
        }

        if (isset($data['su_bmp_id'])) {
            $su_bmp_ids = $data['su_bmp_id'];
            if (!empty($su_bmp_ids)) {
                foreach ($su_bmp_ids as $key => $record_id) {
                    $record = DynamicForm::find($record_id);
                    $su_home_id = ServiceUser::where('id', $record->service_user_id)->value('home_id');
                    if ($home_id == $su_home_id) {
                        $record->details = $data['edit_bmp_details'][$key];
                        //$record->plan    = $data['edit_bmp_plan'][$key];
                        //$record->review  = $data['edit_bmp_review'][$key];
                        $record->save();
                    }
                }
            }
        }
        //in search case editing end
        $this_location_id = DynamicFormLocation::getLocationIdByTag('bmp');

        //$form_bildr_ids_data = DynamicFormBuilder::select('id')->whereRaw('FIND_IN_SET(?,location_ids)',$this_location_id)->get()->toArray();
        //$form_bildr_ids = array_map(function($v) { return $v['id']; }, $form_bildr_ids_data);
        $bmp_record     = DynamicForm::where('location_id', $this_location_id)
            //whereIn('form_builder_id',$form_bildr_ids)
            ->where('service_user_id', $service_user_id)
            ->where('is_deleted', '0')
            ->orderBy('id', 'desc');
        /*$bmp_record = ServiceUserBmp::where('is_deleted','0')
                                    ->where('service_user_id', $service_user_id)
                                    ->where('home_id', $home_id)
                                    ->orderBy('id','desc');*/
        //->get();

        $pagination = '';

        if (isset($_GET['search'])) {
            if (!empty($_GET['search'])) {

                if ($_GET['searchType'] ==  1) {
                    $bmp_form = $bmp_record->where('title', 'like', '%' . $_GET['search'] . '%')->get();
                }

                if ($_GET['searchType'] ==  2) {
                    $search_date = date('Y-m-d', strtotime($_GET['search'])) . ' 00:00:00';
                    $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['search']))) . ' 00:00:00';
                    $bmp_form = $bmp_record->where('created_at', '>', $search_date)->where('created_at', '<', $search_date_next)->get();
                }

                // $bmp_form = $bmp_record->where('title','like','%'.$_GET['search'].'%')->get();

                $tick_btn_class = "search-bmp-btn search-bmp-rmp-btn";
            }
        } else {
            $bmp_form = $bmp_record->paginate(50);
            if ($bmp_form->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //bmp_paginate
                $pagination .= $bmp_form->links();
                $pagination .= '</div>';
            }

            $tick_btn_class = "sbt-edit-bmp-record submit-edit-logged-record";
        }
        // dd($bmp_form);
        foreach ($bmp_form as $key => $value) {
            $form_title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');

            $details_check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';
            //$plan_check    = (!empty($value->plan)) ? '<i class="fa fa-check"></i>' : '';
            //$review_check  = (!empty($value->review)) ? '<i class ="fa fa-check"></i>' : '';
            // if ($value->date == '') {
            //     $date = '';
            // } else {
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

            if(!empty($value->time)){
                $time = $value->time;
            } else {
                $time = '00:00';
            }

            echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel rows">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '">
                                    <input type="text" class="form-control" name="bmp_title_name" disabled value="' . $form_title . ' - ' . $value->title . ' ' . $start_brct . $date . ' : ' . $time . $end_brct . '" maxlength="255"/>
                                     
                                    <div class="input-plus color-green"> <i class="fa fa-plus"></i> 
                                    </div>   
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '"> <span> <i class="fa fa-eye"></i> </span> View</a> </li>
                                                <li> <a href="#" class="edit_bmp_details" su_bmp_id=' . $value->id . '> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li> 
                                                <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Details textarea -->
                        <div class="col-xs-12 input-plusbox form-group p-0 detail">
                            <label class="col-sm-1 col-xs-12 color-themecolor r-p-0"> Details: </label>
                            <div class="col-sm-11 r-p-0">
                                <div class="input-group">
                                    <textarea class="form-control tick_text edit_rcrd txtarea edit_bmp_details_' . $value->id . '" name="edit_bmp_details[]" disabled rows="5" value="" maxlength="1000s">' . $value->details . '</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div ' . $tick_btn_class . '">' . $details_check . '</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>  ';
        }
        echo $pagination;
    }

    /*<div class="col-xs-12 input-plusbox form-group p-0 detail">
            <label class="col-sm-1 col-xs-12 color-themecolor r-p-0"> Plan: </label>
            <div class="col-sm-11 r-p-0">
                <div class="input-group">
                    <textarea class="form-control tick_text edit_rcrd txtarea edit_bmp_plan_'.$value->id.'" disabled rows="5" name="edit_bmp_plan[]" value="" maxlength="1000">'.$value->plan.'</textarea>
                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                        <div class="tick_show sbt_btn_tick_div '.$tick_btn_class.'">'.$plan_check.'</div>
                    </div>
                <!--    <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show '.$tick_btn_class.'">'.$plan_check.'
                    </span> -->
                </div>
            </div>
        </div>
        <div class="col-xs-12 input-plusbox form-group p-0 detail">
            <label class="col-sm-1 col-xs-12 color-themecolor r-p-0"> Review: </label>
            <div class="col-sm-11 r-p-0">
                <div class="input-group">
                    <textarea class="form-control tick_text edit_rcrd txtarea edit_bmp_review_'.$value->id.'" disabled rows="5" name="edit_bmp_review[]" maxlength="1000">'.$value->review.'</textarea>
                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                        <div class="tick_show sbt_btn_tick_div '.$tick_btn_class.'">'.$review_check.'</div>
                    </div>
                <!-- <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show '.$tick_btn_class.'">'.$review_check.'
                    </span> -->
                </div>
            </div>
    </div>*/

    /*public function add_bmp(Request $request) {
        
        $data = $request->all();

        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $home_id = Auth::user()->home_id;

            $bmp                   = new ServiceUserBmp;
            $bmp->service_user_id  = $data['service_user_id'];
            $bmp->title            = $data['bmp_title_name'];
            $bmp->sent_to          = $data['sent_to'];
            //$bmp->details          = $data['plan_detail'];
            $bmp->formdata         = $formdata;
            $bmp->home_id          = $home_id;

            if($bmp->save()) {

                 //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $bmp->id;
                //$notification->event_type      = 'SU_HR';
                $notification->notification_event_type_id = '8';
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
        //echo '<pre>'; print_r($data); die;

        if (isset($data['su_bmp_id'])) {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $su_bmp_ids = $data['su_bmp_id'];
            if (!empty($su_bmp_ids)) {
                foreach ($su_bmp_ids as $key => $record_id) {
                    //$record = ServiceUserBmp::find($record_id);
                    $record = DynamicForm::find($record_id);
                    $su_home_id = ServiceUser::where('id', $record->service_user_id)->value('home_id');
                    if ($home_id == $su_home_id) {
                        $record->details = $data['edit_bmp_details'][$key];
                        // $record->plan    = $data['edit_bmp_plan'][$key];
                        // $record->review  = $data['edit_bmp_review'][$key];
                        if ($record->save()) {

                            $notification                             = new Notification;
                            $notification->service_user_id            = $record->service_user_id;
                            $notification->event_id                   = $record->id;
                            $notification->notification_event_type_id = '8';
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

    /* public function delete($su_bmp_id = null) {

        $bmp_record = ServiceUserBmp::find($su_bmp_id);

        if(!empty($bmp_record)) {

            $su_home_id = ServiceUser::where('id',$bmp_record->service_user_id)->value('home_id');

            if($su_home_id == Auth::user()->home_id){
        
                $res = ServiceUserBmp::where('id', $su_bmp_id)->update(['is_deleted' => '1']);
                echo $res;            
            }
        }
        die;
    }*/

    /*public function view_bmp($su_bmp_id = null) {

        $home_id  = Auth::user()->home_id;
        $bmp_record = ServiceUserBmp::select('su_bmp.*')
                                    ->where('su_bmp.id', $su_bmp_id)
                                    ->where('su_bmp.home_id', $home_id)
                                    ->where('su_bmp.is_deleted', '0')
                                    ->first();
                                    
        if(!empty($bmp_record)) {
            $formdata = $bmp_record->formdata;
            $form_response = FormBuilder::showFormWithValue('su_bmp', $formdata, true);

            if($form_response == true) {
                $bmp_form = $form_response['pattern'];
            } else {
                $bmp_form = '';
            }
            $result['response']       = true;
            $result['su_bmp_id']      = $bmp_record->id;
            $result['bmp_title_name'] = $bmp_record->title;
            $result['bmp_sent_to']    = $bmp_record->sent_to;
            $result['bmp_form']       = $bmp_form;
        } else {
            $result['response'] = false;
        }
         return $result;

    }

    public function edit_bmp(Request $request) {

        $data = $request->all();

        $su_bmp_id = $data['su_bmp_id'];
        if($request->isMethod('post')) {
            if(isset($data['formdata'])) {
                $formdata = json_encode($data['formdata']);
            } else {
                $formdata = '';
            }

            $home_id  = Auth::user()->home_id;
            $edit_bmp = ServiceUserBmp::find($su_bmp_id);
            if(!empty($edit_bmp)) {
                $su_home_id = ServiceUser::where('id', $edit_bmp->service_user_id)->value('home_id');
                if($home_id == $su_home_id) {
                    $edit_bmp->title     = $data['edit_bmp_title'];
                    $edit_bmp->sent_to   = $data['edit_sent_to'];
                   // $edit_bmp->details   = $data['plan_detail'];
                    $edit_bmp->formdata  = $formdata;

                    if($edit_bmp->save()) {
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

    public function form($service_user_id = null)
    {
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

            return view('frontEnd.serviceUserManagement.elements.bmp', $data);
        } else {
            return view('frontEnd.error_404');
        }
    }
  
}
