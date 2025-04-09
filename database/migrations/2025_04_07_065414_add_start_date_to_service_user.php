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
        Schema::table('service_user', function (Blueprint $table) {
            $table->integer('room_type')->after('child_type')->nullable();
            $table->date('start_date')->after('weekly_rate')->nullable();
            $table->date('end_date')->after('start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            //
        });
    }
};
