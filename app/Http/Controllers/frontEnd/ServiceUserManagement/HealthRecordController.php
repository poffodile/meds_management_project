<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use DB, Auth;
use App\DynamicFormBuilder, App\DynamicForm, App\ServiceUserHealthRecord, App\Notification, App\ServiceUser, App\User, App\EarningScheme, App\DynamicFormLocation;

class HealthRecordController extends ServiceUserManagementController
{
    public function index(Request $request, $service_user_id = null)
    {
        
        $data = $request->input();
        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        $staff_members  =   User::where('is_deleted', '0')
            ->where('home_id', $home_id)
            ->get();

        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');

        $today = date('Y-m-d');
        //filter
        //update notify
        $updatenotify = array('read_notify' => 1);
        DB::table('su_health_record')->where('service_user_id', $service_user_id)->where('home_id', $home_id)->update($updatenotify);
        //    update notify

        if (!empty($data)) {

            // $log_book_records = DB::table('log_book')
            //                 ->select('log_book.*', 'user.name as staff_name','category.color as category_color')
            //                 ->whereIn('log_book.id',$su_logs)
            //                 ->join('user', 'log_book.user_id', '=', 'user.id')
            //                 ->join('category', 'log_book.category_id', '=', 'category.id')
            //                 ->orderBy('date','desc');
            $log_book_records = DB::table('su_health_record')
                ->select('su_health_record.*', 'service_user.name as staff_name')
                ->where('su_health_record.service_user_id', $request->service_user_id)
                ->where('su_health_record.is_deleted', "0")
                ->where('su_health_record.home_id', $home_id)
                ->join('service_user', 'su_health_record.service_user_id', '=', 'service_user.id')
                ->orderBy('su_health_record.created_at', 'desc');
            //->whereDate('su_health_record.created_at', '=', $today)

            if (isset($request->start_date) && $request->start_date != 'null') {
                $log_book_records = $log_book_records->whereDate('su_health_record.created_at', '>=', $request->start_date);
                // Log::info("Start Date Logs.");
                // Log::info($log_book_records->get()->toArray());
            }
            if (isset($request->end_date) && $request->end_date != 'null') {
                $log_book_records = $log_book_records->whereDate('su_health_record.created_at', '<=', $request->end_date);
                // Log::info("End Date Logs.");
                // Log::info($log_book_records->get()->toArray());
            }
            if (isset($request->keyword) && $request->keyword != 'null') {
                $log_book_records = $log_book_records->where('su_health_record.details', 'like', '%' . $request->keyword . '%');
                // Log::info("End Date Logs.");
                // Log::info($log_book_records->get()->toArray());
            }


            //$log_book_records = $log_book_records->get()->toArray();
            $log_book_records = $log_book_records->get();
            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            //print_r($log_book_records);
            //die;

            return compact('log_book_records');
        }

        //filter    
        $log_book_records = DB::table('su_health_record')
            ->select('su_health_record.*', 'service_user.name as staff_name')
            ->where('su_health_record.service_user_id', $service_user_id)
            ->where('su_health_record.is_deleted', "0")
            ->where('su_health_record.home_id', $home_id)
            ->whereDate('su_health_record.created_at', '=', $today)
            ->join('service_user', 'su_health_record.service_user_id', '=', 'service_user.id')
            ->orderBy('su_health_record.created_at', 'desc')->get();

            // dd($log_book_records);
           
        $log_book_records = collect($log_book_records)->map(function ($x) {
            return (array) $x;
        })->toArray();

        // dd($log_book_records);
        return view('frontEnd.serviceUserManagement.health_record', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'service_users', 'staff_members'));
    }

    // public function add(Request $request)
    // {   
    //   	if($request->isMethod('post'))
    // 	{
    // 		$data = $request->input();
    //         //echo '<pre>'; print_r($data); echo '</pre>'; die;
    //         $health_record                  = new ServiceUserHealthRecord;
    //         $health_record->service_user_id = $data['service_user_id'];
    //         $health_record->title           = $data['title'];
    //         $health_record->home_id         = Auth::user()->home_id;

    //         if($health_record->save())  {

    //             //saving notification start
    //             $notification                             = new Notification;
    //             $notification->service_user_id            = $data['service_user_id'];
    //             $notification->event_id                   = $health_record->id;
    //             //$notification->event_type      = 'SU_HR';
    //             $notification->notification_event_type_id = '1';
    //             $notification->event_action               = 'ADD';    
    //             $notification->home_id                    = Auth::user()->home_id;
    //             $notification->user_id                    = Auth::user()->id;                  
    //             $notification->save();
    //             //saving notification end

    //             $result = $this->index($data['service_user_id']);
    //             echo $result;       
    //         }  else {
    //             echo '0';
    //         }
    //         // die;
    // 	}
    // }
    public function add(Request $request)
    {
        $data = $request->input();
        // dd($data);

        if (!empty($data)) {
            //save form
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $formdata = json_encode($data);
            $service_user_id        = $data['service_user_id'];
            $form                   = new DynamicForm;
            $form->home_id          = $home_id;
            $form->user_id          = Auth::user()->id;
            $form->form_builder_id  = $data['dynamic_form_builder_id'];
            $form->service_user_id  = $data['service_user_id'];
            $form->location_id      = $data['location_id'];
            $form->title            = $data['title'];
            $form->time             = $data['time']; 
            $form->details          = $data['details']; 
            $form->pattern_data     = $formdata;

            if (isset($data['alert_status'])) {
                $form->alert_status     = $data['alert_status'];
                if ($data['alert_status'] == '1') {
                    if (!empty($data['alert_date'])) {
                        $form->alert_date   = date('Y-m-d', strtotime($data['alert_date']));
                    } else {
                        $form->alert_date   = date('Y-m-d');
                    }
                }
            }


            if (!empty($data['date'])) {
                $form->date = date('Y-m-d', strtotime($data['date']));
            }

            if ($form->save()) {
                $location_id = $data['location_id'];
                $location_tag = DynamicFormLocation::where('id', $location_id)->value('tag');
                $notification_event_type_id = DB::table('notification_event_type')->where('table_linked', 'LIKE', 'su_' . $location_tag)->value('id');
                if (!empty($notification_event_type_id)) {
                    $notification                             = new Notification;
                    $notification->service_user_id            = $data['service_user_id'];
                    $notification->event_id                   = $form->id;
                    $notification->notification_event_type_id = $notification_event_type_id;
                    $notification->event_action               = 'ADD';
                    $notification->home_id                    = $home_id;
                    $notification->user_id                    = Auth::user()->id;
                    $notification->save();
                }
                if (!empty($data['send_to'])) {
                    $senders = $data['send_to'];
                    foreach ($senders as $key => $sender) {
                        $s_type = explode('-', $sender);

                        if ($s_type[0] == 'ct') {
                            $type = 'ct';
                            $care_team_id = $s_type[1];
                            // Parent::sendEmailNotificationDynamicForm($care_team_id, $type, $data['service_user_id'], $data['dynamic_form_builder_id']);
                        } else if ($s_type[0] ==  'sc') {
                            // echo "sc_yes";
                            $type = 'sc';
                            $su_contact_id = $s_type[1];
                            // Parent::sendEmailNotificationDynamicForm($su_contact_id, $type, $data['service_user_id'], $data['dynamic_form_builder_id']);
                        }
                    }
                }
                $form_insert_id = $form->id;
            } else {
                $form_insert_id = '0';
            }
            // echo "<br>"; print_r($form_insert_id);  die;

            if ($form_insert_id != 0) {

                //if this dynamic form is mfc form then manage earning points
                $location_ids = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->value('location_ids');
                $location_ids_arr = explode(',', $location_ids);
                if (in_array('5', $location_ids_arr)) {
                    EarningScheme::updateEarning($data['service_user_id']);
                }
                //update earning scheme in case of mfc form ends here 
                //sourabh log insert
                //logtype
                if ($data['logtype'] == 1) {
                    $d_form_name = DB::table('dynamic_form_builder')->where('id', $data['dynamic_form_builder_id'])->value('title');
                    $inserlogbook = array(
                        'title' => $data['title'],
                        'category_id' => 0,
                        'category_name' => 'Visitor',
                        'category_icon' => 'fa fa-users',
                        'date' => date('Y-m-d H:i:s', strtotime($data['date'])),
                        'details' => $data['details'],
                        'home_id' => $home_id,
                        'user_id' => Auth::user()->id,
                        'image_name' => '',
                        'is_late' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    //return $inserlogbook;
                    //die;
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
                } else {
                    $insert_su_health_record = array(
                        'home_id' => $home_id,
                        'service_user_id' => $data['service_user_id'],
                        'contact_id' => 0,
                        'care_team_id' => 0,
                        'title'=>$data['title'],
                        'status' => 1,
                        'details'=>$data['details'],
                        'is_deleted' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    );
                    DB::table('su_health_record')->insert($insert_su_health_record);
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

    public function edit(Request $request)
    {

        if ($request->isMethod('post')) {

            $data = $request->input();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            if (isset($data['edit_health_record_id'])) {

                $edit_health_record_id = $data['edit_health_record_id'];

                foreach ($edit_health_record_id as $key => $value) {

                    $health_record = ServiceUserHealthRecord::find($value);

                    $su_home_id = ServiceUser::where('id', $health_record->service_user_id)->value('home_id');

                    if ($su_home_id == $home_id) {

                        $health_record->title = $data['edit_health_record_title'][$key];
                        $health_record->save();
                    }
                }
            }

            //saving notification start
            $notification                             = new Notification;   
            $notification->service_user_id            = $data['service_user_id'];
            $notification->event_id                   = $health_record->id;
            // $notification->event_type      = 'SU_HR';
            $notification->notification_event_type_id = '1';
            $notification->event_action               = 'EDIT';
            $notification->home_id                    = $home_id;
            $notification->user_id                    = Auth::user()->id;
            $notification->save();
            //saving notification end

            $res = $this->index($data['service_user_id']);
            echo $res;
            die;
        }
    }

    public function delete($su_health_record_id = null)
    {
        $health_record = ServiceUserHealthRecord::find($su_health_record_id);
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        if (!empty($health_record)) {

            $su_home_id = ServiceUser::where('id', $health_record->service_user_id)->value('home_id');

            if ($su_home_id == $home_id) {

                $res = ServiceUserHealthRecord::where('id', $su_health_record_id)->delete();
                echo $res;
            }
        }
        die;
    }
}
