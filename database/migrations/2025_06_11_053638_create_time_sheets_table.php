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
        Schema::create('time_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('home_id');
            $table->integer('user_id');
            $table->date('date');
            $table->decimal('hours', 5, 2)->nullable();
            $table->decimal('sleep', 5, 2)->nullable();
            $table->decimal('wake_night', 5, 2)->nullable();
            $table->decimal('disturbance', 5, 2)->nullable();
            $table->decimal('annual_leave', 5, 2)->nullable();
            $table->decimal('on_call', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_sheets');
    }
};
