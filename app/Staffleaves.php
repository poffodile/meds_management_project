<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Staffleaves extends Model
{
    // 
    protected $table = 'staff_leaves';
    
    public function leave_types()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type', 'id');
    }
}
