<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.2 — one successful administration per planned dose, enforced by the
 * database rather than by hoping two requests never overlap.
 *
 * WHY A CONSTRAINT AND NOT A CHECK-THEN-INSERT.
 * Two workers on two phones, a double tap, a browser retry after a timeout, two
 * open tabs — every one of those puts two requests in flight at once, and every
 * "does a record already exist?" test in application code has a gap between the
 * question and the answer. A medicine recorded twice is not a cosmetic problem:
 * it is a clinical record that says a person was given something twice, and it
 * cannot be deleted afterwards because administrations are permanent.
 *
 * WHY A GENERATED COLUMN RATHER THAN A PLAIN UNIQUE INDEX.
 * A plain unique on scheduled_dose_id would be wrong in two directions.
 *
 *   PRN administrations carry no scheduled dose at all. MySQL treats NULL as
 *   distinct in a unique index, so those rows are untouched either way — but
 *   only because the column stays NULL, which the expression preserves.
 *
 *   A correction (Section 2.7) is a NEW row about the SAME planned dose. A
 *   plain unique would make corrections impossible, so the claim includes what
 *   the row corrects: an original claims "dose 5, correcting nothing", and a
 *   correction claims "dose 5, correcting 12". Both can exist; two originals
 *   cannot.
 *
 * The one thing this also forbids is two corrections of the same original. That
 * is deliberate rather than accidental: a second correction of an already
 * corrected record should chain from the correction, not race the first one.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Generated columns and indexes are DDL. Kept out of a transaction on
        // purpose — MySQL commits implicitly around DDL, and a half-applied
        // "transaction" is worse than an honest failure.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD COLUMN dose_claim VARCHAR(64)
                    GENERATED ALWAYS AS (
                        CASE
                            WHEN scheduled_dose_id IS NULL THEN NULL
                            ELSE CONCAT(scheduled_dose_id, ':', COALESCE(corrects_administration_id, 0))
                        END
                    ) STORED
        SQL);

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD UNIQUE KEY record7_administrations_one_per_dose (dose_claim)
        SQL);
    }

    public function down(): void
    {
        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations DROP INDEX record7_administrations_one_per_dose'
        );

        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations DROP COLUMN dose_claim'
        );
    }
};
