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
    components: {
        // Soft shadow on every card → depth instead of flat "boxed" borders.
        Card: { defaultProps: { shadow: 'sm' } },
    },
});

export * from './tokens';
