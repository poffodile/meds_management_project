<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;
use App\User;

class SuEducationTask extends Model
{
    protected $table = 'su_education_tasks';

    protected $fillable = [
        'service_user_id',
        'education_profile_id',
        'staff_id',
        'subject',
        'title',
        'description',
        'due_date',
        'attachment',
        'status',
        'submission_file',
        'submitted_at'
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }

    public function profile()
    {
        return $this->belongsTo(SuEducationProfile::class, 'education_profile_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
