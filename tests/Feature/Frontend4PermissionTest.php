<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4AuthenticationEvent;
use App\Models\Frontend4User;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\Permissions;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class Frontend4PermissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_role_matrix_applies_inheritance_and_clinical_separation(): void
    {
        $permissions = app(Permissions::class);

        $this->assertTrue($permissions->allows(RoleResolver::CARER, Permissions::VIEW_CLIENTS));
        $this->assertTrue($permissions->allows(RoleResolver::CARER, Permissions::RECORD_ADMINISTRATION));
        $this->assertFalse($permissions->allows(RoleResolver::CARER, Permissions::CORRECT_RECORD));

        $this->assertTrue($permissions->allows(RoleResolver::LEAD, Permissions::CORRECT_RECORD));
        $this->assertFalse($permissions->allows(RoleResolver::LEAD, Permissions::MANAGE_PRESCRIPTION));

        $this->assertTrue($permissions->allows(RoleResolver::MANAGER, Permissions::MANAGE_PRESCRIPTION));
        $this->assertTrue($permissions->allows(RoleResolver::MANAGER, Permissions::MANAGE_CLIENTS));
        $this->assertTrue($permissions->allows(RoleResolver::MANAGER, Permissions::EXPORT_REPORT));

        $this->assertTrue($permissions->allows(RoleResolver::ADMIN, Permissions::MANAGE_SETTINGS));
        $this->assertTrue($permissions->allows(RoleResolver::ADMIN, Permissions::VIEW_CLIENTS));
        $this->assertTrue($permissions->allows(RoleResolver::ADMIN, Permissions::MANAGE_CLIENTS));
        $this->assertFalse($permissions->allows(RoleResolver::ADMIN, Permissions::RECORD_ADMINISTRATION));
        $this->assertFalse($permissions->allows(RoleResolver::ADMIN, Permissions::CORRECT_RECORD));
        $this->assertFalse($permissions->allows(RoleResolver::ADMIN, Permissions::MANAGE_PRESCRIPTION));
        $this->assertSame([], $permissions->forRole(RoleResolver::NONE));
        $this->assertSame([], $permissions->forRole('unrecognised-role'));
    }

    public function test_explicit_unknown_access_level_fails_closed(): void
    {
        $user = (object) [
            'access_level' => 2147483647,
            'user_type' => 'A',
        ];

        $this->assertSame(RoleResolver::NONE, app(RoleResolver::class)->resolve($user));
    }

    public function test_every_frontend4_clinical_route_declares_its_server_permission(): void
    {
        $expected = [
            'frontend4.today' => Permissions::VIEW_TODAY,
            'frontend4.round' => Permissions::VIEW_ROUND,
            'frontend4.round.record' => Permissions::RECORD_ADMINISTRATION,
            'frontend4.clients' => Permissions::VIEW_CLIENTS,
            'frontend4.clients.create' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.store' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.edit' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.update' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.lifecycle' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.restore' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.transfer' => Permissions::MANAGE_CLIENTS,
            'frontend4.clients.show' => Permissions::VIEW_CLIENTS,
            'frontend4.clients.medication.status' => Permissions::MANAGE_PRESCRIPTION,
            'frontend4.clients.mar' => Permissions::VIEW_MAR,
            'frontend4.clients.mar.correct' => Permissions::CORRECT_RECORD,
            'frontend4.start' => Permissions::MANAGE_SETTINGS,
        ];

        foreach ($expected as $routeName => $permission) {
            $middleware = app('router')->getRoutes()->getByName($routeName)->gatherMiddleware();
            $this->assertContains('frontend4.auth', $middleware, $routeName);
            $this->assertContains('frontend4.can:'.$permission, $middleware, $routeName);
        }
    }

    public function test_direct_url_is_denied_and_audited_before_controller_runs(): void
    {
        $this->requireAuthenticationEventsTable();
        $user = $this->activeUser();
        $this->bindRole(RoleResolver::CARER);

        $before = Frontend4AuthenticationEvent::where('event_type', 'permission_denied')->count();

        $this->actingAs($user, 'frontend4')
            ->withSession($this->frontend4Session($user))
            ->get('/frontend4/start')
            ->assertForbidden();

        $event = Frontend4AuthenticationEvent::where('event_type', 'permission_denied')
            ->latest('created_at')
            ->first();

        $this->assertSame($before + 1, Frontend4AuthenticationEvent::where('event_type', 'permission_denied')->count());
        $this->assertSame($user->id, $event->user_id);
        $this->assertSame(Permissions::MANAGE_SETTINGS, $event->metadata['permission']);
        $this->assertSame('frontend4.start', $event->metadata['route']);
    }

    public function test_carer_cannot_bypass_hidden_prescription_control_with_post(): void
    {
        $this->requireAuthenticationEventsTable();
        $user = $this->activeUser();
        $this->bindRole(RoleResolver::CARER);

        $this->actingAs($user, 'frontend4')
            ->withSession($this->frontend4Session($user))
            ->post('/frontend4/clients/999999999/medications/999999999/status', ['status' => 'stopped'])
            ->assertForbidden();
    }

    public function test_client_direct_url_cannot_cross_service_boundary(): void
    {
        $user = $this->activeUser();
        $currentHome = (int) $this->accessibleService($user)->id;

        $otherClient = ServiceUser::where('is_deleted', 0)
            ->where('home_id', '!=', $currentHome)
            ->first();
        if (! $otherClient) {
            $this->markTestSkipped('The fixture database has no client in a second service.');
        }

        $this->bindRole(RoleResolver::MANAGER);

        $this->actingAs($user, 'frontend4')
            ->withSession($this->frontend4Session($user, $currentHome))
            ->get('/frontend4/clients/'.$otherClient->id)
            ->assertNotFound();
    }

    private function activeUser(): Frontend4User
    {
        $users = Frontend4User::where('status', 1)->where('is_deleted', 0)->get();

        foreach ($users as $user) {
            if ($this->accessibleService($user)) {
                return $user;
            }
        }

        $this->markTestSkipped('The fixture database has no active Frontend 4 user with access to a live service.');
    }

    private function bindRole(string $role): void
    {
        $resolver = Mockery::mock(RoleResolver::class);
        $resolver->shouldReceive('resolve')->andReturn($role);
        $resolver->shouldReceive('label')->andReturn(RoleResolver::LABELS[$role]);
        $this->app->instance(RoleResolver::class, $resolver);
    }

    private function frontend4Session(Frontend4User $user, ?int $currentHome = null): array
    {
        $service = $this->accessibleService($user, $currentHome);
        if (! $service) {
            $this->markTestSkipped('The fixture user does not have access to the requested live service.');
        }

        $serviceId = (int) $service->id;
        $organisationId = (int) $service->admin_id;
        $allowed = app(AccessContext::class)->allowedServiceIds($user, $organisationId);

        return [
            'frontend4.organisation_id' => $organisationId,
            'frontend4.active_service_id' => $serviceId,
            'frontend4.allowed_service_ids' => $allowed,
            'frontend4.active_location_id' => null,
            'frontend4.active_home_id' => $serviceId,
            'frontend4.allowed_home_ids' => $allowed,
            'frontend4.last_activity' => time(),
        ];
    }

    private function accessibleService(Frontend4User $user, ?int $serviceId = null): ?Home
    {
        $services = Home::where('home.is_deleted', 0)
            ->when($serviceId !== null, fn ($query) => $query->where('home.id', $serviceId))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('admin')
                    ->whereColumn('admin.id', 'home.admin_id')
                    ->where('admin.is_deleted', 0);
            })
            ->get();

        $context = app(AccessContext::class);

        return $services->first(fn (Home $service) => in_array(
            (int) $service->id,
            $context->allowedServiceIds($user, (int) $service->admin_id),
            true
        ));
    }

    private function requireAuthenticationEventsTable(): void
    {
        if (! Schema::hasTable('frontend4_authentication_events')) {
            $this->markTestSkipped('Run the Frontend 4 authentication migration first.');
        }
    }
}
