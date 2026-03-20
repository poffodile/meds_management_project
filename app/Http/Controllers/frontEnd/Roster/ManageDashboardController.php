<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ServiceUser;
use Illuminate\Support\Facades\Auth;

class ManageDashboardController extends Controller
{
    public function index()
    {
        $todayShifts = \App\Models\ScheduledShift::homeId()->todayShifts()->count();
        $unfilledShifts = \App\Models\ScheduledShift::homeId()->todayShifts()->unfilledShifts()->count();
        $filledShifts = $todayShifts - $unfilledShifts;
        $fillRate = ($todayShifts > 0) ? round(($filledShifts / $todayShifts) * 100, 1) : 0;

        $data['userCount'] = [
            'activeClients'  => ServiceUser::getServiceUserByResidentialId(1),
            'activeCarers'   => User::getstaffByResidentialId(),
            'todayShifts'    => $todayShifts,
            'unfilledShifts' => $unfilledShifts,
            'fillRate'       => $fillRate
        ];
        return view('frontEnd.roster.manage_dashboard.manage_dashboard', $data);
    }
}
