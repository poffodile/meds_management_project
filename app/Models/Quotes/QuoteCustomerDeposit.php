<?php

namespace App\Models\Quotes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteCustomerDeposit extends Model
{
    use HasFactory;


    protected $fillable = [
        'quote_id', 
        'customer_id',
        'deposit_percantage',
        'amount',
        'reference', 
        'description', 
        'payment_type', 
        'deposit_date'
    ];




}
    