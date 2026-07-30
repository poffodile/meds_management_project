<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flag a controlled-drug movement that took MORE than the running balance held (owner
 * decision B1, 2026-07-28; review CD I-2 / HAZ-26).
 *
 * Why: an outgoing movement used to be floored at a tidy `0` — so removing more of a CD
 * than the register said existed produced a clean zero balance, hiding the exact
 * discrepancy the register exists to catch. Now the true (possibly negative) balance is
 * recorded and the entry is flagged here so it stands out for review (and, once the
 * Incidents module #20 lands, raises an incident).
 *
 * Nullable/additive; existing rows default to not-a-discrepancy. Guarded + re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('controlled_drug_register') && ! Schema::hasColumn('controlled_drug_register', 'is_discrepancy')) {
            Schema::table('controlled_drug_register', function (Blueprint $table) {
                $table->boolean('is_discrepancy')->default(false)->after('balance_after');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('controlled_drug_register') && Schema::hasColumn('controlled_drug_register', 'is_discrepancy')) {
            Schema::table('controlled_drug_register', function (Blueprint $table) {
                $table->dropColumn('is_discrepancy');
            });
        }
    }
};
