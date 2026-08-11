<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['user', 'admin', 'service_user'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'failed_login_attempts')) {
                    $table->unsignedSmallInteger('failed_login_attempts')->default(0);
                }
                if (! Schema::hasColumn($tableName, 'locked_until')) {
                    $table->timestamp('locked_until')->nullable()->index();
                }
                if (! Schema::hasColumn($tableName, 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'last_login_ip')) {
                    $table->string('last_login_ip', 45)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'password_changed_at')) {
                    $table->timestamp('password_changed_at')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'force_password_reset')) {
                    $table->boolean('force_password_reset')->default(false);
                }
            });
        }

        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        Schema::create('password_action_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('authenticatable_type', 50);
            $table->unsignedBigInteger('authenticatable_id');
            $table->char('token_hash', 64)->unique();
            $table->string('purpose', 30);
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->string('requested_ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['authenticatable_type', 'authenticatable_id', 'purpose'],
                'password_action_account_purpose_index'
            );
        });

        Schema::create('authentication_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('actor_type', 50)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->char('identifier_hash', 64)->nullable()->index();
            $table->string('event_type', 64)->index();
            $table->boolean('successful')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentication_events');
        Schema::dropIfExists('password_action_tokens');

        foreach (['user', 'admin', 'service_user'] as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach ([
                    'failed_login_attempts',
                    'locked_until',
                    'last_login_at',
                    'last_login_ip',
                    'password_changed_at',
                    'force_password_reset',
                ] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
