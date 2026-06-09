import { useState } from 'react';
import {
    AppShell as MantineAppShell, Group, Text, Burger, ScrollArea, Avatar, Badge,
    SegmentedControl, ActionIcon, UnstyledButton, Box, Switch, Menu, Divider,
    useMantineColorScheme,
} from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import { usePage, Link } from '@inertiajs/react';
import {
    IconHome, IconNotebook, IconClock, IconShieldLock, IconBox, IconAlertTriangle,
    IconArrowsLeftRight, IconLayoutGrid, IconCalendar, IconCalendarStats, IconUsers,
    IconUserCircle, IconSend, IconReportAnalytics, IconMessage, IconBell, IconMoon,
    IconChevronDown, IconChevronLeft,
} from '@tabler/icons-react';
import { RoleContext } from '@frontend/lib/role';
import { brand } from '@frontend/tokens';
import logoUrl from '@frontend/assets/logo-careoneos.png';

// The whole left-nav, in render order. `section` rows are the grey group labels.
// Only the medication items have real routes today; the rest are placeholders (#).
const NAV = [
    { label: 'Home', icon: IconHome, href: '#' },
    { label: 'Daily Log', icon: IconNotebook, href: '#' },
    { section: 'Medications' },
    { label: 'Medication Round', icon: IconClock, href: '/medication/medication-round-react' },
    { label: 'Controlled Drugs', icon: IconShieldLock, href: '/medication/controlled-drugs-react' },
    { label: 'Medication Stock', icon: IconBox, href: '/medication/stock-react' },
    { label: 'Missed Doses', icon: IconAlertTriangle, href: '/medication/missed-doses-react' },
    { label: 'Shift Handover', icon: IconArrowsLeftRight, href: '/medication/shift-handover-react' },
    { section: 'Domiciliary Care' },
    { label: 'Dom Care Dashboard', icon: IconLayoutGrid, href: '#' },
    { label: 'Visit Schedule', icon: IconCalendar, href: '#' },
    { label: 'Carer Availability', icon: IconCalendarStats, href: '#' },
    { divider: true },
    { label: 'Carers', icon: IconUsers, href: '#' },
    { label: 'Clients', icon: IconUserCircle, href: '#' },
    { label: 'Runs', icon: IconSend, href: '#' },
    { label: 'Reports', icon: IconReportAnalytics, href: '#', managerOnly: true },
    { label: 'Communications', icon: IconMessage, href: '#' },
];

function NavItem({ item, active, collapsed }) {
    const Icon = item.icon;
    const disabled = item.href === '#';
    const inner = (
        <Group gap="sm" wrap="nowrap" px="sm" py={9} style={{
            borderRadius: 8,
            borderLeft: `3px solid ${active ? 'var(--mantine-color-indigo-6)' : 'transparent'}`,
            background: active ? 'var(--mantine-color-indigo-0)' : 'transparent',
            color: active ? 'var(--mantine-color-indigo-7)' : 'var(--mantine-color-gray-7)',
            opacity: disabled ? 0.5 : 1,
            cursor: disabled ? 'default' : 'pointer',
        }}>
            <Icon size={20} stroke={1.6} color={active ? 'var(--mantine-color-indigo-6)' : 'currentColor'} />
            {!collapsed && <Text size="sm" fw={active ? 600 : 500}>{item.label}</Text>}
        </Group>
    );
    if (disabled) return <Box mb={2} title="Coming soon">{inner}</Box>;
    return <Box component={Link} href={item.href} mb={2} style={{ textDecoration: 'none', display: 'block' }}>{inner}</Box>;
}

export default function AppShell({ children }) {
    const [mobileOpened, { toggle: toggleMobile }] = useDisclosure();
    const [desktopOpened, { toggle: toggleDesktop }] = useDisclosure(true);
    const { props, url } = usePage();
    const realRole = props?.auth?.user?.role ?? 'carer';
    const userName = props?.auth?.user?.name ?? 'User';
    const [previewRole, setPreviewRole] = useState(realRole);
    const { colorScheme, toggleColorScheme } = useMantineColorScheme();

    const isManager = previewRole === 'manager';
    const roleLabel = isManager ? 'Care Manager' : 'Carer';

    return (
        <RoleContext.Provider value={previewRole}>
            <MantineAppShell
                layout="alt"
                header={{ height: 64 }}
                navbar={{ width: 264, breakpoint: 'sm', collapsed: { mobile: !mobileOpened, desktop: !desktopOpened } }}
                padding="lg"
            >
                {/* ---- Header ---- */}
                <MantineAppShell.Header>
                    <Group h="100%" px="lg" justify="space-between" wrap="nowrap">
                        <Group gap="sm" wrap="nowrap">
                            <Burger opened={mobileOpened} onClick={toggleMobile} hiddenFrom="sm" size="sm" />
                            <Burger opened={desktopOpened} onClick={toggleDesktop} visibleFrom="sm" size="sm" />
                        </Group>

                        <Group gap="md" wrap="nowrap">
                            <Group gap={6} visibleFrom="md">
                                <Text size="xs" c="dimmed">Preview:</Text>
                                <SegmentedControl
                                    size="xs"
                                    value={previewRole}
                                    onChange={setPreviewRole}
                                    data={[{ label: 'Manager', value: 'manager' }, { label: 'Carer', value: 'carer' }]}
                                />
                            </Group>

                            <ActionIcon variant="subtle" color="gray" radius="xl" size="lg" pos="relative">
                                <IconBell size={20} stroke={1.6} />
                                <Box pos="absolute" top={10} right={11} w={8} h={8} style={{ background: 'var(--mantine-color-indigo-6)', borderRadius: '50%' }} />
                            </ActionIcon>

                            <Menu position="bottom-end" withArrow width={210}>
                                <Menu.Target>
                                    <UnstyledButton>
                                        <Group gap="sm" wrap="nowrap">
                                            <Avatar color="indigo" radius="xl" size={36}>{userName.charAt(0).toUpperCase()}</Avatar>
                                            <Box visibleFrom="sm" style={{ lineHeight: 1.1 }}>
                                                <Text size="sm" fw={600}>{userName}</Text>
                                                <Text size="xs" c="dimmed">{roleLabel}</Text>
                                            </Box>
                                            <IconChevronDown size={16} stroke={1.6} />
                                        </Group>
                                    </UnstyledButton>
                                </Menu.Target>
                                <Menu.Dropdown>
                                    <Box px="sm" py={6}>
                                        <Group justify="space-between">
                                            <Group gap={8}><IconMoon size={16} stroke={1.6} /><Text size="sm">Dark mode</Text></Group>
                                            <Switch size="sm" checked={colorScheme === 'dark'} onChange={() => toggleColorScheme()} />
                                        </Group>
                                    </Box>
                                    <Menu.Divider />
                                    <Menu.Item component="a" href="/logout">Log out</Menu.Item>
                                </Menu.Dropdown>
                            </Menu>
                        </Group>
                    </Group>
                </MantineAppShell.Header>

                {/* ---- Sidebar ---- */}
                <MantineAppShell.Navbar>
                    <Group h={64} px="lg" wrap="nowrap" style={{ background: brand.navy }}>
                        <img src={logoUrl} alt="Care One OS" style={{ height: 28, display: 'block' }} />
                    </Group>
                    <Divider />

                    <MantineAppShell.Section grow component={ScrollArea} px="sm" py="md">
                        {NAV
                            .filter((i) => !i.managerOnly || isManager)
                            .map((item, idx) => {
                                if (item.divider) return <Divider key={`d${idx}`} my="sm" />;
                                if (item.section) {
                                    return (
                                        <Text key={`s${idx}`} size="xs" fw={700} c="dimmed" tt="uppercase"
                                            px="sm" mt="md" mb={6} style={{ letterSpacing: 0.6 }}>
                                            {item.section}
                                        </Text>
                                    );
                                }
                                return <NavItem key={item.label} item={item} active={item.href !== '#' && url.startsWith(item.href)} />;
                            })}
                    </MantineAppShell.Section>

                    <Divider />
                    <MantineAppShell.Section p="sm">
                        <UnstyledButton onClick={toggleDesktop} style={{ width: '100%', borderRadius: 8 }}>
                            <Group gap="sm" px="sm" py={9} c="dimmed">
                                <IconChevronLeft size={18} stroke={1.6} />
                                <Text size="sm" fw={500}>Collapse</Text>
                            </Group>
                        </UnstyledButton>
                    </MantineAppShell.Section>
                </MantineAppShell.Navbar>

                <MantineAppShell.Main>{children}</MantineAppShell.Main>
            </MantineAppShell>
        </RoleContext.Provider>
    );
}
