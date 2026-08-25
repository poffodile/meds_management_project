<?php

namespace App\Services\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\LoginSession;
use App\Models\Record7\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Writes Record7's access audit.
 *
 * Section 0.10. Three properties the audit must have, and how each is kept:
 *
 *   Append-only. The table carries BEFORE UPDATE and BEFORE DELETE triggers
 *   that raise at the database, and AccessAuditEvent refuses both in PHP. A
 *   removal is not merely discouraged; it fails.
 *
 *   Tamper-evident. Each event stores the uuid of the event before it, so a
 *   row deleted by some route that bypassed both guards leaves a visible break
 *   in the chain rather than a silent hole.
 *
 *   Readable later. Staff name and role name are snapshotted at the time of
 *   the event, so an audit read next year still says who did what, even after
 *   the person has been renamed or moved role.
 */
class AuditRecorder
{
    /** Events that describe a refusal — recorded even when nobody is signed in. */
    public const DENIED = 'denied';

    public const SUCCESS = 'success';

    public const FAILURE = 'failure';

    public const INFORMATION = 'information';

    public const WARNING = 'warning';

    public function record(
        string $eventType,
        string $result,
        ?User $user = null,
        ?int $organisationId = null,
        ?int $serviceId = null,
        ?string $reason = null,
        string $riskLevel = 'none',
        array $metadata = [],
        ?Request $request = null,
        ?LoginSession $session = null
    ): AccessAuditEvent {
        $request ??= request();

        $roleName = null;
        if ($user) {
            $roleName = $user->primaryRole()?->name;
        }

        return AccessAuditEvent::create([
            'event_uuid' => (string) Str::uuid(),
            'organisation_id' => $organisationId ?? $user?->organisation_id,
            'service_id' => $serviceId,
            'user_id' => $user?->id,
            'staff_name_at_time' => $user?->full_name,
            'role_name_at_time' => $roleName,
            'event_type' => $eventType,
            'event_result' => $result,
            'event_time' => now(),
            'session_reference' => $session?->reference,
            'device_reference' => $this->deviceReference($request),
            'reason' => $reason,
            'risk_level' => $riskLevel,
            'previous_event_uuid' => $this->previousUuid(),
            'metadata' => $metadata ?: null,
        ]);
    }

    /** The most recent event's uuid, so the chain links backwards. */
    private function previousUuid(): ?string
    {
        return AccessAuditEvent::query()->orderByDesc('id')->value('event_uuid');
    }

    /**
     * A stable, non-identifying device label.
     *
     * The full user agent is not stored: it is unnecessary for an access audit
     * and adds a fingerprinting surface. A short hash is enough to tell
     * "the same device again" from "somewhere new".
     */
    private function deviceReference(?Request $request): ?string
    {
        $agent = $request?->userAgent();

        return $agent ? 'device-'.substr(hash('sha256', $agent), 0, 12) : null;
    }

    /**
     * Verify the chain from oldest to newest.
     *
     * Returns the ids of events whose previous_event_uuid does not match the
     * event actually before them. An empty array means the audit is intact.
     */
    public function brokenLinks(): array
    {
        $broken = [];
        $expected = null;

        foreach (AccessAuditEvent::orderBy('id')->get(['id', 'event_uuid', 'previous_event_uuid']) as $event) {
            if ($expected !== null && $event->previous_event_uuid !== $expected) {
                $broken[] = $event->id;
            }
            $expected = $event->event_uuid;
        }

        return $broken;
    }
}
