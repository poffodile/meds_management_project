<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the care-context fields surfaced on the Medication Round resident card
 * (room number, NHS number, diet). Mobility already exists as `suMobility`.
 * All nullable + additive — existing resident data is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            if (! Schema::hasColumn('service_user', 'room_number')) {
                $table->string('room_number', 20)->nullable()->after('room_type');
            }
            if (! Schema::hasColumn('service_user', 'nhs_number')) {
                $table->string('nhs_number', 20)->nullable()->after('room_number');
            }
            if (! Schema::hasColumn('service_user', 'diet')) {
                $table->string('diet', 100)->nullable()->after('nhs_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            foreach (['room_number', 'nhs_number', 'diet'] as $col) {
                if (Schema::hasColumn('service_user', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
