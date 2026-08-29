<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_rewrite');

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                MODIFY outcome ENUM(
                    'given',
                    'self_administered',
                    'refused',
                    'withheld',
                    'not_available',
                    'missed',
                    'person_unavailable'
                ) NOT NULL
        SQL);

        $schema->table('record7_administrations', function (Blueprint $table) {
            $table->foreignId('reoffer_of_administration_id')->nullable()
                ->after('corrects_administration_id')
                ->constrained('record7_administrations');
            $table->string('action_taken', 500)->nullable()->after('notes');
            $table->string('immediate_action_code', 80)->nullable()->after('action_taken');
            $table->boolean('controlled_drug_no_quantity_removed')->nullable()
                ->after('immediate_action_code');
        });

        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations DROP INDEX record7_administrations_one_per_dose'
        );
        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations DROP COLUMN dose_claim'
        );
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD COLUMN dose_claim VARCHAR(96)
                    GENERATED ALWAYS AS (
                        CASE
                            WHEN scheduled_dose_id IS NULL THEN NULL
                            WHEN corrects_administration_id IS NOT NULL
                                THEN CONCAT(scheduled_dose_id, ':correction:', corrects_administration_id)
                            WHEN reoffer_of_administration_id IS NOT NULL
                                THEN CONCAT(scheduled_dose_id, ':reoffer:', reoffer_of_administration_id)
                            ELSE CONCAT(scheduled_dose_id, ':original')
                        END
                    ) STORED
        SQL);
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD UNIQUE KEY record7_administrations_one_per_dose (dose_claim)
        SQL);

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD CONSTRAINT record7_administrations_one_relationship
                CHECK (
                    NOT (
                        corrects_administration_id IS NOT NULL
                        AND reoffer_of_administration_id IS NOT NULL
                    )
                )
        SQL);

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

        DB::connection('record7')->unprepared(<<<'SQL'
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

        $schema->table('record7_prescriptions', function (Blueprint $table) {
            $table->enum('self_administration_monitoring', ['none', 'check_and_record'])
                ->nullable()
                ->after('support_type');
        });

        DB::connection('record7')->table('record7_prescriptions')
            ->where('support_type', 'self_administered')
            ->whereNull('self_administration_monitoring')
            ->update(['self_administration_monitoring' => 'check_and_record']);
    }

    public function down(): void
    {
        $schema = Schema::connection('record7');

        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_administrations_validate_insert');
        DB::connection('record7')->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_rewrite');
        DB::connection('record7')->statement('ALTER TABLE record7_administrations DROP CHECK record7_administrations_one_relationship');
        DB::connection('record7')->statement('ALTER TABLE record7_administrations DROP INDEX record7_administrations_one_per_dose');
        DB::connection('record7')->statement('ALTER TABLE record7_administrations DROP COLUMN dose_claim');

        $schema->table('record7_administrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reoffer_of_administration_id');
            $table->dropColumn([
                'action_taken',
                'immediate_action_code',
                'controlled_drug_no_quantity_removed',
            ]);
        });

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                MODIFY outcome ENUM(
                    'given',
                    'self_administered',
                    'refused',
                    'withheld',
                    'not_available',
                    'missed'
                ) NOT NULL
        SQL);

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_administrations
                ADD COLUMN dose_claim VARCHAR(64)
                    GENERATED ALWAYS AS (
                        CASE
                            WHEN scheduled_dose_id IS NULL THEN NULL
                            ELSE CONCAT(scheduled_dose_id, ':', COALESCE(corrects_administration_id, 0))
                        END
                    ) STORED
        SQL);
        DB::connection('record7')->statement(
            'ALTER TABLE record7_administrations ADD UNIQUE KEY record7_administrations_one_per_dose (dose_claim)'
        );

        $schema->table('record7_prescriptions', function (Blueprint $table) {
            $table->dropColumn('self_administration_monitoring');
        });
    }
};
