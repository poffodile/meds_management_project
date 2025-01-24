<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReminder extends Model
{
    use HasFactory;
    protected $table="purchase_reminders";
    protected $fillable=['home_id', 'loginUserId', 'po_id', 'user_id', 'reminder_date', 'reminder_time', 'notification', 'sms', 'email', 'title', 'notes', 'status', 'deleted_at'];

    public static function saveReminder($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
    public static function allReminderData($home_id){
        return self::where('home_id',$home_id);
    }
}
