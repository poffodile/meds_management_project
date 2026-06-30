import { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Box, Group, Stack, Text, Title, Badge, Avatar, Button, ActionIcon,
    TextInput, SegmentedControl, Progress, Collapse, ThemeIcon, Divider, Tooltip,
} from '@mantine/core';
import {
    IconSearch, IconChevronDown, IconChevronRight, IconCircle, IconCircleCheck,
    IconPlayerPlay, IconPlayerPause, IconClipboardCheck, IconClock, IconPill,
    IconAlertTriangle, IconAlertCircle, IconBolt, IconBox, IconShieldLock,
    IconCheck, IconCircleX, IconBan, IconLock, IconLockOpen, IconBedFilled, IconCake,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';

import { roundTokens } from '@frontend/tokens';
import { ageFromDob } from '@frontend/lib/dateUtils';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import { usePageReload } from '@frontend/hooks/usePageReload';

// Dashboard-style Medication Round ("Medication Round 3"). Same backend data as the
// other round pages (buildRoundProps), presented as a round-lifecycle stepper plus a
// per-resident progress table — modelled on the reference dashboard, recoloured to the
// Care One OS palette. All markup is inline so the shared components stay untouched.
const ENDPOINT = '/medication/medication-round-3';

// Overall page zoom — scales the whole screen's content down (layout reflows, so no
// empty gap). Tweak this one number to resize everything together. The record dialog
// is left outside the zoom so it stays full size.
const CONTENT_SCALE = 0.75;

const cssVar = (color, shade) => `var(--mantine-color-${color}-${shade})`;

const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

// Sentinel allergy text that means "no allergy" — filtered so it isn't shown as one.
const NO_ALLERGY = /^(no|none|nil|n\/?a|na|none known|no known allergies|no allergies|unknown)$/i;
const cleanAllergies = (list) => (list ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));

// The five lifecycle stages shown in the stepper.
const STAGES = [
    { key: 'not_started', label: 'Not started', icon: IconCircle },
    { key: 'in_progress', label: 'In progress', icon: IconPlayerPlay },
    { key: 'paused', label: 'Paused', icon: IconPlayerPause },
    { key: 'review', label: 'Review', icon: IconClipboardCheck },
    { key: 'completed', label: 'Completed', icon: IconCircleCheck },
];

/** Greeting prefix from the local hour. */
function greeting() {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
}

/** A clickable metric tile (top row). */
function StatTile({ icon: Icon, color, label, value, sub, href }) {
    const inner = (
        <Box style={{ ...surface, padding: '14px 16px', height: '100%', cursor: href ? 'pointer' : 'default' }}>
            <Group gap={8} wrap="nowrap" align="center" mb={6}>
                <ThemeIcon variant="light" color={color} radius="md" size={28}><Icon size={16} stroke={1.8} /></ThemeIcon>
                <Text fz="xs" fw={600} c="dimmed" lh={1.1} style={{ flex: 1, minWidth: 0 }} truncate>{label}</Text>
            </Group>
            <Text fz={28} fw={800} c={`${color}.7`} lh={1}>{value}</Text>
            {sub && <Text fz={11} c="dimmed" mt={4}>{sub}</Text>}
        </Box>
    );
    if (href) return <Box component="a" href={href} style={{ textDecoration: 'none', display: 'block', height: '100%' }}>{inner}</Box>;
    return inner;
}

/** The horizontal 5-stage round-lifecycle stepper. */
function RoundStepper({ activeIndex }) {
    return (
        <Group gap={0} wrap="nowrap" align="flex-start" style={{ width: '100%' }}>
            {STAGES.map((s, i) => {
                const Icon = s.icon;
                const done = i < activeIndex;
                const active = i === activeIndex;
                const color = done ? 'brandGreen' : active ? 'brandTeal' : 'gray';
                return (
                    <Box key={s.key} style={{ flex: 1, minWidth: 0, position: 'relative' }}>
                        {i < STAGES.length - 1 && (
                            <Box style={{
                                position: 'absolute', top: 17, left: '50%', right: '-50%', height: 2,
                                background: done ? cssVar('brandGreen', 4) : cssVar('gray', 2),
                            }} />
                        )}
                        <Stack gap={6} align="center" style={{ position: 'relative' }}>
                            <Box style={{
                                width: 36, height: 36, borderRadius: '50%',
                                background: active ? cssVar(color, 6) : done ? cssVar(color, 0) : 'light-dark(#fff, var(--mantine-color-dark-6))',
                                border: `2px solid ${active || done ? cssVar(color, active ? 6 : 4) : cssVar('gray', 3)}`,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                color: active ? '#fff' : cssVar(color, 6),
                            }}>
                                <Icon size={18} stroke={2} />
                            </Box>
                            <Text fz={11} fw={active ? 700 : 500} ta="center"
                                c={active ? `${color}.7` : done ? 'brandGreen.7' : 'dimmed'}>{s.label}</Text>
                        </Stack>
                    </Box>
                );
            })}
        </Group>
    );
}

/** One medication line inside an expanded resident row, with quick record actions. */
function MedLine({ row, locked, onGiven, onOutcome }) {
    const recorded = Boolean(row.code);
    const isPrn = row.as_required;
    const stat = recorded
        ? { word: CODE_LABELS[row.code] ?? row.code, color: (row.code === 'A' || row.code === 'S') ? 'brandGreen' : (row.code === 'R' ? 'red' : 'brandOrange') }
        : row.status === 'overdue' ? { word: 'Overdue', color: 'red' }
            : row.status === 'due_now' ? { word: 'Due now', color: 'brandOrange' }
                : isPrn ? { word: 'PRN', color: 'brandTeal' } : { word: 'Scheduled', color: 'brandTeal' };
    return (
        <Group gap="sm" wrap="nowrap" align="center" py={8}
            style={{ borderTop: '1px solid var(--mantine-color-gray-1)' }}>
            <Text fz="xs" fw={700} c={`${stat.color}.7`} w={48} style={{ flexShrink: 0 }}>{row.slot || 'PRN'}</Text>
            <ThemeIcon variant="light" color={stat.color} radius="md" size={30} style={{ flexShrink: 0 }}>
                <IconPill size={16} stroke={1.7} />
            </ThemeIcon>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Group gap={6} wrap="nowrap" align="center">
                    <Text fz="sm" fw={600} truncate>{row.medication_name}</Text>
                    {row.is_controlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD{row.cd_schedule ? ` ${row.cd_schedule}` : ''}</Badge>}
                </Group>
                <Text fz="xs" c="dimmed" truncate>
                    {[row.strength, row.dose && `Dose ${row.dose}`, row.route].filter(Boolean).join(' · ') || '—'}
                </Text>
            </Box>
            {recorded ? (
                <Badge color={stat.color} variant="light" radius="sm" style={{ flexShrink: 0 }}>{stat.word}</Badge>
            ) : locked ? (
                <Badge color="gray" variant="light" radius="sm" style={{ flexShrink: 0 }}>Round ended</Badge>
            ) : (
                <Group gap={6} wrap="nowrap" style={{ flexShrink: 0 }}>
                    <Button size="xs" radius="md" color="brandGreen" leftSection={<IconCheck size={14} />}
                        onClick={() => onGiven(row)}>Given</Button>
                    <Tooltip label="Refused"><ActionIcon size="md" radius="md" variant="light" color="red" onClick={() => onOutcome(row, 'R')}><IconCircleX size={16} /></ActionIcon></Tooltip>
                    <Tooltip label="Not given / omitted"><ActionIcon size="md" radius="md" variant="light" color="brandOrange" onClick={() => onOutcome(row, 'O')}><IconBan size={16} /></ActionIcon></Tooltip>
                </Group>
            )}
        </Group>
    );
}

/** A resident row in the round table — summary line, expandable to its medications. */
function ResidentTableRow({ resident, roundLabel, expanded, onToggle, locked, onGiven, onOutcome }) {
    const age = ageFromDob(resident.dob);
    const rows = resident.rows ?? [];
    const scheduled = rows.filter((r) => !r.as_required);
    const total = scheduled.length;
    const done = scheduled.filter((r) => r.code).length;
    const prn = rows.filter((r) => r.as_required);
    const pct = total ? Math.round((done / total) * 100) : 0;
    const overdue = scheduled.filter((r) => !r.code && r.status === 'overdue').length;
    const allergies = cleanAllergies(resident.allergies);
    const onWarfarin = rows.some((r) => /warfarin/i.test(r.medication_name || ''));
    const barColor = done === total && total > 0 ? 'brandGreen' : overdue > 0 ? 'red' : 'brandTeal';

    return (
        <Box style={{ borderTop: '1px solid var(--mantine-color-gray-1)' }}>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={12}
                style={{ cursor: 'pointer' }} onClick={onToggle}
                onMouseEnter={(e) => { e.currentTarget.style.background = 'var(--mantine-color-gray-0)'; }}
                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                {/* Resident */}
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '2 1 220px', minWidth: 0 }}>
                    <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={40}>
                        {initials(resident.name ?? '')}
                    </Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz="sm" fw={700} truncate>{resident.name}</Text>
                        <Group gap={6} wrap="nowrap">
                            <IconCake size={12} color={cssVar('gray', 5)} />
                            <Text fz="xs" c="dimmed">{age != null ? `${age} yrs` : '—'}</Text>
                        </Group>
                    </Box>
                </Group>
                {/* Room */}
                <Group gap={6} wrap="nowrap" align="center" style={{ flex: '0 0 88px' }} visibleFrom="sm">
                    <IconBedFilled size={14} color={cssVar('gray', 5)} />
                    <Text fz="sm" fw={600}>{resident.room || '—'}</Text>
                </Group>
                {/* Due meds + tags */}
                <Box style={{ flex: '1 1 150px', minWidth: 0 }} visibleFrom="md">
                    <Text fz="sm" fw={600}>{total} due{prn.length ? ` · ${prn.length} PRN` : ''}</Text>
                    <Group gap={5} wrap="wrap" mt={3}>
                        {allergies.length > 0 && <Badge size="xs" color="red" variant="light" radius="sm" leftSection={<IconAlertTriangle size={10} />}>Allergies</Badge>}
                        {onWarfarin && <Badge size="xs" color="brandOrange" variant="light" radius="sm">Warfarin</Badge>}
                    </Group>
                </Box>
                {/* Progress */}
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '1 1 160px', minWidth: 120 }}>
                    <Box style={{ flex: 1, minWidth: 0 }}>
                        <Progress value={pct} color={barColor} radius="xl" size="sm" />
                    </Box>
                    <Text fz="xs" fw={700} c="dimmed" style={{ flexShrink: 0 }}>{done} / {total}</Text>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="md" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={18} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>

            <Collapse in={expanded}>
                <Box px="md" pb="md" pt={2} style={{ background: 'var(--mantine-color-gray-0)' }}>
                    {rows.length === 0
                        ? <Text fz="sm" c="dimmed" py="sm">No medications in this round.</Text>
                        : (
                            <Stack gap={0}>
                                {[...scheduled, ...prn].map((row, i) => (
                                    <MedLine key={i} row={row} locked={locked} onGiven={onGiven} onOutcome={onOutcome} />
                                ))}
                            </Stack>
                        )}
                </Box>
            </Collapse>
        </Box>
    );
}

export default function MedsRound3({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {} }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const userName = usePage().props?.auth?.user?.name ?? 'there';
    const isManager = usePage().props?.auth?.user?.role === 'manager';

    const [activeRound, setActiveRound] = useState(currentRound);
    const [query, setQuery] = useState('');
    const [expandedId, setExpandedId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [recordOpened, record] = useDisclosure(false);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const roundClosed = Boolean(closures?.[meta.key]);
    const closure = closures?.[meta.key] ?? null;
    const RoundIcon = roundTokens[meta.key]?.icon ?? IconClock;

    const filtered = query.trim()
        ? residents.filter((r) => (r.name || '').toLowerCase().includes(query.toLowerCase()))
        : residents;

    // Round-wide scheduled-dose tallies drive the stepper + the stat tiles.
    const stats = useMemo(() => {
        const sched = residents.flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const allRows = residents.flatMap((r) => r.rows ?? []);
        const completed = sched.filter((r) => r.code).length;
        const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
        const outstanding = sched.filter((r) => !r.code).length;
        const missed = allRows.filter((r) => ['R', 'N', 'O'].includes(r.code)).length;
        const prnGiven = allRows.filter((r) => r.as_required && ['A', 'S'].includes(r.code)).length;
        const lowStock = new Set(allRows.filter((r) => r.low_stock).map((r) => r.medication_name)).size;
        const controlled = new Set(allRows.filter((r) => r.is_controlled).map((r) => r.medication_name)).size;
        return { total: sched.length, completed, overdue, outstanding, missed, prnGiven, lowStock, controlled };
    }, [residents]);

    // Derive the stepper stage from progress (+ the local pause toggle / closure).
    const activeIndex = useMemo(() => {
        if (roundClosed) return 4;                       // Completed (locked)
        if (paused) return 2;                            // Paused (local)
        if (stats.total === 0 || stats.completed === 0) return 0; // Not started
        if (stats.completed >= stats.total) return 3;    // Review (all recorded, not closed)
        return 1;                                        // In progress
    }, [roundClosed, paused, stats]);

    const dayPct = stats.total ? Math.round((stats.completed / stats.total) * 100) : 0;

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    // One-tap "Given" for scheduled, non-controlled meds; everything else uses the dialog.
    const giveDose = (row) => {
        if (roundClosed) return;
        if (!row.is_controlled && !row.as_required && row.slot) {
            router.post(`${ENDPOINT}/record`, {
                mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '', notes: '',
            }, { preserveScroll: true, preserveState: true });
        } else {
            openRecord(row, 'A');
        }
    };
    const outcomeDose = (row, code) => { if (!roundClosed) openRecord(row, code); };

    const endRound = () => router.post(`${ENDPOINT}/end-round`, { date, round: meta.key }, { preserveScroll: true });
    const reopenRound = () => router.post(`${ENDPOINT}/reopen-round`, { date, round: meta.key }, { preserveScroll: true });
    const startRound = () => { setPaused(false); if (residents[0]) setExpandedId(residents[0].client_id); };

    const statusText = roundClosed ? 'Completed' : STAGES[activeIndex].label;

    return (
        <AppShell>
            <Head title="Medication Round 3" />
            <Box style={{ zoom: CONTENT_SCALE }}>
            <Container size="xl" py="lg">
                <FlashAlerts />

                {/* Header */}
                <Group justify="space-between" align="flex-end" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Title order={2} fw={800} fz={28}>{greeting()}, {userName} 👋</Title>
                        <Text c="dimmed" fz="sm" mt={2}>Here's the medication picture for the {meta.label.toLowerCase()} round.</Text>
                    </Box>
                    <Group gap="sm" wrap="wrap">
                        <SegmentedControl
                            value={activeRound}
                            onChange={(v) => { setActiveRound(v); setExpandedId(null); }}
                            data={rounds.map((r) => ({ value: r.key, label: r.label }))}
                            radius="md"
                        />
                        <TextInput type="date" value={date || ''} radius="md"
                            onChange={(e) => reload({ date: e.currentTarget.value })} />
                    </Group>
                </Group>

                {/* Stat tiles */}
                <Box mb="lg" style={{ display: 'grid', gap: 12, gridTemplateColumns: `repeat(${isMobile ? 2 : 6}, 1fr)` }}>
                    <StatTile icon={IconClock} color="brandTeal" label="Due Now" value={stats.outstanding} sub="Medications" />
                    <StatTile icon={IconAlertTriangle} color="brandOrange" label="Overdue" value={stats.overdue} sub="Medications" />
                    <StatTile icon={IconAlertCircle} color="red" label="Missed Doses" value={stats.missed} sub="Today" href="/medication/missed-doses-react" />
                    <StatTile icon={IconBolt} color="grape" label="PRN Given" value={stats.prnGiven} sub="Today" />
                    <StatTile icon={IconBox} color="brandOrange" label="Low Stock" value={stats.lowStock} sub="Medications" href="/medication/stock-react" />
                    <StatTile icon={IconShieldLock} color="grape" label="Controlled Drugs" value={stats.controlled} sub="In round" href="/medication/controlled-drugs-react" />
                </Box>

                {/* Round card */}
                <Box style={surface}>
                    {/* Card header */}
                    <Group justify="space-between" align="center" wrap="wrap" gap="md" p="lg">
                        <Group gap="sm" wrap="nowrap" align="center">
                            <ThemeIcon variant="light" color={roundTokens[meta.key]?.color ?? 'brandTeal'} radius="md" size={42}>
                                <RoundIcon size={22} stroke={1.7} />
                            </ThemeIcon>
                            <Box>
                                <Group gap={8} align="center">
                                    <Text fw={800} fz="lg">{meta.label} Medication Round</Text>
                                    <Badge variant="light" color={roundClosed ? 'brandGreen' : activeIndex === 0 ? 'gray' : 'brandTeal'} radius="sm">{statusText}</Badge>
                                </Group>
                                <Text fz="sm" c="dimmed">{meta.window}{closure?.by ? ` · ended by ${closure.by}${closure.at ? ` at ${closure.at}` : ''}` : ''}</Text>
                            </Box>
                        </Group>
                        <Group gap="sm">
                            <Text fz="sm" c="dimmed">{stats.completed} / {stats.total} given · {dayPct}%</Text>
                            {roundClosed ? (
                                isManager && <Button variant="light" color="brandTeal" radius="md" leftSection={<IconLockOpen size={16} />} onClick={reopenRound}>Re-open round</Button>
                            ) : activeIndex === 0 ? (
                                <Button color="brandTeal" radius="md" leftSection={<IconPlayerPlay size={16} />} onClick={startRound}>Start round</Button>
                            ) : (
                                <Group gap="xs">
                                    <Button variant="default" radius="md"
                                        leftSection={paused ? <IconPlayerPlay size={16} /> : <IconPlayerPause size={16} />}
                                        onClick={() => setPaused((p) => !p)}>{paused ? 'Resume' : 'Pause'}</Button>
                                    <Button color="brandGreen" radius="md" leftSection={<IconLock size={16} />} onClick={endRound}>End round</Button>
                                </Group>
                            )}
                        </Group>
                    </Group>

                    <Divider color="gray.2" />

                    {/* Stepper */}
                    <Box px="lg" py="xl" style={{ maxWidth: 720, margin: '0 auto' }}>
                        <RoundStepper activeIndex={activeIndex} />
                    </Box>

                    <Divider color="gray.2" />

                    {/* Table toolbar */}
                    <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                        <Text fw={700} fz="sm">Residents <Text span c="dimmed" fz="xs">({residents.length})</Text></Text>
                        <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />}
                            value={query} onChange={(e) => setQuery(e.currentTarget.value)} radius="md" w={isMobile ? '100%' : 260} />
                    </Group>

                    {/* Column headers */}
                    {!isMobile && (
                        <Group gap="md" wrap="nowrap" px="md" pb={6} c="dimmed">
                            <Text fz={11} fw={700} tt="uppercase" style={{ flex: '2 1 220px', letterSpacing: 0.4 }}>Resident</Text>
                            <Text fz={11} fw={700} tt="uppercase" style={{ flex: '0 0 88px', letterSpacing: 0.4 }} visibleFrom="sm">Room</Text>
                            <Text fz={11} fw={700} tt="uppercase" style={{ flex: '1 1 150px', letterSpacing: 0.4 }} visibleFrom="md">Due meds</Text>
                            <Text fz={11} fw={700} tt="uppercase" style={{ flex: '1 1 160px', letterSpacing: 0.4 }}>Progress</Text>
                            <Box style={{ width: 28, flexShrink: 0 }} />
                        </Group>
                    )}

                    {/* Rows */}
                    {filtered.length === 0 ? (
                        <Text fz="sm" c="dimmed" ta="center" py="xl">No residents with medications in this round.</Text>
                    ) : (
                        filtered.map((r) => (
                            <ResidentTableRow key={r.client_id} resident={r} roundLabel={meta.label}
                                expanded={expandedId === r.client_id}
                                onToggle={() => setExpandedId(expandedId === r.client_id ? null : r.client_id)}
                                locked={roundClosed} onGiven={giveDose} onOutcome={outcomeDose} />
                        ))
                    )}
                </Box>
            </Container>
            </Box>

            <RecordDoseModal
                opened={recordOpened}
                onClose={record.close}
                row={recordRow}
                date={date}
                presetCode={recordCode}
                endpoint={`${ENDPOINT}/record`}
            />
        </AppShell>
    );
}
