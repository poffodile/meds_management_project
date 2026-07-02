import { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Box, Group, Text, TextInput, Badge, ThemeIcon, SegmentedControl } from '@mantine/core';
import { IconSearch, IconPill, IconShieldLock, IconChevronRight } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};

function MedRow({ m, last }) {
    return (
        <Group component={Link} href={`/frontend2/medications/${m.id}`} gap="sm" wrap="nowrap" align="center" px="md" py={12}
            style={{ textDecoration: 'none', color: 'inherit', cursor: 'pointer', borderTop: last ? undefined : '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}
            onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#FAFBFF, var(--mantine-color-dark-5))'; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
            <ThemeIcon variant="light" color={m.controlled ? 'grape' : 'indigo'} size={38} radius="md">
                {m.controlled ? <IconShieldLock size={19} /> : <IconPill size={19} />}
            </ThemeIcon>
            <Box style={{ flex: '2 1 220px', minWidth: 0 }}>
                <Group gap={6} wrap="nowrap">
                    <Text fz="sm" fw={700} truncate>{m.name}</Text>
                    {m.controlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD</Badge>}
                    {m.prn && <Badge size="xs" color="violet" variant="light" radius="sm">PRN</Badge>}
                </Group>
                <Text fz="xs" c="dimmed" truncate>{[m.strength, m.dose && `Dose ${m.dose}`, m.route].filter(Boolean).join(' · ') || '—'}</Text>
            </Box>
            <Text fz="sm" c="dimmed" style={{ flex: '1 1 140px', minWidth: 0 }} visibleFrom="sm" truncate>{m.resident || '—'}</Text>
            <Box style={{ flexShrink: 0, textAlign: 'right', width: 70 }}>
                <Text fz="sm" fw={700} c={m.stock != null && m.stock <= 5 ? 'red.6' : undefined}>{m.stock ?? '—'}</Text>
                <Text fz={10} c="dimmed">in stock</Text>
            </Box>
            <ThemeIcon variant="subtle" color="gray" radius="xl" size={24} style={{ flexShrink: 0 }}><IconChevronRight size={16} /></ThemeIcon>
        </Group>
    );
}

export default function Medications({ meds = [] }) {
    const [query, setQuery] = useState('');
    const [filter, setFilter] = useState('all');

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        return meds.filter((m) => {
            if (filter === 'prn' && !m.prn) return false;
            if (filter === 'controlled' && !m.controlled) return false;
            if (q && !`${m.name} ${m.resident ?? ''}`.toLowerCase().includes(q)) return false;
            return true;
        });
    }, [meds, query, filter]);

    return (
        <AppShell title="Medications">
            <Head title="Medications" />
            <Box>
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Text fz={26} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.15}>Medications</Text>
                        <Text c="dimmed" fz="sm">{meds.length} active prescription{meds.length === 1 ? '' : 's'} across the home.</Text>
                    </Box>
                    <Group gap="sm" wrap="wrap">
                        <SegmentedControl radius="xl" value={filter} onChange={setFilter}
                            data={[{ label: 'All', value: 'all' }, { label: 'PRN', value: 'prn' }, { label: 'Controlled', value: 'controlled' }]} />
                        <TextInput placeholder="Search meds or resident…" leftSection={<IconSearch size={16} />} value={query}
                            onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" w={{ base: '100%', sm: 260 }} />
                    </Group>
                </Group>

                <Box style={card}>
                    {filtered.length === 0
                        ? <Text fz="sm" c="dimmed" ta="center" py={48}>No medications match.</Text>
                        : filtered.map((m, i) => <MedRow key={m.id} m={m} last={i === 0} />)}
                </Box>
            </Box>
        </AppShell>
    );
}
