<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth, DB;
use App\User, App\ServiceUser, App\Admin, App\Home, App\LogBook;
use Hash, Session;

class ForgotPasswordController extends Controller
{
	public function send_forgot_pass_link_mail(Request $request) {

        $data = $request->input();

        $user_id = DB::table('user')
                        ->where('email', $data['email'])
                        ->where('is_deleted','0')
                        ->value('id');
        
        if(!empty($user_id)) {

        	// send credentials for user
	        $response = User::sendCredentials($user_id);
	        if(!empty($response)) {
	        	return redirect('/login')->with('success', 'Email sent to ' .$data['email']. ' successfully.');
	        } else {
	        	return redirect('/login')->with('error', COMMON_ERROR);
	        }
	    } else {
		    return redirect('/login')->with('User not Found');
		    // return $resp = array('response'=>'not_found');
	    }
	}

	public function check_email_exists(Request $request){
    	
    	$data = $request->input();
    	$email = '';
    	if(is_array($data)){

	    	$email_arr = array_values($data);
	    	$email = $email_arr[0];
    	}

    	$response = Home::check_email_exists($email);
    	echo json_encode($response);
    	//echo $response; die;	   	
    }

	/*public function logout(){
		
		if(Auth::check()) {
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
    	   	
    }*/

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

    
}
