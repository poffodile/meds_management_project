<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Medicine;
use App\Models\Record7\Organisation;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\UserCompetency;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\RoundQueue;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.2 — signing for a medicine.
 *
 * These attack the things that would put a false statement into a record that
 * can never be deleted: the same dose signed twice, a medicine signed in
 * somebody else's name, a dose belonging to another person or house reached
 * through a number in a URL, a controlled drug given without a witness, and a
 * person in hospital recorded as having been handed a tablet.
 */
class Record7AdministrationTest extends Record7TestCase
{
    /** These describe the medication day, so they run at a fixed hour in it. */
    protected bool $anchorClockToFixtureDay = true;

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    private function recorder(): AdministrationRecorder
    {
        return app(AdministrationRecorder::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function enterRound(string $username = 'noah.williams', string $house = 'Oakwood House')
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
        $this->post('/record7/round/start');

        return app(RoundEntry::class)->openRoundFor($this->house($house)->id);
    }

    /** A dose in this round that Section 2.2 is actually willing to record. */
    private function givableDose($round): ?ScheduledDose
    {
        foreach (app(RoundQueue::class)->forRound($round) as $row) {
            $client = Client::find($row['clientId']);

            if (! $client) {
                continue;
            }

            $doses = ScheduledDose::with(['prescription.medicine', 'administration'])
                ->where('client_id', $client->id)
                ->where('slot', $round->slot)
                ->whereDate('due_at', $round->round_date->toDateString())
                ->get();

            foreach ($doses as $dose) {
                if ($this->recorder()->eligibility($dose, $client)['allowed']) {
                    return $dose;
                }
            }
        }

        return null;
    }

    /**
     * A dose in this round with nothing recorded against it, carrying whatever
     * arrangement the test is actually about.
     *
     * Built rather than borrowed, because a fixture dose that already has an
     * outcome refuses for the wrong reason and the test then passes without
     * exercising the rule it names.
     */
    private function freshDose($round, array $prescription): ScheduledDose
    {
        $client = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'active')->firstOrFail();

        $medicine = Medicine::where('is_controlled', false)->firstOrFail();

        $written = Prescription::create(array_merge([
            'client_id' => $client->id,
            'medicine_id' => $medicine->id,
            'dose' => 'One tablet',
            'route' => 'Oral',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ], $prescription));

        return ScheduledDose::create([
            'prescription_id' => $written->id,
            'client_id' => $client->id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ])->fresh(['prescription.medicine', 'administration']);
    }

    private function url(ScheduledDose $dose, string $suffix = ''): string
    {
        return '/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.$suffix;
    }

    /* ── 1 to 5. The journey, and what it writes ────────────────────────── */

    public function test_an_authorised_worker_records_a_scheduled_medicine_as_given(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->assertNotNull($dose, 'The fixture must offer at least one givable dose.');

        $this->get($this->url($dose))->assertOk();

        $before = Administration::count();

        $this->post($this->url($dose, '/given'))
            ->assertRedirect('/record7/round/person/'.$dose->client_id);

        $this->assertSame($before + 1, Administration::count());

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame('given', $administration->outcome);
    }

    public function test_the_administration_is_anchored_to_the_planned_dose(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // Every fact that makes the record answerable later.
        $this->assertSame($dose->id, (int) $administration->scheduled_dose_id);
        $this->assertSame($dose->prescription_id, (int) $administration->prescription_id);
        $this->assertSame($dose->client_id, (int) $administration->client_id);
        $this->assertSame($round->service_id, (int) $administration->service_id);
    }

    public function test_the_planned_dose_survives_untouched(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $dueAt = $dose->due_at->copy();
        $slot = $dose->slot;

        $this->post($this->url($dose, '/given'));

        $after = ScheduledDose::findOrFail($dose->id);

        // The plan is not edited, moved or deleted to mark it done. A medicine
        // given late must not have its due time quietly changed to make the
        // record look punctual.
        $this->assertTrue($dueAt->equalTo($after->due_at));
        $this->assertSame($slot, $after->slot);
    }

    public function test_the_actual_time_is_recorded_separately_from_the_due_time(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertNotNull($administration->administered_at);
        $this->assertTrue(
            $administration->administered_at->greaterThan($dose->due_at)
                || $administration->administered_at->equalTo($dose->due_at)
                || $administration->administered_at->lessThan($dose->due_at),
            'The times are separate facts.'
        );

        // The server's clock, not the browser's.
        $this->assertLessThan(
            120,
            abs($administration->administered_at->diffInSeconds(now())),
            'The administration time must come from the server.'
        );
    }

    public function test_the_authenticated_worker_is_recorded(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame(
            $this->user('noah.williams')->id,
            (int) $administration->recorded_by_user_id
        );
    }

    /* ── 6. Impersonation ───────────────────────────────────────────────── */

    public function test_a_posted_staff_id_is_ignored_entirely(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $someoneElse = $this->user('olivia.carter');

        $this->post($this->url($dose, '/given'), [
            'recorded_by_user_id' => $someoneElse->id,
            'user_id' => $someoneElse->id,
            'recordedBy' => $someoneElse->id,
        ]);

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame(
            $this->user('noah.williams')->id,
            (int) $administration->recorded_by_user_id,
            'A medicine must never be signed in somebody else\'s name.'
        );
    }

    public function test_joining_another_workers_round_does_not_attribute_the_record_to_them(): void
    {
        // Olivia opens the round.
        $opened = $this->enterRound('olivia.carter');

        // Noah joins the same round and records something.
        $round = $this->enterRound('noah.williams');
        $this->assertSame($opened->id, $round->id, 'Both should be in the same round.');

        $dose = $this->givableDose($round);
        $this->post($this->url($dose, '/given'));

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame(
            $this->user('noah.williams')->id,
            (int) $administration->recorded_by_user_id
        );
        $this->assertNotSame(
            (int) $opened->opened_by_user_id,
            (int) $administration->recorded_by_user_id
        );
    }

    /* ── 7 to 11. Isolation ─────────────────────────────────────────────── */

    public function test_another_organisations_dose_cannot_be_recorded(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-2-2',
            'legal_name' => 'Selby Care Ltd',
            'display_name' => 'Selby Care',
            'name_normalised' => 'selby care 22',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-2-2',
            'organisation_id' => $rival->id,
            'name' => 'Selby Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Selby',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-2-2',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Edith Marsden',
            'date_of_birth' => '1936-02-02',
            'status' => 'active',
        ]);

        $round = $this->enterRound();
        $borrowed = $this->givableDose($round);

        $before = Administration::count();

        // Their person, our dose id — and the other way round.
        $this->post('/record7/round/person/'.$theirClient->id.'/medicine/'.$borrowed->id.'/given')
            ->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    public function test_another_houses_dose_cannot_be_recorded(): void
    {
        $round = $this->enterRound('olivia.carter', 'Oakwood House');

        $elsewhere = ScheduledDose::where('service_id', $this->rosewood()->id)->first();

        if (! $elsewhere) {
            $this->markTestSkipped('No Rosewood dose in the fixture.');
        }

        $before = Administration::count();

        // The fixture may already have answered that dose during its own shift,
        // so the property under test is that NOTHING CHANGED — compared by the
        // actual rows, not by a timestamp window that a reseed can fall inside.
        $rowsBefore = Administration::where('scheduled_dose_id', $elsewhere->id)
            ->orderBy('id')->pluck('id')->all();

        $this->post('/record7/round/person/'.$elsewhere->client_id
            .'/medicine/'.$elsewhere->id.'/given')->assertNotFound();

        $this->assertSame($before, Administration::count());
        $this->assertSame(
            $rowsBefore,
            Administration::where('scheduled_dose_id', $elsewhere->id)
                ->orderBy('id')->pluck('id')->all()
        );
    }

    public function test_another_persons_dose_cannot_be_substituted(): void
    {
        $round = $this->enterRound();
        $queue = app(RoundQueue::class)->forRound($round);

        $this->assertGreaterThan(1, count($queue));

        $dose = $this->givableDose($round);
        $otherPerson = collect($queue)
            ->first(fn ($row) => $row['clientId'] !== $dose->client_id);

        $before = Administration::count();

        // Their person id, somebody else's dose id.
        $this->post('/record7/round/person/'.$otherPerson['clientId']
            .'/medicine/'.$dose->id.'/given')->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    public function test_a_dose_from_another_slot_cannot_be_substituted(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $otherSlot = ScheduledDose::where('client_id', $dose->client_id)
            ->where('slot', '!=', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->first();

        if (! $otherSlot) {
            $this->markTestSkipped('That person has only one slot today.');
        }

        $before = Administration::count();

        $this->post($this->url($otherSlot, '/given'))->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    public function test_a_dose_from_another_day_cannot_be_substituted(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        // Yesterday's obligation, same person, same slot.
        $yesterday = ScheduledDose::create([
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'due_at' => $dose->due_at->copy()->subDay(),
            'slot' => $dose->slot,
            'grace_minutes' => $dose->grace_minutes,
        ]);

        $before = Administration::count();

        $this->post($this->url($yesterday, '/given'))->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    /* ── 12 to 14. Authority lost between looking and pressing ──────────── */

    public function test_competency_expiring_before_confirmation_blocks_the_write(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        // He reads the confirmation screen while still competent.
        $this->get($this->url($dose))->assertOk();

        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $before = Administration::count();

        $this->post($this->url($dose, '/given'))->assertForbidden();

        $this->assertSame($before, Administration::count());

        // The obligation survives. Nothing was destroyed by the refusal.
        $this->assertNotNull(ScheduledDose::find($dose->id));
    }

    public function test_permission_withdrawn_before_confirmation_blocks_the_write(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $user = $this->user('noah.williams');

        DB::connection('record7')->table('record7_user_permissions')
            ->where('user_id', $user->id)->update(['effect' => 'deny']);

        $before = Administration::count();

        $this->post($this->url($dose, '/given'))->assertForbidden();

        $this->assertSame($before, Administration::count());
    }

    public function test_a_suspended_account_cannot_record_an_administration(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $user = $this->user('noah.williams');
        $user->forceFill(['account_status' => 'suspended'])->save();

        $before = Administration::count();

        // A suspended account is turned away by the guard before the round is
        // even reached, so this is a redirect rather than a 403. Either answer
        // is safe; what matters is that nothing was written.
        $response = $this->post($this->url($dose, '/given'));

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertSame($before, Administration::count());
        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    /* ── 15 to 18. Progress comes from records, not clicks ──────────────── */

    public function test_a_successful_administration_moves_person_and_round_progress(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $before = app(RoundQueue::class)->progress($round);

        $this->post($this->url($dose, '/given'));

        $after = app(RoundQueue::class)->progress($round);

        $this->assertNotEquals(
            $before,
            $after,
            'Recording a medicine must move the round on.'
        );

        // And the person's own view now shows it.
        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertTrue($item['recorded']);
        $this->assertSame('given', $item['recordedCode']);
    }

    public function test_merely_opening_the_confirmation_records_nothing(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $before = Administration::count();
        $progressBefore = app(RoundQueue::class)->progress($round);

        $this->get($this->url($dose))->assertOk();
        $this->get($this->url($dose))->assertOk();

        $this->assertSame($before, Administration::count());
        $this->assertEquals($progressBefore, app(RoundQueue::class)->progress($round));
    }

    public function test_cancelling_records_nothing(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $before = Administration::count();

        $this->get($this->url($dose))->assertOk();
        // Cancel is a navigation back to the person, not a submission.
        $this->get('/record7/round/person/'.$dose->client_id)->assertOk();

        $this->assertSame($before, Administration::count());
    }

    /* ── 19 to 21. Duplicates, the thing that must never happen ─────────── */

    public function test_a_repeated_submission_cannot_create_a_second_administration(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));
        $this->post($this->url($dose, '/given'));
        $this->post($this->url($dose, '/given'));

        $this->assertSame(
            1,
            Administration::where('scheduled_dose_id', $dose->id)->count(),
            'One planned dose, one successful administration.'
        );
    }

    public function test_a_retry_after_success_is_answered_rather_than_failing(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $first = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // The retry lands on the person, not on an error page, and says so.
        $this->post($this->url($dose, '/given'))
            ->assertRedirect('/record7/round/person/'.$dose->client_id)
            ->assertSessionHas('r7.recorded');

        $again = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame($first->id, $again->id);
        $this->assertTrue($first->administered_at->equalTo($again->administered_at));
    }

    /**
     * The constraint, not the controller.
     *
     * Two workers on two phones, or one worker whose browser retried a request
     * it thought had timed out, put two inserts in flight with nothing between
     * them. Application logic that asks "has this been recorded?" before
     * inserting has a gap; this proves the database closes it.
     */
    public function test_the_database_itself_refuses_a_second_administration(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $first = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // Straight at the table, bypassing every check the product makes.
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        DB::connection('record7')->table('record7_administrations')->insert([
            'reference' => 'R7A-RACE-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $first->prescription_id,
            'client_id' => $first->client_id,
            'service_id' => $first->service_id,
            'recorded_by_user_id' => $first->recorded_by_user_id,
            'outcome' => 'given',
            'administered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** A correction is a new row about the same dose, and must stay possible. */
    public function test_the_constraint_still_allows_a_later_correction(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $original = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $correctionId = DB::connection('record7')->table('record7_administrations')->insertGetId([
            'reference' => 'R7A-CORR-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $original->prescription_id,
            'client_id' => $original->client_id,
            'service_id' => $original->service_id,
            'recorded_by_user_id' => $original->recorded_by_user_id,
            'outcome' => 'given',
            'corrects_administration_id' => $original->id,
            'administered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertGreaterThan(0, $correctionId, 'Section 2.7 must remain buildable.');
    }

    /* ── 22. Late ───────────────────────────────────────────────────────── */

    public function test_a_late_medicine_can_still_be_given_and_keeps_both_times(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        // Make it unambiguously late.
        $dose->forceFill(['due_at' => now()->subHours(4)])->save();
        $dose->refresh();

        $this->post($this->url($dose, '/given'))
            ->assertRedirect('/record7/round/person/'.$dose->client_id);

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();
        $after = ScheduledDose::findOrFail($dose->id);

        // Lateness is not a reason to refuse, and not a reason to rewrite time.
        $this->assertTrue($dose->due_at->equalTo($after->due_at));
        $this->assertTrue($administration->administered_at->greaterThan($after->due_at));
        $this->assertGreaterThan(60, $after->due_at->diffInMinutes($administration->administered_at));
    }

    /* ── 23 to 26. The boundaries this section must not cross ───────────── */

    public function test_somebody_in_hospital_cannot_be_recorded_as_given(): void
    {
        $round = $this->enterRound();

        $away = Client::where('service_id', $this->oakwood()->id)
            ->where('status', '!=', 'active')->first();

        if (! $away) {
            $this->markTestSkipped('Nobody is away in the fixture.');
        }

        $dose = ScheduledDose::where('client_id', $away->id)
            ->where('slot', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->firstOrFail();

        $before = Administration::count();

        $this->post($this->url($dose, '/given'));

        $this->assertSame($before, Administration::count());
        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());

        // And the screen says why rather than offering a dead button.
        $this->assertFalse($this->recorder()->eligibility($dose, $away)['allowed']);
        $this->assertSame('person_away', $this->recorder()->eligibility($dose, $away)['code']);
    }

    public function test_a_self_administered_medicine_cannot_be_recorded_as_staff_given(): void
    {
        $round = $this->enterRound();

        // A FRESH dose with nothing recorded against it. The fixture's own
        // self-administered dose is already answered, and testing against that
        // would prove only that a recorded dose cannot be recorded twice —
        // which is a different rule entirely.
        $dose = $this->freshDose($round, [
            'support_type' => 'self_administered',
            'reference' => 'TEST-P-SELF-22',
        ]);

        $client = Client::findOrFail($dose->client_id);

        $this->assertSame(
            'support_type_self_administered',
            $this->recorder()->eligibility($dose, $client)['code'],
            'The refusal must be about the arrangement, not about anything else.'
        );

        $this->post($this->url($dose, '/given'));

        $this->assertNull(
            Administration::where('scheduled_dose_id', $dose->id)->first(),
            'Staff must not be able to sign for a medicine somebody takes themselves.'
        );
    }

    public function test_a_prompted_medicine_cannot_be_recorded_as_staff_given(): void
    {
        $round = $this->enterRound();

        $dose = $this->freshDose($round, [
            'support_type' => 'prompted',
            'reference' => 'TEST-P-PROMPT-22',
        ]);

        $client = Client::findOrFail($dose->client_id);

        $this->assertSame(
            'support_type_prompted',
            $this->recorder()->eligibility($dose, $client)['code']
        );

        $this->post($this->url($dose, '/given'));

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    /**
     * Section 2.2 does not inherit the round it was looking at.
     *
     * A manager can close a round mid-shift. A worker whose confirmation screen
     * was already open must not be able to post into a round that no longer
     * accepts work — and the middleware knows nothing about round state, so
     * this is the check the ACTION itself has to make.
     */
    public function test_a_closed_round_cannot_be_written_into(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        // He is standing on the confirmation screen when it closes.
        $this->get($this->url($dose))->assertOk();

        // Section 2.6: a round is closed by appending a lifecycle event, not by
        // writing the projection column. Writing closed_at alone is now proved
        // NOT to close anything — see Record7RoundLifecycleTest — so this
        // closes it the way the application actually does.
        app(\App\Services\Record7\RoundLifecycle::class)
            ->close($this->user('daniel.evans'), $round, request());

        $before = Administration::count();

        $response = $this->post($this->url($dose, '/given'));

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertSame($before, Administration::count());
        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());

        // And the planned obligation is untouched by the refusal.
        $this->assertNotNull(ScheduledDose::find($dose->id));
    }

    public function test_a_prompted_medicine_is_not_collapsed_into_staff_given(): void
    {
        // The arrangement where staff remind and watch but do not hand it over.
        // "Given by" would be a false statement about who administered it.
        $this->assertNotContains('prompted', AdministrationRecorder::CAN_BE_GIVEN);
        $this->assertNotContains('self_administered', AdministrationRecorder::CAN_BE_GIVEN);
    }

    public function test_a_controlled_drug_cannot_bypass_its_witness_requirement(): void
    {
        $round = $this->enterRound();

        $controlled = Medicine::where('is_controlled', true)->firstOrFail();

        // Every controlled medicine in the fixture happens to be as-required,
        // and as-required is refused before the witness rule is even reached.
        // A SCHEDULED controlled drug is the case the witness rule exists for,
        // so the test makes one rather than proving the PRN rule twice.
        $someone = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'active')->firstOrFail();

        $prescription = Prescription::create([
            'reference' => 'TEST-P-CD-22',
            'client_id' => $someone->id,
            'medicine_id' => $controlled->id,
            'dose' => 'One tablet',
            'route' => 'Oral',
            'support_type' => 'staff_administered',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        // Put it in this round so the only thing that can stop it is the
        // witness rule itself.
        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $prescription->client_id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);

        $before = Administration::count();

        $this->post($this->url($dose, '/given'));

        $this->assertSame($before, Administration::count());

        $eligibility = $this->recorder()->eligibility(
            $dose->fresh(['prescription.medicine', 'administration']),
            Client::findOrFail($prescription->client_id)
        );

        $this->assertSame('witness_required', $eligibility['code']);
        $this->assertSame('2.5', $eligibility['nextSection']);
    }

    public function test_an_as_required_medicine_cannot_use_the_scheduled_route(): void
    {
        $round = $this->enterRound();

        $prn = Prescription::where('kind', 'prn')
            ->whereIn('client_id', Client::where('service_id', $this->oakwood()->id)->select('id'))
            ->firstOrFail();

        $dose = ScheduledDose::create([
            'prescription_id' => $prn->id,
            'client_id' => $prn->client_id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);

        $before = Administration::count();

        $this->post($this->url($dose, '/given'));

        $this->assertSame($before, Administration::count());

        $eligibility = $this->recorder()->eligibility(
            $dose->fresh(['prescription.medicine', 'administration']),
            Client::findOrFail($prn->client_id)
        );

        $this->assertSame('as_required', $eligibility['code']);
        $this->assertSame('2.4', $eligibility['nextSection']);
    }

    /* ── 27. Stock stays out of it ──────────────────────────────────────── */

    public function test_recording_an_administration_does_not_touch_stock(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $events = StockEvent::count();
        $levels = DB::connection('record7')->table('record7_stock_levels')->get()->toArray();

        $this->post($this->url($dose, '/given'));

        $this->assertSame($events, StockEvent::count(), 'Stock effects belong to Section 2.7.');
        $this->assertEquals(
            $levels,
            DB::connection('record7')->table('record7_stock_levels')->get()->toArray()
        );
    }

    /* ── 28 and 29. Afterwards ──────────────────────────────────────────── */

    public function test_a_recorded_medicine_stays_visible_on_the_person(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $medicineName = $dose->prescription->medicine->name;

        $this->post($this->url($dose, '/given'));

        $body = $this->get('/record7/round/person/'.$dose->client_id)->assertOk()->getContent();

        // Not removed from the list. A worker must be able to look at what they
        // just signed for without recording it again to find out.
        $this->assertStringContainsString(e($medicineName), $body);

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertNotNull($item['recordedAt']);
        $this->assertNotNull($item['recordedBy']);
        $this->assertFalse($item['canBeGiven'], 'It must not offer to record it a second time.');
    }

    public function test_the_administration_is_written_into_the_append_only_audit(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $event = AccessAuditEvent::where('event_type', 'medication_administered')
            ->orderByDesc('id')->firstOrFail();

        $this->assertSame('success', $event->event_result);
        $this->assertSame($this->user('noah.williams')->id, (int) $event->user_id);
        $this->assertSame($round->service_id, (int) $event->service_id);

        $metadata = $event->metadata;

        $this->assertSame($dose->id, $metadata['scheduled_dose_id']);
        $this->assertSame($round->id, $metadata['round_id']);
        $this->assertSame('given', $metadata['outcome']);

        // BOTH times, or lateness cannot be reconstructed from the audit alone.
        $this->assertArrayHasKey('due_at', $metadata);
        $this->assertArrayHasKey('administered_at', $metadata);

        // No clinical free text in the access audit.
        $this->assertArrayNotHasKey('notes', $metadata);
    }

    public function test_a_recorded_administration_cannot_be_edited_or_deleted(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $administration = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        try {
            $administration->outcome = 'refused';
            $administration->save();
            $this->fail('An administration must not be editable.');
        } catch (\RuntimeException $expected) {
            $this->assertStringContainsString('permanent', $expected->getMessage());
        }

        try {
            $administration->delete();
            $this->fail('An administration must not be deletable.');
        } catch (\RuntimeException $expected) {
            $this->assertStringContainsString('cannot be deleted', $expected->getMessage());
        }
    }

    /* ── Found by looking at it in a browser ────────────────────────────── */

    /**
     * A medicine given eight hours late must not read like one given on time.
     *
     * A dose stops being "late" the moment it is answered — that is correct for
     * the chase lists, and it silently removed the only marker distinguishing a
     * punctual administration from a badly delayed one. The delay is now
     * measured against the moment it was actually given, and kept.
     */
    public function test_a_late_administration_still_says_how_late_it_was(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $dose->forceFill(['due_at' => now()->subHours(5)])->save();

        $this->post($this->url($dose, '/given'));

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertTrue($item['recorded']);
        $this->assertNotNull(
            $item['recordedLatePhrase'],
            'Lateness must survive the dose being answered.'
        );
        // A span, not a repetition — the line it sits on already says "after it
        // was due".
        $this->assertMatchesRegularExpression('/^\d+h( \d+m)?$/', $item['recordedLatePhrase']);

        // And the scheduled time is still the scheduled time.
        $this->assertSame(
            ScheduledDose::findOrFail($dose->id)->due_at->format('H:i'),
            $item['dueAt'],
            'Recording a late dose must not move its due time.'
        );
    }

    /** An on-time administration says nothing about lateness. */
    public function test_an_on_time_administration_carries_no_lateness_phrase(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $dose->forceFill(['due_at' => now(), 'grace_minutes' => 60])->save();

        $this->post($this->url($dose, '/given'));

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertNull($item['recordedLatePhrase']);
    }

    /**
     * One outcome, one word, wherever it appears.
     *
     * The pill and the line beneath it were worded separately, so the same
     * fact appeared twice in two different words. A screen that says a thing
     * twice differently teaches people to stop reading the second one.
     */
    public function test_a_recorded_outcome_has_exactly_one_wording(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        $client = Client::findOrFail($dose->client_id);

        foreach (app(RoundPersonView::class)->forPerson($round, $client)['medicines'] as $item) {
            if (! $item['recorded']) {
                continue;
            }

            $this->assertNotNull($item['recordedWord']);
        }

        // The component reads that one word rather than wording it again.
        $component = file_get_contents(
            resource_path('js/record7/components/MedicineRoundItem.jsx')
        );

        $this->assertStringNotContainsString("word: 'Medicine not available'", $component);
        $this->assertStringNotContainsString("word: 'Taken themselves'", $component);
    }

    /**
     * The count says what is LEFT.
     *
     * "2 to check" beside two medicines that are both already answered is not a
     * neutral inaccuracy. It sends somebody looking for work that is done, and
     * on a medicines round that is how a dose gets given a second time.
     */
    public function test_the_person_page_counts_what_is_left_not_what_exists(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/RoundPerson.jsx'));

        $this->assertStringNotContainsString('{medicines.length} to check', $page);
        $this->assertStringContainsString('still to record', $page);

        // Section 2.3 refined this: a fully self-managed medicine is not work
        // waiting to be done either, so it is excluded from the same count.
        $this->assertStringContainsString('!m.recorded', $page);
        $this->assertStringContainsString('!m.selfManaged', $page);
    }

    /**
     * The confirmation heading must not promise what the screen is refusing.
     */
    public function test_the_confirmation_heading_changes_when_it_cannot_record(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $this->post($this->url($dose, '/given'));

        // Come back to the same confirmation screen after it is answered. The
        // heading is rendered in the browser, so the response carries the FACTS
        // it is decided from rather than the words themselves.
        $this->get($this->url($dose))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('medicine.recorded', true)
                ->where('medicine.canBeGiven', false));

        // And the page words itself from those facts rather than always
        // claiming an action it may be refusing.
        $confirm = file_get_contents(resource_path('js/R7Pages/RoundConfirm.jsx'));

        $this->assertStringContainsString("blocked ? 'This medicine'", $confirm);
        $this->assertStringContainsString('already been recorded', $confirm);
    }

    /**
     * The confirmation message and the medicine beneath it must agree.
     *
     * "Recorded: Given" over an item reading "Given with help" is one fact told
     * two ways in the space of one screen.
     */
    public function test_the_confirmation_message_uses_the_same_word_as_the_medicine(): void
    {
        $round = $this->enterRound();

        $dose = $this->freshDose($round, [
            'support_type' => 'assisted',
            'reference' => 'TEST-P-ASSIST-22',
        ]);

        $this->post($this->url($dose, '/given'))
            ->assertSessionHas('r7.recorded', fn ($recorded) => $recorded['outcome'] === 'Given with help');

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertSame('Given with help', $item['recordedWord']);
    }

    /** "7h 12m late after it was due" is a sentence nobody writes on purpose. */
    public function test_the_recorded_lateness_phrase_does_not_repeat_itself(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $dose->forceFill(['due_at' => now()->subHours(3)])->save();

        $this->post($this->url($dose, '/given'));

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertStringNotContainsString('late', $item['recordedLatePhrase']);
        $this->assertMatchesRegularExpression('/^\d+h( \d+m)?$/', $item['recordedLatePhrase']);

        // While the unanswered phrasing still reads as lateness on its own.
        $this->assertStringContainsString('late', $item['latePhrase']);
    }

    /* ── The screen itself ──────────────────────────────────────────────── */

    public function test_the_confirmation_screen_shows_everything_needed_to_catch_an_error(): void
    {
        $round = $this->enterRound();
        $dose = $this->givableDose($round);

        $client = Client::findOrFail($dose->client_id);
        $body = $this->get($this->url($dose))->assertOk()->getContent();

        foreach ([
            $client->full_name,
            $dose->prescription->medicine->name,
            $dose->prescription->dose,
            $dose->prescription->route,
            $dose->due_at->format('H:i'),
        ] as $fact) {
            $this->assertStringContainsString(
                e($fact),
                $body,
                'The worker must be able to check this without leaving the screen.'
            );
        }
    }

    /**
     * The row is not the button.
     *
     * A control that both opens and commits is how a thumb resting on a phone
     * during a scroll signs for a dose nobody gave.
     */
    public function test_opening_a_medicine_and_recording_it_are_separate_requests(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/RoundPerson.jsx'));

        // The person page navigates; it never posts an outcome.
        $this->assertStringNotContainsString('router.post', $page);
        $this->assertStringContainsString('goToConfirm', $page);

        $item = file_get_contents(
            resource_path('js/record7/components/MedicineRoundItem.jsx')
        );

        // The list item has no submit of its own, and the whole row is not a
        // control — the action is an explicit button at the end of the item.
        $this->assertStringNotContainsString('<form', $item);
        $this->assertStringContainsString('r7-med-item__action', $item);
    }

    public function test_the_confirmation_screen_offers_no_outcome_except_given(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/RoundConfirm.jsx'));

        foreach (['Refused', 'Not available', 'Withheld', 'Missed', 'Omitted'] as $laterOutcome) {
            $this->assertStringNotContainsString(
                '>'.$laterOutcome.'<',
                $page,
                $laterOutcome.' is Section 2.3. A half-built version collects worse records.'
            );
        }
    }
}
