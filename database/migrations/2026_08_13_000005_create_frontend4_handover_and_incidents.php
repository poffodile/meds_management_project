<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('frontend4_handovers')) {
            Schema::create('frontend4_handovers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->dateTime('shift_start');
                $table->dateTime('shift_end');
                $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
                $table->text('general_notes')->nullable();
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('submitted_by_user_id')->nullable();
                $table->dateTime('submitted_at')->nullable();
                $table->timestamps();
                $table->index(['organisation_id', 'service_id', 'status'], 'f4_handovers_scope_status');
                $table->index(['service_id', 'shift_start', 'shift_end'], 'f4_handovers_shift');
            });
        }

        if (! Schema::hasTable('frontend4_handover_items')) {
            Schema::create('frontend4_handover_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handover_id');
                $table->string('category', 40);
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('source_key', 190);
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('mar_sheet_id')->nullable();
                $table->dateTime('occurred_at')->nullable();
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->string('summary');
                $table->text('detail')->nullable();
                $table->boolean('requires_action')->default(false);
                $table->timestamps();
                $table->unique(['handover_id', 'source_key'], 'f4_handover_source_unique');
                $table->index(['handover_id', 'category'], 'f4_handover_items_category');
                $table->index(['client_id', 'occurred_at'], 'f4_handover_items_client');
            });
        }

        if (! Schema::hasTable('frontend4_handover_acknowledgements')) {
            Schema::create('frontend4_handover_acknowledgements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('handover_id');
                $table->unsignedBigInteger('user_id');
                $table->dateTime('acknowledged_at');
                $table->timestamps();
                $table->unique(['handover_id', 'user_id'], 'f4_handover_ack_user_unique');
            });
        }

        if (! Schema::hasTable('frontend4_follow_up_tasks')) {
            Schema::create('frontend4_follow_up_tasks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('handover_item_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->enum('task_type', ['clinical_follow_up', 'professional_advice', 'stock', 'incident_action', 'other'])->default('clinical_follow_up');
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->unsignedBigInteger('owner_user_id');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->dateTime('due_at');
                $table->dateTime('escalate_at');
                $table->enum('status', ['open', 'completed', 'cancelled'])->default('open');
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('completed_by_user_id')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->text('completion_note')->nullable();
                $table->timestamps();
                $table->index(['service_id', 'status', 'due_at'], 'f4_tasks_service_status_due');
                $table->index(['owner_user_id', 'status'], 'f4_tasks_owner_status');
            });
        }

        if (! Schema::hasTable('frontend4_medication_incidents')) {
            Schema::create('frontend4_medication_incidents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->unsignedBigInteger('location_id')->nullable();
                $table->unsignedBigInteger('handover_item_id')->nullable();
                $table->unsignedBigInteger('client_id')->nullable();
                $table->unsignedBigInteger('mar_sheet_id')->nullable();
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->enum('category', ['administration', 'omission', 'controlled_drug', 'stock', 'prescription', 'other']);
                $table->enum('severity', ['low', 'moderate', 'high', 'critical']);
                $table->text('description');
                $table->text('immediate_action');
                $table->enum('status', ['reported', 'investigating', 'closed'])->default('reported');
                $table->unsignedBigInteger('reported_by_user_id');
                $table->dateTime('reported_at');
                $table->unsignedBigInteger('investigator_user_id')->nullable();
                $table->text('outcome')->nullable();
                $table->text('learning')->nullable();
                $table->unsignedBigInteger('closed_by_user_id')->nullable();
                $table->dateTime('closed_at')->nullable();
                $table->timestamps();
                $table->unique(['service_id', 'source_type', 'source_id'], 'f4_incidents_source_unique');
                $table->index(['service_id', 'status', 'severity'], 'f4_incidents_service_status');
                $table->index(['client_id', 'reported_at'], 'f4_incidents_client');
            });
        }

        if (! Schema::hasTable('frontend4_clinical_events')) {
            Schema::create('frontend4_clinical_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('organisation_id');
                $table->unsignedBigInteger('service_id');
                $table->string('subject_type', 60);
                $table->unsignedBigInteger('subject_id');
                $table->string('event_type', 60);
                $table->unsignedBigInteger('actor_user_id');
                $table->json('metadata')->nullable();
                $table->dateTime('occurred_at');
                $table->index(['subject_type', 'subject_id', 'id'], 'f4_clinical_event_subject');
                $table->index(['service_id', 'occurred_at'], 'f4_clinical_event_service');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend4_clinical_events');
        Schema::dropIfExists('frontend4_medication_incidents');
        Schema::dropIfExists('frontend4_follow_up_tasks');
        Schema::dropIfExists('frontend4_handover_acknowledgements');
        Schema::dropIfExists('frontend4_handover_items');
        Schema::dropIfExists('frontend4_handovers');
    }
};
