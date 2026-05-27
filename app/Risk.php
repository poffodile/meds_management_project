<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\ServiceUserRisk;

class Risk extends Model
{
    protected $table = 'risk';

   	public static function checkRiskStatus($service_user_id = null, $risk_id = null){
	
		$status = ServiceUserRisk::where('service_user_id',$service_user_id)
								->where('risk_id',$risk_id)
								->orderBy('id','desc')
								->value('status');
		return $status;
	}   

	public static function overallRiskStatus($service_user_id = null){

		$risk_status = 0;    
    	$status = array();
		$all_risks = Risk::select('id')->where('status','1')->where('read_notify','0')->orderBy('id','asc')->get()->toArray();
    	
    	if(!empty($all_risks)){
	    	foreach($all_risks as $risk){
	    		$su_risk_status[] = Risk::checkRiskStatus($service_user_id,$risk['id']);
	    	}
	    	
	    	rsort($su_risk_status);
	    	if(isset($su_risk_status['0'])){
		    	$risk_status = $su_risk_status['0'];
		    }
		}
    	return $risk_status;
    }
}