<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The current figure for one person's one preparation of an ordinary medicine.
 *
 * DERIVED, NOT HISTORY. Everything here can be rebuilt from the ledger, and a
 * test asserts it agrees. This row exists for one reason: a lock needs
 * something to hold on to, and "is there enough" has to be settled while
 * holding one or it is worthless — two workers who both read "one sachet"
 * before either writes will both conclude there is enough.
 *
 * The reorder level is NOT here. A balance is derived and a threshold is
 * configuration, and a rebuild-from-ledger repair that had to carefully
 * preserve three configuration columns is how configuration gets lost.
 */
class StockBalance extends Record7Model
{
    protected $table = 'record7_stock_balances';

    protected $fillable = [
        'organisation_id', 'service_id', 'owner_type', 'client_id', 'medicine_id',
        'preparation_key', 'unit',
        'current_balance', 'last_sequence_no', 'last_movement_id', 'last_counted_at',
    ];

    protected $casts = [
        'current_balance' => 'decimal:3',
        'last_sequence_no' => 'integer',
        'last_counted_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function lastMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'last_movement_id');
    }

    public function threshold(): HasOne
    {
        return $this->hasOne(StockThreshold::class, 'stock_balance_id');
    }

    /** Nothing left. Needs no configuration to be true. */
    public function isOut(): bool
    {
        return (float) $this->current_balance <= 0;
    }

    /**
     * Below the level this house keeps — where a level has been recorded.
     *
     * Returns false where no threshold exists, and callers must not read that
     * as healthy: `hasThreshold()` is the question that separates "we are fine"
     * from "nobody has said what fine means". Nothing is invented here.
     */
    public function isLow(): bool
    {
        $threshold = $this->threshold;

        if ($threshold === null || $this->isOut()) {
            return false;
        }

        return (float) $this->current_balance <= (float) $threshold->low_threshold;
    }

    public function hasThreshold(): bool
    {
        return $this->threshold !== null;
    }
}
