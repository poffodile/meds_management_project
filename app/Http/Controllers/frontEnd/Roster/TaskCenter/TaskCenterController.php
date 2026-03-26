<?php

namespace App\Http\Controllers\frontEnd\Roster\TaskCenter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskCenterController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.task_center.task_center');
    }
}
