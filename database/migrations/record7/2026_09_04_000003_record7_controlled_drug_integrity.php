<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.5, part three — the rules the database enforces on its own.
 *
 * Everything here is also enforced in PHP. It is here as well because a service
 * can be bypassed by a forged request, a console command, or a future
 * controller that forgets, and a controlled-drug balance that can be edited
 * from outside the application is not a controlled-drug balance.
 *
 * ATOMICITY, AND WHY THE LINK POINTS THIS WAY.
 * The FK is administrations -> register, not the other way round. If the
 * register pointed at the administration it would have to be written first with
 * a null link and UPDATED afterwards, and updating an append-only ledger is
 * exactly what must never happen. So the movement is written first and the
 * administration carries the reference, frozen on insert.
 *
 * This also closes the gap the Section 2.4 review found: witness identity and
 * dose figures on an administration were protected by neither the model nor the
 * trigger. Nothing exploited it because nothing wrote a witness. Section 2.5
 * writes one, so it is closed first.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $db = DB::connection('record7');

        /* ── 1. The administration carries its movement ─────────────────── */

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            $table->foreignId('cd_register_id')->nullable()->unique()
                ->after('controlled_drug_no_quantity_removed')
                ->constrained('record7_cd_register');
        });

        /* ── 2. The register is append-only, without exception ──────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_cd_register_no_rewrite
            BEFORE UPDATE ON record7_cd_register
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a controlled drug register entry is permanent; record a correction instead';
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_no_delete');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_cd_register_no_delete
            BEFORE DELETE ON record7_cd_register
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a controlled drug register entry cannot be deleted';
            END
        SQL);

        /* ── 3. Everything a movement must satisfy to exist ─────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_cd_register_validate_insert
            BEFORE INSERT ON record7_cd_register
            FOR EACH ROW
            BEGIN
                DECLARE client_service   BIGINT UNSIGNED;
                DECLARE service_org      BIGINT UNSIGNED;
                DECLARE presc_client     BIGINT UNSIGNED;
                DECLARE prior_client     BIGINT UNSIGNED;
                DECLARE expected_after   DECIMAL(10,3);

                -- TENANT AND PERSON SCOPE. A cross-house or cross-company
                -- identifier fails here even if every layer above was bypassed.
                SELECT service_id INTO client_service
                    FROM record7_clients WHERE id = NEW.client_id;
                IF client_service IS NULL OR client_service <> NEW.service_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that person is not in that house';
                END IF;

                SELECT organisation_id INTO service_org
                    FROM record7_services WHERE id = NEW.service_id;
                IF service_org IS NULL OR service_org <> NEW.organisation_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that house does not belong to that organisation';
                END IF;

                IF NEW.prescription_id IS NOT NULL THEN
                    SELECT client_id INTO presc_client
                        FROM record7_prescriptions WHERE id = NEW.prescription_id;
                    IF presc_client IS NULL OR presc_client <> NEW.client_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that prescription belongs to somebody else';
                    END IF;
                END IF;

                -- A correction points at an EARLIER entry for the SAME balance.
                IF NEW.corrects_register_id IS NOT NULL THEN
                    SELECT client_id INTO prior_client
                        FROM record7_cd_register WHERE id = NEW.corrects_register_id;
                    IF prior_client IS NULL OR prior_client <> NEW.client_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a correction must name an entry for the same person';
                    END IF;
                END IF;

                -- THE ARITHMETIC OF AN EPISODE. Removed accounts for itself,
                -- exactly: what went in, what went back, what was destroyed.
                IF NEW.action IN ('administration', 'non_administration') THEN
                    IF NEW.quantity_removed IS NULL THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'say how much was removed from storage';
                    END IF;

                    IF NEW.quantity_removed <> IFNULL(NEW.quantity_given, 0)
                                             + IFNULL(NEW.quantity_returned, 0)
                                             + IFNULL(NEW.quantity_wasted, 0) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'the quantity removed must equal what was given, returned and wasted';
                    END IF;
                END IF;

                -- A non-administration is a removal that produced no dose.
                IF NEW.action = 'non_administration' THEN
                    IF IFNULL(NEW.quantity_given, 0) <> 0 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a non administration cannot have given a dose';
                    END IF;
                    IF NEW.quantity_removed <= 0 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a non administration exists only where stock was removed';
                    END IF;
                END IF;

                -- THE BALANCE IS DERIVED HERE. A figure supplied by a browser
                -- is not merely ignored, it is rejected: the trigger recomputes
                -- and refuses anything that disagrees.
                SET expected_after = CASE NEW.action
                    WHEN 'receipt'            THEN IFNULL(NEW.balance_before, 0) + NEW.quantity_received
                    WHEN 'administration'     THEN NEW.balance_before - (IFNULL(NEW.quantity_given, 0) + IFNULL(NEW.quantity_wasted, 0))
                    WHEN 'non_administration' THEN NEW.balance_before - IFNULL(NEW.quantity_wasted, 0)
                    WHEN 'return_to_storage'  THEN NEW.balance_before + IFNULL(NEW.quantity_returned, 0)
                    WHEN 'waste'              THEN NEW.balance_before - IFNULL(NEW.quantity_wasted, 0)
                    WHEN 'stock_check'        THEN NEW.counted_quantity
                    ELSE NEW.balance_after
                END;

                IF NEW.action <> 'correction' AND NEW.balance_after <> expected_after THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'the balance is worked out here and that figure does not agree';
                END IF;

                -- AN ORDINARY MOVEMENT NEVER GOES NEGATIVE. Only a verified
                -- physical count, or a correction to one, may record that the
                -- ledger and the cupboard disagree.
                IF NEW.balance_after < 0 AND NEW.action NOT IN ('stock_check', 'correction') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an ordinary movement cannot drive the balance below zero';
                END IF;

                -- The witness, where there is one, must be a different person
                -- in the same organisation. The CHECK covers the first half.
                IF NEW.witnessed_by_user_id IS NOT NULL THEN
                    IF (SELECT organisation_id FROM record7_users WHERE id = NEW.witnessed_by_user_id)
                       <> NEW.organisation_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that witness is not in this organisation';
                    END IF;
                END IF;
            END
        SQL);

        /* ── 4. A controlled administration carries its movement ────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_administrations_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_validate_insert
            BEFORE INSERT ON record7_administrations
            FOR EACH ROW
            BEGIN
                DECLARE target_dose        BIGINT UNSIGNED;
                DECLARE target_client      BIGINT UNSIGNED;
                DECLARE new_client_org     BIGINT UNSIGNED;
                DECLARE new_service_org    BIGINT UNSIGNED;
                DECLARE medicine_controlled TINYINT(1);
                DECLARE register_client    BIGINT UNSIGNED;
                DECLARE register_presc     BIGINT UNSIGNED;

                IF NEW.corrects_administration_id IS NOT NULL
                    AND NEW.reoffer_of_administration_id IS NOT NULL
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an administration cannot be both a correction and a re-offer';
                END IF;

                -- Section 2.3, unchanged: a re-offer answers the same dose for
                -- the same person in the same house and company.
                IF NEW.reoffer_of_administration_id IS NOT NULL THEN
                    SELECT scheduled_dose_id, client_id
                        INTO target_dose, target_client
                        FROM record7_administrations
                        WHERE id = NEW.reoffer_of_administration_id;

                    SELECT organisation_id INTO new_client_org
                        FROM record7_clients WHERE id = NEW.client_id;
                    SELECT organisation_id INTO new_service_org
                        FROM record7_services WHERE id = NEW.service_id;

                    IF target_dose IS NULL
                        OR target_dose <> NEW.scheduled_dose_id
                        OR target_client <> NEW.client_id
                        OR new_client_org IS NULL
                        OR new_client_org <> new_service_org
                    THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a re-offer must point to the same-dose refusal in the same organisation and house';
                    END IF;
                END IF;

                /* Section 2.5 — the movement must exist where stock moved. */
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

        /* ── 5. The immutability gap the 2.4 review found ───────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_no_rewrite
            BEFORE UPDATE ON record7_administrations
            FOR EACH ROW
            BEGIN
                IF NEW.outcome <> OLD.outcome
                    OR NEW.client_id <> OLD.client_id
                    OR NEW.prescription_id <> OLD.prescription_id
                    OR NEW.recorded_by_user_id <> OLD.recorded_by_user_id
                    OR NEW.administered_at <> OLD.administered_at
                    OR NOT (NEW.corrects_administration_id <=> OLD.corrects_administration_id)
                    OR NOT (NEW.reoffer_of_administration_id <=> OLD.reoffer_of_administration_id)
                    OR NOT (NEW.witnessed_by_user_id <=> OLD.witnessed_by_user_id)
                    OR NOT (NEW.dose_amount <=> OLD.dose_amount)
                    OR NOT (NEW.dose_unit <=> OLD.dose_unit)
                    OR NOT (NEW.controlled_drug_no_quantity_removed <=> OLD.controlled_drug_no_quantity_removed)
                    OR NOT (NEW.cd_register_id <=> OLD.cd_register_id)
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'record7 administrations are a permanent record; record a correction or re-offer instead';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_no_rewrite');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_no_delete');
        $db->unprepared('DROP TRIGGER IF EXISTS record7_cd_register_validate_insert');

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            $table->dropForeign(['cd_register_id']);
            $table->dropColumn('cd_register_id');
        });

        // Restore the Section 2.3/2.4 triggers exactly as they were.
        $db->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_no_rewrite
            BEFORE UPDATE ON record7_administrations
            FOR EACH ROW
            BEGIN
                IF NEW.outcome <> OLD.outcome
                    OR NEW.client_id <> OLD.client_id
                    OR NEW.prescription_id <> OLD.prescription_id
                    OR NEW.recorded_by_user_id <> OLD.recorded_by_user_id
                    OR NEW.administered_at <> OLD.administered_at
                    OR NOT (NEW.corrects_administration_id <=> OLD.corrects_administration_id)
                    OR NOT (NEW.reoffer_of_administration_id <=> OLD.reoffer_of_administration_id)
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'record7 administrations are a permanent record; record a correction or re-offer instead';
                END IF;
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_administrations_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_validate_insert
            BEFORE INSERT ON record7_administrations
            FOR EACH ROW
            BEGIN
                DECLARE target_dose BIGINT UNSIGNED;
                DECLARE target_client BIGINT UNSIGNED;
                DECLARE new_client_org BIGINT UNSIGNED;
                DECLARE new_service_org BIGINT UNSIGNED;

                IF NEW.corrects_administration_id IS NOT NULL
                    AND NEW.reoffer_of_administration_id IS NOT NULL
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an administration cannot be both a correction and a re-offer';
                END IF;

                IF NEW.reoffer_of_administration_id IS NOT NULL THEN
                    SELECT scheduled_dose_id, client_id
                        INTO target_dose, target_client
                        FROM record7_administrations
                        WHERE id = NEW.reoffer_of_administration_id;

                    SELECT organisation_id INTO new_client_org
                        FROM record7_clients WHERE id = NEW.client_id;
                    SELECT organisation_id INTO new_service_org
                        FROM record7_services WHERE id = NEW.service_id;

                    IF target_dose IS NULL
                        OR target_dose <> NEW.scheduled_dose_id
                        OR target_client <> NEW.client_id
                        OR new_client_org IS NULL
                        OR new_client_org <> new_service_org
                    THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a re-offer must point to the same-dose refusal in the same organisation and house';
                    END IF;
                END IF;
            END
        SQL);
    }
};
