<?php
namespace App\Http\Controllers\backEnd\user;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User, App\Staffleaves, App\LeaveType;  
use Illuminate\Support\Facades\Session;

class LatnessLeaveController extends Controller
{
    public function index(Request $request, $user_id){
        $u_home_id = User::where('id',$user_id)->value('home_id');
        $home_id = Session::get('scitsAdminSession')->home_id;
        if($home_id == $u_home_id) {
            $u_late = Staffleaves::where(['user_id'=> $user_id,'leave_type'=>3])->where('is_deleted','1')->select('id','home_id', 'user_id','leave_type','start_date','end_date','notes','leave_status');
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
                $u_late = $u_late->where('notes','like','%'.$search.'%'); //search by date or title
            }

            $u_late_leave = $u_late->paginate(25);
        } else {
            return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
        }
        // $page = 'user-late-leave';
        $page = 'users';
        return view('backEnd.user.latness_leave.latness_leave',compact('page','limit', 'user_id','u_late_leave','search'));
    }
}