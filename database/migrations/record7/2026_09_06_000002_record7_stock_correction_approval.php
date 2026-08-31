<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.7, part two — teaching the existing correction queue about stock.
 *
 * NO SECOND APPROVAL SUBSYSTEM. Section 1.2 already has a correction request
 * that a manager approves under `correction_approval`, and building a parallel
 * one for stock would mean two places where a manager can say yes, two audit
 * shapes and two things to keep in step. So `record7_review_items` learns four
 * narrow columns instead.
 *
 * `subject_type` is already a short string ('administration'), so the
 * discriminator needs no class names and the CHECK below reads as English.
 *
 * TWO SHAPES, UNAMBIGUOUS AT THE DATABASE LEVEL.
 *
 *   administration_outcome  what the record should say instead, and — where a
 *                           direction needs it — the actual HISTORICAL dose.
 *                           subject_type must be 'administration'.
 *
 *   stock_delta             a signed adjustment to one balance, naming the
 *                           discrepancy movement it resolves.
 *                           subject_type must be 'stock_movement'.
 *
 * correction_shape is nullable so every existing row stays exactly as it is.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->table('record7_review_items', function (Blueprint $table) {
            $table->enum('correction_shape', ['administration_outcome', 'stock_delta'])
                ->nullable()->after('requested_outcome');

            // Signed. What the manager approves is a NUMBER, not the idea of a
            // correction, and the movement that carries it out must match.
            $table->decimal('requested_quantity_delta', 10, 3)
                ->nullable()->after('correction_shape');

            // The ACTUAL HISTORICAL amount, stated by whoever was there.
            // Never read from the prescription: a prescription can change
            // between the event and the correction, and reading today's figure
            // into last month's dose would silently rewrite history.
            $table->decimal('requested_dose_amount', 10, 3)
                ->nullable()->after('requested_quantity_delta');
            $table->string('requested_dose_unit', 30)
                ->nullable()->after('requested_dose_amount');
        });

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_review_items
                ADD CONSTRAINT record7_review_items_correction_shape
                CHECK (
                    (correction_shape IS NULL
                        AND requested_quantity_delta IS NULL
                        AND requested_dose_amount    IS NULL
                        AND requested_dose_unit      IS NULL)
                    OR (correction_shape = 'administration_outcome'
                        AND requested_quantity_delta IS NULL
                        AND subject_type = 'administration')
                    OR (correction_shape = 'stock_delta'
                        AND requested_quantity_delta IS NOT NULL
                        AND requested_outcome        IS NULL
                        AND requested_dose_amount    IS NULL
                        AND requested_dose_unit      IS NULL
                        AND subject_type = 'stock_movement')
                )
        SQL);

        // An amount without its unit is not a quantity.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_review_items
                ADD CONSTRAINT record7_review_items_dose_pair
                CHECK (
                    (requested_dose_amount IS NULL AND requested_dose_unit IS NULL)
                    OR (requested_dose_amount IS NOT NULL AND requested_dose_unit IS NOT NULL)
                )
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        $db->statement('ALTER TABLE record7_review_items DROP CHECK record7_review_items_dose_pair');
        $db->statement('ALTER TABLE record7_review_items DROP CHECK record7_review_items_correction_shape');

        Schema::connection('record7')->table('record7_review_items', function (Blueprint $table) {
            $table->dropColumn([
                'correction_shape',
                'requested_quantity_delta',
                'requested_dose_amount',
                'requested_dose_unit',
            ]);
        });
    }
};
