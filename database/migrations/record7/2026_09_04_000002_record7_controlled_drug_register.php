<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.5, part two — the register and the balance it runs.
 *
 * TWO TABLES, DOING DIFFERENT JOBS.
 *
 * `record7_cd_register` is the history: every movement of controlled stock,
 * append-only, never updated, never deleted. It is the record somebody reads
 * afterwards to find out what happened.
 *
 * `record7_cd_balances` is one row per person per preparation holding the
 * current figure. It is DERIVED — rebuildable from the register at any time,
 * and a test asserts it agrees with the ledger. It exists for one reason: you
 * cannot take a row lock on a row that does not exist, and the safety decision
 * "is there enough" has to be made while holding a lock or it is worthless.
 *
 * WHY THE PREPARATION IS SNAPSHOTTED.
 * `record7_medicines.form` and `.strength` are ordinary mutable columns with no
 * guard on them. A register keyed on medicine_id alone would be quietly
 * falsified by somebody correcting a strength: every historical balance would
 * silently start meaning something else. So the register snapshots what was
 * counted, and the balance is keyed on a hash of that snapshot. Correct a
 * strength and later movements land on a NEW key, where the divergence is
 * visible, rather than corrupting the old one.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        /* ── The ledger ─────────────────────────────────────────────────── */

        Schema::connection('record7')->create('record7_cd_register', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();

            // Ownership explicit rather than inferred through the client, so a
            // movement from another company cannot be matched to a balance by
            // accident. Same reasoning as the welfare check.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('medicine_id')->constrained('record7_medicines');

            // Null on a receipt that arrives before the prescription is entered.
            $table->foreignId('prescription_id')->nullable()->constrained('record7_prescriptions');

            // WHAT WAS COUNTED, as it read at the time. Never re-derived.
            $table->string('medicine_name_at_time', 255);
            $table->string('form_at_time', 80)->nullable();
            $table->string('strength_at_time', 80)->nullable();
            $table->string('unit', 30);
            $table->string('cd_schedule_at_time', 4)->nullable();

            $table->enum('action', [
                'receipt',
                'administration',
                'non_administration',
                'return_to_storage',
                'waste',
                'stock_check',
                'correction',
            ]);

            // Four figures, not one signed number. Returned intact stock
            // re-enters the balance; waste leaves it and is a different act.
            $table->decimal('quantity_received', 10, 3)->nullable();
            $table->decimal('quantity_removed', 10, 3)->nullable();
            $table->decimal('quantity_given', 10, 3)->nullable();
            $table->decimal('quantity_returned', 10, 3)->nullable();
            $table->decimal('quantity_wasted', 10, 3)->nullable();

            // A verified physical count, and what the ledger expected. BOTH are
            // kept: the expected figure is evidence of the divergence, never
            // overwritten by the count that disproved it.
            $table->decimal('expected_quantity', 10, 3)->nullable();
            $table->decimal('counted_quantity', 10, 3)->nullable();

            // Null only on the opening receipt, where there is no "before".
            $table->decimal('balance_before', 10, 3)->nullable();
            $table->decimal('balance_after', 10, 3);
            $table->boolean('is_discrepancy')->default(false);

            $table->foreignId('recorded_by_user_id')->constrained('record7_users');
            $table->foreignId('witnessed_by_user_id')->nullable()->constrained('record7_users');

            // What the rule DEMANDED at the time, stored rather than recomputed.
            // A service's setting can change; what was required on the night is
            // a fact about this entry, and re-deriving it later would silently
            // rewrite history.
            $table->boolean('witness_was_required');
            $table->string('unwitnessed_basis', 190)->nullable();

            // Evidence, never identity. A name cannot be supplied instead of a
            // real user — the FK above is the identity.
            $table->string('witness_name_at_time', 255)->nullable();
            $table->string('witness_role_at_time', 120)->nullable();

            $table->timestamp('occurred_at');

            // A correction points at what it corrects. The original stays.
            $table->foreignId('corrects_register_id')->nullable()
                ->constrained('record7_cd_register');

            $table->string('notes', 500)->nullable();

            // Server-allocated, never browser-supplied. The unique key below is
            // what makes a duplicate movement impossible and what serialises
            // two workers onto the same balance.
            $table->unsignedInteger('sequence_no');

            $table->timestamps();

            $table->index(['client_id', 'occurred_at']);
            $table->index(['service_id', 'occurred_at']);
            $table->index(['is_discrepancy', 'service_id']);
        });

        // The preparation key: generated by MySQL from the snapshot columns, so
        // it is deterministic, cannot be supplied by a browser, and cannot
        // drift from the snapshot it describes. medicine_name is deliberately
        // OUTSIDE it — a corrected spelling is a display fix and must not split
        // a balance in two.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD COLUMN preparation_key CHAR(64)
                    GENERATED ALWAYS AS (
                        SHA2(CONCAT_WS('|', medicine_id,
                                            IFNULL(form_at_time, ''),
                                            IFNULL(strength_at_time, ''),
                                            unit,
                                            IFNULL(cd_schedule_at_time, '')), 256)
                    ) STORED
        SQL);

        // One movement per position in the chain. Two workers reading the same
        // tail cannot both write: the second loses here rather than silently
        // recording a balance derived from state that has already moved.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD UNIQUE KEY record7_cd_register_one_per_step (client_id, preparation_key, sequence_no)
        SQL);

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD CONSTRAINT record7_cd_register_quantities_sane
                CHECK (
                    (quantity_received IS NULL OR quantity_received >= 0)
                    AND (quantity_removed  IS NULL OR quantity_removed  >= 0)
                    AND (quantity_given    IS NULL OR quantity_given    >= 0)
                    AND (quantity_returned IS NULL OR quantity_returned >= 0)
                    AND (quantity_wasted   IS NULL OR quantity_wasted   >= 0)
                    AND (counted_quantity  IS NULL OR counted_quantity  >= 0)
                )
        SQL);

        // A witness is present when one was required, and a reason is present
        // when one was not. Neither state may be silent.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD CONSTRAINT record7_cd_register_witness_pair
                CHECK (
                    (witness_was_required = 1 AND witnessed_by_user_id IS NOT NULL)
                    OR (witness_was_required = 0 AND unwitnessed_basis IS NOT NULL)
                )
        SQL);

        // Nobody witnesses themselves.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD CONSTRAINT record7_cd_register_witness_is_another_person
                CHECK (witnessed_by_user_id IS NULL OR witnessed_by_user_id <> recorded_by_user_id)
        SQL);

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_cd_register
                ADD CONSTRAINT record7_cd_register_action_shape
                CHECK (
                    (action <> 'stock_check' OR counted_quantity IS NOT NULL)
                    AND (action <> 'correction' OR corrects_register_id IS NOT NULL)
                    AND (action <> 'receipt' OR quantity_received IS NOT NULL)
                )
        SQL);

        /* ── The lockable head ──────────────────────────────────────────── */

        Schema::connection('record7')->create('record7_cd_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->char('preparation_key', 64);
            $table->string('unit', 30);

            $table->decimal('current_balance', 10, 3)->default(0);
            $table->unsignedInteger('last_sequence_no')->default(0);
            $table->foreignId('last_register_id')->nullable()->constrained('record7_cd_register');

            $table->timestamps();

            $table->unique(['client_id', 'preparation_key']);
        });
    }

    public function down(): void
    {
        Schema::connection('record7')->dropIfExists('record7_cd_balances');
        Schema::connection('record7')->dropIfExists('record7_cd_register');
    }
};
