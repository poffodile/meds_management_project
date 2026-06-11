import { Card, Box, Group, Text, Badge, ThemeIcon, Button } from '@mantine/core';
import { IconPill, IconCheck, IconX, IconBan, IconClock, IconInfoCircle, IconArrowRight } from '@tabler/icons-react';
import { statusColors } from '@frontend/tokens';
import StatusBadge from '@frontend/components/StatusBadge';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';

/**
 * MedicationCard — one medication within a round. Lays out as two stacked rows
 * so it stays clean at any column width: (1) the medication info (icon, name,
 * tags, dose · route · instruction, "HH:MM Due"); (2) the stock block + the
 * outcome actions. Avoids the squeeze that happens when everything is forced
 * onto a single row in a narrow column.
 *
 * Props:
 *   med — display shape from lib/medView.toMed()
 *   onAction(code) — 'A' administer / 'R' refused / 'O' omitted
 */
export default function MedicationCard({ med = {}, onAction }) {
    const accent = statusColors[String(med.status ?? '').toLowerCase()] ?? 'indigo';
    const recorded = Boolean(med.code);
    const title = [med.name, med.strength].filter(Boolean).join(' ');

    return (
        <Card withBorder radius="lg" padding="md" style={{ borderLeft: `4px solid var(--mantine-color-${accent}-5)` }}>
            {/* Row 1 — medication info */}
            <Group gap="sm" align="flex-start" wrap="nowrap">
                <ThemeIcon variant="light" color={accent} size={46} radius="xl" style={{ flexShrink: 0 }}><IconPill size={23} /></ThemeIcon>
                <Box style={{ minWidth: 0, flex: 1 }}>
                    <Group gap="xs" wrap="wrap">
                        <Text fw={700} size="md">{title || 'Medication'}</Text>
                        {med.isControlled && <Badge size="xs" color="grape" variant="light">CD{med.cdSchedule ? ` ${med.cdSchedule}` : ''}</Badge>}
                        {(med.tags ?? []).map((t, i) => <Badge key={i} size="sm" variant="light" color={t.color ?? 'gray'} radius="sm">{t.label}</Badge>)}
                    </Group>
                    <Group gap="md" mt={8} wrap="wrap">
                        {med.dose && <Group gap={4} wrap="nowrap"><IconPill size={13} color="var(--mantine-color-gray-5)" /><Text size="sm" c="dimmed">{med.dose}</Text></Group>}
                        {med.route && <Group gap={4} wrap="nowrap"><IconArrowRight size={13} color="var(--mantine-color-gray-5)" /><Text size="sm" c="dimmed">{med.route}</Text></Group>}
                        {med.instruction && <Group gap={4} wrap="nowrap"><IconInfoCircle size={13} color="var(--mantine-color-gray-5)" /><Text size="sm" c="dimmed">{med.instruction}</Text></Group>}
                    </Group>
                    {med.time && (
                        <Group gap={6} mt={8} wrap="nowrap">
                            <IconClock size={15} color={`var(--mantine-color-${accent}-6)`} />
                            <Text size="sm" fw={600} c={accent}>{med.time}</Text>
                            {med.statusLabel && <Text size="sm" c="dimmed">{med.statusLabel}</Text>}
                        </Group>
                    )}
                </Box>
            </Group>

            {/* Row 2 — stock + actions */}
            <Group justify="space-between" align="center" mt="md" gap="md" wrap="wrap">
                {med.stock != null ? (
                    <Group gap={6} wrap="nowrap">
                        <Text size="sm" c="dimmed">Stock</Text>
                        <Text size="sm" fw={600}>{med.stock}{med.stockUnit ? ` ${med.stockUnit}` : ''}</Text>
                        {med.lowStock && <Badge size="xs" color="orange" variant="light">Low</Badge>}
                    </Group>
                ) : <span />}

                {recorded ? (
                    <StatusBadge status={CODE_LABELS[med.code] ?? med.code} label={CODE_LABELS[med.code] ?? med.code} />
                ) : (
                    <Group gap="sm" wrap="wrap" justify="flex-end" style={{ flex: 1, minWidth: 220 }}>
                        <Button color="green" leftSection={<IconCheck size={16} />} onClick={() => onAction?.('A')} style={{ flex: 1, minWidth: 110 }}>Administer</Button>
                        <Button variant="outline" color="red" leftSection={<IconX size={16} />} onClick={() => onAction?.('R')} style={{ flex: 1, minWidth: 100 }}>Refused</Button>
                        <Button variant="default" leftSection={<IconBan size={16} />} onClick={() => onAction?.('O')} style={{ flex: 1, minWidth: 140 }}>Omitted / Not Given</Button>
                    </Group>
                )}
            </Group>
        </Card>
    );
}
