<?php

namespace App\Services\Staff;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function list(int $homeId, ?int $typeId = null, ?string $startDate = null, ?string $endDate = null, int $page = 1, int $perPage = 20): array
    {
        $query = DB::table('notification as n')
            ->leftJoin('notification_event_type as net', 'n.notification_event_type_id', '=', 'net.id')
            ->select('n.id', 'n.notification_event_type_id', 'n.event_action', 'n.message', 'n.is_sticky', 'n.sticky_master_ack', 'n.created_at', 'net.name as event_type_name')
            ->whereRaw('FIND_IN_SET(?, n.home_id)', [$homeId]);

        if ($typeId) {
            $query->where('n.notification_event_type_id', $typeId);
        }

        if ($startDate) {
            $query->whereDate('n.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('n.created_at', '<=', $endDate);
        }

        $total = $query->count();
        $offset = ($page - 1) * $perPage;

        $notifications = $query->orderBy('n.created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return [
            'notifications' => $notifications,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    public function markRead(int $id, int $homeId): bool
    {
        $notification = DB::table('notification')
            ->where('id', $id)
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->first();

        if (!$notification) {
            return false;
        }

        DB::table('notification')
            ->where('id', $id)
            ->update(['sticky_master_ack' => 1, 'updated_at' => now()]);

        Log::info('Notification marked read', ['notification_id' => $id, 'home_id' => $homeId]);
        return true;
    }

    public function markAllRead(int $homeId): int
    {
        $count = DB::table('notification')
            ->where(function ($q) {
                $q->whereNull('sticky_master_ack')->orWhere('sticky_master_ack', 0);
            })
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->update(['sticky_master_ack' => 1, 'updated_at' => now()]);

        Log::info('All notifications marked read', ['home_id' => $homeId, 'count' => $count]);
        return $count;
    }

    public function unreadCount(int $homeId): int
    {
        return DB::table('notification')
            ->where(function ($q) {
                $q->whereNull('sticky_master_ack')->orWhere('sticky_master_ack', 0);
            })
            ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
            ->count();
    }
}
