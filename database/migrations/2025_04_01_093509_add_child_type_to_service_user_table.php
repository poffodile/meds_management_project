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
            $table->integer('child_type')->after('date_of_birth')->default(1);
            $table->string('weekly_rate')->after('child_type')->nullable();
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
