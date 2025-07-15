<?php
namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use DB, Auth;
use App\EarningScheme, App\Incentive, App\ServiceUser, App\ServiceUserEarningDaily, App\ServiceUserEarningStar, App\Notification, App\ServiceUserEarningIncentive, App\Calendar, App\HomeLabel, App\ServiceUserEarningTarget, App\ServiceUserIncentiveSuspend, App\EarningAreaPercentage, App\ServiceUserEarningRemove, App\DynamicFormBuilder, App\EarningSchemeLabel;

class EarningSchemeController extends ServiceUserManagementController
{
    public function index($service_user_id = null){
       
        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $today = date('Y-m-d 00:0:00');
        // update notify
        $updatenotify = array('read_notify'=>1);
        DB::table('su_education_record')->where('service_user_id',$service_user_id)->where('home_id',$home_id)->update($updatenotify);
        DB::table('su_living_skill')->where('service_user_id',$service_user_id)->where('home_id',$home_id)->update($updatenotify);
        DB::table('su_daily_record')->where('service_user_id',$service_user_id)->where('home_id',$home_id)->update($updatenotify);
        // update notify
        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        if($su_home_id != $home_id){
            return redirect()->back()->with("error",UNAUTHORIZE_ERR);
        }

        $patient = DB::table('service_user')->where('id',$service_user_id)->where('is_deleted','0')->first();
        $daily_score   = DB::table('daily_record_score')->get();

        $form_pattern['su_mfc'] = '';
        $form_pattern['su_bmp'] = '';
        $form_pattern['su_rmp'] = '';
        $dynamic_forms = DynamicFormBuilder::select('id','title','location_ids')->where('home_id',$su_home_id)->get()->toArray();
        $service_users = ServiceUser::where('home_id',$su_home_id)->get()->toArray();

        /*$form     =  FormBuilder::showForm('su_mfc');
        $response = $form['response'];
        if($response == true){
            $form_pattern['su_mfc'] = $form['pattern']; 
        } else{
            $form_pattern['su_mfc'] = '';
        }
        $form     =  FormBuilder::showForm('su_bmp');
        $response = $form['response'];
        if($response == true){
            $form_pattern['su_bmp'] = $form['pattern']; 
        } else{
            $form_pattern['su_bmp'] = '';
        }
        $form     =  FormBuilder::showForm('su_rmp');
        $response = $form['response'];
        if($response == true){
            $form_pattern['su_rmp'] = $form['pattern']; 
        } else{
            $form_pattern['su_rmp'] = '';
        }*/


    	/*$su_daily_record = DB::table('su_daily_record as sudr')
                                    ->select('sudr.*','dr.description')
                                    ->join('daily_record as dr','sudr.daily_record_id','=','dr.id')
                                    ->where('sudr.created_at','>',$today)
                                    ->where('sudr.is_deleted','0')
                                    ->where('dr.status','1')
                                    ->where('sudr.service_user_id',$service_user_id)
                                    ->orderBy('sudr.id','desc')
                                    ->get();*/
    
        $earning_scheme = EarningScheme::where('home_id', $home_id)
                                        ->where('status', '1')
                                        ->orderBy('id','desc')
                                        ->get()->toArray();

        foreach ($earning_scheme as $key => $value) {
            
            $incentives_count = Incentive::where('earning_category_id',$value['id'])
                                    ->where('status','1')
                                    ->count();

            $earning_scheme[$key]['incentives_count'] = $incentives_count;
        }

        $today_date = date('Y-m-d');
        $total_stars = ServiceUserEarningStar::where('service_user_id',$service_user_id)->value('star');
        $total_stars = (int)$total_stars;
     
        $j = 0;
        for($i = 6; $i >= 0; $i--){
        
            $week_date = date('Y-m-d',strtotime('-'.$i.' day'));

            $earn_daily = ServiceUserEarningDaily::where('service_user_id',$service_user_id)
                            ->whereDate('date','=',$week_date)
                            ->first();

            if(!empty($earn_daily)){

                if($earn_daily->score >= $earn_daily->target){
                    $week_graph[$j]['point'] = 1;
                } else{
                    $week_graph[$j]['point'] = 0;
                }
            } else{
                $week_graph[$j]['point'] = 0;                
            }
        
            /*$week_graph[$j]['point'] = (int)ServiceUserEarningDaily::where('service_user_id',$service_user_id)
                                        ->whereDate('date','=',$week_date)
                                        ->value('point');*/

            $week_graph[$j]['date']  = date('d/m',strtotime($week_date));

            $j++;
        }

        $earn_history  = EarningScheme::earningHistory($service_user_id);

        $notifications = Notification::getSuNotification($service_user_id,'','',4,$home_id);

        //su booked incentives to be shown in r.h.s of calendar
        $booked_incentives = EarningScheme::bookedIncentives($service_user_id);
        // echo "<pre>"; print_r($booked_incentives);  die;
        /*$su_daily_record = DB::table('su_daily_record as sudr')
                                    ->select('sudr.*','dr.description')
                                    ->join('daily_record as dr','sudr.daily_record_id','=','dr.id')
                                    ->where('sudr.created_at','>=',$today)
                                    ->where('sudr.is_deleted','0')
                                    ->where('dr.status','1')
                                    ->where('sudr.service_user_id',$service_user_id)
                                    ->orderBy('sudr.id','desc')
                                    ->get();
    
        $su_living_skill = DB::table('su_living_skill as suls')
                                    ->select('suls.*','ls.description')
                                    ->join('living_skill as ls','ls.id','suls.living_skill_id')
                                    ->where('suls.created_at','>=', $today)
                                    ->where('suls.is_deleted','0')
                                    ->where('ls.status','1')
                                    ->where('suls.service_user_id', $service_user_id)
                                    ->orderBy('suls.id','desc')
                                    ->get();
    
        $su_education_record = DB::table('su_education_record as suer')
                                        ->select('suer.*','er.description')
                                        ->join('education_record as er','er.id','suer.education_record_id')
                                        ->where('suer.created_at','>=', $today)
                                        ->where('suer.is_deleted','0')
                                        ->where('er.status','1')
                                        ->where('suer.service_user_id', $service_user_id)
                                        ->orderBy('suer.id','desc')
                                        ->get();
            
        $su_mfc_record  = DB::table('su_mfc as smfc')
                                        ->select('smfc.*', 'm.description')
                                        ->join('mfc as m', 'm.id','smfc.mfc_id')
                                        ->where('smfc.created_at','>=', $today)
                                        ->where('smfc.is_deleted','0')
                                        ->where('m.status','1')
                                        ->where('smfc.service_user_id', $service_user_id)
                                        ->orderBy('smfc.id','desc')
                                        ->get();*/

        $earning_target = ServiceUserEarningTarget::getEarningTarget($service_user_id);
        
        $labels  = HomeLabel::getLabels();
        $earning_scheme_label_ids = ServiceUser::where('id',$service_user_id)->where('home_id',$home_id)->value('earning_scheme_label_id');
        if(!empty($earning_scheme_label_ids)){
            $earning_scheme_label_ids = explode(',', $earning_scheme_label_ids);
            $earning_scheme_labels = EarningSchemeLabel::where('home_id',$home_id)
                                                        ->where('deleted_at',null)
                                                        ->whereIn('id',$earning_scheme_label_ids)
                                                        ->get()
                                                        ->toArray(); 
        }else{
            $earning_scheme_labels=array();
        }
        //echo "<pre>"; print_r($earning_scheme_label_ids);
        //die;
        $record_score = EarningScheme::getRecordsScore($service_user_id);
        $earn_area_percent = EarningAreaPercentage::select('daily_record','education_record','living_skill','mfc')->first();
        $guide_tag = 'earning';
        // echo '<pre>'; print_r($earning_scheme_labels); die;
        return view('frontEnd.serviceUserManagement.earning_scheme', compact('service_user_id','earning_scheme','week_graph','total_stars','earn_history','booked_incentives','notifications','earning_target','labels','record_score','earn_area_percent','daily_score','form_pattern','patient','guide_tag','dynamic_forms','service_users','earning_scheme_labels'));
    }

    public function view_incentive($earning_category_id) { 

        $incentive = Incentive::select('incentive.id','name','stars','details','url','earning_scheme_category.title')
                                ->leftJoin('earning_scheme_category','earning_scheme_category.id','incentive.earning_category_id')
                                ->where('earning_category_id', $earning_category_id)
                                ->get()
                                ->toArray();
     
        foreach ($incentive as $key => $value) {
    
        echo   '<div class="incen-main">
                    <div class="">
                        <h4> '.$value['name'].' 
                            <a href="'.url('/service/earning-scheme/incentive/add?incentive_id='.$value['id']).'"  class="confirm_to_clndr" stars_need="'.$value['stars'].'">
                                <div class="pull-right">
                                    <span class="star-head pull-right">Add to Calendar 
                                    <button class="btn group-ico" type="submit"> <i class="fa fa-plus"></i> </button>
                                    </span>
                                </div>
                            </a>
                        </h4>
                    </div>
                    <p>'.$value['details'].'</p>
                    <a href="'.$value['url'].'" target="_blank" class="incen-link">'.$value['url'].'</a>
                    <div class="stars-sec">
                        <span class="star-head"> Stars : </span>';
                        for($i=1; $i <= $value['stars']; $i++) {
                            echo '<span><i class="fa fa-star"></i></span>';
                        }
                    echo '</div>
                </div>';
        } 
        die;
    }

    //add to Child availed incentives list 
    public function add_to_calendar(Request $request) {

        $data = $request->input();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
        if($su_home_id != $home_id){
            return redirect()->back()->with("error",UNAUTHORIZE_ERR);
        }

        $incentive = Incentive::find($data['incentive_id']);

        if(!empty($incentive)) {

            $su_star_info = ServiceUserEarningStar::where('service_user_id',$data['service_user_id'])->first();
            if(!empty($su_star_info)){
                $availble_star = $su_star_info->star;
            } else{
                return redirect()->back()->with("error", "You don't have enough stars to avail this incentive.");
            }
            
            if($availble_star >= $incentive->stars){    

                $clndr_incentive                  = new ServiceUserEarningIncentive;
                $clndr_incentive->service_user_id = $data['service_user_id'];
                $clndr_incentive->incentive_id    = $incentive->id;
                $clndr_incentive->star_cost       = $incentive->stars;
                $clndr_incentive->detail          = '';
                if($clndr_incentive->save()){

                    $su_remaining_star = $availble_star - $clndr_incentive->star_cost;
                    $su_star_info->star= $su_remaining_star;
                    $su_star_info->save();
                    
                    //saving notification start
                    //$notification->event_type      = 'SU_ER';
                    //saving notification end
                    $notification                             = new Notification;
                    $notification->service_user_id            = $data['service_user_id'];
                    $notification->event_id                   = $clndr_incentive->id;
                    $notification->notification_event_type_id = '3';
                    $notification->event_action               = 'SPEND_STAR';     
                    $notification->home_id                    = $home_id;
                    $notification->user_id                    = Auth::user()->id;                 
                    $notification->save();

                    //return redirect('/service/calendar/'.$data['service_user_id'])->with('success','Incentive added successfully.');
                    return redirect()->back()->with('success','Incentive added successfully.');
                } else{
                    return redirect()->back()->with('error', COMMON_ERROR);
                }
            } else{
                return redirect()->back()->with("error", "You don't have enough stars to avail this incentive.");
            }
        }      
    }

    public function remove_star(Request $request, $service_user_id = null){

        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($su_home_id != $home_id)    {
            return redirect()->back()->with("error",UNAUTHORIZE_ERR);
        }

        $su_star_info = ServiceUserEarningStar::where('service_user_id',$service_user_id)->first();
        $availble_star = $su_star_info->star;
        
        if($availble_star > 0)  {
            $su_remaining_star = $availble_star - 1;
            $su_star_info->star= $su_remaining_star;
            if($su_star_info->save())   {

                $data = $request->all();
                $earning_remove                  = new ServiceUserEarningRemove;
                $earning_remove->user_id         = Auth::user()->id;
                $earning_remove->service_user_id = $service_user_id;
                $earning_remove->detail          = $data['star_remove_detail'];
                $earning_remove->stars_removed   = '1';
                $earning_remove->save();
                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $service_user_id;
                $notification->event_id                   = $su_star_info->id;
                //$notification->event_type      = 'SU_ER';
                $notification->notification_event_type_id = '3';
                $notification->event_action               = 'REMOVE_STAR';     
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;                 
                $notification->save();
                //saving notification end

                return redirect()->back()->with('success','A star removed successfully.');
            } else{
                return redirect()->back()->with('error', COMMON_ERROR);                
            }            
        } else{

            return redirect()->back()->with('error', 'Sorry, No star is available to remove.');                
        }
    }

    public function set_su_earning_target(Request $request){

        $data = $request->input();
        //echo '<pre>'; print_r($data); die;
        $service_user_id = $data['service_user_id'];

        $earn_target = ServiceUserEarningTarget::getEarningTarget($service_user_id);
        
        //if new earn target is same as the previous target then do nothing 
        if($earn_target == $data['target']){
            return redirect()->back()->with('success','Earning target set successfully.');
        }
    
        $earn_target                  = new ServiceUserEarningTarget;
        $earn_target->service_user_id = $service_user_id;
        $earn_target->target          = $data['target'];            
    
        if($earn_target->save()){
            return redirect()->back()->with('success','Earning target set successfully.');
        } else{
            return redirect()->back()->with('error',COMMON_ERROR);
        }

        /*Note
            Everytime when a target is set for su then a new entry is added to the table.
            And while retriving time latest taget entry is retrieved
        */
    }

    public function incentive_suspend(Request $request) {

        $data = $request->input();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
        if($su_home_id != $home_id){
            return redirect()->back()->with("error",UNAUTHORIZE_ERR);
        }

        $date = date('Y-m-d', strtotime($data['suspended_date']));

        $suspend_incentive                           = New ServiceUserIncentiveSuspend;
        $suspend_incentive->su_earning_incentive_id  = $data['su_earning_incentive_id'];
        $suspend_incentive->date                     = $date;
        $suspend_incentive->detail                   = $data['suspended_detail'];

        if($suspend_incentive->save()) {
            return redirect()->back()->with('success','Incentive suspended successfully.');
        } else {
            return redirect()->back()->with('error', COMMON_ERROR);   
        } 
    }

    public function view_suspension($suspended_id = null) {
      $suspend_info = DB::table('su_incentive_suspend as su_is')
                            ->select('su_is.id as su_spd_id','su_is.date','su_is.detail')
                            ->where('su_is.id', $suspended_id)
                            ->where('is_cancelled','0')
                            //->where('surmp.home_id', $home_id)
                            ->first();
        $Date = date("d-m-Y", strtotime($suspend_info->date)); 

        if(!empty($suspend_info)) {
            $result['response']  = true;
            $result['su_spd_id'] = $suspend_info->su_spd_id;
            $result['date']      = $Date;

            $result['detail']    = $suspend_info->detail;
        } else {
            $result['response']  = false;
        }
        return $result;
    }

    // public function edit_suspension(Request $request) {
        
    //     $data = $request->input();

    //     $su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
    //     if($su_home_id != Auth::user()->home_id){
    //         return redirect()->back()->with("error",UNAUTHORIZE_ERR);
    //     }

    //     $suspended_id = $data['su_incentive_suspended_id'];
    //     $edit_suspend_incentive    = ServiceUserIncentiveSuspend::find($suspended_id);
    //     if(!empty($edit_suspend_incentive)) {

    //         $date = date('Y-m-d', strtotime($data['edit_suspended_date']));
    //         $edit_suspend_incentive->date                     = $date;
    //         $edit_suspend_incentive->detail                   = $data['edit_suspended_detail'];

    //         if($edit_suspend_incentive->save()) {
    //             return redirect()->back()->with('success','Incentive suspension updated successfully.');
    //         } else {
    //             return redirect()->back()->with('error', COMMON_ERROR);   
    //         } 
    //     } else {
    //         return redirect()->back()->with(COMMON_ERROR);
    //     }
    // }


    public function remove_suspension(Request $request) {
          
        $data = $request->input(); 
        $suspend_id = $data['suspended_id'];
        $service_user_id = $data['service_user_id'];

        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if($su_home_id != $home_id){
            return redirect()->back()->with("error",UNAUTHORIZE_ERR);
        }

        $remove_suspension = ServiceUserIncentiveSuspend::where('id',$suspend_id)->update(['is_cancelled' => '1']);
        if(!empty($remove_suspension)) {
            return redirect()->back()->with('success', 'Incentive suspension remove successfully.');
        } else {
            return redirect()->back()->with(COMMON_ERROR);
        }
    }
}
