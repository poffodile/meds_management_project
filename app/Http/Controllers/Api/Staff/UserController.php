<?php

namespace App\Http\Controllers\Api\Staff;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth, DB;
use App\User, App\ServiceUser, App\Admin, App\Home, App\LogBook;
use Hash, Session;
use Carbon\Carbon;

class UserController extends Controller
{
	  
	public function login(Request $request){
		
		if(Auth::check()){
			return redirect('/');
		}

		if($request->isMethod('post')){
			$data 		  = $request->input();
			$username 	  = $data['username']; 
			$hme_id 	  = $data['home']; 
			$current_date = date('m/d/Y');
			// $current_date = '10/03/2018';
			// echo "<pre>"; print_r($current_date);  

			$user_info 	= user::select('id','home_id','admn_id','user_type','login_date','login_home_id')
								->where('user_name',$username)
								->where('is_deleted','0')
								->first();
			//echo "<pre>"; print_r($user_info->login_date); 
			//echo "<pre>"; print_r($user_info->toArray());  


			if(!empty($user_info)){
				$searchString = ',';
				//$homde_id = 1,2
				if(strpos($user_info->home_id, $searchString) !== false ) {
				    $array =  explode(',', $user_info->home_id);
					
					// echo "<pre>"; print_r($array); die;
				    if(in_array($hme_id,$array)){
						if($request->isMethod('post')){ 

							$data = $request->input();
							if($user_info->user_type != 'N'){
								if(Auth::attempt(['user_name'=>$data['username'], 'password'=>$data['password'],'admn_id'=>$user_info->admn_id])) { 	
									// echo "<pre>"; print_r($user_info); die; 
									$new_home_ids = $hme_id.','.$user_info->home_id;
							    	$new_home_ids = implode(',',array_unique(explode(',', $new_home_ids)));
							    	$update_home_id = User::where('user_name',$username)
							    						->update(['home_id'=> $new_home_ids]);
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
									if($logged_in == '1'){


										$last_activity = Auth::user()->last_activity_time;
							            $current_time  = date('Y-m-d H:i:s');

							            $last_activity = Carbon::parse($last_activity);
							            $diff_mint     = $last_activity->diffInMinutes();

							            if($diff_mint > SESSION_TIMEOUT){ 

							            } else{ 
											Auth::logout();
											return redirect()->back()->with('error','You are already logged in from some other device.');	
							            }
									}

									User::setUserLogInStatus(1);
									//echo csrf_token(); die;
									//echo "222"; die;
									return redirect('/')->with('success','Welcome back '.Auth::user()->user_name);
								}
							}elseif($user_info->user_type == 'N'){
								
								if(Auth::attempt(['user_name'=>$data['username'], 'password'=>$data['password'], 'login_home_id'=>$user_info->login_home_id ])){ 

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
									if($logged_in == '1'){


										$last_activity = Auth::user()->last_activity_time;
							            $current_time  = date('Y-m-d H:i:s');

							            $last_activity = Carbon::parse($last_activity);
							            $diff_mint     = $last_activity->diffInMinutes();

							            if($diff_mint > SESSION_TIMEOUT){ 

							            } else{ 
											Auth::logout();
											return redirect()->back()->with('error','You are already logged in from some other device.');	
							            }

									}
									
									//if another staff user login date is expired(user_info->login_date) then his home_id is updated 
									if(!empty($user_info->login_date)){ 
								    	if($current_date > $user_info->login_date){
									    	$home_id = substr($user_info->home_id,2);
									    	//echo "<pre>"; print_r($home_id); die; 
											$update  = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
								    		
								    		$this->login_staff_user($data,$user_info);
								    		//this function is used to login staff user with their previous home, not to assigned home because assigned staff user date is expired.
								    	}					
							    	}

									User::setUserLogInStatus(1);
									//echo csrf_token(); die;
									return redirect('/')->with('success','Welcome back '.Auth::user()->user_name);
								}
								else {  //echo "string3"; die;
									return redirect()->back()->with('error','Incorrect email or password combination.'); 
								}
							}	
							else { 
								return redirect()->back()->with('error','Incorrect email or password combination.'); 
							}
						}
					}
					else {    
						return redirect()->back()->with('error','Incorrect email or password combination.'); 
					}
				}else{ 
					if((!empty($user_info->login_date)) && ($user_info->login_date != NULL)){
						
						if($current_date == $user_info->login_date){

							if(Auth::attempt(['user_name'=>$data['username'], 'password'=>$data['password'], 'login_home_id'=>$user_info->login_home_id ])) { 
								
								//check is user already logged in
								$logged_in = Auth::user()->logged_in;
								if($logged_in == '1'){

									$last_activity = Auth::user()->last_activity_time;
						            $current_time  = date('Y-m-d H:i:s');

						            $last_activity = Carbon::parse($last_activity);
						            $diff_mint     = $last_activity->diffInMinutes();

						            if($diff_mint > SESSION_TIMEOUT){ 

						            } else{ 
										Auth::logout();
										return redirect()->back()->with('error','You are already logged in from some other device.');	
						            }
								}

								$home_id  = $user_info->login_home_id .','.$user_info->home_id;  
								$update   = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
								// echo "<pre>"; print_r($home_id); die;
								
								User::setUserLogInStatus(1);
								//echo csrf_token(); die;
								return redirect('/')->with('success','Welcome back '.Auth::user()->user_name);
							}
							else { 
								return redirect()->back()->with('error','Incorrect email or password combination.'); 
							}
						}/*else{
							$home_id = substr($user_info->home_id,2); 
							$update  = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
						}*///echo "string12345"; die;
					}

						//$this->login_staff_user($data,$user_info);
						if(Auth::attempt(['user_name'=>$data['username'], 'password'=>$data['password'], 'home_id'=>$data['home'] ])) { 

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
							if($logged_in == '1'){


								$last_activity = Auth::user()->last_activity_time;
					            $current_time  = date('Y-m-d H:i:s');

					            $last_activity = Carbon::parse($last_activity);
					            $diff_mint     = $last_activity->diffInMinutes();

					            if($diff_mint > SESSION_TIMEOUT){ 
					            } else{ 
									Auth::logout();
									return redirect()->back()->with('error','You are already logged in from some other device.');	
					            }

							}
							//if another staff user date is expired(user_info->login_date) then his home_id is updated 
							if(!empty($user_info->login_date)){ 
						    	if($current_date > $user_info->login_date){
							    	$home_id = substr($user_info->home_id,2);
							    	if($home_id == 0){ 
							    		$update  = User::where('id',$user_info->id)->update(['home_id'=>$user_info->home_id]);
							    	}else{
										$update  = User::where('id',$user_info->id)->update(['home_id'=>$home_id]);
							    	} 
						    	}					
					    	}

							User::setUserLogInStatus(1);
							//echo csrf_token(); die;
							return redirect('/')->with('success','Welcome back '.Auth::user()->user_name);
						}
						else {  
							return redirect()->back()->with('error','Incorrect email or password combination.'); 
						}
				}
			}else{ 
				return redirect()->back()->with('error','Incorrect email or password combination.');
			}
		}

		return view('frontEnd.login');
	}


	function login_staff_user($data,$user_info){

		$current_date = date('m/d/Y');
		//$current_date = '09/01/2018';
		if(Auth::attempt(['user_name'=>$data['username'], 'password'=>$data['password'], 'home_id'=>$data['home'] ])) { 

			//check is user already logged in
			$logged_in = Auth::user()->logged_in;
			if($logged_in == '1'){


				$last_activity = Auth::user()->last_activity_time;
	            $current_time  = date('Y-m-d H:i:s');

	            $last_activity = Carbon::parse($last_activity);
	            $diff_mint     = $last_activity->diffInMinutes();

	            if($diff_mint > SESSION_TIMEOUT){ 

	            } else{ 
					Auth::logout();
					return redirect()->back()->with('error','You are already logged in from some other device.');	
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
			return redirect('/')->with('success','Welcome back '.Auth::user()->user_name);
		}
		else {  
			return redirect()->back()->with('error','Incorrect email or password combination.'); 
		}
	}

	public function logout(){
		
		if(Auth::check()) {
			User::setUserLogInStatus(0);

			Auth::logout();

			Session::forget('LAST_ACTIVITY');
		}
		return redirect('/login');
	}

	public function show_set_password_form(Request $request, $user_id = null, $security_code = null){
		
		$decoded_user_id = convert_uudecode(base64_decode($user_id));
		$decoded_security_code = convert_uudecode(base64_decode($security_code));

		$count = User::where('id',$decoded_user_id)
						->where('security_code',$decoded_security_code)
						->first();
		
		if(!empty($count)){ 
			$user_name = $count->user_name;
			return view('frontEnd.user_set_password',compact('user_id','security_code','user_name'));
		} else{ 
			return redirect('/login')->with('error','This link has been already used.');		
		}
	}

	public function set_password(Request $request)
	{
		$data = $request->input();
		if(empty($data['password']))
		{
			return redirect()->back()->with('error','Please Enter Password');
		}
		else if($data['password'] != $data['confirm_password'])
		{
			return redirect()->back()->with('error', 'Password & confirm password does not matched.');
		}

		$user_id = convert_uudecode(base64_decode($data['user_id']));
		$security_code = convert_uudecode(base64_decode($data['security_code']));
	
		$user = User::where('id',$user_id)
						->where('security_code',$security_code)
						->first();

		$user->security_code = '';
		$user->password =	Hash::make($data['password']);
		//echo $data['password']; die;
		if($user->save()) 
		{ 
			return redirect('/login')->with('success','You have set your password successfully.');
		} 
		else
		{ 
			return redirect('/login')->with('error','Some error occured. Please try again later');			
		}

	}

    public function get_homes(Request $request, $company_name=null)
    {	
    	$admin_id = Admin::where('company','like',$company_name)->value('id');
    	
    	$homes = Home::select('id','title')->where('admin_id',$admin_id)->where('is_deleted','0')->get()->toArray();

    	if(!empty($homes))
    	{
    		foreach($homes as $home)
    		{
	    		echo '<option value="'.$home['id'].'">'.$home['title'].'</option>';
	    	}    
    	}
    	else
    	{
    		echo '';  
    	}
    	die;
    	return view('backEnd.login',compact('page', 'company_name'));
    }

    public function check_username_exists(Request $request){
    	
    	$data = $request->input();
    	
    	$user_name = '';
    	if(is_array($data)){
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


	public function addStaffUser(Request $request)
    {
        // ---------------------------
        // VALIDATION
        // ---------------------------
        $validator = Validator::make($request->all(), [
            'home_id'			  => 'required|string|max:255',
            'name'                => 'required|string|max:255',
            'user_name'           => 'required|string|max:255|unique:user,user_name',
            'email'               => 'required|email|max:255|unique:user,email',
            'job_title'           => 'nullable|string|max:255',
            'user_name'           => 'required|string|max:255',
            'phone_no'            => 'required|string|max:20',
            'description'         => 'nullable|string|max:500',
            'payroll'             => 'nullable|string|max:255',
            'date_of_joining'     => 'required|date',
            'date_of_leaving'     => 'nullable|date|after_or_equal:date_of_joining',
            'holiday_entitlement' => 'nullable|numeric|min:0|max:365',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'send_credentials'    => 'nullable|boolean',
			'password'			  => 'required|string|min:6',
			'job_title'           => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // ---------------------------
        // SECURITY CODE DECODING
        // ---------------------------
		if(isset($data['security_code'])){
			$security_code = convert_uudecode(base64_decode($data['security_code']));
		} 

        // ---------------------------
        // HASH PASSWORD
        // ---------------------------
        $password =	Hash::make($data['password']);

        // ---------------------------
        // CREATE NEW USER
        // ---------------------------
        $user = new User();
        $user->name                = $data['name'];
        $user->user_name           = $data['user_name'];
        $user->email               = $data['email'];
        $user->job_title           = $data['job_title'] ?? null;
        $user->access_level        = $data['access_level'] ?? null;
        $user->department          = $data['department'] ?? null;
        $user->description         = $data['description'] ?? '';
        $user->payroll             = $data['payroll'] ?? '';
        $user->holiday_entitlement = $data['holiday_entitlement'] ?? 0;
		$user->access_rights       = $data['access_rights'] ?? null;
        $user->status              = 1;
		$user->security_code       = $security_code ?? null;
		$user->is_deleted 		   = 0;
        $user->phone_no            = $data['phone_no'];
        $user->current_location    = $data['current_location'] ?? null;
        $user->date_of_joining     = date('Y-m-d', strtotime($data['date_of_joining']));
        $user->date_of_leaving     = !empty($data['date_of_leaving']) ? date('Y-m-d', strtotime($data['date_of_leaving'])) : null;
        $user->personal_info       = $data['personal_info'] ?? null;
        $user->banking_info        = $data['banking_info'] ?? null;
        $user->qualification_info  = $data['qualification_info'] ?? null;
		$user->last_activity_time = null;
		$user->logged_in		   = 0;
		$user->login_ip		       = null;
		$user->design_layout	   = 0;
		$user->company_id	       = null;
		$user->user_type 	       = 'N';
		$user->login_home_id       = null;
        $user->password            = $password ?? null;  
        $user->home_id             = $request->home_id;

        // ---------------------------
        // UPLOAD IMAGE
        // ---------------------------
        if (!empty($_FILES['image']['name'])) {
            $tmp_image  =   $_FILES['image']['tmp_name'];
            $image_info =   pathinfo($_FILES['image']['name']);
            $ext        =   strtolower($image_info['extension']);
            $new_name   =   time() . '.' . $ext;

            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                $destination = base_path() . userProfileImageBasePath;
                if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                    $user->image = $new_name;
                }
            }
        }

        if (!isset($user->image)) {
            $user->image = '';
        }

        // ---------------------------
        // SAVE USER
        // ---------------------------
        if ($user->save()) {

            // OPTIONAL: Save qualification
            User::saveQualification($data, $user->id);

            if (!empty($data['send_credentials'])) {
                User::sendCredentials($user->id);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Staff user added successfully.',
                'data'    => $user
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again.'
        ], 500);
    }

	public function setPassword(){
		
	}
}
