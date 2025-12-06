<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScheduleShiftController extends Controller
{
    public function index(){
        return view('frontEnd.roster.schedule.schedule_shift');   
    }
}
