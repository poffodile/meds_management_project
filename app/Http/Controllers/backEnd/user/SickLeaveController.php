<?php
namespace App\Http\Controllers\backEnd\user;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\User, App\StaffSickLeave, App\Home, App\SanctionStaffSickLeave, App\StaffRota, App\RotaShift, App\RotaShiftType,App\Staffleaves;
use DB; 
use Hash;

class SickLeaveController extends Controller
{
    public function index(Request $request, $user_id) {   
        //compare with su home_id
        $u_home_id = User::where('id',$user_id)->value('home_id');
        $home_id = Session::get('scitsAdminSession')->home_id;
        if($home_id == $u_home_id) {
        // echo $u_home_id; die;
            // $u_sick = StaffSickLeave::where('staff_member_id', $user_id)->where('is_deleted','0')->select('id','title', 'leave_date','staff_member_id');
            $u_sick = Staffleaves::where(['user_id'=> $user_id,'leave_type'=>2])->where('is_deleted','1')->select('id','home_id', 'user_id','leave_type','ongoing_absence','start_date','start_date_full_half','end_date','end_date_full_half','notes','days','leave_status');
            // echo "<pre>"; print_r($u_sick); die;
            
            $search = '';

            if(isset($request->limit))
            {
                $limit = $request->limit;
                Session::put('page_record_limit',$limit);
            } else{

                if(Session::has('page_record_limit')){
                    $limit = Session::get('page_record_limit');
                } else{
                    $limit = 25;
                }
            }

            if(isset($request->search))
            {
                $search = trim($request->search);
                $u_sick = $u_sick->where('notes','like','%'.$search.'%');             //search by date or title
            }

            $u_sick_leave = $u_sick->paginate(25);
        } else {
            return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
        }
        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'user-sick-leave';
        return view('backEnd.user.sickLeave.sick_leaves', compact('page','limit', 'user_id','u_sick_leave','search')); 
    }

    public function add(Request $request, $user_id) {   
        
        if($request->isMethod('post')) { 
            $data = $request->input();
            // echo "<pre>";print_r($data);die;
            //compare with su home_id
            $u_home_id = User::where('id',$user_id)->value('home_id');
            $home_id = Session::get('scitsAdminSession')->home_id;

            if($home_id == $u_home_id) {
                // $u_sick_leave                  =  new StaffSickLeave;
                // $u_sick_leave->title           =  $data['title'];
                // $u_sick_leave->staff_member_id =  $user_id;
                // $u_sick_leave->home_id         =  $home_id;
                // $u_sick_leave->leave_date      =  date('Y-m-d', strtotime($data['leave_date']));
                // $u_sick_leave->reason          =  $data['reason'];
                // $u_sick_leave->comments        =  $data['comment'];
                $u_sick_leave                  =  new Staffleaves;
                $u_sick_leave->home_id         =  $u_home_id;
                $u_sick_leave->user_id         =  $user_id;
                $u_sick_leave->leave_type      =  2;
                $u_sick_leave->ongoing_absence =  $data['ongoing_absence'];
                $u_sick_leave->start_date      =  date('Y-m-d', strtotime($data['start_date']));
                $u_sick_leave->start_date_full_half =  $data['start_date_full_half'];
                $u_sick_leave->end_date        =  date('Y-m-d', strtotime($data['end_date']));
                $u_sick_leave->end_date_full_half   =  $data['end_date_full_half'];
                $u_sick_leave->notes           =  $data['notes'];
                $u_sick_leave->days            =   $data['days'];
                $u_sick_leave->leave_status    =  0;
                $u_sick_leave->is_deleted      =  1;
                $u_sick_leave->created_at      =  date("Y-m-d H:i:s");
                $u_sick_leave->updated_at      =  date("Y-m-d H:i:s");

                if($u_sick_leave->save()) {
                        // return redirect('admin/service-users/care-history/'.$service_user_id)->with('success', 'New Care Timeline added successfully.');
                        return redirect('admin/user/sick-leaves/'.$user_id)->with('success', 'Sick Leave added successfully.');
                    }  else  {
                        return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                    }
            } else {
                return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
            }
        }
        $page = 'user-sick-leave';
        $u_details = User::where('id',$user_id)->first();
        return view('backEnd.user.sickLeave.sick_leave_form', compact('page', 'user_id','u_details'));
    }
            
    public function edit(Request $request, $u_sick_leave_id) {   

        // $u_sick_leave    =  StaffSickLeave::find($u_sick_leave_id);
        $u_sick_leave    =  Staffleaves::find($u_sick_leave_id);
        if(!empty($u_sick_leave)) {
            $user_id    = $u_sick_leave->user_id;

             //comparing u home_id
            $u_home_id = User::where('id',$user_id)->value('home_id');
            $home_id    = Session::get('scitsAdminSession')->home_id;
            if($home_id != $u_home_id) {
                return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
            }

            if($request->isMethod('post')) {   
                $data = $request->input();
                // echo "<pre>"; print_r($data); die;
                // $u_sick_leave->title           =  $data['title'];
                // $u_sick_leave->leave_date      =  date('Y-m-d', strtotime($data['leave_date']));
                // $u_sick_leave->reason          =  $data['reason'];
                // $u_sick_leave->comments        =  $data['comment'];
                $u_sick_leave->ongoing_absence =  $data['ongoing_absence'];
                $u_sick_leave->start_date      =  date('Y-m-d', strtotime($data['start_date']));
                $u_sick_leave->start_date_full_half =  $data['start_date_full_half'];
                $u_sick_leave->end_date        =  date('Y-m-d', strtotime($data['end_date']));
                $u_sick_leave->end_date_full_half   =  $data['end_date_full_half'];
                $u_sick_leave->notes           =  $data['notes'];
                $u_sick_leave->days            =   $data['days'];
                $u_sick_leave->updated_at      =  date("Y-m-d H:i:s");
        
               if($u_sick_leave->save()) {
                   return redirect('admin/user/sick-leaves/'.$user_id)->with('success','Sick Leave Updated Successfully.'); 
                } else {
                   return redirect()->back()->with('error','Sick Leave could not be Updated Successfully.'); 
                }  
            }
        } else {
                return redirect('admin/')->with('error','Sorry,Sick Leave does not exists');
        }

        // $u_sick_leave = StaffSickLeave::where('id', $u_sick_leave_id)->first();
        $u_sick_leave = Staffleaves::where('id', $u_sick_leave_id)->first();

        if(!empty($u_sick_leave)) {
            //compare with su home_id
            $u_home_id = User::where('id',$u_sick_leave->user_id)->value('home_id');
            $home_id    = Session::get('scitsAdminSession')->home_id;
            if($home_id != $u_home_id) { 
                return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
            } 
        } else {
            return redirect('admin/')->with('error','Sorry, Sick Leave does not exists');
        }

        $page = 'user-sick-leave';
        $u_details = User::where('id',$user_id)->first();
        return view('backEnd.user.sickLeave.sick_leave_form', compact('u_sick_leave','page','user_id','u_details'));
    }
        
    public function delete($u_sick_leave_id) {   

        if(!empty($u_sick_leave_id)) {
        //    $u_sick_leave =  StaffSickLeave::where('id', $u_sick_leave_id)->first();
           $u_sick_leave =  Staffleaves::where('id', $u_sick_leave_id)->first();
           
            if(!empty($u_sick_leave)) {
                $u_home_id = User::where('id',$u_sick_leave->user_id)->value('home_id');
                $home_id = Session::get('scitsAdminSession')->home_id;
                
                //compare with su home_id
                if($home_id != $u_home_id) {
                    return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
                }

                // StaffSickLeave::where('id', $u_sick_leave_id)->update(['is_deleted'=>'1']);
                Staffleaves::where('id', $u_sick_leave_id)->update(['is_deleted'=>'0']);
                return redirect()->back()->with('success','Sick Leave deleted Successfully.'); 
            } else  {
                return redirect('admin/')->with('error','Sorry,Sick Leave does not exists'); 
            }
        } else {
            return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
        }
    }
    //Sick Leave Sanction 
    public function sanction_leave(Request $request, $u_sick_leave_id){
        $data = $request->input();
        $sanction_sk_lv_info = SanctionStaffSickLeave::where('staff_sick_leave_id',1)
                                                    ->orderBy('id','desc')
                                                    ->first();
        $staff_rota='';
        $rota_shift='';
        if(!empty($sanction_sk_lv_info)){
            $staff_rota = StaffRota::where('user_id',$sanction_sk_lv_info->staff_user_id)
                                    ->where('home_id',$sanction_sk_lv_info->home_id)
                                    ->first();

            $rota_shift  = RotaShift::where('id',$staff_rota->shift_type_id)->first();
        }
        //$rota_shift_type = !empty($rota_shift_type) ? $rota_shift_type->toArray() : $rota_shift_type;
        // echo "<pre>"; print_r($rota_shift_type); 
        // echo "<pre>"; print_r($staff_rota->start_time); 
        // echo "<pre>"; print_r($staff_rota->end_time); die;        



        $selected_home_id       = Session::get('scitsAdminSession')->home_id; 
        $selected_company_id    = Home::where('id',$selected_home_id)->value('admin_id');
        
        $get_homes = Home::select('id','title')
                        ->where('admin_id',$selected_company_id)
                        ->where('is_deleted','0')
                        ->get()
                        ->toArray();
        // $sick_leave_info = StaffSickLeave::select('staff_member_id','leave_date')
        //                                 ->where('id',$u_sick_leave_id)
        //                                 ->where('is_deleted','0')
        //                                 ->first();
        $sick_leave_info = Staffleaves::select('user_id','start_date')
                                        ->where('id',$u_sick_leave_id)
                                        ->where('is_deleted','1')
                                        ->first();
        $user_id =  $sick_leave_info['user_id'];

        if($request->isMethod('post')){
             //echo "<pre>"; print_r($request->input());
                //echo "<pre>"; print_r($request->rota_shift_id); die;
            $sanction_sk_lv_dtl = SanctionStaffSickLeave::where('staff_sick_leave_id',$request->sick_leave_id)
                                                    ->orderBy('id','desc')
                                                    ->first();
            //echo "<pre>"; print_r($sanction_sk_lv_dtl->toArray());
            
            if(!empty($sanction_sk_lv_dtl)){
                    //echo "<pre>"; print_r($request->input()); 
                $sanction_sk_lv_dtl->staff_sick_leave_id    = $request->sick_leave_id;
                $sanction_sk_lv_dtl->home_id                = $request->home_id;
                $sanction_sk_lv_dtl->company_id             = $selected_company_id;
                $sanction_sk_lv_dtl->staff_user_id          = $request->staff_user_id;
                $sanction_sk_lv_dtl->date                   = date('Y-m-d',strtotime($request->leave_date));
                $sanction_sk_lv_dtl->status                 = $request->sanction_leave;
                if($sanction_sk_lv_dtl->save()){

                    if($sanction_sk_lv_dtl->status == 'A'){
                                     
                        $update = User::where('id',$sanction_sk_lv_dtl->staff_user_id)
                                        ->where('is_deleted','0')
                                        ->update(['login_date'=> $request->leave_date,
                                                'login_home_id'=> $sanction_sk_lv_dtl->home_id
                                        ]);    
                    }

                    $rota = RotaShift::where('id',$request->rota_shift_id)
                                ->where('is_deleted','0')
                                ->first();
                    //echo "<pre>"; print_r($rota);
                    //echo "<pre>"; print_r($request->input()); die;
                    if(!empty($rota)) {
                        
                        $staff_rota                = new StaffRota;
                        $staff_rota->home_id       = $request->home_id;
                        $staff_rota->user_id       = $request->staff_user_id;
                        $staff_rota->shift_type_id = $request->rota_shift_id;
                        $staff_rota->date          = date('Y-m-d',strtotime($request->leave_date));
                        $staff_rota->start_time    = $rota->start_time;
                        $staff_rota->end_time      = $rota->end_time;
                        
                        if($staff_rota->save()) {
                            
                            return redirect('admin/user/sick-leaves/'.$request->user_id)->with('success','Record updated successfully.');
                        }
                    }
                    
                }else{
                    return redirect()->back()->with('error','Some error occurred. Please try after sometime.');
                }     
            }else{
                $sanction_leave                         = new SanctionStaffSickLeave;
                $sanction_leave->staff_sick_leave_id    = $request->sick_leave_id;
                $sanction_leave->home_id                = $request->home_id;
                $sanction_leave->company_id             = $selected_company_id;
                $sanction_leave->staff_user_id          = $request->staff_user_id;
                //$sanction_leave->date                   = date('Y-m-d',strtotime($request->date));
                $sanction_leave->status                 = $request->sanction_leave;
                if($sanction_leave->save()){

                    if($sanction_leave->status == 'A'){
                        $update = User::where('id',$sanction_leave->staff_user_id)
                                        ->where('is_deleted','0')
                                        ->update(['login_date'=> $request->leave_date,
                                                'login_home_id'=> $sanction_leave->home_id
                                        ]);    
                    }
                    $rota = RotaShift::where('id',$request->rota_shift_id)
                                ->where('is_deleted','0')
                                ->first(); 
                    //echo "<pre>"; print_r($rota); //die;
                    if(!empty($rota)) {
                        $staff_rota                = new StaffRota;
                        $staff_rota->home_id       = $request->home_id;
                        $staff_rota->user_id       = $request->staff_user_id;
                        $staff_rota->shift_type_id = $request->rota_shift_id;
                        $staff_rota->date          = date('Y-m-d',strtotime($request->leave_date));
                        $staff_rota->start_time    = $rota->start_time;
                        $staff_rota->end_time      = $rota->end_time;
                        
                        if($staff_rota->save()) {
                            // echo "1"; die;
                            return redirect('admin/user/sick-leaves/'.$request->user_id)->with('success','Record updated successfully.');
                        }
                    }
                }else{
                    return redirect()->back()->with('error','Some error occurred. Please try after sometime.');
                }
            }
        }
        // echo "<pre>"; print_r($sick_leave_info->leave_date); die;
        $page = 'user-sick-leave';
        return view('backEnd.user.sickLeave.sanction_leave_form', compact('page','sick_leave_info','user_id','get_homes','u_sick_leave_id','sanction_sk_lv_info','staff_rota','rota_shift'));
    }

    public function staff_user_list($home_id,$user_id){
        
        $get_user_list = User::select('id','name')
                            ->where('home_id',$home_id)
                            ->where('id','!=',$user_id)
                            ->where('user_type','N')
                            ->where('is_deleted','0')
                            ->get();
        
        echo '<option value="">Select User</option>';
        foreach ($get_user_list as $key => $value) {
            echo'<option value="'.$value->id.'">'.$value->name.'</option>';
        }
        die;
    }
    //Staff User Shift
    public function get_staff_rota(Request $request){
        
        if($request->isMethod('post')){
            $staff_id   = $request->sel_staff_user_id;
            $home_id    = $request->sel_home_id;
            $date       = date('Y-m-d',strtotime($request->sel_date));
                
            $shift_types = RotaShift::select('rota_shift.id as type_id','rota_shift.name','rota_shift.start_time','rota_shift.end_time','s_color.color')
                                    ->leftJoin('rota_shift_color as s_color','s_color.id','=','rota_shift.rota_shift_color_id')
                                    ->where('is_deleted','0')
                                    ->where('home_id', $home_id)
                                    ->get();
            echo '<option value="">Select Staff Rota</option>';                        
            foreach ($shift_types as $key => $value) {
                if($value->start_time >= '12'){
                    $start_time = $value->start_time - 12;
                    $start_time = $start_time.'pm';

                }else{
                    $start_time = $value->start_time;                    
                    $start_time = $start_time.'am';
                }

                if($value->end_time >= '12'){
                    $end_time = $value->end_time - 12;
                    $end_time = $end_time.'pm';

                }else{
                    $end_time = $value->end_time;                    
                    $end_time = $end_time.'am';
                }
                echo'<option value="'.$value->type_id.'">'.$value->name.' ('.$start_time.'-'.$end_time.')</option>';
            }
            // echo "<pre>"; print_r($shift_types); die;
        }    
    }
}