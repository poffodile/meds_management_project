<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Auth;
use App\Home, App\Admin, App\StaffSickLeave;
use App\Models\PersonalManagement\TimeSheet;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function getHomeUsers($home_id)
    {
        return User::where('home_id', $home_id)->select('id', 'name')->where('is_deleted', 0)->get();
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope('active', function ($query) {
    //         $query->where('user.status', 1);
    //     });
    // }

    public function access_level()
    {
        return $this->hasOne('App\AccessLevel', 'id', 'access_level');
    }

    public function certificates()
    {
        return $this->hasMany('App\UserQualification', 'user_id', 'id')->where('is_deleted', 0);
    }

    //send set password link to user
    public static function sendCredentials($user_id = null)
    {

        $user                 = User::where('id', $user_id)->first();
        $home_security_policy = Home::where('id', $user->home_id)->value('security_policy');
        $random_no            = rand(111111, 999999);
        $security_code        = base64_encode(convert_uuencode($random_no));
        $user_id              = base64_encode(convert_uuencode($user->id));
        $email                = $user->email;
        $name                 = $user->name;
        $user->security_code  = $random_no;
        $user_name            = $user->user_name;
        $company_name         = PROJECT_NAME;

        if ($user->save()) {
            $set_password_url = url('/set-password' . '/' . $user_id . '/' . $security_code);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                // Mail::send('emails.user_set_password_mail', ['name'=>$name, 'user_name'=>$user_name, 'set_password_url'=>$set_password_url, 'home_security_policy'=>$home_security_policy], function($message) use ($email,$company_name)
                // {
                //     $message->to($email,$company_name)->subject('SCITS set Password Mail');
                // });
                //echo $email;
                //die;
                $arr = ['name' => $name, 'user_name' => $user_name, 'set_password_url' => $set_password_url, 'home_security_policy' => $home_security_policy];
                Mail::send('emails.user_set_password_mail', $arr, function ($message) use ($arr, $email, $company_name) {

                    $message->to($email, $company_name)

                        ->subject('SCITS set Password Mail');

                    $message->from('mobappssolutions153@gmail.com', $company_name);
                });

                //print_r($arr);
                //die;
                return true;
            }
        }
        return false;
    }

    // public static function saveQualification($data = array(), $user_id = null)
    // {
    //     //saving qualification info and certificates images
    //     if (isset($data['qualification'])) {
    //         foreach ($data['qualification'] as $key => $qualification_name) {
    //             if (!empty($qualification_name) && !empty($_FILES['qualifiaction_cert']['name'][$key])) {

    //                 $tmp_image  =   $_FILES['qualifiaction_cert']['tmp_name'][$key];
    //                 $image_info =   pathinfo($_FILES['qualifiaction_cert']['name'][$key]);
    //                 $ext        =   strtolower($image_info['extension']);
    //                 $random_no  =   rand(111, 999) . '.' . $ext;
    //                 $new_name   =   time() . $random_no . '.' . $ext;

    //                 $allowed_ext = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');

    //                 if (in_array($ext, $allowed_ext)) {
    //                     $destination = base_path() . '/public/images/userQualification';

    //                     if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {

    //                         $qualification          = new UserQualification;
    //                         $qualification->name    = $qualification_name;
    //                         $qualification->image   = $new_name;
    //                         $qualification->user_id = $user_id;
    //                         $qualification->save();
    //                     }
    //                 }

    //                 $quali[$key]['cert_image'] = $new_name;
    //             }
    //         }
    //     }
    // }

    public static function saveQualification($data = [], $user_id = null)
    {
        Log::info('saveQualification called', [
            'user_id' => $user_id,
            'data'    => $data
        ]);

        if (empty($data)) {
            Log::warning('No qualifications found');
            return;
        }

        foreach ($data as $key => $qual) {

            Log::info("Processing qualification", ['key' => $key, 'qual' => $qual]);

            // ✅ Only checked courses
            if (
                empty($qual['course_id']) ||
                empty($qual['name'])
            ) {
                continue;
            }

            // ✅ Certificate check
            if (
                !isset($qual['cert']) ||
                !$qual['cert'] instanceof \Illuminate\Http\UploadedFile
            ) {
                continue;
            }

            $file = $qual['cert'];
            $ext  = strtolower($file->getClientOriginalExtension());

            $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            if (!in_array($ext, $allowed_ext)) {
                continue;
            }

            $new_name = time() . rand(111, 999) . '.' . $ext;

            $file->move(
                public_path('images/userQualification'),
                $new_name
            );

            UserQualification::create([
                'user_id'   => $user_id,
                'course_id' => $qual['course_id'],
                'name'      => $qual['name'],
                'image'     => $new_name,
            ]);

            Log::info('Saved qualification', [
                'user_id' => $user_id,
                'course_id' => $qual['course_id']
            ]);
        }
    }

    public static function getstaffByResidentialId($department)
    {
        return self::where('home_id', Auth::user()->home_id)->where('department', $department)->where('status', 1)->where('is_deleted', 0)->count();
    }

    //one user login at a time
    static function updateUserLastActivityTime()
    {

        User::where('id', Auth::user()->id)->update([
            'last_activity_time' => date('Y-m-d H:i:s')
        ]);
    }

    static function setUserLogInStatus($new_status = null)
    { //echo 'm'; die;

        if ($new_status == 1) {
            $csrf_token = csrf_token();
        } else {
            $csrf_token = '';
        }
        User::where('id', Auth::user()->id)->update([
            'logged_in' => $new_status,
            'session_token' => csrf_token()
        ]);
    }

    public static function getStaffList($home_id)
    {
        $users = User::select('id', 'name', 'user_name', 'email')
            ->where('home_id', $home_id)
            ->where('status', '1')
            ->where('is_deleted', '0')
            ->get()
            ->toArray();
        return $users;
    }

    public static function checkUserHasAccessRight($user_id, $access_id)
    {
        $user = User::select('id', 'access_rights')
            ->whereRaw('FIND_IN_SET(?,access_rights)', $access_id)
            ->where('id', $user_id)
            ->count();

        if ($user > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function sendLeaveEmail($staff_member_id = null)
    {

        //staff member info       
        $staff_user = User::where('id', $staff_member_id)->first();

        $user_email = $staff_user->email;
        $name       = $staff_user->name;
        $user_name  = $staff_user->user_name;
        $job_title  = $staff_user->job_title;

        $staff_sick_leave = StaffSickLeave::where('staff_member_id', $staff_member_id)->first();
        //echo "<pre>"; print_r($staff_sick_leave); die;

        $leave_title    = $staff_sick_leave->title;
        $leave_date     = $staff_sick_leave->leave_date;
        $leave_reason   = $staff_sick_leave->reason;
        $leave_comments = $staff_sick_leave->comments;

        //company admin info
        $admin       = Admin::select('admin.name', 'admin.email', 'admin.user_name', 'admin.company')
            ->join('home', 'home.admin_id', 'admin.id')
            ->join('user', 'user.home_id', 'home.id')
            ->where('home.id', $staff_user->home_id)
            ->first();
        $admin_email     = $admin->email;
        $admin_name      = $admin->name;
        $admin_user_name = $admin->user_name;
        $company_name    = $admin->company;

        if (!empty($admin)) {

            if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL) === false) {
                Mail::send('emails.leave', ['user_email' => $user_email, 'name' => $name, 'user_name' => $user_name, 'job_title' => $job_title, 'leave_title' => $leave_title, 'leave_date' => $leave_date, 'leave_reason' => $leave_reason, 'leave_comments' => $leave_comments, 'admin_name' => $admin_name, 'admin_user_name' => $admin_user_name, 'company_name' => $company_name], function ($message) use ($admin_email, $company_name) {
                    $message->to($admin_email, $company_name)->subject('Leave Application'); /*Leave Application Mail to company admin*/
                });
                return 'Email sent to ' . $name . ' successfully.';
            }
        }
        return false;
    }

    public static function sendEmailToManager($staff_member_id)
    { //if staff user edit his information,this function called 

        $staff_member = User::select('id', 'user_name', 'email', 'admn_id', 'company_id')->where('id', $staff_member_id)->first();
        $user_name    = $staff_member->user_name;
        $user_email   = $staff_member->email;

        if (!empty($staff_member->admn_id)) {

            $company_manager = CompanyManagers::where('company_id', $staff_member->admn_id)->first();
            $manager_name    = $company_manager->name;
            $email           = $company_manager->email;
            // $email = $company_manager->email;
            if (!empty($company_manager)) {
                $company_name = PROJECT_NAME;
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::send('emails.email_to_manager', ['manager_name' => $manager_name, 'user_email' => $user_email, 'user_name' => $user_name], function ($message) use ($email, $company_name) {
                        $message->to($email, $company_name)->subject('Scits Alert Email');
                    });
                }
            }
        }
    }

    public static function getLeadAssignUserName($id)
    {
        return User::where('id', $id)->value('name');
    }

    public static function updateManagerStatus($id, $status)
    {
        return User::where('id', $id)->update(['status' => $status]);
    }

    public static function countManager($id)
    {
        return User::where('id', $id)->count();
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function emergencyContacts()
    {
        return $this->hasOne(
            \App\Models\UserEmergencyContact::class,
            'user_id',
            'id'
        );
    }




    // public static function getData($id)
    // {
    //     return User::select('id', 'name', 'email', 'holiday_entitlement', 'date_of_joining') // specify only the fields you need
    //         ->where('id', $id)
    //         ->first();
    // }
    /*
    Note: User(manager/staff) - set password functionality
        1. Super admin will create a new user
            Its password field will be blank initially
        2. Then admin click on set password button in admin > users list         
            set security code to a random number
            use this security code to generate set password link
            This link will be sent to the user's email id
        3. When user will click on this link, He will be redirected to scits > set password page
            in this page user will type his new password and confirm password, 
            here security code will be in a hidden field 
            
        4. When user submit form
            set password to entered password
            set security code equal to blank
            save user and redirect to login
        5. In login page user needs to 
            input company name
            select home 
            input username
            input password
            press submit the user will be logged in.
    */
}
