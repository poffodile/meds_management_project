import { Head, Link } from '@inertiajs/react';
import {
    Box, Group, Stack, Text, Badge, Button, Avatar, ThemeIcon, Anchor, Progress,
} from '@mantine/core';
import {
    IconArrowLeft, IconPill, IconShieldLock, IconClock, IconBolt, IconRoute2,
    IconBox, IconCalendar, IconBedFilled, IconInfoCircle, IconCircleCheck, IconCircleX, IconBan,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { CODE_LABELS } from '@frontend/lib/medicationCodes';

const ACCENT = '#4C6FFF';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const heading = { fontWeight: 700, fontSize: 16 };

function InfoRow({ icon: Icon, label, value, last }) {
    return (
        <Group justify="space-between" wrap="nowrap" align="center" py={10}
            style={{ borderBottom: last ? 'none' : '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
            <Group gap={8} wrap="nowrap"><Icon size={16} color="var(--mantine-color-gray-5)" /><Text fz="sm" c="dimmed">{label}</Text></Group>
            <Text fz="sm" fw={600} ta="right">{value || '—'}</Text>
        </Group>
    );
}

// A given/refused/omitted marker for an administration row.
function codeTone(code) {
    // 'S' (asleep) is not given — it must not show the same green tick as a dose
    // the resident actually took.
    if (isGivenCode(code)) return { c: 'teal', Icon: IconCircleCheck };
    if (code === 'R') return { c: 'red', Icon: IconCircleX };
    return { c: 'orange', Icon: IconBan };
}

export default function MedicationDetail({ med = {}, administrations = [] }) {
    const r = med.resident;
    const stockRef = med.reorder_level ? med.reorder_level * 2 : Math.max(med.stock ?? 0, 30);
    const stockPct = med.stock != null ? Math.min(100, Math.max(4, Math.round((med.stock / stockRef) * 100))) : 0;

    return (
        <AppShell title="Medication">
            <Head title={`${med.name ?? 'Medication'} · Detail`} />
            <Box>
                {/* Action row */}
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Anchor component={Link} href="/frontend2/medications" c="dimmed" fz="sm">
                        <Group gap={4} wrap="nowrap"><IconArrowLeft size={16} /> Medications</Group>
                    </Anchor>
                </Group>

                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Left — identity + resident */}
                    <Stack gap={18} style={{ flex: '1 1 300px', minWidth: 0 }}>
                        <Box style={{ ...card, padding: 20 }}>
                            <Group gap="md" wrap="nowrap" align="flex-start">
                                <ThemeIcon variant="light" color={med.controlled ? 'grape' : 'indigo'} size={56} radius="lg">
                                    {med.controlled ? <IconShieldLock size={30} /> : <IconPill size={30} />}
                                </ThemeIcon>
                                <Box style={{ minWidth: 0 }}>
                                    <Text fz={20} fw={800} lh={1.15}>{med.name}</Text>
                                    <Text fz="sm" c="dimmed">{[med.strength, med.dose && `Dose ${med.dose}`].filter(Boolean).join(' · ') || '—'}</Text>
                                    <Group gap={6} mt={8} wrap="wrap">
                                        {med.controlled && <Badge color="grape" variant="light" radius="sm" leftSection={<IconShieldLock size={12} />}>Controlled{med.cd_schedule ? ` · ${med.cd_schedule}` : ''}</Badge>}
                                        {med.prn
                                            ? <Badge color="violet" variant="light" radius="sm" leftSection={<IconBolt size={12} />}>PRN (as needed)</Badge>
                                            : <Badge color="indigo" variant="light" radius="sm" leftSection={<IconClock size={12} />}>Scheduled</Badge>}
                                    </Group>
                                </Box>
                            </Group>
                            {med.instruction && (
                                <Group gap={8} wrap="nowrap" align="flex-start" mt="md" p="sm" style={{ background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-7))', borderRadius: 12 }}>
                                    <IconInfoCircle size={16} color={ACCENT} style={{ marginTop: 2, flexShrink: 0 }} />
                                    <Text fz="sm" c="dimmed">{med.instruction}</Text>
                                </Group>
                            )}
                        </Box>

                        {r && (
                            <Box style={{ ...card, padding: 16 }}>
                                <Text style={heading} mb={8}>Resident</Text>
                                <Anchor component={Link} href={`/frontend2/residents/${r.id}`} underline="never">
                                    <Group gap="sm" wrap="nowrap">
                                        <Avatar src={r.photo || undefined} color={avatarColor(r.name ?? '')} radius="xl" size={44}>{initials(r.name ?? '')}</Avatar>
                                        <Box style={{ minWidth: 0 }}>
                                            <Text fz="sm" fw={700} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" truncate>{r.name}</Text>
                                            <Group gap={4} wrap="nowrap"><IconBedFilled size={12} color="var(--mantine-color-gray-5)" /><Text fz="xs" c="dimmed">Room {r.room || '—'}</Text></Group>
                                        </Box>
                                    </Group>
                                </Anchor>
                            </Box>
                        )}
                    </Stack>

                    {/* Middle — prescription details + stock */}
                    <Stack gap={18} style={{ flex: '1 1 280px', minWidth: 0 }}>
                        <Box style={{ ...card, padding: 20 }}>
                            <Text style={heading} mb={4}>Prescription</Text>
                            <InfoRow icon={IconRoute2} label="Route" value={med.route} />
                            <InfoRow icon={IconPill} label="Strength" value={med.strength} />
                            <InfoRow icon={IconPill} label="Dose" value={med.dose} />
                            <InfoRow icon={IconCalendar} label="Expiry" value={med.expiry_date} last />
                            <Box mt="sm">
                                <Text fz="xs" c="dimmed" fw={600} tt="uppercase" mb={6} style={{ letterSpacing: 0.5 }}>Times</Text>
                                {med.prn
                                    ? <Badge color="violet" variant="light" radius="sm">As needed (PRN)</Badge>
                                    : (med.times && med.times.length
                                        ? <Group gap={6}>{med.times.map((t) => <Badge key={t} color="gray" variant="light" radius="sm" leftSection={<IconClock size={11} />}>{t}</Badge>)}</Group>
                                        : <Text fz="sm" c="dimmed">No scheduled times.</Text>)}
                            </Box>
                        </Box>

                        <Box style={{ ...card, padding: 20 }}>
                            <Group justify="space-between" align="center" mb={8}>
                                <Text style={heading}>Stock</Text>
                                {med.low && <Badge color="red" variant="light" radius="sm">Low</Badge>}
                            </Group>
                            <Group align="flex-end" gap={6} mb={6}>
                                <Text fz={34} fw={800} lh={1}>{med.stock ?? '—'}</Text>
                                <Text fz="sm" c="dimmed" mb={4}>{med.unit || 'units'}</Text>
                            </Group>
                            <Progress value={stockPct} color={med.low ? 'red' : 'teal'} radius="xl" size="sm" />
                            <Text fz="xs" c="dimmed" mt={6}>Reorder level: {med.reorder_level ?? '—'}</Text>
                        </Box>
                    </Stack>

                    {/* Right — recent administrations */}
                    <Box style={{ ...card, padding: 20, flex: '1 1 300px', minWidth: 0 }}>
                        <Text style={heading} mb={8}>Recent administrations</Text>
                        {administrations.length === 0
                            ? <Text fz="sm" c="dimmed" py="md">No administrations recorded yet.</Text>
                            : (
                                <Stack gap={0}>
                                    {administrations.map((a, i) => {
                                        const tone = codeTone(a.code);
                                        return (
                                            <Group key={a.id ?? i} gap="sm" wrap="nowrap" align="center" py={10}
                                                style={{ borderTop: i ? '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' : 'none' }}>
                                                <ThemeIcon variant="light" color={tone.c} size={32} radius="xl"><tone.Icon size={16} /></ThemeIcon>
                                                <Box style={{ flex: 1, minWidth: 0 }}>
                                                    <Text fz="sm" fw={600} truncate>{CODE_LABELS[a.code] ?? a.code ?? '—'}</Text>
                                                    <Text fz="xs" c="dimmed" truncate>{a.by ?? '—'}</Text>
                                                </Box>
                                                <Box style={{ flexShrink: 0, textAlign: 'right' }}>
                                                    <Text fz="xs" fw={600}>{a.slot || '—'}</Text>
                                                    <Text fz={10} c="dimmed">{a.date}</Text>
                                                </Box>
                                            </Group>
                                        );
                                    })}
                                </Stack>
                            )}
                    </Box>
                </Box>
            </Box>
        </AppShell>
    );
}
