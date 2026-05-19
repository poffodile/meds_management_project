<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MARAdministration extends Model
{
    protected $table = 'mar_administrations';

    protected $fillable = [
        'mar_sheet_id',
        'date',
        'time_slot',
        'given',
        'dose_given',
        'administered_by',
        'witnessed_by',
        'code',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'given' => 'boolean',
        'mar_sheet_id' => 'integer',
        'home_id' => 'integer',
        'administered_by' => 'integer',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function marSheet()
    {
        return $this->belongsTo(MARSheet::class, 'mar_sheet_id');
    }

    public function administeredByUser()
    {
        return $this->belongsTo(\App\User::class, 'administered_by');
    }
}
