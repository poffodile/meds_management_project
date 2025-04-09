<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'quote_id',
        'product_id',
        'product_code',
        'title',
        'decritption',
        'account_code',
        'quantity',
        'cost_price',
        'price',
        'markup',
        'VAT',
        'discount',
        'discount_type',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
    public static function editQuotePageProduct($quote_id){
        return self::where('quote_id', $quote_id)->get();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
