<?php

namespace App\Http\Controllers\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Models\Record7\Service;
use App\Services\Record7\AccessPolicy;
use App\Services\Record7\AuditRecorder;
use App\Services\Record7\SessionManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * The manager's access-audit screen.
 *
 * Section 0.10. Reaching it requires view_access_audit, which the role matrix
 * gives to Service Managers, Organisation Administrators, Medication Leads and
 * the Quality and Compliance Reviewer — and to nobody else. A support worker
 * asking for this URL directly is refused by middleware before this controller
 * runs, and that refusal is itself audited.
 *
 * Reading the audit is recorded too. Someone looking at who accessed what is
 * an access event like any other.
 */
class AuditController extends R7Controller
{
    private const PER_PAGE = 60;

    public function __construct(
        private readonly AccessPolicy $policy,
        private readonly SessionManager $sessions,
        private readonly AuditRecorder $audit
    ) {
    }

    public function index(Request $request)
    {
        $this->useR7Layout($request);

        $user = $this->user();
        abort_unless($user !== null, 403);

        $organisationId = $this->sessions->organisationId($request);
        $currentHouse = $this->sessions->serviceId($request);

        // Scope: only the houses this person can actually reach. A manager of
        // two houses must not read a third house's access record.
        $visibleHouseIds = array_map(
            fn (Service $house) => $house->id,
            $this->policy->availableServices($user)
        );

        $filters = $request->validate([
            'house' => ['nullable', 'integer'],
            'result' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', 'string', 'max:64'],
        ]);

        $query = AccessAuditEvent::query()
            ->where('organisation_id', $organisationId)
            ->where(function ($q) use ($visibleHouseIds) {
                // Organisation-level events (a refused sign-in has no house yet)
                // stay visible, because they are the ones that matter most.
                $q->whereNull('service_id')->orWhereIn('service_id', $visibleHouseIds);
            });

        if (! empty($filters['house'])) {
            abort_unless(in_array((int) $filters['house'], $visibleHouseIds, true), 403);
            $query->where('service_id', $filters['house']);
        }

        if (! empty($filters['result'])) {
            $query->where('event_result', $filters['result']);
        }

        if (! empty($filters['type'])) {
            $query->where('event_type', $filters['type']);
        }

        $events = $query->orderByDesc('id')->limit(self::PER_PAGE)->get();

        $this->audit->record(
            'access_audit_viewed',
            AuditRecorder::INFORMATION,
            $user,
            $organisationId,
            $currentHouse,
            null,
            'none',
            array_filter($filters),
            $request,
            $this->sessions->current($request)
        );

        return Inertia::render('Audit', [
            'events' => $events->map(fn (AccessAuditEvent $event) => [
                'id' => $event->id,
                'time' => $event->event_time?->format('j M Y, H:i'),
                'type' => $event->event_type,
                'typeLabel' => $this->label($event->event_type),
                'result' => $event->event_result,
                'staff' => $event->staff_name_at_time,
                'role' => $event->role_name_at_time,
                'house' => $event->service_id ? Service::find($event->service_id)?->name : null,
                'reason' => $event->reason,
                'risk' => $event->risk_level,
                'device' => $event->device_reference,
            ])->values()->all(),
            'houses' => array_map(
                fn (Service $house) => ['id' => $house->id, 'name' => $house->name],
                $this->policy->availableServices($user)
            ),

            // Whether Manager Today is offered in the menu from here. Read of
            // the same permission that screen's own route enforces, against
            // the house the session is in.
            'can' => [
                'viewManager' => $currentHouse !== null
                    && $this->policy->allows($user, 'view_manager_dashboard', $currentHouse),
            ],
            'filters' => $filters,
            'total' => $query->count(),
            'shown' => $events->count(),
            // Append-only is a promise; this is the evidence for it.
            'integrity' => [
                'appendOnly' => true,
                'brokenLinks' => count($this->audit->brokenLinks()),
            ],
            'urls' => [
                'self' => route('record7.audit'),
                'manager' => route('record7.manager'),
                'today' => route('record7.today'),
                'houses' => route('record7.houses'),
                'lock' => route('record7.lock.now'),
                'signOut' => route('record7.signout'),
            ],
        ]);
    }

    /** Event types in words a manager reads, not system names. */
    private function label(string $type): string
    {
        return match ($type) {
            'sign_in' => 'Sign in',
            'sign_out' => 'Sign out',
            'password_verified' => 'Password accepted',
            'verification' => 'Security verification',
            'house_selected' => 'House opened',
            'house_switched' => 'House switched',
            'session_locked' => 'Screen locked',
            'session_unlocked' => 'Screen unlocked',
            'session_revoked' => 'Access withdrawn',
            'permission_denied' => 'Action refused',
            'organisation_not_recognised' => 'Unrecognised organisation',
            'no_active_house' => 'No active house',
            'activation' => 'Account activation',
            'password_reset_requested' => 'Password reset requested',
            'password_reset_completed' => 'Password reset completed',
            'access_audit_viewed' => 'Access audit viewed',
            'unlock' => 'Unlock attempt',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
