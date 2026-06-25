import { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import FlashAlerts from '@frontend/components/FlashAlerts';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Group, Button, TextInput, SegmentedControl, SimpleGrid, Box, ThemeIcon, Text, Badge,
    Grid, Table, Select, Tooltip, ActionIcon, Stack,
} from '@mantine/core';
import {
    IconAlertTriangle, IconBan, IconAlertCircle, IconCircleCheck, IconCircleX,
    IconChevronLeft, IconChevronRight, IconCheck, IconSearch, IconArrowRight,
    IconActivity, IconDownload,
} from '@tabler/icons-react';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';
import ResolveDoseModal from '@frontend/features/medications/ResolveDoseModal';
import AppShell from '@frontend/Layouts/AppShell';
import { downloadCsv } from '@frontend/lib/csv';
import classes from './MissedDoses.module.css';

const surface = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06)',
};

const issueMeta = (kind) => (kind === 'missed'
    ? { label: 'Missed', color: 'red', Icon: IconCircleX }
    : { label: 'Not given', color: 'orange', Icon: IconBan });

const statusMeta = (resolved) => (resolved
    ? { label: 'Resolved', color: 'green' }
    : { label: 'Outstanding', color: 'grape' });

const reasonOf = (i) => (i.kind === 'not_given' ? (CODE_LABELS[i.code] ?? i.code ?? '—') : '—');

// Compact horizontal metric tile — icon + label left, value right.
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

export default function MissedDoses({
    items = [], stats = {}, date, prevDate, nextDate, todayDate, statusFilter = 'outstanding',
}) {
    const [resolveItem, setResolveItem] = useState(null);
    const [resolveOpened, resolve] = useDisclosure(false);
    const [search, setSearch] = useState('');
    const [issueFilter, setIssueFilter] = useState('all');

    const reload = (params) => router.get(
        '/medication/missed-doses-react',
        { date, status: statusFilter, ...params },
        { preserveScroll: true, preserveState: true },
    );

    const openResolve = (item) => { setResolveItem(item); resolve.open(); };

    const outstanding = useMemo(() => items.filter((i) => !i.resolved), [items]);

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        return items.filter((i) => {
            if (q && !`${i.resident_name} ${i.medication_name}`.toLowerCase().includes(q)) return false;
            if (issueFilter !== 'all' && i.kind !== issueFilter) return false;
            return true;
        });
    }, [items, search, issueFilter]);

    const timeline = useMemo(
        () => [...items].sort((a, b) => String(a.slot).localeCompare(String(b.slot))).slice(0, 14),
        [items],
    );

    const exportCsv = () => downloadCsv(`missed-doses-${date}.csv`, [
        { header: 'Resident', value: (i) => i.resident_name },
        { header: 'Medication', value: (i) => i.medication_name },
        { header: 'Time', value: (i) => i.slot },
        { header: 'Issue', value: (i) => issueMeta(i.kind).label },
        { header: 'Reason', value: (i) => (reasonOf(i) === '—' ? '' : reasonOf(i)) },
        { header: 'Status', value: (i) => statusMeta(i.resolved).label },
        { header: 'Resolution', value: (i) => i.clinical_action ?? '' },
    ], filtered);

    const rows = filtered.map((i) => {
        const im = issueMeta(i.kind);
        const st = statusMeta(i.resolved);
        return (
            <Table.Tr key={i.id}>
                <Table.Td><Text size="sm" fw={500}>{i.resident_name}</Text></Table.Td>
                <Table.Td><Text size="sm">{i.medication_name}</Text></Table.Td>
                <Table.Td><Text size="sm" fw={600}>{i.slot}</Text></Table.Td>
                <Table.Td>
                    <Group gap={6} wrap="nowrap">
                        <ThemeIcon variant="light" color={im.color} size={24} radius="xl"><im.Icon size={13} /></ThemeIcon>
                        <Text size="sm">{im.label}</Text>
                    </Group>
                </Table.Td>
                <Table.Td><Text size="sm" c={reasonOf(i) === '—' ? 'dimmed' : undefined}>{reasonOf(i)}</Text></Table.Td>
                <Table.Td><Badge color={st.color} variant="light" tt="none">{st.label}</Badge></Table.Td>
                <Table.Td>
                    <Group justify="flex-end" wrap="nowrap" className={i.resolved ? undefined : classes.rowActions}>
                        {i.resolved
                            ? <Text size="xs" c="dimmed">{i.clinical_action ?? 'Reviewed'}</Text>
                            : <Button size="compact-sm" variant="light" leftSection={<IconCheck size={14} />} onClick={() => openResolve(i)}>Resolve</Button>}
                    </Group>
                </Table.Td>
            </Table.Tr>
        );
    });

    return (
        <>
            <Head title="Missed Doses" />
            <Box style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-8))', minHeight: '100%' }}>
                <Container size={1640} py="lg">
                  <Box style={{ transform: 'scale(0.97)', transformOrigin: '50% 0' }}>
                    {/* Header */}
                    <Box style={{ ...surface, padding: '16px 20px' }} mb="lg">
                        <Group gap="sm" wrap="nowrap" align="center">
                            <ThemeIcon variant="light" color="red" size={40} radius="md"><IconAlertTriangle size={22} stroke={1.7} /></ThemeIcon>
                            <Box>
                                <Text fz={{ base: 20, sm: 24 }} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.2}>Missed Doses Review</Text>
                                <Text c="dimmed" fz="sm">Review, resolve and follow up missed and not-given doses.</Text>
                            </Box>
                        </Group>
                    </Box>

                    {/* One-line toolbar — date nav (left) + status filter (right) */}
                    <Box style={{ ...surface, padding: '10px 16px' }} mb="lg">
                        <Group justify="space-between" align="center" wrap="wrap" gap="md">
                            <Group gap="xs" wrap="nowrap" align="center">
                                <Button variant="default" radius="md" px="sm" onClick={() => reload({ date: prevDate })}><IconChevronLeft size={16} /></Button>
                                <TextInput type="date" radius="md" value={date} onChange={(e) => reload({ date: e.currentTarget.value })} />
                                <Button variant="default" radius="md" px="sm" onClick={() => reload({ date: nextDate })}><IconChevronRight size={16} /></Button>
                                <Button variant="light" radius="md" onClick={() => reload({ date: todayDate })}>Today</Button>
                            </Group>
                            <SegmentedControl radius="md"
                                value={statusFilter}
                                onChange={(v) => reload({ status: v })}
                                data={[
                                    { label: 'Outstanding', value: 'outstanding' },
                                    { label: 'Resolved', value: 'resolved' },
                                    { label: 'All', value: 'all' },
                                ]}
                            />
                        </Group>
                    </Box>

                    <FlashAlerts />

                    {/* Compact metric cards */}
                    <SimpleGrid cols={{ base: 2, sm: 4 }} mb="lg" spacing="sm" maw={620}>
                        <MetricCard label="Missed" value={stats.missed ?? 0} color="red" icon={IconCircleX} alert />
                        <MetricCard label="Not given" value={stats.not_given ?? 0} color="orange" icon={IconBan} alert />
                        <MetricCard label="Outstanding" value={stats.outstanding ?? 0} color="grape" icon={IconAlertCircle} alert />
                        <MetricCard label="Resolved" value={stats.resolved ?? 0} color="green" icon={IconCircleCheck} />
                    </SimpleGrid>

                    {/* Workspace — table (left) + recent events timeline (right) */}
                    <Grid gutter={{ base: 'md', lg: 'xl' }} columns={10}>
                        <Grid.Col span={{ base: 10, md: 7 }}>
                            {/* Outstanding follow-up strip */}
                            {outstanding.length > 0 && (
                                <Box style={{
                                    background: 'light-dark(var(--mantine-color-grape-0), var(--mantine-color-dark-6))',
                                    border: '1px solid light-dark(var(--mantine-color-grape-2), var(--mantine-color-dark-4))',
                                    borderRadius: 16, padding: '12px 16px',
                                }} mb="lg">
                                    <Group gap={8} wrap="nowrap" mb={8}>
                                        <IconAlertCircle size={16} stroke={1.9} color="var(--mantine-color-grape-6)" />
                                        <Text fw={700} size="sm">Outstanding follow-up</Text>
                                        <Badge size="sm" variant="light" color="grape" radius="sm">{stats.outstanding ?? outstanding.length}</Badge>
                                    </Group>
                                    <Stack gap={4}>
                                        {outstanding.slice(0, 3).map((i) => (
                                            <Text key={i.id} size="sm">
                                                <b>{i.resident_name}</b> {issueMeta(i.kind).label.toLowerCase()} <b>{i.medication_name}</b> at {i.slot}
                                            </Text>
                                        ))}
                                    </Stack>
                                </Box>
                            )}

                            <Box style={{ ...surface, padding: '16px 18px' }}>
                                {/* table toolbar */}
                                <Group gap="md" mb="md" wrap="wrap">
                                    <TextInput flex={1} miw={200} radius="md" leftSection={<IconSearch size={16} />}
                                        placeholder="Search resident or medication…" value={search} onChange={(e) => setSearch(e.currentTarget.value)} />
                                    <Select radius="md" w={150} checkIconPosition="right" value={issueFilter} onChange={(v) => setIssueFilter(v ?? 'all')}
                                        data={[{ value: 'all', label: 'All issues' }, { value: 'missed', label: 'Missed' }, { value: 'not_given', label: 'Not given' }]} />
                                    <Tooltip label="Download CSV of the listed doses" withArrow><Button variant="default" radius="md" ml="auto" leftSection={<IconDownload size={16} />} onClick={exportCsv} disabled={!filtered.length}>Export</Button></Tooltip>
                                </Group>

                                <Box style={{ maxHeight: 600, overflow: 'auto', borderRadius: 16, border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))' }}>
                                    <Table className={classes.invTable} highlightOnHover stickyHeader verticalSpacing="sm" miw={760}
                                        style={{ '--table-hover-color': 'light-dark(#faf7fc, var(--mantine-color-dark-5))' }}>
                                        <Table.Thead style={{ background: 'light-dark(#FAFBFC, var(--mantine-color-dark-7))', boxShadow: 'inset 0 -1px 0 light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))' }}>
                                            <Table.Tr>
                                                <Table.Th>Resident</Table.Th><Table.Th>Medication</Table.Th><Table.Th>Time</Table.Th>
                                                <Table.Th>Issue</Table.Th><Table.Th>Reason</Table.Th><Table.Th>Status</Table.Th><Table.Th ta="right">Action</Table.Th>
                                            </Table.Tr>
                                        </Table.Thead>
                                        <Table.Tbody>
                                            {rows.length ? rows : (
                                                <Table.Tr><Table.Td colSpan={7}><Text c="dimmed" ta="center" py="lg">No dose issues for this day.</Text></Table.Td></Table.Tr>
                                            )}
                                        </Table.Tbody>
                                    </Table>
                                </Box>
                            </Box>
                        </Grid.Col>

                        {/* Recent events timeline */}
                        <Grid.Col span={{ base: 10, md: 3 }}>
                            <Box style={{ ...surface, padding: '16px 16px' }}>
                                <Group gap={8} wrap="nowrap" mb="md">
                                    <ThemeIcon variant="light" color="red" size={28} radius="md"><IconActivity size={16} stroke={1.7} /></ThemeIcon>
                                    <Text fw={700}>Recent Events</Text>
                                </Group>
                                {timeline.length ? (
                                    <Stack gap={0}>
                                        {timeline.map((i, idx) => {
                                            const im = issueMeta(i.kind);
                                            return (
                                                <Group key={i.id} gap="xs" wrap="nowrap" align="flex-start" py={9}
                                                    style={{ borderTop: idx ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                    <ThemeIcon variant="light" color={im.color} size={24} radius="xl"><im.Icon size={13} /></ThemeIcon>
                                                    <Box style={{ flex: 1, minWidth: 0 }}>
                                                        <Group justify="space-between" wrap="nowrap" gap={6}>
                                                            <Text size="sm" fw={600} lineClamp={1} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))">{i.resident_name}</Text>
                                                            <Text fz={11} c="dimmed" style={{ whiteSpace: 'nowrap' }}>{i.slot}</Text>
                                                        </Group>
                                                        <Text size="xs" c="dimmed" lineClamp={1}>{im.label} {i.medication_name}</Text>
                                                        {i.resolved && <Group gap={4} wrap="nowrap" mt={2}><IconCircleCheck size={12} color="var(--mantine-color-green-6)" /><Text fz={11} c="green.7">Resolution added</Text></Group>}
                                                    </Box>
                                                </Group>
                                            );
                                        })}
                                    </Stack>
                                ) : <Text size="sm" c="dimmed">No events for this day.</Text>}
                            </Box>
                        </Grid.Col>
                    </Grid>
                  </Box>

                    <ResolveDoseModal opened={resolveOpened} onClose={resolve.close} item={resolveItem} date={date} />
                </Container>
            </Box>
        </>
    );
}

MissedDoses.layout = (page) => <AppShell>{page}</AppShell>;
