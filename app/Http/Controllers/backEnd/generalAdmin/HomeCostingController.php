<?php

namespace App\Http\Controllers\backEnd\generalAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeCostingController extends Controller
{
    public function index(){
        $page='home_costing';
        return view('backEnd.generalAdmin.home_costing.home_costing',compact('page'));
    }
    public function add(Request $request){
        $page='home_costing';
        return view('backEnd.generalAdmin.home_costing.home_costing_form',compact('page'));
    }
}
