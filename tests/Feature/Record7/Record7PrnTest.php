<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\IssueState;
use App\Models\Record7\Medicine;
use App\Models\Record7\Organisation;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\UserCompetency;
use App\Services\Record7\IssueRegistry;
use App\Services\Record7\PrnAdministration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.4 — as-required medicines.
 *
 * These attack the ways a PRN record can hurt somebody: a dose given too soon,
 * one too many, more than the prescription allows, a limit invented where none
 * was written, a refusal locking somebody out of a medicine they never had, a
 * controlled drug slipping through without a witness, and a dose given and
 * never asked about again.
 */
class Record7PrnTest extends Record7TestCase
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

    private function prn(): PrnAdministration
    {
        return app(PrnAdministration::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    /** Signed in and standing in a house — with NO round open. */
    private function signInWithoutRound(string $username = 'noah.williams', string $house = 'Oakwood House'): Service
    {
        $this->signIn($username);
        $service = $this->house($house);
        $this->post('/record7/houses', ['house_id' => $service->id]);

        return $service;
    }

    private function paracetamol(): Prescription
    {
        return Prescription::with('medicine')->where('reference', 'OAK-P-014')->firstOrFail();
    }

    private function salbutamol(): Prescription
    {
        return Prescription::with('medicine')->where('reference', 'OAK-P-006')->firstOrFail();
    }

    private function lorazepam(): Prescription
    {
        return Prescription::with('medicine')->where('reference', 'OAK-P-016')->firstOrFail();
    }

    private function url(Prescription $p): string
    {
        return '/record7/person/'.$p->client_id.'/prn/'.$p->id;
    }

    /**
     * A PRN with no history, carrying the same rule as a fixture one.
     *
     * NOT a clear-out. Administrations are permanent and the database refuses
     * to delete them — rightly. A test that needs to start from zero therefore
     * makes a new prescription rather than erasing somebody's record.
     */
    private function freshPrn(Prescription $like): Prescription
    {
        $copy = Prescription::create([
            'reference' => 'TEST-PRN-'.uniqid(),
            'client_id' => $like->client_id,
            'medicine_id' => $like->medicine_id,
            'dose' => $like->dose,
            'route' => $like->route,
            'support_type' => $like->support_type,
            'self_administration_monitoring' => $like->self_administration_monitoring,
            'frequency_text' => $like->frequency_text,
            'kind' => 'prn',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
            'dose_min' => $like->dose_min,
            'dose_max' => $like->dose_max,
            'dose_unit' => $like->dose_unit,
            'prn_indication' => $like->prn_indication,
            'prn_min_gap_minutes' => $like->prn_min_gap_minutes,

            // Carried deliberately: one test proves this old, overloaded value
            // is present and still decides nothing.
            'prn_max_per_day' => $like->prn_max_per_day,
            'prn_limit_period' => $like->prn_limit_period,
            'prn_max_administrations' => $like->prn_max_administrations,
            'prn_max_total_amount' => $like->prn_max_total_amount,
            'prn_review_after_minutes' => $like->prn_review_after_minutes,
        ]);

        return $copy->fresh('medicine');
    }

    /* ── 1 and 2. Outside a round, and with no scheduled dose ───────────── */

    public function test_prn_is_reachable_with_no_round_open(): void
    {
        $service = $this->signInWithoutRound();
        $paracetamol = $this->paracetamol();

        // Nothing has been started. A person in pain is not a round.
        $this->assertNull(app(\App\Services\Record7\RoundEntry::class)->openRoundFor($service->id));

        $this->get('/record7/person/'.$paracetamol->client_id.'/prn')->assertOk();
        $this->get($this->url($paracetamol))->assertOk();
    }

    public function test_giving_a_prn_creates_no_scheduled_dose(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $dosesBefore = ScheduledDose::where('prescription_id', $paracetamol->id)->count();

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2,
            'observed_reason' => 'reported_pain',
        ])->assertRedirect('/record7/person/'.$paracetamol->client_id.'/prn');

        $this->assertSame(
            $dosesBefore,
            ScheduledDose::where('prescription_id', $paracetamol->id)->count(),
            'A PRN answers a need, not a plan.'
        );

        $given = Administration::where('prescription_id', $paracetamol->id)->firstOrFail();

        $this->assertNull($given->scheduled_dose_id);
        $this->assertNull($given->dose_claim, 'A null claim is what lets a second dose exist.');
    }

    /* ── 3 and 4. The dose actually given ───────────────────────────────── */

    public function test_the_actual_dose_amount_and_unit_are_snapshotted(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2,
            'observed_reason' => 'reported_pain',
            'notes' => 'Lower back, said it was a seven.',
        ]);

        $given = Administration::where('prescription_id', $paracetamol->id)->firstOrFail();

        $this->assertSame(2.0, (float) $given->dose_amount);
        $this->assertSame('tablet', $given->dose_unit);

        // Snapshotted, so it survives the prescription changing later.
        $paracetamol->forceFill(['dose_unit' => 'capsule', 'dose_min' => 1, 'dose_max' => 1])->save();

        $this->assertSame('tablet', Administration::findOrFail($given->id)->dose_unit);
        $this->assertSame(2.0, (float) Administration::findOrFail($given->id)->dose_amount);
    }

    public function test_a_dose_outside_the_permitted_range_is_rejected(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // The prescription says exactly two tablets.
        $this->post($this->url($paracetamol), [
            'dose_amount' => 4,
            'observed_reason' => 'reported_pain',
        ])->assertSessionHas('r7.error');

        $this->post($this->url($paracetamol), [
            'dose_amount' => 1,
            'observed_reason' => 'reported_pain',
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $paracetamol->id)->count());
    }

    /* ── 5, 6 and 7. Interval ───────────────────────────────────────────── */

    public function test_the_minimum_interval_is_enforced_on_the_server(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $this->assertSame(1, Administration::where('prescription_id', $paracetamol->id)->count());

        // Straight away again — four hours have not passed.
        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertSessionHas('r7.error');

        $this->assertSame(
            1,
            Administration::where('prescription_id', $paracetamol->id)->count(),
            'The server refuses it, whatever the screen offered.'
        );

        $described = $this->prn()->describe($paracetamol, Client::findOrFail($paracetamol->client_id));

        $this->assertTrue($described['tooSoon']);
        $this->assertNotNull($described['lastGivenAt']);
        $this->assertNotNull($described['nextAllowedAt']);
    }

    public function test_a_dose_is_permitted_once_the_interval_has_passed(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // One five hours ago; the gap is four.
        Administration::create([
            'reference' => 'TEST-PRN-'.uniqid(),
            'scheduled_dose_id' => null,
            'prescription_id' => $paracetamol->id,
            'client_id' => $paracetamol->client_id,
            'service_id' => $this->oakwood()->id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'given',
            'reason_code' => 'reported_pain',
            'dose_amount' => 2, 'dose_unit' => 'tablet',
            'administered_at' => now()->subHours(5),
        ]);

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertRedirect('/record7/person/'.$paracetamol->client_id.'/prn');

        $this->assertSame(2, Administration::where('prescription_id', $paracetamol->id)->count());
    }

    /* ── 8, 9 and 10. Count and amount are different rules ──────────────── */

    public function test_a_count_limit_and_an_amount_limit_are_separate_rules(): void
    {
        $paracetamol = $this->paracetamol();
        $salbutamol = $this->salbutamol();

        // Dennis's paracetamol states both.
        $this->assertSame(4, (int) $paracetamol->prn_max_administrations);
        $this->assertSame(8.0, (float) $paracetamol->prn_max_total_amount);

        // Aisha's inhaler states ONLY an amount — eight puffs, not eight doses.
        $this->assertNull($salbutamol->prn_max_administrations);
        $this->assertSame(8.0, (float) $salbutamol->prn_max_total_amount);
        $this->assertSame('puff', $salbutamol->dose_unit);
    }

    public function test_the_count_limit_blocks_a_further_dose(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // Four doses spread across the window, all inside the interval rule.
        foreach ([20, 15, 10, 5] as $hoursAgo) {
            $this->givenHoursAgo($paracetamol, 2, $hoursAgo);
        }

        $client = Client::findOrFail($paracetamol->client_id);
        $eligibility = $this->prn()->eligibility($paracetamol, $client);

        $this->assertFalse($eligibility['allowed']);
        $this->assertSame('max_administrations_reached', $eligibility['code']);

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertSessionHas('r7.error');

        $this->assertSame(4, Administration::where('prescription_id', $paracetamol->id)->count());
    }

    public function test_the_amount_limit_blocks_a_dose_that_would_exceed_it(): void
    {
        $this->signInWithoutRound();
        $salbutamol = $this->freshPrn($this->salbutamol());

        // Six puffs already, of a maximum eight. Two more is exactly eight and
        // allowed; three would be nine and is not.
        foreach ([20, 15, 10] as $hoursAgo) {
            $this->givenHoursAgo($salbutamol, 2, $hoursAgo);
        }

        $this->assertSame(6.0, $this->prn()->windowUsage($salbutamol)['amount']);

        $this->assertTrue($this->prn()->checkDose($salbutamol, 2)['allowed']);

        $overshoot = $this->prn()->checkDose($salbutamol, 3);
        $this->assertFalse($overshoot['allowed']);

        // The prescription permits exactly two puffs a time, so three is out of
        // range before the window total is even reached — both guards bite.
        $this->assertContains(
            $overshoot['code'],
            ['above_permitted_dose', 'max_total_amount_reached']
        );

        // A fourth two-puff dose would be ten, over the eight-puff maximum.
        $this->givenHoursAgo($salbutamol, 2, 5);

        $this->assertSame(8.0, $this->prn()->windowUsage($salbutamol)['amount']);

        $next = $this->prn()->checkDose($salbutamol, 2);
        $this->assertFalse($next['allowed']);
        $this->assertSame('max_total_amount_reached', $next['code']);
    }

    public function test_no_maximum_is_invented_where_the_prescription_states_none(): void
    {
        $this->signInWithoutRound();

        $client = Client::where('service_id', $this->oakwood()->id)
            ->where('status', 'active')->firstOrFail();

        // A PRN with an indication and a dose, and no limits at all.
        $bare = Prescription::create([
            'reference' => 'TEST-PRN-BARE-'.uniqid(),
            'client_id' => $client->id,
            'medicine_id' => Medicine::where('is_controlled', false)->firstOrFail()->id,
            'dose' => 'One tablet', 'route' => 'Oral',
            'support_type' => 'staff_administered',
            'frequency_text' => 'When required',
            'kind' => 'prn',
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
            'dose_min' => 1, 'dose_max' => 1, 'dose_unit' => 'tablet',
            'prn_indication' => 'For discomfort',
        ]);

        $this->assertNull($bare->prn_max_administrations);
        $this->assertNull($bare->prn_max_total_amount);
        $this->assertNull($bare->prn_limit_period);
        $this->assertNull($bare->prn_min_gap_minutes);

        // Three in a row, because nothing says otherwise.
        foreach (range(1, 3) as $ignored) {
            $this->post('/record7/person/'.$client->id.'/prn/'.$bare->id, [
                'dose_amount' => 1, 'observed_reason' => 'reported_pain',
            ]);
        }

        $this->assertSame(
            3,
            Administration::where('prescription_id', $bare->id)->count(),
            'A limit nobody wrote down is a limit nobody agreed.'
        );

        // And the window arithmetic reports nothing rather than guessing.
        $usage = $this->prn()->windowUsage($bare);
        $this->assertSame(0, $usage['count']);
        $this->assertNull($usage['from']);
    }

    /** The overloaded old column must not be the safety rule. */
    public function test_the_legacy_max_per_day_column_is_not_used_as_the_limit(): void
    {
        $service = $this->signInWithoutRound();
        $salbutamol = $this->freshPrn($this->salbutamol());

        // Legacy says 8. Read as administrations that would permit eight doses;
        // the real instruction is eight PUFFS, which is four doses.
        $this->assertSame(8, (int) $salbutamol->prn_max_per_day);
        $this->assertNull($salbutamol->prn_max_administrations);

        foreach ([20, 15, 10, 5] as $hoursAgo) {
            $this->givenHoursAgo($salbutamol, 2, $hoursAgo);
        }

        // Four doses = eight puffs. Legacy's "8" would still allow four more.
        $this->assertSame(4, Administration::where('prescription_id', $salbutamol->id)->count());

        $blocked = $this->prn()->checkDose($salbutamol, 2);
        $this->assertFalse($blocked['allowed'], 'The amount limit is what binds, not the old column.');
        $this->assertSame('max_total_amount_reached', $blocked['code']);

        // No source file decides anything from the legacy column.
        $service = file_get_contents(app_path('Services/Record7/PrnAdministration.php'));
        $this->assertStringNotContainsString('prn_max_per_day', $service);
    }

    /* ── Rolling window, across midnight ────────────────────────────────── */

    /**
     * The window rolls; it does not reset at midnight.
     *
     * This is the whole reason the period is explicit. Four doses at ten in the
     * evening and four more after midnight are eight doses in six hours, and a
     * calendar-day allowance calls every one of them permitted. The clock is
     * frozen here so the test measures the RULE rather than the hour it happens
     * to run at.
     */
    public function test_the_window_rolls_rather_than_resetting_at_midnight(): void
    {
        // Six in the morning: late enough that the four-hour interval since the
        // last dose has passed, so the COUNT limit is what answers rather than
        // the interval — and early enough that last night's doses are still
        // inside a rolling twenty-four hours.
        Carbon::setTestNow(Carbon::parse('2026-09-02 06:00:00'));

        try {
            $this->signInWithoutRound();
            $paracetamol = $this->freshPrn($this->paracetamol());

            // Two before midnight, two after — four doses inside six hours.
            foreach (['2026-09-01 22:00:00', '2026-09-01 23:00:00',
                      '2026-09-02 00:30:00', '2026-09-02 01:30:00'] as $at) {
                $this->givenAt($paracetamol, 2, Carbon::parse($at));
            }

            $client = Client::findOrFail($paracetamol->client_id);

            // Rolling sees all four and stops. A calendar day would see the two
            // after midnight and cheerfully allow two more.
            $usage = $this->prn()->windowUsage($paracetamol);

            $this->assertSame(4, $usage['count'], 'Doses before midnight are still within 24 hours.');
            $this->assertSame(8.0, $usage['amount']);

            $this->assertSame(
                'max_administrations_reached',
                $this->prn()->eligibility($paracetamol, $client)['code']
            );

            // And the calendar reading, for contrast — the unsafe answer.
            $paracetamol->forceFill(['prn_limit_period' => 'calendar_day'])->save();

            $this->assertSame(
                2,
                $this->prn()->windowUsage($paracetamol->fresh('medicine'))['count'],
                'A calendar day loses the two doses given before midnight.'
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    /* ── What consumes the allowance ────────────────────────────────────── */

    public function test_a_refusal_does_not_consume_the_allowance(): void
    {
        $this->assertNotConsumed('refused', 'client_declined');
    }

    public function test_a_person_unavailable_record_does_not_consume_the_allowance(): void
    {
        $this->assertNotConsumed('person_unavailable', 'in_hospital');
    }

    public function test_a_medicine_unavailable_record_does_not_consume_the_allowance(): void
    {
        $this->assertNotConsumed('not_available', 'stock_unavailable');
    }

    public function test_a_missed_record_does_not_consume_the_allowance(): void
    {
        $this->assertNotConsumed('missed', 'overlooked');
    }

    /**
     * A record that is not a dose must not start the interval clock, count
     * toward a maximum, or spend any of the permitted amount. Somebody who said
     * no at two and asks at three has had nothing.
     */
    private function assertNotConsumed(string $outcome, string $reason): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        Administration::create([
            'reference' => 'TEST-PRN-'.uniqid(),
            'scheduled_dose_id' => null,
            'prescription_id' => $paracetamol->id,
            'client_id' => $paracetamol->client_id,
            'service_id' => $this->oakwood()->id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => $outcome,
            'reason_code' => $reason,
            'administered_at' => now()->subMinutes(5),
        ]);

        $usage = $this->prn()->windowUsage($paracetamol);

        $this->assertSame(0, $usage['count'], $outcome.' is a record, not a dose.');
        $this->assertSame(0.0, $usage['amount']);
        $this->assertNull($this->prn()->lastGiven($paracetamol));

        // And it does not lock them out five minutes later.
        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertRedirect('/record7/person/'.$paracetamol->client_id.'/prn');

        $this->assertSame(
            1,
            Administration::where('prescription_id', $paracetamol->id)
                ->where('outcome', 'given')->count()
        );
    }

    public function test_the_interval_runs_from_the_last_dose_actually_given(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // Given five hours ago, refused five minutes ago.
        $this->givenHoursAgo($paracetamol, 2, 5);

        Administration::create([
            'reference' => 'TEST-PRN-REF-'.uniqid(),
            'scheduled_dose_id' => null,
            'prescription_id' => $paracetamol->id,
            'client_id' => $paracetamol->client_id,
            'service_id' => $this->oakwood()->id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => 'refused',
            'reason_code' => 'client_declined',
            'administered_at' => now()->subMinutes(5),
        ]);

        $last = $this->prn()->lastGiven($paracetamol);

        $this->assertSame('given', $last->outcome);
        $this->assertTrue($last->administered_at->lessThan(now()->subHours(4)));

        // So another dose is due now, despite the recent refusal.
        $this->assertTrue(
            $this->prn()->eligibility($paracetamol, Client::findOrFail($paracetamol->client_id))['allowed']
        );
    }

    /* ── Indication and follow-up ───────────────────────────────────────── */

    public function test_the_observed_reason_is_kept_apart_from_the_prescribed_indication(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2,
            'observed_reason' => 'observed_distress',
            'notes' => 'Rubbing his back and would not settle.',
        ]);

        $given = Administration::where('prescription_id', $paracetamol->id)->firstOrFail();

        // What the prescription is FOR stays on the prescription.
        $this->assertSame('For back pain', $paracetamol->prn_indication);

        // What was seen THIS time is on the administration, structurally.
        $this->assertSame('observed_distress', $given->reason_code);
        $this->assertSame('Rubbing his back and would not settle.', $given->notes);

        // An invented reason is refused.
        $paracetamol = $this->freshPrn($this->paracetamol());
        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'because_i_felt_like_it',
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $paracetamol->id)->count());
    }

    public function test_the_follow_up_time_comes_from_the_prescription_not_a_hardcoded_hour(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // Change the stated interval and the follow-up must follow it.
        $paracetamol->forceFill(['prn_review_after_minutes' => 25])->save();

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $given = Administration::where('prescription_id', $paracetamol->id)->firstOrFail();
        $followUp = PrnFollowUp::where('administration_id', $given->id)->firstOrFail();

        $this->assertSame(
            25,
            (int) round($given->administered_at->diffInMinutes($followUp->due_at)),
            'The interval is an instruction, not an assumption.'
        );

        $this->assertTrue($followUp->isOutstanding());
    }

    public function test_no_follow_up_is_invented_where_no_interval_is_stated(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $paracetamol->forceFill(['prn_review_after_minutes' => null])->save();

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $given = Administration::where('prescription_id', $paracetamol->id)->firstOrFail();

        $this->assertSame(
            0,
            PrnFollowUp::where('administration_id', $given->id)->count(),
            'A review time nobody stated is not one Record7 may invent.'
        );

        // The gap is surfaced rather than hidden.
        $described = $this->prn()->describe($paracetamol, Client::findOrFail($paracetamol->client_id));
        $this->assertNull($described['reviewAfterMinutes']);
    }

    public function test_a_follow_up_stays_outstanding_until_it_is_answered(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $followUp = PrnFollowUp::orderByDesc('id')->firstOrFail();
        $registry = app(IssueRegistry::class);

        $this->assertTrue(
            $registry->conditionActive('prn_follow_up:'.$followUp->id, $this->oakwood()->id)
        );

        $this->post('/record7/prn/follow-up/'.$followUp->id, [
            'outcome' => 'effective',
            'notes' => 'Settled within the hour.',
        ])->assertRedirect('/record7');

        $this->assertFalse(
            $registry->conditionActive('prn_follow_up:'.$followUp->id, $this->oakwood()->id)
        );

        $answered = PrnFollowUp::findOrFail($followUp->id);
        $this->assertSame('effective', $answered->outcome);
        $this->assertSame($this->user('noah.williams')->id, (int) $answered->completed_by_user_id);
        $this->assertNotNull($answered->completed_at);
    }

    public function test_the_three_effectiveness_answers_stay_distinct(): void
    {
        foreach (['effective', 'partly_effective', 'not_effective'] as $outcome) {
            $this->signInWithoutRound();
            $paracetamol = $this->freshPrn($this->paracetamol());

            $this->post($this->url($paracetamol), [
                'dose_amount' => 2, 'observed_reason' => 'reported_pain',
            ]);

            $followUp = PrnFollowUp::orderByDesc('id')->firstOrFail();

            $this->post('/record7/prn/follow-up/'.$followUp->id, ['outcome' => $outcome]);

            $answered = PrnFollowUp::findOrFail($followUp->id);

            $this->assertSame($outcome, $answered->outcome);
            $this->assertFalse((bool) $answered->concerning_response);
        }
    }

    /* ── A concerning response is not the same as "it did not work" ─────── */

    public function test_a_concerning_response_is_separate_from_not_effective(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $followUp = PrnFollowUp::orderByDesc('id')->firstOrFail();

        // It WORKED, and something still worried them. Both are true at once.
        $this->post('/record7/prn/follow-up/'.$followUp->id, [
            'outcome' => 'effective',
            'concerning_response' => true,
            'concern_observed' => 'Came up in a rash across his chest afterwards.',
            'concern_action_code' => 'prescriber_contacted',
        ])->assertRedirect('/record7');

        $answered = PrnFollowUp::findOrFail($followUp->id);

        $this->assertSame('effective', $answered->outcome);
        $this->assertTrue((bool) $answered->concerning_response);
        $this->assertStringContainsString('rash', $answered->concern_observed);
        $this->assertSame('prescriber_contacted', $answered->concern_action_code);

        // And it is live for a manager, on evidence rather than a tick.
        $this->assertTrue(
            app(IssueRegistry::class)->conditionActive(
                'prn_concerning_response:'.$answered->id, $this->oakwood()->id
            )
        );

        $this->assertTrue(IssueState::needsEvidence('prn_concerning_response'));
    }

    public function test_a_concern_must_say_what_was_seen_and_what_was_done(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $followUp = PrnFollowUp::orderByDesc('id')->firstOrFail();

        // Flagged, with nothing said.
        $this->post('/record7/prn/follow-up/'.$followUp->id, [
            'outcome' => 'not_effective',
            'concerning_response' => true,
        ])->assertSessionHas('r7.error');

        // Said, with no action.
        $this->post('/record7/prn/follow-up/'.$followUp->id, [
            'outcome' => 'not_effective',
            'concerning_response' => true,
            'concern_observed' => 'Very drowsy afterwards.',
        ])->assertSessionHas('r7.error');

        $this->assertTrue(PrnFollowUp::findOrFail($followUp->id)->isOutstanding());
    }

    /* ── Self-administration ────────────────────────────────────────────── */

    public function test_a_fully_self_managed_prn_needs_no_staff_record(): void
    {
        $this->signInWithoutRound();
        $salbutamol = $this->salbutamol();

        $salbutamol->forceFill(['self_administration_monitoring' => 'none'])->save();
        $salbutamol->refresh();

        $eligibility = $this->prn()->eligibility(
            $salbutamol->fresh('medicine'),
            Client::findOrFail($salbutamol->client_id)
        );

        $this->assertFalse($eligibility['allowed']);
        $this->assertSame('self_managed', $eligibility['code']);
    }

    public function test_a_monitored_self_administered_prn_records_as_taken_not_given(): void
    {
        $this->signInWithoutRound();
        $salbutamol = $this->freshPrn($this->salbutamol());

        $this->assertSame('self_administered', $salbutamol->support_type);
        $this->assertSame('check_and_record', $salbutamol->self_administration_monitoring);

        $this->post($this->url($salbutamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_breathless',
        ])->assertRedirect('/record7/person/'.$salbutamol->client_id.'/prn');

        $recorded = Administration::where('prescription_id', $salbutamol->id)->firstOrFail();

        $this->assertSame(
            'self_administered',
            $recorded->outcome,
            'She took it herself. Recording "given" would say a worker handed it over.'
        );
    }

    /* ── Controlled drugs stay with Section 2.5 ─────────────────────────── */

    public function test_a_controlled_prn_cannot_be_given_here_even_by_a_forged_request(): void
    {
        $this->signInWithoutRound();
        $lorazepam = $this->lorazepam();

        $before = Administration::where('prescription_id', $lorazepam->id)->count();

        // The screen refuses it.
        $eligibility = $this->prn()->eligibility(
            $lorazepam, Client::findOrFail($lorazepam->client_id)
        );

        $this->assertFalse($eligibility['allowed']);
        $this->assertSame('witness_required', $eligibility['code']);
        $this->assertSame('2.5', $eligibility['nextSection']);

        // And so does the server, when the button is bypassed entirely.
        $this->post($this->url($lorazepam), [
            'dose_amount' => 1, 'observed_reason' => 'observed_distress',
        ])->assertSessionHas('r7.error');

        $this->assertSame(
            $before,
            Administration::where('prescription_id', $lorazepam->id)->count()
        );
    }

    public function test_a_controlled_prn_is_still_visible_to_staff(): void
    {
        $this->signInWithoutRound();
        $lorazepam = $this->lorazepam();

        $body = $this->get('/record7/person/'.$lorazepam->client_id.'/prn')
            ->assertOk()->getContent();

        $this->assertStringContainsString(
            e($lorazepam->medicine->name),
            $body,
            'Hiding it would leave staff unaware the medicine exists.'
        );
    }

    /* ── Stock stays with Section 2.7 ───────────────────────────────────── */

    public function test_giving_a_prn_does_not_touch_stock(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $events = StockEvent::count();
        $levels = DB::connection('record7')->table('record7_stock_levels')->get()->toArray();

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ]);

        $this->assertSame($events, StockEvent::count());
        $this->assertEquals(
            $levels,
            DB::connection('record7')->table('record7_stock_levels')->get()->toArray()
        );
    }

    /* ── Isolation and authority ────────────────────────────────────────── */

    public function test_another_houses_person_cannot_be_reached(): void
    {
        $this->signInWithoutRound('olivia.carter', 'Oakwood House');

        $elsewhere = Client::where('service_id', $this->rosewood()->id)->firstOrFail();

        $this->get('/record7/person/'.$elsewhere->id.'/prn')->assertNotFound();
    }

    public function test_another_organisations_person_cannot_be_reached(): void
    {
        $rival = Organisation::create([
            'reference' => 'TEST-ORG-2-4',
            'legal_name' => 'Selby Care Ltd',
            'display_name' => 'Selby Care',
            'name_normalised' => 'selby care 24',
            'status' => 'active',
        ]);

        $theirHouse = Service::create([
            'reference' => 'TEST-SVC-2-4',
            'organisation_id' => $rival->id,
            'name' => 'Selby Lodge',
            'service_type' => 'Residential Care',
            'town' => 'Selby',
            'status' => 'active',
        ]);

        $theirClient = Client::create([
            'reference' => 'TEST-C-2-4',
            'organisation_id' => $rival->id,
            'service_id' => $theirHouse->id,
            'full_name' => 'Edith Marsden',
            'date_of_birth' => '1936-02-02',
            'status' => 'active',
        ]);

        $this->signInWithoutRound();

        $this->get('/record7/person/'.$theirClient->id.'/prn')->assertNotFound();
    }

    public function test_another_persons_prescription_cannot_be_given_to_this_person(): void
    {
        $this->signInWithoutRound();

        $paracetamol = $this->paracetamol();

        $someoneElse = Client::where('service_id', $this->oakwood()->id)
            ->where('id', '!=', $paracetamol->client_id)->firstOrFail();

        $before = Administration::where('prescription_id', $paracetamol->id)->count();

        $this->get('/record7/person/'.$someoneElse->id.'/prn/'.$paracetamol->id)->assertNotFound();

        $this->post('/record7/person/'.$someoneElse->id.'/prn/'.$paracetamol->id, [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertNotFound();

        $this->assertSame(
            $before,
            Administration::where('prescription_id', $paracetamol->id)->count()
        );
    }

    public function test_a_scheduled_medicine_cannot_be_given_through_the_prn_route(): void
    {
        $this->signInWithoutRound();

        $scheduled = Prescription::where('kind', 'scheduled')
            ->whereIn('client_id', Client::where('service_id', $this->oakwood()->id)->select('id'))
            ->firstOrFail();

        $this->get('/record7/person/'.$scheduled->client_id.'/prn/'.$scheduled->id)
            ->assertNotFound();
    }

    public function test_competency_expiry_blocks_a_prn(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        $this->get($this->url($paracetamol))->assertOk();

        $gate = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $gate->id)
            ->update(['status' => 'expired']);

        $this->post($this->url($paracetamol), [
            'dose_amount' => 2, 'observed_reason' => 'reported_pain',
        ])->assertForbidden();

        $this->assertSame(0, Administration::where('prescription_id', $paracetamol->id)->count());
    }

    /* ── Not required is not missed ─────────────────────────────────────── */

    public function test_a_prn_that_was_not_needed_is_never_treated_as_missed(): void
    {
        $this->signInWithoutRound();
        $paracetamol = $this->freshPrn($this->paracetamol());

        // A whole day with nobody needing it.
        $this->assertSame(0, Administration::where('prescription_id', $paracetamol->id)->count());

        // It does not appear as an omission, an overdue dose, or anything the
        // round is waiting on — because there is no planned dose to omit.
        $this->assertSame(
            0,
            ScheduledDose::where('prescription_id', $paracetamol->id)->count()
        );

        $body = $this->get('/record7')->assertOk()->getContent();

        $this->assertStringNotContainsString('missed', strtolower(strip_tags($body)));
    }

    /* ── Helpers ────────────────────────────────────────────────────────── */

    private function givenHoursAgo(Prescription $p, float $amount, int $hoursAgo): Administration
    {
        return $this->givenAt($p, $amount, now()->subHours($hoursAgo));
    }

    private function givenAt(Prescription $p, float $amount, $at): Administration
    {
        return Administration::create([
            'reference' => 'TEST-PRN-'.uniqid(),
            'scheduled_dose_id' => null,
            'prescription_id' => $p->id,
            'client_id' => $p->client_id,
            'service_id' => $p->client->service_id,
            'recorded_by_user_id' => $this->user('noah.williams')->id,
            'outcome' => $p->support_type === 'self_administered' ? 'self_administered' : 'given',
            'reason_code' => 'reported_pain',
            'dose_amount' => $amount,
            'dose_unit' => $p->dose_unit,
            'administered_at' => $at,
        ]);
    }
}
