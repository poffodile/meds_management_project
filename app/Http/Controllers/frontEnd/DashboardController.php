<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use illuminate\Http\Request;
use App\ModifyRequest, App\ServiceUserIncidentReport, App\Admin, App\Notification, App\User;
use Illuminate\Support\Facades\Mail;
use Auth;

class DashboardController extends Controller
{
	  
	public function dashboard(){
		$page = 'dashboard';
		//$noti = Notification::dashboardEventNotification();
		$guide_tag = 'sys_mngmt';

		$accessRight = User::where('id', Auth::user()->id)->where('is_deleted', 0)->whereRaw("FIND_IN_SET(?, access_rights)", [319])->exists();
		// dd($accessRight);
		$rights = User::where('id', Auth::user()->id)->where('is_deleted', 0)->first()->access_rights;
        $access_rights = explode(',', $rights);
		// echo "<pre>";print_r($access_rights);die;
		return view('frontEnd.dashboard',compact('page','guide_tag', 'accessRight','access_rights'));
	}

	//when a user is not authorized
	public function send_modify_request(Request $request){
		// print_r($request->input()) ;
		// die;
		// dd($request);
		$data = $request->input();
		if(!empty($data)){
			// echo '<pre>'; print_r($data); die;

			$modif_request = new ModifyRequest;
			$modif_request->action  = $data['action'];
			$modif_request->user_id = Auth::user()->id;
			$modif_request->home_id = Auth::user()->home_id;
			$modif_request->content = $data['content'];
			$modif_request->reason  = $data['reason'];
			
			if($modif_request->save()) {

			    //sending mail to home admin about request
				$manager_name = Auth::user()->user_name;
				$action       = $data['action'];
				$content      = $data['content'];
				$reason       = $data['reason'];
				$admin        = Admin::where('id', Auth::user()->home_id)->first();
				$admin_name   = $admin->name;
				$email        = $admin->email;
				$company_name = PROJECT_NAME;
				//$subject 	  = 'Manager Modification Request Mail';
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
					Mail::send('emails.user_modify_request_mail',['name'=>$manager_name, 'action'=>$action, 'content'=>$content, 'admin_name'=>$admin_name, 'reason'=>$reason], function($message) use ($email,$company_name)
					{
						$message->to($email,$company_name)->subject('Manager Modification Request Mail');
					});
				}
				return redirect()->back()->with('success','Request submitted successfully to Home Admin.');
			} else{
				return redirect()->back()->with('error',COMMON_ERROR);				
			}
		} else {
			return redirect()->back()->with('error', "Error in request submit");	
		}
	}
	public function add_incident_report(Request $request) {
		$data = $request->input(); 
		// echo "<pre>";
		// print_r($data); 
		// die;
		if(!empty($data)) {

			if(isset($data['formdata'])){
                $formdata = json_encode($data['formdata']);
            } else{
                $formdata = '';
            }

			$report_request = new ServiceUserIncidentReport;
			$report_request->service_user_id = $data['yp_id'];
			$report_request->home_id         = Auth::user()->home_id;
			$report_request->title           = $data['report_title'];
			$report_request->date 			 = date('Y-m-d',strtotime($data['report_date']));
			$report_request->formdata        = $formdata;
			if($report_request->save()) {

				$notification                             = new Notification;
                $notification->service_user_id            = $data['yp_id'];
                $notification->event_id                   = $report_request->id;
                //$notification->event_type      = 'SU_HR';
                $notification->notification_event_type_id = '10';
                $notification->event_action               = 'ADD';    
                $notification->home_id                    = Auth::user()->home_id;
                $notification->user_id                    = Auth::user()->id;                  
                $notification->save();

				return redirect()->back()->with('success','Incident report submitted successfully.');
			} else{
				return redirect()->back()->with('error',COMMON_ERROR);				
			}
		}
	}

    //changing Design layout for normal or dyslexia users
	public function change_layout($design_layout_id) {

		// echo $design_layout_id; die;

		if(Auth::check()) {

			$user_id = Auth::id();

			$update  = User::where('id',$user_id)->update(['design_layout'=>$design_layout_id]);
			return redirect()->back();
		} else {
			return redirect()->back();
		}
	}

	
}
