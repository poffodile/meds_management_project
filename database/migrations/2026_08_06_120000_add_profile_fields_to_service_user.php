<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the profile fields the frontend4 client dashboard shows but the base
 * schema never had: medication support, capacity & consent, key worker, the GP
 * and pharmacy contacts, and an allergy reaction. These are on the RECORD7
 * onboarding form; the dashboard reads them and shows "Not recorded" until a
 * value exists, so they are safe to add and safe to leave empty.
 *
 * Guarded with hasColumn so it is a no-op where a column already exists (they
 * were first added by hand on the local database).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            foreach ([
                'medication_support', 'capacity_consent', 'key_worker',
                'gp_name', 'gp_practice', 'pharmacy_name', 'pharmacy_phone',
                'allergy_reaction',
            ] as $col) {
                if (! Schema::hasColumn('service_user', $col)) {
                    $table->string($col, 255)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            foreach ([
                'medication_support', 'capacity_consent', 'key_worker',
                'gp_name', 'gp_practice', 'pharmacy_name', 'pharmacy_phone',
                'allergy_reaction',
            ] as $col) {
                if (Schema::hasColumn('service_user', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
