<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;

use Illuminate\Http\Request;

use DB, Auth;

use App\FileManager, App\ServiceUser, App\CareTeam;

use Illuminate\Support\Facades\Mail;



class FileController extends ServiceUserManagementController

{

    public function index($service_user_id = null) {



        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');

        if(Auth::user()->home_id != $su_home_id){

            die;

        }



        $logged_plans = DB::table('su_file_manager')->select('id','file')->where('service_user_id',$service_user_id)->orderBy('id','desc');

        $search = '';

        $records_limit = '10';

        

        if(isset($_GET['search'])) {  // echo '1'; die; 

            //print_r($_GET);

            if( (!empty($_GET['search'])) && (!empty($_GET['category'])) ) { // echo '1'; die;

                $logged_files = $logged_plans->where('file','like','%'.$_GET['search'].'%')->where('category_id',$_GET['category'])->paginate($records_limit);          

            } else if(!empty($_GET['search'])){ //echo '2'; die;

                $logged_files = $logged_plans->where('file','like','%'.$_GET['search'].'%')->paginate($records_limit);                          

            } else{ //echo '3'; die;

                $logged_files = $logged_plans->where('category_id',$_GET['category'])->paginate($records_limit);                

            }



        }

        else  {

            $logged_files = $logged_plans->paginate($records_limit);

        }



        foreach ($logged_files as $key => $value)  {



            $file_name = $value->file;

            $path = pathinfo($file_name);

            $ext  = $path['extension'];



            if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {

                $icn_class = 'fa fa-file-image-o';

                $file_url  =  ServiceUserFilePath.'/'.$service_user_id.'/'.$value->file;

                // echo $file_url; die;

                $file      = '<a class="wrinkled" href="'.$file_url.'" data-fancybox data-caption="'.$value->file.'"> 

                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 

                            </a>';



            } else if($ext == 'pdf') {

                $icn_class = 'fa fa-file-pdf-o';

                $file_url  = 'http://docs.google.com/gview?url='.ServiceUserFilePath.'/'.$service_user_id.'/'.$value->file.'&embedded=true';

                

                $file      = '<a class="wrinkled" data-fancybox data-type="iframe" data-src="'.$file_url.'" href="javascript:;">

                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 

                              </a>';

                                                

            } else if($ext == 'doc' || $ext == 'docx'  || $ext == 'wps' ) {

                $icn_class = 'fa fa-file-word-o';

                $file_url  = 'http://docs.google.com/gview?url='.ServiceUserFilePath.'/'.$service_user_id.'/'.$value->file.'&embedded=true';

                $file      = '<a class="wrinkled" data-fancybox data-type="iframe" data-src="'.$file_url.'" href="javascript:;">

                                <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 

                              </a>';

                

            } else{

                $icn_class = 'fa fa-file-image-o';

                $file_url  =  ServiceUserFilePath.'/'.$service_user_id.'/'.$value->file;

                $file      = '<a class="wrinkled" href="'.$file_url.'" data-fancybox data-caption="'.$value->file.'"> 

                                    <span class="policy-list "><i class="'.$icn_class.'"></i> '.ucfirst($value->file).'</span> 

                              </a>';

            }



            echo '<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cog-panel delete-file">

                    <span>'.$file.'</span> 

                    <span class="clr-blue settings filmngr-setting p-r-20 pull-right">

                        <i class="fa fa-cog"></i>

                        <div class="pop-notifbox">

                            <ul class="pop-notification" type="none">

                                <li> <a href="'.ServiceUserFilePath.'/'.$service_user_id.'/'.$value->file.'" target="_blank"> <span> <i class="fa fa-eye"></i> </span> View File </a> </li>



                                <li> <a href="#" class="delete-logged-file" logged_file_id ="'.base64_encode(convert_uuencode($value->id)).'"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> Remove </a> </li>

                                <li> <a href="#" class="send_email" logged_file_id ="'.$value->id.'"> <span> <i class="fa fa-envelope"></i> </span> Send </a> </li>

                            </ul>

                        </div>

                    </span>

                </div> ';

        }

        // echo '<div class="col-sm-6">';

        // echo $logged_files->links();

        //echo '</div>';

        if(!empty($logged_files)){

            echo '<div class="file_mngr_paginate m-l-15">';

            echo $logged_files->links();

            echo '</div>'; 

        }

    }



    public function add_files(Request $request)	{

        

      	if($request->isMethod('post'))	

		{

			$data = $request->all();

		    // echo '<pre>';

            // echo $request->file_category;

            // print_r($data);

            // print_r($_FILES);

            // print_r($data['images']);

            // print_r($_FILES['images']['name']);

            // die;

          

            $service_user_id = $data['service_userid'];



            if(!empty($_FILES['images']['name'])) {

                

                

                foreach($_FILES['images']['name'] as $key => $value){

                    

                    if($value!=''){

                    $tmp_file   =   $_FILES['images']['tmp_name'][$key];

                    $image_info =   pathinfo($_FILES['images']['name'][$key]);                    

                    //$file_name  =   substr($image_info['filename'],0,100);

                    $file_name  =   $image_info['filename'];

                    $ext        =   strtolower($image_info['extension']);

                    $new_name   =   $file_name.'.'.$ext;



                    $allowed_ext = array('jpg','jpeg','png','pdf','doc','docx','wps');

                    if(in_array($ext,$allowed_ext)){



                        $file_dest = base_path().ServiceUserFileBasePath.'/'.$service_user_id; 

                    

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

                            $home_ids = Auth::user()->home_id;
                            $ex_home_ids = explode(',', $home_ids);
                            $home_id=$ex_home_ids[0];

                            $file                  = new FileManager;

                            $file->service_user_id = $service_user_id;

                            $file->category_id     = $data['file_category'];

                            $file->file            = $new_name;

                            $file->home_id         = $home_id;

                            $file->save();

                            // if($file->save()){

                            //     $result[$key]['response']   = true;

                            //     $result[$key]['save_id']    = base64_encode(convert_uuencode($file->id));

                            //     $result[$key]['file_index'] = $key;

                            //     $result[$key]['file_name']  = $new_name;                                

                            // } else{

                            //     $result[$key]['response'] = false;

                            //     $result[$key]['save_id'] = '';

                            //     $result[$key]['file_index'] = $key;

                            //     $result[$key]['file_name']  = $new_name;                                

                            // }

                        }

                    } 



                    // if(!isset($result[$key]['save_id'])){

                    //     $result[$key]['response'] = false;

                    //     $result[$key]['save_id'] = '';

                    //     $result[$key]['file_index'] = $key;

                    // } Child

                    }



                }

            }

            return 1;

            //echo '<pre>'; print_r($result);

            // $result = array_values($result);

            // return json_encode($result); 

  		}

	}



	public function delete($logged_file_id = null) 

    { 

         

        $deleted = '';

        if(!empty($logged_file_id)) {



            $logged_file_id = convert_uudecode(base64_decode($logged_file_id));



            $file_info = FileManager::select('service_user_id','file')->where('id', $logged_file_id)->first();

            

            if(!empty($file_info)){

                $service_user_id = $file_info->service_user_id;



                $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');

                if(Auth::user()->home_id != $su_home_id){

                    return false; 

                }



                $file_name = $file_info->file;

                $file_dest = base_path().ServiceUserFileBasePath.'/'.$service_user_id; 

            

                if(file_exists($file_dest.'/'.$file_name)){

                    unlink($file_dest.'/'.$file_name);

                }

            

                $deleted = FileManager::where('id', $logged_file_id)->delete();

            }



            if($deleted){

                return 'true';

            } else {

                return 'false';

            }            

        }

    }



    /*individual file operations */



    /*Add a single file*/

    public function add_file(Request $request){

    	

    	$data = $request->input();

    	$service_user_id = $data['service_user_id'];

        $save_id = ''; // last insert id 

        $new_name = '';

        

        $su_home_id = ServiceUser::where('id',$service_user_id)->value('home_id');

        if(Auth::user()->home_id == $su_home_id){

       

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

                    $file_dest = base_path().ServiceUserFileBasePath.'/'.$service_user_id; 

                

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

                        $file                  = new FileManager;

                        $file->service_user_id = $service_user_id;

                        $file->category_id     = $data['file_category_id'];

                        $file->file            = $new_name;

                        $file->home_id         = Auth::user()->home_id;

                        

                        if($file->save()) {

                            $save_id = base64_encode(convert_uuencode($file->id));

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



    /*public function upload_delete(Request $request) 

    {   

        if($request->isMethod('post')) {



            $data = $request->input();

            

            $service_user_id = $data['service_user_id'];

            $file_name = $data['file_name'];



            

            $file_dest = base_path().ServiceUserFileBasePath.'/'.$service_user_id; 

            

            if(file_exists($file_dest.'/'.$file_name)){

                unlink($file_dest.'/'.$file_name);

            }

            $deleted = FileManager::where('service_user_id', $data['service_user_id'])->where('file',$file_name)->delete();

            

            echo $deleted; die;



        }

    }*/

        

    public function file_email(Request $request) {



        $data = $request->input();

        // echo "<pre>"; print_r($data); die;



        $service_user_id = $data['service_user_id'];



        $file_info = FileManager::where('id', $data['file_id'])->value('file');



        $care_team_members = CareTeam::select('id','name','email','service_user_id')

                                        ->whereIn('id', $data['sel_care_team_id'])

                                        ->get()

                                        ->toArray();



        // echo "<pre>"; print_r($care_team_members); die;



        foreach ($care_team_members as $key => $value) {

            

            $yp_name      = ServiceUser::where('id', $service_user_id)->value('user_name');

            // echo $yp_name; die;

            $member_name  = $value['name'];

            $member_email = $value['email'];

            $staff_name   = Auth::user()->user_name;

            $company_name = PROJECT_NAME;

            $file_attached = url(ServiceUserFileBasePath.'/'.$service_user_id.'/'.$file_info);

            // echo $file_attached; die;

            if (!filter_var($member_email, FILTER_VALIDATE_EMAIL) === false) {

                

                Mail::send('emails.file_manager_mail',['member_name'=>$member_name, 'member_email'=>$member_email, 'staff_name' => $staff_name, 'yp_name' => $yp_name,'service_user_id'=>$service_user_id, 'file_attached' => $file_attached,'file_info'=> $file_info ], function($message) use ($member_email,$company_name,$file_info,$service_user_id)

                {

                    $message->to($member_email, $company_name)->subject('Service User File Notification');

                    // $pathToFile = url(ServiceUserFileBasePath.'/'.$service_user_id.'/'.$file_info);

                    // $file_ext   = pathinfo($pathToFile);

                    // $file_ext   = $file_ext['extension'];



                    // $message->attach($pathToFile , ['as' => 'file_info.'.$file_ext]);



                    // $message->getSwiftMessage();

                });

            }

        }

        return redirect('/service/user-profile/'.$service_user_id)->with('success','Email send to care team members successfully.');



    }

    

}

