import { Group, Title, Text, Box, ThemeIcon } from '@mantine/core';

/**
 * PageHeader — the standard title strip at the top of every page (the page "hero").
 *
 * Props:
 *   title    — the page name (required)
 *   subtitle — short description under the title (optional)
 *   icon     — optional Tabler icon, shown in a tinted square before the title
 *   color    — accent colour for the icon (default "indigo")
 *   actions  — buttons/controls shown on the right (optional)
 *
 * Usage:
 *   <PageHeader title="Medication Stock" subtitle="Inventory and disposals"
 *               icon={IconBox} actions={<Button>Add</Button>} />
 */
export default function PageHeader({ title, subtitle, icon: Icon, color = 'indigo', actions }) {
    return (
        <Group justify="space-between" align="center" mb="lg" wrap="nowrap">
            <Group gap="md" wrap="nowrap" align="center">
                {Icon && (
                    <ThemeIcon variant="light" color={color} size={48} radius="lg">
                        <Icon size={26} stroke={1.6} />
                    </ThemeIcon>
                )}
                <Box>
                    <Title order={2}>{title}</Title>
                    {subtitle && <Text c="dimmed" size="sm" mt={2}>{subtitle}</Text>}
                </Box>
            </Group>
            {actions && <Group gap="sm" wrap="nowrap">{actions}</Group>}
        </Group>
    );
}
