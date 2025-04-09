<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_ref',
        'user_id',
        'lead_task_type_id',
        'title',
        'contact_name',
        'contact_phone',
        'create_date',
        'create_time',
        'notification',
        'email_notify',
        'sms_notify',
        'notify_date',
        'notify_time',
        'notes',
        'is_completed'
    ];

    public static function getLeadTaskTypeUser($lead_ref, $type)
    {
        return DB::table('lead_tasks')
            ->join('lead_task_types', 'lead_task_types.id', '=', 'lead_tasks.lead_task_type_id')
            ->join('user', 'user.id', '=', 'lead_tasks.user_id')
            ->select('lead_tasks.*', 'lead_task_types.title as task_type_title', 'user.name')
            ->where('lead_tasks.lead_ref', $lead_ref)
            ->orderBy('lead_tasks.created_at', 'desc')
            ->where('lead_tasks.deleted_at', null)
            ->where('lead_tasks.is_completed', $type)
            ->get();
    }

    public static function getLeadTasks($type)
    {
        return DB::table('lead_tasks')
            ->join('leads', 'leads.lead_ref', '=', 'lead_tasks.lead_ref')
            ->join('user', 'user.id', '=', 'lead_tasks.user_id')
            ->join('lead_task_types', 'lead_task_types.id', '=', 'lead_tasks.lead_task_type_id')
            ->join('customers', 'customers.id', '=', 'leads.customer_id')
            ->select('lead_tasks.*', 'user.name', 'lead_task_types.title as lead_task_type_title', 'leads.id as lead_id', 'customers.contact_name', 'customers.telephone')
            ->orderBy('lead_tasks.created_at', 'desc')
            ->where('lead_tasks.is_completed', $type)
            ->where('lead_tasks.deleted_at', null)
            ->get();
    }

    public static function deleteLeadTask($taskId)
    {
        return LeadTask::where('id', $taskId)->update(['deleted_at' => Carbon::now()]);
    }

    public static function taskMarkAsCompleted($id)
    {
        return LeadTask::where('id', $id)->update(['is_completed' => true]);
    }
}
