<?php

namespace App\Http\Controllers\frontEnd\Domiciliary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VisitScheduleController extends Controller
{
    public function index()
    {
        return view('frontEnd.domiciliary.visit_schedule');
    }
}
