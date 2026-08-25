<?php

namespace App\Models\Record7;

/**
 * A one-time way back in when the usual method is unavailable.
 *
 * Only the hash is stored. A recovery code is a password by another name, and
 * a list of them in plain text would be worse than no second factor at all.
 */
class RecoveryCode extends Record7Model
{
    protected $table = 'record7_recovery_codes';

    protected $casts = [
        'issued_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isUsable(): bool
    {
        return $this->used_at === null;
    }
}
