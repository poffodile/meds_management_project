<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medicine_catalogue')) {
            Schema::create('medicine_catalogue', function (Blueprint $table) {
                $table->id();
                $table->string('dmd_code', 18)->nullable()->unique();
                $table->string('dmd_concept_level', 10)->nullable();
                $table->string('name', 255);
                $table->string('form', 100)->nullable();
                $table->string('default_route', 100)->nullable();
                $table->string('countable_unit', 30)->nullable();
                $table->decimal('strength_amount', 12, 4)->nullable();
                $table->string('strength_unit', 30)->nullable();
                $table->decimal('strength_volume', 12, 4)->nullable();
                $table->string('strength_volume_unit', 30)->nullable();
                $table->boolean('is_controlled')->default(false);
                $table->string('cd_schedule', 10)->nullable();
                $table->string('dmd_status', 20)->default('current');
                $table->unsignedBigInteger('replaced_by_id')->nullable();
                $table->boolean('is_local')->default(false);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->string('source_version', 100)->nullable();
                $table->timestamp('source_updated_at')->nullable();
                $table->timestamps();

                $table->index(['name', 'dmd_status']);
                $table->index(['is_controlled', 'cd_schedule']);
                $table->index('replaced_by_id');
            });
        }

        if (! Schema::hasTable('frontend4_prescription_events')) {
            Schema::create('frontend4_prescription_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('organisation_id');
                $table->unsignedInteger('service_id');
                $table->unsignedInteger('client_id');
                $table->unsignedBigInteger('mar_sheet_id');
                $table->unsignedBigInteger('medicine_id')->nullable();
                $table->unsignedInteger('actor_user_id');
                $table->string('event_type', 40);
                $table->timestamp('effective_at');
                $table->text('reason')->nullable();
                $table->json('changes')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['organisation_id', 'service_id']);
                $table->index(['client_id', 'created_at']);
                $table->index(['mar_sheet_id', 'created_at']);
                $table->index(['medicine_id', 'created_at']);
            });
        }

        Schema::table('mar_sheets', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_sheets', 'medicine_id')) {
                $table->unsignedBigInteger('medicine_id')->nullable()->index();
            }
            if (! Schema::hasColumn('mar_sheets', 'medication_name_as_written')) {
                $table->string('medication_name_as_written', 255)->nullable();
            }
            if (! Schema::hasColumn('mar_sheets', 'dose_amount')) {
                $table->decimal('dose_amount', 12, 4)->nullable();
            }
            if (! Schema::hasColumn('mar_sheets', 'dose_unit')) {
                $table->string('dose_unit', 30)->nullable();
            }
            if (! Schema::hasColumn('mar_sheets', 'prescription_version')) {
                $table->unsignedInteger('prescription_version')->default(1);
            }
            if (! Schema::hasColumn('mar_sheets', 'prescription_source')) {
                $table->string('prescription_source', 40)->nullable();
            }
            if (! Schema::hasColumn('mar_sheets', 'prescribed_at')) {
                $table->timestamp('prescribed_at')->nullable();
            }
            if (! Schema::hasColumn('mar_sheets', 'review_due_date')) {
                $table->date('review_due_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_sheets')) {
            $columns = collect([
                'medicine_id', 'medication_name_as_written', 'dose_amount', 'dose_unit',
                'prescription_version', 'prescription_source', 'prescribed_at', 'review_due_date',
            ])->filter(fn ($column) => Schema::hasColumn('mar_sheets', $column))->all();
            if ($columns !== []) {
                Schema::table('mar_sheets', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }

        Schema::dropIfExists('frontend4_prescription_events');
        Schema::dropIfExists('medicine_catalogue');
    }
};
