<?php

namespace App\Http\Controllers\backEnd\superAdmin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Admin, App\SuperAdmin;
use DB;
use Hash;
use PDF;
use App\DynamicFormBuilder;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index(Request $request)
    {

        $admin    = Session::get('scitsAdminSession');
        $home_id  = $admin->home_id;
        $admin_id = $admin->id;

        $del_status = '0';
        if ($request->user) { //for achive users
            $del_status = '1';
        }

        $supr_usr_query = Admin::select('id', 'name', 'user_name', 'email')
            ->where('access_type', 'S')
            ->where('is_deleted', $del_status)
            ->where('id', '!=', $admin_id);

        $search = "";

        if (isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit', $limit);
        } else {

            if (Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else {
                $limit = 20;
            }
        }
        if (isset($request->search)) {
            $search      = trim($request->search);
            $supr_usr_query = $supr_usr_query->where('name', 'like', '%' . $search . '%');
        }

        $users = $supr_usr_query->paginate($limit);
        $page = "super_admin_user";
        return view('backEnd/superAdmin/user/users', compact('page', 'limit', 'users', 'search', 'del_status'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $user = Session::get('scitsAdminSession');
            // $home_id = $admin->home_id; 
            $company = 'scits super admin';

            $admin               = new Admin;
            // $system_admin->home_id      = $home_id;
            $admin->name         = $request->name;
            $admin->user_name    = $request->user_name;
            $admin->email        = $request->email;
            $admin->company      = $company;
            $admin->access_type  = 'S';
            $admin->password     = '';

            if (!empty($_FILES['image']['name'])) {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time() . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . adminbasePath;

                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                        $admin->image = $new_name;
                    }
                }
            }
            if (!isset($admin->image)) {
                $admin->image = '';
            }

            if ($admin->save()) {
                return redirect('super-admin/users')->with('success', 'Super Admin added successfully.');
            } else {
                return redirect()->back()->with('error', COMMON_ERROR);
            }
        }

        $page = "super_admin_user";
        return view('backEnd/superAdmin/user/user_form', compact('page'));
    }

    public function edit(Request $request, $user_id)
    {

        $del_status = '0';
        if ($request->del_status) { //for achive users
            $del_status = $request->del_status;
        }

        if (!Session::has('scitsAdminSession')) {
            return redirect('admin/login');
        }
        if ($request->isMethod('post')) {
            $company = 'scits super admin';

            $user               = Admin::find($user_id);
            // $system_admin->home_id      = $home_id;
            $user_old_image     = $user->image;
            $user->name         = $request->name;
            $user->user_name    = $request->user_name;
            $user->email        = $request->email;
            $user->company      = $company;

            if (!empty($_FILES['image']['name'])) {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time() . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . adminbasePath;

                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                        if (!empty($admin_old_image)) {
                            if (file_exists($destination . '/' . $admin_old_image)) {
                                unlink($destination . '/' . $admin_old_image);
                            }
                        }
                        $user->image = $new_name;
                    }
                }
            }

            if (!isset($user->image)) {
                $user->image = '';
            }

            if ($user->save()) {
                return redirect('super-admin/users')->with('success', 'Super Admin Updated successfully.');
            } else {
                return redirect()->back()->with('error', COMMON_ERROR);
            }
        }

        $user   = DB::table('admin')
            ->where('id', $user_id)
            ->where('is_deleted', $del_status)
            ->first();

        $page   = "super_admin_user";
        return view('backEnd/superAdmin/user/user_form', compact('page', 'user', 'del_status'));
    }

    public function delete($user_id)
    {

        if (!empty($user_id)) {
            $updated = DB::table('admin')->where('id', $user_id)->update(['is_deleted' => '1']);

            if ($updated) {
                return redirect('super-admin/users')->with('success', 'User deleted Successfully.');
            } else {
                return redirect('super-admin/users')->with('error', COMMON_ERROR);
            }
        }
    }

    public function send_set_password_link_mail(Request $request, $super_admin_id = NULL)
    {
        $response = SuperAdmin::sendCredentials($super_admin_id);
        echo $response;
        die;
    }

    public function show_set_password_form_super_admin(Request $request, $super_admin_id = null, $security_code = null)
    {

        $decoded_super_admin_id = convert_uudecode(base64_decode($super_admin_id));
        $decoded_security_code   = convert_uudecode(base64_decode($security_code));
        //$admin_user_name         = $user_name;
        $super_admin = SuperAdmin::where('id', $decoded_super_admin_id)
            ->where('security_code', $decoded_security_code)
            ->first();

        if (!empty($super_admin)) {
            $user_name = $super_admin->user_name;
            return view('backEnd.admin_set_password', compact('super_admin_id', 'security_code', 'user_name'));
        } else {
            echo "Password Already Set";
        }
    }

    public function set_password_super_admin(Request $request)
    {
        //when admin set his passsword on set password page and press submit
        $data = $request->input();
        // echo "<pre>";
        // print_r($data);
        // die;
        if (empty($data['password'])) {
            return redirect()->back()->with('error', 'Please Enter Password');
        } else if ($data['password'] != $data['confirm_password']) {
            return redirect()->back()->with('error', 'Password & confirm password does not matched.');
        }

        $super_admin_id = convert_uudecode(base64_decode($data['super_admin_id']));
        $security_code   = convert_uudecode(base64_decode($data['security_code']));
        $user_name       = $data['user_name'];

        $super_admin = SuperAdmin::where('id', $super_admin_id)
            ->where('security_code', $security_code)
            ->where('user_name', $user_name)
            ->first();
        // echo "<pre>";
        // print_r($admin);
        // die;
        $super_admin->security_code = '';

        $super_admin->password =   md5($data['password']);

        if ($super_admin->save()) {
            //logging out any previous loggedin admin
            Session::forget('scitsAdminSession');
            return redirect('admin/login')->with('success', 'You have set your password successfully.');
        } else {
            return redirect('admin/login')->with('error', 'Some error occured. Please try again later');
        }
    }

    public function check_username_exist(Request $request)
    {

        $count = SuperAdmin::where('user_name', $request->user_name)->count();

        if ($count > 0) {
            echo '{"valid":false}';
            die; // for bootstrap validations
            // echo json_encode(false);      //  for jquery validations
        } else {
            echo '{"valid":true}';
            die;  // for bootstrap validations
            // echo json_encode(true);      //  for jquery validations
        }
    }

    public function DownloadFormpdf(Request $request)
    {
        // $request->id;
        //print_r($request->patterndata) ; 
        $dataformdata = DynamicFormBuilder::where("id", $request->id)->first();

        $data = [
            'title' => $request->form_title,
            'date' => date('m/d/Y'),
            "formdata" => $dataformdata,
        ];

        $pdf = PDF::loadView('/pdf/form_pdf', $data);
        return $pdf->download('form_title.pdf');
    }
}
