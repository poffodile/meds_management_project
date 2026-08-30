<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.6, part two — the rules the database enforces on its own.
 *
 * All of this is enforced in PHP as well. It is here because a service can be
 * bypassed by a forged request, a console command, or a future controller that
 * forgets, and a lifecycle history that can be edited from outside the
 * application is not a history.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $db = DB::connection('record7');

        /* ── Append-only, without exception ─────────────────────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_round_lifecycle_no_rewrite
            BEFORE UPDATE ON record7_round_lifecycle_events
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a round lifecycle event is permanent; append another instead';
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_no_delete');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_round_lifecycle_no_delete
            BEFORE DELETE ON record7_round_lifecycle_events
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a round lifecycle event cannot be deleted';
            END
        SQL);

        /* ── What an event must satisfy to exist ────────────────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_round_lifecycle_validate_insert
            BEFORE INSERT ON record7_round_lifecycle_events
            FOR EACH ROW
            BEGIN
                DECLARE round_service  BIGINT UNSIGNED;
                DECLARE service_org    BIGINT UNSIGNED;
                DECLARE item_kind      VARCHAR(40);
                DECLARE item_status    VARCHAR(20);
                DECLARE item_subject   VARCHAR(60);
                DECLARE item_subject_id BIGINT UNSIGNED;
                DECLARE item_service   BIGINT UNSIGNED;

                -- TENANT AND ROUND SCOPE. A cross-house or cross-company
                -- identifier fails here even if every layer above was bypassed.
                SELECT service_id INTO round_service
                    FROM record7_rounds WHERE id = NEW.round_id;

                IF round_service IS NULL OR round_service <> NEW.service_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that round is not in that house';
                END IF;

                SELECT organisation_id INTO service_org
                    FROM record7_services WHERE id = NEW.service_id;

                IF service_org IS NULL OR service_org <> NEW.organisation_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that house does not belong to that organisation';
                END IF;

                -- The approval named by a reopen must be an approved request
                -- for THIS round, in THIS house. Checked here rather than
                -- trusted, because a statement that never went through the
                -- application is what this is defending against.
                IF NEW.review_item_id IS NOT NULL THEN
                    SELECT kind, status, subject_type, subject_id, service_id
                        INTO item_kind, item_status, item_subject, item_subject_id, item_service
                        FROM record7_review_items WHERE id = NEW.review_item_id;

                    IF item_kind IS NULL OR item_kind <> 'round_reopen_request' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that is not a request to reopen a round';
                    END IF;

                    IF item_status <> 'approved' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that request has not been approved';
                    END IF;

                    IF item_subject <> 'round' OR item_subject_id <> NEW.round_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that approval was for a different round';
                    END IF;

                    IF item_service <> NEW.service_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that approval belongs to another house';
                    END IF;
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_no_rewrite');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_no_delete');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_round_lifecycle_validate_insert');
    }
};
