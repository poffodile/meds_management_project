<?php

namespace App\Models\Staff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\IncidentType;
use App\ServiceUser;

class StaffReportIncidents extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table="staff_report_incidents";
    protected $fillable = ['home_id','user_id', 'incident_type_id','severity_id','client_id','ref','date_time','location','location_detail','what_happened','immediate_action','investigation_findings','resolution_notes','lessons_learned','is_safeguarding','family_notify','cqcNotification','policeInvolved','status','deleted_at'];

    public function incidentType(){
        return $this->belongsTo(IncidentType::class, 'incident_type_id', 'id');
    }
    public function clients(){
        return $this->belongsTo(ServiceUser::class, 'client_id', 'id');
    }
    public function safeguarddetails()
    {
        return $this->belongsToMany(SafeguardingType::class, 'staff_report_incidents_safeguardings', 'staff_report_incident_id', 'safeguarding_type_id');
    }
}
