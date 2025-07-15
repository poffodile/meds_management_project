<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInvoiceAdditionalHour extends Model
{
    use HasFactory;
    protected $table="pre_invoice_additional_hours";
    protected $fillable=['loggedUserId', 'home_id', 'child_id', 'current_id', 'addHour_start_date', 'addHour_end_date', 'addHour_no_of_days', 'addHour_rate', 'addHour_total_cost', 'additional_per_week', 'deleted_at'];

    public static function savePreInvoiceAdditionalHour($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
}
