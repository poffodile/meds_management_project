<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncidentManagementController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.incident_management.incident');
    }
}
