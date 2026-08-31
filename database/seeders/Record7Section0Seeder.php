<?php

namespace Database\Seeders;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\AccountInvitation;
use App\Models\Record7\CompetencyType;
use App\Models\Record7\LoginSession;
use App\Models\Record7\MfaMethod;
use App\Models\Record7\Organisation;
use App\Models\Record7\Permission;
use App\Models\Record7\Role;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\UserCompetency;
use App\Models\Record7\UserPermission;
use App\Models\Record7\UserRole;
use App\Models\Record7\UserServiceAccess;
use App\Services\Record7\OrganisationDirectory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;

/**
 * Loads the supplied Section 0 fixture into Record7's own database.
 *
 * SOURCE OF TRUTH
 * database/fixtures/record7/record7-section0-test.sqlite — the package as
 * supplied. The matching JSON is deliberately not used: it omits
 * user_permissions, and with it Grace Taylor's explicit deny, which is the
 * whole point of the competency scenario.
 *
 * PASSWORDS
 * The package stores PBKDF2-SHA256 hashes, which Laravel cannot verify. The
 * eight documented fictional passwords are therefore re-hashed with the
 * application's own hasher at seed time. They are published test credentials
 * for a fictional organisation and exist only to walk the journey locally.
 *
 * SAFETY
 * Three independent guards, all of which must pass:
 *   1. The environment must not be production.
 *   2. config('record7.allow_fixture_seed') must be explicitly true
 *      (RECORD7_ALLOW_FIXTURE_SEED=true).
 *   3. The target connection must not be the legacy database.
 *
 * Run it with:
 *   RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section0Seeder
 */
class Record7Section0Seeder extends Seeder
{
    /** The documented fictional passwords, by username. */
    private const PASSWORDS = [
        'olivia.carter' => 'Record7-Test-Staff-2026!',
        'daniel.evans' => 'Record7-Test-Manager-2026!',
        'priya.nair' => 'Record7-Test-Admin-2026!',
        'sarah.ahmed' => 'Record7-Test-MedLead-2026!',
        'noah.williams' => 'Record7-Test-MedAdmin-2026!',
        'maya.thompson' => 'Record7-Test-Reviewer-2026!',
        'grace.taylor' => 'Record7-Test-Agency-2026!',
        'ethan.cole' => 'Record7-Test-Suspended-2026!',
    ];

    /**
     * Which competency gates which permission.
     *
     * The package names competencies and permissions separately; this is the
     * link between them, and it is what makes Grace Taylor's unassessed
     * general-medication competency actually block administration.
     */
    private const COMPETENCY_GATES = [
        'general_medication' => 'administer_medication',
        'medication_witness' => 'witness_medication',
        'controlled_drugs' => 'manage_controlled_drugs',
        'stock_management' => 'stock_management',
    ];

    /** Maps fixture reference to the row created for it. */
    private array $map = [];

    public function run(): void
    {
        $this->guard();

        $pdo = $this->openFixture();

        /* THE APPEND-ONLY GUARDS COME OFF HERE, OUTSIDE THE TRANSACTION.
         *
         * Creating or dropping a trigger implicitly commits in MySQL, so doing
         * it inside would end the transaction out from under the seeder — the
         * same reason Sections 1 and 1.2 swap theirs either side. They go back
         * in the finally below, whatever happens in between.
         *
         * This is a fixture rebuild in a development database, gated by
         * guard() above. There is no runtime path to any of it. */
        $this->liftFixtureGuards();

        try {
            DB::connection('record7')->transaction(function () use ($pdo) {
                $this->clearExisting();
                $this->seedOrganisations($pdo);
                $this->seedServices($pdo);
                $this->seedRolesAndPermissions($pdo);
                $this->seedCompetencyTypes($pdo);
                $this->seedUsers($pdo);
                $this->seedUserRoles($pdo);
                $this->seedServiceAccess($pdo);
                $this->seedUserPermissions($pdo);
                $this->seedCompetencies($pdo);
                $this->seedMfa($pdo);
                $this->seedSessions($pdo);
                $this->separateRoleFromCompetency();
            });
        } finally {
            $this->restoreFixtureGuards();
        }

        // Outside the transaction: the audit table is append-only and its
        // triggers make it a poor citizen inside a rollback.
        $this->seedInvitationsAndResets();
        $this->seedConvenienceAccounts();

        $this->report();
    }

    /* ── Guards ──────────────────────────────────────────────────────────── */

    private function guard(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'Record7 fixture data must never be seeded in production.'
            );
        }

        if (! config('record7.allow_fixture_seed')) {
            throw new RuntimeException(
                'Refusing to seed. Set RECORD7_ALLOW_FIXTURE_SEED=true to confirm this is a local '
                .'Record7 development database.'
            );
        }

        $database = DB::connection('record7')->getDatabaseName();

        if (Str::startsWith($database, 'laravel')) {
            throw new RuntimeException(
                "Refusing to seed: the record7 connection points at '{$database}', which is the "
                .'legacy database. Point RECORD7_DB_DATABASE at a Record7 database.'
            );
        }

        $this->command?->info("Seeding Record7 fixture into '{$database}'.");
    }

    private function openFixture(): PDO
    {
        $path = config('record7.fixture_path');

        if (! is_file($path)) {
            throw new RuntimeException("Section 0 fixture not found at {$path}.");
        }

        if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new RuntimeException(
                'The pdo_sqlite extension is required to read the Section 0 fixture. '
                .'Enable it, or run with: php -d extension=php_pdo_sqlite.dll'
            );
        }

        $pdo = new PDO('sqlite:'.$path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * The append-only guards this rebuild has to work through, and the SQL
     * that puts each one back exactly as its migration wrote it.
     *
     * FIXTURE REBUILD ONLY. guard() has already refused production and refused
     * to run without RECORD7_ALLOW_FIXTURE_SEED. Nothing in the application
     * calls these, and the triggers are back in place before this method's
     * caller returns — including when it throws.
     */
    private const FIXTURE_GUARDS = [
        'record7_round_lifecycle_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_round_lifecycle_no_delete
            BEFORE DELETE ON record7_round_lifecycle_events
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a round lifecycle event cannot be deleted';
            END
        SQL,

        'record7_stock_movements_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_stock_movements_no_delete
            BEFORE DELETE ON record7_stock_movements
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a stock movement cannot be deleted';
            END
        SQL,

        'record7_stock_attempts_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_stock_attempts_no_delete
            BEFORE DELETE ON record7_stock_attempts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a stock attempt cannot be deleted';
            END
        SQL,

        'record7_cd_register_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_cd_register_no_delete
            BEFORE DELETE ON record7_cd_register
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a controlled drug register entry cannot be deleted';
            END
        SQL,

        'record7_prn_attempts_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_prn_attempts_no_delete
            BEFORE DELETE ON record7_prn_attempts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'an as-required attempt cannot be deleted';
            END
        SQL,

        'record7_welfare_checks_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_welfare_checks_no_delete
            BEFORE DELETE ON record7_welfare_checks
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'a welfare check cannot be deleted';
            END
        SQL,

        'record7_administrations_no_delete' => <<<'SQL'
            CREATE TRIGGER record7_administrations_no_delete
            BEFORE DELETE ON record7_administrations
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'record7 administrations are a permanent record and cannot be deleted';
            END
        SQL,
    ];

    private function liftFixtureGuards(): void
    {
        $connection = DB::connection('record7');

        foreach (array_keys(self::FIXTURE_GUARDS) as $trigger) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
        }
    }

    /**
     * Put every one back, whatever happened in between.
     *
     * IF EXISTS on the drop and a create per guard, so a rebuild that failed
     * halfway still leaves the database protected. A seeder that can leave an
     * append-only table writable is worse than one that fails.
     */
    private function restoreFixtureGuards(): void
    {
        $connection = DB::connection('record7');

        foreach (self::FIXTURE_GUARDS as $trigger => $sql) {
            $connection->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
            $connection->unprepared($sql);
        }
    }

    /**
     * Clear the fictional environment, dependants first.
     *
     * WHY THIS GREW.
     * It used to delete the access layer alone — users, roles, services,
     * organisations — with foreign key checks switched off, and leave every
     * clinical row that pointed at them behind. Section 0 then rebuilt the
     * houses with NEW ids, so the previous run's clients, rounds, stock and
     * register entries were not replaced but stranded. That is where the
     * fifteen orphaned rows in each of the two retired stock tables came from:
     * six reseeds, each leaving its predecessor behind.
     *
     * Section 2.6 then made it fail outright rather than merely accumulate.
     * Lifecycle events carry a foreign key to the round and a delete guard, so
     * the first reseed after a round had been closed died on "Cannot delete or
     * update a parent row" — and a clean fictional environment is what every
     * section's verification rests on.
     *
     * So the order below is the FK graph, dependants first. Foreign key checks
     * are still switched off, because a fixture rebuild is not the place to
     * fight a graph this deep — but the order is correct anyway, so the
     * deletes would stand on their own if they ever had to.
     */
    private function clearExisting(): void
    {
        $connection = DB::connection('record7');
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        /* Projections first: a round names its last lifecycle event, and a
           balance names its last movement. Nulling those is what lets the
           rows they point at go. */
        $connection->table('record7_rounds')->update(['last_lifecycle_event_id' => null]);
        $connection->table('record7_stock_balances')->update(['last_movement_id' => null]);

        foreach ([
            // Section 2.7 — stock. Attempts and thresholds point at movements
            // and balances; corrections point at earlier movements, so the
            // ledger comes down newest first.
            'record7_stock_attempts',
            'record7_stock_thresholds',
            'record7_stock_movements',
            'record7_stock_balances',

            // The retired tables. Cleared here for the first time, which is
            // what stops the orphans accumulating again.
            'record7_stock_events',
            'record7_stock_levels',

            // Section 2.6 — round lifecycle, then the rounds themselves.
            // Participants join a round and name a user, so they go first.
            'record7_round_lifecycle_events',
            'record7_round_participants',
            'record7_rounds',

            // Sections 2.3 to 2.5 — everything that answers an administration.
            'record7_welfare_checks',
            'record7_prn_attempts',
            'record7_prn_follow_ups',
            'record7_administrations',
            'record7_cd_balances',
            'record7_cd_register',

            // Section 1.2 — the manager surface.
            'record7_issue_states',
            'record7_review_items',

            // Handover, then the medicines plan, then the people.
            'record7_handover_reads',
            'record7_handover_notes',
            'record7_handovers',
            'record7_scheduled_doses',
            'record7_prescriptions',
            'record7_client_allergies',
            'record7_clients',

            // And finally the access layer this seeder owns.
            'record7_login_sessions', 'record7_mfa_methods', 'record7_user_competencies',
            'record7_user_permissions', 'record7_user_service_access', 'record7_user_roles',
            'record7_account_invitations', 'record7_password_resets',

            // Section 0's own sign-in machinery. These name a user and were
            // never cleared, so every reseed left another set behind.
            'record7_recovery_codes', 'record7_trusted_devices', 'record7_verification_events',
            'record7_users', 'record7_role_permissions', 'record7_permissions',
            'record7_roles', 'record7_competency_types', 'record7_services',
            'record7_organisations',
        ] as $table) {
            // A table a later section has not created yet is not an error:
            // Section 0 has to be runnable against a partly migrated database.
            if (Schema::connection('record7')->hasTable($table)) {
                $connection->table($table)->delete();
            }
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /* ── Import ──────────────────────────────────────────────────────────── */

    private function rows(PDO $pdo, string $sql): array
    {
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function time(?string $value): ?string
    {
        return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
    }

    private function seedOrganisations(PDO $pdo): void
    {
        $directory = app(OrganisationDirectory::class);

        foreach ($this->rows($pdo, 'SELECT * FROM organisations') as $row) {
            $this->map['org'][$row['id']] = Organisation::create([
                'reference' => $row['id'],
                'legal_name' => $row['legal_name'],
                'display_name' => $row['display_name'],
                // Re-normalised through our own rules rather than trusted, so
                // the stored value always matches what step one computes.
                'name_normalised' => $directory->normalise($row['display_name']),
                'status' => $row['status'],
            ])->id;
        }
    }

    private function seedServices(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM services') as $row) {
            $this->map['svc'][$row['id']] = Service::create([
                'reference' => $row['id'],
                'organisation_id' => $this->map['org'][$row['organisation_id']],
                'name' => $row['name'],
                'service_type' => $row['service_type'],
                'town' => $row['town'],
                'status' => $row['status'],
            ])->id;
        }
    }

    private function seedRolesAndPermissions(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM roles') as $row) {
            $this->map['role'][$row['id']] = Role::create([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'privilege_level' => (int) $row['privilege_level'],
            ])->id;
        }

        foreach ($this->rows($pdo, 'SELECT * FROM permissions') as $row) {
            $this->map['perm'][$row['id']] = Permission::create([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'is_sensitive' => (bool) $row['is_sensitive'],
            ])->id;
        }

        foreach ($this->rows($pdo, 'SELECT * FROM role_permissions') as $row) {
            DB::connection('record7')->table('record7_role_permissions')->insert([
                'role_id' => $this->map['role'][$row['role_id']],
                'permission_id' => $this->map['perm'][$row['permission_id']],
            ]);
        }
    }

    private function seedCompetencyTypes(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM competency_types') as $row) {
            $this->map['comp'][$row['id']] = CompetencyType::create([
                'code' => $row['code'],
                'name' => $row['name'],
                'description' => $row['description'],
                'default_review_months' => $row['default_review_months'],
                'gates_permission' => self::COMPETENCY_GATES[$row['code']] ?? null,
            ])->id;
        }
    }

    private function seedUsers(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM users') as $row) {
            $plain = self::PASSWORDS[$row['username']] ?? null;

            $this->map['user'][$row['id']] = User::create([
                'reference' => $row['id'],
                'organisation_id' => $this->map['org'][$row['organisation_id']],
                'full_name' => $row['full_name'],
                'preferred_name' => $row['preferred_name'],
                'username' => $row['username'],
                'work_email' => $row['work_email'],
                'employee_reference' => $row['employee_reference'],
                // Re-hashed locally. The package's PBKDF2 hash is unusable here.
                'password_hash' => $plain ? Hash::make($plain) : null,
                'account_status' => $row['account_status'],
                'employment_type' => $row['employment_type'],
                'access_starts_at' => $this->time($row['access_starts_at_utc']),
                'access_ends_at' => $this->time($row['access_ends_at_utc']),
                'password_set_at' => $plain ? now() : null,
                'last_signed_in_at' => $this->time($row['last_signed_in_at_utc']),
            ])->id;
        }
    }

    /**
     * A job is not a competency, and the supplied package conflates them.
     *
     * Noah Williams arrives from the fixture holding the role "Medication
     * Administrator". That is not a job anybody is employed as — it describes
     * what he is signed off to do. His actual employment role is Support
     * Worker, and Section 0 exists precisely so that what he may do comes from
     * three separate things:
     *
     *   his ROLE          what he is employed as        Support Worker
     *   his PERMISSION    what he is authorised to do   an explicit per-house allow
     *   his COMPETENCY    what he is signed off for     medication administration, current
     *
     * Renaming the job to describe the competency collapses all three into one,
     * and then an expired competency cannot take the ability away without also
     * appearing to demote the person.
     *
     * So he moves to Support Worker and keeps the ability through the same
     * shape Olivia Carter already uses: an explicit allow for the house he
     * works in, which the competency gate still has to pass. Let his
     * medication competency lapse and he stops being able to administer,
     * while remaining a Support Worker — which is the real-world behaviour.
     */
    private function separateRoleFromCompetency(): void
    {
        $noah = User::where('username', 'noah.williams')->first();
        $supportWorker = Role::where('code', 'R7')->first();
        $administer = Permission::where('code', 'administer_medication')->first();

        if (! $noah || ! $supportWorker || ! $administer) {
            return;
        }

        UserRole::where('user_id', $noah->id)->delete();
        UserRole::create(['user_id' => $noah->id, 'role_id' => $supportWorker->id]);

        // One allow per house he actually holds, never a blanket grant: being
        // competent in one house is not being competent everywhere.
        foreach (UserServiceAccess::where('user_id', $noah->id)->get() as $access) {
            UserPermission::firstOrCreate(
                [
                    'user_id' => $noah->id,
                    'permission_id' => $administer->id,
                    'service_id' => $access->service_id,
                ],
                [
                    'effect' => 'allow',
                    'status' => 'active',
                    'reason' => 'Medication administration competency confirmed for this house.',
                ]
            );
        }
    }

    private function seedUserRoles(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM user_roles') as $row) {
            UserRole::create([
                'user_id' => $this->map['user'][$row['user_id']],
                'role_id' => $this->map['role'][$row['role_id']],
                'service_id' => $row['service_id'] ? $this->map['svc'][$row['service_id']] : null,
                'starts_at' => $this->time($row['starts_at_utc']),
                'ends_at' => $this->time($row['ends_at_utc']),
            ]);
        }
    }

    private function seedServiceAccess(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM user_service_access') as $row) {
            UserServiceAccess::create([
                'user_id' => $this->map['user'][$row['user_id']],
                'service_id' => $this->map['svc'][$row['service_id']],
                'access_type' => $row['access_type'],
                'status' => $row['status'],
                'starts_at' => $this->time($row['starts_at_utc']),
                'ends_at' => $this->time($row['ends_at_utc']),
            ]);
        }
    }

    private function seedUserPermissions(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM user_permissions') as $row) {
            UserPermission::create([
                'user_id' => $this->map['user'][$row['user_id']],
                'permission_id' => $this->map['perm'][$row['permission_id']],
                'service_id' => $row['service_id'] ? $this->map['svc'][$row['service_id']] : null,
                'effect' => $row['effect'],
                'status' => $row['status'],
                'starts_at' => $this->time($row['starts_at_utc']),
                'ends_at' => $this->time($row['ends_at_utc']),
                'reason' => $row['reason'],
            ]);
        }
    }

    private function seedCompetencies(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM user_competencies') as $row) {
            UserCompetency::create([
                'user_id' => $this->map['user'][$row['user_id']],
                'competency_type_id' => $this->map['comp'][$row['competency_type_id']],
                'service_id' => $row['service_id'] ? $this->map['svc'][$row['service_id']] : null,
                'status' => $row['status'],
                'assessed_at' => $this->time($row['assessed_at_utc']),
                'review_due_at' => $this->time($row['review_due_at_utc']),
                'evidence_reference' => $row['evidence_reference'],
                'notes' => $row['notes'],
            ]);
        }
    }

    private function seedMfa(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM mfa_methods') as $row) {
            MfaMethod::create([
                'user_id' => $this->map['user'][$row['user_id']],
                'method_type' => $row['method_type'],
                'label' => $row['label'],
                'is_primary' => (bool) $row['is_primary'],
                'status' => $row['status'],
                'registered_at' => $this->time($row['registered_at_utc']),
                'last_verified_at' => $this->time($row['last_verified_at_utc']),
            ]);
        }
    }

    private function seedSessions(PDO $pdo): void
    {
        foreach ($this->rows($pdo, 'SELECT * FROM login_sessions') as $row) {
            LoginSession::create([
                'reference' => (string) Str::uuid(),
                'user_id' => $this->map['user'][$row['user_id']],
                'organisation_id' => $this->map['org'][$row['organisation_id']],
                'active_service_id' => $row['active_service_id']
                    ? $this->map['svc'][$row['active_service_id']] : null,
                'status' => $row['status'],
                'device_reference' => $row['device_reference'],
                'started_at' => $this->time($row['started_at_utc']),
                'last_activity_at' => $this->time($row['last_activity_at_utc']),
                'locked_at' => $this->time($row['locked_at_utc']),
                'ended_at' => $this->time($row['ended_at_utc']),
            ]);
        }
    }

    /**
     * The package seeds no invitations or resets, so Section 0.6 and 0.7 have
     * no fixture. One invited account is added here — clearly fictional, in the
     * same organisation — so first-time activation has something to activate.
     */
    private function seedInvitationsAndResets(): void
    {
        $organisationId = array_values($this->map['org'])[0] ?? null;

        if (! $organisationId) {
            return;
        }

        $invited = User::create([
            'reference' => 'usr_invited_fixture',
            'organisation_id' => $organisationId,
            'full_name' => 'Adam Fletcher',
            'preferred_name' => 'Adam',
            'username' => 'adam.fletcher',
            'work_email' => 'adam.fletcher@record7.test',
            'employee_reference' => 'EMP-1009',
            'password_hash' => null,
            'account_status' => 'invited',
            'employment_type' => 'permanent',
            'access_starts_at' => now()->subDay(),
        ]);

        $supportWorker = Role::where('code', 'R7')->first();
        $oakwood = Service::where('name', 'Oakwood House')->first();

        if ($supportWorker) {
            UserRole::create(['user_id' => $invited->id, 'role_id' => $supportWorker->id]);
        }

        if ($oakwood) {
            UserServiceAccess::create([
                'user_id' => $invited->id,
                'service_id' => $oakwood->id,
                'access_type' => 'standard',
                'status' => 'active',
            ]);
        }

        // A known, fixed invitation token so the activation journey can be
        // walked locally without reading email. Local prototype only.
        AccountInvitation::create([
            'user_id' => $invited->id,
            'token_hash' => hash('sha256', 'record7-local-activation-token'),
            'status' => 'pending',
            'sent_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);
    }

    /**
     * Two short accounts for walking the interface by hand.
     *
     * The supplied fictional passwords are long, which is right for a test
     * suite and miserable for a person clicking through a UI review twenty
     * times over. These two exist purely so that is bearable.
     *
     * They are NOT part of the supplied package. They sit alongside it, use the
     * same houses, and are as fictional as everything else here. The password
     * is deliberately trivial because this database is local, disposable and
     * contains nobody real — the same guards that protect the rest of this
     * seeder protect these.
     */
    private function seedConvenienceAccounts(): void
    {
        $password = (string) env('RECORD7_TEST_ACCOUNT_PASSWORD', 'precious');

        $organisation = Organisation::first();
        $oakwood = Service::where('name', 'Oakwood House')->first();
        $rosewood = Service::where('name', 'Rosewood House')->first();

        if (! $organisation || ! $oakwood || ! $rosewood) {
            return;
        }

        // A manager: oversees both houses, reaches the access audit, AND can
        // administer medication.
        //
        // A service manager is staff. In a real home they are very often
        // medication trained and work rounds themselves, particularly when a
        // shift is short. A system that refuses them would not stop the round
        // happening — it would push them to borrow somebody else's login, which
        // is the worst possible outcome for a medication record.
        //
        // Note HOW this is done: not by editing the supplied role matrix, which
        // stays exactly as the package defines it, but by an explicit per-house
        // allow gated by competency. That is the correct clinical model as well
        // as the tidier one — whether a person may give a medicine depends on
        // whether they have been assessed, not on their job title. A manager
        // without the competency still cannot, and neither can a support
        // worker.
        $this->convenienceAccount(
            $organisation, [$oakwood, $rosewood], $password,
            'testmanager', 'Test Manager', 'R4', 'manager', true
        );

        // A support worker: works in both houses and can administer, set up the
        // same way Olivia Carter is in the supplied package.
        $this->convenienceAccount(
            $organisation, [$oakwood, $rosewood], $password,
            'teststaff', 'Test Staff', 'R7', 'standard', true
        );
    }

    /** @param  array<int, Service>  $houses */
    private function convenienceAccount(
        Organisation $organisation,
        array $houses,
        string $password,
        string $username,
        string $fullName,
        string $roleCode,
        string $accessType,
        bool $canAdminister
    ): void {
        $role = Role::where('code', $roleCode)->first();

        if (! $role) {
            return;
        }

        $user = User::where('username', $username)->first() ?? new User;

        $user->reference = 'usr_'.$username;
        $user->organisation_id = $organisation->id;
        $user->full_name = $fullName;
        $user->preferred_name = explode(' ', $fullName)[1] ?? $fullName;
        $user->username = $username;
        $user->work_email = $username.'@record7.test';
        $user->employee_reference = 'EMP-'.strtoupper($username);
        $user->password_hash = Hash::make($password);
        $user->account_status = 'active';
        $user->employment_type = 'permanent';
        $user->access_starts_at = now()->subYear();
        $user->access_ends_at = null;
        $user->password_set_at = now();
        $user->failed_attempts = 0;
        $user->locked_until = null;
        $user->save();

        UserRole::firstOrCreate(['user_id' => $user->id, 'role_id' => $role->id]);

        foreach ($houses as $house) {
            $access = UserServiceAccess::firstOrNew([
                'user_id' => $user->id,
                'service_id' => $house->id,
            ]);
            $access->access_type = $accessType;
            $access->status = 'active';
            $access->starts_at = null;
            $access->ends_at = null;
            $access->save();
        }

        MfaMethod::firstOrCreate(
            ['user_id' => $user->id, 'method_type' => 'authenticator_app'],
            [
                'label' => $fullName.' authenticator',
                'is_primary' => true,
                'status' => 'active',
                'registered_at' => now(),
            ]
        );

        if (! $canAdminister) {
            return;
        }

        // The Support Worker role matrix does not include administering, so a
        // real support worker holds an explicit allow per house once their
        // competency is confirmed. Same shape as Olivia Carter.
        $permission = Permission::where('code', 'administer_medication')->first();

        if ($permission) {
            foreach ($houses as $house) {
                UserPermission::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'permission_id' => $permission->id,
                        'service_id' => $house->id,
                    ],
                    [
                        'effect' => 'allow',
                        'status' => 'active',
                        'reason' => 'Competency confirmed for this house.',
                    ]
                );
            }
        }

        // And the competency that gates it, or the allow above is refused.
        foreach (['general_medication', 'prn_medication'] as $code) {
            $type = CompetencyType::where('code', $code)->first();

            if (! $type) {
                continue;
            }

            UserCompetency::firstOrCreate(
                ['user_id' => $user->id, 'competency_type_id' => $type->id, 'service_id' => null],
                [
                    'status' => 'current',
                    'assessed_at' => now()->subMonths(2),
                    'review_due_at' => now()->addMonths(10),
                    'evidence_reference' => 'TEST-EVIDENCE-LOCAL',
                    'notes' => 'Fictional competency for local interface review.',
                ]
            );
        }
    }

    private function report(): void
    {
        $organisation = Organisation::first();

        $this->command?->newLine();
        $this->command?->info('Record7 Section 0 fixture loaded.');
        $this->command?->line('  Organisation to type      '.($organisation?->display_name ?? '?'));
        $this->command?->line('  Sign in at                /record7/login');
        $this->command?->line('  Verification code         '
            .(config('record7.mfa.prototype_code') ?: 'not set — set RECORD7_PROTOTYPE_MFA_CODE'));
        $this->command?->line('  Activation link           /record7/activate/record7-local-activation-token');
        $this->command?->newLine();

        foreach (User::orderBy('username')->get() as $user) {
            $this->command?->line(sprintf(
                '  %-16s %-24s %s',
                $user->username,
                $user->primaryRole()?->name ?? 'no role',
                self::PASSWORDS[$user->username] ?? '(no password — invited)'
            ));
        }

        $this->command?->newLine();
        $this->command?->line('  Services  '.Service::orderBy('name')
            ->get()->map(fn ($s) => $s->name.' ['.$s->status.']')->implode('   '));
        $this->command?->line('  Audit events  '.AccessAuditEvent::count());
        $this->command?->newLine();
        $this->command?->line('  Short accounts for walking the interface by hand:');
        $this->command?->line('    testmanager   Service Manager, both houses');
        $this->command?->line('    teststaff     Support Worker, both houses, can administer');
        $this->command?->line('    password      '.env('RECORD7_TEST_ACCOUNT_PASSWORD', 'precious'));
    }
}
