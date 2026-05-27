<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AICarePlan extends Model
{
    protected $table = 'ai_care_plans';

    protected $fillable = [
        'home_id',
        'client_id',
        'created_by',
        'plan_status',
        'assessment_type',
        'care_setting',
        'plan_data',
        'assessment_snapshot',
        'ai_model',
        'tokens_input',
        'tokens_output',
        'generation_time_ms',
        'approved_at',
        'approved_by',
        'review_date',
        'is_deleted',
    ];

    protected $casts = [
        'plan_data' => 'array',
        'assessment_snapshot' => 'array',
        'approved_at' => 'datetime',
        'review_date' => 'date',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'generation_time_ms' => 'integer',
        'is_deleted' => 'integer',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeActive($query)
    {
        return $query->where('plan_status', 'active');
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\User::class, 'approved_by');
    }
}
