<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Organisation;
use App\Models\Record7\Round;
use App\Models\Record7\RoundParticipant;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\UserCompetency;
use App\Models\Record7\UserServiceAccess;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundQueue;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.0 — round foundation and safe entry.
 *
 * The failures worth preventing: two rounds where there should be one, a person
 * whose authority lapsed carrying on regardless, a round from one house
 * appearing in another, and anything at all being administered before Section
 * 2.2 exists to do it properly.
 */
class Record7RoundEntryTest extends Record7TestCase
{
    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    private function entry(): RoundEntry
    {
        return app(RoundEntry::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function enter(string $username, string $house): void
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
    }

    /* ── The journey ────────────────────────────────────────────────────── */

    public function test_noah_starts_the_correct_oakwood_round_and_reaches_the_workspace(): void
    {
        // He holds one house, so Section 0 opens it for him.
        $this->signIn('noah.williams');
        $this->get('/record7/houses')->assertRedirect('/record7');

        $this->post('/record7/round/start')->assertRedirect('/record7/round');

        $round = Round::where('service_id', $this->oakwood()->id)
            ->whereDate('round_date', now()->toDateString())
            ->firstOrFail();

        $this->assertSame($this->oakwood()->id, (int) $round->service_id);
        $this->assertSame(
            (int) $this->oakwood()->organisation_id,
            (int) $round->organisation_id,
            'A round is owned by an organisation explicitly, not through a join.'
        );
        $this->assertSame($this->user('noah.williams')->id, (int) $round->started_by_user_id);

        $body = $this->get('/record7/round')->assertOk()->getContent();

        $this->assertStringContainsString('Oakwood House', $body);
        $this->assertStringContainsString($round->slot, $body);
    }

    public function test_the_workspace_shows_house_round_window_start_progress_and_people(): void
    {
        $this->enter('noah.williams', 'Oakwood House');
        $this->post('/record7/round/start');

        $body = $this->get('/record7/round')->assertOk()->getContent();

        foreach (['&quot;house&quot;', '&quot;round&quot;', '&quot;window&quot;',
            '&quot;startedAt&quot;', '&quot;progress&quot;', '&quot;queue&quot;',
            '&quot;participants&quot;'] as $key) {
            $this->assertStringContainsString($key, $body, "The workspace must carry {$key}.");
        }
    }

    public function test_nothing_can_be_administered_in_section_two_zero(): void
    {
        $this->enter('noah.williams', 'Oakwood House');
        $this->post('/record7/round/start');

        $before = Administration::count();

        $this->get('/record7/round')->assertOk();

        $this->assertSame($before, Administration::count(), 'Section 2.0 records nothing.');

        // And the scaffold cannot submit anything at all. Searching for the
        // word "administer" would be crude — it appears legitimately in
        // "Staff administered", which is a support type. What actually matters
        // is that the page has no way to send anything to the server.
        $page = file_get_contents(resource_path('js/R7Pages/Round.jsx'));

        foreach (['router.post', 'router.put', 'router.patch', 'router.delete', '<form'] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $page,
                'Section 2.0 submits nothing. Recording begins at Section 2.2.'
            );
        }
    }

    /* ── One round, whoever presses first ───────────────────────────────── */

    public function test_starting_twice_resumes_the_same_round(): void
    {
        $this->enter('noah.williams', 'Oakwood House');

        $this->post('/record7/round/start');
        $first = Round::where('service_id', $this->oakwood()->id)->count();

        $this->post('/record7/round/start');

        $this->assertSame($first, Round::where('service_id', $this->oakwood()->id)->count());

        $this->assertSame(
            1,
            RoundParticipant::whereIn('round_id', Round::where('service_id', $this->oakwood()->id)->select('id'))
                ->where('user_id', $this->user('noah.williams')->id)
                ->count(),
            'Coming back is resuming, not joining a second time.'
        );

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'round_resumed',
            'user_id' => $this->user('noah.williams')->id,
        ], 'record7');
    }

    /**
     * The concurrency proof, and it is the DATABASE that provides it.
     *
     * A check-then-insert loses this race: both requests read "no round", both
     * insert, and the house gets two. Here the second insert is attempted
     * directly and must be refused by the unique constraint — not by luck, not
     * by ordering, by the schema.
     */
    public function test_the_database_itself_refuses_a_second_round(): void
    {
        $oakwood = $this->oakwood();

        Round::create([
            'organisation_id' => $oakwood->organisation_id,
            'service_id' => $oakwood->id,
            'round_date' => now()->toDateString(),
            'slot' => 'Morning',
            'started_by_user_id' => $this->user('noah.williams')->id,
            'started_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        // Straight past every application guard.
        DB::connection('record7')->table('record7_rounds')->insert([
            'organisation_id' => $oakwood->organisation_id,
            'service_id' => $oakwood->id,
            'round_date' => now()->toDateString(),
            'slot' => 'Morning',
            'started_by_user_id' => $this->user('olivia.carter')->id,
            'started_at' => now(),
        ]);
    }

    public function test_two_concurrent_starts_produce_exactly_one_round(): void
    {
        $oakwood = $this->oakwood();
        $slot = $this->entry()->currentSlot($oakwood->id);

        $noah = $this->user('noah.williams');
        $olivia = $this->user('olivia.carter');

        $first = $this->entry()->enter($noah, $oakwood->id, $slot, request());
        $second = $this->entry()->enter($olivia, $oakwood->id, $slot, request());

        $this->assertSame($first['round']->id, $second['round']->id);
        $this->assertSame('start', $first['action']);
        $this->assertSame('join', $second['action']);

        $this->assertSame(
            1,
            Round::where('service_id', $oakwood->id)
                ->whereDate('round_date', now()->toDateString())
                ->where('slot', $slot)
                ->count()
        );
    }

    public function test_a_second_worker_joins_and_does_not_replace_the_opener(): void
    {
        $oakwood = $this->oakwood();
        $slot = $this->entry()->currentSlot($oakwood->id);

        $noah = $this->user('noah.williams');
        $olivia = $this->user('olivia.carter');

        $round = $this->entry()->enter($noah, $oakwood->id, $slot, request())['round'];
        $this->entry()->enter($olivia, $oakwood->id, $slot, request());

        $round->refresh();

        $this->assertSame(
            $noah->id,
            (int) $round->started_by_user_id,
            'Joining never overwrites who opened it.'
        );

        $participants = RoundParticipant::where('round_id', $round->id)->get();

        $this->assertCount(2, $participants);
        $this->assertTrue($participants->firstWhere('user_id', $noah->id)->opened_it);
        $this->assertFalse($participants->firstWhere('user_id', $olivia->id)->opened_it);

        // Both actions are audited separately.
        $this->assertDatabaseHas('record7_access_audit_events',
            ['event_type' => 'round_created', 'user_id' => $noah->id], 'record7');
        $this->assertDatabaseHas('record7_access_audit_events',
            ['event_type' => 'round_joined', 'user_id' => $olivia->id], 'record7');
    }

    /* ── Houses and organisations stay apart ────────────────────────────── */

    public function test_an_oakwood_round_is_never_a_rosewood_round(): void
    {
        $oakwood = $this->oakwood();
        $rosewood = $this->rosewood();

        $noah = $this->user('noah.williams');
        $olivia = $this->user('olivia.carter');

        $a = $this->entry()->enter($noah, $oakwood->id, 'Morning', request())['round'];
        $b = $this->entry()->enter($olivia, $rosewood->id, 'Morning', request())['round'];

        $this->assertNotSame($a->id, $b->id, 'Same date, same label, different houses.');
        $this->assertSame($oakwood->id, (int) $a->service_id);
        $this->assertSame($rosewood->id, (int) $b->service_id);
    }

    public function test_another_organisation_cannot_reach_the_round(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-ROUND',
            'legal_name' => 'Kirkby Care Ltd',
            'display_name' => 'Kirkby Care',
            'name_normalised' => 'kirkby care',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-ROUND',
            'organisation_id' => $rival->id,
            'name' => 'Kirkby Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Hull',
            'status' => 'active',
        ]);

        $noah = $this->user('noah.williams');

        $check = app(\App\Services\Record7\RoundAuthority::class)->check($noah, $theirHouse->id);

        $this->assertFalse($check['allowed']);
        $this->assertContains($check['code'], ['wrong_organisation', 'no_house_access']);

        // And nothing was created there.
        $this->assertSame(0, Round::where('service_id', $theirHouse->id)->count());
    }

    /* ── Authority, re-checked every time ───────────────────────────────── */

    public function test_a_support_worker_without_permission_cannot_enter(): void
    {
        // Grace holds Rosewood on a temporary grant with an explicit deny.
        $this->enter('grace.taylor', 'Rosewood House');

        $this->post('/record7/round/start')->assertForbidden();
        $this->get('/record7/round')->assertForbidden();

        $this->assertSame(0, Round::where('service_id', $this->rosewood()->id)
            ->whereDate('round_date', now()->toDateString())->count());
    }

    public function test_permission_without_current_competency_is_not_enough(): void
    {
        $ruth = User::where('username', 'ruth.coleman')->first();

        if (! $ruth) {
            $this->markTestSkipped('Seed Section 1.2 for Ruth Coleman.');
        }

        $authority = app(\App\Services\Record7\RoundAuthority::class);

        // Permission granted, competency expired.
        $this->assertFalse($authority->check($ruth, $this->rosewood()->id)['allowed']);

        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $ruth->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'current', 'review_due_at' => now()->addYear()]);

        $this->assertTrue($authority->check($ruth->fresh(), $this->rosewood()->id)['allowed']);
    }

    public function test_a_suspended_account_and_suspended_house_access_are_both_blocked(): void
    {
        $authority = app(\App\Services\Record7\RoundAuthority::class);
        $noah = $this->user('noah.williams');
        $oakwood = $this->oakwood()->id;

        $this->assertTrue($authority->check($noah, $oakwood)['allowed']);

        UserServiceAccess::where('user_id', $noah->id)->where('service_id', $oakwood)
            ->update(['status' => 'suspended']);

        $this->assertFalse($authority->check($noah->fresh(), $oakwood)['allowed']);

        UserServiceAccess::where('user_id', $noah->id)->where('service_id', $oakwood)
            ->update(['status' => 'active']);

        $noah->update(['account_status' => 'suspended']);

        $this->assertFalse($authority->check($noah->fresh(), $oakwood)['allowed']);
    }

    public function test_a_manager_who_may_oversee_cannot_enter_the_round_to_administer(): void
    {
        // Maya reviews. She can see Manager-style oversight but has never been
        // authorised to give a medicine.
        $this->enter('maya.thompson', 'Oakwood House');

        $this->post('/record7/round/start')->assertForbidden();
        $this->get('/record7/round')->assertForbidden();
    }

    /**
     * The one that matters most: authority lost DURING a round.
     */
    public function test_losing_competency_blocks_further_action_without_destroying_the_round(): void
    {
        $this->enter('noah.williams', 'Oakwood House');
        $this->post('/record7/round/start')->assertRedirect('/record7/round');

        $round = Round::where('service_id', $this->oakwood()->id)->firstOrFail();
        $participants = RoundParticipant::where('round_id', $round->id)->count();

        // The competency lapses mid-shift.
        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        // The route gate refuses him now — and the round survives untouched.
        $this->get('/record7/round')->assertForbidden();

        $round->refresh();

        $this->assertNull($round->closed_at, 'Losing authority must not close the round.');
        $this->assertNotNull($round->started_at);
        $this->assertSame(
            $participants,
            RoundParticipant::where('round_id', $round->id)->count(),
            'The participation history is preserved.'
        );

        // The refusal is audited. It comes from Section 0's authorise
        // middleware as permission_denied rather than from Section 2.0 — the
        // gate that actually turned him away is the one that should record it,
        // and duplicating the event here would mean two rows for one refusal.
        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'permission_denied',
            'user_id' => $this->user('noah.williams')->id,
        ], 'record7');
    }

    /**
     * The refusals Section 2.0 records itself.
     *
     * The middleware catches "no permission". RoundAuthority catches the things
     * it cannot see: a round belonging to another house, a closed round, an
     * organisation mismatch. Those are audited as round_entry_refused.
     */
    public function test_round_entry_refused_is_audited_when_authority_refuses_beyond_permission(): void
    {
        $noah = $this->user('noah.williams');
        $authority = app(\App\Services\Record7\RoundAuthority::class);

        $rosewood = $this->rosewood()->id;

        // Noah holds Oakwood only, so Rosewood is refused on house access —
        // past the permission check, inside RoundAuthority.
        $check = $authority->check($noah, $rosewood);
        $this->assertFalse($check['allowed']);
        $this->assertSame('no_house_access', $check['code']);

        $authority->refuse($noah, $rosewood, $check, request());

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'round_entry_refused',
            'reason' => 'no_house_access',
        ], 'record7');
    }

    /* ── House switching ────────────────────────────────────────────────── */

    public function test_an_open_oakwood_round_does_not_appear_under_rosewood(): void
    {
        // Olivia holds both houses.
        $this->enter('olivia.carter', 'Oakwood House');
        $this->post('/record7/round/start')->assertRedirect('/record7/round');

        $oakwoodRound = Round::where('service_id', $this->oakwood()->id)->firstOrFail();

        // She walks into the other house.
        $this->post('/record7/houses', ['house_id' => $this->rosewood()->id]);

        $shown = app(RoundEntry::class)->openRoundFor($this->rosewood()->id);

        $this->assertTrue(
            $shown === null || $shown->id !== $oakwoodRound->id,
            'An open round in the house she left must not follow her.'
        );

        // And the Oakwood round is still there, untouched.
        $this->assertNull($oakwoodRound->fresh()->closed_at);
    }

    public function test_leaving_a_round_open_and_changing_house_is_audited(): void
    {
        $this->enter('olivia.carter', 'Oakwood House');
        $this->post('/record7/round/start');

        $this->post('/record7/houses', ['house_id' => $this->rosewood()->id]);
        $this->post('/record7/round/start');

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'round_house_context_changed',
            'user_id' => $this->user('olivia.carter')->id,
        ], 'record7');
    }

    /* ── The queue ──────────────────────────────────────────────────────── */

    public function test_the_queue_puts_late_and_time_sensitive_people_first(): void
    {
        $oakwood = $this->oakwood();
        $slot = 'Morning';

        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, $slot, request())['round'];

        $queue = app(RoundQueue::class)->forRound($round);

        $this->assertNotEmpty($queue);

        // Derive the expected banding from the records, exactly as the service
        // does, and confirm the order is non-decreasing.
        $band = function (array $person): int {
            return match (true) {
                $person['late'] && $person['timeSensitive'] => 1,
                $person['late'] => 2,
                $person['timeSensitive'] => 3,
                default => 4,
            };
        };

        $previous = 0;

        foreach ($queue as $person) {
            $this->assertGreaterThanOrEqual(
                $previous,
                $band($person),
                'A late time-sensitive person must never sort below a routine one.'
            );

            $previous = max($previous, $band($person));
        }

        // Terence's Parkinson's dose is the fixture's late time-critical case.
        $first = $queue[0];
        $this->assertTrue($first['late'] || $first['timeSensitive']);
    }

    public function test_queue_counts_come_from_the_scheduled_records(): void
    {
        $oakwood = $this->oakwood();
        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, 'Morning', request())['round'];

        foreach (app(RoundQueue::class)->forRound($round) as $person) {
            $actual = ScheduledDose::where('service_id', $oakwood->id)
                ->where('client_id', $person['clientId'])
                ->where('slot', 'Morning')
                ->whereDate('due_at', now()->toDateString())
                ->count();

            $this->assertSame($actual, $person['itemCount'], 'Counted, never stored.');
        }
    }

    public function test_the_queue_holds_nobody_from_another_house_or_another_round(): void
    {
        $oakwood = $this->oakwood();
        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, 'Morning', request())['round'];

        $rosewoodNames = Client::where('service_id', $this->rosewood()->id)->pluck('full_name')->all();

        foreach (app(RoundQueue::class)->forRound($round) as $person) {
            $client = Client::findOrFail($person['clientId']);

            $this->assertSame($oakwood->id, (int) $client->service_id);
            $this->assertNotContains($person['fullName'], $rosewoodNames);
        }
    }

    public function test_the_queue_shows_no_medicine_detail(): void
    {
        $oakwood = $this->oakwood();
        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, 'Morning', request())['round'];

        $queue = app(RoundQueue::class)->forRound($round);

        foreach ($queue as $person) {
            foreach (['medicines', 'medicine', 'dose', 'route', 'instructions'] as $forbidden) {
                $this->assertArrayNotHasKey(
                    $forbidden,
                    $person,
                    'Medicine detail belongs to Section 2.1, not the entry queue.'
                );
            }
        }
    }

    public function test_a_self_administering_person_is_shown_as_such_and_not_marked_done(): void
    {
        $oakwood = $this->oakwood();

        // Aisha's inhaler is self-administered; her epilepsy tablets are not.
        $aisha = Client::where('reference', 'OAK-C-003')->firstOrFail();

        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, 'Night', request())['round'];

        $queue = app(RoundQueue::class)->forRound($round);
        $person = collect($queue)->firstWhere('clientId', $aisha->id);

        if ($person) {
            $this->assertNotSame(
                'recorded',
                $person['progress'],
                'Nobody is silently counted as administered.'
            );
            $this->assertArrayHasKey('support', $person);
            $this->assertNotEmpty($person['support']['word']);
        }

        // And the support type is a real, distinct concept in the data.
        $this->assertSame(
            'self_administered',
            \App\Models\Record7\Prescription::where('client_id', $aisha->id)
                ->where('kind', 'prn')->firstOrFail()->support_type
        );
    }

    public function test_a_person_in_hospital_is_not_put_in_the_round(): void
    {
        $oakwood = $this->oakwood();
        $callum = Client::where('service_id', $oakwood->id)->where('status', 'in_hospital')->firstOrFail();

        $round = $this->entry()->enter($this->user('noah.williams'), $oakwood->id, 'Morning', request())['round'];

        $ids = array_column(app(RoundQueue::class)->forRound($round), 'clientId');

        $this->assertNotContains($callum->id, $ids);
    }

    /* ── Identity ───────────────────────────────────────────────────────── */

    public function test_round_identity_is_owned_by_organisation_and_house(): void
    {
        $indexes = collect(DB::connection('record7')->select('SHOW INDEXES FROM record7_rounds'))
            ->groupBy('Key_name');

        $this->assertTrue($indexes->has('record7_rounds_owned_identity'));

        $columns = $indexes->get('record7_rounds_owned_identity')
            ->sortBy('Seq_in_index')->pluck('Column_name')->all();

        $this->assertSame(
            ['organisation_id', 'service_id', 'round_date', 'slot'],
            $columns,
            'Identity must carry ownership, not just the house and the label.'
        );
    }
}
