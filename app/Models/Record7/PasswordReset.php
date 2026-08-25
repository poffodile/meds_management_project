<?php

namespace App\Models\Record7;

class PasswordReset extends Record7Model
{
    protected $table = 'record7_password_resets';

    protected $casts = [
        'requested_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isRedeemable(): bool
    {
        return $this->status === 'pending' && now()->lessThan($this->expires_at);
    }
}
