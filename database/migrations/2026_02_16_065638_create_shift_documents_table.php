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
        Schema::create('shift_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_id');
            // Attached Documents (Type 1: System Form)
            $table->unsignedBigInteger('form_id')->nullable()->comment('Dynamic form builder ID if selected from system');
            // Attached Documents (Type 2: File Upload)
            $table->string('doc_name')->nullable();
            $table->string('doc_type')->nullable();
            $table->string('doc_file')->nullable()->comment('Path to uploaded file');
            $table->boolean('doc_required')->default(0);
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
        Schema::dropIfExists('shift_documents');
    }
};
