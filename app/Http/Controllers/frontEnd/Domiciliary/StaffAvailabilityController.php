<?php

namespace App\Http\Controllers\frontEnd\Domiciliary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffAvailabilityController extends Controller
{
    public function index()
    {
        return view('frontEnd.domiciliary.staff_availability');
    }
}
