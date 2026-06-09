import { Badge } from '@mantine/core';
import { statusColors } from '@frontend/tokens';

/**
 * StatusBadge — a coloured status pill driven by the shared `statusColors`
 * token map (frontend/tokens.js). Add a status to that map once → every page
 * shows it the same colour.
 *
 * Usage:
 *   <StatusBadge status="received" />
 *   <StatusBadge status="expired" label="Expired" variant="filled" />
 *   <StatusBadge status="custom" color="grape" />   // override the colour
 */
export default function StatusBadge({ status, label, color, variant = 'light', size }) {
    const key = String(status ?? '').toLowerCase();
    const resolvedColor = color ?? statusColors[key] ?? 'gray';
    return (
        <Badge color={resolvedColor} variant={variant} size={size} tt="capitalize">
            {label ?? status ?? '—'}
        </Badge>
    );
}
