import { Head, router } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Group, Button, TextInput, Paper, Card, Text, Stack, Badge, Divider,
} from '@mantine/core';
import { IconPlus, IconCheck, IconChevronLeft, IconChevronRight, IconArrowsLeftRight } from '@tabler/icons-react';
import PageHeader from '@frontend/components/PageHeader';
import StatusBadge from '@frontend/components/StatusBadge';
import AddHandoverModal from '@frontend/features/medications/AddHandoverModal';
import AppShell from '@frontend/Layouts/AppShell';

function Section({ title, children }) {
    return (
        <>
            <Text size="xs" fw={700} c="dimmed" tt="uppercase" mt="sm">{title}</Text>
            <Stack gap={4} mt={4}>{children}</Stack>
        </>
    );
}

function HandoverCard({ h, onAcknowledge }) {
    return (
        <Paper withBorder radius="lg" p="md">
            <Group justify="space-between" mb="xs" wrap="nowrap">
                <Group gap="sm" wrap="nowrap">
                    <Text fw={700}>{h.handover_time ?? '—'}</Text>
                    <Text size="sm" c="dimmed">{h.from_carer_name || '—'} → {h.to_carer_name || '—'}</Text>
                    {h.location && <Badge variant="light" color="gray">{h.location}</Badge>}
                </Group>
                <Group gap="sm" wrap="nowrap">
                    <StatusBadge status={h.status} label={h.status} />
                    {h.status === 'submitted' && (
                        <Button size="xs" variant="light" color="green" leftSection={<IconCheck size={14} />} onClick={() => onAcknowledge(h.id)}>Acknowledge</Button>
                    )}
                </Group>
            </Group>

            {h.general_notes && <Text size="sm" mb="sm">{h.general_notes}</Text>}

            {h.client_updates.length > 0 && (
                <Section title="Client updates">
                    {h.client_updates.map((u, i) => (
                        <Group key={i} gap={6} wrap="nowrap">
                            {u.priority && <StatusBadge status={u.priority} label={u.priority} size="xs" />}
                            <Text size="sm"><b>{u.client_name}:</b> {u.update}</Text>
                        </Group>
                    ))}
                </Section>
            )}

            {h.medication_concerns.length > 0 && (
                <Section title="Medication concerns">
                    {h.medication_concerns.map((c, i) => (
                        <Group key={i} gap={6} wrap="nowrap">
                            {c.action_required && <Badge size="xs" color="red">Action</Badge>}
                            <Text size="sm"><b>{c.client_name}:</b> {c.concern}</Text>
                        </Group>
                    ))}
                </Section>
            )}

            {h.priority_alerts.length > 0 && (
                <Section title="Priority alerts">
                    {h.priority_alerts.map((a, i) => (
                        <Text key={i} size="sm" c="red">⚠ {a.alert ?? (typeof a === 'string' ? a : '')}</Text>
                    ))}
                </Section>
            )}

            <Divider my="sm" />
            <Text size="xs" c="dimmed">
                Created by {h.created_by ?? '—'}
                {h.acknowledged_at ? ` · Acknowledged by ${h.acknowledged_by} at ${h.acknowledged_at}` : ''}
            </Text>
        </Paper>
    );
}

export default function ShiftHandover({ handovers = [], serviceUsers = [], selectedDate, prevDate, nextDate, todayDate }) {
    const [newOpened, newHandover] = useDisclosure(false);
    const reload = (date) => router.get('/medication/shift-handover-react', { date }, { preserveScroll: true, preserveState: true });
    const acknowledge = (id) => router.post(`/medication/shift-handover-react/${id}/acknowledge`, { date: selectedDate }, { preserveScroll: true });

    return (
        <>
            <Head title="Shift Handover" />
            <Container size="lg" py="lg">
                <PageHeader
                    title="Shift Handover"
                    subtitle="Notes passed between shifts"
                    icon={IconArrowsLeftRight}
                    color="teal"
                    actions={<Button leftSection={<IconPlus size={16} />} onClick={newHandover.open}>New handover</Button>}
                />

                <FlashAlerts />

                <Card withBorder radius="lg" padding="sm" mb="md">
                    <Group gap="xs">
                        <Button variant="default" px="sm" onClick={() => reload(prevDate)}><IconChevronLeft size={16} /></Button>
                        <TextInput type="date" value={selectedDate} onChange={(e) => reload(e.currentTarget.value)} />
                        <Button variant="default" px="sm" onClick={() => reload(nextDate)}><IconChevronRight size={16} /></Button>
                        <Button variant="light" onClick={() => reload(todayDate)}>Today</Button>
                    </Group>
                </Card>

                {handovers.length === 0
                    ? <Paper withBorder radius="md" p="xl"><Text c="dimmed" ta="center">No handovers for this day.</Text></Paper>
                    : <Stack>{handovers.map((h) => <HandoverCard key={h.id} h={h} onAcknowledge={acknowledge} />)}</Stack>}

                <AddHandoverModal
                    opened={newOpened}
                    onClose={newHandover.close}
                    serviceUsers={serviceUsers}
                    defaultDate={selectedDate}
                />
            </Container>
        </>
    );
}

ShiftHandover.layout = (page) => <AppShell>{page}</AppShell>;
