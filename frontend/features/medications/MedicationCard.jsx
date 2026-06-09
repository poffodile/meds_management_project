import { Card, Group, Text, Badge, ThemeIcon, Box, Button, ActionIcon, Stack } from '@mantine/core';
import { IconPill, IconClock, IconDots, IconCheck, IconX, IconBan } from '@tabler/icons-react';
import { statusColors } from '@frontend/tokens';
import StatusBadge from '@frontend/components/StatusBadge';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';

/**
 * MedicationCard — one medication within a round (the mockup's central med card):
 * name + strength + form, therapeutic tags, dose·route·instruction, scheduled
 * time + status, stock, and the three outcome actions.
 *
 * Props:
 *   med — {
 *     name, strength, form, tags:[{label,color}], dose, route, instruction,
 *     time, status, statusLabel, stock, stockUnit, lowStock, isControlled,
 *     cdSchedule, code (set once recorded)
 *   }
 *   onAction(code) — 'A' administer / 'R' refused / 'O' omitted
 *   onMenu — optional "…" handler
 */
export default function MedicationCard({ med = {}, onAction, onMenu }) {
    const accent = statusColors[String(med.status ?? '').toLowerCase()] ?? 'indigo';
    const recorded = Boolean(med.code);
    const title = [med.name, med.strength, med.form].filter(Boolean).join(' ');
    const meta = [med.dose, med.route, med.instruction].filter(Boolean).join(' · ');

    return (
        <Card withBorder radius="lg" padding="md" style={{ borderLeft: `4px solid var(--mantine-color-${accent}-5)` }}>
            <Group justify="space-between" align="flex-start" wrap="nowrap">
                <Group gap="sm" wrap="nowrap" align="flex-start">
                    <ThemeIcon variant="light" color={accent} size={44} radius="md"><IconPill size={22} /></ThemeIcon>
                    <Box>
                        <Group gap="xs" wrap="wrap">
                            <Text fw={700}>{title || 'Medication'}</Text>
                            {med.isControlled && <Badge size="xs" color="grape" variant="light">CD{med.cdSchedule ? ` ${med.cdSchedule}` : ''}</Badge>}
                        </Group>
                        <Group gap={6} mt={4} wrap="wrap">
                            {(med.tags ?? []).map((t, i) => (
                                <Badge key={i} size="sm" variant="light" color={t.color ?? 'gray'} radius="sm">{t.label}</Badge>
                            ))}
                        </Group>
                        {meta && <Text size="sm" c="dimmed" mt={6}>{meta}</Text>}
                        {med.time && (
                            <Group gap={6} mt={6} wrap="nowrap">
                                <IconClock size={14} color={`var(--mantine-color-${accent}-6)`} />
                                <Text size="sm" fw={500}>{med.time}</Text>
                                {med.statusLabel && <Text size="sm" c={accent}>{med.statusLabel}</Text>}
                            </Group>
                        )}
                    </Box>
                </Group>

                <Group gap="lg" align="flex-start" wrap="nowrap">
                    {med.stock != null && (
                        <Box ta="right">
                            <Text size="xs" c="dimmed">Stock</Text>
                            <Text size="sm" fw={600}>{med.stock}{med.stockUnit ? ` ${med.stockUnit}` : ''}</Text>
                            {med.lowStock && <Text size="xs" c="orange" fw={600}>Low Stock</Text>}
                        </Box>
                    )}
                    {onMenu && <ActionIcon variant="subtle" color="gray" onClick={onMenu}><IconDots size={18} /></ActionIcon>}
                </Group>
            </Group>

            {recorded ? (
                <Group mt="md"><StatusBadge status={CODE_LABELS[med.code] ?? med.code} label={CODE_LABELS[med.code] ?? med.code} /></Group>
            ) : (
                <Stack gap={8} mt="md">
                    <Button color="green" leftSection={<IconCheck size={16} />} onClick={() => onAction?.('A')} fullWidth>Administer</Button>
                    <Group grow>
                        <Button variant="outline" color="red" leftSection={<IconX size={16} />} onClick={() => onAction?.('R')}>Refused</Button>
                        <Button variant="default" color="gray" leftSection={<IconBan size={16} />} onClick={() => onAction?.('O')}>Omitted / Not Given</Button>
                    </Group>
                </Stack>
            )}
        </Card>
    );
}
