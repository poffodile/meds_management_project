<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4ClinicalEvent extends Model
{
    protected $table = 'frontend4_clinical_events';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['metadata' => 'array', 'occurred_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Clinical events are append-only.'));
        static::deleting(fn () => throw new LogicException('Clinical events are append-only.'));
    }
}
