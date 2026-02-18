<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftDocument extends Model
{
    protected $table = 'shift_documents';

    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(ScheduledShift::class, 'shift_id');
    }
}
