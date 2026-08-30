<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Administration;
use App\Models\Record7\CdBalance;
use App\Models\Record7\CdRegister;
use App\Models\Record7\Client;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnAttempt;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\UserCompetency;
use App\Services\Record7\PrnAdministration;
use App\Services\Record7\AccessPolicy;
use App\Models\Record7\UserServiceAccess;
use App\Services\Record7\ControlledDrugAdministration;
use App\Services\Record7\ControlledDrugRegister;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Section 2.5 — controlled drugs.
 *
 * These attack the ways a controlled drug record can be wrong: a dose given
 * with nobody watching, a witness who is really the same person, a balance that
 * cannot account for what left the cupboard, a movement that never happened, a
 * clinical record with no movement behind it, and history quietly rewritten
 * afterwards.
 */
class Record7ControlledDrugTest extends Record7TestCase
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

    /* ── Getting somewhere ──────────────────────────────────────────────── */

    private function cd(): ControlledDrugAdministration
    {
        return app(ControlledDrugAdministration::class);
    }

    private function registry(): ControlledDrugRegister
    {
        return app(ControlledDrugRegister::class);
    }

    private function oakwood(): Service
    {
        return $this->house('Oakwood House');
    }

    /** Signed in, standing in a house, no round needed. */
    private function signInWithoutRound(string $username = 'noah.williams', string $house = 'Oakwood House'): Service
    {
        $this->signIn($username);
        $service = $this->house($house);
        $this->post('/record7/houses', ['house_id' => $service->id]);

        return $service;
    }

    /** Joyce's lorazepam: controlled, as-required, with a real balance. */
    private function lorazepam(): Prescription
    {
        return Prescription::with('medicine')->where('reference', 'OAK-P-016')->firstOrFail();
    }

    /** Margaret's morphine: controlled, on a schedule. */
    private function morphine(): Prescription
    {
        return Prescription::with('medicine')
            ->whereHas('medicine', fn ($q) => $q->where('name', 'Morphine sulfate MR'))
            ->firstOrFail();
    }

    private function url(Prescription $p): string
    {
        return '/record7/person/'.$p->client_id.'/controlled/'.$p->id;
    }

    /** A colleague who really can witness in this house. */
    private function witness(): User
    {
        return $this->user('olivia.carter');
    }

    /**
     * A controlled prescription with a clean, known balance.
     *
     * A copy rather than a reset: register entries are permanent and the
     * database refuses to remove them, which is the point of the section.
     */
    private function freshControlled(Prescription $like, float $openingStock = 20): Prescription
    {
        $copy = Prescription::create([
            'reference' => 'TEST-CD-'.uniqid(),
            'client_id' => $like->client_id,
            'medicine_id' => $like->medicine_id,
            'dose' => $like->dose,
            'route' => $like->route,
            'support_type' => $like->support_type,
            'frequency_text' => $like->frequency_text,
            'kind' => $like->kind,
            'status' => 'active',
            'starts_on' => now()->subMonth()->toDateString(),
            'dose_min' => $like->dose_min,
            'dose_max' => $like->dose_max,
            'dose_unit' => $like->dose_unit,
            'prn_indication' => $like->prn_indication,
            'prn_min_gap_minutes' => null,
            'prn_limit_period' => $like->prn_limit_period,
            'prn_max_administrations' => null,
            'prn_review_after_minutes' => $like->prn_review_after_minutes,
        ])->fresh('medicine');

        // A different preparation key from the fixture's, so this starts empty.
        $copy->medicine->forceFill(['strength' => $like->medicine->strength])->save();

        if ($openingStock > 0) {
            $this->cd()->receive(
                $this->user('noah.williams'), $this->oakwood(),
                Client::findOrFail($copy->client_id), $copy,
                $openingStock, $this->witnessIdIfNeeded(), null, request()
            );
        }

        return $copy->fresh('medicine');
    }

    /** Oakwood is supported living, so a witness is not required there. */
    private function witnessIdIfNeeded(?Service $house = null): ?int
    {
        $house ??= $this->oakwood();

        return $house->controlledDrugWitnessRequired() ? $this->witness()->id : null;
    }

    private function attemptToken(Prescription $p): ?string
    {
        $screen = $this->get($this->url($p));

        if ($screen->status() !== 200) {
            return null;
        }

        return $screen->viewData('page')['props']['attemptToken'] ?? null;
    }

    /* ── 1. The witness rule, by setting ────────────────────────────────── */

    public function test_a_care_home_requires_a_witness_and_a_persons_own_home_does_not(): void
    {
        $house = $this->oakwood();

        $house->forceFill(['care_setting' => 'care_home'])->save();
        $this->assertTrue($house->fresh()->controlledDrugWitnessRequired());

        $house->forceFill(['care_setting' => 'supported_living'])->save();
        $this->assertFalse($house->fresh()->controlledDrugWitnessRequired());

        $house->forceFill(['care_setting' => 'persons_own_home'])->save();
        $this->assertFalse($house->fresh()->controlledDrugWitnessRequired());
    }

    /**
     * The whole point of the rule: an unset setting is not a lenient setting.
     *
     * Defaulting the unknown case to "no witness needed" would silently drop
     * the requirement everywhere nobody had got round to filling in.
     */
    public function test_an_unknown_or_missing_setting_still_requires_a_witness(): void
    {
        $house = $this->oakwood();

        $house->forceFill(['care_setting' => null])->save();
        $this->assertTrue($house->fresh()->controlledDrugWitnessRequired(), 'NULL must fail safe.');

        $house->forceFill(['care_setting' => 'childrens_home'])->save();
        $this->assertTrue($house->fresh()->controlledDrugWitnessRequired(), 'Unresolved must fail safe.');

        $house->forceFill(['care_setting' => 'other'])->save();
        $this->assertTrue($house->fresh()->controlledDrugWitnessRequired());
    }

    /** A service may tighten the rule. There is no value that loosens it. */
    public function test_a_service_can_require_a_witness_where_the_setting_does_not(): void
    {
        $house = $this->oakwood();

        $house->forceFill([
            'care_setting' => 'supported_living',
            'cd_witness_policy' => 'always',
        ])->save();

        $this->assertTrue($house->fresh()->controlledDrugWitnessRequired());

        $this->assertSame(
            ['by_setting', 'always'],
            $this->policyOptions(),
            'There must be no policy value that removes a required witness.'
        );
    }

    private function policyOptions(): array
    {
        $type = DB::connection('record7')->selectOne(
            "SHOW COLUMNS FROM record7_services WHERE Field = 'cd_witness_policy'"
        )->Type;

        preg_match_all("/'([^']+)'/", $type, $found);

        return $found[1];
    }

    /* ── 2. Giving it ───────────────────────────────────────────────────── */

    public function test_a_scheduled_controlled_drug_is_given_and_moves_the_balance(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 20);
        $person = Client::findOrFail($morphine->client_id);

        $before = $this->registry()->balanceFor($person, $this->snap($morphine));
        $this->assertSame('20.000', $before->current_balance);

        $result = $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 1.0, $this->witnessIdIfNeeded(), null, 'Morning dose.', request()
        );

        $this->assertSame('given', $result['administration']->outcome);
        $this->assertSame('19.000', $result['entry']->balance_after);
        $this->assertSame(
            $result['entry']->id,
            $result['administration']->cd_register_id,
            'The clinical record must carry its movement.'
        );

        $this->assertSame('19.000', $before->fresh()->current_balance);
    }

    public function test_a_controlled_prn_is_given_with_all_the_section_two_four_guards(): void
    {
        $this->signInWithoutRound();
        $lorazepam = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $token = $this->attemptToken($lorazepam);
        $this->assertNotNull($token, 'The screen must issue an as-required attempt.');

        $this->post($this->url($lorazepam), [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $token,
        ])->assertRedirect('/record7/person/'.$lorazepam->client_id.'/controlled');

        $given = Administration::where('prescription_id', $lorazepam->id)->firstOrFail();

        $this->assertSame('given', $given->outcome);
        $this->assertNotNull($given->cd_register_id);
        $this->assertNull($given->scheduled_dose_id, 'A PRN answers a need, not a plan.');
        $this->assertSame('observed_distress', $given->reason_code);
    }

    /** Replay protection is Section 2.4's, and 2.5 keeps it. */
    public function test_replaying_a_controlled_prn_attempt_records_one_dose(): void
    {
        $this->signInWithoutRound();
        $lorazepam = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $payload = [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($lorazepam),
        ];

        $this->post($this->url($lorazepam), $payload);
        $this->post($this->url($lorazepam), $payload);

        $this->assertSame(1, Administration::where('prescription_id', $lorazepam->id)->count());
        $this->assertSame(
            1,
            CdRegister::where('prescription_id', $lorazepam->id)
                ->where('action', 'administration')->count(),
            'One clinical act is one movement.'
        );
    }

    /** Each 2.4 guard still refuses a controlled dose, one at a time. */
    public function test_every_section_two_four_guard_still_refuses_a_controlled_prn(): void
    {
        $this->signInWithoutRound();

        // Suspended prescription.
        $suspended = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $token = $this->attemptToken($suspended);
        $suspended->forceFill(['status' => 'suspended'])->save();

        $this->post($this->url($suspended), [
            'dose_amount' => 1, 'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(), 'attempt_token' => $token,
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $suspended->id)->count());

        // Dose outside the permitted range.
        $ranged = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->post($this->url($ranged), [
            'dose_amount' => 9, 'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($ranged),
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $ranged->id)->count());

        // Minimum interval.
        $spaced = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $spaced->forceFill(['prn_min_gap_minutes' => 360])->save();

        $this->post($this->url($spaced), [
            'dose_amount' => 1, 'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($spaced),
        ]);

        $this->post($this->url($spaced), [
            'dose_amount' => 1, 'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($spaced),
        ])->assertSessionHas('r7.error');

        $this->assertSame(
            1,
            Administration::where('prescription_id', $spaced->id)->count(),
            'The interval still bites for a controlled medicine.'
        );
    }

    /* ── 3. The witness ─────────────────────────────────────────────────── */

    public function test_a_worker_cannot_witness_their_own_administration(): void
    {
        $this->signInWithoutRound();
        $house = $this->oakwood();
        $house->forceFill(['cd_witness_policy' => 'always'])->save();

        $morphine = $this->freshControlled($this->morphine(), openingStock: 0);
        $noah = $this->user('noah.williams');

        $check = $this->cd()->checkWitness($noah->id, $noah, $house->fresh(), true);

        $this->assertFalse($check['ok']);
        $this->assertSame('witness_is_you', $check['code']);
    }

    public function test_a_witness_must_be_authorised_and_in_this_house(): void
    {
        $this->signInWithoutRound();
        $house = $this->oakwood();
        $house->forceFill(['cd_witness_policy' => 'always'])->save();
        $house = $house->fresh();

        $noah = $this->user('noah.williams');

        // Nobody.
        $this->assertSame('witness_unknown',
            $this->cd()->checkWitness(999999, $noah, $house, true)['code']);

        // Nobody named at all.
        $this->assertSame('witness_missing',
            $this->cd()->checkWitness(null, $noah, $house, true)['code']);

        // A real colleague from another house in the same organisation.
        $elsewhere = User::whereNotIn('id', DB::connection('record7')
                ->table('record7_user_service_access')->where('service_id', $house->id)->pluck('user_id'))
            ->where('organisation_id', $house->organisation_id)
            ->first();

        if ($elsewhere !== null) {
            $this->assertContains(
                $this->cd()->checkWitness($elsewhere->id, $noah, $house, true)['code'],
                ['witness_wrong_house', 'witness_not_authorised', 'witness_unknown'],
                'A colleague who does not work here cannot witness here.'
            );
        }
    }

    /** Where the setting does not require one, the reason is recorded. */
    public function test_an_unwitnessed_movement_records_why_it_was_legitimate(): void
    {
        $this->signInWithoutRound();
        $this->oakwood()->forceFill(['care_setting' => 'supported_living'])->save();

        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);

        $entry = CdRegister::where('prescription_id', $morphine->id)->firstOrFail();

        $this->assertFalse((bool) $entry->witness_was_required);
        $this->assertNull($entry->witnessed_by_user_id);
        $this->assertSame('setting_does_not_require', $entry->unwitnessed_basis,
            'A movement without a witness must say why, never carry a placeholder name.');
    }

    /* ── 4. Failing closed ──────────────────────────────────────────────── */

    public function test_a_dose_is_refused_where_nothing_has_been_counted(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 0);
        $person = Client::findOrFail($morphine->client_id);

        $this->expectExceptionMessageMatches('/no counted stock/i');

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 1.0, $this->witnessIdIfNeeded(), null, null, request()
        );
    }

    public function test_a_dose_larger_than_the_balance_is_refused(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 2);
        $person = Client::findOrFail($morphine->client_id);

        $this->expectExceptionMessageMatches('/less than this would take out/i');

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 5.0, $this->witnessIdIfNeeded(), null, null, request()
        );
    }

    public function test_an_unresolved_discrepancy_stops_a_movement_being_recorded(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);

        // A count that disagrees.
        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            7.0, $this->witnessIdIfNeeded(), 'Recounted twice.', request()
        );

        $this->assertTrue((bool) $count->is_discrepancy);
        $this->assertSame('10.000', $count->expected_quantity, 'The ledger figure is kept.');
        $this->assertSame('7.000', $count->counted_quantity, 'So is the verified one.');
        $this->assertSame('7.000', $count->balance_after, 'The verified count becomes the balance.');

        $this->expectExceptionMessageMatches('/does not agree with the register/i');

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 1.0, $this->witnessIdIfNeeded(), null, null, request()
        );
    }

    /* ── 5. Removed but not given ───────────────────────────────────────── */

    public function test_stock_removed_and_returned_intact_is_not_treated_as_waste(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);

        $result = $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine, null,
            'refused', 'client_declined',
            removed: 1, returned: 1, wasted: 0,
            witnessId: $this->witnessIdIfNeeded(), notes: 'Changed her mind at the door.',
            request: request()
        );

        $this->assertSame('refused', $result['administration']->outcome);
        $this->assertSame('non_administration', $result['entry']->action);
        $this->assertSame('10.000', $result['entry']->balance_after,
            'What went back never left the balance.');
        $this->assertSame('0.000', $result['entry']->quantity_given);
    }

    public function test_stock_removed_and_wasted_leaves_the_balance(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);

        $result = $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine, null,
            'refused', 'client_declined',
            removed: 1, returned: 0, wasted: 1,
            witnessId: $this->witnessIdIfNeeded(), notes: 'Dropped it.', request: request()
        );

        $this->assertSame('9.000', $result['entry']->balance_after);
        $this->assertSame('1.000', $result['entry']->quantity_wasted);
    }

    public function test_a_combination_of_returned_and_wasted_is_allowed(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);

        $result = $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine, null,
            'person_unavailable', 'person_out',
            removed: 2, returned: 1, wasted: 1,
            witnessId: $this->witnessIdIfNeeded(), notes: null, request: request()
        );

        $this->assertSame('9.000', $result['entry']->balance_after);
    }

    public function test_quantities_that_do_not_add_up_are_refused(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);

        $this->expectExceptionMessageMatches('/must add up to what came out/i');

        $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine, null,
            'refused', 'client_declined',
            removed: 2, returned: 0, wasted: 0,
            witnessId: $this->witnessIdIfNeeded(), notes: null, request: request()
        );
    }

    /* ── 6. The database, on its own ────────────────────────────────────── */

    private function assertRawSqlRefused(string $sql, array $bindings = []): void
    {
        try {
            DB::connection('record7')->statement($sql, $bindings);
            $this->fail('The database allowed: '.$sql);
        } catch (QueryException $refused) {
            $this->assertNotEmpty($refused->getMessage());
        }
    }

    public function test_raw_sql_cannot_rewrite_or_delete_a_register_entry(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $entry = CdRegister::where('prescription_id', $morphine->id)->firstOrFail();

        $this->assertRawSqlRefused(
            'UPDATE record7_cd_register SET balance_after = 999 WHERE id = ?', [$entry->id]
        );

        $this->assertRawSqlRefused(
            'UPDATE record7_cd_register SET witnessed_by_user_id = NULL WHERE id = ?', [$entry->id]
        );

        $this->assertRawSqlRefused(
            'DELETE FROM record7_cd_register WHERE id = ?', [$entry->id]
        );

        $this->assertSame('5.000', $entry->fresh()->balance_after);
    }

    public function test_the_model_refuses_to_change_or_delete_a_register_entry(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $entry = CdRegister::where('prescription_id', $morphine->id)->firstOrFail();

        $this->expectExceptionMessageMatches('/permanent/i');

        $entry->forceFill(['notes' => 'rewritten'])->save();
    }

    public function test_a_browser_supplied_balance_is_rejected_by_the_database(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($morphine->client_id);
        $balance = $this->registry()->balanceFor($person, $this->snap($morphine));

        // A movement claiming a flattering balance the arithmetic does not give.
        $this->assertRawSqlRefused(
            'INSERT INTO record7_cd_register
                (reference, organisation_id, service_id, client_id, medicine_id, prescription_id,
                 medicine_name_at_time, unit, action, quantity_removed, quantity_given,
                 quantity_returned, quantity_wasted, balance_before, balance_after,
                 recorded_by_user_id, witness_was_required, unwitnessed_basis,
                 occurred_at, sequence_no, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "administration", 1, 1, 0, 0, 10, 10,
                     ?, 0, "setting_does_not_require", NOW(), ?, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $this->oakwood()->organisation_id, $this->oakwood()->id,
                $person->id, $morphine->medicine_id, $morphine->id,
                $morphine->medicine->name, $morphine->dose_unit,
                $this->user('noah.williams')->id, $balance->last_sequence_no + 1,
            ]
        );
    }

    public function test_an_ordinary_movement_cannot_drive_the_balance_negative(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 1);
        $person = Client::findOrFail($morphine->client_id);
        $balance = $this->registry()->balanceFor($person, $this->snap($morphine));

        $this->assertRawSqlRefused(
            'INSERT INTO record7_cd_register
                (reference, organisation_id, service_id, client_id, medicine_id, prescription_id,
                 medicine_name_at_time, unit, action, quantity_removed, quantity_given,
                 quantity_returned, quantity_wasted, balance_before, balance_after,
                 recorded_by_user_id, witness_was_required, unwitnessed_basis,
                 occurred_at, sequence_no, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "administration", 5, 5, 0, 0, 1, -4,
                     ?, 0, "setting_does_not_require", NOW(), ?, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $this->oakwood()->organisation_id, $this->oakwood()->id,
                $person->id, $morphine->medicine_id, $morphine->id,
                $morphine->medicine->name, $morphine->dose_unit,
                $this->user('noah.williams')->id, $balance->last_sequence_no + 1,
            ]
        );
    }

    public function test_a_controlled_administration_cannot_exist_without_its_movement(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);

        $this->assertRawSqlRefused(
            'INSERT INTO record7_administrations
                (reference, scheduled_dose_id, prescription_id, client_id, service_id,
                 recorded_by_user_id, outcome, administered_at, created_at, updated_at)
             VALUES (?, NULL, ?, ?, ?, ?, "given", NOW(), NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $morphine->id, $morphine->client_id,
                $this->oakwood()->id, $this->user('noah.williams')->id,
            ]
        );
    }

    public function test_a_movement_for_another_person_cannot_be_claimed(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $entry = CdRegister::where('prescription_id', $morphine->id)->firstOrFail();

        $someoneElse = Client::where('service_id', $this->oakwood()->id)
            ->where('id', '!=', $morphine->client_id)->firstOrFail();

        $theirs = Prescription::where('client_id', $someoneElse->id)->firstOrFail();

        $this->assertRawSqlRefused(
            'INSERT INTO record7_administrations
                (reference, scheduled_dose_id, prescription_id, client_id, service_id,
                 recorded_by_user_id, outcome, administered_at, cd_register_id, created_at, updated_at)
             VALUES (?, NULL, ?, ?, ?, ?, "given", NOW(), ?, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $theirs->id, $someoneElse->id,
                $this->oakwood()->id, $this->user('noah.williams')->id, $entry->id,
            ]
        );
    }

    public function test_a_witness_cannot_be_the_recorder_at_database_level(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $person = Client::findOrFail($morphine->client_id);
        $balance = $this->registry()->balanceFor($person, $this->snap($morphine));
        $noah = $this->user('noah.williams');

        $this->assertRawSqlRefused(
            'INSERT INTO record7_cd_register
                (reference, organisation_id, service_id, client_id, medicine_id, prescription_id,
                 medicine_name_at_time, unit, action, quantity_received, balance_before, balance_after,
                 recorded_by_user_id, witnessed_by_user_id, witness_was_required,
                 occurred_at, sequence_no, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "receipt", 1, 5, 6, ?, ?, 1, NOW(), ?, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $this->oakwood()->organisation_id, $this->oakwood()->id,
                $person->id, $morphine->medicine_id, $morphine->id,
                $morphine->medicine->name, $morphine->dose_unit,
                $noah->id, $noah->id, $balance->last_sequence_no + 1,
            ]
        );
    }

    public function test_a_cross_house_movement_is_refused_by_the_database(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $person = Client::findOrFail($morphine->client_id);
        $elsewhere = Service::where('id', '!=', $this->oakwood()->id)->firstOrFail();

        $this->assertRawSqlRefused(
            'INSERT INTO record7_cd_register
                (reference, organisation_id, service_id, client_id, medicine_id, prescription_id,
                 medicine_name_at_time, unit, action, quantity_received, balance_before, balance_after,
                 recorded_by_user_id, witness_was_required, unwitnessed_basis,
                 occurred_at, sequence_no, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "receipt", 1, 5, 6, ?, 0,
                     "setting_does_not_require", NOW(), ?, NOW(), NOW())',
            [
                'FORGED-'.uniqid(), $elsewhere->organisation_id, $elsewhere->id,
                $person->id, $morphine->medicine_id, $morphine->id,
                $morphine->medicine->name, $morphine->dose_unit,
                $this->user('noah.williams')->id, 99,
            ]
        );
    }

    /* ── 7. The balance agrees with the ledger ──────────────────────────── */

    public function test_the_balance_head_always_agrees_with_the_register(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 12);
        $person = Client::findOrFail($morphine->client_id);

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 1.0, $this->witnessIdIfNeeded(), null, null, request()
        );

        $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine, null,
            'refused', 'client_declined', removed: 1, returned: 0, wasted: 1,
            witnessId: $this->witnessIdIfNeeded(), notes: null, request: request()
        );

        $balance = $this->registry()->balanceFor($person, $this->snap($morphine));

        $latest = CdRegister::where('client_id', $person->id)
            ->where('preparation_key', $balance->preparation_key)
            ->orderByDesc('sequence_no')->firstOrFail();

        $this->assertSame(
            $latest->balance_after,
            $balance->current_balance,
            'The head is derived, so it must agree with the ledger it derives from.'
        );

        $this->assertSame('10.000', $balance->current_balance);
    }

    /** Editing a strength must not silently reinterpret history. */
    public function test_changing_a_strength_starts_a_new_balance_rather_than_corrupting_the_old(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 8);
        $person = Client::findOrFail($morphine->client_id);

        $firstKey = $this->registry()->preparationKey($this->snap($morphine));

        $morphine->medicine->forceFill(['strength' => '20mg'])->save();

        $secondKey = $this->registry()->preparationKey(
            $this->registry()->snapshot($morphine->medicine->fresh(), $morphine->dose_unit)
        );

        $this->assertNotSame($firstKey, $secondKey,
            'A different strength is a different thing to count.');

        $this->assertSame(
            '8.000',
            CdBalance::where('client_id', $person->id)
                ->where('preparation_key', $firstKey)->firstOrFail()->current_balance,
            'The old balance is untouched and still means what it meant.'
        );
    }

    private function snap(Prescription $p): array
    {
        return $this->registry()->snapshot($p->medicine, $p->dose_unit);
    }

    /* ── 8. Audit ───────────────────────────────────────────────────────── */

    public function test_a_blocked_controlled_movement_is_audited(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 1);
        $person = Client::findOrFail($morphine->client_id);

        try {
            $this->cd()->give(
                $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
                null, 5.0, $this->witnessIdIfNeeded(), null, null, request()
            );
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'controlled_drug_movement_blocked',
        ], 'record7');
    }

    public function test_a_successful_controlled_administration_is_audited(): void
    {
        $this->signInWithoutRound();
        $morphine = $this->freshControlled($this->morphine(), openingStock: 5);
        $person = Client::findOrFail($morphine->client_id);

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $morphine,
            null, 1.0, $this->witnessIdIfNeeded(), null, null, request()
        );

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'controlled_medication_administered',
        ], 'record7');
    }

    /* ── 9. Scope ───────────────────────────────────────────────────────── */

    public function test_a_person_in_another_house_cannot_be_reached(): void
    {
        $this->signInWithoutRound();

        $elsewhere = Client::where('service_id', '!=', $this->oakwood()->id)->firstOrFail();

        $this->get('/record7/person/'.$elsewhere->id.'/controlled')->assertNotFound();
    }

    public function test_a_medicine_that_is_not_controlled_is_not_reachable_here(): void
    {
        $this->signInWithoutRound();

        $ordinary = Prescription::whereHas('medicine', fn ($q) => $q->where('is_controlled', false))
            ->whereHas('client', fn ($q) => $q->where('service_id', $this->oakwood()->id))
            ->firstOrFail();

        $this->get('/record7/person/'.$ordinary->client_id.'/controlled/'.$ordinary->id)
            ->assertNotFound();
    }

    /* ── Every Section 2.4 guard, proved one at a time ──────────────────── */

    /**
     * The controlled pathway skips ONE thing and nothing else.
     *
     * WHY THIS IS A LIST OF CODES AND NOT A LIST OF "an error happened".
     * The first attempt at this replaced the controlled stop by letting the
     * caller tolerate the code `eligibility()` returned. That looked right and
     * was badly wrong: the controlled stop sits SECOND in the guard order, so
     * tolerating it skipped prescription status, support type, availability,
     * interval and maximum along with it. Asserting the exact refusal code for
     * each guard is what makes that impossible to reintroduce quietly.
     */
    private function assertControlledPrnRefusal(Prescription $p, string $expectedCode, float $amount = 1.0): void
    {
        $client = Client::findOrFail($p->client_id);

        $eligibility = $this->prn()->eligibility($p, $client, null, controlledPathway: true);

        if (! $eligibility['allowed']) {
            $this->assertSame($expectedCode, $eligibility['code'],
                'The controlled pathway must refuse for the stated reason.');

            return;
        }

        $dose = $this->prn()->checkDose($p, $amount);

        $this->assertFalse($dose['allowed'], "Nothing refused a controlled PRN for {$expectedCode}.");
        $this->assertSame($expectedCode, $dose['code']);
    }

    /**
     * The same guard, through the door Section 2.5 actually uses.
     *
     * assertGivable() is what the controlled pathway calls, so every guard is
     * checked there too. Without this, a version of assertGivable that
     * tolerated every refusal for a controlled drug would pass every
     * per-guard test above, because those ask eligibility() directly.
     */
    private function assertGivableRefuses(Prescription $p, float $amount = 1.0, string $reason = 'observed_distress'): void
    {
        $client = Client::findOrFail($p->client_id);

        // NOT try/fail/catch. PHPUnit's fail() throws an AssertionFailedError
        // that extends RuntimeException, so a catch here would swallow the very
        // failure it exists to report — which is exactly what happened, and is
        // why this records the outcome instead of asserting inside the catch.
        $refusal = null;

        try {
            $this->prn()->assertGivable($p, $client, $amount, $reason, controlled: true);
        } catch (\RuntimeException $thrown) {
            $refusal = $thrown->getMessage();
        }

        $this->assertNotNull(
            $refusal,
            'assertGivable let a controlled as-required dose through.'
        );
    }

    private function prn(): PrnAdministration
    {
        return app(PrnAdministration::class);
    }

    /** The obsolete stop, and ONLY that, is gone. */
    public function test_the_controlled_pathway_replaces_only_the_witness_required_stop(): void
    {
        $this->signInWithoutRound();
        $lorazepam = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $client = Client::findOrFail($lorazepam->client_id);

        // Off the pathway, the old Section 2.5 stop still fires.
        $closed = $this->prn()->eligibility($lorazepam, $client);
        $this->assertFalse($closed['allowed']);
        $this->assertSame('witness_required', $closed['code']);

        // On the pathway, that one stop is lifted and the dose is allowed.
        $open = $this->prn()->eligibility($lorazepam, $client, null, controlledPathway: true);
        $this->assertTrue($open['allowed'], 'The controlled pathway must be able to proceed.');
    }

    public function test_a_suspended_controlled_prescription_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['status' => 'suspended'])->save();

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'prescription_suspended');
        $this->assertGivableRefuses($p->fresh('medicine'));
    }

    public function test_a_stopped_controlled_prescription_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['status' => 'stopped'])->save();

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'prescription_stopped');
        $this->assertGivableRefuses($p->fresh('medicine'));
    }

    public function test_a_fully_self_managed_controlled_medicine_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['support_type' => 'self_administered', 'self_administration_monitoring' => 'none'])->save();

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'self_managed');
        $this->assertGivableRefuses($p->fresh('medicine'));
    }

    public function test_a_prompted_controlled_medicine_still_refuses_the_staff_give(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['support_type' => 'prompted'])->save();

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'support_type_prompted');
        $this->assertGivableRefuses($p->fresh('medicine'));
    }

    public function test_a_person_who_is_not_here_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $client = Client::findOrFail($p->client_id);

        $was = $client->status;
        $client->forceFill(['status' => 'in_hospital'])->save();

        try {
            $this->assertControlledPrnRefusal($p, 'person_away');
            $this->assertGivableRefuses($p);
        } finally {
            $client->forceFill(['status' => $was])->save();
        }
    }

    public function test_the_minimum_interval_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['prn_min_gap_minutes' => 360])->save();
        $p = $p->fresh('medicine');

        $this->giveControlled($p, 1.0);

        $this->assertControlledPrnRefusal($p, 'too_soon');
        $this->assertGivableRefuses($p);
    }

    public function test_the_maximum_number_of_doses_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill([
            'prn_min_gap_minutes' => null,
            'prn_limit_period' => 'rolling_24h',
            'prn_max_administrations' => 1,
        ])->save();
        $p = $p->fresh('medicine');

        $this->giveControlled($p, 1.0);

        $this->assertControlledPrnRefusal($p, 'max_administrations_reached');
        $this->assertGivableRefuses($p);
    }

    public function test_the_maximum_total_amount_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill([
            'prn_min_gap_minutes' => null,
            'prn_limit_period' => 'rolling_24h',
            'prn_max_administrations' => null,
            'prn_max_total_amount' => 1,
            'dose_max' => 2,
        ])->save();
        $p = $p->fresh('medicine');

        $this->giveControlled($p, 1.0);

        $this->assertControlledPrnRefusal($p, 'max_total_amount_reached');
        $this->assertGivableRefuses($p);
    }

    public function test_a_dose_below_the_permitted_range_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $p->forceFill(['dose_min' => 2, 'dose_max' => 2])->save();

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'below_permitted_dose', amount: 1.0);
        $this->assertGivableRefuses($p->fresh('medicine'), amount: 1.0);
    }

    public function test_a_dose_above_the_permitted_range_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'above_permitted_dose', amount: 9.0);
        $this->assertGivableRefuses($p->fresh('medicine'), amount: 9.0);
    }

    public function test_a_dose_of_nothing_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->assertControlledPrnRefusal($p->fresh('medicine'), 'dose_not_positive', amount: 0.0);
        $this->assertGivableRefuses($p->fresh('medicine'), amount: 0.0);
    }

    /** The observed indication is validated, not merely carried. */
    public function test_an_unknown_observed_reason_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->post($this->url($p), [
            'dose_amount' => 1,
            'observed_reason' => 'because_i_felt_like_it',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($p),
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $p->id)->count());
    }

    public function test_a_missing_observed_reason_still_refuses(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->post($this->url($p), [
            'dose_amount' => 1,
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $this->attemptToken($p),
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $p->id)->count());
    }

    /* ── The attempt token, on the controlled path ──────────────────────── */

    public function test_a_controlled_prn_needs_an_attempt_token(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->post($this->url($p), [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $p->id)->count());
    }

    public function test_a_forged_attempt_token_is_refused_on_the_controlled_path(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $this->post($this->url($p), [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => 'i-made-this-up',
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $p->id)->count());
    }

    public function test_an_attempt_for_another_medicine_is_refused_on_the_controlled_path(): void
    {
        $this->signInWithoutRound();

        $mine = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $theirs = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $token = $this->attemptToken($theirs);

        $this->post($this->url($mine), [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $token,
        ])->assertSessionHas('r7.error');

        $this->assertSame(0, Administration::where('prescription_id', $mine->id)->count());
        $this->assertFalse(PrnAttempt::where('token', $token)->firstOrFail()->isSpent());
    }

    /** Authority is asked at request time, not assumed from the screen. */
    public function test_a_worker_without_competency_cannot_give_a_controlled_prn(): void
    {
        $service = $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $token = $this->attemptToken($p);

        // Expire the competency after the screen was opened.
        $type = CompetencyType::where('gates_permission', 'administer_medication')->firstOrFail();

        UserCompetency::where('user_id', $this->user('noah.williams')->id)
            ->where('competency_type_id', $type->id)
            ->update(['status' => 'expired']);

        $this->post($this->url($p), [
            'dose_amount' => 1,
            'observed_reason' => 'observed_distress',
            'witness_id' => $this->witnessIdIfNeeded(),
            'attempt_token' => $token,
        ]);

        $this->assertSame(
            0,
            Administration::where('prescription_id', $p->id)->count(),
            'Competency is checked when the button is pressed, not when the screen opened.'
        );
    }

    /** A helper that actually gives one, for the guards that need history. */
    private function giveControlled(Prescription $p, float $amount): void
    {
        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(),
            Client::findOrFail($p->client_id), $p, null, $amount,
            $this->witnessIdIfNeeded(), 'observed_distress', null,
            request(), $this->mintToken($p)
        );
    }

    /** A token minted straight from the service, for direct service calls. */
    private function mintToken(Prescription $p): string
    {
        return $this->prn()->beginAttempt(
            $this->user('noah.williams'), $this->oakwood()->id,
            Client::findOrFail($p->client_id), $p
        )->token;
    }

    /* ── Discrepancy and correction, end to end ─────────────────────────── */

    /**
     * A disagreement is settled by accounting for it, never by a tick.
     *
     * The count records what is really there and flags the divergence. The
     * correction that follows adds an entry naming the one it corrects — the
     * original is never touched — and only then can a dose be recorded again.
     */
    public function test_a_correction_resolves_a_discrepancy_and_leaves_both_on_the_record(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            7.0, $this->witnessIdIfNeeded(), 'Counted twice with a colleague.', request()
        );

        $this->assertTrue((bool) $count->is_discrepancy);

        // While it stands, a dose cannot be accounted for.
        try {
            $this->giveControlled($p, 1.0);
            $this->fail('A dose was recorded against an unresolved discrepancy.');
        } catch (\RuntimeException $expected) {
            $this->assertStringContainsStringIgnoringCase('does not agree', $expected->getMessage());
        }

        $fix = $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, $count,
            7.0, $this->witnessIdIfNeeded(),
            'Three tablets were found in the second pot. Counted again with Olivia.',
            request()
        );

        $this->assertSame('correction', $fix->action);
        $this->assertSame($count->id, (int) $fix->corrects_register_id);
        $this->assertSame('7.000', $fix->balance_after);

        // The original is exactly as it was.
        $this->assertTrue((bool) $count->fresh()->is_discrepancy, 'The original stays as written.');
        $this->assertSame('7.000', $count->fresh()->counted_quantity);
        $this->assertSame('10.000', $count->fresh()->expected_quantity);

        // And now a dose can be recorded again.
        $this->giveControlled($p, 1.0);

        $this->assertSame(
            '6.000',
            $this->registry()->balanceFor($person, $this->snap($p))->current_balance
        );
    }

    public function test_a_correction_must_explain_itself(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);
        $entry = CdRegister::where('prescription_id', $p->id)->firstOrFail();

        $this->expectExceptionMessageMatches('/how you know/i');

        $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, $entry,
            9.0, $this->witnessIdIfNeeded(), '   ', request()
        );
    }

    public function test_a_correction_cannot_name_another_persons_entry(): void
    {
        $this->signInWithoutRound();

        $mine = $this->freshControlled($this->morphine(), openingStock: 10);
        $theirs = $this->freshControlled($this->lorazepam(), openingStock: 10);

        $someoneElse = Client::findOrFail($theirs->client_id);
        $person = Client::findOrFail($mine->client_id);

        if ($someoneElse->id === $person->id) {
            $this->markTestSkipped('The fixture puts both controlled medicines with one person.');
        }

        $theirEntry = CdRegister::where('prescription_id', $theirs->id)->firstOrFail();

        $this->expectExceptionMessageMatches('/somebody else/i');

        $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $mine, $theirEntry,
            9.0, $this->witnessIdIfNeeded(), 'Trying to correct the wrong record.', request()
        );
    }

    /** The correction is audited as the serious thing it is. */
    public function test_a_correction_is_audited(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);
        $entry = CdRegister::where('prescription_id', $p->id)->firstOrFail();

        $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, $entry,
            9.0, $this->witnessIdIfNeeded(), 'One was dropped and not recorded at the time.', request()
        );

        $this->assertDatabaseHas('record7_access_audit_events', [
            'event_type' => 'controlled_drug_corrected',
        ], 'record7');
    }

    /** Correcting through the screen resolves it the same way. */
    public function test_a_correction_can_be_recorded_through_the_screen(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            8.0, $this->witnessIdIfNeeded(), null, request()
        );

        $this->post($this->url($p).'/correct', [
            'corrects_register_id' => $count->id,
            'true_balance' => 8,
            'why' => 'Recounted with Olivia; eight is right.',
            'witness_id' => $this->witnessIdIfNeeded(),
        ]);

        $this->assertSame(
            1,
            CdRegister::where('prescription_id', $p->id)->where('action', 'correction')->count()
        );

        $this->assertNull(
            $this->registry()->openDiscrepancy($person, $this->snap($p)),
            'A corrected discrepancy is no longer open.'
        );
    }

    /** A correction for somebody else's entry is refused at the route too. */
    public function test_the_screen_refuses_a_correction_naming_a_foreign_entry(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);

        $elsewhere = CdRegister::where('client_id', '!=', $p->client_id)->first();

        if ($elsewhere === null) {
            $this->markTestSkipped('No other register entry to point at.');
        }

        $this->post($this->url($p).'/correct', [
            'corrects_register_id' => $elsewhere->id,
            'true_balance' => 5,
            'why' => 'Trying to reach another record.',
            'witness_id' => $this->witnessIdIfNeeded(),
        ])->assertNotFound();
    }

    /**
     * The balance row really is locked while the decision is made.
     *
     * A single-threaded suite cannot watch a lock hold somebody up, so removing
     * lockForUpdate() would otherwise break no test at all and leave the most
     * important guard in this section unguarded. This asserts the statement.
     * The behavioural proof is a two-connection probe run outside the suite.
     */
    public function test_the_balance_row_is_locked_before_anything_is_decided(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $connection = DB::connection('record7');
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            null, 1.0, $this->witnessIdIfNeeded(), null, null, request()
        );

        $statements = collect($connection->getQueryLog())->pluck('query')
            ->map(fn ($q) => strtolower($q));

        $connection->disableQueryLog();

        $this->assertTrue(
            $statements->contains(fn ($q) => str_contains($q, 'record7_cd_balances')
                && str_contains($q, 'for update')),
            'The balance must be selected FOR UPDATE before sufficiency is judged.'
        );
    }

    /** The head row is created before it is locked, never locked into existence. */
    public function test_the_balance_row_is_inserted_before_it_is_locked(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 0);
        $person = Client::findOrFail($p->client_id);

        $connection = DB::connection('record7');
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        $this->cd()->receive(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            5.0, $this->witnessIdIfNeeded(), null, request()
        );

        $queries = collect($connection->getQueryLog())->pluck('query')
            ->map(fn ($q) => strtolower($q))->values();

        $connection->disableQueryLog();

        $insert = $queries->search(fn ($q) => str_contains($q, 'insert ignore')
            && str_contains($q, 'record7_cd_balances'));
        $lock = $queries->search(fn ($q) => str_contains($q, 'record7_cd_balances')
            && str_contains($q, 'for update'));

        $this->assertNotFalse($insert, 'The head row must be created first.');
        $this->assertNotFalse($lock);
        $this->assertLessThan(
            $lock,
            $insert,
            'Locking a row that does not exist takes a gap lock and deadlocks two first movements.'
        );
    }

    /* ── The clean fixture supports the witnessed journey ───────────────── */

    /**
     * A witnessed controlled-drug administration, from a clean seed only.
     *
     * WHY THIS TEST EXISTS.
     * The care-home journey needs two distinct people, and for a while the only
     * worker in this house who could witness was also the one giving it —
     * nobody may witness themselves, so the whole workflow was unreachable
     * without editing the database by hand. A workflow that needs hidden setup
     * is a workflow nobody can check, so the fixture now provides a second
     * signatory and this proves it, using NOTHING but what the seeder wrote.
     */
    public function test_the_clean_fixture_can_perform_a_witnessed_controlled_administration(): void
    {
        $house = $this->signInWithoutRound();

        // A care home, which is where a second signature is required.
        $house->forceFill(['care_setting' => 'care_home'])->save();
        $house = $house->fresh();

        $this->assertTrue($house->controlledDrugWitnessRequired());

        $administrator = $this->user('noah.williams');

        // Whoever the fixture offers, resolved exactly as the screen does.
        $offered = $this->witnessesOfferedTo($administrator, $house);

        $this->assertNotEmpty(
            $offered,
            'A clean fixture must contain a second person who can witness here.'
        );

        $witness = $offered[0];

        $this->assertNotSame(
            $administrator->id,
            $witness->id,
            'The administrator must never be offered as their own witness.'
        );

        // Everything the rule demands of a witness, checked on the real user.
        $check = $this->cd()->checkWitness($witness->id, $administrator, $house, true);
        $this->assertTrue($check['ok'], $check['reason'] ?? 'The fixture witness was refused.');

        // And the whole journey runs: stock in, then a witnessed dose.
        $morphine = $this->morphine();
        $person = Client::findOrFail($morphine->client_id);

        $this->cd()->receive(
            $administrator, $house, $person, $morphine, 20.0, $witness->id,
            'Booked in for the witnessed journey.', request()
        );

        $result = $this->cd()->give(
            $administrator, $house, $person, $morphine, null, 1.0, $witness->id,
            null, 'Given with a second signature.', request()
        );

        $this->assertSame('given', $result['administration']->outcome);
        $this->assertSame($witness->id, $result['administration']->witnessed_by_user_id);
        $this->assertTrue((bool) $result['entry']->witness_was_required);
        $this->assertSame($witness->id, (int) $result['entry']->witnessed_by_user_id);
        $this->assertNotNull($result['entry']->witness_name_at_time);
        $this->assertNull($result['entry']->unwitnessed_basis);
        $this->assertSame('19.000', $result['entry']->balance_after);
    }

    /** The same list the give screen builds, resolved through the policy. */
    private function witnessesOfferedTo(User $administrator, Service $house): array
    {
        $policy = app(AccessPolicy::class);

        return User::whereIn('id', UserServiceAccess::where('service_id', $house->id)->pluck('user_id'))
            ->where('organisation_id', $house->organisation_id)
            ->where('id', '!=', $administrator->id)
            ->get()
            ->filter(fn (User $u) => $u->accessRefusalReason() === null
                && $policy->allows($u, 'witness_medication', $house->id))
            ->values()->all();
    }

    /* ── Same-episode return arithmetic ─────────────────────────────────── */

    /**
     * A return inside an episode is accounted for THERE, once.
     *
     * The owner ruling: where an episode removes stock and some or all comes
     * straight back, that return belongs to the episode. It must NOT also
     * appear as a separate return_to_storage movement, because the stock only
     * physically moved once and a ledger that records it twice is wrong in the
     * direction that hides a shortfall.
     *
     * return_to_storage exists for a genuinely separate physical return — stock
     * going back at the end of a cycle, not the tail of a dose.
     */
    public function test_a_return_inside_an_episode_creates_no_second_movement(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $before = CdRegister::where('client_id', $person->id)->count();

        $result = $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
            'refused', 'client_declined',
            removed: 2, returned: 2, wasted: 0,
            witnessId: $this->witnessIdIfNeeded(), notes: 'Changed her mind at the door.',
            request: request()
        );

        $after = CdRegister::where('client_id', $person->id)->count();

        $this->assertSame($before + 1, $after, 'One physical movement is one entry.');
        $this->assertSame('non_administration', $result['entry']->action);

        $this->assertSame(
            0,
            CdRegister::where('client_id', $person->id)->where('action', 'return_to_storage')->count(),
            'The return was inside the episode; there is no separate return movement.'
        );

        // The figures are all there, on the one entry.
        $this->assertSame('2.000', $result['entry']->quantity_removed);
        $this->assertSame('2.000', $result['entry']->quantity_returned);
        $this->assertSame('0.000', $result['entry']->quantity_given);
        $this->assertSame('0.000', $result['entry']->quantity_wasted);

        // And nothing left the balance, because nothing was destroyed.
        $this->assertSame('10.000', $result['entry']->balance_after);
    }

    /** A partial return inside an episode is likewise one entry. */
    public function test_a_partial_return_inside_an_episode_is_also_one_movement(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $before = CdRegister::where('client_id', $person->id)->count();

        $result = $this->cd()->removedButNotGiven(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
            'not_available', 'medicine_damaged',
            removed: 2, returned: 1, wasted: 1,
            witnessId: $this->witnessIdIfNeeded(), notes: 'One dropped, one back in.',
            request: request()
        );

        $this->assertSame(
            $before + 1,
            CdRegister::where('client_id', $person->id)->count(),
            'Removed, returned and wasted are three figures on one movement.'
        );

        $this->assertSame(
            0,
            CdRegister::where('client_id', $person->id)->where('action', 'return_to_storage')->count()
        );

        // Only the destroyed part leaves the balance.
        $this->assertSame('9.000', $result['entry']->balance_after);
    }

    /* ── The discrepancy lifecycle, in full ─────────────────────────────── */

    /**
     * How a discrepancy opens, and why nothing but evidence closes it.
     *
     * IT IS A DERIVED CONDITION, NOT A WORKFLOW STATE. There is no "resolved"
     * flag to tick, no status column to set and no note field that closes it.
     * `openDiscrepancy()` asks the ledger: is there an entry marked as a
     * disagreement with no correction naming it? That question has exactly one
     * answer, and the only way to change it is to add a correction.
     *
     * This is deliberate. A flag can be set by anybody who finds it
     * inconvenient; a ledger cannot.
     */
    private function openDiscrepancyFor(Prescription $p): ?CdRegister
    {
        return $this->registry()->openDiscrepancy(
            Client::findOrFail($p->client_id), $this->snap($p)
        );
    }

    public function test_a_count_that_disagrees_opens_a_discrepancy_and_records_both_figures(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $this->assertNull($this->openDiscrepancyFor($p), 'Nothing is open to begin with.');

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), 'Counted twice.', request()
        );

        $open = $this->openDiscrepancyFor($p);

        $this->assertNotNull($open, 'A count that disagrees must open a discrepancy.');
        $this->assertSame($count->id, $open->id);
        $this->assertSame('10.000', $open->expected_quantity, 'What the ledger said.');
        $this->assertSame('6.000', $open->counted_quantity, 'What was actually there.');
        $this->assertSame('6.000', $open->balance_after, 'The verified figure becomes the balance.');
    }

    /** A count that agrees is not a discrepancy. */
    public function test_a_count_that_agrees_opens_nothing(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            10.0, $this->witnessIdIfNeeded(), null, request()
        );

        $this->assertFalse((bool) $count->is_discrepancy);
        $this->assertNull($this->openDiscrepancyFor($p));
    }

    /** There is no acknowledgement to make, and adding notes changes nothing. */
    public function test_no_acknowledgement_or_note_can_close_a_discrepancy(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), null, request()
        );

        // There is no status to set and no note that closes it. The nearest
        // thing to an acknowledgement is another count, which cannot clear it.
        $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), 'Checked. Looks fine to me.', request()
        );

        $this->assertNotNull(
            $this->openDiscrepancyFor($p),
            'A second count and a reassuring note do not resolve anything.'
        );

        // Nor can the flag be turned off directly.
        $this->assertRawSqlRefused(
            'UPDATE record7_cd_register SET is_discrepancy = 0 WHERE id = ?', [$count->id]
        );

        $this->assertTrue((bool) $count->fresh()->is_discrepancy);
        $this->assertNotNull($this->openDiscrepancyFor($p));
    }

    /** Ordinary movements are refused while it stands, so none can hide it. */
    public function test_ordinary_movements_are_blocked_while_a_discrepancy_is_open(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), null, request()
        );

        $administrationsBefore = Administration::where('prescription_id', $p->id)->count();

        foreach (['give', 'notGiven'] as $attempt) {
            $refused = false;

            try {
                if ($attempt === 'give') {
                    $this->giveControlled($p, 1.0);
                } else {
                    $this->cd()->removedButNotGiven(
                        $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
                        'refused', 'client_declined', removed: 1, returned: 1, wasted: 0,
                        witnessId: $this->witnessIdIfNeeded(), notes: null, request: request()
                    );
                }
            } catch (\RuntimeException) {
                $refused = true;
            }

            $this->assertTrue(
                $refused,
                "A {$attempt} episode cannot be recorded while the count disagrees."
            );
        }

        $this->assertSame(
            $administrationsBefore,
            Administration::where('prescription_id', $p->id)->count(),
            'No administration slipped through.'
        );

        $this->assertNotNull(
            $this->openDiscrepancyFor($p),
            'And the discrepancy is still exactly where it was.'
        );
    }

    /** Only a correction naming the entry resolves it. */
    public function test_only_a_correction_resolves_it_and_the_original_stays(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $count = $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), null, request()
        );

        $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, $count,
            6.0, $this->witnessIdIfNeeded(),
            'Four were signed out to the hospital on paper and never entered.',
            request()
        );

        $this->assertNull($this->openDiscrepancyFor($p), 'A correction closes it.');

        // The original is untouched and still says what it said.
        $original = $count->fresh();
        $this->assertTrue((bool) $original->is_discrepancy);
        $this->assertSame('10.000', $original->expected_quantity);
        $this->assertSame('6.000', $original->counted_quantity);
        $this->assertSame($count->occurred_at->toDateTimeString(), $original->occurred_at->toDateTimeString());

        // And a dose can be recorded again.
        $this->giveControlled($p, 1.0);
        $this->assertSame('5.000', $this->registry()->balanceFor($person, $this->snap($p))->current_balance);
    }

    /** A correction naming a different entry does not clear this one. */
    public function test_a_correction_naming_another_entry_does_not_clear_the_discrepancy(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->morphine(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $receipt = CdRegister::where('prescription_id', $p->id)->where('action', 'receipt')->firstOrFail();

        $this->cd()->count(
            $this->user('noah.williams'), $this->oakwood(), $person, $p,
            6.0, $this->witnessIdIfNeeded(), null, request()
        );

        // Correct the RECEIPT, not the count.
        $this->cd()->correct(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, $receipt,
            6.0, $this->witnessIdIfNeeded(), 'The delivery note said ten but nine arrived.', request()
        );

        $this->assertNotNull(
            $this->openDiscrepancyFor($p),
            'Only a correction naming the disagreement resolves the disagreement.'
        );
    }

    /* ── Controlled PRN transaction composition ─────────────────────────── */

    /**
     * A failure anywhere in the episode leaves nothing behind.
     *
     * The whole episode — attempt claim, guards, balance lock, movement,
     * administration, attempt consumption, follow-up — is ONE transaction on
     * one connection. There is no nested or independent transaction anywhere
     * in it, so a failure at any point unwinds all of it together.
     */
    public function test_a_controlled_prn_episode_is_one_transaction(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $registerBefore = CdRegister::where('client_id', $person->id)->count();
        $adminBefore = Administration::where('prescription_id', $p->id)->count();
        $balanceBefore = $this->registry()->balanceFor($person, $this->snap($p))->current_balance;

        $token = $this->mintToken($p);

        // A dose larger than the balance fails AFTER the attempt is claimed and
        // the balance is locked, but before anything is written.
        try {
            $this->cd()->give(
                $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
                99.0, $this->witnessIdIfNeeded(), 'observed_distress', null, request(), $token
            );
            $this->fail('A dose larger than the balance was recorded.');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertSame($registerBefore, CdRegister::where('client_id', $person->id)->count(),
            'No movement survived the failure.');
        $this->assertSame($adminBefore, Administration::where('prescription_id', $p->id)->count(),
            'No administration survived the failure.');
        $this->assertSame($balanceBefore,
            $this->registry()->balanceFor($person, $this->snap($p))->current_balance,
            'The balance is exactly where it was.');
        $this->assertFalse(PrnAttempt::where('token', $token)->firstOrFail()->isSpent(),
            'And the attempt was not spent, so it can be used properly.');

        // The same attempt then works for a permitted dose.
        $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
            1.0, $this->witnessIdIfNeeded(), 'observed_distress', null, request(), $token
        );

        $this->assertSame($adminBefore + 1, Administration::where('prescription_id', $p->id)->count());
        $this->assertTrue(PrnAttempt::where('token', $token)->firstOrFail()->isSpent());
    }

    /** Replay of a controlled PRN attempt: one administration, one movement. */
    public function test_replaying_a_controlled_prn_makes_one_movement_and_one_record(): void
    {
        $this->signInWithoutRound();
        $p = $this->freshControlled($this->lorazepam(), openingStock: 10);
        $person = Client::findOrFail($p->client_id);

        $movementsBefore = CdRegister::where('client_id', $person->id)
            ->where('action', 'administration')->count();

        $token = $this->mintToken($p);

        $first = $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
            1.0, $this->witnessIdIfNeeded(), 'observed_distress', null, request(), $token
        );

        $second = $this->cd()->give(
            $this->user('noah.williams'), $this->oakwood(), $person, $p, null,
            1.0, $this->witnessIdIfNeeded(), 'observed_distress', null, request(), $token
        );

        $this->assertTrue($first['created']);
        $this->assertFalse($second['created'], 'The replay must not create.');
        $this->assertSame($first['administration']->id, $second['administration']->id);
        $this->assertSame($first['entry']->id, $second['entry']->id);

        $this->assertSame(1, Administration::where('prescription_id', $p->id)->count());
        $this->assertSame(
            $movementsBefore + 1,
            CdRegister::where('client_id', $person->id)->where('action', 'administration')->count(),
            'One clinical act, one register movement, however many times it arrives.'
        );

        // And exactly one ask-back.
        $this->assertSame(
            1,
            PrnFollowUp::whereIn('administration_id',
                Administration::where('prescription_id', $p->id)->pluck('id'))->count()
        );
    }
}
