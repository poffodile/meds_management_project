import { Head } from '@inertiajs/react';
import { Box, Text, Group, Badge, ThemeIcon } from '@mantine/core';
import { IconTools } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

// Shared placeholder used by the "Medication 2" pages — a titled empty state
// inside the frontend2 shell, ready to be replaced with the real UI later.
export default function PlaceholderPage({ title, blurb }) {
    return (
        <AppShell title={title}>
            <Head title={title} />
            <Box>
                <Group gap="sm" mb="xs" align="center">
                    <Text fz={26} fw={800} lh={1.15}>{title}</Text>
                    <Badge variant="light" color="gray" radius="sm">Placeholder</Badge>
                </Group>
                <Box mt="md" p={40} style={{
                    borderRadius: 18,
                    border: '1px dashed light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))',
                    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
                    textAlign: 'center',
                }}>
                    <ThemeIcon variant="light" color="indigo" size={54} radius="xl" mx="auto" mb="sm"><IconTools size={28} stroke={1.7} /></ThemeIcon>
                    <Text fw={700}>{title} — coming soon</Text>
                    <Text c="dimmed" fz="sm" maw={440} mx="auto" mt={4}>
                        {blurb ?? 'This is a placeholder in the Medication 2 area. The real page will be built here.'}
                    </Text>
                </Box>
            </Box>
        </AppShell>
    );
}
