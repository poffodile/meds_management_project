<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MARSheet extends Model
{
    protected $table = 'mar_sheets';

    protected $fillable = [
        'client_id',
        'medication_name',
        'dosage',
        'dose',
        'route',
        'frequency',
        'time_slots',
        'as_required',
        'prn_details',
        'reason_for_medication',
        'prescribed_by',
        'prescriber',
        'pharmacy',
        'start_date',
        'end_date',
        'stock_level',
        'reorder_level',
        'storage_requirements',
        'allergies_warnings',
        'quantity_received',
        'quantity_carried_forward',
        'quantity_returned',
        'mar_status',
        'discontinued',
        'discontinued_date',
        'discontinued_reason',
        'last_audited',
    ];

    protected $casts = [
        'time_slots' => 'array',
        'as_required' => 'boolean',
        'discontinued' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'discontinued_date' => 'date',
        'last_audited' => 'date',
        'stock_level' => 'integer',
        'reorder_level' => 'integer',
        'quantity_received' => 'integer',
        'quantity_carried_forward' => 'integer',
        'quantity_returned' => 'integer',
        'home_id' => 'integer',
        'client_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function scopeForHome($query, $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function scopeCurrentlyActive($query)
    {
        return $query->where('mar_status', 'active');
    }

    public function administrations()
    {
        return $this->hasMany(MARAdministration::class, 'mar_sheet_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}
