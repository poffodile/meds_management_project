<?php

namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ServiceUser, App\User, App\HomeLabel, App\UserQualification, App\Ethnicity, App\EarningSchemeLabel, App\Models\CompanyDepartment;
use App\Models\suUserCourse;
use App\Services\Staff\StaffService;
use DB, Auth;
use App\Services\Staff\UserEmergencyContactService;
use Carbon\Carbon;

class SystemManagementController extends Controller
{

    protected StaffService $staffService;
 
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function system_management()
    {
        // $home_id = Auth::user()->home_id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $labels  = HomeLabel::getLabels($home_id);
        // echo '<pre>'; print_r($home_id); die;
        $earning_scheme_label = EarningSchemeLabel::where('deleted_at', null)
            ->where('home_id', $home_id)
            ->get()
            ->toArray();
        $su_ethnicity = Ethnicity::select('id', 'name')->where('is_deleted', '0')->get()->toArray();
        $courses = $this->staffService->courses();
        $department = CompanyDepartment::getActiveCompanyDepartment();
        // echo "<pre>"; print_r($su_ethnicity); die;
        return view('frontEnd.systemManagement.index', compact('labels', 'su_ethnicity', 'earning_scheme_label', 'department', 'courses'));
    }

    // public function add_service_user(Request $request)
    // {

    //     if ($request->isMethod('post')) {
    //         $data = $request->all();
    //         // print_r($data);
    //         // die;
    //         $home_ids = Auth::user()->home_id;
    //         $ex_home_ids = explode(',', $home_ids);
    //         $home_id = $ex_home_ids[0];
    //         // $home_id = Auth::user()->home_id;
    //         // echo '<pre>'; print_r($_FILES);die;
    //         $date_of_birth = date('Y-m-d', strtotime($data['date_of_birth']));
    //         $user                   = new ServiceUser;
    //         $user->name             = $data['su_name'];
    //         $user->user_name        = $data['su_user_name'];
    //         $user->email            = $data['email'];
    //         $user->password         = '';
    //         $user->admission_number = $data['admission_number'];
    //         $user->phone_no         = $data['phone_no'];
    //         $user->date_of_birth    = $date_of_birth;
    //         $user->department       = $data['department'];
    //         $user->child_type       =  $data['child_type'] ?? null;
    //         $user->room_type        =  $data['room_type'] ?? null;
    //         $user->weekly_rate      =  $data['weekly_rate'];
    //         $user->subs             =  $data['subs'];
    //         $user->extra            =  $data['extra'];
    //         $user->start_date       =  date('Y-m-d', strtotime($data['start_date']));
    //         $user->local_authority  =  $data['local_authority'];
    //         $user->end_date         =  date('Y-m-d', strtotime($data['end_date']));
    //         $user->section          = $data['section'];
    //         $user->short_description = $data['short_description'];
    //         $user->height_unit           =  $data['height_unit'];
    //         $user->height_ft             =  $data['height_ft'];
    //         $user->height_in             =  $data['height_in'];
    //         $user->weight_unit           =  $data['weight_unit'];
    //         $user->weight                =  $data['weight'];
    //         $user->hair_and_eyes    = $data['hair_and_eyes'];
    //         $user->markings         = $data['markings'];
    //         $user->ethnicity_id     = $data['ethnicity_id'];
    //         // $user->status        = $request->status;

    //         $user->home_id               = $home_id;
    //         $user->personal_info         = '';
    //         $user->education_history     = '';
    //         $user->bereavement_issues    = '';
    //         $user->drug_n_alcohol_issues = '';
    //         $user->mental_health_issues  = '';
    //         $user->current_location      = '';
    //         $user->previous_location     = '';
    //         $user->mobile                = '';
    //         /*$user->facebook    = '';
    //         $user->twitter    = '';
    //         $user->skype    = '';*/

    //         if (!empty($_FILES['img_upload']['name'])) {
    //             $tmp_image  =   $_FILES['img_upload']['tmp_name'];
    //             $image_info =   pathinfo($_FILES['img_upload']['name']);
    //             $ext        =   strtolower($image_info['extension']);
    //             $new_name   =   time() . '.' . $ext;

    //             if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
    //                 $destination = base_path() . serviceUserProfileImageBasePath;
    //                 if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
    //                     $user->image = $new_name;
    //                 }
    //             }
    //         }
    //         if (!isset($user->image)) {
    //             $user->image = '';
    //         }

    //         if ($user->save()) {
    //             if (isset($data['send_credentials'])) {
    //                 $response = ServiceUser::sendCredentials($user->id);
    //             }
    //             return redirect()->back()->with('success', 'User added successfully.');
    //         } else {
    //             return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
    //         }
    //     }
    // }

    public function add_service_user(Request $request)
    {
 
        if ($request->isMethod('post')) {
            if (!$request->has('courses')) {
                return redirect()->back()->with('error', 'Please checked preferred carers');
            }
            $data = $request->all();    
            // echo "<pre>";print_r($data);
            // die;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $address = $data['street'] . ' ' . $data['city'] . ' ' . $data['postcode'];
            $service_user_latLong = $this->staffService->getLatLongFromAddress($address);

            // $home_id = Auth::user()->home_id;
            // echo '<pre>'; print_r($_FILES);die;
            $date_of_birth = date('Y-m-d', strtotime($data['date_of_birth']));
            if($request->has('suClientId') && $request->suClientId != ''){
                $user              = ServiceUser::find($request->suClientId);
                $successMessage = 'User updated successfully.';
            }else{
                $user              = new ServiceUser;
                $successMessage = 'User added successfully.';
            }
          
            $user->name             = $data['su_name'];
            $user->user_name        = $data['su_user_name'];
            $user->email            = $data['email'];
            $user->password         = '';
            $user->admission_number = $data['admission_number'];
            $user->phone_no         = $data['phone_no'];
            $user->date_of_birth    = $date_of_birth;
            $user->department       = $data['department'];
            $user->child_type       =  $data['child_type'] ?? null;
            $user->room_type        =  $data['room_type'] ?? null;
            // $user->weekly_rate      =  $data['weekly_rate'];
            // $user->subs             =  $data['subs'];
            // $user->extra            =  $data['extra'];
            $user->start_date       =  date('Y-m-d', strtotime($data['start_date']));
            // $user->local_authority  =  $data['local_authority'];
            $user->end_date         =  date('Y-m-d', strtotime($data['end_date']));
            $user->section          = $data['section'];
            $user->short_description = $data['short_description'];
            $user->height_unit       =  $data['height_unit'];
            $user->height_ft         =  $data['height_ft'];
            $user->height_in         =  $data['height_in'];
            $user->weight_unit       =  $data['weight_unit'];
            $user->weight            =  $data['weight'];
            $user->hair_and_eyes     = $data['hair_and_eyes'];
            $user->markings          = $data['markings'];
            $user->ethnicity_id      = $data['ethnicity_id'];
            $user->suMobility        = $data['suMobility'];
            $user->suFundingType     = $data['suFundingType'];
            $user->street            = $data['street'];
            $user->city              = $data['city'];
            $user->postcode          = $data['postcode'];
            $user->latitude          = $service_user_latLong['latitude'] ?? null;
            $user->longitude         = $service_user_latLong['longitude'] ?? null;
            $user->care_needs        = $data['care_needs'];
            $user->medical_notes     = $data['medical_notes'];
            $user->em_name           = $data['em_name'];
            $user->em_phone          = $data['em_phone'];
            $user->relationship      = $data['relationship'];
            $user->status            = $data['suStatus'] ?? 1;
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
 
            if (!empty($_FILES['img_upload']['name'])) {
                $tmp_image  =   $_FILES['img_upload']['tmp_name'];
                $image_info =   pathinfo($_FILES['img_upload']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time() . '.' . $ext;
 
                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . serviceUserProfileImageBasePath;
                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                        $user->image = $new_name;
                    }
                }
            }
            if (!isset($user->image) && $request->has('suClientId') && $request->suClientId == '') {
                $user->image = '';
            }
           
            if ($user->save()) {
                if ($request->has('courses') && is_array($request->courses)) {
                    foreach ($request->courses as $course) {
                        // if (isset($course['certificate'])) {
                        //     $imageName = time() . '_' . uniqid() . '.' . $course['certificate']->extension();
                        //     $course['certificate']->move(
                        //         public_path('images/userQualification'),
                        //         $imageName
                        //     );
                        //     $certificate_image = $imageName;
                        // } else {
                        //     $certificate_image = null;
                        // }
                        $certificate_image = null;
                        $su_user_course=new suUserCourse;
                        $su_user_course->su_user_id= $user->id;
                        $su_user_course->coursenumber= $course['course_id'];
                        $su_user_course->title= $course['title'];
                        $su_user_course->level= $course['level'];
                        $su_user_course->image= $course['course_image'];
                        $su_user_course->certificate= $certificate_image;
                        $su_user_course->description= $course['description'];
                        $su_user_course->save();
                    }
                }
                if (isset($data['send_credentials'])) {
                    $response = ServiceUser::sendCredentials($user->id);
                }
                return redirect()->back()->with('success', $successMessage);
            } else {
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
    }

    public function add_staff_user(Request $request)
    {
        if ($request->isMethod('post')) {
            //echo "<pre>";
            /*print_r($_FILES);
            die;*/
            $data = $request->all();
            // echo "<pre>"; print_r($data); die;
            $date_of_joining = date('Y-m-d', strtotime($data['date_of_joining']));
            $date_of_leaving = date('Y-m-d', strtotime($data['date_of_leaving']));
            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];

            $address = $data['street'] . ' ' . $data['city'] . ' ' . $data['postcode'];
            $staffLatLong = $this->staffService->getLatLongFromAddress($address);

            $user                   = new User;
            $user->name             = $data['staff_name'];
            $user->user_name        = $data['staff_user_name'];
            $user->phone_no         = $data['staff_phone_no'];
            $user->email            = $data['staff_email'];
            $user->job_title        = $data['job_title'];
            $user->street           = $data['street'];
            $user->city             = $data['city'];
            $user->postcode         = $data['postcode'];
            $user->latitude         = $staffLatLong['latitude'] ?? null;
            $user->longitude        = $staffLatLong['longitude'] ?? null;
            $user->department       = $data['department'];
            $user->description      = $data['description'];
            // $user->payroll          = $data['payroll'];
            $user->status           = $data['status'];
            $user->hourly_rate      = $data['hourly_rate'];
            $user->available_for_overtime = !empty($data['available_for_overtime']) ? 1 : 0;
            $user->max_extra_hours = !empty($data['available_for_overtime']) ? ($data['max_extra_hours'] ?? null) : null;
            $user->employment_type  = $data['employment_type'];
            $user->dbs_certificate_number = $data['dbs_certificate_number'];
            $user->dbs_expiry_date  = !empty($data['dbs_expiry_date'])
                                    ? Carbon::parse($data['dbs_expiry_date'])->format('Y-m-d')
                                    : null;
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

            if (isset($data['line_manager'])) {
                $user->access_level     = 2; // line manager
            } else {
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
            if ($user->save()) {
                User::saveQualification($data['qualifications'], $user->id);
                UserEmergencyContactService::saveContacts($user->id, $data['emergency_contact']);

                if (isset($data['send_credentials'])) {
                    $response = User::sendCredentials($user->id);
                }
                return redirect()->back()->with('success', 'Staff added successfully.');
            } else {
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
    }

    public function delete_certificate($id = null)
    {
        UserQualification::where('id', $id)->update(['is_deleted' => 1]);
        return $response = array("status" => "ok");
    }
}
