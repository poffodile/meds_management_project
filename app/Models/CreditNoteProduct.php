<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNoteProduct extends Model
{
    use HasFactory;
    protected $table="credit_note_products";
    protected $fillable=['user_id', 'credi_note_id', 'job_id', 'product_id', 'code', 'description', 'accountCode_id', 'qty', 'price', 'vat_id', 'vat', 'deliverd_qty', 'quantity_available', 'outstanding_amount', 'receive_more', 'userType', 'deleted_at'];

    public static function saveCreditProduct($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
    public function creditNotes(){
        return $this->belongsTo(CreditNote::class, 'credi_note_id', 'id');
    }
}
