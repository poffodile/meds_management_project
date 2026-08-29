<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.3 — the evidence that somebody was actually found.
 *
 * WHY A RECORD AND NOT AN INFERENCE.
 * The welfare concern raised when a person cannot be found was clearing as soon
 * as anybody recorded anything else for them. That is a guess dressed as a
 * fact: a later medicine on the same day proves somebody wrote a row, not that
 * anybody went and looked. A concern about where a person is can only be
 * answered by somebody saying, on the record, what they found.
 *
 * WHAT IT DELIBERATELY REFUSES TO ACCEPT AS AN ANSWER.
 * Acknowledging the alert, owning it, escalating it, writing a note, closing a
 * review item, or any unrelated activity for that person. None of those
 * establish where anybody is, and letting them clear the concern is how an
 * urgent item becomes a tick-box.
 *
 * APPEND-ONLY, like every other clinical record here. What somebody recorded
 * finding cannot be edited afterwards, and the original "could not be found"
 * administration is never rewritten — the two rows sit together and tell the
 * whole story.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_welfare_checks', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();

            // Ownership is explicit rather than inferred through the client, so
            // evidence from another company or another house cannot be matched
            // to a concern by accident.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('client_id')->constrained('record7_clients');

            // The specific concern this answers. One answer per concern.
            $table->foreignId('administration_id')->unique()
                ->constrained('record7_administrations');

            // WHAT resolved it, structurally. Not free text, because a manager
            // reading this next month has to be able to tell these apart.
            $table->enum('resolution_type', [
                'located_and_well',
                'located_needs_follow_up',
                'whereabouts_confirmed_elsewhere',
            ]);

            // Supplementary only. It can never be the whole answer.
            $table->string('note', 500)->nullable();

            $table->foreignId('recorded_by_user_id')->constrained('record7_users');

            // The server's clock. A browser can say anything.
            $table->timestamp('occurred_at');

            $table->timestamps();

            $table->index(['service_id', 'occurred_at']);
        });

        // Append-only, the same way administrations are: refused at the
        // database, not merely discouraged in PHP.
        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_welfare_checks_no_rewrite
            BEFORE UPDATE ON record7_welfare_checks
            FOR EACH ROW
            BEGIN
                IF NEW.administration_id <> OLD.administration_id
                    OR NEW.client_id <> OLD.client_id
                    OR NEW.service_id <> OLD.service_id
                    OR NEW.organisation_id <> OLD.organisation_id
                    OR NEW.resolution_type <> OLD.resolution_type
                    OR NEW.recorded_by_user_id <> OLD.recorded_by_user_id
                    OR NEW.occurred_at <> OLD.occurred_at
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a record7 welfare check is a permanent record and cannot be rewritten';
                END IF;
            END
        SQL);

        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_welfare_checks_no_delete
            BEFORE DELETE ON record7_welfare_checks
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a record7 welfare check cannot be deleted';
            END
        SQL);

        // The evidence must belong to the concern it answers: same person, same
        // house, same organisation. Enforced here as well as in the recorder,
        // so a direct insert cannot attach a check to somebody else's concern.
        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_welfare_checks_validate_insert
            BEFORE INSERT ON record7_welfare_checks
            FOR EACH ROW
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM record7_administrations report
                    JOIN record7_services report_service ON report_service.id = report.service_id
                    WHERE report.id = NEW.administration_id
                        AND report.outcome = 'person_unavailable'
                        AND report.reason_code = 'not_found_in_service'
                        AND report.client_id = NEW.client_id
                        AND report.service_id = NEW.service_id
                        AND report_service.organisation_id = NEW.organisation_id
                ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a welfare check must answer a could-not-be-found record for the same person, house and organisation';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_welfare_checks_validate_insert');
        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_welfare_checks_no_delete');
        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_welfare_checks_no_rewrite');

        Schema::connection('record7')->dropIfExists('record7_welfare_checks');
    }
};
