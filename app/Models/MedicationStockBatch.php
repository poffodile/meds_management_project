<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single batch / lot of a medicine (#30). Each has its own quantity + expiry;
 * stock rolls up from the batches and they're consumed First-Expiry-First-Out.
 */
class MedicationStockBatch extends Model
{
    protected $table = 'medication_stock_batches';

    protected $fillable = [
        'home_id',
        'mar_sheet_id',
        'client_id',
        'batch_number',
        'quantity',
        'received_quantity',
        'expiry_date',
        'supplier',
        'is_depleted',
        'performed_by_user_id',
        'received_at',
    ];

    protected $casts = [
        'home_id'              => 'integer',
        'mar_sheet_id'         => 'integer',
        'client_id'            => 'integer',
        'performed_by_user_id' => 'integer',
        'quantity'             => 'decimal:2',
        'received_quantity'    => 'decimal:2',
        'expiry_date'          => 'date',
        'is_depleted'          => 'boolean',
        'received_at'          => 'datetime',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('medication_stock_batches.home_id', $homeId);
    }

    /** Live batches for a sheet, ordered First-Expiry-First-Out (undated last). */
    public function scopeForSheetFefo($query, int $marSheetId)
    {
        return $query->where('mar_sheet_id', $marSheetId)
            ->where('is_depleted', false)
            ->where('quantity', '>', 0)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->orderBy('id');
    }
}
