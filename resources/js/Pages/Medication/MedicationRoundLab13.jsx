import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Card, Paper, Group, Stack, Text, Box, TextInput, Button,
    Badge, ThemeIcon, ScrollArea, ActionIcon, Avatar, Divider, RingProgress,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconPill,
    IconAlertTriangle, IconShieldLock, IconQrcode, IconPlus, IconUserMinus, IconNotes,
    IconFileText, IconClipboardList, IconX, IconClock, IconCheck, IconBan,
    IconArrowRight, IconInfoCircle, IconChevronRight,
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

// EXPERIMENTAL copy (Lab 1.3) — single-workspace redesign: calm, minimal,
// medication-cards-dominant. All markup that diverges from the shared
// components is built INLINE in this file so the shared components stay untouched.
const ENDPOINT = '/medication/medication-round-lab1-3';

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
const surface = {
    background: '#fff',
    borderRadius: 16,
    border: '1px solid var(--mantine-color-gray-2)',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

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
 * MedicationRow — the visual focus. Two sections, thin status side-stripe only.
 * Top: name + tags + dose/route/purpose + time + stock. Bottom: 3 equal buttons
 * (or the recorded outcome). `med` is the toMed() display shape; onAction(code).
 */
function MedicationRow({ med, onAction }) {
    const accent = statusColors[String(med.status ?? '').toLowerCase()] ?? 'indigo';
    const recorded = Boolean(med.code);
    const title = [med.name, med.strength].filter(Boolean).join(' ');
    return (
        <Box style={{ ...surface, overflow: 'hidden', display: 'flex' }}>
            {/* thin coloured side stripe — the ONLY status colour on the card frame */}
            <Box style={{ width: 5, background: cssVar(accent, 5), flexShrink: 0 }} />
            <Box style={{ flex: 1, minWidth: 0, padding: '16px 18px' }}>
                {/* Top section */}
                <Group justify="space-between" align="flex-start" wrap="nowrap" gap="md">
                    <Box style={{ minWidth: 0, flex: 1 }}>
                        <Group gap="xs" wrap="wrap" align="center">
                            <Text fw={700} fz="md" c="gray.9">{title || 'Medication'}</Text>
                            {med.isControlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD{med.cdSchedule ? ` ${med.cdSchedule}` : ''}</Badge>}
                            {(med.tags ?? []).map((t, i) => <Badge key={i} size="xs" variant="light" color={t.color ?? 'gray'} radius="sm">{t.label}</Badge>)}
                        </Group>
                        <Group gap="lg" mt={8} wrap="wrap">
                            {med.dose && <Group gap={5} wrap="nowrap"><IconPill size={14} color="var(--mantine-color-gray-5)" /><Text fz="sm" c="gray.7">{med.dose}</Text></Group>}
                            {med.route && <Group gap={5} wrap="nowrap"><IconArrowRight size={14} color="var(--mantine-color-gray-5)" /><Text fz="sm" c="gray.7">{med.route}</Text></Group>}
                            {med.instruction && <Group gap={5} wrap="nowrap"><IconInfoCircle size={14} color="var(--mantine-color-gray-5)" /><Text fz="sm" c="gray.7">{med.instruction}</Text></Group>}
                        </Group>
                    </Box>
                    <Stack gap={6} align="flex-end" style={{ flexShrink: 0 }}>
                        {med.time && (
                            <Group gap={5} wrap="nowrap">
                                <IconClock size={15} color={cssVar(accent, 6)} />
                                <Text fz="sm" fw={700} c={`${accent}.7`}>{med.time}</Text>
                            </Group>
                        )}
                        {med.statusLabel && <Text fz="xs" c="dimmed">{med.statusLabel}</Text>}
                        {med.stock != null && (
                            <Group gap={6} wrap="nowrap">
                                <Text fz="xs" c="dimmed">Stock</Text>
                                <Text fz="xs" fw={600} c="gray.7">{med.stock}{med.stockUnit ? ` ${med.stockUnit}` : ''}</Text>
                                {med.lowStock && <Badge size="xs" color="orange" variant="light" radius="sm">Low</Badge>}
                            </Group>
                        )}
                    </Stack>
                </Group>

                {/* Bottom section — outcome */}
                <Box mt="md">
                    {recorded ? (
                        <Badge color={statusColors[(CODE_LABELS[med.code] ?? '').toLowerCase()] ?? 'green'} variant="light" size="lg" radius="sm" tt="capitalize">
                            {CODE_LABELS[med.code] ?? med.code}
                        </Badge>
                    ) : (
                        <Group gap="sm" grow wrap="nowrap">
                            <Button color="green" leftSection={<IconCheck size={16} />} onClick={() => onAction?.('A')}>Administer</Button>
                            <Button variant="outline" color="red" leftSection={<IconX size={16} />} onClick={() => onAction?.('R')}>Refused</Button>
                            <Button variant="default" color="gray" leftSection={<IconBan size={16} />} onClick={() => onAction?.('O')}>Not Given</Button>
                        </Group>
                    )}
                </Box>
            </Box>
        </Box>
    );
}

/** A compact stat block for the resident header (Active Risks / PRN / Regular). */
function StatBlock({ icon: Icon, label, value, color }) {
    return (
        <Box style={{ background: cssVar(color, 0), borderRadius: 12, padding: '10px 14px', minWidth: 110 }}>
            <Group gap={8} wrap="nowrap" align="center">
                <ThemeIcon variant="white" color={color} size={32} radius="md" style={{ boxShadow: 'none' }}><Icon size={18} /></ThemeIcon>
                <Box style={{ minWidth: 0 }}>
                    <Text fz={22} fw={800} lh={1} c={`${color}.7`}>{value}</Text>
                    <Text fz="xs" c="dimmed" mt={2}>{label}</Text>
                </Box>
            </Group>
        </Box>
    );
}

/** Small compact alert row (no coloured border — tinted background only). */
function AlertRow({ color, icon: Icon, title, description }) {
    return (
        <Group gap={8} wrap="nowrap" align="flex-start" px={10} py={8} style={{ background: cssVar(color, 0), borderRadius: 10 }}>
            <ThemeIcon variant="white" color={color} size={26} radius="md" style={{ boxShadow: 'none' }}><Icon size={15} stroke={1.7} /></ThemeIcon>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Text fz="xs" fw={600} lh={1.2} c="gray.8">{title}</Text>
                {description && <Text fz={10} c="dimmed" lh={1.25} mt={1} truncate>{description}</Text>}
            </Box>
        </Group>
    );
}

/** A secondary quick-action row. */
function QuickAction({ icon: Icon, label, href, disabled }) {
    const inner = (
        <Group gap={10} wrap="nowrap" px={10} py={7} style={{ borderRadius: 10, opacity: disabled ? 0.5 : 1, cursor: disabled ? 'default' : 'pointer' }}
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

export default function MedicationRoundLab13({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const [activeRound, setActiveRound] = useState(currentRound);
    const [selectedId, setSelectedId] = useState(null);
    const [query, setQuery] = useState('');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState(null);
    const [recordOpened, record] = useDisclosure(false);

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
                    <Stack gap="sm">
                        {recentActivity.map((row, i) => {
                            const given = row.code === 'A' || row.code === 'S' || row.code === 'W';
                            const refused = row.code === 'R' || row.code === 'O' || row.code === 'N';
                            const color = given ? 'green' : refused ? 'red' : 'gray';
                            const Icon = given ? IconCircleCheck : IconAlertTriangle;
                            return (
                                <Group key={i} gap="sm" wrap="nowrap" align="flex-start">
                                    <Text fz="xs" c="dimmed" w={36} ta="right" style={{ flexShrink: 0 }} mt={4}>{row.slot || '—'}</Text>
                                    <ThemeIcon variant="light" color={color} size={24} radius="xl"><Icon size={14} /></ThemeIcon>
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="sm" fw={600} c="gray.8">{row.medication_name} {ACT_CODE[row.code] ?? 'recorded'}</Text>
                                        <Text fz="xs" c="dimmed">{row.resident}{row.recorded_by ? ` · by ${row.recorded_by}` : ''}</Text>
                                    </Box>
                                </Group>
                            );
                        })}
                    </Stack>
                )}
            </PanelSection>

            {/* Next Medications Due */}
            <PanelSection title="Next Medications Due" count={nextDue.length}>
                {nextDue.length === 0 ? (
                    <Text fz="sm" c="dimmed">Nothing left due in this round.</Text>
                ) : (
                    <Stack gap="sm">
                        {nextDue.map((row, i) => {
                            const s = DUE_STATUS[row.status] ?? { label: row.status, color: 'gray' };
                            const t = medType(row);
                            return (
                                <Group key={i} gap="sm" wrap="nowrap" align="flex-start">
                                    <Text fz="xs" fw={700} c={`${s.color}.7`} w={36} ta="right" style={{ flexShrink: 0 }} mt={2}>{row.slot || '—'}</Text>
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="sm" fw={600} c="gray.8" truncate>{row.medication_name}</Text>
                                        <Text fz="xs" c="dimmed" truncate>{row.resident}</Text>
                                    </Box>
                                    <Stack gap={3} align="flex-end" style={{ flexShrink: 0 }}>
                                        <Badge size="xs" variant="light" color={s.color} radius="sm">{s.label}</Badge>
                                        <Badge size="xs" variant="light" color={t.color} radius="sm">{t.label}</Badge>
                                    </Stack>
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
    const residentHeader = selected && (
        <Box style={surface}>
            <Group justify="space-between" align="flex-start" wrap="nowrap" p="lg">
                <Group align="flex-start" gap="md" wrap="nowrap" style={{ minWidth: 0, flex: 1 }}>
                    <Avatar src={selected.photo || undefined} color={avatarColor(selected.name ?? '')} radius="lg" size={84}>
                        {initials(selected.name ?? '')}
                    </Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz={26} fw={800} c="gray.9" lh={1.1}>{selected.name}</Text>
                        <Group gap="md" mt={6} wrap="wrap">
                            {selected.dob && <Text fz="sm" c="dimmed">DOB: {formatDate(selected.dob)}{ageFromDob(selected.dob) != null ? ` (${ageFromDob(selected.dob)})` : ''}</Text>}
                            {selected.gender && <Text fz="sm" c="dimmed">{selected.gender}</Text>}
                            {selected.weight && <Text fz="sm" c="dimmed">{selected.weight}{selected.weight_unit ? ` ${selected.weight_unit}` : ''}</Text>}
                            <Text fz="sm" c="dimmed">{meta.label} round{meta.window ? ` · ${meta.window}` : ''}</Text>
                        </Group>
                        {allergies.length > 0 && (
                            <Group gap={6} mt={10} wrap="nowrap" px={10} py={5} style={{ background: 'var(--mantine-color-red-0)', borderRadius: 10, display: 'inline-flex' }}>
                                <IconAlertTriangle size={15} color="var(--mantine-color-red-6)" />
                                <Text fz="sm" c="red.7" fw={600}>Allergy: {allergies.join(', ')}</Text>
                            </Group>
                        )}
                    </Box>
                </Group>
                <Group gap="sm" align="flex-start" wrap="nowrap" style={{ flexShrink: 0 }}>
                    <StatBlock icon={IconAlertTriangle} label="Active Risks" value={riskFlags.length} color={hasHighRisk ? 'red' : 'gray'} />
                    <StatBlock icon={IconPill} label="PRN Available" value={selected.prn_count ?? 0} color="teal" />
                    <StatBlock icon={IconClipboardList} label="Regular Meds" value={selected.regular_count ?? 0} color="indigo" />
                    <ActionIcon variant="subtle" color="gray" radius="md" onClick={() => setSelectedId(null)} title="Close">
                        <IconX size={18} />
                    </ActionIcon>
                </Group>
            </Group>
        </Box>
    );

    const medGroup = (title, color, count, unit, rows, emptyMsg) => (
        <Box>
            <Group gap={8} mb="sm" align="baseline">
                <Text fw={700} fz="md" c="gray.8">{title}</Text>
                {count != null && <Text fz="sm" c="dimmed">{count} {unit}</Text>}
            </Group>
            <Stack gap="sm">
                {rows.length === 0
                    ? <Paper radius="md" p="md" style={surface}><Text fz="sm" c="dimmed">{emptyMsg}</Text></Paper>
                    : rows.map((row, i) => <MedicationRow key={i} med={toMed(row)} onAction={(code) => handleAction(row, code)} />)}
            </Stack>
        </Box>
    );

    const detailPanel = selected && (
        <Stack gap="lg">
            {residentHeader}
            {medGroup('Due Now', 'red', dueNow.length, 'medications', dueNow, 'Nothing due right now.')}
            {prn.length > 0 && medGroup('PRN Medications', 'grape', prn.length, 'available', prn, '')}
            {upcoming.length > 0 && medGroup('Upcoming', 'indigo', upcoming.length, 'medications', upcoming, '')}
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
            <PanelSection title="Recent Activity">
                {recentActivity.length === 0 ? (
                    <Text fz="sm" c="dimmed">No medications recorded yet this round.</Text>
                ) : (
                    <Stack gap="md">
                        {recentActivity.map((row, i) => {
                            const given = row.code === 'A' || row.code === 'S' || row.code === 'W';
                            const refused = row.code === 'R' || row.code === 'O' || row.code === 'N';
                            const color = given ? 'green' : refused ? 'red' : 'gray';
                            const Icon = given ? IconCircleCheck : IconAlertTriangle;
                            return (
                                <Group key={i} gap="sm" wrap="nowrap" align="flex-start">
                                    <Text fz="xs" c="dimmed" w={40} ta="right" style={{ flexShrink: 0 }} mt={4}>{row.slot || '—'}</Text>
                                    <ThemeIcon variant="light" color={color} size={26} radius="xl"><Icon size={15} /></ThemeIcon>
                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                        <Text fz="sm" fw={600} c="gray.8">{row.medication_name} {ACT_CODE[row.code] ?? 'recorded'}</Text>
                                        <Text fz="xs" c="dimmed">{row.resident}{row.recorded_by ? ` · by ${row.recorded_by}` : ''}</Text>
                                    </Box>
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
    // RIGHT — one sidebar: Round Progress → Alerts → Quick Actions.
    // ---------------------------------------------------------------------------
    const progressSections = [
        { value: sched.length ? (pCompleted / sched.length) * 100 : 0, color: 'green' },
        { value: sched.length ? (pOverdue / sched.length) * 100 : 0, color: 'red' },
        { value: sched.length ? (pDueSoon / sched.length) * 100 : 0, color: 'orange' },
    ];
    const legend = [
        { label: 'Completed', value: pCompleted, color: 'green' },
        { label: 'Overdue', value: pOverdue, color: 'red' },
        { label: 'Due Soon', value: pDueSoon, color: 'orange' },
        { label: 'Not Started', value: pNotStarted, color: 'gray' },
    ];
    const noAlerts = overdueAlerts.length === 0 && lowStockMeds.length === 0 && cdMeds.length === 0;

    const rightPanel = (
        <Box style={surface}>
            {/* Round Progress */}
            <Box px="md" pt="md" pb="md">
                <Text fw={700} fz="sm" c="gray.8" mb="sm">Round Progress</Text>
                <Group gap="md" wrap="nowrap" align="center">
                    <RingProgress
                        size={84} thickness={8} sections={progressSections}
                        label={<Box ta="center"><Text fw={800} fz={16} lh={1}>{dayPct}%</Text><Text c="dimmed" fz={9} lh={1}>day</Text></Box>}
                    />
                    <Stack gap={5} style={{ flex: 1, minWidth: 0 }}>
                        {legend.map((l) => (
                            <Group key={l.label} justify="space-between" gap={6} wrap="nowrap">
                                <Group gap={6} wrap="nowrap">
                                    <Box w={8} h={8} style={{ borderRadius: '50%', flexShrink: 0, background: cssVar(l.color, 6) }} />
                                    <Text fz="xs" c="gray.7">{l.label}</Text>
                                </Group>
                                <Text fz="xs" fw={700} c="gray.8">{l.value}</Text>
                            </Group>
                        ))}
                    </Stack>
                </Group>
            </Box>

            {/* Alerts */}
            <PanelSection title="Alerts">
                <Stack gap={8}>
                    {noAlerts && <Text fz="sm" c="dimmed">No alerts for this round.</Text>}
                    {overdueAlerts.slice(0, 4).map((a, i) => (
                        <AlertRow key={`od-${i}`} color="red" icon={IconAlertTriangle}
                            title="Overdue Medication" description={`${a.resident} — ${a.med}${a.time ? ` · ${a.time}` : ''}`} />
                    ))}
                    {lowStockMeds.slice(0, 3).map((m) => (
                        <AlertRow key={`ls-${m}`} color="orange" icon={IconAlertTriangle} title="Low Stock" description={m} />
                    ))}
                    {cdMeds.slice(0, 3).map((m) => (
                        <AlertRow key={`cd-${m}`} color="blue" icon={IconShieldLock} title="Controlled Drug" description={`${m} · requires witness`} />
                    ))}
                </Stack>
            </PanelSection>

            {/* Quick Actions */}
            <PanelSection title="Quick Actions">
                <Stack gap={2}>
                    <QuickAction icon={IconQrcode} label="Scan Medication" disabled />
                    <QuickAction icon={IconPlus} label="Add PRN" disabled />
                    <QuickAction icon={IconUserMinus} label="Temporary Absence" disabled />
                    <QuickAction icon={IconNotes} label="View Handover Notes" href="/medication/shift-handover-react" />
                    <QuickAction icon={IconFileText} label="View MAR Report" disabled />
                </Stack>
            </PanelSection>
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
                            <Text fz={18} fw={800} c="gray.9">Medication Round</Text>
                            <Badge color="grape" variant="light" radius="sm" size="sm">Lab 1.3</Badge>
                        </Group>
                        <Text c="dimmed" fz="xs">{meta.label}{meta.window ? ` · ${meta.window}` : ''}</Text>
                    </Box>
                </Group>

                <Group gap="xs" wrap="nowrap" align="center">
                    <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })}
                        leftSection={<IconCalendar size={15} />} w={150} radius="md" size="sm" style={{ flexShrink: 0 }} />
                    <Group gap={6} wrap="nowrap" px={4} py={3} style={{ background: 'var(--mantine-color-gray-0)', borderRadius: 10 }}>
                        {rounds.map((r) => {
                            const RI = roundTokens[r.key]?.icon ?? IconPill;
                            const active = r.key === meta.key;
                            const color = roundTokens[r.key]?.color ?? 'indigo';
                            return (
                                <Button key={r.key} size="xs" variant={active ? 'white' : 'subtle'} color={active ? color : 'gray'}
                                    styles={{ root: { boxShadow: active ? '0 1px 2px rgba(16,24,40,0.08)' : 'none' }, label: { fontWeight: 700 } }}
                                    leftSection={<RI size={14} color={cssVar(color, 6)} />}
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
            <Head title="Medication Round (Lab 1.3)" />
            <Box style={{ background: 'var(--mantine-color-gray-0)', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                    {header}
                    <FlashAlerts />

                    {isMobile ? (
                        <Stack gap="lg">
                            {centrePanel}
                            {leftPanel}
                            {rightPanel}
                        </Stack>
                    ) : (
                        <Group align="flex-start" gap="lg" wrap="nowrap">
                            {/* LEFT — narrow unified panel */}
                            <Box style={{ flex: '0 0 300px', minWidth: 0 }}>{leftPanel}</Box>
                            {/* CENTRE — the medication workspace (~70%) */}
                            <Box style={{ flex: '1 1 0%', minWidth: 0 }}>{centrePanel}</Box>
                            {/* RIGHT — single sidebar */}
                            <Box style={{ flex: '0 0 268px', minWidth: 0 }}>{rightPanel}</Box>
                        </Group>
                    )}

                    <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
                </Container>
            </Box>
        </>
    );
}

MedicationRoundLab13.layout = (page) => <AppShell>{page}</AppShell>;
