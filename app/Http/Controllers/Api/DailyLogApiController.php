<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RosterDailyLog;
use App\Models\DailyLogCategory;
use App\Models\AccompanyingStaff;
use App\Models\Transport;
use App\ServiceUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DailyLogApiController extends Controller
{
    /**
     * Get Entity Type List (Subcategories)
     */
    public function get_entity_types(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'data' => []], 200);
        }

        $home_id = $request->home_id;

        $categories = DailyLogCategory::select('id', 'category')
            ->with(['subCategorys' => function ($q) {
                $q->select('id', 'daily_cat_id', 'sub_cat', 'icon', 'color');
            }])
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Entity types fetched successfully.',
            'data' => $categories
        ], 200);
    }

    /**
     * Get Active Service User Data
     */
    public function get_active_service_users(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'data' => []], 200);
        }

        $home_id = $request->home_id;

        $serviceUsers = ServiceUser::select('id', 'name')
            ->where(['home_id' => $home_id, 'is_deleted' => 0])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Service users fetched successfully.',
            'data' => $serviceUsers
        ], 200);
    }

    /**
     * Add Daily Log
     */
    public function add_daily_log(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_id' => 'required',
            'user_id' => 'required', // Staff ID
            'date' => 'required|date',
            'entry_type_id' => 'required',
            'visitor_name' => 'required',
            'client_id' => 'nullable',
            'arrival_time' => 'nullable',
            'departure_time' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 200);
        }

        try {
            DB::beginTransaction();

            $logData = $request->only([
                'home_id',
                'user_id',
                'date',
                'visitor_name',
                'entry_type_id',
                'org_company',
                'purpose_visit',
                'client_id',
                'arrival_time',
                'departure_time',
                'notes',
                'available_for_overtime',
                'follow_details',
                'destination',
                'transport_id',
                'risk_assessment',
                'outing_summary'
            ]);

            // Ensure booleans/integers are handled if passed as strings
            $logData['available_for_overtime'] = $request->available_for_overtime ?? 0;
            $logData['risk_assessment'] = $request->risk_assessment ?? 0;
            $logData['follow_details'] = $request->follow_details ?? null;

            $log = RosterDailyLog::create($logData);

            if ($request->has('accompanyingstaff_id')) {
                $staffIds = is_array($request->accompanyingstaff_id)
                    ? $request->accompanyingstaff_id
                    : explode(',', $request->accompanyingstaff_id);

                foreach ($staffIds as $staffId) {
                    if (!empty($staffId)) {
                        AccompanyingStaff::create([
                            'roster_daily_log_id' => $log->id,
                            'staff_id' => $staffId
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily log added successfully.',
                'data' => $log
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error adding daily log: ' . $e->getMessage()
            ], 200);
        }
    }

    /**
     * Get Transport List
     */
    public function get_transports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'home_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'data' => []], 200);
        }

        $transports = Transport::select('id', 'name')
            ->where('status', 1)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Transports fetched successfully.',
            'data' => $transports
        ], 200);
    }
}
