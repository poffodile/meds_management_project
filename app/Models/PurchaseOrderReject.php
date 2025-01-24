<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReject extends Model
{
    use HasFactory;
    protected $table="purchase_order_rejects";
    protected $fillable=['home_id', 'loginUserId', 'po_id', 'message', 'notify_user_id', 'notification', 'sms', 'email', 'deleted_at'];

    public static function savePurchaseOrderReject($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
}
