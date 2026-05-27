<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dol extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['home_id','user_id','client_id','dols_status','authorisation_type','referral_date','authorisation_start_date','authorisation_end_date','review_date','supervisory_body','case_reference','best_interests_assessor','mental_health_assessor','reason_for_dols','imca_appointed','mental_capacity_assessment','appeal_rights','care_plan_updated','family_notified','additional_notes','status'];
}
