<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManageDashboardController extends Controller
{
    public function index(){
        return view('frontEnd.roster.manage_dashboard.manage_dashboard');
    }
}
