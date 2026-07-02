import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Box, Group, Text, TextInput, Badge, Button, ThemeIcon, SimpleGrid, Select,
} from '@mantine/core';
import {
    IconSearch, IconPlus, IconShieldLock, IconPill, IconTruckDelivery, IconTrash,
    IconArrowBackUp, IconAdjustments, IconActivity,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';

const STORE = '/frontend2/controlled-drugs';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);

const ACTION = {
    administered: { label: 'Administered', color: 'red', Icon: IconPill, flow: -1 },
    received: { label: 'Received', color: 'teal', Icon: IconTruckDelivery, flow: 1 },
    disposed: { label: 'Disposed', color: 'orange', Icon: IconTrash, flow: -1 },
    returned: { label: 'Returned', color: 'indigo', Icon: IconArrowBackUp, flow: 1 },
    adjustment: { label: 'Adjustment', color: 'grape', Icon: IconAdjustments, flow: 0 },
};
const metaOf = (t) => ACTION[t] ?? { label: t || '—', color: 'gray', Icon: IconActivity, flow: 0 };

function Metric({ icon: Icon, label, value, color }) {
    return (
        <Box style={{ ...card, padding: 14 }}>
            <Group gap={10} wrap="nowrap">
                <ThemeIcon variant="light" color={color} size={38} radius="md"><Icon size={20} stroke={1.7} /></ThemeIcon>
                <Box><Text fz={24} fw={800} lh={1}>{value}</Text><Text fz="xs" c="dimmed">{label}</Text></Box>
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

    return (
        <AppShell title="Controlled drugs">
            <Head title="Controlled drugs" />
            <Box>
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Text fz={26} fw={800} lh={1.15}>Controlled drugs</Text>
                        <Text c="dimmed" fz="sm">Append-only register of controlled medication actions.</Text>
                    </Box>
                    <Button radius="xl" color="indigo" leftSection={<IconPlus size={16} />} onClick={add.open}>Add entry</Button>
                </Group>

                <SimpleGrid cols={{ base: 2, sm: 4 }} spacing="md" mb="lg">
                    <Metric icon={IconShieldLock} label="Entries" value={stats.total} color="grape" />
                    <Metric icon={IconPill} label="Administered" value={stats.administered} color="red" />
                    <Metric icon={IconTruckDelivery} label="Received" value={stats.received} color="teal" />
                    <Metric icon={IconTrash} label="Disposed / returned" value={stats.disposed} color="orange" />
                </SimpleGrid>

                <Box style={card}>
                    <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                        <Text fz={18} fw={800}>Register</Text>
                        <Group gap="sm" wrap="wrap">
                            <Select radius="xl" w={160} value={actionFilter} onChange={(v) => setActionFilter(v ?? 'all')}
                                data={[{ value: 'all', label: 'All actions' }, { value: 'administered', label: 'Administered' }, { value: 'received', label: 'Received' }, { value: 'disposed', label: 'Disposed' }, { value: 'returned', label: 'Returned' }, { value: 'adjustment', label: 'Adjustment' }]} />
                            <TextInput placeholder="Search med or resident…" leftSection={<IconSearch size={16} />} value={search}
                                onChange={(e) => setSearch(e.currentTarget.value)} radius="xl" w={{ base: '100%', sm: 240 }} />
                        </Group>
                    </Group>
                    {filtered.length === 0
                        ? <Text fz="sm" c="dimmed" ta="center" py={48}>No register entries yet.</Text>
                        : filtered.map((e, idx) => {
                            const am = metaOf(e.action_type);
                            const flowColor = am.flow < 0 ? 'red.6' : am.flow > 0 ? 'teal.6' : 'dimmed';
                            return (
                                <Group key={e.id ?? idx} gap="md" wrap="nowrap" align="center" px="md" py={12} style={{ borderTop: '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
                                    <ThemeIcon variant="light" color={am.color} size={38} radius="md"><am.Icon size={19} /></ThemeIcon>
                                    <Box style={{ flex: '2 1 220px', minWidth: 0 }}>
                                        <Group gap={6} wrap="nowrap">
                                            <Text fz="sm" fw={700} truncate>{e.medication_name}</Text>
                                            {e.cd_schedule && <Badge size="xs" color="grape" variant="light" radius="sm">{e.cd_schedule}</Badge>}
                                        </Group>
                                        <Text fz="xs" c="dimmed" truncate>{[e.client_name, e.entry_date, e.entry_time].filter(Boolean).join(' · ')}</Text>
                                    </Box>
                                    <Box style={{ flex: '1 1 110px', minWidth: 0 }} visibleFrom="md"><Badge color={am.color} variant="light" radius="sm">{am.label}</Badge></Box>
                                    <Box style={{ width: 76, flexShrink: 0, textAlign: 'right' }}>
                                        <Text fz="sm" fw={800} c={flowColor} lh={1}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                                        <Text fz={10} c="dimmed">{e.unit ?? 'dose'}</Text>
                                    </Box>
                                    <Box style={{ width: 70, flexShrink: 0, textAlign: 'right' }} visibleFrom="sm">
                                        <Text fz="sm" fw={700} lh={1}>{e.balance_after ?? '—'}</Text>
                                        <Text fz={10} c="dimmed">balance</Text>
                                    </Box>
                                    <Text fz="xs" c="dimmed" style={{ width: 90, flexShrink: 0, textAlign: 'right' }} visibleFrom="md" truncate>{e.witness_name ? `W: ${e.witness_name}` : 'No witness'}</Text>
                                </Group>
                            );
                        })}
                    <Box py={4} />
                </Box>
            </Box>

            <AddCdEntryModal opened={addOpened} onClose={add.close} residents={residents} medsByClient={medsByClient} lastBalances={lastBalances} action={STORE} />
        </AppShell>
    );
}
