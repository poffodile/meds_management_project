<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledShift extends Model
{
    use SoftDeletes;

    protected $table = 'scheduled_shifts';

    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'service_user_id');
    }

    public function staff()
    {
        return $this->belongsTo(\App\User::class, 'staff_id');
    }

    public function recurrence()
    {
        return $this->hasOne(ShiftRecurrence::class, 'shift_id');
    }

    public function assessments()
    {
        return $this->hasMany(ShiftAssessment::class, 'shift_id');
    }

    public function documents()
    {
        return $this->hasMany(ShiftDocument::class, 'shift_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'staff_id');
    }
}
