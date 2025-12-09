<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_type'; 

    protected $fillable = [
        'leave_name', 'leave_category', 'max_days', 'status', 'color', 'is_active'
    ];
}
