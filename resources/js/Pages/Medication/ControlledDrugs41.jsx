import { useMemo, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Title, Badge, Button, ActionIcon, TextInput, Tooltip,
    RingProgress, Progress, Collapse,
} from '@mantine/core';
import {
    IconSearch, IconChevronRight, IconChevronDown, IconShieldLock, IconPill, IconTruckDelivery,
    IconTrash, IconArrowBackUp, IconAdjustments, IconActivity, IconPlus, IconDownload,
    IconAlertTriangle, IconUserCheck, IconClockHour4, IconBox, IconCircleX,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';
import { downloadCsv } from '@frontend/lib/csv';

// "Controlled Drugs 4.1" — the CD register counterpart to Medication Round 4 and Meds
// Stock 4.1. Same warm/editorial styling (cream panel, Fraunces serif display, soft
// white cards, a register-health donut, action pill-tabs, alerts + activity rails)
// reading the SAME live register data the legacy/React pages do. Adds entries through
// the shared AddCdEntryModal, posting to (and returning to) the 4.1 endpoint.
const STORE_ENDPOINT = '/medication/controlled-drugs-4-1';

// Warm palette — mirrors MedsStock41 / MedsRound4 (deliberately distinct from teal tokens).
const CREAM = '#F6F2E8';
const INK = '#211F1A';
const ACCENT = '#E9D24E';        // yellow
const LINE = '#ECE6D6';
const DISPLAY = '"Fraunces", "Playfair Display", Georgia, serif';

// Action colours.
const C_ADMIN = '#C0341D';   // red — administered (stock out)
const C_RECV = '#9BBE5B';    // green — received (stock in)
const C_DISP = '#E1632F';    // orange — disposed (out)
const C_RET = '#8C7CC9';     // purple — returned (in)
const C_ADJ = '#C99A2E';     // amber — adjustment

const card = (extra = {}) => ({
    background: '#FFFFFF', borderRadius: 20, border: `1px solid ${LINE}`,
    boxShadow: '0 1px 2px rgba(33,31,26,0.03)', ...extra,
});

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const parseDate = (s) => { const t = Date.parse(s); return Number.isNaN(t) ? null : t; };

// CD action → label, colour, icon and stock direction (+ in / − out).
const ACTION_META = {
    administered: { label: 'Administered', color: C_ADMIN, soft: '#FBE0DA', ink: '#9A2C1A', Icon: IconPill, flow: -1 },
    received: { label: 'Received', color: C_RECV, soft: '#EAF1DA', ink: '#5E7A2E', Icon: IconTruckDelivery, flow: 1 },
    disposed: { label: 'Disposed', color: C_DISP, soft: '#FBE6DC', ink: '#B14A22', Icon: IconTrash, flow: -1 },
    returned: { label: 'Returned', color: C_RET, soft: '#EBE6F7', ink: '#6A5AA6', Icon: IconArrowBackUp, flow: 1 },
    adjustment: { label: 'Adjustment', color: C_ADJ, soft: '#F6EDBE', ink: '#8A7A1E', Icon: IconAdjustments, flow: 0 },
};
const metaOf = (t) => ACTION_META[t] ?? { label: t || '—', color: '#8A857A', soft: '#EFECE3', ink: '#7A756A', Icon: IconActivity, flow: 0 };

function relTime(ms) {
    if (!ms) return null;
    const mins = Math.round((Date.now() - ms) / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const h = Math.round(mins / 60);
    if (h < 24) return `${h}h ago`;
    const days = Math.floor(h / 24);
    if (days === 1) return 'Yesterday';
    return new Date(ms).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

/** Action pill-tab (white card, coloured icon square; active = ink fill) — also the filter. */
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
                <Text fz={10} c={active ? 'rgba(255,255,255,0.6)' : 'dimmed'} lh={1.1}>{count} entr{count === 1 ? 'y' : 'ies'}</Text>
            </Box>
        </Box>
    );
}

/** Register row (icon · resident/med · action · dose · balance) — expands to entry detail. */
function EntryRow({ e, expanded, onToggle, isMobile }) {
    const am = metaOf(e.action_type);
    const flowColor = am.flow < 0 ? C_ADMIN : am.flow > 0 ? C_RECV : '#8A857A';
    const witnessed = Boolean(e.witness_name);

    return (
        <Box>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={11} style={{ cursor: 'pointer', borderTop: `1px solid ${LINE}` }}
                onClick={onToggle}
                onMouseEnter={(ev) => { ev.currentTarget.style.background = '#FBFAF5'; }}
                onMouseLeave={(ev) => { ev.currentTarget.style.background = 'transparent'; }}>
                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 230px', minWidth: 0 }}>
                    <Box style={{ width: 38, height: 38, borderRadius: 11, flexShrink: 0, background: am.soft, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <am.Icon size={19} stroke={1.7} color={am.color} />
                    </Box>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={6} wrap="nowrap">
                            <Text fz="sm" fw={700} c={INK} truncate>{e.medication_name}</Text>
                            {e.cd_schedule && <Badge size="xs" variant="light" color="grape" radius="sm">{e.cd_schedule}</Badge>}
                        </Group>
                        <Text fz="xs" c="dimmed" truncate>{[e.client_name, e.entry_time].filter(Boolean).join(' · ')}</Text>
                    </Box>
                </Group>
                <Box style={{ flex: '1 1 110px', minWidth: 0 }} visibleFrom="md">
                    <Box px={11} py={5} style={{ display: 'inline-block', borderRadius: 999, background: am.soft }}>
                        <Text fz="xs" fw={700} style={{ color: am.ink }}>{am.label}</Text>
                    </Box>
                </Box>
                <Box style={{ flexShrink: 0, textAlign: 'right', width: 76 }}>
                    <Text fz="sm" fw={800} c={flowColor} lh={1}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                    <Text fz={10} c="dimmed" lh={1.2}>{e.unit ?? 'dose'}</Text>
                </Box>
                <Box style={{ flexShrink: 0, textAlign: 'right', width: 66 }} visibleFrom="sm">
                    <Text fz="sm" fw={700} c={INK} lh={1}>{e.balance_after ?? '—'}</Text>
                    <Text fz={10} c="dimmed" lh={1.2}>balance</Text>
                </Box>
                <Tooltip label={witnessed ? `Witness: ${e.witness_name}` : 'No witness recorded'}>
                    <Box style={{ width: 28, height: 28, borderRadius: 8, flexShrink: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', background: witnessed ? '#EAF1DA' : '#FBE0DA' }}>
                        {witnessed ? <IconUserCheck size={15} color={C_RECV} /> : <IconAlertTriangle size={15} color={C_ADMIN} />}
                    </Box>
                </Tooltip>
                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={17} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>
            <Collapse in={expanded}>
                <Box px="md" pb="sm" pt={4} style={{ background: '#FBFAF5' }}>
                    <Group gap="lg" wrap="wrap" py={8}>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Date</Text><Text fz="sm" fw={600} c={INK}>{e.entry_date ?? '—'}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Resident</Text><Text fz="sm" fw={600} c={INK}>{e.client_name ?? '—'}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Balance after</Text><Text fz="sm" fw={600} c={INK}>{e.balance_after ?? '—'}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Witness</Text><Text fz="sm" fw={600} c={witnessed ? INK : C_ADMIN}>{e.witness_name ?? 'Missing'}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Recorded by</Text><Text fz="sm" fw={600} c={INK}>{e.created_by ?? '—'}</Text></Box>
                    </Group>
                </Box>
            </Collapse>
        </Box>
    );
}

export default function ControlledDrugs41({ entries = [], residents = [], medsByClient = {}, lastBalances = {} }) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const userName = (usePage().props?.auth?.user?.name ?? 'there').split(' ')[0];

    const [filter, setFilter] = useState('all');
    const [query, setQuery] = useState('');
    const [expandedId, setExpandedId] = useState(null);
    const [addOpened, add] = useDisclosure(false);
    const [alertsOpen, alertsCtl] = useDisclosure(true);
    const [activityOpen, activityCtl] = useDisclosure(true);

    const filtered = entries.filter((e) => {
        if (filter !== 'all' && e.action_type !== filter) return false;
        const q = query.trim().toLowerCase();
        if (q && !`${e.medication_name} ${e.client_name ?? ''}`.toLowerCase().includes(q)) return false;
        return true;
    });

    const counts = useMemo(() => {
        const c = { administered: 0, received: 0, disposed: 0, returned: 0, adjustment: 0 };
        entries.forEach((e) => { if (c[e.action_type] !== undefined) c[e.action_type]++; });
        return c;
    }, [entries]);

    const total = entries.length;
    const noWitness = useMemo(() => entries.filter((e) => !e.witness_name).length, [entries]);
    const seg = (n) => (total ? (n / total) * 100 : 0);

    const statusTabs = [
        { key: 'all', label: 'All entries', icon: IconBox, color: '#3B82C4', count: total },
        { key: 'administered', label: 'Administered', icon: IconPill, color: C_ADMIN, count: counts.administered },
        { key: 'received', label: 'Received', icon: IconTruckDelivery, color: C_RECV, count: counts.received },
        { key: 'disposed', label: 'Disposed', icon: IconTrash, color: C_DISP, count: counts.disposed },
        { key: 'returned', label: 'Returned', icon: IconArrowBackUp, color: C_RET, count: counts.returned },
    ];

    const breakdown = [
        { key: 'administered', label: 'Administered', color: C_ADMIN, count: counts.administered },
        { key: 'received', label: 'Received', color: C_RECV, count: counts.received },
        { key: 'disposed', label: 'Disposed', color: C_DISP, count: counts.disposed },
        { key: 'returned', label: 'Returned', color: C_RET, count: counts.returned },
        { key: 'adjustment', label: 'Adjustment', color: C_ADJ, count: counts.adjustment },
    ];

    // Alerts — compliance signals: entries without a witness, then low running balances.
    const alerts = useMemo(() => {
        const out = [];
        entries.filter((e) => !e.witness_name).forEach((e) => out.push({
            color: C_ADMIN, icon: IconAlertTriangle, tag: 'Witness',
            title: 'No witness recorded', desc: `${e.medication_name} — ${metaOf(e.action_type).label.toLowerCase()} for ${e.client_name ?? '—'}${e.entry_time ? ` at ${e.entry_time}` : ''}`,
        }));
        entries.filter((e) => e.balance_after !== null && e.balance_after !== undefined && Number(e.balance_after) <= 5)
            .forEach((e) => out.push({
                color: C_ADJ, icon: IconShieldLock, tag: 'Balance',
                title: 'Low running balance', desc: `${e.medication_name} — ${e.balance_after} left after ${metaOf(e.action_type).label.toLowerCase()}`,
            }));
        return out;
    }, [entries]);

    // Recent activity — newest first, regardless of the table filter.
    const timeline = useMemo(() => {
        const withT = entries.map((e) => ({ e, t: parseDate(`${e.entry_date} ${e.entry_time}`) }));
        const sortable = withT.every((x) => x.t !== null);
        return (sortable ? [...withT].sort((a, b) => b.t - a.t).map((x) => x.e) : entries).slice(0, 12);
    }, [entries]);

    const updatedAgo = useMemo(() => {
        const newest = entries.map((e) => parseDate(`${e.entry_date} ${e.entry_time}`)).filter(Boolean).sort((a, b) => b - a)[0];
        return newest ? relTime(newest) : 'just now';
    }, [entries]);

    const exportColumns = [
        { header: 'Date', value: (e) => e.entry_date },
        { header: 'Time', value: (e) => e.entry_time },
        { header: 'Resident', value: (e) => e.client_name },
        { header: 'Medication', value: (e) => e.medication_name },
        { header: 'Schedule', value: (e) => e.cd_schedule },
        { header: 'Action', value: (e) => metaOf(e.action_type).label },
        { header: 'Dose', value: (e) => e.dose_quantity },
        { header: 'Unit', value: (e) => e.unit },
        { header: 'Balance', value: (e) => e.balance_after },
        { header: 'Witness', value: (e) => e.witness_name },
        { header: 'By', value: (e) => e.created_by },
    ];

    const quickActions = [
        { label: 'Add entry', icon: IconPlus, color: '#3B82C4', onClick: add.open },
        { label: 'Export CSV', icon: IconDownload, color: C_RECV, onClick: () => downloadCsv('controlled-drugs-4-1.csv', exportColumns, filtered) },
        { label: 'Missed doses', icon: IconCircleX, color: C_DISP, href: '/medication/missed-doses-4-1' },
        { label: 'Meds stock', icon: IconBox, color: C_ADJ, href: '/medication/stock-4-1' },
        { label: 'Medication round', icon: IconClockHour4, color: C_RET, href: '/medication/medication-round-4' },
    ];

    return (
        <AppShell>
            <Head title="Controlled Drugs 4.1">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />
            </Head>

            <Box style={{ background: CREAM, borderRadius: 28, padding: isMobile ? 16 : 28 }}>
                <style>{`.cd41-scroll{scrollbar-width:thin;scrollbar-color:transparent transparent}.cd41-scroll:hover{scrollbar-color:rgba(33,31,26,0.28) transparent}.cd41-scroll::-webkit-scrollbar{width:6px;height:6px}.cd41-scroll::-webkit-scrollbar-track{background:transparent}.cd41-scroll::-webkit-scrollbar-thumb{background:transparent;border-radius:8px}.cd41-scroll:hover::-webkit-scrollbar-thumb{background:rgba(33,31,26,0.28)}`}</style>
                <FlashAlerts />

                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Title order={1} style={{ fontFamily: DISPLAY }} fz={isMobile ? 30 : 40} fw={600} c={INK} lh={1.05}>
                            Controlled drugs
                        </Title>
                        <Text c="dimmed" fz="sm" mt={6}>
                            {noWitness > 0
                                ? `${noWitness} entr${noWitness === 1 ? 'y' : 'ies'} missing a witness · ${total} total · updated ${updatedAgo}`
                                : `Append-only register · ${total} entr${total === 1 ? 'y' : 'ies'} · updated ${updatedAgo}`}
                        </Text>
                    </Box>
                    <Group gap="sm" wrap="wrap">
                        <Button radius="xl" variant="white" c={INK} style={{ border: `1px solid ${LINE}` }}
                            leftSection={<IconDownload size={16} />} onClick={() => downloadCsv('controlled-drugs-4-1.csv', exportColumns, filtered)}>
                            Export
                        </Button>
                        <Button radius="xl" color="dark" leftSection={<IconPlus size={16} />} onClick={add.open}>Add entry</Button>
                    </Group>
                </Group>

                {/* Action pill-tabs (also filter the register) */}
                <Group gap={10} wrap="wrap" mb="lg">
                    {statusTabs.map((t) => (
                        <StatusPill key={t.key} icon={t.icon} label={t.label} count={t.count} color={t.color}
                            active={t.key === filter} onClick={() => { setFilter(t.key); setExpandedId(null); }} />
                    ))}
                </Group>

                {/* Main area — Alerts/Activity (left) · Register (middle) · Register health (right). */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Register (middle) */}
                    <Box style={card({ flex: '4 1 360px', minWidth: 0, order: isMobile ? 1 : 2 })}>
                        <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                            <Text style={{ fontFamily: DISPLAY }} fz={22} fw={600} c={INK}>Register</Text>
                            <TextInput placeholder="Search med or resident…" leftSection={<IconSearch size={15} />} value={query}
                                onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" variant="filled" w={isMobile ? '100%' : 260}
                                styles={{ input: { background: '#F7F4EC' } }} />
                        </Group>
                        {!isMobile && (
                            <Group gap="md" wrap="nowrap" px="md" pb={6} c="dimmed">
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 230px', letterSpacing: 0.5 }}>Medication</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 110px', letterSpacing: 0.5 }} visibleFrom="md">Action</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 76, letterSpacing: 0.5, textAlign: 'right' }}>Dose</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 66, letterSpacing: 0.5, textAlign: 'right' }} visibleFrom="sm">Balance</Text>
                                <Box style={{ width: 28 }} />
                                <Box style={{ width: 28 }} />
                            </Group>
                        )}
                        <Box className="cd41-scroll" style={{ maxHeight: isMobile ? undefined : 520, overflowY: 'auto', overflowX: 'hidden' }}>
                            {filtered.length === 0
                                ? <Text fz="sm" c="dimmed" ta="center" py="xl">No register entries match.</Text>
                                : filtered.map((e, i) => (
                                    <EntryRow key={e.id ?? i} e={e} isMobile={isMobile}
                                        expanded={expandedId === (e.id ?? i)}
                                        onToggle={() => setExpandedId(expandedId === (e.id ?? i) ? null : (e.id ?? i))} />
                                ))}
                            <Box py={4} />
                        </Box>
                    </Box>

                    {/* Right — Register health + Quick actions */}
                    <Stack gap={16} style={{ flex: '1 1 232px', maxWidth: isMobile ? undefined : 300, order: 3 }}>
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={4}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Register health</Text>
                                <Badge variant="light" color={noWitness ? 'red' : 'green'} radius="sm" size="sm">{noWitness} to witness</Badge>
                            </Group>
                            <Group justify="center" my={6}>
                                <RingProgress
                                    size={146} thickness={13} roundCaps
                                    sections={breakdown.map((b) => ({ value: seg(b.count), color: b.color }))}
                                    label={(
                                        <Box ta="center">
                                            <Text style={{ fontFamily: DISPLAY }} fz={30} fw={600} c={INK} lh={1}>{total}</Text>
                                            <Text fz={10} c="dimmed">entries</Text>
                                        </Box>
                                    )}
                                />
                            </Group>
                            <Box mt={8} pt={12} style={{ borderTop: `1px solid ${LINE}` }}>
                                <Text fz={10} fw={700} tt="uppercase" c="dimmed" mb={8} style={{ letterSpacing: 0.5 }}>By action</Text>
                                <Stack gap={9}>
                                    {breakdown.map((b) => (
                                        <Box key={b.key} component="button" onClick={() => { setFilter(b.key); setExpandedId(null); }}
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

                    {/* Left — Alerts on top, Recent activity below */}
                    <Stack gap={16} style={{ flex: '1 1 232px', maxWidth: isMobile ? undefined : 300, order: isMobile ? 2 : 1 }}>
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
                                    ? <Text fz="sm" c="dimmed">No compliance alerts.</Text>
                                    : (
                                        <Box className="cd41-scroll" style={{ maxHeight: 280, overflowY: 'auto', overflowX: 'hidden' }}>
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

                        {/* Recent activity */}
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={activityOpen ? 8 : 0} style={{ cursor: 'pointer' }} onClick={activityCtl.toggle}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Recent activity</Text>
                                <IconChevronDown size={16} stroke={2} color="#A8A294" style={{ transform: activityOpen ? 'none' : 'rotate(-90deg)', transition: 'transform .15s' }} />
                            </Group>
                            <Collapse in={activityOpen}>
                                {timeline.length === 0
                                    ? <Text fz="sm" c="dimmed">No register activity yet.</Text>
                                    : (
                                        <Box className="cd41-scroll" style={{ maxHeight: 300, overflowY: 'auto', overflowX: 'hidden' }}>
                                            <Stack gap={0} pr={4}>
                                                {timeline.map((e, i) => {
                                                    const am = metaOf(e.action_type);
                                                    return (
                                                        <Group key={e.id ?? i} gap={8} wrap="nowrap" align="center" py={7} style={{ borderTop: i ? `1px solid ${LINE}` : 'none' }}>
                                                            <Box style={{ width: 26, height: 26, borderRadius: 8, flexShrink: 0, background: am.soft, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                                <am.Icon size={14} color={am.color} />
                                                            </Box>
                                                            <Box style={{ flex: 1, minWidth: 0 }}>
                                                                <Text fz={13} fw={600} c={INK} truncate lh={1.2}>{e.medication_name}</Text>
                                                                <Text fz={10} c="dimmed" truncate lh={1.2}>{am.label} · {e.client_name ?? '—'}</Text>
                                                            </Box>
                                                            <Box style={{ flexShrink: 0, textAlign: 'right' }}>
                                                                <Text fz={12} fw={700} c={am.flow < 0 ? C_ADMIN : am.flow > 0 ? C_RECV : 'dimmed'} lh={1.2}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                                                                <Text fz={9} c="dimmed" lh={1.2}>{e.entry_time ?? e.entry_date}</Text>
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

            <AddCdEntryModal
                opened={addOpened}
                onClose={add.close}
                residents={residents}
                medsByClient={medsByClient}
                lastBalances={lastBalances}
                action={STORE_ENDPOINT}
            />
        </AppShell>
    );
}
