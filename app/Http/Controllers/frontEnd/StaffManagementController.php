<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB, Auth;

class StaffManagementController extends Controller
{
	  
	public function staff_member(){
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		// $home_id = Auth::user()->home_id;
		// $staff = DB::table('user')->where('home_id',$home_id)->where('is_deleted', '0')->where('id','!=',Auth::user()->id)->get();
		$staff = DB::table('user')->where('home_id',$home_id)->where('is_deleted', '0')->get();
		$guide_tag = 'staff_mngmt';

		// echo '<pre>'; print_r($staff); die;
		return view('frontEnd.staffManagement.index',compact('staff','guide_tag'));
	}
	
}
