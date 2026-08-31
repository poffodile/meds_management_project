<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\IssueState;
use App\Models\Record7\Medicine;
use App\Models\Record7\Organisation;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\UserCompetency;
use App\Models\Record7\WelfareCheck;
use App\Services\Record7\AdministrationRecorder;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\RoundQueue;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.3 — why a medicine was not given.
 *
 * These attack the ways a non-administration record can lie: a refusal that
 * says nothing about why, an absence recorded because a status said so rather
 * than because anybody looked, a missed dose quietly manufactured out of
 * lateness, a controlled drug written off without saying whether any left the
 * cupboard, and a refusal closed by an unrelated later dose.
 */
class Record7NonAdministrationTest extends Record7TestCase
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

    private function reofferUrl(ScheduledDose $dose): string
    {
        return '/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/again';
    }

    private function url(ScheduledDose $dose): string
    {
        return '/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/outcome';
    }

    /** An unanswered dose in this round, for whoever the test needs. */
    private function openDose($round, ?int $clientId = null, bool $controlled = false): ScheduledDose
    {
        $client = $clientId
            ? Client::findOrFail($clientId)
            : Client::where('service_id', $this->oakwood()->id)->where('status', 'active')->firstOrFail();

        $medicine = Medicine::where('is_controlled', $controlled)->firstOrFail();

        $prescription = Prescription::create([
            'reference' => 'TEST-P-23-'.uniqid(),
            'client_id' => $client->id,
            'medicine_id' => $medicine->id,
            'dose' => 'One tablet',
            'route' => 'Oral',
            'support_type' => 'staff_administered',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        return ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ])->fresh(['prescription.medicine', 'administration']);
    }

    /* ── Refusal ────────────────────────────────────────────────────────── */

    public function test_an_authorised_worker_can_record_a_refusal(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->get($this->url($dose))->assertOk();

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'notes' => 'Said he would rather have it after his tea.',
        ])->assertRedirect('/record7/round/person/'.$dose->client_id);

        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame('refused', $refusal->outcome);
        $this->assertSame('client_declined', $refusal->reason_code);
    }

    public function test_the_refusal_records_the_authenticated_actor_and_server_time(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'felt_unwell',
            // A forged actor, ignored entirely.
            'recorded_by_user_id' => $this->user('olivia.carter')->id,
        ]);

        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame($this->user('noah.williams')->id, (int) $refusal->recorded_by_user_id);
        $this->assertLessThan(120, abs($refusal->administered_at->diffInSeconds(now())));

        // The plan itself is untouched.
        $this->assertTrue($dose->due_at->equalTo(ScheduledDose::findOrFail($dose->id)->due_at));
    }

    public function test_a_refusal_without_a_structured_reason_is_refused(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), ['outcome' => 'refused'])
            ->assertSessionHasErrors('reason_code');

        // And a reason belonging to a DIFFERENT outcome is not a reason either.
        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'stock_unavailable',
        ])->assertSessionHas('r7.error');

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    public function test_filler_text_is_rejected_where_an_explanation_is_required(): void
    {
        foreach (['n/a', 'none', '-', '   ', 'N.A.'] as $filler) {
            $this->assertTrue(
                $this->fillerRejected($filler),
                'Filler "'.$filler.'" must not satisfy a required explanation.'
            );
        }
    }

    private function fillerRejected(string $filler): bool
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
            'notes' => $filler,
            'action_taken' => 'Told the manager straight away.',
            'immediate_action_code' => 'manager_notified',
        ]);

        return Administration::where('scheduled_dose_id', $dose->id)->doesntExist();
    }

    /* ── Person unavailable, and Callum ─────────────────────────────────── */

    public function test_person_unavailable_is_a_different_outcome_from_medicine_unavailable(): void
    {
        $round = $this->enterRound();

        $personGone = $this->openDose($round);
        $this->post($this->url($personGone), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'at_appointment',
        ]);

        $noMedicine = $this->openDose($round);
        $this->post($this->url($noMedicine), [
            'outcome' => 'not_available',
            'reason_code' => 'stock_unavailable',
        ]);

        $this->assertSame(
            'person_unavailable',
            Administration::where('scheduled_dose_id', $personGone->id)->firstOrFail()->outcome
        );
        $this->assertSame(
            'not_available',
            Administration::where('scheduled_dose_id', $noMedicine->id)->firstOrFail()->outcome
        );
    }

    /**
     * Callum is in hospital. That is a fact about where he is — not a statement
     * about what a worker did, and only a person can make that statement.
     */
    public function test_a_hospital_status_alone_never_creates_an_outcome(): void
    {
        $round = $this->enterRound();

        $callum = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'in_hospital')->firstOrFail();

        $dose = ScheduledDose::where('client_id', $callum->id)
            ->where('slot', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->firstOrFail();

        // Merely looking at him, repeatedly, records nothing.
        $this->get('/record7/round/person/'.$callum->id)->assertOk();
        $this->get($this->url($dose))->assertOk();

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    public function test_callum_can_be_explicitly_recorded_as_in_hospital(): void
    {
        $round = $this->enterRound();

        $callum = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'in_hospital')->firstOrFail();

        $dose = ScheduledDose::where('client_id', $callum->id)
            ->where('slot', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->firstOrFail();

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'in_hospital',
            'notes' => 'Still on the ward. Spoke to the nurse.',
        ])->assertRedirect('/record7/round/person/'.$callum->id);

        $answer = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertSame('person_unavailable', $answer->outcome);
        $this->assertSame('in_hospital', $answer->reason_code);

        // Never "given" — nobody handed him anything.
        $this->assertFalse($answer->wasTaken());

        // The planned dose survives, with its original time.
        $this->assertTrue($dose->due_at->equalTo(ScheduledDose::findOrFail($dose->id)->due_at));
    }

    public function test_a_person_who_cannot_be_found_raises_urgent_welfare_attention(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
            'notes' => 'Not in her flat and not in the lounge. Looking now.',
        ]);

        $answer = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $issue = IssueState::where('issue_type', 'welfare_check')
            ->where('source_id', $answer->id)->first();

        $this->assertNotNull($issue, 'Nobody knowing where somebody is has to reach a manager.');

        // Live until the underlying fact changes — not merely until somebody
        // ticks it.
        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive('welfare_check:'.$answer->id, $round->service_id)
        );
    }

    /* ── Missed ─────────────────────────────────────────────────────────── */

    public function test_lateness_never_becomes_missed_on_its_own(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $dose->forceFill(['due_at' => now()->subHours(9)])->save();

        // Reading the round, the person and the outcome screen — repeatedly —
        // must never manufacture an outcome out of elapsed time.
        $this->get('/record7/round')->assertOk();
        $this->get('/record7/round/person/'.$dose->client_id)->assertOk();
        $this->get($this->url($dose))->assertOk();

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    public function test_a_missed_dose_requires_reason_explanation_action_and_escalation(): void
    {
        $round = $this->enterRound();

        // Reason alone is not enough.
        $bare = $this->openDose($round);
        $this->post($this->url($bare), [
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
        ])->assertSessionHas('r7.error');
        $this->assertNull(Administration::where('scheduled_dose_id', $bare->id)->first());

        // Nor is an explanation without saying who was told.
        $noEscalation = $this->openDose($round);
        $this->post($this->url($noEscalation), [
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
            'notes' => 'The trolley was still in the office at ten.',
            'action_taken' => 'Checked he was well and told the manager.',
        ])->assertSessionHas('r7.error');
        $this->assertNull(Administration::where('scheduled_dose_id', $noEscalation->id)->first());

        // Complete, and it records.
        $full = $this->openDose($round);
        $this->post($this->url($full), [
            'outcome' => 'missed',
            'reason_code' => 'overlooked',
            'notes' => 'The trolley was still in the office at ten.',
            'action_taken' => 'Checked he was well and told the manager.',
            'immediate_action_code' => 'manager_notified',
        ])->assertRedirect('/record7/round/person/'.$full->client_id);

        $missed = Administration::where('scheduled_dose_id', $full->id)->firstOrFail();

        $this->assertSame('missed', $missed->outcome);
        $this->assertNotNull($missed->action_taken);
        $this->assertSame('manager_notified', $missed->immediate_action_code);
    }

    /* ── Blocked outcomes ───────────────────────────────────────────────── */

    public function test_withheld_cannot_be_recorded(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'withheld',
            'reason_code' => 'client_declined',
        ])->assertSessionHasErrors('outcome');

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    public function test_an_as_required_medicine_cannot_use_this_workflow(): void
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

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertSessionHas('r7.error');

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    /* ── Controlled drugs ───────────────────────────────────────────────── */

    public function test_a_controlled_drug_needs_the_storage_declaration(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round, controlled: true);

        // Without the declaration, nothing is written.
        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertSessionHas('r7.error');

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());

        // With it, the refusal records and the declaration is stored.
        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'controlled_drug_no_quantity_removed' => true,
        ])->assertRedirect('/record7/round/person/'.$dose->client_id);

        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->assertTrue((bool) $refusal->controlled_drug_no_quantity_removed);
    }

    public function test_the_ordinary_given_path_is_still_closed_to_controlled_drugs(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round, controlled: true);

        $this->post('/record7/round/person/'.$dose->client_id.'/medicine/'.$dose->id.'/given');

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    /* ── Stock ──────────────────────────────────────────────────────────── */

    /**
     * SECTION 2.7 NARROWED THIS; IT DID NOT REMOVE IT.
     *
     * The original guarantee — no administration anywhere touches stock — was
     * correct for Sections 2.2 to 2.6 and is what kept that boundary honest.
     * Section 2.7 makes it wrong for TRACKED preparations only: a medicine
     * somebody is counting, whose prescription carries a structured dose.
     *
     * Everywhere else the old rule stands exactly as it did, and it is worth
     * more now than before: an untracked medicine must not acquire a silent
     * debit just because a ledger exists somewhere in the product.
     */
    public function test_no_outcome_changes_stock(): void
    {
        $round = $this->enterRound();

        $events = StockEvent::count();
        $levels = DB::connection('record7')->table('record7_stock_levels')->get()->toArray();

        foreach ([
            ['refused', 'client_declined'],
            ['not_available', 'stock_unavailable'],
            ['person_unavailable', 'away_on_leave'],
        ] as [$outcome, $reason]) {
            $dose = $this->openDose($round);
            $this->post($this->url($dose), ['outcome' => $outcome, 'reason_code' => $reason]);
        }

        $this->assertSame($events, StockEvent::count(), 'Stock effects are Section 2.7.');
        $this->assertEquals(
            $levels,
            DB::connection('record7')->table('record7_stock_levels')->get()->toArray()
        );
    }

    /* ── Isolation and authority ────────────────────────────────────────── */

    public function test_another_organisations_dose_cannot_be_answered(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-2-3',
            'legal_name' => 'Selby Care Ltd',
            'display_name' => 'Selby Care',
            'name_normalised' => 'selby care 23',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-2-3',
            'organisation_id' => $rival->id,
            'name' => 'Selby Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Selby',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-2-3',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Edith Marsden',
            'date_of_birth' => '1936-02-02',
            'status' => 'active',
        ]);

        $round = $this->enterRound();
        $ours = $this->openDose($round);

        $before = Administration::count();

        $this->post('/record7/round/person/'.$theirClient->id.'/medicine/'.$ours->id.'/outcome', [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    public function test_another_houses_dose_cannot_be_answered(): void
    {
        $round = $this->enterRound('olivia.carter', 'Oakwood House');

        $elsewhere = ScheduledDose::where('service_id', $this->rosewood()->id)->firstOrFail();

        $rows = Administration::where('scheduled_dose_id', $elsewhere->id)
            ->orderBy('id')->pluck('id')->all();

        $this->post($this->url($elsewhere), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertNotFound();

        $this->assertSame(
            $rows,
            Administration::where('scheduled_dose_id', $elsewhere->id)->orderBy('id')->pluck('id')->all()
        );
    }

    public function test_another_persons_dose_cannot_be_substituted(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $other = collect(app(RoundQueue::class)->forRound($round))
            ->first(fn ($row) => $row['clientId'] !== $dose->client_id);

        $this->post('/record7/round/person/'.$other['clientId'].'/medicine/'.$dose->id.'/outcome', [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertNotFound();

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    public function test_a_dose_from_another_day_cannot_be_substituted(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $yesterday = ScheduledDose::create([
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'due_at' => $dose->due_at->copy()->subDay(),
            'slot' => $dose->slot,
            'grace_minutes' => 60,
        ]);

        $this->post($this->url($yesterday), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertNotFound();

        $this->assertNull(Administration::where('scheduled_dose_id', $yesterday->id)->first());
    }

    public function test_competency_expiry_blocks_recording_an_outcome(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->get($this->url($dose))->assertOk();

        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ])->assertForbidden();

        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
        $this->assertNotNull(ScheduledDose::find($dose->id), 'The plan survives the refusal.');
    }

    public function test_a_suspended_account_cannot_record_an_outcome(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->user('noah.williams')->forceFill(['account_status' => 'suspended'])->save();

        $response = $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
        ]);

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertNull(Administration::where('scheduled_dose_id', $dose->id)->first());
    }

    /* ── Duplicates and re-offer ────────────────────────────────────────── */

    public function test_a_dose_cannot_receive_two_original_outcomes(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), ['outcome' => 'refused', 'reason_code' => 'client_declined']);
        $this->post($this->url($dose), ['outcome' => 'refused', 'reason_code' => 'felt_unwell']);

        $this->assertSame(1, Administration::where('scheduled_dose_id', $dose->id)->count());
    }

    /** Given and Refused cannot both be the original answer to one dose. */
    public function test_competing_given_and_refused_cannot_both_be_originals(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), ['outcome' => 'refused', 'reason_code' => 'client_declined']);

        $first = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        DB::connection('record7')->table('record7_administrations')->insert([
            'reference' => 'TEST-RACE-'.$dose->id,
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

    public function test_a_re_offer_must_target_a_refusal(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'not_available',
            'reason_code' => 'stock_unavailable',
        ]);

        $notARefusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::connection('record7')->table('record7_administrations')->insert([
            'reference' => 'TEST-BADREOFFER-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $notARefusal->prescription_id,
            'client_id' => $notARefusal->client_id,
            'service_id' => $notARefusal->service_id,
            'recorded_by_user_id' => $notARefusal->recorded_by_user_id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $notARefusal->id,
            'administered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_a_re_offer_cannot_cross_to_another_dose(): void
    {
        $round = $this->enterRound();

        $refusedDose = $this->openDose($round);
        $this->post($this->url($refusedDose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $refusedDose->id)->firstOrFail();

        $otherDose = $this->openDose($round);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::connection('record7')->table('record7_administrations')->insert([
            'reference' => 'TEST-CROSS-'.$otherDose->id,
            'scheduled_dose_id' => $otherDose->id,
            'prescription_id' => $otherDose->prescription_id,
            'client_id' => $otherDose->client_id,
            'service_id' => $otherDose->service_id,
            'recorded_by_user_id' => $refusal->recorded_by_user_id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_a_correction_and_a_re_offer_cannot_both_be_set(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::connection('record7')->table('record7_administrations')->insert([
            'reference' => 'TEST-BOTH-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $refusal->service_id,
            'recorded_by_user_id' => $refusal->recorded_by_user_id,
            'outcome' => 'given',
            'corrects_administration_id' => $refusal->id,
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** The refusal survives the re-offer, and both links stay put afterwards. */
    public function test_a_re_offer_preserves_the_refusal_and_the_links_are_immutable(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $accepted = Administration::create([
            'reference' => 'TEST-REOFFER-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $refusal->service_id,
            'recorded_by_user_id' => $refusal->recorded_by_user_id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now(),
        ]);

        $stillThere = Administration::findOrFail($refusal->id);
        $this->assertSame('refused', $stillThere->outcome);
        $this->assertSame('client_declined', $stillThere->reason_code);

        // The model refuses to rewrite either relationship.
        try {
            $accepted->reoffer_of_administration_id = null;
            $accepted->save();
            $this->fail('A re-offer link must not be rewritable.');
        } catch (\RuntimeException $expected) {
            $this->assertStringContainsString('permanent record', $expected->getMessage());
        }

        // And so does the database, for anything bypassing the model.
        try {
            DB::connection('record7')->table('record7_administrations')
                ->where('id', $accepted->id)
                ->update(['reoffer_of_administration_id' => null]);
            $this->fail('The database must refuse it too.');
        } catch (\Illuminate\Database\QueryException $expected) {
            $this->assertStringContainsString('permanent record', $expected->getMessage());
        }
    }

    public function test_an_accepted_same_dose_re_offer_closes_the_refusal(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $registry = app(IssueRegistry::class);
        $key = 'refusal:'.$refusal->id;

        $this->assertTrue($registry->conditionActive($key, $round->service_id));

        Administration::create([
            'reference' => 'TEST-ACCEPT-'.$dose->id,
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $refusal->service_id,
            'recorded_by_user_id' => $refusal->recorded_by_user_id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => now()->addMinute(),
        ]);

        $this->assertFalse($registry->conditionActive($key, $round->service_id));
    }

    /** The Section 1.2 defect: an unrelated later dose must not close it. */
    public function test_an_unrelated_later_dose_does_not_close_an_earlier_refusal(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // A later planned dose of the same medicine, given normally.
        $later = ScheduledDose::create([
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $dose->service_id,
            'due_at' => $dose->due_at->copy()->addHours(6),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);

        Administration::create([
            'reference' => 'TEST-LATER-'.$later->id,
            'scheduled_dose_id' => $later->id,
            'prescription_id' => $later->prescription_id,
            'client_id' => $later->client_id,
            'service_id' => $later->service_id,
            'recorded_by_user_id' => $refusal->recorded_by_user_id,
            'outcome' => 'given',
            'administered_at' => now()->addHours(6),
        ]);

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive('refusal:'.$refusal->id, $round->service_id),
            'Tonight\'s tablet is not an answer to this morning\'s refusal.'
        );
    }

    /* ── Re-offer, through the worker-facing journey ────────────────────── */

    /** The refusal is offered again, and this time she takes it. */
    public function test_a_refused_dose_can_be_offered_again_and_accepted(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'disliked_form_or_taste',
            'notes' => 'Said it tastes chalky.',
        ]);

        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // The screen offers it, and shows the refusal it follows.
        $this->get($this->reofferUrl($dose))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('reoffer.of', $refusal->id)
                ->where('reoffer.word', 'Refused')
                ->where('reoffer.reason', 'They did not like the taste or the form'));

        $this->post($this->reofferUrl($dose), ['outcome' => 'given'])
            ->assertRedirect('/record7/round/person/'.$dose->client_id);

        $accepted = Administration::where('scheduled_dose_id', $dose->id)
            ->where('outcome', 'given')->firstOrFail();

        // Linked to the refusal, and the refusal is untouched.
        $this->assertSame($refusal->id, (int) $accepted->reoffer_of_administration_id);
        $this->assertNull($accepted->corrects_administration_id, 'A re-offer is not a correction.');

        $original = Administration::findOrFail($refusal->id);
        $this->assertSame('refused', $original->outcome);
        $this->assertSame('disliked_form_or_taste', $original->reason_code);
        $this->assertTrue($refusal->administered_at->equalTo($original->administered_at));

        // And the refusal is no longer chasing anybody.
        $this->assertFalse(
            app(IssueRegistry::class)->conditionActive('refusal:'.$refusal->id, $round->service_id)
        );
    }

    /** She says no again — and that second refusal can itself be offered again. */
    public function test_a_re_offer_can_be_refused_again_and_the_chain_continues(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $first = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->post($this->reofferUrl($dose), [
            'outcome' => 'refused', 'reason_code' => 'felt_unwell',
        ])->assertRedirect('/record7/round/person/'.$dose->client_id);

        $second = Administration::where('scheduled_dose_id', $dose->id)
            ->where('reoffer_of_administration_id', $first->id)->firstOrFail();

        $this->assertSame('refused', $second->outcome);
        $this->assertSame('felt_unwell', $second->reason_code);

        // Both refusals still chasing — nobody has taken anything yet.
        $registry = app(IssueRegistry::class);
        $this->assertTrue($registry->conditionActive('refusal:'.$first->id, $round->service_id));
        $this->assertTrue($registry->conditionActive('refusal:'.$second->id, $round->service_id));

        // A third attempt chains from the SECOND refusal, not the first —
        // otherwise two workers could both answer the original and produce two
        // competing second attempts.
        $this->get($this->reofferUrl($dose))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('reoffer.of', $second->id));

        $this->post($this->reofferUrl($dose), ['outcome' => 'given']);

        $accepted = Administration::where('scheduled_dose_id', $dose->id)
            ->where('outcome', 'given')->firstOrFail();

        $this->assertSame($second->id, (int) $accepted->reoffer_of_administration_id);

        // All three rows survive. Nothing was overwritten.
        $this->assertSame(3, Administration::where('scheduled_dose_id', $dose->id)->count());
    }

    public function test_a_dose_with_no_refusal_cannot_be_offered_again(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        // Nothing recorded at all.
        $this->get($this->reofferUrl($dose))->assertNotFound();
        $this->post($this->reofferUrl($dose), ['outcome' => 'given'])->assertNotFound();

        // And an outcome that is not a refusal is not offerable either.
        $this->post($this->url($dose), [
            'outcome' => 'not_available', 'reason_code' => 'stock_unavailable',
        ]);

        $this->get($this->reofferUrl($dose))->assertNotFound();
        $this->assertSame(1, Administration::where('scheduled_dose_id', $dose->id)->count());
    }

    /** A second offer is held to every safeguard the first one was. */
    public function test_a_re_offer_cannot_bypass_the_controlled_drug_gate(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round, controlled: true);

        $this->post($this->url($dose), [
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'controlled_drug_no_quantity_removed' => true,
        ]);

        $this->assertSame(1, Administration::where('scheduled_dose_id', $dose->id)->count());

        // The ordinary "given" path stays shut for a controlled drug, whether
        // it is a first attempt or a second one.
        $this->post($this->reofferUrl($dose), ['outcome' => 'given'])
            ->assertSessionHas('r7.error');

        $this->assertSame(
            0,
            Administration::where('scheduled_dose_id', $dose->id)->where('outcome', 'given')->count()
        );
    }

    public function test_another_houses_refusal_cannot_be_offered_again(): void
    {
        $this->enterRound('olivia.carter', 'Oakwood House');

        $elsewhere = ScheduledDose::where('service_id', $this->rosewood()->id)->firstOrFail();

        $before = Administration::count();

        $this->get($this->reofferUrl($elsewhere))->assertNotFound();
        $this->post($this->reofferUrl($elsewhere), ['outcome' => 'given'])->assertNotFound();

        $this->assertSame($before, Administration::count());
    }

    /* ── Welfare attention has to be resolvable ─────────────────────────── */

    /**
     * The concern is raised, and it is urgent.
     */
    public function test_not_found_in_service_raises_urgent_welfare_attention(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
            'notes' => 'Not in her flat or the lounge.',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $issue = IssueState::where('issue_type', 'welfare_check')
            ->where('source_id', $report->id)->firstOrFail();

        $this->assertSame($round->service_id, (int) $issue->service_id);
        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'welfare_check:'.$report->id, $round->service_id
            )
        );
    }

    /**
     * GIVING THEM A MEDICINE LATER IS NOT EVIDENCE THAT ANYBODY LOOKED.
     *
     * This is the correction: the condition used to clear as soon as anything
     * else was recorded for the person. A row being written proves a row was
     * written — not that somebody went and found them.
     */
    public function test_unrelated_activity_does_not_resolve_a_welfare_concern(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();
        $registry = app(IssueRegistry::class);
        $key = 'welfare_check:'.$report->id;

        // A completely ordinary later medicine for the same person.
        $later = $this->openDose($round, $report->client_id);
        $this->post('/record7/round/person/'.$later->client_id.'/medicine/'.$later->id.'/given');

        $this->assertTrue(
            $registry->conditionActive($key, $round->service_id),
            'A medicine recorded later does not establish where anybody is.'
        );

        // Nor does another non-administration outcome.
        $another = $this->openDose($round, $report->client_id);
        $this->post($this->url($another), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);

        $this->assertTrue($registry->conditionActive($key, $round->service_id));
    }

    /**
     * Nor does any amount of managing the alert.
     */
    public function test_acknowledgement_ownership_or_closure_does_not_resolve_it(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();
        $registry = app(IssueRegistry::class);
        $key = 'welfare_check:'.$report->id;

        $issue = IssueState::where('issue_type', 'welfare_check')
            ->where('source_id', $report->id)->firstOrFail();

        // Everything a manager can do to the paperwork, all at once.
        $issue->forceFill([
            'acknowledged_at' => now(),
            'acknowledged_by_user_id' => $this->user('daniel.evans')->id,
            'owner_user_id' => $this->user('daniel.evans')->id,
            'assigned_at' => now(),
            'escalated_at' => now(),
            'action_recorded_at' => now(),
            'action_note' => 'Asked the team to keep an eye out.',
            'closed_at' => now(),
            'closure_reason' => 'Closing this off.',
            'evidence_reference' => 'SOME-REFERENCE',
        ])->save();

        $this->assertTrue(
            $registry->conditionActive($key, $round->service_id),
            'None of that establishes where she is.'
        );
    }

    /**
     * Somebody went and looked, and said what they found. THAT resolves it.
     */
    public function test_a_structured_welfare_check_resolves_the_concern(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();
        $registry = app(IssueRegistry::class);
        $key = 'welfare_check:'.$report->id;

        $this->assertTrue($registry->conditionActive($key, $round->service_id));

        // The screen offers it, and only because a concern is open.
        $this->get('/record7/round/person/'.$report->client_id.'/welfare')->assertOk();

        $this->post('/record7/round/person/'.$report->client_id.'/welfare', [
            'resolution_type' => 'located_and_well',
            'note' => 'She was in the garden with her daughter.',
        ])->assertRedirect('/record7/round/person/'.$report->client_id);

        $this->assertFalse(
            $registry->conditionActive($key, $round->service_id),
            'Somebody found her and said so.'
        );

        $check = WelfareCheck::where('administration_id', $report->id)->firstOrFail();

        // Everything the evidence has to identify.
        $this->assertSame('located_and_well', $check->resolution_type);
        $this->assertSame($this->user('noah.williams')->id, (int) $check->recorded_by_user_id);
        $this->assertSame($report->client_id, (int) $check->client_id);
        $this->assertSame($round->service_id, (int) $check->service_id);
        $this->assertNotNull($check->occurred_at);
        $this->assertLessThan(120, abs($check->occurred_at->diffInSeconds(now())));

        // The original report is untouched.
        $original = Administration::findOrFail($report->id);
        $this->assertSame('person_unavailable', $original->outcome);
        $this->assertSame('not_found_in_service', $original->reason_code);

        // And nothing was labelled safeguarding on anybody's behalf.
        $this->assertStringNotContainsStringIgnoringCase(
            'safeguarding',
            (string) $check->resolution_type.' '.(string) $check->note
        );
    }

    /** A note on its own is not an answer. */
    public function test_a_welfare_check_needs_a_structured_resolution_not_just_a_note(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->post('/record7/round/person/'.$report->client_id.'/welfare', [
            'note' => 'Had a look around, seems fine.',
        ])->assertSessionHasErrors('resolution_type');

        // And an invented resolution is refused too.
        $this->post('/record7/round/person/'.$report->client_id.'/welfare', [
            'resolution_type' => 'probably_fine',
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, WelfareCheck::where('administration_id', $report->id)->count());

        $this->assertTrue(app(IssueRegistry::class)->conditionActive(
            'welfare_check:'.$report->id, $round->service_id
        ));
    }

    /** Evidence from another house or organisation cannot answer it. */
    public function test_evidence_from_elsewhere_cannot_resolve_the_concern(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        // A check written straight at the table, claiming another house.
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::connection('record7')->table('record7_welfare_checks')->insert([
            'reference' => 'TEST-W-CROSS-'.$report->id,
            'organisation_id' => $this->rosewood()->organisation_id,
            'service_id' => $this->rosewood()->id,
            'client_id' => $report->client_id,
            'administration_id' => $report->id,
            'resolution_type' => 'located_and_well',
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** A welfare check is permanent, like everything else clinical here. */
    public function test_a_welfare_check_cannot_be_rewritten_or_deleted(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->post('/record7/round/person/'.$report->client_id.'/welfare', [
            'resolution_type' => 'located_and_well',
        ]);

        $check = WelfareCheck::where('administration_id', $report->id)->firstOrFail();

        try {
            $check->resolution_type = 'whereabouts_confirmed_elsewhere';
            $check->save();
            $this->fail('A welfare check must not be rewritable.');
        } catch (\RuntimeException $expected) {
            $this->assertStringContainsString('permanent', $expected->getMessage());
        }

        try {
            DB::connection('record7')->table('record7_welfare_checks')
                ->where('id', $check->id)
                ->update(['resolution_type' => 'whereabouts_confirmed_elsewhere']);
            $this->fail('The database must refuse it too.');
        } catch (\Illuminate\Database\QueryException $expected) {
            $this->assertStringContainsString('permanent record', $expected->getMessage());
        }
    }

    /**
     * A known whereabouts is itself an answer — but only a KNOWN one.
     *
     * "active" means we believe they are here, which is the very thing in
     * doubt. Any other status names where they actually are.
     */
    public function test_welfare_attention_clears_when_their_whereabouts_become_known(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();
        $registry = app(IssueRegistry::class);
        $key = 'welfare_check:'.$report->id;

        $this->assertTrue($registry->conditionActive($key, $round->service_id));

        // It turns out she had gone to hospital. That is an answer.
        Client::where('id', $report->client_id)->update(['status' => 'in_hospital']);

        $this->assertFalse($registry->conditionActive($key, $round->service_id));
    }

    /**
     * And a manager cannot make it go away by ticking it.
     *
     * The condition is derived from the clinical record, so acknowledging,
     * owning, escalating or closing the paperwork leaves it exactly as active
     * as it was.
     */
    public function test_a_manager_cannot_close_a_live_welfare_concern(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'person_unavailable',
            'reason_code' => 'not_found_in_service',
        ]);

        $report = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $issue = IssueState::where('issue_type', 'welfare_check')
            ->where('source_id', $report->id)->firstOrFail();

        $issue->forceFill([
            'closed_at' => now(),
            'closure_reason' => 'Closing this off.',
        ])->save();

        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'welfare_check:'.$report->id, $round->service_id
            ),
            'Closing the paperwork does not mean somebody found her.'
        );
    }

    /* ── Self-administration monitoring ─────────────────────────────────── */

    public function test_a_fully_self_managed_medicine_needs_no_staff_outcome(): void
    {
        $round = $this->enterRound();

        $client = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'active')->firstOrFail();

        $prescription = Prescription::create([
            'reference' => 'TEST-P-SELF-NONE-'.uniqid(),
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('is_controlled', false)->firstOrFail()->id,
            'dose' => 'One tablet',
            'route' => 'Oral',
            'support_type' => 'self_administered',
            'self_administration_monitoring' => 'none',
            'frequency_text' => 'Once a day',
            'kind' => 'scheduled',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        $dose = ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ])->fresh(['prescription.medicine', 'administration']);

        $this->assertTrue($prescription->isFullySelfManaged());

        $eligibility = $this->recorder()->eligibility($dose, $client);

        $this->assertFalse($eligibility['allowed']);
        $this->assertSame('self_managed', $eligibility['code']);
    }

    public function test_existing_self_administered_prescriptions_were_backfilled(): void
    {
        $unset = Prescription::where('support_type', 'self_administered')
            ->whereNull('self_administration_monitoring')->count();

        $this->assertSame(0, $unset, 'Every self-administered prescription needs a stated arrangement.');

        $wrongly = Prescription::where('support_type', '!=', 'self_administered')
            ->whereNotNull('self_administration_monitoring')->count();

        $this->assertSame(0, $wrongly, 'The field applies only to self-administration.');
    }

    /* ── Found in the browser ───────────────────────────────────────────── */

    /**
     * A fully self-managed medicine is not work the round is waiting on.
     *
     * It was being counted as outstanding, so every round containing one would
     * have stayed permanently incomplete — and "3 remaining" that never reaches
     * zero teaches people that the number means nothing.
     */
    public function test_a_fully_self_managed_dose_is_not_outstanding_round_work(): void
    {
        $round = $this->enterRound();

        $client = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'active')->firstOrFail();

        $before = collect(app(RoundQueue::class)->forRound($round))
            ->firstWhere('clientId', $client->id)['outstandingCount'] ?? 0;

        $prescription = Prescription::create([
            'reference' => 'TEST-SELFNONE-'.uniqid(),
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('is_controlled', false)->firstOrFail()->id,
            'dose' => 'One tablet', 'route' => 'Oral',
            'support_type' => 'self_administered',
            'self_administration_monitoring' => 'none',
            'frequency_text' => 'Once a day', 'kind' => 'scheduled', 'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
        ]);

        ScheduledDose::create([
            'prescription_id' => $prescription->id,
            'client_id' => $client->id,
            'service_id' => $this->oakwood()->id,
            'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            'slot' => $round->slot,
            'grace_minutes' => 60,
        ]);

        $after = collect(app(RoundQueue::class)->forRound($round))
            ->firstWhere('clientId', $client->id)['outstandingCount'] ?? 0;

        $this->assertSame(
            $before,
            $after,
            'A medicine the person manages entirely is not a job the round is waiting on.'
        );

        // And the person's own view says so rather than offering an action.
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('selfManaged', true);

        $this->assertNotNull($item);
        $this->assertFalse($item['canBeGiven']);
    }

    /** A monitored self-administration IS still outstanding work. */
    public function test_a_monitored_self_administration_still_needs_an_answer(): void
    {
        $round = $this->enterRound();

        $prescription = Prescription::where('support_type', 'self_administered')
            ->where('self_administration_monitoring', 'check_and_record')
            ->whereIn('client_id', Client::where('service_id', $this->oakwood()->id)->select('id'))
            ->where('kind', 'scheduled')
            ->firstOrFail();

        $this->assertFalse($prescription->isFullySelfManaged());
        $this->assertTrue($prescription->requiresSelfAdministrationCheck());
    }

    /**
     * The answer that STANDS, not whichever row came back first.
     *
     * A dose carrying a refusal and a later accepted re-offer was showing only
     * the refusal — so a worker who had just given a medicine saw it listed as
     * refused, which is the sort of thing that gets it given twice.
     */
    public function test_a_re_offered_dose_shows_the_answer_that_stands(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'refused', 'reason_code' => 'client_declined',
        ]);
        $refusal = Administration::where('scheduled_dose_id', $dose->id)->firstOrFail();

        $this->post($this->reofferUrl($dose), ['outcome' => 'given']);

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        // What stands now.
        $this->assertSame('given', $item['recordedCode']);

        // And what it followed, so the refusal is not hidden.
        $this->assertNotNull($item['reofferedFrom']);
        $this->assertSame('Refused', $item['reofferedFrom']['word']);
        $this->assertSame('They said no', $item['reofferedFrom']['reason']);

        // Both rows are still there.
        $this->assertSame(2, Administration::where('scheduled_dose_id', $dose->id)->count());
        $this->assertSame('refused', Administration::findOrFail($refusal->id)->outcome);
    }

    /** The reason is shown, not just the outcome. */
    public function test_a_recorded_outcome_shows_its_reason(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->post($this->url($dose), [
            'outcome' => 'not_available',
            'reason_code' => 'awaiting_delivery',
            'notes' => 'Pharmacy said Tuesday.',
        ]);

        $client = Client::findOrFail($dose->client_id);
        $item = collect(app(RoundPersonView::class)->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $dose->id);

        $this->assertSame('Waiting on a delivery', $item['recordedReason']);
        $this->assertSame('Pharmacy said Tuesday.', $item['recordedNotes']);

        // The stored code never reaches the screen.
        $this->assertStringNotContainsString('awaiting_delivery', (string) $item['recordedReason']);
    }

    /* ── The screen ─────────────────────────────────────────────────────── */

    public function test_the_outcome_screen_keeps_identity_and_allergies_in_view(): void
    {
        $round = $this->enterRound();

        $margaret = Client::where('service_id', $this->oakwood()->id)
            ->whereHas('allergies')->firstOrFail();

        $dose = $this->openDose($round, $margaret->id);

        $body = $this->get($this->url($dose))->assertOk()->getContent();

        $this->assertStringContainsString(e($margaret->full_name), $body);
        $this->assertStringContainsString(
            e($margaret->allergies()->first()->substance),
            $body,
            'Allergies must stay visible while an outcome is chosen.'
        );
    }

    /**
     * A screen must never offer an action the server would refuse.
     *
     * Found in the browser: Dennis's prompted medicine — which Section 2.2
     * rightly declines — opened the whole outcome form, and the recorder would
     * only have thrown once a reason and a note had been filled in. A round has
     * less time than anything else in the building.
     */
    public function test_a_medicine_that_cannot_be_answered_here_says_so_first(): void
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

        $this->get($this->url($dose))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where(
                'blockedReason',
                'This is an as-required medicine. Recording it needs the as-required '
                .'workflow, which is not built yet.'
            ));
    }

    /** A medicine that CAN be answered is not blocked by that guard. */
    public function test_an_ordinary_medicine_is_not_blocked_from_the_outcome_screen(): void
    {
        $round = $this->enterRound();
        $dose = $this->openDose($round);

        $this->get($this->url($dose))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('blockedReason', null));
    }

    /** Nothing is preselected, and the four outcomes stay four. */
    public function test_the_screen_preselects_nothing_and_keeps_the_outcomes_distinct(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/RoundOutcome.jsx'));

        $this->assertStringContainsString("useState(null)", $page);
        $this->assertStringContainsString("outcome: ''", $page);

        // No single collapsed "not given".
        $this->assertStringNotContainsString('Not given at all', $page);
    }
}
