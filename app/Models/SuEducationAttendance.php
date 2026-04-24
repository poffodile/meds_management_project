<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;
use App\User;

class SuEducationAttendance extends Model
{
    protected $table = 'su_education_attendance';

    protected $fillable = [
        'service_user_id',
        'education_profile_id',
        'staff_id',
        'date',
        'status',
        'notes'
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
