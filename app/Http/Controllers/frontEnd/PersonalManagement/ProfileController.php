<?php

namespace App\Http\Controllers\frontEnd\PersonalManagement;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\User, App\ManagementSection, App\AccessRight, App\Home, App\Admin;

use DB, Auth;



class ProfileController extends Controller

{

    public function index(Request $request, $manager_id)
    {



        $manager_profile = DB::table('user')->where('id', $manager_id)->first();



        if (!empty($manager_profile)) {



            $home_id = Auth::user()->home_id;

            if ($manager_profile->home_id != $home_id) {

                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            //get coordnate for map

            $current_location = $manager_profile->current_location;



            //removing new line

            $pattern = '/[^a-zA-Z0-9]/u';

            $current_location = preg_replace($pattern, ' ', (string) $current_location);

            $coordinates = $this->get_long_lat($current_location);



            $latitude = (isset($coordinates['results']['0']['geometry']['location']['lat'])) ? $coordinates['results']['0']['geometry']['location']['lat'] : '';

            $longitude = (isset($coordinates['results']['0']['geometry']['location']['lng'])) ? $coordinates['results']['0']['geometry']['location']['lng'] : '';

            //get coordnate for map end



            //2nd tab access rights

            //getting management headings and module headings

            //$managements = ManagementSection::with('moduleList')->has('moduleList')->get()->toArray();

            $managements = ManagementSection::with('moduleList');



            //dd($managements);

            //echo "<pre>"; print_r($managements); die;

            foreach ($managements as $key1 => $management) {



                foreach ($management['module_list'] as $key2 => $module) {



                    $right_types = AccessRight::where('module_code', $module['module_code'])

                        ->where('main_route_id', 0)

                        ->orderBy('submodule_name', 'asc')

                        ->get()

                        ->toArray();

                    $managements[$key1]['module_list'][$key2]['sub_modules'] = $right_types;

                    // echo "<pre>"; print_r($managements); 

                }
            }



            $dashboard = AccessRight::where('module_code', 'DASH')

                ->orderBy('module_name', 'asc')

                ->get()

                ->toArray();

            $user_rights = User::where('id', $manager_id)->where('home_id', Auth::user()->home_id)->where('is_deleted', '0')->value('access_rights');

            $user_rights = explode(',', $user_rights);

            $user_rights = array_unique($user_rights);

            // echo "<pre>"; print_r($user_rights); die;



            //2nd tab access rights end



            $my_qualification = DB::table('user_qualification')->select('id', 'name', 'image')->where('user_id', $manager_id)->where('is_deleted', 0)->get();

            //echo '<pre>'; print_r($my_qualification); die;

            $weekly_allowance = Home::where('id', Auth::user()->home_id)->value('weekly_allowance');
            $admin_id = Home::where('id', Auth::user()->home_id)->value('admin_id');

            $qr_code_id =  Admin::where('id', $admin_id)->where('is_deleted', 0)->value('qr_code_id');

            return view('frontEnd.personalManagement.profile', compact('manager_profile', 'manager_id', 'latitude', 'longitude', 'managements', 'dashboard', 'user_rights', 'my_qualification', 'weekly_allowance', 'admin_id', 'qr_code_id'));
        }
    }



    //4th tab full profile

    // public function edit_staff_detail_info(Request $request) {



    //     if($request->isMethod('post')) {

    //         $data = $request->all();



    //         $staff_id = $data['staff_id'];

    //         unset($data['staff_id']);

    //         unset($data['_token']);



    //         foreach ($data as $key => $value) {

    //             $update_info = User::where('id', $staff_id)

    //                             ->where('home_id', Auth::user()->home_id)

    //                             ->where('is_deleted','0')

    //                             ->update([

    //                                 $key => nl2br(trim($value))

    //                             ]);

    //         }

    //         if($update_info){

    //             return redirect()->back()->with('success','Staff Member Info updated successfully.');

    //         } else{

    //             return redirect()->back()->with('error','Staff Member Info could not be updated,');

    //         }

    //     }



    // }

    //4th tab full profile end





    //3rd tab contacts

    // public function edit_staff_location_info(Request $request) {

    //    // echo "1";

    //     if($request->isMethod('post')) {

    //         $data = $request->all();



    //         $update_location = User::where('id', $data['staff_id'])

    //                         ->where('home_id', Auth::user()->home_id)

    //                         ->where('is_deleted', '0')

    //                         ->update(['current_location' => nl2br(trim($data['current_location']))

    //                         ]);



    //         if($update_location){

    //             return redirect()->back()->with('success','Staff Member location Info updated successfully.');

    //         } else{

    //             return redirect()->back()->with('error','Staff Member location Info could not be updated,');

    //         }

    //     }

    // }



    //function used in google map

    public function get_long_lat($address)

    {

        $this->layout = "";

        $add = str_replace(' ', '+', $address);



        $api_key = env('GOOGLE_MAP_API_KEY');

        $request = "https://maps.googleapis.com/maps/api/geocode/json?address=$add&key=" . $api_key;

        $ch = curl_init($request);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);

        $arr = json_decode($response, true);

        return ($arr);
    }



    // public function edit_staff_contact_info(Request $request) {



    //     if($request->isMethod('post')) {

    //         $data = $request->all();



    //         $update_contact = User::where('id', $data['staff_id'])

    //                         ->where('home_id', Auth::user()->home_id)

    //                         ->where('is_deleted' ,'0')

    //                         ->update([

    //                             'phone_no' => $data['phone_no'],

    //                             'email'    => $data['email']

    //                         ]);

    //         if($update_contact){

    //             return redirect()->back()->with('success','Staff member Contact Info updated successfully.');

    //         } else{

    //             return redirect()->back()->with('error','Staff member Contact Info could not be updated.');

    //         }

    //     }

    // }

    //3rd tab contacts end



    //5th tab settings

    public function edit_profile_setting(Request $request)
    {



        if ($request->isMethod('post')) {



            $data = $request->input();



            $manager_id = $data['manager_id'];

            $home_id    = Auth::user()->home_id;



            $my_prfl = User::find($manager_id);

            if (!empty($my_prfl)) {

                $staff_member = DB::table('user')->where('id', $manager_id)->where('is_deleted', '0')

                    //->where('access_level','1')

                    ->value('home_id');

                if ($staff_member != $home_id) {

                    return redirect('/')->with('error', UNAUTHORIZE_ERR);
                }



                $my_old_image    = $my_prfl->image;

                $my_prfl->name   = $data['name'];

                // $my_prfl->job_title = $data['job_title'];

                $my_prfl->email  = $data['email'];

                $my_prfl->description = $data['description'];

                // $my_prfl->payroll     = $data['payroll'];

                //$my_prfl->holiday_entitlement = $data['holiday_entitlement'];

                $my_prfl->phone_no            = $data['phone_no'];



                $my_prfl->current_location    = nl2br(trim($data['current_location']));

                $my_prfl->personal_info       = nl2br(trim($data['personal_info']));

                $my_prfl->banking_info        = nl2br(trim($data['banking_info']));

                // $my_prfl->qualification_info  = nl2br(trim($data['qualification_info']));



                if (!empty($_FILES['image']['name'])) {

                    $tmp_image  =   $_FILES['image']['tmp_name'];

                    $image_info =   pathinfo($_FILES['image']['name']);

                    $ext        =   strtolower($image_info['extension']);

                    $new_name   =   time() . '.' . $ext;



                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {

                        $destination =   base_path() . userProfileImageBasePath;

                        if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {

                            if (!empty($my_old_image)) {

                                if (file_exists($destination . '/' . $my_old_image)) {

                                    unlink($destination . '/' . $my_old_image);
                                }
                            }

                            $my_prfl->image = $new_name;
                        }
                    }
                }



                if ($my_prfl->save()) {

                    // echo "<pre>"; print_r($my_prfl->id); die;

                    User::sendEmailToManager($my_prfl->id); //send alert email to manager when staff user update their personal information like bank detail etc.

                    return redirect()->back()->with('success', 'My profile updated successfully.');
                } else {

                    return redirect()->back()->with('error', 'My profile could not be Updated.');
                }
            }
        }
    }
}
