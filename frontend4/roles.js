/**
 * frontend4 roles and permissions — the display side.
 *
 * READ THIS BEFORE USING IT
 * Nothing in this file is a security check. The server decides, in
 * app/Services/Frontend4/Permissions.php, and every action that writes anything
 * is refused there if the role does not hold the permission. What this file
 * does is let the interface avoid showing someone a button that would be
 * refused — which is courtesy, not protection.
 *
 * If you ever find yourself relying on a hidden control to keep something safe,
 * the check is missing on the server.
 *
 * The `role` and `can` props come from F4Controller::roleProps(), merged into
 * every frontend4 Inertia response.
 */

/** The four roles, least to most privileged. Mirrors RoleResolver. */
export const ROLES = {
    CARER:   'carer',
    LEAD:    'lead',
    MANAGER: 'manager',
    ADMIN:   'admin',
    NONE:    'none',
};

export const ROLE_LABELS = {
    carer:   'Support worker',
    lead:    'Shift lead',
    manager: 'Manager',
    admin:   'Administrator',
    none:    'No medication access',
};

/** Permission names. Mirrors the constants in Permissions.php. */
export const P = {
    VIEW_TODAY:               'view_today',
    VIEW_ROUND:               'view_round',
    VIEW_CLIENTS:             'view_clients',
    VIEW_MAR:                 'view_mar',
    RECORD_ADMINISTRATION:    'record_administration',
    WITNESS_CONTROLLED_DRUG:  'witness_controlled_drug',
    CORRECT_RECORD:           'correct_record',
    REOPEN_ROUND:             'reopen_round',
    VIEW_STOCK:               'view_stock',
    RECEIVE_DELIVERY:         'receive_delivery',
    APPROVE_STOCK_ADJUSTMENT: 'approve_stock_adjustment',
    VIEW_CD_REGISTER:         'view_cd_register',
    MANAGE_PRESCRIPTION:      'manage_prescription',
    VIEW_REPORTS:             'view_reports',
    EXPORT_REPORT:            'export_report',
    MANAGE_STAFF:             'manage_staff',
    DEFINE_ROLES:             'define_roles',
    MANAGE_SETTINGS:          'manage_settings',
};

/**
 * Does this user hold the permission?
 *
 * @param {string[]} can  the `can` array from the page props
 */
export function allows(can, permission) {
    return Array.isArray(can) && can.includes(permission);
}

/**
 * Which sidebar items a role sees.
 *
 * A support worker sees only implemented workspaces they may use, and
 * everything else is added by permission rather than shown to everybody.
 * `available: false` keeps planned pages out until their server route exists.
 */
export const NAV = [
    { key: 'today',      label: 'Today',            href: '/frontend4',                permission: P.VIEW_TODAY,   available: true },
    { key: 'round',      label: 'Medication round', href: '/frontend4/round',          permission: P.VIEW_ROUND,   available: true },
    { key: 'missed',     label: 'Missed doses',     href: '/frontend4/missed-doses',   permission: P.VIEW_ROUND,   available: false },
    { key: 'clients',    label: 'Clients',          href: '/frontend4/clients',        permission: P.VIEW_CLIENTS, available: true },
    { key: 'handover',   label: 'Handover',         href: '/frontend4/handover',       permission: P.VIEW_TODAY,   available: false },

    // ── Added for a shift lead and above ──────────────────────────────────
    { key: 'cd',         label: 'Controlled drugs', href: '/frontend4/controlled-drugs', permission: P.VIEW_CD_REGISTER, group: 'Oversight', available: false },
    { key: 'stock',      label: 'Stock',            href: '/frontend4/stock',            permission: P.RECEIVE_DELIVERY, group: 'Oversight', available: false },

    // ── Manager and above ─────────────────────────────────────────────────
    { key: 'reports',    label: 'Reports & audit',  href: '/frontend4/reports',          permission: P.VIEW_REPORTS,     group: 'Oversight', available: false },

    // ── Administrator ─────────────────────────────────────────────────────
    { key: 'admin',      label: 'Administration',   href: '/frontend4/admin',            permission: P.MANAGE_SETTINGS,  group: 'Control plane', available: false },
];

/**
 * The navigation for one user, grouped for display.
 *
 * Returns [{ group: string|null, items: [...] }] — a group of `null` is the
 * unlabelled first block.
 *
 * Note `VIEW_STOCK` is NOT what gates the Stock item. A carer holds VIEW_STOCK
 * because they read "stock remaining" on a medicine card; that is not the same
 * as having the stock workspace in their sidebar. RECEIVE_DELIVERY is the
 * permission that means "this is your workspace".
 */
export function navFor(can) {
    const visible = NAV.filter((item) => item.available && allows(can, item.permission));

    const groups = [];
    visible.forEach((item) => {
        const name = item.group || null;
        const last = groups[groups.length - 1];
        if (last && last.group === name) {
            last.items.push(item);
        } else {
            groups.push({ group: name, items: [item] });
        }
    });

    return groups;
}
