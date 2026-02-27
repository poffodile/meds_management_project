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
		$qualifications = $request->input('qualification', []);
		$flatQualifications = collect($qualifications)
			->flatten(1)
			->values()
			->all();

		$data = $request->all();
		$data['qualification'] = $flatQualifications;
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

		$result = UserQualification::saveQualification($flatQualifications, $request->user_id);

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
			$data[] = [
				'id' => $val->id,
				'user_id' => $val->user_id,
				'name' => $val->name,
				'image' => url('public/images/userQualification') . '/' . $val->image,
			];
		}
		return response()->json([
			'success' => true,
			'data' => $data,
			'message' => 'Qualification List.'
		], 200);
	}

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
		$shifts = ScheduledShift::where('staff_id', $staffId);
		// echo "<pre>"; print_r($shifts); die;
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
		// echo "<pre>";
		// print_r($today);
		// print_r($londonTime->format('H:i:s'));
		// die;
		$currentTime = $londonTime->format('H:i:s');
		$todayShift = ScheduledShift::where('staff_id', $staffId)
			->where('start_date', $today)
			->where('status', 'assigned')
			->where('start_time', '<=', $currentTime)
			->where('end_time', '>=', $currentTime)
			->select('start_time', 'end_time', 'tasks', 'notes', 'id')
			->first();
		// dd($todayShift);

		if ($todayShift) {
			$todayShift->start_time = Carbon::parse($todayShift->start_time)->format('H:i');
			$todayShift->end_time   = Carbon::parse($todayShift->end_time)->format('H:i');
			$data['today_shift'] = $todayShift;
		} else {
			$data['today_shift'] = null;
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
}
