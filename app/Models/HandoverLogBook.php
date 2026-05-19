<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandoverLogBook extends Model
{
    protected $table = 'handover_log_book';

    protected $fillable = [
        'user_id',
        'assigned_staff_user_id',
        'service_user_id',
        'log_book_id',
        'home_id',
        'title',
        'details',
        'date',
        'notes',
        'is_deleted',
        'acknowledged_at',
        'acknowledged_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'assigned_staff_user_id' => 'integer',
        'service_user_id' => 'integer',
        'log_book_id' => 'integer',
        'home_id' => 'integer',
        'date' => 'datetime',
        'acknowledged_at' => 'datetime',
        'acknowledged_by' => 'integer',
        'is_deleted' => 'integer',
    ];

    // --- Relationships ---

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function assignedStaff()
    {
        return $this->belongsTo(\App\User::class, 'assigned_staff_user_id');
    }

    public function acknowledgedByUser()
    {
        return $this->belongsTo(\App\User::class, 'acknowledged_by');
    }

    public function serviceUser()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'service_user_id');
    }

    // --- Scopes ---

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('handover_log_book.home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('handover_log_book.is_deleted', 0);
    }
}
