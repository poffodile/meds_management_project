<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->string('start_time')->after('end_date_full_half')->nullable();
            $table->string('end_time')->after('end_date_full_half')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->dropColumn('treatment_id');
            $table->dropColumn('treatment_id');
        });
    }
};
