<?php

namespace Database\Seeders;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ClientAllergy;
use App\Models\Record7\Handover;
use App\Models\Record7\HandoverNote;
use App\Models\Record7\Medicine;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fictional medicines data for Oakwood House.
 *
 * Everyone and everything here is invented. The names are ordinary UK names,
 * the medicines are real medicines used in ordinary ways, and the situations
 * are the ones that actually fill a support worker's shift: a dose that is
 * late, a refusal that needs following up, a medicine the pharmacy has not
 * delivered, a prescription that changed on Monday, and a PRN given at three in
 * the morning that nobody has asked about since.
 *
 * ANCHORED TO NOW, NOT TO A DATE. Doses are generated around the current time
 * so the dashboard is meaningful whenever it is opened, rather than showing an
 * empty day because the fixture was written last Tuesday. Re-running the seeder
 * regenerates today.
 *
 * GUARDED THE SAME WAY AS SECTION 0. Never in production, never without the
 * explicit flag, and never against a database whose name looks like the legacy
 * one.
 */
class Record7Section1Seeder extends Seeder
{
    private const HOUSE = 'Oakwood House';

    /** Everything this seeder writes is findable by this. */
    private const REFERENCE_PREFIX = 'OAK-';

    /** The day's rounds, and how much grace each one gets. */
    private const SLOTS = [
        ['name' => 'Morning', 'hour' => 8, 'minute' => 0, 'grace' => 90],
        ['name' => 'Lunchtime', 'hour' => 12, 'minute' => 30, 'grace' => 60],
        ['name' => 'Teatime', 'hour' => 17, 'minute' => 30, 'grace' => 60],
        ['name' => 'Night', 'hour' => 21, 'minute' => 30, 'grace' => 90],
    ];

    public function run(): void
    {
        $this->refuseUnlessSafe();

        $house = Service::where('name', self::HOUSE)->firstOrFail();
        $organisationId = $house->organisation_id;

        // Who wrote the history. Noah is the person reviewing the dashboard, so
        // the past is somebody else's work — which is what a handover is.
        $olivia = User::where('username', 'olivia.carter')->firstOrFail();
        $noah = User::where('username', 'noah.williams')->firstOrFail();

        // The delete guard has to come off to clear the previous run, and
        // creating or dropping a trigger commits the open transaction out from
        // under you in MySQL. So the trigger swap happens either side of the
        // transaction, never inside it.
        $connection = DB::connection('record7');
        $connection->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_delete');

        try {
            $connection->transaction(function () use ($house, $organisationId, $olivia) {
                $this->seed($house, $organisationId, $olivia);
            });
        } finally {
            $this->restoreDeleteGuard();
        }
    }

    private function seed(Service $house, int $organisationId, User $olivia): void
    {
        $this->clear($house->id);

        $medicines = $this->medicines();
        $clients = $this->clients($house, $organisationId);
        $this->allergies($clients);

        $prescriptions = $this->prescriptions($clients, $medicines);
        $this->day($house, $prescriptions, $olivia);
        $this->overnightPrn($house, $prescriptions, $olivia);
        $this->handover($house, $olivia, $clients);

        $this->command?->info('Oakwood House seeded: '.count($clients).' clients, '
            .count($prescriptions).' prescriptions.');
    }

    /* ── Guards ─────────────────────────────────────────────────────────── */

    private function refuseUnlessSafe(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Fictional data is never seeded in production.');
        }

        if (! filter_var(env('RECORD7_ALLOW_FIXTURE_SEED', false), FILTER_VALIDATE_BOOLEAN)) {
            throw new \RuntimeException(
                'Set RECORD7_ALLOW_FIXTURE_SEED=true to seed the Record7 fixture. '
                .'It is deliberately awkward because it writes invented clinical data.'
            );
        }

        $database = config('database.connections.record7.database');

        if (str_starts_with((string) $database, 'laravel')) {
            throw new \RuntimeException(
                "The record7 connection points at '{$database}', which looks like the legacy "
                .'database. Refusing to write Record7 fixtures into it.'
            );
        }
    }

    /**
     * Clear the previous run so the seeder can be run again.
     *
     * Administrations cannot normally be deleted — that is the whole point of
     * them. The guard is off for the length of this call, which is why the
     * caller puts it straight back in a finally.
     */
    private function clear(int $serviceId): void
    {
        $connection = DB::connection('record7');

        // Found by REFERENCE as well as by house. Re-running the Section 0
        // seeder renumbers the services, which orphans everything this seeder
        // wrote against the old id — a clear-out that trusts the id then finds
        // nothing, and the next insert collides on a reference that is supposed
        // to be unique. The prefix is this seeder's own, so it can always find
        // its own leftovers.
        $clientIds = Client::where('service_id', $serviceId)
            ->orWhere('reference', 'like', self::REFERENCE_PREFIX.'%')
            ->pluck('id');

        if ($clientIds->isEmpty()) {
            return;
        }

        PrnFollowUp::whereIn('client_id', $clientIds)->delete();

        // A review item can name an administration. Deleting the administration
        // and leaving the review item pointing at a vanished id makes the
        // manager queue break the next time somebody opens it — which is how
        // reseeding 1.1 twice broke 1.2's correction request.
        $administrationIds = $connection->table('record7_administrations')
            ->whereIn('client_id', $clientIds)->pluck('id');

        if ($administrationIds->isNotEmpty()) {
            $connection->table('record7_review_items')
                ->where('subject_type', 'administration')
                ->whereIn('subject_id', $administrationIds)
                ->delete();

            $connection->table('record7_issue_states')
                ->whereNotNull('linked_administration_id')
                ->whereIn('linked_administration_id', $administrationIds)
                ->update(['linked_administration_id' => null]);
        }

        // Through the query builder, not the model: the model refuses to delete
        // an administration, and it is right to.
        $connection->table('record7_administrations')->whereIn('client_id', $clientIds)->delete();
        ScheduledDose::whereIn('client_id', $clientIds)->delete();
        Prescription::whereIn('client_id', $clientIds)->delete();
        ClientAllergy::whereIn('client_id', $clientIds)->delete();
        $connection->table('record7_rounds')->where('service_id', $serviceId)->delete();

        // By both routes, for the same reason: a handover written against the
        // previous service id is still holding notes that point at these
        // clients, and a client cannot be deleted while a note references it.
        $handovers = Handover::where('service_id', $serviceId)->pluck('id')
            ->merge(HandoverNote::whereIn('client_id', $clientIds)->pluck('handover_id'))
            ->unique();

        HandoverNote::whereIn('handover_id', $handovers)->delete();
        Handover::whereIn('id', $handovers)->delete();

        Client::whereIn('id', $clientIds)->delete();
    }

    /** Put the permanent-record guarantee back, whatever happened above. */
    private function restoreDeleteGuard(): void
    {
        DB::connection('record7')->unprepared(<<<'SQL'
            CREATE TRIGGER record7_administrations_no_delete
            BEFORE DELETE ON record7_administrations
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'record7 administrations are a permanent record and cannot be deleted';
            END
        SQL);
    }

    /* ── Reference data ─────────────────────────────────────────────────── */

    private function medicines(): array
    {
        $rows = [
            'levodopa' => ['Co-careldopa', '25mg/100mg', 'tablet', false],
            'paracetamol' => ['Paracetamol', '500mg', 'tablet', false],
            'sertraline' => ['Sertraline', '50mg', 'tablet', false],
            'lansoprazole' => ['Lansoprazole', '30mg', 'capsule', false],
            'salbutamol' => ['Salbutamol', '100mcg', 'inhaler', false],
            'levetiracetam' => ['Levetiracetam', '500mg', 'tablet', false],
            'metformin' => ['Metformin', '500mg', 'tablet', false],
            'atorvastatin' => ['Atorvastatin', '20mg', 'tablet', false],
            'lorazepam' => ['Lorazepam', '1mg', 'tablet', true],
            'macrogol' => ['Macrogol', '13.8g', 'sachet', false],
            'colecalciferol' => ['Colecalciferol', '800unit', 'tablet', false],

            // A controlled drug on a REGULAR schedule, not as-required. Every
            // other controlled medicine here is PRN, and as-required is refused
            // before the witness rule is ever reached — so without this the
            // rule that a controlled drug needs a witness is never visible on
            // a round screen.
            'morphine_mr' => ['Morphine sulfate MR', '10mg', 'tablet', true],
            'ferrous' => ['Ferrous fumarate', '210mg', 'tablet', false],
        ];

        $medicines = [];

        foreach ($rows as $key => [$name, $strength, $form, $controlled]) {
            $medicines[$key] = Medicine::firstOrCreate(
                ['name' => $name, 'strength' => $strength],
                ['form' => $form, 'is_controlled' => $controlled]
            );
        }

        return $medicines;
    }

    private function clients(Service $house, int $organisationId): array
    {
        $rows = [
            'margaret' => [
                'OAK-C-001', 'Margaret Whitfield', 'Margaret', '1946-03-14', 'Flat 1', 'active',
                'Likes her morning medicines with tea, not before. Ask before you pour.',
            ],
            'terence' => [
                'OAK-C-002', 'Terence Boyle', 'Terry', '1958-11-02', 'Flat 2', 'active',
                'Parkinson\'s. His timings matter more than the round does — go to him first.',
            ],
            'aisha' => [
                'OAK-C-003', 'Aisha Rahman', 'Aisha', '1991-07-23', 'Flat 3', 'active',
                'Manages her own inhaler. Record it, do not hand it to her.',
            ],
            'dennis' => [
                'OAK-C-004', 'Dennis Okafor', 'Dennis', '1963-01-30', 'Flat 4', 'active',
                'Will say no if he is rushed. Come back in twenty minutes and he usually says yes.',
            ],
            'joyce' => [
                'OAK-C-005', 'Joyce Hartley', 'Joyce', '1939-09-08', 'Flat 5', 'active',
                'Very hard of hearing. Face her when you speak; do not shout from the doorway.',
            ],
            'callum' => [
                'OAK-C-006', 'Callum Fraser', 'Callum', '1988-05-17', 'Flat 6', 'in_hospital',
                // NOT "medicines on hold". That phrase reads at the end of a
                // long shift as "nothing to do here", and each planned dose
                // still has to be answered for. Says the same clinical fact —
                // this service is not giving them — without the implication.
                'Admitted to the Royal on Tuesday. We are not giving his medicines while he is '
                    .'there.',
            ],
        ];

        $clients = [];

        foreach ($rows as $key => [$reference, $full, $preferred, $dob, $room, $status, $note]) {
            $clients[$key] = Client::create([
                'reference' => $reference,
                'organisation_id' => $organisationId,
                'service_id' => $house->id,
                'full_name' => $full,
                'preferred_name' => $preferred,
                'date_of_birth' => $dob,
                'room_name' => $room,
                'status' => $status,
                'support_note' => $note,
            ]);
        }

        return $clients;
    }

    private function allergies(array $clients): void
    {
        $rows = [
            ['margaret', 'Penicillin', 'Widespread rash and swelling', 'severe', 'GP record, 2019'],
            ['dennis', 'Codeine', 'Vomiting and confusion', 'moderate', 'Told us himself, 2024'],
            ['joyce', 'Peanuts', 'Anaphylaxis', 'life_threatening', 'Hospital discharge letter, 2023'],
            ['aisha', 'Ibuprofen', 'Brings on her asthma', 'severe', 'GP record, 2022'],
        ];

        foreach ($rows as [$key, $substance, $reaction, $severity, $source]) {
            ClientAllergy::create([
                'client_id' => $clients[$key]->id,
                'substance' => $substance,
                'reaction' => $reaction,
                'severity' => $severity,
                'source' => $source,
                'recorded_at' => now()->subYears(2),
            ]);
        }
    }

    /* ── Prescriptions ──────────────────────────────────────────────────── */

    private function prescriptions(array $clients, array $medicines): array
    {
        $today = Carbon::today();
        $made = [];

        // [key, client, medicine, dose, route, frequency, slots, options]
        $rows = [
            ['margaret-sertraline', 'margaret', 'sertraline', 'One tablet', 'Oral', 'Once a day', ['Morning'], []],
            ['margaret-atorvastatin', 'margaret', 'atorvastatin', 'One tablet', 'Oral', 'Once a day at night', ['Night'], []],
            ['margaret-colecalciferol', 'margaret', 'colecalciferol', 'One tablet', 'Oral', 'Once a day', ['Morning'], []],

            // The reason time-critical exists as a concept in this product.
            ['terence-levodopa', 'terence', 'levodopa', 'One tablet', 'Oral', 'Four times a day',
                ['Morning', 'Lunchtime', 'Teatime', 'Night'], [
                    'is_time_critical' => true,
                    'grace' => 30,
                    'instructions' => 'Give within 30 minutes of the time shown. Do not save it for the round.',
                ]],
            ['terence-lansoprazole', 'terence', 'lansoprazole', 'One capsule', 'Oral', 'Once a day before food', ['Morning'], []],

            ['aisha-salbutamol', 'aisha', 'salbutamol', 'Two puffs', 'Inhaled', 'When required',
                ['prn'], [
                    'prn_max_per_day' => 8,
                    'prn_min_gap_minutes' => 240,
                    'prn_indication' => 'For breathlessness or wheeze',
                    // She manages this one herself. Recording it as staff
                    // administration would be a false record.
                    'support_type' => 'self_administered',
                    'instructions' => 'Aisha administers this herself. Record it; do not hand it to her.',
                ]],
            // A SCHEDULED self-administered medicine, not only a PRN one.
            // Without this the fixture never puts an authorised
            // self-administration into a round, and the one arrangement where a
            // worker must NOT hand the medicine over is the one arrangement
            // nobody ever sees on the round screen.
            ['aisha-colecalciferol', 'aisha', 'colecalciferol', 'One tablet', 'Oral', 'Once a day',
                ['Morning'], [
                    'support_type' => 'self_administered',
                    'instructions' => 'Aisha keeps this in her own room and takes it herself. '
                        .'Check she has taken it and record it; do not hand it to her.',
                ]],

            // A SECOND self-administered medicine, left open. The first one is
            // recorded so the round shows what an authorised self-administration
            // looks like once answered; this one stays unanswered so it also
            // shows WHY staff cannot sign for it themselves. One without the
            // other only tells half the story.
            ['aisha-ferrous', 'aisha', 'ferrous', 'One tablet', 'Oral', 'Once a day',
                ['Morning'], [
                    'support_type' => 'self_administered',
                    'instructions' => 'Aisha keeps this with her vitamin D and takes both herself.',
                ]],

            ['aisha-levetiracetam', 'aisha', 'levetiracetam', 'One tablet', 'Oral', 'Twice a day',
                ['Morning', 'Night'], [
                    'is_time_critical' => true,
                    'grace' => 45,
                    'changed_at' => $today->copy()->subDays(2)->setTime(14, 20),
                    'change_note' => 'Dose increased from 250mg to 500mg by the epilepsy nurse on Monday.',
                ]],

            // ── Arrangements that were otherwise invisible on a round ──────
            //
            // The fixture had one assisted medicine (already answered), one
            // self-administered one (already answered) and no prompted one at
            // all, so three of the four support arrangements could not be seen
            // on the screen that has to tell them apart. These are ordinary,
            // unremarkable medicines chosen so each arrangement is present and
            // still open when somebody opens the morning round.

            ['margaret-macrogol', 'margaret', 'macrogol', 'One sachet in water', 'Oral',
                'Once a day', ['Morning'], [
                    'support_type' => 'assisted',
                    'instructions' => 'She can hold the cup herself; steady it and stay with her.',
                ]],

            // A controlled drug given on a schedule. It cannot be recorded
            // until witnessed administration exists, and it is meant to sit
            // there saying so.
            ['margaret-morphine', 'margaret', 'morphine_mr', 'One tablet', 'Oral', 'Twice a day',
                ['Morning', 'Night'], [
                    'instructions' => 'Controlled drug. Two signatures and the register, every time.',
                ]],

            ['dennis-colecalciferol', 'dennis', 'colecalciferol', 'One tablet', 'Oral',
                'Once a day', ['Morning'], [
                    'support_type' => 'prompted',
                    'instructions' => 'Dennis takes this himself. Remind him and stay while he does; '
                        .'do not hand it to him.',
                ]],

            ['dennis-metformin', 'dennis', 'metformin', 'One tablet', 'Oral', 'Twice a day with food',
                ['Morning', 'Teatime'], []],
            ['dennis-paracetamol', 'dennis', 'paracetamol', 'Two tablets', 'Oral', 'When required',
                ['prn'], [
                    'prn_max_per_day' => 4,
                    'prn_min_gap_minutes' => 240,
                    'prn_indication' => 'For back pain',
                ]],

            ['joyce-macrogol', 'joyce', 'macrogol', 'One sachet in water', 'Oral', 'Once a day',
                ['Morning'], ['support_type' => 'assisted']],
            ['joyce-lorazepam', 'joyce', 'lorazepam', 'One tablet', 'Oral', 'When required',
                ['prn'], [
                    'prn_max_per_day' => 2,
                    'prn_min_gap_minutes' => 360,
                    'prn_indication' => 'For severe agitation, when reassurance has not worked',
                    'instructions' => 'Controlled drug. Two signatures and a stock count every time.',
                ]],
            ['joyce-paracetamol', 'joyce', 'paracetamol', 'Two tablets', 'Oral', 'Four times a day',
                ['Morning', 'Lunchtime', 'Teatime', 'Night'], []],

            // Callum is in hospital. His prescription is STILL ACTIVE — the
            // prescriber has not stopped it, the house simply is not giving it
            // while he is on a ward. So the dose is planned, it stays planned,
            // and somebody has to record why it was not given. Suspending the
            // prescription instead would have made the obligation vanish, which
            // is how a missing dose becomes a dose nobody ever had to explain.
            ['callum-sertraline', 'callum', 'sertraline', 'One tablet', 'Oral', 'Once a day',
                ['Morning'], []],
        ];

        foreach ($rows as [$key, $clientKey, $medicineKey, $dose, $route, $frequency, $slots, $options]) {
            $made[$key] = [
                'model' => Prescription::create([
                    'reference' => 'OAK-P-'.str_pad((string) (count($made) + 1), 3, '0', STR_PAD_LEFT),
                    'client_id' => $clients[$clientKey]->id,
                    'medicine_id' => $medicines[$medicineKey]->id,
                    'dose' => $dose,
                    'route' => $route,
                    'frequency_text' => $frequency,
                    'kind' => $slots === ['prn'] ? 'prn' : 'scheduled',
                    'is_time_critical' => $options['is_time_critical'] ?? false,
                    'support_type' => $options['support_type'] ?? 'staff_administered',
                    'instructions' => $options['instructions'] ?? null,
                    'prn_max_per_day' => $options['prn_max_per_day'] ?? null,
                    'prn_min_gap_minutes' => $options['prn_min_gap_minutes'] ?? null,
                    'prn_indication' => $options['prn_indication'] ?? null,
                    'starts_on' => $today->copy()->subMonths(6),
                    'status' => $options['status'] ?? 'active',
                    'changed_at' => $options['changed_at'] ?? null,
                    'change_note' => $options['change_note'] ?? null,
                ]),
                'slots' => $slots,
                'grace' => $options['grace'] ?? null,
            ];
        }

        return $made;
    }

    /* ── The day ────────────────────────────────────────────────────────── */

    /**
     * Build today's doses and record what happened to the ones already past.
     *
     * The current round is left open, whichever it is, so there is always a
     * round to start or resume. Everything before it is finished — mostly
     * given, with the handful of outcomes that make a real shift interesting.
     */
    private function day(Service $house, array $prescriptions, User $olivia): void
    {
        $now = now();
        $currentSlot = $this->currentSlot($now);

        foreach (self::SLOTS as $index => $slot) {
            $dueAt = Carbon::today()->setTime($slot['hour'], $slot['minute']);
            $isPast = $index < $currentSlot;

            foreach ($prescriptions as $key => $prescription) {
                if (! in_array($slot['name'], $prescription['slots'], true)) {
                    continue;
                }

                if ($prescription['model']->status !== 'active') {
                    continue;
                }

                $dose = ScheduledDose::create([
                    'prescription_id' => $prescription['model']->id,
                    'client_id' => $prescription['model']->client_id,
                    'service_id' => $house->id,
                    'due_at' => $dueAt,
                    'slot' => $slot['name'],
                    'grace_minutes' => $prescription['grace'] ?? $slot['grace'],
                ]);

                if (! $isPast) {
                    continue;
                }

                // NOTHING IS RECORDED FOR SOMEBODY WHO IS NOT THERE.
                // Callum is on a ward. Auto-filling an outcome for him would be
                // inventing a clinical record, and marking it omitted would be
                // deciding something a person has to decide. The dose stays
                // planned and unanswered until somebody records the real
                // outcome and reason — which is Section 2.3's job.
                if (! $prescription['model']->client->isAvailable()) {
                    continue;
                }

                $this->record($dose, $key, $slot['name'], $olivia, $house);
            }
        }
    }

    /**
     * What happened to one past dose.
     *
     * Mostly "given". The exceptions are chosen so that a support worker
     * arriving mid-shift has something real to pick up: a refusal to re-offer,
     * a medicine the pharmacy has not delivered, and a dose that was simply
     * not recorded and is now overdue.
     */
    private function record(ScheduledDose $dose, string $key, string $slot, User $olivia, Service $house): void
    {
        $exceptions = [
            // Dennis says no when he feels rushed. Real, common, and it needs
            // a re-offer rather than a shrug.
            'dennis-metformin|Morning' => ['refused', 'client_declined',
                'Said he felt sick and would rather leave it. Told him I would come back before lunch.'],

            // The pharmacy has not delivered. Nothing the shift can do about
            // it except make sure the next shift knows.
            'joyce-macrogol|Morning' => ['not_available', 'stock_unavailable',
                'None in the cupboard. Pharmacy order went in Thursday and has not arrived.'],
        ];

        $outcome = $exceptions[$key.'|'.$slot] ?? null;

        // Deliberately left unrecorded. A time-critical dose that nobody wrote
        // down, still not given, is the single most important thing this
        // dashboard can surface — so the fixture guarantees one exists rather
        // than hoping the reviewer happens to look at the right hour.
        //
        // Morning rather than a later slot because the morning is in the past
        // for almost any hour somebody opens this, so the case is always
        // visible. Before about half nine the whole day is still ahead, which
        // is its own valid picture: a shift that has not started yet.
        if ($key === 'terence-levodopa' && $slot === 'Morning') {
            return;
        }

        // A second one, later in the day, so an afternoon reviewer sees more
        // than a single row.
        if ($key === 'joyce-paracetamol' && $slot === 'Lunchtime') {
            return;
        }

        // The three arrangements above are left OPEN on purpose. An assisted
        // medicine that has already been answered demonstrates nothing about
        // assisting somebody, and a prompted or controlled one that is already
        // recorded never shows why it could not be.
        if (in_array($key, [
            'margaret-macrogol', 'margaret-morphine', 'dennis-colecalciferol', 'aisha-ferrous',
        ], true)) {
            return;
        }

        Administration::create([
            'reference' => 'OAK-A-'.substr(md5($key.$slot.now()->timestamp), 0, 10),
            'scheduled_dose_id' => $dose->id,
            'prescription_id' => $dose->prescription_id,
            'client_id' => $dose->client_id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $olivia->id,
            // "Given" means a worker handed it over. Recording that against a
            // medicine the person is authorised to take themselves would be a
            // false record of who did what — and Record7 has a separate outcome
            // for exactly this.
            'outcome' => $outcome[0]
                ?? ($dose->prescription?->support_type === 'self_administered'
                    ? 'self_administered'
                    : 'given'),
            'reason_code' => $outcome[1] ?? null,
            'notes' => $outcome[2] ?? null,
            'administered_at' => $dose->due_at->copy()->addMinutes(random_int(2, 25)),
        ]);
    }

    /**
     * A controlled drug given at three in the morning, still unanswered.
     *
     * This is the thing that most often falls down the gap between shifts, so
     * the fixture makes sure there is one waiting.
     */
    private function overnightPrn(Service $house, array $prescriptions, User $olivia): void
    {
        $lorazepam = $prescriptions['joyce-lorazepam']['model'];
        $givenAt = Carbon::today()->setTime(3, 10);

        $administration = Administration::create([
            'reference' => 'OAK-A-PRN-'.substr(md5('joyce-lorazepam'.now()->timestamp), 0, 8),
            'scheduled_dose_id' => null,
            'prescription_id' => $lorazepam->id,
            'client_id' => $lorazepam->client_id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $olivia->id,
            'witnessed_by_user_id' => null,
            'outcome' => 'given',
            'reason_code' => 'prn_agitation',
            'notes' => 'Awake and very distressed since about two. Sat with her first, '
                .'no settling. Stock counted, 14 remaining.',
            'administered_at' => $givenAt,
        ]);

        PrnFollowUp::create([
            'administration_id' => $administration->id,
            'client_id' => $lorazepam->client_id,
            'service_id' => $house->id,
            'due_at' => $givenAt->copy()->addHours(1),
            'outcome' => 'pending',
        ]);

        // And an ordinary one from this morning, so the list is not a single
        // dramatic row. Dennis's back pain.
        $paracetamol = $prescriptions['dennis-paracetamol']['model'];
        $painGivenAt = now()->copy()->subMinutes(75);

        if ($painGivenAt->isToday()) {
            $painAdministration = Administration::create([
                'reference' => 'OAK-A-PRN-'.substr(md5('dennis-paracetamol'.now()->timestamp), 0, 8),
                'scheduled_dose_id' => null,
                'prescription_id' => $paracetamol->id,
                'client_id' => $paracetamol->client_id,
                'service_id' => $house->id,
                'recorded_by_user_id' => $olivia->id,
                'outcome' => 'given',
                'reason_code' => 'prn_pain',
                'notes' => 'Lower back again. Rated it seven out of ten.',
                'administered_at' => $painGivenAt,
            ]);

            PrnFollowUp::create([
                'administration_id' => $painAdministration->id,
                'client_id' => $paracetamol->client_id,
                'service_id' => $house->id,
                'due_at' => $painGivenAt->copy()->addHour(),
                'outcome' => 'pending',
            ]);
        }
    }

    /* ── Handover ───────────────────────────────────────────────────────── */

    private function handover(Service $house, User $olivia, array $clients): void
    {
        $handover = Handover::create([
            'service_id' => $house->id,
            'written_by_user_id' => $olivia->id,
            'shift' => 'Night shift',
            'covers_from' => Carbon::today()->subDay()->setTime(20, 0),
            'covers_to' => Carbon::today()->setTime(8, 0),
            'summary' => 'Quiet night apart from Joyce. Everyone else settled and nothing else outstanding.',
        ]);

        $notes = [
            ['joyce', 'urgent',
                'Joyce was awake and distressed from about two. Lorazepam given at 3:10 and it '
                .'did settle her, but nobody has been back to record whether it worked. Please '
                .'close that off and let the manager know if she is unsettled again today.'],
            ['dennis', 'important',
                'Dennis refused his morning metformin — felt sick. He usually takes it if you '
                .'leave him twenty minutes and come back. Worth trying again before lunch.'],
            ['joyce', 'important',
                'No macrogol left in the cupboard at all. Pharmacy order went in Thursday and '
                .'still has not arrived. Chase them this morning.'],
            ['aisha', 'important',
                'Aisha\'s levetiracetam went up to 500mg on Monday. The old box is still in the '
                .'trolley — do not use it.'],
            [null, 'routine',
                'Trolley key is back on the board where it should be. Night staff had it in the office.'],
            ['callum', 'routine',
                'Callum is still on the ward at the Royal. No date for coming home yet.'],
        ];

        foreach ($notes as [$clientKey, $priority, $note]) {
            HandoverNote::create([
                'handover_id' => $handover->id,
                'client_id' => $clientKey ? $clients[$clientKey]->id : null,
                'priority' => $priority,
                'note' => $note,
            ]);
        }
    }

    /** Which slot the clock is currently in. Everything before it is history. */
    private function currentSlot(Carbon $now): int
    {
        foreach (self::SLOTS as $index => $slot) {
            $due = Carbon::today()->setTime($slot['hour'], $slot['minute']);

            if ($now->lessThan($due->copy()->addMinutes($slot['grace']))) {
                return $index;
            }
        }

        return count(self::SLOTS) - 1;
    }
}
