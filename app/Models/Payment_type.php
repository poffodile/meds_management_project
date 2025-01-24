<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Payment_type extends Model
{
    use HasFactory;
    protected $table="payment_types";
    protected $fillable=['home_id','title','mobile_visible','status'];

    public static function getAllPayment_type(){
        $data = self::whereNull('deleted_at')->get();
        return $data;
    }

    public static function savePayment_type($data)
    {
        $payment_type=self::updateOrCreate(['id' => $data['id'] ?? null],$data);
        return $payment_type;
    }

    public static function getActivePaymentType($home_id){
        return self::select('id', 'title')->where('home_id', $home_id)->where('status', 1)->whereNull('deleted_at')->get();   
    }
}
