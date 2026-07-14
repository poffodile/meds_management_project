import { useSyncExternalStore } from 'react';

/* Site-wide font preference — SEPARATE choices for headings and body.
   Headings  → --mantine-font-family-headings   (Mantine Titles + any element
               that opts in with fontFamily: 'var(--mantine-font-family-headings)')
   Body      → --mantine-font-family             (everything else)
   Both are set inline on <html> (beats Mantine's stylesheet vars), persisted in
   localStorage, and applied pre-paint by a script in app.blade.php (no flash).
   Families are loaded in app.blade.php (Google Fonts + Fontshare for Satoshi). */

const BODY_KEY = 'careone-font-body';
const HEAD_KEY = 'careone-font-headings';
const DEFAULT = 'inter';
const FALLBACK = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

export const FONTS = [
    { value: 'inter',      label: 'Inter — recommended',         stack: `"Inter", ${FALLBACK}` },
    { value: 'satoshi',    label: 'Satoshi',                     stack: `"Satoshi", ${FALLBACK}` },
    { value: 'manrope',    label: 'Manrope',                     stack: `"Manrope", ${FALLBACK}` },
    { value: 'jakarta',    label: 'Plus Jakarta Sans — current', stack: `"Plus Jakarta Sans", ${FALLBACK}` },
    { value: 'system',     label: 'System — SF Pro / Segoe UI',  stack: FALLBACK },
    { value: 'geist',      label: 'Geist',                       stack: `"Geist", ${FALLBACK}` },
    { value: 'dmsans',     label: 'DM Sans',                     stack: `"DM Sans", ${FALLBACK}` },
    { value: 'outfit',     label: 'Outfit',                      stack: `"Outfit", ${FALLBACK}` },
    { value: 'publicsans', label: 'Public Sans',                 stack: `"Public Sans", ${FALLBACK}` },
    { value: 'plex',       label: 'IBM Plex Sans',               stack: `"IBM Plex Sans", ${FALLBACK}` },
    { value: 'instrument', label: 'Instrument Sans',             stack: `"Instrument Sans", ${FALLBACK}` },
    { value: 'figtree',    label: 'Figtree',                     stack: `"Figtree", ${FALLBACK}` },
];

const stackOf = (v) => (FONTS.find((f) => f.value === v) || FONTS[0]).stack;
const listeners = new Set();
const read = (key) => (typeof window === 'undefined' ? DEFAULT : window.localStorage.getItem(key) || DEFAULT);

function apply() {
    if (typeof document === 'undefined') return;
    document.documentElement.style.setProperty('--mantine-font-family', stackOf(read(BODY_KEY)));
    document.documentElement.style.setProperty('--mantine-font-family-headings', stackOf(read(HEAD_KEY)));
}

export const getBodyFont = () => read(BODY_KEY);
export const getHeadingFont = () => read(HEAD_KEY);

function set(key, v) {
    const val = FONTS.some((f) => f.value === v) ? v : DEFAULT;
    try { window.localStorage.setItem(key, val); } catch (e) { /* ignore */ }
    apply();
    listeners.forEach((l) => l());
}
export const setBodyFont = (v) => set(BODY_KEY, v);
export const setHeadingFont = (v) => set(HEAD_KEY, v);

function subscribe(cb) { listeners.add(cb); return () => listeners.delete(cb); }
export const useBodyFont = () => useSyncExternalStore(subscribe, getBodyFont, () => DEFAULT);
export const useHeadingFont = () => useSyncExternalStore(subscribe, getHeadingFont, () => DEFAULT);

// Re-apply the saved choices once React boots (belt-and-braces with the blade script).
export function initFont() { apply(); }

// Convenience for opting a heading-level element into the heading font.
export const HEADING_FONT = 'var(--mantine-font-family-headings)';
