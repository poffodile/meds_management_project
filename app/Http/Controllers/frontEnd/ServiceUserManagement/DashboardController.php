<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request){
        $data['service_user_id'] = $request->service_user_id;
        return view('frontEnd.serviceUserManagement.dashboard', $data);
    }
}
