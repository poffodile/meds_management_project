<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds an explicit client lifecycle without replacing the legacy service_user row.
 *
 * The old table required placeholder usernames, passwords, measurement units and
 * narrative text even when none had been recorded. Frontend 4 must never invent
 * clinical/profile data simply to satisfy a NOT NULL constraint, so those legacy
 * columns become nullable. Values already present are left untouched.
 */
return new class extends Migration
{
    private const LEGACY_PLACEHOLDER_COLUMNS = [
        'user_name', 'password', 'department', 'short_description',
        'height_unit', 'weight_unit', 'hair_and_eyes', 'markings', 'image',
        'personal_info', 'education_history', 'bereavement_issues',
        'drug_n_alcohol_issues', 'mental_health_issues',
        'current_location', 'previous_location',
    ];

    public function up(): void
    {
        Schema::table('service_user', function (Blueprint $table) {
            if (! Schema::hasColumn('service_user', 'lifecycle_status')) {
                $table->string('lifecycle_status', 24)->nullable()->after('status')->index();
            }
            if (! Schema::hasColumn('service_user', 'lifecycle_status_before_archive')) {
                $table->string('lifecycle_status_before_archive', 24)->nullable()->after('lifecycle_status');
            }
            if (! Schema::hasColumn('service_user', 'lifecycle_changed_at')) {
                $table->dateTime('lifecycle_changed_at')->nullable()->after('lifecycle_status_before_archive');
            }
            if (! Schema::hasColumn('service_user', 'preferred_name')) {
                $table->string('preferred_name', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('service_user', 'pronouns')) {
                $table->string('pronouns', 80)->nullable()->after('preferred_name');
            }
            if (! Schema::hasColumn('service_user', 'primary_language')) {
                $table->string('primary_language', 100)->nullable();
            }
            if (! Schema::hasColumn('service_user', 'communication_needs')) {
                $table->text('communication_needs')->nullable();
            }
            if (! Schema::hasColumn('service_user', 'frontend4_created_by')) {
                $table->integer('frontend4_created_by')->nullable()->index();
            }
            if (! Schema::hasColumn('service_user', 'frontend4_updated_by')) {
                $table->integer('frontend4_updated_by')->nullable()->index();
            }
        });

        DB::table('service_user')->whereNull('lifecycle_status')->update([
            'lifecycle_status' => DB::raw("CASE WHEN is_deleted = 1 THEN 'archived' WHEN status = 1 THEN 'active' ELSE 'inactive' END"),
            'lifecycle_changed_at' => DB::raw('COALESCE(updated_at, created_at, NOW())'),
        ]);

        $this->makeLegacyPlaceholdersNullable();

        if (! Schema::hasTable('frontend4_client_events')) {
            Schema::create('frontend4_client_events', function (Blueprint $table) {
                $table->id();
                $table->integer('organisation_id')->index();
                $table->integer('service_id')->index();
                $table->integer('client_id')->index();
                $table->integer('actor_user_id')->nullable()->index();
                $table->string('event_type', 64)->index();
                $table->string('from_status', 24)->nullable();
                $table->string('to_status', 24)->nullable();
                $table->dateTime('effective_at');
                $table->text('reason')->nullable();
                $table->json('changes')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['client_id', 'created_at'], 'f4_client_event_timeline_idx');
            });
        }

        if (! Schema::hasTable('frontend4_client_transfer_requests')) {
            Schema::create('frontend4_client_transfer_requests', function (Blueprint $table) {
                $table->id();
                $table->integer('organisation_id')->index();
                $table->integer('client_id')->index();
                $table->integer('from_service_id')->index();
                $table->integer('to_service_id')->index();
                $table->string('status', 24)->default('pending_review')->index();
                $table->text('reason');
                $table->integer('requested_by')->index();
                $table->dateTime('requested_at');
                $table->integer('reviewed_by')->nullable();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();
                $table->index(['client_id', 'status'], 'f4_client_transfer_pending_idx');
            });
        }
    }

    /** Preserve each MySQL column's real type instead of guessing a replacement. */
    private function makeLegacyPlaceholdersNullable(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();
        foreach (self::LEGACY_PLACEHOLDER_COLUMNS as $column) {
            if (! Schema::hasColumn('service_user', $column)) {
                continue;
            }

            $meta = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', 'service_user')
                ->where('COLUMN_NAME', $column)
                ->first(['COLUMN_TYPE', 'IS_NULLABLE']);

            if ($meta && $meta->IS_NULLABLE === 'NO') {
                $type = preg_replace('/[^a-zA-Z0-9_(),\' ]/', '', $meta->COLUMN_TYPE);
                DB::statement("ALTER TABLE `service_user` MODIFY `{$column}` {$type} NULL");
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend4_client_transfer_requests');
        Schema::dropIfExists('frontend4_client_events');

        Schema::table('service_user', function (Blueprint $table) {
            foreach ([
                'lifecycle_status', 'lifecycle_status_before_archive', 'lifecycle_changed_at',
                'preferred_name', 'pronouns', 'primary_language', 'communication_needs',
                'frontend4_created_by', 'frontend4_updated_by',
            ] as $column) {
                if (Schema::hasColumn('service_user', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // The old NOT NULL constraints are intentionally not restored: once a real
        // client has an unknown value, forcing the column back would either fail or
        // require fabricating data during rollback.
    }
};
