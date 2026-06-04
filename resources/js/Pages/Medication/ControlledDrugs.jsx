import { Head, usePage } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import { Container, Text, Group, Badge, Button, Paper, Alert } from '@mantine/core';
import PageHeader from '@frontend/components/PageHeader';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';
import AppShell from '@frontend/Layouts/AppShell';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);

export default function ControlledDrugs({ entries = [], residents = [], medsByClient = {}, lastBalances = {} }) {
    const flash = usePage().props.flash ?? {};
    const [addOpened, add] = useDisclosure(false);

    const columns = [
        { key: 'entry_date', label: 'Date' },
        { key: 'entry_time', label: 'Time' },
        { key: 'client_name', label: 'Resident' },
        {
            key: 'medication_name', label: 'Medication',
            render: (e) => (
                <Group gap={6} wrap="nowrap">
                    <Text fw={600} span>{e.medication_name}</Text>
                    {e.cd_schedule && <Badge size="xs" color="grape" variant="light">{e.cd_schedule}</Badge>}
                </Group>
            ),
        },
        { key: 'action_type', label: 'Action', render: (e) => <StatusBadge status={e.action_type} /> },
        { key: 'dose_quantity', label: 'Dose', render: (e) => num(e.dose_quantity, e.unit) },
        { key: 'balance_after', label: 'Balance' },
        { key: 'witness_name', label: 'Witness' },
        { key: 'created_by', label: 'By' },
    ];

    return (
        <>
            <Head title="Controlled Drugs Register" />
            <Container size="xl" py="xl">
                <PageHeader
                    title="Controlled Drugs Register"
                    subtitle="Append-only record of controlled medication actions"
                    actions={<Button onClick={add.open}>Add entry</Button>}
                />

                {flash.success && <Alert color="green" mb="md">{flash.success}</Alert>}
                {flash.error && <Alert color="red" mb="md">{flash.error}</Alert>}

                <Paper withBorder radius="md" p="md">
                    <DataTable
                        columns={columns}
                        data={entries}
                        searchable
                        pageSize={15}
                        emptyMessage="No register entries yet."
                        minWidth={900}
                    />
                </Paper>

                <AddCdEntryModal
                    opened={addOpened}
                    onClose={add.close}
                    residents={residents}
                    medsByClient={medsByClient}
                    lastBalances={lastBalances}
                />
            </Container>
        </>
    );
}

ControlledDrugs.layout = (page) => <AppShell>{page}</AppShell>;
