import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import {
    Box, Group, Text, Badge, TextInput, ThemeIcon, SegmentedControl, ScrollArea,
} from '@mantine/core';
import { IconSearch, IconPill, IconShieldLock, IconClockHour4 } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#8493A8, #6C7C93)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';

/** One prescription row. */
function MedRow({ m }) {
    return (
        <Box style={{ borderTop: `1px solid ${HAIR}`, padding: '12px 14px' }}>
            <Group gap="sm" wrap="nowrap" align="flex-start">
                <ThemeIcon variant="light" color={m.controlled ? 'grape' : 'teal'} size={34} radius="md" style={{ flexShrink: 0 }}>
                    <IconPill size={17} />
                </ThemeIcon>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz={14} fw={700} c={TXT} truncate>{m.name}</Text>
                        {m.controlled && <Badge size="xs" variant="light" color="grape" radius="sm" leftSection={<IconShieldLock size={9} />}>CD{m.cd_schedule ? ` ${m.cd_schedule}` : ''}</Badge>}
                        {m.prn && <Badge size="xs" variant="light" color="violet" radius="sm">PRN</Badge>}
                    </Group>
                    <Text fz={12.5} c={MUTED} mt={1}>
                        {[m.dose && `Dose ${m.dose}`, m.form, m.route].filter(Boolean).join(' · ') || m.strength || '—'}
                    </Text>
                    <Text fz={12} c={FAINT} mt={2}>{m.resident ?? 'Unassigned'}</Text>
                </Box>
                <Box ta="right" style={{ flexShrink: 0 }}>
                    {m.stock != null && (
                        <Text fz={12.5} fw={700} c={m.low_stock ? ORANGE : MUTED} style={{ fontVariantNumeric: 'tabular-nums' }}>
                            {m.stock}{m.unit ? ` ${m.unit}` : ''}
                        </Text>
                    )}
                    {m.low_stock && <Text fz={10.5} c={ORANGE} fw={600}>Low</Text>}
                </Box>
            </Group>
        </Box>
    );
}

export default function Medications({ meds = [], home }) {
    const [q, setQ] = useState('');
    const [filter, setFilter] = useState('all'); // all | prn | cd | low

    const shown = useMemo(() => {
        const needle = q.trim().toLowerCase();
        return meds.filter((m) => {
            if (filter === 'prn' && !m.prn) return false;
            if (filter === 'cd' && !m.controlled) return false;
            if (filter === 'low' && !m.low_stock) return false;
            if (!needle) return true;
            return `${m.name} ${m.resident ?? ''}`.toLowerCase().includes(needle);
        });
    }, [meds, q, filter]);

    const counts = useMemo(() => ({
        all: meds.length,
        prn: meds.filter((m) => m.prn).length,
        cd: meds.filter((m) => m.controlled).length,
        low: meds.filter((m) => m.low_stock).length,
    }), [meds]);

    return (
        <AppShell title="Medications" section="Medication 2">
            <Head title="Medications — Medication 2" />
            <Box maw={760} mx="auto">
                <Box mb="md">
                    <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Medications</Text>
                    <Text fz={13} c={MUTED}>{home ? `${home} · ` : ''}{meds.length} active prescription{meds.length === 1 ? '' : 's'}</Text>
                </Box>

                <Group justify="space-between" mb="md" wrap="wrap" gap="sm">
                    <TextInput
                        flex={1} miw={200} radius="xl" size="sm" placeholder="Search medicine or resident"
                        leftSection={<IconSearch size={15} />} value={q} onChange={(e) => setQ(e.currentTarget.value)}
                    />
                    <SegmentedControl
                        size="xs" radius="xl" value={filter} onChange={setFilter}
                        data={[
                            { label: `All ${counts.all}`, value: 'all' },
                            { label: `PRN ${counts.prn}`, value: 'prn' },
                            { label: `CD ${counts.cd}`, value: 'cd' },
                            { label: `Low ${counts.low}`, value: 'low' },
                        ]}
                    />
                </Group>

                <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
                    {shown.length === 0 ? (
                        <Box py={54} ta="center">
                            <ThemeIcon variant="light" color="gray" size={46} radius="xl" mx="auto" mb="sm"><IconClockHour4 size={22} /></ThemeIcon>
                            <Text fz="sm" fw={600} c={TXT}>Nothing matches</Text>
                            <Text fz="xs" c={MUTED}>Try a different search or filter.</Text>
                        </Box>
                    ) : (
                        <ScrollArea.Autosize mah={620} type="hover">
                            {shown.map((m) => <MedRow key={m.id} m={m} />)}
                        </ScrollArea.Autosize>
                    )}
                </Box>
            </Box>
        </AppShell>
    );
}
