<?php

namespace App\Http\Controllers\Android;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Hash, DB;
use App\User, App\Admin, App\Home;
use App\LeaveType, App\Staffleaves, App\LoginInActivity, App\ServiceUser;
use App\Models\ScheduledShift;


class AndroidApiController extends Controller
{

    // public function add_su_device(Request $request) {

    //     $data = $request->input();

    //     $validator = Validator::make($data, [
    //         'service_user_id' => 'required',
    //         'device_token'    => 'required',
    //         'device_unique_id'=> 'required',
    //         'device_type'     => 'required',
    //     ]);

    //     if($validator->fails()) {

    //         $result['response'] = false;
    //         $result['message']  = FILL_FIELD_ERR;
    //         return json_encode($result);
    //     }

    //     $data['user_type'] = 0;        
    //     $data['user_id']   = $data['service_user_id'];        

    //     $res = $this->_save_device($data);

    //     if($res == true){
    //         $result['response'] = true;
    //         $result['message']  = 'Device added successfully';
    //     } else{
    //         $result['response'] = false;
    //         $result['message']  = COMMON_ERROR;
    //     }
    //     return json_encode($result);
    // }

    // public function add_user_device(Request $request){
    //     $data = $request->input();

    //     $validator = Validator::make($data, [
    //         'user_id' => 'required',
    //         'device_token'    => 'required',
    //         'device_unique_id'=> 'required',
    //         'device_type'     => 'required',
    //     ]);

    //     if($validator->fails()) {

    //         $result['response'] = false;
    //         $result['message']  = FILL_FIELD_ERR;
    //         return json_encode($result);
    //     }

    //     $data['user_type'] = 1;      
    //     $res = $this->_save_device($data);

    //     if($res == true){
    //         $result['response'] = true;
    //         $result['message']  = 'Device added successfully';
    //     } else{
    //         $result['response'] = false;
    //         $result['message']  = COMMON_ERROR;
    //     }
    //     return json_encode($result);

    // }

    // function _save_device($data){

    //     $user_device = UserDevice::where('device_unique_id',$data['device_unique_id'])->where('device_type',$data['device_type'])->first();

    //     if(empty($user_device)){
    //         $user_device                = new UserDevice;
    //     }
    //     $user_device->user_id           = $data['user_id'];
    //     $user_device->user_type         = $data['user_type'];
    //     $user_device->device_token      = $data['device_token'];
    //     $user_device->device_unique_id  = $data['device_unique_id'];
    //     $user_device->device_type       = $data['device_type'];

    //     if($user_device->save()){
    //         return true;
    //     } else{
    //         return false;
    //     }
    // }


    // public function remove_device(Request $request) {

    //     $data = $request->input();

    //     if( !empty($data['user_id']) && !empty($data['user_type']) ) {
    //         $delete = UserDevice::where(['id' => $user_id, 'user_type' => $user_type])->delete();
    //         if($delete) {
    //             $result['response'] = true;
    //             $result['message']  = 'Device remove successfully.';
    //         } else {
    //             $result['response'] = false;
    //             $result['message']  = COMMON_ERROR;
    //         }
    //     } else {
    //         $result['response'] = false;
    //         $result['message']  = "Please fill the required fields.";
    //     }
    //     return json_encode($result);
    // }
    // Ram 19/08/2025 code for get homes
    public function get_homes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'Data' => array()], 200);
        }
        $admin_id = Admin::where('company', 'like', $request->company_name)->where('is_deleted', 0)->value('id');
        if ($admin_id) {
            $homes = Home::select('id', 'title')->where('admin_id', $admin_id)->where('is_deleted', '0')->get();
            return response()->json(['success' => true, 'message' => 'All Homes.', 'Data' => $homes], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Company Name is not correct.', 'Data' => array()], 200);
        }
    }
    public function user_login(Request $request)
    {
        if ($request->user_name === null) {
            return response()->json(['success' => false, 'message' => 'Please provide username..!'], 200);
        }
        if ($request->password == null) {
            return response()->json(['success' => false, 'message' => 'Please provide valid password..!'], 200);
        }
        // if($request->home_id == null){
        //     return response()->json(['success'=> false, 'message'=>'Please provide home id..!'], 200);
        // }

        $recordArray = array();
        // $check_username = ServiceUser::where('user_name', $request->user_name)->first();
        $check_username = User::where('user_name', $request->user_name)->first();
        if (!$check_username) {
            return response()->json(['success' => false, 'message' => 'Invalid Email Address!'], 200);
        }
        if (!Hash::check($request->password, $check_username->password)) {

            return response()->json(['success' => false, 'message' => 'Invalid Password!'], 200);
        } else {
            $data['id'] = $check_username->id;
            $data['home_id'] = $check_username->home_id;
            // $data['home_id'] = $request->home_id;
            $data['name'] = $check_username->name;
            $data['user_name'] = $check_username->user_name;
            $data['phone_no'] = $check_username->phone_no;
            $data['date_of_birth'] = $check_username->date_of_birth ?? "";
            $data['section'] = $check_username->section ?? "";
            $data['admission_number'] = $check_username->admission_number ?? "";
            $data['short_description'] = $check_username->short_description ?? "";
            $data['height'] = $check_username->height ?? "";
            $data['weight'] = $check_username->weight ?? "";
            $data['hair_and_eyes'] = $check_username->hair_and_eyes ?? "";
            $data['markings'] = $check_username->markings ?? "";
            $data['image'] = url('public/images/userProfileImages/') . '/' . $check_username->image;
            $data['email'] = $check_username->email;
            $data['personal_info'] = $check_username->personal_info;
            $data['education_history'] = $check_username->education_history ?? "";
            $data['bereavement_issues'] = $check_username->bereavement_issues ?? "";
            $data['drug_n_alcohol_issues'] = $check_username->drug_n_alcohol_issues ?? "";
            $data['mental_health_issues'] = $check_username->mental_health_issues ?? "";
            $data['current_location'] = $check_username->current_location ?? "";
            $data['previous_location'] = $check_username->previous_location ?? "";
            $data['mobile'] = $check_username->mobile ?? "";
            // $data['last_loc_area_type'] = $check_username->last_loc_area_type;
            // $data['location_get_interval'] = $check_username->location_get_interval;
            $data['created_at'] = $check_username->created_at;
            array_push($recordArray, $data);
            return response()->json(['success' => true, 'message' => 'You have successfully logged in.', 'user_data' => $recordArray[0]], 200);
        }
    }

    public function get_leave_list()
    {
        $recordArray = array();
        $leaves = LeaveType::where('status', 1)->get();
        foreach ($leaves as $leave) {
            $data['id'] = $leave->id;
            $data['leave_name'] = $leave->leave_name;
            $data['leave_category'] = $leave->leave_category;
            array_push($recordArray, $data);
        }

        if (!$recordArray) {
            return response()->json(['success' => false, 'message' => 'No data'], 200);
        }
        return response()->json(['success' => true, 'message' => '', 'Data' => $recordArray], 200);
    }

    public function add_user_leave(Request $request)
    {
        if ($request->userId == null) {
            return response()->json(['success' => false, 'message' => 'Please provide user id..!'], 200);
        }
        if ($request->home_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide home id..!'], 200);
        }
        if ($request->leaveType == null) {
            return response()->json(['success' => false, 'message' => 'Please provide leave Type..!'], 200);
        }

        if ($request->start_date == null) {
            return response()->json(['success' => false, 'message' => 'Please provide start date..!'], 200);
        }

        if ($request->endDate == null) {
            return response()->json(['success' => false, 'message' => 'Please provide end date..!'], 200);
        }

        $startDate = $request->start_date;
        $endDate   = $request->endDate;

        $leaveExists = Staffleaves::where('user_id', $request->userId)
            ->where('home_id', $request->home_id)
            ->where('is_deleted', 1)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->exists();

        if ($leaveExists) {
            return response()->json([
                'success' => false,
                'message' => 'User leave already exists for the selected date range.'
            ], 200);
        }


        if ($request->ongoingLeave == "yes") {
            $ongoingLeave = 1;
        } else {
            $ongoingLeave = 0;
        }

        if (empty($request->start_date)) {
            // $date = $late_date;
            $date = $request->late_date;
        } else {
            $date = $request->start_date;
        }
        if (empty($request->late_time)) {
            $late_time = null;
        } else {
            $late_time = $request->late_time;
        }
        if (empty($request->missed_days)) {
            $missed_working_days = null;
        } else {
            $missed_working_days = $request->missed_days;
        }

        $add_leave = array(
            'home_id' => $request->home_id,
            'user_id' => $request->userId,
            'leave_type' => $request->leaveType,
            'ongoing_absence' => $ongoingLeave,
            'start_date' => $date,
            'start_date_full_half' => $request->start_date_full_half,
            'end_date' => $request->endDate,
            'end_date_full_half' => $request->end_date_full_half,
            'late_by' => $late_time,
            'notes' => $request->notes,
            'days' => $missed_working_days,
            'leave_status' => 0,
            'is_deleted' => 1,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        );
        $data = Staffleaves::insert($add_leave);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'No data'], 200);
        }
        return response()->json(['success' => true, 'message' => 'Record inserted successfully', 'Data' => $data], 200);
    }
    public function user_leave_cancel(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        try {
            $validator = Validator::make($request->all(), [
                'leave_id'    => 'required|exists:staff_leaves,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message'  => $validator->errors()->first(),
                    'data'    => json_decode('{}')
                ]);
            }
            DB::beginTransaction();
            $leave = Staffleaves::find($request->leave_id);
            $leave->is_deleted = 0;
            $leave->save();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Leave cancelled successfully', 'data' => json_decode('{}')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error leave cancel: ' . $e->getMessage(),
            ];
        }
    }

    // public function get_user_leave(Request $request)
    // {
    //     if ($request->user_id == null) {
    //         return response()->json(['success' => false, 'message' => 'Please provide user id..!'], 200);
    //     }
    //     $recordArray = array();
    //     $leaves = Staffleaves::where('user_id', $request->user_id)->where('is_deleted', 1)->orderBy('id', 'DESC')->get();

    //     foreach ($leaves as $leave) {
    //         $data['user_id'] = $leave->user_id;
    //         $data['leave_type'] = $leave->leave_type;
    //         $data['start_date'] = $leave->start_date;

    //         if (is_null($leave->end_date)) {
    //             $end_date = "";
    //             $days = 0;
    //         } else {
    //             $end_date = $leave->end_date;
    //             $to = \Carbon\Carbon::parse($leave->start_date);
    //             $from = \Carbon\Carbon::parse($leave->end_date);
    //             $days = $to->diffInDays($from) + 1;
    //         }
    //         $data['days'] = $days;
    //         $data['end_date'] = $end_date;

    //         if (is_null($leave->notes)) {
    //             $notes = "";
    //         } else {
    //             $notes = $leave->notes;
    //         }
    //         $data['notes'] = $notes;

    //         $data['leave_status'] = $leave->leave_status;
    //         $data['created_at'] = \Carbon\Carbon::parse($leave->created_at)->format('d-m-Y');
    //         array_push($recordArray, $data);
    //     }

    //     if (!$recordArray) {
    //         return response()->json(['success' => false, 'message' => 'No data'], 200);
    //     }
    //     return response()->json(['success' => true, 'message' => '', 'Data' => $recordArray], 200);
    // }
    public function get_user_leave(Request $request)
    {
        // Validate request
        $request->validate([
            'user_id' => 'required|integer|exists:user,id',
        ]);
        $filter_key = [0, 1, 2, 3];
        if ($request->has('filter') && !in_array($request->filter, $filter_key)) {
            return response()->json(['success' => false, 'message' => 'Please provide correct filter!'], 200);
        }
        $leaves_query = Staffleaves::select(
            'staff_leaves.*',
            'leave_type.leave_name'
        )
            ->join('leave_type', 'leave_type.id', '=', 'staff_leaves.leave_type')
            ->where('staff_leaves.user_id', $request->user_id)
            ->where('staff_leaves.is_deleted', 1);

        if ($request->filter == 1) {
            $leaves_query->where('staff_leaves.leave_status', 0);
        } else if ($request->filter == 2) {
            $leaves_query->where('staff_leaves.leave_status', 1);
        } else if ($request->filter == 3) {
            $leaves_query->where('staff_leaves.leave_status', 2);
        }
        $leaves = $leaves_query->orderByDesc('staff_leaves.id')
            ->get();

        $leaveCounts = Staffleaves::where('user_id', $request->user_id)
            ->where('is_deleted', 1)
            ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN leave_status = 0 THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN leave_status = 1 THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN leave_status = 2 THEN 1 ELSE 0 END) as rejected
                    ')
            ->first();


        if ($leaves->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No leave records found',
                'total'    => (int) $leaveCounts->total,
                'pending'  => (int) $leaveCounts->pending,
                'approved' => (int) $leaveCounts->approved,
                'rejected' => (int) $leaveCounts->rejected,
                'data'    => []
            ], 200);
        }

        $recordArray = $leaves->map(function ($leave) {

            $startDate = $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('d M Y') : '';

            $endDate = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('d M Y') : '';

            $startDay = \Carbon\Carbon::parse($startDate)->format('D');
            $endDay   = \Carbon\Carbon::parse($endDate)->format('D');

            $leaveDays = $startDay . ' to ' . $endDay ?? '';

            $days = ($startDate && $endDate)
                ? \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1
                : 0;

            return [
                'id' => $leave->id,
                'user_id'      => $leave->user_id,
                'leave_type'   => $leave->leave_name,
                'start_date'   => $startDate ?? '',
                'end_date'     => $endDate ?? '',
                'leave_days'   => $leaveDays,
                'days'         => $days,
                'notes'        => $leave->notes ?? '',
                'leave_status' => $leave->leave_status == 1
                    ? 'Approved'
                    : ($leave->leave_status == 2 ? 'Rejected' : 'Pending'),
                'created_at'   => \Carbon\Carbon::parse($leave->created_at)->format('d M Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Leave records fetched successfully',
            'total'    => (int) $leaveCounts->total,
            'pending'  => (int) $leaveCounts->pending,
            'approved' => (int) $leaveCounts->approved,
            'rejected' => (int) $leaveCounts->rejected,
            'data'    => $recordArray
        ], 200);
    }

    public function add_login_activity(Request $request)
    {
        if ($request->user_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us user id..!'], 200);
        } else if ($request->latitude_in == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us latitude..!'], 200);
        } else if ($request->longitude_in == null) {
            return response()->json(['success' => false, 'message' => 'Please provide longitude..!'], 200);
        } else if ($request->home_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide home id..!'], 200);
        } else if ($request->shift_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide shift id..!'], 200);
        } else {

            // $record = ServiceUser::where('id', $request->user_id)->where('is_deleted', 0)->where('home_id', $request->home_id)->get();
            $record = User::where('id', $request->user_id)->where('is_deleted', 0)->whereRaw('FIND_IN_SET(?, home_id)', [$request->home_id])->exists();
            // if( $record->count() == 1 ){
            if ($record) {
                $activity = new LoginInActivity();
                $activity->user_id = $request->user_id;
                $activity->shift_id = $request->shift_id;
                $activity->login_date = date('Y-m-d');
                $activity->check_in_time = date("Y-m-d H:i:s");
                $activity->latitude_in = $request->latitude_in;
                $activity->longitude_in = $request->longitude_in;
                $activity->check_in_reason = $request->check_in_reason ?? '';
                $activity->home_id = $request->home_id;
                $activity->save();

                if ($activity->id) {
                    return response()->json(['success' => true, 'message' => 'Checked in successfully..! ', 'Data' => $activity->id], 200);
                }
                return response()->json(['success' => false, 'message' => 'Error in record insert'], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid QR code'], 200);
            }
        }
    }
    public function check_out_activity(Request $request)
    {
        if ($request->user_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us user id..!'], 200);
        }
        if ($request->activity_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us activity id..!'], 200);
        }
        if ($request->latitude_out == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us latitude..!'], 200);
        }
        if ($request->longitude_out == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us longitude..!'], 200);
        }
        if ($request->home_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us home id..!'], 200);
        }
        if ($request->shift_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us shift id..!'], 200);
        }
        // if($request->reason == null){
        //     return response()->json(['success'=> false, 'message'=>'Please provide us reason..!'], 200);
        // }

        // $record = ServiceUser::where('id', $request->user_id)->where('is_deleted', 0)->where('home_id', $request->home_id)->get();
        $record = User::where('id', $request->user_id)->where('is_deleted', 0)->whereRaw('FIND_IN_SET(?, home_id)', [$request->home_id])->exists();
        // if( $record->count() == 1 ){
        if ($record) {
            // $response = LoginInActivity::where('id', $request->activity_id)->where('user_id', $request->user_id)->where('company_id', $request->company_id)->update(['check_out_time'=>date("Y-m-d H:i:s"), 'latitude_out'=> $request->latitude_out, 'longitude_out'=>$request->longitude_out]);
            $response = LoginInActivity::where('id', $request->activity_id)
                ->where('user_id', $request->user_id)
                ->update([
                    'check_out_time' => date("Y-m-d H:i:s"),
                    'latitude_out' => $request->latitude_out,
                    'longitude_out' => $request->longitude_out,
                    'check_out_reason' => $request->check_out_reason ?? ''
                ]);

            if ($response) {
                return response()->json(['success' => true, 'message' => 'Checked out successfully..! ', 'Data' => $response, 'activity_id' => $request->activity_id, 'time' => date("Y-m-d H:i:s")], 200);
            }
            return response()->json(['success' => false, 'message' => 'Error in record update'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid QR code'], 200);
        }
    }

    public function check_out_activity_update_reason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required|exists:login_activities,id',
            'check_out_reason' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 200);
        }

        $response = LoginInActivity::where('id', $request->activity_id)
            ->update([
                'check_out_reason' => $request->check_out_reason
            ]);

        if ($response) {
            return response()->json(['success' => true, 'message' => 'Reason updated successfully..! '], 200);
        }
        return response()->json(['success' => false, 'message' => 'Error in record update'], 200);
    }

    public function get_user_activity(request $request)
    {
        if ($request->user_id == null) {
            return response()->json(['success' => false, 'message' => 'Please provide us user id..!'], 200);
        }

        $limit = $request->limit ? $request->limit : 10;
        $page = $request->page ? $request->page : 1;
        
        $recordArray = array();
        $activities = LoginInActivity::where('user_id', $request->user_id)
            ->orderBy('id', 'DESC')
            ->where('is_deleted', 0)
            ->paginate($limit, ['*'], 'page', $page);

        foreach ($activities as $activity) {
            $data['id'] = $activity->id;
            $data['login_date'] = $activity->login_date;
            $data['check_in_time'] = \Carbon\Carbon::parse($activity->check_in_time)->format('H:i');
            $data['latitude_in'] = $activity->latitude_in;
            $data['longitude_in'] = $activity->longitude_in;

            // Fetch matching shift data
            $shift = ScheduledShift::with('client')->where('staff_id', $activity->user_id)
                ->where('start_date', $activity->login_date)
                ->orderByRaw("ABS(TIMESTAMPDIFF(SECOND, TIMESTAMP(CONCAT(start_date, ' ', start_time)), '{$activity->check_in_time}'))")
                ->first();

            $shift_time = "";
            $overtime = "00:00:00";
            $client_name = "";

            if ($shift) {
                $client_name = $shift->client->name ?? '';
                $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);
                $shift_time = $shiftStart->diff($shiftEnd)->format('%H:%I:%S');

                if (!is_null($activity->check_out_time)) {
                    $actualStart = \Carbon\Carbon::parse($activity->check_in_time);
                    $actualEnd = \Carbon\Carbon::parse($activity->check_out_time);

                    $overtimeSeconds = 0;

                    // Clock in earlier than shift start
                    if ($actualStart->lt($shiftStart)) {
                        $overtimeSeconds += $actualStart->diffInSeconds($shiftStart);
                    }

                    // Clock out later than shift end
                    if ($actualEnd->gt($shiftEnd)) {
                        $overtimeSeconds += $shiftEnd->diffInSeconds($actualEnd);
                    }

                    if ($overtimeSeconds > 0) {
                        $hours = floor($overtimeSeconds / 3600);
                        $minutes = floor(($overtimeSeconds / 60) % 60);
                        $seconds = $overtimeSeconds % 60;
                        $overtime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }
                }
            }

            $data['client_name'] = $client_name;
            $data['shift_time'] = !empty($shift_time) ? \Carbon\Carbon::parse($shift_time)->format('H:i') : "";
            $data['overtime']   = !empty($overtime) ? \Carbon\Carbon::parse($overtime)->format('H:i') : "00:00";

            if (is_null($activity->check_out_time)) {
                $check_out = "";
                $logged_time = "";
                $data['check_out_time'] = "";
                $data['logged_time'] = "";
            } else {
                $check_out = $activity->check_out_time;
                $checkIn  = \Carbon\Carbon::parse($activity->check_in_time);
                $checkOut = \Carbon\Carbon::parse($activity->check_out_time);
                $logged_time = $checkIn->diff($checkOut)->format('%H:%I:%S');
                $data['check_out_time'] = \Carbon\Carbon::parse($check_out)->format('H:i');
                $data['logged_time'] = \Carbon\Carbon::parse($logged_time)->format('H:i');
            }
            if (is_null($activity->latitude_out) || is_null($activity->longitude_out)) {
                $latitude_out = "";
                $longitude_out = "";
            } else {
                $latitude_out = $activity->latitude_out;
                $longitude_out = $activity->longitude_out;
            }
            $data['latitude_out'] = $latitude_out;
            $data['longitude_out'] = $longitude_out;
            array_push($recordArray, $data);
        }

        if ($activities->count() > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Fetched successfully',
                'total' => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'limit' => $activities->perPage(),
                'Data' => $recordArray
            ], 200);
        }
        return response()->json([
            'success' => false, 
            'message' => 'No data', 
            'total' => $activities->total(),
            'current_page' => $activities->currentPage(),
            'last_page' => $activities->lastPage(),
            'limit' => $activities->perPage(),
            'Data' => []
        ], 200);
    }

    public function QRCode(Request $request)
    {
        if ($request->val == 1) {
            $qr_id = uniqid('qr');
            $admin = Admin::where('id', $request->id)->update(['qr_code_id' => $qr_id]);
        }
        $details['qr_code_id'] = Admin::where('id', $request->id)->value('qr_code_id');

        echo json_encode($details);
    }

    public function get_company_data(Request $request)
    {
        if (empty($request->qr_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide qr_id.'
            ], 200);
        }

        $details = Admin::where('qr_code_id', $request->qr_id)->first();

        // ❌ QR code not found
        if (!$details) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not found.'
            ], 404);
        }

        $data = [
            'company_id'    => $details->id,
            'name'          => $details->name,
            'user_name'     => $details->user_name,
            'email'         => $details->email,
            'company'       => $details->company,
            'access_type'   => $details->access_type,
            'home_id'       => $details->home_id,
            'image'         => $details->image,
            'security_code' => $details->security_code,
            'qr_code_id'    => $details->qr_code_id,
            'address'       => $details->address,
            'latitude'      => $details->latitude,
            'longitude'     => $details->longitude,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Company data fetched successfully.',
            'data'    => $data
        ], 200);
    }

    public function get_home_data(Request $request)
    {
        if (empty($request->qr_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide qr_id.'
            ], 200);
        }

        $details = Home::where('qr_code_id', $request->qr_id)->first();

        // ❌ QR code not found
        if (!$details) {
            return response()->json([
                'success' => false,
                'message' => 'QR code not found.'
            ], 404);
        }

        $data = [
            'home_id'       => $details->id,
            'company_id'    => $details->admin_id,
            'name'          => $details->title,
            'address'       => $details->address,
            'range'         => (int)$details->home_area,
            'qr_code_id'    => $details->qr_code_id,
            'address'       => $details->address,
            'latitude'      => $details->latitude,
            'longitude'     => $details->longitude,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Home data fetched successfully.',
            'data'    => $data
        ], 200);
    }
}
