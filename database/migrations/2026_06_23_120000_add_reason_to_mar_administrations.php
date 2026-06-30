<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a structured `reason` to a recorded dose — used when a dose is Refused or
 * Not Given (Omitted), so the record is auditable and can feed the Missed Doses
 * screen. Nullable + additive; existing records untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mar_administrations', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_administrations', 'reason')) {
                $table->string('reason', 255)->nullable()->after('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mar_administrations', function (Blueprint $table) {
            if (Schema::hasColumn('mar_administrations', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
