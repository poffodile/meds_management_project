<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_dose_review_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id')->nullable();
            $table->unsignedBigInteger('mar_sheet_id');
            $table->date('review_date');
            $table->string('time_slot', 10);
            $table->string('action', 20);                 // resolved | edited | removed
            $table->string('clinical_action', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['mar_sheet_id', 'review_date', 'time_slot'], 'mdrl_dose_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_dose_review_logs');
    }
};
