<?php
namespace App\Http\Controllers\backEnd;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\User, App\Home, App\UserQualification, App\AccessLevel;  
use DB; 
use Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\CompanyDepartment;

class UserController extends Controller
{

    public function users(Request $request) {	
        
        // echo "<pre>"; print_r($request->input()); die;
        $admin   = Session::get('scitsAdminSession');
        // dd($admin);
        $home_id = $admin->home_id; 

        $del_status = '0';
        if($request->user) { //for achive users
            $del_status = '1';
        }
       
        if(!empty($home_id)) {

            $users_query =  DB::table('user as u')
                                ->select('u.id','u.name', 'u.email', 'u.job_title', 'u.access_level','al.name as access_level_name')
                                ->where('u.is_deleted',$del_status)
                                ->where('u.home_id',$home_id)
                                ->leftJoin('access_level as al','al.id','u.access_level')
                                ->where('u.user_type','!=','A');

            $search = '';

            if(isset($request->limit)) {
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
                $users_query = $users_query->where('u.name','like','%'.$search.'%');
            }

            /*if($limit == 'all') {
                $users = $users_query->get();
            } else{
                $users = $users_query->paginate($limit);
            }*/

            $users = $users_query->paginate($limit);
            //echo '<pre>'; print_r($users); die;
        } else {
            return redirect('admin/')->with('error',NO_HOME_ERR);
        }

        $page = 'users';

       	return view('backEnd/users', compact('page','limit','users','search','del_status')); //users.blade.php
    }


    public function add(Request $request) { 

        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id; 

        if($request->isMethod('post')) {
            
            $data = $request->input();
            
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
            $user->home_id              = $home_id;
            $user->name                 = $request->name;
            $user->user_name            = $request->user_name;
            $user->email                = $request->email;
            //$random_no                  = rand(111111,999999);
            $user->password             = '';
            //$user->password             = Hash::make($random_no);
            //$user->password           = Hash::make($request->password);
            $user->job_title            = $request->job_title;
            $user->access_level         = $request->access_level;
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
                //save access rights according to access level
                $access_level_info  = AccessLevel::select('id','access_rights')
                                    ->where('home_id', $home_id)
                                    ->where('id', $user->access_level)
                                    ->first();

                if(!empty($access_level_info)){
                    $user->access_rights = $access_level_info->access_rights;
                }
            }

            if($user->save())
                {
                    User::saveQualification($data,$user->id);
                    return redirect('admin/users')->with('success', 'User added successfully.');
                } 
            else
                {
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
        }
        
        $access_levels  = AccessLevel::select('id','name')->where('home_id', $home_id)->get()->toArray();
        $page = 'users';
        $company_departments = CompanyDepartment::getActiveCompanyDepartment();
        return view('backEnd.user_form', compact('page','access_levels','company_departments'));
    }
            
    public function edit(Request $request, $user_id) {   
        
        //echo "<pre>"; print_r($request->input()); die;
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
            $user = User::find($user_id);
            
            if(!empty($user)) {

                //comparing su home_id
                $u_home_id = User::where('id',$user_id)->value('home_id');
                if($home_id != $u_home_id) {
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
                //$user->user_name        = $request->user_name;
                $user->email            = $request->email;
                $user->job_title        = $request->job_title;
                $user->access_level     = $request->access_level;
                $user->description      = $request->description;
                $user->payroll          = $request->payroll;
                $user->holiday_entitlement = $request->holiday_entitlement;
                $user->date_of_joining  = $date_of_joining;
                $user->date_of_leaving  = $date_of_leaving;
                $user->status           = $request->status;
                $user->phone_no         = $request->phone_no;

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
                    //save access rights according to access level
                    $access_level_info  = AccessLevel::select('id','access_rights')
                                        ->where('home_id', $home_id)
                                        ->where('id', $user->access_level)
                                        ->first();

                    if(!empty($access_level_info)){
                        $user->access_rights = $access_level_info->access_rights;
                    }
                }

                if($user->save()) {

                   User::saveQualification($data,$user->id);
                   return redirect('admin/users')->with('success','User Updated successfully.'); 
                } else {
                    return redirect()->back()->with('error','User could not be Updated.'); 
                } 
            } else {
                    return redirect('admin/')->with('error','Sorry, User does not exists');
            }
        }

        $user_info  = User::with('certificates')
                        ->where('id', $user_id)
                        ->where('is_deleted', $del_status)
                        ->first();
        // echo "<pre>"; print_r($user_info); die;

        if(!empty($user_info)) { 
            if($user_info->home_id != $home_id) {
                return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
            }
        } else {
                return redirect('admin/')->with('error','Sorry, User does not exists');
        }

        $access_levels  = AccessLevel::select('id','name')->where('home_id', $home_id)->get()->toArray();
        $page = 'users';
        $company_departments = CompanyDepartment::getActiveCompanyDepartment();
        return view('backEnd/user_form', compact('user_info','page','access_levels','del_status', 'company_departments'));
    }

    public function delete($user_id) {   

        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

	    if(!empty($user_id)) {
            $updated = DB::table('user')->where('id', $user_id)->where('home_id', $home_id)->update(['is_deleted' => '1']);

            if(!empty($updated)) { 
                // return redirect('admin/')->with('error','Sorry, User does not exists');
                return redirect('admin/users')->with('success','User deleted Successfully.'); 
            } else {
                return redirect('admin/users')->with('error',UNAUTHORIZE_ERR); 
            }
        } else {
                return redirect('admin/users')->with('error','Sorry, User does not exists'); 
        }
    }

    public function delete_certificates($id) {
        
        $del = UserQualification::where('id',$id)->update(['is_deleted'=>1]);
      
        if($del){
            echo 'true';    
        } else{
            echo 'false';
        } 
        die;
    }

    public function send_user_set_pass_link_mail(Request $request, $user_id = NULL) {

        //compare home_id
        $admin     = Session::get('scitsAdminSession');
        $home_id   = $admin->home_id; 
        $u_home_id = DB::table('user')
                        ->where('id', $user_id)
                        ->where('is_deleted','0')
                        ->value('home_id'); 
        if($u_home_id != $home_id) {
            return 'You are not authorized to send the credentials.';
        }
        
        
        // send credentials for user              
        $response = User::sendCredentials($user_id);
            echo $response; die;
    }   

    public function check_username_exist(Request $request) {
    
        $data = $request->input();
        $user_name = '';
        if(is_array($data)) {
            $user_name_arr = array_values($data);
            $user_name = $user_name_arr[0];
        }

        $response = Home::userNameUnique($user_name);

        if($response){
            echo '{"valid": true}';
        } else{
            echo '{"valid": false}'; 
        }
        die;
    }

    // public function check_user_email_exists(Request $request)
    // {

    //     $count = DB::table('user')->where('email',$request->email)->count();
    //     if($count > 0)
    //     {
    //         echo '{"valid":false}';die;
    //     }    
    //     else
    //     {
    //         echo '{"valid":true}';die;
    //     }    
    // }
   
    // public function check_user_edit_email_exists(Request $request)
    // {
       
    //     $count = DB::table('user')->where('email',$request->email)->count();
    //     if($count > 1)
    //     {
    //         echo '{"valid":false}';die;
    //     }    
    //     else{
    //         echo '{"valid":true}';die;
    //     }    
    // }

    /*public function check_username_exist(Request $request) {
        
        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

        $count = DB::table('user')->where('user_name',$request->user_name)->where('home_id', $home_id)->count();

        if($count > 0) {
            echo '{"valid":false}'; die;  // for bootstap validations
            //echo json_encode(false);   // for jquery validations
        }    
        else  {
            echo '{"valid":true}'; die;  // for bootstap validations
            //echo json_encode(true);  //  for jquery validations
        }    
    }*/

    // public function check_edit_username_exists(Request $request){
    //     return 't';
    //     echo "1";
    //     die;
    //     $count = DB::table('user')->where('user_name',$request->user_name)->count();

    //     if($count > 0)
    //     {
    //         echo '{"valid":false}'; die;  // for bootstap validations
    //         //echo json_encode(false);   // for jquery validations
    //     }    
    //     else
    //     {
    //         echo '{"valid":true}'; die;  // for bootstap validations
    //         //echo json_encode(true);   //  for jquery validations
    //     }  

    // }
    
}

