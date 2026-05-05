<?php

namespace App\Http\Controllers\frontEnd\Domiciliary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceUserFeedbackController extends Controller
{
    public function index()
    {
        return view('frontEnd.domiciliary.service_user_feedback');
    }
}
