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
export default function AlertItem({ severity = 'warning', icon: Icon, title, description, href, onClick, variant = 'tinted', compact = false }) {
    const color = SEVERITY[severity] ?? SEVERITY.warning;
    const clickable = Boolean(href || onClick);
    const bar = variant === 'bar';
    const inner = (
        <Group gap={compact ? 7 : 'sm'} wrap="nowrap" align="flex-start" px={compact ? 8 : 'sm'} py={compact ? 3 : 'sm'}
            style={{
                borderRadius: 10,
                background: bar ? 'var(--mantine-color-gray-0)' : `var(--mantine-color-${color}-0)`,
                borderLeft: bar ? `4px solid var(--mantine-color-${color}-5)` : undefined,
            }}>
            {Icon && (
                <ThemeIcon variant="light" color={color} size={compact ? 22 : 32} radius="md">
                    <Icon size={compact ? 13 : 18} stroke={1.6} />
                </ThemeIcon>
            )}
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text size={compact ? 'xs' : 'sm'} fw={600} lh={1.2}>{title}</Text>
                {description && <Text size="xs" c="dimmed" lh={1.25} mt={1} fz={compact ? 9 : undefined} truncate={compact || undefined}>{description}</Text>}
            </Box>
            {clickable && <IconChevronRight size={16} color="var(--mantine-color-gray-5)" />}
        </Group>
    );
    if (href) return <UnstyledButton component="a" href={href} w="100%">{inner}</UnstyledButton>;
    if (onClick) return <UnstyledButton onClick={onClick} w="100%">{inner}</UnstyledButton>;
    return inner;
}
