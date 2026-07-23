<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the indexes the Medication Round's own queries need.
 *
 * All three tables are PRIMARY-KEY-only today, so every lookup below is a full
 * table scan. Trivial at current row counts; it degrades linearly as tenants are
 * added, and these are shared tables — one company's growth slows every other
 * company's round.
 *
 *  - care_plan_risks : buildRoundProps() does whereIn(client_id) + status filter
 *                      on EVERY round load. This is the resident risk-flag lookup.
 *  - home            : admin_id is the tenant-scoping column (company id) and is
 *                      unindexed — the single worst one here, because it is the
 *                      column every multi-tenant query will lean on.
 *  - client_consents : not read by the round yet (a known gap — a refused
 *                      medication consent is currently invisible). Indexed now so
 *                      wiring it in later doesn't add a scan.
 *
 * Schema came from a dump, so every add is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('care_plan_risks')
            && Schema::hasColumns('care_plan_risks', ['client_id', 'status', 'deleted_at'])
            && ! $this->hasIndex('care_plan_risks', 'care_plan_risks_client_status_idx')) {
            Schema::table('care_plan_risks', function (Blueprint $table) {
                $table->index(['client_id', 'status', 'deleted_at'], 'care_plan_risks_client_status_idx');
            });
        }

        if (Schema::hasTable('home')
            && Schema::hasColumn('home', 'admin_id')
            && ! $this->hasIndex('home', 'home_admin_id_idx')) {
            Schema::table('home', function (Blueprint $table) {
                $table->index('admin_id', 'home_admin_id_idx');
            });
        }

        if (Schema::hasTable('client_consents')
            && Schema::hasColumns('client_consents', ['client_id', 'consent_type'])
            && ! $this->hasIndex('client_consents', 'client_consents_client_type_idx')) {
            Schema::table('client_consents', function (Blueprint $table) {
                $table->index(['client_id', 'consent_type'], 'client_consents_client_type_idx');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('care_plan_risks', 'care_plan_risks_client_status_idx')) {
            Schema::table('care_plan_risks', fn (Blueprint $t) => $t->dropIndex('care_plan_risks_client_status_idx'));
        }
        if ($this->hasIndex('home', 'home_admin_id_idx')) {
            Schema::table('home', fn (Blueprint $t) => $t->dropIndex('home_admin_id_idx'));
        }
        if ($this->hasIndex('client_consents', 'client_consents_client_type_idx')) {
            Schema::table('client_consents', fn (Blueprint $t) => $t->dropIndex('client_consents_client_type_idx'));
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        return count(Schema::getConnection()->select(
            'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$table, $index]
        )) > 0;
    }
};
