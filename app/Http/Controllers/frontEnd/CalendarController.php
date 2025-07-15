<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User, App\ServiceUser, App\ServiceUserHealthRecord, App\Calendar, App\PlanBuilder, App\ServiceUserCalendarNote, App\ServiceUserDailyRecord, App\ServiceUserCalendarEvent, App\ServiceUserEarningIncentive, App\ServiceUserLivingSkill, App\ServiceUserEducationRecord, App\HomeLabel, App\ServiceUserLogBook, App\LogBook, App\MandatoryLeave;
use Hash;
use DB;

class CalendarController extends Controller
{
	public function index($service_user_id = null) {
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$staff_members  =   User::where('is_deleted','0')
									->where('home_id', $home_id)
									->get();
		
        $service_user = ServiceUser::select('home_id','name')->where('id',$service_user_id)->first();
		
		if(!empty($service_user)){
			// $home_id = Auth::user()->home_id;	
	        
	        if($service_user->home_id != $home_id){
	            return redirect('/')->with('error',UNAUTHORIZE_ERR); 
	        }

	        $page_title = trim($service_user->name)."'s Event Calendar";
			$event_type = "";
			//Health Records
			$health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id','su_health_record.title')
											->join('service_user as su', 'su.id','su_health_record.service_user_id')
											->where('su_health_record.service_user_id', $service_user_id)
											->where('su_health_record.is_deleted', '0')
											->where('su.home_id', $home_id)
											->orderBy('health_record_id','desc')
											->get()
											->toArray();
		
			foreach ($health_records as $key => $health_record) {
				// check if this health_record is booked in calendar
				$event_id 	= $health_record['health_record_id'];
				$event_type = '1';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$health_records[$key] = array_merge($health_records[$key],$booking_response);

			}
			
			//Daily Records
			$daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id','su_daily_record.daily_record_id','dr.description' )
													->join('daily_record as dr', 'dr.id','su_daily_record.daily_record_id')
													->join('service_user as su', 'su.id','su_daily_record.service_user_id')
													->where('su_daily_record.service_user_id', $service_user_id)
													->where('su_daily_record.is_deleted', '0')
													->where('su_daily_record.added_to_calendar', '1')
													->where('su.home_id',$home_id)
													->orderBy('su_daily_record.id','desc')
													->get()
													->toArray();

			foreach ($daily_records as $key => $daily_record) {
				//check if this health record is booked in calendar
				$event_id = $daily_record['su_daily_record_id'];
				$event_type = '2';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$daily_records[$key] = array_merge($daily_records[$key],$booking_response);
			}	

			//Living Skills
			$living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id','su_living_skill.living_skill_id','ls.description')
													->join('living_skill as ls','ls.id','su_living_skill.living_skill_id')
													->join('service_user as su', 'su.id','su_living_skill.service_user_id')
													->where('su_living_skill.service_user_id', $service_user_id)
													->where('su_living_skill.is_deleted','0')
													->where('su_living_skill.added_to_calendar', '1')
													->where('su.home_id', $home_id)
													->orderBy('su_living_skill.id','desc')
													->get()
													->toArray();

			foreach ($living_skills as $key => $living_skill) {
				//check if this living skill is booked in calendar
				$event_id = $living_skill['su_living_skill_id'];
				$event_type = '9';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$living_skills[$key] = array_merge($living_skills[$key],$booking_response);
			}	
			//Living Skills End

			//Education Records
			$education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id','su_education_record.education_record_id','er.description')
													->join('education_record as er','er.id','su_education_record.education_record_id')
													->join('service_user as su', 'su.id','su_education_record.service_user_id')
													->where('su_education_record.service_user_id', $service_user_id)
													->where('su_education_record.is_deleted','0')
													->where('su_education_record.added_to_calendar', '1')
													->where('su.home_id', $home_id)
													->orderBy('su_education_record.id','desc')
													->get()
													->toArray();

			foreach ($education_records as $key => $education_record) {
				//check if this education record is booked in calendar
				$event_id = $education_record['su_education_record_id'];
				$event_type = '10';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$education_records[$key] = array_merge($education_records[$key],$booking_response);
			}	
			//Education Records End
			
			//earningScheme incentives
			$su_incentives   = ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost','incentive.name')
										->join('incentive','incentive.id','su_earning_incentive.incentive_id')
										->where('su_earning_incentive.service_user_id', $service_user_id)
										->where('incentive.is_deleted','0')
										// ->where('su.home_id', $home_id)
										->orderBy('su_earning_incentive.id','desc')
										->get()
										->toArray();

			foreach ($su_incentives as $key => $su_incentive) {
				//check if this incentive is booked in calendar
				$event_id   = $su_incentive['su_ern_inc_id'];
				$event_type = '3';

				$booking_response    = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$su_incentives[$key] = array_merge($su_incentives[$key],$booking_response);
			}

			//calendar added events
			$event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id','su_calendar_event.title','su.id as su_id')
										->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
										->where('su_calendar_event.service_user_id', $service_user_id)
										->where('su.home_id',$home_id)
										->where('su_calendar_event.is_deleted','0')
										->orderBy('su_calendar_event.id','desc')
										->get()
										->toArray();

			foreach ($event_records as $key => $event_record) {

				//check if this event_record is booked in calendar
				$event_id = $event_record['su_calendar_event_id'];
				$event_type = '4';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$event_records[$key] = array_merge($event_records[$key],$booking_response);
			}

			//calendar added notes
			$calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id','su_calendar_note.title as note_title','su_calendar_note.note as title')
									->join('service_user as su', 'su.id','su_calendar_note.service_user_id')
									->where('su_calendar_note.service_user_id', $service_user_id)
									->where('su.home_id', $home_id)
									->where('su_calendar_note.is_deleted', '0')
									->orderBy('su_calendar_note.id','desc')
									->get()
									->toArray();
		
			foreach($calender_notes as $key => $calender_note){
				
				// check if this note is booked in calendar
				$event_id 	= $calender_note['id'];
				$event_type = '5';
				
				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$calender_notes[$key] = array_merge($calender_notes[$key],$booking_response);
			}

			//Child log records
			$su_log_books = ServiceUserLogBook::select('su_log_book.id as su_log_book_id','lb.title as log_title','su_log_book.service_user_id','lb.id as log_book_id')
										->join('log_book as lb','lb.id','su_log_book.log_book_id')
										->where(['lb.added_to_calendar'=>'1', 'lb.is_deleted'=>'0'])
										->where('su_log_book.service_user_id', $service_user_id)

										->get()
										->toArray();
			// echo "<pre>"; print_r($su_log_books); die;
			foreach ($su_log_books as $key => $su_log_book) {
				
				//check if this log record is booked in calendar
				$event_id   = $su_log_book['log_book_id'];
				$event_type = '11';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$su_log_books[$key] = array_merge($su_log_books[$key],$booking_response);
			}

			//data for add entry form
			$appointment_plans = PlanBuilder::select('id','home_id','title')
												->where('home_id',$home_id)
												->where('is_deleted','0')
												->get();
			$service_users = ServiceUser::select('id','home_id','name')->where('home_id',$home_id)->where('status','1')->get();
			$labels = HomeLabel::getLabels($home_id);
			$guide_tag='su_cal';


			// Mandatory Leave
			$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'su.id as su_id')
			->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
			->where('mandatory_leaves.service_user_id', $service_user_id)
			->where('su.home_id', $home_id)
			->where('mandatory_leaves.is_deleted', '0')
			->orderBy('mandatory_leaves.id', 'desc')
			->get()
			->toArray();

			// echo "<pre>"; print_r($mandatory_leave_records); die;

			foreach ($mandatory_leave_records as $key => $mandatory_leave_record) {
				
				//check if this log record is booked in calendar
				$event_id   = $mandatory_leave_record['mandatory_leaves_id'];
				$event_type = '12';

				$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
				$mandatory_leave_records[$key] = array_merge($mandatory_leave_records[$key],$booking_response);
			}
			
			// End Mandatory Leave
			// echo "<pre>"; print_r($mandatory_leave_records); die;
													
			return view('frontEnd.serviceUserManagement.calendar', compact('event_type','service_user_id','page_title','health_records','daily_records','su_incentives','event_records','appointment_plans','service_users','calender_notes','living_skills','education_records','labels','su_log_books', 'mandatory_leave_records', 'guide_tag','staff_members'));
		} else {
			return view('frontEnd.error_404');
		}
	}

	public function add_event(Request $request){

		if($request->isMethod('post')) {
			$data = $request->input();

			// $home_id    = Auth::user()->home_id;
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			$home_id=$ex_home_ids[0];
			$su_home_id = ServiceUser::where('id',$data['service_user_id'])->value('home_id');
            if($su_home_id != $home_id){
                //return redirect('/')->with('error',UNAUTHORIZE_ERR); 
                $result['response'] = false;
                return $result;
            }

			$calendar 					      = new Calendar;
			$calendar->service_user_id 	      = $data['service_user_id'];
			$calendar->calendar_event_type_id = $data['event_type'];
			$calendar->event_id 		      = $data['event_id'];
			$calendar->event_date 		      = date('Y-m-d',strtotime($data['event_date']));
			$calendar->home_id 			      = $home_id;
			if($calendar->save()){
				$result['response'] = true;
				$result['calendar_id'] = $calendar->id;
			} else{
				$result['response'] = false;
				$result['calendar_id'] = 0;
			}
			return $result;
		}		

	}

	public function move_event(Request $request){

		$data = $request->all();
		$event_calendar_id = $data['event_calendar_id'];

		$calendar = Calendar::find($event_calendar_id);
		if(!empty($calendar)) {
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			$home_id=$ex_home_ids[0];
			$su_home_id = ServiceUser::where('id',$calendar->service_user_id)->value('home_id');
            if($su_home_id != $home_id){
                echo 'false'; die;
            }
			
		    $calendar->event_date	= date('Y-m-d',strtotime($data['event_date']));

		    if($calendar->save()){
		    	echo 'true';
		    }
		    else{
		    	echo 'false';
		    }
		    die;
		}
	}

	public function delete_daily_record(Request $request, $event_id){
		
		if($request->record_type == 'health_record'){
			$del = ServiceUserHealthRecord::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'daily_record'){
			$del = ServiceUserDailyRecord::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'living_skill'){
			$del = ServiceUserLivingSkill::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'incentive_record'){
			$del = ServiceUserEarningIncentive::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'event_record'){
			$del = ServiceUserCalendarEvent::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'calender_notes'){
			$del = ServiceUserCalendarNote::where('id',$event_id)
										->update(['is_deleted'=>'1']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}elseif($request->record_type == 'log_book'){
			$del = LogBook::where('id',$event_id)
						->update(['added_to_calendar'=>'0']);
			if($del){
				echo "1";
			}else{
			 	echo "2";
			}
		}
	}

}
