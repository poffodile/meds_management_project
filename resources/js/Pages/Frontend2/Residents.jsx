import { useState, useMemo } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Box, Group, Text, TextInput, Avatar, Badge, SimpleGrid, ThemeIcon } from '@mantine/core';
import { IconSearch, IconBedFilled, IconPill, IconChevronRight, IconUserPlus } from '@tabler/icons-react';
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

function ResidentCard({ r }) {
    const age = ageFromDob(r.dob);
    return (
        <Box component={Link} href={`/frontend2/residents/${r.id}`} style={{ ...card, padding: 16, textDecoration: 'none', display: 'block', transition: 'box-shadow .15s, transform .15s' }}
            onMouseEnter={(e) => { e.currentTarget.style.boxShadow = '0 10px 28px rgba(23,37,84,0.12)'; e.currentTarget.style.transform = 'translateY(-2px)'; }}
            onMouseLeave={(e) => { e.currentTarget.style.boxShadow = card.boxShadow; e.currentTarget.style.transform = 'none'; }}>
            <Group justify="space-between" wrap="nowrap" align="flex-start">
                <Group gap="sm" wrap="nowrap" style={{ minWidth: 0 }}>
                    <Avatar src={r.photo || undefined} color={avatarColor(r.name ?? '')} radius="xl" size={52}>{initials(r.name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fw={700} fz="md" c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" truncate>{r.name}</Text>
                        <Text fz="xs" c="dimmed">{[r.gender, age != null ? `${age} yrs` : null].filter(Boolean).join(' · ') || '—'}</Text>
                    </Box>
                </Group>
                <ThemeIcon variant="light" color="indigo" radius="xl" size={26}><IconChevronRight size={16} /></ThemeIcon>
            </Group>
            <Group gap="xs" mt="md" wrap="wrap">
                <Badge variant="light" color="gray" radius="sm" leftSection={<IconBedFilled size={12} />}>Room {r.room || '—'}</Badge>
                <Badge variant="light" color="indigo" radius="sm" leftSection={<IconPill size={12} />}>{r.med_count} med{r.med_count === 1 ? '' : 's'}</Badge>
                {r.nhs && <Badge variant="light" color="teal" radius="sm">NHS {r.nhs}</Badge>}
            </Group>
        </Box>
    );
}

export default function Residents({ residents = [] }) {
    const [query, setQuery] = useState('');
    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        return q ? residents.filter((r) => `${r.name} ${r.room ?? ''}`.toLowerCase().includes(q)) : residents;
    }, [residents, query]);

    return (
        <AppShell title="Residents">
            <Head title="Residents" />
            <Box>
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Text fz={26} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.15}>Residents</Text>
                        <Text c="dimmed" fz="sm">{residents.length} resident{residents.length === 1 ? '' : 's'} in your care home.</Text>
                    </Box>
                    <TextInput placeholder="Search residents…" leftSection={<IconSearch size={16} />} value={query}
                        onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" w={{ base: '100%', sm: 300 }} />
                </Group>

                {filtered.length === 0
                    ? (
                        <Box style={{ ...card, padding: 48 }} ta="center">
                            <ThemeIcon variant="light" color="indigo" size={48} radius="xl" mx="auto" mb="sm"><IconUserPlus size={26} /></ThemeIcon>
                            <Text fw={600}>No residents found</Text>
                            <Text c="dimmed" fz="sm">Try a different search.</Text>
                        </Box>
                    )
                    : (
                        <SimpleGrid cols={{ base: 1, sm: 2, lg: 3, xl: 4 }} spacing="md">
                            {filtered.map((r) => <ResidentCard key={r.id} r={r} />)}
                        </SimpleGrid>
                    )}
            </Box>
        </AppShell>
    );
}
