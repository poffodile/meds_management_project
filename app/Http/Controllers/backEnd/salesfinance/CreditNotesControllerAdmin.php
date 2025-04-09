<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditNotesControllerAdmin extends Controller
{
    public function credit_notes_form(){
        $data['task']="Add";
        $data['page']="Purchase Order";
       return view('backEnd.salesFinance.credit_notes.credit_notes_form',$data);
    }
}
