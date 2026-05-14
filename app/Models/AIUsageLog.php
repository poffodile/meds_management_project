<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    public $timestamps = false;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'home_id',
        'user_id',
        'feature',
        'model_used',
        'tokens_input',
        'tokens_output',
        'tokens_total',
        'prompt_hash',
        'response_status',
        'error_message',
        'latency_ms',
    ];

    protected $casts = [
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'tokens_total' => 'integer',
        'latency_ms' => 'integer',
    ];
}
