<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets stock hold a fractional quantity, so liquid medicines stop drifting.
 *
 * Every quantity column on mar_sheets was `int unsigned`. That is fine while a
 * dose is "1 tablet", and wrong the moment it is "7.5 ml" — which is exactly what
 * the paediatric suspensions at Neptune are.
 *
 * The drift was demonstrable: a 7.5 ml dose recorded quantity_given = 7.500 in the
 * ledger, while MedicationStockTransaction::apply() rounded the new balance and
 * moved stock by 7. Reconciling the ledger against the balance therefore lost
 * 0.5 ml per dose, silently, forever. Recording the true quantity (CR-02) made
 * that visible; this makes it correct.
 *
 * Owner decision (2026-07-16): a medicine's quantity is expressed in the unit its
 * DOSE FORM implies — tablets in tablets, suspensions in ml, inhalers in puffs —
 * so that counting in, administering, returning and counting out all reconcile in
 * the same unit. An integer column silently forbids half of that.
 *
 * decimal(10,3) unsigned:
 *   - 3 dp covers 7.5 ml, 2.5 ml, 0.5 ml. It does not invite false precision.
 *   - unsigned preserves the existing "stock cannot go negative" guarantee at the
 *     column level, independent of the PHP-side floor in apply().
 *   - current max value across all five columns is 100, so widening cannot overflow.
 *
 * Existing integer values convert exactly (56 -> 56.000). No data is at risk.
 *
 * Schema came from a dump, so each change is guarded and re-runnable.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'stock_level',
        'reorder_level',
        'quantity_received',
        'quantity_carried_forward',
        'quantity_returned',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('mar_sheets')) {
            return;
        }

        foreach (self::COLUMNS as $col) {
            if (! Schema::hasColumn('mar_sheets', $col)) {
                continue;
            }
            if ($this->currentType($col) === 'decimal') {
                continue; // already converted
            }

            // Raw DDL: doctrine/dbal is not required for this and a plain MODIFY is
            // exact for int -> decimal widening.
            DB::statement("ALTER TABLE `mar_sheets` MODIFY COLUMN `{$col}` DECIMAL(10,3) UNSIGNED NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mar_sheets')) {
            return;
        }

        // Narrowing back to int would round away any fractional stock that has since
        // been recorded. Rounding is what this migration exists to stop, so the
        // rollback refuses rather than quietly destroying quantities.
        foreach (self::COLUMNS as $col) {
            if (! Schema::hasColumn('mar_sheets', $col)) {
                continue;
            }
            $fractional = DB::table('mar_sheets')
                ->whereNotNull($col)
                ->whereRaw("`{$col}` <> FLOOR(`{$col}`)")
                ->count();

            if ($fractional > 0) {
                throw new \RuntimeException(
                    "Refusing to roll back: mar_sheets.{$col} holds {$fractional} fractional value(s) "
                    .'that converting back to INT would silently round. Resolve those quantities first.'
                );
            }
        }

        foreach (self::COLUMNS as $col) {
            if (Schema::hasColumn('mar_sheets', $col)) {
                DB::statement("ALTER TABLE `mar_sheets` MODIFY COLUMN `{$col}` INT UNSIGNED NULL");
            }
        }
    }

    private function currentType(string $col): ?string
    {
        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['mar_sheets', $col]
        );

        return $row->DATA_TYPE ?? null;
    }
};
