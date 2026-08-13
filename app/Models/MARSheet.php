<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MARSheet extends Model
{
    protected $table = 'mar_sheets';

    protected $fillable = [
        'client_id',
        'medicine_id',
        'medication_name',
        'medication_name_as_written',
        'dose_amount',
        'dose_unit',
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
        'prescription_version',
        'prescription_source',
        'prescribed_at',
        'review_due_date',
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
        'prescribed_at' => 'datetime',
        'review_due_date' => 'date',
        'dose_amount' => 'float',
        'prescription_version' => 'integer',
        'medicine_id' => 'integer',
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

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where(function ($started) use ($date) {
            $started->whereNull('start_date')->orWhere('start_date', '<=', $date);
        })->where(function ($notEnded) use ($date) {
            $notEnded->whereNull('end_date')->orWhere('end_date', '>=', $date);
        });
    }

    public function medicine()
    {
        return $this->belongsTo(MedicineCatalogue::class, 'medicine_id');
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

