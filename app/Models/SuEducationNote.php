<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;
use App\User;

class SuEducationNote extends Model
{
    protected $table = 'su_education_notes';

    protected $fillable = [
        'service_user_id',
        'education_profile_id',
        'staff_id',
        'notes',
        'type',
        'is_alert'
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
