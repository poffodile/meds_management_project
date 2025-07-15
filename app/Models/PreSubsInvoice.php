<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreSubsInvoice extends Model
{
    use HasFactory;
    protected $table="pre_subs_invoices";
    protected $fillable=['loggedUserId', 'home_id', 'child_id', 'current_id', 'subs_start_date', 'subs_end_date', 'subs_no_of_days', 'subs_rate', 'subs_total_cost', 'deleted_at'];

    public static function savePreSubsInvoice($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
}
