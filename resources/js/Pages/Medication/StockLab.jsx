import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, SimpleGrid, Badge, Button, Box, Stack, Divider, Grid,
    ThemeIcon, Progress, Table, TextInput, Select, Tooltip, ActionIcon, Drawer,
} from '@mantine/core';
import {
    IconBox, IconAlertTriangle, IconCircleX, IconClock, IconCalendar, IconShieldLock,
    IconPill, IconVaccine, IconDroplet, IconEye, IconLungs, IconBottle, IconBandage,
    IconSearch, IconEdit, IconHistory, IconArrowsExchange, IconTrash, IconPlus, IconReload,
    IconArrowDown, IconArrowRight, IconSelector, IconChevronUp, IconChevronDown,
    IconDownload, IconFilter, IconActivity, IconTruckDelivery,
} from '@tabler/icons-react';
import AdjustStockModal from '@frontend/features/medications/AdjustStockModal';
import AppShell from '@frontend/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';
import classes from './StockLab.module.css';

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

// --- stock bar: fill vs 2x reorder, colour-graded ---
function stockBar(m) {
    const stock = m.stock_level;
    if (stock === null || stock === undefined) return { pct: 0, color: 'gray' };
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(stock, 1);
    const pct = Math.min(100, Math.max(4, Math.round((stock / ref) * 100)));
    let color = 'teal';
    if (m.expired || isOut(m) || pct < 25) color = 'red';
    else if (m.low || pct < 55) color = 'yellow';
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
    if (mins < 60) return `${mins} min${mins > 1 ? 's' : ''} ago`;
    const h = Math.round(mins / 60);
    if (h < 24) return `${h}h ago`;
    const days = Math.round(h / 24);
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days}d ago`;
    return new Date(ms).toLocaleDateString();
}

function trendOf(m) {
    if (m.expired || isOut(m) || stockBar(m).pct < 25) return { Icon: IconArrowDown, color: 'red', label: 'Critical' };
    if (m.low) return { Icon: IconArrowDown, color: 'orange', label: 'Declining' };
    return { Icon: IconArrowRight, color: 'teal', label: 'Stable' };
}

const isInflow = (type = '') => /deliver|receiv|return|add|restock|order|adjust\s*up/i.test(type);

// Compact colour-tinted metric tile.
function MetricCard({ label, value, sublabel, color, icon: Icon }) {
    return (
        <Box style={{
            background: `light-dark(var(--mantine-color-${color}-0), var(--mantine-color-dark-6))`,
            border: `1px solid light-dark(var(--mantine-color-${color}-2), var(--mantine-color-dark-4))`,
            borderRadius: 14, padding: '11px 13px',
        }}>
            <Group gap={8} wrap="nowrap" mb={6}>
                <ThemeIcon variant="light" color={color} size={30} radius="md"><Icon size={17} stroke={1.7} /></ThemeIcon>
                <Text size="xs" fw={600} c="dimmed" lineClamp={1}>{label}</Text>
            </Group>
            <Text fw={800} fz={23} lh={1}>{value}</Text>
            {sublabel && <Text size="xs" c="dimmed" mt={3} lineClamp={1}>{sublabel}</Text>}
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

export default function StockLab({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const isManager = role === 'manager';
    const [adjustOpened, adjust] = useDisclosure(false);
    const [drawerMed, setDrawerMed] = useState(null);

    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [stockFilter, setStockFilter] = useState('all');
    const [expiryFilter, setExpiryFilter] = useState('all');
    const [sort, setSort] = useState({ key: null, dir: 'asc' });

    const onSort = (key) => setSort((s) => (s.key === key ? { key, dir: s.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' }));

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
            <Table.Tr key={m.id} onClick={() => setDrawerMed(m)} style={{ cursor: 'pointer' }}>
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
                        <Group gap={6} align="baseline" wrap="nowrap">
                            <Text fw={700} fz="md">{m.stock_level ?? '—'}</Text>
                            <Text size="xs" c="dimmed">{m.unit ?? 'units'}</Text>
                            <Tooltip label={trend.label} withArrow><trend.Icon size={15} color={`var(--mantine-color-${trend.color}-6)`} style={{ marginLeft: 'auto' }} /></Tooltip>
                        </Group>
                        <Progress value={bar.pct} color={bar.color} size="sm" radius="xl" mt={5} />
                    </Box>
                </Table.Td>
                <Table.Td><Badge color={st.color} variant="light">{st.label}</Badge></Table.Td>
                <Table.Td><Text size="sm">{m.resident ?? '—'}</Text></Table.Td>
                <Table.Td><Text size="sm" c={m.expired ? 'red' : undefined}>{m.expiry_date ?? 'No expiry'}</Text></Table.Td>
                <Table.Td><Text size="xs" c="dimmed">{lastAgo ?? '—'}</Text></Table.Td>
                <Table.Td onClick={stop}>
                    <Group gap={2} wrap="nowrap" justify="flex-end" className={classes.rowActions}>
                        {isManager && <Tooltip label="Adjust stock" withArrow><ActionIcon variant="subtle" color="gray" onClick={adjust.open}><IconEdit size={17} /></ActionIcon></Tooltip>}
                        <Tooltip label="View history" withArrow><ActionIcon variant="subtle" color="gray" onClick={() => setDrawerMed(m)}><IconHistory size={17} /></ActionIcon></Tooltip>
                        <Tooltip label="Transfer (coming soon)" withArrow><ActionIcon variant="subtle" color="gray" disabled><IconArrowsExchange size={17} /></ActionIcon></Tooltip>
                        {isManager && <Tooltip label="Remove (coming soon)" withArrow><ActionIcon variant="subtle" color="gray" disabled><IconTrash size={17} /></ActionIcon></Tooltip>}
                    </Group>
                </Table.Td>
            </Table.Tr>
        );
    });

    return (
        <>
            <Head title="Medication Stock (Lab)" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="md">
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

                    {/* Section 1 — compact metric cards */}
                    <SimpleGrid cols={{ base: 2, sm: 3, lg: 6 }} mb="md" spacing="sm">
                        <MetricCard label="Total items" value={stats.total ?? 0} color="brandTeal" icon={IconBox} sublabel={`${stats.total ?? 0} medications`} />
                        <MetricCard label="Low stock" value={stats.low ?? 0} color="orange" icon={IconAlertTriangle} sublabel={lowNames.length ? lowNames.slice(0, 2).join(', ') : 'None'} />
                        <MetricCard label="Out of stock" value={stats.out_of_stock ?? 0} color="red" icon={IconCircleX} sublabel="Require ordering" />
                        <MetricCard label="Expiring soon" value={stats.expiring_soon ?? 0} color="grape" icon={IconClock} sublabel={nextExpiry ? `Next: ${nextExpiry.raw}` : 'Within 30 days'} />
                        <MetricCard label="Expired" value={stats.expired ?? 0} color="red" icon={IconCalendar} sublabel="Remove from stock" />
                        <MetricCard label="Controlled" value={stats.controlled ?? 0} color="brandPurple" icon={IconShieldLock} sublabel="Require witness" />
                    </SimpleGrid>

                    {/* Section 2 — needs-attention strip */}
                    {lowList.length > 0 && (
                        <Box style={{
                            background: 'light-dark(var(--mantine-color-orange-0), var(--mantine-color-dark-6))',
                            border: '1px solid light-dark(var(--mantine-color-orange-2), var(--mantine-color-dark-4))',
                            borderRadius: 16, padding: '14px 18px',
                        }} mb="md">
                            <Group gap="sm" wrap="nowrap" mb={10}>
                                <ThemeIcon variant="light" color="orange" size={32} radius="md"><IconAlertTriangle size={18} stroke={1.7} /></ThemeIcon>
                                <Text fw={700} size="sm">{lowList.length} medication{lowList.length > 1 ? 's' : ''} need attention</Text>
                            </Group>
                            <Stack gap={6}>
                                {lowList.slice(0, 5).map((m) => (
                                    <Group key={m.id} justify="space-between" wrap="nowrap"
                                        style={{ borderTop: '1px solid light-dark(var(--mantine-color-orange-1), var(--mantine-color-dark-5))', paddingTop: 6 }}>
                                        <Group gap="sm" wrap="nowrap">
                                            <Text size="sm" fw={600}>{m.medication_name}</Text>
                                            <Badge size="sm" variant="light" color={isOut(m) ? 'red' : 'orange'} radius="sm">{num(m.stock_level)} left</Badge>
                                        </Group>
                                        {isManager && <Button size="compact-sm" variant="subtle" color="orange" leftSection={<IconReload size={14} />} onClick={adjust.open}>Reorder</Button>}
                                    </Group>
                                ))}
                                {lowList.length > 5 && <Text size="xs" c="dimmed">+{lowList.length - 5} more</Text>}
                            </Stack>
                        </Box>
                    )}

                    {/* Section 3 — inventory (left) + activity feed (right) */}
                    <Grid gutter="md">
                        <Grid.Col span={{ base: 12, lg: 9 }}>
                            <Box style={{ ...surface, padding: '16px 18px' }}>
                                {/* toolbar */}
                                <Group gap="sm" mb="md" wrap="wrap">
                                    <TextInput flex={1} miw={200} radius="md" leftSection={<IconSearch size={16} />}
                                        placeholder="Search meds or resident…" value={search} onChange={(e) => setSearch(e.currentTarget.value)} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={statusFilter} onChange={(v) => setStatusFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'All statuses' }, { value: 'in_stock', label: 'In stock' }, { value: 'low', label: 'Low stock' }, { value: 'critical', label: 'Critical' }, { value: 'expiring', label: 'Expiring soon' }, { value: 'expired', label: 'Expired' }]} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={stockFilter} onChange={(v) => setStockFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'Any stock' }, { value: 'high', label: 'Healthy' }, { value: 'medium', label: 'Getting low' }, { value: 'low', label: 'Critical' }]} />
                                    <Select radius="md" w={140} checkIconPosition="right" value={expiryFilter} onChange={(v) => setExpiryFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'Any expiry' }, { value: 'expiring', label: 'Expiring ≤30d' }, { value: 'expired', label: 'Expired' }, { value: 'ok', label: 'In date' }]} />
                                    <Tooltip label="Export (coming soon)" withArrow><Button variant="default" radius="md" leftSection={<IconDownload size={16} />} disabled>Export</Button></Tooltip>
                                </Group>

                                <Box style={{ maxHeight: 600, overflow: 'auto', borderRadius: 12 }}>
                                    <Table className={classes.invTable} highlightOnHover stickyHeader verticalSpacing="sm" miw={920}>
                                        <Table.Thead style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-7))' }}>
                                            <Table.Tr>
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
                                                <Table.Tr><Table.Td colSpan={7}><Text c="dimmed" ta="center" py="lg">No medications match your filters.</Text></Table.Td></Table.Tr>
                                            )}
                                        </Table.Tbody>
                                    </Table>
                                </Box>
                            </Box>
                        </Grid.Col>

                        {/* Recent activity feed */}
                        <Grid.Col span={{ base: 12, lg: 3 }}>
                            <Box style={{ ...surface, padding: '16px 18px', height: '100%' }}>
                                <Group gap="sm" mb="md" wrap="nowrap">
                                    <ThemeIcon variant="light" color="brandTeal" size={32} radius="md"><IconActivity size={18} stroke={1.7} /></ThemeIcon>
                                    <Text fw={700}>Recent Activity</Text>
                                </Group>
                                {transactions.length ? (
                                    <Stack gap={2}>
                                        {transactions.slice(0, 14).map((t, i) => {
                                            const inflow = isInflow(t.type);
                                            return (
                                                <Box key={t.id ?? i} py={8} style={{ borderTop: i ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                    <Group justify="space-between" wrap="nowrap" mb={2}>
                                                        <Text size="xs" c="dimmed">{relTime(parseDate(t.date)) ?? t.date}</Text>
                                                        <Text size="sm" fw={700} c={inflow ? 'green' : 'red'}>{inflow ? '+' : '−'}{t.quantity}</Text>
                                                    </Group>
                                                    <Text size="sm" fw={600} lineClamp={1}>{t.medication_name}</Text>
                                                    <Text size="xs" c="dimmed" tt="capitalize">{t.type}</Text>
                                                </Box>
                                            );
                                        })}
                                    </Stack>
                                ) : <Text size="sm" c="dimmed">No activity yet.</Text>}
                            </Box>
                        </Grid.Col>
                    </Grid>
                  </Box>

                    <AdjustStockModal opened={adjustOpened} onClose={adjust.close} meds={meds} />

                    {/* Side drawer — med detail */}
                    <Drawer opened={!!drawerMed} onClose={() => setDrawerMed(null)} position="right" size={400}
                        title={<Text fw={800} fz="lg">{drawerMed?.medication_name}</Text>}>
                        {drawerMed && (
                            <Stack gap="lg">
                                <Group>
                                    <Badge color={statusOf(drawerMed).color} variant="light" size="lg">{statusOf(drawerMed).label}</Badge>
                                    {drawerForm && <Badge color="gray" variant="light" size="lg">{drawerForm.route} • {drawerForm.label}</Badge>}
                                    {drawerMed.is_controlled && <Badge color="grape" variant="light" size="lg">CD {drawerMed.cd_schedule}</Badge>}
                                </Group>

                                <SimpleGrid cols={2} spacing="md">
                                    <Box><Text size="xs" c="dimmed">Current stock</Text><Text fw={800} fz={28} lh={1.1}>{num(drawerMed.stock_level, drawerMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Reorder level</Text><Text fw={700} fz="lg">{num(drawerMed.reorder_level, drawerMed.unit)}</Text></Box>
                                    <Box><Text size="xs" c="dimmed">Resident</Text><Text fw={600} size="sm">{drawerMed.resident ?? '—'}</Text></Box>
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

StockLab.layout = (page) => <AppShell>{page}</AppShell>;
