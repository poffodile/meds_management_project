import { Head, usePage } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Text, Group, SimpleGrid, Badge, Button, Tabs, Paper, Anchor, Alert,
} from '@mantine/core';
import StatCard from '@frontend/components/StatCard';
import PageHeader from '@frontend/components/PageHeader';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import AdjustStockModal from '@frontend/features/medications/AdjustStockModal';
import AppShell from '@frontend/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);

export default function Stock({ meds = [], transactions = [], stats = {} }) {
    const role = useRole();
    const flash = usePage().props.flash ?? {};
    const [adjustOpened, adjust] = useDisclosure(false);

    const medColumns = [
        {
            key: 'medication_name', label: 'Medication',
            render: (m) => (
                <Group gap={6} wrap="nowrap">
                    <Text fw={600} span>{m.medication_name}</Text>
                    {m.is_controlled && <Badge size="xs" color="grape" variant="light">CD {m.cd_schedule}</Badge>}
                </Group>
            ),
        },
        { key: 'resident', label: 'Resident' },
        { key: 'stock_level', label: 'Stock', render: (m) => num(m.stock_level, m.unit) },
        { key: 'reorder_level', label: 'Reorder at' },
        { key: 'expiry_date', label: 'Expiry' },
        {
            key: 'status', label: 'Status', sortable: false,
            render: (m) => (
                m.expired ? <StatusBadge status="expired" label="Expired" variant="filled" />
                    : m.low ? <StatusBadge status="low" label="Low stock" variant="filled" />
                        : <StatusBadge status="ok" label="OK" />
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
            <Container size="xl" py="xl">
                <PageHeader
                    title="Medication Stock"
                    subtitle="Inventory, reorder needs and disposals"
                    actions={
                        <>
                            {/* Managers can change stock; carers see it read-only (real check is server-side too). */}
                            {role === 'manager'
                                ? <Button onClick={adjust.open}>Adjust stock</Button>
                                : <Badge variant="light" color="gray" size="lg">View only</Badge>}
                            <Anchor href="/medication/stock" c="dimmed" size="sm">Legacy</Anchor>
                        </>
                    }
                />

                {flash.success && <Alert color="green" mb="md">{flash.success}</Alert>}
                {flash.error && <Alert color="red" mb="md">{flash.error}</Alert>}

                <SimpleGrid cols={{ base: 2, sm: 4 }} mb="xl">
                    <StatCard label="Medications" value={stats.total ?? 0} color="indigo" />
                    <StatCard label="Low stock" value={stats.low ?? 0} color="orange" />
                    <StatCard label="Expired" value={stats.expired ?? 0} color="red" />
                    <StatCard label="Controlled" value={stats.controlled ?? 0} color="grape" />
                </SimpleGrid>

                <Paper withBorder radius="md" p="md">
                    <Tabs defaultValue="overview">
                        <Tabs.List mb="md">
                            <Tabs.Tab value="overview">Overview ({meds.length})</Tabs.Tab>
                            <Tabs.Tab value="transactions">Recent Transactions ({transactions.length})</Tabs.Tab>
                        </Tabs.List>

                        <Tabs.Panel value="overview">
                            <DataTable
                                columns={medColumns}
                                data={meds}
                                searchable
                                pageSize={10}
                                emptyMessage="No medications found."
                                minWidth={720}
                            />
                        </Tabs.Panel>

                        <Tabs.Panel value="transactions">
                            <DataTable
                                columns={txColumns}
                                data={transactions}
                                searchable
                                pageSize={10}
                                emptyMessage="No transactions yet."
                                minWidth={720}
                            />
                        </Tabs.Panel>
                    </Tabs>
                </Paper>

                <AdjustStockModal opened={adjustOpened} onClose={adjust.close} meds={meds} />
            </Container>
        </>
    );
}

// Wrap this page in the app shell (sidebar + top bar, role-aware).
Stock.layout = (page) => <AppShell>{page}</AppShell>;
