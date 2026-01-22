<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class ManageDashboardController extends Controller
{
    public function index(){
        $data['userCount'] = User::getstaffByResidentialId();
        return view('frontEnd.roster.manage_dashboard.manage_dashboard', $data);
    }
}
