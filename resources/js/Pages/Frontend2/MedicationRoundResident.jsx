import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useMediaQuery } from '@mantine/hooks';
import { Box, Group, Stack, Text, Badge, Avatar, Button, ThemeIcon } from '@mantine/core';
import { IconArrowLeft, IconAlertTriangle, IconClockHour4, IconUser } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { initials } from '@frontend/lib/avatarColor';
import { THEME, AdminModal, MedLineV2, metrics, statusOf, cleanAllergies, fmtDate } from './MedicationRoundV2';

const ENDPOINT = '/frontend2/medication-round-v2';
const { ACCENT, INK, TXT, CARD_BG, SOFT, MUTED, GIVEN_D, OVERDUE_D, JAKARTA, ROUND_ICONS, card } = THEME;

const ageFromDob = (dob) => {
    const t = Date.parse(dob);
    if (Number.isNaN(t)) return null;
    return Math.floor((Date.now() - t) / 3.15576e10);
};

function InfoChip({ label, value }) {
    if (!value) return null;
    return (
        <Box style={{ padding: '7px 12px', borderRadius: 10, background: SOFT, minWidth: 0 }}>
            <Text fz={10} fw={700} c={MUTED} tt="uppercase" style={{ letterSpacing: 0.5 }}>{label}</Text>
            <Text fz={13} fw={700} c={TXT} truncate>{value}</Text>
        </Box>
    );
}

export default function MedicationRoundResident({ clientId, rounds = [], grid = {}, date, round, closures = {}, home }) {
    const isSm = useMediaQuery('(max-width: 576px)');
    const p = usePage().props;
    const isManager = p?.auth?.user?.role === 'manager';
    const adminBy = `${p?.auth?.user?.name ?? 'User'} · ${isManager ? 'Care Manager' : 'Carer'}`;
    const [modalCtx, setModalCtx] = useState(null);

    // Client demographics live on any round's resident object; grab the first match.
    let client = null;
    for (const rk of Object.keys(grid)) {
        const found = (grid[rk] ?? []).find((x) => x.client_id === clientId);
        if (found) { client = found; break; }
    }

    const back = () => router.get(ENDPOINT, { date, round: round?.key }, { preserveScroll: true });

    if (!client) {
        return (
            <AppShell title="Medication administration">
                <Head title="Medication administration" />
                <Box p="md">
                    <Button variant="subtle" leftSection={<IconArrowLeft size={16} />} onClick={back}>Back to round</Button>
                    <Text mt="md" c="dimmed">Resident not found for this round.</Text>
                </Box>
            </AppShell>
        );
    }

    // Meds grouped per round (only rounds where this client has meds).
    const sections = rounds
        .map((rd) => {
            const res = (grid[rd.key] ?? []).find((x) => x.client_id === clientId);
            return { rd, meds: res?.rows ?? [], locked: Boolean(closures?.[rd.key]) };
        })
        .filter((s) => s.meds.length > 0);

    const allergies = cleanAllergies(client.allergies);
    const age = client.dob ? ageFromDob(client.dob) : null;
    const currentPath = `${ENDPOINT}/resident/${clientId}?date=${date}&round=${round?.key ?? ''}`;
    const onAct = (row, outcome) => setModalCtx({ row, residentName: client.name, residentRoom: client.room, outcome });

    return (
        <AppShell title="Medication administration">
            <Head title={`${client.name} · Medication`}>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            </Head>
            <Box px={{ base: 0, sm: 10 }} pb={14} style={{ fontFamily: JAKARTA, '--mantine-font-family': JAKARTA, color: INK }}>
                <Button variant="subtle" color="gray" leftSection={<IconArrowLeft size={16} />} onClick={back} style={{ fontFamily: JAKARTA }} mb="sm">
                    Back to {round?.label ?? 'round'} round
                </Button>

                {/* Client info */}
                <Box style={{ ...card, padding: isSm ? '18px 16px' : '24px 26px' }}>
                    <Group align="flex-start" wrap="wrap" gap="lg">
                        <Avatar src={client.photo || undefined} radius="50%" size={72} color="blue"
                            style={{ background: '#dce4ef', color: ACCENT, fontWeight: 800, flexShrink: 0 }}>{initials(client.name ?? '')}</Avatar>
                        <Box style={{ flex: 1, minWidth: 220 }}>
                            <Group gap={9} wrap="wrap" align="center">
                                <Text fz={22} fw={800} c={TXT}>{client.name}</Text>
                                {allergies.length > 0 && (
                                    <Badge variant="light" color="red" radius="sm" leftSection={<IconAlertTriangle size={12} />}>
                                        {allergies.length} {allergies.length === 1 ? 'allergy' : 'allergies'}
                                    </Badge>
                                )}
                                <Button component={Link} href={`/frontend2/residents/${clientId}`} size="compact-sm" radius="xl"
                                    leftSection={<IconUser size={14} />} style={{ fontFamily: JAKARTA, background: '#3A7CA5', color: '#fff', boxShadow: '0 4px 10px rgba(58,124,165,0.22)' }}>View profile</Button>
                            </Group>
                            <Text fz={13} c={MUTED} mb="md">{[client.room && `Room ${client.room}`, fmtDate(date)].filter(Boolean).join(' · ')}</Text>
                            <Group gap={10} wrap="wrap">
                                <InfoChip label="Age" value={age ? `${age} yrs` : null} />
                                <InfoChip label="Gender" value={client.gender} />
                                <InfoChip label="NHS no." value={client.nhs} />
                                <InfoChip label="Weight" value={client.weight} />
                                <InfoChip label="Mobility" value={client.mobility} />
                                <InfoChip label="Diet" value={client.diet} />
                            </Group>
                            {allergies.length > 0 && (
                                <Group gap={7} wrap="wrap" mt="md">
                                    <Text fz={11} fw={700} c={MUTED} tt="uppercase" style={{ letterSpacing: 0.5 }}>Allergies</Text>
                                    {allergies.map((a, i) => <Badge key={i} variant="light" color="red" radius="sm">{a}</Badge>)}
                                </Group>
                            )}
                            {(client.risk_flags ?? []).length > 0 && (
                                <Group gap={7} wrap="wrap" mt={8}>
                                    <Text fz={11} fw={700} c={MUTED} tt="uppercase" style={{ letterSpacing: 0.5 }}>Risk</Text>
                                    {client.risk_flags.map((r, i) => <Badge key={i} variant="light" color="orange" radius="sm">{r}</Badge>)}
                                </Group>
                            )}
                        </Box>
                    </Group>
                </Box>

                {/* Meds per round */}
                <Stack gap="lg" mt="lg">
                    {sections.length === 0
                        ? <Box style={{ ...card, padding: 24 }}><Text c="dimmed">No medications scheduled for this resident today.</Text></Box>
                        : sections.map(({ rd, meds, locked }) => {
                            const res = (grid[rd.key] ?? []).find((x) => x.client_id === clientId);
                            const m = metrics(res);
                            const st = statusOf(m);
                            const RIcon = ROUND_ICONS[rd.key] ?? IconClockHour4;
                            const isCurrent = rd.key === round?.key;
                            return (
                                <Box key={rd.key} style={{ ...card, padding: isSm ? '16px 16px' : '20px 24px', border: isCurrent ? `1.5px solid ${ACCENT}` : card.border }}>
                                    <Group justify="space-between" align="center" wrap="wrap" gap="sm" mb={6}>
                                        <Group gap={11} wrap="nowrap">
                                            <ThemeIcon variant="light" color="blue" size={38} radius={11} style={{ background: 'rgba(58,124,165,0.14)', color: ACCENT }}><RIcon size={20} /></ThemeIcon>
                                            <Box>
                                                <Group gap={8} wrap="nowrap">
                                                    <Text fz={16} fw={800} c={TXT}>{rd.label} round</Text>
                                                    {isCurrent && <Badge radius="sm" style={{ background: ACCENT, color: '#fff' }}>Now</Badge>}
                                                    {locked && <Badge radius="sm" variant="light" color="gray">Ended</Badge>}
                                                </Group>
                                                <Text fz={12} c={MUTED}>{rd.window}</Text>
                                            </Box>
                                        </Group>
                                        <Group gap={7} wrap="nowrap">
                                            <Box style={{ width: 8, height: 8, borderRadius: '50%', background: st.color }} />
                                            <Text fz={12.5} fw={700} style={{ color: st.color }}>{m.done}/{m.total} · {st.label}</Text>
                                        </Group>
                                    </Group>
                                    {meds.map((r, i) => <MedLineV2 key={i} row={r} locked={locked} onAct={onAct} isSm={isSm} />)}
                                </Box>
                            );
                        })}
                </Stack>
            </Box>

            {modalCtx && <AdminModal ctx={modalCtx} date={date} adminBy={adminBy} endpoint={ENDPOINT} redirectTo={currentPath} onClose={() => setModalCtx(null)} />}
        </AppShell>
    );
}
