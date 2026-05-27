<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowExecutionLog extends Model
{
    protected $table = 'workflow_execution_logs';

    public $timestamps = false;

    protected $fillable = [
        'workflow_id',
        'home_id',
        'trigger_type',
        'trigger_data',
        'action_type',
        'action_result',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'executed_at' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(AutomatedWorkflow::class, 'workflow_id');
    }
}
