import { Group, Title, Text, Box } from '@mantine/core';

/**
 * PageHeader — the standard title strip at the top of every page (the page "hero").
 *
 * Props:
 *   title    — the page name (required)
 *   subtitle — short description under the title (optional)
 *   actions  — buttons/controls shown on the right (optional)
 *
 * Usage:
 *   <PageHeader title="Medication Stock" subtitle="Inventory and disposals"
 *               actions={<Button>Add</Button>} />
 */
export default function PageHeader({ title, subtitle, actions }) {
    return (
        <Group justify="space-between" align="flex-end" mb="lg" wrap="nowrap">
            <Box>
                <Title order={2}>{title}</Title>
                {subtitle && <Text c="dimmed" size="sm" mt={2}>{subtitle}</Text>}
            </Box>
            {actions && <Group gap="sm" wrap="nowrap">{actions}</Group>}
        </Group>
    );
}
