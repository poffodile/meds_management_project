import { createTheme } from '@mantine/core';
import { brand, radius, typography } from './tokens';

/**
 * The Mantine theme, built from the design tokens in ./tokens.js — that file
 * is the one place to change colours/spacing/fonts for the whole app.
 *
 * Tokens are re-exported here, so either `@frontend/theme` or `@frontend/tokens`
 * can supply them.
 */
export const theme = createTheme({
    primaryColor: brand.primary,
    // Official Care One OS core brand colours as Mantine scales (see docs/brand-guidelines.md).
    colors: {
        brandTeal:   ['#e6f8f7', '#c9f0ef', '#9ee5e3', '#6fd6d4', '#4ec8c6', '#45c1bf', '#34a8a6', '#258a88', '#196d6c', '#0d4f4e'],
        brandOrange: ['#fff0e1', '#ffdcbf', '#ffc394', '#ffa861', '#f99238', '#f58321', '#e06f10', '#b8590a', '#8f4408', '#663005'],
        brandPurple: ['#f6eef5', '#e7d4e5', '#d3b3d0', '#bd8fb9', '#9d6c98', '#795076', '#6a4567', '#573854', '#432b41', '#2f1e2e'],
        brandGreen:  ['#f1f7e4', '#ddecc0', '#c6e096', '#aed26a', '#9bc44e', '#88b13f', '#739838', '#5b7a2d', '#455d23', '#304118'],
    },
    defaultRadius: radius.control,
    fontFamily: typography.fontFamily,
    headings: { fontWeight: typography.headingWeight },
    // Slightly smaller type + spacing than Mantine defaults so the UI reads
    // compact on large screens — a clean, uniform reduction (no scale artifacts).
    fontSizes: {
        xs: '0.6875rem', // 11px
        sm: '0.8125rem', // 13px
        md: '0.9375rem', // 15px
        lg: '1.0625rem', // 17px
        xl: '1.1875rem', // 19px
    },
    spacing: {
        xs: '0.5rem',
        sm: '0.6875rem',
        md: '0.9375rem',
        lg: '1.1875rem',
        xl: '1.75rem',
    },
    components: {
        // Soft shadow on every card → depth instead of flat "boxed" borders.
        Card: { defaultProps: { shadow: 'sm' } },
    },
});

export * from './tokens';
