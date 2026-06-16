import { RingProgress, Text, Group, Stack, Box } from '@mantine/core';
import { IconClock } from '@tabler/icons-react';

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

export default function RoundProgressDonut({ completed = 0, dueSoon = 0, overdue = 0, notStarted = 0, size = 150, detailed = false, dayCompleted = null, dayTotal = null, ringAlign = 'center', estCompletion = null, pctSize = 36, legendFz = null }) {
    const counts = { completed, dueSoon, overdue, notStarted };
    const total = completed + dueSoon + overdue + notStarted;
    const pctOf = (n) => (total ? Math.round((n / total) * 100) : 0);
    const sections = SEGMENTS
        .filter((s) => counts[s.key] > 0)
        .map((s) => ({ value: total ? (counts[s.key] / total) * 100 : 0, color: s.color }));
    const small = size < 130;
    const legendSize = small ? 'xs' : 'sm';
    const legendTextProps = legendFz ? { fz: legendFz } : { size: legendSize };

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
                        <Text {...legendTextProps} fw={500}>{s.label}</Text>
                    </Group>
                    <Group gap={6} wrap="nowrap">
                        <Text {...legendTextProps} fw={700}>{counts[s.key]}</Text>
                        <Text {...legendTextProps} c="dimmed">({pctOf(counts[s.key])}%)</Text>
                    </Group>
                </Group>
            ))}
        </Stack>
    );

    // Detailed: donut + a "% complete / overdue" summary beside it, legend below.
    if (detailed) {
        // Headline % reflects the whole day when day totals are supplied, else this round.
        const hasDay = dayTotal != null;
        const num = hasDay ? dayCompleted : completed;
        const den = hasDay ? dayTotal : total;
        const pct = den ? Math.round((num / den) * 100) : 0;
        return (
            <Stack gap="sm">
                <Group wrap="nowrap" align="center" gap="md">
                    <Box style={{ marginTop: 18 }}>{ring}</Box>
                    <Stack gap={8} style={{ flex: 1, minWidth: 0, marginTop: -10 }} align="flex-end">
                        <Box ta="right">
                            <Text fz={pctSize} fw={800} lh={1}>{pct}%</Text>
                            <Text size="xs" c="dimmed">Complete</Text>
                        </Box>
                        <Text c="red.6" fw={700} size="sm">{overdue} Overdue</Text>
                        {estCompletion && (
                            <Box>
                                <Text size="xs" c="dimmed">Est. completion</Text>
                                <Group gap={4} wrap="nowrap">
                                    <IconClock size={14} color="var(--mantine-color-gray-6)" />
                                    <Text size="sm" fw={600}>{estCompletion}</Text>
                                </Group>
                            </Box>
                        )}
                    </Stack>
                </Group>
                {legend}
            </Stack>
        );
    }

    // Stacked: donut on top, legend below — a tall, upright box.
    return (
        <Stack align="stretch" gap={small ? 'sm' : 'lg'}>
            <Group justify={ringAlign}>{ring}</Group>
            {legend}
        </Stack>
    );
}
