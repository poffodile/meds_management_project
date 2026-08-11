<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Notifications\Notifiable;
//use Illuminate\Foundation\Auth\Admin as Authenticatable;
use Illuminate\Support\Facades\Mail;
use App\Admin;

class Admin extends Model
{	
	//use Notifiable;
    protected $table = 'admin';


    public static function sendCredentials($system_admin_id = null) {

		$admin       = Admin::where('id',$system_admin_id)->first();
		// echo "<pre>";
		// print_r($admin); 
		// die;
		if($admin->home_id != NULL) {
			$home_security_policy   = Home::where('id', $admin->home_id)->value('security_policy');
		} else {
			$home_security_policy   = '';
		}

		$random_no  			= rand(111111,999999);
		$security_code 			= base64_encode(convert_uuencode($random_no));
		$system_admin_id    	= base64_encode(convert_uuencode($admin->id));
		$email      			= $admin->email;
		$name       			= $admin->name;
		$admin->security_code 	= $random_no;
		$user_name  			= $admin->user_name;

		$company_name = 'SCITS set Password Mail';

		if($admin->save()) {
		    $set_password_url = url('/admin/set-password'.'/'.$system_admin_id.'/'.$security_code);
		    //return $set_password_url;
		    //echo $set_password_url; die;

		    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		        Mail::send('emails.user_set_password_mail', ['name'=>$name, 'user_name'=>$user_name, 'set_password_url'=>$set_password_url,'home_security_policy'=>$home_security_policy], function($message) use ($email,$company_name)
		        {
		            $message->to($email, $company_name)->subject('SCITS Welcome'); /*SCITS Set Password Mail*/
		        });
		        return 'Email sent to '.$name.' successfully.'; 
		    } 
		}
		return false;
	}
}


