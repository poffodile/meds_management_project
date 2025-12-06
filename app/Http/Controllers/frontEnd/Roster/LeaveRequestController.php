<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(){
        return view('frontEnd.roster.leave.leave_request');
    }
}
