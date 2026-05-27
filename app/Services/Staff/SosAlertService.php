<?php

namespace App\Services\Staff;

use App\Models\staffManagement\sosAlert;
use App\Notification;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SosAlertService
{
    public function trigger(int $staffId, int $homeId, ?string $message): sosAlert
    {
        // Step 1: Save the SOS alert record — commit it immediately
        DB::beginTransaction();
        try {
            $alert = sosAlert::create([
                'staff_id' => $staffId,
                'home_id'  => $homeId,
                'location' => 'Web Dashboard',
                'message'  => $message,
                'status'   => 1,
            ]);
            DB::commit();
            Log::info('SOS Alert triggered', ['alert_id' => $alert->id, 'staff_id' => $staffId, 'home_id' => $homeId]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SOS Alert DB save failed: ' . $e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine()
            ]);
            throw $e;
        }

        // Step 2: Send notifications — in a separate try so schema differences
        // on the server don't block the SOS alert from being saved.
        try {
            $staffName = User::where('id', $staffId)->value('name') ?? 'Unknown';

            $managers = User::whereIn('user_type', ['M', 'A'])
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->whereRaw('FIND_IN_SET(?, home_id)', [$homeId])
                ->get();

            foreach ($managers as $manager) {
                try {
                    // Build notification using raw DB insert for maximum compatibility
                    // across different server notification table schemas
                    $notifData = [
                        'home_id'                    => $homeId,
                        'user_id'                    => $manager->id,
                        'event_id'                   => $alert->id,
                        'notification_event_type_id' => 24,
                        'event_action'               => 'SOS_ALERT',
                        'message'                    => $staffName . ' needs help!',
                        'is_sticky'                  => 1,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ];

                    // Add service_user_id only if column exists (handles schema differences)
                    $columns = DB::getSchemaBuilder()->getColumnListing('notification');
                    if (in_array('service_user_id', $columns)) {
                        $notifData['service_user_id'] = 0;
                    }

                    DB::table('notification')->insert($notifData);
                } catch (\Exception $notifEx) {
                    Log::warning('SOS: Could not notify manager #' . $manager->id . ': ' . $notifEx->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('SOS Notification dispatch failed: ' . $e->getMessage(), [
                'alert_id' => $alert->id, 'file' => $e->getFile(), 'line' => $e->getLine()
            ]);
            // Do NOT rethrow — the SOS alert was already saved successfully
        }

        return $alert;
    }

    public function list(int $homeId, int $limit = 10)
    {
        return sosAlert::with(['staff:id,name', 'acknowledgedByUser:id,name', 'resolvedByUser:id,name'])
            ->forHome($homeId)
            ->active()
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function acknowledge(int $id, int $homeId, int $userId): ?sosAlert
    {
        $alert = sosAlert::forHome($homeId)->active()->where('id', $id)->first();
        if (!$alert) {
            return null;
        }
        if ($alert->status !== 1) {
            return null;
        }

        $alert->status = 2;
        $alert->acknowledged_by = $userId;
        $alert->acknowledged_at = now();
        $alert->save();

        Log::info('SOS Alert acknowledged', ['alert_id' => $id, 'user_id' => $userId, 'home_id' => $homeId]);
        return $alert;
    }

    public function resolve(int $id, int $homeId, int $userId, ?string $notes = null): ?sosAlert
    {
        $alert = sosAlert::forHome($homeId)->active()->where('id', $id)->first();
        if (!$alert) {
            return null;
        }
        if (!in_array($alert->status, [1, 2])) {
            return null;
        }

        $alert->status = 3;
        $alert->resolved_by = $userId;
        $alert->resolved_at = now();
        if ($notes) {
            $alert->message = ($alert->message ? $alert->message . "\n\nResolution: " : 'Resolution: ') . $notes;
        }
        $alert->save();

        Log::info('SOS Alert resolved', ['alert_id' => $id, 'user_id' => $userId, 'home_id' => $homeId]);
        return $alert;
    }
}
