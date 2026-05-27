<?php



namespace App\Http\Controllers\frontEnd;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\User;



class LockAccountController extends Controller

{

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {

        $this->middleware('auth');
    }



    //show lockscreen page

    public function lockscreen(Request $request)
    {

        // echo "<pre>"; print_r($request->input()); die;

        session(['LOCKED' => 'TRUE']);

        //Session::forget('LAST_ACTIVITY');

        return view('frontEnd.lockscreen');
    }



    //when clicked from link of header dropdown

    //To save the current page which is to open  after entering correct password on lockscreen  

    public function lock(Request $request)
    {

        $data = $request->input();

        $pre_path = $data['path'];



        //for managing variable lockscreen managing variable

        // Session::set('PREVIOUS_PATH',$pre_path);
        Session::put('PREVIOUS_PATH', $pre_path);

        /* 

            NOTE: so that when a user login again after set time for lockscreen.

                    user should not be redirected to lockscreen after entering credentials

        */



        return redirect('/lockscreen');
    }



    public function unlock(Request $request)
    {



        $previous_user_home_ids = Auth::user()->home_id;

        if (!empty($previous_user_home_ids)) {

            $previous_user_home_ids = explode(',', $previous_user_home_ids);
        }

        if ($request->user_name != Auth::user()->user_name) {

            $user_name          = $request->user_name;

            $entered_password   = $request->password;

            $password           = User::select('id', 'password', 'home_id')

                ->where('user_name', $user_name)

                ->first();

            //Start 25 sep 2018------------------------------------------

            //if previous user home is not match with current user home then it will show error

            $home_ids = $password->home_id;

            if (!empty($home_ids)) {

                $home_ids = explode(',', $home_ids);
            }

            if (!empty($home_ids)) {

                if (!in_array($previous_user_home_ids[0], $home_ids)) {

                    return redirect()->back()->with('error', 'You are not authorized to access this home');
                }
            }

            //End 25 sep 2018------------------------------------------



            if (!empty($password)) {



                $password = json_decode(json_encode($password->password));



                if (Hash::check($entered_password, $password)) {

                    User::setUserLogInStatus(0);

                    Auth::logout();

                    Session::forget('LAST_ACTIVITY');



                    if (Auth::attempt([

                        'user_name'  => $request->user_name,

                        'password'   => $request->password

                    ])) {



                        $logged_in = Auth::user()->logged_in;



                        if ($logged_in == '1') {



                            $last_activity = Auth::user()->last_activity_time;

                            $current_time  = date('Y-m-d H:i:s');



                            $last_activity = Carbon::parse($last_activity);

                            $diff_mint     = $last_activity->diffInMinutes();



                            if ($diff_mint > SESSION_TIMEOUT) {
                            } else {

                                Auth::logout();

                                return redirect()->back()->with('error', 'You are already logged in from some other device.');
                            }
                        }



                        $request->session()->forget('LOCKED');

                        User::setUserLogInStatus(1);

                        return redirect('/roster')->with('success', 'Welcome back ' . Auth::user()->user_name);
                        // return redirect('/')->with('success','Welcome back '.\Auth::user()->user_name);

                    }
                } else {

                    return back()->with('error', 'Password does not match. Please try again.');
                }
            } else {

                return back()->with('error', 'User name does not exist. Please try again.');
            }
        } else {



            $password = $request->password;

            $this->validate($request, [

                'password' => 'required|string',

            ]);



            if (Hash::check($password, Auth::user()->password)) {

                $request->session()->forget('LOCKED');



                Session::put('LAST_ACTIVITY', time());



                //redirecting to previous path

                if (Session::has('PREVIOUS_PATH')) { //echo '1';die;

                    $previous_path = Session::get('PREVIOUS_PATH');

                    Session::forget('PREVIOUS_PATH');



                    if ($previous_path == '/') { //only for dashboard page

                        $previous_path = '';
                    }



                    return redirect('/' . $previous_path);
                }

                return redirect('/');
            }

            return back()->with('error', 'Password does not match. Please try again.');
        }
    }
}
