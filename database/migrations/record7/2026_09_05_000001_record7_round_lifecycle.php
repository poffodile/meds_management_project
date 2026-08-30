<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.6 — the round lifecycle, as history rather than as a flag.
 *
 * WHAT WAS WRONG.
 * Reopening a round set `closed_at = null`. That erased the fact it had ever
 * been closed, when, and by whom — and `reopened_at` held only the most recent
 * reopen, so a second one overwrote the first. A round could go
 * close → reopen → close → reopen and end up claiming almost none of it
 * happened. The unique key means there is one round row per house per date per
 * slot, so there was nowhere for the earlier transitions to live.
 *
 * WHAT THIS DOES.
 * Adds an append-only chain of lifecycle events. The round row stays as the
 * identity and as a convenience projection of "most recent" values, but it is
 * no longer the source of truth for what happened. Nothing is dropped.
 *
 * `completed` is deliberately NOT an event. Completeness is a fact about the
 * doses — true or false at any moment, and able to go from true back to false
 * if a dose is added — so freezing it as an event would record a claim the
 * records could later contradict. It is derived, every time it is asked.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->create('record7_round_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();

            // Ownership explicit rather than inferred through the round, so an
            // event from another company cannot be read as this one's history.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            $table->foreignId('round_id')->constrained('record7_rounds');

            $table->enum('event', ['closed', 'reopened']);

            // Position in this round's chain. The unique key below is what makes
            // two managers unable to write the same step twice.
            $table->unsignedInteger('sequence_no');

            $table->timestamp('occurred_at');

            // Nullable ONLY for imported events, where the legacy schema had
            // already lost the actor. The insert trigger requires it on
            // anything that is not imported.
            $table->foreignId('actor_user_id')->nullable()->constrained('record7_users');
            $table->string('actor_name_at_time', 255)->nullable();
            $table->string('actor_role_at_time', 120)->nullable();

            // The approval that authorised a reopen. UNIQUE: one approval
            // authorises exactly one reopen, so a replayed approval loses here
            // rather than producing a second transition.
            $table->foreignId('review_item_id')->nullable()->unique()
                ->constrained('record7_review_items');

            $table->string('reason', 500)->nullable();

            /* ── Close-time evidence, not authority ──────────────────────── */

            // What the manager was looking at when they signed. The refusal,
            // the welfare check and the controlled drug register remain
            // authoritative; these answer "what did this person see".
            $table->unsignedSmallInteger('planned_doses')->nullable();
            $table->unsignedSmallInteger('accounted_doses')->nullable();
            $table->unsignedSmallInteger('unrecorded_doses')->nullable();

            // Category NAMES only. Never copies of clinical records.
            $table->json('unresolved_categories')->nullable();

            /* ── Honest import metadata ──────────────────────────────────── */

            // Reconstructed during this migration rather than recorded at the
            // time. Nothing is invented: where the legacy schema lost a value,
            // it stays null and the note says so.
            $table->boolean('imported')->default(false);
            $table->string('import_note', 500)->nullable();

            $table->timestamps();

            $table->unique(['round_id', 'sequence_no']);
            $table->index(['service_id', 'occurred_at']);
        });

        /* ── The projection head ────────────────────────────────────────── */

        Schema::connection('record7')->table('record7_rounds', function (Blueprint $table) {
            // A cache of "which event is newest", rebuildable from the chain.
            // It never decides anything on its own.
            $table->foreignId('last_lifecycle_event_id')->nullable()->after('reopened_by_user_id')
                ->constrained('record7_round_lifecycle_events');
        });

        /* ── A live transition must be complete ─────────────────────────── */

        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_round_lifecycle_events
                ADD CONSTRAINT record7_round_lifecycle_live_events_are_complete
                CHECK (
                    imported = 1
                    OR (actor_user_id IS NOT NULL AND actor_name_at_time IS NOT NULL)
                )
        SQL);

        // A reopen has to say why, and name the approval that authorised it.
        // An imported one cannot, because the legacy schema never held either.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_round_lifecycle_events
                ADD CONSTRAINT record7_round_lifecycle_reopen_is_authorised
                CHECK (
                    imported = 1
                    OR event <> 'reopened'
                    OR (reason IS NOT NULL AND review_item_id IS NOT NULL)
                )
        SQL);

        // The snapshot has to add up, or it is not evidence of anything.
        DB::connection('record7')->statement(<<<'SQL'
            ALTER TABLE record7_round_lifecycle_events
                ADD CONSTRAINT record7_round_lifecycle_counts_agree
                CHECK (
                    planned_doses IS NULL
                    OR (accounted_doses IS NOT NULL
                        AND unrecorded_doses IS NOT NULL
                        AND planned_doses = accounted_doses + unrecorded_doses)
                )
        SQL);
    }

    public function down(): void
    {
        Schema::connection('record7')->table('record7_rounds', function (Blueprint $table) {
            $table->dropForeign(['last_lifecycle_event_id']);
            $table->dropColumn('last_lifecycle_event_id');
        });

        Schema::connection('record7')->dropIfExists('record7_round_lifecycle_events');
    }
};
