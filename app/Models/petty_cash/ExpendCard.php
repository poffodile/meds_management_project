<?php

namespace App\Models\petty_cash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpendCard extends Model
{
    use HasFactory;
    protected $fillable=['home_id', 'loginUserId', 'expend_date', 'balance_bfwd', 'fund_added', 'purchase_amount', 'card_details', 'receipt', 'fileName', 'dext', 'invoice_la', 'initial', 'deleted_at'];

    public static function saveExpenseCard($data){
        return self::updateOrCreate(['id'=>$data['id'] ?? null], $data);
    }
    public static function getAllExpendCard(){
        return self::whereNull('deleted_at');
    }
}
