<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use illuminate\Http\Request;
use App\User, App\ServiceUser, App\ServiceUserCareCenter, App\OfficeMessage, App\ServiceUserNeedAssistance, App\ServiceUserEarningStar, App\PettyCash, App\EarningScheme, App\HomeLabel, App\ServiceUserMFC, App\ServiceUserRisk, App\Risk, App\Mood, App\AgendaMeeting, App\Training, App\StaffTraining,App\ServiceUserMood, App\ServiceUserMoney, App\StaffSickLeave, App\StaffAnnualLeave, App\DynamicForm;
use Auth, DB;
use Illuminate\Support\Facades\Log;

class ViewReportController extends Controller
{
	  
	public function index() {
		
		$page = 'index';
		$guide_tag = 'sys_mngmt';
		// $home_id = Auth::user()->home_id;
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];

		$su_in_danger = ServiceUserCareCenter::join('service_user as su','su.id','su_care_center.service_user_id')
												->where('su.home_id', $home_id)
												->where('care_type','D')
												->count();

    	$su_req_cb    = ServiceUserCareCenter::join('service_user as su','su.id','su_care_center.service_user_id')
												->where('su.home_id', $home_id)
												->where('care_type','R')
												->count();

		$off_mesg     = OfficeMessage::where('home_id', $home_id)->where('order','0')->count();

		$need_asitnce = ServiceUserNeedAssistance::where('home_id', $home_id)->count();

		$star_count   = ServiceUserEarningStar::select('star')
												->join('service_user as su','su.id','su_earning_star.service_user_id')
												->where('su.home_id', $home_id)
												->get()
												->toArray();
		$stars=0;
		foreach ($star_count as $key => $star) {
			$stars += $star['star'];
		}
		// echo $stars; die;

		$petty_cash_deposit = PettyCash::where('home_id', $home_id)->where('txn_type','D')->orderBy('petty_cash.created_at','desc')->value('balance');
		$petty_cash_withdraw = PettyCash::where('home_id', $home_id)->where('txn_type','W')->orderBy('petty_cash.created_at','desc')->value('balance');
		$expenditure = $petty_cash_deposit - $petty_cash_withdraw;
		// echo "<pre>"; print_r($expenditure); die;

		$no_risk = ServiceUserRisk::where('home_id', $home_id)->where('status','0')->count();
		$historic_risk = ServiceUserRisk::where('home_id', $home_id)->where('status','1')->count();
		$live_risk = ServiceUserRisk::where('home_id', $home_id)->where('status','2')->count();

		$total_weekly_allowance = 0;
		$total_curnt_bal = 0;
		$service_users = ServiceUser::select('id','name')->where('home_id', $home_id)->where('is_deleted','0')->get()->toArray();
		foreach ($service_users as $key => $su) {
			$su_last_allowance 	= ServiceUserMoney::where('service_user_id',$su['id'])
								->where('txn_type','D')
								->orderBy('id','desc')
								->value('balance');
			$total_weekly_allowance = $total_weekly_allowance + $su_last_allowance;

			$su_curnt_bal 		= ServiceUserMoney::where('service_user_id',$su['id'])
								->orderBy('id','desc')
								->value('balance');
			$total_curnt_bal = $total_curnt_bal + $su_curnt_bal;  
		}
		//echo 'total_weekly_allowance= '.$total_weekly_allowance."<br>";
		//echo 'total_curnt_bal = '.$total_curnt_bal."<br>";
		// $total_curnt_bal
		$spent = (float)$total_weekly_allowance - (float)$total_curnt_bal;

		$incident_report = DynamicForm::countIncidentReport('','','');

		// echo "<pre>"; print_r($record_score); die;
		$count = array();
		$count['in_danger']      = $su_in_danger;
		$count['req_cb']         = $su_req_cb;
		$count['off_mesg']       = $off_mesg;
		$count['assistance']     = $need_asitnce;
		$count['star']           = $stars;
		$count['cash_deposit']   = $petty_cash_deposit;
		$count['cash_withdraw']  = $petty_cash_withdraw;
		$count['expenditure']    = $expenditure;
		$count['no_risk']		 = $no_risk;
		$count['historic_risk']  = $historic_risk;
		$count['live_risk']		 = $live_risk;
		$count['spent']          = $spent;
		$count['current_bal']    = $total_curnt_bal;
		$count['weekly_allowance']    = $total_weekly_allowance;
		$count['incident']       = $incident_report;
		// $count['record_score']   = $record_score;
		// echo "<pre>"; print_r($count); die;

		$record_score = EarningScheme::getRecordsScore('');
		$labels       = HomeLabel::getLabels();
		$service_user = ServiceUser::where('is_deleted','0')->where('home_id', $home_id)->get();

		$j = 0;
		for($i = 6; $i >= 0; $i--) {
			$week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day'));

			$mfc = ServiceUserMFC::where('su_mfc.is_deleted','0')->where('updated_at','=', $week_date)->first();
			if(!empty($mfc)) {
				$week_graph[$j]['point'] = $mfc->id;
			} else {
				// echo "1"; die;
				$week_graph[$j]['point'] = 0;
			}

			$week_graph[$j]['date'] = date('d/m', strtotime($week_date));
			$j++;
		}

		$from_date = '';
		$to_date   = '';	//date('d-m-Y');
		
		$service_users = ServiceUser::select('id','name')->where('home_id', $home_id)->where('is_deleted','0')->get()->toArray();
		foreach ($service_users as $key => $su) {
			$status = Risk::overallRiskStatus($su['id']);
			$service_users[$key]['status'] = $status;
		}
		// echo "<pre>"; print_r($service_users); die;

		$risk_count['no_risk'] 	= 0;
		$risk_count['historic'] = 0;
		$risk_count['live'] 	= 0;

		foreach ($service_users as $key => $su) {
			$status = $su['status'];
			if($status == 0) {
				$risk_count['no_risk'] = 1 + $risk_count['no_risk'];
			} else if($status == 1) {
				$risk_count['historic'] = 1 + $risk_count['historic'];
			} else if($status == 2) {
				$risk_count['live'] = 1 + $risk_count['live'];
			}
		}
		// echo "<pre>"; print_r($risk_count); die;
		
		$moods = Mood::where('is_deleted','0')->where('home_id', $home_id)->where('status','0')->get()->toArray();
		$su_moods = ServiceUserMood::select('mood.value as mood_value','mood.name as mood_name')
												->join('mood','mood.id','su_mood.mood_id')
												->where('su_mood.home_id', $home_id)
												->where('su_mood.is_deleted','0')
												->get()->toArray();
												//->where('su_mood.service_user_id', $data['select_user_id']);

		$last_mood = 0;
		$j = 0;
		for($i = 6; $i >= 0; $i--) {
			$week_date = date('Y-m-d', strtotime('-'.$i.'day'));
			// $week_date = date('Y-m-d',strtotime('-'.$i.'day', strtotime($chart_start_date)));
			$su_mood_query = ServiceUserMood::select('mood.value as mood_value','mood.name as mood_name','su_mood.created_at')
										->join('mood','mood.id','su_mood.mood_id')
										->where('su_mood.home_id', $home_id)
										->where('su_mood.is_deleted','0')
										->where('su_mood.created_at','LIKE',$week_date.'%');

			if((!empty($from_date)) && (!empty($to_date))) {
				$su_mood_query = $su_mood_query->where('su_mood.created_at', '>=', $from_date)->where('su_mood.created_at', '<=', $to_date);
			}
			$su_mood = $su_mood_query->first();
			//echo "<pre>"; print_r($su_mood); die;
			if(!empty($su_mood)) {
				$mood_graph[$j]['mood_value'] = $su_mood->mood_value;
				$last_mood = $su_mood->mood_value;
			} else {
				// echo "1"; die;
				$mood_graph[$j]['mood_value'] = $last_mood;
			}
			$mood_graph[$j]['date'] = date('d/m', strtotime($week_date));
			$j++;
		}
		// echo "<pre>"; print_r($mood_graph); die;

		
		return view('frontEnd.viewReports.index',compact('page','guide_tag','count','record_score','labels','service_user','week_graph','from_date','to_date','risk_count','moods','su_moods','mood_graph'));
	}

    // public function get_user($user_type_id = null) {
	public function get_user(Request $request, $user_type_id) {

		// $home_id      = Auth::user()->home_id;
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		// $user_type_id = $request->user_type_id;
		
		if($user_type_id == 'STAFF') {
			$user_query = User::where('home_id', $home_id)
							->where('is_deleted','0')
							->get()
							->toArray();
			foreach ($user_query as $query) {
				echo "<option value=".$query['id'].">".$query['name']."</option>";
			}
		} else if($user_type_id == 'SERVICE_USER') {
			$yp_query = ServiceUser::where('home_id', $home_id)
											->where('is_deleted','0')
											->get()
											->toArray();
			foreach ($yp_query as $query) {
				echo "<option value=".$query['id'].">".$query['name']."</option>";
			}
		} else {
			// continue;
		}
	}


}
