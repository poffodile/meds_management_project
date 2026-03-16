<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class invoiceManagementController extends Controller
{
    public function index()
    {
        return view("frontEnd/roster/payroll_finance/invoice_management/index");
    }
}
