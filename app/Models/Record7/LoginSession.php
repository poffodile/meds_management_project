<?php

namespace App\Models\Record7;

class LoginSession extends Record7Model
{
    protected $table = 'record7_login_sessions';

    protected $casts = [
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'locked_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activeService()
    {
        return $this->belongsTo(Service::class, 'active_service_id');
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isLive(): bool
    {
        return in_array($this->status, ['active', 'locked'], true);
    }
}
