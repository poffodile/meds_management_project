<?php
namespace App\Http\Controllers\backEnd\superAdmin;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session; 
use DB;
use App\SocialApp; 
use Illuminate\Support\Facades\Mail;

class SocialAppController extends Controller
{
    public function index(Request $request)
    {	
        
        $social_app = SocialApp::select('id','name','created_at')->where('is_deleted','O');
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
            $social_app  = $social_app ->where('name','like','%'.$search.'%');
        }

        /*if($limit == 'all') {
            $users = $users_query->get();
        } else{
            $users = $users_query->paginate($limit);
        }*/

        $social_app = $social_app->paginate($limit);

        //$users = DB::table('user')->select('id','name','user_name', 'email', 'access_level')->paginate(25);
        $page = 'social-app';

       	return view('backEnd/superAdmin/social_app/social_apps', compact('page','limit','social_app','search')); //users.blade.php
    }

    public function add(Request $request) { 
      	
        if($request->isMethod('post')) {

    	    $social_app          = new SocialApp;
            $social_app->name  = $request->name;
            $social_app->icon  = $request->icon ?? '';
                
    		if($social_app->save()) {
    			return redirect('super-admin/social-apps')->with('success', 'Social App added successfully.');
    		} else {
    			 return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
    		}
    	}
        $page = 'social-app';
        return view('backEnd/superAdmin/social_app/social_app_form', compact('page'));
    }
   			
   	public function edit(Request $request, $social_app_id) { 
       
       if($request->isMethod('post')){
            $social_app               = SocialApp::find($social_app_id);
            $social_app->name         = $request->name;
            $social_app->icon         = $request->icon;
            if($social_app->save()){
               return redirect('super-admin/social-apps')->with('success','Social App Updated successfully.'); 
            } else {
               return redirect()->back()->with('error','Socail App could not be Updated.'); 
            }  
        }

       	$social_app = DB::table('social_app')
                        ->where('id', $social_app_id)
                        ->first();
        $page = 'social-app';
        return view('backEnd/superAdmin/social_app/social_app_form', compact('social_app','page'));
    }

    public function delete($social_app_id) {
	    if(!empty($social_app_id)) {    

            $updated = DB::table('social_app')->where('id', $social_app_id)->update(['is_deleted' => '1']);

            if($updated){
                return redirect('super-admin/social-apps')->with('success','Social App deleted Successfully.'); 
            } else{
                return redirect('super-admin/social-apps')->with('error','Social App could not be deleted.'); 
            }
        } else {
                return redirect()->back()->with('error',COMMON_ERROR); 
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

   





    
}