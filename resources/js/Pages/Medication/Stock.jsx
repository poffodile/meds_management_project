import { Head } from '@inertiajs/react';
import {
    Container, Title, Text, Group, SimpleGrid, Badge,
    Table, Tabs, Paper, Box, Anchor,
} from '@mantine/core';
import StatCard from '@frontend/components/StatCard';

const typeColor = {
    received: 'blue', administered: 'green', disposed: 'orange',
    returned: 'gray', correction: 'yellow',
};

export default function Stock({ meds = [], transactions = [], stats = {} }) {
    const medRows = meds.map((m) => (
        <Table.Tr key={m.id}>
            <Table.Td>
                <Group gap={6} wrap="nowrap">
                    <Text fw={600} span>{m.medication_name}</Text>
                    {m.is_controlled && (
                        <Badge size="xs" color="grape" variant="light">CD {m.cd_schedule}</Badge>
                    )}
                </Group>
            </Table.Td>
            <Table.Td>{m.resident ?? '—'}</Table.Td>
            <Table.Td>{m.stock_level ?? '—'} {m.unit}</Table.Td>
            <Table.Td>{m.reorder_level ?? '—'}</Table.Td>
            <Table.Td>{m.expiry_date ?? '—'}</Table.Td>
            <Table.Td>
                {m.expired
                    ? <Badge color="red">Expired</Badge>
                    : m.low
                        ? <Badge color="orange">Low stock</Badge>
                        : <Badge color="green" variant="light">OK</Badge>}
            </Table.Td>
        </Table.Tr>
    ));

    const txRows = transactions.map((t) => (
        <Table.Tr key={t.id}>
            <Table.Td>{t.date}</Table.Td>
            <Table.Td><Badge variant="light" color={typeColor[t.type] ?? 'gray'}>{t.type}</Badge></Table.Td>
            <Table.Td>{t.medication_name}</Table.Td>
            <Table.Td>{t.quantity ?? '—'} {t.unit}</Table.Td>
            <Table.Td>{t.balance_after ?? '—'}</Table.Td>
            <Table.Td>{t.performed_by ?? '—'}</Table.Td>
        </Table.Tr>
    ));

    const emptyRow = (cols, label) => (
        <Table.Tr><Table.Td colSpan={cols}>
            <Text c="dimmed" ta="center" py="lg">{label}</Text>
        </Table.Td></Table.Tr>
    );

    return (
        <>
            <Head title="Medication Stock" />
            <Container size="xl" py="xl">
                <Group justify="space-between" align="flex-end" mb="lg">
                    <Box>
                        <Title order={2}>Medication Stock</Title>
                        <Text c="dimmed" size="sm">Inventory, reorder needs and disposals · Mantine + React pilot</Text>
                    </Box>
                    <Anchor href="/medication/stock" c="dimmed" size="sm">← View legacy version</Anchor>
                </Group>

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
                            <Table.ScrollContainer minWidth={720}>
                                <Table striped highlightOnHover verticalSpacing="sm">
                                    <Table.Thead>
                                        <Table.Tr>
                                            <Table.Th>Medication</Table.Th>
                                            <Table.Th>Resident</Table.Th>
                                            <Table.Th>Stock</Table.Th>
                                            <Table.Th>Reorder at</Table.Th>
                                            <Table.Th>Expiry</Table.Th>
                                            <Table.Th>Status</Table.Th>
                                        </Table.Tr>
                                    </Table.Thead>
                                    <Table.Tbody>{medRows.length ? medRows : emptyRow(6, 'No medications found.')}</Table.Tbody>
                                </Table>
                            </Table.ScrollContainer>
                        </Tabs.Panel>

                        <Tabs.Panel value="transactions">
                            <Table.ScrollContainer minWidth={720}>
                                <Table striped highlightOnHover verticalSpacing="sm">
                                    <Table.Thead>
                                        <Table.Tr>
                                            <Table.Th>Date</Table.Th>
                                            <Table.Th>Type</Table.Th>
                                            <Table.Th>Medication</Table.Th>
                                            <Table.Th>Qty</Table.Th>
                                            <Table.Th>Balance</Table.Th>
                                            <Table.Th>By</Table.Th>
                                        </Table.Tr>
                                    </Table.Thead>
                                    <Table.Tbody>{txRows.length ? txRows : emptyRow(6, 'No transactions yet.')}</Table.Tbody>
                                </Table>
                            </Table.ScrollContainer>
                        </Tabs.Panel>
                    </Tabs>
                </Paper>
            </Container>
        </>
    );
}
