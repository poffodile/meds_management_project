<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceNewTask extends Model
{
    use HasFactory;
    protected $table="invoice_new_tasks";
    protected $fillable=['home_id', 'invoice_id', 'customer_id', 'user_id', 'title', 'task_type_id', 'start_date', 'start_time', 'end_date', 'end_time', 'is_recurring', 'notify', 'notify_date', 'notify_time', 'notification', 'email', 'sms', 'notes	', 'status', 'deleted_at'];

    public static function saveReminderNewTask($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
}
