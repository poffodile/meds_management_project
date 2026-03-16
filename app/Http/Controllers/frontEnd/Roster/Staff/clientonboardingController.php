<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class clientonboardingController extends Controller
{
    public function index()
    {
        return view('frontEnd/roster/client/client_onboarding/client_onboarding');
    }
}
