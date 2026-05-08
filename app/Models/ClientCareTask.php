<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientCareTask extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['home_id', 'user_id', 'overview_id', 'task_title', 'task_type_id', 'task_category_id', 'priority', 'client_id', 'care_plan_id', 'task_tag', 'frequency', 'location', 'scheduled_date', 'scheduled_time', 'duration', 'carer_id', 'visit_id', 'shift_id', 'risk_level_id', 'safeguarding', 'two_person', 'ppe_required', 'risk_notes', 'task_description', 'status', 'deleted_at'];

    public function clientTaskType()
    {
        return $this->belongsTo(ClientTaskType::class, 'task_type_id', 'id');
    }
    public function clientTaskCategorys()
    {
        return $this->belongsTo(ClientTaskCategory::class, 'task_category_id', 'id');
    }
    public function carer()
    {
        return $this->belongsTo(\App\User::class, 'carer_id', 'id');
    }
}
