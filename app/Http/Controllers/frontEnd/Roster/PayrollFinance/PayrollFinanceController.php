<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollFinanceController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.payroll_finance.index');
    }
    public function payrollprocessing()
    {
        return view('frontEnd/roster/payroll_finance/payroll_processing');
    }
    public function timesheetreconciliation()
    {
        return view('frontEnd/roster/payroll_finance/timesheetreconciliation');
    }
}
