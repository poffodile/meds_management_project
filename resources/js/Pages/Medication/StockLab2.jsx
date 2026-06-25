import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, SimpleGrid, Badge, Button, Box, Stack, Divider, Grid,
    ThemeIcon, Progress, Table, TextInput, Select, Tooltip, ActionIcon, Drawer, Checkbox, SegmentedControl,
} from '@mantine/core';
import {
    IconBox, IconAlertTriangle, IconCircleX, IconClock, IconCalendar, IconShieldLock,
    IconPill, IconVaccine, IconDroplet, IconEye, IconLungs, IconBottle, IconBandage,
    IconSearch, IconEdit, IconHistory, IconArrowsExchange, IconTrash, IconPlus, IconReload,
    IconArrowDown, IconArrowRight, IconSelector, IconChevronUp, IconChevronDown,
    IconDownload, IconFilter, IconActivity, IconTruckDelivery, IconArchive, IconPrinter, IconX,
} from '@tabler/icons-react';
import AdjustStockModal from '@frontend/features/medications/AdjustStockModal';
import AppShell from '@frontend/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';
import classes from './StockLab2.module.css';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
const parseDate = (s) => { const t = Date.parse(s); return Number.isNaN(t) ? null : t; };

const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

// --- meaningful status (not just "Good") ---
function statusOf(m) {
    if (m.expired) return { key: 'expired', label: 'Expired', color: 'red' };
    if (isOut(m)) return { key: 'critical', label: 'Critical', color: 'red' };
    if (m.low) return { key: 'low', label: 'Low stock', color: 'orange' };
    if (m.expiring_soon) return { key: 'expiring', label: 'Expiring soon', color: 'grape' };
    return { key: 'in_stock', label: 'In stock', color: 'green' };
}

// --- stock bar: colour-graded by headroom AND absolute quantity, so small
// stocks read as danger even when no reorder level is recorded ---
function stockBar(m) {
    const stock = m.stock_level;
    if (stock === null || stock === undefined) return { pct: 0, color: 'gray' };
    const n = Number(stock);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    let color = 'green';
    if (m.expired || isOut(m)) color = 'red';
    else if (pct < 25 || n <= 5) color = 'red';
    else if (pct < 45 || n <= 12) color = 'orange';
    else if (pct < 70 || n <= 20) color = 'yellow';
    return { pct, color };
}

// --- guess dosage form + route from the name/unit (no form column yet) ---
function medForm(name = '', unit = '') {
    const s = `${name} ${unit}`.toLowerCase();
    if (/cream|ointment|gel|emollient/.test(s)) return { label: 'Cream', route: 'Topical', Icon: IconBottle, color: 'pink' };
    if (/inhaler|inhalation|nebuli/.test(s)) return { label: 'Inhaler', route: 'Inhaled', Icon: IconLungs, color: 'cyan' };
    if (/eye|ocular/.test(s) && /drop/.test(s)) return { label: 'Eye drops', route: 'Ocular', Icon: IconEye, color: 'indigo' };
    if (/drop/.test(s)) return { label: 'Drops', route: 'Topical', Icon: IconDroplet, color: 'indigo' };
    if (/injection|inject|vial|ampoule|vaccine/.test(s)) return { label: 'Injection', route: 'Injection', Icon: IconVaccine, color: 'red' };
    if (/patch/.test(s)) return { label: 'Patch', route: 'Transdermal', Icon: IconBandage, color: 'grape' };
    if (/solution|suspension|syrup|liquid|elixir/.test(s) || /ml/.test(String(unit).toLowerCase())) return { label: 'Oral solution', route: 'Oral', Icon: IconDroplet, color: 'teal' };
    if (/capsule|cap\b/.test(s)) return { label: 'Capsule', route: 'Oral', Icon: IconPill, color: 'brandTeal' };
    return { label: 'Tablet', route: 'Oral', Icon: IconPill, color: 'brandTeal' };
}
const strengthOf = (name = '') => {
    const m = String(name).match(/(\d+(?:\.\d+)?\s?(?:mg|mcg|ml|g|%|units?))/i);
    return m ? m[1].replace(/\s+/g, '') : null;
};

function relTime(ms) {
    if (!ms) return null;
    const d = Date.now() - ms;
    const mins = Math.round(d / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const h = Math.round(mins / 60);
    if (h < 24) return `${h}h ago`;
    const days = Math.floor(h / 24);
    if (days === 1) return 'Yesterday';
    return new Date(ms).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
}

function trendOf(m) {
    if (m.expired || isOut(m) || stockBar(m).pct < 20) return { Icon: IconArrowDown, color: 'red', label: 'Critical' };
    if (m.low) return { Icon: IconArrowDown, color: 'orange', label: 'Declining' };
    return { Icon: IconArrowRight, color: 'green', label: 'Stable' };
}

const isInflow = (type = '') => /deliver|receiv|return|add|restock|order|adjust\s*up/i.test(type);

// Icon + tint for an activity row, by transaction type.
function activityMeta(type = '') {
    const t = type.toLowerCase();
    if (/reorder/.test(t)) return { Icon: IconReload, color: 'yellow' };
    if (/admin|given|dispens/.test(t)) return { Icon: IconPill, color: 'brandTeal' };
    if (/transfer/.test(t)) return { Icon: IconArrowsExchange, color: 'indigo' };
    if (/dispos|waste|destroy/.test(t)) return { Icon: IconTrash, color: 'orange' };
    if (/deliver|receiv|restock|order/.test(t)) return { Icon: IconTruckDelivery, color: 'green' };
    return { Icon: IconActivity, color: 'gray' };
}

// White metric tile — only the icon (and the number, when it's an alert) carry colour.
function MetricCard({ label, value, sublabel, color, icon: Icon, alert, onClick }) {
    const hot = alert && Number(value) > 0;
    return (
        <Box onClick={onClick} className={onClick ? classes.metricClickable : undefined} style={{
            ...surface,
            ...(hot ? { borderLeft: `3px solid var(--mantine-color-${color}-6)` } : {}),
            borderRadius: 20, padding: '9px 12px',
            cursor: onClick ? 'pointer' : 'default',
        }}>
            <Group gap={7} wrap="nowrap" mb={4}>
                <ThemeIcon variant="light" color={color} size={26} radius="md"><Icon size={15} stroke={1.7} /></ThemeIcon>
                <Text size="xs" fw={600} c="dimmed" lineClamp={1}>{label}</Text>
            </Group>
            <Text fw={800} fz={20} lh={1} c={hot ? `${color}.7` : undefined}>{value}</Text>
            {sublabel && <Text fz={11} c="dimmed" mt={2} lineClamp={1}>{sublabel}</Text>}
        </Box>
    );
}

// Sortable header cell.
function SortTh({ label, sortKey, sort, onSort, ta }) {
    const active = sort.key === sortKey;
    const Icon = !active ? IconSelector : (sort.dir === 'asc' ? IconChevronUp : IconChevronDown);
    return (
        <Table.Th className={classes.sortTh} ta={ta} onClick={() => onSort(sortKey)}>
            <Group gap={4} wrap="nowrap" justify={ta === 'right' ? 'flex-end' : 'flex-start'}>
                <span>{label}</span>
                <Icon size={13} style={{ opacity: active ? 1 : 0.4 }} />
            </Group>
        </Table.Th>
    );
}

export default function StockLab2({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const isManager = role === 'manager';
    const [adjustOpened, adjust] = useDisclosure(false);
    const [lowOpened, lowDrawer] = useDisclosure(false);
    const [drawerMed, setDrawerMed] = useState(null);

    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [stockFilter, setStockFilter] = useState('all');
    const [expiryFilter, setExpiryFilter] = useState('all');
    const [sort, setSort] = useState({ key: null, dir: 'asc' });
    const [view, setView] = useState('overview'); // top-level page tab

    const onSort = (key) => setSort((s) => (s.key === key ? { key, dir: s.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' }));

    const disposals = useMemo(() => transactions.filter((t) => /dispos|waste|destroy/i.test(t.type || '')), [transactions]);
    const tabLabel = (text, count) => (
        <Group gap={8} wrap="nowrap" justify="center">
            <Text size="sm" fw={600} span>{text}</Text>
            <Badge size="sm" variant="light" color="gray" circle>{count}</Badge>
        </Group>
    );

    // latest transaction per medication, for "Last activity"
    const lastTxByMed = useMemo(() => {
        const map = {};
        for (const t of transactions) if (!(t.medication_name in map)) map[t.medication_name] = t;
        return map;
    }, [transactions]);

    const updatedAgo = useMemo(() => {
        const newest = transactions.map((t) => parseDate(t.date)).filter(Boolean).sort((a, b) => b - a)[0];
        return newest ? relTime(newest) : 'just now';
    }, [transactions]);

    const lowList = useMemo(() => meds.filter((m) => m.low || isOut(m)), [meds]);
    const lowNames = useMemo(() => meds.filter((m) => m.low).map((m) => m.medication_name), [meds]);
    const nextExpiry = useMemo(() => {
        const now = Date.now();
        return meds.map((m) => ({ raw: m.expiry_date, t: parseDate(m.expiry_date) }))
            .filter((x) => x.t && x.t >= now).sort((a, b) => a.t - b.t)[0] ?? null;
    }, [meds]);

    const filteredMeds = useMemo(() => {
        const q = search.trim().toLowerCase();
        return meds.filter((m) => {
            if (q && !`${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(q)) return false;
            if (statusFilter !== 'all' && statusOf(m).key !== statusFilter) return false;
            if (stockFilter !== 'all') {
                const { pct } = stockBar(m);
                if (stockFilter === 'high' && pct < 55) return false;
                if (stockFilter === 'medium' && (pct < 25 || pct >= 55)) return false;
                if (stockFilter === 'low' && pct >= 25) return false;
            }
            if (expiryFilter === 'expiring' && !m.expiring_soon) return false;
            if (expiryFilter === 'expired' && !m.expired) return false;
            if (expiryFilter === 'ok' && (m.expired || m.expiring_soon)) return false;
            return true;
        });
    }, [meds, search, statusFilter, stockFilter, expiryFilter]);

    const sortedMeds = useMemo(() => {
        const arr = [...filteredMeds];
        const { key, dir } = sort;
        if (!key) return arr;
        const sgn = dir === 'asc' ? 1 : -1;
        arr.sort((a, b) => {
            if (key === 'medication') return sgn * String(a.medication_name).localeCompare(String(b.medication_name));
            if (key === 'resident') return sgn * String(a.resident ?? '').localeCompare(String(b.resident ?? ''));
            if (key === 'stock') return sgn * ((Number(a.stock_level ?? -1)) - (Number(b.stock_level ?? -1)));
            if (key === 'expiry') return sgn * ((parseDate(a.expiry_date) ?? Infinity) - (parseDate(b.expiry_date) ?? Infinity));
            return 0;
        });
        return arr;
    }, [filteredMeds, sort]);

    const drawerTx = useMemo(
        () => (drawerMed ? transactions.filter((t) => t.medication_name === drawerMed.medication_name).slice(0, 6) : []),
        [drawerMed, transactions],
    );
    const drawerForm = drawerMed ? medForm(drawerMed.medication_name, drawerMed.unit) : null;

    // --- bulk selection ---
    const [selected, setSelected] = useState(() => new Set());
    const visibleIds = sortedMeds.map((m) => m.id);
    const allSelected = visibleIds.length > 0 && visibleIds.every((id) => selected.has(id));
    const someSelected = visibleIds.some((id) => selected.has(id)) && !allSelected;
    const toggleOne = (id) => setSelected((prev) => { const n = new Set(prev); n.has(id) ? n.delete(id) : n.add(id); return n; });
    const toggleAll = () => setSelected((prev) => {
        const everyOn = visibleIds.every((id) => prev.has(id));
        const n = new Set(prev);
        visibleIds.forEach((id) => (everyOn ? n.delete(id) : n.add(id)));
        return n;
    });
    const clearSelection = () => setSelected(new Set());

    const stop = (e) => e.stopPropagation();

    const rows = sortedMeds.map((m) => {
        const bar = stockBar(m);
        const st = statusOf(m);
        const form = medForm(m.medication_name, m.unit);
        const strength = strengthOf(m.medication_name);
        const trend = trendOf(m);
        const last = lastTxByMed[m.medication_name];
        const lastAgo = last ? relTime(parseDate(last.date)) : null;
        return (
            <Table.Tr key={m.id} onClick={() => setDrawerMed(m)} style={{ cursor: 'pointer' }}
                bg={selected.has(m.id) ? 'light-dark(var(--mantine-color-brandTeal-0), var(--mantine-color-dark-5))' : undefined}>
                <Table.Td onClick={stop} w={40} className={classes.selectCell}
                    style={{
                        opacity: selected.has(m.id) ? 1 : undefined,
                        boxShadow: selected.has(m.id) ? 'inset 4px 0 0 0 var(--mantine-color-brandTeal-6)' : undefined,
                    }}>
                    <Checkbox size="xs" color="brandTeal" checked={selected.has(m.id)} onChange={() => toggleOne(m.id)} aria-label="Select row" />
                </Table.Td>
                <Table.Td>
                    <Group gap="sm" wrap="nowrap">
                        <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : form.color} size={36} radius="xl"><form.Icon size={19} /></ThemeIcon>
                        <div>
                            <Text fw={600} size="sm">{m.medication_name}</Text>
                            <Text size="xs" c="dimmed">
                                {form.route} • {form.label}{strength ? ` • ${strength}` : ''}{m.is_controlled ? ` • CD ${m.cd_schedule ?? ''}` : ''}
                            </Text>
                        </div>
                    </Group>
                </Table.Td>
                <Table.Td>
                    <Box w={150}>
                        <Group justify="space-between" align="flex-end" wrap="nowrap" mb={4}>
                            <Box>
                                <Text fw={800} fz="xl" lh={1}>{m.stock_level ?? '—'}</Text>
                                <Text size="xs" c="dimmed" lh={1.2}>{m.unit ?? 'units'}</Text>
                            </Box>
                            <Tooltip label={trend.label} withArrow><trend.Icon size={16} color={`var(--mantine-color-${trend.color}-6)`} /></Tooltip>
                        </Group>
                        <Progress value={bar.pct} color={bar.color} size="sm" radius="xl" />
                    </Box>
                </Table.Td>
                <Table.Td><Badge color={st.color} variant="light" tt="none">{st.label}</Badge></Table.Td>
                <Table.Td><Text size="sm">{m.resident ?? '—'}</Text></Table.Td>
                <Table.Td><Text size="sm" c={m.expired ? 'red' : undefined}>{m.expiry_date ?? 'No expiry'}</Text></Table.Td>
                <Table.Td><Text size="xs" c={lastAgo ? 'dimmed' : 'gray.4'}>{lastAgo ?? 'Never'}</Text></Table.Td>
                <Table.Td onClick={stop}>
                    <Group gap={6} wrap="nowrap" justify="flex-end" className={classes.rowActions}>
                        {isManager && <Tooltip label="Adjust stock" withArrow><ActionIcon variant="subtle" color="gray.7" onClick={adjust.open}><IconEdit size={18} /></ActionIcon></Tooltip>}
                        <Tooltip label="View history" withArrow><ActionIcon variant="subtle" color="gray.7" onClick={() => setDrawerMed(m)}><IconHistory size={18} /></ActionIcon></Tooltip>
                        <Tooltip label="Transfer (coming soon)" withArrow><ActionIcon variant="subtle" color="gray.5" disabled><IconArrowsExchange size={18} /></ActionIcon></Tooltip>
                        {isManager && <Tooltip label="Remove (coming soon)" withArrow><ActionIcon variant="subtle" color="gray.5" disabled><IconTrash size={18} /></ActionIcon></Tooltip>}
                    </Group>
                </Table.Td>
            </Table.Tr>
        );
    });

    return (
        <>
            <Head title="Medication Stock (Lab 2)" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="lg">
                        <Group justify="space-between" align="center" wrap="wrap" gap="md">
                            <Group gap="sm" wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="brandTeal" size={40} radius="md"><IconBox size={22} stroke={1.7} /></ThemeIcon>
                                <Box>
                                    <Text fz={24} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>Medication Stock</Text>
                                    <Text c="dimmed" fz="sm">Inventory, alerts and activity across your home.</Text>
                                </Box>
                            </Group>
                            <Group gap="md" wrap="nowrap">
                                <Text size="xs" c="dimmed">Updated {updatedAgo}</Text>
                                {isManager
                                    ? <Button radius="md" leftSection={<IconPlus size={16} />} onClick={adjust.open}>Adjust stock</Button>
                                    : <Badge variant="light" color="gray" size="lg" radius="sm">View only</Badge>}
                            </Group>
                        </Group>
                    </Box>

                    <FlashAlerts />

                    {/* Top-level page tabs — segmented control that swaps the whole view */}
                    <SegmentedControl
                        value={view} onChange={setView} radius={12} mb={24}
                        data={[
                            { value: 'overview', label: tabLabel('Stock Overview', meds.length) },
                            { value: 'transactions', label: tabLabel('Transactions', transactions.length) },
                            { value: 'reorders', label: tabLabel('Reorders', lowList.length) },
                            { value: 'disposals', label: tabLabel('Disposals', disposals.length) },
                        ]}
                    />

                    {view === 'overview' && (
                    <SimpleGrid cols={{ base: 2, sm: 3, lg: 6 }} mb={48} spacing="sm">
                        <MetricCard label="Total items" value={stats.total ?? 0} color="brandTeal" icon={IconBox} sublabel={`${stats.total ?? 0} medications`} />
                        <MetricCard label="Low stock" value={stats.low ?? 0} color="orange" icon={IconAlertTriangle} sublabel="Needs attention" alert onClick={lowList.length ? lowDrawer.open : undefined} />
                        <MetricCard label="Out of stock" value={stats.out_of_stock ?? 0} color="red" icon={IconCircleX} sublabel="Require ordering" alert />
                        <MetricCard label="Expiring soon" value={stats.expiring_soon ?? 0} color="grape" icon={IconClock} sublabel={nextExpiry ? `Next: ${nextExpiry.raw}` : 'Within 30 days'} alert />
                        <MetricCard label="Expired" value={stats.expired ?? 0} color="red" icon={IconCalendar} sublabel="Remove from stock" alert />
                        <MetricCard label="Controlled" value={stats.controlled ?? 0} color="brandPurple" icon={IconShieldLock} sublabel="Require witness" />
                    </SimpleGrid>
                    )}

                    {/* tab views (left) + Alerts & Activity sidebar (right) — sidebar persists across tabs */}
                    <Grid gutter="xl" columns={10}>
                        <Grid.Col span={{ base: 10, lg: 8 }}>
                            {view === 'overview' && (
                            <Box style={{ ...surface, padding: '16px 18px', transform: 'scale(0.97)', transformOrigin: 'top left' }}>
                                {/* toolbar */}
                                <Group gap="lg" mb="md" wrap="wrap">
                                    <TextInput flex={1} miw={220} radius="md" leftSection={<IconSearch size={16} />}
                                        placeholder="Search meds or resident…" value={search} onChange={(e) => setSearch(e.currentTarget.value)} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={statusFilter} onChange={(v) => setStatusFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'All statuses' }, { value: 'in_stock', label: 'In stock' }, { value: 'low', label: 'Low stock' }, { value: 'critical', label: 'Critical' }, { value: 'expiring', label: 'Expiring soon' }, { value: 'expired', label: 'Expired' }]} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={stockFilter} onChange={(v) => setStockFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'Any stock' }, { value: 'high', label: 'Healthy' }, { value: 'medium', label: 'Getting low' }, { value: 'low', label: 'Critical' }]} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={expiryFilter} onChange={(v) => setExpiryFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'Any expiry' }, { value: 'expiring', label: 'Expiring ≤30d' }, { value: 'expired', label: 'Expired' }, { value: 'ok', label: 'In date' }]} />
                                    <Tooltip label="Export (coming soon)" withArrow><Button variant="default" radius="md" ml="auto" leftSection={<IconDownload size={16} />} disabled>Export</Button></Tooltip>
                                </Group>

                                {/* Bulk actions — appear when rows are ticked */}
                                {selected.size > 0 && (
                                    <Group justify="space-between" wrap="wrap" gap="sm" px="sm" py={8} mb="md"
                                        style={{
                                            background: 'light-dark(var(--mantine-color-brandTeal-0), var(--mantine-color-dark-5))',
                                            border: '1px solid light-dark(var(--mantine-color-brandTeal-2), var(--mantine-color-dark-4))',
                                            borderRadius: 12,
                                        }}>
                                        <Group gap="xs" wrap="nowrap">
                                            <Badge color="brandTeal" variant="filled" radius="sm">{selected.size}</Badge>
                                            <Text size="sm" fw={600}>selected</Text>
                                        </Group>
                                        <Group gap="xs" wrap="wrap">
                                            <Button size="compact-sm" variant="light" color="brandTeal" leftSection={<IconArrowsExchange size={14} />}>Transfer</Button>
                                            <Button size="compact-sm" variant="light" color="brandTeal" leftSection={<IconDownload size={14} />}>Export</Button>
                                            <Button size="compact-sm" variant="light" color="brandTeal" leftSection={<IconArchive size={14} />}>Archive</Button>
                                            <Button size="compact-sm" variant="light" color="brandTeal" leftSection={<IconPrinter size={14} />}>Print</Button>
                                            <Button size="compact-sm" variant="subtle" color="gray" leftSection={<IconX size={14} />} onClick={clearSelection}>Clear</Button>
                                        </Group>
                                    </Group>
                                )}

                                <Box style={{ maxHeight: 600, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                    <Table className={classes.invTable} highlightOnHover stickyHeader verticalSpacing="sm" miw={920}
                                        style={{ '--table-hover-color': 'light-dark(#f4fcfc, var(--mantine-color-dark-5))' }}>
                                        <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                            <Table.Tr>
                                                <Table.Th w={36}>
                                                    <Checkbox size="xs" color="brandTeal" checked={allSelected} indeterminate={someSelected} onChange={toggleAll} aria-label="Select all" />
                                                </Table.Th>
                                                <SortTh label="Medication" sortKey="medication" sort={sort} onSort={onSort} />
                                                <SortTh label="Stock" sortKey="stock" sort={sort} onSort={onSort} />
                                                <Table.Th>Status</Table.Th>
                                                <SortTh label="Resident" sortKey="resident" sort={sort} onSort={onSort} />
                                                <SortTh label="Expiry" sortKey="expiry" sort={sort} onSort={onSort} />
                                                <Table.Th>Last activity</Table.Th>
                                                <Table.Th ta="right">Actions</Table.Th>
                                            </Table.Tr>
                                        </Table.Thead>
                                        <Table.Tbody>
                                            {rows.length ? rows : (
                                                <Table.Tr><Table.Td colSpan={8}><Text c="dimmed" ta="center" py="lg">No medications match your filters.</Text></Table.Td></Table.Tr>
                                            )}
                                        </Table.Tbody>
                                    </Table>
                                </Box>
                            </Box>
                            )}

                            {view === 'transactions' && (
                                <Box style={{ ...surface, padding: '16px 18px' }}>
                                    <Text fw={700} mb="md">Transactions</Text>
                                    <Box style={{ maxHeight: 620, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                        <Table className={classes.invTable} highlightOnHover stickyHeader verticalSpacing="sm" miw={760}
                                            style={{ '--table-hover-color': 'light-dark(var(--mantine-color-brandTeal-0), var(--mantine-color-dark-5))' }}>
                                            <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                                <Table.Tr>
                                                    <Table.Th>When</Table.Th><Table.Th>Type</Table.Th><Table.Th>Medication</Table.Th>
                                                    <Table.Th>Change</Table.Th><Table.Th>Balance</Table.Th><Table.Th>By</Table.Th>
                                                </Table.Tr>
                                            </Table.Thead>
                                            <Table.Tbody>
                                                {transactions.length ? transactions.map((t, i) => {
                                                    const inflow = isInflow(t.type); const a = activityMeta(t.type);
                                                    return (
                                                        <Table.Tr key={t.id ?? i}>
                                                            <Table.Td><Text size="sm">{relTime(parseDate(t.date)) ?? t.date}</Text></Table.Td>
                                                            <Table.Td><Group gap={6} wrap="nowrap"><ThemeIcon variant="light" color={a.color} size={24} radius="xl"><a.Icon size={13} /></ThemeIcon><Text size="sm" tt="capitalize">{t.type}</Text></Group></Table.Td>
                                                            <Table.Td><Text size="sm">{t.medication_name}</Text></Table.Td>
                                                            <Table.Td><Text size="sm" fw={700} c={inflow ? 'green.7' : 'red.7'}>{inflow ? '+' : '−'}{t.quantity}</Text></Table.Td>
                                                            <Table.Td><Text size="sm">{t.balance_after}</Text></Table.Td>
                                                            <Table.Td><Text size="sm">{t.performed_by ?? '—'}</Text></Table.Td>
                                                        </Table.Tr>
                                                    );
                                                }) : <Table.Tr><Table.Td colSpan={6}><Text c="dimmed" ta="center" py="lg">No transactions yet.</Text></Table.Td></Table.Tr>}
                                            </Table.Tbody>
                                        </Table>
                                    </Box>
                                </Box>
                            )}

                            {view === 'reorders' && (
                                <Box style={{ ...surface, padding: '16px 18px' }}>
                                    <Group justify="space-between" mb="md">
                                        <Text fw={700}>Reorders needed</Text>
                                        {isManager && lowList.length > 0 && <Button size="sm" radius="md" leftSection={<IconReload size={15} />} onClick={adjust.open}>Reorder all</Button>}
                                    </Group>
                                    {lowList.length ? (
                                        <Box style={{ maxHeight: 620, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                            <Table className={classes.invTable} highlightOnHover verticalSpacing="sm" miw={680}>
                                                <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                                    <Table.Tr><Table.Th>Medication</Table.Th><Table.Th>Stock</Table.Th><Table.Th>Reorder level</Table.Th><Table.Th>Status</Table.Th><Table.Th ta="right">Action</Table.Th></Table.Tr>
                                                </Table.Thead>
                                                <Table.Tbody>
                                                    {lowList.map((m) => { const st = statusOf(m); const f = medForm(m.medication_name, m.unit); return (
                                                        <Table.Tr key={m.id}>
                                                            <Table.Td><Group gap="sm" wrap="nowrap"><ThemeIcon variant="light" color={m.is_controlled ? 'grape' : f.color} size={32} radius="xl"><f.Icon size={17} /></ThemeIcon><div><Text size="sm" fw={600}>{m.medication_name}</Text><Text size="xs" c="dimmed">{f.route} • {f.label}</Text></div></Group></Table.Td>
                                                            <Table.Td><Text size="sm" fw={700} c={isOut(m) ? 'red' : 'orange'}>{num(m.stock_level, m.unit)}</Text></Table.Td>
                                                            <Table.Td><Text size="sm">{num(m.reorder_level, m.unit)}</Text></Table.Td>
                                                            <Table.Td><Badge color={st.color} variant="light" tt="none">{st.label}</Badge></Table.Td>
                                                            <Table.Td ta="right">{isManager && <Button size="compact-sm" variant="light" color="orange" leftSection={<IconReload size={13} />} onClick={adjust.open}>Reorder</Button>}</Table.Td>
                                                        </Table.Tr>
                                                    ); })}
                                                </Table.Tbody>
                                            </Table>
                                        </Box>
                                    ) : <Text c="dimmed" ta="center" py="xl">Nothing needs reordering right now.</Text>}
                                </Box>
                            )}

                            {view === 'disposals' && (
                                <Box style={{ ...surface, padding: '16px 18px' }}>
                                    <Text fw={700} mb="md">Disposals</Text>
                                    {disposals.length ? (
                                        <Box style={{ maxHeight: 620, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                            <Table className={classes.invTable} highlightOnHover verticalSpacing="sm" miw={640}>
                                                <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                                    <Table.Tr><Table.Th>When</Table.Th><Table.Th>Medication</Table.Th><Table.Th>Qty</Table.Th><Table.Th>Reason</Table.Th><Table.Th>By</Table.Th></Table.Tr>
                                                </Table.Thead>
                                                <Table.Tbody>
                                                    {disposals.map((t, i) => (
                                                        <Table.Tr key={t.id ?? i}>
                                                            <Table.Td><Text size="sm">{relTime(parseDate(t.date)) ?? t.date}</Text></Table.Td>
                                                            <Table.Td><Text size="sm">{t.medication_name}</Text></Table.Td>
                                                            <Table.Td><Text size="sm" fw={700} c="red.7">−{t.quantity}</Text></Table.Td>
                                                            <Table.Td><Text size="sm" c="dimmed">{t.reason ?? '—'}</Text></Table.Td>
                                                            <Table.Td><Text size="sm">{t.performed_by ?? '—'}</Text></Table.Td>
                                                        </Table.Tr>
                                                    ))}
                                                </Table.Tbody>
                                            </Table>
                                        </Box>
                                    ) : <Text c="dimmed" ta="center" py="xl">No disposals recorded.</Text>}
                                </Box>
                            )}
                        </Grid.Col>

                        {/* Alerts & Activity — one intentional sidebar */}
                        <Grid.Col span={{ base: 10, lg: 2 }}>
                            <Box style={{ ...surface, padding: '16px 16px' }}>
                                <Text size="xs" fw={700} c="dimmed" tt="uppercase" mb="md" style={{ letterSpacing: 0.5 }}>Alerts &amp; Activity</Text>

                                {lowList.length > 0 && (
                                    <Box mb="xl">
                                        <Group justify="space-between" wrap="nowrap" mb={10} onClick={lowDrawer.open} style={{ cursor: 'pointer' }}>
                                            <Group gap={6} wrap="nowrap">
                                                <IconAlertTriangle size={16} stroke={1.9} color="var(--mantine-color-orange-6)" />
                                                <Text fw={800}>Low Stock</Text>
                                                <Badge size="sm" variant="light" color="orange" radius="sm">{lowList.length}</Badge>
                                            </Group>
                                            <Group gap={3} wrap="nowrap">
                                                <Text size="xs" fw={600} c="orange.7">View all</Text>
                                                <IconArrowRight size={13} color="var(--mantine-color-orange-7)" />
                                            </Group>
                                        </Group>
                                        <Stack gap={8}>
                                            {lowList.slice(0, 5).map((m) => (
                                                <Group key={m.id} justify="space-between" wrap="nowrap">
                                                    <Text size="sm" fw={500} lineClamp={1}>{m.medication_name}</Text>
                                                    <Text size="xs" fw={700} c={isOut(m) ? 'red' : 'orange'} style={{ whiteSpace: 'nowrap' }}>{num(m.stock_level)} left</Text>
                                                </Group>
                                            ))}
                                        </Stack>
                                    </Box>
                                )}

                                <Text fw={700} mb="md">Recent Activity</Text>
                                {transactions.length ? (
                                    <Stack gap={0}>
                                        {transactions.slice(0, 12).map((t, i) => {
                                            const inflow = isInflow(t.type);
                                            const a = activityMeta(t.type);
                                            return (
                                                <Group key={t.id ?? i} gap="xs" wrap="nowrap" align="flex-start" py={7}
                                                    style={{ borderTop: i ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                    <ThemeIcon variant="light" color={a.color} size={24} radius="xl"><a.Icon size={13} /></ThemeIcon>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Text size="xs" c="dimmed" fw={600} tt="capitalize" lh={1.15}>{t.type}</Text>
                                                        <Text size="sm" fw={600} lineClamp={1} lh={1.3} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{t.medication_name}</Text>
                                                        <Group justify="space-between" wrap="nowrap" gap={6} mt={2}>
                                                            <Text fz={11} c="dimmed" style={{ whiteSpace: 'nowrap' }}>{relTime(parseDate(t.date)) ?? t.date}</Text>
                                                            <Text fz={12} fw={600} c={inflow ? 'green.6' : 'red.6'} style={{ whiteSpace: 'nowrap' }}>{inflow ? '+' : '−'}{t.quantity}</Text>
                                                        </Group>
                                                    </Box>
                                                </Group>
                                            );
                                        })}
                                    </Stack>
                                ) : <Text size="sm" c="dimmed">No activity yet.</Text>}
                            </Box>
                        </Grid.Col>
                    </Grid>
                  </Box>

                    <AdjustStockModal opened={adjustOpened} onClose={adjust.close} meds={meds} />

                    {/* Low-stock drawer — opened from the Low Stock card */}
                    <Drawer opened={lowOpened} onClose={lowDrawer.close} position="right" size={360}
                        title={<Group gap={8}><IconAlertTriangle size={20} color="var(--mantine-color-orange-6)" /><Text fw={800} fz="lg">Low stock</Text></Group>}>
                        <Stack gap="md">
                            {lowList.length ? lowList.map((m) => {
                                const f = medForm(m.medication_name, m.unit);
                                return (
                                    <Group key={m.id} justify="space-between" wrap="nowrap" align="flex-start">
                                        <Box style={{ minWidth: 0 }}>
                                            <Text fw={600} size="sm" lineClamp={1}>{m.medication_name}</Text>
                                            <Text size="xs" c="dimmed">{f.route} • {f.label}</Text>
                                        </Box>
                                        <Text fw={700} size="sm" c={isOut(m) ? 'red' : 'orange'} style={{ whiteSpace: 'nowrap' }}>{num(m.stock_level, m.unit)} left</Text>
                                    </Group>
                                );
                            }) : <Text size="sm" c="dimmed">Nothing is low on stock.</Text>}

                            {isManager && lowList.length > 0 && (
                                <Button mt="sm" radius="md" color="orange" leftSection={<IconReload size={16} />}
                                    onClick={() => { lowDrawer.close(); adjust.open(); }}>Reorder</Button>
                            )}
                        </Stack>
                    </Drawer>

                    {/* Side drawer — med detail */}
                    <Drawer opened={!!drawerMed} onClose={() => setDrawerMed(null)} position="right" size={400}
                        title={<Text fw={800} fz="lg">{drawerMed?.medication_name}</Text>}>
                        {drawerMed && (
                            <Stack gap="lg">
                                <Group>
                                    <Badge color={statusOf(drawerMed).color} variant="light" size="lg" tt="none">{statusOf(drawerMed).label}</Badge>
                                    {drawerForm && <Badge color="gray" variant="light" size="lg">{drawerForm.route} • {drawerForm.label}</Badge>}
                                    {drawerMed.is_controlled && <Badge color="grape" variant="light" size="lg">CD {drawerMed.cd_schedule}</Badge>}
                                </Group>

                                <SimpleGrid cols={2} spacing="md">
                                    <Box><Text size="xs" c="dimmed">Current stock</Text><Text fw={800} fz={28} lh={1.1}>{num(drawerMed.stock_level, drawerMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Reorder level</Text><Text fw={700} fz="lg">{num(drawerMed.reorder_level, drawerMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Resident</Text><Text fw={600} size="sm">{drawerMed.resident ?? '—'}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Route</Text><Text fw={600} size="sm">{drawerForm?.route ?? '—'}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Form</Text><Text fw={600} size="sm">{drawerForm?.label ?? '—'}</Text></Box>
                                    <Box>
                                        <Text size="xs" c="dimmed">Last activity</Text>
                                        <Text fw={600} size="sm">{(() => { const l = lastTxByMed[drawerMed.medication_name]; return l ? (relTime(parseDate(l.date)) ?? l.date) : 'None'; })()}</Text>
                                    </Box>
                                    <Box><Text size="xs" c="dimmed">Expiry</Text><Text fw={600} size="sm" c={drawerMed.expired ? 'red' : undefined}>{drawerMed.expiry_date ?? 'No expiry'}</Text></Box>
                                </SimpleGrid>

                                <Divider label="Batch & supply" labelPosition="left" />
                                <SimpleGrid cols={2} spacing="md">
                                    <Box><Text size="xs" c="dimmed">Batch number</Text><Text fw={600} size="sm" c="dimmed">Not recorded</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Supplier</Text><Text fw={600} size="sm" c="dimmed">Not recorded</Text></Box>
                                    <Box>
                                        <Text size="xs" c="dimmed">Last delivery</Text>
                                        <Text fw={600} size="sm" c="dimmed">
                                            {(() => { const d = transactions.find((t) => t.medication_name === drawerMed.medication_name && isInflow(t.type)); return d ? (relTime(parseDate(d.date)) ?? d.date) : '—'; })()}
                                        </Text>
                                    </Box>
                                    <Box><Text size="xs" c="dimmed">Home</Text><Text fw={600} size="sm" c="dimmed">This home</Text></Box>
                                </SimpleGrid>

                                <Divider label="Recent transactions" labelPosition="left" />
                                {drawerTx.length ? (
                                    <Stack gap={8}>
                                        {drawerTx.map((t) => (
                                            <Group key={t.id} justify="space-between" wrap="nowrap">
                                                <Group gap="xs" wrap="nowrap">
                                                    <Text size="sm" fw={700} c={isInflow(t.type) ? 'green' : 'red'}>{isInflow(t.type) ? '+' : '−'}{t.quantity}</Text>
                                                    <Badge variant="light" color="gray" tt="capitalize" radius="sm">{t.type}</Badge>
                                                </Group>
                                                <Text size="xs" c="dimmed">{relTime(parseDate(t.date)) ?? t.date}</Text>
                                            </Group>
                                        ))}
                                    </Stack>
                                ) : <Text size="sm" c="dimmed">No transactions for this medication.</Text>}

                                <Stack gap="sm" mt="sm">
                                    {isManager && <Button radius="md" leftSection={<IconEdit size={16} />} onClick={() => { setDrawerMed(null); adjust.open(); }}>Adjust stock</Button>}
                                    <Button variant="light" radius="md" leftSection={<IconArrowsExchange size={16} />} disabled>Transfer (coming soon)</Button>
                                    <Button variant="default" radius="md" leftSection={<IconHistory size={16} />} disabled>Full history (coming soon)</Button>
                                </Stack>
                            </Stack>
                        )}
                    </Drawer>
                </Container>
            </Box>
        </>
    );
}

StockLab2.layout = (page) => <AppShell>{page}</AppShell>;
