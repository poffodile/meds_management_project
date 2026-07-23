<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives a dose a STRUCTURED quantity, so stock deduction stops being derived
 * from free text.
 *
 * The bug this exists to kill (audit CR-02): deduction quantity was produced by
 * stripping every non-digit out of the dose string —
 *
 *     preg_replace('/[^0-9.]/', '', '10 ml (500mg)')  ===  '10500'
 *
 * so a single tap on "Given" deducted 10,500 units and the balance floored to
 * zero, silently. Verified live: stock 56 -> 0 in one tap.
 *
 *  - mar_sheets.dose_quantity   : how much of `unit` ONE dose consumes.
 *  - mar_administrations.quantity_given : what was actually deducted for this
 *    administration, recorded rather than recomputed, so the ledger can be
 *    audited against the prescription later.
 *
 * BACKFILL IS DELIBERATELY CONSERVATIVE. It only fills a value where the dose
 * string contains exactly one number and no distributive wording. Everything
 * else is left NULL, and a NULL dose_quantity means "do not auto-deduct" — see
 * the deduction guard in Concerns/BuildsMedicationRound::applyRecord().
 *
 * Specifically NOT auto-parsed:
 *   '10 ml (500mg)'         two numbers — volume vs strength. Which one is the
 *                           consumed quantity is a clinical reading, not a
 *                           regex's guess. (It is 10, but the string
 *                           '500mg (10ml)' would be equally valid and would
 *                           give the opposite answer.)
 *   '1 spray each nostril'  one number, but the consumed quantity is TWO
 *                           sprays. "each"/"per" wording defeats the count.
 *
 * Those need a pharmacist-reviewed mapping (audit DA-04), not a cleverer parser.
 * Leaving them NULL means their stock stops auto-decrementing until they are set
 * — inaccurate, but honestly inaccurate, and it cannot destroy a stock balance.
 *
 * Schema came from a dump, so every add is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_sheets') && ! Schema::hasColumn('mar_sheets', 'dose_quantity')) {
            Schema::table('mar_sheets', function (Blueprint $table) {
                $table->decimal('dose_quantity', 10, 3)->nullable()->after('dose');
            });
        }

        if (Schema::hasTable('mar_administrations') && ! Schema::hasColumn('mar_administrations', 'quantity_given')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->decimal('quantity_given', 10, 3)->nullable()->after('dose_given');
            });
        }

        $this->backfill();
    }

    /**
     * Fill dose_quantity only where the dose string is unambiguous.
     * Anything uncertain is left NULL for a human — see the class docblock.
     */
    private function backfill(): void
    {
        if (! Schema::hasTable('mar_sheets') || ! Schema::hasColumn('mar_sheets', 'dose_quantity')) {
            return;
        }

        $rows = DB::table('mar_sheets')
            ->whereNull('dose_quantity')
            ->whereNotNull('dose')
            ->get(['id', 'dose']);

        $filled = 0;
        $skipped = [];

        foreach ($rows as $row) {
            $dose = trim((string) $row->dose);

            // Distributive wording means the number is per-site, not per-dose.
            if (preg_match('/\b(each|per|both|every)\b/i', $dose)) {
                $skipped[] = $dose;

                continue;
            }

            // Exactly one number, or we do not guess.
            preg_match_all('/\d+(?:\.\d+)?/', $dose, $m);
            if (count($m[0]) !== 1) {
                $skipped[] = $dose;

                continue;
            }

            $qty = (float) $m[0][0];
            if ($qty <= 0 || $qty > 9999) {
                $skipped[] = $dose;

                continue;
            }

            DB::table('mar_sheets')->where('id', $row->id)->update(['dose_quantity' => $qty]);
            $filled++;
        }

        echo "  dose_quantity backfilled: {$filled}\n";
        if ($skipped) {
            echo '  left NULL for human review ('.count($skipped)."): \n";
            foreach (array_count_values($skipped) as $d => $n) {
                echo "    - \"{$d}\" x{$n}\n";
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_sheets') && Schema::hasColumn('mar_sheets', 'dose_quantity')) {
            Schema::table('mar_sheets', fn (Blueprint $t) => $t->dropColumn('dose_quantity'));
        }
        if (Schema::hasTable('mar_administrations') && Schema::hasColumn('mar_administrations', 'quantity_given')) {
            Schema::table('mar_administrations', fn (Blueprint $t) => $t->dropColumn('quantity_given'));
        }
    }
};
