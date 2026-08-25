<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Record7 security verification — the pieces that make it a policy rather than
 * a code on every screen.
 *
 * WHY THESE TABLES EXIST
 * Asking for a second factor on every page, or repeatedly through one shift,
 * teaches people to type the code without reading it, and on a shared tablet
 * it simply gets written on a sticky note. Verification has to be demanded
 * when it is actually worth something: a new device, a first sign-in, after a
 * password reset, when something looks wrong, and for privileged roles.
 *
 * That needs Record7 to remember which devices it has seen — and, crucially,
 * to be able to refuse to remember a device that several people share.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        /* ── What a person can verify with ──────────────────────────────── */

        if ($schema->hasTable('record7_mfa_methods')) {
            $schema->table('record7_mfa_methods', function (Blueprint $table) {
                // Where the secret actually lives. Null for a passkey, whose
                // credential lives on the device, and for the prototype code.
                $table->text('secret')->nullable()->after('label');
                // A passkey's credential id, so a device can be recognised.
                $table->string('credential_reference', 255)->nullable()->after('secret');
                $table->unsignedSmallInteger('failed_attempts')->default(0)->after('status');
            });
        }

        /* ── Recovery codes ─────────────────────────────────────────────── */

        $schema->create('record7_recovery_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            // Only ever a hash. A recovery code is a password by another name.
            $table->char('code_hash', 64);
            $table->timestamp('issued_at');
            $table->timestamp('used_at')->nullable();
            $table->string('used_device', 120)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'used_at']);
        });

        /* ── Devices ────────────────────────────────────────────────────── */

        $schema->create('record7_trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            // A hash of the device signature. The raw user agent is never
            // stored: it is a fingerprinting surface and adds nothing here.
            $table->char('device_hash', 64);
            $table->string('label', 120)->nullable();
            /*
             * A SHARED device is never trusted, however many times it is used.
             *
             * The medicines trolley tablet is used by everyone on shift. If it
             * remembered the first person who signed in, the second person's
             * sign-in would silently inherit that trust — so a shared device is
             * marked and always asks.
             */
            $table->boolean('shared')->default(false);
            $table->enum('status', ['trusted', 'revoked', 'expired'])->default('trusted');
            $table->timestamp('trusted_at');
            $table->timestamp('trusted_until')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'device_hash']);
            $table->index(['user_id', 'status']);
        });

        /* ── Why a verification was demanded ────────────────────────────── */

        $schema->create('record7_verification_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('record7_users');
            $table->string('reason', 64);          // first_sign_in, new_device, …
            $table->string('method', 40)->nullable();  // authenticator_app, recovery_code, …
            $table->enum('result', ['required', 'passed', 'failed', 'skipped']);
            $table->char('device_hash', 64)->nullable();
            $table->timestamp('occurred_at');
            $table->index(['user_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('record7');

        $schema->dropIfExists('record7_verification_events');
        $schema->dropIfExists('record7_trusted_devices');
        $schema->dropIfExists('record7_recovery_codes');

        if ($schema->hasTable('record7_mfa_methods')) {
            $schema->table('record7_mfa_methods', function (Blueprint $table) {
                $table->dropColumn(['secret', 'credential_reference', 'failed_attempts']);
            });
        }
    }
};
