<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUser, App\ServiceUserIncidentReport, App\Notification,  App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation;
use DB,Auth;

class IncidentController extends ServiceUserManagementController
{   

    public function index($service_user_id = null) {

        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($home_id != $su_home_id) {
            die;
        }

        // $home_id = Auth::user()->home_id;

        $this_location_id = DynamicFormLocation::getLocationIdByTag('incident_report');
        $incident_record  = DynamicForm::where('location_id',$this_location_id)
                                    //whereIn('form_builder_id',$form_bildr_ids)
                                    ->where('service_user_id',$service_user_id)
                                    ->where('is_deleted','0')
                                    ->orderBy('id','desc');

        /*$incident_record = ServiceUserIncidentReport::where('is_deleted','0')
                                    ->where('service_user_id', $service_user_id)
                                    ->where('home_id', $home_id)
                                    ->orderBy('id','desc');*/
                                    //->get();

        $pagination = '';

        if(isset($_GET['search'])) {
            if(!empty($_GET['search'])) {

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
            if($incident_form->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //incident_paginate
                $pagination .= $incident_form->links();
                $pagination .= '</div>'; 
            }
        }

        foreach ($incident_form as $key => $value) {

            $title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');
            
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
            
              echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel remove-incident-row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                            <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="hidden" name="" value="'.$value->id.'" disabled="disabled" class="edit_incident_id_'.$value->id.'">
                                    <input type="text" class="form-control" name="incident_title_name" disabled value="'.$title.' '.$start_brct.$date.' '.$end_brct.'" maxlength="255"/>
                            
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id='.$value->id.'> <span> <i class="fa fa-eye"></i> </span> View/Edit </a> </li>                                          
                                                <li> <a href="#" class="dyn_form_del_btn" id='.$value->id.'> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>                                            
                                            </ul>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>  ';
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

    public function edit_incident(Request $request) {

        $data = $request->all();

        $su_incident_id = $data['su_incident_id'];
        if($request->isMethod('post')) {
            if(isset($data['formdata'])) {
                $formdata = json_encode($data['formdata']);
            } else {
                $formdata = '';
            }

            // $home_id  = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $edit_incident = ServiceUserIncidentReport::find($su_incident_id);
            if(!empty($edit_incident)) {
                $su_home_id = ServiceUser::where('id', $edit_incident->service_user_id)->value('home_id');
                if($home_id == $su_home_id) {
                    $edit_incident->title  = $data['v-incident-r-title'];
                    $edit_incident->date   = date('Y-m-d', strtotime($data['v-incident-r-date']));
                    $edit_incident->formdata  = $formdata;

                    if($edit_incident->save()) {

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
                    } else{
                        $result['response'] = '0';
                    }
                    return $result;
                } else {
                    echo UNAUTHORIZE_ERR;
                }
            }
        }
    }

}