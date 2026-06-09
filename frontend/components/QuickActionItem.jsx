import { Group, Text, ThemeIcon, Box, UnstyledButton } from '@mantine/core';

/**
 * QuickActionItem — an icon + label + description row used in side panels /
 * action lists (Scan Medication, Add PRN, …). Renders as a link when `href` is
 * given, a button when `onClick` is given.
 *
 * Props: icon, label, description, href, onClick, color, disabled.
 */
export default function QuickActionItem({ icon: Icon, label, description, href, onClick, color = 'indigo', disabled = false }) {
    const inner = (
        <Group gap="sm" wrap="nowrap" px="xs" py={8} style={{ borderRadius: 8, opacity: disabled ? 0.55 : 1 }}>
            {Icon && (
                <ThemeIcon variant="light" color={color} size={36} radius="md">
                    <Icon size={18} stroke={1.6} />
                </ThemeIcon>
            )}
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text size="sm" fw={600}>{label}</Text>
                {description && <Text size="xs" c="dimmed">{description}</Text>}
            </Box>
        </Group>
    );
    if (href && !disabled) {
        return <UnstyledButton component="a" href={href} w="100%">{inner}</UnstyledButton>;
    }
    return <UnstyledButton onClick={disabled ? undefined : onClick} disabled={disabled} w="100%">{inner}</UnstyledButton>;
}
