<?php

namespace App\Services\Frontend4;

use App\Home;
use App\Models\Frontend4User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Frontend 4's authoritative organisation, service and location boundary.
 *
 * Legacy `user.home_id` remains a compatibility source until explicit rows are
 * configured in frontend4_user_service_access. Session values are never an
 * authority: middleware recalculates and revalidates them on every request.
 */
class AccessContext
{
    public function allowedServiceIds(Frontend4User $user, int $organisationId): array
    {
        $explicit = $this->explicitServiceIds($user, $organisationId);
        $candidateIds = $explicit ?? $this->legacyServiceIds($user);

        if ($organisationId <= 0 || $candidateIds === []) {
            return [];
        }

        return Home::whereIn('id', $candidateIds)
            ->where('admin_id', $organisationId)
            ->where('is_deleted', 0)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** Null means no location restriction; an array is an explicit allow-list. */
    public function allowedLocationIds(Frontend4User $user, int $organisationId, int $serviceId): ?array
    {
        if (! Schema::hasTable('frontend4_user_location_access')) {
            return null;
        }

        $hasAssignments = DB::table('frontend4_user_location_access')
            ->where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->where('service_id', $serviceId)
            ->exists();

        if (! $hasAssignments) {
            return null;
        }

        return DB::table('frontend4_user_location_access as access')
            ->join('home_areas as location', 'location.id', '=', 'access.location_id')
            ->where('access.user_id', $user->id)
            ->where('access.organisation_id', $organisationId)
            ->where('access.service_id', $serviceId)
            ->where('access.active', 1)
            ->where('location.home_id', $serviceId)
            ->where('location.is_deleted', 0)
            ->pluck('location.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function validContext(
        Frontend4User $user,
        int $organisationId,
        int $serviceId,
        ?int $locationId = null
    ): bool {
        if (! in_array($serviceId, $this->allowedServiceIds($user, $organisationId), true)) {
            return false;
        }

        if ($locationId === null) {
            return true;
        }

        $locations = $this->allowedLocationIds($user, $organisationId, $serviceId);
        if ($locations !== null && ! in_array($locationId, $locations, true)) {
            return false;
        }

        return DB::table('home_areas')
            ->where('id', $locationId)
            ->where('home_id', $serviceId)
            ->where('is_deleted', 0)
            ->exists();
    }

    public function scopeClients(Builder $query, Frontend4User $user): Builder
    {
        $organisationId = $this->organisationId();
        $serviceId = $this->serviceId();
        $query->where('home_id', $serviceId);

        if (! Schema::hasColumn('service_user', 'home_area_id')) {
            return $query;
        }

        $activeLocation = $this->locationId();
        if ($activeLocation !== null) {
            return $query->where('home_area_id', $activeLocation);
        }

        $locations = $this->allowedLocationIds($user, $organisationId, $serviceId);

        return $locations === null ? $query : $query->whereIn('home_area_id', $locations);
    }

    public function allowedClientIds(Frontend4User $user): array
    {
        return $this->scopeClients(\App\ServiceUser::query(), $user)
            ->where('is_deleted', 0)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function putSession(
        Frontend4User $user,
        int $organisationId,
        int $serviceId,
        ?int $locationId = null
    ): void {
        $allowedServices = $this->allowedServiceIds($user, $organisationId);
        session([
            'frontend4.organisation_id' => $organisationId,
            'frontend4.active_service_id' => $serviceId,
            'frontend4.allowed_service_ids' => $allowedServices,
            'frontend4.active_location_id' => $locationId,
            // Compatibility aliases for the medication code shared with legacy UIs.
            'frontend4.active_home_id' => $serviceId,
            'frontend4.allowed_home_ids' => $allowedServices,
        ]);
    }

    public function organisationId(): int
    {
        return (int) session('frontend4.organisation_id', 0);
    }

    public function serviceId(): int
    {
        return (int) session('frontend4.active_service_id', session('frontend4.active_home_id', 0));
    }

    public function locationId(): ?int
    {
        $id = (int) session('frontend4.active_location_id', 0);

        return $id > 0 ? $id : null;
    }

    public function forgetSession(): void
    {
        session()->forget([
            'frontend4.organisation_id',
            'frontend4.active_service_id',
            'frontend4.allowed_service_ids',
            'frontend4.active_location_id',
            'frontend4.active_home_id',
            'frontend4.allowed_home_ids',
        ]);
    }

    public function props(Frontend4User $user): array
    {
        $organisationId = $this->organisationId();
        $serviceId = $this->serviceId();
        $serviceIds = $this->allowedServiceIds($user, $organisationId);
        $restrictedLocationIds = $this->allowedLocationIds($user, $organisationId, $serviceId);

        $locations = [];
        if (Schema::hasColumn('service_user', 'home_area_id')) {
            $locations = DB::table('home_areas')
                ->where('home_id', $serviceId)
                ->where('is_deleted', 0)
                ->when($restrictedLocationIds !== null, fn ($q) => $q->whereIn('id', $restrictedLocationIds))
                ->whereExists(function ($query) use ($serviceId) {
                    $query->selectRaw('1')
                        ->from('service_user')
                        ->whereColumn('service_user.home_area_id', 'home_areas.id')
                        ->where('service_user.home_id', $serviceId)
                        ->where('service_user.is_deleted', 0);
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => (int) $row->id, 'name' => $row->name])
                ->all();
        }

        return [
            'organisationId' => $organisationId,
            'organisation' => DB::table('admin')->where('id', $organisationId)->value('company'),
            'serviceId' => $serviceId,
            'services' => Home::whereIn('id', $serviceIds)->where('is_deleted', 0)
                ->orderBy('title')->get(['id', 'title'])
                ->map(fn ($home) => ['id' => (int) $home->id, 'name' => $home->title])
                ->all(),
            'locationId' => $this->locationId(),
            'locations' => $locations,
            'serviceSwitchUrl' => route('frontend4.context.service'),
            'locationSwitchUrl' => route('frontend4.context.location'),
        ];
    }

    private function explicitServiceIds(Frontend4User $user, int $organisationId): ?array
    {
        if (! Schema::hasTable('frontend4_user_service_access')) {
            return null;
        }

        $hasAssignments = DB::table('frontend4_user_service_access')
            ->where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->exists();

        if (! $hasAssignments) {
            return null;
        }

        return DB::table('frontend4_user_service_access')
            ->where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->where('active', 1)
            ->pluck('service_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function legacyServiceIds(Frontend4User $user): array
    {
        return collect(explode(',', (string) $user->real_home_id))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
