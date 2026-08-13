<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4PrescriptionEvent extends Model
{
    protected $table = 'frontend4_prescription_events';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'effective_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Prescription events are append-only.'));
        static::deleting(fn () => throw new LogicException('Prescription events are append-only.'));
    }
}
