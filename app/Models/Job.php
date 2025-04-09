<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\Project;
// use App\Customer;
use App\Models\Customer;
use App\Models\Constructor_additional_contact;
class Job extends Model
{
    use HasFactory;
    protected $table = 'jobs';

    protected $fillable = [
        'home_id',
        'customer_id',
        'job_ref',
        'contact_id',
        'project_id',
        'name',
        'contact',
        'telephone',
        'email',
        'short_decinc',
        'description',
        'address',
        'city',
        'country',
        'pincode',
        'site_id',
        'region',
        'company',
        'conatact_name',
        'site_email',
        'site_telephone',
        'site_mobile',
        'site_address',
        'site_city',
        'site_country',
        'site_pincode',
        'notes',
        'customer_ref',
        'cust_job_ref',
        'pay_amount',
        'purchase_order_ref',
        'job_type',
        'priorty',
        'alert_customer',
        'on_route_sms',
        'start_date',
        'complete_by',
        'tags',
        'product_id',
        'customer_notes',
        'internal_notes',
        'attachments',
        'site_country_id',
        'country_id',
        'user_id',
        'status',
    ];
    public static function job_save($data){
        // echo "<pre>";print_r($data);die;
        $last_id=$data['last_job_id'];
        if($last_id == ''){
            $job_ref="JOB-1";    
        }else {
            if($data['id'] != '') {
                $job_ref="JOB-".$data['last_job_id'];
                
            }else {
                $job_ref="JOB-".$last_id+1;
            }
            
        }
        if(isset($data['attachments'])){
            $data['attachments']=$data['attachments'];
        }
       
        $data['job_ref'] = $job_ref;
        if(isset($data['final_amount'])){
            $data['pay_amount']=$data['final_amount'];
        }
            $insert=self::updateOrCreate(
                ['id' => $data['id'] ?? null],
                $data
            );
            $data=['id'=>$insert->id,'name'=>$insert->name,'job_ref'=>$insert->job_ref];
            return $data;
    }
    public static function getAllAppointment($customer_id){
        return DB::table('jobs as job')
                ->select('job.*','appoint.id as appointment_id','appoint.home_id as appoint_home','appoint.job_id','appoint.user_id','appoint.appointment_type_id','appoint.status','appoint.deleted_at')
                ->join('construction_job_appointments as appoint','job.id','=','appoint.job_id')
                ->where('job.customer_id',$customer_id)
                ->where('appoint.status',1)
                ->whereNull('appoint.deleted_at')
                ->get();
    }
    public static function getAllJob($home_id){
        return self::where('home_id',$home_id)->whereNull('deleted_at');
    }

    public function product(){

    }
    
}
