import { useMemo, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Title, Badge, Button, ActionIcon, TextInput, Tooltip,
    RingProgress, Progress, Collapse, ThemeIcon, Modal, Select, NumberInput, Textarea, Checkbox,
} from '@mantine/core';
import {
    IconSearch, IconChevronRight, IconChevronDown, IconBox, IconAlertTriangle, IconCircleX,
    IconClock, IconCalendar, IconPill, IconShieldLock, IconAlertCircle, IconPlus, IconDownload,
    IconArrowDown, IconArrowRight, IconBottle, IconLungs, IconEye, IconDroplet, IconVaccine,
    IconBandage, IconCircleCheck, IconClockHour4, IconRefresh, IconPackage,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useRole } from '@frontend/lib/role';
import { downloadCsv } from '@frontend/lib/csv';

// "Meds Stock 4.1" — the stock counterpart to Medication Round 4. Same warm/editorial
// styling (cream panel, Fraunces serif display, yellow accent, soft white cards, a
// stock-health donut, status pill-tabs, alerts + recent-activity rails) reading the
// SAME shared stock data the round page deducts from.
const ADJUST_ENDPOINT = '/medication/stock-4-1/adjust';

// Warm palette — mirrors MedsRound4 (deliberately distinct from the app's teal tokens).
const CREAM = '#F6F2E8';
const INK = '#211F1A';
const ACCENT = '#E9D24E';        // yellow
const LINE = '#ECE6D6';
const DISPLAY = '"Fraunces", "Playfair Display", Georgia, serif';

// Status colours.
const C_HEALTHY = '#9BBE5B';     // green
const C_EXPIRING = '#8C7CC9';    // purple
const C_LOW = '#C99A2E';         // amber
const C_OUT = '#E1632F';         // orange-red
const C_EXPIRED = '#C0341D';     // red

const card = (extra = {}) => ({
    background: '#FFFFFF', borderRadius: 20, border: `1px solid ${LINE}`,
    boxShadow: '0 1px 2px rgba(33,31,26,0.03)', ...extra,
});

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
const parseDate = (s) => { const t = Date.parse(s); return Number.isNaN(t) ? null : t; };
const isInflow = (type = '') => /deliver|receiv|return|add|restock|order|correct/i.test(type);

/** One bucket per med, by priority. */
function bucketOf(m) {
    if (m.expired) return 'expired';
    if (isOut(m)) return 'out';
    if (m.low) return 'low';
    if (m.expiring_soon) return 'expiring';
    return 'healthy';
}

const STATUS_META = {
    healthy: { label: 'In stock', color: C_HEALTHY, soft: '#EAF1DA', ink: '#5E7A2E' },
    expiring: { label: 'Expiring soon', color: C_EXPIRING, soft: '#EBE6F7', ink: '#6A5AA6' },
    low: { label: 'Low stock', color: C_LOW, soft: '#F6EDBE', ink: '#8A7A1E' },
    out: { label: 'Out of stock', color: C_OUT, soft: '#FBE6DC', ink: '#B14A22' },
    expired: { label: 'Expired', color: C_EXPIRED, soft: '#FBE0DA', ink: '#9A2C1A' },
};

// Stock bar — colour-graded by headroom AND absolute quantity (small stocks read red).
function stockBar(m) {
    const stock = m.stock_level;
    if (stock === null || stock === undefined) return { pct: 0, color: '#CFC8B6' };
    const n = Number(stock);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    let color = C_HEALTHY;
    if (m.expired || isOut(m)) color = C_EXPIRED;
    else if (pct < 25 || n <= 5) color = C_OUT;
    else if (pct < 45 || n <= 12) color = C_LOW;
    else if (pct < 70 || n <= 20) color = ACCENT;
    return { pct, color };
}

// Guess dosage form + route from the name/unit.
function medForm(name = '', unit = '') {
    const s = `${name} ${unit}`.toLowerCase();
    if (/cream|ointment|gel|emollient/.test(s)) return { label: 'Cream', route: 'Topical', Icon: IconBottle };
    if (/inhaler|inhalation|nebuli/.test(s)) return { label: 'Inhaler', route: 'Inhaled', Icon: IconLungs };
    if (/eye|ocular/.test(s) && /drop/.test(s)) return { label: 'Eye drops', route: 'Ocular', Icon: IconEye };
    if (/drop/.test(s)) return { label: 'Drops', route: 'Topical', Icon: IconDroplet };
    if (/injection|inject|vial|ampoule|vaccine/.test(s)) return { label: 'Injection', route: 'Injection', Icon: IconVaccine };
    if (/patch/.test(s)) return { label: 'Patch', route: 'Transdermal', Icon: IconBandage };
    if (/solution|suspension|syrup|liquid|elixir/.test(s) || /ml/.test(String(unit).toLowerCase())) return { label: 'Oral solution', route: 'Oral', Icon: IconDroplet };
    if (/capsule|cap\b/.test(s)) return { label: 'Capsule', route: 'Oral', Icon: IconPill };
    return { label: 'Tablet', route: 'Oral', Icon: IconPill };
}
const strengthOf = (name = '') => { const m = String(name).match(/(\d+(?:\.\d+)?\s?(?:mg|mcg|ml|g|%|units?))/i); return m ? m[1].replace(/\s+/g, '') : null; };

function trendOf(m) {
    if (m.expired || isOut(m) || stockBar(m).pct < 20) return { Icon: IconArrowDown, color: C_EXPIRED, label: 'Critical' };
    if (m.low) return { Icon: IconArrowDown, color: C_LOW, label: 'Declining' };
    return { Icon: IconArrowRight, color: C_HEALTHY, label: 'Stable' };
}

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
                <Text fz={10} c={active ? 'rgba(255,255,255,0.6)' : 'dimmed'} lh={1.1}>{count} item{count === 1 ? '' : 's'}</Text>
            </Box>
        </Box>
    );
}

/** One transaction line inside an expanded med row (or the activity rail). */
function TxLine({ t, first }) {
    const inflow = isInflow(t.type);
    return (
        <Group gap="sm" wrap="nowrap" align="center" py={8} style={{ borderTop: first ? 'none' : `1px solid ${LINE}` }}>
            <Box style={{ width: 28, height: 28, borderRadius: 8, flexShrink: 0, background: inflow ? '#EAF1DA' : '#FBE6DC', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <Text fz="sm" fw={800} c={inflow ? C_HEALTHY : C_OUT}>{inflow ? '+' : '−'}</Text>
            </Box>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Group gap={6} wrap="nowrap">
                    <Text fz="sm" fw={700} c={INK}>{num(t.quantity, t.unit)}</Text>
                    <Badge size="xs" radius="sm" variant="light" color="gray" tt="capitalize">{t.type}</Badge>
                </Group>
                <Text fz={11} c="dimmed" truncate>Balance {t.balance_after ?? '—'} · {t.performed_by ?? '—'}</Text>
            </Box>
            <Text fz={11} c="dimmed" style={{ flexShrink: 0, whiteSpace: 'nowrap' }}>{t.date}</Text>
        </Group>
    );
}

/** Inventory row (icon · name/form · resident · stock bar · status pill) — expands to its transactions. */
function MedRow({ m, txns, expanded, onToggle, isMobile }) {
    const form = medForm(m.medication_name, m.unit);
    const strength = strengthOf(m.medication_name);
    const bar = stockBar(m);
    const trend = trendOf(m);
    const st = STATUS_META[bucketOf(m)];
    const last = txns[0];
    const lastAgo = last ? relTime(parseDate(last.date)) : null;

    return (
        <Box>
            <Group gap="md" wrap="nowrap" align="center" px="md" py={11} style={{ cursor: 'pointer', borderTop: `1px solid ${LINE}` }}
                onClick={onToggle}
                onMouseEnter={(e) => { e.currentTarget.style.background = '#FBFAF5'; }}
                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 230px', minWidth: 0 }}>
                    <Box style={{ width: 38, height: 38, borderRadius: 11, flexShrink: 0, background: m.is_controlled ? '#EBE6F7' : '#F7F4EC', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <form.Icon size={19} stroke={1.7} color={m.is_controlled ? C_EXPIRING : '#8A7A1E'} />
                    </Box>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={6} wrap="nowrap">
                            <Text fz="sm" fw={700} c={INK} truncate>{m.medication_name}</Text>
                            {m.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
                        </Group>
                        <Text fz="xs" c="dimmed" truncate>{[form.route, form.label, strength].filter(Boolean).join(' · ')}</Text>
                    </Box>
                </Group>
                <Text fz="sm" c={INK} fw={600} style={{ flex: '1 1 110px', minWidth: 0 }} visibleFrom="md" truncate>{m.resident ?? '—'}</Text>
                <Group gap="sm" wrap="nowrap" align="center" style={{ flex: '1 1 150px', minWidth: 110 }} visibleFrom="sm">
                    <Box style={{ flex: 1, minWidth: 0 }}><Progress value={bar.pct} radius="xl" size="sm" styles={{ section: { background: bar.color } }} /></Box>
                    <Tooltip label={trend.label}><trend.Icon size={15} color={trend.color} style={{ flexShrink: 0 }} /></Tooltip>
                </Group>
                <Box style={{ flexShrink: 0, textAlign: 'right', width: 64 }}>
                    <Text fz="sm" fw={800} c={INK} lh={1}>{m.stock_level ?? '—'}</Text>
                    <Text fz={10} c="dimmed" lh={1.2}>{m.unit ?? 'units'}</Text>
                </Box>
                <Box px={11} py={5} style={{ flexShrink: 0, borderRadius: 999, background: st.soft }}>
                    <Text fz="xs" fw={700} style={{ color: st.ink }}>{st.label}</Text>
                </Box>
                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}>
                    <IconChevronRight size={17} style={{ transform: expanded ? 'rotate(90deg)' : 'none', transition: 'transform .15s' }} />
                </ActionIcon>
            </Group>
            <Collapse in={expanded}>
                <Box px="md" pb="sm" pt={4} style={{ background: '#FBFAF5' }}>
                    <Group gap="lg" wrap="wrap" py={8}>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Reorder at</Text><Text fz="sm" fw={600} c={INK}>{num(m.reorder_level, m.unit)}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Expiry</Text><Text fz="sm" fw={600} c={m.expired ? C_EXPIRED : INK}>{m.expiry_date ?? '—'}</Text></Box>
                        <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>Last activity</Text><Text fz="sm" fw={600} c={INK}>{lastAgo ?? 'None'}</Text></Box>
                        {m.is_controlled && <Box><Text fz={10} c="dimmed" tt="uppercase" fw={700}>CD schedule</Text><Text fz="sm" fw={600} c={INK}>{m.cd_schedule ?? '—'}</Text></Box>}
                    </Group>
                    <Text fz={10} fw={700} tt="uppercase" c="dimmed" mt={2} style={{ letterSpacing: 0.5 }}>Recent transactions</Text>
                    {txns.length === 0
                        ? <Text fz="sm" c="dimmed" py="sm">No transactions recorded for this medication.</Text>
                        : <Stack gap={0}>{txns.slice(0, 6).map((t, i) => <TxLine key={t.id ?? i} t={t} first={i === 0} />)}</Stack>}
                </Box>
            </Collapse>
        </Box>
    );
}

/** Self-contained adjust-stock modal — posts to the 4.1 endpoint (no shared component touched). */
function AdjustModal({ opened, onClose, meds }) {
    const form = useForm({
        mar_sheet_id: '', transaction_type: 'received', quantity: '', expiry_date: '',
        is_controlled: false, cd_schedule: '', reason: '', disposal_method: '', witness_name: '', notes: '',
    });
    const medOptions = meds.map((m) => ({ value: String(m.id), label: m.medication_name + (m.resident ? ` — ${m.resident}` : '') }));
    const onMedChange = (value) => {
        form.setData('mar_sheet_id', value ?? '');
        const med = meds.find((m) => String(m.id) === value);
        if (med) { form.setData('is_controlled', !!med.is_controlled); form.setData('cd_schedule', med.cd_schedule ?? ''); }
    };
    const submit = () => form.post(ADJUST_ENDPOINT, { preserveScroll: true, onSuccess: () => { form.reset(); onClose(); } });
    const isDisposed = form.data.transaction_type === 'disposed';

    return (
        <Modal opened={opened} onClose={onClose} title={<Text fw={800} fz="lg" style={{ fontFamily: DISPLAY }}>Adjust stock</Text>} radius={18} centered size="md">
            <Stack gap="sm">
                <Select label="Medication" placeholder="Pick a medication" data={medOptions} value={form.data.mar_sheet_id}
                    onChange={onMedChange} error={form.errors.mar_sheet_id} searchable required />
                <Select label="Type" data={[
                    { value: 'received', label: 'Received (stock in)' },
                    { value: 'disposed', label: 'Disposed' },
                    { value: 'returned', label: 'Returned' },
                    { value: 'correction', label: 'Correction' },
                ]} value={form.data.transaction_type} onChange={(v) => form.setData('transaction_type', v)} required />
                <NumberInput label="Quantity" placeholder="e.g. 28" min={0} value={form.data.quantity}
                    onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} />
                <TextInput label="Expiry date" type="date" value={form.data.expiry_date}
                    onChange={(e) => form.setData('expiry_date', e.currentTarget.value)} error={form.errors.expiry_date} />
                <Checkbox label="Controlled drug" checked={form.data.is_controlled}
                    onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)} />
                {form.data.is_controlled && (
                    <TextInput label="CD schedule" placeholder="schedule_2 … schedule_5" value={form.data.cd_schedule}
                        onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)} />
                )}
                {isDisposed && (
                    <TextInput label="Disposal method" value={form.data.disposal_method}
                        onChange={(e) => form.setData('disposal_method', e.currentTarget.value)} />
                )}
                <TextInput label="Witness name" value={form.data.witness_name}
                    onChange={(e) => form.setData('witness_name', e.currentTarget.value)} />
                <TextInput label="Reason" value={form.data.reason}
                    onChange={(e) => form.setData('reason', e.currentTarget.value)} />
                <Textarea label="Notes" autosize minRows={2} value={form.data.notes}
                    onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                <Group justify="flex-end" mt="xs">
                    <Button variant="default" radius="xl" onClick={onClose}>Cancel</Button>
                    <Button radius="xl" color="dark" loading={form.processing} onClick={submit}>Save</Button>
                </Group>
            </Stack>
        </Modal>
    );
}

export default function MedsStock41({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const isManager = role === 'manager';
    const isMobile = useMediaQuery('(max-width: 768px)');
    const userName = (usePage().props?.auth?.user?.name ?? 'there').split(' ')[0];

    const [filter, setFilter] = useState('all');
    const [query, setQuery] = useState('');
    const [expandedId, setExpandedId] = useState(null);
    const [adjustOpened, adjust] = useDisclosure(false);
    const [alertsOpen, alertsCtl] = useDisclosure(true);
    const [activityOpen, activityCtl] = useDisclosure(true);

    // Transactions grouped by medication (newest first — already sorted by the controller).
    const txByMed = useMemo(() => {
        const map = {};
        for (const t of transactions) (map[t.medication_name] ??= []).push(t);
        return map;
    }, [transactions]);

    const statusKey = (m) => bucketOf(m);
    const filtered = meds.filter((m) => {
        if (filter !== 'all' && statusKey(m) !== filter) return false;
        const q = query.trim().toLowerCase();
        if (q && !`${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(q)) return false;
        return true;
    });

    const counts = useMemo(() => {
        const c = { healthy: 0, expiring: 0, low: 0, out: 0, expired: 0 };
        meds.forEach((m) => { c[bucketOf(m)]++; });
        return c;
    }, [meds]);

    const total = meds.length;
    const inGood = counts.healthy;
    const needsAction = counts.low + counts.out + counts.expired;
    const healthPct = total ? Math.round((inGood / total) * 100) : 0;
    const seg = (n) => (total ? (n / total) * 100 : 0);

    const statusTabs = [
        { key: 'all', label: 'All items', icon: IconBox, color: '#3B82C4', count: stats.total ?? total },
        { key: 'low', label: 'Low stock', icon: IconAlertTriangle, color: C_LOW, count: stats.low ?? counts.low },
        { key: 'out', label: 'Out of stock', icon: IconCircleX, color: C_OUT, count: stats.out_of_stock ?? counts.out },
        { key: 'expiring', label: 'Expiring soon', icon: IconClock, color: C_EXPIRING, count: stats.expiring_soon ?? counts.expiring },
        { key: 'expired', label: 'Expired', icon: IconCalendar, color: C_EXPIRED, count: stats.expired ?? counts.expired },
    ];

    const breakdown = [
        { key: 'healthy', label: 'In stock', color: C_HEALTHY, count: counts.healthy },
        { key: 'expiring', label: 'Expiring soon', color: C_EXPIRING, count: counts.expiring },
        { key: 'low', label: 'Low stock', color: C_LOW, count: counts.low },
        { key: 'out', label: 'Out of stock', color: C_OUT, count: counts.out },
        { key: 'expired', label: 'Expired', color: C_EXPIRED, count: counts.expired },
    ];

    // Alerts — expired, out, low, expiring + controlled drugs (each says WHAT and the specifics).
    const alerts = useMemo(() => {
        const out = [];
        meds.forEach((m) => {
            if (m.expired) out.push({ color: C_EXPIRED, icon: IconCalendar, tag: 'Expired', title: 'Expired stock', desc: `${m.medication_name}${m.expiry_date ? ` — expired ${m.expiry_date}` : ''}` });
            else if (isOut(m)) out.push({ color: C_OUT, icon: IconCircleX, tag: 'Out', title: 'Out of stock', desc: `${m.medication_name}${m.resident ? ` — ${m.resident}` : ''}` });
            else if (m.low) out.push({ color: C_LOW, icon: IconAlertTriangle, tag: 'Low', title: 'Low stock — reorder', desc: `${m.medication_name} — ${num(m.stock_level, m.unit)} left (reorder at ${num(m.reorder_level, m.unit)})` });
            else if (m.expiring_soon) out.push({ color: C_EXPIRING, icon: IconClock, tag: 'Expiring', title: 'Expiring soon', desc: `${m.medication_name}${m.expiry_date ? ` — ${m.expiry_date}` : ''}` });
        });
        meds.filter((m) => m.is_controlled).forEach((m) => out.push({ color: C_EXPIRING, icon: IconShieldLock, tag: 'CD', title: 'Controlled drug', desc: `${m.medication_name} — register & witness required` }));
        return out;
    }, [meds]);

    const updatedAgo = useMemo(() => {
        const newest = transactions.map((t) => parseDate(t.date)).filter(Boolean).sort((a, b) => b - a)[0];
        return newest ? relTime(newest) : 'just now';
    }, [transactions]);

    const exportColumns = [
        { header: 'Medication', value: (m) => m.medication_name },
        { header: 'Resident', value: (m) => m.resident },
        { header: 'Stock', value: (m) => m.stock_level },
        { header: 'Unit', value: (m) => m.unit },
        { header: 'Reorder level', value: (m) => m.reorder_level },
        { header: 'Status', value: (m) => STATUS_META[bucketOf(m)].label },
        { header: 'Expiry', value: (m) => m.expiry_date },
        { header: 'Controlled', value: (m) => (m.is_controlled ? `CD ${m.cd_schedule ?? ''}` : '') },
    ];

    const quickActions = [
        ...(isManager ? [{ label: 'Adjust stock', icon: IconPlus, color: '#3B82C4', onClick: adjust.open }] : []),
        { label: 'Export CSV', icon: IconDownload, color: '#9BBE5B', onClick: () => downloadCsv('meds-stock-4-1.csv', exportColumns, filtered) },
        { label: 'View reorders', icon: IconRefresh, color: C_LOW, onClick: () => { setFilter('low'); setExpandedId(null); } },
        { label: 'Controlled drugs', icon: IconShieldLock, color: C_EXPIRING, href: '/medication/controlled-drugs-4-1' },
        { label: 'Missed doses', icon: IconCircleX, color: C_OUT, href: '/medication/missed-doses-4-1' },
        { label: 'Medication round', icon: IconClockHour4, color: C_EXPIRING, href: '/medication/medication-round-4' },
    ];

    return (
        <AppShell>
            <Head title="Meds Stock 4.1">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />
            </Head>

            <Box style={{ background: CREAM, borderRadius: 28, padding: isMobile ? 16 : 28 }}>
                <style>{`.ms41-scroll{scrollbar-width:thin;scrollbar-color:transparent transparent}.ms41-scroll:hover{scrollbar-color:rgba(33,31,26,0.28) transparent}.ms41-scroll::-webkit-scrollbar{width:6px;height:6px}.ms41-scroll::-webkit-scrollbar-track{background:transparent}.ms41-scroll::-webkit-scrollbar-thumb{background:transparent;border-radius:8px}.ms41-scroll:hover::-webkit-scrollbar-thumb{background:rgba(33,31,26,0.28)}`}</style>
                <FlashAlerts />

                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Title order={1} style={{ fontFamily: DISPLAY }} fz={isMobile ? 30 : 40} fw={600} c={INK} lh={1.05}>
                            Medication stock
                        </Title>
                        <Text c="dimmed" fz="sm" mt={6}>
                            {needsAction > 0
                                ? `${needsAction} item${needsAction === 1 ? '' : 's'} need attention · updated ${updatedAgo}`
                                : `All ${total} item${total === 1 ? '' : 's'} in good order · updated ${updatedAgo}`}
                        </Text>
                    </Box>
                    <Group gap="sm" wrap="wrap">
                        <Button radius="xl" variant="white" c={INK} style={{ border: `1px solid ${LINE}` }}
                            leftSection={<IconDownload size={16} />} onClick={() => downloadCsv('meds-stock-4-1.csv', exportColumns, filtered)}>
                            Export
                        </Button>
                        {isManager
                            ? <Button radius="xl" color="dark" leftSection={<IconPlus size={16} />} onClick={adjust.open}>Adjust stock</Button>
                            : <Badge variant="light" color="gray" size="lg" radius="sm">View only</Badge>}
                    </Group>
                </Group>

                {/* Status pill-tabs (also filter the table) */}
                <Group gap={10} wrap="wrap" mb="lg">
                    {statusTabs.map((t) => (
                        <StatusPill key={t.key} icon={t.icon} label={t.label} count={t.count} color={t.color}
                            active={t.key === filter} onClick={() => { setFilter(t.key); setExpandedId(null); }} />
                    ))}
                </Group>

                {/* Main area — Alerts/Activity (left) · Inventory (middle) · Stock health (right). */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Inventory (middle) */}
                    <Box style={card({ flex: '4 1 360px', minWidth: 0, order: isMobile ? 1 : 2 })}>
                        <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                            <Text style={{ fontFamily: DISPLAY }} fz={22} fw={600} c={INK}>Inventory</Text>
                            <TextInput placeholder="Search meds or resident…" leftSection={<IconSearch size={15} />} value={query}
                                onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" variant="filled" w={isMobile ? '100%' : 260}
                                styles={{ input: { background: '#F7F4EC' } }} />
                        </Group>
                        {!isMobile && (
                            <Group gap="md" wrap="nowrap" px="md" pb={6} c="dimmed">
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 230px', letterSpacing: 0.5 }}>Medication</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 110px', letterSpacing: 0.5 }} visibleFrom="md">Resident</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 150px', letterSpacing: 0.5 }} visibleFrom="sm">Level</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 64, letterSpacing: 0.5, textAlign: 'right' }}>Stock</Text>
                                <Text fz={10} fw={700} tt="uppercase" style={{ width: 96, letterSpacing: 0.5 }}>Status</Text>
                                <Box style={{ width: 28 }} />
                            </Group>
                        )}
                        {filtered.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py="xl">No medications match.</Text>
                            : filtered.map((m) => (
                                <MedRow key={m.id} m={m} txns={txByMed[m.medication_name] ?? []} isMobile={isMobile}
                                    expanded={expandedId === m.id}
                                    onToggle={() => setExpandedId(expandedId === m.id ? null : m.id)} />
                            ))}
                        <Box py={4} />
                    </Box>

                    {/* Right — Stock health + Quick actions */}
                    <Stack gap={16} style={{ flex: '1 1 232px', maxWidth: isMobile ? undefined : 300, order: 3 }}>
                        <Box style={card({ padding: 16 })}>
                            <Group justify="space-between" align="center" mb={4}>
                                <Text style={{ fontFamily: DISPLAY }} fz={18} fw={600} c={INK}>Stock health</Text>
                                <Badge variant="light" color={needsAction ? 'orange' : 'green'} radius="sm" size="sm">{needsAction} to action</Badge>
                            </Group>
                            <Group justify="center" my={6}>
                                <RingProgress
                                    size={146} thickness={13} roundCaps
                                    sections={[
                                        { value: seg(counts.healthy), color: C_HEALTHY },
                                        { value: seg(counts.expiring), color: C_EXPIRING },
                                        { value: seg(counts.low), color: C_LOW },
                                        { value: seg(counts.out), color: C_OUT },
                                        { value: seg(counts.expired), color: C_EXPIRED },
                                    ]}
                                    label={(
                                        <Box ta="center">
                                            <Text style={{ fontFamily: DISPLAY }} fz={30} fw={600} c={INK} lh={1}>{total}</Text>
                                            <Text fz={10} c="dimmed">{healthPct}% in stock</Text>
                                        </Box>
                                    )}
                                />
                            </Group>
                            <Box mt={8} pt={12} style={{ borderTop: `1px solid ${LINE}` }}>
                                <Text fz={10} fw={700} tt="uppercase" c="dimmed" mb={8} style={{ letterSpacing: 0.5 }}>By status</Text>
                                <Stack gap={9}>
                                    {breakdown.map((b) => (
                                        <Box key={b.key} component="button" onClick={() => { setFilter(b.key === 'healthy' ? 'all' : b.key); setExpandedId(null); }}
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
                                    ? <Text fz="sm" c="dimmed">No stock alerts.</Text>
                                    : (
                                        <Box className="ms41-scroll" style={{ maxHeight: 280, overflowY: 'auto', overflowX: 'hidden' }}>
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
                                {transactions.length === 0
                                    ? <Text fz="sm" c="dimmed">No stock movements yet.</Text>
                                    : (
                                        <Box className="ms41-scroll" style={{ maxHeight: 300, overflowY: 'auto', overflowX: 'hidden' }}>
                                            <Stack gap={0} pr={4}>
                                                {transactions.slice(0, 12).map((t, i) => (
                                                    <Box key={t.id ?? i}>
                                                        <Group gap={8} wrap="nowrap" align="center" py={7} style={{ borderTop: i ? `1px solid ${LINE}` : 'none' }}>
                                                            <Box style={{ width: 26, height: 26, borderRadius: 8, flexShrink: 0, background: isInflow(t.type) ? '#EAF1DA' : '#FBE6DC', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                                {isInflow(t.type) ? <IconCircleCheck size={14} color={C_HEALTHY} /> : <IconPackage size={14} color={C_OUT} />}
                                                            </Box>
                                                            <Box style={{ flex: 1, minWidth: 0 }}>
                                                                <Text fz={13} fw={600} c={INK} truncate lh={1.2}>{t.medication_name}</Text>
                                                                <Text fz={10} c="dimmed" truncate lh={1.2}>{t.type} · {t.performed_by ?? '—'}</Text>
                                                            </Box>
                                                            <Box style={{ flexShrink: 0, textAlign: 'right' }}>
                                                                <Text fz={12} fw={700} c={isInflow(t.type) ? C_HEALTHY : C_OUT} lh={1.2}>{isInflow(t.type) ? '+' : '−'}{num(t.quantity, t.unit)}</Text>
                                                                <Text fz={9} c="dimmed" lh={1.2}>{t.date}</Text>
                                                            </Box>
                                                        </Group>
                                                    </Box>
                                                ))}
                                            </Stack>
                                        </Box>
                                    )}
                            </Collapse>
                        </Box>
                    </Stack>
                </Box>
            </Box>

            <AdjustModal opened={adjustOpened} onClose={adjust.close} meds={meds} />
        </AppShell>
    );
}
