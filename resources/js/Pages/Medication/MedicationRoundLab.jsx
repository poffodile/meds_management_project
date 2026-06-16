// EXPERIMENTAL copy of the Medication Round page — safe to redesign freely.
// Served at /medication/medication-round-lab; shares the controller data + record
// logic with the main page but is otherwise independent.
import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Card, Paper, Group, Stack, Text, Box, TextInput, Button,
    Badge, ThemeIcon, ScrollArea, ActionIcon, SimpleGrid,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconPill,
    IconAlertTriangle, IconShieldLock, IconQrcode, IconPlus, IconUserMinus, IconNotes,
    IconFileText, IconClipboardList, IconX,
} from '@tabler/icons-react';

import AppShell from '@frontend/Layouts/AppShell';
import FlashAlerts from '@frontend/components/FlashAlerts';
import RoundProgressDonut from '@frontend/components/RoundProgressDonut';
import AlertItem from '@frontend/components/AlertItem';
import QuickActionItem from '@frontend/components/QuickActionItem';
import ResidentListItem from '@frontend/features/medications/ResidentListItem';
import ResidentCard from '@frontend/features/medications/ResidentCard';
import MedicationCard from '@frontend/features/medications/MedicationCard';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';

import { roundTokens } from '@frontend/tokens';
import { ageFromDob, formatDate } from '@frontend/lib/dateUtils';
import { toMed } from '@frontend/lib/medView';
import { usePageReload } from '@frontend/hooks/usePageReload';

const ENDPOINT = '/medication/medication-round-lab';
const RECORD_ENDPOINT = '/medication/medication-round-lab/record';

/** Overall round status for a resident, from their rows' recorded codes/buckets. */
function residentStatus(resident) {
    const rows = resident.rows ?? [];
    if (rows.length === 0) return { status: 'not started', label: 'No meds' };
    const completed = rows.filter((r) => r.code).length;
    if (completed === rows.length) return { status: 'all given', label: 'All Given' };
    const overdue = rows.filter((r) => !r.code && r.status === 'overdue').length;
    if (overdue > 0) return { status: 'overdue', label: `${overdue} overdue` };
    return { status: 'due', label: `${rows.length - completed} due` };
}

function SectionTitle({ color, children, count, unit }) {
    return (
        <Group gap={6} mb="sm" align="baseline">
            <Text fw={700} c={`${color}.7`}>{children}</Text>
            {count != null && <Text size="sm" c="dimmed">({count} {unit})</Text>}
        </Group>
    );
}

export default function MedicationRoundLab({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
    const reload = usePageReload(ENDPOINT);
    const isMobile = useMediaQuery('(max-width: 768px)');
    const [activeRound, setActiveRound] = useState(currentRound);
    const [selectedId, setSelectedId] = useState(null);
    const [query, setQuery] = useState('');
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState(null);
    const [recordOpened, record] = useDisclosure(false);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const filtered = query.trim()
        ? residents.filter((r) => r.name.toLowerCase().includes(query.toLowerCase()))
        : residents;

    const selected = selectedId != null ? (residents.find((r) => r.client_id === selectedId) ?? null) : null;

    const sched = residents.flatMap((r) => r.rows).filter((r) => !r.as_required);
    const pCompleted = sched.filter((r) => r.code).length;
    const pOverdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const pDueSoon = sched.filter((r) => !r.code && r.status === 'due_now').length;
    const pNotStarted = sched.length - pCompleted - pOverdue - pDueSoon;

    const overdueAlerts = residents.flatMap((r) =>
        r.rows.filter((row) => !row.code && row.status === 'overdue')
            .map((row) => ({ resident: r.name, med: row.medication_name, time: row.slot })));
    const lowStockMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.low_stock).map((r) => r.medication_name))];
    const cdMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.is_controlled).map((r) => r.medication_name))];

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    const handleAction = (row, code) => {
        if (code === 'A' && !row.is_controlled && !row.as_required && row.slot) {
            router.post(RECORD_ENDPOINT, {
                mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '', notes: '',
            }, { preserveScroll: true, preserveState: true });
        } else {
            openRecord(row, code);
        }
    };

    const selRows = selected?.rows ?? [];
    const scheduled = selRows.filter((r) => !r.as_required);
    const prn = selRows.filter((r) => r.as_required);
    const dueNow = scheduled.filter((r) => r.code || r.status === 'overdue' || r.status === 'due_now');
    const upcoming = scheduled.filter((r) => !r.code && (r.status === 'upcoming' || r.status === 'later' || r.status === 'due'));
    const riskFlags = selected?.risk_flags ?? [];
    const hasHighRisk = riskFlags.some((r) => r.level === 'high' || r.level === 'urgent');

    const residentsPanel = (
        <Card withBorder radius="lg" padding="sm" style={{ borderLeft: '4px solid var(--mantine-color-indigo-5)' }}>
            <Group justify="space-between" mb="xs">
                <Text fw={700}>Residents Due</Text>
                <Badge variant="light" color="gray">{residents.length}</Badge>
            </Group>
            <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />} value={query} onChange={(e) => setQuery(e.currentTarget.value)} mb="sm" />
            <ScrollArea.Autosize mah={isMobile ? 400 : 640}>
                {filtered.length === 0
                    ? <Text size="sm" c="dimmed" ta="center" py="md">No residents.</Text>
                    : (
                        <SimpleGrid cols={(isMobile || selected) ? 1 : 2} spacing={6} verticalSpacing={6}>
                            {filtered.map((r) => {
                                const st = residentStatus(r);
                                return (
                                    <ResidentListItem key={r.client_id}
                                        resident={{ name: r.name, room: r.room, photo: r.photo }}
                                        status={st.status} statusLabel={st.label}
                                        selected={selected?.client_id === r.client_id}
                                        onClick={() => setSelectedId(r.client_id)} />
                                );
                            })}
                        </SimpleGrid>
                    )}
            </ScrollArea.Autosize>
        </Card>
    );

    const detailPanel = selected && (
        <Stack gap="md">
            <Group justify="space-between" align="center">
                <Text fw={700} fz="lg">Resident Detail</Text>
                <ActionIcon variant="subtle" color="gray" onClick={() => setSelectedId(null)} title="Close">
                    <IconX size={18} />
                </ActionIcon>
            </Group>

            <ResidentCard
                resident={{
                    name: selected.name,
                    photo: selected.photo,
                    dob: selected.dob ? formatDate(selected.dob) : null,
                    age: ageFromDob(selected.dob),
                    gender: selected.gender,
                    weight: selected.weight,
                    weightUnit: selected.weight_unit,
                    allergies: selected.allergies ?? [],
                    riskFlags,
                }}
                metrics={[
                    { icon: IconAlertTriangle, label: 'Active Risks', value: riskFlags.length, color: hasHighRisk ? 'red' : 'gray' },
                    { icon: IconPill, label: 'PRN Available', value: selected.prn_count ?? 0, color: 'blue' },
                    { icon: IconClipboardList, label: 'Regular Meds', value: selected.regular_count ?? 0, color: 'indigo' },
                ]}
            />

            <Box>
                <SectionTitle color="blue" count={dueNow.length} unit="medications">Due Now</SectionTitle>
                <Stack gap="sm">
                    {dueNow.length === 0
                        ? <Paper withBorder radius="md" p="md"><Text size="sm" c="dimmed">Nothing due right now.</Text></Paper>
                        : dueNow.map((row, i) => <MedicationCard key={i} med={toMed(row)} onAction={(code) => handleAction(row, code)} />)}
                </Stack>
            </Box>

            {prn.length > 0 && (
                <Box>
                    <SectionTitle color="grape" count={prn.length} unit="available">PRN Medications</SectionTitle>
                    <Stack gap="sm">
                        {prn.map((row, i) => <MedicationCard key={i} med={toMed(row)} onAction={(code) => handleAction(row, code)} />)}
                    </Stack>
                </Box>
            )}

            {upcoming.length > 0 && (
                <Box>
                    <SectionTitle color="indigo" count={upcoming.length} unit="medications">Upcoming · Next 2 hours</SectionTitle>
                    <Stack gap="sm">
                        {upcoming.map((row, i) => <MedicationCard key={i} med={toMed(row)} onAction={(code) => handleAction(row, code)} />)}
                    </Stack>
                </Box>
            )}
        </Stack>
    );

    const sidebarPanel = (
        <Stack gap="md">
            <Card withBorder radius="lg" padding="lg" style={{ borderLeft: '4px solid var(--mantine-color-indigo-5)' }}>
                <Text fw={700} mb="md">Round Progress</Text>
                <RoundProgressDonut completed={pCompleted} dueSoon={pDueSoon} overdue={pOverdue} notStarted={pNotStarted} />
            </Card>

            <Card withBorder radius="lg" padding="md" style={{ borderLeft: '4px solid var(--mantine-color-orange-5)' }}>
                <Text fw={700} mb="sm">Alerts</Text>
                <Stack gap="xs">
                    {overdueAlerts.length === 0 && lowStockMeds.length === 0 && cdMeds.length === 0 && (
                        <Text size="sm" c="dimmed">No alerts for this round.</Text>
                    )}
                    {overdueAlerts.slice(0, 4).map((a, i) => (
                        <AlertItem key={`od-${i}`} severity="danger" icon={IconAlertTriangle}
                            title="Overdue Medication" description={`${a.resident} — ${a.med}${a.time ? ` · ${a.time}` : ''}`} />
                    ))}
                    {lowStockMeds.slice(0, 3).map((m) => (
                        <AlertItem key={`ls-${m}`} severity="warning" icon={IconAlertTriangle} title="Low Stock" description={m} />
                    ))}
                    {cdMeds.slice(0, 3).map((m) => (
                        <AlertItem key={`cd-${m}`} severity="info" icon={IconShieldLock} title="Controlled Drug" description={`${m} · requires witness`} />
                    ))}
                </Stack>
            </Card>

            <Card withBorder radius="lg" padding="md" style={{ borderLeft: '4px solid var(--mantine-color-teal-5)' }}>
                <Text fw={700} mb="sm">Quick Actions</Text>
                <Stack gap={2}>
                    <QuickActionItem icon={IconQrcode} label="Scan Medication" disabled />
                    <QuickActionItem icon={IconPlus} label="Add PRN" disabled />
                    <QuickActionItem icon={IconUserMinus} label="Temporary Absence" disabled />
                    <QuickActionItem icon={IconNotes} label="View Handover Notes" href="/medication/shift-handover-react" />
                    <QuickActionItem icon={IconFileText} label="View MAR Report" disabled />
                </Stack>
            </Card>
        </Stack>
    );

    return (
        <>
            <Head title="Medication Round (Lab)" />
            <Container size="xl" py="md">
                {/* Top row — date + actions */}
                <Group justify="space-between" mb="sm" wrap="wrap">
                    <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })} leftSection={<IconCalendar size={16} />} w={170}
                        styles={{ input: { fontWeight: 600, borderColor: 'var(--mantine-color-indigo-3)' } }} />
                    <Group gap="xs" wrap="nowrap">
                        <Button variant="default" leftSection={<IconRefresh size={16} />} onClick={() => reload({ date })}>Refresh</Button>
                        <Button leftSection={<IconCircleCheck size={16} />} disabled title="Coming soon">End Round</Button>
                    </Group>
                </Group>

                <Group gap="md" wrap="nowrap" align="center" mb="md">
                    <ThemeIcon variant="light" color="indigo" size={48} radius="lg"><IconPill size={26} stroke={1.6} /></ThemeIcon>
                        <Box>
                            <Group gap="xs" align="center">
                                <Text fz={24} fw={700}>Medication Round</Text>
                                <Badge color="grape" variant="light">Lab</Badge>
                            </Group>
                            <Text c="dimmed" size="sm">{meta.label} Round{meta.window ? ` • ${meta.window}` : ''}</Text>
                        </Box>
                </Group>

                <FlashAlerts />

                <Group justify="flex-end" gap="sm" wrap="wrap" mb="md">
                    {rounds.map((r) => {
                        const RI = roundTokens[r.key]?.icon ?? IconPill;
                        const active = r.key === meta.key;
                        const color = roundTokens[r.key]?.color ?? 'indigo';
                        return (
                            <Button key={r.key} size="md" radius="md" variant={active ? 'light' : 'default'} color={active ? color : 'gray'}
                                leftSection={<RI size={17} color={`var(--mantine-color-${color}-6)`} />}
                                onClick={() => { setActiveRound(r.key); setSelectedId(null); }}>
                                <Box ta="left">
                                    <Text size="sm" fw={600} lh={1.1}>{r.label}</Text>
                                    {r.window && <Text size="xs" c="dimmed">{r.window}</Text>}
                                </Box>
                            </Button>
                        );
                    })}
                </Group>

                {isMobile ? (
                    <Stack gap="md">
                        {selected ? detailPanel : residentsPanel}
                        {sidebarPanel}
                    </Stack>
                ) : (
                    <Group align="flex-start" gap="md" wrap="nowrap" style={{ overflow: 'hidden' }}>
                        <Box style={{ flexGrow: selected ? 27 : 62, flexBasis: 0, minWidth: 0, transition: 'flex-grow 0.3s ease' }}>
                            {residentsPanel}
                        </Box>
                        <Box style={{ flexGrow: selected ? 50 : 0, flexBasis: 0, minWidth: 0, overflow: 'hidden', opacity: selected ? 1 : 0, transition: 'flex-grow 0.3s ease, opacity 0.25s ease' }}>
                            {detailPanel}
                        </Box>
                        <Box style={{ flexGrow: selected ? 27 : 38, flexBasis: 0, minWidth: 0, transition: 'flex-grow 0.3s ease' }}>
                            {sidebarPanel}
                        </Box>
                    </Group>
                )}

                <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={RECORD_ENDPOINT} />
            </Container>
        </>
    );
}

MedicationRoundLab.layout = (page) => <AppShell>{page}</AppShell>;
