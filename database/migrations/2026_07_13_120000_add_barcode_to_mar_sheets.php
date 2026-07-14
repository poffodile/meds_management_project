<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Barcode scanning (#34) — store the pack barcode/GTIN on the medicine so a
 * scanner can jump straight to it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_sheets', 'barcode')) {
                $table->string('barcode', 100)->nullable()->after('cd_schedule');
                $table->index('barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mar_sheets', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
