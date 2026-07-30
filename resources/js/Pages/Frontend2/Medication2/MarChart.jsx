import { Head, router } from '@inertiajs/react';
import { Box, Group, Text, Badge, Select, ActionIcon, ThemeIcon, Tooltip } from '@mantine/core';
import {
    IconChevronLeft, IconChevronRight, IconPill, IconTable, IconUser,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#586780, #9BA9BD)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';
const TODAY = 'light-dark(#EAF6F4, #102A28)';

const ENDPOINT = '/frontend2/medication-2/mar-chart';

// The MAR codes, their letter, colour and meaning — the chart's legend.
const CODE = {
    A: { letter: 'A', c: TEAL, label: 'Given' },
    S: { letter: 'S', c: ORANGE, label: 'Asleep — not given' },
    R: { letter: 'R', c: RED, label: 'Refused' },
    W: { letter: 'W', c: ORANGE, label: 'Withheld' },
    N: { letter: 'N', c: ORANGE, label: 'Not available' },
    O: { letter: 'O', c: ORANGE, label: 'Omitted' },
};

/** Age in whole years from an ISO date-of-birth. */
function ageFromDob(dob) {
    if (!dob) return null;
    const d = new Date(dob);
    if (Number.isNaN(d.getTime())) return null;
    const now = new Date();
    let a = now.getFullYear() - d.getFullYear();
    const m = now.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a -= 1;
    return a >= 0 && a < 130 ? a : null;
}

/** One administration box in the grid. */
function Cell({ entry }) {
    if (!entry) return <Text fz={13} c={FAINT} ta="center">·</Text>;
    const meta = CODE[entry.code] ?? { letter: entry.code || '?', c: MUTED, label: entry.code };
    return (
        <Tooltip label={`${meta.label}${entry.is_late ? ' (late)' : ''}`} withArrow openDelay={200}>
            <Box style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 24, height: 24, borderRadius: 7, margin: '0 auto', background: `color-mix(in srgb, ${meta.c} 16%, transparent)`, position: 'relative' }}>
                <Text fz={12} fw={800} c={meta.c} style={{ lineHeight: 1 }}>{meta.letter}</Text>
                {entry.is_late && <Box pos="absolute" top={-2} right={-2} w={6} h={6} style={{ background: ORANGE, borderRadius: '50%' }} />}
            </Box>
        </Tooltip>
    );
}

export default function MarChart({ residents = [], resident, meds = [], days = [], weekStart, weekLabel, prevWeek, nextWeek, isThisWeek }) {
    const go = (params) => router.get(ENDPOINT, { client_id: resident?.id, week_start: weekStart, ...params }, { preserveScroll: true, preserveState: true });
    const age = resident ? ageFromDob(resident.dob) : null;

    return (
        <AppShell title="MAR chart" section="Medication 2">
            <Head title="MAR chart — Medication 2" />
            <Box maw={980} mx="auto">
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Group gap="sm" align="center">
                        <ThemeIcon variant="light" color="teal" size={38} radius="md"><IconTable size={20} /></ThemeIcon>
                        <Box>
                            <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>MAR chart</Text>
                            <Text fz={13} c={MUTED}>Medication administration record — the recorded history</Text>
                        </Box>
                    </Group>
                    {residents.length > 0 && (
                        <Select w={240} radius="xl" size="sm" data={residents} value={resident ? String(resident.id) : null}
                            searchable allowDeselect={false} leftSection={<IconUser size={15} />}
                            onChange={(v) => go({ client_id: v })} />
                    )}
                </Group>

                {!resident ? (
                    <Box py={60} ta="center" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16 }}>
                        <ThemeIcon variant="light" color="gray" size={52} radius="xl" mx="auto" mb="sm"><IconUser size={26} /></ThemeIcon>
                        <Text fz="md" fw={700} c={TXT}>No one to show</Text>
                        <Text fz="sm" c={MUTED}>There are no residents in this home yet.</Text>
                    </Box>
                ) : (
                    <>
                        {/* Resident identity + week nav */}
                        <Group justify="space-between" mb="md" wrap="wrap" gap="sm">
                            <Box>
                                <Text fz={16} fw={750} c={TXT}>{resident.name}</Text>
                                <Text fz={12.5} c={MUTED}>
                                    {[age != null && `${age}y`, resident.room && `Room ${resident.room}`, resident.nhs && `NHS ${resident.nhs}`].filter(Boolean).join(' · ') || '—'}
                                </Text>
                            </Box>
                            <Group gap={8} align="center">
                                <ActionIcon variant="default" radius="md" onClick={() => go({ week_start: prevWeek })} aria-label="Previous week"><IconChevronLeft size={17} /></ActionIcon>
                                <Text fz={13} fw={700} c={TXT} style={{ minWidth: 150, textAlign: 'center' }}>{weekLabel}</Text>
                                <ActionIcon variant="default" radius="md" onClick={() => go({ week_start: nextWeek })} aria-label="Next week" disabled={isThisWeek}><IconChevronRight size={17} /></ActionIcon>
                            </Group>
                        </Group>

                        {meds.length === 0 ? (
                            <Box py={54} ta="center" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16 }}>
                                <ThemeIcon variant="light" color="gray" size={46} radius="xl" mx="auto" mb="sm"><IconPill size={22} /></ThemeIcon>
                                <Text fz="sm" fw={700} c={TXT}>No active medicines</Text>
                                <Text fz="xs" c={MUTED}>This resident has no active prescriptions.</Text>
                            </Box>
                        ) : (
                            <Box style={{ overflowX: 'auto', border: `1px solid ${HAIR}`, borderRadius: 16, background: SURFACE }}>
                                <Box style={{ minWidth: 720 }}>
                                    {/* Header row: days of the week */}
                                    <Group gap={0} wrap="nowrap" style={{ borderBottom: `1px solid ${HAIR}`, background: SOFT }}>
                                        <Box style={{ flex: '0 0 260px', padding: '10px 14px' }}>
                                            <Text fz={11} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.4 }}>Medicine</Text>
                                        </Box>
                                        <Box style={{ flex: '0 0 60px', padding: '10px 6px', textAlign: 'center' }}>
                                            <Text fz={11} fw={700} c={FAINT} tt="uppercase">Time</Text>
                                        </Box>
                                        {days.map((d) => (
                                            <Box key={d.date} style={{ flex: 1, padding: '8px 4px', textAlign: 'center', background: d.today ? TODAY : 'transparent' }}>
                                                <Text fz={11} fw={700} c={d.today ? TEAL : MUTED}>{d.dow}</Text>
                                                <Text fz={13} fw={800} c={d.today ? TEAL : TXT} style={{ fontVariantNumeric: 'tabular-nums' }}>{d.day}</Text>
                                            </Box>
                                        ))}
                                    </Group>

                                    {/* One block per medicine */}
                                    {meds.map((m) => (
                                        <Box key={m.mar_sheet_id} style={{ borderBottom: `1px solid ${HAIR}` }}>
                                            {m.as_required ? (
                                                <Group gap={0} wrap="nowrap">
                                                    <Box style={{ flex: '0 0 260px', padding: '10px 14px' }}>
                                                        <MedName m={m} />
                                                    </Box>
                                                    <Box style={{ flex: '0 0 60px', textAlign: 'center' }}>
                                                        <Badge size="xs" variant="light" color="grape" radius="sm">PRN</Badge>
                                                    </Box>
                                                    {days.map((d) => (
                                                        <Box key={d.date} style={{ flex: 1, padding: '8px 4px', textAlign: 'center', background: d.today ? TODAY : 'transparent' }}>
                                                            {m.prn_by_day?.[d.date] > 0
                                                                ? <Tooltip label={`Given ${m.prn_by_day[d.date]}× as needed`} withArrow><Text fz={12} fw={700} c={TEAL}>{m.prn_by_day[d.date]}×</Text></Tooltip>
                                                                : <Text fz={13} c={FAINT}>·</Text>}
                                                        </Box>
                                                    ))}
                                                </Group>
                                            ) : (
                                                (m.slots.length ? m.slots : ['—']).map((slot, si) => (
                                                    <Group key={slot} gap={0} wrap="nowrap" style={si > 0 ? { borderTop: `1px dashed ${HAIR}` } : undefined}>
                                                        <Box style={{ flex: '0 0 260px', padding: '10px 14px' }}>
                                                            {si === 0 ? <MedName m={m} /> : <Box />}
                                                        </Box>
                                                        <Box style={{ flex: '0 0 60px', textAlign: 'center', padding: '10px 6px' }}>
                                                            <Text fz={12} fw={700} c={MUTED} style={{ fontVariantNumeric: 'tabular-nums' }}>{slot}</Text>
                                                        </Box>
                                                        {days.map((d) => (
                                                            <Box key={d.date} style={{ flex: 1, padding: '8px 4px', textAlign: 'center', background: d.today ? TODAY : 'transparent' }}>
                                                                <Cell entry={m.grid?.[slot]?.[d.date]} />
                                                            </Box>
                                                        ))}
                                                    </Group>
                                                ))
                                            )}
                                        </Box>
                                    ))}
                                </Box>
                            </Box>
                        )}

                        {/* Legend + honest note */}
                        <Group justify="space-between" mt="md" wrap="wrap" gap="sm">
                            <Group gap={12} wrap="wrap">
                                {Object.values(CODE).map((c) => (
                                    <Group key={c.letter} gap={5} wrap="nowrap">
                                        <Box style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', width: 18, height: 18, borderRadius: 5, background: `color-mix(in srgb, ${c.c} 16%, transparent)` }}>
                                            <Text fz={10} fw={800} c={c.c}>{c.letter}</Text>
                                        </Box>
                                        <Text fz={11.5} c={MUTED}>{c.label}</Text>
                                    </Group>
                                ))}
                            </Group>
                        </Group>
                        <Text fz={11.5} c={FAINT} mt="sm">
                            This is the record. Doses are recorded on the <b style={{ color: 'var(--mantine-color-text)' }}>Medication round</b>; a blank box means nothing has been recorded for that time yet.
                        </Text>
                    </>
                )}
            </Box>
        </AppShell>
    );
}

/** Medicine name + dose/route line, with a CD marker. */
function MedName({ m }) {
    return (
        <Box>
            <Group gap={6} wrap="nowrap">
                <Text fz={13} fw={650} c={TXT} truncate style={{ letterSpacing: '-0.01em' }}>{m.medication_name}</Text>
                {m.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
            </Group>
            <Text fz={11.5} c={MUTED}>{[m.dose, m.route].filter(Boolean).join(' · ') || '—'}</Text>
        </Box>
    );
}
