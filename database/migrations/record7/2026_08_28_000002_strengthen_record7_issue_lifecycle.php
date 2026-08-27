<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Six things that were one thing, and an identity that was a string.
 *
 * THE SAFETY PROBLEM THIS FIXES
 * record7_issue_states had a single resolved_at, and the manager dashboard
 * filtered anything with one out of the active list. So a manager could clear a
 * time-critical omission or a controlled-drug discrepancy off their own screen
 * by pressing a button, while the dose was still unrecorded and the balance
 * still did not match. A workflow flag was hiding a live clinical condition.
 *
 * The two are now completely separate ideas:
 *
 *   ACKNOWLEDGED          somebody has seen it
 *   OWNED                 somebody is dealing with it
 *   ESCALATED             somebody senior has been told
 *   ACTION RECORDED       something was done, and what
 *   CLOSED                administratively finished, with a reason and evidence
 *   CONDITION RESOLVED    the actual dose, balance or competency is fixed
 *
 * Only the last of those is allowed to remove an issue from the list, and it is
 * never stored — it is derived from the clinical record every time. An issue
 * that is closed while its condition persists stays visible and says so.
 *
 * THE IDENTITY PROBLEM THIS FIXES
 * Identity was (service_id, issue_key) where issue_key was free text like
 * "omitted_dose:412". Nothing tied 412 to the house it was stored under, so a
 * crafted request could attach state in one house to a record belonging to
 * another. Type and source id are now explicit columns, the unique key spans
 * organisation and house, and the application resolves every source and refuses
 * one that does not belong to the house the session is in.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        $schema = Schema::connection('record7');

        $schema->table('record7_issue_states', function (Blueprint $table) {
            // ── Identity, explicitly ───────────────────────────────────────
            $table->string('issue_type', 60)->nullable()->after('issue_key');
            $table->unsignedBigInteger('source_id')->nullable()->after('issue_type');

            // ── Seen ───────────────────────────────────────────────────────
            $table->timestamp('acknowledged_at')->nullable()->after('assigned_at');
            $table->foreignId('acknowledged_by_user_id')->nullable()->after('acknowledged_at')
                ->constrained('record7_users');

            // ── Something was done, and what ───────────────────────────────
            $table->timestamp('action_recorded_at')->nullable()->after('escalated_to_user_id');
            $table->foreignId('action_recorded_by_user_id')->nullable()->after('action_recorded_at')
                ->constrained('record7_users');
            $table->string('action_note', 1000)->nullable()->after('action_recorded_by_user_id');

            // ── Administratively closed, which is NOT the same as fixed ────
            $table->timestamp('closed_at')->nullable()->after('action_note');
            $table->foreignId('closed_by_user_id')->nullable()->after('closed_at')
                ->constrained('record7_users');
            $table->string('closure_reason', 500)->nullable()->after('closed_by_user_id');
            // For a safety-critical issue, closing requires one of these.
            $table->string('evidence_reference', 190)->nullable()->after('closure_reason');
            $table->foreignId('linked_administration_id')->nullable()->after('evidence_reference')
                ->constrained('record7_administrations');
        });

        // Backfill identity from the old text key, then make it required in
        // practice by adding the scoped unique constraint.
        foreach (DB::connection('record7')->table('record7_issue_states')->get() as $row) {
            [$type, $source] = array_pad(explode(':', (string) $row->issue_key, 2), 2, null);

            DB::connection('record7')->table('record7_issue_states')
                ->where('id', $row->id)
                ->update([
                    'issue_type' => $type,
                    'source_id' => is_numeric($source) ? (int) $source : null,
                ]);
        }

        $schema->table('record7_issue_states', function (Blueprint $table) {
            // Ownership is part of identity now. Two organisations can both
            // have a dose 412 and they must never meet.
            $table->unique(
                ['organisation_id', 'service_id', 'issue_type', 'source_id'],
                'record7_issue_states_owned_identity'
            );
            $table->index(['service_id', 'issue_type']);
        });
    }

    public function down(): void
    {
        Schema::connection('record7')->table('record7_issue_states', function (Blueprint $table) {
            $table->dropUnique('record7_issue_states_owned_identity');
            $table->dropIndex(['service_id', 'issue_type']);

            $table->dropConstrainedForeignId('acknowledged_by_user_id');
            $table->dropConstrainedForeignId('action_recorded_by_user_id');
            $table->dropConstrainedForeignId('closed_by_user_id');
            $table->dropConstrainedForeignId('linked_administration_id');

            $table->dropColumn([
                'issue_type', 'source_id', 'acknowledged_at', 'action_recorded_at',
                'action_note', 'closed_at', 'closure_reason', 'evidence_reference',
            ]);
        });
    }
};
