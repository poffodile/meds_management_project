<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Care One OS — demo dataset ("NEPTUNE HOUSE" build).
 * =====================================================================
 * THIS DATABASE IS THE **NEPTUNE HOUSE** DEMO DATASET.
 *
 * Company (tenant)      : home.admin_id = 1
 *   - Neptune House (101) : CHILDREN & YOUNG PEOPLE'S home   <-- primary focus
 *   - Aries House    (8)  : ADULT SUPPORTED LIVING           <-- proves both markets + manager house-switching
 *   - Station Road   (1)  : deleted home; orphaned med sheets removed
 * Home1 (92) belongs to a DIFFERENT company (admin_id = 131) and is deliberately
 * left untouched as a TENANT-SEPARATION negative test: it must never appear for company 1.
 *
 * WHY THIS EXISTS
 * ---------------
 * The original dummy data was not fit to demo or to test against:
 *   - Warfarin 25mg x2 flagged as a Schedule 2 controlled drug (warfarin is NOT a CD, and
 *     50mg is a dangerous dose); Cetirizine flagged as a CD; Metformin marked PRN.
 *   - "Test"/"test" placeholder medicines; residents called "L H", "S C", "Test Child";
 *     Aries residents with 2025/2026 dates of birth (babies) and triplicate names.
 *   - care_plan_risks held ONE placeholder row ("Risk Description"/"test") — which is why the
 *     risk_flags render crash in MedicationRound.jsx:208 stayed invisible.
 * Sparse/wrong data hides defects. This seed deliberately covers the edge cases that matter.
 *
 * DELIBERATE TEST CASES SEEDED (do not "tidy" these away):
 *   - NKDA allergy strings          -> exercises the no-allergy regex (MedicationRound.jsx:36)
 *   - active care_plan_risks         -> exercises the risk_flags render path (the crash)
 *   - PRN at daily maximum           -> buildRoundProps() must return blocked=true
 *   - PRN inside its interval        -> buildRoundProps() must return blocked=true
 *   - low stock and ZERO stock       -> low_stock flag + "Given" against no stock
 *   - a very long medicine name      -> layout/truncation
 *   - a discontinued sheet           -> must NOT appear in the round
 *   - correctly-scheduled CDs        -> Methylphenidate (Sch 2), Morphine (Sch 2)
 *   - time-critical medicines        -> Levetiracetam, Co-careldopa
 *
 * SAFETY / SCOPE
 * --------------
 *  - DEV/DEMO ONLY. Refuses to run unless the expected demo homes are present.
 *  - Idempotent: safe to re-run. Rewrites only company 1's medication demo data.
 *  - Clinical content is realistic but is NOT clinically approved. It is demo/test data and
 *    must be reviewed by a pharmacist / medication lead before being shown as exemplary.
 *
 * ⚠️ KNOWN LIMITATIONS — DO NOT TREAT THIS DATA AS EXEMPLARY UNTIL RESOLVED
 * -------------------------------------------------------------------------
 *  1. PAEDIATRIC DOSING IS NOT CLINICALLY VALID. Every child's PRN here uses a flat
 *     "max 4 in 24h / 4h interval". Real paediatric dosing is WEIGHT- and AGE-BANDED
 *     (per BNFc), not one figure for a 7-year-old and a 17-year-old alike. Resident
 *     weights are seeded so a pharmacist CAN band these properly — but the actual
 *     figures must come from a pharmacist reading the primary BNFc. They are NOT
 *     invented here. Neptune spans ages ~7-17, so this matters.
 *  2. ARIES CD REQUIREMENTS ARE UNRESOLVED. CQC's confirmed position is that a person's
 *     OWN HOME — including a supported-living tenancy — needs NO CD register and NO legal
 *     second-signature witness; the strict register/running-balance/witness convention
 *     attaches to registered CARE HOMES. Aries' actual registration category is unknown,
 *     so the Morphine entry below ASSUMES the strict model. If Aries is a supported-living
 *     tenancy, that assumption is wrong. Needs a compliance/human answer — do not infer it
 *     from the name "Aries House".
 *
 *   php artisan db:seed --class=NeptuneHouseDemoSeeder
 */
class NeptuneHouseDemoSeeder extends Seeder
{
    private const NEPTUNE = 101;      // children & young people
    private const ARIES = 8;          // adult supported living
    private const STATION_ROAD = 1;   // deleted home — clean orphans

    /** Staff used for attribution (verified to exist: Phil Holt = manager @ Neptune). */
    private const NEPTUNE_STAFF = 427;
    private const ARIES_STAFF = 219;

    public function run(): void
    {
        $this->guard();

        DB::transaction(function () {
            $this->cleanOrphans();
            $this->seedNeptuneResidents();
            $this->seedAriesResidents();

            // Wipe only company-1 demo medication data, then rebuild it.
            DB::table('mar_administrations')->whereIn('home_id', [self::NEPTUNE, self::ARIES])->delete();
            DB::table('mar_sheets')->whereIn('home_id', [self::NEPTUNE, self::ARIES])->delete();
            DB::table('care_plan_risks')->whereIn('home_id', [self::NEPTUNE, self::ARIES])->delete();

            $this->seedNeptuneMeds();
            $this->seedAriesMeds();
            $this->seedRisks();
            $this->seedBlockedPrnAdministrations();
            // Inside the transaction: if the fixtures don't demonstrate what they claim,
            // roll the whole thing back rather than hand back misleading demo data.
            $this->verifyFixtures();
        });

        $this->command->info('Neptune House demo dataset seeded.');
        $this->command->info('  Neptune House (101) — children & young people');
        $this->command->info('  Aries House    (8)  — adult supported living');
        $this->command->warn('  Demo data only — not clinically approved. Pharmacist review required.');
    }

    /** Refuse to run against anything that is not the expected demo database. */
    private function guard(): void
    {
        foreach ([self::NEPTUNE => 'Neptune House', self::ARIES => 'Aries House'] as $id => $title) {
            $row = DB::table('home')->where('id', $id)->first();
            if (! $row || stripos($row->title, explode(' ', $title)[0]) === false) {
                throw new \RuntimeException(
                    "Refusing to seed: home {$id} is not {$title}. This seeder is for the Neptune House demo database only."
                );
            }
        }
    }

    /** Station Road (id 1) is a deleted home still holding medication sheets. */
    private function cleanOrphans(): void
    {
        $ids = DB::table('mar_sheets')->where('home_id', self::STATION_ROAD)->pluck('id');
        if ($ids->isNotEmpty()) {
            DB::table('mar_administrations')->whereIn('mar_sheet_id', $ids)->delete();
            DB::table('mar_sheets')->whereIn('id', $ids)->delete();
        }

        // The controlled-drug register contains entries for medicines that are NOT
        // controlled drugs (Paracetamol, Warfarin) — the same misclassification that was
        // in mar_sheets, but in a second table where it survived the first clean-up.
        // A CD register listing paracetamol is not a cosmetic problem: the register is a
        // legal record (Misuse of Drugs Regulations 2001 reg 19) and its running balance
        // is meaningless if non-CDs are in it. Remove only our demo homes' bad rows.
        if (Schema::hasTable('controlled_drug_register')) {
            DB::table('controlled_drug_register')
                ->whereIn('home_id', [self::NEPTUNE, self::ARIES])
                ->delete();
        }
    }

    // ---------------------------------------------------------------- residents

    /**
     * Neptune House — children & young people.
     *
     * Age spread is deliberate and covers the owner's real intake (~6 to late teens) AND
     * the whole consent spectrum, because consent frame is driven by AGE, not house type
     * (REQ-MED-109/111):
     *   - Maya (7)      -> youngest; paediatric weight/age-banded dosing matters most here
     *   - 10-15         -> Gillick competence / parental-responsibility consent
     *   - Susanna (16)  -> MCA-and-Gillick OVERLAP band  <-- precedence UNVERIFIED (legal review)
     *   - Samuel (17)   -> overlap band; ages out to adult (MCA) within a year -> transition test
     * Weights are set because paediatric dosing is weight-based; they are demo values.
     */
    private function seedNeptuneResidents(): void
    {
        $residents = [
            // id,  name,                  dob,          room, gender, weight, allergies,               diet,             mobility
            [177, 'Samuel Cooper',       '2009-03-17', '18', 'M', '62', '',                      'No special diet', 'Independent'],
            [233, 'Susanna Rose Craven', '2009-11-02', '34', 'F', '54', 'NKDA',                  'Vegetarian',      'Independent'],
            [235, 'Darren Smith',        '2011-02-05', '36', 'M', '58', '',                      'No special diet', 'Independent'],
            [243, 'Amelia Hughes',       '2014-04-12', 'C1', 'F', '38', 'Penicillin',            'No special diet', 'Independent'],
            [244, 'Jacob Bennett',       '2012-09-03', 'C2', 'M', '45', 'NKDA',                  'No special diet', 'Independent'],
            [245, 'Sofia Martins',       '2015-11-20', 'C3', 'F', '32', 'Tree nuts, Latex',      'Nut-free',        'Independent'],
            [246, 'Leo Walsh',           '2013-06-15', 'C4', 'M', '44', '',                      'No special diet', 'Independent'],
            [247, 'Maya Patel',          '2019-02-28', 'C5', 'F', '22', 'Lactose intolerance',   'Lactose-free',    'Independent'],
        ];

        foreach ($residents as [$id, $name, $dob, $room, $gender, $weight, $allergies, $diet, $mobility]) {
            $this->updateResident($id, self::NEPTUNE, compact('name', 'dob', 'room', 'gender', 'weight', 'allergies', 'diet', 'mobility'));
        }

        // "L H" (175) is already deleted — leave it deleted.
    }

    /** Aries House — ADULT supported living. Replaces the test-spam residents. */
    private function seedAriesResidents(): void
    {
        $residents = [
            [180, 'Alex Sheffield',      '1991-03-20', '21', 'M', '84', '',                    'No special diet', 'Independent'],
            [186, 'Margaret Whitfield',  '1943-08-12', '27', 'F', '61', 'Penicillin',          'Diabetic',        'Walks with frame'],
            [190, 'Raymond Chalmers',    '1951-01-27', '31', 'M', '78', 'NKDA',                'Soft diet',       'Walks with stick'],
            [191, 'Doreen Baptiste',     '1938-06-04', '32', 'F', '52', 'Codeine',             'Pureed (Level 4)', 'Wheelchair'],
            [192, 'Ian Hollis',          '1962-11-19', '33', 'M', '95', '',                    'Diabetic',        'Independent'],
            [193, 'Patricia Nwosu',      '1955-04-30', '34', 'F', '67', 'Sulfonamides',        'No special diet', 'Independent'],
        ];

        foreach ($residents as [$id, $name, $dob, $room, $gender, $weight, $allergies, $diet, $mobility]) {
            $this->updateResident($id, self::ARIES, compact('name', 'dob', 'room', 'gender', 'weight', 'allergies', 'diet', 'mobility'));
        }

        // Retire the obvious developer test accounts rather than leaving them in a demo.
        DB::table('service_user')
            ->where('home_id', self::ARIES)
            ->where(function ($q) {
                $q->where('name', 'like', 'Test %')->orWhere('name', 'like', '% Test %')->orWhere('name', 'Ethan');
            })
            ->update(['is_deleted' => 1]);

        // Any remaining duplicate/baby records in Aries that we haven't repurposed.
        DB::table('service_user')
            ->where('home_id', self::ARIES)
            ->whereNotIn('id', [180, 186, 190, 191, 192, 193])
            ->update(['is_deleted' => 1]);
    }

    private function updateResident(int $id, int $homeId, array $d): void
    {
        $exists = DB::table('service_user')->where('id', $id)->exists();
        if (! $exists) {
            return; // never invent residents — only correct the ones already here
        }

        $payload = [
            'home_id' => $homeId,
            'name' => $d['name'],
            'date_of_birth' => $d['dob'],
            'room_number' => $d['room'],
            'gender' => $d['gender'],
            'weight' => $d['weight'],
            'allergies' => $d['allergies'],
            'is_deleted' => 0,
            'status' => 1,
        ];
        // These columns exist in this schema but guard anyway (schema-from-dump).
        if (Schema::hasColumn('service_user', 'weight_unit')) {
            $payload['weight_unit'] = 'kg';
        }
        if (Schema::hasColumn('service_user', 'diet')) {
            $payload['diet'] = $d['diet'];
        }
        if (Schema::hasColumn('service_user', 'suMobility')) {
            $payload['suMobility'] = $d['mobility'];
        }

        DB::table('service_user')->where('id', $id)->update($payload);
        // NOTE: photos are intentionally NOT faked. Resident photo is a safety control
        // (REQ-MED-02) and pointing at image files that do not exist would render broken
        // images — worse than an honest initials avatar. Real photos need real files.
    }

    // ---------------------------------------------------------------- medication

    /**
     * Neptune House — children & young people.
     * Columns: client, name, strength, form, dose, unit, route, slots, prn, [max,interval], cd_schedule, stock, reorder, reason, status
     *
     * ⚠️⚠️ PAEDIATRIC DOSING — ILLUSTRATIVE DEMO VALUES, NOT CLINICALLY VERIFIED ⚠️⚠️
     * ------------------------------------------------------------------------------
     * Unlike adults (one rule: paracetamol 500mg-1g every 4-6h, max 4g/24h, same at 35
     * or 88), children are dosed by AGE and WEIGHT. Maya is 7 (~22kg); Samuel is 17
     * (~62kg) — they must not share a dose. The doses below are therefore BANDED by each
     * child's age/weight rather than being one flat figure, so the data has the correct
     * SHAPE to design and test against (PRN limits, blocked states, dose display).
     *
     * They are NOT taken from the BNFc and they are NOT clinically approved. They are
     * demo values chosen to be structurally realistic. A pharmacist MUST replace/verify
     * every paediatric dose against the primary BNFc before this data is shown to any
     * clinician, used in a demo to a care provider, or treated as exemplary. Resident
     * weights are seeded specifically so a pharmacist CAN band them properly.
     *
     * Banding used (illustrative only — paracetamol 250mg/5ml suspension):
     *    ~6-8y   -> 250mg  (5 ml)
     *    ~8-10y  -> 375mg  (7.5 ml)
     *    ~10-12y -> 500mg  (10 ml)
     *    ~12-16y -> 500mg  (1 tablet)
     *    16y+    -> 1g     (2 tablets)  [adult dose]
     * Ibuprofen 100mg/5ml suspension:
     *    ~7-9y   -> 200mg  (10 ml)
     *    ~10-11y -> 300mg  (15 ml)
     *
     * PRN max/24h is kept at 4 for paracetamol (the frequency ceiling is broadly the same
     * across these ages); it is the DOSE SIZE that varies most. Ibuprofen uses 3/24h.
     */
    /**
     * Prove the fixtures actually demonstrate what they claim, before declaring success.
     *
     * WHY THIS EXISTS. These rows exist to demonstrate the PRN daily-maximum and
     * minimum-interval blocks. For a whole day they demonstrated nothing: the seeder
     * never wrote `administered_at`, which is the column the interval maths reads.
     * The fixtures appeared to work only because a migration backfilled that column
     * AFTER the seeder had run. A clean seed left it NULL, `$last` became null,
     * `$tooSoon` became false, and the block silently died — while the demo, the tests
     * and the fix plan all still said it worked.
     *
     * Seeded data that silently stops demonstrating its own point is worse than no
     * seeded data, because it manufactures false confidence. So the seeder now fails
     * loudly rather than hand back a dataset that quietly proves nothing.
     */
    private function verifyFixtures(): void
    {
        $bad = DB::table('mar_administrations as a')
            ->join('mar_sheets as s', 's.id', '=', 'a.mar_sheet_id')
            ->whereIn('s.home_id', [self::NEPTUNE, self::ARIES])
            ->whereNull('a.administered_at')
            ->count();

        if ($bad > 0) {
            throw new \LogicException(
                "Seeder wrote {$bad} administration(s) with a NULL administered_at. "
                .'The PRN interval block reads that column, so these fixtures would prove nothing. '
                .'Refusing to hand back a dataset that silently disables the safety rule it exists to demonstrate.'
            );
        }

        $nullQty = DB::table('mar_sheets')
            ->whereIn('home_id', [self::NEPTUNE, self::ARIES])
            ->where('is_deleted', 0)
            ->whereNull('dose_quantity')
            ->count();

        if ($nullQty > 0) {
            throw new \LogicException(
                "Seeder left {$nullQty} prescription(s) with a NULL dose_quantity. "
                .'Stock would never decrement for those medicines and the gap would be invisible.'
            );
        }
    }

    /**
     * How much stock ONE dose consumes, for the dose strings this seeder writes.
     *
     * Every value here is an explicit reading of a dose string that a regex cannot
     * safely resolve. They are listed rather than parsed because the audit (CR-02)
     * found stock deduction was being derived by stripping non-digits out of the
     * dose text — '10 ml (500mg)' became 10500, and one tap zeroed a real balance.
     *
     * These are demo values the seeder itself authored, so reading them back is
     * bookkeeping, not a clinical judgement. REAL tenant data must NOT be mapped
     * this way — it needs a pharmacist-reviewed mapping against the medicine
     * catalogue (audit DA-04), because '10 ml (500mg)' and '500mg (10ml)' are
     * indistinguishable to a parser and mean different things.
     */
    private const DOSE_QUANTITY_OVERRIDES = [
        // Two numbers: volume + strength. The consumed quantity is the VOLUME.
        '10 ml (500mg)' => 10.0,
        '7.5 ml (375mg)' => 7.5,
        '10 ml (200mg)' => 10.0,
        // Two numbers: count + strength. The consumed quantity is the COUNT.
        '1 tablet (500mg)' => 1.0,
        // Distributive: ONE spray per nostril consumes TWO sprays. The single
        // number in the string is per-site, not per-dose — the exact trap the
        // conservative backfill refuses to guess at.
        '1 spray each nostril' => 2.0,
    ];

    /** Resolve a dose string to a per-dose quantity, or null if it cannot be known. */
    private function doseQuantityFor(string $dose): ?float
    {
        $dose = trim($dose);

        if (array_key_exists($dose, self::DOSE_QUANTITY_OVERRIDES)) {
            return self::DOSE_QUANTITY_OVERRIDES[$dose];
        }

        // Otherwise only accept an unambiguous single number with no distributive wording.
        if (preg_match('/\b(each|per|both|every)\b/i', $dose)) {
            return null;
        }
        preg_match_all('/\d+(?:\.\d+)?/', $dose, $matches);
        if (count($matches[0]) !== 1) {
            return null;
        }
        $qty = (float) $matches[0][0];

        return $qty > 0 ? $qty : null;
    }

    private function seedNeptuneMeds(): void
    {
        $meds = [
            // Amelia Hughes (12) — epilepsy + asthma. PENICILLIN allergy.
            [243, 'Levetiracetam 250mg tablets', '250mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '20:00'], false, null, null, 56, 14, 'Epilepsy — seizure prevention. TIME CRITICAL: do not delay.', 'active'],
            [243, 'Salbutamol 100micrograms/dose inhaler', '100mcg', 'Inhaler', '2 puffs', 'puff', 'Inhaled', ['08:00'], true, [4, 4.0], null, 12, 5, 'Asthma — reliever. Use for wheeze or breathlessness.', 'active'],
            [243, 'Paracetamol 250mg/5ml oral suspension', '250mg/5ml', 'Oral suspension', '10 ml (500mg)', 'ml', 'Oral', ['12:00'], true, [4, 4.0], null, 60, 20, 'Pain or fever. Age/weight-banded: 12y, 38kg. Max 4 doses in 24h, min 4h apart.', 'active'],

            // Jacob Bennett (13) — ADHD. NKDA (tests the no-allergy regex).
            [244, 'Methylphenidate 10mg tablets', '10mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '12:00'], false, null, '2', 28, 10, 'ADHD — supports focus during the school day.', 'active'],
            [244, 'Melatonin 2mg modified-release tablets', '2mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['20:00'], false, null, null, 68, 10, 'Sleep-onset difficulty.', 'active'],

            // Sofia Martins (10) — antibiotic course. LOW STOCK test.
            [245, 'Amoxicillin 250mg/5ml oral suspension', '250mg/5ml', 'Oral suspension', '5 ml', 'ml', 'Oral', ['08:00', '14:00', '20:00'], false, null, null, 3, 5, 'Chest infection — 7 day course. Complete the course.', 'active'],
            [245, 'Paracetamol 250mg/5ml oral suspension', '250mg/5ml', 'Oral suspension', '7.5 ml (375mg)', 'ml', 'Oral', ['12:00'], true, [4, 4.0], null, 60, 20, 'Pain or fever. Age/weight-banded: 10y, 32kg. Max 4 doses in 24h, min 4h apart.', 'active'],

            // Leo Walsh (13) — ADHD + hay fever. Cetirizine is NOT a controlled drug (was wrongly flagged).
            [246, 'Methylphenidate 10mg tablets', '10mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '12:00'], false, null, '2', 20, 10, 'ADHD — supports focus during the school day.', 'active'],
            [246, 'Cetirizine 5mg tablets', '5mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 40, 10, 'Hay fever / allergic rhinitis.', 'active'],
            [246, 'Salbutamol 100micrograms/dose inhaler', '100mcg', 'Inhaler', '2 puffs', 'puff', 'Inhaled', ['12:00'], true, [4, 4.0], null, 8, 5, 'Asthma — reliever.', 'active'],

            // Maya Patel (10) — reflux. ZERO STOCK test on the PRN.
            [247, 'Omeprazole 10mg gastro-resistant capsules', '10mg', 'Capsule', '1 capsule', 'capsule', 'Oral', ['08:00'], false, null, null, 28, 10, 'Acid reflux.', 'active'],
            [247, 'Ibuprofen 100mg/5ml oral suspension', '100mg/5ml', 'Oral suspension', '10 ml (200mg)', 'ml', 'Oral', ['14:00'], true, [3, 6.0], null, 0, 15, 'Pain or inflammation. Age/weight-banded: 7y, 22kg. Give with food. Max 3 doses in 24h, min 6h apart.', 'active'],

            // Susanna Rose Craven (16) — PRN AT DAILY MAX (administrations seeded below).
            [233, 'Sertraline 50mg tablets', '50mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 30, 10, 'Low mood / anxiety.', 'active'],
            [233, 'Paracetamol 500mg tablets', '500mg', 'Tablet', '2 tablets', 'tablet', 'Oral', ['12:00'], true, [4, 4.0], null, 44, 12, 'Pain or fever. Max 4 doses in 24 hours.', 'active'],

            // Darren Smith (15) — PRN INSIDE INTERVAL (administration seeded below).
            [235, 'Risperidone 500microgram tablets', '500mcg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '20:00'], false, null, null, 56, 14, 'Prescribed by CAMHS — review date monthly.', 'active'],
            [235, 'Paracetamol 500mg tablets', '500mg', 'Tablet', '1 tablet (500mg)', 'tablet', 'Oral', ['12:00'], true, [4, 4.0], null, 41, 8, 'Pain or fever. Age/weight-banded: 15y, 58kg. Max 4 doses in 24h, min 4h apart.', 'active'],

            // Samuel Cooper (17) — LONG NAME test + a DISCONTINUED sheet (must not show in the round).
            [177, 'Fluticasone propionate 50 micrograms/dose aqueous nasal spray suspension', '50mcg/dose', 'Nasal spray', '1 spray each nostril', 'spray', 'Nasal', ['08:00'], false, null, null, 1, 1, 'Allergic rhinitis — long medicine name, checks layout/truncation.', 'active'],
            [177, 'Loratadine 10mg tablets', '10mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 12, 5, 'Discontinued — switched to fluticasone.', 'discontinued'],
        ];

        $this->insertMeds($meds, self::NEPTUNE, self::NEPTUNE_STAFF);
    }

    /** Aries House — ADULT supported living. */
    private function seedAriesMeds(): void
    {
        $meds = [
            // Alex Sheffield (35) — epilepsy.
            [180, 'Sodium valproate 500mg gastro-resistant tablets', '500mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '20:00'], false, null, null, 56, 14, 'Epilepsy. TIME CRITICAL: do not delay.', 'active'],
            [180, 'Paracetamol 500mg tablets', '500mg', 'Tablet', '2 tablets', 'tablet', 'Oral', ['12:00'], true, [4, 4.0], null, 32, 10, 'Pain or fever. Max 4 doses in 24 hours.', 'active'],

            // Margaret Whitfield (82) — diabetes + thyroid. Metformin is REGULAR, not PRN (was wrongly PRN).
            [186, 'Metformin 500mg tablets', '500mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '18:00'], false, null, null, 56, 14, 'Type 2 diabetes. Take with food.', 'active'],
            [186, 'Levothyroxine 75microgram tablets', '75mcg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 28, 10, 'Underactive thyroid. Take 30 min before food.', 'active'],

            // Raymond Chalmers (75) — Parkinson's + anticoagulation. Warfarin is NOT a CD (was wrongly Sch 2 at 50mg).
            [190, 'Co-careldopa 25mg/100mg tablets', '25mg/100mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '12:00', '16:00', '20:00'], false, null, null, 84, 20, "Parkinson's disease. TIME CRITICAL: give on time, do not delay.", 'active'],
            [190, 'Warfarin 3mg tablets', '3mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['18:00'], false, null, null, 28, 10, 'Anticoagulation. Dose per INR — check yellow book before giving.', 'active'],

            // Doreen Baptiste (88) — palliative. Morphine IS correctly a Schedule 2 CD.
            [191, 'Morphine sulfate 10mg/5ml oral solution', '10mg/5ml', 'Oral solution', '5 ml', 'ml', 'Oral', ['12:00'], true, [6, 4.0], '2', 100, 30, 'Breakthrough pain. Witness required. Record in CD register.', 'active'],
            [191, 'Donepezil 10mg tablets', '10mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['20:00'], false, null, null, 28, 10, "Alzheimer's disease.", 'active'],

            // Ian Hollis (63) — diabetes + cholesterol.
            [192, 'Gliclazide 80mg tablets', '80mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 28, 10, 'Type 2 diabetes. Monitor for hypoglycaemia.', 'active'],
            [192, 'Atorvastatin 20mg tablets', '20mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['20:00'], false, null, null, 28, 10, 'High cholesterol.', 'active'],

            // Patricia Nwosu (71) — anticoagulation + hypertension.
            [193, 'Apixaban 5mg tablets', '5mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00', '20:00'], false, null, null, 56, 14, 'Anticoagulation — atrial fibrillation.', 'active'],
            [193, 'Amlodipine 5mg tablets', '5mg', 'Tablet', '1 tablet', 'tablet', 'Oral', ['08:00'], false, null, null, 28, 10, 'High blood pressure.', 'active'],
        ];

        $this->insertMeds($meds, self::ARIES, self::ARIES_STAFF);
    }

    private function insertMeds(array $meds, int $homeId, int $staffId): void
    {
        $now = now();
        $hasForm = Schema::hasColumn('mar_sheets', 'form');
        $hasUnit = Schema::hasColumn('mar_sheets', 'unit');

        foreach ($meds as $i => $m) {
            // Positional rows are easy to mis-count, and a silent off-by-one writes the
            // WRONG VALUE INTO THE WRONG COLUMN — e.g. a clinical reason landing in
            // mar_status. That only surfaced here because the string was too long for
            // varchar(20); a shorter one would have been written silently. Fail loudly.
            if (count($m) !== 15) { // NB: dose_quantity is resolved from $dose, not a 16th field.
                throw new \LogicException(sprintf(
                    'Medication row %d ("%s") has %d fields, expected 15. Refusing to seed misaligned clinical data.',
                    $i, $m[1] ?? '?', count($m)
                ));
            }

            [$client, $name, $strength, $form, $dose, $unit, $route, $slots, $prn, $prnLimits, $cdSchedule, $stock, $reorder, $reason, $status] = $m;

            if ($this->doseQuantityFor($dose) === null) {
                throw new \LogicException(sprintf(
                    'Medication row %d ("%s") has dose "%s" with no resolvable dose_quantity. '
                    .'Add it to DOSE_QUANTITY_OVERRIDES — do not let it seed as NULL, or its stock will never decrement.',
                    $i, $name, $dose
                ));
            }

            if (! in_array($status, ['active', 'discontinued'], true)) {
                throw new \LogicException("Medication row {$i} has invalid mar_status '{$status}'.");
            }

            $row = [
                'home_id' => $homeId,
                'client_id' => $client,
                'medication_name' => $name,
                'dosage' => $strength,
                'dose' => $dose,
                'dose_quantity' => $this->doseQuantityFor($dose),
                'route' => $route,
                'time_slots' => json_encode($slots),
                'as_required' => $prn ? 1 : 0,
                'prn_details' => $prn ? $reason : null,
                'prn_max_daily' => $prn ? $prnLimits[0] : null,
                'prn_min_interval_hours' => $prn ? $prnLimits[1] : null,
                'reason_for_medication' => $reason,
                // is_controlled is DERIVED from the schedule — never typed independently.
                // (REQ-MED-107: coded reference data should ultimately supply this.)
                'is_controlled' => $cdSchedule !== null ? 1 : 0,
                'cd_schedule' => $cdSchedule,
                'stock_level' => $stock,
                'reorder_level' => $reorder,
                'mar_status' => $status,
                'discontinued' => $status === 'discontinued' ? 1 : 0,
                'created_by' => $staffId,
                'is_deleted' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($hasForm) {
                $row['form'] = $form;
            }
            if ($hasUnit) {
                $row['unit'] = $unit;
            }

            DB::table('mar_sheets')->insert($row);
        }
    }

    // ---------------------------------------------------------------- risks

    /**
     * Medication-relevant clinical risks.
     *
     * NOTE: buildRoundProps() reads risks from `care_plan_risks`, which held a single
     * placeholder row — while 365 real rows sit in the legacy `su_risk`/`risk` tables whose
     * taxonomy is youth SAFEGUARDING ("Gang Affiliated", "Harm to Self"), not medication
     * safety. Neither served the round. These rows are what a medication round actually needs.
     */
    private function seedRisks(): void
    {
        $risks = [
            // home,        client, description,                                                                     likelihood, impact
            [self::NEPTUNE, 243, 'Epilepsy — risk of seizure if Levetiracetam is missed or given late. Time critical.', 'Medium', 'High'],
            [self::NEPTUNE, 243, 'Penicillin allergy — do NOT administer penicillin-class antibiotics (incl. amoxicillin).', 'Low', 'High'],
            [self::NEPTUNE, 245, 'Swallowing difficulty — oral suspensions preferred, do not crush tablets.', 'Medium', 'Medium'],
            [self::NEPTUNE, 244, 'ADHD — medication timing critical for the school day.', 'Medium', 'Medium'],
            [self::NEPTUNE, 233, 'Self-harm risk — medicines to be stored securely; no stockpiling; observe administration.', 'Medium', 'High'],
            [self::NEPTUNE, 235, 'Antipsychotic — monitor for side effects; CAMHS review monthly.', 'Low', 'Medium'],
            [self::ARIES,   190, "Parkinson's — time-critical medication. Delay causes loss of mobility and swallow.", 'Medium', 'High'],
            [self::ARIES,   190, 'Anticoagulated (warfarin) — bleeding risk. Check INR/yellow book before giving.', 'Medium', 'High'],
            [self::ARIES,   191, 'Falls risk — sedating medicines; assist when mobilising.', 'High', 'High'],
            [self::ARIES,   191, 'Pureed diet (IDDSI Level 4) — do not give whole tablets; check formulation.', 'Medium', 'High'],
            [self::ARIES,   186, 'Diabetes — monitor for hypoglycaemia, especially if a meal is refused.', 'Medium', 'Medium'],
            [self::ARIES,   192, 'Diabetes — monitor for hypoglycaemia.', 'Medium', 'Medium'],
        ];

        $now = now();
        foreach ($risks as [$homeId, $client, $desc, $likelihood, $impact]) {
            DB::table('care_plan_risks')->insert([
                'home_id' => $homeId,
                'client_id' => $client,
                'user_id' => $homeId === self::NEPTUNE ? self::NEPTUNE_STAFF : self::ARIES_STAFF,
                'overview_id' => 1,
                'emergency_information_id' => 1,
                'description' => $desc,
                'likelihood' => $likelihood,
                'impact' => $impact,
                'control_measures' => 'See care plan. Escalate to the manager and prescriber if concerns.',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Seed administrations that put two PRNs into a BLOCKED state, so the round's
     * PRN safety logic (buildRoundProps -> prn.blocked / block_reason) actually fires.
     */
    private function seedBlockedPrnAdministrations(): void
    {
        $today = now()->toDateString();

        // Susanna (233) paracetamol PRN: max_daily = 4 -> give 4 today => "Daily maximum reached".
        $atMax = DB::table('mar_sheets')
            ->where('home_id', self::NEPTUNE)->where('client_id', 233)
            ->where('as_required', 1)->first();

        if ($atMax) {
            foreach (['08:10', '12:15', '16:20', '20:05'] as $i => $t) {
                $at = now()->subHours(12 - ($i * 3));
                DB::table('mar_administrations')->insert([
                    'mar_sheet_id' => $atMax->id,
                    'home_id' => self::NEPTUNE,
                    'date' => $today,
                    'time_slot' => $t,
                    // administered_at is what the PRN interval maths reads. Omitting it
                    // silently disables the "Too soon" block — the very thing these rows
                    // exist to demonstrate. It was omitted, and the fixtures only worked
                    // because a migration backfilled the column AFTER the seeder ran; a
                    // clean seed left it NULL and the block died with no error anywhere.
                    'administered_at' => $at,
                    'given' => 1,
                    'code' => 'A',
                    'dose_given' => $atMax->dose,
                    'quantity_given' => $atMax->dose_quantity,
                    'administered_by' => self::NEPTUNE_STAFF,
                    'notes' => 'PRN given for headache.',
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
        }

        // Darren (235) paracetamol PRN: interval = 4h -> one dose 1h ago => "Not due until ...".
        $tooSoon = DB::table('mar_sheets')
            ->where('home_id', self::NEPTUNE)->where('client_id', 235)
            ->where('as_required', 1)->first();

        if ($tooSoon) {
            $at = now()->subHour();
            DB::table('mar_administrations')->insert([
                'mar_sheet_id' => $tooSoon->id,
                'home_id' => self::NEPTUNE,
                'date' => $today,
                'time_slot' => $at->format('H:i'),
                'administered_at' => $at,     // see the note above — this drives the interval block
                'given' => 1,
                'code' => 'A',
                // Was hardcoded '2 tablets' while the prescription says '1 tablet (500mg)'.
                // A fixture that contradicts its own prescription teaches the wrong number.
                'dose_given' => $tooSoon->dose,
                'quantity_given' => $tooSoon->dose_quantity,
                'administered_by' => self::NEPTUNE_STAFF,
                'notes' => 'PRN given for toothache.',
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }
}
