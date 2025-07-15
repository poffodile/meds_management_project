<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use App\Models\Construction_tax_rate;
use Illuminate\Support\Facades\Session;

class InvoiceController extends Controller
{
    public function getActiveTaxRate(){
        $data = Construction_tax_rate::getAllTax_rate(Session::get('scitsAdminSession')->home_id, "Active");

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }
}
