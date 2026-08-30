<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Section 2.6, part three — importing what the old columns still know.
 *
 * NOTHING IS INVENTED. This was the whole point of the owner ruling.
 *
 * The old reopen set `closed_at = null`, which destroyed the time and actor of
 * the closure that must have preceded it. An earlier draft of this section
 * proposed manufacturing a `closed` event for those rounds so the chain would
 * look tidy. That would have written a clinical history Record7 does not know,
 * which is worse than an incomplete one.
 *
 * So: every imported event carries only values the database still holds, is
 * flagged `imported`, and says in `import_note` what it is and what was lost.
 *
 *   closed_at set, reopened_at null   -> one imported 'closed', known time/actor
 *   closed_at set AND reopened_at set -> imported 'closed' then 'reopened'
 *   closed_at NULL, reopened_at set   -> ONLY the imported 'reopened'.
 *                                        No closure is fabricated; the note
 *                                        records that its detail was lost.
 *   neither set                       -> nothing. It was never closed.
 */
return new class extends Migration
{
    protected $connection = 'record7';

    private const RECONSTRUCTED = 'Reconstructed during the round lifecycle migration '
        .'from the closed_at and closed_by_user_id columns.';

    private const CLOSURE_LOST = 'A closure preceded this reopen, but the legacy mutable '
        .'schema did not retain its time or actor, so none is recorded here.';

    public function up(): void
    {
        $db = DB::connection('record7');

        $rounds = $db->table('record7_rounds')
            ->where(function ($q) {
                $q->whereNotNull('closed_at')->orWhereNotNull('reopened_at');
            })
            ->orderBy('id')
            ->get();

        foreach ($rounds as $round) {
            // Never import twice. This runs once in the ordinary way, but a
            // second pass must not duplicate a history it already wrote — and
            // being able to say that is what makes it testable.
            $already = $db->table('record7_round_lifecycle_events')
                ->where('round_id', $round->id)->exists();

            if ($already) {
                continue;
            }

            $sequence = 0;
            $lastId = null;

            if ($round->closed_at !== null) {
                $lastId = $this->import($db, $round, 'closed', ++$sequence, [
                    'occurred_at' => $round->closed_at,
                    'actor_user_id' => $round->closed_by_user_id,
                    'import_note' => self::RECONSTRUCTED,
                ]);
            }

            if ($round->reopened_at !== null) {
                $lastId = $this->import($db, $round, 'reopened', ++$sequence, [
                    'occurred_at' => $round->reopened_at,
                    'actor_user_id' => $round->reopened_by_user_id,
                    'import_note' => $round->closed_at === null
                        ? self::CLOSURE_LOST
                        : self::RECONSTRUCTED,
                ]);
            }

            if ($lastId !== null) {
                $db->table('record7_rounds')->where('id', $round->id)
                    ->update(['last_lifecycle_event_id' => $lastId]);
            }
        }
    }

    /** One imported event, carrying only what is still known. */
    private function import($db, $round, string $event, int $sequence, array $known): int
    {
        $actorName = null;

        if ($known['actor_user_id'] !== null) {
            $actorName = $db->table('record7_users')
                ->where('id', $known['actor_user_id'])->value('full_name');
        }

        return (int) $db->table('record7_round_lifecycle_events')->insertGetId([
            'reference' => 'R7RL-'.strtoupper(Str::random(12)),
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'round_id' => $round->id,
            'event' => $event,
            'sequence_no' => $sequence,
            'occurred_at' => $known['occurred_at'],
            'actor_user_id' => $known['actor_user_id'],
            'actor_name_at_time' => $actorName,

            // Deliberately absent: an imported reopen has no reason and no
            // approval, because the legacy schema never held either. The CHECK
            // constraints permit that only when imported is true.
            'reason' => null,
            'review_item_id' => null,

            // No snapshot either. Nobody counted at the time, and inventing
            // figures would be worse than leaving them null.
            'planned_doses' => null,
            'accounted_doses' => null,
            'unrecorded_doses' => null,
            'unresolved_categories' => null,

            'imported' => true,
            'import_note' => $known['import_note'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Imported events are removed by dropping the table in the migration
        // that created it. Deleting them here would need the append-only guard
        // lifted, and a down() that fights its own protections is worse than
        // one that leaves the rollback to the owner of the table.
    }
};
