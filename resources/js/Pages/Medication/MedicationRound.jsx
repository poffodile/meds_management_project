import { useState } from 'react';
import { Head } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Container, Grid, Card, Paper, Group, Stack, Text, Box, TextInput, Button,
    ScrollArea, Divider, Badge,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconUsers, IconShieldCheck,
    IconQrcode, IconPlus, IconUserMinus, IconNotes, IconFileText, IconClipboardList,
    IconAlertTriangle, IconShieldLock, IconPill, IconClock,
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
import { usePageReload } from '@frontend/hooks/usePageReload';

const ENDPOINT = '/medication/medication-round-react';

// Map a derived dose-timing bucket to a display status + label for MedicationCard.
const STATUS_DISPLAY = {
    overdue: { status: 'overdue', label: 'Overdue' },
    due_now: { status: 'due soon', label: 'Due Now' },
    upcoming: { status: 'due', label: 'Upcoming' },
    later: { status: 'due', label: 'Scheduled' },
    due: { status: 'due', label: 'PRN' },
};

/** Overall round status for a resident, from their rows' derived buckets. */
function residentStatus(resident) {
    const rows = resident.rows ?? [];
    if (rows.length === 0) return { status: 'not started', label: 'No meds' };
    const completed = rows.filter((r) => r.code).length;
    if (completed === rows.length) return { status: 'all given', label: 'All Given' };
    const overdue = rows.filter((r) => !r.code && r.status === 'overdue').length;
    if (overdue > 0) return { status: 'overdue', label: `${overdue} overdue` };
    return { status: 'due', label: `${rows.length - completed} due` };
}

/** Map a payload row into MedicationCard's `med` shape. */
function toMed(row) {
    const d = STATUS_DISPLAY[row.status] ?? { status: 'due', label: null };
    return {
        name: row.medication_name,
        strength: row.strength,
        tags: [{ label: row.as_required ? 'PRN' : 'Regular', color: row.as_required ? 'grape' : 'blue' }],
        dose: row.dose,
        route: row.route,
        instruction: row.instruction,
        time: row.slot,
        status: row.code ? 'completed' : d.status,
        statusLabel: row.code ? null : d.label,
        stock: row.stock,
        stockUnit: row.unit,
        lowStock: row.low_stock,
        isControlled: row.is_controlled,
        cdSchedule: row.cd_schedule,
        code: row.code,
    };
}

export default function MedicationRound({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
    const reload = usePageReload(ENDPOINT);
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

    const selected = residents.find((r) => r.client_id === selectedId) ?? residents[0] ?? null;

    // Round-wide progress (scheduled meds only — PRN aren't "due").
    const sched = residents.flatMap((r) => r.rows).filter((r) => !r.as_required);
    const pCompleted = sched.filter((r) => r.code).length;
    const pOverdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const pDueSoon = sched.filter((r) => !r.code && r.status === 'due_now').length;
    const pNotStarted = sched.length - pCompleted - pOverdue - pDueSoon;

    // Round-wide alerts.
    const overdueAlerts = residents.flatMap((r) =>
        r.rows.filter((row) => !row.code && row.status === 'overdue')
            .map((row) => ({ resident: r.name, med: row.medication_name, time: row.slot })));
    const lowStockMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.low_stock).map((r) => r.medication_name))];
    const cdMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.is_controlled).map((r) => r.medication_name))];

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    // Selected resident's meds, grouped.
    const selRows = selected?.rows ?? [];
    const scheduled = selRows.filter((r) => !r.as_required);
    const prn = selRows.filter((r) => r.as_required);
    const dueNow = scheduled.filter((r) => r.code || r.status === 'overdue' || r.status === 'due_now');
    const upcoming = scheduled.filter((r) => !r.code && (r.status === 'upcoming' || r.status === 'later' || r.status === 'due'));

    const riskFlags = selected?.risk_flags ?? [];
    const hasHighRisk = riskFlags.some((r) => r.level === 'high' || r.level === 'urgent');

    return (
        <>
            <Head title="Medication Round" />
            <Container size="xl" py="md">
                {/* ---- Page header ---- */}
                <Group justify="space-between" align="flex-start" mb="md" wrap="nowrap">
                    <Box>
                        <Text fz={24} fw={700}>Medication Round</Text>
                        <Text c="dimmed" size="sm">Record medication administration for your residents</Text>
                    </Box>
                    <Group gap="xs" wrap="nowrap">
                        <Button variant="default" leftSection={<IconRefresh size={16} />} onClick={() => reload({ date })}>Refresh</Button>
                        <Button leftSection={<IconCircleCheck size={16} />} disabled title="Coming soon">End Round</Button>
                    </Group>
                </Group>

                <FlashAlerts />

                {/* ---- Controls: date + round selector ---- */}
                <Card withBorder radius="lg" padding="sm" mb="md">
                    <Group justify="space-between" wrap="wrap" gap="sm">
                        <TextInput
                            type="date"
                            value={date}
                            onChange={(e) => reload({ date: e.currentTarget.value })}
                            leftSection={<IconCalendar size={16} />}
                        />
                        <Group gap="xs" wrap="wrap">
                            {rounds.map((r) => {
                                const RI = roundTokens[r.key]?.icon ?? IconPill;
                                const active = r.key === meta.key;
                                const color = roundTokens[r.key]?.color ?? 'indigo';
                                return (
                                    <Button
                                        key={r.key}
                                        size="sm"
                                        variant={active ? 'light' : 'subtle'}
                                        color={active ? color : 'gray'}
                                        leftSection={<RI size={16} />}
                                        onClick={() => { setActiveRound(r.key); setSelectedId(null); }}
                                    >
                                        <Box ta="left">
                                            <Text size="sm" fw={600} lh={1}>{r.label}</Text>
                                            {r.window && <Text size="xs" c="dimmed">{r.window}</Text>}
                                        </Box>
                                    </Button>
                                );
                            })}
                        </Group>
                    </Group>
                </Card>

                {/* ---- 3-column workspace ---- */}
                <Grid gutter="md">
                    {/* Left — residents due */}
                    <Grid.Col span={{ base: 12, md: 3 }}>
                        <Card withBorder radius="lg" padding="sm">
                            <Group justify="space-between" mb="xs">
                                <Text fw={700}>Residents Due</Text>
                                <Badge variant="light" color="gray">{residents.length}</Badge>
                            </Group>
                            <TextInput
                                placeholder="Search residents…"
                                leftSection={<IconSearch size={15} />}
                                value={query}
                                onChange={(e) => setQuery(e.currentTarget.value)}
                                mb="sm"
                            />
                            <ScrollArea.Autosize mah={580}>
                                <Stack gap={4}>
                                    {filtered.length === 0
                                        ? <Text size="sm" c="dimmed" ta="center" py="md">No residents.</Text>
                                        : filtered.map((r) => {
                                            const st = residentStatus(r);
                                            return (
                                                <ResidentListItem
                                                    key={r.client_id}
                                                    resident={{ name: r.name, room: r.room, photo: r.photo }}
                                                    status={st.status}
                                                    statusLabel={st.label}
                                                    selected={selected?.client_id === r.client_id}
                                                    onClick={() => setSelectedId(r.client_id)}
                                                />
                                            );
                                        })}
                                </Stack>
                            </ScrollArea.Autosize>
                        </Card>
                    </Grid.Col>

                    {/* Centre — selected resident + medications */}
                    <Grid.Col span={{ base: 12, md: 6 }}>
                        {!selected ? (
                            <Paper withBorder radius="lg" p="xl"><Text c="dimmed" ta="center">No medications in this round.</Text></Paper>
                        ) : (
                            <Stack gap="md">
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
                                    <Group gap="xs" mb="xs">
                                        <Text fw={700}>Due Now</Text>
                                        <Badge variant="light" color="blue">{dueNow.length}</Badge>
                                    </Group>
                                    <Stack gap="sm">
                                        {dueNow.length === 0
                                            ? <Paper withBorder radius="md" p="md"><Text size="sm" c="dimmed">Nothing due right now.</Text></Paper>
                                            : dueNow.map((row, i) => (
                                                <MedicationCard key={i} med={toMed(row)} onAction={(code) => openRecord(row, code)} />
                                            ))}
                                    </Stack>
                                </Box>

                                {prn.length > 0 && (
                                    <Box>
                                        <Group gap="xs" mb="xs">
                                            <Text fw={700}>PRN Medications</Text>
                                            <Badge variant="light" color="grape">{prn.length}</Badge>
                                        </Group>
                                        <Stack gap="sm">
                                            {prn.map((row, i) => (
                                                <MedicationCard key={i} med={toMed(row)} onAction={(code) => openRecord(row, code)} />
                                            ))}
                                        </Stack>
                                    </Box>
                                )}

                                {upcoming.length > 0 && (
                                    <Box>
                                        <Group gap="xs" mb="xs">
                                            <IconClock size={16} />
                                            <Text fw={700}>Upcoming</Text>
                                            <Text size="sm" c="dimmed">Next 2 hours</Text>
                                            <Badge variant="light" color="gray">{upcoming.length}</Badge>
                                        </Group>
                                        <Stack gap="sm">
                                            {upcoming.map((row, i) => (
                                                <MedicationCard key={i} med={toMed(row)} onAction={(code) => openRecord(row, code)} />
                                            ))}
                                        </Stack>
                                    </Box>
                                )}
                            </Stack>
                        )}
                    </Grid.Col>

                    {/* Right — progress, alerts, quick actions */}
                    <Grid.Col span={{ base: 12, md: 3 }}>
                        <Stack gap="md">
                            <Card withBorder radius="lg" padding="lg">
                                <Text fw={700} mb="md">Round Progress</Text>
                                <RoundProgressDonut completed={pCompleted} dueSoon={pDueSoon} overdue={pOverdue} notStarted={pNotStarted} />
                            </Card>

                            <Card withBorder radius="lg" padding="md">
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

                            <Card withBorder radius="lg" padding="md">
                                <Text fw={700} mb="sm">Quick Actions</Text>
                                <Stack gap={2}>
                                    <QuickActionItem icon={IconQrcode} label="Scan Medication" description="Scan barcode to administer" disabled />
                                    <QuickActionItem icon={IconPlus} label="Add PRN" description="Record PRN medication" disabled />
                                    <QuickActionItem icon={IconUserMinus} label="Temporary Absence" description="Mark resident as absent" disabled />
                                    <QuickActionItem icon={IconNotes} label="View Handover Notes" description="See notes for this round" href="/medication/shift-handover-react" />
                                    <QuickActionItem icon={IconFileText} label="View MAR Report" description="Full administration record" disabled />
                                </Stack>
                            </Card>
                        </Stack>
                    </Grid.Col>
                </Grid>

                {/* ---- Footer ---- */}
                <Group justify="center" gap="xl" mt="xl" pt="md" c="dimmed" style={{ borderTop: '1px solid var(--mantine-color-gray-2)' }}>
                    <Group gap={6}><IconShieldCheck size={15} /><Text size="xs">All medication records are secure and audit-trail enabled</Text></Group>
                    <Group gap={6}><IconUsers size={15} /><Text size="xs">{residents.length} residents this round · {formatDate(date)}</Text></Group>
                </Group>

                <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} />
            </Container>
        </>
    );
}

MedicationRound.layout = (page) => <AppShell>{page}</AppShell>;
