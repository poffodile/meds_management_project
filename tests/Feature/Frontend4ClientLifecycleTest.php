<?php

namespace Tests\Feature;

use App\Home;
use App\Models\Frontend4ClientEvent;
use App\Models\Frontend4ClientTransferRequest;
use App\Models\Frontend4User;
use App\ServiceUser;
use App\Services\Frontend4\AccessContext;
use App\Services\Frontend4\ClientRecordService;
use App\Services\Frontend4\RoleResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Mockery;
use Tests\TestCase;

class Frontend4ClientLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_manager_can_create_a_real_client_without_invented_measurements_or_account_fields(): void
    {
        [$user, $service] = $this->userAndService();
        $this->bindRole(RoleResolver::MANAGER);

        $payload = ['name' => 'Lifecycle Test Client', 'start_date' => now()->toDateString()];
        $locations = app(AccessContext::class)->allowedLocationIds($user, (int) $service->admin_id, (int) $service->id);
        if ($locations !== null) {
            $payload['home_area_id'] = $locations[0];
        }
        $response = $this->actingAs($user, 'frontend4')->withSession($this->sessionFor($user, $service))
            ->post('/frontend4/clients', $payload);

        $client = ServiceUser::where('name', 'Lifecycle Test Client')->latest('id')->first();
        $response->assertRedirect('/frontend4/clients/'.$client->id);
        $this->assertSame((int) $service->id, (int) $client->home_id);
        $this->assertSame('active', $client->lifecycle_status);
        $this->assertNull($client->weight);
        $this->assertNull($client->weight_unit);
        $this->assertNull($client->height_unit);
        $this->assertNull($client->user_name);
        $this->assertNull($client->password);
        $this->assertDatabaseHas('frontend4_client_events', [
            'client_id' => $client->id,
            'event_type' => 'created',
            'to_status' => 'active',
        ]);
    }

    public function test_carer_cannot_create_or_change_a_client_record(): void
    {
        [$user, $service] = $this->userAndService();
        $client = $this->clientFor($service, $user);
        $this->bindRole(RoleResolver::CARER);
        $session = $this->sessionFor($user, $service);

        $this->actingAs($user, 'frontend4')->withSession($session)
            ->post('/frontend4/clients', ['name' => 'Denied', 'start_date' => now()->toDateString()])
            ->assertForbidden();
        $this->actingAs($user, 'frontend4')->withSession($session)
            ->post('/frontend4/clients/'.$client->id.'/lifecycle', [
                'lifecycle_status' => 'inactive', 'effective_at' => now(), 'reason' => 'Denied test',
            ])->assertForbidden();
    }

    public function test_lifecycle_change_archives_instead_of_deleting_and_restore_preserves_history(): void
    {
        [$user, $service] = $this->userAndService();
        $client = $this->clientFor($service, $user);
        $client->lifecycle_status = 'active';
        $client->status = 1;
        $client->is_deleted = 0;
        $client->save();

        $records = app(ClientRecordService::class);
        $records->changeLifecycle($client, 'archived', 'Record closed after discharge', now(), $user, (int) $service->admin_id);
        $client->refresh();

        $this->assertSame('archived', $client->lifecycle_status);
        $this->assertSame(0, (int) $client->status);
        $this->assertSame(1, (int) $client->is_deleted);
        $this->assertDatabaseHas('frontend4_client_events', ['client_id' => $client->id, 'event_type' => 'archived']);

        $records->restore($client, 'Archive was applied in error', $user, (int) $service->admin_id);
        $client->refresh();
        $this->assertSame('active', $client->lifecycle_status);
        $this->assertSame(0, (int) $client->is_deleted);
        $this->assertDatabaseHas('frontend4_client_events', ['client_id' => $client->id, 'event_type' => 'restored']);
    }

    public function test_client_events_are_append_only(): void
    {
        [$user, $service] = $this->userAndService();
        $client = $this->clientFor($service, $user);
        app(ClientRecordService::class)->changeLifecycle(
            $client, 'inactive', 'Temporary absence from service', now(), $user, (int) $service->admin_id
        );
        $event = Frontend4ClientEvent::where('client_id', $client->id)->latest('id')->firstOrFail();

        $this->expectException(LogicException::class);
        $event->update(['reason' => 'rewritten']);
    }

    public function test_transfer_is_a_review_request_and_does_not_split_the_client_record(): void
    {
        [$user, $service] = $this->userAndService(true);
        $target = Home::whereIn('id', app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id))
            ->where('id', '!=', $service->id)->first();
        $client = $this->clientFor($service, $user);
        $originalService = (int) $client->home_id;

        $request = app(ClientRecordService::class)->requestTransfer(
            $client, (int) $target->id, 'Move requested by placement team', $user, (int) $service->admin_id
        );

        $this->assertSame('pending_review', $request->status);
        $this->assertSame($originalService, (int) $client->fresh()->home_id);
        $this->assertDatabaseHas('frontend4_client_transfer_requests', [
            'client_id' => $client->id,
            'from_service_id' => $originalService,
            'to_service_id' => $target->id,
            'status' => 'pending_review',
        ]);
    }

    public function test_inactive_clients_are_excluded_from_live_round_scope_but_remain_in_service_scope(): void
    {
        [$user, $service] = $this->userAndService();
        $client = $this->clientFor($service, $user);
        $client->lifecycle_status = 'inactive';
        $client->status = 0;
        $client->is_deleted = 0;
        $client->save();
        session($this->sessionFor($user, $service));

        $this->assertNotContains((int) $client->id, app(AccessContext::class)->allowedClientIds($user));
        $this->assertTrue(app(AccessContext::class)->scopeClients(ServiceUser::query(), $user)->whereKey($client->id)->exists());
    }

    private function userAndService(bool $multiple = false): array
    {
        $this->requireSchema();
        foreach (Frontend4User::where('status', 1)->where('is_deleted', 0)->get() as $user) {
            $services = Home::whereIn('id', collect(explode(',', (string) $user->real_home_id))->map(fn ($id) => (int) trim($id))->filter())
                ->where('is_deleted', 0)->get();
            foreach ($services->groupBy('admin_id') as $sameOrganisation) {
                $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $sameOrganisation->first()->admin_id);
                $actuallyAllowed = $sameOrganisation->whereIn('id', $allowed);
                if (! $multiple || $actuallyAllowed->count() > 1) {
                    foreach ($actuallyAllowed as $service) {
                        try {
                            $this->clientFor($service, $user);
                            return [$user, $service];
                        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                            // Try another assigned service from this organisation.
                        }
                    }
                }
            }
        }
        $this->markTestSkipped('The fixture database has no suitable Frontend 4 user, service and client.');
    }

    private function clientFor(Home $service, Frontend4User $user): ServiceUser
    {
        $query = ServiceUser::where('home_id', $service->id)
            ->whereIn('lifecycle_status', ['active', 'inactive']);
        $locations = app(AccessContext::class)->allowedLocationIds($user, (int) $service->admin_id, (int) $service->id);
        if ($locations !== null) {
            $query->whereIn('home_area_id', $locations);
        }

        return $query->firstOrFail();
    }

    private function sessionFor(Frontend4User $user, Home $service): array
    {
        $allowed = app(AccessContext::class)->allowedServiceIds($user, (int) $service->admin_id);
        return [
            'frontend4.organisation_id' => (int) $service->admin_id,
            'frontend4.active_service_id' => (int) $service->id,
            'frontend4.allowed_service_ids' => $allowed,
            'frontend4.active_location_id' => null,
            'frontend4.active_home_id' => (int) $service->id,
            'frontend4.allowed_home_ids' => $allowed,
            'frontend4.last_activity' => time(),
        ];
    }

    private function bindRole(string $role): void
    {
        $resolver = Mockery::mock(RoleResolver::class);
        $resolver->shouldReceive('resolve')->andReturn($role);
        $resolver->shouldReceive('label')->andReturn(RoleResolver::LABELS[$role]);
        $this->app->instance(RoleResolver::class, $resolver);
    }

    private function requireSchema(): void
    {
        foreach (['frontend4_client_events', 'frontend4_client_transfer_requests'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped('Run the Frontend 4 client lifecycle migration first.');
            }
        }
    }
}
