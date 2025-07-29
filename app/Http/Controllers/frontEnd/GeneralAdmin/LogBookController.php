<?php
namespace App\Http\Controllers\frontEnd\GeneralAdmin;
use App\Http\Controllers\frontEnd\GeneralAdminController;
use Illuminate\Http\Request;
/*use App\ServiceUserDailyRecord, App\DailyRecordScores, App\DailyRecord, App\Notification;
use App\ServiceUserEarningDailyPoints, App\ServiceUserEarningStar, App\ServiceUser, App\EarningScheme;*/
use App\LogBook, App\ServiceUser ,App\ServiceUserLogBook;
use DB, Auth;

class LogBookController extends GeneralAdminController
{
    public function index()
    {   
        //in search case editing start
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $log_book_records = LogBook::select('log_book.*','u.name as staff_name')
                                    ->where('log_book.is_deleted','0')
                                    ->where('log_book.home_id', $home_id)
                                    ->orderBy('log_book.id','desc')
                                    ->orderBy('log_book.date','asc')
                                    ->leftJoin('user as u','u.id','log_book.user_id');

        $today = date('Y-m-d 00:0:00');

        $pagination  = '';
        if(isset($_GET['search'])) {

            // echo "<pre>"; print_r($_GET['search']); die;
            $log_book_search_type = $_GET['log_book_search_type'];
            
            if($log_book_search_type == 'log_title'){

                $log_book_records = $log_book_records->where('log_book.title','like','%'.$_GET['search'].'%');
                // echo "<pre>"; print_r($_GET['search']); die;
            } else{

                $search_date = date('Y-m-d',strtotime($_GET['log_book_date_search'])).' 00:00:00';
                $search_date_next = date('Y-m-d',strtotime('+1 day', strtotime($_GET['log_book_date_search']))).' 00:00:00';

                $log_book_records = $log_book_records->where('log_book.date','>',$search_date)
                                                     ->where('log_book.date','<',$search_date_next);
            }
        }

        // $log_book_records  = $log_book_records;
        // $log_book_records  = $log_book_records->orderBy('log_book.id','asc');
        // $log_book_records  = $log_book_records->orderBy(array('log_book.date' => 'asc', 'log_book.id' => 'desc'));
      // $log_book_records  =  $log_book_records->orderBy('log_book.date','asc');
                                              //->paginate(50);
        
        if(isset($_GET['search'])) {

            $log_book_records = $log_book_records->get();
        }   
        else {
                $log_book_records = $log_book_records->paginate(20);

                if($log_book_records->links() != '')  {
                    $pagination .= '</div><div class="log_records_paginate m-l-15 position-botm">';
                    $pagination .= $log_book_records->links();
                    $pagination .= '</div>';
                }
        }
        
        if(!$log_book_records->isEmpty()){

            $pre_date = date('y-m-d',strtotime($log_book_records['0']->date));
            // echo "<pre>";print_r($pre_date);die;
        }

        foreach ($log_book_records as $key => $value) {

            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';
            
            $first = 0;

            $record_time  = date('h:i a',strtotime($value->date));
            $created_time = date('h:i a', strtotime($value->created_at));

            $rec_time     = $record_time.' ('. $created_time. ')';

            // echo $rec_time; die;
            if(isset($_GET['logged']) ||  isset($_GET['search']) ){
             
                $record_date = date('Y-m-d',strtotime($value->date));

                if($record_date != $pre_date){
                    $pre_date    = $record_date; 
               
                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                        '.date('d F Y',strtotime($record_date)).'
                                    </span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="daily-rcd-content">';
                }
                else{}
            } 

            echo '
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                    <div class="form-group col-md-11 col-sm-11 col-xs-12 r-p-0 pull-center">
                        <div class="input-group popovr">
                            <input type="text" name="edit_su_record_desc[]" class="form-control cus-control edit_record_desc_'.$value->id.' edit_rcrd"  disabled  value="'.ucfirst($value->title).' | '.$rec_time.'" />';
                             
                            if(!empty($value->details)){
                                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
                            }
                              echo '<input type="hidden" name="edit_su_record_id[]" value="'.$value->id.'" disabled="disabled" class="edit_record_id_'.$value->id.'" />
                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none">';
                                        //if(isset($add_new_case)) { 
                                        echo '<li> <a href="#" log_book_id="'.$value->id.'" class="add_to_yp_profile"> <span> <i class="fa fa-pencil"></i> </span>Add to YP Daily log</a> </li>';
                                        //}
                                        /*<li> <a href="#" log_book_id="'.$value->id.'" class="delete-log-record"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>*/
                                        echo '<li> <a href="#" log_book_id="'.$value->id.'" class="log-record-detail"> <span class="color-red"> <i class="fa fa-plus color-green"></i> </span> Details </a> </li>
                                        <li>';
                                        // if($value->added_to_calendar == '0') {
                                        echo'<a href="#" log_book_id="'.$value->id.'" class="add_to_clndr"> <span class="color-green"> <i class="fa fa-plus-circle"></i> </span> Add to Calendar</a> </li>'; 
                                        //}
                                    echo '</ul>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="input-plusbox form-group col-xs-11 p-0 detail">
                        <label class="cus-label color-themecolor"> Details: </label>
                        <div class="cus-input p-r-10">
                            <div class="input-group">
                                <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_detail_'.$value->id.' edit_rcrd " value="">'.$value->details.'</textarea>
                                <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show">'.$check.'</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="input-plusbox form-group col-xs-11 p-0 detail" style="display: block;">
                        <label class="cus-label color-themecolor"> Staff created: </label>
                        <div class="cus-input p-r-10">
                            <div class="input-group">
                              <input type="text" value="'.ucfirst($value->staff_name).'" disabled="" class="form-control ">
                            </div>
                        </div>
                    </div>
                    
                </div>
                ';
                
        }
        
        echo $pagination;
    }

    public function add(Request $request){

        if($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            // echo "<pre>"; print_r($data); die;
            /*$su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
            if(Auth::user()->home_id != $su_home_id){
                echo '0'; die; 
            }*/

            $log_book_record            = new LogBook;
            $log_book_record->title     = $data['log_title'];
            $log_book_record->date      = date('Y-m-d H:i:s', strtotime($data['log_date']));
            $log_book_record->details   = $data['log_detail'];
            // $log_book_record->date    = '';
            // $log_book_record->status  = 1;
            $log_book_record->home_id   = $home_id;
            $log_book_record->user_id   = Auth::user()->id;
            if($log_book_record->save()){
                if(!empty($data['select_usr_id'])){
                    $su_log_record                  = new ServiceUserLogBook;
                    $su_log_record->log_book_id     = $log_book_record['id'];
                    $su_log_record->service_user_id = $data['select_usr_id'];
                    $su_log_record->user_id         = Auth::user()->id;
                    if($su_log_record->save())  {
                        if($data['add_calender'] == 'on'){
                            $clndr_add   = LogBook::where('id', $log_book_record['id'])
                                                    ->where('added_to_calendar','0')
                                                    ->update([
                                                                'added_to_calendar'=> '1',
                                                            ]);

                            // if($clndr_add) {
            
                            //     $service_user                  = new ServiceUserLogBook;
                            //     $service_user->log_book_id     =  $log_book_record['id'];
                            //     $service_user->service_user_id =  $data['select_usr_id'];
                            //     $service_user->user_id         =  Auth::user()->id; 
                            //     $service_user->save();
                            // }
                        }
                    }
                }
                
                echo '1';
                //return redirect()->back()->with('success','Request submitted successfully.');

            }
            else { 
                echo "0";
                //return redirect()->back()->with('error',COMMON_ERROR);
            }
            die;
        }

    }

    public function serviceuserlist(Request $request) {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $serviceuserlist = ServiceUser::where('is_deleted','0')
                                ->where('home_id', $home_id)
                                ->select('id', 'name', 'user_name')->get();
        // echo "<pre>"; print_r($serviceuserlist); die;

        echo '<label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Child: </label>
                    <div class="col-md-6 col-sm-9 col-xs-10">
                        <div class="select-bi" style="width:100%;float:left;">
                            
                            <select class="js-example-placeholder-single select-field form-control" required id="records_list" style="width:100%;" name="su_id">
                                <option value=""></option>';

                                foreach($serviceuserlist as $value){
                                echo '<option value="'.$value->id.'">'.$value->name.'</option>';
                                }
                                
                        echo '</select>
                        </div>
                       
                    </div>';
                    die;
    }

    public function service_user_add_log(Request $request) {  

        if ($request->isMethod('post'))   {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $su_log_yp = ServiceUserLogBook::where('log_book_id', $data['log_id'])
                                           ->where('service_user_id', $data['service_user_id'])
                                           ->first();
            
            if(empty($su_log_yp)) {

                $su_log_record                  = new ServiceUserLogBook;
                $su_log_record->log_book_id     = $data['log_id'];
                $su_log_record->service_user_id = $data['service_user_id'];
                $su_log_record->user_id         = Auth::user()->id;
                //$su_log_record->category_id     = $data['category_id'];

                // echo "<pre>"; print_r($su_log_yp); die;
                if($su_log_record->save())  {
                    $response = 1;
                    //$result['response'] = '1';
                }   else   {
                    $response = 0;
                   //$result['response'] = '0';
                }
            } else{
                $response = 'already';
               //$result['response'] = 'already_su_log_book';
            }
            echo $response; die;
        } 
        //return $result;
    }

    // public function add_to_calendar(Request $request) {
        
    //     $data = $request->input();
    //     $log_book_id = $data['log_book_id'];
        
    //     $clndr_add = LogBook::where('id', $log_book_id)->where('added_to_calendar','0')->update(['added_to_calendar'=> '1']);
    //     if($clndr_add) {
    //         return redirect('/system/calendar')->with('success', 'Log has been add to calendar successfully');
    //     } else {
    //         return redirect()->back()->with('error', 'Log is already add to calendar');
    //     }

    // }

    //------ yp add to calendar 20
    public function add_to_calendar(Request $request) {
        
        $data            = $request->input();
        $user_id         = Auth::user()->id;
        $log_book_id     = $data['log_id'];
        $service_user_id = $data['su_id'];
       
        $clndr_add   = LogBook::where('id', $log_book_id)
                                ->where('added_to_calendar','0')
                                ->update([
                                            'added_to_calendar'=> '1',
                                        ]);

        if($clndr_add) {
            
            $service_user                  = new ServiceUserLogBook;
            $service_user->log_book_id     =  $log_book_id;
            $service_user->service_user_id =  $service_user_id;
            $service_user->user_id         =  $user_id; 

            if($service_user->save()){

                return redirect('service/calendar/'.$service_user_id)->with('success', 'Log has been add to YP calendar successfully');  
            }
            
        } else {
            
            return redirect()->back()->with('error', 'Log is already added to YP calendar');
        }

    }


    // Log-Book Delete Record 
    /*public function delete($log_book_id){

        if(!empty($log_book_id)){

            $log_book_record = LogBook::where('id', $log_book_id)->first();            
            if(!empty($log_book_record)){
             
                $log_book_record->is_deleted = 1;
                if($log_book_record->save()){
                    echo '1';
                } else{
                    echo '0';
                }
            }
        }
        die;
    }*/

}