import { useState } from 'react';
import { Head } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, SimpleGrid, Badge, Button, Box, Menu, ActionIcon,
    ThemeIcon, Progress, Drawer, Stack, Divider, Select, Collapse,
    SegmentedControl, Checkbox, Table, TextInput, Tooltip,
} from '@mantine/core';
import {
    IconBox, IconAlertTriangle, IconCircleX, IconClock, IconCalendar,
    IconPill, IconDots, IconFilter, IconPlus, IconSearch,
    IconArrowsExchange, IconDownload, IconArchive, IconPrinter, IconX,
    IconVaccine, IconDroplet, IconEye, IconLungs, IconBottle, IconBandage,
    IconArrowDown, IconArrowRight, IconSelector, IconChevronUp, IconChevronDown,
} from '@tabler/icons-react';
import StatCard from '@frontend/components/StatCard';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import AdjustStockModal from '@frontend/features/medications/AdjustStockModal';
import AppShell from '@frontend/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';
import { downloadCsv } from '@frontend/lib/csv';
import classes from './Stock.module.css';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
const parseDate = (s) => { const t = Date.parse(s); return Number.isNaN(t) ? null : t; };

// Meaningful status (sentence case).
function statusMeta(m) {
    if (m.expired) return { label: 'Expired', color: 'red' };
    if (isOut(m)) return { label: 'Critical', color: 'red' };
    if (m.low) return { label: 'Low stock', color: 'orange' };
    if (m.expiring_soon) return { label: 'Expiring soon', color: 'grape' };
    return { label: 'In stock', color: 'green' };
}

// Stock bar — colour-graded by headroom AND absolute quantity (small stocks read red).
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

// Guess dosage form + route from the name/unit.
function medForm(name = '', unit = '') {
    const s = `${name} ${unit}`.toLowerCase();
    if (/cream|ointment|gel|emollient/.test(s)) return { label: 'Cream', route: 'Topical', Icon: IconBottle, color: 'pink' };
    if (/inhaler|inhalation|nebuli/.test(s)) return { label: 'Inhaler', route: 'Inhaled', Icon: IconLungs, color: 'cyan' };
    if (/eye|ocular/.test(s) && /drop/.test(s)) return { label: 'Eye drops', route: 'Ocular', Icon: IconEye, color: 'indigo' };
    if (/drop/.test(s)) return { label: 'Drops', route: 'Topical', Icon: IconDroplet, color: 'indigo' };
    if (/injection|inject|vial|ampoule|vaccine/.test(s)) return { label: 'Injection', route: 'Injection', Icon: IconVaccine, color: 'red' };
    if (/patch/.test(s)) return { label: 'Patch', route: 'Transdermal', Icon: IconBandage, color: 'grape' };
    if (/solution|suspension|syrup|liquid|elixir/.test(s) || /ml/.test(String(unit).toLowerCase())) return { label: 'Oral solution', route: 'Oral', Icon: IconDroplet, color: 'teal' };
    if (/capsule|cap\b/.test(s)) return { label: 'Capsule', route: 'Oral', Icon: IconPill, color: 'indigo' };
    return { label: 'Tablet', route: 'Oral', Icon: IconPill, color: 'indigo' };
}
const strengthOf = (name = '') => { const m = String(name).match(/(\d+(?:\.\d+)?\s?(?:mg|mcg|ml|g|%|units?))/i); return m ? m[1].replace(/\s+/g, '') : null; };

function trendOf(m) {
    if (m.expired || isOut(m) || stockBar(m).pct < 20) return { Icon: IconArrowDown, color: 'red', label: 'Critical' };
    if (m.low) return { Icon: IconArrowDown, color: 'orange', label: 'Declining' };
    return { Icon: IconArrowRight, color: 'green', label: 'Stable' };
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

// Sortable header cell.
function SortTh({ label, sortKey, sort, onSort, ta, w, visibleFrom }) {
    const active = sort.key === sortKey;
    const Icon = !active ? IconSelector : (sort.dir === 'asc' ? IconChevronUp : IconChevronDown);
    return (
        <Table.Th className={classes.sortTh} ta={ta} w={w} visibleFrom={visibleFrom} onClick={() => onSort(sortKey)}>
            <Group gap={4} wrap="nowrap" justify={ta === 'right' ? 'flex-end' : 'flex-start'}>
                <span>{label}</span><Icon size={13} style={{ opacity: active ? 1 : 0.4 }} />
            </Group>
        </Table.Th>
    );
}

// Matches the Medication Round look: white card, soft shadow, hairline border.
const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

export default function Stock({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const [adjustOpened, adjust] = useDisclosure(false);
    const [historyMed, setHistoryMed] = useState(null);
    const isInflow = (type = '') => /deliver|receiv|return|add|restock|order/i.test(type);
    const historyTx = historyMed ? transactions.filter((t) => t.medication_name === historyMed.medication_name) : [];
    const drawerForm = historyMed ? medForm(historyMed.medication_name, historyMed.unit) : null;
    const updatedAgo = (() => { const newest = transactions.map((t) => parseDate(t.date)).filter(Boolean).sort((a, b) => b - a)[0]; return newest ? relTime(newest) : 'just now'; })();

    // --- inventory filters ---
    const [filtersOpen, filters] = useDisclosure(false);
    const [statusFilter, setStatusFilter] = useState('all');
    const [stockFilter, setStockFilter] = useState('all');
    const [expiryFilter, setExpiryFilter] = useState('all');
    const statusKey = (m) => (m.expired ? 'expired' : (m.stock_level == 0 ? 'critical' : m.low ? 'low' : m.expiring_soon ? 'expiring' : 'in_stock'));
    const activeFilters = (statusFilter !== 'all') + (stockFilter !== 'all') + (expiryFilter !== 'all');
    const clearFilters = () => { setStatusFilter('all'); setStockFilter('all'); setExpiryFilter('all'); };
    const filteredMeds = meds.filter((m) => {
        if (statusFilter !== 'all' && statusKey(m) !== statusFilter) return false;
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

    // --- top tabs + search + bulk selection ---
    const [view, setView] = useState('overview');
    const [search, setSearch] = useState('');
    const [selected, setSelected] = useState(() => new Set());
    const disposals = transactions.filter((t) => /dispos|waste|destroy/i.test(t.type || ''));
    const lowList = meds.filter((m) => m.low || (m.stock_level != null && Number(m.stock_level) === 0));
    const searchedMeds = (() => {
        const q = search.trim().toLowerCase();
        return q ? filteredMeds.filter((m) => `${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(q)) : filteredMeds;
    })();
    const visibleIds = searchedMeds.map((m) => m.id);
    const allSelected = visibleIds.length > 0 && visibleIds.every((id) => selected.has(id));
    const someSelected = visibleIds.some((id) => selected.has(id)) && !allSelected;
    const toggleOne = (id) => setSelected((p) => { const n = new Set(p); n.has(id) ? n.delete(id) : n.add(id); return n; });
    const toggleAll = () => setSelected((p) => { const every = visibleIds.every((id) => p.has(id)); const n = new Set(p); visibleIds.forEach((id) => (every ? n.delete(id) : n.add(id))); return n; });
    const clearSelection = () => setSelected(new Set());
    const [sort, setSort] = useState({ key: null, dir: 'asc' });
    const onSort = (key) => setSort((s) => (s.key === key ? { key, dir: s.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' }));
    const sortedMeds = (() => {
        const arr = [...searchedMeds]; const { key, dir } = sort; if (!key) return arr; const sgn = dir === 'asc' ? 1 : -1;
        arr.sort((a, b) => {
            if (key === 'medication') return sgn * String(a.medication_name).localeCompare(String(b.medication_name));
            if (key === 'resident') return sgn * String(a.resident ?? '').localeCompare(String(b.resident ?? ''));
            if (key === 'stock') return sgn * (Number(a.stock_level ?? -1) - Number(b.stock_level ?? -1));
            if (key === 'expiry') return sgn * ((parseDate(a.expiry_date) ?? Infinity) - (parseDate(b.expiry_date) ?? Infinity));
            return 0;
        });
        return arr;
    })();
    const lastTxByMed = (() => { const map = {}; for (const t of transactions) if (!(t.medication_name in map)) map[t.medication_name] = t; return map; })();
    const tab = (label, count) => (
        <Group gap={8} wrap="nowrap" justify="center">
            <Text size="sm" fw={600} span>{label}</Text>
            <Badge size="sm" variant="light" color="gray" circle>{count}</Badge>
        </Group>
    );
    const statusBadgeFor = (m) => { const s = statusMeta(m); return <Badge color={s.color} variant="light" tt="none">{s.label}</Badge>; };

    const exportCsv = () => downloadCsv('medication-stock.csv', [
        { header: 'Medication', value: (m) => m.medication_name },
        { header: 'Resident', value: (m) => m.resident },
        { header: 'Stock', value: (m) => m.stock_level },
        { header: 'Unit', value: (m) => m.unit },
        { header: 'Reorder level', value: (m) => m.reorder_level },
        { header: 'Status', value: (m) => statusMeta(m).label },
        { header: 'Expiry', value: (m) => m.expiry_date },
        { header: 'Controlled', value: (m) => (m.is_controlled ? `CD ${m.cd_schedule ?? ''}` : '') },
    ], sortedMeds);

    const reorderColumns = [
        { key: 'medication_name', label: 'Medication' },
        { key: 'resident', label: 'Resident' },
        { key: 'stock_level', label: 'Stock', render: (m) => num(m.stock_level, m.unit) },
        { key: 'reorder_level', label: 'Reorder level', render: (m) => num(m.reorder_level, m.unit) },
        { key: 'status', label: 'Status', sortable: false, render: statusBadgeFor },
    ];
    const disposalColumns = [
        { key: 'date', label: 'Date' },
        { key: 'medication_name', label: 'Medication' },
        { key: 'quantity', label: 'Qty', render: (t) => num(t.quantity, t.unit) },
        { key: 'reason', label: 'Reason', render: (t) => t.reason ?? '—' },
        { key: 'performed_by', label: 'By' },
    ];

    const txColumns = [
        { key: 'date', label: 'Date' },
        { key: 'type', label: 'Type', render: (t) => <StatusBadge status={t.type} /> },
        { key: 'medication_name', label: 'Medication' },
        { key: 'quantity', label: 'Qty', render: (t) => num(t.quantity, t.unit) },
        { key: 'balance_after', label: 'Balance' },
        { key: 'performed_by', label: 'By' },
    ];

    return (
        <>
            <Head title="Medication Stock" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  {/* Whole page content scaled to 97% — smaller, same proportions & spacing. */}
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header card — icon chip + title + actions, matching the Round */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="lg">
                        <Group justify="space-between" align="center" wrap="wrap" gap="md">
                            <Group gap="sm" wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="brandTeal" size={40} radius="md"><IconBox size={22} stroke={1.7} /></ThemeIcon>
                                <Box>
                                    <Text fz={24} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>Medication Stock</Text>
                                    <Text c="dimmed" fz="sm">View and manage all medication inventory across your locations.</Text>
                                </Box>
                            </Group>
                            <Group gap="sm" wrap="nowrap">
                                <Text size="xs" c="dimmed" visibleFrom="sm">Updated {updatedAgo}</Text>
                                <Button variant={filtersOpen || activeFilters ? 'light' : 'default'} radius="md" leftSection={<IconFilter size={16} />}
                                    onClick={filters.toggle} rightSection={activeFilters ? <Badge size="sm" circle variant="filled">{activeFilters}</Badge> : null}>Filter</Button>
                                {role === 'manager'
                                    ? <Button radius="md" leftSection={<IconPlus size={16} />} onClick={adjust.open}>Adjust stock</Button>
                                    : <Badge variant="light" color="gray" size="lg" radius="sm">View only</Badge>}
                            </Group>
                        </Group>
                    </Box>

                    <FlashAlerts />

                    <SimpleGrid cols={{ base: 2, sm: 3, lg: 5 }} mb="lg" spacing="md">
                        <StatCard label="Total items" value={stats.total ?? 0} color="indigo" icon={IconBox} sublabel="In this home" />
                        <StatCard label="Low stock" value={stats.low ?? 0} color="orange" icon={IconAlertTriangle} sublabel="Need attention" />
                        <StatCard label="Out of stock" value={stats.out_of_stock ?? 0} color="red" icon={IconCircleX} sublabel="Require ordering" />
                        <StatCard label="Expiring soon" value={stats.expiring_soon ?? 0} color="grape" icon={IconClock} sublabel="Within 30 days" />
                        <StatCard label="Expired" value={stats.expired ?? 0} color="red" icon={IconCalendar} sublabel="Remove from stock" />
                    </SimpleGrid>

                    <Box style={{ ...surface, padding: '16px 18px' }}>
                        <SegmentedControl value={view} onChange={setView} radius={12} mb="md"
                            data={[
                                { value: 'overview', label: tab('Stock Overview', filteredMeds.length) },
                                { value: 'transactions', label: tab('Transactions', transactions.length) },
                                { value: 'reorders', label: tab('Reorders', lowList.length) },
                                { value: 'disposals', label: tab('Disposals', disposals.length) },
                            ]} />

                        {view === 'overview' && (
                            <>
                                <Collapse in={filtersOpen}>
                                    <Group gap="sm" mb="md" wrap="wrap" align="flex-end">
                                        <Select label="Status" radius="md" w={150} checkIconPosition="right" value={statusFilter} onChange={(v) => setStatusFilter(v ?? 'all')}
                                            data={[{ value: 'all', label: 'All statuses' }, { value: 'in_stock', label: 'Good' }, { value: 'low', label: 'Low stock' }, { value: 'critical', label: 'Out of stock' }, { value: 'expiring', label: 'Expiring soon' }, { value: 'expired', label: 'Expired' }]} />
                                        <Select label="Stock level" radius="md" w={150} checkIconPosition="right" value={stockFilter} onChange={(v) => setStockFilter(v ?? 'all')}
                                            data={[{ value: 'all', label: 'Any stock' }, { value: 'high', label: 'Healthy' }, { value: 'medium', label: 'Getting low' }, { value: 'low', label: 'Critical' }]} />
                                        <Select label="Expiry" radius="md" w={150} checkIconPosition="right" value={expiryFilter} onChange={(v) => setExpiryFilter(v ?? 'all')}
                                            data={[{ value: 'all', label: 'Any expiry' }, { value: 'expiring', label: 'Expiring ≤30d' }, { value: 'expired', label: 'Expired' }, { value: 'ok', label: 'In date' }]} />
                                        {activeFilters > 0 && <Button variant="subtle" color="gray" radius="md" onClick={clearFilters}>Clear</Button>}
                                    </Group>
                                </Collapse>

                                <Group gap="sm" mb="md" wrap="wrap">
                                    <TextInput flex={1} miw={220} radius="md" leftSection={<IconSearch size={16} />}
                                        placeholder="Search meds or resident…" value={search} onChange={(e) => setSearch(e.currentTarget.value)} />
                                </Group>

                                {selected.size > 0 && (
                                    <Group justify="space-between" wrap="wrap" gap="sm" px="sm" py={8} mb="md"
                                        style={{ background: 'light-dark(var(--mantine-color-brandTeal-0), var(--mantine-color-dark-5))', border: '1px solid light-dark(var(--mantine-color-brandTeal-2), var(--mantine-color-dark-4))', borderRadius: 12 }}>
                                        <Group gap="xs" wrap="nowrap"><Badge color="brandTeal" variant="filled" radius="sm">{selected.size}</Badge><Text size="sm" fw={600}>selected</Text></Group>
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
                                    <Table className={classes.invTable} highlightOnHover stickyHeader verticalSpacing="sm" horizontalSpacing="sm"
                                        style={{ width: '100%', maxWidth: 1160, margin: '0 auto', tableLayout: 'fixed', '--table-hover-color': 'light-dark(#f4fcfc, var(--mantine-color-dark-5))' }}>
                                        <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                            <Table.Tr>
                                                <Table.Th w={44}><Checkbox size="xs" color="brandTeal" checked={allSelected} indeterminate={someSelected} onChange={toggleAll} aria-label="Select all" /></Table.Th>
                                                <SortTh label="Medication" sortKey="medication" sort={sort} onSort={onSort} w={300} />
                                                <SortTh label="Stock" sortKey="stock" sort={sort} onSort={onSort} w={150} />
                                                <Table.Th w={130}>Status</Table.Th>
                                                <SortTh label="Resident" sortKey="resident" sort={sort} onSort={onSort} w={160} visibleFrom="md" />
                                                <SortTh label="Expiry" sortKey="expiry" sort={sort} onSort={onSort} w={130} />
                                                <Table.Th w={120} visibleFrom="lg">Last activity</Table.Th>
                                                <Table.Th w={96} ta="right">Actions</Table.Th>
                                            </Table.Tr>
                                        </Table.Thead>
                                        <Table.Tbody>
                                            {sortedMeds.length ? sortedMeds.map((m) => {
                                                const bar = stockBar(m); const sel = selected.has(m.id);
                                                const form = medForm(m.medication_name, m.unit); const strength = strengthOf(m.medication_name);
                                                const trend = trendOf(m); const last = lastTxByMed[m.medication_name];
                                                const lastAgo = last ? relTime(parseDate(last.date)) : null;
                                                return (
                                                    <Table.Tr key={m.id} bg={sel ? 'light-dark(var(--mantine-color-brandTeal-0), var(--mantine-color-dark-5))' : undefined}>
                                                        <Table.Td w={40} style={{ boxShadow: sel ? 'inset 4px 0 0 0 var(--mantine-color-brandTeal-6)' : undefined }}>
                                                            <Checkbox size="xs" color="brandTeal" checked={sel} onChange={() => toggleOne(m.id)} aria-label="Select row" />
                                                        </Table.Td>
                                                        <Table.Td>
                                                            <Group gap="sm" wrap="nowrap">
                                                                <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : form.color} size={34} radius="xl" style={{ flexShrink: 0 }}><form.Icon size={18} /></ThemeIcon>
                                                                <Box style={{ minWidth: 0 }}>
                                                                    <Text fw={600} size="sm" truncate>{m.medication_name}</Text>
                                                                    <Text size="xs" c="dimmed" truncate>{form.route} • {form.label}{strength ? ` • ${strength}` : ''}{m.is_controlled ? ` • CD ${m.cd_schedule ?? ''}` : ''}</Text>
                                                                </Box>
                                                            </Group>
                                                        </Table.Td>
                                                        <Table.Td>
                                                            <Group justify="space-between" align="flex-end" wrap="nowrap" mb={4} gap={4}>
                                                                <Box style={{ minWidth: 0 }}>
                                                                    <Text fw={800} fz="md" lh={1}>{m.stock_level ?? '—'}</Text>
                                                                    <Text size="xs" c="dimmed" lh={1.2} truncate>{m.unit ?? 'units'}</Text>
                                                                </Box>
                                                                <Tooltip label={trend.label} withArrow><trend.Icon size={16} color={`var(--mantine-color-${trend.color}-6)`} style={{ flexShrink: 0 }} /></Tooltip>
                                                            </Group>
                                                            <Progress value={bar.pct} color={bar.color} size="sm" radius="xl" />
                                                        </Table.Td>
                                                        <Table.Td>{statusBadgeFor(m)}</Table.Td>
                                                        <Table.Td><Text size="sm" truncate>{m.resident ?? '—'}</Text></Table.Td>
                                                        <Table.Td><Text size="sm" truncate c={m.expired ? 'red' : undefined}>{m.expiry_date ?? '—'}</Text></Table.Td>
                                                        <Table.Td visibleFrom="lg"><Text size="xs" c="dimmed" truncate>{lastAgo ?? '—'}</Text></Table.Td>
                                                        <Table.Td ta="right">
                                                            <Group justify="flex-end" wrap="nowrap" className={classes.rowActions}>
                                                                <Menu position="bottom-end" withinPortal>
                                                                    <Menu.Target><ActionIcon variant="subtle" color="gray.7"><IconDots size={18} /></ActionIcon></Menu.Target>
                                                                    <Menu.Dropdown>
                                                                        {role === 'manager' && <Menu.Item onClick={adjust.open}>Adjust stock</Menu.Item>}
                                                                        <Menu.Item onClick={() => setHistoryMed(m)}>View history</Menu.Item>
                                                                    </Menu.Dropdown>
                                                                </Menu>
                                                            </Group>
                                                        </Table.Td>
                                                    </Table.Tr>
                                                );
                                            }) : <Table.Tr><Table.Td colSpan={8}><Text c="dimmed" ta="center" py="lg">No medications match your filters.</Text></Table.Td></Table.Tr>}
                                        </Table.Tbody>
                                    </Table>
                                </Box>
                            </>
                        )}

                        {view === 'transactions' && (
                            <DataTable columns={txColumns} data={transactions} searchable pageSize={10} emptyMessage="No transactions yet." minWidth={720} />
                        )}
                        {view === 'reorders' && (
                            <DataTable columns={reorderColumns} data={lowList} searchable pageSize={10} emptyMessage="Nothing needs reordering right now." minWidth={680} />
                        )}
                        {view === 'disposals' && (
                            <DataTable columns={disposalColumns} data={disposals} searchable pageSize={10} emptyMessage="No disposals recorded." minWidth={640} />
                        )}
                    </Box>

                  </Box>
                    <AdjustStockModal opened={adjustOpened} onClose={adjust.close} meds={meds} />

                    {/* View history — transactions for the selected medication */}
                    <Drawer opened={!!historyMed} onClose={() => setHistoryMed(null)} position="right" size={400}
                        title={<Text fw={800} fz="lg">{historyMed?.medication_name}</Text>}>
                        {historyMed && (
                            <Stack gap="lg">
                                <Group>
                                    <Badge color={statusMeta(historyMed).color} variant="light" size="lg" tt="none">{statusMeta(historyMed).label}</Badge>
                                    {drawerForm && <Badge color="gray" variant="light" size="lg">{drawerForm.route} • {drawerForm.label}</Badge>}
                                    {historyMed.is_controlled && <Badge color="grape" variant="light" size="lg">CD {historyMed.cd_schedule}</Badge>}
                                </Group>

                                <SimpleGrid cols={2} spacing="md">
                                    <Box><Text size="xs" c="dimmed">Current stock</Text><Text fw={800} fz={26} lh={1.1}>{num(historyMed.stock_level, historyMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Reorder level</Text><Text fw={700} fz="lg">{num(historyMed.reorder_level, historyMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Resident</Text><Text fw={600} size="sm">{historyMed.resident ?? '—'}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Route</Text><Text fw={600} size="sm">{drawerForm?.route ?? '—'}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Form</Text><Text fw={600} size="sm">{drawerForm?.label ?? '—'}</Text></Box>
                                    <Box>
                                        <Text size="xs" c="dimmed">Last activity</Text>
                                        <Text fw={600} size="sm">{(() => { const l = lastTxByMed[historyMed.medication_name]; return l ? (relTime(parseDate(l.date)) ?? l.date) : 'None'; })()}</Text>
                                    </Box>
                                    <Box><Text size="xs" c="dimmed">Expiry</Text><Text fw={600} size="sm" c={historyMed.expired ? 'red' : undefined}>{historyMed.expiry_date ?? '—'}</Text></Box>
                                </SimpleGrid>

                                <Divider label="Batch & supply" labelPosition="left" />
                                <SimpleGrid cols={2} spacing="md">
                                    <Box><Text size="xs" c="dimmed">Batch number</Text><Text fw={600} size="sm" c="dimmed">Not recorded</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Supplier</Text><Text fw={600} size="sm" c="dimmed">Not recorded</Text></Box>
                                    <Box>
                                        <Text size="xs" c="dimmed">Last delivery</Text>
                                        <Text fw={600} size="sm" c="dimmed">{(() => { const d = transactions.find((t) => t.medication_name === historyMed.medication_name && isInflow(t.type)); return d ? (relTime(parseDate(d.date)) ?? d.date) : '—'; })()}</Text>
                                    </Box>
                                    <Box><Text size="xs" c="dimmed">Home</Text><Text fw={600} size="sm" c="dimmed">This home</Text></Box>
                                </SimpleGrid>

                                <Divider label="Recent transactions" labelPosition="left" />
                                {historyTx.length ? (
                                    <Stack gap={0}>
                                        {historyTx.map((t, i) => (
                                            <Group key={t.id ?? i} justify="space-between" wrap="nowrap" py={9}
                                                style={{ borderTop: i ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                <Box style={{ minWidth: 0 }}>
                                                    <Group gap={8} wrap="nowrap">
                                                        <Text size="sm" fw={700} c={isInflow(t.type) ? 'green.7' : 'red.7'}>{isInflow(t.type) ? '+' : '−'}{num(t.quantity, t.unit)}</Text>
                                                        <Badge variant="light" color="gray" tt="capitalize" radius="sm">{t.type}</Badge>
                                                    </Group>
                                                    <Text fz={11} c="dimmed" mt={2}>Balance: {t.balance_after ?? '—'} · {t.performed_by ?? '—'}</Text>
                                                </Box>
                                                <Text fz={11} c="dimmed" style={{ whiteSpace: 'nowrap' }}>{t.date}</Text>
                                            </Group>
                                        ))}
                                    </Stack>
                                ) : <Text size="sm" c="dimmed">No transactions recorded for this medication.</Text>}

                                <Stack gap="sm" mt="sm">
                                    {role === 'manager' && <Button radius="md" leftSection={<IconPlus size={16} />} onClick={() => { setHistoryMed(null); adjust.open(); }}>Adjust stock</Button>}
                                    <Button variant="light" radius="md" leftSection={<IconArrowsExchange size={16} />} disabled>Transfer (coming soon)</Button>
                                    <Button variant="default" radius="md" leftSection={<IconClock size={16} />} disabled>Full history (coming soon)</Button>
                                </Stack>
                            </Stack>
                        )}
                    </Drawer>
                </Container>
            </Box>
        </>
    );
}

Stock.layout = (page) => <AppShell>{page}</AppShell>;
