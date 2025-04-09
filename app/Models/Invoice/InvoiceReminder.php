<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReminder extends Model
{
    use HasFactory;
    protected $table="invoice_reminders";
    protected $fillable=['home_id', 'loginUserId', 'invoice_id', 'user_id', 'reminder_date', 'reminder_time', 'notification', 'sms', 'email', 'title', 'notes', 'status', 'deleted_at'];

    public static function saveReminder($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
    public static function allInvoiceReminderData($home_id){
        return self::where('home_id',$home_id)->whereNull('deleted_at');
    }
}
