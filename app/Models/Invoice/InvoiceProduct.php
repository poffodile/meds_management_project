<?php

namespace App\Models\Invoice;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    use HasFactory;
    protected $fillable = ['home_id','invoice_id','customer_id','product_id','code','accountcode','description','qty','price','discount','discount_type','vat_id','vat','deleted_at'];
    public static function saveInvoiceProduct($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null],$data);
    }
}
