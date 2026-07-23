<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A DATED, append-only weight series for residents (owner requirement REQ-MED-112).
 *
 * Why this exists (audit CR-09 / DA-02): weight lived in a single `service_user.weight`
 * varchar with a `weight_unit` enum of kg OR lbs. Two problems, both live:
 *   - 2 residents are recorded in lbs. Read as kg that is a 2.2x dose error, and
 *     paediatric doses here are weight-based (mg/kg).
 *   - There is no measurement date at all. The only timestamp is the row's updated_at,
 *     bumped by any profile edit, so staff see an UNDATED weight during administration.
 * A stale or wrong-unit weight silently justifies a wrong dose while looking authoritative.
 *
 * The fix:
 *   - ONE canonical unit: integer GRAMS. No unit column to disagree with the value.
 *     lb/st are rejected at input and converted at the UI, never stored (a unit *choice*
 *     is how a unit *error* happens).
 *   - Every reading carries WHEN it was measured (measured_at) separately from when it was
 *     typed (recorded_at), plus who recorded it. "Current weight" is DERIVED as the latest
 *     non-voided reading — never cached back onto service_user (same denormalisation trap
 *     as is_controlled drifting from cd_schedule).
 *   - Append-only: corrections supersede, nothing is destroyed.
 *
 * STALENESS THRESHOLD and whether a stale weight should BLOCK or merely WARN a
 * weight-based dose are NOT decided here — they need a qualified clinical reviewer, and
 * are age-dependent (an infant outgrows a weight far faster than a 16-year-old). This
 * migration builds the mechanism; the round shows the weight's AGE always and flags it,
 * and the policy sits in one documented constant to be set by a clinician.
 *
 * Schema came from a dump, so this is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_user_weights')) {
            Schema::create('service_user_weights', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('home_id')->index();          // tenant scope, denormalised deliberately
                $table->unsignedInteger('service_user_id')->index();
                // Canonical unit: grams, integer. 500 g – 500 kg guards unit-confusion + typos.
                $table->unsignedInteger('weight_grams');
                $table->dateTime('measured_at');                       // when WEIGHED
                $table->dateTime('recorded_at');                       // when TYPED (provenance)
                $table->unsignedInteger('recorded_by')->nullable();    // staff id
                $table->string('method', 30)->nullable();              // standing_scale|chair_scale|hoist|estimated|reported|legacy_import
                $table->boolean('is_estimated')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('supersedes_id')->nullable(); // correction chain (append-only)
                $table->timestamp('voided_at')->nullable();
                $table->string('void_reason', 255)->nullable();
                $table->timestamps();

                $table->index(['service_user_id', 'measured_at'], 'suw_client_measured_idx');
            });

            // Plausibility bound at the DB layer (defence in depth alongside input validation).
            // 0.5 kg – 500 kg. Wrapped in try: not every MySQL/MariaDB build enforces CHECK,
            // but where it does it catches a unit-confusion write.
            try {
                DB::statement('ALTER TABLE service_user_weights ADD CONSTRAINT suw_grams_plausible CHECK (weight_grams BETWEEN 500 AND 500000)');
            } catch (\Throwable $e) {
                // CHECK unsupported on this engine — validation still enforces it in the app.
            }
        }

        $this->backfill();
    }

    /**
     * Seed one reading per resident who has a legacy weight, flagged unreliable.
     *
     * measured_at = service_user.updated_at, but is_estimated + method='legacy_import' mark
     * it as NOT a trustworthy measurement date (it demonstrably isn't — several residents
     * share an identical bulk-seed timestamp). lbs values are converted to kg here so no
     * pounds ever enter the series.
     */
    private function backfill(): void
    {
        if (! Schema::hasTable('service_user_weights') || ! Schema::hasTable('service_user')) {
            return;
        }
        if (DB::table('service_user_weights')->where('method', 'legacy_import')->exists()) {
            return; // already backfilled — keep re-runs idempotent
        }

        $now = now();
        $rows = DB::table('service_user')
            ->whereNotNull('weight')->where('weight', '<>', '')
            ->get(['id', 'home_id', 'weight', 'weight_unit', 'updated_at']);

        $seeded = 0;
        $converted = 0;
        foreach ($rows as $r) {
            $val = (float) preg_replace('/[^0-9.]/', '', (string) $r->weight);
            if ($val <= 0) {
                continue;
            }

            // Normalise to grams. lbs -> kg (x0.45359237).
            if ($r->weight_unit === 'lbs') {
                $grams = (int) round($val * 453.59237);
                $converted++;
            } else {
                $grams = (int) round($val * 1000);
            }
            if ($grams < 500 || $grams > 500000) {
                continue; // implausible legacy value — leave it out rather than seed nonsense
            }

            DB::table('service_user_weights')->insert([
                'home_id' => (int) $r->home_id,
                'service_user_id' => (int) $r->id,
                'weight_grams' => $grams,
                'measured_at' => $r->updated_at ?: $now,
                'recorded_at' => $now,
                'recorded_by' => null,
                'method' => 'legacy_import',
                'is_estimated' => 1,
                'notes' => 'Imported from the legacy single-value weight field; measurement date is unreliable.'
                    .($r->weight_unit === 'lbs' ? ' Converted from '.$r->weight.' lb.' : ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $seeded++;
        }

        echo "  service_user_weights backfilled: {$seeded} ({$converted} converted from lbs)\n";
    }

    public function down(): void
    {
        // Only drop if this migration created the table AND no non-legacy readings exist —
        // real captured weights would be destroyed otherwise.
        if (! Schema::hasTable('service_user_weights')) {
            return;
        }
        $real = DB::table('service_user_weights')->where('method', '<>', 'legacy_import')->orWhereNull('method')->count();
        if ($real > 0) {
            throw new \RuntimeException(
                "Refusing to drop service_user_weights: {$real} non-legacy weight reading(s) exist. "
                .'Dropping the table would destroy captured clinical measurements.'
            );
        }
        Schema::dropIfExists('service_user_weights');
    }
};
