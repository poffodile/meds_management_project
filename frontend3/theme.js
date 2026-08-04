/**
 * frontend3 Mantine theme — built from frontend3/tokens.js only.
 *
 * Deliberately NOT importing `@frontend/theme` or `@frontend/tokens`.
 * frontend3 shares the stack with frontend2 and shares none of its files.
 */

import { createTheme } from '@mantine/core';
import { palette, radius, fonts } from './tokens';

/** Mantine wants 10 shades. Built around the spec's clinical teal #176B65. */
const teal = [
  '#EFF5F4', '#DCE9E7', '#B8D2CF', '#92BAB6', '#72A6A1',
  '#5D9993', '#4F938C', '#3C7F79', '#2A6F69', '#176B65',
];

/** Around deep navy #17243B. */
const navy = [
  '#EEF0F3', '#D6DAE1', '#ADB5C2', '#8391A5', '#63738C',
  '#4D5F7C', '#405274', '#324362', '#263553', '#17243B',
];

export const theme = createTheme({
  fontFamily: fonts.body,
  fontFamilyMonospace: 'ui-monospace, SFMono-Regular, Menlo, monospace',
  headings: {
    fontFamily: fonts.heading,
    fontWeight: '700',
  },

  colors: { teal, navy },
  primaryColor: 'teal',
  primaryShade: 9,

  white: palette.porcelain,
  black: palette.ink,

  defaultRadius: 'md',
  radius: {
    sm: `${radius.sm}px`,
    md: `${radius.md}px`,
    lg: `${radius.lg}px`,
  },

  /** Base 16px on mobile; the spec forbids shrinking critical content to fit. */
  fontSizes: {
    xs: '0.75rem', sm: '0.8125rem', md: '0.875rem', lg: '1rem', xl: '1.125rem',
  },

  components: {
    Button: {
      defaultProps: { radius: 'md' },
      // 44px keeps primary mobile controls inside the spec's touch-target guidance.
      styles: { root: { minHeight: 44, fontWeight: 600 } },
    },
    Card: {
      defaultProps: { radius: 'lg', withBorder: true },
    },
  },

  other: { palette },
});

export default theme;
