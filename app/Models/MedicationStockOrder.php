<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A reorder line raised against a low medicine (#32).
 * ordered → received (books stock in) / cancelled.
 */
class MedicationStockOrder extends Model
{
    protected $table = 'medication_stock_orders';

    protected $fillable = [
        'home_id',
        'mar_sheet_id',
        'client_id',
        'medication_name',
        'quantity',
        'received_quantity',
        'supplier',
        'status',
        'notes',
        'created_by_user_id',
        'ordered_at',
        'received_at',
    ];

    protected $casts = [
        'home_id'             => 'integer',
        'mar_sheet_id'        => 'integer',
        'client_id'           => 'integer',
        'created_by_user_id'  => 'integer',
        'quantity'            => 'decimal:2',
        'received_quantity'   => 'decimal:2',
        'ordered_at'          => 'datetime',
        'received_at'         => 'datetime',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('medication_stock_orders.home_id', $homeId);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'ordered');
    }
}
