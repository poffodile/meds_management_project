import { RingProgress, Text, Group, Stack, Box } from '@mantine/core';

/**
 * RoundProgressDonut — a segmented ring + legend for round completion
 * (Completed / Due Soon / Overdue / Not Started) with an "N/total Complete"
 * centre label.
 *
 * Props: completed, dueSoon, overdue, notStarted (counts).
 */
const SEGMENTS = [
    { key: 'completed', label: 'Completed', color: 'teal' },
    { key: 'dueSoon', label: 'Due Soon', color: 'blue' },
    { key: 'overdue', label: 'Overdue', color: 'red' },
    { key: 'notStarted', label: 'Not Started', color: 'gray' },
];

export default function RoundProgressDonut({ completed = 0, dueSoon = 0, overdue = 0, notStarted = 0 }) {
    const counts = { completed, dueSoon, overdue, notStarted };
    const total = completed + dueSoon + overdue + notStarted;
    const pctOf = (n) => (total ? Math.round((n / total) * 100) : 0);
    const sections = SEGMENTS
        .filter((s) => counts[s.key] > 0)
        .map((s) => ({ value: total ? (counts[s.key] / total) * 100 : 0, color: s.color }));

    return (
        <Group align="center" gap="lg" wrap="nowrap">
            <RingProgress
                size={120}
                thickness={12}
                roundCaps
                sections={sections}
                label={
                    <Box ta="center">
                        <Text fw={700} fz={20} lh={1}>{completed}/{total}</Text>
                        <Text size="xs" c="dimmed">Complete</Text>
                    </Box>
                }
            />
            <Stack gap={8} style={{ flex: 1, minWidth: 0 }}>
                {SEGMENTS.map((s) => (
                    <Group key={s.key} justify="space-between" gap={8} wrap="nowrap">
                        <Group gap={8} wrap="nowrap">
                            <Box w={9} h={9} style={{ borderRadius: '50%', background: `var(--mantine-color-${s.color}-6)` }} />
                            <Text size="sm">{s.label}</Text>
                        </Group>
                        <Text size="sm" fw={600}>{counts[s.key]} ({pctOf(counts[s.key])}%)</Text>
                    </Group>
                ))}
            </Stack>
        </Group>
    );
}
