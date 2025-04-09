<?php

namespace App;
use DB, Auth;
use Illuminate\Database\Eloquent\Model;
use Carbon;
use App\ServiceUserPlacementPlan, App\ServiceUserEarningIncentive, App\ServiceUserHealthRecord, App\ServiceUserBmp, App\ServiceUserRmp, App\ServiceUserIncidentReport, App\Notification, App\ServiceUserDailyRecord, App\ServiceUserLivingSkill, App\ServiceUserEducationRecord, App\ServiceUserAFC, App\ServiceUserLocationNotification;

class Notification extends Model
{
    protected $table = 'notification';

    //notification center
    public static function getSuNotification($service_user_id = null, $start_date = null, $end_date = null, $limit = null, $home_id = null){

        //$usr_home_id = Auth::user()->home_id;

        $notif_query = DB::table('notification as n')
                    ->select('n.*')
                    ->where('n.home_id', $home_id);
                    //->leftJoin('service_user as su','n.service_user_id','su.id')
                    //->where('n.service_user_id', $service_user_id);
                    //->where('su.home_id', $usr_home_id);
       
        //if $service_user_id is not empty it means notifications will be shown for specific su only.
        if(!empty($service_user_id)){
            $notif_query = $notif_query->where('n.service_user_id', $service_user_id);
        }

        if(!empty($start_date)){
            
            $start_date = date('Y-m-d',strtotime($start_date));
            $start_date = $start_date.' 00:00:00';

            $notif_query = $notif_query->whereDate('n.created_at', '>=', $start_date);
        }

        if(!empty($end_date)){

            $end_date = date('Y-m-d',strtotime('+1 day', strtotime($end_date)));
            $end_date = $end_date.' 00:00:00';

            $notif_query = $notif_query->whereDate('n.created_at', '<', $end_date);
        }          
        
        // echo '$start_date'.$start_date;
        // echo '$end_date'.$end_date;
        
        if(!empty($limit)){
            $notif_query = $notif_query->limit($limit);
        }
       
        $notifications = $notif_query->orderBy('n.created_at', 'desc')->get()->toArray();
        //echo '<pre>'; print_r($notifications); die;

        $notif = '';
        //$notifications = array();
        if(empty($notifications)){
            $notif = '<div class="text-center">No Notifications Found.</div>';

        } else{
            
            foreach($notifications as $notification)  {

                $created_at = $notification->created_at;
                $created_at1 = Carbon\Carbon::parse($created_at);
                $diff = $created_at1->diffForHumans();
                /*$diff = $created_at1->diffForHumans();          //working
                $diff = $current->diffForHumans($created_at1);
                echo $diff; die;*/ 
                //echo '<pre>';print_r($notification);die;
                
                //If notification is for su list page then there should be su name.
                if(empty($service_user_id)) { //means show notifications for all

                    $su_name = ServiceUser::where('id',$notification->service_user_id)->value('name');                    
                    $su_id = ServiceUser::where('id',$notification->service_user_id)->value('id');
                    // echo $su_id; die;                    
                }

                if($notification->notification_event_type_id == "2")   { 
                    if(isset($su_name)){
                        $event_name   = ucfirst($su_name);
                        $list_msg_cntnt = "daily";
                    } else{
                        $event_name   = "Daily Record";
                        $list_msg_cntnt = '';
                    }
                    $tile_color = "alert alert-info";     //terques
                    $icon       = "fa fa-calendar";

                    if($notification->event_action == "ADD"){
                        $dr_description = DB::table('su_daily_record')->select('dr.description')
                                        ->join('daily_record as dr','dr.id','su_daily_record.daily_record_id')
                                        ->where('su_daily_record.id', $notification->event_id)
                                        ->first();

                        if(!empty($dr_description)){
                            $dr_description = $dr_description->description;
                        } else{
                            //$dr_description = '';
                            continue;
                        }
                        $message = "A new ".$list_msg_cntnt." record '".$dr_description."' is added";                        

                    } else{
                        //edit case
                        $message = "Daily record all upto date";
                    }
                }

                else if($notification->notification_event_type_id == "1")   {

                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'health';
                    } else{
                        $event_name = "Health Record";
                        $list_msg_cntnt = '';
                    }

                    //$event_name = "Health Record";
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-heartbeat";

                    if($notification->event_action == "ADD"){
                        $hr_description = ServiceUserHealthRecord::where('id', $notification->event_id)->value('title');
                        if(empty($hr_description)){
                            continue;
                        }
                        $message = "A new ".$list_msg_cntnt." record '".$hr_description."' is added";      
                    } else{
                        //edit case
                        $message = "Health record all upto date";
                    }
                }

                else if($notification->notification_event_type_id == "4")   {
                    if(isset($su_name)){
                        $event_name     = ucfirst($su_name);
                        $placement_url  = url('service/placement-plans/'.$su_id); 
                    } else{
                        $event_name     = "Placement Plan";
                        $placement_url  = '';

                    }
                    // $event_name = "Placement Plan";
                    $tile_color = "alert alert-placement";    
                    $icon       = "fa fa-map-marker";
                    $task       = ServiceUserPlacementPlan::where('id', $notification->event_id)->value('task');
                    
                    if(empty($task)){
                        continue;
                    }

                    if($notification->event_action == "ADD"){

                        $message = "A new Placement Plan '".$task."' is added";      
                    
                    } else if($notification->event_action == "MARK_COMPLETE"){
                        
                        $message = "Placement Plan '".$task."' is completed";      
                    
                    } else if($notification->event_action == "MARK_ACTIVE"){
                        
                        $message = "Placement Plan '".$task."' is made active";      
                    
                    } else{
                        $message = "Placement Plan all upto date";
                    }
                }

                elseif($notification->notification_event_type_id == "3") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Earning Scheme';

                    } else{
                        $event_name = "Earning Scheme";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Earning Scheme";
                    $tile_color = "alert alert-earning";   //purple
                    $icon = "fa fa-star-half-o";

                    if($notification->event_action == 'ADD_STAR'){
                        //$message     = "Star added for Daily records of ".date('d M Y',strtotime($created_at))." ";
                        $message     = $list_msg_cntnt." 1 Star Added, Well done, add a new activity to your calendar!";

                    } elseif($notification->event_action == 'REMOVE_STAR'){

                        $message     = $list_msg_cntnt." 1 Star Removed, Not meeting your target, Management plan to be organized.";

                    } elseif($notification->event_action == 'SPEND_STAR'){
                        
                        $inventive_info = ServiceUserEarningIncentive::select('su_earning_incentive.star_cost','i.name','esc.title as category_name')
                                                ->where('su_earning_incentive.id',$notification->event_id)
                                                ->join('incentive as i','i.id','su_earning_incentive.incentive_id')
                                                ->join('earning_scheme_category as esc','esc.id','i.earning_category_id')
                                                ->first();
                        
                        if(!empty($inventive_info)){
                            
                            if($inventive_info->star_cost > 1){
                                $star = $inventive_info->star_cost." Stars";
                            } else{
                                $star = $inventive_info->star_cost." Star";
                            }

                            $label       = $star." Spend";
                            //$message     = $star." Spend for ".ucfirst($inventive_info->name)." ";
                            $message     = $list_msg_cntnt.' '.$label.' '.$inventive_info->category_name." chosen, keep up the good work!";
                            //Star total now 
                        } else{
                            continue;
                        }

                    }

                } 
                /*elseif ($notification->notification_event_type_id == "5") {
                        
                        $event_name = "MFC/AFC";
                        $tile_color = "alert alert-info";     //terques
                        $icon       = "fa fa-user-times";

                        if($notification->event_action == "ADD"){
                            $mf_description = DB::table('su_mfc')->select('mf.description')
                                            ->join('mfc as mf','mf.id','su_mfc.mfc_id')
                                            ->where('su_mfc.id', $notification->event_id)
                                            ->first();

                            if(!empty($mf_description)){
                                $mf_description = $mf_description->description;
                            } else{
                                //$mf_description = '';
                                continue;
                            }
                            $message = "A new record '".$mf_description."' is added";                        

                        } else{
                            //edit case
                            $message = "MFC/AFC record all upto date";
                        }
                }*/
                else if($notification->notification_event_type_id == "5") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'MFC';
                    } else{
                        $event_name     = "MFC";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-info";     
                    $icon       = "fa fa-user-times";

                    if($notification->event_action == "ADD"){
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $mfc_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($mfc_description)){
                            continue;
                        }

                        $message = "A new ".$list_msg_cntnt." record '".$mfc_description."' is added";      
                    } else{
                        //edit case
                        $mfc_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($mfc_description)){
                            continue;
                        }
                        $message = "MFC record ".$mfc_description." has been updated";
                    }
                } else if ($notification->notification_event_type_id == "6") {
                        if(isset($su_name)){
                            $event_name = ucfirst($su_name);
                            $list_msg_cntnt = 'living skill ';
                        } else{
                            $event_name = "Living Skill";
                            $list_msg_cntnt = '';
                        }
                        // $event_name = "Living Skill";
                        $tile_color = "alert alert-info";     //terques
                        $icon       = "fa fa-child";

                        if($notification->event_action == "ADD") {
                            $su_living_skill = DB::table('su_living_skill')->select('ls.description')
                                            ->join('living_skill as ls','ls.id','su_living_skill.living_skill_id')
                                            ->where('su_living_skill.id', $notification->event_id)
                                            ->first();

                            if(!empty($su_living_skill)) {
                                $su_living_skill = $su_living_skill->description;
                            } else {
                                //$su_living_skill = '';
                                continue;
                            }
                            $message = "A new ".$list_msg_cntnt." record '".$su_living_skill."' is added";                        

                        } else{
                            //edit case
                            $message = "Living skill record all upto date";
                        }
                } else if ($notification->notification_event_type_id == "7") {
                        if(isset($su_name)){
                            $event_name = ucfirst($su_name);
                            $list_msg_cntnt = 'education/training ';
                        } else{
                            $event_name = "Education/Training";
                            $list_msg_cntnt = '';
                        }
                        // $event_name = "Education/Training";
                        $tile_color = "alert alert-info";     //terques
                        $icon       = "fa fa-graduation-cap";

                        if($notification->event_action == "ADD") {
                            $su_education_record = DB::table('su_education_record')->select('er.description')
                                                    ->join('education_record as er','er.id','su_education_record.education_record_id')
                                                    ->where('su_education_record.id', $notification->event_id)
                                                    ->first();

                            if(!empty($su_education_record)) {
                                $su_education_record = $su_education_record->description;
                            } else {
                                //$su_education_record = '';
                                continue;
                            }
                            $message = "A new ".$list_msg_cntnt."record '".$su_education_record."' is added";                        
                        } else {
                            //edit case
                            $message = "Education / Training record all upto date";
                        }
                } else if($notification->notification_event_type_id == "8") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'BMP';
                    } else{
                        $event_name = "BMP";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "BMP";
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-frown-o";

                    if($notification->event_action == "ADD"){
                        /*$bmp_description = ServiceUserBmp::where('id', $notification->event_id)->value('title');
                        if(empty($bmp_description)){
                            continue;
                        }*/

                        $description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($description)){
                            continue;
                        }

                        $message = "A new ".$list_msg_cntnt." record '".$description.' '.$notification->event_id."' is added";      
                    } else{
                        //edit case
                        $description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($description)){
                            continue;
                        }
                        $message = "BMP record ".$description." has been updated";
                    }

                } else if($notification->notification_event_type_id == "9") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'RMP';
                    } else{
                        $event_name     = "RMP";
                        $list_msg_cntnt ='';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-meh-o";

                    if($notification->event_action == "ADD"){
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $rmp_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($rmp_description)){
                            continue;
                        }

                        $message = "A new ".$list_msg_cntnt." record '".$rmp_description."' is added";      
                    } else{
                        //edit case
                        $rmp_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($rmp_description)){
                            continue;
                        }
                        $message = "RMP record ".$rmp_description." has been updated";
                    }
                } else if($notification->notification_event_type_id == "10")   {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Incident Report';
                    } else{
                        $event_name = "Incident Report";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Incident Report";
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-bolt";

                    if($notification->event_action == "ADD"){
                        /*$incident_report = ServiceUserIncidentReport::where('id', $notification->event_id)->value('title');
                        if(empty($incident_report)){
                            continue;
                        }*/
                        
                        $description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($description)){
                            continue;
                        }
                        $message = "A new ".$list_msg_cntnt." record '".$description."' is added";      
                    } else{
                        //edit case
                        $description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($description)){
                            continue;
                        }
                        $message = "Incident Reports ".$description." has been updated";
                    }
                } else if($notification->notification_event_type_id == "11") {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Risk Change';
                    } else {
                        $event_name = 'Risk Change';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation-triangle";
                    if($notification->event_action == "ADD") {
                        $risk_change = DB::table('su_risk')->select('su_risk.status','r.description','r.icon')
                                        ->join('risk as r', 'r.id','su_risk.risk_id')
                                        ->where('su_risk.id', $notification->event_id)
                                        ->first();
                        // echo "<pre>"; print_r($risk_title); die;
                        if(!empty($risk_change)) {
                            if($risk_change->status == 2) {
                                $risk_type = "Live Risk";
                            } elseif($risk_change->status == 1) {
                                $risk_type = "Historic Risk";
                            } else {
                                $risk_type = "No Risk";
                            }
                            $risk_description = $risk_change->description;
                            $icon = $risk_change->icon;
                        } else {
                            continue;
                        } 
                        $message = "Risk ".$risk_description." has changed to ".$risk_type;
                    } 
                } else if($notification->notification_event_type_id == "12") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Form';
                    } else{
                        $event_name     = "Form";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-info";     
                    $icon       = "fa fa-bolt";

                    if($notification->event_action == "ADD"){
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $form_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($form_description)){
                            continue;
                        }

                        $message = "A new ".$list_msg_cntnt." form '".$form_description."' is added";      
                    } else{
                        //edit case
                        $form_description = DynamicForm::where('id',$notification->event_id)->value('title');
                        if(empty($form_description)){
                            continue;
                        }
                        $message = "Form record ".$form_description." has been updated";
                    }
                } else if($notification->notification_event_type_id == "13") {
                    if(isset($su_name)){
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'AFC Status';
                    } else{
                        $event_name     = "AFC Status";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    // $tile_color = "alert alert-info";     
                    $icon       = "fa fa-male";

                    if($notification->event_action == "ADD"){

                        //$afc_status = ServiceUserAFC::where('id',$notification->event_id)->value('afc_status');

                        $afc = ServiceUserAFC::select('afc_status','created_at')->where('id',$notification->event_id)->first();
                        $afc_status     = $afc->afc_status;
                        $afc_created_at = $afc->created_at;
                     
                        if($afc_status == '1') {
                            $content = "Came in home";     
                            $tile_color = "alert alert-success"; 
                        } else if($afc_status == '0') {
                            $content = "Came out home";   
                            $tile_color = "alert alert-danger";   
                        } else {
                            $tile_color = "alert alert-info";
                            continue;
                        }
                    
                        $message = $content.' on '.date('d-m-Y H:i',strtotime($afc_created_at));
                    } else{
                        //edit case
                        $afc_status = ServiceUserAFC::where('id',$notification->event_id)->value('afc_status');
                        if(empty($afc_status)){
                            continue;
                        }
                        $message = "AFC status";
                    }
                } else if($notification->notification_event_type_id == "14") {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'In danger';
                    } else {
                        $event_name = 'In danger';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation-triangle";
                    if($notification->event_action == "ADD") {
                        $in_danger = DB::table('su_care_center')->select('su_care_center.created_at','su.name')
                                                    ->join('service_user as su', 'su.id', 'su_care_center.service_user_id')
                                                    ->where('su_care_center.care_type','D')
                                                    ->where('su_care_center.id', $notification->event_id)
                                                    ->first();

                        // echo "<pre>"; print_r($risk_title); die;
                        if(!empty($in_danger)) {
                            $in_danger_created_at = $in_danger->created_at;
                            $content = $in_danger->name." is in danger.";
                            $message = $content;
                        } else {
                            continue;
                        }
                        //$message = $content.' on '.date('d-m-Y H:i',strtotime($in_danger_created_at));
                    } 
                } else if($notification->notification_event_type_id == "15") {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Request Callback';
                    } else {
                        $event_name = 'Request Callback';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-info";
                    $icon       = "fa fa-phone";
                    if($notification->event_action == "ADD") {
                        $req_call_bk = DB::table('su_care_center')->select('su_care_center.created_at','su.name')
                                            ->join('service_user as su', 'su.id','su_care_center.service_user_id')
                                            ->where('su_care_center.care_type','R')
                                            ->where('su_care_center.id', $notification->event_id)
                                            ->first();

                        // echo "<pre>"; print_r($risk_title); die;
                        if(!empty($req_call_bk)) {
                            $req_call_bk_created_at = $req_call_bk->created_at;
                            $content = $req_call_bk->name." has requested to callback.";
                            $message = $content;
                        } else {
                            continue;
                        }
                        // $message = $content.' on '.date('d-m-Y H:i',strtotime($req_call_bk_created_at));
                    } 
                } else if($notification->notification_event_type_id == "16")   {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Need Assistance';
                    } else{
                        $event_name = "Need Assistance";
                        $list_msg_cntnt = '';
                    }
    
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-exclamation";

                    if($notification->event_action == "ADD"){
                        
                        $assistance = DB::table('su_need_assistance')->select('su_need_assistance.created_at','su_need_assistance.message','su.name')
                                        ->join('service_user as su','su.id','su_need_assistance.service_user_id')
                                        ->where('su_need_assistance.id',$notification->event_id)
                                        ->first();
                        if(!empty($assistance)) {
                            $assistance_created_at = $assistance->created_at;
                            $content = $assistance->name." has made need assistance request.";
                            $message = $content;      
                        } else {
                            continue;
                        }
                        // $message = "Need assistance '".$description."' is added";      
                    }
                } else if($notification->notification_event_type_id == "17")   {

                    if($notification->event_action == 'ADD'){
                
                        $su_loc_notif = ServiceUserLocationNotification::
                                            select('id','service_user_id','location_name','location_type','old_location_type')
                                            ->where('id',$notification->event_id)
                                            ->first();

                        if(!empty($su_loc_notif)){
                            $old_loc = $su_loc_notif->old_location_type;
                            $new_loc = $su_loc_notif->location_type;

                            if($old_loc == 'A'){
                                $old_loc_txt = 'allowed';
                            } else if($old_loc == 'R'){
                                $old_loc_txt = 'restricted';
                            } else{
                                $old_loc_txt = 'grey';
                            }
                            
                            if($new_loc == 'A') {
                                $new_loc_txt = 'allowed';
                            } else if($new_loc == 'R'){
                                $new_loc_txt = 'restricted';
                            } else{
                                $new_loc_txt = 'grey';
                            }
                            $list_msg_cntnt   = "Location alert";
                            $s_user_name      = ServiceUser::where('id',$su_loc_notif->service_user_id)->value('name'); 

                            $message = ucfirst($s_user_name)." has entered into ".$new_loc_txt." area from the ".$old_loc_txt." area ".$created_at.".";

                            if(isset($su_name)) {
                                $event_name = ucfirst($su_name);
                                $list_msg_cntnt = 'Location alert';
                            } else{
                                $event_name = "Location alert";
                                $list_msg_cntnt = '';
                            }
                    
                            $tile_color = "alert alert-health";     
                            $icon       = "fa fa-exclamation";

                        } else {
                            continue;
                        }
                    }

                    //default
                    /*$tile_color = "alert alert-health";     
                    $icon       = "fa fa-exclamation";

                    if($notification->event_action == "ADD"){
                        
                        $assistance = DB::table('su_need_assistance')->select('su_need_assistance.created_at','su_need_assistance.message','su.name')
                                        ->join('service_user as su','su.id','su_need_assistance.service_user_id')
                                        ->where('su_need_assistance.id',$notification->event_id)
                                        ->first();
                        if(!empty($assistance)) {
                            $assistance_created_at = $assistance->created_at;
                            $content = $assistance->name." has made need assistance request.";
                            $message = $content;      
                        } else {
                            continue;
                        }
                        // $message = "Need assistance '".$description."' is added";      
                    }*/
                } else if($notification->notification_event_type_id == "18")   {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Money Request';
                    } else{
                        $event_name = "Money Request";
                        $list_msg_cntnt = '';
                    }
    
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-credit-card";

                    if($notification->event_action == "ADD"){
                        
                        $money_req = DB::table('su_money_request')->select('su_money_request.created_at','su_money_request.amount','su.name')
                                        ->join('service_user as su','su.id','su_money_request.service_user_id')
                                        ->where('su_money_request.id',$notification->event_id)
                                        ->first();
                        if(!empty($money_req)) {
                            $money_req_created_at = $money_req->amount;
                            $content              = $money_req->name." has made money request for €".$money_req->amount;
                            $message = $content;      
                        } else {
                            continue;
                        }
                        // $message = "Need money_req '".$description."' is added";      
                    }
                } else if($notification->notification_event_type_id == "19")   {
                    if(isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Appointment Event';
                    } else{
                        $event_name = "Appointment Event";
                        $list_msg_cntnt = '';
                    }
    
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-map-marker";

                    if($notification->event_action == "ADD"){
                        
                        $event_req  = DB::table('su_calendar_event')->select('su_calendar_event.created_at','su_calendar_event.title','su.name')
                                        ->join('service_user as su','su.id','su_calendar_event.service_user_id')
                                        ->where('su_calendar_event.id',$notification->event_id)
                                        ->first();
                        if(!empty($event_req)) {
                            $event_req_title = $event_req->title;
                            $content = "A new ".$event_req_title." appointment is added";   
                            $message = $content;      
                        } else {
                            continue;
                        }     
                    }
                } else if($notification->notification_event_type_id == "20")   {
                    if(isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'Event Change Request';
                    } else{
                        $event_name = "Event Change Request";
                        $list_msg_cntnt = '';
                    }
    
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-calendar-o";

                    if($notification->event_action == "ADD"){
                        
                        $event_chng_req  = DB::table('event_change_request')->select('event_change_request.new_date','event_change_request.calendar_id')
                                                ->where('event_change_request.id',$notification->event_id)
                                                ->first();
                        if(!empty($event_chng_req)) {
                            $evt_req_new_date = date('m-d-y', strtotime($event_chng_req->new_date));
                            $content = "A new date ".$evt_req_new_date." for event change request is added.";   
                            $message = $content;      
                        } else {
                            continue;
                        }     
                    }
                } else if($notification->notification_event_type_id == "21")   {
                    if(isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'New Mood added';
                    } else{
                        $event_name = "New Mood added";
                        $list_msg_cntnt = '';
                    }
    
                    $tile_color = "alert alert-health";     
                    $icon       = "fa fa-user";

                    if($notification->event_action == "ADD"){
                        
                        $su_mood_info  = DB::table('su_mood')
                                                ->select('su_mood.description','m.name')
                                                ->join('mood as m','m.id', 'su_mood.mood_id')
                                                ->where('su_mood.id',$notification->event_id)
                                                ->first();
                        if(!empty($su_mood_info)) {
                            $mood_title = $su_mood_info->name;
                            $content = "A ".$mood_title." is added.";   
                            $message = $content;      
                        } else {
                            continue;
                        }     
                    }
                } 
                else {
                    continue;
                }
                if($tile_color == 'alert alert-placement'){
                    $notif .= '<div class="'.$tile_color.' clearfix">
                            <span class="alert-icon"><i class="'.$icon.'"></i></span>
                            <div class="notification-info">
                                <ul class="clearfix notification-meta">
                                    <li class="pull-left notification-sender"><span><a href="'.$placement_url.'"><b>'.$event_name.'</b></a></span></li>
                                    <li class="pull-right notification-time">'.$diff.'</li>
                                </ul>
                                <p>'.$message.'</p>
                            </div>
                        </div>';
                }else{
                    $notif .= '<div class="'.$tile_color.' clearfix">
                            <span class="alert-icon"><i class="'.$icon.'"></i></span>
                            <div class="notification-info">
                                <ul class="clearfix notification-meta">
                                    <li class="pull-left notification-sender"><span><a href="#"><b>'.$event_name.'</b></a></span></li>
                                    <li class="pull-right notification-time">'.$diff.'</li>
                                </ul>
                                <p>'.$message.'</p>
                            </div>
                        </div>';
                }
                
            } 
        }

        return $notif;
    }

    //dashboard under clock notifications
    public static function dashboardEventNotification() {

        $home_id = Auth::user()->home_id;

        $records = array();

        //Daily Records
        $daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id','su_daily_record.daily_record_id','su_daily_record.service_user_id','su_daily_record.created_at','dr.description','su.name as su_name')
                                                ->join('daily_record as dr', 'dr.id','su_daily_record.daily_record_id')
                                                ->join('service_user as su', 'su.id','su_daily_record.service_user_id')
                                                ->where('su_daily_record.is_deleted', '0')
                                                ->where('su.home_id',$home_id)
                                                ->orderBy('su_daily_record.id','asc')
                                                ->get()
                                                ->toArray();
        // echo '<pre>'; print_r($daily_records); die;
        $daily_count = 0;
        foreach ($daily_records as $key => $daily_record) {
            //check if this daily record is booked in calendar
            $event_id         = $daily_record['su_daily_record_id'];
            $event_type       = '2';
            $service_user_id  = $daily_record['service_user_id'];
            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            // echo "<pre>"; print_r($booking_response); die;
            if(empty($booking_response['calendar_id'])){ 
                if($daily_count <= 4){
                    $daily_record['color_class'] = 'label-daily';
                    $records[] = $daily_record;
                    $daily_count++;
                } else {  
                    break;
                }
            } 
            //$daily_records[$key] = array_merge($daily_records[$key],$booking_response);
        }   
        // echo '<pre>'; print_r($daily_records); die;
        
        //Health Records
        $health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id','su_health_record.title as description','su_health_record.created_at','su_health_record.service_user_id','su.name as su_name')
                                        ->join('service_user as su', 'su.id','su_health_record.service_user_id')
                                        ->where('su_health_record.is_deleted', '0')
                                        ->where('su.home_id', $home_id)
                                        ->orderBy('health_record_id','asc')
                                        ->get()
                                        ->toArray();
        $health_count = 0;
        foreach ($health_records as $key => $health_record) {
            // check if this health_record is booked in calendar
            $event_id   = $health_record['health_record_id'];
            $event_type = '1';
            $service_user_id = $health_record['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$health_records[$key] = array_merge($health_records[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($health_count <= 4){
                    $health_record['color_class'] = 'label-health';
                    $records[] = $health_record;
                    $health_count++;
                } else {  
                    break;
                }
            } 
        }
        //echo '<pre>'; print_r($records); die;
        
        //Living Skills
        $living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id','su_living_skill.living_skill_id','su_living_skill.created_at','su_living_skill.service_user_id','ls.description','su.name as su_name')
                                                ->join('living_skill as ls','ls.id','su_living_skill.living_skill_id')
                                                ->join('service_user as su', 'su.id','su_living_skill.service_user_id')
                                                ->where('su_living_skill.is_deleted','0')
                                                ->where('su.home_id', $home_id)
                                                ->orderBy('su_living_skill.id','asc')
                                                ->get()
                                                ->toArray();
        $living_count = 0;
        foreach ($living_skills as $key => $living_skill) {
            //check if this living skill is booked in calendar
            $event_id        = $living_skill['su_living_skill_id'];
            $event_type      = '9';
            $service_user_id = $living_skill['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$living_skills[$key] = array_merge($living_skills[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($living_count <= 4){
                    $living_skill['color_class'] = 'label-living';
                    $records[] = $living_skill;
                    $living_count++;
                } else {  
                    break;
                }
            }
        }   
        //Living Skills End

        //Education Records
        $education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id','su_education_record.education_record_id','su_education_record.service_user_id','su_education_record.created_at','er.description','su.name as su_name')
                                            ->join('education_record as er','er.id','su_education_record.education_record_id')
                                            ->join('service_user as su', 'su.id','su_education_record.service_user_id')
                                            ->where('su_education_record.is_deleted','0')
                                            ->where('su.home_id', $home_id)
                                            ->orderBy('su_education_record.id','asc')
                                            ->get()
                                            ->toArray();
        $education_count = 0;
        foreach ($education_records as $key => $education_record) {
            //check if this education record is booked in calendar
            $event_id        = $education_record['su_education_record_id'];
            $event_type      = '10';
            $service_user_id = $education_record['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$education_records[$key] = array_merge($education_records[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($education_count <= 4){
                    $education_record['color_class'] = 'label-education';
                    $records[] = $education_record;
                    $education_count++;
                } else {  
                    break;
                }
            }
        }   
        //Education Records End

        //earningScheme incentives
        $su_incentives   = ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost','su_earning_incentive.created_at','su_earning_incentive.service_user_id','incentive.name as description','su.name as su_name')
                                    ->join('incentive','incentive.id','su_earning_incentive.incentive_id')
                                    ->leftJoin('service_user as su', 'su.id','su_earning_incentive.service_user_id')
                                    ->where('su.home_id', $home_id)
                                    ->where('incentive.is_deleted','0')
                                    ->orderBy('su_earning_incentive.id','asc')
                                    ->get()
                                    ->toArray();
        $incentive_count = 0;
        foreach ($su_incentives as $key => $su_incentive) {
            //check if this incentive is booked in calendar
            $event_id        = $su_incentive['su_ern_inc_id'];
            $event_type      = '3';
            $service_user_id = $su_incentive['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$su_incentives[$key] = array_merge($su_incentives[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($incentive_count <= 4){
                    $su_incentive['color_class'] = 'label-incentive';
                    $records[] = $su_incentive;
                    $incentive_count++;
                } else {  
                    break;
                }
            }
        }

       /* //calendar added events
        $event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id','su_calendar_event.title as description','su_calendar_event.service_user_id','su_calendar_event.created_at','su.id as su_id','su.name as su_name')
                                    ->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
                                    //->where('su_calendar_event.service_user_id', $service_user_id)
                                    ->where('su.home_id',$home_id)
                                    ->orderBy('su_calendar_event.id','desc')
                                    ->get()
                                    ->toArray();
        $event_count = 0;
        foreach ($event_records as $key => $event_record) {

            //check if this event_record is booked in calendar
            $event_id        = $event_record['su_calendar_event_id'];
            $event_type      = '4';
            $service_user_id = $event_record['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$event_records[$key] = array_merge($event_records[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($event_count <= 4){
                    $records[] = $event_record;
                    $event_count++;
                } else {  
                    break;
                }
            }
        }*/

        //calendar added notes
        $calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id','su_calendar_note.title as description','su_calendar_note.note as title','su_calendar_note.service_user_id','su_calendar_note.created_at','su.name as su_name')
                                ->join('service_user as su', 'su.id','su_calendar_note.service_user_id')
                                ->where('su.home_id', $home_id)
                                ->orderBy('su_calendar_note.id','asc')
                                ->get()
                                ->toArray();
        $note_count = 0;
        foreach($calender_notes as $key => $calender_note){
            
            // check if this note is booked in calendar
            $event_id        = $calender_note['id'];
            $event_type      = '5';
            $service_user_id = $calender_note['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            //$calender_notes[$key] = array_merge($calender_notes[$key],$booking_response);
            if(empty($booking_response['calendar_id'])){ 
                if($note_count <= 4){
                    $calender_note['color_class'] = 'label-note';
                    $records[] = $calender_note;
                    $note_count++;
                } else {  
                    break;
                }
            }
        }

        //$records = Notification::aasort($records,"created_at");
        // echo '<pre>'; print_r($records); die;
        $newArray = array_slice($records, 0, 4, true);
        return $newArray;
        // echo '<pre>'; print_r($newArray); die;
        

        //get 4 oldest daily records which are not yet 
        //$daily_records = DB::table('su_daily_record')->where('')
        //su_daily_record
        

        /*$daily_record = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id','su_daily_record.daily_record_id','dr.description' )
                            ->join('daily_record as dr', 'dr.id','su_daily_record.daily_record_id')
                            ->join('service_user as su', 'su.id','su_daily_record.service_user_id')
                            //->where('su_daily_record.service_user_id', $service_user_id)
                            ->where('su_daily_record.is_deleted', '0')
                            ->where('su.home_id',$home_id)
                            ->orderBy('su_daily_record.id','asc')
                            ->first();
        $service_user_id = '';
        $event_id = $daily_record->su_daily_record_id;
        $event_type = '2';

        $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
        if(!empty($booking_response['calendar_id'])){
            $records[] = 
        }
        echo '<pre>'; print_r($booking_response); die;*/
    }

    public static function dashboardPlanEventNotification() {
        
        $home_id = Auth::user()->home_id;
        $records = array();
        
        //calendar added events
        $event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id','su_calendar_event.title as description','su_calendar_event.service_user_id','su_calendar_event.created_at','su.id as su_id','su.name as su_name')
                                    ->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
                                    //->where('su_calendar_event.service_user_id', $service_user_id)
                                    ->where('su.home_id',$home_id)
                                    ->where('su_calendar_event.is_deleted','0')
                                    ->orderBy('su_calendar_event.id','desc')
                                    ->get()
                                    ->toArray();
        // echo"<pre>";  print_r($event_records); die();
        $event_count = 0;
        foreach ($event_records as $key => $event_record) {

            //check if this event_record is booked in calendar
            $event_id        = $event_record['su_calendar_event_id'];
            $event_type      = '4';
            $service_user_id = $event_record['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
        //     echo"<pre>";
        // print_r($booking_response);
       
            //$event_records[$key] = array_merge($event_records[$key],$booking_response);
            // if(empty($booking_response['calendar_id'])){ 
            //     if($event_count <= 4){
            //         $records[] = $event_record;
            //         $event_count++;

            
            //     } else {  
            //         break;
            //     }
            // }
            if($event_count <= 4){
                $records[] = $event_record;
                $event_count++;
            } else {  
                break;
            }
        }
        // echo"<pre>";  print_r($records);  die;
        return $records;
        //$plan_records = Notification::aasort($records,"created_at");
        // echo '<pre>'; print_r($records); die;
       // $PlanNewArray = array_slice($plan_records, 0, 4, true);
        //return $PlanNewArray;
    }

    public static function aasort (&$array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        // echo "<pre>"; print_r($sorter); die;
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
        return $array;
    }

    //show sticky notification
    public static function getStickyNotifications($service_user_id = null,$limit = null){

        $home_id = Auth::user()->home_id;

        $notif_query = Notification::where('home_id', $home_id)
                    ->where('is_sticky', 1)
                    ->where('sticky_master_ack', null)
                    ->orderBy('id','desc');
                    
        $notifications = $notif_query->get()->toArray();
        return $notifications;
        //echo '<prE>'; print_r($notif_query); die;

    }

    /*public static function getNotificationCount($service_user_id = null,$notification_event_type_id = null) {     
        
        $fromDate = Carbon\Carbon::now()->subDay()->startOfWeek()->toDateString(); // or ->format(..)
        //$tillDate = Date('Y-m-d');
    
        $counts = DB::table('notification as n')
                            ->select('n.*')
                            //->whereBetween('created_at',[$fromDate, $tillDate])
                            ->whereDate('created_at','>=',$fromDate)
                            ->where('service_user_id', $service_user_id)
                            ->where('notification_event_type_id',$notification_event_type_id)
                            ->count();

        $notif_query = array();
        if($counts == 0) {
            $notif_query['color'] = 'label-success';
        } else if($counts <= 10) {
            $notif_query['color'] = 'label-warning';
        } else {
            $notif_query['color'] = 'label-danger';
        }

        if($notification_event_type_id == '1') {
            $notif_query['count'] = $counts;
        } else if($notification_event_type_id == '2') {
            $notif_query['count'] = $counts;
        } else if($notification_event_type_id == '3') {
            $notif_query['count'] = $counts;
        } else if($notification_event_type_id == '4') {
            $notif_query['count'] = $counts;
        } else {

        }
        return $notif_query;

    }*/

    /*public static function healthRecordCount($service_user_id = null) {

        $home_id = Auth::user()->home_id;
        //Health Records
        $health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id','su_health_record.service_user_id')
                                        ->join('service_user as su', 'su.id','su_health_record.service_user_id')
                                        ->where('su_health_record.is_deleted', '0')
                                        ->where('su_health_record.service_user_id', $service_user_id)
                                        ->where('su.home_id', $home_id)
                                        ->orderBy('health_record_id','asc')
                                        ->get()
                                        ->toArray();
        $health_count = 0;
        foreach ($health_records as $key => $health_record) {
            // check if this health_record is booked in calendar
            $event_id   = $health_record['health_record_id'];
            $event_type = '1';
            $service_user_id = $health_record['service_user_id'];

            $booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
            if(empty($booking_response['calendar_id'])){ 
                $health_count++;
            
            } 
        }

        $su_health_record = array();
        if($health_count == 0) {
            $su_health_record['color'] = 'label-success';
        } else if($health_count <= 10) {
            $su_health_record['color'] = 'label-warning';
        } else {
            $su_health_record['color'] = 'label-danger';
        }

        $su_health_record['count'] = $health_count;

        return $su_health_record;
    }*/
}