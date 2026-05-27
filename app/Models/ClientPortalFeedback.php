<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPortalFeedback extends Model
{
    protected $table = 'client_portal_feedback';

    protected $fillable = [
        'home_id',
        'client_id',
        'submitted_by',
        'submitted_by_id',
        'relationship',
        'feedback_type',
        'category',
        'rating',
        'subject',
        'comments',
        'priority',
        'status',
        'is_anonymous',
        'wants_callback',
        'contact_email',
        'contact_phone',
        'response',
        'response_date',
        'responded_by',
        'responded_by_name',
        'acknowledged_by',
        'acknowledged_date',
        'is_deleted',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
        'wants_callback' => 'boolean',
        'is_deleted' => 'boolean',
        'response_date' => 'datetime',
        'acknowledged_date' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function portalAccess()
    {
        return $this->belongsTo(ClientPortalAccess::class, 'submitted_by_id');
    }

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
