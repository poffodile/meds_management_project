<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUserDailyRecord, App\DailyRecordScores, App\DailyRecord, App\Notification, App\ServiceUserEarningDailyPoints, App\ServiceUserEarningStar, App\ServiceUser, App\EducationRecord, App\ServiceUserEducationRecord, App\EarningScheme, App\ServiceUserMoney;
use DB, Auth;

class EducationRecordController extends ServiceUserManagementController
{
    public function index($service_user_id = null)
    {   
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
            $this->_edit($data);
        }

        $su_edu_records = ServiceUserEducationRecord::select('su_education_record.*','eslr.description','eslr.amount')
                                    ->join('earning_scheme_label_records as eslr','su_education_record.education_record_id','=','eslr.id')
                                    // ->join('education_record as er','su_education_record.education_record_id','=','er.id')
                                    ->where('eslr.status','1')
                                    ->where('su_education_record.is_deleted','0')
                                    ->where('su_education_record.service_user_id',$service_user_id)
                                    ->orderBy('su_education_record.id','desc')
                                    ->orderBy('su_education_record.created_at','desc');

        $today = date('Y-m-d 00:0:00');
        //$yesterday = date('Y-m-d 00:0:00',strtotime('-1 day'));
        
        if(isset($_GET['logged'])) {

            $tick_btn_class = "edit-edu-logged-form-sub-btn";
            $su_edu_records = $su_edu_records->where('su_education_record.created_at','<',$today)->paginate(5);

            if($su_edu_records->links() != '') {
                echo '</div><div class="liv_skill_paginate m-l-15 position-botm">';
                echo $su_edu_records->links();
                echo '</div>';       
            }

        }
        elseif(isset($_GET['search'])) {

            $tick_btn_class = "search_edu_rec_btn";

            $er_search_type = $_GET['er_search_type'];
            if($er_search_type == 'title'){
            
                $su_edu_records = $su_edu_records->where('eslr.description','like','%'.$_GET['search'].'%')->get();
            
            } else{

                $search_date = date('Y-m-d',strtotime($_GET['er_date'])).' 00:00:00';
                $search_date_next = date('Y-m-d',strtotime('+1 day', strtotime($_GET['er_date']))).' 00:00:00';

                $su_edu_records = $su_edu_records->where('su_education_record.created_at','>',$search_date)
                                                            ->where('su_education_record.created_at','<',$search_date_next)->get();
            }
        }
        else {
            $su_edu_records = $su_edu_records->where('su_education_record.created_at','>',$today)->get();

            $tick_btn_class = "sbmt-edittd-edu-rec";
            //$add_new_case = '';
        }

        if(!$su_edu_records->isEmpty()){
            $pre_date = date('y-m-d',strtotime($su_edu_records['0']->created_at));
        }
        foreach ($su_edu_records as $key => $value) {
            //echo "<pre>"; print_r($su_edu_records); die;
            if($value->status == 1){
                $edu_set_btn_class = "clr-blue";
            }
            else{
                $edu_set_btn_class = "clr_grey";
            }

            if($value->am_pm == 'A'){
                $am_pm = 'Am';
            }else{
                $am_pm = 'Pm';
            }

            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';
            
            $first = 0;

            if(isset($_GET['logged']) ||  isset($_GET['search']) ){ 
                $edu_record_date = date('Y-m-d',strtotime($value->created_at));

                if($edu_record_date != $pre_date){
                    $pre_date = $edu_record_date; 
               
                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 edu_rec_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                       '.date('d F Y',strtotime($edu_record_date)).'
                                    </span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="daily-rcd-content">';
                }
                else{  }
            } 

            echo '
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 edu_rec_row">
                        <div class="form-group col-md-2 col-sm-2 col-xs-6 p-0">
                            <label class="col-md-6 col-sm-6 col-xs-6 p-t-7 r-p-0"> Score: </label>
                            <div class="col-md-6 col-sm-6 col-xs-6 p-0">
                                <div class="select-style small-select">
                                    <select name="edit_su_edu_score[]" disabled  class="edit_edu_rec_score_'.$value->id.' edit_edu_record sel">'; 

                                        /*if($value->scored == 0){
                                            echo '<option value="0">0</option>';
                                        } else{
                                            echo '<option value="0" disabled >0</option>';                                        
                                        }*/

                                        for($i=0; $i<=5; $i++){
                                            $select         = ($i == $value->scored) ? 'selected' : '';
                                            //$disable_option = ( ($i > $value->scored) && ($value->scored != 0) ) ? 'disabled' : '';
                                            /*if($select && $disable_option){
                                                $disable_option = '';
                                            }*/
                                            echo '<option value="'.$i.'"'. $select .' >'.$i.'</option>';
                                        }
                                  echo '</select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-10 col-sm-10 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <div class="col-sm-9">
                                    <input style="width:350px; padding-left:50px;" type="text" name="edit_su_record_desc[]" class="form-control cus-control edit_record_desc_'.$value->id.' edit_edu_record"  disabled  value="'.$value->description.'" maxlength="255" />
                                    <input type="hidden" name="su_task_desc[]" value="'.$value->description.'"/>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" name="edit_su_record_amount[]" class="form-control plusInside cus-control edit_record_desc_'.$value->id.' edit_edu_record"  disabled  value="'.$value->amount.'" maxlength="255" />
                                    <input class="plusicon" type="hidden" name="su_task_amount[]" value="'.$value->amount.'"/>
                                </div>
                                <div class="col-sm-9 m-t-5">
                                    <input style="width:390px; padding-left:50px;" type="text" name="edit_su_record_am_pm[]" class=" form-control cus-control edit_record_desc_'.$value->id.' edit_edu_record"  disabled  value="'.$am_pm.'" maxlength="255" />
                                    <input type="hidden" name="su_task_am_pm[]" value="'.$am_pm.'"/>
                                </div>';
                                 
                                if(!empty($value->details)){
                                    echo '<div class="edu-rec input-plus color-green"> <i class="fa fa-plus"></i> </div>';
                                }
                                  echo '<input type="hidden" name="edit_su_record_id[]" value="'.$value->id.'" disabled="disabled" class="edit_edu_rec_id'.$value->id.'" />
                                    <span class="input-group-addon cus-inpt-grp-addon '.$edu_set_btn_class.' settings">
                                    <i class="fa fa-cog"></i>
                                    <div class="pop-notifbox">
                                        <ul class="pop-notification" type="none">';
                                            //if(isset($add_new_case)) { 
                                            echo '<li> <a href="#" su_edu_record_id="'.$value->id.'" class="edit_edu_rec_btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>';
                                            //}
                                            echo '<li> <a href="#" su_edu_record_id="'.$value->id.'" class="delete-edu-rec"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                           
                                            <li>  <a href="'.url('/service/education-record/calendar/add/'.$value->id).'" >  <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to calendar </a> </li>
                                        </ul>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <div class="input-plusbox form-group col-xs-12 p-0 detail">
                            <label class="cus-label color-themecolor"> Details: </label>
                            <div class="cus-input">
                                <div class="input-group">
                                    <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_edu_rec_detail_'.$value->id.' edit_edu_record " value="" maxlength="1000">'.$value->details.'</textarea>
                                    <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                        <div class="tick_show sbt_btn_tick_div '.$tick_btn_class.'">'.$check.'</div>
                                    </div>
                                <!--  <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show '.$tick_btn_class.'">'.$check.'</span> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';

        }
    }

    public function add(Request $request){

        if($request->isMethod('get'))
        {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
            if($home_id != $su_home_id){
                echo '0'; die; 
            }

            $su_er = new ServiceUserEducationRecord;
            $su_er->service_user_id     = $data['service_user_id'];
            $su_er->education_record_id = $data['edu_rec_id'];
            $su_er->am_pm               = $data['am_pm'];
            $su_er->details             = '';
            $su_er->status              = 1;
            $su_er->home_id             = $home_id;

            if($su_er->save()){

                //saving notification start
                $notification                                  = new Notification;
                $notification->service_user_id                 = $data['service_user_id'];
                $notification->event_id                        = $su_er->id;
                $notification->notification_event_type_id      = '7';
                $notification->event_action                    = 'ADD';      
                $notification->home_id                         = $home_id;
                $notification->user_id                         = Auth::user()->id;        
                $notification->save();
                //saving notification end

                $res = $this->index($data['service_user_id']);
                echo $res; die; 
            }
            else{ 
                echo '0';
            }
            die;
        }
    }

    public function delete($su_edu_record_id){

        if(!empty($su_edu_record_id)){
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $su_er = ServiceUserEducationRecord::where('id', $su_edu_record_id)->first();            
            if(!empty($su_er)){
             
                $su_home_id = ServiceUser::where('id',$su_er->service_user_id)->value('home_id');
                if($home_id != $su_home_id){
                    echo '0'; die; 
                }

                $su_er->is_deleted = 1;
                if($su_er->save()){
                    echo '1';
                } else{
                    echo '0';
                }
            }
        }
        die;
    }

    public function edit(Request $request){

        $data            = $request->all();
        $service_user_id = $this->_edit($data);
        $res             = $this->index($service_user_id);
        echo $res;  
        die;
    }

    public function _edit($data = array()){
        // echo "<pre>"; print_r($data); die;
        $service_user_id = '';
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];

        if(isset($data['edit_su_record_id'])){ 
            $su_edu_rec_ids = $data['edit_su_record_id'];
            
            if(!empty($su_edu_rec_ids)) {

                foreach ($su_edu_rec_ids as $key => $su_edu_rec_id) {
                    
                    $su_er = ServiceUserEducationRecord::find($su_edu_rec_id);
                    if(!empty($su_er)) {

                        $service_user_id = $su_er->service_user_id;

                        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
                        
                        if($home_id == $su_home_id){
                            
                            $su_er->scored  = $data['edit_su_edu_score'][$key];
                            $su_er->details = $data['edit_su_record_detail'][$key];

                            if($su_er->save()) {
                                //update earning of su for that dates
                              $updated_earning_star_id = EarningScheme::updateEarning($service_user_id,$su_er->created_at);
                            }
                        }
                        if(!empty($data['su_task_amount'][$key])){
                            //here Child gets his task amount
                            $su_record_id = ServiceUserMoney::where('su_record_id',$su_edu_rec_id)->value('su_record_id');
                            // echo "<pre>"; print_r($su_record_id); die;
                            if(!empty($su_record_id)){
                                return true;
                            }else{
                                // echo "string"; die;
                                $total_balance = 0;
                                $service_usermoney                  = new ServiceUserMoney; 
                                $service_usermoney->service_user_id = $service_user_id;
                                $service_usermoney->su_record_id    = $su_edu_rec_id; 
                                $service_usermoney->description     = $data['su_task_desc'][$key];
                                $service_usermoney->task_amount     = $data['su_task_amount'][$key];
                                $service_usermoney->txn_type        = 'D';
                                $service_usermoney->txn_amount      =  0;

                                //here we get balance amount of Child 
                                $balance       = ServiceUserMoney::where('service_user_id',$service_user_id)->orderBy('id','desc')->value("balance");
                                $total_balance = $balance + $data['su_task_amount'][$key];  

                                $service_usermoney->balance         =  $total_balance;    
                                $service_usermoney->save();
                            }
                        }
                    }
                }
            }
            if(!empty($updated_earning_star_id)) { //if a new star has been added
                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $service_user_id;
                $notification->event_id                   = $updated_earning_star_id;
                $notification->notification_event_type_id = '3';
                $notification->event_action               = 'ADD_STAR';   
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;             
                $notification->save();
                //saving notification end
            
            } else {
                //saving notification start
                $notification                              = new Notification;
                $notification->service_user_id             = $service_user_id;
                $notification->event_id                    = $su_er->id;
                $notification->notification_event_type_id  = '7';
                $notification->event_action                = 'EDIT'; 
                $notification->home_id                     = $home_id;
                $notification->user_id                     = Auth::user()->id;               
                $notification->save();
                //saving notification end
            }
        }        
        return $service_user_id;
    }

    public function add_to_calendar($su_edu_record_id = null) {
        
        $clndr_add = ServiceUserEducationRecord::where('id',$su_edu_record_id)->update(['added_to_calendar'=> '1']);
        if($clndr_add) {
            return redirect()->back()->with('success', CAl_ADD_RECORD);
        } else {
            return redirect()->back()->with('error', COMMON_ERROR);
        }
    }

    /*public function edit(Request $request){

        $data = $request->all();

        if(isset($data['edit_su_record_id'])){ 
            $su_edu_rec_ids = $data['edit_su_record_id'];
            
            if(!empty($su_edu_rec_ids)){

                foreach ($su_edu_rec_ids as $key => $su_edu_rec_id) {

                    $su_er     = ServiceUserEducationRecord::find($su_edu_rec_id);
                    if(!empty($su_er)){

                        $service_user_id = $su_er->service_user_id;
                        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
                        if(Auth::user()->home_id == $su_home_id){
                            $su_er->scored  = $data['edit_su_edu_score'][$key];
                            $su_er->details = $data['edit_su_record_detail'][$key];
                            $su_er->save();

                            EarningScheme::updateEarning($service_user_id,$su_er->created_at);

                        }
                    }
                }
            }
        }*/
       
        //$updated_earning_star_id = EarningScheme::updateEarning($service_user_id);

        /*if(!empty($updated_earning_star_id)) { //if a new star has been added
            
            //saving notification start
            $notification                  = new Notification;
            $notification->service_user_id = $service_user_id;
            $notification->event_id        = $updated_earning_star_id;
            $notification->event_type      = 'SU_ER';
            $notification->event_action    = 'ADD_STAR';   
            $notification->home_id         = Auth::user()->home_id;
            $notification->user_id         = Auth::user()->id;             
            $notification->save();
            //saving notification end
        
        } else{
            //saving notification start
            $notification                  = new Notification;
            $notification->service_user_id = $service_user_id;
            $notification->event_id        = $record->id;
            $notification->event_type      = 'SU_DR';
            $notification->event_action    = 'EDIT'; 
            $notification->home_id         = Auth::user()->home_id;
            $notification->user_id         = Auth::user()->id;               
            $notification->save();
            //saving notification end
        }*/

        /*$res = $this->index($service_user_id);
        echo $res;  
        die;
    }*/
}