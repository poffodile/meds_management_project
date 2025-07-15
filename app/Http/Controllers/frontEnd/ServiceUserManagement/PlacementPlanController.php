<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use DB,Auth;
use App\ServiceUserPlacementPlan, App\Notification, App\FormBuilder, App\ServiceUser, App\HomeLabel, App\DynamicFormLocation, App\DynamicForm, App\DynamicFormBuilder;

class PlacementPlanController extends ServiceUserManagementController
{
    public function __construct() {
        //number of records per page
        $this->active    = '5';
        $this->completed = '7';
        $this->pending   = '2';
    }

    public function index(Request $request, $service_user_id = null) {

        //check su home id
        $su_home_id     = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $usr_home_id    = $ex_home_ids[0];
        if($su_home_id != $usr_home_id){
            return redirect('/')->with('error',UNAUTHORIZE_ERR); 
        }
        
        //check su home id end
        
        $today = date('Y-m-d');
        
        $completed_targets = DB::table('su_placement_plan')
                                ->where('service_user_id',$service_user_id)
                                ->where('status','1')
                                ->paginate($this->completed);
        
        $active_targets = DB::table('su_placement_plan')
                                ->where('service_user_id',$service_user_id)
                                ->whereDate('date', '>=', $today)
                                ->orderBy('date', 'asc')
                                ->where('status','0')
                                ->paginate($this->active);
        
        $pending_targets = DB::table('su_placement_plan')
                                ->where('service_user_id',$service_user_id)
                                ->whereDate('date', '<=', $today)
                                ->where('status','0')
                                ->paginate(100);

        $service_user = ServiceUser::select('id', 'name')
                                ->where('id', $service_user_id)
                                ->first();
        // update notify
        $updatenotify = array('read_notify'=>1);
        DB::table('su_placement_plan')->where('service_user_id',$service_user_id)->update($updatenotify);
        // update notify

        /*$message = 'You have ' .$count. ' pending targets';
                $notification                  = new Notification;
                $notification->service_user_id = $service_user_id;
                // $notification->title        = $data['title'];
                $notification->event_type      = 'P';
                $notification->message         = $message;
                $notification->save();*/

        // echo "<pre>"; print_r($notification); die;                

        //getting form patterns
        /*$form     =  FormBuilder::showForm('placement_plan');
        $response = $form['response'];
        if($response == true){
            $form_pattern['placement_plan'] = $form['pattern'];
        } else{
            $form_pattern['placement_plan'] = '';
        }*/
        $form_pattern['placement_plan'] = '';
        $placement_plan_label = HomeLabel::getLabelbyTag('placement_plan');
        $guide_tag = 'placement';

        return view('frontEnd.serviceUserManagement.placement_plan', compact('service_user_id', 'active_targets','completed_targets','pending_targets','form_pattern','service_user','placement_plan_label','guide_tag'));
    }

    public function completed_targets(Request $request, $service_user_id = null) {

        //check su home id
        $su_home_id     = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $usr_home_id    = $ex_home_ids[0];
        // if($su_home_id != $usr_home_id){
        //     die;
        // }
        if($su_home_id != $usr_home_id){
            return redirect('/')->with('error',UNAUTHORIZE_ERR);
        }
        //check su home id end

        $today = date('Y-m-d');
        $completed_targets = DB::table('su_placement_plan')
                                ->where('service_user_id', $service_user_id)
                                ->where('status','1')
                                ->paginate($this->completed);

        echo '<div class="completed-box">';
        foreach($completed_targets as $key => $value)  {

        echo   '<div class="form-group col-md-12 col-sm-12 col-xs-12">
                    '.$value->task.' <span class="color-green m-l-15"><i class="fa fa-check"></i></span>
                </div> ';
        }
        echo '</div>';
        echo '<div class="col-md-12 col-sm-12 col-xs-12 clearfix completed-target-list-link">';
        echo $completed_targets->links();
        echo '</div>';
        die;
    }

    public function active_targets(Request $request, $service_user_id = null)  {
        
        //check su home id
        $su_home_id     = ServiceUser::where('id',$service_user_id)->value('home_id');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $usr_home_id    = $ex_home_ids[0];
        // if($su_home_id != $usr_home_id){
        //     die;
        // }
        if($su_home_id != $usr_home_id){
            return redirect('/')->with('error',UNAUTHORIZE_ERR);
        }
        //check su home id end

        $today = date('Y-m-d');
        $active_targets = DB::table('su_placement_plan')
                            ->where('service_user_id',$service_user_id)
                            ->whereDate('date', '>=', $today)
                            ->orderBy('date', 'asc')
                            ->where('status','0')
                            ->paginate($this->active);
        
        echo '<div class="active-box">';

        foreach($active_targets as $key=>$value)    {

            $current_month = date('m');
            $target_month  = date('m',strtotime($value->date));

            if($target_month == $current_month){
                $clr_class = 'orange-bg';
            } else{
                $clr_class = 'bg-darkgreen';
            }

            echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel p-0 delete-row">
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <input type="text" class="form-control trans" value="'.$value->task.'" disabled="disabled" maxlength="255">
                    </div>
                    <div class="date-settin">
                        <div class="input-group popovr">
                            <span> Date:&nbsp; </span>
                            <label class="btn label-danger pp-txt-clr '.$clr_class.' "> '.date('d M',strtotime($value->date)).' </label>
                            <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul type="none" class="pop-notification" target_id="'.$value->id.'" target_task="'.$value->task.'">
                                        <li class="view_active_target_btn active-targets"> <a href="#"> <span><i class="fa fa-pencil"></i> </span> View/Edit </a></li>
                                        <li> <a href="'.url('/service/placement-plan/mark-complete/'.$value->id).'" target_id="'.$value->id.'"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>
                                        <li class="view_qqa_review_btn" qqa="'.$value->qqa_review.'"> <a href="#"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> QQA Review </a></li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>';
        } 
        
        echo '</div>';
        echo '<div class="col-md-12 col-sm-12 col-xs-12 clearfix active-target-list-link">';
        echo $active_targets->links();
        echo '</div>';
        die;
    }

    public function mark_complete($target_id = null)    {

        $su_pp = ServiceUserPlacementPlan::where('id', $target_id)->first();
        
        if(!empty($su_pp)){

            $su_home_id = ServiceUser::where('id',$su_pp->service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            if($home_id != $su_home_id){
                return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            }
            
            $su_pp->status = 1;
        
            if($su_pp->save()) {
    
                $completed_target = ServiceUserPlacementPlan::select('id','service_user_id')
                                        ->where('id', $target_id)
                                        ->first();

                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $completed_target->service_user_id;
                $notification->event_id                   = $completed_target->id;
               // $notification->event_type      = 'SU_PP';
                $notification->notification_event_type_id = '4';
                $notification->event_action               = 'MARK_COMPLETE'; 
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;                     
                $notification->save();
                //saving notification end
                return redirect()->back()->with('success','Target Updated Successfully');
            }   else  {
                return redirect()->back()->with('error',COMMON_ERROR);
            }
        }
    }

    /*public function mark_active($target_id = null)  {

        $today = date('Y-m-d');
        if(!empty($target_id))  {

            $updated = ServiceUserPlacementPlan::select('id','service_user_id', 'task', 'status')->where('id', $target_id)->update(['date' => $today]);
            $updated_target = ServiceUserPlacementPlan::select('id','service_user_id', 'task', 'status')->where('id', $target_id)->first();
            $task = $updated_target->task;
            $service_user_id = $updated_target->service_user_id;

            if($updated)    {
                
                //saving notification start
                $notification                  = new Notification;
                $notification->service_user_id = $data['service_user_id'];
                $notification->event_id        = $target_id;
                $notification->event_type      = 'SU_PP';
                $notification->event_action    = 'ACTIVE';                
                $notification->save();
                //saving notification end

                return redirect()->back()->with('success','Target Activated Successfully');
            } else  {
                return redirect()->back()->with('error','Some Error Occured');
            }
        }
    }*/
    /*public function mark_active($target_id = null)  {

        // echo $target_id; die;
        $today = date('Y-m-d');
        if(!empty($target_id)){
            $updated = ServiceUserPlacementPlan::where('id', $target_id)->update(['date' => $today]);
    
            if($updated){
                return redirect()->back()->with('success','Target Updated Successfully');
             } else{
                return redirect()->back()->with('error','Some Error Occured');
            }
        }
    }*/

    public function pending_targets(Request $request, $service_user_id = null) {
        
        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        // if(Auth::user()->home_id != $su_home_id){
        //     die; 
        // }
        $usr_home_id    = Auth::user()->home_id;
        $ex_usr_home_id=explode(',',$usr_home_id);
        if (!in_array($su_home_id, $ex_usr_home_id)) {
            return redirect('/')->with('error',UNAUTHORIZE_ERR);
        }            

        $today = date('Y-m-d');
        $pending_targets = DB::table('su_placement_plan')
                            ->where('service_user_id',$service_user_id)
                            ->whereDate('date', '<=', $today)
                            ->where('status','0')
                            ->orderBy('date', 'asc')
                            ->paginate($this->pending);

        echo '<div class="pending-box">';
        foreach($pending_targets as $key => $value)  { 
            echo   '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            '.$value->task.'
                            <span class="m-l-15 clr-blue settings setting-sze">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul type="none" class="pop-notification" target_id="'.$value->id.'" target_task="'.$value->task.'">
                                        <li class="view_active_target_btn active-targets"> <a href="#"> <span> <i class="fa fa-pencil"></i> </span> Mark Active </a> </li>
                                        <li> <a href="'.url('/service/placement-plan/mark-complete/'.$value->id).'" target_id="'.$value->id.'"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>
                                        <li class="view_qqa_review_btn" qqa="'.$value->qqa_review.'"> <a href="#"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> QQA Review </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>';
        }
        echo '</div>';
        echo '<div class="col-md-12 col-sm-12 col-xs-12 clearfix pending-target-list-link">';
        echo $pending_targets->links();
        echo '</div>';
        die;
    }

    public function add(Request $request) { 

        if($request->isMethod('post'))  {

            $today = date('Y-m-d');
            $data = $request->input();
            //echo "<pre>"; print_r($data); die;
            $date = date('Y-m-d', strtotime($data['date']));
            
            if($date < $today)  {
                return redirect()->back()->with('error', "Past targets can't be added");
            }  else    {
                
                $service_user_id = $data['service_user_id'];

                $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
                $home_ids = Auth::user()->home_id;
                $ex_home_ids = explode(',', $home_ids);
                $home_id=$ex_home_ids[0];
                if($home_id != $su_home_id){
                    return redirect('/')->with('error',UNAUTHORIZE_ERR); 
                }

                $placement_plan                  = new ServiceUserPlacementPlan;
                $placement_plan->service_user_id = $service_user_id;
                $placement_plan->task            = $data['task'];
                $placement_plan->date            = $date;
                $placement_plan->description     = $data['description'];
                $placement_plan->home_id         = $home_id;
                $placement_plan->formdata        = '';
                $placement_plan->qqa_review      = '';

                if($placement_plan->save()) {

                    //saving notification start
                    $notification                             = new Notification;
                    $notification->service_user_id            = $service_user_id;
                    $notification->event_id                   = $placement_plan->id;
                    // $notification->event_type      = 'SU_PP';
                    $notification->notification_event_type_id = '4';
                    $notification->event_action               = 'ADD';     
                    $notification->home_id                    = $home_id;
                    $notification->user_id                    = Auth::user()->id;                 
                    $notification->save();
                    //saving notification end

                    $message = 'A Placement Plan "' .$placement_plan->task. '" added';
                    return redirect('/service/placement-plans/'.$service_user_id)->with('success', 'Target added to Active targets successfully.');
                } else {
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
            }
        }   
    }




    public function view_target(Request $request, $active_target_id=null) {

        $active_target = ServiceUserPlacementPlan::select('su_placement_plan.id as su_placement_plan_id','su_placement_plan.task','su_placement_plan.service_user_id','su_placement_plan.description','su_placement_plan.date','d.title as d_title','d.date as d_date', 'd.time as d_time','d.details as d_details', 'd.pattern_data as d_pattern','su_placement_plan.dynamic_form_id','d.location_id','d.alert_date as d_alert_date','d.alert_status as d_alert_status')
                                ->leftJoin('dynamic_form as d','d.id','su_placement_plan.dynamic_form_id')
                                ->where('su_placement_plan.id',$active_target_id)
                                ->first();

        // echo "<pre>"; print_r($active_target); die;

        if(!empty($active_target)){

            if(!empty($active_target->dynamic_form_id)) {
                $dyn_form = DynamicForm::showFormWithValue($active_target->dynamic_form_id, true);
                // echo "<pre>"; print_r($dyn_form); die;
            } else {
                $dyn_form['form_data'] = '';
                $dyn_form['form_builder_id'] = '0';
            }
            // echo "<pre>"; print_r($dyn_form); die;
            $su_home_id = ServiceUser::where('id',$active_target->service_user_id)->value('home_id');
            $usr_home_id    = Auth::user()->home_id;
            $ex_usr_home_id=explode(',',$usr_home_id);
            if (!in_array($su_home_id, $ex_usr_home_id)) {
                return redirect('/')->with('error',UNAUTHORIZE_ERR);
            }
            // if(Auth::user()->home_id != $su_home_id){
            //     $result['response'] = false;
            //     $result['error']    = 'AUTH';
            //     return $result; 
            // }

            /*$formdata = $active_target->formdata;
            $form_response = FormBuilder::showFormWithValue('placement_plan',$formdata,true);
            
            if($form_response['response'] == true){
                $placement_plan_form = $form_response['pattern'];
            } else {
                $placement_plan_form = '';
            }*/
            $placement_plan_form = '';
            
            if(!empty($active_target)){

                $formdata            = '';
                $result['response']  = true;
                $result['task']      = $active_target->task;
                $result['description']  = $active_target->description;
                $result['date']      = date('d-m-Y' ,strtotime($active_target->date));
                $result['pp_form']   = $placement_plan_form;
                $result['d_title']   = $active_target->d_title;
                $result['d_date']    = $active_target->d_date;
                $result['d_time']    = $active_target->d_time;
                $result['d_alert_status']= $active_target->d_alert_status;
                $result['d_alert_date']= $active_target->d_alert_date;
                $result['d_details'] = $active_target->d_details;
                $result['d_pattern'] = $active_target->d_pattern;
                $result['dyn_form']  = $dyn_form['form_data'];
                $result['form_builder_id'] = $dyn_form['form_builder_id'];
            }   else {
                $result['response'] = false;
                $result['error']    = 'NOT_FOUND';
            }
            return $result; 
        }
    }

    public function edit(Request $request) { 
        
        if($request->isMethod('post'))  {
            // echo "<pre>"; print_r($request->input()); die;
            $today = date('Y-m-d');
            $data = $request->input();
            // echo "<pre>"; print_r($data); die;
            // $date = date('Y-m-d', strtotime($data['target_date']));
            $date = null ;

            // if(isset($data['formdata'])){
            //     $formdata = json_encode($data['formdata']);
            // } else{
            //     $formdata = '';
            // }
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            if($date < $today)  {
                return redirect()->back()->with('error', "Past Date for targets can not be set.");
            }  else    {
            
            if(!empty($data)){
                    
                if(isset($data['formdata'])){
                    $formdata = json_encode($data['formdata']);
                } else{
                    $formdata = '';
                }

                $placement_plan = ServiceUserPlacementPlan::where('id',$data['placement_plan_id'])->first();
                
                // if($placement_plan->dynamic_form_id != 'Null') {
                    $dynamic_form_id = $placement_plan->dynamic_form_id;
                    // echo $dynamic_form_id; die;
                    if($data['title'] || $data['time'] || $data['details'] === "" ){
                        $data['details'] = $data['time'] = $data['title'] = null;
                    }
                    if(!empty($dynamic_form_id)) {
                        $dynamic_form       = DynamicForm::find($dynamic_form_id);
                        if(!empty($dynamic_form)) {
                          
                            $dynamic_form->title            = $data['title'];
                            $dynamic_form->time             = $data['time']; 
                            $dynamic_form->details          = $data['details']; 
                            $dynamic_form->dynamic_form_id  = $data['dynamic_form_builder_id'];
                            $dynamic_form->pattern_data     = $formdata; 
                            if(!empty($data['date'])){
                                $dynamic_form->date         = date('Y-m-d',strtotime($data['date']));   
                            }    
                            $dynamic_form->save();
                            
                        } else {
                            return redirect()->back()->with('error','Task could not be Updated Successfully.'); 

                        }
                    }
               /* }*/ else {
                //echo "111"; die;
                    $form                   = new DynamicForm;
                    $form->home_id          = $home_id; 
                    $form->user_id          = Auth::user()->id;
                    $form->form_builder_id  = $data['dynamic_form_builder_id']; 
                    // $form->user_id          = $data['user_id']; 
                    $form->service_user_id  = $data['service_user_id']; 
                    $form->location_id      = $data['location_id']; 
                    $form->title            = $data['title'];
                    $form->time             = $data['time']; 
                    $form->details          = $data['details']; 
                    $form->pattern_data     = $formdata; 
                    
                    if(isset($data['alert_status'])) {
                         if($data['alert_status'] == '1') {
                            if(!empty($data['alert_date'])) {
                                $form->alert_date   = date('Y-m-d',strtotime($data['alert_date']));
                            } else {
                                $form->alert_date   = date('Y-m-d');
                            }
                        }
                    }
                   

                    if(!empty($data['date'])){
                        $form->date         = date('Y-m-d',strtotime($data['date']));   
                    }           
                    
                    if($form->save()){

                        $dynamic_form_id = $form->id;
                        /*$location_id = $data['location_id'];
                        $location_tag = DynamicFormLocation::where('id', $location_id)->value('tag');
                        //echo "<pre>"; print_r($form_location); die;
                        $notification_event_type_id = DB::table('notification_event_type')->where('table_linked','LIKE', 'su_'.$location_tag)->value('id');
                        if(!empty($notification_event_type_id)) {
                            //echo $notification_event_type_id; die;

                            //saving notification start
                            $notification                             = new Notification;
                            $notification->service_user_id            = $data['service_user_id'];
                            $notification->event_id                   = $form->id;
                            $notification->notification_event_type_id = $notification_event_type_id;
                            $notification->event_action               = 'ADD';      
                            $notification->home_id                    = Auth::user()->home_id;
                            $notification->user_id                    = Auth::user()->id;        
                            $notification->save();
                            //saving notification end
                        }*/

                    } else{
                        return redirect()->back()->with('error', UNAUTHORIZE_ERR);
                    }

                }  

            } else {
                return redirect()->back()->with('error', UNAUTHORIZE_ERR);
            }


            $su_home_id = ServiceUser::where('id',$placement_plan->service_user_id)->value('home_id');
            // if(Auth::user()->home_id != $su_home_id){
            //     return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            // }
            $usr_home_id    = Auth::user()->home_id;
            $ex_usr_home_id=explode(',',$usr_home_id);
            if (!in_array($su_home_id, $ex_usr_home_id)) {
                return redirect('/')->with('error',UNAUTHORIZE_ERR);
            }

            // $placement_plan                  = new ServiceUserPlacementPlan;
            $placement_plan->task            = $data['target_task'];
            $placement_plan->date            = $date;
            $placement_plan->description     = $data['target_description'];
            $placement_plan->formdata        = '';
            $placement_plan->dynamic_form_id = $dynamic_form_id;

            //echo "<pre>"; print_r($placement_plan); die;
            if($placement_plan->save()) {

                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $placement_plan->id;
                //$notification->event_type      = 'SU_PP';
                $notification->notification_event_type_id = '4';
                $notification->event_action               = 'EDIT';   
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;               
                $notification->save();
                //saving notification end

                return redirect('/service/placement-plans/'.$data['service_user_id'])->with('success', 'Target updated successfully.');
            } else {
                return redirect()->back()->with('error', COMMON_ERROR);
            }
            }
        }   
    }

    public function add_qqa_review(Request $request) {
        if($request->isMethod('post')) {

            $data = $request->all();

            $placement_plan = ServiceUserPlacementPlan::where('id',$data['placement_plan_id'])->first();

            $su_home_id = ServiceUser::where('id',$placement_plan->service_user_id)->value('home_id');
            // if(Auth::user()->home_id != $su_home_id){
            //     return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            // }
            $usr_home_id    = Auth::user()->home_id;
            $ex_usr_home_id=explode(',',$usr_home_id);
            if (!in_array($su_home_id, $ex_usr_home_id)) {
                return redirect('/')->with('error',UNAUTHORIZE_ERR);
            }
            
            $placement_plan->qqa_review = $data['qqa_review'];            
 
            if($placement_plan->save()){
                return redirect('/service/placement-plans/'.$placement_plan->service_user_id)->with('success', 'Target QQA review updated successfully.');
            } else{
                return redirect()->back()->with('error', COMMON_ERROR);
            }
        }
    }
}