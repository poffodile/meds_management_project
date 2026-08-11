<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4AuthenticationEvent extends Model
{
    protected $table = 'frontend4_authentication_events';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'identifier_hash', 'event_type', 'successful',
        'ip_address', 'user_agent_hash', 'metadata', 'created_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Frontend 4 authentication events are append-only.'));
        static::deleting(fn () => throw new LogicException('Frontend 4 authentication events are append-only.'));
    }
}
