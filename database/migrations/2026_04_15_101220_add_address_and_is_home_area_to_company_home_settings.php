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
        Schema::table('company_home_settings', function (Blueprint $table) {
            $table->text('address')->nullable()->after('company_id');
            $table->boolean('is_home_area')->default(0)->after('clock_in_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_home_settings', function (Blueprint $table) {
            $table->dropColumn(['address', 'is_home_area']);
        });
    }
};
