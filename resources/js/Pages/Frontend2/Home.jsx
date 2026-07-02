import { Head } from '@inertiajs/react';
import { Box, Title, Text, Group, Badge, ThemeIcon } from '@mantine/core';
import { IconLayoutDashboard, IconSparkles } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

// Frontend2 landing page — a placeholder inside the second shell (its own sidebar).
// The real UI will be built here from the picture the owner provides.
export default function Home() {
    return (
        <AppShell>
            <Head title="Frontend 2" />
            <Box>
                <Group gap="sm" mb="xs">
                    <ThemeIcon variant="light" color="brandTeal" size={40} radius="md"><IconLayoutDashboard size={22} stroke={1.7} /></ThemeIcon>
                    <Box>
                        <Title order={2} lh={1.2}>Frontend 2</Title>
                        <Text c="dimmed" size="sm">A separate app shell with its own sidebar.</Text>
                    </Box>
                    <Badge variant="light" color="brandTeal" radius="sm" ml="sm">Scaffold</Badge>
                </Group>

                <Box mt="lg" p="xl" style={{
                    borderRadius: 18,
                    border: '1px dashed light-dark(var(--mantine-color-gray-3), var(--mantine-color-dark-4))',
                    background: 'light-dark(var(--mantine-color-gray-0), var(--mantine-color-dark-6))',
                }}>
                    <Group gap="sm" mb="xs">
                        <IconSparkles size={18} color="var(--mantine-color-brandTeal-6)" />
                        <Text fw={600}>Ready for your design</Text>
                    </Group>
                    <Text size="sm" c="dimmed">
                        This page lives in the new <b>frontend2</b> shell — separate sidebar, separate nav, same brand
                        header/footer. Send the picture and I'll build the sidebar and content here.
                    </Text>
                </Box>
            </Box>
        </AppShell>
    );
}
