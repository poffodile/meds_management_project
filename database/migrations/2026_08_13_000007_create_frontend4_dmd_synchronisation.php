<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('frontend4_terminology_imports')) {
            Schema::create('frontend4_terminology_imports', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 40)->default('nhs_dmd');
                $table->string('source_version', 100);
                $table->date('release_date')->nullable();
                $table->string('source_name', 255);
                $table->char('source_sha256', 64);
                $table->enum('status', ['applied', 'dry_run', 'failed']);
                $table->unsignedSmallInteger('file_count')->default(0);
                $table->unsignedInteger('concept_count')->default(0);
                $table->unsignedInteger('relationship_count')->default(0);
                $table->unsignedInteger('gtin_count')->default(0);
                $table->unsignedInteger('replacement_count')->default(0);
                $table->json('summary')->nullable();
                $table->text('failure_message')->nullable();
                $table->unsignedBigInteger('requested_by_user_id')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['provider', 'source_sha256', 'status'], 'f4_terminology_import_lookup');
                $table->index(['provider', 'created_at'], 'f4_terminology_provider_created');
            });
        }

        if (! Schema::hasTable('medicine_catalogue_relationships')) {
            Schema::create('medicine_catalogue_relationships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('child_medicine_id');
                $table->unsignedBigInteger('parent_medicine_id');
                $table->enum('relationship_type', ['has_vtm', 'has_vmp', 'has_amp', 'has_vmpp', 'replaced_by']);
                $table->string('source_version', 100);
                $table->timestamp('source_updated_at');
                $table->unique(['child_medicine_id', 'parent_medicine_id', 'relationship_type'], 'medicine_catalogue_relationship_unique');
                $table->index(['parent_medicine_id', 'relationship_type'], 'medicine_catalogue_parent_type');
            });
        }

        if (! Schema::hasTable('medicine_gtin_mappings')) {
            Schema::create('medicine_gtin_mappings', function (Blueprint $table) {
                $table->id();
                $table->string('gtin', 14);
                $table->unsignedBigInteger('medicine_id');
                $table->string('ampp_code', 18);
                $table->boolean('active')->default(true);
                $table->string('source_version', 100);
                $table->timestamp('source_updated_at');
                $table->timestamps();
                $table->unique(['gtin', 'ampp_code'], 'medicine_gtin_ampp_unique');
                $table->index(['gtin', 'active'], 'medicine_gtin_active');
                $table->index(['medicine_id', 'active'], 'medicine_gtin_medicine_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_gtin_mappings');
        Schema::dropIfExists('medicine_catalogue_relationships');
        Schema::dropIfExists('frontend4_terminology_imports');
    }
};
