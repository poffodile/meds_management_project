<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use DB,Auth,Hash;
use App\ServiceUserAFC;
use App\Models\suUserCourse;

class ServiceUser extends Model
{
    protected $table = 'service_user';


    public static function get_afc_status($service_user_id = null) {

        $service_user = ServiceUser::where('id',$service_user_id)->where('home_id',Auth::user()->home_id)->first();

        if(!empty($service_user)){

            $afc = ServiceUserAFC::where('service_user_id',$service_user_id)
                                ->where('home_id',Auth::user()->home_id)
                                ->orderBy('id','desc')
                                ->first(); 
    
            if(!empty($afc)){
                $afc_status = $afc->afc_status;
            } else{
                //set status = 1, by default
                $afc_status = 1;
            }
            return $afc_status;            
        }
    }

    //send set password link to user
    public static function sendCredentials($user_id = null){

        $user           = ServiceUser::where('id',$user_id)->first();

        $home_security_policy = Home::where('id',$user->home_id)->value('security_policy');

        $random_no      = rand(111111,999999);

        $user->password = Hash::make($random_no);

        $company_name = 'SCITS set Password Mail';
        $email        = $user->email;
        $name         = $user->name;
        $user_name    = $user->user_name;        
        $password     = $random_no;        

        /*echo '$user_name = '.$user_name;
        echo '$random_no = '.$random_no;
        die;*/
        if($user->save())
        {  
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) 
            {
                Mail::send('emails.service_user_send_password_mail', ['name'=>$name, 'user_name'=>$user_name, 'password'=>$password,'home_security_policy'=>$home_security_policy], function($message) use ($email,$company_name)
                {
                    $message->to($email, $company_name)->subject('SCITS Welcome');
                });
                return true; 
            } 
        }
        return false;
    }

    public static function getLongLat($address)
    {
        $add=str_replace(' ','+',$address);
        
        $api_key = env('GOOGLE_MAP_API_KEY');
        $request = "https://maps.googleapis.com/maps/api/geocode/json?address=$add&key=".$api_key;
        $ch = curl_init($request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($ch);
        $arr = json_decode($response, true);
        return($arr); 
    }

    public static function getLocationInterval($service_user_id) { 
        $location_get_interval = ServiceUser::where('id',$service_user_id)->value('location_get_interval');
        if($location_get_interval === null){
            $location_get_interval = DEFAULT_LOCATION_RECALL_TIME;
        }
        return $location_get_interval;
    }

    public static function getServiceUserByResidentialId($department)
    {
        return self::where('home_id', Auth::user()->home_id)->where('department', $department)->where('status', 1)->where('is_deleted', 0)->count();
    }
    public function courses(){
        return $this->hasMany(suUserCourse::class, 'su_user_id', 'id');
    }

}