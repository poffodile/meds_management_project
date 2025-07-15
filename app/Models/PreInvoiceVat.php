<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInvoiceVat extends Model
{
    use HasFactory;
    protected $table="pre_invoice_vats";
    protected $fillable=['vat','deleted_at'];
}
