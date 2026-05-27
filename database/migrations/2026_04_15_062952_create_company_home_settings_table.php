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
        Schema::create('company_home_settings', function (Blueprint $table) {
            $table->id();
            // Foreign key
            $table->unsignedBigInteger('company_id');
            // Default settings fields
            $table->decimal('weekly_allowance_service_users', 10, 2)->nullable();
            $table->decimal('monthly_allowance_service_users', 10, 2)->nullable();
            $table->integer('clock_in_range')->nullable()->comment('in meters');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_home_settings');
    }
};
