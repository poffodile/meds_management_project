<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * How much of one medicine is in one house's cupboard.
 *
 * Per house, never per organisation: two houses in the same company hold their
 * own stock and count it separately, and a manager standing in one must never
 * be shown the other's shortage.
 */
class StockLevel extends Record7Model
{
    protected $table = 'record7_stock_levels';

    protected $casts = [
        'last_counted_at' => 'datetime',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function isOut(): bool
    {
        return $this->quantity <= 0;
    }

    public function isLow(): bool
    {
        return ! $this->isOut() && $this->quantity <= $this->low_threshold;
    }
}
