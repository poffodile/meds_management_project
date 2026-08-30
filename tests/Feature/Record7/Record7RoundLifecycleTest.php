<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\IssueState;
use App\Models\Record7\Prescription;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Round;
use App\Models\Record7\RoundLifecycleEvent;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\ControlledDrugAdministration;
use App\Services\Record7\ManagerBoard;
use App\Services\Record7\RoundLifecycle;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.6 — round completion, closing and reopening.
 *
 * The line this whole section defends: a dose being ACCOUNTED FOR is not the
 * same as its consequences being RESOLVED. A refusal answers "what happened to
 * this dose" completely, and answers "is this person all right" not at all.
 * Closing a round settles the first and must never touch the second.
 */
class Record7RoundLifecycleTest extends Record7TestCase
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

    private function lifecycle(): RoundLifecycle
    {
        return app(RoundLifecycle::class);
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function manager(): User
    {
        return $this->user('daniel.evans');
    }

    /** A round of our own, so the fixture's is left alone. */
    private function freshRound(?string $slot = null): Round
    {
        $house = $this->oakwood();

        $slot ??= 'LifecycleTest-'.uniqid();

        return Round::create([
            'organisation_id' => $house->organisation_id,
            'service_id' => $house->id,
            'round_date' => now()->toDateString(),
            'slot' => $slot,
            'started_by_user_id' => $this->user('noah.williams')->id,
            'started_at' => now()->subHour(),
        ]);
    }

    /** A planned dose in that round's slot, unanswered. */
    private int $doseOffset = 0;

    /**
     * A planned dose in this round's slot.
     *
     * Each one gets its own minute: `(prescription_id, due_at)` is unique, and
     * two doses for the same medicine at the same instant is not a thing a
     * timetable produces anyway.
     */
    private function plannedDose(Round $round, ?Prescription $for = null): ScheduledDose
    {
        $prescription = $for ?? Prescription::with('medicine')
            ->whereHas('client', fn ($q) => $q->where('service_id', $round->service_id))
            ->where('kind', 'scheduled')
            ->firstOrFail();

        return ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $round->service_id,
            'due_at' => now()->setTimeFromTimeString('09:00')->addMinutes(++$this->doseOffset),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);
    }

    private function answer(ScheduledDose $dose, string $outcome, ?string $reason = 'client_declined'): Administration
    {
        return Administration::create([
            'reference' => 'TEST-2-6-'.uniqid(),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => $outcome,
            'reason_code' => in_array($outcome, ['given', 'self_administered'], true) ? null : $reason,
            'administered_at' => now(),
        ]);
    }

    /* ── Completeness ───────────────────────────────────────────────────── */

    public function test_a_round_with_no_unrecorded_doses_is_complete(): void
    {
        $round = $this->freshRound();
        $dose = $this->plannedDose($round);

        $this->assertFalse($this->lifecycle()->isComplete($round));

        $this->answer($dose, 'given');

        $this->assertTrue($this->lifecycle()->isComplete($round->fresh()));
        $this->assertSame('complete', $round->fresh()->status());
    }

    public function test_one_unrecorded_dose_leaves_the_round_incomplete(): void
    {
        $round = $this->freshRound();
        $this->answer($this->plannedDose($round), 'given');
        $this->plannedDose($round);

        $counts = $this->lifecycle()->accountability($round);

        $this->assertSame(2, $counts['planned']);
        $this->assertSame(1, $counts['accounted']);
        $this->assertSame(1, $counts['unrecorded']);
        $this->assertFalse($this->lifecycle()->isComplete($round));
        $this->assertSame('in_progress', $round->fresh()->status());
    }

    /**
     * The distinction this section exists for.
     *
     * A refusal answers the dose. It does not answer the person.
     */
    public function test_a_refusal_accounts_for_the_dose_while_the_refusal_stays_open(): void
    {
        $round = $this->freshRound();
        $dose = $this->plannedDose($round);

        $refusal = $this->answer($dose, 'refused');

        $this->assertTrue(
            $this->lifecycle()->isComplete($round),
            'The dose has an answer, so the round is accounted for.'
        );

        // The condition is keyed on the administration, which is the refusal.
        $this->assertTrue(
            app(\App\Services\Record7\IssueRegistry::class)
                ->conditionActive('refusal:'.$refusal->id, $round->service_id),
            'And the refusal itself is still a live condition.'
        );
    }

    /** Every terminal outcome accounts for its dose. */
    public function test_every_terminal_outcome_accounts_for_its_dose(): void
    {
        foreach (['given', 'refused', 'withheld', 'missed', 'not_available', 'person_unavailable'] as $outcome) {
            $round = $this->freshRound();
            $dose = $this->plannedDose($round);

            $this->answer($dose, $outcome);

            $this->assertTrue(
                $this->lifecycle()->isComplete($round),
                "{$outcome} must account for its dose."
            );
        }
    }

    /** A self-managed medicine is not staff work and must not block a round. */
    public function test_a_fully_self_managed_dose_creates_no_false_incompleteness(): void
    {
        $round = $this->freshRound();

        $selfManaged = Prescription::whereHas('client', fn ($q) => $q->where('service_id', $round->service_id))
            ->where('kind', 'scheduled')->firstOrFail();

        $selfManaged->forceFill([
            'support_type' => 'self_administered',
            'self_administration_monitoring' => 'none',
        ])->save();

        $this->plannedDose($round, $selfManaged->fresh());

        $this->assertSame(0, $this->lifecycle()->accountability($round)['planned']);
        $this->assertTrue($this->lifecycle()->isComplete($round));
    }

    /** As-required medicines answer a need, not a plan. */
    public function test_prn_activity_does_not_change_completeness(): void
    {
        $round = $this->freshRound();
        $this->answer($this->plannedDose($round), 'given');

        $before = $this->lifecycle()->accountability($round);

        $prn = Prescription::with('medicine')
            ->whereHas('client', fn ($q) => $q->where('service_id', $round->service_id))
            ->where('kind', 'prn')->firstOrFail();

        Administration::create([
            'reference' => 'TEST-2-6-PRN-'.uniqid(),
            'scheduled_dose_id' => null,
            'prescription_id' => $prn->id,
            'client_id' => $prn->client_id,
            'service_id' => $round->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'reason_code' => 'reported_pain',
            'administered_at' => now(),
        ]);

        $this->assertSame($before, $this->lifecycle()->accountability($round->fresh()));
        $this->assertTrue($this->lifecycle()->isComplete($round->fresh()));
    }

    /** A re-offer adds rows to one dose; it must not add accountability. */
    public function test_a_re_offer_does_not_double_count_accountability(): void
    {
        $round = $this->freshRound();
        $dose = $this->plannedDose($round);

        $refusal = $this->answer($dose, 'refused');

        Administration::create([
            'reference' => 'TEST-2-6-RE-'.uniqid(),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'administered_at' => now(),
            'reoffer_of_administration_id' => $refusal->id,
        ]);

        $counts = $this->lifecycle()->accountability($round);

        $this->assertSame(1, $counts['planned']);
        $this->assertSame(1, $counts['accounted'], 'One dose, one accountability, however many rows.');
        $this->assertSame(0, $counts['unrecorded']);
    }

    /* ── Closing ────────────────────────────────────────────────────────── */

    public function test_an_incomplete_round_can_be_closed_and_the_gap_is_recorded(): void
    {
        $round = $this->freshRound();
        $this->answer($this->plannedDose($round), 'given');
        $this->plannedDose($round);

        $event = $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertSame('closed', $event->event);
        $this->assertSame(2, $event->planned_doses);
        $this->assertSame(1, $event->accounted_doses);
        $this->assertSame(1, $event->unrecorded_doses, 'The gap is on the record, not hidden.');
        $this->assertSame('closed', $round->fresh()->status());
    }

    public function test_closing_records_who_and_when_and_what_was_unresolved(): void
    {
        $round = $this->freshRound();
        $this->answer($this->plannedDose($round), 'refused');

        $event = $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertSame($this->manager()->id, $event->actor_user_id);
        $this->assertNotNull($event->actor_name_at_time);
        $this->assertNotNull($event->occurred_at);
        $this->assertIsArray($event->unresolved_categories);
        $this->assertFalse((bool) $event->imported);
    }

    /** Closing twice is refused, not silently repeated. */
    public function test_closing_an_already_closed_round_is_refused(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $this->expectExceptionMessageMatches('/already closed/i');
        $this->lifecycle()->close($this->manager(), $round->fresh(), request());
    }

    public function test_a_replayed_close_adds_no_second_event(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        try {
            $this->lifecycle()->close($this->manager(), $round->fresh(), request());
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame(1, RoundLifecycleEvent::where('round_id', $round->id)->count());
    }

    /* ── Closing resolves nothing ───────────────────────────────────────── */

    public function test_closing_does_not_resolve_an_earlier_refusal(): void
    {
        $round = $this->freshRound();
        $dose = $this->plannedDose($round);
        $refusal = $this->answer($dose, 'refused');

        $key = 'refusal:'.$refusal->id;
        $registry = app(\App\Services\Record7\IssueRegistry::class);

        $this->assertTrue($registry->conditionActive($key, $round->service_id));

        $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertTrue(
            $registry->conditionActive($key, $round->service_id),
            'Signing the round off does not go back to the person.'
        );

        $this->assertSame(
            0,
            IssueState::where('issue_key', $key)->whereNotNull('closed_at')->count()
        );
    }

    /** A later unrelated dose is not an answer to an earlier refusal. */
    public function test_a_later_given_does_not_resolve_an_earlier_refusal(): void
    {
        $round = $this->freshRound();
        $refusal = $this->answer($this->plannedDose($round), 'refused');

        $this->answer($this->plannedDose($round), 'given');
        $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertTrue(
            app(\App\Services\Record7\IssueRegistry::class)
                ->conditionActive('refusal:'.$refusal->id, $round->service_id),
            'A later dose for somebody else answers nothing about this refusal.'
        );
    }

    public function test_closing_does_not_resolve_a_controlled_drug_discrepancy(): void
    {
        $round = $this->freshRound();
        $house = $this->oakwood();

        $morphine = Prescription::with('medicine')
            ->whereHas('medicine', fn ($q) => $q->where('name', 'Morphine sulfate MR'))
            ->firstOrFail();

        $person = Client::findOrFail($morphine->client_id);
        $cd = app(ControlledDrugAdministration::class);
        $witness = $house->controlledDrugWitnessRequired() ? $this->user('sarah.ahmed')->id : null;

        $cd->receive($this->user('noah.williams'), $house, $person, $morphine, 10.0, $witness, null, request());
        $cd->count($this->user('noah.williams'), $house, $person, $morphine, 6.0, $witness, null, request());

        $this->assertTrue($this->lifecycle()->hasControlledDrugDiscrepancy($house->id));

        $event = $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertContains(
            'controlled_drug_register_discrepancy',
            $event->unresolved_categories,
            'The manager was shown it at the moment of signing.'
        );

        $this->assertTrue(
            $this->lifecycle()->hasControlledDrugDiscrepancy($house->id),
            'And it is still there afterwards.'
        );
    }

    public function test_closing_does_not_decide_a_pending_correction_request(): void
    {
        $round = $this->freshRound();

        $item = ReviewItem::create([
            'reference' => 'TEST-2-6-'.uniqid(),
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'kind' => 'correction_request',
            'title' => 'Please correct an outcome',
            'detail' => 'Recorded as given, was actually refused.',
            'raised_by_user_id' => $this->user('noah.williams')->id,
            'raised_at' => now(),
            'severity' => 'medium',
            'status' => 'open',
        ]);

        $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertSame('open', $item->fresh()->status, 'Closing decides nothing.');
    }

    /* ── Reopening ──────────────────────────────────────────────────────── */

    private function approvedRequest(Round $round): ReviewItem
    {
        return ReviewItem::create([
            'reference' => 'TEST-2-6-RO-'.uniqid(),
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'kind' => 'round_reopen_request',
            'title' => 'Reopen the round',
            'detail' => 'A dose was given but not recorded before it closed.',
            'subject_type' => 'round',
            'subject_id' => $round->id,
            'raised_by_user_id' => $this->user('noah.williams')->id,
            'raised_at' => now(),
            'severity' => 'low',
            'status' => 'approved',
            'decided_by_user_id' => $this->manager()->id,
            'decided_at' => now(),
        ]);
    }

    /** The heart of it: nothing about the closure is erased. */
    public function test_reopening_never_erases_the_closure(): void
    {
        $round = $this->freshRound();
        $closed = $this->lifecycle()->close($this->manager(), $round, request());

        $closedAt = $round->fresh()->closed_at;
        $closedBy = $round->fresh()->closed_by_user_id;

        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round),
            'A dose was given but not recorded.', request()
        );

        $after = $round->fresh();

        $this->assertNotNull($after->closed_at, 'closed_at must never be cleared again.');
        $this->assertEquals($closedAt, $after->closed_at);
        $this->assertSame($closedBy, $after->closed_by_user_id);
        $this->assertNotNull($after->reopened_at);

        $this->assertNotNull(
            RoundLifecycleEvent::find($closed->id),
            'And the closure event itself is still there.'
        );

        $this->assertFalse($after->isClosed(), 'A reopened round is open again.');
    }

    /** Any number of cycles, all of them kept. */
    public function test_every_close_and_reopen_cycle_is_preserved(): void
    {
        $round = $this->freshRound();

        foreach (range(1, 2) as $cycle) {
            $this->lifecycle()->close($this->manager(), $round->fresh(), request());
            $this->lifecycle()->reopen(
                $this->manager(), $round->fresh(), $this->approvedRequest($round),
                "Cycle {$cycle}.", request()
            );
        }

        $events = RoundLifecycleEvent::where('round_id', $round->id)
            ->orderBy('sequence_no')->pluck('event')->all();

        $this->assertSame(['closed', 'reopened', 'closed', 'reopened'], $events);
    }

    public function test_reopening_needs_an_approved_request_for_this_round(): void
    {
        $round = $this->freshRound();
        $other = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        // Approved, but for a different round.
        $this->expectExceptionMessageMatches('/does not authorise/i');

        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($other), 'Wrong round.', request()
        );
    }

    public function test_reopening_needs_the_request_to_be_approved(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $open = $this->approvedRequest($round);
        $open->forceFill(['status' => 'open'])->save();

        $this->expectExceptionMessageMatches('/does not authorise/i');

        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $open->fresh(), 'Not decided yet.', request()
        );
    }

    public function test_reopening_needs_a_reason(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $this->expectExceptionMessageMatches('/say why/i');

        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round), '   ', request()
        );
    }

    public function test_a_round_that_is_not_closed_cannot_be_reopened(): void
    {
        $round = $this->freshRound();

        $this->expectExceptionMessageMatches('/not closed/i');

        $this->lifecycle()->reopen(
            $this->manager(), $round, $this->approvedRequest($round), 'Stale screen.', request()
        );
    }

    /** One approval authorises exactly one reopen. */
    public function test_one_approval_cannot_be_spent_twice(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $approval = $this->approvedRequest($round);

        $this->lifecycle()->reopen($this->manager(), $round->fresh(), $approval, 'First.', request());
        $this->lifecycle()->close($this->manager(), $round->fresh(), request());

        $spentTwice = false;

        try {
            $this->lifecycle()->reopen($this->manager(), $round->fresh(), $approval, 'Again.', request());
            $spentTwice = true;
        } catch (\Throwable) {
            // expected: the unique key refuses it
        }

        $this->assertFalse($spentTwice, 'One approval, one reopen.');

        $this->assertSame(
            1,
            RoundLifecycleEvent::where('round_id', $round->id)->where('event', 'reopened')->count()
        );
    }

    /* ── Authority ──────────────────────────────────────────────────────── */

    public function test_reopening_needs_its_own_permission(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $worker = $this->user('noah.williams');

        $this->assertFalse(
            app(AccessPolicy::class)->allows($worker, 'reopen_medication_round', $round->service_id),
            'A medication administrator does not reopen a signed-off period.'
        );
    }

    public function test_the_three_approved_roles_hold_the_permission_and_others_do_not(): void
    {
        $holders = DB::connection('record7')->table('record7_role_permissions as rp')
            ->join('record7_roles as r', 'r.id', '=', 'rp.role_id')
            ->join('record7_permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('p.code', 'reopen_medication_round')
            ->pluck('r.name')->sort()->values()->all();

        $this->assertSame(
            ['Medication Lead', 'Organisation Owner', 'Service Manager'],
            $holders,
            'Three roles, and only three.'
        );
    }

    /* ── Projections are projections ────────────────────────────────────── */

    /**
     * Writing the column does not close the round.
     *
     * This is the whole point of moving state to the chain: a projection that
     * has drifted, or been written by hand, must not be able to tell the
     * application something the history denies.
     */
    public function test_writing_closed_at_by_hand_does_not_close_a_round(): void
    {
        $round = $this->freshRound();

        $round->forceFill(['closed_at' => now(), 'closed_by_user_id' => $this->manager()->id])->save();

        $this->assertFalse($round->fresh()->isClosed(), 'The chain says nothing happened.');
        $this->assertSame(0, RoundLifecycleEvent::where('round_id', $round->id)->count());
    }

    /** A stale closed_at on a reopened round does not reclose it. */
    public function test_state_follows_the_chain_not_a_stale_closed_at(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());
        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round), 'Reopened.', request()
        );

        // closed_at is still set — it now means "most recent closure".
        $this->assertNotNull($round->fresh()->closed_at);

        $this->assertFalse($round->fresh()->isClosed(), 'The newest event is a reopen.');
        $this->assertNotSame('closed', $round->fresh()->status());
    }

    /** Clearing closed_at on a closed round does not open it. */
    public function test_clearing_closed_at_does_not_open_a_closed_round(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $round->forceFill(['closed_at' => null, 'closed_by_user_id' => null])->save();

        $this->assertTrue($round->fresh()->isClosed(), 'The chain still says closed.');
    }

    /** A head pointer at the wrong event does not decide anything either. */
    public function test_a_wrong_head_pointer_does_not_change_state(): void
    {
        $round = $this->freshRound();
        $closed = $this->lifecycle()->close($this->manager(), $round, request());
        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round), 'Reopened.', request()
        );

        // Point the head back at the closure.
        $round->forceFill(['last_lifecycle_event_id' => $closed->id])->save();

        $this->assertFalse(
            $round->fresh()->isClosed(),
            'The chain is read, not the pointer.'
        );
    }

    /* ── Immutability ───────────────────────────────────────────────────── */

    private function assertRawSqlRefused(string $sql, array $bindings = []): void
    {
        try {
            DB::connection('record7')->statement($sql, $bindings);
            $this->fail('The database allowed: '.$sql);
        } catch (QueryException $refused) {
            $this->assertNotEmpty($refused->getMessage());
        }
    }

    public function test_raw_sql_cannot_rewrite_or_delete_a_lifecycle_event(): void
    {
        $round = $this->freshRound();
        $event = $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertRawSqlRefused(
            'UPDATE record7_round_lifecycle_events SET event = ? WHERE id = ?', ['reopened', $event->id]
        );

        $this->assertRawSqlRefused(
            'UPDATE record7_round_lifecycle_events SET unrecorded_doses = 0 WHERE id = ?', [$event->id]
        );

        $this->assertRawSqlRefused(
            'DELETE FROM record7_round_lifecycle_events WHERE id = ?', [$event->id]
        );

        $this->assertSame('closed', $event->fresh()->event);
    }

    public function test_the_model_refuses_to_change_or_delete_an_event(): void
    {
        $round = $this->freshRound();
        $event = $this->lifecycle()->close($this->manager(), $round, request());

        $this->expectExceptionMessageMatches('/permanent/i');
        $event->forceFill(['reason' => 'rewritten'])->save();
    }

    public function test_an_event_cannot_be_written_for_another_house(): void
    {
        $round = $this->freshRound();
        $elsewhere = Service::where('id', '!=', $round->service_id)->firstOrFail();

        $this->assertRawSqlRefused(
            'INSERT INTO record7_round_lifecycle_events
                (reference, organisation_id, service_id, round_id, event, sequence_no,
                 occurred_at, actor_user_id, actor_name_at_time, imported, created_at, updated_at)
             VALUES (?, ?, ?, ?, "closed", 1, NOW(), ?, "Forged", 0, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $elsewhere->organisation_id, $elsewhere->id,
                $round->id, $this->manager()->id,
            ]
        );
    }

    /** The snapshot has to add up, or it is not evidence of anything. */
    public function test_a_snapshot_that_does_not_add_up_is_refused(): void
    {
        $round = $this->freshRound();

        $this->assertRawSqlRefused(
            'INSERT INTO record7_round_lifecycle_events
                (reference, organisation_id, service_id, round_id, event, sequence_no,
                 occurred_at, actor_user_id, actor_name_at_time,
                 planned_doses, accounted_doses, unrecorded_doses, imported, created_at, updated_at)
             VALUES (?, ?, ?, ?, "closed", 1, NOW(), ?, "Someone", 5, 1, 1, 0, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $round->organisation_id, $round->service_id,
                $round->id, $this->manager()->id,
            ]
        );
    }

    /** A live transition must name who did it. */
    public function test_a_live_event_without_an_actor_is_refused(): void
    {
        $round = $this->freshRound();

        $this->assertRawSqlRefused(
            'INSERT INTO record7_round_lifecycle_events
                (reference, organisation_id, service_id, round_id, event, sequence_no,
                 occurred_at, imported, created_at, updated_at)
             VALUES (?, ?, ?, ?, "closed", 1, NOW(), 0, NOW(), NOW())',
            ['FORGED-'.uniqid(), $round->organisation_id, $round->service_id, $round->id]
        );
    }

    /* ── Recording ──────────────────────────────────────────────────────── */

    public function test_a_reopened_round_accepts_recording_again(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $this->assertTrue($round->fresh()->isClosed());

        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round), 'Finish the record.', request()
        );

        $this->assertFalse(
            $round->fresh()->isClosed(),
            'RoundAuthority asks isClosed(), so recording is permitted again.'
        );
    }

    /** The lock is taken before anything is decided. */
    public function test_the_round_row_is_locked_before_state_is_read(): void
    {
        $round = $this->freshRound();

        $connection = DB::connection('record7');
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        $this->lifecycle()->close($this->manager(), $round, request());

        $statements = collect($connection->getQueryLog())->pluck('query')
            ->map(fn ($q) => strtolower($q));

        $connection->disableQueryLog();

        $this->assertTrue(
            $statements->contains(fn ($q) => str_contains($q, 'record7_rounds')
                && str_contains($q, 'for update')),
            'The round must be selected FOR UPDATE before the transition is decided.'
        );
    }

    /**
     * A worker without the permission cannot reopen, however the request looks.
     *
     * The earlier version of this only asked AccessPolicy whether the
     * permission was held, which a weakened requirement would have passed
     * happily. This actually tries it.
     */
    public function test_a_worker_without_the_permission_cannot_reopen(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $worker = $this->user('noah.williams');
        $approval = $this->approvedRequest($round);

        $refused = false;

        try {
            $this->lifecycle()->reopen($worker, $round->fresh(), $approval, 'Trying it on.', request());
        } catch (\Throwable) {
            $refused = true;
        }

        $this->assertTrue($refused, 'Reopening needs reopen_medication_round.');
        $this->assertTrue($round->fresh()->isClosed(), 'And the round stayed closed.');

        $this->assertSame(
            0,
            RoundLifecycleEvent::where('round_id', $round->id)->where('event', 'reopened')->count()
        );
    }

    /** The medication lead may, because the role carries the permission. */
    public function test_the_medication_lead_can_reopen(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $lead = $this->user('sarah.ahmed');

        $this->lifecycle()->reopen(
            $lead, $round->fresh(), $this->approvedRequest($round), 'Record needs finishing.', request()
        );

        $this->assertFalse($round->fresh()->isClosed());
    }

    /**
     * Closing writes nothing to issue state.
     *
     * Asserting that a DERIVED condition survives is not enough on its own: a
     * derived condition would survive even if closing had quietly closed every
     * issue row, because it is not read from those rows. So this puts a real
     * open issue row in front of the close and checks it is untouched.
     */
    public function test_closing_writes_nothing_to_issue_state(): void
    {
        $round = $this->freshRound();
        $dose = $this->plannedDose($round);
        $refusal = $this->answer($dose, 'refused');

        $state = IssueState::create([
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'issue_key' => 'refusal:'.$refusal->id,
            'issue_type' => 'refusal',
            'source_id' => $refusal->id,
            'owner_user_id' => $this->manager()->id,
            'assigned_at' => now(),
        ]);

        $this->lifecycle()->close($this->manager(), $round, request());

        $after = $state->fresh();

        $this->assertNull($after->closed_at, 'Closing a round closes no issue.');
        $this->assertNull($after->closed_by_user_id);

        $this->assertSame(
            0,
            IssueState::where('service_id', $round->service_id)
                ->whereNotNull('closed_at')->count(),
            'Not this one, and not any other.'
        );
    }

    /** Reopening writes nothing to issue state either. */
    public function test_reopening_writes_nothing_to_issue_state(): void
    {
        $round = $this->freshRound();
        $refusal = $this->answer($this->plannedDose($round), 'refused');

        $state = IssueState::create([
            'organisation_id' => $round->organisation_id,
            'service_id' => $round->service_id,
            'issue_key' => 'refusal:'.$refusal->id,
            'issue_type' => 'refusal',
            'source_id' => $refusal->id,
            'owner_user_id' => $this->manager()->id,
            'assigned_at' => now(),
        ]);

        $this->lifecycle()->close($this->manager(), $round, request());
        $this->lifecycle()->reopen(
            $this->manager(), $round->fresh(), $this->approvedRequest($round), 'Finish it.', request()
        );

        $this->assertNull($state->fresh()->closed_at);
    }

    /**
     * The new permission does work the old one did not.
     *
     * Priya Nair is an Organisation Administrator: she holds
     * view_manager_dashboard, which is what reopening used to fall through to.
     * If the requirement ever weakened back to that, she would be able to
     * reopen a signed-off round — so she is the person who proves it has not.
     */
    public function test_a_manager_dashboard_user_without_the_new_permission_cannot_reopen(): void
    {
        $round = $this->freshRound();
        $this->lifecycle()->close($this->manager(), $round, request());

        $priya = $this->user('priya.nair');
        $policy = app(AccessPolicy::class);

        $this->assertTrue(
            $policy->allows($priya, 'view_manager_dashboard', $round->service_id),
            'She can see the manager screen — that is the point of this test.'
        );

        $this->assertFalse(
            $policy->allows($priya, 'reopen_medication_round', $round->service_id),
            'But administering accounts is not accountability for the clinical record.'
        );

        $refused = false;

        try {
            $this->lifecycle()->reopen(
                $priya, $round->fresh(), $this->approvedRequest($round), 'Trying it on.', request()
            );
        } catch (\Throwable) {
            $refused = true;
        }

        $this->assertTrue($refused);
        $this->assertTrue($round->fresh()->isClosed());
        $this->assertSame(
            0,
            RoundLifecycleEvent::where('round_id', $round->id)->where('event', 'reopened')->count()
        );
    }
}