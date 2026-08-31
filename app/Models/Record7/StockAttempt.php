<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One stock act, once.
 *
 * A receipt, a count, a waste and a return each arrive from a plain form post
 * and each create or destroy quantity, so a double submit doubles a delivery. A
 * time window would be the wrong answer for the same reason it was wrong for
 * PRN: two genuine receipts on one afternoon are not a duplicate. The question
 * is not "does this look like the last one" but "is this the same attempt",
 * which only the server can answer by issuing the identity itself.
 *
 * The identity fields are frozen on insert and a spent attempt cannot be
 * changed at all, in the model and in the database. A correction needs none of
 * this: it must name one approved review item, and that link is unique.
 */
class StockAttempt extends Record7Model
{
    protected $table = 'record7_stock_attempts';

    protected $fillable = [
        'token', 'organisation_id', 'service_id', 'stock_balance_id', 'action',
        'issued_to_user_id', 'issued_at', 'consumed_at', 'stock_movement_id',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    /** What an attempt is may never change, and a spent one not at all. */
    private const FROZEN = [
        'token', 'organisation_id', 'service_id', 'stock_balance_id',
        'action', 'issued_to_user_id', 'issued_at',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $attempt) {
            if ($attempt->getOriginal('consumed_at') !== null) {
                throw new RuntimeException('A spent stock attempt cannot be changed.');
            }

            foreach (self::FROZEN as $field) {
                if ($attempt->isDirty($field)) {
                    throw new RuntimeException('A stock attempt cannot be re-pointed.');
                }
            }
        });

        static::deleting(function () {
            throw new RuntimeException('A stock attempt cannot be deleted.');
        });
    }

    public function isSpent(): bool
    {
        return $this->consumed_at !== null;
    }

    public function balance(): BelongsTo
    {
        return $this->belongsTo(StockBalance::class, 'stock_balance_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }
}
