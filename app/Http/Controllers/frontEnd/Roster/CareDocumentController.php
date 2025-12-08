<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CareDocumentController extends Controller
{
    public function  index(){
        return view('frontEnd.roster.care_document.care_document');
    }
}
