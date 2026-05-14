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
        if (!Schema::hasTable('form_templates')) {
            Schema::create('form_templates', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('source_filename')->nullable();
                $table->longText('form_json');
                $table->string('status')->default('published');
                $table->boolean('ai_generated')->default(0);
                $table->integer('created_by')->nullable();
                $table->boolean('is_deleted')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('form_submissions')) {
            Schema::create('form_submissions', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->integer('form_template_id');
                $table->integer('client_id')->nullable();
                $table->string('form_title');
                $table->longText('values_json');
                $table->integer('submitted_by');
                $table->string('submitted_by_name');
                $table->boolean('ai_filled')->default(0);
                $table->boolean('is_deleted')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_templates');
    }
};
