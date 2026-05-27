<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mar_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('home_id');
            $table->unsignedInteger('client_id');

            $table->string('medication_name', 255);
            $table->string('dosage', 100)->nullable();
            $table->string('dose', 100)->nullable();
            $table->string('route', 100)->nullable();
            $table->string('frequency', 255)->nullable();
            $table->json('time_slots')->nullable();
            $table->boolean('as_required')->default(false);
            $table->text('prn_details')->nullable();
            $table->text('reason_for_medication')->nullable();

            $table->string('prescribed_by', 255)->nullable();
            $table->string('prescriber', 255)->nullable();
            $table->string('pharmacy', 255)->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->unsignedInteger('stock_level')->nullable();
            $table->unsignedInteger('reorder_level')->nullable();
            $table->text('storage_requirements')->nullable();

            $table->text('allergies_warnings')->nullable();

            $table->string('mar_status', 20)->default('active');
            $table->boolean('discontinued')->default(false);
            $table->date('discontinued_date')->nullable();
            $table->text('discontinued_reason')->nullable();
            $table->date('last_audited')->nullable();

            $table->unsignedInteger('created_by');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->index('home_id');
            $table->index('client_id');
            $table->index('mar_status');
            $table->index('is_deleted');
        });

        Schema::create('mar_administrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mar_sheet_id');
            $table->unsignedInteger('home_id');

            $table->date('date');
            $table->string('time_slot', 10);
            $table->boolean('given')->default(false);
            $table->string('dose_given', 100)->nullable();
            $table->unsignedInteger('administered_by');
            $table->string('witnessed_by', 255)->nullable();
            $table->string('code', 5);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('mar_sheet_id');
            $table->index('home_id');
            $table->index('date');
            $table->index('administered_by');
            $table->index(['mar_sheet_id', 'date', 'time_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_administrations');
        Schema::dropIfExists('mar_sheets');
    }
};
