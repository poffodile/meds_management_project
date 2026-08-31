<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RETIRED BY SECTION 2.7. Read only, and read by nothing.
 *
 * This held one mutable quantity per house per medicine. It could not say
 * whose supply had run out — Oakwood has two people prescribed macrogol and one
 * row between them — and it carried no history, no unit, no owner and nothing
 * to stop the figure being typed over.
 *
 * Ordinary balances are now derived from `record7_stock_movements` through
 * `StockBalance`. Nothing in the application reads this class any more, and the
 * database refuses every insert and update to the table behind it. The rows are
 * kept because they are the pre-2.7 record; 15 of the 18 are reseed debris, and
 * none of them was imported.
 *
 * @deprecated Section 2.7. Use StockBalance.
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
