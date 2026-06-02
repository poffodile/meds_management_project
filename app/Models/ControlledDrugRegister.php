<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlledDrugRegister extends Model
{
    protected $table = 'controlled_drug_register';

    protected $fillable = [
        'home_id',
        'client_id',
        'client_name',
        'mar_sheet_id',
        'medication_name',
        'cd_schedule',
        'action_type',
        'entry_date',
        'entry_time',
        'dose_quantity',
        'unit',
        'balance_before',
        'balance_after',
        'witness_name',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'home_id'            => 'integer',
        'client_id'          => 'integer',
        'mar_sheet_id'       => 'integer',
        'created_by_user_id' => 'integer',
        'entry_date'         => 'date',
        'dose_quantity'      => 'decimal:2',
        'balance_before'     => 'decimal:2',
        'balance_after'      => 'decimal:2',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('controlled_drug_register.home_id', $homeId);
    }
}
