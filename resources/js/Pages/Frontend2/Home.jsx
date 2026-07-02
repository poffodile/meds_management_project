import { Head, Link } from '@inertiajs/react';
import { Box, Group, Text, SimpleGrid, ThemeIcon } from '@mantine/core';
import { IconUsers, IconPill, IconBolt, IconShieldLock, IconChevronRight } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const ACCENT = '#4C6FFF';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};

function StatCard({ icon: Icon, label, value, color }) {
    return (
        <Box style={{ ...card, padding: 18 }}>
            <Group gap="sm" wrap="nowrap">
                <ThemeIcon variant="light" color={color} size={44} radius="md"><Icon size={24} stroke={1.7} /></ThemeIcon>
                <Box>
                    <Text fz={28} fw={800} lh={1}>{value}</Text>
                    <Text fz="xs" c="dimmed" mt={2}>{label}</Text>
                </Box>
            </Group>
        </Box>
    );
}

function EntryCard({ icon: Icon, title, desc, href, color }) {
    return (
        <Box component={Link} href={href} style={{ ...card, padding: 22, textDecoration: 'none', display: 'block', transition: 'box-shadow .15s, transform .15s' }}
            onMouseEnter={(e) => { e.currentTarget.style.boxShadow = '0 10px 28px rgba(23,37,84,0.12)'; e.currentTarget.style.transform = 'translateY(-2px)'; }}
            onMouseLeave={(e) => { e.currentTarget.style.boxShadow = card.boxShadow; e.currentTarget.style.transform = 'none'; }}>
            <Group justify="space-between" align="flex-start" wrap="nowrap">
                <ThemeIcon variant="light" color={color} size={48} radius="md"><Icon size={26} stroke={1.7} /></ThemeIcon>
                <ThemeIcon variant="light" color="gray" radius="xl" size={26}><IconChevronRight size={16} /></ThemeIcon>
            </Group>
            <Text fw={800} fz="lg" mt="md">{title}</Text>
            <Text c="dimmed" fz="sm">{desc}</Text>
        </Box>
    );
}

export default function Home({ stats = {} }) {
    return (
        <AppShell title="Dashboard">
            <Head title="CareOne · Dashboard" />
            <Box>
                <Text fz={26} fw={800} c="light-dark(var(--mantine-color-gray-9), var(--mantine-color-gray-1))" lh={1.15}>Welcome back</Text>
                <Text c="dimmed" fz="sm" mb="lg">Residents and medication at a glance.</Text>

                <SimpleGrid cols={{ base: 2, md: 4 }} spacing="md" mb="lg">
                    <StatCard icon={IconUsers} label="Residents" value={stats.residents ?? 0} color="indigo" />
                    <StatCard icon={IconPill} label="Active meds" value={stats.medications ?? 0} color="blue" />
                    <StatCard icon={IconBolt} label="PRN meds" value={stats.prn ?? 0} color="violet" />
                    <StatCard icon={IconShieldLock} label="Controlled" value={stats.controlled ?? 0} color="grape" />
                </SimpleGrid>

                <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="md">
                    <EntryCard icon={IconUsers} title="Residents" desc="Browse profiles, care details and medications." href="/frontend2/residents" color="indigo" />
                    <EntryCard icon={IconPill} title="Medications" desc="Every active prescription across the home." href="/frontend2/medications" color="blue" />
                </SimpleGrid>
            </Box>
        </AppShell>
    );
}
