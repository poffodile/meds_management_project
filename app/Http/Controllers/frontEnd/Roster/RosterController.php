<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyDepartment;

class RosterController extends Controller
{
    public function index(){
        $data['departments'] = CompanyDepartment::getActiveCompanyDepartment();
        return view('frontEnd.roster.index', $data);
    }

    public function dashboard(){
        return view('frontEnd.roster.dashboard');
    }  
}
