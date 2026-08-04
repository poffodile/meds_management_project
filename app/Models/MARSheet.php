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
        'dose_quantity',
        'form',
        'unit',
        'route',
        'frequency',
        'time_slots',
        'as_required',
        'prn_details',
        // The PRN safety limits. These were missing here and from every validation list,
        // so no prescription created through the product could carry them — only the
        // seeder's direct DB writes. The enforcement in applyRecord() was therefore
        // unreachable for every real resident while appearing to work in the demo.
        'prn_max_daily',
        'prn_min_interval_hours',
        'reason_for_medication',
        'administration_instructions',   // "how to give it" directive (issue #29 / C1)
        'prescribed_by',
        'prescriber',
        'pharmacy',
        'start_date',
        'end_date',
        'expiry_date',
        'is_controlled',
        'cd_schedule',
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
        'expiry_date' => 'date',
        'is_controlled' => 'boolean',
        'discontinued_date' => 'date',
        'last_audited' => 'date',
        // Quantities are decimal(10,3): a dose can be 7.5 ml, so a stock balance can
        // be 52.5. These were 'integer', which silently rounded liquids.
        'dose_quantity' => 'float',
        'prn_max_daily' => 'integer',
        'prn_min_interval_hours' => 'float',
        'stock_level' => 'float',
        'reorder_level' => 'float',
        'quantity_received' => 'float',
        'quantity_carried_forward' => 'float',
        'quantity_returned' => 'float',
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
