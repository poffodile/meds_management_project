import { useState, useMemo } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Avatar, Button, ActionIcon, ThemeIcon,
    Progress, Tooltip, RingProgress, Divider, Transition, SegmentedControl,
} from '@mantine/core';
import {
    IconChevronRight, IconAlertTriangle, IconLock, IconLockOpen, IconPlayerPause,
    IconPlayerPlay, IconClockHour4, IconX, IconUser,
    IconPill, IconPencil, IconCheck, IconFileText,
} from '@tabler/icons-react';
import { CalendarRemove, ShieldSecurity, Box1, Profile2User } from 'iconsax-react';
import AppShell from '@frontend2/Layouts/AppShell';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';
import AdministerModal from './AdministerModal';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import {
    V1THEME, metrics, statusOf, cleanAllergies, fmtDate, ageFromDob, isGiven, RoundTab,
} from './MedicationRound';

const ENDPOINT = '/frontend2/medication-round-split-c';
const { INK, TXT, ORANGE, GREEN, GRAY, card, ROUND_ICONS } = V1THEME;

// Outcome donut palette — matches the owner's mockup (green / grey / purple / red).
const REFUSED = '#795076';
const MISSED = '#B23B30';

// Quick-action links — same softened square tiles as Split B.
const QUICK_ACTIONS = [
    { label: 'Missed doses', short: 'Missed', href: '/frontend2/missed-doses', color: 'red', hex: '#e03131', icon: CalendarRemove },
    { label: 'Controlled drugs', short: 'CDs', href: '/frontend2/controlled-drugs', color: 'grape', hex: '#ae3ec9', icon: ShieldSecurity },
    { label: 'Medication stock', short: 'Stock', href: '/frontend2/stock', color: 'blue', hex: '#1971c2', icon: Box1 },
    { label: 'Clients', short: 'Clients', href: '/frontend2/residents', color: 'teal', hex: '#0ca678', icon: Profile2User },
];

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
const fmtDob = (d) => { const t = Date.parse(d); return Number.isNaN(t) ? d : new Date(t).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }); };

// A distinct badge colour per recorded outcome.
const OUTCOME_COLORS = {
    'Given': { c: '#1F8A5B', bg: 'light-dark(#EDF4DD, rgba(31,138,91,0.20))' },
    'Self-administered': { c: '#2C7E7A', bg: 'light-dark(#E2F4F2, rgba(44,126,122,0.22))' },
    'Refused': { c: '#795076', bg: 'light-dark(#F1E9EF, rgba(121,80,118,0.26))' },
    'Vomited': { c: '#7A5AA0', bg: 'light-dark(#EEE9F6, rgba(122,90,160,0.24))' },
    'Missed': { c: '#B23B30', bg: 'light-dark(#F8E7E5, rgba(178,59,48,0.22))' },
    'Held': { c: '#B5601A', bg: 'light-dark(#FDEBDB, rgba(181,96,26,0.22))' },
    'Delayed': { c: '#3E6FB0', bg: 'light-dark(#E6EEF8, rgba(62,111,176,0.22))' },
    'Resident away': { c: '#69727C', bg: 'light-dark(#EEF1F4, rgba(105,114,124,0.24))' },
    'Omitted': { c: '#B5601A', bg: 'light-dark(#FDEBDB, rgba(181,96,26,0.22))' },
    'Withheld': { c: '#8A6D3B', bg: 'light-dark(#F5EEDD, rgba(138,109,59,0.22))' },
    'Not available': { c: '#69727C', bg: 'light-dark(#EEF1F4, rgba(105,114,124,0.24))' },
    'Sleeping': { c: '#5B6BB0', bg: 'light-dark(#E9ECF7, rgba(91,107,176,0.22))' },
};

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

/** A medication row in the detail pane. Recorded doses show by/witness + notes + time & outcome badge. */
function SplitMedLine({ row, locked, onAdminister, isSm, isFirst }) {
    const recorded = Boolean(row.code);
    const given = isGiven(row.code);
    // The administer modal stores the specific outcome as "Outcome — detail"; surface it for a per-outcome badge.
    const OUTCOME_WORDS = ['Given', 'Self-administered', 'Refused', 'Missed', 'Held', 'Delayed', 'Vomited', 'Resident away', 'Omitted', 'Withheld', 'Not available', 'Sleeping'];
    const reasonRaw = recorded ? (row.reason || '') : '';
    const reasonHead = reasonRaw.split(' — ')[0].trim();
    const specific = OUTCOME_WORDS.includes(reasonHead) ? reasonHead : null;
    const detail = specific ? reasonRaw.slice(reasonHead.length).replace(/^\s*—\s*/, '').trim() : reasonRaw;
    const badgeLabel = !recorded ? null
        : specific || (given ? (CODE_LABELS[row.code] || 'Given') : (row.code === 'R' ? 'Refused' : (CODE_LABELS[row.code] || 'Recorded')));
    const badge = badgeLabel
        ? { ...(OUTCOME_COLORS[badgeLabel] || { c: '#B5601A', bg: 'light-dark(#FDEBDB, rgba(181,96,26,0.22))' }), t: badgeLabel }
        : null;
    const sub = [row.dose, row.route, row.frequency].filter(Boolean).join(' · ') || [row.strength].filter(Boolean).join(' · ') || '—';
    const time = row.recorded_at || row.slot || (row.as_required ? 'PRN' : '');
    const reasonText = recorded && !given ? detail : '';
    const notesText = recorded ? (row.notes || '') : '';

    // .rc-badge / .rc-btn / "Round ended"
    const trailing = recorded
        ? <Box style={{ display: 'inline-flex', alignItems: 'center', padding: '5px 12px', borderRadius: 999, fontSize: 11.5, fontWeight: 700, whiteSpace: 'nowrap', background: badge.bg, color: badge.c }}>{badge.t}</Box>
        : locked
            ? <Text fz={11.5} c="#98A1AB">Round ended</Text>
            : <Box component="button" onClick={() => onAdminister(row)} style={{ border: 'none', cursor: 'pointer', fontFamily: 'inherit', whiteSpace: 'nowrap', padding: '8px 16px', borderRadius: 9, fontSize: 12.5, fontWeight: 700, background: '#13233F', color: '#fff', boxShadow: '0 4px 10px rgba(19,35,63,0.22)' }}>Administer</Box>;

    return (
        <Box style={{ borderTop: isFirst ? 'none' : '1px solid light-dark(#E7EAEF, var(--mantine-color-dark-5))' }}>
            <Group gap={13} wrap="nowrap" align="center" py={12}>
                {/* .rc-pill */}
                <Box style={{ width: 34, height: 34, flexShrink: 0, borderRadius: 9, background: 'light-dark(#F4F5F7, var(--mantine-color-dark-5))', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <IconPill size={16} stroke={1.8} color="#6B7682" />
                </Box>
                {/* .rc-rowmain */}
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz={13.5} fw={700} c={TXT} truncate>{row.medication_name}</Text>
                        {row.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
                        {row.as_required && <Badge size="xs" variant="light" color="violet" radius="sm">PRN</Badge>}
                    </Group>
                    <Text fz={11.5} c="#98A1AB" truncate mt={2}>{sub}</Text>
                    {recorded && (row.recorded_by || row.witnessed_by) && (
                        <Group gap={10} mt={6} wrap="wrap">
                            {row.recorded_by && (
                                <Group gap={5} wrap="nowrap"><IconPencil size={12} color="#5B7A9E" /><Text fz={11} fw={600} c="#566A86">{given ? 'Given' : 'Recorded'} by {row.recorded_by}</Text></Group>
                            )}
                            {row.witnessed_by && (
                                <Group gap={5} wrap="nowrap"><IconCheck size={12} stroke={2.6} color="#1F8A5B" /><Text fz={11} fw={600} c="#1F8A5B">Witnessed by {row.witnessed_by}</Text></Group>
                            )}
                        </Group>
                    )}
                    {recorded && (reasonText || notesText) && (
                        <Group gap={7} wrap="nowrap" align="flex-start" mt={6} style={{ background: 'light-dark(#F7F8FA, var(--mantine-color-dark-6))', border: '1px solid light-dark(#E4E9EF, var(--mantine-color-dark-4))', borderRadius: 9, padding: '7px 10px' }}>
                            <IconFileText size={12} color={reasonText ? (badge?.c ?? '#7E90A8') : '#7E90A8'} style={{ flexShrink: 0, marginTop: 2 }} />
                            <Box style={{ flex: 1, minWidth: 0 }}>
                                {reasonText && <Text fz={11} fw={600} c={badge?.c ?? '#5F6B7A'} style={{ lineHeight: 1.4 }} truncate>{reasonText}</Text>}
                                {notesText && <Text fz={11} c="#6B7684" style={{ lineHeight: 1.4 }} mt={reasonText ? 2 : 0}>{notesText}</Text>}
                            </Box>
                            {row.recorded_at && <Text fz={10.5} fw={600} c="#98A1AB" style={{ flexShrink: 0, whiteSpace: 'nowrap', marginTop: 1 }}>{row.recorded_at}</Text>}
                        </Group>
                    )}
                    {isSm && (
                        <Group gap={12} mt={10} justify="flex-end" wrap="nowrap">
                            {time && <Text fz={12} fw={600} c="#69727C">{time}</Text>}
                            {trailing}
                        </Group>
                    )}
                </Box>
                {/* .rc-time + trailing */}
                {!isSm && time && <Text fz={12} fw={600} c="#69727C" style={{ width: 44, textAlign: 'right', flexShrink: 0, fontVariantNumeric: 'tabular-nums' }}>{time}</Text>}
                {!isSm && <Box style={{ flexShrink: 0 }}>{trailing}</Box>}
            </Group>
        </Box>
    );
}

/** The detail pane that opens beside the shrunken list. */
function DetailPane({ resident, locked, onAdminister, onClose, isSm }) {
    const scheduled = (resident.rows ?? []).filter((r) => !r.as_required);
    const prn = (resident.rows ?? []).filter((r) => r.as_required);
    const allergies = cleanAllergies(resident.allergies);
    const age = resident.dob ? ageFromDob(resident.dob) : null;
    const m = metrics(resident);
    const pct = m.total ? Math.round((m.done / m.total) * 100) : 0;
    const barColor = m.total === 0 ? '#E4E8EC' : m.done === m.total ? '#1F8A5B' : '#B5601A';
    const info = [
        { k: 'Age', v: age ? `${age} years` : null, sub: resident.dob ? `DOB ${fmtDob(resident.dob)}` : null },
        { k: 'Gender', v: resident.gender },
        { k: 'NHS number', v: resident.nhs },
        { k: 'Weight', v: resident.weight },
        { k: 'Mobility', v: resident.mobility },
        { k: 'Diet', v: resident.diet },
    ].filter((x) => x.v);
    return (
        <Box style={{ background: 'light-dark(#F7F9FC, var(--mantine-color-dark-7))', borderRadius: 18, padding: isSm ? '18px 16px' : '24px 26px' }}>
            <style>{STAGGER_CSS}</style>
            <Group justify="space-between" align="flex-start" wrap="nowrap" mb="lg" style={rise(30)}>
                <Group gap="md" wrap="nowrap" style={{ minWidth: 0 }}>
                    <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="xl" size={52}>{initials(resident.name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz={19} fw={800} c={TXT} truncate>{resident.name}</Text>
                        <Text fz={13} c="dimmed">{[resident.room && `Room ${resident.room}`, age && `${age} yrs`, resident.gender].filter(Boolean).join(' · ')}</Text>
                        <Group gap={10} mt={9} wrap="nowrap">
                            <Box style={{ width: 130, height: 5, borderRadius: 999, background: 'light-dark(#EEF0F3, var(--mantine-color-dark-5))', overflow: 'hidden' }}>
                                <Box style={{ width: `${pct}%`, height: '100%', borderRadius: 999, background: barColor, transition: 'width .4s ease' }} />
                            </Box>
                            <Text fz={11} fw={600} c="#A2AAB4">{m.done} / {m.total} done</Text>
                        </Group>
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
            {info.length > 0 && (
                <Box mb="lg" style={{ ...rise(140), display: 'flex', flexWrap: 'wrap', background: 'light-dark(#ffffff, var(--mantine-color-dark-6))', border: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))', borderRadius: 16, overflow: 'hidden' }}>
                    {info.map((f, i) => (
                        <Box key={f.k} style={{ flex: '1 1 104px', minWidth: 94, padding: '8px 13px', borderLeft: i === 0 ? 'none' : '1px solid light-dark(#EDF0F3, var(--mantine-color-dark-5))' }}>
                            <Text fz={8.5} fw={700} c="#98A1AB" tt="uppercase" style={{ letterSpacing: 0.5 }}>{f.k}</Text>
                            <Text fz={12.5} fw={700} c={TXT} mt={3} truncate>{f.v}</Text>
                            {f.sub && <Text fz={9.5} c="#A2AAB4" mt={1} truncate>{f.sub}</Text>}
                        </Box>
                    ))}
                </Box>
            )}
            <Divider label="Medications this round" labelPosition="left" mt={4} mb={6} style={rise(180)} />
            {(resident.rows ?? []).length === 0
                ? <Text fz="sm" c="dimmed" py="sm">No medications in this round.</Text>
                : (
                    <Stack gap={0}>
                        {[...scheduled, ...prn].map((row, i) => (
                            <Box key={i} style={rise(220 + i * 55)}>
                                <SplitMedLine row={row} locked={locked} onAdminister={onAdminister} isSm={isSm} isFirst={i === 0} />
                            </Box>
                        ))}
                    </Stack>
                )}
        </Box>
    );
}

export default function MedicationRoundSplitC({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {}, home }) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    // The rail drops below the list (via flex-wrap) well before 768px — give it its own breakpoint so it
    // goes full-width + un-zoomed when below, instead of staying a tight zoomed side rail.
    const railBelow = useMediaQuery('(max-width: 1000px)');
    const authUser = usePage().props?.auth?.user;
    const isManager = authUser?.role === 'manager';
    const adminBy = `${authUser?.name ?? 'You'} · ${isManager ? 'Care Manager' : 'Carer'}`;

    const [activeRound, setActiveRound] = useState(currentRound);
    const [openId, setOpenId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [tab, setTab] = useState('all');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [recordOpened, record] = useDisclosure(false);
    const [modalStyle, setModalStyle] = useState('new'); // 'classic' (RecordDoseModal) | 'new' (AdministerModal) — temporary compare toggle
    const [adminCtx, setAdminCtx] = useState(null);

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

    // Whole-day outcome mix for the "Today's outcomes" donut (Given / Due / Refused / Missed).
    const outc = useMemo(() => {
        const sched = Object.values(grid).flat().flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const given = sched.filter((r) => isGiven(r.code)).length;
        const refused = sched.filter((r) => r.code === 'R').length;
        const missed = sched.filter((r) => r.code && !isGiven(r.code) && r.code !== 'R').length;
        const due = sched.filter((r) => !r.code).length;
        return { given, refused, missed, due, total: sched.length };
    }, [grid]);
    const oseg = (n) => (outc.total ? (n / outc.total) * 100 : 0);
    const opct = (n) => (outc.total ? Math.round((n / outc.total) * 100) : 0);

    // "On track" unless there's an overdue, still-unrecorded dose anywhere today.
    const overdueToday = useMemo(() => Object.values(grid).flat()
        .flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required))
        .filter((r) => !r.code && r.status === 'overdue').length, [grid]);
    const onTrack = overdueToday === 0;

    // This round's flagged doses (recorded, but not given).
    const flaggedCount = useMemo(() => residents
        .flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required))
        .filter((r) => r.code && !isGiven(r.code)).length, [residents]);

    const statusWord = roundClosed ? 'completed' : paused ? 'paused' : (roundDone === 0 ? 'not started' : roundDone >= roundTotal ? 'review' : 'in progress');

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };
    const openAdminister = (row, statusKey) => setAdminCtx({ row, residentName: selected?.name, residentRoom: selected?.room, status: statusKey });
    const administer = (row) => {
        if (roundClosed) return;
        if (modalStyle === 'new') { openAdminister(row, 'given'); return; }
        openRecord(row, 'A');
    };
    const endRound = () => router.post(`${ENDPOINT}/end-round`, { date, round: meta.key }, { preserveScroll: true });
    const reopenRound = () => router.post(`${ENDPOINT}/reopen-round`, { date, round: meta.key }, { preserveScroll: true });

    const RoundIcon = ROUND_ICONS[meta.key] ?? IconClockHour4;
    const selected = residents.find((r) => r.client_id === openId) ?? null;

    // Right-rail card width: full on phones, capped on tablet (below), 320 as the zoomed desktop side rail.
    const RAIL = railBelow ? (isSm ? undefined : 340) : 320;

    return (
        <AppShell title="Medication round">
            <Head title="Medication round · split C" />
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
                        <Tooltip label="Which administer modal opens when you record a dose (temporary — for comparing)">
                            <SegmentedControl size="xs" radius="xl" value={modalStyle} onChange={setModalStyle}
                                data={[{ label: 'Classic', value: 'classic' }, { label: 'New modal', value: 'new' }]} />
                        </Tooltip>
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
                                        style={{ background: '#13233F', paddingInline: 22, boxShadow: '0 8px 18px rgba(19,35,63,0.28)' }}>End round</Button>
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
                                        {selected && <DetailPane key={selected.client_id} resident={selected} locked={roundClosed} onAdminister={administer} onClose={() => setOpenId(null)} isSm={isSm} />}
                                    </Box>
                                )}
                            </Transition>
                        </Box>
                    </Box>

                    {/* Right rail — box layout + sizes copied from the mockup, then shrunk to 75% as one unit.
                        `zoom` (not `transform`) so the column actually reflows to the smaller size: the rail no
                        longer grows, so it hugs the boxes and the resident list expands into the reclaimed space.
                        Every box + the 22px gaps keep the same proportions. */}
                    <Stack gap={22} align={railBelow ? (isSm ? 'stretch' : 'center') : 'flex-end'} style={{ flex: railBelow ? '1 1 100%' : '0 1 auto', minWidth: 0, maxWidth: railBelow ? undefined : 360, paddingRight: railBelow ? 0 : 14, zoom: railBelow ? undefined : 0.5625 }}>
                        {/* Quick actions — softened square tiles (same as Split B) */}
                        <Box style={{ ...card, width: '100%', maxWidth: RAIL, borderRadius: 26, padding: '18px 20px' }}>
                            <Text fz={16} fw={800} c={TXT} mb={14}>Quick actions</Text>
                            <Box style={{ display: 'grid', gridTemplateColumns: 'repeat(4, minmax(0, 1fr))', gap: 8 }}>
                                {QUICK_ACTIONS.map((a) => {
                                    const Icon = a.icon;
                                    return (
                                        <Box key={a.label} component={Link} href={a.href}
                                            style={{ textDecoration: 'none', minWidth: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', textAlign: 'center', gap: 7, aspectRatio: '1 / 1', padding: 4, borderRadius: 16, background: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))', boxShadow: '0 6px 16px -10px rgba(20,50,80,0.28)', transition: 'transform .15s ease, box-shadow .15s ease' }}
                                            onMouseEnter={(e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 10px 22px -10px rgba(20,50,80,0.34)'; }}
                                            onMouseLeave={(e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = '0 6px 16px -10px rgba(20,50,80,0.28)'; }}>
                                            {/* Soft neutral chip + muted icon (no sharp per-category colour) */}
                                            <Box style={{ width: 34, height: 34, borderRadius: 11, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'light-dark(#F3F6FA, var(--mantine-color-dark-5))', boxShadow: 'inset 0 0 0 1px rgba(20,50,80,0.04)' }}>
                                                <Icon size={19} color="#6B7A90" variant="TwoTone" />
                                            </Box>
                                            <Text fz={12} fw={700} c={TXT} style={{ lineHeight: 1.1 }}>{a.short}</Text>
                                        </Box>
                                    );
                                })}
                            </Box>
                        </Box>

                        {/* Stat trio */}
                        <Group gap={14} wrap="nowrap" align="stretch" style={{ width: '100%', maxWidth: RAIL }}>
                            {[{ l: 'Given', v: roundDone, c: GREEN }, { l: 'Flagged', v: flaggedCount, c: ORANGE }, { l: 'Left', v: Math.max(roundTotal - roundDone, 0), c: TXT }].map((s) => (
                                <Box key={s.l} style={{ ...card, flex: 1, minWidth: 0, borderRadius: 20, padding: '20px 8px', textAlign: 'center' }}>
                                    <Text fz={30} fw={800} c={s.c} lh={1}>{s.v}</Text>
                                    <Text fz={13} c="dimmed" mt={7}>{s.l}</Text>
                                </Box>
                            ))}
                        </Group>

                        {/* Today's outcomes */}
                        <Box style={{ ...card, width: '100%', maxWidth: RAIL, borderRadius: 26, padding: '24px 24px 26px' }}>
                            <Group justify="space-between" align="center" mb={6} wrap="nowrap">
                                <Text fz={18} fw={800} c={TXT}>Today's outcomes</Text>
                                <Box style={{ padding: '5px 14px', borderRadius: 999, whiteSpace: 'nowrap', fontSize: 13, fontWeight: 700, background: onTrack ? 'light-dark(#E7F3E8, rgba(31,138,91,0.20))' : 'light-dark(#FCEBDD, rgba(232,132,43,0.22))', color: onTrack ? '#1F8A5B' : '#B5601A' }}>{onTrack ? 'On track' : 'Behind'}</Box>
                            </Group>
                            <Group justify="center" my={14}>
                                <RingProgress size={200} thickness={20} roundCaps
                                    sections={[{ value: oseg(outc.given), color: GREEN }, { value: oseg(outc.due), color: GRAY }, { value: oseg(outc.refused), color: REFUSED }, { value: oseg(outc.missed), color: MISSED }]}
                                    label={<Box ta="center"><Text fz={13} c="dimmed" lh={1}>Doses</Text><Text fz={40} fw={800} c={TXT} lh={1.2}>{outc.total}</Text></Box>} />
                            </Group>
                            <Stack gap={14} mt={6}>
                                {[{ l: 'Given', c: GREEN, v: outc.given }, { l: 'Due', c: GRAY, v: outc.due }, { l: 'Refused', c: REFUSED, v: outc.refused }, { l: 'Missed', c: MISSED, v: outc.missed }].map((s) => (
                                    <Group key={s.l} justify="space-between" wrap="nowrap">
                                        <Group gap={12} wrap="nowrap"><Box w={12} h={12} style={{ borderRadius: '50%', background: s.c, flexShrink: 0 }} /><Text fz={15} fw={600} c={TXT}>{s.l}</Text></Group>
                                        <Text fz={15} fw={700} c="#98A1AB">{opct(s.v)}%</Text>
                                    </Group>
                                ))}
                            </Stack>
                        </Box>
                    </Stack>
                </Box>

                {closure?.by && <Text fz="xs" c="dimmed" mt="md">Round ended by {closure.by}{closure.at ? ` at ${closure.at}` : ''}.</Text>}
            </Box>

            <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
            {adminCtx && <AdministerModal ctx={adminCtx} date={date} adminBy={adminBy} endpoint={ENDPOINT} onClose={() => setAdminCtx(null)} />}
        </AppShell>
    );
}
