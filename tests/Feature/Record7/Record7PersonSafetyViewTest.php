<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Medicine;
use App\Models\Record7\Organisation;
use App\Models\Record7\Prescription;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\UserCompetency;
use App\Services\Record7\RoundEntry;
use App\Services\Record7\RoundPersonView;
use App\Services\Record7\RoundQueue;

/**
 * Section 2.1 — the check before a medicine is given.
 *
 * These attack the things that would actually hurt somebody: the wrong person
 * opened, a medicine from another round shown as due, a support arrangement
 * flattened into a lie, an absent person quietly marked done, and anything at
 * all being recordable before Section 2.2 exists to record it properly.
 */
class Record7PersonSafetyViewTest extends Record7TestCase
{
    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    private function rosewood(): Service
    {
        return $this->house('Rosewood House');
    }

    private function safetyView(): RoundPersonView
    {
        return app(RoundPersonView::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    /** Enter the house and open the round, returning it. */
    private function enterRound(string $username = 'noah.williams', string $house = 'Oakwood House')
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
        $this->post('/record7/round/start');

        return app(RoundEntry::class)->openRoundFor($this->house($house)->id);
    }

    private function personIn($round, string $reference): Client
    {
        return Client::where('reference', $reference)->firstOrFail();
    }

    /* ── 1. The journey ─────────────────────────────────────────────────── */

    public function test_an_authorised_worker_opens_a_person_from_their_round(): void
    {
        $round = $this->enterRound();

        $queue = app(RoundQueue::class)->forRound($round);
        $this->assertNotEmpty($queue);

        $clientId = $queue[0]['clientId'];

        $body = $this->get('/record7/round/person/'.$clientId)->assertOk()->getContent();

        $client = Client::findOrFail($clientId);

        // Identity comes from the client record, not from anything passed in.
        $this->assertStringContainsString(e($client->full_name), $body);
        $this->assertStringContainsString('Oakwood House', $body);
        $this->assertStringContainsString($round->slot, $body);
    }

    public function test_the_round_context_survives_opening_a_person(): void
    {
        $round = $this->enterRound();
        $clientId = app(RoundQueue::class)->forRound($round)[0]['clientId'];

        $this->get('/record7/round/person/'.$clientId)->assertOk();

        // The round is still the same round, still open, still this house.
        $after = app(RoundEntry::class)->openRoundFor($this->oakwood()->id);

        $this->assertSame($round->id, $after->id);
        $this->assertNull($after->closed_at);

        // And going back lands on it.
        $this->get('/record7/round')->assertOk();
    }

    /* ── 2 to 4. Isolation ──────────────────────────────────────────────── */

    public function test_a_person_from_another_house_cannot_be_opened(): void
    {
        $round = $this->enterRound('olivia.carter', 'Oakwood House');

        $elsewhere = Client::where('service_id', $this->rosewood()->id)->firstOrFail();

        $this->get('/record7/round/person/'.$elsewhere->id)->assertNotFound();

        // And the service refuses it directly, not merely the route.
        $this->assertNull($this->safetyView()->resolve($round, $elsewhere->id));
    }

    public function test_a_person_from_another_organisation_cannot_be_opened(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-2-1',
            'legal_name' => 'Selby Care Ltd',
            'display_name' => 'Selby Care',
            'name_normalised' => 'selby care',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-2-1',
            'organisation_id' => $rival->id,
            'name' => 'Selby Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Selby',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-2-1',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Edith Marsden',
            'date_of_birth' => '1936-02-02',
            'status' => 'active',
        ]);

        $round = $this->enterRound();

        $this->get('/record7/round/person/'.$theirClient->id)->assertNotFound();
        $this->assertNull($this->safetyView()->resolve($round, $theirClient->id));
    }

    /**
     * Somebody who genuinely lives here, but is not in THIS round.
     *
     * The dangerous middle case: the house is right, the organisation is right,
     * and opening them anyway would show a set of medicines that are not part
     * of the round the worker is doing.
     */
    public function test_a_person_who_lives_here_but_is_not_in_this_round_cannot_be_opened(): void
    {
        $round = $this->enterRound();

        $inRound = array_column(app(RoundQueue::class)->forRound($round), 'clientId');

        $outsider = Client::where('service_id', $this->oakwood()->id)
            ->whereNotIn('id', $inRound)
            ->first();

        if (! $outsider) {
            // Everybody in the house is in this round, so make somebody who is
            // not: a client with no dose in this slot.
            $outsider = Client::create([
                'reference' => 'TEST-C-NOTINROUND',
                'organisation_id' => $this->oakwood()->organisation_id,
                'service_id' => $this->oakwood()->id,
                'full_name' => 'Peter Anselm',
                'date_of_birth' => '1950-05-05',
                'status' => 'active',
            ]);
        }

        $this->get('/record7/round/person/'.$outsider->id)->assertNotFound();
        $this->assertNull($this->safetyView()->resolve($round, $outsider->id));
    }

    public function test_a_nonexistent_id_does_not_fall_back_to_anybody(): void
    {
        $this->enterRound();

        $this->get('/record7/round/person/99999999')->assertNotFound();
    }

    /* ── 5. Authority ───────────────────────────────────────────────────── */

    public function test_losing_competency_blocks_the_safety_view_without_losing_the_round(): void
    {
        $round = $this->enterRound();
        $clientId = app(RoundQueue::class)->forRound($round)[0]['clientId'];

        $this->get('/record7/round/person/'.$clientId)->assertOk();

        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $this->get('/record7/round/person/'.$clientId)->assertForbidden();

        // The round survives untouched.
        $round->refresh();
        $this->assertNull($round->closed_at);
    }

    public function test_a_reviewer_who_may_oversee_cannot_open_the_safety_view(): void
    {
        $round = $this->enterRound();
        $clientId = app(RoundQueue::class)->forRound($round)[0]['clientId'];

        $this->signIn('maya.thompson');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->get('/record7/round/person/'.$clientId)->assertForbidden();
    }

    /* ── 6 and 7. Identity and safety come from the records ─────────────── */

    public function test_identity_is_read_from_the_client_record(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-001');

        $view = $this->safetyView()->forPerson($round, $client);

        $this->assertSame($client->full_name, $view['person']['fullName']);
        $this->assertSame($client->room_name, $view['person']['room']);
        $this->assertSame($client->date_of_birth->format('j F Y'), $view['person']['bornOn']);
        $this->assertSame($client->reference, $view['person']['reference']);
    }

    public function test_allergies_come_from_the_allergy_records_and_are_worst_first(): void
    {
        $round = $this->enterRound();

        // Joyce's peanut allergy is life threatening; Margaret's is severe.
        $joyce = $this->personIn($round, 'OAK-C-005');
        $view = $this->safetyView()->forPerson($round, $joyce);

        $recorded = $joyce->allergies()->pluck('substance')->all();

        $this->assertNotEmpty($view['safety']['allergies']);
        $this->assertSame('recorded', $view['safety']['allergiesState']);

        foreach ($view['safety']['allergies'] as $allergy) {
            $this->assertContains($allergy['substance'], $recorded, 'No allergy may be invented.');
            $this->assertNotEmpty($allergy['severityWord'], 'Severity is a word, never a colour alone.');
        }

        // Worst first.
        $severities = array_column($view['safety']['allergies'], 'severity');
        $rank = ['life_threatening' => 3, 'severe' => 2, 'moderate' => 1, 'mild' => 0];
        $ranks = array_map(fn ($s) => $rank[$s], $severities);
        $sorted = $ranks;
        rsort($sorted);

        $this->assertSame($sorted, $ranks);
    }

    /**
     * "None recorded" must never read as "none".
     */
    public function test_a_person_with_no_allergies_gets_a_truthful_state_not_an_all_clear(): void
    {
        $round = $this->enterRound();

        $terry = $this->personIn($round, 'OAK-C-002');
        $terry->allergies()->delete();

        $view = $this->safetyView()->forPerson($round, $terry->fresh());

        $this->assertSame([], $view['safety']['allergies']);
        $this->assertSame('none_recorded', $view['safety']['allergiesState']);
    }

    public function test_record7_does_not_pretend_to_hold_photographs_or_sensitivities(): void
    {
        $round = $this->enterRound();
        $view = $this->safetyView()->forPerson($round, $this->personIn($round, 'OAK-C-001'));

        // Neither concept exists in the schema. Saying so is the honest answer;
        // a placeholder would suggest one had been looked for and not found.
        $this->assertNull($view['person']['photo']);
        $this->assertSame('not_held', $view['person']['photoState']);
        $this->assertSame('not_separately_held', $view['safety']['sensitivitiesState']);
    }

    /* ── 8 to 9. The medicines are this round's ─────────────────────────── */

    public function test_medicine_details_come_from_the_scheduled_records(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-001');

        $view = $this->safetyView()->forPerson($round, $client);
        $this->assertNotEmpty($view['medicines']);

        foreach ($view['medicines'] as $item) {
            $dose = ScheduledDose::findOrFail($item['doseId']);
            $prescription = $dose->prescription;
            $medicine = $prescription->medicine;

            $this->assertSame($medicine->name, $item['name']);
            $this->assertSame($medicine->strength, $item['strength']);
            $this->assertSame($medicine->form, $item['form']);
            $this->assertSame($prescription->dose, $item['dose']);
            $this->assertSame($prescription->route, $item['route']);
            $this->assertSame($dose->due_at->format('H:i'), $item['dueAt']);
        }
    }

    public function test_only_this_rounds_medicines_are_shown(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-005');

        $view = $this->safetyView()->forPerson($round, $client);

        foreach ($view['medicines'] as $item) {
            $dose = ScheduledDose::findOrFail($item['doseId']);

            $this->assertSame($round->slot, $dose->slot, 'Another slot is another round.');
            $this->assertSame(
                $round->round_date->toDateString(),
                $dose->due_at->toDateString(),
                'Another day is another round.'
            );
            $this->assertSame((int) $round->service_id, (int) $dose->service_id);
            $this->assertSame($client->id, (int) $dose->client_id);
        }

        // She has doses in other slots, so the filter is doing real work.
        $this->assertGreaterThan(
            count($view['medicines']),
            ScheduledDose::where('client_id', $client->id)->count()
        );
    }

    /* ── 10 to 12. Support type, per medicine ───────────────────────────── */

    public function test_support_type_is_resolved_per_medicine(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-005');

        $view = $this->safetyView()->forPerson($round, $client);

        foreach ($view['medicines'] as $item) {
            $expected = ScheduledDose::findOrFail($item['doseId'])->prescription->support_type;

            $this->assertSame($expected, $item['support']);
            $this->assertNotEmpty($item['supportWord']);
            $this->assertNotEmpty($item['supportMeaning'], 'A worker must be told what it means.');
        }
    }

    /**
     * The correction Section 2.0 could only summarise.
     *
     * Joyce is handed one medicine and assisted with another. Section 2.0 said
     * "Mixed — see each medicine"; here each medicine says which it is, and
     * nothing is collapsed into a single label that would be wrong about one.
     */
    public function test_mixed_support_types_stay_distinct(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-005');

        $view = $this->safetyView()->forPerson($round, $client);

        $types = array_unique(array_column($view['medicines'], 'support'));

        $this->assertGreaterThan(
            1,
            count($types),
            'This person is the mixed-support case; the fixture must keep them mixed.'
        );

        // And nothing anywhere in the payload says "mixed".
        foreach ($view['medicines'] as $item) {
            $this->assertNotSame('mixed', $item['support']);
            $this->assertStringNotContainsStringIgnoringCase('mixed', (string) $item['supportWord']);
        }
    }

    public function test_a_self_administered_medicine_stays_visible_and_is_labelled(): void
    {
        $round = $this->enterRound();

        $selfAdministered = Prescription::where('support_type', 'self_administered')
            ->whereIn('client_id', Client::where('service_id', $this->oakwood()->id)->select('id'))
            ->first();

        if (! $selfAdministered) {
            $this->markTestSkipped('No self-administered medicine in the fixture.');
        }

        // Give it a scheduled dose in this round so the round view must carry it.
        ScheduledDose::firstOrCreate(
            [
                'prescription_id' => $selfAdministered->id,
                'due_at' => $round->round_date->copy()->setTimeFromTimeString('08:00'),
            ],
            [
                'client_id' => $selfAdministered->client_id,
                'service_id' => $round->service_id,
                'slot' => $round->slot,
                'grace_minutes' => 60,
            ]
        );

        $client = Client::findOrFail($selfAdministered->client_id);
        $view = $this->safetyView()->forPerson($round, $client);

        $item = collect($view['medicines'])
            ->firstWhere('name', $selfAdministered->medicine->name);

        $this->assertNotNull($item, 'Staff not handing it over does not remove it from the round.');
        $this->assertTrue($item['selfAdministered']);
        $this->assertSame('Self-administered', $item['supportWord']);
        $this->assertFalse($item['recorded'], 'Nothing is auto-recorded for it.');
    }

    /* ── 13 to 14. Callum, away ─────────────────────────────────────────── */

    public function test_an_absent_persons_medication_stays_visible_with_nothing_invented(): void
    {
        $round = $this->enterRound();

        $callum = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'in_hospital')->firstOrFail();

        $view = $this->safetyView()->forPerson($round, $callum);

        // Still there, still scheduled, still unanswered.
        $this->assertNotEmpty($view['medicines'], 'The obligation does not vanish with the person.');
        $this->assertFalse($view['person']['available']);
        $this->assertNotEmpty($view['person']['statusWord']);

        foreach ($view['medicines'] as $item) {
            $this->assertNotEmpty($item['dueAt'], 'The original scheduled time stays visible.');
            $this->assertFalse($item['recorded']);
            $this->assertNull($item['recordedOutcome']);
        }

        // And absolutely nothing has been written against him.
        $this->assertSame(0, Administration::where('client_id', $callum->id)->count());
    }

    public function test_an_absent_person_can_still_be_opened_from_the_round(): void
    {
        $round = $this->enterRound();

        $callum = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'in_hospital')->firstOrFail();

        $this->get('/record7/round/person/'.$callum->id)->assertOk();
    }

    /* ── 15 to 16. Time-sensitive, and honest gaps ──────────────────────── */

    public function test_a_time_sensitive_medicine_is_identified_from_the_prescription(): void
    {
        $round = $this->enterRound();
        $terry = $this->personIn($round, 'OAK-C-002');

        $view = $this->safetyView()->forPerson($round, $terry);

        $critical = array_filter($view['medicines'], fn ($m) => $m['timeSensitive']);
        $this->assertNotEmpty($critical, 'Terry is the fixture time-critical case.');

        foreach ($critical as $item) {
            $this->assertTrue(
                (bool) ScheduledDose::findOrFail($item['doseId'])->prescription->is_time_critical
            );
        }
    }

    public function test_missing_medicine_details_are_named_rather_than_left_blank(): void
    {
        $round = $this->enterRound();
        $client = $this->personIn($round, 'OAK-C-001');

        $dose = ScheduledDose::where('client_id', $client->id)
            ->where('slot', $round->slot)
            ->whereDate('due_at', $round->round_date->toDateString())
            ->firstOrFail();

        // Take the route away, as an incomplete record would.
        $dose->prescription->update(['route' => '']);
        Medicine::where('id', $dose->prescription->medicine_id)->update(['strength' => null]);

        $view = $this->safetyView()->forPerson($round, $client->fresh());
        $item = collect($view['medicines'])->firstWhere('doseId', $dose->id);

        $this->assertContains('route', $item['missing']);
        $this->assertContains('strength', $item['missing']);
    }

    /* ── 17 to 19. Navigation, house safety, and no recording ───────────── */

    public function test_next_and_previous_never_leave_the_round(): void
    {
        $round = $this->enterRound();
        $queue = app(RoundQueue::class)->forRound($round);
        $ids = array_column($queue, 'clientId');

        foreach ($ids as $id) {
            $neighbours = $this->safetyView()->neighbours($queue, $id);

            foreach (['previous', 'next'] as $side) {
                if ($neighbours[$side]) {
                    $this->assertContains(
                        $neighbours[$side]['clientId'],
                        $ids,
                        'Neighbours are drawn from the authorised queue only.'
                    );
                }
            }
        }
    }

    public function test_switching_house_cannot_leak_the_previously_selected_person(): void
    {
        // Olivia holds both houses.
        $round = $this->enterRound('olivia.carter', 'Oakwood House');
        $oakwoodPerson = app(RoundQueue::class)->forRound($round)[0]['clientId'];

        $this->get('/record7/round/person/'.$oakwoodPerson)->assertOk();

        $name = Client::findOrFail($oakwoodPerson)->full_name;

        // She walks into the other house.
        $this->post('/record7/houses', ['house_id' => $this->rosewood()->id]);
        $this->post('/record7/round/start');

        $response = $this->get('/record7/round/person/'.$oakwoodPerson);

        // Two honest outcomes, depending on whether Rosewood has a round open
        // to be standing in. Not found if it does, and sent back to Today if it
        // does not. What must NEVER happen is the Oakwood person rendering,
        // and that is the thing asserted rather than a particular status code.
        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertStringNotContainsString(e($name), $response->getContent());

        if (app(RoundEntry::class)->openRoundFor($this->rosewood()->id)) {
            $response->assertNotFound();
        } else {
            $response->assertRedirect('/record7/today');
        }
    }

    /* ── Found by looking at it: a recorded outcome is not a good outcome ── */

    /**
     * The screen was painting every recorded outcome in the success colour,
     * because it only knew that SOMETHING had been recorded. A medicine that
     * never left the trolley read exactly like a completed administration.
     */
    public function test_the_view_carries_the_outcome_code_not_only_its_word(): void
    {
        $round = $this->enterRound();

        // Find one that is actually IN this round rather than taking the first
        // in the table and skipping when it is not — a safety test that quietly
        // skips is worth nothing.
        $recorded = Administration::whereNotIn('outcome', ['given'])
            ->whereIn(
                'scheduled_dose_id',
                ScheduledDose::where('service_id', $round->service_id)
                    ->where('slot', $round->slot)
                    ->whereDate('due_at', $round->round_date->toDateString())
                    ->select('id')
            )->first();

        if (! $recorded) {
            $this->markTestSkipped('No non-given outcome inside this round.');
        }

        $client = Client::findOrFail($recorded->client_id);

        $item = collect($this->safetyView()->forPerson($round, $client)['medicines'])
            ->firstWhere('doseId', $recorded->scheduled_dose_id);

        $this->assertNotNull($item);
        $this->assertSame(
            $recorded->outcome,
            $item['recordedCode'],
            'Without the code the screen cannot tell a refusal from an administration.'
        );
    }

    /**
     * A rule about how it must LOOK, held where the looking is decided.
     *
     * Not a style preference: an omission rendered in the success colour is a
     * clinical misread, and the mistake is one line of JSX away at all times.
     */
    public function test_only_a_completed_administration_is_painted_as_success(): void
    {
        $item = file_get_contents(
            resource_path('js/record7/components/MedicineRoundItem.jsx')
        );

        foreach (['refused', 'withheld', 'not_available', 'missed'] as $outcome) {
            $this->assertMatchesRegularExpression(
                '/'.$outcome.":\s*\{\s*tone:\s*'(warning|error)'/",
                $item,
                $outcome.' is not a completed administration and must not be painted as one.'
            );
        }

        foreach (['given', 'self_administered'] as $outcome) {
            $this->assertMatchesRegularExpression(
                '/'.$outcome.":\s*\{\s*tone:\s*'success'/",
                $item
            );
        }
    }

    /**
     * "Not available" sits next to a person whose own availability is a
     * first-class fact on this screen — "In hospital", "At home". The bare word
     * is genuinely ambiguous, and it is the medicine that was missing.
     */
    public function test_an_unavailable_medicine_says_it_is_the_medicine(): void
    {
        // The wording moved to the service in Section 2.2 so the pill and the
        // line beneath it cannot word the same fact differently. The rule is
        // unchanged; only its home is.
        $service = file_get_contents(app_path('Services/Record7/RoundPersonView.php'));

        $this->assertStringContainsString("'Medicine not available'", $service);
        $this->assertStringContainsString("'Taken themselves'", $service);
    }

    /**
     * "Given" beside an assisted arrangement claims too much.
     *
     * The worker steadied a hand; they did not hand it over. The stored outcome
     * is still `given` — staff were physically part of the administration — but
     * a record that a manager reads back months later must not say a worker
     * administered something the person took themselves with help.
     */
    public function test_an_assisted_medicine_is_not_worded_as_plainly_given(): void
    {
        $service = file_get_contents(app_path('Services/Record7/RoundPersonView.php'));

        $this->assertStringContainsString("'Given with help'", $service);
        $this->assertMatchesRegularExpression(
            "/support\s*===\s*'assisted'/",
            $service,
            'The wording has to depend on the arrangement, not only on the outcome.'
        );
    }

    /**
     * Recording "Given" against a medicine somebody is authorised to take
     * themselves is a false record of who did what. Record7 has a separate
     * outcome, and the fixture has to use it or the screen is verified against
     * a lie.
     */
    public function test_a_self_administered_medicine_is_never_recorded_as_staff_given(): void
    {
        $wrong = Administration::where('outcome', 'given')
            ->whereIn(
                'prescription_id',
                Prescription::where('support_type', 'self_administered')->select('id')
            )->count();

        $this->assertSame(
            0,
            $wrong,
            'A self-administered medicine recorded as "Given" says a worker handed it over.'
        );
    }

    /** Under an hour in minutes, past that in hours — as Today already says it. */
    public function test_lateness_is_readable_rather_than_a_raw_minute_count(): void
    {
        $round = $this->enterRound();
        $client = Client::findOrFail(
            app(RoundQueue::class)->forRound($round)[0]['clientId']
        );

        foreach ($this->safetyView()->forPerson($round, $client)['medicines'] as $item) {
            if (! $item['late']) {
                continue;
            }

            if ($item['minutesLate'] >= 60) {
                $this->assertMatchesRegularExpression(
                    '/^\d+h( \d+m)? late$/',
                    $item['latePhrase'],
                    'Six hours late should not be written as 355 minutes.'
                );
            } else {
                $this->assertMatchesRegularExpression('/^\d+ min late$/', $item['latePhrase']);
            }
        }
    }

    /**
     * A preferred name earns its line only when it says something.
     *
     * "Terence Boyle, known as Terry" helps a worker who has only heard him
     * called Terry. "Callum Fraser, known as Callum" is the same name printed
     * twice, and a screen that repeats itself teaches people to skim.
     */
    public function test_a_preferred_name_is_shown_only_when_it_differs(): void
    {
        $round = $this->enterRound();

        foreach (app(RoundQueue::class)->forRound($round) as $row) {
            $client = Client::findOrFail($row['clientId']);
            $person = $this->safetyView()->forPerson($round, $client)['person'];

            if ($person['preferredName'] === null) {
                continue;
            }

            $firstName = explode(' ', $client->full_name)[0];

            $this->assertNotEqualsIgnoringCase(
                $firstName,
                $person['preferredName'],
                $client->full_name.' is not "known as" their own first name.'
            );
        }
    }

    /**
     * An absent person's screen must not read as "nothing to do here".
     *
     * Two facts pull in opposite directions: this service is not giving the
     * medicine, and the planned dose still has to be answered for. Said
     * together, or left to a free-text note about medicines being "on hold",
     * the first swallows the second — and a planned dose with no outcome is the
     * gap nobody can explain months later.
     */
    public function test_an_absent_person_is_never_told_that_nothing_needs_recording(): void
    {
        $round = $this->enterRound();

        $away = collect(app(RoundQueue::class)->forRound($round))
            ->map(fn ($row) => Client::find($row['clientId']))
            ->first(fn ($client) => $client && ! $client->isAvailable());

        if (! $away) {
            $this->markTestSkipped('Nobody is away in this round.');
        }

        $view = $this->safetyView()->forPerson($round, $away);

        // The obligation is still live: a planned dose with nothing recorded.
        $this->assertTrue(
            collect($view['medicines'])->contains(fn ($m) => ! $m['recorded']),
            'The fixture must keep an unanswered dose for an absent person.'
        );

        // Nothing anywhere on their screen may suggest the dose has lapsed.
        $words = strtolower(implode(' ', array_filter([
            $view['person']['supportNote'],
            $view['person']['statusWord'],
        ])));

        foreach (['on hold', 'no action', 'nothing to do', 'cancelled', 'suspended'] as $phrase) {
            $this->assertStringNotContainsString(
                $phrase,
                $words,
                "An absent person's note must not imply the planned dose has gone away."
            );
        }

        // And the panel that carries the message states BOTH halves.
        $panel = file_get_contents(
            resource_path('js/record7/components/PersonAvailability.jsx')
        );

        $this->assertStringContainsString('not giving their medicines', $panel);
        $this->assertStringContainsString('must', $panel);
        $this->assertStringContainsString('outcome recorded', $panel);
        $this->assertStringContainsString('r7-away__obligation', $panel);
    }

    /**
     * The whole point of Section 2.1: it reads.
     */
    public function test_there_is_no_route_or_control_that_records_an_outcome(): void
    {
        $round = $this->enterRound();
        $clientId = app(RoundQueue::class)->forRound($round)[0]['clientId'];

        $before = Administration::count();

        $this->get('/record7/round/person/'.$clientId)->assertOk();

        $this->assertSame($before, Administration::count());

        // No POST exists on this path at all.
        $this->post('/record7/round/person/'.$clientId)->assertStatus(405);

        // And the page submits nothing.
        $page = file_get_contents(resource_path('js/R7Pages/RoundPerson.jsx'));

        foreach (['router.post', 'router.put', 'router.patch', 'router.delete', '<form'] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $page,
                'Section 2.1 is the check before giving. Recording begins at 2.2.'
            );
        }

        // Nor does any component it renders.
        foreach (['MedicineRoundItem', 'AllergyWarning', 'SupportType', 'PersonAvailability'] as $component) {
            $source = file_get_contents(resource_path("js/record7/components/{$component}.jsx"));

            $this->assertStringNotContainsString('router.post', $source);
            $this->assertStringNotContainsString('<form', $source);
        }
    }

    public function test_the_service_itself_has_no_write_method(): void
    {
        $source = file_get_contents(app_path('Services/Record7/RoundPersonView.php'));

        foreach (['->save()', '::create(', '->update(', '->delete(', 'firstOrCreate'] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $source,
                'RoundPersonView reads. It must not be able to write anything at all.'
            );
        }
    }
}
