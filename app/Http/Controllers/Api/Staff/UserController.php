<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use App\User, App\ServiceUser, App\Admin, App\Home, App\LogBook;
use Session;
use Illuminate\Support\Facades\Hash;
use App\Services\Staff\AddStaffService;
use App\Http\Requests\Staff\StoreStaffRequest;
use Carbon\Carbon;
use App\Services\Staff\StaffService;
use App\UserQualification;
use App\Models\ScheduledShift;
use Illuminate\Support\Facades\Mail;
use App\LoginInActivity;
use App\Models\Timesheet;
use App\Models\HomeManagement\PayRate;
use App\Models\HomeManagement\PayRateType;
use App\Models\ShiftCategory;

class UserController extends Controller
{
	protected StaffService $staffService;

	public function __construct(StaffService $staffService)
	{
		$this->staffService = $staffService;
	}

	public function login(Request $request)
	{

		if (Auth::check()) {
			return redirect('/');
		}

		if ($request->isMethod('post')) {
			$data 		  = $request->input();
			$username 	  = $data['username'];
			$hme_id 	  = $data['home'];
			$current_date = date('m/d/Y');
			// $current_date = '10/03/2018';
			// echo "<pre>"; print_r($current_date);  

			$user_info 	= user::select('id', 'home_id', 'admn_id', 'user_type', 'login_date', 'login_home_id')
				->where('user_name', $username)
				->where('is_deleted', '0')
				->first();
			//echo "<pre>"; print_r($user_info->login_date); 
			//echo "<pre>"; print_r($user_info->toArray());  


			if (!empty($user_info)) {
				$searchString = ',';
				//$homde_id = 1,2
				if (strpos($user_info->home_id, $searchString) !== false) {
					$array =  explode(',', $user_info->home_id);

					// echo "<pre>"; print_r($array); die;
					if (in_array($hme_id, $array)) {
						if ($request->isMethod('post')) {

							$data = $request->input();
							if ($user_info->user_type != 'N') {
								if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'admn_id' => $user_info->admn_id])) {
									// echo "<pre>"; print_r($user_info); die; 
									$new_home_ids = $hme_id . ',' . $user_info->home_id;
									$new_home_ids = implode(',', array_unique(explode(',', $new_home_ids)));
									$update_home_id = User::where('user_name', $username)
										->update(['home_id' => $new_home_ids]);
									//$monolog = \Log::getMonolog();
									//echo '<pre>'; print_r($monolog); die;
									//saving log start
									/*$logbook 		  			= new LogBook;
									$logbook->home_id 			= Auth::user()->home_id;
									$logbook->user_id 			= Auth::user()->id;
									$logbook->action 			= 'LOGIN';
									$logbook->module_name 		= 'USER_LOGIN';
									$logbook->model_name 		= 'USER';
									$logbook->table_primary_id 	= Auth::user()->id;
									$logbook->save();*/
									//saving log end
									//Session::put('LAST_ACTIVITY',time());

									//check is user already logged in
									$logged_in = Auth::user()->logged_in;
									if ($logged_in == '1') {


										$last_activity = Auth::user()->last_activity_time;
										$current_time  = date('Y-m-d H:i:s');

										$last_activity = Carbon::parse($last_activity);
										$diff_mint     = $last_activity->diffInMinutes();

										if ($diff_mint > SESSION_TIMEOUT) {
										} else {
											Auth::logout();
											return redirect()->back()->with('error', 'You are already logged in from some other device.');
										}
									}

									User::setUserLogInStatus(1);
									//echo csrf_token(); die;
									//echo "222"; die;
									return redirect('/')->with('success', 'Welcome back ' . Auth::user()->user_name);
								}
							} elseif ($user_info->user_type == 'N') {

								if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'login_home_id' => $user_info->login_home_id])) {

									// $monolog = \Log::getMonolog();
									// echo '<pre>'; print_r($monolog); die;
									//saving log start
									/*$logbook 		  			= new LogBook;
									$logbook->home_id 			= Auth::user()->home_id;
									$logbook->user_id 			= Auth::user()->id;
									$logbook->action 			= 'LOGIN';
									$logbook->module_name 		= 'USER_LOGIN';
									$logbook->model_name 		= 'USER';
									$logbook->table_primary_id 	= Auth::user()->id;
									$logbook->save();*/
									//saving log end


									//Session::put('LAST_ACTIVITY',time());

									//check is user already logged in
									$logged_in = Auth::user()->logged_in;
									if ($logged_in == '1') {


										$last_activity = Auth::user()->last_activity_time;
										$current_time  = date('Y-m-d H:i:s');

										$last_activity = Carbon::parse($last_activity);
										$diff_mint     = $last_activity->diffInMinutes();

										if ($diff_mint > SESSION_TIMEOUT) {
										} else {
											Auth::logout();
											return redirect()->back()->with('error', 'You are already logged in from some other device.');
										}
									}

									//if another staff user login date is expired(user_info->login_date) then his home_id is updated 
									if (!empty($user_info->login_date)) {
										if ($current_date > $user_info->login_date) {
											$home_id = substr($user_info->home_id, 2);
											//echo "<pre>"; print_r($home_id); die; 
											$update  = User::where('id', $user_info->id)->update(['home_id' => $home_id]);

											$this->login_staff_user($data, $user_info);
											//this function is used to login staff user with their previous home, not to assigned home because assigned staff user date is expired.
										}
									}

									User::setUserLogInStatus(1);
									//echo csrf_token(); die;
									return redirect('/')->with('success', 'Welcome back ' . Auth::user()->user_name);
								} else {  //echo "string3"; die;
									return redirect()->back()->with('error', 'Incorrect email or password combination.');
								}
							} else {
								return redirect()->back()->with('error', 'Incorrect email or password combination.');
							}
						}
					} else {
						return redirect()->back()->with('error', 'Incorrect email or password combination.');
					}
				} else {
					if ((!empty($user_info->login_date)) && ($user_info->login_date != NULL)) {

						if ($current_date == $user_info->login_date) {

							if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'login_home_id' => $user_info->login_home_id])) {

								//check is user already logged in
								$logged_in = Auth::user()->logged_in;
								if ($logged_in == '1') {

									$last_activity = Auth::user()->last_activity_time;
									$current_time  = date('Y-m-d H:i:s');

									$last_activity = Carbon::parse($last_activity);
									$diff_mint     = $last_activity->diffInMinutes();

									if ($diff_mint > SESSION_TIMEOUT) {
									} else {
										Auth::logout();
										return redirect()->back()->with('error', 'You are already logged in from some other device.');
									}
								}

								$home_id  = $user_info->login_home_id . ',' . $user_info->home_id;
								$update   = User::where('id', $user_info->id)->update(['home_id' => $home_id]);
								// echo "<pre>"; print_r($home_id); die;

								User::setUserLogInStatus(1);
								//echo csrf_token(); die;
								return redirect('/')->with('success', 'Welcome back ' . Auth::user()->user_name);
							} else {
								return redirect()->back()->with('error', 'Incorrect email or password combination.');
							}
						}/*else{
							$home_id = substr($user_info->home_id,2); 
							$update  = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
						}*/ //echo "string12345"; die;
					}

					//$this->login_staff_user($data,$user_info);
					if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'home_id' => $data['home']])) {

						// $monolog = \Log::getMonolog();
						// echo '<pre>'; print_r($monolog); die;
						//saving log start
						/*$logbook 		  			= new LogBook;
							$logbook->home_id 			= Auth::user()->home_id;
							$logbook->user_id 			= Auth::user()->id;
							$logbook->action 			= 'LOGIN';
							$logbook->module_name 		= 'USER_LOGIN';
							$logbook->model_name 		= 'USER';
							$logbook->table_primary_id 	= Auth::user()->id;
							$logbook->save();*/
						//saving log end


						//Session::put('LAST_ACTIVITY',time());

						//check is user already logged in
						$logged_in = Auth::user()->logged_in;
						if ($logged_in == '1') {


							$last_activity = Auth::user()->last_activity_time;
							$current_time  = date('Y-m-d H:i:s');

							$last_activity = Carbon::parse($last_activity);
							$diff_mint     = $last_activity->diffInMinutes();

							if ($diff_mint > SESSION_TIMEOUT) {
							} else {
								Auth::logout();
								return redirect()->back()->with('error', 'You are already logged in from some other device.');
							}
						}
						//if another staff user date is expired(user_info->login_date) then his home_id is updated 
						if (!empty($user_info->login_date)) {
							if ($current_date > $user_info->login_date) {
								$home_id = substr($user_info->home_id, 2);
								if ($home_id == 0) {
									$update  = User::where('id', $user_info->id)->update(['home_id' => $user_info->home_id]);
								} else {
									$update  = User::where('id', $user_info->id)->update(['home_id' => $home_id]);
								}
							}
						}

						User::setUserLogInStatus(1);
						//echo csrf_token(); die;
						return redirect('/')->with('success', 'Welcome back ' . Auth::user()->user_name);
					} else {
						return redirect()->back()->with('error', 'Incorrect email or password combination.');
					}
				}
			} else {
				return redirect()->back()->with('error', 'Incorrect email or password combination.');
			}
		}

		return view('frontEnd.login');
	}


	function login_staff_user($data, $user_info)
	{

		$current_date = date('m/d/Y');
		//$current_date = '09/01/2018';
		if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'home_id' => $data['home']])) {

			//check is user already logged in
			$logged_in = Auth::user()->logged_in;
			if ($logged_in == '1') {


				$last_activity = Auth::user()->last_activity_time;
				$current_time  = date('Y-m-d H:i:s');

				$last_activity = Carbon::parse($last_activity);
				$diff_mint     = $last_activity->diffInMinutes();

				if ($diff_mint > SESSION_TIMEOUT) {
				} else {
					Auth::logout();
					return redirect()->back()->with('error', 'You are already logged in from some other device.');
				}
			}

			//if another staff user date is expired(user_info->login_date) then his home_id is updated 
			/*if(!empty($user_info->login_date)){
		    	if($current_date > $user_info->login_date){
			    	$home_id = substr($user_info->home_id,2); 
					$update  = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
		    	}					
	    	}*/

			User::setUserLogInStatus(1);
			//echo csrf_token(); die;
			return redirect('/')->with('success', 'Welcome back ' . Auth::user()->user_name);
		} else {
			return redirect()->back()->with('error', 'Incorrect email or password combination.');
		}
	}

	public function logout()
	{

		if (Auth::check()) {
			User::setUserLogInStatus(0);

			Auth::logout();

			Session::forget('LAST_ACTIVITY');
		}
		return redirect('/login');
	}

	public function show_set_password_form(Request $request, $user_id = null, $security_code = null)
	{

		$decoded_user_id = convert_uudecode(base64_decode($user_id));
		$decoded_security_code = convert_uudecode(base64_decode($security_code));

		$count = User::where('id', $decoded_user_id)
			->where('security_code', $decoded_security_code)
			->first();

		if (!empty($count)) {
			$user_name = $count->user_name;
			return view('frontEnd.user_set_password', compact('user_id', 'security_code', 'user_name'));
		} else {
			return redirect('/login')->with('error', 'This link has been already used.');
		}
	}

	public function set_password(Request $request)
	{
		$data = $request->input();
		if (empty($data['password'])) {
			return redirect()->back()->with('error', 'Please Enter Password');
		} else if ($data['password'] != $data['confirm_password']) {
			return redirect()->back()->with('error', 'Password & confirm password does not matched.');
		}

		$user_id = convert_uudecode(base64_decode($data['user_id']));
		$security_code = convert_uudecode(base64_decode($data['security_code']));

		$user = User::where('id', $user_id)
			->where('security_code', $security_code)
			->first();

		$user->security_code = '';
		$user->password =	Hash::make($data['password']);
		//echo $data['password']; die;
		if ($user->save()) {
			return redirect('/login')->with('success', 'You have set your password successfully.');
		} else {
			return redirect('/login')->with('error', 'Some error occured. Please try again later');
		}
	}

	public function get_homes(Request $request, $company_name = null)
	{
		$admin_id = Admin::where('company', 'like', $company_name)->value('id');

		$homes = Home::select('id', 'title')->where('admin_id', $admin_id)->where('is_deleted', '0')->get()->toArray();

		if (!empty($homes)) {
			foreach ($homes as $home) {
				echo '<option value="' . $home['id'] . '">' . $home['title'] . '</option>';
			}
		} else {
			echo '';
		}
		die;
		return view('backEnd.login', compact('page', 'company_name'));
	}

	public function check_username_exists(Request $request)
	{

		$data = $request->input();

		$user_name = '';
		if (is_array($data)) {
			$user_name_arr = array_values($data);
			$user_name = $user_name_arr[0];
		}

		$response = Home::userNameUnique($user_name);
		echo json_encode($response);
		//echo $response; die;

	}

	/*public function check_staff_username_exists(Request $request){
    	
    	$count = User::where('user_name',$request->staff_user_name)->count();

        if($count > 0)  {
          	echo json_encode(false);	 //  for jquery validations
        } else {
            echo json_encode(true);      //  for jquery validations
        }    
    }

    public function check_su_username_exists(Request $request){
    	
    	$count = ServiceUser::where('user_name',$request->su_user_name)->count();

        if($count > 0) {
           echo json_encode(false);	  	 //  for jquery validations
        } else {
            echo json_encode(true);      //  for jquery validations
        }  
    }*/


	public function addStaffUser(StoreStaffRequest $request, AddStaffService $service)
	{
		$data = $request->validated();

		$user = $service->create($data);

		if (!$user) {
			return response()->json([
				'status'  => false,
				'message' => 'Something went wrong. Please try again.'
			], 500);
		}

		return response()->json([
			'status'  => true,
			'message' => 'Staff user added successfully.',
			'data'    => $user
		], 201);
	}

	public function setPassword() {}

	public function cources_list(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'user_id' => 'required',
		]);
		if ($validator->fails()) {
			return [
				'success' => false,
				'errors' => $validator->errors()->first()
			];
		}
		$courses = $this->staffService->courses();
		// echo "<pre>";print_r($courses);die;
		$data = array();
		foreach ($courses as $val) {
			$isSelect = 0;
			$previous_id = '';
			$previous_image = '';
			$qualificationDetails = UserQualification::where(['user_id' => $request->user_id, 'course_id' => $val['course_id'], 'is_deleted' => 0])
				->first();
			if ($qualificationDetails) {
				$isSelect = 1;
				$previous_id = $qualificationDetails->id;
				$previous_image = url('public/images/userQualification') . '/' . $qualificationDetails->image;
			}
			$data[] = [
				'course_id' => $val['course_id'],
				'coursenumber' => $val['coursenumber'],
				'title' => $val['title'],
				'level' => $val['level'],
				'country_name' => $val['country_name'],
				'image' => $val['image'],
				'description' => $val['description'],
				'course_credit' => $val['course_credit'],
				'passing_score' => $val['passing_score'],
				'isSelect' => $isSelect,
				'previous_id' => $previous_id,
				'previous_image' => $previous_image
			];
		}
		if (empty($courses)) {
			return response()->json([
				'success' => false,
				'message' => 'No courses found',
				'data'    => []
			], 200);
		}

		return response()->json([
			'success' => true,
			'message' => "Course List",
			'data'    => $data
		], 200);
	}

	// public function addStaffQualification(Request $request)
	// {
	// 	// echo "<pre>";print_r($request->all());die;
	// 	$validator = Validator::make($request->all(), [
	// 		'user_id'    => 'required|exists:user,id',
	// 		'qualification' => 'required|array',
	// 		'qualification.*.course_id' => 'required|integer',
	// 		'qualification.*.name' => 'required|string',
	// 		'qualification.*.cert' => 'nullable',
	// 		'qualification.*.previous_id' => 'nullable|integer|exists:user_qualification,id',
	// 	]);
	// 	if ($validator->fails()) {
	// 		return response()->json([
	// 			'success' => false,
	// 			'errors'  => $validator->errors()
	// 		], 422);
	// 	}

	// 	$result = UserQualification::saveQualification($request->qualification, $request->user_id);

	// 	if ($result === false) {
	// 		return response()->json([
	// 			'success' => false,
	// 			'message' => 'Failed to assign courses to staff user.'
	// 		], 500);
	// 	}

	// 	return response()->json([
	// 		'success' => true,
	// 		'message' => 'Qualification assigned to staff user successfully.'
	// 	], 200);
	// }
	public function addStaffQualification(Request $request)
	{
		// echo "<pre>";print_r($request->all());die;
		// echo "<pre>";print_r($request->all());die;
		// $qualifications = $request->input('qualification', []);
		// $flatQualifications = collect($qualifications)
		// 	->flatten(1)
		// 	->values()
		// 	->all();

		$data = $request->all();
		// $data['qualification'] = $flatQualifications;
		$validator = Validator::make($data, [
			'user_id' => 'required|exists:user,id',

			'qualification' => 'required|array',
			'qualification.*.course_id' => 'required|integer',
			'qualification.*.name' => 'required|string',
			'qualification.*.cert' => 'nullable',
			'qualification.*.previous_id' => 'nullable|integer|exists:user_qualification,id',
		]);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'errors'  => $validator->errors()
			], 422);
		}

		$result = UserQualification::saveQualification($request->qualification, $request->user_id);

		if ($result === false) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to assign courses to staff user.'
			], 500);
		}

		return response()->json([
			'success' => true,
			'message' => 'Qualification assigned to staff user successfully.'
		], 200);
	}
	public function staffQualificationList(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'user_id' => 'required',
		]);
		if ($validator->fails()) {
			return [
				'success' => false,
				'errors' => $validator->errors()->first()
			];
		}
		$qualifications = UserQualification::where(['user_id' => $request->user_id, 'is_deleted' => 0])
			->get();
		$data = array();
		foreach ($qualifications as $val) {
			$image = "";
			if ($val->image) {
				$image = url('public/images/userQualification') . '/' . $val->image;
			}
			$data[] = [
				'id' => $val->id,
				'user_id' => $val->user_id,
				'name' => $val->name,
				'image' => $image,
			];
		}
		return response()->json([
			'success' => true,
			'data' => $data,
			'message' => 'Qualification List.'
		], 200);
	}

	// public function dashboard(Request $request)
	// {
	// 	// Validate the incoming request
	// 	$validator = Validator::make($request->all(), [
	// 		'staff_id' => 'required',
	// 	]);
	// 	if ($validator->fails()) {
	// 		return [
	// 			'success' => false,
	// 			'errors' => $validator->errors()->first()
	// 		];
	// 	}

	// 	// Fetch scheduled shifts for the given staff member
	// 	$staffId = $request->input('staff_id');
	// 	$londonTime = Carbon::now('Europe/London');
	// 	$data['date_time'] = $londonTime->format('D d M Y');
	// 	$hour = $londonTime->hour;

	// 	if ($hour >= 5 && $hour < 12) {
	// 		$greeting = "Good Morning";
	// 	} elseif ($hour >= 12 && $hour < 17) {
	// 		$greeting = "Good Afternoon";
	// 	} elseif ($hour >= 17 && $hour < 21) {
	// 		$greeting = "Good Evening";
	// 	} else {
	// 		$greeting = "Good Night";
	// 	}
	// 	$user = User::where('id', $staffId)->select('name')->first();
	// 	$data['greeting'] = $greeting . ", " . $user->name;
	// 	$today = $londonTime->toDateString();
	// 	$currentTime = $londonTime->format('H:i:s');
	// 	$todayShift = ScheduledShift::where('staff_id', $staffId)
	// 		->where('start_date', $today)
	// 		->where('status', 'assigned')
	// 		->orderBy('start_time', 'asc')
	// 		->select('start_time', 'end_time', 'tasks', 'notes', 'id')
	// 		->get();

	// 	$data['today_shift'] = null;

	// 	foreach ($todayShift as $shift) {
	// 		$shiftEnd = Carbon::parse($shift->end_time)->format('H:i:s');

	// 		$loginInActivity = LoginInActivity::where('user_id', $staffId)
	// 			->where('shift_id', $shift->id)
	// 			->where('login_date', $today)
	// 			->orderBy('id', 'desc')
	// 			->first();

	// 		if ($loginInActivity) {
	// 			// Second condition: check login activity if null on checkout time the shift show else show the next shift
	// 			if ($loginInActivity->check_out_time == null) {
	// 				$shift->start_time = Carbon::parse($shift->start_time)->format('H:i');
	// 				$shift->end_time   = Carbon::parse($shift->end_time)->format('H:i');
	// 				$data['today_shift'] = $shift;
	// 				break;
	// 			} elseif ($shiftEnd >= $currentTime) {
	// 				$shift->start_time = Carbon::parse($shift->start_time)->format('H:i');
	// 				$shift->end_time   = Carbon::parse($shift->end_time)->format('H:i');
	// 				$data['today_shift'] = $shift;
	// 				break;
	// 			}
	// 		} else {
	// 			$data['today_shift'] = $shift;
	// 			break;
	// 		}
	// 	}

	// 	// Tomorrow shift: first shift after today
	// 	$tomorrowShift = ScheduledShift::where('staff_id', $staffId)
	// 		->select('start_time', 'end_time', 'tasks', 'id')
	// 		->where('start_date', '>', $today)
	// 		->first();
	// 	if ($tomorrowShift) {
	// 		$tomorrowShift->start_time = Carbon::parse($tomorrowShift->start_time)->format('H:i');
	// 		$tomorrowShift->end_time   = Carbon::parse($tomorrowShift->end_time)->format('H:i');
	// 		$data['tommorrow_shift'] = $tomorrowShift;
	// 	} else {
	// 		$data['tommorrow_shift'] = null;
	// 	}
	// 	return response()->json([
	// 		'success' => true,
	// 		'data' => $data,
	// 		'message' => 'Shift schedule fetched successfully.'
	// 	], 200);
	// }

	public function dashboard(Request $request)
	{
		// Validate the incoming request
		$validator = Validator::make($request->all(), [
			'staff_id' => 'required',
		]);
		if ($validator->fails()) {
			return [
				'success' => false,
				'errors' => $validator->errors()->first()
			];
		}

		// Fetch scheduled shifts for the given staff member
		$staffId = $request->input('staff_id');
		$londonTime = Carbon::now('Europe/London');
		$data['date_time'] = $londonTime->format('D d M Y');
		$hour = $londonTime->hour;

		if ($hour >= 5 && $hour < 12) {
			$greeting = "Good Morning";
		} elseif ($hour >= 12 && $hour < 17) {
			$greeting = "Good Afternoon";
		} elseif ($hour >= 17 && $hour < 21) {
			$greeting = "Good Evening";
		} else {
			$greeting = "Good Night";
		}
		$user = User::where('id', $staffId)->select('name')->first();
		$data['greeting'] = $greeting . ", " . $user->name;
		$today = $londonTime->toDateString();

		// Check for previous shift status
		$lastPreviousShift = ScheduledShift::where('staff_id', $staffId)
			->where('start_date', '<', $today)
			->where('status', 'assigned')
			->orderBy('start_date', 'desc')
			->orderBy('start_time', 'desc')
			->select('start_time', 'end_time', 'tasks', 'notes', 'id', 'start_date', 'status')
			->first();

		$data['is_previous_shift_logged_out'] = 0;
		$data['previous_shift_id'] = $lastPreviousShift ? $lastPreviousShift->id : null;
		$data['previous_shift'] = null;
		$data['status_of_shift'] = $lastPreviousShift ? $lastPreviousShift->status : null;

		if ($lastPreviousShift) {
			$lastActivity = LoginInActivity::where('user_id', $staffId)
				->where('shift_id', $lastPreviousShift->id)
				->orderBy('id', 'desc')
				->first();

			if ($lastActivity && $lastActivity->check_out_time == null) {
				$data['is_previous_shift_logged_out'] = 1;
				// $data['status_of_shift'] = $lastPreviousShift->status;
				$lastPreviousShift->start_time = Carbon::parse($lastPreviousShift->start_time)->format('H:i');
				$lastPreviousShift->end_time   = Carbon::parse($lastPreviousShift->end_time)->format('H:i');
				$data['previous_shift'] = $lastPreviousShift;
			}
		}

		$currentTime = $londonTime->format('H:i:s');
		$todayShift = ScheduledShift::where('staff_id', $staffId)
			->where('start_date', $today)
			->where('status', 'assigned')
			->orderBy('start_time', 'asc')
			->select('start_time', 'end_time', 'tasks', 'notes', 'id')
			->get();

		$data['today_shift'] = null;
		$nextShift = null;

		foreach ($todayShift as $shift) {

			$shiftStart = Carbon::parse($shift->start_time)->format('H:i:s');
			$shiftEnd   = Carbon::parse($shift->end_time)->format('H:i:s');

			$loginInActivity = LoginInActivity::where('user_id', $staffId)
				->where('shift_id', $shift->id)
				->where('login_date', $today)
				->orderBy('id', 'desc')
				->first();

			// ✅ 1. Highest Priority → Active shift (checkout NULL)
			if ($loginInActivity && $loginInActivity->check_out_time == null) {
				$shift->start_time = Carbon::parse($shift->start_time)->format('H:i');
				$shift->end_time   = Carbon::parse($shift->end_time)->format('H:i');

				$data['today_shift'] = $shift;
				break;
			}

			// ✅ 2. Skip expired shifts (IMPORTANT FIX)
			if ($shiftEnd < $currentTime) {
				continue;
			}

			// ✅ 3. Store next valid shift (if no active found)
			if (!$nextShift) {
				$shift->start_time = Carbon::parse($shift->start_time)->format('H:i');
				$shift->end_time   = Carbon::parse($shift->end_time)->format('H:i');

				$nextShift = $shift;
			}
		}

		// ✅ Final assignment if no active shift found
		if (!$data['today_shift']) {
			$data['today_shift'] = $nextShift;
		}

		// Tomorrow shift: first shift after today
		$tomorrowShift = ScheduledShift::where('staff_id', $staffId)
			->select('start_time', 'end_time', 'tasks', 'id')
			->where('start_date', '>', $today)
			->first();
		if ($tomorrowShift) {
			$tomorrowShift->start_time = Carbon::parse($tomorrowShift->start_time)->format('H:i');
			$tomorrowShift->end_time   = Carbon::parse($tomorrowShift->end_time)->format('H:i');
			$data['tommorrow_shift'] = $tomorrowShift;
		} else {
			$data['tommorrow_shift'] = null;
		}
		return response()->json([
			'success' => true,
			'data' => $data,
			'message' => 'Shift schedule fetched successfully.'
		], 200);
	}

	public function forget_password(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'type' => 'required|in:staff,service_user',
		]);
		if ($validator->fails()) {
			return [
				'success' => false,
				'message' => $validator->errors()->first()
			];
		}
		$email = $request->input('email');
		$type = $request->input('type');
		if ($type == 'staff') {
			$user = User::where('email', $email)->first();
			if (!$user) {
				return [
					'success' => false,
					'message' => 'User not found'
				];
			}
			$home_security_policy = Home::where('id', $user->home_id)->value('security_policy');
			$random_no            = rand(111111, 999999);
			$security_code        = base64_encode(convert_uuencode($random_no));
			$user_id              = base64_encode(convert_uuencode($user->id));
			$email                = $user->email;
			$name                 = $user->name;
			$user->security_code  = $random_no;
			$user_name            = $user->user_name;
			$company_name         = PROJECT_NAME;

			if ($user->save()) {
				$set_password_url = url('/set-password' . '/' . $user_id . '/' . $security_code);
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
					$arr = ['name' => $name, 'user_name' => $user_name, 'set_password_url' => $set_password_url, 'home_security_policy' => $home_security_policy];
					Mail::send('emails.user_set_password_mail', $arr, function ($message) use ($arr, $email, $company_name) {

						$message->to($email, $company_name)

							->subject('SCITS set Password Mail');

						$message->from('mobappssolutions153@gmail.com', $company_name);
					});
					return response()->json([
						'success' => true,
						'message' => 'Please check you email for password reset link.'
					], 200);
				}
			}
			return response()->json([
				'success' => false,
				'message' => 'Failed to reset password.'
			], 500);
		} else {
			$user = ServiceUser::where('email', $email)->first();
			if (!$user) {
				return [
					'success' => false,
					'message' => 'User not found'
				];
			}
			$home_security_policy = Home::where('id', $user->home_id)->value('security_policy');

			$random_no      = rand(111111, 999999);

			$user->password = Hash::make($random_no);

			$company_name = 'SCITS set Password Mail';
			$email        = $user->email;
			$name         = $user->name;
			$user_name    = $user->user_name;
			$password     = $random_no;

			/*echo '$user_name = '.$user_name;
        echo '$random_no = '.$random_no;
        die;*/
			if ($user->save()) {
				if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
					Mail::send('emails.service_user_send_password_mail', ['name' => $name, 'user_name' => $user_name, 'password' => $password, 'home_security_policy' => $home_security_policy], function ($message) use ($email, $company_name) {
						$message->to($email, $company_name)->subject('SCITS Welcome');
					});
					return response()->json([
						'success' => true,
						'message' => 'Please check you email for new password.'
					], 200);
				}
			}
			return response()->json([
				'success' => false,
				'message' => 'Failed to reset password.'
			], 500);
		}
	}
	public function payroll(Request $request)
	{
		// Validate the incoming request
		$validator = Validator::make($request->all(), [
			'staff_id' => 'required|exists:user,id',
			'page'     => 'integer|min:1',
			'limit'    => 'integer|min:1|max:100',
		]);
		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'errors' => $validator->errors()->first()
			], 400);
		}

		$staffId = $request->input('staff_id');
		$limit   = $request->input('limit', 10);
		$page    = $request->input('page', 1);
		$staff   = User::find($staffId);
		$homeId  = $staff->home_id;

		// 1. Efficiently find unique week start dates (Mondays) from both tables first
		// This avoids loading thousands of full records into memory at once
		$weekKeysShifts = ScheduledShift::where('staff_id', $staffId)
			->selectRaw("DISTINCT DATE_SUB(start_date, INTERVAL WEEKDAY(start_date) DAY) as week_key")
			->pluck('week_key');

		$weekKeysManual = Timesheet::where('staff_id', $staffId)
			->whereNull('shift_id')
			->selectRaw("DISTINCT DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY) as week_key")
			->pluck('week_key');

		$allUniqueWeeks = $weekKeysShifts->merge($weekKeysManual)
			->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
			->unique()
			->sortDesc()
			->values();

		$total = $allUniqueWeeks->count();
		$paginatedWeekKeys = $allUniqueWeeks->forPage($page, $limit);

		if ($paginatedWeekKeys->isEmpty()) {
			return response()->json([
				'success' => true,
				'data'    => [],
				'pagination' => [
					'total'        => $total,
					'current_page' => (int)$page,
					'per_page'     => (int)$limit,
					'last_page'    => (int)ceil($total / $limit),
				],
				'message' => 'No more payroll data found.'
			], 200);
		}

		// Identify the precise date range to load only the 10 weeks needed from DB
		$maxDateRange = Carbon::parse($paginatedWeekKeys->first())->endOfWeek()->format('Y-m-d H:i:s');
		$minDateRange = Carbon::parse($paginatedWeekKeys->last())->startOfWeek()->format('Y-m-d H:i:s');

		// 2. Load Timesheets for the target 10 weeks
		$timesheets = Timesheet::where('staff_id', $staffId)
			->whereIn('status', ['approved', 'processed'])
			->where(function ($q) use ($minDateRange, $maxDateRange) {
				$q->whereBetween('created_at', [$minDateRange, $maxDateRange])
					->orWhereHas('shift', fn($sq) => $sq->whereBetween('start_date', [substr($minDateRange, 0, 10), substr($maxDateRange, 0, 10)]));
			})
			->with(['category', 'shift.shiftCategory'])
			->get()
			->map(function ($t) use ($homeId, $staff) {
				$date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
				$start = Carbon::parse($date . ' ' . $t->clock_in);
				$end = Carbon::parse($date . ' ' . $t->clock_out);

				if ($end->lessThan($start)) {
					$end->addDay();
				}

				$t->duration_hours = $start->diffInMinutes($end) / 60;
				$t->week_key = $start->startOfWeek()->format('Y-m-d');
				$t->week_label = "Week " . $start->format('W') . " - " . $start->format('F Y');
				$t->week_range = $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y');

				// Rate logic
				$categoryName = ($t->category->name ?? ($t->shift->shiftCategory->name ?? ''));
				$normalizedCategory = strtolower(trim($categoryName));
				$rate = 0;

				if ($normalizedCategory == 'general' || empty($normalizedCategory)) {
					$rate = $staff->hourly_rate ?? 0;
				} else {
					$payRateType = PayRateType::where('type_name', $categoryName)->where('home_id', $homeId)->where('is_deleted', 0)->first();
					if ($payRateType) {
						$payRate = PayRate::where('rate_type_id', $payRateType->id)->where('access_level_id', $staff->access_level)->where('home_id', $homeId)->where('is_deleted', 0)->first();
						$rate = $payRate ? $payRate->pay_rate : ($staff->hourly_rate ?? 0);
					} else {
						$rate = $staff->hourly_rate ?? 0;
					}
				}

				$t->gross_pay = $t->duration_hours * $rate;
				return $t;
			});

		// 3. Load Pending Shifts for the target 10 weeks
		$pendingShifts = ScheduledShift::where('staff_id', $staffId)
			->where('status', '!=', 'approved')
			->whereBetween('start_date', [substr($minDateRange, 0, 10), substr($maxDateRange, 0, 10)])
			->get()
			->map(function ($s) {
				$start = Carbon::parse($s->start_date . ' ' . $s->start_time);
				$end = Carbon::parse($s->start_date . ' ' . $s->end_time);
				if ($end->lessThan($start)) $end->addDay();
				$s->duration_hours = $start->diffInMinutes($end) / 60;
				$s->week_key = $start->startOfWeek()->format('Y-m-d');
				return $s;
			});

		// 4. Summarize for Output (Mapping only the requested 10 week keys)
		$payrollGroups = $paginatedWeekKeys->map(function ($key) use ($timesheets, $pendingShifts, $staffId) {
			$weekT = $timesheets->where('week_key', $key);
			$weekS = $pendingShifts->where('week_key', $key);

			$start = Carbon::parse($key);
			return [
				'week_label'    => "Week " . $start->format('W') . " - " . $start->format('F Y'),
				'week_range'    => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
				'pay_date'      => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
				'total_gross'   => number_format($weekT->sum('gross_pay'), 2),
				'total_hours'   => number_format($weekT->sum('duration_hours'), 1),
				'approved_hours' => number_format($weekT->where('status', 'approved')->sum('duration_hours'), 1),
				'pending_hours' => number_format($weekS->sum('duration_hours'), 1),
				'shift_count'   => $weekT->count() + $weekS->count(),
				'status'        => ($weekT->count() > 0 && $weekT->where('status', 'approved')->count() == 0) ? 'processed' : 'pending',
				'week_key'      => $key,
				'payslip_url'   => url('api/staff/staff-payslip/' . $staffId . '/' . $key)
			];
		})->values();

		return response()->json([
			'success' => true,
			'data'    => $payrollGroups,
			'pagination' => [
				'total'        => $total,
				'current_page' => (int)$page,
				'per_page'     => (int)$limit,
				'last_page'    => (int)ceil($total / $limit),
			],
			'message' => 'Payroll data fetched successfully.'
		], 200);
	}

	public function staffPayslip($staff_id, $week_key)
	{
		// This fetches a single user's data for a specific week.
		$timesheets = Timesheet::where('staff_id', $staff_id)
			->where('status', 'processed')
			->whereHas('shift', function ($query) use ($week_key) {
				$query->whereBetween('start_date', [
					Carbon::parse($week_key)->startOfWeek(),
					Carbon::parse($week_key)->endOfWeek()
				]);
			})
			->with(['staff', 'category', 'shift.shiftCategory'])
			->get()
			->map(function ($t) {
				$date = $t->shift ? $t->shift->start_date : $t->created_at->format('Y-m-d');
				$start = Carbon::parse($date . ' ' . $t->clock_in);
				$end = Carbon::parse($date . ' ' . $t->clock_out);
				if ($end->lessThan($start)) $end->addDay();
				$t->duration_hours = $start->diffInMinutes($end) / 60;
				$t->gross_pay = $t->duration_hours * ($t->staff->hourly_rate ?? 0);
				return $t;
			});

		if ($timesheets->isEmpty()) abort(404, 'Payslip not found.');

		$start = Carbon::parse($week_key);
		$group = [
			'week_label' => "Week " . $start->format('W') . " - " . $start->format('F Y'),
			'week_range' => $start->startOfWeek()->format('M d') . " - " . $start->endOfWeek()->format('M d, Y'),
			'week_key' => $week_key,
			'total_gross' => $timesheets->sum('gross_pay'),
			'pay_date' => $start->endOfWeek()->addDays(5)->format('l, M d, Y'),
			'home_name' => Home::getHomeById($timesheets->first()->staff->home_id ?? null),
			'staff_breakdown' => [
				[
					'name'  => $timesheets->first()->staff->name ?? 'Unknown',
					'hours' => number_format($timesheets->sum('duration_hours'), 1),
					'gross' => number_format($timesheets->sum('gross_pay'), 2)
				]
			]
		];

		return view('frontEnd.roster.payroll_finance.payroll_report', compact('group'));
	}
}
