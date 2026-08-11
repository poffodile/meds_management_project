<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthenticationEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'identifier_hash',
        'event_type',
        'successful',
        'ip_address',
        'user_agent_hash',
        'metadata',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Authentication events are append-only.');
        });

        static::deleting(function () {
            throw new \LogicException('Authentication events are append-only.');
        });
    }
}
