<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class Frontend4ReportExportEvent extends Model
{
    protected $table = 'frontend4_report_export_events';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'identifiable' => 'boolean',
        'generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Report export events are append-only.'));
        static::deleting(fn () => throw new LogicException('Report export events are append-only.'));
    }

    public function scopeForContext($query, int $organisationId, int $serviceId)
    {
        return $query->where('organisation_id', $organisationId)->where('service_id', $serviceId);
    }
}
