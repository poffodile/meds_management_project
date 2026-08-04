/**
 * frontend4 design tokens.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DO NOT import this from frontend1, frontend2 or frontend3, and do not merge it
 * into any of their token files. Each front end owns its own palette on purpose.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * RELATIONSHIP TO THE CARE ONE OS VISUAL SPECIFICATION
 * The specification's direction is "Quiet Clinical Luxury" — warm ivory #F6F2E9
 * with clinical teal #176B65. That is frontend3's palette. Frontend4 keeps its
 * cool grey + indigo instead, decided 2026-08-04, so the two front ends can be
 * put side by side and chosen between.
 *
 * The HUE diverges. Nothing else does. Every craft rule in the specification
 * still applies here and is implemented below:
 *   · ten statuses, each at three intensities — strong / soft / faint
 *   · status shown as dot or icon PLUS a word, never colour alone
 *   · Manrope headings, Inter interface text, on the specified size scale
 *   · thin edge indicators rather than filled colour blocks
 *   · eight-point spacing, near-invisible shadows, thin borders
 *
 * Mirrored by the custom properties at the top of frontend4/f4.css.
 * Change the two together.
 */

/** Core colours — cool greys with a single indigo accent. */
export const palette = {
  canvas:   '#F7F8FA', // app background
  surface:  '#FFFFFF', // cards and primary surfaces
  sunken:   '#EEF1F5', // secondary bands, grouping
  ink:      '#111827', // headings, and the sidebar ground
  body:     '#334155', // body text
  muted:    '#64748B', // secondary text, metadata
  line:     '#E2E8F0', // borders and dividers
  lineHard: '#CBD5E1', // strong divider
  accent:   '#3F4FD6', // primary action, focus, current location
  accentIn: '#2F3CB0', // accent, pressed
  accentEdge:'#7C88E8', // the thin indicator on a dark ground
};

/**
 * The ten statuses from the visual specification, in frontend4's cool register.
 *
 * THREE INTENSITIES, each with one job:
 *   strong — text, icons, the status dot, thin borders
 *   soft   — badge background, selected filter, small notification area
 *   faint  — hover state, table-row emphasis, context-panel background
 *   edge   — the outlined-badge border, between strong and soft
 *
 * Every `strong` value clears 4.5:1 on white, because it is used as TEXT. The
 * tints are never used as text and never carry meaning on their own — see the
 * `label` field: a status always renders its word.
 *
 * `witness` is deliberately a greyer, deeper indigo than `palette.accent`. The
 * accent means "this is the action"; witness means "this is waiting for a second
 * person". Two indigos that read alike would be a genuine safety confusion.
 */
export const statusPalette = {
  given:    { label: 'Given',         strong: '#146B4A', soft: '#E6F2EC', faint: '#F2F9F6', edge: '#A8CFBE' },
  due:      { label: 'Due now',       strong: '#2A5FA0', soft: '#E7EEF8', faint: '#F4F7FC', edge: '#AEC4E2' },
  upcoming: { label: 'Upcoming',      strong: '#5A6675', soft: '#EDEFF3', faint: '#F6F7F9', edge: '#C3C9D2' },
  late:     { label: 'Late',          strong: '#8C5410', soft: '#F7EEE2', faint: '#FBF6F0', edge: '#DCC3A0' },
  overdue:  { label: 'Overdue',       strong: '#A32638', soft: '#F9E9EC', faint: '#FDF4F6', edge: '#E0B2BB' },
  refused:  { label: 'Refused',       strong: '#6E4A6B', soft: '#F2ECF1', faint: '#F9F5F8', edge: '#CDBACA' },
  omitted:  { label: 'Not available', strong: '#64605A', soft: '#F0EFED', faint: '#F8F7F6', edge: '#CBC7C1' },
  witness:  { label: 'Witness needed',strong: '#474F7A', soft: '#ECEEF6', faint: '#F5F7FB', edge: '#B9BFD6' },
  info:     { label: 'Information',   strong: '#4A6479', soft: '#EBF0F4', faint: '#F5F8FA', edge: '#BCCBD6' },
  offline:  { label: 'Offline',       strong: '#4E545B', soft: '#EEEFF1', faint: '#F7F8F9', edge: '#C4C7CB' },
};

/** Geometry. Cards sit at 14px per the specification's 12–16px range. */
export const radius  = { sm: 6, md: 10, lg: 14, pill: 999 };

/** Eight-point spacing, with the specification's stated purposes. */
export const spacing = {
  hair: 4,  // very small internal gaps
  xs:   8,  // icon to text
  sm:   12, // compact controls
  md:   16, // standard card padding
  lg:   24, // between related sections
  xl:   32, // between major sections
  xxl:  48, // page-level breathing space
};

/**
 * Manrope for headings — modern, refined, calm.
 * Inter for interface and clinical text — the most legible at small sizes for
 * medicine names, doses, tables and MAR records.
 */
export const fonts = {
  heading: '"Manrope", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  body:    '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
  mono:    'ui-monospace, SFMono-Regular, Menlo, Consolas, monospace',
};

/** The specification's type scale. Nothing below 12px, ever. */
export const type = {
  pageTitle:     '1.9375rem', // 31px  (spec 30–32)
  sectionTitle:  '1.3125rem', // 21px  (spec 20–22)
  cardHeading:   '1.0625rem', // 17px  (spec 16–18)
  body:          '1rem',      // 16px  (spec 15–16)
  supporting:    '0.875rem',  // 14px  (spec 13–14)
  label:         '0.75rem',   // 12px  (spec 12)
  dose:          '1.0625rem', // 17px  (spec 16–18)
  medicineName:  '1.1875rem', // 19px  (spec 18–20) — critical medicine name
};

/** Restrained elevation. Cards rely on surface and border contrast, not shadow. */
export const shadows = {
  hairline: `1px solid ${palette.line}`,
  card:     '0 1px 2px rgba(17,24,39,.03)',
  float:    '0 8px 24px -12px rgba(17,24,39,.22)', // drawers, sheets, modals only
};

/** Control heights — the specification's touch targets. */
export const controls = {
  fieldDesktop: 44,
  fieldMobile:  50,
  buttonMin:    44,
};

export const breakpoints = { sm: '600px', md: '900px', lg: '1200px' };

export default {
  palette, statusPalette, radius, spacing, fonts, type, shadows, controls, breakpoints,
};
