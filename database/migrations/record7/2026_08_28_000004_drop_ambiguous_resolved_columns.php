<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove the column that caused the problem.
 *
 * resolved_at meant "a manager pressed resolve", but it READ as "the problem is
 * fixed", and the dashboard treated it as the second. Leaving it in place beside
 * the new lifecycle would keep that ambiguity alive for whoever reads this table
 * next: two fields that both look like they answer "is it sorted", one of which
 * is a workflow flag and one of which does not exist here at all.
 *
 * Whether the underlying condition is resolved is derived from the clinical
 * record by IssueRegistry and is deliberately not stored. What people did about
 * it is now acknowledged / owned / escalated / action recorded / closed, each
 * with its own actor and timestamp.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    public function up(): void
    {
        Schema::connection('record7')->table('record7_issue_states', function (Blueprint $table) {
            $table->dropIndex('record7_issue_states_service_id_resolved_at_index');
            $table->dropConstrainedForeignId('resolved_by_user_id');
            $table->dropColumn('resolved_at');
        });

        Schema::connection('record7')->table('record7_issue_states', function (Blueprint $table) {
            $table->index(['service_id', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::connection('record7')->table('record7_issue_states', function (Blueprint $table) {
            $table->dropIndex(['service_id', 'closed_at']);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('record7_users');
            $table->index(['service_id', 'resolved_at']);
        });
    }
};
