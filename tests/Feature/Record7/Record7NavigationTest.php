<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Client;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ScheduledDose;

/**
 * The staff navigation and hand-off pass.
 *
 * WHY THIS FILE EXISTS.
 * Sections 2.4, 2.5 and 2.7 all shipped complete, tested and unreachable. The
 * as-required workflow, the controlled-drug workflow and the whole of stock
 * could be exercised by every test in the suite and by nobody holding a phone,
 * because no screen linked to them. A test that calls a route directly cannot
 * see that: it supplies the address itself, which is the one thing a support
 * worker cannot do.
 *
 * So these tests assert the DOORS rather than the rooms. What the round offers
 * when it refuses a medicine, what Today offers when it names a follow-up, who
 * is shown the way to stock, and where each workflow puts somebody down
 * afterwards.
 *
 * Nothing here changes a clinical rule. Every refusal these tests navigate
 * around is still a refusal; the screens they reach still enforce everything
 * they enforced before, and the last two tests exist to prove exactly that —
 * that being shown a door is not the same as being given the keys.
 */
class Record7NavigationTest extends Record7TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    /** Props from a rendered Record7 screen. */
    private function props(string $url): array
    {
        $response = $this->get($url);

        $response->assertOk();

        return $response->viewData('page')['props'];
    }

    /** Open the round the fixture is standing in, as somebody who may record. */
    private function inRound(string $username = 'olivia.carter', string $house = 'Rosewood House'): void
    {
        $this->signInAt($username, $house);
        $this->post('/record7/round/start');
    }

    /** Margaret's scheduled controlled drug: the dose that had nowhere to go. */
    private function controlledDose(): ScheduledDose
    {
        $prescription = Prescription::with('medicine', 'client')
            ->whereHas('medicine', fn ($q) => $q->where('is_controlled', true))
            ->where('kind', 'scheduled')
            ->firstOrFail();

        return ScheduledDose::where('prescription_id', $prescription->id)
            ->orderBy('due_at')
            ->firstOrFail();
    }

    /* ── The round hands off what it cannot record ───────────────────────── */

    /**
     * THE DEFECT THIS PASS EXISTS FOR.
     *
     * A scheduled controlled drug appears in the round, is correctly refused
     * because it needs a witness and a register entry, and used to leave the
     * worker with an explanation and no way forward. The refusal stands; the
     * way forward is now beside it.
     */
    public function test_a_controlled_dose_in_the_round_offers_the_way_to_the_register(): void
    {
        $dose = $this->controlledDose();
        $person = $dose->prescription->client;

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $props = $this->props('/record7/round/person/'.$person->id);

        $medicine = collect($props['medicines'])->firstWhere('doseId', $dose->id);

        $this->assertNotNull($medicine, 'The controlled dose is not in the round at all.');

        // The refusal is unchanged. This pass did not make it recordable here.
        $this->assertFalse($medicine['canBeGiven']);
        $this->assertSame('witness_required', $medicine['blockedCode']);

        // What is new: the prescription travels, so a link can be built.
        $this->assertSame(
            $dose->prescription_id,
            $medicine['prescriptionId'],
            'Without the prescription there is nothing to link to.'
        );

        $this->assertArrayHasKey('controlled', $props['urls']);

        // The template the page fills, resolved the way the page resolves it.
        $built = str_replace(
            ['__ID__', '__PRESCRIPTION__'],
            [$person->id, $dose->prescription_id],
            (string) $props['urls']['controlled']
        );

        $this->assertStringContainsString(
            '/record7/person/'.$person->id.'/controlled/'.$dose->prescription_id,
            $built,
            'The hand-off does not resolve to this person and this prescription.'
        );
    }

    /** And the address it builds is real, not merely well-formed. */
    public function test_the_controlled_hand_off_reaches_a_working_screen(): void
    {
        $dose = $this->controlledDose();
        $person = $dose->prescription->client;

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $this->get('/record7/person/'.$person->id.'/controlled/'.$dose->prescription_id)
            ->assertOk();
    }

    /** The as-required refusal gets its door too. */
    public function test_the_person_screen_offers_the_way_to_as_required_medicines(): void
    {
        $person = $this->personWithAnAsRequiredMedicine();

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $props = $this->props('/record7/round/person/'.$person->id);

        $this->assertArrayHasKey('asRequired', $props['urls']);
        $this->assertStringContainsString('/prn', (string) $props['urls']['asRequired']);
    }

    /**
     * THE GAP THE FIRST PASS LEFT.
     *
     * The hand-off above only fires when a dose is REFUSED as as-required, and
     * that almost never happens: an as-required medicine has no scheduled dose,
     * so it is not in the round to be refused. The person screen therefore still
     * had no way to reach their as-required medicines — found by walking the
     * journey in a browser, not by any test, because every test knew the address
     * already.
     */
    public function test_a_person_with_as_required_medicines_can_reach_them_from_their_round_screen(): void
    {
        $person = $this->personWithAnAsRequiredMedicine();

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $props = $this->props('/record7/round/person/'.$person->id);

        $this->assertNotNull(
            $props['asRequired'],
            'Their as-required medicines are unreachable from the screen a worker stands on.'
        );

        $this->assertGreaterThan(0, $props['asRequired']['count']);
        $this->assertSame(
            route('record7.prn', ['client' => $person->id]),
            $props['asRequired']['url']
        );
    }

    /** And somebody with none is offered nothing, rather than an empty list. */
    public function test_a_person_with_no_as_required_medicines_is_offered_no_link(): void
    {
        $person = Client::whereDoesntHave(
            'prescriptions', fn ($q) => $q->where('kind', 'prn')->where('status', 'active')
        )->whereIn('id', ScheduledDose::whereDate('due_at', now()->toDateString())
            ->distinct()->pluck('client_id'))->first();

        if (! $person) {
            $this->markTestSkipped('Everybody in the round has an as-required medicine.');
        }

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $this->assertNull(
            $this->props('/record7/round/person/'.$person->id)['asRequired'],
            'An empty list is worse than no link: it reads as "there is something here".'
        );
    }

    /**
     * The confirm screen carries the same two doors.
     *
     * Somebody who opened the medicine before reading why it is refused must
     * not have to go back a screen to find the way on — that is how a dose
     * ends up recorded in the wrong place.
     */
    public function test_the_confirm_screen_carries_the_same_hand_offs(): void
    {
        $dose = $this->controlledDose();
        $person = $dose->prescription->client;

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $props = $this->props(
            '/record7/round/person/'.$person->id.'/medicine/'.$dose->id
        );

        $this->assertNotNull($props['urls']['controlled'] ?? null);
        $this->assertNotNull($props['urls']['asRequired'] ?? null);
        $this->assertSame('witness_required', $props['medicine']['blockedCode']);
    }

    /* ── Today hands off the follow-up it asks about ─────────────────────── */

    public function test_today_can_open_the_follow_up_it_names(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertArrayHasKey('prnFollowUp', $props['urls']);

        $followUp = PrnFollowUp::where('outcome', 'pending')->first();

        if (! $followUp) {
            $this->markTestSkipped('No outstanding follow-up in the fixture.');
        }

        // Whichever band carries it, the id has to travel or the row cannot
        // become a link.
        $onBoard = collect($props['attention'])->firstWhere('kind', 'follow_up');
        $asTask = collect($props['tasks'])->first();

        $this->assertTrue(
            ($onBoard['followUpId'] ?? null) !== null || ($asTask['id'] ?? null) !== null,
            'Today names an unanswered follow-up and carries no way to open it.'
        );
    }

    public function test_the_follow_up_screen_opens_from_the_address_today_builds(): void
    {
        $followUp = PrnFollowUp::where('outcome', 'pending')->first();

        if (! $followUp) {
            $this->markTestSkipped('No outstanding follow-up in the fixture.');
        }

        $this->signInAt('olivia.carter', $this->houseNameFor($followUp->client));

        $this->get('/record7/prn/follow-up/'.$followUp->id)->assertOk();
    }

    /* ── Coming back ─────────────────────────────────────────────────────── */

    /**
     * A worker who finished on an out-of-round screen used to be left there.
     * On a shift that means going back to Today and starting the round again to
     * reach the next person, which is how somebody loses their place in a list
     * of six and gives one of them nothing.
     */
    public function test_the_as_required_screen_returns_to_the_round_when_one_is_open(): void
    {
        $this->inRound();

        $person = Client::whereHas(
            'prescriptions', fn ($q) => $q->where('kind', 'prn')
        )->where('service_id', $this->house('Rosewood House')->id)->firstOrFail();

        $props = $this->props('/record7/person/'.$person->id.'/prn');

        $this->assertSame(
            route('record7.round.person', ['client' => $person->id]),
            $props['urls']['back'],
            'Finishing an as-required medicine leaves the worker outside the round.'
        );
    }

    /** And says Today, honestly, when there is no round to go back to. */
    public function test_the_as_required_screen_returns_to_today_when_no_round_is_open(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $person = Client::whereHas(
            'prescriptions', fn ($q) => $q->where('kind', 'prn')
        )->where('service_id', $this->house('Rosewood House')->id)->firstOrFail();

        $props = $this->props('/record7/person/'.$person->id.'/prn');

        $this->assertSame(route('record7.today'), $props['urls']['back']);
        $this->assertSame('Today', $props['urls']['backLabel']);
    }

    public function test_the_controlled_screen_returns_to_the_round_when_one_is_open(): void
    {
        $dose = $this->controlledDose();
        $person = $dose->prescription->client;

        $this->inRound('olivia.carter', $this->houseNameFor($person));

        $props = $this->props('/record7/person/'.$person->id.'/controlled');

        $this->assertSame(
            route('record7.round.person', ['client' => $person->id]),
            $props['urls']['back']
        );
    }

    /* ── Stock is offered to the people whose job it is ──────────────────── */

    public function test_stock_is_offered_to_somebody_with_stock_authority(): void
    {
        $this->signInAt('sarah.ahmed', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertTrue($props['can']['viewStock']);
        $this->assertSame(route('record7.stock'), $props['urls']['stock']);
    }

    public function test_stock_is_not_put_in_front_of_a_worker_with_no_stock_duty(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $props = $this->props('/record7');

        $this->assertFalse(
            $props['can']['viewStock'],
            'A support worker with no stock duty is being offered a stock menu.'
        );
    }

    /**
     * AND THE MENU IS A COURTESY, NOT THE CONTROL.
     *
     * Hiding the way in is a kindness to somebody whose job it is not. It is
     * not a permission, and this pass did not turn it into one: the writes are
     * refused on their own authority exactly as they were before.
     */
    public function test_hiding_the_stock_menu_did_not_become_the_only_protection(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $balance = \App\Models\Record7\StockBalance::where(
            'service_id', $this->house('Rosewood House')->id
        )->firstOrFail();

        // She was shown no door. She types the address anyway.
        $this->post('/record7/stock/'.$balance->id.'/count', [
            'counted_quantity' => 5,
        ])->assertStatus(403);

        $this->post('/record7/stock/'.$balance->id.'/receipt', [
            'quantity' => 5,
        ])->assertStatus(403);
    }

    /* ── The dead control ────────────────────────────────────────────────── */

    /**
     * "People" pointed at Today — the screen it was already on. Record7 has no
     * people index: the round IS the list of people, and a person's own screen
     * is resolved through it.
     */
    public function test_the_people_control_leads_to_the_round_and_not_to_itself(): void
    {
        $this->signInAt('olivia.carter', 'Rosewood House');

        $page = file_get_contents(resource_path('js/R7Pages/Today.jsx'));

        $this->assertStringNotContainsString(
            "label: 'People', href: '/record7'",
            $page,
            'The People control still points at the screen it is on.'
        );

        $props = $this->props('/record7');

        $this->assertSame(route('record7.round'), $props['urls']['round']);
        $this->assertSame(
            route('record7.round.person', ['client' => '__ID__']),
            $props['urls']['person']
        );
    }

    /* ── Nothing tells staff a built thing is unbuilt ────────────────────── */

    /**
     * The round refused a controlled drug with "Witnessed administration is not
     * built yet" for three sections after it was built. A worker reading that
     * has been told, by the product, to stop.
     */
    public function test_no_screen_tells_staff_a_built_workflow_is_not_built(): void
    {
        $sources = array_merge(
            glob(app_path('Services/Record7/*.php')),
            glob(app_path('Http/Controllers/Record7/*.php')),
            glob(resource_path('js/R7Pages/*.jsx')),
            glob(resource_path('js/record7/components/*.jsx'))
        );

        $offenders = [];

        foreach ($sources as $file) {
            if (str_contains((string) file_get_contents($file), 'not built yet')) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'These still tell a support worker that something built is unbuilt: '
            .implode(', ', $offenders)
        );
    }

    /** Said positively as well: the refusal now names where to go instead. */
    public function test_the_refusals_name_the_screen_that_can_record_it(): void
    {
        $recorder = app(\App\Services\Record7\AdministrationRecorder::class);
        $dose = $this->controlledDose();

        $this->signInAt('olivia.carter', $this->houseNameFor($dose->prescription->client));

        $eligibility = $recorder->eligibility($dose, $dose->prescription->client);

        $this->assertFalse($eligibility['allowed']);
        $this->assertStringContainsString('controlled drug screen', $eligibility['reason']);
        $this->assertStringNotContainsString('not built', $eligibility['reason']);
    }

    /** Which house a person lives in, so a test signs in to the right one. */
    private function houseNameFor(Client $client): string
    {
        return \App\Models\Record7\Service::findOrFail($client->service_id)->name;
    }

    /**
     * Somebody with an as-required medicine who is also IN today's round.
     *
     * Both halves matter. A person with a PRN prescription who has nothing
     * planned in this slot cannot be opened through the round at all — the
     * round is what resolves them — so a test that picked on the prescription
     * alone was asserting against a 404 of its own making.
     */
    private function personWithAnAsRequiredMedicine(): Client
    {
        $inRoundToday = ScheduledDose::whereDate('due_at', now()->toDateString())
            ->distinct()->pluck('client_id');

        $client = Client::whereHas('prescriptions', fn ($q) => $q->where('kind', 'prn'))
            ->whereIn('id', $inRoundToday)
            ->first();

        if (! $client) {
            $this->markTestSkipped('Nobody in the fixture has both a PRN and a dose today.');
        }

        return $client;
    }
}
