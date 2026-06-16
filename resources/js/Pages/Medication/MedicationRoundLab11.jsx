import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Container, Card, Paper, Group, Stack, Text, Box, TextInput, Button,
    Badge, ThemeIcon, ScrollArea, ActionIcon, SimpleGrid, Table,
} from '@mantine/core';
import {
    IconCalendar, IconSearch, IconRefresh, IconCircleCheck, IconPill,
    IconAlertTriangle, IconShieldLock, IconQrcode, IconPlus, IconUserMinus, IconNotes,
    IconFileText, IconClipboardList, IconX, IconClock,
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

// EXPERIMENTAL copy of the Medication Round page — safe to redesign freely.
const ENDPOINT = '/medication/medication-round-lab1-1';

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

/**
 * A sidebar box (Round Progress / Alerts / Quick Actions). Shared on purpose so all
 * three stay identical — change the styling here and every sidebar box updates.
 */
// Status + type lookups for the "Next Medications Due" / "Recent Activity" sections.
const DUE_STATUS = {
    overdue: { label: 'Overdue', color: 'red' },
    due_now: { label: 'Due Soon', color: 'orange' },
    due: { label: 'Due', color: 'blue' },
    upcoming: { label: 'Upcoming', color: 'gray' },
    later: { label: 'Later', color: 'gray' },
    completed: { label: 'Done', color: 'green' },
};
const ACT_CODE = { A: 'given', S: 'self-administered', R: 'refused', W: 'witnessed', N: 'not given', O: 'omitted' };
const medType = (row) => (row.is_controlled
    ? { label: 'Controlled', color: 'grape' }
    : row.as_required ? { label: 'PRN', color: 'teal' } : { label: 'Regular', color: 'blue' });

function SidebarCard({ accent, title, children, align = 'center', headerRight = null }) {
    return (
        <Card withBorder radius="lg" padding="sm"
            style={{ borderLeft: `4px solid var(--mantine-color-${accent}-5)`, minHeight: 170, display: 'flex', flexDirection: 'column' }}>
            <Group justify="space-between" align="flex-start" wrap="nowrap" gap="xs" mb={14}>
                <Text fw={700} size="xl">{title}</Text>
                {headerRight}
            </Group>
            {/* min-height keeps a short box looking like a box; content grows past it */}
            <Box style={{ flex: 1, minHeight: 0, display: 'flex', flexDirection: 'column', justifyContent: align }}>
                {children}
            </Box>
        </Card>
    );
}

export default function MedicationRoundLab11({ rounds = [], grid = {}, date, currentRound = 'morning' }) {
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

    // Detail opens only when a resident is explicitly selected (closable).
    const selected = selectedId != null ? (residents.find((r) => r.client_id === selectedId) ?? null) : null;

    // Round-wide progress (scheduled meds only).
    const sched = residents.flatMap((r) => r.rows).filter((r) => !r.as_required);
    const pCompleted = sched.filter((r) => r.code).length;
    const pOverdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const pDueSoon = sched.filter((r) => !r.code && r.status === 'due_now').length;
    const pNotStarted = sched.length - pCompleted - pOverdue - pDueSoon;

    // Whole-day totals (across every round) — for the overall "% complete" headline.
    const daySched = Object.values(grid).flat().flatMap((r) => r.rows).filter((r) => !r.as_required);
    const dayCompleted = daySched.filter((r) => r.code).length;
    const dayTotal = daySched.length;
    const dayPct = dayTotal ? Math.round((dayCompleted / dayTotal) * 100) : 0;
    // Rough estimated completion = the latest time slot still outstanding today.
    const dayRemainingSlots = daySched.filter((r) => !r.code && r.slot).map((r) => r.slot).sort();
    const estCompletion = dayRemainingSlots.length ? dayRemainingSlots[dayRemainingSlots.length - 1] : null;

    // For the lists below: every dose in this round tagged with its resident.
    const roundRows = residents.flatMap((r) => r.rows.map((row) => ({ ...row, resident: r.name })));
    const nextDue = roundRows
        .filter((row) => !row.as_required && !row.code)
        .sort((a, b) => String(a.slot || '').localeCompare(String(b.slot || '')))
        .slice(0, 6);
    const recentActivity = roundRows
        .filter((row) => row.code)
        .sort((a, b) => String(b.slot || '').localeCompare(String(a.slot || '')))
        .slice(0, 5);

    // Round-wide alerts.
    const overdueAlerts = residents.flatMap((r) =>
        r.rows.filter((row) => !row.code && row.status === 'overdue')
            .map((row) => ({ resident: r.name, med: row.medication_name, time: row.slot })));
    const lowStockMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.low_stock).map((r) => r.medication_name))];
    const cdMeds = [...new Set(residents.flatMap((r) => r.rows).filter((r) => r.is_controlled).map((r) => r.medication_name))];

    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); record.open(); };

    // One-tap "Given" for scheduled, non-controlled meds; everything else opens the dialog.
    const handleAction = (row, code) => {
        if (code === 'A' && !row.is_controlled && !row.as_required && row.slot) {
            router.post(`${ENDPOINT}/record`, {
                mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '', notes: '',
            }, { preserveScroll: true, preserveState: true });
        } else {
            openRecord(row, code);
        }
    };

    // Selected resident's meds, grouped.
    const selRows = selected?.rows ?? [];
    const scheduled = selRows.filter((r) => !r.as_required);
    const prn = selRows.filter((r) => r.as_required);
    const dueNow = scheduled.filter((r) => r.code || r.status === 'overdue' || r.status === 'due_now');
    const upcoming = scheduled.filter((r) => !r.code && (r.status === 'upcoming' || r.status === 'later' || r.status === 'due'));
    const riskFlags = selected?.risk_flags ?? [];
    const hasHighRisk = riskFlags.some((r) => r.level === 'high' || r.level === 'urgent');

    // ---- Panels (rendered into the desktop slide layout or the mobile stack) ----
    const residentsPanel = (
        <Card withBorder radius="lg" padding="sm" style={{ borderLeft: '4px solid var(--mantine-color-indigo-5)' }}>
            <Group justify="space-between" mb="xs">
                <Text fw={700}>Residents Due</Text>
                <Badge variant="light" color="gray">{residents.length}</Badge>
            </Group>
            <TextInput placeholder="Search residents…" leftSection={<IconSearch size={15} />} value={query} onChange={(e) => setQuery(e.currentTarget.value)} mb="sm" maw={560} />
            <ScrollArea.Autosize mah={isMobile ? 400 : 640}>
                {filtered.length === 0
                    ? <Text size="sm" c="dimmed" ta="center" py="md">No residents.</Text>
                    : (
                        <SimpleGrid cols={1} spacing={6} verticalSpacing={6} maw={560}>
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
        <Stack gap={12}>
            <SidebarCard accent="indigo" title="Round Progress" align="flex-start">
                <RoundProgressDonut completed={pCompleted} dueSoon={pDueSoon} overdue={pOverdue} notStarted={pNotStarted} size={84} detailed dayCompleted={dayCompleted} dayTotal={dayTotal} pctSize={31} legendFz={9} />
            </SidebarCard>

            <SidebarCard accent="orange" title="Alerts" align="flex-start">
                <Stack gap={9} px={6}>
                    {overdueAlerts.length === 0 && lowStockMeds.length === 0 && cdMeds.length === 0 && (
                        <Text size="sm" c="dimmed">No alerts for this round.</Text>
                    )}
                    {overdueAlerts.slice(0, 4).map((a, i) => (
                        <AlertItem key={`od-${i}`} compact severity="danger" icon={IconAlertTriangle}
                            title="Overdue Medication" description={`${a.resident} — ${a.med}${a.time ? ` · ${a.time}` : ''}`} />
                    ))}
                    {lowStockMeds.slice(0, 3).map((m) => (
                        <AlertItem key={`ls-${m}`} compact severity="warning" icon={IconAlertTriangle} title="Low Stock" description={m} />
                    ))}
                    {cdMeds.slice(0, 3).map((m) => (
                        <AlertItem key={`cd-${m}`} compact severity="info" icon={IconShieldLock} title="Controlled Drug" description={`${m} · requires witness`} />
                    ))}
                </Stack>
            </SidebarCard>

            <SidebarCard accent="teal" title="Quick Actions">
                <Stack gap={2}>
                    <QuickActionItem compact icon={IconQrcode} label="Scan Medication" disabled />
                    <QuickActionItem compact icon={IconPlus} label="Add PRN" disabled />
                    <QuickActionItem compact icon={IconUserMinus} label="Temporary Absence" disabled />
                    <QuickActionItem compact icon={IconNotes} label="View Handover Notes" href="/medication/shift-handover-react" />
                    <QuickActionItem compact icon={IconFileText} label="View MAR Report" disabled />
                </Stack>
            </SidebarCard>
        </Stack>
    );

    const controlsCard = (
        <Card withBorder radius="lg" padding="sm">
            <Group gap={44} wrap="nowrap" align="center" pl="md" pr="md">
                <TextInput type="date" value={date} onChange={(e) => reload({ date: e.currentTarget.value })} leftSection={<IconCalendar size={16} />} w={150} style={{ flexShrink: 0 }} />
                <Group gap="xs" wrap="nowrap" justify="flex-end" style={{ flex: 1 }}>
                    {rounds.map((r) => {
                        const RI = roundTokens[r.key]?.icon ?? IconPill;
                        const active = r.key === meta.key;
                        const color = roundTokens[r.key]?.color ?? 'indigo';
                        return (
                            <Button key={r.key} size="xs" variant={active ? 'light' : 'default'} color={active ? color : 'gray'}
                                styles={{ label: { fontWeight: 700 } }}
                                leftSection={<RI size={15} color={`var(--mantine-color-${color}-6)`} />}
                                onClick={() => { setActiveRound(r.key); setSelectedId(null); }}
                                title={r.window}>
                                {r.label}
                            </Button>
                        );
                    })}
                </Group>
            </Group>
        </Card>
    );

    const nextDueCard = (
        <Card withBorder radius="lg" padding="sm">
            <Group justify="space-between" mb="xs">
                <Text fw={700} size="sm">Next Medications Due</Text>
                <Badge variant="light" color="gray">{nextDue.length}</Badge>
            </Group>
            {nextDue.length === 0 ? (
                <Text size="sm" c="dimmed">Nothing left due in this round.</Text>
            ) : (
                <Table.ScrollContainer minWidth={300}>
                <Table verticalSpacing={5} horizontalSpacing={4} fz={10} highlightOnHover>
                    <Table.Thead>
                        <Table.Tr>
                            <Table.Th>Time</Table.Th><Table.Th>Resident</Table.Th>
                            <Table.Th>Medication</Table.Th><Table.Th>Type</Table.Th><Table.Th>Status</Table.Th>
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        {nextDue.map((row, i) => {
                            const t = medType(row);
                            const s = DUE_STATUS[row.status] ?? { label: row.status, color: 'gray' };
                            return (
                                <Table.Tr key={i}>
                                    <Table.Td><Text fw={700} c={`${s.color}.7`}>{row.slot || '—'}</Text></Table.Td>
                                    <Table.Td>{row.resident}</Table.Td>
                                    <Table.Td>{row.medication_name}</Table.Td>
                                    <Table.Td><Badge size="xs" variant="light" color={t.color} styles={{ root: { fontSize: 9, paddingInline: 5 } }}>{t.label}</Badge></Table.Td>
                                    <Table.Td><Badge size="xs" variant="light" color={s.color} styles={{ root: { fontSize: 9, paddingInline: 5 } }}>{s.label}</Badge></Table.Td>
                                </Table.Tr>
                            );
                        })}
                    </Table.Tbody>
                </Table>
                </Table.ScrollContainer>
            )}
        </Card>
    );

    const activityCard = (
        <Card withBorder radius="lg" padding="sm">
            <Group gap="xs" mb="xs">
                <IconClock size={16} color="var(--mantine-color-indigo-6)" />
                <Text fw={700} size="sm">Recent Activity</Text>
            </Group>
            {recentActivity.length === 0 ? (
                <Text size="sm" c="dimmed">No medications recorded yet this round.</Text>
            ) : (
                <Stack gap="md">
                    {recentActivity.map((row, i) => {
                        const given = row.code === 'A' || row.code === 'S' || row.code === 'W';
                        const refused = row.code === 'R' || row.code === 'O' || row.code === 'N';
                        const color = given ? 'green' : refused ? 'red' : 'gray';
                        const Icon = given ? IconCircleCheck : IconAlertTriangle;
                        return (
                            <Group key={i} gap="sm" wrap="nowrap" align="flex-start">
                                <Text size="xs" c="dimmed" w={40} ta="right" style={{ flexShrink: 0 }} mt={3}>{row.slot || '—'}</Text>
                                <ThemeIcon variant="light" color={color} size={26} radius="xl"><Icon size={15} /></ThemeIcon>
                                <Box style={{ flex: 1, minWidth: 0 }}>
                                    <Text size="sm" fw={600}>{row.medication_name} {ACT_CODE[row.code] ?? 'recorded'}</Text>
                                    <Text size="xs" c="dimmed">{row.resident}{row.recorded_by ? ` · by ${row.recorded_by}` : ''}</Text>
                                </Box>
                            </Group>
                        );
                    })}
                </Stack>
            )}
        </Card>
    );

    return (
        <>
            <Head title="Medication Round (Lab 1.1)" />
            <Container size={1700} py="md">
                {/* ---- Page header ---- */}
                <Group align="flex-start" gap={40} wrap="wrap" mb="xl">
                    {/* matches the left section so the buttons land above Night */}
                    <Box style={{ flex: '2.6 1 440px', minWidth: 0 }}>
                        <Group justify="space-between" align="center" wrap="wrap">
                            <Group gap="md" wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="indigo" size={48} radius="lg"><IconPill size={26} stroke={1.6} /></ThemeIcon>
                                <Box>
                                    <Group gap="xs" align="center">
                                        <Text fz={24} fw={700}>Medication Round</Text>
                                        <Badge color="grape" variant="light">Lab 1.1</Badge>
                                    </Group>
                                    <Text c="dimmed" size="sm">{meta.label} Round{meta.window ? ` • ${meta.window}` : ''}</Text>
                                </Box>
                            </Group>
                            <Group gap="xs" wrap="nowrap">
                                <Button variant="default" leftSection={<IconRefresh size={16} />} onClick={() => reload({ date })}>Refresh</Button>
                                <Button leftSection={<IconCircleCheck size={16} />} disabled title="Coming soon">End Round</Button>
                            </Group>
                        </Group>
                    </Box>
                    {/* matches the right sidebar (empty above the round progress box) */}
                    <Box style={{ flex: '0 1 260px', minWidth: 0 }} />
                </Group>

                <FlashAlerts />

                {/* ---- Main (2/3) + sidebar (1/3) ---- */}
                {isMobile ? (
                    <Stack gap="md">
                        {controlsCard}
                        {selected ? detailPanel : residentsPanel}
                        {nextDueCard}
                        {activityCard}
                        {sidebarPanel}
                    </Stack>
                ) : (
                    <Group align="flex-start" gap={40} wrap="wrap">
                        {/* LEFT SECTION — date/round bar + residents due (+ next due / activity) */}
                        <Box style={{ flex: '2.6 1 440px', minWidth: 0 }}>
                            <Stack gap="md">
                                {controlsCard}
                                <Group align="flex-start" gap={selected ? 'md' : 0} wrap="nowrap" style={{ overflow: 'hidden' }}>
                                    <Box style={{ flexGrow: selected ? 34 : 100, flexBasis: 0, minWidth: 0, transition: 'flex-grow 0.3s ease' }}>
                                        {residentsPanel}
                                    </Box>
                                    <Box style={{ flexGrow: selected ? 66 : 0, flexBasis: 0, minWidth: 0, overflow: 'hidden', opacity: selected ? 1 : 0, transition: 'flex-grow 0.3s ease, opacity 0.25s ease' }}>
                                        {detailPanel}
                                    </Box>
                                </Group>
                                <Group align="flex-start" gap="md" grow wrap="wrap">
                                    {nextDueCard}
                                    {activityCard}
                                </Group>
                            </Stack>
                        </Box>
                        {/* RIGHT — round progress / alerts / quick actions block (pulled up the page) */}
                        <Box style={{ flex: '0 1 260px', minWidth: 0, marginTop: -84 }}>
                            {sidebarPanel}
                        </Box>
                    </Group>
                )}

                <RecordDoseModal opened={recordOpened} onClose={record.close} row={recordRow} date={date} presetCode={recordCode} endpoint={`${ENDPOINT}/record`} />
            </Container>
        </>
    );
}

MedicationRoundLab11.layout = (page) => <AppShell>{page}</AppShell>;
