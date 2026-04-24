<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;

class SuEducationProfile extends Model
{
    protected $table = 'su_education_profiles';

    protected $fillable = [
        'service_user_id',
        'school_name',
        'grade',
        'subjects',
        'academic_year',
        'home_id',
        'created_by',
        'status'
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    public function tasks()
    {
        return $this->hasMany(SuEducationTask::class, 'education_profile_id');
    }

    public function attendance()
    {
        return $this->hasMany(SuEducationAttendance::class, 'education_profile_id');
    }

    public function notes()
    {
        return $this->hasMany(SuEducationNote::class, 'education_profile_id');
    }

    public function resources()
    {
        return $this->hasMany(SuEducationResource::class, 'education_profile_id');
    }
}
