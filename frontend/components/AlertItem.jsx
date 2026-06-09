import { Group, Text, ThemeIcon, Box, UnstyledButton } from '@mantine/core';
import { IconChevronRight } from '@tabler/icons-react';

const SEVERITY = { danger: 'red', warning: 'orange', info: 'blue', success: 'green' };

/**
 * AlertItem — a tinted alert row (icon + title + description + optional chevron)
 * for an alerts panel. Becomes clickable when `href` or `onClick` is given.
 *
 * Props: severity ('danger'|'warning'|'info'|'success'), icon, title,
 *        description, href, onClick.
 */
export default function AlertItem({ severity = 'warning', icon: Icon, title, description, href, onClick }) {
    const color = SEVERITY[severity] ?? SEVERITY.warning;
    const clickable = Boolean(href || onClick);
    const inner = (
        <Group gap="sm" wrap="nowrap" align="flex-start" p="sm"
            style={{ borderRadius: 10, background: `var(--mantine-color-${color}-0)` }}>
            {Icon && (
                <ThemeIcon variant="light" color={color} size={32} radius="md">
                    <Icon size={18} stroke={1.6} />
                </ThemeIcon>
            )}
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text size="sm" fw={600}>{title}</Text>
                {description && <Text size="xs" c="dimmed">{description}</Text>}
            </Box>
            {clickable && <IconChevronRight size={16} color="var(--mantine-color-gray-5)" />}
        </Group>
    );
    if (href) return <UnstyledButton component="a" href={href} w="100%">{inner}</UnstyledButton>;
    if (onClick) return <UnstyledButton onClick={onClick} w="100%">{inner}</UnstyledButton>;
    return inner;
}
