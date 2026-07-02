import { useState, useMemo } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Avatar, Button, ActionIcon, ThemeIcon,
    Progress, Collapse, Tooltip, RingProgress,
} from '@mantine/core';
import {
    IconChevronRight, IconCheck, IconCircleX, IconBan, IconPill, IconAlertTriangle,
    IconLock, IconLockOpen, IconPlayerPause, IconPlayerPlay, IconSun, IconSoup,
    IconSunset, IconMoon, IconClockHour4, IconArrowRight,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

const ENDPOINT = '/frontend2/medication-round';
const INK = '#1E2F4D';        // dark navy — headings / active tab / primary button
const ORANGE = '#E8842B';     // overdue
const GREEN = '#4F9E57';      // given / complete
const GRAY = '#C7CDD8';       // scheduled

const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 16,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const ROUND_ICONS = { morning: IconSun, lunchtime: IconSoup, evening: IconSunset, night: IconMoon };
const NO_ALLERGY = /^(no|none|nil|n\/?a|na|none known|no known allergies|no allergies|unknown)$/i;
const cleanAllergies = (list) => (list ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));

const isGiven = (c) => c === 'A' || c === 'S';
const isOutcome = (c) => ['R', 'N', 'O', 'W'].includes(c);

function fmtDate(d) {
    if (!d) return '';
    const t = Date.parse(d);
    if (Number.isNaN(t)) return d;
    return new Date(t).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}

/** Per-resident round metrics. */
function metrics(resident) {
    const sched = (resident.rows ?? []).filter((r) => !r.as_required);
    const total = sched.length;
    const done = sched.filter((r) => r.code).length;
    const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const nextDue = sched.filter((r) => !r.code && r.slot).map((r) => r.slot).sort()[0] ?? null;
    const complete = total > 0 && done === total;
    return { total, done, overdue, nextDue, complete };
}
function statusOf(m) {
    if (m.total === 0) return { label: 'No meds', color: '#98A2B3', soft: '#EEF0F4' };
    if (m.complete) return { label: 'Complete', color: GREEN, soft: '#E7F3E8' };
    if (m.overdue > 0) return { label: `${m.overdue} overdue`, color: ORANGE, soft: '#FCEBDD' };
    return { label: `${m.total - m.done} due`, color: '#3E6FB0', soft: '#E6EEF8' };
}

/** Round tab (icon + label + x/y). Active = navy pill. */
function RoundTab({ round, active, done, total, onClick }) {
    const Icon = ROUND_ICONS[round.key] ?? IconClockHour4;
    return (
        <Box component="button" onClick={onClick} style={{
            display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer', padding: '9px 14px', borderRadius: 12,
            background: active ? INK : 'transparent', border: 'none', transition: 'background .12s',
        }}>
            <Icon size={17} stroke={1.8} color={active ? '#fff' : (round.key === 'morning' ? '#E8A93B' : round.key === 'night' ? '#6E5BE6' : '#3E6FB0')} />
            <Text fz="sm" fw={700} c={active ? '#fff' : INK}>{round.label}</Text>
            <Badge size="sm" radius="sm" variant="filled" color="gray"
                style={{ background: active ? 'rgba(255,255,255,0.22)' : '#EEF0F4', color: active ? '#fff' : '#667085' }}>
                {done}/{total}
            </Badge>
        </Box>
    );
}

/** A med line inside an expanded resident row. */
function MedLine({ row, locked, onGiven, onOutcome }) {
    const recorded = Boolean(row.code);
    const tone = recorded
        ? (isGiven(row.code) ? { c: GREEN, t: CODE_LABELS[row.code] } : (row.code === 'R' ? { c: '#D14343', t: 'Refused' } : { c: ORANGE, t: CODE_LABELS[row.code] }))
        : row.status === 'overdue' ? { c: ORANGE, t: 'Overdue' } : row.as_required ? { c: '#6E5BE6', t: 'PRN' } : { c: '#3E6FB0', t: 'Due' };
    return (
        <Group gap="sm" wrap="nowrap" align="center" py={9} style={{ borderTop: '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
            <Text fz="xs" fw={700} c={tone.c} w={42} style={{ flexShrink: 0 }}>{row.slot || 'PRN'}</Text>
            <ThemeIcon variant="light" color={row.is_controlled ? 'grape' : 'blue'} size={28} radius="md"><IconPill size={15} /></ThemeIcon>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Group gap={6} wrap="nowrap">
                    <Text fz="sm" fw={600} truncate>{row.medication_name}</Text>
                    {row.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
                </Group>
                <Text fz="xs" c="dimmed" truncate>{[row.strength, row.dose && `Dose ${row.dose}`, row.route].filter(Boolean).join(' · ') || '—'}</Text>
            </Box>
            {recorded ? (
                <Badge variant="light" radius="sm" style={{ flexShrink: 0, background: `${tone.c}1A`, color: tone.c }}>{tone.t}</Badge>
            ) : locked ? (
                <Text fz="xs" c="dimmed" style={{ flexShrink: 0 }}>Round ended</Text>
            ) : (
                <Group gap={6} wrap="nowrap" style={{ flexShrink: 0 }}>
                    <Button size="xs" radius="xl" color="teal" leftSection={<IconCheck size={14} />} onClick={() => onGiven(row)}>Given</Button>
                    <Tooltip label="Refused"><ActionIcon size="md" radius="xl" variant="light" color="red" onClick={() => onOutcome(row, 'R')}><IconCircleX size={15} /></ActionIcon></Tooltip>
                    <Tooltip label="Omitted"><ActionIcon size="md" radius="xl" variant="light" color="orange" onClick={() => onOutcome(row, 'O')}><IconBan size={15} /></ActionIcon></Tooltip>
                </Group>
            )}
        </Group>
    );
}

/** Resident row — expands to their meds. */
function ResidentRow({ resident, isNext, expanded, onToggle, locked, onGiven, onOutcome, isMobile }) {
    const m = metrics(resident);
    const st = statusOf(m);
    const rows = resident.rows ?? [];
    const scheduled = rows.filter((r) => !r.as_required);
    const prn = rows.filter((r) => r.as_required);
    const first = scheduled[0];
    const allergies = cleanAllergies(resident.allergies);

    return (
        <Box>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={12} style={{ cursor: 'pointer', borderTop: '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}
                onClick={onToggle}
                onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#F7F9FC, var(--mantine-color-dark-5))'; }}
                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 220px', minWidth: 0 }}>
                    <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={40}>{initials(resident.name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={6} wrap="nowrap">
                            <Text fz="sm" fw={700} c={INK} truncate>{resident.name}</Text>
                            {isNext && <Badge size="xs" radius="sm" style={{ background: INK, color: '#fff' }}>NEXT</Badge>}
                            {allergies.length > 0 && <Tooltip label={`Allergies: ${allergies.join(', ')}`}><ThemeIcon variant="transparent" color="red" size={15}><IconAlertTriangle size={12} /></ThemeIcon></Tooltip>}
                        </Group>
                        <Text fz="xs" c="dimmed" truncate>{[`Room ${resident.room || '—'}`, first?.medication_name ?? (m.complete ? 'all doses given' : null)].filter(Boolean).join(' · ')}</Text>
                    </Box>
                </Group>
                <Text fz="sm" fw={700} c={m.nextDue ? ORANGE : 'dimmed'} style={{ width: 70, flexShrink: 0 }} visibleFrom="md">{m.nextDue || '—'}</Text>
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '1 1 150px', minWidth: 100 }} visibleFrom="sm">
                    <Box style={{ flex: 1, minWidth: 0 }}><Progress value={m.total ? (m.done / m.total) * 100 : 0} color={m.complete ? 'teal' : 'orange'} radius="xl" size="sm" /></Box>
                    <Text fz="xs" fw={700} c="dimmed">{m.done}/{m.total}</Text>
                </Group>
                <Group gap={6} wrap="nowrap" style={{ width: 108, flexShrink: 0 }}>
                    <Box w={7} h={7} style={{ borderRadius: '50%', background: st.color, flexShrink: 0 }} />
                    <Text fz="xs" fw={600} style={{ color: st.color }} truncate>{st.label}</Text>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={17} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>
            <Collapse in={expanded}>
                <Box px="md" pb="sm" pt={0} style={{ background: 'light-dark(#F7F9FC, var(--mantine-color-dark-7))' }}>
                    {rows.length === 0
                        ? <Text fz="sm" c="dimmed" py="sm">No medications in this round.</Text>
                        : <Stack gap={0}>{[...scheduled, ...prn].map((row, i) => <MedLine key={i} row={row} locked={locked} onGiven={onGiven} onOutcome={onOutcome} />)}</Stack>}
                </Box>
            </Collapse>
        </Box>
    );
}

export default function MedicationRound({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {}, home }) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const isManager = usePage().props?.auth?.user?.role === 'manager';

    const [activeRound, setActiveRound] = useState(currentRound);
    const [expandedId, setExpandedId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [tab, setTab] = useState('all');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [recordOpened, record] = useDisclosure(false);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const roundClosed = Boolean(closures?.[meta.key]);
    const closure = closures?.[meta.key] ?? null;

    // Per-round done/total (for the round tabs).
    const roundCounts = useMemo(() => {
        const out = {};
        rounds.forEach((r) => {
            const sched = (grid[r.key] ?? []).flatMap((res) => (res.rows ?? []).filter((row) => !row.as_required));
            out[r.key] = { done: sched.filter((s) => s.code).length, total: sched.length };
        });
        return out;
    }, [rounds, grid]);
    const roundDone = roundCounts[meta.key]?.done ?? 0;
    const roundTotal = roundCounts[meta.key]?.total ?? 0;

    // The "next" resident — earliest uncoded scheduled slot in this round.
    const nextResidentId = useMemo(() => {
        let best = null;
        residents.forEach((r) => {
            (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.slot).forEach((row) => {
                if (!best || row.slot < best.slot) best = { id: r.client_id, slot: row.slot };
            });
        });
        return best?.id ?? null;
    }, [residents]);

    // Filter tabs.
    const withM = residents.map((r) => ({ r, m: metrics(r) }));
    const overdueCount = withM.filter((x) => x.m.overdue > 0).length;
    const doneCount = withM.filter((x) => x.m.complete).length;
    const shown = withM.filter((x) => tab === 'overdue' ? x.m.overdue > 0 : tab === 'done' ? x.m.complete : true).map((x) => x.r);

    // Day-wide "Today" donut (across every round).
    const day = useMemo(() => {
        const sched = Object.values(grid).flat().flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const given = sched.filter((r) => isGiven(r.code)).length;
        const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
        const scheduled = sched.filter((r) => !r.code && r.status !== 'overdue').length;
        return { given, overdue, scheduled, total: given + overdue + scheduled };
    }, [grid]);
    const dseg = (n) => (day.total ? (n / day.total) * 100 : 0);

    // Most urgent overdue dose → Alert card.
    const alert = useMemo(() => {
        let best = null;
        residents.forEach((r) => {
            (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.status === 'overdue' && row.slot).forEach((row) => {
                if (!best || row.slot < best.slot) best = { slot: row.slot, med: row.medication_name, resident: r.name };
            });
        });
        return best;
    }, [residents]);

    // Recent activity — today's recorded doses (newest first).
    const activity = useMemo(() => {
        const out = [];
        Object.values(grid).flat().forEach((r) => (r.rows ?? []).forEach((row) => {
            if (row.code && row.recorded_at) out.push({
                med: row.medication_name, prn: row.as_required, given: isGiven(row.code),
                label: row.as_required && isGiven(row.code) ? 'PRN given' : (isGiven(row.code) ? 'given' : (CODE_LABELS[row.code] ?? 'recorded')),
                at: row.recorded_at, by: row.recorded_by,
            });
        }));
        return out.sort((a, b) => String(b.at).localeCompare(String(a.at))).slice(0, 6);
    }, [grid]);

    const statusWord = roundClosed ? 'completed' : paused ? 'paused' : (roundDone === 0 ? 'not started' : roundDone >= roundTotal ? 'review' : 'in progress');

    const reload = (params) => router.get(ENDPOINT, { date, ...params }, { preserveScroll: true, preserveState: true });
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

    const RoundIcon = ROUND_ICONS[meta.key] ?? IconClockHour4;

    return (
        <AppShell title="Medication round">
            <Head title="Medication round" />
            <Box>
                {/* Sub-header */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="md">
                    <Group gap={12} wrap="nowrap">
                        <ThemeIcon variant="light" color="indigo" size={42} radius="md"><RoundIcon size={22} stroke={1.7} /></ThemeIcon>
                        <Box>
                            <Text fz={16} fw={800} c={INK} lh={1.2}>{meta.label} round</Text>
                            <Text fz={11.5} c="dimmed">{[meta.window, fmtDate(date), statusWord].filter(Boolean).join(' · ')}</Text>
                        </Box>
                    </Group>
                    <Group gap="sm" wrap="wrap">
                        <Group gap={6} wrap="nowrap" style={{ padding: '7px 14px', borderRadius: 10, background: '#fff', border: '1px solid #E4E7EC' }}>
                            <Text fz="sm" c="dimmed" fw={500}>Progress</Text>
                            <Text fz="sm" fw={800} c={INK}>{roundDone}/{roundTotal}</Text>
                        </Group>
                        {roundClosed
                            ? (isManager && <Button radius="md" variant="light" color="indigo" leftSection={<IconLockOpen size={16} />} onClick={reopenRound}>Re-open</Button>)
                            : (
                                <>
                                    <Button radius="md" variant="default" leftSection={paused ? <IconPlayerPlay size={16} /> : <IconPlayerPause size={16} />} onClick={() => setPaused((p) => !p)}>{paused ? 'Resume' : 'Pause'}</Button>
                                    <Button radius="md" leftSection={<IconLock size={16} />} onClick={endRound} style={{ background: INK }}>End round</Button>
                                </>
                            )}
                    </Group>
                </Group>

                {/* Round tabs */}
                <Box style={{ ...card, padding: 6, marginBottom: 18 }}>
                    <Group gap={4} wrap="wrap">
                        {rounds.map((r) => <RoundTab key={r.key} round={r} active={r.key === activeRound}
                            done={roundCounts[r.key]?.done ?? 0} total={roundCounts[r.key]?.total ?? 0}
                            onClick={() => { setActiveRound(r.key); setExpandedId(null); setTab('all'); }} />)}
                    </Group>
                </Box>

                {/* Main + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Residents table */}
                    <Box style={{ ...card, flex: '3 1 460px', minWidth: 0 }}>
                        <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                            <Text fz={17} fw={800} c={INK}>Residents to give meds</Text>
                            <Group gap={4} style={{ background: '#F1F3F7', borderRadius: 10, padding: 3 }}>
                                {[{ k: 'all', l: `All ${residents.length}` }, { k: 'overdue', l: `Overdue ${overdueCount}` }, { k: 'done', l: `Done ${doneCount}` }].map((t) => (
                                    <Box key={t.k} component="button" onClick={() => setTab(t.k)} style={{
                                        border: 'none', cursor: 'pointer', borderRadius: 8, padding: '5px 12px', fontSize: 13, fontWeight: 600,
                                        background: tab === t.k ? '#fff' : 'transparent', color: tab === t.k ? INK : '#667085',
                                        boxShadow: tab === t.k ? '0 1px 2px rgba(16,24,40,0.1)' : 'none',
                                    }}>{t.l}</Box>
                                ))}
                            </Group>
                        </Group>
                        {!isMobile && (
                            <Group gap="md" wrap="nowrap" px="md" pb={6} c="dimmed">
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 220px', letterSpacing: 0.5 }}>Resident</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 70, letterSpacing: 0.5 }} visibleFrom="md">Next due</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 150px', letterSpacing: 0.5 }} visibleFrom="sm">Progress</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 108, letterSpacing: 0.5 }}>Status</Text>
                                <Box style={{ width: 28 }} />
                            </Group>
                        )}
                        {shown.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py={48}>No residents in this view.</Text>
                            : shown.map((r) => <ResidentRow key={r.client_id} resident={r} isMobile={isMobile} isNext={r.client_id === nextResidentId}
                                expanded={expandedId === r.client_id} onToggle={() => setExpandedId(expandedId === r.client_id ? null : r.client_id)}
                                locked={roundClosed} onGiven={giveDose} onOutcome={outcomeDose} />)}
                        <Box py={4} />
                    </Box>

                    {/* Right rail */}
                    <Stack gap={18} style={{ flex: '1 1 280px', minWidth: 0, maxWidth: isMobile ? undefined : 340 }}>
                        {/* Today */}
                        <Box style={{ ...card, padding: 18 }}>
                            <Text fz={16} fw={800} c={INK} mb={8}>Today</Text>
                            <Group gap="lg" wrap="nowrap" align="center">
                                <RingProgress size={104} thickness={11} roundCaps
                                    sections={[{ value: dseg(day.given), color: GREEN }, { value: dseg(day.overdue), color: ORANGE }, { value: dseg(day.scheduled), color: GRAY }]}
                                    label={<Box ta="center"><Text fz={18} fw={800} c={INK} lh={1}>{day.given}/{day.total}</Text><Text fz={9} c="dimmed">given</Text></Box>} />
                                <Stack gap={8} style={{ flex: 1 }}>
                                    {[{ c: GREEN, l: 'Given', v: day.given }, { c: ORANGE, l: 'Overdue', v: day.overdue }, { c: GRAY, l: 'Scheduled', v: day.scheduled }].map((s) => (
                                        <Group key={s.l} justify="space-between" wrap="nowrap">
                                            <Group gap={7} wrap="nowrap"><Box w={9} h={9} style={{ borderRadius: '50%', background: s.c }} /><Text fz="sm" c="dimmed">{s.l}</Text></Group>
                                            <Text fz="sm" fw={700} c={INK}>{s.v}</Text>
                                        </Group>
                                    ))}
                                </Stack>
                            </Group>
                        </Box>

                        {/* Alert */}
                        {alert && !roundClosed && (
                            <Box style={{ ...card, padding: 18, background: 'light-dark(#FFF7EF, var(--mantine-color-dark-6))', borderColor: '#F3D9BE' }}>
                                <Group gap={8} mb={6} wrap="nowrap"><IconAlertTriangle size={18} color={ORANGE} /><Text fz={16} fw={800} c={INK}>Alert</Text></Group>
                                <Text fz="sm" c="dimmed" lh={1.4}>
                                    <b style={{ color: INK }}>{alert.med}</b> was due at {alert.slot} for {alert.resident} and is now overdue. Record or omit before the round ends.
                                </Text>
                                <Button component={Link} href="/frontend2/missed-doses" mt="sm" fullWidth radius="md"
                                    variant="light" color="orange" rightSection={<IconArrowRight size={16} />}>Resolve now</Button>
                            </Box>
                        )}

                        {/* Recent activity */}
                        <Box style={{ ...card, padding: 18 }}>
                            <Text fz={16} fw={800} c={INK} mb={8}>Recent activity</Text>
                            {activity.length === 0
                                ? <Text fz="sm" c="dimmed">No doses recorded yet today.</Text>
                                : (
                                    <Stack gap={12}>
                                        {activity.map((a, i) => (
                                            <Group key={i} gap={10} wrap="nowrap" align="flex-start">
                                                <Box w={8} h={8} mt={5} style={{ borderRadius: '50%', flexShrink: 0, background: a.prn ? '#6E5BE6' : a.given ? GREEN : ORANGE }} />
                                                <Box style={{ flex: 1, minWidth: 0 }}>
                                                    <Text fz="sm" fw={600} c={INK} truncate>{a.med} {a.label}</Text>
                                                    <Text fz="xs" c="dimmed">{a.at}{a.by ? ` · by ${a.by}` : ''}</Text>
                                                </Box>
                                            </Group>
                                        ))}
                                    </Stack>
                                )}
                        </Box>
                    </Stack>
                </Box>

                {closure?.by && <Text fz="xs" c="dimmed" mt="md">Round ended by {closure.by}{closure.at ? ` at ${closure.at}` : ''}.</Text>}
            </Box>

            <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
        </AppShell>
    );
}
