<?php

namespace Tests\Feature;

use App\Models\Frontend4AuthenticationEvent;
use App\Models\Frontend4Credential;
use App\Models\Frontend4User;
use App\Services\Frontend4\AuthenticationSecurityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class Frontend4AuthenticationIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_legacy_login_and_admin_routes_still_use_legacy_controllers(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertSame(
            'App\\Http\\Controllers\\frontEnd\\UserController@login',
            $routes->getByName('login')->getActionName()
        );
        $this->assertSame(
            'App\\Http\\Controllers\\backEnd\\AdminController@login',
            $routes->match(Request::create('/admin/login', 'GET'))->getActionName()
        );
        $this->assertSame(
            'App\\Http\\Controllers\\Android\\AndroidApiController@user_login',
            app('router')->getRoutes()->match(Request::create('/api/user-login', 'POST'))->getActionName()
        );
    }

    public function test_frontend4_has_its_own_guard_and_login_routes(): void
    {
        $this->assertSame('frontend4_users', config('auth.guards.frontend4.provider'));
        $this->assertSame(Frontend4User::class, config('auth.providers.frontend4_users.model'));

        $this->get('/frontend4')->assertRedirect('/frontend4/login');
        $this->get('/frontend4/login')->assertOk();
        $this->get('/frontend4/logout')->assertStatus(405);
    }

    public function test_frontend4_password_reset_does_not_change_the_legacy_password(): void
    {
        $this->requireFrontend4Schema();
        $user = Frontend4User::where('status', 1)->where('is_deleted', 0)->firstOrFail();
        $legacyPasswordHash = $user->getRawOriginal('password');
        $request = Request::create('/frontend4/forgot-password', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.0.2.10',
            'HTTP_USER_AGENT' => 'Care One OS test client',
        ]);
        $security = app(AuthenticationSecurityService::class);
        $plainToken = $security->issuePasswordToken($user, $request);
        $token = $security->validPasswordToken($plainToken);
        $newPassword = Str::password(20);

        $this->assertNotNull($token);
        $security->consumePasswordToken($token, $user, $request, $newPassword);

        $this->assertSame($legacyPasswordHash, $user->fresh()->getRawOriginal('password'));
        $this->assertTrue(Hash::check(
            $newPassword,
            Frontend4Credential::where('user_id', $user->id)->value('password_hash')
        ));
        $this->assertNull($security->validPasswordToken($plainToken));
    }

    public function test_frontend4_authentication_events_are_append_only(): void
    {
        $this->requireFrontend4Schema();
        $event = Frontend4AuthenticationEvent::create([
            'event_type' => 'test_event',
            'successful' => true,
            'ip_address' => '192.0.2.10',
            'created_at' => now(),
        ]);

        $this->expectException(\LogicException::class);
        $event->update(['event_type' => 'changed']);
    }

    private function requireFrontend4Schema(): void
    {
        if (
            ! Schema::hasTable('frontend4_credentials')
            || ! Schema::hasTable('frontend4_password_tokens')
            || ! Schema::hasTable('frontend4_authentication_events')
        ) {
            $this->markTestSkipped('Run the Frontend 4 authentication migration first.');
        }
    }
}
