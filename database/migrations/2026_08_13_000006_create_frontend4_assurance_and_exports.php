<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('frontend4_assurance_reviews')) {
            Schema::create('frontend4_assurance_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->date('period_start');
                $table->date('period_end');
                $table->unsignedInteger('administration_records')->default(0);
                $table->unsignedInteger('given_records')->default(0);
                $table->unsignedInteger('not_given_records')->default(0);
                $table->unsignedInteger('late_records')->default(0);
                $table->unsignedInteger('prn_records')->default(0);
                $table->unsignedInteger('low_stock_count')->default(0);
                $table->unsignedInteger('cd_discrepancy_count')->default(0);
                $table->unsignedInteger('open_task_count')->default(0);
                $table->unsignedInteger('overdue_task_count')->default(0);
                $table->unsignedInteger('open_incident_count')->default(0);
                $table->unsignedInteger('high_risk_incident_count')->default(0);
                $table->unsignedInteger('prescription_event_count')->default(0);
                $table->text('review_note');
                $table->text('action_summary')->nullable();
                $table->unsignedBigInteger('reviewed_by_user_id');
                $table->dateTime('reviewed_at');
                $table->timestamps();
                $table->index(['organisation_id', 'service_id', 'reviewed_at'], 'f4_assurance_context_reviewed');
                $table->index(['service_id', 'period_start', 'period_end'], 'f4_assurance_period');
            });
        }

        if (! Schema::hasTable('frontend4_report_export_events')) {
            Schema::create('frontend4_report_export_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('requested_by_user_id');
                $table->string('report_type', 40);
                $table->date('period_start');
                $table->date('period_end');
                $table->boolean('identifiable')->default(false);
                $table->string('format', 12)->default('csv');
                $table->unsignedInteger('record_count')->default(0);
                $table->text('reason');
                $table->dateTime('generated_at');
                $table->index(['organisation_id', 'service_id', 'generated_at'], 'f4_export_context_generated');
                $table->index(['requested_by_user_id', 'generated_at'], 'f4_export_requester');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend4_report_export_events');
        Schema::dropIfExists('frontend4_assurance_reviews');
    }
};
