<?php

namespace App\Http\Controllers\frontEnd\SystemManagement;

use App\Http\Controllers\frontEnd\SystemManagementController;
use Illuminate\Http\Request;
use Auth;
use App\User, App\ServiceUser, App\ServiceUserHealthRecord, App\Calendar, App\PlanBuilder, App\ServiceUserCalendarNote, App\ServiceUserDailyRecord, App\ServiceUserCalendarEvent, App\ServiceUserEarningIncentive, App\StaffAnnualLeave, App\StaffSickLeave, App\StaffTaskAllocation, App\ServiceUserLivingSkill, App\ServiceUserEducationRecord, App\LogBook, App\HomeLabel, App\MandatoryLeave;
use Hash;
use DB;

class CalendarController extends SystemManagementController
{
	public function index(Request $request)
	{
		$home_id       = 	Auth::user()->home_id;
		$user_type     = $request->user_type;
		$user_id       = $request->user_id;
		$selected_user_id = $request->user_id;

		if ((!empty($user_type)) && (!empty($user_id))) {

			if ($user_type == 'SU') {

				$staff = '';

				if ($user_id == 'All') {
					$service_users  = 	ServiceUser::select('id', 'name')
						->where('status', '1')
						->where('home_id', $home_id)
						->get();
					//->toArray();

					foreach ($service_users as $su_key => $service_user) {

						// $service_user_id = $service_user['id'];
						$service_user_id = $service_user->id;

						// heath record list
						$health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id', 'su_health_record.title')
							->join('service_user as su', 'su.id', 'su_health_record.service_user_id')
							->where('su_health_record.service_user_id', $service_user_id)
							->where('su.home_id', $home_id)
							->where('su_health_record.is_deleted', '0')
							->orderBy('health_record_id', 'desc')
							->get()
							->toArray();

						foreach ($health_records as $key => $health_record) {
							// check if this health_record is booked in calendar
							$event_id 	= $health_record['health_record_id'];
							$event_type = '1';

							$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$health_records[$key] = array_merge($health_records[$key], $booking_response);
						}
						$service_users[$su_key]['health_records'] = $health_records;

						// daily record list
						$daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id', 'su_daily_record.daily_record_id', 'dr.description')
							->join('daily_record as dr', 'dr.id', 'su_daily_record.daily_record_id')
							->join('service_user as su', 'su.id', 'su_daily_record.service_user_id')
							->where('su_daily_record.service_user_id', $service_user_id)
							->where('su.home_id', $home_id)
							->where('su_daily_record.is_deleted', '0')
							->where('su_daily_record.added_to_calendar', '1')
							->orderBy('su_daily_record.id', 'desc')
							->get()
							->toArray();

						foreach ($daily_records as $key => $daily_record) {
							//check if this daily record is booked in calendar
							$event_id   = $daily_record['su_daily_record_id'];
							$event_type = '2';

							$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$daily_records[$key] = array_merge($daily_records[$key], $booking_response);
						}
						$service_users[$su_key]['daily_records'] = $daily_records;

						//Living Skills
						$living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id', 'su_living_skill.living_skill_id', 'ls.description')
							->join('living_skill as ls', 'ls.id', 'su_living_skill.living_skill_id')
							->join('service_user as su', 'su.id', 'su_living_skill.service_user_id')
							->where('su_living_skill.service_user_id', $service_user_id)
							->where('su_living_skill.is_deleted', '0')
							->where('su_living_skill.added_to_calendar', '1')
							->where('su.home_id', $home_id)
							->orderBy('su_living_skill.id', 'desc')
							->get()
							->toArray();

						foreach ($living_skills as $key => $living_skill) {
							//check if this living skill is booked in calendar
							$event_id   = $living_skill['su_living_skill_id'];
							$event_type = '9';

							$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$living_skills[$key] = array_merge($living_skills[$key], $booking_response);
						}
						$service_users[$su_key]['living_skills'] = $living_skills;
						//Living Skills End

						//Education Records
						$education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id', 'su_education_record.education_record_id', 'er.description')
							->join('education_record as er', 'er.id', 'su_education_record.education_record_id')
							->join('service_user as su', 'su.id', 'su_education_record.service_user_id')
							->where('su_education_record.service_user_id', $service_user_id)
							->where('su_education_record.is_deleted', '0')
							->where('su_education_record.added_to_calendar', '1')
							->where('su.home_id', $home_id)
							->orderBy('su_education_record.id', 'desc')
							->get()
							->toArray();
						//echo "<pre>"; print_r($education_records); die;
						foreach ($education_records as $key => $education_record) {
							//check if this living skill is booked in calendar
							$event_id   = $education_record['su_education_record_id'];
							$event_type = '10';

							$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$education_records[$key] = array_merge($education_records[$key], $booking_response);
						}
						$service_users[$su_key]['education_records'] = $education_records;
						//echo "<pre>"; print_r($service_users[$su_key]['education_records']); die;
						//Education Records End

						//earning category incentives
						$earn_incentives =  ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost', 'incentive.name')
							->join('incentive', 'incentive.id', 'su_earning_incentive.incentive_id')
							->where('su_earning_incentive.service_user_id', $service_user_id)
							->where('incentive.is_deleted', '0')
							// ->where('su.home_id', $home_id)
							->orderBy('su_earning_incentive.id', 'desc')
							->get()
							->toArray();

						foreach ($earn_incentives as $key => $earn_incentive) {
							//check if this incentive is booked in calendar
							$event_id   = $earn_incentive['su_ern_inc_id'];
							$event_type = '3';

							$booking_response 	   = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$earn_incentives[$key] = array_merge($earn_incentives[$key], $booking_response);
						}
						$service_users[$su_key]['earn_incentives'] = $earn_incentives;


						// events list 
						$event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id', 'su_calendar_event.title', 'su.id as su_id')
							->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
							->where('su_calendar_event.service_user_id', $service_user_id)
							->where('su.home_id', $home_id)
							->where('su_calendar_event.is_deleted', '0')
							->orderBy('su_calendar_event.id', 'desc')
							->get()
							->toArray();

						foreach ($event_records as $key => $event_record) {
							//check if this event is booked in calendar
							$event_id   = $event_record['su_calendar_event_id'];
							$event_type = '4';

							$booking_response    = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$event_records[$key] = array_merge($event_records[$key], $booking_response);
						}
						$service_users[$su_key]['event_records'] = $event_records;

						// calendar notes list
						$calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id', 'su_calendar_note.title as note_title', 'su_calendar_note.note as title')
							->join('service_user as su', 'su.id', 'su_calendar_note.service_user_id')
							->where('su_calendar_note.service_user_id', $service_user_id)
							->where('su.home_id', $home_id)
							->where('su_calendar_note.is_deleted', '0')
							->orderBy('su_calendar_note.id', 'desc')
							->get()
							->toArray();


						foreach ($calender_notes as $key => $calender_note) {
							//check if this calendar note is booked in calendar
							$event_id   = $calender_note['id'];
							$event_type = '5';

							$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
							$calender_notes[$key] = array_merge($calender_notes[$key], $booking_response);
						}
						$service_users[$su_key]['calender_notes'] = $calender_notes;

						// Mandatory Leave
						$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
							->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
							->where('mandatory_leaves.user_id', Auth::user()->id)
							->where('su.home_id', $home_id)
							->where('mandatory_leaves.is_deleted', '0')
							->orderBy('mandatory_leaves.id', 'desc')
							->get()
							->toArray();

						// dd($mandatory_leave_records);

						$ml_record = array();
						for ($i = 0; $i < count($mandatory_leave_records); $i++) {
							$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
							$su_user   = $mandatory_leave_records[$i]['su_id'];
							$ml_type = '12';

							$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
							$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
						}
						$service_users[$su_key]['ml_record'] = $ml_record;
						// End Mandatory Leave
					}
					// echo "<pre>"; print_r($service_users); die;
				} else {
					$service_users  = 	ServiceUser::select('id', 'name')
						->where('status', '1')
						->where('home_id', $home_id)
						->where('id', $user_id)
						->get();
					//->toArray();
					// echo "<pre>"; print_r($service_users); die;
					foreach ($service_users as $su_key => $service_user) {
						$service_user_id = $service_user->id;

						// heath record list
							$health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id', 'su_health_record.title')
								->join('service_user as su', 'su.id', 'su_health_record.service_user_id')
								->where('su_health_record.service_user_id', $service_user_id)
								->where('su.home_id', $home_id)
								->where('su_health_record.is_deleted', '0')
								->orderBy('health_record_id', 'desc')
								->get()
								->toArray();

							foreach ($health_records as $key => $health_record) {
								// check if this health_record is booked in calendar
								$event_id 	= $health_record['health_record_id'];
								$event_type = '1';

								$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$health_records[$key] = array_merge($health_records[$key], $booking_response);
							}
							$service_users[$su_key]['health_records'] = $health_records;

							// daily record list
							$daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id', 'su_daily_record.daily_record_id', 'dr.description')
								->join('daily_record as dr', 'dr.id', 'su_daily_record.daily_record_id')
								->join('service_user as su', 'su.id', 'su_daily_record.service_user_id')
								->where('su_daily_record.service_user_id', $service_user_id)
								->where('su.home_id', $home_id)
								->where('su_daily_record.is_deleted', '0')
								->where('su_daily_record.added_to_calendar', '1')
								->orderBy('su_daily_record.id', 'desc')
								->get()
								->toArray();

							foreach ($daily_records as $key => $daily_record) {
								//check if this daily record is booked in calendar
								$event_id   = $daily_record['su_daily_record_id'];
								$event_type = '2';

								$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$daily_records[$key] = array_merge($daily_records[$key], $booking_response);
							}
							$service_users[$su_key]['daily_records'] = $daily_records;

							//Living Skills
							$living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id', 'su_living_skill.living_skill_id', 'ls.description')
								->join('living_skill as ls', 'ls.id', 'su_living_skill.living_skill_id')
								->join('service_user as su', 'su.id', 'su_living_skill.service_user_id')
								->where('su_living_skill.service_user_id', $service_user_id)
								->where('su_living_skill.is_deleted', '0')
								->where('su_living_skill.added_to_calendar', '1')
								->where('su.home_id', $home_id)
								->orderBy('su_living_skill.id', 'desc')
								->get()
								->toArray();

							foreach ($living_skills as $key => $living_skill) {
								//check if this living skill is booked in calendar
								$event_id   = $living_skill['su_living_skill_id'];
								$event_type = '9';

								$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$living_skills[$key] = array_merge($living_skills[$key], $booking_response);
							}
							$service_users[$su_key]['living_skills'] = $living_skills;
							//Living Skills End

							//Education Records
							$education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id', 'su_education_record.education_record_id', 'er.description')
								->join('education_record as er', 'er.id', 'su_education_record.education_record_id')
								->join('service_user as su', 'su.id', 'su_education_record.service_user_id')
								->where('su_education_record.service_user_id', $service_user_id)
								->where('su_education_record.is_deleted', '0')
								->where('su_education_record.added_to_calendar', '1')
								->where('su.home_id', $home_id)
								->orderBy('su_education_record.id', 'desc')
								->get()
								->toArray();
							//echo "<pre>"; print_r($education_records); die;
							foreach ($education_records as $key => $education_record) {
								//check if this living skill is booked in calendar
								$event_id   = $education_record['su_education_record_id'];
								$event_type = '10';

								$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$education_records[$key] = array_merge($education_records[$key], $booking_response);
							}
							$service_users[$su_key]['education_records'] = $education_records;
							//echo "<pre>"; print_r($service_users[$su_key]['education_records']); die;
							//Education Records End

							//earning category incentives
							$earn_incentives =  ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost', 'incentive.name')
								->join('incentive', 'incentive.id', 'su_earning_incentive.incentive_id')
								->where('su_earning_incentive.service_user_id', $service_user_id)
								->where('incentive.is_deleted', '0')
								// ->where('su.home_id', $home_id)
								->orderBy('su_earning_incentive.id', 'desc')
								->get()
								->toArray();

							foreach ($earn_incentives as $key => $earn_incentive) {
								//check if this incentive is booked in calendar
								$event_id   = $earn_incentive['su_ern_inc_id'];
								$event_type = '3';

								$booking_response 	  = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$earn_incentives[$key] = array_merge($earn_incentives[$key], $booking_response);
							}
							$service_users[$su_key]['earn_incentives'] = $earn_incentives;


							// events list 
							$event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id', 'su_calendar_event.title', 'su.id as su_id')
								->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
								->where('su_calendar_event.service_user_id', $service_user_id)
								->where('su.home_id', $home_id)
								->where('su_calendar_event.is_deleted', '0')
								->orderBy('su_calendar_event.id', 'desc')
								->get()
								->toArray();

							foreach ($event_records as $key => $event_record) {
								//check if this event is booked in calendar
								$event_id   = $event_record['su_calendar_event_id'];
								$event_type = '4';

								$booking_response    = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$event_records[$key] = array_merge($event_records[$key], $booking_response);
							}
							$service_users[$su_key]['event_records'] = $event_records;

							// calendar notes list
							$calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id', 'su_calendar_note.title as note_title', 'su_calendar_note.note as title')
								->join('service_user as su', 'su.id', 'su_calendar_note.service_user_id')
								->where('su_calendar_note.service_user_id', $service_user_id)
								->where('su.home_id', $home_id)
								->where('su_calendar_note.is_deleted', '0')
								->orderBy('su_calendar_note.id', 'desc')
								->get()
								->toArray();

							foreach ($calender_notes as $key => $calender_note) {
								//check if this calendar note is booked in calendar
								$event_id   = $calender_note['id'];
								$event_type = '5';

								$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
								$calender_notes[$key] = array_merge($calender_notes[$key], $booking_response);
							}
							$service_users[$su_key]['calender_notes'] = $calender_notes;

							// Mandatory Leave
							$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
							->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
							->where('mandatory_leaves.user_id', Auth::user()->id)
							->where('su.home_id', $home_id)
							->where('mandatory_leaves.is_deleted', '0')
							->orderBy('mandatory_leaves.id', 'desc')
							->get()
							->toArray();

							$ml_record = array();
							for ($i = 0; $i < count($mandatory_leave_records); $i++) {
								$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
								$su_user   = $mandatory_leave_records[$i]['su_id'];
								$ml_type = '12';

								$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
								$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
							}
							$service_users[$su_key]['ml_record'] = $ml_record;
							// End Mandatory Leave
					}

					// echo "<pre>"; print_r($service_users); die;

				}
			} else if ($user_type == 'STAFF') {

				$service_users = '';

				if ($user_id == 'All') {
				
					//Staff Annual Leave
					$staff_annual_leaves = StaffAnnualLeave::select('staff_annual_leave.id', 'staff_annual_leave.title as annual_title', 'staff_annual_leave.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_annual_leave.staff_member_id')
						->where('staff_annual_leave.is_deleted', '0')
						->where('staff_annual_leave.home_id', $home_id)
						->orderBy('staff_annual_leave.id', 'desc')
						->get()
						->toArray();

					foreach ($staff_annual_leaves as $key => $staff_annual_leave) {
						// check if this annual leave is booked in calendar
						$event_id 	= $staff_annual_leave['id'];
						$event_type = '6';
						$user_id    = $staff_annual_leave['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_annual_leaves[$key] = array_merge($staff_annual_leaves[$key], $booking_response);
					}
					$staff['annual_leaves'] = $staff_annual_leaves;
					//Staff Annual Leave End

					//Staff Sick Leave
					$staff_sick_leaves = StaffSickLeave::select('staff_sick_leave.id', 'staff_sick_leave.title as sick_title', 'staff_sick_leave.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_sick_leave.staff_member_id')
						->where('staff_sick_leave.is_deleted', '0')
						->where('staff_sick_leave.home_id', $home_id)
						->orderBy('staff_sick_leave.id', 'desc')
						->get()
						->toArray();
					foreach ($staff_sick_leaves as $key => $staff_sick_leave) {
						// check if this sick leave is booked in calendar
						$event_id   = $staff_sick_leave['id'];
						$event_type = '7';
						$user_id    = $staff_sick_leave['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_sick_leaves[$key] = array_merge($staff_sick_leaves[$key], $booking_response);
					}
					$staff['sick_leaves'] = $staff_sick_leaves;
					//echo "<pre>"; print_r($staff['sick_leaves']); die;
					//Staff Sick Leave End

					//Staff Task Allocation End
					$staff_tasks = StaffTaskAllocation::select('staff_task_allocation.id', 'staff_task_allocation.title as task_title', 'staff_task_allocation.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_task_allocation.staff_member_id')
						->where('staff_task_allocation.is_deleted', '0')
						->where('staff_task_allocation.status', '1')
						->where('staff_task_allocation.home_id', $home_id)
						->orderBy('staff_task_allocation.id', 'desc')
						->get()
						->toArray();

					foreach ($staff_tasks as $key => $staff_task) {
						// check if this sick leave is booked in calendar
						$event_id   = $staff_task['id'];
						$event_type = '8';
						$user_id    = $staff_task['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_tasks[$key] = array_merge($staff_tasks[$key], $booking_response);
					}
					$staff['task_allocs'] = $staff_tasks;
					//echo "<pre>"; print_r($staff['task_allocs']); die;
					//Staff Task Allocation End
					// echo "<pre>"; print_r($staff); die;

					// // Mandatory Leave
					$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
						->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
						->where('mandatory_leaves.user_id', Auth::user()->id)
						->where('su.home_id', $home_id)
						->where('mandatory_leaves.is_deleted', '0')
						->orderBy('mandatory_leaves.id', 'desc')
						->get()
						->toArray();

					$ml_record = array();
					for ($i = 0; $i < count($mandatory_leave_records); $i++) {
						$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
						$su_user   = $mandatory_leave_records[$i]['su_id'];
						$ml_type = '12';

						$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
						$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
					}
					$staff['ml_record'] = $ml_record;
					// End Mandatory Leave
				} else {
				
					//Staff Annual Leave
					$staff_annual_leaves = StaffAnnualLeave::select('staff_annual_leave.id', 'staff_annual_leave.title as annual_title', 'staff_annual_leave.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_annual_leave.staff_member_id')
						->where('staff_annual_leave.is_deleted', '0')
						->where('staff_annual_leave.home_id', $home_id)
						->where('staff_annual_leave.staff_member_id', $user_id)
						->orderBy('staff_annual_leave.id', 'desc')
						->get()
						->toArray();

					foreach ($staff_annual_leaves as $key => $staff_annual_leave) {
						// check if this annual leave is booked in calendar
						$event_id 	= $staff_annual_leave['id'];
						$event_type = '6';
						$user_id    = $staff_annual_leave['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_annual_leaves[$key] = array_merge($staff_annual_leaves[$key], $booking_response);
					}
					$staff['annual_leaves'] = $staff_annual_leaves;
					//Staff Annual Leave End

					//Staff Sick Leave
					$staff_sick_leaves = StaffSickLeave::select('staff_sick_leave.id', 'staff_sick_leave.title as sick_title', 'staff_sick_leave.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_sick_leave.staff_member_id')
						->where('staff_sick_leave.is_deleted', '0')
						->where('staff_sick_leave.home_id', $home_id)
						->where('staff_sick_leave.staff_member_id', $user_id)
						->orderBy('staff_sick_leave.id', 'desc')
						->get()
						->toArray();
					foreach ($staff_sick_leaves as $key => $staff_sick_leave) {
						// check if this sick leave is booked in calendar
						$event_id   = $staff_sick_leave['id'];
						$event_type = '7';
						$user_id    = $staff_sick_leave['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_sick_leaves[$key] = array_merge($staff_sick_leaves[$key], $booking_response);
					}
					$staff['sick_leaves'] = $staff_sick_leaves;
					//echo "<pre>"; print_r($staff['sick_leaves']); die;
					//Staff Sick Leave End

					//Staff Task Allocation End
					$staff_tasks = StaffTaskAllocation::select('staff_task_allocation.id', 'staff_task_allocation.title as task_title', 'staff_task_allocation.staff_member_id', 'user.name as staff_name')
						->join('user', 'user.id', 'staff_task_allocation.staff_member_id')
						->where('staff_task_allocation.is_deleted', '0')
						->where('staff_task_allocation.status', '1')
						->where('staff_task_allocation.home_id', $home_id)
						->where('staff_task_allocation.staff_member_id', $user_id)
						->orderBy('staff_task_allocation.id', 'desc')
						->get()
						->toArray();

					foreach ($staff_tasks as $key => $staff_task) {
						// check if this sick leave is booked in calendar
						$event_id   = $staff_task['id'];
						$event_type = '8';
						$user_id    = $staff_task['staff_member_id'];

						$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
						$staff_tasks[$key] = array_merge($staff_tasks[$key], $booking_response);
					}
					$staff['task_allocs'] = $staff_tasks;
					//echo "<pre>"; print_r($staff['task_allocs']); die;
					//Staff Task Allocation End

					// // Mandatory Leave
					$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
						->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
						->where('mandatory_leaves.user_id', Auth::user()->id)
						->where('su.home_id', $home_id)
						->where('mandatory_leaves.is_deleted', '0')
						->orderBy('mandatory_leaves.id', 'desc')
						->get()
						->toArray();

					$ml_record = array();
					for ($i = 0; $i < count($mandatory_leave_records); $i++) {
						$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
						$su_user   = $mandatory_leave_records[$i]['su_id'];
						$ml_type = '12';

						$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
						$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
					}
					$staff['ml_record'] = $ml_record;
					// End Mandatory Leave

				}
			} else {
				$service_users = '';
				$staff         = '';
			}
		} else {
			$service_users   = 	ServiceUser::select('id', 'name')
				->where('status', '1')
				->where('home_id', $home_id)
				->get();
			
			foreach ($service_users as $su_key => $service_user) {
				$service_user_id = $service_user->id;

				// heath record list
				$health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id', 'su_health_record.title')
					->join('service_user as su', 'su.id', 'su_health_record.service_user_id')
					->where('su_health_record.service_user_id', $service_user_id)
					->where('su.home_id', $home_id)
					->where('su_health_record.is_deleted', '0')
					->orderBy('health_record_id', 'desc')
					->get()
					->toArray();

				foreach ($health_records as $key => $health_record) {
					// check if this health_record is booked in calendar
					$event_id 	= $health_record['health_record_id'];
					$event_type = '1';

					$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$health_records[$key] = array_merge($health_records[$key], $booking_response);
				}
				$service_users[$su_key]['health_records'] = $health_records;

				// daily record list
				$daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id', 'su_daily_record.daily_record_id', 'dr.description')
					->join('daily_record as dr', 'dr.id', 'su_daily_record.daily_record_id')
					->join('service_user as su', 'su.id', 'su_daily_record.service_user_id')
					->where('su_daily_record.service_user_id', $service_user_id)
					->where('su.home_id', $home_id)
					->where('su_daily_record.is_deleted', '0')
					->where('su_daily_record.added_to_calendar', '1')
					->orderBy('su_daily_record.id', 'desc')
					->get()
					->toArray();

				foreach ($daily_records as $key => $daily_record) {
					//check if this daily record is booked in calendar
					$event_id   = $daily_record['su_daily_record_id'];
					$event_type = '2';

					$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$daily_records[$key] = array_merge($daily_records[$key], $booking_response);
				}
				$service_users[$su_key]['daily_records'] = $daily_records;

				//Living Skills
				$living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id', 'su_living_skill.living_skill_id', 'ls.description')
					->join('living_skill as ls', 'ls.id', 'su_living_skill.living_skill_id')
					->join('service_user as su', 'su.id', 'su_living_skill.service_user_id')
					->where('su_living_skill.service_user_id', $service_user_id)
					->where('su_living_skill.is_deleted', '0')
					->where('su_living_skill.added_to_calendar', '1')
					->where('su.home_id', $home_id)
					->orderBy('su_living_skill.id', 'desc')
					->get()
					->toArray();

				foreach ($living_skills as $key => $living_skill) {
					//check if this living skill is booked in calendar
					$event_id   = $living_skill['su_living_skill_id'];
					$event_type = '9';

					$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$living_skills[$key] = array_merge($living_skills[$key], $booking_response);
				}
				$service_users[$su_key]['living_skills'] = $living_skills;
				//Living Skills End

				//Education Records
				$education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id', 'su_education_record.education_record_id', 'er.description')
					->join('education_record as er', 'er.id', 'su_education_record.education_record_id')
					->join('service_user as su', 'su.id', 'su_education_record.service_user_id')
					->where('su_education_record.service_user_id', $service_user_id)
					->where('su_education_record.is_deleted', '0')
					->where('su_education_record.added_to_calendar', '1')
					->where('su.home_id', $home_id)
					->orderBy('su_education_record.id', 'desc')
					->get()
					->toArray();
				//echo "<pre>"; print_r($education_records); die;
				foreach ($education_records as $key => $education_record) {
					//check if this living skill is booked in calendar
					$event_id   = $education_record['su_education_record_id'];
					$event_type = '10';

					$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$education_records[$key] = array_merge($education_records[$key], $booking_response);
				}
				$service_users[$su_key]['education_records'] = $education_records;
				//echo "<pre>"; print_r($service_users[$su_key]['education_records']); die;
				//Education Records End

				//earning category incentives
				$earn_incentives =  ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost', 'incentive.name')
					->join('incentive', 'incentive.id', 'su_earning_incentive.incentive_id')
					->where('su_earning_incentive.service_user_id', $service_user_id)
					->where('incentive.is_deleted', '0')
					// ->where('su.home_id', $home_id)
					->orderBy('su_earning_incentive.id', 'desc')
					->get()
					->toArray();

				foreach ($earn_incentives as $key => $earn_incentive) {
					//check if this incentive is booked in calendar
					$event_id   = $earn_incentive['su_ern_inc_id'];
					$event_type = '3';

					$booking_response 	   = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$earn_incentives[$key] = array_merge($earn_incentives[$key], $booking_response);
				}
				$service_users[$su_key]['earn_incentives'] = $earn_incentives;


				// events list 
				$event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id', 'su_calendar_event.title', 'su.id as su_id')
					->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
					->where('su_calendar_event.service_user_id', $service_user_id)
					->where('su.home_id', $home_id)
					->where('su_calendar_event.is_deleted', '0')
					->orderBy('su_calendar_event.id', 'desc')
					->get()
					->toArray();

				foreach ($event_records as $key => $event_record) {
					//check if this event is booked in calendar
					$event_id   = $event_record['su_calendar_event_id'];
					$event_type = '4';

					$booking_response    = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$event_records[$key] = array_merge($event_records[$key], $booking_response);
				}
				$service_users[$su_key]['event_records'] = $event_records;

				// calendar notes list
				$calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id', 'su_calendar_note.title as note_title', 'su_calendar_note.note as title')
					->join('service_user as su', 'su.id', 'su_calendar_note.service_user_id')
					->where('su_calendar_note.service_user_id', $service_user_id)
					->where('su.home_id', $home_id)
					->where('su_calendar_note.is_deleted', '0')
					->orderBy('su_calendar_note.id', 'desc')
					->get()
					->toArray();


				foreach ($calender_notes as $key => $calender_note) {
					//check if this calendar note is booked in calendar
					$event_id   = $calender_note['id'];
					$event_type = '5';

					$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
					$calender_notes[$key] = array_merge($calender_notes[$key], $booking_response);
				}
				$service_users[$su_key]['calender_notes'] = $calender_notes;


				// Mandatory Leave
				$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
				->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
				->where('mandatory_leaves.user_id', Auth::user()->id)
				->where('su.home_id', $home_id)
				->where('mandatory_leaves.is_deleted', '0')
				->orderBy('mandatory_leaves.id', 'desc')
				->get()
				->toArray();

				$ml_record = array();
				for ($i = 0; $i < count($mandatory_leave_records); $i++) {
					$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
					$su_user   = $mandatory_leave_records[$i]['su_id'];
					$ml_type = '12';

					$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
					$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
				}
				$service_users[$su_key]['ml_record'] = $ml_record;
				// End Mandatory Leave
			}

			// echo "<pre>"; print_r($service_users); die;

			//Staff Annual Leave
			$staff_annual_leaves = StaffAnnualLeave::select('staff_annual_leave.id', 'staff_annual_leave.title as annual_title', 'staff_annual_leave.staff_member_id', 'user.name as staff_name')
				->join('user', 'user.id', 'staff_annual_leave.staff_member_id')
				->where('staff_annual_leave.is_deleted', '0')
				->where('staff_annual_leave.home_id', $home_id)
				->orderBy('staff_annual_leave.id', 'desc')
				->get()
				->toArray();

			foreach ($staff_annual_leaves as $key => $staff_annual_leave) {
				// check if this annual leave is booked in calendar
				$event_id 	= $staff_annual_leave['id'];
				$event_type = '6';
				$user_id    = $staff_annual_leave['staff_member_id'];

				$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
				$staff_annual_leaves[$key] = array_merge($staff_annual_leaves[$key], $booking_response);
			}
			$staff['annual_leaves'] = $staff_annual_leaves;
			//Staff Annual Leave End

			//Staff Sick Leave
			$staff_sick_leaves = StaffSickLeave::select('staff_sick_leave.id', 'staff_sick_leave.title as sick_title', 'staff_sick_leave.staff_member_id', 'user.name as staff_name')
				->join('user', 'user.id', 'staff_sick_leave.staff_member_id')
				->where('staff_sick_leave.is_deleted', '0')
				->where('staff_sick_leave.home_id', $home_id)
				->orderBy('staff_sick_leave.id', 'desc')
				->get()
				->toArray();
			foreach ($staff_sick_leaves as $key => $staff_sick_leave) {
				// check if this sick leave is booked in calendar
				$event_id   = $staff_sick_leave['id'];
				$event_type = '7';
				$user_id    = $staff_sick_leave['staff_member_id'];

				$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
				$staff_sick_leaves[$key] = array_merge($staff_sick_leaves[$key], $booking_response);
			}
			$staff['sick_leaves'] = $staff_sick_leaves;
			//echo "<pre>"; print_r($staff['sick_leaves']); die;
			//Staff Sick Leave End

			//Staff Task Allocation End
			$staff_tasks = StaffTaskAllocation::select('staff_task_allocation.id', 'staff_task_allocation.title as task_title', 'staff_task_allocation.staff_member_id', 'user.name as staff_name')
				->join('user', 'user.id', 'staff_task_allocation.staff_member_id')
				->where('staff_task_allocation.is_deleted', '0')
				->where('staff_task_allocation.status', '1')
				->where('staff_task_allocation.home_id', $home_id)
				->orderBy('staff_task_allocation.id', 'desc')
				->get()
				->toArray();

			foreach ($staff_tasks as $key => $staff_task) {
				// check if this sick leave is booked in calendar
				$event_id   = $staff_task['id'];
				$event_type = '8';
				$user_id    = $staff_task['staff_member_id'];

				$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
				$staff_tasks[$key] = array_merge($staff_tasks[$key], $booking_response);
			}
			$staff['task_allocs'] = $staff_tasks;
			//echo "<pre>"; print_r($staff['task_allocs']); die;
			//Staff Task Allocation End
		}

		$staff_members   = 	User::where('is_deleted', '0')
			->where('home_id', Auth::user()->home_id)
			->get();

		$system_calendar = 	'yes';

		//Log Book
		$log_books = LogBook::where('added_to_calendar', '1')
			->where('home_id', $home_id)
			->get()
			->toArray();

		foreach ($log_books as $key => $log_book) {
			//check if this log book is booked in calendar
			$event_id   = $log_book['id'];
			$event_type = '11';

			$booking_response = Calendar::checkIsEventAddedtoCalendar('', $event_id, $event_type);
			$log_books[$key]  = array_merge($log_books[$key], $booking_response);
			// echo"<pre>";print_r($log_books[$key]);die;
		}
		$log['log_book'] = $log_books;
		// echo "<pre>"; print_r($log); die;

			// Mandatory Leave
		$mandatory_leave_records = MandatoryLeave::select('mandatory_leaves.id as mandatory_leaves_id', 'mandatory_leaves.title', 'mandatory_leaves.user_id as user_id', 'su.id as su_id')
			->join('service_user as su', 'su.id', 'mandatory_leaves.service_user_id')
			->where('mandatory_leaves.user_id', Auth::user()->id)
			->where('su.home_id', $home_id)
			->where('mandatory_leaves.is_deleted', '0')
			->orderBy('mandatory_leaves.id', 'desc')
			->get()
			->toArray();

		// dd($mandatory_leave_records);

		$ml_record = array();
		for ($i = 0; $i < count($mandatory_leave_records); $i++) {
			$ml_id   = $mandatory_leave_records[$i]['mandatory_leaves_id'];
			$su_user   = $mandatory_leave_records[$i]['su_id'];
			$ml_type = '12';

			$booking_response_value   = Calendar::checkIsEventAddedtoCalendar($su_user, $ml_id, $ml_type);
			$ml_record[] = array_merge($mandatory_leave_records[$i], $booking_response_value);
		}
		$mandatory['ml_record'] = $ml_record;
		// End Mandatory Leave

		$labels = HomeLabel::getLabels($home_id);

		$appointment_plans = PlanBuilder::select('id', 'home_id', 'title')->where('home_id', $home_id)->where('is_deleted', '0')->get();
		$guide_tag = 'main_cal';

		// echo "<pre>"; print_r($staff); die;

		if ($user_type == 'STAFF') {

			$members =  User::select('id', 'name')
				->where('is_deleted', '0')
				->where('home_id', Auth::user()->home_id)
				->get()
				->toArray();
		} else if ($user_type == 'SU') {

			$members =  ServiceUser::select('id', 'name')
				->where('is_deleted', '0')
				->where('home_id', Auth::user()->home_id)
				->get()
				->toArray();
		} else {
			$members = '';
		}
		// echo "<pre>"; print_r($service_users); die;
		return view('frontEnd.systemManagement.calendar', compact('service_users', 'appointment_plans', 'system_calendar', 'staff', 'log', 'labels', 'guide_tag', 'staff_members', 'user_type', 'members','ml_record','mandatory','selected_user_id'));
	}

	public function add_event(Request $request)
	{

		if ($request->isMethod('post')) {
			$data = $request->input();
			$home_ids = Auth::user()->home_id;
			$ex_home_ids = explode(',', $home_ids);
			$home_id                    = $ex_home_ids[0];

			$calendar 					      = new Calendar;
			$calendar->service_user_id 	      = $data['service_user_id'];
			$calendar->calendar_event_type_id = $data['event_type'];
			$calendar->event_id 		      = $data['event_id'];
			$calendar->event_date 		      = date('Y-m-d', strtotime($data['event_date']));
			$calendar->home_id 			      = $home_id;
			if ($calendar->save()) {
				$result['response'] = true;
				$result['calendar_id'] = $calendar->id;
			} else {
				$result['response'] = false;
				$result['calendar_id'] = 0;
			}
			return $result;
		}
	}

	public function move_event(Request $request)
	{

		$data = $request->all();
		$event_calendar_id = $data['event_calendar_id'];

		foreach ($data as $key => $value) {
			$updated 				= Calendar::find($event_calendar_id);
			$updated->event_date	= date('Y-m-d', strtotime($data['event_date']));

			if ($updated->save()) {
				echo 'true';
			} else {
				echo 'false';
			}
			die;
		}
	}


	public function select_member(Request $request)
	{

		// echo "<pre>"; print_r($request->input()); die;
		$user_type = $request->user_type;

		if ($user_type == 'STAFF') {

			$members =  User::select('id', 'name')
				->where('is_deleted', '0')
				->where('home_id', Auth::user()->home_id)
				->get()
				->toArray();
		} else if ($user_type == 'SU') {

			$members =  ServiceUser::select('id', 'name')
				->where('is_deleted', '0')
				->where('home_id', Auth::user()->home_id)
				->get()
				->toArray();
		} else {
			$members = '';
		}

		$html = '<option value="">Select User</option>';
		if (!empty($members)) {

			$html .= '<option value="All">ALL</option>';
			foreach ($members as $key => $member) {
				$html .= '<option value ="' . $member['id'] . '">' . ucfirst($member['name']) . '</option>';
			}
		}

		echo $html;
		die;
	}

	/*public function sel_user_type(Request $request) {

		// echo "<pre>"; print_r($request->input()); die;
		$user_type = $request->user_type;

		if($user_type == 'STAFF') {
			
			$members =  User::select('id','name')
								->where('is_deleted','0')
								->where('home_id', Auth::user()->home_id)
								->get()
								->toArray();
			

		} else if($user_type == 'SU') {

			$members =  ServiceUser::select('id', 'name')
										->where('is_deleted','0')
										->where('home_id', Auth::user()->home_id)
										->get()
										->toArray();
		
		} else {
			$members = '';
		} 
		
		$html = '<option value="">Select User</option>';			
		if(!empty($members)) {
			
			$html .= '<option value="'.$user_type.'->All">ALL</option>';
			foreach ($members as $key => $member) {
				$html .= '<option value ="'.$user_type.'->'.$member['id'].'">'.ucfirst($member['name']).'</option>';
			}
		}

		echo $html; 
		die;
	}*/

	/*---------- beforeCalendarManage(June12,2018 AKHIL) ----*/
	// public function index(Request $request){

	// 	// echo "<pre>"; print_r($request->input()); die;
	// 	$staff_members = User::where('is_deleted','0')
	// 							->where('home_id', Auth::user()->home_id)
	// 							->get();

	// 	$system_calendar = 'yes';
	// 	$home_id         = Auth::user()->home_id;	
	// 	$service_users   = ServiceUser::select('id','name')
	// 							->where('status','1')
	// 							->where('home_id', $home_id)
	// 							->get();
	// 							//->toArray();

	// 	foreach ($service_users as $su_key => $service_user) {

	// 		// $service_user_id = $service_user['id'];
	// 		$service_user_id = $service_user->id;

	// 		// heath record list
	// 		$health_records = ServiceUserHealthRecord::select('su_health_record.id as health_record_id','su_health_record.title')
	// 									->join('service_user as su', 'su.id','su_health_record.service_user_id')
	// 									->where('su_health_record.service_user_id', $service_user_id)
	// 									->where('su.home_id', $home_id)
	// 									->where('su_health_record.is_deleted', '0')
	// 									->orderBy('health_record_id','desc')
	// 									->get()
	// 									->toArray();

	// 		foreach ($health_records as $key => $health_record) {
	// 			// check if this health_record is booked in calendar
	// 			$event_id 	= $health_record['health_record_id'];
	// 			$event_type = '1';

	// 			$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
	// 			$health_records[$key] = array_merge($health_records[$key],$booking_response);

	// 		}
	// 		$service_users[$su_key]['health_records'] = $health_records;

	// 		// daily record list
	// 		$daily_records = ServiceUserDailyRecord::select('su_daily_record.id as su_daily_record_id','su_daily_record.daily_record_id','dr.description' )
	// 											->join('daily_record as dr', 'dr.id','su_daily_record.daily_record_id')
	// 											->join('service_user as su', 'su.id','su_daily_record.service_user_id')
	// 											->where('su_daily_record.service_user_id', $service_user_id)
	// 											->where('su.home_id',$home_id)
	// 											->where('su_daily_record.is_deleted', '0')
	// 											->where('su_daily_record.added_to_calendar', '1')
	// 											->orderBy('su_daily_record.id','desc')
	// 											->get()
	// 											->toArray();

	// 		foreach ($daily_records as $key => $daily_record) {
	// 			//check if this daily record is booked in calendar
	// 			$event_id   = $daily_record['su_daily_record_id'];
	// 			$event_type = '2';

	// 			$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
	// 			$daily_records[$key] = array_merge($daily_records[$key],$booking_response);
	// 		}	
	// 		$service_users[$su_key]['daily_records'] = $daily_records;

	// 		//Living Skills
	// 		$living_skills = ServiceUserLivingSkill::select('su_living_skill.id as su_living_skill_id','su_living_skill.living_skill_id','ls.description')
	// 												->join('living_skill as ls','ls.id','su_living_skill.living_skill_id')
	// 												->join('service_user as su', 'su.id','su_living_skill.service_user_id')
	// 												->where('su_living_skill.service_user_id', $service_user_id)
	// 												->where('su_living_skill.is_deleted','0')
	// 												->where('su_living_skill.added_to_calendar', '1')
	// 												->where('su.home_id', $home_id)
	// 												->orderBy('su_living_skill.id','desc')
	// 												->get()
	// 												->toArray();

	// 		foreach ($living_skills as $key => $living_skill) {
	// 			//check if this living skill is booked in calendar
	// 			$event_id   = $living_skill['su_living_skill_id'];
	// 			$event_type = '9';

	// 			$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
	// 			$living_skills[$key] = array_merge($living_skills[$key],$booking_response);
	// 		}
	// 		$service_users[$su_key]['living_skills'] = $living_skills;
	// 		//Living Skills End

	// 		//Education Records
	// 		$education_records = ServiceUserEducationRecord::select('su_education_record.id as su_education_record_id','su_education_record.education_record_id','er.description')
	// 												->join('education_record as er','er.id','su_education_record.education_record_id')
	// 												->join('service_user as su', 'su.id','su_education_record.service_user_id')
	// 												->where('su_education_record.service_user_id', $service_user_id)
	// 												->where('su_education_record.is_deleted','0')
	// 												->where('su_education_record.added_to_calendar', '1')
	// 												->where('su.home_id', $home_id)
	// 												->orderBy('su_education_record.id','desc')
	// 												->get()
	// 												->toArray();
	// 		//echo "<pre>"; print_r($education_records); die;
	// 		foreach ($education_records as $key => $education_record) {
	// 			//check if this living skill is booked in calendar
	// 			$event_id   = $education_record['su_education_record_id'];
	// 			$event_type = '10';

	// 			$booking_response = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
	// 			$education_records[$key] = array_merge($education_records[$key],$booking_response);
	// 		}
	// 		$service_users[$su_key]['education_records'] = $education_records;
	// 		//echo "<pre>"; print_r($service_users[$su_key]['education_records']); die;
	// 		//Education Records End

	// 		//earning category incentives
	// 		$earn_incentives =  ServiceUserEarningIncentive::select('su_earning_incentive.id as su_ern_inc_id', 'su_earning_incentive.star_cost','incentive.name')
	// 								->join('incentive','incentive.id','su_earning_incentive.incentive_id')
	// 								->where('su_earning_incentive.service_user_id', $service_user_id)
	// 								->where('incentive.is_deleted','0')
	// 								// ->where('su.home_id', $home_id)
	// 								->orderBy('su_earning_incentive.id','desc')
	// 								->get()
	// 								->toArray();

	// 		foreach ($earn_incentives as $key => $earn_incentive) {
	// 			//check if this incentive is booked in calendar
	// 			$event_id   = $earn_incentive['su_ern_inc_id'];
	// 			$event_type = '3';

	// 			$booking_response 	   = Calendar::checkIsEventAddedtoCalendar($service_user_id,$event_id,$event_type);
	// 			$earn_incentives[$key] = array_merge($earn_incentives[$key], $booking_response);
	// 		}
	// 		$service_users[$su_key]['earn_incentives'] = $earn_incentives;


	// 		// events list 
	// 		$event_records = ServiceUserCalendarEvent::select('su_calendar_event.id as su_calendar_event_id','su_calendar_event.title','su.id as su_id')
	// 												->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
	// 												->where('su_calendar_event.service_user_id', $service_user_id)
	// 												->where('su.home_id', $home_id)
	// 												->orderBy('su_calendar_event.id', 'desc')
	// 												->get()
	// 												->toArray(); 

	// 		foreach ($event_records as $key => $event_record) {
	// 			//check if this event is booked in calendar
	// 			$event_id   = $event_record['su_calendar_event_id'];
	// 			$event_type = '4';

	// 			$booking_response    = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
	// 			$event_records[$key] = array_merge($event_records[$key], $booking_response);
	// 		}
	// 		$service_users[$su_key]['event_records'] = $event_records;

	// 		// calendar notes list
	// 		$calender_notes = ServiceUserCalendarNote::select('su_calendar_note.id','su_calendar_note.title as note_title','su_calendar_note.note as title')
	// 							->join('service_user as su', 'su.id','su_calendar_note.service_user_id')
	// 							->where('su_calendar_note.service_user_id', $service_user_id)
	// 							->where('su.home_id', $home_id)
	// 							->orderBy('su_calendar_note.id','desc')
	// 							->get()
	// 							->toArray();


	// 		foreach ($calender_notes as $key => $calender_note) {
	// 			//check if this calendar note is booked in calendar
	// 			$event_id   = $calender_note['id'];
	// 			$event_type = '5';

	// 			$booking_response     = Calendar::checkIsEventAddedtoCalendar($service_user_id, $event_id, $event_type);
	// 			$calender_notes[$key] = array_merge($calender_notes[$key], $booking_response);
	// 		}
	// 		$service_users[$su_key]['calender_notes'] = $calender_notes;
	// 	}

	// 	// echo "<pre>"; print_r($service_users); die;

	// 	//Staff Annual Leave
	// 	$staff_annual_leaves = StaffAnnualLeave::select('staff_annual_leave.id','staff_annual_leave.title as annual_title','staff_annual_leave.staff_member_id','user.name as staff_name')
	// 									->join('user','user.id','staff_annual_leave.staff_member_id')
	// 									->where('staff_annual_leave.is_deleted','0')
	// 									->where('staff_annual_leave.home_id', $home_id)
	// 									->orderBy('staff_annual_leave.id','desc')
	// 									->get()
	// 									->toArray();

	// 	foreach ($staff_annual_leaves as $key => $staff_annual_leave) {
	// 		// check if this annual leave is booked in calendar
	// 		$event_id 	= $staff_annual_leave['id'];
	// 		$event_type = '6';
	// 		$user_id    = $staff_annual_leave['staff_member_id'];

	// 		$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id,$event_id, $event_type);
	// 		$staff_annual_leaves[$key] = array_merge($staff_annual_leaves[$key], $booking_response);
	// 	}	
	// 	$staff['annual_leaves'] = $staff_annual_leaves;
	// 	//Staff Annual Leave End

	// 	//Staff Sick Leave
	// 	$staff_sick_leaves = StaffSickLeave::select('staff_sick_leave.id','staff_sick_leave.title as sick_title','staff_sick_leave.staff_member_id','user.name as staff_name')
	// 									->join('user','user.id','staff_sick_leave.staff_member_id')
	// 									->where('staff_sick_leave.is_deleted','0')
	// 									->where('staff_sick_leave.home_id', $home_id)
	// 									->orderBy('staff_sick_leave.id','desc')
	// 									->get()
	// 									->toArray();
	// 	foreach ($staff_sick_leaves as $key => $staff_sick_leave) {
	// 		// check if this sick leave is booked in calendar
	// 		$event_id   = $staff_sick_leave['id'];
	// 		$event_type = '7';
	// 		$user_id    = $staff_sick_leave['staff_member_id'];

	// 		$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
	// 		$staff_sick_leaves[$key] = array_merge($staff_sick_leaves[$key], $booking_response);

	// 	}
	// 	$staff['sick_leaves'] = $staff_sick_leaves;
	// 	//echo "<pre>"; print_r($staff['sick_leaves']); die;
	// 	//Staff Sick Leave End

	// 	//Staff Task Allocation End
	// 	$staff_tasks = StaffTaskAllocation::select('staff_task_allocation.id','staff_task_allocation.title as task_title','staff_task_allocation.staff_member_id','user.name as staff_name')
	// 									->join('user','user.id','staff_task_allocation.staff_member_id')
	// 									->where('staff_task_allocation.is_deleted','0')
	// 									->where('staff_task_allocation.status','1')
	// 									->where('staff_task_allocation.home_id', $home_id)
	// 									->orderBy('staff_task_allocation.id','desc')
	// 									->get()
	// 									->toArray();

	// 	foreach ($staff_tasks as $key => $staff_task) {
	// 		// check if this sick leave is booked in calendar
	// 		$event_id   = $staff_task['id'];
	// 		$event_type = '8';
	// 		$user_id    = $staff_task['staff_member_id'];

	// 		$booking_response = Calendar::checkIsEventAddedtoCalendar($user_id, $event_id, $event_type);
	// 		$staff_tasks[$key] = array_merge($staff_tasks[$key], $booking_response);

	// 	}
	// 	$staff['task_allocs'] = $staff_tasks;
	// 	//echo "<pre>"; print_r($staff['task_allocs']); die;
	// 	//Staff Task Allocation End

	// 	//Log Book
	// 	$log_books = LogBook::where('added_to_calendar','1')->get()->toArray();
	// 	foreach ($log_books as $key => $log_book) {
	// 		//check if this log book is booked in calendar
	// 		$event_id   = $log_book['id'];
	// 		$event_type = '11';

	// 		$booking_response = Calendar::checkIsEventAddedtoCalendar('',$event_id, $event_type);
	// 		$log_books[$key]  = array_merge($log_books[$key], $booking_response);

	// 	}
	// 	$log['log_book'] = $log_books;

	// 	$labels = HomeLabel::getLabels($home_id);

	// 	$appointment_plans = PlanBuilder::select('id','home_id','title')->where('home_id',$home_id)->where('is_deleted','0')->get();
	// 	$guide_tag = 'main_cal';

	// 	return view('frontEnd.systemManagement.calendar', compact('service_users','appointment_plans','system_calendar','staff','log','labels','guide_tag','staff_members'));
	// }

	public function del_calender_event(Request $request, $event_id)
	{

		// echo $event_id;//die;
		// echo "<pre>"; print_r($request->input());die;
		if ($request->record_type == 'health_record') {
			$del = ServiceUserHealthRecord::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'daily_records') {
			$del = ServiceUserDailyRecord::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'living_skills') {
			$del = ServiceUserLivingSkill::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'education_records') {
			$del = ServiceUserEducationRecord::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'earn_incentives') {
			$del = ServiceUserEarningIncentive::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'event_records') {
			$del = ServiceUserCalendarEvent::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'calender_notes') {
			$del = ServiceUserCalendarNote::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'annual_leaves') {
			$del = StaffAnnualLeave::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'sick_leaves') {
			$del = StaffSickLeave::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'task_allocs') {
			$del = StaffTaskAllocation::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'log_book') {
			$del = LogBook::where('id', $event_id)
				->update(['added_to_calendar' => '0']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		} elseif ($request->record_type == 'mandatory_leave') {
			$del = MandatoryLeave::where('id', $event_id)
				->update(['is_deleted' => '1']);
			if ($del) {
				echo "1";
			} else {
				echo "2";
			}
		}
	}
}
