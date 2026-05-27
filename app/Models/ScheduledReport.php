<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    protected $table = 'scheduled_reports';

    protected $fillable = [
        'report_name',
        'report_type',
        'schedule_frequency',
        'schedule_day',
        'schedule_time',
        'recipients',
        'output_format',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'next_run_date' => 'datetime',
        'last_run_date' => 'datetime',
        'schedule_day' => 'integer',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeDue($query)
    {
        return $query->where('next_run_date', '<=', now())
            ->where('is_active', 1)
            ->where('is_deleted', 0);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
