<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Restore the Section 2.3 re-offer check, alongside the Section 2.5 additions.
 *
 * WHAT WENT WRONG, RECORDED SO IT IS NOT REPEATED.
 * Section 2.5 needed to add controlled-drug rules to the administrations insert
 * trigger. MySQL has no "alter trigger", so the whole thing has to be dropped
 * and recreated — and in doing that I rewrote the Section 2.3 re-offer check
 * from memory instead of carrying it across verbatim. The rewrite lost
 * `target.outcome = 'refused'` and the four organisation joins, which meant a
 * re-offer could point at an administration that was never a refusal.
 *
 * The regression suite caught it immediately (test_a_re_offer_must_target_a
 * _refusal), which is the entire reason that test exists.
 *
 * This migration recreates the trigger with the Section 2.3 clause copied
 * EXACTLY as 2026_08_31_000001 wrote it, and the Section 2.5 rules appended
 * after it rather than woven through it.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        DB::connection('record7')->unprepared(
            'DROP TRIGGER IF EXISTS record7_administrations_validate_insert'
        );

        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_validate_insert
            BEFORE INSERT ON record7_administrations
            FOR EACH ROW
            BEGIN
                DECLARE medicine_controlled TINYINT(1);
                DECLARE register_client     BIGINT UNSIGNED;
                DECLARE register_presc      BIGINT UNSIGNED;

                /* ── Section 2.3, carried across verbatim ──────────────── */

                IF NEW.corrects_administration_id IS NOT NULL
                    AND NEW.reoffer_of_administration_id IS NOT NULL
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an administration cannot be both a correction and a re-offer';
                END IF;

                IF NEW.reoffer_of_administration_id IS NOT NULL THEN
                    IF NEW.reoffer_of_administration_id = NEW.id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a re-offer cannot refer to itself';
                    END IF;

                    IF NOT EXISTS (
                        SELECT 1
                        FROM record7_administrations target
                        JOIN record7_clients target_client ON target_client.id = target.client_id
                        JOIN record7_services target_service ON target_service.id = target.service_id
                        JOIN record7_clients new_client ON new_client.id = NEW.client_id
                        JOIN record7_services new_service ON new_service.id = NEW.service_id
                        WHERE target.id = NEW.reoffer_of_administration_id
                            AND target.outcome = 'refused'
                            AND target.scheduled_dose_id = NEW.scheduled_dose_id
                            AND target.client_id = NEW.client_id
                            AND target.prescription_id = NEW.prescription_id
                            AND target.service_id = NEW.service_id
                            AND target_client.organisation_id = new_client.organisation_id
                            AND target_service.organisation_id = new_service.organisation_id
                            AND target_client.organisation_id = target_service.organisation_id
                            AND new_client.organisation_id = new_service.organisation_id
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a re-offer must point to the same-dose refusal in the same organisation and house';
                    END IF;
                END IF;

                /* ── Section 2.5, appended ─────────────────────────────── */

                SELECT m.is_controlled INTO medicine_controlled
                    FROM record7_prescriptions p
                    JOIN record7_medicines m ON m.id = p.medicine_id
                    WHERE p.id = NEW.prescription_id;

                IF medicine_controlled = 1 AND NEW.corrects_administration_id IS NULL THEN
                    IF NEW.outcome IN ('given', 'self_administered') THEN
                        -- A dose that went in always moved stock.
                        IF NEW.cd_register_id IS NULL THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'a controlled administration must carry its register movement';
                        END IF;
                    ELSE
                        -- A non-administration: the storage declaration decides.
                        -- Nothing removed means nothing to account for, and a
                        -- zero-quantity movement would pollute the ledger.
                        IF NEW.controlled_drug_no_quantity_removed IS NOT TRUE
                            AND NEW.cd_register_id IS NULL THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'controlled stock was removed; account for it in the register';
                        END IF;

                        IF NEW.controlled_drug_no_quantity_removed IS TRUE
                            AND NEW.cd_register_id IS NOT NULL THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'nothing was removed, so there is no movement to record';
                        END IF;
                    END IF;
                END IF;

                -- Whatever movement is named must be for this person and this
                -- medicine. Checked here, not trusted from the application.
                IF NEW.cd_register_id IS NOT NULL THEN
                    SELECT client_id, prescription_id
                        INTO register_client, register_presc
                        FROM record7_cd_register WHERE id = NEW.cd_register_id;

                    IF register_client IS NULL OR register_client <> NEW.client_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that register movement is for another person';
                    END IF;

                    IF register_presc IS NOT NULL AND register_presc <> NEW.prescription_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that register movement is for another medicine';
                    END IF;
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        // Nothing to undo: 2026_09_04_000003's down() rebuilds the Section 2.3
        // trigger, and this migration only ever made that trigger stricter.
    }
};
