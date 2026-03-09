<?php

namespace App\Models\Staff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\user;

class StaffTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "staff_tasks";
    protected $fillable = ['home_id', 'user_id', 'task_type_id', 'title', 'assign_to', 'staff_member', 'form_template_id', 'due_date', 'scheduled_date', 'scheduled_time', 'priority', 'description', 'complete_notes', 'status', 'deleted_at'];

    public function assigns()
    {
        return $this->belongsTo(user::class, 'assign_to', 'id');
    }

    public function staffMembers()
    {
        return $this->belongsTo(user::class, 'staff_member', 'id');
    }
    public function stafftasktype()
    {
        return $this->belongsTo(StaffTaskType::class, 'task_type_id', 'id');
    }
}
