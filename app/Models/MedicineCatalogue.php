<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineCatalogue extends Model
{
    protected $table = 'medicine_catalogue';

    protected $fillable = [
        'dmd_code', 'dmd_concept_level', 'name', 'form', 'default_route',
        'countable_unit', 'strength_amount', 'strength_unit', 'strength_volume',
        'strength_volume_unit', 'is_controlled', 'cd_schedule', 'dmd_status',
        'replaced_by_id', 'is_local', 'valid_from', 'valid_to', 'source_version',
        'source_updated_at',
    ];

    protected $casts = [
        'strength_amount' => 'float',
        'strength_volume' => 'float',
        'is_controlled' => 'boolean',
        'is_local' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'source_updated_at' => 'datetime',
    ];

    public function scopeSelectable($query)
    {
        return $query->where('dmd_status', 'current');
    }

    public function prescriptions()
    {
        return $this->hasMany(MARSheet::class, 'medicine_id');
    }

    public function replacement()
    {
        return $this->belongsTo(self::class, 'replaced_by_id');
    }
}
