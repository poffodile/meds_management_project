<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ai_care_plans')) {
            Schema::create('ai_care_plans', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->integer('client_id');
                $table->integer('created_by');
                $table->string('plan_status', 20)->default('draft'); // draft, active
                $table->string('assessment_type', 30); // initial, review, reassessment
                $table->string('care_setting', 30); // residential, nursing, domiciliary
                $table->json('plan_data');
                $table->json('assessment_snapshot')->nullable();
                $table->string('ai_model', 50);
                $table->integer('tokens_input')->default(0);
                $table->integer('tokens_output')->default(0);
                $table->integer('generation_time_ms')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->integer('approved_by')->nullable();
                $table->date('review_date')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();

                $table->index(['home_id', 'client_id']);
                $table->index('plan_status');
                $table->index('is_deleted');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ai_care_plans');
    }
};
