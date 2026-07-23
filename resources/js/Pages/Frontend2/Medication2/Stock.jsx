import { useState, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import {
    Box, Group, Text, Badge, TextInput, ThemeIcon, SegmentedControl, ScrollArea,
} from '@mantine/core';
import {
    IconSearch, IconBox, IconShieldLock, IconAlertTriangle, IconCalendarX,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#8493A8, #6C7C93)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';

function Stat({ label, value, tone }) {
    return (
        <Box style={{ flex: 1, minWidth: 88, background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 12, padding: '10px 14px' }}>
            <Text fz={22} fw={800} c={tone || TXT} style={{ fontVariantNumeric: 'tabular-nums', letterSpacing: '-0.02em' }}>{value}</Text>
            <Text fz={11.5} fw={600} c={MUTED}>{label}</Text>
        </Box>
    );
}

function StockRow({ m }) {
    const flag = m.expired ? { c: RED, t: 'Expired' } : m.out ? { c: RED, t: 'Out of stock' } : m.low ? { c: ORANGE, t: 'Low' } : m.expiring_soon ? { c: ORANGE, t: 'Expiring soon' } : null;
    return (
        <Box style={{ borderTop: `1px solid ${HAIR}`, padding: '12px 14px' }}>
            <Group gap="sm" wrap="nowrap" align="flex-start">
                <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : 'teal'} size={34} radius="md" style={{ flexShrink: 0 }}>
                    <IconBox size={17} />
                </ThemeIcon>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz={13.5} fw={700} c={TXT} truncate>{m.medication_name}</Text>
                        {m.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm" leftSection={<IconShieldLock size={9} />}>CD{m.cd_schedule ? ` ${m.cd_schedule}` : ''}</Badge>}
                    </Group>
                    <Text fz={12} c={FAINT}>{m.resident ?? 'Unassigned'}{m.expiry_date ? ` · expires ${m.expiry_date}` : ''}</Text>
                    {flag && <Badge size="xs" variant="light" radius="sm" color={m.expired || m.out ? 'red' : 'orange'} mt={3} leftSection={m.expired ? <IconCalendarX size={9} /> : <IconAlertTriangle size={9} />}>{flag.t}</Badge>}
                </Box>
                <Box ta="right" style={{ flexShrink: 0 }}>
                    <Text fz={15} fw={800} c={flag ? flag.c : TXT} style={{ fontVariantNumeric: 'tabular-nums' }}>
                        {m.stock_level ?? '—'}<Text span fz={11} c={FAINT} fw={500}> {m.unit || 'units'}</Text>
                    </Text>
                    {m.reorder_level != null && <Text fz={10.5} c={FAINT}>reorder at {m.reorder_level}</Text>}
                </Box>
            </Group>
        </Box>
    );
}

export default function Stock({ meds = [], stats = {} }) {
    const [q, setQ] = useState('');
    const [filter, setFilter] = useState('all'); // all | attention | cd

    const shown = useMemo(() => {
        const needle = q.trim().toLowerCase();
        return meds
            .map((m) => ({ ...m, out: m.stock_level != null && Number(m.stock_level) === 0 }))
            .filter((m) => {
                if (filter === 'attention' && !(m.low || m.out || m.expired || m.expiring_soon)) return false;
                if (filter === 'cd' && !m.is_controlled) return false;
                if (!needle) return true;
                return `${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(needle);
            })
            // things needing attention first, then by name
            .sort((a, b) => (Number(b.expired || b.out) - Number(a.expired || a.out)) || (Number(b.low) - Number(a.low)) || String(a.medication_name).localeCompare(b.medication_name));
    }, [meds, q, filter]);

    return (
        <AppShell title="Stock" section="Medication 2">
            <Head title="Stock — Medication 2" />
            <Box maw={760} mx="auto">
                <Box mb="md">
                    <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Stock</Text>
                    <Text fz={13} c={MUTED}>{stats.total ?? meds.length} medicine{(stats.total ?? meds.length) === 1 ? '' : 's'} tracked</Text>
                </Box>

                <Group gap="sm" mb="md" wrap="wrap">
                    <Stat label="Low stock" value={stats.low ?? 0} tone={(stats.low ?? 0) > 0 ? ORANGE : TEAL} />
                    <Stat label="Out of stock" value={stats.out_of_stock ?? 0} tone={(stats.out_of_stock ?? 0) > 0 ? RED : TEAL} />
                    <Stat label="Expiring" value={stats.expiring_soon ?? 0} tone={(stats.expiring_soon ?? 0) > 0 ? ORANGE : TEAL} />
                    <Stat label="Expired" value={stats.expired ?? 0} tone={(stats.expired ?? 0) > 0 ? RED : TEAL} />
                </Group>

                <Group justify="space-between" mb="md" wrap="wrap" gap="sm">
                    <TextInput flex={1} miw={200} radius="xl" size="sm" placeholder="Search medicine or resident"
                        leftSection={<IconSearch size={15} />} value={q} onChange={(e) => setQ(e.currentTarget.value)} />
                    <SegmentedControl size="xs" radius="xl" value={filter} onChange={setFilter}
                        data={[{ label: 'All', value: 'all' }, { label: 'Needs attention', value: 'attention' }, { label: 'CD', value: 'cd' }]} />
                </Group>

                <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
                    {shown.length === 0 ? (
                        <Box py={54} ta="center">
                            <ThemeIcon variant="light" color="gray" size={46} radius="xl" mx="auto" mb="sm"><IconBox size={22} /></ThemeIcon>
                            <Text fz="sm" fw={600} c={TXT}>Nothing matches</Text>
                            <Text fz="xs" c={MUTED}>Try a different search or filter.</Text>
                        </Box>
                    ) : (
                        <ScrollArea.Autosize mah={600} type="hover">
                            {shown.map((m) => <StockRow key={m.id} m={m} />)}
                        </ScrollArea.Autosize>
                    )}
                </Box>

                <Text fz={11.5} c={FAINT} mt="sm" ta="center">
                    Read-only overview. Receiving stock, adjustments and reorders are managed on the Stock 2 page.
                </Text>
            </Box>
        </AppShell>
    );
}
