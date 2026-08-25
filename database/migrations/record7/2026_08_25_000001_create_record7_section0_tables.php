<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Record7 Section 0 — access schema.
 *
 * Every table lives on the 'record7' connection, in Record7's own database.
 * Nothing here touches the legacy schema, Frontend 3 or Frontend 4.
 *
 * The shape follows the supplied Section 0 test-data package
 * (database/fixtures/record7/source-schema.sql) so the fictional Omega Care
 * Group fixture maps across without distortion — including the parts the
 * legacy schema cannot express: per-user allow/deny rules, competencies,
 * access types, access date windows, MFA methods, invitations and an
 * append-only audit trail.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        /* ── Organisation and services ──────────────────────────────────── */

        $schema->create('record7_organisations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();      // usr/org id from the fixture
            $table->string('legal_name', 255);
            $table->string('display_name', 255);
            // Lower-cased, space-collapsed. Sign-in step one matches on THIS,
            // which is what makes "Omega   Care  Group" resolve correctly.
            $table->string('name_normalised', 255)->unique();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamps();
        });

        $schema->create('record7_services', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->string('name', 255);
            $table->string('service_type', 120)->nullable();
            $table->string('town', 120)->nullable();
            $table->enum('status', ['active', 'suspended', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['organisation_id', 'name']);
            $table->index(['organisation_id', 'status']);
        });

        /* ── Roles and permissions ──────────────────────────────────────── */

        $schema->create('record7_roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();           // R1..R9
            $table->string('name', 120)->unique();
            $table->string('description', 500);
            $table->unsignedTinyInteger('privilege_level'); // 0..100
            $table->timestamps();
        });

        $schema->create('record7_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->string('description', 500);
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();
        });

        $schema->create('record7_role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('record7_roles');
            $table->foreignId('permission_id')->constrained('record7_permissions');
            $table->primary(['role_id', 'permission_id']);
        });

        /* ── People ─────────────────────────────────────────────────────── */

        $schema->create('record7_users', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->string('full_name', 255);
            $table->string('preferred_name', 120)->nullable();
            $table->string('username', 120);
            $table->string('work_email', 190)->nullable();
            $table->string('employee_reference', 64)->nullable();
            $table->string('password_hash', 255)->nullable();
            $table->enum('account_status', [
                'invited', 'active', 'security_locked', 'suspended', 'inactive', 'access_expired',
            ])->default('invited');
            $table->enum('employment_type', [
                'permanent', 'temporary', 'agency', 'contractor', 'external',
            ])->default('permanent');
            // Access date window. Outside it the account cannot sign in, even
            // when the status still says active.
            $table->timestamp('access_starts_at')->nullable();
            $table->timestamp('access_ends_at')->nullable();
            $table->unsignedSmallInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('password_set_at')->nullable();
            $table->timestamp('last_signed_in_at')->nullable();
            $table->timestamps();
            $table->unique(['organisation_id', 'username']);
            $table->index(['organisation_id', 'account_status']);
        });

        $schema->create('record7_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->foreignId('role_id')->constrained('record7_roles');
            $table->foreignId('service_id')->nullable()->constrained('record7_services');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'service_id']);
        });

        $schema->create('record7_user_service_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->enum('access_type', ['standard', 'manager', 'oversight', 'read_only', 'temporary']);
            $table->enum('status', ['active', 'suspended', 'expired', 'revoked'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'service_id']);
            $table->index(['user_id', 'status']);
        });

        /* ── Per-user permission rules (allow / deny) ───────────────────── */

        $schema->create('record7_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->foreignId('permission_id')->constrained('record7_permissions');
            $table->foreignId('service_id')->nullable()->constrained('record7_services');
            // A deny always beats an allow, and both beat the role matrix.
            $table->enum('effect', ['allow', 'deny']);
            $table->enum('status', ['active', 'suspended', 'expired', 'revoked'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('reason', 500);
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        /* ── Competency ─────────────────────────────────────────────────── */

        $schema->create('record7_competency_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 120);
            $table->string('description', 500);
            $table->unsignedSmallInteger('default_review_months')->nullable();
            // Which permission this competency gates. A permission with a
            // gating competency is refused unless the competency is current.
            $table->string('gates_permission', 64)->nullable();
            $table->timestamps();
        });

        $schema->create('record7_user_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->foreignId('competency_type_id')->constrained('record7_competency_types');
            $table->foreignId('service_id')->nullable()->constrained('record7_services');
            $table->enum('status', [
                'current', 'review_due', 'expired', 'suspended', 'not_assessed', 'not_required',
            ]);
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('review_due_at')->nullable();
            $table->string('evidence_reference', 120)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        /* ── Credentials lifecycle ──────────────────────────────────────── */

        $schema->create('record7_mfa_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->enum('method_type', [
                'passkey', 'security_key', 'authenticator_app', 'hardware_otp', 'work_email', 'sms',
            ]);
            $table->string('label', 120);
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['pending', 'active', 'revoked', 'lost'])->default('pending');
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        $schema->create('record7_account_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->char('token_hash', 64)->index();
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('sent_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        $schema->create('record7_password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->char('token_hash', 64)->index();
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('expires_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        /* ── Sessions ───────────────────────────────────────────────────── */

        $schema->create('record7_login_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('active_service_id')->nullable()->constrained('record7_services');
            $table->enum('status', ['active', 'locked', 'signed_out', 'expired', 'revoked'])->default('active');
            $table->string('device_reference', 120)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        /* ── Append-only access audit ───────────────────────────────────── */

        $schema->create('record7_access_audit_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('event_uuid')->unique();
            $table->foreignId('organisation_id')->nullable()->constrained('record7_organisations');
            $table->foreignId('service_id')->nullable()->constrained('record7_services');
            $table->foreignId('user_id')->nullable()->constrained('record7_users');
            // Snapshots: the audit must still read correctly after a person is
            // renamed or their role changes.
            $table->string('staff_name_at_time', 255)->nullable();
            $table->string('role_name_at_time', 120)->nullable();
            $table->string('event_type', 64);
            $table->enum('event_result', ['success', 'failure', 'denied', 'warning', 'information']);
            $table->timestamp('event_time');
            $table->string('session_reference', 64)->nullable();
            $table->string('device_reference', 120)->nullable();
            $table->string('reason', 500)->nullable();
            $table->enum('risk_level', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            // Chains each event to the previous one, so a removed row is
            // detectable rather than merely forbidden.
            $table->uuid('previous_event_uuid')->nullable();
            $table->json('metadata')->nullable();
            $table->index(['organisation_id', 'event_time']);
            $table->index(['service_id', 'event_time']);
            $table->index(['user_id', 'event_time']);
            $table->index(['event_type', 'event_result']);
        });

        $this->createAuditTriggers();
    }

    /**
     * Append-only enforced by the database, not merely by the application.
     *
     * The supplied package protects its audit table with SQLite triggers. The
     * same guarantee is reproduced here in MySQL: an UPDATE or DELETE against
     * the audit table raises rather than silently succeeding, so a bug or a
     * console session cannot quietly rewrite an access record.
     */
    private function createAuditTriggers(): void
    {
        $connection = DB::connection('record7');

        foreach (['update', 'delete'] as $action) {
            $connection->unprepared(
                "DROP TRIGGER IF EXISTS record7_audit_no_{$action}"
            );
            $connection->unprepared(
                "CREATE TRIGGER record7_audit_no_{$action}
                 BEFORE ".strtoupper($action)." ON record7_access_audit_events
                 FOR EACH ROW
                 SIGNAL SQLSTATE '45000'
                 SET MESSAGE_TEXT = 'record7 access audit events are append-only'"
            );
        }
    }

    public function down(): void
    {
        $connection = DB::connection('record7');
        $connection->unprepared('DROP TRIGGER IF EXISTS record7_audit_no_update');
        $connection->unprepared('DROP TRIGGER IF EXISTS record7_audit_no_delete');

        $schema = Schema::connection('record7');

        foreach ([
            'record7_access_audit_events',
            'record7_login_sessions',
            'record7_password_resets',
            'record7_account_invitations',
            'record7_mfa_methods',
            'record7_user_competencies',
            'record7_competency_types',
            'record7_user_permissions',
            'record7_user_service_access',
            'record7_user_roles',
            'record7_users',
            'record7_role_permissions',
            'record7_permissions',
            'record7_roles',
            'record7_services',
            'record7_organisations',
        ] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
