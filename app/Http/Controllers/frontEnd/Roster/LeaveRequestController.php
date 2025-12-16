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
        $data['totalLeaveCount'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('is_deleted', 1)->count();
        $data['pendingLeaveCount'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 0)->count();
        $data['approvedLeaveCount'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 1)->count();
        $data['rejectedLeaveCount'] = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 2)->count();
        $leave = Staffleaves::where('is_deleted', 1)->where('home_id', Auth::user()->home_id)->where('staff_leaves.leave_status', 1)->get();
        $recordArray = array();
        foreach ($leave as $value) {
            $leave_name = LeaveType::where('id', $value->leave_type)->pluck('leave_name');
            $leave_color = LeaveType::where('id', $value->leave_type)->pluck('color');
            // $user_name  =  ServiceUser::where('id', $value->user_id)->pluck('name');
            $user_name  =  User::where('id', $value->user_id)->pluck('name');
            $arr['title'] =  $user_name;
            $arr['color'] =  $leave_color[0];
            $arr['start'] = $value->start_date;
            $arr['end'] = $value->end_date;
            array_push($recordArray, $arr);
        }
        $data['calender'] = json_encode($recordArray);

        $query = Staffleaves::join('user', 'user.id', '=', 'staff_leaves.user_id')
            ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
            ->where('staff_leaves.is_deleted', 1)
            ->where('staff_leaves.home_id', Auth::user()->home_id)
            ->orderBy('staff_leaves.id', 'DESC')
            ->select(
                'staff_leaves.*',
                'user.name as staff_name',
                'leave_type.leave_name as leave_type_name'
            );

        $data['leaves'] = $query->get();

        $data['pending_leave'] = (clone $query)
            ->where('leave_status', 0)
            ->get();

        $data['approved_leave'] = (clone $query)
            ->where('leave_status', 1)
            ->get();

        $data['rejected_leave'] = (clone $query)
            ->where('leave_status', 2)
            ->get();

        return view('frontEnd.roster.leave.leave_request', $data);
    }

    // function update(Request $request){
    //     $result = Staffleaves::where('id',$request->id)->update(['leave_status'=> 1]);
    //     echo json_encode($result);
    // }

    public function update(Request $request)
    {

        $leave = Staffleaves::find($request->id);

        if (!$leave) {
            return response()->json(['error' => 'Leave not found'], 404);
        }

        $leave->leave_status = $request->status;
        $leave->save();

        return response()->json(['success' => true]);
    }
}
