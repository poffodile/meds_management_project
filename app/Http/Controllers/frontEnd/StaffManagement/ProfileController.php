<?php
namespace App\Http\Controllers\frontEnd\StaffManagement;
use App\Http\Controllers\frontEnd\StaffManagementController;
use Illuminate\Http\Request;
use App\User, App\ManagementSection, App\AccessRight, App\UserQualification;
use DB, Auth;

class ProfileController extends StaffManagementController
{
    public function index(Request $request, $staff_id){
        
        $staff_member = DB::table('user')->where('id',$staff_id)->first();

        if(!empty($staff_member)){

            // $home_id = Auth::user()->home_id;
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            if($staff_member->home_id != $home_id){
                return redirect('/')->with('error',UNAUTHORIZE_ERR); 
            }


            //get coordnate for map
            $current_location = $staff_member->current_location;
    
            //removing new line
            $pattern = '/[^a-zA-Z0-9]/u';
            $current_location = preg_replace($pattern, ' ', (string) $current_location);
            $coordinates = $this->get_long_lat($current_location);

            $latitude = (isset($coordinates['results']['0']['geometry']['location']['lat'])) ? $coordinates['results']['0']['geometry']['location']['lat'] : ''; 
            $longitude = (isset($coordinates['results']['0']['geometry']['location']['lng'])) ? $coordinates['results']['0']['geometry']['location']['lng'] : ''; 
            //get coordnate for map end

            //2nd tab access rights
                //getting management headings and module headings
            $managements = ManagementSection::with('moduleList')->has('moduleList')->get()->toArray();
            //echo "<pre>"; print_r($managements); die;
            foreach($managements as $key1 => $management){

                foreach($management['module_list'] as $key2 => $module){
                   
                    $right_types = AccessRight::where('module_code',$module['module_code'])
                                        ->where('main_route_id',0)
                                        ->orderBy('submodule_name','asc')
                                        ->get()
                                        ->toArray();
                    $managements[$key1]['module_list'][$key2]['sub_modules'] = $right_types;
                }
            }

            $dashboard = AccessRight::where('module_code','DASH')
                            ->orderBy('module_name','asc')
                            ->get()
                            ->toArray();
                            
            $user_rights = User::where('id',$staff_id)->where('home_id', $home_id)->where('is_deleted','0')->value('access_rights');
            $user_rights = explode(',', $user_rights);

            $staff_qualification = DB::table('user_qualification')->select('id','name','image')->where('user_id',$staff_id)->where('is_deleted',0)->get();
            //2nd tab access rights end

            return view('frontEnd.staffManagement.profile',compact('staff_member','staff_id','latitude','longitude','managements','dashboard','user_rights','staff_qualification'));  
        }
    }

    //4th tab full profile
    public function edit_staff_detail_info(Request $request) {

        if($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            
            $staff_id = $data['staff_id'];
            unset($data['staff_id']);
            unset($data['_token']);

            foreach ($data as $key => $value) {
                $update_info = User::where('id', $staff_id)
                                ->where('home_id', $home_id)
                                ->where('is_deleted','0')
                                ->update([
                                    $key => nl2br(trim($value))
                                ]);
            }
            if($update_info){
                return redirect()->back()->with('success','Staff Member Info updated successfully.');
            } else{
                return redirect()->back()->with('error','Staff Member Info could not be updated,');
            }
        }

    }
    //4th tab full profile end

    //3rd tab contacts
    public function edit_staff_location_info(Request $request) {
       // echo "1";
        if($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $update_location = User::where('id', $data['staff_id'])
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->update(['current_location' => nl2br(trim($data['current_location']))
                            ]);

            if($update_location){
                return redirect()->back()->with('success','Staff Member location Info updated successfully.');
            } else{
                return redirect()->back()->with('error','Staff Member location Info could not be updated,');
            }
        }
    }

      //function used in google map
    public function get_long_lat($address)
    {
            $this->layout="";
            $add=str_replace(' ','+',$address);
            
            $api_key = env('GOOGLE_MAP_API_KEY');
            $request = "https://maps.googleapis.com/maps/api/geocode/json?address=$add&key=".$api_key;
            $ch = curl_init($request);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($ch);
            $arr = json_decode($response, true);
            return($arr); 
    }

    public function edit_staff_contact_info(Request $request) {

        if($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $update_contact = User::where('id', $data['staff_id'])
                            ->where('home_id', $home_id)
                            ->where('is_deleted' ,'0')
                            ->update([
                                'phone_no' => $data['phone_no'],
                                'email'    => $data['email']
                            ]);
            if($update_contact){
                return redirect()->back()->with('success','Staff member Contact Info updated successfully.');
            } else{
                return redirect()->back()->with('error','Staff member Contact Info could not be updated.');
            }
        }
    }
    //3rd tab contacts end

    //5th tab settings
    public function edit_staff_setting(Request $request) {
        //echo "1";
        if($request->isMethod('post')) {
            
            $data = $request->input();

            $staff_id     = $data['staff_id'];
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            // $home_id      = Auth::user()->home_id;

            $staff_prfl = User::find($staff_id);
            if(!empty($staff_prfl)) {
                $staff_member = DB::table('user')->where('id',$staff_id)->where('is_deleted','0')
                                                ->where('access_level','3')
                                                ->value('home_id');
                if($staff_member != $home_id){
                    return redirect('/')->with('error',UNAUTHORIZE_ERR); 
                }

                $staff_old_image    = $staff_prfl->image;
                $staff_prfl->name   = $data['name'];
                $staff_prfl->job_title = $data['job_title'];
                $staff_prfl->email  = $data['email'];
                $staff_prfl->description = $data['description'];
                $staff_prfl->payroll     = $data['payroll'];
                $staff_prfl->holiday_entitlement = $data['holiday_entitlement'];
                $staff_prfl->phone_no            = $data['phone_no'];   

                $staff_prfl->current_location    = nl2br(trim($data['current_location']));
                $staff_prfl->personal_info       = nl2br(trim($data['personal_info']));
                $staff_prfl->banking_info        = nl2br(trim($data['banking_info']));
                //$staff_prfl->qualification_info  = nl2br(trim($data['qualification_info']));

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
                            if(!empty($staff_old_image)){
                                if(file_exists($destination.'/'.$staff_old_image))
                                {
                                    unlink($destination.'/'.$staff_old_image);
                                }
                            }
                            $staff_prfl->image = $new_name;
                        }
                    }
                }
                
               if($staff_prfl->save()) {
                    User::saveQualification($data,$data['staff_id']);
                    return redirect()->back()->with('success','Staff member profile updated successfully.'); 
               } else {
                    return redirect()->back()->with('error','Staff member profile could not be Updated.'); 
               } 

            }

        }
    }

    public function delete_certificate($id=null)
    {
        UserQualification::where('id',$id)->update(['is_deleted'=>1]);
        return $response = array("status" => "ok");
    }   
    
}
