<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.7, part two — the rules the database enforces on its own.
 *
 * Everything here is also enforced in PHP. It is here as well because a service
 * can be bypassed by a forged request, a console command, or a future
 * controller that forgets, and a stock balance that can be edited from outside
 * the application is not a stock balance.
 *
 * WHY THE LINK POINTS THIS WAY.
 * The FK is administrations -> movements, not the other way round. If the
 * movement pointed at the administration it would have to be written first with
 * a null link and UPDATED afterwards, and updating an append-only ledger is
 * exactly what must never happen. So the movement is written first and the
 * administration carries the reference, frozen on insert.
 *
 * WHY THE BALANCE HEAD IS NOT SIMPLY FROZEN.
 * It legitimately changes — that is its job. What must be impossible is moving
 * it to a figure the ledger never produced. So the update trigger freezes the
 * identity and requires the new figure to match the balance_after of the
 * movement it names. `update(['current_balance' => 500])` is refused; the
 * service's own step-six update is not.
 *
 * The two Section 2.3/2.5 administration triggers are reproduced here VERBATIM
 * from the live definitions and appended to. They are not rewritten from
 * memory: an earlier section lost a clause that way.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $db = DB::connection('record7');

        /* ── 1. The administration carries its movement ─────────────────── */

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            $table->foreignId('stock_movement_id')->nullable()->unique()
                ->after('cd_register_id')
                ->constrained('record7_stock_movements');

            // The ordinary equivalent of controlled_drug_no_quantity_removed.
            // Null is honest where there is nothing to declare; it is refused
            // where the preparation is tracked and a quantity is knowable.
            $table->boolean('stock_no_quantity_removed')->nullable()
                ->after('stock_movement_id');
        });

        /* ── 2. The ledger is append-only, without exception ────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_movements_no_rewrite');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_movements_no_rewrite
            BEFORE UPDATE ON record7_stock_movements
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a stock movement is permanent; record a correction instead';
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_movements_no_delete');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_movements_no_delete
            BEFORE DELETE ON record7_stock_movements
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a stock movement cannot be deleted';
            END
        SQL);

        /* ── 3. Everything a movement must satisfy to exist ─────────────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_movements_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_movements_validate_insert
            BEFORE INSERT ON record7_stock_movements
            FOR EACH ROW
            BEGIN
                DECLARE medicine_controlled TINYINT(1);
                DECLARE client_service      BIGINT UNSIGNED;
                DECLARE client_org          BIGINT UNSIGNED;
                DECLARE service_org         BIGINT UNSIGNED;
                DECLARE presc_client        BIGINT UNSIGNED;
                DECLARE prior_service       BIGINT UNSIGNED;
                DECLARE prior_owner         BIGINT UNSIGNED;
                DECLARE prior_key           CHAR(64);
                DECLARE prior_discrepancy   TINYINT(1);
                DECLARE this_key            CHAR(64);
                DECLARE shape               VARCHAR(40);
                DECLARE item_status         VARCHAR(20);
                DECLARE item_service        BIGINT UNSIGNED;
                DECLARE item_delta          DECIMAL(10,3);
                DECLARE expected_after      DECIMAL(10,3);

                /* THE CONTROLLED BOUNDARY, BEFORE ANYTHING ELSE.
                   Section 2.5 is the only authority for a controlled balance,
                   and no ordinary movement may exist for one. */
                SELECT is_controlled INTO medicine_controlled
                    FROM record7_medicines WHERE id = NEW.medicine_id;

                IF medicine_controlled = 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a controlled medicine is accounted for in the controlled drug register';
                END IF;

                /* SECTION 2.7 IMPLEMENTS CLIENT-OWNED STOCK ONLY. The column
                   exists so a later section needs no data migration. Until one
                   deliberately enables it, a service-owned write fails closed
                   here as well as in the service. */
                IF NEW.owner_type <> 'client' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'service-owned stock is not implemented in this version';
                END IF;

                /* TENANT AND PERSON SCOPE. A cross-house or cross-company
                   identifier fails here even if every layer above was bypassed. */
                SELECT service_id, organisation_id INTO client_service, client_org
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

                IF client_org IS NULL OR client_org <> NEW.organisation_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that person does not belong to that organisation';
                END IF;

                IF NEW.prescription_id IS NOT NULL THEN
                    SELECT client_id INTO presc_client
                        FROM record7_prescriptions WHERE id = NEW.prescription_id;
                    IF presc_client IS NULL OR presc_client <> NEW.client_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that prescription belongs to somebody else';
                    END IF;
                END IF;

                /* THE OPENING POSITION. balance_before is absent only where
                   there is genuinely no before. */
                IF NEW.action = 'opening_balance' THEN
                    IF NEW.balance_before IS NOT NULL OR NEW.sequence_no <> 1 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'an opening balance is the first movement and has nothing before it';
                    END IF;
                ELSE
                    IF NEW.balance_before IS NULL THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'every movement after the first records what the balance was';
                    END IF;
                END IF;

                /* THE ARITHMETIC OF AN EPISODE. Removed accounts for itself,
                   exactly: what went in, what went back, what was destroyed. */
                IF NEW.action IN ('administration', 'non_administration') THEN
                    IF NEW.quantity_removed IS NULL OR NEW.quantity_removed <= 0 THEN
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

                IF NEW.action = 'administration' AND IFNULL(NEW.quantity_given, 0) <= 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'an administration records a dose that was given';
                END IF;

                IF NEW.action = 'non_administration' AND IFNULL(NEW.quantity_given, 0) <> 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a non administration cannot have given a dose';
                END IF;

                /* A COUNT OBSERVES; IT DOES NOT CORRECT. The expected figure is
                   the balance it was taken against, and the balance does not
                   move. Only an approved correction moves it. */
                IF NEW.action = 'stock_check' THEN
                    IF NEW.expected_quantity <> NEW.balance_before THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'the expected figure on a count is the balance it was taken against';
                    END IF;

                    IF (ABS(NEW.counted_quantity - NEW.expected_quantity) > 0.0005)
                       <> (NEW.is_discrepancy = 1) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a count that disagrees is a discrepancy, and one that agrees is not';
                    END IF;
                END IF;

                /* THE BALANCE IS DERIVED HERE. A figure supplied by a browser
                   is not merely ignored, it is rejected: the trigger recomputes
                   and refuses anything that disagrees. */
                SET expected_after = CASE NEW.action
                    WHEN 'opening_balance'    THEN NEW.quantity_received
                    WHEN 'receipt'            THEN NEW.balance_before + NEW.quantity_received
                    WHEN 'administration'     THEN NEW.balance_before - (IFNULL(NEW.quantity_given, 0) + IFNULL(NEW.quantity_wasted, 0))
                    WHEN 'non_administration' THEN NEW.balance_before - IFNULL(NEW.quantity_wasted, 0)
                    WHEN 'return_to_stock'    THEN NEW.balance_before + IFNULL(NEW.quantity_returned, 0)
                    WHEN 'waste'              THEN NEW.balance_before - IFNULL(NEW.quantity_wasted, 0)
                    WHEN 'stock_check'        THEN NEW.balance_before
                    WHEN 'correction'         THEN NEW.balance_before + NEW.quantity_delta
                END;

                IF NEW.balance_after <> expected_after THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'the balance is worked out here and that figure does not agree';
                END IF;

                /* AN ORDINARY MOVEMENT NEVER GOES NEGATIVE. Only a dose that
                   was actually given, an approved correction, or a count
                   inheriting an already-negative position may sit below zero,
                   and the CHECK makes every one of those a discrepancy. */
                IF NEW.balance_after < 0
                   AND NEW.action IN ('opening_balance', 'receipt', 'return_to_stock',
                                      'waste', 'non_administration') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'that movement cannot drive the balance below zero';
                END IF;

                /* A CORRECTION POINTS AT AN EARLIER MOVEMENT FOR THE SAME
                   BALANCE, and consumes an approval that says so. */
                IF NEW.corrects_movement_id IS NOT NULL THEN
                    SET this_key = SHA2(CONCAT_WS('|', NEW.medicine_id,
                                                       IFNULL(NEW.form_at_time, ''),
                                                       IFNULL(NEW.strength_at_time, ''),
                                                       NEW.unit), 256);

                    SELECT service_id, IFNULL(client_id, 0), preparation_key, is_discrepancy
                        INTO prior_service, prior_owner, prior_key, prior_discrepancy
                        FROM record7_stock_movements WHERE id = NEW.corrects_movement_id;

                    IF prior_service IS NULL
                        OR prior_service <> NEW.service_id
                        OR prior_owner <> IFNULL(NEW.client_id, 0)
                        OR prior_key <> this_key THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a correction must name an earlier movement for the same balance';
                    END IF;
                END IF;

                IF NEW.review_item_id IS NOT NULL THEN
                    SELECT status, service_id, correction_shape, requested_quantity_delta
                        INTO item_status, item_service, shape, item_delta
                        FROM record7_review_items WHERE id = NEW.review_item_id;

                    IF item_status IS NULL OR item_status <> 'approved' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that correction has not been approved';
                    END IF;

                    IF item_service <> NEW.service_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that approval belongs to another house';
                    END IF;

                    /* Path B reconciles a DISCREPANCY and must name one. Path A
                       compensates an ordinary administration movement, which is
                       not a discrepancy, so the requirement is scoped to the
                       shape the manager actually approved. */
                    IF shape = 'stock_delta' THEN
                        IF NEW.action <> 'correction' THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'an approved reconciliation is carried out as a correction';
                        END IF;

                        IF prior_discrepancy IS NULL OR prior_discrepancy <> 1 THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'a reconciliation must name an unresolved discrepancy';
                        END IF;

                        IF item_delta IS NULL OR item_delta <> NEW.quantity_delta THEN
                            SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'the correction must apply exactly the quantity that was approved';
                        END IF;
                    END IF;
                END IF;
            END
        SQL);

        /* ── 4. The head may move, but only where the ledger took it ────── */

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_balances_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_balances_validate_insert
            BEFORE INSERT ON record7_stock_balances
            FOR EACH ROW
            BEGIN
                DECLARE medicine_controlled TINYINT(1);
                DECLARE client_service      BIGINT UNSIGNED;
                DECLARE service_org         BIGINT UNSIGNED;

                SELECT is_controlled INTO medicine_controlled
                    FROM record7_medicines WHERE id = NEW.medicine_id;

                IF medicine_controlled = 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a controlled medicine is accounted for in the controlled drug register';
                END IF;

                IF NEW.owner_type <> 'client' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'service-owned stock is not implemented in this version';
                END IF;

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

                -- A head starts empty. A balance arrives through the ledger.
                IF NEW.current_balance <> 0 OR NEW.last_sequence_no <> 0
                   OR NEW.last_movement_id IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a stock balance opens empty and is moved only by the ledger';
                END IF;
            END
        SQL);

        $db->unprepared('DROP TRIGGER IF EXISTS record7_stock_balances_no_drift');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_stock_balances_no_drift
            BEFORE UPDATE ON record7_stock_balances
            FOR EACH ROW
            BEGIN
                DECLARE moved_to    DECIMAL(10,3);
                DECLARE moved_seq   INT UNSIGNED;
                DECLARE moved_svc   BIGINT UNSIGNED;
                DECLARE moved_owner BIGINT UNSIGNED;
                DECLARE moved_key   CHAR(64);

                -- What this balance IS cannot change.
                IF NEW.service_id <> OLD.service_id
                    OR NEW.organisation_id <> OLD.organisation_id
                    OR NEW.owner_type <> OLD.owner_type
                    OR NOT (NEW.client_id <=> OLD.client_id)
                    OR NEW.medicine_id <> OLD.medicine_id
                    OR NEW.preparation_key <> OLD.preparation_key
                    OR NEW.unit <> OLD.unit
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'what a stock balance describes cannot be changed';
                END IF;

                -- And it may only move to a figure the ledger actually reached.
                IF NEW.current_balance <> OLD.current_balance
                    OR NEW.last_sequence_no <> OLD.last_sequence_no
                THEN
                    IF NEW.last_movement_id IS NULL THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a stock balance moves only by recording the movement that moved it';
                    END IF;

                    SELECT balance_after, sequence_no, service_id,
                           IFNULL(client_id, 0), preparation_key
                        INTO moved_to, moved_seq, moved_svc, moved_owner, moved_key
                        FROM record7_stock_movements WHERE id = NEW.last_movement_id;

                    IF moved_to IS NULL
                        OR moved_svc <> NEW.service_id
                        OR moved_owner <> IFNULL(NEW.client_id, 0)
                        OR moved_key <> NEW.preparation_key THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that movement belongs to another balance';
                    END IF;

                    IF moved_to <> NEW.current_balance OR moved_seq <> NEW.last_sequence_no THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a stock balance cannot hold a figure the ledger never reached';
                    END IF;
                END IF;
            END
        SQL);

        /* ── 5. The administration triggers, extended ───────────────────── */

        $this->administrationNoRewrite();
        $this->administrationValidateInsert();
    }

    /**
     * Reproduced verbatim from the live Section 2.5 definition, with the two
     * Section 2.7 columns appended. A recorded administration's stock
     * consequence is as permanent as the outcome it belongs to.
     */
    private function administrationNoRewrite(): void
    {
        $db = DB::connection('record7');

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
                    OR NOT (NEW.stock_movement_id <=> OLD.stock_movement_id)
                    OR NOT (NEW.stock_no_quantity_removed <=> OLD.stock_no_quantity_removed)
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'record7 administrations are a permanent record; record a correction or re-offer instead';
                END IF;
            END
        SQL);
    }

    /**
     * Reproduced verbatim from the live definition — every Section 2.3 and 2.5
     * clause carried across unchanged — with the Section 2.7 clauses appended.
     */
    private function administrationValidateInsert(): void
    {
        $db = DB::connection('record7');

        $db->unprepared('DROP TRIGGER IF EXISTS record7_administrations_validate_insert');
        $db->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_validate_insert
            BEFORE INSERT ON record7_administrations
            FOR EACH ROW
            BEGIN
                DECLARE medicine_controlled TINYINT(1);
                DECLARE register_client     BIGINT UNSIGNED;
                DECLARE register_presc      BIGINT UNSIGNED;
                DECLARE movement_client     BIGINT UNSIGNED;
                DECLARE movement_service    BIGINT UNSIGNED;
                DECLARE movement_presc      BIGINT UNSIGNED;
                DECLARE tracked             BIGINT UNSIGNED;
                DECLARE quantified          TINYINT(1);

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

                /* ── Section 2.5, carried across verbatim ──────────────── */

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

                /* ── Section 2.7, appended ─────────────────────────────── */

                -- A controlled medicine never touches the ordinary ledger.
                IF medicine_controlled = 1 AND NEW.stock_movement_id IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'a controlled medicine is accounted for in the controlled drug register';
                END IF;

                -- Whatever ordinary movement is named must be for this person,
                -- this house and this medicine.
                IF NEW.stock_movement_id IS NOT NULL THEN
                    SELECT client_id, service_id, prescription_id
                        INTO movement_client, movement_service, movement_presc
                        FROM record7_stock_movements WHERE id = NEW.stock_movement_id;

                    IF movement_client IS NULL OR movement_client <> NEW.client_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that stock movement is for another person';
                    END IF;

                    IF movement_service <> NEW.service_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that stock movement is for another house';
                    END IF;

                    IF movement_presc IS NOT NULL AND movement_presc <> NEW.prescription_id THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'that stock movement is for another medicine';
                    END IF;
                END IF;

                -- The declaration and the movement must agree.
                IF NEW.stock_no_quantity_removed IS TRUE AND NEW.stock_movement_id IS NOT NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'nothing was removed, so there is no stock movement to record';
                END IF;

                /* IS THIS PREPARATION BEING COUNTED, AND CAN A QUANTITY BE
                   KNOWN? Only then is there something to declare. An untracked
                   or unquantified medicine has no answer to give, and demanding
                   one would be demanding a guess. */
                IF NEW.corrects_administration_id IS NULL
                    AND medicine_controlled <> 1
                    AND NEW.outcome NOT IN ('given', 'self_administered') THEN

                    SELECT b.id INTO tracked
                        FROM record7_stock_balances b
                        JOIN record7_prescriptions p ON p.id = NEW.prescription_id
                        JOIN record7_medicines m ON m.id = p.medicine_id
                        WHERE b.service_id = NEW.service_id
                          AND b.owner_ref = NEW.client_id
                          AND b.preparation_key = SHA2(CONCAT_WS('|', m.id,
                                                                      IFNULL(m.form, ''),
                                                                      IFNULL(m.strength, ''),
                                                                      IFNULL(p.dose_unit, '')), 256)
                        LIMIT 1;

                    /* Quantified AND drawn from stock Record7 accounts for. A
                       medicine the person holds and manages themselves has no
                       balance to declare against, however carefully somebody
                       wrote down that they took it. */
                    SELECT CASE WHEN p.dose_unit IS NOT NULL
                                 AND p.dose_min IS NOT NULL
                                 AND p.dose_min = p.dose_max
                                 AND (p.support_type <> 'self_administered'
                                      OR p.self_administration_monitoring = 'check_and_record')
                                THEN 1 ELSE 0 END
                        INTO quantified
                        FROM record7_prescriptions p WHERE p.id = NEW.prescription_id;

                    IF tracked IS NOT NULL AND quantified = 1
                        AND NEW.stock_no_quantity_removed IS NULL THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'say whether any of this medicine was taken out of stock';
                    END IF;
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        $db = DB::connection('record7');

        foreach ([
            'record7_stock_movements_no_rewrite',
            'record7_stock_movements_no_delete',
            'record7_stock_movements_validate_insert',
            'record7_stock_balances_validate_insert',
            'record7_stock_balances_no_drift',
        ] as $trigger) {
            $db->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }

        Schema::connection('record7')->table('record7_administrations', function (Blueprint $table) {
            $table->dropForeign(['stock_movement_id']);
            $table->dropColumn(['stock_movement_id', 'stock_no_quantity_removed']);
        });

        // The Section 2.3/2.5 administration triggers are left in their
        // Section 2.7 form rather than half-restored from here. Rolling those
        // back belongs to the migration that owns them.
    }
};
