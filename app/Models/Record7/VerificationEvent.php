<?php

namespace App\Models\Record7;

/**
 * Why a verification was asked for, and what happened.
 *
 * Recorded whether or not a code was demanded, so a manager can see what was
 * waved through as well as what was challenged.
 */
class VerificationEvent extends Record7Model
{
    protected $table = 'record7_verification_events';

    public $timestamps = false;

    protected $casts = ['occurred_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
