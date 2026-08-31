<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\IssueState;
use App\Models\Record7\Organisation;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockLevel;
use App\Models\Record7\User;
use App\Models\Record7\UserCompetency;
use App\Models\Record7\UserServiceAccess;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\ManagerBoard;
use Illuminate\Support\Facades\DB;

/**
 * The lifecycle of an issue, and the one thing it must never be able to do.
 *
 * THE FAILURE THESE EXIST TO PREVENT
 * A manager pressing a button and clearing a live clinical problem off their
 * own screen. Every test below attacks that from a different angle: through the
 * service, through a direct HTTP post, through a crafted key belonging to
 * another house, and through closing something that is not fixed.
 */
class Record7IssueLifecycleTest extends Record7TestCase
{
    /** These describe the medication day, so they run at a fixed hour in it. */
    protected bool $anchorClockToFixtureDay = true;

    private function board(): ManagerBoard
    {
        return app(ManagerBoard::class);
    }

    private function registry(): IssueRegistry
    {
        return app(IssueRegistry::class);
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
                'Seed Section 1.2 first: RECORD7_ALLOW_FIXTURE_SEED=true '
                .'php artisan db:seed --class=Record7Section12Seeder'
            );
        }
    }

    private function enter(string $username, string $house): void
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
    }

    private function keys(int $serviceId): array
    {
        return array_column($this->board()->attention($serviceId), 'key');
    }

    /* ── 1. Closing cannot hide a live condition ────────────────────────── */

    /**
     * The headline case. An unrecorded time-critical dose, closed by a manager
     * with evidence, stays on the list and says why.
     */
    public function test_closing_an_omitted_dose_does_not_hide_it(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $key = collect($this->board()->attention($rosewood))
            ->firstWhere('kind', 'time_critical_omission')['key'] ?? null;

        $this->assertNotNull($key, 'The fixture guarantees an unexplained time-critical omission.');

        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Spoke to the night staff. Believed given but not recorded.',
            'evidence_reference' => 'INCIDENT-2026-0431',
        ])->assertRedirect('/record7/manager');

        $after = collect(app(ManagerBoard::class)->attention($rosewood))->firstWhere('key', $key);

        $this->assertNotNull(
            $after,
            'A closed issue whose dose is STILL unrecorded must remain on the manager list. '
            .'Hiding it is exactly the failure this lifecycle exists to prevent.'
        );

        $this->assertTrue($after['closed']);
        $this->assertTrue($after['conditionActive']);
        $this->assertSame('Action recorded — underlying issue remains unresolved', $after['status']);
    }

    public function test_the_dose_leaving_the_list_requires_the_dose_to_be_recorded(): void
    {
        $rosewood = $this->rosewood()->id;

        $dose = ScheduledDose::with('administration')
            ->where('service_id', $rosewood)
            ->get()
            ->first(fn ($d) => $d->isLate());

        $this->assertNotNull($dose);
        $key = 'omitted_dose:'.$dose->id;

        $this->assertTrue($this->registry()->conditionActive($key, $rosewood));

        // Record the outcome — the actual fix.
        Administration::create([
            'reference' => 'TEST-FIX-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $rosewood,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'given',
            'administered_at' => now(),
        ]);

        $this->assertFalse(
            app(IssueRegistry::class)->conditionActive($key, $rosewood),
            'Recording the dose is what resolves it — nothing else.'
        );

        $this->assertNotContains($key, $this->keys($rosewood));
    }

    /**
     * REPLACES test_closing_a_controlled_drug_discrepancy_needs_evidence.
     *
     * OLD BEHAVIOUR. Closing a `stock_event` of kind `discrepancy` whose
     * medicine was controlled, with no evidence and no linked record, was
     * refused and left `resolved_at` null.
     *
     * WHY IT IS OBSOLETE. Section 2.7 forbids a controlled medicine from having
     * an ordinary stock record at all — the insert trigger refuses one before
     * any other check — so the row this test selected has ceased to exist. The
     * rule it protected is not lost: it is now carried by Section 2.5's derived
     * condition, which is stronger, because a controlled-drug discrepancy
     * cannot be closed WITH evidence either. Only a correction in the register
     * ends it.
     *
     * NEW INVARIANT, in two parts. An ordinary medicine's discrepancy still
     * needs evidence before the board item can be closed, and closing it still
     * does not resolve it; and a controlled medicine cannot reach this table.
     */
    public function test_closing_an_ordinary_stock_discrepancy_needs_evidence_and_still_does_not_resolve_it(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $balance = \App\Models\Record7\StockBalance::where('service_id', $rosewood)
            ->whereHas('medicine', fn ($q) => $q->where('name', 'Senna'))
            ->firstOrFail();

        $entry = app(\App\Services\Record7\StockLedger::class)
            ->unresolvedDiscrepancies($balance)->firstOrFail();

        $key = 'stock_discrepancy:'.$entry->id;

        // The fixture has already closed this one once, with evidence, and
        // deliberately: it is the case where a manager has acted and the
        // shortage is still there. So the assertion is that a NEW closure
        // without evidence does not happen, not that none has ever happened.
        $before = IssueState::where('issue_key', $key)->whereNotNull('closed_at')
            ->value('closed_at');

        // No evidence, no linked record: refused.
        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Dealt with it.',
        ])->assertStatus(500);

        $this->assertEquals(
            $before,
            IssueState::where('issue_key', $key)->whereNotNull('closed_at')->value('closed_at'),
            'A closure with no evidence changed nothing.'
        );

        // With evidence it closes — and the disagreement is still there.
        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Recounted and reported.',
            'evidence_reference' => 'INCIDENT-2026-0912',
        ])->assertRedirect('/record7/manager');

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive($key, $rosewood),
            'Closing the paperwork does not put the tablets back.'
        );
    }

    public function test_a_controlled_medicine_cannot_have_an_ordinary_stock_record(): void
    {
        $oxycodone = \App\Models\Record7\Medicine::where('name', 'Oxycodone')->firstOrFail();

        $this->assertSame(
            0,
            \App\Models\Record7\StockBalance::where('medicine_id', $oxycodone->id)->count(),
            'Section 2.5 is the only authority for a controlled balance.'
        );

        $this->assertSame(
            0,
            \App\Models\Record7\StockMovement::where('medicine_id', $oxycodone->id)->count()
        );
    }

    public function test_a_refusal_stays_until_it_is_re_offered_and_accepted(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $refusal = Administration::where('service_id', $rosewood)
            ->where('outcome', 'refused')->firstOrFail();

        $key = 'refusal:'.$refusal->id;

        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Spoke to him, he still says no.',
            'evidence_reference' => 'CARE-PLAN-REVIEW-88',
        ])->assertRedirect('/record7/manager');

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive($key, $rosewood),
            'Closing the paperwork does not mean he took it.'
        );

        // SECTION 2.3 CORRECTION. This used to be closed by any later `given`
        // for the same PRESCRIPTION — so tonight's dose closed this morning's
        // refusal, and an unrelated obligation silently answered for one nobody
        // had gone back to. It now takes an accepted re-offer of THIS dose.
        //
        // A free-floating administration no longer resolves it, however
        // plausible it looks.
        Administration::create([
            'reference' => 'TEST-UNRELATED-'.$refusal->id,
            'scheduled_dose_id' => null,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $rosewood,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'given',
            'administered_at' => $refusal->administered_at->copy()->addMinutes(30),
        ]);

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive($key, $rosewood),
            'A later dose of the same medicine is not an answer to THIS refusal.'
        );

        // The real thing: an accepted re-offer, linked to the refusal itself.
        Administration::create([
            'reference' => 'TEST-REOFFER-'.$refusal->id,
            'scheduled_dose_id' => $refusal->scheduled_dose_id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $rosewood,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => $refusal->administered_at->copy()->addMinutes(45),
        ]);

        $this->assertFalse(
            app(IssueRegistry::class)->conditionActive($key, $rosewood),
            'He was offered it again and took it. That closes it.'
        );
    }

    public function test_a_prn_follow_up_stays_until_the_answer_is_recorded(): void
    {
        $rosewood = $this->rosewood()->id;

        $followUp = \App\Models\Record7\PrnFollowUp::where('service_id', $rosewood)
            ->where('outcome', 'pending')->firstOrFail();

        $key = 'prn_follow_up:'.$followUp->id;

        $this->assertTrue($this->registry()->conditionActive($key, $rosewood));

        $followUp->update(['outcome' => 'effective', 'completed_at' => now()]);

        $this->assertFalse(app(IssueRegistry::class)->conditionActive($key, $rosewood));
    }

    /**
     * REPLACES test_low_stock_stays_until_the_cupboard_is_restocked.
     *
     * OLD BEHAVIOUR. `$level->update(['quantity' => 500])` made `stock_low`
     * inactive — a balance written directly, in one statement, by a test.
     *
     * WHY IT IS UNSAFE. It is the exact act Section 2.7 exists to make
     * impossible. The balance is derived from an append-only ledger, and a
     * figure that can be typed over is a figure nobody can rely on. A test that
     * demonstrates the write also licenses it.
     *
     * NEW INVARIANT. The condition clears only when stock genuinely arrives —
     * a receipt, through the ledger, under the lock — and a direct UPDATE on
     * the head is refused by the database.
     */
    public function test_low_stock_stays_until_stock_is_actually_received(): void
    {
        $rosewood = $this->rosewood()->id;
        $ledger = app(\App\Services\Record7\StockLedger::class);

        $balance = \App\Models\Record7\StockBalance::where('service_id', $rosewood)
            ->get()->first(fn ($b) => $b->isLow() || $b->isOut());

        if (! $balance) {
            // Rosewood's balances are healthy in the fixture; Oakwood carries
            // the low and out cases. Either is a fair subject for this rule.
            $balance = \App\Models\Record7\StockBalance::all()
                ->first(fn ($b) => $b->isLow() || $b->isOut());
        }

        $this->assertNotNull($balance);
        $key = ($balance->isOut() ? 'stock_out:' : 'stock_low:').$balance->id;
        $service = $balance->service_id;

        $this->assertTrue(app(IssueRegistry::class)->conditionActive($key, $service));

        // The old shortcut, now refused outright.
        try {
            \Illuminate\Support\Facades\DB::connection('record7')
                ->table('record7_stock_balances')
                ->where('id', $balance->id)->update(['current_balance' => 500]);
            $this->fail('A balance was written directly.');
        } catch (\Illuminate\Database\QueryException $refused) {
            $this->assertNotEmpty($refused->getMessage());
        }

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive($key, $service),
            'Nothing arrived, so nothing changed.'
        );

        // What actually clears it: stock, recorded through the ledger.
        \Illuminate\Support\Facades\DB::connection('record7')->transaction(function () use ($ledger, $balance) {
            $locked = $ledger->lockExisting($balance);

            $ledger->record(
                balance: $locked,
                snapshot: $ledger->snapshot($balance->medicine, $balance->unit),
                action: 'receipt',
                quantities: ['received' => 500],
                user: $this->user('sarah.ahmed'),
                client: $balance->client,
                house: \App\Models\Record7\Service::findOrFail($balance->service_id),
            );
        });

        $this->assertFalse(app(IssueRegistry::class)->conditionActive($key, $service));
    }

    /**
     * The other half of the same rule: where nobody has said what low means,
     * `stock_low` is UNAVAILABLE rather than false. A blank must never render
     * as healthy, and nothing invents a level.
     */
    public function test_low_stock_is_unavailable_where_no_threshold_is_recorded(): void
    {
        $balance = \App\Models\Record7\StockBalance::with('threshold')->get()
            ->first(fn ($b) => ! $b->hasThreshold() && ! $b->isOut());

        $this->assertNotNull($balance, 'The fixture deliberately leaves one without a level.');

        $ledger = app(\App\Services\Record7\StockLedger::class);

        // Take it down to almost nothing. With no rule recorded, nothing can
        // call that low.
        \Illuminate\Support\Facades\DB::connection('record7')->transaction(function () use ($ledger, $balance) {
            $locked = $ledger->lockExisting($balance);

            $ledger->record(
                balance: $locked,
                snapshot: $ledger->snapshot($balance->medicine, $balance->unit),
                action: 'waste',
                quantities: ['wasted' => (float) $balance->current_balance - 1],
                user: $this->user('sarah.ahmed'),
                client: $balance->client,
                house: \App\Models\Record7\Service::findOrFail($balance->service_id),
            );
        });

        $fresh = $balance->fresh();

        $this->assertSame('1.000', (string) $fresh->current_balance);
        $this->assertFalse($fresh->hasThreshold());
        $this->assertFalse($fresh->isLow(), 'No rule exists, so nothing is low.');
        $this->assertFalse(
            app(IssueRegistry::class)->conditionActive('stock_low:'.$fresh->id, $fresh->service_id)
        );
    }

    public function test_an_incomplete_record_stays_until_it_is_explained(): void
    {
        $rosewood = $this->rosewood()->id;

        $administration = Administration::create([
            'reference' => 'TEST-INCOMPLETE-1',
            'scheduled_dose_id' => null,
            'prescription_id' => \App\Models\Record7\Prescription::whereIn(
                'client_id',
                Client::where('service_id', $rosewood)->select('id')
            )->firstOrFail()->id,
            'client_id' => Client::where('service_id', $rosewood)->firstOrFail()->id,
            'service_id' => $rosewood,
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
            'outcome' => 'withheld',
            'reason_code' => null,
            'notes' => null,
            'administered_at' => now()->subMinutes(30),
        ]);

        $key = 'incomplete_record:'.$administration->id;

        $this->assertTrue($this->registry()->conditionActive($key, $rosewood));
        $this->assertContains($key, $this->keys($rosewood));

        // A correction explaining it is what closes the gap.
        Administration::create([
            'reference' => 'TEST-INCOMPLETE-COR',
            'scheduled_dose_id' => null,
            'prescription_id' => $administration->prescription_id,
            'client_id' => $administration->client_id,
            'service_id' => $rosewood,
            'recorded_by_user_id' => $this->user('daniel.evans')->id,
            'outcome' => 'withheld',
            'reason_code' => 'clinical_decision',
            'notes' => 'Withheld on the nurse instruction. Recorded late.',
            'administered_at' => $administration->administered_at,
            'corrects_administration_id' => $administration->id,
        ]);

        $this->assertFalse(app(IssueRegistry::class)->conditionActive($key, $rosewood));
    }

    /**
     * The blunt version: post straight at the endpoint and try to make it go
     * away. It must not.
     */
    public function test_posting_close_directly_cannot_hide_an_active_omission(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $before = $this->keys($rosewood);
        $omissions = array_values(array_filter($before, fn ($k) => str_starts_with($k, 'omitted_dose:')
            || str_starts_with($k, 'time_critical_omission:')));

        $this->assertNotEmpty($omissions);

        foreach ($omissions as $key) {
            $this->post('/record7/manager/close', [
                'issue_key' => $key,
                'reason' => 'Closing it.',
                'evidence_reference' => 'REF-1',
            ]);
        }

        $after = $this->keys($rosewood);

        foreach ($omissions as $key) {
            $this->assertContains($key, $after, "{$key} was hidden by a direct post.");
        }
    }

    /* ── Every closure is attributable ──────────────────────────────────── */

    public function test_closing_records_a_reason_an_actor_a_time_and_an_audit_event(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;

        $key = $this->keys($rosewood)[0];

        $this->post('/record7/manager/close', [
            'issue_key' => $key,
            'reason' => 'Investigated and reported to the nurse.',
            'evidence_reference' => 'INCIDENT-2026-0999',
        ])->assertRedirect('/record7/manager');

        $state = IssueState::where('service_id', $rosewood)->where('issue_key', $key)->firstOrFail();

        $this->assertNotNull($state->closed_at);
        $this->assertSame($this->user('daniel.evans')->id, $state->closed_by_user_id);
        $this->assertSame('Investigated and reported to the nurse.', $state->closure_reason);
        $this->assertSame('INCIDENT-2026-0999', $state->evidence_reference);

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'issue_closed',
            'reason' => $key,
            'user_id' => $this->user('daniel.evans')->id,
        ], 'record7');
    }

    public function test_recording_an_action_demands_words(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');

        $this->post('/record7/manager/action', [
            'issue_key' => $this->keys($this->rosewood()->id)[0],
            'note' => '',
        ])->assertSessionHasErrors('note');
    }

    public function test_the_five_response_states_are_separate(): void
    {
        $this->enter('daniel.evans', 'Rosewood House');
        $rosewood = $this->rosewood()->id;
        $key = $this->keys($rosewood)[0];

        $this->post('/record7/manager/acknowledge', ['issue_key' => $key]);
        $item = collect(app(ManagerBoard::class)->attention($rosewood))->firstWhere('key', $key);
        $this->assertTrue($item['acknowledged']);
        $this->assertNull($item['owner']);
        $this->assertFalse($item['actionRecorded']);
        $this->assertFalse($item['closed']);

        $this->post('/record7/manager/own', ['issue_key' => $key]);
        $item = collect(app(ManagerBoard::class)->attention($rosewood))->firstWhere('key', $key);
        $this->assertNotNull($item['owner']);
        $this->assertFalse($item['actionRecorded']);

        $this->post('/record7/manager/action', ['issue_key' => $key, 'note' => 'Rang the nurse.']);
        $item = collect(app(ManagerBoard::class)->attention($rosewood))->firstWhere('key', $key);
        $this->assertTrue($item['actionRecorded']);
        $this->assertFalse($item['closed']);
        $this->assertTrue($item['conditionActive'], 'Recording an action is not fixing it.');
    }

    /* ── 2. Identity and tenant scoping ─────────────────────────────────── */

    public function test_an_issue_key_naming_another_houses_record_is_refused(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        // A real dose — but it belongs to Rosewood.
        $elsewhere = ScheduledDose::where('service_id', $this->rosewood()->id)->firstOrFail();
        $key = 'omitted_dose:'.$elsewhere->id;

        $this->post('/record7/manager/own', ['issue_key' => $key])->assertNotFound();

        $this->assertSame(
            0,
            IssueState::where('issue_key', $key)->count(),
            'No state row may be written against a record from another house.'
        );
    }

    public function test_the_same_source_id_in_two_houses_stays_isolated(): void
    {
        $oakwood = $this->oakwood()->id;
        $rosewood = $this->rosewood()->id;

        // Deliberately the same numeric source id, in both houses.
        foreach ([$oakwood, $rosewood] as $serviceId) {
            $service = Service::find($serviceId);

            IssueState::create([
                'organisation_id' => $service->organisation_id,
                'service_id' => $serviceId,
                'issue_key' => 'stock_low:99999',
                'issue_type' => 'stock_low',
                'source_id' => 99999,
                'note' => 'House '.$serviceId,
            ]);
        }

        $this->assertSame(2, IssueState::where('source_id', 99999)->count());

        $oakwoodRow = IssueState::where('service_id', $oakwood)->where('source_id', 99999)->firstOrFail();
        $rosewoodRow = IssueState::where('service_id', $rosewood)->where('source_id', 99999)->firstOrFail();

        $this->assertNotSame($oakwoodRow->id, $rosewoodRow->id);
        $this->assertNotSame($oakwoodRow->note, $rosewoodRow->note);
    }

    public function test_identity_is_unique_within_a_house_and_not_across_them(): void
    {
        $indexes = collect(DB::connection('record7')->select('SHOW INDEXES FROM record7_issue_states'))
            ->groupBy('Key_name');

        $this->assertTrue(
            $indexes->has('record7_issue_states_owned_identity'),
            'Identity must be scoped by organisation and house, not by a bare text key.'
        );

        $columns = $indexes->get('record7_issue_states_owned_identity')
            ->sortBy('Seq_in_index')->pluck('Column_name')->all();

        $this->assertSame(
            ['organisation_id', 'service_id', 'issue_type', 'source_id'],
            $columns
        );
    }

    public function test_another_organisation_cannot_be_reached_through_an_issue_key(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-LIFECYCLE',
            'legal_name' => 'Eastvale Care Ltd',
            'display_name' => 'Eastvale Care',
            'name_normalised' => 'eastvale care',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-LIFECYCLE',
            'organisation_id' => $rival->id,
            'name' => 'Eastvale Lodge',
            'service_type' => 'Residential Care',
            'town' => 'York',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-LIFECYCLE',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Albert Rennison',
            'date_of_birth' => '1940-01-01',
            'status' => 'active',
        ]);

        $theirPrescription = \App\Models\Record7\Prescription::create([
            'reference' => 'TEST-P-LIFECYCLE',
            'client_id' => $theirClient->id,
            'medicine_id' => \App\Models\Record7\Medicine::firstOrFail()->id,
            'dose' => 'One tablet',
            'route' => 'Oral',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'starts_on' => now()->subMonth(),
            'status' => 'active',
        ]);

        $theirDose = ScheduledDose::create([
            'prescription_id' => $theirPrescription->id,
            'client_id' => $theirClient->id,
            'service_id' => $theirHouse->id,
            'due_at' => now()->subHours(4),
            'slot' => 'Morning',
            'grace_minutes' => 30,
        ]);

        $this->enter('daniel.evans', 'Oakwood House');

        $this->post('/record7/manager/own', ['issue_key' => 'omitted_dose:'.$theirDose->id])
            ->assertNotFound();

        $this->post('/record7/manager/close', [
            'issue_key' => 'omitted_dose:'.$theirDose->id,
            'reason' => 'Trying it on.',
            'evidence_reference' => 'X',
        ])->assertNotFound();

        $this->assertSame(0, IssueState::where('source_id', $theirDose->id)
            ->where('issue_type', 'omitted_dose')->count());
    }

    public function test_an_unknown_issue_type_is_refused(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $this->post('/record7/manager/own', ['issue_key' => 'made_up_thing:1'])
            ->assertStatus(500);
    }

    /* ── 3. Correction approval ─────────────────────────────────────────── */

    public function test_a_manager_cannot_invent_a_correction_without_a_request(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        // There is no endpoint that writes a correction without a review item.
        // The only route into correct() is decideReview, which loads one.
        $this->post('/record7/manager/decide', [
            'review_id' => 999999,
            'decision' => 'approved',
        ])->assertNotFound();
    }

    public function test_a_manager_cannot_substitute_a_different_outcome(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $item = ReviewItem::where('service_id', $this->oakwood()->id)
            ->where('kind', 'correction_request')
            ->where('status', 'open')
            ->firstOrFail();

        $this->assertSame('missed', $item->requested_outcome);

        // Asking for something other than what was requested is refused.
        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'corrected_outcome' => 'refused',
            'note' => 'Changing it to something else.',
        ])->assertStatus(500);

        $this->assertSame('open', $item->fresh()->status);
        $this->assertSame(
            0,
            Administration::where('corrects_administration_id', $item->subject_id)->count()
        );
    }

    public function test_an_approved_correction_names_the_requester_and_the_approver(): void
    {
        $this->enter('daniel.evans', 'Oakwood House');

        $item = ReviewItem::where('service_id', $this->oakwood()->id)
            ->where('kind', 'correction_request')
            ->where('status', 'open')
            ->firstOrFail();

        $this->post('/record7/manager/decide', [
            'review_id' => $item->id,
            'decision' => 'approved',
            'note' => 'Confirmed with the staff member.',
        ])->assertRedirect('/record7/manager');

        $correction = Administration::where('corrects_administration_id', $item->subject_id)->firstOrFail();

        $this->assertSame('missed', $correction->outcome, 'It carries out what was requested.');
        $this->assertStringContainsString('requested by', $correction->notes);
        $this->assertStringContainsString('approved by', $correction->notes);
        $this->assertSame($this->user('daniel.evans')->id, $correction->recorded_by_user_id);

        // Who asked is on the queue item, and it cannot be rewritten.
        $this->assertSame($item->raised_by_user_id, $item->fresh()->raised_by_user_id);
        $this->assertSame($this->user('daniel.evans')->id, $item->fresh()->decided_by_user_id);
    }

    /* ── 4. Final administration authority ──────────────────────────────── */

    /**
     * The five factors, and the rule that the last word is never the job title.
     */
    public function test_the_final_decision_uses_every_factor(): void
    {
        $rosewood = $this->rosewood()->id;
        $ruth = User::where('username', 'ruth.coleman')->firstOrFail();
        $policy = app(\App\Services\Record7\AccessPolicy::class);

        $row = fn () => collect(app(ManagerBoard::class)->staffReadiness($rosewood))
            ->firstWhere('userId', $ruth->id);

        // Competency expired: permission says yes, the final answer says no.
        $this->assertTrue($row()['hasPermission']);
        $this->assertSame('expired', $row()['competencyStatus']);
        $this->assertFalse($row()['mayAdminister']);

        // Fix the competency: now yes.
        $gate = \App\Models\Record7\CompetencyType::where('gates_permission', 'administer_medication')
            ->firstOrFail();

        UserCompetency::where('user_id', $ruth->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'current', 'review_due_at' => now()->addYear()]);

        $this->assertTrue($policy->allows($ruth->fresh(), 'administer_medication', $rosewood));

        // Suspend her access to the house: no again, and the role is untouched.
        UserServiceAccess::where('user_id', $ruth->id)
            ->where('service_id', $rosewood)
            ->update(['status' => 'suspended']);

        $this->assertFalse($policy->allows($ruth->fresh(), 'administer_medication', $rosewood));
        $this->assertSame('Support Worker', $ruth->fresh()->primaryRole()?->name);

        // Restore access, then suspend the account itself.
        UserServiceAccess::where('user_id', $ruth->id)
            ->where('service_id', $rosewood)
            ->update(['status' => 'active']);

        $this->assertTrue($policy->allows($ruth->fresh(), 'administer_medication', $rosewood));

        $ruth->update(['account_status' => 'suspended']);
        $this->assertFalse($policy->allows($ruth->fresh(), 'administer_medication', $rosewood));
    }

    public function test_a_role_alone_never_authorises_administration(): void
    {
        $rosewood = $this->rosewood()->id;
        $policy = app(\App\Services\Record7\AccessPolicy::class);

        // Grace is a Support Worker with access to Rosewood and an explicit
        // deny. Her job title is identical to Ruth's and to Olivia's.
        $grace = User::where('username', 'grace.taylor')->firstOrFail();

        $this->assertSame('Support Worker', $grace->primaryRole()?->name);
        $this->assertFalse($policy->allows($grace, 'administer_medication', $rosewood));

        $olivia = User::where('username', 'olivia.carter')->firstOrFail();
        $this->assertSame('Support Worker', $olivia->primaryRole()?->name);
        $this->assertTrue($policy->allows($olivia, 'administer_medication', $rosewood));
    }
}
