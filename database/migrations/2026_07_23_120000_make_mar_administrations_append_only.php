<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the medication administration record APPEND-ONLY.
 *
 * The compliance review (2026-07-23) named this the single most inspector-visible
 * gap: correcting a dose — e.g. "Given" changed to "Refused" — overwrote the
 * original row in place (MARSheetService::administer did fill()+save()), leaving no
 * trace of the previous value, who recorded it, or why it changed. That sits
 * directly against CQC Reg 17 (good governance) and the Children's Homes Regs 2015
 * reg 23(2)(c) record-keeping duty: a clinical record must be contemporaneous and
 * its amendments auditable, not destructive.
 *
 * The mechanism (implemented in MARSheetService): an amendment no longer edits the
 * existing row. It writes a NEW row carrying the corrected values and pointing back
 * to the one it replaces (supersedes_id), and flips the old row's is_current to 0
 * with superseded_at set. Nothing is ever updated in place except that flip;
 * nothing is ever deleted. "Current" = is_current = 1, enforced by a global scope
 * on the model so every reader sees the live version by default and history is
 * opt-in.
 *
 *   is_current        1 = the live version of this slot; 0 = a superseded prior version
 *   supersedes_id     the row this one replaced (NULL for an original)
 *   superseded_at     when this row was replaced (NULL while current)
 *   amendment_reason  why the change was made (NULL for an original)
 *
 * NOT done here (deliberately): a UNIQUE constraint on (mar_sheet_id, date,
 * time_slot). CR-08 is more subtle than a plain unique index because a PRN sheet
 * legitimately has many current rows on one nominal slot, so a blanket unique index
 * would wrongly block a second PRN dose. The concurrent-write race is already
 * serialised in the app by the row lock in applyRecord(); a DB-level constraint
 * that respects the PRN/scheduled distinction is a separate change. Flagged, not
 * silently skipped.
 *
 * Schema came from a dump, so every add is guarded and re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mar_administrations')) {
            return;
        }

        Schema::table('mar_administrations', function (Blueprint $table) {
            if (! Schema::hasColumn('mar_administrations', 'is_current')) {
                $table->boolean('is_current')->default(true)->after('code');
            }
            if (! Schema::hasColumn('mar_administrations', 'supersedes_id')) {
                $table->unsignedBigInteger('supersedes_id')->nullable()->after('is_current');
            }
            if (! Schema::hasColumn('mar_administrations', 'superseded_at')) {
                $table->dateTime('superseded_at')->nullable()->after('supersedes_id');
            }
            if (! Schema::hasColumn('mar_administrations', 'amendment_reason')) {
                $table->string('amendment_reason', 255)->nullable()->after('superseded_at');
            }
        });

        // Every existing row is a live, un-amended record.
        DB::table('mar_administrations')->whereNull('is_current')->update(['is_current' => 1]);

        // Reads filter is_current constantly (the global scope adds it to every query);
        // this keeps that cheap as history accumulates.
        if (! $this->hasIndex('mar_administrations', 'mar_adm_sheet_current_idx')
            && Schema::hasColumn('mar_administrations', 'is_current')) {
            Schema::table('mar_administrations', function (Blueprint $table) {
                $table->index(['mar_sheet_id', 'date', 'is_current'], 'mar_adm_sheet_current_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('mar_administrations')) {
            return;
        }

        // Refuse to roll back if history exists — dropping these columns would collapse
        // superseded versions and their live counterparts into indistinguishable
        // duplicate rows, destroying exactly the audit trail this migration created.
        $superseded = Schema::hasColumn('mar_administrations', 'is_current')
            ? DB::table('mar_administrations')->where('is_current', 0)->count()
            : 0;

        if ($superseded > 0) {
            throw new \RuntimeException(
                "Refusing to roll back: {$superseded} superseded administration record(s) exist. "
                .'Dropping the append-only columns would destroy the amendment audit trail and leave '
                .'duplicate current+superseded rows indistinguishable.'
            );
        }

        if ($this->hasIndex('mar_administrations', 'mar_adm_sheet_current_idx')) {
            Schema::table('mar_administrations', fn (Blueprint $t) => $t->dropIndex('mar_adm_sheet_current_idx'));
        }
        Schema::table('mar_administrations', function (Blueprint $table) {
            foreach (['amendment_reason', 'superseded_at', 'supersedes_id', 'is_current'] as $col) {
                if (Schema::hasColumn('mar_administrations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
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
