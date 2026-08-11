<?php

namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\User, App\ServiceUser, App\Admin, App\Home, App\LogBook;
use Hash, Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Services\AuthenticationSecurityService;

class UserController extends Controller
{

	public function login(Request $request)
	{
		// echo "check";
		// die();
		if (Auth::check()) {
			return redirect('/roster');
			// return redirect('/');
		}
		if ($request->isMethod('post')) {
			$data = $request->validate([
				'username' => ['required', 'string', 'max:255'],
				'password' => ['required', 'string', 'max:1024'],
				'home' => ['required', 'integer'],
			]);
			$data['username'] = trim($data['username']);
			$throttleKey = $this->loginThrottleKey($request, $data['username']);

			if (RateLimiter::tooManyAttempts($throttleKey, config('auth_security.max_attempts'))) {
				return redirect()->back()->with('error', $this->loginFailureMessage());
			}

			$username 	  = $data['username'];
			$hme_id 	  = $data['home'];
			$current_date = date('m/d/Y');

			// $current_date = '10/03/2018';
			// echo "<pre>"; print_r($current_date);  
			$user_info 	= user::select(
				'id',
				'home_id',
				'admn_id',
				'user_type',
				'login_date',
				'login_home_id',
				'failed_login_attempts',
				'locked_until'
			)
				->where('user_name', $username)
				->where('is_deleted', '0')
				->where('status', '1')
				->first();
			//echo "<pre>"; print_r($user_info->login_date); 
			//echo "<pre>"; print_r($user_info);  

			if (!empty($user_info)) {
				if (app(AuthenticationSecurityService::class)->isLocked($user_info)) {
					return redirect()->back()->with('error', $this->loginFailureMessage());
				}
				$login_ip = $request->ip();
				// print_r($login_ip);die;
				$assigned_homes = explode(',', $user_info->real_home_id);
				if (in_array($hme_id, $assigned_homes)) {
					if ($request->isMethod('post')) {
						$data = $request->input();
						if ($user_info->user_type != 'N') {
							if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'admn_id' => $user_info->admn_id])) {
								// echo "<pre>"; print_r($user_info); die; 
								$new_home_ids = $hme_id . ',' . $user_info->home_id;
								$new_home_ids = implode(',', array_unique(explode(',', $new_home_ids)));
								$update_home_id = User::where('user_name', $username)->update(['home_id' => $new_home_ids]);
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
								$last_activity = Auth::user()->last_activity_time;
								$last_activity = Carbon::parse($last_activity);
								$diff_mint     = $last_activity->diffInMinutes();
								if ($logged_in == '1' && $diff_mint < 60 && $login_ip != Auth::user()->login_ip) {
									// $last_activity = Auth::user()->last_activity_time;
									$current_time  = date('Y-m-d H:i:s');
									// $last_activity = Carbon::parse($last_activity);
									// $diff_mint     = $last_activity->diffInMinutes();
									if ($diff_mint > SESSION_TIMEOUT) {
									} else {
										Auth::logout();
										Session::put('pending_login', [
											'user_id' => $user_info->id,
											'home_id' => (int) $data['home'],
											'expires_at' => now()->addMinutes(5)->timestamp,
										]);
										// return redirect()->back()->with('error', 'You are already logged in from some other device.');
										return redirect()->back()->with('login_error', 'This account is currently logged in on another device.Do you want to log out from the other device and continue logging in here?');
									}
								}
								$session_id_update = User::find(Auth::user()->id);
								$session_id_update->login_ip = $login_ip;
								$session_id_update->save();
								$this->completeLogin($request, Auth::user(), $username);
								User::setUserLogInStatus(1);
								//echo csrf_token(); die;
								//echo "222"; die;
								$this->handleManagerSession($hme_id);
								return redirect('/roster')->with('success', 'Welcome back ' . Auth::user()->user_name);
								// return redirect('/roster/')->with('success', 'Welcome back ' . Auth::user()->user_name);
							} else {
								return $this->failedLogin($request, $user_info, $username);
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
									$last_activity = Auth::user()->last_activity_time;
									$last_activity = Carbon::parse($last_activity);
									$diff_mint     = $last_activity->diffInMinutes();
									if ($logged_in == '1' && $diff_mint < 60 && $login_ip != Auth::user()->login_ip) {
										// $last_activity = Auth::user()->last_activity_time;
										$current_time  = date('Y-m-d H:i:s');
										// $last_activity = Carbon::parse($last_activity);
										// $diff_mint     = $last_activity->diffInMinutes();
										if ($diff_mint > SESSION_TIMEOUT) {
										} else {
											Auth::logout();
											Session::put('pending_login', [
												'user_id' => $user_info->id,
												'home_id' => (int) $data['home'],
												'expires_at' => now()->addMinutes(5)->timestamp,
											]);
											return redirect()->back()->with('login_error', 'This account is currently logged in on another device.Do you want to log out from the other device and continue logging in here?');
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
									$session_id_update = User::find(Auth::user()->id);
									$session_id_update->login_ip = $login_ip;
									$session_id_update->save();
									$this->completeLogin($request, Auth::user(), $username);
									User::setUserLogInStatus(1);
									//echo csrf_token(); die;
									// return redirect('/roster/')->with('success', 'Welcome back ' . Auth::user()->user_name);
									$this->handleManagerSession($hme_id);
									return redirect('/roster')->with('success', 'Welcome back ' . Auth::user()->user_name);
								} else {  //echo "string3"; die;
									return $this->failedLogin($request, $user_info, $username);
								}
							} else {  //echo "string4"; die;
								return $this->failedLogin($request, $user_info, $username);
							}
						}
					} else {
						app(AuthenticationSecurityService::class)->record(
							$request,
							'login_wrong_service',
							false,
							$user_info,
							$username,
							['home_id' => (int) $hme_id]
						);
						return redirect()->back()->with('error', $this->loginFailureMessage());
					}
				}
			} else {
				RateLimiter::hit($throttleKey, config('auth_security.decay_seconds'));
				app(AuthenticationSecurityService::class)->record(
					$request,
					'login_failed',
					false,
					null,
					$username
				);
				return redirect()->back()->with('error', $this->loginFailureMessage());
			}
		return view('frontEnd.login');
	}
	public function yes_logout(Request $request)
	{
		$pending = Session::pull('pending_login');

		if (! is_array($pending) || ($pending['expires_at'] ?? 0) < now()->timestamp) {
			return redirect('/login')->with('error', 'Please sign in again.');
		}

		$user = User::query()
			->whereKey($pending['user_id'] ?? 0)
			->where('status', 1)
			->where('is_deleted', 0)
			->first();

		if (! $user) {
			return redirect('/login')->with('error', 'Please sign in again.');
		}

		$user->forceFill([
			'login_ip' => null,
			'session_token' => null,
			'logged_in' => 0,
		])->save();

		Auth::login($user);
		$request->session()->regenerate();
		$this->handleManagerSession((int) $pending['home_id']);
		$this->completeLogin($request, $user, $user->user_name);
		User::setUserLogInStatus(1);

		return redirect('/roster')->with('success', 'Welcome back '.$user->user_name);
	}
	public function no_logout()
	{
		Session::forget('pending_login');
		return response()->json(['success' => true, 'message' => 'Session Deleted']);
	}
	function login_staff_user($data, $user_info)
	{
		$current_date = date('m/d/Y');
		//$current_date = '09/01/2018';
		if (Auth::attempt(['user_name' => $data['username'], 'password' => $data['password'], 'home_id' => $data['home']])) {
			//check is user already logged in
			$logged_in = Auth::user()->logged_in;
			$last_activity = Auth::user()->last_activity_time;
			$last_activity = Carbon::parse($last_activity);
			$diff_mint     = $last_activity->diffInMinutes();
			if ($logged_in == '1' && $diff_mint < 60) {
				// $last_activity = Auth::user()->last_activity_time;
				$current_time  = date('Y-m-d H:i:s');
				// $last_activity = Carbon::parse($last_activity);
				// $diff_mint     = $last_activity->diffInMinutes();
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
			$this->completeLogin(request(), Auth::user(), $data['username']);
			User::setUserLogInStatus(1);
			//echo csrf_token(); die;
			return redirect('/roster')->with('success', 'Welcome back ' . Auth::user()->user_name);
		} else {
			return $this->failedLogin(request(), $user_info, $data['username']);
		}
	}
	public function logout(Request $request)
	{

		if (Auth::check()) {
			User::setUserLogInStatus(0);
			$user = Auth::user();
			$user->login_ip = null;
			$user->save();
			Auth::logout();
			$request->session()->invalidate();
			$request->session()->regenerateToken();
		}
		return redirect('/login');
	}
	public function show_set_password_form(Request $request, string $token)
	{
		$security = app(AuthenticationSecurityService::class);
		$passwordToken = $security->validPasswordToken($token);
		$user = $passwordToken && $passwordToken->authenticatable_type === 'user'
			? $security->accountModel($passwordToken)
			: null;

		if (! $user || $user->is_deleted || ! $user->status) {
			return redirect('/login')->with('error', 'This password link is invalid or has expired.');
		}

		$user_name = $user->user_name;
		return view('frontEnd.user_set_password', compact('token', 'user_name'));
	}
	public function set_password(Request $request)
	{
		$data = $request->validate([
			'token' => ['required', 'string', 'size:64'],
			'password' => [
				'required',
				'confirmed',
				Password::min(12)->mixedCase()->numbers()->symbols(),
			],
		]);

		$security = app(AuthenticationSecurityService::class);
		$passwordToken = $security->validPasswordToken($data['token']);
		$user = $passwordToken && $passwordToken->authenticatable_type === 'user'
			? $security->accountModel($passwordToken)
			: null;

		if (! $user || $user->is_deleted || ! $user->status) {
			return redirect('/login')->with('error', 'This password link is invalid or has expired.');
		}

		$security->consumePasswordToken($passwordToken, $user, $request, Hash::make($data['password']));

		return redirect('/login')->with('success', 'Your password has been set successfully.');
	}
	public function get_homes(Request $request, $company_name = null)
	{
		$admin_id = Admin::where('company', 'like', $company_name)->where('is_deleted', 0)->value('id');

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
		Log::info('check_username_exists called', $request->all());

		$username = $request->username;
		$userId   = $request->staff_id; // null on add, filled on edit

		$exists = User::where('user_name', $username)
			->when($userId, function ($query) use ($userId) {
				$query->where('id', '!=', $userId); // 👈 ignore current user
			})
			->exists();

		// jQuery validate expects true or false
		return response()->json(!$exists);

		// $data = $request->input();

		// $user_name = '';
		// if (is_array($data)) {
		// 	$user_name_arr = array_values($data);
		// 	$user_name = $user_name_arr[0];
		// }
		// $response = Home::userNameUnique($user_name);
		// echo json_encode($response);
		// //echo $response; die;

	}

	// code given by Ethan start
	public function switch_home()
	{
		// return "Hello";
		$admin_id = Admin::where('id', Auth::user()->admn_id)->where('is_deleted', 0)->value('id');
		$homes = Home::select('id', 'title')->where('admin_id', $admin_id)->where('is_deleted', '0')->get()->toArray();
		return view('frontEnd.switch_home', compact('admin_id', 'homes'));
	}

	public function switch_home_submit(Request $request)
	{
		$raw_home_id = Auth::user()->getAttributes()['home_id'] ?? '';
		$allowed_ids = array_filter(explode(',', str_replace(' ', '', $raw_home_id)));

		if (Auth::check() && count($allowed_ids) > 1) {
			if (in_array($request->home, $allowed_ids)) {
				Session::put('active_home_id', $request->home);
				return redirect('/roster')->with('success', 'Home switched successfully.');
			}
		}

		$previouHome = User::where('id', Auth::user()->id)->value('home_id');
		$array = [$request->home];
		$array = array_merge($array, [$previouHome]);
		$string = implode(',', $array);

		User::where('id', Auth::user()->id)->update(['home_id' => $string]);

		return redirect('/roster');
	}

	private function completeLogin(Request $request, User $user, string $identifier): void
	{
		$request->session()->regenerate();
		RateLimiter::clear($this->loginThrottleKey($request, $identifier));
		app(AuthenticationSecurityService::class)->registerSuccess($user, $request, $identifier);
	}

	private function failedLogin(Request $request, User $user, string $identifier)
	{
		Auth::logout();
		RateLimiter::hit(
			$this->loginThrottleKey($request, $identifier),
			config('auth_security.decay_seconds')
		);
		app(AuthenticationSecurityService::class)->registerFailure($user, $request, $identifier);

		return redirect()->back()->with('error', $this->loginFailureMessage());
	}

	private function loginThrottleKey(Request $request, string $identifier): string
	{
		return 'staff-login:'.hash('sha256', Str::lower(trim($identifier)).'|'.$request->ip());
	}

	private function loginFailureMessage(): string
	{
		return 'We could not sign you in with those details. Please check them or try again later.';
	}
	// code given by Ethan End

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
	private function handleManagerSession($selected_home_id)
	{
		if (Auth::check() && in_array(Auth::user()->user_type, ['M', 'CM', 'O', 'A'])) {
			$user = Auth::user();
			// Get raw home_id (using real_home_id accessor to support O/A/M/CM dynamically)
			$raw_home_id = $user->real_home_id;
			Session::put('allowed_home_ids', explode(',', $raw_home_id));
			Session::put('active_home_id', $selected_home_id);
		}
	}
}
