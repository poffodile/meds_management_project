<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderInvoiceReceives extends Model
{
    use HasFactory;
    protected $table="purchase_order_invoice_receives";
    protected $fillable=['home_id', 'loginUserId', 'po_id', 'supplier_id', 'inv_ref', 'net_amount', 'vat_id', 'vat_amount', 'gross_amount', 'invoice_date', 'due_date', 'notes', 'file', 'original_file_name', 'deleted_at'];

    public static function purchaseOrderInvoiceReceives_save($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
}
