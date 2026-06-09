import { Card, Text, Group, ThemeIcon } from '@mantine/core';

/**
 * StatCard — a dashboard stat tile.
 *
 * Props:
 *   label    — the metric name (required)
 *   value    — the big number (required)
 *   color    — accent colour (icon tint, or value colour when no icon)
 *   icon     — optional Tabler icon component, shown in a tinted square
 *   sublabel — optional small caption under the value
 */
export default function StatCard({ label, value, color = 'gray', icon: Icon, sublabel }) {
    return (
        <Card withBorder radius="lg" padding="lg">
            <Group gap="sm" wrap="nowrap">
                {Icon && (
                    <ThemeIcon variant="light" color={color} size={40} radius="md">
                        <Icon size={22} stroke={1.6} />
                    </ThemeIcon>
                )}
                <Text size="sm" fw={600} c="dimmed">{label}</Text>
            </Group>
            <Text fw={700} fz={30} mt="md" c={Icon ? undefined : color}>{value}</Text>
            {sublabel && <Text size="xs" c="dimmed" mt={4}>{sublabel}</Text>}
        </Card>
    );
}
