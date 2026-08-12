<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4AuthenticationEvent;
use App\Models\Frontend4Credential;
use App\Models\Frontend4User;
use App\Services\Frontend4\AuthenticationSecurityService;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
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
        $this->post('/frontend4/login/organisation', [])->assertSessionHasErrors('company_name');
        $this->get('/frontend4/select-service')->assertRedirect('/frontend4/login');
        $this->get('/frontend4/services')->assertNotFound();
        $this->get('/frontend4/logout')->assertStatus(405);
    }

    public function test_password_verified_user_with_one_service_is_taken_straight_to_today(): void
    {
        $this->requireAccessScopeSchema();
        [$user, $service] = $this->activeUserAndService();
        $this->assignServices($user, [$service]);
        $password = 'Known-password!123';
        $this->setFrontend4Password($user, $password);
        $this->allowMedicationAccess();

        $this->withSession($this->pendingOrganisationSession((int) $service->admin_id))
            ->post('/frontend4/login', [
                'username' => $user->user_name,
                'password' => $password,
            ])
            ->assertRedirect('/frontend4');

        $this->assertAuthenticatedAs($user, 'frontend4');
        $this->assertSame((int) $service->admin_id, (int) session('frontend4.organisation_id'));
        $this->assertSame((int) $service->id, (int) session('frontend4.active_service_id'));
    }

    public function test_organisation_is_resolved_before_credentials_are_requested(): void
    {
        $this->requireAccessScopeSchema();
        [, $service] = $this->activeUserAndService();
        $slug = 'login-test-'.Str::uuid();
        DB::table('admin')->where('id', $service->admin_id)->update(['frontend4_slug' => $slug]);

        $this->post('/frontend4/login/organisation', ['company_name' => $slug])
            ->assertRedirect('/frontend4/login')
            ->assertSessionHas('frontend4.pending_organisation_id', (int) $service->admin_id);

        $this->assertGreaterThan(0, (int) session('frontend4.pending_organisation_at'));
        $this->assertGuest('frontend4');
    }

    public function test_password_verified_user_with_multiple_services_must_choose_before_clinical_access(): void
    {
        $this->requireAccessScopeSchema();
        [$user, $service] = $this->activeUserAndService(true);
        $other = Home::where('admin_id', $service->admin_id)
            ->where('is_deleted', 0)
            ->where('id', '!=', $service->id)
            ->firstOrFail();
        $this->assignServices($user, [$service, $other]);
        $password = 'Known-password!123';
        $this->setFrontend4Password($user, $password);
        $this->allowMedicationAccess();

        $this->withSession($this->pendingOrganisationSession((int) $service->admin_id))
            ->post('/frontend4/login', [
                'username' => $user->user_name,
                'password' => $password,
            ])
            ->assertRedirect('/frontend4/select-service');

        $this->assertAuthenticatedAs($user, 'frontend4');
        $this->assertNull(session('frontend4.active_service_id'));

        $this->get('/frontend4')->assertRedirect('/frontend4/select-service');
        $this->get('/frontend4/select-service')->assertOk();
        $this->post('/frontend4/select-service', ['service_id' => $other->id])
            ->assertRedirect('/frontend4');
        $this->assertSame((int) $other->id, (int) session('frontend4.active_service_id'));
    }

    public function test_user_with_no_active_service_is_not_left_partially_authenticated(): void
    {
        $this->requireAccessScopeSchema();
        [$user, $service] = $this->activeUserAndService();
        $this->assignServices($user, [$service], false);
        $password = 'Known-password!123';
        $this->setFrontend4Password($user, $password);
        $this->allowMedicationAccess();

        $this->withSession($this->pendingOrganisationSession((int) $service->admin_id))
            ->post('/frontend4/login', [
                'username' => $user->user_name,
                'password' => $password,
            ])
            ->assertRedirect('/frontend4/login');

        $this->assertGuest('frontend4');
        $this->assertDatabaseHas('frontend4_authentication_events', [
            'user_id' => $user->id,
            'event_type' => 'no_active_service',
            'successful' => 0,
        ]);
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

    private function requireAccessScopeSchema(): void
    {
        $this->requireFrontend4Schema();
        if (
            ! Schema::hasTable('frontend4_user_service_access')
            || ! Schema::hasColumn('admin', 'frontend4_slug')
        ) {
            $this->markTestSkipped('Run the Frontend 4 access-scope migration first.');
        }
    }

    private function activeUserAndService(bool $needsTwoServices = false): array
    {
        $services = Home::where('home.is_deleted', 0)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('admin')
                    ->whereColumn('admin.id', 'home.admin_id')
                    ->where('admin.is_deleted', 0);
            })
            ->get();

        if ($needsTwoServices) {
            $services = $services->groupBy('admin_id')->first(fn ($group) => $group->count() >= 2) ?? collect();
        }

        $service = $services->first();
        $user = Frontend4User::where('status', 1)
            ->where('is_deleted', 0)
            ->whereNotNull('user_name')
            ->where('user_name', '!=', '')
            ->first();
        if (! $service || ! $user) {
            $this->markTestSkipped('The fixture database has no suitable active user and organisation services.');
        }

        return [$user, $service];
    }

    private function assignServices(Frontend4User $user, array $services, bool $active = true): void
    {
        $organisationId = (int) $services[0]->admin_id;
        DB::table('frontend4_user_service_access')
            ->where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->delete();

        foreach ($services as $service) {
            DB::table('frontend4_user_service_access')->insert([
                'user_id' => $user->id,
                'organisation_id' => $organisationId,
                'service_id' => $service->id,
                'active' => $active ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function setFrontend4Password(Frontend4User $user, string $password): void
    {
        Frontend4Credential::updateOrCreate(
            ['user_id' => $user->id],
            [
                'password_hash' => Hash::make($password),
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]
        );
    }

    private function allowMedicationAccess(): void
    {
        $resolver = Mockery::mock(RoleResolver::class);
        $resolver->shouldReceive('hasAccess')->andReturnTrue();
        $this->app->instance(RoleResolver::class, $resolver);
    }

    private function pendingOrganisationSession(int $organisationId): array
    {
        return [
            'frontend4.pending_organisation_id' => $organisationId,
            'frontend4.pending_organisation_at' => time(),
        ];
    }
}
