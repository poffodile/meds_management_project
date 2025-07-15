<?php

namespace App\Http\Controllers\backEnd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use App\CompanyManagers, App\Home, App\User, App\Admin, App\AccessLevel, App\AccessRight;
use DB;

class ManagersController extends Controller
{

    public function index(Request $request){

        $admin    = Session::get('scitsAdminSession');
        $home_id  = $admin->home_id; 
        $admin_id = $admin->id;

        $del_status = '0';
        /*if($request->user) { //for achive users
            $del_status = '1';
        }*/

        $company_manager_query = User::select('user.id','user.name','user_name','email','phone_no','status')
                                    ->where('user.user_type','CM')
                                    ->where('user.is_deleted', $del_status)
                                    // ->whereRaw('FIND_IN_SET(?,home_id)', [$home_id]);
                                    ->where('id','!=',$admin_id);
        $search = "";

        if(isset($request->limit))
        {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } else{

            if(Session::has('page_record_limit')){
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }
        if(isset($request->search))
        {
            $search      = trim($request->search);
            $company_manager_query = $company_manager_query->where('user.name','like','%'.$search.'%');
        }

        $users = $company_manager_query->paginate($limit);
        $page="managers";

        return view('backEnd.managers.index',compact('page','limit','users','search','del_status'));
    }

    // public function index(Request $request)
    // {


    //     $admin                  = Session::get('scitsAdminSession');
    //     $access_type            = Session::get('scitsAdminSession')->access_type;
    //     $selected_home_id       = Session::get('scitsAdminSession')->home_id;
    //     $selected_company_id    = Home::where('id', $selected_home_id)->value('admin_id');
    //     if ($access_type == 'S') {
    //         $company_id = $selected_company_id;
    //     } else {
    //         $company_id = $admin->id;
    //     }

    //     $managers     = CompanyManagers::select('id', 'name', 'contact_no', 'email', 'status')
    //         ->where('company_id', $company_id)
    //         ->where('is_deleted', '0');
    //     $search = '';

    //     if (isset($request->limit)) {
    //         $limit = $request->limit;
    //         Session::put('page_record_limit', $limit);
    //     } else {

    //         if (Session::has('page_record_limit')) {
    //             $limit = Session::get('page_record_limit');
    //         } else {
    //             $limit = 20;
    //         }
    //     }
    //     if (isset($request->search)) {
    //         $search     = trim($request->search);
    //         $managers   = $managers->where('name', 'like', '%' . $search . '%');
    //     }

    //     $managers = $managers->paginate($limit);

    //     $page = 'managers';
    //     return view('backEnd.managers.index', compact('page', 'managers', 'search', 'limit'));
    // }

    public function add(Request $request){

        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id; 
        if($request->isMethod('post')) {

            $data = $request->input();
            // echo "<pre>"; print_r($data); die;
            $company_ids = implode(',', $data['company_id']);

            if(!empty($company_ids)){
                $home_ids = Home::select('id')->whereIn('admin_id',$data['company_id'])->get()->toArray();
                $home_ids = array_map(function($v){ return $v['id'];  }, $home_ids);
                $home_ids = implode(',', $home_ids);
            }

            if(!empty($data['date_of_joining'])) {
                $date_of_joining = date('Y-m-d',strtotime($data['date_of_joining']));
            } else {
                $date_of_joining = null;
            }

            if(!empty($data['date_of_leaving'])) {
                $date_of_leaving = date('Y-m-d',strtotime($data['date_of_leaving']));
            } else {
                $date_of_leaving = null;   
            }

            $user                       = new User;
            $user->home_id              = $home_ids;
            $user->name                 = $request->name;
            $user->user_name            = $request->user_name;
            $user->email                = $request->email;
            //$random_no                  = rand(111111,999999);
            $user->password             = '';
            //$user->password             = Hash::make($random_no);
            //$user->password           = Hash::make($request->password);
            if(!empty($company_ids)){
                $user->company_id       = $company_ids;
            }
            $user->user_type            = 'CM';
            $user->job_title            = $request->job_title;
            $user->access_level         = '';
            $user->description          = $request->description;
            $user->payroll              = $request->payroll;
            $user->holiday_entitlement  = $request->holiday_entitlement;
            $user->date_of_joining      = $date_of_joining;
            $user->date_of_leaving      = $date_of_leaving;
            $user->status               = $request->status;
            $user->phone_no             = $request->phone_no;

            $user->current_location     = nl2br(trim($request->current_location));
            $user->personal_info        = nl2br(trim($request->personal_info));
            $user->banking_info         = nl2br(trim($request->banking_info));
            $user->qualification_info   = nl2br(trim($request->qualification_info));

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
            if(!isset($user->image)) {
                $user->image = '';
            }

            //if checkbox is checked
            if(isset($data['assign_right_check'])) {
                //save access rights
                $access_rights = AccessRight::select('id')
                                            ->where('disabled','0')
                                            ->where('submodule_name','View')
                                            ->orWhere('submodule_name',' ')
                                            ->get()->toArray();
                //echo "<pre>"; print_r($access_rights);
                if(!empty($access_rights)){
                    $access_rights_ids = array_map(function($v){ return $v['id']; }, $access_rights);
                    $access_rights_ids = implode(',', $access_rights_ids);
                    $user->access_rights = $access_rights_ids;
                }
            }

            if($user->save()){
                User::saveQualification($data,$user->id);
                return redirect('admin/managers')->with('success', 'Manager added successfully.');
            } 
            else{
                return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
            }
        }
        $companies  = Admin::select('id','name', 'user_name', 'email', 'company')
                            ->where('access_type','O')
                            ->where('is_deleted','0')
                            ->get()
                            ->toArray();
        $access_levels  = AccessLevel::select('id','name')->where('home_id', $home_id)->get()->toArray();

        $page="managers";
        return view('backEnd.managers.form',compact('page','companies','access_levels'));
    }

    // public function add(Request $request)
    // {

    //     $admin                  = Session::get('scitsAdminSession');
    //     $access_type            = Session::get('scitsAdminSession')->access_type;
    //     $selected_home_id       = Session::get('scitsAdminSession')->home_id;
    //     $selected_company_id    = Home::where('id', $selected_home_id)->value('admin_id');
    //     if ($access_type == 'S') {
    //         $company_id = $selected_company_id;
    //     } else {
    //         $company_id = $admin->id;
    //     }

    //     if ($request->isMethod('post')) {

    //         $manager                = new CompanyManagers;
    //         $manager->company_id    = $company_id;
    //         $manager->name          = $request->name;
    //         $manager->email         = $request->email;
    //         $manager->contact_no    = $request->contact_no;
    //         $manager->address       = $request->address;

    //         if (!empty($_FILES['image']['name'])) {

    //             $tmp_image  =   $_FILES['image']['tmp_name'];
    //             $image_info =   pathinfo($_FILES['image']['name']);
    //             $ext        =   strtolower($image_info['extension']);
    //             $new_name   =   time() . '.' . $ext;

    //             if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {

    //                 $destination = base_path() . managerImageBasePath;

    //                 if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
    //                     $manager->image = $new_name;
    //                 }
    //             }
    //         }
    //         if (!isset($manager->image)) {
    //             $manager->image = '';
    //         }

    //         if ($manager->save()) {
    //             return redirect('admin/managers')->with('success', 'Manager added successfully.');
    //         } else {
    //             return redirect('admin/managers')->with('error', COMMON_ERROR);
    //         }
    //     }

    //     $page = 'managers';
    //     return View('backEnd.managers.form', compact('page'));
    // }

    public function edit(Request $request, $user_id){

        $del_status = '0';
        if($request->del_status) { //for achive users
            $del_status = $request->del_status;
        } 

        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id; 
        if(!Session::has('scitsAdminSession')) {   
            return redirect('admin/login');
        }

        if($request->isMethod('post')) {

            $data = $request->input();
            $company_ids = implode(',', $data['company_id']);
            // echo "<pre>"; print_r($data['company_id']);

            if(!empty($company_ids)){
                $home_ids = Home::select('id')->whereIn('admin_id',$data['company_id'])->get()->toArray();
                $home_ids = array_map(function($v){ return $v['id'];  }, $home_ids);
                $home_ids = implode(',', $home_ids);
            }

            $user = User::find($user_id);

            if(!empty($user)) {

                //comparing su home_id
                $u_home_ids = User::where('id',$user_id)->value('home_id');
                $u_home_ids = explode(',', $u_home_ids);
                /*if($home_id != $u_home_id) {*/
                if(!in_array($home_id,$u_home_ids)){
                    return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
                }

                if(!empty($data['date_of_joining'])) {

                    $date_of_joining = date('Y-m-d',strtotime($data['date_of_joining']));
                } else {
                    $date_of_joining = null;
                }

                if(!empty($data['date_of_leaving'])) {

                    $date_of_leaving = date('Y-m-d',strtotime($data['date_of_leaving']));
                } else {
                    $date_of_leaving = null;   
                }

                $user_old_image         = $user->image;
                $user->name             = $request->name;
                $user->home_id          = $home_ids;
                //$user->user_name        = $request->user_name;
                $user->email            = $request->email;
                $user->job_title        = $request->job_title;
                $user->access_level     = '';
                $user->description      = $request->description;
                $user->payroll          = $request->payroll;
                $user->holiday_entitlement = $request->holiday_entitlement;
                $user->date_of_joining  = $date_of_joining;
                $user->date_of_leaving  = $date_of_leaving;
                $user->status           = $request->status;
                $user->phone_no         = $request->phone_no;
                if(!empty($company_ids)){
                    $user->company_id   = $company_ids;
                }
                $user->user_type            = 'CM';
                $user->current_location     =  nl2br(trim($request->current_location));
                $user->personal_info        =  nl2br(trim($request->personal_info));
                $user->banking_info         =  nl2br(trim($request->banking_info));
                $user->qualification_info   =  nl2br(trim($request->qualification_info));
                /*if(!empty($request->password))
                {
                    $user->password   = Hash::make($request->password);
                }*/

                if(!empty($_FILES['image']['name'])) {
                    $tmp_image  =   $_FILES['image']['tmp_name'];
                    $image_info =   pathinfo($_FILES['image']['name']);
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   time().'.'.$ext; 

                    if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                    {
                        $destination=   base_path().userProfileImageBasePath; 
                        if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                        {
                            if(!empty($user_old_image)){
                                if(file_exists($destination.'/'.$user_old_image))
                                {
                                    unlink($destination.'/'.$user_old_image);
                                }
                            }
                            $user->image = $new_name;
                        }
                    }
                }

                //if checkbox is checked
                if(isset($data['assign_right_check'])) {
                    //save access rights of user 

                    $access_rights = AccessRight::select('id')
                                                ->where('disabled','0')
                                                ->where('submodule_name','View')
                                                ->orWhere('submodule_name',' ')
                                                ->get()->toArray();
                    // echo "<pre>"; print_r($access_rights); die;
                    if(!empty($access_rights)){

                        $access_rights_ids = array_map(function($v){ return $v['id']; }, $access_rights);
                        $access_rights_ids = implode(',', $access_rights_ids);
                        $user->access_rights = $access_rights_ids;
                    }
                }

                if($user->save()) {

                   User::saveQualification($data,$user->id);
                   return redirect('admin/managers')->with('success','Manager Updated successfully.'); 
                } else {
                    return redirect()->back()->with('error','Manager could not be Updated.'); 
                } 
            } else {
                    return redirect('admin/')->with('error','Sorry, Manager does not exists');
            }
        }

        $companies  = Admin::select('id','name', 'user_name', 'email', 'company')
                        ->where('access_type','O')
                        ->where('is_deleted','0')
                        ->get()
                        ->toArray();

        $user_info =  User::with('certificates')
                        ->where('id', $user_id)
                        ->where('is_deleted', $del_status)
                        ->first();

        if(!empty($user_info)) { 

            $array = explode(',',$user_info->home_id);
            /*if($user_info->home_id != $home_id) {*/
            if(!in_array($home_id,$array)){
                return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
            }
        } else {
                return redirect('admin/')->with('error','Sorry, User does not exists');
        }

        $access_levels  = AccessLevel::select('id','name')->where('home_id', $home_id)->get()->toArray();

        $page   = "managers";
        return view('backEnd.managers.form',compact('page','del_status','companies','access_levels','user_info'));
    }

    // public function edit(Request $request, $manager_id = Null)
    // {

    //     $manager = CompanyManagers::select('id', 'name', 'contact_no', 'email', 'address', 'image')
    //         ->where('id', $manager_id)
    //         ->first();

    //     if ($request->isMethod('post')) {
    //         // echo"<pre>"; print_r($request->input()); die;
    //         $update = CompanyManagers::find($manager_id);

    //         if (!empty($update)) {
    //             $old_image          = $update->image;
    //             $update->name       = $request->name;
    //             $update->contact_no = $request->contact_no;
    //             $update->email      = $request->email;
    //             $update->address    = $request->address;

    //             if (!empty($_FILES['image']['name'])) {
    //                 $tmp_image  =   $_FILES['image']['tmp_name'];
    //                 $image_info =   pathinfo($_FILES['image']['name']);
    //                 $ext        =   strtolower($image_info['extension']);
    //                 $new_name   =   time() . '.' . $ext;

    //                 if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
    //                     $destination = base_path() . managerImageBasePath;
    //                     if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
    //                         if (!empty($old_image)) {
    //                             if (file_exists($destination . '/' . $old_image)) {
    //                                 unlink($destination . '/' . $old_image);
    //                             }
    //                         }
    //                         $update->image = $new_name;
    //                     }
    //                 }
    //             }

    //             if ($update->save()) {
    //                 return redirect('admin/managers')->with('success', 'Record updated successfully.');
    //             } else {
    //                 return redirect('admin/managers')->with('error', COMMON_ERROR);
    //             }
    //         } else {
    //             return redirect('admin/managers')->with('error', COMMON_ERROR);
    //         }
    //     }
    //     $page = 'managers';
    //     return View('backEnd.managers.form', compact('page', 'manager'));
    // }

    public function change_status(Request $request)
    {

        $manager_id             = $request->manager_id;
        $admin                  = Session::get('scitsAdminSession');
        $access_type            = Session::get('scitsAdminSession')->access_type;
        $selected_home_id       = Session::get('scitsAdminSession')->home_id;
        $selected_company_id    = Home::where('id', $selected_home_id)->value('admin_id');
        if ($access_type == 'S') {
            $company_id = $selected_company_id;
        } else {
            $company_id = $admin->id;
        }

        $count_active   = CompanyManagers::select('id', 'status')
            ->where('id', '!=', $manager_id)
            ->where('company_id', $company_id)
            ->where('is_deleted', '0')
            ->where('status', '1')
            ->count();
        if ($count_active > 0) {
            return 'false';
        } else {
            $check_status = CompanyManagers::where('id', $manager_id)
                ->where('is_deleted', '0')
                ->value('status');
            if ($check_status == '1') {
                $change_status = CompanyManagers::where('id', $manager_id) // inactive the status
                    ->update(['status' => '0']);
                return '0';
            } elseif ($check_status == '0') {

                $change_status = CompanyManagers::where('id', $manager_id) // active the status
                    ->update(['status' => '1']);
                return '1';
            }
        }
    }

    public function check_email_exists(Request $request)
    {


        $email          = $request->email;
        $manager_id     = $request->manager_id;
        if (!empty($manager_id)) {
            $email_exists   = CompanyManagers::where('email', $email)
                ->where('id', '!=', $manager_id)
                ->count();
        } else {
            $email_exists   = CompanyManagers::where('email', $email)
                ->count();
        }

        if ($email_exists > 0) {
            $r['valid'] = false;
            echo json_encode($r);
        } else {
            $r['valid'] = true;
            echo json_encode($r);
        }
    }

    public function check_contact_no_exists(Request $request)
    {

        $contact_no     = $request->contact_no;
        $manager_id     = $request->manager_id;

        if (!empty($manager_id)) {
            $contact_exists   = CompanyManagers::where('contact_no', $contact_no)
                ->where('id', '!=', $manager_id)
                ->count();
        } else {
            $contact_exists   = CompanyManagers::where('contact_no', $contact_no)
                ->count();
        }

        if ($contact_exists > 0) {
            $r['valid'] = false;
            echo json_encode($r);
        } else {
            $r['valid'] = true;
            echo json_encode($r);
        }
    }

    public function delete($manager_id)
    {
        $manager_delete = User::where('id', $manager_id)->update(['is_deleted' => '1']);
        if ($manager_delete) {
            return redirect('admin/managers')->with('success', 'Record deleted successfully.');
        } else {
            return redirect('admin/managers')->with('error', COMMON_ERROR);
        }
    }

    public function manager_change_status(Request $request)
    {

        if ($request->status == '1') {
            $change_status = User::updateManagerStatus($request->manager_id, '0');
            return '0';
        } elseif ($request->status == '0') {
            $change_status = User::updateManagerStatus($request->manager_id, '1');
            return '1';
        }
    }

    public function send_user_set_pass_link_mail($user_id = NULL)
    {

        //compare home_id
        $admin     = Session::get('scitsAdminSession');
        $home_id   = $admin->home_id;
        $u_home_id = DB::table('user')->where('id', $user_id)->where('is_deleted', '0')->value('home_id');
        $u_home_id = explode(',', $u_home_id);
        if (!in_array($home_id, $u_home_id)) {
            return 'You are not authorized to send the credentials.';
        }
        //send credentials for user              
        $response = User::sendCredentials($user_id);
        echo $response;
        die;
    }

    // public function delete($user_id)
    // {

    //     if (!empty($user_id)) {
    //         $updated = DB::table('user')->where('id', $user_id)->update(['is_deleted' => '1']);

    //         if ($updated) {
    //             return redirect('admin/managers')->with('success', 'Manager deleted Successfully.');
    //         } else {
    //             return redirect('admin/managers')->with('error', COMMON_ERROR);
    //         }
    //     }
    // }
}
