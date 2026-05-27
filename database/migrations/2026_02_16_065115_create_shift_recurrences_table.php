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
        Schema::create('shift_recurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id')->comment('Reference to scheduled_shifts');
            $table->string('frequency'); // daily, weekly, monthly
            $table->string('week_days')->nullable();
            $table->date('end_recurring_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shift_id')->references('id')->on('scheduled_shifts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_recurrences');
    }
};
