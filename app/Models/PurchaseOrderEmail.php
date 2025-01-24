<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderEmail extends Model
{
    use HasFactory;
    protected $table="purchase_order_emails";
    protected $fillable=['home_id', 'loginUserId', 'po_id', 'to', 'cc', 'subject', 'defaultSelect', 'body', 'deleted_at'];

    public static function saveEmail($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
}
