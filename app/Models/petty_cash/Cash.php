<?php

namespace App\Models\petty_cash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    use HasFactory;
    protected $table="cashes";
    protected $fillable=['home_id', 'loginUserId', 'cash_date', 'balance_bfwd', 'petty_cashIn', 'cash_out', 'card_details', 'receipt', 'fileName', 'dext', 'invoice_la', 'initial', 'deleted_at'];

    public static function saveCash($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
    public static function getAllCash(){
        return self::whereNull('deleted_at');
    }
}
