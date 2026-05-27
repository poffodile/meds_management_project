<?php

namespace App\Http\Controllers\frontend\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        return view('frontEnd/roster/staff/audit_log/index');
    }
}
