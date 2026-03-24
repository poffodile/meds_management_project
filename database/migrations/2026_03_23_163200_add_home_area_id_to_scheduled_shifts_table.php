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
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            $table->integer('home_area_id')->nullable()->after('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_shifts', function (Blueprint $table) {
            $table->dropColumn('home_area_id');
        });
    }
};
