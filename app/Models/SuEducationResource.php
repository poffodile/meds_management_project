<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;
use App\User;

class SuEducationResource extends Model
{
    protected $table = 'su_education_resources';

    protected $fillable = [
        'service_user_id',
        'education_profile_id',
        'staff_id',
        'title',
        'subject',
        'link',
        'file_path'
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
