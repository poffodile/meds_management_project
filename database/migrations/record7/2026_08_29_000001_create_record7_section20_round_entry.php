<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Section 2.0 — round identity, participation, and how a person is supported.
 *
 * WHAT WAS REUSED
 * record7_rounds itself, which Sections 1.1 and 1.2 already created. It already
 * carried the house, the date, the slot, who opened it, when, and the
 * completed/closed/reopened timestamps. None of that is rebuilt.
 *
 * WHAT WAS MISSING, AND WHY EACH MATTERS
 *
 *   1. ORGANISATION ON THE ROUND ITSELF.
 *      It was reachable through the house, and "reachable through a join" is
 *      not ownership. A round is now explicitly owned by an organisation and
 *      the unique key says so, which is what makes "Oakwood's morning round can
 *      never be Rosewood's" a database fact rather than a query habit.
 *
 *   2. PARTICIPATION AS A SEPARATE RECORD.
 *      started_by_user_id said who opened it and nothing said who else worked
 *      on it. Two people on a busy morning is ordinary, and the second must
 *      JOIN — not silently become the opener, and not open a rival round. Who
 *      joined and when is its own append-only row per person.
 *
 *   3. HOW A PERSON IS SUPPORTED WITH THEIR MEDICINES.
 *      Nothing recorded whether a medicine is given by staff, given with
 *      assistance, prompted, or taken by the person themselves. Aisha Rahman
 *      manages her own inhaler; recording that as staff administration is a
 *      false record, and treating a self-administering person as "not yet done"
 *      in a round is how they end up chased for something that was never
 *      anybody else's to do. It sits on the prescription because it varies by
 *      medicine for the same person.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        /* ── 1. A round belongs to an organisation, explicitly ──────────── */

        $schema->table('record7_rounds', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->after('id')
                ->constrained('record7_organisations');
        });

        DB::connection('record7')->statement(
            'UPDATE record7_rounds r
             JOIN record7_services s ON s.id = r.service_id
             SET r.organisation_id = s.organisation_id'
        );

        // ORDER MATTERS HERE. MySQL keeps the service_id foreign key on the
        // leftmost column of the old unique index, and refuses to drop it while
        // nothing else covers that column. So the replacement index goes in
        // first, the old unique comes out second, and the owned identity goes on
        // last. Doing it in the obvious order fails with "needed in a foreign
        // key constraint".
        $schema->table('record7_rounds', function (Blueprint $table) {
            $table->index(['service_id', 'round_date'], 'record7_rounds_service_date_index');
        });

        $schema->table('record7_rounds', function (Blueprint $table) {
            $table->dropUnique('record7_rounds_service_id_round_date_slot_unique');
        });

        $schema->table('record7_rounds', function (Blueprint $table) {
            // Adding the organisation makes ownership part of identity rather
            // than something inferred through a join. The constraint is what
            // stops a duplicate under concurrency — not a check-then-insert,
            // which two simultaneous requests can both pass.
            $table->unique(
                ['organisation_id', 'service_id', 'round_date', 'slot'],
                'record7_rounds_owned_identity'
            );
        });

        /* ── 2. Who else worked on it ───────────────────────────────────── */

        $schema->create('record7_round_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('record7_rounds')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('record7_users');
            // Kept on the row as well as on the round, so a participation
            // record is meaningful on its own in an audit export.
            $table->foreignId('organisation_id')->constrained('record7_organisations');
            $table->foreignId('service_id')->constrained('record7_services');
            // The person who opened it is a participant too, marked as such.
            $table->boolean('opened_it')->default(false);
            $table->timestamp('joined_at');
            $table->timestamp('last_acted_at')->nullable();
            // Recorded at the moment of joining, because it is what was true
            // then. If their competency lapses later, the round history must
            // still show that they were entitled when they started.
            $table->string('role_at_join', 120)->nullable();
            $table->string('access_type_at_join', 40)->nullable();
            $table->timestamps();

            // One participation row per person per round. Joining twice is
            // resuming, not a second participation.
            $table->unique(['round_id', 'user_id']);
            $table->index(['round_id', 'last_acted_at']);
        });

        /* ── 3. How this person is supported with this medicine ─────────── */

        $schema->table('record7_prescriptions', function (Blueprint $table) {
            $table->enum('support_type', [
                'staff_administered',
                'assisted',
                'prompted',
                'self_administered',
            ])->default('staff_administered')->after('route');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('record7');

        $schema->dropIfExists('record7_round_participants');

        $schema->table('record7_prescriptions', function (Blueprint $table) {
            $table->dropColumn('support_type');
        });

        $schema->table('record7_rounds', function (Blueprint $table) {
            $table->dropUnique('record7_rounds_owned_identity');
            $table->dropIndex('record7_rounds_service_date_index');
            $table->dropConstrainedForeignId('organisation_id');
            $table->unique(['service_id', 'round_date', 'slot']);
        });
    }
};
