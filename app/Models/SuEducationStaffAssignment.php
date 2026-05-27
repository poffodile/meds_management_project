<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\ServiceUser;
use App\User;

class SuEducationStaffAssignment extends Model
{
    protected $table = 'su_education_staff_assignments';

    protected $fillable = [
        'service_user_id',
        'staff_id',
        'assigned_by',
        'status'
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
