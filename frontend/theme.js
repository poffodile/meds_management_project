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
