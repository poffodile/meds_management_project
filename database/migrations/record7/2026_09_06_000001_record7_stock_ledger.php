<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.7, part one — the ordinary stock ledger and the balance it runs.
 *
 * TWO TABLES, DOING DIFFERENT JOBS — the same split Section 2.5 uses.
 *
 * `record7_stock_movements` is the history: every movement of ordinary stock,
 * append-only, never updated, never deleted. It is what somebody reads
 * afterwards to find out what happened.
 *
 * `record7_stock_balances` is one row per owner per preparation holding the
 * current figure. It is DERIVED — rebuildable from the ledger at any time, and
 * a test asserts it agrees. It exists for one reason: you cannot take a row
 * lock on a row that does not exist, and "is there enough" has to be decided
 * while holding a lock or it is worthless.
 *
 * WHY owner_ref EXISTS.
 * MySQL treats NULLs as distinct in a unique index, so a key on
 * (service_id, client_id, preparation_key, sequence_no) would silently permit
 * two service-owned movements at the same position in the chain — the exact
 * concurrency hole the key exists to close. The generated IFNULL(client_id, 0)
 * closes it without a hash.
 *
 * WHY THE PREPARATION IS SNAPSHOTTED.
 * `record7_medicines.form` and `.strength` are ordinary mutable columns with no
 * guard on them. A ledger keyed on medicine_id alone would be quietly falsified
 * by somebody correcting a strength: every historical balance would start
 * meaning something else. So the ledger snapshots what was counted and the
 * balance is keyed on a hash of that snapshot.
 *
 * The key deliberately OMITS cd_schedule, which Section 2.5's includes.
 * Controlled medicines never appear here, so the column would always be null —
 * and a different formula means the two keys can never be accidentally
 * compared or joined.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');
        $db = DB::connection('record7');

        /* ── The ledger ─────────────────────────────────────────────────── */

        $schema->create('record7_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();

            // Ownership explicit rather than inferred through the client, so a
            // movement from another company cannot be matched to a balance by
            // accident. Same reasoning as the controlled drug register.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');

            // NEVER inferred from whether client_id is null. Section 2.7
            // implements 'client' only; 'service' exists so a later section
            // needs no data migration, and the application refuses it.
            $table->enum('owner_type', ['client', 'service']);
            $table->foreignId('client_id')->nullable()->constrained('record7_clients');

            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->foreignId('prescription_id')->nullable()->constrained('record7_prescriptions');

            // WHAT WAS COUNTED, as it read at the time. Never re-derived.
            $table->string('medicine_name_at_time', 255);
            $table->string('form_at_time', 80)->nullable();
            $table->string('strength_at_time', 80)->nullable();
            $table->string('unit', 30);

            $table->enum('action', [
                'opening_balance',
                'receipt',
                'administration',
                'non_administration',
                'return_to_stock',
                'waste',
                'stock_check',
                'correction',
            ]);

            // Separate figures, not one signed number. Returned intact stock
            // re-enters the balance; waste leaves it and is a different act.
            $table->decimal('quantity_received', 10, 3)->nullable();
            $table->decimal('quantity_removed', 10, 3)->nullable();
            $table->decimal('quantity_given', 10, 3)->nullable();
            $table->decimal('quantity_returned', 10, 3)->nullable();
            $table->decimal('quantity_wasted', 10, 3)->nullable();

            // Signed, and only on a correction. Section 2.5's trigger skips the
            // balance check for a correction; this one does not, so even a
            // correction's arithmetic is recomputed and verified.
            $table->decimal('quantity_delta', 10, 3)->nullable();

            // A verified physical count, and what the ledger expected. BOTH are
            // kept: the expected figure is the evidence of the divergence and is
            // never overwritten by the count that disproved it.
            $table->decimal('expected_quantity', 10, 3)->nullable();
            $table->decimal('counted_quantity', 10, 3)->nullable();

            // Null only on the opening balance, where there is no "before".
            $table->decimal('balance_before', 10, 3)->nullable();
            $table->decimal('balance_after', 10, 3);
            $table->boolean('is_discrepancy')->default(false);

            // What the worker confirmed when the ledger said there was not
            // enough and the medicine was physically present anyway. Required
            // in exactly that case, and only where there was a point of care.
            $table->foreignId('shortfall_verified_by_user_id')->nullable()
                ->constrained('record7_users');
            $table->timestamp('shortfall_verified_at')->nullable();
            $table->enum('shortfall_basis', [
                'physically_counted_sufficient',
                'unrecorded_stock_present',
                'other',
            ])->nullable();
            $table->string('shortfall_statement', 190)->nullable();

            // What the worker saw, for whoever reconciles this. Deliberately
            // NOT a count: it sets no counted_quantity, moves no balance and
            // opens no discrepancy of its own.
            $table->decimal('shortfall_observed_quantity', 10, 3)->nullable();

            $table->foreignId('recorded_by_user_id')->constrained('record7_users');
            $table->foreignId('witnessed_by_user_id')->nullable()->constrained('record7_users');

            // Evidence, never identity. The FK above is who the witness IS.
            $table->string('witness_name_at_time', 255)->nullable();
            $table->string('witness_role_at_time', 120)->nullable();

            $table->timestamp('occurred_at');

            // A correction points at what it corrects. The original stays.
            $table->foreignId('corrects_movement_id')->nullable()->unique()
                ->constrained('record7_stock_movements');

            // The approved Section 1.2 correction request this consumed.
            // UNIQUE: one approval buys exactly one movement.
            $table->foreignId('review_item_id')->nullable()->unique()
                ->constrained('record7_review_items');

            $table->string('notes', 500)->nullable();

            // Server-allocated, never browser-supplied. The unique key below is
            // what makes a duplicate movement impossible and what serialises
            // two workers onto the same balance.
            $table->unsignedInteger('sequence_no');

            // Section 2.7 writes no imported rows. These exist for the eventual
            // production migration, which is the only writer that may set them.
            $table->boolean('imported')->default(false);
            $table->string('import_note', 500)->nullable();

            $table->timestamps();

            $table->index(['service_id', 'occurred_at']);
            $table->index(['client_id', 'occurred_at']);
            $table->index(['is_discrepancy', 'service_id']);
        });

        // Generated by MySQL, so they are deterministic, cannot be supplied by
        // a browser, and cannot drift from the columns they describe.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD COLUMN owner_ref BIGINT UNSIGNED
                    GENERATED ALWAYS AS (IFNULL(client_id, 0)) STORED
        SQL);

        // medicine_name is deliberately OUTSIDE the key — a corrected spelling
        // is a display fix and must not split a balance in two.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD COLUMN preparation_key CHAR(64)
                    GENERATED ALWAYS AS (
                        SHA2(CONCAT_WS('|', medicine_id,
                                            IFNULL(form_at_time, ''),
                                            IFNULL(strength_at_time, ''),
                                            unit), 256)
                    ) STORED
        SQL);

        // One movement per position in the chain. Two workers reading the same
        // tail cannot both write: the second loses here rather than silently
        // recording a balance derived from state that has already moved.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD UNIQUE KEY record7_stock_movements_one_per_step
                    (service_id, owner_ref, preparation_key, sequence_no)
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD KEY record7_stock_movements_balance
                    (service_id, owner_ref, preparation_key, is_discrepancy)
        SQL);

        /* ── What a movement must satisfy to exist ──────────────────────── */

        // Ownership is stated, never inferred from a null.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_ownership_shape
                CHECK (
                    (owner_type = 'client'  AND client_id IS NOT NULL)
                    OR (owner_type = 'service' AND client_id IS NULL)
                )
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_quantities_sane
                CHECK (
                    (quantity_received  IS NULL OR quantity_received  >= 0)
                    AND (quantity_removed   IS NULL OR quantity_removed   >= 0)
                    AND (quantity_given     IS NULL OR quantity_given     >= 0)
                    AND (quantity_returned  IS NULL OR quantity_returned  >= 0)
                    AND (quantity_wasted    IS NULL OR quantity_wasted    >= 0)
                    AND (counted_quantity   IS NULL OR counted_quantity   >= 0)
                    AND (expected_quantity  IS NULL OR expected_quantity  >= 0)
                    AND (shortfall_observed_quantity IS NULL
                         OR shortfall_observed_quantity >= 0)
                )
        SQL);

        // An approved correction may also ESTABLISH a debit that never existed
        // (specification section 9.3, historical amount known). That movement
        // is an `administration` and carries the approval it consumed, so
        // review_item_id is permitted on exactly two verbs and nowhere else.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_action_shape
                CHECK (
                    (action <> 'opening_balance' OR quantity_received IS NOT NULL)
                    AND (action <> 'receipt'     OR quantity_received IS NOT NULL)
                    AND (action <> 'stock_check' OR (counted_quantity IS NOT NULL
                                                     AND expected_quantity IS NOT NULL))
                    AND (action =  'stock_check' OR (counted_quantity IS NULL
                                                     AND expected_quantity IS NULL))
                    AND (action <> 'correction'  OR (corrects_movement_id IS NOT NULL
                                                     AND quantity_delta    IS NOT NULL
                                                     AND review_item_id    IS NOT NULL))
                    AND (action =  'correction'  OR quantity_delta IS NULL)
                    AND (review_item_id IS NULL
                         OR action IN ('correction', 'administration'))
                )
        SQL);

        // Any negative balance is a disagreement, whatever produced it. This
        // holds for a point-of-care shortfall and a retrospective correction
        // alike, so nothing can drive the ledger below zero quietly.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_negative_is_discrepancy
                CHECK (balance_after >= 0 OR is_discrepancy = 1)
        SQL);

        // Point-of-care evidence is required only where there WAS a point of
        // care: a contemporaneous administration recorded by the person giving
        // the dose. A retrospective movement written under an approved
        // correction has no cupboard to stand at.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_shortfall_evidence
                CHECK (
                    action <> 'administration'
                    OR review_item_id IS NOT NULL
                    OR balance_after >= 0
                    OR (shortfall_verified_by_user_id IS NOT NULL
                        AND shortfall_verified_at     IS NOT NULL
                        AND shortfall_basis           IS NOT NULL
                        AND shortfall_statement       IS NOT NULL)
                )
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_shortfall_scope
                CHECK (
                    shortfall_basis IS NULL
                    OR (action = 'administration' AND review_item_id IS NULL)
                )
        SQL);

        // Nobody witnesses themselves.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_witness_is_another_person
                CHECK (witnessed_by_user_id IS NULL
                       OR witnessed_by_user_id <> recorded_by_user_id)
        SQL);

        // An imported row says what it is and what was lost.
        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_movements
                ADD CONSTRAINT record7_stock_movements_import_note
                CHECK (imported = 0 OR import_note IS NOT NULL)
        SQL);

        /* ── The lockable head ──────────────────────────────────────────── */

        $schema->create('record7_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->enum('owner_type', ['client', 'service']);
            $table->foreignId('client_id')->nullable()->constrained('record7_clients');
            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->char('preparation_key', 64);
            $table->string('unit', 30);

            $table->decimal('current_balance', 10, 3)->default(0);
            $table->unsignedInteger('last_sequence_no')->default(0);
            $table->foreignId('last_movement_id')->nullable()
                ->constrained('record7_stock_movements');

            // MAX(occurred_at) over stock_check movements. Still ledger-derived.
            $table->timestamp('last_counted_at')->nullable();

            $table->timestamps();
        });

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_balances
                ADD COLUMN owner_ref BIGINT UNSIGNED
                    GENERATED ALWAYS AS (IFNULL(client_id, 0)) STORED
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_balances
                ADD UNIQUE KEY record7_stock_balances_one_per_owner
                    (service_id, owner_ref, preparation_key)
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_balances
                ADD KEY record7_stock_balances_service_balance
                    (service_id, current_balance)
        SQL);

        $db->statement(<<<'SQL'
            ALTER TABLE record7_stock_balances
                ADD CONSTRAINT record7_stock_balances_ownership_shape
                CHECK (
                    (owner_type = 'client'  AND client_id IS NOT NULL)
                    OR (owner_type = 'service' AND client_id IS NULL)
                )
        SQL);
    }

    public function down(): void
    {
        Schema::connection('record7')->dropIfExists('record7_stock_balances');
        Schema::connection('record7')->dropIfExists('record7_stock_movements');
    }
};
