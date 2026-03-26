<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use App\Models\ScheduledShift;
use Illuminate\Http\Request;
use App\User;

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

        // Fetch all shifts for the home to provide a comprehensive reconciliation view
        $shifts = \App\Models\ScheduledShift::where('home_id', $homeId)
            ->with('staff')
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

                if ($shift->staff_id) {
                    $bufferStart = $shiftStart->copy()->subHours(2);
                    $bufferEnd = $shiftEnd->copy()->addHours(2);

                    $shift->login_activities = \App\LoginInActivity::where('user_id', $shift->staff_id)
                        ->where('shift_id', $shift->id)
                        ->where(function ($query) use ($bufferStart, $bufferEnd) {
                            $query->whereBetween('check_in_time', [$bufferStart->toDateTimeString(), $bufferEnd->toDateTimeString()])
                                ->orWhereBetween('check_out_time', [$bufferStart->toDateTimeString(), $bufferEnd->toDateTimeString()]);
                        })
                        ->get();

                    foreach ($shift->login_activities as $activity) {
                        if ($activity->check_in_time && $activity->check_out_time) {
                            $checkIn = \Carbon\Carbon::parse($activity->check_in_time);
                            $checkOut = \Carbon\Carbon::parse($activity->check_out_time);
                            $actualDuration += $checkIn->diffInMinutes($checkOut);
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
        $matchedCount = $shifts->where('reconciliation_status', 'Matched')->count();
        $needsAdjustmentCount = $shifts->where('reconciliation_status', 'Needs Adjustment')->count();
        $unscheduledCount = $shifts->where('reconciliation_status', 'Unscheduled')->count();
        $approvedCount = $shifts->where('reconciliation_status', 'Approved')->count();
        $rejectedCount = $shifts->where('reconciliation_status', 'Rejected')->count();

        return view('frontEnd/roster/payroll_finance/timesheetreconciliation', compact(
            'shifts',
            'matchedCount',
            'needsAdjustmentCount',
            'unscheduledCount',
            'approvedCount',
            'rejectedCount',
            'users'
        ));
    }
}
