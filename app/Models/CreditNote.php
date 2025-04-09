<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;
    protected $table="credit_notes";
    protected $fillable=['home_id', 'loginUserId', 'supplier_id', 'credit_ref', 'contact_id', 'name', 'email', 'telephone_code', 'telephone', 'mobile_code', 'mobile', 'address', 'city', 'county', 'post_code', 'date', 'supplier_ref', 'status', 'supplier_notes', 'internal_notes', 'balance_credit', 'deleted_at'];

    public static function saveCreditNotes($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null],$data);
    }
    public function suppliers(){
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function creditNoteProducts(){
        return $this->hasMany(CreditNoteProduct::class, 'credi_note_id', 'id')->whereNull('deleted_at');
    }
}
