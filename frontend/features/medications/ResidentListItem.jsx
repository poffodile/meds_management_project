import { Group, Text, Avatar, Box, UnstyledButton } from '@mantine/core';
import { statusColors } from '@frontend/tokens';
import StatusBadge from '@frontend/components/StatusBadge';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

/**
 * ResidentListItem — a compact resident row for the round's "Residents Due"
 * list: avatar, name, room, and round-status pill. Highlights when selected.
 *
 * Props: resident {name, room, photo}, status, statusLabel, selected, onClick.
 */
export default function ResidentListItem({ resident = {}, status, statusLabel, selected = false, onClick }) {
    const accent = statusColors[String(status ?? '').toLowerCase()] ?? 'gray';
    return (
        <UnstyledButton
            onClick={onClick}
            w="100%"
            style={{
                borderRadius: 10,
                borderLeft: `3px solid ${selected ? `var(--mantine-color-${accent}-6)` : 'transparent'}`,
                background: selected ? 'var(--mantine-color-gray-0)' : 'transparent',
            }}
        >
            <Group gap="sm" wrap="nowrap" p="sm">
                <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={40}>
                    {initials(resident.name ?? '')}
                </Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Text size="sm" fw={600} truncate>{resident.name}</Text>
                    {resident.room && <Text size="xs" c="dimmed">Room {resident.room}</Text>}
                </Box>
                {status && <StatusBadge status={status} label={statusLabel ?? status} size="sm" />}
            </Group>
        </UnstyledButton>
    );
}
