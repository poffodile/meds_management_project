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
        Schema::create('scheduled_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->string('care_type_id');
            $table->string('assignment')->comments('Location, Client, Property');
            $table->unsignedBigInteger('service_user_id')->nullable();  
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('location_name')->nullable();
            $table->string('location_address')->nullable();
                    
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('shift_type')->nullable();
            $table->text('tasks')->nullable();
            $table->text('notes')->nullable();
            
            // Recurring info
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_shifts');
    }
};
