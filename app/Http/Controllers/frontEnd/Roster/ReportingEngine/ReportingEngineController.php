<?php

namespace App\Http\Controllers\frontEnd\Roster\ReportingEngine;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportingEngineController extends Controller
{
    public function reporting_engine()
    {
        return view('frontEnd.roster.reporting_engine.reporting_engine');
    }
}
