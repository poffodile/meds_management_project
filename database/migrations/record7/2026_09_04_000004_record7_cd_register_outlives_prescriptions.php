<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The register outlives the prescription it was written against.
 *
 * WHAT THIS FIXES, AND WHY IT IS NOT A CONVENIENCE.
 * A controlled drug register is a permanent account of physical stock. It is
 * append-only: no entry is ever updated or removed. A foreign key to
 * `record7_prescriptions` put those two facts in direct conflict — the register
 * could never release the prescription, and the prescription could therefore
 * never be removed, so an ordinary catalogue or record lifecycle operation
 * would fail against a table it has no business being blocked by.
 *
 * The fix follows the principle the register was already built on: it SNAPSHOTS
 * everything it needs to be read on its own — the medicine name, form, strength,
 * unit and schedule as they stood at the time. It never has to join back to a
 * prescription to say what was counted. `prescription_id` is a pointer for
 * convenience and for joining while the prescription still exists, not the
 * source of the entry's meaning.
 *
 * So the column stays, indexed, and the constraint goes. The register keeps
 * pointing at the prescription where there still is one, and remains readable
 * where there is not.
 *
 * `client_id`, `service_id`, `organisation_id` and `medicine_id` KEEP their
 * constraints. Those are the ownership facts that make an entry findable and
 * attributable, and none of them is removed in ordinary use.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        DB::connection('record7')->statement(
            'ALTER TABLE record7_cd_register DROP FOREIGN KEY record7_cd_register_prescription_id_foreign'
        );

        DB::connection('record7')->statement(
            'ALTER TABLE record7_cd_register ADD INDEX record7_cd_register_prescription_id_index (prescription_id)'
        );
    }

    public function down(): void
    {
        DB::connection('record7')->statement(
            'ALTER TABLE record7_cd_register DROP INDEX record7_cd_register_prescription_id_index'
        );

        DB::connection('record7')->statement(
            'ALTER TABLE record7_cd_register
                ADD CONSTRAINT record7_cd_register_prescription_id_foreign
                FOREIGN KEY (prescription_id) REFERENCES record7_prescriptions (id)'
        );
    }
};
