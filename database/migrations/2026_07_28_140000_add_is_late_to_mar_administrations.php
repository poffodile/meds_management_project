<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mark an administration that was recorded as GIVEN LATE (owner decision B4, 2026-07-28;
 * review Missed I1 / HAZ-26).
 *
 * Why: resolving a missed dose as "dose given late" used to record only a review, leaving
 * the medication chart (MAR) with a gap — the chart and the review disagreed about whether
 * the resident got the dose. Now "given late" also writes a real administration, flagged
 * with this column so it reads as *given late*, not as a normal on-time dose.
 *
 * Nullable/additive; existing rows default to not-late. Schema came from a dump, so this is
 * guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_administrations') && ! Schema::hasColumn('mar_administrations', 'is_late')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->boolean('is_late')->default(false)->after('given');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_administrations') && Schema::hasColumn('mar_administrations', 'is_late')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->dropColumn('is_late');
            });
        }
    }
};
