<?php

namespace App\Models\Record7;

class TrustedDevice extends Record7Model
{
    protected $table = 'record7_trusted_devices';

    protected $casts = [
        'shared' => 'boolean',
        'trusted_at' => 'datetime',
        'trusted_until' => 'datetime',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function hasExpired(): bool
    {
        return $this->trusted_until !== null && now()->greaterThan($this->trusted_until);
    }

    /** A shared device is recognised, but never trusted. */
    public function isUsableWithoutVerification(): bool
    {
        return ! $this->shared && $this->status === 'trusted' && ! $this->hasExpired();
    }
}
