<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when a medication round (a home + date + round) has been "ended" — so it
 * locks (no further recording) and the page can show who closed it and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medication_round_closures')) {
            return;
        }
        Schema::create('medication_round_closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->date('date');
            $table->string('round', 20);
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
            $table->unique(['home_id', 'date', 'round']);
            $table->index(['home_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_round_closures');
    }
};
