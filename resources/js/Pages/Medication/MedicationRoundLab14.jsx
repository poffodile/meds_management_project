import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Card, Paper, Group, Stack, Text, Box, TextInput, Button,
    Badge, ThemeIcon, ScrollArea, ActionIcon, Avatar, Divider, RingProgress, Progress, Collapse,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconPill,
    IconAlertTriangle, IconShieldLock, IconQrcode, IconPlus, IconUserMinus, IconNotes,
    IconFileText, IconClipboardList, IconX, IconClock, IconCheck, IconBan,
    IconArrowRight, IconInfoCircle, IconChevronRight, IconChevronDown,
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
const ENDPOINT = '/medication/medication-round-lab1-4';

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
    background: '#fff',
    borderRadius: 18,
    border: '1px solid var(--mantine-color-gray-2)',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};
const surfacePrimary = { ...surface, borderRadius: 24 };
const surfaceTertiary = { ...surface, borderRadius: 12 };

/** A section heading used inside the unified panels. */
function PanelSection({ title, count, right, children, divider = true, pt }) {
    return (
        <Box>
            {divider && <Divider color="gray.2" />}
            <Box px="md" pt={pt ?? 'md'} pb="md">
                <Group justify="space-between" align="center" mb="sm" wrap="nowrap">
                    <Group gap={8} align="baseline" wrap="nowrap">
                        <Text fw={700} fz="sm" c="gray.8">{title}</Text>
                        {count != null && <Text fz="xs" c="dimmed">{count}</Text>}
                    </Group>
                    {right}
                </Group>
                {children}
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
                    <Text fz="sm" fw={600} c="gray.8" truncate>{resident.name}</Text>
                    <Text fz="xs" c="dimmed" truncate>{resident.context}</Text>
                </Box>
                <Badge size="sm" variant="light" color={accent} radius="sm" style={{ flexShrink: 0 }}>{label}</Badge>
            </Group>
        </Box>
    );
}

/**
 * MedicationRow — the visual focus. Thin status side-stripe only, three zones:
 *   left   = name + tags, then `dose • route`
 *   middle = purpose / instructions / notes (balances the card)
 *   right  = time / status / stock, compact & stacked vertically
 * Action buttons sit grouped in the lower-right. `med` is the toMed() display
 * shape; onAction(code).
 */
function MedicationRow({ med, onAction }) {
    const accent = statusColors[String(med.status ?? '').toLowerCase()] ?? 'indigo';
    const recorded = Boolean(med.code);
    const title = [med.name, med.strength].filter(Boolean).join(' ');
    const doseRoute = [med.dose, med.route].filter(Boolean).join('  •  ');
    return (
        <Box style={{ ...surfacePrimary, overflow: 'hidden', display: 'flex' }}>
            {/* thin coloured side stripe — the ONLY status colour on the card frame */}
            <Box style={{ width: 4, background: cssVar(accent, 5), flexShrink: 0 }} />
            <Box style={{ flex: 1, minWidth: 0, padding: '20px 22px', display: 'flex', flexDirection: 'column', minHeight: 132 }}>
                {/* Three zones across the top */}
                <Group justify="space-between" align="flex-start" wrap="nowrap" gap="xl" style={{ flex: 1 }}>
                    {/* LEFT — name, tags, dose • route */}
                    <Box style={{ minWidth: 0, flex: '1 1 38%' }}>
                        <Group gap={8} wrap="wrap" align="center">
                            <Text fw={650} fz="md" c="gray.9" lh={1.2}>{title || 'Medication'}</Text>
                            {med.isControlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD{med.cdSchedule ? ` ${med.cdSchedule}` : ''}</Badge>}
                            {(med.tags ?? []).map((t, i) => <Badge key={i} size="xs" variant="light" color={t.color ?? 'gray'} radius="sm">{t.label}</Badge>)}
                        </Group>
                        {doseRoute && (
                            <Text fz="sm" c="gray.6" mt={6} style={{ lineHeight: 1.4 }}>{doseRoute}</Text>
                        )}
                    </Box>

                    {/* MIDDLE — purpose / instructions / notes */}
                    <Box style={{ minWidth: 0, flex: '1 1 34%', alignSelf: 'stretch' }}>
                        {med.instruction ? (
                            <Group gap={7} wrap="nowrap" align="flex-start">
                                <IconNotes size={15} stroke={1.6} color="var(--mantine-color-gray-4)" style={{ flexShrink: 0, marginTop: 2 }} />
                                <Text fz="sm" c="gray.6" style={{ lineHeight: 1.45 }}>{med.instruction}</Text>
                            </Group>
                        ) : (
                            <Text fz="xs" c="gray.4" style={{ lineHeight: 1.45 }}>No additional instructions.</Text>
                        )}
                    </Box>

                    {/* RIGHT — time / status / stock, stacked & compact */}
                    <Stack gap={4} align="flex-end" style={{ flexShrink: 0, flex: '0 0 auto' }}>
                        {med.time && (
                            <Group gap={5} wrap="nowrap">
                                <IconClock size={15} color={cssVar(accent, 6)} />
                                <Text fz="sm" fw={700} c={`${accent}.7`}>{med.time}</Text>
                            </Group>
                        )}
                        {med.statusLabel && <Text fz="xs" fw={500} c="dimmed">{med.statusLabel}</Text>}
                        {med.stock != null && (
                            <Group gap={6} wrap="nowrap">
                                <Text fz="xs" c="gray.6">Stock {med.stock}{med.stockUnit ? ` ${med.stockUnit}` : ''}</Text>
                                {med.lowStock && <Badge size="xs" color="orange" variant="light" radius="sm">Low</Badge>}
                            </Group>
                        )}
                    </Stack>
                </Group>

                {/* Lower-right — grouped action buttons (or recorded outcome) */}
                <Group justify="flex-end" mt="md">
                    {recorded ? (
                        <Badge color={statusColors[(CODE_LABELS[med.code] ?? '').toLowerCase()] ?? 'green'} variant="light" size="lg" radius="sm" tt="capitalize">
                            {CODE_LABELS[med.code] ?? med.code}
                        </Badge>
                    ) : (
                        <Group gap="xs" wrap="nowrap">
                            <Button size="sm" variant="subtle" color="gray" radius="md" w={116} leftSection={<IconBan size={15} />} onClick={() => onAction?.('O')}>Not Given</Button>
                            <Button size="sm" variant="outline" color="red" radius="md" w={116} leftSection={<IconX size={15} />} onClick={() => onAction?.('R')}>Refused</Button>
                            <Button size="sm" color="green" radius="md" w={130} leftSection={<IconCheck size={15} />} onClick={() => onAction?.('A')}>Administer</Button>
                        </Group>
                    )}
                </Group>
            </Box>
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

/** Clean alert row — white, colour only on the icon, subtle hover. */
function AlertRow({ color, icon: Icon, title, description }) {
    return (
        <Group gap={9} wrap="nowrap" align="flex-start" px={4} py={4}>
            <Icon size={18} stroke={1.8} color={cssVar(color, 6)} style={{ flexShrink: 0, marginTop: 1 }} />
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text fz="sm" fw={650} lh={1.2} c="gray.8">{title}</Text>
                {description && <Text fz="xs" c="gray.6" lh={1.25} lineClamp={2}>{description}</Text>}
            </Box>
        </Group>
    );
}

/** A secondary quick-action row. */
function QuickAction({ icon: Icon, label, href, disabled }) {
    const inner = (
        <Group gap={10} wrap="nowrap" px={9} py={7} style={{ borderRadius: 10, opacity: disabled ? 0.5 : 1, cursor: disabled ? 'default' : 'pointer' }}
            onMouseEnter={(e) => { if (!disabled) e.currentTarget.style.background = 'var(--mantine-color-gray-0)'; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
            <ThemeIcon variant="light" color="gray" size={30} radius="md"><Icon size={16} stroke={1.6} /></ThemeIcon>
            <Text fz="sm" fw={500} c="gray.7" style={{ flex: 1 }}>{label}</Text>
            {!disabled && <IconChevronRight size={15} color="var(--mantine-color-gray-4)" />}
        </Group>
    );
    if (href && !disabled) return <Box component="a" href={href} style={{ textDecoration: 'none' }}>{inner}</Box>;
    return inner;
}

export default function MedicationRoundLab14({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const [activeRound, setActiveRound] = useState(currentRound);
    const [selectedId, setSelectedId] = useState(null);
    const [query, setQuery] = useState('');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState(null);
    const [recordOpened, record] = useDisclosure(false);
    const [alertsOpen, setAlertsOpen] = useState(false);
    const [alertsSectionOpen, { toggle: toggleAlertsSection }] = useDisclosure(true);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const filtered = query.trim()
        ? residents.filter((r) => r.name.toLowerCase().includes(query.toLowerCase()))
        : residents;

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
        .slice(0, 5);

    // Round-wide alerts.
    const overdueAlerts = residents.flatMap((r) =>
        r.rows.filter((row) => !row.code && row.status === 'overdue')
            .map((row) => ({ resident: r.name, med: row.medication_name, time: row.slot })));
    const lowStockMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.low_stock).map((r) => r.medication_name))];
    const cdMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.is_controlled).map((r) => r.medication_name))];

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    // One-tap "Given" for scheduled, non-controlled meds; everything else opens the dialog.
    const handleAction = (row, code) => {
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
    const scheduled = selRows.filter((r) => !r.as_required);
    const prn = selRows.filter((r) => r.as_required);
    const dueNow = scheduled.filter((r) => r.code || r.status === 'overdue' || r.status === 'due_now');
    const upcoming = scheduled.filter((r) => !r.code && (r.status === 'upcoming' || r.status === 'later' || r.status === 'due'));
    const riskFlags = selected?.risk_flags ?? [];
    const hasHighRisk = riskFlags.some((r) => r.level === 'high' || r.level === 'urgent');
    const allergies = selected?.allergies ?? [];

    // ---------------------------------------------------------------------------
    // LEFT — one unified panel: Residents Due → Recent Activity → Next Due.
    // ---------------------------------------------------------------------------
    const leftPanel = (
        <Box style={surface}>
            {/* Residents Due */}
            <Box px="md" pt="md" pb="md">
                <Group justify="space-between" align="center" mb="sm">
                    <Text fw={700} fz="sm" c="gray.8">Residents Due</Text>
                    <Badge variant="light" color="gray" radius="sm">{residents.length}</Badge>
                </Group>
                <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />} value={query}
                    onChange={(e) => setQuery(e.currentTarget.value)} mb="sm" radius="md" />
                <ScrollArea.Autosize mah={isMobile ? 360 : 420}>
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
            <PanelSection title="Recent Activity"
                right={<IconClock size={15} color="var(--mantine-color-gray-5)" />}>
                {recentActivity.length === 0 ? (
                    <Text fz="sm" c="dimmed">No medications recorded yet this round.</Text>
                ) : (
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
                                        <Text fz="xs" fw={600} c="gray.8" truncate>{row.medication_name} {ACT_CODE[row.code] ?? 'recorded'}</Text>
                                        <Text fz={11} c="dimmed" truncate>{row.resident}{row.recorded_by ? ` · ${row.recorded_by}` : ''}</Text>
                                    </Box>
                                    <Text fz={11} c="dimmed" style={{ flexShrink: 0 }}>{row.slot || '—'}</Text>
                                </Group>
                            );
                        })}
                    </Stack>
                )}
            </PanelSection>

            {/* Next Medications Due — dense, table-like */}
            <PanelSection title="Next Medications Due" count={nextDue.length}>
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
                                        <Text fz="xs" fw={600} c="gray.8" truncate>{row.medication_name}</Text>
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
    // OPTION 1 — Context Card. Everything stacked under the name as the resident
    // currently being administered to: room/age/weight, round + window, allergy, chips.
    const lineA = selected ? [
        selected.room ? `Room ${selected.room}` : null,
        ageFromDob(selected.dob) != null ? `Age ${ageFromDob(selected.dob)}` : null,
        selected.weight ? `${selected.weight}${selected.weight_unit ? ` ${selected.weight_unit}` : ''}` : null,
        selected.gender || null,
    ].filter(Boolean).join('  •  ') : '';

    const residentHeader = selected && (
        <Box style={surfacePrimary}>
            <Group align="flex-start" gap="md" wrap="nowrap" p="lg">
                <Avatar src={selected.photo || undefined} color={avatarColor(selected.name ?? '')} radius="lg" size={68}>
                    {initials(selected.name ?? '')}
                </Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    {/* Name (strongest) + close */}
                    <Group justify="space-between" align="flex-start" wrap="nowrap" gap="sm">
                        <Text fz={24} fw={800} c="gray.9" lh={1.15} truncate>{selected.name}</Text>
                        <ActionIcon variant="subtle" color="gray" radius="md" onClick={() => setSelectedId(null)} title="Close" style={{ flexShrink: 0 }}>
                            <IconX size={18} />
                        </ActionIcon>
                    </Group>
                    {/* Room • Age • Weight */}
                    {lineA && <Text fz="sm" c="gray.6" mt={4}>{lineA}</Text>}
                    {/* Round • window */}
                    <Text fz="sm" c="gray.6" mt={3}>{meta.label} Round{meta.window ? `  •  ${meta.window}` : ''}</Text>
                    {/* Allergy line — red only when there is one */}
                    <Text fz="sm" fw={600} mt={7} c={allergies.length ? 'red.7' : 'gray.6'}>
                        {allergies.length ? `Allergy: ${allergies.join(', ')}` : 'Allergy: None'}
                    </Text>
                    {/* Context chips */}
                    <Group gap={10} mt={12} wrap="wrap">
                        <InfoChip icon={IconAlertTriangle}
                            color={hasHighRisk ? 'red' : (riskFlags.length ? 'orange' : 'gray')} label={`Risks ${riskFlags.length}`} />
                        <InfoChip icon={IconPill} color="gray" label={`PRN ${selected.prn_count ?? 0}`} />
                        <InfoChip icon={IconClipboardList} color="gray" label={`Meds ${selected.regular_count ?? 0}`} />
                    </Group>
                </Box>
            </Group>
        </Box>
    );

    const medGroup = (title, color, count, unit, rows, emptyMsg) => (
        <Box>
            <Group gap={8} mb="sm" align="baseline" px={4}>
                <Text fw={700} fz="md" c="gray.8">{title}</Text>
                {count != null && <Text fz="sm" c="dimmed">{count} {unit}</Text>}
            </Group>
            <Stack gap="lg">
                {rows.length === 0
                    ? <Box p="md" style={surfaceTertiary}><Text fz="sm" c="dimmed">{emptyMsg}</Text></Box>
                    : rows.map((row, i) => <MedicationRow key={i} med={toMed(row)} onAction={(code) => handleAction(row, code)} />)}
            </Stack>
        </Box>
    );

    const detailPanel = selected && (
        <Stack gap="lg">
            {residentHeader}
            {/* Med cards constrained to ~78% of the centre column so they read as
                intentional, balanced cards rather than full-width banners. */}
            <Box style={{ maxWidth: 720, width: '100%' }}>
                <Stack gap="lg">
                    {medGroup('Due Now', 'red', dueNow.length, 'medications', dueNow, 'Nothing due right now.')}
                    {prn.length > 0 && medGroup('PRN Medications', 'grape', prn.length, 'available', prn, '')}
                    {upcoming.length > 0 && medGroup('Upcoming', 'indigo', upcoming.length, 'medications', upcoming, '')}
                </Stack>
            </Box>
        </Stack>
    );

    // Overview shown in the centre when no resident is selected.
    const overviewPanel = (
        <Box style={surface}>
            <Box px="lg" pt="lg" pb="md">
                <Text fw={700} fz="lg" c="gray.9">Round Overview</Text>
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
                                        <Text fz="sm" fw={600} c="gray.8" truncate>{row.medication_name}</Text>
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
        { value: daySched.length ? (dayCompleted / daySched.length) * 100 : 0, color: 'green' },
        { value: daySched.length ? (dayOverdue / daySched.length) * 100 : 0, color: 'red' },
        { value: daySched.length ? (dayDueSoon / daySched.length) * 100 : 0, color: 'orange' },
    ];
    const legend = [
        { label: 'Completed', value: dayCompleted, color: 'green' },
        { label: 'Overdue', value: dayOverdue, color: 'red' },
        { label: 'Due Soon', value: dayDueSoon, color: 'orange' },
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
            key: `od-${i}`, color: 'red', icon: IconAlertTriangle,
            title: 'Overdue Medication', description: `${a.resident} — ${a.med}${a.time ? ` · ${a.time}` : ''}`,
        })),
        ...lowStockMeds.map((m) => ({ key: `ls-${m}`, color: 'orange', icon: IconAlertTriangle, title: 'Low Stock', description: m })),
        ...cdMeds.map((m) => ({ key: `cd-${m}`, color: 'blue', icon: IconShieldLock, title: 'Controlled Drug', description: `${m} · requires witness` })),
    ];
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
                    <Text fw={700} fz="md" c="gray.8">Round Progress</Text>
                    <Badge size="sm" variant="light" color="indigo" radius="sm">Today</Badge>
                </Group>
                <Group justify="center" mb="xs">
                    <RingProgress
                        size={124} thickness={10} roundCaps sections={progressSections}
                        label={<Box ta="center">
                            <Text fw={800} fz={28} lh={1} c="gray.9">{dayPct}%</Text>
                            <Text c="dimmed" fz={11} lh={1} mt={3}>today</Text>
                        </Box>}
                    />
                </Group>
                <Stack gap={11}>
                    {legend.map((l) => (
                        <Group key={l.label} justify="space-between" gap={6} wrap="nowrap">
                            <Group gap={9} wrap="nowrap">
                                <Box w={9} h={9} style={{ borderRadius: '50%', flexShrink: 0, background: cssVar(l.color, 6) }} />
                                <Text fz="sm" c="gray.7">{l.label}</Text>
                            </Group>
                            <Text fz="sm" fw={700} c="gray.9">{l.value}</Text>
                        </Group>
                    ))}
                </Stack>
                {/* Per-round breakdown — each individual round's own progress */}
                <Box mt="md" pt="md" style={{ borderTop: '1px dashed var(--mantine-color-gray-2)' }}>
                    <Text fz="sm" fw={600} c="gray.6" mb={9}>By round</Text>
                    <Stack gap={10}>
                        {roundBreakdown.map((r) => (
                            <Box key={r.key}>
                                <Group justify="space-between" mb={4} wrap="nowrap">
                                    <Text fz="xs" fw={r.key === meta.key ? 700 : 600} c={r.key === meta.key ? `${r.color}.7` : 'gray.7'}>{r.label}</Text>
                                    <Text fz="xs" fw={700} c="gray.8">{r.done}/{r.total}</Text>
                                </Group>
                                <Progress value={r.pct} size="sm" radius="xl" color={r.color} />
                            </Box>
                        ))}
                    </Stack>
                </Box>
            </Box>

            <Divider color="gray.2" />

            {/* Alerts — collapsible section; click the header to fold it away */}
            <Box p="lg">
                <Box component="button" onClick={toggleAlertsSection}
                    style={{ width: '100%', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0, marginBottom: alertsSectionOpen ? 'var(--mantine-spacing-sm)' : 0 }}>
                    <Group justify="space-between" align="center" wrap="nowrap">
                        <Text fw={700} fz="md" c="gray.8">Alerts</Text>
                        <Group gap={8} wrap="nowrap" align="center">
                            <Badge size="sm" variant="light" color={alertTotal > 0 ? 'red' : 'gray'} radius="sm">
                                {alertTotal > 0 ? `${alertTotal} notification${alertTotal > 1 ? 's' : ''}` : 'None'}
                            </Badge>
                            <IconChevronDown size={16} stroke={2}
                                style={{ color: 'var(--mantine-color-gray-5)', transform: alertsSectionOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                        </Group>
                    </Group>
                </Box>
                <Collapse in={alertsSectionOpen}>
                    {noAlerts ? (
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
                                    <AlertRow key={a.key} color={a.color} icon={a.icon} title={a.title} description={a.description} />
                                ))}
                            </Stack>
                        </ScrollArea.Autosize>
                    )}
                </Collapse>
            </Box>

            <Divider color="gray.2" />

            {/* Quick Actions */}
            <Box p="lg">
                <Text fw={700} fz="md" c="gray.8" mb="sm">Quick Actions</Text>
                <Stack gap={2}>
                    <QuickAction icon={IconQrcode} label="Scan Medication" disabled />
                    <QuickAction icon={IconPlus} label="Add PRN" disabled />
                    <QuickAction icon={IconUserMinus} label="Temporary Absence" disabled />
                    <QuickAction icon={IconNotes} label="View Handover Notes" href="/medication/shift-handover-react" />
                    <QuickAction icon={IconFileText} label="View MAR Report" disabled />
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
                    <ThemeIcon variant="light" color="indigo" size={38} radius="md"><IconPill size={20} stroke={1.7} /></ThemeIcon>
                    <Box>
                        <Group gap={8} align="center">
                            <Text fz={24} fw={800} c="gray.9">Medication Round</Text>
                            <Badge color="grape" variant="light" radius="sm" size="sm">Lab 1.4</Badge>
                        </Group>
                        <Text c="dimmed" fz="xs">{meta.label}{meta.window ? ` · ${meta.window}` : ''}</Text>
                    </Box>
                </Group>

                <Group gap="xs" wrap="nowrap" align="center">
                    <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })}
                        leftSection={<IconCalendar size={15} color={cssVar('indigo', 6)} />} w={150} radius="md" size="sm"
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
                    <Button leftSection={<IconCircleCheck size={16} />} radius="md" size="sm" disabled title="Coming soon">End Round</Button>
                </Group>
            </Group>
        </Box>
    );

    return (
        <>
            <Head title="Medication Round (Lab 1.4)" />
            <Box style={{ background: 'var(--mantine-color-gray-0)', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                    {isMobile ? (
                        <Stack gap="lg">
                            {header}
                            <FlashAlerts />
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
                </Container>
            </Box>
        </>
    );
}

MedicationRoundLab14.layout = (page) => <AppShell>{page}</AppShell>;
