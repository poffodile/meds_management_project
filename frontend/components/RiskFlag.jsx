import StatusBadge from '@frontend/components/StatusBadge';

/**
 * RiskFlag — a coloured pill for a risk/priority (Falls Risk, etc.), coloured by
 * severity level via the shared `priority_*` status tokens.
 *
 * Props: label, level ('low'|'medium'|'high'|'urgent'), + any StatusBadge prop.
 */
export default function RiskFlag({ label, level = 'medium', ...rest }) {
    return <StatusBadge status={`priority_${level}`} label={label} {...rest} />;
}
