<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\FormBuilder, App\ServiceUserBmp, App\Notification, App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation;
use DB, Auth;

class BmpController extends ServiceUserManagementController
{

    public function index($service_user_id = null)
    {

        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
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

        foreach ($bmp_form as $key => $value) {
            $title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');

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

            echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel rows">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '">
                                    <input type="text" class="form-control" name="bmp_title_name" disabled value="' . $title . ' ' . $start_brct . $date . ' ' . $end_brct . '" maxlength="255"/>
                                     
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
            $home_id=$ex_home_ids[0];

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
}
