<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Staffleaves, App\LeaveType, App\User;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $data['total_leave'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('is_deleted', 1)->count();
        $data['pending'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 0)->count();
        $data['approved'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 1)->count();
        $data['rejected'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 2)->count();
        $leave = Staffleaves::where('is_deleted', 1 )->where('home_id', Auth::user()->home_id)->where('staff_leaves.leave_status', 1)->get();
        $recordArray = array();
        foreach($leave as $value){
            $leave_name = LeaveType::where('id', $value->leave_type)->pluck('leave_name'); 
            $leave_color = LeaveType::where('id', $value->leave_type)->pluck('color'); 
            // $user_name  =  ServiceUser::where('id', $value->user_id)->pluck('name');
            $user_name  =  User::where('id', $value->user_id)->pluck('name');
            $arr['title'] =  $user_name;
            $arr['color'] =  $leave_color[0];
            $arr['start'] = $value->start_date;
            $arr['end'] = $value->end_date;
            array_push($recordArray,$arr);
        }
        $data['calender']=json_encode($recordArray);

        $data['leaves'] = Staffleaves::join('user', 'user.id', '=', 'staff_leaves.user_id')
            ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
            ->where('staff_leaves.is_deleted', 1)
            ->where('staff_leaves.home_id', Auth::user()->home_id)
            ->orderBy('staff_leaves.id', 'DESC')
            ->select(
                'staff_leaves.*',
                'user.name as staff_name',
                'leave_type.leave_name as leave_type_name'
            )
            ->get();


        return view('frontEnd.roster.leave.leave_request', $data);
    }
}
