<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInvoiceExtrasOneOff extends Model
{
    use HasFactory;
    protected $table="pre_invoice_extras_one_offs";
    protected $fillable=['loggedUserId', 'home_id', 'child_id', 'current_id', 'extras_oneoff_expenditure_type', 'extras_oneoff_start_date', 'extras_oneoff_amount', 'extras_oneoff_total_cost', 'deleted_at'];

    public static function savePreInvoiceExtrasOneOff($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
}
