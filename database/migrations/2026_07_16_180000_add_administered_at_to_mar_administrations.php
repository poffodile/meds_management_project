<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records WHEN a dose was actually administered.
 *
 * Until now there was no such column. `date` + `time_slot` record the SCHEDULED
 * slot, not the real time, and for a PRN medicine the "slot" is a single fixed
 * nominal time (e.g. 12:00) that every dose is filed under regardless of when it
 * was given.
 *
 * So the PRN minimum-interval check used `updated_at` as a stand-in for the last
 * dose time (audit CR-01). Two consequences, both real:
 *
 *   1. Editing a dose's NOTES hours later bumped updated_at, moved the "last
 *      given" time forward, and wrongly blocked the next dose with
 *      "Too soon — not due until HH:MM".
 *   2. A dose recorded late (or backdated) measured the interval from the moment
 *      of DATA ENTRY rather than the moment of administration.
 *
 * administered_at is captured, never derived from date+time_slot, precisely
 * because the scheduled slot and the actual time are different facts.
 *
 * BACKFILL: existing rows get created_at, which is the closest honest
 * approximation available (the row's first write). It is explicitly NOT
 * updated_at — that is the value whose misuse caused the bug. For seeded demo
 * rows created in bulk this is approximate, and it is approximate in a visible
 * way rather than a wrong-and-confident way.
 *
 * Schema came from a dump, so the add is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mar_administrations')) {
            return;
        }

        if (! Schema::hasColumn('mar_administrations', 'administered_at')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->dateTime('administered_at')->nullable()->after('time_slot');
                $table->index(['mar_sheet_id', 'administered_at'], 'mar_adm_sheet_administered_idx');
            });
        }

        $backfilled = DB::table('mar_administrations')
            ->whereNull('administered_at')
            ->update(['administered_at' => DB::raw('created_at')]);

        echo "  administered_at backfilled from created_at: {$backfilled}\n";
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_administrations') && Schema::hasColumn('mar_administrations', 'administered_at')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->dropIndex('mar_adm_sheet_administered_idx');
                $table->dropColumn('administered_at');
            });
        }
    }
};
