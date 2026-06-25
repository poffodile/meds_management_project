import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, Badge, Button, Box, ThemeIcon, SimpleGrid, Grid,
    Table, TextInput, Select, Tooltip, Stack,
} from '@mantine/core';
import {
    IconPill, IconPlus, IconShieldLock, IconTruckDelivery, IconTrash, IconArrowBackUp,
    IconAdjustments, IconActivity, IconSearch, IconDownload,
} from '@tabler/icons-react';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';
import AppShell from '@frontend/Layouts/AppShell';
import { downloadCsv } from '@frontend/lib/csv';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const parseDate = (s) => { const t = Date.parse(s); return Number.isNaN(t) ? null : t; };

const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

// CD action → icon, colour and stock direction (+ in / − out).
function actionMeta(type = '') {
    const t = String(type).toLowerCase();
    if (t === 'administered') return { label: 'Administered', color: 'red', Icon: IconPill, flow: -1 };
    if (t === 'received') return { label: 'Received', color: 'green', Icon: IconTruckDelivery, flow: 1 };
    if (t === 'disposed') return { label: 'Disposed', color: 'orange', Icon: IconTrash, flow: -1 };
    if (t === 'returned') return { label: 'Returned', color: 'indigo', Icon: IconArrowBackUp, flow: 1 };
    if (t === 'adjustment') return { label: 'Adjustment', color: 'grape', Icon: IconAdjustments, flow: 0 };
    return { label: type || '—', color: 'gray', Icon: IconActivity, flow: 0 };
}

// Compact horizontal metric tile.
function MetricCard({ label, value, color, icon: Icon }) {
    return (
        <Box style={{ ...surface, borderRadius: 16, padding: '8px 13px' }}>
            <Group justify="space-between" wrap="nowrap">
                <Group gap={8} wrap="nowrap" style={{ minWidth: 0 }}>
                    <ThemeIcon variant="light" color={color} size={26} radius="md"><Icon size={15} stroke={1.7} /></ThemeIcon>
                    <Text size="xs" fw={600} c="dimmed" lineClamp={1}>{label}</Text>
                </Group>
                <Text fw={800} fz={20} lh={1}>{value}</Text>
            </Group>
        </Box>
    );
}

export default function ControlledDrugs({ entries = [], residents = [], medsByClient = {}, lastBalances = {} }) {
    const [addOpened, add] = useDisclosure(false);
    const [search, setSearch] = useState('');
    const [actionFilter, setActionFilter] = useState('all');

    const stats = useMemo(() => ({
        total: entries.length,
        administered: entries.filter((e) => e.action_type === 'administered').length,
        received: entries.filter((e) => e.action_type === 'received').length,
        disposed: entries.filter((e) => e.action_type === 'disposed' || e.action_type === 'returned').length,
    }), [entries]);

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return entries.filter((e) => {
            if (q && !`${e.client_name} ${e.medication_name}`.toLowerCase().includes(q)) return false;
            if (actionFilter !== 'all' && e.action_type !== actionFilter) return false;
            return true;
        });
    }, [entries, search, actionFilter]);

    const exportCsv = () => downloadCsv('controlled-drugs-register.csv', [
        { header: 'Date', value: (e) => e.entry_date },
        { header: 'Time', value: (e) => e.entry_time },
        { header: 'Resident', value: (e) => e.client_name },
        { header: 'Medication', value: (e) => e.medication_name },
        { header: 'Schedule', value: (e) => e.cd_schedule },
        { header: 'Action', value: (e) => actionMeta(e.action_type).label },
        { header: 'Dose', value: (e) => e.dose_quantity },
        { header: 'Unit', value: (e) => e.unit },
        { header: 'Balance', value: (e) => e.balance_after },
        { header: 'Witness', value: (e) => e.witness_name },
        { header: 'By', value: (e) => e.created_by },
    ], filtered);

    const timeline = useMemo(() => {
        const withT = entries.map((e) => ({ e, t: parseDate(`${e.entry_date} ${e.entry_time}`) }));
        const sortable = withT.every((x) => x.t !== null);
        const ordered = sortable ? [...withT].sort((a, b) => b.t - a.t).map((x) => x.e) : entries;
        return ordered.slice(0, 14);
    }, [entries]);

    const rows = filtered.map((e) => {
        const am = actionMeta(e.action_type);
        return (
            <Table.Tr key={e.id ?? `${e.entry_date}-${e.entry_time}-${e.medication_name}`}>
                <Table.Td><Text size="sm" fw={600}>{e.entry_time}</Text><Text fz={11} c="dimmed">{e.entry_date}</Text></Table.Td>
                <Table.Td><Text size="sm" fw={500}>{e.client_name}</Text></Table.Td>
                <Table.Td>
                    <Group gap="sm" wrap="nowrap">
                        <ThemeIcon variant="light" color="grape" size={32} radius="xl"><IconPill size={16} /></ThemeIcon>
                        <div>
                            <Text fw={600} size="sm">{e.medication_name}</Text>
                            {e.cd_schedule && <Badge size="xs" color="grape" variant="light">{e.cd_schedule}</Badge>}
                        </div>
                    </Group>
                </Table.Td>
                <Table.Td>
                    <Group gap={6} wrap="nowrap">
                        <ThemeIcon variant="light" color={am.color} size={24} radius="xl"><am.Icon size={13} /></ThemeIcon>
                        <Badge color={am.color} variant="light" tt="none">{am.label}</Badge>
                    </Group>
                </Table.Td>
                <Table.Td><Text size="sm" fw={600} c={am.flow < 0 ? 'red.7' : am.flow > 0 ? 'green.7' : undefined}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity, e.unit)}</Text></Table.Td>
                <Table.Td><Text size="sm">{e.balance_after ?? '—'}</Text></Table.Td>
                <Table.Td><Text size="sm">{e.witness_name ?? '—'}</Text></Table.Td>
                <Table.Td><Text size="sm">{e.created_by ?? '—'}</Text></Table.Td>
            </Table.Tr>
        );
    });

    return (
        <>
            <Head title="Controlled Drugs Register" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="lg">
                        <Group justify="space-between" align="center" wrap="wrap" gap="md">
                            <Group gap="sm" wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="brandPurple" size={40} radius="md"><IconShieldLock size={22} stroke={1.7} /></ThemeIcon>
                                <Box>
                                    <Text fz={{ base: 20, sm: 24 }} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>Controlled Drugs Register</Text>
                                    <Text c="dimmed" fz="sm">Append-only record of controlled medication actions.</Text>
                                </Box>
                            </Group>
                            <Button radius="md" leftSection={<IconPlus size={16} />} onClick={add.open}>Add entry</Button>
                        </Group>
                    </Box>

                    <FlashAlerts />

                    {/* Compact metric cards */}
                    <SimpleGrid cols={{ base: 2, sm: 4 }} mb="lg" spacing="sm" maw={620}>
                        <MetricCard label="Entries" value={stats.total} color="brandPurple" icon={IconShieldLock} />
                        <MetricCard label="Administered" value={stats.administered} color="red" icon={IconPill} />
                        <MetricCard label="Received" value={stats.received} color="green" icon={IconTruckDelivery} />
                        <MetricCard label="Disposed" value={stats.disposed} color="orange" icon={IconTrash} />
                    </SimpleGrid>

                    {/* Workspace — register (left) + recent activity (right) */}
                    <Grid gutter={{ base: 'md', lg: 'xl' }} columns={10}>
                        <Grid.Col span={{ base: 10, md: 7 }}>
                            <Box style={{ ...surface, padding: '16px 18px' }}>
                                <Group gap="md" mb="md" wrap="wrap">
                                    <TextInput flex={1} miw={200} radius="md" leftSection={<IconSearch size={16} />}
                                        placeholder="Search resident or medication…" value={search} onChange={(ev) => setSearch(ev.currentTarget.value)} />
                                    <Select radius="md" w={160} checkIconPosition="right" value={actionFilter} onChange={(v) => setActionFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'All actions' }, { value: 'administered', label: 'Administered' }, { value: 'received', label: 'Received' }, { value: 'disposed', label: 'Disposed' }, { value: 'returned', label: 'Returned' }, { value: 'adjustment', label: 'Adjustment' }]} />
                                    <Tooltip label="Download CSV of the listed entries" withArrow><Button variant="default" radius="md" ml="auto" leftSection={<IconDownload size={16} />} onClick={exportCsv} disabled={!filtered.length}>Export</Button></Tooltip>
                                </Group>

                                <Box style={{ maxHeight: 600, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                    <Table highlightOnHover stickyHeader verticalSpacing="md" miw={860}
                                        style={{ '--table-hover-color': 'light-dark(#f7f3fb, var(--mantine-color-dark-5))' }}>
                                        <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                            <Table.Tr>
                                                <Table.Th>When</Table.Th><Table.Th>Resident</Table.Th><Table.Th>Medication</Table.Th>
                                                <Table.Th>Action</Table.Th><Table.Th>Dose</Table.Th><Table.Th>Balance</Table.Th><Table.Th>Witness</Table.Th><Table.Th>By</Table.Th>
                                            </Table.Tr>
                                        </Table.Thead>
                                        <Table.Tbody>
                                            {rows.length ? rows : (
                                                <Table.Tr><Table.Td colSpan={8}><Text c="dimmed" ta="center" py="lg">No register entries yet.</Text></Table.Td></Table.Tr>
                                            )}
                                        </Table.Tbody>
                                    </Table>
                                </Box>
                            </Box>
                        </Grid.Col>

                        {/* Recent activity timeline */}
                        <Grid.Col span={{ base: 10, md: 3 }}>
                            <Box style={{ ...surface, padding: '16px 16px' }}>
                                <Group gap={8} wrap="nowrap" mb="md">
                                    <ThemeIcon variant="light" color="brandPurple" size={28} radius="md"><IconActivity size={16} stroke={1.7} /></ThemeIcon>
                                    <Text fw={700}>Recent Activity</Text>
                                </Group>
                                {timeline.length ? (
                                    <Stack gap={0}>
                                        {timeline.map((e, idx) => {
                                            const am = actionMeta(e.action_type);
                                            return (
                                                <Group key={e.id ?? idx} gap="xs" wrap="nowrap" align="flex-start" py={9}
                                                    style={{ borderTop: idx ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                    <ThemeIcon variant="light" color={am.color} size={24} radius="xl"><am.Icon size={13} /></ThemeIcon>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Text size="xs" c="dimmed" fw={600} lh={1.2}>{am.label}</Text>
                                                        <Group justify="space-between" wrap="nowrap" gap={6}>
                                                            <Text size="sm" fw={600} lineClamp={1} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{e.medication_name}</Text>
                                                            <Text fz={12} fw={600} c={am.flow < 0 ? 'red.6' : am.flow > 0 ? 'green.6' : 'dimmed'} style={{ whiteSpace: 'nowrap' }}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                                                        </Group>
                                                        <Text fz={11} c="dimmed" lineClamp={1}>{e.client_name} · {e.entry_time}</Text>
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

                    <AddCdEntryModal
                        opened={addOpened}
                        onClose={add.close}
                        residents={residents}
                        medsByClient={medsByClient}
                        lastBalances={lastBalances}
                    />
                </Container>
            </Box>
        </>
    );
}

ControlledDrugs.layout = (page) => <AppShell>{page}</AppShell>;
