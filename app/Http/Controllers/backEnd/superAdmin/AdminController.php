<?php

namespace App\Http\Controllers\backEnd\superAdmin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Admin, App\CompanyPaymentInformation, App\CompanyPayment;
use App\Models\CompanyHomeSetting;
use App\Models\CompanyHomeArea;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function system_admins(Request $request)
    {

        $admin   = Session::get('scitsAdminSession');
        $home_id = $admin->home_id;

        $del_status = '0';
        if ($request->user) { //for achive users
            $del_status = '1';
        }

        $system_admin_results   = Admin::select('id', 'name', 'user_name', 'email', 'company', 'qr_code_id')
            ->where('access_type', 'O')
            ->where('is_deleted', $del_status);
        $search = '';

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
            $system_admin_results = $system_admin_results->where('name', 'like', '%' . $search . '%');
        }

        /*if($limit == 'all') {
            $users = $users_query->get();
        } else{
            $users = $users_query->paginate($limit);
        }*/

        $system_admins = $system_admin_results->paginate($limit);

        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'system-admins';

        return view('backEnd/superAdmin/admin/admins', compact('page', 'limit', 'system_admins', 'search', 'del_status')); //users.blade.php
    }

    // public function add(Request $request)
    // {
    //     if ($request->isMethod('post')) {
    //         $admin = Session::get('scitsAdminSession');
    //         // $home_id = $admin->home_id; 
    //         $address = $request->address;
    //         $apiKey = 'AIzaSyBxoFiKEhpV_lzf-i17vjFb9hZZwHSkZGI'; // Google maps now requires an API key.
    //         // Get JSON results from this request
    //         $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $apiKey);

    //         $geo = json_decode($geo, true); // Convert the JSON to an array

    //         if (isset($geo['status']) && ($geo['status'] == 'OK')) {
    //             $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
    //             $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude
    //         }

    //         // =========================
    //         // 1. SAVE COMPANY (ADMIN)
    //         // =========================

    //         $system_admin               = new Admin;
    //         // $system_admin->home_id      = $home_id;
    //         $system_admin->name         = $request->name;
    //         $system_admin->user_name    = $request->user_name;
    //         $system_admin->email        = $request->email;
    //         $system_admin->company      = $request->company;
    //         $system_admin->address      = $request->address;
    //         $system_admin->post_code    = $request->post_code;
    //         $system_admin->latitude     = $latitude;
    //         // $system_admin->latitude     = "53.4084°";
    //         $system_admin->longitude    = $longitude;
    //         // $system_admin->longitude    =   "2.9916°";
    //         $system_admin->access_type  = 'O';
    //         $system_admin->password     = '';
    //         //$system_admin->password     = md5($request->password);

    //         if (!empty($_FILES['image']['name'])) {
    //             $tmp_image  =   $_FILES['image']['tmp_name'];
    //             $image_info =   pathinfo($_FILES['image']['name']);
    //             $ext        =   strtolower($image_info['extension']);
    //             $new_name   =   time() . '.' . $ext;

    //             if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
    //                 $destination = base_path() . adminbasePath;

    //                 if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
    //                     $system_admin->image = $new_name;
    //                 }
    //             }
    //         }
    //         if (!isset($system_admin->image)) {
    //             $system_admin->image = '';
    //         }

    //         if ($system_admin->save()) {

    //             // =========================
    //             // 2. SAVE COMPANY SETTINGS
    //             // =========================
    //             CompanyHomeSetting::create([
    //                 'company_id' => $system_admin->id,
    //                 'weekly_allowance_service_users'  => $request->weekly_allowance_service_users,
    //                 'monthly_allowance_service_users' => $request->monthly_allowance_service_users,
    //                 'clock_in_range'                  => $request->home_area,
    //             ]);

    //             // =========================
    //             // 3. SAVE COMPANY AREAS
    //             // =========================

    //             // Delete old areas first
    //             CompanyHomeArea::where('company_id', $system_admin->id)->delete();

    //             if ($request->is_home_area == 1 && !empty($request->home_area_names)) {

    //                 foreach ($request->home_area_names as $area) {
    //                     if (!empty($area)) {
    //                         CompanyHomeArea::create([
    //                             'company_id' => $system_admin->id,
    //                             'area_name'  => $area,
    //                         ]);
    //                     }
    //                 }
    //             }


    //             return redirect('admin/system-admins')->with('success', 'System Admin added successfully.');
    //         } else {
    //             return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
    //         }
    //     }
    //     $page = 'system-admins';
    //     return view('backEnd/superAdmin/admin/admin_form', compact('page'));
    // }



    public function add(Request $request)
    {
        Log::info('ADD FUNCTION STARTED');

        if ($request->isMethod('post')) {

            DB::beginTransaction();
            Log::info('Request Data:', $request->all());

            try {

                // =========================
                // 1. VALIDATION
                // =========================
                Log::info('Step 1: Validation started');

                $request->validate([
                    'name'      => 'required',
                    'user_name' => 'required|unique:admin,user_name',
                    'email'     => 'required|email',
                    'post_code' => 'required',
                ]);

                Log::info('Step 1: Validation passed');

                // =========================
                // 2. GET LAT/LONG
                // =========================
                Log::info('Step 2: Geocoding started');

                $latitude = null;
                $longitude = null;

                if (!empty($request->address)) {

                    Log::info('Address found:', ['address' => $request->address]);

                    $apiKey = 'YOUR_GOOGLE_API_KEY';

                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' .
                        urlencode($request->address) . '&key=' . $apiKey;

                    Log::info('Geocode URL:', ['url' => $url]);

                    $geo = @file_get_contents($url);

                    if ($geo) {
                        $geo = json_decode($geo, true);

                        Log::info('Geocode Response:', $geo);

                        if (isset($geo['status']) && $geo['status'] == 'OK') {
                            $latitude  = $geo['results'][0]['geometry']['location']['lat'];
                            $longitude = $geo['results'][0]['geometry']['location']['lng'];

                            Log::info('Lat/Lng Found:', [
                                'lat' => $latitude,
                                'lng' => $longitude
                            ]);
                        } else {
                            Log::warning('Geocode failed:', $geo);
                        }
                    } else {
                        Log::error('Geocode API call failed');
                    }
                }

                // =========================
                // 3. SAVE ADMIN
                // =========================
                Log::info('Step 3: Saving Admin');

                $system_admin = new Admin();

                $system_admin->name       = $request->name;
                $system_admin->user_name  = $request->user_name;
                $system_admin->email      = $request->email;
                $system_admin->company    = $request->company;
                $system_admin->address    = $request->address;
                $system_admin->post_code  = $request->post_code;
                $system_admin->latitude   = $latitude;
                $system_admin->longitude  = $longitude;
                $system_admin->access_type = 'O';
                $system_admin->password   = '';

                // =========================
                // 4. IMAGE UPLOAD
                // =========================
                Log::info('Step 4: Image Upload Check');

                if ($request->hasFile('image')) {

                    $file = $request->file('image');
                    $ext  = strtolower($file->getClientOriginalExtension());

                    Log::info('Image detected:', ['ext' => $ext]);

                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                        $name = time() . '.' . $ext;
                        $path = base_path() . adminbasePath;

                        Log::info('Uploading image to:', ['path' => $path]);

                        $file->move($path, $name);

                        $system_admin->image = $name;

                        Log::info('Image uploaded:', ['name' => $name]);
                    } else {
                        Log::warning('Invalid image extension');
                    }
                } else {
                    Log::info('No image uploaded');
                    $system_admin->image = '';
                }

                $system_admin->save();

                Log::info('Admin saved successfully:', ['id' => $system_admin->id]);

                // =========================
                // 5. COMPANY SETTINGS
                // =========================
                Log::info('Step 5: Saving Company Settings');

                CompanyHomeSetting::create([
                    'company_id'                      => $system_admin->id,
                    'address'                         => $request->home_address,
                    'is_home_area'                    => $request->is_home_area ?? 0,
                    'weekly_allowance_service_users'  => $request->weekly_allowance_service_users,
                    'monthly_allowance_service_users' => $request->monthly_allowance_service_users,
                    'clock_in_range'                  => $request->clock_in_range,
                    'staff_term'                      => $request->staff_term ?? 'Staff',
                    'service_user_term'               => $request->service_user_term ?? 'Service User',
                ]);

                // Clear terminology cache for this company
                \Illuminate\Support\Facades\Cache::forget('terminology_' . $system_admin->id . '_staff_term');
                \Illuminate\Support\Facades\Cache::forget('terminology_' . $system_admin->id . '_service_user_term');

                Log::info('Company settings saved');

                // =========================
                // 6. COMPANY AREAS
                // =========================
                Log::info('Step 6: Saving Company Areas');

                if ($request->is_home_area == 1 && !empty($request->home_area_names)) {

                    foreach ($request->home_area_names as $area) {

                        if (!empty(trim($area))) {

                            CompanyHomeArea::create([
                                'company_id' => $system_admin->id,
                                'area_name'  => $area,
                            ]);

                            Log::info('Area saved:', ['area' => $area]);
                        }
                    }
                } else {
                    Log::info('No areas to save');
                }

                DB::commit();
                Log::info('Transaction committed successfully');

                return redirect('admin/system-admins')
                    ->with('success', 'System Admin added successfully.');
            } catch (\Exception $e) {

                DB::rollback();

                Log::error('ERROR OCCURRED:', [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => $e->getFile(),
                ]);

                return redirect()->back()
                    ->with('error', $e->getMessage());
            }
        }

        Log::info('Loading form view');

        $page = 'system-admins';
        return view('backEnd/superAdmin/admin/admin_form', compact('page'));
    }

    // public function edit(Request $request, $system_admin_id)
    // {

    //     $del_status = '0';
    //     if ($request->del_status) { //for achive users
    //         $del_status = $request->del_status;
    //     }

    //     if (!Session::has('scitsAdminSession')) {
    //         return redirect('admin/login');
    //     }
    //     if ($request->isMethod('post')) {
    //         $address = $request->address;
    //         $apiKey = 'AIzaSyCPmAAbKW3OvAqDoEXdetwiP6X0TF7CJL4'; // Google maps now requires an API key. 
    //         // $apiKey = 'AIzaSyAMCKKwljh4nvmKVhFHngldmyw7At9rndg'; // Google maps now requires an API key. 
    //         // Get JSON results from this request
    //         $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $apiKey);
    //         $geo = json_decode($geo, true); // Convert the JSON to an array

    //         if (isset($geo['status']) && ($geo['status'] == 'OK')) {
    //             $latitude = $geo['results'][0]['geometry']['location']['lat']; // Latitude
    //             $longitude = $geo['results'][0]['geometry']['location']['lng']; // Longitude  
    //         }
    //         $system_admin               = Admin::find($system_admin_id);
    //         // $system_admin->home_id      = $home_id;
    //         $admin_old_image            = $system_admin->image;
    //         $system_admin->name         = $request->name;
    //         $system_admin->user_name    = $request->user_name;
    //         $system_admin->email        = $request->email;
    //         $system_admin->company      = $request->company;
    //         $system_admin->address      = $request->address;
    //         $system_admin->post_code    = $request->post_code;
    //         // $system_admin->latitude     = $latitude; 
    //         $system_admin->latitude     = "53.4084°";
    //         // $system_admin->longitude    = $longitude; 
    //         $system_admin->longitude    =   "2.9916°";
    //         //$system_admin->password     = $request->password;
    //         // if(!empty($request->password)){
    //         //     $system_admin->password = md5($request->password);
    //         // }
    //         if (!empty($_FILES['image']['name'])) {
    //             $tmp_image  =   $_FILES['image']['tmp_name'];
    //             $image_info =   pathinfo($_FILES['image']['name']);
    //             $ext        =   strtolower($image_info['extension']);
    //             $new_name   =   time() . '.' . $ext;

    //             if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
    //                 $destination = base_path() . adminbasePath;

    //                 if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
    //                     if (!empty($admin_old_image)) {
    //                         if (file_exists($destination . '/' . $admin_old_image)) {
    //                             unlink($destination . '/' . $admin_old_image);
    //                         }
    //                     }
    //                     $system_admin->image = $new_name;
    //                     //echo "okk";

    //                     //     $system_admin->image = $new_name;
    //                     //     echo "<pre>";
    //                     //     print_r($system_admin->image);
    //                     //     die;
    //                     // }
    //                     // else{
    //                     //     echo "noo";
    //                     // }
    //                     // die;
    //                 }
    //             }
    //         }

    //         if (!isset($system_admin->image)) {
    //             $system_admin->image = '';
    //         }

    //         if ($system_admin->save()) {
    //             return redirect('admin/system-admins')->with('success', 'System Admin Updated successfully.');
    //         } else {
    //             return redirect()->back()->with('error', 'System Admin could not be Updated.');
    //         }
    //     }

    //     $system_admins = DB::table('admin')
    //         ->where('id', $system_admin_id)
    //         ->where('is_deleted', $del_status)
    //         ->first();
    //     $page = 'system-admins';
    //     return view('backEnd/superAdmin/admin/admin_form', compact('system_admins', 'page', 'del_status'));
    // }

    public function edit(Request $request, $system_admin_id)
    {
        $del_status = $request->del_status ?? '0';

        if (!Session::has('scitsAdminSession')) {
            return redirect('admin/login');
        }

        // =========================
        // POST (UPDATE)
        // =========================
        if ($request->isMethod('post')) {
            Log::info('EDIT POST STARTED', ['id' => $system_admin_id]);
            Log::info('Request Data:', $request->all());

            DB::beginTransaction();

            try {

                // =========================
                // 1. VALIDATION
                // =========================
                $request->validate([
                    'name'  => 'required',
                    'email' => 'required|email',
                ]);
                Log::info('Validation passed');

                // =========================
                // 2. GET LAT/LONG
                // =========================
                $latitude = null;
                $longitude = null;

                if (!empty($request->address)) {
                    $apiKey = 'YOUR_GOOGLE_API_KEY';

                    $geo = @file_get_contents(
                        'https://maps.googleapis.com/maps/api/geocode/json?address=' .
                            urlencode($request->address) . '&key=' . $apiKey
                    );

                    if ($geo) {
                        $geo = json_decode($geo, true);

                        if (isset($geo['status']) && $geo['status'] == 'OK') {
                            $latitude  = $geo['results'][0]['geometry']['location']['lat'];
                            $longitude = $geo['results'][0]['geometry']['location']['lng'];
                        }
                    }
                }

                // =========================
                // 3. UPDATE ADMIN
                // =========================
                $system_admin = Admin::findOrFail($system_admin_id);

                $old_image = $system_admin->image;

                $system_admin->name      = $request->name;
                $system_admin->email     = $request->email;
                $system_admin->company   = $request->company;
                $system_admin->address   = $request->address;
                $system_admin->post_code = $request->post_code;

                // Only update username if needed
                if (!empty($request->user_name)) {
                    $system_admin->user_name = $request->user_name;
                }

                // Update lat/long if available
                if ($latitude && $longitude) {
                    $system_admin->latitude  = $latitude;
                    $system_admin->longitude = $longitude;
                }

                // =========================
                // 4. IMAGE UPDATE
                // =========================
                if ($request->hasFile('image')) {

                    $file = $request->file('image');
                    $ext  = strtolower($file->getClientOriginalExtension());

                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {

                        $name = time() . '.' . $ext;
                        $destination = base_path() . adminbasePath;

                        $file->move($destination, $name);

                        // delete old image
                        if (!empty($old_image) && file_exists($destination . '/' . $old_image)) {
                            unlink($destination . '/' . $old_image);
                        }

                        $system_admin->image = $name;
                    }
                }

                $system_admin->save();

                // =========================
                // 5. UPDATE COMPANY SETTINGS
                // =========================
                CompanyHomeSetting::updateOrCreate(
                    ['company_id' => $system_admin->id],
                    [
                        'address'                         => $request->home_address,
                        'is_home_area'                    => $request->is_home_area ?? 0,
                        'weekly_allowance_service_users'  => $request->weekly_allowance_service_users,
                        'monthly_allowance_service_users' => $request->monthly_allowance_service_users,
                        'clock_in_range'                  => $request->clock_in_range,
                        'staff_term'                      => $request->staff_term ?? 'Staff',
                        'service_user_term'               => $request->service_user_term ?? 'Service User',
                    ]
                );
                Log::info('Company settings updated');
                
                // Clear terminology cache for this company
                \Illuminate\Support\Facades\Cache::forget('terminology_' . $system_admin->id . '_staff_term');
                \Illuminate\Support\Facades\Cache::forget('terminology_' . $system_admin->id . '_service_user_term');

                // =========================
                // 6. UPDATE COMPANY AREAS
                // =========================
                CompanyHomeArea::where('company_id', $system_admin->id)->forceDelete();
                Log::info('Old home areas force deleted');

                if ($request->is_home_area == 1 && !empty($request->home_area_names)) {
                    Log::info('Saving new home areas', ['count' => count($request->home_area_names)]);

                    foreach ($request->home_area_names as $area) {

                        if (!empty(trim($area))) {

                            CompanyHomeArea::create([
                                'company_id' => $system_admin->id,
                                'area_name'  => $area,
                            ]);
                            Log::info('Area saved in Edit:', ['area' => $area]);
                        }
                    }
                }

                DB::commit();

                return redirect('admin/system-admins')
                    ->with('success', 'System Admin Updated successfully.');
            } catch (\Exception $e) {

                DB::rollback();
                Log::error('EDIT FAILED', [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine()
                ]);

                return redirect()->back()
                    ->with('error', $e->getMessage());
            }
        }

        // =========================
        // GET (EDIT FORM LOAD)
        // =========================
        $system_admins = DB::table('admin')
            ->where('id', $system_admin_id)
            ->where('is_deleted', $del_status)
            ->first();

        // 🔥 VERY IMPORTANT: Load settings & areas
        $system_admin_home = CompanyHomeSetting::where('company_id', $system_admin_id)->first();
        $home_areas = CompanyHomeArea::where('company_id', $system_admin_id)->get();

        $page = 'system-admins';

        return view('backEnd/superAdmin/admin/admin_form', compact(
            'system_admins',
            'page',
            'del_status',
            'system_admin_home',
            'home_areas'
        ));
    }

    public function delete($system_admin_id)
    {
        if (!empty($system_admin_id)) {
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

            if ($updated) {
                return redirect('admin/system-admins')->with('success', 'System Admin deleted Successfully.');
            } else {
                return redirect('admin/system-admins')->with('error', 'System Admin could not be deleted.');
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
        echo $response;
        die;
    }

    public function show_set_password_form_system_admin(Request $request, $system_admin_id = null, $security_code = null)
    {

        $decoded_system_admin_id = convert_uudecode(base64_decode($system_admin_id));
        $decoded_security_code   = convert_uudecode(base64_decode($security_code));
        //$admin_user_name         = $user_name;
        $admin = Admin::where('id', $decoded_system_admin_id)
            ->where('security_code', $decoded_security_code)
            ->first();

        if (!empty($admin)) {
            $user_name = $admin->user_name;
            return view('backEnd.admin_set_password', compact('system_admin_id', 'security_code', 'user_name'));
        } else {
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
        if (empty($data['password'])) {
            return redirect()->back()->with('error', 'Please Enter Password');
        } else if ($data['password'] != $data['confirm_password']) {
            return redirect()->back()->with('error', 'Password & confirm password does not matched.');
        }

        $system_admin_id = convert_uudecode(base64_decode($data['system_admin_id']));
        $security_code   = convert_uudecode(base64_decode($data['security_code']));
        $user_name       = $data['user_name'];

        $admin = Admin::where('id', $system_admin_id)
            ->where('security_code', $security_code)
            ->where('user_name', $user_name)
            ->first();
        // echo "<pre>";
        // print_r($admin);
        // die;
        $admin->security_code = '';

        $admin->password =   md5($data['password']);

        if ($admin->save()) {
            //logging out any previous loggedin admin
            Session::forget('scitsAdminSession');
            return redirect('admin/login')->with('success', 'You have set your password successfully.');
        } else {
            return redirect('admin/login')->with('error', 'Some error occured. Please try again later');
        }
    }

    public function check_username_exist(Request $request)
    {

        $count = Admin::where('user_name', $request->user_name)->count();

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
    public function check_company_exist(Request $request)
    {
        $count = Admin::where('company', $request->company)->where('is_deleted', 0)->count();

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

    public function package_detail($system_admin_id, Request $request)
    {

        $package_detail = CompanyPaymentInformation::select('cc.package_type', 'cc.home_range', 'paid_amount', 'expiry_date', 'cc.id')
            ->where('admin_id', $system_admin_id)
            ->join('company_charges as cc', 'cc.id', 'company_payment_information.company_charges_id')
            ->orderBy('company_payment_information.id', 'desc')
            ->first();
        // echo "<pre>"; print_r($package_detail); die;

        // if(!empty($package_detail)){

        //     $home_range = explode('-', $package_detail->home_range);
        //     $last_range = $home_range[1];
        //     if($package_detail->paid_amount != '0'){

        //         $amount = explode('%2e', $package_detail->paid_amount);
        //         $paid_amount = $amount[0].'.'.$amount[1];
        //     } else {
        //         $paid_amount = $package_detail->paid_amount;
        //     }
        // }else{
        //     return redirect()->back()->with('error','No package selected');
        //     $last_range = '';
        //     $paid_amount = '';
        // }

        if (empty($package_detail)) {
            return redirect()->back()->with('error', 'No package selected');
        }

        $home_range   = explode('-', $package_detail->home_range ?? '');
        $last_range   = $home_range[1] ?? ''; // safely get 2nd part or fallback to ''

        if (!empty($package_detail->paid_amount) && $package_detail->paid_amount !== '0') {
            // decode paid_amount safely
            $amount_parts = explode('%2e', $package_detail->paid_amount);
            $paid_amount  = $amount_parts[0] . '.' . ($amount_parts[1] ?? '0');
        } else {
            $paid_amount = $package_detail->paid_amount ?? '0';
        }


        if ($request->isMethod('post')) {

            if (!empty($request->extra_day)) {
                // echo "<pre>"; print_r($request->input()); //die;
                $extra_day              = $request->extra_day;
                $system_admin_id        = $request->system_admin_id;
                $company_charges_id     = $request->company_charges_id;

                $company_payment_dtl = CompanyPayment::select('expiry_date')
                    ->where('admin_id', $system_admin_id)
                    ->where('company_charges_id', $company_charges_id)
                    ->first();
                if (!empty($company_payment_dtl)) {
                    $new_expiry_date = date('Y-m-d H:i:s', strtotime('+' . $extra_day . 'days', strtotime($company_payment_dtl->expiry_date)));

                    if (!empty($new_expiry_date)) {
                        // $update = $company_payment_dtl->update(['expiry_date'=> $new_expiry_date]);

                        $update = CompanyPayment::select('expiry_date')
                            ->where('admin_id', $system_admin_id)
                            ->where('company_charges_id', $company_charges_id)
                            ->update(['expiry_date' => $new_expiry_date]);
                        if ($update) {
                            $upd = CompanyPaymentInformation::where('admin_id', $system_admin_id)
                                ->where('company_charges_id', $company_charges_id)
                                ->orderBy('id', 'desc')
                                ->update(['expiry_date' => $new_expiry_date]);
                            if ($upd) {
                                return redirect('admin/system-admins')->with('success', 'Current package expended successfully');
                            } else {

                                return redirect()->back()->with('error', COMMON_ERRRO);
                            }
                        }
                    }
                    // echo "<pre>"; print_r($new_expiry_date); die;
                }
                // echo "<pre>"; print_r($company_payment_dtl); die;


            }
        }

        $page = 'system-admins';

        return view('backEnd/superAdmin/admin/admin_package_detail', compact('page', 'package_detail', 'last_range', 'paid_amount', 'system_admin_id'));
    }
}
