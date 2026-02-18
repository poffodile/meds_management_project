<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftAssessment extends Model
{
    protected $table = 'shift_assessments';

    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(ScheduledShift::class, 'shift_id');
    }
}
