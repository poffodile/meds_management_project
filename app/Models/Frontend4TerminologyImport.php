<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4TerminologyImport extends Model
{
    protected $table = 'frontend4_terminology_imports';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['release_date' => 'date', 'summary' => 'array', 'created_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Terminology import records are append-only.'));
        static::deleting(fn () => throw new LogicException('Terminology import records are append-only.'));
    }
}
