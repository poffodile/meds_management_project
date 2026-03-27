<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'staff_id',
        'category_id',
        'home_id',
        'clock_in',
        'clock_out',
        'notes',
        'status',
    ];

    public function staff()
    {
        return $this->belongsTo(\App\User::class, 'staff_id');
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\ShiftCategory::class, 'category_id');
    }
}
