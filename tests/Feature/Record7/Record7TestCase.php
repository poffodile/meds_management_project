<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\Organisation;
use App\Models\Record7\ScheduledDose;
use App\Models\Record7\Service;
use App\Models\Record7\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
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

    /**
     * Mid-afternoon: the morning round is behind us and the evening one is not.
     *
     * Chosen so that a test which reaches backwards a few hours to make a dose
     * late stays inside the same day, and one that looks at "this morning"
     * finds something that has already happened.
     */
    private const FIXTURE_HOUR = 14;

    /**
     * Opt in where the tests are about the medication DAY.
     *
     * Off by default, deliberately. Section 0 has its own time semantics — an
     * activation link seeded on Thursday expires on Sunday morning, and pinning
     * the clock to Sunday afternoon would expire it. Those tests want the real
     * clock; the medication-day suites want the fixture's.
     */
    protected bool $anchorClockToFixtureDay = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Organisation::where('display_name', self::ORGANISATION)->exists()) {
            $this->markTestSkipped(
                'Seed the Record7 fixture first: RECORD7_DB_DATABASE=record7_test '
                .'RECORD7_ALLOW_FIXTURE_SEED=true php artisan db:seed --class=Record7Section0Seeder'
            );
        }

        if ($this->anchorClockToFixtureDay) {
            $this->anchorTheClockToTheFixture();
        }
    }

    protected function tearDown(): void
    {
        // Never leave a frozen clock behind for whatever runs next.
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Run the suite at a fixed point in the fixture's own day.
     *
     * WHY THIS EXISTS.
     * The fixture is anchored to whenever it was seeded, and a great many of
     * these tests describe behaviour that only exists at certain times: a dose
     * is late, this morning's round is behind us, a medicine given three hours
     * ago is still within today. Run at ten past midnight, all of that is
     * false — the morning is in the future, and a test reaching back five hours
     * to make something late lands on yesterday and falls straight out of the
     * round's own date filter.
     *
     * That is not the product misbehaving. It is a suite whose invariants
     * depend on the hour, being run at an hour nobody had in mind.
     *
     * So the clock is pinned to the FIXTURE'S date, read from the data rather
     * than written down here — no literal date appears in this file, and
     * reseeding on any day still works. Production is untouched: this is a
     * test-time freeze and nothing else.
     *
     * A test that needs a different moment overrides it with its own
     * Carbon::setTestNow(), and tearDown clears whichever was in force.
     */
    private function anchorTheClockToTheFixture(): void
    {
        $anchor = ScheduledDose::orderByDesc('due_at')->value('due_at');

        // Section 0 fixtures carry no doses at all; those tests do not care
        // what time it is, so the real clock is left alone.
        if ($anchor === null) {
            return;
        }

        Carbon::setTestNow(
            Carbon::parse($anchor)->startOfDay()->addHours(self::FIXTURE_HOUR)
        );
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
