<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\Round;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Services\Record7\ShiftBoard;
use Illuminate\Support\Carbon;

/**
 * Section 1.1 — the support worker's Today screen.
 *
 * These are the things that would actually hurt somebody if they broke:
 * seeing another house's medicines, a late time-critical dose sitting below a
 * routine one, a suspended prescription still appearing in the round, a
 * medicines record that can be quietly rewritten, and two people opening the
 * same round and recording against different copies of it.
 */
class Record7TodayTest extends Record7TestCase
{
    private function board(): ShiftBoard
    {
        return app(ShiftBoard::class);
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('service_id', $this->oakwood()->id)->exists()) {
            $this->markTestSkipped(
                'Seed the Section 1 fixture first: RECORD7_DB_DATABASE=record7_test '
                .'RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section1Seeder'
            );
        }
    }

    /* ── Noah gets in, and lands where he should ────────────────────────── */

    public function test_noah_reaches_today_without_choosing_a_house(): void
    {
        // He holds one house, so Section 0 opens it for him rather than asking
        // a question with one answer. Today has to work from that entry.
        $this->signIn('noah.williams');

        $this->get('/record7/houses')->assertRedirect('/record7');
        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);
    }

    public function test_the_six_bands_appear_in_the_agreed_order(): void
    {
        // Inertia ships props, not markup, so the rendered response carries no
        // headings to search. The order is a property of the component, and the
        // component is where it is checked.
        $page = file_get_contents(resource_path('js/R7Pages/Today.jsx'));

        preg_match_all('/<BoardSection\s[^>]*?title="([^"]+)"/s', $page, $matches);

        $this->assertSame(
            [
                'Shift handover',
                'Needs attention',
                'Shift overview',
                'People due',
                'My tasks and PRN follow-ups',
                'Recently completed',
            ],
            $matches[1],
            'The bands are out of order, or one is missing. The order is clinical, not '
            .'cosmetic: it is the order the questions arrive in on a shift, and on a '
            .'phone it is also the scroll order.'
        );
    }

    public function test_the_bands_are_never_placed_side_by_side(): void
    {
        // A two-column layout at a wide width would silently change which band
        // is read first, and the order is the whole design.
        $css = file_get_contents(resource_path('css/record7/r7.css'));

        preg_match('/\.r7-root \.r7-board \{(.*?)\}/s', $css, $block);

        $this->assertNotEmpty($block, 'The .r7-board rule is missing.');
        $this->assertStringContainsString('flex-direction: column', $block[1]);
        $this->assertStringNotContainsString('grid-template-columns', $block[1]);
    }

    /* ── One house, and only one house ──────────────────────────────────── */

    public function test_today_never_shows_a_client_from_another_house(): void
    {
        $rosewood = $this->house('Rosewood House');

        $stranger = Client::create([
            'reference' => 'ROSE-TEST-001',
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'full_name' => 'Harriet Bexley',
            'preferred_name' => 'Harriet',
            'date_of_birth' => '1950-01-01',
            'status' => 'active',
        ]);

        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->get('/record7')
            ->assertOk()
            ->assertDontSee($stranger->full_name, false)
            ->assertDontSee('Rosewood House', false);
    }

    public function test_every_board_query_is_bound_to_the_one_house(): void
    {
        $oakwood = $this->oakwood()->id;

        // Willow House, not Rosewood: Rosewood was the empty house until
        // Section 1.2 gave it its own people, and a test whose meaning depends
        // on a house STAYING empty is a test that quietly stops proving
        // anything. This one asserts against a house with no Section 1 data.
        $rosewood = $this->house('Willow House')->id;
        $this->assertSame([], $this->board()->peopleDue($rosewood));
        $this->assertSame([], $this->board()->needsAttention($rosewood));
        $this->assertSame(0, $this->board()->recentlyCompleted($rosewood)['count']);
        $this->assertNull($this->board()->handover($rosewood));

        // And Oakwood does, so the assertions above are not passing by accident.
        $this->assertGreaterThan(0, $this->board()->recentlyCompleted($oakwood)['count']);
        $this->assertNotNull($this->board()->handover($oakwood));
    }

    /* ── The order things need doing in ─────────────────────────────────── */

    public function test_a_late_time_critical_dose_comes_before_everything_else(): void
    {
        $items = $this->board()->needsAttention($this->oakwood()->id);

        $this->assertNotEmpty($items, 'The fixture guarantees a late time-critical dose.');
        $this->assertSame(
            'late_time_critical',
            $items[0]['kind'],
            'A late Parkinson\'s dose must sort above a refusal and above a follow-up. '
            .'Sorting this list by time alone is how the dangerous one ends up third.'
        );
    }

    public function test_a_refusal_that_was_later_given_stops_being_chased(): void
    {
        $oakwood = $this->oakwood()->id;

        $refusal = Administration::where('service_id', $oakwood)
            ->where('outcome', 'refused')
            ->firstOrFail();

        // SECTION 2.3 CORRECTION.
        //
        // This test used to pass for the wrong reason. The fixture's refusal is
        // for a medicine given FOUR TIMES A DAY, so the teatime dose — a
        // different planned obligation entirely — was closing the morning's
        // refusal before this test even began. Dennis was dropping off the
        // chase list because somebody gave him a later tablet, not because
        // anybody went back to the one he turned down.
        //
        // The refusal is therefore live at the start now, and only an accepted
        // re-offer of THAT dose closes it.
        // The board only chases the last twelve hours, and the fixture's refusal
        // is anchored to this morning — so late in the day it falls out of the
        // window and this test would be measuring the clock rather than the
        // rule. A refusal is written INSIDE the window instead: administrations
        // are permanent and the database rightly refuses to have one back-dated.
        // Its own planned dose, because one dose may carry exactly one original
        // outcome and the fixture's is already answered.
        $freshDose = \App\Models\Record7\ScheduledDose::create([
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $oakwood,
            'due_at' => now()->subHours(2),
            'slot' => 'Teatime',
            'grace_minutes' => 30,
        ]);

        $refusal = Administration::create([
            'reference' => 'TEST-REFUSAL-'.uniqid(),
            'scheduled_dose_id' => $freshDose->id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $oakwood,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'administered_at' => now()->subHours(2),
        ]);

        $before = collect($this->board()->needsAttention($oakwood))
            ->where('kind', 'refused')->count();

        $this->assertGreaterThan(0, $before, 'The refusal has not been answered yet.');

        // An unrelated later dose of the same medicine is not an answer to it.
        Administration::create([
            'reference' => 'TEST-UNRELATED-'.$refusal->id,
            'scheduled_dose_id' => null,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $oakwood,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'administered_at' => $refusal->administered_at->copy()->addMinutes(20),
        ]);

        $this->assertSame(
            $before,
            collect($this->board()->needsAttention($oakwood))->where('kind', 'refused')->count(),
            'Another dose of the same medicine does not answer for this refusal.'
        );

        // Somebody went back and offered THIS dose again, and he took it.
        Administration::create([
            'reference' => 'TEST-REOFFER-'.$refusal->id,
            'scheduled_dose_id' => $refusal->scheduled_dose_id,
            'prescription_id' => $refusal->prescription_id,
            'client_id' => $refusal->client_id,
            'service_id' => $oakwood,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'reoffer_of_administration_id' => $refusal->id,
            'administered_at' => $refusal->administered_at->copy()->addMinutes(35),
        ]);

        $this->assertSame(
            $before - 1,
            collect($this->board()->needsAttention($oakwood))->where('kind', 'refused')->count(),
            'A refusal that was re-offered and accepted is closed. Leaving it on the '
            .'list trains people to ignore the list.'
        );
    }

    public function test_people_due_is_now_and_not_the_whole_day(): void
    {
        $oakwood = $this->oakwood()->id;
        $people = $this->board()->peopleDue($oakwood);

        foreach ($people as $person) {
            $this->assertTrue(
                $person['isLate'] || $person['slot'] !== null,
                'Everybody on People due is either late or in the round now open.'
            );
        }

        // The rest of the day is counted rather than given a card each.
        $later = $this->board()->laterToday($oakwood);

        foreach ($later as $round) {
            $this->assertArrayHasKey('doses', $round);
            $this->assertArrayHasKey('people', $round);
        }
    }

    /* ── Somebody who is not there ──────────────────────────────────────── */

    /**
     * REVERSED, deliberately, as part of Section 2.0.
     *
     * This used to assert that somebody in hospital had no doses planned and
     * never appeared. That was the wrong model: the prescriber has not stopped
     * his medicine, so the dose is still planned, and removing it made the
     * obligation silently cease to exist — nobody would ever be asked why it
     * was not given.
     *
     * He now appears, marked as away, with nothing recorded on his behalf.
     */
    public function test_a_client_in_hospital_still_has_their_planned_dose(): void
    {
        $oakwood = $this->oakwood()->id;

        $callum = Client::where('service_id', $oakwood)
            ->where('status', 'in_hospital')
            ->firstOrFail();

        $this->assertGreaterThan(
            0,
            ScheduledDose::where('client_id', $callum->id)->count(),
            'An absent person keeps the doses that were planned for them.'
        );

        // And nothing has been decided for him.
        $this->assertSame(
            0,
            \App\Models\Record7\Administration::where('client_id', $callum->id)->count()
        );

        // Where he appears at all, it says where he is — otherwise a support
        // worker is sent to an empty flat.
        $people = collect($this->board()->peopleDue($oakwood, Carbon::today()->setTime(9, 30)));
        $entry = $people->firstWhere('fullName', $callum->full_name);

        if ($entry) {
            $this->assertFalse($entry['available']);
            $this->assertNotEmpty($entry['whereabouts']);
        }
    }

    public function test_a_severe_allergy_is_shown_before_the_door_is_knocked_on(): void
    {
        $people = $this->board()->peopleDue($this->oakwood()->id, Carbon::today()->setTime(21, 45));

        $withAllergies = collect($people)->filter(fn ($person) => $person['criticalAllergies'] !== []);

        $this->assertNotEmpty(
            $withAllergies,
            'A severe or life-threatening allergy belongs on the card you read before '
            .'you walk in, not on a screen two taps further on.'
        );

        foreach ($withAllergies as $person) {
            foreach ($person['criticalAllergies'] as $allergy) {
                // The severity is a word. This gets read in greyscale, by people
                // who are colour-blind, and aloud down a telephone.
                $this->assertNotEmpty($allergy['severity']);
                $this->assertIsString($allergy['severity']);
            }
        }
    }

    /* ── Today names no medicines ───────────────────────────────────────── */

    /**
     * The dashboard answers "what do I need to do right now", and a drug name
     * does not help answer it.
     *
     * This reads the ACTUAL RESPONSE, so it catches a medicine name arriving
     * through any prop, not just the ones anybody thought to check.
     *
     * ONE EXEMPTION, AND IT IS DELIBERATE: what a colleague wrote in a handover
     * note. "Lorazepam given at 3:10 and it did settle her" is one human
     * telling another what happened during the night. Stripping the drug out of
     * somebody's sentence would not reduce clutter, it would break the
     * handover — and it is not the product rendering a field from the medicines
     * record, which is what this rule is actually about.
     */
    public function test_the_dashboard_never_names_a_medicine(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $body = $this->get('/record7')->assertOk()->getContent();

        // Take the free text out first, so what remains is only what Record7
        // itself chose to render.
        $handover = $this->board()->handover($this->oakwood()->id);

        foreach (array_column($handover['notes'], 'note') as $prose) {
            $body = str_replace(e($prose), '', $body);
        }

        if ($handover['summary']) {
            $body = str_replace(e($handover['summary']), '', $body);
        }

        $names = \App\Models\Record7\Medicine::pluck('name')->all();
        $this->assertNotEmpty($names, 'The fixture must have medicines for this to prove anything.');

        $leaked = array_values(array_filter(
            $names,
            fn ($name) => str_contains($body, $name)
        ));

        $this->assertSame(
            [],
            $leaked,
            'Today is naming medicines: '.implode(', ', $leaked).'. Names, strengths, '
            .'routes and doses belong on the round screen, where somebody is holding '
            .'the box — not on the screen that decides who to walk to.'
        );
    }

    public function test_people_due_carries_a_count_rather_than_a_list(): void
    {
        $people = $this->board()->peopleDue($this->oakwood()->id);

        $this->assertNotEmpty($people);

        foreach ($people as $person) {
            $this->assertArrayHasKey('medicineCount', $person);
            $this->assertIsInt($person['medicineCount']);
            $this->assertArrayNotHasKey('medicines', $person);
        }
    }

    public function test_needs_attention_says_what_to_do_next(): void
    {
        $items = $this->board()->needsAttention($this->oakwood()->id);

        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            // The point of the row. A problem with no next action is a worry,
            // not a task.
            $this->assertNotEmpty($item['next'], 'Every attention row must say what to do next.');
            $this->assertNotEmpty($item['problem']);
            $this->assertArrayNotHasKey('medicine', $item);
        }
    }

    public function test_the_handover_shows_at_most_three_notes_and_counts_the_rest(): void
    {
        $handover = $this->board()->handover($this->oakwood()->id);

        $this->assertLessThanOrEqual(
            3,
            count($handover['notes']),
            'A briefing somebody reads standing in a corridor is three lines long.'
        );

        // Nothing is silently dropped — you are told what was left out.
        $this->assertArrayHasKey('moreCount', $handover);
        $this->assertGreaterThan(0, $handover['moreCount']);
    }

    /* ── Confirming the handover ────────────────────────────────────────── */

    public function test_reading_the_handover_is_recorded_for_that_person(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $handover = \App\Models\Record7\Handover::where('service_id', $this->oakwood()->id)
            ->firstOrFail();

        $this->assertNull($this->board()->handover($this->oakwood()->id, $this->user('noah.williams'))['readAt']);

        $this->post('/record7/handover/read', ['handover_id' => $handover->id])
            ->assertRedirect('/record7');

        $this->assertNotNull(
            $this->board()->handover($this->oakwood()->id, $this->user('noah.williams'))['readAt'],
            'Confirming you have read the handover is what turns notes on a screen '
            .'into a transfer of responsibility, so it has to be recorded.'
        );
    }

    public function test_confirming_twice_does_not_create_a_second_record(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $handover = \App\Models\Record7\Handover::where('service_id', $this->oakwood()->id)
            ->firstOrFail();

        $this->post('/record7/handover/read', ['handover_id' => $handover->id]);
        $this->post('/record7/handover/read', ['handover_id' => $handover->id]);

        $this->assertSame(
            1,
            \App\Models\Record7\HandoverRead::where('handover_id', $handover->id)
                ->where('user_id', $this->user('noah.williams')->id)
                ->count()
        );
    }

    public function test_a_handover_from_another_house_cannot_be_acknowledged(): void
    {
        $rosewood = $this->house('Rosewood House');

        $elsewhere = \App\Models\Record7\Handover::create([
            'service_id' => $rosewood->id,
            'written_by_user_id' => $this->user('olivia.carter')->id,
            'shift' => 'Night shift',
            'covers_from' => now()->subDay(),
            'covers_to' => now(),
        ]);

        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->post('/record7/handover/read', ['handover_id' => $elsewhere->id])
            ->assertNotFound();

        $this->assertSame(0, \App\Models\Record7\HandoverRead::where('handover_id', $elsewhere->id)->count());
    }

    /* ── A job is not a competency ──────────────────────────────────────── */

    /**
     * Noah is employed as a Support Worker. He can give medicines because he
     * holds the permission for this house and his competency is current — not
     * because his job has been renamed after the thing he is signed off to do.
     *
     * Collapsing the three into one job title is tempting and wrong: an expired
     * competency then cannot take the ability away without also appearing to
     * demote the person.
     */
    public function test_noah_is_employed_as_a_support_worker(): void
    {
        $noah = $this->user('noah.williams');

        $this->assertSame(
            'Support Worker',
            $noah->primaryRole()?->name,
            'His visible role is what he is employed as. "Medication Administrator" '
            .'describes a competency, not a job.'
        );
    }

    public function test_he_may_administer_through_permission_and_competency(): void
    {
        $noah = $this->user('noah.williams');
        $oakwood = $this->oakwood()->id;
        $policy = app(\App\Services\Record7\AccessPolicy::class);

        // His ROLE does not carry it — that is the point.
        $this->assertNotContains(
            'administer_medication',
            $noah->primaryRole()->permissions()->pluck('code')->all(),
            'The Support Worker role must not grant administration by itself, or the '
            .'permission layer is doing nothing.'
        );

        // The explicit per-house allow does, and only for the house he works in.
        $this->assertTrue($policy->allows($noah, 'administer_medication', $oakwood));
    }

    public function test_letting_the_competency_lapse_removes_the_ability_but_not_the_job(): void
    {
        $noah = $this->user('noah.williams');
        $oakwood = $this->oakwood()->id;
        $policy = app(\App\Services\Record7\AccessPolicy::class);

        $this->assertTrue($policy->allows($noah, 'administer_medication', $oakwood));

        $gate = \App\Models\Record7\CompetencyType::where('gates_permission', 'administer_medication')
            ->firstOrFail();

        \App\Models\Record7\UserCompetency::where('user_id', $noah->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $noah->refresh();

        $this->assertFalse(
            $policy->allows($noah, 'administer_medication', $oakwood),
            'An expired medication competency must stop him administering.'
        );

        $this->assertSame(
            'Support Worker',
            $noah->primaryRole()?->name,
            'And it must not change what he is employed as. That is the whole reason '
            .'role, permission and competency are three separate things.'
        );
    }

    public function test_the_round_action_follows_the_competency_and_not_the_job_title(): void
    {
        $gate = \App\Models\Record7\CompetencyType::where('gates_permission', 'administer_medication')
            ->firstOrFail();

        \App\Models\Record7\UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->post('/record7/round/start')->assertForbidden();
    }

    /* ── Starting the round ─────────────────────────────────────────────── */

    public function test_starting_the_round_twice_joins_it_rather_than_duplicating_it(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        // Section 2.0 moved the destination: starting a round now lands in the
        // round workspace rather than back on Today. Joining is still the point
        // of this test, and it still holds.
        $this->post('/record7/round/start')->assertRedirect('/record7/round');
        $this->post('/record7/round/start')->assertRedirect('/record7/round');

        $rounds = Round::where('service_id', $this->oakwood()->id)
            ->whereDate('round_date', now()->toDateString())
            ->count();

        $this->assertSame(
            1,
            $rounds,
            'Two people opening the morning round within a minute of each other is an '
            .'ordinary event. The second must join the first, not open a rival round.'
        );
    }

    public function test_the_round_shows_as_in_progress_once_it_is_started(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->assertSame('not_started', $this->board()->round($this->oakwood()->id)['state']);

        $this->post('/record7/round/start');

        $this->assertSame('in_progress', $this->board()->round($this->oakwood()->id)['state']);
    }

    public function test_somebody_who_may_not_record_cannot_start_a_round(): void
    {
        // Maya reviews; she does not administer.
        $this->signIn('maya.thompson');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $this->post('/record7/round/start')->assertForbidden();

        $this->assertSame(
            0,
            Round::where('service_id', $this->oakwood()->id)
                ->whereDate('round_date', now()->toDateString())
                ->count()
        );
    }

    public function test_a_reviewer_still_sees_the_screen_but_is_told_they_cannot_record(): void
    {
        $this->signIn('maya.thompson');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $body = $this->get('/record7')->assertOk()->getContent();

        // Seeing what is outstanding is part of reviewing, so the board is still
        // sent. What she may not do is said in words, rather than by a button
        // that silently does nothing.
        $this->assertStringContainsString('&quot;record&quot;:false', $body);
        $this->assertStringContainsString('review access only', $body);

        // And she is still sent the shift itself, or the refusal would be the
        // only thing on the screen.
        $this->assertStringContainsString('&quot;handover&quot;', $body);
        $this->assertStringContainsString('&quot;attention&quot;', $body);
    }

    /* ── The record is permanent ────────────────────────────────────────── */

    public function test_an_administration_cannot_be_rewritten(): void
    {
        $administration = Administration::where('service_id', $this->oakwood()->id)
            ->where('outcome', 'given')
            ->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/permanent record/');

        // A genuinely different outcome. Turning a recorded "given" into a
        // "refused" after the fact is exactly the rewrite this forbids.
        $administration->update(['outcome' => 'refused']);
    }

    public function test_an_administration_cannot_be_deleted(): void
    {
        $administration = Administration::where('service_id', $this->oakwood()->id)->firstOrFail();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cannot be deleted/');

        $administration->delete();
    }

    public function test_the_database_refuses_a_rewrite_even_when_the_model_is_bypassed(): void
    {
        $administration = Administration::where('service_id', $this->oakwood()->id)->firstOrFail();

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Straight through the query builder, past every Eloquent guard. The
        // guarantee has to hold here too, or it is not a guarantee.
        \Illuminate\Support\Facades\DB::connection('record7')
            ->table('record7_administrations')
            ->where('id', $administration->id)
            ->update(['outcome' => 'missed']);
    }

    /* ── Nothing a manager sees ─────────────────────────────────────────── */

    public function test_today_carries_no_organisation_wide_or_manager_figures(): void
    {
        $this->signIn('noah.williams');
        $this->post('/record7/houses', ['house_id' => $this->oakwood()->id]);

        $body = $this->get('/record7')->getContent();

        foreach ([
            'Compliance',
            'compliance rate',
            'across all houses',
            'Organisation total',
            'Rosewood',
            'Meadow View',
            'Willow House',
        ] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $body,
                "Today is one house and one shift. '{$forbidden}' belongs on the manager "
                .'dashboard, which is a different screen for a different job.'
            );
        }
    }

    /* ── Lateness ───────────────────────────────────────────────────────── */

    public function test_a_dose_is_not_late_until_its_own_grace_has_run_out(): void
    {
        $timeCritical = Prescription::where('is_time_critical', true)
            ->whereIn('client_id', Client::where('service_id', $this->oakwood()->id)->select('id'))
            ->firstOrFail();

        $dose = ScheduledDose::where('prescription_id', $timeCritical->id)->firstOrFail();

        // A Parkinson's dose gets minutes, not the round's hour and a half.
        $this->assertLessThanOrEqual(30, $dose->grace_minutes);

        $this->assertFalse($dose->isLate($dose->due_at->copy()->addMinutes(5)));
        $this->assertTrue($dose->isLate($dose->due_at->copy()->addMinutes(31)));
    }
}
