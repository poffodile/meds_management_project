<?php

namespace App\Http\Controllers\backEnd\salesfinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditNotesControllerAdmin extends Controller
{
    public function credit_notes_form(){
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;
        if($home_id){
            $data['task']="Add";
            $data['page']="Purchase Order";
            return view('backEnd.salesFinance.credit_notes.credit_notes_form',$data);
        }else{
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }
    }
}
