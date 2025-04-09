<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteAllocate extends Model
{
    use HasFactory;
    protected $table="credit_note_allocates";
    protected $fillable=['home_id', 'loginUserId', 'loginUserName', 'po_id', 'product_id', 'supplier_id', 'credit_id', 'amount_paid', 'date', 'deleted_at'];

    public static function saveCreditAllocate($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
}
