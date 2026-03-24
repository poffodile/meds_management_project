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

                // Assign reconciliation status
                $status = strtolower($shift->status);
                if ($status == 'approved') {
                    $shift->reconciliation_status = 'Approved';
                } elseif ($status == 'rejected') {
                    $shift->reconciliation_status = 'Rejected';
                } elseif (empty($shift->staff_id)) {
                    $shift->reconciliation_status = 'Unscheduled';
                } else {
                    if ($shift->actual_duration_minutes > 0 && $shift->variance_minutes == 0) {
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
