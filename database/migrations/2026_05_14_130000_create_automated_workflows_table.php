<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('automated_workflows')) {
            Schema::create('automated_workflows', function (Blueprint $table) {
                $table->id();
                $table->string('workflow_name');
                $table->string('template_id')->nullable();
                $table->string('category'); // scheduling, compliance, etc.
                $table->string('trigger_type'); // scheduled, condition, event
                $table->text('trigger_config');
                $table->string('action_type'); // send_notification, send_email
                $table->text('action_config');
                $table->integer('cooldown_hours')->default(24);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->integer('home_id');
                $table->integer('created_by');
                $table->timestamp('next_run_date')->nullable();
                $table->timestamp('last_triggered_at')->nullable();
                $table->timestamps();

                $table->index('home_id');
                $table->index(['home_id', 'is_active', 'is_deleted']);
            });
        }

        if (!Schema::hasTable('workflow_execution_logs')) {
            Schema::create('workflow_execution_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('workflow_id');
                $table->integer('home_id');
                $table->string('trigger_type');
                $table->text('trigger_data')->nullable();
                $table->string('action_type');
                $table->string('action_result'); // success, failed, skipped
                $table->text('error_message')->nullable();
                $table->timestamp('executed_at');
                $table->timestamps();

                $table->index('workflow_id');
                $table->index('home_id');
                $table->index('executed_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('workflow_execution_logs');
        Schema::dropIfExists('automated_workflows');
    }
};
