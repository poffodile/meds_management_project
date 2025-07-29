<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use illuminate\Http\Request;
use App\User, App\ServiceUser, App\ServiceUserCareCenter, App\OfficeMessage, App\ServiceUserNeedAssistance, App\ServiceUserEarningStar, App\PettyCash, App\EarningScheme, App\HomeLabel, App\ServiceUserMFC, App\ServiceUserRisk, App\Risk, App\Mood, App\AgendaMeeting, App\Training, App\StaffTraining,App\ServiceUserMood, App\ServiceUserMoney, App\StaffSickLeave, App\StaffAnnualLeave, App\DynamicForm;
use Auth, DB;

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
	public function get_user(Request $request) {

		// $home_id      = Auth::user()->home_id;
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		$user_type_id = $request->user_type_id;
		
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


	public function record(Request $request) {
		
		$page = 'index';
		$guide_tag = 'sys_mngmt';

		$data = $request->all();
		// echo "<pre>"; print_r($data); die;
		$from_date = '';
		if(!empty($data['from_date'])){
			$from_date = date('Y-m-d',strtotime($data['from_date']));
		}
		$to_date = '';
		if(!empty($data['to_date'])){
			$to_date = date('Y-m-d',strtotime($data['to_date']));
		}
		// $from_date = date('Y-m-d',strtotime($data['from_date']));
		// $to_date   = date('Y-m-d',strtotime($data['to_date']));
		// echo $from_date."<br>".$to_date; die;	
		// if(empty($report_type)) {
		// 	$fun_call = $this->index();
		// 	echo $fun_call;
		// }
		$report_type = $data['report_type'];
		$user_type   = $data['user_type'];
		// $home_id = Auth::user()->home_id;
		$home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id=$ex_home_ids[0];
		if($data['user_type'] == 'SERVICE_USER') {
			if($data['report_type'] == 'INDIVIDUAL') {
				$select_user = $data['select_user_id'];
	
				// $su_in_danger = ServiceUserCareCenter::where('service_user_id', $data['select_user_id'])->where('care_type','D')->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date)->count();

				$su_in_danger_query = ServiceUserCareCenter::where('service_user_id', $data['select_user_id'])->where('care_type','D');
				if((!empty($from_date)) && (!empty($to_date))) {
					$su_in_danger_query = $su_in_danger_query->where('created_at', '>=', $from_date)
											->where('created_at','<=', $to_date);
				}
				$su_in_danger = $su_in_danger_query->count();

            	$su_req_cb_query    = ServiceUserCareCenter::where('service_user_id', $data['select_user_id'])->where('care_type','R');
            	if((!empty($from_date)) && (!empty($to_date))) {
  			          $su_req_cb_query =	$su_req_cb_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);		
            	}
            	$su_req_cb = $su_req_cb_query->count();
        		
        		$off_mesg_query = OfficeMessage::where('service_user_id',$data['select_user_id'])->where('home_id', $home_id)->where('order','0');
        		if((!empty($from_date)) && (!empty($to_date))) {
        				$off_mesg_query	= $off_mesg_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
        		}
        		$off_mesg = $off_mesg_query->count();
        		
        		$need_asitnce_query = ServiceUserNeedAssistance::where('service_user_id',$data['select_user_id'])->where('home_id', $home_id);
        		if((!empty($from_date)) && (!empty($to_date))) {
        			$need_asitnce_query = $need_asitnce_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
        		}
        		$need_asitnce = $need_asitnce_query->count();
        		
        		$total_stars_query  = ServiceUserEarningStar::where('service_user_id', $data['select_user_id']);
        		if((!empty($from_date)) && (!empty($to_date))) {
        			$total_stars_query = $total_stars_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
        		}
        		$total_stars = $total_stars_query->value('star');

        		$petty_cash_deposit_query = PettyCash::where('home_id', $home_id)->where('txn_type','D')->orderBy('petty_cash.created_at','desc');
        		if((!empty($from_date)) && (!empty($to_date))) {
        			$petty_cash_deposit_query = $petty_cash_deposit_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
        		}
        			$petty_cash_deposit = $petty_cash_deposit_query->value('balance');
				$petty_cash_withdraw_query = PettyCash::where('home_id', $home_id)->where('txn_type','W')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
					$petty_cash_withdraw_query = $petty_cash_withdraw_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$petty_cash_withdraw = $petty_cash_withdraw_query->value('balance');
				$expenditure = $petty_cash_deposit - $petty_cash_withdraw;

				$su_last_allowance 	= ServiceUserMoney::where('service_user_id',$data['select_user_id'])
														->where('txn_type','D')
														->orderBy('id','desc')
														->value('balance');

				$su_curnt_bal 		= ServiceUserMoney::where('service_user_id',$data['select_user_id'])
														->orderBy('id','desc')
														->value('balance');
				$su_curnt_bal = (float)$su_curnt_bal;
				$spent = (float)$su_last_allowance - (float)$su_curnt_bal;

				// echo 'select_user_id'.$data['select_user_id'];
				// echo 'su_last_allowance'.$su_last_allowance; 
				// echo 'su_curnt_bal'.$su_curnt_bal; 
				// echo '$spent'.$spent; die;

				$no_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('service_user_id', $data['select_user_id'])->where('status','0');
				if((!empty($from_date)) && (!empty($to_date))) {
					$no_risk_query = $no_risk_query->where('created_at','>=', $from_date)->where('created_at','<=', $to_date);
				}
				$no_risk = $no_risk_query->count();

				$historic_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('service_user_id', $data['select_user_id'])->where('status','1');
				if((!empty($from_date)) && (!empty($to_date))) {
					$historic_risk_query = $historic_risk_query->where('created_at','>=', $from_date)->where('created_at','<=', $to_date);
				}
				$historic_risk = $historic_risk_query->count();

				$live_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('service_user_id', $data['select_user_id'])->where('status','2');
				if((!empty($from_date)) && (!empty($to_date))) {
					$live_risk_query = $live_risk_query->where('created_at','>=', $from_date)->where('created_at','<=', $to_date);
				}
				$live_risk = $live_risk_query->count();
				
				$service_user_id = $data['select_user_id'];
				if((!empty($from_date)) && (!empty($to_date))) {
					$incident_report = DynamicForm::countIncidentReport($service_user_id,$from_date,$to_date);
				} else {
					$incident_report = DynamicForm::countIncidentReport($service_user_id,'','');
				}
				//echo $incident_report; die;

        		$count = array();
        		$count['in_danger']  	 = $su_in_danger;
        		$count['req_cb']     	 = $su_req_cb;
        		$count['off_mesg']   	 = $off_mesg;
        		$count['assistance'] 	 = $need_asitnce;
        		$count['star']       	 = $total_stars;
        		$count['cash_deposit']   = $petty_cash_deposit;
				$count['cash_withdraw']  = $petty_cash_withdraw;
				$count['expenditure']    = $expenditure;
				$count['no_risk']		 = $no_risk;
				$count['historic_risk']  = $historic_risk;
				$count['live_risk']		 = $live_risk;
				$count['spent']          = $spent;
				$count['current_bal']    = $su_curnt_bal;
				$count['weekly_allowance']    = $su_last_allowance;
				$count['incident']		 = $incident_report;

				$record_score = EarningScheme::getRecordsScore($data['select_user_id']);
				$labels       = HomeLabel::getLabels();
				$service_user = ServiceUser::where('is_deleted','0')->where('home_id', $home_id)->get();

				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;	
				} else{
					$chart_start_date = date('Y-m-d h:i:s');
				}

				$j = 0;
				for($i = 6; $i >= 0; $i--) {
				
					//$week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day'));
					$week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day', strtotime($chart_start_date)));


					$mfc_query = ServiceUserMFC::where('service_user_id', $data['select_user_id'])
											->where('su_mfc.is_deleted','0')
											->where('created_at','LIKE', $week_date.'%');

					if((!empty($from_date)) && (!empty($to_date))) {
						$mfc_query = $mfc_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
					} 
					$mfc = $mfc_query->first();

					if(!empty($mfc)) {
						$week_graph[$j]['point'] = $mfc->id;
					} else {
						// echo "1"; die;
						$week_graph[$j]['point'] = 0;
					}

					$week_graph[$j]['date'] = date('d/m', strtotime($week_date));
					$j++;
				}

				/*$j = 0;
				for($i = 6; $i >= 0; $i--) {
					$week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day'));

					$mfc_query = ServiceUserMFC::where('service_user_id', $data['select_user_id'])
											->where('su_mfc.is_deleted','0')
											->where('created_at','=', $week_date);

					if((!empty($from_date)) && (!empty($to_date))) {
						$mfc_query = $mfc_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
					} 
					$mfc = $mfc_query->first();

					if(!empty($mfc)) {
						$week_graph[$j]['point'] = $mfc->id;
					} else {
						// echo "1"; die;
						$week_graph[$j]['point'] = 0;
					}

					$week_graph[$j]['date'] = date('d/m', strtotime($week_date));
					$j++;
				}*/

				$risks = Risk::select('id','description')->where('is_deleted','0')->get()->toArray();
				foreach ($risks as $key =>  $risk) {
				    $status = Risk::checkRiskStatus($data['select_user_id'],$risk['id']);    
				    if(!empty($status)) {    
			        	$risks[$key]['status'] =  $status;
				    } else {
			        	$risks[$key]['status'] =  0;
				    }
				}
				// echo '<pre>'; print_r($risks); die;

				$risk_count['no_risk'] 	= 0;
				$risk_count['historic'] = 0;
				$risk_count['live'] 	= 0;
				
				foreach ($risks as $key =>  $risk) {
					$status = $risk['status'];
					if($status == 0) {    
						$risk_count['no_risk']  = 1 + $risk_count['no_risk'];
				    } else if($status == 1) {    
						$risk_count['historic'] = 1 + $risk_count['historic'];
				    } else if($status == 2) {
						$risk_count['live']     = 1 + $risk_count['live'];
				    }
				}
				// echo '<pre>'; print_r($risk_count); die;

				$moods = Mood::where('is_deleted','0')->where('home_id', $home_id)->where('status','0')->get()->toArray();
				// echo '<pre>'; print_r($moods); die;

				$su_mood_query = ServiceUserMood::select('mood.value as mood_value','mood.name as mood_name')
												->join('mood','mood.id','su_mood.mood_id')
												->where('su_mood.home_id', $home_id)
												->where('su_mood.is_deleted','0')
												->where('su_mood.service_user_id', $data['select_user_id']);
				if((!empty($from_date)) && (!empty($to_date))) {

					$su_mood_query = $su_mood_query->where('su_mood.created_at', '>=', $from_date)
														->where('su_mood.created_at','<=', $to_date);
				}
				$su_moods = $su_mood_query->get()->toArray();
				// echo "<pre>"; print_r($su_moods); die;


				$last_mood = 0;
				$j = 0;
				for($i = 6; $i >= 0; $i--) {
					// $week_date = date('Y-m-d h:i:s', strtotime('-'.$i.'day'));
					$week_date = date('Y-m-d',strtotime('-'.$i.'day', strtotime($chart_start_date)));

					$su_mood_query = ServiceUserMood::select('mood.value as mood_value','mood.name as mood_name','su_mood.created_at')
												->where('service_user_id', $data['select_user_id'])
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

        		return view('frontEnd.viewReports.index',compact('count','report_type','user_type','select_user','record_score','labels','service_user','week_graph','from_date','to_date','page','guide_tag','risk_count','moods','su_moods','su_curnt_bal','spent','mood_graph'));
			} else {
				// echo "11"; die;
				/*$record = $this->index();
				echo $record; */
				// $home_id = Auth::user()->home_id;
				$home_ids = Auth::user()->home_id;
				$ex_home_ids = explode(',', $home_ids);
				$home_id=$ex_home_ids[0];

				$su_in_danger_query = ServiceUserCareCenter::join('service_user as su','su.id','su_care_center.service_user_id')
														->where('su.home_id', $home_id)
														->where('care_type','D');
				if((!empty($from_date)) && (!empty($to_date))) {

					$su_in_danger_query = $su_in_danger_query->where('su_care_center.created_at', '>=', $from_date)
														->where('su_care_center.created_at','<=', $to_date);
				}
				$su_in_danger = $su_in_danger_query->count();

		    	$su_req_cb_query = ServiceUserCareCenter::join('service_user as su','su.id','su_care_center.service_user_id')
														->where('su.home_id', $home_id)
														->where('care_type','R');
				if((!empty($from_date)) && (!empty($to_date))) {
					$su_req_cb_query = $su_req_cb_query->where('su_care_center.created_at', '>=', $from_date)
														->where('su_care_center.created_at','<=', $to_date);
				}
				$su_req_cb = $su_req_cb_query->count();

				$off_mesg_query = OfficeMessage::where('home_id', $home_id)
												->where('order','0');
				if((!empty($from_date)) && (!empty($to_date))) {
				 $off_mesg_query = $off_mesg_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$off_mesg = $off_mesg_query->count();

				$need_asitnce_query = ServiceUserNeedAssistance::where('home_id', $home_id);
				if((!empty($from_date)) && (!empty($to_date))) {
					$need_asitnce_query = $need_asitnce_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$need_asitnce = $need_asitnce_query->count();

				$star_count_query = ServiceUserEarningStar::select('star')
														->join('service_user as su','su.id','su_earning_star.service_user_id')
														->where('su.home_id', $home_id);
				if((!empty($from_date)) && (!empty($to_date))) {
					$star_count_query = $star_count_query->where('su_earning_star.created_at', '>=', $from_date)
														->where('su_earning_star.created_at','<=', $to_date);
				}
				$star_count = $star_count_query->get()->toArray();
				$stars=0;
				foreach ($star_count as $key => $star) {
					$stars += $star['star'];
				}
				// echo $stars; die;

				$petty_cash_deposit_query = PettyCash::where('home_id', $home_id)->where('txn_type','D')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
					$petty_cash_deposit_query = $petty_cash_deposit_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);	
				} 
				$petty_cash_deposit = $petty_cash_deposit_query->value('balance');

				$petty_cash_withdraw_query = PettyCash::where('home_id', $home_id)->where('txn_type','W')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
				$petty_cash_withdraw_query = $petty_cash_withdraw_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$petty_cash_withdraw = $petty_cash_withdraw_query->value('balance');

				$expenditure = $petty_cash_deposit - $petty_cash_withdraw;
				// echo "<pre>"; print_r($expenditure); die;

				$no_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('status','0');
				if((!empty($from_date)) && (!empty($to_date))) {
					$no_risk_query = $no_risk_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$no_risk = $no_risk_query->count();

				$historic_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('status','1');
				if((!empty($from_date)) && (!empty($to_date))) {
					$historic_risk_query = $historic_risk_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$historic_risk = $historic_risk_query->count();

				$live_risk_query = ServiceUserRisk::where('home_id', $home_id)->where('status','2');
				if((!empty($from_date)) && (!empty($to_date))) {
					$live_risk_query = $live_risk_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$live_risk = $live_risk_query->count();


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
				$spent = (float)$total_weekly_allowance - (float)$total_curnt_bal;
				//echo 'spent = '.$spent; die;
				// die;
				//$service_user_id = $data['select_user_id'];
				if((!empty($from_date)) && (!empty($to_date))) {
					$incident_report = DynamicForm::countIncidentReport('',$from_date,$to_date);
				} else {
					$incident_report = DynamicForm::countIncidentReport('','','');
				}
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
				$count['incident']		 = $incident_report;
				// $count['record_score']   = $record_score;
				// echo "<pre>"; print_r($count); die;

				$record_score = EarningScheme::getRecordsScore('');
				$labels       = HomeLabel::getLabels();
				$service_user = ServiceUser::where('is_deleted','0')->where('home_id', $home_id)->get();

				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;	
				} else{
					$chart_start_date = date('Y-m-d h:i:s');
				}

				$j = 0;
				for($i = 6; $i >= 0; $i--) {
					// $week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day'));
					$week_date = date('Y-m-d h:i:s',strtotime('-'.$i.'day', strtotime($chart_start_date)));
					// echo $week_date; die;
					$mfc_query = ServiceUserMFC::where('su_mfc.is_deleted','0')
											->where('created_at','LIKE', $week_date.'%');
					if((!empty($from_date)) && (!empty($to_date))) {

						$mfc_query= $mfc_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
					}
					$mfc = $mfc_query->first();
					// echo "<pre>"; print_r($mfc); die;
					if(!empty($mfc)) {
						$week_graph[$j]['point'] = $mfc->id;
					} else {
						// echo "1"; die;
						$week_graph[$j]['point'] = 0;
					}
					$week_graph[$j]['date'] = date('d/m', strtotime($week_date));
					$j++;
				}

				$all_record = '';

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
												//->where('su_mood.service_user_id', $data['select_user_id'])
												->get()->toArray();

				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;	
				} else{
					$chart_start_date = date('Y-m-d');
				}

				$last_mood = 0;
				$j = 0;
				for($i = 6; $i >= 0; $i--) {
					// $week_date = date('Y-m-d h:i:s', strtotime('-'.$i.'day'));
					$week_date = date('Y-m-d',strtotime('-'.$i.'day', strtotime($chart_start_date)));

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

				
				return view('frontEnd.viewReports.index',compact('page','guide_tag','count','record_score','labels','service_user','week_graph','from_date','to_date','all_record','risk_count','moods','su_moods','mood_graph'));
			}
		} else { 
			// For Staff
			if($data['report_type'] == 'INDIVIDUAL') {
				$select_user = $data['select_user_id'];	
				$service_user = User::where('is_deleted','0')->where('home_id', $home_id)->get();

				$absence_meeting_query = AgendaMeeting::where('home_id', $home_id)->where('is_deleted','0')->whereRaw('FIND_IN_SET(?,staff_not_present)',$data['select_user_id']);
				if((!empty($from_date)) && (!empty($to_date))) {
					$absence_meeting_query = $absence_meeting_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$absence_meeting = $absence_meeting_query->get()->count();

				$missed_training_target_query = StaffTraining::select('training.id','training.training_name','staff_training.status')
											->join('training','training.id', 'staff_training.training_id')
											->where('staff_training.user_id', $data['select_user_id'])
											->where('training.home_id', $home_id)
											->where('training.is_deleted','0')
											->where('staff_training.status','0');
				if((!empty($from_date)) && (!empty($to_date))) {
					$missed_training_target_query = $missed_training_target_query->where('staff_training.created_at', '>=', $from_date)->where('staff_training.created_at','<=', $to_date);
				}
				$missed_training_target = $missed_training_target_query->get()->count();

				$petty_cash_deposit_query = PettyCash::where('home_id', $home_id)->where('txn_type','D')->orderBy('petty_cash.created_at','desc');
        		if((!empty($from_date)) && (!empty($to_date))) {
        			$petty_cash_deposit_query = $petty_cash_deposit_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
        		}
        			$petty_cash_deposit = $petty_cash_deposit_query->value('balance');
				$petty_cash_withdraw_query = PettyCash::where('home_id', $home_id)->where('txn_type','W')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
					$petty_cash_withdraw_query = $petty_cash_withdraw_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$petty_cash_withdraw = $petty_cash_withdraw_query->value('balance');
				$expenditure = $petty_cash_deposit - $petty_cash_withdraw;

				$annual_leaves_query = StaffAnnualLeave::where('home_id', $home_id)->where('is_deleted','0')->where('staff_member_id', $data['select_user_id']);
				if((!empty($from_date)) && (!empty($to_date))) {
				$annual_leaves_query = $annual_leaves_query->where('leave_date', '>=', $from_date)->where('leave_date','<=', $to_date);
				}
				$annual_leaves = $annual_leaves_query->count();
				// echo $annual_leaves; die;

				$staff_joined_query = User::where('home_id', $home_id)
									->where('is_deleted','0')
									->where('date_of_joining','!=','')
									->where('id', $data['select_user_id']);

				if(!empty($from_date)) {
					$staff_joined_query = $staff_joined_query->where('date_of_joining','>', $from_date);
				}
				$staff_joined = $staff_joined_query->get()->count();
				// echo "<pre>"; print_r($staff_joined); die;

				$staff_leaved_query = User::where('home_id', $home_id)
									->where('is_deleted','0')
									->where('date_of_leaving','!=','')
									->where('id', $data['select_user_id']);

				if(!empty($to_date)) {
					$staff_leaved_query = $staff_leaved_query->where('date_of_leaving','<', $to_date);
				}
				$staff_leaved = $staff_leaved_query->get()->count();
				// echo "<pre>"; print_r($staff_leaved); die;

				$counts = array();
				$counts['absence'] = $absence_meeting;
				$counts['target']  = $missed_training_target;
				$counts['cash_deposit']   = $petty_cash_deposit;
				$counts['cash_withdraw']  = $petty_cash_withdraw;
				$counts['expenditure']    = $expenditure;
				$counts['anual_leave']    = $annual_leaves;

				$counts['joined']  = $staff_joined;
				$counts['leaved']  = $staff_leaved;
				// echo "<pre>"; print_r($counts); die;

				//For Sickness Graph(Line Chart)
				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;
				} else {
					$chart_start_date = date('Y-m-d h:i:s');
				}
				$j = 0;
				for($i = 6; $i >= 0; $i--) {
					$week_date = date('Y-m-d', strtotime('-'.$i.'day', strtotime($chart_start_date)));

					$sick_query = StaffSickLeave::select('id')->where('is_deleted','0')
												->where('staff_member_id', $data['select_user_id'])
												->where('leave_date','LIKE', $week_date.'%');

					if((!empty($from_date)) && (!empty($to_date))) {

						$sick_query= $sick_query->where('leave_date', '>=', $from_date)->where('leave_date','<=', $to_date);
					}
					$sick = $sick_query->first();

					// echo "<pre>"; print_r($sick); die;
					if(!empty($sick)) {
						$sick_graph[$j]['point'] = 1;
					} else {
						// echo "1"; die;
						$sick_graph[$j]['point'] = 0;
					}

						$sick_graph[$j]['date'] = date('d/m', strtotime($week_date));
						$j++;
				}
				//echo "<pre>"; print_r($sick_graph); die;

				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;
				} else {
					$chart_start_date = date('Y');
				}
				$j = 0;
				for ($i = 2; $i >= 0 ; $i--) {
					$sel_year = date('Y', strtotime('-'.$i.'years', strtotime($chart_start_date)));
					// echo $sel_year; die;
					$staff_joined_query = User::where('is_deleted','0');
					//->where('date_of_joining','!=','')
					$staff_joined = $staff_joined_query->where('date_of_joining', 'LIKE', $sel_year.'%')->count();
					$staff_leaved = $staff_joined_query->where('date_of_leaving', 'LIKE', $sel_year.'%')->count();

					$turnover_graph[$j]['join'] = $staff_joined;
					$turnover_graph[$j]['leave'] = $staff_leaved;
					$turnover_graph[$j]['year']  = $sel_year;
					$j++;
				}


        		return view('frontEnd.viewReports.staff_index',compact('report_type','user_type','select_user','service_user','from_date','to_date','page','guide_tag','counts','sick_graph','turnover_graph'));
			} else {
				//echo "2"; die;
				$service_user = User::where('is_deleted','0')->where('home_id', $home_id)->get();

				$absence_meeting_query = AgendaMeeting::where('home_id', $home_id)->where('is_deleted','0')->where('staff_not_present','!=','')->orderBy('id','asc');
				;
				if((!empty($from_date)) && (!empty($to_date))) {
					$absence_meeting_query = $absence_meeting_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$absence_meeting = $absence_meeting_query->get()->count();

				$missed_training_target_query = StaffTraining::select('training.id','training.training_name','staff_training.status')
											->join('training','training.id', 'staff_training.training_id')
											//->where('staff_training.user_id', $data['select_user_id'])
											->where('training.home_id', $home_id)
											->where('training.is_deleted','0')
											->where('staff_training.status','0');
											// ->get()
											// ->count();
				if((!empty($from_date)) && (!empty($to_date))) {
					$missed_training_target_query = $missed_training_target_query->where('staff_training.created_at', '>=', $from_date)->where('staff_training.created_at','<=', $to_date);
				}
				$missed_training_target = $missed_training_target_query->get()->count();

				$petty_cash_deposit_query = PettyCash::where('home_id', $home_id)->where('txn_type','D')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
					$petty_cash_deposit_query = $petty_cash_deposit_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);	
				} 
				$petty_cash_deposit = $petty_cash_deposit_query->value('balance');

				$petty_cash_withdraw_query = PettyCash::where('home_id', $home_id)->where('txn_type','W')->orderBy('petty_cash.created_at','desc');
				if((!empty($from_date)) && (!empty($to_date))) {
				$petty_cash_withdraw_query = $petty_cash_withdraw_query->where('created_at', '>=', $from_date)->where('created_at','<=', $to_date);
				}
				$petty_cash_withdraw = $petty_cash_withdraw_query->value('balance');

				$expenditure = $petty_cash_deposit - $petty_cash_withdraw;
				// echo "<pre>"; print_r($expenditure); die;

				$annual_leaves_query = StaffAnnualLeave::where('home_id', $home_id)->where('is_deleted','0');
				if((!empty($from_date)) && (!empty($to_date))) {
				$annual_leaves_query = $annual_leaves_query->where('leave_date', '>=', $from_date)->where('leave_date','<=', $to_date);
				}
				$annual_leaves = $annual_leaves_query->count();
				// echo $annual_leaves; die;

				$staff_joined_query = User::where('home_id', $home_id)
									->where('is_deleted','0')
									->where('date_of_joining','!=','');

				if(!empty($from_date)) {
					$staff_joined_query = $staff_joined_query->where('date_of_joining','>', $from_date);
				}
				$staff_joined = $staff_joined_query->get()->count();
				// echo "<pre>"; print_r($staff_joined); die;

				$staff_leaved_query = User::where('home_id', $home_id)
									->where('is_deleted','0')
									->where('date_of_leaving','!=','');

				if(!empty($to_date)) {
					$staff_leaved_query = $staff_leaved_query->where('date_of_leaving','<', $to_date);
				}
				$staff_leaved = $staff_leaved_query->get()->count();
				// echo "<pre>"; print_r($staff_leaved); die;

				$counts = array();
				$counts['absence'] = $absence_meeting;
				$counts['target']  = $missed_training_target;
				$counts['cash_deposit']   = $petty_cash_deposit;
				$counts['cash_withdraw']  = $petty_cash_withdraw;
				$counts['expenditure']    = $expenditure;
				$counts['anual_leave']    = $annual_leaves;

				$counts['joined']  = $staff_joined;
				$counts['leaved']  = $staff_leaved;
				
				//For Sickness Graph(Line Chart)
				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;
				} else {
					$chart_start_date = date('Y-m-d h:i:s');
				}
				$j = 0;
				for($i = 6; $i >= 0; $i--) {
					$week_date = date('Y-m-d', strtotime('-'.$i.'day', strtotime($chart_start_date)));

					$sick_query = StaffSickLeave::select('id')->where('is_deleted','0')
												// ->where('staff_member_id', $data['select_user_id'])
												->where('leave_date','LIKE', $week_date.'%');

					if((!empty($from_date)) && (!empty($to_date))) {

						$sick_query= $sick_query->where('leave_date', '>=', $from_date)->where('leave_date','<=', $to_date);
					}
					$sick = $sick_query->count(); 
					// echo "<pre>"; print_r($sick); die;
					if(!empty($sick)) {
						$sick_graph[$j]['point'] = $sick;
					} else {
						// echo "1"; die;
						$sick_graph[$j]['point'] = 0;
					}

						$sick_graph[$j]['date'] = date('d/m', strtotime($week_date));
						$j++;
				}
				// echo "<pre>"; print_r($sick_graph); die;
				

				//echo date('F Y'); die;
				//echo date('M Y'); die;
				if((!empty($from_date)) && (!empty($to_date))) {
					$chart_start_date = $to_date;
				} else {
					$chart_start_date = date('Y');
				}
				$j = 0;
				for ($i = 2; $i >= 0 ; $i--) {
					$sel_year = date('Y', strtotime('-'.$i.'years', strtotime($chart_start_date)));
					// echo $sel_year; die;
					$staff_joined_query = User::where('is_deleted','0');
					//->where('date_of_joining','!=','')
					$staff_joined = $staff_joined_query->where('date_of_joining', 'LIKE', $sel_year.'%')->count();
					$staff_leaved = $staff_joined_query->where('date_of_leaving', 'LIKE', $sel_year.'%')->count();

					$turnover_graph[$j]['join'] = $staff_joined;
					$turnover_graph[$j]['leave'] = $staff_leaved;
					$turnover_graph[$j]['year']  = $sel_year;
					$j++;
				}
				// echo '<pre>'; print_r($turnover_graph); die;
				// echo '<pre>'; print_r($staff_joined); echo "<br>"; 
				// print_r($staff_leaved); die;

				$all_record = '';
				return view('frontEnd.viewReports.staff_index',compact('page','guide_tag','service_user','from_date','to_date','all_record','counts','sick_graph','turnover_graph'));
			}
		} die;
	}

}
