<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\LogBook, App\ServiceUser, App\ServiceUserLogBook, App\User, App\HandoverLogBook, App\ServiceUserReport, App\ServiceUserCareTeam, App\CareTeam, App\CategoryFrontEnd;
use App\DynamicFormBuilder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Notification;
use Exception;
use DB, Auth;
use PDF;
use Carbon\Carbon;

class LogBookController extends ServiceUserManagementController
{
    public function index($service_user_id)
    {

        $su_log_book_records =  ServiceUserLogBook::select('su_log_book.id as su_log_book_id', 'log_book.title', 'log_book.details', 'log_book.id', 'log_book.date', 'log_book.created_at', 'u.name as staff_name')
            ->join('log_book', 'log_book.id', 'su_log_book.log_book_id')
            ->leftJoin('user as u', 'u.id', 'log_book.user_id')
            //->leftJoin('dynamic_form as d','d.id', 'su_log_book.dynamic_form_id')
            ->where('su_log_book.service_user_id', $service_user_id)
            // ->where('log_book.home_id', Auth::user()->home_id)
            // ->where('log_book.is_deleted','0')
            ->orderBy('su_log_book.id', 'desc');

        // ->get()->toArray();
        // echo "<pre>"; print_r($su_log_book_records); die;

        //LogBook::where('is_deleted','0')

        $today = date('Y-m-d 00:0:00');

        $pagination  = '';
        if (isset($_GET['search'])) {

            // echo "<pre>"; print_r($_GET['search']); die;
            $log_book_search_type = $_GET['log_book_search_type'];

            if ($log_book_search_type == 'log_title') {

                $su_log_book_records = $su_log_book_records->where('log_book.title', 'like', '%' . $_GET['search'] . '%');
            } else {

                $search_date = date('Y-m-d', strtotime($_GET['log_book_date_search'])) . ' 00:00:00';
                $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['log_book_date_search']))) . ' 00:00:00';

                $su_log_book_records = $su_log_book_records->where('log_book.date', '>', $search_date)
                    ->where('log_book.date', '<', $search_date_next);
            }
        }

        $su_log_book_records  = $su_log_book_records->orderBy('log_book.id', 'desc')
            ->orderBy('log_book.date', 'desc');
        //->paginate(50);

        if (isset($_GET['search'])) {

            $su_log_book_records = $su_log_book_records->get();
        } else {
            $su_log_book_records = $su_log_book_records->paginate(50);

            if ($su_log_book_records->links() != '') {
                $pagination .= '</div><div class="log_records_paginate m-l-15 position-botm">';
                $pagination .= $su_log_book_records->links();
                $pagination .= '</div>';
            }
        }

        if (!$su_log_book_records->isEmpty()) {
            $pre_date = date('y-m-d', strtotime($su_log_book_records['0']->date));
        }

        foreach ($su_log_book_records as $key => $value) {

            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';

            $first = 0;
            $record_time = date('h:i a', strtotime($value->date));
            $created_time = date('h:i a', strtotime($value->created_at));
            $rec_time = $record_time . ' (' . $created_time . ')';

            if (isset($_GET['logged']) ||  isset($_GET['search'])) {
                $record_date = date('Y-m-d', strtotime($value->date));

                if ($record_date != $pre_date) {
                    $pre_date = $record_date;

                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                        ' . date('d F Y', strtotime($record_date)) . '
                                    </span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="daily-rcd-content">';
                } else {
                }
            }

            echo '
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                    <div class="form-group col-md-11 col-sm-11 col-xs-12 r-p-0 pull-center">
                        <div class="input-group popovr">
                            <input type="text" name="edit_su_record_desc[]"  class="form-control cus-control edit_record_desc_' . $value->id . ' edit_rcrd"  disabled  value="' . ucfirst($value->title) . ' | ' . $rec_time . '" />';

            if (!empty($value->details)) {
                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
            }
            echo '<input type="hidden" name="edit_su_record_id[]" value="' . $value->id . '" disabled="disabled" class="edit_record_id_' . $value->id . '" />
                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">';
            //if(isset($add_new_case)) { 
            echo '<li> <a href="#" log_book_id="' . $value->id . '" class="view-su-log-book"> <span> <i class="fa fa-eye"></i> </span> View </a> </li>';
            //}
            /*<li> <a href="#" log_book_id="'.$value->id.'" class="delete-log-record"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>*/
            echo '<li> <a href="#" log_book_id="' . $value->id . '" class="log-record-detail"> <span class="color-red"> <i class="fa fa-plus color-green"></i> </span> Details </a> </li>
                                        <li><a href="' . url('/service/logbook/Calendar/add?log_book_id=' . $value->id . '&service_user_id=' . $service_user_id) . '" log_book_id="' . $value->id . '" class="add_to_clndr"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to Calendar</a> </li>';

            echo '<li> <a href="#" log_book_id="' . $value->id . '" class="add_to_hndovr"> <span> <i class="fa fa-plus-circle"></i> </span> Add to Handover </a> </li>';

            echo '<li> <a href="#" log_book_id="' . $value->id . '" class="add_to_weekly"> <span> <i class="fa fa-plus-circle"></i> </span> Add to Weekly Log </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="input-plusbox form-group col-xs-11 p-0 detail">
                        <label class="cus-label color-themecolor"> Details: </label>
                        <div class="cus-input p-r-10">
                            <div class="input-group">
                                <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_detail_' . $value->id . ' edit_rcrd " value="">' . $value->details . '</textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-plusbox form-group col-xs-11 p-0 detail" style="display: block;">
                        <label class="cus-label color-themecolor"> Staff created: </label>
                        <div class="cus-input p-r-10">
                            <div class="input-group">
                              <input type="text" value="' . ucfirst($value->staff_name) . '" disabled="" class="form-control ">
                            </div>
                        </div>
                    </div>
                    
                </div>
                ';
        }

        echo $pagination;
    }

    public function sendNotification($log_book_record, $su_log_book_record)
    {
        try {
            $service_user = ServiceUser::where('id', $su_log_book_record->service_user_id)->where('home_id', Auth::user()->home_id)->first();
            $notification                  = new Notification;
            $notification->service_user_id = $service_user->id;
            $notification->event_id        = $su_log_book_record->id;
            $notification->notification_event_type_id      = 2;
            $notification->event_action    = 'ADD';
            $notification->home_id         = Auth::user()->home_id;
            $notification->user_id         = Auth::user()->id;
            $notification->save();
            $emails = User::getStaffList($service_user->home_id);
            foreach ($emails as $value) {
                //echo '<pre>'; print_r($value); die;
                $staff_email        = $value['email'];
                $staff_name         = $value['name'];
                if (!filter_var($staff_email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::send(
                        'emails.su_late_entry_mail',
                        [
                            'staff_name'     => $staff_name,
                            'service_user_name'     => $service_user->name,
                            'title'     => $log_book_record->title,
                            'date'  => $log_book_record->date,
                            'details'   => $log_book_record->details,
                            'home_id'   => $log_book_record->home_id,
                            'user_id'   => $log_book_record->user_id,
                        ],
                        function ($message) use ($staff_email, $staff_name, $service_user) {
                            $message
                                ->to($staff_email, $staff_name)
                                ->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                                ->subject("Child $service_user->name logged a late entry");
                        }
                    );
                    Log::info("Email sent to $staff_name successfully. As Child $service_user->name logged a late entry");
                }
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }

    public function add(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        if ($request->isMethod('post')) {
            //sourabh geo location
            // $ip = '49.35.41.195'; //For static IP address get
            // $ip = request()->ip(); //Dynamic IP address get

            $res = file_get_contents('https://api.ipify.org/?format=json');
            $data_value = json_decode($res, true);

            if ($data_value !== null && isset($data_value['ip'])) {
                $ip = $data_value['ip'];
            } else {
                $ip = "";
            }

            $response = file_get_contents('https://ipinfo.io/' . $ip . '/json?token=babd113378833b');

            if ($response === false) {
                $data = "";
            } else {
                $data = json_decode($response, true);
                if ($data !== null) {
                    $coordinates = explode(',', $data['loc']);
                    $latitude = $coordinates[0];
                    $longitude = $coordinates[1];
                } else {
                    $latitude = "";
                    $longitude = "";
                }
            }

            // $current_location = \Location::get($ip);
            //sourabh geo location
            $data = $request->all();
            //print_r($data['log_image']);
            /*sourabh image upload*/
            if ($request->hasFile('log_image')) {
                //echo "string";
                $log_image = time() . '.' . request()->log_image->getClientOriginalExtension();
                request()->log_image->move('upload/events/', $log_image);
            } else {
                //echo "false";
                $log_image = '';
            }
            // echo $data['service_user_id'];
            // die;
            /*sourabh*/
            // echo "<pre>"; print_r($data); die;

            /*$su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
            if(Auth::user()->home_id != $su_home_id){
                echo '0'; die; 
            }*/
            $home_ids = Auth::user()->home_id;
            $searchString = ',';
            //$homde_id = 1,2
            if (strpos(@$home_ids, $searchString) !== false) {
                $home_id =  explode(',', @$home_ids);
                $login_home_id = @$home_id[0];
            } else {
                $login_home_id = @$home_ids;
            }


            $latest_date    = LogBook::select('log_book.*')
                ->orderBy('date', 'desc')->take(1)->value('date');

            $latest_date    = date('Y-m-d H:i:s', strtotime($latest_date));
            $given_date    = date('Y-m-d H:i:s', strtotime($data['log_date']));
            // $given_date    = date('Y-m-d H:i:s');
            $latest_date_without_time    = date('Y-m-d', strtotime($latest_date));
            $given_date_without_time    = date('Y-m-d', strtotime($given_date));
            $current_date_without_time    = date('Y-m-d');


            // $latest_date_value = $latest_date->value('date');

            $log_book_record          = new LogBook;
            // echo "<pre>"; print_r($log_book_record); die;

            $category_icon = CategoryFrontEnd::where('id', $data['category'])->value('icon');
            $category_name = CategoryFrontEnd::where('id', $data['category'])->value('name');

            $log_book_record->title   = $data['log_title'];
            $log_book_record->category_id = $data['category'];
            $log_book_record->category_name   = $category_name;
            $log_book_record->category_icon   = $category_icon;
            $log_book_record->date    = date('Y-m-d H:i:s', strtotime($data['log_date']));
            $log_book_record->details = $data['log_detail'];
            $log_book_record->home_id = $login_home_id;
            $log_book_record->user_id = Auth::user()->id;
            $log_book_record->image_name = $log_image;
            $log_book_record->latitude = $latitude;
            $log_book_record->longitude = $longitude;

            // Log::info($current_date_without_time);
            // Log::info($latest_date_without_time);
            // Log::info('*******');

            // Log::info($given_date);
            // Log::info($latest_date);
            // Log::info('*******');
            if ($given_date < $latest_date) {
                $log_book_record->is_late = true;
            } else if ($current_date_without_time > $latest_date_without_time && $given_date_without_time < $current_date_without_time) {
                $log_book_record->is_late = true;
            }

            $log_book_record->save();
            if ($log_book_record->save()) {

                $su_log_book_record                     =   new ServiceUserLogBook;
                $su_log_book_record->service_user_id    =   $data['service_user_id'];
                $su_log_book_record->log_book_id        =   $log_book_record->id;
                $su_log_book_record->user_id            =   Auth::user()->id;
                //$su_log_book_record->category_id        =   $data['category_id'];
                $su_log_book_record->logType = '1';

                if ($given_date < $latest_date) {
                    $su_log_book_record->is_late = true;
                    Log::info("Send notification for late entry ");
                    $this->sendNotification($log_book_record, $su_log_book_record);
                    if ($su_log_book_record->save()) {
                        $result['response'] = true;
                    } else {
                        $result['response'] = false;
                    }
                } else {
                    if ($su_log_book_record->save()) {
                        $result['response'] = true;
                        echo "1";
                    } else {
                        $result['response'] = false;
                        echo "2";
                    }
                }
                // if($su_log_book_record->save()) {
                //     if(strtotime($log_book_record->date) < strtotime('now')) {
                //         Log::info("Send notification for late entry ");
                //         $this->sendNotification($log_book_record, $su_log_book_record);
                //     }
                //     $result['response'] = true;
                // }  else {
                //     $result['response'] = false;  
                // }
            } else {

                $result['response'] = false;
                echo "2";
            }
            // if($log_book_record->save()){

            //saving notification start

            /*$notification                  = new Notification;
                $notification->service_user_id = $data['service_user_id'];
                $notification->event_id        = $records->id;
                $notification->event_type      = 'SU_DR';
                $notification->event_action    = 'ADD';      
                $notification->home_id         = Auth::user()->home_id;
                $notification->user_id         = Auth::user()->id;        
                $notification->save();*/

            //saving notification end

            /*$res = $this->index();
                echo $res; die;*/

            /*return redirect()->back()->with('success','Request submitted successfully.');

            }
            else { 
                return redirect()->back()->with('error',COMMON_ERROR);
            }*/
            // return $result;
        }
    }

    public function view($log_book_id = null)
    {

        $home_ids = Auth::user()->home_id;
        $searchString = ',';
        //$homde_id = 1,2
        if (strpos(@$home_ids, $searchString) !== false) {
            $home_id =  explode(',', @$home_ids);
            $login_home_id = @$home_id[0];
        } else {
            $login_home_id = @$home_ids;
        }
        // $home_id = Auth::user()->home_id;
        //'su_lb.category_id', 'su_lb_ctgry.category_name'
        $su_log_book_record = LogBook::select('log_book.*', 'su_lb.service_user_id', 'su_lb.log_book_id', 'su_lb.user_id', 'u.name as staff_name')
            ->where('log_book.home_id', $login_home_id)
            // ->where('su_lb.service_user_id', $service_user_id)
            ->join('su_log_book as su_lb', 'su_lb.log_book_id', 'log_book.id')
            ->join('user as u', 'u.id', 'su_lb.user_id')
            //->join('su_log_book_category as su_lb_ctgry', 'su_lb_ctgry.id', 'su_lb.category_id')
            ->where('log_book.id', $log_book_id)
            ->first();
        // echo "<pre>"; print_r($su_log_book_record); die;
        if (!empty($su_log_book_record)) {

            $result['response'] = true;
            //$result['category_id'] = $su_log_book_record->category_id;
            $result['title']        = $su_log_book_record->title;
            $result['details']      = $su_log_book_record->details;
            $result['date']         = $su_log_book_record->date;
            $result['staff_name']   = $su_log_book_record->staff_name;
        } else {
            $result['response'] = false;
        }

        return $result;
    }

    public function serviceuserlist(Request $request)
    {

        $home_ids = Auth::user()->home_id;
        $searchString = ',';
        //$homde_id = 1,2
        if (strpos(@$home_ids, $searchString) !== false) {
            $home_id =  explode(',', @$home_ids);
            $login_home_id = @$home_id[0];
        } else {
            $login_home_id = @$home_ids;
        }

        $serviceuserlist = ServiceUser::where('is_deleted', '0')
            ->where('home_id', $login_home_id)
            ->select('id', 'name', 'user_name')->get();

        echo '<label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Child: </label>
                    <div class="col-md-6 col-sm-9 col-xs-10">
                        <div class="select-bi" style="width:100%;float:left;">
                            
                            <select class="js-example-placeholder-single select-field form-control" required id="records_list" style="width:100%;" name="service_user_id">
                                <option value=""></option>';

        foreach ($serviceuserlist as $value) {
            echo '<option value="' . $value->id . '">' . $value->name . '</option>';
        }

        echo '</select>
                        </div>
                       
                    </div>';
        die;
    }

    public function service_user_add_log(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();

            $su_log_record = new ServiceUserLogBook;
            $su_log_record->log_id          = $data['log_id'];
            $su_log_record->service_user_id = $data['service_user_id'];
            //$su_log_record->category_id     = $data['category_id'];

            if ($su_log_record->save()) {

                $result['response'] = '1';
            } else {
                $result['response'] = '0';
            }
        } else {
            echo '0';
            die;
        }
        return $result;
    }

    public function add_to_calendar(Request $request)
    {

        $data = $request->input();
        $service_user_id = $data['service_user_id'];
        $log_book_id     = $data['log_book_id'];

        $clndr_add       = LogBook::where('id', $log_book_id)->where('added_to_calendar', '0')->update(['added_to_calendar' => '1']);

        if ($clndr_add) {
            return redirect('/service/calendar/' . $service_user_id)->with('success', 'Log has been add to calendar successfully');
        } else {
            return redirect()->back()->with('error', 'Log record already in calendar');
        }
    }

    //ETA Handover point
    public function staffuserlist(Request $request)
    {

        $home_ids = Auth::user()->home_id;
        $searchString = ',';
        //$homde_id = 1,2
        if (strpos(@$home_ids, $searchString) !== false) {
            $home_id =  explode(',', @$home_ids);
            $login_home_id = @$home_id[0];
        } else {
            $login_home_id = @$home_ids;
        }

        $staffuserlist = User::where('is_deleted', '0')
            ->where('home_id', $login_home_id)
            ->select('id', 'name', 'user_name')
            ->where('user_type', 'N')
            ->get();
        // echo "<pre>"; print_r($staffuserlist); die;

        echo '<label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Staff User: </label>
                    <div class="col-md-6 col-sm-9 col-xs-10">
                        <div class="select-bi" style="width:100%;float:left;">
                            
                            <select class="js-example-placeholder-single select-field form-control" required id="records_list" style="width:100%;" name="staff_id">
                                <option value=""></option>';

        foreach ($staffuserlist as $value) {
            echo '<option value="' . $value->id . '">' . ucfirst($value->user_name) . '</option>';
        }

        echo '</select>
                        </div>
                       
                    </div>';
        die;
    }
    public function log_handover_to_staff_user(Request $request)
    {
        // echo "<pre>"; print_r($request->input()); die;

        if ($request->isMethod('post')) {

            $home_ids = Auth::user()->home_id;
            $searchString = ',';
            //$homde_id = 1,2
            if (strpos(@$home_ids, $searchString) !== false) {
                $home_id =  explode(',', @$home_ids);
                $login_home_id = @$home_id[0];
            } else {
                $login_home_id = @$home_ids;
            }

            $data = $request->all();
            $log_book_info = LogBook::where('id', $data['log_id'])
                ->where('is_deleted', '0')
                ->first();

            if (!empty($log_book_info)) {
                $handover_log = HandoverLogBook::where('log_book_id', $data['log_id'])
                    ->where('assigned_staff_user_id', $data['staff_user_id'])
                    ->where('service_user_id', $data['servc_use_id'])
                    ->first();

                if (empty($handover_log)) {

                    $andover_log_record                             = new HandoverLogBook;
                    $andover_log_record->log_book_id                = $data['log_id'];
                    $andover_log_record->assigned_staff_user_id     = $data['staff_user_id'];
                    $andover_log_record->service_user_id            = $data['servc_use_id'];
                    $andover_log_record->user_id                    = Auth::user()->id;
                    $andover_log_record->home_id                    = $login_home_id;
                    $andover_log_record->title                      = $log_book_info->title;
                    $andover_log_record->details                    = !empty($log_book_info->details) ? $log_book_info->details : '';
                    $andover_log_record->date                       = !empty($log_book_info->date) ? $log_book_info->date : '';
                    //$su_log_record->category_id     = $data['category_id'];

                    // echo "<pre>"; print_r($su_log_yp); die;
                    if ($andover_log_record->save()) {
                        $response = 1;
                        //$result['response'] = '1';
                    } else {
                        $response = 0;
                        //$result['response'] = '0';
                    }
                } else {
                    $response = 'already';
                    //$result['response'] = 'already_su_log_book';
                }
            } else {
                $response = 0;
            }

            echo $response;
            die;
        }
        //return $result;
    }

    //ETA Weekly Report
    public function weekly_report(Request $request, $log_book_id = null)
    {
        // $service_user_id = Auth::user()->id;

        // echo "<pre>"; print_r($request->input()); die;
        // echo $service_user_id die;
        $log_book_info = LogBook::where('id', $log_book_id)
            ->where('is_deleted', '0')
            ->first();

        // echo "<pre>"; print_r($care_team_members); die;
        if (!empty($log_book_info)) {
            echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                        <label class="col-md-3 col-sm-4 col-xs-12 p-t-7"> Title: </label>
                        <div class="col-md-9 col-sm-6 col-xs-12">
                            <input type="text" placeholder="Enter Title" class="form-control" value="' . $log_book_info->title . '" name="title1">
                        </div>
                    </div>
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                        <label class="col-md-3 col-sm-4 col-xs-12 p-t-7"> Detail: </label>
                        <div class="col-md-9 col-sm-6 col-xs-12">
                            <textarea class="form-control txtarea" rows="5" name="detail">' . $log_book_info->details . '</textarea>
                        </div>
                    </div>
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                        <label class="col-md-3 col-sm-4 col-xs-12 p-t-7"> Add To Monthly: </label>
                        <div class="col-md-9 col-sm-6 col-xs-12">
                            <div class="select-style">
                                <select name="add_monthly" class="form-control">
                                    <option value="Y">Yes</option>
                                    <option value="N">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group modal-footer m-t-0 modal-bttm">
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <input type="hidden" name="_token" value="' . csrf_token() . '">
                        <input type="hidden" name="log_book_id" value="' . $log_book_id . '">
                        <input type="hidden" name="user_id" value="' . $log_book_info->user_id . '">
                        <button class="btn btn-warning submt_wekly_report" type="submit"> Submit </button>
                        
                    </div>';
        } else {
            echo '';
        }

        // echo "<pre>"; print_r($request->input()); die;
    }

    public function weekly_report_edit(Request $request, $service_user_id)
    {
        // echo $service_user_id; die;
        if ($request->isMethod('post')) {
            // echo "<pre>"; print_r($request->input()); die;
            $home_ids = Auth::user()->home_id;
            $searchString = ',';
            //$homde_id = 1,2
            if (strpos(@$home_ids, $searchString) !== false) {
                $home_id =  explode(',', @$home_ids);
                $login_home_id = @$home_id[0];
            } else {
                $login_home_id = @$home_ids;
            }

            $log_book_info = LogBook::where('id', $request->log_book_id)
                ->where('is_deleted', '0')
                ->first();

            if (!empty($log_book_info)) {
                $su_weekly = ServiceUserReport::where('log_book_id', $request->log_book_id)
                    ->where('user_id', $request->user_id)
                    ->first();

                if (empty($su_weekly)) {
                    $su_report                      = new ServiceUserReport;
                    $su_report->home_id             = $login_home_id;
                    $su_report->user_id             = $request->user_id;
                    $su_report->log_book_id         = $request->log_book_id;
                    $su_report->title               = $request->title1;
                    $su_report->details             = $request->detail;
                    $su_report->prev_title          = $log_book_info->title;
                    $su_report->prev_details        = $log_book_info->details;
                    $su_report->date                = $log_book_info->date;
                    $su_report->add_to_monthly      = $request->add_monthly;
                    $su_report->service_user_id     = $service_user_id;

                    if ($su_report->save()) {

                        // if(isset($request->sent_mail)){
                        //     if($request->sent_mail == 'Y'){

                        //         ServiceUserCareTeam::sendReport($service_user_id , $su_report->title ,$su_report->details);
                        //     }
                        // }

                        $response =  "1";
                    } else {
                        $response =  "2";
                    }
                } else {
                    $response = 'already';
                }
            }

            echo $response;
            die;
        }
    }

    public function forms(){

        $home_id = Auth::user()->home_id;

        //staff_member
        $data['staff_members']  =   User::where('is_deleted', '0')
            ->where('home_id', Auth::user()->home_id)
            ->get();

        $data['service_users'] = ServiceUser::select('id', 'name')
            ->where('home_id', Auth::user()->home_id)
            ->where('is_deleted', '0')
            ->get();

        $data['dynamic_forms'] = DynamicFormBuilder::getFormList();
        // dd($dynamic_forms);




        return view('frontEnd.forms', $data);   
    }
}
