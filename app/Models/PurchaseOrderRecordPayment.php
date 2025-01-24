<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderRecordPayment extends Model
{
    use HasFactory;
    protected $table="purchase_order_record_payments";
    protected $fillable=['home_id', 'loginUserId', 'loginUserName', 'po_id', 'record_amount_paid', 'record_payment_date', 'record_payment_type', 'record_reference', 'record_description', 'deleted_at'];

    public static function savePurchaseOrderRecordPayment($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
}
