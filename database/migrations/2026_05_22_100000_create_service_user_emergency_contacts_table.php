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
        Schema::create('service_user_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_user_id');
            $table->string('name')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();

            // Index for faster queries
            $table->index('service_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_user_emergency_contacts');
    }
};
