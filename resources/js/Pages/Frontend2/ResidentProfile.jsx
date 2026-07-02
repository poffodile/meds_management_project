import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    Box, Group, Stack, Text, Avatar, Badge, Button, Tabs, ThemeIcon, Divider, Anchor,
} from '@mantine/core';
import {
    IconArrowLeft, IconPrinter, IconPencil, IconPhone, IconMail, IconPill, IconShieldLock,
    IconAlertTriangle, IconToolsKitchen2, IconWalk, IconWeight, IconClock, IconFileText, IconNote,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { ageFromDob } from '@frontend/lib/dateUtils';

const ACCENT = '#4C6FFF';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const heading = { fontWeight: 700, fontSize: 16 };

function InfoRow({ label, value, last }) {
    return (
        <Group justify="space-between" wrap="nowrap" align="flex-start" py={9}
            style={{ borderBottom: last ? 'none' : '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
            <Text fz="sm" c="dimmed" style={{ flexShrink: 0 }}>{label}</Text>
            <Text fz="sm" fw={600} ta="right" style={{ minWidth: 0 }}>{value || '—'}</Text>
        </Group>
    );
}

function MedRow({ m, last }) {
    return (
        <Group component={Link} href={`/frontend2/medications/${m.id}`} gap="sm" wrap="nowrap" align="center" py={10}
            style={{ textDecoration: 'none', color: 'inherit', cursor: 'pointer', borderBottom: last ? 'none' : '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
            <ThemeIcon variant="light" color={m.controlled ? 'grape' : 'indigo'} size={34} radius="md">
                {m.controlled ? <IconShieldLock size={18} /> : <IconPill size={18} />}
            </ThemeIcon>
            <Box style={{ flex: 1, minWidth: 0 }}>
                <Group gap={6} wrap="nowrap">
                    <Text fz="sm" fw={600} truncate>{m.name}</Text>
                    {m.controlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD</Badge>}
                </Group>
                <Text fz="xs" c="dimmed" truncate>{[m.strength, m.dose && `Dose ${m.dose}`, m.route].filter(Boolean).join(' · ') || '—'}</Text>
            </Box>
            <Group gap={4} wrap="nowrap" style={{ flexShrink: 0 }}>
                {m.prn
                    ? <Badge size="sm" color="violet" variant="light" radius="sm">PRN</Badge>
                    : (m.times || []).slice(0, 4).map((t) => (
                        <Badge key={t} size="sm" color="gray" variant="light" radius="sm" leftSection={<IconClock size={10} />}>{t}</Badge>
                    ))}
            </Group>
        </Group>
    );
}

export default function ResidentProfile({ resident = {}, meds = [] }) {
    const age = ageFromDob(resident.dob);
    const scheduled = meds.filter((m) => !m.prn);
    const prn = meds.filter((m) => m.prn);
    const [tab, setTab] = useState('all');
    const shown = tab === 'scheduled' ? scheduled : tab === 'prn' ? prn : meds;

    return (
        <AppShell title="Resident profile">
            <Head title={`${resident.name ?? 'Resident'} · Profile`} />
            <Box>
                {/* Action row */}
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Anchor component={Link} href="/frontend2/residents" c="dimmed" fz="sm">
                        <Group gap={4} wrap="nowrap"><IconArrowLeft size={16} /> Residents</Group>
                    </Anchor>
                    <Group gap="sm">
                        <Button variant="default" radius="xl" leftSection={<IconPrinter size={16} />} onClick={() => window.print()}>Print</Button>
                        <Button radius="xl" color="indigo" leftSection={<IconPencil size={16} />}>Edit</Button>
                    </Group>
                </Group>

                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 18, alignItems: 'flex-start' }}>
                    {/* Left — profile + medications */}
                    <Stack gap={18} style={{ flex: '1 1 300px', minWidth: 0 }}>
                        <Box style={{ ...card, padding: 20 }}>
                            <Stack align="center" gap={6}>
                                <Avatar src={resident.photo || undefined} color={avatarColor(resident.name ?? '')} radius="50%" size={104}>{initials(resident.name ?? '')}</Avatar>
                                <Text fz={20} fw={800} ta="center" mt={4}>{resident.name}</Text>
                                <Text fz="xs" c="dimmed">{[resident.gender, age != null ? `${age} yrs` : null].filter(Boolean).join(' · ')}</Text>
                                <Stack gap={4} mt="xs" w="100%">
                                    {resident.phone && <Group gap={8} justify="center" wrap="nowrap"><IconPhone size={14} color={ACCENT} /><Text fz="sm" fw={600} style={{ color: ACCENT }}>{resident.phone}</Text></Group>}
                                    {resident.email && <Group gap={8} justify="center" wrap="nowrap"><IconMail size={14} color="var(--mantine-color-gray-5)" /><Text fz="xs" c="dimmed" truncate>{resident.email}</Text></Group>}
                                </Stack>
                            </Stack>
                        </Box>

                        <Box style={{ ...card, padding: 16 }}>
                            <Group justify="space-between" align="center" mb={6}>
                                <Text style={heading}>Medications</Text>
                                <Badge variant="light" color="indigo" radius="sm">{meds.length}</Badge>
                            </Group>
                            <Tabs value={tab} onChange={setTab} color="indigo" variant="pills" radius="xl">
                                <Tabs.List mb="xs">
                                    <Tabs.Tab value="all" fz="xs">All ({meds.length})</Tabs.Tab>
                                    <Tabs.Tab value="scheduled" fz="xs">Scheduled ({scheduled.length})</Tabs.Tab>
                                    <Tabs.Tab value="prn" fz="xs">PRN ({prn.length})</Tabs.Tab>
                                </Tabs.List>
                            </Tabs>
                            {shown.length === 0
                                ? <Text fz="sm" c="dimmed" py="md" ta="center">No medications in this list.</Text>
                                : <Box>{shown.map((m, i) => <MedRow key={m.id} m={m} last={i === shown.length - 1} />)}</Box>}
                        </Box>
                    </Stack>

                    {/* Middle — general information */}
                    <Box style={{ ...card, padding: 20, flex: '1 1 280px', minWidth: 0 }}>
                        <Text style={heading} mb={4}>General information</Text>
                        <InfoRow label="Date of birth" value={resident.dob} />
                        <InfoRow label="Age" value={age != null ? `${age} years` : null} />
                        <InfoRow label="Gender" value={resident.gender} />
                        <InfoRow label="Room" value={resident.room} />
                        <InfoRow label="NHS number" value={resident.nhs} />
                        <InfoRow label="Address" value={resident.address} />
                        <InfoRow label="Registration date" value={resident.registered} last />
                    </Box>

                    {/* Right — health/anamnesis + records */}
                    <Stack gap={18} style={{ flex: '1 1 280px', minWidth: 0 }}>
                        <Box style={{ ...card, padding: 20 }}>
                            <Text style={heading} mb={8}>Health &amp; care</Text>
                            <Group gap={6} mb={4} wrap="nowrap" align="center">
                                <IconAlertTriangle size={15} color="var(--mantine-color-red-6)" />
                                <Text fz="xs" c="dimmed" fw={600} tt="uppercase" style={{ letterSpacing: 0.5 }}>Allergies</Text>
                            </Group>
                            {resident.allergies && resident.allergies.length
                                ? <Group gap={6} mb="sm">{resident.allergies.map((a) => <Badge key={a} color="red" variant="light" radius="sm">{a}</Badge>)}</Group>
                                : <Text fz="sm" c="dimmed" mb="sm">No known allergies.</Text>}
                            <Divider mb={4} />
                            <InfoRow label={<span>Diet</span>} value={resident.diet} />
                            <InfoRow label="Mobility" value={resident.mobility} />
                            <InfoRow label="Weight" value={resident.weight ? `${resident.weight}${resident.weight_unit ? ' ' + resident.weight_unit : ''}` : null} last />
                        </Box>

                        <Box style={{ ...card, padding: 20 }}>
                            <Text style={heading} mb={8}>Records</Text>
                            <Group gap="sm" wrap="nowrap" py={6}>
                                <ThemeIcon variant="light" color="gray" size={34} radius="md"><IconFileText size={18} /></ThemeIcon>
                                <Box><Text fz="sm" fw={600}>Files</Text><Text fz="xs" c="dimmed">No files uploaded yet.</Text></Box>
                            </Group>
                            <Group gap="sm" wrap="nowrap" py={6}>
                                <ThemeIcon variant="light" color="gray" size={34} radius="md"><IconNote size={18} /></ThemeIcon>
                                <Box><Text fz="sm" fw={600}>Notes</Text><Text fz="xs" c="dimmed">No notes recorded yet.</Text></Box>
                            </Group>
                        </Box>
                    </Stack>
                </Box>
            </Box>
        </AppShell>
    );
}
