<?php

namespace Tests\Feature;

use App\Models\PasswordActionToken;
use App\Models\AuthenticationEvent;
use App\Services\AuthenticationSecurityService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_password_action_token_is_hashed_expiring_and_single_use(): void
    {
        $this->requireHardenedSchema();

        $user = User::query()
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->firstOrFail();
        $request = Request::create('/forgot-password', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_USER_AGENT' => 'Care One OS test client',
        ]);
        $security = app(AuthenticationSecurityService::class);

        $plainToken = $security->issuePasswordToken($user, $request);
        $storedToken = PasswordActionToken::query()
            ->where('authenticatable_type', 'user')
            ->where('authenticatable_id', $user->id)
            ->latest('created_at')
            ->firstOrFail();

        $this->assertSame(64, strlen($plainToken));
        $this->assertNotSame($plainToken, $storedToken->token_hash);
        $this->assertSame(hash('sha256', $plainToken), $storedToken->token_hash);
        $this->assertTrue($storedToken->expires_at->isFuture());

        $security->consumePasswordToken(
            $storedToken,
            $user,
            $request,
            Hash::make('SecurePassword!2026')
        );

        $this->assertNull($security->validPasswordToken($plainToken));
        $this->assertNotNull($storedToken->fresh()->used_at);
        $this->assertTrue(Hash::check('SecurePassword!2026', $user->fresh()->password));
    }

    public function test_logout_endpoints_reject_get_requests(): void
    {
        $this->get('/logout')->assertStatus(405);
        $this->get('/admin/logout')->assertStatus(405);
    }

    public function test_authentication_events_are_append_only(): void
    {
        $this->requireHardenedSchema();

        $event = AuthenticationEvent::create([
            'event_type' => 'test_event',
            'successful' => true,
            'ip_address' => '192.0.2.10',
        ]);

        $this->expectException(\LogicException::class);
        $event->update(['event_type' => 'changed']);
    }

    public function test_maintenance_cache_route_is_not_available_during_tests(): void
    {
        $this->get('/clear')->assertNotFound();
    }

    private function requireHardenedSchema(): void
    {
        if (
            ! Schema::hasTable('password_action_tokens')
            || ! Schema::hasTable('authentication_events')
            || ! Schema::hasColumn('user', 'failed_login_attempts')
        ) {
            $this->markTestSkipped('Run the authentication hardening migration first.');
        }
    }
}
