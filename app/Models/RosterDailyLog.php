<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ServiceUser;

class RosterDailyLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable=['home_id','user_id','date','visitor_name','entry_type_id','org_company','purpose_visit','client_id','arrival_time','departure_time','notes','available_for_overtime','follow_details','destination','transport_id','risk_assessment','outing_summary','deleted_at'];

    public function subCategorys(){
        return $this->belongsTo(DailyLogSubCategory::class, 'entry_type_id' , 'id');
    }
    public function accompanyingStaffs(){
        return $this->hasMany(AccompanyingStaff::class, 'roster_daily_log_id' , 'id');
    }
    public function clients(){
        return $this->belongsTo(ServiceUser::class, 'client_id' , 'id');
    }
    public function transport(){
        return $this->belongsTo(Transport::class, 'transport_id' , 'id');
    }
}
