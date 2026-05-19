<?php

namespace App\Services\Portal;

use App\Models\ClientPortalAccess;
use App\Models\ScheduledShift;
use App\ServiceUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\Portal\PortalMessageService;

class ClientPortalService
{
    public function getDashboardData(ClientPortalAccess $access): array
    {
        $client = ServiceUser::where('id', $access->client_id)
            ->where('home_id', $access->home_id)
            ->first();

        return [
            'portal_access' => $access,
            'client' => $client,
            'stats' => [
                'upcoming_schedule' => $access->can_view_schedule
                    ? $this->getUpcomingScheduleCount($access)
                    : 0,
                'unread_messages' => $access->can_send_messages
                    ? app(PortalMessageService::class)->getUnreadCount($access)
                    : 0,
                'pending_requests' => 0,
                'notifications' => 0,
            ],
        ];
    }

    public function getScheduleData(ClientPortalAccess $access, ?string $weekStart = null): array
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
        if ($weekStart) {
            try {
                $start = Carbon::parse($weekStart)->startOfWeek(Carbon::MONDAY);
            } catch (\Exception $e) {
                $start = Carbon::now()->startOfWeek(Carbon::MONDAY);
            }
        }
        $end = $start->copy()->endOfWeek(Carbon::SUNDAY);

        $shifts = ScheduledShift::where('home_id', (string) $access->home_id)
            ->where('service_user_id', $access->client_id)
            ->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['cancelled'])
            ->with('staff:id,name')
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $shifts->each(function ($shift) {
            if ($shift->staff) {
                $shift->staff->name = explode(' ', $shift->staff->name)[0];
            }
        });

        return [
            'shifts' => $shifts,
            'week_start' => $start,
            'week_end' => $end,
            'week_days' => collect(range(0, 6))->map(fn($i) => $start->copy()->addDays($i)),
        ];
    }

    public function getUpcomingScheduleCount(ClientPortalAccess $access): int
    {
        return ScheduledShift::where('home_id', (string) $access->home_id)
            ->where('service_user_id', $access->client_id)
            ->where('start_date', '>=', Carbon::today()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();
    }

    public function listPortalUsers(int $homeId, ?int $clientId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = ClientPortalAccess::active()
            ->forHome($homeId)
            ->with('client');

        if ($clientId) {
            $query->forClient($clientId);
        }

        return $query->orderBy('full_name')->get();
    }

    public function createPortalAccess(array $data, int $homeId, int $createdBy): ClientPortalAccess
    {
        $access = ClientPortalAccess::create(array_merge($data, [
            'home_id' => $homeId,
            'created_by' => $createdBy,
            'is_active' => 1,
            'is_deleted' => 0,
            'activation_date' => now()->toDateString(),
        ]));

        Log::info('Portal access created', [
            'id' => $access->id,
            'client_id' => $access->client_id,
            'user_email' => $access->user_email,
            'home_id' => $homeId,
            'created_by' => $createdBy,
        ]);

        return $access;
    }

    public function revokePortalAccess(int $id, int $homeId): bool
    {
        $access = ClientPortalAccess::where('id', $id)
            ->forHome($homeId)
            ->where('is_deleted', 0)
            ->first();

        if (!$access) {
            return false;
        }

        $access->update(['is_active' => 0]);

        Log::info('Portal access revoked', [
            'id' => $id,
            'home_id' => $homeId,
        ]);

        return true;
    }

    public function deletePortalAccess(int $id, int $homeId): bool
    {
        $access = ClientPortalAccess::where('id', $id)
            ->forHome($homeId)
            ->first();

        if (!$access) {
            return false;
        }

        $access->update(['is_deleted' => 1, 'is_active' => 0]);

        Log::info('Portal access deleted', [
            'id' => $id,
            'home_id' => $homeId,
        ]);

        return true;
    }
}
