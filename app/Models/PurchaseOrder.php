<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table="purchase_orders";
    protected $fillable=['home_id', 'user_id', 'supplier_id', 'contact_id', 'name', 'address', 'city', 'county', 'postcode', 'telephone_code', 'telephone', 'mobile_code', 'mobile', 'email', 'customer_id', 'project_id', 'site_id', 'user_name', 'company_name', 'user_address', 'user_city', 'user_county', 'user_post_code', 'user_telephone_code', 'user_telephone', 'user_mobile_code', 'user_mobile', 'expected_deleveryDate', 'department_id', 'purchase_date', 'reference', 'qoute_ref', 'job_ref', 'invoice_ref', 'payment_terms', 'payment_due_date', 'tag_id', 'status', 'delivery_status', 'supplier_notes', 'delivery_notes', 'internal_notes', 'attachment', 'file_original_name', 'purchase_order_ref', 'outstanding_amount', 'deleted_at'];

    public static function savePurchaseOrder($data){
        //  echo "<pre>";print_r($data);die;
         return self::updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );
    }
    public function suppliers(){
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function poAttachments(){
        return $this->hasMany(PoAttachment::class, 'po_id', 'id')->whereNull('deleted_at');
    }
    public function purchaseOrderProducts(){
        return $this->hasMany(PurchaseOrderProduct::class, 'purchase_order_id', 'id')->whereNull('deleted_at');
    }
}
