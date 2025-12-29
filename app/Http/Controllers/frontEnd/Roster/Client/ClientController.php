<?php

namespace App\Http\Controllers\frontend\roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index()
    {
        return view('frontEnd.roster.client.client');
    }

    public function client_details($client_id)
    {

        // if (!$carer_id) {
        //     abort(400, 'User ID is required.');
        // }

        // $data['staffDetails'] = $this->staffService->getStaffDetails($carer_id);
        // dd($data['staffDetails']);
        return view('frontEnd.roster.client.client_details');
    }
   
}
