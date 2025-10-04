<?php
namespace App\Http\Controllers\backEnd;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\ManagementSection, App\User, App\AccessRight, App\Home;  
use DB; 

class AccessRightController extends Controller
{
    public function index(Request $request,$user_id = null)
    {	

        $admin = Session::get('scitsAdminSession');
        // echo "</pre>";
        // print_r($admin); die;
        $home_id = $admin->home_id;
        // print_r($home_id); die;
        if(empty($home_id)) {

            return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
        }

        $user = User::select('id','access_rights')->where('id',$user_id)->where('home_id', $home_id)->first();

        
        $available_rights = array();
        if(!empty($user)) {
            //get all the access rights of user available checked
            $available_rights = explode(',', $user->access_rights);
        } else {

            return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
        }

        // print_r($available_rights);
        // die;

        $dashboard_rights = AccessRight::dashboardAccessRightList();
        
        $access_rights    = AccessRight::accessRightList();
        // echo "<pre>";
        // print_r($access_rights);
        // die();
        //$access_rights='';
        // $page = 'agents';
        $page = 'users';

       	return view('backEnd/access_rights', compact('page','user_id','dashboard_rights','access_rights','available_rights'));
    }

    public function update(Request $request){

        if($request->isMethod('post')) {
            $data = $request->input();
            //echo '<pre>'; print_r($data); echo '</pre>';   die;
            $access_str     = AccessRight::getAccessRightString($data);
            $admin_home_id  = Session::get('scitsAdminSession')->home_id;
            
            $user = User::select('id')
                        ->where('id',$data['user_id'])
                        ->where('home_id', $admin_home_id)
                        ->first();

            if(!empty($user)) {

                $user->access_rights = $access_str;
                //echo '<pre>'; print_r($user->access_rights); echo '</pre>';   die;

                if($user->save()) {

                    return redirect('admin/users')->with("success","User's Access Rights assigned successfully");
                } else{
                    return redirect()->back()->with("error","Some Error occured! Please try again later.");
                }
            } else {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        }
    }

    //Only use for Agent
    public function agent_index(Request $request,$user_id = null)
    {       

        $admin                  = Session::get('scitsAdminSession');
        $access_type            = Session::get('scitsAdminSession')->access_type;
        $selected_home_id       = Session::get('scitsAdminSession')->home_id; 
        $selected_company_id    = Home::where('id',$selected_home_id)->value('admin_id');
        if($access_type == 'S'){

            $cmpny_id = $selected_company_id;
        }else{
            $cmpny_id = $admin->id;
        }
        
        if(empty($cmpny_id)) {
            
            return redirect('admin/')->with('error',UNAUTHORIZE_ERR);
        }
        
        $user = User::select('id','access_rights')->where('id',$user_id)->where('admn_id', $cmpny_id)->first();
        
        $available_rights = array();
        if(!empty($user)) {
            //get all the access rights of user available checked
            $available_rights = explode(',', $user->access_rights);
        } else {
            return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
        }
        
        $dashboard_rights = AccessRight::dashboardAccessRightList();
        $access_rights    = AccessRight::accessRightList();
        
        $page = 'agents';
        return view('backEnd/agent_access_rights', compact('page','user_id','dashboard_rights','access_rights','available_rights'));
    }
    //Only use for Agent
    public function agent_update(Request $request){

        if($request->isMethod('post')) {
            $data = $request->input();
            //echo '<pre>'; print_r($data); echo '</pre>';   die;
            $access_str             = AccessRight::getAccessRightString($data);
            $admin                  = Session::get('scitsAdminSession');
            $access_type            = Session::get('scitsAdminSession')->access_type;
            $selected_home_id       = Session::get('scitsAdminSession')->home_id; 
            $selected_company_id    = Home::where('id',$selected_home_id)->value('admin_id');
            if($access_type == 'S'){

                $cmpny_id = $selected_company_id;
            }else{
                $cmpny_id = $admin->id;
            }
            
            $user = User::select('id')
                        ->where('id',$data['user_id'])
                        ->where('admn_id', $cmpny_id)
                        ->first();

            if(!empty($user)) {

                $user->access_rights = $access_str;
                //echo '<pre>'; print_r($user->access_rights); echo '</pre>';   die;

                if($user->save()) {

                    return redirect('admin/agents')->with("success","Agent's Access Rights assigned successfully");
                } else{
                    return redirect()->back()->with("error","Some Error occured! Please try again later.");
                }
            } else {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        }
    }
}

/* if(isset($data['modules'])){

                foreach($data['modules'] as $module_code){
                 
                    $access_rights = AccessRight::select('id')->where('module_code',$module_code)->get()->toArray();
                    $access_rights = array_map(function($right){ return $right['id']; },$access_rights);
                    $access_right_str = implode(',',$access_rights);
                }

                $user           = User::find($data['user_id']);
                $user->access_rights = $access_right_str;
                if($user->save()){
                    return redirect('/users')->with("success","User's Access Rights assigned successfully");
                } else{
                    return redirect()->back()->with("error","Some Error occured! Please try again later.");
                }
            }*/
