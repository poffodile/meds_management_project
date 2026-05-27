<?php
namespace App\Http\Controllers\backEnd\homeManage;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\Home;  
use App\Admin, App\SuperAdmin; 
use DB; 
use Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index(Request $request,$home_id = null){
        
        $admin    = Session::get('scitsAdminSession');

        $del_status = '0';
        if($request->user) { //for achive users
            $del_status = '1';
        }

        //$home_id  = $admin->home_id; 
       // $admin_id = $admin->id;
        //echo $home_id; die;
        $admin_query = Admin::select('id','name','user_name','email')
                                    ->where('access_type','A')
                                    ->where('is_deleted', $del_status)
                                    ->where('home_id','=',$home_id);

        $search="";

        if(isset($request->limit))
        {
            $limit = $request->limit;
            Session::put('page_record_limit',$limit);
        } else {

            if(Session::has('page_record_limit')){
                $limit = Session::get('page_record_limit');
            } else{
                $limit = 20;
            }
        }
        if(isset($request->search))
        {
            $search      = trim($request->search);
            $admin_query = $admin_query->where('name','like','%'.$search.'%');
        }

        $admins = $admin_query->paginate($limit);

        //echo "<pre>"; print_r($admins); die;
        $page = 'homelist';
        return view('backEnd/homeManage/home/admin/admin',compact('page','limit','admins','search','home_id','del_status'));
    }	

    public function add(Request $request, $home_id){

        if($request->isMethod('post'))
        {  
            $request->validate([
                'name'      => 'required',
                'user_name' => 'required|unique:admin,user_name|unique:user,user_name',
                'email'     => 'required|email',
            ]);

            $admin               = new Admin;
            // $system_admin->home_id      = $home_id;
            $admin->name         = $request->name;
            $admin->user_name    = $request->user_name;
            $admin->email        = $request->email;
            //$admin->company      = $company;
            $admin->home_id      = $home_id;
            $admin->access_type  = 'A';
            $admin->password     = '';

            if(!empty($_FILES['image']['name']))
            {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time().'.'.$ext; 
               
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                {
                    $destination = base_path().adminbasePath; 
                  
                    if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                    {
                        $admin->image = $new_name;
                    }
                }
            }
            if(!isset($admin->image)) {
                $admin->image = '';
            }

            if($admin->save())
                {
                    // Create corresponding user in user table
                    $user = new \App\User();
                    $user->holiday_entitlement = '';
                    $user->description = '';
                    $user->job_title = '';
                    $user->name = $request->name;
                    $user->user_name = $request->user_name;
                    $user->email = $request->email;
                    $user->password = '';
                    $user->user_type = 'A';
                    $user->admn_id = \App\Home::where('id', $home_id)->where('is_deleted', 0)->value('admin_id');
                    $user->home_id = $home_id;
                    $user->status = 1;
                    $user->is_deleted = '0';
                    $user->image = $admin->image;
                    $user->save();

                    return redirect('admin/homelist/home-admin/'.$home_id)->with('success', 'Home Admin added successfully.');
                } 
            else
                {
                     return redirect()->back()->with('error', COMMON_ERROR);
                }
        }
      
       	$page = 'homelist';
        return view('backEnd/homeManage/home/admin/admin_form',compact('page','home_id'));
    }

    public function edit(Request $request, $home_admin_id){
      
        if(!Session::has('scitsAdminSession')){   
            return redirect('admin/login');
        }

        $del_status = '0';
        if($request->del_status) { //for achive users
            $del_status = $request->del_status;
        } 

        if($request->isMethod('post')){
             $company = '';

            $admin               = Admin::find($home_admin_id);

            $request->validate([
                'name'      => 'required',
                'user_name' => 'required|unique:admin,user_name,' . $home_admin_id . '|unique:user,user_name,' . $admin->user_name . ',user_name',
                'email'     => 'required|email',
            ]);

            $old_username        = $admin->user_name;
            // $system_admin->home_id      = $home_id;
            $admin_old_image     = $admin->image;
            $admin->name         = $request->name;
            $admin->user_name    = $request->user_name;
            $admin->email        = $request->email;
            //$admin->company      = $company;
            //$admin->home_id      = $home_id;

            if(!empty($_FILES['image']['name']))
            {
                $tmp_image  =   $_FILES['image']['tmp_name'];
                $image_info =   pathinfo($_FILES['image']['name']);
                $ext        =   strtolower($image_info['extension']);
                $new_name   =   time().'.'.$ext; 
               
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
                {
                    $destination = base_path().adminbasePath; 
                  
                    if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                    {
                        if(!empty($admin_old_image)){
                            if(file_exists($destination.'/'.$admin_old_image))
                            {
                                unlink($destination.'/'.$admin_old_image);
                            }
                        }
                            $admin->image = $new_name;
                         }
                }
            }
            
            if(!isset($admin->image)) {
                $admin->image = '';
            }

            if($admin->save())
            {
               // Update corresponding user in user table
               \App\User::updateOrCreate(
                   ['user_name' => $old_username, 'user_type' => 'A'],
                   [
                       'name'      => $request->name,
                       'user_name' => $request->user_name,
                       'email'     => $request->email,
                       'image'     => $admin->image,
                       'is_deleted'=> '0'
                   ]
               );
               return redirect('admin/homelist/home-admin/'.$admin->home_id)->with('success', 'Home Admin updated successfully.'); 
            } 	
            else
            {
               return redirect()->back()->with('error', COMMON_ERROR);
            }  
        }

        $admin = DB::table('admin')
                    ->where('id', $home_admin_id)
                    ->where('is_deleted', $del_status)
                    ->first();
        $home_id = $admin->home_id;

        $page="home_admin";
        return view('backEnd/homeManage/home/admin/admin_form',compact('page','admin','home_admin_id','home_id','del_status'));
    }

    public function delete($user_id){
       
        if(!empty($user_id))
       {    
            $admin = DB::table('admin')->where('id', $user_id)->first();
            $updated = DB::table('admin')->where('id', $user_id)->update(['is_deleted' => '1']);

            if($updated){
                if ($admin) {
                    DB::table('user')->where('user_name', $admin->user_name)->where('user_type', 'A')->update(['is_deleted' => '1']);
                }
                return redirect()->back()->with('success','User deleted Successfully.'); 
            } else{
                return redirect()->back()->with('error',COMMON_ERROR); 
            }
        }
 
    }

    public function send_set_password_link_mail(Request $request, $super_admin_id = NULL)
    {   
        $response = Admin::sendCredentials($super_admin_id);
        echo $response; die;
    }

    public function show_set_password_form_super_admin(Request $request, $super_admin_id = null, $security_code = null){

        $decoded_super_admin_id = convert_uudecode(base64_decode($super_admin_id));
        $decoded_security_code   = convert_uudecode(base64_decode($security_code));
        //$admin_user_name         = $user_name;
        $super_admin = Admin::where('id',$decoded_super_admin_id)
                        ->where('security_code',$decoded_security_code)
                        ->first();
        
        if(!empty($super_admin)){ 
            $user_name = $super_admin->user_name;
            return view('backEnd.admin_set_password',compact('super_admin_id','security_code','user_name'));
        } else{ 
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
        if(empty($data['password']))
        {
            return redirect()->back()->with('error','Please Enter Password');
        }
        else if($data['password'] != $data['confirm_password'])
        {
            return redirect()->back()->with('error', 'Password & confirm password does not matched.');
        }

        $super_admin_id = convert_uudecode(base64_decode($data['super_admin_id']));
        $security_code   = convert_uudecode(base64_decode($data['security_code']));
        $user_name       = $data['user_name'];

        $super_admin = Admin::where('id',$super_admin_id)
                        ->where('security_code',$security_code)
                        ->where('user_name', $user_name)
                        ->first();
        // echo "<pre>";
        // print_r($admin);
        // die;
        $super_admin->security_code = '';

        $super_admin->password =   md5($data['password']);
        
        if($super_admin->save())  {   
            // Update in user table too
            \App\User::where('user_name', $user_name)->where('user_type', 'A')->update([
                'password' => \Hash::make($data['password'])
            ]);

            //logging out any previous loggedin admin
            Session::forget('scitsAdminSession');
            return redirect('admin/login')->with('success','You have set your password successfully.');
        }  else  { 
            return redirect('admin/login')->with('error','Some error occured. Please try again later');          
        }
    }

    public function check_username_exist(Request $request){
        
        $count = Admin::where('user_name',$request->user_name)->count();

        if($count > 0)
        {
            echo '{"valid":false}'; die; // for bootstrap validations
            // echo json_encode(false);      //  for jquery validations
        }    
        else
        {
            echo '{"valid":true}'; die;  // for bootstrap validations
            // echo json_encode(true);      //  for jquery validations
        }    
    }


}