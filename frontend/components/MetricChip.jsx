import { Group, Text, ThemeIcon } from '@mantine/core';

/**
 * MetricChip — a compact "icon · label · value" row for inline metrics
 * (e.g. Fall Risk: High, PRN Available: 2). Lighter than StatCard, meant to
 * stack inside a panel.
 *
 * Props: icon (Tabler component), label, value, color (accent for icon + value).
 */
export default function MetricChip({ icon: Icon, label, value, color = 'gray' }) {
    return (
        <Group justify="space-between" wrap="nowrap" gap="sm">
            <Group gap="sm" wrap="nowrap">
                {Icon && (
                    <ThemeIcon variant="light" color={color} size={32} radius="md">
                        <Icon size={18} stroke={1.6} />
                    </ThemeIcon>
                )}
                <Text size="sm" c="dimmed">{label}</Text>
            </Group>
            <Text fw={700} c={color === 'gray' ? undefined : color}>{value}</Text>
        </Group>
    );
}
