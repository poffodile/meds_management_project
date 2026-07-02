import { useMemo, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Title, Badge, Button, ActionIcon, TextInput, Tooltip,
    RingProgress, Progress, Collapse, SegmentedControl,
} from '@mantine/core';
import {
    IconSearch, IconChevronRight, IconChevronDown, IconChevronLeft,
    IconCircleX, IconBan, IconCircleCheck, IconCheck, IconDownload,
    IconClockHour4, IconBox, IconShieldLock,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import ResolveDoseModal from '@frontend/features/medications/ResolveDoseModal';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import { downloadCsv } from '@frontend/lib/csv';

// "Missed Doses 4.1" — the missed/not-given review counterpart to Medication Round 4
// and Meds Stock 4.1. Same warm/editorial styling (cream panel, Fraunces serif, soft
// white cards, a review-progress donut, status pill-tabs, follow-up alerts + activity
// rails) reading the SAME live dose-review data. Resolves through the shared
// ResolveDoseModal, posting to (and returning to) the 4.1 endpoint.
const PAGE_ENDPOINT = '/medication/missed-doses-4-1';
const RESOLVE_ENDPOINT = '/medication/missed-doses-4-1/resolve';

// Warm palette — mirrors MedsStock41 / MedsRound4.
const CREAM = '#F6F2E8';
const INK = '#211F1A';
const LINE = '#ECE6D6';
const DISPLAY = '"Fraunces", "Playfair Display", Georgia, serif';

const C_MISSED = '#C0341D';   // red — missed (no record)
const C_NOTGIVEN = '#E1632F'; // orange — refused / omitted
const C_OUTSTANDING = '#8C7CC9'; // purple
const C_RESOLVED = '#9BBE5B'; // green

const card = (extra = {}) => ({
    background: '#FFFFFF', borderRadius: 20, border: `1px solid ${LINE}`,
    boxShadow: '0 1px 2px rgba(33,31,26,0.03)', ...extra,
});

const issueMeta = (kind) => (kind === 'missed'
    ? { label: 'Missed', color: C_MISSED, soft: '#FBE0DA', ink: '#9A2C1A', Icon: IconCircleX }
    : { label: 'Not given', color: C_NOTGIVEN, soft: '#FBE6DC', ink: '#B14A22', Icon: IconBan });

const reasonOf = (i) => (i.kind === 'not_given' ? (CODE_LABELS[i.code] ?? i.code ?? '—') : '—');

/** Status pill-tab (white card, coloured icon square; active = ink fill) — also the filter. */
function StatusPill({ icon: Icon, label, count, color, active, onClick }) {
    return (
        <Box component="button" onClick={onClick} style={{
            display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer',
            padding: '8px 14px 8px 8px', borderRadius: 14,
            background: active ? INK : '#FFFFFF', border: `1px solid ${active ? INK : LINE}`,
            transition: 'all .12s',
        }}>
            <Box style={{
                width: 30, height: 30, borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center',
                background: active ? 'rgba(255,255,255,0.16)' : `${color}1A`,
            }}>
                <Icon size={17} stroke={1.8} color={active ? '#fff' : color} />
            </Box>
            <Box style={{ textAlign: 'left' }}>
                <Text fz="sm" fw={700} c={active ? '#fff' : INK} lh={1.1}>{label}</Text>
                <Text fz={10} c={active ? 'rgba(255,255,255,0.6)' : 'dimmed'} lh={1.1}>{count} dose{count === 1 ? '' : 's'}</Text>
            </Box>
        </Box>
    );
}

/** Dose-issue row (icon · resident/med · slot · issue · reason · status/resolve). */
function DoseRow({ i, expanded, onToggle, onResolve, isMobile }) {
    const im = issueMeta(i.kind);
    const reason = reasonOf(i);

    return (
        <Box>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={11} style={{ cursor: 'pointer', borderTop: `1px solid ${LINE}` }}
                onClick={onToggle}
                onMouseEnter={(ev) => { ev.currentTarget.style.background = '#FBFAF5'; }}
                onMouseLeave={(ev) => { ev.currentTarget.style.background = 'transparent'; }}>
                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 230px', minWidth: 0 }}>
                    <Box style={{ width: 38, height: 38, borderRadius: 11, flexShrink: 0, background: im.soft, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <im.Icon size={19} stroke={1.7} color={im.color} />
                    </Box>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz="sm" fw={700} c={INK} truncate>{i.resident_name}</Text>
                        <Text fz="xs" c="dimmed" truncate>{i.medication_name}</Text>
                    </Box>
                </Group>
                <Text fz="sm" fw={700} c={INK} style={{ width: 56, flexShrink: 0 }} visibleFrom="sm">{i.slot}</Text>
                <Box style={{ flex: '1 1 100px', minWidth: 0 }} visibleFrom="md">
                    <Box px={11} py={5} style={{ display: 'inline-block', borderRadius: 999, background: im.soft }}>
                        <Text fz="xs" fw={700} style={{ color: im.ink }}>{im.label}</Text>
                    </Box>
                </Box>
                <Text fz="xs" c={reason === '—' ? 'dimmed' : INK} fw={reason === '—' ? 400 : 600} style={{ flex: '1 1 110px', minWidth: 0 }} visibleFrom="md" truncate>{reason}</Text>
                <Box style={{ flexShrink: 0 }} onClick={(ev) => ev.stopPropagation()}>
                    {i.resolved
                        ? <Box px={11} py={5} style={{ borderRadius: 999, background: '#EAF1DA' }}><Text fz="xs" fw={700} style={{ color: '#5E7A2E' }}>Resolved</Text></Box>
                        : <Button size="xs" radius="xl" color="dark" leftSection={<IconCheck size={14} />} onClick={() => onResolve(i)}>Resolve</Button>}
                </Box>
                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={17} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>
            <Collapse in={expanded}>
                <Box px="md" pb="sm" pt={4} style={{ background: '#FBFAF5' }}>
                    <Group gap="lg" wrap="wrap" py={8}>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Time slot</Text><Text fz="sm" fw={600} c={INK}>{i.slot}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Issue</Text><Text fz="sm" fw={600} style={{ color: im.color }}>{im.label}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Reason</Text><Text fz="sm" fw={600} c={INK}>{reason}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Status</Text><Text fz="sm" fw={600} c={i.resolved ? '#5E7A2E' : C_OUTSTANDING}>{i.resolved ? 'Resolved' : 'Outstanding'}</Text></Box>
                        {i.resolved && <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Resolution</Text><Text fz="sm" fw={600} c={INK}>{i.clinical_action ?? 'Reviewed'}{i.reviewed_by ? ` · ${i.reviewed_by}` : ''}</Text></Box>}
                    </Group>
                </Box>
            </Collapse>
        </Box>
    );
}

/** Clickable sortable column header — chevron shows the active sort + direction. */
function SortHeader({ label, k, sort, onSort, style, visibleFrom }) {
    const active = sort.key === k;
    return (
        <Box component="button" onClick={() => onSort(k)} visibleFrom={visibleFrom}
            style={{ display: 'flex', alignItems: 'center', gap: 3, border: 'none', background: 'transparent', cursor: 'pointer', padding: 0, ...style }}>
            <Text fz={10} fw={700} tt="uppercase" c={active ? INK : 'dimmed'} style={{ letterSpacing: 0.5 }}>{label}</Text>
            {active && <IconChevronDown size={11} stroke={2.5} color={INK} style={{ transform: sort.dir === 'asc' ? 'rotate(180deg)' : 'none', transition: 'transform .12s' }} />}
        </Box>
    );
}

export default function MissedDoses41({
    items = [], stats = {}, date, prevDate, nextDate, todayDate, statusFilter = 'outstanding',
}) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const userName = (usePage().props?.auth?.user?.name ?? 'there').split(' ')[0];

    const [resolveItem, setResolveItem] = useState(null);
    const [resolveOpened, resolve] = useDisclosure(false);
    const [query, setQuery] = useState('');
    const [issueFilter, setIssueFilter] = useState('all');
    const [expandedId, setExpandedId] = useState(null);
    const [alertsOpen, alertsCtl] = useDisclosure(true);
    const [activityOpen, activityCtl] = useDisclosure(true);

    const reload = (params) => router.get(PAGE_ENDPOINT, { date, status: statusFilter, ...params },
        { preserveScroll: true, preserveState: true });

    const openResolve = (item) => { setResolveItem(item); resolve.open(); };

    const filtered = items.filter((i) => {
        if (issueFilter !== 'all' && i.kind !== issueFilter) return false;
        const q = query.trim().toLowerCase();
        if (q && !`${i.resident_name} ${i.medication_name}`.toLowerCase().includes(q)) return false;
        return true;
    });

    // Sortable table — default Time (latest slot) first so the most recent missed meds are on top.
    const [sort, setSort] = useState({ key: 'slot', dir: 'desc' });
    const toggleSort = (key) => setSort((s) => (s.key === key
        ? { key, dir: s.dir === 'asc' ? 'desc' : 'asc' }
        : { key, dir: key === 'slot' ? 'desc' : 'asc' }));
    const sortKeyVal = (i) => {
        if (sort.key === 'resident') return (i.resident_name || '').toLowerCase();
        if (sort.key === 'issue') return i.kind || '';
        if (sort.key === 'reason') { const r = reasonOf(i); return r === '—' ? '' : r.toLowerCase(); }
        if (sort.key === 'status') return i.resolved ? '1' : '0';
        return i.slot || ''; // slot / time
    };
    const sorted = [...filtered].sort((a, b) => {
        const c = String(sortKeyVal(a)).localeCompare(String(sortKeyVal(b)), undefined, { numeric: true });
        const tie = c !== 0 ? c : String(a.slot).localeCompare(String(b.slot));
        return sort.dir === 'asc' ? tie : -tie;
    });

    const counts = useMemo(() => ({
        missed: items.filter((i) => i.kind === 'missed').length,
        not_given: items.filter((i) => i.kind === 'not_given').length,
        resolved: items.filter((i) => i.resolved).length,
        outstanding: items.filter((i) => !i.resolved).length,
    }), [items]);

    const outstanding = useMemo(() => items.filter((i) => !i.resolved), [items]);

    // Donut spans the four review states (the day's whole picture).
    const dMissed = stats.missed ?? counts.missed;
    const dNotGiven = stats.not_given ?? counts.not_given;
    const dResolved = stats.resolved ?? counts.resolved;
    const dOutstanding = stats.outstanding ?? counts.outstanding;
    const total = items.length;
    const resolvedPct = total ? Math.round((dResolved / total) * 100) : 0;
    const seg = (n) => (total ? (n / total) * 100 : 0);

    const statusTabs = [
        { key: 'all', label: 'All issues', icon: IconBox, color: '#3B82C4', count: total, filterKind: true },
        { key: 'missed', label: 'Missed', icon: IconCircleX, color: C_MISSED, count: counts.missed, filterKind: true },
        { key: 'not_given', label: 'Not given', icon: IconBan, color: C_NOTGIVEN, count: counts.not_given, filterKind: true },
    ];

    const breakdown = [
        { key: 'missed', label: 'Missed', color: C_MISSED, count: dMissed },
        { key: 'not_given', label: 'Not given', color: C_NOTGIVEN, count: dNotGiven },
        { key: 'outstanding', label: 'Outstanding', color: C_OUTSTANDING, count: dOutstanding },
        { key: 'resolved', label: 'Resolved', color: C_RESOLVED, count: dResolved },
    ];

    // Alerts — the outstanding follow-ups (each says who/what/when).
    const alerts = useMemo(() => outstanding.map((i) => {
        const im = issueMeta(i.kind);
        return { color: im.color, icon: im.Icon, tag: im.label,
            title: `${im.label} dose`, desc: `${i.resident_name} — ${i.medication_name}${i.slot ? ` at ${i.slot}` : ''}` };
    }), [outstanding]);

    // Recent events — sorted by slot.
    const timeline = useMemo(() => [...items].sort((a, b) => String(a.slot).localeCompare(String(b.slot))).slice(0, 12), [items]);

    const exportColumns = [
        { header: 'Resident', value: (i) => i.resident_name },
        { header: 'Medication', value: (i) => i.medication_name },
        { header: 'Time', value: (i) => i.slot },
        { header: 'Issue', value: (i) => issueMeta(i.kind).label },
        { header: 'Reason', value: (i) => (reasonOf(i) === '—' ? '' : reasonOf(i)) },
        { header: 'Status', value: (i) => (i.resolved ? 'Resolved' : 'Outstanding') },
        { header: 'Resolution', value: (i) => i.clinical_action ?? '' },
    ];

    const quickActions = [
        { label: 'Resolve next', icon: IconCheck, color: '#3B82C4', onClick: () => outstanding[0] && openResolve(outstanding[0]) },
        { label: 'Export CSV', icon: IconDownload, color: C_RESOLVED, onClick: () => downloadCsv(`missed-doses-4-1-${date}.csv`, exportColumns, filtered) },
        { label: 'Controlled drugs', icon: IconShieldLock, color: C_OUTSTANDING, href: '/medication/controlled-drugs-4-1' },
        { label: 'Meds stock', icon: IconBox, color: '#C99A2E', href: '/medication/stock-4-1' },
        { label: 'Medication round', icon: IconClockHour4, color: C_NOTGIVEN, href: '/medication/medication-round-4' },
    ];

    return (
        <AppShell>
            <Head title="Missed Doses 4.1">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />
            </Head>

            <Box style={{ background: CREAM, borderRadius: 28, padding: isMobile ? 16 : 28 }}>
                <style>{`.md41-scroll{scrollbar-width:thin;scrollbar-color:transparent transparent}.md41-scroll:hover{scrollbar-color:rgba(33,31,26,0.28) transparent}.md41-scroll::-webkit-scrollbar{width:6px;height:6px}.md41-scroll::-webkit-scrollbar-track{background:transparent}.md41-scroll::-webkit-scrollbar-thumb{background:transparent;border-radius:8px}.md41-scroll:hover::-webkit-scrollbar-thumb{background:rgba(33,31,26,0.28)}`}</style>
                <FlashAlerts />

                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Title order={1} style={{ fontFamily: DISPLAY }} fz={isMobile ? 30 : 40} fw={600} c={INK} lh={1.05}>
                            Missed doses
                        </Title>
                        <Text c="dimmed" fz="sm" mt={6}>
                            {dOutstanding > 0
                                ? `${dOutstanding} dose${dOutstanding === 1 ? '' : 's'} need follow-up · ${date}`
                                : `All doses reviewed · ${date}`}
                        </Text>
                    </Box>
                    <Group gap="sm" wrap="wrap">
                        <Button radius="xl" variant="white" c={INK} style={{ border: `1px solid ${LINE}` }} px="sm"
                            onClick={() => reload({ date: prevDate })}><IconChevronLeft size={16} /></Button>
                        <TextInput type="date" value={date || ''} radius="xl" variant="filled"
                            onChange={(e) => reload({ date: e.currentTarget.value })} styles={{ input: { background: '#fff', border: `1px solid ${LINE}` } }} />
                        <Button radius="xl" variant="white" c={INK} style={{ border: `1px solid ${LINE}` }} px="sm"
                            onClick={() => reload({ date: nextDate })}><IconChevronRight size={16} /></Button>
                        <Button radius="xl" color="dark" onClick={() => reload({ date: todayDate })}>Today</Button>
                    </Group>
                </Group>

                {/* Status pill-tabs (filter by issue) + status segmented (server reload) */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Group gap={10} wrap="wrap">
                        {statusTabs.map((t) => (
                            <StatusPill key={t.key} icon={t.icon} label={t.label} count={t.count} color={t.color}
                                active={t.key === issueFilter} onClick={() => { setIssueFilter(t.key === 'all' ? 'all' : t.key); setExpandedId(null); }} />
                        ))}
                    </Group>
                    <SegmentedControl radius="xl" value={statusFilter} onChange={(v) => reload({ status: v })}
                        styles={{ root: { background: '#fff', border: `1px solid ${LINE}` } }}
                        data={[
                            { label: 'Outstanding', value: 'outstanding' },
                            { label: 'Resolved', value: 'resolved' },
                            { label: 'All', value: 'all' },
                        ]} />
                </Group>

                {/* Main area — Alerts/Activity (left) · Doses (middle) · Review progress (right). */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Doses (middle) */}
                    <Box style={card({ flex: '4 1 360px', minWidth: 0, order: isMobile ? 1 : 2 })}>
                        <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                            <Text style={{ fontFamily: DISPLAY }} fz={22} fw={600} c={INK}>Dose issues</Text>
                            <TextInput placeholder="Search resident or med…" leftSection={<IconSearch size={15} />} value={query}
                                onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" variant="filled" w={isMobile ? '100%' : 260}
                                styles={{ input: { background: '#F7F4EC' } }} />
                        </Group>
                        {!isMobile && (
                            <Group gap="md" wrap="nowrap" px="md" pb={6}>
                                <SortHeader label="Resident" k="resident" sort={sort} onSort={toggleSort} style={{ flex: '2 1 230px' }} />
                                <SortHeader label="Time" k="slot" sort={sort} onSort={toggleSort} style={{ width: 56 }} visibleFrom="sm" />
                                <SortHeader label="Issue" k="issue" sort={sort} onSort={toggleSort} style={{ flex: '1 1 100px' }} visibleFrom="md" />
                                <SortHeader label="Reason" k="reason" sort={sort} onSort={toggleSort} style={{ flex: '1 1 110px' }} visibleFrom="md" />
                                <SortHeader label="Status" k="status" sort={sort} onSort={toggleSort} />
                                <Box style={{ width: 28 }} />
                            </Group>
                        )}
                        <Box className="md41-scroll" style={{ maxHeight: isMobile ? undefined : 520, overflowY: 'auto', overflowX: 'hidden' }}>
                            {sorted.length === 0
                                ? <Text fz="sm" c="dimmed" ta="center" py="xl">No dose issues match.</Text>
                                : sorted.map((i) => (
                                    <DoseRow key={i.id} i={i} isMobile={isMobile}
                                        expanded={expandedId === i.id}
                                        onToggle={() => setExpandedId(expandedId === i.id ? null : i.id)}
                                        onResolve={openResolve} />
                                ))}
                            <Box py={4} />
                        </Box>
                    </Box>

                    {/* Right — Review progress + Quick actions */}
                    <Stack gap={16} style={{ flex: '1 1 232px', maxWidth: isMobile ? undefined : 300, order: 3 }}>
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={4}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Review progress</Text>
                                <Badge variant="light" color={dOutstanding ? 'orange' : 'green'} radius="sm" size="sm">{dOutstanding} left</Badge>
                            </Group>
                            <Group justify="center" my={6}>
                                <RingProgress
                                    size={146} thickness={13} roundCaps
                                    sections={[
                                        { value: seg(dResolved), color: C_RESOLVED },
                                        { value: seg(dOutstanding), color: C_OUTSTANDING },
                                    ]}
                                    label={(
                                        <Box ta="center">
                                            <Text style={{ fontFamily: DISPLAY }} fz={30} fw={600} c={INK} lh={1}>{total}</Text>
                                            <Text fz={10} c="dimmed">{resolvedPct}% resolved</Text>
                                        </Box>
                                    )}
                                />
                            </Group>
                            <Box mt={8} pt={12} style={{ borderTop: `1px solid ${LINE}` }}>
                                <Text fz={10} fw={700} tt="uppercase" c="dimmed" mb={8} style={{ letterSpacing: 0.5 }}>Breakdown</Text>
                                <Stack gap={9}>
                                    {breakdown.map((b) => (
                                        <Box key={b.key} component="button"
                                            onClick={() => { if (b.key === 'missed' || b.key === 'not_given') { setIssueFilter(b.key); setExpandedId(null); } else { reload({ status: b.key === 'resolved' ? 'resolved' : 'outstanding' }); } }}
                                            style={{ width: '100%', textAlign: 'left', border: 'none', background: 'transparent', cursor: 'pointer', padding: 0 }}>
                                            <Group justify="space-between" wrap="nowrap" mb={3}>
                                                <Group gap={8} wrap="nowrap"><Box w={10} h={10} style={{ borderRadius: 3, background: b.color }} /><Text fz="xs" fw={600} c="dimmed">{b.label}</Text></Group>
                                                <Text fz="xs" fw={700} c={INK}>{b.count}</Text>
                                            </Group>
                                            <Progress value={seg(b.count)} radius="xl" size={6} styles={{ section: { background: b.color } }} />
                                        </Box>
                                    ))}
                                </Stack>
                            </Box>
                        </Box>

                        {/* Quick actions */}
                        <Box style={card({ padding: 14 })}>
                            <Text style={{ fontFamily: DISPLAY }} fz={16} fw={600} c={INK} mb={8}>Quick actions</Text>
                            <Box style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8 }}>
                                {quickActions.map((a) => {
                                    const Icon = a.icon;
                                    const inner = (
                                        <Group gap={8} wrap="nowrap" align="center" style={{ padding: '7px 9px', borderRadius: 10, background: '#FBFAF5', border: `1px solid ${LINE}`, height: '100%', cursor: 'pointer' }}>
                                            <Box style={{ width: 26, height: 26, borderRadius: 8, flexShrink: 0, background: `${a.color}1A`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                <Icon size={15} stroke={1.8} color={a.color} />
                                            </Box>
                                            <Text fz={11} fw={600} c={INK} lh={1.15}>{a.label}</Text>
                                        </Group>
                                    );
                                    return a.href
                                        ? <Box component="a" key={a.label} href={a.href} style={{ textDecoration: 'none' }}>{inner}</Box>
                                        : <Box component="button" key={a.label} onClick={a.onClick} style={{ border: 'none', background: 'transparent', padding: 0, textAlign: 'left' }}>{inner}</Box>;
                                })}
                            </Box>
                        </Box>
                    </Stack>

                    {/* Left — Outstanding follow-up alerts on top, Recent events below */}
                    <Stack gap={16} style={{ flex: '1 1 232px', maxWidth: isMobile ? undefined : 300, order: isMobile ? 2 : 1 }}>
                        {/* Alerts */}
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={alertsOpen ? 8 : 0} style={{ cursor: 'pointer' }} onClick={alertsCtl.toggle}>
                                <Group gap={8} wrap="nowrap">
                                    <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Follow-up</Text>
                                    <Badge variant="light" color={alerts.length ? 'red' : 'gray'} radius="sm" size="sm">{alerts.length}</Badge>
                                </Group>
                                <IconChevronDown size={16} stroke={2} color="#A8A294" style={{ transform: alertsOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Group>
                            <Collapse in={alertsOpen}>
                                {alerts.length === 0
                                    ? <Text fz="sm" c="dimmed">Nothing outstanding — all caught up.</Text>
                                    : (
                                        <Box className="md41-scroll" style={{ maxHeight: 280, overflowY: 'auto', overflowX: 'hidden' }}>
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

                        {/* Recent events */}
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={activityOpen ? 8 : 0} style={{ cursor: 'pointer' }} onClick={activityCtl.toggle}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Recent events</Text>
                                <IconChevronDown size={16} stroke={2} color="#A8A294" style={{ transform: activityOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Group>
                            <Collapse in={activityOpen}>
                                {timeline.length === 0
                                    ? <Text fz="sm" c="dimmed">No events for this day.</Text>
                                    : (
                                        <Box className="md41-scroll" style={{ maxHeight: 300, overflowY: 'auto', overflowX: 'hidden' }}>
                                            <Stack gap={0} pr={4}>
                                                {timeline.map((i, idx) => {
                                                    const im = issueMeta(i.kind);
                                                    return (
                                                        <Group key={i.id} gap={8} wrap="nowrap" align="center" py={7} style={{ borderTop: idx ? `1px solid ${LINE}` : 'none' }}>
                                                            <Box style={{ width: 26, height: 26, borderRadius: 8, flexShrink: 0, background: i.resolved ? '#EAF1DA' : im.soft, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                                {i.resolved ? <IconCircleCheck size={14} color={C_RESOLVED} /> : <im.Icon size={14} color={im.color} />}
                                                            </Box>
                                                            <Box style={{ flex: 1, minWidth: 0 }}>
                                                                <Text fz={13} fw={600} c={INK} truncate lh={1.2}>{i.resident_name}</Text>
                                                                <Text fz={10} c="dimmed" truncate lh={1.2}>{im.label} · {i.medication_name}</Text>
                                                            </Box>
                                                            <Box style={{ flexShrink: 0, textAlign: 'right' }}>
                                                                <Text fz={12} fw={700} c={i.resolved ? C_RESOLVED : im.color} lh={1.2}>{i.slot}</Text>
                                                                <Text fz={9} c="dimmed" lh={1.2}>{i.resolved ? 'Resolved' : 'Open'}</Text>
                                                            </Box>
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

            <ResolveDoseModal opened={resolveOpened} onClose={resolve.close} item={resolveItem} date={date} action={RESOLVE_ENDPOINT} />
        </AppShell>
    );
}
