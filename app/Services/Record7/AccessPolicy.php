<?php

namespace App\Services\Record7;

use App\Models\Record7\Service;
use App\Models\Record7\User;
use App\Models\Record7\UserServiceAccess;

/**
 * Record7's single authorisation decision.
 *
 * Section 0.5. Every protected action asks exactly one question — may this
 * person do this thing, in this house, right now — and this class answers it.
 * Hiding a button is a courtesy; this is the check.
 *
 * FIVE LAYERS, RESOLVED IN THIS ORDER
 *
 *   1. Account state      suspended, invited, expired or locked stops
 *                         everything, whatever the role says.
 *   2. Service access     the person must hold usable access to that house —
 *                         status active AND inside the access date window.
 *   3. Access type        a read_only grant can never authorise a write, even
 *                         for a role whose matrix allows it.
 *   4. Permission rules   an explicit per-user deny beats everything below it;
 *                         an explicit allow beats the role matrix; otherwise
 *                         the role matrix decides.
 *   5. Competency         a permission gated by a competency is refused unless
 *                         that competency permits practice in that house.
 *
 * The order matters. Grace Taylor's agency account holds an active Support
 * Worker role whose matrix allows administering medication, an explicit deny
 * at Rosewood House, and an unassessed competency. Layers 4 and 5 each refuse
 * her independently, which is the intended belt and braces.
 */
class AccessPolicy
{
    /** Permissions that write to a record. A read-only grant never gets these. */
    private const WRITE_PERMISSIONS = [
        'administer_medication',
        'witness_medication',
        'manage_controlled_drugs',
        'stock_management',
        'reconciliation',
        'correction_approval',
        'reopen_medication_round',
        'manage_staff',
        'manage_organisation',
    ];

    /** A decision, with the reason attached for the audit trail. */
    public function decide(User $user, string $permission, ?int $serviceId = null): AccessDecision
    {
        if ($refusal = $user->accessRefusalReason()) {
            return AccessDecision::deny('account_'.$refusal, 'The account is not able to sign in.');
        }

        $access = null;

        if ($serviceId !== null) {
            $access = $this->usableAccess($user, $serviceId);

            if (! $access) {
                return AccessDecision::deny(
                    'no_service_access',
                    'This account does not currently have access to that house.'
                );
            }

            if ($access->isReadOnly() && in_array($permission, self::WRITE_PERMISSIONS, true)) {
                return AccessDecision::deny(
                    'read_only_access',
                    'This account has review access only and cannot record or change anything.'
                );
            }
        }

        $rule = $this->explicitRule($user, $permission, $serviceId);

        if ($rule === 'deny') {
            return AccessDecision::deny(
                'explicit_deny',
                'A specific rule on this account prevents this action.'
            );
        }

        if ($rule !== 'allow' && ! $this->roleAllows($user, $permission)) {
            return AccessDecision::deny(
                'role_does_not_allow',
                'This role does not include that action.'
            );
        }

        if ($blocked = $this->competencyBlock($user, $permission, $serviceId)) {
            return AccessDecision::deny('competency_'.$blocked, 'Competency for this action is not in place.');
        }

        return AccessDecision::allow();
    }

    public function allows(User $user, string $permission, ?int $serviceId = null): bool
    {
        return $this->decide($user, $permission, $serviceId)->allowed;
    }

    /* ── Service access ──────────────────────────────────────────────────── */

    /** The usable access row for one house, or null. */
    public function usableAccess(User $user, int $serviceId): ?UserServiceAccess
    {
        return $user->serviceAccess()
            ->where('service_id', $serviceId)
            ->get()
            ->first(fn (UserServiceAccess $row) => $row->isUsable() && $this->serviceIsActive($row));
    }

    /** Every house this person can work in right now, ordered by name. */
    public function availableServices(User $user): array
    {
        return $user->serviceAccess()->with('service')->get()
            ->filter(fn (UserServiceAccess $row) => $row->isUsable() && $this->serviceIsActive($row))
            ->map(fn (UserServiceAccess $row) => $row->service)
            ->filter()
            ->sortBy('name')
            ->values()
            ->all();
    }

    /** An inactive house is never selectable, however the grant reads. */
    private function serviceIsActive(UserServiceAccess $access): bool
    {
        $service = $access->relationLoaded('service') ? $access->service : Service::find($access->service_id);

        return $service !== null && $service->status === 'active';
    }

    /* ── Permission rules ────────────────────────────────────────────────── */

    /**
     * The strongest explicit rule for this permission: 'deny', 'allow', or null.
     *
     * A rule scoped to the house is considered alongside an organisation-wide
     * one, and deny always wins.
     */
    private function explicitRule(User $user, string $permission, ?int $serviceId): ?string
    {
        $rules = $user->permissionRules()->with('permission')->get()
            ->filter(fn ($rule) => $rule->isInForce()
                && $rule->permission?->code === $permission
                && ($rule->service_id === null || (int) $rule->service_id === (int) $serviceId));

        if ($rules->contains(fn ($rule) => $rule->effect === 'deny')) {
            return 'deny';
        }

        return $rules->contains(fn ($rule) => $rule->effect === 'allow') ? 'allow' : null;
    }

    private function roleAllows(User $user, string $permission): bool
    {
        foreach ($user->roleAssignments()->with('role.permissions')->get() as $assignment) {
            $role = $assignment->role;

            if ($role && $role->permissions->contains(fn ($p) => $p->code === $permission)) {
                return true;
            }
        }

        return false;
    }

    /* ── Competency ──────────────────────────────────────────────────────── */

    /**
     * Why competency blocks this permission, or null when it does not.
     *
     * A competency gate applies only to the permission it names. When a
     * competency type gates a permission and the person has no record at all
     * for it, that is treated as not assessed — the safe reading.
     */
    private function competencyBlock(User $user, string $permission, ?int $serviceId): ?string
    {
        $gates = \App\Models\Record7\CompetencyType::where('gates_permission', $permission)->get();

        if ($gates->isEmpty()) {
            return null;
        }

        $held = $user->competencies()->get();

        foreach ($gates as $gate) {
            $record = $held->first(fn ($c) => (int) $c->competency_type_id === (int) $gate->id
                && ($c->service_id === null || (int) $c->service_id === (int) $serviceId));

            if (! $record) {
                return 'not_assessed';
            }

            if (! $record->permitsPractice()) {
                return $record->status;
            }
        }

        return null;
    }

    /* ── For the interface ───────────────────────────────────────────────── */

    /**
     * Every permission code this person may use in this house.
     *
     * Display only — the React side hides what it should hide, and every
     * request is checked again on the server regardless.
     */
    public function grantedPermissions(User $user, ?int $serviceId = null): array
    {
        return \App\Models\Record7\Permission::orderBy('code')->pluck('code')
            ->filter(fn (string $code) => $this->allows($user, $code, $serviceId))
            ->values()
            ->all();
    }
}
