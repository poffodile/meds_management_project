<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A dedicated administration-directions field on a prescription (issue #29, owner decision
 * C1 2026-07-28; review Round C1 / HAZ-22).
 *
 * Why: there was NO field for a genuine "how to give it" directive — "do not crush",
 * "take with food", "half an hour before food", "shake well", "rotate injection site".
 * The round could only show the *indication* (reason_for_medication), and briefly it was
 * being shown styled as if it were a directive — a real safety-confusion risk, now fixed.
 * This is the proper place for the directive.
 *
 * A manager/prescriber types it now; once the meds dictionary (#17, dm+d) lands it can
 * auto-fill from the formulary so it isn't re-typed per resident. Nullable + additive, so
 * existing prescriptions are unaffected.
 *
 * Schema came from a dump, so this is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_sheets') && ! Schema::hasColumn('mar_sheets', 'administration_instructions')) {
            Schema::table('mar_sheets', function (Blueprint $table) {
                $table->string('administration_instructions', 500)->nullable()->after('reason_for_medication');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_sheets') && Schema::hasColumn('mar_sheets', 'administration_instructions')) {
            Schema::table('mar_sheets', function (Blueprint $table) {
                $table->dropColumn('administration_instructions');
            });
        }
    }
};
