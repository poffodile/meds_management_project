<?php
namespace App\Http\Controllers\backEnd\superAdmin;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use App\Admin, App\CompanyPaymentInformation, App\CompanyPayment; 
use DB; 
use Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function system_admins(Request $request)
    {	
                
        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id; 

        $del_status = '0';
        if($request->user) { //for achive users
            $del_status = '1';
        }

        $system_admin_results   = Admin::select('id','name', 'user_name', 'email', 'company', 'qr_code_id')
                                        ->where('access_type','O')
                                        ->where('is_deleted', $del_status);
        $search = '';

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
            $system_admin_results = $system_admin_results->where('name','like','%'.$search.'%');
        }

        /*if($limit == 'all') {
            $users = $users_query->get();
        } else{
            $users = $users_query->paginate($limit);
        }*/

        $system_admins = $system_admin_results->paginate($limit);
       
        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'system-admins';

       	return view('backEnd/superAdmin/admin/admins', compact('page','limit','system_admins','search','del_status')); //users.blade.php
    }

    public function add(Request $request)
    { 
      	if($request->isMethod('post'))
    	{
            $admin = Session::get('scitsAdminSession');
            // $home_id = $admin->home_id; 
            $address = $request->address;
            $apiKey = 'AIzaSyAMCKKwljh4nvmKVhFHngldmyw7At9rndg'; // Google maps now requires an API key.
            // Get JSON results from this request
            $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);
           
            $geo = json_decode($geo, true); // Convert the JSON to an array
            
            if (isset($geo['status']) && ($geo['status'] == 'OK')) {
              $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
              $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
            }
            

    	    $system_admin               = new Admin;
            // $system_admin->home_id      = $home_id;
            $system_admin->name         = $request->name;
            $system_admin->user_name    = $request->user_name;
            $system_admin->email        = $request->email;
            $system_admin->company      = $request->company;
            $system_admin->address      = $request->address; 
            $system_admin->post_code    = $request->post_code; 
            $system_admin->latitude     = $latitude; 
            $system_admin->longitude    = $longitude; 
            $system_admin->access_type  = 'O';
            $system_admin->password     = '';
            //$system_admin->password     = md5($request->password);
                
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
                        $system_admin->image = $new_name;
                    }
                }
            }
            if(!isset($system_admin->image)) {
                $system_admin->image = '';
            }

    		if($system_admin->save())
                {
        			return redirect('admin/system-admins')->with('success', 'System Admin added successfully.');
        		} 
            else
                {
        			 return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
        		}
    	}
        $page = 'system-admins';
        return view('backEnd/superAdmin/admin/admin_form', compact('page'));
    }
   			
   	public function edit(Request $request, $system_admin_id) {       
        
        $del_status = '0';
        if($request->del_status) { //for achive users
            $del_status = $request->del_status;
        }

        if(!Session::has('scitsAdminSession'))
        {   
            return redirect('admin/login');
        }
        if($request->isMethod('post'))
        {
            $address = $request->address;
            $apiKey = 'AIzaSyCPmAAbKW3OvAqDoEXdetwiP6X0TF7CJL4'; // Google maps now requires an API key. 
            // $apiKey = 'AIzaSyAMCKKwljh4nvmKVhFHngldmyw7At9rndg'; // Google maps now requires an API key. 
            // Get JSON results from this request
            $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);
            $geo = json_decode($geo, true); // Convert the JSON to an array
            
            if (isset($geo['status']) && ($geo['status'] == 'OK')) {
              $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
              $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
            }
            $system_admin               = Admin::find($system_admin_id);
            // $system_admin->home_id      = $home_id;
            $admin_old_image            = $system_admin->image;
            $system_admin->name         = $request->name;
            $system_admin->user_name    = $request->user_name;
            $system_admin->email        = $request->email;
            $system_admin->company      = $request->company;
            $system_admin->address      = $request->address; 
            $system_admin->post_code    = $request->post_code; 
            $system_admin->latitude     = $latitude; 
            $system_admin->longitude    = $longitude; 
            //$system_admin->password     = $request->password;
            // if(!empty($request->password)){
            //     $system_admin->password = md5($request->password);
            // }
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
                            $system_admin->image = $new_name;
                        //echo "okk";

                    //     $system_admin->image = $new_name;
                    //     echo "<pre>";
                    //     print_r($system_admin->image);
                    //     die;
                    // }
                    // else{
                    //     echo "noo";
                    // }
                    // die;
                    }
                }
            }
            
            if(!isset($system_admin->image)) {
                $system_admin->image = '';
            }

            if($system_admin->save())
            {
               return redirect('admin/system-admins')->with('success','System Admin Updated successfully.'); 
            } 
            else
            {
               return redirect()->back()->with('error','System Admin could not be Updated.'); 
            }  
        }

       	$system_admins = DB::table('admin')
                            ->where('id', $system_admin_id)
                            ->where('is_deleted', $del_status)
                            ->first();
        $page = 'system-admins';
        return view('backEnd/superAdmin/admin/admin_form', compact('system_admins','page','del_status'));
    }

    public function delete($system_admin_id)
    {
	   if(!empty($system_admin_id))
       {    
            // $updated = Admin::find($system_admin_id);
            // $updated_image = $updated->image;
            // $destination = base_path().adminbasePath; 

            // if(!empty($updated_image)){
            //     if(file_exists($destination.'/'.$updated_image))
            //     {
            //         unlink($destination.'/'.$updated_image);
            //     }
            // }

            $updated = DB::table('admin')->where('id', $system_admin_id)->update(['is_deleted' => '1']);

            if($updated){
                return redirect('admin/system-admins')->with('success','System Admin deleted Successfully.'); 
            } else{
                return redirect('admin/system-admins')->with('error','System Admin could not be deleted.'); 
            }
        }
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
    //     else
    //     {
    //         echo '{"valid":true}';die;
    //     }    
    // }

    public function send_system_admin_set_pass_link_mail(Request $request, $system_admin_id = NULL)
    {   
        $response = Admin::sendCredentials($system_admin_id);
        echo $response; die;
    }

    public function show_set_password_form_system_admin(Request $request, $system_admin_id = null, $security_code = null){

        $decoded_system_admin_id = convert_uudecode(base64_decode($system_admin_id));
        $decoded_security_code   = convert_uudecode(base64_decode($security_code));
        //$admin_user_name         = $user_name;
        $admin = Admin::where('id',$decoded_system_admin_id)
                        ->where('security_code',$decoded_security_code)
                        ->first();
        
        if(!empty($admin)){ 
            $user_name = $admin->user_name;
            return view('backEnd.admin_set_password',compact('system_admin_id','security_code','user_name'));
        } else{ 
            echo "Password Already Set";
        }

    }

    public function set_password_system_admin(Request $request)
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

        $system_admin_id = convert_uudecode(base64_decode($data['system_admin_id']));
        $security_code   = convert_uudecode(base64_decode($data['security_code']));
        $user_name       = $data['user_name'];

        $admin = Admin::where('id',$system_admin_id)
                        ->where('security_code',$security_code)
                        ->where('user_name', $user_name)
                        ->first();
        // echo "<pre>";
        // print_r($admin);
        // die;
        $admin->security_code = '';

        $admin->password =   md5($data['password']);
        
        if($admin->save())  {   
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
    public function check_company_exist(Request $request){
        $count = Admin::where('company',$request->company)->where('is_deleted', 0)->count();

        if($count > 0){
            echo '{"valid":false}'; die; // for bootstrap validations
            // echo json_encode(false);      //  for jquery validations
        }else{
            echo '{"valid":true}'; die;  // for bootstrap validations
            // echo json_encode(true);      //  for jquery validations
        }    
    }

    public function package_detail($system_admin_id, Request $request){

        $package_detail = CompanyPaymentInformation::select('cc.package_type','cc.home_range','paid_amount','expiry_date','cc.id')
                                                    ->where('admin_id',$system_admin_id)
                                                    ->join('company_charges as cc','cc.id','company_payment_information.company_charges_id')
                                                    ->orderBy('company_payment_information.id','desc')
                                                    ->first();
                                                    // echo "<pre>"; print_r($package_detail); die;
        if(!empty($package_detail)){

            $home_range = explode('-', $package_detail->home_range);
            $last_range = $home_range[1];
            if($package_detail->paid_amount != '0'){

                $amount = explode('%2e', $package_detail->paid_amount);
                $paid_amount = $amount[0].'.'.$amount[1];
            }
        }else{
            return redirect()->back()->with('error','No package selected');
            $last_range = '';
            $paid_amount = '';
        }

        if($request->isMethod('post')){
            
            if(!empty($request->extra_day)){
            // echo "<pre>"; print_r($request->input()); //die;
                $extra_day              = $request->extra_day;
                $system_admin_id        = $request->system_admin_id;
                $company_charges_id     = $request->company_charges_id;

                $company_payment_dtl = CompanyPayment::select('expiry_date')
                                        ->where('admin_id',$system_admin_id)
                                        ->where('company_charges_id',$company_charges_id)
                                        ->first();
                if(!empty($company_payment_dtl)){
                    $new_expiry_date = date('Y-m-d H:i:s',strtotime('+'.$extra_day.'days',strtotime($company_payment_dtl->expiry_date)));

                    if(!empty($new_expiry_date)){
                        // $update = $company_payment_dtl->update(['expiry_date'=> $new_expiry_date]);

                        $update = CompanyPayment::select('expiry_date')
                                        ->where('admin_id',$system_admin_id)
                                        ->where('company_charges_id',$company_charges_id)
                                        ->update(['expiry_date'=> $new_expiry_date]);
                        if($update){
                            $upd = CompanyPaymentInformation::where('admin_id',$system_admin_id)
                                                            ->where('company_charges_id',$company_charges_id)
                                                            ->orderBy('id','desc')
                                                            ->update(['expiry_date'=>$new_expiry_date]);
                            if($upd){
                                return redirect('admin/system-admins')->with('success','Current package expended successfully');
                            }else{
                                
                                return redirect()->back()->with('error',COMMON_ERRRO);
                            }
                        }
                    }
                    // echo "<pre>"; print_r($new_expiry_date); die;
                }
                // echo "<pre>"; print_r($company_payment_dtl); die;

                
            }
        }

        $page = 'system-admins';

        return view('backEnd/superAdmin/admin/admin_package_detail', compact('page','package_detail','last_range','paid_amount','system_admin_id')); 
    }


    
}