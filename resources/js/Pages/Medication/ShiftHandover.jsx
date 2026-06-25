import { useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Group, Button, TextInput, Box, ThemeIcon, Text, Stack, Badge, Divider, SimpleGrid, Grid,
} from '@mantine/core';
import {
    IconPlus, IconCheck, IconChevronLeft, IconChevronRight, IconArrowsLeftRight,
    IconClockHour4, IconUserCheck, IconNote, IconAlertTriangle, IconActivity,
} from '@tabler/icons-react';
import AddHandoverModal from '@frontend/features/medications/AddHandoverModal';
import AppShell from '@frontend/Layouts/AppShell';

const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

function statusMeta(status = '') {
    const s = String(status).toLowerCase();
    if (s === 'acknowledged') return { label: 'Acknowledged', color: 'green', Icon: IconUserCheck };
    if (s === 'submitted') return { label: 'Submitted', color: 'orange', Icon: IconClockHour4 };
    return { label: status || 'Draft', color: 'gray', Icon: IconNote };
}

function MetricCard({ label, value, color, icon: Icon, alert }) {
    const hot = alert && Number(value) > 0;
    return (
        <Box style={{
            ...surface,
            ...(hot ? { borderLeft: `3px solid var(--mantine-color-${color}-6)` } : {}),
            borderRadius: 16, padding: '8px 13px',
        }}>
            <Group justify="space-between" wrap="nowrap">
                <Group gap={8} wrap="nowrap" style={{ minWidth: 0 }}>
                    <ThemeIcon variant="light" color={color} size={26} radius="md"><Icon size={15} stroke={1.7} /></ThemeIcon>
                    <Text size="xs" fw={600} c="dimmed" lineClamp={1}>{label}</Text>
                </Group>
                <Text fw={800} fz={20} lh={1} c={hot ? `${color}.7` : undefined}>{value}</Text>
            </Group>
        </Box>
    );
}

function Section({ title, children }) {
    return (
        <>
            <Text size="xs" fw={700} c="dimmed" tt="uppercase" mt="sm">{title}</Text>
            <Stack gap={4} mt={4}>{children}</Stack>
        </>
    );
}

function HandoverCard({ h, onAcknowledge }) {
    const sm = statusMeta(h.status);
    return (
        <Box style={{ ...surface, padding: '16px 18px', borderLeft: `3px solid var(--mantine-color-${sm.color}-6)` }}>
            <Group justify="space-between" mb="xs" wrap="nowrap">
                <Group gap="sm" wrap="nowrap">
                    <ThemeIcon variant="light" color={sm.color} size={32} radius="md"><sm.Icon size={17} /></ThemeIcon>
                    <Box>
                        <Text fw={700} lh={1.2}>{h.handover_time ?? '—'}</Text>
                        <Text size="sm" c="dimmed">{h.from_carer_name || '—'} → {h.to_carer_name || '—'}</Text>
                    </Box>
                    {h.location && <Badge variant="light" color="gray">{h.location}</Badge>}
                </Group>
                <Group gap="sm" wrap="nowrap">
                    <Badge color={sm.color} variant="light" tt="none">{sm.label}</Badge>
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
                            {u.priority && <Badge size="xs" variant="light" color="grape" tt="none">{u.priority}</Badge>}
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
        </Box>
    );
}

export default function ShiftHandover({ handovers = [], serviceUsers = [], selectedDate, prevDate, nextDate, todayDate }) {
    const [newOpened, newHandover] = useDisclosure(false);
    const reload = (date) => router.get('/medication/shift-handover-react', { date }, { preserveScroll: true, preserveState: true });
    const acknowledge = (id) => router.post(`/medication/shift-handover-react/${id}/acknowledge`, { date: selectedDate }, { preserveScroll: true });

    const priorityAlerts = useMemo(
        () => handovers.flatMap((h) => (h.priority_alerts || []).map((a) => ({
            text: a.alert ?? (typeof a === 'string' ? a : ''), time: h.handover_time, from: h.from_carer_name,
        }))).filter((a) => a.text),
        [handovers],
    );
    const actionConcerns = useMemo(
        () => handovers.flatMap((h) => (h.medication_concerns || []).filter((c) => c.action_required).map((c) => ({
            client: c.client_name, concern: c.concern, time: h.handover_time,
        }))),
        [handovers],
    );

    const stats = useMemo(() => ({
        total: handovers.length,
        pending: handovers.filter((h) => h.status === 'submitted').length,
        acknowledged: handovers.filter((h) => h.status === 'acknowledged').length,
        alerts: priorityAlerts.length,
    }), [handovers, priorityAlerts]);

    const hasAttention = priorityAlerts.length > 0 || actionConcerns.length > 0;

    return (
        <>
            <Head title="Shift Handover" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="lg">
                        <Group justify="space-between" align="center" wrap="wrap" gap="md">
                            <Group gap="sm" wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="brandTeal" size={40} radius="md"><IconArrowsLeftRight size={22} stroke={1.7} /></ThemeIcon>
                                <Box>
                                    <Text fz={{ base: 20, sm: 24 }} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>Shift Handover</Text>
                                    <Text c="dimmed" fz="sm">Notes and alerts passed between shifts.</Text>
                                </Box>
                            </Group>
                            <Button radius="md" leftSection={<IconPlus size={16} />} onClick={newHandover.open}>New handover</Button>
                        </Group>
                    </Box>

                    {/* One-line date toolbar */}
                    <Box style={{ ...surface, padding: '10px 16px' }} mb="lg">
                        <Group gap="xs" wrap="nowrap">
                            <Button variant="default" radius="md" px="sm" onClick={() => reload(prevDate)}><IconChevronLeft size={16} /></Button>
                            <TextInput type="date" radius="md" value={selectedDate} onChange={(e) => reload(e.currentTarget.value)} />
                            <Button variant="default" radius="md" px="sm" onClick={() => reload(nextDate)}><IconChevronRight size={16} /></Button>
                            <Button variant="light" radius="md" onClick={() => reload(todayDate)}>Today</Button>
                        </Group>
                    </Box>

                    <FlashAlerts />

                    {/* Compact metric cards */}
                    <SimpleGrid cols={{ base: 2, sm: 4 }} mb="lg" spacing="sm" maw={620}>
                        <MetricCard label="Handovers" value={stats.total} color="brandTeal" icon={IconArrowsLeftRight} />
                        <MetricCard label="Pending ack" value={stats.pending} color="orange" icon={IconClockHour4} alert />
                        <MetricCard label="Acknowledged" value={stats.acknowledged} color="green" icon={IconUserCheck} />
                        <MetricCard label="Priority alerts" value={stats.alerts} color="red" icon={IconAlertTriangle} alert />
                    </SimpleGrid>

                    {/* Workspace — handover cards (left) + attention sidebar (right) */}
                    <Grid gutter={{ base: 'md', lg: 'xl' }} columns={10}>
                        <Grid.Col span={{ base: 10, md: 7 }}>
                            {handovers.length === 0
                                ? <Box style={{ ...surface, padding: '40px 18px' }}><Text c="dimmed" ta="center">No handovers for this day.</Text></Box>
                                : <Stack gap="lg">{handovers.map((h) => <HandoverCard key={h.id} h={h} onAcknowledge={acknowledge} />)}</Stack>}
                        </Grid.Col>

                        {/* Attention sidebar */}
                        <Grid.Col span={{ base: 10, md: 3 }}>
                            <Box style={{ ...surface, padding: '16px 16px' }}>
                                <Group gap={8} wrap="nowrap" mb="md">
                                    <ThemeIcon variant="light" color="red" size={28} radius="md"><IconActivity size={16} stroke={1.7} /></ThemeIcon>
                                    <Text fw={700}>Attention</Text>
                                </Group>

                                {!hasAttention && <Text size="sm" c="dimmed">No priority alerts or actions for this day.</Text>}

                                {priorityAlerts.length > 0 && (
                                    <Box mb="lg">
                                        <Text size="xs" fw={700} c="dimmed" tt="uppercase" mb={6}>Priority alerts</Text>
                                        <Stack gap={8}>
                                            {priorityAlerts.slice(0, 6).map((a, i) => (
                                                <Group key={i} gap="xs" wrap="nowrap" align="flex-start">
                                                    <ThemeIcon variant="light" color="red" size={22} radius="xl"><IconAlertTriangle size={12} /></ThemeIcon>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Text size="sm" lineClamp={2}>{a.text}</Text>
                                                        {a.time && <Text fz={11} c="dimmed">{a.time}</Text>}
                                                    </Box>
                                                </Group>
                                            ))}
                                        </Stack>
                                    </Box>
                                )}

                                {actionConcerns.length > 0 && (
                                    <Box>
                                        <Text size="xs" fw={700} c="dimmed" tt="uppercase" mb={6}>Action required</Text>
                                        <Stack gap={8}>
                                            {actionConcerns.slice(0, 6).map((c, i) => (
                                                <Group key={i} gap="xs" wrap="nowrap" align="flex-start">
                                                    <ThemeIcon variant="light" color="orange" size={22} radius="xl"><IconNote size={12} /></ThemeIcon>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Text size="sm" lineClamp={2}><b>{c.client}:</b> {c.concern}</Text>
                                                        {c.time && <Text fz={11} c="dimmed">{c.time}</Text>}
                                                    </Box>
                                                </Group>
                                            ))}
                                        </Stack>
                                    </Box>
                                )}
                            </Box>
                        </Grid.Col>
                    </Grid>
                  </Box>

                    <AddHandoverModal
                        opened={newOpened}
                        onClose={newHandover.close}
                        serviceUsers={serviceUsers}
                        defaultDate={selectedDate}
                    />
                </Container>
            </Box>
        </>
    );
}

ShiftHandover.layout = (page) => <AppShell>{page}</AppShell>;
