<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use App\Models\ScheduledShift;
use Illuminate\Http\Request;

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
        $userId = \Illuminate\Support\Facades\Auth::user()->id;
        $homeId = \Illuminate\Support\Facades\Auth::user()->home_id;

        $shifts = \App\Models\ScheduledShift::where('home_id', $homeId)
            ->where('staff_id', $userId)
            ->get()
            ->sortByDesc('start_date')
            ->map(function ($shift) use ($userId) {

                $shiftStart = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->start_time);
                $shiftEnd = \Carbon\Carbon::parse($shift->start_date . ' ' . $shift->end_time);

                // If it ends the next day (e.g. 22:00 to 06:00), add a day to end_time
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }

                $bufferStart = $shiftStart->copy()->subHours(2);
                $bufferEnd = $shiftEnd->copy()->addHours(2);

                $shift->login_activities = \App\LoginInActivity::where('user_id', $userId)
                    ->where('shift_id', $shift->id)
                    ->where(function ($query) use ($bufferStart, $bufferEnd) {
                        $query->whereBetween('check_in_time', [$bufferStart->toDateTimeString(), $bufferEnd->toDateTimeString()])
                            ->orWhereBetween('check_out_time', [$bufferStart->toDateTimeString(), $bufferEnd->toDateTimeString()]);
                    })
                    ->get();

                $shift->scheduled_duration_minutes = $shiftStart->diffInMinutes($shiftEnd);

                $actualDuration = 0;
                foreach ($shift->login_activities as $activity) {
                    if ($activity->check_in_time && $activity->check_out_time) {
                        $checkIn = \Carbon\Carbon::parse($activity->check_in_time);
                        $checkOut = \Carbon\Carbon::parse($activity->check_out_time);
                        $actualDuration += $checkIn->diffInMinutes($checkOut);
                    }
                }

                $shift->actual_duration_minutes = $actualDuration;
                $shift->variance_minutes = $actualDuration - $shift->scheduled_duration_minutes;

                return $shift;
            });
        // dd($shifts);
        return view('frontEnd/roster/payroll_finance/timesheetreconciliation', compact('shifts'));
    }
}
