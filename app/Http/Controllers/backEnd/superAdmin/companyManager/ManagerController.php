<?php
namespace App\Http\Controllers\backEnd\superAdmin\companyManager;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\Admin, App\SuperAdmin, App\User, App\AccessLevel, App\saveQualification, App\AccessRight, App\Home;
use DB; 
use Hash;
use Illuminate\Support\Facades\Mail;

class ManagerController extends Controller
{
    public function index(Request $request){

        $admin    = Session::get('scitsAdminSession');
        $home_id  = $admin->home_id; 
        $admin_id = $admin->id;
        
        $del_status = '0';
        /*if($request->user) { //for achive users
            $del_status = '1';
        }*/

        $company_manager_query = User::select('user.id','user.name','user_name','email','phone_no')
                                    ->where('user.user_type','M')
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
        $page="company_manager";

        return view('backEnd/superAdmin/companyManager/index',compact('page','limit','users','search','del_status'));
    }

    public function add(Request $request){
        
        $admin = Session::get('scitsAdminSession');
        $home_id = $admin->home_id; 
        //$del_status = '0';
        if($request->isMethod('post')) {
            // dd($request);
            
            $data = $request->input();
            // echo "<pre>"; print_r($data); die;
            $company_ids = implode(',', $data['company_id']);

            if(isset($request->allHome)){
                if(!empty($company_ids)){
                    $home_ids = Home::select('id')->whereIn('admin_id',$data['company_id'])->get()->toArray();
                    $home_ids = array_map(function($v){ return $v['id'];  }, $home_ids);
                    $home_ids = implode(',', $home_ids);
                }
            } else {
                $home_ids = implode(',', $request->homes);
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
            $user->user_type            = 'M';
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
            // Ram 28/07/2025 this code is hide to give all access permission for Manager 
            // if(isset($data['assign_right_check'])) {
                //save access rights
                $access_rights = AccessRight::select('id')
                                            ->where('disabled','0')
                                            // ->where('submodule_name','View')
                                            // ->orWhere('submodule_name',' ')
                                            ->get()->toArray();
                //echo "<pre>"; print_r($access_rights);
                if(!empty($access_rights)){
                    $access_rights_ids = array_map(function($v){ return $v['id']; }, $access_rights);
                    $access_rights_ids = implode(',', $access_rights_ids);
                    $user->access_rights = $access_rights_ids;
                }
            // }


            if($user->save()){
                User::saveQualification($data,$user->id);
                return redirect('admin/company-managers')->with('success', 'Company Manager added successfully.');
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

        $page="company_manager";
        return view('backEnd/superAdmin/companyManager/form',compact('page','companies','access_levels'));
    }

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

            if(isset($request->allHome)){
                if(!empty($company_ids)){
                    $home_ids = Home::select('id')->whereIn('admin_id',$data['company_id'])->get()->toArray();
                    $home_ids = array_map(function($v){ return $v['id'];  }, $home_ids);
                    $home_ids = implode(',', $home_ids);
                }
            } else {
                $home_ids = implode(',', $request->homes);
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
                $user->user_type            = 'M';
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
                // Ram 28/07/2025 this code is hide to give all access permission for Manager 
                // if(isset($data['assign_right_check'])) {
                    //save access rights of user 
                    
                    $access_rights = AccessRight::select('id')
                                                ->where('disabled','0')
                                                // ->where('submodule_name','View')
                                                // ->orWhere('submodule_name',' ')
                                                ->get()->toArray();
                    // echo "<pre>"; print_r($access_rights); die;
                    if(!empty($access_rights)){
                        
                        $access_rights_ids = array_map(function($v){ return $v['id']; }, $access_rights);
                        $access_rights_ids = implode(',', $access_rights_ids);
                        $user->access_rights = $access_rights_ids;
                    }
                // }

                if($user->save()) {

                   User::saveQualification($data,$user->id);
                   return redirect('admin/company-managers')->with('success','Manager Updated successfully.'); 
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

        $page   = "company_manager";
        return view('backEnd/superAdmin/companyManager/form',compact('page','del_status','companies','access_levels','user_info'));
    }

    public function delete($user_id){
       
        if(!empty($user_id))
       {    
            $updated = DB::table('user')->where('id',$user_id)->update(['is_deleted' => '1']);

            if($updated){
                return redirect('admin/company-managers')->with('success','Manager deleted Successfully.'); 
            } else{
                return redirect('admin/company-managers')->with('error',COMMON_ERROR); 
            }
        }
    }

    public function send_user_set_pass_link_mail(Request $request, $user_id = NULL) {

        //compare home_id
        $admin     = Session::get('scitsAdminSession');
        $home_id   = $admin->home_id; 
        $u_home_id = DB::table('user')
                        ->where('id', $user_id)
                        ->where('is_deleted','0')
                        ->value('home_id'); 
        //if($u_home_id != $home_id) {
        $u_home_id = explode(',', $u_home_id);            
        // if($u_home_id != $home_id) {
        if(!in_array($home_id,$u_home_id)){
            return 'You are not authorized to send the credentials.';
        }
        //send credentials for user              
        $response = User::sendCredentials($user_id);
            echo $response; die;
    }   
   
    public function check_username_exist(Request $request){
        
        $count = User::where('user_name',$request->user_name)->count();

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