import { useState, useMemo } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Title, Badge, Avatar, Button, ActionIcon, TextInput,
    RingProgress, Progress, Collapse, ThemeIcon, Tooltip,
} from '@mantine/core';
import {
    IconSearch, IconChevronRight, IconCheck, IconCircleX, IconBan, IconPill,
    IconArrowUpRight, IconCake, IconBedFilled, IconLock, IconLockOpen,
    IconPlayerPlay, IconPlayerPause, IconAlertTriangle, IconUsers, IconClockHour4,
    IconCircleCheck, IconCircleDot, IconShieldLock, IconAlertCircle, IconChevronDown,
    IconBolt, IconFileText,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';

import { roundTokens } from '@frontend/tokens';
import { ageFromDob } from '@frontend/lib/dateUtils';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import { usePageReload } from '@frontend/hooks/usePageReload';

// "Medication Round 4" — warm/editorial styling modelled on the Crextio + learning
// dashboard references: cream panel, Fraunces serif display headings & numbers, a
// yellow accent, soft white cards, pill round-tabs, a round-progress donut, a schedule
// timeline and an avatar resident table. Same live data as the other round pages.
const ENDPOINT = '/medication/medication-round-4';

// Warm palette (page-local — deliberately distinct from the app's teal tokens).
const CREAM = '#F6F2E8';
const INK = '#211F1A';
const ACCENT = '#E9D24E';        // yellow
const ACCENT_SOFT = '#F6EDBE';
const LINE = '#ECE6D6';
const DISPLAY = '"Fraunces", "Playfair Display", Georgia, serif';

// Donut section colours.
const C_GIVEN = '#9BBE5B';
const C_MISSED = '#E1632F';
const C_DUE = ACCENT;

const card = (extra = {}) => ({
    background: '#FFFFFF', borderRadius: 20, border: `1px solid ${LINE}`,
    boxShadow: '0 1px 2px rgba(33,31,26,0.03)', ...extra,
});

const NO_ALLERGY = /^(no|none|nil|n\/?a|na|none known|no known allergies|no allergies|unknown)$/i;
const cleanAllergies = (list) => (list ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));

function greeting() {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
}

/** Overall status for a resident from their scheduled rows. */
function residentStatus(resident) {
    const rows = (resident.rows ?? []).filter((r) => !r.as_required);
    if (rows.length === 0) return { label: 'No meds', color: 'gray', soft: '#EFECE3', ink: '#7A756A' };
    const done = rows.filter((r) => r.code).length;
    if (done === rows.length) return { label: 'Complete', color: C_GIVEN, soft: '#EAF1DA', ink: '#5E7A2E' };
    const overdue = rows.filter((r) => !r.code && r.status === 'overdue').length;
    if (overdue > 0) return { label: `${overdue} overdue`, color: C_MISSED, soft: '#FBE6DC', ink: '#B14a22' };
    if (done > 0) return { label: 'In progress', color: ACCENT, soft: ACCENT_SOFT, ink: '#8A7A1E' };
    return { label: 'Due', color: '#8C7CC9', soft: '#EBE6F7', ink: '#6A5AA6' };
}

/** Round pill-tab (white card, coloured icon square; active = ink fill). */
function RoundPill({ round, active, onClick }) {
    const Icon = roundTokens[round.key]?.icon ?? IconClockHour4;
    const tint = roundTokens[round.key]?.color ?? 'brandTeal';
    return (
        <Box component="button" onClick={onClick} style={{
            display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer',
            padding: '8px 14px 8px 8px', borderRadius: 14,
            background: active ? INK : '#FFFFFF', border: `1px solid ${active ? INK : LINE}`,
            transition: 'all .12s',
        }}>
            <Box style={{
                width: 30, height: 30, borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center',
                background: active ? 'rgba(255,255,255,0.16)' : `var(--mantine-color-${tint}-0)`,
            }}>
                <Icon size={17} stroke={1.8} color={active ? '#fff' : `var(--mantine-color-${tint}-6)`} />
            </Box>
            <Box style={{ textAlign: 'left' }}>
                <Text fz="sm" fw={700} c={active ? '#fff' : INK} lh={1.1}>{round.label}</Text>
                <Text fz={10} c={active ? 'rgba(255,255,255,0.6)' : 'dimmed'} lh={1.1}>{round.window}</Text>
            </Box>
        </Box>
    );
}

/** Large serif metric (icon · big number · label) — like the reference's 91/104/185. */
function BigStat({ icon: Icon, value, label }) {
    return (
        <Group gap={12} wrap="nowrap" align="center">
            <ThemeIcon variant="transparent" color="dark" size={26}><Icon size={22} stroke={1.6} /></ThemeIcon>
            <Box>
                <Text style={{ fontFamily: DISPLAY }} fz={38} fw={600} c={INK} lh={1}>{value}</Text>
                <Text fz="xs" c="dimmed" mt={2}>{label}</Text>
            </Box>
        </Group>
    );
}

/** A single med line inside an expanded resident row. */
function MedLine({ row, locked, onGiven, onOutcome }) {
    const recorded = Boolean(row.code);
    const isPrn = row.as_required;
    const tone = recorded
        ? ((row.code === 'A' || row.code === 'S') ? { c: C_GIVEN, t: CODE_LABELS[row.code] } : (row.code === 'R' ? { c: C_MISSED, t: 'Refused' } : { c: '#C99A2E', t: CODE_LABELS[row.code] }))
        : row.status === 'overdue' ? { c: C_MISSED, t: 'Overdue' }
            : isPrn ? { c: '#8C7CC9', t: 'PRN' } : { c: '#8A7A1E', t: 'Due' };
    return (
        <Group gap="sm" wrap="nowrap" align="center" py={9} style={{ borderTop: `1px solid ${LINE}` }}>
            <Text fz="xs" fw={700} c={tone.c} w={46} style={{ flexShrink: 0 }}>{row.slot || 'PRN'}</Text>
            <Box style={{ width: 30, height: 30, borderRadius: 9, flexShrink: 0, background: '#F7F4EC', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IconPill size={16} stroke={1.7} color={tone.c} />
            </Box>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Group gap={6} wrap="nowrap">
                    <Text fz="sm" fw={600} c={INK} truncate>{row.medication_name}</Text>
                    {row.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
                </Group>
                <Text fz="xs" c="dimmed" truncate>{[row.strength, row.dose && `Dose ${row.dose}`, row.route].filter(Boolean).join(' · ') || '—'}</Text>
            </Box>
            {recorded ? (
                <Box px={10} py={4} style={{ flexShrink: 0, borderRadius: 999, background: '#F2EFE6' }}>
                    <Text fz="xs" fw={700} style={{ color: tone.c }}>{tone.t}</Text>
                </Box>
            ) : locked ? (
                <Text fz="xs" c="dimmed" style={{ flexShrink: 0 }}>Round ended</Text>
            ) : (
                <Group gap={6} wrap="nowrap" style={{ flexShrink: 0 }}>
                    <Button size="xs" radius="xl" color="dark" leftSection={<IconCheck size={14} />} onClick={() => onGiven(row)}>Given</Button>
                    <Tooltip label="Refused"><ActionIcon size="md" radius="xl" variant="light" color="red" onClick={() => onOutcome(row, 'R')}><IconCircleX size={15} /></ActionIcon></Tooltip>
                    <Tooltip label="Omitted"><ActionIcon size="md" radius="xl" variant="light" color="yellow" onClick={() => onOutcome(row, 'O')}><IconBan size={15} /></ActionIcon></Tooltip>
                </Group>
            )}
        </Group>
    );
}

/** Resident table row (avatar · name/room · meds · progress · status pill) — expands to meds. */
function ResidentRow({ resident, expanded, onToggle, locked, onGiven, onOutcome }) {
    const age = ageFromDob(resident.dob);
    const rows = resident.rows ?? [];
    const scheduled = rows.filter((r) => !r.as_required);
    const prn = rows.filter((r) => r.as_required);
    const total = scheduled.length;
    const done = scheduled.filter((r) => r.code).length;
    const pct = total ? Math.round((done / total) * 100) : 0;
    const st = residentStatus(resident);
    const allergies = cleanAllergies(resident.allergies);

    return (
        <Box>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={11} style={{ cursor: 'pointer', borderTop: `1px solid ${LINE}` }}
                onClick={onToggle}
                onMouseEnter={(e) => { e.currentTarget.style.background = '#FBFAF5'; }}
                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 210px', minWidth: 0 }}>
                    <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={38}>{initials(resident.name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={6} wrap="nowrap">
                            <Text fz="sm" fw={700} c={INK} truncate>{resident.name}</Text>
                            {allergies.length > 0 && <Tooltip label={`Allergies: ${allergies.join(', ')}`}><ThemeIcon variant="transparent" color="red" size={16}><IconAlertTriangle size={13} /></ThemeIcon></Tooltip>}
                        </Group>
                        <Group gap={10} wrap="nowrap">
                            <Group gap={3} wrap="nowrap"><IconCake size={11} color="#A8A294" /><Text fz="xs" c="dimmed">{age != null ? `${age}` : '—'}</Text></Group>
                            <Group gap={3} wrap="nowrap"><IconBedFilled size={11} color="#A8A294" /><Text fz="xs" c="dimmed">Room {resident.room || '—'}</Text></Group>
                        </Group>
                    </Box>
                </Group>
                <Text fz="sm" c={INK} fw={600} style={{ flex: '1 1 90px' }} visibleFrom="md">{total} due{prn.length ? ` · ${prn.length} PRN` : ''}</Text>
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '1 1 150px', minWidth: 110 }} visibleFrom="sm">
                    <Box style={{ flex: 1, minWidth: 0 }}><Progress value={pct} color={done === total && total ? 'green' : 'yellow'} radius="xl" size="sm" /></Box>
                    <Text fz="xs" fw={700} c="dimmed">{done}/{total}</Text>
                </Group>
                <Box px={11} py={5} style={{ flexShrink: 0, borderRadius: 999, background: st.soft }}>
                    <Text fz="xs" fw={700} style={{ color: st.ink }}>{st.label}</Text>
                </Box>
                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={17} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>
            <Collapse in={expanded}>
                <Box px="md" pb="sm" pt={0} style={{ background: '#FBFAF5' }}>
                    {rows.length === 0
                        ? <Text fz="sm" c="dimmed" py="sm">No medications in this round.</Text>
                        : <Stack gap={0}>{[...scheduled, ...prn].map((row, i) => <MedLine key={i} row={row} locked={locked} onGiven={onGiven} onOutcome={onOutcome} />)}</Stack>}
                </Box>
            </Collapse>
        </Box>
    );
}

export default function MedsRound4({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {} }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const userName = (usePage().props?.auth?.user?.name ?? 'there').split(' ')[0];
    const isManager = usePage().props?.auth?.user?.role === 'manager';

    const [activeRound, setActiveRound] = useState(currentRound);
    const [query, setQuery] = useState('');
    const [expandedId, setExpandedId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [recordOpened, record] = useDisclosure(false);
    const [alertsOpen, alertsCtl] = useDisclosure(true);
    const [scheduleOpen, scheduleCtl] = useDisclosure(true);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const roundClosed = Boolean(closures?.[meta.key]);
    const closure = closures?.[meta.key] ?? null;

    const filtered = query.trim() ? residents.filter((r) => (r.name || '').toLowerCase().includes(query.toLowerCase())) : residents;

    const stats = useMemo(() => {
        const sched = residents.flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const all = residents.flatMap((r) => r.rows ?? []);
        const given = sched.filter((r) => r.code === 'A' || r.code === 'S').length;
        const missed = sched.filter((r) => ['R', 'N', 'O', 'W'].includes(r.code)).length;
        const outstanding = sched.filter((r) => !r.code).length;
        const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
        const prnGiven = all.filter((r) => r.as_required && ['A', 'S'].includes(r.code)).length;
        return { total: sched.length, given, missed, outstanding, overdue, prnGiven, residents: residents.length };
    }, [residents]);

    // Whole-day totals across EVERY round (the donut shows the day, not just this round).
    // Scheduled doses carry a time slot so each belongs to exactly one round — no double count.
    const dayStats = useMemo(() => {
        const sched = Object.values(grid).flat().flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const given = sched.filter((r) => r.code === 'A' || r.code === 'S').length;
        const missed = sched.filter((r) => ['R', 'N', 'O', 'W'].includes(r.code)).length;
        const outstanding = sched.filter((r) => !r.code).length;
        return { total: sched.length, given, missed, outstanding };
    }, [grid]);
    const dayPct = dayStats.total ? Math.round(((dayStats.given + dayStats.missed) / dayStats.total) * 100) : 0;
    const dseg = (n) => (dayStats.total ? (n / dayStats.total) * 100 : 0);

    // Per-round "countdown" — done / total scheduled for each round (Morning…Night).
    const roundBreakdown = rounds.map((r) => {
        const rs = (grid[r.key] ?? []).flatMap((res) => (res.rows ?? []).filter((row) => !row.as_required));
        const done = rs.filter((row) => row.code).length;
        return { key: r.key, label: r.label, color: roundTokens[r.key]?.color ?? 'gray', done, total: rs.length, left: rs.length - done };
    });

    // Day-wide alerts (across EVERY round). Each says WHAT the alert is (title), the
    // specifics (desc) and a category tag — not just a med name.
    const alerts = useMemo(() => {
        const out = [];
        const all = Object.values(grid).flat();
        // Overdue doses.
        all.forEach((r) => {
            (r.rows ?? []).forEach((row) => {
                if (!row.code && !row.as_required && row.status === 'overdue') {
                    out.push({ color: C_MISSED, icon: IconAlertTriangle, tag: 'Overdue',
                        title: 'Dose overdue', desc: `${row.medication_name} for ${r.name}${row.slot ? ` — was due ${row.slot}` : ''}` });
                }
            });
        });
        // Allergies + high risks — per-resident (deduped, since residents repeat per round).
        const seen = new Set();
        all.forEach((r) => {
            if (seen.has(r.client_id)) return;
            seen.add(r.client_id);
            const allergies = cleanAllergies(r.allergies);
            if (allergies.length) {
                out.push({ color: C_MISSED, icon: IconAlertTriangle, tag: 'Allergy',
                    title: 'Allergy on file', desc: `${r.name} — allergic to ${allergies.join(', ')}` });
            }
            (r.risk_flags ?? []).forEach((f) => {
                if (f.level === 'high' || f.level === 'urgent') {
                    out.push({ color: C_MISSED, icon: IconAlertTriangle, tag: 'High risk',
                        title: 'Safety risk', desc: `${r.name} — ${f.label}` });
                }
            });
        });
        // Low stock — include the remaining quantity where we have it.
        const lowQty = new Map();
        all.flatMap((r) => r.rows ?? []).forEach((x) => {
            if (!x.low_stock) return;
            const prev = lowQty.get(x.medication_name);
            if (prev === undefined || (x.stock != null && (prev == null || x.stock < prev))) lowQty.set(x.medication_name, x.stock ?? prev ?? null);
        });
        lowQty.forEach((qty, m) => out.push({ color: '#C99A2E', icon: IconAlertCircle, tag: 'Stock',
            title: 'Low stock — reorder', desc: `${m}${qty != null ? ` — only ${qty} left` : ' is running low'}` }));
        // Controlled drugs in use today.
        [...new Set(all.flatMap((r) => r.rows ?? []).filter((x) => x.is_controlled).map((x) => x.medication_name))]
            .forEach((m) => out.push({ color: '#8C7CC9', icon: IconShieldLock, tag: 'CD',
                title: 'Controlled drug', desc: `${m} — a witness is required to administer` }));
        return out;
    }, [grid]);

    // Schedule timeline — scheduled doses in the round, tagged with resident, sorted by time.
    const schedule = useMemo(() => residents
        .flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required).map((row) => ({ ...row, resident: r.name })))
        .sort((a, b) => String(a.slot || '').localeCompare(String(b.slot || ''))), [residents]);
    const nextDueKey = schedule.find((r) => !r.code && (r.status === 'overdue' || r.status === 'due_now'));

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };
    const giveDose = (row) => {
        if (roundClosed) return;
        if (!row.is_controlled && !row.as_required && row.slot) {
            router.post(`${ENDPOINT}/record`, { mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '', notes: '' }, { preserveScroll: true, preserveState: true });
        } else { openRecord(row, 'A'); }
    };
    const outcomeDose = (row, code) => { if (!roundClosed) openRecord(row, code); };
    const endRound = () => router.post(`${ENDPOINT}/end-round`, { date, round: meta.key }, { preserveScroll: true });
    const reopenRound = () => router.post(`${ENDPOINT}/reopen-round`, { date, round: meta.key }, { preserveScroll: true });

    // Quick actions — the two with a clear destination link out; PRN / refusal open the
    // first resident's panel to record from there (placeholder targets, easy to refine).
    const quickActions = [
        { label: 'Record PRN', icon: IconBolt, color: '#8C7CC9', onClick: () => residents[0] && setExpandedId(residents[0].client_id) },
        { label: 'Record refusal', icon: IconCircleX, color: '#E1632F', onClick: () => residents[0] && setExpandedId(residents[0].client_id) },
        { label: 'Report missed dose', icon: IconAlertTriangle, color: '#C99A2E', href: '/medication/missed-doses-4-1' },
        { label: 'Add note', icon: IconFileText, color: '#3B82C4', href: '/medication/shift-handover-react' },
    ];

    const statusWord = roundClosed ? 'Completed' : paused ? 'Paused' : stats.given + stats.missed === 0 ? 'Not started' : stats.given + stats.missed >= stats.total ? 'Review' : 'In progress';

    return (
        <AppShell>
            <Head title="Medication Round 4">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />
            </Head>

            <Box style={{ background: CREAM, borderRadius: 28, padding: isMobile ? 16 : 28 }}>
                {/* Scrollbars hidden until the area is hovered (thin overlay line). */}
                <style>{`.mr4-scroll{scrollbar-width:thin;scrollbar-color:transparent transparent}.mr4-scroll:hover{scrollbar-color:rgba(33,31,26,0.28) transparent}.mr4-scroll::-webkit-scrollbar{width:6px;height:6px}.mr4-scroll::-webkit-scrollbar-track{background:transparent}.mr4-scroll::-webkit-scrollbar-thumb{background:transparent;border-radius:8px}.mr4-scroll:hover::-webkit-scrollbar-thumb{background:rgba(33,31,26,0.28)}`}</style>
                <FlashAlerts />

                {/* === Macro 2-col: LEFT (greeting · tabs+controls · alerts/schedule + table) and
                       RIGHT (Today's Progress + Quick actions, pulled up to the top). === */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                <Box style={{ flex: '1 1 560px', minWidth: 0, order: isMobile ? 2 : 1 }}>

                {/* Greeting */}
                <Box mb="lg">
                    <Title order={1} style={{ fontFamily: DISPLAY }} fz={isMobile ? 30 : 40} fw={600} c={INK} lh={1.05}>
                        {greeting()}, {userName} <span style={{ fontFamily: 'system-ui' }}>👋</span>
                    </Title>
                    <Text c="dimmed" fz="sm" mt={6}>Here's the {meta.label.toLowerCase()} medication round — {statusWord.toLowerCase()}.</Text>
                </Box>

                {/* Round pill-tabs + controls (date / pause / end) on one line */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Group gap={10} wrap="wrap">
                        {rounds.map((r) => <RoundPill key={r.key} round={r} active={r.key === activeRound} onClick={() => { setActiveRound(r.key); setExpandedId(null); }} />)}
                    </Group>
                    <Group gap="sm" wrap="wrap" mr={isMobile ? 0 : 180}>
                        <TextInput type="date" value={date || ''} radius="xl" variant="filled"
                            onChange={(e) => reload({ date: e.currentTarget.value })} styles={{ input: { background: '#fff', border: `1px solid ${LINE}` } }} />
                        {roundClosed
                            ? (isManager && <Button radius="xl" color="dark" variant="light" leftSection={<IconLockOpen size={16} />} onClick={reopenRound}>Re-open</Button>)
                            : (
                                <Group gap={8} wrap="nowrap">
                                    <Button radius="xl" variant="white" c={INK} style={{ border: `1px solid ${LINE}` }}
                                        leftSection={paused ? <IconPlayerPlay size={16} /> : <IconPlayerPause size={16} />} onClick={() => setPaused((p) => !p)}>
                                        {paused ? 'Resume' : 'Pause'}
                                    </Button>
                                    <Button radius="xl" color="dark" leftSection={<IconLock size={16} />} onClick={endRound}>End round</Button>
                                </Group>
                            )}
                    </Group>
                </Group>

                {/* Inner content — Alerts/Schedule + Residents table */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Residents table — sits next to the Alerts column, with extra padding between them. */}
                    <Box style={card({ flex: '0 1 800px', minWidth: 0, marginLeft: isMobile ? undefined : 28, order: isMobile ? 1 : 2 })}>
                        <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                            <Text style={{ fontFamily: DISPLAY }} fz={22} fw={600} c={INK}>Residents</Text>
                            <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />} value={query}
                                onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" variant="filled" w={isMobile ? '100%' : 240}
                                styles={{ input: { background: '#F7F4EC' } }} />
                        </Group>
                        {!isMobile && (
                            <Group gap="md" wrap="nowrap" px="md" pb={6} c="dimmed">
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 210px', letterSpacing: 0.5 }}>Resident</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 90px', letterSpacing: 0.5 }} visibleFrom="md">Due</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 150px', letterSpacing: 0.5 }} visibleFrom="sm">Progress</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 78, letterSpacing: 0.5 }}>Status</Text>
                                <Box style={{ width: 28 }} />
                            </Group>
                        )}
                        {filtered.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py="xl">No residents with medications in this round.</Text>
                            : filtered.map((r) => <ResidentRow key={r.client_id} resident={r} expanded={expandedId === r.client_id}
                                onToggle={() => setExpandedId(expandedId === r.client_id ? null : r.client_id)}
                                locked={roundClosed} onGiven={giveDose} onOutcome={outcomeDose} />)}
                        <Box py={4} />
                    </Box>

                    {/* Left — Alerts on top, Schedule below */}
                    <Stack gap={16} style={{ flex: isMobile ? '1 1 232px' : '0 1 280px', maxWidth: isMobile ? undefined : 300, order: isMobile ? 2 : 1 }}>
                        {/* Alerts */}
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={alertsOpen ? 8 : 0} style={{ cursor: 'pointer' }} onClick={alertsCtl.toggle}>
                                <Group gap={8} wrap="nowrap">
                                    <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Alerts</Text>
                                    <Badge variant="light" color={alerts.length ? 'red' : 'gray'} radius="sm" size="sm">{alerts.length}</Badge>
                                </Group>
                                <IconChevronDown size={16} stroke={2} color="#A8A294" style={{ transform: alertsOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Group>
                            <Collapse in={alertsOpen}>
                            {alerts.length === 0
                                ? <Text fz="sm" c="dimmed">No alerts today.</Text>
                                : (
                                    <Box className="mr4-scroll" style={{ maxHeight: 240, overflowY: 'auto', overflowX: 'hidden' }}>
                                    <Stack gap={10} pr={4}>
                                        {alerts.map((a, i) => {
                                            const Icon = a.icon;
                                            return (
                                                <Group key={i} gap={10} wrap="nowrap" align="flex-start">
                                                    <Box style={{ width: 30, height: 30, borderRadius: 9, flexShrink: 0, background: `${a.color}1A`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                        <Icon size={16} stroke={1.8} color={a.color} />
                                                    </Box>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Group justify="space-between" wrap="nowrap" gap={6} align="center">
                                                            <Text fz={12} fw={700} c={INK} lh={1.2} truncate>{a.title}</Text>
                                                            <Badge size="xs" radius="sm" style={{ flexShrink: 0, background: `${a.color}1A`, color: a.color }}>{a.tag}</Badge>
                                                        </Group>
                                                        <Text fz={9} c="dimmed" lh={1.25} lineClamp={2}>{a.desc}</Text>
                                                    </Box>
                                                </Group>
                                            );
                                        })}
                                    </Stack>
                                    </Box>
                                )}
                            </Collapse>
                        </Box>

                        {/* Schedule timeline */}
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={scheduleOpen ? 8 : 0} style={{ cursor: 'pointer' }} onClick={scheduleCtl.toggle}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Schedule</Text>
                                <IconChevronDown size={16} stroke={2} color="#A8A294" style={{ transform: scheduleOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Group>
                            <Collapse in={scheduleOpen}>
                            {schedule.length === 0
                                ? <Text fz="sm" c="dimmed">No scheduled doses in this round.</Text>
                                : (
                                    <Box className="mr4-scroll" style={{ maxHeight: 236, overflowY: 'auto', overflowX: 'hidden' }}>
                                    <Stack gap={1} pr={4}>
                                        {schedule.map((row, i) => {
                                            const isNext = nextDueKey && row === nextDueKey;
                                            const given = row.code === 'A' || row.code === 'S';
                                            const missed = ['R', 'N', 'O', 'W'].includes(row.code);
                                            const dot = given ? C_GIVEN : missed ? C_MISSED : row.status === 'overdue' ? C_MISSED : ACCENT;
                                            return (
                                                <Group key={i} gap={8} wrap="nowrap" align="center" px={8} py={4}
                                                    style={{ borderRadius: 10, background: isNext ? INK : 'transparent' }}>
                                                    <Text fz={11} fw={700} w={38} style={{ flexShrink: 0, color: isNext ? '#fff' : INK }}>{row.slot || '—'}</Text>
                                                    {given ? <IconCircleCheck size={14} color={isNext ? '#fff' : C_GIVEN} style={{ flexShrink: 0 }} />
                                                        : <IconCircleDot size={14} color={isNext ? ACCENT : dot} style={{ flexShrink: 0 }} />}
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Text fz={13} fw={600} truncate c={isNext ? '#fff' : INK} lh={1.2}>{row.medication_name}</Text>
                                                        <Text fz={10} truncate c={isNext ? 'rgba(255,255,255,0.65)' : 'dimmed'} lh={1.2}>{row.resident}</Text>
                                                    </Box>
                                                    {isNext && <Badge size="xs" radius="sm" style={{ background: ACCENT, color: INK }}>Next</Badge>}
                                                </Group>
                                            );
                                        })}
                                    </Stack>
                                    </Box>
                                )}
                            </Collapse>
                    </Box>
                    </Stack>
                </Box>
                </Box>

                {/* === RIGHT — Today's Progress + Quick actions (pulled up to the top) === */}
                <Stack gap={14} style={{ flex: '1 1 240px', maxWidth: isMobile ? undefined : 256, order: isMobile ? 1 : 2 }}>
                    {/* Round Progress */}
                    <Box style={card({ padding: 18 })}>
                        <Group justify="space-between" align="center" mb={6}>
                            <Text style={{ fontFamily: DISPLAY }} fz={17} fw={600} c={INK}>Today's Progress</Text>
                            <Badge variant="light" color={dayStats.outstanding ? 'gray' : 'green'} radius="sm" size="sm">{dayStats.outstanding} left</Badge>
                        </Group>
                        <Group justify="center" my={12}>
                            <RingProgress
                                size={156} thickness={14} roundCaps
                                sections={[
                                    { value: dseg(dayStats.given), color: C_GIVEN },
                                    { value: dseg(dayStats.missed), color: C_MISSED },
                                    { value: dseg(dayStats.outstanding), color: C_DUE },
                                ]}
                                label={(
                                    <Box ta="center">
                                        <Text style={{ fontFamily: DISPLAY }} fz={34} fw={600} c={INK} lh={1}>{dayStats.total}</Text>
                                        <Text fz={11} c="dimmed">all day · {dayPct}%</Text>
                                    </Box>
                                )}
                            />
                        </Group>
                        <Stack gap={11} mt={8}>
                            {[
                                { c: C_GIVEN, label: 'Given', v: dayStats.given },
                                { c: C_DUE, label: 'Outstanding', v: dayStats.outstanding },
                                { c: C_MISSED, label: 'Refused / omitted', v: dayStats.missed },
                            ].map((s) => (
                                <Group key={s.label} justify="space-between" wrap="nowrap">
                                    <Group gap={8} wrap="nowrap"><Box w={9} h={9} style={{ borderRadius: 3, background: s.c }} /><Text fz={12} c="dimmed">{s.label}</Text></Group>
                                    <Text fz={12} fw={700} c={INK}>{s.v}</Text>
                                </Group>
                            ))}
                        </Stack>

                        {/* By-round countdown — how many doses are left in each round. */}
                        <Box mt={16} pt={16} style={{ borderTop: `1px solid ${LINE}` }}>
                            <Text fz={10} fw={700} tt="uppercase" c="dimmed" mb={10} style={{ letterSpacing: 0.5 }}>By round</Text>
                            <Stack gap={14}>
                                {roundBreakdown.map((r) => {
                                    const done = r.total > 0 && r.left === 0;
                                    return (
                                        <Box key={r.key} component="button" onClick={() => { setActiveRound(r.key); setExpandedId(null); }}
                                            style={{ width: '100%', textAlign: 'left', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0 }}>
                                            <Group justify="space-between" wrap="nowrap" mb={3}>
                                                <Text fz="xs" fw={r.key === activeRound ? 700 : 600} c={r.key === activeRound ? INK : 'dimmed'}>{r.label}</Text>
                                                <Text fz="xs" fw={700} c={done ? C_GIVEN : INK}>
                                                    {r.done}/{r.total}
                                                </Text>
                                            </Group>
                                            <Progress value={r.total ? (r.done / r.total) * 100 : 0}
                                                color={r.color} radius="xl" size={8} />
                                        </Box>
                                    );
                                })}
                            </Stack>
                        </Box>
                    </Box>

                    {/* Quick actions */}
                    <Box style={card({ padding: 12 })}>
                        <Text style={{ fontFamily: DISPLAY }} fz={15} fw={600} c={INK} mb={8}>Quick actions</Text>
                        <Box style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 7 }}>
                            {quickActions.map((a) => {
                                const Icon = a.icon;
                                const inner = (
                                    <Group gap={7} wrap="nowrap" align="center" style={{ padding: '6px 8px', borderRadius: 10, background: '#FBFAF5', border: `1px solid ${LINE}`, height: '100%', cursor: 'pointer' }}>
                                        <Box style={{ width: 24, height: 24, borderRadius: 7, flexShrink: 0, background: `${a.color}1A`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                            <Icon size={14} stroke={1.8} color={a.color} />
                                        </Box>
                                        <Text fz={10} fw={600} c={INK} lh={1.15}>{a.label}</Text>
                                    </Group>
                                );
                                return a.href
                                    ? <Box component="a" key={a.label} href={a.href} style={{ textDecoration: 'none' }}>{inner}</Box>
                                    : <Box component="button" key={a.label} onClick={a.onClick} style={{ border: 'none', background: 'transparent', padding: 0, textAlign: 'left' }}>{inner}</Box>;
                            })}
                        </Box>
                    </Box>
                </Stack>
                </Box>

                {closure?.by && <Text fz="xs" c="dimmed" ta="right" mt="md">Round ended by {closure.by}{closure.at ? ` at ${closure.at}` : ''}.</Text>}
            </Box>

            <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
        </AppShell>
    );
}
