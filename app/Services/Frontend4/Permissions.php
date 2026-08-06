<?php

namespace App\Services\Frontend4;

/**
 * What each frontend4 role may do.
 *
 * THE RULE THIS FILE EXISTS TO ENFORCE
 * Hiding a button is a courtesy. The check is the feature. Every action listed
 * here is verified on the server before anything is written, and the React side
 * only ever uses the same list to decide what to *show*.
 *
 * Confirmed with the product owner 2026-08-04. Separate from
 * {@see RoleResolver} on purpose: that class answers "who is this person", this
 * one answers "what may they do". Either can be corrected without disturbing
 * the other.
 */
class Permissions
{
    // ── Clinical ──────────────────────────────────────────────────────────
    public const RECORD_ADMINISTRATION = 'record_administration';
    public const WITNESS_CONTROLLED_DRUG = 'witness_controlled_drug';
    public const CORRECT_RECORD = 'correct_record';
    public const REOPEN_ROUND = 'reopen_round';

    // ── Supply ────────────────────────────────────────────────────────────
    public const VIEW_STOCK = 'view_stock';
    public const RECEIVE_DELIVERY = 'receive_delivery';
    public const APPROVE_STOCK_ADJUSTMENT = 'approve_stock_adjustment';
    public const VIEW_CD_REGISTER = 'view_cd_register';

    // ── Clinical record management ────────────────────────────────────────
    public const MANAGE_PRESCRIPTION = 'manage_prescription'; // pause/stop/change a prescription

    // ── Oversight ─────────────────────────────────────────────────────────
    public const VIEW_REPORTS = 'view_reports';
    public const EXPORT_REPORT = 'export_report';

    // ── Control plane ─────────────────────────────────────────────────────
    public const MANAGE_STAFF = 'manage_staff';   // within their own homes
    public const DEFINE_ROLES = 'define_roles';   // what a role is allowed to do
    public const MANAGE_SETTINGS = 'manage_settings';

    /**
     * Role → what it may do. Each role INHERITS everything below it, which is
     * applied in allows() rather than repeated here.
     *
     * Note what is absent from every single row: there is no permission to
     * overwrite or delete a clinical record, because nobody has one. A
     * correction is a new linked record — see CORRECT_RECORD, and
     * `mar_administrations.supersedes_id` in the schema.
     */
    private const GRANTS = [
        RoleResolver::CARER => [
            self::RECORD_ADMINISTRATION,
            self::VIEW_STOCK,   // reads "stock remaining" on a medicine; cannot change it
        ],
        RoleResolver::LEAD => [
            self::WITNESS_CONTROLLED_DRUG,
            self::CORRECT_RECORD,
            self::REOPEN_ROUND,
            self::RECEIVE_DELIVERY,
            self::VIEW_CD_REGISTER,
        ],
        RoleResolver::MANAGER => [
            self::APPROVE_STOCK_ADJUSTMENT,
            self::VIEW_REPORTS,
            self::EXPORT_REPORT,
            self::MANAGE_STAFF,
            // Manager-and-above may change a prescription (owner decision
            // 2026-08-05). Pause/stop/change is a clinical-record edit, so it is
            // written through an append-only change-log — see mar_sheet_changes.
            self::MANAGE_PRESCRIPTION,
        ],
        RoleResolver::ADMIN => [
            self::DEFINE_ROLES,
            self::MANAGE_SETTINGS,
        ],
    ];

    /**
     * Permissions an administrator does NOT inherit.
     *
     * An administrator manages access; they do not administer medicines. Both
     * specifications say this, and it is the separation that stops "I'll just
     * give myself the rights" being a route into the clinical record.
     *
     * DEFINE_ROLES is the reason it matters: someone who can rewrite what a
     * role may do must not also be able to act clinically, or the permission
     * model protects nothing.
     */
    private const ADMIN_EXCLUDES = [
        self::RECORD_ADMINISTRATION,
        self::WITNESS_CONTROLLED_DRUG,
        self::CORRECT_RECORD,
        // Changing a prescription is a clinical-record edit; an administrator
        // manages access, not the clinical record. So admin does NOT inherit it,
        // even though it sits at the manager tier they otherwise inherit.
        self::MANAGE_PRESCRIPTION,
    ];

    /** Least-to-most privileged, for inheritance. */
    private const LADDER = [
        RoleResolver::CARER,
        RoleResolver::LEAD,
        RoleResolver::MANAGER,
        RoleResolver::ADMIN,
    ];

    /** May this role perform this action? */
    public function allows(string $role, string $permission): bool
    {
        return in_array($permission, $this->forRole($role), true);
    }

    /**
     * Everything a role may do, inheritance applied.
     *
     * Returned to the React side so the interface can hide what it should hide.
     * It is a display aid — never the check.
     */
    public function forRole(string $role): array
    {
        if ($role === RoleResolver::NONE) {
            return [];
        }

        $index = array_search($role, self::LADDER, true);
        if ($index === false) {
            return [];
        }

        $granted = [];
        foreach (array_slice(self::LADDER, 0, $index + 1) as $rung) {
            $granted = array_merge($granted, self::GRANTS[$rung] ?? []);
        }

        if ($role === RoleResolver::ADMIN) {
            $granted = array_values(array_diff($granted, self::ADMIN_EXCLUDES));
        }

        return array_values(array_unique($granted));
    }
}
