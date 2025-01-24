<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProduct extends Model
{
    use HasFactory;
    protected $table="purchase_order_products";
    protected $fillable=['user_id', 'purchase_order_id', 'job_id', 'product_id', 'code', 'description', 'accountCode_id', 'qty', 'price', 'vat_id', 'vat', 'deliverd_qty', 'quantity_available', 'outstanding_amount', 'receive_more', 'userType', 'deleted_at'];

    public static function savePurchaseOrderProduct($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
    public function purchaseOrders(){
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }
}
