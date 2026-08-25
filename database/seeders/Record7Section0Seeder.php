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
        });

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

    private function clearExisting(): void
    {
        $connection = DB::connection('record7');
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'record7_login_sessions', 'record7_mfa_methods', 'record7_user_competencies',
            'record7_user_permissions', 'record7_user_service_access', 'record7_user_roles',
            'record7_account_invitations', 'record7_password_resets',
            'record7_users', 'record7_role_permissions', 'record7_permissions',
            'record7_roles', 'record7_competency_types', 'record7_services',
            'record7_organisations',
        ] as $table) {
            $connection->table($table)->delete();
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

        // A manager: oversees both houses, reaches the access audit, and does
        // not administer medication. That separation is the fixture's design.
        $this->convenienceAccount(
            $organisation, [$oakwood, $rosewood], $password,
            'testmanager', 'Test Manager', 'R4', 'manager', false
        );

        // A support worker: works in both houses and CAN administer, because a
        // staff account that cannot do the job is not much use for reviewing
        // the job. Mirrors how Olivia Carter is set up in the package.
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
