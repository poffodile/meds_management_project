<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRecurrence extends Model
{
    protected $table = 'shift_recurrences';

    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(ScheduledShift::class, 'shift_id');
    }
}
