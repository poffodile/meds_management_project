<?php

namespace App\Http\Controllers\frontend\roster\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\ServiceUser;
use Auth,DB;

class ClientController extends Controller
{
    public function index()
    {
        $home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id = $ex_home_ids[0];
        $query = ServiceUser::select('id','home_id','earning_scheme_label_id','name','user_name','phone_no','date_of_birth','child_type','room_type','current_location','status','is_deleted')
        ->where(['home_id'=>$home_id,'is_deleted'=>0]);
        $data['child'] = $query->get();
        $data['active_child_count']= (clone $query)->where('status',1)->get();
        $data['inactive_child_count'] = (clone $query)->where('status',0)->get();
        // echo "<pre>";print_r($data['active_child_count']);die;
        return view('frontEnd.roster.client.client',$data);
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
