<?php

namespace App\Models\DayBook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDayBook extends Model
{
    use HasFactory;

    protected $fillable = [
                'home_id',
                'supplier_id',
                'date',
                'netAmount',
                'Vat', 
                'vatAmount',
                'grossAmount', 
                'reclaim', 
                'not_reclaim', 
                'expense_type', 
                'expense_amount',
                'deleted_at'
            ];

}
