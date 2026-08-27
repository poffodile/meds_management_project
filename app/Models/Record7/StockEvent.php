<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A count, a discrepancy, or a delivery that has not arrived.
 *
 * A controlled-drug balance that does not match the book is the most serious
 * thing this table holds, which is why both the expected and the counted
 * figure are kept rather than a note saying they disagreed.
 */
class StockEvent extends Record7Model
{
    protected $table = 'record7_stock_events';

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function isOutstanding(): bool
    {
        return $this->resolved_at === null;
    }

    /** How far out the balance is, when that is the point of the event. */
    public function difference(): ?int
    {
        if ($this->expected_quantity === null || $this->counted_quantity === null) {
            return null;
        }

        return (int) $this->counted_quantity - (int) $this->expected_quantity;
    }
}
