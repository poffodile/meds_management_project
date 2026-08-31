<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PARTLY RETIRED BY SECTION 2.7. One kind of row is still live.
 *
 * `count` and `discrepancy` are history and nothing more. They were resolved by
 * writing `resolved_at`, which meant a shortage stopped existing because
 * somebody typed a sentence — fixture row 90 is a senna count two tablets short,
 * closed with "Found recorded on the wrong chart. Balance corrected at the next
 * count", where no balance was corrected and no corrective record exists. Those
 * live in `record7_stock_movements` now, where only a correction ends one, and
 * the database refuses new rows and any update to the old ones.
 *
 * `delivery_overdue` is still written and still resolved here, and that is not
 * an exception to the rule. It asserts no quantity: the condition is "the
 * pharmacy has not delivered" and the fact that ends it is "it arrived". A
 * workflow act and the condition genuinely coincide, in a way they never do for
 * a balance, and nothing about closing it can make a missing quantity cease to
 * exist because it never claimed one.
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
