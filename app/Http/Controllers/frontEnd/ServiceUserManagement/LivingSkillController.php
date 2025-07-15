<?php
namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUserDailyRecord, App\DailyRecordScores, App\Notification, App\ServiceUserEarningDailyPoints, App\ServiceUserEarningStar, App\ServiceUser, App\LivingSkill, App\ServiceUserLivingSkill, App\EarningScheme;
use DB, Auth;
class LivingSkillController extends ServiceUserManagementController
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
        $tick_btn_class = ""; 
        //editing record while logged record and search
        /*if(isset($data['edit_su_skill_id'])){ 
            $su_living_skill_ids = $data['edit_su_skill_id'];
            if(!empty($su_living_skill_ids)){
                foreach ($su_living_skill_ids as $key => $record_id) {
                    $record          = ServiceUserDailyRecord::find($record_id);
                    $record->scored  = $data['edit_su_skill_score'][$key];
                    $record->details = $data['edit_su_skill_detail'][$key];
                    $record->save();
                }
            }
        }*/
        //in search case editing end
        $su_living_skills = ServiceUserLivingSkill::select('su_living_skill.*','eslr.description')
                                    ->join('earning_scheme_label_records as eslr','su_living_skill.living_skill_id','=','eslr.id')
                                    // ->join('living_skill as ls','su_living_skill.living_skill_id','=','ls.id')
                                    // ->where('ls.status','1')
                                    ->where('eslr.status','1')
                                    ->where('su_living_skill.is_deleted','0')
                                    ->where('su_living_skill.service_user_id',$service_user_id)
                                    ->orderBy('su_living_skill.id','desc')
                                    ->orderBy('su_living_skill.created_at','desc');
        // echo "<pre>"; print_r($su_living_skills); die;
        $today = date('Y-m-d 00:0:00');
        if(isset($_GET['logged'])) {
            $su_living_skills = $su_living_skills->where('su_living_skill.created_at','<',$today)->paginate(50);
                if($su_living_skills->links() != '') {
                    echo '</div><div class="liv_skill_paginate m-l-15 position-botm">';
                    echo $su_living_skills->links();
                    echo '</div>';       
                }
            $tick_btn_class = "edit-living-logged-form-sbmitbtn";
        }
        elseif(isset($_GET['search'])) {
            $tick_btn_class = "search_liv_skill_btn";
            $ls_search_type = $_GET['ls_search_type'];
            if($ls_search_type == 'title'){
                $su_living_skills = $su_living_skills->where('eslr.id',$_GET['search'])->get();
            } else{
                $search_date = date('Y-m-d',strtotime($_GET['ls_date'])).' 00:00:00';
                $search_date_next = date('Y-m-d',strtotime('+1 day', strtotime($_GET['ls_date']))).' 00:00:00';
                $su_living_skills = $su_living_skills->where('su_living_skill.created_at','>',$search_date)
                                                            ->where('su_living_skill.created_at','<',$search_date_next)
                                                            ->get();
            }
        }
        else {
            $su_living_skills = $su_living_skills->where('su_living_skill.created_at','>',$today)->get();
            $tick_btn_class    = "sbmt-edittd-liv-skill";
            //$add_new_case = '';
        }
        // $su_living_skills  = $su_living_skills->orderBy('su_living_skill.id','desc')
        //                                                     ->orderBy('su_living_skill.created_at','desc')
        //                                                     ->paginate(50);
        if(!$su_living_skills->isEmpty()){
            $pre_date = date('y-m-d',strtotime($su_living_skills['0']->created_at));
        }
        foreach ($su_living_skills as $key => $value) {
            if($value->status == 1){
                $skill_set_btn_class = "clr-blue";
            }
            else{
                $skill_set_btn_class = "clr_grey";
            }
            if($value->am_pm == 'A'){
                $am_pm = 'Am'; 
            }else{
                $am_pm = 'Pm'; 
            }
            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';
            $first = 0;
            if(isset($_GET['logged']) ||  isset($_GET['search']) ){ 
                $skill_date = date('Y-m-d',strtotime($value->created_at));
                if($skill_date != $pre_date){
                    $pre_date = $skill_date; 
                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 skill_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                       '.date('d F Y',strtotime($skill_date)).'
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
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 skill_row">
                    <div class="form-group col-md-3 col-sm-3 col-xs-6 p-0">
                        <label class="col-md-6 col-sm-6 col-xs-6 p-t-7 r-p-0"> Score: </label>
                        <div class="col-md-6 col-sm-6 col-xs-6 p-0">
                            <div class="select-style small-select">
                                <select name="edit_su_skill_score[]" disabled  class="edit_skill_score_'.$value->id.' edit_skill sel">'; 
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
                                        echo '<option value="'.$i.'"'. $select .' >'.$i.'
                                        </option>';
                                    }
                              echo '</select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-9 col-sm-9 col-xs-12 r-p-0">
                        <div class="input-group popovr">
                            <input type="text" name="edit_su_skill_desc[]" class="form-control cus-control edit_skill_desc_'.$value->id.' edit_skill"  disabled  value="'.$value->description.'" maxlength="255"/>
                            <input type="text" name="edit_su_am_pm[]" class="form-control cus-control edit_skill_desc_'.$value->id.' edit_skill"  disabled  value="'.$am_pm.'" maxlength="255"/>';
                            if(!empty($value->details)){
                                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
                            }
                              echo '<input type="hidden" name="edit_su_skill_id[]" value="'.$value->id.'" disabled="disabled" class="edit_skill_id_'.$value->id.'" />
                                <span class="input-group-addon cus-inpt-grp-addon '.$skill_set_btn_class.' settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">';
                                        //if(isset($add_new_case)) { 
                                        echo '<li> <a href="#" su_living_skill_id="'.$value->id.'" class="skill_edit_btn"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>';
                                        //}
                                        echo '<li> <a href="#" su_living_skill_id="'.$value->id.'" class="delete-skill"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                        <li> <a href="#" su_living_skill_id="'.$value->id.'" class="skill-detail"> <span class="color-red"> <i class="fa fa-plus color-green"></i> </span> Details </a> </li>
                                        <li>  <a href="'.url('/service/living-skill/calendar/add/'.$value->id).'" >  <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to calendar </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>
                    <div class="input-plusbox form-group col-xs-12 p-0 detail">
                        <label class="cus-label color-themecolor"> Details: </label>
                        <div class="cus-input">
                            <div class="input-group">
                                <textarea rows="5" name="edit_su_skill_detail[]" disabled class="form-control tick_text txtarea edit_detail_'.$value->id.' edit_skill" value="" maxlength="1000">'.$value->details.'</textarea>
                                <div class="input-group-addon cus-inpt-grp-addon sbt_tick_area"">
                                    <div class="tick_show sbt_btn_tick_div '.$tick_btn_class.'">'.$check.'</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ';
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
            //echo '<pre>'; print_r($data); die;
            $su_liv_skill                  = new ServiceUserLivingSkill;
            $su_liv_skill->service_user_id = $data['service_user_id'];
            $su_liv_skill->living_skill_id = $data['liv_skill_id'];
            $su_liv_skill->am_pm           = $data['am_pm'];
            $su_liv_skill->details         = '';
            $su_liv_skill->status          = 1;
            $su_liv_skill->home_id         = $home_id;
            // echo "<pre>"; print_r($su_liv_skill); die;
            if($su_liv_skill->save()){
                //saving notification start
                $notification                            = new Notification;
                $notification->service_user_id           = $data['service_user_id'];
                $notification->event_id                   = $su_liv_skill->id;
                $notification->notification_event_type_id = '6';
                $notification->event_action               = 'ADD';      
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;        
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
    public function edit(Request $request){
        $data            = $request->all();
        $service_user_id = $this->_edit($data);
        $res             = $this->index($service_user_id);
        echo $res;  
        die;
    }
    public function _edit($data = array()){
        $service_user_id = '';
        if(isset($data['edit_su_skill_id'])){
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0]; 
            $su_living_skill_ids = $data['edit_su_skill_id'];
            if(!empty($su_living_skill_ids)){
                foreach ($su_living_skill_ids as $key => $liv_skill_id) {
                    $su_liv_skill     = ServiceUserLivingSkill::find($liv_skill_id);
                    if(!empty($su_liv_skill)) {
                        $service_user_id = $su_liv_skill->service_user_id;
                        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
                        if($home_id == $su_home_id){
                            $su_liv_skill->scored  = $data['edit_su_skill_score'][$key];
                            $su_liv_skill->details = $data['edit_su_skill_detail'][$key];
                            if($su_liv_skill->save()){
                                //update earning of su for that dates
                                $updated_earning_star_id = EarningScheme::updateEarning($service_user_id,$su_liv_skill->created_at);
                            }
                        }
                    }
                }
            }
            if(!empty($updated_earning_star_id)) { //if a new star has been added
                //saving notification start
                $notification                            = new Notification;
                $notification->service_user_id           = $service_user_id;
                $notification->event_id                  = $updated_earning_star_id;
                $notification->notification_event_type_id = '3';
                $notification->event_action               = 'ADD_STAR';   
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;             
                $notification->save();
                //saving notification end
            } else {
                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $service_user_id;
                $notification->event_id                   = $su_liv_skill->id;
                $notification->notification_event_type_id = '6';
                $notification->event_action               = 'EDIT'; 
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;               
                $notification->save();
                //saving notification end
            }
        }
        return $service_user_id;
    }
    public function delete($su_living_skill_id = null){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $su_ls = ServiceUserLivingSkill::where('id', $su_living_skill_id)->first(); 
        if(!empty($su_ls))  {
            $su_home_id = ServiceUser::where('id',$su_ls->service_user_id)->value('home_id');
            if($home_id != $su_home_id){
                echo '0'; die; 
            }
            $su_ls->is_deleted = 1;
            if($su_ls->save()){
                echo '1';
            } else{
                echo '0';
            }
        }
        die;
    }
    public function add_to_calendar($su_liv_skill_id = null) {
        $clndr_add = ServiceUserLivingSkill::where('id', $su_liv_skill_id)->update(['added_to_calendar'=> '1']);
        if($clndr_add) {
            return redirect()->back()->with('success', CAl_ADD_RECORD);
        } else {
            return redirect()->back()->with('error', COMMON_ERROR);
        }
    }
}