<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4ClientEvent extends Model
{
    protected $table = 'frontend4_client_events';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'effective_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Client lifecycle events are append-only.'));
        static::deleting(fn () => throw new LogicException('Client lifecycle events are append-only.'));
    }
}
