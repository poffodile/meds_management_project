<?php

namespace App\Http\Controllers\Api\ServiceUser;

use App\Http\Controllers\Controller;
use App\Models\SuBehavior;
use App\User, App\ServiceUser, App\SocialApp, App\ServiceUserContacts, App\Risk, App\Home;
use Auth, DB, Hash;
use DateTime, Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $r)
    {
        $data = $r->input();
        //   echo "<pre>"; print_r($data); die;
        if (!empty($data['user_name']) && !empty($data['password'])) {
            $exist_user = DB::table('service_user')->where('user_name', $data['user_name'])->first();
            //    echo "<pre>";print_r($exist_user);die;
            if (!empty($exist_user)) {
                $user_password = $exist_user->password;
                if (Hash::check($data['password'], $user_password)) {
                    $user_detail = DB::table('service_user')->select('id', 'name', 'date_of_birth', 'image')
                        ->where('user_name', $data['user_name'])
                        ->first();
                    $date_of_birth = date('d F Y', strtotime($user_detail->date_of_birth));

                    $user_age = Carbon::parse($date_of_birth)->diff(Carbon::now())->format('%y years, %m months and %d days');
                    $numeric_age = Carbon::parse($date_of_birth)->age ?? 0;
                    /*echo "<pre>";
                    print_r($user_detail);
                    die;*/
                    $time = date("H");
                    $timezone = date("e");
                    if ($time < "12") {
                        $wish = "Good morning";
                    } elseif ($time >= "12" && $time < "17") {
                        $wish = "Good afternoon";
                    } elseif ($time >= "17" && $time < "19") {
                        $wish = "Good evening";
                    } elseif ($time >= "19") {
                        $wish = "Good night";
                    }
                    $details  = array(
                        "id"            => $user_detail->id,
                        "name"          => $user_detail->name,
                        "date_of_birth" => date('d M Y', strtotime($user_detail->date_of_birth)),
                        "age"           => $user_age,
                        'user_age' => $numeric_age,
                        "wish"          => $wish,
                        "image"         => $user_detail->image ?? '',
                        "su_image_ur"   => serviceUserProfileImagePath,
                        "user_type"     => "Child"
                    );
                    return response(array(
                        'success' => true,
                        'message' => "User login successfully.",
                        'data' => $details,
                    ));
                } else {
                    return response(array(
                        'success' => false,
                        'message' => "Invalid username and password."
                    ));
                }
            } else {
                /*return json_encode(array(
                    'result' => array(
                    'response' => false,
                    'message' => "You are not authorized,Please contact to admin."
                    )
                )); */
                $user_staff_admin  = DB::table('user')->where('user_name', $data['user_name'])->first();
                if (!empty($user_staff_admin)) {
                    $user_password = $user_staff_admin->password;
                    if (Hash::check($data['password'], $user_password)) {
                        $user_detail = User::with('access_level')->select('id', 'name', 'access_level', 'image')->where('user_name', $data['user_name'])->first();
                        $user_detail = json_decode(json_encode($user_detail), true);

                        $time = date("H");
                        $timezone = date("e");
                        if ($time < "12") {
                            $wish = "Good morning";
                        } elseif ($time >= "12" && $time < "17") {
                            $wish = "Good afternoon";
                        } elseif ($time >= "17" && $time < "19") {
                            $wish = "Good evening";
                        } elseif ($time >= "19") {
                            $wish = "Good night";
                        }

                        $details = array(
                            "id" => (string)$user_detail['id'],
                            "name" => $user_detail['name'],
                            "access_level_id" => (string)$user_detail['access_level']['id'],
                            "access_level_name" => $user_detail['access_level']['name'],
                            "image" => $user_detail['image'],
                            "wish" => $wish,
                            "user_image_url" => userProfileImagePath,
                            "user_type" => "Staff"
                        );

                        return json_encode(array(
                            'result' => array(
                                'response' => true,
                                'data' => $details,
                                'message' => "User login successfully."
                            )
                        ));
                    } else {
                        return json_encode(array(
                            'result' => array(
                                'response' => false,
                                'message' => "Invalid username and password."
                            )
                        ));
                    }
                } else {
                    return json_encode(array(
                        'result' => array(
                            'response' => false,
                            'message' => "User does not exist."
                        )
                    ));
                }
            }
        } else {
            return response(array(
                'success' => false,
                'message' => "Fill all fields."
            ));
        }
    }

    /*-------Personal Info-------*/

    // public function personal_details($service_user_id)
    // {
    //     $exist = DB::table('service_user')->where('id',$service_user_id)->first();

    //     if(!empty($exist))
    //     {
    //         $user_details = json_decode(json_encode($exist),true);

    //         $date_of_birth = $user_details['date_of_birth'];
    //         $user_age = Carbon::parse($date_of_birth)->diff(Carbon::now())->format('%y years');
    //         // $user_details['date_of_birth'] = date('d M Y',strtotime($date_of_birth));
    //         $user_details['date_of_birth'] = date('d/m/Y',strtotime($date_of_birth));

    //         $current_location = $user_details['current_location'];
    //         //removing new line
    //         $pattern = '/[^a-zA-Z0-9]/u';
    //         $current_location = preg_replace($pattern, ' ', (string) $current_location);
    //         $coordinates = ServiceUser::getLongLat($current_location);

    //         $latitude = (isset($coordinates['results']['0']['geometry']['location']['lat'])) ? $coordinates['results']['0']['geometry']['location']['lat'] : '';
    //         $longitude = (isset($coordinates['results']['0']['geometry']['location']['lng'])) ? $coordinates['results']['0']['geometry']['location']['lng'] : '';

    //         $user_details['location']['latitude'] = $latitude;
    //         $user_details['location']['longitude'] = $longitude;
    //         $user_details['image_url'] = serviceUserProfileImagePath;
    //         $user_details['age'] = $user_age;
    //         $risk_status = Risk::overallRiskStatus($service_user_id);

    //         if($risk_status == 1){
    //             //$color = 'orange-clr';
    //             $risk_status = 'Historic';
    //         } else if($risk_status == 2){
    //             //$color = 'red-clr';
    //             $risk_status = 'High';
    //         } else{
    //             //$color = 'darkgreen-clr';
    //             $risk_status = 'No';
    //         }
    //         $user_details['risk_status'] = $risk_status;

    //         $care_history = DB::table('su_care_history')
    //                             ->select('id','title',DB::Raw("DATE_FORMAT(date, '%d %b %Y') as date"))
    //                             ->where('service_user_id',$service_user_id)
    //                             ->orderBy('date','desc')
    //                             ->get()
    //                             ->toArray();
    //         $user_details['care_history'] = $care_history;

    //         //social app
    //         $social_apps = SocialApp::select('social_app.id','social_app.name','ssa.value')
    //                         ->where('social_app.is_deleted',0)
    //                         ->leftJoin('su_social_app as ssa', function($join) use ($service_user_id) {
    //                             $join->on('ssa.social_app_id','=','social_app.id');
    //                             $join->on('ssa.service_user_id','=',DB::raw($service_user_id));
    //                         })
    //                         ->get()
    //                         ->toArray();
    //         $user_details['social_apps'] = $social_apps;

    //         $su_contacts = ServiceUserContacts::select('id','job_title_id as relation','name','email','phone_no','image','address')
    //                         ->where('service_user_id', $service_user_id)
    //                         ->where('is_deleted','0')
    //                         ->get();
    //         $user_details['contacts'] = $su_contacts;
    //         $user_details['contacts_image_url'] = contactsPath;
    //         // $ratingStats = DB::table('service_user')
    //         // ->where('id', $service_user_id)
    //         // ->select(DB::raw('AVG(behavior_rate) as avg_rating'), DB::raw('COUNT(*) as rating_count'))
    //         // ->first();

    //         // $avg_rating = $ratingStats && $ratingStats->avg_rating ? round($ratingStats->avg_rating, 1) : 0;
    //         // $rating_count = $ratingStats ? intval($ratingStats->rating_count) : 0;
    //         $lastMonth = now()->subDays(30);
    //         $rating = SuBehavior::where('service_user_id', $service_user_id)
    //         ->whereDate('created_at', '>=', $lastMonth)
    //         ->where('is_deleted', 0)
    //         ->avg('rate');

    //         $avg_rating = number_format($rating, 1);
    //         // echo "<pre>";print_r($avg_rating);die;
    //         // $avg_rating=5;
    //         $user_details['avg_rating']=$avg_rating;
    //         $su_mood_fecth=DB::table('su_mood as su_m')->select('su_m.id as su_mood_tableid','su_m.service_user_id','su_m.mood_id','su_m.is_deleted as su_mood_is_deleted','su_m.created_at as su_mood_created_at','m.id as mood_table_id','m.name','m.image','m.status','m.is_deleted as mood_delete')
    //         ->join('mood as m', 'su_m.mood_id','=','m.id')
    //         ->where('su_m.service_user_id',$service_user_id)
    //         ->where('su_m.is_deleted',0)
    //         ->where('m.is_deleted',0)
    //         ->where('m.status',1)
    //         ->whereDate('su_m.created_at', Carbon::today())
    //         ->orderby('su_m.id','desc')->first();
    //         // echo "<pre>";print_r($su_mood_fecth);die;
    //         $su_mood_arr=json_decode('{}');
    //         if($su_mood_fecth){
    //             $su_mood_arr=[
    //                 'id'=>$su_mood_fecth->su_mood_tableid ?? '',
    //                 'mood_image'=>url('public/images/mood').'/'.$su_mood_fecth->image ?? '',
    //                 'mood_name'=>ucfirst($su_mood_fecth->name ?? ''),
    //             ];
    //         }
    //         // echo "<pre>";print_r($su_mood_arr);die;
    //         $user_details['mood']=$su_mood_arr;
    //         // $user_details['rating_count']=$rating_count;
    //         $user_details = $this->replace_null($user_details);

    //         return response(array(
    //             'success' => true,
    //             'message' => "User Detail.",
    //             'data' => $user_details
    //         ));
    //     }
    //     else
    //     {
    //         return response(array(
    //             'success' => false,
    //             'message' => "User not found."
    //         ));
    //     }
    // }

    public function personal_details($service_user_id)
    {
        $exist = DB::table('service_user')->where('id', $service_user_id)->first();

        if (!$exist) {
            return response([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $user = (array) $exist;

        /* ================= BASIC DETAILS ================= */
        $dob_db = $user['date_of_birth'] ?? '';

        // age calculation (parse DB format)
        $age = 0;
        if (!empty($dob_db)) {
            $age = Carbon::createFromFormat('Y-m-d', $dob_db)->diffInYears(now());
        }

        /* ================= LOCATION ================= */
        $location_text = preg_replace('/[^a-zA-Z0-9]/u', ' ', $user['current_location'] ?? '');
        $coordinates = ServiceUser::getLongLat($location_text);

        $latitude  = $coordinates['results'][0]['geometry']['location']['lat'] ?? 0.0;
        $longitude = $coordinates['results'][0]['geometry']['location']['lng'] ?? 0.0;

        $location = [
            'latitude' => $latitude ? (float)$latitude : '',
            'longitude' => $longitude ? (float)$longitude : '',
        ];

        $data['id'] = $user['id'];
        $data['home_id'] = $user['home_id'] ?? '';
        $data['earning_scheme_label_id'] = $user['earning_scheme_label_id'] ?? '';
        $data['name'] = $user['name'] ?? '';
        $data['user_name'] = $user['user_name'] ?? '';
        $data['phone_no'] = $user['phone_no'] ?? '';
        $data['date_of_birth'] = !empty($dob_db) ? Carbon::createFromFormat('Y-m-d', $dob_db)->format('d/m/Y') : '';
        $data['age'] = $age . ' years';
        $data['department'] = $user['department'] ?? null;
        $data['child_type'] = $user['child_type'] ?? '';
        $data['room_type'] = $user['room_type'] ?? null;
        $data['weekly_rate'] = $user['weekly_rate'] ?? null;
        $data['subs'] = $user['subs'] ?? null;
        $data['extra'] = $user['extra'] ?? null;
        $data['local_authority'] = $user['local_authority'] ?? '';
        $data['start_date'] = $user['start_date'] ?? null;
        $data['end_date'] = $user['end_date'] ?? null;
        $data['section'] = $user['section'] ?? '';
        $data['admission_number'] = $user['admission_number'] ?? '';
        $data['short_description'] = $user['short_description'] ?? '';
        $data['height_unit'] = $user['height_unit'] ?? null;
        $data['height_ft'] = $user['height_ft'] ?? null;
        $data['height_in'] = $user['height_in'] ?? null;
        $data['weight_unit'] = $user['weight_unit'] ?? null;
        $data['weight'] = $user['weight'] ?? null;
        $data['hair_and_eyes'] = $user['hair_and_eyes'] ?? '';
        $data['markings'] = $user['markings'] ?? '';
        $data['image'] = $user['image'] ?? '';
        $data['email'] = $user['email'] ?? '';
        $data['ethnicity_id'] = $user['ethnicity_id'] ?? null;
        $data['personal_info'] = $user['personal_info'] ?? '';
        $data['education_history'] = $user['education_history'] ?? '';
        $data['bereavement_issues'] = $user['bereavement_issues'] ?? '';
        $data['drug_n_alcohol_issues'] = $user['drug_n_alcohol_issues'] ?? '';
        $data['mental_health_issues'] = $user['mental_health_issues'] ?? '';
        $data['current_location'] = $user['current_location'] ?? '';
        $data['previous_location'] = $user['previous_location'] ?? '';
        $data['mobile'] = $user['mobile'] ?? '';
        $data['behavior_rate'] = $user['behavior_rate'] ?? null;
        $data['last_loc_area_type'] = $user['last_loc_area_type'] ?? null;
        $data['location_get_interval'] = $user['location_get_interval'] ?? null;
        $data['build'] = $user['build'] ?? '';
        $data['previous_name'] = $user['previous_name'] ?? '';
        $data['allergies'] = $user['allergies'] ?? '';
        $data['status'] = $user['status'] ?? null;
        $data['security_code'] = $user['security_code'] ?? '';
        $data['is_deleted'] = $user['is_deleted'] ?? null;
        $data['created_at'] = $user['created_at'] ?? null;
        $data['updated_at'] = $user['updated_at'] ?? null;
        $data['location'] =  $location ?? null;
        /* ================= RISK ================= */
        $risk = Risk::overallRiskStatus($service_user_id);
        $data['risk_status'] = match ($risk) {
            1 => 'Historic',
            2 => 'High',
            default => 'No'
        };

        /* ================= CARE HISTORY ================= */
        $data['care_history'] = DB::table('su_care_history')
            ->select('id', 'title', DB::raw("DATE_FORMAT(date, '%d %b %Y') as date"))
            ->where('service_user_id', $service_user_id)
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
        /* ================= SOCIAL APPS ================= */
        $data['social_apps'] = SocialApp::select(
            'social_app.id',
            'social_app.name',
            'social_app.icon',
            DB::raw('IFNULL(ssa.value, "") as value')
        )
            ->where('social_app.is_deleted', 0)
            ->leftJoin('su_social_app as ssa', function ($join) use ($service_user_id) {
                $join->on('ssa.social_app_id', '=', 'social_app.id')
                    ->where('ssa.service_user_id', '=', $service_user_id);
            })
            ->get()
            ->toArray();
        /* ================= CONTACTS ================= */
        $data['contacts'] = ServiceUserContacts::select(
            'id',
            'job_title_id as relation',
            'name',
            'email',
            'phone_no',
            'image',
            'address'
        )
            ->where('service_user_id', $service_user_id)
            ->where('is_deleted', 0)
            ->get()
            ->toArray();

        /* ================= IMAGE URL ================= */
        $data['contacts_image_url'] = contactsPath ?? null;
        $data['image_url'] = serviceUserProfileImagePath ?? null;

        /* ================= RATING ================= */
        $rating = SuBehavior::where('service_user_id', $service_user_id)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->where('is_deleted', 0)
            ->avg('rate');

        $data['avg_rating'] = $rating ? (string)round($rating, 1) : '0.0';

        /* ================= MOOD ================= */
        $data['mood'] = [
            'id' => null,
            'mood_image' => '',
            'mood_name' => ''
        ];

        $su_mood = DB::table('su_mood as su_m')
            ->join('mood as m', 'su_m.mood_id', '=', 'm.id')
            ->where('su_m.service_user_id', $service_user_id)
            ->where('su_m.is_deleted', 0)
            ->where('m.is_deleted', 0)
            ->where('m.status', 1)
            ->whereDate('su_m.created_at', Carbon::today())
            ->orderByDesc('su_m.id')
            ->first();

        if ($su_mood) {
            $data['mood'] = [
                'id' => (int) $su_mood->id,
                'mood_image' => url('public/images/mood') . '/' . $su_mood->image,
                'mood_name' => ucfirst($su_mood->name)
            ];
        }

        
        /* ================= EDUCATION PROFILE CHECK ================= */
        $data['education_profile'] = DB::table('su_education_profiles')
            ->where('service_user_id', $service_user_id)
            ->exists();
        

        /* ================= FINAL RESPONSE ================= */
        return response()->json([
            'success' => true,
            'message' => 'User Details.',
            'data' => $data
        ]);
    }


    public function change_password(Request $r)
    {
        $data = $r->input();
        //if(!empty($data['user_type']) && !empty($data['username']) && !empty($data['password']) && !empty($data['old_password']) && !empty($data['confirm_password'])){
        if (!empty($data['user_type']) && !empty($data['user_id']) && !empty($data['password']) && !empty($data['old_password']) && !empty($data['confirm_password'])) {

            if ($data['password'] != $data['confirm_password']) {

                return json_encode(array(
                    'result' => array(
                        'response' => false,
                        'message' => 'New password and Confirm password does not match.',
                    )
                ));
            }

            if ($data['user_type'] == "Child") {
                $user = ServiceUser::where('id', $data['user_id'])->first();
                if ($data['password'] == $data['confirm_password'] && Hash::check($data['old_password'], $user->password) && !empty($user)) {
                    $password = Hash::make($data['password']);
                    $save = ServiceUser::where('id', $data['user_id'])->update(['password' => $password]);
                    if ($save) {
                        return json_encode(array(
                            'result' => array(
                                'response' => true,
                                'message' => "Password has been successfully changed."
                            )
                        ));
                    }
                } else {
                    return json_encode(array(
                        'result' => array(
                            'response' => false,
                            'message' => 'You have entered wrong old password.',
                        )
                    ));
                }
            } elseif ($data['user_type'] == 'Staff') {

                $user = User::where('id', $data['user_id'])->first();
                if ($data['password'] == $data['confirm_password'] && Hash::check($data['old_password'], $user->password) && !empty($user)) {
                    $password = Hash::make($data['password']);
                    $save = User::where('id', $data['user_id'])->update(['password' => $password]);
                    if ($save) {
                        return json_encode(array(
                            'result' => array(
                                'response' => true,
                                'message' => "Password has been successfully changed."
                            )
                        ));
                    }
                } else {
                    return json_encode(array(
                        'result' => array(
                            'response' => false,
                            'message' => 'You have entered wrong old password.',
                        )
                    ));
                }
            } else {
                return json_encode(array(
                    'result' => array(
                        'response' => false,
                        'message' => 'Enter correct data.',
                    )
                ));
            }
        } else {
            return json_encode(array(
                'result' => array(
                    'response' => false,
                    'message' => 'Fill all fields.',
                )
            ));
        }
    }

    public function forgot_password(Request $r)
    {
        $data = $r->input();
        // echo "<pre>"; print_r($data); die;
        if (!empty($data['user_name'])) {
            $user = ServiceUser::where('user_name', $data['user_name'])->first();
            // echo "<pre>"; print_r($user); die;
            if (!empty($user)) {
                $random_no           = rand(111111, 999999);
                $security_code       = base64_encode(convert_uuencode($random_no));
                $user_name_enc       = base64_encode(convert_uuencode($user->user_name));
                $home_security_policy = Home::where('id', $user->home_id)->value('security_policy');
                $name                = $user->name;
                $user_name           = $user->user_name;
                $company_name        = PROJECT_NAME;
                $email               = $user->email;
                $update = ServiceUser::where('user_name', $data['user_name'])->update(['security_code' => $security_code]);
                $set_password_url = url('/reset-password' . '/' . $user_name_enc . '/' . $security_code);

                //return $set_password_url;
                // echo $set_password_url; die;

                if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    // echo $company_name; die;
                    Mail::send('emails.user_reset_password_mail', ['name' => $name, 'user_name' => $user_name, 'set_password_url' => $set_password_url, 'home_security_policy' => $home_security_policy], function ($message) use ($email, $company_name) {
                        $message->to($email, $company_name)->subject('SCITS forgot Password Mail');
                    });

                    return json_encode(array(
                        'result' => array(
                            'response' => true,
                            'message' => 'Forgot email link has been sent to your email.',
                        )
                    ));
                }
            } else {
                $user = User::where('user_name', $data['user_name'])->first();
                if (!empty($user)) {
                    $random_no           = rand(111111, 999999);
                    $security_code       = base64_encode(convert_uuencode($random_no));
                    $user_name_enc       = base64_encode(convert_uuencode($user->user_name));
                    $home_security_policy = Home::where('id', $user->home_id)->value('security_policy');
                    $name                = $user->name;
                    $user_name           = $user->user_name;
                    $company_name        = PROJECT_NAME;
                    $update = User::where('user_name', $data['user_name'])->update(['security_code' => $security_code]);
                    $set_password_url = url('/reset-password' . '/' . $user_name_enc . '/' . $security_code);
                    $email = $user->email;
                    //return $set_password_url;
                    //echo $set_password_url; die;

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                        Mail::send('emails.user_reset_password_mail', ['name' => $name, 'user_name' => $user_name, 'set_password_url' => $set_password_url, 'home_security_policy' => $home_security_policy], function ($message) use ($email, $company_name) {
                            $message->to($email, $company_name)->subject('SCITS forgot Password Mail');
                        });
                        return json_encode(array(
                            'result' => array(
                                'response' => true,
                                'message' => 'Forgot email link has been sent to your email.',
                            )
                        ));
                    }
                } else {
                    return json_encode(array(
                        'result' => array(
                            'response' => false,
                            'message' => 'Please enter correct User Name to get a reset link.',
                        )
                    ));
                }
            }
        } else {
            return json_encode(array(
                'result' => array(
                    'response' => false,
                    'message' => 'Fill all fields.',
                )
            ));
        }
    }


    /* public function show_forget_password_form(Request $request, $user_name = null, $security_code = null) {

        $decoded_user_name     = convert_uudecode(base64_decode($user_name));
        $decoded_security_code = convert_uudecode(base64_decode($security_code));
        $count = ServiceUser::where('user_name', $decoded_user_name)
                                ->where('security_code', $security_code)
                                ->first();
        if(!empty($count)) {
            $user_name = $count->user_name;
            $user_id   = $count->id;
            return view('frontEnd.forget_set_password', compact('user_id','user_name','security_code'));
        } else {
            return redirect('/login')->with('error','This link has been already used.');
        }

    }*/

    public function show_forget_password_form(Request $request, $user_name = null, $security_code = null)
    {

        $decoded_user_name = convert_uudecode(base64_decode($user_name));
        $decoded_security_code = convert_uudecode(base64_decode($security_code));

        if (!empty($decoded_user_name)) {
            $user = ServiceUser::where('user_name', $decoded_user_name)->first();
            if (!empty($user)) {
                $record = ServiceUser::where('user_name', $decoded_user_name)
                    ->where('security_code', $security_code)
                    ->first();

                if (!empty($record)) {
                    $user_name = $record->user_name;
                    $user_id   = $record->id;
                    return view('frontEnd.forget_set_password', compact('user_id', 'user_name', 'security_code'));
                } else {
                    return redirect('/login')->with('error', 'This link has been already used.');
                }
            } else {
                $user = User::where('user_name', $decoded_user_name)->first();

                $record = User::where('user_name', $decoded_user_name)
                    ->where('security_code', $security_code)
                    ->first();
                if (!empty($record)) {
                    $user_name = $record->user_name;
                    $user_id   = $record->id;
                    return view('frontEnd.forget_set_password', compact('user_id', 'user_name', 'security_code'));
                } else {
                    return redirect('/login')->with('error', 'This link has been already used.');
                }
            }
        } else {
            return redirect('/login')->with(COMMON_ERROR);
        }
    }

    public function set_forget_password(Request $request)
    {
        $data = $request->input();
        // echo "<pre>"; print_r($data); die;
        if (empty($data['password'])) {
            return redirect()->back()->with('error', 'Please Enter Password');
        } else if ($data['password'] != $data['confirm_password']) {
            return redirect()->back()->with('error', 'Password & confirm password does not matched.');
        } else {

            if (!empty($data['user_name'])) {
                $user = ServiceUser::where('user_name', $data['user_name'])->first();
                if (!empty($user)) {
                    $user->password = Hash::make($data['password']);
                    $user->security_code = '';
                    if ($user->save()) {
                        return redirect('/login')->with('success', 'You have set your password successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Some error occured. Please try again later');
                    }
                } else {
                    $user = User::where('user_name', $data['user_name'])->first();
                    $user->password = Hash::make($data['password']);
                    $user->security_code = '';
                    if ($user->save()) {
                        return redirect('/login')->with('success', 'You have set your password successfully.');
                    } else {
                        return redirect()->back()->with('error', 'Some error occured. Please try again later');
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Some error occured. Please try again later');
            }
        }
    }

    public function change_notification(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'child_id' => 'required|exists:service_user,id',
                'type' => 'required',
                'status' => "required|in:0,1"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $child_id = $req->child_id ?? "";
            $childData = ServiceUser::find($child_id);
            $msg = 'Notification Changed Successfully';
            if ($req->type == 'mood') {

                $childData->mood_notification = $req->status ?? 0;
                $msg = ($req->status == 1)
                    ? 'Mood sound notifications have been enabled.'
                    : 'Mood sound notifications have been disabled.';
            } else {
                $childData->notification = $req->status ?? 0;
                $msg = ($req->status == 1)
                    ? 'Notifications have been enabled.'
                    : 'Notifications have been disabled.';
            }
            $childData->save();

            return response()->json([
                'status'  => true,
                'message' => $msg,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function dashboard(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'child_id' => 'required|exists:service_user,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $child_id = $req->child_id ?? "";
            $childData = ServiceUser::find($child_id);


            /* ================= BASIC DETAILS ================= */
            $dob_db = $childData->date_of_birth;

            // age calculation (parse DB format)
            $age = 0;
            if (!empty($dob_db)) {
                $age = Carbon::createFromFormat('Y-m-d', date('Y-m-d', strtotime($dob_db)))->diffInYears(now());
            }
            $time = date("H");
            $timezone = date("e");
            if ($time < "12") {
                $wish = "Good morning";
            } elseif ($time >= "12" && $time < "17") {
                $wish = "Good afternoon";
            } elseif ($time >= "17" && $time < "19") {
                $wish = "Good evening";
            } elseif ($time >= "19") {
                $wish = "Good night";
            }
            $data = [
                'id' => $child_id,
                'home_id' => $childData->home_id,
                'name' => $childData->name,
                'age' => $age,
                "wish" => $wish,
                "mood_notification" => $childData->mood_notification,
                "notification" => $childData->notification,
                "current_date" => date('l d, F'),
            ];

            /* ================= FINAL RESPONSE ================= */
            return response()->json([
                'status' => true,
                'message' => 'User Details',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
