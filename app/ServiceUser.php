<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use DB, Hash;
use App\ServiceUserAFC;
use App\Models\suUserCourse;
use App\Models\SuUserPreferredCarers;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class ServiceUser extends Model
{
    use HasApiTokens;

    protected $table = 'service_user';

    protected $hidden = [
        'password',
        'security_code',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_reset' => 'boolean',
    ];


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
    public static function sendCredentials($user_id = null, string $purpose = 'password_setup')
    {
        $user = self::query()
            ->whereKey($user_id)
            ->where('is_deleted', 0)
            ->first();

        if (! $user || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $homeSecurityPolicy = Home::whereKey($user->home_id)->value('security_policy');
        $token = app(\App\Services\AuthenticationSecurityService::class)
            ->issuePasswordToken($user, request(), $purpose);
        $setPasswordUrl = url('/reset-password/'.$token);
        $companyName = defined('PROJECT_NAME') ? PROJECT_NAME : config('app.name');

        Mail::send('emails.user_set_password_mail', [
            'name' => $user->name,
            'user_name' => $user->user_name,
            'set_password_url' => $setPasswordUrl,
            'home_security_policy' => $homeSecurityPolicy,
        ], function ($message) use ($user, $companyName) {
            $message->to($user->email, $user->name)
                ->subject($companyName.' password setup');
        });

        return true;
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
        return self::where('home_id', Auth::user()->home_id)->where('status', 1)->where('is_deleted', 0)->count();
    }
    public function courses(){
        return $this->hasMany(suUserCourse::class, 'su_user_id', 'id');
    }
    public function carers(){
        return $this->hasMany(SuUserPreferredCarers::class, 'su_user_id', 'id');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(\App\Models\ServiceUserEmergencyContact::class, 'service_user_id', 'id');
    }


}
