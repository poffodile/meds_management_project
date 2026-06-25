import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Card, Paper, Group, Stack, Text, Box, TextInput, Button, Modal, Textarea, Checkbox,
    Badge, ThemeIcon, ScrollArea, ActionIcon, Avatar, Divider, RingProgress, Progress, Collapse, Menu, Drawer, Select,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconPill,
    IconAlertTriangle, IconShieldLock, IconQrcode, IconPlus, IconUserMinus, IconNotes,
    IconFileText, IconClipboardList, IconX, IconClock, IconCheck, IconBan,
    IconArrowRight, IconInfoCircle, IconChevronRight, IconChevronDown,
    IconBedFilled, IconUser, IconCake, IconId, IconShieldFilled, IconHeart, IconWeight, IconActivity,
    IconCircleX, IconFlag,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';

import { roundTokens, statusColors } from '@frontend/tokens';
import { ageFromDob, formatDate } from '@frontend/lib/dateUtils';
import { toMed } from '@frontend/lib/medView';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import { usePageReload } from '@frontend/hooks/usePageReload';

// EXPERIMENTAL copy (Lab 1.4) — single-workspace redesign: calm, minimal,
// medication-cards-dominant. All markup that diverges from the shared
// components is built INLINE in this file so the shared components stay untouched.
const ENDPOINT = '/medication/medication-round-lab1-4-2';

/** Overall round status for a resident, from their rows' recorded codes/buckets. */
function residentStatus(resident) {
    const rows = resident.rows ?? [];
    if (rows.length === 0) return { status: 'not started', label: 'No meds' };
    const completed = rows.filter((r) => r.code).length;
    if (completed === rows.length) return { status: 'all given', label: 'All Given' };
    const overdue = rows.filter((r) => !r.code && r.status === 'overdue').length;
    if (overdue > 0) return { status: 'overdue', label: `${overdue} overdue` };
    return { status: 'due', label: `${rows.length - completed} due` };
}

// Status + type lookups for the "Next Medications Due" / "Recent Activity" sections.
const DUE_STATUS = {
    overdue: { label: 'Overdue', color: 'red' },
    due_now: { label: 'Due Soon', color: 'orange' },
    due: { label: 'Due', color: 'blue' },
    upcoming: { label: 'Upcoming', color: 'gray' },
    later: { label: 'Later', color: 'gray' },
    completed: { label: 'Done', color: 'green' },
};
const ACT_CODE = { A: 'given', S: 'self-administered', R: 'refused', W: 'witnessed', N: 'not given', O: 'omitted' };
const medType = (row) => (row.is_controlled
    ? { label: 'Controlled', color: 'grape' }
    : row.as_required ? { label: 'PRN', color: 'teal' } : { label: 'Regular', color: 'blue' });

// ---- Shared visual primitives (local to this file) ----

const cssVar = (color, shade) => `var(--mantine-color-${color}-${shade})`;
// One calm card style: white surface, soft shadow, hairline border — no coloured borders.
// Radius hierarchy: primary (resident header, med cards) 24, secondary (left panel,
// right sidebar) 18, tertiary (recent activity, alert rows, quick actions) 12.
const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};
const surfacePrimary = { ...surface, borderRadius: 24 };
const surfaceTertiary = { ...surface, borderRadius: 12 };

/** A section heading used inside the unified panels. */
function PanelSection({ title, count, right, children, divider = true, pt, collapsible = false, open = true, onToggle }) {
    const header = (
        <Group justify="space-between" align="center" mb={collapsible && !open ? 0 : 'sm'} wrap="nowrap">
            <Group gap={8} align="baseline" wrap="nowrap">
                <Text fw={700} fz="sm" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">{title}</Text>
                {count != null && <Text fz="xs" c="dimmed">{count}</Text>}
            </Group>
            {collapsible ? (
                <Box style={{ width: 22, height: 22, borderRadius: '50%', background: 'var(--mantine-color-gray-1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <IconChevronDown size={14} stroke={2.5} style={{ color: 'var(--mantine-color-gray-7)', transform: open ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                </Box>
            ) : right}
        </Group>
    );
    return (
        <Box>
            {divider && <Divider color="gray.2" />}
            <Box px="md" pt={pt ?? 'md'} pb="md">
                {collapsible ? (
                    <Box component="button" onClick={onToggle} style={{ width: '100%', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0 }}>
                        {header}
                    </Box>
                ) : header}
                {collapsible ? <Collapse in={open}>{children}</Collapse> : children}
            </Box>
        </Box>
    );
}

/** Compact resident row — avatar, name, round context, status badge. */
function ResidentRow({ resident, status, label, selected, onClick }) {
    const accent = statusColors[String(status).toLowerCase()] ?? 'gray';
    return (
        <Box
            component="button"
            onClick={onClick}
            style={{
                display: 'block', width: '100%', textAlign: 'left', cursor: 'pointer',
                border: 'none', borderRadius: 12, padding: '8px 10px',
                background: selected ? cssVar(accent, 0) : 'transparent',
                outline: selected ? `1px solid ${cssVar(accent, 2)}` : '1px solid transparent',
                transition: 'background 120ms ease',
            }}
            onMouseEnter={(e) => { if (!selected) e.currentTarget.style.background = 'var(--mantine-color-gray-0)'; }}
            onMouseLeave={(e) => { if (!selected) e.currentTarget.style.background = 'transparent'; }}
        >
            <Group gap="sm" wrap="nowrap" align="center">
                <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={36}>
                    {initials(resident.name ?? '')}
                </Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Text fz="sm" fw={600} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" truncate>{resident.name}</Text>
                    <Text fz="xs" c="dimmed" truncate>{resident.context}</Text>
                </Box>
                <Badge size="sm" variant="light" color={accent} radius="sm" style={{ flexShrink: 0 }}>{label}</Badge>
            </Group>
        </Box>
    );
}

const MED_STATUS = {
    overdue: { word: 'Overdue', color: 'red' },
    'due soon': { word: 'Due Now', color: 'brandOrange' },
    due_now: { word: 'Due Now', color: 'brandOrange' },
    due: { word: 'Due', color: 'brandTeal' },
    completed: { word: 'Given', color: 'brandGreen' },
};
const medStatusOf = (med) => MED_STATUS[String(med.status ?? '').toLowerCase()] ?? { word: med.statusLabel || 'Scheduled', color: 'blue' };

/** A bare label/value pair used in the med-card detail trio (Dose / Route / Stock). */
function CardStat({ label, value, valueColor = 'gray.9', sub, subColor = 'orange.7' }) {
    return (
        <Box style={{ minWidth: 0 }}>
            <Text fz={10} fw={700} c="gray.5" tt="uppercase" lh={1.1} style={{ letterSpacing: 0.4 }}>{label}</Text>
            <Text fz="sm" fw={700} c={valueColor} mt={3} lh={1.2}>{value}</Text>
            {sub && <Text fz={11} fw={600} c={subColor} mt={2} lh={1.1}>{sub}</Text>}
        </Box>
    );
}

/** Record split-button: primary "Record" runs Administer; the chevron opens the rest. */
function RecordSplitButton({ onAction }) {
    return (
        <Group gap={0} wrap="nowrap" style={{ flexShrink: 0 }}>
            <Button color="blue" radius="md" size="sm" leftSection={<IconCheck size={16} />} onClick={() => onAction?.('A')}
                styles={{ root: { borderTopRightRadius: 0, borderBottomRightRadius: 0, paddingRight: 14 } }}>
                Record
            </Button>
            <Menu position="bottom-end" radius="md" shadow="md" width={210} withinPortal>
                <Menu.Target>
                    <Button color="blue" radius="md" size="sm" px={8} aria-label="More outcomes"
                        styles={{ root: { borderTopLeftRadius: 0, borderBottomLeftRadius: 0, borderLeft: '1px solid rgba(255,255,255,0.35)' } }}>
                        <IconChevronDown size={16} />
                    </Button>
                </Menu.Target>
                <Menu.Dropdown>
                    <Menu.Label>Record outcome</Menu.Label>
                    <Menu.Item leftSection={<IconCheck size={15} color={cssVar('green', 6)} />} onClick={() => onAction?.('A')}>Administer (Given)</Menu.Item>
                    <Menu.Item leftSection={<IconCircleX size={15} color={cssVar('red', 6)} />} onClick={() => onAction?.('R')}>Refused</Menu.Item>
                    <Menu.Item leftSection={<IconBan size={15} color={cssVar('gray', 6)} />} onClick={() => onAction?.('O')}>Not Given / Omitted</Menu.Item>
                </Menu.Dropdown>
            </Menu>
        </Group>
    );
}

/** A "recorded" pill shown in place of the Record button once an outcome exists. */
function RecordedPill({ code }) {
    const label = CODE_LABELS[code] ?? code;
    const positive = code === 'A' || code === 'S' || code === 'W';
    const color = positive ? 'green' : (code === 'R' ? 'red' : 'gray');
    return (
        <Group gap={6} wrap="nowrap" px={12} py={7} style={{ flexShrink: 0, background: cssVar(color, 0), border: `1px solid ${cssVar(color, 2)}`, borderRadius: 10 }}>
            {positive ? <IconCircleCheck size={16} color={cssVar(color, 6)} /> : <IconInfoCircle size={16} color={cssVar(color, 6)} />}
            <Text fz="sm" fw={700} c={`${color}.7`}>{label}</Text>
        </Group>
    );
}

/**
 * MedicationRow — rich card: time + status word, tinted pill tile, name + badges +
 * instruction, a Dose/Route/Stock (or PRN) trio, and the Record split-button.
 */
// Cautionary instruction keywords get a stronger amber highlight.
const CAUTION_RE = /with food|before food|after food|empty stomach|crush|do not|don'?t|dissolve|nil by mouth|half|whole|swallow whole|on an empty/i;

function MedicationRow({ med, onAction, onEdit, variant = 'scheduled', locked = false, doubleDose = false }) {
    const isPrn = variant === 'prn';
    const st = medStatusOf(med);
    const recorded = Boolean(med.code);
    const [detailOpen, setDetailOpen] = useState(false);
    const accent = recorded ? 'green' : (isPrn ? 'grape' : st.color);
    const generic = [med.name, med.strength, med.route].filter(Boolean).join('  •  ');
    const doseValue = [med.dose, med.stockUnit].filter(Boolean).join(' ') || med.dose || '—';
    const tags = (med.tags ?? []).filter(Boolean);
    const outcomeLabel = CODE_LABELS[med.code] ?? med.code;
    const caution = med.instruction && CAUTION_RE.test(med.instruction);
    const instrTone = caution ? 'orange' : 'blue';
    const InstrIcon = caution ? IconAlertTriangle : IconInfoCircle;

    return (
        <Box style={{ ...surfacePrimary, padding: '18px 20px' }}>
            <Group align="flex-start" wrap="wrap" gap="lg" style={{ rowGap: 14 }}>
                {/* 1 — Time + status word */}
                <Box style={{ flex: '0 0 56px', textAlign: 'center' }}>
                    <Text fz={18} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.1}>{med.time || '—'}</Text>
                    <Text fz={11} fw={700} c={`${accent}.7`} mt={3} tt="uppercase" style={{ letterSpacing: 0.3 }}>
                        {recorded ? 'Given' : (isPrn ? 'PRN' : st.word)}
                    </Text>
                </Box>

                {/* 2 — Tinted icon tile */}
                <Box style={{
                    flex: '0 0 44px', width: 44, height: 44, borderRadius: 12,
                    background: cssVar(accent, 0), border: `1px solid ${cssVar(accent, 1)}`,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                    <IconPill size={22} stroke={1.7} color={cssVar(accent, 6)} />
                </Box>

                {/* 3 — Main info */}
                <Box style={{ flex: '1 1 240px', minWidth: 0 }}>
                    <Group gap={8} wrap="wrap" align="center">
                        <Text fw={700} fz="md" c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>{med.name || 'Medication'}</Text>
                        {med.isControlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD{med.cdSchedule ? ` ${med.cdSchedule}` : ''}</Badge>}
                        {doubleDose && !recorded && (
                            <Badge size="xs" color="red" variant="filled" radius="sm" leftSection={<IconAlertTriangle size={11} />}>May already be given</Badge>
                        )}
                    </Group>
                    {generic && <Text fz="sm" c="gray.6" mt={3} lh={1.35}>{generic}</Text>}
                    {tags.length > 0 && (
                        <Group gap={6} mt={7} wrap="wrap">
                            {tags.map((t, i) => <Badge key={i} size="xs" variant="light" color={t.color ?? 'gray'} radius="sm">{t.label}</Badge>)}
                        </Group>
                    )}
                    {med.instruction ? (
                        <Group gap={8} wrap="nowrap" align="center" mt={8} px={10} py={7}
                            style={{ background: cssVar(instrTone, 0), border: `1px solid ${cssVar(instrTone, 1)}`, borderRadius: 8 }}>
                            <InstrIcon size={15} color={cssVar(instrTone, 6)} style={{ flexShrink: 0 }} />
                            <Text fz="sm" fw={caution ? 600 : 500} c={`${instrTone}.8`} lh={1.35}>{med.instruction}</Text>
                        </Group>
                    ) : (
                        <Text fz="sm" c="gray.4" mt={8} lh={1.4}>No additional instructions.</Text>
                    )}
                </Box>

                {/* 4/5/6 — detail trio */}
                {isPrn ? (
                    <Group gap={26} wrap="wrap" align="flex-start" style={{ flex: '0 0 auto' }}>
                        <CardStat label="Last Given" value={med.prn?.last_given || '—'} valueColor={med.prn?.last_given ? 'gray.9' : 'gray.5'} />
                        <CardStat label="Next Available"
                            value={med.prn?.next_available || (med.prn?.last_given ? 'Now' : '—')}
                            valueColor={med.prn?.blocked ? 'orange.7' : (med.prn?.last_given ? 'green.7' : 'gray.5')} />
                        <CardStat label="Today"
                            value={med.prn?.max_daily != null ? `${med.prn?.given_today ?? 0} of ${med.prn.max_daily}` : `${med.prn?.given_today ?? 0}`}
                            valueColor={med.prn?.blocked ? 'orange.7' : 'gray.9'} />
                    </Group>
                ) : (
                    <Group gap={26} wrap="wrap" align="flex-start" style={{ flex: '0 0 auto' }}>
                        <CardStat label="Dose" value={doseValue} />
                        <CardStat label="Route" value={med.route || '—'} valueColor={med.route ? 'gray.9' : 'gray.5'} />
                        <CardStat label="Stock"
                            value={med.stock != null ? `${med.stock}${med.stockUnit ? ` ${med.stockUnit}` : ''}` : '—'}
                            valueColor={med.stock != null ? 'green.7' : 'gray.5'}
                            sub={med.lowStock ? 'Low stock' : null} />
                    </Group>
                )}

                {/* 7 — action */}
                <Box style={{ flex: '0 0 auto', alignSelf: 'center' }}>
                    {recorded ? (
                        <Stack gap={3} align="flex-end">
                            <Group gap={6} wrap="nowrap" align="center">
                                <RecordedPill code={med.code} />
                                <ActionIcon variant="subtle" color="gray" size={28} radius="md"
                                    onClick={() => setDetailOpen((o) => !o)} title="Dose details">
                                    <IconChevronDown size={16} style={{ transform: detailOpen ? 'rotate(180deg)' : 'none', transition: 'transform .15s' }} />
                                </ActionIcon>
                            </Group>
                            <Text fz={11} c="gray.6" ta="right">
                                {med.recordedAt ? med.recordedAt : (med.time || '')}{med.recordedBy ? ` · by ${med.recordedBy}` : ''}
                            </Text>
                        </Stack>
                    ) : locked ? (
                        <Button variant="light" color="gray" radius="md" size="sm" disabled>Round ended</Button>
                    ) : isPrn ? (
                        med.prn?.blocked ? (
                            <Stack gap={2} align="flex-end">
                                <Button variant="light" color="gray" radius="md" size="sm" disabled>Not available</Button>
                                {med.prn?.block_reason && <Text fz={10} fw={600} c="orange.7" ta="right" style={{ maxWidth: 150 }}>{med.prn.block_reason}</Text>}
                            </Stack>
                        ) : (
                            <Button color="grape" radius="md" size="sm" leftSection={<IconCheck size={16} />} onClick={() => onAction?.('A')}>Give PRN</Button>
                        )
                    ) : (
                        <RecordSplitButton onAction={onAction} />
                    )}
                </Box>
            </Group>

            {/* Recorded dose details — expands inline (option 1); "Open record" re-opens
                the dialog pre-filled (option 3). */}
            {recorded && (
                <Collapse in={detailOpen}>
                    <Box mt="md" pt="md" style={{ borderTop: '1px dashed var(--mantine-color-gray-2)' }}>
                        <Group gap={40} wrap="wrap" align="flex-start">
                            <CardStat label="Outcome" value={outcomeLabel} valueColor={`${accent}.7`} />
                            {med.code === 'A' && <CardStat label="Dose given" value={med.doseGiven || med.dose || '—'} />}
                            {med.reason && <CardStat label="Reason" value={med.reason} />}
                            {med.witnessedBy && <CardStat label="Witness" value={med.witnessedBy} />}
                            <CardStat label="Recorded by" value={med.recordedBy || '—'} sub={med.recordedAt ? `at ${med.recordedAt}` : null} subColor="gray.5" />
                        </Group>
                        <Box mt="sm">
                            <CardStat label="Notes" value={med.notes || 'No notes.'} valueColor={med.notes ? 'gray.8' : 'gray.5'} />
                        </Box>
                        {onEdit && (
                            <Button variant="light" color="indigo" size="xs" radius="md" mt="md"
                                leftSection={<IconFileText size={14} />} onClick={onEdit}>
                                Open record
                            </Button>
                        )}
                    </Box>
                </Collapse>
            )}
        </Box>
    );
}

/** A compact stat pill for the resident header (Risks / PRN / Meds). */
function StatBlock({ icon: Icon, label, value, color }) {
    return (
        <Group gap={6} wrap="nowrap" align="center" px={9} py={5}
            style={{ background: cssVar(color, 0), borderRadius: 999, border: `1px solid ${cssVar(color, 1)}` }}>
            <Icon size={14} stroke={1.8} color={cssVar(color, 6)} />
            <Text fz="xs" c="gray.6" lh={1}>{label}</Text>
            <Text fz="xs" fw={700} lh={1} c={`${color}.7`}>{value}</Text>
        </Group>
    );
}

/** Compact context chip for the resident panel — subtle bg, colour only when meaningful. */
function InfoChip({ icon: Icon, label, color = 'gray' }) {
    const tinted = color !== 'gray';
    return (
        <Group gap={5} wrap="nowrap" align="center" px={9} py={4}
            style={{ background: cssVar(color, 0), borderRadius: 8 }}>
            {Icon && <Icon size={13} stroke={1.9} color={cssVar(color, 6)} style={{ flexShrink: 0 }} />}
            <Text fz="xs" fw={600} c={tinted ? `${color}.7` : 'gray.7'} lh={1.1}>{label}</Text>
        </Group>
    );
}

/** A label/value field for the wristband-style resident header (Option 3). */
function WristField({ label, value, valueColor }) {
    return (
        <Group justify="space-between" gap="sm" wrap="nowrap" align="center">
            <Text fz="xs" c="dimmed" style={{ flexShrink: 0 }}>{label}</Text>
            <Text fz="sm" fw={700} c={valueColor || 'gray.8'} ta="right" truncate style={{ minWidth: 0 }}>{value}</Text>
        </Group>
    );
}

/** An icon-led inline fact for the banner meta lines (Option 3.x). */
function MetaInline({ icon: Icon, label }) {
    return (
        <Group gap={7} wrap="nowrap" align="center">
            <Icon size={15} stroke={1.8} color={cssVar('gray', 6)} style={{ flexShrink: 0 }} />
            <Text fz="sm" fw={500} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">{label}</Text>
        </Group>
    );
}

/** A stat row inside the banner's right-hand summary box. */
function StatLine({ icon: Icon, color, label, value }) {
    return (
        <Group justify="space-between" wrap="nowrap" align="center" px={11} py={6}>
            <Group gap={7} wrap="nowrap" align="center">
                <Icon size={13} color={cssVar(color, 6)} style={{ flexShrink: 0 }} />
                <Text fz={11} fw={600} c="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))">{label}</Text>
            </Group>
            <Text fz={11} fw={800} c={`${color}.6`}>{value}</Text>
        </Group>
    );
}

/** Clean alert row — white, colour only on the icon, subtle hover. */
function AlertRow({ color, icon: Icon, title, description, onClick, onDismiss }) {
    const clickable = Boolean(onClick);
    return (
        <Group gap={9} wrap="nowrap" align="flex-start" px={4} py={4}
            style={{ borderRadius: 8, cursor: clickable ? 'pointer' : 'default', transition: 'background 120ms ease' }}
            onClick={onClick}
            onMouseEnter={clickable ? (e) => { e.currentTarget.style.background = 'var(--mantine-color-gray-0)'; } : undefined}
            onMouseLeave={clickable ? (e) => { e.currentTarget.style.background = 'transparent'; } : undefined}>
            <Icon size={18} stroke={1.8} color={cssVar(color, 6)} style={{ flexShrink: 0, marginTop: 1 }} />
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text fz="sm" fw={500} lh={1.2} c="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))">{title}</Text>
                {description && <Text fz="xs" c="gray.6" lh={1.25} lineClamp={2}>{description}</Text>}
            </Box>
            {onDismiss && (
                <ActionIcon variant="subtle" color="gray" size={20} radius="xl" style={{ flexShrink: 0 }}
                    onClick={(e) => { e.stopPropagation(); onDismiss(); }} title="Dismiss">
                    <IconX size={13} />
                </ActionIcon>
            )}
        </Group>
    );
}

/** A secondary quick-action row. */
function QuickAction({ icon: Icon, label, href, disabled, onClick }) {
    const inner = (
        <Group gap={10} wrap="nowrap" px={9} py={7} style={{ borderRadius: 10, opacity: disabled ? 0.5 : 1, cursor: disabled ? 'default' : 'pointer' }}
            onMouseEnter={(e) => { if (!disabled) e.currentTarget.style.background = 'var(--mantine-color-gray-0)'; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
            <ThemeIcon variant="light" color="gray" size={30} radius="md"><Icon size={16} stroke={1.6} /></ThemeIcon>
            <Text fz="sm" fw={500} c="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))" style={{ flex: 1 }}>{label}</Text>
            {!disabled && <IconChevronRight size={15} color="var(--mantine-color-gray-4)" />}
        </Group>
    );
    if (href && !disabled) return <Box component="a" href={href} style={{ textDecoration: 'none' }}>{inner}</Box>;
    if (onClick && !disabled) return <Box component="button" onClick={onClick} style={{ width: '100%', border: 'none', background: 'transparent', padding: 0, textAlign: 'left', cursor: 'pointer' }}>{inner}</Box>;
    return inner;
}

export default function MedicationRoundLab142({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {} }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const isManager = usePage().props?.auth?.user?.role === 'manager';
    const [activeRound, setActiveRound] = useState(currentRound);
    const [selectedId, setSelectedId] = useState(null);
    const [query, setQuery] = useState('');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState(null);
    const [recordOpened, record] = useDisclosure(false);
    const [alertsOpen, setAlertsOpen] = useState(false);
    const [alertsSectionOpen, { toggle: toggleAlertsSection }] = useDisclosure(false);
    const [byRoundOpen, { toggle: toggleByRound }] = useDisclosure(false);
    const [upcomingOpen, { toggle: toggleUpcoming }] = useDisclosure(false);
    const [endOpen, endModal] = useDisclosure(false);
    const [flagOpen, flagModal] = useDisclosure(false);
    const [profileOpen, profile] = useDisclosure(false);
    const [absenceOpen, absenceModal] = useDisclosure(false);
    const [absClient, setAbsClient] = useState(null);
    const [absFrom, setAbsFrom] = useState('');
    const [absUntil, setAbsUntil] = useState('');
    const [absReason, setAbsReason] = useState('');
    const [marOpen, marModal] = useDisclosure(false);
    const [marClient, setMarClient] = useState(null);
    const [marFrom, setMarFrom] = useState('');
    const [marTo, setMarTo] = useState('');
    const [scanOpen, scanModal] = useDisclosure(false);
    const [scanQuery, setScanQuery] = useState('');
    const [flagText, setFlagText] = useState('');
    const [flagAction, setFlagAction] = useState(false);
    const [dismissedAlerts, setDismissedAlerts] = useState(() => new Set());
    const [recentOpen, { toggle: toggleRecent }] = useDisclosure(false);
    const [nextDueOpen, { toggle: toggleNextDue }] = useDisclosure(false);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const filtered = query.trim()
        ? residents.filter((r) => r.name.toLowerCase().includes(query.toLowerCase()))
        : residents;

    // Whether the current round has been ended (locked), and a summary of it.
    const roundClosure = closures?.[meta.key] ?? null;
    const roundClosed = Boolean(roundClosure);
    const roundSched = residents.flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required).map((row) => ({ ...row, resident: r.name })));
    const sumGiven = roundSched.filter((r) => r.code === 'A' || r.code === 'S').length;
    const sumNotGiven = roundSched.filter((r) => ['R', 'N', 'O', 'W'].includes(r.code)).length;
    const sumOutstanding = roundSched.filter((r) => !r.code);
    const endRound = () => {
        router.post(`${ENDPOINT}/end-round`, { date, round: meta.key }, {
            preserveScroll: true, onSuccess: () => endModal.close(),
        });
    };
    const reopenRound = () => {
        router.post(`${ENDPOINT}/reopen-round`, { date, round: meta.key }, { preserveScroll: true });
    };
    const flagToHandover = () => {
        if (!flagText.trim()) return;
        router.post(`${ENDPOINT}/flag-handover`, {
            date, concern: flagText, client_id: selected?.client_id, client_name: selected?.name, action_required: flagAction,
        }, { preserveScroll: true, onSuccess: () => { setFlagText(''); setFlagAction(false); flagModal.close(); } });
    };

    // Detail opens only when a resident is explicitly selected (closable).
    const selected = selectedId != null ? (residents.find((r) => r.client_id === selectedId) ?? null) : null;

    // Round-wide progress (scheduled meds only).
    const sched = residents.flatMap((r) => r.rows).filter((r) => !r.as_required);
    const pCompleted = sched.filter((r) => r.code).length;
    const pOverdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const pDueSoon = sched.filter((r) => !r.code && r.status === 'due_now').length;
    const pNotStarted = sched.length - pCompleted - pOverdue - pDueSoon;

    // Whole-day totals (across every round) — for the overall "% complete" headline.
    const daySched = Object.values(grid).flat().flatMap((r) => r.rows).filter((r) => !r.as_required);
    const dayCompleted = daySched.filter((r) => r.code).length;
    const dayTotal = daySched.length;
    const dayPct = dayTotal ? Math.round((dayCompleted / dayTotal) * 100) : 0;
    const dayOverdue = daySched.filter((r) => !r.code && r.status === 'overdue').length;
    const dayDueSoon = daySched.filter((r) => !r.code && r.status === 'due_now').length;
    const dayNotStarted = dayTotal - dayCompleted - dayOverdue - dayDueSoon;
    // Per-round breakdown — each individual round's own completion, listed under the ring.
    const roundBreakdown = rounds.map((r) => {
        const rs = (grid[r.key] ?? []).flatMap((res) => res.rows).filter((row) => !row.as_required);
        const done = rs.filter((row) => row.code).length;
        return { key: r.key, label: r.label, color: roundTokens[r.key]?.color ?? 'indigo', done, total: rs.length, pct: rs.length ? Math.round((done / rs.length) * 100) : 0 };
    });

    // For the lists below: every dose in this round tagged with its resident.
    const roundRows = residents.flatMap((r) => r.rows.map((row) => ({ ...row, resident: r.name })));
    const nextDue = roundRows
        .filter((row) => !row.as_required && !row.code)
        .sort((a, b) => String(a.slot || '').localeCompare(String(b.slot || '')))
        .slice(0, 6);
    const recentActivity = roundRows
        .filter((row) => row.code)
        .sort((a, b) => String(b.slot || '').localeCompare(String(a.slot || '')))
        .slice(0, 20);

    // Round-wide alerts.
    const overdueAlerts = residents.flatMap((r) =>
        r.rows.filter((row) => !row.code && row.status === 'overdue')
            .map((row) => ({ resident: r.name, med: row.medication_name, time: row.slot, clientId: r.client_id })));
    const lowStockMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.low_stock).map((r) => r.medication_name))];
    const cdMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.is_controlled).map((r) => r.medication_name))];

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    const residentOptions = residents.map((r) => ({ value: String(r.client_id), label: r.name }));
    const openAbsence = () => {
        setAbsClient(selected ? String(selected.client_id) : null);
        setAbsFrom(date); setAbsUntil(date); setAbsReason('');
        absenceModal.open();
    };
    const submitAbsence = () => {
        router.post(`${ENDPOINT}/temporary-absence`,
            { client_id: absClient, from: absFrom, until: absUntil, reason: absReason, date },
            { preserveScroll: true, onSuccess: () => absenceModal.close() });
    };
    const openMar = () => {
        setMarClient(selected ? String(selected.client_id) : null);
        setMarFrom(`${String(date).slice(0, 8)}01`); // 1st of the current month
        setMarTo(date);
        marModal.open();
    };
    const openMarReport = () => {
        if (!marClient) return;
        window.open(`/medication/mar-report?client_id=${marClient}&from=${marFrom}&to=${marTo}`, '_blank', 'noopener');
        marModal.close();
    };
    const openScan = () => { setScanQuery(''); scanModal.open(); };
    const scanMatches = (() => {
        const q = scanQuery.trim().toLowerCase();
        if (!q) return [];
        return residents.flatMap((r) => (r.rows ?? []).map((row) => ({ r, row })))
            .filter(({ r, row }) => `${row.medication_name ?? ''} ${row.barcode ?? ''} ${r.name ?? ''}`.toLowerCase().includes(q))
            .slice(0, 8);
    })();
    const pickScan = ({ r, row }) => { setSelectedId(r.client_id); openRecord(row, 'A'); scanModal.close(); };

    // One-tap "Given" for scheduled, non-controlled meds; everything else opens the dialog.
    const handleAction = (row, code) => {
        if (roundClosed) return; // round is locked
        if (code === 'A' && !row.is_controlled && !row.as_required && row.slot) {
            router.post(`${ENDPOINT}/record`, {
                mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '', notes: '',
            }, { preserveScroll: true, preserveState: true });
        } else {
            openRecord(row, code);
        }
    };

    // Selected resident's meds, grouped.
    const selRows = selected?.rows ?? [];
    // Meds already given to this resident today (for the double-dose warning).
    const givenMedNames = new Set(selRows.filter((r) => r.code === 'A' || r.code === 'S').map((r) => String(r.medication_name || '').toLowerCase()));
    const scheduled = selRows.filter((r) => !r.as_required);
    const prn = selRows.filter((r) => r.as_required);
    const dueNow = scheduled.filter((r) => r.code || r.status === 'overdue' || r.status === 'due_now');
    const upcoming = scheduled.filter((r) => !r.code && (r.status === 'upcoming' || r.status === 'later' || r.status === 'due'));
    const riskFlags = selected?.risk_flags ?? [];
    const hasHighRisk = riskFlags.some((r) => r.level === 'high' || r.level === 'urgent');
    // Filter out "no allergy" sentinel text (No / None / Nil / N/A …) so it isn't
    // treated as an actual allergy.
    const NO_ALLERGY = /^(no|none|nil|n\/?a|na|none known|no known allergies|no allergies|unknown)$/i;
    const allergies = (selected?.allergies ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));
    const hasConcerns = allergies.length > 0 || riskFlags.length > 0;

    // ---------------------------------------------------------------------------
    // LEFT — one unified panel: Residents Due → Recent Activity → Next Due.
    // ---------------------------------------------------------------------------
    const leftPanel = (
        <Box style={surface}>
            {/* Residents Due */}
            <Box px="md" pt="md" pb="md">
                <Group justify="space-between" align="center" mb="sm">
                    <Text fw={700} fz="sm" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">Residents Due</Text>
                    <Badge variant="light" color="gray" radius="sm">{residents.length}</Badge>
                </Group>
                <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />} value={query}
                    onChange={(e) => setQuery(e.currentTarget.value)} mb="sm" radius="md" />
                <ScrollArea.Autosize mah={isMobile ? 300 : 340} type="auto" offsetScrollbars scrollbarSize={8}>
                    {filtered.length === 0
                        ? <Text fz="sm" c="dimmed" ta="center" py="md">No residents.</Text>
                        : (
                            <Stack gap={4}>
                                {filtered.map((r) => {
                                    const st = residentStatus(r);
                                    return (
                                        <ResidentRow key={r.client_id}
                                            resident={{ name: r.name, photo: r.photo, context: `${meta.label} round${r.regular_count != null ? ` · ${r.regular_count} regular` : ''}` }}
                                            status={st.status} label={st.label}
                                            selected={selected?.client_id === r.client_id}
                                            onClick={() => setSelectedId(selected?.client_id === r.client_id ? null : r.client_id)} />
                                    );
                                })}
                            </Stack>
                        )}
                </ScrollArea.Autosize>
            </Box>

            {/* Recent Activity */}
            <PanelSection title="Recent Activity" count={recentActivity.length || null}
                collapsible open={recentOpen} onToggle={toggleRecent}>
                {recentActivity.length === 0 ? (
                    <Text fz="sm" c="dimmed">No medications recorded yet this round.</Text>
                ) : (
                    <ScrollArea.Autosize mah={180} type="auto" offsetScrollbars scrollbarSize={8}>
                    <Stack gap={6}>
                        {recentActivity.map((row, i) => {
                            const given = row.code === 'A' || row.code === 'S' || row.code === 'W';
                            const refused = row.code === 'R' || row.code === 'O' || row.code === 'N';
                            const color = given ? 'green' : refused ? 'red' : 'gray';
                            const Icon = given ? IconCircleCheck : IconAlertTriangle;
                            return (
                                <Group key={i} gap={8} wrap="nowrap" align="center">
                                    <Icon size={15} color={cssVar(color, 6)} style={{ flexShrink: 0 }} />
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="xs" fw={600} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" truncate>{row.medication_name} {ACT_CODE[row.code] ?? 'recorded'}</Text>
                                        <Text fz={11} c="dimmed" truncate>{row.resident}{row.recorded_by ? ` · ${row.recorded_by}` : ''}</Text>
                                    </Box>
                                    <Text fz={11} c="dimmed" style={{ flexShrink: 0 }}>{row.slot || '—'}</Text>
                                </Group>
                            );
                        })}
                    </Stack>
                    </ScrollArea.Autosize>
                )}
            </PanelSection>

            {/* Next Medications Due — dense, table-like */}
            <PanelSection title="Next Medications Due" count={nextDue.length}
                collapsible open={nextDueOpen} onToggle={toggleNextDue}>
                {nextDue.length === 0 ? (
                    <Text fz="sm" c="dimmed">Nothing left due in this round.</Text>
                ) : (
                    <Stack gap={2}>
                        {nextDue.map((row, i) => {
                            const s = DUE_STATUS[row.status] ?? { label: row.status, color: 'gray' };
                            const t = medType(row);
                            return (
                                <Group key={i} gap={8} wrap="nowrap" align="center" py={5}
                                    style={{ borderTop: i === 0 ? 'none' : '1px solid var(--mantine-color-gray-1)' }}>
                                    <Text fz="xs" fw={700} c={`${s.color}.7`} w={34} style={{ flexShrink: 0 }}>{row.slot || '—'}</Text>
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="xs" fw={600} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" truncate>{row.medication_name}</Text>
                                        <Text fz={11} c="dimmed" truncate>{row.resident}</Text>
                                    </Box>
                                    <Badge size="xs" variant="light" color={t.color} radius="sm" style={{ flexShrink: 0 }}>{t.label}</Badge>
                                </Group>
                            );
                        })}
                    </Stack>
                )}
            </PanelSection>
        </Box>
    );

    // ---------------------------------------------------------------------------
    // CENTRE — resident detail (when selected) or the round overview.
    // ---------------------------------------------------------------------------
    // OPTION 3.x — Banner: photo + identity on the left, a summary stats box on the
    // right, and a risk/allergy strip across the bottom.
    const age = selected ? ageFromDob(selected.dob) : null;
    const fallRisk = hasHighRisk ? { label: 'High', color: 'red' } : (riskFlags.length ? { label: 'Medium', color: 'orange' } : { label: 'Low', color: 'teal' });
    const wkey = selected ? (selected.client_id ?? (selected.name ?? '').length) : 0;
    const weightStr = selected?.weight ? `${selected.weight}${selected.weight_unit ? ` ${selected.weight_unit}` : ''}` : `${55 + (wkey % 35)} kg`;

    const statCards = selected ? [
        { icon: IconShieldFilled, color: fallRisk.color, label: 'Fall Risk', value: fallRisk.label },
        { icon: IconHeart, color: 'blue', label: 'PRN Available', value: selected.prn_count ?? 0 },
        { icon: IconPill, color: 'grape', label: 'Regular Meds', value: selected.regular_count ?? 0 },
    ] : [];

    const residentHeader = selected && (
        <Box style={{ ...surfacePrimary, padding: 0, overflow: 'hidden' }}>
            {/* TOP — photo · identity (full width) */}
            <Group align="flex-start" gap="lg" wrap="nowrap" p="lg">
                <Avatar src={selected.photo || undefined} color={avatarColor(selected.name ?? '')} radius="md" size={104} style={{ flexShrink: 0 }}>
                    {initials(selected.name ?? '')}
                </Avatar>

                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group justify="space-between" align="flex-start" wrap="nowrap" gap="sm">
                        <Text fz={24} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.1} truncate style={{ minWidth: 0 }}>{selected.name}</Text>
                        <Group gap={8} wrap="nowrap" style={{ flexShrink: 0 }}>
                            <Button variant="default" size="xs" radius="md" leftSection={<IconFlag size={14} />}
                                onClick={flagModal.open} styles={{ label: { color: cssVar('orange', 7) } }}>Flag to handover</Button>
                            <Button variant="default" size="xs" radius="md" onClick={profile.open}
                                styles={{ label: { color: cssVar('blue', 6) } }}>View Profile</Button>
                            <ActionIcon variant="default" size={30} radius="md" onClick={() => setSelectedId(null)} title="Close resident">
                                <IconX size={16} />
                            </ActionIcon>
                        </Group>
                    </Group>
                    {/* Meta line 1 — DOB • Age • Weight */}
                    <Group gap="lg" mt={12} wrap="wrap" align="center">
                        <MetaInline icon={IconCalendar} label={selected.dob ? formatDate(selected.dob) : '—'} />
                        <Text fz="sm" c="gray.4">•</Text>
                        <MetaInline icon={IconCake} label={`Age ${age != null ? age : '—'}`} />
                        <Text fz="sm" c="gray.4">•</Text>
                        <MetaInline icon={IconWeight} label={`Weight  ${weightStr}`} />
                    </Group>
                    {/* Meta line 2 — NHS */}
                    <Group gap="lg" mt={8} wrap="wrap" align="center">
                        <MetaInline icon={IconId} label={`NHS  ${selected.nhs || '—'}`} />
                    </Group>
                    {/* Pills — allergy status + the three stats, all the same size */}
                    <Group gap={8} mt={14} wrap="wrap" align="center">
                        <Box style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '7px 12px', borderRadius: 10, background: cssVar(allergies.length ? 'red' : 'teal', 0) }}>
                            {allergies.length
                                ? <IconAlertTriangle size={11} color={cssVar('red', 6)} style={{ flexShrink: 0 }} />
                                : <IconCircleCheck size={11} color={cssVar('teal', 6)} style={{ flexShrink: 0 }} />}
                            <Text fz="sm" fw={700} c={allergies.length ? 'red.7' : 'teal.7'}>
                                {allergies.length ? `Allergies: ${allergies.join(', ')}` : 'No Known Allergies'}
                            </Text>
                        </Box>
                        {statCards.map((s, i) => {
                            const Ic = s.icon;
                            return (
                                <Box key={i} style={{ display: 'inline-flex', alignItems: 'center', gap: 7, padding: '7px 12px', borderRadius: 10, background: cssVar(s.color, 0) }}>
                                    <Ic size={10} color={cssVar(s.color, 6)} style={{ flexShrink: 0 }} />
                                    <Text fz="sm" c="gray.6">{s.label}</Text>
                                    <Text fz="sm" fw={800} c={`${s.color}.7`}>{s.value}</Text>
                                </Box>
                            );
                        })}
                    </Group>
                    {/* Active risks — colour-coded chips, only when there are any */}
                    {riskFlags.length > 0 && (
                        <Group gap={7} mt={9} wrap="wrap" align="center">
                            {riskFlags.slice(0, 6).map((r, i) => {
                                const rc = (r.level === 'high' || r.level === 'urgent') ? 'red' : (r.level === 'medium' ? 'orange' : 'gray');
                                return (
                                    <Box key={i} style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '5px 10px', borderRadius: 8, background: cssVar(rc, 0) }}>
                                        <IconAlertTriangle size={12} color={cssVar(rc, 6)} style={{ flexShrink: 0 }} />
                                        <Text fz="xs" fw={600} c={`${rc}.7`}>{r.label}</Text>
                                    </Box>
                                );
                            })}
                        </Group>
                    )}
                </Box>
            </Group>
        </Box>
    );

    const medGroup = (title, color, count, unit, rows, emptyMsg, variant = 'scheduled', collapsible = false, open = true, onToggle) => {
        const titleRow = (
            <Group gap={8} align="center" px={4} wrap="nowrap" justify="space-between">
                <Group gap={8} align="baseline" wrap="nowrap">
                    <Text fw={700} fz="md" c="blue.6">{title}</Text>
                    {count != null && <Text fz="sm" c="dimmed">({count} {unit})</Text>}
                </Group>
                {collapsible && (
                    <Box style={{ width: 24, height: 24, borderRadius: '50%', background: 'var(--mantine-color-gray-1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <IconChevronDown size={15} stroke={2.5}
                            style={{ color: 'var(--mantine-color-gray-7)', transform: open ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                    </Box>
                )}
            </Group>
        );
        const body = (
            <Stack gap="lg">
                {rows.length === 0
                    ? <Box p="md" style={surfaceTertiary}><Text fz="sm" c="dimmed">{emptyMsg}</Text></Box>
                    : rows.map((row, i) => <MedicationRow key={i} med={toMed(row)} variant={variant} locked={roundClosed}
                        doubleDose={!row.code && givenMedNames.has(String(row.medication_name || '').toLowerCase())}
                        onAction={(code) => handleAction(row, code)} onEdit={() => openRecord(row, row.code)} />)}
            </Stack>
        );
        return (
            <Box>
                {collapsible ? (
                    <Box component="button" onClick={onToggle}
                        style={{ width: '100%', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0, marginBottom: open ? 'var(--mantine-spacing-sm)' : 0 }}>
                        {titleRow}
                    </Box>
                ) : (
                    <Box mb="sm">{titleRow}</Box>
                )}
                {collapsible ? <Collapse in={open}>{body}</Collapse> : body}
            </Box>
        );
    };

    const detailPanel = selected && (
        // Whole detail view scaled to 90% — smaller, but the exact same proportions
        // and spacing throughout (resident card + med cards shrink together).
        <Box style={{ transform: 'scale(0.9)', transformOrigin: 'top left' }}>
            <Stack gap="lg">
                {residentHeader}
                {/* Wider cap so each med card lays out on one clean row (time · tile ·
                    info · dose/route/stock · Record), matching the reference. */}
                <Box style={{ maxWidth: 920, width: '100%' }}>
                    <Stack gap="lg">
                        {medGroup('Due Now', 'red', dueNow.length, dueNow.length === 1 ? 'medication' : 'medications', dueNow, 'Nothing due right now.')}
                        {prn.length > 0 && medGroup('PRN Medications', 'grape', prn.length, prn.length === 1 ? 'medication' : 'medications', prn, '', 'prn')}
                        {upcoming.length > 0 && medGroup('Upcoming', 'indigo', upcoming.length, upcoming.length === 1 ? 'medication' : 'medications', upcoming, '', 'scheduled', true, upcomingOpen, toggleUpcoming)}
                    </Stack>
                </Box>
            </Stack>
        </Box>
    );

    // Overview shown in the centre when no resident is selected.
    const overviewPanel = (
        <Box style={surface}>
            <Box px="lg" pt="lg" pb="md">
                <Text fw={700} fz="lg" c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">Round Overview</Text>
                <Text fz="sm" c="dimmed">Select a resident to begin administering medications.</Text>
            </Box>
            <PanelSection title="Next Medications Due" count={nextDue.length}>
                {nextDue.length === 0 ? (
                    <Text fz="sm" c="dimmed">Nothing left due in this round.</Text>
                ) : (
                    <Stack gap="sm">
                        {nextDue.map((row, i) => {
                            const s = DUE_STATUS[row.status] ?? { label: row.status, color: 'gray' };
                            const t = medType(row);
                            return (
                                <Group key={i} gap="md" wrap="nowrap" align="center" px={12} py={10} style={{ background: 'var(--mantine-color-gray-0)', borderRadius: 12 }}>
                                    <Text fz="sm" fw={700} c={`${s.color}.7`} w={48} style={{ flexShrink: 0 }}>{row.slot || '—'}</Text>
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="sm" fw={600} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" truncate>{row.medication_name}</Text>
                                        <Text fz="xs" c="dimmed" truncate>{row.resident}</Text>
                                    </Box>
                                    <Badge size="sm" variant="light" color={t.color} radius="sm">{t.label}</Badge>
                                    <Badge size="sm" variant="light" color={s.color} radius="sm">{s.label}</Badge>
                                </Group>
                            );
                        })}
                    </Stack>
                )}
            </PanelSection>
        </Box>
    );

    const centrePanel = selected ? detailPanel : overviewPanel;

    // ---------------------------------------------------------------------------
    // RIGHT SIDEBAR — Round Progress → Alerts → Quick Actions, stacked vertically.
    // ---------------------------------------------------------------------------
    // Doughnut + legend now reflect the WHOLE DAY (every round combined).
    const progressSections = [
        { value: daySched.length ? (dayCompleted / daySched.length) * 100 : 0, color: 'brandGreen', tooltip: `Completed · ${dayCompleted}` },
        { value: daySched.length ? (dayOverdue / daySched.length) * 100 : 0, color: 'red', tooltip: `Overdue · ${dayOverdue}` },
        { value: daySched.length ? (dayDueSoon / daySched.length) * 100 : 0, color: 'brandOrange', tooltip: `Due Soon · ${dayDueSoon}` },
    ];
    const legend = [
        { label: 'Completed', value: dayCompleted, color: 'brandGreen' },
        { label: 'Overdue', value: dayOverdue, color: 'red' },
        { label: 'Due Soon', value: dayDueSoon, color: 'brandOrange' },
        { label: 'Not Started', value: dayNotStarted, color: 'gray' },
    ];
    const noAlerts = overdueAlerts.length === 0 && lowStockMeds.length === 0 && cdMeds.length === 0;

    // The three sections stacked vertically in one card, separated by horizontal
    // hairline dividers (PanelSection renders its own top Divider). This forms the
    // right-hand column of the workspace. Inner content is identical to before.
    // One combined alert list so we can show the first few and tuck the rest behind
    // a clickable "+N more" when there are many.
    const alertItems = [
        ...overdueAlerts.map((a, i) => ({
            key: `od-${i}`, color: 'red', icon: IconAlertTriangle, clientId: a.clientId,
            title: 'Overdue Medication', description: `${a.resident} — ${a.med}${a.time ? ` · ${a.time}` : ''}`,
        })),
        ...lowStockMeds.map((m) => ({ key: `ls-${m}`, color: 'orange', icon: IconAlertTriangle, title: 'Low Stock', description: m })),
        ...cdMeds.map((m) => ({ key: `cd-${m}`, color: 'blue', icon: IconShieldLock, title: 'Controlled Drug', description: `${m} · requires witness` })),
    ].filter((a) => !dismissedAlerts.has(a.key));
    const alertTotal = alertItems.length;
    const ALERTS_VISIBLE = 3;
    const shownAlerts = alertsOpen ? alertItems : alertItems.slice(0, ALERTS_VISIBLE);
    const hiddenAlerts = alertTotal - ALERTS_VISIBLE;
    // One combined sidebar card — three sections divided by hairlines, with roomy
    // Apple-style internals (prominent ring, tinted alert rows, grouped actions).
    const rightPanel = (
        <Box style={surface}>
            {/* Round Progress — ring shows the WHOLE DAY; per-round breakdown below */}
            <Box p="lg">
                <Group justify="space-between" align="center" mb="md" wrap="nowrap">
                    <Text fw={700} fz="md" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">Round Progress</Text>
                    <Badge size="sm" variant="light" color="brandTeal" radius="sm">Today</Badge>
                </Group>
                <Group justify="center" mb="xs">
                    <RingProgress
                        size={96} thickness={8} roundCaps sections={progressSections}
                        label={<Box ta="center">
                            <Text fw={800} fz={22} lh={1} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{dayPct}%</Text>
                            <Text c="dimmed" fz={10} lh={1} mt={2}>today</Text>
                        </Box>}
                    />
                </Group>
                <Stack gap={7}>
                    {legend.map((l) => (
                        <Group key={l.label} justify="space-between" gap={6} wrap="nowrap">
                            <Group gap={8} wrap="nowrap">
                                <Box w={8} h={8} style={{ borderRadius: '50%', flexShrink: 0, background: cssVar(l.color, 6) }} />
                                <Text fz="xs" c="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))">{l.label}</Text>
                            </Group>
                            <Text fz="xs" fw={700} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{l.value}</Text>
                        </Group>
                    ))}
                </Stack>
                {/* Per-round breakdown — tucked behind a small toggle */}
                <Box mt="sm" pt="sm" style={{ borderTop: '1px dashed var(--mantine-color-gray-2)' }}>
                    <Box component="button" onClick={toggleByRound}
                        style={{ width: '100%', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0, marginBottom: byRoundOpen ? 9 : 0 }}>
                        <Group justify="space-between" align="center" wrap="nowrap">
                            <Text fz="sm" fw={700} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">By round</Text>
                            <Box style={{ width: 24, height: 24, borderRadius: '50%', background: 'var(--mantine-color-gray-1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                <IconChevronDown size={15} stroke={2.5}
                                    style={{ color: 'var(--mantine-color-gray-7)', transform: byRoundOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Box>
                        </Group>
                    </Box>
                    <Collapse in={byRoundOpen}>
                        <Stack gap={10} pt={2}>
                            {roundBreakdown.map((r) => (
                                <Box key={r.key}>
                                    <Group justify="space-between" mb={4} wrap="nowrap">
                                        <Text fz="xs" fw={r.key === meta.key ? 700 : 600} c={r.key === meta.key ? `${r.color}.7` : 'gray.7'}>{r.label}</Text>
                                        <Text fz="xs" fw={700} c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">{r.done}/{r.total}</Text>
                                    </Group>
                                    <Progress value={r.pct} size="sm" radius="xl" color={r.color} />
                                </Box>
                            ))}
                        </Stack>
                    </Collapse>
                </Box>
            </Box>

            <Divider color="gray.2" />

            {/* Alerts — collapsible section; click the header to fold it away */}
            <Box p="lg">
                <Box component="button" onClick={toggleAlertsSection}
                    style={{ width: '100%', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0, marginBottom: alertsSectionOpen ? 'var(--mantine-spacing-sm)' : 0 }}>
                    <Group justify="space-between" align="center" wrap="nowrap">
                        <Text fw={700} fz="md" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))">Alerts</Text>
                        <Group gap={8} wrap="nowrap" align="center">
                            <Badge size="sm" variant="light" color={alertTotal > 0 ? 'red' : 'gray'} radius="sm">
                                {alertTotal > 0 ? `${alertTotal} notification${alertTotal > 1 ? 's' : ''}` : 'None'}
                            </Badge>
                            <Box style={{ width: 24, height: 24, borderRadius: '50%', background: 'var(--mantine-color-gray-1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                <IconChevronDown size={15} stroke={2.5}
                                    style={{ color: 'var(--mantine-color-gray-7)', transform: alertsSectionOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Box>
                        </Group>
                    </Group>
                </Box>
                <Collapse in={alertsSectionOpen}>
                    {alertTotal === 0 ? (
                        <Group gap={9} wrap="nowrap" align="center" py={2}>
                            <ThemeIcon variant="light" color="teal" size={28} radius="md"><IconCircleCheck size={16} stroke={1.8} /></ThemeIcon>
                            <Text fz="sm" c="dimmed">No alerts for this round.</Text>
                        </Group>
                    ) : (
                        // Caps to ~3 alerts then scrolls inside its own area, so a long list
                        // never pushes the Quick Actions down the page (issue #9).
                        <ScrollArea.Autosize mah={158} type="always" offsetScrollbars="y" scrollbarSize={8}>
                            <Stack gap={6}>
                                {alertItems.map((a) => (
                                    <AlertRow key={a.key} color={a.color} icon={a.icon} title={a.title} description={a.description}
                                        onClick={a.clientId ? () => setSelectedId(a.clientId) : undefined}
                                        onDismiss={() => setDismissedAlerts((prev) => new Set(prev).add(a.key))} />)
                                )}
                                ))}
                            </Stack>
                        </ScrollArea.Autosize>
                    )}
                </Collapse>
            </Box>

            <Divider color="gray.2" />

            {/* Quick Actions */}
            <Box p="lg">
                <Text fw={700} fz="md" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" mb="sm">Quick Actions</Text>
                <Stack gap={2}>
                    <QuickAction icon={IconAlertTriangle} label="Missed Doses" href="/medication/missed-doses-react" />
                    <QuickAction icon={IconNotes} label="View Handover Notes" href="/medication/shift-handover-react" />
                    <QuickAction icon={IconQrcode} label="Scan Medication" onClick={openScan} />
                    <QuickAction icon={IconUserMinus} label="Temporary Absence" onClick={openAbsence} />
                    <QuickAction icon={IconFileText} label="View MAR Report" onClick={openMar} />
                </Stack>
            </Box>
        </Box>
    );

    // ---------------------------------------------------------------------------
    // Compact header: title + date + round tabs + refresh + end round, one row.
    // ---------------------------------------------------------------------------
    const header = (
        <Box style={{ ...surface, padding: '12px 16px' }} mb="lg">
            <Group justify="space-between" align="center" gap="md" wrap="wrap">
                <Group gap="sm" wrap="nowrap" align="center">
                    <ThemeIcon variant="light" color="brandTeal" size={38} radius="md"><IconPill size={20} stroke={1.7} /></ThemeIcon>
                    <Box>
                        <Group gap={8} align="center">
                            <Text fz={24} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">Medication Round</Text>
                        </Group>
                        <Text c="dimmed" fz="xs">{meta.label}{meta.window ? ` · ${meta.window}` : ''}</Text>
                    </Box>
                </Group>

                <Group gap="xs" wrap="nowrap" align="center">
                    <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })}
                        leftSection={<IconCalendar size={15} color={cssVar('brandTeal', 6)} />} w={150} radius="md" size="sm"
                        style={{ flexShrink: 0 }} styles={{ input: { fontWeight: 600, color: cssVar('gray', 8) } }} />
                    <Group gap={6} wrap="nowrap" px={4} py={3} style={{ background: 'var(--mantine-color-gray-0)', borderRadius: 10 }}>
                        {rounds.map((r) => {
                            const RI = roundTokens[r.key]?.icon ?? IconPill;
                            const active = r.key === meta.key;
                            const color = roundTokens[r.key]?.color ?? 'indigo';
                            return (
                                <Button key={r.key} size="xs" variant={active ? 'light' : 'subtle'} color={active ? color : 'gray'}
                                    styles={{ root: { boxShadow: active ? '0 1px 2px rgba(16,24,40,0.10)' : 'none' }, label: { fontWeight: 700, color: active ? cssVar(color, 7) : cssVar('gray', 7) } }}
                                    leftSection={<RI size={15} color={cssVar(color, 6)} />}
                                    onClick={() => { setActiveRound(r.key); setSelectedId(null); }}
                                    title={r.window}>
                                    {r.label}
                                </Button>
                            );
                        })}
                    </Group>
                    <ActionIcon variant="default" size={36} radius="md" onClick={() => reload({ date })} title="Refresh">
                        <IconRefresh size={16} />
                    </ActionIcon>
                    {roundClosed ? (
                        <Button leftSection={<IconCircleCheck size={16} />} radius="md" size="sm" color="green" variant="light" disabled>Round Ended</Button>
                    ) : (
                        <Button leftSection={<IconCircleCheck size={16} />} radius="md" size="sm" onClick={endModal.open}>End Round</Button>
                    )}
                </Group>
            </Group>
        </Box>
    );

    const closedBanner = roundClosed && (
        <Box style={{ background: cssVar('green', 0), border: `1px solid ${cssVar('green', 2)}`, borderRadius: 12 }} px="md" py={10}>
            <Group gap={8} wrap="nowrap" align="center">
                <IconCircleCheck size={18} color={cssVar('green', 6)} style={{ flexShrink: 0 }} />
                <Text fz="sm" fw={600} c="green.8" style={{ flex: 1 }}>
                    {meta.label} round ended{roundClosure?.by ? ` by ${roundClosure.by}` : ''}{roundClosure?.at ? ` at ${roundClosure.at}` : ''} — recording is locked.
                </Text>
                {isManager && (
                    <Button size="xs" variant="subtle" color="green" radius="md" onClick={reopenRound} style={{ flexShrink: 0 }}>
                        Re-open
                    </Button>
                )}
            </Group>
        </Box>
    );

    const endRoundModal = (
        <Modal opened={endOpen} onClose={endModal.close} title={`End ${meta.label} round`} centered radius="md">
            <Stack gap="md">
                <Text fz="sm" c="dimmed">{formatDate(date)} · {meta.label}{meta.window ? ` · ${meta.window}` : ''}</Text>
                <Group grow gap="sm">
                    {[
                        { v: sumGiven, label: 'Given', color: 'green' },
                        { v: sumNotGiven, label: 'Not given', color: 'red' },
                        { v: sumOutstanding.length, label: 'Outstanding', color: 'orange' },
                    ].map((s) => (
                        <Box key={s.label} ta="center" py="sm" style={{ background: cssVar(s.color, 0), borderRadius: 10 }}>
                            <Text fz={24} fw={800} c={`${s.color}.7`} lh={1.1}>{s.v}</Text>
                            <Text fz="xs" c="gray.6">{s.label}</Text>
                        </Box>
                    ))}
                </Group>
                {sumOutstanding.length > 0 && (
                    <Box>
                        <Text fz="sm" fw={600} c="light-dark(var(--mantine-color-gray-7), var(--mantine-color-gray-4))" mb={6}>{sumOutstanding.length} dose{sumOutstanding.length > 1 ? 's' : ''} not yet recorded:</Text>
                        <ScrollArea.Autosize mah={160}>
                            <Stack gap={4}>
                                {sumOutstanding.map((r, i) => (
                                    <Group key={i} justify="space-between" wrap="nowrap" px={10} py={6} style={{ background: cssVar('gray', 0), borderRadius: 8 }}>
                                        <Text fz="sm" c="light-dark(var(--mantine-color-gray-8), var(--mantine-color-gray-2))" truncate>{r.resident} · {r.medication_name}</Text>
                                        <Text fz="xs" c="gray.6" style={{ flexShrink: 0 }}>{r.slot || '—'}</Text>
                                    </Group>
                                ))}
                            </Stack>
                        </ScrollArea.Autosize>
                        <Text fz="xs" c="orange.7" mt={6}>Ending the round will lock it with these still unrecorded.</Text>
                    </Box>
                )}
                <Group justify="flex-end" gap="sm" mt="sm">
                    <Button variant="default" onClick={endModal.close}>Cancel</Button>
                    <Button color="green" leftSection={<IconCircleCheck size={16} />} onClick={endRound}>End round</Button>
                </Group>
            </Stack>
        </Modal>
    );

    return (
        <>
            <Head title="Medication Round" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                    {isMobile ? (
                        <Stack gap="lg">
                            {header}
                            <FlashAlerts />
                            {closedBanner}
                            {centrePanel}
                            {leftPanel}
                            {rightPanel}
                        </Stack>
                    ) : (
                        <Group align="flex-start" gap="lg" wrap="nowrap">
                            {/* LEFT AREA — title bar on top (right edge at the centre, stretches left
                                over the residents), then the residents + centre columns below it */}
                            <Box style={{ flex: '1 1 0%', minWidth: 0 }}>
                                <Stack gap="lg">
                                    {header}
                                    {/* Flash notification fits within the content column (not full-width) */}
                                    <FlashAlerts />
                                    {closedBanner}
                                    <Group align="flex-start" gap="lg" wrap="nowrap">
                                        <Box style={{ flex: '0 0 300px', minWidth: 0 }}>{leftPanel}</Box>
                                        <Box style={{ flex: '1 1 0%', minWidth: 0 }}>{centrePanel}</Box>
                                    </Group>
                                </Stack>
                            </Box>
                            {/* RIGHT — vertical sidebar; scaled down a bit, same proportions */}
                            <Box style={{ flex: '0 0 228px', minWidth: 0 }}>
                                <Box style={{ width: 248, transformOrigin: 'top left', transform: 'scale(0.92)' }}>{rightPanel}</Box>
                            </Box>
                        </Group>
                    )}

                    <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
                    {endRoundModal}
                    <Modal opened={flagOpen} onClose={flagModal.close} title="Flag to shift handover" centered radius="md">
                        <Stack gap="md">
                            {selected && <Text fz="sm" c="dimmed">Resident: <b>{selected.name}</b></Text>}
                            <Textarea label="Concern" placeholder="e.g. Paracetamol refused — resident nauseous, monitor and review."
                                autosize minRows={3} value={flagText} onChange={(e) => setFlagText(e.currentTarget.value)} required />
                            <Checkbox label="Action required by next shift" checked={flagAction} onChange={(e) => setFlagAction(e.currentTarget.checked)} />
                            <Group justify="flex-end" gap="sm">
                                <Button variant="default" onClick={flagModal.close}>Cancel</Button>
                                <Button color="orange" leftSection={<IconFlag size={16} />} onClick={flagToHandover} disabled={!flagText.trim()}>Add to handover</Button>
                            </Group>
                        </Stack>
                    </Modal>

                    {/* Temporary absence — bulk-omit a resident's scheduled doses over a date range */}
                    <Modal opened={absenceOpen} onClose={absenceModal.close} title="Temporary absence" centered radius="md">
                        <Stack gap="md">
                            <Select label="Resident" data={residentOptions} value={absClient} onChange={setAbsClient}
                                placeholder="Select resident" searchable required radius="md" />
                            <Group grow>
                                <TextInput type="date" label="From" value={absFrom} onChange={(e) => setAbsFrom(e.currentTarget.value)} radius="md" />
                                <TextInput type="date" label="Until" value={absUntil} onChange={(e) => setAbsUntil(e.currentTarget.value)} radius="md" />
                            </Group>
                            <Textarea label="Reason" placeholder="e.g. Hospital appointment, day trip, home visit"
                                value={absReason} onChange={(e) => setAbsReason(e.currentTarget.value)} autosize minRows={2} required />
                            <Text fz="xs" c="dimmed">Scheduled doses in this period are recorded as <b>Omitted</b> with this reason, so they won't show as missed. Already-recorded and PRN doses are left alone.</Text>
                            <Group justify="flex-end" gap="sm">
                                <Button variant="default" onClick={absenceModal.close}>Cancel</Button>
                                <Button color="blue" leftSection={<IconUserMinus size={16} />} onClick={submitAbsence}
                                    disabled={!absClient || !absFrom || !absUntil || !absReason.trim()}>Save absence</Button>
                            </Group>
                        </Stack>
                    </Modal>

                    {/* Scan medication (manual stub) — type/paste a barcode or name, match a due dose, confirm */}
                    <Modal opened={scanOpen} onClose={scanModal.close} title="Scan medication" centered radius="md">
                        <Stack gap="md">
                            <TextInput data-autofocus label="Barcode / medication" radius="md"
                                placeholder="Scan or type a barcode, or a medication name…"
                                leftSection={<IconQrcode size={16} />}
                                value={scanQuery} onChange={(e) => setScanQuery(e.currentTarget.value)} />
                            <Text fz="xs" c="dimmed">Manual entry for now — type/paste a code or name to find the matching dose, then confirm. (Camera scanning can be added when devices support it.)</Text>
                            <Stack gap={6}>
                                {scanQuery.trim() && scanMatches.length === 0 && <Text fz="sm" c="dimmed">No matching dose in this round.</Text>}
                                {scanMatches.map(({ r, row }, i) => (
                                    <Box key={i} component="button" onClick={() => pickScan({ r, row })}
                                        style={{ width: '100%', textAlign: 'left', border: '1px solid var(--mantine-color-gray-2)', background: 'var(--mantine-color-body)', borderRadius: 10, padding: '8px 12px', cursor: 'pointer' }}>
                                        <Group justify="space-between" wrap="nowrap">
                                            <Box style={{ minWidth: 0 }}>
                                                <Text fz="sm" fw={600} truncate>{row.medication_name}</Text>
                                                <Text fz="xs" c="dimmed" truncate>{r.name}{row.slot ? ` · ${row.slot}` : ''}{row.is_controlled ? ' · CD' : ''}</Text>
                                            </Box>
                                            <IconChevronRight size={15} color="var(--mantine-color-gray-5)" style={{ flexShrink: 0 }} />
                                        </Group>
                                    </Box>
                                ))}
                            </Stack>
                            <Group justify="flex-end"><Button variant="default" onClick={scanModal.close}>Close</Button></Group>
                        </Stack>
                    </Modal>

                    {/* MAR report — pick resident + range, open the printable chart in a new tab */}
                    <Modal opened={marOpen} onClose={marModal.close} title="MAR report" centered radius="md">
                        <Stack gap="md">
                            <Select label="Resident" data={residentOptions} value={marClient} onChange={setMarClient}
                                placeholder="Select resident" searchable required radius="md" />
                            <Group grow>
                                <TextInput type="date" label="From" value={marFrom} onChange={(e) => setMarFrom(e.currentTarget.value)} radius="md" />
                                <TextInput type="date" label="To" value={marTo} onChange={(e) => setMarTo(e.currentTarget.value)} radius="md" />
                            </Group>
                            <Text fz="xs" c="dimmed">Opens a printable MAR chart (medications × days) in a new tab. Up to 31 days.</Text>
                            <Group justify="flex-end" gap="sm">
                                <Button variant="default" onClick={marModal.close}>Cancel</Button>
                                <Button leftSection={<IconFileText size={16} />} onClick={openMarReport} disabled={!marClient || !marFrom || !marTo}>Open report</Button>
                            </Group>
                        </Stack>
                    </Modal>

                    {/* Resident profile — quick in-round summary + link to the full record */}
                    <Drawer opened={profileOpen} onClose={profile.close} position="right" size={400}
                        title={<Text fw={800} fz="lg">Resident profile</Text>}>
                        {selected && (
                            <Stack gap="lg">
                                <Group gap="md" wrap="nowrap" align="center">
                                    <Avatar src={selected.photo || undefined} color={avatarColor(selected.name ?? '')} radius="md" size={64}>{initials(selected.name ?? '')}</Avatar>
                                    <Box style={{ minWidth: 0 }}>
                                        <Text fw={800} fz="lg" lh={1.2} truncate>{selected.name}</Text>
                                        <Text size="sm" c="dimmed">{selected.dob ? formatDate(selected.dob) : '—'} · Age {age ?? '—'}</Text>
                                    </Box>
                                </Group>

                                <Box style={{ padding: '10px 12px', borderRadius: 10, background: cssVar(allergies.length ? 'red' : 'teal', 0) }}>
                                    <Group gap={8} wrap="nowrap" align="flex-start">
                                        {allergies.length
                                            ? <IconAlertTriangle size={15} color={cssVar('red', 6)} style={{ flexShrink: 0, marginTop: 2 }} />
                                            : <IconCircleCheck size={15} color={cssVar('teal', 6)} style={{ flexShrink: 0, marginTop: 2 }} />}
                                        <Text size="sm" fw={700} c={allergies.length ? 'red.7' : 'teal.7'}>
                                            {allergies.length ? `Allergies: ${allergies.join(', ')}` : 'No known allergies'}
                                        </Text>
                                    </Group>
                                </Box>

                                <Group grow gap="sm" align="stretch">
                                    <Box style={surfaceTertiary} p="sm"><Text size="xs" c="dimmed">Fall risk</Text><Text fw={800} c={`${fallRisk.color}.7`}>{fallRisk.label}</Text></Box>
                                    <Box style={surfaceTertiary} p="sm"><Text size="xs" c="dimmed">PRN</Text><Text fw={800}>{selected.prn_count ?? 0}</Text></Box>
                                    <Box style={surfaceTertiary} p="sm"><Text size="xs" c="dimmed">Regular</Text><Text fw={800}>{selected.regular_count ?? 0}</Text></Box>
                                </Group>

                                <Stack gap={8}>
                                    <Group justify="space-between"><Text size="sm" c="dimmed">NHS number</Text><Text size="sm" fw={600}>{selected.nhs || '—'}</Text></Group>
                                    <Group justify="space-between"><Text size="sm" c="dimmed">Weight</Text><Text size="sm" fw={600}>{weightStr}</Text></Group>
                                    <Group justify="space-between"><Text size="sm" c="dimmed">Date of birth</Text><Text size="sm" fw={600}>{selected.dob ? formatDate(selected.dob) : '—'}</Text></Group>
                                </Stack>

                                {riskFlags.length > 0 && (
                                    <Box>
                                        <Text size="xs" fw={700} c="dimmed" tt="uppercase" mb={6}>Active risks</Text>
                                        <Group gap={6} wrap="wrap">
                                            {riskFlags.slice(0, 8).map((r, i) => {
                                                const rc = (r.level === 'high' || r.level === 'urgent') ? 'red' : (r.level === 'medium' ? 'orange' : 'gray');
                                                return <Badge key={i} variant="light" color={rc} tt="none">{r.label}</Badge>;
                                            })}
                                        </Group>
                                    </Box>
                                )}

                                {selected.client_id && (
                                    <Button component="a" href={`/client-details/${selected.client_id}`} target="_blank" rel="noopener noreferrer"
                                        variant="light" radius="md" rightSection={<IconChevronRight size={14} />}>View full record</Button>
                                )}
                            </Stack>
                        )}
                    </Drawer>
                </Container>
            </Box>
        </>
    );
}

MedicationRoundLab142.layout = (page) => <AppShell>{page}</AppShell>;
