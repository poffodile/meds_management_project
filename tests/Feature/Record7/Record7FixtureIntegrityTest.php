<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Client;
use Illuminate\Support\Facades\DB;

/**
 * The fictional environment has to survive being rebuilt.
 *
 * WHY THIS EXISTS.
 * Section 0 used to clear the access layer — users, roles, services,
 * organisations — with foreign key checks switched off, and leave every
 * clinical row that pointed at them behind. It then rebuilt the houses with NEW
 * ids, so the previous run's data was not replaced but stranded. Fifteen of the
 * eighteen rows in each retired stock table were exactly that: six reseeds,
 * each leaving its predecessor.
 *
 * Section 2.6 turned the leak into a failure. Lifecycle events carry a foreign
 * key to the round and a delete guard, so the first reseed after a round had
 * been closed died on "Cannot delete or update a parent row" — and every
 * section's verification rests on being able to rebuild a clean fixture.
 *
 * THE STRUCTURAL TEST IS THE IMPORTANT ONE. Asserting today's data is clean
 * only catches today. Asserting that the clear-out NAMES every table pointing
 * at a service or a person catches the next section's table before it strands
 * anything — which is how this was missed twice.
 */
class Record7FixtureIntegrityTest extends Record7TestCase
{
    /** Every append-only guard that must be in place once a rebuild settles. */
    private const GUARDS = [
        'record7_administrations_no_delete',
        'record7_administrations_no_rewrite',
        'record7_administrations_validate_insert',
        'record7_cd_register_no_delete',
        'record7_cd_register_no_rewrite',
        'record7_cd_register_validate_insert',
        'record7_prn_attempts_no_delete',
        'record7_prn_attempts_no_rewrite',
        'record7_round_lifecycle_no_delete',
        'record7_round_lifecycle_no_rewrite',
        'record7_stock_attempts_no_delete',
        'record7_stock_attempts_no_rewrite',
        'record7_stock_balances_no_drift',
        'record7_stock_balances_validate_insert',
        'record7_stock_events_retired_insert',
        'record7_stock_levels_retired_insert',
        'record7_stock_movements_no_delete',
        'record7_stock_movements_no_rewrite',
        'record7_stock_movements_validate_insert',
        'record7_welfare_checks_no_delete',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Client::where('reference', 'like', 'OAK-%')->exists()) {
            $this->markTestSkipped('Seed the Section 1 fixtures first.');
        }
    }

    private function database(): string
    {
        return DB::connection('record7')->getDatabaseName();
    }

    /* ── The clear-out has to know about every dependent table ──────────── */

    /**
     * THE REGRESSION THAT MATTERS.
     *
     * Anything with a foreign key to a service or a person is destroyed when
     * Section 0 rebuilds them. A table the clear-out does not name is a table
     * whose rows are stranded on the next reseed, and nothing else in the
     * suite would notice until somebody counted the orphans by hand.
     */
    public function test_the_section_0_clear_out_names_every_table_that_depends_on_it(): void
    {
        $source = file_get_contents(database_path('seeders/Record7Section0Seeder.php'));
        $start = strpos($source, 'private function clearExisting');
        $this->assertNotFalse($start, 'Section 0 no longer has a clear-out.');

        $body = substr($source, $start);
        preg_match_all("/'(record7_[a-z_]+)'/", $body, $found);
        $cleared = array_unique($found[1]);

        $dependent = DB::connection('record7')->select(
            'SELECT DISTINCT TABLE_NAME AS t
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ?
                AND REFERENCED_TABLE_NAME IN (?, ?, ?)',
            [$this->database(), 'record7_services', 'record7_clients', 'record7_users']
        );

        /* THE ONE DELIBERATE EXCEPTION.
         *
         * The access audit is append-only and outlives the accounts it
         * describes — that is the point of it. A trail that was wiped whenever
         * somebody rebuilt the fixture would not be a trail. Its rows naming a
         * deleted user are history, not debris, and its own no-delete trigger
         * would refuse anyway. */
        $keptOnPurpose = ['record7_access_audit_events'];

        $missing = [];

        foreach ($dependent as $row) {
            if (in_array($row->t, $keptOnPurpose, true)) {
                continue;
            }

            if (! in_array($row->t, $cleared, true)) {
                $missing[] = $row->t;
            }
        }

        sort($missing);

        $this->assertSame(
            [],
            $missing,
            "These tables point at a service, a person or a user, and the Section 0 clear-out "
            ."does not name them. Their rows will be stranded on the next reseed: "
            .implode(', ', $missing)
        );
    }

    /** And it puts every guard back, whatever happened in between. */
    public function test_the_clear_out_restores_every_guard_it_lifts(): void
    {
        $source = file_get_contents(database_path('seeders/Record7Section0Seeder.php'));

        preg_match_all("/DROP TRIGGER IF EXISTS \{\\\$trigger}/", $source, $drops);
        $this->assertNotEmpty($drops[0], 'Section 0 no longer lifts any guard.');

        // Every guard it knows how to lift, it also knows how to rebuild.
        $start = strpos($source, 'private const FIXTURE_GUARDS');
        $this->assertNotFalse($start, 'Section 0 has no list of the guards it lifts.');

        $body = substr($source, $start, strpos($source, 'private function liftFixtureGuards') - $start);
        preg_match_all("/'(record7_[a-z_]+)' => <<<'SQL'/", $body, $named);

        foreach ($named[1] as $trigger) {
            $this->assertStringContainsString(
                "CREATE TRIGGER {$trigger}",
                $body,
                "Section 0 drops {$trigger} and cannot put it back."
            );
        }

        $this->assertGreaterThanOrEqual(7, count($named[1]));
    }

    /* ── And the environment it produced is actually clean ──────────────── */

    public function test_every_append_only_guard_is_in_place(): void
    {
        $present = array_map(
            fn ($row) => $row->t,
            DB::connection('record7')->select(
                'SELECT TRIGGER_NAME AS t FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = ?',
                [$this->database()]
            )
        );

        $missing = array_values(array_diff(self::GUARDS, $present));

        $this->assertSame(
            [],
            $missing,
            'An append-only guard is not in place: '.implode(', ', $missing)
        );
    }

    public function test_nothing_is_stranded_from_a_previous_seed(): void
    {
        $orphans = [
            'lifecycle events with no round' => 'SELECT COUNT(*) n FROM record7_round_lifecycle_events e
                LEFT JOIN record7_rounds r ON r.id = e.round_id WHERE r.id IS NULL',
            'rounds with no service' => 'SELECT COUNT(*) n FROM record7_rounds x
                LEFT JOIN record7_services s ON s.id = x.service_id WHERE s.id IS NULL',
            'stock movements with no service' => 'SELECT COUNT(*) n FROM record7_stock_movements x
                LEFT JOIN record7_services s ON s.id = x.service_id WHERE s.id IS NULL',
            'stock balances with no service' => 'SELECT COUNT(*) n FROM record7_stock_balances x
                LEFT JOIN record7_services s ON s.id = x.service_id WHERE s.id IS NULL',
            'clients with no service' => 'SELECT COUNT(*) n FROM record7_clients x
                LEFT JOIN record7_services s ON s.id = x.service_id WHERE s.id IS NULL',
            'administrations with no client' => 'SELECT COUNT(*) n FROM record7_administrations x
                LEFT JOIN record7_clients c ON c.id = x.client_id WHERE c.id IS NULL',
            'register entries with no client' => 'SELECT COUNT(*) n FROM record7_cd_register x
                LEFT JOIN record7_clients c ON c.id = x.client_id WHERE c.id IS NULL',
        ];

        foreach ($orphans as $what => $sql) {
            $this->assertSame(
                0,
                (int) DB::connection('record7')->selectOne($sql)->n,
                "The fixture carries {$what}, which is what a broken reseed leaves behind."
            );
        }
    }

    public function test_no_reference_is_used_twice(): void
    {
        foreach ([
            'record7_clients', 'record7_prescriptions', 'record7_administrations',
            'record7_review_items', 'record7_cd_register', 'record7_stock_movements',
            'record7_round_lifecycle_events',
        ] as $table) {
            $this->assertSame(
                0,
                (int) DB::connection('record7')->selectOne(
                    "SELECT COUNT(*) n FROM (SELECT reference FROM {$table}
                      GROUP BY reference HAVING COUNT(*) > 1) d"
                )->n,
                "{$table} has a duplicated reference, which is how a half-cleared reseed shows up."
            );
        }
    }

    /**
     * A rebuild that completes but leaves nobody able to work has not restored
     * anything worth having.
     */
    public function test_the_section_2_7_workflows_survive_a_rebuild(): void
    {
        $policy = app(\App\Services\Record7\AccessPolicy::class);
        $rosewood = $this->house('Rosewood House')->id;

        foreach ([
            ['sarah.ahmed', 'stock_management', true],
            ['sarah.ahmed', 'reconciliation', true],
            ['daniel.evans', 'stock_management', true],
            ['daniel.evans', 'correction_approval', true],
            ['daniel.evans', 'reconciliation', false],
            ['olivia.carter', 'stock_management', false],
        ] as [$username, $permission, $expected]) {
            $this->assertSame(
                $expected,
                $policy->allows($this->user($username), $permission, $rosewood),
                "{$username} and {$permission} came back wrong after the rebuild."
            );
        }

        $this->assertGreaterThanOrEqual(5, \App\Models\Record7\StockBalance::count());
        $this->assertGreaterThanOrEqual(2, app(\App\Services\Record7\StockLedger::class)
            ->unresolvedDiscrepancies(
                \App\Models\Record7\StockBalance::whereHas(
                    'medicine', fn ($q) => $q->where('name', 'Senna')
                )->firstOrFail()
            )->count());
    }
}
