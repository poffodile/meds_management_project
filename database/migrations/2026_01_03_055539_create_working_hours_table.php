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
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');

            // weekly / alternate
            $table->tinyInteger('week_number')->nullable(); // 1 or 2
            $table->string('day')->nullable(); // monday, tuesday...

            // specific date
            $table->date('work_date')->nullable();

            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_working')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
