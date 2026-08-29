<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.3 — let a re-offer chain past its first link.
 *
 * The trigger written with the re-offer column required the refusal being
 * answered to be an ORIGINAL one:
 *
 *     AND target.reoffer_of_administration_id IS NULL
 *
 * That allows exactly one second attempt and no more. But a person can decline
 * twice. She says no at eight, no again at nine, and takes it at ten — and the
 * third attempt has to attach to the SECOND refusal, because attaching it to
 * the first would leave the second unanswered and let two workers produce two
 * competing second attempts.
 *
 * Removing the clause does not open a cycle. A re-offer may only name a row
 * that already exists, administrations are insert-only, and both link columns
 * are frozen by the no-rewrite trigger — so A pointing at B while B points at A
 * would require each to exist before the other.
 *
 * What still holds, unchanged:
 *   the target must be a refusal;
 *   it must be the same dose, person, prescription, house and organisation;
 *   nothing may be both a correction and a re-offer;
 *   one direct answer per refusal, enforced by the unique claim.
 *
 * Additive: the earlier migration is left exactly as applied, and this replaces
 * only the insert-validation trigger.
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
            END
        SQL);
    }

    public function down(): void
    {
        DB::connection('record7')->unprepared(
            'DROP TRIGGER IF EXISTS record7_administrations_validate_insert'
        );

        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_validate_insert
            BEFORE INSERT ON record7_administrations
            FOR EACH ROW
            BEGIN
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
                            AND target.reoffer_of_administration_id IS NULL
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'a re-offer must point to the same-dose refusal in the same organisation and house';
                    END IF;
                END IF;
            END
        SQL);
    }
};
