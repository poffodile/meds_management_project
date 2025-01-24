<?php

namespace App\Http\Controllers\frontEnd\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditNotesController extends Controller
{
    public function credit_notes(Request $request){
        echo "<pre>";print_r($request->all());die;
    }
}
