<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Auth;
use App\User, App\ServiceUser, App\Admin;


class Home extends Model
{
    protected $table = 'home';  


    public static function userNameUnique($user_name = null) {

    	//return true if not exists
    	//return false if exists

    	//username check in user table
    	$count = User::where('user_name',$user_name)->count();
    	if($count > 0) {
        	return false;

        } else {
    
	    	//username check in service_user table
        	$count = ServiceUser::where('user_name',$user_name)->count();
        	if($count > 0) {
	        	return false;
	
	        }/*else {
	
	        	$count = Admin::where('user_name',$user_name)->count();
	        	if($count > 0) {
		           echo json_encode(false);	  	 //  for jquery validations
		        }else {
	            	echo json_encode(true);      //  for jquery validations
	            }
	       	}*/
        } 
        return true;
    }

    //---------------{{ FRONTEND }} Forgot Password Send Email (for Login Page)------------------
    public static function check_email_exists($email = null) {

        //email check in user table
        $user_email = User::where('is_deleted', '0')->where('email', $email)->value('email');
        if(!empty($user_email)) {

            return true;

        } else {
            
            return false;
        } 
    }

    //---------------{{ BACKEND }} Forgot Password Send Email (for Login Page)--------------------
    public static function check_admin_email_exists($email = null) {

        // email check in user table
        $admin_email = Admin::where('is_deleted', '0')->where('email', $email)->value('email');

        if(!empty($admin_email)) {

            return true;
        } else {
            
            return false;
        } 
    }

    public static function getHomeFromConpany($Id){
        return Home::where('admin_id', $Id)->where('is_deleted', 0)->get();
    }


    public static function getCompanyIdFromHome(){
        $home_id = Auth::user()->home_id;
        return self::where('id', $home_id)->value('admin_id');
    }
}