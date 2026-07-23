<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds `form` and `unit` to mar_sheets.
 *
 * WHY:
 *  - `form` (tablet / capsule / oral suspension / inhaler / patch ...) is required by the
 *    owner's Medication Round brief ("name, strength, FORM and dose") and by REQ-MED-04,
 *    but has never existed on this table. Without it the round cannot fully confirm the
 *    "right medicine", and it blocks correct dm+d VMP-level mapping later (form + strength
 *    are what define a VMP).
 *  - `unit` is already READ by the round payload (MedicationRoundController::buildRoundProps
 *    -> 'unit' => $sheet->unit) but the column was never added, so Eloquent silently returns
 *    null and the unit has always been blank in the UI. This closes that latent bug.
 *
 * Both are nullable free text for now, matching the existing dose/route pattern. They are the
 * staging ground for coded values later (form_snomed_code / unit_snomed_code) per REQ-MED-50.
 *
 * The schema is loaded from a dump rather than a clean migration history, so every change is
 * guarded with hasTable/hasColumn and is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mar_sheets')) {
            return;
        }

        Schema::table('mar_sheets', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_sheets', 'form')) {
                // e.g. "Tablet", "Capsule", "Oral suspension", "Inhaler", "Patch".
                $table->string('form', 100)->nullable()->after('dosage');
            }
            if (! Schema::hasColumn('mar_sheets', 'unit')) {
                // Dose/stock unit, e.g. "tablet", "ml", "puff". Read by buildRoundProps().
                $table->string('unit', 50)->nullable()->after('dose');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mar_sheets')) {
            return;
        }

        Schema::table('mar_sheets', function (Blueprint $table) {
            foreach (['form', 'unit'] as $col) {
                if (Schema::hasColumn('mar_sheets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
