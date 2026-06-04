import { createTheme } from '@mantine/core';

/**
 * Brand design tokens — the ONE place that controls the whole app's look
 * (colours, fonts, spacing, radius). Change it here, every page updates.
 *
 * White-label ready: a per-company theme can later be merged on top of this
 * so each care company can have its own colours/logo.
 */
export const theme = createTheme({
    primaryColor: 'indigo',
    defaultRadius: 'md',
    fontFamily: 'Inter, -apple-system, "Segoe UI", Roboto, sans-serif',
    headings: { fontWeight: '700' },
});
