<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreInvoice extends Model
{
    use HasFactory;
    protected $table="pre_invoices";
    protected $fillable=['loggedUserId', 'home_id', 'child_id', 'start_date', 'end_date', 'no_of_days', 'current_rate', 'total_cost', 'vat','deleted_at'];

    public static function savePreInvoice($data){
        return self::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }

    public function preInvoiceSubs(){
        return $this->hasMany(PreSubsInvoice::class, 'current_id', 'id')->whereNull('deleted_at');
    }
    public function preInvoiceAdditionalHours(){
        return $this->hasMany(PreInvoiceAdditionalHour::class, 'current_id', 'id')->whereNull('deleted_at');
    }
    public function preInvoiceExtrasWeeklies(){
        return $this->hasMany(PreInvoiceExtrasWeekly::class, 'current_id', 'id')->whereNull('deleted_at');
    }
    public function preInvoiceExtrasOneOffs(){
        return $this->hasMany(PreInvoiceExtrasOneOff::class, 'current_id', 'id')->whereNull('deleted_at');
    }
}
