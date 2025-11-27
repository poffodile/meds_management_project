<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUserReport, App\ServiceUserCareTeam, App\CareTeam, App\ServiceUser, App\ServiceUserLivingSkill, App\DynamicForm, App\DynamicFormBuilder, App\ServiceUserEducationRecord, App\ServiceUserDailyRecord, App\ServiceUserHealthRecord, App\Mood, App\ServiceUserMood;
use Response;
use App\Models\SuBehavior;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TCPDF;

class ReportController extends ServiceUserManagementController
{
    public function index(Request $request)
    {

        // if($request->isMethod('post')){
        // // echo "<pre>"; print_r($request->input()); die;

        //     $report_name        = $request->reprt_name;
        //     if($report_name == 'M'){
        //         $r_name = 'Monthly';
        //     }else{
        //         $r_name = 'Weekly';
        //     }
        //     $service_user_id    = $request->service_user_id;
        //     // echo $service_user_id; die;

        //     $report_detail =  ServiceUserReport::select('su_report.*','u.name as staff_name')
        //                                 ->leftJoin('user as u','u.id','su_report.user_id')
        //                                 ->where('su_report.service_user_id', $service_user_id)
        //                                 ->orderBy('su_report.id','desc');
        //     //                             ->get()
        //     //                             ->toArray();
        //     // echo "<pre>"; print_r($report_detail); die;

        //     if($report_name == 'M'){
        //         $report_detail =  $report_detail->where('su_report.add_to_monthly','Y');
        //     }
        //     // $report_detail = $report_detail->get()->toArray();
        //     // echo "<pre>"; print_r($report_detail); die;

        //                             //LogBook::where('is_deleted','0')
        // }
        // $today = date('Y-m-d 00:0:00');

        // $pagination  = '';
        // if(isset($_GET['search'])) {

        //     // echo "<pre>"; print_r($_GET['search']); die;
        //     $log_book_search_type = $_GET['log_book_search_type'];

        //     if($log_book_search_type == 'log_title'){

        //         $su_log_book_records = $su_log_book_records->where('log_book.title','like','%'.$_GET['search'].'%');

        //     } else{

        //         $search_date = date('Y-m-d',strtotime($_GET['log_book_date_search'])).' 00:00:00';
        //         $search_date_next = date('Y-m-d',strtotime('+1 day', strtotime($_GET['log_book_date_search']))).' 00:00:00';

        //         $su_log_book_records = $su_log_book_records->where('log_book.date','>',$search_date)
        //                                              ->where('log_book.date','<',$search_date_next);
        //     }
        // }

        // $report_detail  = $report_detail->orderBy('su_report.id','desc')
        //                                       ->orderBy('su_report.date','desc');
        //                                       //->paginate(50);

        // if(isset($_GET['search'])) {

        //     $report_detail = $report_detail->get();
        // }   
        // else {
        //         $report_detail = $report_detail->paginate(2);

        //         if($report_detail->links() != '')  {
        //             $pagination .= '</div><div class="log_records_paginate m-l-15 position-botm">';
        //             $pagination .= $report_detail->links();
        //             $pagination .= '</div>';
        //     }
        // }

        // if(!$report_detail->isEmpty()){
        //     $pre_date = date('y-m-d',strtotime($report_detail['0']->date));
        // }

        // foreach ($report_detail as $key => $value) {

        //     $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';

        //     $first = 0;
        //     $record_time = date('h:i a',strtotime($value->date));
        //     $created_time = date('h:i a', strtotime($value->created_at));
        //     $rec_time = $record_time.' ('. $created_time. ')';

        //     // if(isset($_GET['logged']) ||  isset($_GET['search']) ){ 
        //         $record_date = date('Y-m-d',strtotime($value->date));

        //         if($record_date != $pre_date){
        //             $pre_date = $record_date; 

        //             echo '</div>
        //             <div class="daily-rcd-head">
        //                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
        //                     <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        //                         <a  class="date-tab">
        //                             <span class="pull-left">
        //                                 '.date('d F Y',strtotime($record_date)).'
        //                             </span>
        //                             <i class="fa fa-angle-right pull-right"></i>
        //                         </a>
        //                     </div>
        //                 </div>
        //             </div>
        //             <div class="daily-rcd-content">';
        //         }
        //         else{}
        //     // } 

        //     echo '
        //         <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
        //             <div class="form-group col-md-11 col-sm-11 col-xs-12 r-p-0 pull-center">
        //                 <div class="input-group popovr">
        //                     <input type="text" name="edit_su_record_desc[]" class="form-control cus-control edit_record_desc_'.$value->id.' edit_rcrd"  disabled  value="'.ucfirst($value->title).' | '.$rec_time.'" />';

        //                     if(!empty($value->details)){
        //                         echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
        //                     }
        //                       echo '<input type="hidden" name="edit_su_record_id[]" value="'.$value->id.'" disabled="disabled" class="edit_record_id_'.$value->id.'" />';
        //                         if($report_name == 'M'){
        //                             echo '<span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
        //                                 <i class="fa fa-cog"></i>
        //                                 <div class="pop-notifbox">
        //                                     <ul class="pop-notification" type="none">';
        //                                         //if(isset($add_new_case)) { 
        //                                         echo '<li> <a href="#" log_book_id="'.$value->id.'" class="edit-mnthly-reprt"> <span> <i class="fa fa-edit"></i> </span> Edit </a> </li>

        //                                     </ul>
        //                                 </div>
        //                             </span>';
        //                         }
        //                 echo '</div>
        //             </div>

        //             <div class="input-plusbox form-group col-xs-11 p-0 detail">
        //                 <label class="cus-label color-themecolor"> Details: </label>
        //                 <div class="cus-input p-r-10">
        //                     <div class="input-group">
        //                         <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_detail_'.$value->id.' edit_rcrd " value="">'.$value->details.'</textarea>
        //                     </div>
        //                 </div>
        //             </div>

        //             <div class="input-plusbox form-group col-xs-11 p-0 detail" style="display: block;">
        //                 <label class="cus-label color-themecolor"> Staff created: </label>
        //                 <div class="cus-input p-r-10">
        //                     <div class="input-group">
        //                       <input type="text" value="'.ucfirst($value->staff_name).'" disabled="" class="form-control ">
        //                     </div>
        //                 </div>
        //             </div>

        //         </div>
        //         ';

        // }

        // echo $pagination;
        $service_user_id = base64_decode($request->key);
        //Calender Events Added
        $totaleventsadded = DB::table('su_calendar_event')->where('service_user_id', $service_user_id)->where('is_deleted', 0)->count();
        //pollice called
        $totalpolicecall = DB::table('log_book')
            ->join('su_log_book', 'log_book.id', '=', 'su_log_book.log_book_id')
            ->where('su_log_book.service_user_id', $service_user_id)
            ->where('log_book.category_id', '1')
            ->count();
        //appointments
        $totalappointments = DB::table('log_book')
            ->join('su_log_book', 'log_book.id', '=', 'su_log_book.log_book_id')
            ->where('su_log_book.service_user_id', $service_user_id)
            ->where('log_book.category_id', '13')
            ->count();
        //building left
        $missingserviceuser = DB::table('su_afc')->where('service_user_id', $service_user_id)->where('afc_status', 0)->count();
        //current year month list
        $month = [];

        for ($m = 1; $m <= 12; $m++) {
            $month[] = date('m', mktime(0, 0, 0, $m, 1, date('Y')));
        }

        $currentyear = date('Y');
        $policecallpermonth = array();
        foreach ($month as $mpvalue) {
            $totalpolicecallmonth = DB::table('log_book')
                ->join('su_log_book', 'log_book.id', '=', 'su_log_book.log_book_id')
                ->where('su_log_book.service_user_id', $service_user_id)
                ->where('log_book.category_id', '13')
                ->whereMonth('log_book.created_at', $mpvalue)
                ->whereYear('log_book.created_at', $currentyear)
                ->count();
            array_push($policecallpermonth, $totalpolicecallmonth);
        }
        //print_r(implode(",",$policecallpermonth));
        //die;
        $policecallpermonths = implode(",", $policecallpermonth);
        //appointments
        $appointmentpermonth = array();
        foreach ($month as $mpvalues) {
            $totalappointmentmonth = DB::table('log_book')
                ->join('su_log_book', 'log_book.id', '=', 'su_log_book.log_book_id')
                ->where('su_log_book.service_user_id', $service_user_id)
                ->where('log_book.category_id', '1')
                ->whereMonth('log_book.created_at', $mpvalues)
                ->whereYear('log_book.created_at', $currentyear)
                ->count();
            array_push($appointmentpermonth, $totalappointmentmonth);
        }

        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $mood = Mood::where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->where('status', 1)
            ->get();

        // dd($mood);
            

        $lastMonth = now()->subDays(30);

        $mood_graph = ServiceUserMood::where('service_user_id', $service_user_id)
        ->whereDate('su_mood.created_at', '>=', $lastMonth)
        ->join('mood', 'mood.id', '=', 'su_mood.mood_id')
        ->selectRaw('DATE(su_mood.created_at) AS date, su_mood.mood_id AS mood_id')
        ->orderBy('date', 'ASC')
        ->get()
        ->toArray();

        $behavior_graph = SuBehavior::where('service_user_id', $service_user_id)
                    ->whereDate('created_at', '>=', $lastMonth)
                    ->selectRaw('DATE(created_at) as date, rate')
                    ->orderBy('date', 'ASC')
                    ->get()
                    ->toArray();

        $incidentCount =  DynamicForm::where('service_user_id', $service_user_id) 
                        ->where('location_id', 4)
                        ->where('is_deleted', 0)
                        ->count();


         // Get average rating and count for this service user
        $ratingStats = DB::table('su_behavior')
            ->where('service_user_id', $service_user_id)
            ->select(DB::raw('AVG(rate) as avg_rating'), DB::raw('COUNT(*) as rating_count'))
            ->where('home_id', $home_id)
            ->first();

        $avg_rating = $ratingStats && $ratingStats->avg_rating ? round($ratingStats->avg_rating, 1) : 0;
        $rating_count = $ratingStats ? intval($ratingStats->rating_count) : 0;

        $current_moods = DB::table('su_mood')->select('su_mood.*', 'mood.id as mood_id', 'mood.name', 'mood.image')
            ->where('su_mood.is_deleted', '0')
            ->where('mood.home_id', $home_id)
            ->where('su_mood.service_user_id', $service_user_id)
            ->join('mood', 'mood.id', 'su_mood.mood_id')
            ->whereDate('su_mood.created_at', \Carbon\Carbon::today())
            ->orderBy('su_mood.id', 'desc')
            ->first();


        $today = date('Y-m-d');

        $task_count['completed_targets'] = DB::table('su_placement_plan')
            ->where('service_user_id', $service_user_id)
            ->where('status', '1')
            ->count();

        $task_count['active_targets'] = DB::table('su_placement_plan')
            ->where('service_user_id', $service_user_id)
            ->whereDate('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->where('status', '0')
            ->count();

        $task_count['pending_targets'] = DB::table('su_placement_plan')
            ->where('service_user_id', $service_user_id)
            ->whereDate('date', '<=', $today)
            ->where('status', '0')
            ->orderBy('created_at', 'desc')
            ->count();

        // dd($task_count);

        $appointmentpermonths = implode(",", $appointmentpermonth);
        return view('frontEnd.serviceUserManagement.report', compact('service_user_id', 'totaleventsadded', 'totalappointments', 'totalpolicecall', 'missingserviceuser', 'policecallpermonths', 'appointmentpermonths', 'mood_graph','behavior_graph', 'mood', 'incidentCount', 'avg_rating', 'rating_count', 'current_moods','task_count'));
    }

    public function monthly_report_detail($log_book_id)
    {
        if (!empty($log_book_id)) {

            $report_detail = ServiceUserReport::select('su_report.*', 'su.name as service_user_name', 'su.id as service_user_id', 'u.name as staff_name')
                ->where('su_report.id', $log_book_id)
                ->leftJoin('service_user as su', 'su.id', 'su_report.service_user_id')
                ->leftJoin('user as u', 'u.id', 'su_report.user_id')
                ->first();
            if (!empty($report_detail)) {

                echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Child: </label>
                            <div class="col-md-10 col-sm-10 col-xs-10 p-l-30">
                                <div class="select-style">
                                    <select name="r_service_user_id" class=""/ disabled="disabled">
                                        <option value="' . $report_detail->service_user_id . '">' . $report_detail->service_user_name . '</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Staff: </label>
                            <div class="col-md-10 col-sm-10 col-xs-10 p-l-30">
                                
                                <input type="text" class="form-control" disabled="disabled" placeholder="" name="r_staff_name" value="' . $report_detail->staff_name . '" />
                                
                            </div>
                        </div>
                        
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Title: </label>
                            <div class="col-md-10 col-sm-10 col-xs-10 p-l-30">
                                
                                <input type="text" class="form-control" placeholder="" name="r_title" value="' . $report_detail->title . '"/>
                                
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Details: </label>
                            <div class="col-md-10 col-sm-10 col-xs-10 p-l-30">
                                <div class="select-bi">
                                    <textarea name="r_detail" class="form-control detail-info-txt" rows="3">' . $report_detail->details . '</textarea>
                                </div>
                            </div>
                        </div>
            
                        <div class="form-group modal-footer m-t-0 modal-bttm">
                            <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            
                            <input type="hidden" name="su_report_id" value="' . $log_book_id . '">
                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                            <button class="btn btn-warning sbmt-edit-rprt" type="submit"> Submit </button>
                        </div>';
            }
        }
    }

    public function edit_report_detail(Request $request)
    {

        // echo "<pre>"; print_r($request->input()); //die;
        if ($request->isMethod('post')) {

            $su_report_id = $request->su_report_id;

            $edit_dtl = ServiceUserReport::where('id', $su_report_id)
                ->update([
                    'title'     => $request->r_title,
                    'details'   => $request->r_detail
                ]);
            if ($edit_dtl) {
                echo "1";
            } else {
                echo "2";
            }
        } else {
            echo "2";
        }
    }

    public function send_mail_to_careteam($log_book_id)
    {
        // echo $log_book_id; die;
        if (!empty($log_book_id)) {

            $service_user = ServiceUserReport::select('service_user_id', 'title', 'details')
                ->where('id', $log_book_id)
                ->first();

            $service_user_id    = $service_user->service_user_id;
            $title              = $service_user->title;
            $details            = $service_user->details;

            if (!empty($service_user)) {

                $send_mail = ServiceUserCareTeam::sendReport($service_user_id, $title, $details);

                if ($send_mail == '') {
                    echo "1";
                } else {
                    echo "0";
                }
            } else {
                echo "0";
            }
        } else {
            echo "0";
        }
    }

    public function social_worker($service_user_id)
    {
        // echo $service_user_id; die;

        $social_workers = Careteam::where('service_user_id', $service_user_id)
            ->where('is_deleted', '0')
            ->where('job_title_id', '2')
            ->orWhere('job_title_id', '10')
            ->where('home_id', Auth::user()->home_id)
            ->get();
        // echo "<pre>"; print_r($social_workers); die;
        if (!empty($social_workers)) {
            // echo '<option value="">Select Social Worker</option>';
            foreach ($social_workers as $key => $value) {
                echo '<option value="' . $value->id . '">' . $value->name . '</option>';
            }
        } else {
            echo '<option value="">No Record Found</option>';
        }
    }

    public function send_mail_social_work(Request $request)
    {


        if ($request->isMethod('post')) {
            // echo "<pre>"; print_r($request->input()); die;

            $current_date = date('d M, Y');
            $created_by = Auth::user()->name;

            $service_user_id    = $request->srrvc_usr_id;
            $social_worker_ids  = $request->social_worker_id;
            $sel_date_range     = $request->hourrange;
            $report_type        = $request->report_type;

            // echo "<pre>"; print_r($social_worker_ids); die;
            $start = substr($sel_date_range, 0, 10);
            $end   = substr($sel_date_range, 13, 24);

            if (!empty($sel_date_range)) {

                foreach ($social_worker_ids as $key => $value) {
                    $social_worker_dtl = DB::table('su_care_team')
                        ->select('id', 'job_title_id', 'name', 'email', 'phone_no', 'image', 'address')
                        ->where('id', $value)
                        ->where('is_deleted', '0')
                        ->first();
                    // echo "<pre>"; print_r($social_worker_dtl); die;


                    $service_user_dtl = ServiceUser::where('id', $service_user_id)
                        ->where('is_deleted', '0')
                        ->first();
                    // echo "<pre>"; print_r($service_user_dtl); die;

                    $su_living_skills = ServiceUserLivingSkill::select('su_living_skill.*', 'ls.description')
                        ->join('living_skill as ls', 'su_living_skill.living_skill_id', '=', 'ls.id')
                        ->where('ls.status', '1')
                        ->where('su_living_skill.is_deleted', '0')
                        ->where('su_living_skill.service_user_id', $service_user_id)
                        ->orderBy('su_living_skill.id', 'desc')
                        ->orderBy('su_living_skill.created_at', 'desc')
                        // ->whereBetween('su_living_skill.created_at',[$start, $end])
                        ->get();
                    // echo "<pre>"; print_r($su_living_skills); //die; 
                    $form_bildr_ids_data = DynamicFormBuilder::select('id')->whereRaw('FIND_IN_SET(5,location_ids)')->get()->toArray();
                    $form_bildr_ids      = array_map(function ($v) {
                        return $v['id'];
                    }, $form_bildr_ids_data);
                    $mfc_afc_recd         = DynamicForm::select('date', 'title', 'details')
                        ->where('is_deleted', '0')
                        ->where('service_user_id', $service_user_id)
                        ->orderBy('created_at', 'desc')
                        ->whereIn('form_builder_id', $form_bildr_ids)
                        // ->whereBetween('created_at',[$start, $end])
                        ->get();

                    // echo "<pre>"; print_r($mfc_afc_recd); die;
                    //General Behaviour
                    $su_daily_record_query = ServiceUserDailyRecord::select('su_daily_record.*', 'dr.description')
                        ->join('daily_record as dr', 'su_daily_record.daily_record_id', '=', 'dr.id')
                        ->where('dr.status', '1')
                        ->where('su_daily_record.is_deleted', '0')
                        ->where('su_daily_record.service_user_id', $service_user_id)
                        ->orderBy('su_daily_record.id', 'desc')
                        ->orderBy('su_daily_record.created_at', 'desc')
                        // ->whereBetween('su_daily_record.created_at',[$start, $end])
                        ->get();
                    // echo "<pre>"; print_r($su_daily_record_query); die;
                    $su_edu_records = ServiceUserEducationRecord::select('su_education_record.*', 'er.description', 'er.amount')
                        ->join('education_record as er', 'su_education_record.education_record_id', '=', 'er.id')
                        ->where('er.status', '1')
                        ->where('su_education_record.is_deleted', '0')
                        ->where('su_education_record.service_user_id', $service_user_id)
                        ->orderBy('su_education_record.id', 'desc')
                        ->orderBy('su_education_record.created_at', 'desc')
                        // ->whereBetween('su_education_record.created_at',[$start, $end])
                        ->get();
                    // echo "<pre>"; print_r($su_edu_records); die; 
                    $su_health_record         = ServiceUserHealthRecord::select('created_at', 'title', 'details')
                        ->where('is_deleted', '0')
                        ->where('service_user_id', $service_user_id)
                        ->orderBy('created_at', 'desc')
                        // ->whereBetween('created_at',[$start, $end])
                        ->get();
                    // echo "<pre>"; print_r($su_health_record); die; 
                    // echo "<pre>"; print_r($su_health_record); die;
                    $su_moods = DB::table('su_mood')->select('su_mood.created_at', 'su_mood.description', 'mood.name as title')
                        ->where('su_mood.is_deleted', '0')
                        ->where('mood.home_id', Auth::user()->home_id)
                        ->where('su_mood.service_user_id', $service_user_id)
                        ->join('mood', 'mood.id', 'su_mood.mood_id')
                        // ->orderBy('su_mood.id', 'desc')  
                        ->orderBy('su_mood.created_at', 'desc')
                        // ->whereBetween('su_mood.created_at',[$start, $end])                           
                        ->get();

                    $progress_report =  ServiceUserReport::select('su_report.date', 'su_report.title', 'su_report.details')
                        // ->leftJoin('user as u','u.id','su_report.user_id')
                        ->where('su_report.service_user_id', $service_user_id)
                        ->orderBy('su_report.id', 'desc')
                        ->orderBy('su_report.date', 'desc');
                    // ->whereBetween('su_report.date',[$start, $end]);
                    if ($report_type == 'M') {
                        $rprt_titl = 'MONTHLY';
                        $report_name = 'MONTHLY LOG';
                        $progress_report = $progress_report->where('add_to_monthly', 'Y')->get();
                    } else {
                        $rprt_titl = 'WEEKLY';
                        $report_name = 'WEEKLY LOG';
                        $progress_report = $progress_report->where('add_to_monthly', 'N')->get();
                    }
                    // echo "<pre>"; print_r($weekly_log); die;
                    // $social_worker_dtl = DB::table('su_care_team')
                    //                         ->select('id','job_title_id','name','email','phone_no','image','address')
                    //                         ->where('service_user_id',$service_user_id)
                    //                         ->where('is_deleted','0')
                    //                         ->first();




                    $curr  = date('d-m-y');
                    include('vendor/tcpdf/tcpdf.php');

                    $tcpdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    $tcpdf->AddPage('L', 'A4');
                    $tcpdf->SetCreator(PDF_CREATOR);
                    $title = "SCITS";
                    //$subtitle = (isset($orderdata['Order']['opt_out']) && $orderdata['Order']['opt_out'])? 'This is an Opt Out PO' : '';
                    $subtitle = 'dsds';
                    $tcpdf->SetTitle('Rota Shifts');
                    $tcpdf->SetSubject('Subject');

                    $tcpdf->setPrintHeader(true);
                    $tcpdf->setPrintFooter(false);
                    // $tcpdf->SetDefaultMonospacedFont('helvetica');
                    $tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                    $tcpdf->SetMargins(10, 10, 10, true);
                    //$tcpdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
                    $tcpdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                    $tcpdf->SetFont('helvetica', '', 9);

                    $tcpdf->setFontSubsetting(false);


                    $tcpdf->setFontSubsetting(false);
                    $list = '';
                    $i    = 1;
                    $report = '';

                    $url = url('/public/images/scits.png');

                    $report .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; text-align: center;">
                                    <tbody>
                                        <tr>
                                            <td style="display: inline-block;" border="1|0">
                                                <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; margin:0 auto; max-width:600px; background-color: #fafafa;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="color: rgb(77, 87, 99); font-size: 18px; padding: 15px 0px; font-weight: bold;">' . $rprt_titl . ' PROGRESS REPORT</td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <img src="' . $url . '" alt="Company Logo" style="width:75px; margin:10px 0;"/>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="color: rgb(103, 113, 125); line-height: 1.4; font-size:14px;">7 Station Rd <br> PRESCOTT <br> LIVERPOOL <br> L34 5SN</td>
                                                        </tr>
                                                    </tbody>    
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-top-color: #333; border-top-width:1px, border-top-style:solid; border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333;">Name</td>
                                                            <td style="border-right-width: 1px; border-right-style: solid; border-right-color: #333; font-size: 14px; color: rgb(103, 113, 125);">' . ucfirst($service_user_dtl->name) . '</td>
                                                            <td style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333;">DATE OF BIRTH</td>
                                                            <td style="padding: 15px 0px; font-size: 14px; color: rgb(103, 113, 125);">' . date('d M, Y', strtotime($service_user_dtl->date_of_birth)) . '</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">INDEPENDENT LIVING SKILLS</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($su_living_skills->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($su_living_skills as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value['created_at']));
                            $title = $value->description;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                 <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                                    <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                                    <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td></tr>';
                        }
                    }
                    $report .= '</tbody>
                                                </table>

                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">M.F.C / CURFEW / AFC</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($mfc_afc_recd->isEmpty()) {

                        $report .= '<tr>
                                                                        <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                                    </tr>';
                    } else {
                        foreach ($mfc_afc_recd as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value['date']));
                            $title = $value->description;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                 <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                                    <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                                    <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td></tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">BEHAVIOUR AND ATTITUDE</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($su_daily_record_query->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($su_daily_record_query as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value['created_at']));
                            $title = $value->description;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                 <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td></tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">WORK/TRAINING/EDUCATION</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($su_edu_records->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($su_edu_records as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value['created_at']));
                            $title = $value->description;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                 <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td></tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">HEALTH RECORD</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($su_health_record->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($su_health_record as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value['created_at']));
                            $title = $value->title;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td>
                                                                    </tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">Emotional Thermometer</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #333;">Details</td>
                                                        </tr>';
                    if ($su_moods->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($su_moods as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value->created_at));
                            $title = $value->title;
                            $detail = $value->description;
                            // echo $date; die;
                            $report .= '<tr>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">Feeling - ' . $title . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td>
                                                                    </tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); border-bottom-color: #333; border-bottom-width:1px; border-bottom-style:solid; table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="12" style="text-align: left; color: rgb(51, 51, 51); border-bottom-color: #333; border-bottom-width:1px, border-bottom-style:solid; font-size: 14px; font-weight: bold;">' . $report_name . '</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333;">Date</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333;">Title</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">Details</td>
                                                        </tr>';
                    if ($progress_report->isEmpty()) {

                        $report .= '<tr>
                                                                <td colspan="12" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold;">No Record Found</td>
                                                             
                                                            </tr>';
                    } else {
                        foreach ($progress_report as $key => $value) {
                            // echo "<pre>"; print_r($value); die;
                            $date = date('d M, Y', strtotime($value->date));
                            $title = $value->title;
                            $detail = $value->details;
                            // echo $date; die;
                            $report .= '<tr>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $date . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $title . '</td>
                                                                        <td colspan="4" style="color: #67717d; font-size: 14px; vertical-align: top;">' . $detail . '</td>
                                                                    </tr>';
                        }
                    }

                    $report .= '</tbody>
                                                </table>
                                                <table width="100%" cellpadding="10" cellspacing="0" valign="top" style="margin: 0px auto; max-width: 600px; background-color: rgb(250, 250, 250); table-layout: fixed;">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">Completed By</td>
                                                            <td colspan="4" style="color: rgb(103, 113, 125); font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">' . $created_by . '</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">Date</td>
                                                            <td colspan="4" style="color: rgb(103, 113, 125); font-size: 14px; vertical-align: top;">' . $current_date . '</td>
                                                        </tr>';
                    $report .= '<tr>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">Emailed To</td>
                                                            <td colspan="4" style="color: rgb(103, 113, 125); font-size: 14px; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top; word-wrap:break-word;">' . $social_worker_dtl->email . '</td>
                                                            <td colspan="4" style="color: rgb(77, 87, 99); font-size: 14px; font-weight: bold; border-right-width: 1px; border-right-style: solid; border-right-color: #333; vertical-align: top;">Date</td>
                                                            <td colspan="4" style="color: rgb(103, 113, 125); font-size: 14px; vertical-align: top;">' . $current_date . '</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>';


                    // print_r($list); die;
                    $html = <<<EOD
                            {$report}
EOD;

                    // $tcpdf->writeHTML($html, true, false, true, false, '');
                    $tcpdf->writeHTML($html, true, 0, true, 0, 0, 0);
                    $tcpdf->lastPage();
                    $path = public_path() . '/images/reportPdf/' . ucfirst($service_user_dtl->name) . '.pdf';
                    $tcpdf->Output($path, 'F');

                    // if(!empty($social_worker_ids)){

                    $company_name = PROJECT_NAME;
                    $flag = '0';

                    // foreach ($social_worker_ids as $key => $value) {

                    // $social_worker_dtl = DB::table('su_care_team')
                    //                         ->select('id','job_title_id','name','email','phone_no','image','address')
                    //                         ->where('id',$value)
                    //                         ->where('is_deleted','0')
                    //                         ->first();
                    if (!empty($social_worker_dtl)) {

                        // $email  = $social_worker_dtl->email;
                        $email              = 'promatics.hashishgarg@gmail.com';
                        $name               = $social_worker_dtl->name;
                        $report_user_name   = $service_user_dtl->name;

                        if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                            Mail::send('emails.report', ['name' => $name, 'report_user_name' => $report_user_name], function ($message) use ($email, $company_name, $path) {
                                $message->attach($path)->to($email, $company_name)->subject('SCITS Report');
                            });
                            $flag = '1';
                        }
                    }
                    // }
                    if ($flag == '1') {

                        return redirect()->back()->with('success', 'Report Send Successfully.');
                    } else {
                        return redirect()->back()->with('error', COMMON_ERROR);
                    }
                    // }
                }
            }
        }
    }
}
