/**
 * frontend3 design tokens — "Quiet Clinical Luxury"
 *
 * Source: docs/care-one-os/FRONTEND3/CARE-ONE-OS-UX-SPECIFICATION.md §17
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DO NOT import this from frontend1 or frontend2, and do not merge it into
 * `frontend/tokens.js`. That file is frontend2's palette and it is DIFFERENT
 * (navy #13233F, multi-hue accents). These two are close enough to be confused
 * and different enough to look wrong if mixed. Keeping them apart is the point.
 * See docs/care-one-os/FRONTEND3/FRONTEND3-PLAN.md.
 * ─────────────────────────────────────────────────────────────────────────────
 */

/** The nine core colours. */
export const palette = {
  ivory:      '#F6F2E9', // app background, calm negative space
  porcelain:  '#FFFCF7', // cards and primary surfaces
  mist:       '#EEEAE2', // subtle grouping, secondary bands
  navy:       '#17243B', // primary headings, high-confidence structure
  teal:       '#176B65', // primary action, focus, positive emphasis
  eucalyptus: '#7E9B90', // secondary accents, quiet data visualisation
  ink:        '#202A35', // body text
  slate:      '#626D78', // secondary text, metadata
  stone:      '#D9D4CA', // borders and dividers
};

/**
 * Status tints. Muted on purpose — no rainbow badges.
 * Each is only ever used ALONGSIDE a word and a shape, never as the sole
 * carrier of meaning (spec §17 and §18).
 */
export const statusPalette = {
  neutral: { fg: '#3B4757', bg: '#ECEAE3', mark: '●' },
  good:    { fg: '#135C57', bg: '#E2EDEA', mark: '✓' },
  caution: { fg: '#7A5A22', bg: '#F3EBDC', mark: '▲' },
  risk:    { fg: '#7E3327', bg: '#F5E7E3', mark: '■' },
  info:    { fg: '#4A6560', bg: '#E9EEEB', mark: '◐' },
};

/** Maps a domain status to a tint + word + shape. Status is never colour alone. */
export const statusFor = (status) => ({
  due:         { tone: 'neutral', label: 'Due' },
  early:       { tone: 'info',    label: 'Early window' },
  late:        { tone: 'caution', label: 'Late' },
  overdue:     { tone: 'risk',    label: 'Overdue' },
  completed:   { tone: 'good',    label: 'Completed' },
  given:       { tone: 'good',    label: 'Administered' },
  declined:    { tone: 'risk',    label: 'Declined' },
  unavailable: { tone: 'risk',    label: 'Unavailable' },
  partial:     { tone: 'caution', label: 'Part administered' },
  inProgress:  { tone: 'info',    label: 'In progress' },
  needsReview: { tone: 'caution', label: 'Needs review' },
}[status] ?? { tone: 'neutral', label: status });

/** Geometry — spec §17: radius 12–16px, eight-point spacing. */
export const radius  = { sm: 8, md: 12, lg: 16, pill: 999 };
export const spacing = { xs: 4, sm: 8, md: 16, lg: 24, xl: 32, xxl: 48 };

export const fonts = {
  heading: '"Manrope", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  body:    '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
};

/** Restrained elevation — overlays and active workspaces only. */
export const shadows = {
  hairline: `1px solid ${palette.stone}`,
  lift:     '0 1px 2px rgba(23,36,59,.04), 0 8px 24px -12px rgba(23,36,59,.16)',
  liftLg:   '0 2px 4px rgba(23,36,59,.05), 0 24px 48px -20px rgba(23,36,59,.28)',
};

/** Mobile-first breakpoints, spec §18. */
export const breakpoints = {
  sm: '600px',   // below: single column, bottom nav, sticky primary action
  md: '900px',   // below: adaptive cards, compact side sheet
  lg: '1200px',  // below: icon rail + list-detail. At/above: three-zone workspace
};

export default { palette, statusPalette, statusFor, radius, spacing, fonts, shadows, breakpoints };
