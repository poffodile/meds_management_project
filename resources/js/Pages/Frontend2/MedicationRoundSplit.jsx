import { useState, useMemo } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Avatar, Button, ActionIcon, ThemeIcon,
    Progress, Tooltip, RingProgress, Divider, Transition,
} from '@mantine/core';
import {
    IconChevronRight, IconAlertTriangle, IconLock, IconLockOpen, IconPlayerPause,
    IconPlayerPlay, IconClockHour4, IconArrowRight, IconX, IconUser,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import {
    V1THEME, metrics, statusOf, cleanAllergies, fmtDate, ageFromDob, isGiven, MedLine, RoundTab,
} from './MedicationRound';

const ENDPOINT = '/frontend2/medication-round-split';
const { INK, TXT, ORANGE, GREEN, GRAY, card, ROUND_ICONS } = V1THEME;

// A springy "overshoot-free" easing that makes the shrink/slide feel physical.
const SPRING = 'cubic-bezier(0.22, 1, 0.36, 1)';
// Custom detail entrance: slides in from the right, fades and scales up from the list edge.
const DETAIL_TRANSITION = {
    in: { opacity: 1, transform: 'translateX(0) scale(1)' },
    out: { opacity: 0, transform: 'translateX(34px) scale(0.975)' },
    common: { transformOrigin: 'left center' },
    transitionProperty: 'transform, opacity',
};
// Keyframe for the staggered cascade of the detail's contents.
const STAGGER_CSS = '@keyframes splitFadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}';
const rise = (delay) => ({ animation: `splitFadeUp .45s ${SPRING} both`, animationDelay: `${delay}ms` });

/** A resident row in the master list. Full columns when wide; compact when the list has shrunk (narrow). */
function SplitRow({ resident, narrow, active, isNext, isFirst, isSm, onClick }) {
    const m = metrics(resident);
    const st = statusOf(m);
    const first = (resident.rows ?? []).filter((r) => !r.as_required)[0];
    const allergies = cleanAllergies(resident.allergies);
    const compact = isSm || narrow;
    return (
        <Group gap={isSm ? 8 : 14} wrap="nowrap" align="center" px={isSm ? 2 : 6} py={isSm ? 12 : 14} onClick={onClick}
            style={{ cursor: 'pointer', borderTop: isFirst ? 'none' : '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))', background: active ? 'light-dark(#EAF1FA, var(--mantine-color-dark-5))' : 'transparent', borderRadius: 10, boxShadow: active ? 'inset 3px 0 0 #3A7CA5' : 'none', transform: active ? 'translateX(2px)' : 'none', transition: `background .2s ease, box-shadow .2s ease, transform .25s ${SPRING}` }}
            onMouseEnter={(e) => { if (!active) e.currentTarget.style.background = 'light-dark(#F7F9FC, var(--mantine-color-dark-5))'; }}
            onMouseLeave={(e) => { if (!active) e.currentTarget.style.background = 'transparent'; }}>
            <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 200px', minWidth: 0 }}>
                <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={40}>{initials(resident.name ?? '')}</Avatar>
                <Box style={{ minWidth: 0 }}>
                    <Group gap={6} wrap="nowrap">
                        <Text fz="sm" fw={700} c={TXT} truncate>{resident.name}</Text>
                        {isNext && <Badge size="xs" radius="sm" style={{ background: INK, color: '#fff' }}>NEXT</Badge>}
                        {allergies.length > 0 && <Tooltip label={`Allergies: ${allergies.join(', ')}`}><ThemeIcon variant="transparent" color="red" size={15}><IconAlertTriangle size={12} /></ThemeIcon></Tooltip>}
                    </Group>
                    <Text fz="xs" c="dimmed" truncate>{[`Room ${resident.room || '—'}`, !narrow && (first?.medication_name ?? (m.complete ? 'all doses given' : null))].filter(Boolean).join(' · ')}</Text>
                </Box>
            </Group>
            {!narrow && <Text fz="sm" fw={700} c={m.nextDue ? ORANGE : 'dimmed'} style={{ width: 70, flexShrink: 0 }} visibleFrom="md">{m.nextDue || '—'}</Text>}
            {!narrow && (
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '1 1 150px', minWidth: 100 }} visibleFrom="sm">
                    <Box style={{ flex: 1, minWidth: 0 }}><Progress value={m.total ? (m.done / m.total) * 100 : 0} color={m.complete ? 'teal' : 'orange'} radius="xl" size="sm" /></Box>
                    <Text fz="xs" fw={700} c="dimmed">{m.done}/{m.total}</Text>
                </Group>
            )}
            <Group gap={6} wrap="nowrap" justify={compact ? 'flex-end' : 'flex-start'} style={{ width: narrow ? 'auto' : (isSm ? 16 : 108), flexShrink: 0 }}>
                <Tooltip label={st.label} disabled={!compact}><Box w={9} h={9} style={{ borderRadius: '50%', background: st.color, flexShrink: 0 }} /></Tooltip>
                {!compact && <Text fz="xs" fw={600} style={{ color: st.color }} truncate>{st.label}</Text>}
                {narrow && <Text fz="xs" fw={700} c="dimmed">{m.done}/{m.total}</Text>}
            </Group>
            <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}><IconChevronRight size={16} /></ActionIcon>
        </Group>
    );
}

/** The detail pane that opens beside the shrunken list. */
function DetailPane({ resident, locked, onGiven, onOutcome, onClose, isSm }) {
    const scheduled = (resident.rows ?? []).filter((r) => !r.as_required);
    const prn = (resident.rows ?? []).filter((r) => r.as_required);
    const allergies = cleanAllergies(resident.allergies);
    const age = resident.dob ? ageFromDob(resident.dob) : null;
    const chips = [
        ['Age', age ? `${age} yrs` : null], ['Gender', resident.gender], ['NHS no.', resident.nhs],
        ['Weight', resident.weight], ['Mobility', resident.mobility], ['Diet', resident.diet],
    ].filter(([, v]) => v);
    return (
        <Box style={{ background: 'light-dark(#F7F9FC, var(--mantine-color-dark-7))', borderRadius: 18, padding: isSm ? '18px 16px' : '24px 26px' }}>
            <style>{STAGGER_CSS}</style>
            <Group justify="space-between" align="flex-start" wrap="nowrap" mb="lg" style={rise(30)}>
                <Group gap="md" wrap="nowrap" style={{ minWidth: 0 }}>
                    <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={52}>{initials(resident.name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz={19} fw={800} c={TXT} truncate>{resident.name}</Text>
                        <Text fz={13} c="dimmed">{[resident.room && `Room ${resident.room}`, age && `${age} yrs`, resident.gender].filter(Boolean).join(' · ')}</Text>
                    </Box>
                </Group>
                <Group gap={6} wrap="nowrap" style={{ flexShrink: 0 }}>
                    <Button component={Link} href={`/frontend2/residents/${resident.client_id}`} size="compact-sm" radius="xl"
                        leftSection={<IconUser size={14} />} style={{ background: '#3A7CA5', color: '#fff', boxShadow: '0 4px 10px rgba(58,124,165,0.22)' }}>View profile</Button>
                    <ActionIcon variant="subtle" color="gray" radius="xl" size="lg" onClick={onClose}><IconX size={18} /></ActionIcon>
                </Group>
            </Group>
            {(allergies.length > 0 || (resident.risk_flags ?? []).length > 0) && (
                <Group gap={8} wrap="wrap" mb="md" style={rise(90)}>
                    {allergies.map((a, i) => <Badge key={`a${i}`} color="red" variant="light" radius="sm" size="lg" leftSection={<IconAlertTriangle size={12} />}>{a}</Badge>)}
                    {(resident.risk_flags ?? []).map((r, i) => <Badge key={`r${i}`} color="orange" variant="light" radius="sm" size="lg">{r}</Badge>)}
                </Group>
            )}
            {chips.length > 0 && (
                <Group gap={10} wrap="wrap" mb="lg" style={rise(140)}>
                    {chips.map(([k, v]) => (
                        <Box key={k} style={{ padding: '8px 14px', borderRadius: 11, background: 'light-dark(#ffffff, var(--mantine-color-dark-5))', border: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))' }}>
                            <Text fz={10} fw={700} c="dimmed" tt="uppercase" style={{ letterSpacing: 0.5 }}>{k}</Text>
                            <Text fz={13.5} fw={700} c={TXT}>{v}</Text>
                        </Box>
                    ))}
                </Group>
            )}
            <Divider label="Medications this round" labelPosition="left" mt={4} mb={6} style={rise(180)} />
            {(resident.rows ?? []).length === 0
                ? <Text fz="sm" c="dimmed" py="sm">No medications in this round.</Text>
                : (
                    <Stack gap={0}>
                        {[...scheduled, ...prn].map((row, i) => (
                            <Box key={i} style={rise(220 + i * 55)}>
                                <MedLine row={row} locked={locked} onGiven={onGiven} onOutcome={onOutcome} isSm={isSm} />
                            </Box>
                        ))}
                    </Stack>
                )}
        </Box>
    );
}

export default function MedicationRoundSplit({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {}, home }) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    const isManager = usePage().props?.auth?.user?.role === 'manager';

    const [activeRound, setActiveRound] = useState(currentRound);
    const [openId, setOpenId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [tab, setTab] = useState('all');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [recordOpened, record] = useDisclosure(false);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const roundClosed = Boolean(closures?.[meta.key]);
    const closure = closures?.[meta.key] ?? null;

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

    const nextResidentId = useMemo(() => {
        let best = null;
        residents.forEach((r) => (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.slot).forEach((row) => {
            if (!best || row.slot < best.slot) best = { id: r.client_id, slot: row.slot };
        }));
        return best?.id ?? null;
    }, [residents]);

    const withM = residents.map((r) => ({ r, m: metrics(r) }));
    const overdueCount = withM.filter((x) => x.m.overdue > 0).length;
    const doneCount = withM.filter((x) => x.m.complete).length;
    const shown = withM.filter((x) => tab === 'overdue' ? x.m.overdue > 0 : tab === 'done' ? x.m.complete : true).map((x) => x.r);

    const day = useMemo(() => {
        const sched = Object.values(grid).flat().flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const given = sched.filter((r) => isGiven(r.code)).length;
        const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
        const scheduled = sched.filter((r) => !r.code && r.status !== 'overdue').length;
        return { given, overdue, scheduled, total: given + overdue + scheduled };
    }, [grid]);
    const dseg = (n) => (day.total ? (n / day.total) * 100 : 0);

    const alert = useMemo(() => {
        let best = null;
        residents.forEach((r) => (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.status === 'overdue' && row.slot).forEach((row) => {
            if (!best || row.slot < best.slot) best = { slot: row.slot, med: row.medication_name, resident: r.name };
        }));
        return best;
    }, [residents]);

    const activity = useMemo(() => {
        const out = [];
        Object.values(grid).flat().forEach((r) => (r.rows ?? []).forEach((row) => {
            if (row.code && row.recorded_at) out.push({
                med: row.medication_name, prn: row.as_required, given: isGiven(row.code),
                label: row.as_required && isGiven(row.code) ? 'PRN given' : (isGiven(row.code) ? 'given' : 'recorded'),
                at: row.recorded_at, by: row.recorded_by,
            });
        }));
        return out.sort((a, b) => String(b.at).localeCompare(String(a.at))).slice(0, 6);
    }, [grid]);

    const statusWord = roundClosed ? 'completed' : paused ? 'paused' : (roundDone === 0 ? 'not started' : roundDone >= roundTotal ? 'review' : 'in progress');

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
    const selected = residents.find((r) => r.client_id === openId) ?? null;

    return (
        <AppShell title="Medication round">
            <Head title="Medication round · split" />
            <Box px={{ base: 0, sm: 10 }} pb={14}>
                {/* Sub-header */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb={18}>
                    <Group gap={12} wrap="nowrap">
                        <ThemeIcon variant="light" color="indigo" size={42} radius="md"><RoundIcon size={22} stroke={1.7} /></ThemeIcon>
                        <Box>
                            <Text fz={16} fw={800} c={TXT} lh={1.2}>{meta.label} round</Text>
                            <Text fz={11.5} c="dimmed">{[meta.window, fmtDate(date), statusWord].filter(Boolean).join(' · ')}</Text>
                        </Box>
                    </Group>
                    <Group gap={12} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <Group gap={9} wrap="nowrap" style={{ padding: '9px 14px', borderRadius: 12, background: 'light-dark(#ffffff, var(--mantine-color-dark-6))', boxShadow: '0 2px 6px rgba(20,50,80,0.05)' }}>
                            <Text fz={13} fw={600} c="#7a8590">Progress</Text>
                            <Text fz={13.5} fw={800} c="#3A7CA5">{roundDone} / {roundTotal}</Text>
                        </Group>
                        {roundClosed
                            ? (isManager && <Button radius={999} variant="default" leftSection={<IconLockOpen size={15} />} onClick={reopenRound}>Re-open</Button>)
                            : (
                                <>
                                    <Button radius={999} variant="default" leftSection={paused ? <IconPlayerPlay size={14} /> : <IconPlayerPause size={14} />} onClick={() => setPaused((p) => !p)}
                                        style={{ border: '1.5px solid #cddbe6', color: '#3A7CA5', paddingInline: 18 }}>{paused ? 'Resume' : 'Pause'}</Button>
                                    <Button radius={999} leftSection={<IconLock size={15} />} onClick={endRound}
                                        style={{ background: '#3A7CA5', paddingInline: 22, boxShadow: '0 8px 18px rgba(58,124,165,0.28)' }}>End round</Button>
                                </>
                            )}
                    </Group>
                </Group>

                {/* Round tabs */}
                <Box style={{ ...card, borderRadius: 14, boxShadow: '0 2px 6px rgba(20,50,80,0.05)', padding: 6, marginBottom: 18, overflowX: 'auto' }}>
                    <Group gap={8} wrap="nowrap" style={{ minWidth: 'max-content' }}>
                        {rounds.map((r) => <RoundTab key={r.key} round={r} active={r.key === activeRound}
                            done={roundCounts[r.key]?.done ?? 0} total={roundCounts[r.key]?.total ?? 0}
                            onClick={() => { setActiveRound(r.key); setOpenId(null); setTab('all'); }} />)}
                    </Group>
                </Box>

                {/* Main + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 22, alignItems: 'flex-start' }}>
                    {/* Residents — master/detail */}
                    <Box style={{ ...card, flex: '3 1 460px', minWidth: 0, padding: isSm ? '16px 14px' : '24px 26px' }}>
                        <Group justify="space-between" align="center" pb={16} wrap="wrap" gap="sm">
                            <Text fz={17} fw={800} c={TXT}>Residents to give meds</Text>
                            <Group gap={4} style={{ background: 'light-dark(#F1F3F7, var(--mantine-color-dark-8))', borderRadius: 10, padding: 3 }}>
                                {[{ k: 'all', l: `All ${residents.length}` }, { k: 'overdue', l: `Overdue ${overdueCount}` }, { k: 'done', l: `Done ${doneCount}` }].map((t) => (
                                    <Box key={t.k} component="button" onClick={() => setTab(t.k)} style={{
                                        border: 'none', cursor: 'pointer', borderRadius: 8, padding: '5px 12px', fontSize: 13, fontWeight: 600,
                                        background: tab === t.k ? 'light-dark(#ffffff, var(--mantine-color-dark-6))' : 'transparent', color: tab === t.k ? TXT : '#667085',
                                        boxShadow: tab === t.k ? '0 1px 2px rgba(16,24,40,0.1)' : 'none',
                                    }}>{t.l}</Box>
                                ))}
                            </Group>
                        </Group>
                        {!isMobile && !selected && (
                            <Group gap={14} wrap="nowrap" px={6} pb={12} c="dimmed" style={{ borderBottom: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))' }}>
                                <Text fz={10.5} fw={700} tt="uppercase" style={{ flex: '2 1 200px', letterSpacing: 0.6 }}>Resident</Text>
                                <Text fz={10.5} fw={700} tt="uppercase" style={{ width: 70, letterSpacing: 0.6 }} visibleFrom="md">Next due</Text>
                                <Text fz={10.5} fw={700} tt="uppercase" style={{ flex: '1 1 150px', letterSpacing: 0.6 }} visibleFrom="sm">Progress</Text>
                                <Text fz={10.5} fw={700} tt="uppercase" style={{ width: 108, letterSpacing: 0.6 }}>Status</Text>
                                <Box style={{ width: 20 }} />
                            </Group>
                        )}

                        <Box style={{ display: 'flex', gap: 20, alignItems: 'flex-start' }}>
                            <Box style={{ flexBasis: selected ? (isSm ? '100%' : '250px') : '100%', flexGrow: selected ? 0 : 1, flexShrink: 0, minWidth: 0, transition: `flex-basis .46s ${SPRING}`, display: (isSm && selected) ? 'none' : 'block' }}>
                                {shown.length === 0
                                    ? <Text fz="sm" c="dimmed" ta="center" py={48}>No residents in this view.</Text>
                                    : shown.map((r, idx) => (
                                        <SplitRow key={r.client_id} resident={r} narrow={!!selected} isSm={isSm} isNext={r.client_id === nextResidentId} isFirst={idx === 0}
                                            active={openId === r.client_id} onClick={() => setOpenId(openId === r.client_id ? null : r.client_id)} />
                                    ))}
                            </Box>
                            <Transition mounted={!!selected} transition={DETAIL_TRANSITION} duration={380} timingFunction={SPRING}>
                                {(styles) => (
                                    <Box style={{ ...styles, flex: 1, minWidth: 0 }}>
                                        {selected && <DetailPane key={selected.client_id} resident={selected} locked={roundClosed} onGiven={giveDose} onOutcome={outcomeDose} onClose={() => setOpenId(null)} isSm={isSm} />}
                                    </Box>
                                )}
                            </Transition>
                        </Box>
                    </Box>

                    {/* Right rail */}
                    <Stack gap={22} align={isMobile ? 'stretch' : 'flex-end'} style={{ flex: '1 1 320px', minWidth: 0, maxWidth: isMobile ? undefined : 350, paddingRight: isMobile ? 0 : 14 }}>
                        <Box style={{ ...card, width: '100%', maxWidth: isMobile ? undefined : 290, padding: isSm ? '18px 16px' : '22px 24px' }}>
                            <Text fz={16} fw={800} c={TXT} mb={16}>Today</Text>
                            <Group gap={18} wrap="nowrap" align="center">
                                <RingProgress size={106} thickness={11} roundCaps style={{ flexShrink: 0 }}
                                    sections={[{ value: dseg(day.given), color: GREEN }, { value: dseg(day.overdue), color: ORANGE }, { value: dseg(day.scheduled), color: GRAY }]}
                                    label={<Box ta="center"><Text fz={17} fw={800} c={TXT} lh={1}>{day.given}/{day.total}</Text><Text fz={9} c="dimmed">given</Text></Box>} />
                                <Stack gap={11} style={{ flex: 1, minWidth: 0 }}>
                                    {[{ c: GREEN, l: 'Given', v: day.given }, { c: ORANGE, l: 'Overdue', v: day.overdue }, { c: GRAY, l: 'Scheduled', v: day.scheduled }].map((s) => (
                                        <Group key={s.l} justify="space-between" wrap="nowrap" gap={8}>
                                            <Group gap={8} wrap="nowrap" style={{ minWidth: 0 }}><Box w={9} h={9} style={{ borderRadius: '50%', background: s.c, flexShrink: 0 }} /><Text fz="sm" c="dimmed" truncate>{s.l}</Text></Group>
                                            <Text fz="sm" fw={700} c={TXT}>{s.v}</Text>
                                        </Group>
                                    ))}
                                </Stack>
                            </Group>
                        </Box>

                        {alert && !roundClosed && (
                            <Box style={{ ...card, width: '100%', maxWidth: isMobile ? undefined : 290, padding: isSm ? '18px 16px' : '22px 24px', border: '1px solid #F4D2B4' }}>
                                <Group gap={9} mb={10} wrap="nowrap">
                                    <Box style={{ width: 32, height: 32, borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(245,131,33,0.14)' }}><IconAlertTriangle size={17} color="#F58321" /></Box>
                                    <Text fz={16} fw={700} c={TXT}>Alert</Text>
                                </Group>
                                <Text fz={12.5} c="#7a8590" lh={1.5} mb={13}>
                                    <b style={{ color: TXT }}>{alert.med}</b> was due at {alert.slot} for {alert.resident} and is now overdue. Record or omit before the round ends.
                                </Text>
                                <Button component={Link} href="/frontend2/missed-doses" fullWidth radius={12}
                                    rightSection={<IconChevronRight size={15} />} style={{ background: 'rgba(245,131,33,0.12)', color: '#cf6a12' }}>Resolve now</Button>
                            </Box>
                        )}

                        <Box style={{ ...card, width: '100%', maxWidth: isMobile ? undefined : 290, padding: isSm ? '18px 16px' : '22px 24px' }}>
                            <Text fz={16} fw={800} c={TXT} mb={16}>Recent activity</Text>
                            {activity.length === 0
                                ? <Text fz="sm" c="dimmed">No doses recorded yet today.</Text>
                                : (
                                    <Stack gap={14}>
                                        {activity.map((a, i) => (
                                            <Group key={i} gap={11} wrap="nowrap" align="flex-start">
                                                <Box w={8} h={8} mt={6} style={{ borderRadius: '50%', flexShrink: 0, background: a.prn ? '#6E5BE6' : a.given ? GREEN : ORANGE }} />
                                                <Box style={{ flex: 1, minWidth: 0 }}>
                                                    <Text fz="sm" fw={600} c={TXT} truncate>{a.med} {a.label}</Text>
                                                    <Text fz="xs" c="dimmed" truncate>{a.at}{a.by ? ` · by ${a.by}` : ''}</Text>
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
