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
        Schema::create('timesheet_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('home_id');
            $table->string('month'); // e.g. "2025-06"
            $table->string('file_path');
            $table->boolean('is_final')->default(false); // true if uploaded by finance
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheet_uploads');
    }
};
