<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use App\Models\ScheduledShift;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Log;
use Exception;

class PayrollFinanceController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.payroll_finance.index');
    }
    public function payrollprocessing()
    {
        return view('frontEnd/roster/payroll_finance/payroll_processing');
    }
    public function timesheetreconciliation()
    {
        $users = User::getHomeActiveUsers();
        $userId = \Illuminate\Support\Facades\Auth::user()->id;
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;
        $categories = \App\Models\ShiftCategory::orderBy('name')->get();

        // Default to current week
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
        $endOfWeek = \Carbon\Carbon::now()->endOfWeek();

        // Fetch all shifts for the home to provide a comprehensive reconciliation view
        $shifts = \App\Models\ScheduledShift::where('home_id', $homeId)
            ->with(['staff', 'shiftCategory', 'timesheet'])
            ->get()
            ->sortByDesc('start_date')
            ->map(function ($shift) {

                $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);

                // If it ends the next day (e.g. 22:00 to 06:00), add a day to end_time
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $shift->scheduled_duration_minutes = $shiftStart->diffInMinutes($shiftEnd);

                // Initialize duration variables
                $actualDuration = 0;
                $shift->login_activities = collect();

                if ($shift->timesheet && $shift->timesheet->clock_in && $shift->timesheet->clock_out) {
                    $checkIn = \Carbon\Carbon::parse($shift->timesheet->clock_in);
                    $checkOut = \Carbon\Carbon::parse($shift->timesheet->clock_out);

                    // Handle crossing midnight
                    if ($checkOut->lessThan($checkIn)) {
                        $checkOut->addDay();
                    }

                    $actualDuration = $checkIn->diffInMinutes($checkOut);
                } elseif ($shift->staff_id) {
                    $bufferStart = $shiftStart->copy()->subHours(2);
                    $bufferEnd = $shiftEnd->copy()->addHours(2);

                    $shift->login_activities = \App\LoginInActivity::where('user_id', $shift->staff_id)
                        ->whereBetween('check_in_time', [$bufferStart, $bufferEnd])
                        ->get();

                    if ($shift->login_activities->count() > 0) {
                        $firstCheckIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                        $lastCheckOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;

                        if ($lastCheckOut) {
                            $actualDuration = $firstCheckIn->diffInMinutes($lastCheckOut);
                        }
                    }
                }
                $shift->actual_duration_minutes = $actualDuration;
                $shift->variance_minutes = $actualDuration - $shift->scheduled_duration_minutes;

                // Calculate shift start/end for late/early comparison
                $shiftStartFull = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                $shiftEndFull = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);
                if ($shiftEndFull->lessThan($shiftStartFull)) {
                    $shiftEndFull->addDay();
                }

                $shift->is_late = false;
                $shift->late_minutes = 0;
                $shift->is_early = false;
                $shift->early_minutes = 0;

                if ($shift->login_activities->count() > 0) {
                    $firstCheckIn = \Carbon\Carbon::parse($shift->login_activities->min('check_in_time'));
                    $lastCheckOut = $shift->login_activities->max('check_out_time') ? \Carbon\Carbon::parse($shift->login_activities->max('check_out_time')) : null;

                    if ($firstCheckIn->greaterThan($shiftStartFull->copy()->addMinutes(5))) {
                        $shift->is_late = true;
                        $shift->late_minutes = $shiftStartFull->diffInMinutes($firstCheckIn);
                    }

                    if ($lastCheckOut && $lastCheckOut->lessThan($shiftEndFull->copy()->subMinutes(5))) {
                        $shift->is_early = true;
                        $shift->early_minutes = $lastCheckOut->diffInMinutes($shiftEndFull);
                    }
                }

                // Assign reconciliation status
                $status = strtolower($shift->status);
                if ($status == 'approved') {
                    $shift->reconciliation_status = 'Approved';
                } elseif ($status == 'rejected') {
                    $shift->reconciliation_status = 'Rejected';
                } elseif (empty($shift->staff_id)) {
                    $shift->reconciliation_status = 'Unscheduled';
                } else {
                    // Rule: variance > 60 mins -> Needs Adjustment, otherwise Matched
                    if (abs($shift->variance_minutes) <= 60) {
                        $shift->reconciliation_status = 'Matched';
                    } else {
                        $shift->reconciliation_status = 'Needs Adjustment';
                    }
                }

                return $shift;
            });

        // Calculate aggregate counts
        $manual_timesheets = \App\Models\Timesheet::whereNull('shift_id')
            ->where('home_id', $homeId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        $matchedCount = $shifts->where('reconciliation_status', 'Matched')->count();
        $needsAdjustmentCount = $shifts->where('reconciliation_status', 'Needs Adjustment')->count();
        $unscheduledCount = $shifts->where('reconciliation_status', 'Unscheduled')->count();
        $approvedCount = $shifts->where('reconciliation_status', 'Approved')->count() + $manual_timesheets->count();
        $rejectedCount = $shifts->where('reconciliation_status', 'Rejected')->count();

        $shift_options = $shifts->values()->map(function ($s) {
            return [
                'id' => $s->id,
                'staff_id' => (int)$s->staff_id,
                'date' => \Carbon\Carbon::parse($s->start_date)->format('D, M d'),
                'time' => $s->start_time . ' - ' . $s->end_time,
                'category' => $s->shiftCategory ? $s->shiftCategory->name : 'No Category'
            ];
        });

        return view('frontEnd/roster/payroll_finance/timesheetreconciliation', compact(
            'shifts',
            'matchedCount',
            'needsAdjustmentCount',
            'unscheduledCount',
            'approvedCount',
            'rejectedCount',
            'users',
            'shift_options',
            'categories',
            'manual_timesheets'
        ));
    }

    public function saveTimesheet(Request $request)
    {
        // Require either shift_id OR staff_id
        if (!$request->shift_id && !$request->staff_id) {
            return back()->with('error', 'Please select either a staff member or a shift.');
        }

        $data = [
            'staff_id'    => $request->staff_id,
            'category_id' => $request->category_id,
            'home_id'     => \Illuminate\Support\Facades\Auth::user()->home_id,
            'clock_in'    => $request->clock_in,
            'clock_out'   => $request->clock_out,
            'notes'       => $request->notes,
            'status'      => 'approved'
        ];

        if ($request->timesheet_id) {
            \App\Models\Timesheet::where('id', $request->timesheet_id)->update($data);
        } elseif ($request->shift_id) {
            $data['shift_id'] = $request->shift_id;

            // If shift_id is provided, ensure staff_id is set from the shift if missing
            $shift = \App\Models\ScheduledShift::find($request->shift_id);
            if ($shift) {
                if (!$request->staff_id) $data['staff_id'] = $shift->staff_id;
                if (!$request->category_id) $data['category_id'] = $shift->shift_category_id;
            }

            \App\Models\Timesheet::updateOrCreate(
                ['shift_id' => $request->shift_id],
                $data
            );

            // Also update the shift status to approved
            if ($shift) {
                $shift->status = 'approved';
                $shift->save();
            }
        } else {
            // Purely manual entry without shift association
            \App\Models\Timesheet::create($data);
        }

        return back()->with('success', 'Timesheet record saved successfully.');
    }

    public function approveShift(Request $request)
    {
        try {
            $shiftIds = $request->shift_id;

            if (empty($shiftIds)) {
                return response()->json(['success' => false, 'message' => 'No shifts provided.'], 400);
            }

            if (!is_array($shiftIds)) {
                $shiftIds = [$shiftIds];
            }

            $approvedCount = 0;
            foreach ($shiftIds as $shiftId) {
                $shift = \App\Models\ScheduledShift::find($shiftId);

                if (!$shift) continue;

                // Fetch actual clock times from activities
                $clockIn = null;
                $clockOut = null;

                if ($shift->staff_id) {
                    // Buffer times same as in timesheetreconciliation method
                    $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                    $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);
                    if ($shiftEnd->lessThan($shiftStart)) $shiftEnd->addDay();

                    $bufferStart = $shiftStart->copy()->subHours(2);
                    $bufferEnd = $shiftEnd->copy()->addHours(2);

                    $activities = \App\LoginInActivity::where('user_id', $shift->staff_id)
                        ->whereBetween('check_in_time', [$bufferStart, $bufferEnd])
                        ->get();

                    if ($activities->count() > 0) {
                        $clockIn = \Carbon\Carbon::parse($activities->min('check_in_time'))->format('H:i');
                        $maxOut = $activities->max('check_out_time');
                        $clockOut = $maxOut ? \Carbon\Carbon::parse($maxOut)->format('H:i') : null;
                    }
                }

                // If no clock out but we're approving, default to planned if actual is missing.
                $finalClockIn = $clockIn ?? $shift->start_time;
                $finalClockOut = $clockOut ?? $shift->end_time;

                $data = [
                    'staff_id'    => $shift->staff_id,
                    'category_id' => $shift->shift_category_id,
                    'home_id'     => $shift->home_id,
                    'clock_in'    => $finalClockIn,
                    'clock_out'   => $finalClockOut,
                    'status'      => 'approved',
                    'shift_id'    => $shift->id,
                    'notes'       => 'Automatically approved from reconciliation dashboard.'
                ];

                \App\Models\Timesheet::updateOrCreate(
                    ['shift_id' => $shift->id],
                    $data
                );

                $shift->status = 'approved';
                $shift->save();
                $approvedCount++;
            }

            if ($approvedCount === 0) {
                return response()->json(['success' => false, 'message' => 'No matching shifts found to approve.']);
            }

            return response()->json([
                'success' => true,
                'message' => $approvedCount > 1 ? "$approvedCount shifts approved successfully." : "Shift approved successfully."
            ]);
        } catch (\Exception $e) {
            \Log::error('Approve Shift Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
