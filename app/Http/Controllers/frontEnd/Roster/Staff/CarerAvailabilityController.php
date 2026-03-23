<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use App\Models\ClientCareWorkPrefer;
use App\Services\Staff\CarerWorkingHourService;
use App\Services\Staff\StaffService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CarerAvailabilityController extends Controller
{
    protected StaffService $staffService;
    protected CarerWorkingHourService $carerWorkingHourService;

    public function __construct(StaffService $staffService, CarerWorkingHourService $carerWorkingHourService)
    {
        $this->staffService = $staffService;
        $this->carerWorkingHourService = $carerWorkingHourService;
    }
    public function index()
    {
        return view('frontEnd.roster.staff.staff_availability');
    }

    public function loadUserData(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            return response()->json([
                'status' => false,
                'message' => 'Home ID not found'
            ]);
        }
        $search = trim($req->search ?? '');
        $staff = $this->staffService->allStaff($homeId);
        $staff->select('id', 'home_id', 'name', 'email');
        if (!empty($search)) {
            $staff->where('name', 'LIKE', "%{$search}%");
        }
        $staff->withCount('working_hours as total_working_hours')
            ->withCount('work_unavailability')
            ->withSum(
                ['working_hours as total_working_hours_sum' => function ($q) {
                    $q->select(DB::raw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)/60)'));
                }],
                DB::raw('0')
            );
        /**
         * ----------------------------------
         * Fetch staff
         * ----------------------------------
         */
        $staff = $staff->latest()->paginate(10);

        // dd($staff);

        // Attach qualifications
        // $staff = $this->staffService->attachQualifications($staff);
        // dd($staff);
        /**
         * ----------------------------------
         * Response
         * ----------------------------------
         */
        return response()->json([
            'status' => true,
            'data'   => $staff->items(),
            'next_page' => $staff->nextPageUrl(),
            'pagination' => [
                'total' => $staff->total(),
                'per_page' => $staff->perPage(),
                'current_page' => $staff->currentPage(),
                'total_pages' => $staff->lastPage(),
                'next_page' => $staff->nextPageUrl()
            ]
        ]);
    }

    public function details(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            return response()->json([
                'status' => false,
                'message' => 'Home ID not found'
            ]);
        }
        $user_id = $req->userId;
        $staff = $this->staffService->getCarerAvailabilityDetails($user_id);
        if (!$staff) {
            return response()->json([
                'status' => false,
                'message' => 'Data Not Found !!'
            ], 422);
        }
        return response()->json([
            'status' => true,
            'data'   => $staff,
        ]);
    }
    public function loadworkinghours(Request $req)
    {
        $user_id = $req->userId;

        if (!$user_id) {
            return response()->json([
                'status' => false,
                'message' => 'User ID not found'
            ]);
        }
        $reqData = ['carer_id' => $user_id, 'type' => $req->type];


        if ($req->type == 'specific') {
            $load_overview_data =  $this->carerWorkingHourService->load_specific_working_data($reqData)->map(function ($wh) {
                return [
                    'id' => $wh->id,
                    'type' => 'specific',
                    'start_date' => Carbon::parse($wh->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($wh->end_date)->format('Y-m-d'),
                    'start_time' => Carbon::parse($wh->start_date)->format('H:i'),
                    'end_time' => Carbon::parse($wh->end_date)->format('H:i'),
                    'is_working' => $wh->is_working,
                ];
            })->values();
        } else {
            $load_overview_data = $this->carerWorkingHourService->load_overview_data($reqData);
        }
        // $load_specific_working_data = ;
        $work_preferences = ClientCareWorkPrefer::where('carer_id', $user_id)->first();
        return response()->json([
            'status' => true,
            'data'   => [
                'working_hours' => $load_overview_data,
                'work_preferences' => $work_preferences,
                'staff' => $this->staffService->getCarerAvailabilityDetails($user_id)
            ],
        ]);
    }

    public function save_working_hrs(Request $req)
    {
        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = $user_id;
            // return $reqData;
            $data = $this->carerWorkingHourService->store($reqData);
            if ($data) {
                return response()->json([
                    'status' => true,
                    'message'   => 'Working Hours Saved Successfully !!',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }
    public function save_work_preferences(Request $req)
    {
        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $validator = Validator::make($req->all(), [
                'carer_id' => 'required',
                'max_per_day' => 'required|integer|min:1|max:8',
                'max_per_week' => 'required|integer|min:1|max:40',
                'postcode' => 'nullable|string|max:255',
            ], [
                'max_per_day.required' => 'Max hours per day is required',
                'max_per_day.integer' => 'Max hours per day must be an integer',
                'max_per_day.min' => 'Max hours per day must be at least 1',
                'max_per_day.max' => 'Max hours per day must not exceed 8',
                'max_per_week.required' => 'Max hours per week is required',
                'max_per_week.integer' => 'Max hours per week must be an integer',
                'max_per_week.min' => 'Max hours per week must be at least 1',
                'max_per_week.max' => 'Max hours per week must not exceed 40',
                'postcode.string' => 'Postcode must be a string',
                'postcode.max' => 'Postcode must not exceed 255 characters',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->toArray(),
                ], 422);
            }
            $reqData = $req->all();
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = $user_id;
            // return $reqData;
            $data = $this->carerWorkingHourService->save_work_preferences($reqData);
            if ($data) {
                return response()->json([
                    'status' => true,
                    'message'   => 'Work Preferences Saved Successfully !!',
                    'data' => $data
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }
    public function save_unavailability(Request $req)
    {
        try {
            // return $req->all();
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $validator = Validator::make($req->all(), [
                'carer_id' => 'required',
                'unavailability_id' => 'nullable',
                'unavailability_type' => 'required|in:single,range',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'nullable|date|after:start_date|required_if:unavailability_type,range',
                'unavailability_start_time' => 'nullable',
                'unavailability_end_time' => 'nullable',
                'unavailability_reason' => 'nullable',
            ], [
                'unavailability_type.required' => 'Unavailability type is required',
                'unavailability_type.in' => 'Unavailability type must be either single or range',
                'start_date.required' => 'Start date is required',
                'start_date.date' => 'Start date must be a valid date',
                'start_date.after_or_equal' => 'Start date must be today or a future date',
                'end_date.required_if' => 'End date is required.',
                'end_date.date' => 'End date must be a valid date',
                'end_date.after' => 'End date must be after start date',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->toArray(),
                ], 422);
            }
            $reqData = $req->all();
            $type = $reqData['unavailability_type'] ?? '';
            $reqData['home_id'] = $homeId;
            $reqData['user_id'] = $user_id;
            if ($type == 'single') {
                $reqData['start_date'] = $req->start_date . ' ' . ($req->unavailability_start_time ?? '00:00');
                $reqData['end_date'] = $req->start_date . ' ' . ($req->unavailability_end_time ?? '23:59');
            } else {
                $reqData['start_date'] = $req->start_date . ' ' . "00:00";
                $reqData['end_date'] = $req->end_date . ' ' . "23:59";
            }
            // $reqData['start_date'] = date('Y-m-d', strtotime($reqData['start_date'] ?? ''));
            // $reqData['end_date'] = date('Y-m-d', strtotime($reqData['end_date'] ?? ''));
            //


            // return $reqData;
            $data = $this->carerWorkingHourService->save_unavailability($reqData);
            if ($data) {
                return response()->json([
                    'status' => true,
                    'message'   => 'Unavailability Saved Successfully !!',
                    'data' => $data
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    public function load_unavailability_data(Request $req)
    {
        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $reqData = $req->all();
            // $reqData['home_id'] = $homeId;
            // $reqData['user_id'] = $user_id;
            $data = $this->carerWorkingHourService->get_unavailability_data($reqData);
            $load_staff_leaves = $this->carerWorkingHourService->load_staff_leaves($reqData)
                ->select(
                    'id',
                    'home_id',
                    'user_id',
                    'leave_type',
                    'start_date',
                    'end_date',
                    'end_time',
                    'start_time'
                )
                ->with('leave_types:id,leave_name')
                ->latest()->get()->map(function ($q) {
                    $start_date = date('F j, Y', strtotime($q->start_date));
                    $start_time = date('H:i', strtotime($q->start_time));
                    $end_date = date('F j, Y', strtotime($q->end_date));
                    $end_time = date('H:i', strtotime($q->end_time));
                    $formatted_date = $start_date . ' - ' . $end_date;
                    return [
                        'id' => $q->id,
                        'home_id' => $q->home_id,
                        'user_id' => $q->user_id,
                        'leave_name' => $q->leave_types ? $q->leave_types->leave_name : "",
                        'formatted_date' => $formatted_date,
                    ];
                });
            $ar = [];
            foreach ($data as $item) {

                $start_date = date('D, F j, Y', strtotime($item->start_date));
                $start_time = date('H:i', strtotime($item->start_time));
                $end_date = date('D, F j, Y', strtotime($item->end_date));
                $end_time = date('H:i', strtotime($item->end_time));
                $duration = Carbon::parse($start_time)
                    ->diffInHours(Carbon::parse($end_time));
                $formatted_date = $start_date;
                $formatted_time = '';

                if (isset($item->start_time) && isset($item->end_time)) {
                    $formatted_time = "$start_time - $end_time • $duration hrs";
                }

                if (!empty($item->reason)) {
                    $formatted_time .= $formatted_time ? " • {$item->reason}" : $item->reason;
                }
                if ($item->type == 'range') {
                    $formatted_date = $start_date . ' - ' . $end_date;
                    $formatted_time = $item->reason ?? '';
                }
                $now = Carbon::now();
                $start = Carbon::parse($item->start_date);
                $end = Carbon::parse($item->end_date);

                if ($now->lt($start)) {
                    $status = 'Upcoming';
                    $statusColor = 'inactive';
                } elseif ($now->gt($end)) {
                    $statusColor = 'inactive';
                    $status = 'Past';
                } else {
                    $statusColor = 'yellow';
                    $status = 'Active';
                }
                $ar[] = [
                    'id' => $item->id,
                    'type' => $item->type,
                    'formatted_date' => $formatted_date,
                    'start_time' => $item->start_time,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'reason' => $item->reason,
                    'formatted_time' => $formatted_time,
                    'status' => $status,
                    'statusColor' => $statusColor,
                ];
            }
            return response()->json([
                'status' => true,
                'data'   => $ar,
                'leave_data' => $load_staff_leaves
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }
    public function load_overview_data(Request $req)
    {
        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $reqData = $req->all();
            // $reqData['home_id'] = $homeId;
            // $reqData['user_id'] = $user_id;
            $data = $this->carerWorkingHourService->load_overview_data($reqData);
            $type = 'standard';
            if ($data->count() == 0) {
                // return $reqData;
                $type = 'specific';
                $data = $this->carerWorkingHourService->load_specific_working_data($reqData);
            }

            // return $data;
            $get_unavailability_data = $this->carerWorkingHourService->get_unavailability_data($reqData);
            $get_staff_leaves = $this->carerWorkingHourService->load_staff_leaves($reqData)
                ->select(
                    'id',
                    'home_id',
                    'user_id',
                    'leave_type',
                    'start_date',
                    'end_date',
                    'end_time',
                    'start_time'
                )
                ->with('leave_types:id,leave_name')
                ->latest()->get();
            $ar = [];
            $leave_arr = [];
            $unavailability_ar = [];
            foreach ($data as $item) {
                if ($type == 'specific') {
                    $start_date = date('Y-m-d', strtotime($item->start_date));
                    $end_date = date('Y-m-d', strtotime($item->end_date));
                    $start_time = date('H:i', strtotime($item->start_date));
                    $end_time = date('H:i', strtotime($item->end_date));
                    $week_number = '';
                    $daysName = '';
                    $type = 'specific';
                } else {
                    $week_number = $item->week_number;
                    $start_date = '';
                    $end_date = '';
                    $start_time = date('H:i', strtotime($item->start_time));
                    $end_time = date('H:i', strtotime($item->end_time));
                    $daysName = $item->day;
                    $type = $item->type;
                }


                // $now = Carbon::now();
                // $start = Carbon::parse($item->start_date);
                // $end = Carbon::parse($item->end_date);

                $ar[] = [
                    'id' => $item->id,
                    'type' => $type,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'start_date' => $start_date ?? "",
                    'end_date' => $end_date ?? "",
                    'day' => $daysName,
                    'is_working' => $item->is_working,
                    'week_number' => $week_number
                ];
            }
            foreach ($get_unavailability_data as $item) {

                $start = Carbon::parse($item->start_date);
                $end = Carbon::parse($item->end_date);

                if ($item->type == 'range') {

                    $period = CarbonPeriod::create($start, $end);

                    foreach ($period as $date) {
                        $unavailability_ar[] = $date->format('Y-m-d');
                    }
                } else {

                    $unavailability_ar[] =
                        $start->format('Y-m-d');
                }
            }
            foreach ($get_staff_leaves as $item) {

                $start = Carbon::parse($item->start_date);
                $end = Carbon::parse($item->end_date);

                $period = CarbonPeriod::create($start, $end);

                foreach ($period as $date) {
                    $leave_arr[] = $date->format('Y-m-d');
                }
            }
            sort($unavailability_ar);
            return response()->json([
                'status' => true,
                'data'   => [
                    'availability' => $ar,
                    'unavailability'   => $unavailability_ar,
                    'leave_arr' => $leave_arr,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }

    function delete_unavailability(Request $req)
    {
        try {
            $homeIds = explode(',', auth()->user()->home_id);
            $user_id = auth()->id();
            $homeId  = $homeIds[0] ?? null;

            if (!$homeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Home ID not found'
                ]);
            }
            $unavailabilityId = $req->unavailability_id;
            $data = $this->carerWorkingHourService->delete_unavailability($unavailabilityId);
            if ($data) {
                return response()->json([
                    'status' => true,
                    'message'   => 'Unavailability Deleted Successfully !!',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message'   => $e->getMessage(),
            ], 500);
        }
    }
}
