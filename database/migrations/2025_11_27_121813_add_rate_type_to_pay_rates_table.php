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
        Schema::table('pay_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('rate_type_id')
              ->nullable()
              ->after('access_level_id');  // position after access_level_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_rates', function (Blueprint $table) {
            //
        });
    }
};
