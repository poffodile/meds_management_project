<?php

namespace App\Http\Controllers\Api\Staff;

use DateTime, Carbon\Carbon;

use Illuminate\Http\Request;
use App\ServiceUserHealthRecord;
use App\ServiceUserPlacementPlan;
use Illuminate\Support\Facades\DB;
use Validator;
use App\User, App\ServiceUser, App\ServiceUserEarningStar, App\DynamicForm, App\ServiceUserEarningIncentive, App\ServiceUserAFC;
use App\Http\Controllers\frontEnd\StaffManagementController;
use App\UserQualification;
use App\Models\UserEmergencyContact;

class ServiceUserController extends StaffManagementController
{
    public function listing_service_user($staff_id)
    {
        $staff_home_id = User::where('id', $staff_id)->value('home_id');
        $listing_service_users = ServiceUser::select('id', 'name', 'date_of_birth', 'admission_number', 'section', 'image')->where('home_id', $staff_home_id)->get();
        $listing_service_users = json_decode(json_encode($listing_service_users), true);

        foreach ($listing_service_users as $key => $listing) {
            $age = $listing['date_of_birth'];
            $listing_service_users[$key]['age'] = Carbon::parse($age)->diff(Carbon::now())->format('%y years');

            $total_stars = ServiceUserEarningStar::where('service_user_id', $listing['id'])->value('star');
            $listing_service_users[$key]['earning_stars'] = (int)$total_stars;
        }
        if (!empty($listing_service_users)) {
            return json_encode(array(
                'result' => array(
                    'response' => true,
                    'image_url' => serviceUserProfileImagePath,
                    'data' => $listing_service_users,
                    'message' => "Listing of Childs."
                )
            ));
        } else {
            return json_encode(array(
                'result' => array(
                    'response' => false,
                    'message' => "Data not found."
                )
            ));
        }
    }

    // Rohan
    public function notification_list(Request $request)
    {
        try {
            if ($request->user_type == 'staff') {
                $validateRequest = [
                    'user_id' => 'required|exists:user,id',
                    'user_type' => 'required|string|in:child,staff',
                ];
            } else {
                $validateRequest = [
                    'user_id' => 'required|exists:service_user,id',
                    'user_type' => 'required|string|in:child,staff',
                ];
            }
            $validator = Validator::make($request->all(), $validateRequest);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'Data' => array()], 200);
            }
            $service_user_id = '';
            $notif_query = DB::table('notification as n')
                ->select('n.*')
                ->where('is_sticky', 0);
            if ($request->user_type == 'staff') {
                $notif_query = $notif_query->where('n.user_id', $request->user_id);
            } else {
                $notif_query = $notif_query->where('n.service_user_id', $request->user_id);
                $service_user_id = $request->user_id;
            }

            if (!empty($start_date)) {

                $start_date = date('Y-m-d', strtotime($start_date));
                $start_date = $start_date . ' 00:00:00';

                $notif_query = $notif_query->whereDate('n.created_at', '>=', $start_date);
            }

            if (!empty($end_date)) {

                $end_date = date('Y-m-d', strtotime('+1 day', strtotime($end_date)));
                $end_date = $end_date . ' 00:00:00';

                $notif_query = $notif_query->whereDate('n.created_at', '<', $end_date);
            }
            // if (!empty($limit)) {
            //     $notif_query = $notif_query->limit($limit);
            // }

            $notifications = $notif_query->where('status', 0)->orderBy('n.created_at', 'desc')->paginate(20);
            // return count($notifications);
            foreach ($notifications as $notification) {
                DB::table('notification')->where('id', $notification->id)->update(['status' => 1]);
                $created_at = $notification->created_at;
                $created_at1 = Carbon::parse($created_at);
                $diff = $created_at1->diffForHumans();
                /*$diff = $created_at1->diffForHumans();          //working
                $diff = $current->diffForHumans($created_at1);
                echo $diff; die;*/
                //echo '<pre>';print_r($notification);die;
                //If notification is for su list page then there should be su name.
                if (empty($service_user_id)) { //means show notifications for all

                    $su_name = ServiceUser::where('id', $notification->service_user_id)->value('name');
                    $su_id = ServiceUser::where('id', $notification->service_user_id)->value('id');
                    // echo $su_id; die;
                }
                if ($notification->notification_event_type_id == "2") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name   = ucfirst($su_name);
                        $list_msg_cntnt = "daily";
                    } else {
                        $event_name   = "Daily Record";
                        $list_msg_cntnt = '';
                    }
                    $tile_color = "alert alert-info";     //terques
                    $icon       = "fa fa-calendar";
                    if ($notification->event_action == "ADD") {
                        $dr_description = DB::table('su_daily_record')->select('dr.description')
                            ->join('daily_record as dr', 'dr.id', 'su_daily_record.daily_record_id')
                            ->where('su_daily_record.id', $notification->event_id)
                            ->first();

                        if (!empty($dr_description)) {
                            $dr_description = $dr_description->description;
                        } else {
                            //$dr_description = '';
                            // continue;
                        }
                        $message = "A new " . $list_msg_cntnt . " record '" . $dr_description . "' is added";
                    } else {
                        //edit case
                        $message = "Daily record all upto date";
                    }
                } else if ($notification->notification_event_type_id == "1") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'health';
                    } else {
                        $event_name = "Health Record";
                        $list_msg_cntnt = '';
                    }
                    //$event_name = "Health Record";
                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-heartbeat";
                    $hr_description = ServiceUserHealthRecord::where('id', $notification->event_id)->value('title');
                    if (empty($hr_description)) {
                        // continue;
                    }
                    if ($notification->event_action == "ADD") {
                        $message = "A new " . $list_msg_cntnt . " record '" . $hr_description . "' is added";
                    } else {
                        //edit case
                        $message = "A " . $list_msg_cntnt . " record '" . $hr_description . "' is edited";
                        // $message = "Health record all upto date";
                    }
                } else if ($notification->notification_event_type_id == "4") {
                    $event_name     = 'Placement Plan'; // ucfirst($su_name);
                    // if (isset($su_name)) {
                    //     $placement_url  = url('service/placement-plans/' . $su_id);
                    // } else {
                    //     $event_name     = "Placement Plan";
                    //     $placement_url  = '';
                    // }
                    // $event_name = "Placement Plan";
                    $tile_color = "alert alert-placement";
                    $icon       = "fa fa-map-marker";
                    $task       = ServiceUserPlacementPlan::where('id', $notification->event_id)->value('task');
                    if (empty($task)) {
                        // continue;
                    }
                    if ($notification->event_action == "ADD") {

                        $message = "A new Placement Plan '" . $task . "' is added";
                    } else if ($notification->event_action == "MARK_COMPLETE") {

                        $message = "Placement Plan '" . $task . "' is completed";
                    } else if ($notification->event_action == "MARK_ACTIVE") {

                        $message = "Placement Plan '" . $task . "' is made active";
                    } else {
                        $message = "Placement Plan all upto date";
                    }
                } elseif ($notification->notification_event_type_id == "3") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Earning Scheme';
                    } else {
                        $event_name = "Earning Scheme";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Earning Scheme";
                    $tile_color = "alert alert-earning";   //purple
                    $icon = "fa fa-star-half-o";
                    if ($notification->event_action == 'ADD_STAR') {
                        //$message     = "Star added for Daily records of ".date('d M Y',strtotime($created_at))." ";
                        $message     = $list_msg_cntnt . " 1 Star Added, Well done, add a new activity to your calendar!";
                    } elseif ($notification->event_action == 'REMOVE_STAR') {

                        $message     = $list_msg_cntnt . " 1 Star Removed, Not meeting your target, Management plan to be organized.";
                    } elseif ($notification->event_action == 'SPEND_STAR') {

                        $inventive_info = ServiceUserEarningIncentive::select('su_earning_incentive.star_cost', 'i.name', 'esc.title as category_name')
                            ->where('su_earning_incentive.id', $notification->event_id)
                            ->join('incentive as i', 'i.id', 'su_earning_incentive.incentive_id')
                            ->join('earning_scheme_category as esc', 'esc.id', 'i.earning_category_id')
                            ->first();
                        if (!empty($inventive_info)) {
                            if ($inventive_info->star_cost > 1) {
                                $star = $inventive_info->star_cost . " Stars";
                            } else {
                                $star = $inventive_info->star_cost . " Star";
                            }
                            $label       = $star . " Spend";
                            //$message     = $star." Spend for ".ucfirst($inventive_info->name)." ";
                            $message     = $list_msg_cntnt . ' ' . $label . ' ' . $inventive_info->category_name . " chosen, keep up the good work!";
                            //Star total now
                        } else {
                            // continue;
                        }
                    }
                }
                /*elseif ($notification->notification_event_type_id == "5") {

                        $event_name = "MFC/AFC";
                        $tile_color = "alert alert-info";     //terques
                        $icon       = "fa fa-user-times";

                        if($notification->event_action == "ADD"){
                            $mf_description = DB::table('su_mfc')->select('mf.description')
                                            ->join('mfc as mf','mf.id','su_mfc.mfc_id')
                                            ->where('su_mfc.id', $notification->event_id)
                                            ->first();

                            if(!empty($mf_description)){
                                $mf_description = $mf_description->description;
                            } else{
                                //$mf_description = '';
                                continue;
                            }
                            $message = "A new record '".$mf_description."' is added";

                        } else{
                            //edit case
                            $message = "MFC/AFC record all upto date";
                        }
                }*/ else if ($notification->notification_event_type_id == "5") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'MFC';
                    } else {
                        $event_name     = "MFC";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-info";
                    $icon       = "fa fa-user-times";

                    if ($notification->event_action == "ADD") {
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $mfc_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($mfc_description)) {
                            // continue;
                        }

                        $message = "A new " . $list_msg_cntnt . " record '" . $mfc_description . "' is added";
                    } else {
                        //edit case
                        $mfc_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($mfc_description)) {
                            // continue;
                        }
                        $message = "MFC record " . $mfc_description . " has been updated";
                    }
                } else if ($notification->notification_event_type_id == "6") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'living skill ';
                    } else {
                        $event_name = "Living Skill";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Living Skill";
                    $tile_color = "alert alert-info";     //terques
                    $icon       = "fa fa-child";

                    if ($notification->event_action == "ADD") {
                        $su_living_skill = DB::table('su_living_skill')->select('ls.description')
                            ->join('living_skill as ls', 'ls.id', 'su_living_skill.living_skill_id')
                            ->where('su_living_skill.id', $notification->event_id)
                            ->first();

                        if (!empty($su_living_skill)) {
                            $su_living_skill = $su_living_skill->description;
                        } else {
                            //$su_living_skill = '';
                            // continue;
                        }
                        $message = "A new " . $list_msg_cntnt . " record '" . $su_living_skill . "' is added";
                    } else {
                        //edit case
                        $message = "Living skill record all upto date";
                    }
                } else if ($notification->notification_event_type_id == "7") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'education/training ';
                    } else {
                        $event_name = "Education/Training";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Education/Training";
                    $tile_color = "alert alert-info";     //terques
                    $icon       = "fa fa-graduation-cap";

                    if ($notification->event_action == "ADD") {
                        $su_education_record = DB::table('su_education_record')->select('er.description')
                            ->join('education_record as er', 'er.id', 'su_education_record.education_record_id')
                            ->where('su_education_record.id', $notification->event_id)
                            ->first();

                        if (!empty($su_education_record)) {
                            $su_education_record = $su_education_record->description;
                        } else {
                            //$su_education_record = '';
                            // continue;
                        }
                        $message = "A new " . $list_msg_cntnt . "record '" . $su_education_record . "' is added";
                    } else {
                        //edit case
                        $message = "Education / Training record all upto date";
                    }
                } else if ($notification->notification_event_type_id == "8") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'BMP';
                    } else {
                        $event_name = "BMP";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "BMP";
                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-frown-o";

                    if ($notification->event_action == "ADD") {
                        /*$bmp_description = ServiceUserBmp::where('id', $notification->event_id)->value('title');
                        if(empty($bmp_description)){
                            continue;
                        }*/

                        $description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($description)) {
                            // continue;
                        }

                        $message = "A new " . $list_msg_cntnt . " record '" . $description . ' ' . $notification->event_id . "' is added";
                    } else {
                        //edit case
                        $description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($description)) {
                            // continue;
                        }
                        $message = "BMP record " . $description . " has been updated";
                    }
                } else if ($notification->notification_event_type_id == "9") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'RMP';
                    } else {
                        $event_name     = "RMP";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-meh-o";

                    if ($notification->event_action == "ADD") {
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $rmp_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($rmp_description)) {
                            // continue;
                        }

                        $message = "A new " . $list_msg_cntnt . " record '" . $rmp_description . "' is added";
                    } else {
                        //edit case
                        $rmp_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($rmp_description)) {
                            // continue;
                        }
                        $message = "RMP record " . $rmp_description . " has been updated";
                    }
                } else if ($notification->notification_event_type_id == "10") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Incident Report';
                    } else {
                        $event_name = "Incident Report";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "Incident Report";
                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-bolt";

                    if ($notification->event_action == "ADD") {
                        /*$incident_report = ServiceUserIncidentReport::where('id', $notification->event_id)->value('title');
                        if(empty($incident_report)){
                            continue;
                        }*/

                        $description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($description)) {
                            // continue;
                        }
                        $message = "A new " . $list_msg_cntnt . " record '" . $description . "' is added";
                    } else {
                        //edit case
                        $description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($description)) {
                            // continue;
                        }
                        $message = "An " . $list_msg_cntnt . " record '" . $description . "' is edited";

                        // $message = "Incident Reports " . $description . " has been updated";
                    }
                } else if ($notification->notification_event_type_id == "11") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Risk Change';
                    } else {
                        $event_name = 'Risk Change';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation-triangle";
                    if ($notification->event_action == "ADD") {
                        $risk_change = DB::table('su_risk')->select('su_risk.status', 'r.description', 'r.icon')
                            ->join('risk as r', 'r.id', 'su_risk.risk_id')
                            ->where('su_risk.id', $notification->event_id)
                            ->first();
                        // echo "<pre>"; print_r($risk_change); die;
                        if (!empty($risk_change)) {
                            if ($risk_change->status == 2) {
                                $risk_type = "Live Risk";
                            } elseif ($risk_change->status == 1) {
                                $risk_type = "Historic Risk";
                            } else {
                                $risk_type = "No Risk";
                            }
                            $risk_description = $risk_change->description;
                            $icon = $risk_change->icon;
                            $message = "Risk " . $risk_description . " has changed to " . $risk_type;
                        } else {
                            // continue;
                        }
                        // $message = "Risk " . $risk_description . " has changed to " . $risk_type;
                    }
                } else if ($notification->notification_event_type_id == "12") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Form';
                    } else {
                        $event_name     = "Form";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    $tile_color = "alert alert-info";
                    $icon       = "fa fa-bolt";

                    if ($notification->event_action == "ADD") {
                        //$rmp_description = ServiceUserRmp::where('id', $notification->event_id)->value('title');
                        //$rmp_id = ServiceUserRisk::where('id', $notification->event_id)->value('rmp_id');
                        $form_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($form_description)) {
                            // continue;
                        }

                        $message = "A new " . $list_msg_cntnt . " form '" . $form_description . "' is added";
                    } else {
                        //edit case
                        $form_description = DynamicForm::where('id', $notification->event_id)->value('title');
                        if (empty($form_description)) {
                            // continue;
                        }
                        $message = "Form record " . $form_description . " has been updated";
                    }
                } else if ($notification->notification_event_type_id == "13") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'AFC Status';
                    } else {
                        $event_name     = "AFC Status";
                        $list_msg_cntnt = '';
                    }
                    // $event_name = "RMP";
                    // $tile_color = "alert alert-info";
                    $icon       = "fa fa-male";

                    if ($notification->event_action == "ADD") {

                        //$afc_status = ServiceUserAFC::where('id',$notification->event_id)->value('afc_status');

                        $afc = ServiceUserAFC::select('afc_status', 'created_at')->where('id', $notification->event_id)->first();
                        $afc_status     = $afc->afc_status;
                        $afc_created_at = $afc->created_at;

                        if ($afc_status == '1') {
                            $content = "Came in home";
                            $tile_color = "alert alert-success";
                        } else if ($afc_status == '0') {
                            $content = "Came out home";
                            $tile_color = "alert alert-danger";
                        } else {
                            $tile_color = "alert alert-info";
                            // continue;
                        }

                        $message = $content . ' on ' . date('d-m-Y H:i', strtotime($afc_created_at));
                    } else {
                        //edit case
                        $afc_status = ServiceUserAFC::where('id', $notification->event_id)->value('afc_status');
                        if (empty($afc_status)) {
                            // continue;
                        }
                        $message = "AFC status";
                    }
                } else if ($notification->notification_event_type_id == "14") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'In danger';
                    } else {
                        $event_name = 'In danger';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation-triangle";
                    if ($notification->event_action == "ADD") {
                        $in_danger = DB::table('su_care_center')->select('su_care_center.created_at', 'su.name')
                            ->join('service_user as su', 'su.id', 'su_care_center.service_user_id')
                            ->where('su_care_center.care_type', 'D')
                            ->where('su_care_center.id', $notification->event_id)
                            ->first();

                        // echo "<pre>"; print_r($risk_title); die;
                        if (!empty($in_danger)) {
                            $in_danger_created_at = $in_danger->created_at;
                            $content = $in_danger->name . " is in danger.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                        //$message = $content.' on '.date('d-m-Y H:i',strtotime($in_danger_created_at));
                    }
                } else if ($notification->notification_event_type_id == "15") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Request Callback';
                    } else {
                        $event_name = 'Request Callback';
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-info";
                    $icon       = "fa fa-phone";
                    if ($notification->event_action == "ADD") {
                        $req_call_bk = DB::table('su_care_center')->select('su_care_center.created_at', 'su.name')
                            ->join('service_user as su', 'su.id', 'su_care_center.service_user_id')
                            ->where('su_care_center.care_type', 'R')
                            ->where('su_care_center.id', $notification->event_id)
                            ->first();

                        // echo "<pre>"; print_r($risk_title); die;
                        if (!empty($req_call_bk)) {
                            $req_call_bk_created_at = $req_call_bk->created_at;
                            $content = $req_call_bk->name . " has requested to callback.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                        // $message = $content.' on '.date('d-m-Y H:i',strtotime($req_call_bk_created_at));
                    }
                } else if ($notification->notification_event_type_id == "16") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Need Assistance';
                    } else {
                        $event_name = "Need Assistance";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation";

                    if ($notification->event_action == "ADD") {

                        $assistance = DB::table('su_need_assistance')->select('su_need_assistance.created_at', 'su_need_assistance.message', 'su.name')
                            ->join('service_user as su', 'su.id', 'su_need_assistance.service_user_id')
                            ->where('su_need_assistance.id', $notification->event_id)
                            ->first();
                        if (!empty($assistance)) {
                            $assistance_created_at = $assistance->created_at;
                            $content = $assistance->name . " has made need assistance request.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                        // $message = "Need assistance '".$description."' is added";
                    }
                } else if ($notification->notification_event_type_id == "17") {
                    // continue;

                    if ($notification->event_action == 'ADD') {

                        $su_loc_notif = ServiceUserLocationNotification::select('id', 'service_user_id', 'location_name', 'location_type', 'old_location_type')
                            ->where('id', $notification->event_id)
                            ->first();

                        if (!empty($su_loc_notif)) {
                            $old_loc = $su_loc_notif->old_location_type;
                            $new_loc = $su_loc_notif->location_type;

                            if ($old_loc == 'A') {
                                $old_loc_txt = 'allowed';
                            } else if ($old_loc == 'R') {
                                $old_loc_txt = 'restricted';
                            } else {
                                $old_loc_txt = 'grey';
                            }

                            if ($new_loc == 'A') {
                                $new_loc_txt = 'allowed';
                            } else if ($new_loc == 'R') {
                                $new_loc_txt = 'restricted';
                            } else {
                                $new_loc_txt = 'grey';
                            }
                            $list_msg_cntnt   = "Location alert";
                            $s_user_name      = ServiceUser::where('id', $su_loc_notif->service_user_id)->value('name');

                            $message = ucfirst($s_user_name) . " has entered into " . $new_loc_txt . " area from the " . $old_loc_txt . " area " . $created_at . ".";

                            if (isset($su_name)) {
                                $event_name = ucfirst($su_name);
                                $list_msg_cntnt = 'Location alert';
                            } else {
                                $event_name = "Location alert";
                                $list_msg_cntnt = '';
                            }

                            $tile_color = "alert alert-health";
                            $icon       = "fa fa-exclamation";
                        } else {
                            // continue;
                        }
                    }

                    //default
                    /*$tile_color = "alert alert-health";
                    $icon       = "fa fa-exclamation";

                    if($notification->event_action == "ADD"){

                        $assistance = DB::table('su_need_assistance')->select('su_need_assistance.created_at','su_need_assistance.message','su.name')
                                        ->join('service_user as su','su.id','su_need_assistance.service_user_id')
                                        ->where('su_need_assistance.id',$notification->event_id)
                                        ->first();
                        if(!empty($assistance)) {
                            $assistance_created_at = $assistance->created_at;
                            $content = $assistance->name." has made need assistance request.";
                            $message = $content;
                        } else {
                            continue;
                        }
                        // $message = "Need assistance '".$description."' is added";
                    }*/
                } else if ($notification->notification_event_type_id == "18") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Money Request';
                    } else {
                        $event_name = "Money Request";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-credit-card";

                    if ($notification->event_action == "ADD") {

                        $money_req = DB::table('su_money_request')->select('su_money_request.created_at', 'su_money_request.amount', 'su.name')
                            ->join('service_user as su', 'su.id', 'su_money_request.service_user_id')
                            ->where('su_money_request.id', $notification->event_id)
                            ->first();
                        if (!empty($money_req)) {
                            $money_req_created_at = $money_req->amount;
                            $content              = $money_req->name . " has made money request for €" . $money_req->amount;
                            $message = $content;
                        } else {
                            // continue;
                        }
                        // $message = "Need money_req '".$description."' is added";
                    }
                } else if ($notification->notification_event_type_id == "19") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name = ucfirst($su_name);
                        $list_msg_cntnt = 'Appointment Event';
                    } else {
                        $event_name = "Appointment Event";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-map-marker";

                    if ($notification->event_action == "ADD") {

                        $event_req  = DB::table('su_calendar_event')->select('su_calendar_event.created_at', 'su_calendar_event.title', 'su.name')
                            ->join('service_user as su', 'su.id', 'su_calendar_event.service_user_id')
                            ->where('su_calendar_event.id', $notification->event_id)
                            ->first();
                        if (!empty($event_req)) {
                            $event_req_title = $event_req->title;
                            $content = "A new " . $event_req_title . " appointment is added";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    }
                } else if ($notification->notification_event_type_id == "20") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'Event Change Request';
                    } else {
                        $event_name = "Event Change Request";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-calendar-o";

                    if ($notification->event_action == "ADD") {

                        $event_chng_req  = DB::table('event_change_request')->select('event_change_request.new_date', 'event_change_request.calendar_id')
                            ->where('event_change_request.id', $notification->event_id)
                            ->first();
                        if (!empty($event_chng_req)) {
                            $evt_req_new_date = date('m-d-y', strtotime($event_chng_req->new_date));
                            $content = "A new date " . $evt_req_new_date . " for event change request is added.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    }
                } else if ($notification->notification_event_type_id == "21") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'New Mood added';
                    } else {
                        $event_name = $notification->event_action == "ADD" ? "New Mood Added" : "New Mood Edited";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-smile-o";

                    if ($notification->event_action == "ADD") {

                        $su_mood_info  = DB::table('su_mood')
                            ->select('su_mood.description', 'm.name')
                            ->join('mood as m', 'm.id', 'su_mood.mood_id')
                            ->where('su_mood.id', $notification->event_id)
                            ->first();
                        if (!empty($su_mood_info)) {
                            $mood_title = $su_mood_info->name;
                            $content = "A " . $mood_title . " is added.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    } elseif ($notification->event_action == "EDIT") {
                        $su_mood_info  = DB::table('su_mood')
                            ->select('su_mood.description', 'm.name')
                            ->join('mood as m', 'm.id', 'su_mood.mood_id')
                            ->where('su_mood.id', $notification->event_id)
                            ->first();
                        if (!empty($su_mood_info)) {
                            $mood_title = $su_mood_info->name;
                            $content = "A " . $mood_title . " is edited.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    }
                } else if ($notification->notification_event_type_id == "22") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'New Behavior added';
                    } else {
                        $event_name = $notification->event_action == "ADD" ? "New Behavior Added" : "New Behavior Edited";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-health";
                    $icon       = "fa fa-star-o";

                    if ($notification->event_action == "ADD") {

                        $su_mood_info  = DB::table('su_behavior')
                            ->select('su_behavior.description', 'su_behavior.rate')
                            // ->join('mood as m', 'm.id', 'su_behavior.mood_id')
                            ->where('su_behavior.id', $notification->event_id)
                            ->first();
                        if (!empty($su_mood_info)) {
                            $mood_title = $su_mood_info->rate;
                            // $content = "A " . $mood_title . " is added.";
                            $content = "'{$mood_title} Star' is added.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    } elseif ($notification->event_action == "EDIT") {
                        $su_mood_info  = DB::table('su_behavior')
                            ->select('su_behavior.description', 'su_behavior.rate')
                            // ->join('mood as m', 'm.id', 'su_mood.mood_id')
                            ->where('su_behavior.id', $notification->event_id)
                            ->first();
                        if (!empty($su_mood_info)) {
                            $mood_title = $su_mood_info->rate;
                            $content = "'{$mood_title} Star' is edited.";
                            // $content = "A " . $mood_title . " is edited.";
                            $message = $content;
                        } else {
                            // continue;
                        }
                    }
                } else if ($notification->notification_event_type_id == "23") {
                    // continue;
                    if (isset($su_name)) {
                        $event_name     = ucfirst($su_name);
                        $list_msg_cntnt = 'New Daily Log Added';
                    } else {
                        echo 1;
                        $event_name = $notification->event_action == "ADD" ? "New Daily Log Added" : "New Daily Log Edited";
                        $list_msg_cntnt = '';
                    }

                    $tile_color = "alert alert-success";
                    $icon       = "fa fa-address-book";

                    $su_mood_info  = DB::table('su_log_book')
                        ->select('su_log_book.id', 'lb.title', 'lb.user_id', 'lb.category_name')
                        ->join('log_book as lb', 'lb.id', 'su_log_book.log_book_id')
                        ->where('su_log_book.id', $notification->event_id)
                        ->first();

                    if (empty($su_mood_info)) {
                        // continue;
                    } else {
                        $mood_title = $su_mood_info->title;
                        $content = $notification->event_action == "ADD" ? "'{$mood_title}' daily log is added." :
                            "'{$mood_title}' daily log is edited.";
                        $message = $content;
                    }
                    // if ($notification->event_action == "ADD") {
                    //     if (!empty($su_mood_info)) {
                    //         $mood_title = $su_mood_info->title;
                    //         // $content = "A " . $mood_title . " is added.";
                    //         $content = "'{$mood_title}' daily log is added.";
                    //         $message = $content;
                    //     } else {
                    //         continue;
                    //     }
                    // } elseif ($notification->event_action == "EDIT") {
                    //     if (!empty($su_mood_info)) {
                    //         $mood_title = $su_mood_info->rate;
                    //         $content = "'{$mood_title} Star' is edited.";
                    //         // $content = "A " . $mood_title . " is edited.";
                    //         $message = $content;
                    //     } else {
                    //         continue;
                    //     }
                    // }
                } else {
                    continue;
                }
                if ($tile_color == 'alert alert-placement') {
                } else {
                }

                $ar[] = [
                    'id' => $notification->id,
                    'notification_event_type_id' => $notification->notification_event_type_id,
                    'event_name' => $event_name,
                    'message' => $message,
                    'created_at' => Carbon::parse($notification->created_at)->diffForHumans()
                ];
            }

            return response()->json([
                'status' => true,
                'message' => "Notification list",
                'data' => $ar ?? [],
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'last_page_url'   => $notifications->url($notifications->lastPage()),
                'next_page_url'   => $notifications->nextPageUrl(),
                'prev_page_url'   => $notifications->previousPageUrl(),
                'current_page_url' => $notifications->url($notifications->currentPage()),
                // 'data' => $notifications->items(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.:' . $e->getMessage()
            ]);
        }
    }
    public function notification_count(Request $request)
    {
        try {
            if ($request->user_type == 'staff') {
                $validateRequest = [
                    'user_id' => 'required|exists:user,id',
                    'user_type' => 'required|string|in:child,staff',
                ];
            } else {
                $validateRequest = [
                    'user_id' => 'required|exists:service_user,id',
                    'user_type' => 'required|string|in:child,staff',
                ];
            }
            $validator = Validator::make($request->all(), $validateRequest);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'Data' => array()], 200);
            }
            $service_user_id = '';
            $notif_query = DB::table('notification as n')
                ->select('n.*')
                ->where('is_sticky', 0)
                ->where('status', 0);
            if ($request->user_type == 'staff') {
                $notif_query = $notif_query->where('n.user_id', $request->user_id);
            } else {
                $notif_query = $notif_query->where('n.service_user_id', $request->user_id);
                $service_user_id = $request->user_id;
            }

            if (!empty($start_date)) {

                $start_date = date('Y-m-d', strtotime($start_date));
                $start_date = $start_date . ' 00:00:00';

                $notif_query = $notif_query->whereDate('n.created_at', '>=', $start_date);
            }

            if (!empty($end_date)) {

                $end_date = date('Y-m-d', strtotime('+1 day', strtotime($end_date)));
                $end_date = $end_date . ' 00:00:00';

                $notif_query = $notif_query->whereDate('n.created_at', '<', $end_date);
            }
            // if (!empty($limit)) {
            //     $notif_query = $notif_query->limit($limit);
            // }

            $notifications = $notif_query->count();
            return response()->json([
                'status' => true,
                'message' => "Notification Count",
                'count' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.:' . $e->getMessage()
            ]);
        }
    }
    public function staffDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|integer|exists:user,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'data'    => json_decode('{}')
            ]);
        }
        $staffDetails = User::select('user.id as user_id', 'user.image', 'user.home_id', 'user.name', 'user.user_name', 'user.email', 'user.job_title', 'user.is_deleted', 'user.phone_no', 'user.current_location', 'user.available_for_overtime', 'user.max_extra_hours', 'user.employment_type', 'user.dbs_certificate_number', 'user.dbs_expiry_date', 'user.hourly_rate')
            ->where('user.id', $request->user_id)
            ->where('user.is_deleted', 0)
            ->first();

        if (!$staffDetails) {
            return response()->json([
                'status'  => false,
                'message' => 'Staff not found',
                'data'    => json_decode('{}')
            ]);
        }
        $staffDetails->emergencyContact = UserEmergencyContact::select('user_emergency_contacts.id as emc_id', 'user_emergency_contacts.name as emc_name', 'user_emergency_contacts.phone_no as emc_phone_no', 'user_emergency_contacts.relationship', 'user_emergency_contacts.email', 'user_emergency_contacts.user_id as emc_user_id')->where('user_emergency_contacts.user_id', $request->user_id)->first() ?? "";
        // $staffDetails->qualifications   = UserQualification::where('user_id', $request->user_id)->get();
        $data['personal_details'] = [
            'id' => $staffDetails->user_id,
            'home_id' => $staffDetails->home_id,
            'name' => $staffDetails->name,
            'user_name' => $staffDetails->user_name,
            'email' => $staffDetails->email,
            'job_title' => $staffDetails->job_title,
            'phone_no' => $staffDetails->phone_no,
            'address' => $staffDetails->current_location,
            'available_for_overtime' => ($staffDetails->available_for_overtime == 1) ? 'Yes' : 'No',
            'max_extra_hours' => $staffDetails->max_extra_hours ?? 0,
            'employment_type' => $staffDetails->employment_type ?? "",
            'dbs_certificate_number' => $staffDetails->dbs_certificate_number ?? "",
            'dbs_expiry_date' => $staffDetails->dbs_expiry_date ?? "",
            'hourly_rate' => $staffDetails->hourly_rate ?? 0,
            'image' => url('public/images/userProfileImages/') . '/' . $staffDetails->image,
        ];
        // $data['emergency_contact'] = json_decode('{}');
        // if ($staffDetails->emergencyContact) {
        //     $data['emergency_contact'] = [
        //         'id' => $staffDetails->emergencyContact->emc_id,
        //         'emc_name' => $staffDetails->emergencyContact->emc_name ?? "",
        //         'emc_phone_no' => $staffDetails->emergencyContact->emc_phone_no ?? "",
        //         'relationship' => $staffDetails->emergencyContact->relationship ?? "",
        //         'email' => $staffDetails->emergencyContact->email ?? "",
        //         'emc_user_id' => $staffDetails->emergencyContact->emc_user_id
        //     ];
        // }

        $data['emergency_contact'] = [
            'id' => '',
            'emc_name' => '',
            'emc_phone_no' => '',
            'relationship' => '',
            'email' => '',
            'emc_user_id' => ''
        ];

        if ($staffDetails->emergencyContact) {
            $emc = $staffDetails->emergencyContact;

            $data['emergency_contact'] = [
                'id' => (string) ($emc->emc_id ?? ''),
                'emc_name' => (string) ($emc->emc_name ?? ''),
                'emc_phone_no' => (string) ($emc->emc_phone_no ?? ''),
                'relationship' => (string) ($emc->relationship ?? ''),
                'email' => (string) ($emc->email ?? ''),
                'emc_user_id' => (string) ($emc->emc_user_id ?? '')
            ];
        }
        return response()->json([
            'status'  => true,
            'message' => 'Staff Personal Details',
            'data'    => $data
        ]);
    }
}
