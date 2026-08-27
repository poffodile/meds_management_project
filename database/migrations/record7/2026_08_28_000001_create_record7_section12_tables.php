<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Record7 Section 1.2 — what a manager has to act on.
 *
 * WHAT WAS REUSED RATHER THAN REBUILT
 * Almost everything. Late doses, refusals, omissions and PRN follow-ups are
 * already recorded by Section 1.1 and are DERIVED here, not copied — a manager
 * looking at a late dose is looking at the same row the support worker is.
 * Staff readiness comes straight from Section 0's roles, per-user permissions
 * and competencies. Handover acknowledgement is Section 1.1's
 * record7_handover_reads. None of that is duplicated under a manager-shaped
 * name, because two tables meaning the same thing is how a system starts
 * disagreeing with itself.
 *
 * WHAT GENUINELY DID NOT EXIST
 *   1. Stock. Nothing recorded how much of anything was in a cupboard, so
 *      "medicine unavailable" could only ever be inferred from a failed dose.
 *   2. A review queue. Corrections, incidents, escalations and reopen requests
 *      had nowhere to wait for a decision.
 *   3. State ON a derived issue. A late dose is a fact; "Sarah is dealing with
 *      it" and "this was escalated at 09:40" are not facts about the dose, they
 *      are facts about the response to it, and they need somewhere to live that
 *      does not corrupt the underlying record.
 *   4. A closed round. Rounds could start and finish but not be signed off, and
 *      reopening one had no meaning.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        /* ── Stock ──────────────────────────────────────────────────────── */

        $schema->create('record7_stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            // Stock belongs to a house, not to an organisation. Two houses in
            // the same company hold their own cupboards and count them
            // separately, and a manager must never see one house's shortage
            // while standing in the other.
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->integer('quantity');
            // Below this, somebody needs to order more. Per house and per
            // medicine because a week's supply is a different number for a
            // four-times-a-day tablet than for a once-a-month one.
            $table->integer('low_threshold')->default(0);
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'medicine_id']);
            $table->index(['service_id', 'quantity']);
        });

        $schema->create('record7_stock_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('medicine_id')->constrained('record7_medicines');
            $table->enum('kind', ['count', 'discrepancy', 'delivery_overdue']);
            // A controlled-drug balance that does not match the book is the
            // single most serious thing on this list, which is why the expected
            // and the counted figure are both kept rather than just a note.
            $table->integer('expected_quantity')->nullable();
            $table->integer('counted_quantity')->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('recorded_by_user_id')->constrained('record7_users');
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('record7_users');
            $table->string('resolution_note', 500)->nullable();
            $table->timestamps();

            $table->index(['service_id', 'kind', 'resolved_at']);
        });

        /* ── The manager review queue ───────────────────────────────────── */

        $schema->create('record7_review_items', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->enum('kind', [
                'correction_request',
                'incident',
                'round_reopen_request',
                'handover_escalation',
            ]);
            $table->string('title', 190);
            $table->text('detail')->nullable();
            // What it is about, when it is about something. Deliberately a
            // loose reference rather than four nullable foreign keys: the queue
            // is a workflow, and hard-wiring it to today's four kinds would
            // mean a migration every time a fifth appears.
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreignId('raised_by_user_id')->constrained('record7_users');
            $table->timestamp('raised_at');
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'approved', 'declined'])->default('open');
            $table->foreignId('decided_by_user_id')->nullable()->constrained('record7_users');
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status', 'severity']);
            $table->index(['subject_type', 'subject_id']);
        });

        /* ── State about an issue, not state IN the record ──────────────── */

        $schema->create('record7_issue_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            // A deterministic name for a derived issue — "late_dose:412".
            // The issue itself is computed from the clinical record every time;
            // this row only carries what people have DONE about it, so that
            // taking ownership of a late dose never writes to the dose.
            $table->string('issue_key', 120);
            $table->foreignId('owner_user_id')->nullable()->constrained('record7_users');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to_user_id')->nullable()->constrained('record7_users');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('record7_users');
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'issue_key']);
            $table->index(['service_id', 'resolved_at']);
        });

        /* ── Rounds can now be signed off, and reopened ─────────────────── */

        $schema->table('record7_rounds', function (Blueprint $table) {
            // Finishing a round and signing it off are different acts by
            // different people: completed_at is the last dose recorded, this is
            // a manager saying the round is accounted for.
            $table->timestamp('closed_at')->nullable()->after('completed_at');
            $table->foreignId('closed_by_user_id')->nullable()->after('closed_at')
                ->constrained('record7_users');
            $table->timestamp('reopened_at')->nullable()->after('closed_by_user_id');
            $table->foreignId('reopened_by_user_id')->nullable()->after('reopened_at')
                ->constrained('record7_users');
        });

        $this->protectTheQueue();
    }

    /**
     * A decision, once made, is a decision.
     *
     * The queue is a workflow and its status legitimately changes — but the
     * thing that was asked for, who asked, and when, are history. Rewriting
     * those would let somebody quietly change what a manager actually approved
     * after the fact, which is the same class of problem the administrations
     * trigger exists to prevent.
     */
    private function protectTheQueue(): void
    {
        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_review_items_no_rewrite
            BEFORE UPDATE ON record7_review_items
            FOR EACH ROW
            BEGIN
                IF NEW.kind <> OLD.kind
                    OR NEW.service_id <> OLD.service_id
                    OR NEW.raised_by_user_id <> OLD.raised_by_user_id
                    OR NEW.raised_at <> OLD.raised_at
                    OR NOT (NEW.subject_id <=> OLD.subject_id)
                    OR NOT (NEW.subject_type <=> OLD.subject_type)
                THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'what a record7 review item asks, and who asked, cannot be changed';
                END IF;
            END
        SQL);
    }

    public function down(): void
    {
        $connection = DB::connection('record7');
        $connection->unprepared('DROP TRIGGER IF EXISTS record7_review_items_no_rewrite');

        $schema = Schema::connection('record7');

        $schema->table('record7_rounds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by_user_id');
            $table->dropConstrainedForeignId('reopened_by_user_id');
            $table->dropColumn(['closed_at', 'reopened_at']);
        });

        foreach ([
            'record7_issue_states',
            'record7_review_items',
            'record7_stock_events',
            'record7_stock_levels',
        ] as $table) {
            $schema->dropIfExists($table);
        }
    }
};
