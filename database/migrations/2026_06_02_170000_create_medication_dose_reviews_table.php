<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_dose_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_id');
            $table->unsignedBigInteger('mar_sheet_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('client_name')->nullable();
            $table->string('medication_name');
            $table->date('review_date');
            $table->string('time_slot', 10);
            $table->enum('dose_kind', ['missed', 'not_given']);
            $table->string('code', 5)->nullable();           // MAR code for not-given doses
            $table->string('clinical_action');
            $table->text('notes')->nullable();
            $table->string('status')->default('resolved');
            $table->unsignedBigInteger('reviewed_by_user_id');
            $table->timestamps();

            $table->index('home_id');
            $table->unique(['mar_sheet_id', 'review_date', 'time_slot'], 'dose_review_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_dose_reviews');
    }
};
