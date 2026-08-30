<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.4 correction — one clinical attempt, one record.
 *
 * THE PROBLEM THIS SOLVES.
 * A scheduled dose is protected from being recorded twice by `dose_claim`, a
 * generated column carrying a unique index. A PRN has no scheduled dose, so
 * `dose_claim` is NULL, and MySQL permits unlimited NULLs in a unique index —
 * which left the as-required path with no duplicate protection at all. A
 * double-click, a retry, or two workers pressing at once could each write a
 * separate administration for one clinical act.
 *
 * WHY NOT A TIME WINDOW.
 * "The same medicine within thirty seconds" is not a duplicate. A prescription
 * that permits a second dose permits it whenever the interval allows, and a
 * heuristic that guesses would eventually refuse a dose somebody needed. The
 * question is not "does this look like the last one" but "is this the same
 * attempt" — which only the server can answer, by issuing the identity itself.
 *
 * SO THE SERVER ISSUES IT.
 * Opening the give screen mints an attempt. The form carries it back. The
 * attempt is scoped to the person, the prescription, the house and the worker
 * it was issued to, so a token cannot be pointed at somebody else. Consuming it
 * is what creates the administration, and it can only be consumed once.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_prn_attempts', function (Blueprint $table) {
            $table->id();

            // Server-issued, never accepted from a browser as a new value. The
            // only thing a browser may do is hand one back.
            $table->string('token', 64)->unique();

            // Ownership is explicit rather than inferred through the client, so
            // a token from another company or another house cannot be matched
            // to a person by accident. The same reasoning as the welfare check.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('prescription_id')->constrained('record7_prescriptions');

            // Who it was minted for. A worker cannot spend a colleague's token.
            $table->foreignId('issued_to_user_id')->constrained('record7_users');

            $table->timestamp('issued_at');
            $table->timestamp('consumed_at')->nullable();

            // What it became. UNIQUE is the actual duplicate guard: one attempt
            // can never point at two administrations, so a replay that gets
            // past every other check still cannot write a second record.
            $table->foreignId('administration_id')->nullable()->unique()
                ->constrained('record7_administrations');

            $table->timestamps();

            $table->index(['client_id', 'prescription_id']);
            $table->index(['issued_to_user_id', 'consumed_at']);
        });

        // Consumed and unconsumed are the only two honest states. A row that
        // claims a time but no record, or a record but no time, is neither.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_prn_attempts
                ADD CONSTRAINT record7_prn_attempts_consumed_pair
                CHECK (
                    (consumed_at IS NULL AND administration_id IS NULL)
                    OR (consumed_at IS NOT NULL AND administration_id IS NOT NULL)
                )
        SQL);

        // An attempt records what happened. Re-pointing a spent one at a
        // different administration would be a rewrite of exactly the fact this
        // table exists to hold, so the database refuses it outright.
        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_prn_attempts_no_rewrite
            BEFORE UPDATE ON record7_prn_attempts
            FOR EACH ROW
            BEGIN
                IF OLD.consumed_at IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a spent as-required attempt cannot be changed';
                END IF;

                IF NEW.token <> OLD.token
                    OR NEW.client_id <> OLD.client_id
                    OR NEW.prescription_id <> OLD.prescription_id
                    OR NEW.service_id <> OLD.service_id
                    OR NEW.issued_to_user_id <> OLD.issued_to_user_id
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an as-required attempt cannot be re-pointed';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        DB::connection('record7')->unprepared(
            'DROP TRIGGER IF EXISTS record7_prn_attempts_no_rewrite'
        );

        Schema::connection('record7')->dropIfExists('record7_prn_attempts');
    }
};
