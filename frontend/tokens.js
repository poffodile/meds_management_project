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
// Official Care One OS brand colours — see docs/brand-guidelines.md.
export const brand = {
    primary: 'indigo',   // Mantine primaryColor
    navy: '#13233F',     // Support: Navy — header / sidebar / reverse-logo background
    teal: '#45C1BF',     // Core
    orange: '#F58321',   // Core
    purple: '#795076',   // Core
    green: '#88B13F',    // Core
    lightGrey: '#F4F6F8',
    midGrey: '#DDE4EA',
    textGrey: '#5F6B76',
};

// ---- Semantic status / label colours ----
// ONE place: add a status here and every <StatusBadge> across the app matches.
// Keys are lowercased status strings. Priority levels are namespaced
// (`priority_*`) so they don't collide with the stock-level `low`.
// Brand-aligned: brandGreen = good/given, brandOrange = due/low, brandTeal = info,
// red kept for danger, gray for neutral (see docs/brand-guidelines.md).
export const statusColors = {
    // stock / general state
    ok: 'brandGreen', active: 'brandGreen', inactive: 'gray',
    low: 'brandOrange', low_stock: 'brandOrange', 'low stock': 'brandOrange',
    expired: 'red', 'out of stock': 'red', out_of_stock: 'red',
    pending: 'yellow', draft: 'gray', submitted: 'brandTeal',
    acknowledged: 'brandGreen', resolved: 'brandGreen',
    // medication transaction types
    received: 'brandTeal', administered: 'brandGreen', given: 'brandGreen',
    disposed: 'brandOrange', returned: 'gray', correction: 'yellow', adjustment: 'yellow',
    // MAR / dose codes
    refused: 'red', omitted: 'brandOrange', withheld: 'brandOrange',
    sleeping: 'gray', 'not available': 'gray',
    missed: 'red', not_given: 'brandOrange',
    // round / dose states
    due: 'brandTeal', 'due soon': 'brandOrange', due_soon: 'brandOrange',
    overdue: 'red', completed: 'brandGreen',
    'not started': 'gray', not_started: 'gray', 'all given': 'brandGreen',
    // priority levels (namespaced — see note above)
    priority_low: 'gray', priority_medium: 'yellow', priority_high: 'brandOrange', priority_urgent: 'red',
};

// ---- Time-of-day medication rounds ----
// One brand colour per round (Care One OS core palette).
export const roundTokens = {
    morning: { label: 'Morning', icon: IconSun, color: 'brandOrange' },
    lunchtime: { label: 'Lunchtime', icon: IconCoffee, color: 'brandGreen' },
    evening: { label: 'Evening', icon: IconSunset, color: 'brandPurple' },
    night: { label: 'Night', icon: IconMoon, color: 'brandTeal' },
};

// ---- Avatar palette (deterministic initials avatars where there's no photo) ----
export const avatarColors = ['brandTeal', 'brandOrange', 'brandPurple', 'brandGreen', 'indigo', 'cyan', 'pink'];

// ---- Layout scale ----
export const radius = { card: 'lg', control: 'md' };
export const typography = {
    // Global app font. Loaded once in resources/views/app.blade.php; change it
    // here (one place) and every modern page updates.
    fontFamily: '"Plus Jakarta Sans", Inter, -apple-system, "Segoe UI", Roboto, sans-serif',
    headingWeight: '700',
};
