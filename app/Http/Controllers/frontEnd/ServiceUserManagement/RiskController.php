<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUserRisk, App\Risk, App\FormBuilder, App\ServiceUser, App\ServiceUserRmp, App\ServiceUserIncidentReport, App\DynamicForm, App\Notification, App\User, App\ServiceUserCareTeam, App\BodyMap,App\DynamicFormBuilder,App\HomeLabel;
use DB,Auth;
use Illuminate\Support\Facades\Mail;

class RiskController extends ServiceUserManagementController
{
    public function index(Request $request,$service_user_id = null){
        $data = $request->input();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        // $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id','name')
                            ->where('home_id',$home_id)
                            ->where('is_deleted','0')
                            ->get();       
        $staff_members  =   User::where('is_deleted','0')
                                ->where('home_id', $home_id)
                                ->get();
        
           $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
           $service_user_name = ServiceUser::where('id',$service_user_id)->value('name');
          
           $today = date('Y-m-d');
           //filter
           

             
            if(!empty($data)){
                
                // $log_book_records = DB::table('log_book')
                //                 ->select('log_book.*', 'user.name as staff_name','category.color as category_color')
                //                 ->whereIn('log_book.id',$su_logs)
                //                 ->join('user', 'log_book.user_id', '=', 'user.id')
                //                 ->join('category', 'log_book.category_id', '=', 'category.id')
                //                 ->orderBy('date','desc');
                $log_book_records =  DB::table('su_risk as sur')
                                ->select('sur.*','d.*', 'u.name as staff_name','r.description','r.icon')
                                ->join('dynamic_form as d','d.id','sur.dynamic_form_id')
                                ->join('risk as r','r.id','sur.risk_id')
                            ->join('service_user', 'sur.service_user_id', '=', 'service_user.id')
                            ->leftJoin('user as u','u.id','d.user_id')
                                ->where('sur.service_user_id',$service_user_id)
                                ->where('sur.home_id',$home_id)
                                ->where('r.is_deleted',"0")
                                ->orderBy('sur.created_at','desc');
        
                                       //->whereDate('su_health_record.created_at', '=', $today)
                
                if(isset($request->start_date) && $request->start_date!='null') {
                    $log_book_records = $log_book_records->whereDate('sur.created_at', '>=', $request->start_date);
                    // Log::info("Start Date Logs.");
                    // Log::info($log_book_records->get()->toArray());
                }
                if(isset($request->end_date) && $request->end_date!='null') {
                    $log_book_records = $log_book_records->whereDate('sur.created_at', '<=', $request->end_date);       
                    // Log::info("End Date Logs.");
                    // Log::info($log_book_records->get()->toArray());
                }
                if(isset($request->keyword) && $request->keyword!='null') {
                    $log_book_records = $log_book_records->where('d.details', 'like', '%'.$request->keyword.'%');       
                    // Log::info("End Date Logs.");
                    // Log::info($log_book_records->get()->toArray());
                }


                //$log_book_records = $log_book_records->get()->toArray();
                $log_book_records = $log_book_records->get();
                $log_book_records = collect($log_book_records)->map(function($x){ return (array) $x; })->toArray();
                //print_r($log_book_records);
                //die;
                
                return compact('log_book_records');
                
            }
           
           //filter    
        //    $log_book_records = DB::table('su_health_record')
        //                                ->select('su_health_record.*', 'service_user.name as staff_name')
        //                                ->where('su_health_record.service_user_id',$service_user_id)
        //                                ->whereDate('su_health_record.created_at', '=', $today)
        //                                ->join('service_user', 'su_health_record.service_user_id', '=', 'service_user.id')
        //                                ->orderBy('su_health_record.created_at','desc')->get();
           $log_book_records = DB::table('su_risk as sur')
                                ->select('sur.*','d.*', 'u.name as staff_name','r.description','r.icon')
                                ->join('dynamic_form as d','d.id','sur.dynamic_form_id')
                                ->join('risk as r','r.id','sur.risk_id')
                               ->whereDate('sur.created_at', '=', $today)
                               ->join('service_user', 'sur.service_user_id', '=', 'service_user.id')
                               ->leftJoin('user as u','u.id','d.user_id')
                                ->where('sur.service_user_id',$service_user_id)
                                ->where('sur.home_id',$home_id)
                                ->where('r.is_deleted',"0")
                                ->orderBy('sur.created_at','desc')->get();
            // echo "<pre>";
            //                     print_r($log_book_records);
            // die;
            $log_book_records = collect($log_book_records)->map(function($x){ return (array) $x; })->toArray();
            
            ///////////////////////////////////////////////////////////////////
           
        
        
        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');
        if($su_home_id != $home_id){
            echo ''; die;
        }

        $risks_query = ServiceUserRisk::select('su_risk.id','su_risk.risk_id','su_risk.created_at','r.description','su_risk.status')
                    ->join('risk as r','r.id','su_risk.risk_id')
                    ->where('su_risk.service_user_id',$service_user_id);

        if(isset($_GET['search'])) { 
            $risks_query = $risks_query->where('r.description','like','%'.$_GET['search'].'%');
        }
        // sourabh
        if(isset($_GET['start_date']) && $_GET['start_date']!='null'){
            
            $risks_query = $risks_query->whereDate('su_risk.created_at','>=',$_GET['start_date']);
        }
        if(isset($_GET['end_date']) && $_GET['end_date']!='null') {
            $risks_query = $risks_query->whereDate('su_risk.created_at','<=',$_GET['end_date']);
        }
        if(isset($_GET['category_id']) && $_GET['category_id']!="all"){           
            $risks_query = $risks_query->where('su_risk.status','=',$_GET['category_id']);          
        }
        if(isset($_GET['keyword']) && $_GET['keyword']!=''){
            $risks_query = $risks_query->where('r.description','like','%'.$_GET['keyword'].'%');            
        }
        // sourabh
               
        $risks = $risks_query->orderBy('su_risk.id','desc')
                    ->paginate(10);
        // echo '<pre>'; print_r($risks); die;

        // foreach($risks as $risk){

        //     $risk_status = $risk->status;
        //     if($risk_status == '1'){         //historic
        //         $clr_color = 'orange-clr';

        //     } else if($risk_status == '2'){  //live
        //         $clr_color = 'red-clr';
            
        //     } else{                             //no risk
        //         $clr_color = 'darkgreen-clr';
        //     }

        //     // echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 ">
        //     //         <div class="form-group col-md-12 col-sm-12 col-xs-12 r-p-0">
        //     //             <div class="input-group popovr ">
        //     //                 <a  class="form-control curs-point '.$clr_color.' view-risk" su_risk_id="'.$risk->id.'" data-toggle="modal" data-dismiss="modal" data-target="#riskViewModal" >'.$risk->description.'</a>
        //     //             </div>
        //     //         </div>
        //     //     </div>';

        // }
        // if($risks->links() != '') {
        //     // echo '<div class="risk-pagination m-l-15">';
        //     // echo $risks->links();
        //     // echo '</div>';
        // }
        // die;
        $labels = HomeLabel::getLabels($home_id);
        //getting form patterns
        $form_pattern['bmp_rmp'] = '';
        $form_pattern['risk'] = '';
        $form_pattern['su_rmp'] = '';
        $form_pattern['su_bmp'] = '';
        $form_pattern['su_mfc'] = '';
        $form_pattern['incident_report'] = '';
        $dynamic_forms = DynamicFormBuilder::getFormList();
        $patient = DB::table('service_user')->where('id',$service_user_id)->where('is_deleted','0')->first();
        return view('frontEnd.serviceUserManagement.risk', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'service_users','staff_members','risks','dynamic_forms','form_pattern','labels','patient'));
    }

    public function change_risk_status(Request $request){

        $data = $request->all();
        //echo "<pre>"; print_r($data); die;
        if($request->isMethod('post')) {

            $su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');

            if($su_home_id != Auth::user()->home_id){
                //return redirect('/')->with('error',UNAUTHORIZE_ERR); 
                $result['response'] = 'AUTH_ERR';
                return $result;
            }

            $form_insert_id = DynamicForm::saveForm($data);
            
            $risk = new ServiceUserRisk;
            $risk->service_user_id  = $data['service_user_id'];
            $risk->risk_id          = $data['risk_id'];
            $risk->status           = $data['new_risk_status'];
            $risk->dynamic_form_id  = $form_insert_id;

            //$risk->formdata         = $formdata;
            $risk->home_id          = $su_home_id;
            // echo "<pre>"; print_r($risk); die;

            if($risk->save()) {
                $result['response'] = 'true';
                $result['status']   = $risk->status;
                $result['su_risk_id'] = $risk->id;

                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $risk->id;
                $notification->notification_event_type_id = '11';
                $notification->event_action               = 'ADD';      
                $notification->home_id                    = Auth::user()->home_id;
                $notification->user_id                    = Auth::user()->id;        
                $notification->save();
                //saving notification end

            } else{
                $result['response'] = 'false';
                $result['status']   = $risk->status;
            }

            //get overall su risk status
            $result['overall_risk_status'] = Risk::overallRiskStatus($data['service_user_id']);

            return $result;
        }
    }   

    public function view($risk_id = null) {/*$su_risk_id*/

        // $su_risk_id from su_risk table
        $risk = DB::table('su_risk as sur')
                    ->select('sur.id as sur_id','sur.risk_id','sur.created_at','sur.status','sur.dynamic_form_id','sur.rmp_id','sur.incident_report_id','r.description','r.home_id')
                    ->where('sur.id',$risk_id)
                    ->join('risk as r','r.id','sur.risk_id')
                    //->leftjoin('su_rmp','su_rmp.su_risk_id','sur.id')
                    ->first();

        /*$risk = DB::table('su_risk as sur')
                    ->select('sur.id as sur_id','sur.risk_id','sur.created_at','sur.status','sur.formdata','r.description','r.home_id','su_rmp.id as su_rmp_id')
                    ->where('sur.id',$risk_id)
                    ->join('risk as r','r.id','sur.risk_id')
                    ->leftjoin('su_rmp','su_rmp.su_risk_id','sur.id')
                    ->first();*/
        //echo "<pre>"; print_r($risk); die;
        if(!empty($risk)){

            $staff_id         = Auth::user()->id;

            $service_user_id  = ServiceUserRisk::where('id', $risk->sur_id)->value('service_user_id');
            // echo $service_user_id; die;
            $sel_injury_parts = BodyMap::select('id','sel_body_map_id','service_user_id','staff_id','su_risk_id')
                                        ->where('service_user_id',$service_user_id)
                                        ->where('staff_id',$staff_id)
                                        ->where('su_risk_id',$risk->sur_id)
                                        ->where('is_deleted','0')
                                        ->get()
                                    ->toArray();

            // echo "<pre>"; print_r($sel_injury_parts); die;
            $risk_home_id = $risk->home_id;
            if($risk_home_id != Auth::user()->home_id){
                $result['response'] = 'AUTH_ERR';
                return $result;
            }

            if($risk->status == '1'){
                $status_txt = 'Historic';
            } else if($risk->status == '2'){
                $status_txt = 'Live';
            }else{
                $status_txt = 'No Risk';
            }

            // $formdata = $risk->formdata;
            // $risk_form_response = FormBuilder::showFormWithValue('change_risk',$formdata);
            $dyn_res = DynamicForm::showFormWithValue($risk->dynamic_form_id, false);
            if($dyn_res['response'] == true){
                $risk_form       = $dyn_res['form_data'];
                $form_builder_id = $dyn_res['form_builder_id'];
            } else{
                $risk_form = '';
                $form_builder_id = '';
            }            

            /*if($risk_form_response == true){
                $risk_form = $risk_form_response['pattern'];
            } else{
                $risk_form = '';
            }*/

            $result['response']         = true;
            $result['sur_id']           = $risk->sur_id;

            $result['form_builder_id']  = $form_builder_id;            
            $result['su_rmp_id']        = $risk->rmp_id;            
            $result['dynamic_form_id']  = $risk->dynamic_form_id;            
            $result['incident_report_id'] = $risk->incident_report_id;            
            
            $result['risk_txt']         = $risk->description;
            $result['risk_status']      = $risk->status;            
            $result['risk_status_txt']  = $status_txt;            
            $result['risk_form']        = $risk_form;            
            $result['created_at']       = date('d-m-Y g:i A',strtotime($risk->created_at));
            $result['sel_injury_parts'] = json_encode($sel_injury_parts);            

        } else{
            $result['response']         = false;            
        }
        return $result;
    }

    public function add_rmp_risk(Request $request) {
        
        $data = $request->all();
        //echo '<pre>'; print_r($data); die;
        if($request->isMethod('post')) {

            $home_id         = Auth::user()->home_id;

            $form_insert_id  = DynamicForm::saveForm($data);
            $su_risk         = ServiceUserRisk::where('id',$data['su_risk_id'])->first();
            $su_risk->rmp_id = $form_insert_id;

            if($su_risk->save()) {

                //$this->sendRmpNotification($data);
                $notify_method = $data['notify_method'];
                $notify_person = isset($data['notify_person']) ? $data['notify_person'] : '';

                $service_user = ServiceUser::select('name')->where('id',$su_risk->service_user_id)->where('is_deleted','0')->first();

                if($notify_method == 'EMAIL') {
                    if($notify_person == 'STAFF') {

                        $emails = User::select('email','name')
                                    ->where('home_id',$home_id)
                                    ->where('status','1')
                                    ->where('is_deleted','0')
                                    ->get()
                                    ->toArray();

                        //echo '<pre>'; print_r($emails); die;
                    } else if($notify_person == 'CARETEAM') {

                        $emails = ServiceUserCareTeam::select('email','name')->where('is_deleted','0')->get()->toArray();                        
                    }

                     /*else if($notify_person == 'SOCIAL_WORKER') {

                        $emails = ServiceUserCareTeam::select('email')->where('is_deleted','0')->get()->toArray();
                    }*/

                    if(!empty($emails)){
                        $company_name        = PROJECT_NAME;

                        foreach($emails as $value){

                            $staff_email        = $value['email'];
                            $staff_name         = $value['name'];
                            $service_user_name  = $service_user->name;
                            $plan_title         = $data['title'];
                            $plan_date          = $data['date'];
                            $plan_details       = $data['details'];
                            
                            if (!filter_var($staff_email, FILTER_VALIDATE_EMAIL) === false) 
                            {   
                                Mail::send('emails.new_rmp_plan', [
                                        'staff_name' => $staff_name, 
                                        'service_user_name' => $service_user_name,
                                        'plan_title' => $plan_title,
                                        'plan_date' => $plan_date,
                                        'plan_details' => $plan_details
                                    ], function($message) use ($staff_email,$company_name)
                                {
                                    $message->to($staff_email,$company_name)->subject('SCITS new risk management plan');
                                });
                            } 
                        }
                    }

                } else if($notify_method == 'SYS_NOTIFY') {

                     //saving notification start
                    $notification                             = new Notification;
                    $notification->service_user_id            = $su_risk->service_user_id;
                    $notification->event_id                   = $su_risk->rmp_id;
                    $notification->notification_event_type_id = '9';
                    $notification->event_action               = 'ADD';    
                    $notification->home_id                    = Auth::user()->home_id;
                    $notification->user_id                    = Auth::user()->id;                  
                    $notification->save();
                }

                $result['response'] = true;

                $notification                             = new Notification;
                $notification->service_user_id            = $su_risk->service_user_id;
                $notification->event_id                   = $su_risk->rmp_id;
                $notification->notification_event_type_id = '9';
                $notification->event_action               = 'ADD';    
                $notification->home_id                    = Auth::user()->home_id;
                $notification->user_id                    = Auth::user()->id;                  
                $notification->save();


            } else{
                $result['response'] = false;
            }
            return $result;
        }
    }

    public function add_inc_rep(Request $request) {
        $data = $request->all();

        if($request->isMethod('post')) {
            //echo '<pre>'; print_r($data); die;

            $home_id = Auth::user()->home_id;

            $form_insert_id = DynamicForm::saveForm($data);
            $su_risk        = ServiceUserRisk::where('id',$data['su_risk_id'])->first();
            $su_risk->incident_report_id = $form_insert_id;

            if($su_risk->save()) {
                $result['response'] = true;

                $notification                             = new Notification;
                $notification->service_user_id            = $su_risk->service_user_id;
                $notification->event_id                   = $su_risk->rmp_id;
                $notification->notification_event_type_id = '10';
                $notification->event_action               = 'ADD';    
                $notification->home_id                    = Auth::user()->home_id;
                $notification->user_id                    = Auth::user()->id;                  
                $notification->save();

            } else{
                $result['response'] = false;
            }
            return $result;
        }
    }
    // rmp_risk_id
    public function view_rmp_risk($su_risk_id = null) {
        // echo $su_risk_id; die;
        $home_id  = Auth::user()->home_id;
        
        $su_risk = DB::table('su_risk')->select('*')->where('id',$su_risk_id)->first();

        // echo $home_id; die; 
        /*$rmp_risk = DB::table('su_rmp as surmp')
                        ->select('surmp.id as su_rmp_id','surmp.su_risk_id as surmp_su_risk_id','surmp.service_user_id as service_user_id','surmp.title','surmp.formdata','surmp.sent_to')
                        ->join('su_risk as su_r','su_r.id','surmp.su_risk_id')
                        // ->join('su_risk as su_r','su_r.id','surmp.su_risk_id')
                        ->join('risk as r','r.id','su_r.risk_id')
                        ->where('surmp.su_risk_id',$su_risk_id)
                        ->where('r.home_id', $home_id)
                        ->first();*/
        //echo "<pre>"; print_r($rmp_risk); die; 

        if(!empty($su_risk->rmp_id)) {

            $dyn_res = DynamicForm::showFormWithValue($su_risk->rmp_id, true);
            if($dyn_res['response'] == true){
                $form            = $dyn_res['form_data'];
                $form_builder_id = $dyn_res['form_builder_id'];
            } else{
                $form = '';
                $form_builder_id = '';
            }
            //echo '<pre>'; print_r($dyn_res); die;
            /*$formdata = $rmp_risk->formdata;
            $risk_form_response = FormBuilder::showFormWithValue('su_rmp',$formdata,true);

            if($risk_form_response == true) {
                $risk_form = $risk_form_response['pattern'];
            } else{
                $risk_form = '';
            }*/

            /*$result['response']         = true;
            $result['surmp_su_risk_id'] = $rmp_risk->surmp_su_risk_id;
            $result['service_user_id']  = $rmp_risk->service_user_id;*/
            // echo $result['surmp_su_risk_id']; die;
            $result['response']         = true;
            $result['su_rmp_id']        = $su_risk->rmp_id;
            $result['form_builder_id']  = $form_builder_id;
            // $result['rmp_title']        = $su_risk->title;
            // $result['rmp_sent_to']     =  $su_risk->sent_to;
            // echo $result['rmp_title']; die;
            $result['rmp_risk_form']    = $form;   

        } else {
            $result['response']         = false;            
        }
        return $result;
    }
    
    public function view_inc_rec_risk($su_risk_id = null) {

        $home_id = Auth::user()->home_id;
        $su_risk = DB::table('su_risk')->select('*')->where('id',$su_risk_id)->first();
        /*$inc_rec_risk = DB::table('su_incident_report as su_ir')
                        ->select('su_ir.id as su_ir_id','su_ir.su_risk_id as su_ir_su_risk_id','su_ir.service_user_id as service_user_id','su_ir.title','su_ir.date','su_ir.formdata')
                        ->join('su_risk as su_r','su_r.id','su_ir.su_risk_id')
                        // ->join('su_risk as su_r','su_r.id','surmp.su_risk_id')
                        ->join('risk as r','r.id','su_r.risk_id')
                        ->where('su_ir.su_risk_id',$su_risk_id)
                        ->where('r.home_id', $home_id)
                        ->first();*/

        if(!empty($su_risk->incident_report_id)) {
     
            $dyn_res = DynamicForm::showFormWithValue($su_risk->incident_report_id, true);
            if($dyn_res['response'] == true){
                $form            = $dyn_res['form_data'];
                $form_builder_id = $dyn_res['form_builder_id'];
            } else{
                $form = '';
                $form_builder_id = '';
            }

            /*$formdata = $inc_rec_risk->formdata;
            $inc_rec_form_response = FormBuilder::showFormWithValue('incident_report',$formdata,true);
            if($inc_rec_form_response == true) {
                $inc_rec_form = $inc_rec_form_response['pattern'];
            } else{
                $inc_rec_form = '';
            }*/
            // echo "<pre>"; print_r($inc_rec_form_response); die;

            /*$result['response']         = true;
            $result['surmp_su_risk_id'] = $rmp_risk->surmp_su_risk_id;
            $result['service_user_id']  = $rmp_risk->service_user_id;*/
            // echo $result['surmp_su_risk_id']; die;
            $result['response']         = true;
            $result['su_ir_id']         = $su_risk->incident_report_id;
            $result['inc_rec_form']     = $form;   
            $result['form_builder_id']  = $form_builder_id;
            // $result['inc_rec_title']    = $inc_rec_risk->title;
            // $result['inc_rec_date']     = date('d-m-Y', strtotime($inc_rec_risk->date));
            // $result['inc_rec_form']     = $inc_rec_form;   

        } else {
            $result['response']         = false;            
        }
        return $result;
    }

    public function edit_rmp_risk(Request $request) {
        
        $data = $request->all();
        //echo "<pre>"; print_r($data); return false;
        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $home_id = Auth::user()->home_id;

            $su_rmp_id               = $data['su_rmp_id'];
            
            $dyn_form               = DynamicForm::where('id',$su_rmp_id)->first();
            $dyn_form->pattern_data = $formdata;
            $dyn_form->title        = $data['title'];
            $dyn_form->time         = $data['time'];
            $dyn_form->details      = $data['details'];
            if(!empty($data['date'])){
                $dyn_form->date     = date('Y-m-d',strtotime($data['date']));   
            }           

            /*$edit_rmp = ServiceUserRmp::where('id',$data['su_rmp_id'])->first();
            $edit_rmp->service_user_id  = $edit_rmp->service_user_id;
            $edit_rmp->su_risk_id       = $edit_rmp->su_risk_id;
            $edit_rmp->title            = $data['edit_rmp_title'];
            $edit_rmp->sent_to          = $data['edit_sent_to'];
            $edit_rmp->formdata         = $formdata;
            $edit_rmp->home_id          = $home_id;*/
            
            // echo "<pre>"; print_r($edit_rmp); die;
            if($dyn_form->save()) {
                $result['response'] = '1';
            } else{
                $result['response'] = '0';
            }
            return $result;
        }
    }

    public function edit_inc_rep(Request $request) {
        
        $data = $request->all();
        //echo '<pre>'; print_r($data); die;
        if($request->isMethod('post')) {

            if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }
            $home_id                  = Auth::user()->home_id;
            $su_risk_id               = $data['su_risk_id'];
            
            $su_risk = ServiceUserRisk::select('*')->where('id',$su_risk_id)->first();
            
            if(!empty($su_risk->incident_report_id)) {
                
                $dyn_form               = DynamicForm::where('id',$su_ir_id)->first();
                $dyn_form->pattern_data = $formdata;
                $dyn_form->title        = $data['title'];
                $dyn_form->time         = $data['time'];
                $dyn_form->details      = $data['details'];
                if(!empty($data['date'])){
                    $dyn_form->date     = date('Y-m-d',strtotime($data['date']));   
                }  
                if($dyn_form->save()) {
                    $result['response'] = '1';
                } else{
                    $result['response'] = '0';
                }
                return $result;
            
            } else {
                
                $form_insert_id = DynamicForm::saveForm($data);
                $su_risk->incident_report_id = $form_insert_id;

                if($su_risk->save()) {
                    $result['response'] = '1';
                } else{
                    $result['response'] = '0';
                }
                return $result;
            }
        }
    }

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
    }   */  

    

}