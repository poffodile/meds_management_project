<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\FormBuilder, App\ServiceUserRmp, App\Notification, App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation;
use DB,Auth;

class RmpController extends ServiceUserManagementController
{   

    public function index($service_user_id = null) {

        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($home_id != $su_home_id){
            die; 
        }
                 
        // $home_id   = Auth::user()->home_id;

        //in search case editing start for plan,details and review
        if(isset($_POST)) {
            $data = $_POST;
        }

        if(isset($data['su_rmp_id'])) {

            $su_rmp_ids = $data['su_rmp_id'];
                if(!empty($su_rmp_ids)) { 
                    foreach($su_rmp_ids as $key => $record_id) { 
                        // $record = ServiceUserRmp::find($record_id);
                        $record = DynamicForm::find($record_id);
                        $su_home_id = ServiceUser::where('id',$record->service_user_id)->value('home_id');
                        if($home_id == $su_home_id) {
                            $record->details = $data['edit_rmp_details'][$key];
                            // $record->plan    = $data['edit_rmp_plan'][$key];
                            // $record->review  = $data['edit_rmp_review'][$key];
                            $record->save();
                        }
                    }
                }
        }
         //in search case editing end
        /*$rmp_title = ServiceUserRmp::where('is_deleted','0')
                                    ->where('service_user_id', $service_user_id)
                                    ->where('home_id', $home_id)
                                    ->orderBy('id','desc');*/
                                    //->get();

        $this_location_id = DynamicFormLocation::getLocationIdByTag('rmp');
        $rmp_title        = DynamicForm::where('location_id',$this_location_id)
                                    //whereIn('form_builder_id',$form_bildr_ids)
                                    ->where('service_user_id',$service_user_id)
                                    ->where('is_deleted','0')
                                    ->orderBy('id','desc');

        $pagination = '';

        if(isset($_GET['search'])) { 
            if(!empty($_GET['search'])) { 
                // $rmp_form_title = $rmp_title->where('title', 'like', '%'.$_GET['search'].'%')->get();
                if ($_GET['searchType'] ==  1) {
                    $rmp_form_title = $rmp_title->where('title', 'like', '%'.$_GET['search'].'%')->get();
                } if($_GET['searchType'] ==  2) {
                    $search_date = date('Y-m-d', strtotime($_GET['search'])) . ' 00:00:00';
                    $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['search']))) . ' 00:00:00';
                    $rmp_form_title = $rmp_title->where('created_at', '>', $search_date)->where('created_at', '<', $search_date_next)->get();
                }
                $tick_btn_class = "search-rmp-btn search-bmp-rmp-btn";
            }
        } else {
            // $rmp_form_title = $rmp_title->get();
            $rmp_form_title = $rmp_title->paginate(50);
            if($rmp_form_title->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //rmp_paginate
                $pagination .= $rmp_form_title->links();
                $pagination .= '</div>'; 
            }
            $tick_btn_class = "sbt-edit-rmp-record submit-edit-logged-record";
        }

        foreach ($rmp_form_title as $key => $value) {

            $title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');
            
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

            if((!empty($date)) || (!empty($value->time))) {
                $start_brct = '(';
                $end_brct = ')';
            } else {
                $start_brct = '';
                $end_brct = '';
            }

            echo   '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel delete-row rows">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                <input type="hidden" name="su_rmp_id[]" value="'.$value->id.'" disabled="disabled" class="edit_rmp_id_'.$value->id.'">
                                <input type="text" class="form-control" name="rmp_title_name" disabled value="'.$title.' '.$start_brct.$date.' '.$end_brct.'" maxlength="255"/>
                                <div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>   
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="'.$value->id.'" > <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                                <li> <a href="#" class="edit_rmp_details" su_rmp_id="'.$value->id.'"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                                <li> <a href="#" class="dyn_form_del_btn" id="'.$value->id.'"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
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
                                    <textarea class="form-control tick_text edit_rcrd txtarea edit_rmp_details_'.$value->id.'" name="edit_rmp_details[]" disabled rows="5" value="" maxlength="1000">'.$value->details.'</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div '.$tick_btn_class.'">'.$details_check.'</div>
                                    </div>
                                   <!--  <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show '.$tick_btn_class.'">'.$details_check.'
                                    </span> -->
                                </div>
                            </div>
                        </div>
                    </div>  ';
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

    public function edit(Request $request) {

        $data = $request->all();
        if(isset($data['su_rmp_id'])) {
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];

            $su_rmp_ids = $data['su_rmp_id'];
                if(!empty($su_rmp_ids)) { 
                    foreach($su_rmp_ids as $key => $record_id) { 
                        //$record = ServiceUserRmp::find($record_id);
                        $record = DynamicForm::find($record_id);
                        $su_home_id = ServiceUser::where('id',$record->service_user_id)->value('home_id');
                        if($home_id == $su_home_id) {
                            $record->details = $data['edit_rmp_details'][$key];
                            // $record->plan    = $data['edit_rmp_plan'][$key];
                            // $record->review  = $data['edit_rmp_review'][$key];
                            //$record->save();
                            if($record->save()) {

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

}