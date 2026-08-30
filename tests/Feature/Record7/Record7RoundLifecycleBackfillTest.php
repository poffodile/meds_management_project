<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\Round;
use App\Models\Record7\RoundLifecycleEvent;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.6 — importing what the old mutable columns still know.
 *
 * THESE RUN THE REAL MIGRATION. The file is required and its up() is called, so
 * what is proved here is the code that will actually touch a live database —
 * not a second implementation written in a test, which would only prove the
 * test agrees with itself.
 *
 * The rule being defended: NOTHING IS INVENTED. Where the old
 * `closed_at = null` reopen destroyed a closure's time and actor, the import
 * records the reopen it can still support and says plainly that the earlier
 * detail is gone. A tidy history that is partly fiction would be worse than an
 * honest one with a hole in it.
 */
class Record7RoundLifecycleBackfillTest extends Record7TestCase
{
    /** These describe the medication day, so they run at a fixed hour in it. */
    protected bool $anchorClockToFixtureDay = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    /** The migration itself, loaded from disk. */
    private function migration(): object
    {
        return require database_path(
            'migrations/record7/2026_09_05_000003_record7_round_lifecycle_backfill.php'
        );
    }

    /**
     * A round in a pre-2.6 shape, written straight to the table.
     *
     * Deliberately NOT through the lifecycle service: the point is to recreate
     * the state the old mutable code left behind, which the new code can no
     * longer produce.
     */
    private function legacyRound(array $columns): Round
    {
        $house = $this->house('Oakwood House');

        $id = DB::connection('record7')->table('record7_rounds')->insertGetId([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => 'Legacy-'.uniqid(),
            'started_by_user_id' => $this->user('noah.williams')->id,
            'started_at' => now()->subHours(6),
            'created_at' => now(),
            'updated_at' => now(),
        ] + $columns);

        return Round::findOrFail($id);
    }

    private function eventsFor(Round $round)
    {
        return RoundLifecycleEvent::where('round_id', $round->id)->orderBy('sequence_no')->get();
    }

    /* ── A. A round that was closed and stayed closed ───────────────────── */

    public function test_a_legacy_closed_round_imports_one_event_with_the_known_facts(): void
    {
        $closedAt = Carbon::parse(now()->subHours(3)->format('Y-m-d H:i:s'));
        $manager = $this->user('daniel.evans');

        $round = $this->legacyRound([
            'closed_at' => $closedAt,
            'closed_by_user_id' => $manager->id,
        ]);

        $this->migration()->up();

        $events = $this->eventsFor($round);

        $this->assertCount(1, $events, 'One closure, one event.');

        $event = $events->first();

        $this->assertSame('closed', $event->event);
        $this->assertSame(1, $event->sequence_no);
        $this->assertSame(
            $closedAt->toDateTimeString(),
            $event->occurred_at->toDateTimeString(),
            'The known time is preserved exactly.'
        );
        $this->assertSame($manager->id, $event->actor_user_id, 'And the known actor.');
        $this->assertSame($manager->full_name, $event->actor_name_at_time);

        $this->assertTrue((bool) $event->imported, 'Marked as reconstructed.');
        $this->assertStringContainsStringIgnoringCase('reconstructed', $event->import_note);

        // Nothing invented.
        $this->assertNull($event->reason, 'The old schema held no reason, so none is written.');
        $this->assertNull($event->review_item_id);
        $this->assertNull($event->planned_doses, 'Nobody counted at the time.');
        $this->assertNull($event->accounted_doses);
        $this->assertNull($event->unrecorded_doses);
        $this->assertNull($event->unresolved_categories);
    }

    public function test_an_imported_closed_round_reads_as_closed(): void
    {
        $round = $this->legacyRound([
            'closed_at' => now()->subHours(3),
            'closed_by_user_id' => $this->user('daniel.evans')->id,
        ]);

        $this->migration()->up();

        $this->assertTrue($round->fresh()->isClosed());
        $this->assertSame('closed', $round->fresh()->status());
        $this->assertNotNull($round->fresh()->last_lifecycle_event_id, 'The head is pointed at it.');
    }

    /* ── B. A round whose closure the old reopen destroyed ──────────────── */

    /**
     * The case the owner ruling is about.
     *
     * `closed_at = null` with `reopened_at` set is the fingerprint of the old
     * behaviour: it WAS closed, and the record of that closure is gone. The
     * import records the reopen — which is still known — and refuses to invent
     * the closure that must have preceded it.
     */
    public function test_a_legacy_reopened_round_imports_only_what_is_still_known(): void
    {
        $reopenedAt = Carbon::parse(now()->subHour()->format('Y-m-d H:i:s'));
        $manager = $this->user('daniel.evans');

        $round = $this->legacyRound([
            'closed_at' => null,
            'closed_by_user_id' => null,
            'reopened_at' => $reopenedAt,
            'reopened_by_user_id' => $manager->id,
        ]);

        $this->migration()->up();

        $events = $this->eventsFor($round);

        $this->assertCount(1, $events, 'Only the reopen, because only the reopen is known.');

        $event = $events->first();

        $this->assertSame('reopened', $event->event);
        $this->assertSame(
            $reopenedAt->toDateTimeString(),
            $event->occurred_at->toDateTimeString()
        );
        $this->assertSame($manager->id, $event->actor_user_id);
        $this->assertTrue((bool) $event->imported);

        // The note has to say what is missing, in words somebody can act on.
        $this->assertStringContainsStringIgnoringCase('closure preceded', $event->import_note);
        $this->assertStringContainsStringIgnoringCase('did not retain', $event->import_note);

        $this->assertSame(
            0,
            $events->where('event', 'closed')->count(),
            'No closure is manufactured.'
        );
    }

    /** The unknown actor stays unknown rather than becoming somebody. */
    public function test_a_legacy_reopen_with_no_recorded_actor_invents_nobody(): void
    {
        $round = $this->legacyRound([
            'closed_at' => null,
            'reopened_at' => now()->subHour(),
            'reopened_by_user_id' => null,
        ]);

        $this->migration()->up();

        $event = $this->eventsFor($round)->first();

        $this->assertSame('reopened', $event->event);
        $this->assertNull($event->actor_user_id, 'Nobody is invented.');
        $this->assertNull($event->actor_name_at_time);
        $this->assertTrue((bool) $event->imported, 'Which the CHECK permits only because it is imported.');
    }

    /** Where both are known, both are imported, in order. */
    public function test_a_legacy_closed_and_reopened_round_imports_both(): void
    {
        $closedAt = Carbon::parse(now()->subHours(4)->format('Y-m-d H:i:s'));
        $reopenedAt = Carbon::parse(now()->subHours(2)->format('Y-m-d H:i:s'));

        $round = $this->legacyRound([
            'closed_at' => $closedAt,
            'closed_by_user_id' => $this->user('daniel.evans')->id,
            'reopened_at' => $reopenedAt,
            'reopened_by_user_id' => $this->user('sarah.ahmed')->id,
        ]);

        $this->migration()->up();

        $events = $this->eventsFor($round);

        $this->assertSame(['closed', 'reopened'], $events->pluck('event')->all());
        $this->assertSame($closedAt->toDateTimeString(), $events[0]->occurred_at->toDateTimeString());
        $this->assertSame($reopenedAt->toDateTimeString(), $events[1]->occurred_at->toDateTimeString());

        $this->assertFalse($round->fresh()->isClosed(), 'The newest event is the reopen.');
    }

    /* ── C. A round that was never closed ───────────────────────────────── */

    public function test_an_open_round_gets_no_fabricated_history(): void
    {
        $round = $this->legacyRound([]);

        $this->migration()->up();

        $this->assertCount(0, $this->eventsFor($round), 'Nothing happened, so nothing is written.');
        $this->assertNull($round->fresh()->last_lifecycle_event_id);
        $this->assertFalse($round->fresh()->isClosed());
    }

    /* ── D. Imported history outranks the legacy projections ────────────── */

    /**
     * Once imported, the chain decides — not the columns it came from.
     *
     * This matters most for the destroyed-closure case: the round has a
     * `reopened_at` and no `closed_at`, and somebody could later write
     * `closed_at` back by hand. The chain still says the last thing that
     * happened was a reopen.
     */
    public function test_a_stale_closed_at_cannot_override_imported_history(): void
    {
        $round = $this->legacyRound([
            'closed_at' => null,
            'reopened_at' => now()->subHour(),
            'reopened_by_user_id' => $this->user('daniel.evans')->id,
        ]);

        $this->migration()->up();

        $this->assertFalse($round->fresh()->isClosed());

        // Somebody writes the projection back by hand.
        DB::connection('record7')->table('record7_rounds')->where('id', $round->id)
            ->update(['closed_at' => now(), 'closed_by_user_id' => $this->user('daniel.evans')->id]);

        $this->assertFalse(
            $round->fresh()->isClosed(),
            'The imported chain is authoritative; the column is a projection.'
        );
    }

    public function test_clearing_a_projection_cannot_reopen_an_imported_closure(): void
    {
        $round = $this->legacyRound([
            'closed_at' => now()->subHours(3),
            'closed_by_user_id' => $this->user('daniel.evans')->id,
        ]);

        $this->migration()->up();

        DB::connection('record7')->table('record7_rounds')->where('id', $round->id)
            ->update(['closed_at' => null, 'closed_by_user_id' => null]);

        $this->assertTrue(
            $round->fresh()->isClosed(),
            'The imported closure still stands.'
        );
    }

    /* ── Re-running imports nothing twice ───────────────────────────────── */

    public function test_running_the_migration_again_imports_nothing_twice(): void
    {
        $round = $this->legacyRound([
            'closed_at' => now()->subHours(3),
            'closed_by_user_id' => $this->user('daniel.evans')->id,
        ]);

        $this->migration()->up();
        $first = $this->eventsFor($round)->count();

        $this->migration()->up();

        $this->assertSame($first, $this->eventsFor($round)->count(), 'One history, imported once.');
    }

    /** And an imported event is as permanent as a live one. */
    public function test_an_imported_event_cannot_be_rewritten_or_deleted(): void
    {
        $round = $this->legacyRound([
            'closed_at' => now()->subHours(3),
            'closed_by_user_id' => $this->user('daniel.evans')->id,
        ]);

        $this->migration()->up();

        $event = $this->eventsFor($round)->first();

        foreach ([
            'UPDATE record7_round_lifecycle_events SET import_note = "tidied" WHERE id = ?',
            'UPDATE record7_round_lifecycle_events SET imported = 0 WHERE id = ?',
            'DELETE FROM record7_round_lifecycle_events WHERE id = ?',
        ] as $sql) {
            try {
                DB::connection('record7')->statement($sql, [$event->id]);
                $this->fail('The database allowed: '.$sql);
            } catch (\Illuminate\Database\QueryException $refused) {
                $this->assertNotEmpty($refused->getMessage());
            }
        }

        $this->assertTrue((bool) $event->fresh()->imported);
    }
}
