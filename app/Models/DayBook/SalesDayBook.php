<?php

namespace App\Models\DayBook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDayBook extends Model
{
    use HasFactory;

    protected $fillable = ['home_id', 'customer_id', 'date', 'invoice_no', 'netAmount', 'Vat', 'vatAmount','grossAmount', 'deleted_at'];
}
