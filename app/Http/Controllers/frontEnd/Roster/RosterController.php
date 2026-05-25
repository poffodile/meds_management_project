<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Staffleaves;
use App\Models\CompanyDepartment;
use App\User;
use App\ServiceUser;
use App\Models\ScheduledShift;
use App\Models\ClientAlert;
use Carbon\Carbon;

class RosterController extends Controller
{
    public function index()
    {
        $home_id = explode(',', Auth::user()->home_id)[0];
        $data['departments'] = CompanyDepartment::getActiveCompanyDepartment();
        $data['serviceUserCount'] = ServiceUser::getServiceUserByResidentialId(1);
        $data['userCount'] = User::getstaffByResidentialId();
        $data['pendingLeaveCount'] = Staffleaves::where('home_id', $home_id)->where('leave_status', 0)->count();

        $data['scheduled_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->orderBy('start_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });

        $data['today_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->whereDate('start_date', date('Y-m-d'))
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });

        $data['today_shifts_count'] = ScheduledShift::where('home_id', Auth::user()->home_id)
            ->whereDate('start_date', date('Y-m-d'))
            ->count();

        $data['unfilled_shifts_count'] = ScheduledShift::where('home_id', Auth::user()->home_id)
            ->whereNull('staff_id')
            ->count();
            
            
        $now = Carbon::now();
		$home_id = Auth::user()->home_id;

        // Automatically sync missed shifts to ClientAlert table for persistent tracking
        // (Removing the need to pass $missed_shifts separately to view)
        $missed_shifts_raw = ScheduledShift::where('home_id', $home_id)
			->where(function($query) use ($now) {
				$query->where('status', 'no_show')
					->orWhere(function($q) use ($now) {
						$q->where('status', 'assigned')
							->whereRaw("CONCAT(start_date, ' ', start_time) < ?", [$now->copy()->subMinutes(30)->toDateTimeString()]);
					});
			})
			->get();

          foreach($missed_shifts_raw as $shift) {
            // Check if alert already exists for this shift
            $exists = ClientAlert::where('shift_id', $shift->id)->exists();
            if (!$exists && !empty($shift->service_user_id)) {
                ClientAlert::create([
                    'home_id' => $home_id,
                    'client_id' => $shift->service_user_id,
                    'shift_id' => $shift->id,
                    'user_id' => Auth::user()->id,
                    'alert_type_id' => 1, 
                    'severity' => 'critical',
                    'alert_title' => 'MISSED SHIFT',
                    'description' => 'A scheduled shift for ' . $shift->start_date . ' at ' . date('H:i', strtotime($shift->start_time)) . ' was missed.',
                    'status' => 1
                ]);
            }
        }

		// 2. Unfilled shifts (starting within 24 hours) - These are dynamic
		$data['unfilled_shifts_alerts'] = ScheduledShift::where('home_id', $home_id)
			->where('status', 'unfilled')
			->whereRaw("CONCAT(start_date, ' ', start_time) <= ?", [$now->copy()->addHours(24)->toDateTimeString()])
			->with('client:id,name')
			->get();

		// 3. Combined Critical Alerts (Custom + Automated Missed)
		$data['critical_alerts'] = ClientAlert::where('severity', 'critical')
            ->where('home_id', $home_id)
			->where(function($query) use ($now) {
				$query->whereNull('expiry_date')
					->orWhere('expiry_date', '>=', $now->toDateString());
			})
            ->whereNull('resolve_date')
			->with(['client:id,name', 'alert_types'])
            ->orderBy('created_at', 'desc')
			->get();

        $data['missed_shifts'] = []; // Cleared to avoid duplicates in view

        return view('frontEnd.roster.index', $data);
    }

    public function dashboard()
    {
        $data['serviceUserCount'] = ServiceUser::getServiceUserByResidentialId(1);
        $data['userCount'] = User::getstaffByResidentialId();

        $data['scheduled_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->orderBy('start_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });

        $data['today_shifts'] = ScheduledShift::with(['client', 'staff'])
            ->where('home_id', Auth::user()->home_id)
            ->whereDate('start_date', date('Y-m-d'))
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($shift) {
                $shift->client_name = $shift->client->name ?? 'Unknown Client';
                $shift->staff_name = $shift->staff->name ?? 'Unassigned';
                return $shift;
            });

        $data['today_shifts_count'] = ScheduledShift::where('home_id', Auth::user()->home_id)
            ->whereDate('start_date', date('Y-m-d'))
            ->count();

        $data['unfilled_shifts_count'] = ScheduledShift::where('home_id', Auth::user()->home_id)
            ->whereNull('staff_id')
            ->count();

        return view('frontEnd.roster.dashboard', $data);
    }
}
