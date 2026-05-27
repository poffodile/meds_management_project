<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class medicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_id',
        'user_id',
        'client_id',
        'medication_name',
        'dosage',
        'frequesncy',
        'administrator_date',
        'witnessed_by',
        'notes',
        'side_effect',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'administrator_date' => 'datetime',
        'status' => 'integer',
        'home_id' => 'integer',
        'user_id' => 'integer',
        'client_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }
}
