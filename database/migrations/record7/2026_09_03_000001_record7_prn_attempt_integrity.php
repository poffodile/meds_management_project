<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.4 correction, part two — closing the gaps the owner review found.
 *
 * WHAT WAS WRONG.
 * Three things, all found by attacking the table with raw SQL rather than
 * through Eloquent:
 *
 *   1. DELETE was refused by the model and by nothing else. A statement issued
 *      outside the application removed a spent attempt, taking with it the
 *      evidence of how a dose came to be recorded. The dose itself survived —
 *      administrations have their own delete trigger — but the claim that
 *      produced it did not.
 *
 *   2. `issued_at` could be moved on an unspent attempt, and the row's own
 *      account of when it was minted is part of what makes it evidence.
 *
 *   3. Nothing at database level checked that the administration an attempt was
 *      consumed against actually belonged to the same person, medicine, house
 *      and worker. The service checked it; the database took it on trust.
 *
 * WHAT THIS TABLE IS.
 * Not append-only, and it should not be described that way. It is a controlled
 * one-way transition: issued, then consumed, and nothing else ever. This
 * migration makes the database say that rather than the application.
 *
 * The previous trigger is dropped and recreated rather than edited, which is
 * the same pattern 2026_08_31_000001_record7_reoffer_chain used. No applied
 * migration is modified.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $db = DB::connection('record7');

        /* ── 1. Deleting an attempt is not a thing that happens ─────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_no_delete');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_prn_attempts_no_delete
            BEFORE DELETE ON record7_prn_attempts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'an as-required attempt cannot be deleted';
            END
        SQL);

        /* ── 2. One way, one time, and only ever the same pair ──────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_prn_attempts_no_rewrite
            BEFORE UPDATE ON record7_prn_attempts
            FOR EACH ROW
            BEGIN
                DECLARE admin_client      BIGINT UNSIGNED;
                DECLARE admin_prescription BIGINT UNSIGNED;
                DECLARE admin_service     BIGINT UNSIGNED;
                DECLARE admin_recorder    BIGINT UNSIGNED;
                DECLARE service_org       BIGINT UNSIGNED;

                -- Spent is final. Nothing about it moves again, including the
                -- linkage: re-pointing a consumed attempt at another record is
                -- the precise forgery this table exists to prevent.
                IF OLD.consumed_at IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a spent as-required attempt cannot be changed';
                END IF;

                -- Issuance and ownership are fixed from the moment of minting.
                -- issued_at is included because when it was minted is part of
                -- what makes the row evidence rather than an assertion.
                IF NEW.token              <> OLD.token
                    OR NEW.organisation_id   <> OLD.organisation_id
                    OR NEW.service_id        <> OLD.service_id
                    OR NEW.client_id         <> OLD.client_id
                    OR NEW.prescription_id   <> OLD.prescription_id
                    OR NEW.issued_to_user_id <> OLD.issued_to_user_id
                    OR NEW.issued_at         <> OLD.issued_at
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an as-required attempt cannot be re-pointed';
                END IF;

                -- The ONLY permitted change: both halves of the pair, together,
                -- from nothing to something. The CHECK constraint already
                -- refuses a mismatched pair; this refuses a pair that moves in
                -- any direction other than forward.
                IF NOT (NEW.consumed_at IS NOT NULL AND NEW.administration_id IS NOT NULL) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an as-required attempt can only be consumed, never cleared';
                END IF;

                -- The administration must be the one this attempt was for.
                -- Checked here rather than trusted from the application,
                -- because a statement that never went through the application
                -- is exactly the case this is defending against.
                SELECT client_id, prescription_id, service_id, recorded_by_user_id
                    INTO admin_client, admin_prescription, admin_service, admin_recorder
                    FROM record7_administrations
                    WHERE id = NEW.administration_id;

                IF admin_client IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that administration does not exist';
                END IF;

                IF admin_client      <> NEW.client_id
                    OR admin_prescription <> NEW.prescription_id
                    OR admin_service      <> NEW.service_id
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that administration is for another person, medicine or house';
                END IF;

                -- The worker who was given the attempt is the worker who
                -- recorded the dose. A colleague cannot spend it for them.
                IF admin_recorder <> NEW.issued_to_user_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that administration was recorded by somebody else';
                END IF;

                -- And the house it names really belongs to the company named.
                SELECT organisation_id INTO service_org
                    FROM record7_services WHERE id = NEW.service_id;

                IF service_org IS NULL OR service_org <> NEW.organisation_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that house does not belong to that organisation';
                END IF;
            END
        SQL);

        /* ── 3. An attempt is born unspent ──────────────────────────────── */

        // Without this, everything above is avoidable by inserting a row that
        // is already consumed and already pointing wherever you like.
        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_prn_attempts_validate_insert
            BEFORE INSERT ON record7_prn_attempts
            FOR EACH ROW
            BEGIN
                DECLARE service_org BIGINT UNSIGNED;

                IF NEW.consumed_at IS NOT NULL OR NEW.administration_id IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an as-required attempt is issued unspent and consumed later';
                END IF;

                SELECT organisation_id INTO service_org
                    FROM record7_services WHERE id = NEW.service_id;

                IF service_org IS NULL OR service_org <> NEW.organisation_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that house does not belong to that organisation';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_no_delete');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_validate_insert');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_prn_attempts_no_rewrite');

        // Restore the looser trigger this migration replaced, so down() really
        // returns the schema to where it was.
        $db->unprepared(<<<'SQL'
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
};
