<?php
namespace App\Http\Controllers\frontEnd\GeneralAdmin;
use App\Http\Controllers\frontEnd\GeneralAdminController;
use Illuminate\Http\Request;
use App\AgendaMeeting, App\User;
use Illuminate\Support\Facades\Mail;
use Auth;
class AgendaMeetingController extends GeneralAdminController
{	

	public function index() {
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$meeting_record = AgendaMeeting::where('home_id', $home_id)->where('is_deleted','0')->orderBy('id','desc');

		$pagination = '';

        if(isset($_GET['search'])) {
            if(!empty($_GET['search'])) {
                $meeting_record = $meeting_record->where('title','like','%'.$_GET['search'].'%')->get();
            }
        } else {
            $meeting_record = $meeting_record->paginate(10);
            if($meeting_record->links() != '') {
                $pagination .= '<div class="m-l-15 position-botm">'; //position-botm
                $pagination .= $meeting_record->links();
                $pagination .= '</div>'; 
            }
        }

        foreach ($meeting_record as $key => $value) {
        	echo 	'<div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 meeting_record_delete">
                        <div class="form-group col-md-11 col-sm-11 col-xs-12 r-p-0 pull-center">
                            <div class="input-group popovr">
                                <input name="" class="form-control cus-control" disabled="" value="'.ucfirst($value->title).'" type="text"><input name="" value="" disabled="disabled" class="" type="hidden">
                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                    <i class="fa fa-cog"></i>
                                    <div class="pop-notifbox">
                                        <ul class="pop-notification" type="none">
                                        	<li> <a href="#" class="view_agenda_record" agenda_meeting_id="'.$value->id.'"> <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                        	<li> <a href="#" class="edit_agenda_record" agenda_meeting_id="'.$value->id.'"> <span> <i class="fa fa-pencil"></i> </span> Edit </a> </li>
                                        	<li> <a href="delete" class="delete_agenda_record" agenda_meeting_id="'.$value->id.'"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a>  </li>
                                        </ul>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>';
        }
        echo $pagination;
		
	}

	public function add(Request $r){

		$data = $r->input();
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$attended = "";
		$not_attended = "";
		if(isset($data['attended_user_ids'])){
			$attended = implode(',', $data['attended_user_ids']);			
		}
		

		if(isset($data['not_attended_user_ids'])) {
			$not_attended = implode(",", $data['not_attended_user_ids']);
		}
		
		$meeting 		     		= new AgendaMeeting;
		$meeting->home_id    		= $home_id;  
		$meeting->title 			= $data['title'];
		$meeting->staff_present 	= $attended;
		$meeting->staff_not_present = $not_attended;
		$meeting->notes 			= $data['notes'];
		if($meeting->save()) {
			
			if(isset($data['not_attended_user_ids'])) {
				//sending mail to not present staff in meeting
				$title = $data['title'];
				$notes = $data['notes'];	
				foreach ($data['not_attended_user_ids'] as $value) {
					
					$user = User::select('email','user_name','name')->where('id',$value)->first();
					$name      = $user->name;
					$user_name = $user->user_name;
					$email     = $user->email;

					Mail::send('emails.agenda_meeting_mail',['name'=>$name,'user_name'=>$user_name, 'email'=>$email,'title'=>$title,'notes'=>$notes], function($message) use($email)
					{
						$message->to($email)->subject('Staff Agenda Meeting.');
					});

				}
			}
			echo true;
		} else {
			echo false;
		}
		die;
		
	} 

	public function delete($meeting_id) {

		$meeting_record = AgendaMeeting::find($meeting_id); {
			if(!empty($meeting_record)) {
				$home_ids = Auth::user()->home_id;
				$ex_home_ids = explode(',', $home_ids);
				$home_id=$ex_home_ids[0];
				$res = AgendaMeeting::where('id', $meeting_id)->where('home_id', $home_id)->update(['is_deleted' => '1']);
                echo $res;    
			}
			die;
		}
	}

	public function view($meeting_id) {
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$meeting_record = AgendaMeeting::where('agenda_meeting.id', $meeting_id)
										->where('home_id', $home_id)
										->where('is_deleted','0')
										->first();
		if(!empty($meeting_record)) {
			$result['response']          = true;
			$result['meeting_id'] 		 = $meeting_record->id;
			$result['title']    		 = $meeting_record->title;
			// $result['staff_present'] 	 = $meeting_record->staff_present;
			// $result['staff_not_present'] = $meeting_record->staff_not_present;
			$result['staff_present'] 	 = explode(',', $meeting_record->staff_present);

			$result['staff_not_present'] = explode(',', $meeting_record->staff_not_present);
			$result['notes']  			 = $meeting_record->notes;
		} else {
			$result['response'] = false;
		}
		return $result;
	}
	

	public function edit(Request $request) {
		
		$data = $request->all();
		$meeting_id = $data['agenda_meeting_id'];
		if($request->isMethod('post')) {
	
			$edit_record = AgendaMeeting::find($meeting_id); {

				$edit_record->title 			= $data['e_title'];
				$edit_record->staff_present     = implode(',', $data['e_attended_user_ids']);
				$edit_record->staff_not_present = implode(',', $data['e_not_attended_user_ids']);
				$edit_record->notes 			= $data['e_notes'];
				if($edit_record->save()) {
					echo true;
				} else {
					echo false;
				}
			} 
		} else {
				echo UNAUTHORIZE_ERR;
		} 
	}

}