<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4HandoverAcknowledgement extends Model
{
    protected $table = 'frontend4_handover_acknowledgements';
    protected $guarded = [];
    protected $casts = ['acknowledged_at' => 'datetime'];
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Handover acknowledgements are append-only.'));
        static::deleting(fn () => throw new LogicException('Handover acknowledgements are append-only.'));
    }
}
