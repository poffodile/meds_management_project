<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessagingCenterController extends Controller
{
    public function index(){
        return view('frontEnd.roster.messaging.messaging_center');
    }
}
