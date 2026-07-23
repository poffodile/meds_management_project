<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The home switcher (CR-06) and — more importantly — its tenant-separation guarantee.
 *
 * The switcher lets a multi-home manager choose which home they are viewing. The
 * security property that matters is that they can ONLY ever resolve to a home in their
 * own home_id list, even if the session is tampered to name another company's home.
 *
 * Uses DatabaseTransactions (NOT RefreshDatabase — that would wipe the real DB; see
 * MedicationRoundSafetyTest for the full explanation).
 */
class HomeSwitcherTest extends TestCase
{
    use DatabaseTransactions;

    /** Invoke the private resolver on a controller as the given user with a given session. */
    private function resolve(User $user, ?int $sessionHome): int
    {
        $this->actingAs($user);
        if ($sessionHome !== null) {
            session(['active_home_id' => $sessionHome]);
        } else {
            session()->forget('active_home_id');
        }

        $controller = new \App\Http\Controllers\frontEnd\Frontend2Controller();
        $m = new \ReflectionMethod($controller, 'getHomeId');
        $m->setAccessible(true);

        return (int) $m->invoke($controller);
    }

    public function test_a_manager_with_one_home_resolves_to_it(): void
    {
        $u = User::find(427); // Phil Holt, home_id "101"
        $this->assertNotNull($u);
        $this->assertSame(101, $this->resolve($u, null));
    }

    public function test_a_multi_home_manager_defaults_to_their_first_home(): void
    {
        $u = $this->multiHomeUser();
        // real_home_id, not home_id — home_id is App\User's resolved-single accessor.
        $first = (int) explode(',', $u->real_home_id)[0];
        $this->assertSame($first, $this->resolve($u, null),
            'A multi-home manager with no selection did not default to their first home.');
    }

    public function test_switching_to_an_allowed_home_takes_effect(): void
    {
        $u = $this->multiHomeUser();
        $ids = collect(explode(',', $u->real_home_id))->map(fn ($h) => (int) trim($h))->filter()->values();
        $second = (int) $ids[1];

        $this->assertSame($second, $this->resolve($u, $second),
            'Selecting a home the manager is allowed did not take effect.');
    }

    /** The security test: a session naming a home outside the user's list must be ignored. */
    public function test_a_tampered_session_cannot_cross_tenants(): void
    {
        $u = User::find(427); // allowed: [101] only
        $foreignHome = \App\Home::where('id', '!=', 101)->value('id'); // some home they don't have
        $this->assertNotNull($foreignHome);

        $resolved = $this->resolve($u, (int) $foreignHome);

        $this->assertSame(101, $resolved,
            'A tampered active_home_id resolved to a home outside the user\'s own list — tenant separation breached.');
        $this->assertNotSame((int) $foreignHome, $resolved);
    }

    /** The switch endpoint must reject a home the user has no access to. */
    public function test_switch_endpoint_rejects_a_forbidden_home(): void
    {
        $u = User::find(427);
        $foreignHome = \App\Home::where('id', '!=', 101)->value('id');

        $this->actingAs($u)->withoutMiddleware()
            ->post('/frontend2/switch-home', ['home_id' => (int) $foreignHome]);

        // The session must not have been changed to the forbidden home.
        $this->assertNotSame((int) $foreignHome, (int) session('active_home_id'));
    }

    private function multiHomeUser(): User
    {
        // Query the raw column (comma list). Exclude type O — their real_home_id is derived
        // from the company, so the stored column may not itself be a list.
        $u = User::where('home_id', 'like', '%,%')
            ->whereIn('user_type', ['M', 'CM', 'A'])
            ->where('is_deleted', 0)
            ->first();

        if (! $u || count(array_filter(explode(',', (string) $u->real_home_id))) < 2) {
            $this->markTestSkipped('No multi-home manager in the database.');
        }

        return $u;
    }
}
