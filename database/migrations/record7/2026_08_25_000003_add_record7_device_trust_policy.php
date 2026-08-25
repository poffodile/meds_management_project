<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Device-trust duration belongs to the organisation, not to the code.
 *
 * A domiciliary service issuing personal phones and a children's home with one
 * shared trolley tablet have genuinely different answers to "how long should we
 * remember this device". Baking thirty days into a constant forces both to
 * accept a number that suits neither, and the one that finds it too long has no
 * way to tighten it.
 *
 * Null means "use the secure default from configuration", so an organisation
 * that never thinks about it still gets a sensible answer.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        if ($schema->hasTable('record7_organisations')) {
            $schema->table('record7_organisations', function (Blueprint $table) {
                $table->unsignedSmallInteger('trusted_device_days')->nullable()->after('status');
                // An organisation can switch device trust off altogether, and
                // then everybody verifies every time.
                $table->boolean('device_trust_enabled')->default(true)->after('trusted_device_days');
            });
        }

        if ($schema->hasTable('record7_trusted_devices')) {
            $schema->table('record7_trusted_devices', function (Blueprint $table) {
                // Who revoked it, and why. A device withdrawn from someone must
                // be answerable later.
                $table->foreignId('revoked_by_user_id')->nullable()->after('status')
                    ->constrained('record7_users');
                $table->timestamp('revoked_at')->nullable()->after('revoked_by_user_id');
                $table->string('revoked_reason', 255)->nullable()->after('revoked_at');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('record7');

        if ($schema->hasTable('record7_trusted_devices')) {
            $schema->table('record7_trusted_devices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('revoked_by_user_id');
                $table->dropColumn(['revoked_at', 'revoked_reason']);
            });
        }

        if ($schema->hasTable('record7_organisations')) {
            $schema->table('record7_organisations', function (Blueprint $table) {
                $table->dropColumn(['trusted_device_days', 'device_trust_enabled']);
            });
        }
    }
};
