<?php 
namespace App\Http\Controllers\Api\Staff;
use App\Http\Controllers\frontEnd\StaffManagementController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use App\ServiceUserMood, App\User, App\ServiceUser;

class MoodController extends StaffManagementController
{
   
    public function give_suggestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff_id' => 'required',
            'service_user_mood_id' => 'required',
            'suggestion' => 'required'                
        ]);

        if($validator->fails()) {
            return json_encode(array(
                    'result' => array(
                        'response' => false,
                        'message'  => FILL_FIELD_ERR,
                   )
            ));
        } else {
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $staff = User::where('id',$data['staff_id'])->count();
            if($staff > 0){

                $su_mood  = ServiceUserMood::where('id',$data['service_user_mood_id'])->first();
                
                if(!empty($su_mood)){
                    $su_home_id = ServiceUser::where('id',$su_mood->service_user_id)->value('home_id');
                    $staff_info = User::select('id', 'home_id', 'user_name')
                                            ->where('id',$data['staff_id'])
                                            ->first();
                    if($su_home_id != $staff_info['home_id']){
                        
                        return json_encode(array(
                            'result' =>array(
                                'response' => false,
                                'message' => UNAUTHORIZE_ERR_APP,
                            )
                        ));
                    }
              
                    $su_mood->suggestions = $data['suggestion'];
                    $su_mood->suggestion_provider_id = $data['staff_id'];
                    
                    if($su_mood->save()){
                        
                        $patient = ServiceUser::select('service_user.id','service_user.name','ud.device_token','ud.device_type','ud.user_type')
                                            ->join('user_device as ud','ud.user_id', 'service_user.id')
                                            ->where([ 'service_user.id' => $su_mood->service_user_id, 'ud.user_type' => '0', 'service_user.is_deleted' => '0' ])
                                            ->first();
                                            
                        $notify = [];
                        // foreach ($users as $key => $user) {
                            
                            $message     = 'Staff member '. $staff_info['user_name'].' gave suggestion to a mood.';
                            
                            $token       = array();
                            $token[]     = $patient['device_token'];
                            $device_type = $patient['device_type'];
                            $user_name   = $patient['name'];

                            //     //Android notification
                            $messageAndroid                      = array();
                            $messageAndroid['title']             = PROJECT_NAME;
                            $messageAndroid['message']           = $message;
                            // $messageAndroid['service_user_id']   = $data['service_user_id'];
                            $messageAndroid['subject']           = 'Notification';
                            $messageAndroid['notification_type'] = 'add_suggestion';
                            $notify[] =  $this->notifyFcm($token, $messageAndroid);
                        // }
                        
                        return json_encode(array(
                            'result' => array(
                                'response' => 'true',
                                'message' => 'Mood suggestion added successfully.'
                            )
                        ));
                    } else{
                        return json_encode(array(
                            'result' =>array(
                                'response' => false,
                                'message' => COMMON_ERROR,
                            )
                        ));
                    }
                }
            } else { 
                return json_encode(array(
                    'result' =>array(
                        'response' => false,
                        'message' => COMMON_ERROR,
                    )
                ));
            }
        }
    }
   
}