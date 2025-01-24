<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth, DB;
use App\DynamicFormBuilder, App\DynamicForm, App\ServiceUser, App\DynamicFormLocation, App\Notification, App\ServiceUserLogBook, App\LogBook, App\EarningScheme, APP\ServiceUserRisk;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

//App\ServiceUser, App\Admin, App\Home, App\LogBook;
//use Hash, Session;
//use Carbon\Carbon;


class DynamicFormController extends Controller
{
    //public function view_form_pattern($form_default_id = null, $service_user_id = null) {
    public function view_form_pattern(Request $request)
    {
        $form_builder_id = $request->form_builder_id;
        $service_user_id = $request->service_user_id;
        $form = DynamicForm::showForm($form_builder_id, $service_user_id);
        //   	dd($form);
        return $form;
    }

    public function save_form(Request $request)
    {
        $data = $request->input();

        if (!empty($data)) {
            // dd($request);

            $form_insert_id = DynamicForm::saveForm($data);

            if ($form_insert_id != 0) {

                //if this dynamic form is mfc form then manage earning points
                $location_ids = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->value('location_ids');
                $location_ids_arr = explode(',', $location_ids);
                if (in_array('5', $location_ids_arr)) {
                    EarningScheme::updateEarning($data['service_user_id']);
                }
                //update earning scheme in case of mfc form ends here 
                //sourabh log insert
                $logtype = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->value('logtype');
                $logtype_arr = explode(',', $logtype);

                foreach ($logtype_arr as $val) {
                    if ($val == 1 || $val == 10) {
                        //Daily record
                        $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                        $inserlogbook = array(
                            // 'title'=>$data['title'],
                            'title' => null,
                            'category_id' => 3,
                            'category_name' => 'Visitor',
                            'category_icon' => 'fa fa-users',
                            // 'date' => date('Y-m-d H:i:s', strtotime($data['date'])),
                            'date' => null,
                            // 'details'=>$data['details'],
                            'details' => null,
                            'home_id' => Auth::user()->home_id,
                            'user_id' => Auth::user()->id,
                            'image_name' => '',
                            'is_late' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        );

                        $last_id = DB::table('log_book')->insertGetId($inserlogbook);

                        if ($last_id > 0) {
                            $insertServiceUserLogBook = array(
                                'service_user_id' => $data['service_user_id'],
                                'log_book_id' => $last_id,
                                'user_id' => Auth::user()->id,
                                'is_late' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('su_log_book')->insert($insertServiceUserLogBook);
                        }
                    } else if ($val == 2 || $val == 10) {
                    } else if ($val == 3 || $val == 10) {
                    } else if ($val == 4 || $val == 10) {
                        //health record
                        $insert_su_health_record = array(
                            'home_id' => Auth::user()->home_id,
                            'service_user_id' => $data['service_user_id'],
                            'contact_id' => 0,
                            'care_team_id' => 0,
                            // 'title'=>$data['title'],
                            'title' => null,
                            'status' => 1,
                            // 'details'=>$data['details'],
                            'details' => null,
                            'is_deleted' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('su_health_record')->insert($insert_su_health_record);
                    } else if ($val == 6 || $val == 10) {
                        //risk managment
                        $insert_risk_managment = array(
                            'service_user_id' => $data['service_user_id'],
                            'risk_id' => 1,
                            'status' => 0,
                            'dynamic_form_id' => $form_insert_id,
                            'home_id' => Auth::user()->home_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('su_risk')->insert($insert_risk_managment);
                        //notification
                        $insert_risknotification = array(
                            'service_user_id' => $data['service_user_id'],
                            'event_id' => 1,
                            'notification_event_type_id' => '11',
                            'event_action' => 'ADD',
                            'home_id' => Auth::user()->home_id,
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('notification')->insert($insert_risknotification);
                    } else if ($val == 7 || $val == 10) {
                        //Behaviour Management
                        $insert_behaviour_managment = array(
                            'service_user_id' => $data['service_user_id'],
                            // 'title'=> $data['title'],
                            'title' => null,
                            // 'details' => $data['details'],
                            'details' => null,
                            'sent_to' => 2,
                            'formdata' => json_encode($data['data']),
                            'home_id' => Auth::user()->home_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        $lastid_behaviour = DB::table('su_bmp')->insertGetId($insert_behaviour_managment);
                        //notification
                        $insert_behaviournotification = array(
                            'service_user_id' => $data['service_user_id'],
                            'event_id' => $lastid_behaviour,
                            'notification_event_type_id' => '8',
                            'event_action' => 'ADD',
                            'home_id' => Auth::user()->home_id,
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('notification')->insert($insert_behaviournotification);
                    } else if ($val == 8 || $val == 10 || $val == 5) {
                        //plans
                        $insert_plans_managment = array(
                            'service_user_id' => $data['service_user_id'],
                            // 'task'=>$data['title'],
                            'task' => null,
                            'date' => date('Y-m-d'),
                            // 'description'=>$data['details'],
                            'description' => null,
                            'qqa_review' => "",
                            'formdata' => json_encode($data['data']),
                            'home_id' => Auth::user()->home_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        $lastid_plans = DB::table('su_placement_plan')->insertGetId($insert_plans_managment);
                        //notification
                        $insert_plansnotification = array(
                            'service_user_id' => $data['service_user_id'],
                            'event_id' => $lastid_plans,
                            'notification_event_type_id' => '8',
                            'event_action' => 'ADD',
                            'home_id' => Auth::user()->home_id,
                            'user_id' => Auth::user()->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        );
                        DB::table('notification')->insert($insert_plansnotification);
                    } else if ($val == 9 || $val == 10) {
                    }
                    // else if($val==10){
                    //     //daily record
                    //     $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                    //     $inserlogbook = array(
                    //         'title'=>$data['title'],
                    //         'category_id'=>3,
                    //         'category_name'=>'Visitor',
                    //         'category_icon'=>'fa fa-users',
                    //         'date'=>date('Y-m-d H:i:s', strtotime($data['date'])),
                    //         'details'=>$data['details'],
                    //         'home_id'=>Auth::user()->home_id,
                    //         'user_id'=>Auth::user()->id,
                    //         'image_name'=>'',
                    //         'is_late'=>0,
                    //         'created_at'=>date('Y-m-d H:i:s'),
                    //         'updated_at'=>date('Y-m-d H:i:s')
                    //     );
                    //     //return $inserlogbook;
                    //     //die;
                    //     $last_id = DB::table('log_book')->insertGetId($inserlogbook);

                    //     if($last_id>0){
                    //         $insertServiceUserLogBook = array(
                    //             'service_user_id'=>$data['service_user_id'],
                    //             'log_book_id'=>$last_id,
                    //             'user_id'=> Auth::user()->id,
                    //             'is_late'=>0,
                    //             'created_at'=>date('Y-m-d H:i:s'),
                    //             'updated_at'=>date('Y-m-d H:i:s'),
                    //         );
                    //         DB::table('su_log_book')->insert($insertServiceUserLogBook);

                    //     }
                    //     //health record
                    //     $insert_su_health_record = array(
                    //         'home_id'=>Auth::user()->home_id,
                    //         'service_user_id'=>$data['service_user_id'],
                    //         'contact_id'=>0,
                    //         'care_team_id'=>0,
                    //         'title'=>$data['title'],
                    //         'status'=>1,
                    //         'details'=>$data['details'],
                    //         'is_deleted'=>0,
                    //         'created_at'=>date('Y-m-d H:i:s'),
                    //         'updated_at'=>date('Y-m-d H:i:s'),
                    //     );
                    //     DB::table('su_health_record')->insert($insert_su_health_record);
                    //     //risk managment
                    //     $risk = new ServiceUserRisk;
                    //     $risk->service_user_id  = $data['service_user_id'];
                    //     $risk->risk_id          = 1;
                    //     $risk->status           = 0;
                    //     $risk->dynamic_form_id  = $form_insert_id;
                    //     $risk->home_id          = Auth::user()->home_id;
                    //     if($risk->save()) {
                    //         //saving notification start
                    //         $notification                             = new Notification;
                    //         $notification->service_user_id            = $data['service_user_id'];
                    //         $notification->event_id                   = $risk->id;
                    //         $notification->notification_event_type_id = '11';
                    //         $notification->event_action               = 'ADD';      
                    //         $notification->home_id                    = Auth::user()->home_id;
                    //         $notification->user_id                    = Auth::user()->id;        
                    //         $notification->save();
                    //         //saving notification end

                    //     } 
                    //     //

                    // }

                }



                //sourabh log insert

                return 'true';
            } else {
                return 'false';
            }
        } else {

            return 'false';
        }
    }


    public function view_form_data($dynamic_form_id = null)
    {
        $result = DynamicForm::showFormWithValue($dynamic_form_id, false);
        return $result;
    }

    public function edit_form(Request $request)
    {

        $data = $request->input();

        if (!empty($data)) {
            $home_id = Auth::user()->home_id;
            $dynamic_form_id = $request->dynamic_form_id;
            $form            = DynamicForm::where('dynamic_form.id', $dynamic_form_id)->first();
            //join('service_user as su','su.id','=','dynamic_form.service_user_id') ->where('su.home_id',$home_id)
            // echo "<pre>";
            //  print_r(json_encode($data['data']));
            //($data['data']==null)?"hello" +die() :json_encode($data['data']);
            //  die();

            if(isset($data['formImage'])){
                $formImage = $data['formImage'];
            } else if(isset($data['formImage2'])) {
                $formImage = $data['formImage2'];
            } else {
                $formImage = null;
            }

            if (!empty($data['date'])) {
                $form->date         = date('Y-m-d', strtotime($data['date']));
            }

            // $form->title             = $data['title'];
            $form->title                = null;
            // $form->details             = $data['details'];
            $form->details              = null;
            // $form->time             = $data['time'];
            $form->time                = null;
            $form->image_path          =  $formImage;
            $form->pattern_data        = json_encode($data['data']);
            // $form->alert_status     = $data['alert_status']; 
            // $form->alert_date       = $data['alert_date']; 

            if ($form->save()) {

                //for notification's
                $location_tag = DynamicFormLocation::where('id', $form->location_id)->value('tag');
                $notification_event_type_id = DB::table('notification_event_type')->where('table_linked', 'LIKE', 'su_' . $location_tag)->value('id');
                if (!empty($notification_event_type_id)) {
                    //saving notification start
                    $notification                             = new Notification;
                    $notification->service_user_id            = $form->service_user_id;
                    $notification->event_id                   = $form->id;
                    $notification->notification_event_type_id = $notification_event_type_id;
                    $notification->event_action               = 'EDIT';
                    $notification->home_id                    = Auth::user()->home_id;
                    $notification->user_id                    = Auth::user()->id;
                    $notification->save();
                    //saving notification end
                }

                return 'true';
                //return redirect()->back()->with('success','Form has been saved successfully');    
            } else {
                return 'false';
                //return redirect()->back()->with('error',COMMON_ERROR);	
            }
        } else {
            return 'false';
            //return redirect()->back()->with('error',COMMON_ERROR);	
        }
        //echo '<pre>'; print_r($data); die;
    }

    public function delete_form($dynamic_form_id = null)
    {

        $form = DynamicForm::find($dynamic_form_id);

        if (!empty($form)) {

            $su_home_id = ServiceUser::where('id', $form->service_user_id)->value('home_id');

            if ($su_home_id == Auth::user()->home_id) {

                $res = DynamicForm::where('id', $dynamic_form_id)->update(['is_deleted' => '1']);
                echo $res;
            }
        }
        die;
    }

    public function index()
    {

        $home_id = Auth::user()->home_id;

        //in search case editing start for plan,details and review
        /*if(isset($_POST)) {
            $data = $_POST;
        }*/
        //$this_location_id = DynamicFormLocation::getLocationIdByTag('bmp');
        $dyn_record       = DynamicForm:: //where('location_id',$this_location_id)
            //whereIn('form_builder_id',$form_bildr_ids)
            where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->orderBy('id', 'desc');

        $pagination = '';
        if (isset($_GET['search'])) {
            if (!empty($_GET['search'])) {

                if ($_GET['searchType'] ==  1) {
                    $dyn_forms = $dyn_record->where('title', 'like', '%' . $_GET['search'] . '%')->get();
                }
                if ($_GET['searchType'] ==  2) {
                    $search_date = date('Y-m-d', strtotime($_GET['search'])) . ' 00:00:00';
                    $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['search']))) . ' 00:00:00';
                    $dyn_forms = $dyn_record->where('created_at', '>', $search_date)->where('created_at', '<', $search_date_next)->get();
                }
            }
        } else {
            $dyn_forms = $dyn_record->paginate();
            if ($dyn_forms->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm ">'; //bmp_paginate
                $pagination .= $dyn_forms->links();
                $pagination .= '</div>';
            }
        }

        foreach ($dyn_forms as $key => $value) {


            $title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');

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

                            <!-- <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '"> -->
                            <input type="text" class="form-control" name="" disabled value="' . $title . ' ' . $start_brct . $date . ' ' .  $end_brct . '" maxlength="255"/ style="cursor:auto;"> 
                            
                            <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">
                                        <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '"> <span> <i class="fa fa-eye"></i> </span> View/Edit</a> </li>
                                        <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                        <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Daily Log Book </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
            </div>  ';

            // echo '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel rows">
            //             <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                 <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
            //                 <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
            //                     <div class="input-group popovr">

            //                         <!-- <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '"> -->
            //                         <input type="text" class="form-control" name="" disabled value="' . $value->title . ' ' . $start_brct . $date . ' ' . $value->time . $end_brct . '" maxlength="255"/> 

            //                         <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
            //                             <i class="fa fa-cog"></i>
            //                             <div class="pop-notifbox">
            //                                 <ul class="pop-notification" type="none">
            //                                     <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '"> <span> <i class="fa fa-eye"></i> </span> View/Edit</a> </li>
            //                                     <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
            //                                     <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Daily Log Book </a> </li>
            //                                 </ul>
            //                             </div>
            //                         </span>
            //                     </div>
            //                 </div>
            //             </div>
            //         </div>  ';
        }
        //echo $pagination;
    }

    public function su_daily_log_add(Request $request)
    {
        // echo "<pre>"; print_r($request->input()); die;

        if ($request->isMethod('post')) {

            $data = $request->all();

            $dyn_form = DynamicForm::where('id', $data['dyn_form_id'])->first();

            $check_log_record = LogBook::where('dynamic_form_id', $data['dyn_form_id'])->get()->toArray();
            // echo "<pre>"; print_r($check_log_record); die;
            if (!empty($check_log_record)) {
                foreach ($check_log_record as $key => $log_record) {
                    $su_log_yp = ServiceUserLogBook::where('log_book_id', $log_record['id'])
                        ->where('service_user_id', $data['s_user_id'])
                        ->first();
                    // echo "<pre>"; print_r($su_log_yp); die;

                    if (!empty($su_log_yp)) {
                        echo "already";
                        die;
                        // $response = 'already';
                    }
                }
            }

            $log_book                  = new LogBook;
            $log_book->dynamic_form_id = $data['dyn_form_id'];
            $log_book->home_id         = Auth::user()->home_id;
            $log_book->user_id         = Auth::user()->id;
            $log_book->title           = $dyn_form->title;
            $log_book->date            = date('Y-m-d H:i:s');
            $log_book->details         = $dyn_form->details;
            $log_book->save();

            $su_log_record                  = new ServiceUserLogBook;
            $su_log_record->user_id         = Auth::user()->id;
            $su_log_record->log_book_id     = $log_book->id;
            $su_log_record->service_user_id = $data['s_user_id'];
            $su_log_record->user_id         = Auth::user()->id;

            if ($su_log_record->save()) {
                // $response = 1;
                echo 1;
            } else {
                //$response = 0;
                echo 2;
            }
            die;
            // echo $response; die;

        }
    }
    /*public function edit_details(Request $request) {

        $data = $request->all();
        //echo '<pre>'; print_r($data); die;

        if(isset($data['dyn_id'])) {

            $dyn_form_ids = $data['dyn_id'];
            if(!empty($dyn_form_ids)) { 
                foreach($dyn_form_ids as $key => $record_id) { 
                    $record = DynamicForm::find($record_id);
                    $su_home_id = ServiceUser::where('id',$record->service_user_id)->value('home_id');
                    if(Auth::user()->home_id == $su_home_id) {
                        $record->details = $data['edit_dyn_details'][$key];
                        // $record->plan    = $data['edit_bmp_plan'][$key];
                        // $record->review  = $data['edit_bmp_review'][$key];
                        if($record->save()) {

                            $notification                             = new Notification;
                            $notification->service_user_id            = $record->service_user_id;
                            $notification->event_id                   = $record->id;
                            $notification->notification_event_type_id = '8';
                            $notification->event_action               = 'EDIT';
                            $notification->home_id                    = Auth::user()->home_id;
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
    }*/

    public function patterndataformio(Request $request)
    {
        $patterndata = $request->patterndata;
        $home_id = $request->home_id;
        $result = DynamicFormBuilder::where("id", $patterndata,)->where('home_id', $home_id)->value("pattern");
        return $result;
    }
    public function patterndataformiovalue(Request $request)
    {
        $dynamic_form_idformio = $request->dynamic_form_idformio;
        $res = DB::table('dynamic_form')
            ->join('dynamic_form_builder', 'dynamic_form.form_builder_id', '=', 'dynamic_form_builder.id')
            ->select('dynamic_form_builder.pattern', 'dynamic_form.pattern_data', 'dynamic_form.image_path')->where('dynamic_form.id', $dynamic_form_idformio)
            ->get();

        return $res;
    }

    public function saveFormDotIoImage(Request $request)
    {
        // dd($request);
        // Check if an image is uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
        
            // Generate a unique filename using timestamp
            $fileName = time() . '_' . $image->getClientOriginalName();
        
            // Define the target path in the public folder
            $publicPath = public_path('images/formio/' . $fileName);
        
            // Move the image to the public folder
            $image->move(public_path('images/formio'), $fileName);
        
            // Return success response with the public file path
            return response()->json(['success' => true, 'file_path' => 'images/formio/' . $fileName]);
        }

        // Return error response if no file is uploaded
        return response()->json(['success' => false, 'message' => 'No image uploaded.'], 400);
    }
}
