<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB, Auth;
use Carbon, Session;
use App\CareTeam, App\HomeLabel, App\ServiceUser, App\Notification;

class ServiceUserManagementController extends Controller
{
	  
	public function service_users() {
		
		$home_ids  = Auth::user()->home_id;
        $ex_home_id=explode(',',$home_ids);
        $home_id=$ex_home_id[0];
       
		$patients = DB::table('service_user')->where('home_id',$home_id)->where('is_deleted','0')->get();
        //echo "<pre>"; print_r($patients); die;
        $labels   = HomeLabel::getLabels($home_id);

        // dd($labels);

        // $daily_records_options = DB::table('daily_record')
        //             ->where('home_id',$home_id)
        //             ->where('status','1')
        //             ->orderBy('id','desc')
        //             ->get();

        //living skill option
        $living_skill_options = DB::table('living_skill')
                                    ->where('home_id',$home_id)
                                    ->where('status','1')
                                    ->where('is_deleted','0')
                                    ->orderBy('id','desc')
                                    ->get();

        $education_record_options = DB::table('education_record')
                                    ->select('id','description')
                                    ->where('home_id', $home_id)
                                    ->where('status','1')
                                    ->where('is_deleted','0')
                                    ->orderBy('id','desc')
                                    ->get();
        //echo '<pre>'; print_r($education_record_options); die;
        $mfc_options = DB::table('mfc')
                        ->select('id','description')
                        ->where('home_id', $home_id)
                        ->where('status','1')
                        ->where('is_deleted','0')
                        ->orderBy('id','desc')
                        ->get();
        
        $daily_score   = DB::table('daily_record_score')->get();

        //service_users list for bmp-rmp
        $service_users = ServiceUser::select('id','name')
                            ->where('home_id',$home_id)
                            ->where('status','1')
                            ->where('is_deleted','0')
                            ->get()
                            ->toArray();

        // $notifications = Notification::getsuNotification('','','',10);
         // echo "<pre>"; print_r($notifications); die;
         $guide_tag = 'su_mngmt';

		return view('frontEnd.serviceUserManagement.index',compact('patients','labels','living_skill_options','mfc_options','service_users','daily_score','guide_tag'));
	}
	
	public function calendar() {
		return view('frontEnd.serviceUserManagement.calendar');
	}

    public function notif_response(Request $request){
        $data = $request->input();
     
        //echo '<pre>'; print_r($data); die;
        $noti_data = [];
        $noti_data['event_id']   = $data['event_id'];
        $noti_data['event_type'] = $data['event_type'];
        $noti_data['su_id']      = $data['su_id'];
        $noti_data['back_path']  = $data['back_path'];

        Session::put('noti_data', $noti_data);
        
        if( ($noti_data['event_type'] == 'RISK') || 
            ($noti_data['event_type'] == 'NEED_ASSIT') || 
            ($noti_data['event_type'] == 'REQ_CALLBACK') || 
            ($noti_data['event_type'] == 'IN_DANGER') ||
            ($noti_data['event_type'] == 'MONEY_REQ') 
        ){
            return redirect('service/user-profile/'.$data['su_id']);
        } else if($noti_data['event_type'] == 'LOC_ALERT'){
            return redirect('service/location-history/'.$data['su_id']);
        } 
    }


}
