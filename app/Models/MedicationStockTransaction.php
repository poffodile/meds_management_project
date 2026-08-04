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
                    /*
                     * THE TRUE arithmetic, which may be negative (issue I16).
                     *
                     * This was `max(0, $beforeNum - $quantity)`. Administering a 5ml
                     * dose against 3ml of recorded stock produced a ledger row reading
                     * balance_before 3.00, quantity 5.00, balance_after 0.00 — three
                     * minus five presented as zero. A ledger whose own three numbers
                     * contradict each other cannot be reconciled or investigated, and on
                     * a controlled drug it manufactures exactly the unbalanced register
                     * the discrepancy workflow exists to detect.
                     *
                     * The dose is still recorded. Refusing to record a dose that was
                     * physically given would be the worse error — the same principle
                     * applied where a prescription has no structured dose quantity.
                     * What must not happen is the RECORD being right while the LEDGER
                     * quietly lies.
                     */
                    $after = $beforeNum - $quantity;
                    // Batch tracking (#30 v2) — draw down the earliest-expiry batches first (FEFO).
                    self::consumeBatchesFefo($sheet, $quantity);
            }

            /*
             * `mar_sheets.stock_level` is decimal(10,3) UNSIGNED, so it physically
             * cannot hold a negative and strict mode would reject the write. The
             * balance therefore floors at zero — but that flooring is a real
             * discrepancy, and it is recorded as one below rather than swallowed.
             */
            $shortfall = $after < 0 ? abs($after) : 0.0;

            // Keep the fraction. stock_level is decimal(10,3) — rounding to int here
            // was losing 0.5 ml on every 7.5 ml dose, so the ledger (which records the
            // true quantity) and the balance drifted apart a little more each time.
            // Rounded to 3dp only to match the column and avoid float representation
            // noise accumulating over many doses.
            $sheet->stock_level = round(max(0, $after), 3);
            $sheet->save();

            $transaction = self::create(array_merge([
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

            /*
             * The floor, written down.
             *
             * Without this the ledger's last balance_after (say −2.00) would disagree
             * with the stock level actually held (0.000), which is a fresh
             * inconsistency in place of the old one. This second entry closes the gap
             * explicitly: −2.00 → 0.00, labelled as a shortfall, with the amount named.
             *
             * The specification's rule is "prevent silent balance changes" and
             * "preserve the original stock history". A discrepancy is allowed to
             * exist — it is real — but it has to be visible and explained.
             *
             * NOT YET DONE: nobody is notified. The specification requires a stock
             * discrepancy to reach a shift lead or manager. Tracked in the issue log.
             */
            if ($shortfall > 0) {
                self::create(array_merge([
                    'home_id'              => $sheet->home_id,
                    'mar_sheet_id'         => $sheet->id,
                    'client_id'            => $sheet->client_id,
                    'medication_name'      => $sheet->medication_name,
                    'transaction_type'     => 'correction',
                    'quantity'             => $shortfall,
                    'balance_before'       => $after,
                    'balance_after'        => 0,
                    'unit'                 => $sheet->dose,
                    'reason'               => 'Stock count short by '.rtrim(rtrim(number_format($shortfall, 3, '.', ''), '0'), '.')
                        .' — more was administered than the recorded balance held. Balance floored at zero; the count needs checking.',
                    'performed_by_user_id' => $userId,
                    'transaction_date'     => now(),
                ], array_intersect_key($extra, array_flip(['client_name']))));
            }

            return $transaction;
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
