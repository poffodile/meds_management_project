<?php

namespace App\Http\Controllers\frontEnd\Roster\CRMDashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CRMDashboardController extends Controller
{
    public function crm_dashboard()
    {
        return view('frontEnd.roster.crm_dashboard.crm_dashboard');
    }
    public function crm_dashboard_details()
    {
        return view('frontEnd.roster.crm_dashboard.crm_dashboard_details');
    }
}
