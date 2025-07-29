<?php

namespace App\Http\Controllers\Rota;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rota, App\User, App\RotaShift, App\RotaAssignEmployee,App\LeaveType, App\Staffleaves,  App\ServiceUser, App\AccessRight ; 
use Session, DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Auth;

class RotaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function check_access_rights(){
      // AccessRight::where('')
    }
    public function index()
    {
        // return view('welcome');
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $leave['sickness'] = Staffleaves::where('is_deleted', 1 )->where('leave_status', 1)->where('leave_type', 2)->where('home_id', $home_id)->count();
        $leave['lateness'] = Staffleaves::where('is_deleted', 1 )->where('leave_status', 1)->where('leave_type', 3)->where('home_id', $home_id)->count();

        $date_min_three = Carbon::parse('Now -3 days')->format('Y-m-d');
        
        $annual1 =  Staffleaves::where('start_date', '<=', $date_min_three)->where('end_date', '>=', $date_min_three)->where('leave_type', 1)->where('is_deleted', 1)->where('home_id',  $home_id)->where('leave_status', 1)->count();
        $sickness1 = Staffleaves::where('start_date', '<=', $date_min_three)->where('end_date', '>=', $date_min_three)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness1 =  Staffleaves::where('start_date', '=', $date_min_three)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other1 =  Staffleaves::where('start_date', '<=', $date_min_three)->where('end_date', '>=', $date_min_three)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_min_three'] = $annual1 + $sickness1 + $lateness1 + $other1;

        $date_min_two = Carbon::parse('Now -2 days')->format('Y-m-d');
        $annual2 =  Staffleaves::where('start_date', '<=', $date_min_two)->where('end_date', '>=', $date_min_two)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness2 = Staffleaves::where('start_date', '<=', $date_min_two)->where('end_date', '>=', $date_min_two)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness2 =  Staffleaves::where('start_date', '=', $date_min_two)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other2 =  Staffleaves::where('start_date', '<=', $date_min_two)->where('end_date', '>=', $date_min_two)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_min_two'] =  $annual2 + $sickness2 + $lateness2 + $other2;

        $date_min_one = Carbon::parse('Now -1 days')->format('Y-m-d');
        $annual3 =  Staffleaves::where('start_date', '<=', $date_min_one)->where('end_date', '>=', $date_min_one)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness3 = Staffleaves::where('start_date', '<=', $date_min_one)->where('end_date', '>=', $date_min_one)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness3 =  Staffleaves::where('start_date', '=', $date_min_one)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other3 =  Staffleaves::where('start_date', '<=', $date_min_one)->where('end_date', '>=', $date_min_one)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_min_one'] =  $annual3 + $sickness3 + $lateness3 + $other3;

        $date_current = Carbon::now()->format('Y-m-d');
        $annual4 =  Staffleaves::where('start_date', '<=', $date_current)->where('end_date', '>=', $date_current)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness4 = Staffleaves::where('start_date', '<=', $date_current)->where('end_date', '>=', $date_current)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness4 =  Staffleaves::where('start_date', '=', $date_current)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other4 =  Staffleaves::where('start_date', '<=', $date_current)->where('end_date', '>=', $date_current)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_current'] =  $annual4 + $sickness4 + $lateness4 +$other4;

        $date_plus_one = Carbon::parse('Now +1 days')->format('Y-m-d');
        $annual5 =  Staffleaves::where('start_date', '<=', $date_plus_one)->where('end_date', '>=', $date_plus_one)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness5 = Staffleaves::where('start_date', '<=', $date_plus_one)->where('end_date', '>=', $date_plus_one)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness5 =  Staffleaves::where('start_date', '=', $date_plus_one)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other5 =  Staffleaves::where('start_date', '<=', $date_plus_one)->where('end_date', '>=', $date_plus_one)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_plus_one'] =  $annual5 +$sickness5 + $lateness5 + $lateness5 + $other5;

        $date_plus_two = Carbon::parse('Now +2 days')->format('Y-m-d');
        $annual6 =  Staffleaves::where('start_date', '<=', $date_plus_two)->where('end_date', '>=', $date_plus_one)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness6 = Staffleaves::where('start_date', '<=', $date_plus_two)->where('end_date', '>=', $date_plus_two)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness6 =  Staffleaves::where('start_date', '=', $date_plus_two)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other6 =  Staffleaves::where('start_date', '<=', $date_plus_two)->where('end_date', '>=', $date_plus_two)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_plus_two'] =  $annual6 + $sickness6 + $lateness6 + $other6;

        $date_plus_three = Carbon::parse('Now +3 days')->format('Y-m-d');
        $annual7 =  Staffleaves::where('start_date', '<=', $date_plus_three)->where('end_date', '>=', $date_plus_three)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $sickness7 = Staffleaves::where('start_date', '<=', $date_plus_three)->where('end_date', '>=', $date_plus_three)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $lateness7 =  Staffleaves::where('start_date', '=', $date_plus_three)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $other7 =  Staffleaves::where('start_date', '<=', $date_plus_three)->where('end_date', '>=', $date_plus_three)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->where('home_id',  $home_id)->count();
        $leave['total_leave_plus_three'] =  $annual7 + $sickness7 + $lateness7 + $other7;

        
        return view('rotaStaff.StaffDashboard', ['sidebar' => 'dashborad'], $leave);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $current_month =  Carbon::now()->format('m');
        $current_year = Carbon::now()->format('Y');

        $previous_month_data = Carbon::now()->endOfMonth()->subMonth()->toDateString();
        $priormonth = date ('m', strtotime ( '-1 month' , strtotime ( $previous_month_data )));
        $prioryear = date('Y', strtotime($previous_month_data));

        $rota = Rota::where('deleted_status', 1)->get(); 
        $arr = array();
        $active_publish_rota= 0;
        $active_unpublish_rota= 0;
        $old_publish_rota = 0;
        $old_unpublish_rota = 0;
        $old = array();
        foreach($rota as $rotas){
            $convert_date = strtotime($rotas->created_at);
            $month = date('m',$convert_date);
            $year = date('Y',$convert_date);
            if($current_year == $year){
                if( $current_month == $month){
                    $arr[] = $rotas;
                    if($rotas->status === 1){
                        $active_publish_rota++;
                    } if($rotas->status === 0) {
                        $active_unpublish_rota++;
                    }
                }
            }
            if($prioryear == $year){
                if( $priormonth == $month){
                    $old[] = $rotas;
                    if($rotas->status === 1){
                        $old_publish_rota++;
                    } if($rotas->status === 0) {
                        $old_unpublish_rota++;
                    }
                }
            }
           
        }         
        $data['active_rota'] =  $arr;
        $data['old_rota'] =  $old; 
        $data['sidebar'] = 'rota';
        $data['active_publish_rota_count'] = $active_publish_rota;
        $data['active_unpublish_rota_count'] = $active_unpublish_rota;
        $data['old_publish_rota_count'] = $old_publish_rota;
        $data['old_unpublish_rota_count'] = $old_unpublish_rota;
      
        return view('rotaStaff.rotaView', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $rota_name = $request->rota_name;
        // Rota::where('rota_name', $request->)->get();
        $date = $request->start_date;
        $date = strtotime($date);
        $last_date = $request->rotaPeriodLength-1;
        $date = strtotime("+".(int)$last_date." day", $date);
        $date = date('Y-m-d', $date);
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $rota_data = array(
            'home_id' => $home_id,
            'rota_name' => $request->rota_name,
            'rota_duration' => $request->rotaPeriodLength,
            'rota_start_date' => $request->start_date,
            'rota_end_date' => $date,
            'rota_view' =>2,
            'status' => 0,
            'deleted_status' => 1,                  //deleted record status 1 for active 0 for deleted
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
        );
        $rota = Rota::insert($rota_data);
        return redirect('/rota-planner');  
    }

    public function get_all_users_edit(){
      $home_ids = Auth::user()->home_id;
      $ex_home_ids = explode(',', $home_ids);
      $home_id=$ex_home_ids[0];
      $data = ServiceUser::where('home_id', $home_id)->orderBy('id', 'DESC')->get();
      echo json_encode($data);
    }

    public function rota_calender_view(){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $data['user'] = ServiceUser::where('home_id', $home_id)->get();
        $data['sidebar'] = 'rota';
        $data['rota'] =   Rota::where('deleted_status', 1)->orderBy('id','DESC')->where('home_id', $home_id)->take(1)->get();
        $rota =   Rota::where('deleted_status', 1)->orderBy('id','DESC')->where('home_id', $home_id)->take(1)->get();
        foreach($rota as $rotaView){
           $rota_view_id =  $rotaView->rota_view;
        }
      
        if($rota_view_id === 1){
            return view('rotaStaff.rota_table', $data);
        }
        if($rota_view_id === 2){
            return view('rotaStaff.rota_timeline', $data);
        }
        if($rota_view_id === 3){
            echo "3";
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete_shift_data(Request $request)
    {
        $data = RotaShift::where('id', $request->delete_shift_id)
          ->update([
              'status' => 0
              ]);
          echo json_encode($data);
    }

    public function get_all_users(Request $request){
      $home_ids = Auth::user()->home_id;
      $ex_home_ids = explode(',', $home_ids);
      $home_id=$ex_home_ids[0];
      $user = ServiceUser::where('home_id', $home_id)->orderBy('name', 'DESC')->where('is_deleted', 0)->get();
      // dd($user);
      $leave = array();
      $complete_hours = array();

      $now = Carbon::now()->format('Y-m-d');
      foreach($user as $users){
        $leave[] =  Staffleaves::where('user_id', $users->id)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('leave_status', 1)->where('is_deleted', 1)->get();

        $selected = RotaAssignEmployee::where('rota_id', $request->rota_id)->where('emp_id', $users->id)->where('status', 1)->get();
        $time_total = 0;
        foreach($selected as $selected_user){
          $time = Carbon::parse($selected_user['total_hours'])->format('H');
          $time_total = $time_total + (int)$time;
        }
        $total_users = array();
        if($time_total > 40 ){

        }else {
          $total_users['id'] =  $users->id;
          $total_users['name'] =  $users->name;
          array_push($complete_hours, $total_users);
        }  
      }
      $data['users'] = $complete_hours;
      $data['leave'] =$leave;
      echo json_encode($data);
    }

    public function assign_rota_users(Request $request){
        $rota = $request->rota_id;
        if(isset($rota)){
            $get_rota =  $request->rota_id;
        } else {
            $get_rota =  $request->edit_rota_id;
        }

        if(!$request->break_time){
          $break = 0;
        } else {
          $break = $request->break_time;
        }
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        
        $shift_data = array(
            'rota_id' =>  $get_rota,
            'home_id' => $home_id,
            'shift_time' => "7",
            'rota_day_date' => Carbon::parse($request->rota_shift_day_date)->format('Y-m-d'),
            'shift_start_time' => $request->start_date,
            'shift_end_time' => $request->end_date,
            'break' => $break,
            'description' => $request->shift_notes,
            'status' =>1,
            'shift_color' => 'black',
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
        );
        
        $rota_shift = RotaShift::insert($shift_data);
        $latestShiftId = RotaShift::latest()->first();

        $start = new Carbon($request->start_date); 
        $end = new Carbon($request->end_date); 

        $shift_hours = $start->diff($end)->format('%H:%I:%S');;

        for ($i=0; $i < count($request->user_ids); $i++) { 
            $assignedUsersRota = array(
                'rota_id' => $get_rota,
                'home_id' => $home_id,
                'shift_id' => $latestShiftId->id,
                'emp_id' => $request->user_ids[$i],
                'total_hours' => $shift_hours,
                'status' =>1,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")
            );
            $rota_shift = RotaAssignEmployee::insert($assignedUsersRota);
        }

        $data['rotaShift'] = RotaShift::where('id',$latestShiftId->id)->where('status', 1)->get();

        $list_emp = RotaAssignEmployee::where('rota_id', $get_rota)->where('shift_id', $latestShiftId->id)->where('status', 1)->get();
        $userdata = array();
        
        foreach($list_emp as $emp_ids){
            $userdata[] = ServiceUser::where('id', $emp_ids->emp_id)->get();
        }
        $data['user_name'] = $userdata;
        echo json_encode($data);
    }
    function update_rota_name(Request $request){
        $data = Rota::where('id', $request->rota_id)
        ->update([
            'rota_name' => $request->rota_name
            ]);
        echo json_encode($data);
    }

    function publish_rota_employee(Request $request){
        $data = Rota::where('id', $request->publish_rota_id)
        ->update([
            'status' => 1
            ]);
        echo json_encode($data);
    }

    function unpublish_rota_employee(Request $request){
        $data = Rota::where('id', $request->unpublish_rota_id)
        ->update([
            'status' => 0
            ]);
        echo json_encode($data);
    }


    function calender_view(){
        //get all data for calender
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $leave = Staffleaves::where('is_deleted', 1 )->where('home_id', $home_id)->where('staff_leaves.leave_status', 1)->get();
        $recordArray = array();
        foreach($leave as $value){
            $leave_name = LeaveType::where('id', $value->leave_type)->pluck('leave_name'); 
            $leave_color = LeaveType::where('id', $value->leave_type)->pluck('color'); 
            $user_name  =  ServiceUser::where('id', $value->user_id)->pluck('name');
            $arr['title'] =  $user_name;
            $arr['color'] =  $leave_color[0];
            $arr['start'] = $value->start_date;
            $arr['end'] = $value->end_date;
            array_push($recordArray,$arr);
        }
        $data['pending_leave'] = DB::table('staff_leaves')
                            ->join('service_user', 'staff_leaves.user_id', '=', 'service_user.id')
                            ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
                            ->select('leave_type.leave_name','leave_type.color','leave_type.id as leavetype_id','service_user.name','service_user.id as user_id','staff_leaves.start_date', 'staff_leaves.end_date','staff_leaves.id as staffleave_id', 'staff_leaves.days', 'staff_leaves.notes')
                            ->where('staff_leaves.is_deleted', 1 )
                            ->where('staff_leaves.leave_status', 0)
                            ->where('staff_leaves.home_id', $home_id)
                            ->get();

                            // dd($data['pending_leave']);

        $data['count'] = DB::table('staff_leaves')->where('staff_leaves.is_deleted', 1 )->where('home_id', $home_id)->where('staff_leaves.leave_status', 0)->count();
        $data['calander']=json_encode($recordArray);

        // dd($data);
        return view('rotaStaff.calender', ['sidebar' => 'calender'], $data);
    }

    function annual_leave_view($id){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $data['leave'] = $id;
        $data['sidebar'] = '';
        $data['leavetype'] = LeaveType::where('status', 1)->get();
        $data['users'] = ServiceUser::where('home_id', $home_id)->where('is_deleted',0)->get();
        // dd($data);
        return view('rotaStaff.add_leave', $data);
    }

    function get_all_users_search(Request $request){
      // $data['users'] = User::where('home_id', 1)->Where('name', 'like', '%' . $request->search_data . '%')->orderBy('name', 'DESC')->where('is_deleted', 0)->get();
      $home_ids = Auth::user()->home_id;
      $ex_home_ids = explode(',', $home_ids);
      $home_id=$ex_home_ids[0];
      $user = User::where('home_id', $home_id)->Where('name', 'like', '%' . $request->search_data . '%')->orderBy('id', 'DESC')->where('is_deleted', 0)->get();
      $leave = array();
      $complete_hours = array();
      
      $now = Carbon::now()->format('Y-m-d');
      foreach($user as $users){
        $leave[] =  Staffleaves::where('user_id', $users->id)->where('start_date', '<=', $now)->where('end_date', '>=', $now)->where('leave_status', 1)->where('is_deleted', 1)->get();

        $selected = RotaAssignEmployee::where('rota_id', $request->rota_id)->where('emp_id', $users->id)->where('status', 1)->get();
        $time_total = 0;
        foreach($selected as $selected_user){
          $time = Carbon::parse($selected_user['total_hours'])->format('H');
          $time_total = $time_total + (int)$time;
        }
        $total_users = array();
        if($time_total > 40 ){

        }else {
          $total_users['id'] =  $users->id;
          $total_users['name'] =  $users->name;
         
          array_push($complete_hours,$total_users);
        }
          
      }
      $data['users'] = $complete_hours;
      $data['leave'] =$leave;
      echo json_encode($data);
    }


    function delete_rota_employee(Request $request){
        $data = Rota::where('id', $request->id)
        ->update([
            'deleted_status' => 0
            ]);
        echo json_encode($data);
    }


    function edit_rota($id){
        $rota = Rota::where('id', $id)->get();
        $data['sidebar'] = 'rota';
        $latestUser = Rota::latest()->first();
        foreach($rota as $rota_data){
           $view = $rota_data->rota_view;
        }


        $data['pass_rota_id'] = $id; 
        $data['rota'] = Rota::where('id', $id)->get();
        if($view == 1){
            return view('rotaStaff.rota_table', $data);
        }
        if($view == 2){
            return view('rotaStaff.edit_rota_timeline', $data);
        }
        if($view == 3){
            echo "3";
        }
    }

    function publish_unpublish_rota(Request $request){

      
        if($request->rota_status == 1)
        {
            $data = Rota::where('id', $request->rota_id)
            ->update([
                'status' => 0
                ]);
            echo "unpublished";
        }   
        if($request->rota_status == 0 ){
            $data = Rota::where('id', $request->rota_id)
            ->update([
                'status' => 1
                ]);
                echo "published";
        }
    }

    function add_leave(Request $request){
      
        if($request->ongoingLeave == "yes"){
            $ongoingLeave = 1;
        }else {
            $ongoingLeave = 0;
        }

        if(empty($request->start_date)){
            $date = $request->late_date;
            $request->end_date = null;
            $date = $request->late_date;
        } else {
            $date = $request->start_date;
        }
        // dd($date);
        // $data = $request->validate([
        //     'type' => 'required',
        //     'title' => 'required',
        //     'description' => 'required',
        //     'base_capacity' => 'required',
        //     'max_occupancy' => 'required',
        // ]);
        if(empty($request->late_time)){
            $late_time = null;
        }else{
            $late_time = $request->late_time;
        }
        if(empty($request->missed_days)){
            $missed_working_days = null;
        }else{
            $missed_working_days = $request->missed_days;
        }

        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $add_leave = array(
            'home_id' => $home_id,
            'user_id' => (int)$request->employee_list,
            'leave_type' => (int)$request->leave_type,
            'ongoing_absence' => $ongoingLeave,
            'start_date' => $date,
            'start_date_full_half' => (int)$request->start_date_full_half,
            'end_date' => $request->end_date,
            'end_date_full_half' => (int)$request->end_date_full_half,
            'late_by' => $late_time,
            'notes' => $request->notes,
            'days' => $missed_working_days,
            'leave_status' => 0,
            'is_deleted' => 1,
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")  
        );
        $data = Staffleaves::insert($add_leave);
        return redirect('/pending-request');
       
    }

    function leave_pending(){
        $data['last_leave'] = Staffleaves::latest()->first();
        $last_leave = Staffleaves::latest()->first();
        $user = ServiceUser::where('id', $last_leave->user_id)->get();
        foreach($user as $user_data){
            $user_name = $user_data->name;
        }
        $data['username'] = $user_name;
        $data['sidebar'] = 'action';
        return view('rotaStaff.leave_pending', $data);
    }

    function date_validation_for_user(Request $request){
        $request->start_date_input;
        $staff_leaves = Staffleaves::where('is_deleted', 1)->where('user_id', $request->start_id)->get();
        $arr = array();
        foreach($staff_leaves as $staff_leave ){
            $period = \Carbon\CarbonPeriod::create($staff_leave->start_date, $staff_leave->end_date);
            foreach ($period as $date) {
                // echo $date->format('Y-m-d');
                if($request->start_date_input == $date->format('Y-m-d')){
                    $arr[] =  \Carbon\Carbon::parse($staff_leave->start_date)->format('D, jS M');
                    $arr[] =  \Carbon\Carbon::parse($staff_leave->end_date)->format('D, jS M');
                }
            }
            $dates = $period->toArray(); 
        }
        echo json_encode($arr);
    }

    // function get_all_leave(){
    //     $data['leaves'] = Staffleaves::where('user_id', 15)->get();
    //     echo json_encode($data);   
    // }

    function employee_view(){
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $data['sidebar'] = 'employee';
        $data['user'] = User::where('home_id', $home_id)->get();
        return view('rotaStaff.employee', $data);
    }

    function get_rota_employee(Request $request){
      $home_ids = Auth::user()->home_id;
      $ex_home_ids = explode(',', $home_ids);
      $home_id=$ex_home_ids[0];
      $rotaID=$request->id;
      $employeeArray = array();
      $recordArray = array();
      //get employee id
      $employee = RotaAssignEmployee::select('emp_id')->where('rota_id' ,$request->id)->where('status', 1)->get();
      foreach($employee as $empValue){
        if(!in_array($empValue->emp_id,$employeeArray)){
          array_push($employeeArray,$empValue->emp_id);
        }
      }
      
      //get employee detail
      foreach($employeeArray as $usersValue){
        $rota['name'] = User::where('home_id',$home_id)->where('id', $usersValue)->where('is_deleted', 0)->pluck('name');
        $rota['user_id'] = $usersValue;
        $rota['rotaId'] = $request->id;
        //shift
        $getshift=RotaAssignEmployee::select('shift_id')->where('rota_id' ,$request->id)->where('emp_id', $usersValue)->get();
       // $rotaShifts = RotaShift::select('rota_day_date')->where('rota_id', $request->id)->get();
        $rota['rota_day_date'] =  RotaShift::select('rota_day_date')->whereIn('id', $getshift)->get();
        $rota['shift_start_time'] = RotaShift::select('shift_start_time')->whereIn('id', $getshift)->get();
        $rota['shift_end_time'] = RotaShift::select('shift_end_time')->whereIn('id', $getshift)->get();
        //$rota['total_hours'] = RotaShift::select('total_hours')->where('rota_id', $request->id)->get();
        $rota['break'] = RotaShift::select('break')->whereIn('id', $getshift)->get();

        // $rota['rota_start_date'] = $rotaData->rota_start_date;
        // $rota['rota_end_date'] = $rotaData->rota_end_date;
        // $date = RotaShift::select('rota_day_date')->where('rota_id', $request->id)->get();
        // $rota['date'] = $date;
        // $gettotalbreakhour=RotaAssignEmployee::select('shift_id')->where('rota_id' ,$request->id)->where('emp_id', $usersValue)->get();
        // //dd($gettotalbreakhour);
        // $rota['totalBreak'] = RotaShift::whereIn('id', $gettotalbreakhour)->sum('break');
        array_push($recordArray,$rota);
      }
      //  dd($recordArray);

      echo json_encode($recordArray); 


      // $recordArray = array();
      // $rota = array();
      // $check_user = array();
      // $userArray=array();
      // $rota_data = Rota::where('id', $request->id)->where('deleted_status', 1)->get();
      
      // foreach($rota_data as $rotaData){
      //   $rotaShift = RotaShift::where('rota_id' ,$request->id)->where('status', 1)->get();
       
      //     foreach($rotaShift as $rotaShifts){
      //       $user = RotaAssignEmployee::where('rota_id' ,$request->id)->where('shift_id', $rotaShifts->id)->where('status', 1)->get();
      //       //dd($user);
          
      //       foreach($user as $users){
      //         if(!in_array($users->emp_id, $userArray)){
      //           array_push($userArray, $users->emp_id);                
      //         }
      //       }
      //       //dd($userArray);
      //       foreach($userArray as $usersValue){
      //         $rota['name'] = User::where('home_id',1)->where('id', $usersValue)->where('is_deleted', 0)->pluck('name');
      //           $rota['user_id'] = $usersValue;
      //           $rota['rotaId'] = $request->id;
      //           $rota['rota_day_date'] =  $rotaShifts->rota_day_date;
      //           $rota['shift_start_time'] = $rotaShifts->shift_start_time;
      //           $rota['shift_end_time'] = $rotaShifts->shift_end_time;
      //           $rota['total_hours'] = $rotaShifts->total_hours;
      //           $rota['break'] = $rotaShifts->break;
      //           $rota['rota_start_date'] = $rotaData->rota_start_date;
      //           $rota['rota_end_date'] = $rotaData->rota_end_date;
      //           $date = RotaShift::select('rota_day_date')->where('rota_id', $request->id)->get();
      //           $rota['date'] = $date;
      //           $gettotalbreakhour=RotaAssignEmployee::select('shift_id')->where('rota_id' ,$request->id)->where('emp_id', $usersValue)->get();
      //           //dd($gettotalbreakhour);
      //           $rota['totalBreak'] = RotaShift::whereIn('id', $gettotalbreakhour)->sum('break');

      //           array_push($recordArray, $rota);
      //       }


      //       // foreach($user as $users){
      //       //     $rota['name'] = User::where('home_id',1)->where('id', $users->emp_id)->where('is_deleted', 0)->pluck('name');
      //       //     $rota['user_id'] = $users->emp_id;
      //       //     $rota['rota_day_date'] =  $rotaShifts->rota_day_date;
      //       //     $rota['shift_start_time'] = $rotaShifts->shift_start_time;
      //       //     $rota['shift_end_time'] = $rotaShifts->shift_end_time;
      //       //     $rota['total_hours'] = $rotaShifts->total_hours;
      //       //     $rota['break'] = $rotaShifts->break;
      //       //     $rota['rota_start_date'] = $rotaData->rota_start_date;
      //       //     $rota['rota_end_date'] = $rotaData->rota_end_date;
      //       //     // $date = RotaShift::select('rota_day_date')->where('rota_id', $request->id)->get();
      //       //     // $rota['date'] = $date;
      //       //     // $rota['totalBreak'] = RotaShift::where('rota_id', $request->id)->sum('break');
      //       //     // $rota['totalhour'] = RotaShift::where('rota_id', $request->id)->sum('break');

      //       //     array_push($recordArray, $rota);
      //       // } 
      //    }  
      // }
      
      

    //   $newRecord = array();
    //   $rotaEmp = array();
    //  for ($i=0; $i < count($recordArray); $i++) { 

    //     if (in_array($recordArray[$i]['user_id'], $check_user)) {

    //      for ($j=0; $j < count($newRecord); $j++) { 
    //       $newRecord[$j]['break'] = $newRecord[$j]['break'] + $recordArray[$i]['break'];
    //       $newRecord[$j]['total_hours'] = $newRecord[$j]['total_hours'] + $recordArray[$i]['total_hours'];
    //       $newRecord[$j]['rota_day_date'] = array($newRecord[$j]['total_hours'], $recordArray[$i]['rota_day_date']);
    //      }

          
    //     } else {
        
    //       $check_user[] = $recordArray[$i]['user_id'];
    //       $rotaEmp['name'] =$recordArray[$i]['name'];
    //       $rotaEmp['user_id'] = $recordArray[$i]['user_id'];
    //       $rotaEmp['rota_day_date'] = $recordArray[$i]['rota_day_date'];
    //       $rotaEmp['shift_start_time'] = $recordArray[$i]['shift_start_time'];
    //       $rotaEmp['shift_end_time'] = $recordArray[$i]['shift_end_time'];
    //       $rotaEmp['total_hours'] = $recordArray[$i]['total_hours'];
    //       $rotaEmp['break'] = $recordArray[$i]['break'];
    //       $rotaEmp['rota_start_date'] = $recordArray[$i]['rota_start_date'];
    //       $rotaEmp['rota_end_date'] = $recordArray[$i]['rota_end_date'];
    //       array_push($newRecord, $rotaEmp);
    //     }

       
    //  }
    
      // dd($check_user);
       
    }

    function get_all_shift(Request $request){
        $data = Rota::where('id', $request->id)->pluck('rota_start_date');
        $data = \Carbon\Carbon::parse($data[0])->format('d/m/Y');
        echo json_encode($data);
    }

    function edit_shift_data_get(Request $request ){
         
        // $rota = DB::table('rota_shift')
        // ->join('rota_assign_employees', 'rota_assign_employees.shift_id', '=', 'rota_shift.id')
        // ->select('rota_shift.id','rota_assign_employees.id', 'rota_shift.rota_day_date', 'rota_shift.shift_start_time', 'rota_shift.shift_end_time', 'rota_shift.break','rota_shift.description')
        // ->where('rota_shift.rota_id', $request->rota_id)
        // ->where('rota_assign_employees.emp_id', 'rota_assign_employees.user_id')
        // ->get();

        $rota = DB::table('rota_assign_employees')
        ->join('rota_shift', 'rota_assign_employees.shift_id', '=', 'rota_shift.id')
        ->select('rota_shift.id as rota_shift_id','rota_assign_employees.id as assigned_id', 'rota_shift.rota_day_date', 'rota_shift.shift_start_time', 'rota_shift.shift_end_time', 'rota_shift.break','rota_shift.description')
        ->where('rota_assign_employees.rota_id', $request->rota_id)
        ->where('rota_assign_employees.emp_id', $request->user_id)
        ->get();

        echo json_encode($rota); 
    }

    function update_shift_data(Request $request){
       
        $edit_rota_id = $request->edit_rota_id;
        $edit_shift_id = $request->edit_shift_id;
        $update_user_id = $request->update_user_id;
        $rotashift = array(
          'rota_day_date' => $request->updtate_date_of_shift,
          'shift_start_time' => $request->update_shift_start_time,
          'shift_end_time' => $request->update_shift_end_time,
          'break' => $request->update_break,
          'description' => $request->description
        );

        RotaShift::where('id', $request->rota_shift_id)
        ->update($rotashift);

        $data = RotaAssignEmployee::where('id', $request->assigned_user_id)
        ->update(['emp_id' => $request->update_user_id]);

        echo json_encode($data);
    }

    function approve_leave(Request $request){
        $result = Staffleaves::where('id',$request->id)->update(['leave_status'=> 1]);
        echo json_encode($result);
    }

    function get_leave_record_for_1_week(Request $request){
     
    	if(Auth::check()){
     
        $date = carbon::parse($request->date)->format('Y-m-d');

        $data['annual'] =  Staffleaves::where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('leave_type', 1)->where('is_deleted', 1)->where('leave_status', 1)->count();
        $data['sickness'] = Staffleaves::where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('leave_type', 2)->where('is_deleted', 1)->where('leave_status', 1)->count();
        $data['lateness'] =  Staffleaves::where('start_date', '=', $date)->where('leave_type', 3)->where('is_deleted', 1)->where('leave_status', 1)->count();
        $data['other'] =  Staffleaves::where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('leave_type', 4)->where('is_deleted', 1)->where('leave_status', 1)->count();

        echo json_encode($data);
      } else {
        echo json_encode("Record not found");
      }
    }

    function get_record_of_rota(Request $request){
        $data = Rota::where('deleted_status', 1)->orWhere('rota_name', 'like', '%' . $request->search_name . '%')->get();
        echo json_encode($data);
    }

    function get_all_rota_data(){
      $arr_active = array();
      $arr_inactive = array();
      $active_publish_rota= 0;
      $active_unpublish_rota= 0;
      $old_publish_rota = 0;
      $old_unpublish_rota = 0;
      $old_active = array();
      $old_inactive = array();
      $old = array();

      $current_date = Carbon::now()->format('Y-m-d');

      $new_rota = Rota::where('deleted_status', 1)->where('rota_start_date', '>=', $current_date)->where('rota_end_date', '>=', $current_date)->orderBy('rota_name', 'ASC')->get(); 
      foreach($new_rota as $new_rotas){
          if($new_rotas->status === 1){
              $count_emp_active = RotaAssignEmployee::where(['rota_id' => $new_rotas->id, 'status'=> 1])->count();
              $rota_start_end_time = RotaShift::where('rota_id', $new_rotas->id)->where('status', 1)->get();
              $hours = 0; $minutes = 0;
              foreach($rota_start_end_time as $rota_start_end_times){
                  $startTime = \Carbon\Carbon::parse($rota_start_end_times->shift_start_time);
                  $finishTime = \Carbon\Carbon::parse($rota_start_end_times->shift_end_time);
                  $duration = $finishTime->diff($startTime)->format('%H:%i:%s');
                  $totaltime = \Carbon\Carbon::parse($duration)->addMinutes($rota_start_end_times->break)->format('h:i:m');
                  $hours += \Carbon\Carbon::parse($totaltime)->format('h');
                  $minutes += \Carbon\Carbon::parse($totaltime)->format('i');
              }
              
              $time_hour = date('G', mktime($hours, $minutes));
              $time_min = date('i', mktime($hours, $minutes));
              $total_hours_min_one =   $time_hour ." hrs ". $time_min. " mins";

              $arr_active[] = "<div  class='parent_div my-2'>
              <div class='d-flex justify-content-between'>
                <div class='d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota'>
                  <div> ".Carbon::parse($new_rotas->rota_start_date)->format('D')." </div>
                  <div class=''>".Carbon::parse($new_rotas->rota_start_date)->format('j M')."</div>
                </div>
                <div class='col-md-10 px-2'>
                  <div class='d-flex justify-content-between align-items-center'>
                    <div>
                      <a href=". url('/edit_rota',$new_rotas->id )." class='rota_shift_employee_name'> ".$new_rotas->rota_name."</a>
                    </div>
                    <div class='dropdown'>
                      <button class='my-2 d-flex justify-content-center align-items-center three_dot_btn' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        <span class='dropbtn'>
                          <svg width='32' class='dropbtn' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'>
                            <circle cx='16' class='dropbtn' cy='24' r='2'></circle>
                            <circle cx='16' class='dropbtn' cy='16' r='2'></circle>
                            <circle cx='16' class='dropbtn' cy='8' r='2'></circle>
                          </svg>
                        </span>
                      </button>
                      <div class='dropdown-menu dropdown-content'>
                        <ul>
                            <li>
                            <span class='edit-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                            </span>
                              <a href=".url('/edit_rota', $new_rotas->id ).">Edit</a>
                            </li>
                            <li>  
                            <span class='i-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M16 30C8.275 30 2 23.725 2 16C2 8.275 8.275 2 16 2C23.725 2 30 8.275 30 16C30 23.725 23.725 30 16 30ZM16 4C9.387 4 4 9.387 4 16C4 22.613 9.387 28 16 28C22.613 28 28 22.613 28 16C28 9.387 22.613 4 16 4Z'></path><path d='M19 22H17V13C17 12.45 16.55 12 16 12H13C12.45 12 12 12.45 12 13C12 13.55 12.45 14 13 14H15V22H13C12.45 22 12 22.45 12 23C12 23.55 12.45 24 13 24H19C19.55 24 20 23.55 20 23C20 22.45 19.55 22 19 22Z'></path><path d='M17 9C17 9.552 16.552 10 16 10C15.448 10 15 9.552 15 9C15 8.448 15.448 8 16 8C16.552 8 17 8.448 17 9Z'></path></svg>
                            </span>
                              <a onclick='RotaView($new_rotas->id,`$new_rotas->rota_name`)'>View</a>
                            </li>
                            <li>
                            <span class='edit-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                            </span>
                              <a onclick='renamedata($new_rotas->id, `$new_rotas->rota_name`)'>Rename</a> 
                            </li>
                            <li>
                            <span class='tick-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M17 28H16C9.383 28 4 22.617 4 16C4 12.508 5.467 9.265 8 7.007V9C8 9.552 8.448 10 9 10C9.552 10 10 9.552 10 9V5C10 4.448 9.552 4 9 4H5C4.448 4 4 4.448 4 5C4 5.552 4.448 6 5 6H6.152C3.515 8.602 2 12.173 2 16C2 23.72 8.28 30 16 30H17C17.552 30 18 29.552 18 29C18 28.448 17.552 28 17 28Z'></path><path d='M27 26H25.848C28.485 23.398 30 19.827 30 16C30 8.28 23.72 2 16 2H15C14.448 2 14 2.448 14 3C14 3.552 14.448 4 15 4H16C22.617 4 28 9.383 28 16C28 19.492 26.533 22.735 24 24.993V23C24 22.448 23.552 22 23 22C22.448 22 22 22.448 22 23V27C22 27.552 22.448 28 23 28H27C27.552 28 28 27.552 28 27C28 26.448 27.552 26 27 26V26Z'></path><path d='M22 11C22 10.448 21.552 10 21 10C20.661 10 20.362 10.169 20.181 10.427L13.972 19.296L11.799 16.401C11.617 16.158 11.327 16 11 16C10.448 16 10 16.448 10 17C10 17.225 10.075 17.432 10.201 17.599L13.202 21.6C13.384 21.842 13.674 22 14.001 22C14.34 22 14.639 21.831 14.82 21.573L21.82 11.573C21.934 11.411 22.001 11.213 22.001 11H22Z'></path></svg>
                            </span>
                              <a onclick='unpublishRotaEmployee($new_rotas->id,`$new_rotas->rota_name`)'>Unpublish</a>
                            </li>
                            <li>
                            <span class='delete-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-error-700 group-hover/listitem:fill-error-800 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M27 8H22V7C22 4.243 19.757 2 17 2H15C12.243 2 10 4.243 10 7V8H5C4.448 8 4 8.448 4 9C4 9.552 4.448 10 5 10H6V25C6 27.757 8.243 30 11 30H21C23.757 30 26 27.757 26 25V10H27C27.552 10 28 9.552 28 9C28 8.448 27.552 8 27 8ZM12 7C12 5.346 13.346 4 15 4H17C18.654 4 20 5.346 20 7V8H12V7ZM24 25C24 26.654 22.654 28 21 28H11C9.346 28 8 26.654 8 25V10H24V25Z'></path><path d='M19 14C18.448 14 18 14.448 18 15V23C18 23.552 18.448 24 19 24C19.552 24 20 23.552 20 23V15C20 14.448 19.552 14 19 14Z'></path><path d='M13 14C12.448 14 12 14.448 12 15V23C12 23.552 12.448 24 13 24C13.552 24 14 23.552 14 23V15C14 14.448 13.552 14 13 14Z'></path></svg>
                          </span>
                              <a onclick='DeleteRotaEmployee($new_rotas->id, `$new_rotas->rota_name`)' class='delete_btn'>Delete</a>
                            </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div class='d-flex'>
                    <div class='pe-3'>Total: ".$total_hours_min_one." (Incl. breaks)</div>
                    <div class='order-1'>".$new_rotas->rota_duration." days<span class='px-2'></span><span>
                      ".$count_emp_active." employees</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>";
              $active_publish_rota++;
            } if($new_rotas->status === 0) {

                $rota_start_end_time = RotaShift::where('rota_id', $new_rotas->id)->where('status', 0)->get();
                $hours = 0; $minutes = 0;
                foreach($rota_start_end_time as $rota_start_end_times){
                    $startTime = \Carbon\Carbon::parse($rota_start_end_times->shift_start_time);
                    $finishTime = \Carbon\Carbon::parse($rota_start_end_times->shift_end_time);
                    $duration = $finishTime->diff($startTime)->format('%H:%i:%s');
                    $totaltime = \Carbon\Carbon::parse($duration)->addMinutes($rota_start_end_times->break)->format('h:i:m');
                    $hours += \Carbon\Carbon::parse($totaltime)->format('h');
                    $minutes += \Carbon\Carbon::parse($totaltime)->format('i');
                }
                
                $time_hour = date('G', mktime($hours, $minutes));
                $time_min = date('i', mktime($hours, $minutes));
                $total_hours_min_two =   $time_hour ." hrs ". $time_min. " mins";

                $count_emp_inactive = RotaAssignEmployee::where(['rota_id' => $new_rotas->id, 'status'=> 1])->count();

                $arr_inactive[] = "<div  class='parent_div my-2'>
                <div class='d-flex justify-content-between'>
                  <div class='d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota'>
                    <div> ".Carbon::parse($new_rotas->rota_start_date)->format('D')." </div>
                    <div class=''>".Carbon::parse($new_rotas->rota_start_date)->format('j M')."</div>
                  </div>
                  <div class='col-md-10 px-2'>
                    <div class='d-flex justify-content-between align-items-center'>
                      <div>
                        <a href=". url('/edit_rota',$new_rotas->id )." class='rota_shift_employee_name'> ".$new_rotas->rota_name."</a>
                      </div>
                      <div class='dropdown'>
                        <button class='my-2 d-flex justify-content-center align-items-center three_dot_btn' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                          <span class='dropbtn'>
                            <svg width='32' class='dropbtn' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'>
                              <circle cx='16' class='dropbtn' cy='24' r='2'></circle>
                              <circle cx='16' class='dropbtn' cy='16' r='2'></circle>
                              <circle cx='16' class='dropbtn' cy='8' r='2'></circle>
                            </svg>
                          </span>
                        </button>
                        <div class='dropdown-menu dropdown-content'>
                          <ul>
                              <li class='d-flex align-items-center'>
                                <span class='edit-icon dropdown_icon'>
                                <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                                </span>
                                <a href=".url('/edit_rota', $new_rotas->id ) . ">Edit</a>
                              </li>
                              <li class='d-flex align-items-center'>  
                                <span class='i-icon dropdown_icon'>
                                <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M16 30C8.275 30 2 23.725 2 16C2 8.275 8.275 2 16 2C23.725 2 30 8.275 30 16C30 23.725 23.725 30 16 30ZM16 4C9.387 4 4 9.387 4 16C4 22.613 9.387 28 16 28C22.613 28 28 22.613 28 16C28 9.387 22.613 4 16 4Z'></path><path d='M19 22H17V13C17 12.45 16.55 12 16 12H13C12.45 12 12 12.45 12 13C12 13.55 12.45 14 13 14H15V22H13C12.45 22 12 22.45 12 23C12 23.55 12.45 24 13 24H19C19.55 24 20 23.55 20 23C20 22.45 19.55 22 19 22Z'></path><path d='M17 9C17 9.552 16.552 10 16 10C15.448 10 15 9.552 15 9C15 8.448 15.448 8 16 8C16.552 8 17 8.448 17 9Z'></path></svg>
                                </span>
                                <a onclick='RotaView($new_rotas->id,`$new_rotas->rota_name`)'>View</a>
                              </li>
                              <li class='d-flex align-items-center'>
                                <span class='edit-icon dropdown_icon'>
                                <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                                </span>
                                <a onclick='renamedata($new_rotas->id, `$new_rotas->rota_name`)'>Rename</a> 
                              </li>
                              <li class='d-flex align-items-center'>
                                <span class='tick-icon dropdown_icon'>
                                <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M17 28H16C9.383 28 4 22.617 4 16C4 12.508 5.467 9.265 8 7.007V9C8 9.552 8.448 10 9 10C9.552 10 10 9.552 10 9V5C10 4.448 9.552 4 9 4H5C4.448 4 4 4.448 4 5C4 5.552 4.448 6 5 6H6.152C3.515 8.602 2 12.173 2 16C2 23.72 8.28 30 16 30H17C17.552 30 18 29.552 18 29C18 28.448 17.552 28 17 28Z'></path><path d='M27 26H25.848C28.485 23.398 30 19.827 30 16C30 8.28 23.72 2 16 2H15C14.448 2 14 2.448 14 3C14 3.552 14.448 4 15 4H16C22.617 4 28 9.383 28 16C28 19.492 26.533 22.735 24 24.993V23C24 22.448 23.552 22 23 22C22.448 22 22 22.448 22 23V27C22 27.552 22.448 28 23 28H27C27.552 28 28 27.552 28 27C28 26.448 27.552 26 27 26V26Z'></path><path d='M22 11C22 10.448 21.552 10 21 10C20.661 10 20.362 10.169 20.181 10.427L13.972 19.296L11.799 16.401C11.617 16.158 11.327 16 11 16C10.448 16 10 16.448 10 17C10 17.225 10.075 17.432 10.201 17.599L13.202 21.6C13.384 21.842 13.674 22 14.001 22C14.34 22 14.639 21.831 14.82 21.573L21.82 11.573C21.934 11.411 22.001 11.213 22.001 11H22Z'></path></svg>
                                </span>
                                <a onclick='publishRotaEmployee($new_rotas->id,`$new_rotas->rota_name`)'>Publish</a>
                              </li>
                              <li class='d-flex align-items-center'>
                                <span class='delete-icon dropdown_icon'>
                                  <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-error-700 group-hover/listitem:fill-error-800 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M27 8H22V7C22 4.243 19.757 2 17 2H15C12.243 2 10 4.243 10 7V8H5C4.448 8 4 8.448 4 9C4 9.552 4.448 10 5 10H6V25C6 27.757 8.243 30 11 30H21C23.757 30 26 27.757 26 25V10H27C27.552 10 28 9.552 28 9C28 8.448 27.552 8 27 8ZM12 7C12 5.346 13.346 4 15 4H17C18.654 4 20 5.346 20 7V8H12V7ZM24 25C24 26.654 22.654 28 21 28H11C9.346 28 8 26.654 8 25V10H24V25Z'></path><path d='M19 14C18.448 14 18 14.448 18 15V23C18 23.552 18.448 24 19 24C19.552 24 20 23.552 20 23V15C20 14.448 19.552 14 19 14Z'></path><path d='M13 14C12.448 14 12 14.448 12 15V23C12 23.552 12.448 24 13 24C13.552 24 14 23.552 14 23V15C14 14.448 13.552 14 13 14Z'></path></svg>
                                </span>
                                <a onclick='DeleteRotaEmployee($new_rotas->id,`$new_rotas->rota_name`)' class='delete_btn'>Delete</a>
                              </li>
                          </ul>
                        </div>
                      </div>
                    </div>
                    <div class='d-flex'>
                      <div class='pe-3'>Total: ".$total_hours_min_two."  (Incl. breaks)</div>
                      <div class='order-1'>".$new_rotas->rota_duration." days<span class='px-2'></span><span>
                        ".$count_emp_inactive." employees</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>";
                $active_unpublish_rota++;
            }
          
      }


      $old_rota = Rota::where('deleted_status', 1)->where('rota_end_date', '<', $current_date)->orderBy('rota_name', 'ASC')->get(); 
      foreach($old_rota as $old_rotas){
        if($old_rotas->status === 1){
          $count_emp_active = RotaAssignEmployee::where(['rota_id' => $old_rotas->id, 'status'=> 1])->count();
          $rota_start_end_time = RotaShift::where('rota_id', $old_rotas->id)->where('status', 1)->get();
          $hours = 0; $minutes = 0;
          foreach($rota_start_end_time as $rota_start_end_times){
              $startTime = \Carbon\Carbon::parse($rota_start_end_times->shift_start_time);
              $finishTime = \Carbon\Carbon::parse($rota_start_end_times->shift_end_time);
              $duration = $finishTime->diff($startTime)->format('%H:%i:%s');
              $totaltime = \Carbon\Carbon::parse($duration)->addMinutes($rota_start_end_times->break)->format('h:i:m');
              $hours += \Carbon\Carbon::parse($totaltime)->format('h');
              $minutes += \Carbon\Carbon::parse($totaltime)->format('i');
          }
          
          $time_hour = date('G', mktime($hours, $minutes));
          $time_min = date('i', mktime($hours, $minutes));
          $total_hours_min_one =   $time_hour ." hrs ". $time_min. " mins";

          $old_active[] = "<div  class='parent_div my-2'>
          <div class='d-flex justify-content-between'>
            <div class='d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota'>
              <div> ".Carbon::parse($old_rotas->rota_start_date)->format('D')." </div>
              <div class=''>".Carbon::parse($old_rotas->rota_start_date)->format('j M')."</div>
            </div>
            <div class='col-md-10 px-2'>
              <div class='d-flex justify-content-between align-items-center'>
                <div>
                  <a href=". url('/edit_rota',$old_rotas->id )." class='rota_shift_employee_name'> ".$old_rotas->rota_name."</a>
                </div>
                <div class='dropdown'>
                  <button class='my-2 d-flex justify-content-center align-items-center three_dot_btn' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                    <span class='dropbtn'>
                      <svg width='32' class='dropbtn' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'>
                        <circle cx='16' class='dropbtn' cy='24' r='2'></circle>
                        <circle cx='16' class='dropbtn' cy='16' r='2'></circle>
                        <circle cx='16' class='dropbtn' cy='8' r='2'></circle>
                      </svg>
                    </span>
                  </button>
                  <div class='dropdown-menu dropdown-content'>
                    <ul>
                        <li>
                          <span class='edit-icon dropdown_icon'>
                          <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                          </span>
                          <a href=".url('/edit_rota', $old_rotas->id ).">Edit</a>
                        </li>
                        <li>  
                        <span class='i-icon dropdown_icon'>
                        <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M16 30C8.275 30 2 23.725 2 16C2 8.275 8.275 2 16 2C23.725 2 30 8.275 30 16C30 23.725 23.725 30 16 30ZM16 4C9.387 4 4 9.387 4 16C4 22.613 9.387 28 16 28C22.613 28 28 22.613 28 16C28 9.387 22.613 4 16 4Z'></path><path d='M19 22H17V13C17 12.45 16.55 12 16 12H13C12.45 12 12 12.45 12 13C12 13.55 12.45 14 13 14H15V22H13C12.45 22 12 22.45 12 23C12 23.55 12.45 24 13 24H19C19.55 24 20 23.55 20 23C20 22.45 19.55 22 19 22Z'></path><path d='M17 9C17 9.552 16.552 10 16 10C15.448 10 15 9.552 15 9C15 8.448 15.448 8 16 8C16.552 8 17 8.448 17 9Z'></path></svg>
                        </span>
                          <a onclick='RotaView($old_rotas->id,`$old_rotas->rota_name`)'>View</a>
                        </li>
                        <li>
                          <span class='edit-icon dropdown_icon'>
                          <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                          </span>
                          <a onclick='renamedata($old_rotas->id, `$old_rotas->rota_name`)'>Rename</a> 
                        </li>
                        <li>
                        <span class='tick-icon dropdown_icon'>
                        <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M17 28H16C9.383 28 4 22.617 4 16C4 12.508 5.467 9.265 8 7.007V9C8 9.552 8.448 10 9 10C9.552 10 10 9.552 10 9V5C10 4.448 9.552 4 9 4H5C4.448 4 4 4.448 4 5C4 5.552 4.448 6 5 6H6.152C3.515 8.602 2 12.173 2 16C2 23.72 8.28 30 16 30H17C17.552 30 18 29.552 18 29C18 28.448 17.552 28 17 28Z'></path><path d='M27 26H25.848C28.485 23.398 30 19.827 30 16C30 8.28 23.72 2 16 2H15C14.448 2 14 2.448 14 3C14 3.552 14.448 4 15 4H16C22.617 4 28 9.383 28 16C28 19.492 26.533 22.735 24 24.993V23C24 22.448 23.552 22 23 22C22.448 22 22 22.448 22 23V27C22 27.552 22.448 28 23 28H27C27.552 28 28 27.552 28 27C28 26.448 27.552 26 27 26V26Z'></path><path d='M22 11C22 10.448 21.552 10 21 10C20.661 10 20.362 10.169 20.181 10.427L13.972 19.296L11.799 16.401C11.617 16.158 11.327 16 11 16C10.448 16 10 16.448 10 17C10 17.225 10.075 17.432 10.201 17.599L13.202 21.6C13.384 21.842 13.674 22 14.001 22C14.34 22 14.639 21.831 14.82 21.573L21.82 11.573C21.934 11.411 22.001 11.213 22.001 11H22Z'></path></svg>
                        </span>
                          <a onclick='unpublishRotaEmployee($old_rotas->id,`$old_rotas->rota_name`)'>Unpublish</a>
                        </li>
                        <li>
                            <span class='delete-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-error-700 group-hover/listitem:fill-error-800 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M27 8H22V7C22 4.243 19.757 2 17 2H15C12.243 2 10 4.243 10 7V8H5C4.448 8 4 8.448 4 9C4 9.552 4.448 10 5 10H6V25C6 27.757 8.243 30 11 30H21C23.757 30 26 27.757 26 25V10H27C27.552 10 28 9.552 28 9C28 8.448 27.552 8 27 8ZM12 7C12 5.346 13.346 4 15 4H17C18.654 4 20 5.346 20 7V8H12V7ZM24 25C24 26.654 22.654 28 21 28H11C9.346 28 8 26.654 8 25V10H24V25Z'></path><path d='M19 14C18.448 14 18 14.448 18 15V23C18 23.552 18.448 24 19 24C19.552 24 20 23.552 20 23V15C20 14.448 19.552 14 19 14Z'></path><path d='M13 14C12.448 14 12 14.448 12 15V23C12 23.552 12.448 24 13 24C13.552 24 14 23.552 14 23V15C14 14.448 13.552 14 13 14Z'></path></svg>
                          </span>
                          <a onclick='DeleteRotaEmployee($old_rotas->id, `$old_rotas->rota_name`)' class='delete_btn'>Delete</a>
                        </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class='d-flex'>
                <div class='pe-3'>Total: ".$total_hours_min_one." (Incl. breaks)</div>
                <div class='order-1'>".$old_rotas->rota_duration." days<span class='px-2'></span><span>
                  ".$count_emp_active." employees</span>
                </div>
              </div>
            </div>
          </div>
        </div>";
          $old_publish_rota++;
        } if($old_rotas->status === 0) {

            $rota_start_end_time = RotaShift::where('rota_id', $old_rotas->id)->where('status', 0)->get();
            $hours = 0; $minutes = 0;
            foreach($rota_start_end_time as $rota_start_end_times){
                $startTime = \Carbon\Carbon::parse($rota_start_end_times->shift_start_time);
                $finishTime = \Carbon\Carbon::parse($rota_start_end_times->shift_end_time);
                $duration = $finishTime->diff($startTime)->format('%H:%i:%s');
                $totaltime = \Carbon\Carbon::parse($duration)->addMinutes($rota_start_end_times->break)->format('h:i:m');
                $hours += \Carbon\Carbon::parse($totaltime)->format('h');
                $minutes += \Carbon\Carbon::parse($totaltime)->format('i');
            }
            
            $time_hour = date('G', mktime($hours, $minutes));
            $time_min = date('i', mktime($hours, $minutes));
            $total_hours_min_two =   $time_hour ." hrs ". $time_min. " mins";

            $count_emp_inactive = RotaAssignEmployee::where(['rota_id' => $old_rotas->id, 'status'=> 1])->count();

            $old_inactive[] = "<div  class='parent_div my-2'>
            <div class='d-flex justify-content-between'>
              <div class='d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota'>
                <div> ".Carbon::parse($old_rotas->rota_start_date)->format('D')." </div>
                <div class=''>".Carbon::parse($old_rotas->rota_start_date)->format('j M')."</div>
              </div>
              <div class='col-md-10 px-2'>
                <div class='d-flex justify-content-between align-items-center'>
                  <div>
                    <a href=". url('/edit_rota',$old_rotas->id )." class='rota_shift_employee_name'> ".$old_rotas->rota_name."</a>
                  </div>
                  <div class='dropdown'>
                    <button class='my-2 d-flex justify-content-center align-items-center three_dot_btn' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                      <span class='dropbtn'>
                        <svg width='32' class='dropbtn' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'>
                          <circle cx='16' class='dropbtn' cy='24' r='2'></circle>
                          <circle cx='16' class='dropbtn' cy='16' r='2'></circle>
                          <circle cx='16' class='dropbtn' cy='8' r='2'></circle>
                        </svg>
                      </span>
                    </button>
                    <div class='dropdown-menu dropdown-content'>
                      <ul>
                          <li>
                          <span class='edit-icon dropdown_icon'>
                          <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                          </span>
                            <a href=".url('/edit_rota', $old_rotas->id ) . ">Edit</a>
                          </li>
                          <li>  
                          <span class='i-icon dropdown_icon'>
                          <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M16 30C8.275 30 2 23.725 2 16C2 8.275 8.275 2 16 2C23.725 2 30 8.275 30 16C30 23.725 23.725 30 16 30ZM16 4C9.387 4 4 9.387 4 16C4 22.613 9.387 28 16 28C22.613 28 28 22.613 28 16C28 9.387 22.613 4 16 4Z'></path><path d='M19 22H17V13C17 12.45 16.55 12 16 12H13C12.45 12 12 12.45 12 13C12 13.55 12.45 14 13 14H15V22H13C12.45 22 12 22.45 12 23C12 23.55 12.45 24 13 24H19C19.55 24 20 23.55 20 23C20 22.45 19.55 22 19 22Z'></path><path d='M17 9C17 9.552 16.552 10 16 10C15.448 10 15 9.552 15 9C15 8.448 15.448 8 16 8C16.552 8 17 8.448 17 9Z'></path></svg>
                          </span>
                            <a onclick='RotaView($old_rotas->id,`$old_rotas->rota_name`)'>View</a>
                          </li>
                          <li>
                            <span class='edit-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M28 9.5C28 6.487 25.512 4 22.5 4C21.038 4 19.637 4.575 18.613 5.612L5.463 18.762C4.513 19.712 4 20.962 4 22.3V27C4 27.55 4.45 28 5 28H9.713C11.051 28 12.301 27.475 13.25 26.538L26.4 13.388C27.425 12.363 28 10.963 28 9.5ZM11.825 25.125C11.262 25.688 10.5 26 9.7 26H6V22.288C6 21.488 6.313 20.738 6.875 20.163L16.813 10.225L21.763 15.175L11.825 25.125ZM24.975 11.975L23.187 13.762L18.237 8.812L20.025 7.025C20.675 6.375 21.575 6 22.5 6C24.425 6 26 7.575 26 9.5C26 10.425 25.625 11.325 24.975 11.975Z'></path></svg>
                            </span>
                            <a onclick='renamedata($old_rotas->id, `$old_rotas->rota_name`)'>Rename</a> 
                          </li>
                          <li>
                          <span class='tick-icon dropdown_icon'>
                          <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-primary-400 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M17 28H16C9.383 28 4 22.617 4 16C4 12.508 5.467 9.265 8 7.007V9C8 9.552 8.448 10 9 10C9.552 10 10 9.552 10 9V5C10 4.448 9.552 4 9 4H5C4.448 4 4 4.448 4 5C4 5.552 4.448 6 5 6H6.152C3.515 8.602 2 12.173 2 16C2 23.72 8.28 30 16 30H17C17.552 30 18 29.552 18 29C18 28.448 17.552 28 17 28Z'></path><path d='M27 26H25.848C28.485 23.398 30 19.827 30 16C30 8.28 23.72 2 16 2H15C14.448 2 14 2.448 14 3C14 3.552 14.448 4 15 4H16C22.617 4 28 9.383 28 16C28 19.492 26.533 22.735 24 24.993V23C24 22.448 23.552 22 23 22C22.448 22 22 22.448 22 23V27C22 27.552 22.448 28 23 28H27C27.552 28 28 27.552 28 27C28 26.448 27.552 26 27 26V26Z'></path><path d='M22 11C22 10.448 21.552 10 21 10C20.661 10 20.362 10.169 20.181 10.427L13.972 19.296L11.799 16.401C11.617 16.158 11.327 16 11 16C10.448 16 10 16.448 10 17C10 17.225 10.075 17.432 10.201 17.599L13.202 21.6C13.384 21.842 13.674 22 14.001 22C14.34 22 14.639 21.831 14.82 21.573L21.82 11.573C21.934 11.411 22.001 11.213 22.001 11H22Z'></path></svg>
                          </span>
                            <a onclick='publishRotaEmployee($old_rotas->id,`$old_rotas->rota_name`)'>Publish</a>
                          </li>
                          <li>
                            <span class='delete-icon dropdown_icon'>
                            <svg width='28' height='32' viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg' class='fill-error-700 group-hover/listitem:fill-error-800 group-active/listitem:fill-white group-disabled/listitem:fill-link-disabled'><path d='M27 8H22V7C22 4.243 19.757 2 17 2H15C12.243 2 10 4.243 10 7V8H5C4.448 8 4 8.448 4 9C4 9.552 4.448 10 5 10H6V25C6 27.757 8.243 30 11 30H21C23.757 30 26 27.757 26 25V10H27C27.552 10 28 9.552 28 9C28 8.448 27.552 8 27 8ZM12 7C12 5.346 13.346 4 15 4H17C18.654 4 20 5.346 20 7V8H12V7ZM24 25C24 26.654 22.654 28 21 28H11C9.346 28 8 26.654 8 25V10H24V25Z'></path><path d='M19 14C18.448 14 18 14.448 18 15V23C18 23.552 18.448 24 19 24C19.552 24 20 23.552 20 23V15C20 14.448 19.552 14 19 14Z'></path><path d='M13 14C12.448 14 12 14.448 12 15V23C12 23.552 12.448 24 13 24C13.552 24 14 23.552 14 23V15C14 14.448 13.552 14 13 14Z'></path></svg>
                          </span>
                            <a onclick='DeleteRotaEmployee($old_rotas->id,`$old_rotas->rota_name`)' class='delete_btn'>Delete</a>
                          </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class='d-flex'>
                  <div class='pe-3'>Total: ".$total_hours_min_two."  (Incl. breaks)</div>
                  <div class='order-1'>".$old_rotas->rota_duration." days<span class='px-2'></span><span>
                    ".$count_emp_inactive." employees</span>
                  </div>
                </div>
              </div>
            </div>
          </div>";
            $old_unpublish_rota++;
        }
      }
    
    
      $data['new_active'] =  $arr_active;
      $data['new_inactive'] =  $arr_inactive;
      $data['old_active'] =  $old_active; 
      $data['old_inactive'] =  $old_inactive; 
      $data['sidebar'] = 'rota';
      $data['active_publish_rota_count'] = $active_publish_rota;
      $data['active_unpublish_rota_count'] = $active_unpublish_rota;
      $data['old_publish_rota_count'] = $old_publish_rota;
      $data['old_unpublish_rota_count'] = $old_unpublish_rota;

      echo json_encode($data);
  }

  public function pending_request_data(Request $request){
    $data['count'] = DB::table('staff_leaves')->where('staff_leaves.is_deleted', 1 )->where('staff_leaves.leave_status', 0)->count();
    if($request->type == 1){
      $data['pending_leave'] = DB::table('staff_leaves')
      ->join('service_user', 'staff_leaves.user_id', '=', 'service_user.id')
      ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
      ->select('leave_type.leave_name','leave_type.color','leave_type.id as leavetype_id','service_user.name','service_user.id as user_id','staff_leaves.start_date', 'staff_leaves.end_date','staff_leaves.id as staffleave_id', 'staff_leaves.days', 'staff_leaves.notes')
      ->orderBy('staff_leaves.id', 'DESC')
      ->where('staff_leaves.is_deleted', 1 )
      ->where('staff_leaves.leave_status', 0)
      ->get();
    } elseif ($request->type == 2) {
      $data['pending_leave'] = DB::table('staff_leaves')
      ->join('service_user', 'staff_leaves.user_id', '=', 'service_user.id')
      ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
      ->select('leave_type.leave_name','leave_type.color','leave_type.id as leavetype_id','service_user.name','service_user.id as user_id','staff_leaves.start_date', 'staff_leaves.end_date','staff_leaves.id as staffleave_id', 'staff_leaves.days', 'staff_leaves.notes')
      ->where('staff_leaves.is_deleted', 1 )
      ->where('staff_leaves.leave_status', 0)
      ->get();
    }
    echo json_encode($data);
  }

    public function privacy_policy(){
        return view('rotaStaff.privacy_policy');
    }

    public function recruitment_index(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.recruitment', $data);
    }

    public function jobs_index(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.jobs', $data);
    }

    public function create_jobs(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.create_job', $data);
    }

    public function permission_index(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.permissions', $data);
    }

    public function check_users_add_in_shift(Request $request){
      $data = RotaShift::where('rota_id', $request->id)->where('status', 1)->value('id');
      echo json_encode($data);
    }

    public function payroll(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.payroll', $data);
    }

    public function information_checker(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.information_checker', $data);
    }

    public function overtime(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.overtime', $data);
    }

    public function payroll_glossary(){
      $data['sidebar'] = 'dashborad';
      return view('rotaStaff.payroll_glossary', $data);
    }
    public function rota_management_dashboard(){
      
      return view('rotaStaff.rota_management_dashboard');
    }

}