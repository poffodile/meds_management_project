<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One movement of ordinary stock.
 *
 * Append-only in the strict sense: nothing about a movement ever changes, and
 * nothing is ever removed. Where a figure was wrong, a correction is added that
 * names the movement it corrects, and both stay on the record — because the
 * error is part of what happened and hiding it is how a ledger stops being one.
 *
 * The balance is computed by the database, not here. This class never decides
 * what a figure should be; it only carries what the ledger recorded.
 *
 * Controlled medicines never appear here. Section 2.5's register is their sole
 * authority, and the insert trigger refuses one before any other check.
 */
class StockMovement extends Record7Model
{
    protected $table = 'record7_stock_movements';

    protected $fillable = [
        'reference',
        'organisation_id', 'service_id', 'owner_type', 'client_id',
        'medicine_id', 'prescription_id',
        'medicine_name_at_time', 'form_at_time', 'strength_at_time', 'unit',
        'action',
        'quantity_received', 'quantity_removed', 'quantity_given',
        'quantity_returned', 'quantity_wasted', 'quantity_delta',
        'expected_quantity', 'counted_quantity',
        'balance_before', 'balance_after', 'is_discrepancy',
        'shortfall_verified_by_user_id', 'shortfall_verified_at',
        'shortfall_basis', 'shortfall_statement', 'shortfall_observed_quantity',
        'recorded_by_user_id', 'witnessed_by_user_id',
        'witness_name_at_time', 'witness_role_at_time',
        'occurred_at', 'corrects_movement_id', 'review_item_id',
        'notes', 'sequence_no', 'imported', 'import_note',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_removed' => 'decimal:3',
        'quantity_given' => 'decimal:3',
        'quantity_returned' => 'decimal:3',
        'quantity_wasted' => 'decimal:3',
        'quantity_delta' => 'decimal:3',
        'expected_quantity' => 'decimal:3',
        'counted_quantity' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'shortfall_observed_quantity' => 'decimal:3',
        'is_discrepancy' => 'boolean',
        'imported' => 'boolean',
        'occurred_at' => 'datetime',
        'shortfall_verified_at' => 'datetime',
        'sequence_no' => 'integer',
    ];

    /** Why a movement happened, in words a person would use. */
    public const ACTION_WORDS = [
        'opening_balance' => 'Opening count',
        'receipt' => 'Came in',
        'administration' => 'Given',
        'non_administration' => 'Taken out but not given',
        'return_to_stock' => 'Put back',
        'waste' => 'Disposed of',
        'stock_check' => 'Counted',
        'correction' => 'Correction',
    ];

    /**
     * What somebody confirmed when the ledger said there was not enough.
     * Structured, never free text alone — the statement accompanies it.
     */
    public const SHORTFALL_BASES = [
        'physically_counted_sufficient' => 'I counted what is here and there is enough for this dose',
        'unrecorded_stock_present' => 'There is stock here that has not been booked in yet',
        'other' => 'Something else, described below',
    ];

    /** The verbs that a stock attempt token protects. */
    public const TOKENED_ACTIONS = [
        'opening_balance', 'receipt', 'stock_check', 'waste', 'return_to_stock',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException(
                'A stock movement is permanent. Record a correction that refers to it '
                .'instead of changing it.'
            );
        });

        static::deleting(function () {
            throw new RuntimeException('A stock movement cannot be deleted.');
        });
    }

    public function actionWord(): string
    {
        return self::ACTION_WORDS[$this->action] ?? $this->action;
    }

    /**
     * What this movement proves went wrong, where it proves anything.
     *
     * Signed, and never floored. A count short by two reads -2, which is the
     * whole point: a figure that cannot go negative cannot describe a shortage.
     */
    public function difference(): ?float
    {
        if ($this->action === 'stock_check'
            && $this->counted_quantity !== null
            && $this->expected_quantity !== null) {
            return (float) $this->counted_quantity - (float) $this->expected_quantity;
        }

        // A dose given against a balance that could not cover it. The shortfall
        // is how far below zero the ledger ended up.
        if ($this->is_discrepancy && (float) $this->balance_after < 0) {
            return (float) $this->balance_after;
        }

        return null;
    }

    /** Why this entry is a disagreement, for a manager reading the board. */
    public function discrepancyCause(): ?string
    {
        if (! $this->is_discrepancy) {
            return null;
        }

        return $this->action === 'stock_check' ? 'count' : 'shortfall';
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function shortfallVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shortfall_verified_by_user_id');
    }

    public function corrects(): BelongsTo
    {
        return $this->belongsTo(self::class, 'corrects_movement_id');
    }

    public function reviewItem(): BelongsTo
    {
        return $this->belongsTo(ReviewItem::class, 'review_item_id');
    }
}
