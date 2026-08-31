<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.7, part four — the reorder level, kept apart from the ledger.
 *
 * A BALANCE IS DERIVED. A THRESHOLD IS CONFIGURATION. Mixing them would mean a
 * rebuild-from-ledger repair had to carefully preserve three unrelated columns,
 * which is how configuration gets lost during a repair. So they live in
 * separate tables and the head stays purely rebuildable.
 *
 * NOTHING IS INVENTED. The old `record7_stock_levels.low_threshold` figures
 * were written by a seeder and have no screen, owner, policy or provenance
 * behind them, so they are not carried over. A threshold exists only where a
 * person with `stock_management` has recorded one.
 *
 * NO ROW MEANS NO RULE. `stock_low` is then unavailable, not false — a blank
 * must never render as healthy. `stock_out` is unaffected: it derives from the
 * balance alone and needs no configuration.
 *
 * This table holds only the CURRENT rule. Its history lives in the append-only
 * access audit, which cannot be rewritten.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_stock_thresholds', function (Blueprint $table) {
            $table->id();

            // Keyed to the balance identity itself, one rule per balance.
            $table->foreignId('stock_balance_id')->unique()
                ->constrained('record7_stock_balances')->cascadeOnDelete();

            $table->decimal('low_threshold', 10, 3);

            // Who decided, and when. The audit trail holds the sequence.
            $table->foreignId('set_by_user_id')->constrained('record7_users');
            $table->timestamp('set_at');
            $table->string('note', 190)->nullable();

            $table->timestamps();
        });

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_stock_thresholds
                ADD CONSTRAINT record7_stock_thresholds_sane
                CHECK (low_threshold >= 0)
        SQL);
    }

    public function down(): void
    {
        Schema::connection('record7')->dropIfExists('record7_stock_thresholds');
    }
};
