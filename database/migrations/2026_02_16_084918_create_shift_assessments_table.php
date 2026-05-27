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
        Schema::create('shift_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id');
            $table->string('assessment_doc')->nullable()->comment('Path to the assessment document');
            $table->string('assessment_type')->nullable();
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
        Schema::dropIfExists('shift_assessments');
    }
};
