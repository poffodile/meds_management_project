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

// ---- Unified UI palette (theme-aware) ----
// The premium, high-contrast light/dark system used by the modern pages.
// Values are `light-dark(light, dark)` strings so a single token renders
// correctly in both colour schemes. Full reference: docs/COLOUR-PALETTE.md.
export const palette = {
    ink: 'light-dark(#16233B, #EAEDF2)',     // primary text / headings
    ink2: 'light-dark(#5E6878, #97A2B3)',    // secondary text / body
    faint: 'light-dark(#939DAD, #6C7688)',   // captions / units / disabled
    line: 'light-dark(#ECEEF2, #2C2E33)',    // hairline borders / dividers
    cardBg: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))',
    // Table surface — soft blue wash in LIGHT mode, subtle deep-navy gradient in DARK
    // (echoes the light wash instead of going flat near-black).
    tableBg: 'linear-gradient(125deg, light-dark(#FFFFFF, #131E33) 0%, light-dark(#ECF0F7, #1A2842) 30%, light-dark(#DCE4F0, #223454) 50%, light-dark(#ECF0F7, #1A2842) 70%, light-dark(#FFFFFF, #131E33) 100%)',
    // Brand accents (normalised light → dark).
    navy: 'light-dark(#13233F, #1A2B4C)',
    teal: 'light-dark(#208280, #45C1BF)',
    orange: 'light-dark(#D96A14, #F58321)',
    purple: 'light-dark(#6F446C, #9B6898)',
    primaryBtn: 'light-dark(#13233F, #45C1BF)',  // primary CTA (navy in light, teal in dark)
    // Neutral icon tile.
    tileBg: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))',
    tileBorder: 'light-dark(#EBEEF3, rgba(255,255,255,0.07))',
    controlledIcon: 'light-dark(#6F446C, #CBBCE4)',  // CD / controlled
    normalIcon: 'light-dark(#4E6B9A, #93ACD6)',      // non-controlled
    rowHover: 'light-dark(#F8FAFC, var(--mantine-color-dark-5))',
    progressTrack: 'light-dark(#F0F2F6, var(--mantine-color-dark-5))',
};

// Unified 5-state stock status — text/dot + chip bg, dark-mode aware.
export const statusPalette = {
    healthy:  { label: 'In stock',      c: 'light-dark(#2F7D5B, #A2E0C1)', dot: 'light-dark(#2F7D5B, #A2E0C1)', bg: 'light-dark(#EAF4EF, #1E2B24)' },
    expiring: { label: 'Expiring soon', c: 'light-dark(#6E5A94, #CBBCE4)', dot: 'light-dark(#6E5A94, #CBBCE4)', bg: 'light-dark(#F0ECF8, #221C2B)' },
    low:      { label: 'Low stock',     c: 'light-dark(#9C6B22, #E6C594)', dot: 'light-dark(#9C6B22, #E6C594)', bg: 'light-dark(#FBF2E4, #2D2314)' },
    out:      { label: 'Out of stock',  c: 'light-dark(#A24A41, #F1B2AC)', dot: 'light-dark(#A24A41, #F1B2AC)', bg: 'light-dark(#F8ECEA, #2E1917)' },
    expired:  { label: 'Expired',       c: 'light-dark(#9A4560, #F5B8CC)', dot: 'light-dark(#9A4560, #F5B8CC)', bg: 'light-dark(#F6EAEF, #2B171D)' },
};

// ---- Layout scale ----
export const radius = { card: 'lg', control: 'md' };
export const typography = {
    // Global app font. Loaded once in resources/views/app.blade.php; change it
    // here (one place) and every modern page updates.
    fontFamily: '"Plus Jakarta Sans", Inter, -apple-system, "Segoe UI", Roboto, sans-serif',
    headingWeight: '700',
};
