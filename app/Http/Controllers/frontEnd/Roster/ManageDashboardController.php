<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use App\Models\RosterDailyLog;
use App\Models\ScheduledShift;
use App\Models\Staff\StaffReportIncidents;
use App\ServiceUser;
use App\Staffleaves;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ManageDashboardController extends Controller
{
    public function index()
    {
        $todayShifts = ScheduledShift::homeId()->todayShifts()->count();
        $unfilledShifts = ScheduledShift::homeId()->todayShifts()->unfilledShifts()->count();
        $filledShifts = $todayShifts - $unfilledShifts;
        $fillRate = ($todayShifts > 0) ? round(($filledShifts / $todayShifts) * 100, 1) : 0;
        $incidentThisMonth = StaffReportIncidents::where('home_id', Auth::user()->home_id)->whereMonth('date_time', date('m'))
            ->whereYear('date_time', date('Y'))
            ->count();
        $unresolvedIncident = StaffReportIncidents::where('home_id', Auth::user()->home_id)->where('status', '!=', 4)
            ->count();
        $criticalIncident = StaffReportIncidents::where('home_id', Auth::user()->home_id)->where('status', '!=', 4)
            ->where('severity_id', 4)
            ->count();
        $subQueryDailyLogs = RosterDailyLog::with([
            'subCategorys:id,home_id,daily_cat_id',
            'subCategorys.dailyLogCategory:id,home_id,category',
        ])->where('home_id', Auth::user()->home_id)
            ->whereDate('date', date('Y-m-d'));

        $followUpDailyLogs = RosterDailyLog::where('home_id', Auth::user()->home_id)
            ->whereDate('date', date('Y-m-d'))
            ->whereDate('available_for_overtime', 1)
            ->count();
        $todayDailyLogsCount = $subQueryDailyLogs->count();
        $arr = [];
        $subQueryDailyLogs->latest()->limit(2)->get();
        foreach ($subQueryDailyLogs->latest()->limit(2)->get() as $key => $value) {
            $arr[] = [
                'visitor_name' => $value->visitor_name,
                'arrival_time' => date('H:i', strtotime($value->arrival_time)),
                'category' => $value->subCategorys->dailyLogCategory->category,
            ];
        }
        $now = Carbon::now();
        // return $now->toTimeString();
        $alertScheduledShift = ScheduledShift::homeId()
            ->with(['client:id,name', 'staff:id,name'])
            ->where(function ($q) use ($now) {
                $q->whereDate('start_date', '<', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('start_date', $now->toDateString()) // today
                            ->where('start_time', '<', $now->toTimeString()); // time bhi nikal gaya
                    });
            })
            ->whereIn('status', ['unfilled', 'assigned', 'in_progress'])
            ->latest()
            ->paginate(5);

        $pendingLeaveCount = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 0)->count();
        // return  $arr;
        $data['userCount'] = [
            'activeClients'  => ServiceUser::where('home_id', Auth::user()->home_id)->where('status', 1)->where('is_deleted', 0)->count(), //ServiceUser::getServiceUserByResidentialId(1),
            'activeCarers'   => User::getstaffByResidentialId(),
            'todayShifts'    => $todayShifts,
            'unfilledShifts' => $unfilledShifts,
            'fillRate'       => $fillRate,
            'incidentThisMonth' => $incidentThisMonth,
            'unresolvedIncident' => $unresolvedIncident,
            'criticalIncident' => $criticalIncident,
            'todayDailyLogs' => $todayDailyLogsCount,
            'followUpDailyLogs' => $followUpDailyLogs,
            'dailyLogData' => $arr,
            'pendingLeaveCount' => $pendingLeaveCount

        ];
        $data['alertScheduledShift'] = $alertScheduledShift;
        return view('frontEnd.roster.manage_dashboard.manage_dashboard', $data);
    }

    public function export()
    {
        $todayShifts = ScheduledShift::homeId()->todayShifts()->count();
        $unfilledShifts = ScheduledShift::homeId()->todayShifts()->unfilledShifts()->count();
        $filledShifts = $todayShifts - $unfilledShifts;
        $fillRate = ($todayShifts > 0) ? round(($filledShifts / $todayShifts) * 100, 1) : 0;

        $activeClients = ServiceUser::where('home_id', Auth::user()->home_id)->where('status', 1)->where('is_deleted', 0)->count();

        // Capacity matches the hardcoded 50 in the dashboard view
        $totalBeds = 50;
        $occupancyRate = ($totalBeds > 0) ? round(($activeClients / $totalBeds) * 100, 1) : 0;

        $incidentThisMonth = StaffReportIncidents::where('home_id', Auth::user()->home_id)->whereMonth('date_time', date('m'))
            ->whereYear('date_time', date('Y'))
            ->count();
        $unresolvedIncident = StaffReportIncidents::where('home_id', Auth::user()->home_id)->where('status', '!=', 4)
            ->count();
        $criticalIncident = StaffReportIncidents::where('home_id', Auth::user()->home_id)->where('status', '!=', 4)
            ->where('severity_id', 4)
            ->count();

        $pendingLeaveCount = Staffleaves::where('home_id', Auth::user()->home_id)->where('leave_status', 0)->count();

        // Using placeholders for training as they are currently hardcoded in the dashboard view
        $overdueTraining = 9;
        $expiringCertificates = 0;
        $completionRate = 0.0;

        $reportData = [
            'reportDate' => date('Y-m-d H:i'),
            'occupancy' => [
                'bedsOccupied' => $activeClients,
                'totalBeds' => $totalBeds,
                'percentage' => $occupancyRate
            ],
            'staffManagement' => [
                'todayShifts' => $todayShifts,
                'unfilledShifts' => $unfilledShifts,
                'fillRate' => $fillRate
            ],
            'training' => [
                'completionRate' => $completionRate,
                'expiring' => $expiringCertificates,
                'overdue' => $overdueTraining
            ],
            'incidents' => [
                'recent' => $incidentThisMonth,
                'unresolved' => $unresolvedIncident,
                'critical' => $criticalIncident
            ],
            'communication' => [
                'pendingLeave' => $pendingLeaveCount,
                'newFeedback' => 1,
                'criticalAlerts' => 1
            ]
        ];

        $pdf = Pdf::loadView('frontEnd.roster.manage_dashboard.export_pdf', $reportData);
        return $pdf->download('Manager-Dashboard-Report-' . date('Y-m-d-His') . '.pdf');
    }
}
