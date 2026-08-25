<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Base for the Record7 Section 0 suites.
 *
 * FIXTURE
 * Every test runs against the supplied Omega Care Group package, loaded into
 * record7_test by Record7Section0Seeder. Nothing is invented here: the eight
 * named accounts, four houses and their roles, permission rules and
 * competencies are exactly as supplied.
 *
 * Changes made by a test are rolled back on the record7 connection, so the
 * fixture is the same for the next one.
 */
abstract class Record7TestCase extends TestCase
{
    use DatabaseTransactions;

    /** Roll back Record7's own connection, not the legacy one. */
    protected $connectionsToTransact = ['record7'];

    /** The documented fictional passwords. */
    protected const PASSWORDS = [
        'olivia.carter' => 'Record7-Test-Staff-2026!',
        'daniel.evans' => 'Record7-Test-Manager-2026!',
        'priya.nair' => 'Record7-Test-Admin-2026!',
        'sarah.ahmed' => 'Record7-Test-MedLead-2026!',
        'noah.williams' => 'Record7-Test-MedAdmin-2026!',
        'maya.thompson' => 'Record7-Test-Reviewer-2026!',
        'grace.taylor' => 'Record7-Test-Agency-2026!',
        'ethan.cole' => 'Record7-Test-Suspended-2026!',
    ];

    protected const ORGANISATION = 'Omega Care Group';

    protected const CODE = '246810';

    protected function setUp(): void
    {
        parent::setUp();

        if (! Organisation::where('display_name', self::ORGANISATION)->exists()) {
            $this->markTestSkipped(
                'Seed the Record7 fixture first: RECORD7_DB_DATABASE=record7_test '
                .'RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section0Seeder'
            );
        }
    }

    protected function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    protected function house(string $name): Service
    {
        return Service::where('name', $name)->firstOrFail();
    }

    protected function organisation(): Organisation
    {
        return Organisation::where('display_name', self::ORGANISATION)->firstOrFail();
    }

    /**
     * Assert a response served Record7's own bundle and nobody else's.
     *
     * The markup differs by build mode: with Vite running it is the source
     * path, and from a production build it is a hashed asset. Asserting on
     * either alone makes the suite depend on whether a dev server happens to be
     * up, which is not what these tests are about.
     */
    protected function assertServedRecord7Bundle(\Illuminate\Testing\TestResponse $response): void
    {
        $html = $response->getContent();

        $this->assertTrue(
            str_contains($html, 'resources/js/r7.jsx')      // Vite dev server
            || (bool) preg_match('#/build/assets/r7-[^"]+\.js#', $html),  // built
            'The page did not serve the Record7 bundle.'
        );

        foreach (['f4.jsx', 'f3.jsx', '/assets/f4-', '/assets/f3-', '/assets/app-'] as $foreign) {
            $this->assertStringNotContainsString($foreign, $html, 'Another front end leaked in: '.$foreign);
        }
    }

    /** Walk the real screens: organisation, credentials, verification. */
    protected function signIn(string $username, ?string $password = null): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);
        $this->post('/record7/login/credentials', [
            'username' => $username,
            'password' => $password ?? self::PASSWORDS[$username],
        ]);
        $this->post('/record7/verify', ['code' => self::CODE]);
    }

    /** Sign in and open a named house. */
    protected function signInAt(string $username, string $house): void
    {
        $this->signIn($username);
        $this->post('/record7/houses', ['house_id' => $this->house($house)->id]);
    }
}
