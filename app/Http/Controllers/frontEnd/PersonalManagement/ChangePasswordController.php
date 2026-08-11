<?php
namespace App\Http\Controllers\frontEnd\PersonalManagement;
use App\Http\Controllers\frontEnd\PersonalManagement;
use Illuminate\Http\Request;
use App\User;
use DB, Auth, Hash;

class ChangePasswordController extends ProfileController
{   
    public function change_password(Request $request) 
    {
        $data = $request->input();
        $home_id = Auth::User()->home_id;
        $user_id = Auth::User()->id;

        // $old_password = User::where('home_id', $home_id)->where('id', $user_id)->value('password');
        $user = User::where('id', $user_id)->select('id', 'home_id', 'password')->first();
        //Old Password Check with entered password
        if(!empty($user)) {

            $old_password = $user->password; 
            
            /*$current_password = $data['current_password'];
            echo "old = ".$old_password;
            echo "<br/>";
            echo "current = ".$current_password; die;*/
            
            if(Hash::check($data['current_password'],  $old_password)) { //password Verification

                //$user = User::find($user_id);
                $user->password = Hash::make($data['new_password']);

                if($user->save()) {
                    
                    return $resp = array('response'=>'ok');
                } else {

                    return $resp = array('response'=>'not_saved');
                }
                
            } else{
                
                return $resp = array('response'=>'not_correct');
            }
        } else {
            
            return $resp = array('response'=>'user_not_found');
        }
        return $result;
    }



    /*public function change_password(Request $request, $user_id=null) 
    {
        $data = $request->input();
        $home_id = Auth::User()->home_id;

        // $old_password = User::where('home_id', $home_id)->where('id', $user_id)->value('password');
        $user = User::where('id', $user_id)->select('id', 'home_id', 'password')->first();
        //Old Password Check with entered password
        if(!empty($user)) {

            $old_password = $user->password; 
            if($home_id == $user->home_id) {

                if(Hash::check($data['current_password'], $old_password)) {

                    //password Verification
                    if($data['new_password'] == $data['confirm_password']) {

                        if($request->isMethod('post')) {

                            $user = User::find($user_id);
                            $user->password = Hash::make($data['new_password']);

                            if($user->save()) {
                                return redirect()->back()->with('success', "New password saved successfully.");
                            } else {
                                return redirect()->back()->with('error', COMMON_ERROR);
                            }
                        }
                    } else {
                        return redirect()->back()->with('error', "Password didn't matched.");
                    }
                } else{
                    return redirect()->back()->with('error', "Current Password entered not correct.");
                }
            } else {
                return redirect()->back()->with('error', UNAUTHORIZE_ERR);
            }
        } else {
            return redirect()->back()->with('error', 'User not found');
        }
    }*/

    /*public function change_password(Request $request) 
    {
        $data = $request->input();
        $home_id = $data['j'];
        // echo "<pre>"; print_r($data); die;
        // $current_password = User::where('id', $user_id)->value('password');
        
        $current_password = User::where('home_id', $home_id)->value('password');

        if(Hash::check($data['current_password'], $current_password)) {

            $user = User::find($)
            $result['response'] = true;
        } else {

            $result['response'] = false;
        }
        return $result;
    }*/
}
