import { useState } from 'react';
import { Head, usePage, router } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Group, Button, TextInput, SegmentedControl, SimpleGrid, Paper, Alert, Text, Badge,
} from '@mantine/core';
import PageHeader from '@frontend/components/PageHeader';
import StatCard from '@frontend/components/StatCard';
import DataTable from '@frontend/components/DataTable';
import StatusBadge from '@frontend/components/StatusBadge';
import ResolveDoseModal from '@frontend/features/medications/ResolveDoseModal';
import AppShell from '@frontend/Layouts/AppShell';

export default function MissedDoses({
    items = [], stats = {}, date, prevDate, nextDate, todayDate, statusFilter = 'outstanding',
}) {
    const flash = usePage().props.flash ?? {};
    const [resolveItem, setResolveItem] = useState(null);
    const [resolveOpened, resolve] = useDisclosure(false);

    const reload = (params) => router.get(
        '/medication/missed-doses-react',
        { date, status: statusFilter, ...params },
        { preserveScroll: true, preserveState: true },
    );

    const openResolve = (item) => { setResolveItem(item); resolve.open(); };

    const columns = [
        { key: 'resident_name', label: 'Resident' },
        { key: 'medication_name', label: 'Medication' },
        { key: 'slot', label: 'Time' },
        {
            key: 'kind', label: 'Issue',
            render: (i) => <StatusBadge status={i.kind} label={i.kind === 'missed' ? 'Missed' : 'Not given'} />,
        },
        { key: 'code', label: 'Code', render: (i) => i.code ?? '—' },
        {
            key: 'resolved', label: 'Status', sortable: false,
            render: (i) => (i.resolved
                ? <Group gap={6} wrap="nowrap"><StatusBadge status="resolved" label="Resolved" /><Text size="xs" c="dimmed">{i.clinical_action}</Text></Group>
                : <Badge color="gray" variant="light">Outstanding</Badge>),
        },
        {
            key: 'actions', label: '', sortable: false,
            render: (i) => (!i.resolved
                ? <Button size="xs" variant="light" onClick={() => openResolve(i)}>Resolve</Button>
                : null),
        },
    ];

    return (
        <>
            <Head title="Missed Doses" />
            <Container size="xl" py="xl">
                <PageHeader title="Missed Doses Review" subtitle="Missed and not-given doses, with clinical follow-up" />

                {flash.success && <Alert color="green" mb="md">{flash.success}</Alert>}
                {flash.error && <Alert color="red" mb="md">{flash.error}</Alert>}

                <Group justify="space-between" mb="md" wrap="wrap">
                    <Group gap="xs">
                        <Button variant="default" onClick={() => reload({ date: prevDate })}>◀</Button>
                        <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })} />
                        <Button variant="default" onClick={() => reload({ date: nextDate })}>▶</Button>
                        <Button variant="light" onClick={() => reload({ date: todayDate })}>Today</Button>
                    </Group>
                    <SegmentedControl
                        value={statusFilter}
                        onChange={(v) => reload({ status: v })}
                        data={[
                            { label: 'Outstanding', value: 'outstanding' },
                            { label: 'Resolved', value: 'resolved' },
                            { label: 'All', value: 'all' },
                        ]}
                    />
                </Group>

                <SimpleGrid cols={{ base: 2, sm: 4 }} mb="xl">
                    <StatCard label="Missed" value={stats.missed ?? 0} color="red" />
                    <StatCard label="Not given" value={stats.not_given ?? 0} color="orange" />
                    <StatCard label="Outstanding" value={stats.outstanding ?? 0} color="grape" />
                    <StatCard label="Resolved" value={stats.resolved ?? 0} color="green" />
                </SimpleGrid>

                <Paper withBorder radius="md" p="md">
                    <DataTable
                        columns={columns}
                        data={items}
                        searchable
                        pageSize={15}
                        emptyMessage="No dose issues for this day."
                        minWidth={900}
                    />
                </Paper>

                <ResolveDoseModal opened={resolveOpened} onClose={resolve.close} item={resolveItem} date={date} />
            </Container>
        </>
    );
}

MissedDoses.layout = (page) => <AppShell>{page}</AppShell>;
