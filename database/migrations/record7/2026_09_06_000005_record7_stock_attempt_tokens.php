<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.7, part five — one stock act, one movement.
 *
 * THE PROBLEM THIS SOLVES.
 * A receipt, a count, a waste and a return each arrive from a plain form post
 * and each create or destroy quantity. A double-click doubles a delivery. The
 * clinical paths are already protected — a scheduled dose by `dose_claim`, a
 * PRN by its own attempt token — but these four had nothing.
 *
 * A time window would be wrong for the same reason it was wrong for PRN: two
 * genuine receipts of the same medicine on the same afternoon are not a
 * duplicate. The question is not "does this look like the last one" but "is
 * this the same attempt", which only the server can answer by issuing the
 * identity itself.
 *
 * A correction needs no token: it must name one approved review item, and
 * `record7_stock_movements.review_item_id` is UNIQUE, so a replay finds the
 * approval already spent.
 *
 * Modelled directly on `record7_prn_attempts` rather than invented afresh, so
 * there is one shape of replay protection in Record7 and not two.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_stock_attempts', function (Blueprint $table) {
            $table->id();

            // Server-issued, never accepted from a browser as a new value.
            $table->string('token', 64)->unique();

            // Ownership explicit rather than inferred, so a token from another
            // company or another house cannot be matched to a balance.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('stock_balance_id')->constrained('record7_stock_balances');

            // What it may be spent on. A count token cannot book in a delivery.
            $table->enum('action', ['opening_balance', 'receipt', 'stock_check', 'waste', 'return_to_stock']);

            // Who it was minted for. A worker cannot spend a colleague's token.
            $table->foreignId('issued_to_user_id')->constrained('record7_users');

            $table->timestamp('issued_at');
            $table->timestamp('consumed_at')->nullable();

            // What it became. UNIQUE is the actual duplicate guard: one attempt
            // can never point at two movements.
            $table->foreignId('stock_movement_id')->nullable()->unique()
                ->constrained('record7_stock_movements');

            $table->timestamps();

            $table->index(['stock_balance_id', 'action']);
            $table->index(['issued_to_user_id', 'consumed_at']);
        });

        // Consumed and unconsumed are the only two honest states.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_stock_attempts
                ADD CONSTRAINT record7_stock_attempts_consumed_pair
                CHECK (
                    (consumed_at IS NULL AND stock_movement_id IS NULL)
                    OR (consumed_at IS NOT NULL AND stock_movement_id IS NOT NULL)
                )
        SQL);

        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_attempts_no_rewrite
            BEFORE UPDATE ON record7_stock_attempts
            FOR EACH ROW
            BEGIN
                IF OLD.consumed_at IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a spent stock attempt cannot be changed';
                END IF;

                IF NEW.token <> OLD.token
                    OR NEW.stock_balance_id <> OLD.stock_balance_id
                    OR NEW.service_id <> OLD.service_id
                    OR NEW.action <> OLD.action
                    OR NEW.issued_to_user_id <> OLD.issued_to_user_id
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a stock attempt cannot be re-pointed';
                END IF;
            END
        SQL);

        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_attempts_no_delete
            BEFORE DELETE ON record7_stock_attempts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a stock attempt cannot be deleted';
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_attempts_no_rewrite');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_attempts_no_delete');

        Schema::connection('record7')->dropIfExists('record7_stock_attempts');
    }
};
