<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanyDepartment, App\User, App\ServiceUser, App\Notification;

class RosterController extends Controller
{
    public function index(){
        $data['departments'] = CompanyDepartment::getActiveCompanyDepartment();
        return view('frontEnd.roster.index', $data);
    }

    public function dashboard(){
        $data['serviceUserCount'] = ServiceUser::getServiceUserByResidentialId(1);
        $data['userCount'] = User::getstaffByResidentialId(1);
        return view('frontEnd.roster.dashboard', $data);
    }  
}
