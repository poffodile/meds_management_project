<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ServiceUser, App\User, App\HomeLabel, App\UserQualification, App\Ethnicity, App\EarningSchemeLabel;
use DB;
use Auth;

class SystemManagementController extends Controller
{
	  
	public function system_management() {
        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $labels  = HomeLabel::getLabels($home_id);
        // echo '<pre>'; print_r($home_id); die;
        $earning_scheme_label = EarningSchemeLabel::where('deleted_at',null)
                                                  ->where('home_id',$home_id)
                                                  ->get()
                                                  ->toArray();
        // echo "<pre>"; print_r($earning_scheme_label); die;
        $su_ethnicity = Ethnicity::select('id','name')->where('is_deleted','0')->get()->toArray();
        // echo "<pre>"; print_r($su_ethnicity); die;
        return view('frontEnd.systemManagement.index', compact('labels','su_ethnicity','earning_scheme_label'));
	}

    public function add_service_user(Request $request){

        if($request->isMethod('post'))
        { 
            $data = $request->all();
            // print_r($data);
            // die;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            // $home_id = Auth::user()->home_id;
            // echo '<pre>'; print_r($_FILES);die;
            $date_of_birth = date('Y-m-d',strtotime($data['date_of_birth']));
            $user                   = new ServiceUser;
            $user->name             = $data['su_name'];
            $user->user_name        = $data['su_user_name'];
            $user->email            = $data['email'];
            $user->password         = '';
            $user->admission_number = $data['admission_number'];
            $user->phone_no         = $data['phone_no'];
            $user->date_of_birth    = $date_of_birth;
            $user->child_type       =  $data['child_type'];
            $user->room_type        =  $data['room_type'];
            $user->weekly_rate      =  $data['weekly_rate'];
            $user->subs             =  $data['subs'];
            $user->extra            =  $data['extra'];
            $user->start_date       =  date('Y-m-d', strtotime($data['start_date']));
            $user->local_authority  =  $data['local_authority'];
            $user->end_date         =  date('Y-m-d', strtotime($data['end_date']));
            $user->section          = $data['section'];
            $user->short_description= $data['short_description'];
            $user->height           = $data['height'];
            $user->weight           = $data['weight'];
            $user->hair_and_eyes    = $data['hair_and_eyes'];
            $user->markings         = $data['markings'];
            $user->ethnicity_id     = $data['ethnicity_id'];
            // $user->status        = $request->status;
            
            $user->home_id               = $home_id;
            $user->personal_info         = '';
            $user->education_history     = '';
            $user->bereavement_issues    = '';
            $user->drug_n_alcohol_issues = '';
            $user->mental_health_issues  = '';
            $user->current_location      = '';
            $user->previous_location     = '';
            $user->mobile                = '';
            /*$user->facebook    = '';
            $user->twitter    = '';
            $user->skype    = '';*/

            if(!empty($_FILES['image']['name']))
            {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time().'.'.$ext; 
               
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                {
                    $destination = base_path().serviceUserProfileImageBasePath; 
                    if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                    {
                        $user->image = $new_name;
                    }
                }
            }
            if(!isset($user->image)){
                $user->image = '';
            }

            if($user->save()) {
                if(isset($data['send_credentials'])) {
                    $response = ServiceUser::sendCredentials($user->id);
                } 
                return redirect()->back()->with('success', 'User added successfully.');
                } 
            else {
                     return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
        }     
    }
    
    public function add_staff_user(Request $request)
    {
        if($request->isMethod('post'))
        {    
            //echo "<pre>"; print_r($data); die;
            //echo "<pre>";
            /*print_r($_FILES);
            die;*/
            $data = $request->all();
            $date_of_joining = date('Y-m-d',strtotime($data['date_of_joining']));
            $date_of_leaving = date('Y-m-d',strtotime($data['date_of_leaving']));
            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
           
            $user                   = new User;
            $user->name             = $data['staff_name'];
            $user->user_name        = $data['staff_user_name'];
            $user->phone_no         = $data['staff_phone_no'];
            $user->email            = $data['staff_email'];
            $user->job_title        = $data['job_title'];
            $user->description      = $data['description'];
            $user->payroll          = $data['payroll'];
            
            $user->date_of_joining  = $date_of_joining;
            $user->date_of_leaving  = $date_of_leaving;
            $user->holiday_entitlement = $data['holiday_entitlement'];
            $user->password         = '';
            $user->status           = 1;
            $user->home_id          = $home_id;
            $user->personal_info    = '';
            $user->banking_info     = '';
            $user->qualification_info = '';
            $user->current_location   = '';

            if(!empty($_FILES['image']['name']))
            {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time().'.'.$ext; 
               
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                {
                    $destination = base_path().userProfileImageBasePath; 
                    if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                    {
                        $user->image = $new_name;
                    }
                }
            }

            if(!isset($user->image)){
                $user->image = '';
            }

            if (isset($data['line_manager'])){
                $user->access_level     = 2; // line manager
            }else{
                $user->access_level     = 3; //staff
            }
            /*//if checkbox is checked
            if(isset($data['assign_right_check'])){
                //save access rights according to access level
                $access_level_info  = AccessLevel::select('id','access_rights')
                                    ->where('home_id', $home_id)
                                    ->where('id', $user->access_level)
                                    ->first();

                if(!empty($access_level_info)){
                    $user->access_rights = $access_level_info->access_rights;
                }
            }*/
            if($user->save()) {
                User::saveQualification($data,$user->id);
               
                if (isset($data['send_credentials']))  {
                    $response = User::sendCredentials($user->id);
                }
                return redirect()->back()->with('success', 'Staff added successfully.');
            }else{
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
    }
    
    public function delete_certificate($id=null){
        UserQualification::where('id',$id)->update(['is_deleted'=>1]);
        return $response = array("status" => "ok");
    }
}