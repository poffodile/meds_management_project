<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomatedWorkflow extends Model
{
    protected $table = 'automated_workflows';

    protected $fillable = [
        'workflow_name',
        'category',
        'trigger_type',
        'trigger_config',
        'action_type',
        'action_config',
        'cooldown_hours',
        'is_active',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'action_config' => 'array',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'next_run_date' => 'datetime',
        'last_triggered_at' => 'datetime',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function executionLogs()
    {
        return $this->hasMany(WorkflowExecutionLog::class, 'workflow_id');
    }
}
