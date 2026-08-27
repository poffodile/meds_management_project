<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Record7 Section 1 — the medicines record.
 *
 * Section 0 built who may do what. Nothing existed for what they actually do,
 * so all of this is new: the people, their medicines, the plan for the day,
 * what happened to each dose, and the two things a shift hands to the next one
 * — a handover and any PRN that still needs following up.
 *
 * Every table is on the 'record7' connection. Nothing here touches the legacy
 * schema, Frontend 3 or Frontend 4.
 *
 * TERMINOLOGY. The people receiving care are CLIENTS, which is the owner's
 * company wording and already the legacy schema's (client_id appears in 37
 * tables there). Not "residents", not "service users".
 *
 * ADMINISTRATIONS ARE APPEND-ONLY, and enforced the same way Section 0's audit
 * is: database triggers, not just application code. A medicines record that can
 * be quietly edited afterwards is not a medicines record. A mistake is
 * corrected by recording a correction that refers to the original, never by
 * changing what was written.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        /* ── The people ─────────────────────────────────────────────────── */

        $schema->create('record7_clients', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            // A client belongs to one house at a time. Moving house is a real
            // event and will need its own record; for now this is the current
            // one, and it is what every authorisation check reads.
            $table->foreignId('service_id')->constrained('record7_services');
            $table->string('full_name', 255);
            $table->string('preferred_name', 120)->nullable();
            $table->date('date_of_birth');
            // "Flat 2", "Room 4", "Upstairs back". Deliberately free text —
            // this product is not only for care homes, and a settings-agnostic
            // field beats an enum that fits one kind of building.
            $table->string('room_name', 80)->nullable();
            $table->enum('status', ['active', 'on_leave', 'in_hospital', 'moved_out'])->default('active');
            // Something a support worker needs to know before they knock:
            // "prefers her medicines with breakfast, not before".
            $table->string('support_note', 500)->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status']);
        });

        /* ── Allergies ──────────────────────────────────────────────────── */

        $schema->create('record7_client_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('record7_clients')->cascadeOnDelete();
            $table->string('substance', 190);
            $table->string('reaction', 255)->nullable();
            // Not a colour and not a number. The word is what gets read aloud.
            $table->enum('severity', ['mild', 'moderate', 'severe', 'life_threatening']);
            $table->string('source', 120)->nullable();       // who said so
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });

        /* ── Medicines ──────────────────────────────────────────────────── */

        $schema->create('record7_medicines', function (Blueprint $table) {
            $table->id();
            // Room for a dm+d code later. Storing a typed name alone is how
            // medicines records end up unmatchable; this is nullable now
            // because Section 1 has no catalogue synchronisation yet, but the
            // column exists so nothing has to be migrated later to add it.
            $table->string('dmd_code', 32)->nullable()->index();
            $table->string('name', 255);
            $table->string('form', 80)->nullable();          // tablet, capsule, liquid
            $table->string('strength', 80)->nullable();      // 500mg, 10mg/5ml
            $table->boolean('is_controlled')->default(false);
            $table->timestamps();
        });

        /* ── Prescriptions ──────────────────────────────────────────────── */

        $schema->create('record7_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('client_id')->constrained('record7_clients')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->string('dose', 120);                     // "One tablet", "5ml"
            $table->string('route', 60);                     // Oral, Topical, Inhaled
            $table->string('frequency_text', 190);           // "Twice a day"
            $table->enum('kind', ['scheduled', 'prn'])->default('scheduled');
            // Some medicines do real harm if they drift — Parkinson's, insulin,
            // epilepsy. This is why the dashboard can sort by more than "how
            // late is it", and it is set on the prescription because it is a
            // property of this medicine for this person, not of the class.
            $table->boolean('is_time_critical')->default(false);
            $table->string('instructions', 500)->nullable();
            // PRN only.
            $table->unsignedSmallInteger('prn_max_per_day')->nullable();
            $table->unsignedSmallInteger('prn_min_gap_minutes')->nullable();
            $table->string('prn_indication', 190)->nullable(); // "for pain"
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->enum('status', ['active', 'suspended', 'stopped'])->default('active');
            // A change to someone's medicines is the single most common moment
            // for something to go wrong, so the dashboard surfaces it and needs
            // to know when it happened and what it was.
            $table->timestamp('changed_at')->nullable();
            $table->string('change_note', 500)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });

        /* ── The plan for the day ───────────────────────────────────────── */

        $schema->create('record7_scheduled_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('record7_prescriptions')->cascadeOnDelete();
            // Denormalised on purpose. Every authorisation check and every
            // dashboard query filters by house first, and joining three tables
            // to discover which house a dose belongs to would be both slower
            // and easier to get wrong.
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->timestamp('due_at');
            // Named so a person can say it out loud: "the lunchtime round".
            $table->string('slot', 40);
            // How long after due_at this still counts as on time. A morning
            // round has hours of grace; a Parkinson's dose has minutes.
            $table->unsignedSmallInteger('grace_minutes')->default(60);
            $table->timestamps();

            $table->index(['service_id', 'due_at']);
            $table->unique(['prescription_id', 'due_at']);
        });

        /* ── What actually happened ─────────────────────────────────────── */

        $schema->create('record7_administrations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            // Null for a PRN, which by definition was not on the plan.
            $table->foreignId('scheduled_dose_id')->nullable()
                ->constrained('record7_scheduled_doses');
            $table->foreignId('prescription_id')->constrained('record7_prescriptions');
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('recorded_by_user_id')->constrained('record7_users');
            $table->foreignId('witnessed_by_user_id')->nullable()->constrained('record7_users');
            // "given" is one outcome among several, not the default. Every
            // other outcome is a real clinical event, which is why they are
            // first-class values here rather than a note on a failed give.
            $table->enum('outcome', [
                'given',
                'self_administered',
                'refused',
                'withheld',
                'not_available',
                'missed',
            ]);
            $table->string('reason_code', 60)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('administered_at');
            // A correction points at what it corrects. The original stays.
            $table->foreignId('corrects_administration_id')->nullable()
                ->constrained('record7_administrations');
            $table->timestamps();

            $table->index(['service_id', 'administered_at']);
            $table->index(['client_id', 'administered_at']);
        });

        /* ── PRN follow-up ──────────────────────────────────────────────── */

        $schema->create('record7_prn_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administration_id')->constrained('record7_administrations')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('record7_clients');
            $table->foreignId('service_id')->constrained('record7_services');
            // Giving something "as required" and never asking whether it worked
            // is how a person stays in pain all afternoon. This is the ask-back.
            $table->timestamp('due_at');
            $table->enum('outcome', ['pending', 'effective', 'partly_effective', 'not_effective'])
                ->default('pending');
            $table->string('notes', 500)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('record7_users');
            $table->timestamps();

            $table->index(['service_id', 'outcome', 'due_at']);
        });

        /* ── Handover ───────────────────────────────────────────────────── */

        $schema->create('record7_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('written_by_user_id')->constrained('record7_users');
            $table->string('shift', 40);                     // "Night shift"
            $table->timestamp('covers_from');
            $table->timestamp('covers_to');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'covers_to']);
        });

        $schema->create('record7_handover_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('handover_id')->constrained('record7_handovers')->cascadeOnDelete();
            // Not every note is about one person — "the pharmacy delivery is
            // late" belongs to the house.
            $table->foreignId('client_id')->nullable()->constrained('record7_clients');
            $table->enum('priority', ['routine', 'important', 'urgent'])->default('routine');
            $table->string('note', 1000);
            $table->timestamps();

            $table->index('handover_id');
        });

        /* ── Rounds ─────────────────────────────────────────────────────── */

        $schema->create('record7_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('record7_services');
            $table->date('round_date');
            $table->string('slot', 40);
            $table->foreignId('started_by_user_id')->constrained('record7_users');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // One round per house per slot per day. Two people starting the
            // same round is a real thing that happens on a busy morning, and
            // it must join the existing round rather than open a second one.
            $table->unique(['service_id', 'round_date', 'slot']);
        });

        $this->protectTheRecord();
    }

    /**
     * The medicines record cannot be rewritten.
     *
     * Same approach as Section 0's audit trail: the guarantee lives in the
     * database, so it holds against a future controller, a console command, a
     * careless tinker session, or anything else that reaches this table by a
     * route the application layer does not control.
     *
     * UPDATE is not blocked outright — a correction has to be able to set
     * corrects_administration_id on the new row — but the clinical facts are
     * frozen. Changing what was given, to whom, by whom, or when is refused.
     */
    private function protectTheRecord(): void
    {
        $connection = DB::connection('record7');

        $connection->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_no_rewrite
            BEFORE UPDATE ON record7_administrations
            FOR EACH ROW
            BEGIN
                IF NEW.outcome <> OLD.outcome
                    OR NEW.client_id <> OLD.client_id
                    OR NEW.prescription_id <> OLD.prescription_id
                    OR NEW.recorded_by_user_id <> OLD.recorded_by_user_id
                    OR NEW.administered_at <> OLD.administered_at
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'record7 administrations are a permanent record; record a correction instead';
                END IF;
            END
        SQL);

        $connection->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_no_delete
            BEFORE DELETE ON record7_administrations
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'record7 administrations are a permanent record and cannot be deleted';
            END
        SQL);
    }

    public function down(): void
    {
        $connection = DB::connection('record7');

        foreach (['record7_administrations_no_rewrite', 'record7_administrations_no_delete'] as $trigger) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }

        $schema = Schema::connection('record7');

        // Children before parents.
        foreach ([
            'record7_handover_notes',
            'record7_handovers',
            'record7_prn_follow_ups',
            'record7_rounds',
            'record7_administrations',
            'record7_scheduled_doses',
            'record7_prescriptions',
            'record7_medicines',
            'record7_client_allergies',
            'record7_clients',
        ] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
