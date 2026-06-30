<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured PRN ("as needed") limits so the round can show last-given / next-due
 * and stop an early or over-the-limit dose:
 *   - prn_max_daily          : max doses allowed in a day
 *   - prn_min_interval_hours : minimum gap between doses (hours)
 * Nullable + additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_sheets', 'prn_max_daily')) {
                $table->unsignedSmallInteger('prn_max_daily')->nullable()->after('prn_details');
            }
            if (! Schema::hasColumn('mar_sheets', 'prn_min_interval_hours')) {
                $table->decimal('prn_min_interval_hours', 4, 1)->nullable()->after('prn_max_daily');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            foreach (['prn_max_daily', 'prn_min_interval_hours'] as $col) {
                if (Schema::hasColumn('mar_sheets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
