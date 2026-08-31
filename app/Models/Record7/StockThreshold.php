<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The reorder level for one balance — configuration, not ledger history.
 *
 * NOTHING IS INVENTED. Record7 has never held an authoritative reorder level:
 * the figures in the retired `record7_stock_levels` were written by a seeder
 * and have no screen, owner or policy behind them, so they were not carried
 * over. A threshold exists only where a person with `stock_management` has
 * recorded one.
 *
 * NO ROW MEANS NO RULE, and no rule is not the same as healthy. Where this is
 * absent, `stock_low` is unavailable rather than false, and the screens say so
 * rather than showing a reassuring blank.
 *
 * This holds only the CURRENT rule. Its history lives in the append-only access
 * audit, which cannot be rewritten.
 */
class StockThreshold extends Record7Model
{
    protected $table = 'record7_stock_thresholds';

    protected $fillable = [
        'stock_balance_id', 'low_threshold', 'set_by_user_id', 'set_at', 'note',
    ];

    protected $casts = [
        'low_threshold' => 'decimal:3',
        'set_at' => 'datetime',
    ];

    public function balance(): BelongsTo
    {
        return $this->belongsTo(StockBalance::class, 'stock_balance_id');
    }

    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by_user_id');
    }
}
