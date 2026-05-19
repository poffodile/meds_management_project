<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ai_chat_sessions')) {
            Schema::create('ai_chat_sessions', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->integer('user_id');
                $table->string('session_title')->default('New Chat');
                $table->string('context_type')->default('general');
                $table->integer('context_id')->nullable();
                $table->integer('message_count')->default(0);
                $table->integer('total_tokens')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_deleted')->default(false);
                $table->timestamps();

                $table->index('home_id');
                $table->index('user_id');
                $table->index(['home_id', 'user_id', 'is_deleted']);
            });
        }

        if (!Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->integer('home_id');
                $table->string('role', 20); // user, assistant, system
                $table->text('content');
                $table->string('model_used')->nullable();
                $table->integer('tokens_input')->default(0);
                $table->integer('tokens_output')->default(0);
                $table->timestamp('created_at')->useCurrent();

                $table->index('session_id');
                $table->index('home_id');
                $table->foreign('session_id')->references('id')->on('ai_chat_sessions')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('home_id');
                $table->integer('user_id');
                $table->string('feature');
                $table->string('model_used');
                $table->integer('tokens_input');
                $table->integer('tokens_output');
                $table->integer('tokens_total');
                $table->string('prompt_hash')->nullable();
                $table->string('response_status');
                $table->text('error_message')->nullable();
                $table->integer('latency_ms')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index('home_id');
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_sessions');
    }
};
