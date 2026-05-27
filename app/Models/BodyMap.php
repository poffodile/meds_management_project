<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BodyMap extends Model
{
    protected $table = 'body_map';

    public $timestamps = true;

    protected $fillable = [
        'home_id',
        'service_user_id',
        'staff_id',
        'su_risk_id',
        'sel_body_map_id',
        'injury_type',
        'injury_description',
        'injury_date',
        'injury_size',
        'injury_colour',
        'is_deleted',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'injury_date' => 'date',
        'home_id' => 'integer',
        'service_user_id' => 'integer',
        'staff_id' => 'integer',
        'su_risk_id' => 'integer',
    ];

    // --- Relationships ---

    public function staff()
    {
        return $this->belongsTo(\App\User::class, 'staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function serviceUserRisk()
    {
        return $this->belongsTo(\App\ServiceUserRisk::class, 'su_risk_id');
    }

    // --- Scopes ---

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', '0');
    }
}
