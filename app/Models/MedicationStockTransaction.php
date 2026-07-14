<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MedicationStockTransaction extends Model
{
    protected $table = 'medication_stock_transactions';

    protected $fillable = [
        'home_id',
        'mar_sheet_id',
        'client_id',
        'client_name',
        'medication_name',
        'transaction_type',
        'quantity',
        'balance_before',
        'balance_after',
        'unit',
        'reason',
        'disposal_method',
        'witness_name',
        'notes',
        'performed_by_user_id',
        'transaction_date',
    ];

    protected $casts = [
        'home_id'              => 'integer',
        'mar_sheet_id'         => 'integer',
        'client_id'            => 'integer',
        'performed_by_user_id' => 'integer',
        'quantity'             => 'decimal:2',
        'balance_before'       => 'decimal:2',
        'balance_after'        => 'decimal:2',
        'transaction_date'     => 'datetime',
    ];

    public function performedByUser()
    {
        return $this->belongsTo(\App\User::class, 'performed_by_user_id');
    }

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('medication_stock_transactions.home_id', $homeId);
    }

    /**
     * Apply a stock movement: update the MAR sheet's stock level and log a transaction.
     * Returns the created transaction.
     *
     * $type: received | administered | disposed | returned | correction
     * For 'correction', $quantity is the new absolute count; otherwise it's the amount moved.
     */
    public static function apply(MARSheet $sheet, string $type, float $quantity, int $userId, array $extra = []): self
    {
        return DB::transaction(function () use ($sheet, $type, $quantity, $userId, $extra) {
            $before    = $sheet->stock_level;          // may be null if never tracked
            $beforeNum = $before ?? 0;

            switch ($type) {
                case 'received':
                    $after = $beforeNum + $quantity;
                    break;
                case 'correction':
                    $after = $quantity;                // recount to an absolute value
                    break;
                default: // administered, disposed, returned
                    $after = max(0, $beforeNum - $quantity);
                    // Batch tracking (#30 v2) — draw down the earliest-expiry batches first (FEFO).
                    self::consumeBatchesFefo($sheet, $quantity);
            }

            $sheet->stock_level = (int) round($after);
            $sheet->save();

            return self::create(array_merge([
                'home_id'              => $sheet->home_id,
                'mar_sheet_id'         => $sheet->id,
                'client_id'            => $sheet->client_id,
                'medication_name'      => $sheet->medication_name,
                'transaction_type'     => $type,
                'quantity'             => $quantity,
                'balance_before'       => $before,
                'balance_after'        => $after,
                'unit'                 => $sheet->dose,
                'performed_by_user_id' => $userId,
                'transaction_date'     => now(),
            ], $extra));
        });
    }

    /**
     * Draw down live batches First-Expiry-First-Out for a consuming movement
     * (administered / disposed / returned). Additive + safe: a no-op when the
     * batches table is absent or the sheet has no batches. Any quantity beyond
     * tracked batches is simply left untracked (stock_level stays authoritative).
     */
    protected static function consumeBatchesFefo(MARSheet $sheet, float $quantity): void
    {
        if ($quantity <= 0 || ! Schema::hasTable('medication_stock_batches')) {
            return;
        }

        $remaining = $quantity;
        $batches = \App\Models\MedicationStockBatch::forSheetFefo($sheet->id)->lockForUpdate()->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            $take = min((float) $batch->quantity, $remaining);
            $batch->quantity = (float) $batch->quantity - $take;
            if ($batch->quantity <= 0) {
                $batch->quantity = 0;
                $batch->is_depleted = true;
            }
            $batch->save();
            $remaining -= $take;
        }
    }
}
