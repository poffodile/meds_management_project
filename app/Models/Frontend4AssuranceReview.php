<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4AssuranceReview extends Model
{
    protected $table = 'frontend4_assurance_reviews';
    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Assurance reviews are append-only.'));
        static::deleting(fn () => throw new LogicException('Assurance reviews are append-only.'));
    }

    public function scopeForContext($query, int $organisationId, int $serviceId)
    {
        return $query->where('organisation_id', $organisationId)->where('service_id', $serviceId);
    }
}
