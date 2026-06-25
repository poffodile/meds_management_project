import { Box, Group, Text, Anchor } from '@mantine/core';

/**
 * AppFooter — slim app-wide footer shown at the bottom of every shell page.
 * Light/dark aware; keep it quiet (small dimmed text + a few links).
 */
export default function AppFooter() {
    const year = new Date().getFullYear();
    return (
        <Box
            component="footer"
            style={{
                borderTop: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
                background: 'light-dark(#ffffff, var(--mantine-color-dark-7))',
                padding: '14px 24px',
                // bleed out past the AppShell.Main padding so it spans edge-to-edge / hugs the bottom
                marginLeft: 'calc(-1 * var(--app-shell-padding))',
                marginRight: 'calc(-1 * var(--app-shell-padding))',
                marginBottom: 'calc(-1 * var(--app-shell-padding))',
            }}
        >
            <Group justify="space-between" wrap="wrap" gap="sm">
                <Text size="xs" c="dimmed">© {year} Care One OS · Care management platform</Text>
                <Group gap="lg" wrap="wrap">
                    <Anchor href="#" size="xs" c="dimmed" underline="never">Privacy</Anchor>
                    <Anchor href="#" size="xs" c="dimmed" underline="never">Terms</Anchor>
                    <Anchor href="#" size="xs" c="dimmed" underline="never">Support</Anchor>
                    <Text size="xs" c="dimmed">v1.0.0</Text>
                </Group>
            </Group>
        </Box>
    );
}
