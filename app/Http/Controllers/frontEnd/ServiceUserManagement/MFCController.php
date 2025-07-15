<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\MFC, App\ServiceUserMFC, App\FormBuilder, App\ServiceUser, App\EarningScheme, App\Notification, App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation;
use DB,Auth;

class MFCController extends ServiceUserManagementController
{
    // Listing in LOGGED & SEARCH MFC 
    public function index($service_user_id = null) {   
        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($home_id != $su_home_id){
            die; 
        }
        
        //in search case editing start
        if(isset($_POST)) {
            $data = $_POST;
        }
        //in search case editing end
        //$dyn_form = DynamicForm::where('form_builder_id','1')->get();

        //get dynamic forms id of mfc
        $form_bildr_ids_data = DynamicFormBuilder::select('id')->whereRaw('FIND_IN_SET(5,location_ids)')->get()->toArray();
        $form_bildr_ids      = array_map(function($v) { return $v['id']; }, $form_bildr_ids_data);
        $mfc_records         = DynamicForm::whereIn('form_builder_id',$form_bildr_ids)
                                            ->where('is_deleted','0')
                                            ->where('service_user_id',$service_user_id);
                                            // ->get()
                                            // ->toArray();

        //echo '<pre>'; print_r($form_bildr_ids);
        
        /*$mfc_records = ServiceUserMFC::select('su_mfc.*','mfc.description')
                                    ->join('mfc','su_mfc.mfc_id','=','mfc.id')
                                    ->where('su_mfc.is_deleted','0')
                                    ->where('su_mfc.service_user_id',$service_user_id);*/

        $today = date('Y-m-d 00:0:00');
    
        if(isset($_GET['search'])) {

            $mfc_search_type = $_GET['mfc_search_type'];
            if($mfc_search_type == 'title'){
            
                //$mfc_records = $mfc_records->where('mfc.description','like','%'.$_GET['search'].'%');
                $mfc_records = $mfc_records->where('title','like','%'.$_GET['search'].'%');
            
            } else{

                $search_date = date('Y-m-d',strtotime($_GET['mfc_date'])).' 00:00:00';
                $search_date_next = date('Y-m-d',strtotime('+1 day', strtotime($_GET['mfc_date']))).' 00:00:00';

                $mfc_records = $mfc_records->where('created_at','>',$search_date)
                                        ->where('created_at','<',$search_date_next);
            }
        }
        
        $mfc_records  = $mfc_records->orderBy('id','desc')
                                    ->orderBy('created_at','desc');
        $pagination = '';

        // if it is search case then no pagination should be there
        if(isset($_GET['search'])) {                            
            $mfc_records = $mfc_records->get();
        } else{
            $mfc_records = $mfc_records->paginate(50);
            if($mfc_records->links() != '') {
                $pagination = '</div><div class="mfc_paginate m-l-15 position-botm">'.$mfc_records->links().'</div>'; 
            }
        }
        
        //get first date to show in heading 
        if(!$mfc_records->isEmpty()){
            $pre_date = date('y-m-d',strtotime($mfc_records['0']->created_at));
        }
        
        //echo '<pre>'; print_r($mfc_records); die;
        
        foreach ($mfc_records as $key => $value) {

            $title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');


            $first = 0;

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

            $mfc_rcrd_date = date('Y-m-d',strtotime($value->created_at));

            if($mfc_rcrd_date != $pre_date){
                $pre_date = $mfc_rcrd_date; 
           
                echo '</div>
                <div class="daily-rcd-head">
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row ">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <a  class="date-tab">
                                <span class="pull-left">
                                   '.date('d F Y',strtotime($mfc_rcrd_date)).'
                                </span>
                               <i class="fa fa-angle-right pull-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="daily-rcd-content">';
            }
            else{  }

            echo '
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row rows">
                    
                    <div class="form-group col-md-9 col-sm-9 col-xs-12 r-p-0">
                        <div class="input-group popovr">
                            <input type="text" name="edit_su_record_desc[]" class="form-control cus-control edit_record_desc_'.$value->id.' edit_mfc_rcrd"  disabled  value="'.ucfirst($title).' '.$start_brct.$date.' '.$end_brct.'" maxlength="255"/>';
                             
                            if(!empty($value->info)){
                                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
                            }
                              echo '<input type="hidden" name="edit_su_record_id[]" value="'.$value->id.'" disabled="disabled" class="edit_record_id_'.$value->id.'" />
                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">';
                                        /*if(isset($add_new_case)) { 
                                        echo '<li> <a href="#" su_mfc_id="'.$value->id.'" class="edit_record_btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>';
                                        }*/
                                        echo '<li> <a href="#" id="'.$value->id.'" class="dyn-form-view-data"> <span class="color-red"> <i class="fa fa-eye clr-blue"></i> </span> View/Edit </a> 
                                        </li>
                                        <li> <a href="#" id="'.$value->id.'" class="dyn_form_del_btn"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="input-plusbox form-group col-xs-12 p-0 detail">
                        <label class="cus-label color-themecolor"> Details: </label>
                        <div class="cus-input">
                            <div class="input-group">
                                <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_detail_'.$value->id.' edit_mfc_rcrd " value="" maxlength="1000">'.$value->info.'</textarea>
                                <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show"></span>
                            </div>
                        </div>
                    </div>
                </div>
                ';
        }

        echo $pagination;
    }
    
    public function add(Request $request) {
        
        $data = $request->all();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $su_home_id = ServiceUser::where('id', $data['service_user_id'])->value('home_id');

            if($home_id != $su_home_id)  {
                echo '0'; die;
            }

            $su_mfc                   = new ServiceUserMFC;
            $su_mfc->service_user_id  = $data['service_user_id'];
            $su_mfc->home_id          = $home_id;
            $su_mfc->mfc_id           = $data['mfc_id'];
            $su_mfc->formdata         = $formdata;

            if($su_mfc->save()) {

                //saving notification start
                // $notification                             = new Notification;
                // $notification->service_user_id            = $data['service_user_id'];
                // $notification->event_id                   = $su_mfc->id;
                // //$notification->event_type      = 'SU_HR';
                // $notification->notification_event_type_id = '5';
                // $notification->event_action               = 'ADD';    
                // $notification->home_id                    = Auth::user()->home_id;
                // $notification->user_id                    = Auth::user()->id;                  
                // $notification->save();

                $result['response'] = '1';
            } else{
                $result['response'] = '0';
            }
            return $result;
        }
    }

    public function edit(Request $request, $su_mfc_id=null)  {
        
        $data=$request->all();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($request->isMethod('post'))  {

            if(isset($data['formdata']))  {
                $formdata=json_encode($data['formdata']);
            }
            else{ 
                $formdata = ''; 
            }
        }

        // $home_id = Auth::user()->home_id;
        $su_home_id = ServiceUser::where('id', $data['service_user_id'])->value('home_id');
        
        if($su_home_id != $home_id) {
            echo 0; die;
        }
        
        $edit_su_mfc_rcrd = ServiceUserMFC::where('id', $data['su_mfc_id'])->first();
        $edit_su_mfc_rcrd->formdata = $formdata;

        if($edit_su_mfc_rcrd->save())   {
            $updated_earning_star_id = EarningScheme::updateEarning($data['service_user_id']);

            //saving notification start
            // $notification                             = new Notification;
            // $notification->service_user_id            = $data['service_user_id'];
            // $notification->event_id                   = $updated_earning_star_id;
            // $notification->notification_event_type_id = '5';
            // $notification->event_action               = 'EDIT';
            // $notification->home_id                    = Auth::user()->home_id;
            // $notification->user_id                    = Auth::user()->id;
            // $notification->save();
            //saving notification end

            $result['response'] = '1';    
        }   else {
            $result['response'] = '0';
        }
        return $result;
    }

    /*public function view_mfc_rcrd($su_mfc_id = null) {

        $home_id  = Auth::user()->home_id;

        $mfc_rcrd = ServiceUserMFC::select('su_mfc.*', 'mfc.id', 'mfc.description')
                                    ->join('mfc', 'mfc.id', 'su_mfc.mfc_id')
                                    ->where('su_mfc.id', $su_mfc_id)
                                    ->first();

        if(!empty($mfc_rcrd)) {

            $formdata = $mfc_rcrd->formdata;

            $mfc_form_response = FormBuilder::showFormWithValue('su_mfc',$formdata,true);

            if($mfc_form_response == true) {
                $su_mfc_form = $mfc_form_response['pattern'];
            } else{
                $su_mfc_form = '';
            }

            $result['response']             = true;
            $result['su_mfc_id']            = $mfc_rcrd->id;
            $result['su_mfc_description']   = $mfc_rcrd->description;
            $result['su_mfc_form']          = $su_mfc_form;   

        } else {
            $result['response']         = false;            
        }
        return $result;
    }*/

    /*public function delete($su_mfc_id){

        if(!empty($su_mfc_id)){

            $mfc_record = ServiceUserMFC::where('id', $su_mfc_id)->first();

            if(!empty($mfc_record)){
             
                $su_home_id = ServiceUser::where('id',$mfc_record->service_user_id)->value('home_id');
                if(Auth::user()->home_id != $su_home_id){
                    echo '0'; die; 
                }

                $mfc_record->is_deleted = 1;
                if($mfc_record->save()){
                    echo '1';
                } else{
                    echo '0';
                }
            }
        }
        die;
    }*/

}