<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Services\Staff\StaffService, App\Models\CompanyDepartment, App\AccessLevel, App\Models\Staff\UserNote;


class CarerController extends Controller
{
    protected StaffService $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index()
    {
        $homeIds = explode(',', Auth::user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            abort(403, 'Home ID not found.');
        }
        $data['department'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['access_levels'] = AccessLevel::select('id', 'name')->where('home_id', $homeId)->get()->toArray();
        $data['counts'] = $this->staffService->staffCounts($homeId);
        $data['courses'] = $this->staffService->courses();

        return view('frontEnd.roster.staff.carer', $data);
    }

    public function getStaffByStatus(Request $request)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            return response()->json([
                'status' => false,
                'message' => 'Home ID not found'
            ]);
        }

        $type   = $request->type ?? 'allCarerActibity';     // all | active | inactive | leave
        $search = trim($request->search ?? ''); // username / name search

        /**
         * ----------------------------------
         * Base query (COMMON CONDITIONS)
         * ----------------------------------
         */
        switch ($type) {
            case 'activeCarer':
                $staff = $this->staffService->activeStaff($homeId);
                break;

            case 'inactiveCarer':
                $staff = $this->staffService->inactiveStaff($homeId);
                break;

            case 'onLeaveCarer':
                $staff = $this->staffService->onLeaveStaff($homeId); // if exists
                break;

            case 'allCarerActibity':
            default:
                $staff = $this->staffService->allStaff($homeId);
                break;
        }

        /**
         * ----------------------------------
         * SEARCH (username / name)
         * ----------------------------------
         */
        // if ($search !== '') {
        //     $staff = $staff->filter(function ($user) use ($search) {
        //         return str_contains(strtolower($user->name), strtolower($search))
        //             || str_contains(strtolower($user->user_name ?? ''), strtolower($search));
        //     })->values();
        // }



        /**
         * ----------------------------------
         * Apply TAB FILTER
         * ----------------------------------
         */
        // $query = clone $baseQuery;

        // if ($type === 'active') {
        //     $query->where('status', 1);
        // } elseif ($type === 'inactive') {
        //     $query->where('status', 0);
        // } elseif ($type === 'leave') {
        //     // $query->where('on_leave', 1);
        // }

        /**
         * ----------------------------------
         * Apply SEARCH FILTER
         * ----------------------------------
         */
        if (!empty($search)) {
            $staff->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('user_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        /**
         * ----------------------------------
         * Fetch staff
         * ----------------------------------
         */
        $staff = $staff->get();

        // dd($staff);

        // Attach qualifications
        $staff = $this->staffService->attachQualifications($staff);
        // dd($staff);
        /**
         * ----------------------------------
         * Response
         * ----------------------------------
         */
        return response()->json([
            'status' => true,
            'data'   => $staff
        ]);
    }



    public function update(Request $request, $carer_id)
    {
        $staff = User::findOrFail($carer_id);

        // Basic validation (extend as needed)
        $request->validate([
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'nullable|email|max:255',
        ]);

        // delegate to service to perform the update
        $this->staffService->updateFromRequest($staff, $request);

        return back()->with('success', 'Staff updated');
    }

    public function getHourlyRate(Request $request)
    {
        $access_level_id = $request->input('access_level_id');

        if (!$access_level_id) {
            return response()->json(['error' => 'Access level is required.'], 400);
        }

        $pay_rate = $this->staffService->getPayRateForAccessLevel($access_level_id);

        if (!$pay_rate) {
            return response()->json(['error' => 'Pay rate not found.'], 404);
        }

        return response()->json(['hourly_rate' => $pay_rate]);
    }

    public function deleteCarer(Request $request)
    {
        try {
            $request->validate([
                'carer_id' => 'required|integer|exists:user,id'
            ]);

            $carer = User::find($request->carer_id);

            if (!$carer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Carer not found'
                ]);
            }

            // Soft delete (recommended)
            $carer->update(['is_deleted' => 1]);

            // OR Hard delete
            // $carer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Carer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function shift_resources()
    {
        $staff = User::where('home_id', Auth::user()->home_id)->select('id', 'name')->where('is_deleted', 0)->where('status', 1)->get();

        $resources = [];

        // Open Shifts FIRST
        $resources[] = [
            'id'    => 'open',
            'title' => '🟡 Open Shifts',
            'order' => 0
        ];

        foreach ($staff as $index => $member) {
            $resources[] = [
                'id'    => (string) $member->id,
                'title' => $member->name,
                'order' => $index + 1
            ];
        }

        return response()->json($resources);
    }

    public function getShiftStaff($client_id)
    {
        $staff = $this->staffService->getShiftUser($client_id);

        if ($staff && $staff->count() > 0) {
            return response()->json([
                'status' => true,
                'data'   => $staff
            ], 200);
        }

        return response()->json([
            'status'  => false,
            'message' => 'No staff found',
            'data'    => []
        ], 200);
    }

    public function allShifts()
    {
        // Add where('home_id', Auth::user()->home_id) optionally for security as seen elsewhere
        $homeId = Auth::user()->home_id;
        $shifts = \App\Models\ScheduledShift::with(['staff', 'documents', 'assessments', 'recurrence'])->where('home_id', $homeId)->get();

        $events = $shifts->map(function ($shift) {
            $startDate = $shift->start_date;
            $endDate = $shift->end_date ?? $shift->start_date;

            $startTime = \Carbon\Carbon::parse($shift->start_time);
            $endTime = \Carbon\Carbon::parse($shift->end_time);

            return [
                'id' => (string) $shift->id,
                'title' => $shift->client_name ?? ucfirst($shift->shift_type) ?? 'Shift',
                'start' => $startDate . 'T' . $shift->start_time,
                'end' => $endDate . 'T' . $shift->end_time,
                'resourceId' => $shift->staff_id ? (string) $shift->staff_id : 'open',
                'backgroundColor' => $shift->staff_id ? '#d1fae5' : '#fde68a',
                // Extended props for editing:
                'shift_id' => $shift->id,
                'staff_id' => $shift->staff_id,
                'staff_name' => $shift->staff ? $shift->staff->name : null,
                'client_id' => $shift->service_user_id,
                'shift_type_raw' => $shift->shift_type,
                'start_time_raw' => $startTime->format('H:i'),
                'end_time_raw' => $endTime->format('H:i'),
                'start_date' => $shift->start_date,
                'care_type' => $shift->care_type_id,
                'assignment' => $shift->assignment,
                'property_id' => $shift->property_id,
                'location_name' => $shift->location_name,
                'location_address' => $shift->location_address,
                'home_area_id' => $shift->home_area_id,
                'notes' => $shift->notes,
                'tasks' => $shift->tasks,
                'documents' => $shift->documents,
                'assessments' => $shift->assessments,
                'is_recurring' => $shift->is_recurring,
                'recurrence' => $shift->recurrence,
            ];
        })->toArray();

        return response()->json($events);
    }

    public function dayShifts(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $homeId = Auth::user()->home_id;
        $shifts = \App\Models\ScheduledShift::with(['staff', 'client', 'recurrence', 'documents', 'assessments'])
            ->where('start_date', $date)
            ->where('home_id', $homeId)
            ->get();

        $formatted = $shifts->map(function ($shift) {
            $startTime = \Carbon\Carbon::parse($shift->start_time);
            $endTime = \Carbon\Carbon::parse($shift->end_time);
            $durationFormat = $startTime->diffInHours($endTime);

            return [
                'id' => $shift->id,
                'shift_type' => ucfirst($shift->shift_type),
                'shift_type_raw' => $shift->shift_type,
                'start_time' => $startTime->format('H:i'),
                'start_time_raw' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'end_time_raw' => $endTime->format('H:i'),
                'duration' => $durationFormat . 'h',
                'staff_name' => $shift->staff ? $shift->staff->name : 'Unassigned',
                'staff_id' => $shift->staff_id,
                'client_name' => $shift->client ? $shift->client->name : 'Unknown Location',
                'client_id' => $shift->service_user_id,
                'property_id' => $shift->property_id,
                'location_name' => $shift->location_name,
                'location_address' => $shift->location_address,
                'home_area_id' => $shift->home_area_id,
                'start_date' => $shift->start_date,
                'care_type' => $shift->care_type_id,
                'assignment' => $shift->assignment,
                'notes' => $shift->notes,
                'tasks' => $shift->tasks,
                'is_recurring' => $shift->is_recurring,
                'recurrence' => $shift->recurrence,
                'documents' => $shift->documents,
                'assessments' => $shift->assessments,
            ];
        });

        return response()->json([
            'date' => \Carbon\Carbon::parse($date)->format('l, F j, Y'),
            'total' => $shifts->count(),
            'shifts' => $formatted
        ]);
    }
    public function weekShifts(Request $request)
    {
        $dateStr = $request->query('date', date('Y-m-d'));
        $date = \Carbon\Carbon::parse($dateStr);
        // Assuming week starts on Monday
        $startOfWeek = $date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = $date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $homeId = Auth::user()->home_id;
        $shifts = \App\Models\ScheduledShift::with(['staff', 'client', 'recurrence', 'documents', 'assessments'])
            ->whereBetween('start_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->where('home_id', $homeId)
            ->get();

        $weekData = [];
        $currentDate = $startOfWeek->copy();

        while ($currentDate <= $endOfWeek) {
            $dateKey = $currentDate->format('Y-m-d');

            $dayShifts = $shifts->where('start_date', $dateKey)->map(function ($shift) {
                $startTime = \Carbon\Carbon::parse($shift->start_time);
                $endTime = \Carbon\Carbon::parse($shift->end_time);
                $durationFormat = $startTime->diffInHours($endTime);

                return [
                    'id' => $shift->id,
                    'shift_type' => ucfirst($shift->shift_type),
                    'shift_type_raw' => $shift->shift_type,
                    'start_time' => $startTime->format('H:i'),
                    'start_time_raw' => $startTime->format('H:i'),
                    'end_time' => $endTime->format('H:i'),
                    'end_time_raw' => $endTime->format('H:i'),
                    'duration' => $durationFormat . 'h',
                    'staff_name' => $shift->staff ? $shift->staff->name : 'Unassigned',
                    'staff_id' => $shift->staff_id,
                    'client_name' => $shift->client ? $shift->client->name : 'Unknown Location',
                    'client_id' => $shift->service_user_id,
                    'property_id' => $shift->property_id,
                    'location_name' => $shift->location_name,
                    'location_address' => $shift->location_address,
                    'home_area_id' => $shift->home_area_id,
                    'start_date' => $shift->start_date,
                    'care_type' => $shift->care_type_id,
                    'assignment' => $shift->assignment,
                    'notes' => $shift->notes,
                    'tasks' => $shift->tasks,
                    'is_recurring' => $shift->is_recurring,
                    'recurrence' => $shift->recurrence,
                    'documents' => $shift->documents,
                    'assessments' => $shift->assessments,
                ];
            })->values();

            $weekData[] = [
                'date' => $dateKey,
                'day_name' => $currentDate->format('D'),
                'day_number' => $currentDate->format('d'),
                'is_today' => $dateKey == date('Y-m-d'),
                'shifts' => $dayShifts
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'week_label' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y'),
            'days' => $weekData
        ]);
    }

    public function ninetyDaysShifts(Request $request)
    {
        $dateStr = $request->query('date', date('Y-m-d'));
        $startDate = \Carbon\Carbon::parse($dateStr);
        $endDate = $startDate->copy()->addDays(90);

        $homeId = Auth::user()->home_id;
        $shifts = \App\Models\ScheduledShift::whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->where('home_id', $homeId)->get();

        $totalShifts = $shifts->count();
        $unfilledShifts = 0;
        $filledShifts = 0;

        foreach ($shifts as $shift) {
            if ($shift->status == 'unfilled' || empty($shift->staff_id)) {
                $unfilledShifts++;
            } else {
                $filledShifts++;
            }
        }

        $fillRate = $totalShifts > 0 ? round(($filledShifts / $totalShifts) * 100) : 0;

        $weeksData = [];
        $currentDate = $startDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekNumber = 1;

        while ($currentDate < $endDate) {
            $weekStart = $currentDate->copy();
            $weekEnd = $currentDate->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

            $weekShifts = $shifts->filter(function ($shift) use ($weekStart, $weekEnd) {
                $shiftDate = \Carbon\Carbon::parse($shift->start_date);
                return $shiftDate->between($weekStart, $weekEnd);
            });

            $weekTotal = $weekShifts->count();
            $weekUnfilled = 0;
            $weekFilled = 0;
            $weekCompleted = 0;

            foreach ($weekShifts as $shift) {
                if ($shift->status == 'unfilled' || empty($shift->staff_id)) {
                    $weekUnfilled++;
                } else {
                    $weekFilled++;
                }

                if (strtolower($shift->status) == 'completed') {
                    $weekCompleted++;
                }
            }

            $weekFillRate = $weekTotal > 0 ? round(($weekFilled / $weekTotal) * 100) : 0;

            $weeksData[] = [
                'week_number' => $weekNumber,
                'label' => 'Week ' . $weekNumber . ': ' . $weekStart->format('M j') . ' - ' . $weekEnd->format('M j, Y'),
                'total' => $weekTotal,
                'filled' => $weekFilled,
                'unfilled' => $weekUnfilled,
                'completed' => $weekCompleted,
                'fill_rate' => $weekFillRate
            ];

            $currentDate->addWeek();
            $weekNumber++;

            if ($weekNumber > 13) {
                break;
            }
        }

        return response()->json([
            'overview_date' => $startDate->format('l, F j, Y'),
            'summary' => [
                'total' => $totalShifts,
                'filled' => $filledShifts,
                'unfilled' => $unfilledShifts,
                'fill_rate' => $fillRate
            ],
            'weeks' => $weeksData
        ]);
    }
}
