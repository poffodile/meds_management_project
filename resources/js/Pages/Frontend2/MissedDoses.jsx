import { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Box, Group, Text, TextInput, Badge, Button, ThemeIcon, SegmentedControl, SimpleGrid, ActionIcon,
} from '@mantine/core';
import {
    IconChevronLeft, IconChevronRight, IconCircleX, IconBan, IconAlertCircle, IconCircleCheck, IconCheck,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import ResolveDoseModal from '@frontend/features/medications/ResolveDoseModal';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';

const PAGE = '/frontend2/missed-doses';
const RESOLVE = '/frontend2/missed-doses/resolve';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const issueMeta = (kind) => (kind === 'missed'
    ? { label: 'Missed', color: 'red', Icon: IconCircleX }
    : { label: 'Not given', color: 'orange', Icon: IconBan });
const reasonOf = (i) => (i.kind === 'not_given' ? (CODE_LABELS[i.code] ?? i.code ?? '—') : '—');

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

export default function MissedDoses({ items = [], stats = {}, date, prevDate, nextDate, todayDate, statusFilter = 'outstanding' }) {
    const [resolveItem, setResolveItem] = useState(null);
    const [resolveOpened, resolve] = useDisclosure(false);
    const reload = (params) => router.get(PAGE, { date, status: statusFilter, ...params }, { preserveScroll: true, preserveState: true });
    const openResolve = (i) => { setResolveItem(i); resolve.open(); };
    const sorted = useMemo(() => [...items].sort((a, b) => String(a.slot).localeCompare(String(b.slot))), [items]);

    return (
        <AppShell title="Missed doses">
            <Head title="Missed doses" />
            <Box>
                {/* Toolbar */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Group gap="xs" wrap="nowrap" align="center">
                        <ActionIcon variant="default" radius="xl" size="lg" onClick={() => reload({ date: prevDate })}><IconChevronLeft size={16} /></ActionIcon>
                        <TextInput type="date" radius="xl" value={date || ''} onChange={(e) => reload({ date: e.currentTarget.value })} />
                        <ActionIcon variant="default" radius="xl" size="lg" onClick={() => reload({ date: nextDate })}><IconChevronRight size={16} /></ActionIcon>
                        <Button variant="light" color="indigo" radius="xl" onClick={() => reload({ date: todayDate })}>Today</Button>
                    </Group>
                    <SegmentedControl radius="xl" value={statusFilter} onChange={(v) => reload({ status: v })}
                        data={[{ label: 'Outstanding', value: 'outstanding' }, { label: 'Resolved', value: 'resolved' }, { label: 'All', value: 'all' }]} />
                </Group>

                <SimpleGrid cols={{ base: 2, sm: 4 }} spacing="md" mb="lg">
                    <Metric icon={IconCircleX} label="Missed" value={stats.missed ?? 0} color="red" />
                    <Metric icon={IconBan} label="Not given" value={stats.not_given ?? 0} color="orange" />
                    <Metric icon={IconAlertCircle} label="Outstanding" value={stats.outstanding ?? 0} color="violet" />
                    <Metric icon={IconCircleCheck} label="Resolved" value={stats.resolved ?? 0} color="teal" />
                </SimpleGrid>

                <Box style={card}>
                    <Group px="md" py="sm" c="dimmed" wrap="nowrap" visibleFrom="sm">
                        <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 200px', letterSpacing: 0.5 }}>Resident · medication</Text>
                        <Text fz={10} fw={700} tt="uppercase" style={{ width: 56, letterSpacing: 0.5 }}>Time</Text>
                        <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 100px', letterSpacing: 0.5 }}>Issue</Text>
                        <Text fz={10} fw={700} tt="uppercase" style={{ flex: '1 1 110px', letterSpacing: 0.5 }}>Reason</Text>
                        <Box style={{ width: 110 }} />
                    </Group>
                    {sorted.length === 0
                        ? <Text fz="sm" c="dimmed" ta="center" py={48}>No dose issues for this day.</Text>
                        : sorted.map((i, idx) => {
                            const im = issueMeta(i.kind);
                            return (
                                <Group key={i.id} gap="md" wrap="nowrap" align="center" px="md" py={12} style={{ borderTop: idx ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
                                    <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 200px', minWidth: 0 }}>
                                        <ThemeIcon variant="light" color={im.color} size={36} radius="md"><im.Icon size={18} /></ThemeIcon>
                                        <Box style={{ minWidth: 0 }}>
                                            <Text fz="sm" fw={700} truncate>{i.resident_name}</Text>
                                            <Text fz="xs" c="dimmed" truncate>{i.medication_name}</Text>
                                        </Box>
                                    </Group>
                                    <Text fz="sm" fw={700} style={{ width: 56, flexShrink: 0 }} visibleFrom="sm">{i.slot}</Text>
                                    <Box style={{ flex: '1 1 100px', minWidth: 0 }} visibleFrom="sm"><Badge color={im.color} variant="light" radius="sm">{im.label}</Badge></Box>
                                    <Text fz="xs" c={reasonOf(i) === '—' ? 'dimmed' : undefined} fw={reasonOf(i) === '—' ? 400 : 600} style={{ flex: '1 1 110px', minWidth: 0 }} visibleFrom="md" truncate>{reasonOf(i)}</Text>
                                    <Box style={{ width: 110, flexShrink: 0, textAlign: 'right' }}>
                                        {i.resolved
                                            ? <Badge color="teal" variant="light" radius="sm">Resolved</Badge>
                                            : <Button size="xs" radius="xl" color="indigo" leftSection={<IconCheck size={14} />} onClick={() => openResolve(i)}>Resolve</Button>}
                                    </Box>
                                </Group>
                            );
                        })}
                    <Box py={4} />
                </Box>
            </Box>

            <ResolveDoseModal opened={resolveOpened} onClose={resolve.close} item={resolveItem} date={date} action={RESOLVE} />
        </AppShell>
    );
}
