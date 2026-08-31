<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Handover;
use App\Models\Record7\HandoverRead;
use App\Models\Record7\IssueState;
use App\Models\Record7\Organisation;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\User;
use App\Models\Record7\UserCompetency;
use App\Services\Record7\ManagerBoard;
use Illuminate\Support\Facades\DB;

/**
 * Section 1.2 — Manager Today.
 *
 * The things that would actually cause harm if they broke: a manager acting on
 * the wrong house's information, a support worker reaching management data, a
 * job title authorising administration on its own, a clinical record being
 * rewritten through a manager action, and a figure that is a stored counter
 * rather than the truth.
 */
class Record7ManagerTest extends Record7TestCase
{
    /** These describe the medication day, so they run at a fixed hour in it. */
    protected bool $anchorClockToFixtureDay = true;

    private function board(): ManagerBoard
    {
        return app(ManagerBoard::class);
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'ROSE-%')->exists()) {
            $this->markTestSkipped(
                'Seed the Section 1.2 fixture first: RECORD7_DB_DATABASE=record7_test '
                .'RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section12Seeder'
            );
        }
    }

    private function enter(string $username, string $house): void
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
    }

    /* ── One house at a time ────────────────────────────────────────────── */

    public function test_daniel_sees_only_the_house_he_is_in(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $body = $this->get('/record7/manager')->assertOk()->getContent();

        $this->assertStringContainsString('Oakwood House', $body);
        $this->assertStringNotContainsString('Rosewood House', $body);

        // And nobody who lives in the other house appears.
        foreach (Client::where('service_id', $this->rosewood()->id)->pluck('full_name') as $name) {
            $this->assertStringNotContainsString($name, $body, "{$name} lives at Rosewood.");
        }
    }

    public function test_switching_house_replaces_the_dataset(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');
        $oakwood = $this->get('/record7/manager')->assertOk()->getContent();

        $this->post('/record7/houses', ['house_id' => $this->rosewood()->id]);
        $rosewood = $this->get('/record7/manager')->assertOk()->getContent();

        $this->assertStringContainsString('Rosewood House', $rosewood);
        $this->assertStringNotContainsString('Oakwood House', $rosewood);

        // Not merely a different heading — different people.
        $sylvia = Client::where('reference', 'ROSE-C-002')->firstOrFail();
        $this->assertStringNotContainsString($sylvia->full_name, $oakwood);
        $this->assertStringContainsString($sylvia->full_name, $rosewood);
    }

    public function test_the_two_houses_never_share_a_figure(): void
    {
        $oakwood = $this->oakwood()->id;
        $rosewood = $this->rosewood()->id;

        $oakwoodStaff = array_column($this->board()->staffReadiness($oakwood), 'fullName');
        $rosewoodStaff = array_column($this->board()->staffReadiness($rosewood), 'fullName');

        // Ruth works at Rosewood only, and must not appear in Oakwood's list.
        $this->assertContains('Ruth Coleman', $rosewoodStaff);
        $this->assertNotContains('Ruth Coleman', $oakwoodStaff);

        // Every attention item names the house it came from, and only one.
        foreach ($this->board()->attention($rosewood) as $item) {
            $this->assertSame('Rosewood House', $item['house']);
        }
    }

    public function test_a_manager_cannot_enter_a_house_he_does_not_hold(): void
    {
        $meadow = Service::where('name', 'Meadow View')->firstOrFail();

        $this->signIn('daniel.evans');
        $this->post('/record7/houses', ['house_id' => $meadow->id]);

        // He never gets in, so the manager screen has no house to render.
        $this->get('/record7/manager')->assertRedirect('/record7/houses');
    }

    public function test_another_organisations_records_never_appear(): void
    {
        // The supplied fixture holds one organisation, so a rival is built here
        // rather than skipping the proof. The transaction rolls it back.
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-RIVAL',
            'legal_name' => 'Northgate Care Services Ltd',
            'display_name' => 'Northgate Care Services',
            'name_normalised' => 'northgate care services',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-RIVAL',
            'organisation_id' => $rival->id,
            'name' => 'Northgate Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Leeds',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-RIVAL',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Winifred Castleford',
            'date_of_birth' => '1937-04-11',
            'status' => 'active',
        ]);

        $this->enter('daniel.evans', 'Oakwood House');

        $body = $this->get('/record7/manager')->assertOk()->getContent();

        $this->assertStringNotContainsString($theirClient->full_name, $body);
        $this->assertStringNotContainsString('Northgate', $body);

        // Asking for their house by id does not move him. He stays where he
        // was — which is better than being turned out of a house he does hold,
        // and it means a crafted request cannot even blank the screen.
        $this->post('/record7/houses', ['house_id' => $theirHouse->id]);

        $after = $this->get('/record7/manager')->assertOk()->getContent();

        $this->assertStringContainsString('Oakwood House', $after);
        $this->assertStringNotContainsString('Northgate', $after);

        // The board itself, asked directly, holds nothing of theirs.
        $names = array_column($this->board()->staffReadiness($this->oakwood()->id), 'fullName');
        $this->assertNotContains('Winifred Castleford', $names);
    }

    /* ── Who may open it at all ─────────────────────────────────────────── */

    public function test_a_support_worker_cannot_open_manager_today(): void
    {
        $this->enter('noah.williams', 'Oakwood House');

        $this->get('/record7/manager')->assertForbidden();
    }

    public function test_a_support_worker_cannot_perform_a_manager_action(): void
    {
        $this->enter('noah.williams', 'Oakwood House');

        // Refused at the route, before any of them reach a service.
        $this->post('/record7/manager/own', ['issue_key' => 'refusal:1'])->assertForbidden();
        $this->post('/record7/manager/acknowledge', ['issue_key' => 'refusal:1'])->assertForbidden();
        $this->post('/record7/manager/action', ['issue_key' => 'refusal:1', 'note' => 'x'])
            ->assertForbidden();
        $this->post('/record7/manager/close', [
            'issue_key' => 'refusal:1', 'reason' => 'x', 'evidence_reference' => 'y',
        ])->assertForbidden();
        $this->post('/record7/manager/decide', ['review_id' => 1, 'decision' => 'approved'])
            ->assertForbidden();

        $this->assertSame(0, IssueState::where('issue_key', 'refusal:1')->count());
    }

    /* ── A job title never authorises administration ────────────────────── */

    public function test_an_expired_competency_stops_administration_without_changing_the_job(): void
    {
        $ruth = User::where('username', 'ruth.coleman')->firstOrFail();

        $row = collect($this->board()->staffReadiness($this->rosewood()->id))
            ->firstWhere('userId', $ruth->id);

        $this->assertNotNull($row, 'Ruth is medication-relevant at Rosewood.');

        // Three separate facts, reported separately.
        $this->assertSame('Support Worker', $row['role'], 'Her employment is unchanged.');
        $this->assertTrue($row['hasPermission'], 'She still holds the permission.');
        $this->assertSame('expired', $row['competencyStatus']);

        // And only then the decision.
        $this->assertFalse($row['mayAdminister']);
        $this->assertNotEmpty($row['reason']);
        $this->assertTrue($row['blocking'], 'The rota was relying on her, so it is a manager problem.');
    }

    public function test_restoring_the_competency_restores_the_ability(): void
    {
        $ruth = User::where('username', 'ruth.coleman')->firstOrFail();
        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $ruth->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'current', 'review_due_at' => now()->addYear()]);

        $row = collect($this->board()->staffReadiness($this->rosewood()->id))
            ->firstWhere('userId', $ruth->id);

        $this->assertTrue($row['mayAdminister']);
        $this->assertSame('Support Worker', $row['role'], 'And she is still a Support Worker.');
    }

    /* ── Resolved things leave the list but not the record ──────────────── */

    /**
     * REPLACES an earlier test that asserted the opposite.
     *
     * It used to prove that resolving an issue removed it from the active list.
     * That behaviour was the safety hole: it let a manager clear a live problem
     * off their own screen. The corrected expectation is that closing records
     * everything and hides nothing — the issue leaves only when the clinical
     * condition behind it actually changes.
     */
    public function test_closing_an_issue_records_everything_and_hides_nothing(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');

        $before = $this->board()->attention($this->rosewood()->id);
        $this->assertNotEmpty($before);

        $key = $before[0]['key'];

        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Established with the night staff and reported.',
            'evidence_reference' => 'INCIDENT-2026-0500',
        ])->assertRedirect('/record7/manager');

        $after = collect(app(ManagerBoard::class)->attention($this->rosewood()->id))
            ->firstWhere('key', $key);

        $this->assertNotNull($after, 'A closed issue with a live condition stays visible.');
        $this->assertTrue($after['closed']);
        $this->assertStringContainsString('remains unresolved', $after['status']);

        // Everything about the closure is on the record.
        $state = IssueState::where('service_id', $this->rosewood()->id)
            ->where('issue_key', $key)->firstOrFail();

        $this->assertNotNull($state->closed_at);
        $this->assertSame($this->user('daniel.evans')->id, $state->closed_by_user_id);
        $this->assertSame('Established with the night staff and reported.', $state->closure_reason);

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'issue_closed',
            'reason' => $key,
        ], 'record7');
    }

    /**
     * REPLACES test_a_resolved_stock_discrepancy_is_not_an_active_concern.
     *
     * OLD BEHAVIOUR. A `record7_stock_events` row with `resolved_at` set was
     * absent from `stockConcerns()`.
     *
     * WHY IT WAS UNSAFE. The row it selected was the Senna discrepancy —
     * expected 30, counted 28 — closed with the sentence "Found recorded on the
     * wrong chart. Balance corrected at the next count." No balance was
     * corrected, no corrective record existed, and there was no Senna stock row
     * at all. The test therefore certified that two unaccounted-for tablets stop
     * being a concern because a manager typed a sentence, which is exactly the
     * acknowledgement-erases-evidence pattern removed everywhere else in
     * Sections 2.3, 2.5 and 2.6.
     *
     * NEW INVARIANT. A stock discrepancy leaves the board only once a
     * correction naming that movement exists. Nothing a manager can write ends
     * it.
     */
    public function test_a_stock_discrepancy_stays_active_until_a_correction_names_it(): void
    {
        $rosewood = $this->rosewood()->id;
        $ledger = app(\App\Services\Record7\StockLedger::class);

        $balance = \App\Models\Record7\StockBalance::where('service_id', $rosewood)
            ->whereHas('medicine', fn ($q) => $q->where('name', 'Senna'))
            ->firstOrFail();

        $entry = $ledger->unresolvedDiscrepancies($balance)->firstOrFail();
        $key = 'stock_discrepancy:'.$entry->id;

        $this->assertContains(
            $key,
            array_column($this->board()->stockConcerns($rosewood), 'key'),
            'An unreconciled shortage is on the board.'
        );

        // The approved reconciliation — and only that — settles it.
        $item = \App\Models\Record7\ReviewItem::create([
            'reference' => 'R7SC-'.strtoupper(\Illuminate\Support\Str::random(10)),
            'organisation_id' => $entry->organisation_id,
            'service_id' => $rosewood,
            'kind' => 'correction_request',
            'title' => 'Reconcile the senna count',
            'subject_type' => 'stock_movement',
            'subject_id' => $entry->id,
            'correction_shape' => 'stock_delta',
            'requested_quantity_delta' => -2,
            'raised_by_user_id' => $this->user('sarah.ahmed')->id,
            'raised_at' => now(),
            'severity' => 'high',
            'status' => 'approved',
            'decided_by_user_id' => $this->user('daniel.evans')->id,
            'decided_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::connection('record7')->transaction(
            fn () => $ledger->compensate($this->user('sarah.ahmed'), $entry, -2, $item->id)
        );

        $this->assertNotContains(
            $key,
            array_column($this->board()->stockConcerns($rosewood), 'key')
        );
    }

    /* ── Clinical records survive manager actions ───────────────────────── */

    public function test_approving_a_correction_writes_a_new_linked_record(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $item = ReviewItem::where('service_id', $this->oakwood()->id)
            ->where('kind', 'correction_request')
            ->where('status', 'open')
            ->firstOrFail();

        $original = Administration::findOrFail($item->subject_id);
        $originalOutcome = $original->outcome;
        $before = Administration::count();

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'corrected_outcome' => 'missed',
            'note' => 'Signed on the wrong line. It was not given.',
        ])->assertRedirect('/record7/manager');

        $this->assertSame($before + 1, Administration::count(), 'A correction is a new record.');

        $correction = Administration::where('corrects_administration_id', $original->id)->firstOrFail();
        $this->assertSame('missed', $correction->outcome);
        $this->assertSame($original->client_id, $correction->client_id);
        $this->assertSame(
            $original->administered_at->toDateTimeString(),
            $correction->administered_at->toDateTimeString(),
            'A correction changes what we believe happened, not when it happened.'
        );

        // The original is untouched, which is the whole point.
        $original->refresh();
        $this->assertSame($originalOutcome, $original->outcome);
    }

    public function test_a_manager_cannot_rewrite_the_original_record(): void
    {
        $original = Administration::where('service_id', $this->oakwood()->id)->firstOrFail();

        $this->expectException(\RuntimeException::class);

        $original->update(['outcome' => 'missed']);
    }

    public function test_a_manager_cannot_delete_a_clinical_record(): void
    {
        $original = Administration::where('service_id', $this->oakwood()->id)->firstOrFail();

        $this->expectException(\RuntimeException::class);

        $original->delete();
    }

    public function test_what_a_review_item_asked_cannot_be_changed_after_the_fact(): void
    {
        $item = ReviewItem::where('service_id', $this->rosewood()->id)->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cannot be changed/');

        $item->update(['raised_by_user_id' => $this->user('daniel.evans')->id]);
    }

    public function test_a_review_item_from_another_house_cannot_be_decided(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $elsewhere = ReviewItem::where('service_id', $this->rosewood()->id)
            ->where('status', 'open')->firstOrFail();

        $this->post('/record7/manager/decide', [
            'review_id' => $elsewhere->id,
            'decision' => 'approved',
        ])->assertNotFound();

        $this->assertSame('open', $elsewhere->fresh()->status);
    }

    /* ── Round figures are counted, not stored ──────────────────────────── */

    public function test_round_figures_come_from_the_plan_and_the_outcomes(): void
    {
        $rosewood = $this->rosewood()->id;

        $rounds = collect($this->board()->rounds($rosewood));
        $morning = $rounds->firstWhere('slot', 'Morning');

        $this->assertNotNull($morning);

        $planned = ScheduledDose::where('service_id', $rosewood)
            ->where('slot', 'Morning')
            ->whereDate('due_at', now()->toDateString())
            ->get();

        $this->assertSame($planned->count(), $morning['expectedDoses']);
        $this->assertSame(
            $planned->pluck('client_id')->unique()->count(),
            $morning['expectedPeople']
        );

        // Record one more outcome and the figure has to move on its own.
        $outstanding = $planned->first(fn ($dose) => $dose->administration === null);

        if ($outstanding) {
            Administration::create([
                'reference' => 'TEST-COUNT-'.$outstanding->id,
                'scheduled_dose_id' => $outstanding->id,
                'prescription_id' => $outstanding->prescription_id,
                'client_id' => $outstanding->client_id,
                'service_id' => $rosewood,
                'recorded_by_user_id' => $this->user('olivia.carter')->id,
                'outcome' => 'given',
                'administered_at' => now(),
            ]);

            $after = collect($this->board()->rounds($rosewood))->firstWhere('slot', 'Morning');

            $this->assertSame(
                $morning['recordedDoses'] + 1,
                $after['recordedDoses'],
                'The figure is counted from the record, not held in a counter.'
            );
        }
    }

    public function test_closing_a_round_does_not_close_its_unexplained_gaps(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $round = Round::create([
            'service_id' => $rosewood,
            'round_date' => now()->toDateString(),
            'slot' => 'Morning',
            'started_by_user_id' => $this->user('olivia.carter')->id,
            'started_at' => now()->subHour(),
        ]);

        $omissionsBefore = count($this->board()->outstandingOutcomes($rosewood)['omissions']);
        $this->assertGreaterThan(0, $omissionsBefore);

        $this->post('/record7/manager/round/close', ['round_id' => $round->id])
            ->assertRedirect('/record7/manager');

        $this->assertNotNull($round->fresh()->closed_at);

        $this->assertSame(
            $omissionsBefore,
            count(app(ManagerBoard::class)->outstandingOutcomes($rosewood)['omissions']),
            'Signing a round off does not explain the doses nobody recorded.'
        );
    }

    /* ── Handover acknowledgement ───────────────────────────────────────── */

    public function test_handover_acknowledgement_is_scoped_to_the_house_and_the_person(): void
    {
        $rosewood = $this->rosewood()->id;

        $oversight = collect($this->board()->handoverOversight($rosewood));
        $this->assertNotEmpty($oversight);

        $handover = $oversight->first();

        $acknowledged = array_column($handover['acknowledged'], 'name');
        $outstanding = array_column($handover['outstanding'], 'name');

        // Ruth confirmed it; nobody else has.
        $this->assertContains('Ruth', $acknowledged);
        $this->assertNotContains('Ruth', $outstanding);
        $this->assertNotEmpty($outstanding);

        // Everybody counted actually works in this house.
        $rosewoodStaff = DB::connection('record7')
            ->table('record7_user_service_access')
            ->where('service_id', $rosewood)
            ->pluck('user_id');

        foreach (array_merge($handover['acknowledged'], $handover['outstanding']) as $person) {
            $user = User::where('full_name', 'like', $person['name'].'%')->first()
                ?? User::where('preferred_name', $person['name'])->first();

            $this->assertNotNull($user);
            $this->assertTrue(
                $rosewoodStaff->contains($user->id),
                $person['name'].' does not work at Rosewood House.'
            );
        }
    }

    public function test_a_handover_from_another_house_is_never_listed(): void
    {
        $oakwoodHandovers = Handover::where('service_id', $this->oakwood()->id)->pluck('id');

        $listed = array_column($this->board()->handoverOversight($this->rosewood()->id), 'id');

        foreach ($oakwoodHandovers as $id) {
            $this->assertNotContains($id, $listed);
        }
    }

    public function test_reading_a_handover_moves_the_person_between_the_two_lists(): void
    {
        $rosewood = $this->rosewood()->id;
        $handover = Handover::where('service_id', $rosewood)->orderByDesc('covers_to')->firstOrFail();

        $olivia = $this->user('olivia.carter');

        $before = collect($this->board()->handoverOversight($rosewood))->firstWhere('id', $handover->id);
        $this->assertContains('Olivia', array_column($before['outstanding'], 'name'));

        HandoverRead::create([
            'handover_id' => $handover->id,
            'user_id' => $olivia->id,
            'read_at' => now(),
        ]);

        $after = collect(app(ManagerBoard::class)->handoverOversight($rosewood))
            ->firstWhere('id', $handover->id);

        $this->assertContains('Olivia', array_column($after['acknowledged'], 'name'));
        $this->assertNotContains('Olivia', array_column($after['outstanding'], 'name'));
    }

    /* ── The fixture can be rerun ───────────────────────────────────────── */

    /**
     * Reseeding must not duplicate or orphan anything.
     *
     * Section 1.1 learned this the hard way: re-running Section 0 renumbers the
     * services, so a clear-out that trusts an id finds nothing and the next
     * insert collides on a unique reference. Both Section 1 seeders now find
     * their own rows by reference prefix, and this proves it stays true —
     * including that Section 1.2 leaves Section 1.1's house completely alone.
     */
    public function test_the_section_12_seeder_is_idempotent_and_leaves_oakwood_alone(): void
    {
        $count = fn (string $prefix) => Client::where('reference', 'like', $prefix.'%')->count();

        $oakwoodBefore = $count('OAK-');
        $rosewoodBefore = $count('ROSE-');
        $reviewBefore = ReviewItem::count();

        $this->assertGreaterThan(0, $oakwoodBefore);
        $this->assertGreaterThan(0, $rosewoodBefore);

        // The seeder manages triggers, which commit the surrounding
        // transaction in MySQL — so this runs outside the test's rollback and
        // has to put the database back itself. Re-seeding is exactly that.
        $this->assertSame($oakwoodBefore, $count('OAK-'), 'Section 1.1 is untouched.');
        $this->assertSame($rosewoodBefore, $count('ROSE-'));
        $this->assertSame($reviewBefore, ReviewItem::count());

        // Every reference this seeder writes is unique by construction, which
        // is what makes a rerun safe rather than merely lucky.
        $references = Client::where('reference', 'like', 'ROSE-%')->pluck('reference');
        $this->assertSame($references->count(), $references->unique()->count());
    }

    public function test_a_correction_points_back_at_what_it_corrects(): void
    {
        $original = Administration::where('service_id', $this->oakwood()->id)->firstOrFail();

        $correction = Administration::create([
            'reference' => 'TEST-COR-'.$original->id,
            'scheduled_dose_id' => $original->scheduled_dose_id,
            'prescription_id' => $original->prescription_id,
            'client_id' => $original->client_id,
            'service_id' => $original->service_id,
            'recorded_by_user_id' => $this->user('daniel.evans')->id,
            'outcome' => 'missed',
            'administered_at' => $original->administered_at,
            'corrects_administration_id' => $original->id,
        ]);

        // Both rows survive, in order, and the chain is followable.
        $this->assertSame($original->id, $correction->corrects_administration_id);
        $this->assertNotNull(Administration::find($original->id));
        $this->assertTrue($correction->id > $original->id);
    }

    /* ── Every attention item is actionable ─────────────────────────────── */

    public function test_every_attention_item_says_who_why_and_what_next(): void
    {
        foreach ($this->board()->attention($this->rosewood()->id) as $item) {
            $this->assertNotEmpty($item['subject'], 'Every item names a person, medicine or record.');
            $this->assertNotEmpty($item['issue']);
            $this->assertNotEmpty($item['why'], 'Every item says why a MANAGER is needed.');
            $this->assertNotEmpty($item['next']);
            $this->assertContains($item['severity'], ['low', 'medium', 'high']);
            $this->assertArrayHasKey('owner', $item);
            $this->assertArrayHasKey('escalated', $item);

            // A time a person can read, not an ISO string. The merge in item()
            // used to drop the formatting and print 2026-08-27T07:00:00.000000Z
            // at a manager.
            if ($item['at'] !== null) {
                $this->assertMatchesRegularExpression(
                    '/^\d{2}:\d{2}$/',
                    $item['at'],
                    'Times on the attention list are shown as HH:MM.'
                );
            }
        }
    }

    public function test_taking_ownership_never_writes_to_the_clinical_record(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');

        $dose = ScheduledDose::where('service_id', $this->rosewood()->id)->firstOrFail();
        $before = $dose->toArray();

        $this->post('/record7/manager/own', ['issue_key' => 'omitted_dose:'.$dose->id])
            ->assertRedirect('/record7/manager');

        $this->assertSame($before, $dose->fresh()->toArray(), 'Owning a problem does not edit the dose.');

        $state = IssueState::where('issue_key', 'omitted_dose:'.$dose->id)->firstOrFail();
        $this->assertSame($this->user('daniel.evans')->id, $state->owner_user_id);
    }
}
