import { Head } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import { Container, Text, Group, Badge, Button, Paper, Alert, ThemeIcon } from '@mantine/core';
import { IconPill, IconPlus } from '@tabler/icons-react';
import PageHeader from '@frontend/components/PageHeader';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';
import AppShell from '@frontend/Layouts/AppShell';

const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);

export default function ControlledDrugs({ entries = [], residents = [], medsByClient = {}, lastBalances = {} }) {
    const [addOpened, add] = useDisclosure(false);

    const columns = [
        { key: 'entry_date', label: 'Date' },
        { key: 'entry_time', label: 'Time' },
        { key: 'client_name', label: 'Resident' },
        {
            key: 'medication_name', label: 'Medication',
            render: (e) => (
                <Group gap="sm" wrap="nowrap">
                    <ThemeIcon variant="light" color="grape" size={34} radius="xl"><IconPill size={18} /></ThemeIcon>
                    <div>
                        <Text fw={600} size="sm">{e.medication_name}</Text>
                        {e.cd_schedule && <Badge size="xs" color="grape" variant="light">{e.cd_schedule}</Badge>}
                    </div>
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
            <Container size="xl" py="lg">
                <PageHeader
                    title="Controlled Drugs Register"
                    subtitle="Append-only record of controlled medication actions"
                    actions={<Button leftSection={<IconPlus size={16} />} onClick={add.open}>Add entry</Button>}
                />

                <FlashAlerts />

                <Paper withBorder radius="lg" p="md">
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
