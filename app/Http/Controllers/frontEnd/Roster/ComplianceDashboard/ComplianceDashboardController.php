<?php

namespace App\Http\Controllers\frontEnd\Roster\ComplianceDashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplianceDashboardController extends Controller
{
    public function compliance_dashboard()
    {
        return view('frontEnd.roster.compliance_dashboard.compliance_dashboard');
    }
}
