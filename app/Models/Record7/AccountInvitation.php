<?php

namespace App\Models\Record7;

class AccountInvitation extends Record7Model
{
    protected $table = 'record7_account_invitations';

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'used_at' => 'datetime',
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
