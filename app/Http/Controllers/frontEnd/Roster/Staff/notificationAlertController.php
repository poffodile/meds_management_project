<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class notificationAlertController extends Controller
{
    public function index()
    {
        return view('/frontEnd/roster/notification/system_alert');
    }
}
