<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medication_logs') && !Schema::hasColumn('medication_logs', 'is_deleted')) {
            Schema::table('medication_logs', function (Blueprint $table) {
                $table->tinyInteger('is_deleted')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('medication_logs') && Schema::hasColumn('medication_logs', 'is_deleted')) {
            Schema::table('medication_logs', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
