<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyDepartment;
use App\User;
use App\ServiceUser;
use App\Models\ScheduledShift;

class RosterController extends Controller
{
    public function index()
    {
        $data['departments'] = CompanyDepartment::getActiveCompanyDepartment();
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
