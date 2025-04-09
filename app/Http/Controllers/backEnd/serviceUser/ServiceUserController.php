<?php

namespace App\Http\Controllers\backEnd\serviceUser;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\ServiceUser, App\Home, App\SocialApp, App\ServiceUserSocialApp, App\Ethnicity;
use Hash, DB, Session;

class ServiceUserController extends Controller
{
    public function index(Request $request)
    {
        $home_id = Session::get('scitsAdminSession')->home_id;

        $del_status = '0';
        if ($request->user) { //for achive users
            $del_status = '1';
        }

        if (!empty($home_id)) {
            $users_query = DB::table('service_user')
                ->select('id', 'name', 'email')
                ->where('is_deleted', $del_status)
                ->where('home_id', $home_id);

            $search = '';

            if (isset($request->limit)) {
                $limit = $request->limit;
                Session::put('page_record_limit', $limit);
            } else {
                if (Session::has('page_record_limit')) {
                    $limit = Session::get('page_record_limit');
                } else {
                    $limit = 25;
                }
            }

            if (isset($request->search)) {
                $search = trim($request->search);
                $users_query = $users_query->where('name', 'like', '%' . $search . '%');
            }

            $service_users = $users_query->paginate(25);
        } else {
            return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
        }

        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'service-users';
        return view('backEnd.serviceUser.service_users', compact('page', 'limit', 'service_users', 'search', 'del_status'));
    }


    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->input();
            // echo "<pre>"; print_r($data); die;
            // $social_apps = $data['social_app_name'];
            // echo "<pre>"; print_r($social_apps); die;

            $home_id = Session::get('scitsAdminSession')->home_id;
            // echo $home_id; 
            // dd(Session::get('scitsAdminSession'));
            $date_of_birth = date('Y-m-d', strtotime($data['date_of_birth']));

            $ethnicity_id = NULL;
            if (!empty($request->ethnicity_id)) {
                $ethnicity_id = $request->ethnicity_id;
            }

            $user                        =  new ServiceUser;
            $user->name                  =  $data['name'];
            $user->user_name             =  $data['user_name'];
            $user->home_id               =  $home_id;
            $user->email                 =  $data['email'];
            $user->password              =  '';
            $user->phone_no              =  $data['phone_no'];
            $user->date_of_birth         =  $date_of_birth;
            $user->child_type            =  $data['child_type'];
            $user->room_type             =  $data['room_type'];
            $user->weekly_rate           =  $data['weekly_rate'];
            $user->subs                  =  $data['subs'];
            $user->extra                 =  $data['extra'];
            $user->start_date            =  date('Y-m-d', strtotime($data['start_date']));
            $user->local_authority       =  $data['local_authority'];
            $user->end_date              =  date('Y-m-d', strtotime($data['end_date']));
            $user->section               =  $data['section'];
            $user->admission_number      =  $data['admission_number'];
            $user->ethnicity_id          =  $ethnicity_id;
            $user->short_description     =  nl2br($data['short_description']);
            $user->height                =  $data['height'];
            $user->weight                =  $data['weight'];
            $user->hair_and_eyes         =  $data['hair_and_eyes'];
            $user->markings              =  $data['markings'];
            $user->status                =  $data['status'];
            $user->current_location      =  nl2br($data['current_location']);
            $user->previous_location     =  nl2br($data['previous_location']);
            $user->mobile                =  $data['mobile'];
            // $user->skype              =  $data['skype'];
            // $user->facebook           =  $data['facebook'];
            // $user->twitter            =  $data['twitter'];
            $user->personal_info         =  nl2br(trim($data['personal_info']));
            $user->education_history     =  nl2br(trim($data['education_history']));
            $user->bereavement_issues    =  nl2br(trim($data['bereavement_issues']));
            $user->drug_n_alcohol_issues =  nl2br(trim($data['drug_n_alcohol_issues']));
            $user->mental_health_issues  =  nl2br(trim($data['mental_health_issues']));

            if (!empty($_FILES['image']['name'])) {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time() . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . serviceUserProfileImageBasePath;
                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                        $user->image = $new_name;
                    }
                }
            }
            if (!isset($user->image)) {
                $user->image = '';
            }

            if ($user->save()) {
                if (isset($data['social_app'])) {
                    foreach ($data['social_app'] as $social_data) {

                        if (!empty($social_data['value'])) {
                            $su_soc_app                  = new ServiceUserSocialApp;
                            $su_soc_app->social_app_id   = $social_data['social_app_id'];
                            $su_soc_app->service_user_id = $user->id;
                            $su_soc_app->value = $social_data['value'];
                            $su_soc_app->save();
                        }
                    }
                }
                return redirect('admin/service-users')->with('success', 'Child added successfully.');
            } else {
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
        $page = 'service-users';

        $social_app = SocialApp::select('id', 'name')->where('is_deleted', '0')->get()->toArray();
        // echo "<pre>"; print_r($social_app); die;

        $ethnicity = Ethnicity::select('id', 'name')->where('is_deleted', '0')->get();
        // echo "<pre>"; print_r($ethnicity); die;

        return view('backEnd.serviceUser.service_user_form', compact('page', 'social_app', 'ethnicity'));
    }

    public function edit(Request $request, $service_id)
    {
        // echo "<pre>"; print_r($request->input()); die;

        $del_status = '0';
        if ($request->del_status) { //for achive users
            $del_status = $request->del_status;
        }

        $ethnicity_id = NULL;
        if (!empty($request->ethnicity_id)) {
            $ethnicity_id = $request->ethnicity_id;
        }
        // echo $ethnicity_id; die;

        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

        $ethnicity = Ethnicity::select('id', 'name')->where('is_deleted', '0')->get();

        if ($request->isMethod('post')) {
            $data = $request->input();
            //echo '<pre>'; print_r($data); die;
            $user                   = ServiceUser::find($service_id);
            if (!empty($user)) {

                //comparing su home_id
                $su_home_id = ServiceUser::where('id', $service_id)->value('home_id');
                if ($home_id != $su_home_id) {
                    return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
                }

                $user_old_image         = $user->image;
                $date_of_birth = date('Y-m-d', strtotime($data['date_of_birth']));

                $user->name             =  $data['name'];
                $user->user_name        =  $data['user_name'];
                $user->email            =  $data['email'];
                $user->phone_no         =  $data['phone_no'];
                $user->date_of_birth    =  $date_of_birth;
                $user->section          =  $data['section'];
                $user->admission_number =  $data['admission_number'];
                $user->short_description =  $data['short_description'];
                $user->child_type        =  $data['child_type'];
                $user->room_type         =  $data['room_type'];
                $user->weekly_rate       =  $data['weekly_rate'];
                $user->subs              =  $data['subs'];
                $user->extra             =  $data['extra'];
                $user->start_date        =  date('Y-m-d', strtotime($data['start_date']));
                $user->local_authority   =  $data['local_authority'];
                $user->end_date          =  date('Y-m-d', strtotime($data['end_date']));
                $user->height           =  $data['height'];
                $user->weight           =  $data['weight'];
                $user->hair_and_eyes    =  $data['hair_and_eyes'];
                $user->markings         =  $data['markings'];
                $user->status           =  $data['status'];
                $user->ethnicity_id     =  $ethnicity_id;

                $user->current_location =  nl2br($data['current_location']);
                $user->previous_location =  nl2br($data['previous_location']);
                $user->mobile           =  $data['mobile'];
                /* $user->skype            =  $data['skype'];
                $user->facebook         =  $data['facebook'];
                $user->twitter          =  $data['twitter'];   */

                $user->personal_info         =  nl2br(trim($data['personal_info']));
                $user->education_history     =  nl2br(trim($data['education_history']));
                $user->bereavement_issues    =  nl2br(trim($data['bereavement_issues']));
                $user->drug_n_alcohol_issues =  nl2br(trim($data['drug_n_alcohol_issues']));
                $user->mental_health_issues  =  nl2br(trim($data['mental_health_issues']));

                if (!empty($_FILES['image']['name'])) {

                    $tmp_image  =   $_FILES['image']['tmp_name'];
                    $image_info =   pathinfo($_FILES['image']['name']);
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   time() . '.' . $ext;

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                        $destination =   base_path() . serviceUserProfileImageBasePath;
                        if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                            if (!empty($user_old_image)) {
                                if (file_exists($destination . '/' . $user_old_image)) {
                                    unlink($destination . '/' . $user_old_image);
                                }
                            }
                            $user->image = $new_name;
                        }
                    }
                }

                if ($user->save()) {
                    //saving social app info
                    if (isset($data['social_app'])) {
                        foreach ($data['social_app'] as $social_data) {

                            if (!empty($social_data['value'])) {

                                $su_soc_app = ServiceUserSocialApp::where('id', $social_data['su_app_id'])->first();

                                //if its value is not already stored then save it as a new record
                                if (empty($su_soc_app)) {
                                    $su_soc_app                  = new ServiceUserSocialApp;
                                    $su_soc_app->social_app_id   = $social_data['social_app_id'];
                                    $su_soc_app->service_user_id = $service_id;
                                }
                                $su_soc_app->value = $social_data['value'];
                                $su_soc_app->save();
                            } else {
                                $su_soc_app = ServiceUserSocialApp::where('id', $social_data['su_app_id'])->delete();
                            }
                        }
                    }


                    return redirect('admin/service-users')->with('success', 'Child  updated successfully.');
                } else {
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
            } else {
                return redirect('admin/')->with('error', 'Sorry, Child does not exists');
            }
        }

        $user_info = DB::table('service_user')
            ->where('id', $service_id)
            ->where('is_deleted', $del_status)
            ->first();
        if (!empty($user_info)) {
            if ($user_info->home_id != $home_id) {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        } else {
            return redirect('admin/')->with('error', 'Sorry, Child does not exists');
        }
        $social_app = SocialApp::select('id', 'name')->where('is_deleted', '0')->get()->toArray();

        $su_social_app = ServiceUserSocialApp::select('id', 'social_app_id', 'value')
            ->where('su_social_app.service_user_id', $service_id)
            ->get()
            ->toArray();

        $social_app_val = array();
        foreach ($su_social_app as $key => $value) {
            $social_app_val[$value['social_app_id']]['id']    = $value['id'];
            $social_app_val[$value['social_app_id']]['value'] = $value['value'];
        }

        $page = 'service-users';

        return view('backEnd/serviceUser/service_user_form', compact('page', 'user_info', 'social_app', 'social_app', 'social_app_val', 'ethnicity', 'del_status')); //name of view file*/
    }

    public function check_username_exist(Request $request)
    {
        $data = $request->input();
        $user_name = '';
        if (is_array($data)) {
            $user_name_arr = array_values($data);
            $user_name = $user_name_arr[0];
        }

        $response = Home::userNameUnique($user_name);

        if ($response) {
            echo '{"valid": true}';
        } else {
            echo '{"valid": false}';
        }
        die;
    }

    public function delete($user_id)
    {
        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

        if (!empty($user_id)) {
            $updated = DB::table('service_user')->where('id', $user_id)->where('home_id', $home_id)->update(['is_deleted' => '1']);

            if (!empty($updated)) {
                return redirect('admin/service-users')->with('success', 'Child deleted Successfully.');
            } else {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        } else {
            return redirect('admin/')->with('error', 'Sorry, Child does not exists');
        }
    }

    public function send_set_pass_link_mail(Request $request, $user_id = NULL)
    {

        //compare home_id
        $admin     = Session::get('scitsAdminSession');
        $home_id   = $admin->home_id;
        $u_home_id = ServiceUser::where('id', $user_id)
            ->where('is_deleted', '0')
            ->value('home_id');
        if ($u_home_id != $home_id) {
            return 'You are not authorized to send the credentials.';
        }

        // send credentials for user              
        $response = ServiceUser::sendCredentials($user_id);
        echo $response;
        die;
    }

    /*public function delete($user_id)
    {
       if(!empty($user_id))
       {
        ServiceUser::where('id', $user_id)->delete();
        return redirect('admin/service-users')->with('success','Child deleted Successfully.'); 
        }
    }*/
    public function check_serviceuser_email_exists(Request $request)
    {
        $count = DB::table('service_user')->where('email', $request->email)->count();
        if ($count > 0) {
            echo '{"valid":false}';
            die;
        } else {
            echo '{"valid":true}';
            die;
        }
    }

    /*public function check_username_exist(Request $request) {
        
        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

        $count = ServiceUser::where('user_name',$request->user_name)->where('home_id', $home_id)->count();

        if($count > 0)
        {
            echo '{"valid":false}'; die;  // for bootstap validations
            //echo json_encode(false);   // for jquery validations
        }    
        else
        {
            echo '{"valid":true}'; die;  // for bootstap validations
            //echo json_encode(true);  //  for jquery validations
        }    
    }*/
}
