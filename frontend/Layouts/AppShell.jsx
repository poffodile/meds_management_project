import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, NavLink, ScrollArea,
    Avatar, Badge, SegmentedControl, ActionIcon, UnstyledButton, Box, Switch,
    Menu, useMantineColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconLayoutDashboard, IconUsers, IconPill, IconBriefcase, IconClipboardText,
    IconReportAnalytics, IconSettings, IconSearch, IconBell, IconChevronRight, IconMoon,
} from '@tabler/icons-react';
import { RoleContext } from '@frontend/lib/role';
import logo from '@frontend/assets/logo.jpg';

// Medication sub-pages (the ones we've built).
const MED_LINKS = [
    { label: 'Medication Round', href: '/medication/medication-round-react' },
    { label: 'Medication Stock', href: '/medication/stock-react' },
    { label: 'Controlled Drugs', href: '/medication/controlled-drugs-react' },
    { label: 'Missed Doses', href: '/medication/missed-doses-react' },
    { label: 'Shift Handover', href: '/medication/shift-handover-react' },
];

// Top-level items. `managerOnly` hides them from the carer view.
const TOP_LINKS = [
    { label: 'Dashboard', icon: IconLayoutDashboard, href: '#' },
    { label: 'Residents', icon: IconUsers, href: '#' },
    { label: 'Staff', icon: IconBriefcase, href: '#', managerOnly: true },
    { label: 'Care Plans', icon: IconClipboardText, href: '#' },
    { label: 'Reports', icon: IconReportAnalytics, href: '#', managerOnly: true },
    { label: 'Settings', icon: IconSettings, href: '#', managerOnly: true },
];

export default function AppShell({ children }) {
    const [opened, { toggle }] = useDisclosure();
    const { props, url } = usePage();
    const realRole = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const [previewRole, setPreviewRole] = useState(realRole);
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();

    const isManager = previewRole === 'manager';
    const onMedication = url.startsWith('/medication/');

    const navItem = (item) => (
        <NavLink
            key={item.label}
            label={item.label}
            leftSection={<item.icon size={20} stroke={1.6} />}
            component={item.href === '#' ? undefined : Link}
            href={item.href === '#' ? undefined : item.href}
            active={item.href !== '#' && url.startsWith(item.href)}
            disabled={item.href === '#'}
        />
    );

    return (
        <RoleContext.Provider value={previewRole}>
            <MantineAppShell
                header={{ height: 64 }}
                navbar={{ width: 264, breakpoint: 'sm', collapsed: { mobile: !opened } }}
                padding="lg"
            >
                {/* ---- Header: clean, with search + notifications ---- */}
                <MantineAppShell.Header>
                    <Group h="100%" px="lg" justify="space-between" wrap="nowrap">
                        <Burger opened={opened} onClick={toggle} hiddenFrom="sm" size="sm" />
                        <Group gap="sm" wrap="nowrap" ml="auto">
                            <Group gap={6} visibleFrom="sm">
                                <Text size="xs" c="dimmed">Preview:</Text>
                                <SegmentedControl
                                    size="xs"
                                    value={previewRole}
                                    onChange={setPreviewRole}
                                    data={[{ label: 'Manager', value: 'manager' }, { label: 'Carer', value: 'carer' }]}
                                />
                            </Group>
                            <ActionIcon variant="default" radius="xl" size="lg"><IconSearch size={18} stroke={1.6} /></ActionIcon>
                            <ActionIcon variant="default" radius="xl" size="lg" pos="relative">
                                <IconBell size={18} stroke={1.6} />
                                <Badge size="xs" circle color="blue" pos="absolute" top={-2} right={-2}>3</Badge>
                            </ActionIcon>
                        </Group>
                    </Group>
                </MantineAppShell.Header>

                {/* ---- Sidebar ---- */}
                <MantineAppShell.Navbar p={0}>
                    <Box style={{ background: '#2778A5' }} px="lg" py="md">
                        <img src={logo} alt="Care One OS" style={{ height: 30, display: 'block' }} />
                    </Box>

                    <MantineAppShell.Section grow component={ScrollArea} px="sm" py="md">
                        {TOP_LINKS.slice(0, 2).map(navItem)}

                        <NavLink
                            label="Medication"
                            leftSection={<IconPill size={20} stroke={1.6} />}
                            defaultOpened={onMedication}
                            childrenOffset={28}
                        >
                            {MED_LINKS.map((m) => (
                                <NavLink key={m.href} component={Link} href={m.href} label={m.label} active={url.startsWith(m.href)} />
                            ))}
                        </NavLink>

                        {TOP_LINKS.slice(2).filter((i) => !i.managerOnly || isManager).map(navItem)}
                    </MantineAppShell.Section>

                    <MantineAppShell.Section p="sm" style={{ borderTop: '1px solid var(--mantine-color-gray-2)' }}>
                        <Menu position="top-start" withArrow width={200}>
                            <Menu.Target>
                                <UnstyledButton style={{ width: '100%', borderRadius: 8, padding: 8 }}>
                                    <Group gap="sm" wrap="nowrap">
                                        <Avatar color="blue" radius="xl">{userName.charAt(0).toUpperCase()}</Avatar>
                                        <Box style={{ flex: 1, minWidth: 0 }}>
                                            <Text size="sm" fw={600} truncate>{userName}</Text>
                                            <Text size="xs" c="dimmed">{isManager ? 'Manager' : 'Carer'}</Text>
                                        </Box>
                                        <IconChevronRight size={16} />
                                    </Group>
                                </UnstyledButton>
                            </Menu.Target>
                            <Menu.Dropdown>
                                <Menu.Item component="a" href="/logout">Log out</Menu.Item>
                            </Menu.Dropdown>
                        </Menu>

                        <Group justify="space-between" px={8} mt={4}>
                            <Group gap={8}><IconMoon size={18} stroke={1.6} /><Text size="sm">Dark Mode</Text></Group>
                            <Switch checked={colorScheme === 'dark'} onChange={() => toggleColorScheme()} />
                        </Group>
                    </MantineAppShell.Section>
                </MantineAppShell.Navbar>

                <MantineAppShell.Main>{children}</MantineAppShell.Main>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
