import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Group, Button, TextInput, Tabs, Paper, Alert, Text, Badge, Stack, Table,
} from '@mantine/core';
import PageHeader from '@frontend/components/PageHeader';
import StatusBadge from '@frontend/components/StatusBadge';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';
import AppShell from '@frontend/Layouts/AppShell';

const CODE_LABELS = { A: 'Given', S: 'Sleeping', R: 'Refused', W: 'Withheld', N: 'Not available', O: 'Omitted' };

export default function MedicationRound({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
    const flash = usePage().props.flash ?? {};
    const [recordRow, setRecordRow] = useState(null);
    const [recordOpened, record] = useDisclosure(false);

    const reload = (params) => router.get('/medication/medication-round-react', params, { preserveScroll: true, preserveState: true });
    const openRecord = (row) => { setRecordRow(row); record.open(); };

    return (
        <>
            <Head title="Medication Round" />
            <Container size="xl" py="xl">
                <PageHeader
                    title="Medication Round"
                    subtitle="Give medications by time-of-day round"
                    actions={
                        <Group gap="xs">
                            <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })} />
                            <Button variant="light" onClick={() => reload({})}>Today</Button>
                        </Group>
                    }
                />

                {flash.success && <Alert color="green" mb="md">{flash.success}</Alert>}
                {flash.error && <Alert color="red" mb="md">{flash.error}</Alert>}

                <Tabs defaultValue={currentRound}>
                    <Tabs.List mb="md">
                        {rounds.map((r) => (
                            <Tabs.Tab key={r.key} value={r.key}>{r.label} ({(grid[r.key] ?? []).length})</Tabs.Tab>
                        ))}
                    </Tabs.List>

                    {rounds.map((r) => {
                        const residents = grid[r.key] ?? [];
                        return (
                            <Tabs.Panel key={r.key} value={r.key}>
                                <Text size="xs" c="dimmed" mb="sm">{r.window}</Text>
                                {residents.length === 0
                                    ? <Text c="dimmed" ta="center" py="xl">No medications in this round.</Text>
                                    : (
                                        <Stack>
                                            {residents.map((resident) => (
                                                <Paper key={resident.client_id} withBorder radius="md" p="md">
                                                    <Text fw={700} mb="xs">{resident.name}</Text>
                                                    <Table verticalSpacing="xs">
                                                        <Table.Tbody>
                                                            {resident.rows.map((row, idx) => (
                                                                <Table.Tr key={idx}>
                                                                    <Table.Td>
                                                                        <Text fw={500} span>{row.medication_name}</Text>
                                                                        {row.dose && <Text size="xs" c="dimmed">{row.dose}</Text>}
                                                                    </Table.Td>
                                                                    <Table.Td w={70}>{row.slot ?? 'PRN'}</Table.Td>
                                                                    <Table.Td w={130}>
                                                                        {row.code
                                                                            ? <StatusBadge status={CODE_LABELS[row.code]} label={CODE_LABELS[row.code]} />
                                                                            : <Badge variant="light" color="blue">Due</Badge>}
                                                                    </Table.Td>
                                                                    <Table.Td w={100} ta="right">
                                                                        {row.slot && (
                                                                            <Button size="xs" variant="light" onClick={() => openRecord(row)}>
                                                                                {row.code ? 'Edit' : 'Record'}
                                                                            </Button>
                                                                        )}
                                                                    </Table.Td>
                                                                </Table.Tr>
                                                            ))}
                                                        </Table.Tbody>
                                                    </Table>
                                                </Paper>
                                            ))}
                                        </Stack>
                                    )}
                            </Tabs.Panel>
                        );
                    })}
                </Tabs>

                <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} />
            </Container>
        </>
    );
}

MedicationRound.layout = (page) => <AppShell>{page}</AppShell>;
