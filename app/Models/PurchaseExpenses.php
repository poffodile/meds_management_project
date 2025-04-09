<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseExpenses extends Model
{
    use HasFactory;

    protected $fillable=['title', 'status', 'purchase_expense_id'];
}
