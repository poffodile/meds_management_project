import { Badge } from '@mantine/core';

/**
 * StatusBadge — a coloured status pill with ONE shared colour map for the whole app.
 * Add a status here once → every page shows it the same colour.
 *
 * Usage:
 *   <StatusBadge status="received" />
 *   <StatusBadge status="expired" label="Expired" variant="filled" />
 *   <StatusBadge status="custom" color="grape" />   // override the colour
 */
const STATUS_COLORS = {
    // stock / general
    ok: 'green',
    active: 'green',
    low: 'orange',
    'low stock': 'orange',
    expired: 'red',
    'out of stock': 'red',
    inactive: 'gray',
    pending: 'yellow',
    draft: 'gray',
    submitted: 'blue',
    acknowledged: 'green',
    resolved: 'green',
    // medication transaction types
    received: 'blue',
    administered: 'green',
    given: 'green',
    disposed: 'orange',
    returned: 'gray',
    correction: 'yellow',
    adjustment: 'yellow',
    // MAR / dose codes
    refused: 'red',
    omitted: 'orange',
    withheld: 'orange',
    sleeping: 'gray',
    'not available': 'gray',
    missed: 'red',
    not_given: 'orange',
    // priorities
    low: 'gray',
    medium: 'yellow',
    high: 'orange',
    urgent: 'red',
};

export default function StatusBadge({ status, label, color, variant = 'light', size }) {
    const key = String(status ?? '').toLowerCase();
    const resolvedColor = color ?? STATUS_COLORS[key] ?? 'gray';
    return (
        <Badge color={resolvedColor} variant={variant} size={size} tt="capitalize">
            {label ?? status ?? '—'}
        </Badge>
    );
}
