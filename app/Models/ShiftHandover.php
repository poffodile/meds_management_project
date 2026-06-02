<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftHandover extends Model
{
    protected $table = 'shift_handovers';

    protected $fillable = [
        'home_id',
        'location',
        'handover_date',
        'handover_time',
        'from_carer_user_id',
        'from_carer_name',
        'to_carer_user_id',
        'to_carer_name',
        'general_notes',
        'client_updates',
        'medication_concerns',
        'priority_alerts',
        'status',
        'submitted_at',
        'acknowledged_at',
        'acknowledged_by_user_id',
        'created_by_user_id',
        'edit_log',
    ];

    protected $casts = [
        'home_id' => 'integer',
        'from_carer_user_id' => 'integer',
        'to_carer_user_id' => 'integer',
        'acknowledged_by_user_id' => 'integer',
        'created_by_user_id' => 'integer',
        'handover_date' => 'date',
        'submitted_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'client_updates' => 'array',
        'medication_concerns' => 'array',
        'priority_alerts' => 'array',
        'edit_log' => 'array',
    ];

    public const MANAGER_TYPES = ['M', 'CM', 'A', 'O'];

    /** Can the given user edit this handover? Author may edit until acknowledged; managers always may. */
    public function canBeEditedBy($user): bool
    {
        if (!$user) {
            return false;
        }
        if (in_array($user->user_type, self::MANAGER_TYPES, true)) {
            return true;
        }
        return $this->created_by_user_id == $user->id && $this->status !== 'acknowledged';
    }

    public function fromCarer()
    {
        return $this->belongsTo(\App\User::class, 'from_carer_user_id');
    }

    public function toCarer()
    {
        return $this->belongsTo(\App\User::class, 'to_carer_user_id');
    }

    public function acknowledgedByUser()
    {
        return $this->belongsTo(\App\User::class, 'acknowledged_by_user_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('shift_handovers.home_id', $homeId);
    }
}
