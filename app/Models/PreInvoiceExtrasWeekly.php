<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInvoiceExtrasWeekly extends Model
{
    use HasFactory;
    protected $table="pre_invoice_extras_weeklies";
    protected $fillable=['loggedUserId', 'home_id', 'child_id', 'current_id', 'extras_weekly_start_date', 'extras_weekly_end_date', 'extras_weekly_expenditure_type', 'extras_weekly_no_of_days', 'extras_weekly_amount', 'extras_weekly_total_cost', 'deleted_at'];

    public static function savePreInvoiceExtrasWeekly($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
}
