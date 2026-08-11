<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4AuthenticationEvent;
use App\Models\Frontend4PasswordToken;
use App\Models\Frontend4User;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class Frontend4AccessScopeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_allowed_services_are_restricted_to_the_selected_organisation(): void
    {
        [$user, $service] = $this->userAndService();
        $context = app(AccessContext::class);

        $allowed = $context->allowedServiceIds($user, (int) $service->admin_id);

        $this->assertContains((int) $service->id, $allowed);
        $this->assertSame(0, Home::whereIn('id', $allowed)->where('admin_id', '!=', $service->admin_id)->count());
        $this->assertSame(0, Home::whereIn('id', $allowed)->where('is_deleted', '!=', 0)->count());
    }

    public function test_explicit_service_assignments_override_the_legacy_comma_list(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService(true);
        $legacyIds = $this->legacyServiceIds($user);
        $other = Home::whereIn('id', $legacyIds)
            ->where('admin_id', $service->admin_id)
            ->where('is_deleted', 0)
            ->where('id', '!=', $service->id)
            ->first();
        if (! $other) {
            $this->markTestSkipped('The fixture database has no multi-service user.');
        }

        DB::table('frontend4_user_service_access')->insert([
            'user_id' => $user->id,
            'organisation_id' => $service->admin_id,
            'service_id' => $service->id,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id);
        $this->assertSame([(int) $service->id], $allowed);
        $this->assertNotContains((int) $other->id, $allowed);
    }

    public function test_service_discovery_returns_only_that_users_services_in_the_organisation(): void
    {
        [$user, $service] = $this->userAndService();
        $company = DB::table('admin')->where('id', $service->admin_id)->value('company');
        if (! $company) {
            $this->markTestSkipped('The fixture organisation has no company name.');
        }

        $response = $this->getJson('/frontend4/services?company_name='.urlencode($company).'&username='.urlencode($user->user_name));
        $response->assertOk();
        $ids = collect($response->json('services'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertContains((int) $service->id, $ids);
        $this->assertSame(0, Home::whereIn('id', $ids)->where('admin_id', '!=', $service->admin_id)->count());
        $this->assertEqualsCanonicalizing(
            app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id),
            $ids
        );
    }

    public function test_password_reset_does_not_select_the_same_email_from_another_organisation(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService();
        if (! $user->email) {
            $this->markTestSkipped('The fixture user has no email address.');
        }
        $foreign = Home::where('admin_id', '!=', $service->admin_id)->where('is_deleted', 0)->first();
        $foreignCompany = $foreign
            ? DB::table('admin')->where('id', $foreign->admin_id)->value('company')
            : null;
        if (! $foreignCompany) {
            $this->markTestSkipped('The fixture database has no second named organisation.');
        }

        Mail::fake();
        $before = Frontend4PasswordToken::where('user_id', $user->id)->count();

        $this->post('/frontend4/forgot-password', [
            'company_name' => $foreignCompany,
            'email' => $user->email,
        ])->assertRedirect();

        $this->assertSame($before, Frontend4PasswordToken::where('user_id', $user->id)->count());
    }

    public function test_tampered_cross_organisation_session_is_denied_and_audited(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService();
        $foreign = Home::where('admin_id', '!=', $service->admin_id)->where('is_deleted', 0)->first();
        if (! $foreign) {
            $this->markTestSkipped('The fixture database has no second organisation.');
        }

        $before = Frontend4AuthenticationEvent::where('event_type', 'access_scope_denied')->count();

        $this->actingAs($user, 'frontend4')->withSession([
            'frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $foreign->id,
            'frontend4.active_home_id' => (int) $foreign->id,
            'frontend4.last_activity' => time(),
        ])->get('/frontend4')->assertRedirect('/frontend4/login');

        $this->assertGuest('frontend4');
        $this->assertSame($before + 1, Frontend4AuthenticationEvent::where('event_type', 'access_scope_denied')->count());
    }

    public function test_deleted_service_access_is_revoked_on_the_next_request(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService();
        $service->is_deleted = 1;
        $service->save();

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($service))
            ->get('/frontend4')
            ->assertRedirect('/frontend4/login');

        $this->assertGuest('frontend4');
    }

    public function test_service_switch_cannot_cross_the_organisation_boundary(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService();
        $foreign = Home::where('admin_id', '!=', $service->admin_id)->where('is_deleted', 0)->first();
        if (! $foreign) {
            $this->markTestSkipped('The fixture database has no second organisation.');
        }

        $before = Frontend4AuthenticationEvent::where('event_type', 'access_scope_denied')->count();

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($service))
            ->post('/frontend4/context/service', ['service_id' => $foreign->id])
            ->assertForbidden();

        $this->assertAuthenticatedAs($user, 'frontend4');
        $this->assertSame($before + 1, Frontend4AuthenticationEvent::where('event_type', 'access_scope_denied')->count());
    }

    public function test_location_assignment_limits_clients_inside_the_service(): void
    {
        $this->requireScopeSchema();
        [$user, $service] = $this->userAndService();
        $clients = ServiceUser::where('home_id', $service->id)->where('is_deleted', 0)->limit(2)->get();
        if ($clients->count() < 2) {
            $this->markTestSkipped('The fixture service needs two clients for location isolation.');
        }

        $allowedLocation = DB::table('home_areas')->insertGetId([
            'home_id' => $service->id,
            'name' => 'Scope test allowed',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherLocation = DB::table('home_areas')->insertGetId([
            'home_id' => $service->id,
            'name' => 'Scope test other',
            'is_deleted' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('frontend4_user_location_access')->insert([
            'user_id' => $user->id,
            'organisation_id' => $service->admin_id,
            'service_id' => $service->id,
            'location_id' => $allowedLocation,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ServiceUser::whereKey($clients[0]->id)->update(['home_area_id' => $allowedLocation]);
        ServiceUser::whereKey($clients[1]->id)->update(['home_area_id' => $otherLocation]);

        session([
            'frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $service->id,
        ]);
        $ids = app(AccessContext::class)->allowedClientIds($user);

        $this->assertContains((int) $clients[0]->id, $ids);
        $this->assertNotContains((int) $clients[1]->id, $ids);

        $resolver = Mockery::mock(RoleResolver::class);
        $resolver->shouldReceive('resolve')->andReturn(RoleResolver::CARER);
        $resolver->shouldReceive('label')->andReturn(RoleResolver::LABELS[RoleResolver::CARER]);
        $this->app->instance(RoleResolver::class, $resolver);

        $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($service))
            ->get('/frontend4/clients/'.$clients[1]->id)
            ->assertNotFound();
    }

    private function userAndService(bool $needsMultipleServices = false): array
    {
        $users = Frontend4User::where('status', 1)->where('is_deleted', 0)->get();
        foreach ($users as $user) {
            $services = Home::whereIn('id', $this->legacyServiceIds($user))->where('is_deleted', 0)->get();
            if ($services->isEmpty()) {
                continue;
            }
            if ($needsMultipleServices && $services->where('admin_id', $services->first()->admin_id)->count() < 2) {
                continue;
            }

            return [$user, $services->first()];
        }

        $this->markTestSkipped('The fixture database has no suitable active Frontend 4 user and service.');
    }

    private function legacyServiceIds(Frontend4User $user): array
    {
        return collect(explode(',', (string) $user->real_home_id))
            ->map(fn ($id) => (int) trim($id))->filter()->unique()->values()->all();
    }

    private function sessionFor(Home $service): array
    {
        return [
            'frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $service->id,
            'frontend4.active_home_id' => (int) $service->id,
            'frontend4.last_activity' => time(),
        ];
    }

    private function requireScopeSchema(): void
    {
        if (
            ! Schema::hasTable('frontend4_user_service_access')
            || ! Schema::hasTable('frontend4_user_location_access')
            || ! Schema::hasColumn('service_user', 'home_area_id')
            || ! Schema::hasTable('frontend4_authentication_events')
        ) {
            $this->markTestSkipped('Run the Frontend 4 access-scope migration first.');
        }
    }
}
