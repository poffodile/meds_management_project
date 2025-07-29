<?php
namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use DB, Auth;
use App\Policies, App\User, App\UserAcceptedPolicy, App\Admin;
use Illuminate\Support\Facades\Mail;

class PoliciesController extends ServiceUserManagementController
{
    public function index()
    {
        $user_id = Auth::user()->id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        // $home_id = Auth::user()->home_id;
        //,'uap.id as accepted_id'
        $policy_query = Policies::select('polices.id','polices.file','user_accepted_policy.id as user_accepted_id')
                                ->where('polices.home_id',$home_id)
                                ->where('polices.is_deleted',0)
                                ->leftJoin('user_accepted_policy', function($join)
                                    {
                                        $join->on('user_accepted_policy.policy_id','=','polices.id');
                                        $join->on('user_accepted_policy.user_id','=',DB::raw(Auth::user()->id));
                                        $join->on('user_accepted_policy.created_at','>','polices.updated_at');
                                    })
                                ->orderBy('polices.id','desc'); 
        $search = '';
        $records_limit = '10';
     
        if(isset($_GET['search'])) {  

            if( (!empty($_GET['search'])) ) { 
                $policy_query = $policy_query->where('file','like','%'.$_GET['search'].'%');         
            }  

        } else if(isset($_GET['not-accepted'])) {

            $policy_query = $policy_query->where('user_accepted_policy.id',null);
     
        } else if(isset($_GET['accepted'])) {
     
            $policy_query = $policy_query->where('user_accepted_policy.id','!=',null);
        }

        $policies = $policy_query->paginate($records_limit);
        //$policies = $policies->toArray();

        //echo "<pre>"; print_r($policies); die;
        foreach ($policies as $key => $value)  {
            
            $check = '';
            $but   = '';
            if(isset($_GET['not-accepted'])) {
                $but   = '<li> <a href="#" class="accept_policy" file_id="'. $value->id.'" > <span class="color-green"> <i class="fa fa-check"></i> </span> Accept </a> </li>';
            }

            /*if($value->accept_policy == ""){ //echo '1'; 
                $check = '<i class="fa fa-times cross" aria-hidden="true"></i></span>';
                //$but   = '<button class="btn btn-success button-green accept_policy" file_id="'. $value->id.'" >Accept</button>';
                $but   = '<li> <a href="#" class="accept_policy" file_id="'. $value->id.'" > <span class="color-green"> <i class="fa fa-check"></i> </span> Accept </a> </li>';
            } else{ //echo '2'; 
                $check = '<i class="fa fa-check tick" aria-hidden="true"></i></span>';
                $but   = '';
            }*/
            
            $file_name = $value->file;
            $path = pathinfo($file_name);
            $ext  = $path['extension'];
           
            if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                $icn_class = 'fa fa-file-image-o';
                $file_url  =  PoliciesFilePath.'/'.$value->file;

                $file      = '<a class="wrinkled" href="'.$file_url.'" data-fancybox data-caption="'.$value->file.'"> 
                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
                            </a>';

            } else if($ext == 'pdf') {
                $icn_class = 'fa fa-file-pdf-o';
                $file_url  = 'http://docs.google.com/gview?url='.PoliciesFilePath.'/'.$value->file.'&embedded=true';
                
                $file      = '<a class="wrinkled" data-fancybox data-type="iframe" data-src="'.$file_url.'" href="javascript:;">
                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
                              </a>';
                                                
            } else if($ext == 'doc' || $ext == 'docx'  || $ext == 'wps' ) {
                $icn_class = 'fa fa-file-word-o';
                $file_url  = 'http://docs.google.com/gview?url='.PoliciesFilePath.'/'.$value->file.'&embedded=true';
                $file      = '<a class="wrinkled" data-fancybox data-type="iframe" data-src="'.$file_url.'" href="javascript:;">
                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
                              </a>';
                
            } else{
                $icn_class = 'fa fa-file-image-o';
                $file_url  =  PoliciesFilePath.'/'.$value->file;
                $file      = '<a class="wrinkled" href="'.$file_url.'" data-fancybox data-caption="'.$value->file.'"> 
                                    <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
                              </a>';
            }

            echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cog-panel delete-file">
                    <div class="col-md-10 col-sm-10 col-xs-12">
                        <div class="row">
                            <!-- <div class="col-md-1 ck">
                                <span class="color-green">'.$check.'</span>
                            </div> -->
                            <div class="col-md-11 value-tx">
                                '.$file.'
                           </div>
                        </div>    
                    </div>    
                    <div class="col-sm-2 col-md-2 col-xs-12 pull-right">
                        <span class="clr-blue settings filmngr-setting">
                            <i class="fa fa-cog"></i>
                            <div class="pop-notifbox">
                                <ul class="pop-notification" type="none">
                                    '.$but.'
                                    <li> <a href="#" class="edit_policy" policy_id="'.$value->id.'" > <span class="color-green"> <i class="fa fa-pencil"></i> </span> Update </a> </li>
                                    <li> <a href="#" class="del_policy" file_id="'.$value->id.'" > <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>
                                </ul>
                            </div>
                        </span>
                    </div>
                </div> ';

                //<a class="word" href="http://docs.google.com/gview?url=http://domain.com/path/docFile.doc&embedded=true">open a word document in fancybox</a>
            
            /*<a class="wrinkled" href="'.$file_url.'" data-fancybox data-caption="'.$value->file.'"> 
                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
            </a>
            <a data-fancybox data-type="iframe" data-src="'.$file_url.'" href="javascript:;"> Webpage </a>*/

            /*echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cog-panel delete-file">
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <div class="row">
                            <div class="col-md-1 ck">
                                <span >'.$check.'</span>
                            </div>
                            <div class="col-md-11 value-tx">
                                <a class="wrinkled" href="'.PoliciesFilePath.'/'.$value->file.'" target="_blank"> 
                                    <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 
                                </a>
                           </div>
                        </div>    
                    </div>    
                    <div class="col-sm-4 col-md-4 col-xs-4 pull-right">
                        <span>'.$but.'
                              <button class="btn btn-danger delete del_policy" file_id="'.$value->id.'">Delete</button>  
                        </span>
                    </div>
                </div> ';*/
        }
        // echo '<div class="col-sm-6">';
        // echo $logged_files->links();
        //echo '</div>';
        // if(!empty($logged_files)){
        if($policies != ''){

            echo '<div class="file_mngr_paginate m-l-15">';
            echo $policies->links();
            echo '</div>'; 
        }
    }

    public function add_multiple(Request $request)   
    {   
        if($request->isMethod('post'))  
        {
            $data = $request->all();
            /* echo '<pre>';
            print_r($data);
            print_r($_FILES);
            print_r($data['files']);
            die;*/
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $user_id = Auth::user()->id;

            if(!empty($_FILES['files']['name'])) {

                foreach($_FILES['files']['name'] as $key => $value){

                    $tmp_file   =   $_FILES['files']['tmp_name'][$key];
                    $image_info =   pathinfo($_FILES['files']['name'][$key]);
                    
                    //$file_name  =   substr($image_info['filename'],0,100);
                    $file_name  =   $image_info['filename'];
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   $file_name.'.'.$ext;

                    $allowed_ext = array('jpg','jpeg','png','pdf','doc','docx','wps');
                    if(in_array($ext,$allowed_ext)){

                        $file_dest = base_path().PoliciesFileBasePath; 
                    
                        if(!is_dir($file_dest)) { //check if file directory not exits then create it
                            mkdir($file_dest);
                        } else{ //if directory exits check if any file with same name exists
                            
                            $i = 1;
                            while(file_exists($file_dest.'/'.$new_name)){ 
                                $i++;
                                $new_name = $file_name.$i.'.'.$ext;                                
                            }
                        }
                        
                        if(move_uploaded_file($tmp_file, $file_dest.'/'.$new_name)) {

                            $file                  = new Policies;
                            $file->user_id         = $user_id;
                            $file->file            = $new_name;
                            $file->home_id         = $home_id;
                            
                            if($file->save()){
                                $result[$key]['response']   = true;
                                //$result[$key]['save_id']    = base64_encode(convert_uuencode($file->id));
                                $result[$key]['save_id']    = $file->id;
                                $result[$key]['file_index'] = $key;
                                $result[$key]['file_name']  = $new_name;                                
                            } else{
                                $result[$key]['response'] = false;
                                $result[$key]['save_id'] = '';
                                $result[$key]['file_index'] = $key;
                                $result[$key]['file_name']  = $new_name;                                
                            }
                        }
                    } 

                    if(!isset($result[$key]['save_id'])){
                        $result[$key]['response'] = false;
                        $result[$key]['save_id'] = '';
                        $result[$key]['file_index'] = $key;
                    } 

                }
            }
            //echo '<pre>'; print_r($result);
            $result = array_values($result);

            return json_encode($result); 
        }
    }

    public function add_single(Request $request){
        
        $data = $request->input();
        $user_id = $data['user_id'];
        $save_id = ''; // last insert id 
        $new_name = '';
        
        $su_home_id = User::where('id',$user_id)->value('home_id');
        if(Auth::user()->home_id == $su_home_id){
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            if(!empty($_FILES['file']['name']))
            {  
                $tmp_image  =   $_FILES['file']['tmp_name'];
                $image_info =   pathinfo($_FILES['file']['name']);
                $file_name  =   $image_info['filename'];
                $ext        =   strtolower($image_info['extension']);
                        
                $new_name   =   $file_name.'.'.$ext; 
                $allowed_ext = array('jpg','jpeg','png','pdf','doc','docx','wps');
                if(in_array($ext,$allowed_ext))
                {   
                    $file_dest = base_path().PoliciesFileBasePath; 
                
                    if(!is_dir($file_dest)) {
                        mkdir($file_dest);
                    } else{ //if directory exits check if any file with same name exists
                        
                        $i = 1;
                        while(file_exists($file_dest.'/'.$new_name)){ 
                            $i++;
                            $new_name = $file_name.$i.'.'.$ext;                                
                        }
                    }
                   
                    if(move_uploaded_file($tmp_image, $file_dest.'/'.$new_name))
                    {
                        $file                  = new Policies;
                        $file->user_id         = $user_id;
                        $file->file            = $new_name;
                        $file->home_id         = $home_id;
                        
                        if($file->save()) {
                            //$save_id = base64_encode(convert_uuencode($file->id));
                            $save_id = $file->id;
                        }        
                    }
                }
            }
        }
        
        if(!empty($save_id)) {
            $result['response'] = true;
            $result['save_id'] = $save_id;
            $result['file_name'] = $new_name;
        } else {
            $result['response'] = false;
            $result['save_id']  = $save_id;
            $result['file_name']= $new_name;
        }
        
        $result = json_encode($result);
        return $result;
    }

    public function delete($logged_file_id = null) 
    {   
        $deleted = '';
        if(!empty($logged_file_id)) {

            //$logged_file_id = convert_uudecode(base64_decode($logged_file_id));

            $file_info = Policies::select('user_id','file')->where('id', $logged_file_id)->first();
            if(!empty($file_info)){
                $user_id = $file_info->user_id;

                $su_home_id = User::where('id',$user_id)->value('home_id');
                if(Auth::user()->home_id != $su_home_id){
                    return false; 
                }

                $file_name = $file_info->file;
                $file_dest = base_path().PoliciesFileBasePath; 
            
                if(file_exists($file_dest.'/'.$file_name)){
                    unlink($file_dest.'/'.$file_name);
                }
            
                $deleted = Policies::where('id', $logged_file_id)->delete();
            }

            if($deleted){
                return 'true';
            } else {
                return 'false';
            }            
        }
    }

    public function accept_policy($id=null) {

        $policy_file = Policies::where('id',$id)->value('file');
      //  echo $policy_file; die;

        $accept_policy              = new UserAcceptedPolicy;
        $accept_policy->user_id     = Auth::user()->id;
        $accept_policy->policy_id   = $id;
        $accept_policy->save();

        $accepted_policy = UserAcceptedPolicy::select('user_accepted_policy.created_at')
                                                ->where('user_accepted_policy.id', $accept_policy->id)
                                                ->first();
        // echo "<pre>"; print_r($accept_policy); die;

        if(!empty($accept_policy)) {
            $accepted_time = $accept_policy->created_at;
        }

        $user_name   = Auth::user()->user_name;
        $admin        = Admin::where('id', Auth::user()->home_id)->first();
        $admin_name   = $admin->name;
        $admin_email  = $admin->email;
        $company_name = PROJECT_NAME;
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL) === false) {
            Mail::send('emails.policy.staff_accept_policy',['user_name'=>$user_name, 'admin_name'=>$admin_name, 'accepted_time'=>$accepted_time, 'policy_file'=>$policy_file], function($message) use ($admin_email,$company_name)
            {
                $message->to($admin_email, $company_name)->subject('Staff Accepted Policy Mail');
            });
        }

        return $response = array('status'=>"OK");
        
    }

    public function delete_policy($policy_id=null){
        $deleted = Policies::where('id',$policy_id)->update(['is_deleted'=>1]);
        if($deleted){
            return 'true';
        }
    }

    public function update_policy(Request $req) {

        $data = $req->input();
        $policy_id = $data['policy_id'];
        //echo "<pre>"; print_r($policy_id); die;
        if(!empty($policy_id)) {
            $file = Policies::find($policy_id);
            if(!empty($file)) {
                $old_file = $file->file;
                //echo $old_file; die;
                if(!empty($_FILES['file']['name'])) {
                    //echo "YEs"; 
                    $tmp_image  =   $_FILES['file']['tmp_name'];
                    $image_info =   pathinfo($_FILES['file']['name']);
                    $file_name  =   $image_info['filename'];
                    // echo $file_name; die;
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   $file_name.'.'.$ext; 
                   
                    if($ext =="jpg" || $ext =="jpeg" || $ext =="png" || $ext =="pdf" || $ext =="doc" || $ext =="docx" || $ext =="wps") {
                        $destination = base_path().PoliciesFileBasePath; 
                        if(!is_dir($destination)) {
                            mkdir($destination);
                        } else{ //if directory exits check if any file with same name exists
                            
                            $i = 1;
                            while(file_exists($destination.'/'.$new_name)){ 
                                $i++;
                                $new_name = $file_name.$i.'.'.$ext;                                
                            }
                        }
                        if(move_uploaded_file($tmp_image, $destination.'/'.$new_name))
                        {
                            if(!empty($old_file)){
                                if(file_exists($destination.'/'.$old_file))
                                {
                                    unlink($destination.'/'.$old_file);
                                }
                            }
                            $file->file = $new_name;
                        }
                    } 
                    if($file->save()) {
                        
                        //send notifications to the users
                        $this->sendNotificationToStaff($new_name);

                        echo '1';
                    } 
                }else {
                    echo '2';
                }
            } else {
                echo "2"; 
            }
        } else {
            echo false;
        } 
        die;
        // echo "<pre>"; print_r($req->input())."<br>".print_r($_FILES); die;
        // echo $file_id; die;
    }

    function sendNotificationToStaff($policy_name = null){

        $home_id = Auth::user()->home_id;
        $users = User::getStaffList($home_id);
        
        foreach ($users as $key => $user) {
            //echo '<pre>'; print_r($user); die;
            $email               = $user['email'];
            $name                = $user['name'];
            $user_name           = $user['user_name'];
            $company_name        = PROJECT_NAME;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) 
            {   
                Mail::send('emails.policy.notify_policy_update', ['name'=>$name, 'user_name'=>$user_name, 'policy_name' => $policy_name], function($message) use ($email,$company_name)
                {
                    $message->to($email,$company_name)->subject('SCITS Policy has been updated');
                });                
            } 
        }
    }                        


}
