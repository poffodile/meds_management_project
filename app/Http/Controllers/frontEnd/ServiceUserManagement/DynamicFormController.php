<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\File;

use App\Http\Controllers\Controller;
use Auth, DB;
use App\DynamicFormBuilder, App\DynamicForm, App\ServiceUser, App\DynamicFormLocation, App\Notification, App\ServiceUserLogBook, App\LogBook, App\EarningScheme, APP\ServiceUserRisk, App\CategoryFrontEnd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
//use Hash, Session;


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
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $form_insert_id = DynamicForm::saveForm($data);
            // dd($form_insert_id);
            // echo "dfdf"; die;

            if ($form_insert_id != 0) {

                //if this dynamic form is mfc form then manage earning points
                $location_ids = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->value('location_ids');
                // dd($location_ids);
                $location_ids_arr = explode(',', $location_ids);
                if (in_array('5', $location_ids_arr)) {
                    EarningScheme::updateEarning($data['service_user_id']);
                }
                //update earning scheme in case of mfc form ends here
                //sourabh log insert
                $logtype = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->value('logtype');
                // dd($logtype); 

                $logtype_arr = explode(',', $logtype);

                // Convert all values to integers and remove duplicates
                $logtype_arr = array_unique(array_map('intval', $logtype_arr));

                // If 10 is selected, override and use all 1–9
                if (in_array(10, $logtype_arr)) {
                    $logtype_arr = range(1, 9); // [1, 2, ..., 9]
                }

                $currentDate = Carbon::now()->format('Y-m-d');

                foreach ($logtype_arr as $val) {
                    switch ($val) {
                        case 1:
                            //Daily record
                            $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                            $inserlogbook = array(
                                'title' => $data['title'],
                                'category_id' => 3,
                                'category_name' => 'Visitor',
                                'category_icon' => 'fa fa-users',
                                'date' => date('Y-m-d H:i:s', strtotime($data['date'])),
                                'formdata' => json_encode($data['data']),
                                'details' => $data['details'],
                                'start_date' => $currentDate,
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'dynamic_form_id' => $form_insert_id,
                                'image_name' => '',
                                'logType' => 1,
                                'is_late' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            );

                            $last_id = DB::table('log_book')->insertGetId($inserlogbook);
                            Log::info("Inserting logType {$val} with data: $last_id" . json_encode($last_id));

                            if ($last_id > 0) {
                                $insertServiceUserLogBook = array(
                                    'service_user_id' => $data['service_user_id'],
                                    'log_book_id' => $last_id,
                                    'user_id' => Auth::user()->id,
                                    'is_late' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'logType' => 1,
                                );
                                DB::table('su_log_book')->insert($insertServiceUserLogBook);
                            }
                            break;

                        case 2:
                            // Weekly Log
                          
                            // Date after 1 week
                            $nextWeek = Carbon::now()->addWeek()->format('Y-m-d');

                            $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                            $inserlogbook = array(
                                'title' => $data['title'],
                                'category_id' => 3,
                                'category_name' => 'Visitor',
                                'category_icon' => 'fa fa-users',
                                'date' => date('Y-m-d H:i:s', strtotime($data['date'])),
                                'start_date' => $currentDate,
                                'end_date' => $nextWeek,
                                'formdata' => json_encode($data['data']),
                                'details' => $data['details'],
                                'dynamic_form_id' => $form_insert_id,
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'image_name' => '',
                                'logType' => 2,
                                'is_late' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            );

                            $last_id = DB::table('log_book')->insertGetId($inserlogbook);
                            Log::info("Inserting logType {$val} with data: $last_id" . json_encode($inserlogbook));


                            if ($last_id > 0) {
                                $insertServiceUserLogBook = array(
                                    'service_user_id' => $data['service_user_id'],
                                    'log_book_id' => $last_id,
                                    'user_id' => Auth::user()->id,
                                    'is_late' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'logType' => 2,
                                );
                                DB::table('su_log_book')->insert($insertServiceUserLogBook);
                            }
                            break;

                        case 3:
                            // Monthly log
                            // Date after 1 month
                            $nextMonth = Carbon::now()->addMonth()->format('Y-m-d');

                            $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                            $inserlogbook = array(
                                'title' => $data['title'],
                                'category_id' => 3,
                                'category_name' => 'Visitor',
                                'category_icon' => 'fa fa-users',
                                'date' => date('Y-m-d H:i:s', strtotime($data['date'])),
                                'start_date' => $currentDate,
                                'end_date' => $nextMonth,
                                'formdata' => json_encode($data['data']),
                                'details' => $data['details'],
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'dynamic_form_id' => $form_insert_id,
                                'image_name' => '',
                                'logType' => 3,
                                'is_late' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            );

                            $last_id = DB::table('log_book')->insertGetId($inserlogbook);
                            Log::info("Inserting logType {$val} with data: $last_id" . json_encode($inserlogbook));


                            if ($last_id > 0) {
                                $insertServiceUserLogBook = array(
                                    'service_user_id' => $data['service_user_id'],
                                    'log_book_id' => $last_id,
                                    'user_id' => Auth::user()->id,
                                    'is_late' => 0,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'logType' => 3,
                                );
                                DB::table('su_log_book')->insert($insertServiceUserLogBook);
                            }
                            break;

                        case 4:
                            //health record
                            $insert_su_health_record = array(
                                'home_id' => $home_id,
                                'service_user_id' => $data['service_user_id'],
                                'contact_id' => 0,
                                'care_team_id' => 0,
                                'title' => $data['title'],
                                'status' => 1,
                                'details' => $data['details'],
                                'formdata' => json_encode($data['data']),
                                'is_deleted' => 0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('su_health_record')->insert($insert_su_health_record);
                            Log::info("Inserting logType {$val} with data: " . json_encode($insert_su_health_record));
                            break;

                        case 5:
                        case 8:
                            //plans
                            $insert_plans_managment = array(
                                'service_user_id' => $data['service_user_id'],
                                'task' => $data['title'],
                                'date' => date('Y-m-d'),
                                'description' => $data['details'],
                                'qqa_review' => "",
                                'formdata' => json_encode($data['data']),
                                'home_id' => $home_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            $lastid_plans = DB::table('su_placement_plan')->insertGetId($insert_plans_managment);
                            Log::info("Inserting logType {$val} with data: $lastid_plans" . json_encode($insert_plans_managment));

                            //notification
                            $insert_plansnotification = array(
                                'service_user_id' => $data['service_user_id'],
                                'event_id' => $lastid_plans,
                                'notification_event_type_id' => '8',
                                'event_action' => 'ADD',
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('notification')->insert($insert_plansnotification);
                            break;

                        case 6:
                            //risk managment
                            $insert_risk_managment = array(
                                'service_user_id' => $data['service_user_id'],
                                'risk_id' => 1,
                                'status' => 0,
                                'dynamic_form_id' => $form_insert_id,
                                'home_id' => $home_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            $risk_id = DB::table('su_risk')->insert($insert_risk_managment);
                            Log::info("Inserting logType {$val} with data: $risk_id" . json_encode($insert_risk_managment));

                            //notification
                            $insert_risknotification = array(
                                'service_user_id' => $data['service_user_id'],
                                'event_id' => 1,
                                'notification_event_type_id' => '11',
                                'event_action' => 'ADD',
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('notification')->insert($insert_risknotification);
                            break;

                        case 7:
                            //Behaviour Management
                            $insert_behaviour_managment = array(
                                'service_user_id' => $data['service_user_id'],
                                'title' => $data['title'],
                                'details' => $data['details'],
                                'sent_to' => 2,
                                'formdata' => json_encode($data['data']),
                                'home_id' => $home_id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            $lastid_behaviour = DB::table('su_bmp')->insertGetId($insert_behaviour_managment);
                            Log::info("Inserting logType {$val} with data: {$lastid_behaviour}" . json_encode($insert_behaviour_managment));

                            //notification
                            $insert_behaviournotification = array(
                                'service_user_id' => $data['service_user_id'],
                                'event_id' => $lastid_behaviour,
                                'notification_event_type_id' => '8',
                                'event_action' => 'ADD',
                                'home_id' => $home_id,
                                'user_id' => Auth::user()->id,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            );
                            DB::table('notification')->insert($insert_behaviournotification);
                            break;

                        case 9:
                            // Any special log
                            break;
                    }
                }




                // foreach ($logtype_arr as $val) {

                //     if ($val == 1 || $val == 10) {
                //     } else if ($val == 2 || $val == 10) {
                //     } else if ($val == 3 || $val == 10) {
                //     } else if ($val == 4 || $val == 10) {
                //     } else if ($val == 6 || $val == 10) {
                //     } else if ($val == 7 || $val == 10) {
                //     } else if ($val == 8 || $val == 10 || $val == 5) {
                //     } else if ($val == 9 || $val == 10) {
                //     }
                // }
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


            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            // $home_id = Auth::user()->home_id;
            $dynamic_form_id = $request->dynamic_form_id;
            $form            = DynamicForm::where('dynamic_form.id', $dynamic_form_id)->first();
            //join('service_user as su','su.id','=','dynamic_form.service_user_id') ->where('su.home_id',$home_id)
            // echo "<pre>";
            //  print_r(json_encode($data['data']));
            //($data['data']==null)?"hello" +die() :json_encode($data['data']);
            //  die();

            if (isset($data['formImage'])) {
                $formImage = $data['formImage'];
            } else if (isset($data['formImage2'])) {
                $formImage = $data['formImage2'];
            } else {
                $formImage = null;
            }

            if (!empty($data['date'])) {
                $form->date         = date('Y-m-d', strtotime($data['date']));
            }

            $form->title                = $data['title'];
            $form->details              = $data['details'];
            $form->time                 = $data['time'];
            $form->image_path           = $formImage;
            $form->pattern_data         = json_encode($data['data']);
            // $form->alert_status      = $data['alert_status'];
            // $form->alert_date        = $data['alert_date'];

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
                    $notification->home_id                    = $home_id;
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
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $su_home_id = ServiceUser::where('id', $form->service_user_id)->value('home_id');

            if ($su_home_id == $home_id) {

                $res = DynamicForm::where('id', $dynamic_form_id)->update(['is_deleted' => '1']);
                echo $res;
            }
        }
        die;
    }

    public function index()
    {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

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
        $loop = 1;

        $colors = ['#8fd6d6', '#f57775', '#bda4ec', '#fed65a', '#81b56b'];
        shuffle($colors); // Randomizes the order

        // dd($dyn_forms);
        foreach ($dyn_forms as $key => $value) {
            $form_title = DynamicFormBuilder::where('id', $value->form_builder_id)->value('title');

            if ($value->date == '') {
                $date = '';
            } else {
                $date = date('d-m-Y', strtotime($value->date));
            }

            // if ($value->created_at == '') {
            //     $date = '';
            // } else {
            //     $date = \Carbon\Carbon::parse($value->created_at)->format('d-m-Y');
            // }

            if ((!empty($date)) || (!empty($value->time))) {
                $start_brct = '(';
                $end_brct = ')';
            } else {
                $start_brct = '';
                $end_brct = '';
            }

            if(!empty($value->time)){
                $time = $value->time;
            } else {
                $time = '00:00';
            }

            $color = $colors[$key % count($colors)]; // Cycle through colors if more records than colors


            if ($loop % 2 == 0) {

                echo '<div class="col-md-6 col-sm-6 col-xs-6 cog-panel rows">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                        <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr rightSideInput">

                                <!-- <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '"> -->

                                <a href="#" class="ritOrdring one dyn-form-view-data" id="' . $value->id . '"><span>
                                    <input type="text" class="form-control" style="cursor:pointer; background-color: ' . $color . ';" name="" readonly value="' . $form_title.' - '.$value->title . ' " maxlength="255"/></span></a>
                                
                                <span class="ritOrdring two input-group-addon cus-inpt-grp-addon clr-blue settings" style="cursor:pointer; background-color: ' . $color . ';">
                                    <i class="fa fa-cog"></i>
                                    <div class="pop-notifbox">
                                        <ul class="pop-notification" type="none">
                                            <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '"> <span> <i class="fa fa-eye"></i> </span> View/Edit</a> </li>
                                            <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="1"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span>Send to Daily Log Book (In development)</a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="2"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Send to Weekly Log Book (In development)</a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="3"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Send to Monthly Log Book (In development)</a> </li>
                                        </ul>
                                    </div>
                                </span>
                                <span class="ritOrdring three rightdate"> ' . $date . ' - '. $time .'</span>
                                <span class="rightArrow"></span>
                            </div>
                        </div>
                        </div>
                    </div>  ';
            } else {

                echo '<div class="col-md-6 col-sm-6 col-xs-6 cog-panel rows">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                        <!-- <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"></label> -->
                        <div class="col-md-12 col-sm-11 col-xs-12 r-p-0">
                            <div class="input-group popovr timelineInput">

                               <!-- <input type="hidden" name="su_bmp_id[]" value="' . $value->id . '" disabled="disabled" class="edit_bmp_id_' . $value->id . '"> -->
                                <a href="#" class="dyn-form-view-data" id="' . $value->id . '"><span>
                                <input type="text" class="form-control" style="cursor:pointer; background-color: ' . $color . ';" name="" readonly value="' .$form_title.' - '.$value->title . '" maxlength="255"/></span></a>
                                <span class="timLineDate">' . $date . ' - '. $time .'</span>
                                <span class="arrow"></span>

                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings" style="cursor:pointer; background-color: ' . $color . ';">
                                    <i class="fa fa-cog"></i>
                                    <div class="pop-notifbox">
                                        <ul class="pop-notification" type="none">
                                            <li> <a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="' . $value->id . '"> <span> <i class="fa fa-eye"></i> </span> View/Edit</a> </li>
                                            <li> <a href="#" class="dyn_form_del_btn" id="' . $value->id . '"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="1"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span>Send to Daily Log Book (In development)</a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="2"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Send to Weekly Log Book (In development)</a> </li>
                                            <li> <a href="#" class="dyn_form_daily_log" dyn_form_id="' . $value->id . '" logtype="3"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Send to Monthly Log Book (In development)</a> </li>
                                        </ul>
                                    </div>
                                </span>
                            </div>
                        </div>
                        </div>
                    </div>  ';
            }

            $loop++;
        }
        // echo $pagination;
    }

    public function su_daily_log_add(Request $request)
    {
        // echo "<pre>"; print_r($request->input()); die;

        if ($request->isMethod('post')) {

            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $dyn_form = DynamicForm::where('id', $data['dyn_form_id'])->first();
            // echo "<pre>"; print_r($dyn_form); die;

            // Ram here code for save title and detail when user want to send to daily log 19/06/2025
            $title_detail = DynamicFormBuilder::where('id', $dyn_form->form_builder_id)->first();
            // echo "<pre>";print_r($title_detail);die;

            $check_log_record = LogBook::where('dynamic_form_id', $data['dyn_form_id'])->where('logType', $data['logtype'])->get()->toArray();
            // echo "<pre>"; print_r($check_log_record); die;
            if (!empty($check_log_record)) {
                foreach ($check_log_record as $key => $log_record) {
                    $su_log_yp = ServiceUserLogBook::where('log_book_id', $log_record['id'])->where('service_user_id', $data['s_user_id'])->first();
                    // echo "<pre>"; print_r($su_log_yp); die;

                    if (!empty($su_log_yp)) {
                        if ($data['logtype'] == 1) {
                            echo "already_daily";
                        } else if ($data['logtype'] == 2) {
                            echo "already_weekly";
                        } else if ($data['logtype'] == 3) {
                            echo "already_monthly";
                        }
                        //echo "already";
                        die;
                        // $response = 'already';
                    }
                }
            }

            $s_category_id = $data['s_category_id'] ?? null;
            $category_data = $s_category_id ? CategoryFrontEnd::where('id', $s_category_id)->first() : null;


            $form = DynamicForm::showForm($data);

            $log_book                  = new LogBook;
            $log_book->dynamic_form_id = $data['dyn_form_id'] ?? null;
            $log_book->home_id         = $home_id;
            $log_book->user_id         = Auth::user()->id;
            $log_book->title           = $dyn_form->title ?? $title_detail->title;
            $log_book->date            = date('Y-m-d H:i:s');
            $log_book->start_date = !empty($data['start_date'])
                ? Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d')
                : null;

            $log_book->end_date = !empty($data['end_date'])
                ? Carbon::createFromFormat('d-m-Y', $data['end_date'])->format('Y-m-d')
                : null;
            $log_book->details         = $dyn_form->details ?? $title_detail->detail;
            $log_book->category_id     = $s_category_id;
            $log_book->category_name   = $category_data ? $category_data->name : null;
            $log_book->category_icon   = $category_data ? $category_data->icon : null;
            $log_book->formdata        = $dyn_form->pattern_data;
            $log_book->logType         = $data['logtype'] ?? null;
            $log_book->save();


            $su_log_record                  = new ServiceUserLogBook;
            $su_log_record->user_id         = Auth::user()->id;
            $su_log_record->log_book_id     = $log_book->id;
            $su_log_record->service_user_id = $data['s_user_id'];
            $su_log_record->user_id         = Auth::user()->id;
            $su_log_record->logType         = $data['logtype'];

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
