import { RingProgress, Text, Group, Stack, Box } from '@mantine/core';

/**
 * RoundProgressDonut — a big segmented ring with an "N/total Complete" centre
 * label and a legend listed below (Completed / Overdue / Due Soon / Not Started),
 * each with a coloured dot, count and percentage.
 *
 * Props: completed, dueSoon, overdue, notStarted (counts).
 */
const SEGMENTS = [
    { key: 'completed', label: 'Completed', color: 'green' },
    { key: 'overdue', label: 'Overdue', color: 'yellow' },
    { key: 'dueSoon', label: 'Due Soon', color: 'orange' },
    { key: 'notStarted', label: 'Not Started', color: 'gray' },
];

export default function RoundProgressDonut({ completed = 0, dueSoon = 0, overdue = 0, notStarted = 0, size = 150 }) {
    const counts = { completed, dueSoon, overdue, notStarted };
    const total = completed + dueSoon + overdue + notStarted;
    const pctOf = (n) => (total ? Math.round((n / total) * 100) : 0);
    const sections = SEGMENTS
        .filter((s) => counts[s.key] > 0)
        .map((s) => ({ value: total ? (counts[s.key] / total) * 100 : 0, color: s.color }));
    const small = size < 130;
    const legendSize = small ? 'xs' : 'sm';

    const ring = (
        <RingProgress
            size={size}
            thickness={Math.max(7, Math.round(size * 0.09))}
            sections={sections}
            label={
                <Box ta="center">
                    <Text fw={700} fz={small ? 16 : 26} lh={1}>{completed}/{total}</Text>
                    <Text c="dimmed" lh={1} fz={small ? 9 : 12}>Complete</Text>
                </Box>
            }
        />
    );

    const legend = (
        <Stack gap={small ? 4 : 10}>
            {SEGMENTS.map((s) => (
                <Group key={s.key} justify="space-between" gap={8} wrap="nowrap">
                    <Group gap={8} wrap="nowrap">
                        <Box w={small ? 8 : 10} h={small ? 8 : 10} style={{ borderRadius: '50%', flexShrink: 0, background: `var(--mantine-color-${s.color}-6)` }} />
                        <Text size={legendSize} fw={500}>{s.label}</Text>
                    </Group>
                    <Group gap={6} wrap="nowrap">
                        <Text size={legendSize} fw={700}>{counts[s.key]}</Text>
                        <Text size={legendSize} c="dimmed">({pctOf(counts[s.key])}%)</Text>
                    </Group>
                </Group>
            ))}
        </Stack>
    );

    // Stacked: donut on top, legend below — a tall, upright box.
    return (
        <Stack align="stretch" gap={small ? 'sm' : 'lg'}>
            <Group justify="center">{ring}</Group>
            {legend}
        </Stack>
    );
}
