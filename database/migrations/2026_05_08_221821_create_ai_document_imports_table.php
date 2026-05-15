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
        Schema::create('ai_document_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('home_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('uploaded_by');
            $table->string('original_filename', 255);
            $table->string('stored_path', 500);
            $table->unsignedInteger('file_size');
            $table->string('file_mime', 100);
            $table->unsignedInteger('extracted_text_length')->nullable();
            $table->string('import_status', 20)->default('uploaded');
            $table->json('extracted_data')->nullable();
            $table->json('imported_categories')->nullable();
            $table->json('import_summary')->nullable();
            $table->string('ai_model', 50)->nullable();
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->unsignedInteger('generation_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamps();

            $table->index(['home_id', 'client_id'], 'idx_adi_home_client');
            $table->index('import_status', 'idx_adi_status');
            $table->index('uploaded_by', 'idx_adi_uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_document_imports');
    }
};
