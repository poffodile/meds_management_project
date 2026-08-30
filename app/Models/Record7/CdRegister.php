<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One movement of controlled stock.
 *
 * Append-only in the strict sense: nothing about an entry ever changes, and
 * nothing is ever removed. Where a figure was wrong, a correction is added that
 * names the entry it corrects, and both stay on the record — because the error
 * is part of what happened and hiding it is how a register stops being one.
 *
 * The balance is computed by the database, not here. This class never decides
 * what a figure should be; it only carries what the ledger recorded.
 */
class CdRegister extends Record7Model
{
    protected $table = 'record7_cd_register';

    protected $fillable = [
        'reference',
        'organisation_id', 'service_id', 'client_id', 'medicine_id', 'prescription_id',
        'medicine_name_at_time', 'form_at_time', 'strength_at_time', 'unit', 'cd_schedule_at_time',
        'action',
        'quantity_received', 'quantity_removed', 'quantity_given',
        'quantity_returned', 'quantity_wasted',
        'expected_quantity', 'counted_quantity',
        'balance_before', 'balance_after', 'is_discrepancy',
        'recorded_by_user_id', 'witnessed_by_user_id',
        'witness_was_required', 'unwitnessed_basis',
        'witness_name_at_time', 'witness_role_at_time',
        'occurred_at', 'corrects_register_id', 'notes', 'sequence_no',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:3',
        'quantity_removed' => 'decimal:3',
        'quantity_given' => 'decimal:3',
        'quantity_returned' => 'decimal:3',
        'quantity_wasted' => 'decimal:3',
        'expected_quantity' => 'decimal:3',
        'counted_quantity' => 'decimal:3',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'is_discrepancy' => 'boolean',
        'witness_was_required' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    /** Why a movement happened, in words a person would use. */
    public const ACTION_WORDS = [
        'receipt' => 'Came in',
        'administration' => 'Given',
        'non_administration' => 'Taken out but not given',
        'return_to_storage' => 'Put back',
        'waste' => 'Disposed of',
        'stock_check' => 'Counted',
        'correction' => 'Correction',
    ];

    /** Why a movement legitimately had no witness. Structured, never free text. */
    public const UNWITNESSED_REASONS = [
        'setting_does_not_require' => 'This is where the person lives, so a second signature '
            .'is not required by the setting',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException(
                'A controlled drug register entry is permanent. Record a correction '
                .'that refers to it instead of changing it.'
            );
        });

        static::deleting(function () {
            throw new RuntimeException('A controlled drug register entry cannot be deleted.');
        });
    }

    public function actionWord(): string
    {
        return self::ACTION_WORDS[$this->action] ?? $this->action;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function witnessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witnessed_by_user_id');
    }

    public function corrects(): BelongsTo
    {
        return $this->belongsTo(self::class, 'corrects_register_id');
    }
}
