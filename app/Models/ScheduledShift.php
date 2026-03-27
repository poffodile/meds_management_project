<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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

    public function homeArea()
    {
        return $this->belongsTo(HomeArea::class, 'home_area_id');
    }

    public function shiftCategory()
    {
        return $this->belongsTo(ShiftCategory::class, 'shift_category_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'staff_id');
    }
    public function todayShifts()
    {
        return $this->whereDate('start_date', date('Y-m-d'));
    }
    public function unfilledShifts()
    {
        return $this->where('status', 'unfilled');
    }
    public function scopeHomeId($query)
    {
        return $query->where('home_id', Auth::user()->home_id);
    }

    public function timesheet()
    {
        return $this->hasOne(Timesheet::class, 'shift_id');
    }
}
