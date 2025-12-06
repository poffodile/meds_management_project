<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffTaskController extends Controller
{
    public function index(){
        return view('frontEnd.roster.staff.staff_task');
    }
}
