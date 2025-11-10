<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use App\ServiceUserCareHistory, App\CareTeam, App\ServiceUser, App\FormBuilder, App\Notification, App\ServiceUserAFC, App\HomeLabel, App\LogBook, App\ServiceUserLogBook, App\CareTeamJobTitle, App\ServiceUserCareCenter, App\ServiceUserContacts, App\DynamicFormBuilder, App\DynamicForm, App\SocialApp, App\ServiceUserSocialApp, App\ServiceUserMoney, App\ServiceUserMoneyRequest, App\ServiceUserCareHistoryFile, App\User;
use DB, Auth, Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ProfileController extends ServiceUserManagementController
{
    public function index(Request $request, $service_user_id)
    {

        //$d = DynamicForm::countIncidentReport(1,'29-08-2017','30-08-2017');
        //echo '<prE>'; print_r($d); die;
        // update notify
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $updatenotify = array('read_notify' => 1);
        DB::table('su_risk')->where('service_user_id', $service_user_id)->where('home_id', $home_id)->update($updatenotify);
        $risktable = DB::table('su_risk')->where('service_user_id', $service_user_id)->where('home_id', $home_id)->get();
        foreach ($risktable as $rval) {
            DB::table('risk')->where('id', $rval->risk_id)->update($updatenotify);
        }
        DB::table('su_afc')->where('service_user_id', $service_user_id)->update($updatenotify);
        // update notify
        $patient = DB::table('service_user')->where('id', $service_user_id)->where('is_deleted', '0')->first();

        if (!empty($patient)) {

            if ($patient->home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            // $risks = DB::table('risk')->select('id', 'description', 'icon', 'status')
            //     ->where('home_id', $home_id)
            //     ->where('is_deleted', '0')
            //     ->get();

            // subquery that returns latest su_risk.id per risk_id for the given service_user
            $latest = DB::table('su_risk as s')
                ->select('s.risk_id', DB::raw('MAX(s.id) as max_id'))
                ->where('s.service_user_id', $service_user_id)
                ->groupBy('s.risk_id');

            $risks = DB::table('risk')
                ->leftJoinSub($latest, 'latest', function ($join) {
                    $join->on('risk.id', '=', 'latest.risk_id');
                })
                ->leftJoin('su_risk as su', function ($join) {
                    $join->on('su.id', '=', 'latest.max_id');
                })
                ->select(
                    'risk.id as risk_id',
                    'risk.description',
                    'risk.icon',
                    'risk.status as risk_status',        // risk table status (if needed)
                    'su.id as user_risk_id',             // latest su_risk id for this risk & user (or null)
                    'su.status as user_status'           // latest su_risk status for this risk & user (or null)
                )
                ->where('risk.home_id', $home_id)
                ->where('risk.is_deleted', '0')
                ->orderByRaw("
                        CASE 
                        WHEN su.status = 2 THEN 0
                        WHEN su.status = 1 THEN 1
                        WHEN su.status = 0 THEN 2
                        ELSE 3
                        END
                    ")
                ->get();

            $daily_score   = DB::table('daily_record_score')->get();
            $care_team = DB::table('su_care_team')->select('id', 'job_title_id', 'name', 'email', 'phone_no', 'image', 'address')->where('service_user_id', $service_user_id)->where('is_deleted', '0')->orderBy('id', 'desc')->get();

            $care_history = DB::table('su_care_history')->select('id', 'title', 'date', 'description')->where('service_user_id', $service_user_id)->where('is_deleted', '0')->orderBy('date', 'desc')->get();

            $file_category = DB::table('file_category')->select('id', 'name')->where('is_deleted', '0')->orderBy('name', 'asc')->get();

            //get coordnate for map
            $current_location = $patient->current_location;

            //removing new line
            $pattern = '/[^a-zA-Z0-9]/u';
            $current_location = preg_replace($pattern, ' ', (string) $current_location);
            $coordinates = ServiceUser::getLongLat($current_location);

            $latitude = (isset($coordinates['results']['0']['geometry']['location']['lat'])) ? $coordinates['results']['0']['geometry']['location']['lat'] : '';
            $longitude = (isset($coordinates['results']['0']['geometry']['location']['lng'])) ? $coordinates['results']['0']['geometry']['location']['lng'] : '';
            //get coordnate for map end

            /*$daily_records_options = DB::table('daily_record')
                                ->where('home_id',$home_id)
                                ->where('status','1')
                                ->orderBy('id','desc')
                                ->get();*/

            //living skill option
            /*$living_skill_options = DB::table('living_skill')
                                        ->where('home_id',$home_id)
                                        ->where('status','1')
                                        ->where('is_deleted','0')
                                        ->orderBy('id','desc')
                                        ->get();

            $education_record_options = DB::table('education_record')
                                        ->select('id','description')
                                        ->where('home_id', $home_id)
                                        ->where('status','1')
                                        ->where('is_deleted','0')
                                        ->orderBy('id','desc')
                                        ->get();
            //echo '<pre>'; print_r($education_record_options); die;
            $mfc_options = DB::table('mfc')
                            ->select('id','description')
                            ->where('home_id', $home_id)
                            ->where('status','1')
                            ->where('is_deleted','0')
                            ->orderBy('id','desc')
                            ->get();
            
            //service_users list for bmp-rmp
            $service_users = ServiceUser::select('id','name')
                                ->where('home_id',$home_id)
                                ->where('status','1')
                                ->where('is_deleted','0')
                                ->get()
                                ->toArray();*/

            //getting form patterns
            $form_pattern['bmp_rmp'] = '';
            $form_pattern['risk'] = '';
            $form_pattern['su_rmp'] = '';
            $form_pattern['su_bmp'] = '';
            $form_pattern['su_mfc'] = '';
            $form_pattern['incident_report'] = '';

            $service_users = ServiceUser::where('home_id', $home_id)->get()->toArray();
            $dynamic_forms = DynamicFormBuilder::getFormList();

            /*$form     =  FormBuilder::showForm('bmp_rmp');
            $response = $form['response'];
            //echo '<pre>'; print_r($service_users); die;
            if($response == true){
                $form_pattern['bmp_rmp'] = $form['pattern'];
            } else{
                $form_pattern['bmp_rmp'] = '';
            }

            $form     =  FormBuilder::showForm('change_risk');
            $response = $form['response'];
            if($response == true){
                $form_pattern['risk'] = $form['pattern']; 
            } else{
                $form_pattern['risk'] = '';
            }

            $form     =  FormBuilder::showForm('su_rmp');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_rmp'] = $form['pattern']; 
            } else{
                $form_pattern['su_rmp'] = '';
            }
            
            $form     =  FormBuilder::showForm('su_bmp');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_bmp'] = $form['pattern']; 
            } else{
                $form_pattern['su_bmp'] = '';
            }

            $form     =  FormBuilder::showForm('su_mfc');
            $response = $form['response'];
            if($response == true){
                $form_pattern['su_mfc'] = $form['pattern']; 
            } else{
                $form_pattern['su_mfc'] = '';
            }
            //echo $form_pattern['su_mfc']; die;

            $form     =  FormBuilder::showForm('incident_report');
            $response = $form['response'];
            if($response == true){
                $form_pattern['incident_report'] = $form['pattern']; 
                //echo "<pre>"; print_r($form_pattern['incident_report']); die;
            } else{
                $form_pattern['incident_report'] = '';
            }
*/
            $notifications = Notification::getSuNotification($service_user_id, '', '', 6, $home_id);

            $afc_status = ServiceUser::get_afc_status($service_user_id);

            $labels = HomeLabel::getLabels($home_id);

            //pending rmp and incident reports notifications
            /*$pending_notif = DB::table('su_risk')
                                ->select('su_risk.id','su_risk.rmp_id','su_risk.incident_report_id',
                                    'risk.description as risk_name')
                                ->where('su_risk.service_user_id',$service_user_id)
                                ->join('risk', 'su_risk.risk_id','=', 'risk.id')                                            
                                //->leftJoin('su_rmp', 'su_risk.id', '=', 'su_rmp.su_risk_id')
                                //->leftJoin('su_incident_report', 'su_risk.id', '=', 'su_incident_report.su_risk_id')
                                ->orderBy('su_risk.id','desc')
                                ->get();
            echo '<pre>'; print_r($pending_notif); die;*/
            /*$pending_notif = DB::table('su_risk')
                                ->select('su_risk.id as su_risk_id','su_rmp.id as su_rmp_id', 'su_incident_report.id as su_incident_record_id','risk.description as risk_name')
                                ->where('su_risk.service_user_id',$service_user_id)
                                ->join('risk', 'su_risk.risk_id','=', 'risk.id')                                            
                                ->leftJoin('su_rmp', 'su_risk.id', '=', 'su_rmp.su_risk_id')
                                ->leftJoin('su_incident_report', 'su_risk.id', '=', 'su_incident_report.su_risk_id')
                                ->orderBy('su_risk.id','desc')
                                ->get();*/

            //echo '<pre>'; print_r($pending_notif); die;

            //$su_log_book_category = DB::table('su_log_book_category')->get();
            $care_team_job_title = CareTeamJobTitle::where('is_deleted', '0')
                ->where('home_id', $home_id)
                ->get();
            $su_in_danger        = ServiceUserCareCenter::where('service_user_id', $service_user_id)->where('care_type', 'D')->count();
            $su_req_cb           = ServiceUserCareCenter::where('service_user_id', $service_user_id)->where('care_type', 'R')->count();
            $su_contact          = ServiceUserContacts::where('service_user_id', $service_user_id)->where('is_deleted', '0')->get();

            $social_app     = SocialApp::select('id', 'name', 'icon')->where('is_deleted', '0')->get()->toArray();
            $su_social_app  = ServiceUserSocialApp::select('id', 'social_app_id', 'value')
                ->where('su_social_app.service_user_id', $service_user_id)
                ->get()
                ->toArray();
            $social_app_val = array();
            foreach ($su_social_app as $key => $value) {
                $social_app_val[$value['social_app_id']]['id']    = $value['id'];
                $social_app_val[$value['social_app_id']]['value'] = $value['value'];
                // $social_app_val[$value['social_app_id']]['icon'] = $value['icon'];
            }
            // echo "<pre>"; print_r($social_app);
            // echo "<pre>"; print_r($su_social_app);
            // echo "<pre>"; print_r($social_app_val); 
            // die;

            //Child money
            $my_money = $this->my_money($service_user_id);
            // echo "<pre>"; print_r($my_money); die;
            $noti_data = array();
            if (Session::has('noti_data')) {
                $noti_data = Session::get('noti_data');
                Session::forget('noti_data');
            }

            $users  = User::select('id', 'name', 'email', 'image', 'phone_no')
                ->where('home_id', $home_id)
                ->where('is_deleted', '0')
                ->get()
                ->toArray();

            // Get average rating and count for this service user
            $ratingStats = DB::table('service_user')
                ->where('id', $service_user_id)
                ->select(DB::raw('AVG(behavior_rate) as avg_rating'), DB::raw('COUNT(*) as rating_count'))
                ->first();

            $avg_rating = $ratingStats && $ratingStats->avg_rating ? round($ratingStats->avg_rating, 1) : 0;
            $rating_count = $ratingStats ? intval($ratingStats->rating_count) : 0;

            $current_moods = DB::table('su_mood')->select('su_mood.*', 'mood.id as mood_id', 'mood.name', 'mood.image')
            ->where('su_mood.is_deleted', '0')
            ->where('mood.home_id', $home_id)
            ->where('su_mood.service_user_id', $service_user_id)
            ->join('mood', 'mood.id', 'su_mood.mood_id')
            ->orderBy('su_mood.id', 'desc')
            ->first();

            //echo "<pre>"; print_r($noti_data); die;
            //print_r($patient); die;
            return view('frontEnd.serviceUserManagement.profile', compact('patient', 'risks', 'file_category', 'service_user_id', 'care_team', 'care_history', 'daily_score', 'latitude', 'longitude', 'form_pattern', 'notifications', 'afc_status', 'labels', 'care_team_job_title', 'su_in_danger', 'su_req_cb', 'su_contact', 'service_users', 'dynamic_forms', 'social_app', 'social_app_val', 'my_money', 'noti_data', 'users', 'avg_rating', 'rating_count','current_moods'));
        } else {
            return view('frontEnd.error_404');
        }
    }

    //1st tab care team
    public function add_care_team(Request $request, $service_user_id = null)
    {

        if ($request->isMethod('post')) {

            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $usr_home_id   = $ex_home_ids[0];
            if ($su_home_id != $usr_home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $data                   = $request->all();
            $team                   = new CareTeam;
            $team->service_user_id  = $service_user_id;
            $team->name             = $data['name'];
            $team->job_title_id     = $data['job_title'];
            $team->phone_no         = $data['phone_no'];
            $team->email            = $data['email'];
            $team->address          = $data['address'];
            $team->home_id          = $su_home_id;

            if (!empty($_FILES['image']['name'])) {

                // $member_old_image =  $member->image;
                $tmp_image  =  $_FILES['image']['tmp_name'];
                $image_info =  pathinfo($_FILES['image']['name']);
                $ext        =  strtolower($image_info['extension']);
                $new_name   =  time() . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination =   base_path() . careTeamPath;
                    if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                        $team->image = $new_name;
                    }
                }
            } else {

                $staff_image = $request->staff_image_name;
                $team->image = $staff_image;
                // $user_img = public_path("images\\userProfileImages\\$staff_image");
                $user_img = public_path("images/userProfileImages/$staff_image");  // Ram new code 08/07/2025 avoid error for slash
                // print_r($user_img);
                // echo "<br>";
                // $ctm_img  = '/opt/lampp/htdocs/scits/public/images/careTeam/'.$staff_image; //for localhost
                // $ctm_img  = '/home/mercury/public_html/scits/public/images/careTeam/' . $staff_image; //for mercury server
                // $ctm_img  = public_path("images\\careTeam\\$staff_image"); //for mercury server
                $ctm_img  = public_path("images/careTeam/$staff_image"); // Ram new code 08/07/2025 avoid error for slash

                if (file_exists($user_img)) {
                    if (copy($user_img, $ctm_img)) {
                        Log::info('File copied successfully.');
                    } else {
                        Log::info('Failed to copy file..');
                    }
                } else {
                    Log::info('Source file does not exist');
                }
                // echo $user_img."<br>".$ctm_img; //die;
                copy($user_img, $ctm_img);
            }

            if (!isset($team->image)) {
                $team->image = '';
            }

            if ($team->save()) {
                return redirect()->back()->with('success', 'Care team added successfully.');
            }
        }
    }

    public function edit_care_team(Request $request)
    {
        if ($request->isMethod('post')) {
            $data = $request->all();
            $care_team_id = $data['care_team_id'];

            $careteam   = CareTeam::find($care_team_id);
            if (!empty($careteam)) {

                $su_home_id  = ServiceUser::where('id', $careteam->service_user_id)->value('home_id');
                // $usr_home_id = Auth::user()->home_id;
                $home_ids = Auth::user()->home_id;
                $ex_home_ids = explode(',', $home_ids);
                $usr_home_id = $ex_home_ids[0];
                if ($su_home_id != $usr_home_id) {
                    return redirect('/')->with('error', UNAUTHORIZE_ERR);
                }

                //foreach ($data as $key => $value) {

                $team_old_image             = $careteam->image;
                $careteam->name             = $data['name'];
                $careteam->job_title_id     = $data['job_title'];
                $careteam->phone_no         = $data['phone_no'];
                $careteam->email            = $data['email'];
                $careteam->address          = $data['address'];

                if (!empty($_FILES['image']['name'])) {
                    $tmp_image  =   $_FILES['image']['tmp_name'];
                    $image_info =   pathinfo($_FILES['image']['name']);
                    $ext        =   strtolower($image_info['extension']);
                    $new_name   =   time() . '.' . $ext;

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                        $destination = base_path() . careTeamPath;
                        if (move_uploaded_file($tmp_image, $destination . '/' . $new_name)) {
                            if (!empty($user_old_image)) {
                                if (file_exists($destination . '/' . $team_old_image)) {
                                    unlink($destination . '/' . $team_old_image);
                                }
                            }
                            $careteam->image = $new_name;
                        }
                    }
                }
                if (!isset($careteam->image)) {
                    $careteam->image = '';
                }

                if ($careteam->save()) {
                    return redirect()->back()->with('success', 'Care team Updated successfully.');
                }
            }
        }
    }

    public function delete_care_team($care_team_id = null)
    {

        $careteam = CareTeam::find($care_team_id);

        if (!empty($careteam)) {

            $su_home_id     = ServiceUser::where('id', $careteam->service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($su_home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $careteam_image = $careteam->image;
            $destination    = base_path() . careTeamPath;

            if (!empty($careteam_image)) {
                if (file_exists($destination . '/' . $careteam_image)) {
                    unlink($destination . '/' . $careteam_image);
                }
            }

            $deleted = CareTeam::where('id', $care_team_id)->update(['is_deleted' => '1']);
            if ($deleted) {
                return redirect()->back()->with('success', 'Care Team Deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Care Team could not be Deleted.');
            }
        }
    }

    //2nd tab care history
    public function add_care_history(Request $request, $service_user_id)
    {

        if ($request->isMethod('post')) {
            $data = $request->all();

            $su_home_id     = ServiceUser::where('id', $service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($su_home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $care                   = new ServiceUserCareHistory;
            $care->service_user_id  = $service_user_id;
            $care->title            = $data['title'];
            $care->date             = date('Y-m-d', strtotime($data['date']));
            $care->home_id          = $su_home_id;
            $care->description      = $data['description'];
            if ($care->save()) {

                if (!empty($_FILES['files']['name'])) {
                    //echo '<pre>'; print_r($_FILES); die;
                    foreach ($_FILES['files']['name'] as $key => $value) {
                        //echo '<pre>'; print_r($value); die;
                        if (!empty($value)) {

                            $tmp_file   =   $_FILES['files']['tmp_name'][$key];
                            $image_info =   pathinfo($_FILES['files']['name'][$key]);

                            //$file_name  =   substr($image_info['filename'],0,100);
                            $file_name  =   $image_info['filename'];

                            $ext        =   strtolower($image_info['extension']);
                            $new_name   =   $file_name . '.' . $ext;

                            $allowed_ext = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'wps');
                            if (in_array($ext, $allowed_ext)) {

                                $file_dest = base_path() . suCareHistoryFileBasePath;

                                //if(!is_dir($file_dest)) { //check if file directory not exits then create it
                                //   mkdir($file_dest);
                                //} else { //if directory exits check if any file with same name exists

                                //  $i = 1;
                                // while(file_exists($file_dest.'/'.$new_name)){ 
                                //   $i++;
                                $new_name = $file_name . '.' . $ext;
                                // }
                                //}

                                if (move_uploaded_file($tmp_file, $file_dest . '/' . $new_name)) {

                                    $file                       = new ServiceUserCareHistoryFile;
                                    $file->su_care_history_id   = $care->id;
                                    $file->file                 = $new_name;
                                    $file->save();
                                }
                            }
                        }
                    }
                }

                return redirect()->back()->with('success', 'Care History added successfully.');
            }
        }
    }

    public function edit_care_history(Request $request)
    {

        if ($request->isMethod('post')) {
            $data = $request->all();

            $care_history   = ServiceUserCareHistory::find($data['care_history_id']);

            $su_home_id     = ServiceUser::where('id', $care_history->service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($su_home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $care_history->title  = $data['title'];
            $care_history->date   = date('Y-m-d', strtotime($data['date']));
            $care_history->description = $data['description'];
            $care_history->save();

            if ($care_history) {
                return redirect()->back()->with('success', 'Care History updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Care History could not be updated,');
            }
        }
    }

    public function delete_care_history($care_history_id = null)
    {

        $care_history   = ServiceUserCareHistory::find($care_history_id);

        if (!empty($care_history)) {

            $su_home_id     = ServiceUser::where('id', $care_history->service_user_id)->value('home_id');
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($su_home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $res = ServiceUserCareHistory::where('id', $care_history_id)->update(['is_deleted' => '1']);

            if ($res == 1) {
                return redirect()->back()->with('success', 'Care History Deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Care History could not be Deleted,');
            }
        }
    }

    //4th tab full profile
    public function edit_detail_info(Request $request)
    {


        if ($request->isMethod('post')) {
            $data = $request->all();

            $service_user_id = $data['service_user_id'];
            unset($data['service_user_id']);
            unset($data['_token']);
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            foreach ($data as $key => $value) {
                $updated = ServiceUser::where('id', $service_user_id)
                    ->where('home_id', $home_id)
                    ->update([
                        // $key => nl2br(trim($value))           
                        $key => trim($value)
                    ]);
            }
            if ($updated) {
                return redirect()->back()->with('success', 'User Info updated successfully.');
            } else {
                return redirect()->back()->with('error', 'User Info could not be updated,');
            }
        }
    }

    //3rd tab contacts
    public function edit_location_info(Request $request)
    {

        if ($request->isMethod('post')) {
            $data = $request->all();
            // $data['current_location'] = trim($data['current_location']);
            // $current_location = str_replace("\n\r", '<br />', $data['current_location']);
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $updated = ServiceUser::where('id', $data['service_user_id'])
                ->where('home_id', $home_id)
                ->update([
                    //'current_location' => $current_location,
                    'current_location' => nl2br(trim($data['current_location'])),
                    'previous_location' => nl2br(trim($data['previous_location']))
                ]);

            if ($updated) {
                return redirect()->back()->with('success', 'User location Info updated successfully.');
            } else {
                return redirect()->back()->with('error', 'User location Info could not be updated,');
            }
        }
    }

    public function edit_contact_info(Request $request)
    {

        if ($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $updated = ServiceUser::where('id', $data['service_user_id'])
                // ->where('home_id', Auth::user()->home_id) this line is not need because we have already compare with primary id. but developer wants it then I write a new code below for the checking of home
                ->where('home_id', $home_id)
                ->update([
                    'phone_no' => $data['phone_no'],
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                ]);

            if ($updated) {

                //saving social app info
                if (isset($data['social_app'])) {
                    foreach ($data['social_app'] as $social_data) {

                        if (!empty($social_data['value'])) {

                            $su_soc_app = ServiceUserSocialApp::where('id', $social_data['su_app_id'])->first();

                            //if its value is not already stored then save it as a new record
                            if (empty($su_soc_app)) {
                                $su_soc_app                  = new ServiceUserSocialApp;
                                $su_soc_app->social_app_id   = $social_data['social_app_id'];
                                $su_soc_app->service_user_id = $data['service_user_id'];
                            }
                            $su_soc_app->value = $social_data['value'];
                            $su_soc_app->save();
                        } else {
                            $su_soc_app = ServiceUserSocialApp::where('id', $social_data['su_app_id'])->delete();
                        }
                    }
                }

                return redirect()->back()->with('success', 'User Contact Info updated successfully.');
            } else {
                return redirect()->back()->with('error', 'User Contact Info could not be updated.');
            }
        }
    }

    public function update_afc_status($service_user_id = null, Request $request)
    {

        $service_user = ServiceUser::select('home_id', 'name')->where('id', $service_user_id)->first();
        //echo "<pre>"; print_r($service_user); die;
        if (empty($service_user)) {
            return false;
        }
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $su_home_id = $service_user->home_id;
        if ($su_home_id != $home_id) {
            echo 'AUTH_ERR';
            die;
        }

        $current_afc_status = $this->get_afc_status($service_user_id);

        //0 = present  i.e. came in
        //1 = absent   i.e. came out
        // if($current_afc_status == 0){
        //     $data = $request->all();
        //     $new_status  = 1;
        //     $log_title   = 'out';
        //     $wear_detail = 'Wear: '.$data['log_detail'];
        // } else{
        //     $new_status  = 0;
        //     $log_title   = 'came in';
        //     $wear_detail = '';
        // }
        if ($current_afc_status == 1) {
            $data = $request->all();
            $new_status  = 0;
            $log_title   = 'out';
            $wear_detail = 'Wear: ' . $data['log_detail'];
        } else {
            $new_status  = 1;
            $log_title   = 'came in';
            $wear_detail = '';
        }

        $su_afc                  = new ServiceUserAFC;
        $su_afc->home_id         = $su_home_id;
        $su_afc->service_user_id = $service_user_id;
        $su_afc->created_at      = date("Y-m-d H:i:s");
        $su_afc->afc_status      = $new_status;

        if ($su_afc->save()) {

            if ($request->isMethod('post')) {
                //$data = $request->all();

                //saving record in yp's log book
                $log_book_record          = new LogBook;
                //$log_book_record->title   = $service_user->name.' '.$log_title.' at '.date('H:i a');
                $log_book_record->title   = ucfirst($service_user->name) . ' ' . $log_title;
                $log_book_record->date    = date('Y-m-d H:i:s');
                $log_book_record->details = $wear_detail; //clothing info
                $log_book_record->home_id = $home_id;
                $log_book_record->user_id = Auth::user()->id;
                $log_book_record->category_name = "Attendance";
                $log_book_record->category_icon = "fa fa-clock-o";


                if ($log_book_record->save()) {

                    $su_log_book_record                     =   new ServiceUserLogBook;
                    $su_log_book_record->service_user_id    =   $service_user_id;
                    $su_log_book_record->log_book_id        =   $log_book_record->id;
                    $su_log_book_record->user_id            =   Auth::user()->id;
                    //$su_log_book_record->category_id        =   'YP_WEAR';

                    if ($su_log_book_record->save()) {
                        $result['response'] = true;
                    } else {
                        $result['response'] = false;
                    }
                }
            }

            $notification                             = new Notification;
            $notification->service_user_id            = $service_user_id;
            $notification->event_id                   = $su_afc->id;
            $notification->notification_event_type_id = '13';
            $notification->event_action               = 'ADD';
            $notification->home_id                    = $home_id;
            $notification->user_id                    = Auth::user()->id;
            $notification->save();

            echo 'true';
            //echo '1';
        } else {
            echo 'false';
            // echo '0';
        }
        die;
    }

    public function show_notifications(Request $request)
    {

        $data = $request->input();
        $service_user_id = $data['service_user_id'];

        $start_date = '';
        $end_date   = '';

        if (isset($data['start_date'])) {
            if (!empty($data['start_date'])) {
                $start_date = $data['start_date'];
            }
        }

        if (isset($data['end_date'])) {
            if (!empty($data['end_date'])) {
                $end_date = $data['end_date'];
            }
        }
        $home_id = Auth::user()->home_id;
        $notifications = Notification::getSuNotification($service_user_id, $start_date, $end_date, '', $home_id);
        //echo '<pre>'; sprint_r($notifications); die;
        echo $notifications;
        die;
    }

    public function get_afc_status($service_user_id = null)
    {

        $afc_status = ServiceUser::get_afc_status($service_user_id);
        //echo  $afc_status;
        return $afc_status;
    }

    //yp personal contacts
    public function edit_contact_person(Request $request)
    {

        $data = $request->all();
        $contact_us_id = $data['contact_us_id'];

        $contact_us = ServiceUserContacts::find($contact_us_id);
        if (!empty($contact_us)) {
            $su_home_id = ServiceUser::where('id', $contact_us->service_user_id)->value('home_id');
            $home_id = Auth::user()->home_id;
            if ($su_home_id != $home_id) {
                return redirect('/')->with('error', UNAUTHORIZE_ERR);
            }

            $contact_us_old_image     = $contact_us->image;
            $contact_us->name         = $data['contact_name'];
            $contact_us->job_title_id = $data['contact_job_title'];
            $contact_us->phone_no     = $data['contact_phone_no'];
            $contact_us->email        = $data['contact_email'];
            $contact_us->address      = $data['contact_address'];

            // echo "<pre>"; print_r($_FILES); die;
            if (!empty($_FILES['contact_image']['name'])) {
                $temp_image = $_FILES['contact_image']['tmp_name'];
                $image_info = pathinfo($_FILES['contact_image']['name']);
                $ext        = strtolower($image_info['extension']);
                $new_name   = time() . '.' . $ext;
                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') {
                    $destination = base_path() . contactsBasePath;
                    if (move_uploaded_file($temp_image, $destination . '/' . $new_name)) {
                        if (!empty($contact_us_old_image)) {
                            if (file_exists($destination . '/' . $contact_us_old_image)) {
                                unlink($destination . '/' . $contact_us_old_image);
                            }
                        }
                        $contact_us->image = $new_name;
                    }
                }
            }
            if (!isset($contact_us->image)) {
                $contact_us->image = '';
            }
            if ($contact_us->save()) {
                return redirect()->back()->with('success', 'Contact person Updated successfully.');
            }
        } else {
            return redirect()->back()->with('error', COMMON_ERROR);
        }
    }


    public function delete_contact_person($contact_us_id = null)
    {

        if (!empty($contact_us_id)) {
            $contact_us = ServiceUserContacts::find($contact_us_id);
            if (!empty($contact_us)) {
                $su_home_id     = ServiceUser::where('id', $contact_us->service_user_id)->value('home_id');
                if ($su_home_id != Auth::user()->home_id) {
                    return redirect('/')->with('error', UNAUTHORIZE_ERR);
                }

                /*$contact_image = $contact_us->image;
                $destination = base_path().contactsBasePath;

                if(!empty($contact_image)) {
                    if(file_exists($destination.'/'.$contact_image)) {
                        unlink($destination.'/'.$contact_image);
                    }
                }*/

                $deleted = ServiceUserContacts::where('id', $contact_us_id)->update(['is_deleted' => '1']);
                if ($deleted) {
                    return redirect()->back()->with('success', 'Contact person deleted successfully.');
                } else {
                    return redirect()->back()->with('error', 'Contact person could not be Deleted.');
                }
            }
        } else {
            return redirect()->back()->with('error', COMMON_ERROR);
        }
    }


    function my_money($service_user_id = null)
    {

        $my_money = array();

        $my_money['balance'] = ServiceUserMoney::where('service_user_id', $service_user_id)
            ->orderBy('id', 'desc')
            ->value('balance');

        $accept = ServiceUserMoneyRequest::where('service_user_id', $service_user_id)->where('status', '2')->orderBy('id', 'desc')->get()->toArray();

        $my_money['accepted']['request'] = count($accept);
        $my_money['accepted']['amount'] = 0;
        foreach ($accept as $key => $value) {
            $my_money['accepted']['amount'] += $value['amount'];
        }

        $pending = ServiceUserMoneyRequest::where('service_user_id', $service_user_id)->where('status', '0')->orderBy('id', 'desc')->get()->toArray();

        $my_money['pending']['request'] = count($pending);
        $my_money['pending']['amount']  = 0;
        foreach ($pending as $key => $value) {
            $my_money['pending']['amount'] += $value['amount'];
        }

        $reject = ServiceUserMoneyRequest::where('service_user_id', $service_user_id)->where('status', '1')->orderBy('id', 'desc')->get()->toArray();

        $my_money['reject']['request'] = count($reject);
        $my_money['reject']['amount']  = 0;
        foreach ($reject as $key => $value) {
            $my_money['reject']['amount'] += $value['amount'];
        }

        return $my_money;
    }


    public function delete_hist_file($su_care_history_id = null)
    {

        $del = ServiceUserCareHistoryFile::where('id', $su_care_history_id)->delete();
        if ($del) {
            return redirect()->back()->with('success', 'Care History file deleted successfully.');
        } else {
            return redirect()->back()->with('success', COMMON_ERROR);
        }
    }

    /**
     * Save child behavior rating (AJAX)
     */
    public function saveRating(Request $request, $service_user_id)
    {
        $data = $request->all();

        $rules = [
            'rating' => 'required|integer|min:1|max:5',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // verify service user belongs to current user's home
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
        if (empty($su_home_id) || $su_home_id != $home_id) {
            return response()->json(['message' => 'Unauthorized or service user not found'], 403);
        }

        try {
            // Attempt to insert into su_rating table. If table doesn't exist, this will throw and return error.
            DB::table('service_user')->updateOrInsert(
                ['id' => $service_user_id], // condition
                [
                    'behavior_rate' => $data['rating'],
                ]
            );

            return response()->json(['status' => true, 'message' => 'Rating saved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to save child rating: ' . $e->getMessage(), ['data' => $data, 'user' => Auth::user()->id]);
            return response()->json(['status' => false, 'message' => 'Failed to save rating'], 500);
        }
    }

    public function submit_form()
    {
        echo "Hiiiii";
    }
}
