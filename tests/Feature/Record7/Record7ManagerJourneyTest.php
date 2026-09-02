<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Services\Record7\ManagerActions;
use App\Services\Record7\ManagerBoard;
use Illuminate\Support\Facades\DB;

/**
 * The Team Leader / Manager journey, end to end.
 *
 * WHY THIS FILE EXISTS.
 * The manager readiness review found the same shape of defect as the staff one,
 * and worse: Manager Today and the access audit were referenced by nothing in
 * the front end, three of the four review-item kinds could not be raised by any
 * application code, and the board rendered a stale second copy of its own review
 * queue which drew Decline outside every authority check.
 *
 * None of that was visible to the existing suite, because a test that calls a
 * route supplies the address itself and a test that calls a service never reads
 * the page. So these tests assert the DOORS, the DUPLICATION and the
 * SEPARATION OF DUTIES — what a person is offered, what they are refused, and
 * that asking for something is never the same as being granted it.
 *
 * Nothing here relaxes a rule. Several of these tests exist specifically to
 * prove a rule still holds after the journey around it was completed.
 */
class Record7ManagerJourneyTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function props(string $url): array
    {
        $response = $this->get($url);
        $response->assertOk();

        return $response->viewData('page')['props'];
    }

    private function rosewood(): int
    {
        return $this->house('Rosewood House')->id;
    }

    /* ── 1. The senior destinations exist and are offered correctly ──────── */

    public function test_a_manager_is_offered_the_manager_and_audit_destinations(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertTrue($props['can']['viewManager']);
        $this->assertTrue($props['can']['viewAudit']);
        $this->assertSame(route('record7.manager'), $props['urls']['manager']);
        $this->assertSame(route('record7.audit'), $props['urls']['audit']);
    }

    /**
     * A support worker is offered neither — and, more importantly, is still
     * refused both when they type the address.
     */
    public function test_a_support_worker_is_offered_neither_and_refused_both(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertFalse($props['can']['viewManager']);
        $this->assertFalse($props['can']['viewAudit']);

        // THE MENU IS A COURTESY, NOT THE CONTROL.
        $this->get('/record7/manager')->assertStatus(403);
        $this->get('/record7/access-audit')->assertStatus(403);
    }

    /** Somebody who may read the audit but not manage gets exactly one door. */
    public function test_the_two_destinations_are_offered_independently(): void
    {
        $this->signInAt('maya.thompson', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertFalse($props['can']['viewManager'], 'Maya holds no manager dashboard.');
        $this->assertTrue($props['can']['viewAudit']);

        $this->get('/record7/manager')->assertStatus(403);
        $this->get('/record7/access-audit')->assertOk();
    }

    /* ── 2. The stale duplicate rendering is gone ────────────────────────── */

    /**
     * THE DEFECT THIS PASS EXISTS FOR.
     *
     * Four sections were rendered twice and the copies were not the same. The
     * second was the code Section 2.7 replaced: it never read item.actions and
     * drew Decline outside every authority check, so somebody holding no
     * correction authority was still offered the button.
     */
    public function test_each_manager_section_is_rendered_exactly_once(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/Manager.jsx'));

        foreach ([
            'Round oversight',
            'Staff readiness',
            'Outstanding outcomes and follow-ups',
            'Manager review queue',
        ] as $title) {
            $this->assertSame(
                1,
                substr_count($page, '<Section title="'.$title.'"'),
                "\"{$title}\" is rendered more than once. Two copies of the review queue is how "
                .'the superseded Decline button survived the Section 2.7 fix.'
            );
        }
    }

    /** And the superseded client-side authority branching is gone with it. */
    public function test_the_review_queue_no_longer_decides_authority_in_the_browser(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/Manager.jsx'));

        $this->assertStringNotContainsString(
            'can.decideCorrections',
            $page,
            'The queue is deciding authority in the browser again. What a person may do with a '
            .'request depends on its kind, its subject, its status and their authority in THIS '
            .'house — all of which the server knows and the screen does not.'
        );

        $this->assertStringContainsString('item.actions', $page);
    }

    /**
     * The behaviour underneath, said as a fact rather than as source-reading:
     * the server offers nothing to somebody who may not decide.
     */
    public function test_the_server_offers_no_action_to_a_manager_who_cannot_decide(): void
    {
        $board = app(ManagerBoard::class);

        $this->signInAt('priya.nair', 'Rosewood House');

        $offered = collect($board->openReviewItems($this->rosewood(), $this->user('priya.nair')))
            ->flatMap(fn ($item) => $item['actions'])
            ->all();

        $this->assertSame(
            [],
            $offered,
            'priya.nair holds neither incident_review nor correction_approval and must be '
            .'offered nothing for either item.'
        );
    }

    /**
     * THE BOARD ACTUALLY RENDERS.
     *
     * Every test above this line called a service or read a file. None of them
     * loaded the page, so when `reopenRequested` was added inside the rounds()
     * closure without capturing $serviceId, the whole suite stayed green and
     * the screen died with "Undefined variable $serviceId" the moment a browser
     * opened it. A board nobody loads is a board nobody is testing.
     */
    public function test_the_manager_board_loads_and_carries_the_round_state_it_needs(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $props = $this->props('/record7/manager');

        $this->assertNotEmpty($props['rounds'], 'The board rendered no rounds at all.');

        foreach ($props['rounds'] as $round) {
            $this->assertArrayHasKey(
                'reopenRequested',
                $round,
                'The reopen control has nothing to decide on.'
            );
            $this->assertIsBool($round['reopenRequested']);
        }

        // And the sections the screen is built from are all present.
        foreach (['attention', 'staff', 'outcomes', 'review', 'stock', 'handovers'] as $key) {
            $this->assertArrayHasKey($key, $props);
        }
    }

    /**
     * A closed round says a request is outstanding once one exists.
     *
     * This one needs a round on a REAL slot. The board is built by grouping
     * today's planned doses by slot, so a round invented on a slot of its own —
     * which is what the other tests use, to avoid the permanent uniqueness of
     * (service, date, slot) — has no doses and never appears on it.
     */
    public function test_a_closed_round_reports_an_outstanding_reopen_request(): void
    {
        $slot = ScheduledDose::where('service_id', $this->rosewood())
            ->whereDate('due_at', now()->toDateString())
            ->value('slot');

        if (! $slot) {
            $this->markTestSkipped('Nothing is planned in Rosewood today.');
        }

        $service = $this->house('Rosewood House');

        $round = Round::firstOrCreate(
            [
                'service_id' => $service->id,
                'round_date' => now()->toDateString(),
                'slot' => $slot,
            ],
            [
                'organisation_id' => $service->organisation_id,
                'started_by_user_id' => $this->user('olivia.carter')->id,
                'started_at' => now()->subHour(),
            ]
        );

        if (! $round->isClosed()) {
            app(ManagerActions::class)->closeRound(
                $this->user('daniel.evans'), $this->rosewood(), $round->id, request()
            );
        }

        $round = $round->fresh();

        $this->signInAt('priya.nair', 'Rosewood House');
        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $round->id,
            'reason' => 'The teatime doses were recorded after it was signed off.',
        ])->assertRedirect();

        $this->signInAt('daniel.evans', 'Rosewood House');

        $row = collect($this->props('/record7/manager')['rounds'])
            ->firstWhere('roundId', $round->id);

        $this->assertNotNull($row, 'The closed round is not on the board.');
        $this->assertTrue(
            $row['reopenRequested'],
            'The board would offer a second request against the same round.'
        );
    }

    /* ── 3. Attention rows lead somewhere, and only inside this house ────── */

    public function test_attention_destinations_never_leave_the_house(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $rosewood = $this->rosewood();
        $oakwoodClients = Client::where('service_id', $this->house('Oakwood House')->id)
            ->pluck('id')->all();

        foreach (app(ManagerBoard::class)->attention($rosewood) as $item) {
            if (($item['destination'] ?? null) === null) {
                continue;
            }

            $url = $item['destination']['url'];

            foreach ($oakwoodClients as $foreign) {
                $this->assertStringNotContainsString(
                    '/person/'.$foreign,
                    $url,
                    'A Rosewood row links to an Oakwood person.'
                );
                $this->assertStringNotContainsString(
                    '/round/person/'.$foreign,
                    $url,
                    'A Rosewood row links to an Oakwood person.'
                );
            }
        }
    }

    /** Every destination offered actually opens. */
    public function test_every_offered_destination_is_reachable(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $offered = collect(app(ManagerBoard::class)->attention($this->rosewood()))
            ->pluck('destination')->filter();

        if ($offered->isEmpty()) {
            $this->markTestSkipped('No destinations resolved in this fixture.');
        }

        foreach ($offered as $destination) {
            $response = $this->get($destination['url']);

            $this->assertContains(
                $response->status(),
                [200, 302],
                'A manager row offers '.$destination['url'].', which does not open.'
            );
        }
    }

    /**
     * A stock discrepancy names a MOVEMENT, not a balance.
     *
     * The two key families look identical — `stock_low:51` and
     * `stock_discrepancy:91` — and only one of those numbers is a balance. The
     * first version of this resolver treated them alike, which sent a manager
     * to whichever balance happened to share a number with the movement: a link
     * into the right house and the wrong medicine, which is worse than none.
     */
    public function test_a_stock_discrepancy_resolves_to_its_own_balance(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $rosewood = $this->rosewood();

        $row = collect(app(ManagerBoard::class)->attention($rosewood, null, $this->user('daniel.evans')))
            ->first(fn ($item) => str_starts_with($item['key'], 'stock_discrepancy:'));

        if (! $row || ! $row['destination']) {
            $this->markTestSkipped('No stock discrepancy with a tracked balance in this fixture.');
        }

        $movementId = (int) explode(':', $row['key'])[1];
        $movement = \App\Models\Record7\StockMovement::findOrFail($movementId);

        $balance = app(\App\Services\Record7\StockLedger::class)
            ->trackedFor($movement->client_id, $movement->medicine_id);

        $this->assertSame(
            route('record7.stock.show', ['balance' => $balance->id]),
            $row['destination']['url'],
            'The discrepancy links somewhere other than its own balance.'
        );

        // And the movement id is not being used as a balance id.
        $this->assertStringNotContainsString(
            '/stock/'.$movementId,
            $row['destination']['url'],
            'The movement id is being used as a balance id.'
        );
    }

    /** A key naming another house's record resolves to nothing at all. */
    public function test_a_foreign_record_resolves_to_no_destination(): void
    {
        $this->signInAt('daniel.evans', 'Rosewood House');

        $foreignDose = ScheduledDose::where('service_id', $this->house('Oakwood House')->id)
            ->first();

        if (! $foreignDose) {
            $this->markTestSkipped('No Oakwood dose in the fixture.');
        }

        $resolve = new \ReflectionMethod(ManagerBoard::class, 'destinationFor');
        $resolve->setAccessible(true);

        $this->assertNull(
            $resolve->invoke(app(ManagerBoard::class), $this->rosewood(), 'omitted_dose:'.$foreignDose->id),
            'A key naming another house resolved to a link out of this one.'
        );
    }

    /* ── 4. Asking for an administration correction ──────────────────────── */

    private function correctableAdministration(string $house = 'Rosewood House'): Administration
    {
        return Administration::where('service_id', $this->house($house)->id)
            ->whereNull('corrects_administration_id')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('record7_administrations as fix')
                ->whereColumn('fix.corrects_administration_id', 'record7_administrations.id'))
            ->firstOrFail();
    }

    public function test_a_worker_can_ask_for_a_recorded_administration_to_be_corrected(): void
    {
        $record = $this->correctableAdministration();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $before = $record->getAttributes();

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => $record->outcome === 'refused' ? 'given' : 'refused',
            'detail' => 'I signed the wrong line on the trolley and noticed straight after.',
        ])->assertRedirect();

        $item = ReviewItem::where('subject_type', 'administration')
            ->where('subject_id', $record->id)
            ->where('kind', 'correction_request')
            ->firstOrFail();

        $this->assertSame('open', $item->status);
        $this->assertSame('administration_outcome', $item->correction_shape);
        $this->assertNotNull($item->requested_outcome);
        $this->assertSame($this->user('olivia.carter')->id, $item->raised_by_user_id);
        $this->assertSame($this->rosewood(), $item->service_id);

        // THE ORIGINAL IS UNTOUCHED. Every column, not just the outcome.
        $this->assertSame(
            $before,
            $record->fresh()->getAttributes(),
            'Asking for a correction altered the record it was asking about.'
        );
    }

    /** And no corrected record appears merely because one was asked for. */
    public function test_requesting_a_correction_creates_no_administration(): void
    {
        $record = $this->correctableAdministration();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $before = Administration::count();

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'missed',
            'detail' => 'This was never actually given; I recorded it against the wrong person.',
        ])->assertRedirect();

        $this->assertSame(
            $before,
            Administration::count(),
            'A request wrote a clinical record. Approval is what writes one.'
        );
    }

    /**
     * ASKING IS NOT APPROVING.
     *
     * Olivia may record doses, which is what lets her raise this. It gives her
     * no authority over the decision, and the server refuses her at it.
     */
    public function test_the_requester_cannot_then_approve_their_own_request(): void
    {
        $record = $this->correctableAdministration();

        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'refused',
            'detail' => 'She turned her head away and I recorded it as given by mistake.',
        ])->assertRedirect();

        $item = ReviewItem::where('subject_id', $record->id)
            ->where('subject_type', 'administration')->firstOrFail();

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
        ])->assertStatus(403);

        $this->assertSame('open', $item->fresh()->status);
    }

    public function test_a_correction_cannot_be_requested_on_another_houses_record(): void
    {
        $foreign = $this->correctableAdministration('Oakwood House');

        $this->signInAt('olivia.carter', 'Rosewood House');

        $this->post('/record7/administration/'.$foreign->id.'/correction', [
            'requested_outcome' => 'missed',
            'detail' => 'Trying to reach into a house I am not standing in.',
        ])->assertStatus(404);

        $this->assertSame(
            0,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $foreign->id)->count()
        );
    }

    public function test_a_correction_request_needs_a_reason_and_a_different_outcome(): void
    {
        $record = $this->correctableAdministration();

        $this->signInAt('olivia.carter', 'Rosewood House');

        // No reason.
        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'missed',
            'detail' => 'wrong',
        ])->assertSessionHasErrors('detail');

        // The outcome it already has.
        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => $record->outcome,
            'detail' => 'Asking for it to say exactly what it already says.',
        ]);

        $this->assertSame(
            0,
            ReviewItem::where('subject_type', 'administration')
                ->where('subject_id', $record->id)->count()
        );
    }

    /** Somebody who does not give medicines cannot raise one either. */
    public function test_a_user_without_administration_authority_cannot_request_a_correction(): void
    {
        $record = $this->correctableAdministration();

        $this->signInAt('maya.thompson', 'Rosewood House');

        $this->post('/record7/administration/'.$record->id.'/correction', [
            'requested_outcome' => 'missed',
            'detail' => 'A reviewer trying to raise a clinical correction request.',
        ])->assertStatus(403);
    }

    /* ── 5. Asking for a round to be reopened ────────────────────────────── */

    /**
     * A round of this test's own, so the fixture's are left alone.
     *
     * The same approach Section 2.6's own tests take: `(service, date, slot)`
     * is unique forever, so a test that reused a real round would be the only
     * one able to run twice.
     */
    private function freshRound(string $house = 'Rosewood House'): Round
    {
        $service = $this->house($house);

        return Round::create([
            'organisation_id' => $service->organisation_id,
            'service_id' => $service->id,
            'round_date' => now()->toDateString(),
            'slot' => 'ManagerJourney-'.uniqid(),
            'started_by_user_id' => $this->user('olivia.carter')->id,
            'started_at' => now()->subHour(),
        ]);
    }

    private function closedRound(): Round
    {
        $round = $this->freshRound();

        app(ManagerActions::class)->closeRound(
            $this->user('daniel.evans'), $this->rosewood(), $round->id, request()
        );

        return $round->fresh();
    }

    public function test_a_manager_can_ask_for_a_closed_round_to_be_reopened(): void
    {
        $round = $this->closedRound();

        $this->signInAt('priya.nair', 'Rosewood House');

        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $round->id,
            'reason' => 'The teatime doses were recorded after it was signed off.',
        ])->assertRedirect();

        $item = ReviewItem::where('kind', 'round_reopen_request')
            ->where('subject_id', $round->id)->where('status', 'open')->firstOrFail();

        $this->assertSame('round', $item->subject_type);
        $this->assertSame($this->rosewood(), $item->service_id);

        // ASKING IS NOT REOPENING.
        $this->assertTrue(
            $round->fresh()->isClosed(),
            'Asking for a round to be reopened reopened it.'
        );
    }

    /**
     * And the person who asked cannot grant it unless they independently hold
     * the reopening authority. Priya holds the manager dashboard and not that.
     */
    public function test_asking_does_not_confer_authority_to_reopen(): void
    {
        $round = $this->closedRound();

        $this->signInAt('priya.nair', 'Rosewood House');

        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $round->id,
            'reason' => 'Asked for so the record could be completed properly.',
        ])->assertRedirect();

        $item = ReviewItem::where('kind', 'round_reopen_request')
            ->where('subject_id', $round->id)->where('status', 'open')->firstOrFail();

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
        ])->assertStatus(403);

        $this->assertSame('open', $item->fresh()->status);
        $this->assertTrue($round->fresh()->isClosed());
    }

    /** Somebody holding it approves, and the existing lifecycle carries it out. */
    public function test_an_authorised_approval_reopens_through_the_existing_lifecycle(): void
    {
        $round = $this->closedRound();

        $this->signInAt('priya.nair', 'Rosewood House');
        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $round->id,
            'reason' => 'Two doses were given before it was closed and never recorded.',
        ])->assertRedirect();

        $item = ReviewItem::where('kind', 'round_reopen_request')
            ->where('subject_id', $round->id)->where('status', 'open')->firstOrFail();

        $eventsBefore = DB::connection('record7')->table('record7_round_lifecycle_events')
            ->where('round_id', $round->id)->count();

        // Daniel holds reopen_medication_round.
        $this->signInAt('daniel.evans', 'Rosewood House');
        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'note' => 'Agreed. Reopening so the record can be completed.',
        ])->assertRedirect();

        $this->assertSame('approved', $item->fresh()->status);

        $fresh = $round->fresh();
        $this->assertNotNull($fresh->reopened_at, 'The round was not reopened.');

        // THE CLOSURE IS NOT ERASED. Section 2.6 appends; it does not rewrite.
        $this->assertNotNull(
            $fresh->closed_at,
            'Reopening destroyed the closure it was undoing.'
        );

        $this->assertGreaterThan(
            $eventsBefore,
            DB::connection('record7')->table('record7_round_lifecycle_events')
                ->where('round_id', $round->id)->count(),
            'Reopening left no lifecycle event, so it did not go through RoundLifecycle.'
        );
    }

    public function test_a_reopen_request_cannot_name_another_houses_round(): void
    {
        $foreign = $this->freshRound('Oakwood House');

        $this->signInAt('daniel.evans', 'Rosewood House');

        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $foreign->id,
            'reason' => 'Reaching into a house I am not standing in.',
        ])->assertStatus(404);

        $this->assertSame(
            0,
            ReviewItem::where('kind', 'round_reopen_request')
                ->where('subject_id', $foreign->id)->count()
        );
    }

    public function test_an_open_round_cannot_be_asked_to_reopen(): void
    {
        $open = $this->freshRound();

        $this->signInAt('daniel.evans', 'Rosewood House');

        $this->post('/record7/manager/round/reopen-request', [
            'round_id' => $open->id,
            'reason' => 'Asking to reopen something that is not closed.',
        ]);

        $this->assertSame(
            0,
            ReviewItem::where('kind', 'round_reopen_request')
                ->where('subject_id', $open->id)->where('status', 'open')->count()
        );
    }

    /** There is still no way to reopen a round without a decision behind it. */
    public function test_no_route_reopens_a_round_directly(): void
    {
        $routes = collect(app('router')->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'record7/'))
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_contains($uri, 'reopen'))
            ->values()->all();

        $this->assertSame(
            ['record7/manager/round/reopen-request'],
            $routes,
            'A route reopens a round outside the request-and-approval lifecycle.'
        );
    }
}
