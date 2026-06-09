import { IconSun, IconCoffee, IconSunset, IconMoon } from '@tabler/icons-react';

/**
 * Design tokens — the SINGLE source of truth for the app's visual language.
 * Brand colours, the semantic status/label palette, time-of-day round metadata,
 * the avatar palette, and layout scale (radius / typography).
 *
 * Consumed by `frontend/theme.js` (Mantine `createTheme`) AND directly by
 * components (e.g. StatusBadge reads `statusColors`). Change a value here and
 * the whole app updates. White-label ready: a per-tenant theme can later
 * override `brand` without touching component code.
 */

// ---- Brand ----
export const brand = {
    primary: 'indigo',   // Mantine primaryColor
    navy: '#16223a',     // dark sidebar logo band (was hardcoded in AppShell)
};

// ---- Semantic status / label colours ----
// ONE place: add a status here and every <StatusBadge> across the app matches.
// Keys are lowercased status strings. Priority levels are namespaced
// (`priority_*`) so they don't collide with the stock-level `low`.
export const statusColors = {
    // stock / general state
    ok: 'green', active: 'green', inactive: 'gray',
    low: 'orange', low_stock: 'orange', 'low stock': 'orange',
    expired: 'red', 'out of stock': 'red', out_of_stock: 'red',
    pending: 'yellow', draft: 'gray', submitted: 'blue',
    acknowledged: 'green', resolved: 'green',
    // medication transaction types
    received: 'blue', administered: 'green', given: 'green',
    disposed: 'orange', returned: 'gray', correction: 'yellow', adjustment: 'yellow',
    // MAR / dose codes
    refused: 'red', omitted: 'orange', withheld: 'orange',
    sleeping: 'gray', 'not available': 'gray',
    missed: 'red', not_given: 'orange',
    // round / dose states
    due: 'blue', 'due soon': 'orange', due_soon: 'orange',
    overdue: 'red', completed: 'green',
    'not started': 'gray', not_started: 'gray', 'all given': 'green',
    // priority levels (namespaced — see note above)
    priority_low: 'gray', priority_medium: 'yellow', priority_high: 'orange', priority_urgent: 'red',
};

// ---- Time-of-day medication rounds ----
export const roundTokens = {
    morning: { label: 'Morning', icon: IconSun, color: 'orange' },
    lunchtime: { label: 'Lunchtime', icon: IconCoffee, color: 'yellow' },
    evening: { label: 'Evening', icon: IconSunset, color: 'grape' },
    night: { label: 'Night', icon: IconMoon, color: 'indigo' },
};

// ---- Avatar palette (deterministic initials avatars where there's no photo) ----
export const avatarColors = ['indigo', 'teal', 'grape', 'cyan', 'orange', 'pink', 'blue'];

// ---- Layout scale ----
export const radius = { card: 'lg', control: 'md' };
export const typography = {
    fontFamily: 'Inter, -apple-system, "Segoe UI", Roboto, sans-serif',
    headingWeight: '700',
};
