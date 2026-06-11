import { Group, Text, Avatar, Box, UnstyledButton } from '@mantine/core';
import { IconAlertTriangle, IconCircleCheck, IconClock } from '@tabler/icons-react';
import { statusColors } from '@frontend/tokens';
import StatusBadge from '@frontend/components/StatusBadge';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

/**
 * ResidentListItem — a compact resident row for the round's "Residents Due"
 * list: avatar + (name / room / round-status pill) stacked, a coloured left
 * accent by status, and a status icon. Highlights when selected. Never
 * overflows horizontally (name truncates).
 *
 * Props: resident {name, room, photo}, status, statusLabel, selected, onClick.
 */
const STATUS_ICON = {
    'all given': IconCircleCheck,
    completed: IconCircleCheck,
    overdue: IconAlertTriangle,
    due: IconClock,
    'due soon': IconClock,
    'not started': IconClock,
};

export default function ResidentListItem({ resident = {}, status, statusLabel, selected = false, onClick }) {
    const key = String(status ?? '').toLowerCase();
    const accent = statusColors[key] ?? 'gray';
    const Icon = STATUS_ICON[key];

    return (
        <UnstyledButton
            onClick={onClick}
            w="100%"
            style={{
                borderRadius: 10,
                borderLeft: `4px solid var(--mantine-color-${accent}-6)`,
                background: selected ? `var(--mantine-color-${accent}-0)` : 'transparent',
            }}
        >
            <Group gap="sm" wrap="nowrap" p="sm" align="flex-start">
                <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={40}>
                    {initials(resident.name ?? '')}
                </Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group justify="space-between" wrap="nowrap" gap="xs">
                        <Text size="sm" fw={600} truncate>{resident.name}</Text>
                        {Icon && <Icon size={16} color={`var(--mantine-color-${accent}-6)`} style={{ flexShrink: 0 }} />}
                    </Group>
                    {resident.room && <Text size="xs" c="dimmed">Room {resident.room}</Text>}
                    {status && <StatusBadge status={status} label={statusLabel ?? status} size="xs" mt={5} />}
                </Box>
            </Group>
        </UnstyledButton>
    );
}
