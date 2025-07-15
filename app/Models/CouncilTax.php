<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouncilTax extends Model
{
    use HasFactory;

    protected $table = "council_tax";

    protected $fillable = [
        'flat_number',
        'address',
        'post_code',
        'council',
        'no_of_bedrooms',
        'owned_by_omega',
        'occupancy',
        'exempt',
        'account_number',
        'last_bill_date',
        'bill_period_start_date',
        'bill_period_end_date',
        'amount_paid',
        'additional',
    ];
}
