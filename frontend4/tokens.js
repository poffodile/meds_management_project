/**
 * frontend4 design tokens.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DO NOT import this from frontend1, frontend2 or frontend3, and do not merge it
 * into any of their token files. Each front end owns its own palette on purpose.
 * frontend3 is warm (ivory #F6F2E9 / clinical teal #176B65); frontend4 is cool
 * and high-contrast. They are different by design — mixing them looks like a bug.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Mirrored by the custom properties at the top of frontend4/f4.css.
 * Change the two together.
 */

/** Core colours — cool greys with a single indigo accent. */
export const palette = {
  canvas:  '#F7F8FA', // app background
  surface: '#FFFFFF', // cards and primary surfaces
  sunken:  '#EEF1F5', // secondary bands, grouping
  ink:     '#111827', // headings
  body:    '#334155', // body text
  muted:   '#64748B', // secondary text, metadata
  line:    '#E2E8F0', // borders and dividers
  accent:  '#3F4FD6', // primary action, focus
  accentIn:'#2F3CB0', // accent, pressed
};

/**
 * Status tints. Each is used ALONGSIDE a word, never as the sole carrier of
 * meaning — same rule frontend3 follows, kept here so the two behave alike
 * even though they look nothing alike.
 */
export const statusPalette = {
  neutral: { fg: '#334155', bg: '#EEF1F5' },
  good:    { fg: '#166534', bg: '#E7F4EC' },
  caution: { fg: '#854D0E', bg: '#FBF1DC' },
  risk:    { fg: '#9F1239', bg: '#FCE9EE' },
  info:    { fg: '#3F4FD6', bg: '#EAECFB' },
};

/** Geometry — eight-point spacing. */
export const radius  = { sm: 6, md: 10, lg: 14, pill: 999 };
export const spacing = { xs: 4, sm: 8, md: 16, lg: 24, xl: 32, xxl: 48 };

export const fonts = {
  heading: '"Figtree", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  body:    '"Figtree", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
};

/** Restrained elevation. */
export const shadows = {
  hairline: `1px solid ${palette.line}`,
  lift:     '0 1px 2px rgba(17,24,39,.04), 0 8px 20px -12px rgba(17,24,39,.18)',
};

export const breakpoints = { sm: '600px', md: '900px', lg: '1200px' };

export default { palette, statusPalette, radius, spacing, fonts, shadows, breakpoints };
