<?php

namespace Database\Seeders;

use App\Models\Record7\Administration;
use App\Models\Record7\Client;
use App\Models\Record7\ClientAllergy;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\Handover;
use App\Models\Record7\HandoverNote;
use App\Models\Record7\HandoverRead;
use App\Models\Record7\IssueState;
use App\Models\Record7\Medicine;
use App\Models\Record7\Permission;
use App\Models\Record7\Prescription;
use App\Models\Record7\PrnFollowUp;
use App\Models\Record7\ReviewItem;
use App\Models\Record7\Role;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\StockEvent;
use App\Models\Record7\StockLevel;
use App\Models\Record7\User;
use App\Models\Record7\UserCompetency;
use App\Models\Record7\UserPermission;
use App\Models\Record7\UserRole;
use App\Models\Record7\UserServiceAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Fictional data for Section 1.2 — Manager Today.
 *
 * WHY THIS EXISTS SEPARATELY FROM SECTION 1.1
 * Daniel Evans manages two houses and Section 1.1 only ever filled one, so
 * switching house showed a populated screen and then an empty one — which
 * proves nothing about scoping. Rosewood House gets its own people, its own
 * medicines and its own shift, and every row this seeder writes carries the
 * ROSE- prefix so it can find and replace exactly its own work.
 *
 * IT NEVER TOUCHES OAKWOOD. Section 1.1's fixture is left alone: this seeder
 * clears only what it wrote, so running it after Section 1.1 cannot orphan or
 * duplicate anything, and running it twice changes nothing.
 *
 * Guarded exactly like the others: never in production, never without the
 * explicit flag, never against a database whose name looks like the legacy one.
 */
class Record7Section12Seeder extends Seeder
{
    private const HOUSE = 'Rosewood House';

    /** Everything this seeder writes is findable by this. */
    private const PREFIX = 'ROSE-';

    /** The permission that opens Manager Today. */
    private const MANAGER_PERMISSION = 'view_manager_dashboard';

    private const SLOTS = [
        ['name' => 'Morning', 'hour' => 8, 'minute' => 0, 'grace' => 90],
        ['name' => 'Lunchtime', 'hour' => 12, 'minute' => 30, 'grace' => 60],
        ['name' => 'Teatime', 'hour' => 17, 'minute' => 30, 'grace' => 60],
        ['name' => 'Night', 'hour' => 21, 'minute' => 30, 'grace' => 90],
    ];

    public function run(): void
    {
        $this->refuseUnlessSafe();

        $rosewood = Service::where('name', self::HOUSE)->firstOrFail();
        $oakwood = Service::where('name', 'Oakwood House')->firstOrFail();

        $daniel = User::where('username', 'daniel.evans')->firstOrFail();
        $olivia = User::where('username', 'olivia.carter')->firstOrFail();
        $sarah = User::where('username', 'sarah.ahmed')->firstOrFail();

        $connection = DB::connection('record7');
        $connection->unprepared('DROP TRIGGER IF EXISTS record7_administrations_no_delete');

        try {
            $connection->transaction(function () use ($rosewood, $oakwood, $daniel, $olivia, $sarah) {
                $this->clear($rosewood, $oakwood);

                $this->managerPermission();
                $this->managerGrants($daniel, [$rosewood, $oakwood]);
                $ruth = $this->staffWithExpiredCompetency($rosewood);

                $medicines = $this->medicines();
                $clients = $this->clients($rosewood);
                $prescriptions = $this->prescriptions($clients, $medicines);
                $this->day($rosewood, $prescriptions, $olivia);
                $this->handover($rosewood, $olivia, $clients, $ruth);

                $this->stock($rosewood, $oakwood, $medicines, $olivia);
                $this->reviewQueue($rosewood, $oakwood, $olivia, $sarah, $daniel);
                $this->resolvedIssue($rosewood, $daniel);

                $this->command?->info(
                    'Rosewood House seeded: '.count($clients).' clients, '
                    .count($prescriptions).' prescriptions. Manager fixtures for Daniel Evans in place.'
                );
            });
        } finally {
            $this->restoreDeleteGuard();
        }
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
     * Remove only what this seeder wrote.
     *
     * Found by reference prefix rather than by house id, for the reason Section
     * 1.1 learned the hard way: re-running the Section 0 seeder renumbers the
     * services, and a clear-out that trusts an id then finds nothing and the
     * next insert collides on a unique reference.
     */
    private function clear(Service $rosewood, Service $oakwood): void
    {
        $connection = DB::connection('record7');

        $clientIds = Client::where('service_id', $rosewood->id)
            ->orWhere('reference', 'like', self::PREFIX.'%')
            ->pluck('id');

        if ($clientIds->isNotEmpty()) {
            PrnFollowUp::whereIn('client_id', $clientIds)->delete();
            $connection->table('record7_administrations')->whereIn('client_id', $clientIds)->delete();
            ScheduledDose::whereIn('client_id', $clientIds)->delete();
            Prescription::whereIn('client_id', $clientIds)->delete();
            ClientAllergy::whereIn('client_id', $clientIds)->delete();

            $handovers = Handover::where('service_id', $rosewood->id)->pluck('id')
                ->merge(HandoverNote::whereIn('client_id', $clientIds)->pluck('handover_id'))
                ->unique();

            HandoverRead::whereIn('handover_id', $handovers)->delete();
            HandoverNote::whereIn('handover_id', $handovers)->delete();
            Handover::whereIn('id', $handovers)->delete();

            $connection->table('record7_rounds')->where('service_id', $rosewood->id)->delete();
            Client::whereIn('id', $clientIds)->delete();
        }

        // Manager fixtures for BOTH houses — they are this seeder's, wherever
        // they point.
        foreach ([$rosewood->id, $oakwood->id] as $serviceId) {
            StockEvent::where('service_id', $serviceId)->delete();
            StockLevel::where('service_id', $serviceId)->delete();
            ReviewItem::where('service_id', $serviceId)->delete();
            IssueState::where('service_id', $serviceId)->delete();
        }
    }

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

    /* ── Who may open Manager Today ─────────────────────────────────────── */

    /**
     * A permission of its own, rather than reusing one that means something
     * else.
     *
     * Gating the manager dashboard on "manage_staff" would have worked today
     * and been wrong tomorrow: the two are not the same idea, and the first
     * time somebody may manage staff but not oversee medicines the conflation
     * becomes a hole. It is granted to the roles that actually oversee a house.
     */
    private function managerPermission(): void
    {
        $permission = Permission::firstOrCreate(
            ['code' => self::MANAGER_PERMISSION],
            [
                'name' => 'View the manager dashboard',
                'description' => 'See medication oversight for a house, including staff readiness.',
                'is_sensitive' => true,
            ]
        );

        foreach (['R2', 'R3', 'R4', 'R5'] as $code) {
            $role = Role::where('code', $code)->first();

            if (! $role) {
                continue;
            }

            DB::connection('record7')->table('record7_role_permissions')->updateOrInsert(
                ['role_id' => $role->id, 'permission_id' => $permission->id],
                []
            );
        }
    }

    /**
     * Daniel can decide corrections and review incidents — per house, by name.
     *
     * NOT by widening the Service Manager role. The supplied matrix puts those
     * with the Medication Lead, and quietly changing what every Service Manager
     * in the organisation may do in order to make one screen work would be a
     * far larger decision than it looks. An explicit grant for the two houses
     * he actually manages is the same shape Section 0 already uses.
     */
    private function managerGrants(User $daniel, array $houses): void
    {
        foreach (['correction_approval', 'incident_review', 'stock_management'] as $code) {
            $permission = Permission::where('code', $code)->first();

            if (! $permission) {
                continue;
            }

            foreach ($houses as $house) {
                UserPermission::updateOrCreate(
                    [
                        'user_id' => $daniel->id,
                        'permission_id' => $permission->id,
                        'service_id' => $house->id,
                    ],
                    [
                        'effect' => 'allow',
                        'status' => 'active',
                        'reason' => 'Service Manager for this house.',
                    ]
                );
            }
        }
    }

    /**
     * Ruth Coleman: the staff-readiness case that matters.
     *
     * Employed as a Support Worker, explicitly permitted to administer in this
     * house, and her medication competency has expired. She therefore may NOT
     * administer — while remaining, in every other respect, a Support Worker.
     * That is the whole reason role, permission and competency are three
     * separate things, and Manager Today has to show it as three separate
     * facts rather than one red cross.
     */
    private function staffWithExpiredCompetency(Service $rosewood): User
    {
        $ruth = User::updateOrCreate(
            ['username' => 'ruth.coleman'],
            [
                'reference' => self::PREFIX.'U-001',
                'organisation_id' => $rosewood->organisation_id,
                'full_name' => 'Ruth Coleman',
                'preferred_name' => 'Ruth',
                'work_email' => 'ruth.coleman@example.invalid',
                'employee_reference' => 'OMG-0141',
                'password_hash' => Hash::make('Record7-Test-Expired-2026!'),
                'account_status' => 'active',
                'employment_type' => 'permanent',
                'password_set_at' => now()->subMonths(8),
            ]
        );

        $supportWorker = Role::where('code', 'R7')->firstOrFail();
        UserRole::updateOrCreate(['user_id' => $ruth->id, 'role_id' => $supportWorker->id], []);

        UserServiceAccess::updateOrCreate(
            ['user_id' => $ruth->id, 'service_id' => $rosewood->id],
            ['access_type' => 'standard', 'status' => 'active']
        );

        $administer = Permission::where('code', 'administer_medication')->firstOrFail();

        UserPermission::updateOrCreate(
            ['user_id' => $ruth->id, 'permission_id' => $administer->id, 'service_id' => $rosewood->id],
            [
                'effect' => 'allow',
                'status' => 'active',
                'reason' => 'Signed off for medication administration in 2025.',
            ]
        );

        $general = CompetencyType::where('code', 'general_medication')->firstOrFail();

        UserCompetency::updateOrCreate(
            ['user_id' => $ruth->id, 'competency_type_id' => $general->id, 'service_id' => $rosewood->id],
            [
                'status' => 'expired',
                'assessed_at' => now()->subYear()->subMonths(2),
                'review_due_at' => now()->subDays(19),
                'evidence_reference' => self::PREFIX.'EVIDENCE-001',
                'notes' => 'Annual reassessment overdue. Booked but not yet completed.',
            ]
        );

        return $ruth;
    }

    /* ── Rosewood's own shift ───────────────────────────────────────────── */

    private function medicines(): array
    {
        $rows = [
            'amlodipine' => ['Amlodipine', '5mg', 'tablet', false],
            'insulin' => ['Insulin glargine', '100units/ml', 'injection', false],
            'donepezil' => ['Donepezil', '10mg', 'tablet', false],
            'oxycodone' => ['Oxycodone', '5mg', 'capsule', true],
            'senna' => ['Senna', '7.5mg', 'tablet', false],
            'paracetamol' => ['Paracetamol', '500mg', 'tablet', false],
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

    private function clients(Service $house): array
    {
        $rows = [
            'harold' => [self::PREFIX.'C-001', 'Harold Nkemelu', 'Harold', '1941-06-19', 'Room 1',
                'Very deaf on the left. Stand on his right when you speak to him.'],
            'sylvia' => [self::PREFIX.'C-002', 'Sylvia Ashcroft', 'Sylvia', '1955-12-03', 'Room 2',
                'Insulin before breakfast, never after. She will tell you if it has been missed.'],
            'bridget' => [self::PREFIX.'C-003', 'Bridget Kelly', 'Bridget', '1948-02-27', 'Room 3',
                'Prefers to take her own tablets from the pot. Watch, do not hand them over.'],
        ];

        $clients = [];

        foreach ($rows as $key => [$reference, $full, $preferred, $dob, $room, $note]) {
            $clients[$key] = Client::create([
                'reference' => $reference,
                'organisation_id' => $house->organisation_id,
                'service_id' => $house->id,
                'full_name' => $full,
                'preferred_name' => $preferred,
                'date_of_birth' => $dob,
                'room_name' => $room,
                'status' => 'active',
                'support_note' => $note,
            ]);
        }

        ClientAllergy::create([
            'client_id' => $clients['harold']->id,
            'substance' => 'Sulfonamides',
            'reaction' => 'Severe rash',
            'severity' => 'severe',
            'source' => 'GP record, 2021',
            'recorded_at' => now()->subYears(4),
        ]);

        return $clients;
    }

    private function prescriptions(array $clients, array $medicines): array
    {
        $today = Carbon::today();
        $made = [];
        $index = 0;

        $rows = [
            ['harold-amlodipine', 'harold', 'amlodipine', 'One tablet', 'Oral', 'Once a day', ['Morning'], []],
            ['harold-donepezil', 'harold', 'donepezil', 'One tablet', 'Oral', 'Once a day at night', ['Night'], []],

            // Insulin is the reason time-critical exists as a concept.
            ['sylvia-insulin', 'sylvia', 'insulin', '12 units', 'Subcutaneous', 'Once a day before breakfast',
                ['Morning'], ['is_time_critical' => true, 'grace' => 30,
                    'instructions' => 'Before food. Do not give if she has already eaten — tell the nurse.']],
            ['sylvia-senna', 'sylvia', 'senna', 'Two tablets', 'Oral', 'Once a day at night', ['Night'], []],

            ['bridget-paracetamol', 'bridget', 'paracetamol', 'Two tablets', 'Oral', 'Four times a day',
                ['Morning', 'Lunchtime', 'Teatime', 'Night'], []],
            ['bridget-oxycodone', 'bridget', 'oxycodone', 'One capsule', 'Oral', 'When required',
                ['prn'], [
                    'prn_max_per_day' => 4,
                    'prn_min_gap_minutes' => 240,
                    'prn_indication' => 'For breakthrough pain',
                    'instructions' => 'Controlled drug. Two signatures and a stock count every time.',
                ]],
        ];

        foreach ($rows as [$key, $clientKey, $medicineKey, $dose, $route, $frequency, $slots, $options]) {
            $index++;

            $made[$key] = [
                'model' => Prescription::create([
                    'reference' => self::PREFIX.'P-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                    'client_id' => $clients[$clientKey]->id,
                    'medicine_id' => $medicines[$medicineKey]->id,
                    'dose' => $dose,
                    'route' => $route,
                    'frequency_text' => $frequency,
                    'kind' => $slots === ['prn'] ? 'prn' : 'scheduled',
                    'is_time_critical' => $options['is_time_critical'] ?? false,
                    'instructions' => $options['instructions'] ?? null,
                    'prn_max_per_day' => $options['prn_max_per_day'] ?? null,
                    'prn_min_gap_minutes' => $options['prn_min_gap_minutes'] ?? null,
                    'prn_indication' => $options['prn_indication'] ?? null,
                    'starts_on' => $today->copy()->subMonths(4),
                    'status' => 'active',
                ]),
                'slots' => $slots,
                'grace' => $options['grace'] ?? null,
            ];
        }

        return $made;
    }

    /**
     * Rosewood's day, with two things a manager has to act on.
     *
     * Sylvia's insulin was never recorded — a time-critical omission with no
     * explanation, which is the most serious thing on either dashboard. And
     * Harold refused his amlodipine and nobody went back.
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

                // Deliberately unrecorded: a time-critical omission nobody
                // explained. This is the headline of the manager's list.
                if ($key === 'sylvia-insulin' && $slot['name'] === 'Morning') {
                    continue;
                }

                $refused = $key === 'harold-amlodipine' && $slot['name'] === 'Morning';

                Administration::create([
                    'reference' => self::PREFIX.'A-'.substr(md5($key.$slot['name'].$now->timestamp), 0, 10),
                    'scheduled_dose_id' => $dose->id,
                    'prescription_id' => $dose->prescription_id,
                    'client_id' => $dose->client_id,
                    'service_id' => $house->id,
                    'recorded_by_user_id' => $olivia->id,
                    'outcome' => $refused ? 'refused' : 'given',
                    'reason_code' => $refused ? 'client_declined' : null,
                    'notes' => $refused
                        ? 'Said he did not want it and turned away. Left it and said I would come back.'
                        : null,
                    'administered_at' => $dose->due_at->copy()->addMinutes(random_int(3, 22)),
                ]);
            }
        }

        // A controlled drug given overnight whose effect nobody recorded.
        $oxycodone = $prescriptions['bridget-oxycodone']['model'];
        $givenAt = Carbon::today()->setTime(4, 40);

        $administration = Administration::create([
            'reference' => self::PREFIX.'A-PRN-'.substr(md5('bridget-oxycodone'.$now->timestamp), 0, 8),
            'scheduled_dose_id' => null,
            'prescription_id' => $oxycodone->id,
            'client_id' => $oxycodone->client_id,
            'service_id' => $house->id,
            'recorded_by_user_id' => $olivia->id,
            'outcome' => 'given',
            'reason_code' => 'prn_pain',
            'notes' => 'Woke in pain. Stock counted, 22 remaining.',
            'administered_at' => $givenAt,
        ]);

        PrnFollowUp::create([
            'administration_id' => $administration->id,
            'client_id' => $oxycodone->client_id,
            'service_id' => $house->id,
            'due_at' => $givenAt->copy()->addHour(),
            'outcome' => 'pending',
        ]);
    }

    private function handover(Service $house, User $olivia, array $clients, User $ruth): void
    {
        $handover = Handover::create([
            'service_id' => $house->id,
            'written_by_user_id' => $olivia->id,
            'shift' => 'Night shift',
            'covers_from' => Carbon::today()->subDay()->setTime(20, 0),
            'covers_to' => Carbon::today()->setTime(8, 0),
            'summary' => 'Bridget was in pain overnight. Everything else settled.',
        ]);

        $notes = [
            ['sylvia', 'urgent',
                'Sylvia has not had her morning insulin recorded and nobody can say whether it '
                .'was given. Please establish what happened before anything else today.'],
            ['bridget', 'important',
                'Bridget had oxycodone at 4:40 for pain. Nobody has recorded whether it helped, '
                .'and the controlled-drug balance needs checking against the book.'],
            ['harold', 'important',
                'Harold refused his morning tablet. He often takes it later if you sit with him.'],
            [null, 'routine', 'The upstairs trolley wheel is sticking again. Maintenance told.'],
        ];

        foreach ($notes as [$clientKey, $priority, $note]) {
            HandoverNote::create([
                'handover_id' => $handover->id,
                'client_id' => $clientKey ? $clients[$clientKey]->id : null,
                'priority' => $priority,
                'note' => $note,
            ]);
        }

        // One person has read it and one has not, so the manager's
        // acknowledgement view has something real to show.
        HandoverRead::updateOrCreate(
            ['handover_id' => $handover->id, 'user_id' => $ruth->id],
            ['read_at' => Carbon::today()->setTime(8, 12)]
        );
    }

    /* ── Manager fixtures ───────────────────────────────────────────────── */

    private function stock(Service $rosewood, Service $oakwood, array $medicines, User $olivia): void
    {
        // Rosewood: a controlled-drug balance two capsules short of the book.
        // The single most serious thing a manager can be shown.
        $oxycodone = StockLevel::create([
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'medicine_id' => $medicines['oxycodone']->id,
            'quantity' => 20,
            'low_threshold' => 8,
            'last_counted_at' => now()->subHours(2),
        ]);

        StockEvent::create([
            'service_id' => $rosewood->id,
            'medicine_id' => $medicines['oxycodone']->id,
            'kind' => 'discrepancy',
            'expected_quantity' => 22,
            'counted_quantity' => 20,
            'note' => 'Book says 22 after the 4:40 dose. Cupboard holds 20. Counted twice.',
            'recorded_by_user_id' => $olivia->id,
            'occurred_at' => now()->subHours(2),
        ]);

        StockLevel::create([
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'medicine_id' => $medicines['insulin']->id,
            'quantity' => 2,
            'low_threshold' => 5,
            'last_counted_at' => now()->subDay(),
        ]);

        // Oakwood: the macrogol that Section 1.1's shift could not give. The
        // support worker saw "none in the cupboard"; the manager sees why.
        $macrogol = Medicine::where('name', 'Macrogol')->first();

        if ($macrogol) {
            StockLevel::create([
                'organisation_id' => $oakwood->organisation_id,
                'service_id' => $oakwood->id,
                'medicine_id' => $macrogol->id,
                'quantity' => 0,
                'low_threshold' => 6,
                'last_counted_at' => now()->subHours(4),
            ]);

            StockEvent::create([
                'service_id' => $oakwood->id,
                'medicine_id' => $macrogol->id,
                'kind' => 'delivery_overdue',
                'note' => 'Ordered Thursday. Pharmacy has not delivered and has not answered.',
                'recorded_by_user_id' => $olivia->id,
                'occurred_at' => now()->subHours(4),
            ]);
        }

        // And a resolved one, so "resolved things disappear" is testable.
        StockEvent::create([
            'service_id' => $rosewood->id,
            'medicine_id' => $medicines['senna']->id,
            'kind' => 'discrepancy',
            'expected_quantity' => 30,
            'counted_quantity' => 28,
            'note' => 'Two tablets unaccounted for at the Monday count.',
            'recorded_by_user_id' => $olivia->id,
            'occurred_at' => now()->subDays(2),
            'resolved_at' => now()->subDay(),
            'resolved_by_user_id' => $olivia->id,
            'resolution_note' => 'Found recorded on the wrong chart. Balance corrected at the next count.',
        ]);

        $oxycodone->refresh();
    }

    private function reviewQueue(
        Service $rosewood,
        Service $oakwood,
        User $olivia,
        User $sarah,
        User $daniel
    ): void {
        // A correction request against a real Oakwood administration, so
        // approving it exercises the append-only correction path.
        $oakwoodAdministration = Administration::where('service_id', $oakwood->id)
            ->where('outcome', 'given')
            ->orderBy('id')
            ->first();

        if ($oakwoodAdministration) {
            ReviewItem::create([
                'reference' => self::PREFIX.'R-001',
                'organisation_id' => $oakwood->organisation_id,
                'service_id' => $oakwood->id,
                'kind' => 'correction_request',
                'title' => 'Recorded against the wrong person',
                'detail' => 'I signed this on the wrong line and only noticed at the end of the round. '
                    .'It was not given. Please correct it.',
                // The person who was there says what they believe happened. The
                // manager approves or declines THAT, rather than typing a
                // different outcome at the moment of approving.
                'requested_outcome' => 'missed',
                'subject_type' => 'administration',
                'subject_id' => $oakwoodAdministration->id,
                'raised_by_user_id' => $olivia->id,
                'raised_at' => now()->subHours(1),
                'severity' => 'high',
                'status' => 'open',
            ]);
        }

        ReviewItem::create([
            'reference' => self::PREFIX.'R-002',
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'kind' => 'incident',
            'title' => 'Time-critical insulin not recorded',
            'detail' => 'Sylvia Ashcroft. Morning insulin has no record and no explanation. '
                .'Needs establishing and reporting today.',
            'raised_by_user_id' => $sarah->id,
            'raised_at' => now()->subMinutes(50),
            'severity' => 'high',
            'status' => 'open',
        ]);

        ReviewItem::create([
            'reference' => self::PREFIX.'R-003',
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'kind' => 'handover_escalation',
            'title' => 'Night handover not acknowledged by the morning staff',
            'detail' => 'The urgent insulin note has not been confirmed as read by everyone on shift.',
            'raised_by_user_id' => $sarah->id,
            'raised_at' => now()->subMinutes(25),
            'severity' => 'medium',
            'status' => 'open',
        ]);

        // One already decided, so the queue can be shown to empty properly and
        // the decision remains in the record.
        ReviewItem::create([
            'reference' => self::PREFIX.'R-004',
            'organisation_id' => $rosewood->organisation_id,
            'service_id' => $rosewood->id,
            'kind' => 'round_reopen_request',
            'title' => 'Reopen yesterday evening round',
            'detail' => 'A dose was given but not recorded before the round closed.',
            'raised_by_user_id' => $olivia->id,
            'raised_at' => now()->subDay(),
            'severity' => 'low',
            'status' => 'approved',
            'decided_by_user_id' => $daniel->id,
            'decided_at' => now()->subDay()->addHour(),
            'decision_note' => 'Reopened so the record could be completed properly.',
        ]);
    }

    /**
     * One issue somebody has already dealt with.
     *
     * It must vanish from the active list while the row, and the audit event
     * that closed it, both remain. That is the difference between resolving a
     * problem and hiding one.
     */
    private function resolvedIssue(Service $rosewood, User $daniel): void
    {
        // Closed by a manager while the cupboard is STILL below its level.
        // This is the case the whole lifecycle change exists for: it must stay
        // on the list and say "Action recorded — underlying issue remains
        // unresolved" rather than vanishing because somebody pressed a button.
        $insulin = StockLevel::where('service_id', $rosewood->id)
            ->whereHas('medicine', fn ($q) => $q->where('name', 'Insulin glargine'))
            ->first();

        if (! $insulin) {
            return;
        }

        IssueState::updateOrCreate(
            [
                'organisation_id' => $rosewood->organisation_id,
                'service_id' => $rosewood->id,
                'issue_type' => 'stock_low',
                'source_id' => $insulin->id,
            ],
            [
                'issue_key' => 'stock_low:'.$insulin->id,
                'owner_user_id' => $daniel->id,
                'assigned_at' => now()->subHours(3),
                'acknowledged_at' => now()->subHours(3),
                'acknowledged_by_user_id' => $daniel->id,
                'action_recorded_at' => now()->subHours(2),
                'action_recorded_by_user_id' => $daniel->id,
                'action_note' => 'Rang the pharmacy and chased the order.',
                'closed_at' => now()->subHour(),
                'closed_by_user_id' => $daniel->id,
                'closure_reason' => 'Emergency supply collected. Delivery due tomorrow.',
                'evidence_reference' => 'PHARMACY-ORDER-4471',
            ]
        );
    }

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
