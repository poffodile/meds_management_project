<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPortalAccess extends Model
{
    protected $table = 'client_portal_accesses';

    protected $fillable = [
        'home_id',
        'client_id',
        'client_type',
        'user_email',
        'full_name',
        'relationship',
        'access_level',
        'can_view_schedule',
        'can_view_care_notes',
        'can_send_messages',
        'can_request_bookings',
        'phone',
        'is_primary_contact',
        'is_active',
        'activation_date',
        'last_login',
        'notes',
        'is_deleted',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary_contact' => 'boolean',
        'can_view_schedule' => 'boolean',
        'can_view_care_notes' => 'boolean',
        'can_send_messages' => 'boolean',
        'can_request_bookings' => 'boolean',
        'activation_date' => 'date',
        'last_login' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->where('is_deleted', 0);
    }

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForEmail($query, $email)
    {
        return $query->where('user_email', $email);
    }
}
