<?php

namespace App\Http\Controllers\frontEnd\PersonalManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonalManagement\TimeSheet;
use App\Http\Requests\PersonalManagement\TimeSheetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User, App\RotaAssignEmployee;
use Exception;

class TimesheetController extends Controller
{

    public function index($manager_id)
    {
        $data['manager_id'] = $manager_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $data['users'] = User::getHomeUsers($home_id);
        // $data['time_sheets'] = TimeSheet::with('user')->where('deleted_at', null)->orderBy('created_at', 'desc')->get();
        return view('frontEnd.personalManagement.elements.time_sheet.index', $data);
    }
    public function save(TimeSheetRequest $request)
    {
        try {
            $validated = $request->validated();
            // dd($validated);
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $validated['home_id'] = $home_id;
            if (empty($validated['time_sheet_id'])) {
                $validated['time'] = \Carbon\Carbon::now()->format('H:i:s');
                $validated['date'] = \Carbon\Carbon::now()->format('Y-m-d');
            }
            TimeSheet::updateOrCreate(['id' => $validated['time_sheet_id']], $validated);

            if (empty($validated['time_sheet_id'])) {
                return response()->json([
                    'success'=>true,
                    'message' => 'Time sheet saved successfully.',
                    'data' => $validated
                ], 201);
            } else {
                return response()->json([
                    'success'=>true,
                    'message' => 'Time sheet updated successfully.',
                    'data' => $validated
                ], 200);
            }
            // return response()->json([
            //     'message' => 'Time sheet saved successfully.',
            //     'data' => $validated
            // ], 201);
        } catch (\Exception $e) {
            // Optional: Log the error for debugging
            Log::error('TimeSheet Store Error: ' . $e->getMessage());

            return response()->json([
                'success'=>false,
                'message' => 'Failed to save time sheet.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            TimeSheet::where('id', $id)->update(['deleted_at' => now()]);
            return response()->json(['status' => 'success', 'message' => 'Record deleted successfully!']);
        } catch (Exception $e) {
            Log::error('Record deletion failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong while deleting. Please try again.']);
        }
    }

    public function getData(Request $request)
    {
        // $query = TimeSheet::with('user')->where('deleted_at', null)->orderBy('created_at', 'desc');
        $month=date('m');
        $year=date('Y');
        $query = TimeSheet::with('user')->whereMonth('date',$month)->whereYear('date',$year)->where('deleted_at', null)->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        $data=$query->get();
        $category_type='';
        $data_arr=array();
        foreach($data as $key=> $val){
            $total_hours=RotaAssignEmployee::where('emp_id',$val->user_id)->whereDate('created_at',$val->date)->sum('total_hours');
            if($val->category_id == 1){
                $category_type="Sleep";
            }else if($val->category_id == 2){
                $category_type="Disturbance";
            }else if($val->category_id == 3){
                $category_type="Wake Night";
            }else if($val->category_id == 4){
                $category_type="Annual Leave";
            }else{
                $category_type="On Call";
            }
            $ex_time = explode('.', $val->hours);
            $hour = isset($ex_time[0]) ? $ex_time[0] . "h" : "0h";
            $min  = isset($ex_time[1]) ? $ex_time[1] . "min" : "0min";
            $data_arr[]=[
                'id'=>$val->id,
                'user'=>$val->user->name,
                'user_id'=>$val->user_id,
                'date'=>$val->date,
                'total_shift_hours'=>$total_hours,
                'category_type'=>$category_type,
                'category_id'=>$val->category_id,
                'hours'=>$val->hours,
                'comments'=>$val->comments,
            ];
        }

        return response()->json($data_arr);
    }
}
