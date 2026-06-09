import { Head, usePage } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, SimpleGrid, Badge, Button, Tabs, Paper, Alert,
    ThemeIcon, Progress, Box, Menu, ActionIcon,
} from '@mantine/core';
import {
    IconBox, IconAlertTriangle, IconCircleX, IconClock, IconCalendar,
    IconPill, IconDots, IconFilter, IconPlus,
} from '@tabler/icons-react';
import StatCard from '@frontend/components/StatCard';
import PageHeader from '@frontend/components/PageHeader';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import AdjustStockModal from '@frontend/features/medications/AdjustStockModal';
import AppShell from '@frontend/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);

// A relative fill for the stock bar (no absolute max in the data, so scale off the reorder level).
function stockBar(m) {
    const stock = m.stock_level;
    if (stock === null || stock === undefined) return { pct: 0, color: 'gray' };
    const ref = m.reorder_level ? m.reorder_level * 3 : Math.max(stock, 1);
    const pct = Math.min(100, Math.max(4, Math.round((stock / ref) * 100)));
    const color = stock == 0 ? 'red' : m.expired ? 'red' : m.low ? 'orange' : 'teal';
    return { pct, color };
}

export default function Stock({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const flash = usePage().props.flash ?? {};
    const [adjustOpened, adjust] = useDisclosure(false);

    const medColumns = [
        {
            key: 'medication_name', label: 'Medication',
            render: (m) => (
                <Group gap="sm" wrap="nowrap">
                    <ThemeIcon variant="light" color="indigo" size={34} radius="xl"><IconPill size={18} /></ThemeIcon>
                    <div>
                        <Text fw={600} size="sm">{m.medication_name}</Text>
                        {m.is_controlled && <Badge size="xs" color="grape" variant="light">CD {m.cd_schedule}</Badge>}
                    </div>
                </Group>
            ),
        },
        { key: 'resident', label: 'Resident' },
        {
            key: 'stock_level', label: 'Stock level',
            render: (m) => {
                const bar = stockBar(m);
                return (
                    <Box w={130}>
                        <Text size="sm" fw={600}>{num(m.stock_level, m.unit)}</Text>
                        <Progress value={bar.pct} color={bar.color} size="sm" radius="xl" mt={4} />
                    </Box>
                );
            },
        },
        {
            key: 'status', label: 'Status', sortable: false,
            render: (m) => {
                if (m.expired) return <StatusBadge status="expired" label="Expired" />;
                if (m.stock_level == 0) return <StatusBadge status="out of stock" label="Out of stock" />;
                if (m.low) return <StatusBadge status="low" label="Low stock" />;
                return <StatusBadge status="ok" label="Good" color="green" />;
            },
        },
        { key: 'expiry_date', label: 'Expiry' },
        {
            key: 'actions', label: '', sortable: false,
            render: () => (
                <Menu position="bottom-end" withinPortal>
                    <Menu.Target>
                        <ActionIcon variant="subtle" color="gray"><IconDots size={18} /></ActionIcon>
                    </Menu.Target>
                    <Menu.Dropdown>
                        {role === 'manager' && <Menu.Item onClick={adjust.open}>Adjust stock</Menu.Item>}
                        <Menu.Item disabled>View history</Menu.Item>
                    </Menu.Dropdown>
                </Menu>
            ),
        },
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
            <Container size="xl" py="lg">
                <PageHeader
                    title="Medication Stock"
                    subtitle="View and manage all medication inventory across your locations."
                    actions={
                        <Group gap="sm">
                            <Button variant="default" leftSection={<IconFilter size={16} />}>Filter</Button>
                            {role === 'manager'
                                ? <Button leftSection={<IconPlus size={16} />} onClick={adjust.open}>Adjust stock</Button>
                                : <Badge variant="light" color="gray" size="lg">View only</Badge>}
                        </Group>
                    }
                />

                {flash.success && <Alert color="green" mb="md">{flash.success}</Alert>}
                {flash.error && <Alert color="red" mb="md">{flash.error}</Alert>}

                <SimpleGrid cols={{ base: 2, sm: 3, lg: 5 }} mb="xl">
                    <StatCard label="Total items" value={stats.total ?? 0} color="indigo" icon={IconBox} sublabel="In this home" />
                    <StatCard label="Low stock" value={stats.low ?? 0} color="orange" icon={IconAlertTriangle} sublabel="Need attention" />
                    <StatCard label="Out of stock" value={stats.out_of_stock ?? 0} color="red" icon={IconCircleX} sublabel="Require ordering" />
                    <StatCard label="Expiring soon" value={stats.expiring_soon ?? 0} color="grape" icon={IconClock} sublabel="Within 30 days" />
                    <StatCard label="Expired" value={stats.expired ?? 0} color="red" icon={IconCalendar} sublabel="Remove from stock" />
                </SimpleGrid>

                <Paper withBorder radius="lg" p="md">
                    <Tabs defaultValue="overview">
                        <Tabs.List mb="md">
                            <Tabs.Tab value="overview">Medication Inventory ({meds.length})</Tabs.Tab>
                            <Tabs.Tab value="transactions">Recent Transactions ({transactions.length})</Tabs.Tab>
                        </Tabs.List>

                        <Tabs.Panel value="overview">
                            <DataTable columns={medColumns} data={meds} searchable pageSize={10} emptyMessage="No medications found." minWidth={760} />
                        </Tabs.Panel>

                        <Tabs.Panel value="transactions">
                            <DataTable columns={txColumns} data={transactions} searchable pageSize={10} emptyMessage="No transactions yet." minWidth={720} />
                        </Tabs.Panel>
                    </Tabs>
                </Paper>

                <AdjustStockModal opened={adjustOpened} onClose={adjust.close} meds={meds} />
            </Container>
        </>
    );
}

Stock.layout = (page) => <AppShell>{page}</AppShell>;
